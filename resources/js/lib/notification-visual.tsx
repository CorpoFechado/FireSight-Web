import {
    Activity,
    AlertTriangle,
    CheckCircle2,
    FileText,
    Flame,
    Megaphone,
    Send,
    type LucideIcon,
} from 'lucide-react';

export type NotificationType =
    'incident_alert' | 'status_update' | 'system' | 'reminder';

export function getNotificationVisual(
    type: NotificationType,
    title: string,
    message: string,
): { Icon: LucideIcon; bg: string; color: string } {
    const text = `${title} ${message}`.toLowerCase();

    if (type === 'incident_alert') {
        if (text.includes('critical')) {
            return { Icon: Flame, bg: '#FDE8EA', color: '#E63946' };
        }
        return { Icon: AlertTriangle, bg: '#FFF4E6', color: '#F77F00' };
    }

    if (type === 'status_update') {
        if (text.includes('resolved') || text.includes('complete')) {
            return { Icon: CheckCircle2, bg: '#E0F5F3', color: '#1B7A72' };
        }
        if (text.includes('dispatch')) {
            return { Icon: Send, bg: '#E3EFF7', color: '#2C5F82' };
        }
        return { Icon: FileText, bg: '#E3EFF7', color: '#2C5F82' };
    }

    if (type === 'reminder') {
        return { Icon: Megaphone, bg: '#F3F4F6', color: '#1D3557' };
    }

    // system
    return { Icon: Activity, bg: '#F3F4F6', color: '#6B7A8D' };
}

/** "Today 14:35" / "Yesterday 22:18" / "Jul 18 14:00" */
export function formatNotificationTime(iso: string): string {
    const date = new Date(iso);
    const now = new Date();
    const yesterday = new Date(now);
    yesterday.setDate(now.getDate() - 1);

    const time = date.toLocaleTimeString('en-GB', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    });

    if (date.toDateString() === now.toDateString()) {
        return `Today ${time}`;
    }

    if (date.toDateString() === yesterday.toDateString()) {
        return `Yesterday ${time}`;
    }

    return `${date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} ${time}`;
}
