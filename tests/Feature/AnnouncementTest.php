<?php

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

test('announcements table does not have an announcement type column', function () {
    expect(Schema::hasColumn('announcement', 'announcement_type'))->toBeFalse();
});

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
        'title' => 'Red Alert: Dry Season Fire Risk Advisory',
        'content' => 'Due to the ongoing dry spell, all personnel are advised to be on heightened alert.',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('announcement', [
        'created_by' => $admin->getKey(),
        'title' => 'Red Alert: Dry Season Fire Risk Advisory',
    ]);
});

test('announcement fields are required', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $response = $this->post(route('announcements.store'), []);

    $response->assertSessionHasErrors(['title', 'content']);
});

test('bfp admin can update an announcement', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $announcement = Announcement::create([
        'created_by' => $admin->getKey(),
        'title' => 'Original Title',
        'content' => 'Original content.',
    ]);

    $response = $this->patch(route('announcements.update', $announcement), [
        'title' => 'Updated Title',
        'content' => 'Updated content.',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('announcement', [
        'announcement_id' => $announcement->announcement_id,
        'title' => 'Updated Title',
    ]);
});

test('bfp admin can delete an announcement', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $announcement = Announcement::create([
        'created_by' => $admin->getKey(),
        'title' => 'To Be Deleted',
        'content' => 'Content.',
    ]);

    $response = $this->delete(route('announcements.destroy', $announcement));

    $response->assertRedirect();
    $this->assertDatabaseMissing('announcement', ['announcement_id' => $announcement->announcement_id]);
});

test('announcement rows do not expose the removed type field', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    Announcement::create([
        'created_by' => $admin->getKey(),
        'title' => 'Advisory Alert',
        'content' => 'First content',
    ]);
    Announcement::create([
        'created_by' => $admin->getKey(),
        'title' => 'Emergency Alert',
        'content' => 'Second content',
    ]);

    $response = $this->get(route('announcements'));
    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->component('announcements/index')
            ->has('announcements', 2)
            ->missing('announcements.0.type')
    );
});

test('announcements can be searched by title or content', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    Announcement::create([
        'created_by' => $admin->getKey(),
        'title' => 'Water Interruption Schedule',
        'content' => 'Water service temporarily interrupted.',
    ]);
    Announcement::create([
        'created_by' => $admin->getKey(),
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

test('legacy category query does not affect announcement search', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    Announcement::create([
        'created_by' => $admin->getKey(),
        'title' => 'Typhoon Signal Warning',
        'content' => 'Advisory about storm.',
    ]);
    Announcement::create([
        'created_by' => $admin->getKey(),
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
            ->where('filters.search', 'Typhoon')
            ->has('announcements', 2)
            ->missing('filters.category')
    );
});
