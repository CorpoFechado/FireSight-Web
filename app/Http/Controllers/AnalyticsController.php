<?php

namespace App\Http\Controllers;

use App\Enums\AlarmLevel;
use App\Models\Barangay;
use App\Models\CommunityReport;
use App\Models\IncidentRecord;
use App\Support\DateRange;
use App\Support\IncidentTypeBreakdown;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    /** @var array<string, string> */
    public const TYPE_LABELS = IncidentTypeBreakdown::TYPE_LABELS;

    /** @var array<string, string> */
    public const TYPE_COLORS = IncidentTypeBreakdown::TYPE_COLORS;

    public function index(Request $request): Response
    {
        $input = $request->validate([
            'period' => ['nullable', Rule::in(['all_time', 'this_month', 'last_3_months', 'this_year', 'custom'])],
            'date_from' => ['required_if:period,custom', 'nullable', 'date_format:Y-m-d'],
            'date_to' => ['required_if:period,custom', 'nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'incident_type' => ['nullable', Rule::in([...array_keys(self::TYPE_LABELS), 'unclassified'])],
            'barangay_id' => ['nullable', 'integer', 'exists:barangay,barangay_id'],
            'incident_page' => ['nullable', 'integer', 'min:1'],
            'supporting_view' => ['nullable', Rule::in(['barangay', 'alarm'])],
            'supporting_type' => ['nullable', Rule::in([...array_keys(self::TYPE_LABELS), 'unclassified'])],
            'supporting_alarm_level' => ['nullable', Rule::in([...array_column(AlarmLevel::cases(), 'value'), 'unassessed'])],
        ], [
            'date_to.after_or_equal' => 'The end date must be on or after the start date.',
        ]);

        $now = Date::now();
        $period = $input['period'] ?? 'all_time';
        $type = $input['incident_type'] ?? $input['supporting_type'] ?? '';
        [$start, $end] = DateRange::resolve($period, $input['date_from'] ?? '', $input['date_to'] ?? '', $now);
        if ($period === 'all_time') {
            $bounds = IncidentRecord::query()
                ->selectRaw('MIN(incident_datetime) as earliest, MAX(incident_datetime) as latest')
                ->first();
            $start = Date::parse($bounds?->earliest ?? $now)->startOfYear();
            $end = Date::parse($bounds?->latest ?? $now)->max($now)->endOfDay();
        }

        $filters = [
            'period' => $period,
            'date_from' => $period === 'custom' ? $start->format('Y-m-d') : '',
            'date_to' => $period === 'custom' ? $end->format('Y-m-d') : '',
            'incident_type' => $type,
            'barangay_id' => isset($input['barangay_id']) ? (int) $input['barangay_id'] : null,
            'supporting_view' => $input['supporting_view'] ?? 'barangay',
            'supporting_type' => $input['supporting_type'] ?? '',
            'supporting_alarm_level' => $input['supporting_alarm_level'] ?? '',
        ];

        $allTypesQuery = IncidentRecord::query();
        if ($period !== 'all_time') {
            $allTypesQuery->whereBetween('incident_datetime', [$start, $end]);
        }
        $query = $this->filterType(clone $allTypesQuery, $type);

        $counts = (clone $query)
            ->select('barangay_id', 'incident_type')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('barangay_id', 'incident_type')
            ->get();
        $barangays = Barangay::query()->orderBy('barangay_name')->get(['barangay_id', 'barangay_name']);
        $countsByBarangay = $counts->groupBy('barangay_id');
        $overview = $barangays->map(function (Barangay $barangay) use ($countsByBarangay): array {
            $breakdown = $this->typeBreakdown($countsByBarangay->get($barangay->barangay_id, collect()));
            $classified = collect($breakdown)->where('type', '!=', 'unclassified');
            $highest = $classified->max('count');

            return [
                'barangay_id' => $barangay->barangay_id,
                'barangay_name' => $barangay->barangay_name,
                'count' => (int) collect($breakdown)->sum('count'),
                'leading_types' => $classified->where('count', $highest)->pluck('label')->values()->all(),
            ];
        })->sortBy([
            ['count', 'desc'],
            ['barangay_name', 'asc'],
        ])->values();

        $selected = $barangays->firstWhere('barangay_id', $filters['barangay_id']);
        $contextQuery = clone $query;
        $selectedBarangay = null;
        $supportingIncidents = null;

        if ($selected !== null) {
            $contextQuery->where('barangay_id', $selected->barangay_id);
            $impact = (clone $contextQuery)
                ->selectRaw('COUNT(*) as total, COALESCE(SUM(injuries), 0) as injuries, COALESCE(SUM(fatalities), 0) as fatalities')
                ->selectRaw('SUM(estimated_damage) as estimated_damage, COUNT(estimated_damage) as damage_recorded')
                ->first();

            $selectedBarangay = [
                'barangay_id' => $selected->barangay_id,
                'barangay_name' => $selected->barangay_name,
                'count' => (int) $impact->getAttribute('total'),
                'injuries' => (int) $impact->getAttribute('injuries'),
                'fatalities' => (int) $impact->getAttribute('fatalities'),
                'estimated_damage' => $impact->estimated_damage !== null ? (float) $impact->estimated_damage : null,
                'damage_recorded' => (int) $impact->getAttribute('damage_recorded'),
                'types' => $this->typeBreakdown((clone $allTypesQuery)->where('barangay_id', $selected->barangay_id)
                    ->select('incident_type')->selectRaw('COUNT(*) as total')->groupBy('incident_type')->get()),
            ];
        }

        if ($selected !== null || $filters['supporting_view'] === 'alarm') {
            $supportingQuery = $this->filterType(clone $contextQuery, $filters['supporting_type']);
            $supportingQuery = $this->filterAlarmLevel($supportingQuery, $filters['supporting_alarm_level']);

            $supportingIncidents = $supportingQuery
                ->with('report:report_id,created_at')
                ->orderByDesc('incident_datetime')
                ->orderByDesc('incident_id')
                ->paginate(10, ['incident_id', 'report_id', 'incident_datetime', 'incident_type', 'cause_of_fire', 'alarm_level'], 'incident_page')
                ->appends($filters)
                ->through(function (IncidentRecord $incident): array {
                    /** @var CommunityReport $report */
                    $report = $incident->report;

                    return [
                        'incident_id' => $incident->incident_id,
                        'report_id' => $incident->report_id,
                        'reference' => IncidentController::reference($report),
                        'recorded_at' => $incident->incident_datetime?->format('M j, Y g:i A') ?? 'Date unavailable',
                        'type' => self::TYPE_LABELS[$incident->normalized_type] ?? $incident->normalized_type ?? 'Unclassified',
                        'cause' => $incident->cause_of_fire,
                        'alarm_level' => $incident->alarm_level?->label() ?? 'Awaiting assessment',
                    ];
                });
        }

        $alarmCounts = (clone $contextQuery)->whereNotNull('alarm_level')
            ->selectRaw('alarm_level, COUNT(*) as total')->groupBy('alarm_level')->pluck('total', 'alarm_level');

        return Inertia::render('analytics/index', [
            'summary' => [
                'total' => (int) $counts->sum('total'),
                'unclassified' => (int) $counts->whereNull('incident_type')->sum('total'),
                'unassigned' => (int) $counts->whereNotIn('barangay_id', $barangays->modelKeys())->sum('total'),
            ],
            'barangayOverview' => $overview,
            'selectedBarangay' => $selectedBarangay,
            'supportingIncidents' => $supportingIncidents,
            'monthlyTrend' => $this->monthlyTrend($this->filterAlarmLevel(clone $contextQuery, $filters['supporting_alarm_level']), $start, $end),
            'incidentsByAlarmLevel' => collect(AlarmLevel::cases())->map(fn (AlarmLevel $level): array => [
                'level' => $level->value,
                'label' => $level->label(),
                'count' => (int) ($alarmCounts[$level->value] ?? 0),
                'color' => $level->color(),
            ]),
            'typeOptions' => collect(self::TYPE_LABELS)->map(fn (string $label, string $value): array => compact('value', 'label'))->values(),
            'periodLabel' => $period === 'all_time' ? 'All time' : $start->format($start->year === $end->year ? 'M j' : 'M j, Y').' – '.$end->format('M j, Y'),
            'dateRange' => ['start' => $start->format('Y-m-d'), 'end' => $end->format('Y-m-d')],
            'filters' => $filters,
        ]);
    }

    /**
     * @param  Builder<IncidentRecord>  $query
     * @return Builder<IncidentRecord>
     */
    private function filterAlarmLevel(Builder $query, string $level): Builder
    {
        if ($level === 'unassessed') {
            return $query->whereNull('alarm_level');
        }

        return $level === '' ? $query : $query->where('alarm_level', $level);
    }

    /**
     * @param  Builder<IncidentRecord>  $query
     * @return Builder<IncidentRecord>
     */
    private function filterType(Builder $query, string $type): Builder
    {
        if ($type === 'unclassified') {
            return $query->whereNull('incident_type');
        }

        if ($type === '') {
            return $query;
        }

        return $query->whereIn('incident_type', [$type, ...array_keys(IncidentRecord::LEGACY_TYPE_MAP, $type, true)]);
    }

    /**
     * @param  Collection<int, IncidentRecord>  $counts
     * @return list<array{type: string, label: string, count: int, color: string}>
     */
    private function typeBreakdown(Collection $counts): array
    {
        return array_values($counts->groupBy(fn (IncidentRecord $row): string => $row->normalized_type ?? 'unclassified')
            ->map(fn (Collection $rows, string $type): array => [
                'type' => $type,
                'label' => $type === 'unclassified' ? 'Unclassified' : (self::TYPE_LABELS[$type] ?? $type),
                'count' => (int) $rows->sum('total'),
                'color' => self::TYPE_COLORS[$type] ?? '#868E96',
            ])->sortBy([['count', 'desc'], ['label', 'asc']])->all());
    }

    /**
     * @param  Builder<IncidentRecord>  $query
     * @return list<array{month: string, incidents: int}>
     */
    private function monthlyTrend(Builder $query, CarbonInterface $start, CarbonInterface $end): array
    {
        $counts = (clone $query)->selectRaw('SUBSTR(incident_datetime, 1, 7) as month, COUNT(*) as total')
            ->groupByRaw('SUBSTR(incident_datetime, 1, 7)')->pluck('total', 'month');
        $months = [];
        $month = $start->copy()->startOfMonth();

        while ($month->lessThanOrEqualTo($end)) {
            $months[] = [
                'month' => $month->format($start->year === $end->year ? 'M' : 'M Y'),
                'incidents' => (int) ($counts[$month->format('Y-m')] ?? 0),
            ];
            $month = $month->addMonth();
        }

        return $months;
    }
}
