import { Link, router, useForm, useRemember } from '@inertiajs/react';
import { ChevronDown, ChevronRight, LoaderCircle, X } from 'lucide-react';
import { useEffect, useState } from 'react';
import type { FormEvent, ReactNode } from 'react';
import {
    Bar,
    BarChart,
    CartesianGrid,
    Cell,
    Line,
    LineChart,
    Pie,
    PieChart,
    ResponsiveContainer,
    Sector,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import type { SectorProps } from 'recharts';
import { PortalCard } from '@/components/portal/portal-card';
import { Button } from '@/components/ui/button';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import PortalLayout from '@/layouts/portal-layout';
import { analytics } from '@/routes';
import { show as showIncident } from '@/routes/incidents';
import type { Paginated } from '@/types/pagination';

type Filters = {
    period: 'this_month' | 'last_3_months' | 'this_year' | 'custom';
    date_from: string;
    date_to: string;
    incident_type: string;
    barangay_id: number | null;
    supporting_view: 'barangay' | 'alarm';
    supporting_type: string;
    supporting_alarm_level: string;
};

type SupportingFilters = Pick<
    Filters,
    'supporting_type' | 'supporting_alarm_level'
>;

type BarangayRow = {
    barangay_id: number;
    barangay_name: string;
    count: number;
    leading_types: string[];
};

type SelectedBarangay = {
    barangay_id: number;
    barangay_name: string;
    count: number;
    injuries: number;
    fatalities: number;
    estimated_damage: number | null;
    damage_recorded: number;
    types: { type: string; label: string; count: number; color: string }[];
};

type SupportingIncident = {
    incident_id: number;
    report_id: number;
    reference: string;
    recorded_at: string;
    type: string;
    cause: string | null;
    alarm_level: string;
};

type AnalyticsProps = {
    summary: { total: number; unclassified: number; unassigned: number };
    barangayOverview: BarangayRow[];
    selectedBarangay: SelectedBarangay | null;
    supportingIncidents: Paginated<SupportingIncident> | null;
    monthlyTrend: { month: string; incidents: number }[];
    incidentsByAlarmLevel: {
        level: string;
        label: string;
        count: number;
        color: string;
    }[];
    typeOptions: { value: string; label: string }[];
    periodLabel: string;
    dateRange: { start: string; end: string };
    filters: Filters;
};

const number = new Intl.NumberFormat('en-PH');
const pesos = new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
});
const inputClass =
    'min-h-10 w-full rounded-lg border border-brand-navy/15 bg-brand-bg px-3 py-2 text-sm text-brand-navy focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy';
const chartTooltipStyle = {
    borderRadius: 8,
    border: '1px solid rgba(43,45,66,0.13)',
};

function AnalyticsFilters({
    filters,
    dateRange,
    typeOptions,
}: Pick<AnalyticsProps, 'filters' | 'dateRange' | 'typeOptions'>) {
    const { data, setData, get, transform, processing, errors } = useForm({
        period: filters.period,
        incident_type: filters.incident_type,
        date_from: filters.date_from || dateRange.start,
        date_to: filters.date_to || dateRange.end,
    });

    useEffect(() => {
        setData({
            period: filters.period,
            incident_type: filters.incident_type,
            date_from: filters.date_from || dateRange.start,
            date_to: filters.date_to || dateRange.end,
        });
    }, [
        filters.period,
        filters.incident_type,
        filters.date_from,
        filters.date_to,
        dateRange.start,
        dateRange.end,
        setData,
    ]);

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        transform((values) => ({
            ...values,
            date_from: values.period === 'custom' ? values.date_from : '',
            date_to: values.period === 'custom' ? values.date_to : '',
            barangay_id: filters.barangay_id,
        }));
        get(analytics().url, { preserveState: true, preserveScroll: true });
    };

    return (
        <PortalCard className="p-4 sm:p-5">
            <form onSubmit={submit} aria-label="Analytics filters">
                <div className="flex flex-wrap items-end gap-3">
                    <div className="min-w-40 flex-1 space-y-1.5 text-sm font-medium text-brand-navy">
                        <label className="block" htmlFor="analytics-period">
                            Period
                        </label>
                        <select
                            id="analytics-period"
                            className={inputClass}
                            value={data.period}
                            onChange={(event) =>
                                setData(
                                    'period',
                                    event.target.value as Filters['period'],
                                )
                            }
                        >
                            <option value="this_month">This Month</option>
                            <option value="last_3_months">Last 3 Months</option>
                            <option value="this_year">This Year</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div className="min-w-48 flex-1 space-y-1.5 text-sm font-medium text-brand-navy">
                        <label className="block" htmlFor="analytics-type">
                            Fire type
                        </label>
                        <select
                            id="analytics-type"
                            className={inputClass}
                            value={data.incident_type}
                            onChange={(event) =>
                                setData('incident_type', event.target.value)
                            }
                        >
                            <option value="">All Fire Types</option>
                            {typeOptions.map((type) => (
                                <option key={type.value} value={type.value}>
                                    {type.label}
                                </option>
                            ))}
                            <option value="unclassified">Unclassified</option>
                        </select>
                    </div>
                    {data.period === 'custom' && (
                        <>
                            <div className="min-w-40 flex-1 space-y-1.5 text-sm font-medium text-brand-navy">
                                <label
                                    className="block"
                                    htmlFor="analytics-from"
                                >
                                    From
                                </label>
                                <input
                                    id="analytics-from"
                                    type="date"
                                    required
                                    className={inputClass}
                                    value={data.date_from}
                                    onChange={(event) =>
                                        setData('date_from', event.target.value)
                                    }
                                    aria-invalid={Boolean(errors.date_from)}
                                    aria-describedby={
                                        errors.date_from
                                            ? 'analytics-filter-errors'
                                            : undefined
                                    }
                                />
                            </div>
                            <div className="min-w-40 flex-1 space-y-1.5 text-sm font-medium text-brand-navy">
                                <label className="block" htmlFor="analytics-to">
                                    To
                                </label>
                                <input
                                    id="analytics-to"
                                    type="date"
                                    required
                                    className={inputClass}
                                    value={data.date_to}
                                    onChange={(event) =>
                                        setData('date_to', event.target.value)
                                    }
                                    aria-invalid={Boolean(errors.date_to)}
                                    aria-describedby={
                                        errors.date_to
                                            ? 'analytics-filter-errors'
                                            : undefined
                                    }
                                />
                            </div>
                        </>
                    )}
                    <Button
                        type="submit"
                        variant="secondary"
                        disabled={processing}
                    >
                        {processing && (
                            <LoaderCircle
                                className="animate-spin"
                                aria-hidden="true"
                            />
                        )}
                        {processing ? 'Applying…' : 'Apply filters'}
                    </Button>
                </div>
                {Object.keys(errors).length > 0 && (
                    <div
                        id="analytics-filter-errors"
                        role="alert"
                        className="mt-3 space-y-1 text-sm text-brand-red"
                    >
                        {Object.entries(errors).map(([field, error]) => (
                            <p key={field}>{error}</p>
                        ))}
                    </div>
                )}
            </form>
        </PortalCard>
    );
}

function SupportingIncidentList({
    incidents,
    filters,
    typeOptions,
    alarmOptions,
    onChange,
}: {
    incidents: Paginated<SupportingIncident>;
    filters: Filters;
    typeOptions: AnalyticsProps['typeOptions'];
    alarmOptions: AnalyticsProps['incidentsByAlarmLevel'];
    onChange: (values: Partial<SupportingFilters>) => void;
}) {
    const resetScroll = () =>
        document
            .getElementById('supporting-incident-list')
            ?.scrollTo({ top: 0 });

    return (
        <section
            id="supporting-incidents"
            aria-label="Supporting incidents"
            className="space-y-3"
        >
            <div className="flex flex-wrap gap-3">
                <div className="min-w-40 flex-1 space-y-1.5">
                    <label
                        htmlFor="supporting-type"
                        className="block text-sm font-medium text-brand-navy"
                    >
                        Supporting fire type
                    </label>
                    <select
                        id="supporting-type"
                        className={inputClass}
                        value={filters.incident_type || filters.supporting_type}
                        onChange={(event) =>
                            onChange({ supporting_type: event.target.value })
                        }
                    >
                        <option value="">All Fire Types</option>
                        {typeOptions.map((type) => (
                            <option key={type.value} value={type.value}>
                                {type.label}
                            </option>
                        ))}
                        <option value="unclassified">Unclassified</option>
                    </select>
                </div>
                <div className="min-w-40 flex-1 space-y-1.5">
                    <label
                        htmlFor="supporting-alarm"
                        className="block text-sm font-medium text-brand-navy"
                    >
                        Supporting alarm level
                    </label>
                    <select
                        id="supporting-alarm"
                        className={inputClass}
                        value={filters.supporting_alarm_level}
                        onChange={(event) =>
                            onChange({
                                supporting_alarm_level: event.target.value,
                            })
                        }
                    >
                        <option value="">All Alarm Levels</option>
                        {alarmOptions.map((alarm) => (
                            <option key={alarm.level} value={alarm.level}>
                                {alarm.label}
                            </option>
                        ))}
                        <option value="unassessed">Awaiting assessment</option>
                    </select>
                </div>
            </div>
            <p className="text-sm text-brand-muted" aria-live="polite">
                {number.format(incidents.total)} matching incidents
            </p>
            <div
                id="supporting-incident-list"
                role="region"
                aria-label="Supporting incident records"
                tabIndex={0}
                scroll-region=""
                className="max-h-80 [scrollbar-gutter:stable] overflow-y-auto overscroll-contain"
            >
                {incidents.data.length === 0 ? (
                    <p className="py-3 text-sm text-brand-muted">
                        No incidents match these filters.
                    </p>
                ) : (
                    <ul className="divide-y divide-brand-navy/10">
                        {incidents.data.map((incident) => (
                            <li
                                key={incident.incident_id}
                                className="py-3 pr-2 first:pt-0"
                            >
                                <div className="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                                    <Link
                                        href={showIncident(incident.report_id, {
                                            query: { from: 'analytics' },
                                        })}
                                        className="font-medium text-brand-navy underline decoration-brand-navy/30 underline-offset-4"
                                    >
                                        {incident.reference}
                                    </Link>
                                    <span className="text-xs text-brand-muted">
                                        {incident.recorded_at}
                                    </span>
                                </div>
                                <p className="mt-1 text-sm text-brand-navy">
                                    {incident.type} · {incident.alarm_level}
                                </p>
                                <p className="mt-1 text-sm break-words text-brand-muted">
                                    Recorded cause ·{' '}
                                    {incident.cause?.trim() ||
                                        'Awaiting assessment'}
                                </p>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
            {incidents.last_page > 1 && (
                <nav
                    aria-label="Supporting incident pages"
                    className="flex flex-wrap items-center justify-between gap-3 border-t border-brand-navy/10 pt-3"
                >
                    <p className="text-xs text-brand-muted">
                        Page {number.format(incidents.current_page)} of{' '}
                        {number.format(incidents.last_page)}
                    </p>
                    <div className="flex items-center gap-3 text-sm">
                        {incidents.current_page > 1 && (
                            <Link
                                href={analytics({
                                    query: {
                                        ...filters,
                                        incident_page:
                                            incidents.current_page - 1,
                                    },
                                })}
                                preserveState
                                preserveScroll
                                onSuccess={resetScroll}
                                className="rounded px-1 py-2 text-brand-navy underline underline-offset-4"
                            >
                                Previous
                            </Link>
                        )}
                        {incidents.current_page < incidents.last_page && (
                            <Link
                                href={analytics({
                                    query: {
                                        ...filters,
                                        incident_page:
                                            incidents.current_page + 1,
                                    },
                                })}
                                preserveState
                                preserveScroll
                                onSuccess={resetScroll}
                                className="rounded px-1 py-2 text-brand-navy underline underline-offset-4"
                            >
                                Next
                            </Link>
                        )}
                    </div>
                </nav>
            )}
        </section>
    );
}

function IncidentTypeChart({
    types,
    selectedType,
    onSelect,
}: {
    types: SelectedBarangay['types'];
    selectedType: string;
    onSelect: (type: string) => void;
}) {
    const total = types.reduce((sum, type) => sum + type.count, 0);
    const selectedIndex = types.findIndex((type) => type.type === selectedType);
    const selected = types[selectedIndex];
    const percentage = (count: number) =>
        total > 0 ? `${Math.round((count / total) * 1000) / 10}%` : '0%';
    const selectType = (type: string) =>
        onSelect(type === selectedType ? '' : type);

    return (
        <div
            role="group"
            aria-labelledby="incident-type-chart-heading"
            className="space-y-3"
        >
            <div className="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3
                        id="incident-type-chart-heading"
                        className="text-sm font-semibold text-brand-navy"
                    >
                        Incident type breakdown
                    </h3>
                    <p className="mt-1 text-xs text-brand-muted">
                        Select a segment or type to filter supporting incidents.
                    </p>
                </div>
                {selectedType && (
                    <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => onSelect('')}
                    >
                        Clear type filter
                    </Button>
                )}
            </div>
            <div className="grid grid-cols-1 items-center gap-4 lg:grid-cols-2 lg:gap-6">
                <div className="min-w-0">
                    <div
                        className="relative mx-auto aspect-square w-full max-w-sm"
                        role="img"
                        aria-label={`Incident types. ${types.map((type) => `${type.label}: ${number.format(type.count)} (${percentage(type.count)})`).join(', ')}`}
                    >
                        <ResponsiveContainer width="100%" height="100%">
                            <PieChart>
                                <Pie
                                    data={types}
                                    dataKey="count"
                                    nameKey="label"
                                    cx="50%"
                                    cy="50%"
                                    innerRadius="55%"
                                    outerRadius="86%"
                                    startAngle={90}
                                    endAngle={-270}
                                    paddingAngle={2}
                                    stroke="none"
                                    isAnimationActive={false}
                                    rootTabIndex={-1}
                                    activeIndex={
                                        selectedIndex >= 0
                                            ? selectedIndex
                                            : undefined
                                    }
                                    activeShape={(props: unknown) => {
                                        const sector = props as SectorProps;

                                        return (
                                            <Sector
                                                {...sector}
                                                outerRadius={
                                                    (sector.outerRadius ?? 94) +
                                                    6
                                                }
                                            />
                                        );
                                    }}
                                    onClick={(entry) => selectType(entry.type)}
                                    className="cursor-pointer"
                                >
                                    {types.map((type) => (
                                        <Cell
                                            key={type.type}
                                            fill={type.color}
                                            fillOpacity={
                                                selectedType &&
                                                selectedType !== type.type
                                                    ? 0.25
                                                    : 1
                                            }
                                        />
                                    ))}
                                </Pie>
                                <Tooltip
                                    contentStyle={chartTooltipStyle}
                                    formatter={(value: number) => [
                                        `${number.format(value)} (${percentage(value)})`,
                                        'Incidents',
                                    ]}
                                />
                            </PieChart>
                        </ResponsiveContainer>
                        <div
                            className="pointer-events-none absolute inset-0 flex flex-col items-center justify-center text-center"
                            aria-hidden="true"
                        >
                            <span className="text-3xl font-semibold text-brand-navy tabular-nums sm:text-4xl">
                                {number.format(
                                    selectedType
                                        ? (selected?.count ?? 0)
                                        : total,
                                )}
                            </span>
                            <span className="mt-1 max-w-[44%] text-xs text-brand-muted sm:text-sm">
                                {selected?.label ||
                                    (selectedType
                                        ? 'Matching incidents'
                                        : 'All types')}
                            </span>
                        </div>
                    </div>
                </div>
                <div
                    className="w-full min-w-0 space-y-1"
                    aria-label="Incident type breakdown values"
                >
                    {types.map((type) => (
                        <button
                            key={type.type}
                            type="button"
                            onClick={() => selectType(type.type)}
                            aria-label={`View ${type.label} supporting incidents`}
                            aria-pressed={selectedType === type.type}
                            className="flex min-h-11 w-full items-center gap-2 rounded-lg px-2 py-2 text-left text-sm hover:bg-brand-green/5 focus-visible:outline-2 focus-visible:outline-brand-navy aria-pressed:bg-brand-green/10"
                        >
                            <span
                                className="size-2.5 shrink-0 rounded-full"
                                style={{ backgroundColor: type.color }}
                                aria-hidden="true"
                            />
                            <span className="min-w-0 flex-1 text-brand-navy">
                                {type.label}
                            </span>
                            <span className="shrink-0 text-right text-xs text-brand-muted tabular-nums">
                                {number.format(type.count)} (
                                {percentage(type.count)})
                            </span>
                        </button>
                    ))}
                </div>
            </div>
        </div>
    );
}

function BarangayDetails({
    barangay,
    incidents,
    filters,
    typeLabel,
    supportingRecords,
    onShowSupporting,
}: {
    barangay: SelectedBarangay;
    incidents: Paginated<SupportingIncident>;
    filters: Filters;
    typeLabel: string | undefined;
    supportingRecords: ReactNode;
    onShowSupporting: (
        values: Partial<SupportingFilters>,
        scrollToRecords?: boolean,
    ) => void;
}) {
    const [showIncidents, setShowIncidents] = useRemember(
        incidents.current_page > 1,
        `Analytics:incidents:${barangay.barangay_id}`,
    );
    const showingIncidents =
        showIncidents && filters.supporting_view === 'barangay';

    return (
        <PortalCard>
            <section aria-labelledby="barangay-detail-heading">
                <div className="flex items-start justify-between gap-3 border-b border-brand-navy/10 px-4 py-4 sm:px-5">
                    <div className="min-w-0">
                        <h2
                            id="barangay-detail-heading"
                            className="text-lg font-semibold break-words text-brand-navy"
                        >
                            {barangay.barangay_name}
                        </h2>
                        <p className="mt-1 text-sm text-brand-muted">
                            {number.format(barangay.count)}{' '}
                            {typeLabel
                                ? `${typeLabel.toLowerCase()} incidents`
                                : 'recorded incidents'}
                        </p>
                    </div>
                </div>
                <div className="space-y-5 p-4 sm:p-5">
                    {barangay.types.length > 0 && (
                        <IncidentTypeChart
                            types={barangay.types}
                            selectedType={filters.incident_type}
                            onSelect={(type) => {
                                setShowIncidents(true);
                                onShowSupporting(
                                    {
                                        supporting_type: type,
                                        supporting_alarm_level: '',
                                    },
                                    false,
                                );
                            }}
                        />
                    )}
                    {barangay.count === 0 && (
                        <p className="text-sm text-brand-muted">
                            No matching incident records for this barangay.
                        </p>
                    )}
                    <dl className="flex flex-wrap gap-x-10 gap-y-4 border-t border-brand-navy/10 pt-4">
                        <div>
                            <dt className="text-sm text-brand-muted">
                                Recorded injuries
                            </dt>
                            <dd className="mt-1 font-semibold text-brand-navy tabular-nums">
                                {number.format(barangay.injuries)}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-sm text-brand-muted">
                                Recorded fatalities
                            </dt>
                            <dd className="mt-1 font-semibold text-brand-navy tabular-nums">
                                {number.format(barangay.fatalities)}
                            </dd>
                        </div>
                        <div className="min-w-0">
                            <dt className="text-sm text-brand-muted">
                                Recorded estimated damage
                            </dt>
                            <dd className="mt-1 font-semibold break-words text-brand-navy tabular-nums">
                                {barangay.estimated_damage === null
                                    ? 'No damage recorded'
                                    : pesos.format(barangay.estimated_damage)}
                            </dd>
                            <dd className="mt-1 text-xs text-brand-muted">
                                Damage recorded for{' '}
                                {number.format(barangay.damage_recorded)} of{' '}
                                {number.format(barangay.count)} incidents
                            </dd>
                        </div>
                    </dl>
                    <Collapsible
                        open={showingIncidents}
                        onOpenChange={(open) => {
                            setShowIncidents(open);

                            if (
                                open &&
                                filters.supporting_view !== 'barangay'
                            ) {
                                onShowSupporting({});
                            }
                        }}
                    >
                        <CollapsibleTrigger asChild>
                            <Button variant="outline" size="sm">
                                {showingIncidents ? (
                                    <ChevronDown aria-hidden="true" />
                                ) : (
                                    <ChevronRight aria-hidden="true" />
                                )}
                                {showingIncidents
                                    ? 'Hide supporting incidents'
                                    : 'View supporting incidents'}
                            </Button>
                        </CollapsibleTrigger>
                        <CollapsibleContent className="analytics-collapse">
                            <div className="pt-4">
                                {filters.supporting_view === 'barangay' &&
                                    supportingRecords}
                            </div>
                        </CollapsibleContent>
                    </Collapsible>
                </div>
            </section>
        </PortalCard>
    );
}

export default function Analytics({
    summary,
    barangayOverview,
    selectedBarangay,
    supportingIncidents,
    monthlyTrend,
    incidentsByAlarmLevel,
    typeOptions,
    periodLabel,
    dateRange,
    filters,
}: AnalyticsProps) {
    const [alarmOpen, setAlarmOpen] = useRemember(
        filters.supporting_view === 'alarm',
        'Analytics:alarm',
    );
    const [showAlarmIncidents, setShowAlarmIncidents] = useRemember(
        filters.supporting_view === 'alarm',
        'Analytics:alarmIncidents',
    );
    const [selecting, setSelecting] = useState(false);
    const typeLabel =
        typeOptions.find((type) => type.value === filters.incident_type)
            ?.label ||
        (filters.incident_type === 'unclassified' ? 'Unclassified' : undefined);
    const zeroIncidentBarangays = barangayOverview.filter(
        (row) => row.count === 0,
    ).length;
    const maximum = Math.max(...barangayOverview.map((row) => row.count), 1);
    const alarmCount = incidentsByAlarmLevel.reduce(
        (sum, row) => sum + row.count,
        0,
    );
    const contextLabel = [
        selectedBarangay?.barangay_name || 'All barangays',
        typeLabel || 'All fire types',
    ].join(' · ');

    const selectedAlarmLabel =
        filters.supporting_alarm_level === 'unassessed'
            ? 'Awaiting assessment'
            : incidentsByAlarmLevel.find(
                  (level) => level.level === filters.supporting_alarm_level,
              )?.label;
    const trendContextLabel = [contextLabel, selectedAlarmLabel]
        .filter(Boolean)
        .join(' · ');

    const selectBarangay = (id: number | null) => {
        router.get(
            analytics().url,
            { ...filters, barangay_id: id },
            {
                preserveState: true,
                preserveScroll: true,
                onStart: () => setSelecting(true),
                onFinish: () => setSelecting(false),
            },
        );
    };

    const showSupporting = (
        view: Filters['supporting_view'],
        values: Partial<SupportingFilters> = {},
        scrollToRecords = true,
    ) => {
        if (view === 'alarm') {
            setAlarmOpen(true);
            setShowAlarmIncidents(true);
        }

        router.get(
            analytics().url,
            {
                ...filters,
                supporting_view: view,
                incident_type: values.supporting_type ?? filters.incident_type,
                supporting_type: '',
                supporting_alarm_level:
                    values.supporting_type !== undefined
                        ? ''
                        : (values.supporting_alarm_level ??
                          filters.supporting_alarm_level),
            },
            {
                preserveState: true,
                preserveScroll: true,
                onStart: () => setSelecting(true),
                onFinish: () => setSelecting(false),
                onSuccess: () =>
                    requestAnimationFrame(() => {
                        document
                            .getElementById('supporting-incident-list')
                            ?.scrollTo({ top: 0 });

                        if (scrollToRecords) {
                            document
                                .getElementById('supporting-incidents')
                                ?.scrollIntoView({
                                    block: 'nearest',
                                    behavior: window.matchMedia(
                                        '(prefers-reduced-motion: reduce)',
                                    ).matches
                                        ? 'auto'
                                        : 'smooth',
                                });
                        }
                    }),
            },
        );
    };

    const supportingRecords = supportingIncidents && (
        <SupportingIncidentList
            incidents={supportingIncidents}
            filters={filters}
            typeOptions={typeOptions}
            alarmOptions={incidentsByAlarmLevel}
            onChange={(values) =>
                showSupporting(filters.supporting_view, values)
            }
        />
    );

    return (
        <PortalLayout title="Analytics">
            <div className="min-w-0 space-y-4" aria-busy={selecting}>
                <div>
                    <h1 className="text-2xl font-bold text-brand-navy">
                        Analytics
                    </h1>
                    <p className="mt-1 text-sm text-brand-muted">
                        Recorded incident date · {periodLabel}
                    </p>
                </div>
                <AnalyticsFilters
                    filters={filters}
                    dateRange={dateRange}
                    typeOptions={typeOptions}
                />
                <p className="px-1 text-sm text-brand-muted" aria-live="polite">
                    <span className="font-semibold text-brand-navy tabular-nums">
                        {number.format(summary.total)}
                    </span>{' '}
                    {typeLabel
                        ? `${typeLabel.toLowerCase()} incidents`
                        : 'recorded incidents'}{' '}
                    across all barangays
                    {!filters.incident_type && (
                        <>
                            {' '}
                            · {number.format(summary.unclassified)} awaiting
                            classification
                        </>
                    )}
                </p>
                <PortalCard>
                    <section aria-labelledby="barangay-overview-heading">
                        <div className="flex flex-wrap items-center justify-between gap-3 border-b border-brand-navy/10 px-4 py-4 sm:px-5">
                            <div className="min-w-0 flex-1 basis-48">
                                <h2
                                    id="barangay-overview-heading"
                                    className="font-semibold text-brand-navy"
                                >
                                    Barangay overview
                                </h2>
                                <p className="mt-1 text-xs text-brand-muted">
                                    All {number.format(barangayOverview.length)}{' '}
                                    barangays ·{' '}
                                    {number.format(zeroIncidentBarangays)} with
                                    no{' '}
                                    {filters.incident_type
                                        ? 'matching'
                                        : 'recorded'}{' '}
                                    incidents
                                </p>
                            </div>
                            {selectedBarangay && (
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    disabled={selecting}
                                    onClick={() => selectBarangay(null)}
                                >
                                    <X aria-hidden="true" /> Deselect barangay
                                </Button>
                            )}
                            {selecting && (
                                <span
                                    role="status"
                                    className="text-sm text-brand-muted"
                                >
                                    Loading…
                                </span>
                            )}
                        </div>
                        <div
                            className="max-h-[28rem] [scrollbar-gutter:stable] overflow-y-auto overscroll-contain"
                            role="region"
                            aria-label="Barangay comparison"
                            tabIndex={0}
                            scroll-region=""
                        >
                            <table className="w-full table-fixed text-left text-sm">
                                <caption className="sr-only">
                                    Recorded incidents by barangay for{' '}
                                    {periodLabel}
                                    {typeLabel && `, ${typeLabel}`}. Select a
                                    barangay to view its details.
                                </caption>
                                <thead className="sticky top-0 z-10 bg-brand-bg text-xs text-brand-muted">
                                    <tr className="border-b border-brand-navy/10">
                                        <th
                                            scope="col"
                                            className="w-2/3 px-4 py-3 font-medium sm:w-1/3 sm:px-5"
                                        >
                                            Barangay
                                        </th>
                                        <th
                                            scope="col"
                                            className="px-2 py-3 font-medium"
                                        >
                                            Incidents
                                        </th>
                                        {!filters.incident_type && (
                                            <th
                                                scope="col"
                                                className="hidden px-3 py-3 font-medium sm:table-cell sm:px-5"
                                            >
                                                Most frequent type
                                            </th>
                                        )}
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-brand-navy/10">
                                    {barangayOverview.map((row) => (
                                        <tr
                                            key={row.barangay_id}
                                            className={
                                                row.barangay_id ===
                                                filters.barangay_id
                                                    ? 'bg-brand-green/10'
                                                    : ''
                                            }
                                        >
                                            <th
                                                scope="row"
                                                className="px-4 py-1 text-left font-medium sm:px-5"
                                            >
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        selectBarangay(
                                                            row.barangay_id ===
                                                                filters.barangay_id
                                                                ? null
                                                                : row.barangay_id,
                                                        )
                                                    }
                                                    disabled={selecting}
                                                    aria-expanded={
                                                        row.barangay_id ===
                                                        filters.barangay_id
                                                    }
                                                    aria-controls={
                                                        selectedBarangay
                                                            ? 'barangay-details'
                                                            : undefined
                                                    }
                                                    className="min-h-11 w-full rounded py-2 text-left break-words text-brand-navy underline decoration-brand-navy/20 underline-offset-4 hover:decoration-brand-navy focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy"
                                                >
                                                    {row.barangay_name}
                                                </button>
                                                {!filters.incident_type && (
                                                    <p className="pb-3 text-xs font-normal text-brand-muted sm:hidden">
                                                        Most frequent ·{' '}
                                                        {row.leading_types
                                                            .length > 1 &&
                                                            'Tied · '}
                                                        {row.leading_types.join(
                                                            ', ',
                                                        ) ||
                                                            (row.count > 0
                                                                ? 'Awaiting classification'
                                                                : 'No recorded incidents')}
                                                    </p>
                                                )}
                                            </th>
                                            <td className="px-2 py-3">
                                                <div className="grid grid-cols-[4ch_minmax(0,1fr)] items-center gap-2 sm:gap-3">
                                                    <span className="text-left font-medium text-brand-navy tabular-nums">
                                                        {number.format(
                                                            row.count,
                                                        )}
                                                    </span>
                                                    <div
                                                        aria-hidden="true"
                                                        className="h-1.5 min-w-0 overflow-hidden rounded-full bg-brand-navy/5"
                                                    >
                                                        <div
                                                            className="h-full rounded-full bg-brand-green transition-[width] duration-300 motion-reduce:transition-none"
                                                            style={{
                                                                width: `${(row.count / maximum) * 100}%`,
                                                            }}
                                                        />
                                                    </div>
                                                </div>
                                            </td>
                                            {!filters.incident_type && (
                                                <td className="hidden px-3 py-3 break-words text-brand-muted sm:table-cell sm:px-5">
                                                    {row.leading_types.length >
                                                        1 && 'Tied · '}
                                                    {row.leading_types.join(
                                                        ', ',
                                                    ) ||
                                                        (row.count > 0
                                                            ? 'Awaiting classification'
                                                            : 'No recorded incidents')}
                                                </td>
                                            )}
                                        </tr>
                                    ))}
                                    {barangayOverview.length === 0 && (
                                        <tr>
                                            <td
                                                colSpan={
                                                    filters.incident_type
                                                        ? 2
                                                        : 3
                                                }
                                                className="px-5 py-8 text-center text-brand-muted"
                                            >
                                                No barangays available.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                        <div className="flex flex-wrap items-center justify-between gap-3 border-t border-brand-navy/10 px-4 py-3 sm:px-5">
                            <p className="text-xs text-brand-muted">
                                {number.format(summary.unassigned)} matching
                                incidents without an assigned barangay
                            </p>
                        </div>
                    </section>
                </PortalCard>
                {selectedBarangay && supportingIncidents && (
                    <div id="barangay-details">
                        <BarangayDetails
                            key={selectedBarangay.barangay_id}
                            barangay={selectedBarangay}
                            incidents={supportingIncidents}
                            filters={filters}
                            typeLabel={typeLabel}
                            supportingRecords={supportingRecords}
                            onShowSupporting={(values, scrollToRecords) =>
                                showSupporting(
                                    'barangay',
                                    values,
                                    scrollToRecords,
                                )
                            }
                        />
                    </div>
                )}
                <PortalCard>
                    <section aria-labelledby="monthly-trend-heading">
                        <div className="border-b border-brand-navy/10 px-4 py-4 sm:px-5">
                            <h2
                                id="monthly-trend-heading"
                                className="font-semibold text-brand-navy"
                            >
                                Monthly incident trend
                            </h2>
                            <p className="mt-1 text-sm text-brand-muted">
                                {trendContextLabel}
                            </p>
                        </div>
                        <div
                            className="p-3 sm:p-5"
                            role="img"
                            aria-label={`Monthly recorded incidents. ${trendContextLabel}. ${monthlyTrend.map((point) => `${point.month}: ${point.incidents}`).join(', ')}`}
                        >
                            <ResponsiveContainer width="100%" height={260}>
                                <LineChart
                                    data={monthlyTrend}
                                    margin={{
                                        top: 12,
                                        right: 18,
                                        bottom: 8,
                                        left: 0,
                                    }}
                                >
                                    <CartesianGrid
                                        strokeDasharray="3 3"
                                        stroke="rgba(43,45,66,0.07)"
                                        vertical={false}
                                    />
                                    <XAxis
                                        dataKey="month"
                                        tick={{ fontSize: 12, fill: '#6B7A8D' }}
                                        tickLine={false}
                                        axisLine={false}
                                        minTickGap={24}
                                    />
                                    <YAxis
                                        allowDecimals={false}
                                        tick={{ fontSize: 12, fill: '#6B7A8D' }}
                                        tickLine={false}
                                        axisLine={false}
                                        width={35}
                                    />
                                    <Tooltip
                                        contentStyle={chartTooltipStyle}
                                        formatter={(value: number) => [
                                            number.format(value),
                                            'Incidents',
                                        ]}
                                    />
                                    <Line
                                        type="linear"
                                        dataKey="incidents"
                                        name="Incidents"
                                        stroke="#2A9D8F"
                                        strokeWidth={2.5}
                                        dot={{ r: 3 }}
                                        activeDot={{ r: 5 }}
                                        isAnimationActive={false}
                                    />
                                </LineChart>
                            </ResponsiveContainer>
                        </div>
                    </section>
                </PortalCard>
                <PortalCard>
                    <Collapsible open={alarmOpen} onOpenChange={setAlarmOpen}>
                        <CollapsibleTrigger className="group flex min-h-12 w-full items-center justify-between gap-3 px-4 py-4 text-left focus-visible:outline-2 focus-visible:outline-offset-[-3px] focus-visible:outline-brand-navy sm:px-5">
                            <span>
                                <span className="block font-semibold text-brand-navy">
                                    Alarm-level distribution
                                </span>
                                <span className="mt-1 block text-sm text-brand-muted">
                                    {number.format(alarmCount)} incidents with a
                                    recorded alarm level · {contextLabel}
                                </span>
                            </span>
                            <ChevronDown
                                size={18}
                                className="shrink-0 text-brand-muted transition-transform duration-200 group-data-[state=open]:rotate-180 motion-reduce:transition-none"
                                aria-hidden="true"
                            />
                        </CollapsibleTrigger>
                        <CollapsibleContent className="analytics-collapse">
                            {filters.supporting_alarm_level && (
                                <div className="flex flex-wrap items-center justify-between gap-2 px-4 pt-4 sm:px-5">
                                    <p className="text-sm text-brand-navy">
                                        Selected alarm · {selectedAlarmLabel}
                                    </p>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() =>
                                            showSupporting('alarm', {
                                                supporting_alarm_level: '',
                                            })
                                        }
                                    >
                                        Clear alarm filter
                                    </Button>
                                </div>
                            )}
                            <div
                                className="overflow-x-auto border-t border-brand-navy/10 p-4 sm:p-5"
                                role="img"
                                aria-label={`Alarm-level distribution. ${contextLabel}. ${incidentsByAlarmLevel.map((row) => `${row.label}: ${row.count}`).join(', ')}`}
                            >
                                <div className="min-w-[520px]">
                                    <ResponsiveContainer
                                        width="100%"
                                        height={260}
                                    >
                                        <BarChart
                                            data={incidentsByAlarmLevel}
                                            margin={{
                                                top: 10,
                                                right: 10,
                                                bottom: 25,
                                                left: 0,
                                            }}
                                        >
                                            <CartesianGrid
                                                strokeDasharray="3 3"
                                                stroke="rgba(43,45,66,0.07)"
                                                vertical={false}
                                            />
                                            <XAxis
                                                dataKey="label"
                                                tick={{
                                                    fontSize: 11,
                                                    fill: '#6B7A8D',
                                                }}
                                                tickLine={false}
                                                axisLine={false}
                                                angle={-25}
                                                textAnchor="end"
                                                interval={0}
                                                height={50}
                                            />
                                            <YAxis
                                                allowDecimals={false}
                                                tick={{
                                                    fontSize: 12,
                                                    fill: '#6B7A8D',
                                                }}
                                                tickLine={false}
                                                axisLine={false}
                                                width={35}
                                            />
                                            <Tooltip
                                                contentStyle={chartTooltipStyle}
                                            />
                                            <Bar
                                                dataKey="count"
                                                name="Incidents"
                                                maxBarSize={32}
                                                radius={[3, 3, 0, 0]}
                                                isAnimationActive={false}
                                                className="cursor-pointer"
                                                onClick={(entry) =>
                                                    showSupporting('alarm', {
                                                        supporting_alarm_level:
                                                            filters.supporting_alarm_level ===
                                                            entry.level
                                                                ? ''
                                                                : entry.level,
                                                    })
                                                }
                                            >
                                                {incidentsByAlarmLevel.map(
                                                    (row) => (
                                                        <Cell
                                                            key={row.level}
                                                            fill={
                                                                filters.supporting_alarm_level ===
                                                                row.level
                                                                    ? '#1D3557'
                                                                    : row.color
                                                            }
                                                            fillOpacity={
                                                                filters.supporting_alarm_level &&
                                                                filters.supporting_alarm_level !==
                                                                    row.level
                                                                    ? 0.25
                                                                    : 1
                                                            }
                                                        />
                                                    ),
                                                )}
                                            </Bar>
                                        </BarChart>
                                    </ResponsiveContainer>
                                </div>
                            </div>
                            <div className="px-4 pb-4 sm:px-5 sm:pb-5">
                                <Collapsible
                                    open={
                                        showAlarmIncidents &&
                                        filters.supporting_view === 'alarm'
                                    }
                                    onOpenChange={(open) => {
                                        setShowAlarmIncidents(open);

                                        if (
                                            open &&
                                            filters.supporting_view !== 'alarm'
                                        ) {
                                            showSupporting('alarm');
                                        }
                                    }}
                                >
                                    <CollapsibleTrigger asChild>
                                        <Button variant="outline" size="sm">
                                            {showAlarmIncidents &&
                                            filters.supporting_view === 'alarm'
                                                ? 'Hide alarm incidents'
                                                : 'View alarm incidents'}
                                        </Button>
                                    </CollapsibleTrigger>
                                    <CollapsibleContent className="analytics-collapse">
                                        <div className="pt-4">
                                            {filters.supporting_view ===
                                                'alarm' && supportingRecords}
                                        </div>
                                    </CollapsibleContent>
                                </Collapsible>
                            </div>
                        </CollapsibleContent>
                    </Collapsible>
                </PortalCard>
            </div>
        </PortalLayout>
    );
}
