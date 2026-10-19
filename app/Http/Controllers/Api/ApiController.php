<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BfpPersonnelDetails;
use App\Models\ResidentAddress;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    /**
     * Send a successful JSON response matching the mobile frontend's ApiClient expectations.
     */
    protected function sendSuccess(mixed $data, int $statusCode = 200): JsonResponse
    {
        return response()->json($data, $statusCode);
    }

    /**
     * Send an error JSON response matching firesight-api's sendError shape: { "message": "..." }.
     */
    protected function sendError(string $message, int $statusCode = 400): JsonResponse
    {
        return response()->json(['message' => $message], $statusCode);
    }

    /**
     * Builds the exact user object shape expected by the mobile frontend's AppUser type.
     * Matches firesight-api's getUserWithAddress helper.
     *
     * @return array<string, mixed>|null
     */
    protected function formatUserWithAddress(User|int $user): ?array
    {
        if (is_int($user)) {
            $user = User::find($user);
        }

        if (! $user) {
            return null;
        }

        $address = ResidentAddress::where('user_id', $user->user_id)
            ->leftJoin('barangay', 'barangay.barangay_id', '=', 'resident_address.barangay_id')
            ->select([
                'resident_address.house_no_street',
                'resident_address.barangay_id',
                'resident_address.municipality',
                'resident_address.province',
                'barangay.barangay_name',
            ])
            ->first();

        $personnelDetails = null;
        if (in_array($user->role, [User::ROLE_BFP_PERSONNEL, User::ROLE_BFP_ADMIN], true)) {
            $pd = BfpPersonnelDetails::where('user_id', $user->user_id)->first();
            if ($pd) {
                $personnelDetails = [
                    'rank' => $pd->rank,
                    'station_assigned' => $pd->station_assigned,
                    'employee_number' => $pd->employee_number,
                    'duty_group' => $pd->duty_group?->value ?? $pd->duty_group,
                ];
            }
        }

        $profileImage = $user->profile_image;
        if ($profileImage && ! str_starts_with($profileImage, 'http')) {
            $fullPath = public_path(ltrim($profileImage, '/'));
            if (file_exists($fullPath)) {
                $profileImage .= '?v='.filemtime($fullPath);
            }
        }

        return [
            'user_id' => (int) $user->user_id,
            'role' => $user->role,
            'first_name' => $user->first_name,
            'middle_name' => $user->middle_name,
            'last_name' => $user->last_name,
            'suffix' => $user->suffix,
            'contact_number' => $user->contact_number,
            'email' => $user->email,
            'profile_image' => $profileImage,
            'is_verified' => (bool) $user->is_verified,
            'address' => $address ? [
                'house_no_street' => $address->house_no_street,
                'barangay_id' => $address->barangay_id !== null ? (int) $address->barangay_id : null,
                'barangay_name' => $address->barangay_name,
                'municipality' => $address->municipality,
                'province' => $address->province,
            ] : null,
            'personnel_details' => $personnelDetails,
        ];
    }
}
