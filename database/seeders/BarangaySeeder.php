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
     * IMPORTANT: The coordinates here are NOT surveyed centroids. They're generated
     * points spread in a ring around the municipal center (13.9457, 120.6412)
     * purely so each barangay has a distinct, plausible-looking spot on the
     * Leaflet map. If precise placement ever matters (e.g. real dispatch
     * routing), replace these with actual barangay hall coordinates or a
     * PSGC/QGIS shapefile centroid.
     */
    public function run(): void
    {
        $barangays = [
            1 => ['Barangay 1 (Poblacion)', 14.0392000, 120.6389000],
            2 => ['Barangay 2 (Poblacion)', 14.0405000, 120.6401000],
            3 => ['Barangay 3 (Poblacion)', 14.0378000, 120.6410000],
            4 => ['Barangay 4 (Poblacion)', 14.0365000, 120.6395000],
            5 => ['Barangay 5 (Poblacion)', 14.0410000, 120.6375000],
            6 => ['Bagong Pook', 14.0452000, 120.6320000],
            7 => ['Balibago', 14.0300000, 120.6450000],
            8 => ['Binubusan', 14.0510000, 120.6280000],
            9 => ['Bungahan', 14.0250000, 120.6500000],
            10 => ['Cumba', 14.0480000, 120.6440000],
            11 => ['Humayingan', 14.0210000, 120.6360000],
            12 => ['Kapito', 14.0560000, 120.6350000],
            13 => ['Lumaniag', 14.0330000, 120.6280000],
            14 => ['Luyahan', 14.0180000, 120.6420000],
            15 => ['Malaruhatan', 14.0430000, 120.6470000],
            16 => ['Matabungkay', 14.0160000, 120.6180000],
            17 => ['Prenza', 14.0350000, 120.6520000],
            18 => ['Puting Kahoy', 14.0290000, 120.6210000],
            19 => ['San Diego', 14.0230000, 120.6250000],
        ];

        foreach ($barangays as $id => [$name, $lat, $lng]) {
            Barangay::updateOrCreate(
                ['barangay_id' => $id],
                ['barangay_name' => $name, 'centroid_lat' => $lat, 'centroid_lng' => $lng],
            );
        }
    }
}
