import { router } from '@inertiajs/react';
import {
    AlertTriangle,
    BookOpen,
    Clock,
    Eye,
    Flame,
    LayoutGrid,
    LifeBuoy,
    Lightbulb,
    Pencil,
    Plus,
    Search,
    Shield,
    Sparkles,
    Table as TableIcon,
    Trash2,
    X,
} from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { PortalCard } from '@/components/portal/portal-card';
import { EducationCategoryBadge } from '@/components/portal/status-badge';
import {
    FireEducationFormModal,
    type FireEducationRow,
} from '@/components/fire-education/fire-education-form-modal';
import { DeleteFireEducationDialog } from '@/components/fire-education/delete-fire-education-dialog';
import { FireEducationViewModal } from '@/components/fire-education/fire-education-view-modal';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import PortalLayout from '@/layouts/portal-layout';
import { fireEducation as fireEducationRoute } from '@/routes';
import type { FireEducationCategory } from '@/lib/fire-status';

interface StatsProps {
    total: number;
    prevention: number;
    emergency_response: number;
    awareness: number;
    featured: number;
}

const CATEGORY_TABS = [
    { value: 'all', label: 'All Articles' },
    { value: 'prevention', label: 'Fire Prevention' },
    { value: 'emergency_response', label: 'Emergency Response' },
    { value: 'awareness', label: 'Awareness' },
] as const;

export default function FireEducationIndex({
    contents,
    filters = { search: '', category: 'all', featured: 'all' },
    stats,
}: {
    contents: FireEducationRow[];
    filters?: { search: string; category: string; featured: string };
    stats: StatsProps;
}) {
    const [search, setSearch] = useState(filters.search ?? '');
    const isFirstRender = useRef(true);

    const [viewMode, setViewMode] = useState<'grid' | 'table'>('grid');
    const [createOpen, setCreateOpen] = useState(false);
    const [editing, setEditing] = useState<FireEducationRow | null>(null);
    const [viewing, setViewing] = useState<FireEducationRow | null>(null);
    const [deleting, setDeleting] = useState<FireEducationRow | null>(null);

    const currentCategory = filters.category || 'all';
    const currentFeatured = filters.featured || 'all';
    const hasActiveFilters =
        (filters.search && filters.search.trim() !== '') ||
        currentCategory !== 'all' ||
        currentFeatured !== 'all';

    // Debounce search query so requests are not fired on every keystroke
    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }

        const timeout = setTimeout(() => {
            router.get(
                fireEducationRoute().url,
                { category: currentCategory, featured: currentFeatured, search },
                { preserveState: true, replace: true },
            );
        }, 350);

        return () => clearTimeout(timeout);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search]);

    const setCategory = (cat: string) => {
        router.get(
            fireEducationRoute().url,
            { category: cat, featured: currentFeatured, search },
            { preserveState: true, replace: true },
        );
    };

    const setFeaturedFilter = (feat: string) => {
        router.get(
            fireEducationRoute().url,
            { category: currentCategory, featured: feat === 'all' ? undefined : feat, search },
            { preserveState: true, replace: true },
        );
    };

    const clearFilters = () => {
        setSearch('');
        router.get(
            fireEducationRoute().url,
            { category: 'all', featured: 'all', search: '' },
            { preserveState: true, replace: true },
        );
    };

    return (
        <PortalLayout title="Fire Education">
            <div className="space-y-6">
                {/* Page header */}
                <div className="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                    <div>
                        <div className="flex items-center gap-2">
                            <h1 className="text-2xl font-bold tracking-tight text-brand-navy">
                                Fire Education Content
                            </h1>
                            <span className="rounded-full bg-brand-bg px-2.5 py-0.5 text-xs font-semibold text-brand-muted">
                            </span>
                        </div>
                        <p className="mt-1 text-sm text-brand-muted">
                            Manage educational articles, fire safety guidelines, and emergency tips visible to Lian residents on the mobile application.
                        </p>
                    </div>

                    <button
                        onClick={() => setCreateOpen(true)}
                        className="flex items-center justify-center gap-2 rounded-xl bg-brand-navy px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-brand-navy/90 hover:shadow active:scale-[0.98]"
                    >
                        <Plus size={18} />
                        <span>Create New Article</span>
                    </button>
                </div>

                {/* KPI Stat Cards */}
                <div className="grid grid-cols-2 gap-3 sm:grid-cols-5">
                    <div className="surface-glass rounded-xl p-4 transition-all hover:shadow-sm">
                        <div className="flex items-center justify-between text-brand-muted">
                            <span className="text-xs font-medium uppercase tracking-wider">Total Content</span>
                            <BookOpen size={16} className="text-brand-blue" />
                        </div>
                        <p className="mt-2 text-2xl font-bold text-brand-navy">{stats.total}</p>
                    </div>

                    <div className="surface-glass rounded-xl p-4 transition-all hover:shadow-sm">
                        <div className="flex items-center justify-between text-brand-muted">
                            <span className="text-xs font-medium uppercase tracking-wider">Prevention</span>
                            <Shield size={16} className="text-teal-600" />
                        </div>
                        <p className="mt-2 text-2xl font-bold text-brand-navy">{stats.prevention}</p>
                    </div>

                    <div className="surface-glass rounded-xl p-4 transition-all hover:shadow-sm">
                        <div className="flex items-center justify-between text-brand-muted">
                            <span className="text-xs font-medium uppercase tracking-wider">Emergency</span>
                            <LifeBuoy size={16} className="text-rose-600" />
                        </div>
                        <p className="mt-2 text-2xl font-bold text-brand-navy">{stats.emergency_response}</p>
                    </div>

                    <div className="surface-glass rounded-xl p-4 transition-all hover:shadow-sm">
                        <div className="flex items-center justify-between text-brand-muted">
                            <span className="text-xs font-medium uppercase tracking-wider">Awareness</span>
                            <Lightbulb size={16} className="text-sky-600" />
                        </div>
                        <p className="mt-2 text-2xl font-bold text-brand-navy">{stats.awareness}</p>
                    </div>

                    <div className="surface-glass rounded-xl p-4 transition-all hover:shadow-sm col-span-2 sm:col-span-1">
                        <div className="flex items-center justify-between text-brand-muted">
                            <span className="text-xs font-medium uppercase tracking-wider">Featured</span>
                            <Sparkles size={16} className="text-amber-500" />
                        </div>
                        <p className="mt-2 text-2xl font-bold text-brand-navy">{stats.featured}</p>
                    </div>
                </div>

                {/* Filter and Control Bar */}
                <div className="flex flex-col gap-3 rounded-2xl border border-gray-100 bg-white p-3 shadow-xs sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex flex-1 flex-col gap-2 sm:flex-row sm:items-center">
                        {/* Search Input */}
                        <div className="relative flex-1 sm:max-w-xs">
                            <Search
                                size={16}
                                className="absolute top-1/2 left-3 -translate-y-1/2 text-brand-muted"
                            />
                            <input
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Search articles or guidelines…"
                                className="h-9 w-full rounded-lg border bg-brand-bg/50 pr-8 pl-9 text-xs font-medium text-brand-navy outline-none transition-colors focus:border-brand-blue focus:bg-white"
                                style={{ borderColor: 'rgba(43,45,66,0.15)' }}
                            />
                            {search && (
                                <button
                                    onClick={() => setSearch('')}
                                    className="absolute top-1/2 right-2.5 -translate-y-1/2 text-brand-muted hover:text-brand-navy"
                                >
                                    <X size={14} />
                                </button>
                            )}
                        </div>

                        {/* Category Filter Pills / Dropdown */}
                        <div className="flex flex-wrap items-center gap-1.5">
                            {CATEGORY_TABS.map((tab) => {
                                const active = currentCategory === tab.value;
                                return (
                                    <button
                                        key={tab.value}
                                        onClick={() => setCategory(tab.value)}
                                        className={`rounded-lg px-3 py-1.5 text-xs font-semibold transition-all ${active
                                                ? 'bg-brand-navy text-white shadow-xs'
                                                : 'bg-brand-bg text-brand-muted hover:text-brand-navy'
                                            }`}
                                    >
                                        {tab.label}
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    <div className="flex items-center justify-between gap-2 sm:justify-end">
                        {/* Featured Filter */}
                        <Select
                            value={currentFeatured}
                            onValueChange={setFeaturedFilter}
                        >
                            <SelectTrigger className="h-9 w-36 text-xs">
                                <SelectValue placeholder="All Status" />
                            </SelectTrigger>
                            <SelectContent className="bg-white text-brand-navy">
                                <SelectItem value="all">All Articles</SelectItem>
                                <SelectItem value="yes">Featured Only</SelectItem>
                                <SelectItem value="no">Standard Only</SelectItem>
                            </SelectContent>
                        </Select>

                        {/* View Mode Toggle */}
                        <div className="flex items-center rounded-lg border border-gray-200 bg-brand-bg/60 p-0.5">
                            <button
                                onClick={() => setViewMode('grid')}
                                className={`rounded-md p-1.5 transition-colors ${viewMode === 'grid'
                                        ? 'bg-white text-brand-navy shadow-xs'
                                        : 'text-brand-muted hover:text-brand-navy'
                                    }`}
                                title="Grid View"
                            >
                                <LayoutGrid size={15} />
                            </button>
                            <button
                                onClick={() => setViewMode('table')}
                                className={`rounded-md p-1.5 transition-colors ${viewMode === 'table'
                                        ? 'bg-white text-brand-navy shadow-xs'
                                        : 'text-brand-muted hover:text-brand-navy'
                                    }`}
                                title="Table View"
                            >
                                <TableIcon size={15} />
                            </button>
                        </div>

                        {hasActiveFilters && (
                            <button
                                onClick={clearFilters}
                                className="flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-brand-muted hover:bg-gray-100 hover:text-brand-navy"
                            >
                                <X size={13} />
                                Reset
                            </button>
                        )}
                    </div>
                </div>

                {/* Article List / Empty State */}
                {contents.length === 0 ? (
                    <PortalCard className="p-12 text-center">
                        <div className="mx-auto flex size-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                            <BookOpen size={28} />
                        </div>
                        <h3 className="mt-4 text-base font-bold text-brand-navy">
                            No educational content found
                        </h3>
                        <p className="mx-auto mt-1 max-w-sm text-xs text-brand-muted">
                            {hasActiveFilters
                                ? 'No articles match your current search and category filters. Try resetting the filters.'
                                : 'Get started by creating the first fire education article for the mobile app.'}
                        </p>
                        <div className="mt-5 flex justify-center gap-2">
                            {hasActiveFilters ? (
                                <button
                                    onClick={clearFilters}
                                    className="rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-brand-navy hover:bg-gray-50"
                                >
                                    Clear Filters
                                </button>
                            ) : (
                                <button
                                    onClick={() => setCreateOpen(true)}
                                    className="rounded-lg bg-brand-navy px-4 py-2 text-xs font-semibold text-white shadow hover:bg-brand-navy/90"
                                >
                                    Create First Article
                                </button>
                            )}
                        </div>
                    </PortalCard>
                ) : viewMode === 'grid' ? (
                    /* Grid Cards View */
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        {contents.map((item) => (
                            <div
                                key={item.id}
                                className="group surface-glass flex flex-col justify-between overflow-hidden rounded-2xl border border-gray-100 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                            >
                                <div>
                                    {/* Cover photo or placeholder banner */}
                                    {item.image_path ? (
                                        <div className="relative h-40 w-full overflow-hidden bg-gray-100">
                                            <img
                                                src={
                                                    item.image_path.startsWith('http')
                                                        ? item.image_path
                                                        : `/storage/${item.image_path}`
                                                }
                                                alt={item.title}
                                                className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                            />
                                            {item.is_featured && (
                                                <span className="absolute top-2.5 right-2.5 flex items-center gap-1 rounded-full bg-amber-500/90 px-2 py-0.5 text-[11px] font-bold text-white shadow-xs backdrop-blur-xs">
                                                    <Sparkles size={11} />
                                                    Featured
                                                </span>
                                            )}
                                        </div>
                                    ) : (
                                        <div className="flex h-3 items-center justify-end px-3 pt-2">
                                            {item.is_featured && (
                                                <span className="flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-bold text-amber-700">
                                                    <Sparkles size={11} className="text-amber-500" />
                                                    Featured
                                                </span>
                                            )}
                                        </div>
                                    )}

                                    <div className="p-4">
                                        <div className="flex items-center justify-between gap-2">
                                            <EducationCategoryBadge category={item.category} />
                                            <span className="flex items-center gap-1 text-[11px] text-brand-muted font-medium">
                                                <Clock size={11} />
                                                {item.read_minutes} min read
                                            </span>
                                        </div>

                                        <h3
                                            onClick={() => setViewing(item)}
                                            className="mt-2.5 line-clamp-2 cursor-pointer text-base font-bold text-brand-navy transition-colors hover:text-brand-blue"
                                        >
                                            {item.title}
                                        </h3>

                                        <p className="mt-1.5 line-clamp-3 text-xs leading-relaxed text-brand-muted">
                                            {item.summary}
                                        </p>
                                    </div>
                                </div>

                                <div className="flex items-center justify-between border-t border-gray-100 bg-gray-50/40 px-4 py-2.5">
                                    <span className="text-[11px] text-brand-muted">
                                        {item.created_date}
                                    </span>

                                    <div className="flex items-center gap-1">
                                        <button
                                            onClick={() => setViewing(item)}
                                            className="flex size-7 items-center justify-center rounded-lg text-brand-muted transition-colors hover:bg-white hover:text-brand-navy hover:shadow-xs"
                                            title="View Details"
                                        >
                                            <Eye size={14} />
                                        </button>
                                        <button
                                            onClick={() => setEditing(item)}
                                            className="flex size-7 items-center justify-center rounded-lg text-brand-muted transition-colors hover:bg-white hover:text-brand-blue hover:shadow-xs"
                                            title="Edit Article"
                                        >
                                            <Pencil size={14} />
                                        </button>
                                        <button
                                            onClick={() => setDeleting(item)}
                                            className="flex size-7 items-center justify-center rounded-lg text-brand-muted transition-colors hover:bg-white hover:text-brand-red hover:shadow-xs"
                                            title="Delete Article"
                                        >
                                            <Trash2 size={14} />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    /* Table View */
                    <PortalCard className="overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="border-b border-gray-100 bg-gray-50/60 font-semibold text-brand-muted uppercase tracking-wider">
                                <tr>
                                    <th className="py-3 pr-4 pl-6">Article</th>
                                    <th className="py-3 px-4">Category</th>
                                    <th className="py-3 px-4">Read Time</th>
                                    <th className="py-3 px-4">Status</th>
                                    <th className="py-3 px-4">Published</th>
                                    <th className="py-3 pr-6 pl-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100 text-brand-navy">
                                {contents.map((item) => (
                                    <tr
                                        key={item.id}
                                        className="transition-colors hover:bg-gray-50/40"
                                    >
                                        <td className="py-3.5 pr-4 pl-6">
                                            <div className="font-bold text-brand-navy">
                                                {item.title}
                                            </div>
                                            <div className="line-clamp-1 max-w-md text-brand-muted">
                                                {item.summary}
                                            </div>
                                        </td>
                                        <td className="py-3.5 px-4">
                                            <EducationCategoryBadge category={item.category} />
                                        </td>
                                        <td className="py-3.5 px-4 font-medium text-brand-muted">
                                            {item.read_minutes} mins
                                        </td>
                                        <td className="py-3.5 px-4">
                                            {item.is_featured ? (
                                                <span className="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-bold text-amber-700">
                                                    <Sparkles size={11} className="text-amber-500" />
                                                    Featured
                                                </span>
                                            ) : (
                                                <span className="text-[11px] text-brand-muted">
                                                    Standard
                                                </span>
                                            )}
                                        </td>
                                        <td className="py-3.5 px-4 text-brand-muted">
                                            {item.created_date}
                                        </td>
                                        <td className="py-3.5 pr-6 pl-4 text-right">
                                            <div className="flex items-center justify-end gap-1">
                                                <button
                                                    onClick={() => setViewing(item)}
                                                    className="flex size-7 items-center justify-center rounded-lg text-brand-muted hover:bg-brand-bg hover:text-brand-navy"
                                                    title="View Article"
                                                >
                                                    <Eye size={14} />
                                                </button>
                                                <button
                                                    onClick={() => setEditing(item)}
                                                    className="flex size-7 items-center justify-center rounded-lg text-brand-muted hover:bg-brand-bg hover:text-brand-blue"
                                                    title="Edit Article"
                                                >
                                                    <Pencil size={14} />
                                                </button>
                                                <button
                                                    onClick={() => setDeleting(item)}
                                                    className="flex size-7 items-center justify-center rounded-lg text-brand-muted hover:bg-brand-bg hover:text-brand-red"
                                                    title="Delete Article"
                                                >
                                                    <Trash2 size={14} />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </PortalCard>
                )}

                {/* Modals and Dialogs */}
                <FireEducationFormModal
                    mode="create"
                    open={createOpen}
                    onOpenChange={setCreateOpen}
                />

                <FireEducationFormModal
                    mode="edit"
                    content={editing ?? undefined}
                    open={editing !== null}
                    onOpenChange={(open) => !open && setEditing(null)}
                />

                <FireEducationViewModal
                    content={viewing}
                    open={viewing !== null}
                    onOpenChange={(open) => !open && setViewing(null)}
                    onEdit={(content) => setEditing(content)}
                />

                <DeleteFireEducationDialog
                    content={deleting}
                    open={deleting !== null}
                    onOpenChange={(open) => !open && setDeleting(null)}
                />
            </div>
        </PortalLayout>
    );
}
