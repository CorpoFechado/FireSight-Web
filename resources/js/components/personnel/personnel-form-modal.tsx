import { useForm } from '@inertiajs/react';
import { useEffect, useState, type FormEvent } from 'react';
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
import { store, update } from '@/routes/personnel';
import type { PersonnelRole, PersonnelStatus } from '@/lib/fire-status';

export type PersonnelRow = {
    id: number;
    employee_number: string | null;
    name: string;
    rank: string | null;
    role: PersonnelRole;
    status: PersonnelStatus;
    contact_number: string | null;
    email: string;
};

const ROLE_OPTIONS: { value: PersonnelRole; label: string }[] = [
    { value: 'bfp_personnel', label: 'Personnel' },
    { value: 'bfp_admin', label: 'Admin' },
];

const STATUS_OPTIONS: { value: PersonnelStatus; label: string }[] = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];

export function PersonnelFormModal({
    mode,
    personnel,
    open,
    onOpenChange,
}: {
    mode: 'create' | 'edit';
    personnel?: PersonnelRow;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const [confirming, setConfirming] = useState(false);

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
        full_name: personnel?.name ?? '',
        rank: personnel?.rank ?? '',
        contact_number: personnel?.contact_number ?? '',
        email: personnel?.email ?? '',
        role: personnel?.role ?? 'bfp_personnel',
        status: personnel?.status ?? 'active',
    });

    // Re-seed the form whenever the modal is (re)opened, so stale edits
    // from a previous open don't leak in.
    useEffect(() => {
        if (open) {
            setData({
                full_name: personnel?.name ?? '',
                rank: personnel?.rank ?? '',
                contact_number: personnel?.contact_number ?? '',
                email: personnel?.email ?? '',
                role: personnel?.role ?? 'bfp_personnel',
                status: personnel?.status ?? 'active',
            });
            setConfirming(false);
            clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, personnel?.id]);

    const isValid =
        data.full_name.trim() !== '' &&
        data.rank.trim() !== '' &&
        data.contact_number.trim() !== '' &&
        data.email.trim() !== '';

    const close = () => {
        reset();
        onOpenChange(false);
    };

    const submitCreate = () => {
        post(store().url, {
            preserveScroll: true,
            // The new account's temp password comes back as a flash toast
            // (see PersonnelController::store) — the modal just closes.
            onSuccess: close,
            onError: () => setConfirming(false),
        });
    };

    const submitUpdate = (e: FormEvent) => {
        e.preventDefault();

        if (!personnel) {
            return;
        }

        patch(update(personnel.id).url, {
            preserveScroll: true,
            onSuccess: close,
        });
    };

    const handlePrimarySubmit = (e: FormEvent) => {
        e.preventDefault();

        if (mode === 'edit') {
            submitUpdate(e);
            return;
        }

        setConfirming(true);
    };

    return (
        <Dialog open={open} onOpenChange={close}>
            <DialogContent className="bg-white text-brand-navy sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle className="text-brand-navy">
                        {mode === 'create'
                            ? 'Add New Personnel'
                            : 'Edit Personnel'}
                    </DialogTitle>
                </DialogHeader>

                <form onSubmit={handlePrimarySubmit} className="space-y-4">
                    <div className="grid grid-cols-2 gap-3">
                        <div className="space-y-1.5">
                            <Label>Full Name</Label>
                            <input
                                value={data.full_name}
                                onChange={(e) =>
                                    setData('full_name', e.target.value)
                                }
                                placeholder="Enter full name"
                                className="h-9 w-full rounded-md border px-3 text-sm outline-none"
                                style={{ borderColor: 'rgba(43,45,66,0.15)' }}
                            />
                            {errors.full_name && (
                                <p className="text-xs text-brand-red">
                                    {errors.full_name}
                                </p>
                            )}
                        </div>

                        <div className="space-y-1.5">
                            <Label>Rank</Label>
                            <input
                                value={data.rank}
                                onChange={(e) =>
                                    setData('rank', e.target.value)
                                }
                                placeholder="Enter rank"
                                className="h-9 w-full rounded-md border px-3 text-sm outline-none"
                                style={{ borderColor: 'rgba(43,45,66,0.15)' }}
                            />
                            {errors.rank && (
                                <p className="text-xs text-brand-red">
                                    {errors.rank}
                                </p>
                            )}
                        </div>

                        <div className="space-y-1.5">
                            <Label>Contact Number</Label>
                            <input
                                value={data.contact_number}
                                onChange={(e) =>
                                    setData('contact_number', e.target.value)
                                }
                                placeholder="Enter contact number"
                                className="h-9 w-full rounded-md border px-3 text-sm outline-none"
                                style={{ borderColor: 'rgba(43,45,66,0.15)' }}
                            />
                            {errors.contact_number && (
                                <p className="text-xs text-brand-red">
                                    {errors.contact_number}
                                </p>
                            )}
                        </div>

                        <div className="space-y-1.5">
                            <Label>Email</Label>
                            <input
                                type="email"
                                value={data.email}
                                onChange={(e) =>
                                    setData('email', e.target.value)
                                }
                                placeholder="Enter email"
                                className="h-9 w-full rounded-md border px-3 text-sm outline-none"
                                style={{ borderColor: 'rgba(43,45,66,0.15)' }}
                            />
                            {errors.email && (
                                <p className="text-xs text-brand-red">
                                    {errors.email}
                                </p>
                            )}
                        </div>
                    </div>

                    <div
                        className={`grid gap-3 ${mode === 'edit' ? 'grid-cols-2' : 'grid-cols-1'}`}
                    >
                        <div className="space-y-1.5">
                            <Label>Role</Label>
                            <Select
                                value={data.role}
                                onValueChange={(v) =>
                                    setData('role', v as PersonnelRole)
                                }
                            >
                                <SelectTrigger className="w-full">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent className="bg-white text-brand-navy">
                                    {ROLE_OPTIONS.map((r) => (
                                        <SelectItem
                                            key={r.value}
                                            value={r.value}
                                        >
                                            {r.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.role && (
                                <p className="text-xs text-brand-red">
                                    {errors.role}
                                </p>
                            )}
                        </div>

                        {mode === 'edit' && (
                            <div className="space-y-1.5">
                                <Label>Status</Label>
                                <Select
                                    value={data.status}
                                    onValueChange={(v) =>
                                        setData('status', v as PersonnelStatus)
                                    }
                                >
                                    <SelectTrigger className="w-full">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent className="bg-white text-brand-navy">
                                        {STATUS_OPTIONS.map((s) => (
                                            <SelectItem
                                                key={s.value}
                                                value={s.value}
                                            >
                                                {s.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.status && (
                                    <p className="text-xs text-brand-red">
                                        {errors.status}
                                    </p>
                                )}
                            </div>
                        )}
                    </div>

                    {confirming && mode === 'create' ? (
                        <div
                            className="flex items-center justify-between rounded-lg px-4 py-3"
                            style={{
                                background: '#FFFBEB',
                                border: '1px solid rgba(244,162,97,0.35)',
                            }}
                        >
                            <p className="text-sm font-semibold text-brand-navy">
                                Create account for{' '}
                                <span className="text-brand-blue">
                                    {data.full_name}
                                </span>
                                ?
                            </p>
                            <div className="flex gap-2">
                                <button
                                    type="button"
                                    onClick={() => setConfirming(false)}
                                    className="rounded-lg px-3 py-1.5 text-xs font-semibold text-brand-muted"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="button"
                                    onClick={submitCreate}
                                    disabled={processing}
                                    className="rounded-lg bg-brand-navy px-3 py-1.5 text-xs font-semibold text-white disabled:opacity-50"
                                >
                                    {processing ? 'Creating…' : 'Confirm'}
                                </button>
                            </div>
                        </div>
                    ) : (
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
                                        ? 'Creating…'
                                        : 'Saving…'
                                    : mode === 'create'
                                      ? 'Create Account'
                                      : 'Save Changes'}
                            </button>
                        </DialogFooter>
                    )}
                </form>
            </DialogContent>
        </Dialog>
    );
}
