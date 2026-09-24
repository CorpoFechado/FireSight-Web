<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthToken extends Model
{
    protected $table = 'auth_token';

    protected $primaryKey = 'token_id';

    /** Only `created_at` exists; no `updated_at`. */
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'token', 'device_info', 'expires_at', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
