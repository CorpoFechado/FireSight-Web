<?php

use App\Models\AuthToken;
use App\Models\Barangay;
use App\Models\ResidentAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Barangay::create([
        'barangay_id' => 1,
        'barangay_name' => 'Poblacion 1',
        'latitude' => 14.035,
        'longitude' => 120.654,
    ]);
});

test('login succeeds with email and returns correct token and user shape', function (): void {
    $user = User::create([
        'role' => User::ROLE_RESIDENT,
        'status' => User::STATUS_ACTIVE,
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
        'email' => 'juan@example.com',
        'contact_number' => '09171234567',
        'password' => Hash::make('password123'),
        'is_verified' => true,
    ]);

    ResidentAddress::create([
        'user_id' => $user->user_id,
        'house_no_street' => '123 Rizal St',
        'barangay_id' => 1,
        'municipality' => 'Lian',
        'province' => 'Batangas',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'juan@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'token',
            'user' => [
                'user_id',
                'role',
                'first_name',
                'middle_name',
                'last_name',
                'suffix',
                'contact_number',
                'email',
                'profile_image',
                'is_verified',
                'address' => [
                    'house_no_street',
                    'barangay_id',
                    'barangay_name',
                    'municipality',
                    'province',
                ],
                'personnel_details',
            ],
        ]);

    $token = $response->json('token');
    expect(strlen($token))->toBe(64);

    $this->assertDatabaseHas('auth_token', [
        'user_id' => $user->user_id,
        'token' => $token,
    ]);
});

test('login succeeds with mobile number lookup', function (): void {
    $user = User::create([
        'role' => User::ROLE_RESIDENT,
        'status' => User::STATUS_ACTIVE,
        'first_name' => 'Maria',
        'last_name' => 'Santos',
        'email' => 'maria@example.com',
        'contact_number' => '0918-123-4567',
        'password' => Hash::make('secretpass123'),
        'is_verified' => false,
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'login' => '09181234567',
        'password' => 'secretpass123',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('user.first_name', 'Maria')
        ->assertJsonPath('user.contact_number', '0918-123-4567');
});

test('login fails with wrong password returning generic 401', function (): void {
    User::create([
        'role' => User::ROLE_RESIDENT,
        'status' => User::STATUS_ACTIVE,
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
        'email' => 'juan@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'juan@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertJson(['message' => 'Incorrect credentials. Please check your email or mobile number.']);
});

test('login fails for suspended user returning 403', function (): void {
    User::create([
        'role' => User::ROLE_RESIDENT,
        'status' => User::STATUS_INACTIVE,
        'first_name' => 'Banned',
        'last_name' => 'User',
        'email' => 'banned@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'banned@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(403)
        ->assertJson(['message' => 'This account has been suspended. Please contact BFP Lian.']);
});

test('register creates user address and token returning 201', function (): void {
    $payload = [
        'first_name' => 'Pedro',
        'middle_name' => 'Cruz',
        'last_name' => 'Penduko',
        'suffix' => 'Jr.',
        'mobile_number' => '09201234567',
        'house_no_street' => 'Block 5 Lot 2',
        'barangay_id' => 1,
        'municipality' => 'Lian',
        'province' => 'Batangas',
        'email' => 'pedro@example.com',
        'password' => 'password123',
    ];

    $response = $this->postJson('/api/v1/auth/register', $payload);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'token',
            'user' => [
                'user_id',
                'first_name',
                'last_name',
                'email',
                'address' => [
                    'house_no_street',
                    'barangay_id',
                    'barangay_name',
                ],
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'pedro@example.com',
        'first_name' => 'Pedro',
        'role' => 'resident',
        'status' => 'active',
    ]);

    $this->assertDatabaseHas('resident_address', [
        'house_no_street' => 'Block 5 Lot 2',
        'barangay_id' => 1,
    ]);
});

test('register fails with duplicate email returning 409', function (): void {
    User::create([
        'role' => User::ROLE_RESIDENT,
        'status' => User::STATUS_ACTIVE,
        'first_name' => 'Existing',
        'last_name' => 'User',
        'email' => 'duplicate@example.com',
        'password' => Hash::make('password123'),
    ]);

    $payload = [
        'first_name' => 'New',
        'last_name' => 'User',
        'mobile_number' => '09201234567',
        'barangay_id' => 1,
        'municipality' => 'Lian',
        'province' => 'Batangas',
        'email' => 'duplicate@example.com',
        'password' => 'password123',
    ];

    $response = $this->postJson('/api/v1/auth/register', $payload);

    $response->assertStatus(409)
        ->assertJson(['message' => 'An account with this email already exists. Try signing in instead.']);
});

test('logout deletes token when authenticated', function (): void {
    $user = User::create([
        'role' => User::ROLE_RESIDENT,
        'status' => User::STATUS_ACTIVE,
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'logout@example.com',
        'password' => Hash::make('password123'),
    ]);

    $token = bin2hex(random_bytes(32));
    AuthToken::create([
        'user_id' => $user->user_id,
        'token' => $token,
        'expires_at' => now()->addDays(90),
    ]);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/auth/logout');

    $response->assertStatus(200)
        ->assertJson(['message' => 'Signed out successfully.']);

    $this->assertDatabaseMissing('auth_token', ['token' => $token]);
});

test('logout fails without authorization header returning 401', function (): void {
    $response = $this->postJson('/api/v1/auth/logout');

    $response->assertStatus(401)
        ->assertJson(['message' => 'Authentication required. Please sign in again.']);
});

test('forgot password creates token and returns success message', function (): void {
    User::create([
        'role' => User::ROLE_RESIDENT,
        'status' => User::STATUS_ACTIVE,
        'first_name' => 'Forgot',
        'last_name' => 'User',
        'email' => 'forgot@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/v1/auth/forgot-password', [
        'email' => 'forgot@example.com',
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'If an account exists for that email, password reset instructions have been sent.']);

    $this->assertDatabaseHas('password_reset_tokens', [
        'email' => 'forgot@example.com',
    ]);
});

test('reset password updates user password and revokes all auth tokens', function (): void {
    $user = User::create([
        'role' => User::ROLE_RESIDENT,
        'status' => User::STATUS_ACTIVE,
        'first_name' => 'Reset',
        'last_name' => 'User',
        'email' => 'reset@example.com',
        'password' => Hash::make('oldpassword'),
    ]);

    $token = bin2hex(random_bytes(32));
    DB::table('password_reset_tokens')->insert([
        'email' => 'reset@example.com',
        'token' => $token,
        'created_at' => now(),
    ]);

    AuthToken::create([
        'user_id' => $user->user_id,
        'token' => bin2hex(random_bytes(32)),
        'expires_at' => now()->addDays(90),
    ]);

    $response = $this->postJson('/api/v1/auth/reset-password', [
        'token' => $token,
        'new_password' => 'brandnewpassword123',
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Your password has been reset successfully. Please sign in with your new password.']);

    $user->refresh();
    expect(Hash::check('brandnewpassword123', $user->password))->toBeTrue()
        ->and($user->password_changed_at)->not->toBeNull();

    // Reset token deleted
    $this->assertDatabaseMissing('password_reset_tokens', ['token' => $token]);
    // All auth tokens revoked
    $this->assertDatabaseMissing('auth_token', ['user_id' => $user->user_id]);
});

test('google login returns 501 not implemented', function (): void {
    $response = $this->postJson('/api/v1/auth/google-login');

    $response->assertStatus(501)
        ->assertJson(['message' => 'Google Sign-In is not yet configured on this server.']);
});

test('legacy .php endpoints work for backwards compatibility', function (): void {
    $user = User::create([
        'role' => User::ROLE_RESIDENT,
        'status' => User::STATUS_ACTIVE,
        'first_name' => 'Legacy',
        'last_name' => 'Tester',
        'email' => 'legacy@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/auth/login.php', [
        'email' => 'legacy@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('user.first_name', 'Legacy');
});
