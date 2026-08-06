import { Link, router } from '@inertiajs/react';
import { Search } from 'lucide-react';
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

const STATUS_FILTERS: { label: string; value: string }[] = [
    { label: 'All', value: 'all' },
    { label: 'Pending', value: 'pending' },
    { label: 'Verified', value: 'verified' },
    { label: 'Dispatched', value: 'dispatched' },
    { label: 'Resolved', value: 'resolved' },
    { label: 'Rejected', value: 'rejected' },
];

export default function IncidentsIndex({
    reports,
    filters,
    totalCount,
}: {
    reports: Paginated<IncidentRow>;
    filters: { status: string; search: string };
    totalCount: number;
}) {
    const [search, setSearch] = useState(filters.search);
    const isFirstRender = useRef(true);

    // Debounce search so we're not firing a request on every keystroke.
    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }

        const timeout = setTimeout(() => {
            router.get(
                incidentsIndex().url,
                { status: filters.status, search },
                { preserveState: true, replace: true },
            );
        }, 400);

        return () => clearTimeout(timeout);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search]);

    const setStatus = (status: string) => {
        router.get(incidentsIndex().url, { status, search }, { preserveState: true, replace: true });
    };

    return (
        <PortalLayout title="Incident Reports" subtitle={`${totalCount} total reports — showing ${reports.total}`}>
            <div className="space-y-4">
                {/* Filter bar */}
                <PortalCard className="p-4">
                    <div className="flex flex-wrap items-center gap-3">
                        <div className="relative">
                            <Search size={13} className="absolute top-1/2 left-3 -translate-y-1/2 text-brand-muted" />
                            <input
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Search reporter, contact, barangay…"
                                className="w-56 rounded-lg border bg-brand-bg py-2 pr-4 pl-8 text-sm text-brand-navy outline-none"
                                style={{ borderColor: 'rgba(43,45,66,0.13)' }}
                            />
                        </div>
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
                                    {['Reference ID', 'Reporter', 'Barangay', 'Type', 'Severity', 'Status', 'Date / Time'].map(
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
                                        <td colSpan={7} className="px-4 py-8 text-center text-xs text-brand-muted">
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
                                        <td className="px-4 py-3 font-mono text-xs font-medium text-brand-blue">
                                            {r.reference}
                                        </td>
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
