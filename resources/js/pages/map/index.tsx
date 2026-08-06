import 'leaflet/dist/leaflet.css';
import { Link } from '@inertiajs/react';
import L from 'leaflet';
import type { Feature, FeatureCollection, Geometry } from 'geojson';
import { ChevronLeft, ChevronRight, Layers } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import { CircleMarker, GeoJSON, MapContainer, Marker, Popup, TileLayer } from 'react-leaflet';
import { PortalCard } from '@/components/portal/portal-card';
import { StatusBadge } from '@/components/portal/status-badge';
import PortalLayout from '@/layouts/portal-layout';
import { RISK_CFG, STATUS_CFG, riskLevelColor } from '@/lib/fire-status';
import type { ReportStatus, RiskLevel, SeverityLevel } from '@/lib/fire-status';
import { BARANGAY_GEOJSON_URL, resolveDbBarangayName } from '@/lib/barangay-geo';
import { LIAN_CENTER, LIAN_DEFAULT_ZOOM, OSM_ATTRIBUTION, OSM_TILE_URL } from '@/lib/map-constants';
import { show as showIncident } from '@/routes/incidents';

type BarangayGeoProperties = { barangay: string };
type BarangayFeature = Feature<Geometry, BarangayGeoProperties>;

type MapIncident = {
    report_id: number;
    reference: string;
    type: string | null;
    severity: SeverityLevel | null;
    status: ReportStatus;
    barangay: string | null;
    latitude: number;
    longitude: number;
};

type BarangayRiskPoint = {
    barangay_id: number;
    barangay_name: string;
    latitude: number;
    longitude: number;
    risk_level: RiskLevel | null;
    prediction_score: number | null;
};

const stationIcon = L.divIcon({
    className: '',
    html: `<div style="width:26px;height:26px;border-radius:6px;background:#1D3557;border:2px solid white;display:flex;align-items:center;justify-content:center;color:white;font-size:10px;font-weight:700;box-shadow:0 1px 4px rgba(0,0,0,0.35)">BFP</div>`,
    iconSize: [26, 26],
    iconAnchor: [13, 13],
});

const ACTIVE_STATUSES: ReportStatus[] = ['pending', 'verified', 'dispatched'];

export default function FireIncidentsMap({
    incidents,
    barangayRisk,
}: {
    incidents: MapIncident[];
    barangayRisk: BarangayRiskPoint[];
}) {
    const [sidebarOpen, setSidebarOpen] = useState(true);
    const [layers, setLayers] = useState({ pins: true, risk: true, station: true });
    const [selectedId, setSelectedId] = useState<number | null>(null);
    const [barangayGeo, setBarangayGeo] = useState<FeatureCollection<Geometry, BarangayGeoProperties> | null>(null);
    const mapRef = useRef<L.Map | null>(null);

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

    const counts = useMemo(() => {
        const byStatus: Record<string, number> = {};
        for (const inc of incidents) {
            byStatus[inc.status] = (byStatus[inc.status] ?? 0) + 1;
        }
        return byStatus;
    }, [incidents]);

    const focusIncident = (inc: MapIncident) => {
        setSelectedId(inc.report_id);
        mapRef.current?.flyTo([inc.latitude, inc.longitude], 15, { duration: 0.6 });
    };

    return (
        <PortalLayout title="Fire Incidents Map" subtitle="Lian, Batangas — GIS View">
            <div className="flex gap-4" style={{ height: 'calc(100vh - 64px - 48px)' }}>
                {/* Incident sidebar */}
                {sidebarOpen && (
                    <PortalCard className="flex w-64 flex-shrink-0 flex-col overflow-hidden">
                        <div
                            className="flex items-center justify-between border-b px-4 py-3"
                            style={{ borderColor: 'rgba(43,45,66,0.08)' }}
                        >
                            <h3 className="text-sm font-bold text-brand-navy">Active Incidents</h3>
                            <span className="rounded-full bg-orange-100 px-2 py-0.5 text-xs font-semibold text-brand-orange">
                                {incidents.length}
                            </span>
                        </div>
                        <div
                            className="flex items-center gap-3 border-b px-4 py-2"
                            style={{ borderColor: 'rgba(43,45,66,0.06)' }}
                        >
                            {ACTIVE_STATUSES.map((s) => (
                                <div key={s} className="flex items-center gap-1">
                                    <span className="size-1.5 rounded-full" style={{ background: STATUS_CFG[s].text }} />
                                    <span className="text-xs text-brand-muted">
                                        {counts[s] ?? 0} {STATUS_CFG[s].label}
                                    </span>
                                </div>
                            ))}
                        </div>
                        <div className="flex-1 overflow-y-auto">
                            {incidents.length === 0 && (
                                <p className="p-4 text-center text-xs text-brand-muted">No active incidents right now.</p>
                            )}
                            {incidents.map((inc) => (
                                <div
                                    key={inc.report_id}
                                    onClick={() => focusIncident(inc)}
                                    className="cursor-pointer border-b px-4 py-3 transition-colors hover:bg-blue-50"
                                    style={{
                                        borderColor: 'rgba(43,45,66,0.06)',
                                        background: selectedId === inc.report_id ? '#EBF3FB' : undefined,
                                    }}
                                >
                                    <div className="flex items-start gap-2">
                                        <div
                                            className="mt-1 size-2 flex-shrink-0 rounded-full"
                                            style={{ background: STATUS_CFG[inc.status].text }}
                                        />
                                        <div className="min-w-0 flex-1">
                                            <p className="font-mono text-xs font-medium text-brand-blue">{inc.reference}</p>
                                            <p className="truncate text-xs font-semibold text-brand-navy">
                                                {inc.type ?? 'Unclassified'}
                                            </p>
                                            <p className="text-xs text-brand-muted">{inc.barangay ?? 'Barangay pending'}</p>
                                        </div>
                                        <StatusBadge status={inc.status} />
                                    </div>
                                </div>
                            ))}
                        </div>
                    </PortalCard>
                )}

                {/* Map */}
                <PortalCard className="flex flex-1 flex-col overflow-hidden">
                    {/* Toolbar */}
                    <div
                        className="flex items-center gap-3 border-b px-4 py-3"
                        style={{ borderColor: 'rgba(43,45,66,0.08)' }}
                    >
                        <button
                            onClick={() => setSidebarOpen(!sidebarOpen)}
                            className="rounded p-1.5 text-brand-muted transition-colors hover:bg-gray-100"
                        >
                            {sidebarOpen ? <ChevronLeft size={16} /> : <ChevronRight size={16} />}
                        </button>
                        <span className="text-sm font-semibold text-brand-navy">Lian, Batangas — GIS View</span>
                        <div className="ml-auto flex items-center gap-2">
                            {(
                                [
                                    ['pins', 'Incidents'],
                                    ['risk', 'Risk Map'],
                                    ['station', 'Stations'],
                                ] as const
                            ).map(([key, label]) => (
                                <button
                                    key={key}
                                    onClick={() => setLayers((l) => ({ ...l, [key]: !l[key] }))}
                                    className="flex items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-xs font-semibold transition-all"
                                    style={{
                                        background: layers[key] ? '#1D3557' : 'white',
                                        color: layers[key] ? 'white' : '#6B7A8D',
                                        borderColor: layers[key] ? '#1D3557' : 'rgba(43,45,66,0.13)',
                                    }}
                                >
                                    <Layers size={11} />
                                    {label}
                                </button>
                            ))}
                        </div>
                    </div>

                    {/* Map canvas */}
                    <div className="relative flex-1">
                        <MapContainer
                            ref={mapRef}
                            center={LIAN_CENTER}
                            zoom={LIAN_DEFAULT_ZOOM}
                            style={{ height: '100%', width: '100%' }}
                        >
                            <TileLayer url={OSM_TILE_URL} attribution={OSM_ATTRIBUTION} />

                            {layers.risk && barangayGeo && (
                                <GeoJSON
                                    key="barangay-risk-layer"
                                    data={barangayGeo}
                                    style={(feature) => {
                                        const risk = feature
                                            ? riskByBarangayName.get(resolveDbBarangayName(feature.properties.barangay))
                                            : undefined;
                                        const color = risk?.risk_level ? riskLevelColor(risk.risk_level) : '#6B7A8D';

                                        return { color, weight: 1, fillColor: color, fillOpacity: 0.28 };
                                    }}
                                    onEachFeature={(feature: BarangayFeature, layer) => {
                                        const risk = riskByBarangayName.get(resolveDbBarangayName(feature.properties.barangay));
                                        const label = risk?.risk_level ? RISK_CFG[risk.risk_level].label : 'No risk data';

                                        layer.bindPopup(
                                            `<p class="text-sm font-semibold">${feature.properties.barangay}</p>` +
                                                `<p class="text-xs text-gray-500">${label} risk</p>`,
                                        );
                                    }}
                                />
                            )}

                            {layers.station && (
                                <Marker position={LIAN_CENTER} icon={stationIcon}>
                                    <Popup>
                                        <p className="text-sm font-semibold">BFP Lian Fire Station</p>
                                    </Popup>
                                </Marker>
                            )}

                            {layers.pins &&
                                incidents.map((inc) => (
                                    <CircleMarker
                                        key={inc.report_id}
                                        center={[inc.latitude, inc.longitude]}
                                        radius={selectedId === inc.report_id ? 11 : 8}
                                        pathOptions={{
                                            color: '#fff',
                                            weight: selectedId === inc.report_id ? 3 : 1.5,
                                            fillColor: STATUS_CFG[inc.status].text,
                                            fillOpacity: 0.9,
                                        }}
                                        eventHandlers={{ click: () => setSelectedId(inc.report_id) }}
                                    >
                                        <Popup>
                                            <div className="space-y-1">
                                                <p className="font-mono text-xs font-semibold text-blue-700">{inc.reference}</p>
                                                <p className="text-sm font-semibold">{inc.type ?? 'Unclassified'}</p>
                                                <p className="text-xs text-gray-500">{inc.barangay ?? 'Barangay pending'}</p>
                                                <Link
                                                    href={showIncident(inc.report_id)}
                                                    className="text-xs font-semibold text-blue-600 underline"
                                                >
                                                    View full report →
                                                </Link>
                                            </div>
                                        </Popup>
                                    </CircleMarker>
                                ))}
                        </MapContainer>

                        {/* Legend */}
                        <div className="absolute bottom-3 left-3 z-[1000] space-y-2 rounded-lg bg-white/95 px-3 py-2 shadow">
                            <div className="flex items-center gap-3">
                                {ACTIVE_STATUSES.map((s) => (
                                    <div key={s} className="flex items-center gap-1">
                                        <span
                                            className="size-2 rounded-full"
                                            style={{ background: STATUS_CFG[s].text }}
                                        />
                                        <span className="text-xs text-brand-muted">{STATUS_CFG[s].label}</span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </PortalCard>
            </div>
        </PortalLayout>
    );
}
