<?php

use App\Models\Announcement;
use App\Models\User;

test('bfp personnel cannot access the announcements page', function () {
    $user = User::factory()->bfpPersonnel()->create();
    $this->actingAs($user);

    $response = $this->get(route('announcements'));
    $response->assertForbidden();
});

test('bfp admin can view the announcements page', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $response = $this->get(route('announcements'));
    $response->assertOk();
});

test('bfp admin can publish a new announcement', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $response = $this->post(route('announcements.store'), [
        'announcement_type' => 'advisory',
        'title' => 'Red Alert: Dry Season Fire Risk Advisory',
        'content' => 'Due to the ongoing dry spell, all personnel are advised to be on heightened alert.',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('announcement', [
        'created_by' => $admin->id,
        'announcement_type' => 'advisory',
        'title' => 'Red Alert: Dry Season Fire Risk Advisory',
    ]);
});

test('announcement fields are required', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $response = $this->post(route('announcements.store'), []);

    $response->assertSessionHasErrors(['announcement_type', 'title', 'content']);
});

test('bfp admin can update an announcement', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $announcement = Announcement::create([
        'created_by' => $admin->id,
        'announcement_type' => 'general',
        'title' => 'Original Title',
        'content' => 'Original content.',
    ]);

    $response = $this->patch(route('announcements.update', $announcement), [
        'announcement_type' => 'emergency',
        'title' => 'Updated Title',
        'content' => 'Updated content.',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('announcement', [
        'announcement_id' => $announcement->announcement_id,
        'announcement_type' => 'emergency',
        'title' => 'Updated Title',
    ]);
});

test('bfp admin can delete an announcement', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $announcement = Announcement::create([
        'created_by' => $admin->id,
        'announcement_type' => 'general',
        'title' => 'To Be Deleted',
        'content' => 'Content.',
    ]);

    $response = $this->delete(route('announcements.destroy', $announcement));

    $response->assertRedirect();
    $this->assertDatabaseMissing('announcement', ['announcement_id' => $announcement->announcement_id]);
});

test('announcements can be filtered by category', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    Announcement::create([
        'created_by' => $admin->id,
        'announcement_type' => 'advisory',
        'title' => 'Advisory Alert',
        'content' => 'First content',
    ]);
    Announcement::create([
        'created_by' => $admin->id,
        'announcement_type' => 'emergency',
        'title' => 'Emergency Alert',
        'content' => 'Second content',
    ]);

    $response = $this->get(route('announcements', ['category' => 'emergency']));
    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->component('announcements/index')
            ->where('filters.category', 'emergency')
            ->has('announcements', 1)
            ->where('announcements.0.title', 'Emergency Alert')
    );
});

test('announcements can be searched by title or content', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    Announcement::create([
        'created_by' => $admin->id,
        'announcement_type' => 'general',
        'title' => 'Water Interruption Schedule',
        'content' => 'Water service temporarily interrupted.',
    ]);
    Announcement::create([
        'created_by' => $admin->id,
        'announcement_type' => 'fire_safety_tip',
        'title' => 'Electrical Safety',
        'content' => 'Inspect frayed cables carefully.',
    ]);

    $response = $this->get(route('announcements', ['search' => 'Electrical']));
    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->where('filters.search', 'Electrical')
            ->has('announcements', 1)
            ->where('announcements.0.title', 'Electrical Safety')
    );

    $responseContent = $this->get(route('announcements', ['search' => 'interrupted']));
    $responseContent->assertOk();
    $responseContent->assertInertia(
        fn ($page) => $page
            ->has('announcements', 1)
            ->where('announcements.0.title', 'Water Interruption Schedule')
    );
});

test('announcements can be filtered by both category and search', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    Announcement::create([
        'created_by' => $admin->id,
        'announcement_type' => 'advisory',
        'title' => 'Typhoon Signal Warning',
        'content' => 'Advisory about storm.',
    ]);
    Announcement::create([
        'created_by' => $admin->id,
        'announcement_type' => 'emergency',
        'title' => 'Typhoon Evacuation Notice',
        'content' => 'Immediate evacuation.',
    ]);

    $response = $this->get(route('announcements', [
        'category' => 'emergency',
        'search' => 'Typhoon',
    ]));

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->where('filters.category', 'emergency')
            ->where('filters.search', 'Typhoon')
            ->has('announcements', 1)
            ->where('announcements.0.title', 'Typhoon Evacuation Notice')
    );
});
