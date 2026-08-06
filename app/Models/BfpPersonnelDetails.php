<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BfpPersonnelDetails extends Model
{
    protected $table = 'bfp_personnel_details';

    protected $primaryKey = 'details_id';

    public $timestamps = false;

    protected $fillable = ['user_id', 'rank', 'station_assigned', 'employee_number'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
