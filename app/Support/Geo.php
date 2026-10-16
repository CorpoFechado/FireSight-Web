<?php

namespace App\Support;

class Geo
{
    /**
     * Ray-casting point-in-polygon test.
     *
     * @param  float  $lat  Point latitude
     * @param  float  $lng  Point longitude
     * @param  array<int, array{0: float, 1: float}>  $polygon  Ring of
     *                                                          [longitude, latitude] pairs (GeoJSON order), as stored in
     *                                                          `barangay.boundary`.
     */
    public static function pointInPolygon(float $lat, float $lng, array $polygon): bool
    {
        $inside = false;
        $count = count($polygon);

        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            $xi = (float) $polygon[$i][0];
            $yi = (float) $polygon[$i][1];
            $xj = (float) $polygon[$j][0];
            $yj = (float) $polygon[$j][1];

            $intersects = (($yi > $lat) !== ($yj > $lat))
                && ($lng < ($xj - $xi) * ($lat - $yi) / ($yj - $yi) + $xi);

            if ($intersects) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }

    /**
     * Great-circle (haversine) distance between two coordinates, in meters.
     */
    public static function distanceInMeters(float $fromLat, float $fromLng, float $toLat, float $toLng): float
    {
        $earthRadiusMeters = 6371000;

        $latDelta = deg2rad($toLat - $fromLat);
        $lngDelta = deg2rad($toLng - $fromLng);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($fromLat)) * cos(deg2rad($toLat)) * sin($lngDelta / 2) ** 2;

        return $earthRadiusMeters * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
