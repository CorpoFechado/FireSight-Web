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
     * Returns null if the point is outside all polygons (e.g. testing in Nasugbu).
     */
    public function findBarangayByLocation(float $lat, float $lng): ?int
    {
        $geojsonPath = public_path('geo/barangay_boundaries_lian.json');
        if (! File::exists($geojsonPath)) {
            return null;
        }

        $json = File::get($geojsonPath);
        $data = json_decode($json, true);
        if (! isset($data['features']) || ! is_array($data['features'])) {
            return null;
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
                    $matchedBarangayName = $feature['properties']['BGY_NAME']
                        ?? $feature['properties']['name']
                        ?? $feature['properties']['adm4_name']
                        ?? null;
                    break;
                }
            } elseif ($geometry['type'] === 'MultiPolygon') {
                foreach ($geometry['coordinates'] as $poly) {
                    $polygon = $poly[0] ?? [];
                    if (Geo::pointInPolygon($lat, $lng, $polygon)) {
                        $matchedBarangayName = $feature['properties']['BGY_NAME']
                            ?? $feature['properties']['name']
                            ?? $feature['properties']['adm4_name']
                            ?? null;
                        break 2;
                    }
                }
            }
        }

        if ($matchedBarangayName) {
            $cleanedTarget = strtolower(str_replace('-', ' ', trim($matchedBarangayName)));

            // Find matching barangay in DB
            $barangays = Barangay::all();
            foreach ($barangays as $b) {
                $cleanedName = strtolower(str_replace('-', ' ', trim($b->barangay_name)));
                if ($cleanedName === $cleanedTarget) {
                    return (int) $b->barangay_id;
                }
            }
        }

        return null;
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
