<?php

use App\Models\Notification;
use App\Models\User;

it('admin sees all notifications from all users', function () {
    $admin = User::factory()->create([
        'role' => User::ROLE_BFP_ADMIN,
        'email_verified_at' => now(),
    ]);

    $resident = User::factory()->create([
        'role' => User::ROLE_RESIDENT,
        'email_verified_at' => now(),
    ]);

    Notification::create([
        'user_id' => $resident->id,
        'title' => 'Test Alert',
        'message' => 'Something happened',
        'notification_type' => 'incident_alert',
        'is_read' => false,
    ]);

    $response = $this->actingAs($admin)->get(route('notifications'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('notifications/index')
        ->where('isAdmin', true)
        ->has('groups')
    );
});

it('bfp personnel only sees their own notifications', function () {
    $personnel = User::factory()->bfpPersonnel()->create([
        'email_verified_at' => now(),
    ]);

    $other = User::factory()->create(['email_verified_at' => now()]);

    Notification::create([
        'user_id' => $personnel->id,
        'title' => 'My Notif',
        'message' => 'For me',
        'notification_type' => 'system',
        'is_read' => false,
    ]);

    Notification::create([
        'user_id' => $other->id,
        'title' => 'Other Notif',
        'message' => 'Not mine',
        'notification_type' => 'system',
        'is_read' => false,
    ]);

    $response = $this->actingAs($personnel)->get(route('notifications'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('notifications/index')
        ->where('isAdmin', false)
    );
});
