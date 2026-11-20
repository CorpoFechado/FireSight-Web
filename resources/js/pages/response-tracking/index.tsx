import 'leaflet/dist/leaflet.css';
import { Link, router } from '@inertiajs/react';
import L from 'leaflet';
import { AlertTriangle, Navigation } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import {
    Marker,
    MapContainer,
    Polyline,
    ScaleControl,
    TileLayer,
    useMap,
} from 'react-leaflet';
import { PortalCard } from '@/components/portal/portal-card';
import { AlarmLevelBadge } from '@/components/portal/status-badge';
import PortalLayout from '@/layouts/portal-layout';
import type { AlarmLevel } from '@/lib/fire-status';
import {
    LIAN_CENTER,
    OSM_ATTRIBUTION,
    OSM_TILE_URL,
} from '@/lib/map-constants';

type Incident = {
    id: number;
    reference: string;
    type: string;
    barangay: string;
    alarm_level: AlarmLevel | null;
    reporter: string;
    latitude: number;
    longitude: number;
};

type DispatchedIncident = {
    id: number;
    reference: string;
    barangay: string;
    type: string;
    alarm_level: AlarmLevel | null;
};

type RouteSummary = {
    distanceKm: number;
    etaMinutes: number;
    routeType: string;
    algorithm?: string;
};

type ResponderLocation = {
    report_id?: number;
    user_id?: number;
    user_name?: string;
    rank?: string;
    latitude: number;
    longitude: number;
    heading?: number | null;
    speed?: number | null;
    updated_at?: string;
    timestamp?: number;
};

type RoadRouteData = {
    path: [number, number][];
    offRoadStart?: [number, number][] | null;
    offRoadEnd?: [number, number][] | null;
    distanceKm: number;
    etaMinutes: number;
};

type Station = { name: string };

function getDistanceInMeters(
    lat1: number,
    lon1: number,
    lat2: number,
    lon2: number,
): number {
    const R = 6371e3;
    const phi1 = (lat1 * Math.PI) / 180;
    const phi2 = (lat2 * Math.PI) / 180;
    const deltaPhi = ((lat2 - lat1) * Math.PI) / 180;
    const deltaLambda = ((lon2 - lon1) * Math.PI) / 180;

    const a =
        Math.sin(deltaPhi / 2) * Math.sin(deltaPhi / 2) +
        Math.cos(phi1) *
            Math.cos(phi2) *
            Math.sin(deltaLambda / 2) *
            Math.sin(deltaLambda / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

    return R * c;
}

const stationIcon = L.divIcon({
    className: '',
    html: `<div style="width:24px;height:24px;border-radius:6px;background:#1D3557;border:2px solid white;display:flex;align-items:center;justify-content:center;color:white;font-size:11px;font-weight:700;box-shadow:0 1px 4px rgba(0,0,0,0.35)">S</div>`,
    iconSize: [24, 24],
    iconAnchor: [12, 12],
});

function firetruckIcon() {
    return L.divIcon({
        className: '',
        html: `
            <div style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;">
                <span style="font-size:26px;line-height:1;user-select:none;filter:drop-shadow(0 2px 4px rgba(0,0,0,0.35));">🚒</span>
            </div>
        `,
        iconSize: [32, 32],
        iconAnchor: [16, 16],
    });
}

function incidentIcon() {
    return L.divIcon({
        className: '',
        html: `<div style="width:26px;height:26px;border-radius:50%;background:#E63946;border:3px solid white;box-shadow:0 1px 6px rgba(0,0,0,0.4)"></div>`,
        iconSize: [26, 26],
        iconAnchor: [13, 13],
    });
}

/**
 * Automatically fits the map to the initial route bounds ONLY ONCE when an incident loads,
 * allowing user pan/drag and zoom to remain completely uninterrupted thereafter.
 */
function MapViewportController({
    initialBounds,
    incidentId,
}: {
    initialBounds: [[number, number], [number, number]];
    incidentId: number;
}) {
    const map = useMap();
    const fittedIncidentIdRef = useRef<number | null>(null);

    useEffect(() => {
        if (fittedIncidentIdRef.current !== incidentId) {
            fittedIncidentIdRef.current = incidentId;
            const timer = setTimeout(() => {
                map.invalidateSize();
                map.fitBounds(initialBounds, { padding: [50, 50] });
            }, 200);
            return () => clearTimeout(timer);
        }
    }, [map, incidentId, initialBounds]);

    useEffect(() => {
        const handleResize = () => {
            map.invalidateSize();
        };

        window.addEventListener('resize', handleResize);

        let observer: ResizeObserver | null = null;
        const container = map.getContainer();
        if (typeof ResizeObserver !== 'undefined' && container) {
            observer = new ResizeObserver(() => {
                map.invalidateSize();
            });
            observer.observe(container);
        }

        return () => {
            window.removeEventListener('resize', handleResize);
            observer?.disconnect();
        };
    }, [map]);

    return null;
}

/**
 * Floating recenter button allowing dispatcher to re-align view with route anytime.
 */
function RecenterControl({
    bounds,
}: {
    bounds: [[number, number], [number, number]];
}) {
    const map = useMap();

    return (
        <div className="leaflet-top leaflet-right" style={{ pointerEvents: 'auto', margin: '12px 12px 0 0' }}>
            <button
                type="button"
                onClick={() => {
                    map.invalidateSize();
                    map.fitBounds(bounds, { padding: [50, 50] });
                }}
                className="flex size-9 items-center justify-center rounded-lg bg-white text-gray-700 shadow-md transition hover:bg-gray-50 hover:text-brand-orange border border-gray-200"
                title="Recenter route view"
            >
                <Navigation className="size-4 rotate-45" />
            </button>
        </div>
    );
}

export default function ResponseTracking({
    incident,
    dispatchedIncidents = [],
    routeSummary,
    initialResponderLocation,
    station,
}: {
    incident: Incident | null;
    dispatchedIncidents?: DispatchedIncident[];
    routeSummary?: RouteSummary | null;
    initialResponderLocation?: ResponderLocation | null;
    station: Station;
}) {
    const [roadRoute, setRoadRoute] = useState<RoadRouteData | null>(null);
    const [responderLocation, setResponderLocation] = useState<ResponderLocation | null>(
        initialResponderLocation ?? null,
    );

    const incidentPosition: [number, number] | null = incident
        ? [incident.latitude, incident.longitude]
        : null;

    // Origin coordinate: live responder phone location if available, otherwise Lian Fire Station
    const originCoords: [number, number] = useMemo(() => {
        if (
            responderLocation &&
            typeof responderLocation.latitude === 'number' &&
            typeof responderLocation.longitude === 'number' &&
            !isNaN(responderLocation.latitude) &&
            !isNaN(responderLocation.longitude)
        ) {
            return [responderLocation.latitude, responderLocation.longitude];
        }
        return [LIAN_CENTER[0], LIAN_CENTER[1]];
    }, [responderLocation]);

    const midpoint: [number, number] | null = incidentPosition
        ? [
              (originCoords[0] + incidentPosition[0]) / 2 + 0.002,
              (originCoords[1] + incidentPosition[1]) / 2 - 0.0015,
          ]
        : null;

    // 1. Poll live responder location from mobile phone every 3 seconds
    useEffect(() => {
        if (!incident?.id) {
            setResponderLocation(null);
            return;
        }

        let cancelled = false;

        const pollLocation = async () => {
            try {
                const res = await fetch(`/response/${incident.id}/location`, {
                    headers: {
                        Accept: 'application/json',
                    },
                    credentials: 'same-origin',
                });
                if (!res.ok) return;
                const data = (await res.json()) as { success: boolean; location: ResponderLocation | null };
                if (!cancelled && data.success) {
                    setResponderLocation(data.location ?? null);
                }
            } catch {
                // Ignore network glitch on polling
            }
        };

        void pollLocation();
        const interval = setInterval(pollLocation, 3000);

        return () => {
            cancelled = true;
            clearInterval(interval);
        };
    }, [incident?.id]);

    // 2. Dynamic OSRM road recalculation & Off-Road Connector detection
    useEffect(() => {
        if (!incidentPosition) {
            setRoadRoute(null);
            return;
        }

        let cancelled = false;
        const coords = `${originCoords[1]},${originCoords[0]};${incidentPosition[1]},${incidentPosition[0]}`;

        fetch(
            `https://router.project-osrm.org/route/v1/driving/${coords}?overview=full&geometries=geojson`,
        )
            .then((res) => res.json())
            .then(
                (data: {
                    code: string;
                    routes?: {
                        geometry: { coordinates: [number, number][] };
                        distance: number;
                        duration: number;
                    }[];
                }) => {
                    if (
                        cancelled ||
                        data.code !== 'Ok' ||
                        !data.routes?.length
                    ) {
                        return;
                    }

                    const route = data.routes[0];
                    const path: [number, number][] = route.geometry.coordinates.map(([lng, lat]) => [
                        lat,
                        lng,
                    ]);

                    // Off-road connector at departure origin (if responder is off-street > 10m)
                    let offRoadStart: [number, number][] | null = null;
                    let offRoadDistStart = 0;
                    if (path.length > 0) {
                        const distToStart = getDistanceInMeters(
                            originCoords[0],
                            originCoords[1],
                            path[0][0],
                            path[0][1],
                        );
                        if (distToStart > 10) {
                            offRoadStart = [originCoords, path[0]];
                            offRoadDistStart = distToStart;
                        }
                    }

                    // Off-road connector at arrival destination (if fire is off-street > 10m)
                    let offRoadEnd: [number, number][] | null = null;
                    let offRoadDistEnd = 0;
                    if (path.length > 0) {
                        const lastPt = path[path.length - 1];
                        const distToEnd = getDistanceInMeters(
                            incidentPosition[0],
                            incidentPosition[1],
                            lastPt[0],
                            lastPt[1],
                        );
                        if (distToEnd > 10) {
                            offRoadEnd = [lastPt, incidentPosition];
                            offRoadDistEnd = distToEnd;
                        }
                    }

                    const totalDistMeters = route.distance + offRoadDistStart + offRoadDistEnd;
                    const totalDurationSecs = route.duration + (offRoadDistStart + offRoadDistEnd) / 8.3;

                    setRoadRoute({
                        path,
                        offRoadStart,
                        offRoadEnd,
                        distanceKm: Math.round((totalDistMeters / 1000) * 10) / 10,
                        etaMinutes: Math.max(1, Math.round(totalDurationSecs / 60)),
                    });
                },
            )
            .catch(() => {
                // Keep null to fall back to straight line
            });

        return () => {
            cancelled = true;
        };
    }, [originCoords, incidentPosition]);

    if (!incident || !routeSummary || !incidentPosition || !midpoint) {
        return (
            <PortalLayout title="Response Tracking">
                <div className="flex min-h-[380px] sm:min-h-[480px] flex-1 items-center justify-center py-8">
                    <PortalCard className="flex w-full max-w-md flex-col items-center justify-center p-6 sm:p-8 text-center">
                        <div className="mb-4 flex size-14 items-center justify-center rounded-2xl bg-orange-50 text-brand-orange">
                            <Navigation className="size-7" />
                        </div>
                        <h2 className="text-base font-bold text-brand-navy">
                            No Active Dispatches
                        </h2>
                        <p className="mt-2 text-xs leading-relaxed text-brand-muted">
                            There are currently no fire incidents in dispatched status. Live road routing and response tracking activate automatically once a response unit is dispatched.
                        </p>
                        <div className="mt-6 flex flex-wrap items-center justify-center gap-3">
                            <Link
                                href="/incidents"
                                className="inline-flex items-center gap-2 rounded-lg bg-[#E63946] px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-[#d62828] transition-colors"
                            >
                                View Incidents
                            </Link>
                            <Link
                                href="/map"
                                className="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-brand-navy shadow-sm hover:bg-gray-50 transition-colors"
                            >
                                Open Incident Map
                            </Link>
                        </div>
                    </PortalCard>
                </div>
            </PortalLayout>
        );
    }

    const displayDistanceKm = roadRoute?.distanceKm ?? routeSummary.distanceKm;
    const displayEtaMinutes = roadRoute?.etaMinutes ?? routeSummary.etaMinutes;

    return (
        <PortalLayout title="Response Tracking">
            <div className="flex flex-col gap-4 lg:grid lg:grid-cols-[360px_1fr] lg:h-[calc(100vh-140px)] lg:min-h-[580px] pb-6 lg:pb-0">
                {/* Route map: Shows prominently on mobile, expands on desktop */}
                <PortalCard className="order-1 flex flex-col overflow-hidden lg:order-2 lg:h-full">
                    <div
                        className="flex shrink-0 flex-wrap items-center justify-between gap-2 border-b px-4 py-3"
                        style={{ borderColor: 'rgba(43,45,66,0.08)' }}
                    >
                        <div className="min-w-0">
                            <h2 className="truncate text-xs sm:text-sm font-bold text-brand-navy">
                                Route Map — {station.name.replace(' Station', '')} →{' '}
                                {incident.barangay}
                            </h2>
                            <p className="text-[11px] text-brand-muted truncate sm:hidden">
                                {displayDistanceKm} km · {displayEtaMinutes} min est.
                            </p>
                        </div>
                        <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] sm:text-[11px] font-semibold text-emerald-700">
                            <span className="size-1.5 rounded-full bg-emerald-500 animate-pulse" />
                            {responderLocation ? 'Live GPS' : 'Live Route'}
                        </span>
                    </div>

                    <div className="relative flex-1 w-full h-[360px] sm:h-[460px] lg:h-full min-h-[320px] sm:min-h-[420px]">
                        <MapContainer
                            center={midpoint ?? LIAN_CENTER}
                            zoom={13}
                            style={{ height: '100%', width: '100%' }}
                            scrollWheelZoom={true}
                        >
                            <MapViewportController
                                initialBounds={[originCoords, incidentPosition]}
                                incidentId={incident.id}
                            />
                            <RecenterControl
                                bounds={[originCoords, incidentPosition]}
                            />
                            <TileLayer
                                url={OSM_TILE_URL}
                                attribution={OSM_ATTRIBUTION}
                            />
                            <ScaleControl
                                position="bottomleft"
                                imperial={false}
                            />

                            {/* Off-road departure connector (dashed) */}
                            {roadRoute?.offRoadStart && (
                                <Polyline
                                    positions={roadRoute.offRoadStart}
                                    pathOptions={{
                                        color: '#E63946',
                                        weight: 3.5,
                                        dashArray: '4 5',
                                        opacity: 0.85,
                                    }}
                                />
                            )}

                            {/* Solid road route */}
                            <Polyline
                                positions={
                                    roadRoute?.path ?? [
                                        originCoords,
                                        midpoint,
                                        incidentPosition,
                                    ]
                                }
                                pathOptions={{
                                    color: '#E63946',
                                    weight: 5,
                                    opacity: 0.9,
                                    dashArray: roadRoute ? undefined : '10 8',
                                }}
                            />

                            {/* Off-road arrival connector (dashed) */}
                            {roadRoute?.offRoadEnd && (
                                <Polyline
                                    positions={roadRoute.offRoadEnd}
                                    pathOptions={{
                                        color: '#E63946',
                                        weight: 3.5,
                                        dashArray: '4 5',
                                        opacity: 0.85,
                                    }}
                                />
                            )}

                            {/* BFP Station Marker */}
                            <Marker position={LIAN_CENTER} icon={stationIcon} />

                            {/* Live Firetruck Marker (if responder is broadcasting GPS) */}
                            {responderLocation && (
                                <Marker
                                    position={[responderLocation.latitude, responderLocation.longitude]}
                                    icon={firetruckIcon()}
                                    zIndexOffset={1000}
                                />
                            )}

                            {/* Incident Location Marker */}
                            <Marker
                                position={incidentPosition}
                                icon={incidentIcon()}
                            />
                        </MapContainer>

                        {/* Legend */}
                        <div className="absolute bottom-3 left-3 sm:left-14 z-[1000] space-y-1 rounded-lg bg-white/95 px-2.5 py-1.5 shadow-md backdrop-blur-xs text-[11px]">
                            <div className="flex items-center gap-2">
                                <span
                                    className="inline-block h-1 w-4 rounded-xs"
                                    style={{ background: '#E63946' }}
                                />
                                <span className="text-brand-muted text-[11px]">
                                    Optimal route
                                </span>
                            </div>
                            {responderLocation && (
                                <div className="flex items-center gap-2">
                                    <span className="text-xs">🚒</span>
                                    <span className="text-brand-muted text-[11px]">
                                        Live Firetruck
                                    </span>
                                </div>
                            )}
                            <div className="flex items-center gap-2">
                                <span
                                    className="flex size-3 rounded-xs"
                                    style={{ background: '#1D3557' }}
                                />
                                <span className="text-brand-muted text-[11px]">
                                    {station.name}
                                </span>
                            </div>
                        </div>
                    </div>
                </PortalCard>

                {/* Left/Bottom column: Actionable incident & route cards */}
                <div className="order-2 flex flex-col gap-4 lg:order-1 lg:overflow-y-auto lg:pr-1">
                    <PortalCard className="p-4">
                        {dispatchedIncidents.length > 1 && (
                            <div className="mb-3 border-b border-gray-100 pb-3">
                                <label
                                    htmlFor="select-dispatch-incident"
                                    className="mb-1 block text-[11px] font-bold tracking-wider text-brand-muted uppercase"
                                >
                                    Active Dispatches ({dispatchedIncidents.length})
                                </label>
                                <select
                                    id="select-dispatch-incident"
                                    value={incident.id}
                                    onChange={(e) => {
                                        router.get(
                                            '/response',
                                            { incident: e.target.value },
                                            { preserveState: true, replace: true },
                                        );
                                    }}
                                    className="w-full rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-brand-navy focus:border-brand-navy focus:outline-none"
                                >
                                    {dispatchedIncidents.map((d) => (
                                        <option key={d.id} value={d.id}>
                                            {d.reference} — {d.barangay} ({d.type})
                                        </option>
                                    ))}
                                </select>
                            </div>
                        )}

                        <div className="mb-3 flex items-center gap-3">
                            <div className="flex size-9 flex-shrink-0 items-center justify-center rounded-lg bg-orange-100">
                                <AlertTriangle
                                    size={18}
                                    className="text-brand-orange"
                                />
                            </div>
                            <div className="min-w-0 flex-1">
                                <h2 className="text-sm font-bold text-brand-navy truncate">
                                    Target Incident
                                </h2>
                                <p className="font-mono text-xs text-brand-muted truncate">
                                    {incident.reference}
                                </p>
                            </div>
                        </div>
                        <dl className="space-y-2 text-xs sm:text-sm">
                            {(
                                [
                                    ['Type', incident.type],
                                    ['Barangay', incident.barangay],
                                    ['Reporter', incident.reporter],
                                ] as const
                            ).map(([label, value]) => (
                                <div
                                    key={label}
                                    className="flex items-center justify-between gap-2"
                                >
                                    <dt className="text-brand-muted shrink-0">
                                        {label}
                                    </dt>
                                    <dd className="font-semibold text-brand-navy text-right truncate">
                                        {value}
                                    </dd>
                                </div>
                            ))}
                            {incident.alarm_level && (
                                <div className="flex items-center justify-between gap-2">
                                    <dt className="text-brand-muted shrink-0">
                                        Alarm Level
                                    </dt>
                                    <dd>
                                        <AlarmLevelBadge
                                            alarmLevel={incident.alarm_level}
                                        />
                                    </dd>
                                </div>
                            )}
                        </dl>
                    </PortalCard>

                    <PortalCard className="p-4">
                        <h3 className="mb-3 text-xs font-bold tracking-wider text-brand-muted uppercase">
                            Route Summary
                        </h3>
                        <div className="grid grid-cols-3 gap-2">
                            {(
                                [
                                    [
                                        'Est. Distance',
                                        `${displayDistanceKm} km`,
                                    ],
                                    ['Est. Time', `${displayEtaMinutes} min`],
                                    ['Route Type', routeSummary.routeType],
                                ] as const
                            ).map(([label, value]) => (
                                <div
                                    key={label}
                                    className="rounded-lg bg-gray-50 px-2 py-2 min-w-0 text-center sm:text-left"
                                >
                                    <p className="text-[10px] sm:text-[11px] text-brand-muted truncate">
                                        {label}
                                    </p>
                                    <p className="font-mono text-xs sm:text-sm font-bold text-brand-navy truncate">
                                        {value}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </PortalCard>

                    <PortalCard className="p-4">
                        <h3 className="mb-3 text-xs font-bold tracking-wider text-brand-muted uppercase">
                            Station & Target
                        </h3>
                        <div className="space-y-3 text-xs">
                            <div className="flex items-start gap-2.5">
                                <div className="flex size-6 shrink-0 items-center justify-center rounded-md bg-[#1D3557] text-[10px] font-bold text-white">
                                    S
                                </div>
                                <div className="min-w-0 flex-1">
                                    <p className="font-semibold text-brand-navy truncate">
                                        {station.name}
                                    </p>
                                    <p className="text-[11px] text-brand-muted truncate">
                                        Poblacion, Lian, Batangas
                                    </p>
                                </div>
                            </div>
                            <div className="flex items-start gap-2.5">
                                <div className="flex size-6 shrink-0 items-center justify-center rounded-md bg-[#E63946] text-[10px] font-bold text-white">
                                    !
                                </div>
                                <div className="min-w-0 flex-1">
                                    <p className="font-semibold text-brand-navy truncate">
                                        Incident in {incident.barangay}
                                    </p>
                                    <p className="font-mono text-[11px] text-brand-muted truncate">
                                        {incident.latitude.toFixed(5)},{' '}
                                        {incident.longitude.toFixed(5)}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </PortalCard>
                </div>
            </div>
        </PortalLayout>
    );
}
