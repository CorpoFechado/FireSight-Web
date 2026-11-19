<?php

namespace App\Services;

use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    public function isConfigured(): bool
    {
        return filled(config('services.webpush.public_key'))
            && filled(config('services.webpush.private_key'));
    }

    /**
     * Send a Web Push notification to all browser subscriptions of the given user(s).
     *
     * @param  list<int>|int  $userIds
     * @param  array<string, mixed>  $data
     */
    public function sendToUsers(
        array|int $userIds,
        string $title,
        string $body,
        array $data = [],
    ): void {
        if (! $this->isConfigured()) {
            return;
        }

        $ids = array_values(array_unique(array_map('intval', is_array($userIds) ? $userIds : [$userIds])));
        if ($ids === []) {
            return;
        }

        $subscriptions = PushSubscription::query()
            ->whereIn('user_id', $ids)
            ->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        $reportId = isset($data['report_id']) ? (int) $data['report_id'] : null;

        $payload = json_encode([
            'title' => $title,
            'body' => $body,
            'icon' => $data['icon'] ?? '/favicon.png?v=3',
            'badge' => $data['badge'] ?? '/favicon.png?v=3',
            'tag' => $data['tag'] ?? ($reportId !== null ? "firesight-report-{$reportId}" : 'firesight-alert'),
            'url' => $data['url'] ?? ($reportId !== null ? "/incidents/{$reportId}" : '/notifications'),
            'report_id' => $reportId,
            'type' => $data['type'] ?? 'new_report',
            'requireInteraction' => (bool) ($data['requireInteraction'] ?? true),
        ], JSON_THROW_ON_ERROR);

        $auth = [
            'VAPID' => [
                'subject' => (string) (config('services.webpush.subject') ?: 'mailto:admin@firesight.local'),
                'publicKey' => (string) config('services.webpush.public_key'),
                'privateKey' => (string) config('services.webpush.private_key'),
            ],
        ];

        try {
            $webPush = $this->makeWebPush($auth);

            foreach ($subscriptions as $record) {
                $webPush->queueNotification(
                    Subscription::create([
                        'endpoint' => $record->endpoint,
                        'publicKey' => $record->public_key,
                        'authToken' => $record->auth_token,
                        'contentEncoding' => $record->content_encoding ?: 'aes128gcm',
                    ]),
                    $payload,
                    ['TTL' => 300, 'urgency' => 'high'],
                );
            }

            $expiredEndpoints = [];

            foreach ($webPush->flush() as $report) {
                if ($report->isSuccess()) {
                    continue;
                }

                if ($report->isSubscriptionExpired()) {
                    $expiredEndpoints[] = $report->getEndpoint();
                    Log::info('[WebPushService] Subscription expired or rejected by provider.', [
                        'endpoint' => $report->getEndpoint(),
                        'reason' => $report->getReason(),
                    ]);

                    continue;
                }

                Log::warning('[WebPushService] Failed to deliver push notification.', [
                    'endpoint' => $report->getEndpoint(),
                    'reason' => $report->getReason(),
                ]);
            }

            if ($expiredEndpoints !== []) {
                PushSubscription::query()
                    ->whereIn('endpoint', $expiredEndpoints)
                    ->delete();

                Log::info('[WebPushService] Cleaned up '.count($expiredEndpoints).' expired push subscriptions.');
            }
        } catch (\Throwable $e) {
            Log::error('[WebPushService] Exception while sending Web Push: '.$e->getMessage());
        }
    }

    /**
     * @param  array<string, mixed>  $auth
     */
    protected function makeWebPush(array $auth): WebPush
    {
        return new WebPush($auth);
    }
}
