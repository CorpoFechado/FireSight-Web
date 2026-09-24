<?php

use App\Models\FireEducationContent;
use App\Models\User;
use Database\Seeders\FireEducationContentSeeder;

test('bfp personnel cannot access the fire education page', function () {
    $user = User::factory()->bfpPersonnel()->create();
    $this->actingAs($user);

    $response = $this->get(route('fireEducation'));
    $response->assertForbidden();
});

test('bfp admin can view the fire education page', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $response = $this->get(route('fireEducation'));
    $response->assertOk();
});

test('bfp admin can publish a new fire education article', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $response = $this->post(route('fireEducation.store'), [
        'title' => 'Electrical Safety Protocol 2026',
        'category' => 'prevention',
        'summary' => 'Comprehensive electrical safety standards for residential areas.',
        'body' => 'Ensure all circuit breakers are inspected regularly and avoid daisy chaining extension leads.',
        'read_minutes' => 6,
        'is_featured' => true,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('fire_education_content', [
        'title' => 'Electrical Safety Protocol 2026',
        'category' => 'prevention',
        'read_minutes' => 6,
        'is_featured' => 1,
    ]);
});

test('fire education required fields are validated', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $response = $this->post(route('fireEducation.store'), []);

    $response->assertSessionHasErrors(['title', 'category', 'summary', 'body', 'read_minutes']);
});

test('bfp admin can update a fire education article', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $content = FireEducationContent::create([
        'title' => 'Draft Guidelines',
        'category' => 'awareness',
        'summary' => 'Draft summary',
        'body' => 'Draft body',
        'read_minutes' => 3,
        'is_featured' => false,
    ]);

    $response = $this->patch(route('fireEducation.update', $content), [
        'title' => 'Final Guidelines for LPG Safety',
        'category' => 'prevention',
        'summary' => 'Safe handling and inspection of LPG tanks at home.',
        'body' => 'Inspect regulator hoses every 6 months for cracks or brittle surfaces.',
        'read_minutes' => 5,
        'is_featured' => true,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('fire_education_content', [
        'content_id' => $content->content_id,
        'title' => 'Final Guidelines for LPG Safety',
        'category' => 'prevention',
        'read_minutes' => 5,
        'is_featured' => 1,
    ]);
});

test('bfp admin can delete a fire education article', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $content = FireEducationContent::create([
        'title' => 'To Be Removed',
        'category' => 'prevention',
        'summary' => 'Summary to delete',
        'body' => 'Body to delete',
        'read_minutes' => 2,
    ]);

    $response = $this->delete(route('fireEducation.destroy', $content));

    $response->assertRedirect();

    $this->assertDatabaseMissing('fire_education_content', [
        'content_id' => $content->content_id,
    ]);
});

test('fire education seeder populates mobile reference articles', function () {
    $this->seed(FireEducationContentSeeder::class);

    $this->assertDatabaseHas('fire_education_content', [
        'title' => 'Philippines Fire Season Safety Guide 2026',
        'category' => 'prevention',
        'read_minutes' => 8,
        'is_featured' => 1,
    ]);

    $this->assertDatabaseHas('fire_education_content', [
        'title' => 'What To Do During a Fire',
        'category' => 'emergency_response',
    ]);

    $this->assertDatabaseHas('fire_education_content', [
        'title' => 'Understanding Barangay Fire Risk Levels',
        'category' => 'awareness',
    ]);
});
