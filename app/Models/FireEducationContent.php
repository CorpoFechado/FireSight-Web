<?php

namespace App\Models;

use Database\Factories\FireEducationContentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FireEducationContent extends Model
{
    /** @use HasFactory<FireEducationContentFactory> */
    use HasFactory;

    protected $table = 'fire_education_content';

    protected $primaryKey = 'content_id';

    public $timestamps = false;

    protected $fillable = [
        'title', 'category', 'summary', 'body', 'image_path',
        'read_minutes', 'is_featured', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'read_minutes' => 'integer',
            'created_at' => 'datetime',
        ];
    }
}
