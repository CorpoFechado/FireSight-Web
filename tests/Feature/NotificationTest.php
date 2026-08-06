<?php

use App\Models\Barangay;
use App\Models\CommunityReport;
use App\Models\Notification;
use App\Models\User;

test('bfp staff can view their own notifications page', function () {
    $user = User::factory()->bfpPersonnel()->create();
    $this->actingAs($user);

    $response = $this->get(route('notifications'));
    $response->assertOk();
});

test('notifications are grouped into today, recent, and earlier', function () {
    $user = User::factory()->bfpAdmin()->create();
    $this->actingAs($user);

    $today = Notification::create([
        'user_id' => $user->id,
        'title' => 'Today item',
        'message' => 'This happened today.',
        'notification_type' => 'system',
    ]);

    $recent = Notification::create([
        'user_id' => $user->id,
        'title' => 'Recent item',
        'message' => 'This happened a few days ago.',
        'notification_type' => 'system',
    ]);
    $recent->forceFill(['created_at' => now()->subDays(3)])->saveQuietly();

    $earlier = Notification::create([
        'user_id' => $user->id,
        'title' => 'Earlier item',
        'message' => 'This happened a while ago.',
        'notification_type' => 'system',
    ]);
    $earlier->forceFill(['created_at' => now()->subDays(30)])->saveQuietly();

    $response = $this->get(route('notifications'));

    $response->assertInertia(fn ($page) => $page
        ->where('groups.today.0.title', 'Today item')
        ->where('groups.recent.0.title', 'Recent item')
        ->where('groups.earlier.0.title', 'Earlier item')
        ->where('unreadCount', 3)
    );
});

test('a user can mark a single notification as read', function () {
    $user = User::factory()->bfpPersonnel()->create();
    $this->actingAs($user);

    $notification = Notification::create([
        'user_id' => $user->id,
        'title' => 'Test',
        'message' => 'Test message.',
        'notification_type' => 'system',
    ]);

    $response = $this->patch(route('notifications.read', $notification));

    $response->assertRedirect();
    $this->assertDatabaseHas('notification', [
        'notification_id' => $notification->notification_id,
        'is_read' => 1,
    ]);
});

test('a user cannot mark another user\'s notification as read', function () {
    $owner = User::factory()->bfpPersonnel()->create();
    $intruder = User::factory()->bfpPersonnel()->create();
    $this->actingAs($intruder);

    $notification = Notification::create([
        'user_id' => $owner->id,
        'title' => 'Not yours',
        'message' => 'Test message.',
        'notification_type' => 'system',
    ]);

    $response = $this->patch(route('notifications.read', $notification));

    $response->assertNotFound();
    $this->assertDatabaseHas('notification', [
        'notification_id' => $notification->notification_id,
        'is_read' => 0,
    ]);
});

test('a user can mark all their notifications as read', function () {
    $user = User::factory()->bfpAdmin()->create();
    $this->actingAs($user);

    Notification::create(['user_id' => $user->id, 'title' => 'A', 'message' => 'A', 'notification_type' => 'system']);
    Notification::create(['user_id' => $user->id, 'title' => 'B', 'message' => 'B', 'notification_type' => 'system']);

    $response = $this->post(route('notifications.readAll'));

    $response->assertRedirect();
    expect($user->appNotifications()->where('is_read', false)->count())->toBe(0);
});

test('verifying a report notifies the reporter', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $reporter = User::factory()->create();
    $barangay = Barangay::create(['barangay_name' => 'Test Barangay']);
    $this->actingAs($admin);

    $report = CommunityReport::create([
        'user_id' => $reporter->id,
        'reporter_name' => $reporter->name,
        'contact_number' => '09171234567',
        'description' => 'Test report',
        'latitude' => 13.9,
        'longitude' => 120.6,
        'status' => CommunityReport::STATUS_PENDING,
    ]);

    $this->post(route('incidents.verify', $report), [
        'barangay_id' => $barangay->barangay_id,
        'incident_type' => 'structural',
        'severity_level' => 'high',
    ]);

    $this->assertDatabaseHas('notification', [
        'user_id' => $reporter->id,
        'notification_type' => 'status_update',
    ]);
});

test('publishing an announcement notifies other active staff but not the publisher', function () {
    $publisher = User::factory()->bfpAdmin()->create();
    $otherActive = User::factory()->bfpPersonnel()->create();
    $inactive = User::factory()->bfpPersonnel()->inactive()->create();
    $this->actingAs($publisher);

    $this->post(route('announcements.store'), [
        'announcement_type' => 'general',
        'title' => 'Test Announcement',
        'content' => 'Test content.',
    ]);

    $this->assertDatabaseHas('notification', [
        'user_id' => $otherActive->id,
        'notification_type' => 'reminder',
    ]);
    $this->assertDatabaseMissing('notification', ['user_id' => $publisher->id]);
    $this->assertDatabaseMissing('notification', ['user_id' => $inactive->id]);
});
