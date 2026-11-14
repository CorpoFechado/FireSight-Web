import 'leaflet/dist/leaflet.css';
import { useForm } from '@inertiajs/react';
import L from 'leaflet';
import {
    AlertCircle,
    ArrowLeft,
    ArrowRight,
    Camera,
    Check,
    Flame,
    MapPin,
    Maximize2,
    Trash2,
    Truck,
    Users,
    X,
} from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import type { ChangeEvent, FormEvent } from 'react';
import { renderToStaticMarkup } from 'react-dom/server';
import {
    MapContainer,
    Marker,
    TileLayer,
    useMap,
    useMapEvents,
    ZoomControl,
} from 'react-leaflet';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    BARANGAY_GEOJSON_URL,
    getFeatureBarangayName,
    resolveDbBarangayName,
} from '@/lib/barangay-geo';
import { ALARM_LEVEL_CFG } from '@/lib/fire-status';
import { OSM_ATTRIBUTION, OSM_TILE_URL } from '@/lib/map-constants';
import { storeManual } from '@/routes/incidents';

export type PersonnelOption = {
    user_id: number;
    name: string;
    rank: string | null;
};

type GeoJsonFeature = {
    properties?: Record<string, unknown>;
    geometry?: {
        type: 'Polygon' | 'MultiPolygon';
        coordinates: number[][][] | number[][][][];
    };
};

const LIAN_CENTER: [number, number] = [14.03694, 120.652673];

const INCIDENT_TYPES = [
    { value: 'residential_fire', label: 'Residential Fire' },
    { value: 'commercial_fire', label: 'Commercial Fire' },
    { value: 'vehicular_fire', label: 'Vehicular Fire' },
    { value: 'storage_fire', label: 'Storage Fire' },
    { value: 'rubbish_fire', label: 'Rubbish Fire' },
    { value: 'others', label: 'Others' },
];

const ALARM_LEVELS = Object.entries(ALARM_LEVEL_CFG).map(([value, config]) => ({
    value,
    label: config.label,
}));

const pinIcon = L.divIcon({
    className: '',
    html: renderToStaticMarkup(
        <span
            style={{
                alignItems: 'center',
                background: '#E63946',
                border: '3px solid white',
                borderRadius: '9999px',
                boxShadow: '0 2px 8px rgba(0, 0, 0, 0.35)',
                color: 'white',
                display: 'flex',
                height: 36,
                justifyContent: 'center',
                width: 36,
            }}
        >
            <Flame size={18} strokeWidth={2.5} />
        </span>,
    ),
    iconSize: [36, 36],
    iconAnchor: [18, 18],
});

function pointInRing(lat: number, lng: number, ring: number[][]): boolean {
    let inside = false;
    for (let i = 0, j = ring.length - 1; i < ring.length; j = i++) {
        const xi = ring[i][0];
        const yi = ring[i][1];
        const xj = ring[j][0];
        const yj = ring[j][1];

        const intersects =
            yi > lat !== yj > lat &&
            lng < ((xj - xi) * (lat - yi)) / (yj - yi) + xi;
        if (intersects) {
            inside = !inside;
        }
    }
    return inside;
}

function detectBarangayName(
    lat: number,
    lng: number,
    features: GeoJsonFeature[],
): string | null {
    for (const feature of features) {
        const geom = feature.geometry;
        if (!geom) continue;

        if (geom.type === 'Polygon') {
            const ring = (geom.coordinates as number[][][])[0] ?? [];
            if (pointInRing(lat, lng, ring)) {
                const raw = getFeatureBarangayName(feature.properties);
                return raw ? resolveDbBarangayName(raw) : null;
            }
        } else if (geom.type === 'MultiPolygon') {
            for (const poly of geom.coordinates as number[][][][]) {
                const ring = poly[0] ?? [];
                if (pointInRing(lat, lng, ring)) {
                    const raw = getFeatureBarangayName(feature.properties);
                    return raw ? resolveDbBarangayName(raw) : null;
                }
            }
        }
    }

    return null;
}

function MapClickHandler({
    onSelect,
}: {
    onSelect: (lat: number, lng: number) => void;
}) {
    useMapEvents({
        click(e) {
            onSelect(
                Number(e.latlng.lat.toFixed(7)),
                Number(e.latlng.lng.toFixed(7)),
            );
        },
    });
    return null;
}

function MapViewSync({
    latitude,
    longitude,
}: {
    latitude: string;
    longitude: string;
}) {
    const map = useMap();

    useEffect(() => {
        if (!latitude || !longitude) return;
        const lat = Number(latitude);
        const lng = Number(longitude);
        if (!Number.isNaN(lat) && !Number.isNaN(lng)) {
            map.panTo([lat, lng], { animate: true });
        }
    }, [latitude, longitude, map]);

    return null;
}

function todayDateString(): string {
    const now = new Date();
    const pad = (n: number) => String(n).padStart(2, '0');
    return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
}

export function CreateManualReportModal({
    open,
    onOpenChange,
    personnel,
    availableTrucks,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    personnel: PersonnelOption[];
    availableTrucks: string[];
}) {
    const [step, setStep] = useState<1 | 2>(1);
    const [expandedMapOpen, setExpandedMapOpen] = useState(false);
    const [startTime, setStartTime] = useState('');
    const [outTime, setOutTime] = useState('');
    const [geoFeatures, setGeoFeatures] = useState<GeoJsonFeature[]>([]);
    const [photoPreviews, setPhotoPreviews] = useState<string[]>([]);
    const fileInputRef = useRef<HTMLInputElement | null>(null);

    const { data, setData, post, processing, errors, reset, clearErrors } =
        useForm<{
            reporter_name: string;
            contact_number: string;
            incident_date: string;
            description: string;
            latitude: string;
            longitude: string;
            photos: File[];
            trucks: string[];
            personnel_ids: number[];
            establishment_name: string;
            owner_name: string;
            occupant_name: string;
            time_fire_started: string;
            time_fire_out: string;
            incident_type: string;
            alarm_level: string;
            cause_of_fire: string;
            injuries: string;
            fatalities: string;
            estimated_damage: string;
            notes: string;
        }>({
            reporter_name: '',
            contact_number: '',
            incident_date: todayDateString(),
            description: '',
            latitude: '',
            longitude: '',
            photos: [],
            trucks: [],
            personnel_ids: [],
            establishment_name: '',
            owner_name: '',
            occupant_name: '',
            time_fire_started: '',
            time_fire_out: '',
            incident_type: '',
            alarm_level: '',
            cause_of_fire: '',
            injuries: '0',
            fatalities: '0',
            estimated_damage: '',
            notes: '',
        });

    useEffect(() => {
        if (!open || geoFeatures.length > 0) return;

        fetch(BARANGAY_GEOJSON_URL)
            .then((res) => (res.ok ? res.json() : null))
            .then((json) => {
                if (json && Array.isArray(json.features)) {
                    setGeoFeatures(json.features);
                }
            })
            .catch(() => {
                // Backend still resolves barangay accurately via GeoBoundaryService
            });
    }, [open, geoFeatures.length]);

    useEffect(() => {
        const urls = data.photos.map((file) => URL.createObjectURL(file));
        setPhotoPreviews(urls);
        return () => {
            urls.forEach((url) => URL.revokeObjectURL(url));
        };
    }, [data.photos]);

    const detectedBarangay = useMemo(() => {
        if (!data.latitude || !data.longitude || geoFeatures.length === 0) {
            return null;
        }
        return detectBarangayName(
            Number(data.latitude),
            Number(data.longitude),
            geoFeatures,
        );
    }, [data.latitude, data.longitude, geoFeatures]);

    const isStep1Valid = Boolean(
        data.reporter_name.trim() &&
        data.contact_number.trim() &&
        data.incident_date &&
        data.description.trim() &&
        data.latitude &&
        data.longitude,
    );

    const isStep2Valid =
        data.trucks.length > 0 && data.personnel_ids.length > 0;

    const handleDateChange = (newDate: string) => {
        setData((prev) => ({
            ...prev,
            incident_date: newDate,
            time_fire_started:
                startTime && newDate ? `${newDate} ${startTime}:00` : '',
            time_fire_out: outTime && newDate ? `${newDate} ${outTime}:00` : '',
        }));
    };

    const handlePinSelect = (lat: number, lng: number) => {
        setData((prev) => ({
            ...prev,
            latitude: String(lat),
            longitude: String(lng),
        }));
    };

    const handlePhotosChange = (e: ChangeEvent<HTMLInputElement>) => {
        const files = Array.from(e.target.files ?? []);
        if (files.length === 0) return;
        setData('photos', [...data.photos, ...files]);
        if (fileInputRef.current) {
            fileInputRef.current.value = '';
        }
    };

    const removePhoto = (index: number) => {
        setData(
            'photos',
            data.photos.filter((_, i) => i !== index),
        );
    };

    const toggleTruck = (truck: string) => {
        const exists = data.trucks.includes(truck);
        setData(
            'trucks',
            exists
                ? data.trucks.filter((t) => t !== truck)
                : [...data.trucks, truck],
        );
    };

    const togglePersonnel = (userId: number) => {
        const exists = data.personnel_ids.includes(userId);
        setData(
            'personnel_ids',
            exists
                ? data.personnel_ids.filter((id) => id !== userId)
                : [...data.personnel_ids, userId],
        );
    };

    const handleOpenChange = (next: boolean) => {
        if (!next) {
            clearErrors();
            reset();
            setStep(1);
            setStartTime('');
            setOutTime('');
        }
        onOpenChange(next);
    };

    const submit = (e: FormEvent) => {
        e.preventDefault();
        if (!isStep2Valid) return;

        post(storeManual().url, {
            forceFormData: true,
            onSuccess: () => {
                handleOpenChange(false);
            },
            onError: (errs) => {
                const step1Fields = [
                    'reporter_name',
                    'contact_number',
                    'incident_date',
                    'description',
                    'latitude',
                    'longitude',
                    'photos',
                ];
                const hasStep1Error = Object.keys(errs).some((key) =>
                    step1Fields.some(
                        (f) => key === f || key.startsWith(`${f}.`),
                    ),
                );
                if (hasStep1Error) {
                    setStep(1);
                }
            },
        });
    };

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogContent className="flex flex-col gap-0 overflow-hidden bg-white p-0 text-brand-navy sm:max-w-3xl">
                {/* Fixed Header */}
                <DialogHeader
                    className="border-b px-6 py-4"
                    style={{ borderColor: 'rgba(43,45,66,0.1)' }}
                >
                    <div className="flex flex-wrap items-center justify-between gap-2 pr-6">
                        <div>
                            <DialogTitle className="text-base font-bold text-brand-navy">
                                Add Manual Incident Report
                            </DialogTitle>
                            <p className="mt-0.5 text-xs text-brand-muted">
                                {step === 1
                                    ? 'Step 1 of 2 · Caller Details, Pinned Location & Scene Photos'
                                    : 'Step 2 of 2 · Dispatched Units & Spot Report Assessment'}
                            </p>
                        </div>

                        <div className="flex items-center gap-1.5">
                            <button
                                type="button"
                                onClick={() => setStep(1)}
                                className={`rounded-full px-2.5 py-1 text-[11px] font-semibold transition ${
                                    step === 1
                                        ? 'bg-brand-navy text-white'
                                        : 'bg-brand-bg text-brand-navy hover:bg-gray-200'
                                }`}
                            >
                                1. Caller & Pin
                            </button>
                            <button
                                type="button"
                                disabled={!isStep1Valid}
                                onClick={() => isStep1Valid && setStep(2)}
                                className={`rounded-full px-2.5 py-1 text-[11px] font-semibold transition disabled:opacity-45 ${
                                    step === 2
                                        ? 'bg-brand-navy text-white'
                                        : 'bg-brand-bg text-brand-navy hover:bg-gray-200'
                                }`}
                            >
                                2. Units & Assessment
                            </button>
                        </div>
                    </div>
                </DialogHeader>

                <form onSubmit={submit} className="flex flex-col">
                    {/* Fixed-Height Scrollable Body — prevents layout jump between steps */}
                    <div className="h-[540px] max-h-[68vh] overflow-y-auto px-6 py-4">
                        {step === 1 ? (
                            <div className="space-y-4">
                                <div className="grid gap-3 sm:grid-cols-3">
                                    <div className="space-y-1.5">
                                        <Label>
                                            Caller Name{' '}
                                            <span className="text-brand-red">
                                                *
                                            </span>
                                        </Label>
                                        <input
                                            value={data.reporter_name}
                                            onChange={(e) =>
                                                setData(
                                                    'reporter_name',
                                                    e.target.value,
                                                )
                                            }
                                            placeholder="Full name of caller"
                                            className="h-9 w-full rounded-md border px-3 text-sm outline-none"
                                            style={{
                                                borderColor:
                                                    'rgba(43,45,66,0.15)',
                                            }}
                                        />
                                        {errors.reporter_name && (
                                            <p className="text-xs text-brand-red">
                                                {errors.reporter_name}
                                            </p>
                                        )}
                                    </div>

                                    <div className="space-y-1.5">
                                        <Label>
                                            Contact Number{' '}
                                            <span className="text-brand-red">
                                                *
                                            </span>
                                        </Label>
                                        <input
                                            value={data.contact_number}
                                            onChange={(e) =>
                                                setData(
                                                    'contact_number',
                                                    e.target.value,
                                                )
                                            }
                                            placeholder="09XX-XXX-XXXX"
                                            className="h-9 w-full rounded-md border px-3 text-sm outline-none"
                                            style={{
                                                borderColor:
                                                    'rgba(43,45,66,0.15)',
                                            }}
                                        />
                                        {errors.contact_number && (
                                            <p className="text-xs text-brand-red">
                                                {errors.contact_number}
                                            </p>
                                        )}
                                    </div>

                                    <div className="space-y-1.5">
                                        <Label>
                                            Incident Date{' '}
                                            <span className="text-brand-red">
                                                *
                                            </span>
                                        </Label>
                                        <input
                                            type="date"
                                            value={data.incident_date}
                                            onChange={(e) =>
                                                handleDateChange(e.target.value)
                                            }
                                            className="h-9 w-full rounded-md border bg-white px-3 text-sm outline-none"
                                            style={{
                                                borderColor:
                                                    'rgba(43,45,66,0.15)',
                                            }}
                                        />
                                        {errors.incident_date && (
                                            <p className="text-xs text-brand-red">
                                                {errors.incident_date}
                                            </p>
                                        )}
                                    </div>
                                </div>

                                <div className="space-y-1.5">
                                    <Label>
                                        Incident Description{' '}
                                        <span className="text-brand-red">
                                            *
                                        </span>
                                    </Label>
                                    <textarea
                                        value={data.description}
                                        onChange={(e) =>
                                            setData(
                                                'description',
                                                e.target.value,
                                            )
                                        }
                                        rows={2}
                                        placeholder="Summary of the reported fire incident..."
                                        className="w-full rounded-md border px-3 py-2 text-sm outline-none"
                                        style={{
                                            borderColor: 'rgba(43,45,66,0.15)',
                                        }}
                                    />
                                    {errors.description && (
                                        <p className="text-xs text-brand-red">
                                            {errors.description}
                                        </p>
                                    )}
                                </div>

                                {/* Map Pin Picker */}
                                <div className="space-y-1.5">
                                    <div className="flex items-center justify-between">
                                        <Label>
                                            Pin Incident Location{' '}
                                            <span className="text-brand-red">
                                                *
                                            </span>
                                        </Label>
                                        {data.latitude && data.longitude ? (
                                            <span className="font-mono text-[11px] text-brand-muted">
                                                {Number(data.latitude).toFixed(
                                                    5,
                                                )}
                                                ,{' '}
                                                {Number(data.longitude).toFixed(
                                                    5,
                                                )}
                                            </span>
                                        ) : (
                                            <span className="text-[11px] text-brand-muted">
                                                Click on the map to place the
                                                pin
                                            </span>
                                        )}
                                    </div>

                                    <div className="relative isolate h-[230px] overflow-hidden rounded-lg border border-[rgba(43,45,66,0.15)]">
                                        <MapContainer
                                            center={LIAN_CENTER}
                                            zoom={14}
                                            zoomControl={false}
                                            style={{
                                                height: '100%',
                                                width: '100%',
                                            }}
                                        >
                                            <TileLayer
                                                url={OSM_TILE_URL}
                                                attribution={OSM_ATTRIBUTION}
                                            />
                                            <ZoomControl position="bottomleft" />
                                            <MapClickHandler
                                                onSelect={handlePinSelect}
                                            />
                                            <MapViewSync
                                                latitude={data.latitude}
                                                longitude={data.longitude}
                                            />
                                            {data.latitude &&
                                                data.longitude && (
                                                    <Marker
                                                        position={[
                                                            Number(
                                                                data.latitude,
                                                            ),
                                                            Number(
                                                                data.longitude,
                                                            ),
                                                        ]}
                                                        icon={pinIcon}
                                                        draggable
                                                        eventHandlers={{
                                                            dragend: (e) => {
                                                                const pos =
                                                                    e.target.getLatLng();
                                                                handlePinSelect(
                                                                    Number(
                                                                        pos.lat.toFixed(
                                                                            7,
                                                                        ),
                                                                    ),
                                                                    Number(
                                                                        pos.lng.toFixed(
                                                                            7,
                                                                        ),
                                                                    ),
                                                                );
                                                            },
                                                        }}
                                                    />
                                                )}
                                        </MapContainer>

                                        <div className="pointer-events-none absolute top-2 left-2 z-[400] flex items-center gap-1.5 rounded-md bg-white/95 px-2.5 py-1 text-xs font-semibold text-brand-navy shadow-xs">
                                            <MapPin
                                                size={12}
                                                className="text-brand-red"
                                            />
                                            {!data.latitude ||
                                            !data.longitude ? (
                                                <span>
                                                    Click map to pin fire
                                                    location
                                                </span>
                                            ) : detectedBarangay ? (
                                                <span>
                                                    Brgy. {detectedBarangay},
                                                    Lian
                                                </span>
                                            ) : (
                                                <span className="text-amber-700">
                                                    Border / Outside Lian
                                                    (verified on save)
                                                </span>
                                            )}
                                        </div>

                                        <button
                                            type="button"
                                            onClick={() =>
                                                setExpandedMapOpen(true)
                                            }
                                            className="absolute top-2 right-2 z-[400] flex items-center gap-1 rounded-md border border-[rgba(43,45,66,0.18)] bg-white/95 px-2.5 py-1 text-xs font-semibold text-brand-navy shadow-xs transition hover:bg-white"
                                            title="Open larger map to pin location"
                                        >
                                            <Maximize2
                                                size={12}
                                                className="text-brand-blue"
                                            />
                                            Larger Map
                                        </button>
                                    </div>
                                    {(errors.latitude || errors.longitude) && (
                                        <p className="text-xs text-brand-red">
                                            {errors.latitude ||
                                                errors.longitude}
                                        </p>
                                    )}
                                </div>

                                {/* Optional Multiple Scene Photos */}
                                <div className="space-y-2">
                                    <div className="flex items-center justify-between">
                                        <Label>
                                            Fire Scene Photos{' '}
                                            <span className="text-xs font-normal text-brand-muted">
                                                (Optional — can be added later)
                                            </span>
                                        </Label>
                                        <button
                                            type="button"
                                            onClick={() =>
                                                fileInputRef.current?.click()
                                            }
                                            className="flex items-center gap-1.5 rounded-md border border-[rgba(43,45,66,0.18)] bg-brand-bg px-2.5 py-1 text-xs font-semibold text-brand-navy transition hover:bg-gray-200/70"
                                        >
                                            <Camera
                                                size={13}
                                                className="text-brand-blue"
                                            />
                                            Add Photos
                                        </button>
                                        <input
                                            ref={fileInputRef}
                                            type="file"
                                            multiple
                                            accept="image/jpeg,image/png,image/heic,image/heif"
                                            onChange={handlePhotosChange}
                                            className="hidden"
                                        />
                                    </div>

                                    {photoPreviews.length > 0 ? (
                                        <div className="grid grid-cols-4 gap-2 sm:grid-cols-6">
                                            {photoPreviews.map((url, idx) => (
                                                <div
                                                    key={`${url}-${idx}`}
                                                    className="group relative h-16 overflow-hidden rounded-md border border-[rgba(43,45,66,0.12)] bg-gray-50"
                                                >
                                                    <img
                                                        src={url}
                                                        alt={`Selected ${idx + 1}`}
                                                        className="h-full w-full object-cover"
                                                    />
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            removePhoto(idx)
                                                        }
                                                        className="absolute top-1 right-1 rounded-full bg-black/70 p-0.5 text-white opacity-90 hover:bg-brand-red"
                                                        title="Remove photo"
                                                    >
                                                        <Trash2 size={11} />
                                                    </button>
                                                </div>
                                            ))}
                                        </div>
                                    ) : (
                                        <p className="rounded-md border border-dashed border-[rgba(43,45,66,0.18)] bg-brand-bg/50 px-3 py-2 text-xs text-brand-muted">
                                            No photos attached yet. You may
                                            upload multiple JPG, PNG, or HEIC
                                            images now or after saving.
                                        </p>
                                    )}
                                    {errors.photos && (
                                        <p className="text-xs text-brand-red">
                                            {errors.photos}
                                        </p>
                                    )}
                                </div>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {/* Required Dispatched Units */}
                                <div className="rounded-xl border border-[rgba(43,45,66,0.12)] bg-brand-bg/60 p-3.5 space-y-3">
                                    <div className="space-y-1.5">
                                        <Label className="flex items-center gap-1.5">
                                            <Truck
                                                size={13}
                                                className="text-brand-blue"
                                            />
                                            Fire Trucks Used{' '}
                                            <span className="text-brand-red">
                                                *
                                            </span>
                                        </Label>
                                        <div className="flex flex-wrap gap-2">
                                            {availableTrucks.map((truck) => {
                                                const selected =
                                                    data.trucks.includes(truck);
                                                return (
                                                    <button
                                                        key={truck}
                                                        type="button"
                                                        onClick={() =>
                                                            toggleTruck(truck)
                                                        }
                                                        className={`flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-semibold transition ${
                                                            selected
                                                                ? 'border-brand-navy bg-brand-navy text-white'
                                                                : 'border-[rgba(43,45,66,0.18)] bg-white text-brand-navy hover:bg-gray-50'
                                                        }`}
                                                    >
                                                        {selected && (
                                                            <Check size={12} />
                                                        )}
                                                        {truck}
                                                    </button>
                                                );
                                            })}
                                        </div>
                                        {errors.trucks && (
                                            <p className="text-xs text-brand-red">
                                                {errors.trucks}
                                            </p>
                                        )}
                                    </div>

                                    <div className="space-y-1.5">
                                        <Label className="flex items-center gap-1.5">
                                            <Users
                                                size={13}
                                                className="text-brand-blue"
                                            />
                                            Dispatched Personnel{' '}
                                            <span className="text-brand-red">
                                                *
                                            </span>
                                            <span className="text-[11px] font-normal text-brand-muted">
                                                ({data.personnel_ids.length}{' '}
                                                selected)
                                            </span>
                                        </Label>
                                        <div className="grid max-h-32 grid-cols-1 gap-1.5 overflow-y-auto rounded-lg border border-[rgba(43,45,66,0.12)] bg-white p-2 sm:grid-cols-2">
                                            {personnel.map((person) => {
                                                const selected =
                                                    data.personnel_ids.includes(
                                                        person.user_id,
                                                    );
                                                return (
                                                    <button
                                                        key={person.user_id}
                                                        type="button"
                                                        onClick={() =>
                                                            togglePersonnel(
                                                                person.user_id,
                                                            )
                                                        }
                                                        className={`flex items-center justify-between rounded-md border px-2.5 py-1.5 text-left text-xs transition ${
                                                            selected
                                                                ? 'border-brand-navy bg-brand-navy/10 font-semibold text-brand-navy'
                                                                : 'border-transparent bg-brand-bg/60 text-brand-navy hover:bg-brand-bg'
                                                        }`}
                                                    >
                                                        <span className="truncate">
                                                            {person.rank
                                                                ? `${person.rank} `
                                                                : ''}
                                                            {person.name}
                                                        </span>
                                                        {selected && (
                                                            <Check
                                                                size={12}
                                                                className="shrink-0 text-brand-navy"
                                                            />
                                                        )}
                                                    </button>
                                                );
                                            })}
                                        </div>
                                        {errors.personnel_ids && (
                                            <p className="text-xs text-brand-red">
                                                {errors.personnel_ids}
                                            </p>
                                        )}
                                    </div>
                                </div>

                                {/* Spot Report / BFP Assessment Fields (same format as EditDetailsModal) */}
                                <div className="space-y-3">
                                    <p className="text-xs font-semibold tracking-wider text-brand-muted uppercase">
                                        Spot Report Assessment Details
                                    </p>

                                    <div className="space-y-1.5">
                                        <Label>Name of Establishment</Label>
                                        <input
                                            value={data.establishment_name}
                                            onChange={(e) =>
                                                setData(
                                                    'establishment_name',
                                                    e.target.value,
                                                )
                                            }
                                            placeholder="Complete name of the establishment"
                                            className="h-9 w-full rounded-md border px-3 text-sm outline-none"
                                            style={{
                                                borderColor:
                                                    'rgba(43,45,66,0.15)',
                                            }}
                                        />
                                        {errors.establishment_name && (
                                            <p className="text-xs text-brand-red">
                                                {errors.establishment_name}
                                            </p>
                                        )}
                                    </div>

                                    <div className="grid grid-cols-2 gap-3">
                                        <div className="space-y-1.5">
                                            <Label>Owner</Label>
                                            <input
                                                value={data.owner_name}
                                                onChange={(e) =>
                                                    setData(
                                                        'owner_name',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="Structure or property owner"
                                                className="h-9 w-full rounded-md border px-3 text-sm outline-none"
                                                style={{
                                                    borderColor:
                                                        'rgba(43,45,66,0.15)',
                                                }}
                                            />
                                            {errors.owner_name && (
                                                <p className="text-xs text-brand-red">
                                                    {errors.owner_name}
                                                </p>
                                            )}
                                        </div>

                                        <div className="space-y-1.5">
                                            <Label>Occupant</Label>
                                            <input
                                                value={data.occupant_name}
                                                onChange={(e) =>
                                                    setData(
                                                        'occupant_name',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="Tenant or resident occupant"
                                                className="h-9 w-full rounded-md border px-3 text-sm outline-none"
                                                style={{
                                                    borderColor:
                                                        'rgba(43,45,66,0.15)',
                                                }}
                                            />
                                            {errors.occupant_name && (
                                                <p className="text-xs text-brand-red">
                                                    {errors.occupant_name}
                                                </p>
                                            )}
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-2 gap-3">
                                        <div className="space-y-1.5">
                                            <Label>Time Fire Started</Label>
                                            <input
                                                type="time"
                                                value={startTime}
                                                onChange={(e) => {
                                                    const val = e.target.value;
                                                    setStartTime(val);
                                                    setData(
                                                        'time_fire_started',
                                                        val &&
                                                            data.incident_date
                                                            ? `${data.incident_date} ${val}:00`
                                                            : '',
                                                    );
                                                }}
                                                className="h-9 w-full rounded-md border bg-white px-3 text-sm outline-none"
                                                style={{
                                                    borderColor:
                                                        'rgba(43,45,66,0.15)',
                                                }}
                                            />
                                            {errors.time_fire_started && (
                                                <p className="text-xs text-brand-red">
                                                    {errors.time_fire_started}
                                                </p>
                                            )}
                                        </div>

                                        <div className="space-y-1.5">
                                            <Label>Time Fire Out</Label>
                                            <input
                                                type="time"
                                                value={outTime}
                                                onChange={(e) => {
                                                    const val = e.target.value;
                                                    setOutTime(val);
                                                    setData(
                                                        'time_fire_out',
                                                        val &&
                                                            data.incident_date
                                                            ? `${data.incident_date} ${val}:00`
                                                            : '',
                                                    );
                                                }}
                                                className="h-9 w-full rounded-md border bg-white px-3 text-sm outline-none"
                                                style={{
                                                    borderColor:
                                                        'rgba(43,45,66,0.15)',
                                                }}
                                            />
                                            {errors.time_fire_out && (
                                                <p className="text-xs text-brand-red">
                                                    {errors.time_fire_out}
                                                </p>
                                            )}
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-2 gap-3">
                                        <div className="space-y-1.5">
                                            <Label>Incident Type</Label>
                                            <div className="relative">
                                                <Select
                                                    value={data.incident_type}
                                                    onValueChange={(v) =>
                                                        setData(
                                                            'incident_type',
                                                            v,
                                                        )
                                                    }
                                                >
                                                    <SelectTrigger
                                                        className={`w-full ${data.incident_type ? 'pr-8 [&_svg]:hidden' : ''}`}
                                                    >
                                                        <SelectValue placeholder="Select type" />
                                                    </SelectTrigger>
                                                    <SelectContent className="bg-white text-brand-navy">
                                                        {INCIDENT_TYPES.map(
                                                            (t) => (
                                                                <SelectItem
                                                                    key={
                                                                        t.value
                                                                    }
                                                                    value={
                                                                        t.value
                                                                    }
                                                                >
                                                                    {t.label}
                                                                </SelectItem>
                                                            ),
                                                        )}
                                                    </SelectContent>
                                                </Select>
                                                {data.incident_type && (
                                                    <button
                                                        type="button"
                                                        onClick={(e) => {
                                                            e.preventDefault();
                                                            e.stopPropagation();
                                                            setData(
                                                                'incident_type',
                                                                '',
                                                            );
                                                        }}
                                                        className="absolute top-1/2 right-2.5 z-10 flex size-5 -translate-y-1/2 items-center justify-center rounded-full text-brand-muted transition-colors hover:bg-neutral-100 hover:text-brand-navy"
                                                        title="Clear Incident Type"
                                                    >
                                                        <X className="size-3.5" />
                                                    </button>
                                                )}
                                            </div>
                                            {errors.incident_type && (
                                                <p className="text-xs text-brand-red">
                                                    {errors.incident_type}
                                                </p>
                                            )}
                                        </div>

                                        <div className="space-y-1.5">
                                            <Label>Alarm Level</Label>
                                            <div className="relative">
                                                <Select
                                                    value={data.alarm_level}
                                                    onValueChange={(v) =>
                                                        setData(
                                                            'alarm_level',
                                                            v,
                                                        )
                                                    }
                                                >
                                                    <SelectTrigger
                                                        className={`w-full ${data.alarm_level ? 'pr-8 [&_svg]:hidden' : ''}`}
                                                    >
                                                        <SelectValue placeholder="Select alarm level" />
                                                    </SelectTrigger>
                                                    <SelectContent className="bg-white text-brand-navy">
                                                        {ALARM_LEVELS.map(
                                                            (a) => (
                                                                <SelectItem
                                                                    key={
                                                                        a.value
                                                                    }
                                                                    value={
                                                                        a.value
                                                                    }
                                                                >
                                                                    {a.label}
                                                                </SelectItem>
                                                            ),
                                                        )}
                                                    </SelectContent>
                                                </Select>
                                                {data.alarm_level && (
                                                    <button
                                                        type="button"
                                                        onClick={(e) => {
                                                            e.preventDefault();
                                                            e.stopPropagation();
                                                            setData(
                                                                'alarm_level',
                                                                '',
                                                            );
                                                        }}
                                                        className="absolute top-1/2 right-2.5 z-10 flex size-5 -translate-y-1/2 items-center justify-center rounded-full text-brand-muted transition-colors hover:bg-neutral-100 hover:text-brand-navy"
                                                        title="Clear Alarm Level"
                                                    >
                                                        <X className="size-3.5" />
                                                    </button>
                                                )}
                                            </div>
                                            {errors.alarm_level && (
                                                <p className="text-xs text-brand-red">
                                                    {errors.alarm_level}
                                                </p>
                                            )}
                                        </div>
                                    </div>

                                    <div className="space-y-1.5">
                                        <Label>Cause of Fire</Label>
                                        <input
                                            value={data.cause_of_fire}
                                            onChange={(e) =>
                                                setData(
                                                    'cause_of_fire',
                                                    e.target.value,
                                                )
                                            }
                                            placeholder="e.g. Electrical short circuit"
                                            className="h-9 w-full rounded-md border px-3 text-sm outline-none"
                                            style={{
                                                borderColor:
                                                    'rgba(43,45,66,0.15)',
                                            }}
                                        />
                                        {errors.cause_of_fire && (
                                            <p className="text-xs text-brand-red">
                                                {errors.cause_of_fire}
                                            </p>
                                        )}
                                    </div>

                                    <div className="grid grid-cols-3 gap-3">
                                        <div className="space-y-1.5">
                                            <Label>Injuries</Label>
                                            <input
                                                type="number"
                                                min={0}
                                                value={data.injuries}
                                                onChange={(e) =>
                                                    setData(
                                                        'injuries',
                                                        e.target.value,
                                                    )
                                                }
                                                className="h-9 w-full rounded-md border px-3 text-sm outline-none"
                                                style={{
                                                    borderColor:
                                                        'rgba(43,45,66,0.15)',
                                                }}
                                            />
                                            {errors.injuries && (
                                                <p className="text-xs text-brand-red">
                                                    {errors.injuries}
                                                </p>
                                            )}
                                        </div>

                                        <div className="space-y-1.5">
                                            <Label>Fatalities</Label>
                                            <input
                                                type="number"
                                                min={0}
                                                value={data.fatalities}
                                                onChange={(e) =>
                                                    setData(
                                                        'fatalities',
                                                        e.target.value,
                                                    )
                                                }
                                                className="h-9 w-full rounded-md border px-3 text-sm outline-none"
                                                style={{
                                                    borderColor:
                                                        'rgba(43,45,66,0.15)',
                                                }}
                                            />
                                            {errors.fatalities && (
                                                <p className="text-xs text-brand-red">
                                                    {errors.fatalities}
                                                </p>
                                            )}
                                        </div>

                                        <div className="space-y-1.5">
                                            <Label>Estimated Damage (₱)</Label>
                                            <input
                                                type="number"
                                                min={0}
                                                step="any"
                                                value={data.estimated_damage}
                                                onChange={(e) =>
                                                    setData(
                                                        'estimated_damage',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="e.g. 150000"
                                                className="h-9 w-full rounded-md border px-3 text-sm outline-none"
                                                style={{
                                                    borderColor:
                                                        'rgba(43,45,66,0.15)',
                                                }}
                                            />
                                            {errors.estimated_damage && (
                                                <p className="text-xs text-brand-red">
                                                    {errors.estimated_damage}
                                                </p>
                                            )}
                                        </div>
                                    </div>

                                    <div className="space-y-1.5">
                                        <Label>Notes</Label>
                                        <textarea
                                            value={data.notes}
                                            onChange={(e) =>
                                                setData('notes', e.target.value)
                                            }
                                            rows={3}
                                            placeholder="Add incident notes, response units, or observations..."
                                            className="w-full rounded-md border px-3 py-2 text-sm outline-none"
                                            style={{
                                                borderColor:
                                                    'rgba(43,45,66,0.15)',
                                            }}
                                        />
                                        {errors.notes && (
                                            <p className="text-xs text-brand-red">
                                                {errors.notes}
                                            </p>
                                        )}
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Fixed Footer */}
                    <div
                        className="flex flex-wrap items-center justify-between gap-2 border-t bg-brand-bg/50 px-6 py-3.5"
                        style={{ borderColor: 'rgba(43,45,66,0.1)' }}
                    >
                        {step === 1 ? (
                            <>
                                <button
                                    type="button"
                                    onClick={() => handleOpenChange(false)}
                                    className="rounded-lg px-4 py-2 text-sm font-semibold text-brand-muted hover:text-brand-navy"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="button"
                                    disabled={!isStep1Valid}
                                    onClick={() => setStep(2)}
                                    className="flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-semibold text-white disabled:opacity-45"
                                    style={{ background: '#1D3557' }}
                                >
                                    Next: Units & Assessment
                                    <ArrowRight size={14} />
                                </button>
                            </>
                        ) : (
                            <>
                                <button
                                    type="button"
                                    onClick={() => setStep(1)}
                                    className="flex items-center gap-1.5 rounded-lg border border-[rgba(43,45,66,0.18)] bg-white px-3.5 py-2 text-sm font-semibold text-brand-navy hover:bg-brand-bg"
                                >
                                    <ArrowLeft size={14} />
                                    Back
                                </button>

                                <div className="flex items-center gap-2">
                                    {!isStep2Valid && (
                                        <span className="hidden items-center gap-1 text-xs text-amber-700 sm:flex">
                                            <AlertCircle size={13} />
                                            Select at least 1 truck & 1
                                            personnel
                                        </span>
                                    )}
                                    <button
                                        type="submit"
                                        disabled={processing || !isStep2Valid}
                                        className="rounded-lg px-4 py-2 text-sm font-semibold text-white disabled:opacity-45"
                                        style={{ background: '#1D3557' }}
                                    >
                                        {processing
                                            ? 'Saving…'
                                            : 'Save Manual Report'}
                                    </button>
                                </div>
                            </>
                        )}
                    </div>
                </form>
            </DialogContent>

            {/* Expanded Map Picker Modal */}
            <Dialog open={expandedMapOpen} onOpenChange={setExpandedMapOpen}>
                <DialogContent className="flex flex-col gap-0 overflow-hidden bg-white p-0 text-brand-navy sm:max-w-5xl">
                    <DialogHeader
                        className="border-b px-6 py-3.5"
                        style={{ borderColor: 'rgba(43,45,66,0.1)' }}
                    >
                        <div className="flex flex-wrap items-center justify-between gap-2 pr-6">
                            <div>
                                <DialogTitle className="text-base font-bold text-brand-navy">
                                    Pin Incident Location
                                </DialogTitle>
                                <p className="text-xs text-brand-muted">
                                    Click anywhere on the map or drag the fire
                                    marker to set the exact location.
                                </p>
                            </div>
                            {data.latitude && data.longitude && (
                                <span className="font-mono text-xs text-brand-muted">
                                    {Number(data.latitude).toFixed(6)},{' '}
                                    {Number(data.longitude).toFixed(6)}
                                </span>
                            )}
                        </div>
                    </DialogHeader>

                    <div className="relative isolate h-[65vh] w-full">
                        {expandedMapOpen && (
                            <MapContainer
                                center={
                                    data.latitude && data.longitude
                                        ? [
                                              Number(data.latitude),
                                              Number(data.longitude),
                                          ]
                                        : LIAN_CENTER
                                }
                                zoom={data.latitude && data.longitude ? 16 : 14}
                                zoomControl={false}
                                style={{ height: '100%', width: '100%' }}
                            >
                                <TileLayer
                                    url={OSM_TILE_URL}
                                    attribution={OSM_ATTRIBUTION}
                                />
                                <ZoomControl position="bottomleft" />
                                <MapClickHandler onSelect={handlePinSelect} />
                                {data.latitude && data.longitude && (
                                    <Marker
                                        position={[
                                            Number(data.latitude),
                                            Number(data.longitude),
                                        ]}
                                        icon={pinIcon}
                                        draggable
                                        eventHandlers={{
                                            dragend: (e) => {
                                                const pos =
                                                    e.target.getLatLng();
                                                handlePinSelect(
                                                    Number(pos.lat.toFixed(7)),
                                                    Number(pos.lng.toFixed(7)),
                                                );
                                            },
                                        }}
                                    />
                                )}
                            </MapContainer>
                        )}

                        <div className="pointer-events-none absolute top-3 left-3 z-[400] flex items-center gap-1.5 rounded-md bg-white/95 px-3 py-1.5 text-xs font-semibold text-brand-navy shadow-sm">
                            <MapPin size={13} className="text-brand-red" />
                            {!data.latitude || !data.longitude ? (
                                <span>Click map to pin fire location</span>
                            ) : detectedBarangay ? (
                                <span>Brgy. {detectedBarangay}, Lian</span>
                            ) : (
                                <span className="text-amber-700">
                                    Border / Outside Lian (verified on save)
                                </span>
                            )}
                        </div>
                    </div>

                    <div
                        className="flex items-center justify-end gap-2 border-t bg-brand-bg/50 px-6 py-3"
                        style={{ borderColor: 'rgba(43,45,66,0.1)' }}
                    >
                        <button
                            type="button"
                            onClick={() => setExpandedMapOpen(false)}
                            className="rounded-lg px-4 py-2 text-sm font-semibold text-white"
                            style={{ background: '#1D3557' }}
                        >
                            Done
                        </button>
                    </div>
                </DialogContent>
            </Dialog>
        </Dialog>
    );
}
