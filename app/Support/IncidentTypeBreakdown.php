<?php

namespace App\Support;

use App\Models\IncidentRecord;
use Carbon\CarbonInterface;

class IncidentTypeBreakdown
{
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
        'residential_fire' => '#1D3557',
        'commercial_fire' => '#1E4D5B',
        'vehicular_fire' => '#236B6E',
        'storage_fire' => '#2A9D8F',
        'rubbish_fire' => '#48B6A3',
        'others' => '#76CEBF',
    ];

    /**
     * @return array<int, array{type: string, label: string, count: int, percentage: int, color: string}>
     */
    public static function forRange(CarbonInterface $start, CarbonInterface $end): array
    {
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
}
