import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import { AlertTriangle, CheckCircle2, Truck } from 'lucide-react';
import { useEffect, useState } from 'react';
import { Marker, MapContainer, Polyline, ScaleControl, TileLayer } from 'react-leaflet';
import { PortalCard } from '@/components/portal/portal-card';
import PortalLayout from '@/layouts/portal-layout';
import { LIAN_CENTER, OSM_ATTRIBUTION, OSM_TILE_URL } from '@/lib/map-constants';

type Incident = {
    reference: string;
    type: string;
    barangay: string;
    severity: string;
    reporter: string;
    latitude: number;
    longitude: number;
};

type RouteSummary = {
    distanceKm: number;
    etaMinutes: number;
    routeType: string;
    algorithm: string;
};

type Station = { name: string };

const stationIcon = L.divIcon({
    className: '',
    html: `<div style="width:24px;height:24px;border-radius:6px;background:#1D3557;border:2px solid white;display:flex;align-items:center;justify-content:center;color:white;font-size:11px;font-weight:700;box-shadow:0 1px 4px rgba(0,0,0,0.35)">S</div>`,
    iconSize: [24, 24],
    iconAnchor: [12, 12],
});

function incidentIcon(dispatched: boolean) {
    return L.divIcon({
        className: '',
        html: `<div style="width:26px;height:26px;border-radius:50%;background:${dispatched ? '#1D3557' : '#E63946'};border:3px solid white;box-shadow:0 1px 6px rgba(0,0,0,0.4)"></div>`,
        iconSize: [26, 26],
        iconAnchor: [13, 13],
    });
}

/**
 * This page is still a static stand-in for the real dispatch feature — there's
 * no in-house routing algorithm yet (see the `response` route comment in
 * routes/web.php). Station and incident positions are real (BFP Lian's actual
 * coordinates and community_report #44's location). The route line itself
 * now comes from OSRM's free public routing API (a real road-following path,
 * not a straight line), with distance/duration read from that response —
 * if the request fails (offline, rate-limited), it falls back to the static
 * two-point line and the demo distance/time passed in from the route. Turn-by-
 * turn directions remain fixed demo text either way. "Dispatch Unit" only
 * toggles local UI state.
 */
export default function ResponseTracking({
    incident,
    routeSummary,
    directions,
    station,
}: {
    incident: Incident;
    routeSummary: RouteSummary;
    directions: string[];
    station: Station;
}) {
    const [dispatched, setDispatched] = useState(false);
    const [dispatchedAt, setDispatchedAt] = useState<string | null>(null);
    const [roadRoute, setRoadRoute] = useState<{ path: [number, number][]; distanceKm: number; etaMinutes: number } | null>(null);
    const [routeFailed, setRouteFailed] = useState(false);

    const incidentPosition: [number, number] = [incident.latitude, incident.longitude];
    const midpoint: [number, number] = [
        (LIAN_CENTER[0] + incidentPosition[0]) / 2 + 0.002,
        (LIAN_CENTER[1] + incidentPosition[1]) / 2 - 0.0015,
    ];

    useEffect(() => {
        let cancelled = false;
        const coords = `${LIAN_CENTER[1]},${LIAN_CENTER[0]};${incidentPosition[1]},${incidentPosition[0]}`;

        fetch(`https://router.project-osrm.org/route/v1/driving/${coords}?overview=full&geometries=geojson`)
            .then((res) => res.json())
            .then((data: { code: string; routes?: { geometry: { coordinates: [number, number][] }; distance: number; duration: number }[] }) => {
                if (cancelled || data.code !== 'Ok' || !data.routes?.length) {
                    setRouteFailed(true);
                    return;
                }

                const route = data.routes[0];
                setRoadRoute({
                    path: route.geometry.coordinates.map(([lng, lat]) => [lat, lng]),
                    distanceKm: Math.round((route.distance / 1000) * 10) / 10,
                    etaMinutes: Math.max(1, Math.round(route.duration / 60)),
                });
            })
            .catch(() => {
                if (!cancelled) {
                    setRouteFailed(true);
                }
            });

        return () => {
            cancelled = true;
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [incident.latitude, incident.longitude]);

    const displayDistanceKm = roadRoute?.distanceKm ?? routeSummary.distanceKm;
    const displayEtaMinutes = roadRoute?.etaMinutes ?? routeSummary.etaMinutes;

    const handleDispatch = () => {
        setDispatched(true);
        setDispatchedAt(
            new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false }),
        );
    };

    return (
        <PortalLayout title="Route to Incident — Dispatch View" subtitle="Dijkstra-computed shortest path from BFP Lian Station to incident location">
            <div className="grid gap-4 lg:grid-cols-[360px_1fr]">
                {/* Left column */}
                <div className="space-y-4">
                    <PortalCard className="p-4">
                        <div className="mb-3 flex items-center gap-3">
                            <div className="flex size-9 flex-shrink-0 items-center justify-center rounded-lg bg-orange-100">
                                <AlertTriangle size={18} className="text-brand-orange" />
                            </div>
                            <div>
                                <h2 className="text-sm font-bold text-brand-navy">Target Incident</h2>
                                <p className="font-mono text-xs text-brand-muted">{incident.reference}</p>
                            </div>
                        </div>
                        <dl className="space-y-2 text-sm">
                            {(
                                [
                                    ['Type', incident.type],
                                    ['Barangay', incident.barangay],
                                    ['Severity', incident.severity],
                                    ['Reporter', incident.reporter],
                                ] as const
                            ).map(([label, value]) => (
                                <div key={label} className="flex items-center justify-between">
                                    <dt className="text-brand-muted">{label}</dt>
                                    <dd className="font-semibold text-brand-navy">{value}</dd>
                                </div>
                            ))}
                        </dl>
                    </PortalCard>

                    <PortalCard className="p-4">
                        <h3 className="mb-3 text-xs font-bold tracking-wider text-brand-muted uppercase">Route Summary</h3>
                        <div className="grid grid-cols-2 gap-3">
                            {(
                                [
                                    ['Est. Distance', `${displayDistanceKm} km`],
                                    ['Est. Time', `${displayEtaMinutes} min`],
                                    ['Route Type', routeSummary.routeType],
                                    ['Algorithm', routeSummary.algorithm],
                                ] as const
                            ).map(([label, value]) => (
                                <div key={label} className="rounded-lg bg-gray-50 px-3 py-2.5">
                                    <p className="text-xs text-brand-muted">{label}</p>
                                    <p className="font-mono text-sm font-bold text-brand-navy">{value}</p>
                                </div>
                            ))}
                        </div>
                        <p className="mt-2 text-[11px] text-brand-muted">
                            {roadRoute
                                ? 'Distance & time via OpenStreetMap road routing.'
                                : routeFailed
                                  ? 'Road routing unavailable — showing straight-line estimate.'
                                  : 'Loading road route…'}
                        </p>
                    </PortalCard>

                    <PortalCard className="p-4">
                        <h3 className="mb-3 text-xs font-bold tracking-wider text-brand-muted uppercase">Turn-by-Turn Directions</h3>
                        <ol className="space-y-3">
                            {directions.map((step, i) => {
                                const isFirst = i === 0;
                                const isLast = i === directions.length - 1;
                                return (
                                    <li key={step} className="flex items-start gap-3">
                                        <span
                                            className="flex size-5 flex-shrink-0 items-center justify-center rounded-full text-[11px] font-bold text-white"
                                            style={{ background: isLast ? '#E63946' : isFirst ? '#2A9D8F' : '#457B9D' }}
                                        >
                                            {i + 1}
                                        </span>
                                        <span className="text-xs text-brand-navy">{step}</span>
                                    </li>
                                );
                            })}
                        </ol>
                    </PortalCard>

                    {dispatched ? (
                        <div className="flex flex-col items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-4 text-center">
                            <CheckCircle2 size={20} className="text-emerald-600" />
                            <p className="text-sm font-bold text-emerald-700">Unit Dispatched</p>
                            <p className="text-xs text-emerald-700/80">
                                En route at {dispatchedAt} — ETA {displayEtaMinutes} min
                            </p>
                        </div>
                    ) : (
                        <button
                            onClick={handleDispatch}
                            className="flex w-full items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-semibold text-white transition-colors hover:opacity-90"
                            style={{ background: '#1D3557' }}
                        >
                            <Truck size={16} />
                            Dispatch Unit
                        </button>
                    )}
                </div>

                {/* Route map */}
                <PortalCard className="flex flex-col overflow-hidden">
                    <div className="border-b px-4 py-3" style={{ borderColor: 'rgba(43,45,66,0.08)' }}>
                        <h2 className="text-sm font-bold text-brand-navy">
                            Route Map — {station.name.replace(' Station', '')} → {incident.barangay}
                        </h2>
                    </div>
                    <div className="relative flex-1" style={{ minHeight: 520 }}>
                        <MapContainer
                            bounds={[LIAN_CENTER, incidentPosition]}
                            boundsOptions={{ padding: [60, 60] }}
                            style={{ height: '100%', width: '100%' }}
                        >
                            <TileLayer url={OSM_TILE_URL} attribution={OSM_ATTRIBUTION} />
                            <ScaleControl position="bottomleft" imperial={false} />

                            <Polyline
                                positions={roadRoute?.path ?? [LIAN_CENTER, midpoint, incidentPosition]}
                                pathOptions={{ color: '#F4A261', weight: 4, dashArray: roadRoute ? undefined : '10 8' }}
                            />

                            <Marker position={LIAN_CENTER} icon={stationIcon} />
                            <Marker position={incidentPosition} icon={incidentIcon(dispatched)} />
                        </MapContainer>

                        {/* Legend */}
                        <div className="absolute bottom-3 left-14 z-[1000] space-y-1.5 rounded-lg bg-white/95 px-3 py-2 shadow">
                            <div className="flex items-center gap-2">
                                <span className="inline-block h-0.5 w-5 border-t-2 border-dashed" style={{ borderColor: '#F4A261' }} />
                                <span className="text-xs text-brand-muted">Optimal route</span>
                            </div>
                            <div className="flex items-center gap-2">
                                <span className="flex size-3.5 rounded-sm" style={{ background: '#1D3557' }} />
                                <span className="text-xs text-brand-muted">{station.name}</span>
                            </div>
                        </div>
                    </div>
                </PortalCard>
            </div>
        </PortalLayout>
    );
}
