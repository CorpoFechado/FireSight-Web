<?php

namespace App\Models;

use App\Observers\CommunityReportObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

#[ObservedBy(CommunityReportObserver::class)]
class CommunityReport extends Model
{
    // ── Canonical workflow statuses ──────────────────────────────────────────

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_DISPATCHED = 'dispatched';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_INVALID = 'invalid';

    protected $table = 'community_report';

    protected $primaryKey = 'report_id';

    protected $fillable = [
        'user_id', 'reporter_name', 'contact_number', 'description',
        'report_image', 'latitude', 'longitude', 'status',
        'barangay_id', 'location_accuracy_m', 'device_latitude', 'device_longitude',
        'ai_fire_label', 'ai_fire_confidence', 'ai_verified_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'device_latitude' => 'decimal:8',
            'device_longitude' => 'decimal:8',
            'ai_fire_confidence' => 'float',
            'ai_verified_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }

    public function incidentRecord(): HasOne
    {
        return $this->hasOne(IncidentRecord::class, 'report_id');
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(ReportEvidence::class, 'report_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(ReportStatusHistory::class, 'report_id');
    }

    public function dispatches(): HasMany
    {
        return $this->hasMany(IncidentDispatch::class, 'report_id', 'report_id');
    }

    /**
     * Get all report IDs that belong to the same duplicate cluster (connected graph)
     * as this report, including the report itself.
     *
     * @return array<int>
     */
    public static function getClusterReportIds(int $reportId): array
    {
        $cluster = [$reportId];
        $added = true;

        while ($added) {
            $newNeighbors = DB::table('report_link')
                ->where(function ($q) use ($cluster) {
                    $q->whereIn('main_report_id', $cluster)
                        ->orWhereIn('related_report_id', $cluster);
                })
                ->get()
                ->flatMap(fn ($row) => [(int) $row->main_report_id, (int) $row->related_report_id])
                ->unique()
                ->diff($cluster)
                ->values()
                ->all();

            if (! empty($newNeighbors)) {
                $cluster = array_values(array_unique(array_merge($cluster, $newNeighbors)));
            } else {
                $added = false;
            }
        }

        return $cluster;
    }

    /**
     * Get all other report IDs linked to this report within the duplicate cluster.
     *
     * @return array<int>
     */
    public function allLinkedReportIds(): array
    {
        return array_values(array_filter(
            self::getClusterReportIds($this->report_id),
            fn ($id) => $id !== (int) $this->report_id
        ));
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
