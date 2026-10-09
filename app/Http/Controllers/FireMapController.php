<?php

namespace App\Http\Controllers;

use App\Models\CommunityReport;
use App\Support\BarangayRiskSnapshot;
use App\Support\DateRange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Inertia\Inertia;
use Inertia\Response;

class FireMapController extends Controller
{
    /**
     * The only status shown on the historical fire map.
     * Keeping it as a named constant makes it easy to add an
     * "all / in-progress" option later without touching query logic.
     */
    private const DISPLAY_STATUS = CommunityReport::STATUS_RESOLVED;

    /** @var list<string> */
    private const VALID_PERIODS = ['this_month', 'last_3_months', 'this_year', 'custom'];

    /** @var list<string> */
    private const VALID_TYPES = [
        'residential_fire',
        'commercial_fire',
        'vehicular_fire',
        'storage_fire',
        'rubbish_fire',
        'others',
    ];

    /** @var list<string> */
    private const VALID_ALARM_LEVELS = [
        '1st_alarm',
        '2nd_alarm',
        '3rd_alarm',
        '4th_alarm',
        '5th_alarm',
        'task_force_alpha',
        'task_force_bravo',
        'task_force_charlie',
        'task_force_delta',
        'task_force_echo',
        'task_force_hotel',
        'task_force_india',
        'general_alarm',
    ];

    public function index(Request $request): Response
    {
        $now = Date::now();

        // ── Period ----------------------------------------------------------
        $period = $request->string('period')->lower()->value();
        if (! in_array($period, self::VALID_PERIODS, true)) {
            $period = 'this_year';
        }

        $dateFrom = $request->string('date_from')->trim()->value();
        $dateTo = $request->string('date_to')->trim()->value();

        [$rangeStart, $rangeEnd] = DateRange::resolve($period, $dateFrom, $dateTo, $now);

        // ── Type / alarm level filters ──────────────────────────────────────
        // Any value not in the whitelist (including '' or 'all') means no filter.
        $incidentType = $request->string('incident_type')->trim()->value();
        if (! in_array($incidentType, self::VALID_TYPES, true)) {
            $incidentType = 'all';
        }

        $alarmLevel = $request->string('alarm_level')->trim()->value();
        if (! in_array($alarmLevel, self::VALID_ALARM_LEVELS, true)) {
            $alarmLevel = 'all';
        }

        // ── Query ───────────────────────────────────────────────────────────
        $incidents = CommunityReport::query()
            ->where('status', self::DISPLAY_STATUS)
            // Date range on community_report.created_at (submission date).
            ->whereBetween('created_at', [$rangeStart, $rangeEnd])
            ->when(
                $incidentType !== 'all',
                fn ($q) => $q->whereHas('incidentRecord', fn ($q) => $q->where('incident_type', $incidentType))
            )
            ->when(
                $alarmLevel !== 'all',
                fn ($q) => $q->whereHas('incidentRecord', fn ($q) => $q->where('alarm_level', $alarmLevel))
            )
            ->with('incidentRecord.barangay')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (CommunityReport $report) => [
                'report_id' => $report->report_id,
                'reference' => sprintf('INC-%s-%04d', $report->created_at->format('Y'), $report->report_id),
                'type' => $report->incidentRecord?->incident_type,
                'typeLabel' => AnalyticsController::TYPE_LABELS[$report->incidentRecord?->incident_type ?? ''] ?? null,
                'alarm_level' => $report->incidentRecord?->alarm_level?->value ?? $report->incidentRecord?->alarm_level,
                'barangay' => $report->incidentRecord?->barangay?->barangay_name,
                'latitude' => (float) $report->latitude,
                'longitude' => (float) $report->longitude,
                'dateTime' => $report->created_at->format('Y-m-d H:i'),
            ]);

        return Inertia::render('map/index', [
            'incidents' => $incidents,
            'barangayRisk' => BarangayRiskSnapshot::all(),
            'periodLabel' => DateRange::label($period, $rangeStart, $rangeEnd, $now),
            'filters' => [
                'period' => $period,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'incident_type' => $incidentType,
                'alarm_level' => $alarmLevel,
            ],
        ]);
    }
}
