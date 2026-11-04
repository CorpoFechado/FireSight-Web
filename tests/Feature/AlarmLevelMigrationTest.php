<?php

use App\Enums\AlarmLevel;
use App\Models\AforReport;
use App\Models\CommunityReport;
use App\Models\IncidentRecord;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('alarm level enum has all canonical levels in escalation order with official colors', function () {
    $expected = [
        'verification' => ['Verification', '#A9B8D0', '#1F2937'],
        '1st_alarm' => ['1st Alarm', '#4FA3E0', '#FFFFFF'],
        '2nd_alarm' => ['2nd Alarm', '#4CAF50', '#FFFFFF'],
        '3rd_alarm' => ['3rd Alarm', '#F2C94C', '#1F2937'],
        '4th_alarm' => ['4th Alarm', '#F2811D', '#FFFFFF'],
        '5th_alarm' => ['5th Alarm', '#E02424', '#FFFFFF'],
        'task_force_alpha' => ['Task Force Alpha', '#6C6FD9', '#FFFFFF'],
        'task_force_bravo' => ['Task Force Bravo', '#2F5FB3', '#FFFFFF'],
        'task_force_charlie' => ['Task Force Charlie', '#B0182E', '#FFFFFF'],
        'task_force_delta' => ['Task Force Delta', '#8C6D2C', '#FFFFFF'],
        'task_force_echo' => ['Task Force Echo', '#8C6D2C', '#FFFFFF'],
        'task_force_hotel' => ['Task Force Hotel', '#8C6D2C', '#FFFFFF'],
        'task_force_india' => ['Task Force India', '#8C6D2C', '#FFFFFF'],
        'general_alarm' => ['General Alarm', '#1E2A5E', '#FFFFFF'],
    ];

    expect(AlarmLevel::values())->toBe(array_keys($expected));

    foreach ($expected as $value => [$label, $color, $textColor]) {
        $case = AlarmLevel::from($value);
        expect($case->label())->toBe($label)
            ->and($case->color())->toBe($color)
            ->and($case->textColor())->toBe($textColor);
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
        ->and($criticals)->not->toContain('verification')
        ->and($criticals)->not->toContain('2nd_alarm')
        ->and($criticals)->not->toContain('3rd_alarm')
        ->and($criticals)->not->toContain('4th_alarm');
});

test('verification enum migration rolls both alarm level columns backward safely', function () {
    $user = User::factory()->create();
    $report = CommunityReport::create([
        'user_id' => $user->getKey(),
        'reporter_name' => 'Verification Migration Test',
        'contact_number' => '09123456789',
        'description' => 'Verification-level incident',
        'latitude' => 13.95,
        'longitude' => 120.65,
        'status' => CommunityReport::STATUS_RESOLVED,
    ]);

    $record = IncidentRecord::create([
        'report_id' => $report->report_id,
        'incident_datetime' => now(),
        'incident_type' => 'residential_fire',
        'alarm_level' => AlarmLevel::Verification,
    ]);

    $aforReport = AforReport::create([
        'incident_id' => $record->incident_id,
        'alarm_level' => AlarmLevel::Verification,
    ]);

    $migration = require database_path('migrations/2026_11_04_092454_add_verification_to_alarm_level_enums.php');
    $migration->down();

    expect(DB::table('incident_record')->where('incident_id', $record->incident_id)->value('alarm_level'))
        ->toBe(AlarmLevel::FirstAlarm->value)
        ->and(DB::table('afor_report')->where('afor_id', $aforReport->afor_id)->value('alarm_level'))
        ->toBe(AlarmLevel::FirstAlarm->value);

    $migration->up();

    DB::table('incident_record')->where('incident_id', $record->incident_id)->update([
        'alarm_level' => AlarmLevel::Verification->value,
    ]);
    DB::table('afor_report')->where('afor_id', $aforReport->afor_id)->update([
        'alarm_level' => AlarmLevel::Verification->value,
    ]);

    expect(DB::table('incident_record')->where('incident_id', $record->incident_id)->value('alarm_level'))
        ->toBe(AlarmLevel::Verification->value)
        ->and(DB::table('afor_report')->where('afor_id', $aforReport->afor_id)->value('alarm_level'))
        ->toBe(AlarmLevel::Verification->value);
});

test('incident record and afor report cast alarm_level to AlarmLevel enum', function () {
    $user = User::factory()->create();
    $report = CommunityReport::create([
        'user_id' => $user->getKey(),
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
        'user_id' => $user->getKey(),
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
        'user_id' => $user->getKey(),
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
        'user_id' => $user->getKey(),
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
        'user_id' => $user->getKey(),
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

    // Restore the current schema after exercising the historical migration.
    $migration->up();
    $verificationMigration = require database_path('migrations/2026_11_04_092454_add_verification_to_alarm_level_enums.php');
    $verificationMigration->up();
});
