<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResidentCctv extends Model
{
    use HasFactory;

    protected $table = 'resident_cctv';

    protected $fillable = [
        'user_id',
        'name',
        'camera_ip',
        'rtsp_port',
        'stream_path',
        'rtsp_username',
        'rtsp_password',
        'custom_rtsp_url',
        'camera_token',
        'notifications_enabled',
        'last_fire_detected_at',
    ];

    protected $casts = [
        'rtsp_port' => 'integer',
        'notifications_enabled' => 'boolean',
        'last_fire_detected_at' => 'datetime',
    ];

    /**
     * Get the resolved RTSP URL for this camera.
     */
    public function getResolvedRtspUrlAttribute(): string
    {
        if (! empty($this->custom_rtsp_url)) {
            return $this->custom_rtsp_url;
        }

        $user = $this->rtsp_username ? rawurlencode($this->rtsp_username) : '';
        $pass = $this->rtsp_password ? rawurlencode($this->rtsp_password) : '';
        $auth = ($user !== '' || $pass !== '') ? "{$user}:{$pass}@" : '';
        $ip = $this->camera_ip ?: '127.0.0.1';
        $port = $this->rtsp_port ?: 554;
        $path = ltrim((string) ($this->stream_path ?: 'stream2'), '/');

        return "rtsp://{$auth}{$ip}:{$port}/{$path}";
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
