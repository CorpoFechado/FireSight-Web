<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncidentDispatch extends Model
{
    protected $table = 'incident_dispatch';

    protected $primaryKey = 'dispatch_id';

    public $timestamps = false;

    protected $fillable = [
        'report_id',
        'dispatched_by',
        'dispatched_at',
    ];

    protected function casts(): array
    {
        return [
            'dispatched_at' => 'datetime',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(CommunityReport::class, 'report_id', 'report_id');
    }

    public function dispatcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by', 'user_id');
    }

    public function trucks(): HasMany
    {
        return $this->hasMany(IncidentDispatchTruck::class, 'dispatch_id', 'dispatch_id');
    }

    public function personnel(): HasMany
    {
        return $this->hasMany(IncidentDispatchPersonnel::class, 'dispatch_id', 'dispatch_id');
    }

    public function crew(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'incident_dispatch_personnel', 'dispatch_id', 'user_id');
    }
}
