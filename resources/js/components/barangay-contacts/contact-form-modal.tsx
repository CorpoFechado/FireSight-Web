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
import { store, update } from '@/routes/barangayContacts';

export type ContactRow = {
    id: number;
    barangay_id: number;
    barangay_name: string | null;
    name: string;
    role: string;
    phone_number: string;
};

export type BarangayOption = {
    barangay_id: number;
    barangay_name: string;
};

export function ContactFormModal({
    mode,
    contact,
    barangays,
    open,
    onOpenChange,
}: {
    mode: 'create' | 'edit';
    contact?: ContactRow;
    barangays: BarangayOption[];
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const {
        data,
        setData,
        post,
        patch,
        processing,
        errors,
        reset,
        clearErrors,
    } = useForm({
        barangay_id: contact?.barangay_id?.toString() ?? '',
        name: contact?.name ?? '',
        role: contact?.role ?? '',
        phone_number: contact?.phone_number ?? '',
    });

    // Re-seed the form whenever the modal is (re)opened so stale edits
    // from a previous open don't leak in.
    useEffect(() => {
        if (open) {
            setData({
                barangay_id: contact?.barangay_id?.toString() ?? '',
                name: contact?.name ?? '',
                role: contact?.role ?? '',
                phone_number: contact?.phone_number ?? '',
            });
            clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, contact?.id]);

    const isValid =
        data.barangay_id !== '' &&
        data.name.trim() !== '' &&
        data.role.trim() !== '' &&
        data.phone_number.trim() !== '';

    const close = () => {
        reset();
        onOpenChange(false);
    };

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();

        if (mode === 'create') {
            post(store().url, {
                preserveScroll: true,
                onSuccess: close,
            });
        } else {
            if (!contact) {
                return;
            }
            patch(update(contact.id).url, {
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
                        {mode === 'create' ? 'Add Barangay Contact' : 'Edit Barangay Contact'}
                    </DialogTitle>
                </DialogHeader>

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="space-y-1.5">
                        <Label>Barangay</Label>
                        <Select
                            value={data.barangay_id}
                            onValueChange={(v) => setData('barangay_id', v)}
                        >
                            <SelectTrigger className="w-full">
                                <SelectValue placeholder="Select barangay" />
                            </SelectTrigger>
                            <SelectContent className="bg-white text-brand-navy">
                                {barangays.map((b) => (
                                    <SelectItem
                                        key={b.barangay_id}
                                        value={b.barangay_id.toString()}
                                    >
                                        {b.barangay_name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.barangay_id && (
                            <p className="text-xs text-brand-red">{errors.barangay_id}</p>
                        )}
                    </div>

                    <div className="grid grid-cols-2 gap-3">
                        <div className="space-y-1.5">
                            <Label>Name</Label>
                            <input
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                placeholder="Enter full name"
                                className="h-9 w-full rounded-md border px-3 text-sm outline-none"
                                style={{ borderColor: 'rgba(43,45,66,0.15)' }}
                            />
                            {errors.name && (
                                <p className="text-xs text-brand-red">{errors.name}</p>
                            )}
                        </div>

                        <div className="space-y-1.5">
                            <Label>Role / Position</Label>
                            <input
                                value={data.role}
                                onChange={(e) => setData('role', e.target.value)}
                                placeholder="e.g. Barangay Captain"
                                className="h-9 w-full rounded-md border px-3 text-sm outline-none"
                                style={{ borderColor: 'rgba(43,45,66,0.15)' }}
                            />
                            {errors.role && (
                                <p className="text-xs text-brand-red">{errors.role}</p>
                            )}
                        </div>

                        <div className="space-y-1.5">
                            <Label>Phone Number</Label>
                            <input
                                value={data.phone_number}
                                onChange={(e) => setData('phone_number', e.target.value)}
                                placeholder="Enter phone number"
                                className="h-9 w-full rounded-md border px-3 text-sm outline-none"
                                style={{ borderColor: 'rgba(43,45,66,0.15)' }}
                            />
                            {errors.phone_number && (
                                <p className="text-xs text-brand-red">{errors.phone_number}</p>
                            )}
                        </div>
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
                            className="rounded-lg bg-brand-navy px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
                        >
                            {processing
                                ? mode === 'create'
                                    ? 'Adding…'
                                    : 'Saving…'
                                : mode === 'create'
                                  ? 'Add Contact'
                                  : 'Save Changes'}
                        </button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
