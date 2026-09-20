import { useForm } from '@inertiajs/react';
import { useEffect, type FormEvent } from 'react';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { store, update } from '@/routes/dutySchedule';
import {
    presetKeyFor,
    SHIFT_PRESETS,
    type ShiftPresetKey,
} from '@/lib/duty-schedule';

export type ShiftRow = {
    schedule_id: number;
    user_id: number;
    name: string | null;
    duty_date: string;
    time_start: string;
    time_end: string;
};

export type PersonnelOption = {
    id: number;
    name: string;
    rank: string | null;
};

export function DutyShiftFormModal({
    mode,
    shift,
    personnel,
    prefill,
    open,
    onOpenChange,
}: {
    mode: 'create' | 'edit';
    shift?: ShiftRow;
    personnel: PersonnelOption[];
    /** Pre-fill for the "+" hover-add case: personnel id + date. */
    prefill?: { userId: number; date: string };
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    // NOTE: no fallback to personnel[0] — an empty string forces the
    // admin to make an explicit choice instead of silently assigning
    // whoever happens to be first in the list.
    const seed = () => {
        const matchingPreset = shift
            ? presetKeyFor(shift.time_start, shift.time_end)
            : null;

        return {
            user_id: shift?.user_id
                ? String(shift.user_id)
                : prefill?.userId
                    ? String(prefill.userId)
                    : '',
            duty_date: shift?.duty_date ?? prefill?.date ?? '',
            // If editing a shift whose times don't match either preset (e.g.
            // left over from before this change), fall back to blank so the
            // admin has to consciously pick one rather than silently keeping
            // an odd custom range.
            time_start: matchingPreset ? SHIFT_PRESETS[matchingPreset].time_start : '',
            time_end: matchingPreset ? SHIFT_PRESETS[matchingPreset].time_end : '',
        };
    };

    const { data, setData, post, patch, processing, errors, reset, clearErrors } =
        useForm(seed());

    // Re-seed the form whenever the modal is (re)opened so stale edits
    // from a previous open don't leak in.
    useEffect(() => {
        if (open) {
            setData(seed());
            clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, shift?.schedule_id, prefill?.userId, prefill?.date]);

    const isValid =
        data.user_id !== '' &&
        data.duty_date.trim() !== '' &&
        data.time_start !== '' &&
        data.time_end !== '';

    const close = () => {
        reset();
        onOpenChange(false);
    };

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();

        if (!isValid) {
            return;
        }

        if (mode === 'create') {
            post(store().url, {
                preserveScroll: true,
                onSuccess: close,
            });
        } else {
            if (!shift) {
                return;
            }

            patch(update(shift.schedule_id).url, {
                preserveScroll: true,
                onSuccess: close,
            });
        }
    };

    return (
        <Dialog open={open} onOpenChange={close}>
            <DialogContent className="bg-white text-brand-navy sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle className="text-brand-navy">
                        {mode === 'create' ? 'Add Duty Shift' : 'Edit Duty Shift'}
                    </DialogTitle>
                </DialogHeader>

                <form onSubmit={handleSubmit} className="space-y-4">
                    {/* Personnel picker */}
                    <div className="space-y-1.5">
                        <Label>Personnel</Label>
                        <Select
                            value={data.user_id}
                            onValueChange={(v) => setData('user_id', v)}
                        >
                            <SelectTrigger className="w-full">
                                <SelectValue placeholder="Select personnel…" />
                            </SelectTrigger>
                            <SelectContent className="bg-white text-brand-navy">
                                {personnel.map((p) => (
                                    <SelectItem key={p.id} value={String(p.id)}>
                                        {p.name}
                                        {p.rank ? ` — ${p.rank}` : ''}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.user_id && (
                            <p className="text-xs text-brand-red">{errors.user_id}</p>
                        )}
                    </div>

                    {/* Date */}
                    <div className="space-y-1.5">
                        <Label>Date</Label>
                        <input
                            type="date"
                            value={data.duty_date}
                            onChange={(e) => setData('duty_date', e.target.value)}
                            className="h-9 w-full rounded-md border px-3 text-sm outline-none"
                            style={{ borderColor: 'rgba(43,45,66,0.15)' }}
                        />
                        {errors.duty_date && (
                            <p className="text-xs text-brand-red">
                                {errors.duty_date}
                            </p>
                        )}
                    </div>

                    {/* Shift preset */}
                    <div className="space-y-1.5">
                        <Label>Shift</Label>
                        <div className="grid grid-cols-2 gap-3">
                            {(Object.keys(SHIFT_PRESETS) as ShiftPresetKey[]).map(
                                (key) => {
                                    const preset = SHIFT_PRESETS[key];
                                    const selected =
                                        data.time_start === preset.time_start &&
                                        data.time_end === preset.time_end;

                                    return (
                                        <button
                                            key={key}
                                            type="button"
                                            onClick={() =>
                                                setData((prev) => ({
                                                    ...prev,
                                                    time_start: preset.time_start,
                                                    time_end: preset.time_end,
                                                }))
                                            }
                                            className="rounded-lg border px-3 py-2.5 text-left"
                                            style={{
                                                borderColor: selected
                                                    ? '#1D3557'
                                                    : 'rgba(43,45,66,0.15)',
                                                background: selected
                                                    ? 'rgba(29,53,87,0.06)'
                                                    : 'white',
                                            }}
                                        >
                                            <p className="text-sm font-semibold text-brand-navy">
                                                {preset.label}
                                            </p>
                                            <p className="text-xs text-brand-muted">
                                                {preset.sublabel}
                                            </p>
                                        </button>
                                    );
                                },
                            )}
                        </div>
                        {errors.time_start && (
                            <p className="text-xs text-brand-red">
                                {errors.time_start}
                            </p>
                        )}
                        {errors.time_end && (
                            <p className="text-xs text-brand-red">
                                {errors.time_end}
                            </p>
                        )}
                    </div>

                    <DialogFooter>
                        <button
                            type="button"
                            onClick={close}
                            className="rounded-lg px-4 py-2 text-sm font-semibold text-brand-muted"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            disabled={processing || !isValid}
                            className="rounded-lg bg-brand-navy px-4 py-2 text-sm font-semibold text-white hover:bg-brand-navy-hover disabled:opacity-50"
                        >
                            {processing
                                ? mode === 'create'
                                    ? 'Adding…'
                                    : 'Saving…'
                                : mode === 'create'
                                    ? 'Add Shift'
                                    : 'Save Changes'}
                        </button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}