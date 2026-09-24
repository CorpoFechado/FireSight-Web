<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResidentAddress extends Model
{
    protected $table = 'resident_address';

    protected $primaryKey = 'address_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'house_no_street', 'barangay_id', 'municipality', 'province',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }
}
