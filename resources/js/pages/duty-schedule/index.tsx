import { router } from '@inertiajs/react';
import {
    ArrowLeftRight,
    ArrowRightLeft,
    Calendar,
    ChevronLeft,
    ChevronRight,
    MoreHorizontal,
    Trash2,
    Users,
} from 'lucide-react';
import { useMemo, useState } from 'react';
import { toast } from 'sonner';
import { DutyOverrideModal } from '@/components/duty-schedule/duty-override-modal';
import { RemoveOverrideDialog } from '@/components/duty-schedule/remove-override-dialog';
import { PortalCard } from '@/components/portal/portal-card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { useInitials } from '@/hooks/use-initials';
import PortalLayout from '@/layouts/portal-layout';
import {
    addDays,
    formatChipDate,
    formatWeekRange,
    groupLabel,
    parseISODate,
    toISODate
    
    
    
} from '@/lib/duty-schedule';
import type {DutySchedulePageProps, Member, Override} from '@/lib/duty-schedule';
import { dutySchedule } from '@/routes';
import { updateGroup } from '@/routes/dutySchedule';

const PERMANENT_MOVE_OPTIONS: { value: string | null; label: string }[] = [
    { value: 'A', label: 'Duty A' },
    { value: 'B', label: 'Duty B' },
    { value: 'regular', label: 'Always on Duty' },
    { value: 'detached', label: 'Detached' },
    { value: null, label: 'Unassigned' },
];

export default function DutySchedulePage({
    week,
    roster,
    overrides,
    upcoming,
    groups,
}: DutySchedulePageProps) {
    const getInitials = useInitials();

    // Modal state for Reassign / Swap
    const [overrideModalOpen, setOverrideModalOpen] = useState(false);
    const [modalPrefill, setModalPrefill] = useState<{
        mode: 'reassign' | 'swap';
        userId?: number;
    }>({ mode: 'reassign' });

    // Dialog state for removing an override
    const [removeOverrideTarget, setRemoveOverrideTarget] =
        useState<Override | null>(null);

    // Map overrides by override_id for instant tooltip lookup
    const overridesMap = useMemo(() => {
        const map = new Map<number, Override>();

        for (const ov of overrides) {
            map.set(ov.override_id, ov);
        }

        return map;
    }, [overrides]);

    // Flat list of all members across all groups for modal prefill & selection
    const allMembers = useMemo(
        () => [
            ...roster.A,
            ...roster.B,
            ...roster.regular,
            ...roster.detached,
            ...roster.unassigned,
        ],
        [roster],
    );

    const totalPersonnelCount = allMembers.length;

    // Week navigation handlers
    const goToOffset = (days: number) => {
        const base = parseISODate(week.start);
        const target = addDays(base, days);
        router.get(
            dutySchedule({ query: { date: toISODate(target) } }).url,
            {},
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    const goToCurrentWeek = () => {
        router.get(
            dutySchedule().url,
            {},
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    const goToDate = (iso: string) => {
        router.get(
            dutySchedule({ query: { date: iso } }).url,
            {},
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    // Permanent move handler
    const handleMoveGroup = (member: Member, nextGroup: string | null) => {
        router.patch(
            updateGroup(member.id).url,
            { duty_group: nextGroup },
            {
                preserveScroll: true,
                onError: (errs) => {
                    const firstErr = Object.values(errs)[0];

                    if (firstErr) {
toast.error(firstErr);
}
                },
            },
        );
    };

    // Open override modal with prefill
    const openOverrideModal = (
        mode: 'reassign' | 'swap',
        userId?: number,
    ) => {
        setModalPrefill({ mode, userId });
        setOverrideModalOpen(true);
    };

    // Open remove override dialog for a member
    const handleRemoveOverrideForMember = (member: Member) => {
        if (!member.override_id) {
return;
}

        const ov =
            overridesMap.get(member.override_id) ??
            ({
                override_id: member.override_id,
                user_id: member.id,
                name: member.name,
                duty_group: member.effective_group ?? '',
                from_group: member.base_group,
                starts_on: week.start,
                ends_on: week.end,
                reason: null,
                created_by_name: null,
            } as Override);
        setRemoveOverrideTarget(ov);
    };

    // Render individual member card
    const renderMemberCard = (member: Member, isDimmed: boolean = false) => {
        const memberOverride = member.override_id
            ? overridesMap.get(member.override_id)
            : undefined;

        return (
            <div
                key={member.id}
                className={`group relative flex items-center justify-between rounded-xl border border-neutral-200/80 bg-white p-3 shadow-xs transition-all duration-150 hover:-translate-y-0.5 hover:shadow-md ${
                    isDimmed ? 'opacity-65 hover:opacity-95' : ''
                }`}
            >
                <div className="flex items-center gap-3 min-w-0 pr-2">
                    <span className="flex size-9 shrink-0 items-center justify-center rounded-full bg-brand-navy/10 text-xs font-bold text-brand-navy">
                        {getInitials(member.name)}
                    </span>
                    <div className="min-w-0">
                        <div className="flex items-center gap-1.5 flex-wrap">
                            <span className="text-sm font-semibold text-brand-navy truncate">
                                {member.name}
                            </span>
                            {member.is_overridden && (
                                <TooltipProvider>
                                    <Tooltip>
                                        <TooltipTrigger asChild>
                                            <span className="inline-flex cursor-help items-center rounded-full border border-amber-200 bg-amber-50 px-2 py-0.2 text-[10px] font-semibold text-amber-800">
                                                Reassigned
                                            </span>
                                        </TooltipTrigger>
                                        <TooltipContent className="max-w-xs bg-brand-navy text-white text-xs p-2.5">
                                            <p className="font-semibold text-amber-300">
                                                {groupLabel(member.base_group)} →{' '}
                                                {groupLabel(member.effective_group)}
                                            </p>
                                            {memberOverride ? (
                                                <>
                                                    <p className="mt-0.5 text-[11px] text-neutral-300">
                                                        {formatWeekRange(
                                                            memberOverride.starts_on,
                                                            memberOverride.ends_on,
                                                        )}
                                                    </p>
                                                    {memberOverride.reason && (
                                                        <p className="mt-1 text-[11px] italic text-neutral-200">
                                                            “{memberOverride.reason}”
                                                        </p>
                                                    )}
                                                </>
                                            ) : (
                                                <p className="mt-0.5 text-[11px] text-neutral-300">
                                                    Temporary duty reassignment
                                                </p>
                                            )}
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            )}
                        </div>
                        <div className="text-xs font-medium text-brand-muted truncate">
                            {member.rank ?? 'Firefighter'}
                        </div>
                    </div>
                </div>

                {/* Card Actions Dropdown */}
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <button
                            type="button"
                            id={`member-actions-btn-${member.id}`}
                            className="shrink-0 rounded-lg p-1.5 text-brand-muted hover:bg-neutral-100 hover:text-brand-navy transition-colors focus:outline-none"
                            aria-label={`Actions for ${member.name}`}
                        >
                            <MoreHorizontal size={16} />
                        </button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" className="w-56 bg-white text-brand-navy">
                        <DropdownMenuLabel className="text-xs font-medium text-brand-muted">
                            {member.name}
                        </DropdownMenuLabel>
                        <DropdownMenuSeparator />

                        {/* Move To Submenu */}
                        <DropdownMenuSub>
                            <DropdownMenuSubTrigger className="cursor-pointer gap-2 text-xs">
                                <ArrowRightLeft size={14} /> Move to…
                            </DropdownMenuSubTrigger>
                            <DropdownMenuSubContent className="w-52 bg-white text-brand-navy">
                                <DropdownMenuLabel className="text-[11px] font-normal text-brand-muted">
                                    Permanent platoon change
                                </DropdownMenuLabel>
                                <DropdownMenuSeparator />
                                {PERMANENT_MOVE_OPTIONS.map((opt) => {
                                    const isCurrent =
                                        member.base_group === opt.value ||
                                        (member.base_group === null && opt.value === null);

                                    return (
                                        <DropdownMenuItem
                                            key={opt.value ?? 'unassigned'}
                                            disabled={isCurrent}
                                            onClick={() =>
                                                handleMoveGroup(member, opt.value)
                                            }
                                            className="cursor-pointer flex flex-col items-start gap-0.5"
                                        >
                                            <span className="font-semibold text-xs text-brand-navy">
                                                {opt.label} {isCurrent ? '(Current)' : ''}
                                            </span>
                                            <span className="text-[10px] text-brand-muted">
                                                Permanent roster update
                                            </span>
                                        </DropdownMenuItem>
                                    );
                                })}
                            </DropdownMenuSubContent>
                        </DropdownMenuSub>

                        {/* Reassign For a Period */}
                        <DropdownMenuItem
                            className="cursor-pointer gap-2 text-xs"
                            onClick={() => openOverrideModal('reassign', member.id)}
                        >
                            <Calendar size={14} /> Reassign for a period…
                        </DropdownMenuItem>

                        {/* Swap With (only for base group A or B) */}
                        {(member.base_group === 'A' || member.base_group === 'B') && (
                            <DropdownMenuItem
                                className="cursor-pointer gap-2 text-xs"
                                onClick={() => openOverrideModal('swap', member.id)}
                            >
                                <ArrowLeftRight size={14} /> Swap with…
                            </DropdownMenuItem>
                        )}

                        {/* Remove Reassignment */}
                        {member.is_overridden && (
                            <>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem
                                    className="cursor-pointer gap-2 text-xs text-red-600 focus:text-red-700 focus:bg-red-50"
                                    onClick={() => handleRemoveOverrideForMember(member)}
                                >
                                    <Trash2 size={14} /> Remove reassignment
                                </DropdownMenuItem>
                            </>
                        )}
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        );
    };

    const isDutyAActive = week.activeGroup === 'A';
    const isDutyBActive = week.activeGroup === 'B';

    return (
        <PortalLayout title="Duty Schedule">
            <div className="space-y-6">
                {/* Page Header */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-brand-navy">
                            Duty Schedule
                        </h1>
                        <p className="mt-1 text-sm text-brand-muted">
                            {formatWeekRange(week.start, week.end)} ·{' '}
                            <span className="font-semibold text-brand-navy">
                                Duty {week.activeGroup} on duty
                            </span>
                        </p>
                    </div>

                    {/* Right Side: Week Navigator + Action Button */}
                    <div className="flex flex-wrap items-center gap-2.5">
                        <div className="flex items-center rounded-lg border border-neutral-300 bg-white p-1 shadow-2xs">
                            <button
                                type="button"
                                id="prev-week-btn"
                                onClick={() => goToOffset(-7)}
                                className="rounded-md p-1.5 text-brand-muted hover:bg-neutral-100 hover:text-brand-navy transition-colors"
                                title="Previous week"
                                aria-label="Previous week"
                            >
                                <ChevronLeft size={16} />
                            </button>
                            <span className="px-3 text-xs font-semibold text-brand-navy select-none">
                                {formatChipDate(week.start)} – {formatChipDate(week.end)}
                            </span>
                            <button
                                type="button"
                                id="next-week-btn"
                                onClick={() => goToOffset(7)}
                                className="rounded-md p-1.5 text-brand-muted hover:bg-neutral-100 hover:text-brand-navy transition-colors"
                                title="Next week"
                                aria-label="Next week"
                            >
                                <ChevronRight size={16} />
                            </button>
                        </div>

                        <button
                            type="button"
                            id="this-week-btn"
                            onClick={goToCurrentWeek}
                            disabled={week.isCurrent}
                            className={`rounded-lg border px-3 py-2 text-xs font-semibold transition-colors ${
                                week.isCurrent
                                    ? 'border-neutral-200 bg-neutral-100 text-neutral-400 cursor-default'
                                    : 'border-neutral-300 bg-white text-brand-navy hover:bg-neutral-50 shadow-2xs'
                            }`}
                        >
                            This week
                        </button>

                        <button
                            type="button"
                            id="reassign-swap-open-btn"
                            onClick={() => openOverrideModal('reassign')}
                            className="flex items-center gap-1.5 rounded-lg bg-brand-navy px-4 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-brand-navy-hover transition-colors"
                        >
                            <ArrowLeftRight size={14} /> Reassign / Swap
                        </button>
                    </div>
                </div>

                {/* Rotation Strip */}
                <div className="overflow-x-auto pb-1">
                    <div className="flex items-center gap-2 min-w-max">
                        <span className="text-xs font-semibold text-brand-muted uppercase tracking-wider pr-1">
                            Rotation:
                        </span>
                        {upcoming.map((u) => {
                            const isSelected = u.week_start === week.start;
                            const isA = u.group === 'A';

                            return (
                                <button
                                    key={u.week_start}
                                    type="button"
                                    id={`rotation-chip-${u.week_start}`}
                                    onClick={() => goToDate(u.week_start)}
                                    className={`flex items-center gap-1.5 rounded-full px-3 py-1 text-xs transition-all ${
                                        isSelected
                                            ? isA
                                                ? 'bg-indigo-600 text-white font-semibold ring-2 ring-indigo-500 ring-offset-1 shadow-sm'
                                                : 'bg-amber-600 text-white font-semibold ring-2 ring-amber-500 ring-offset-1 shadow-sm'
                                            : isA
                                              ? 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200/80'
                                              : 'bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200/80'
                                    }`}
                                >
                                    <span>{formatChipDate(u.week_start)}</span>
                                    <span>·</span>
                                    <span className="font-bold">Duty {u.group}</span>
                                </button>
                            );
                        })}
                    </div>
                </div>

                {/* Empty State if No Personnel in System */}
                {totalPersonnelCount === 0 ? (
                    <PortalCard className="p-12 text-center">
                        <Users className="mx-auto size-12 text-brand-muted/50" />
                        <h3 className="mt-3 text-base font-bold text-brand-navy">
                            No Personnel Registered
                        </h3>
                        <p className="mt-1 text-sm text-brand-muted">
                            Register personnel accounts first to populate the station duty roster.
                        </p>
                    </PortalCard>
                ) : (
                    /* Roster Board: 3 Columns like the Station's Wall Board */
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {/* COLUMN 1: Duty A */}
                        <div className="flex flex-col rounded-2xl border border-indigo-200/90 bg-indigo-50/20 p-4 shadow-2xs">
                            <div className="flex items-center justify-between pb-3 border-b border-indigo-100">
                                <div className="flex items-center gap-2">
                                    <div className="h-3 w-3 rounded-full bg-indigo-600" />
                                    <h2 className="text-base font-bold text-indigo-950">
                                        Duty A
                                    </h2>
                                    <span className="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700">
                                        {roster.A.length}
                                    </span>
                                </div>
                                {isDutyAActive ? (
                                    <span className="inline-flex items-center rounded-full border border-emerald-300 bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 shadow-2xs">
                                        <span className="relative flex h-2 w-2 mr-1.5">
                                            <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75" />
                                            <span className="relative inline-flex rounded-full h-2 w-2 bg-emerald-500" />
                                        </span>
                                        On Duty
                                    </span>
                                ) : (
                                    <span className="rounded-full border border-neutral-200 bg-neutral-100 px-2.5 py-0.5 text-xs font-medium text-neutral-500">
                                        Off Duty
                                    </span>
                                )}
                            </div>

                            <div className="mt-3.5 space-y-2.5 flex-1">
                                {roster.A.length === 0 ? (
                                    <div className="rounded-xl border border-dashed border-indigo-200 bg-white/50 p-6 text-center text-xs text-brand-muted">
                                        No personnel assigned to Duty A.
                                    </div>
                                ) : (
                                    roster.A.map((member) =>
                                        renderMemberCard(member, !isDutyAActive),
                                    )
                                )}
                            </div>
                        </div>

                        {/* COLUMN 2: Always on Duty (Command Group / Regular) */}
                        <div className="flex flex-col rounded-2xl border border-emerald-200/90 bg-emerald-50/20 p-4 shadow-2xs">
                            <div className="flex items-center justify-between pb-3 border-b border-emerald-100">
                                <div className="flex items-center gap-2">
                                    <div className="h-3 w-3 rounded-full bg-emerald-600" />
                                    <h2 className="text-base font-bold text-emerald-950">
                                        Always on Duty
                                    </h2>
                                    <span className="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800">
                                        {roster.regular.length}
                                    </span>
                                </div>
                                <span className="inline-flex items-center rounded-full border border-emerald-300 bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 shadow-2xs">
                                    <span className="relative flex h-2 w-2 mr-1.5">
                                        <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75" />
                                        <span className="relative inline-flex rounded-full h-2 w-2 bg-emerald-500" />
                                    </span>
                                    On Duty
                                </span>
                            </div>

                            <div className="mt-3.5 space-y-2.5 flex-1">
                                {roster.regular.length === 0 ? (
                                    <div className="rounded-xl border border-dashed border-emerald-200 bg-white/50 p-6 text-center text-xs text-brand-muted">
                                        No personnel assigned to Always on Duty.
                                    </div>
                                ) : (
                                    roster.regular.map((member) =>
                                        renderMemberCard(member, false),
                                    )
                                )}
                            </div>
                        </div>

                        {/* COLUMN 3: Duty B */}
                        <div className="flex flex-col rounded-2xl border border-amber-200/90 bg-amber-50/20 p-4 shadow-2xs">
                            <div className="flex items-center justify-between pb-3 border-b border-amber-100">
                                <div className="flex items-center gap-2">
                                    <div className="h-3 w-3 rounded-full bg-amber-600" />
                                    <h2 className="text-base font-bold text-amber-950">
                                        Duty B
                                    </h2>
                                    <span className="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">
                                        {roster.B.length}
                                    </span>
                                </div>
                                {isDutyBActive ? (
                                    <span className="inline-flex items-center rounded-full border border-emerald-300 bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 shadow-2xs">
                                        <span className="relative flex h-2 w-2 mr-1.5">
                                            <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75" />
                                            <span className="relative inline-flex rounded-full h-2 w-2 bg-emerald-500" />
                                        </span>
                                        On Duty
                                    </span>
                                ) : (
                                    <span className="rounded-full border border-neutral-200 bg-neutral-100 px-2.5 py-0.5 text-xs font-medium text-neutral-500">
                                        Off Duty
                                    </span>
                                )}
                            </div>

                            <div className="mt-3.5 space-y-2.5 flex-1">
                                {roster.B.length === 0 ? (
                                    <div className="rounded-xl border border-dashed border-amber-200 bg-white/50 p-6 text-center text-xs text-brand-muted">
                                        No personnel assigned to Duty B.
                                    </div>
                                ) : (
                                    roster.B.map((member) =>
                                        renderMemberCard(member, !isDutyBActive),
                                    )
                                )}
                            </div>
                        </div>
                    </div>
                )}

                {/* Below Board: 2 Smaller PortalCards Side-by-Side */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Detached / Unassigned Card */}
                    <PortalCard className="p-5">
                        <div className="flex items-center justify-between mb-4">
                            <div>
                                <h2 className="text-base font-bold text-brand-navy">
                                    Detached / Unassigned
                                </h2>
                                <p className="text-xs text-brand-muted">
                                    Personnel not currently active in Duty A, Duty B, or command rotation
                                </p>
                            </div>
                            <span className="rounded-full bg-neutral-100 px-2.5 py-0.5 text-xs font-semibold text-brand-muted">
                                {roster.detached.length + roster.unassigned.length} personnel
                            </span>
                        </div>

                        {roster.detached.length === 0 && roster.unassigned.length === 0 ? (
                            <div className="rounded-xl border border-dashed border-neutral-200 bg-neutral-50/50 p-8 text-center text-xs text-brand-muted">
                                No detached or unassigned personnel this week.
                            </div>
                        ) : (
                            <div className="space-y-2.5">
                                {roster.detached.map((m) => renderMemberCard(m, true))}
                                {roster.unassigned.map((m) => renderMemberCard(m, true))}
                            </div>
                        )}
                    </PortalCard>

                    {/* Reassignments this week Card */}
                    <PortalCard className="p-5">
                        <div className="flex items-center justify-between mb-4">
                            <div>
                                <h2 className="text-base font-bold text-brand-navy">
                                    Reassignments this week
                                </h2>
                                <p className="text-xs text-brand-muted">
                                    Active temporary overrides covering {formatChipDate(week.start)} – {formatChipDate(week.end)}
                                </p>
                            </div>
                            <span className="rounded-full bg-amber-50 border border-amber-200 px-2.5 py-0.5 text-xs font-semibold text-amber-800">
                                {overrides.length} active
                            </span>
                        </div>

                        {overrides.length === 0 ? (
                            <div className="rounded-xl border border-dashed border-neutral-200 bg-neutral-50/50 p-8 text-center text-xs text-brand-muted">
                                No temporary reassignments active for this week.
                            </div>
                        ) : (
                            <div className="space-y-2.5">
                                {overrides.map((ov) => (
                                    <div
                                        key={ov.override_id}
                                        className="flex items-center justify-between rounded-xl border border-neutral-200/80 bg-white p-3 shadow-2xs hover:shadow-xs transition-shadow"
                                    >
                                        <div className="min-w-0 pr-3">
                                            <div className="flex items-center gap-2 flex-wrap">
                                                <span className="text-sm font-semibold text-brand-navy">
                                                    {ov.name}
                                                </span>
                                                <span className="inline-flex items-center gap-1 rounded-md bg-neutral-100 px-2 py-0.5 text-[11px] font-medium text-brand-navy">
                                                    <span>{groupLabel(ov.from_group)}</span>
                                                    <span>→</span>
                                                    <span className="font-semibold text-brand-navy">
                                                        {groupLabel(ov.duty_group)}
                                                    </span>
                                                </span>
                                            </div>
                                            <div className="mt-1 flex items-center gap-2 text-xs text-brand-muted flex-wrap">
                                                <span>
                                                    {formatWeekRange(ov.starts_on, ov.ends_on)}
                                                </span>
                                                {ov.reason && (
                                                    <>
                                                        <span>•</span>
                                                        <span className="truncate italic">
                                                            “{ov.reason}”
                                                        </span>
                                                    </>
                                                )}
                                                {ov.created_by_name && (
                                                    <>
                                                        <span>•</span>
                                                        <span className="text-[11px]">
                                                            By {ov.created_by_name}
                                                        </span>
                                                    </>
                                                )}
                                            </div>
                                        </div>
                                        <button
                                            type="button"
                                            id={`delete-override-btn-${ov.override_id}`}
                                            onClick={() => setRemoveOverrideTarget(ov)}
                                            className="shrink-0 rounded-lg p-2 text-brand-muted hover:bg-red-50 hover:text-red-600 transition-colors"
                                            title="Remove reassignment"
                                            aria-label={`Remove reassignment for ${ov.name}`}
                                        >
                                            <Trash2 size={15} />
                                        </button>
                                    </div>
                                ))}
                            </div>
                        )}
                    </PortalCard>
                </div>
            </div>

            {/* Modals & Dialogs */}
            <DutyOverrideModal
                open={overrideModalOpen}
                onOpenChange={setOverrideModalOpen}
                allMembers={allMembers}
                groups={groups}
                defaultWeekStart={week.start}
                initialMode={modalPrefill.mode}
                initialUserId={modalPrefill.userId}
            />

            <RemoveOverrideDialog
                override={removeOverrideTarget}
                open={removeOverrideTarget !== null}
                onOpenChange={(open) => {
                    if (!open) {
setRemoveOverrideTarget(null);
}
                }}
            />
        </PortalLayout>
    );
}