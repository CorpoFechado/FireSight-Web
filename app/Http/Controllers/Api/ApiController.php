<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BfpPersonnelDetails;
use App\Models\CommunityReport;
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
            $fullPath = public_path('storage/'.ltrim($profileImage, '/'));
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

    /**
     * Formats a community_report record into the exact CommunityReport shape the frontend expects.
     * Matches firesight-api's formatReportRow helper.
     *
     * @return array<string, mixed>
     */
    protected function formatReportRow(mixed $row): array
    {
        $createdAt = $row['created_at'] ?? ($row->created_at ?? null);
        if ($createdAt instanceof \DateTimeInterface) {
            $createdAt = $createdAt->format('Y-m-d H:i:s');
        }

        $barangayName = null;
        if ($row instanceof CommunityReport) {
            $barangayName = $row->barangay?->barangay_name;
        } elseif (is_array($row)) {
            $barangayName = $row['barangay_name'] ?? ($row['barangay']['barangay_name'] ?? null);
        } elseif (is_object($row)) {
            $barangayName = $row->barangay_name ?? ($row->barangay->barangay_name ?? null);
        }

        return [
            'report_id' => (int) ($row['report_id'] ?? $row->report_id ?? 0),
            'description' => $row['description'] ?? $row->description ?? null,
            'report_image' => $row['report_image'] ?? $row->report_image ?? null,
            'latitude' => (float) ($row['latitude'] ?? $row->latitude ?? 0),
            'longitude' => (float) ($row['longitude'] ?? $row->longitude ?? 0),
            'barangay_id' => (int) ($row['barangay_id'] ?? $row->barangay_id ?? 0),
            'barangay_name' => $barangayName,
            'status' => $row['status'] ?? $row->status ?? null,
            'created_at' => $createdAt,
        ];
    }

    /**
     * Formats an incident record (community_report joined with incident_record and barangay)
     * into the exact BFPIncident shape the frontend expects.
     * Matches firesight-api's formatIncidentRow helper.
     *
     * @return array<string, mixed>
     */
    protected function formatIncidentRow(mixed $row): array
    {
        $createdAt = $row['created_at'] ?? ($row->created_at ?? null);
        if ($createdAt instanceof \DateTimeInterface) {
            $createdAt = $createdAt->format('Y-m-d H:i:s');
        }

        $incidentDatetime = $row['incident_datetime'] ?? ($row->incident_datetime ?? null);
        if ($incidentDatetime instanceof \DateTimeInterface) {
            $incidentDatetime = $incidentDatetime->format('Y-m-d H:i:s');
        }

        $alarmLevel = $row['alarm_level'] ?? ($row->alarm_level ?? null);
        if ($alarmLevel instanceof \BackedEnum) {
            $alarmLevel = $alarmLevel->value;
        }

        $barangayName = null;
        if (is_array($row)) {
            $barangayName = $row['barangay_name'] ?? ($row['barangay']['barangay_name'] ?? null);
        } elseif (is_object($row)) {
            $barangayName = $row->barangay_name ?? ($row->barangay->barangay_name ?? null);
        }

        $accuracy = isset($row['location_accuracy_m']) ? $row['location_accuracy_m'] : ($row->location_accuracy_m ?? null);
        $confidence = isset($row['ai_fire_confidence']) ? $row['ai_fire_confidence'] : ($row->ai_fire_confidence ?? null);
        $casualties = isset($row['casualties']) ? $row['casualties'] : ($row->casualties ?? null);

        return [
            'report_id' => (int) ($row['report_id'] ?? $row->report_id ?? 0),
            'reporter_name' => $row['reporter_name'] ?? $row->reporter_name ?? null,
            'contact_number' => $row['contact_number'] ?? $row->contact_number ?? null,
            'description' => $row['description'] ?? $row->description ?? null,
            'report_image' => $row['report_image'] ?? $row->report_image ?? null,
            'latitude' => (float) ($row['latitude'] ?? $row->latitude ?? 0),
            'longitude' => (float) ($row['longitude'] ?? $row->longitude ?? 0),
            'location_accuracy_m' => $accuracy !== null ? (float) $accuracy : null,
            'barangay_id' => (int) ($row['barangay_id'] ?? $row->barangay_id ?? 0),
            'barangay_name' => $barangayName,
            'status' => $row['status'] ?? $row->status ?? null,
            'created_at' => $createdAt,
            'ai_fire_label' => $row['ai_fire_label'] ?? $row->ai_fire_label ?? null,
            'ai_fire_confidence' => $confidence !== null ? (float) $confidence : null,
            'incident_type' => $row['incident_type'] ?? $row->incident_type ?? null,
            'alarm_level' => $alarmLevel,
            'incident_datetime' => $incidentDatetime,
            'data_time' => $incidentDatetime ?? $createdAt,
            'cause_of_fire' => $row['cause_of_fire'] ?? $row->cause_of_fire ?? null,
            'casualties' => $casualties !== null ? (int) $casualties : null,
            'notes' => $row['notes'] ?? $row->notes ?? null,
        ];
    }
}
