<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('barangay schema refactor preserves coordinates and boundaries and is reversible', function () {
    $migration = require database_path('migrations/2026_10_18_203427_refactor_barangay_coordinates_and_boundary.php');

    $migration->down();

    try {
        DB::table('barangay')->insert([
            [
                'barangay_id' => 9001,
                'barangay_name' => 'Legacy Coordinates',
                'latitude' => 13.9123456,
                'longitude' => 120.6123456,
                'boundary' => json_encode([[120.6, 13.9], [120.7, 14.0], [120.6, 13.9]], JSON_THROW_ON_ERROR),
                'centroid_lat' => null,
                'centroid_lng' => null,
            ],
            [
                'barangay_id' => 9002,
                'barangay_name' => 'Existing Centroid',
                'latitude' => 13.1000000,
                'longitude' => 120.1000000,
                'boundary' => null,
                'centroid_lat' => 13.87654321,
                'centroid_lng' => 120.87654321,
            ],
        ]);

        $migration->up();
        $migration->up();

        expect(Schema::getColumnListing('barangay'))
            ->toContain('barangay_id', 'barangay_name', 'centroid_lat', 'centroid_lng', 'boundary_geojson')
            ->not->toContain('latitude', 'longitude', 'boundary');

        $legacy = DB::table('barangay')->where('barangay_id', 9001)->first();
        $existing = DB::table('barangay')->where('barangay_id', 9002)->first();

        expect((float) $legacy->centroid_lat)->toBe(13.9123456)
            ->and((float) $legacy->centroid_lng)->toBe(120.6123456)
            ->and(json_decode($legacy->boundary_geojson, true, flags: JSON_THROW_ON_ERROR)[0])->toBe([120.6, 13.9])
            ->and((float) $existing->centroid_lat)->toBe(13.87654321)
            ->and((float) $existing->centroid_lng)->toBe(120.87654321);

        $migration->down();
        $migration->down();

        expect(Schema::getColumnListing('barangay'))
            ->toContain('latitude', 'longitude', 'boundary')
            ->not->toContain('boundary_geojson');

        $rolledBack = DB::table('barangay')->where('barangay_id', 9002)->first();

        expect(round((float) $rolledBack->latitude, 7))->toBe(13.8765432)
            ->and(round((float) $rolledBack->longitude, 7))->toBe(120.8765432);
    } finally {
        $migration->up();
    }
});
