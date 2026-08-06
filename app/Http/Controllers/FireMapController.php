<?php

namespace App\Http\Controllers;

use App\Models\CommunityReport;
use App\Support\BarangayRiskSnapshot;
use Inertia\Inertia;
use Inertia\Response;

class FireMapController extends Controller
{
    public function index(): Response
    {
        $activeStatuses = [
            CommunityReport::STATUS_PENDING,
            CommunityReport::STATUS_VERIFIED,
            CommunityReport::STATUS_DISPATCHED,
        ];

        $incidents = CommunityReport::query()
            ->whereIn('status', $activeStatuses)
            ->with('incidentRecord.barangay')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (CommunityReport $report) => [
                'report_id' => $report->report_id,
                'reference' => sprintf('INC-%s-%04d', $report->created_at->format('Y'), $report->report_id),
                'type' => $report->incidentRecord ? ucfirst($report->incidentRecord->incident_type) : null,
                'severity' => $report->incidentRecord?->severity_level,
                'status' => $report->status,
                'barangay' => $report->incidentRecord?->barangay?->barangay_name,
                'latitude' => (float) $report->latitude,
                'longitude' => (float) $report->longitude,
            ]);

        return Inertia::render('map/index', [
            'incidents' => $incidents,
            'barangayRisk' => BarangayRiskSnapshot::all(),
        ]);
    }
}
