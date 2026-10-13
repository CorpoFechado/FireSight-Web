<?php

namespace Database\Seeders;

use App\Models\BfpPersonnelDetails;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BfpAccountSeeder extends Seeder
{
    /**
     * Seeds the primary bfp_admin account (id 1) used for local administration.
     * Admin has no duty_group assigned.
     */
    public function run(): void
    {
        User::unguard();
        BfpPersonnelDetails::unguard();

        $admin = User::updateOrCreate(
            ['email' => 'admin2@firesight.bfp.lian'],
            [
                'id' => 1,
                'role' => User::ROLE_BFP_ADMIN,
                'first_name' => 'FireSight',
                'last_name' => 'Admin',
                'contact_number' => '09171234567',
                'username' => 'admin2',
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
                'duty_group' => null,
            ],
        );

        User::reguard();
        BfpPersonnelDetails::reguard();
    }
}
