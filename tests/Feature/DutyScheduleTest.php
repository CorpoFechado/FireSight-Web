<?php

use App\Enums\DutyGroup;
use App\Models\BfpPersonnelDetails;
use App\Models\DutyOverride;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'duty.rotation.anchor_week_start' => '2026-10-12',
        'duty.rotation.anchor_group' => 'A',
        'duty.rotation.changeover_time' => '00:00',
    ]);
});

test('bfp admin sees roster buckets by effective group and activeGroup', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $personnelA = User::factory()->bfpPersonnel()->create(['last_name' => 'Alvarez']);
    BfpPersonnelDetails::create([
        'user_id' => $personnelA->getKey(),
        'rank' => 'Senior Fire Officer 4',
        'station_assigned' => 'BFP Lian Fire Station',
        'employee_number' => 'EMP-010',
        'duty_group' => DutyGroup::A,
    ]);

    $personnelB = User::factory()->bfpPersonnel()->create(['last_name' => 'Bernardo']);
    BfpPersonnelDetails::create([
        'user_id' => $personnelB->getKey(),
        'rank' => 'Fire Officer 1',
        'station_assigned' => 'BFP Lian Fire Station',
        'employee_number' => 'EMP-011',
        'duty_group' => DutyGroup::B,
    ]);

    $response = $this->get(route('dutySchedule', ['date' => '2026-10-12']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('duty-schedule/index')
        ->has('week', fn (Assert $week) => $week
            ->where('start', '2026-10-12')
            ->where('end', '2026-10-18')
            ->where('activeGroup', 'A')
            ->etc()
        )
        ->has('roster.A', 1)
        ->has('roster.B', 1)
        ->has('roster.regular', 0)
        ->has('roster.detached', 0)
        ->has('roster.unassigned', 0)
        ->where('roster.A.0.user_id', $personnelA->getKey())
        ->where('roster.A.0.is_on_duty', true)
        ->where('roster.B.0.user_id', $personnelB->getKey())
        ->where('roster.B.0.is_on_duty', false)
    );
});

test('non-admin gets 403 on every duty route', function () {
    $personnel = User::factory()->bfpPersonnel()->create();
    $this->actingAs($personnel);

    $target = User::factory()->bfpPersonnel()->create();
    $override = DutyOverride::factory()->create(['user_id' => $target->getKey()]);

    $this->get(route('dutySchedule'))->assertForbidden();
    $this->patch(route('dutySchedule.updateGroup', $target), ['duty_group' => 'A'])->assertForbidden();
    $this->post(route('dutySchedule.overrides.store'), [
        'user_id' => $target->getKey(),
        'duty_group' => 'A',
        'starts_on' => '2026-10-12',
        'ends_on' => '2026-10-18',
    ])->assertForbidden();
    $this->post(route('dutySchedule.swap'), [
        'user_id' => $target->getKey(),
        'swap_with_id' => $personnel->getKey(),
        'starts_on' => '2026-10-12',
        'ends_on' => '2026-10-18',
    ])->assertForbidden();
    $this->delete(route('dutySchedule.overrides.destroy', $override))->assertForbidden();
});

test('updateGroup saves base duty_group; rejects admin users and invalid groups', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $personnel = User::factory()->bfpPersonnel()->create();
    BfpPersonnelDetails::create([
        'user_id' => $personnel->getKey(),
        'rank' => 'Fire Officer 1',
        'station_assigned' => 'BFP Lian Fire Station',
        'employee_number' => 'EMP-020',
        'duty_group' => null,
    ]);

    // Valid update
    $response = $this->patch(route('dutySchedule.updateGroup', $personnel), [
        'duty_group' => DutyGroup::A->value,
    ]);
    $response->assertRedirect();
    $response->assertSessionHas('toast.message', "{$personnel->name} moved to Duty A.");

    $this->assertDatabaseHas('bfp_personnel_details', [
        'user_id' => $personnel->getKey(),
        'duty_group' => 'A',
    ]);

    // Rejects invalid group enum value
    $this->patch(route('dutySchedule.updateGroup', $personnel), [
        'duty_group' => 'invalid_group',
    ])->assertSessionHasErrors('duty_group');

    // Rejects targeting admin users with 404
    $otherAdmin = User::factory()->bfpAdmin()->create();
    $this->patch(route('dutySchedule.updateGroup', $otherAdmin), [
        'duty_group' => 'A',
    ])->assertNotFound();
});

test('storeOverride: rejects non-Monday start / non-Sunday end / overlap / same-as-base group; creates on valid', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $personnel = User::factory()->bfpPersonnel()->create();
    BfpPersonnelDetails::create([
        'user_id' => $personnel->getKey(),
        'rank' => 'Fire Officer 2',
        'station_assigned' => 'BFP Lian Fire Station',
        'employee_number' => 'EMP-030',
        'duty_group' => DutyGroup::A,
    ]);

    // Non-Monday start (Tuesday)
    $this->post(route('dutySchedule.overrides.store'), [
        'user_id' => $personnel->getKey(),
        'duty_group' => DutyGroup::B->value,
        'starts_on' => '2026-10-13',
        'ends_on' => '2026-10-18',
    ])->assertSessionHasErrors('starts_on');

    // Non-Sunday end (Saturday)
    $this->post(route('dutySchedule.overrides.store'), [
        'user_id' => $personnel->getKey(),
        'duty_group' => DutyGroup::B->value,
        'starts_on' => '2026-10-12',
        'ends_on' => '2026-10-17',
    ])->assertSessionHasErrors('ends_on');

    // Same-as-base group (DutyGroup A)
    $this->post(route('dutySchedule.overrides.store'), [
        'user_id' => $personnel->getKey(),
        'duty_group' => DutyGroup::A->value,
        'starts_on' => '2026-10-12',
        'ends_on' => '2026-10-18',
    ])->assertSessionHasErrors('duty_group');

    // Valid create
    $response = $this->post(route('dutySchedule.overrides.store'), [
        'user_id' => $personnel->getKey(),
        'duty_group' => DutyGroup::B->value,
        'starts_on' => '2026-10-12',
        'ends_on' => '2026-10-18',
        'reason' => 'Covering shift',
    ]);
    $response->assertRedirect();
    $response->assertSessionHas('toast.message', "Override for {$personnel->name} was added.");

    $this->assertDatabaseHas('duty_overrides', [
        'user_id' => $personnel->getKey(),
        'duty_group' => 'B',
        'starts_on' => '2026-10-12',
        'ends_on' => '2026-10-18',
        'created_by' => $admin->getKey(),
    ]);

    // Overlapping override rejected
    $this->post(route('dutySchedule.overrides.store'), [
        'user_id' => $personnel->getKey(),
        'duty_group' => DutyGroup::Regular->value,
        'starts_on' => '2026-10-12',
        'ends_on' => '2026-10-18',
    ])->assertSessionHasErrors('starts_on');
});

test('swap creates two overrides and moves both people in the roster; rejects same-group swap', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $userA = User::factory()->bfpPersonnel()->create(['first_name' => 'Alice', 'last_name' => 'Alpha']);
    BfpPersonnelDetails::create([
        'user_id' => $userA->getKey(),
        'rank' => 'Fire Officer 1',
        'station_assigned' => 'BFP Lian Fire Station',
        'employee_number' => 'EMP-041',
        'duty_group' => DutyGroup::A,
    ]);

    $userB = User::factory()->bfpPersonnel()->create(['first_name' => 'Bob', 'last_name' => 'Bravo']);
    BfpPersonnelDetails::create([
        'user_id' => $userB->getKey(),
        'rank' => 'Fire Officer 2',
        'station_assigned' => 'BFP Lian Fire Station',
        'employee_number' => 'EMP-042',
        'duty_group' => DutyGroup::B,
    ]);

    $userA2 = User::factory()->bfpPersonnel()->create(['first_name' => 'Alex', 'last_name' => 'Alpha2']);
    BfpPersonnelDetails::create([
        'user_id' => $userA2->getKey(),
        'rank' => 'Fire Officer 3',
        'station_assigned' => 'BFP Lian Fire Station',
        'employee_number' => 'EMP-043',
        'duty_group' => DutyGroup::A,
    ]);

    // Reject same-group swap (A with A)
    $this->post(route('dutySchedule.swap'), [
        'user_id' => $userA->getKey(),
        'swap_with_id' => $userA2->getKey(),
        'starts_on' => '2026-10-12',
        'ends_on' => '2026-10-18',
    ])->assertSessionHasErrors('swap_with_id');

    // Valid swap between A and B
    $response = $this->post(route('dutySchedule.swap'), [
        'user_id' => $userA->getKey(),
        'swap_with_id' => $userB->getKey(),
        'starts_on' => '2026-10-12',
        'ends_on' => '2026-10-18',
        'reason' => 'Personal request',
    ]);
    $response->assertRedirect();
    $response->assertSessionHas('toast.message', "{$userA->name} and {$userB->name} swapped for Oct 12 – Oct 18.");

    // User A takes B
    $this->assertDatabaseHas('duty_overrides', [
        'user_id' => $userA->getKey(),
        'duty_group' => 'B',
        'starts_on' => '2026-10-12',
        'ends_on' => '2026-10-18',
    ]);

    // User B takes A
    $this->assertDatabaseHas('duty_overrides', [
        'user_id' => $userB->getKey(),
        'duty_group' => 'A',
        'starts_on' => '2026-10-12',
        'ends_on' => '2026-10-18',
    ]);
});

test('destroyOverride removes it', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $personnel = User::factory()->bfpPersonnel()->create();
    $override = DutyOverride::factory()->create([
        'user_id' => $personnel->getKey(),
        'created_by' => $admin->getKey(),
    ]);

    $response = $this->delete(route('dutySchedule.overrides.destroy', $override));
    $response->assertRedirect();
    $response->assertSessionHas('toast.message', "Override for {$personnel->name} was removed.");

    $this->assertDatabaseMissing('duty_overrides', [
        'override_id' => $override->override_id,
    ]);
});
