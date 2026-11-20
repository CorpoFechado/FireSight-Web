<?php

namespace App\Http\Controllers\Api;

use App\Models\ResidentCctv;
use App\Services\MobileNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CctvController extends ApiController
{
    public function __construct(
        protected MobileNotificationService $notificationService,
    ) {}

    /**
     * Get the logged-in resident's saved CCTV device.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return $this->sendError('Unauthenticated.', 401);
        }

        $cctv = ResidentCctv::where('user_id', $user->user_id)->first();
        if (! $cctv) {
            return $this->sendSuccess([
                'has_camera' => false,
                'camera' => null,
            ]);
        }

        return $this->sendSuccess([
            'has_camera' => true,
            'camera' => $this->formatCamera($cctv, $request),
        ]);
    }

    /**
     * Save or update the resident's CCTV camera configuration.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return $this->sendError('Unauthenticated.', 401);
        }

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:100'],
            'camera_ip' => ['nullable', 'string', 'max:100'],
            'rtsp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'stream_path' => ['nullable', 'string', 'max:100'],
            'rtsp_username' => ['nullable', 'string', 'max:100'],
            'rtsp_password' => ['nullable', 'string', 'max:100'],
            'custom_rtsp_url' => ['nullable', 'string', 'max:500'],
            'notifications_enabled' => ['nullable', 'boolean'],
        ]);

        $cctv = ResidentCctv::firstOrNew(['user_id' => $user->user_id]);

        if (isset($validated['name'])) {
            $cctv->name = trim($validated['name']) ?: 'My Home CCTV';
        } elseif (! $cctv->exists) {
            $cctv->name = 'My Home CCTV';
        }

        if (array_key_exists('camera_ip', $validated)) {
            $cctv->camera_ip = $validated['camera_ip'];
        }
        if (array_key_exists('rtsp_port', $validated)) {
            $cctv->rtsp_port = (int) ($validated['rtsp_port'] ?: 554);
        }
        if (array_key_exists('stream_path', $validated)) {
            $cctv->stream_path = $validated['stream_path'] ?: 'stream2';
        }
        if (array_key_exists('rtsp_username', $validated)) {
            $cctv->rtsp_username = $validated['rtsp_username'];
        }
        if (array_key_exists('rtsp_password', $validated) && $validated['rtsp_password'] !== null && trim((string) $validated['rtsp_password']) !== '') {
            $cctv->rtsp_password = $validated['rtsp_password'];
        }
        if (array_key_exists('custom_rtsp_url', $validated)) {
            $cctv->custom_rtsp_url = $validated['custom_rtsp_url'];
        }
        if (array_key_exists('notifications_enabled', $validated)) {
            $cctv->notifications_enabled = (bool) $validated['notifications_enabled'];
        }

        if (empty($cctv->camera_token)) {
            $cctv->camera_token = Str::random(32);
        }

        $cctv->save();

        // Notify local Python stream service of the new camera connection
        try {
            Http::timeout(1.5)->post('http://127.0.0.1:5001/api/configure', [
                'token' => $cctv->camera_token,
                'rtsp_url' => $cctv->resolved_rtsp_url,
            ]);
        } catch (\Throwable) {
            // Background daemon will auto-read or pick up upon request
        }

        return $this->sendSuccess([
            'has_camera' => true,
            'camera' => $this->formatCamera($cctv, $request),
            'message' => 'Camera configuration saved successfully.',
        ]);
    }

    /**
     * Delete the resident's saved CCTV camera.
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return $this->sendError('Unauthenticated.', 401);
        }

        $cctv = ResidentCctv::where('user_id', $user->user_id)->first();
        if ($cctv) {
            $cctv->delete();
        }

        return $this->sendSuccess([
            'has_camera' => false,
            'message' => 'CCTV camera removed successfully.',
        ]);
    }

    /**
     * Webhook called by the Python detection service when fire/smoke is spotted.
     */
    public function alert(Request $request): JsonResponse
    {
        $token = (string) $request->input('token', '');
        if ($token === '') {
            return $this->sendError('Missing camera token.', 400);
        }

        $cctv = ResidentCctv::where('camera_token', $token)->first();
        if (! $cctv) {
            // Fallback for default token: find first resident with active camera
            if ($token === 'default') {
                $cctv = ResidentCctv::latest('updated_at')->first();
            }
        }

        if (! $cctv) {
            return $this->sendError('Camera token not found.', 404);
        }

        if (! $cctv->notifications_enabled) {
            return $this->sendSuccess(['notified' => false, 'reason' => 'notifications_disabled']);
        }

        // Throttle notifications to at most once per 30 seconds
        if ($cctv->last_fire_detected_at && $cctv->last_fire_detected_at->diffInSeconds(now()) < 30) {
            return $this->sendSuccess(['notified' => false, 'reason' => 'throttled']);
        }

        $cctv->update(['last_fire_detected_at' => now()]);

        $alertType = (string) $request->input('type', 'fire');
        $confidence = (float) $request->input('confidence', 0.0);

        $this->notificationService->notifyResidentCctvAlert(
            $cctv->user_id,
            $cctv->name,
            $alertType,
            $confidence
        );

        return $this->sendSuccess([
            'notified' => true,
            'camera_id' => $cctv->id,
            'user_id' => $cctv->user_id,
        ]);
    }

    /**
     * Resolve camera stream details by token for the Python AI stream engine.
     */
    public function resolve(string $token): JsonResponse
    {
        $cctv = ResidentCctv::where('camera_token', $token)->first();
        if (! $cctv && $token === 'default') {
            $cctv = ResidentCctv::latest('updated_at')->first();
        }

        if (! $cctv) {
            return $this->sendError('Camera token not found.', 404);
        }

        return $this->sendSuccess([
            'token' => $cctv->camera_token,
            'rtsp_url' => $cctv->resolved_rtsp_url,
            'name' => $cctv->name,
            'notifications_enabled' => (bool) $cctv->notifications_enabled,
        ]);
    }

    /**
     * Format camera object for mobile consumption.
     *
     * @return array<string, mixed>
     */
    protected function formatCamera(ResidentCctv $cctv, Request $request): array
    {
        // Resolve stream URL: prefer configured public base URL (for VPS / Nginx SSL proxying)
        // or dynamically infer from the incoming request.
        $configuredBase = config('services.cctv.stream_base_url') ?: env('CCTV_STREAM_BASE_URL');
        if ($configuredBase) {
            $base = rtrim((string) $configuredBase, '/');
            $streamUrl = "{$base}/stream/{$cctv->camera_token}";
            $telemetryUrl = "{$base}/telemetry/{$cctv->camera_token}";
        } else {
            $scheme = $request->isSecure() ? 'https' : 'http';
            $host = $request->getHost();
            $streamPort = $request->isSecure() ? '' : ':5001';
            $streamUrl = "{$scheme}://{$host}{$streamPort}/stream/{$cctv->camera_token}";
            $telemetryUrl = "{$scheme}://{$host}{$streamPort}/telemetry/{$cctv->camera_token}";
        }

        return [
            'id' => $cctv->id,
            'name' => $cctv->name,
            'camera_ip' => $cctv->camera_ip,
            'rtsp_port' => $cctv->rtsp_port,
            'stream_path' => $cctv->stream_path,
            'rtsp_username' => $cctv->rtsp_username,
            'has_password' => ! empty($cctv->rtsp_password),
            'custom_rtsp_url' => $cctv->custom_rtsp_url,
            'camera_token' => $cctv->camera_token,
            'notifications_enabled' => (bool) $cctv->notifications_enabled,
            'last_fire_detected_at' => $cctv->last_fire_detected_at?->toIso8601String(),
            'stream_url' => $streamUrl,
            'telemetry_url' => $telemetryUrl,
            'created_at' => $cctv->created_at?->toIso8601String(),
            'updated_at' => $cctv->updated_at?->toIso8601String(),
        ];
    }
}
