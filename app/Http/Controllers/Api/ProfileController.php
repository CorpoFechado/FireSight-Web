<?php

namespace App\Http\Controllers\Api;

use App\Models\ResidentAddress;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ProfileController extends ApiController
{
    /**
     * Get the authenticated user's full profile including address and personnel details.
     * Matches firesight-api/profile/get.php.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->sendSuccess($this->formatUserWithAddress($user));
    }

    /**
     * Update user profile fields, password, address, and profile photo.
     * Matches firesight-api/profile/update.php.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        // Contact number validation if present
        if ($request->filled('contact_number')) {
            $mobileDigits = preg_replace('/\D/', '', (string) $request->input('contact_number'));
            if (! preg_match('/^(09\d{9}|639\d{9})$/', $mobileDigits)) {
                return $this->sendError('Please enter a valid Philippine mobile number.', 422);
            }
        }

        // Password validation if present
        if ($request->filled('password')) {
            $password = (string) $request->input('password');
            if (strlen($password) < 8) {
                return $this->sendError('New password must be at least 8 characters.', 422);
            }

            if ($request->filled('current_password')) {
                if (! Hash::check((string) $request->input('current_password'), $user->password)) {
                    return $this->sendError('The current password you entered is incorrect.', 422);
                }
            }
        }

        DB::beginTransaction();
        try {
            $userFields = ['first_name', 'middle_name', 'last_name', 'suffix', 'contact_number'];
            foreach ($userFields as $field) {
                if ($request->has($field)) {
                    $val = $request->input($field);
                    $user->{$field} = $val !== null && trim((string) $val) !== '' ? trim(strip_tags((string) $val)) : null;
                }
            }

            if ($request->filled('password')) {
                $user->password = Hash::make((string) $request->input('password'));
            }

            // Handle Profile Photo Upload if provided
            $file = $request->file('profile_image') ?? $request->file('photo');
            if ($file && $file->isValid()) {
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/heic', 'image/heif'];
                $mimeType = $file->getMimeType();

                if (! in_array($mimeType, $allowedMimeTypes, true)) {
                    return $this->sendError('Unsupported image format. Please upload JPG, PNG, or WEBP.', 422);
                }

                if ($file->getSize() > 10 * 1024 * 1024) {
                    return $this->sendError('Image is too large. Maximum size is 10MB.', 422);
                }

                $extMap = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                    'image/heic' => 'heic',
                    'image/heif' => 'heif',
                ];
                $ext = $extMap[$mimeType] ?? ($file->getClientOriginalExtension() ?: 'jpg');
                $fileName = 'profile_'.$user->user_id.'_'.time().'_'.bin2hex(random_bytes(4)).'.'.$ext;

                $uploadDir = storage_path('app/public/uploads/profiles');
                if (! File::isDirectory($uploadDir)) {
                    File::makeDirectory($uploadDir, 0755, true);
                }

                $file->move($uploadDir, $fileName);
                $user->profile_image = 'uploads/profiles/'.$fileName;
            } elseif ($request->boolean('remove_photo')) {
                $user->profile_image = null;
            }

            $user->save();

            // Address handling
            if ($request->has('house_no_street') || $request->has('barangay_id')) {
                $address = ResidentAddress::where('user_id', $user->user_id)->first();
                if ($address) {
                    if ($request->has('house_no_street')) {
                        $address->house_no_street = trim(strip_tags((string) $request->input('house_no_street', ''))) ?: null;
                    }
                    if ($request->has('barangay_id')) {
                        $address->barangay_id = (int) $request->input('barangay_id') ?: null;
                    }
                    $address->save();
                } else {
                    ResidentAddress::create([
                        'user_id' => $user->user_id,
                        'house_no_street' => trim(strip_tags((string) $request->input('house_no_street', ''))) ?: null,
                        'barangay_id' => (int) $request->input('barangay_id') ?: null,
                        'municipality' => 'Lian',
                        'province' => 'Batangas',
                    ]);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[ProfileController@update] Error: '.$e->getMessage());

            return $this->sendError('Unable to save changes right now. Please try again.', 500);
        }

        return $this->sendSuccess($this->formatUserWithAddress($user->fresh()));
    }

    /**
     * Upload profile photo directly.
     * Matches firesight-api/profile/upload_photo.php.
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        $user = $request->user();

        $file = $request->file('profile_image') ?? $request->file('photo');
        if (! $file || ! $file->isValid()) {
            return $this->sendError('Please provide an image file to upload.', 422);
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/heic', 'image/heif'];
        $mimeType = $file->getMimeType();

        if (! in_array($mimeType, $allowedMimeTypes, true)) {
            return $this->sendError('Unsupported image format. Please upload JPG, PNG, or WEBP.', 422);
        }

        if ($file->getSize() > 10 * 1024 * 1024) {
            return $this->sendError('Image is too large. Maximum size is 10MB.', 422);
        }

        $extMap = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/heic' => 'heic',
            'image/heif' => 'heif',
        ];
        $ext = $extMap[$mimeType] ?? ($file->getClientOriginalExtension() ?: 'jpg');
        $fileName = 'profile_'.$user->user_id.'_'.time().'_'.bin2hex(random_bytes(4)).'.'.$ext;

        $uploadDir = storage_path('app/public/uploads/profiles');
        if (! File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true);
        }

        $file->move($uploadDir, $fileName);
        $user->profile_image = 'uploads/profiles/'.$fileName;
        $user->save();

        return $this->sendSuccess($this->formatUserWithAddress($user->fresh()));
    }

    /**
     * Request account deletion (30-day grace period).
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== User::ROLE_RESIDENT) {
            return $this->sendError('Official personnel accounts cannot be self-deleted.', 403);
        }

        $user->status = User::STATUS_INACTIVE;
        $user->suspended_at = now();
        $user->suspension_reason = 'Resident requested account deletion (30-day grace period)';
        $user->save();

        // Invalidate active mobile tokens
        DB::table('auth_token')->where('user_id', $user->user_id)->delete();

        return $this->sendSuccess([
            'message' => 'Your account deletion has been scheduled. Your account will be permanently removed in 30 days.',
        ]);
    }
}
