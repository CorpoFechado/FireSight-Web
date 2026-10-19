<?php

use App\Models\Announcement;
use App\Models\Barangay;
use App\Models\BarangayContact;
use App\Models\EmergencyContact;
use App\Models\FireEducationContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('mobile api can fetch fire education articles', function () {
    FireEducationContent::create([
        'title' => 'Electrical Safety Protocol',
        'category' => 'prevention',
        'summary' => 'Comprehensive electrical safety guide.',
        'body' => 'Inspect wiring and do not overload outlets.',
        'image_path' => 'uploads/education/electrical_safety.jpg',
        'is_featured' => true,
        'created_at' => now(),
    ]);

    $response = $this->getJson('/api/v1/education');

    $response->assertOk()
        ->assertJsonIsArray()
        ->assertJsonStructure([
            '*' => [
                'content_id',
                'title',
                'category',
                'summary',
                'body',
                'image_path',
                'is_featured',
                'created_at',
            ],
        ]);
});

test('mobile api can fetch specific fire education article by id', function () {
    $article = FireEducationContent::create([
        'title' => 'Kitchen Safety Guide',
        'category' => 'prevention',
        'summary' => 'Cooking fire safety.',
        'body' => 'Never leave stove unattended.',
        'is_featured' => false,
        'created_at' => now(),
    ]);

    $response = $this->getJson("/api/v1/education/{$article->content_id}");

    $response->assertOk()
        ->assertJson([
            'content_id' => $article->content_id,
            'title' => 'Kitchen Safety Guide',
        ]);

    $missing = $this->getJson('/api/v1/education/99999');
    $missing->assertNotFound()
        ->assertJson(['message' => 'Article not found.']);
});

test('mobile api education list.php and detail.php compatibility aliases work', function () {
    $article = FireEducationContent::create([
        'title' => 'LPG Safety Guide',
        'category' => 'emergency_response',
        'summary' => 'LPG leak detection.',
        'body' => 'Use soapy water test.',
        'is_featured' => false,
        'created_at' => now(),
    ]);

    $listResponse = $this->getJson('/api/v1/education/list.php');
    $listResponse->assertOk()->assertJsonIsArray();

    $detailResponse = $this->getJson("/api/v1/education/detail.php?content_id={$article->content_id}");
    $detailResponse->assertOk()
        ->assertJson(['content_id' => $article->content_id]);
});

test('mobile api can fetch emergency contacts', function () {
    EmergencyContact::create([
        'name' => 'Bureau of Fire Protection Lian',
        'category' => 'fire',
        'phone_number' => '160',
        'description' => 'National emergency hotline',
        'is_primary' => true,
        'sort_order' => 1,
    ]);

    $response = $this->getJson('/api/v1/contacts');

    $response->assertOk()
        ->assertJsonIsArray()
        ->assertJsonStructure([
            '*' => [
                'contact_id',
                'name',
                'category',
                'phone_number',
                'description',
                'is_primary',
            ],
        ])
        ->assertJsonFragment([
            'phone_number' => '160',
            'is_primary' => true,
        ]);
});

test('mobile api can fetch barangay contacts', function () {
    $barangay = Barangay::create([
        'barangay_id' => 1,
        'barangay_name' => 'Barangay 1 (Poblacion)',
        'centroid_lat' => 14.0392,
        'centroid_lng' => 120.6389,
    ]);

    BarangayContact::create([
        'barangay_id' => $barangay->barangay_id,
        'name' => 'Hon. Captain Juan Dela Cruz',
        'role' => 'Barangay Captain',
        'phone_number' => '09941614944',
    ]);

    $response = $this->getJson('/api/v1/contacts/barangay');

    $response->assertOk()
        ->assertJsonIsArray()
        ->assertJsonStructure([
            '*' => [
                'contact_id',
                'barangay_id',
                'barangay_name',
                'name',
                'role',
                'phone_number',
            ],
        ])
        ->assertJsonFragment([
            'barangay_name' => 'Barangay 1 (Poblacion)',
            'role' => 'Barangay Captain',
        ]);
});

test('mobile api can fetch public announcements', function () {
    $admin = User::factory()->create();

    Announcement::create([
        'created_by' => $admin->getKey(),
        'title' => 'Fire Prevention Month Opening',
        'content' => 'BFP Lian announces community seminar.',
        'created_at' => now(),
    ]);

    $response = $this->getJson('/api/v1/announcements');

    $response->assertOk()
        ->assertJsonIsArray()
        ->assertJsonStructure([
            '*' => [
                'announcement_id',
                'title',
                'content',
                'created_at',
            ],
        ])
        ->assertJsonFragment([
            'title' => 'Fire Prevention Month Opening',
        ]);
});
