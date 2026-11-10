<?php

namespace App\Http\Controllers\Api;

use App\Mail\PasswordResetOtpMail;
use App\Mail\VerifyEmailMail;
use App\Models\AuthToken;
use App\Models\Barangay;
use App\Models\ResidentAddress;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class AuthController extends ApiController
{
    /**
     * Authenticate a resident or personnel user.
     * Supports both email address and mobile number lookup.
     * Matches firesight-api/auth/login.php.
     */
    public function login(Request $request): JsonResponse
    {
        $loginInput = trim((string) ($request->input('email') ?? $request->input('login') ?? ''));
        $password = (string) $request->input('password', '');

        if (empty($loginInput)) {
            return $this->sendError('Please enter your email address or mobile number.', 422);
        }

        if (empty($password)) {
            return $this->sendError('Missing required field(s): password', 422);
        }

        if (str_contains($loginInput, '@')) {
            $user = User::whereRaw('LOWER(email) = ?', [strtolower($loginInput)])->first();
        } else {
            $digitsOnly = preg_replace('/\D/', '', $loginInput);
            if (strlen($digitsOnly) >= 7) {
                $lastDigits = substr($digitsOnly, -9);
                $user = User::where('contact_number', $loginInput)
                    ->orWhereRaw('contact_number IS NOT NULL AND REPLACE(REPLACE(contact_number, "-", ""), " ", "") LIKE ?', ['%'.$lastDigits])
                    ->first();
            } else {
                $user = User::where('contact_number', $loginInput)->first();
            }
        }

        if (! $user || ! $user->password || ! Hash::check($password, $user->password)) {
            return $this->sendError('Incorrect credentials. Please check your email or mobile number.', 401);
        }

        if ($user->role === User::ROLE_RESIDENT && ! $user->is_verified && ! $user->email_verified_at) {
            return response()->json([
                'success' => false,
                'requires_verification' => true,
                'message' => 'Please verify your email address to activate your account. We sent a verification link to your email.',
                'email' => $user->email,
            ], 403);
        }

        if ($user->status === User::STATUS_INACTIVE) {
            // Check if this was a resident self-requested deletion within the 30-day grace period
            $isGracePeriod = $user->role === User::ROLE_RESIDENT
                && $user->suspended_at
                && $user->suspended_at->greaterThanOrEqualTo(now()->subDays(30))
                && str_contains($user->suspension_reason ?? '', 'deletion');

            if ($isGracePeriod) {
                // Cancel scheduled deletion and reactivate account
                $user->status = User::STATUS_ACTIVE;
                $user->suspended_at = null;
                $user->suspension_reason = null;
                $user->save();
            } else {
                return $this->sendError($user->suspensionMessage(), 403);
            }
        }

        $token = bin2hex(random_bytes(32));
        AuthToken::create([
            'user_id' => $user->user_id,
            'token' => $token,
            'device_info' => $request->userAgent() ?? 'unknown',
            'expires_at' => now()->addDays(90),
            'created_at' => now(),
        ]);

        return $this->sendSuccess([
            'token' => $token,
            'user' => $this->formatUserWithAddress($user),
        ]);
    }

    /**
     * Register a new resident user with address and issue an authentication token.
     * Matches firesight-api/auth/register.php.
     */
    public function register(Request $request): JsonResponse
    {
        $googleId = trim((string) $request->input('google_id', ''));
        $isGoogle = ! empty($googleId);

        $required = ['first_name', 'last_name', 'mobile_number', 'barangay_id', 'municipality', 'province', 'email'];
        if (! $isGoogle) {
            $required[] = 'password';
        }

        $missing = [];
        foreach ($required as $field) {
            if (! $request->has($field) || $request->input($field) === null || $request->input($field) === '') {
                $missing[] = $field;
            }
        }

        if (! empty($missing)) {
            return $this->sendError('Missing required field(s): '.implode(', ', $missing), 422);
        }

        $firstName = trim(strip_tags((string) $request->input('first_name')));
        $middleName = trim(strip_tags((string) $request->input('middle_name', '')));
        $lastName = trim(strip_tags((string) $request->input('last_name')));
        $suffix = trim(strip_tags((string) $request->input('suffix', '')));
        $mobileNumber = trim(strip_tags((string) $request->input('mobile_number')));
        $houseNoStreet = trim(strip_tags((string) $request->input('house_no_street', '')));
        $barangayId = (int) $request->input('barangay_id');
        $municipality = trim(strip_tags((string) $request->input('municipality')));
        $province = trim(strip_tags((string) $request->input('province')));
        $email = strtolower(trim((string) $request->input('email')));
        $password = (string) $request->input('password', '');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->sendError('Please enter a valid email address.', 422);
        }

        $mobileDigits = preg_replace('/\D/', '', $mobileNumber);
        if (! preg_match('/^(09\d{9}|639\d{9})$/', $mobileDigits)) {
            return $this->sendError('Please enter a valid Philippine mobile number.', 422);
        }

        if (! $isGoogle) {
            if (strlen($password) < 8) {
                return $this->sendError('Password must be at least 8 characters.', 422);
            }
        } else {
            // For Google sign-ups, password is completely optional (nullable in database)
            if (empty($password) || strlen($password) < 8) {
                $password = null;
            }
        }

        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            $hasAddress = ResidentAddress::where('user_id', $existingUser->user_id)->exists();
            if ($isGoogle && ! $hasAddress) {
                // User was partially initialized via Google; update their profile and add address
                DB::beginTransaction();
                try {
                    $existingUser->update([
                        'first_name' => $firstName,
                        'middle_name' => $middleName ?: null,
                        'last_name' => $lastName,
                        'suffix' => $suffix ?: null,
                        'contact_number' => $mobileNumber,
                        'google_id' => $googleId,
                        'is_verified' => true,
                        'email_verified_at' => now(),
                    ]);

                    ResidentAddress::create([
                        'user_id' => $existingUser->user_id,
                        'house_no_street' => $houseNoStreet ?: null,
                        'barangay_id' => $barangayId,
                        'municipality' => $municipality,
                        'province' => $province,
                    ]);

                    $token = bin2hex(random_bytes(32));
                    AuthToken::create([
                        'user_id' => $existingUser->user_id,
                        'token' => $token,
                        'device_info' => $request->userAgent() ?? 'mobile',
                        'expires_at' => now()->addDays(90),
                        'created_at' => now(),
                    ]);

                    DB::commit();

                    return $this->sendSuccess([
                        'token' => $token,
                        'user' => $this->formatUserWithAddress($existingUser),
                    ], 201);
                } catch (\Throwable $e) {
                    DB::rollBack();
                    Log::error('API Google complete registration error: '.$e->getMessage());

                    return $this->sendError('Unable to complete your account registration. Please try again.', 500);
                }
            }

            return $this->sendError('An account with this email already exists. Try signing in instead.', 409);
        }

        if (! Barangay::where('barangay_id', $barangayId)->exists()) {
            return $this->sendError('Please select a valid barangay.', 422);
        }

        if (! $isGoogle) {
            $regToken = bin2hex(random_bytes(32));
            $cacheKey = 'pending_reg:'.$regToken;
            $emailCacheKey = 'pending_reg_email:'.md5($email);

            $registrationData = [
                'first_name' => $firstName,
                'middle_name' => $middleName ?: null,
                'last_name' => $lastName,
                'suffix' => $suffix ?: null,
                'contact_number' => $mobileNumber,
                'house_no_street' => $houseNoStreet ?: null,
                'barangay_id' => $barangayId,
                'municipality' => $municipality,
                'province' => $province,
                'email' => $email,
                'password' => Hash::make($password),
            ];

            Cache::put($cacheKey, $registrationData, now()->addMinutes(60));
            Cache::put($emailCacheKey, $regToken, now()->addMinutes(60));

            try {
                $verificationUrl = URL::temporarySignedRoute(
                    'firesight.verify_registration',
                    now()->addMinutes(60),
                    ['token' => $regToken]
                );
                Mail::to($email)->send(new VerifyEmailMail($firstName, $verificationUrl));
            } catch (\Throwable $e) {
                Log::error('[Register] Failed sending verification email: '.$e->getMessage());
            }

            return $this->sendSuccess([
                'pending' => true,
                'requires_verification' => true,
                'email' => $email,
                'message' => 'Please verify your email address. A verification link has been sent to your inbox.',
            ], 200);
        }

        // Google sign-up: immediate insertion since Google already verifies ownership
        DB::beginTransaction();
        try {
            $user = User::create([
                'role' => User::ROLE_RESIDENT,
                'status' => User::STATUS_ACTIVE,
                'first_name' => $firstName,
                'middle_name' => $middleName ?: null,
                'last_name' => $lastName,
                'suffix' => $suffix ?: null,
                'contact_number' => $mobileNumber,
                'email' => $email,
                'password' => $password ? Hash::make($password) : null,
                'google_id' => $googleId ?: null,
                'is_verified' => true,
                'email_verified_at' => now(),
            ]);

            ResidentAddress::create([
                'user_id' => $user->user_id,
                'house_no_street' => $houseNoStreet ?: null,
                'barangay_id' => $barangayId,
                'municipality' => $municipality,
                'province' => $province,
            ]);

            $token = bin2hex(random_bytes(32));
            AuthToken::create([
                'user_id' => $user->user_id,
                'token' => $token,
                'device_info' => $request->userAgent() ?? 'unknown',
                'expires_at' => now()->addDays(90),
                'created_at' => now(),
            ]);

            DB::commit();

            return $this->sendSuccess([
                'token' => $token,
                'user' => $this->formatUserWithAddress($user),
                'requires_verification' => false,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('API Registration error: '.$e->getMessage());

            return $this->sendError('Unable to complete your registration right now. Please try again.', 500);
        }
    }

    /**
     * Invalidate the current session token.
     * Matches firesight-api/auth/logout.php.
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->bearerToken();
        if ($token) {
            AuthToken::where('token', $token)->delete();
        }

        return $this->sendSuccess(['message' => 'Signed out successfully.']);
    }

    /**
     * Request a password reset token.
     * Matches firesight-api/auth/forgot_password.php.
     */
    /**
     * Request a 6-digit OTP code to reset password.
     * Enforces role security: BFP Personnel accounts must be reset via Station Admin.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $email = strtolower(trim((string) $request->input('email', '')));

        if (empty($email)) {
            return $this->sendError('Missing required field(s): email', 422);
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->sendError('Please enter a valid email address.', 422);
        }

        $user = User::where('email', $email)->first();

        // Security Guard: Prevent public self-service resets for official responder accounts
        if ($user && in_array($user->role, [User::ROLE_BFP_PERSONNEL, User::ROLE_BFP_ADMIN], true)) {
            return $this->sendError(
                'BFP Personnel accounts are managed by your Fire Station Administrator. Please coordinate with your Station Commander for credential resets.',
                403
            );
        }

        if (! $user) {
            return $this->sendError('No registered account was found with that email address.', 404);
        }

        // Generate secure 6-digit numerical OTP code
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_tokens')->where('email', $email)->delete();
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => $otp,
            'created_at' => now(),
        ]);

        Log::info("FIRESIGHT 6-DIGIT RESET OTP for {$email}: {$otp}");

        try {
            Mail::to($email)->send(new PasswordResetOtpMail($otp, $user->first_name));
        } catch (\Throwable $e) {
            Log::error("[ForgotPassword] Failed sending OTP email to {$email}: ".$e->getMessage());
        }

        $data = [
            'message' => 'If an account exists for that email, password reset instructions have been sent.',
            'email' => $email,
        ];

        // Return dev_otp in non-production environments to allow instant testing
        if (app()->environment('local', 'testing') || config('app.debug')) {
            $data['dev_otp'] = $otp;
        }

        return $this->sendSuccess($data);
    }

    /**
     * Verify the 6-digit OTP code before proceeding to password change.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $email = strtolower(trim((string) $request->input('email', '')));
        $otp = trim((string) $request->input('otp', ''));

        if (empty($email) || empty($otp)) {
            return $this->sendError('Email and 6-digit verification code are required.', 422);
        }

        $resetRow = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('token', $otp)
            ->where('created_at', '>=', now()->subMinutes(15))
            ->first();

        if (! $resetRow) {
            return $this->sendError('Invalid or expired verification code. Please check your code or request a new one.', 400);
        }

        return $this->sendSuccess([
            'valid' => true,
            'message' => 'Verification code confirmed. You may now enter your new password.',
        ]);
    }

    /**
     * Reset password using the verified 6-digit OTP.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $email = strtolower(trim((string) $request->input('email', '')));
        $otp = trim((string) ($request->input('otp') ?? $request->input('token') ?? ''));
        $newPassword = (string) $request->input('new_password', '');

        $missing = [];
        if (empty($otp)) {
            $missing[] = 'otp';
        }
        if (empty($newPassword)) {
            $missing[] = 'new_password';
        }

        if (! empty($missing)) {
            return $this->sendError('Missing required field(s): '.implode(', ', $missing), 422);
        }

        if (strlen($newPassword) < 8) {
            return $this->sendError('Password must be at least 8 characters.', 422);
        }

        $query = DB::table('password_reset_tokens')
            ->where('token', $otp)
            ->where('created_at', '>=', now()->subMinutes(15));

        if (! empty($email)) {
            $query->where('email', $email);
        }

        $resetRow = $query->first();

        if (! $resetRow) {
            return $this->sendError('This verification code is invalid or has expired. Please request a new one.', 400);
        }

        $user = User::where('email', $resetRow->email)->first();
        if (! $user) {
            return $this->sendError('Account not found.', 404);
        }

        DB::beginTransaction();
        try {
            $user->password = Hash::make($newPassword);
            $user->save();

            DB::table('password_reset_tokens')->where('email', $resetRow->email)->delete();
            AuthToken::where('user_id', $user->user_id)->delete();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('API Reset password error: '.$e->getMessage());

            return $this->sendError('Unable to reset your password right now. Please try again.', 500);
        }

        return $this->sendSuccess(['message' => 'Your password has been reset successfully. Please sign in with your new password.']);
    }

    /**
     * Google Sign-In endpoint.
     * Verifies the Google token/ID token, checks or logs in existing user,
     * or returns profile info so mobile can complete resident registration.
     */
    public function googleLogin(Request $request): JsonResponse
    {
        $idToken = $request->input('id_token') ?? $request->input('token');
        $accessToken = $request->input('access_token');
        $googleId = $request->input('google_id');
        $email = $request->input('email');

        if (empty($idToken) && empty($accessToken) && (empty($googleId) || empty($email))) {
            return $this->sendError('Google token or user information is required.', 422);
        }

        $googleUser = null;

        // 1. Verify ID token with Google's public tokeninfo endpoint
        if (! empty($idToken)) {
            try {
                $response = Http::timeout(7)->get('https://oauth2.googleapis.com/tokeninfo', [
                    'id_token' => $idToken,
                ]);

                if ($response->successful()) {
                    $payload = $response->json();
                    $googleId = $payload['sub'] ?? $googleId;
                    $email = $payload['email'] ?? $email;
                    $googleUser = [
                        'google_id' => $googleId,
                        'email' => strtolower((string) $email),
                        'first_name' => $payload['given_name'] ?? ($payload['name'] ?? ''),
                        'last_name' => $payload['family_name'] ?? '',
                        'profile_image' => $payload['picture'] ?? null,
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('Google tokeninfo verification network issue: '.$e->getMessage());
            }
        }

        // 2. If ID token wasn't provided or didn't yield user, verify via access_token with userinfo
        if (! $googleUser && ! empty($accessToken)) {
            try {
                $response = Http::timeout(7)->withToken($accessToken)->get('https://www.googleapis.com/oauth2/v3/userinfo');
                if ($response->successful()) {
                    $payload = $response->json();
                    $googleId = $payload['sub'] ?? $googleId;
                    $email = $payload['email'] ?? $email;
                    $googleUser = [
                        'google_id' => $googleId,
                        'email' => strtolower((string) $email),
                        'first_name' => $payload['given_name'] ?? ($payload['name'] ?? ''),
                        'last_name' => $payload['family_name'] ?? '',
                        'profile_image' => $payload['picture'] ?? null,
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('Google userinfo verification network issue: '.$e->getMessage());
            }
        }

        if (! $googleUser && ! empty($googleId) && ! empty($email)) {
            $googleUser = [
                'google_id' => $googleId,
                'email' => strtolower((string) $email),
                'first_name' => (string) $request->input('first_name', ''),
                'last_name' => (string) $request->input('last_name', ''),
                'profile_image' => $request->input('profile_image'),
            ];
        }

        if (! $googleUser || empty($googleUser['email'])) {
            return $this->sendError('Invalid or expired Google authentication credentials.', 401);
        }

        // Check if user already exists by google_id OR email
        $user = User::where('google_id', $googleUser['google_id'])
            ->orWhereRaw('LOWER(email) = ?', [$googleUser['email']])
            ->first();

        if ($user) {
            // Link google_id if not linked yet
            if (empty($user->google_id)) {
                $user->google_id = $googleUser['google_id'];
            }
            if (! $user->email_verified_at) {
                $user->email_verified_at = now();
            }
            if (! empty($googleUser['profile_image']) && empty($user->profile_image)) {
                $user->profile_image = $googleUser['profile_image'];
            }
            $user->save();

            // Check if resident profile is complete (has contact number & resident address)
            $hasAddress = ResidentAddress::where('user_id', $user->user_id)->exists();
            $hasContact = ! empty($user->contact_number);

            if (! $hasAddress || ! $hasContact) {
                return $this->sendSuccess([
                    'is_new_user' => true,
                    'requires_completion' => true,
                    'google_user' => [
                        'user_id' => $user->user_id,
                        'google_id' => $user->google_id,
                        'email' => $user->email,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'contact_number' => $user->contact_number,
                    ],
                ]);
            }

            if ($user->status === User::STATUS_INACTIVE) {
                return $this->sendError($user->suspensionMessage(), 403);
            }

            $token = bin2hex(random_bytes(32));
            AuthToken::create([
                'user_id' => $user->user_id,
                'token' => $token,
                'device_info' => $request->userAgent() ?? 'mobile',
                'expires_at' => now()->addDays(90),
                'created_at' => now(),
            ]);

            return $this->sendSuccess([
                'is_new_user' => false,
                'token' => $token,
                'user' => $this->formatUserWithAddress($user),
            ]);
        }

        // Brand-new citizen registering with Google:
        return $this->sendSuccess([
            'is_new_user' => true,
            'requires_completion' => true,
            'google_user' => $googleUser,
        ]);
    }

    /**
     * Resend verification email link to the resident.
     * Supports both pending registrations in cache and unverified records in DB.
     */
    public function resendVerification(Request $request): JsonResponse
    {
        $email = strtolower(trim((string) $request->input('email', '')));
        if (empty($email) && $request->bearerToken()) {
            $at = AuthToken::where('token', $request->bearerToken())->first();
            $u = $at ? User::find($at->user_id) : null;
            $email = $u?->email ?? '';
        }

        if (empty($email)) {
            return $this->sendError('Please provide your email address.', 422);
        }

        // 1. If user already exists in DB:
        $existing = User::where('email', $email)->first();
        if ($existing) {
            if ($existing->hasVerifiedEmail() || $existing->is_verified) {
                return $this->sendSuccess([
                    'already_verified' => true,
                    'message' => 'Your email has already been verified.',
                ]);
            }

            try {
                $verificationUrl = URL::temporarySignedRoute(
                    'firesight.verify_email',
                    now()->addMinutes(60),
                    [
                        'id' => $existing->user_id,
                        'hash' => sha1($existing->getEmailForVerification()),
                    ]
                );

                Mail::to($existing->email)->send(new VerifyEmailMail($existing->first_name, $verificationUrl, $existing));
            } catch (\Throwable $e) {
                Log::error('[ResendVerification] Failed sending email to '.$existing->email.': '.$e->getMessage());

                return $this->sendError('Unable to send verification email right now. Please try again later.', 500);
            }

            return $this->sendSuccess([
                'message' => 'A new verification link has been sent to your email.',
            ]);
        }

        // 2. Pending registration in Cache:
        $token = Cache::get('pending_reg_email:'.md5($email));
        $data = $token ? Cache::get('pending_reg:'.$token) : null;

        if (! $data) {
            return $this->sendError('Registration session expired or not found. Please register again.', 404);
        }

        try {
            $verificationUrl = URL::temporarySignedRoute(
                'firesight.verify_registration',
                now()->addMinutes(60),
                ['token' => $token]
            );

            Mail::to($email)->send(new VerifyEmailMail($data['first_name'], $verificationUrl));
        } catch (\Throwable $e) {
            Log::error('[ResendVerification] Failed sending email to '.$email.': '.$e->getMessage());

            return $this->sendError('Unable to send verification email right now. Please try again later.', 500);
        }

        return $this->sendSuccess([
            'message' => 'A new verification link has been sent to your email.',
        ]);
    }

    /**
     * Check if the email has completed verification.
     */
    public function verificationStatus(Request $request): JsonResponse
    {
        $email = strtolower(trim((string) $request->input('email', '')));
        if (empty($email) && $request->bearerToken()) {
            $at = AuthToken::where('token', $request->bearerToken())->first();
            $u = $at ? User::find($at->user_id) : null;
            $email = $u?->email ?? '';
        }

        if (empty($email)) {
            return $this->sendError('Email address is required.', 422);
        }

        $user = User::where('email', $email)->first();

        if ($user && ($user->is_verified || $user->hasVerifiedEmail())) {
            return $this->sendSuccess([
                'is_verified' => true,
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'user' => $this->formatUserWithAddress($user),
            ]);
        }

        return $this->sendSuccess([
            'is_verified' => false,
            'email_verified_at' => null,
            'user' => null,
        ]);
    }

    /**
     * Cancel pending registration and remove from cache.
     */
    public function cancelRegistration(Request $request): JsonResponse
    {
        $email = strtolower(trim((string) $request->input('email', '')));
        if ($email) {
            $token = Cache::get('pending_reg_email:'.md5($email));
            if ($token) {
                Cache::forget('pending_reg:'.$token);
            }
            Cache::forget('pending_reg_email:'.md5($email));

            // Also clean up if an unverified user row was left in DB
            User::where('email', $email)->where('is_verified', false)->delete();
        }

        return $this->sendSuccess([
            'message' => 'Pending registration cancelled successfully.',
        ]);
    }
}
