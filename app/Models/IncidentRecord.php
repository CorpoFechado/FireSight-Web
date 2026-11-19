<?php

namespace App\Models;

use App\Enums\AlarmLevel;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $incident_id
 * @property int $report_id
 * @property string|null $incident_type
 * @property CarbonInterface|null $incident_datetime
 */
class IncidentRecord extends Model
{
    /** @var array<string, string> */
    public const LEGACY_TYPE_MAP = [
        'structural' => 'residential_fire',
        'grass' => 'rubbish_fire',
        'electrical' => 'rubbish_fire',
        'vehicular' => 'vehicular_fire',
        'other' => 'others',
    ];

    protected $table = 'incident_record';

    protected $primaryKey = 'incident_id';

    public $timestamps = false;

    protected $fillable = [
        'report_id', 'barangay_id', 'incident_datetime', 'incident_type',
        'alarm_level', 'cause_of_fire', 'casualties', 'notes',
        'establishment_name', 'owner_name', 'occupant_name',
        'time_fire_started', 'time_fire_out', 'injuries', 'fatalities',
        'estimated_damage',
    ];

    protected function casts(): array
    {
        return [
            'incident_datetime' => 'datetime',
            'time_fire_started' => 'datetime',
            'time_fire_out' => 'datetime',
            'injuries' => 'integer',
            'fatalities' => 'integer',
            'estimated_damage' => 'decimal:2',
            'alarm_level' => AlarmLevel::class,
        ];
    }

    // ── Backward-compatibility accessors for code that still uses `data_time` ──

    /**
     * Allows existing code reading `$incident->data_time` to keep working
     * without modification while the DB column is named `incident_datetime`.
     */
    public function getDataTimeAttribute(): mixed
    {
        return $this->incident_datetime;
    }

    /**
     * Allows existing code writing `data_time` (e.g. `IncidentRecord::create(['data_time' => now()])`)
     * to keep working — writes through to the real column.
     */
    public function setDataTimeAttribute(mixed $value): void
    {
        $this->attributes['incident_datetime'] = $value;
    }

    /**
     * Returns the canonical incident type.
     * Legacy web types are mapped to the mobile-standard set:
     * residential_fire | commercial_fire | vehicular_fire | storage_fire | rubbish_fire | others
     */
    public function getNormalizedTypeAttribute(): ?string
    {
        return self::LEGACY_TYPE_MAP[$this->incident_type] ?? $this->incident_type;
    }

    // ── Spot Investigation Report ─────────────────────────────────────────────

    /**
     * Incident types where the establishment, owner and occupant must be
     * recorded before a Spot Investigation Report can be exported.
     *
     * @var list<string>
     */
    public const SPOT_REPORT_STRUCTURE_TYPES = ['residential_fire', 'commercial_fire', 'storage_fire'];

    /**
     * Human labels of the assessment fields still required for the SIR export.
     *
     * @return list<string>
     */
    public function missingSpotReportFields(): array
    {
        /** @var array<string, string> $required */
        $required = [
            'incident_type' => 'Incident type',
            'alarm_level' => 'Alarm level',
            'time_fire_started' => 'Time fire started',
            'time_fire_out' => 'Time of fire out',
            'estimated_damage' => 'Estimated damage',
            'cause_of_fire' => 'Cause of fire',
            'notes' => 'Details / notes',
        ];

        if (in_array($this->normalized_type, self::SPOT_REPORT_STRUCTURE_TYPES, true)) {
            $required += [
                'establishment_name' => 'Name of establishment',
                'owner_name' => 'Owner',
                'occupant_name' => 'Occupant',
            ];
        }

        $missing = [];

        foreach ($required as $attribute => $label) {
            $value = $this->getAttribute($attribute);

            if ($value === null || (is_string($value) && trim($value) === '')) {
                $missing[] = $label;
            }
        }

        return $missing;
    }

    public function isSpotReportReady(): bool
    {
        return $this->missingSpotReportFields() === [];
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    /** @return BelongsTo<CommunityReport, $this> */
    public function report(): BelongsTo
    {
        return $this->belongsTo(CommunityReport::class, 'report_id');
    }

    /** @return BelongsTo<Barangay, $this> */
    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }

    /** @return HasMany<ReportEvidence, $this> */
    public function evidence(): HasMany
    {
        return $this->hasMany(ReportEvidence::class, 'report_id', 'report_id');
    }
}
