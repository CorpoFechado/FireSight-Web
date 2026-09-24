<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportEvidence extends Model
{
    protected $table = 'report_evidence';

    protected $primaryKey = 'evidence_id';

    public $timestamps = false;

    protected $fillable = [
        'report_id', 'image_path', 'uploaded_by', 'caption', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(CommunityReport::class, 'report_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
