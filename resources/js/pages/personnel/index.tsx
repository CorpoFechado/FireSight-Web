import { router } from '@inertiajs/react';
import { Filter, Pencil, Plus, Search, Trash2 } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { PortalCard } from '@/components/portal/portal-card';
import {
    PersonnelStatusBadge,
    RoleBadge,
} from '@/components/portal/status-badge';
import { DeletePersonnelDialog } from '@/components/personnel/delete-personnel-dialog';
import {
    PersonnelFormModal,
    type PersonnelRow,
} from '@/components/personnel/personnel-form-modal';
import { useInitials } from '@/hooks/use-initials';
import PortalLayout from '@/layouts/portal-layout';
import { personnel as personnelIndex } from '@/routes';

const ROLE_FILTERS = [
    { label: 'All roles', value: 'all' },
    { label: 'Admin', value: 'bfp_admin' },
    { label: 'Personnel', value: 'bfp_personnel' },
];

const STATUS_FILTERS = [
    { label: 'All statuses', value: 'all' },
    { label: 'Active', value: 'active' },
    { label: 'Inactive', value: 'inactive' },
];

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

    const activeFilterCount =
        (filters.role !== 'all' ? 1 : 0) + (filters.status !== 'all' ? 1 : 0);

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
                                style={{ borderColor: 'rgba(43,45,66,0.13)' }}
                            />
                        </div>

                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <button
                                    className="flex items-center gap-1.5 rounded-lg border px-3 py-2 text-xs font-semibold text-brand-navy"
                                    style={{
                                        borderColor: 'rgba(43,45,66,0.15)',
                                    }}
                                >
                                    <Filter size={13} /> Filter
                                    {activeFilterCount > 0 && (
                                        <span className="flex size-4 items-center justify-center rounded-full bg-brand-navy text-[10px] text-white">
                                            {activeFilterCount}
                                        </span>
                                    )}
                                </button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent
                                align="start"
                                className="w-48 bg-white text-brand-navy"
                            >
                                <DropdownMenuLabel>Role</DropdownMenuLabel>
                                <DropdownMenuRadioGroup
                                    value={filters.role}
                                    onValueChange={(v) => setFilter('role', v)}
                                >
                                    {ROLE_FILTERS.map((r) => (
                                        <DropdownMenuRadioItem
                                            key={r.value}
                                            value={r.value}
                                        >
                                            {r.label}
                                        </DropdownMenuRadioItem>
                                    ))}
                                </DropdownMenuRadioGroup>
                                <DropdownMenuSeparator />
                                <DropdownMenuLabel>Status</DropdownMenuLabel>
                                <DropdownMenuRadioGroup
                                    value={filters.status}
                                    onValueChange={(v) =>
                                        setFilter('status', v)
                                    }
                                >
                                    {STATUS_FILTERS.map((s) => (
                                        <DropdownMenuRadioItem
                                            key={s.value}
                                            value={s.value}
                                        >
                                            {s.label}
                                        </DropdownMenuRadioItem>
                                    ))}
                                </DropdownMenuRadioGroup>
                            </DropdownMenuContent>
                        </DropdownMenu>
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
                                        key={p.id}
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
                                            {p.rank ?? '—'}
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
