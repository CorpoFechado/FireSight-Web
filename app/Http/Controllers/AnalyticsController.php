<?php

namespace App\Http\Controllers;

use App\Models\CommunityReport;
use App\Models\IncidentRecord;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    /**
     * A report counts as "resolved" for these charts once the fire is out,
     * whether or not the post-incident assessment (Complete step) has
     * happened yet.
     *
     * @var array<int, string>
     */
    private const RESOLVED_STATUSES = [
        CommunityReport::STATUS_RESOLVED,
        CommunityReport::STATUS_COMPLETED,
    ];

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

    public function index(Request $request): Response
    {
        $now = Date::now();

        // --- Global filter (snapshot cards) ---
        $period = $request->string('period')->lower()->value();
        $validPeriods = ['this_month', 'last_3_months', 'this_year', 'custom'];
        if (! in_array($period, $validPeriods, true)) {
            $period = 'this_year';
        }

        $dateFrom = $request->string('date_from')->trim()->value();
        $dateTo = $request->string('date_to')->trim()->value();

        [$rangeStart, $rangeEnd] = $this->resolveRange($period, $dateFrom, $dateTo, $now);

        // --- Trend chart control ---
        $trendYear = $request->integer('trend_year');
        if ($trendYear < 2000 || $trendYear > $now->year + 1) {
            $trendYear = $now->year;
        }

        return Inertia::render('analytics/index', [
            'incidentsByType' => $this->incidentsByType($rangeStart, $rangeEnd),
            'monthlyTrend' => $this->monthlyTrend($trendYear, $now),
            'incidentsBySeverity' => $this->incidentsBySeverity($rangeStart, $rangeEnd),
            'responseTimeTrend' => $this->responseTimeTrend(),
            'periodLabel' => $this->periodLabel($period, $rangeStart, $rangeEnd, $now),
            'filters' => [
                'period' => $period,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'trend_year' => $trendYear ?: $now->year,
            ],
        ]);
    }

    /**
     * Resolve a [start, end] Carbon pair from a period preset or custom dates.
     *
     * @return array{Carbon, Carbon}
     */
    private function resolveRange(
        string $period,
        string $dateFrom,
        string $dateTo,
        CarbonInterface $now,
    ): array {
        return match ($period) {
            'this_month' => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
            ],
            'last_3_months' => [
                $now->copy()->subMonths(2)->startOfMonth(),
                $now->copy()->endOfMonth(),
            ],
            'custom' => [
                $dateFrom !== '' ? Date::parse($dateFrom)->startOfDay() : $now->copy()->startOfYear(),
                $dateTo !== '' ? Date::parse($dateTo)->endOfDay() : $now->copy()->endOfDay(),
            ],
            default => [ // this_year
                $now->copy()->startOfYear(),
                $now->copy()->endOfDay(),
            ],
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function incidentsByType(
        CarbonInterface $start,
        CarbonInterface $end,
    ): array {
        // incident_type is only recorded once an incident is marked
        // Complete (post-assessment), so exclude the still-unassessed rows.
        $query = IncidentRecord::whereNotNull('incident_type')
            ->whereBetween('data_time', [$start, $end]);

        $total = (clone $query)->count();

        return (clone $query)
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
     * Incident volume vs. resolved count for each month of the selected year.
     * For the current year, only shows months up to today; for past years,
     * shows all 12 months.
     *
     * @return array<int, array<string, mixed>>
     */
    private function monthlyTrend(int $trendYear, CarbonInterface $now): array
    {
        $maxMonth = ($trendYear === $now->year) ? $now->month : 12;

        return collect(range(1, $maxMonth))
            ->map(function (int $month) use ($trendYear) {
                $start = Date::createFromDate($trendYear, $month, 1)->startOfMonth();
                $end = $start->copy()->endOfMonth();

                $total = IncidentRecord::whereBetween('data_time', [$start, $end])->count();
                $resolved = IncidentRecord::whereBetween('data_time', [$start, $end])
                    ->whereHas('report', fn ($q) => $q->whereIn('status', self::RESOLVED_STATUSES))
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
    private function incidentsBySeverity(
        CarbonInterface $start,
        CarbonInterface $end,
    ): array {
        // severity_level is only recorded once an incident is marked
        // Complete (post-assessment), so exclude the still-unassessed rows.
        $counts = IncidentRecord::query()
            ->whereNotNull('severity_level')
            ->whereBetween('data_time', [$start, $end])
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

    private function periodLabel(
        string $period,
        CarbonInterface $start,
        CarbonInterface $end,
        CarbonInterface $now,
    ): string {
        return match ($period) {
            'this_month' => $now->format('F Y'),
            'last_3_months' => $start->format('M Y').' – '.$end->format('M Y'),
            'custom' => $start->format('M j').' – '.$end->format('M j, Y'),
            default => $now->month === 1
                ? $now->format('F Y')
                : $start->format('M').' – '.$end->format('M Y'),
        };
    }
}
