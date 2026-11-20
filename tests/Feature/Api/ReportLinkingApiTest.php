<?php

use App\Models\AuthToken;
use App\Models\Barangay;
use App\Models\CommunityReport;
use App\Models\ReportStatusHistory;
use App\Models\ResidentAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Barangay::create([
        'barangay_id' => 1,
        'barangay_name' => 'Barangay 1 (Poblacion)',
        'centroid_lat' => 14.03920000,
        'centroid_lng' => 120.63890000,
    ]);
});

test('bfp responder can see nearby duplicate candidate reports and link them', function (): void {
    // 1. Create BFP Personnel
    $bfpUser = User::create([
        'role' => User::ROLE_BFP_PERSONNEL,
        'status' => User::STATUS_ACTIVE,
        'first_name' => 'Responder',
        'last_name' => 'Reyes',
        'email' => 'responder@example.com',
        'contact_number' => '09187654321',
        'password' => bcrypt('secret'),
    ]);
    $bfpToken = 'bfp_test_token_123';
    AuthToken::create(['user_id' => $bfpUser->user_id, 'token' => $bfpToken]);

    // 2. Create Resident A and Report 1 (Poblacion, accepted)
    $residentA = User::create([
        'role' => User::ROLE_RESIDENT,
        'status' => User::STATUS_ACTIVE,
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
        'email' => 'juan@example.com',
        'contact_number' => '09171111111',
        'password' => bcrypt('secret'),
    ]);
    ResidentAddress::create([
        'user_id' => $residentA->user_id,
        'house_no_street' => '123 Main St',
        'barangay_id' => 1,
        'municipality' => 'Lian',
        'province' => 'Batangas',
    ]);
    $report1 = CommunityReport::create([
        'user_id' => $residentA->user_id,
        'barangay_id' => 1,
        'reporter_name' => 'Juan Dela Cruz',
        'contact_number' => '09171111111',
        'description' => 'Kitchen fire started on 2nd floor',
        'latitude' => 14.03920000,
        'longitude' => 120.63890000,
        'status' => CommunityReport::STATUS_ACCEPTED,
        'created_at' => now()->subMinutes(10),
    ]);

    // 3. Create Resident B and Report 2 (50m away from Report 1, pending)
    $residentB = User::create([
        'role' => User::ROLE_RESIDENT,
        'status' => User::STATUS_ACTIVE,
        'first_name' => 'Maria',
        'last_name' => 'Santos',
        'email' => 'maria@example.com',
        'contact_number' => '09172222222',
        'password' => bcrypt('secret'),
    ]);
    $report2 = CommunityReport::create([
        'user_id' => $residentB->user_id,
        'barangay_id' => 1,
        'reporter_name' => 'Maria Santos',
        'contact_number' => '09172222222',
        'description' => 'Heavy black smoke from neighboring roof',
        'latitude' => 14.03950000,
        'longitude' => 120.63910000,
        'status' => CommunityReport::STATUS_PENDING,
        'created_at' => now()->subMinutes(5),
    ]);

    // 4. BFP views Report 1 via API
    $response = $this->withHeader('Authorization', "Bearer {$bfpToken}")
        ->getJson("/api/v1/incidents/{$report1->report_id}");

    $response->assertOk();
    $data = $response->json();

    expect($data)->toHaveKey('nearby_unlinked_reports');
    expect($data)->toHaveKey('linked_reports');
    expect($data['nearby_unlinked_reports'])->toHaveCount(1);
    expect($data['nearby_unlinked_reports'][0]['report_id'])->toBe($report2->report_id);
    expect($data['linked_reports'])->toBeEmpty();

    // 5. BFP links Report 2 to Report 1 (testing both /api/v1 and mobile default /api endpoint)
    $linkResponse = $this->withHeader('Authorization', "Bearer {$bfpToken}")
        ->postJson("/api/incidents/{$report1->report_id}/link", [
            'related_report_ids' => [$report2->report_id],
        ]);

    $linkResponse->assertOk();
    $linkedData = $linkResponse->json();

    expect($linkedData['linked_reports'])->toHaveCount(1);
    expect($linkedData['linked_reports'][0]['report_id'])->toBe($report2->report_id);
    expect($linkedData['nearby_unlinked_reports'])->toBeEmpty();

    // Verify report_link table
    expect(DB::table('report_link')->where('main_report_id', $report1->report_id)->where('related_report_id', $report2->report_id)->exists())->toBeTrue();

    // Verify report 2 status synchronized from pending to accepted
    $report2->refresh();
    expect($report2->status)->toBe(CommunityReport::STATUS_ACCEPTED);

    // Verify linking does NOT create a status history record
    expect(ReportStatusHistory::where('report_id', $report2->report_id)->count())->toBe(0);

    // 6. BFP unlinks Report 2
    $unlinkResponse = $this->withHeader('Authorization', "Bearer {$bfpToken}")
        ->postJson("/api/v1/incidents/{$report1->report_id}/unlink", [
            'related_report_id' => $report2->report_id,
        ]);

    $unlinkResponse->assertOk();
    $unlinkedData = $unlinkResponse->json();
    expect($unlinkedData['linked_reports'])->toBeEmpty();
    expect($unlinkedData['nearby_unlinked_reports'])->toHaveCount(1);

    // Verify unlinking does NOT create a status history record
    expect(ReportStatusHistory::where('report_id', $report2->report_id)->count())->toBe(0);
});

test('multiple pending reports can be linked simultaneously into one primary incident', function (): void {
    $bfpUser = User::create([
        'role' => User::ROLE_BFP_PERSONNEL,
        'status' => User::STATUS_ACTIVE,
        'first_name' => 'Officer',
        'last_name' => 'Bautista',
        'email' => 'bautista@example.com',
        'contact_number' => '09187654322',
        'password' => bcrypt('secret'),
    ]);
    $bfpToken = 'bfp_test_token_456';
    AuthToken::create(['user_id' => $bfpUser->user_id, 'token' => $bfpToken]);

    $resident = User::create([
        'role' => User::ROLE_RESIDENT,
        'status' => User::STATUS_ACTIVE,
        'first_name' => 'Citizen',
        'last_name' => 'One',
        'email' => 'citizen@example.com',
        'contact_number' => '09173333333',
        'password' => bcrypt('secret'),
    ]);

    // 3 Pending reports within 100 meters
    $primary = CommunityReport::create([
        'user_id' => $resident->user_id,
        'barangay_id' => 1,
        'reporter_name' => 'Citizen One',
        'contact_number' => '09173333333',
        'description' => 'Commercial fire in market',
        'latitude' => 14.0392,
        'longitude' => 120.6389,
        'status' => CommunityReport::STATUS_PENDING,
        'created_at' => now()->subMinutes(15),
    ]);

    $reportB = CommunityReport::create([
        'user_id' => $resident->user_id,
        'barangay_id' => 1,
        'reporter_name' => 'Citizen Two',
        'contact_number' => '09173333334',
        'description' => 'Flames at store stall 4',
        'latitude' => 14.0394,
        'longitude' => 120.6390,
        'status' => CommunityReport::STATUS_PENDING,
        'created_at' => now()->subMinutes(12),
    ]);

    $reportC = CommunityReport::create([
        'user_id' => $resident->user_id,
        'barangay_id' => 1,
        'reporter_name' => 'Citizen Three',
        'contact_number' => '09173333335',
        'description' => 'Roof collapsing at market',
        'latitude' => 14.0396,
        'longitude' => 120.6392,
        'status' => CommunityReport::STATUS_PENDING,
        'created_at' => now()->subMinutes(10),
    ]);

    // Primary verifies / accepts
    $primary->status = CommunityReport::STATUS_ACCEPTED;
    $primary->save();

    // Link both B and C to primary in a single call
    $linkResponse = $this->withHeader('Authorization', "Bearer {$bfpToken}")
        ->postJson("/api/v1/incidents/{$primary->report_id}/link", [
            'related_report_ids' => [$reportB->report_id, $reportC->report_id],
        ]);

    $linkResponse->assertOk();
    $data = $linkResponse->json();

    expect($data['linked_reports'])->toHaveCount(2);
    expect($data['nearby_unlinked_reports'])->toBeEmpty();

    $reportB->refresh();
    $reportC->refresh();
    expect($reportB->status)->toBe(CommunityReport::STATUS_ACCEPTED);
    expect($reportC->status)->toBe(CommunityReport::STATUS_ACCEPTED);

    // Verify when viewing Report B, it is linked to BOTH Primary and Report C
    $viewBResponse = $this->withHeader('Authorization', "Bearer {$bfpToken}")
        ->getJson("/api/v1/incidents/{$reportB->report_id}");
    $viewBResponse->assertOk();
    $bData = $viewBResponse->json();
    $bLinkedIds = collect($bData['linked_reports'])->pluck('report_id')->all();
    expect($bLinkedIds)->toContain($primary->report_id);
    expect($bLinkedIds)->toContain($reportC->report_id);
    expect($bData['nearby_unlinked_reports'])->toBeEmpty();

    // Verify when viewing Report C, it is linked to BOTH Primary and Report B
    $viewCResponse = $this->withHeader('Authorization', "Bearer {$bfpToken}")
        ->getJson("/api/v1/incidents/{$reportC->report_id}");
    $viewCResponse->assertOk();
    $cData = $viewCResponse->json();
    $cLinkedIds = collect($cData['linked_reports'])->pluck('report_id')->all();
    expect($cLinkedIds)->toContain($primary->report_id);
    expect($cLinkedIds)->toContain($reportB->report_id);
    expect($cData['nearby_unlinked_reports'])->toBeEmpty();
});

test('linking from a pending report adopts the highest status among all linked reports', function (): void {
    $bfpUser = User::create([
        'role' => User::ROLE_BFP_PERSONNEL,
        'status' => User::STATUS_ACTIVE,
        'first_name' => 'Officer',
        'last_name' => 'Dalisay',
        'email' => 'dalisay@example.com',
        'contact_number' => '09187654329',
        'password' => bcrypt('secret'),
    ]);
    $bfpToken = 'bfp_test_token_highest_status';
    AuthToken::create(['user_id' => $bfpUser->user_id, 'token' => $bfpToken]);

    $resident = User::create([
        'role' => User::ROLE_RESIDENT,
        'status' => User::STATUS_ACTIVE,
        'first_name' => 'Citizen',
        'last_name' => 'Test',
        'email' => 'citizen_highest@example.com',
        'contact_number' => '09174444444',
        'password' => bcrypt('secret'),
    ]);

    // Current report #25 is PENDING
    $report25 = CommunityReport::create([
        'user_id' => $resident->user_id,
        'barangay_id' => 1,
        'reporter_name' => 'Reporter 25',
        'contact_number' => '09174444444',
        'description' => 'Smoke sighting',
        'latitude' => 14.0392,
        'longitude' => 120.6389,
        'status' => CommunityReport::STATUS_PENDING,
        'created_at' => now()->subMinutes(20),
    ]);

    // Related 1: PENDING
    $report26 = CommunityReport::create([
        'user_id' => $resident->user_id,
        'barangay_id' => 1,
        'reporter_name' => 'Reporter 26',
        'contact_number' => '09174444445',
        'description' => 'Smoke near plaza',
        'latitude' => 14.0393,
        'longitude' => 120.6390,
        'status' => CommunityReport::STATUS_PENDING,
        'created_at' => now()->subMinutes(18),
    ]);

    // Related 2: ACCEPTED
    $report27 = CommunityReport::create([
        'user_id' => $resident->user_id,
        'barangay_id' => 1,
        'reporter_name' => 'Reporter 27',
        'contact_number' => '09174444446',
        'description' => 'Fire confirmed on 2nd floor',
        'latitude' => 14.0394,
        'longitude' => 120.6391,
        'status' => CommunityReport::STATUS_ACCEPTED,
        'created_at' => now()->subMinutes(15),
    ]);

    // Related 3: DISPATCHED (highest)
    $report28 = CommunityReport::create([
        'user_id' => $resident->user_id,
        'barangay_id' => 1,
        'reporter_name' => 'Reporter 28',
        'contact_number' => '09174444447',
        'description' => 'Fire engine en route',
        'latitude' => 14.0395,
        'longitude' => 120.6392,
        'status' => CommunityReport::STATUS_DISPATCHED,
        'created_at' => now()->subMinutes(12),
    ]);

    // Link reports from pending report #25's screen
    $response = $this->withHeader('Authorization', "Bearer {$bfpToken}")
        ->postJson("/api/v1/incidents/{$report25->report_id}/link", [
            'related_report_ids' => [$report26->report_id, $report27->report_id, $report28->report_id],
        ]);

    $response->assertOk();
    $data = $response->json();

    // Report #25 should now be upgraded to DISPATCHED
    expect($data['status'])->toBe(CommunityReport::STATUS_DISPATCHED);
    expect($data['linked_reports'])->toHaveCount(3);

    // Verify all cluster members were upgraded to the highest status (DISPATCHED)
    $report25->refresh();
    $report26->refresh();
    $report27->refresh();
    $report28->refresh();

    expect($report25->status)->toBe(CommunityReport::STATUS_DISPATCHED);
    expect($report26->status)->toBe(CommunityReport::STATUS_DISPATCHED);
    expect($report27->status)->toBe(CommunityReport::STATUS_DISPATCHED);
    expect($report28->status)->toBe(CommunityReport::STATUS_DISPATCHED);
});
