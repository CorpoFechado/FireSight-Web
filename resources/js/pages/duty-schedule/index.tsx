import { router } from '@inertiajs/react';
import { Pencil, Plus, Trash2, TriangleAlert } from 'lucide-react';
import { useMemo, useState } from 'react';
import { DeleteShiftDialog } from '@/components/duty-schedule/delete-shift-dialog';
import { DutyShiftFormModal } from '@/components/duty-schedule/duty-shift-form-modal';
import type {
    PersonnelOption,
    ShiftRow,
} from '@/components/duty-schedule/duty-shift-form-modal';
import { PortalCard } from '@/components/portal/portal-card';
import PortalLayout from '@/layouts/portal-layout';
import {
    dayHeader,
    formatTime,
    getWeekDates,
    shiftLabel,
    toISODate,
    weekRangeLabel,
} from '@/lib/duty-schedule';

type OnDutyEntry = {
    id: number;
    name: string;
    shift_end_time: string;
};

type DutySchedulePageProps = {
    shifts: ShiftRow[];
    personnel: PersonnelOption[];
    onDutyNow: OnDutyEntry[];
    weekStart: string; // 'YYYY-MM-DD', Monday of the displayed week
    weekEnd: string;
};

export default function DutySchedulePage({
    shifts,
    personnel,
    onDutyNow,
    weekStart,
}: DutySchedulePageProps) {
    const [modalOpen, setModalOpen] = useState(false);
    const [modalMode, setModalMode] = useState<'create' | 'edit'>('create');
    const [activeShift, setActiveShift] = useState<ShiftRow | undefined>();
    const [prefill, setPrefill] = useState<
        { userId: number; date: string } | undefined
    >();
    const [deleteTarget, setDeleteTarget] = useState<ShiftRow | null>(null);

    // Single source of truth for the 7 displayed dates — both the column
    // headers AND the shift lookup key are derived from this same array,
    // so they can never disagree.
    const weekDates = useMemo(() => getWeekDates(weekStart), [weekStart]);
    const weekDateISOs = useMemo(
        () => weekDates.map((d) => toISODate(d)),
        [weekDates],
    );

    // Map "userId_YYYY-MM-DD" -> shift, so each grid cell is a single
    // O(1) lookup instead of filtering the whole shifts array per cell.
    const shiftsByCell = useMemo(() => {
        const map = new Map<string, ShiftRow>();

        for (const shift of shifts) {
            map.set(`${shift.user_id}_${shift.duty_date}`, shift);
        }

        return map;
    }, [shifts]);

    // IMPORTANT: this must be 'date', matching what DutyScheduleController@index
    // actually reads. It was previously sent as 'week', which the controller
    // silently ignored — that's why prev/next looked broken (every click
    // returned the same current week).
    const goToWeek = (offsetDays: number) => {
        const base = getWeekDates(weekStart)[0];
        const target = new Date(base);
        target.setDate(target.getDate() + offsetDays);

        router.get(
            '/duty-schedule',
            { date: toISODate(target) },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    const goToToday = () => {
        router.get(
            '/duty-schedule',
            {},
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    const openAdd = (cell?: { userId: number; date: string }) => {
        setModalMode('create');
        setActiveShift(undefined);
        setPrefill(cell);
        setModalOpen(true);
    };

    const openEdit = (shift: ShiftRow) => {
        setModalMode('edit');
        setActiveShift(shift);
        setPrefill(undefined);
        setModalOpen(true);
    };

    return (
        <PortalLayout title="Duty Schedule">
            <div className="flex items-start justify-between">
                <div>
                    <h1 className="text-3xl font-bold text-brand-navy">
                        Duty Schedule
                    </h1>
                    <p className="mt-1 text-sm text-brand-muted">
                        {shifts.length} shift{shifts.length === 1 ? '' : 's'}{' '}
                        this week — {weekRangeLabel(weekDates)}
                    </p>
                </div>

                <button
                    type="button"
                    onClick={() => openAdd()}
                    className="flex items-center gap-2 rounded-full bg-brand-navy px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-navy-hover"
                >
                    <Plus className="h-4 w-4" />
                    Add Duty Shift
                </button>
            </div>

            {/* On duty right now */}
            <PortalCard className="mt-6">
                {onDutyNow.length === 0 ? (
                    <div className="flex items-center gap-3 px-1 py-1">
                        <TriangleAlert
                            className="h-5 w-5 shrink-0"
                            style={{ color: '#F4A261' }}
                        />
                        <div>
                            <p className="text-sm font-semibold text-brand-navy">
                                No personnel currently on duty
                            </p>
                            <p className="text-xs text-brand-muted">
                                Nobody has an active shift right now.
                            </p>
                        </div>
                    </div>
                ) : (
                    <div className="flex flex-wrap items-center gap-4">
                        <div className="flex items-center gap-2">
                            <span className="relative flex h-2.5 w-2.5">
                                <span
                                    className="absolute inline-flex h-full w-full animate-ping rounded-full opacity-75"
                                    style={{ background: '#2A9D8F' }}
                                />
                                <span
                                    className="relative inline-flex h-2.5 w-2.5 rounded-full"
                                    style={{ background: '#2A9D8F' }}
                                />
                            </span>
                            <span className="text-sm font-semibold text-brand-navy">
                                On duty right now
                            </span>
                        </div>

                        {onDutyNow.map((entry) => (
                            <div
                                key={entry.id}
                                className="flex items-center gap-2 rounded-full bg-brand-bg px-3 py-1.5"
                            >
                                <span className="flex h-6 w-6 items-center justify-center rounded-full bg-brand-navy text-[10px] font-semibold text-white">
                                    {initials(entry.name)}
                                </span>
                                <span className="text-xs font-medium text-brand-navy">
                                    {entry.name}
                                </span>
                                <span className="text-xs text-brand-muted">
                                    Off duty at {formatTime(entry.shift_end_time)}
                                </span>
                            </div>
                        ))}
                    </div>
                )}
            </PortalCard>

            {/* Week navigator */}
            <PortalCard className="mt-4 flex items-center justify-center gap-4 py-3">
                <button
                    type="button"
                    onClick={() => goToWeek(-7)}
                    className="rounded-full p-1.5 text-brand-navy hover:bg-brand-bg"
                    aria-label="Previous week"
                >
                    ‹
                </button>
                <span className="text-sm font-bold text-brand-navy">
                    {weekRangeLabel(weekDates)}
                </span>
                <button
                    type="button"
                    onClick={goToToday}
                    className="rounded-full border px-3 py-1 text-xs font-semibold text-brand-navy"
                    style={{ borderColor: 'rgba(43,45,66,0.15)' }}
                >
                    Today
                </button>
                <button
                    type="button"
                    onClick={() => goToWeek(7)}
                    className="rounded-full p-1.5 text-brand-navy hover:bg-brand-bg"
                    aria-label="Next week"
                >
                    ›
                </button>
            </PortalCard>

            {/* Roster grid */}
            <PortalCard className="mt-4 overflow-x-auto p-0">
                <table className="w-full border-collapse">
                    <thead>
                        <tr
                            className="border-b"
                            style={{ borderColor: 'rgba(43,45,66,0.08)' }}
                        >
                            <th className="px-5 py-3 text-left text-xs font-bold tracking-wide text-brand-muted">
                                PERSONNEL
                            </th>
                            {weekDates.map((date) => {
                                const { dow, label } = dayHeader(date);

                                return (
                                    <th
                                        key={toISODate(date)}
                                        className="px-3 py-3 text-center text-xs font-bold tracking-wide text-brand-muted"
                                    >
                                        {dow}
                                        <div className="font-normal text-brand-muted/70">
                                            {label}
                                        </div>
                                    </th>
                                );
                            })}
                        </tr>
                    </thead>
                    <tbody>
                        {personnel.map((person) => (
                            <tr
                                key={person.id}
                                className="border-b last:border-0"
                                style={{ borderColor: 'rgba(43,45,66,0.06)' }}
                            >
                                <td className="px-5 py-3">
                                    <div className="flex items-center gap-3">
                                        <span className="flex h-9 w-9 items-center justify-center rounded-full bg-brand-navy text-xs font-semibold text-white">
                                            {initials(person.name)}
                                        </span>
                                        <div>
                                            <p className="text-sm font-semibold text-brand-navy">
                                                {person.name}
                                            </p>
                                            {person.rank && (
                                                <p className="text-xs text-brand-muted">
                                                    {person.rank}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                </td>

                                {weekDateISOs.map((iso) => {
                                    const shift = shiftsByCell.get(
                                        `${person.id}_${iso}`,
                                    );

                                    return (
                                        <td key={iso} className="px-2 py-2">
                                            {shift ? (
                                                <div
                                                    className="group relative flex items-center justify-center gap-1 rounded-lg px-2 py-2 text-xs font-medium"
                                                    style={{
                                                        background:
                                                            'rgba(42,157,143,0.12)',
                                                        color: '#1D3557',
                                                    }}
                                                >
                                                    <span>
                                                        {shiftLabel(
                                                            shift.time_start,
                                                            shift.time_end,
                                                        )}
                                                    </span>
                                                    <div className="absolute right-1 top-1 hidden gap-1 group-hover:flex">
                                                        <button
                                                            type="button"
                                                            onClick={() =>
                                                                openEdit(shift)
                                                            }
                                                            className="rounded bg-white/90 p-1 text-brand-navy shadow-sm"
                                                            aria-label="Edit shift"
                                                        >
                                                            <Pencil className="h-3 w-3" />
                                                        </button>
                                                        <button
                                                            type="button"
                                                            onClick={() =>
                                                                setDeleteTarget(shift)
                                                            }
                                                            className="rounded bg-white/90 p-1 shadow-sm"
                                                            style={{ color: '#E63946' }}
                                                            aria-label="Remove shift"
                                                        >
                                                            <Trash2 className="h-3 w-3" />
                                                        </button>
                                                    </div>
                                                </div>
                                            ) : (
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        openAdd({
                                                            userId: person.id,
                                                            date: iso,
                                                        })
                                                    }
                                                    className="group flex h-9 w-full items-center justify-center rounded-lg border border-dashed"
                                                    style={{
                                                        borderColor:
                                                            'rgba(43,45,66,0.15)',
                                                    }}
                                                    aria-label={`Add shift for ${person.name}`}
                                                >
                                                    <Plus className="h-3.5 w-3.5 text-brand-muted opacity-0 group-hover:opacity-100" />
                                                </button>
                                            )}
                                        </td>
                                    );
                                })}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </PortalCard>

            <DutyShiftFormModal
                mode={modalMode}
                shift={activeShift}
                personnel={personnel}
                prefill={prefill}
                open={modalOpen}
                onOpenChange={setModalOpen}
            />

            <DeleteShiftDialog
                shift={deleteTarget}
                open={deleteTarget !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setDeleteTarget(null);
                    }
                }}
            />
        </PortalLayout>
    );
}

function initials(name: string): string {
    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() ?? '')
        .join('');
}