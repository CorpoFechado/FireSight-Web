import { router } from '@inertiajs/react';
import { CheckCheck } from 'lucide-react';
import { PortalCard } from '@/components/portal/portal-card';
import {
    NotificationItem,
    type NotificationRow,
} from '@/components/notifications/notification-item';
import PortalLayout from '@/layouts/portal-layout';
import { readAll } from '@/routes/notifications';

type Groups = {
    today: NotificationRow[];
    recent: NotificationRow[];
    earlier: NotificationRow[];
};

export default function NotificationsIndex({
    groups,
    unreadCount,
}: {
    groups: Groups;
    unreadCount: number;
}) {
    const isEmpty =
        groups.today.length === 0 &&
        groups.recent.length === 0 &&
        groups.earlier.length === 0;

    const handleMarkAllRead = () => {
        router.post(readAll().url, {}, { preserveScroll: true });
    };

    return (
        <PortalLayout title="Notifications">
            <div className="space-y-4">
                {/* Page header */}
                <div className="flex items-start justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold text-brand-navy">
                            Notifications
                        </h1>
                        <p className="mt-1 text-sm text-brand-muted">
                            {unreadCount > 0
                                ? `${unreadCount} unread notification${unreadCount === 1 ? '' : 's'}`
                                : 'You’re all caught up'}
                        </p>
                    </div>
                    <button
                        onClick={handleMarkAllRead}
                        disabled={unreadCount === 0}
                        className="flex items-center gap-1.5 rounded-lg border px-4 py-2.5 text-sm font-semibold text-brand-navy disabled:cursor-not-allowed disabled:opacity-50"
                        style={{ borderColor: 'rgba(43,45,66,0.15)' }}
                    >
                        <CheckCheck size={15} /> Mark all as read
                    </button>
                </div>

                {isEmpty ? (
                    <PortalCard className="flex flex-col items-center justify-center gap-2 p-10 text-center">
                        <p className="text-sm font-semibold text-brand-navy">
                            No notifications yet
                        </p>
                        <p className="max-w-sm text-xs text-brand-muted">
                            Updates on your reports and station announcements
                            will show up here.
                        </p>
                    </PortalCard>
                ) : (
                    <div className="space-y-5">
                        <NotificationGroup label="Today" items={groups.today} />
                        <NotificationGroup
                            label="Recent"
                            items={groups.recent}
                        />
                        <NotificationGroup
                            label="Earlier"
                            items={groups.earlier}
                        />
                    </div>
                )}
            </div>
        </PortalLayout>
    );
}

function NotificationGroup({
    label,
    items,
}: {
    label: string;
    items: NotificationRow[];
}) {
    if (items.length === 0) {
        return null;
    }

    return (
        <div>
            <p className="mb-2 text-xs font-semibold tracking-wider text-brand-muted uppercase">
                {label}
            </p>
            <PortalCard>
                {items.map((n) => (
                    <NotificationItem key={n.id} notification={n} />
                ))}
            </PortalCard>
        </div>
    );
}
