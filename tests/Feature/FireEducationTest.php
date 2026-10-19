<?php

use App\Models\FireEducationContent;
use App\Models\User;
use Database\Seeders\FireEducationContentSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

test('fire education content table does not have a read minutes column', function () {
    expect(Schema::hasColumn('fire_education_content', 'read_minutes'))->toBeFalse();
});

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
        'is_featured' => true,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('fire_education_content', [
        'title' => 'Electrical Safety Protocol 2026',
        'category' => 'prevention',
        'is_featured' => 1,
    ]);
});

test('fire education required fields are validated', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $response = $this->post(route('fireEducation.store'), []);

    $response->assertSessionHasErrors(['title', 'category', 'summary', 'body']);
});

test('bfp admin can update a fire education article', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $content = FireEducationContent::create([
        'title' => 'Draft Guidelines',
        'category' => 'awareness',
        'summary' => 'Draft summary',
        'body' => 'Draft body',
        'is_featured' => false,
    ]);

    $response = $this->patch(route('fireEducation.update', $content), [
        'title' => 'Final Guidelines for LPG Safety',
        'category' => 'prevention',
        'summary' => 'Safe handling and inspection of LPG tanks at home.',
        'body' => 'Inspect regulator hoses every 6 months for cracks or brittle surfaces.',
        'is_featured' => true,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('fire_education_content', [
        'content_id' => $content->content_id,
        'title' => 'Final Guidelines for LPG Safety',
        'category' => 'prevention',
        'is_featured' => 1,
    ]);
});

test('editing a fire education article retains its existing image when no new image is uploaded', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $content = FireEducationContent::create([
        'title' => 'Electrical Safety Protocol',
        'category' => 'prevention',
        'summary' => 'Comprehensive electrical safety standards.',
        'body' => 'Inspect breakers regularly.',
        'is_featured' => false,
        'image_path' => 'uploads/education/existing-photo.jpg',
    ]);

    $response = $this->patch(route('fireEducation.update', $content), [
        'title' => 'Electrical Safety Protocol',
        'category' => 'prevention',
        'summary' => 'Comprehensive electrical safety standards.',
        'body' => 'Inspect breakers regularly.',
        'is_featured' => true,
        'remove_image' => false,
        'image_url' => '',
    ]);

    $response->assertRedirect();

    $content->refresh();
    expect($content->is_featured)->toBeTrue();
    expect($content->image_path)->toBe('uploads/education/existing-photo.jpg');
});

test('editing a fire education article deletes old image when remove_image is true', function () {
    Storage::fake('public');

    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $oldPath = 'uploads/education/old-photo.jpg';
    Storage::disk('public')->put($oldPath, 'dummy-content');

    $content = FireEducationContent::create([
        'title' => 'Electrical Safety Protocol',
        'category' => 'prevention',
        'summary' => 'Comprehensive electrical safety standards.',
        'body' => 'Inspect breakers regularly.',
        'is_featured' => false,
        'image_path' => $oldPath,
    ]);

    $response = $this->patch(route('fireEducation.update', $content), [
        'title' => 'Electrical Safety Protocol',
        'category' => 'prevention',
        'summary' => 'Comprehensive electrical safety standards.',
        'body' => 'Inspect breakers regularly.',
        'is_featured' => false,
        'remove_image' => true,
        'image_url' => '',
    ]);

    $response->assertRedirect();

    $content->refresh();
    expect($content->image_path)->toBeNull();
    Storage::disk('public')->assertMissing($oldPath);
});

test('editing a fire education article replaces existing image when a new file is uploaded', function () {
    Storage::fake('public');

    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $oldPath = 'uploads/education/old-photo.jpg';
    Storage::disk('public')->put($oldPath, 'dummy-content');

    $content = FireEducationContent::create([
        'title' => 'Electrical Safety Protocol',
        'category' => 'prevention',
        'summary' => 'Comprehensive electrical safety standards.',
        'body' => 'Inspect breakers regularly.',
        'is_featured' => false,
        'image_path' => $oldPath,
    ]);

    $newFile = UploadedFile::fake()->image('new-photo.jpg');

    $response = $this->patch(route('fireEducation.update', $content), [
        'title' => 'Electrical Safety Protocol',
        'category' => 'prevention',
        'summary' => 'Comprehensive electrical safety standards.',
        'body' => 'Inspect breakers regularly.',
        'is_featured' => false,
        'remove_image' => false,
        'image' => $newFile,
    ]);

    $response->assertRedirect();

    $content->refresh();
    expect($content->image_path)->not->toBeNull()
        ->and($content->image_path)->not->toBe($oldPath);
    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($content->image_path);
});

test('bfp admin can delete a fire education article', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $content = FireEducationContent::create([
        'title' => 'To Be Removed',
        'category' => 'prevention',
        'summary' => 'Summary to delete',
        'body' => 'Body to delete',
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
        'is_featured' => 1,
    ]);

    $this->assertDatabaseHas('fire_education_content', [
        'title' => 'Emergency Evacuation: The R.A.C.E. & P.A.S.S. Standards',
        'category' => 'emergency_response',
    ]);

    $this->assertDatabaseHas('fire_education_content', [
        'title' => 'Understanding Lian Barangay Risk Levels & Action Steps',
        'category' => 'awareness',
    ]);
});
