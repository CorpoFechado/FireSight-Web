<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentRecord extends Model
{
    protected $table = 'incident_record';

    protected $primaryKey = 'incident_id';

    public $timestamps = false;

    protected $fillable = [
        'report_id', 'barangay_id', 'data_time', 'incident_type',
        'severity_level', 'cause_of_fire', 'casualties', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'data_time' => 'datetime',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(CommunityReport::class, 'report_id');
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }
}
