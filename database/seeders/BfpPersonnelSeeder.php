<?php

namespace Database\Seeders;

use App\Enums\DutyGroup;
use App\Models\BfpPersonnelDetails;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

/**
 * Seeds the 17 real BFP Lian station personnel.
 *
 * Test login account:
 * - FO1 Dexter M. Pantas (ID 8) uses email 'personnel@firesight.bfp.lian'
 *   and username 'personnel' with password 'password'.
 */
class BfpPersonnelSeeder extends Seeder
{
    /**
     * Single source of truth for the 17 real BFP Lian personnel.
     *
     * @var list<array{
     *     user_id: int,
     *     rank: string,
     *     first_name: string,
     *     middle_name: string|null,
     *     last_name: string,
     *     suffix: string|null,
     *     email: string,
     *     username: string,
     *     employee_number: string,
     *     duty_group: string,
     *     key: string
     * }>
     */
    public const ROSTER = [
        [
            'user_id' => 2,
            'rank' => 'Senior Fire Officer 4',
            'first_name' => 'Felonia Amor',
            'middle_name' => 'D',
            'last_name' => 'Barcelon',
            'suffix' => null,
            'email' => 'fbarcelon@firesight.bfp.lian',
            'username' => 'fbarcelon',
            'employee_number' => 'EMP-002',
            'duty_group' => DutyGroup::Regular->value,
            'key' => 'barcelon',
        ],
        [
            'user_id' => 3,
            'rank' => 'Senior Fire Officer 3',
            'first_name' => 'Enrico',
            'middle_name' => 'R',
            'last_name' => 'Lapitan',
            'suffix' => null,
            'email' => 'elapitan@firesight.bfp.lian',
            'username' => 'elapitan',
            'employee_number' => 'EMP-003',
            'duty_group' => DutyGroup::Regular->value,
            'key' => 'lapitan',
        ],
        [
            'user_id' => 4,
            'rank' => 'Senior Fire Officer 2',
            'first_name' => 'Ma. Carmela',
            'middle_name' => 'J',
            'last_name' => 'Tenorio',
            'suffix' => null,
            'email' => 'mtenorio@firesight.bfp.lian',
            'username' => 'mtenorio',
            'employee_number' => 'EMP-004',
            'duty_group' => DutyGroup::Regular->value,
            'key' => 'tenorio',
        ],
        [
            'user_id' => 5,
            'rank' => 'Fire Officer 3',
            'first_name' => 'Michael Angelo',
            'middle_name' => 'T',
            'last_name' => 'Lagrisola',
            'suffix' => null,
            'email' => 'mlagrisola@firesight.bfp.lian',
            'username' => 'mlagrisola',
            'employee_number' => 'EMP-005',
            'duty_group' => DutyGroup::Regular->value,
            'key' => 'lagrisola',
        ],
        [
            'user_id' => 6,
            'rank' => 'Senior Fire Officer 2',
            'first_name' => 'Ana Maria',
            'middle_name' => 'A',
            'last_name' => 'Lainez',
            'suffix' => null,
            'email' => 'alainez@firesight.bfp.lian',
            'username' => 'alainez',
            'employee_number' => 'EMP-006',
            'duty_group' => DutyGroup::A->value,
            'key' => 'lainez',
        ],
        [
            'user_id' => 7,
            'rank' => 'Senior Fire Officer 1',
            'first_name' => 'Alejandro',
            'middle_name' => 'A',
            'last_name' => 'Vergara',
            'suffix' => null,
            'email' => 'avergara@firesight.bfp.lian',
            'username' => 'avergara',
            'employee_number' => 'EMP-007',
            'duty_group' => DutyGroup::B->value,
            'key' => 'vergara',
        ],
        [
            'user_id' => 8,
            'rank' => 'Fire Officer 1',
            'first_name' => 'Dexter',
            'middle_name' => 'M',
            'last_name' => 'Pantas',
            'suffix' => null,
            'email' => 'personnel@firesight.bfp.lian',
            'username' => 'personnel',
            'employee_number' => 'EMP-008',
            'duty_group' => DutyGroup::B->value,
            'key' => 'pantas',
        ],
        [
            'user_id' => 34,
            'rank' => 'Fire Officer 2',
            'first_name' => 'Reylan',
            'middle_name' => 'L',
            'last_name' => 'Asaytuno',
            'suffix' => null,
            'email' => 'rasaytuno@firesight.bfp.lian',
            'username' => 'rasaytuno',
            'employee_number' => 'EMP-009',
            'duty_group' => DutyGroup::A->value,
            'key' => 'asaytuno',
        ],
        [
            'user_id' => 35,
            'rank' => 'Fire Officer 2',
            'first_name' => 'Jerome',
            'middle_name' => 'B',
            'last_name' => 'Pagsaligan',
            'suffix' => null,
            'email' => 'jpagsaligan@firesight.bfp.lian',
            'username' => 'jpagsaligan',
            'employee_number' => 'EMP-010',
            'duty_group' => DutyGroup::A->value,
            'key' => 'pagsaligan',
        ],
        [
            'user_id' => 36,
            'rank' => 'Fire Officer 2',
            'first_name' => 'Antonio',
            'middle_name' => 'P',
            'last_name' => 'Nepomuceno',
            'suffix' => 'III',
            'email' => 'anepomuceno@firesight.bfp.lian',
            'username' => 'anepomuceno',
            'employee_number' => 'EMP-011',
            'duty_group' => DutyGroup::A->value,
            'key' => 'nepomuceno',
        ],
        [
            'user_id' => 37,
            'rank' => 'Fire Officer 1',
            'first_name' => 'Vergel',
            'middle_name' => 'A',
            'last_name' => 'Lozada',
            'suffix' => null,
            'email' => 'vlozada@firesight.bfp.lian',
            'username' => 'vlozada',
            'employee_number' => 'EMP-012',
            'duty_group' => DutyGroup::A->value,
            'key' => 'lozada',
        ],
        [
            'user_id' => 38,
            'rank' => 'Fire Officer 1',
            'first_name' => 'John Denver',
            'middle_name' => 'A',
            'last_name' => 'De Leon',
            'suffix' => null,
            'email' => 'jdeleon@firesight.bfp.lian',
            'username' => 'jdeleon',
            'employee_number' => 'EMP-013',
            'duty_group' => DutyGroup::A->value,
            'key' => 'deleon',
        ],
        [
            'user_id' => 39,
            'rank' => 'Fire Officer 1',
            'first_name' => 'Jessa Pola',
            'middle_name' => 'B',
            'last_name' => 'Villaveza',
            'suffix' => null,
            'email' => 'jvillaveza@firesight.bfp.lian',
            'username' => 'jvillaveza',
            'employee_number' => 'EMP-014',
            'duty_group' => DutyGroup::A->value,
            'key' => 'villaveza',
        ],
        [
            'user_id' => 40,
            'rank' => 'Fire Officer 2',
            'first_name' => 'Patricio',
            'middle_name' => 'R',
            'last_name' => 'Lopez',
            'suffix' => null,
            'email' => 'plopez@firesight.bfp.lian',
            'username' => 'plopez',
            'employee_number' => 'EMP-015',
            'duty_group' => DutyGroup::B->value,
            'key' => 'lopez',
        ],
        [
            'user_id' => 41,
            'rank' => 'Fire Officer 1',
            'first_name' => 'Elpidio',
            'middle_name' => 'M',
            'last_name' => 'Gutierrez',
            'suffix' => null,
            'email' => 'egutierrez@firesight.bfp.lian',
            'username' => 'egutierrez',
            'employee_number' => 'EMP-016',
            'duty_group' => DutyGroup::B->value,
            'key' => 'gutierrez',
        ],
        [
            'user_id' => 42,
            'rank' => 'Fire Officer 1',
            'first_name' => 'Leo Angelo',
            'middle_name' => 'V',
            'last_name' => 'Villadelrey',
            'suffix' => null,
            'email' => 'lvilladelrey@firesight.bfp.lian',
            'username' => 'lvilladelrey',
            'employee_number' => 'EMP-017',
            'duty_group' => DutyGroup::B->value,
            'key' => 'villadelrey',
        ],
        [
            'user_id' => 43,
            'rank' => 'Fire Officer 1',
            'first_name' => 'Erwin',
            'middle_name' => 'A',
            'last_name' => 'Ellao',
            'suffix' => null,
            'email' => 'eellao@firesight.bfp.lian',
            'username' => 'eellao',
            'employee_number' => 'EMP-018',
            'duty_group' => DutyGroup::B->value,
            'key' => 'ellao',
        ],
    ];

    public function run(): void
    {
        User::unguard();
        BfpPersonnelDetails::unguard();

        foreach (self::ROSTER as $item) {
            $contactNumber = sprintf('0917%07d', 1000000 + $item['user_id']);

            $user = User::updateOrCreate(
                ['email' => $item['email']],
                [
                    'user_id' => $item['user_id'],
                    'role' => User::ROLE_BFP_PERSONNEL,
                    'status' => User::STATUS_ACTIVE,
                    'first_name' => $item['first_name'],
                    'middle_name' => $item['middle_name'],
                    'last_name' => $item['last_name'],
                    'suffix' => $item['suffix'],
                    'contact_number' => $contactNumber,
                    'username' => $item['username'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
            );

            BfpPersonnelDetails::updateOrCreate(
                ['user_id' => $user->getKey()],
                [
                    'rank' => $item['rank'],
                    'station_assigned' => 'BFP Lian Fire Station',
                    'employee_number' => $item['employee_number'],
                    'duty_group' => $item['duty_group'],
                ],
            );
        }

        User::reguard();
        BfpPersonnelDetails::reguard();
    }

    /**
     * Resolve email address for a known roster member by key or surname.
     */
    public static function emailFor(string $key): string
    {
        $keyLower = strtolower(trim($key));

        foreach (self::ROSTER as $item) {
            if ($item['key'] === $keyLower || str_contains(strtolower($item['email']), $keyLower)) {
                return $item['email'];
            }
        }

        throw new InvalidArgumentException("No BFP personnel found matching '{$key}'.");
    }
}
