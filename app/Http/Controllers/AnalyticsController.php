<?php

namespace App\Http\Controllers;

use App\Models\CommunityReport;
use App\Models\IncidentRecord;
use Illuminate\Support\Facades\Date;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    /** @var array<string, string> */
    private const TYPE_LABELS = [
        'structural' => 'Structure Fire',
        'grass' => 'Grass/Vegetation',
        'vehicular' => 'Vehicle Fire',
        'electrical' => 'Electrical Fire',
        'other' => 'Other',
    ];

    /** @var array<string, string> */
    private const TYPE_COLORS = [
        'structural' => '#E63946',
        'grass' => '#F77F00',
        'vehicular' => '#F4A261',
        'electrical' => '#457B9D',
        'other' => '#868E96',
    ];

    /** @var array<string, string> */
    private const SEVERITY_COLORS = [
        'critical' => '#E63946',
        'high' => '#F77F00',
        'moderate' => '#F4A261',
        'low' => '#2A9D8F',
    ];

    public function index(): Response
    {
        return Inertia::render('analytics/index', [
            'incidentsByType' => $this->incidentsByType(),
            'monthlyTrend' => $this->monthlyTrend(),
            'incidentsBySeverity' => $this->incidentsBySeverity(),
            'responseTimeTrend' => $this->responseTimeTrend(),
            'periodLabel' => $this->periodLabel(),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function incidentsByType(): array
    {
        $total = IncidentRecord::count();

        return IncidentRecord::query()
            ->selectRaw('incident_type, count(*) as total')
            ->groupBy('incident_type')
            ->pluck('total', 'incident_type')
            ->sortDesc()
            ->map(fn (int $count, string $type) => [
                'type' => $type,
                'label' => self::TYPE_LABELS[$type] ?? ucfirst($type),
                'count' => $count,
                'percentage' => $total > 0 ? (int) round($count / $total * 100) : 0,
                'color' => self::TYPE_COLORS[$type] ?? '#868E96',
            ])
            ->values()
            ->all();
    }

    /**
     * Incident volume vs. resolved count for each month of the current
     * year, up to and including the current month.
     *
     * @return array<int, array<string, mixed>>
     */
    private function monthlyTrend(): array
    {
        $now = Date::now();

        return collect(range(1, $now->month))
            ->map(function (int $month) use ($now) {
                $start = $now->copy()->month($month)->startOfMonth();
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

    /**
     * @return array<int, array<string, mixed>>
     */
    private function incidentsBySeverity(): array
    {
        $counts = IncidentRecord::query()
            ->selectRaw('severity_level, count(*) as total')
            ->groupBy('severity_level')
            ->pluck('total', 'severity_level');

        return collect(['critical', 'high', 'moderate', 'low'])
            ->map(fn (string $level) => [
                'level' => $level,
                'label' => ucfirst($level),
                'count' => (int) ($counts[$level] ?? 0),
                'color' => self::SEVERITY_COLORS[$level],
            ])
            ->all();
    }

    /**
     * Static stand-in trend — the schema doesn't yet capture dispatch /
     * on-scene timestamps needed to compute a real response time, so this
     * mirrors the static stand-in pattern used on the Response Tracking
     * route (see routes/web.php) until that instrumentation exists.
     *
     * @return array<int, array<string, mixed>>
     */
    private function responseTimeTrend(): array
    {
        $now = Date::now();
        $minutesByMonth = [12.4, 10.8, 13.9, 11.2, 15.1, 13.6, 12.0, 11.5, 13.2, 12.7, 11.9, 12.3];

        return collect(range(1, $now->month))
            ->map(fn (int $month) => [
                'month' => $now->copy()->month($month)->format('M'),
                'minutes' => $minutesByMonth[$month - 1],
            ])
            ->all();
    }

    private function periodLabel(): string
    {
        $now = Date::now();

        return $now->month === 1
            ? $now->format('F Y')
            : $now->copy()->startOfYear()->format('M').' – '.$now->format('M Y');
    }
}
