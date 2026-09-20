import type { InertiaLinkProps } from '@inertiajs/react';
import {
    BarChart3,
    Bell,
    CalendarClock,
    FileText,
    Home,
    Layers,
    Map,
    Megaphone,
    Navigation,
    User,
    Users,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import {
    analytics,
    announcements,
    dashboard,
    dutySchedule,
    fireProne,
    map as fireMap,
    notifications,
    personnel,
    response,
} from '@/routes';
import { index as incidents } from '@/routes/incidents';
import { edit as profileEdit } from '@/routes/profile';
import type { BfpRole } from '@/types/auth';

export type PortalNavItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon: LucideIcon;
    /** When set, only these roles see this item. Omit to show to all BFP staff. */
    roles?: BfpRole[];
};

/**
 * Single source of truth for the portal sidebar. `bfp_personnel` sees
 * everything without a `roles` restriction; `bfp_admin`-only items
 * (Analytics, Personnel Accounts, Announcements) are flagged explicitly —
 * mirrors ADMIN_NAV / PERSONNEL_NAV from the original design.
 */
export const PORTAL_NAV: PortalNavItem[] = [
    { title: 'Dashboard', href: dashboard(), icon: Home },
    { title: 'Incident Reports', href: incidents(), icon: FileText },
    { title: 'Fire Incidents Map', href: fireMap(), icon: Map },
    { title: 'Risk Analytics', href: fireProne(), icon: Layers },
    { title: 'Response Tracking', href: response(), icon: Navigation },
    { title: 'Analytics & Reports', href: analytics(), icon: BarChart3, roles: ['bfp_admin'] },
    { title: 'Personnel Accounts', href: personnel(), icon: Users, roles: ['bfp_admin'] },
    { title: 'Duty Schedule', href: dutySchedule(), icon: CalendarClock, roles: ['bfp_admin'] },
    { title: 'Announcements', href: announcements(), icon: Megaphone, roles: ['bfp_admin'] },
    { title: 'Notifications', href: notifications(), icon: Bell },
    { title: 'Profile', href: profileEdit(), icon: User },
];

export function navItemsForRole(role: BfpRole): PortalNavItem[] {
    return PORTAL_NAV.filter((item) => !item.roles || item.roles.includes(role));
}
