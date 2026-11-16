import { Head, router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import type { ReactNode } from 'react';
import { toast } from 'sonner';
import { AuroraBackground } from '@/components/aurora-background';
import { registerServiceWorker } from '@/hooks/use-web-push';
import { PortalSidebar } from '@/layouts/portal/portal-sidebar';
import { PortalTopbar } from '@/layouts/portal/portal-topbar';
import { playSubtleAlertChime } from '@/lib/notification-sound';

type PushMessageEventData = {
    type?: string;
    payload?: {
        title?: string;
        body?: string;
        url?: string;
        report_id?: number | null;
        notification_type?: string;
    };
};

export default function PortalLayout({
    title,
    subtitle,
    children,
}: {
    title: string;
    subtitle?: string;
    children: ReactNode;
}) {
    const { auth, unreadNotificationCount } = usePage().props;
    const [sidebarOpen, setSidebarOpen] = useState(false);

    useEffect(() => {
        if (
            !auth.user ||
            typeof window === 'undefined' ||
            !('serviceWorker' in navigator)
        ) {
            return;
        }

        void registerServiceWorker().catch(() => {});

        const handleServiceWorkerMessage = (
            event: MessageEvent<PushMessageEventData>,
        ) => {
            if (
                event.data?.type !== 'FIRESIGHT_PUSH_NOTIFICATION' ||
                !event.data.payload
            ) {
                return;
            }

            const {
                title: alertTitle = 'New Fire Report',
                body = 'A new fire report is awaiting verification.',
                url,
                report_id: reportId,
                notification_type: notificationType,
            } = event.data.payload;

            playSubtleAlertChime();

            const targetUrl =
                url ?? (reportId ? `/incidents/${reportId}` : '/notifications');

            const showToast =
                notificationType === 'test_alert' ? toast.info : toast.warning;

            showToast(alertTitle, {
                description: body,
                duration: 10000,
                action: reportId
                    ? {
                          label: 'View Report',
                          onClick: () => router.visit(targetUrl),
                      }
                    : undefined,
            });

            router.reload({ only: ['unreadNotificationCount', 'groups'] });
        };

        navigator.serviceWorker.addEventListener(
            'message',
            handleServiceWorkerMessage,
        );

        return () => {
            navigator.serviceWorker.removeEventListener(
                'message',
                handleServiceWorkerMessage,
            );
        };
    }, [auth.user]);

    // Fortify's `verified`/`bfp.admin` middleware guarantee this on every
    // portal route, but the type is nullable — narrow it once here.
    if (!auth.user) {
        return null;
    }

    return (
        <div className="relative isolate flex h-screen w-screen overflow-hidden bg-brand-bg">
            <Head title={title} />

            <AuroraBackground variant="light" className="!fixed inset-0" />

            <PortalSidebar
                unreadCount={unreadNotificationCount ?? 0}
                isOpen={sidebarOpen}
                onClose={() => setSidebarOpen(false)}
            />

            <div className="relative flex min-w-0 flex-1 flex-col">
                <PortalTopbar
                    title={title}
                    subtitle={subtitle}
                    unreadCount={unreadNotificationCount ?? 0}
                    onToggleSidebar={() => setSidebarOpen((v) => !v)}
                />

                <main className="flex-1 overflow-y-auto p-4 sm:p-6">
                    {children}
                </main>
            </div>
        </div>
    );
}
