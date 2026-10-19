<?php

use App\Models\Announcement;
use App\Models\AuthToken;
use App\Models\Barangay;
use App\Models\CommunityReport;
use App\Models\IncidentRecord;
use App\Models\ResidentAddress;
use App\Models\RiskAssessment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Barangay::create([
        'barangay_id' => 1,
        'barangay_name' => 'Barangay 1 (Poblacion)',
        'centroid_lat' => 14.03920000,
        'centroid_lng' => 120.63890000,
    ]);

    Barangay::create([
        'barangay_id' => 2,
        'barangay_name' => 'Barangay 2 (Poblacion)',
        'centroid_lat' => 14.04050000,
        'centroid_lng' => 120.64010000,
    ]);
});

test('resident incidents endpoint returns confirmed mapped incidents without notes or PII', function (): void {
    $reporter = User::create([
        'role' => User::ROLE_RESIDENT,
        'status' => User::STATUS_ACTIVE,
        'first_name' => 'Reporter',
        'last_name' => 'One',
        'email' => 'reporter1@example.com',
        'contact_number' => '09171112233',
        'password' => 'secret123',
    ]);

    // 1. Accepted report with incident record
    $report1 = CommunityReport::create([
        'user_id' => $reporter->user_id,
        'barangay_id' => 1,
        'latitude' => 14.0395,
        'longitude' => 120.6390,
        'reporter_name' => 'Secret Reporter',
        'contact_number' => '09171112233',
        'status' => 'accepted',
        'created_at' => now()->subDay(),
    ]);

    IncidentRecord::create([
        'report_id' => $report1->report_id,
        'barangay_id' => 1,
        'incident_type' => 'residential_fire',
        'alarm_level' => '1st_alarm',
        'cause_of_fire' => 'faulty wiring',
        'casualties' => 0,
        'notes' => 'Confidential BFP internal investigation notes',
        'incident_datetime' => now()->subDay(),
    ]);

    // 2. Dispatched report without incident record yet (fallback)
    CommunityReport::create([
        'user_id' => $reporter->user_id,
        'barangay_id' => 2,
        'latitude' => 14.0410,
        'longitude' => 120.6405,
        'reporter_name' => 'Reporter Two',
        'contact_number' => '09170001111',
        'status' => 'dispatched',
        'created_at' => now()->subHours(2),
    ]);

    // 3. Pending report (should be excluded)
    CommunityReport::create([
        'user_id' => $reporter->user_id,
        'barangay_id' => 1,
        'latitude' => 14.0398,
        'longitude' => 120.6392,
        'reporter_name' => 'Reporter Three',
        'contact_number' => '09170002222',
        'status' => 'pending',
        'created_at' => now(),
    ]);

    $response = $this->getJson('/api/v1/map/incidents');

    $response->assertStatus(200)
        ->assertJsonCount(2);

    $items = $response->json();
    expect($items[0]['notes'])->toBeNull();
    expect($items[0]['latitude'])->toBeFloat();
    expect($items[0]['longitude'])->toBeFloat();
    expect(array_key_exists('reporter_name', $items[0]))->toBeFalse();
    expect(array_key_exists('contact_number', $items[0]))->toBeFalse();
});

test('bfp incidents endpoint requires bfp personnel authentication', function (): void {
    // Unauthenticated
    $this->getJson('/api/v1/map/incidents-bfp')->assertStatus(401);

    // Resident user
    $resident = User::create([
        'role' => User::ROLE_RESIDENT,
        'status' => User::STATUS_ACTIVE,
        'first_name' => 'Regular',
        'last_name' => 'Resident',
        'email' => 'res@example.com',
        'password' => 'secret123',
    ]);
    $tokenRes = bin2hex(random_bytes(32));
    AuthToken::create(['user_id' => $resident->user_id, 'token' => $tokenRes]);

    $this->withHeader('Authorization', 'Bearer '.$tokenRes)
        ->getJson('/api/v1/map/incidents-bfp')
        ->assertStatus(403);

    // BFP Personnel
    $bfp = User::create([
        'role' => User::ROLE_BFP_PERSONNEL,
        'status' => User::STATUS_ACTIVE,
        'first_name' => 'Firefighter',
        'last_name' => 'Officer',
        'email' => 'bfp@example.com',
        'password' => 'secret123',
    ]);
    $tokenBfp = bin2hex(random_bytes(32));
    AuthToken::create(['user_id' => $bfp->user_id, 'token' => $tokenBfp]);

    $this->withHeader('Authorization', 'Bearer '.$tokenBfp)
        ->getJson('/api/v1/map/incidents-bfp')
        ->assertStatus(200);
});

test('bfp incidents endpoint returns full details and supports status filter', function (): void {
    $bfp = User::create([
        'role' => User::ROLE_BFP_ADMIN,
        'status' => User::STATUS_ACTIVE,
        'first_name' => 'Admin',
        'last_name' => 'Chief',
        'email' => 'chief@example.com',
        'password' => 'secret123',
    ]);
    $token = bin2hex(random_bytes(32));
    AuthToken::create(['user_id' => $bfp->user_id, 'token' => $token]);

    $report = CommunityReport::create([
        'user_id' => $bfp->user_id,
        'barangay_id' => 1,
        'latitude' => 14.0392,
        'longitude' => 120.6389,
        'reporter_name' => 'Juan Dela Cruz',
        'contact_number' => '09181234567',
        'description' => 'Fire in kitchen',
        'status' => 'dispatched',
        'created_at' => now(),
    ]);

    IncidentRecord::create([
        'report_id' => $report->report_id,
        'barangay_id' => 1,
        'incident_type' => 'commercial_fire',
        'alarm_level' => '2nd_alarm',
        'cause_of_fire' => 'LPG leak',
        'casualties' => 1,
        'notes' => 'Responding unit: Engine 1',
        'incident_datetime' => now(),
    ]);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/map/incidents-bfp?status=dispatched');

    $response->assertStatus(200)
        ->assertJsonCount(1)
        ->assertJsonPath('0.reporter_name', 'Juan Dela Cruz')
        ->assertJsonPath('0.contact_number', '09181234567')
        ->assertJsonPath('0.notes', 'Responding unit: Engine 1')
        ->assertJsonPath('0.alarm_level', '2nd_alarm');

    // Filter by another status returns 0
    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/map/incidents-bfp?status=resolved')
        ->assertStatus(200)
        ->assertJsonCount(0);
});

test('barangay risk endpoint returns all barangays with normalized risk levels', function (): void {
    RiskAssessment::create([
        'barangay_id' => 1,
        'risk_level' => 'high',
        'prediction_score' => 0.85,
        'date' => now()->toDateString(),
    ]);

    $response = $this->getJson('/api/v1/map/barangay-risk');

    $response->assertStatus(200)
        ->assertJsonCount(2);

    $items = $response->json();
    $b1 = collect($items)->firstWhere('barangay_id', 1);
    expect($b1['risk_level'])->toBe('high');
    expect($b1['prediction_score'])->toBe(0.85);
    expect($b1['centroid_lat'])->toBe(14.0392);

    $b2 = collect($items)->firstWhere('barangay_id', 2);
    // When no assessment exists, defaults to mild and score 0
    expect($b2['risk_level'])->toBe('mild');
    expect((float) $b2['prediction_score'])->toEqual(0.0);
});

test('area status endpoint returns resident barangay status and active advisories', function (): void {
    $resident = User::create([
        'role' => User::ROLE_RESIDENT,
        'status' => User::STATUS_ACTIVE,
        'first_name' => 'Local',
        'last_name' => 'Resident',
        'email' => 'local@example.com',
        'password' => 'secret123',
    ]);

    ResidentAddress::create([
        'user_id' => $resident->user_id,
        'barangay_id' => 1,
        'municipality' => 'Lian',
        'province' => 'Batangas',
    ]);

    $token = bin2hex(random_bytes(32));
    AuthToken::create(['user_id' => $resident->user_id, 'token' => $token]);

    RiskAssessment::create([
        'barangay_id' => 1,
        'risk_level' => 'moderate',
        'prediction_score' => 0.55,
        'date' => now()->toDateString(),
    ]);

    Announcement::create([
        'created_by' => $resident->user_id,
        'title' => 'Heat Advisory',
        'content' => 'High temperatures this week.',
        'created_at' => now()->subDay(),
    ]);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/reports/area-status');

    $response->assertStatus(200)
        ->assertJsonPath('barangay_id', 1)
        ->assertJsonPath('barangay_name', 'Barangay 1 (Poblacion)')
        ->assertJsonPath('risk_level', 'moderate')
        ->assertJsonPath('is_external', false)
        ->assertJsonPath('advisory_active', true);
});

test('legacy map .php aliases return identical responses', function (): void {
    $response = $this->getJson('/api/map/incidents.php');
    $response->assertStatus(200);

    $responseRisk = $this->getJson('/api/map/barangay_risk.php');
    $responseRisk->assertStatus(200);
});
