<?php

namespace Database\Seeders;

use App\Models\Barangay;
use Illuminate\Database\Seeder;

class BarangaySeeder extends Seeder
{
    /**
     * The 19 barangays of Lian, Batangas — same IDs as firesight_db2.sql
     * so any data that references barangay_id lines up.
     *
     * IMPORTANT: lat/lng here are NOT surveyed centroids. They're generated
     * points spread in a ring around the municipal center (13.9457, 120.6412)
     * purely so each barangay has a distinct, plausible-looking spot on the
     * Leaflet map. If precise placement ever matters (e.g. real dispatch
     * routing), replace these with actual barangay hall coordinates or a
     * PSGC/QGIS shapefile centroid.
     */
    public function run(): void
    {
        $barangays = [
            1 => ['Poblacion 1', 13.9577000, 120.6412000],
            2 => ['Poblacion 2', 13.9494082, 120.6531833],
            3 => ['Poblacion 3', 13.9359918, 120.6486061],
            4 => ['Poblacion 4', 13.9359918, 120.6337939],
            5 => ['Poblacion 5', 13.9494082, 120.6292167],
            6 => ['Balibago', 13.9762708, 120.6511295],
            7 => ['Bagong Pook', 13.9691402, 120.6640735],
            8 => ['Binubusan', 13.9573671, 120.6724872],
            9 => ['Bungahan', 13.9432831, 120.6747040],
            10 => ['Cumba', 13.9296778, 120.6702850],
            11 => ['Humayingan', 13.9192459, 120.6601053],
            12 => ['Malaruhatan', 13.9140536, 120.6461812],
            13 => ['Matabungkay', 13.9151292, 120.6312705],
            14 => ['Prenza', 13.9222598, 120.6183265],
            15 => ['Lumaniag', 13.9340329, 120.6099128],
            16 => ['Luyahan', 13.9481169, 120.6076960],
            17 => ['Kapito', 13.9617222, 120.6121150],
            18 => ['San Diego', 13.9721541, 120.6222947],
            19 => ['Puting Kahoy', 13.9773464, 120.6362188],
        ];

        foreach ($barangays as $id => [$name, $lat, $lng]) {
            Barangay::updateOrCreate(
                ['barangay_id' => $id],
                ['barangay_name' => $name, 'latitude' => $lat, 'longitude' => $lng],
            );
        }
    }
}
