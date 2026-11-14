<?php

use App\Models\Barangay;
use App\Models\BfpPersonnelDetails;
use App\Models\CommunityReport;
use App\Models\IncidentDispatch;
use App\Models\IncidentRecord;
use App\Models\Notification;
use App\Models\ReportEvidence;
use App\Models\ReportStatusHistory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    $this->admin = User::factory()->bfpAdmin()->create([
        'first_name' => 'Felonia Amor',
        'last_name' => 'Barcelon',
    ]);
    BfpPersonnelDetails::create([
        'user_id' => $this->admin->getKey(),
        'rank' => 'Senior Fire Officer 4',
        'station_assigned' => 'BFP Lian Fire Station',
        'employee_number' => 'EMP-ADMIN-1',
    ]);

    $this->staff = User::factory()->bfpPersonnel()->create([
        'first_name' => 'Dexter',
        'last_name' => 'Pantas',
    ]);
    BfpPersonnelDetails::create([
        'user_id' => $this->staff->getKey(),
        'rank' => 'Fire Officer 1',
        'station_assigned' => 'BFP Lian Fire Station',
        'employee_number' => 'EMP-STAFF-1',
    ]);

    $this->barangay = Barangay::firstOrCreate(
        ['barangay_id' => 1],
        [
            'barangay_name' => 'Barangay 1 (Poblacion)',
            'centroid_lat' => 14.0392,
            'centroid_lng' => 120.6389,
        ]
    );
});

test('incidents index passes active personnel and available trucks for manual report modal', function () {
    $this->actingAs($this->admin)
        ->get(route('incidents.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('incidents/index')
            ->has('availableTrucks', 3)
            ->has('personnel', 1, fn ($p) => $p
                ->where('user_id', $this->staff->getKey())
                ->where('name', 'Dexter Pantas')
                ->where('rank', 'Fire Officer 1')
            )
        );
});

test('admin can create a manual resolved incident with auto-detected barangay, trucks, personnel, assessment, and photos', function () {
    Storage::fake('public');

    $photo1 = UploadedFile::fake()->image('scene1.jpg');
    $photo2 = UploadedFile::fake()->image('scene2.png');

    $response = $this->actingAs($this->admin)
        ->post(route('incidents.storeManual'), [
            'reporter_name' => 'Juan Dela Cruz',
            'contact_number' => '09171234567',
            'incident_date' => '2026-11-10',
            'description' => 'Phone-in report of a kitchen fire already extinguished upon arrival.',
            'latitude' => 14.0392,
            'longitude' => 120.6389,
            'photos' => [$photo1, $photo2],
            'trucks' => ['Fire Truck 1', 'Fire Truck 2'],
            'personnel_ids' => [$this->staff->getKey()],
            'establishment_name' => 'Dela Cruz Residence',
            'owner_name' => 'Juan Dela Cruz',
            'occupant_name' => 'Maria Dela Cruz',
            'time_fire_started' => '2026-11-10 13:15:00',
            'time_fire_out' => '2026-11-10 13:45:00',
            'incident_type' => 'residential_fire',
            'alarm_level' => '1st_alarm',
            'cause_of_fire' => 'Overheated cooking oil',
            'injuries' => 0,
            'fatalities' => 0,
            'estimated_damage' => 25000,
            'notes' => 'Overhaul completed and scene cleared.',
        ]);

    $report = CommunityReport::latest('report_id')->first();
    expect($report)->not->toBeNull();

    $response->assertRedirect(route('incidents.show', $report));

    expect($report->status)->toBe(CommunityReport::STATUS_RESOLVED)
        ->and($report->reporter_name)->toBe('Juan Dela Cruz')
        ->and($report->barangay_id)->toBe($this->barangay->barangay_id)
        ->and($report->device_latitude)->toBeNull()
        ->and($report->device_longitude)->toBeNull()
        ->and($report->ai_fire_label)->toBeNull();

    $record = IncidentRecord::where('report_id', $report->report_id)->first();
    expect($record)->not->toBeNull()
        ->and($record->incident_type)->toBe('residential_fire')
        ->and($record->alarm_level->value)->toBe('1st_alarm')
        ->and($record->establishment_name)->toBe('Dela Cruz Residence')
        ->and((float) $record->estimated_damage)->toBe(25000.00);

    $dispatch = IncidentDispatch::with(['trucks', 'crew'])
        ->where('report_id', $report->report_id)
        ->first();
    expect($dispatch)->not->toBeNull()
        ->and($dispatch->trucks->pluck('truck_name')->all())->toBe(['Fire Truck 1', 'Fire Truck 2'])
        ->and($dispatch->crew->pluck('user_id')->all())->toBe([$this->staff->getKey()]);

    $evidence = ReportEvidence::where('report_id', $report->report_id)->get();
    expect($evidence)->toHaveCount(2);
    foreach ($evidence as $ev) {
        @unlink(storage_path('app/public/'.$ev->image_path));
    }

    $history = ReportStatusHistory::where('report_id', $report->report_id)->get();
    expect($history)->toHaveCount(1)
        ->and($history->first()->status)->toBe(CommunityReport::STATUS_RESOLVED)
        ->and($history->first()->changed_by)->toBe($this->admin->getKey())
        ->and($history->first()->notes)->toContain('manually added as resolved by Senior Fire Officer 4 Felonia Amor Barcelon');

    // Must not trigger pending or duplicate notifications on manual resolved creation
    expect(Notification::count())->toBe(0);
});

test('non-admin bfp personnel cannot create a manual incident report', function () {
    $this->actingAs($this->staff)
        ->post(route('incidents.storeManual'), [
            'reporter_name' => 'Juan Dela Cruz',
            'contact_number' => '09171234567',
            'incident_date' => '2026-11-10',
            'description' => 'Test',
            'latitude' => 14.0392,
            'longitude' => 120.6389,
            'trucks' => ['Fire Truck 1'],
            'personnel_ids' => [$this->staff->getKey()],
        ])
        ->assertForbidden();
});

test('manual incident report requires at least one fire truck and one dispatched personnel', function () {
    $this->actingAs($this->admin)
        ->post(route('incidents.storeManual'), [
            'reporter_name' => 'Juan Dela Cruz',
            'contact_number' => '09171234567',
            'incident_date' => '2026-11-10',
            'description' => 'Test fire',
            'latitude' => 14.0392,
            'longitude' => 120.6389,
            'trucks' => [],
            'personnel_ids' => [],
        ])
        ->assertSessionHasErrors(['trucks', 'personnel_ids']);
});

test('staff can upload additional evidence photos later on a resolved incident', function () {
    $report = CommunityReport::create([
        'user_id' => $this->admin->getKey(),
        'reporter_name' => 'Manual Caller',
        'contact_number' => '09171234567',
        'description' => 'Resolved fire without initial photos',
        'latitude' => 14.0392,
        'longitude' => 120.6389,
        'barangay_id' => $this->barangay->barangay_id,
        'status' => CommunityReport::STATUS_RESOLVED,
    ]);

    $photo = UploadedFile::fake()->image('followup.jpg');

    $this->actingAs($this->staff)
        ->post(route('incidents.storeEvidence', $report), [
            'photos' => [$photo],
        ])
        ->assertRedirect();

    $evidence = ReportEvidence::where('report_id', $report->report_id)->get();
    expect($evidence)->toHaveCount(1)
        ->and($evidence->first()->uploaded_by)->toBe($this->staff->getKey());

    @unlink(storage_path('app/public/'.$evidence->first()->image_path));
});

test('staff can delete an evidence photo from a resolved incident', function () {
    $report = CommunityReport::create([
        'user_id' => $this->admin->getKey(),
        'reporter_name' => 'Manual Caller',
        'contact_number' => '09171234567',
        'description' => 'Resolved fire with photo to delete',
        'latitude' => 14.0392,
        'longitude' => 120.6389,
        'barangay_id' => $this->barangay->barangay_id,
        'status' => CommunityReport::STATUS_RESOLVED,
    ]);

    $photo = UploadedFile::fake()->image('to-delete.jpg');

    $this->actingAs($this->staff)
        ->post(route('incidents.storeEvidence', $report), [
            'photos' => [$photo],
        ])
        ->assertRedirect();

    $evidence = ReportEvidence::where('report_id', $report->report_id)->firstOrFail();
    $filePath = storage_path('app/public/'.$evidence->image_path);
    expect(file_exists($filePath))->toBeTrue();

    $this->actingAs($this->staff)
        ->delete(route('incidents.destroyEvidence', [$report, $evidence]))
        ->assertRedirect();

    expect(ReportEvidence::find($evidence->evidence_id))->toBeNull()
        ->and(file_exists($filePath))->toBeFalse();
});
