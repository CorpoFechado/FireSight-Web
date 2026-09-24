import { Link, router } from '@inertiajs/react';
import { Search, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { PaginationBar } from '@/components/portal/pagination-bar';
import { PortalCard } from '@/components/portal/portal-card';
import { SeverityBadge, StatusBadge } from '@/components/portal/status-badge';
import PortalLayout from '@/layouts/portal-layout';
import type { ReportStatus, SeverityLevel } from '@/lib/fire-status';
import { index as incidentsIndex, show as showIncident } from '@/routes/incidents';
import type { Paginated } from '@/types/pagination';

type IncidentRow = {
    report_id: number;
    reference: string;
    reporter_name: string;
    contact_number: string;
    barangay: string | null;
    type: string | null;
    severity: SeverityLevel | null;
    status: ReportStatus;
    dateTime: string;
};

type Filters = {
    status: string;
    search: string;
    date_from: string;
    date_to: string;
    barangay_id: string;
    incident_type: string;
    severity_level: string;
};

type BarangayOption = {
    barangay_id: number;
    barangay_name: string;
};

const STATUS_FILTERS: { label: string; value: string }[] = [
    { label: 'All', value: 'all' },
    { label: 'Pending', value: 'pending' },
    { label: 'Accepted', value: 'accepted' },
    { label: 'Dispatched', value: 'dispatched' },
    { label: 'Resolved', value: 'resolved' },
    { label: 'Invalid', value: 'invalid' },
];

const TYPE_OPTIONS: { label: string; value: string }[] = [
    { label: 'Residential Fire', value: 'residential_fire' },
    { label: 'Commercial Fire', value: 'commercial_fire' },
    { label: 'Vehicular Fire', value: 'vehicular_fire' },
    { label: 'Storage Fire', value: 'storage_fire' },
    { label: 'Rubbish Fire', value: 'rubbish_fire' },
    { label: 'Others', value: 'others' },
];

const SEVERITY_OPTIONS: { label: string; value: string }[] = [
    { label: 'Critical', value: 'critical' },
    { label: 'High', value: 'high' },
    { label: 'Moderate', value: 'moderate' },
    { label: 'Low', value: 'low' },
];

const inputClass =
    'rounded-lg border bg-brand-bg py-2 px-3 text-sm text-brand-navy outline-none focus:ring-1 focus:ring-brand-navy/20';
const inputStyle = { borderColor: 'rgba(43,45,66,0.13)' };

export default function IncidentsIndex({
    reports,
    filters,
    barangays,
    totalCount,
}: {
    reports: Paginated<IncidentRow>;
    filters: Filters;
    barangays: BarangayOption[];
    totalCount: number;
}) {
    const [search, setSearch] = useState(filters.search);
    const isFirstRender = useRef(true);

    const navigate = (overrides: Partial<Filters>) => {
        const params = {
            status: filters.status,
            search,
            date_from: filters.date_from,
            date_to: filters.date_to,
            barangay_id: filters.barangay_id,
            incident_type: filters.incident_type,
            severity_level: filters.severity_level,
            ...overrides,
        };
        router.get(incidentsIndex().url, params, { preserveState: true, replace: true });
    };

    // Debounce search so we're not firing a request on every keystroke.
    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }

        const timeout = setTimeout(() => {
            navigate({ search });
        }, 400);

        return () => clearTimeout(timeout);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search]);

    const setStatus = (status: string) => navigate({ status });

    const hasSecondaryFilters =
        filters.date_from !== '' ||
        filters.date_to !== '' ||
        filters.barangay_id !== '' ||
        filters.incident_type !== '' ||
        filters.severity_level !== '';

    const clearSecondaryFilters = () =>
        navigate({ date_from: '', date_to: '', barangay_id: '', incident_type: '', severity_level: '' });

    return (
        <PortalLayout title="Incident Reports" subtitle={`${totalCount} total reports — showing ${reports.total}`}>
            <div className="space-y-4">
                {/* Filter bar */}
                <PortalCard className="p-4">
                    <div className="flex flex-wrap items-center gap-3">
                        {/* Text search */}
                        <div className="relative">
                            <Search size={13} className="absolute top-1/2 left-3 -translate-y-1/2 text-brand-muted" />
                            <input
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Search reporter, contact, barangay…"
                                className="w-56 rounded-lg border bg-brand-bg py-2 pr-4 pl-8 text-sm text-brand-navy outline-none"
                                style={inputStyle}
                            />
                        </div>

                        {/* Date range */}
                        <div className="flex items-center gap-1.5">
                            <span className="text-xs text-brand-muted">From</span>
                            <input
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
                                type="date"
                                value={filters.date_to}
                                min={filters.date_from || undefined}
                                onChange={(e) => navigate({ date_to: e.target.value })}
                                className={inputClass}
                                style={inputStyle}
                            />
                        </div>

                        {/* Barangay dropdown */}
                        <select
                            value={filters.barangay_id}
                            onChange={(e) => navigate({ barangay_id: e.target.value })}
                            className={`${inputClass} pr-8`}
                            style={inputStyle}
                        >
                            <option value="">All Barangays</option>
                            {barangays.map((b) => (
                                <option key={b.barangay_id} value={String(b.barangay_id)}>
                                    {b.barangay_name}
                                </option>
                            ))}
                        </select>

                        {/* Type dropdown */}
                        <select
                            value={filters.incident_type}
                            onChange={(e) => navigate({ incident_type: e.target.value })}
                            className={`${inputClass} pr-8`}
                            style={inputStyle}
                        >
                            <option value="">All Types</option>
                            {TYPE_OPTIONS.map((t) => (
                                <option key={t.value} value={t.value}>
                                    {t.label}
                                </option>
                            ))}
                        </select>

                        {/* Severity dropdown */}
                        <select
                            value={filters.severity_level}
                            onChange={(e) => navigate({ severity_level: e.target.value })}
                            className={`${inputClass} pr-8`}
                            style={inputStyle}
                        >
                            <option value="">All Severities</option>
                            {SEVERITY_OPTIONS.map((s) => (
                                <option key={s.value} value={s.value}>
                                    {s.label}
                                </option>
                            ))}
                        </select>

                        {/* Clear secondary filters */}
                        {hasSecondaryFilters && (
                            <button
                                onClick={clearSecondaryFilters}
                                className="flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-medium text-brand-muted transition-colors hover:bg-red-50 hover:text-red-500"
                                style={{ border: '1px solid rgba(43,45,66,0.13)' }}
                            >
                                <X size={11} />
                                Clear filters
                            </button>
                        )}

                        {/* Status tabs */}
                        <div className="flex flex-wrap items-center gap-1">
                            {STATUS_FILTERS.map((s) => (
                                <button
                                    key={s.value}
                                    onClick={() => setStatus(s.value)}
                                    className="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all"
                                    style={{
                                        background: filters.status === s.value ? '#1D3557' : '#F8F9FA',
                                        color: filters.status === s.value ? '#fff' : '#6B7A8D',
                                    }}
                                >
                                    {s.label}
                                </button>
                            ))}
                        </div>
                    </div>
                </PortalCard>

                {/* Table */}
                <PortalCard>
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead>
                                <tr style={{ background: '#FAFBFC', borderBottom: '1px solid rgba(43,45,66,0.08)' }}>
                                    {['Reporter', 'Barangay', 'Type', 'Severity', 'Status', 'Date / Time'].map(
                                        (h) => (
                                            <th
                                                key={h}
                                                className="px-4 py-3 text-left text-xs font-semibold tracking-wider text-brand-muted uppercase"
                                            >
                                                {h}
                                            </th>
                                        ),
                                    )}
                                </tr>
                            </thead>
                            <tbody>
                                {reports.data.length === 0 && (
                                    <tr>
                                        <td colSpan={6} className="px-4 py-8 text-center text-xs text-brand-muted">
                                            No reports match these filters.
                                        </td>
                                    </tr>
                                )}
                                {reports.data.map((r) => (
                                    <Link
                                        key={r.report_id}
                                        href={showIncident(r.report_id)}
                                        as="tr"
                                        className="cursor-pointer border-t transition-colors hover:bg-blue-50"
                                        style={{ borderColor: 'rgba(43,45,66,0.06)' }}
                                    >
                                        <td className="px-4 py-3">
                                            <p className="text-xs font-medium text-brand-navy">{r.reporter_name}</p>
                                            <p className="text-xs text-brand-muted">{r.contact_number}</p>
                                        </td>
                                        <td className="px-4 py-3 text-xs text-brand-navy">{r.barangay ?? '—'}</td>
                                        <td className="px-4 py-3 text-xs text-brand-navy">{r.type ?? '—'}</td>
                                        <td className="px-4 py-3">
                                            {r.severity ? <SeverityBadge severity={r.severity} /> : <span className="text-xs text-brand-muted">—</span>}
                                        </td>
                                        <td className="px-4 py-3">
                                            <StatusBadge status={r.status} />
                                        </td>
                                        <td className="px-4 py-3 font-mono text-xs text-brand-muted">{r.dateTime}</td>
                                    </Link>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <PaginationBar paginated={reports} />
                </PortalCard>
            </div>
        </PortalLayout>
    );
}
