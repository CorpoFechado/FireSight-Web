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

const INCIDENT_TYPES = [
    { value: 'residential_fire', label: 'Residential Fire' },
    { value: 'commercial_fire', label: 'Commercial Fire' },
    { value: 'vehicular_fire', label: 'Vehicular Fire' },
    { value: 'storage_fire', label: 'Storage Fire' },
    { value: 'rubbish_fire', label: 'Rubbish Fire' },
    { value: 'others', label: 'Others' },
];

const SEVERITY_LEVELS = [
    { value: 'low', label: 'Low' },
    { value: 'moderate', label: 'Moderate' },
    { value: 'high', label: 'High' },
    { value: 'critical', label: 'Critical' },
];

export function EditDetailsModal({
    reportId,
    open,
    onOpenChange,
    initialValues,
}: {
    reportId: number;
    open: boolean;
    onOpenChange: (open: boolean) => void;
    initialValues: {
        incident_type: string | null;
        severity_level: string | null;
        cause_of_fire: string | null;
        casualties: number | null;
        notes: string | null;
    };
}) {
    const { data, setData, patch, processing, errors, reset, clearErrors } =
        useForm({
            incident_type: initialValues.incident_type ?? '',
            severity_level: initialValues.severity_level ?? '',
            cause_of_fire: initialValues.cause_of_fire ?? '',
            casualties: initialValues.casualties !== null && initialValues.casualties !== undefined
                ? initialValues.casualties.toString()
                : '0',
            notes: initialValues.notes ?? '',
        });

    useEffect(() => {
        if (open) {
            setData({
                incident_type: initialValues.incident_type ?? '',
                severity_level: initialValues.severity_level ?? '',
                cause_of_fire: initialValues.cause_of_fire ?? '',
                casualties: initialValues.casualties !== null && initialValues.casualties !== undefined
                    ? initialValues.casualties.toString()
                    : '0',
                notes: initialValues.notes ?? '',
            });
        }
    }, [open, initialValues]);

    const submit = (e: FormEvent) => {
        e.preventDefault();

        patch(`/incidents/${reportId}/details`, {
            preserveScroll: true,
            onSuccess: () => {
                onOpenChange(false);
            },
        });
    };

    const handleOpenChange = (next: boolean) => {
        if (!next) {
            clearErrors();
        }
        onOpenChange(next);
    };

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogContent className="bg-white text-brand-navy sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle className="text-brand-navy">
                        Edit Incident Assessment Details
                    </DialogTitle>
                </DialogHeader>

                <form onSubmit={submit} className="space-y-4">
                    <p className="text-xs text-brand-muted">
                        Update the official BFP investigation findings, fire classification, and casualty counts.
                    </p>

                    <div className="grid grid-cols-2 gap-3">
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

                        <div className="col-span-2 space-y-1.5">
                            <Label>Cause of Fire (optional)</Label>
                            <input
                                value={data.cause_of_fire}
                                onChange={(e) =>
                                    setData('cause_of_fire', e.target.value)
                                }
                                placeholder="e.g. Electrical short circuit"
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
                        <Label>Investigation Notes (optional)</Label>
                        <textarea
                            value={data.notes}
                            onChange={(e) => setData('notes', e.target.value)}
                            rows={3}
                            placeholder="Add incident notes, response units, or observations..."
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
                            onClick={() => handleOpenChange(false)}
                            className="rounded-lg px-4 py-2 text-sm font-semibold text-brand-muted"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            disabled={
                                processing ||
                                !data.incident_type ||
                                !data.severity_level
                            }
                            className="rounded-lg px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
                            style={{ background: '#1D3557' }}
                        >
                            {processing ? 'Saving…' : 'Save Changes'}
                        </button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
