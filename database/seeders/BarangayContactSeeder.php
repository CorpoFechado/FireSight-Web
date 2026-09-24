<?php

namespace Database\Seeders;

use App\Models\Barangay;
use App\Models\BarangayContact;
use Illuminate\Database\Seeder;

class BarangayContactSeeder extends Seeder
{
    /**
     * Source: firesight_mobile_cs.barangay_contact (mobile app DB).
     *
     * IMPORTANT: that dump's `barangay_id` values do NOT line up with this
     * app's `barangay` table — e.g. mobile id 6 is Bagong Pook, but this
     * DB's id 6 is Balibago (see BarangaySeeder). So contacts here are
     * matched by `barangay_name` instead of copying the mobile IDs across,
     * which would silently attach contacts to the wrong barangay.
     *
     * Run after BarangaySeeder.
     *
     * @var array<string, array{0: string, 1: string}> barangay_name => [captain phone, councilor phone]
     */
    private const CONTACTS = [
        'Poblacion 1' => ['09941614944', '09804957679'],
        'Poblacion 2' => ['09653057935', '09452624164'],
        'Poblacion 3' => ['09563768225', '09222164528'],
        'Poblacion 4' => ['09227847309', '09636981392'],
        'Poblacion 5' => ['09469597425', '09441081504'],
        'Bagong Pook' => ['09162314220', '09993712332'],
        'Balibago' => ['09640012116', '09687303418'],
        'Binubusan' => ['09915598512', '09811341929'],
        'Bungahan' => ['09179222521', '09786350508'],
        'Cumba' => ['09840584298', '09910533529'],
        'Humayingan' => ['09389768922', '09100592007'],
        'Kapito' => ['09682209737', '09608125782'],
        'Lumaniag' => ['09363704389', '09352577395'],
        'Luyahan' => ['09162205713', '09675147340'],
        'Malaruhatan' => ['09620645876', '09903434467'],
        'Matabungkay' => ['09210531004', '09779616772'],
        'Prenza' => ['09903441220', '09932711263'],
        'Puting Kahoy' => ['09585961787', '09626279540'],
        'San Diego' => ['09641251768', '09801778301'],
    ];

    public function run(): void
    {
        foreach (self::CONTACTS as $barangayName => [$captainPhone, $councilorPhone]) {
            $barangay = Barangay::where('barangay_name', $barangayName)->first();

            if (! $barangay) {
                $this->command?->warn("No barangay row matches \"{$barangayName}\" — skipped its contacts.");

                continue;
            }

            BarangayContact::updateOrCreate(
                ['barangay_id' => $barangay->barangay_id, 'role' => 'Barangay Captain'],
                ['name' => "Hon. Captain {$barangayName}", 'phone_number' => $captainPhone],
            );

            BarangayContact::updateOrCreate(
                ['barangay_id' => $barangay->barangay_id, 'role' => 'Barangay Councilor'],
                ['name' => "Kagawad {$barangayName}", 'phone_number' => $councilorPhone],
            );
        }
    }
}
