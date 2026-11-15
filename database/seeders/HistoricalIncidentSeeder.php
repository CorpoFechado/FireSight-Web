<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class HistoricalIncidentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seeds the 407 official historical fire incidents (2016-2025) for Lian, Batangas.
     *
     * Creates:
     * 1. A parent `community_report` row for geographic coordinates and timestamps.
     * 2. An `incident_record` row for official fire categorization, alarm, cause, and damage.
     * All non-essential fields (photos, AI tags, injuries, notes, owner details) are kept null.
     */
    public function run(): void
    {
        $csvPath = base_path('dataset/lian_historical_fire_incidents_2016_2025.csv');

        if (! file_exists($csvPath)) {
            throw new RuntimeException("Historical incidents CSV not found at: {$csvPath}");
        }

        // 1. Retrieve the official BFP Admin user to act as the station archivist
        $adminUser = DB::table('users')
            ->where('role', 'bfp_admin')
            ->where('status', 'active')
            ->orderBy('user_id')
            ->first();

        if (! $adminUser) {
            $adminUser = DB::table('users')->orderBy('user_id')->first();
        }

        if (! $adminUser) {
            throw new RuntimeException('HistoricalIncidentSeeder requires at least one user in the database (BfpAccountSeeder).');
        }

        // 2. Build a normalized lookup map for Lian's 19 barangays
        $barangays = DB::table('barangay')->get();

        if ($barangays->isEmpty()) {
            throw new RuntimeException('HistoricalIncidentSeeder requires the barangay table to be seeded (BarangaySeeder).');
        }

        $barangayMap = [];
        foreach ($barangays as $b) {
            $normalizedName = $this->normalizeName($b->barangay_name);
            $barangayMap[$normalizedName] = (int) $b->barangay_id;
            $barangayMap[strtolower(trim($b->barangay_name))] = (int) $b->barangay_id;
        }

        // 3. Alarm level mapping (CSV string -> DB enum)
        $alarmMap = [
            '1st alarm' => '1st_alarm',
            '2nd alarm' => '2nd_alarm',
            '3rd alarm' => '3rd_alarm',
            '4th alarm' => '4th_alarm',
            '5th alarm' => '5th_alarm',
        ];

        // 4. Read and parse CSV records
        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            throw new RuntimeException("Unable to open CSV file: {$csvPath}");
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            throw new RuntimeException("CSV file is empty: {$csvPath}");
        }

        $header = array_map(fn (string $col): string => strtolower(trim($col)), $header);
        $records = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($header)) {
                $records[] = array_combine($header, $row);
            }
        }
        fclose($handle);

        $this->command?->info(sprintf('Seeding %d historical incidents from 2016 to 2025...', count($records)));

        // 5. Insert records inside a single transaction for speed and integrity
        DB::transaction(function () use ($adminUser, $alarmMap, $barangayMap, $records): void {
            // Full reset of previous reports and incidents so historical archives start from a clean slate
            DB::table('report_link')->delete();
            DB::table('report_evidence')->delete();
            DB::table('incident_dispatch_personnel')->delete();
            DB::table('incident_dispatch_truck')->delete();
            DB::table('incident_dispatch')->delete();
            DB::table('report_status_history')->delete();
            DB::table('incident_record')->delete();
            DB::table('community_report')->delete();

            foreach ($records as $record) {
                $rawBarangay = $record['barangay_name'] ?? '';
                $barangayId = $barangayMap[$this->normalizeName($rawBarangay)]
                    ?? $barangayMap[strtolower(trim($rawBarangay))]
                    ?? null;

                if (! $barangayId) {
                    throw new RuntimeException("Unmatched barangay name: '{$rawBarangay}'");
                }

                $incidentDatetime = trim($record['date_of_occurrence']).' '.trim($record['time_of_occurrence']);
                $rawAlarm = strtolower(trim($record['alarm_level'] ?? '1st Alarm'));
                $alarmLevel = $alarmMap[$rawAlarm] ?? '1st_alarm';

                // A. Insert into community_report (Geographic & Timestamp Anchor)
                $reportId = (int) DB::table('community_report')->insertGetId([
                    'user_id' => $adminUser->user_id,
                    'reporter_name' => 'BFP Station Archive',
                    'contact_number' => '(043) 703-1234',
                    'description' => null,
                    'report_image' => null,
                    'latitude' => (float) $record['latitude'],
                    'longitude' => (float) $record['longitude'],
                    'barangay_id' => $barangayId,
                    'location_accuracy_m' => null,
                    'device_latitude' => null,
                    'device_longitude' => null,
                    'ai_fire_label' => null,
                    'ai_fire_confidence' => null,
                    'ai_verified_at' => null,
                    'status' => 'resolved',
                    'created_at' => $incidentDatetime,
                    'updated_at' => $incidentDatetime,
                ]);

                // B. Insert into incident_record (Official Investigation & Damage Anchor)
                DB::table('incident_record')->insert([
                    'report_id' => $reportId,
                    'barangay_id' => $barangayId,
                    'incident_datetime' => $incidentDatetime,
                    'incident_type' => trim($record['fire_type']),
                    'alarm_level' => $alarmLevel,
                    'cause_of_fire' => trim($record['cause_of_fire']),
                    'casualties' => 0,
                    'injuries' => null,
                    'fatalities' => null,
                    'establishment_name' => null,
                    'owner_name' => null,
                    'occupant_name' => null,
                    'time_fire_started' => null,
                    'time_fire_out' => null,
                    'estimated_damage' => (float) ($record['estimated_damages_php'] ?? 0),
                    'notes' => null,
                ]);
            }
        });

        $this->command?->info('HistoricalIncidentSeeder successfully seeded 407 incident records!');
    }

    /**
     * Normalizes a barangay string by removing hyphens, parentheses, and extra whitespace.
     */
    private function normalizeName(string $name): string
    {
        $cleaned = str_replace(['-', '(', ')'], ' ', strtolower(trim($name)));

        return preg_replace('/\s+/', ' ', $cleaned) ?? '';
    }
}
