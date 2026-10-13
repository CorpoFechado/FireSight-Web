/**
 * Date, rotation, and platoon helpers for the BFP Lian Duty Schedule.
 *
 * IMPORTANT: Never parse 'YYYY-MM-DD' with native `new Date('YYYY-MM-DD')`
 * directly without adjusting for timezone, as JavaScript interprets ISO date-only
 * strings as UTC midnight, which shifts backwards in timezones behind UTC.
 */

export type DutyGroup = 'A' | 'B' | 'regular' | 'detached';

export type Member = {
    id: number;
    name: string;
    rank: string | null;
    base_group: string | null;
    effective_group: string | null;
    is_overridden: boolean;
    override_id: number | null;
    is_on_duty: boolean;
};

export type Override = {
    override_id: number;
    user_id: number;
    name: string;
    duty_group: string;
    from_group: string | null;
    starts_on: string;
    ends_on: string;
    reason: string | null;
    created_by_name: string | null;
};

export type WeekInfo = {
    start: string;
    end: string;
    activeGroup: 'A' | 'B';
    isCurrent: boolean;
};

export type UpcomingWeek = {
    week_start: string;
    week_end: string;
    group: 'A' | 'B';
};

export type GroupOption = {
    value: string;
    label: string;
};

export type PersonnelOption = {
    id: number;
    name: string;
    rank: string | null;
};

export type Roster = {
    regular: Member[];
    A: Member[];
    B: Member[];
    detached: Member[];
    unassigned: Member[];
};

export type DutySchedulePageProps = {
    week: WeekInfo;
    roster: Roster;
    overrides: Override[];
    upcoming: UpcomingWeek[];
    groups: GroupOption[];
    personnel: PersonnelOption[];
    filters: { date: string };
};

const MONTH_NAMES = [
    'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
];

/** Parse a 'YYYY-MM-DD' string into a local-time Date (no UTC shift). */
export function parseISODate(iso: string): Date {
    const [y, m, d] = iso.split('-').map(Number);

    return new Date(y, (m ?? 1) - 1, d ?? 1);
}

/** Format a Date back to 'YYYY-MM-DD' in local time. */
export function toISODate(date: Date): string {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');

    return `${y}-${m}-${d}`;
}

/** Add `days` days to a Date, returning a new Date. */
export function addDays(date: Date, days: number): Date {
    const copy = new Date(date);
    copy.setDate(copy.getDate() + days);

    return copy;
}

/** Snap any date string to the Monday of its week ('YYYY-MM-DD'). */
export function snapToMonday(iso: string): string {
    const d = parseISODate(iso);
    const day = d.getDay(); // 0 is Sunday, 1 is Monday ...
    const diff = day === 0 ? -6 : 1 - day;

    return toISODate(addDays(d, diff));
}

/**
 * Given a Monday ISO date string and number of weeks (1-12),
 * calculate the ending Sunday's ISO date string.
 */
export function computeWeekEnd(startMondayISO: string, numberOfWeeks: number): string {
    const monday = parseISODate(startMondayISO);
    const totalDays = Math.max(1, numberOfWeeks) * 7 - 1;

    return toISODate(addDays(monday, totalDays));
}

/**
 * Format a start and end date range into: "Oct 12 – Oct 18, 2026"
 * or "Dec 28, 2026 – Jan 3, 2027" if spanning a year boundary.
 */
export function formatWeekRange(startISO: string, endISO: string): string {
    const start = parseISODate(startISO);
    const end = parseISODate(endISO);

    const startMonth = MONTH_NAMES[start.getMonth()];
    const endMonth = MONTH_NAMES[end.getMonth()];
    const startYear = start.getFullYear();
    const endYear = end.getFullYear();

    if (startYear === endYear) {
        return `${startMonth} ${start.getDate()} – ${endMonth} ${end.getDate()}, ${startYear}`;
    }

    return `${startMonth} ${start.getDate()}, ${startYear} – ${endMonth} ${end.getDate()}, ${endYear}`;
}

/**
 * Format a short start date for rotation chips: "Oct 12"
 */
export function formatChipDate(iso: string): string {
    const d = parseISODate(iso);

    return `${MONTH_NAMES[d.getMonth()]} ${d.getDate()}`;
}

/** Human-friendly group label. */
export function groupLabel(group: string | null | undefined): string {
    if (!group) {
return 'Unassigned';
}

    switch (group) {
        case 'A':
            return 'Duty A';
        case 'B':
            return 'Duty B';
        case 'regular':
            return 'Always on Duty';
        case 'detached':
            return 'Detached';
        default:
            return group;
    }
}

/**
 * Color palette for Duty A and Duty B platoons:
 * Duty A: Navy / Indigo accent
 * Duty B: Amber / Terracotta accent
 */
export const DUTY_COLORS = {
    A: {
        badgeBg: 'bg-indigo-50 border-indigo-200 text-indigo-800',
        activeBadgeBg: 'bg-indigo-600 text-white',
        dot: 'bg-indigo-500',
        border: 'border-indigo-400',
        ring: 'ring-indigo-500',
        chipActive: 'bg-indigo-600 text-white font-semibold shadow-sm',
        chipInactive: 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200',
        subtle: 'text-indigo-600',
    },
    B: {
        badgeBg: 'bg-amber-50 border-amber-200 text-amber-800',
        activeBadgeBg: 'bg-amber-600 text-white',
        dot: 'bg-amber-500',
        border: 'border-amber-400',
        ring: 'ring-amber-500',
        chipActive: 'bg-amber-600 text-white font-semibold shadow-sm',
        chipInactive: 'bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200',
        subtle: 'text-amber-700',
    },
} as const;