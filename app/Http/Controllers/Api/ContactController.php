<?php

namespace App\Http\Controllers\Api;

use App\Models\BarangayContact;
use App\Models\EmergencyContact;
use Illuminate\Http\JsonResponse;

class ContactController extends ApiController
{
    /**
     * GET /api/v1/contacts (or /contacts/list.php)
     * Matches firesight-api/contacts/list.php exactly.
     */
    public function index(): JsonResponse
    {
        $rows = EmergencyContact::orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->get();

        $result = $rows->map(function (EmergencyContact $row) {
            return [
                'contact_id' => (int) $row->contact_id,
                'name' => $row->name,
                'category' => $row->category,
                'phone_number' => $row->phone_number,
                'description' => $row->description,
                'is_primary' => (bool) $row->is_primary,
            ];
        });

        return $this->sendSuccess($result);
    }

    /**
     * GET /api/v1/contacts/barangay
     * Returns barangay captains and councilors for all barangays.
     */
    public function barangayContacts(): JsonResponse
    {
        $rows = BarangayContact::with('barangay:barangay_id,barangay_name')
            ->orderBy('barangay_id')
            ->get();

        $result = $rows->map(function (BarangayContact $row) {
            return [
                'contact_id' => (int) $row->contact_id,
                'barangay_id' => (int) $row->barangay_id,
                'barangay_name' => $row->barangay?->barangay_name,
                'name' => $row->name,
                'role' => $row->role,
                'phone_number' => $row->phone_number,
            ];
        });

        return $this->sendSuccess($result);
    }
}
