import { router } from '@inertiajs/react';
import { useState } from 'react';
import { toast } from 'sonner';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { destroy } from '@/routes/dutySchedule';
import type { ShiftRow } from './duty-shift-form-modal';

export function DeleteShiftDialog({
    shift,
    open,
    onOpenChange,
}: {
    shift: ShiftRow | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const [processing, setProcessing] = useState(false);

    if (!shift) {
        return null;
    }

    const confirmDelete = () => {
        setProcessing(true);

        router.delete(destroy(shift.schedule_id).url, {
            preserveScroll: true,
            onError: (errors) =>
                toast.error(
                    Object.values(errors)[0] ?? "Couldn't remove this shift.",
                ),
            onFinish: () => {
                setProcessing(false);
                onOpenChange(false);
            },
        });
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="bg-white text-brand-navy sm:max-w-sm">
                <DialogHeader>
                    <DialogTitle className="text-brand-navy">
                        Remove Shift?
                    </DialogTitle>
                </DialogHeader>

                <p className="text-sm text-brand-muted">
                    This will permanently remove{' '}
                    <span className="font-semibold text-brand-navy">
                        {shift.name ?? 'this personnel'}
                    </span>
                    &apos;s shift on{' '}
                    <span className="font-semibold text-brand-navy">
                        {shift.duty_date}
                    </span>{' '}
                    ({shift.time_start} – {shift.time_end}). This can&apos;t be
                    undone.
                </p>

                <DialogFooter>
                    <button
                        type="button"
                        onClick={() => onOpenChange(false)}
                        className="rounded-lg px-4 py-2 text-sm font-semibold text-brand-muted"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        onClick={confirmDelete}
                        disabled={processing}
                        className="rounded-lg px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
                        style={{ background: '#E63946' }}
                    >
                        {processing ? 'Removing…' : 'Remove Shift'}
                    </button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}