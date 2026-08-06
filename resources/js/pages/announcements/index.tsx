import { Pencil, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { PortalCard } from '@/components/portal/portal-card';
import { AnnouncementBadge } from '@/components/portal/status-badge';
import {
    AnnouncementFormModal,
    type AnnouncementRow,
} from '@/components/announcements/announcement-form-modal';
import { DeleteAnnouncementDialog } from '@/components/announcements/delete-announcement-dialog';
import PortalLayout from '@/layouts/portal-layout';

export default function AnnouncementsIndex({
    announcements,
}: {
    announcements: AnnouncementRow[];
}) {
    const [addOpen, setAddOpen] = useState(false);
    const [editing, setEditing] = useState<AnnouncementRow | null>(null);
    const [deleting, setDeleting] = useState<AnnouncementRow | null>(null);

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

                {announcements.length === 0 ? (
                    <PortalCard className="flex flex-col items-center justify-center gap-2 p-10 text-center">
                        <p className="text-sm font-semibold text-brand-navy">
                            No announcements yet
                        </p>
                        <p className="max-w-sm text-xs text-brand-muted">
                            Published announcements will show up here for BFP
                            Lian personnel to see.
                        </p>
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
