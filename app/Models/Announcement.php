<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $table = 'announcement';

    protected $primaryKey = 'announcement_id';

    public const UPDATED_AT = null;

    protected $fillable = ['created_by', 'title', 'content', 'announcement_type'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
