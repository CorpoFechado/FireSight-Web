import 'leaflet/dist/leaflet.css';
import { router } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import L from 'leaflet';
import type { Feature, FeatureCollection, Geometry } from 'geojson';
import { CalendarDays, ChevronDown, ChevronLeft, ChevronRight, Layers } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import { CircleMarker, GeoJSON, MapContainer, Marker, Popup, TileLayer } from 'react-leaflet';
import { PortalCard } from '@/components/portal/portal-card';
import { SeverityBadge } from '@/components/portal/status-badge';
import PortalLayout from '@/layouts/portal-layout';
import {
    RISK_CFG,
    SEVERITY_CFG,
    SEVERITY_MARKER_COLORS,
    TYPE_CFG,
    riskLevelColor,
} from '@/lib/fire-status';
import type { IncidentType, RiskLevel, SeverityLevel } from '@/lib/fire-status';
import { BARANGAY_GEOJSON_URL, resolveDbBarangayName } from '@/lib/barangay-geo';
import { LIAN_CENTER, LIAN_DEFAULT_ZOOM, OSM_ATTRIBUTION, OSM_TILE_URL } from '@/lib/map-constants';
import { map as mapRoute } from '@/routes/index';
import { show as showIncident } from '@/routes/incidents';

// ─── Types ────────────────────────────────────────────────────────────────────

type BarangayGeoProperties = { barangay: string };
type BarangayFeature = Feature<Geometry, BarangayGeoProperties>;

type MapIncident = {
    report_id: number;
    reference: string;
    type: IncidentType | null;
    typeLabel: string | null;
    severity: SeverityLevel | null;
    barangay: string | null;
    latitude: number;
    longitude: number;
    dateTime: string;
};

type BarangayRiskPoint = {
    barangay_id: number;
    barangay_name: string;
    latitude: number;
    longitude: number;
    risk_level: RiskLevel | null;
    prediction_score: number | null;
};

type Period = 'this_month' | 'last_3_months' | 'this_year' | 'custom';

type Filters = {
    period: Period;
    date_from: string;
    date_to: string;
    incident_type: string;
    severity_level: string;
};

type ColorBy = 'severity' | 'type';

// ─── Constants ────────────────────────────────────────────────────────────────

const PERIOD_OPTIONS: { label: string; value: Period }[] = [
    { label: 'This Month', value: 'this_month' },
    { label: 'Last 3 Months', value: 'last_3_months' },
    { label: 'This Year', value: 'this_year' },
    { label: 'Custom', value: 'custom' },
];

const TYPE_OPTIONS = [
    { label: 'All types', value: 'all' },
    { label: 'Structure Fire', value: 'structural' },
    { label: 'Grass/Vegetation', value: 'grass' },
    { label: 'Electrical Fire', value: 'electrical' },
    { label: 'Vehicle Fire', value: 'vehicular' },
    { label: 'Other', value: 'other' },
];

const SEVERITY_OPTIONS = [
    { label: 'All severities', value: 'all' },
    { label: 'Critical', value: 'critical' },
    { label: 'High', value: 'high' },
    { label: 'Moderate', value: 'moderate' },
    { label: 'Low', value: 'low' },
];

const inputStyle = { borderColor: 'rgba(43,45,66,0.13)' };
const inputClass =
    'rounded-lg border bg-brand-bg py-1.5 px-2.5 text-sm text-brand-navy outline-none focus:ring-1 focus:ring-brand-navy/20';
const selectClass =
    'appearance-none rounded-lg border bg-brand-bg py-1.5 pl-2.5 pr-6 text-sm text-brand-navy outline-none focus:ring-1 focus:ring-brand-navy/20';

const stationIcon = L.divIcon({
    className: '',
    html: `<div style="width:26px;height:26px;border-radius:6px;background:#1D3557;border:2px solid white;display:flex;align-items:center;justify-content:center;color:white;font-size:10px;font-weight:700;box-shadow:0 1px 4px rgba(0,0,0,0.35)">BFP</div>`,
    iconSize: [26, 26],
    iconAnchor: [13, 13],
});

const FALLBACK_COLOR = '#6B7A8D';

// ─── Helpers ──────────────────────────────────────────────────────────────────

function markerColor(inc: MapIncident, colorBy: ColorBy): string {
    if (colorBy === 'severity') {
        return inc.severity ? (SEVERITY_MARKER_COLORS[inc.severity] ?? FALLBACK_COLOR) : FALLBACK_COLOR;
    }
    return inc.type ? (TYPE_CFG[inc.type]?.color ?? FALLBACK_COLOR) : FALLBACK_COLOR;
}

function buildResultSummary(
    incidents: MapIncident[],
    filters: Filters,
    periodLabel: string,
): string {
    const count = incidents.length;
    const typeLabel =
        filters.incident_type !== 'all'
            ? (TYPE_CFG[filters.incident_type as IncidentType]?.label ?? null)
            : null;
    const severityLabel =
        filters.severity_level !== 'all'
            ? (SEVERITY_CFG[filters.severity_level as SeverityLevel]?.label ?? null)
            : null;

    const qualifier = [severityLabel, typeLabel].filter(Boolean).join(' ');
    const noun = qualifier ? `${qualifier} incident${count !== 1 ? 's' : ''}` : `incident${count !== 1 ? 's' : ''}`;

    return `${count} ${noun} · ${periodLabel}`;
}

// ─── Component ────────────────────────────────────────────────────────────────

export default function FireIncidentsMap({
    incidents,
    barangayRisk,
    periodLabel,
    filters,
}: {
    incidents: MapIncident[];
    barangayRisk: BarangayRiskPoint[];
    periodLabel: string;
    filters: Filters;
}) {
    const [sidebarOpen, setSidebarOpen] = useState(true);
    const [layers, setLayers] = useState({ pins: true, risk: true, station: true });
    const [colorBy, setColorBy] = useState<ColorBy>('severity');
    const [selectedId, setSelectedId] = useState<number | null>(null);
    const [barangayGeo, setBarangayGeo] = useState<FeatureCollection<Geometry, BarangayGeoProperties> | null>(null);
    const mapRef = useRef<L.Map | null>(null);

    // Clear selection when it's no longer in the filtered list
    useEffect(() => {
        if (selectedId !== null && !incidents.find((i) => i.report_id === selectedId)) {
            setSelectedId(null);
        }
    }, [incidents, selectedId]);

    // Load barangay GeoJSON
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
        const m = new Map<string, BarangayRiskPoint>();
        for (const b of barangayRisk) {
            m.set(b.barangay_name, b);
        }
        return m;
    }, [barangayRisk]);

    // Sidebar grouped counts
    const groupedCounts = useMemo(() => {
        const counts: Record<string, number> = {};
        for (const inc of incidents) {
            const key = colorBy === 'severity' ? (inc.severity ?? 'unknown') : (inc.type ?? 'unknown');
            counts[key] = (counts[key] ?? 0) + 1;
        }
        return counts;
    }, [incidents, colorBy]);

    // Legend entries driven by color-by
    const legendEntries = useMemo(() => {
        if (colorBy === 'severity') {
            return (['critical', 'high', 'moderate', 'low'] as SeverityLevel[])
                .filter((s) => groupedCounts[s] !== undefined)
                .map((s) => ({ key: s, label: SEVERITY_CFG[s].label, color: SEVERITY_MARKER_COLORS[s] }));
        }
        return (Object.keys(TYPE_CFG) as IncidentType[])
            .filter((t) => groupedCounts[t] !== undefined)
            .map((t) => ({ key: t, label: TYPE_CFG[t].label, color: TYPE_CFG[t].color }));
    }, [colorBy, groupedCounts]);

    const navigate = (overrides: Partial<Filters>) => {
        const raw = { ...filters, ...overrides };
        // Omit 'all' / empty params from the URL
        const params: Record<string, string> = {};
        if (raw.period !== 'this_year') {
            params.period = raw.period;
        }
        if (raw.date_from) {
            params.date_from = raw.date_from;
        }
        if (raw.date_to) {
            params.date_to = raw.date_to;
        }
        if (raw.incident_type && raw.incident_type !== 'all') {
            params.incident_type = raw.incident_type;
        }
        if (raw.severity_level && raw.severity_level !== 'all') {
            params.severity_level = raw.severity_level;
        }
        router.get(mapRoute().url, params, { preserveState: true, replace: true });
    };

    const setPeriod = (period: Period) => {
        navigate({
            period,
            date_from: period === 'custom' ? filters.date_from : '',
            date_to: period === 'custom' ? filters.date_to : '',
        });
    };

    const focusIncident = (inc: MapIncident) => {
        setSelectedId(inc.report_id);
        mapRef.current?.flyTo([inc.latitude, inc.longitude], 15, { duration: 0.6 });
    };

    const resultSummary = buildResultSummary(incidents, filters, periodLabel);

    return (
        <PortalLayout title="Fire Incidents Map" subtitle="Lian, Batangas — Completed Incidents">
            <div className="space-y-3">
                {/* ── Filter bar ─────────────────────────────────────────── */}
                <PortalCard className="p-4">
                    <div className="flex flex-wrap items-center gap-3">
                        {/* Period label */}
                        <div className="flex items-center gap-1.5">
                            <CalendarDays size={14} className="text-brand-muted" />
                            <span className="text-xs font-semibold uppercase tracking-wide text-brand-muted">
                                Period
                            </span>
                        </div>

                        {/* Period segmented buttons */}
                        <div className="flex flex-wrap items-center gap-1">
                            {PERIOD_OPTIONS.map((opt) => (
                                <button
                                    key={opt.value}
                                    id={`map-period-${opt.value}`}
                                    onClick={() => setPeriod(opt.value)}
                                    className="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all"
                                    style={{
                                        background: filters.period === opt.value ? '#1D3557' : '#F8F9FA',
                                        color: filters.period === opt.value ? '#fff' : '#6B7A8D',
                                    }}
                                >
                                    {opt.label}
                                </button>
                            ))}
                        </div>

                        {/* Custom date inputs */}
                        {filters.period === 'custom' && (
                            <div className="flex flex-wrap items-center gap-2">
                                <div className="flex items-center gap-1.5">
                                    <span className="text-xs text-brand-muted">From</span>
                                    <input
                                        id="map-date-from"
                                        type="date"
                                        value={filters.date_from}
                                        onChange={(e) => navigate({ date_from: e.target.value })}
                                        className={inputClass}
                                        style={inputStyle}
                                    />
                                </div>
                                <div className="flex items-center gap-1.5">
                                    <span className="text-xs text-brand-muted">To</span>
                                    <input
                                        id="map-date-to"
                                        type="date"
                                        value={filters.date_to}
                                        min={filters.date_from || undefined}
                                        onChange={(e) => navigate({ date_to: e.target.value })}
                                        className={inputClass}
                                        style={inputStyle}
                                    />
                                </div>
                            </div>
                        )}

                        {/* Separator */}
                        <div className="h-5 w-px" style={{ background: 'rgba(43,45,66,0.12)' }} />

                        {/* Type dropdown */}
                        <div className="relative flex items-center">
                            <select
                                id="map-type-filter"
                                value={filters.incident_type}
                                onChange={(e) => navigate({ incident_type: e.target.value })}
                                className={selectClass}
                                style={inputStyle}
                            >
                                {TYPE_OPTIONS.map((opt) => (
                                    <option key={opt.value} value={opt.value}>
                                        {opt.label}
                                    </option>
                                ))}
                            </select>
                            <ChevronDown size={11} className="pointer-events-none absolute right-1.5 text-brand-muted" />
                        </div>

                        {/* Severity dropdown */}
                        <div className="relative flex items-center">
                            <select
                                id="map-severity-filter"
                                value={filters.severity_level}
                                onChange={(e) => navigate({ severity_level: e.target.value })}
                                className={selectClass}
                                style={inputStyle}
                            >
                                {SEVERITY_OPTIONS.map((opt) => (
                                    <option key={opt.value} value={opt.value}>
                                        {opt.label}
                                    </option>
                                ))}
                            </select>
                            <ChevronDown size={11} className="pointer-events-none absolute right-1.5 text-brand-muted" />
                        </div>

                        {/* Separator */}
                        <div className="h-5 w-px" style={{ background: 'rgba(43,45,66,0.12)' }} />

                        {/* Color-by toggle */}
                        <div className="flex items-center gap-1.5">
                            <span className="text-xs text-brand-muted">Color by:</span>
                            {(['severity', 'type'] as ColorBy[]).map((opt) => (
                                <button
                                    key={opt}
                                    id={`map-color-by-${opt}`}
                                    onClick={() => setColorBy(opt)}
                                    className="rounded-lg px-2.5 py-1.5 text-xs font-semibold capitalize transition-all"
                                    style={{
                                        background: colorBy === opt ? '#1D3557' : '#F8F9FA',
                                        color: colorBy === opt ? '#fff' : '#6B7A8D',
                                    }}
                                >
                                    {opt}
                                </button>
                            ))}
                        </div>
                    </div>
                </PortalCard>

                {/* ── Map + Sidebar ──────────────────────────────────────── */}
                <div className="flex gap-4" style={{ height: 'calc(100vh - 64px - 48px - 80px)' }}>
                    {/* Incident sidebar */}
                    {sidebarOpen && (
                        <PortalCard className="flex w-64 flex-shrink-0 flex-col overflow-hidden">
                            {/* Header */}
                            <div
                                className="flex items-center justify-between border-b px-4 py-3"
                                style={{ borderColor: 'rgba(43,45,66,0.08)' }}
                            >
                                <h3 className="text-sm font-bold text-brand-navy">Incidents</h3>
                                <span className="rounded-full bg-orange-100 px-2 py-0.5 text-xs font-semibold text-brand-orange">
                                    {incidents.length}
                                </span>
                            </div>

                            {/* Grouped counts */}
                            <div
                                className="flex flex-wrap items-center gap-x-3 gap-y-1 border-b px-4 py-2"
                                style={{ borderColor: 'rgba(43,45,66,0.06)' }}
                            >
                                {Object.entries(groupedCounts).map(([key, count]) => {
                                    const color =
                                        colorBy === 'severity'
                                            ? (SEVERITY_MARKER_COLORS[key as SeverityLevel] ?? FALLBACK_COLOR)
                                            : (TYPE_CFG[key as IncidentType]?.color ?? FALLBACK_COLOR);
                                    const label =
                                        colorBy === 'severity'
                                            ? (SEVERITY_CFG[key as SeverityLevel]?.label ?? key)
                                            : (TYPE_CFG[key as IncidentType]?.label ?? key);
                                    return (
                                        <div key={key} className="flex items-center gap-1">
                                            <span className="size-1.5 rounded-full" style={{ background: color }} />
                                            <span className="text-xs text-brand-muted">
                                                {count} {label}
                                            </span>
                                        </div>
                                    );
                                })}
                            </div>

                            {/* Incident list */}
                            <div className="flex-1 overflow-y-auto">
                                {incidents.length === 0 && (
                                    <div className="flex flex-col items-center gap-2 p-6 text-center">
                                        <p className="text-xs font-semibold text-brand-muted">No incidents found</p>
                                        <p className="text-xs text-brand-muted">
                                            Try adjusting the period or filters above.
                                        </p>
                                    </div>
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
                                                style={{ background: markerColor(inc, colorBy) }}
                                            />
                                            <div className="min-w-0 flex-1">
                                                <p className="font-mono text-xs font-medium text-brand-blue">
                                                    {inc.reference}
                                                </p>
                                                <p className="truncate text-xs font-semibold text-brand-navy">
                                                    {inc.typeLabel ?? 'Unclassified'}
                                                </p>
                                                <p className="text-xs text-brand-muted">
                                                    {inc.barangay ?? '—'}
                                                </p>
                                            </div>
                                            {inc.severity && <SeverityBadge severity={inc.severity} />}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </PortalCard>
                    )}

                    {/* Map card */}
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

                            {/* Result summary */}
                            <span className="text-sm text-brand-muted">{resultSummary}</span>

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
                                                ? riskByBarangayName.get(
                                                      resolveDbBarangayName(feature.properties.barangay),
                                                  )
                                                : undefined;
                                            const color = risk?.risk_level
                                                ? riskLevelColor(risk.risk_level)
                                                : '#6B7A8D';
                                            return { color, weight: 1, fillColor: color, fillOpacity: 0.28 };
                                        }}
                                        onEachFeature={(feature: BarangayFeature, layer) => {
                                            const risk = riskByBarangayName.get(
                                                resolveDbBarangayName(feature.properties.barangay),
                                            );
                                            const label = risk?.risk_level
                                                ? RISK_CFG[risk.risk_level].label
                                                : 'No risk data';
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
                                                fillColor: markerColor(inc, colorBy),
                                                fillOpacity: 0.9,
                                            }}
                                            eventHandlers={{ click: () => setSelectedId(inc.report_id) }}
                                        >
                                            <Popup>
                                                <div className="space-y-1">
                                                    <p className="font-mono text-xs font-semibold text-blue-700">
                                                        {inc.reference}
                                                    </p>
                                                    <p className="text-sm font-semibold">
                                                        {inc.typeLabel ?? 'Unclassified'}
                                                    </p>
                                                    {inc.severity && (
                                                        <p className="text-xs capitalize text-gray-600">
                                                            Severity:{' '}
                                                            <span style={{ color: SEVERITY_MARKER_COLORS[inc.severity] }}>
                                                                {inc.severity}
                                                            </span>
                                                        </p>
                                                    )}
                                                    <p className="text-xs text-gray-500">
                                                        {inc.barangay ?? '—'}
                                                    </p>
                                                    <p className="text-xs text-gray-400">{inc.dateTime}</p>
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

                            {/* Dynamic legend */}
                            {legendEntries.length > 0 && (
                                <div className="absolute bottom-3 left-3 z-[1000] space-y-1.5 rounded-lg bg-white/95 px-3 py-2 shadow">
                                    <p className="text-[10px] font-semibold uppercase tracking-wide text-brand-muted">
                                        {colorBy === 'severity' ? 'Severity' : 'Type'}
                                    </p>
                                    <div className="flex flex-col gap-1">
                                        {legendEntries.map((entry) => (
                                            <div key={entry.key} className="flex items-center gap-1.5">
                                                <span
                                                    className="size-2 rounded-full"
                                                    style={{ background: entry.color }}
                                                />
                                                <span className="text-xs text-brand-muted">{entry.label}</span>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}

                            {/* Empty state overlay */}
                            {incidents.length === 0 && (
                                <div className="pointer-events-none absolute inset-0 z-[999] flex items-center justify-center">
                                    <div className="rounded-xl bg-white/90 px-6 py-4 text-center shadow-lg">
                                        <p className="text-sm font-semibold text-brand-navy">
                                            No completed incidents found
                                        </p>
                                        <p className="mt-1 text-xs text-brand-muted">
                                            Try a different period, type, or severity.
                                        </p>
                                    </div>
                                </div>
                            )}
                        </div>
                    </PortalCard>
                </div>
            </div>
        </PortalLayout>
    );
}
