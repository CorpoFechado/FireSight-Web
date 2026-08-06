<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskAssessment extends Model
{
    protected $table = 'risk_assessment';

    protected $primaryKey = 'risk_id';

    public const CREATED_AT = 'generated_at';

    public const UPDATED_AT = null;

    protected $fillable = ['barangay_id', 'date', 'prediction_score', 'risk_level'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'prediction_score' => 'decimal:4',
        ];
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }
}
