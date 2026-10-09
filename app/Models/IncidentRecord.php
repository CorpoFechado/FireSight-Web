<?php

namespace App\Models;

use App\Enums\AlarmLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncidentRecord extends Model
{
    protected $table = 'incident_record';

    protected $primaryKey = 'incident_id';

    public $timestamps = false;

    protected $fillable = [
        'report_id', 'barangay_id', 'incident_datetime', 'incident_type',
        'alarm_level', 'cause_of_fire', 'casualties', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'incident_datetime' => 'datetime',
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
        return match ($this->incident_type) {
            'structural' => 'residential_fire',
            'grass', 'electrical' => 'rubbish_fire',
            'vehicular' => 'vehicular_fire',
            'other' => 'others',
            default => $this->incident_type,
        };
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function report(): BelongsTo
    {
        return $this->belongsTo(CommunityReport::class, 'report_id');
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(ReportEvidence::class, 'report_id', 'report_id');
    }
}
