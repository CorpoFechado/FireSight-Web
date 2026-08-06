<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barangay extends Model
{
    protected $table = 'barangay';

    protected $primaryKey = 'barangay_id';

    public $timestamps = false;

    protected $fillable = ['barangay_name', 'latitude', 'longitude', 'boundary'];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'boundary' => 'array',
        ];
    }

    public function incidentRecords(): HasMany
    {
        return $this->hasMany(IncidentRecord::class, 'barangay_id');
    }

    public function riskAssessments(): HasMany
    {
        return $this->hasMany(RiskAssessment::class, 'barangay_id');
    }
}
