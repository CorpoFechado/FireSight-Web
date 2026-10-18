import { useForm } from '@inertiajs/react';
import { useEffect, useMemo, useState  } from 'react';
import type {FormEvent} from 'react';
import { toast } from 'sonner';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import {
    computeWeekEnd,
    formatWeekRange,
    groupLabel,
    snapToMonday
    
    
} from '@/lib/duty-schedule';
import type {GroupOption, Member} from '@/lib/duty-schedule';
import { swap as swapRoute } from '@/routes/dutySchedule';
import { store as storeOverrideRoute } from '@/routes/dutySchedule/overrides';

export function DutyOverrideModal({
    open,
    onOpenChange,
    allMembers,
    groups,
    defaultWeekStart,
    initialMode = 'reassign',
    initialUserId,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    allMembers: Member[];
    groups: GroupOption[];
    defaultWeekStart: string;
    initialMode?: 'reassign' | 'swap';
    initialUserId?: number;
}) {
    const [mode, setMode] = useState<'reassign' | 'swap'>(initialMode);
    const [reassignWeeks, setReassignWeeks] = useState(1);
    const [swapWeeks, setSwapWeeks] = useState(1);

    const initialMonday = useMemo(
        () => snapToMonday(defaultWeekStart),
        [defaultWeekStart],
    );

    // Form for "Reassign"
    const reassignForm = useForm({
        user_id: initialUserId ? String(initialUserId) : '',
        duty_group: '',
        starts_on: initialMonday,
        ends_on: computeWeekEnd(initialMonday, 1),
        reason: '',
    });

    // Form for "Swap"
    const swapForm = useForm({
        user_id: initialUserId ? String(initialUserId) : '',
        swap_with_id: '',
        starts_on: initialMonday,
        ends_on: computeWeekEnd(initialMonday, 1),
        reason: '',
    });

    // Members lookup map
    const memberMap = useMemo(() => {
        const map = new Map<number, Member>();

        for (const m of allMembers) {
            map.set(m.user_id, m);
        }

        return map;
    }, [allMembers]);

    // Reseed on open
    useEffect(() => {
        if (open) {
            // eslint-disable-next-line react-hooks/set-state-in-effect
            setMode(initialMode);
            const monday = snapToMonday(defaultWeekStart);
            setReassignWeeks(1);
            setSwapWeeks(1);

            const selectedMember = initialUserId
                ? memberMap.get(initialUserId)
                : undefined;

            // Compute default group that is different from base group
            const defaultTargetGroup =
                groups.find((g) => g.value !== selectedMember?.base_group)?.value ??
                '';

            reassignForm.setData({
                user_id: initialUserId ? String(initialUserId) : '',
                duty_group: defaultTargetGroup,
                starts_on: monday,
                ends_on: computeWeekEnd(monday, 1),
                reason: '',
            });
            reassignForm.clearErrors();

            swapForm.setData({
                user_id: initialUserId ? String(initialUserId) : '',
                swap_with_id: '',
                starts_on: monday,
                ends_on: computeWeekEnd(monday, 1),
                reason: '',
            });
            swapForm.clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, initialMode, initialUserId, defaultWeekStart]);

    // Reassign helpers
    const selectedReassignMember = reassignForm.data.user_id
        ? memberMap.get(Number(reassignForm.data.user_id))
        : undefined;

    const availableReassignGroups = useMemo(() => {
        if (!selectedReassignMember?.base_group) {
            return groups;
        }

        return groups.filter(
            (g) => g.value !== selectedReassignMember.base_group,
        );
    }, [groups, selectedReassignMember]);

    const handleReassignDateChange = (dateValue: string) => {
        if (!dateValue) {
return;
}

        const snappedMonday = snapToMonday(dateValue);
        const computedEnd = computeWeekEnd(snappedMonday, reassignWeeks);
        reassignForm.setData((prev) => ({
            ...prev,
            starts_on: snappedMonday,
            ends_on: computedEnd,
        }));
    };

    const handleReassignWeeksChange = (weeks: number) => {
        const clamped = Math.max(1, Math.min(12, weeks));
        setReassignWeeks(clamped);
        const computedEnd = computeWeekEnd(reassignForm.data.starts_on, clamped);
        reassignForm.setData('ends_on', computedEnd);
    };

    // Swap helpers
    const selectedSwapMemberA = swapForm.data.user_id
        ? memberMap.get(Number(swapForm.data.user_id))
        : undefined;

    const availableSwapMembersB = useMemo(() => {
        if (!selectedSwapMemberA) {
return [];
}

        const baseA = selectedSwapMemberA.base_group;

        if (baseA !== 'A' && baseA !== 'B') {
return [];
}

        const targetBase = baseA === 'A' ? 'B' : 'A';

        return allMembers.filter((m) => m.base_group === targetBase);
    }, [allMembers, selectedSwapMemberA]);

    const selectedSwapMemberB = swapForm.data.swap_with_id
        ? memberMap.get(Number(swapForm.data.swap_with_id))
        : undefined;

    const handleSwapDateChange = (dateValue: string) => {
        if (!dateValue) {
return;
}

        const snappedMonday = snapToMonday(dateValue);
        const computedEnd = computeWeekEnd(snappedMonday, swapWeeks);
        swapForm.setData((prev) => ({
            ...prev,
            starts_on: snappedMonday,
            ends_on: computedEnd,
        }));
    };

    const handleSwapWeeksChange = (weeks: number) => {
        const clamped = Math.max(1, Math.min(12, weeks));
        setSwapWeeks(clamped);
        const computedEnd = computeWeekEnd(swapForm.data.starts_on, clamped);
        swapForm.setData('ends_on', computedEnd);
    };

    const close = () => {
        reassignForm.reset();
        swapForm.reset();
        onOpenChange(false);
    };

    const submitReassign = (e: FormEvent) => {
        e.preventDefault();
        reassignForm.post(storeOverrideRoute().url, {
            preserveScroll: true,
            onSuccess: close,
            onError: (errs) => {
                const firstErr = Object.values(errs)[0];

                if (firstErr) {
toast.error(firstErr);
}
            },
        });
    };

    const submitSwap = (e: FormEvent) => {
        e.preventDefault();
        swapForm.post(swapRoute().url, {
            preserveScroll: true,
            onSuccess: close,
            onError: (errs) => {
                const firstErr = Object.values(errs)[0];

                if (firstErr) {
toast.error(firstErr);
}
            },
        });
    };

    return (
        <Dialog open={open} onOpenChange={close}>
            <DialogContent className="bg-white text-brand-navy sm:max-w-lg max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle className="text-xl font-bold text-brand-navy">
                        Reassign or Swap Platoon
                    </DialogTitle>
                </DialogHeader>

                {/* Mode Segmented Control */}
                <div className="flex rounded-lg bg-neutral-100 p-1 text-sm font-medium">
                    <button
                        type="button"
                        id="duty-override-mode-reassign-btn"
                        onClick={() => setMode('reassign')}
                        className={`flex-1 rounded-md py-1.5 text-center transition-all ${
                            mode === 'reassign'
                                ? 'bg-white font-semibold text-brand-navy shadow-sm'
                                : 'text-brand-muted hover:text-brand-navy'
                        }`}
                    >
                        Reassign
                    </button>
                    <button
                        type="button"
                        id="duty-override-mode-swap-btn"
                        onClick={() => setMode('swap')}
                        className={`flex-1 rounded-md py-1.5 text-center transition-all ${
                            mode === 'swap'
                                ? 'bg-white font-semibold text-brand-navy shadow-sm'
                                : 'text-brand-muted hover:text-brand-navy'
                        }`}
                    >
                        Swap
                    </button>
                </div>

                {mode === 'reassign' ? (
                    <form onSubmit={submitReassign} className="space-y-4">
                        {/* Personnel Select */}
                        <div>
                            <Label htmlFor="reassign-user-select" className="text-xs font-semibold uppercase text-brand-muted">
                                Personnel
                            </Label>
                            <select
                                id="reassign-user-select"
                                value={reassignForm.data.user_id}
                                onChange={(e) => {
                                    const nextUserId = e.target.value;
                                    const nextMember = nextUserId
                                        ? memberMap.get(Number(nextUserId))
                                        : undefined;
                                    const nextGroup =
                                        groups.find(
                                            (g) => g.value !== nextMember?.base_group,
                                        )?.value ?? '';
                                    reassignForm.setData((prev) => ({
                                        ...prev,
                                        user_id: nextUserId,
                                        duty_group: nextGroup,
                                    }));
                                }}
                                className="mt-1 w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-brand-navy focus:border-brand-navy focus:outline-none focus:ring-1 focus:ring-brand-navy"
                                required
                            >
                                <option value="" disabled>
                                    Select personnel…
                                </option>
                                {allMembers.map((m) => (
                                    <option key={m.user_id} value={m.user_id}>
                                        {m.name} {m.rank ? `— ${m.rank}` : ''} ({groupLabel(m.base_group)})
                                    </option>
                                ))}
                            </select>
                            {reassignForm.errors.user_id && (
                                <p className="mt-1 text-xs text-red-600">
                                    {reassignForm.errors.user_id}
                                </p>
                            )}
                        </div>

                        {/* Assign To Group */}
                        <div>
                            <Label htmlFor="reassign-group-select" className="text-xs font-semibold uppercase text-brand-muted">
                                Assign To
                            </Label>
                            <select
                                id="reassign-group-select"
                                value={reassignForm.data.duty_group}
                                onChange={(e) =>
                                    reassignForm.setData('duty_group', e.target.value)
                                }
                                className="mt-1 w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-brand-navy focus:border-brand-navy focus:outline-none focus:ring-1 focus:ring-brand-navy"
                                required
                            >
                                <option value="" disabled>
                                    Select duty group…
                                </option>
                                {availableReassignGroups.map((g) => (
                                    <option key={g.value} value={g.value}>
                                        {g.label}
                                    </option>
                                ))}
                            </select>
                            {reassignForm.errors.duty_group && (
                                <p className="mt-1 text-xs text-red-600">
                                    {reassignForm.errors.duty_group}
                                </p>
                            )}
                        </div>

                        {/* Date & Duration Controls */}
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <Label htmlFor="reassign-start-date" className="text-xs font-semibold uppercase text-brand-muted">
                                    From Week (Monday)
                                </Label>
                                <input
                                    id="reassign-start-date"
                                    type="date"
                                    value={reassignForm.data.starts_on}
                                    onChange={(e) =>
                                        handleReassignDateChange(e.target.value)
                                    }
                                    className="mt-1 w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-brand-navy focus:border-brand-navy focus:outline-none focus:ring-1 focus:ring-brand-navy"
                                    required
                                />
                                {reassignForm.errors.starts_on && (
                                    <p className="mt-1 text-xs text-red-600">
                                        {reassignForm.errors.starts_on}
                                    </p>
                                )}
                            </div>

                            <div>
                                <Label htmlFor="reassign-duration-weeks" className="text-xs font-semibold uppercase text-brand-muted">
                                    Number of Weeks
                                </Label>
                                <select
                                    id="reassign-duration-weeks"
                                    value={reassignWeeks}
                                    onChange={(e) =>
                                        handleReassignWeeksChange(Number(e.target.value))
                                    }
                                    className="mt-1 w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-brand-navy focus:border-brand-navy focus:outline-none focus:ring-1 focus:ring-brand-navy"
                                >
                                    {Array.from({ length: 12 }, (_, i) => i + 1).map((w) => (
                                        <option key={w} value={w}>
                                            {w} {w === 1 ? 'week' : 'weeks'}
                                        </option>
                                    ))}
                                </select>
                                {reassignForm.errors.ends_on && (
                                    <p className="mt-1 text-xs text-red-600">
                                        {reassignForm.errors.ends_on}
                                    </p>
                                )}
                            </div>
                        </div>

                        {/* Reason */}
                        <div>
                            <Label htmlFor="reassign-reason-input" className="text-xs font-semibold uppercase text-brand-muted">
                                Reason (Optional)
                            </Label>
                            <input
                                id="reassign-reason-input"
                                type="text"
                                maxLength={255}
                                placeholder="e.g. Covering medical leave, training course"
                                value={reassignForm.data.reason}
                                onChange={(e) =>
                                    reassignForm.setData('reason', e.target.value)
                                }
                                className="mt-1 w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-brand-navy focus:border-brand-navy focus:outline-none focus:ring-1 focus:ring-brand-navy"
                            />
                            {reassignForm.errors.reason && (
                                <p className="mt-1 text-xs text-red-600">
                                    {reassignForm.errors.reason}
                                </p>
                            )}
                        </div>

                        {/* Preview Box */}
                        {selectedReassignMember && reassignForm.data.duty_group && (
                            <div className="rounded-lg border border-brand-navy/15 bg-brand-navy/5 p-3 text-xs text-brand-navy leading-relaxed">
                                <span className="font-semibold">{selectedReassignMember.name}</span> will be on{' '}
                                <span className="font-semibold">{groupLabel(reassignForm.data.duty_group)}</span> from{' '}
                                <span className="font-semibold">{formatWeekRange(reassignForm.data.starts_on, reassignForm.data.ends_on)}</span>.
                            </div>
                        )}

                        <DialogFooter className="mt-6 gap-2">
                            <button
                                type="button"
                                id="reassign-cancel-btn"
                                onClick={close}
                                className="rounded-lg px-4 py-2 text-sm font-semibold text-brand-muted hover:bg-neutral-100 transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                id="reassign-submit-btn"
                                disabled={reassignForm.processing || !reassignForm.data.user_id || !reassignForm.data.duty_group}
                                className="rounded-lg bg-brand-navy px-4 py-2 text-sm font-semibold text-white hover:bg-brand-navy-hover transition-colors disabled:opacity-50"
                            >
                                {reassignForm.processing ? 'Saving…' : 'Save Reassignment'}
                            </button>
                        </DialogFooter>
                    </form>
                ) : (
                    <form onSubmit={submitSwap} className="space-y-4">
                        {/* Person 1 Select */}
                        <div>
                            <Label htmlFor="swap-person-1-select" className="text-xs font-semibold uppercase text-brand-muted">
                                Person 1 (Platoon A or B)
                            </Label>
                            <select
                                id="swap-person-1-select"
                                value={swapForm.data.user_id}
                                onChange={(e) => {
                                    const nextA = e.target.value;
                                    swapForm.setData((prev) => ({
                                        ...prev,
                                        user_id: nextA,
                                        swap_with_id: '',
                                    }));
                                }}
                                className="mt-1 w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-brand-navy focus:border-brand-navy focus:outline-none focus:ring-1 focus:ring-brand-navy"
                                required
                            >
                                <option value="" disabled>
                                    Select first person…
                                </option>
                                {allMembers
                                    .filter((m) => m.base_group === 'A' || m.base_group === 'B')
                                    .map((m) => (
                                        <option key={m.user_id} value={m.user_id}>
                                            {m.name} {m.rank ? `— ${m.rank}` : ''} ({groupLabel(m.base_group)})
                                        </option>
                                    ))}
                            </select>
                            {swapForm.errors.user_id && (
                                <p className="mt-1 text-xs text-red-600">
                                    {swapForm.errors.user_id}
                                </p>
                            )}
                        </div>

                        {/* Person 2 Select (Opposite Platoon) */}
                        <div>
                            <Label htmlFor="swap-person-2-select" className="text-xs font-semibold uppercase text-brand-muted">
                                Swap With (Opposite Platoon)
                            </Label>
                            <select
                                id="swap-person-2-select"
                                value={swapForm.data.swap_with_id}
                                onChange={(e) =>
                                    swapForm.setData('swap_with_id', e.target.value)
                                }
                                disabled={!selectedSwapMemberA || (selectedSwapMemberA.base_group !== 'A' && selectedSwapMemberA.base_group !== 'B')}
                                className="mt-1 w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-brand-navy focus:border-brand-navy focus:outline-none focus:ring-1 focus:ring-brand-navy disabled:bg-neutral-100 disabled:text-neutral-400"
                                required
                            >
                                <option value="" disabled>
                                    {selectedSwapMemberA
                                        ? `Select member from Duty ${selectedSwapMemberA.base_group === 'A' ? 'B' : 'A'}…`
                                        : 'Select Person 1 first…'}
                                </option>
                                {availableSwapMembersB.map((m) => (
                                    <option key={m.user_id} value={m.user_id}>
                                        {m.name} {m.rank ? `— ${m.rank}` : ''} ({groupLabel(m.base_group)})
                                    </option>
                                ))}
                            </select>
                            {swapForm.errors.swap_with_id && (
                                <p className="mt-1 text-xs text-red-600">
                                    {swapForm.errors.swap_with_id}
                                </p>
                            )}
                        </div>

                        {/* Date & Duration Controls */}
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <Label htmlFor="swap-start-date" className="text-xs font-semibold uppercase text-brand-muted">
                                    From Week (Monday)
                                </Label>
                                <input
                                    id="swap-start-date"
                                    type="date"
                                    value={swapForm.data.starts_on}
                                    onChange={(e) =>
                                        handleSwapDateChange(e.target.value)
                                    }
                                    className="mt-1 w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-brand-navy focus:border-brand-navy focus:outline-none focus:ring-1 focus:ring-brand-navy"
                                    required
                                />
                                {swapForm.errors.starts_on && (
                                    <p className="mt-1 text-xs text-red-600">
                                        {swapForm.errors.starts_on}
                                    </p>
                                )}
                            </div>

                            <div>
                                <Label htmlFor="swap-duration-weeks" className="text-xs font-semibold uppercase text-brand-muted">
                                    Number of Weeks
                                </Label>
                                <select
                                    id="swap-duration-weeks"
                                    value={swapWeeks}
                                    onChange={(e) =>
                                        handleSwapWeeksChange(Number(e.target.value))
                                    }
                                    className="mt-1 w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-brand-navy focus:border-brand-navy focus:outline-none focus:ring-1 focus:ring-brand-navy"
                                >
                                    {Array.from({ length: 12 }, (_, i) => i + 1).map((w) => (
                                        <option key={w} value={w}>
                                            {w} {w === 1 ? 'week' : 'weeks'}
                                        </option>
                                    ))}
                                </select>
                                {swapForm.errors.ends_on && (
                                    <p className="mt-1 text-xs text-red-600">
                                        {swapForm.errors.ends_on}
                                    </p>
                                )}
                            </div>
                        </div>

                        {/* Reason */}
                        <div>
                            <Label htmlFor="swap-reason-input" className="text-xs font-semibold uppercase text-brand-muted">
                                Reason (Optional)
                            </Label>
                            <input
                                id="swap-reason-input"
                                type="text"
                                maxLength={255}
                                placeholder="e.g. Shift mutual exchange"
                                value={swapForm.data.reason}
                                onChange={(e) =>
                                    swapForm.setData('reason', e.target.value)
                                }
                                className="mt-1 w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-brand-navy focus:border-brand-navy focus:outline-none focus:ring-1 focus:ring-brand-navy"
                            />
                            {swapForm.errors.reason && (
                                <p className="mt-1 text-xs text-red-600">
                                    {swapForm.errors.reason}
                                </p>
                            )}
                        </div>

                        {/* Swap Preview Box */}
                        {selectedSwapMemberA && selectedSwapMemberB && (
                            <div className="rounded-lg border border-brand-navy/15 bg-brand-navy/5 p-3 text-xs text-brand-navy leading-relaxed">
                                <span className="font-semibold">{selectedSwapMemberA.name}</span> (Duty {selectedSwapMemberA.base_group}) ⇄{' '}
                                <span className="font-semibold">{selectedSwapMemberB.name}</span> (Duty {selectedSwapMemberB.base_group}) for{' '}
                                <span className="font-semibold">{formatWeekRange(swapForm.data.starts_on, swapForm.data.ends_on)}</span>.
                            </div>
                        )}

                        <DialogFooter className="mt-6 gap-2">
                            <button
                                type="button"
                                id="swap-cancel-btn"
                                onClick={close}
                                className="rounded-lg px-4 py-2 text-sm font-semibold text-brand-muted hover:bg-neutral-100 transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                id="swap-submit-btn"
                                disabled={swapForm.processing || !swapForm.data.user_id || !swapForm.data.swap_with_id}
                                className="rounded-lg bg-brand-navy px-4 py-2 text-sm font-semibold text-white hover:bg-brand-navy-hover transition-colors disabled:opacity-50"
                            >
                                {swapForm.processing ? 'Saving…' : 'Save Swap'}
                            </button>
                        </DialogFooter>
                    </form>
                )}
            </DialogContent>
        </Dialog>
    );
}
