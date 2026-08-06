import { Link } from '@inertiajs/react';
import type { Paginated } from '@/types/pagination';

export function PaginationBar<T>({ paginated }: { paginated: Paginated<T> }) {
    if (paginated.last_page <= 1) {
        return (
            <p className="px-4 py-3 text-xs text-brand-muted">
                Showing {paginated.total} of {paginated.total} results
            </p>
        );
    }

    return (
        <div
            className="flex items-center justify-between border-t px-4 py-3"
            style={{ borderColor: 'rgba(43,45,66,0.08)' }}
        >
            <p className="text-xs text-brand-muted">
                Showing {paginated.from}–{paginated.to} of {paginated.total} results
            </p>
            <div className="flex items-center gap-1">
                {paginated.links.map((link, i) => (
                    <Link
                        key={i}
                        href={link.url ?? '#'}
                        preserveState
                        preserveScroll
                        dangerouslySetInnerHTML={{ __html: link.label }}
                        className="flex h-7 min-w-7 items-center justify-center rounded px-2 text-xs font-semibold"
                        style={{
                            background: link.active ? '#1D3557' : 'transparent',
                            color: link.active ? '#fff' : link.url ? '#6B7A8D' : 'rgba(107,122,141,0.4)',
                            pointerEvents: link.url ? 'auto' : 'none',
                        }}
                    />
                ))}
            </div>
        </div>
    );
}
