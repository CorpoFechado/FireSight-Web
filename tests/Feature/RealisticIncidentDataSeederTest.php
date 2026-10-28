<?php

use App\Models\User;
use App\Support\Geo;
use Database\Seeders\RealisticIncidentDataSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

test('it rebuilds realistic incident analytics data without changing protected records', function () {
    Carbon::setTestNow('2026-10-28 16:00:00');

    try {
        $resident = User::factory()->create();
        User::factory()->bfpPersonnel()->count(3)->create();
        User::factory()->bfpAdmin()->create();

        foreach (range(1, 4) as $barangayId) {
            $latitude = 14.00 + $barangayId / 100;
            $longitude = 120.60 + $barangayId / 100;
            $boundary = [
                [$longitude - 0.01, $latitude - 0.01],
                [$longitude + 0.01, $latitude - 0.01],
                [$longitude + 0.01, $latitude + 0.01],
                [$longitude - 0.01, $latitude + 0.01],
                [$longitude - 0.01, $latitude - 0.01],
            ];

            DB::table('barangay')->insert([
                'barangay_id' => $barangayId,
                'barangay_name' => $barangayId === 1 ? 'Barangay 1 (Poblacion)' : 'Test Barangay '.$barangayId,
                'boundary_geojson' => json_encode($boundary, JSON_THROW_ON_ERROR),
                'centroid_lat' => $latitude,
                'centroid_lng' => $longitude,
            ]);
        }

        DB::table('resident_address')->insert([
            'user_id' => $resident->user_id,
            'house_no_street' => '12 Test Street',
            'barangay_id' => 1,
            'municipality' => 'Lian',
            'province' => 'Batangas',
        ]);

        $oldReportId = DB::table('community_report')->insertGetId([
            'user_id' => $resident->user_id,
            'reporter_name' => $resident->name,
            'contact_number' => $resident->contact_number,
            'description' => 'Old report replaced by the analytics seeder.',
            'report_image' => 'report_images/existing-test.jpg',
            'latitude' => 14.01,
            'longitude' => 120.61,
            'barangay_id' => 1,
            'status' => 'pending',
            'created_at' => now()->subYear(),
            'updated_at' => now()->subYear(),
        ]);
        DB::table('report_evidence')->insert([
            'report_id' => $oldReportId,
            'image_path' => 'uploads/evidence/existing-test.jpg',
            'uploaded_by' => null,
            'caption' => 'Existing evidence path',
            'created_at' => now()->subYear(),
        ]);

        $protectedCounts = [
            'users' => DB::table('users')->count(),
            'resident_address' => DB::table('resident_address')->count(),
            'bfp_personnel_details' => DB::table('bfp_personnel_details')->count(),
        ];

        $this->seed(RealisticIncidentDataSeeder::class);

        expect(DB::table('community_report')->count())->toBe(385)
            ->and(DB::table('incident_record')->count())->toBe(301)
            ->and(DB::table('report_status_history')->count())->toBe(1372)
            ->and(DB::table('report_link')->count())->toBeGreaterThanOrEqual(5)
            ->and(DB::table('report_evidence')->count())->toBeGreaterThan(0)
            ->and(DB::table('users')->count())->toBe($protectedCounts['users'])
            ->and(DB::table('resident_address')->count())->toBe($protectedCounts['resident_address'])
            ->and(DB::table('bfp_personnel_details')->count())->toBe($protectedCounts['bfp_personnel_details']);

        expect(DB::table('community_report')->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status')->map(fn ($count): int => (int) $count)->all())
            ->toMatchArray([
                'resolved' => 301,
                'invalid' => 36,
                'pending' => 16,
                'accepted' => 16,
                'dispatched' => 16,
            ]);

        $types = DB::table('incident_record')
            ->selectRaw('incident_type, COUNT(*) as total')
            ->groupBy('incident_type')
            ->pluck('total', 'incident_type');
        $incidentTotal = (int) $types->sum();

        expect((int) round((int) $types['residential_fire'] / $incidentTotal * 100))->toBeBetween(38, 42)
            ->and((int) round((int) $types['rubbish_fire'] / $incidentTotal * 100))->toBeBetween(18, 22)
            ->and((int) DB::table('incident_record')->whereIn('alarm_level', ['task_force_alpha', 'task_force_bravo', 'general_alarm'])->count())->toBe(2);

        $lastStatusMismatches = DB::table('community_report')->get()->filter(function (object $report): bool {
            $lastStatus = DB::table('report_status_history')
                ->where('report_id', $report->report_id)
                ->orderByDesc('created_at')
                ->orderByDesc('history_id')
                ->value('status');

            return $lastStatus !== $report->status;
        });

        expect($lastStatusMismatches)->toBeEmpty()
            ->and(DB::table('community_report as reports')
                ->join('users', 'reports.user_id', '=', 'users.user_id')
                ->whereNotNull('reports.user_id')
                ->where('users.role', '!=', 'resident')
                ->count())->toBe(0)
            ->and(DB::table('report_status_history as history')
                ->join('users', 'history.changed_by', '=', 'users.user_id')
                ->whereNotIn('users.role', ['bfp_personnel', 'bfp_admin'])
                ->count())->toBe(0)
            ->and(DB::table('incident_record as incidents')
                ->join('community_report as reports', 'incidents.report_id', '=', 'reports.report_id')
                ->where('reports.status', '!=', 'resolved')
                ->count())->toBe(0)
            ->and(DB::table('community_report as reports')
                ->leftJoin('incident_record as incidents', 'reports.report_id', '=', 'incidents.report_id')
                ->where('reports.status', 'resolved')
                ->whereNull('incidents.incident_id')
                ->count())->toBe(0)
            ->and(DB::table('community_report')->whereDate('created_at', '2026-10-28')->count())->toBeGreaterThan(0)
            ->and(DB::table('community_report')->whereDate('created_at', '2026-10-27')->count())->toBeGreaterThan(0);

        $boundaries = DB::table('barangay')->pluck('boundary_geojson', 'barangay_id');
        $outsideBoundaryCount = DB::table('community_report')->get()->filter(function (object $report) use ($boundaries): bool {
            $polygon = json_decode($boundaries[$report->barangay_id], true, flags: JSON_THROW_ON_ERROR);

            return ! Geo::pointInPolygon((float) $report->latitude, (float) $report->longitude, $polygon);
        })->count();

        expect($outsideBoundaryCount)->toBe(0);

        $this->seed(RealisticIncidentDataSeeder::class);

        expect(DB::table('community_report')->count())->toBe(385)
            ->and(DB::table('incident_record')->count())->toBe(301)
            ->and(DB::table('report_status_history')->count())->toBe(1372)
            ->and(DB::table('users')->count())->toBe($protectedCounts['users'])
            ->and(DB::table('resident_address')->count())->toBe($protectedCounts['resident_address']);
    } finally {
        Carbon::setTestNow();
    }
});
