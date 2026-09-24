<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmergencyContact extends Model
{
    protected $table = 'emergency_contact';

    protected $primaryKey = 'contact_id';

    public $timestamps = false;

    protected $fillable = [
        'name', 'category', 'phone_number', 'description', 'is_primary', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
