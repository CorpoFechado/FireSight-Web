<?php

namespace App\Http\Controllers;

use App\Models\ResidentAddress;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    /**
     * Complete pending registration from cache and insert into database.
     * No user row exists in the database until this signed link is verified!
     */
    public function verifyRegistration(Request $request, string $token): View
    {
        if (! $request->hasValidSignature()) {
            return view('auth.email-verified', [
                'status' => 'expired',
                'message' => 'This verification link has expired or is invalid. Please register again in the FireSight app.',
                'deepLink' => null,
            ]);
        }

        $cacheKey = 'pending_reg:'.$token;
        $data = Cache::get($cacheKey);

        if (! $data) {
            return view('auth.email-verified', [
                'status' => 'already_verified',
                'message' => 'This registration has already been completed or expired. You can return to the FireSight mobile app to sign in.',
                'deepLink' => 'firesightmobile://',
            ]);
        }

        // Check if an account with this email was somehow already created in the meantime
        if (User::where('email', $data['email'])->exists()) {
            Cache::forget($cacheKey);
            Cache::forget('pending_reg_email:'.md5($data['email']));

            return view('auth.email-verified', [
                'status' => 'already_verified',
                'message' => 'An account with this email already exists and is verified. You can return to the FireSight mobile app to sign in.',
                'deepLink' => 'firesightmobile://',
            ]);
        }

        DB::beginTransaction();
        try {
            $user = User::create([
                'role' => User::ROLE_RESIDENT,
                'status' => User::STATUS_ACTIVE,
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'],
                'last_name' => $data['last_name'],
                'suffix' => $data['suffix'],
                'contact_number' => $data['contact_number'],
                'email' => $data['email'],
                'password' => $data['password'],
                'is_verified' => true,
                'email_verified_at' => now(),
            ]);

            ResidentAddress::create([
                'user_id' => $user->user_id,
                'house_no_street' => $data['house_no_street'],
                'barangay_id' => $data['barangay_id'],
                'municipality' => $data['municipality'],
                'province' => $data['province'],
            ]);

            DB::commit();

            Cache::forget($cacheKey);
            Cache::forget('pending_reg_email:'.md5($data['email']));

            return view('auth.email-verified', [
                'status' => 'success',
                'message' => 'Your email has been verified and your FireSight account is now created! You can now sign in to the mobile app.',
                'deepLink' => 'firesightmobile://',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[VerifyRegistration] DB transaction failed: '.$e->getMessage());

            return view('auth.email-verified', [
                'status' => 'error',
                'message' => 'Unable to complete your registration right now. Please try again.',
                'deepLink' => null,
            ]);
        }
    }

    /**
     * Mark an existing user's email as verified via the signed URL link (for existing users).
     */
    public function verify(Request $request, int $id, string $hash): View
    {
        if (! $request->hasValidSignature()) {
            return view('auth.email-verified', [
                'status' => 'expired',
                'message' => 'This verification link has expired or is invalid. Please request a new verification link from the FireSight app.',
                'deepLink' => null,
            ]);
        }

        $user = User::find($id);

        if (! $user) {
            return view('auth.email-verified', [
                'status' => 'error',
                'message' => 'User account not found.',
                'deepLink' => null,
            ]);
        }

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return view('auth.email-verified', [
                'status' => 'error',
                'message' => 'The verification link does not match this user account.',
                'deepLink' => null,
            ]);
        }

        if ($user->hasVerifiedEmail()) {
            return view('auth.email-verified', [
                'status' => 'already_verified',
                'message' => 'Your email has already been verified! You can return to the FireSight mobile app.',
                'deepLink' => 'firesightmobile://',
            ]);
        }

        $user->markEmailAsVerified();
        $user->is_verified = true;
        $user->save();

        return view('auth.email-verified', [
            'status' => 'success',
            'message' => 'Your email has been successfully verified! Your FireSight account is now activated. You can return to the mobile app to continue.',
            'deepLink' => 'firesightmobile://',
        ]);
    }
}
