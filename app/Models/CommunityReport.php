<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CommunityReport extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_DISPATCHED = 'dispatched';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_COMPLETED = 'completed';

    protected $table = 'community_report';

    protected $primaryKey = 'report_id';

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'reporter_name', 'contact_number', 'description',
        'report_image', 'latitude', 'longitude', 'status',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'created_at' => 'datetime',
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function incidentRecord(): HasOne
    {
        return $this->hasOne(IncidentRecord::class, 'report_id');
    }

    /**
     * Other community reports linked to this one via `report_link`
     * (e.g. duplicate reports of the same incident).
     */
    public function linkedReports(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'report_link',
            'main_report_id',
            'related_report_id',
        );
    }

    /**
     * The inverse side of `linkedReports()` — reports that named *this*
     * report as their related report. `report_link` is directional, so a
     * pair only shows up on one side unless both are checked.
     */
    public function linkedFromReports(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'report_link',
            'related_report_id',
            'main_report_id',
        );
    }
}
