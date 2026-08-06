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
import { destroy } from '@/routes/personnel';
import type { PersonnelRow } from './personnel-form-modal';

export function DeletePersonnelDialog({
    personnel,
    open,
    onOpenChange,
}: {
    personnel: PersonnelRow | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const [processing, setProcessing] = useState(false);

    if (!personnel) {
        return null;
    }

    const confirmDelete = () => {
        setProcessing(true);

        router.delete(destroy(personnel.id).url, {
            preserveScroll: true,
            onError: (errors) =>
                toast.error(
                    Object.values(errors)[0] ?? "Couldn't delete this account.",
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
                        Delete Personnel?
                    </DialogTitle>
                </DialogHeader>

                <p className="text-sm text-brand-muted">
                    This will permanently remove{' '}
                    <span className="font-semibold text-brand-navy">
                        {personnel.name}
                    </span>
                    &apos;s account ({personnel.employee_number ?? '—'}). This
                    can&apos;t be undone.
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
                        {processing ? 'Deleting…' : 'Delete'}
                    </button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
