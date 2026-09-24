<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarangayContact extends Model
{
    protected $table = 'barangay_contact';

    protected $primaryKey = 'contact_id';

    public $timestamps = false;

    protected $fillable = ['barangay_id', 'name', 'role', 'phone_number'];

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }
}
