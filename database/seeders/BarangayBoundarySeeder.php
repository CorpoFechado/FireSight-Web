<?php

namespace Database\Seeders;

use App\Models\Barangay;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use RuntimeException;

class BarangayBoundarySeeder extends Seeder
{
    /**
     * Populates `barangay.boundary_geojson` from the exterior ring of each polygon
     * in public/geo/barangay_boundaries_lian.json. Matches purely
     * by `barangay_name`, so run BarangaySeeder first.
     */
    public function run(): void
    {
        $path = public_path('geo/barangay_boundaries_lian.json');

        if (! File::exists($path)) {
            throw new RuntimeException("Boundary file not found at {$path}");
        }

        $geojson = json_decode(File::get($path), associative: true, flags: JSON_THROW_ON_ERROR);

        $nameMap = [
            'Barangay 1 (Pob.)' => 'Barangay 1 (Poblacion)',
            'Barangay 2 (Pob.)' => 'Barangay 2 (Poblacion)',
            'Barangay 3 (Pob.)' => 'Barangay 3 (Poblacion)',
            'Barangay 4 (Pob.)' => 'Barangay 4 (Poblacion)',
            'Barangay 5 (Pob.)' => 'Barangay 5 (Poblacion)',
            'Puting-Kahoy' => 'Puting Kahoy',
        ];

        foreach ($geojson['features'] as $feature) {
            $name = $feature['properties']['barangay_name']
                ?? $feature['properties']['adm4_name']
                ?? $feature['properties']['name']
                ?? $feature['properties']['BGY_NAME']
                ?? null;

            // Exterior ring only (index 0) — these polygons have no holes.
            $ring = $feature['geometry']['coordinates'][0] ?? null;

            if (! $name || ! $ring) {
                continue;
            }

            $canonicalName = $nameMap[$name] ?? $name;

            $barangay = Barangay::where('barangay_name', $canonicalName)
                ->orWhere('barangay_name', $name)
                ->orWhere('barangay_name', str_replace('Poblacion ', 'Barangay ', $name).' (Poblacion)')
                ->first();

            if (! $barangay) {
                $this->command?->warn("No barangay row matches boundary name \"{$name}\" — skipped.");

                continue;
            }

            $barangay->update(['boundary_geojson' => $ring]);
        }
    }
}
