<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExpoPushService
{
    private const EXPO_PUSH_URL = 'https://exp.host/--/api/v2/push/send';

    /**
     * Send a push notification to one or multiple users by user_id.
     *
     * @param  list<int>|int  $userIds
     * @param  array<string, mixed>  $data
     */
    public function sendToUsers(
        array|int $userIds,
        string $title,
        string $body,
        array $data = [],
        string $priority = 'high',
        string $channelId = 'firesight-emergency-alerts-v5'
    ): void {
        $ids = is_array($userIds) ? $userIds : [$userIds];

        if (empty($ids)) {
            return;
        }

        $tokens = DB::table('auth_token')
            ->whereIn('user_id', $ids)
            ->whereNotNull('push_token')
            ->where('push_token', 'like', 'ExponentPushToken%')
            ->pluck('push_token')
            ->unique()
            ->values()
            ->all();

        if (empty($tokens)) {
            return;
        }

        $this->sendToTokens($tokens, $title, $body, $data, $priority, $channelId);
    }

    /**
     * Send messages to a list of Expo push tokens in batches.
     *
     * @param  list<string>  $tokens
     * @param  array<string, mixed>  $data
     */
    public function sendToTokens(
        array $tokens,
        string $title,
        string $body,
        array $data = [],
        string $priority = 'high',
        string $channelId = 'firesight-emergency-alerts-v5'
    ): void {
        // Expo supports up to 100 messages per batch request
        $chunks = array_chunk($tokens, 100);

        foreach ($chunks as $chunk) {
            $messages = [];
            foreach ($chunk as $token) {
                $messages[] = [
                    'to' => $token,
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                    'priority' => $priority,
                    'channelId' => $channelId,
                    'data' => $data,
                ];
            }

            try {
                $response = Http::timeout(5)
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ])
                    ->post(self::EXPO_PUSH_URL, $messages);

                if ($response->successful()) {
                    $json = $response->json();
                    $this->cleanupInvalidTokens($chunk, $json['data'] ?? []);
                } else {
                    Log::warning('[ExpoPushService] Failed to send push batch: '.$response->body());
                }
            } catch (\Throwable $e) {
                Log::error('[ExpoPushService] Exception while sending push: '.$e->getMessage());
            }
        }
    }

    /**
     * Clean up tokens that Expo flagged as unregistered/invalid.
     *
     * @param  list<string>  $tokens
     * @param  list<array<string, mixed>>  $receipts
     */
    private function cleanupInvalidTokens(array $tokens, array $receipts): void
    {
        $invalidTokens = [];

        foreach ($receipts as $index => $receipt) {
            if (isset($receipt['status']) && $receipt['status'] === 'error') {
                $details = $receipt['details'] ?? [];
                if (isset($details['error']) && $details['error'] === 'DeviceNotRegistered') {
                    if (isset($tokens[$index])) {
                        $invalidTokens[] = $tokens[$index];
                    }
                }
            }
        }

        if (! empty($invalidTokens)) {
            DB::table('auth_token')
                ->whereIn('push_token', $invalidTokens)
                ->update(['push_token' => null]);

            Log::info('[ExpoPushService] Cleaned up '.count($invalidTokens).' unregistered push tokens.');
        }
    }
}
