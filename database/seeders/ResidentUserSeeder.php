<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ResidentUserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seeds realistic resident citizen accounts for Lian, Batangas.
     * All accounts use the standard password 'password' for testing and authentication.
     */
    public function run(): void
    {
        $password = Hash::make('password');

        $residents = [
            ['first_name' => 'Ren', 'last_name' => 'Adrias', 'email' => 'test@gmail.com', 'contact_number' => '09123456789', 'username' => 'renadrias'],
            ['first_name' => 'Jennifer', 'last_name' => 'Rivera', 'email' => 'jrivera@gmail.com', 'contact_number' => '09316944844', 'username' => 'jrivera'],
            ['first_name' => 'Jennifer', 'last_name' => 'Garcia', 'email' => 'jgarcia@gmail.com', 'contact_number' => '09249585092', 'username' => 'jgarcia'],
            ['first_name' => 'Ricardo', 'last_name' => 'Malabanan', 'email' => 'rmalabanan@gmail.com', 'contact_number' => '09434027113', 'username' => 'rmalabanan'],
            ['first_name' => 'Erlinda', 'last_name' => 'Garcia', 'email' => 'egarcia@gmail.com', 'contact_number' => '09556448196', 'username' => 'egarcia'],
            ['first_name' => 'Marilou', 'last_name' => 'Malabanan', 'email' => 'mmalabanan@gmail.com', 'contact_number' => '09538302652', 'username' => 'mmalabanan'],
            ['first_name' => 'Ana', 'last_name' => 'Umali', 'email' => 'aumali@gmail.com', 'contact_number' => '09394703907', 'username' => 'aumali'],
            ['first_name' => 'Angelica', 'last_name' => 'Rosales', 'email' => 'arosales@gmail.com', 'contact_number' => '09258449460', 'username' => 'arosales'],
            ['first_name' => 'Michael', 'last_name' => 'Fernandez', 'email' => 'mfernandez@gmail.com', 'contact_number' => '09541735568', 'username' => 'mfernandez'],
            ['first_name' => 'Ernesto', 'last_name' => 'Umali', 'email' => 'eumali@gmail.com', 'contact_number' => '09436665249', 'username' => 'eumali'],
            ['first_name' => 'Josefina', 'last_name' => 'Marasigan', 'email' => 'jmarasigan@gmail.com', 'contact_number' => '09932893941', 'username' => 'jmarasigan'],
            ['first_name' => 'Rodrigo', 'last_name' => 'Rivera', 'email' => 'rrivera@gmail.com', 'contact_number' => '09837194506', 'username' => 'rrivera'],
            ['first_name' => 'Kristine', 'last_name' => 'Aguilar', 'email' => 'kaguilar@gmail.com', 'contact_number' => '09210062915', 'username' => 'kaguilar'],
            ['first_name' => 'Jennifer', 'last_name' => 'Villanueva', 'email' => 'jvillanueva@gmail.com', 'contact_number' => '09437484583', 'username' => 'jvillanueva'],
            ['first_name' => 'Mark', 'last_name' => 'Navarro', 'email' => 'mnavarro@gmail.com', 'contact_number' => '09920854579', 'username' => 'mnavarro'],
            ['first_name' => 'Rolando', 'last_name' => 'Villanueva', 'email' => 'rvillanueva@gmail.com', 'contact_number' => '09840256940', 'username' => 'rvillanueva'],
            ['first_name' => 'Manuel', 'last_name' => 'Flores', 'email' => 'mflores@gmail.com', 'contact_number' => '09949925830', 'username' => 'mflores'],
            ['first_name' => 'Leonora', 'last_name' => 'Navarro', 'email' => 'lnavarro@gmail.com', 'contact_number' => '09776563708', 'username' => 'lnavarro'],
            ['first_name' => 'Manuel', 'last_name' => 'Pascual', 'email' => 'mpascual@gmail.com', 'contact_number' => '09679908599', 'username' => 'mpascual'],
            ['first_name' => 'Rosario', 'last_name' => 'Bautista', 'email' => 'rbautista@gmail.com', 'contact_number' => '09823715057', 'username' => 'rbautista'],
            ['first_name' => 'Leonora', 'last_name' => 'Malabanan', 'email' => 'lmalabanan@gmail.com', 'contact_number' => '09218212356', 'username' => 'lmalabanan'],
            ['first_name' => 'Alfredo', 'last_name' => 'Pascual', 'email' => 'apascual@gmail.com', 'contact_number' => '09419953851', 'username' => 'apascual'],
            ['first_name' => 'Rosario', 'last_name' => 'Umali', 'email' => 'rumali@gmail.com', 'contact_number' => '09726567501', 'username' => 'rumali'],
            ['first_name' => 'Jennifer', 'last_name' => 'Garcia Jr.', 'email' => 'jgarcia2@gmail.com', 'contact_number' => '09465143362', 'username' => 'jgarcia2'],
            ['first_name' => 'Juan', 'last_name' => 'Corpuz', 'email' => 'jcorpuz@gmail.com', 'contact_number' => '09922751234', 'username' => 'jcorpuz'],
            ['first_name' => 'Kristine', 'last_name' => 'Mendoza', 'email' => 'kmendoza@gmail.com', 'contact_number' => '09695119047', 'username' => 'kmendoza'],
        ];

        foreach ($residents as $data) {
            User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'role' => User::ROLE_RESIDENT,
                    'status' => User::STATUS_ACTIVE,
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'contact_number' => $data['contact_number'],
                    'username' => $data['username'],
                    'password' => $password,
                    'is_verified' => true,
                    'email_verified_at' => now(),
                ]
            );
        }

        $this->command?->info(sprintf('ResidentUserSeeder successfully ensured %d resident accounts exist.', count($residents)));
    }
}
