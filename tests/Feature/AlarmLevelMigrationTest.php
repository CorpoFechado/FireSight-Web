<?php

use App\Enums\AlarmLevel;
use App\Models\CommunityReport;
use App\Models\IncidentRecord;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('alarm level enum has all 13 canonical levels in order and correct labels', function () {
    $expected = [
        '1st_alarm' => '1st Alarm',
        '2nd_alarm' => '2nd Alarm',
        '3rd_alarm' => '3rd Alarm',
        '4th_alarm' => '4th Alarm',
        '5th_alarm' => '5th Alarm',
        'task_force_alpha' => 'Task Force Alpha',
        'task_force_bravo' => 'Task Force Bravo',
        'task_force_charlie' => 'Task Force Charlie',
        'task_force_delta' => 'Task Force Delta',
        'task_force_echo' => 'Task Force Echo',
        'task_force_hotel' => 'Task Force Hotel',
        'task_force_india' => 'Task Force India',
        'general_alarm' => 'General Alarm',
    ];

    expect(AlarmLevel::values())->toBe(array_keys($expected));

    foreach ($expected as $val => $label) {
        $case = AlarmLevel::from($val);
        expect($case->label())->toBe($label);
    }
});

test('alarm level critical values helper includes 5th alarm, all task force stages, and general alarm', function () {
    $criticals = AlarmLevel::criticalValues();

    expect($criticals)->toBe([
        '5th_alarm',
        'task_force_alpha',
        'task_force_bravo',
        'task_force_charlie',
        'task_force_delta',
        'task_force_echo',
        'task_force_hotel',
        'task_force_india',
        'general_alarm',
    ]);

    expect($criticals)->not->toContain('1st_alarm')
        ->and($criticals)->not->toContain('2nd_alarm')
        ->and($criticals)->not->toContain('3rd_alarm')
        ->and($criticals)->not->toContain('4th_alarm');
});

test('incident record and afor report cast alarm_level to AlarmLevel enum', function () {
    $user = User::factory()->create();
    $report = CommunityReport::create([
        'user_id' => $user->id,
        'reporter_name' => 'Test Reporter',
        'contact_number' => '09123456789',
        'description' => 'Test fire',
        'latitude' => 13.95,
        'longitude' => 120.65,
        'status' => CommunityReport::STATUS_RESOLVED,
    ]);

    $record = IncidentRecord::create([
        'report_id' => $report->report_id,
        'incident_datetime' => now(),
        'incident_type' => 'residential_fire',
        'alarm_level' => 'task_force_alpha',
    ]);

    expect($record->alarm_level)->toBe(AlarmLevel::TaskForceAlpha)
        ->and($record->alarm_level->label())->toBe('Task Force Alpha');
});

test('migration data mapping up and down logic preserves expected category mappings', function () {
    $migration = require database_path('migrations/2026_10_08_000001_replace_severity_level_with_alarm_level_in_incident_record.php');

    // Run down to revert to severity_level
    $migration->down();

    // Verify column exists and insert old severity values
    $user = User::factory()->create();
    $report = CommunityReport::create([
        'user_id' => $user->id,
        'reporter_name' => 'Migration Test',
        'contact_number' => '09123456789',
        'description' => 'Test',
        'latitude' => 13.95,
        'longitude' => 120.65,
        'status' => CommunityReport::STATUS_RESOLVED,
    ]);

    DB::table('incident_record')->insert([
        ['report_id' => $report->report_id, 'incident_datetime' => '2026-06-01 10:00:00', 'incident_type' => 'residential_fire', 'severity_level' => 'low'],
    ]);

    $rep2 = CommunityReport::create([
        'user_id' => $user->id,
        'reporter_name' => 'Migration Test 2',
        'contact_number' => '09123456789',
        'description' => 'Test',
        'latitude' => 13.95,
        'longitude' => 120.65,
        'status' => CommunityReport::STATUS_RESOLVED,
    ]);
    DB::table('incident_record')->insert([
        ['report_id' => $rep2->report_id, 'incident_datetime' => '2026-06-01 10:00:00', 'incident_type' => 'residential_fire', 'severity_level' => 'moderate'],
    ]);

    $rep3 = CommunityReport::create([
        'user_id' => $user->id,
        'reporter_name' => 'Migration Test 3',
        'contact_number' => '09123456789',
        'description' => 'Test',
        'latitude' => 13.95,
        'longitude' => 120.65,
        'status' => CommunityReport::STATUS_RESOLVED,
    ]);
    DB::table('incident_record')->insert([
        ['report_id' => $rep3->report_id, 'incident_datetime' => '2026-06-01 10:00:00', 'incident_type' => 'residential_fire', 'severity_level' => 'high'],
    ]);

    $rep4 = CommunityReport::create([
        'user_id' => $user->id,
        'reporter_name' => 'Migration Test 4',
        'contact_number' => '09123456789',
        'description' => 'Test',
        'latitude' => 13.95,
        'longitude' => 120.65,
        'status' => CommunityReport::STATUS_RESOLVED,
    ]);
    DB::table('incident_record')->insert([
        ['report_id' => $rep4->report_id, 'incident_datetime' => '2026-06-01 10:00:00', 'incident_type' => 'residential_fire', 'severity_level' => 'critical'],
    ]);

    // Now run migration up()
    $migration->up();

    // Verify mapping:
    // low -> 1st_alarm
    // moderate -> 2nd_alarm
    // high -> 3rd_alarm
    // critical -> 5th_alarm
    $row1 = DB::table('incident_record')->where('report_id', $report->report_id)->first();
    $row2 = DB::table('incident_record')->where('report_id', $rep2->report_id)->first();
    $row3 = DB::table('incident_record')->where('report_id', $rep3->report_id)->first();
    $row4 = DB::table('incident_record')->where('report_id', $rep4->report_id)->first();

    expect($row1->alarm_level)->toBe('1st_alarm')
        ->and($row2->alarm_level)->toBe('2nd_alarm')
        ->and($row3->alarm_level)->toBe('3rd_alarm')
        ->and($row4->alarm_level)->toBe('5th_alarm');

    // Run migration down() to verify reverse mapping
    $migration->down();

    $downRow1 = DB::table('incident_record')->where('report_id', $report->report_id)->first();
    $downRow2 = DB::table('incident_record')->where('report_id', $rep2->report_id)->first();
    $downRow3 = DB::table('incident_record')->where('report_id', $rep3->report_id)->first();
    $downRow4 = DB::table('incident_record')->where('report_id', $rep4->report_id)->first();

    expect($downRow1->severity_level)->toBe('low')
        ->and($downRow2->severity_level)->toBe('moderate')
        ->and($downRow3->severity_level)->toBe('high')
        ->and($downRow4->severity_level)->toBe('critical');

    // Run up() once more so database schema remains migrated
    $migration->up();
});
