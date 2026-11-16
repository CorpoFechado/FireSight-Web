import { router } from '@inertiajs/react';
import { Pencil, Plus, Search, Trash2, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { DeletePersonnelDialog } from '@/components/personnel/delete-personnel-dialog';
import { PersonnelFormModal } from '@/components/personnel/personnel-form-modal';
import type { PersonnelRow } from '@/components/personnel/personnel-form-modal';
import { PortalCard } from '@/components/portal/portal-card';
import {
    PersonnelStatusBadge,
    RoleBadge,
} from '@/components/portal/status-badge';
import { useInitials } from '@/hooks/use-initials';
import PortalLayout from '@/layouts/portal-layout';
import { groupLabel } from '@/lib/duty-schedule';
import { personnel as personnelIndex } from '@/routes';

const ROLE_FILTERS = [
    { label: 'All Roles', value: 'all' },
    { label: 'Admin', value: 'bfp_admin' },
    { label: 'Personnel', value: 'bfp_personnel' },
];

const STATUS_FILTERS = [
    { label: 'All Statuses', value: 'all' },
    { label: 'Active', value: 'active' },
    { label: 'Inactive', value: 'inactive' },
];

const inputClass =
    'rounded-lg border bg-brand-bg py-2 px-3 text-sm text-brand-navy outline-none focus:ring-1 focus:ring-brand-navy/20';
const inputStyle = { borderColor: 'rgba(43,45,66,0.13)' };

export default function PersonnelIndex({
    personnel,
    filters,
    totalCount,
}: {
    personnel: PersonnelRow[];
    filters: { search: string; role: string; status: string };
    totalCount: number;
}) {
    const getInitials = useInitials();
    const [search, setSearch] = useState(filters.search);
    const isFirstRender = useRef(true);

    const [addOpen, setAddOpen] = useState(false);
    const [editing, setEditing] = useState<PersonnelRow | null>(null);
    const [deleting, setDeleting] = useState<PersonnelRow | null>(null);

    // Debounce search so we're not firing a request on every keystroke.
    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;

            return;
        }

        if (search === filters.search) {
            return;
        }

        const timeout = setTimeout(() => {
            router.get(
                personnelIndex().url,
                { role: filters.role, status: filters.status, search },
                { preserveState: true, replace: true },
            );
        }, 400);

        return () => clearTimeout(timeout);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search]);

    const setFilter = (key: 'role' | 'status', value: string) => {
        router.get(
            personnelIndex().url,
            {
                role: filters.role,
                status: filters.status,
                search,
                [key]: value,
            },
            { preserveState: true, replace: true },
        );
    };

    const hasActiveFilters =
        search !== '' ||
        filters.search !== '' ||
        filters.role !== 'all' ||
        filters.status !== 'all';

    const resetFilters = () => {
        setSearch('');
        router.get(
            personnelIndex().url,
            { role: 'all', status: 'all', search: '' },
            { preserveState: true, replace: true },
        );
    };

    return (
        <PortalLayout title="Personnel Accounts">
            <div className="space-y-4">
                {/* Page header */}
                <div className="flex items-start justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold text-brand-navy">
                            Personnel Accounts
                        </h1>
                        <p className="mt-1 text-sm text-brand-muted">
                            {totalCount} personnel registered at BFP Lian
                            Station
                        </p>
                    </div>
                    <button
                        onClick={() => setAddOpen(true)}
                        className="flex items-center gap-1.5 rounded-lg bg-brand-navy px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-navy-hover"
                    >
                        <Plus size={15} /> Add Personnel
                    </button>
                </div>

                {/* Search + filter bar */}
                <PortalCard className="p-4">
                    <div className="flex flex-wrap items-center gap-3">
                        <div className="relative">
                            <Search
                                size={13}
                                className="absolute top-1/2 left-3 -translate-y-1/2 text-brand-muted"
                            />
                            <input
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Search personnel…"
                                className="w-64 rounded-lg border bg-brand-bg py-2 pr-4 pl-8 text-sm text-brand-navy outline-none"
                                style={inputStyle}
                            />
                        </div>

                        <select
                            aria-label="Filter by role"
                            value={filters.role}
                            onChange={(e) => setFilter('role', e.target.value)}
                            className={`${inputClass} pr-8`}
                            style={inputStyle}
                        >
                            {ROLE_FILTERS.map((r) => (
                                <option key={r.value} value={r.value}>
                                    {r.label}
                                </option>
                            ))}
                        </select>

                        <select
                            aria-label="Filter by status"
                            value={filters.status}
                            onChange={(e) =>
                                setFilter('status', e.target.value)
                            }
                            className={`${inputClass} pr-8`}
                            style={inputStyle}
                        >
                            {STATUS_FILTERS.map((s) => (
                                <option key={s.value} value={s.value}>
                                    {s.label}
                                </option>
                            ))}
                        </select>

                        {hasActiveFilters && (
                            <button
                                type="button"
                                onClick={resetFilters}
                                className="flex items-center gap-1 rounded-lg border px-2.5 py-2 text-xs font-medium text-brand-muted transition-colors hover:bg-red-50 hover:text-red-500"
                                style={inputStyle}
                            >
                                <X size={11} />
                                Reset filters
                            </button>
                        )}
                    </div>
                </PortalCard>

                {/* Table */}
                <PortalCard>
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead>
                                <tr
                                    style={{
                                        background: '#FAFBFC',
                                        borderBottom:
                                            '1px solid rgba(43,45,66,0.08)',
                                    }}
                                >
                                    {[
                                        'Name',
                                        'Rank',
                                        'Role',
                                        'Status',
                                        'Contact',
                                        'Actions',
                                    ].map((h) => (
                                        <th
                                            key={h}
                                            className="px-4 py-3 text-left text-xs font-semibold tracking-wider text-brand-muted uppercase"
                                        >
                                            {h}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {personnel.length === 0 && (
                                    <tr>
                                        <td
                                            colSpan={6}
                                            className="px-4 py-8 text-center text-xs text-brand-muted"
                                        >
                                            No personnel match these filters.
                                        </td>
                                    </tr>
                                )}
                                {personnel.map((p) => (
                                    <tr
                                        key={p.user_id}
                                        className="border-t"
                                        style={{
                                            borderColor: 'rgba(43,45,66,0.06)',
                                        }}
                                    >
                                        <td className="px-4 py-3">
                                            <div className="flex items-center gap-2.5">
                                                <span
                                                    className="flex size-8 flex-shrink-0 items-center justify-center rounded-full text-xs font-semibold text-white"
                                                    style={{
                                                        background: '#1D3557',
                                                    }}
                                                >
                                                    {getInitials(p.name)}
                                                </span>
                                                <span className="text-xs font-medium text-brand-navy">
                                                    {p.name}
                                                </span>
                                            </div>
                                        </td>
                                        <td className="px-4 py-3 text-xs text-brand-navy">
                                            <div>{p.rank ?? '—'}</div>
                                            {p.duty_group && (
                                                <span
                                                    className={`mt-1 inline-flex items-center rounded border px-1.5 py-0.5 text-[10px] font-semibold ${
                                                        p.duty_group === 'A'
                                                            ? 'border-brand-navy/20 bg-brand-navy/5 text-brand-navy'
                                                            : p.duty_group ===
                                                                'B'
                                                              ? 'border-amber-200 bg-amber-50 text-amber-800'
                                                              : p.duty_group ===
                                                                  'regular'
                                                                ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                                                : 'border-neutral-200 bg-neutral-100 text-neutral-600'
                                                    }`}
                                                >
                                                    {groupLabel(p.duty_group)}
                                                </span>
                                            )}
                                        </td>
                                        <td className="px-4 py-3">
                                            <RoleBadge role={p.role} />
                                        </td>
                                        <td className="px-4 py-3">
                                            <PersonnelStatusBadge
                                                status={p.status}
                                            />
                                        </td>
                                        <td className="px-4 py-3 font-mono text-xs text-brand-muted">
                                            {p.contact_number ?? '—'}
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="flex items-center gap-1">
                                                <button
                                                    onClick={() =>
                                                        setEditing(p)
                                                    }
                                                    className="rounded-lg p-1.5 text-brand-blue hover:bg-blue-50"
                                                    aria-label={`Edit ${p.name}`}
                                                >
                                                    <Pencil size={14} />
                                                </button>
                                                <button
                                                    onClick={() =>
                                                        setDeleting(p)
                                                    }
                                                    className="rounded-lg p-1.5 hover:bg-red-50"
                                                    style={{ color: '#E63946' }}
                                                    aria-label={`Delete ${p.name}`}
                                                >
                                                    <Trash2 size={14} />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </PortalCard>
            </div>

            <PersonnelFormModal
                mode="create"
                open={addOpen}
                onOpenChange={setAddOpen}
            />

            <PersonnelFormModal
                mode="edit"
                personnel={editing ?? undefined}
                open={editing !== null}
                onOpenChange={(open) => !open && setEditing(null)}
            />

            <DeletePersonnelDialog
                personnel={deleting}
                open={deleting !== null}
                onOpenChange={(open) => !open && setDeleting(null)}
            />
        </PortalLayout>
    );
}
