<?php

use App\Models\CommunityReport;
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
        'user_id' => $resident->getKey(),
        'title' => 'Resident Alert',
        'message' => 'Something happened',
        'notification_type' => 'status_update',
        'is_read' => false,
    ]);

    Notification::create([
        'user_id' => $admin->getKey(),
        'title' => 'New Fire Report',
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
        ->where('groups.today.0.title', 'New Fire Report')
        ->missing('groups.today.0.user_name')
        ->where('unreadCount', 1)
    );
});

it('includes the report id in notification rows and opens read and unread notifications', function (bool $isRead) {
    $admin = User::factory()->create(['role' => User::ROLE_BFP_ADMIN, 'email_verified_at' => now()]);
    $report = CommunityReport::create([
        'user_id' => $admin->getKey(),
        'reporter_name' => 'Reporter',
        'contact_number' => '09123456789',
        'description' => 'Fire report',
        'latitude' => 13.95,
        'longitude' => 120.65,
        'status' => CommunityReport::STATUS_PENDING,
    ]);
    $notification = Notification::create([
        'user_id' => $admin->getKey(),
        'report_id' => $report->getKey(),
        'title' => 'Fire report',
        'message' => 'A report was submitted.',
        'notification_type' => 'incident_alert',
        'is_read' => $isRead,
    ]);

    $this->actingAs($admin)->get(route('notifications'))
        ->assertInertia(fn ($page) => $page->where('groups.today.0.report_id', $report->getKey()));

    $this->get(route('notifications.open', $notification))
        ->assertRedirect(route('incidents.show', ['report' => $report->getKey(), 'from' => 'notifications']));

    expect($notification->fresh()->is_read)->toBeTrue();
})->with([false, true]);

it('does not open another users notification', function () {
    $admin = User::factory()->create(['role' => User::ROLE_BFP_ADMIN, 'email_verified_at' => now()]);
    $other = User::factory()->create();
    $notification = Notification::create([
        'user_id' => $other->getKey(),
        'title' => 'Other notification',
        'message' => 'Private',
        'notification_type' => 'incident_alert',
        'is_read' => false,
    ]);

    $this->actingAs($admin)->get(route('notifications.open', $notification))->assertNotFound();
    expect($notification->fresh()->is_read)->toBeFalse();
});

it('marks a notification read and shows an error when its report is unavailable', function () {
    $admin = User::factory()->create(['role' => User::ROLE_BFP_ADMIN, 'email_verified_at' => now()]);
    $notification = Notification::create([
        'user_id' => $admin->getKey(),
        'title' => 'Missing report',
        'message' => 'Unavailable',
        'notification_type' => 'incident_alert',
        'is_read' => false,
    ]);

    $this->actingAs($admin)->get(route('notifications.open', $notification))
        ->assertRedirect(route('notifications'))
        ->assertSessionHas('toast', [
            'type' => 'error',
            'message' => 'The related report is no longer available.',
        ]);
    expect($notification->fresh()->is_read)->toBeTrue();
});

it('bfp personnel only sees their own notifications', function () {
    $personnel = User::factory()->bfpPersonnel()->create([
        'email_verified_at' => now(),
    ]);

    $other = User::factory()->create(['email_verified_at' => now()]);

    Notification::create([
        'user_id' => $personnel->getKey(),
        'title' => 'My Notif',
        'message' => 'For me',
        'notification_type' => 'status_update',
        'is_read' => false,
    ]);

    Notification::create([
        'user_id' => $other->getKey(),
        'title' => 'Other Notif',
        'message' => 'Not mine',
        'notification_type' => 'status_update',
        'is_read' => false,
    ]);

    $response = $this->actingAs($personnel)->get(route('notifications'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('notifications/index')
        ->where('isAdmin', false)
    );
});
