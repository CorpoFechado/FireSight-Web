/**
 * Date + shift helpers for the Duty Schedule page.
 *
 * IMPORTANT: never do `new Date('2026-08-03')` for an ISO date string.
 * JS parses that as UTC midnight, then your browser renders it in local
 * time — in any timezone behind UTC that silently shifts the date back
 * a day. `parseISODate` below builds the Date from its y/m/d parts
 * directly in local time instead, so there's no implicit UTC step.
 */

const DAY_LABELS = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];
const MONTH_LABELS = [
    'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN',
    'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC',
];

/** Parse a 'YYYY-MM-DD' string into a local-time Date (no UTC shift). */
export function parseISODate(iso: string): Date {
    const [y, m, d] = iso.split('-').map(Number);
    return new Date(y, m - 1, d);
}

/** Format a Date back to 'YYYY-MM-DD', in local time. */
export function toISODate(date: Date): string {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

/** Add `days` days to a Date, returning a new Date (doesn't mutate). */
export function addDays(date: Date, days: number): Date {
    const copy = new Date(date);
    copy.setDate(copy.getDate() + days);
    return copy;
}

/**
 * Build the 7 dates (Mon–Sun) for the week that `weekStartISO` starts.
 * `weekStartISO` should already be the Monday of that week (as returned
 * by DutyScheduleController@index) — this just fans it out to an array
 * so the header labels and the column lookup keys always agree.
 */
export function getWeekDates(weekStartISO: string): Date[] {
    const start = parseISODate(weekStartISO);
    return Array.from({ length: 7 }, (_, i) => addDays(start, i));
}

/** e.g. { dow: 'MON', label: 'AUG 3' } for a grid column header. */
export function dayHeader(date: Date): { dow: string; label: string } {
    // getDay(): 0=Sun..6=Sat. Shift so Monday is index 0.
    const dow = DAY_LABELS[(date.getDay() + 6) % 7];
    const label = `${MONTH_LABELS[date.getMonth()]} ${date.getDate()}`;
    return { dow, label };
}

/** e.g. "Aug 3 – Aug 9" for the week navigator, from the same dates. */
export function weekRangeLabel(weekDates: Date[]): string {
    const start = weekDates[0];
    const end = weekDates[weekDates.length - 1];
    const startLabel = `${capitalize(MONTH_LABELS[start.getMonth()])} ${start.getDate()}`;
    const endLabel = `${capitalize(MONTH_LABELS[end.getMonth()])} ${end.getDate()}`;
    return `${startLabel} – ${endLabel}`;
}

function capitalize(word: string): string {
    return word.charAt(0) + word.slice(1).toLowerCase();
}

/** "18:00:00" or "18:00" -> "6:00 PM" for display in shift chips. */
export function formatTime(time: string): string {
    const [hStr, mStr] = time.split(':');
    let h = Number(hStr);
    const m = mStr ?? '00';
    const suffix = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    return `${h}:${m} ${suffix}`;
}

// ---------------------------------------------------------------------
// Shift presets — Morning (06:00–18:00) and Night (18:00–06:00, crosses
// midnight). Keeping these as the only two options means the underlying
// time_start/time_end columns don't change at all; we're just
// constraining what values the UI is allowed to write.
// ---------------------------------------------------------------------

export const SHIFT_PRESETS = {
    morning: { label: 'Morning Shift', sublabel: '6:00 AM – 6:00 PM', time_start: '06:00', time_end: '18:00' },
    night: { label: 'Night Shift', sublabel: '6:00 PM – 6:00 AM', time_start: '18:00', time_end: '06:00' },
} as const;

export type ShiftPresetKey = keyof typeof SHIFT_PRESETS;

/** Which preset key matches a given time_start/time_end pair, if any. */
export function presetKeyFor(timeStart: string, timeEnd: string): ShiftPresetKey | null {
    const start = timeStart.slice(0, 5);
    const end = timeEnd.slice(0, 5);
    for (const key of Object.keys(SHIFT_PRESETS) as ShiftPresetKey[]) {
        if (SHIFT_PRESETS[key].time_start === start && SHIFT_PRESETS[key].time_end === end) {
            return key;
        }
    }
    return null;
}

/** Short label for a shift chip: "Morning Shift" or the raw time range as a fallback. */
export function shiftLabel(timeStart: string, timeEnd: string): string {
    const key = presetKeyFor(timeStart, timeEnd);
    if (key) {
        return `${SHIFT_PRESETS[key].label} (${SHIFT_PRESETS[key].sublabel})`;
    }
    return `${formatTime(timeStart)} – ${formatTime(timeEnd)}`;
}