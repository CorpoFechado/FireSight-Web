<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentDispatchPersonnel extends Model
{
    protected $table = 'incident_dispatch_personnel';

    public $timestamps = false;

    protected $fillable = [
        'dispatch_id',
        'user_id',
    ];

    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(IncidentDispatch::class, 'dispatch_id', 'dispatch_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
