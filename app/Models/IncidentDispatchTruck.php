<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentDispatchTruck extends Model
{
    protected $table = 'incident_dispatch_truck';

    public $timestamps = false;

    protected $fillable = [
        'dispatch_id',
        'truck_name',
    ];

    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(IncidentDispatch::class, 'dispatch_id', 'dispatch_id');
    }
}
