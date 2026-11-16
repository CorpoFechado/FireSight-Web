import type { InertiaLinkProps } from '@inertiajs/react';
import {
    BarChart3,
    Bell,
    BookOpen,
    CalendarClock,
    FileText,
    Home,
    Layers,
    Map,
    Megaphone,
    Navigation,
    Phone,
    User,
    UserRound,
    Users,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import {
    analytics,
    announcements,
    barangayContacts,
    dashboard,
    dutySchedule,
    fireEducation,
    fireProne,
    map as fireMap,
    notifications,
    personnel,
    residents,
    response,
} from '@/routes';
import { index as incidents } from '@/routes/incidents';
import { edit as profileEdit } from '@/routes/profile';

export type PortalNavItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon: LucideIcon;
};

/**
 * Single source of truth for the BFP Administrator portal sidebar.
 */
export const PORTAL_NAV: PortalNavItem[] = [
    { title: 'Dashboard', href: dashboard(), icon: Home },
    { title: 'Incident Reports', href: incidents(), icon: FileText },
    { title: 'Fire Incidents Map', href: fireMap(), icon: Map },
    { title: 'Risk Analytics', href: fireProne(), icon: Layers },
    { title: 'Response Tracking', href: response(), icon: Navigation },
    {
        title: 'Analytics & Reports',
        href: analytics(),
        icon: BarChart3,
    },
    {
        title: 'Personnel Accounts',
        href: personnel(),
        icon: Users,
    },
    {
        title: 'Resident Accounts',
        href: residents(),
        icon: UserRound,
    },
    {
        title: 'Barangay Contacts',
        href: barangayContacts(),
        icon: Phone,
    },
    {
        title: 'Duty Schedule',
        href: dutySchedule(),
        icon: CalendarClock,
    },
    {
        title: 'Announcements',
        href: announcements(),
        icon: Megaphone,
    },
    {
        title: 'Fire Education',
        href: fireEducation(),
        icon: BookOpen,
    },
    { title: 'Notifications', href: notifications(), icon: Bell },
    { title: 'Profile', href: profileEdit(), icon: User },
];
