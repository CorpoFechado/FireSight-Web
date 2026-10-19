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
import { formatWeekRange, groupLabel } from '@/lib/duty-schedule';
import type { Override } from '@/lib/duty-schedule';
import { destroy as destroyOverride } from '@/routes/dutySchedule/overrides';

export function RemoveOverrideDialog({
    override,
    open,
    onOpenChange,
}: {
    override: Override | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const [processing, setProcessing] = useState(false);

    if (!override) {
        return null;
    }

    const confirmDelete = () => {
        setProcessing(true);

        router.delete(destroyOverride(override.override_id).url, {
            preserveScroll: true,
            onError: (errors) =>
                toast.error(
                    Object.values(errors)[0] ??
                        "Couldn't remove this reassignment.",
                ),
            onFinish: () => {
                setProcessing(false);
                onOpenChange(false);
            },
        });
    };

    const range = formatWeekRange(override.starts_on, override.ends_on);
    const returnGroup = groupLabel(override.from_group);

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="bg-white text-brand-navy sm:max-w-sm">
                <DialogHeader>
                    <DialogTitle className="text-brand-navy">
                        Remove Reassignment?
                    </DialogTitle>
                </DialogHeader>

                <p className="text-sm text-brand-muted">
                    This will remove the temporary reassignment for{' '}
                    <span className="font-semibold text-brand-navy">
                        {override.name}
                    </span>{' '}
                    ({range}). They will return to {returnGroup}.
                </p>

                <DialogFooter className="mt-4 gap-2">
                    <button
                        type="button"
                        id="cancel-remove-override-btn"
                        onClick={() => onOpenChange(false)}
                        className="rounded-lg px-4 py-2 text-sm font-semibold text-brand-muted transition-colors hover:bg-neutral-100"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        id="confirm-remove-override-btn"
                        onClick={confirmDelete}
                        disabled={processing}
                        className="rounded-lg px-4 py-2 text-sm font-semibold text-white transition-colors disabled:opacity-50"
                        style={{ background: '#E63946' }}
                    >
                        {processing ? 'Removing…' : 'Remove Reassignment'}
                    </button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
