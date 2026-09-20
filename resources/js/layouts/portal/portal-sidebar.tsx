import { Link, router } from '@inertiajs/react';
import { LogOut } from 'lucide-react';
import { dashboard, logout } from '@/routes';
import { useCurrentUrl } from '@/hooks/use-current-url';
import type { BfpRole } from '@/types/auth';
import { navItemsForRole } from './nav-config';

export function PortalSidebar({
    role,
    unreadCount = 0,
}: {
    role: BfpRole;
    unreadCount?: number;
}) {
    const { isCurrentUrl } = useCurrentUrl();
    const items = navItemsForRole(role);

    return (
        <div
            className="relative z-10 flex h-full w-60 flex-shrink-0 flex-col"
            style={{
                background: 'linear-gradient(165deg, #1d3557 0%, #16294a 60%, #12213d 100%)',
            }}
        >
            {/* Logo */}
            <Link
                href={dashboard()}
                className="flex items-center gap-3 border-b px-4 py-3.5 transition-colors hover:bg-white/5"
                style={{ borderColor: 'rgba(255,255,255,0.08)', minHeight: 64 }}
            >
                <img
                    src="/images/firesight-logo.png"
                    alt="FireSight Logo"
                    className="h-10 w-auto object-contain flex-shrink-0 drop-shadow-sm"
                />
                <div className="overflow-hidden">
                    <p className="text-sm leading-none font-bold text-white">FireSight</p>
                    <p className="mt-1 text-xs leading-none text-white/45">BFP Lian</p>
                </div>
            </Link>

            {/* Role badge */}
            <div className="surface-glass-dark mx-3 mt-3 mb-1 rounded-xl px-3 py-2">
                <p className="text-xs font-semibold text-white/50">Signed in as</p>
                <p className="mt-0.5 text-xs font-bold text-white">
                    {role === 'bfp_admin' ? 'BFP Administrator' : 'BFP Personnel'}
                </p>
            </div>

            {/* Nav items */}
            <nav className="flex-1 space-y-1 overflow-y-auto px-2 py-2">
                {items.map((item) => {
                    const active = isCurrentUrl(item.href);
                    const Icon = item.icon;
                    const isNotif = item.title === 'Notifications';

                    return (
                        <Link
                            key={item.title}
                            href={item.href}
                            prefetch
                            className="press-scale relative flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left transition-colors"
                            style={{
                                background: active
                                    ? 'linear-gradient(135deg, rgba(255,255,255,0.14) 0%, rgba(255,255,255,0.06) 100%)'
                                    : 'transparent',
                                color: active ? '#fff' : 'rgba(255,255,255,0.65)',
                                boxShadow: active ? 'inset 0 1px 0 0 rgba(255,255,255,0.08)' : 'none',
                            }}
                        >
                            {active && (
                                <span className="absolute top-1/2 left-1.5 h-4 w-1 -translate-y-1/2 rounded-full bg-brand-orange" />
                            )}
                            <span className="relative flex-shrink-0">
                                <Icon size={17} />
                                {isNotif && unreadCount > 0 && (
                                    <span
                                        className="absolute -top-1.5 -right-1.5 flex size-4 items-center justify-center rounded-full bg-brand-orange text-white"
                                        style={{ fontSize: 9, fontWeight: 700 }}
                                    >
                                        {unreadCount}
                                    </span>
                                )}
                            </span>
                            <span className="truncate text-sm font-medium">{item.title}</span>
                        </Link>
                    );
                })}
            </nav>

            {/* Footer */}
            <div className="border-t px-2 py-3" style={{ borderColor: 'rgba(255,255,255,0.08)' }}>
                <Link
                    href={logout()}
                    method="post"
                    as="button"
                    onClick={() => router.flushAll()}
                    className="press-scale flex w-full items-center gap-3 rounded-xl px-3 py-2 text-left text-white/50 transition-colors hover:bg-white/5 hover:text-white"
                >
                    <LogOut size={17} />
                    <span className="text-sm">Sign Out</span>
                </Link>
            </div>
        </div>
    );
}
