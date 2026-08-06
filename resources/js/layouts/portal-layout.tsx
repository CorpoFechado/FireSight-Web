import { Head, usePage } from '@inertiajs/react';
import { type ReactNode } from 'react';
import { AuroraBackground } from '@/components/aurora-background';
import { PortalSidebar } from '@/layouts/portal/portal-sidebar';
import { PortalTopbar } from '@/layouts/portal/portal-topbar';

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

    // Fortify's `verified`/`bfp.staff` middleware guarantee this on every
    // portal route, but the type is nullable — narrow it once here.
    if (!auth.user) {
        return null;
    }

    return (
        <div className="relative isolate flex h-screen w-screen overflow-hidden bg-brand-bg">
            <Head title={title} />

            <AuroraBackground variant="light" className="!fixed inset-0" />

            <PortalSidebar
                role={auth.user.role}
                unreadCount={unreadNotificationCount ?? 0}
            />

            <div className="relative flex min-w-0 flex-1 flex-col">
                <PortalTopbar
                    title={title}
                    subtitle={subtitle}
                    unreadCount={unreadNotificationCount ?? 0}
                />

                <main className="flex-1 overflow-y-auto p-6">{children}</main>
            </div>
        </div>
    );
}
