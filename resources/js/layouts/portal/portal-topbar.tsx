import { Link, usePage } from '@inertiajs/react';
import { Bell, ChevronDown, Menu } from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { UserInfo } from '@/components/user-info';
import { UserMenuContent } from '@/components/user-menu-content';
import { notifications } from '@/routes';

export function PortalTopbar({
    title,
    subtitle,
    unreadCount = 0,
    onToggleSidebar,
}: {
    title: string;
    subtitle?: string;
    unreadCount?: number;
    onToggleSidebar?: () => void;
}) {
    const { auth } = usePage().props;

    return (
        <div
            className="relative z-10 flex flex-shrink-0 items-center gap-2 sm:gap-4 border-b px-4 sm:px-6 backdrop-blur-xl backdrop-saturate-150"
            style={{
                height: 64,
                background: 'rgba(255,255,255,0.72)',
                borderColor: 'rgba(29,53,87,0.07)',
            }}
        >
            {onToggleSidebar && (
                <button
                    type="button"
                    onClick={onToggleSidebar}
                    className="inline-flex lg:hidden -ml-1 p-2 rounded-lg text-brand-navy hover:bg-black/5 transition-colors cursor-pointer"
                    aria-label="Toggle navigation menu"
                >
                    <Menu size={20} />
                </button>
            )}

            <div className="min-w-0">
                <h1 className="truncate text-base font-bold tracking-tight text-brand-navy leading-none">
                    {title}
                </h1>
            </div>

            <div className="flex-1" />

            {/* Notifications */}
            <Link
                href={notifications()}
                className="press-scale relative rounded-full p-2 transition-colors hover:bg-black/5"
            >
                <Bell size={18} className="text-brand-muted" />
                {unreadCount > 0 && (
                    <span
                        className="absolute top-1 right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-brand-orange px-1 text-white"
                        style={{ fontSize: 9, fontWeight: 700 }}
                    >
                        {unreadCount > 99 ? '99+' : unreadCount}
                    </span>
                )}
            </Link>

            {/* User */}
            {auth.user && (
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <button className="flex cursor-pointer items-center gap-2">
                            <UserInfo user={auth.user} />
                            <ChevronDown
                                size={13}
                                className="text-brand-muted"
                            />
                        </button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent
                        className="min-w-56 rounded-lg"
                        align="end"
                        side="bottom"
                    >
                        <UserMenuContent user={auth.user} />
                    </DropdownMenuContent>
                </DropdownMenu>
            )}
        </div>
    );
}
