import { useForm } from '@inertiajs/react';
import { X } from 'lucide-react';
import { useEffect, useState } from 'react';
import type { FormEvent } from 'react';
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
import { ALARM_LEVEL_CFG } from '@/lib/fire-status';
import type { AlarmLevel } from '@/lib/fire-status';
import { updateDetails } from '@/routes/incidents';

const INCIDENT_TYPES = [
    { value: 'residential_fire', label: 'Residential Fire' },
    { value: 'commercial_fire', label: 'Commercial Fire' },
    { value: 'vehicular_fire', label: 'Vehicular Fire' },
    { value: 'storage_fire', label: 'Storage Fire' },
    { value: 'rubbish_fire', label: 'Rubbish Fire' },
    { value: 'others', label: 'Others' },
];

const ALARM_LEVELS = Object.entries(ALARM_LEVEL_CFG).map(([value, config]) => ({
    value,
    label: config.label,
}));

function extractTime(dt?: string | null): string {
    if (!dt) return '';
    const match = dt.match(/(\d{2}):(\d{2})/);
    return match ? `${match[1]}:${match[2]}` : '';
}

function combineWithBaseDate(
    baseDateStr?: string | null,
    timeStr?: string,
): string {
    if (!timeStr) return '';
    const base = baseDateStr
        ? new Date(baseDateStr.replace(' ', 'T'))
        : new Date();
    const pad = (n: number) => String(n).padStart(2, '0');
    const y = base.getFullYear();
    const m = pad(base.getMonth() + 1);
    const d = pad(base.getDate());
    return `${y}-${m}-${d} ${timeStr}:00`;
}

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
        alarm_level: AlarmLevel | null;
        cause_of_fire: string | null;
        injuries?: number | null;
        fatalities?: number | null;
        establishment_name?: string | null;
        owner_name?: string | null;
        occupant_name?: string | null;
        time_fire_started?: string | null;
        time_fire_out?: string | null;
        estimated_damage?: number | null;
        notes: string | null;
    };
}) {
    const [startTime, setStartTime] = useState(() =>
        extractTime(initialValues.time_fire_started),
    );
    const [outTime, setOutTime] = useState(() =>
        extractTime(initialValues.time_fire_out),
    );

    const hasExistingDetails = Boolean(
        initialValues.incident_type ||
        initialValues.alarm_level ||
        initialValues.cause_of_fire ||
        initialValues.notes ||
        (initialValues.injuries !== null &&
            initialValues.injuries !== undefined &&
            initialValues.injuries > 0) ||
        (initialValues.fatalities !== null &&
            initialValues.fatalities !== undefined &&
            initialValues.fatalities > 0),
    );

    const { data, setData, patch, processing, errors, clearErrors } = useForm({
        establishment_name: initialValues.establishment_name ?? '',
        owner_name: initialValues.owner_name ?? '',
        occupant_name: initialValues.occupant_name ?? '',
        time_fire_started: initialValues.time_fire_started ?? '',
        time_fire_out: initialValues.time_fire_out ?? '',
        incident_type: initialValues.incident_type ?? '',
        alarm_level: initialValues.alarm_level ?? '',
        cause_of_fire: initialValues.cause_of_fire ?? '',
        injuries:
            initialValues.injuries !== null &&
            initialValues.injuries !== undefined
                ? initialValues.injuries.toString()
                : '0',
        fatalities:
            initialValues.fatalities !== null &&
            initialValues.fatalities !== undefined
                ? initialValues.fatalities.toString()
                : '0',
        estimated_damage:
            initialValues.estimated_damage !== null &&
            initialValues.estimated_damage !== undefined
                ? initialValues.estimated_damage.toString()
                : '',
        notes: initialValues.notes ?? '',
    });

    const isChanged =
        data.establishment_name !== (initialValues.establishment_name ?? '') ||
        data.owner_name !== (initialValues.owner_name ?? '') ||
        data.occupant_name !== (initialValues.occupant_name ?? '') ||
        data.time_fire_started !== (initialValues.time_fire_started ?? '') ||
        data.time_fire_out !== (initialValues.time_fire_out ?? '') ||
        data.incident_type !== (initialValues.incident_type ?? '') ||
        data.alarm_level !== (initialValues.alarm_level ?? '') ||
        data.cause_of_fire !== (initialValues.cause_of_fire ?? '') ||
        data.injuries !== ((initialValues.injuries ?? 0).toString()) ||
        data.fatalities !== ((initialValues.fatalities ?? 0).toString()) ||
        data.estimated_damage !==
            (initialValues.estimated_damage !== null &&
            initialValues.estimated_damage !== undefined
                ? initialValues.estimated_damage.toString()
                : '') ||
        data.notes !== (initialValues.notes ?? '');

    useEffect(() => {
        if (open) {
            setStartTime(extractTime(initialValues.time_fire_started));
            setOutTime(extractTime(initialValues.time_fire_out));
            setData({
                establishment_name: initialValues.establishment_name ?? '',
                owner_name: initialValues.owner_name ?? '',
                occupant_name: initialValues.occupant_name ?? '',
                time_fire_started: initialValues.time_fire_started ?? '',
                time_fire_out: initialValues.time_fire_out ?? '',
                incident_type: initialValues.incident_type ?? '',
                alarm_level: initialValues.alarm_level ?? '',
                cause_of_fire: initialValues.cause_of_fire ?? '',
                injuries:
                    initialValues.injuries !== null &&
                    initialValues.injuries !== undefined
                        ? initialValues.injuries.toString()
                        : '0',
                fatalities:
                    initialValues.fatalities !== null &&
                    initialValues.fatalities !== undefined
                        ? initialValues.fatalities.toString()
                        : '0',
                estimated_damage:
                    initialValues.estimated_damage !== null &&
                    initialValues.estimated_damage !== undefined
                        ? initialValues.estimated_damage.toString()
                        : '',
                notes: initialValues.notes ?? '',
            });
        }
    }, [open, initialValues, setData]);

    const submit = (e: FormEvent) => {
        e.preventDefault();

        patch(updateDetails(reportId).url, {
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
            <DialogContent className="max-h-[90vh] overflow-y-auto bg-white text-brand-navy sm:max-w-xl">
                <DialogHeader>
                    <DialogTitle className="text-brand-navy">
                        {hasExistingDetails ? 'Edit' : 'Add'} Incident
                        Assessment Details
                    </DialogTitle>
                </DialogHeader>

                <form onSubmit={submit} className="space-y-3.5">
                    <p className="text-xs text-brand-muted">
                        Update official BFP investigation findings, occupancy
                        details, and casualty counts.
                    </p>

                    <div className="space-y-1.5">
                        <Label>Name of Establishment</Label>
                        <input
                            value={data.establishment_name}
                            onChange={(e) =>
                                setData('establishment_name', e.target.value)
                            }
                            placeholder="Complete name of the establishment"
                            className="h-9 w-full rounded-md border px-3 text-sm outline-none"
                            style={{ borderColor: 'rgba(43,45,66,0.15)' }}
                        />
                        {errors.establishment_name && (
                            <p className="text-xs text-brand-red">
                                {errors.establishment_name}
                            </p>
                        )}
                    </div>

                    <div className="grid grid-cols-2 gap-3">
                        <div className="space-y-1.5">
                            <Label>Owner</Label>
                            <input
                                value={data.owner_name}
                                onChange={(e) =>
                                    setData('owner_name', e.target.value)
                                }
                                placeholder="Structure or property owner"
                                className="h-9 w-full rounded-md border px-3 text-sm outline-none"
                                style={{ borderColor: 'rgba(43,45,66,0.15)' }}
                            />
                            {errors.owner_name && (
                                <p className="text-xs text-brand-red">
                                    {errors.owner_name}
                                </p>
                            )}
                        </div>

                        <div className="space-y-1.5">
                            <Label>Occupant</Label>
                            <input
                                value={data.occupant_name}
                                onChange={(e) =>
                                    setData('occupant_name', e.target.value)
                                }
                                placeholder="Tenant or resident occupant"
                                className="h-9 w-full rounded-md border px-3 text-sm outline-none"
                                style={{ borderColor: 'rgba(43,45,66,0.15)' }}
                            />
                            {errors.occupant_name && (
                                <p className="text-xs text-brand-red">
                                    {errors.occupant_name}
                                </p>
                            )}
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-3">
                        <div className="space-y-1.5">
                            <Label>Time Fire Started</Label>
                            <input
                                type="time"
                                value={startTime}
                                onChange={(e) => {
                                    setStartTime(e.target.value);
                                    setData(
                                        'time_fire_started',
                                        combineWithBaseDate(
                                            initialValues.time_fire_started,
                                            e.target.value,
                                        ),
                                    );
                                }}
                                className="h-9 w-full rounded-md border bg-white px-3 text-sm outline-none"
                                style={{ borderColor: 'rgba(43,45,66,0.15)' }}
                            />
                            {errors.time_fire_started && (
                                <p className="text-xs text-brand-red">
                                    {errors.time_fire_started}
                                </p>
                            )}
                        </div>

                        <div className="space-y-1.5">
                            <Label>Time Fire Out</Label>
                            <input
                                type="time"
                                value={outTime}
                                onChange={(e) => {
                                    setOutTime(e.target.value);
                                    setData(
                                        'time_fire_out',
                                        combineWithBaseDate(
                                            initialValues.time_fire_out ||
                                                initialValues.time_fire_started,
                                            e.target.value,
                                        ),
                                    );
                                }}
                                className="h-9 w-full rounded-md border bg-white px-3 text-sm outline-none"
                                style={{ borderColor: 'rgba(43,45,66,0.15)' }}
                            />
                            {errors.time_fire_out && (
                                <p className="text-xs text-brand-red">
                                    {errors.time_fire_out}
                                </p>
                            )}
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-3">
                        <div className="space-y-1.5">
                            <Label>Incident Type</Label>
                            <div className="relative">
                                <Select
                                    value={data.incident_type}
                                    onValueChange={(v) =>
                                        setData('incident_type', v)
                                    }
                                >
                                    <SelectTrigger className={`w-full ${data.incident_type ? '[&_svg]:hidden pr-8' : ''}`}>
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
                                {data.incident_type && (
                                    <button
                                        type="button"
                                        onClick={(e) => {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            setData('incident_type', '');
                                        }}
                                        className="absolute right-2.5 top-1/2 -translate-y-1/2 z-10 flex size-5 items-center justify-center rounded-full text-brand-muted hover:bg-neutral-100 hover:text-brand-navy transition-colors"
                                        title="Clear Incident Type"
                                        aria-label="Clear Incident Type"
                                    >
                                        <X className="size-3.5" />
                                    </button>
                                )}
                            </div>
                            {errors.incident_type && (
                                <p className="text-xs text-brand-red">
                                    {errors.incident_type}
                                </p>
                            )}
                        </div>

                        <div className="space-y-1.5">
                            <Label>Alarm Level</Label>
                            <div className="relative">
                                <Select
                                    value={data.alarm_level}
                                    onValueChange={(v) => setData('alarm_level', v)}
                                >
                                    <SelectTrigger className={`w-full ${data.alarm_level ? '[&_svg]:hidden pr-8' : ''}`}>
                                        <SelectValue placeholder="Select alarm level" />
                                    </SelectTrigger>
                                    <SelectContent className="bg-white text-brand-navy">
                                        {ALARM_LEVELS.map((a) => (
                                            <SelectItem
                                                key={a.value}
                                                value={a.value}
                                            >
                                                {a.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {data.alarm_level && (
                                    <button
                                        type="button"
                                        onClick={(e) => {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            setData('alarm_level', '');
                                        }}
                                        className="absolute right-2.5 top-1/2 -translate-y-1/2 z-10 flex size-5 items-center justify-center rounded-full text-brand-muted hover:bg-neutral-100 hover:text-brand-navy transition-colors"
                                        title="Clear Alarm Level"
                                        aria-label="Clear Alarm Level"
                                    >
                                        <X className="size-3.5" />
                                    </button>
                                )}
                            </div>
                            {errors.alarm_level && (
                                <p className="text-xs text-brand-red">
                                    {errors.alarm_level}
                                </p>
                            )}
                        </div>
                    </div>

                    <div className="space-y-1.5">
                        <Label>Cause of Fire</Label>
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

                    <div className="grid grid-cols-2 gap-3">
                        <div className="space-y-1.5">
                            <Label>Injuries</Label>
                            <input
                                type="number"
                                min={0}
                                value={data.injuries}
                                onChange={(e) =>
                                    setData('injuries', e.target.value)
                                }
                                className="h-9 w-full rounded-md border px-3 text-sm outline-none"
                                style={{ borderColor: 'rgba(43,45,66,0.15)' }}
                            />
                            {errors.injuries && (
                                <p className="text-xs text-brand-red">
                                    {errors.injuries}
                                </p>
                            )}
                        </div>

                        <div className="space-y-1.5">
                            <Label>Fatalities</Label>
                            <input
                                type="number"
                                min={0}
                                value={data.fatalities}
                                onChange={(e) =>
                                    setData('fatalities', e.target.value)
                                }
                                className="h-9 w-full rounded-md border px-3 text-sm outline-none"
                                style={{ borderColor: 'rgba(43,45,66,0.15)' }}
                            />
                            {errors.fatalities && (
                                <p className="text-xs text-brand-red">
                                    {errors.fatalities}
                                </p>
                            )}
                        </div>
                    </div>

                    <div className="space-y-1.5">
                        <Label>Estimated Damage (₱)</Label>
                        <input
                            type="number"
                            min={0}
                            step="any"
                            value={data.estimated_damage}
                            onChange={(e) =>
                                setData('estimated_damage', e.target.value)
                            }
                            placeholder="e.g. 150000.00"
                            className="h-9 w-full rounded-md border px-3 text-sm outline-none"
                            style={{ borderColor: 'rgba(43,45,66,0.15)' }}
                        />
                        {errors.estimated_damage && (
                            <p className="text-xs text-brand-red">
                                {errors.estimated_damage}
                            </p>
                        )}
                    </div>

                    <div className="space-y-1.5">
                        <Label>Notes</Label>
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
                            disabled={processing || !isChanged}
                            className="rounded-lg px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
                            style={{ background: '#1D3557' }}
                        >
                            {processing
                                ? 'Saving…'
                                : hasExistingDetails
                                  ? 'Save Changes'
                                  : 'Add Details'}
                        </button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
