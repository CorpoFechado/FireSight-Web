<?php

namespace Database\Seeders;

use App\Models\BfpPersonnelDetails;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BfpAccountSeeder extends Seeder
{
    /**
     * Creates one bfp_admin and one bfp_personnel test account so the
     * Admin dashboard has something to log into locally. Swap the
     * passwords before this ever touches a real deployment.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@firesight.bfp.lian'],
            [
                'role' => User::ROLE_BFP_ADMIN,
                'first_name' => 'Ricardo',
                'last_name' => 'Dela Cruz',
                'contact_number' => '09171234567',
                'username' => 'rdelacruz',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        BfpPersonnelDetails::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'rank' => 'Senior Fire Officer 3',
                'station_assigned' => 'BFP Lian Fire Station',
                'employee_number' => 'EMP-001',
            ],
        );

        $personnel = User::updateOrCreate(
            ['email' => 'personnel@firesight.bfp.lian'],
            [
                'role' => User::ROLE_BFP_PERSONNEL,
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'contact_number' => '09181234568',
                'username' => 'msantos',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        BfpPersonnelDetails::updateOrCreate(
            ['user_id' => $personnel->id],
            [
                'rank' => 'Fire Officer 2',
                'station_assigned' => 'BFP Lian Fire Station',
                'employee_number' => 'EMP-002',
            ],
        );
    }
}
