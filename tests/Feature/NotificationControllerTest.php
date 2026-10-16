<?php

use App\Models\Notification;
use App\Models\User;

it('admin only sees their own notifications, not residents\'', function () {
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
        'title' => 'Resident Alert',
        'message' => 'Something happened',
        'notification_type' => 'status_update',
        'is_read' => false,
    ]);

    Notification::create([
        'user_id' => $admin->id,
        'title' => 'New Community Report',
        'message' => 'Report #1 is awaiting verification.',
        'notification_type' => 'incident_alert',
        'is_read' => false,
    ]);

    $response = $this->actingAs($admin)->get(route('notifications'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('notifications/index')
        ->where('isAdmin', true)
        ->has('groups.today', 1)
        ->where('groups.today.0.title', 'New Community Report')
        ->missing('groups.today.0.user_name')
        ->where('unreadCount', 1)
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
