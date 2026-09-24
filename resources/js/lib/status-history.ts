import type { ReportStatus } from '@/lib/fire-status';

export type StatusHistoryEntry = {
    id: string | number;
    status: ReportStatus;
    changed_by: string;
    changed_at: string;
    notes?: string | null;
    personnel?: {
        id: number;
        name: string;
        role: string;
        rank: string | null;
        employee_number?: string | null;
    } | null;
};

const STANDARD_SEQUENCE: ReportStatus[] = [
    'pending',
    'accepted',
    'dispatched',
    'resolved',
];

const INVALID_SEQUENCE: ReportStatus[] = [
    'pending',
    'invalid',
];

const STATUS_PERSONNEL: Record<ReportStatus, string> = {
    pending: 'FO1 J. Dela Cruz (Intake)',
    accepted: 'FO2 R. Santos (Duty Verifier)',
    dispatched: 'SFO1 M. Bautista (Dispatch Lead)',
    resolved: 'SFO2 E. Ramos (Ground Commander)',
    invalid: 'FO2 R. Santos (Duty Inspector)',
};

const STEP_GAP_MINUTES: Record<ReportStatus, number> = {
    pending: 0,
    accepted: 8,
    dispatched: 14,
    resolved: 38,
    invalid: 12,
};

function parseDate(dateStr: string): Date {
    if (!dateStr) {
        return new Date();
    }
    const normalized = dateStr.includes(' ') && !dateStr.includes('T')
        ? dateStr.replace(' ', 'T')
        : dateStr;
    const parsed = new Date(normalized);
    return isNaN(parsed.getTime()) ? new Date() : parsed;
}

function formatDateTime(d: Date): string {
    const pad = (n: number) => n.toString().padStart(2, '0');
    const year = d.getFullYear();
    const month = pad(d.getMonth() + 1);
    const day = pad(d.getDate());
    const hours = pad(d.getHours());
    const mins = pad(d.getMinutes());
    return `${year}-${month}-${day} ${hours}:${mins}`;
}

/**
 * Derives a sequential status history timeline starting from the report's submission
 * time up to its current status.
 */
export function generateStatusHistory(
    currentStatus: ReportStatus,
    submissionDateTime: string
): StatusHistoryEntry[] {
    const sequence = currentStatus === 'invalid'
        ? INVALID_SEQUENCE
        : (() => {
            const idx = STANDARD_SEQUENCE.indexOf(currentStatus);
            return idx >= 0 ? STANDARD_SEQUENCE.slice(0, idx + 1) : ['pending' as ReportStatus];
        })();

    const baseTime = parseDate(submissionDateTime);
    let cumulativeMs = baseTime.getTime();

    return sequence.map((status, index) => {
        const gapMinutes = STEP_GAP_MINUTES[status] ?? 10;
        cumulativeMs += gapMinutes * 60 * 1000;
        const entryDate = new Date(cumulativeMs);

        return {
            id: `sh-${index}-${status}`,
            status,
            changed_by: STATUS_PERSONNEL[status] ?? 'BFP Personnel',
            changed_at: formatDateTime(entryDate),
        };
    });
}
