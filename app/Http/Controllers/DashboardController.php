<?php

namespace App\Http\Controllers;

use App\Models\CommunityReport;
use App\Models\IncidentRecord;
use App\Support\BarangayRiskSnapshot;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Date;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
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
    private function kpis(CarbonInterface $today): array
    {
        $reportsToday = CommunityReport::whereDate('created_at', $today)->count();
        $reportsYesterday = CommunityReport::whereDate('created_at', $today->copy()->subDay())->count();

        $pending = CommunityReport::where('status', CommunityReport::STATUS_PENDING)->count();

        $activeStatuses = [CommunityReport::STATUS_VERIFIED, CommunityReport::STATUS_DISPATCHED];
        $activeCount = CommunityReport::whereIn('status', $activeStatuses)->count();
        $dispatchedCount = CommunityReport::where('status', CommunityReport::STATUS_DISPATCHED)->count();
        $criticalActiveCount = IncidentRecord::where('severity_level', 'critical')
            ->whereHas('report', fn ($q) => $q->whereIn('status', $activeStatuses))
            ->count();

        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();
        $totalThisWeek = CommunityReport::whereBetween('created_at', [$weekStart, $weekEnd])->count();
        $resolvedThisWeek = CommunityReport::where('status', CommunityReport::STATUS_RESOLVED)
            ->whereBetween('created_at', [$weekStart, $weekEnd])->count();
        $resolutionRate = $totalThisWeek > 0 ? (int) round($resolvedThisWeek / $totalThisWeek * 100) : 0;

        return [
            'reportsToday' => $reportsToday,
            'reportsDeltaFromYesterday' => $reportsToday - $reportsYesterday,
            'pendingVerification' => $pending,
            'activeIncidents' => $activeCount,
            'criticalActiveCount' => $criticalActiveCount,
            'dispatchedCount' => $dispatchedCount,
            'resolvedThisWeek' => $resolvedThisWeek,
            'resolutionRate' => $resolutionRate,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function recentIncidents(): array
    {
        return IncidentRecord::with(['report', 'barangay'])
            ->orderByDesc('data_time')
            ->limit(5)
            ->get()
            ->map(fn (IncidentRecord $incident) => [
                'report_id' => $incident->report_id,
                'reference' => sprintf('INC-%s-%04d', $incident->data_time->format('Y'), $incident->report_id),
                'barangay' => $incident->barangay->barangay_name,
                'type' => ucfirst($incident->incident_type),
                'status' => $incident->report->status,
                'dateTime' => $incident->data_time->format('Y-m-d H:i'),
            ])
            ->all();
    }

    /**
     * Incident volume vs. resolved count for the last 7 calendar months.
     *
     * @return array<int, array<string, mixed>>
     */
    private function monthlyTrend(): array
    {
        return collect(range(6, 0))
            ->map(function (int $monthsAgo) {
                $start = Date::now()->subMonths($monthsAgo)->startOfMonth();
                $end = $start->copy()->endOfMonth();

                $total = IncidentRecord::whereBetween('data_time', [$start, $end])->count();
                $resolved = IncidentRecord::whereBetween('data_time', [$start, $end])
                    ->whereHas('report', fn ($q) => $q->where('status', CommunityReport::STATUS_RESOLVED))
                    ->count();

                return [
                    'month' => $start->format('M'),
                    'incidents' => $total,
                    'resolved' => $resolved,
                ];
            })
            ->all();
    }
}
