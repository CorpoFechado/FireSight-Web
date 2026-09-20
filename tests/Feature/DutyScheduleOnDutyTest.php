<?php

use App\Models\DutySchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('onDutyAt scope returns only the shift covering the given moment', function () {
    $moment = Carbon::parse('2026-09-20 10:00:00');

    // Personnel who IS on duty at 10:00 (08:00–16:00)
    $onDutyUser = User::factory()->bfpPersonnel()->create();
    DutySchedule::factory()->create([
        'user_id' => $onDutyUser->id,
        'duty_date' => '2026-09-20',
        'time_start' => '08:00:00',
        'time_end' => '16:00:00',
    ]);

    // Personnel whose shift starts after the moment (12:00–20:00)
    $laterUser = User::factory()->bfpPersonnel()->create();
    DutySchedule::factory()->create([
        'user_id' => $laterUser->id,
        'duty_date' => '2026-09-20',
        'time_start' => '12:00:00',
        'time_end' => '20:00:00',
    ]);

    // Personnel whose shift ended before the moment (00:00–08:00)
    $earlierUser = User::factory()->bfpPersonnel()->create();
    DutySchedule::factory()->create([
        'user_id' => $earlierUser->id,
        'duty_date' => '2026-09-20',
        'time_start' => '00:00:00',
        'time_end' => '08:00:00',
    ]);

    // Personnel scheduled on a different date entirely
    $wrongDateUser = User::factory()->bfpPersonnel()->create();
    DutySchedule::factory()->create([
        'user_id' => $wrongDateUser->id,
        'duty_date' => '2026-09-21',
        'time_start' => '08:00:00',
        'time_end' => '16:00:00',
    ]);

    $results = DutySchedule::onDutyAt($moment)->get();

    expect($results)->toHaveCount(1);
    expect($results->first()->user_id)->toBe($onDutyUser->id);
});

test('onDutyAt scope handles an exact shift boundary — start is inclusive, end is exclusive', function () {
    $admin = User::factory()->bfpAdmin()->create();

    // Shift: 08:00–16:00
    $shift = DutySchedule::factory()->create([
        'duty_date' => '2026-09-20',
        'time_start' => '08:00:00',
        'time_end' => '16:00:00',
        'created_by' => $admin->id,
    ]);

    // At exactly 08:00:00 — should be included (time_start <=)
    expect(DutySchedule::onDutyAt(Carbon::parse('2026-09-20 08:00:00'))->count())->toBe(1);

    // At exactly 16:00:00 — should be excluded (time_end >)
    expect(DutySchedule::onDutyAt(Carbon::parse('2026-09-20 16:00:00'))->count())->toBe(0);
});

test('onDutyAt scope returns multiple personnel on duty simultaneously', function () {
    $moment = Carbon::parse('2026-09-20 09:00:00');

    DutySchedule::factory()->count(3)->create([
        'duty_date' => '2026-09-20',
        'time_start' => '08:00:00',
        'time_end' => '16:00:00',
    ]);

    // One off-duty shift on the same date
    DutySchedule::factory()->create([
        'duty_date' => '2026-09-20',
        'time_start' => '16:00:00',
        'time_end' => '23:00:00',
    ]);

    expect(DutySchedule::onDutyAt($moment)->count())->toBe(3);
});
