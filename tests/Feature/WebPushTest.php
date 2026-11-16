<?php

use App\Models\Barangay;
use App\Models\CommunityReport;
use App\Models\PushSubscription;
use App\Models\User;
use App\Services\WebPushService;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Minishlink\WebPush\MessageSentReport;
use Minishlink\WebPush\WebPush;

test('bfp admin can store, reassign, and delete a push subscription', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $otherAdmin = User::factory()->bfpAdmin()->create();
    $endpoint = 'https://fcm.googleapis.com/fcm/send/example-endpoint-123';

    $this->actingAs($admin)
        ->postJson(route('notifications.pushSubscriptions.store'), [
            'endpoint' => $endpoint,
            'keys' => [
                'p256dh' => 'BOr7ExamplePublicKeyBase64UrlEncodedString',
                'auth' => 'ExampleAuthSecret123',
            ],
            'content_encoding' => 'aes128gcm',
        ])
        ->assertCreated()
        ->assertJson(['subscribed' => true]);

    $this->assertDatabaseHas('push_subscriptions', [
        'user_id' => $admin->getKey(),
        'endpoint' => $endpoint,
        'content_encoding' => 'aes128gcm',
    ]);

    // Reassigning the same browser endpoint to another admin updates the owner cleanly.
    $this->actingAs($otherAdmin)
        ->postJson(route('notifications.pushSubscriptions.store'), [
            'endpoint' => $endpoint,
            'keys' => [
                'p256dh' => 'BOr7UpdatedPublicKeyBase64UrlEncodedString',
                'auth' => 'UpdatedAuthSecret456',
            ],
            'content_encoding' => 'aes128gcm',
        ])
        ->assertCreated();

    expect(PushSubscription::where('endpoint', $endpoint)->count())->toBe(1)
        ->and(PushSubscription::where('endpoint', $endpoint)->first()?->user_id)->toBe($otherAdmin->getKey());

    $this->actingAs($otherAdmin)
        ->deleteJson(route('notifications.pushSubscriptions.destroy'), [
            'endpoint' => $endpoint,
        ])
        ->assertOk()
        ->assertJson(['subscribed' => false]);

    $this->assertDatabaseMissing('push_subscriptions', [
        'endpoint' => $endpoint,
    ]);
});

test('non-admin and unauthenticated users cannot manage push subscriptions', function () {
    $endpoint = 'https://fcm.googleapis.com/fcm/send/restricted-endpoint';

    $this->post(route('notifications.pushSubscriptions.store'), [
        'endpoint' => $endpoint,
    ])->assertRedirect(route('login'));

    $personnel = User::factory()->bfpPersonnel()->create();
    $resident = User::factory()->resident()->create();

    foreach ([$personnel, $resident] as $user) {
        $this->actingAs($user)
            ->postJson(route('notifications.pushSubscriptions.store'), [
                'endpoint' => $endpoint,
                'keys' => [
                    'p256dh' => 'Key',
                    'auth' => 'Auth',
                ],
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->postJson(route('notifications.pushSubscriptions.test'))
            ->assertForbidden();
    }
});

test('bfp admin can trigger a test push alert when configured and subscribed', function () {
    $admin = User::factory()->bfpAdmin()->create();

    $mock = Mockery::mock(WebPushService::class);
    $mock->shouldReceive('isConfigured')->andReturn(true);
    $mock->shouldReceive('sendToUsers')
        ->once()
        ->withArgs(function ($userIds, string $title, string $body, array $data) use ($admin): bool {
            return $userIds === $admin->getKey()
                && $title === 'FireSight Test Alert'
                && ($data['type'] ?? null) === 'test_alert';
        });
    $this->app->instance(WebPushService::class, $mock);

    // Without a subscription, returns 422.
    $this->actingAs($admin)
        ->postJson(route('notifications.pushSubscriptions.test'))
        ->assertUnprocessable();

    PushSubscription::create([
        'user_id' => $admin->getKey(),
        'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-device',
        'public_key' => 'BOr7TestKey',
        'auth_token' => 'TestAuth',
        'content_encoding' => 'aes128gcm',
    ]);

    $this->actingAs($admin)
        ->postJson(route('notifications.pushSubscriptions.test'))
        ->assertOk()
        ->assertJson(['sent' => true]);
});

test('creating a pending community report triggers WebPushService for active bfp admins only', function () {
    $activeAdmin = User::factory()->bfpAdmin()->create();
    $inactiveAdmin = User::factory()->bfpAdmin()->inactive()->create();
    $personnel = User::factory()->bfpPersonnel()->create();
    $reporter = User::factory()->resident()->create();
    $barangay = Barangay::create(['barangay_name' => 'Matabungkay']);

    $mock = Mockery::mock(WebPushService::class);
    $mock->shouldReceive('sendToUsers')
        ->once()
        ->withArgs(function ($userIds, string $title, string $body, array $data) use ($activeAdmin, $inactiveAdmin, $personnel): bool {
            $ids = is_array($userIds) ? $userIds : [$userIds];

            return in_array($activeAdmin->getKey(), $ids, true)
                && ! in_array($inactiveAdmin->getKey(), $ids, true)
                && ! in_array($personnel->getKey(), $ids, true)
                && $title === 'New Fire Report'
                && str_contains($body, 'Matabungkay')
                && str_contains($body, '94%')
                && ($data['type'] ?? null) === 'new_report'
                && str_starts_with((string) ($data['url'] ?? ''), '/incidents/');
        });
    $this->app->instance(WebPushService::class, $mock);

    CommunityReport::create([
        'user_id' => $reporter->getKey(),
        'reporter_name' => 'Maria Santos',
        'contact_number' => '09171234567',
        'description' => 'Visible flames near residential area.',
        'latitude' => 14.0353,
        'longitude' => 120.6511,
        'barangay_id' => $barangay->barangay_id,
        'status' => CommunityReport::STATUS_PENDING,
        'ai_fire_label' => 'fire',
        'ai_fire_confidence' => 0.94,
    ]);
});

test('status updates and non-pending reports do not trigger WebPushService', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $reporter = User::factory()->resident()->create();
    $barangay = Barangay::create(['barangay_name' => 'Poblacion 1']);

    $mock = Mockery::mock(WebPushService::class);
    $mock->shouldReceive('sendToUsers')->never();
    $this->app->instance(WebPushService::class, $mock);

    $resolvedReport = CommunityReport::create([
        'user_id' => $reporter->getKey(),
        'reporter_name' => 'Manual Log',
        'contact_number' => '09170001111',
        'description' => 'Manual resolved incident',
        'latitude' => 14.0353,
        'longitude' => 120.6511,
        'barangay_id' => $barangay->barangay_id,
        'status' => CommunityReport::STATUS_RESOLVED,
    ]);

    $resolvedReport->update(['status' => CommunityReport::STATUS_ACCEPTED]);
});

test('WebPushService removes expired 410 endpoints and keeps valid subscriptions', function () {
    config([
        'services.webpush.public_key' => str_repeat('A', 87),
        'services.webpush.private_key' => str_repeat('B', 43),
        'services.webpush.subject' => 'mailto:admin@firesight.local',
    ]);

    $admin = User::factory()->bfpAdmin()->create();
    $validEndpoint = 'https://push.example.test/valid-sub';
    $expiredEndpoint = 'https://push.example.test/expired-sub';

    PushSubscription::create([
        'user_id' => $admin->getKey(),
        'endpoint' => $validEndpoint,
        'public_key' => 'BOr7ValidPublicKey',
        'auth_token' => 'ValidAuth',
        'content_encoding' => 'aes128gcm',
    ]);

    PushSubscription::create([
        'user_id' => $admin->getKey(),
        'endpoint' => $expiredEndpoint,
        'public_key' => 'BOr7ExpiredPublicKey',
        'auth_token' => 'ExpiredAuth',
        'content_encoding' => 'aes128gcm',
    ]);

    $fakeWebPush = Mockery::mock(WebPush::class);
    $fakeWebPush->shouldReceive('queueNotification')->twice();
    $fakeWebPush->shouldReceive('flush')->once()->andReturn((function () use ($validEndpoint, $expiredEndpoint) {
        yield new MessageSentReport(new Request('POST', $validEndpoint), new Response(201), true, 'Created');
        yield new MessageSentReport(new Request('POST', $expiredEndpoint), new Response(410), false, 'Gone');
    })());

    $service = new class($fakeWebPush) extends WebPushService
    {
        public function __construct(private WebPush $client) {}

        protected function makeWebPush(array $auth): WebPush
        {
            return $this->client;
        }
    };

    $service->sendToUsers($admin->getKey(), 'New Fire Report', 'Test alert', ['report_id' => 42]);

    $this->assertDatabaseHas('push_subscriptions', ['endpoint' => $validEndpoint]);
    $this->assertDatabaseMissing('push_subscriptions', ['endpoint' => $expiredEndpoint]);
});

test('notifications page shares vapidPublicKey to bfp admin', function () {
    config(['services.webpush.public_key' => 'test-public-vapid-key']);
    $admin = User::factory()->bfpAdmin()->create();

    $this->actingAs($admin)
        ->get(route('notifications'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('notifications/index')
            ->where('vapidPublicKey', 'test-public-vapid-key')
        );
});

test('webpush:vapid --show prints valid public and private VAPID keys', function () {
    $this->artisan('webpush:vapid', ['--show' => true])
        ->expectsOutputToContain('VAPID_PUBLIC_KEY=')
        ->expectsOutputToContain('VAPID_PRIVATE_KEY=')
        ->assertSuccessful();
});
