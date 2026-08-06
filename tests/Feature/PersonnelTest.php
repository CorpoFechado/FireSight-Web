<?php

use App\Models\BfpPersonnelDetails;
use App\Models\User;

test('bfp personnel cannot access the personnel accounts page', function () {
    $user = User::factory()->bfpPersonnel()->create();
    $this->actingAs($user);

    $response = $this->get(route('personnel'));
    $response->assertForbidden();
});

test('bfp admin can view the personnel accounts page', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $response = $this->get(route('personnel'));
    $response->assertOk();
});

test('bfp admin can create a new personnel account', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $response = $this->post(route('personnel.store'), [
        'full_name' => 'Juan Dela Cruz',
        'rank' => 'Fire Officer 1',
        'contact_number' => '09171234567',
        'email' => 'juan.delacruz@example.com',
        'role' => 'bfp_personnel',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('toast.message', function (string $message) {
        return str_contains($message, 'Temporary password:');
    });

    $this->assertDatabaseHas('users', [
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
        'email' => 'juan.delacruz@example.com',
        'role' => 'bfp_personnel',
        'status' => 'active',
    ]);

    $created = User::where('email', 'juan.delacruz@example.com')->first();
    $this->assertDatabaseHas('bfp_personnel_details', [
        'user_id' => $created->id,
        'rank' => 'Fire Officer 1',
        'station_assigned' => 'BFP Lian Fire Station',
    ]);
});

test('bfp admin can update a personnel account, including status', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $personnel = User::factory()->bfpPersonnel()->create();
    BfpPersonnelDetails::create([
        'user_id' => $personnel->id,
        'rank' => 'Fire Officer 1',
        'station_assigned' => 'BFP Lian Fire Station',
        'employee_number' => 'EMP-100',
    ]);

    $response = $this->patch(route('personnel.update', $personnel), [
        'full_name' => 'Updated Name',
        'rank' => 'Fire Officer 2',
        'contact_number' => '09181234567',
        'email' => $personnel->email,
        'role' => 'bfp_personnel',
        'status' => 'inactive',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('users', [
        'id' => $personnel->id,
        'first_name' => 'Updated',
        'last_name' => 'Name',
        'status' => 'inactive',
    ]);

    $this->assertDatabaseHas('bfp_personnel_details', [
        'user_id' => $personnel->id,
        'rank' => 'Fire Officer 2',
    ]);
});

test('an admin cannot deactivate their own account', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $response = $this->patch(route('personnel.update', $admin), [
        'full_name' => $admin->name,
        'rank' => 'Senior Fire Officer 3',
        'contact_number' => '09171234567',
        'email' => $admin->email,
        'role' => 'bfp_admin',
        'status' => 'inactive',
    ]);

    $response->assertSessionHasErrors('status');
    $this->assertDatabaseHas('users', ['id' => $admin->id, 'status' => 'active']);
});

test('bfp admin can delete a personnel account', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $personnel = User::factory()->bfpPersonnel()->create();

    $response = $this->delete(route('personnel.destroy', $personnel));

    $response->assertRedirect();
    $this->assertDatabaseMissing('users', ['id' => $personnel->id]);
});

test('an admin cannot delete their own account', function () {
    $admin = User::factory()->bfpAdmin()->create();
    $this->actingAs($admin);

    $response = $this->delete(route('personnel.destroy', $admin));

    $response->assertSessionHasErrors('id');
    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});
