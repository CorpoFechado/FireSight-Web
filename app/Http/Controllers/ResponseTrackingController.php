<?php

namespace App\Http\Controllers;

use App\Models\CommunityReport;
use App\Support\Geo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ResponseTrackingController extends Controller
{
    private const LIAN_STATION_LAT = 14.03694;

    private const LIAN_STATION_LNG = 120.652673;

    public function index(Request $request): Response
    {
        $dispatchedReports = CommunityReport::query()
            ->where('status', CommunityReport::STATUS_DISPATCHED)
            ->with(['incidentRecord.barangay', 'barangay', 'reporter'])
            ->latest('created_at')
            ->get();

        $selectedId = $request->integer('incident');
        $selectedReport = $selectedId
            ? $dispatchedReports->firstWhere('report_id', $selectedId)
            : $dispatchedReports->first();

        if (! $selectedReport && $dispatchedReports->isNotEmpty()) {
            $selectedReport = $dispatchedReports->first();
        }

        $incident = null;
        $routeSummary = null;

        if ($selectedReport && $selectedReport->latitude !== null && $selectedReport->longitude !== null) {
            $incident = [
                'id' => $selectedReport->report_id,
                'reference' => IncidentController::reference($selectedReport),
                'type' => $selectedReport->incidentRecord?->incident_type
                    ? AnalyticsController::TYPE_LABELS[$selectedReport->incidentRecord->incident_type] ?? ucfirst(str_replace('_', ' ', $selectedReport->incidentRecord->incident_type))
                    : 'Fire Incident',
                'barangay' => $selectedReport->barangay?->barangay_name ?? $selectedReport->incidentRecord?->barangay?->barangay_name ?? 'Lian',
                'alarm_level' => $selectedReport->incidentRecord?->alarm_level?->value ?? $selectedReport->incidentRecord?->alarm_level,
                'reporter' => $selectedReport->reporter_name ?: ($selectedReport->reporter?->name ?? 'Citizen Reporter'),
                'latitude' => (float) $selectedReport->latitude,
                'longitude' => (float) $selectedReport->longitude,
            ];

            $distanceKm = round(Geo::distanceInMeters(
                self::LIAN_STATION_LAT,
                self::LIAN_STATION_LNG,
                (float) $selectedReport->latitude,
                (float) $selectedReport->longitude
            ) / 1000, 1);

            $etaMinutes = max(1, (int) round(($distanceKm / 35) * 60));

            $routeSummary = [
                'distanceKm' => $distanceKm,
                'etaMinutes' => $etaMinutes,
                'routeType' => 'Optimal',
            ];
        }

        $allDispatched = $dispatchedReports->map(fn (CommunityReport $r) => [
            'id' => $r->report_id,
            'reference' => IncidentController::reference($r),
            'barangay' => $r->barangay?->barangay_name ?? $r->incidentRecord?->barangay?->barangay_name ?? 'Lian',
            'type' => $r->incidentRecord?->incident_type
                ? AnalyticsController::TYPE_LABELS[$r->incidentRecord->incident_type] ?? ucfirst(str_replace('_', ' ', $r->incidentRecord->incident_type))
                : 'Fire Incident',
            'alarm_level' => $r->incidentRecord?->alarm_level?->value ?? $r->incidentRecord?->alarm_level,
        ])->values()->all();

        return Inertia::render('response-tracking/index', [
            'incident' => $incident,
            'dispatchedIncidents' => $allDispatched,
            'routeSummary' => $routeSummary,
            'station' => [
                'name' => 'BFP Lian Station',
            ],
        ]);
    }
}
