import 'leaflet/dist/leaflet.css';
import { Link, router } from '@inertiajs/react';
import L from 'leaflet';
import { AlertTriangle, Navigation } from 'lucide-react';
import { useEffect, useState } from 'react';
import {
    Marker,
    MapContainer,
    Polyline,
    ScaleControl,
    TileLayer,
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

type Station = { name: string };

const stationIcon = L.divIcon({
    className: '',
    html: `<div style="width:24px;height:24px;border-radius:6px;background:#1D3557;border:2px solid white;display:flex;align-items:center;justify-content:center;color:white;font-size:11px;font-weight:700;box-shadow:0 1px 4px rgba(0,0,0,0.35)">S</div>`,
    iconSize: [24, 24],
    iconAnchor: [12, 12],
});

function incidentIcon() {
    return L.divIcon({
        className: '',
        html: `<div style="width:26px;height:26px;border-radius:50%;background:#E63946;border:3px solid white;box-shadow:0 1px 6px rgba(0,0,0,0.4)"></div>`,
        iconSize: [26, 26],
        iconAnchor: [13, 13],
    });
}

export default function ResponseTracking({
    incident,
    dispatchedIncidents = [],
    routeSummary,
    station,
}: {
    incident: Incident | null;
    dispatchedIncidents?: DispatchedIncident[];
    routeSummary?: RouteSummary | null;
    station: Station;
}) {
    const [roadRoute, setRoadRoute] = useState<{
        path: [number, number][];
        distanceKm: number;
        etaMinutes: number;
    } | null>(null);

    const incidentPosition: [number, number] | null = incident
        ? [incident.latitude, incident.longitude]
        : null;

    const midpoint: [number, number] | null = incidentPosition
        ? [
              (LIAN_CENTER[0] + incidentPosition[0]) / 2 + 0.002,
              (LIAN_CENTER[1] + incidentPosition[1]) / 2 - 0.0015,
          ]
        : null;

    useEffect(() => {
        if (!incidentPosition) {
            setRoadRoute(null);
            return;
        }

        let cancelled = false;
        const coords = `${LIAN_CENTER[1]},${LIAN_CENTER[0]};${incidentPosition[1]},${incidentPosition[0]}`;

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
                    setRoadRoute({
                        path: route.geometry.coordinates.map(([lng, lat]) => [
                            lat,
                            lng,
                        ]),
                        distanceKm:
                            Math.round((route.distance / 1000) * 10) / 10,
                        etaMinutes: Math.max(
                            1,
                            Math.round(route.duration / 60),
                        ),
                    });
                },
            )
            .catch(() => {
                // Keep null to fall back to straight line
            });

        return () => {
            cancelled = true;
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [incident?.latitude, incident?.longitude]);

    if (!incident || !routeSummary || !incidentPosition || !midpoint) {
        return (
            <PortalLayout
                title="Route to Incident — Dispatch View"
                subtitle="Live response tracking and road routing for active dispatches"
            >
                <div className="flex h-[calc(100vh-140px)] min-h-[580px] items-center justify-center">
                    <PortalCard className="flex max-w-md flex-col items-center justify-center p-8 text-center">
                        <div className="mb-4 flex size-14 items-center justify-center rounded-2xl bg-orange-50 text-brand-orange">
                            <Navigation className="size-7" />
                        </div>
                        <h2 className="text-base font-bold text-brand-navy">
                            No Active Dispatches
                        </h2>
                        <p className="mt-2 text-xs leading-relaxed text-brand-muted">
                            There are currently no fire incidents in dispatched status. Live road routing and response tracking activate automatically once a response unit is dispatched.
                        </p>
                        <div className="mt-6 flex items-center gap-3">
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
        <PortalLayout
            title="Route to Incident — Dispatch View"
            subtitle="Shortest path from BFP Lian Station to incident location"
        >
            <div className="grid h-[calc(100vh-140px)] min-h-[580px] gap-4 lg:grid-cols-[360px_1fr]">
                {/* Left column: Actionable incident & route cards */}
                <div className="flex flex-col gap-4 overflow-y-auto pr-1">
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
                            <div>
                                <h2 className="text-sm font-bold text-brand-navy">
                                    Target Incident
                                </h2>
                                <p className="font-mono text-xs text-brand-muted">
                                    {incident.reference}
                                </p>
                            </div>
                        </div>
                        <dl className="space-y-2 text-sm">
                            {(
                                [
                                    ['Type', incident.type],
                                    ['Barangay', incident.barangay],
                                    ['Reporter', incident.reporter],
                                ] as const
                            ).map(([label, value]) => (
                                <div
                                    key={label}
                                    className="flex items-center justify-between"
                                >
                                    <dt className="text-brand-muted">
                                        {label}
                                    </dt>
                                    <dd className="font-semibold text-brand-navy">
                                        {value}
                                    </dd>
                                </div>
                            ))}
                            {incident.alarm_level && (
                                <div className="flex items-center justify-between">
                                    <dt className="text-brand-muted">
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
                        <div className="grid grid-cols-3 gap-2.5">
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
                                    className="rounded-lg bg-gray-50 px-2.5 py-2"
                                >
                                    <p className="text-[11px] text-brand-muted">
                                        {label}
                                    </p>
                                    <p className="font-mono text-sm font-bold text-brand-navy">
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
                                <div>
                                    <p className="font-semibold text-brand-navy">
                                        {station.name}
                                    </p>
                                    <p className="text-[11px] text-brand-muted">
                                        Poblacion, Lian, Batangas
                                    </p>
                                </div>
                            </div>
                            <div className="flex items-start gap-2.5">
                                <div className="flex size-6 shrink-0 items-center justify-center rounded-md bg-[#E63946] text-[10px] font-bold text-white">
                                    !
                                </div>
                                <div>
                                    <p className="font-semibold text-brand-navy">
                                        Incident in {incident.barangay}
                                    </p>
                                    <p className="font-mono text-[11px] text-brand-muted">
                                        {incident.latitude.toFixed(5)},{' '}
                                        {incident.longitude.toFixed(5)}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </PortalCard>
                </div>

                {/* Route map: Expands to maximize available space */}
                <PortalCard className="flex h-full flex-col overflow-hidden">
                    <div
                        className="shrink-0 border-b px-4 py-3"
                        style={{ borderColor: 'rgba(43,45,66,0.08)' }}
                    >
                        <h2 className="text-sm font-bold text-brand-navy">
                            Route Map — {station.name.replace(' Station', '')} →{' '}
                            {incident.barangay}
                        </h2>
                    </div>
                    <div className="relative flex-1 w-full h-full min-h-[460px]">
                        <MapContainer
                            bounds={[LIAN_CENTER, incidentPosition]}
                            boundsOptions={{ padding: [60, 60] }}
                            style={{ height: '100%', width: '100%' }}
                        >
                            <TileLayer
                                url={OSM_TILE_URL}
                                attribution={OSM_ATTRIBUTION}
                            />
                            <ScaleControl
                                position="bottomleft"
                                imperial={false}
                            />

                            <Polyline
                                positions={
                                    roadRoute?.path ?? [
                                        LIAN_CENTER,
                                        midpoint,
                                        incidentPosition,
                                    ]
                                }
                                pathOptions={{
                                    color: '#F4A261',
                                    weight: 4,
                                    dashArray: roadRoute ? undefined : '10 8',
                                }}
                            />

                            <Marker position={LIAN_CENTER} icon={stationIcon} />
                            <Marker
                                position={incidentPosition}
                                icon={incidentIcon()}
                            />
                        </MapContainer>

                        {/* Legend */}
                        <div className="absolute bottom-3 left-14 z-[1000] space-y-1.5 rounded-lg bg-white/95 px-3 py-2 shadow">
                            <div className="flex items-center gap-2">
                                <span
                                    className="inline-block h-0.5 w-5 border-t-2 border-dashed"
                                    style={{ borderColor: '#F4A261' }}
                                />
                                <span className="text-xs text-brand-muted">
                                    Optimal route
                                </span>
                            </div>
                            <div className="flex items-center gap-2">
                                <span
                                    className="flex size-3.5 rounded-sm"
                                    style={{ background: '#1D3557' }}
                                />
                                <span className="text-xs text-brand-muted">
                                    {station.name}
                                </span>
                            </div>
                        </div>
                    </div>
                </PortalCard>
            </div>
        </PortalLayout>
    );
}
