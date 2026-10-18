<?php

use App\Enums\DutyGroup;
use App\Models\BfpPersonnelDetails;
use App\Models\User;
use Database\Seeders\BfpAccountSeeder;
use Database\Seeders\BfpPersonnelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('running BfpAccountSeeder and BfpPersonnelSeeder creates 18 accounts with correct passwords and groups', function () {
    $this->seed(BfpAccountSeeder::class);
    $this->seed(BfpPersonnelSeeder::class);

    // Creates exactly 18 accounts (1 admin + 17 personnel)
    $allUsers = User::all();
    expect($allUsers)->toHaveCount(18);

    // All passwords verify against 'password'
    foreach ($allUsers as $user) {
        expect(Hash::check('password', $user->password))->toBeTrue();
    }

    // Personnel groups: 4 regular, 7 A, 6 B
    $regularCount = BfpPersonnelDetails::where('duty_group', DutyGroup::Regular->value)->count();
    $groupACount = BfpPersonnelDetails::where('duty_group', DutyGroup::A->value)->count();
    $groupBCount = BfpPersonnelDetails::where('duty_group', DutyGroup::B->value)->count();

    expect($regularCount)->toBe(4);
    expect($groupACount)->toBe(7);
    expect($groupBCount)->toBe(6);

    // personnel@firesight.bfp.lian is Dexter Pantas (ID 8)
    $testLoginUser = User::where('email', 'personnel@firesight.bfp.lian')->first();
    expect($testLoginUser)->not->toBeNull();
    expect($testLoginUser->user_id)->toBe(8);
    expect($testLoginUser->first_name)->toBe('Dexter');
    expect($testLoginUser->last_name)->toBe('Pantas');
    expect($testLoginUser->personnelDetails?->duty_group)->toBe(DutyGroup::B);
});
