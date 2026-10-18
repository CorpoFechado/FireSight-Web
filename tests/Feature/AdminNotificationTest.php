<?php

use App\Models\Barangay;
use App\Models\BfpPersonnelDetails;
use App\Models\CommunityReport;
use App\Models\IncidentRecord;
use App\Models\Notification;
use App\Models\User;

/**
 * @param  array<string, mixed>  $overrides
 */
function makeCommunityReport(array $overrides = []): CommunityReport
{
    $reporter = User::factory()->create();

    return CommunityReport::create([
        'user_id' => $reporter->getKey(),
        'reporter_name' => 'Juan Dela Cruz',
        'contact_number' => '09171234567',
        'description' => 'Smoke near the market.',
        'latitude' => 14.0353,
        'longitude' => 120.6511,
        'status' => CommunityReport::STATUS_PENDING,
        ...$overrides,
    ]);
}

test('a new report notifies active admins only', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $inactiveAdmin = User::factory()->bfpAdmin()->inactive()->create();
    $personnel = User::factory()->bfpPersonnel()->create();
    $barangay = Barangay::create(['barangay_name' => 'Poblacion 1']);

    $report = makeCommunityReport(['barangay_id' => $barangay->barangay_id]);

    $this->assertDatabaseHas('notification', [
        'user_id' => $admin->getKey(),
        'title' => 'New Community Report',
        'message' => "Report #{$report->report_id} from Juan Dela Cruz in Poblacion 1 is awaiting verification.",
        'notification_type' => 'incident_alert',
    ]);
    $this->assertDatabaseMissing('notification', ['user_id' => $inactiveAdmin->getKey()]);
    $this->assertDatabaseMissing('notification', ['user_id' => $personnel->getKey()]);
});

test('a high-confidence AI fire detection is sent as a single critical alert', function () {
    $admin = User::factory()->bfpAdmin()->create();

    $report = makeCommunityReport([
        'ai_fire_label' => 'fire',
        'ai_fire_confidence' => 0.94,
    ]);

    $notifications = Notification::where('user_id', $admin->getKey())->get();

    expect($notifications)->toHaveCount(1)
        ->and($notifications->first()->title)->toBe('Critical: AI-Detected Fire')
        ->and($notifications->first()->message)->toContain("94% confidence on Report #{$report->report_id}");
});

test('a low-confidence AI detection sends a regular new report notification', function () {
    $admin = User::factory()->bfpAdmin()->create();

    makeCommunityReport([
        'ai_fire_label' => 'fire',
        'ai_fire_confidence' => 0.6,
    ]);

    expect(Notification::where('user_id', $admin->getKey())->pluck('title')->all())
        ->toBe(['New Community Report']);
});

test('AI verification completing after submission triggers a critical alert', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $report = makeCommunityReport();

    $report->update([
        'ai_fire_label' => 'smoke',
        'ai_fire_confidence' => 0.97,
        'ai_verified_at' => now(),
    ]);

    $this->assertDatabaseHas('notification', [
        'user_id' => $admin->getKey(),
        'title' => 'Critical: AI-Detected Fire',
    ]);
});

test('a report submitted near an active report is flagged as a possible duplicate', function () {
    $admin = User::factory()->bfpAdmin()->create();

    $original = makeCommunityReport();
    $nearby = makeCommunityReport(['latitude' => 14.0363, 'longitude' => 120.6511]);

    $this->assertDatabaseHas('notification', [
        'user_id' => $admin->getKey(),
        'title' => 'Possible Duplicate Report',
        'message' => "Report #{$nearby->report_id} was submitted near active Report #{$original->report_id}. Review and link them if they describe the same incident.",
    ]);
});

test('far away or closed reports are not flagged as duplicates', function () {
    $admin = User::factory()->bfpAdmin()->create();

    makeCommunityReport(['latitude' => 13.9465, 'longitude' => 120.6252]);
    makeCommunityReport(['status' => CommunityReport::STATUS_RESOLVED]);
    makeCommunityReport();

    $this->assertDatabaseMissing('notification', [
        'user_id' => $admin->getKey(),
        'title' => 'Possible Duplicate Report',
    ]);
});

test('a status change by personnel notifies admins with the personnel name', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $personnel = User::factory()->bfpPersonnel()->create(['first_name' => 'Alejandro', 'last_name' => 'Vergara']);
    BfpPersonnelDetails::create([
        'user_id' => $personnel->getKey(),
        'rank' => 'SFO1',
        'employee_number' => 'EMP-TEST-001',
    ]);
    $report = makeCommunityReport(['status' => CommunityReport::STATUS_ACCEPTED]);

    $this->actingAs($personnel)
        ->patch(route('incidents.updateStatus', $report), ['status' => CommunityReport::STATUS_DISPATCHED])
        ->assertRedirect();

    $this->assertDatabaseHas('notification', [
        'user_id' => $admin->getKey(),
        'title' => 'Report Dispatched',
        'message' => "SFO1 Alejandro Vergara marked Report #{$report->report_id} as Dispatched.",
        'notification_type' => 'status_update',
    ]);
    $this->assertDatabaseMissing('notification', ['user_id' => $personnel->getKey()]);
});

test('the admin who changed a status is not notified about their own action', function () {
    $actingAdmin = User::factory()->bfpAdmin()->create();
    $otherAdmin = User::factory()->bfpAdmin()->create();
    $barangay = Barangay::create(['barangay_name' => 'Bungahan']);
    $report = makeCommunityReport();

    $this->actingAs($actingAdmin)
        ->post(route('incidents.accept', $report), ['barangay_id' => $barangay->barangay_id])
        ->assertRedirect();

    $this->assertDatabaseHas('notification', [
        'user_id' => $otherAdmin->getKey(),
        'title' => 'Report Accepted',
        'message' => "{$actingAdmin->name} marked Report #{$report->report_id} in Bungahan as Accepted.",
    ]);
    $this->assertDatabaseMissing('notification', [
        'user_id' => $actingAdmin->getKey(),
        'title' => 'Report Accepted',
    ]);
});

test('editing incident details notifies admins with who edited them', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $personnel = User::factory()->bfpPersonnel()->create();
    $report = makeCommunityReport(['status' => CommunityReport::STATUS_RESOLVED]);
    IncidentRecord::create([
        'report_id' => $report->report_id,
        'incident_datetime' => now(),
        'incident_type' => 'residential_fire',
        'alarm_level' => '1st_alarm',
    ]);

    $this->actingAs($personnel)
        ->patch(route('incidents.updateDetails', $report), [
            'incident_type' => 'commercial_fire',
            'alarm_level' => '2nd_alarm',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('notification', [
        'user_id' => $admin->getKey(),
        'title' => 'Incident Details Updated',
        'message' => "{$personnel->name} updated the incident assessment for Report #{$report->report_id}.",
        'notification_type' => 'system',
    ]);
});
