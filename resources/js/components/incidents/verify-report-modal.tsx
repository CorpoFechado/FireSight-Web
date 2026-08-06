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
import { verify } from '@/routes/incidents';

type Barangay = { barangay_id: number; barangay_name: string };

const INCIDENT_TYPES = [
    { value: 'structural', label: 'Structural' },
    { value: 'grass', label: 'Grass' },
    { value: 'electrical', label: 'Electrical' },
    { value: 'vehicular', label: 'Vehicular' },
    { value: 'other', label: 'Other' },
];

const SEVERITY_LEVELS = [
    { value: 'low', label: 'Low' },
    { value: 'moderate', label: 'Moderate' },
    { value: 'high', label: 'High' },
    { value: 'critical', label: 'Critical' },
];

export function VerifyReportModal({
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
            incident_type: '',
            severity_level: '',
            cause_of_fire: '',
            casualties: '0',
            notes: '',
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

        post(verify(reportId).url, {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                onOpenChange(false);
            },
        });
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="bg-white text-brand-navy sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle className="text-brand-navy">
                        Verify Report
                    </DialogTitle>
                </DialogHeader>

                <form onSubmit={submit} className="space-y-4">
                    <div className="grid grid-cols-2 gap-3">
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

                        <div className="space-y-1.5">
                            <Label>Incident Type</Label>
                            <Select
                                value={data.incident_type}
                                onValueChange={(v) =>
                                    setData('incident_type', v)
                                }
                            >
                                <SelectTrigger className="w-full">
                                    <SelectValue placeholder="Select type" />
                                </SelectTrigger>
                                <SelectContent className="bg-white text-brand-navy">
                                    {INCIDENT_TYPES.map((t) => (
                                        <SelectItem
                                            key={t.value}
                                            value={t.value}
                                        >
                                            {t.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.incident_type && (
                                <p className="text-xs text-brand-red">
                                    {errors.incident_type}
                                </p>
                            )}
                        </div>

                        <div className="space-y-1.5">
                            <Label>Severity</Label>
                            <Select
                                value={data.severity_level}
                                onValueChange={(v) =>
                                    setData('severity_level', v)
                                }
                            >
                                <SelectTrigger className="w-full">
                                    <SelectValue placeholder="Select severity" />
                                </SelectTrigger>
                                <SelectContent className="bg-white text-brand-navy">
                                    {SEVERITY_LEVELS.map((s) => (
                                        <SelectItem
                                            key={s.value}
                                            value={s.value}
                                        >
                                            {s.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.severity_level && (
                                <p className="text-xs text-brand-red">
                                    {errors.severity_level}
                                </p>
                            )}
                        </div>

                        <div className="space-y-1.5">
                            <Label>Casualties</Label>
                            <input
                                type="number"
                                min={0}
                                value={data.casualties}
                                onChange={(e) =>
                                    setData('casualties', e.target.value)
                                }
                                className="h-9 w-full rounded-md border px-3 text-sm outline-none"
                                style={{ borderColor: 'rgba(43,45,66,0.15)' }}
                            />
                            {errors.casualties && (
                                <p className="text-xs text-brand-red">
                                    {errors.casualties}
                                </p>
                            )}
                        </div>
                    </div>

                    <div className="space-y-1.5">
                        <Label>Cause of Fire (optional)</Label>
                        <input
                            value={data.cause_of_fire}
                            onChange={(e) =>
                                setData('cause_of_fire', e.target.value)
                            }
                            placeholder="e.g. Faulty electrical wiring"
                            className="h-9 w-full rounded-md border px-3 text-sm outline-none"
                            style={{ borderColor: 'rgba(43,45,66,0.15)' }}
                        />
                        {errors.cause_of_fire && (
                            <p className="text-xs text-brand-red">
                                {errors.cause_of_fire}
                            </p>
                        )}
                    </div>

                    <div className="space-y-1.5">
                        <Label>Notes (optional)</Label>
                        <textarea
                            value={data.notes}
                            onChange={(e) => setData('notes', e.target.value)}
                            rows={3}
                            className="w-full rounded-md border px-3 py-2 text-sm outline-none"
                            style={{ borderColor: 'rgba(43,45,66,0.15)' }}
                        />
                        {errors.notes && (
                            <p className="text-xs text-brand-red">
                                {errors.notes}
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
                            disabled={
                                processing ||
                                !data.barangay_id ||
                                !data.incident_type ||
                                !data.severity_level
                            }
                            className="rounded-lg bg-brand-navy px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
                        >
                            {processing ? 'Verifying…' : 'Confirm Verification'}
                        </button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
