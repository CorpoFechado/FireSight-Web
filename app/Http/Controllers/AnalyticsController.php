<?php

namespace App\Http\Controllers;

use App\Models\CommunityReport;
use App\Models\IncidentRecord;
use App\Support\DateRange;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
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
    ];

    /** @var array<string, string> */
    public const TYPE_LABELS = [
        'residential_fire' => 'Residential Fire',
        'commercial_fire' => 'Commercial Fire',
        'vehicular_fire' => 'Vehicular Fire',
        'storage_fire' => 'Storage Fire',
        'rubbish_fire' => 'Rubbish Fire',
        'others' => 'Others',
    ];

    /** @var array<string, string> */
    public const TYPE_COLORS = [
        'residential_fire' => '#E63946',
        'commercial_fire' => '#D62828',
        'vehicular_fire' => '#F4A261',
        'storage_fire' => '#7B2CBF',
        'rubbish_fire' => '#F77F00',
        'others' => '#457B9D',
    ];

    /** @var array<string, string> */
    public const SEVERITY_COLORS = [
        'critical' => '#DC2626',
        'high' => '#F97316',
        'moderate' => '#EAB308',
        'low' => '#16A34A',
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

        [$rangeStart, $rangeEnd] = DateRange::resolve($period, $dateFrom, $dateTo, $now);

        // --- Trend chart control ---
        $trendYear = $request->integer('trend_year');
        if ($trendYear < 2000 || $trendYear > $now->year + 1) {
            $trendYear = $now->year;
        }

        return Inertia::render('analytics/index', [
            'incidentsByType' => $this->incidentsByType($rangeStart, $rangeEnd),
            'monthlyTrend' => $this->monthlyTrend($trendYear, $now),
            'incidentsBySeverity' => $this->incidentsBySeverity($rangeStart, $rangeEnd),
            'barangaysWithMostIncidents' => $this->barangaysWithMostIncidents($rangeStart, $rangeEnd),
            'periodLabel' => DateRange::label($period, $rangeStart, $rangeEnd, $now),
            'filters' => [
                'period' => $period,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'trend_year' => $trendYear ?: $now->year,
            ],
        ]);
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
            ->whereBetween('incident_datetime', [$start, $end]);

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

                $total = IncidentRecord::whereBetween('incident_datetime', [$start, $end])->count();
                $resolved = IncidentRecord::whereBetween('incident_datetime', [$start, $end])
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
            ->whereBetween('incident_datetime', [$start, $end])
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
     * Top barangays by incident count for the snapshot period.
     *
     * @return array<int, array{barangay_id: int, barangay_name: string, count: int}>
     */
    private function barangaysWithMostIncidents(
        CarbonInterface $start,
        CarbonInterface $end,
        int $limit = 5,
    ): array {
        return IncidentRecord::query()
            ->join('barangay', 'incident_record.barangay_id', '=', 'barangay.barangay_id')
            ->whereBetween('incident_record.incident_datetime', [$start, $end])
            ->selectRaw('barangay.barangay_id, barangay.barangay_name, count(incident_record.incident_id) as count')
            ->groupBy('barangay.barangay_id', 'barangay.barangay_name')
            ->orderByDesc('count')
            ->orderBy('barangay.barangay_name')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'barangay_id' => (int) $row->barangay_id,
                'barangay_name' => (string) $row->barangay_name,
                'count' => (int) $row->count,
            ])
            ->all();
    }
}
