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
import { destroy } from '@/routes/barangayContacts';
import type { ContactRow } from './contact-form-modal';

export function DeleteContactDialog({
    contact,
    open,
    onOpenChange,
}: {
    contact: ContactRow | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const [processing, setProcessing] = useState(false);

    if (!contact) {
        return null;
    }

    const confirmDelete = () => {
        setProcessing(true);

        router.delete(destroy(contact.id).url, {
            preserveScroll: true,
            onError: (errors) =>
                toast.error(
                    Object.values(errors)[0] ?? "Couldn't delete this contact.",
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
                        Delete Contact?
                    </DialogTitle>
                </DialogHeader>

                <p className="text-sm text-brand-muted">
                    This will permanently remove{' '}
                    <span className="font-semibold text-brand-navy">
                        {contact.name}
                    </span>{' '}
                    ({contact.role}) from Barangay Contacts. This can&apos;t be undone.
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
