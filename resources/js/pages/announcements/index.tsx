import { router } from '@inertiajs/react';
import { Pencil, Plus, Search, Trash2, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { PortalCard } from '@/components/portal/portal-card';
import { AnnouncementBadge } from '@/components/portal/status-badge';
import {
    AnnouncementFormModal,
    type AnnouncementRow,
} from '@/components/announcements/announcement-form-modal';
import { DeleteAnnouncementDialog } from '@/components/announcements/delete-announcement-dialog';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import PortalLayout from '@/layouts/portal-layout';
import { announcements as announcementsRoute } from '@/routes';

const CATEGORY_OPTIONS = [
    { value: 'all', label: 'All Categories' },
    { value: 'general', label: 'General' },
    { value: 'advisory', label: 'Advisory' },
    { value: 'emergency', label: 'Emergency' },
    { value: 'fire_safety_tip', label: 'Fire Safety Tip' },
] as const;

export default function AnnouncementsIndex({
    announcements,
    filters = { search: '', category: 'all' },
    totalCount,
}: {
    announcements: AnnouncementRow[];
    filters?: { search: string; category: string };
    totalCount?: number;
}) {
    const [search, setSearch] = useState(filters.search ?? '');
    const isFirstRender = useRef(true);

    const [addOpen, setAddOpen] = useState(false);
    const [editing, setEditing] = useState<AnnouncementRow | null>(null);
    const [deleting, setDeleting] = useState<AnnouncementRow | null>(null);

    const currentCategory = filters.category || 'all';
    const hasActiveFilters =
        (filters.search && filters.search.trim() !== '') ||
        currentCategory !== 'all';

    // Debounce search query so we don't fire requests on every keystroke.
    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }

        const timeout = setTimeout(() => {
            router.get(
                announcementsRoute().url,
                { category: currentCategory, search },
                { preserveState: true, replace: true },
            );
        }, 400);

        return () => clearTimeout(timeout);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search]);

    const setCategory = (cat: string) => {
        router.get(
            announcementsRoute().url,
            { category: cat, search },
            { preserveState: true, replace: true },
        );
    };

    const clearFilters = () => {
        setSearch('');
        router.get(
            announcementsRoute().url,
            { category: 'all', search: '' },
            { preserveState: true, replace: true },
        );
    };

    return (
        <PortalLayout title="Announcements">
            <div className="space-y-4">
                {/* Page header */}
                <div className="flex items-start justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold text-brand-navy">
                            Announcements
                        </h1>
                        <p className="mt-1 text-sm text-brand-muted">
                            Publish and manage official communications through FireSight
                        </p>
                    </div>
                    <button
                        onClick={() => setAddOpen(true)}
                        className="flex items-center gap-1.5 rounded-lg bg-brand-navy px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-navy-hover"
                    >
                        <Plus size={15} /> Create Announcement
                    </button>
                </div>

                {/* Search & Category filter bar */}
                <PortalCard className="p-4">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <div className="flex flex-wrap items-center gap-3">
                            <div className="relative">
                                <Search
                                    size={13}
                                    className="absolute top-1/2 left-3 -translate-y-1/2 text-brand-muted"
                                />
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Search announcements…"
                                    className="w-64 rounded-lg border bg-brand-bg py-2 pr-8 pl-8 text-sm text-brand-navy outline-none placeholder:text-brand-muted focus:bg-white"
                                    style={{ borderColor: 'rgba(43,45,66,0.13)' }}
                                />
                                {search && (
                                    <button
                                        type="button"
                                        onClick={() => setSearch('')}
                                        className="absolute top-1/2 right-2.5 -translate-y-1/2 text-brand-muted hover:text-brand-navy"
                                        aria-label="Clear search"
                                    >
                                        <X size={13} />
                                    </button>
                                )}
                            </div>

                            <Select
                                value={currentCategory}
                                onValueChange={setCategory}
                            >
                                <SelectTrigger
                                    className="h-[38px] w-48 rounded-lg border bg-brand-bg text-sm text-brand-navy"
                                    style={{ borderColor: 'rgba(43,45,66,0.13)' }}
                                >
                                    <SelectValue placeholder="All Categories" />
                                </SelectTrigger>
                                <SelectContent className="bg-white text-brand-navy">
                                    {CATEGORY_OPTIONS.map((cat) => (
                                        <SelectItem
                                            key={cat.value}
                                            value={cat.value}
                                        >
                                            {cat.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>

                            {hasActiveFilters && (
                                <button
                                    onClick={clearFilters}
                                    className="flex items-center gap-1 rounded-lg px-2.5 py-2 text-xs font-medium text-brand-muted transition-colors hover:bg-red-50 hover:text-red-500"
                                    style={{
                                        border: '1px solid rgba(43,45,66,0.13)',
                                    }}
                                >
                                    <X size={12} /> Clear filters
                                </button>
                            )}
                        </div>

                        {(totalCount !== undefined || announcements.length > 0) && (
                            <span className="text-xs text-brand-muted">
                                Showing {announcements.length} announcement{announcements.length === 1 ? '' : 's'}
                            </span>
                        )}
                    </div>
                </PortalCard>

                {announcements.length === 0 ? (
                    <PortalCard className="flex flex-col items-center justify-center gap-2 p-10 text-center">
                        <p className="text-sm font-semibold text-brand-navy">
                            {hasActiveFilters
                                ? 'No announcements match your filter'
                                : 'No announcements yet'}
                        </p>
                        <p className="max-w-sm text-xs text-brand-muted">
                            {hasActiveFilters
                                ? 'Try adjusting your search query or selecting a different category.'
                                : 'Published announcements will show up here for BFP Lian personnel to see.'}
                        </p>
                        {hasActiveFilters && (
                            <button
                                onClick={clearFilters}
                                className="mt-2 rounded-lg border px-3 py-1.5 text-xs font-semibold text-brand-navy hover:bg-brand-bg"
                                style={{
                                    borderColor: 'rgba(43,45,66,0.15)',
                                }}
                            >
                                Clear filters
                            </button>
                        )}
                    </PortalCard>
                ) : (
                    <div className="grid gap-4 lg:grid-cols-2">
                        {announcements.map((a) => (
                            <PortalCard
                                key={a.id}
                                className="flex flex-col p-5"
                            >
                                <div className="flex items-center gap-2.5">
                                    <AnnouncementBadge type={a.type} />
                                    <span className="text-xs text-brand-muted">
                                        {a.date}
                                    </span>
                                </div>

                                <h2 className="mt-3 text-base font-bold text-brand-navy">
                                    {a.title}
                                </h2>
                                <p className="mt-1.5 flex-1 text-sm text-brand-muted">
                                    {a.content}
                                </p>

                                <div className="mt-4 flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <button
                                            onClick={() => setEditing(a)}
                                            className="flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-semibold text-brand-navy"
                                            style={{
                                                borderColor:
                                                    'rgba(43,45,66,0.15)',
                                            }}
                                        >
                                            <Pencil size={12} /> Edit
                                        </button>
                                        <button
                                            onClick={() => setDeleting(a)}
                                            className="flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-semibold"
                                            style={{
                                                borderColor:
                                                    'rgba(230,57,70,0.25)',
                                                color: '#E63946',
                                            }}
                                        >
                                            <Trash2 size={12} /> Delete
                                        </button>
                                    </div>
                                    <span className="text-xs text-brand-muted">
                                        Published by {a.publishedBy}
                                    </span>
                                </div>
                            </PortalCard>
                        ))}
                    </div>
                )}
            </div>

            <AnnouncementFormModal
                mode="create"
                open={addOpen}
                onOpenChange={setAddOpen}
            />

            <AnnouncementFormModal
                mode="edit"
                announcement={editing ?? undefined}
                open={editing !== null}
                onOpenChange={(open) => !open && setEditing(null)}
            />

            <DeleteAnnouncementDialog
                announcement={deleting}
                open={deleting !== null}
                onOpenChange={(open) => !open && setDeleting(null)}
            />
        </PortalLayout>
    );
}
