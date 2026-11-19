import { Head, router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import type { ReactNode } from 'react';
import { toast } from 'sonner';
import { AuroraBackground } from '@/components/aurora-background';
import {
    autoEnrollPushNotifications,
    registerServiceWorker,
} from '@/hooks/use-web-push';
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
    const { auth, unreadNotificationCount, vapidPublicKey } = usePage().props;
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [showPermissionBanner, setShowPermissionBanner] = useState(false);

    useEffect(() => {
        if (!auth.user || typeof window === 'undefined') {
            return;
        }

        // 1. If permission is not yet decided on this device, show the emergency prompt banner
        if (
            'Notification' in window &&
            Notification.permission === 'default' &&
            vapidPublicKey
        ) {
            setShowPermissionBanner(true);
        }

        // 2. If permission is already granted, auto-subscribe silently in background
        if (
            'Notification' in window &&
            Notification.permission === 'granted' &&
            vapidPublicKey
        ) {
            void autoEnrollPushNotifications(vapidPublicKey).catch(() => {});
        }

        // 3. Register service worker and listen for push notifications
        if (!('serviceWorker' in navigator)) {
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

            playSubtleAlertChime();
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
    }, [auth.user, vapidPublicKey]);

    const handleEnableAlarms = async () => {
        try {
            const result = await Notification.requestPermission();

            if (result === 'granted') {
                setShowPermissionBanner(false);
                await autoEnrollPushNotifications(vapidPublicKey);
                toast.success('Real-time emergency fire alarms enabled for this station.');
            } else {
                setShowPermissionBanner(false);
            }
        } catch {
            setShowPermissionBanner(false);
        }
    };

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
                {showPermissionBanner && (
                    <div
                        className="relative z-50 flex items-center justify-between gap-3 border-b px-4 py-2 text-xs font-medium text-white shadow-md sm:px-6"
                        style={{
                            background:
                                'linear-gradient(90deg, #1d3557 0%, #e63946 100%)',
                        }}
                    >
                        <div className="flex min-w-0 items-center gap-2">
                            <span className="relative flex size-2 shrink-0">
                                <span className="absolute inline-flex size-full animate-ping rounded-full bg-white opacity-75" />
                                <span className="relative inline-flex size-2 rounded-full bg-white" />
                            </span>
                            <span className="truncate">
                                <strong>Emergency Dispatch:</strong> Enable
                                desktop alerts to receive real-time fire alarms on
                                this computer.
                            </span>
                        </div>
                        <div className="flex shrink-0 items-center gap-2">
                            <button
                                type="button"
                                onClick={handleEnableAlarms}
                                className="cursor-pointer rounded-md bg-white px-3 py-1 text-xs font-bold text-brand-navy shadow-sm transition-transform hover:scale-105 active:scale-95"
                            >
                                Allow Alarms
                            </button>
                            <button
                                type="button"
                                onClick={() => setShowPermissionBanner(false)}
                                className="cursor-pointer rounded-md px-2 py-1 text-xs text-white/80 hover:text-white"
                                title="Dismiss"
                            >
                                ✕
                            </button>
                        </div>
                    </div>
                )}

                <PortalTopbar
                    title={title}
                    subtitle={subtitle}
                    unreadCount={unreadNotificationCount ?? 0}
                    onToggleSidebar={() => setSidebarOpen((v) => !v)}
                />

                <main
                    scroll-region=""
                    className="flex-1 overflow-y-auto p-4 sm:p-6"
                >
                    {children}
                </main>
            </div>
        </div>
    );
}
