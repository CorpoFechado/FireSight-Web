<?php

namespace Database\Seeders;

use App\Models\Barangay;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use RuntimeException;

class BarangayBoundarySeeder extends Seeder
{
    /**
     * Populates `barangay.boundary` from the exterior ring of each polygon
     * in database/seeders/data/barangay-boundaries.geojson. Matches purely
     * by `barangay_name`, so run BarangaySeeder first.
     */
    public function run(): void
    {
        $path = database_path('seeders/data/barangay-boundaries.geojson');

        if (! File::exists($path)) {
            throw new RuntimeException("Boundary file not found at {$path}");
        }

        $geojson = json_decode(File::get($path), associative: true, flags: JSON_THROW_ON_ERROR);

        foreach ($geojson['features'] as $feature) {
            $name = $feature['properties']['barangay_name'] ?? null;
            // Exterior ring only (index 0) — these polygons have no holes.
            $ring = $feature['geometry']['coordinates'][0] ?? null;

            if (! $name || ! $ring) {
                continue;
            }

            $barangay = Barangay::where('barangay_name', $name)->first();

            if (! $barangay) {
                $this->command?->warn("No barangay row matches boundary name \"{$name}\" — skipped.");

                continue;
            }

            $barangay->update(['boundary' => $ring]);
        }
    }
}
