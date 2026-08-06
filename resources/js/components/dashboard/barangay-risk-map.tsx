import 'leaflet/dist/leaflet.css';
import type { Feature, FeatureCollection, Geometry } from 'geojson';
import { useEffect, useMemo, useState } from 'react';
import { GeoJSON, MapContainer, TileLayer } from 'react-leaflet';
import { RISK_CFG, riskLevelColor } from '@/lib/fire-status';
import type { RiskLevel } from '@/lib/fire-status';
import { BARANGAY_GEOJSON_URL, resolveDbBarangayName } from '@/lib/barangay-geo';
import { LIAN_CENTER, LIAN_DEFAULT_ZOOM, OSM_ATTRIBUTION, OSM_TILE_URL } from '@/lib/map-constants';

type BarangayGeoProperties = { barangay: string };
type BarangayFeature = Feature<Geometry, BarangayGeoProperties>;

export type BarangayRiskPoint = {
    barangay_id: number;
    barangay_name: string;
    latitude: number;
    longitude: number;
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
    const [barangayGeo, setBarangayGeo] = useState<FeatureCollection<Geometry, BarangayGeoProperties> | null>(null);

    useEffect(() => {
        let cancelled = false;

        fetch(BARANGAY_GEOJSON_URL)
            .then((res) => res.json())
            .then((data: FeatureCollection<Geometry, BarangayGeoProperties>) => {
                if (!cancelled) {
                    setBarangayGeo(data);
                }
            })
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
                            const risk = feature
                                ? riskByBarangayName.get(resolveDbBarangayName(feature.properties.barangay))
                                : undefined;
                            const color = risk?.risk_level ? riskLevelColor(risk.risk_level) : '#6B7A8D';

                            return { color, weight: 1, fillColor: color, fillOpacity: 0.6 };
                        }}
                        onEachFeature={(feature: BarangayFeature, layer) => {
                            const risk = riskByBarangayName.get(resolveDbBarangayName(feature.properties.barangay));
                            const label = risk?.risk_level ? RISK_CFG[risk.risk_level].label : 'No data';
                            const score =
                                risk?.prediction_score !== null && risk?.prediction_score !== undefined
                                    ? ` · ${Math.round(risk.prediction_score * 100)}% score`
                                    : '';

                            layer.bindPopup(
                                `<p class="text-sm font-semibold">${feature.properties.barangay}</p>` +
                                    `<p class="text-xs text-gray-500">${label}${score}</p>`,
                            );
                        }}
                    />
                )}
            </MapContainer>
        </div>
    );
}
