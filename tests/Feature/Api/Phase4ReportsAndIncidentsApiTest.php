<?php

use App\Models\AuthToken;
use App\Models\Barangay;
use App\Models\CommunityReport;
use App\Models\Notification;
use App\Models\ReportStatusHistory;
use App\Models\ResidentAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('public');

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

    Barangay::create([
        'barangay_id' => 9,
        'barangay_name' => 'Barangay 9 (Poblacion)',
        'centroid_lat' => 14.03920000,
        'centroid_lng' => 120.63890000,
    ]);
});

function createTestResident(string $email = 'resident@example.com'): array
{
    $user = User::create([
        'role' => User::ROLE_RESIDENT,
        'status' => User::STATUS_ACTIVE,
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
        'email' => $email,
        'contact_number' => '09171234567',
        'password' => bcrypt('password123'),
    ]);

    ResidentAddress::create([
        'user_id' => $user->user_id,
        'house_no_street' => '123 Main St',
        'barangay_id' => 1,
        'municipality' => 'Lian',
        'province' => 'Batangas',
    ]);

    $token = bin2hex(random_bytes(32));
    AuthToken::create(['user_id' => $user->user_id, 'token' => $token]);

    return [$user, $token];
}

function createTestBfpPersonnel(string $email = 'bfp@example.com', string $role = User::ROLE_BFP_PERSONNEL): array
{
    $user = User::create([
        'role' => $role,
        'status' => User::STATUS_ACTIVE,
        'first_name' => 'Officer',
        'last_name' => 'Reyes',
        'email' => $email,
        'contact_number' => '09187654321',
        'password' => bcrypt('password123'),
    ]);

    $token = bin2hex(random_bytes(32));
    AuthToken::create(['user_id' => $user->user_id, 'token' => $token]);

    return [$user, $token];
}

test('resident can submit a report with photo and dispatches notifications', function (): void {
    [$resident, $token] = createTestResident();
    [$bfp, $bfpToken] = createTestBfpPersonnel();

    $photo = UploadedFile::fake()->image('fire_smoke.jpg', 600, 400);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->post('/api/v1/reports', [
            'description' => 'Smoke coming from residential roof',
            'latitude' => 14.0392,
            'longitude' => 120.6389,
            'barangay_id' => 1,
            'location_name' => 'Barangay 1 (Poblacion)',
            'report_image' => $photo,
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('status', 'pending')
        ->assertJsonPath('barangay_id', 1)
        ->assertJsonPath('barangay_name', 'Barangay 1 (Poblacion)');

    $reportId = $response->json('report_id');
    expect($reportId)->toBeGreaterThan(0);

    // Verify report in database
    $this->assertDatabaseHas('community_report', [
        'report_id' => $reportId,
        'user_id' => $resident->user_id,
        'status' => 'pending',
    ]);

    // Verify report status history
    $this->assertDatabaseHas('report_status_history', [
        'report_id' => $reportId,
        'status' => 'pending',
        'notes' => 'Report submitted by resident.',
    ]);

    // Verify resident received confirmation notification
    $this->assertDatabaseHas('notification', [
        'user_id' => $resident->user_id,
        'title' => 'Report Submitted',
        'notification_type' => 'incident_alert',
    ]);

    // Verify BFP personnel received alert notification
    $this->assertDatabaseHas('notification', [
        'user_id' => $bfp->user_id,
        'notification_type' => 'incident_alert',
    ]);
});

test('resident can list own reports, view detail, status history, and cannot view others reports', function (): void {
    [$resident1, $token1] = createTestResident('res1@example.com');
    [$resident2, $token2] = createTestResident('res2@example.com');

    $report1 = CommunityReport::create([
        'user_id' => $resident1->user_id,
        'reporter_name' => 'Juan Dela Cruz',
        'contact_number' => '09171234567',
        'description' => 'Grass fire',
        'latitude' => 14.0392,
        'longitude' => 120.6389,
        'barangay_id' => 1,
        'status' => 'pending',
    ]);

    ReportStatusHistory::create([
        'report_id' => $report1->report_id,
        'status' => 'pending',
        'notes' => 'Report submitted.',
        'created_at' => now(),
    ]);

    // Resident 1 list
    $resList = $this->withHeader('Authorization', 'Bearer '.$token1)
        ->getJson('/api/v1/reports');
    $resList->assertStatus(200)->assertJsonCount(1);
    expect($resList->json('0.report_id'))->toBe($report1->report_id);

    // Resident 1 view detail
    $resDetail = $this->withHeader('Authorization', 'Bearer '.$token1)
        ->getJson('/api/v1/reports/'.$report1->report_id);
    $resDetail->assertStatus(200)->assertJsonPath('report_id', $report1->report_id);

    // Resident 1 view history
    $resHist = $this->withHeader('Authorization', 'Bearer '.$token1)
        ->getJson('/api/v1/reports/'.$report1->report_id.'/history');
    $resHist->assertStatus(200)->assertJsonCount(1);

    // Resident 2 unauthorized to view Resident 1's report
    $this->withHeader('Authorization', 'Bearer '.$token2)
        ->getJson('/api/v1/reports/'.$report1->report_id)
        ->assertStatus(403);

    $this->withHeader('Authorization', 'Bearer '.$token2)
        ->getJson('/api/v1/reports/'.$report1->report_id.'/history')
        ->assertStatus(403);
});

test('bfp operations workflow: verify, advance status, update details, mark invalid, and create manual', function (): void {
    [$resident, $resToken] = createTestResident();
    [$bfp, $bfpToken] = createTestBfpPersonnel();

    // 1. Create a pending report
    $report = CommunityReport::create([
        'user_id' => $resident->user_id,
        'reporter_name' => 'Juan Dela Cruz',
        'contact_number' => '09171234567',
        'description' => 'Kitchen fire',
        'latitude' => 14.0392,
        'longitude' => 120.6389,
        'barangay_id' => 1,
        'status' => 'pending',
        'created_at' => now(),
    ]);

    // 2. BFP verifies report (moves pending -> accepted)
    $resVerify = $this->withHeader('Authorization', 'Bearer '.$bfpToken)
        ->postJson('/api/v1/incidents/'.$report->report_id.'/verify', [
            'notes' => 'Confirmed with barangay captain.',
        ]);

    $resVerify->assertStatus(200)
        ->assertJsonPath('status', 'accepted');

    // Resident notified of accepted status
    $this->assertDatabaseHas('notification', [
        'user_id' => $resident->user_id,
        'title' => 'Report Status Updated',
        'message' => 'Your fire report has been accepted by BFP personnel.',
    ]);

    // 3. BFP advances status to dispatched then resolved
    $resDispatched = $this->withHeader('Authorization', 'Bearer '.$bfpToken)
        ->postJson('/api/v1/incidents/'.$report->report_id.'/status', [
            'status' => 'dispatched',
            'notes' => 'Engine 1 en route.',
        ]);
    $resDispatched->assertStatus(200)->assertJsonPath('status', 'dispatched');

    $resResolved = $this->withHeader('Authorization', 'Bearer '.$bfpToken)
        ->postJson('/api/v1/incidents/'.$report->report_id.'/status', [
            'status' => 'resolved',
            'notes' => 'Fire extinguished.',
        ]);
    $resResolved->assertStatus(200)->assertJsonPath('status', 'resolved');

    // Automatically created incident_record upon resolve
    $this->assertDatabaseHas('incident_record', [
        'report_id' => $report->report_id,
        'barangay_id' => 1,
    ]);

    // 4. BFP updates incident details
    $resDetails = $this->withHeader('Authorization', 'Bearer '.$bfpToken)
        ->putJson('/api/v1/incidents/'.$report->report_id.'/details', [
            'incident_type' => 'residential_fire',
            'alarm_level' => '1st_alarm',
            'cause_of_fire' => 'Faulty wiring',
            'casualties' => 0,
            'notes' => 'Minor property damage.',
        ]);

    $resDetails->assertStatus(200)
        ->assertJsonPath('incident_type', 'residential_fire')
        ->assertJsonPath('alarm_level', '1st_alarm')
        ->assertJsonPath('cause_of_fire', 'Faulty wiring');

    // 5. BFP status history with changed_by_name
    $resHist = $this->withHeader('Authorization', 'Bearer '.$bfpToken)
        ->getJson('/api/v1/incidents/'.$report->report_id.'/history');

    $resHist->assertStatus(200);
    $historyEntries = $resHist->json('history');
    expect(count($historyEntries))->toBeGreaterThan(1);
    expect($historyEntries[0]['changed_by_name'])->toBe('Officer Reyes');

    // 6. BFP adds evidence photo
    $evidencePhoto = UploadedFile::fake()->image('evidence1.jpg');
    $resEvidence = $this->withHeader('Authorization', 'Bearer '.$bfpToken)
        ->post('/api/v1/incidents/'.$report->report_id.'/evidence', [
            'photo' => $evidencePhoto,
            'caption' => 'Post-fire scene',
        ]);
    $resEvidence->assertStatus(200)
        ->assertJsonPath('message', 'Evidence added successfully.');

    // 7. BFP mark another report invalid
    $report2 = CommunityReport::create([
        'user_id' => $resident->user_id,
        'reporter_name' => 'Juan Dela Cruz',
        'contact_number' => '09171234567',
        'description' => 'False sighting',
        'latitude' => 14.0392,
        'longitude' => 120.6389,
        'barangay_id' => 1,
        'status' => 'pending',
    ]);

    $resInvalid = $this->withHeader('Authorization', 'Bearer '.$bfpToken)
        ->postJson('/api/v1/incidents/'.$report2->report_id.'/invalid', [
            'reason' => 'Controlled rubbish burning, no hazard.',
        ]);
    $resInvalid->assertStatus(200)->assertJsonPath('status', 'invalid');

    // 8. BFP manual incident entry
    $resManual = $this->withHeader('Authorization', 'Bearer '.$bfpToken)
        ->post('/api/v1/incidents/manual', [
            'barangay_id' => 2,
            'latitude' => 14.0405,
            'longitude' => 120.6401,
            'description' => 'Walk-in report of market fire',
            'incident_type' => 'commercial_fire',
            'alarm_level' => '2nd_alarm',
            'data_time' => now()->toIso8601String(),
            'cause_of_fire' => 'Electrical short circuit',
            'casualties' => 0,
            'notes' => 'Assisted by local brigade.',
        ]);

    $resManual->assertStatus(201)
        ->assertJsonPath('status', 'accepted')
        ->assertJsonPath('incident_type', 'commercial_fire')
        ->assertJsonPath('barangay_id', 2);
});

test('profile endpoints support view, update, photo upload, and photo deletion', function (): void {
    [$user, $token] = createTestResident();

    // 1. View Profile
    $resGet = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/profile');

    $resGet->assertStatus(200)
        ->assertJsonPath('user_id', $user->user_id)
        ->assertJsonPath('first_name', 'Juan')
        ->assertJsonPath('address.barangay_name', 'Barangay 1 (Poblacion)');

    // 2. Update Profile fields
    $resUpdate = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/profile', [
            'first_name' => 'Juanito',
            'last_name' => 'Dela Cruz Jr.',
            'contact_number' => '09198765432',
            'house_no_street' => '456 New St',
            'barangay_id' => 2,
        ]);

    $resUpdate->assertStatus(200)
        ->assertJsonPath('first_name', 'Juanito')
        ->assertJsonPath('contact_number', '09198765432')
        ->assertJsonPath('address.barangay_id', 2)
        ->assertJsonPath('address.barangay_name', 'Barangay 2 (Poblacion)');

    // 3. Upload Profile Photo
    $photo = UploadedFile::fake()->image('profile.jpg');
    $resPhoto = $this->withHeader('Authorization', 'Bearer '.$token)
        ->post('/api/v1/profile/photo', ['photo' => $photo]);

    $resPhoto->assertStatus(200);
    expect($resPhoto->json('profile_image'))->not->toBeNull();

    // 4. Remove Profile Photo
    $resRemove = $this->withHeader('Authorization', 'Bearer '.$token)
        ->putJson('/api/v1/profile', ['remove_photo' => true]);

    $resRemove->assertStatus(200)
        ->assertJsonPath('profile_image', null);
});

test('notification endpoints return list and allow marking single or all as read', function (): void {
    [$resident, $token] = createTestResident();

    $report = CommunityReport::create([
        'user_id' => $resident->user_id,
        'reporter_name' => 'Juan',
        'contact_number' => '09171234567',
        'latitude' => 14.0392,
        'longitude' => 120.6389,
        'description' => 'Test report',
        'status' => 'pending',
    ]);

    $n1 = Notification::create([
        'user_id' => $resident->user_id,
        'title' => 'First Notification',
        'message' => 'A new fire report (#'.$report->report_id.') was submitted.',
        'notification_type' => 'incident_alert',
        'is_read' => false,
    ]);

    $n2 = Notification::create([
        'user_id' => $resident->user_id,
        'title' => 'Second Notification',
        'message' => 'Status update message.',
        'notification_type' => 'status_update',
        'is_read' => false,
    ]);

    // List notifications
    $resList = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/notifications');

    $resList->assertStatus(200)->assertJsonCount(2);
    expect($resList->json('0.report_id'))->toBe($report->report_id);
    expect($resList->json('0.latitude'))->toBe(14.0392);

    // Mark single notification read
    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/notifications/mark-read', ['notification_id' => $n1->notification_id])
        ->assertStatus(200)
        ->assertJsonPath('message', 'Notification marked as read.');

    expect($n1->fresh()->is_read)->toBeTrue();
    expect($n2->fresh()->is_read)->toBeFalse();

    // Mark all as read
    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/notifications/mark-read', ['all' => true])
        ->assertStatus(200)
        ->assertJsonPath('message', 'All notifications marked as read.');

    expect($n2->fresh()->is_read)->toBeTrue();
});

test('bfp dashboard analytics returns aggregated incident metrics', function (): void {
    [$bfp, $bfpToken] = createTestBfpPersonnel();

    // Pending incident
    CommunityReport::create([
        'user_id' => $bfp->user_id,
        'reporter_name' => 'Reporter',
        'contact_number' => '09171234567',
        'latitude' => 14.0392,
        'longitude' => 120.6389,
        'status' => 'pending',
        'created_at' => now(),
    ]);

    // Dispatched incident
    CommunityReport::create([
        'user_id' => $bfp->user_id,
        'reporter_name' => 'Reporter',
        'contact_number' => '09171234567',
        'latitude' => 14.0392,
        'longitude' => 120.6389,
        'status' => 'dispatched',
        'created_at' => now(),
    ]);

    // Resolved incident today
    CommunityReport::create([
        'user_id' => $bfp->user_id,
        'reporter_name' => 'Reporter',
        'contact_number' => '09171234567',
        'latitude' => 14.0392,
        'longitude' => 120.6389,
        'status' => 'resolved',
        'created_at' => now(),
    ]);

    $response = $this->withHeader('Authorization', 'Bearer '.$bfpToken)
        ->getJson('/api/v1/dashboard/analytics');

    $response->assertStatus(200)
        ->assertJsonPath('active_incidents', 2)
        ->assertJsonPath('pending_verification', 1)
        ->assertJsonPath('resolved_today', 1)
        ->assertJsonPath('total_this_month', 1);
});

test('legacy .php endpoint aliases match behavior', function (): void {
    [$resident, $token] = createTestResident();
    [$bfp, $bfpToken] = createTestBfpPersonnel();

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/reports/list.php')
        ->assertStatus(200);

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/profile/get.php')
        ->assertStatus(200);

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/notifications/list.php')
        ->assertStatus(200);

    $this->withHeader('Authorization', 'Bearer '.$bfpToken)
        ->getJson('/api/incidents/list.php')
        ->assertStatus(200);

    $this->withHeader('Authorization', 'Bearer '.$bfpToken)
        ->getJson('/api/dashboard/analytics.php')
        ->assertStatus(200);
});
