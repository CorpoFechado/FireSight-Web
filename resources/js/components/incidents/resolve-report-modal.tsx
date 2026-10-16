import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { resolve } from '@/routes/incidents';

export function ResolveReportModal({
    reportId,
    open,
    onOpenChange,
}: {
    reportId: number;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const { post, processing, errors, clearErrors } = useForm<{
        status?: string;
    }>({});

    const submit = (event: FormEvent) => {
        event.preventDefault();

        post(resolve(reportId).url, {
            preserveScroll: true,
            onSuccess: () => onOpenChange(false),
        });
    };

    const handleOpenChange = (nextOpen: boolean) => {
        if (!nextOpen) {
            clearErrors();
        }

        onOpenChange(nextOpen);
    };

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogContent className="bg-white text-brand-navy sm:max-w-md">
                <DialogHeader>
                    <DialogTitle className="text-brand-navy">
                        Mark as Resolved
                    </DialogTitle>
                </DialogHeader>

                <form onSubmit={submit} className="space-y-4">
                    <p className="text-sm leading-relaxed text-brand-muted">
                        Confirm that this incident has been resolved. Assessment
                        details can be added from the resolved incident
                        afterward.
                    </p>

                    {errors.status && (
                        <p className="text-xs text-brand-red">
                            {errors.status}
                        </p>
                    )}

                    <DialogFooter>
                        <button
                            type="button"
                            onClick={() => handleOpenChange(false)}
                            className="rounded-lg px-4 py-2 text-sm font-semibold text-brand-muted"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="rounded-lg px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
                            style={{ background: '#2A9D8F' }}
                        >
                            {processing ? 'Resolving...' : 'Confirm Resolution'}
                        </button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
