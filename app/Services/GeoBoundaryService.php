<?php

namespace App\Services;

use App\Models\Barangay;
use App\Support\Geo;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoBoundaryService
{
    /**
     * Given a lat/lng, reads the Lian barangay boundaries GeoJSON and returns the matched barangay_id.
     * If the point lies on an immediate coastal edge or border road within 800m, falls back to the nearest centroid.
     * Returns null if the point is outside Lian borders (e.g. Nasugbu, Calatagan, or outside town).
     */
    public function findBarangayByLocation(float $lat, float $lng): ?int
    {
        $geojsonPath = public_path('geo/barangay_boundaries_lian.json');
        if (! File::exists($geojsonPath)) {
            return $this->findNearestBarangay($lat, $lng, 800);
        }

        $json = File::get($geojsonPath);
        $data = json_decode($json, true);
        if (! isset($data['features']) || ! is_array($data['features'])) {
            return $this->findNearestBarangay($lat, $lng, 800);
        }

        $matchedBarangayName = null;

        foreach ($data['features'] as $feature) {
            $geometry = $feature['geometry'] ?? null;
            if (! $geometry || ! isset($geometry['type'], $geometry['coordinates'])) {
                continue;
            }

            if ($geometry['type'] === 'Polygon') {
                $polygon = $geometry['coordinates'][0] ?? [];
                if (Geo::pointInPolygon($lat, $lng, $polygon)) {
                    $matchedBarangayName = $feature['properties']['barangay_name']
                        ?? $feature['properties']['BGY_NAME']
                        ?? $feature['properties']['name']
                        ?? $feature['properties']['adm4_name']
                        ?? null;
                    break;
                }
            } elseif ($geometry['type'] === 'MultiPolygon') {
                foreach ($geometry['coordinates'] as $poly) {
                    $polygon = $poly[0] ?? [];
                    if (Geo::pointInPolygon($lat, $lng, $polygon)) {
                        $matchedBarangayName = $feature['properties']['barangay_name']
                            ?? $feature['properties']['BGY_NAME']
                            ?? $feature['properties']['name']
                            ?? $feature['properties']['adm4_name']
                            ?? null;
                        break 2;
                    }
                }
            }
        }

        if ($matchedBarangayName) {
            $normalizedTarget = $this->normalizeName($matchedBarangayName);

            // Find matching barangay in DB
            $barangays = Barangay::all();
            foreach ($barangays as $b) {
                $normalizedName = $this->normalizeName($b->barangay_name);
                if ($normalizedName === $normalizedTarget) {
                    return (int) $b->barangay_id;
                }
            }
        }

        // Only allow fallback if within 800m of a Lian barangay (e.g. shoreline / beach drift).
        // If further away (in Nasugbu, Calatagan, Tuy, etc.), return null (outside jurisdiction).
        return $this->findNearestBarangay($lat, $lng, 800);
    }

    /**
     * Fallback if the point is slightly outside polygons (e.g. coastal edge or border road).
     * Calculates the nearest barangay by centroid distance if within $maxMeters.
     */
    public function findNearestBarangay(float $lat, float $lng, float $maxMeters = 800): ?int
    {
        $barangays = Barangay::whereNotNull('centroid_lat')
            ->whereNotNull('centroid_lng')
            ->get();

        if ($barangays->isEmpty()) {
            return null;
        }

        $bestId = null;
        $bestDistance = INF;

        foreach ($barangays as $b) {
            $dist = Geo::distanceInMeters($lat, $lng, (float) $b->centroid_lat, (float) $b->centroid_lng);
            if ($dist < $bestDistance) {
                $bestDistance = $dist;
                $bestId = (int) $b->barangay_id;
            }
        }

        // If the coordinate is beyond the Lian boundary buffer, it is outside Lian jurisdiction
        if ($bestDistance > $maxMeters) {
            return null;
        }

        return $bestId;
    }

    /**
     * Normalizes barangay name variants between GeoJSON and the database.
     * e.g. "Barangay 1 (Pob.)" -> "barangay 1 poblacion"
     */
    private function normalizeName(string $name): string
    {
        $normalized = strtolower(trim($name));
        $normalized = str_replace(['-', '.', ',', '(', ')'], ' ', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;
        $normalized = trim($normalized);

        $aliases = [
            'barangay 1 pob' => 'barangay 1 poblacion',
            'barangay 2 pob' => 'barangay 2 poblacion',
            'barangay 3 pob' => 'barangay 3 poblacion',
            'barangay 4 pob' => 'barangay 4 poblacion',
            'barangay 5 pob' => 'barangay 5 poblacion',
            'brgy 1 pob' => 'barangay 1 poblacion',
            'brgy 2 pob' => 'barangay 2 poblacion',
            'brgy 3 pob' => 'barangay 3 poblacion',
            'brgy 4 pob' => 'barangay 4 poblacion',
            'brgy 5 pob' => 'barangay 5 poblacion',
            'brgy 1' => 'barangay 1 poblacion',
            'brgy 2' => 'barangay 2 poblacion',
            'brgy 3' => 'barangay 3 poblacion',
            'brgy 4' => 'barangay 4 poblacion',
            'brgy 5' => 'barangay 5 poblacion',
            'pob 1' => 'barangay 1 poblacion',
            'pob 2' => 'barangay 2 poblacion',
            'pob 3' => 'barangay 3 poblacion',
            'pob 4' => 'barangay 4 poblacion',
            'pob 5' => 'barangay 5 poblacion',
            'poblacion 1' => 'barangay 1 poblacion',
            'poblacion 2' => 'barangay 2 poblacion',
            'poblacion 3' => 'barangay 3 poblacion',
            'poblacion 4' => 'barangay 4 poblacion',
            'poblacion 5' => 'barangay 5 poblacion',
            'puting kahoy' => 'puting kahoy',
        ];

        return $aliases[$normalized] ?? $normalized;
    }

    /**
     * Returns a short human-readable location for notification titles.
     */
    public function findReadableLocation(float $lat, float $lng, ?string $barangayName = null): string
    {
        try {
            $response = Http::withHeaders(['User-Agent' => 'FireSight/1.0'])
                ->timeout(3)
                ->connectTimeout(2)
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'format' => 'jsonv2',
                    'lat' => $lat,
                    'lon' => $lng,
                ]);

            if ($response->successful()) {
                $address = $response->json('address', []);
                $parts = [];
                foreach (['road', 'village', 'neighbourhood', 'suburb', 'town', 'city'] as $key) {
                    $value = trim((string) ($address[$key] ?? ''));
                    if ($value !== '' && ! in_array($value, $parts, true)) {
                        $parts[] = $value;
                    }
                    if (count($parts) === 3) {
                        break;
                    }
                }

                if (! empty($parts)) {
                    return implode(', ', $parts);
                }
            }
        } catch (\Throwable $e) {
            Log::debug('Nominatim reverse geocoding skipped: '.$e->getMessage());
        }

        return trim((string) $barangayName) !== '' ? trim((string) $barangayName) : 'submitted location';
    }
}
