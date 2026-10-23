import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import { Flame, Smartphone } from 'lucide-react';
import { useEffect } from 'react';
import { renderToStaticMarkup } from 'react-dom/server';
import {
    MapContainer,
    Marker,
    Polyline,
    TileLayer,
    Tooltip,
    useMap,
    ZoomControl,
} from 'react-leaflet';
import { OSM_ATTRIBUTION, OSM_TILE_URL } from '@/lib/map-constants';

const FIRE_COLOR = '#E63946';
const NAVY_COLOR = '#1D3557';

const fireIcon = L.divIcon({
    className: '',
    html: renderToStaticMarkup(
        <span
            style={{
                alignItems: 'center',
                display: 'flex',
                height: 46,
                justifyContent: 'center',
                position: 'relative',
                width: 46,
            }}
        >
            <span
                className="motion-safe:animate-ping"
                style={{
                    background: 'rgba(230, 57, 70, 0.2)',
                    borderRadius: '9999px',
                    height: 46,
                    position: 'absolute',
                    width: 46,
                }}
            />
            <span
                style={{
                    alignItems: 'center',
                    background: FIRE_COLOR,
                    border: '3px solid white',
                    borderRadius: '9999px',
                    boxShadow: '0 2px 7px rgba(0, 0, 0, 0.35)',
                    color: 'white',
                    display: 'flex',
                    height: 34,
                    justifyContent: 'center',
                    position: 'relative',
                    width: 34,
                }}
            >
                <Flame size={18} strokeWidth={2.5} />
            </span>
        </span>,
    ),
    iconSize: [46, 46],
    iconAnchor: [23, 23],
});

const reporterIcon = L.divIcon({
    className: '',
    html: renderToStaticMarkup(
        <span
            style={{
                alignItems: 'center',
                background: NAVY_COLOR,
                border: '3px solid white',
                borderRadius: '9999px',
                boxShadow: '0 2px 7px rgba(0, 0, 0, 0.35)',
                color: 'white',
                display: 'flex',
                height: 34,
                justifyContent: 'center',
                width: 34,
            }}
        >
            <Smartphone size={17} strokeWidth={2.5} />
        </span>,
    ),
    iconSize: [34, 34],
    iconAnchor: [17, 17],
});

function MapView({
    latitude,
    longitude,
    deviceLatitude,
    deviceLongitude,
    zoom,
}: {
    latitude: number;
    longitude: number;
    deviceLatitude: number | null;
    deviceLongitude: number | null;
    zoom: number;
}) {
    const map = useMap();

    useEffect(() => {
        const fireLocation = L.latLng(latitude, longitude);

        if (deviceLatitude === null || deviceLongitude === null) {
            map.setView(fireLocation, zoom);

            return;
        }

        const reporterLocation = L.latLng(deviceLatitude, deviceLongitude);

        if (fireLocation.equals(reporterLocation)) {
            map.setView(fireLocation, zoom);

            return;
        }

        map.fitBounds(L.latLngBounds([fireLocation, reporterLocation]), {
            padding: [40, 40],
        });
    }, [deviceLatitude, deviceLongitude, latitude, longitude, map, zoom]);

    return null;
}

function formatDistance(distanceMeters: number): string {
    if (distanceMeters < 1000) {
        return `${Math.round(distanceMeters)} m`;
    }

    return `${(distanceMeters / 1000).toFixed(1)} km`;
}

export function IncidentLocationMap({
    latitude,
    longitude,
    deviceLatitude,
    deviceLongitude,
    height = 220,
}: {
    latitude: number;
    longitude: number;
    deviceLatitude: number | null;
    deviceLongitude: number | null;
    height?: number;
}) {
    const fireLocation: [number, number] = [latitude, longitude];
    const hasDistinctReporterLocation =
        deviceLatitude !== null &&
        deviceLongitude !== null &&
        !L.latLng(fireLocation).equals(
            L.latLng(deviceLatitude, deviceLongitude),
        );
    const reporterLocation: [number, number] | null =
        hasDistinctReporterLocation ? [deviceLatitude, deviceLongitude] : null;
    const distance = reporterLocation
        ? L.latLng(fireLocation).distanceTo(L.latLng(reporterLocation))
        : null;

    return (
        <div
            className="relative isolate overflow-hidden rounded-lg"
            style={{ height }}
        >
            <MapContainer
                className="relative z-0"
                center={fireLocation}
                zoom={15}
                zoomControl={false}
                scrollWheelZoom={false}
                style={{ height: '100%', width: '100%' }}
            >
                <TileLayer url={OSM_TILE_URL} attribution={OSM_ATTRIBUTION} />
                <ZoomControl position="bottomleft" />
                <MapView
                    latitude={latitude}
                    longitude={longitude}
                    deviceLatitude={
                        hasDistinctReporterLocation ? deviceLatitude : null
                    }
                    deviceLongitude={
                        hasDistinctReporterLocation ? deviceLongitude : null
                    }
                    zoom={15}
                />

                {reporterLocation && (
                    <Polyline
                        positions={[fireLocation, reporterLocation]}
                        pathOptions={{
                            color: NAVY_COLOR,
                            dashArray: '5 6',
                            opacity: 0.75,
                            weight: 2,
                        }}
                        interactive={false}
                    />
                )}

                <Marker position={fireLocation} icon={fireIcon}>
                    <Tooltip direction="top" offset={[0, -19]}>
                        Reported fire location
                    </Tooltip>
                </Marker>

                {reporterLocation && (
                    <Marker position={reporterLocation} icon={reporterIcon}>
                        <Tooltip direction="top" offset={[0, -14]}>
                            Reporter&apos;s device location
                        </Tooltip>
                    </Marker>
                )}
            </MapContainer>

            <div className="pointer-events-none absolute top-2 right-2 z-[20] space-y-1.5 rounded-lg bg-white/90 px-2.5 py-2 shadow-sm backdrop-blur-xs">
                <div className="flex items-center gap-2">
                    <Flame size={13} className="text-brand-red" />
                    <span className="text-[11px] font-semibold text-brand-navy">
                        Fire Location (Pinned)
                    </span>
                </div>
                {reporterLocation && (
                    <div className="flex items-center gap-2">
                        <Smartphone size={13} className="text-brand-navy" />
                        <span className="text-[11px] font-semibold text-brand-navy">
                            Reporter Location
                        </span>
                    </div>
                )}
                {distance !== null && (
                    <div className="flex items-center gap-2">
                        <span
                            className="inline-block w-[13px] border-t-2 border-dashed border-brand-muted"
                            aria-hidden="true"
                        />
                        <span className="text-[11px] font-semibold text-brand-navy">
                            Distance ({formatDistance(distance)})
                        </span>
                    </div>
                )}
            </div>
        </div>
    );
}
