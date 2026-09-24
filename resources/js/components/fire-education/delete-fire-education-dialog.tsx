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
import { destroy } from '@/routes/fireEducation';
import type { FireEducationRow } from './fire-education-form-modal';

export function DeleteFireEducationDialog({
    content,
    open,
    onOpenChange,
}: {
    content: FireEducationRow | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const [processing, setProcessing] = useState(false);

    if (!content) {
        return null;
    }

    const confirmDelete = () => {
        setProcessing(true);

        router.delete(destroy(content.id).url, {
            preserveScroll: true,
            onError: (errors) =>
                toast.error(
                    Object.values(errors)[0] ??
                        "Couldn't delete this educational content.",
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
                        Delete Article?
                    </DialogTitle>
                </DialogHeader>

                <p className="text-sm text-brand-muted">
                    This will permanently remove{' '}
                    <span className="font-semibold text-brand-navy">
                        "{content.title}"
                    </span>{' '}
                    from Fire Education. Residents will no longer see it on the
                    mobile app. This action cannot be undone.
                </p>

                <DialogFooter>
                    <button
                        type="button"
                        onClick={() => onOpenChange(false)}
                        className="rounded-lg px-4 py-2 text-sm font-semibold text-brand-muted hover:text-brand-navy"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        onClick={confirmDelete}
                        disabled={processing}
                        className="rounded-lg bg-brand-red px-4 py-2 text-sm font-semibold text-white transition-opacity hover:opacity-90 disabled:opacity-50"
                    >
                        {processing ? 'Deleting…' : 'Delete'}
                    </button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
