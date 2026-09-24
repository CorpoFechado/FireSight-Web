<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barangay extends Model
{
    protected $table = 'barangay';

    protected $primaryKey = 'barangay_id';

    public $timestamps = false;

    protected $fillable = [
        'barangay_name', 'latitude', 'longitude', 'boundary',
        'centroid_lat', 'centroid_lng',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'centroid_lat' => 'decimal:8',
            'centroid_lng' => 'decimal:8',
            'boundary' => 'array',
        ];
    }

    public function incidentRecords(): HasMany
    {
        return $this->hasMany(IncidentRecord::class, 'barangay_id');
    }

    public function communityReports(): HasMany
    {
        return $this->hasMany(CommunityReport::class, 'barangay_id');
    }

    public function riskAssessments(): HasMany
    {
        return $this->hasMany(RiskAssessment::class, 'barangay_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(BarangayContact::class, 'barangay_id');
    }

    public function residentAddresses(): HasMany
    {
        return $this->hasMany(ResidentAddress::class, 'barangay_id');
    }
}
