<?php

use App\Models\AuthToken;
use App\Models\Barangay;
use App\Models\CommunityReport;
use App\Models\ResidentAddress;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

function createResidentAddress(User $resident, Barangay $barangay, string $street = '123 Rizal Street'): ResidentAddress
{
    return ResidentAddress::create([
        'user_id' => $resident->getKey(),
        'house_no_street' => $street,
        'barangay_id' => $barangay->getKey(),
        'municipality' => 'Lian',
        'province' => 'Batangas',
    ]);
}

function residentRouteRequests(User $target): array
{
    return [
        ['GET', route('residents')],
        ['GET', route('residents.show', $target)],
        ['PATCH', route('residents.update', $target)],
        ['POST', route('residents.resetPassword', $target)],
        ['POST', route('residents.suspend', $target)],
        ['POST', route('residents.reactivate', $target)],
    ];
}

test('resident routes require an authenticated bfp administrator', function () {
    $resident = User::factory()->resident()->create();
    $personnel = User::factory()->bfpPersonnel()->create();

    foreach (residentRouteRequests($resident) as [$method, $url]) {
        $this->call($method, $url)->assertRedirect(route('login'));
    }

    $this->actingAs($personnel);

    foreach (residentRouteRequests($resident) as [$method, $url]) {
        $this->call($method, $url)->assertForbidden();
    }
});

test('resident index lists only residents and supports pagination search barangay and status filters', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $barangayOne = Barangay::create(['barangay_name' => 'Barangay One']);
    $barangayTwo = Barangay::create(['barangay_name' => 'Barangay Two']);

    User::factory()->bfpPersonnel()->create(['email' => 'not-a-resident@example.com']);

    User::factory()->resident()->count(16)->create()->each(
        fn (User $resident) => createResidentAddress($resident, $barangayOne),
    );

    $suspendedResident = User::factory()->resident()->suspended()->create([
        'first_name' => 'Unique',
        'last_name' => 'Resident',
        'email' => 'unique.resident@example.com',
        'username' => 'unique-resident',
    ]);
    createResidentAddress($suspendedResident, $barangayTwo);

    $this->get(route('residents'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('residents/index')
            ->has('residents.data', 15)
            ->where('residents.total', 17)
            ->where('totalCount', 17)
            ->where('residents.data', fn ($rows) => collect($rows)
                ->every(fn (array $row) => $row['email'] !== 'not-a-resident@example.com'))
        );

    $this->get(route('residents', ['barangay' => $barangayTwo->getKey()]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('residents.data', 1)
            ->where('residents.data.0.id', $suspendedResident->getKey())
        );

    $this->get(route('residents', ['search' => 'unique-resident']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('residents.data', 1)
            ->where('residents.data.0.id', $suspendedResident->getKey())
        );

    $this->get(route('residents', ['status' => 'suspended']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('residents.data', 1)
            ->where('residents.data.0.status', 'suspended')
        );
});

test('resident show returns profile and reports without exposing authentication secrets', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $resident = User::factory()->resident()->create();
    $barangay = Barangay::create(['barangay_name' => 'Binubusan']);
    createResidentAddress($resident, $barangay);

    $report = CommunityReport::create([
        'user_id' => $resident->getKey(),
        'reporter_name' => $resident->name,
        'contact_number' => $resident->contact_number,
        'description' => 'Smoke is visible from a nearby home.',
        'latitude' => 13.99,
        'longitude' => 120.65,
        'barangay_id' => $barangay->getKey(),
        'status' => CommunityReport::STATUS_PENDING,
    ]);

    $this->actingAs($admin)
        ->getJson(route('residents.show', $resident))
        ->assertOk()
        ->assertJsonPath('resident.id', $resident->getKey())
        ->assertJsonPath('reports.0.id', $report->getKey())
        ->assertJsonPath('reports.0.barangay', 'Binubusan')
        ->assertJsonMissingPath('resident.password')
        ->assertJsonMissingPath('resident.remember_token')
        ->assertJsonMissingPath('resident.two_factor_secret');
});

test('every resident action rejects bfp account targets', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $personnel = User::factory()->bfpPersonnel()->create();
    $this->actingAs($admin);

    foreach (array_slice(residentRouteRequests($personnel), 1) as [$method, $url]) {
        $this->call($method, $url)->assertNotFound();
    }
});

test('administrator can update resident details without changing role or status', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $resident = User::factory()->resident()->create();
    $oldBarangay = Barangay::create(['barangay_name' => 'Prenza']);
    $newBarangay = Barangay::create(['barangay_name' => 'Matabungkay']);
    createResidentAddress($resident, $oldBarangay);

    $this->actingAs($admin)
        ->patch(route('residents.update', $resident), [
            'first_name' => 'Updated',
            'middle_name' => 'Middle',
            'last_name' => 'Resident',
            'suffix' => 'Jr.',
            'email' => 'updated.resident@example.com',
            'contact_number' => '09181234567',
            'username' => 'updated-resident',
            'house_no_street' => '45 Beach Road',
            'barangay_id' => $newBarangay->getKey(),
            'role' => User::ROLE_BFP_ADMIN,
            'status' => User::STATUS_INACTIVE,
        ])
        ->assertRedirect();

    $resident->refresh();

    expect($resident->first_name)->toBe('Updated')
        ->and($resident->role)->toBe(User::ROLE_RESIDENT)
        ->and($resident->status)->toBe(User::STATUS_ACTIVE)
        ->and($resident->details_updated_at)->not->toBeNull()
        ->and($resident->homeAddress?->barangay_id)->toBe($newBarangay->getKey());
});

test('administrator can reset a resident password and revoke mobile tokens', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $resident = User::factory()->resident()->create(['password' => 'old-password']);
    $oldPasswordHash = $resident->password;
    AuthToken::create([
        'user_id' => $resident->getKey(),
        'token' => str_repeat('a', 64),
        'expires_at' => now()->addDay(),
        'created_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->post(route('residents.resetPassword', $resident));

    $response->assertRedirect()
        ->assertSessionHas('residentTemporaryPassword')
        ->assertSessionHas('toast.message', fn (string $message) => str_contains($message, 'Temporary password:'));

    $resident->refresh();

    expect($resident->password)->not->toBe($oldPasswordHash)
        ->and($resident->password_changed_at)->not->toBeNull()
        ->and(AuthToken::where('user_id', $resident->getKey())->exists())->toBeFalse();

    expect(Hash::check(session('residentTemporaryPassword'), $resident->password))->toBeTrue();
});

test('suspending a resident revokes tokens and blocks mobile authentication with the reason', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $resident = User::factory()->resident()->create(['password' => 'password123']);
    AuthToken::create([
        'user_id' => $resident->getKey(),
        'token' => str_repeat('b', 64),
        'expires_at' => now()->addDay(),
        'created_at' => now(),
    ]);

    $this->actingAs($admin)
        ->post(route('residents.suspend', $resident), ['reason' => 'Identity verification required.'])
        ->assertRedirect();

    $resident->refresh();

    expect($resident->status)->toBe(User::STATUS_INACTIVE)
        ->and($resident->suspended_at)->not->toBeNull()
        ->and($resident->suspension_reason)->toBe('Identity verification required.')
        ->and(AuthToken::where('user_id', $resident->getKey())->exists())->toBeFalse();

    $this->postJson('/api/v1/auth/login', [
        'email' => $resident->email,
        'password' => 'password123',
    ])->assertForbidden()
        ->assertJsonPath('message', 'This account has been suspended. Please contact BFP Lian. Reason: Identity verification required.');

    $token = str_repeat('c', 64);
    AuthToken::create([
        'user_id' => $resident->getKey(),
        'token' => $token,
        'expires_at' => now()->addDay(),
        'created_at' => now(),
    ]);

    $this->withToken($token)
        ->postJson('/api/v1/auth/logout')
        ->assertForbidden()
        ->assertJsonPath('message', 'This account has been suspended. Please contact BFP Lian. Reason: Identity verification required.');
});

test('administrator can reactivate a suspended resident', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $resident = User::factory()->resident()->suspended()->create();

    $this->actingAs($admin)
        ->post(route('residents.reactivate', $resident))
        ->assertRedirect();

    $resident->refresh();

    expect($resident->status)->toBe(User::STATUS_ACTIVE)
        ->and($resident->suspended_at)->toBeNull()
        ->and($resident->suspension_reason)->toBeNull();
});
