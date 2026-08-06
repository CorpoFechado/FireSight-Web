import { router } from '@inertiajs/react';
import {
    formatNotificationTime,
    getNotificationVisual,
    type NotificationType,
} from '@/lib/notification-visual';
import { read } from '@/routes/notifications';

export type NotificationRow = {
    id: number;
    type: NotificationType;
    title: string;
    message: string;
    is_read: boolean;
    created_at: string;
};

export function NotificationItem({
    notification,
}: {
    notification: NotificationRow;
}) {
    const { Icon, bg, color } = getNotificationVisual(
        notification.type,
        notification.title,
        notification.message,
    );

    const handleClick = () => {
        if (notification.is_read) {
            return;
        }

        router.patch(
            read(notification.id).url,
            {},
            { preserveScroll: true },
        );
    };

    return (
        <button
            type="button"
            onClick={handleClick}
            className="flex w-full items-start gap-3 border-b px-5 py-4 text-left transition-colors last:border-b-0 hover:bg-brand-bg/60"
            style={{
                borderColor: 'rgba(43,45,66,0.06)',
                background: notification.is_read ? 'transparent' : '#F7FAFC',
                cursor: notification.is_read ? 'default' : 'pointer',
            }}
        >
            <span
                className="flex size-9 flex-shrink-0 items-center justify-center rounded-full"
                style={{ background: bg }}
            >
                <Icon size={16} style={{ color }} />
            </span>

            <div className="min-w-0 flex-1">
                <p
                    className={`text-sm ${notification.is_read ? 'font-medium' : 'font-bold'} text-brand-navy`}
                >
                    {notification.title}
                </p>
                <p className="mt-0.5 text-xs text-brand-muted">
                    {notification.message}
                </p>
            </div>

            <div className="flex flex-shrink-0 items-center gap-1.5 pl-3">
                {!notification.is_read && (
                    <span className="size-1.5 rounded-full bg-brand-orange" />
                )}
                <span className="text-xs text-brand-muted">
                    {formatNotificationTime(notification.created_at)}
                </span>
            </div>
        </button>
    );
}
