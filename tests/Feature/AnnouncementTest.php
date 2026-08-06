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
