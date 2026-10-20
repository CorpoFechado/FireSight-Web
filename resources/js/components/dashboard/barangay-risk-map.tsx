import 'leaflet/dist/leaflet.css';
import type { Feature, FeatureCollection, Geometry } from 'geojson';
import { useEffect, useMemo, useState } from 'react';
import { GeoJSON, MapContainer, TileLayer } from 'react-leaflet';
import {
    BARANGAY_GEOJSON_URL,
    getFeatureBarangayName,
    resolveDbBarangayName,
} from '@/lib/barangay-geo';
import { RISK_CFG, riskMapPolygonStyle } from '@/lib/fire-status';
import type { RiskLevel } from '@/lib/fire-status';
import {
    LIAN_CENTER,
    LIAN_DEFAULT_ZOOM,
    OSM_ATTRIBUTION,
    OSM_TILE_URL,
} from '@/lib/map-constants';

type BarangayGeoProperties = {
    adm4_name?: string;
    barangay?: string;
    name?: string;
    BGY_NAME?: string;
    [key: string]: unknown;
};
type BarangayFeature = Feature<Geometry, BarangayGeoProperties>;

export type BarangayRiskPoint = {
    barangay_id: number;
    barangay_name: string;
    centroid_lat: number;
    centroid_lng: number;
    risk_level: RiskLevel | null;
    prediction_score: number | null;
};

export function BarangayRiskMap({
    barangays,
    height = 260,
}: {
    barangays: BarangayRiskPoint[];
    height?: number;
}) {
    const [barangayGeo, setBarangayGeo] = useState<FeatureCollection<
        Geometry,
        BarangayGeoProperties
    > | null>(null);

    useEffect(() => {
        let cancelled = false;

        fetch(BARANGAY_GEOJSON_URL)
            .then((res) => res.json())
            .then(
                (data: FeatureCollection<Geometry, BarangayGeoProperties>) => {
                    if (!cancelled) {
                        setBarangayGeo(data);
                    }
                },
            )
            .catch(() => {
                // Boundary layer is decorative — silently fall back to no overlay.
            });

        return () => {
            cancelled = true;
        };
    }, []);

    const riskByBarangayName = useMemo(() => {
        const map = new Map<string, BarangayRiskPoint>();

        for (const b of barangays) {
            map.set(b.barangay_name, b);
        }

        return map;
    }, [barangays]);

    return (
        <div className="overflow-hidden rounded-lg" style={{ height }}>
            <MapContainer
                center={LIAN_CENTER}
                zoom={LIAN_DEFAULT_ZOOM}
                scrollWheelZoom={false}
                style={{ height: '100%', width: '100%' }}
            >
                <TileLayer url={OSM_TILE_URL} attribution={OSM_ATTRIBUTION} />
                {barangayGeo && (
                    <GeoJSON
                        key="barangay-risk-layer"
                        data={barangayGeo}
                        style={(feature) => {
                            const rawName = feature
                                ? getFeatureBarangayName(feature.properties)
                                : '';
                            const dbName = resolveDbBarangayName(rawName);
                            const risk = dbName
                                ? riskByBarangayName.get(dbName)
                                : undefined;

                            return riskMapPolygonStyle(risk?.risk_level);
                        }}
                        onEachFeature={(feature: BarangayFeature, layer) => {
                            const rawName = getFeatureBarangayName(
                                feature.properties,
                            );
                            const dbName = resolveDbBarangayName(rawName);
                            const risk = riskByBarangayName.get(dbName);
                            const label = risk?.risk_level
                                ? RISK_CFG[risk.risk_level].label
                                : 'No data';
                            const score =
                                risk?.prediction_score !== null &&
                                risk?.prediction_score !== undefined
                                    ? ` · ${Math.round(risk.prediction_score * 100)}% score`
                                    : '';
                            const displayName =
                                dbName || rawName || 'Unknown Barangay';

                            layer.bindPopup(
                                `<p class="text-sm font-semibold">${displayName}</p>` +
                                    `<p class="text-xs text-gray-500">${label}${score}</p>`,
                            );
                        }}
                    />
                )}
            </MapContainer>
        </div>
    );
}
