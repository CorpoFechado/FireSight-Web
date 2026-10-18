<?php

use App\Models\Notification;
use App\Models\User;

test('users authenticate and resolve relationships with the user_id primary key', function () {
    $user = User::factory()->bfpAdmin()->create();
    $notification = Notification::create([
        'user_id' => $user->getKey(),
        'title' => 'Primary key check',
        'message' => 'Relationship keys resolve correctly.',
        'notification_type' => 'system',
    ]);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticatedAs($user);

    expect($user->getKeyName())->toBe('user_id')
        ->and($notification->user->is($user))->toBeTrue()
        ->and($user->appNotifications()->first()?->is($notification))->toBeTrue();
});
