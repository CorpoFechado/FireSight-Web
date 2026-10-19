<?php

namespace App\Http\Controllers\Api;

use App\Models\AuthToken;
use App\Models\Barangay;
use App\Models\ResidentAddress;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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

        if ($user->status === User::STATUS_INACTIVE) {
            return $this->sendError('This account has been deactivated. Please contact BFP Lian.', 403);
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
        $required = ['first_name', 'last_name', 'mobile_number', 'barangay_id', 'municipality', 'province', 'email', 'password'];
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
        $password = (string) $request->input('password');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->sendError('Please enter a valid email address.', 422);
        }

        $mobileDigits = preg_replace('/\D/', '', $mobileNumber);
        if (! preg_match('/^(09\d{9}|639\d{9})$/', $mobileDigits)) {
            return $this->sendError('Please enter a valid Philippine mobile number.', 422);
        }

        if (strlen($password) < 8) {
            return $this->sendError('Password must be at least 8 characters.', 422);
        }

        if (User::where('email', $email)->exists()) {
            return $this->sendError('An account with this email already exists. Try signing in instead.', 409);
        }

        if (! Barangay::where('barangay_id', $barangayId)->exists()) {
            return $this->sendError('Please select a valid barangay.', 422);
        }

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
                'password' => Hash::make($password),
                'is_verified' => false,
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
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('API Registration error: '.$e->getMessage());

            return $this->sendError('Unable to create your account right now. Please try again.', 500);
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
        if ($user) {
            $token = bin2hex(random_bytes(32));
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            DB::table('password_reset_tokens')->insert([
                'email' => $email,
                'token' => $token,
                'created_at' => now(),
            ]);

            Log::info("PASSWORD RESET TOKEN for {$email}: {$token}");
        }

        return $this->sendSuccess(['message' => 'If an account exists for that email, password reset instructions have been sent.']);
    }

    /**
     * Reset password using a valid reset token.
     * Matches firesight-api/auth/reset_password.php.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $token = trim((string) $request->input('token', ''));
        $newPassword = (string) $request->input('new_password', '');

        $missing = [];
        if (empty($token)) {
            $missing[] = 'token';
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

        $resetRow = DB::table('password_reset_tokens')
            ->where('token', $token)
            ->where('created_at', '>=', now()->subMinutes(60))
            ->first();

        if (! $resetRow) {
            return $this->sendError('This reset link is invalid or has expired. Please request a new one.', 400);
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
     * Google Sign-In endpoint stub.
     * Matches firesight-api/auth/google_login.php.
     */
    public function googleLogin(): JsonResponse
    {
        return $this->sendError('Google Sign-In is not yet configured on this server.', 501);
    }
}
