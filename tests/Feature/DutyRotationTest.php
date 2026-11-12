<?php

use App\Enums\DutyGroup;
use App\Models\BfpPersonnelDetails;
use App\Models\DutyOverride;
use App\Models\User;
use App\Support\DutyRotation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'duty.rotation.anchor_week_start' => '2026-10-12',
        'duty.rotation.anchor_group' => 'B',
        'duty.rotation.changeover_time' => '00:00',
    ]);
});

test('rotation active group: anchor week is B, next week is A, week after is B', function () {
    // Anchor week (Mon 2026-10-12)
    $anchorMoment = Carbon::parse('2026-10-12 10:00:00');
    expect(DutyRotation::activeGroupFor($anchorMoment))->toBe(DutyGroup::B);

    // Next week (Mon 2026-10-19)
    $nextWeek = Carbon::parse('2026-10-19 14:00:00');
    expect(DutyRotation::activeGroupFor($nextWeek))->toBe(DutyGroup::A);

    // Week after (Mon 2026-10-26)
    $twoWeeksAfter = Carbon::parse('2026-10-26 08:30:00');
    expect(DutyRotation::activeGroupFor($twoWeeksAfter))->toBe(DutyGroup::B);
    expect(DutyRotation::activeGroupFor(Carbon::parse('2026-11-09')))->toBe(DutyGroup::B);
    expect(DutyRotation::activeGroupFor(Carbon::parse('2026-11-16')))->toBe(DutyGroup::A);
    expect(array_column(DutyRotation::upcomingWeeks(Carbon::parse('2026-11-09'), 4), 'group'))->toBe(['B', 'A', 'B', 'A']);
});

test('rotation active group: a week before the anchor is A', function () {
    // 1 week before anchor (Mon 2026-10-05)
    $weekBefore = Carbon::parse('2026-10-05 12:00:00');
    expect(DutyRotation::activeGroupFor($weekBefore))->toBe(DutyGroup::A);

    // 2 weeks before anchor (Mon 2026-09-28)
    $twoWeeksBefore = Carbon::parse('2026-09-28 12:00:00');
    expect(DutyRotation::activeGroupFor($twoWeeksBefore))->toBe(DutyGroup::B);
});

test('rotation active group: Sunday 23:59 vs Monday 00:00 boundary flips the group', function () {
    // Sunday 23:59:59 of anchor week remains Group B.
    $sundayNight = Carbon::parse('2026-10-18 23:59:59');
    expect(DutyRotation::activeGroupFor($sundayNight))->toBe(DutyGroup::B);

    // Monday 00:00:00 next week flips to Group A.
    $mondayMidnight = Carbon::parse('2026-10-19 00:00:00');
    expect(DutyRotation::activeGroupFor($mondayMidnight))->toBe(DutyGroup::A);
});

test('changing changeover_time to 08:00 makes Monday 07:59 still the previous group', function () {
    config(['duty.rotation.changeover_time' => '08:00']);

    // Monday 07:59:59 on the rollover day is still the previous week's group (Group B).
    $beforeChangeover = Carbon::parse('2026-10-19 07:59:59');
    expect(DutyRotation::activeGroupFor($beforeChangeover))->toBe(DutyGroup::B);

    // Monday 08:00:00 flips to Group A.
    $afterChangeover = Carbon::parse('2026-10-19 08:00:00');
    expect(DutyRotation::activeGroupFor($afterChangeover))->toBe(DutyGroup::A);
});

test('effective group and isOnDuty: override wins over base group, Office Hours follows its schedule, Detached never', function () {
    config(['duty.rotation.anchor_group' => 'A']);
    $anchorMoment = Carbon::parse('2026-10-12 12:00:00'); // Group A active
    $weekStart = DutyRotation::weekStartFor($anchorMoment);

    // Regular personnel
    $regularUser = User::factory()->bfpPersonnel()->create();
    BfpPersonnelDetails::create([
        'user_id' => $regularUser->getKey(),
        'rank' => 'Senior Fire Officer 3',
        'station_assigned' => 'BFP Lian Fire Station',
        'employee_number' => 'EMP-001',
        'duty_group' => DutyGroup::Regular,
    ]);

    // Group B personnel (normally off-duty during Group A week)
    $bUser = User::factory()->bfpPersonnel()->create();
    BfpPersonnelDetails::create([
        'user_id' => $bUser->getKey(),
        'rank' => 'Fire Officer 1',
        'station_assigned' => 'BFP Lian Fire Station',
        'employee_number' => 'EMP-002',
        'duty_group' => DutyGroup::B,
    ]);

    // Detached personnel
    $detachedUser = User::factory()->bfpPersonnel()->create();
    BfpPersonnelDetails::create([
        'user_id' => $detachedUser->getKey(),
        'rank' => 'Fire Officer 2',
        'station_assigned' => 'BFP Lian Fire Station',
        'employee_number' => 'EMP-003',
        'duty_group' => DutyGroup::Detached,
    ]);

    // Office Hours is on duty at noon on Monday.
    expect(DutyRotation::effectiveGroupFor($regularUser, $weekStart))->toBe(DutyGroup::Regular);
    expect(DutyRotation::isOnDuty($regularUser, $anchorMoment))->toBeTrue();

    // Detached is never on duty
    expect(DutyRotation::effectiveGroupFor($detachedUser, $weekStart))->toBe(DutyGroup::Detached);
    expect(DutyRotation::isOnDuty($detachedUser, $anchorMoment))->toBeFalse();

    // Group B user is off duty during Group A week
    expect(DutyRotation::effectiveGroupFor($bUser, $weekStart))->toBe(DutyGroup::B);
    expect(DutyRotation::isOnDuty($bUser, $anchorMoment))->toBeFalse();

    // Add override for Group B user moving them to Group A for the anchor week
    DutyOverride::factory()->create([
        'user_id' => $bUser->getKey(),
        'duty_group' => DutyGroup::A,
        'starts_on' => '2026-10-12',
        'ends_on' => '2026-10-18',
    ]);

    // Override wins over base group, making them now on duty
    $bUser->refresh();
    expect(DutyRotation::effectiveGroupFor($bUser, $weekStart))->toBe(DutyGroup::A);
    expect(DutyRotation::isOnDuty($bUser, $anchorMoment))->toBeTrue();
});

test('office hours follow local weekdays and boundaries', function (string $moment, bool $expected) {
    $user = User::factory()->bfpPersonnel()->create();
    BfpPersonnelDetails::create([
        'user_id' => $user->getKey(),
        'rank' => 'Fire Officer 1',
        'station_assigned' => 'BFP Lian Fire Station',
        'employee_number' => 'EMP-OFFICE',
        'duty_group' => DutyGroup::Regular,
    ]);

    expect(DutyRotation::isOnDuty($user, Carbon::parse($moment, 'Asia/Manila')))->toBe($expected);
})->with([
    ['2026-11-09 08:00', true],
    ['2026-11-11 12:00', true],
    ['2026-11-13 16:59', true],
    ['2026-11-09 07:59', false],
    ['2026-11-09 17:00', false],
    ['2026-11-09 20:00', false],
    ['2026-11-14 10:00', false],
    ['2026-11-15 10:00', false],
]);

test('office hours convert timezones and honor configured hours', function () {
    config(['duty.office_hours.start' => '09:00', 'duty.office_hours.end' => '16:00']);

    expect(DutyRotation::isWithinOfficeHours(Carbon::parse('2026-11-09 00:59', 'UTC')))->toBeFalse()
        ->and(DutyRotation::isWithinOfficeHours(Carbon::parse('2026-11-09 01:00', 'UTC')))->toBeTrue()
        ->and(DutyRotation::isWithinOfficeHours(Carbon::parse('2026-11-09 08:00', 'UTC')))->toBeFalse();
});
