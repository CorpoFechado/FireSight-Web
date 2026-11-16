<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Services\WebPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PushSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'url', 'max:512'],
            'keys' => ['required', 'array'],
            'keys.p256dh' => ['required', 'string', 'max:255'],
            'keys.auth' => ['required', 'string', 'max:255'],
            'content_encoding' => ['nullable', 'string', Rule::in(['aesgcm', 'aes128gcm'])],
        ]);

        $user = $request->user();
        $userAgent = Str::limit((string) $request->userAgent(), 512, '');

        PushSubscription::query()->updateOrCreate(
            ['endpoint' => $data['endpoint']],
            [
                'user_id' => $user->getKey(),
                'public_key' => $data['keys']['p256dh'],
                'auth_token' => $data['keys']['auth'],
                'content_encoding' => $data['content_encoding'] ?? 'aes128gcm',
                'user_agent' => $userAgent !== '' ? $userAgent : null,
            ],
        );

        return response()->json(['subscribed' => true], 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'url', 'max:512'],
        ]);

        $request->user()
            ->pushSubscriptions()
            ->where('endpoint', $data['endpoint'])
            ->delete();

        return response()->json(['subscribed' => false]);
    }

    public function test(Request $request, WebPushService $webPushService): JsonResponse
    {
        if (! $webPushService->isConfigured()) {
            return response()->json([
                'message' => 'Web Push VAPID keys are not configured on the server.',
            ], 422);
        }

        $user = $request->user();

        if (! $user->pushSubscriptions()->exists()) {
            return response()->json([
                'message' => 'Enable browser push notifications on this device before sending a test alert.',
            ], 422);
        }

        $webPushService->sendToUsers(
            $user->getKey(),
            'FireSight Test Alert',
            'Browser push notifications are active for new fire report alerts.',
            [
                'url' => '/notifications',
                'type' => 'test_alert',
                'tag' => 'firesight-test-alert',
                'requireInteraction' => false,
            ],
        );

        return response()->json([
            'sent' => true,
            'message' => 'Test push alert dispatched to your subscribed browser(s).',
        ]);
    }
}
