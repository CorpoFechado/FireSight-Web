<?php

use App\Models\Barangay;
use App\Models\BarangayContact;
use App\Models\BfpPersonnelDetails;
use App\Models\CommunityReport;
use App\Models\IncidentRecord;
use App\Models\ReportStatusHistory;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->bfpAdmin()->create();
    $this->staff = User::factory()->bfpPersonnel()->create();
    $this->resident = User::factory()->create(['role' => 'resident']);

    $this->barangay = Barangay::firstOrCreate(
        ['barangay_id' => 101],
        ['barangay_name' => 'Barangay Test', 'latitude' => 13.9, 'longitude' => 120.6]
    );
});

test('admin can accept a pending report and assign barangay without creating incident_record', function () {
    $report = CommunityReport::create([
        'user_id' => $this->resident->id,
        'reporter_name' => 'Juan Dela Cruz',
        'contact_number' => '09123456789',
        'description' => 'Smoke sighting',
        'latitude' => 13.95,
        'longitude' => 120.65,
        'status' => CommunityReport::STATUS_PENDING,
    ]);

    $response = $this->actingAs($this->admin)
        ->post(route('incidents.accept', $report), [
            'barangay_id' => $this->barangay->barangay_id,
        ]);

    $response->assertRedirect();
    $report->refresh();

    expect($report->status)->toBe(CommunityReport::STATUS_ACCEPTED)
        ->and($report->barangay_id)->toBe($this->barangay->barangay_id);

    // CRITICAL: incident_record must NOT be created at accept time
    expect(IncidentRecord::where('report_id', $report->report_id)->count())->toBe(0);

    // Verify status history was recorded
    expect(ReportStatusHistory::where('report_id', $report->report_id)
        ->where('status', CommunityReport::STATUS_ACCEPTED)
        ->count())->toBe(1);
});

test('admin can invalidate a pending report without creating incident_record', function () {
    $report = CommunityReport::create([
        'user_id' => $this->resident->id,
        'reporter_name' => 'Pedro Penduko',
        'contact_number' => '09123456789',
        'description' => 'False alarm report',
        'latitude' => 13.95,
        'longitude' => 120.65,
        'status' => CommunityReport::STATUS_PENDING,
    ]);

    $response = $this->actingAs($this->admin)
        ->post(route('incidents.invalidate', $report));

    $response->assertRedirect();
    $report->refresh();

    expect($report->status)->toBe(CommunityReport::STATUS_INVALID);
    expect(IncidentRecord::where('report_id', $report->report_id)->count())->toBe(0);
    expect(ReportStatusHistory::where('report_id', $report->report_id)
        ->where('status', CommunityReport::STATUS_INVALID)
        ->count())->toBe(1);
});

test('staff can advance accepted report to dispatched without creating incident_record', function () {
    $report = CommunityReport::create([
        'user_id' => $this->resident->id,
        'reporter_name' => 'Maria Clara',
        'contact_number' => '09123456789',
        'description' => 'Jeep fire',
        'latitude' => 13.95,
        'longitude' => 120.65,
        'barangay_id' => $this->barangay->barangay_id,
        'status' => CommunityReport::STATUS_ACCEPTED,
    ]);

    $response = $this->actingAs($this->staff)
        ->patch(route('incidents.updateStatus', $report), [
            'status' => CommunityReport::STATUS_DISPATCHED,
        ]);

    $response->assertRedirect();
    $report->refresh();

    expect($report->status)->toBe(CommunityReport::STATUS_DISPATCHED);
    expect(IncidentRecord::where('report_id', $report->report_id)->count())->toBe(0);
    expect(ReportStatusHistory::where('report_id', $report->report_id)
        ->where('status', CommunityReport::STATUS_DISPATCHED)
        ->count())->toBe(1);
});

test('resolving a dispatched report requires incident details and inserts incident_record', function () {
    $report = CommunityReport::create([
        'user_id' => $this->resident->id,
        'reporter_name' => 'Crisostomo Ibarra',
        'contact_number' => '09123456789',
        'description' => 'Kitchen fire',
        'latitude' => 13.95,
        'longitude' => 120.65,
        'barangay_id' => $this->barangay->barangay_id,
        'status' => CommunityReport::STATUS_DISPATCHED,
    ]);

    $response = $this->actingAs($this->staff)
        ->post(route('incidents.resolve', $report), [
            'incident_type' => 'residential_fire',
            'severity_level' => 'moderate',
            'cause_of_fire' => 'Gas stove unattended',
            'casualties' => 0,
            'notes' => 'Extinguished by responding fire personnel.',
        ]);

    $response->assertRedirect();
    $report->refresh();

    expect($report->status)->toBe(CommunityReport::STATUS_RESOLVED);

    $record = IncidentRecord::where('report_id', $report->report_id)->first();
    expect($record)->not->toBeNull()
        ->and($record->incident_type)->toBe('residential_fire')
        ->and($record->severity_level)->toBe('moderate')
        ->and($record->cause_of_fire)->toBe('Gas stove unattended')
        ->and($record->casualties)->toBe(0)
        ->and($record->notes)->toBe('Extinguished by responding fire personnel.');

    expect(ReportStatusHistory::where('report_id', $report->report_id)
        ->where('status', CommunityReport::STATUS_RESOLVED)
        ->count())->toBe(1);
});

test('resolve validates incident_type to canonical mobile fire types', function () {
    $report = CommunityReport::create([
        'user_id' => $this->resident->id,
        'reporter_name' => 'Test',
        'contact_number' => '09123456789',
        'description' => 'Test fire',
        'latitude' => 13.95,
        'longitude' => 120.65,
        'barangay_id' => $this->barangay->barangay_id,
        'status' => CommunityReport::STATUS_DISPATCHED,
    ]);

    $response = $this->actingAs($this->staff)
        ->post(route('incidents.resolve', $report), [
            'incident_type' => 'structural', // legacy type, should fail validation
            'severity_level' => 'moderate',
        ]);

    $response->assertSessionHasErrors('incident_type');
    expect(IncidentRecord::where('report_id', $report->report_id)->count())->toBe(0);
});

test('resolve accepts critical severity_level and normalizes legacy medium to moderate', function () {
    $report1 = CommunityReport::create([
        'user_id' => $this->resident->id,
        'reporter_name' => 'Test 1',
        'contact_number' => '09123456789',
        'description' => 'Test fire 1',
        'latitude' => 13.95,
        'longitude' => 120.65,
        'barangay_id' => $this->barangay->barangay_id,
        'status' => CommunityReport::STATUS_DISPATCHED,
    ]);

    $this->actingAs($this->staff)
        ->post(route('incidents.resolve', $report1), [
            'incident_type' => 'residential_fire',
            'severity_level' => 'critical',
        ])
        ->assertRedirect();

    $record1 = IncidentRecord::where('report_id', $report1->report_id)->first();
    expect($record1->severity_level)->toBe('critical');

    $report2 = CommunityReport::create([
        'user_id' => $this->resident->id,
        'reporter_name' => 'Test 2',
        'contact_number' => '09123456789',
        'description' => 'Test fire 2',
        'latitude' => 13.95,
        'longitude' => 120.65,
        'barangay_id' => $this->barangay->barangay_id,
        'status' => CommunityReport::STATUS_DISPATCHED,
    ]);

    $this->actingAs($this->staff)
        ->post(route('incidents.resolve', $report2), [
            'incident_type' => 'residential_fire',
            'severity_level' => 'medium', // legacy mobile value
        ])
        ->assertRedirect();

    $record2 = IncidentRecord::where('report_id', $report2->report_id)->first();
    expect($record2->severity_level)->toBe('moderate');
});

test('incidents show renders with actual statusHistory from database including personnel and notes', function () {
    $officer = User::factory()->bfpPersonnel()->create([
        'first_name' => 'Maria',
        'last_name' => 'Santos',
    ]);
    BfpPersonnelDetails::create([
        'user_id' => $officer->id,
        'rank' => 'Fire Officer 2',
        'station_assigned' => 'BFP Lian Fire Station',
        'employee_number' => 'EMP-002',
    ]);

    $report = CommunityReport::create([
        'user_id' => $this->resident->id,
        'reporter_name' => 'Jennifer Rivera',
        'contact_number' => '09123456789',
        'description' => 'Smoke sighting',
        'latitude' => 13.95,
        'longitude' => 120.65,
        'barangay_id' => $this->barangay->barangay_id,
        'status' => CommunityReport::STATUS_ACCEPTED,
    ]);

    ReportStatusHistory::create([
        'report_id' => $report->report_id,
        'status' => CommunityReport::STATUS_PENDING,
        'notes' => 'Submitted via mobile app.',
        'changed_by' => $this->resident->id,
        'created_at' => now()->subMinutes(10),
    ]);

    ReportStatusHistory::create([
        'report_id' => $report->report_id,
        'status' => CommunityReport::STATUS_ACCEPTED,
        'notes' => 'Report verified by duty verifier.',
        'changed_by' => $officer->id,
        'created_at' => now()->subMinutes(5),
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('incidents.show', $report));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('incidents/show')
            ->has('statusHistory', 2)
            ->where('statusHistory.0.status', 'pending')
            ->where('statusHistory.0.notes', 'Submitted via mobile app.')
            ->where('statusHistory.1.status', 'accepted')
            ->where('statusHistory.1.notes', 'Report verified by duty verifier.')
            ->where('statusHistory.1.changed_by', 'Fire Officer 2 Maria Santos')
            ->where('statusHistory.1.personnel.rank', 'Fire Officer 2')
        );
});

test('incidents index and show serialize mobile app ai verification results', function () {
    $verifiedAt = now()->subMinutes(15)->format('Y-m-d H:i');

    $report = CommunityReport::create([
        'user_id' => $this->resident->id,
        'reporter_name' => 'Mobile User',
        'contact_number' => '09123456789',
        'description' => 'AI detected fire in mobile upload',
        'latitude' => 13.95,
        'longitude' => 120.65,
        'status' => CommunityReport::STATUS_PENDING,
        'ai_fire_label' => 'fire',
        'ai_fire_confidence' => 0.9845,
        'ai_verified_at' => $verifiedAt,
    ]);

    // Test show page
    $this->actingAs($this->admin)
        ->get(route('incidents.show', $report))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('incidents/show')
            ->where('report.ai_fire_label', 'fire')
            ->where('report.ai_fire_confidence', 0.9845)
            ->where('report.ai_verified_at', $verifiedAt)
        );

    // Test index page
    $this->actingAs($this->admin)
        ->get(route('incidents.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('incidents/index')
            ->has('reports.data.0', fn ($r) => $r
                ->where('report_id', $report->report_id)
                ->where('ai_fire_label', 'fire')
                ->where('ai_fire_confidence', 0.9845)
                ->where('ai_verified_at', $verifiedAt)
                ->etc()
            )
        );
});

test('incidents show includes barangay contact person and number', function () {
    BarangayContact::create([
        'barangay_id' => $this->barangay->barangay_id,
        'name' => 'Hon. Capt. Juanito',
        'role' => 'Barangay Captain',
        'phone_number' => '09123456780',
    ]);

    $report = CommunityReport::create([
        'user_id' => $this->resident->id,
        'reporter_name' => 'Test Resident',
        'contact_number' => '09123456789',
        'description' => 'Fire report in test barangay',
        'latitude' => 13.95,
        'longitude' => 120.65,
        'barangay_id' => $this->barangay->barangay_id,
        'status' => CommunityReport::STATUS_ACCEPTED,
    ]);

    $this->actingAs($this->admin)
        ->get(route('incidents.show', $report))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('incidents/show')
            ->where('report.barangay_contact_person', 'Hon. Capt. Juanito')
            ->where('report.barangay_contact_number', '09123456780')
            ->where('report.barangay_contact_role', 'Barangay Captain')
            ->has('report.barangay_contacts', 1)
        );
});

test('staff and admin can update incident assessment details after resolution', function () {
    $report = CommunityReport::create([
        'user_id' => $this->resident->id,
        'reporter_name' => 'Resolved Test',
        'contact_number' => '09123456789',
        'description' => 'Already resolved incident',
        'latitude' => 13.95,
        'longitude' => 120.65,
        'barangay_id' => $this->barangay->barangay_id,
        'status' => CommunityReport::STATUS_RESOLVED,
    ]);

    $record = IncidentRecord::create([
        'report_id' => $report->report_id,
        'barangay_id' => $this->barangay->barangay_id,
        'incident_datetime' => now()->subHour(),
        'incident_type' => 'residential_fire',
        'severity_level' => 'low',
        'cause_of_fire' => 'Initial guess',
        'casualties' => 0,
        'notes' => 'Initial notes',
    ]);

    $response = $this->actingAs($this->staff)
        ->patch(route('incidents.updateDetails', $report), [
            'incident_type' => 'commercial_fire',
            'severity_level' => 'high',
            'cause_of_fire' => 'Faulty electrical wiring behind refrigerator',
            'casualties' => 2,
            'notes' => 'Updated investigation report after full inspection.',
        ]);

    $response->assertRedirect();
    $record->refresh();

    expect($record->incident_type)->toBe('commercial_fire')
        ->and($record->severity_level)->toBe('high')
        ->and($record->cause_of_fire)->toBe('Faulty electrical wiring behind refrigerator')
        ->and($record->casualties)->toBe(2)
        ->and($record->notes)->toBe('Updated investigation report after full inspection.');

    expect(ReportStatusHistory::where('report_id', $report->report_id)
        ->where('notes', 'Incident assessment details updated by BFP personnel.')
        ->count())->toBe(1);
});
