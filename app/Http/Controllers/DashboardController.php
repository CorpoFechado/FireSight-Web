<?php

namespace App\Http\Controllers;

use App\Enums\AlarmLevel;
use App\Models\CommunityReport;
use App\Models\IncidentRecord;
use App\Support\BarangayRiskSnapshot;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Date;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * A report counts as "resolved" for KPI purposes once the fire is out,
     * whether or not the post-incident assessment (Complete step) has
     * happened yet.
     */
    private const RESOLVED_STATUSES = [
        CommunityReport::STATUS_RESOLVED,
    ];

    public function index(): Response
    {
        $today = Date::today();

        return Inertia::render('dashboard', [
            'kpis' => $this->kpis($today),
            'recentIncidents' => $this->recentIncidents(),
            'barangayRisk' => BarangayRiskSnapshot::all(),
            'monthlyTrend' => $this->monthlyTrend(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function kpis(CarbonInterface $today, ?CarbonInterface $now = null): array
    {
        $now = $now ?? Date::now();

        $reportsToday = CommunityReport::whereDate('created_at', $today)->count();
        $reportsYesterday = CommunityReport::whereDate('created_at', $today->copy()->subDay())->count();

        $pending = CommunityReport::where('status', CommunityReport::STATUS_PENDING)->count();
        $oldestPending = CommunityReport::where('status', CommunityReport::STATUS_PENDING)
            ->oldest('created_at')
            ->first();

        if ($oldestPending) {
            $hours = (int) $oldestPending->created_at->diffInHours($now);
            $pendingComparison = $hours < 1
                ? 'Oldest waiting <1h'
                : "Oldest waiting {$hours}h";
        } else {
            $pendingComparison = 'None waiting';
        }

        $activeStatuses = [CommunityReport::STATUS_ACCEPTED, CommunityReport::STATUS_DISPATCHED];
        $activeCount = CommunityReport::whereIn('status', $activeStatuses)->count();
        $activeYesterday = $this->activeIncidentsAt($now->copy()->subDay());
        $activeDelta = $activeCount - $activeYesterday;
        $activeComparison = ($activeDelta > 0 ? "+{$activeDelta}" : (string) $activeDelta).' since yesterday';

        $dispatchedCount = CommunityReport::where('status', CommunityReport::STATUS_DISPATCHED)->count();
        $criticalActiveCount = IncidentRecord::whereIn('alarm_level', AlarmLevel::criticalValues())
            ->whereHas('report', fn ($q) => $q->whereIn('status', $activeStatuses))
            ->count();

        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();
        $totalThisWeek = CommunityReport::whereBetween('created_at', [$weekStart, $weekEnd])->count();
        $resolvedThisWeek = CommunityReport::whereIn('status', self::RESOLVED_STATUSES)
            ->whereBetween('created_at', [$weekStart, $weekEnd])->count();
        $resolutionRate = $totalThisWeek > 0 ? (int) round($resolvedThisWeek / $totalThisWeek * 100) : 0;

        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();
        $yesterday = $today->copy()->subDay();

        $resolvedToday = CommunityReport::whereIn('status', self::RESOLVED_STATUSES)
            ->where(function ($query) use ($today) {
                $query->whereHas('statusHistory', fn ($q) => $q->where('status', CommunityReport::STATUS_RESOLVED)->whereDate('created_at', $today))
                    ->orWhere(function ($q) use ($today) {
                        $q->whereDoesntHave('statusHistory', fn ($sh) => $sh->where('status', CommunityReport::STATUS_RESOLVED))
                            ->where(fn ($sub) => $sub->whereDate('updated_at', $today)->orWhere(fn ($sq) => $sq->whereNull('updated_at')->whereDate('created_at', $today)));
                    });
            })
            ->count();

        $resolvedYesterday = CommunityReport::whereIn('status', self::RESOLVED_STATUSES)
            ->where(function ($query) use ($yesterday) {
                $query->whereHas('statusHistory', fn ($q) => $q->where('status', CommunityReport::STATUS_RESOLVED)->whereDate('created_at', $yesterday))
                    ->orWhere(function ($q) use ($yesterday) {
                        $q->whereDoesntHave('statusHistory', fn ($sh) => $sh->where('status', CommunityReport::STATUS_RESOLVED))
                            ->where(fn ($sub) => $sub->whereDate('updated_at', $yesterday)->orWhere(fn ($sq) => $sq->whereNull('updated_at')->whereDate('created_at', $yesterday)));
                    });
            })
            ->count();

        $resolvedTodayDelta = $resolvedToday - $resolvedYesterday;
        $resolvedTodayComparison = ($resolvedTodayDelta > 0 ? "+{$resolvedTodayDelta}" : (string) $resolvedTodayDelta).' from yesterday';

        $resolvedThisMonth = CommunityReport::whereIn('status', self::RESOLVED_STATUSES)
            ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereHas('statusHistory', fn ($q) => $q->where('status', CommunityReport::STATUS_RESOLVED)->whereBetween('created_at', [$startOfMonth, $endOfMonth]))
                    ->orWhere(function ($q) use ($startOfMonth, $endOfMonth) {
                        $q->whereDoesntHave('statusHistory', fn ($sh) => $sh->where('status', CommunityReport::STATUS_RESOLVED))
                            ->where(fn ($sub) => $sub->whereBetween('updated_at', [$startOfMonth, $endOfMonth])->orWhere(fn ($sq) => $sq->whereNull('updated_at')->whereBetween('created_at', [$startOfMonth, $endOfMonth])));
                    });
            })
            ->count();

        $startOfLastMonth = $today->copy()->subMonthNoOverflow()->startOfMonth();
        $endOfSamePeriodLastMonth = $today->copy()->subMonthNoOverflow()->endOfDay();

        $resolvedLastMonthSamePeriod = CommunityReport::whereIn('status', self::RESOLVED_STATUSES)
            ->where(function ($query) use ($startOfLastMonth, $endOfSamePeriodLastMonth) {
                $query->whereHas('statusHistory', fn ($q) => $q->where('status', CommunityReport::STATUS_RESOLVED)->whereBetween('created_at', [$startOfLastMonth, $endOfSamePeriodLastMonth]))
                    ->orWhere(function ($q) use ($startOfLastMonth, $endOfSamePeriodLastMonth) {
                        $q->whereDoesntHave('statusHistory', fn ($sh) => $sh->where('status', CommunityReport::STATUS_RESOLVED))
                            ->where(fn ($sub) => $sub->whereBetween('updated_at', [$startOfLastMonth, $endOfSamePeriodLastMonth])->orWhere(fn ($sq) => $sq->whereNull('updated_at')->whereBetween('created_at', [$startOfLastMonth, $endOfSamePeriodLastMonth])));
                    });
            })
            ->count();

        $resolvedThisMonthDelta = $resolvedThisMonth - $resolvedLastMonthSamePeriod;
        $resolvedThisMonthComparison = ($resolvedThisMonthDelta > 0 ? "+{$resolvedThisMonthDelta}" : (string) $resolvedThisMonthDelta).' vs same period last month';

        return [
            'activeIncidents' => $activeCount,
            'activeComparison' => $activeComparison,
            'resolvedToday' => $resolvedToday,
            'resolvedTodayComparison' => $resolvedTodayComparison,
            'resolvedThisMonth' => $resolvedThisMonth,
            'resolvedThisMonthComparison' => $resolvedThisMonthComparison,
            'pendingVerification' => $pending,
            'pendingComparison' => $pendingComparison,
            'reportsToday' => $reportsToday,
            'reportsDeltaFromYesterday' => $reportsToday - $reportsYesterday,
            'criticalActiveCount' => $criticalActiveCount,
            'dispatchedCount' => $dispatchedCount,
            'resolvedThisWeek' => $resolvedThisWeek,
            'resolutionRate' => $resolutionRate,
        ];
    }

    /**
     * Count active incidents at a specific point in time based on status history.
     */
    private function activeIncidentsAt(CarbonInterface $pointInTime): int
    {
        $activeStatuses = [CommunityReport::STATUS_ACCEPTED, CommunityReport::STATUS_DISPATCHED];

        return CommunityReport::where('created_at', '<=', $pointInTime)
            ->where(function ($query) use ($pointInTime, $activeStatuses) {
                $query->whereHas('statusHistory', function ($q) use ($pointInTime, $activeStatuses) {
                    $q->where('created_at', '<=', $pointInTime)
                        ->whereIn('status', $activeStatuses)
                        ->whereRaw('history_id = (
                            SELECT h2.history_id
                            FROM report_status_history h2
                            WHERE h2.report_id = report_status_history.report_id
                              AND h2.created_at <= ?
                            ORDER BY h2.created_at DESC, h2.history_id DESC
                            LIMIT 1
                        )', [$pointInTime]);
                })
                    ->orWhere(function ($q) use ($activeStatuses) {
                        $q->whereDoesntHave('statusHistory')
                            ->whereIn('status', $activeStatuses);
                    });
            })
            ->count();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function recentIncidents(): array
    {
        return CommunityReport::with(['barangay', 'incidentRecord.barangay'])
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn (CommunityReport $report) => [
                'report_id' => $report->report_id,
                'reference' => sprintf('INC-%s-%04d', $report->created_at->format('Y'), $report->report_id),
                'barangay' => $report->barangay?->barangay_name ?? $report->incidentRecord?->barangay?->barangay_name ?? 'Not assigned',
                'type' => $report->incidentRecord?->incident_type
                    ? AnalyticsController::TYPE_LABELS[$report->incidentRecord->incident_type] ?? ucfirst(str_replace('_', ' ', $report->incidentRecord->incident_type))
                    : 'Unclassified',
                'status' => $report->status,
                'dateTime' => $report->created_at->format('Y-m-d H:i'),
            ])
            ->all();
    }

    /**
     * Incident volume for the last 7 calendar months.
     *
     * @return array<int, array<string, mixed>>
     */
    private function monthlyTrend(): array
    {
        return collect(range(6, 0))
            ->map(function (int $monthsAgo) {
                $start = Date::now()->subMonths($monthsAgo)->startOfMonth();
                $end = $start->copy()->endOfMonth();

                $total = IncidentRecord::whereBetween('incident_datetime', [$start, $end])->count();

                return [
                    'month' => $start->format('M'),
                    'incidents' => $total,
                ];
            })
            ->all();
    }
}
