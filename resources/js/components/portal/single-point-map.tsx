import 'leaflet/dist/leaflet.css';
import { CircleMarker, MapContainer, TileLayer } from 'react-leaflet';
import { OSM_ATTRIBUTION, OSM_TILE_URL } from '@/lib/map-constants';

export function SinglePointMap({
    latitude,
    longitude,
    color = '#E63946',
    zoom = 15,
    height = 220,
}: {
    latitude: number;
    longitude: number;
    color?: string;
    zoom?: number;
    height?: number;
}) {
    return (
        <div
            className="relative isolate overflow-hidden rounded-lg"
            style={{ height }}
        >
            <MapContainer
                center={[latitude, longitude]}
                zoom={zoom}
                scrollWheelZoom={false}
                style={{ height: '100%', width: '100%' }}
            >
                <TileLayer url={OSM_TILE_URL} attribution={OSM_ATTRIBUTION} />
                <CircleMarker
                    center={[latitude, longitude]}
                    radius={9}
                    pathOptions={{
                        color: '#fff',
                        weight: 2,
                        fillColor: color,
                        fillOpacity: 0.9,
                    }}
                />
                <CircleMarker
                    center={[latitude, longitude]}
                    radius={22}
                    pathOptions={{
                        color,
                        weight: 0,
                        fillColor: color,
                        fillOpacity: 0.15,
                    }}
                />
            </MapContainer>
        </div>
    );
}
