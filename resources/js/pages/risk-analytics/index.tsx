import 'leaflet/dist/leaflet.css';
import type { Feature, FeatureCollection, Geometry } from 'geojson';
import L from 'leaflet';
import { useEffect, useMemo, useState } from 'react';
import { Bar, BarChart, CartesianGrid, Cell, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';
import { GeoJSON, MapContainer, Marker, ScaleControl, TileLayer } from 'react-leaflet';
import { PortalCard } from '@/components/portal/portal-card';
import PortalLayout from '@/layouts/portal-layout';
import { BARANGAY_GEOJSON_URL, resolveDbBarangayName } from '@/lib/barangay-geo';
import { RISK_CFG, riskLevelColor } from '@/lib/fire-status';
import type { RiskLevel } from '@/lib/fire-status';
import { LIAN_CENTER, LIAN_DEFAULT_ZOOM, OSM_ATTRIBUTION, OSM_TILE_URL } from '@/lib/map-constants';

type BarangayGeoProperties = { barangay: string };
type BarangayFeature = Feature<Geometry, BarangayGeoProperties>;

type BarangayRiskPoint = {
    barangay_id: number;
    barangay_name: string;
    latitude: number;
    longitude: number;
    risk_level: RiskLevel | null;
    prediction_score: number | null;
};

type RankingRow = {
    barangay_id: number;
    barangay_name: string;
    risk_level: RiskLevel | null;
    score: number | null;
    incidentCount: number;
};

type FrequencyRow = { barangay_name: string; incidentCount: number; risk_level: RiskLevel | null };

/**
 * This page labels the top risk tier "Critical" (matching the Figma design)
 * even though `risk_level` stores it as `severe` — RISK_CFG elsewhere in the
 * app (badges, other maps) keeps the "Severe" wording, so the override is
 * scoped to this page only.
 */
const LEGEND: { level: RiskLevel; label: string; range: string }[] = [
    { level: 'severe', label: 'Critical', range: '80+' },
    { level: 'high', label: 'High', range: '60–79' },
    { level: 'moderate', label: 'Moderate', range: '40–59' },
    { level: 'low', label: 'Low', range: '<40' },
];

const stationIcon = L.divIcon({
    className: '',
    html: `<div style="width:22px;height:22px;border-radius:6px;background:#1D3557;border:2px solid white;display:flex;align-items:center;justify-content:center;color:white;font-size:10px;font-weight:700;box-shadow:0 1px 4px rgba(0,0,0,0.35)">S</div>`,
    iconSize: [22, 22],
    iconAnchor: [11, 11],
});

export default function RiskAnalytics({
    barangayRisk,
    ranking,
    incidentFrequency,
}: {
    barangayRisk: BarangayRiskPoint[];
    ranking: RankingRow[];
    incidentFrequency: FrequencyRow[];
}) {
    const [barangayGeo, setBarangayGeo] = useState<FeatureCollection<Geometry, BarangayGeoProperties> | null>(null);
    const [sortBy, setSortBy] = useState<'risk' | 'count'>('risk');

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
        for (const b of barangayRisk) {
            map.set(b.barangay_name, b);
        }
        return map;
    }, [barangayRisk]);

    const sortedRanking = useMemo(() => {
        const rows = [...ranking];
        rows.sort((a, b) => (sortBy === 'risk' ? (b.score ?? -1) - (a.score ?? -1) : b.incidentCount - a.incidentCount));
        return rows;
    }, [ranking, sortBy]);

    const maxCount = Math.max(1, ...ranking.map((r) => r.incidentCount));

    return (
        <PortalLayout title="Fire-Prone Areas — Risk Analytics" subtitle="Barangay-level risk assessment based on recorded incident history">
            <div className="space-y-4">
                <div className="grid gap-4 lg:grid-cols-[1fr_360px]">
                    {/* Heatmap */}
                    <PortalCard className="flex flex-col overflow-hidden">
                        <div
                            className="flex flex-wrap items-center gap-3 border-b px-4 py-3"
                            style={{ borderColor: 'rgba(43,45,66,0.08)' }}
                        >
                            <h2 className="text-sm font-bold text-brand-navy">Risk Heatmap — Lian, Batangas</h2>
                            <div className="flex flex-wrap items-center gap-3 lg:ml-auto">
                                {LEGEND.map((item) => (
                                    <div key={item.level} className="flex items-center gap-1.5">
                                        <span
                                            className="size-2.5 rounded-full"
                                            style={{ background: RISK_CFG[item.level].color }}
                                        />
                                        <span className="text-xs text-brand-muted">
                                            {item.label} ({item.range})
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>
                        <div className="relative" style={{ height: 520 }}>
                            <MapContainer
                                center={LIAN_CENTER}
                                zoom={LIAN_DEFAULT_ZOOM}
                                style={{ height: '100%', width: '100%' }}
                            >
                                <TileLayer url={OSM_TILE_URL} attribution={OSM_ATTRIBUTION} />
                                <ScaleControl position="bottomleft" imperial={false} />

                                {barangayGeo && (
                                    <GeoJSON
                                        key="risk-analytics-layer"
                                        data={barangayGeo}
                                        style={(feature) => {
                                            const risk = feature
                                                ? riskByBarangayName.get(resolveDbBarangayName(feature.properties.barangay))
                                                : undefined;
                                            const color = risk?.risk_level ? riskLevelColor(risk.risk_level) : '#6B7A8D';

                                            return { color, weight: 1, fillColor: color, fillOpacity: 0.55 };
                                        }}
                                        onEachFeature={(feature: BarangayFeature, layer) => {
                                            const risk = riskByBarangayName.get(resolveDbBarangayName(feature.properties.barangay));
                                            const score =
                                                risk?.prediction_score !== null && risk?.prediction_score !== undefined
                                                    ? Math.round(risk.prediction_score * 100)
                                                    : null;

                                            layer.bindTooltip(
                                                `<div style="text-align:center"><div style="font-weight:700">${feature.properties.barangay}</div>${
                                                    score !== null ? `<div>${score}</div>` : ''
                                                }</div>`,
                                                { permanent: true, direction: 'center', className: 'barangay-risk-label' },
                                            );
                                        }}
                                    />
                                )}

                                <Marker position={LIAN_CENTER} icon={stationIcon} />
                            </MapContainer>
                        </div>
                    </PortalCard>

                    {/* Ranking */}
                    <PortalCard className="flex flex-col overflow-hidden">
                        <div
                            className="flex items-center justify-between border-b px-4 py-3"
                            style={{ borderColor: 'rgba(43,45,66,0.08)' }}
                        >
                            <h2 className="text-sm font-bold text-brand-navy">Barangay Risk Ranking</h2>
                            <div className="flex rounded-lg border p-0.5" style={{ borderColor: 'rgba(43,45,66,0.13)' }}>
                                {(['risk', 'count'] as const).map((key) => (
                                    <button
                                        key={key}
                                        onClick={() => setSortBy(key)}
                                        className="rounded-md px-2.5 py-1 text-xs font-semibold capitalize transition-colors"
                                        style={{
                                            background: sortBy === key ? '#1D3557' : 'transparent',
                                            color: sortBy === key ? 'white' : '#6B7A8D',
                                        }}
                                    >
                                        {key}
                                    </button>
                                ))}
                            </div>
                        </div>
                        <div className="flex-1 overflow-y-auto" style={{ maxHeight: 520 }}>
                            {sortedRanking.map((row, i) => (
                                <div
                                    key={row.barangay_id}
                                    className="flex items-center gap-3 border-b px-4 py-3"
                                    style={{ borderColor: 'rgba(43,45,66,0.06)' }}
                                >
                                    <span className="w-4 text-xs font-semibold text-brand-muted">{i + 1}</span>
                                    <div className="min-w-0 flex-1">
                                        <p className="truncate text-xs font-semibold text-brand-navy">{row.barangay_name}</p>
                                        <div className="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
                                            <div
                                                className="h-full rounded-full"
                                                style={{
                                                    width: `${
                                                        sortBy === 'risk'
                                                            ? (row.score ?? 0)
                                                            : Math.round((row.incidentCount / maxCount) * 100)
                                                    }%`,
                                                    background: row.risk_level ? riskLevelColor(row.risk_level) : '#6B7A8D',
                                                }}
                                            />
                                        </div>
                                    </div>
                                    <div className="flex flex-shrink-0 flex-col items-end">
                                        <span className="text-sm font-bold text-brand-navy">{row.score ?? '—'}</span>
                                        <span className="text-[11px] text-brand-muted">{row.incidentCount} inc.</span>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </PortalCard>
                </div>

                {/* Frequency chart */}
                <PortalCard>
                    <div className="border-b px-5 py-4" style={{ borderColor: 'rgba(43,45,66,0.08)' }}>
                        <h2 className="text-sm font-bold text-brand-navy">Incident Frequency by Barangay</h2>
                    </div>
                    <div className="p-5">
                        <ResponsiveContainer width="100%" height={220}>
                            <BarChart data={incidentFrequency} margin={{ top: 4, right: 4, bottom: 0, left: 0 }}>
                                <CartesianGrid strokeDasharray="3 3" stroke="rgba(43,45,66,0.06)" vertical={false} />
                                <XAxis
                                    dataKey="barangay_name"
                                    tick={{ fontSize: 11, fill: '#6B7A8D' }}
                                    axisLine={false}
                                    tickLine={false}
                                    interval={0}
                                    angle={-20}
                                    textAnchor="end"
                                    height={50}
                                />
                                <YAxis tick={{ fontSize: 11, fill: '#6B7A8D' }} axisLine={false} tickLine={false} width={28} />
                                <Tooltip contentStyle={{ fontSize: 12, borderRadius: 8, border: '1px solid rgba(43,45,66,0.1)' }} />
                                <Bar dataKey="incidentCount" name="Incidents" radius={[3, 3, 0, 0]}>
                                    {incidentFrequency.map((row) => (
                                        <Cell
                                            key={row.barangay_name}
                                            fill={row.risk_level ? riskLevelColor(row.risk_level) : '#6B7A8D'}
                                        />
                                    ))}
                                </Bar>
                            </BarChart>
                        </ResponsiveContainer>
                    </div>
                </PortalCard>
            </div>
        </PortalLayout>
    );
}
