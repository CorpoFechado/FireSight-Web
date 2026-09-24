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
import { accept } from '@/routes/incidents';

type Barangay = { barangay_id: number; barangay_name: string };

export function AcceptReportModal({
    reportId,
    barangays,
    suggestedBarangayId,
    open,
    onOpenChange,
}: {
    reportId: number;
    barangays: Barangay[];
    suggestedBarangayId: number | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const { data, setData, post, processing, errors, reset, clearErrors } =
        useForm({
            barangay_id: suggestedBarangayId ? String(suggestedBarangayId) : '',
        });

    // Re-seed the suggested barangay whenever the modal is (re)opened.
    useEffect(() => {
        if (open) {
            setData(
                'barangay_id',
                suggestedBarangayId ? String(suggestedBarangayId) : '',
            );
            clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open]);

    const submit = (e: FormEvent) => {
        e.preventDefault();

        post(accept(reportId).url, {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                onOpenChange(false);
            },
        });
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="bg-white text-brand-navy sm:max-w-md">
                <DialogHeader>
                    <DialogTitle className="text-brand-navy">
                        Accept Report
                    </DialogTitle>
                </DialogHeader>

                <form onSubmit={submit} className="space-y-4">
                    <p className="text-xs text-brand-muted">
                        Confirm the barangay for dispatch routing. Incident
                        type, severity, and other assessment details are
                        recorded later, once the fire has been marked
                        Resolved.
                    </p>

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
                                        value={String(b.barangay_id)}
                                    >
                                        {b.barangay_name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.barangay_id && (
                            <p className="text-xs text-brand-red">
                                {errors.barangay_id}
                            </p>
                        )}
                    </div>

                    <DialogFooter>
                        <button
                            type="button"
                            onClick={() => onOpenChange(false)}
                            className="rounded-lg px-4 py-2 text-sm font-semibold text-brand-muted"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            disabled={processing || !data.barangay_id}
                            className="rounded-lg bg-brand-navy px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
                        >
                            {processing ? 'Accepting…' : 'Confirm Acceptance'}
                        </button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
