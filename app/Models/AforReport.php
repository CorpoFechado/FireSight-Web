<?php

namespace App\Models;

use App\Enums\AlarmLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $afor_id
 * @property int $incident_id
 * @property Carbon|null $alarm_received_time
 * @property string|null $location_description
 * @property string|null $caller_name
 * @property string|null $caller_office_address
 * @property string|null $receiving_personnel
 * @property array<int, array{
 *     engine_name?: string,
 *     time_dispatched?: string,
 *     time_arrived?: string,
 *     response_time_minutes?: int|float,
 *     time_returned_to_base?: string,
 *     water_tank_refilled_gal?: int|float,
 *     gas_consumed_l?: int|float
 * }>|null $engine_responses
 * @property string|null $responder_type
 * @property Carbon|null $time_under_control
 * @property Carbon|null $time_fire_out
 * @property string|null $occupancy_type
 * @property string|null $occupancy_type_detail
 * @property string|null $distance_from_station_km
 * @property string|null $structure_description
 * @property int $civilian_injured
 * @property int $civilian_death
 * @property int $firefighter_injured
 * @property int $firefighter_death
 * @property array<int, array{type_kind?: string}>|null $breathing_apparatus
 * @property AlarmLevel|null $alarm_level
 * @property array<int, array{level?: string, time?: string, ground_commander?: string}>|null $alarm_declarations
 * @property array<int, array{qty?: int|string, type_kind?: string}>|null $extinguishing_agents
 * @property array<int, array{type?: string, length?: string|int}>|null $ropes_ladders
 * @property array<int, array{nr?: int|string, type_kind?: string, total_ft?: int|float}>|null $hose_lines
 * @property array<int, array{rank_name?: string, designation?: string, remarks?: string}>|null $duty_personnel
 * @property string|null $sketch_path
 * @property string|null $narrative
 * @property string|null $problems_encountered
 * @property string|null $observations_recommendations
 * @property string|null $prepared_by_name
 * @property string|null $noted_by_name
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read IncidentRecord $incidentRecord
 * @property-read User|null $creator
 * @property-read User|null $editor
 */
class AforReport extends Model
{
    protected $table = 'afor_report';

    protected $primaryKey = 'afor_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'incident_id',
        'alarm_received_time',
        'location_description',
        'caller_name',
        'caller_office_address',
        'receiving_personnel',
        'engine_responses',
        'responder_type',
        'time_under_control',
        'time_fire_out',
        'occupancy_type',
        'occupancy_type_detail',
        'distance_from_station_km',
        'structure_description',
        'civilian_injured',
        'civilian_death',
        'firefighter_injured',
        'firefighter_death',
        'breathing_apparatus',
        'alarm_level',
        'alarm_declarations',
        'extinguishing_agents',
        'ropes_ladders',
        'hose_lines',
        'duty_personnel',
        'sketch_path',
        'narrative',
        'problems_encountered',
        'observations_recommendations',
        'prepared_by_name',
        'noted_by_name',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'alarm_received_time' => 'datetime:H:i:s',
            'engine_responses' => 'array',
            'time_under_control' => 'datetime',
            'time_fire_out' => 'datetime',
            'distance_from_station_km' => 'decimal:2',
            'civilian_injured' => 'integer',
            'civilian_death' => 'integer',
            'firefighter_injured' => 'integer',
            'firefighter_death' => 'integer',
            'breathing_apparatus' => 'array',
            'alarm_level' => AlarmLevel::class,
            'alarm_declarations' => 'array',
            'extinguishing_agents' => 'array',
            'ropes_ladders' => 'array',
            'hose_lines' => 'array',
            'duty_personnel' => 'array',
        ];
    }

    /**
     * The incident record this AFOR belongs to.
     */
    public function incidentRecord(): BelongsTo
    {
        return $this->belongsTo(IncidentRecord::class, 'incident_id', 'incident_id');
    }

    /**
     * The user who digitally created this AFOR.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The user who last digitally edited this AFOR.
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
