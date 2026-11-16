import { router } from '@inertiajs/react';
import { Pencil, Plus, Search, Trash2, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { ContactFormModal } from '@/components/barangay-contacts/contact-form-modal';
import type {
    BarangayOption,
    ContactRow,
} from '@/components/barangay-contacts/contact-form-modal';
import { DeleteContactDialog } from '@/components/barangay-contacts/delete-contact-dialog';
import { PortalCard } from '@/components/portal/portal-card';
import PortalLayout from '@/layouts/portal-layout';
import { barangayContacts as barangayContactsIndex } from '@/routes';

const inputClass =
    'rounded-lg border bg-brand-bg py-2 px-3 text-sm text-brand-navy outline-none focus:ring-1 focus:ring-brand-navy/20';
const inputStyle = { borderColor: 'rgba(43,45,66,0.13)' };

export default function BarangayContactsIndex({
    contacts,
    barangays,
    filters,
    totalCount,
}: {
    contacts: ContactRow[];
    barangays: BarangayOption[];
    filters: { search: string; barangay_id: number | null };
    totalCount: number;
}) {
    const [search, setSearch] = useState(filters.search);
    const isFirstRender = useRef(true);

    const setBarangayFilter = (value: string) => {
        router.get(
            barangayContactsIndex().url,
            { search, barangay_id: value === '' ? undefined : value },
            { preserveState: true, replace: true },
        );
    };

    const activeBarangayId = filters.barangay_id?.toString() ?? '';
    const hasActiveFilters =
        search !== '' || filters.search !== '' || filters.barangay_id !== null;

    const resetFilters = () => {
        setSearch('');
        router.get(
            barangayContactsIndex().url,
            { search: '', barangay_id: undefined },
            { preserveState: true, replace: true },
        );
    };

    const [addOpen, setAddOpen] = useState(false);
    const [editing, setEditing] = useState<ContactRow | null>(null);
    const [deleting, setDeleting] = useState<ContactRow | null>(null);

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
                barangayContactsIndex().url,
                { barangay_id: filters.barangay_id, search },
                { preserveState: true, replace: true },
            );
        }, 400);

        return () => clearTimeout(timeout);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search]);

    return (
        <PortalLayout title="Barangay Contacts">
            <div className="space-y-4">
                {/* Page header */}
                <div className="flex items-start justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold text-brand-navy">
                            Barangay Contacts
                        </h1>
                        <p className="mt-1 text-sm text-brand-muted">
                            {totalCount} contact{totalCount !== 1 ? 's' : ''}{' '}
                            registered across all barangays
                        </p>
                    </div>
                    <button
                        onClick={() => setAddOpen(true)}
                        className="flex items-center gap-1.5 rounded-lg bg-brand-navy px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-navy-hover"
                    >
                        <Plus size={15} /> Add Contact
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
                                placeholder="Search by name, role, or phone…"
                                className="w-72 rounded-lg border bg-brand-bg py-2 pr-4 pl-8 text-sm text-brand-navy outline-none"
                                style={inputStyle}
                            />
                        </div>

                        <select
                            aria-label="Filter by barangay"
                            value={activeBarangayId}
                            onChange={(e) => setBarangayFilter(e.target.value)}
                            className={`${inputClass} pr-8`}
                            style={inputStyle}
                        >
                            <option value="">All Barangays</option>
                            {barangays.map((b) => (
                                <option
                                    key={b.barangay_id}
                                    value={b.barangay_id.toString()}
                                >
                                    {b.barangay_name}
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
                                        'Barangay',
                                        'Name',
                                        'Role',
                                        'Phone Number',
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
                                {contacts.length === 0 && (
                                    <tr>
                                        <td
                                            colSpan={5}
                                            className="px-4 py-8 text-center text-xs text-brand-muted"
                                        >
                                            No contacts match these filters.
                                        </td>
                                    </tr>
                                )}
                                {contacts.map((c) => (
                                    <tr
                                        key={c.id}
                                        className="border-t"
                                        style={{
                                            borderColor: 'rgba(43,45,66,0.06)',
                                        }}
                                    >
                                        <td className="px-4 py-3 text-xs font-medium text-brand-navy">
                                            {c.barangay_name ?? '—'}
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="flex items-center gap-2.5">
                                                <span
                                                    className="flex size-8 flex-shrink-0 items-center justify-center rounded-full text-xs font-semibold text-white"
                                                    style={{
                                                        background: '#1D3557',
                                                    }}
                                                >
                                                    {c.name
                                                        .split(' ')
                                                        .map((w) => w[0])
                                                        .slice(0, 2)
                                                        .join('')
                                                        .toUpperCase()}
                                                </span>
                                                <span className="text-xs font-medium text-brand-navy">
                                                    {c.name}
                                                </span>
                                            </div>
                                        </td>
                                        <td className="px-4 py-3 text-xs text-brand-navy">
                                            {c.role}
                                        </td>
                                        <td className="px-4 py-3 font-mono text-xs text-brand-muted">
                                            {c.phone_number}
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="flex items-center gap-1">
                                                <button
                                                    onClick={() =>
                                                        setEditing(c)
                                                    }
                                                    className="rounded-lg p-1.5 text-brand-blue hover:bg-blue-50"
                                                    aria-label={`Edit ${c.name}`}
                                                >
                                                    <Pencil size={14} />
                                                </button>
                                                <button
                                                    onClick={() =>
                                                        setDeleting(c)
                                                    }
                                                    className="rounded-lg p-1.5 hover:bg-red-50"
                                                    style={{ color: '#E63946' }}
                                                    aria-label={`Delete ${c.name}`}
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

            <ContactFormModal
                mode="create"
                barangays={barangays}
                open={addOpen}
                onOpenChange={setAddOpen}
            />

            <ContactFormModal
                mode="edit"
                contact={editing ?? undefined}
                barangays={barangays}
                open={editing !== null}
                onOpenChange={(open) => !open && setEditing(null)}
            />

            <DeleteContactDialog
                contact={deleting}
                open={deleting !== null}
                onOpenChange={(open) => !open && setDeleting(null)}
            />
        </PortalLayout>
    );
}
