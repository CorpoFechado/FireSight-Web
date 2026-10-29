import { Link, router, useForm, useHttp } from '@inertiajs/react';
import {
    Check,
    Clipboard,
    Edit3,
    Eye,
    KeyRound,
    Search,
    ShieldAlert,
    ShieldCheck,
} from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { toast } from 'sonner';
import { PaginationBar } from '@/components/portal/pagination-bar';
import { PortalCard } from '@/components/portal/portal-card';
import { StatusBadge } from '@/components/portal/status-badge';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Skeleton } from '@/components/ui/skeleton';
import PortalLayout from '@/layouts/portal-layout';
import type { ReportStatus } from '@/lib/fire-status';
import { residents as residentsIndex } from '@/routes';
import { show as showIncident } from '@/routes/incidents';
import {
    reactivate,
    resetPassword,
    show,
    suspend,
    update,
} from '@/routes/residents';
import type { Paginated } from '@/types/pagination';

type ResidentRow = {
    id: number;
    name: string;
    email: string;
    contact_number: string | null;
    barangay: string | null;
    status: 'active' | 'suspended';
    report_count: number;
    created_at: string | null;
    is_google_linked: boolean;
    password_changed_at: string | null;
    details_updated_at: string | null;
    suspended_at: string | null;
};

type ResidentProfile = {
    id: number;
    first_name: string;
    middle_name: string | null;
    last_name: string;
    suffix: string | null;
    email: string;
    username: string | null;
    contact_number: string | null;
    house_no_street: string | null;
    barangay_id: number | null;
    barangay: string | null;
    municipality: string | null;
    province: string | null;
    is_verified: boolean;
    joined_at: string | null;
    password_changed_at: string | null;
    details_updated_at: string | null;
    suspended_at: string | null;
    suspension_reason: string | null;
    is_google_linked: boolean;
    status: 'active' | 'suspended';
};

type ResidentReport = {
    id: number;
    reference: string;
    incident_type: string;
    barangay: string | null;
    status: ReportStatus;
    created_at: string | null;
    url: string;
};

type ResidentResponse = {
    resident: ResidentProfile;
    reports: ResidentReport[];
};

type BarangayOption = {
    barangay_id: number;
    barangay_name: string;
};

type Tab = 'profile' | 'security' | 'status' | 'reports';
type Confirmation = 'reset' | 'suspend' | 'reactivate' | null;

const inputClass =
    'h-9 w-full rounded-md border border-brand-navy/15 bg-white px-3 text-sm text-brand-navy outline-none focus:border-brand-blue';

function formatDateTime(value: string | null): string {
    if (!value) {
        return 'Never';
    }

    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    }).format(new Date(value));
}

function ResidentStatusBadge({ status }: { status: ResidentRow['status'] }) {
    const active = status === 'active';

    return (
        <span
            className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ${
                active
                    ? 'bg-emerald-50 text-emerald-700'
                    : 'bg-red-50 text-red-700'
            }`}
        >
            <span
                className={`size-1.5 rounded-full ${active ? 'bg-emerald-600' : 'bg-red-600'}`}
            />
            {active ? 'Active' : 'Suspended'}
        </span>
    );
}

function ModalSkeleton() {
    return (
        <div className="space-y-5 py-2">
            <Skeleton className="h-10 w-full" />
            <div className="grid grid-cols-2 gap-4">
                {Array.from({ length: 6 }).map((_, index) => (
                    <Skeleton key={index} className="h-16 w-full" />
                ))}
            </div>
        </div>
    );
}

export default function ResidentIndex({
    residents,
    filters,
    barangays,
    totalCount,
}: {
    residents: Paginated<ResidentRow>;
    filters: { search: string; barangay: string; status: string };
    barangays: BarangayOption[];
    totalCount: number;
}) {
    const [searchValue, setSearchValue] = useState(filters.search);
    const [selected, setSelected] = useState<ResidentRow | null>(null);
    const [details, setDetails] = useState<ResidentResponse | null>(null);
    const [tab, setTab] = useState<Tab>('profile');
    const [editing, setEditing] = useState(false);
    const [confirmation, setConfirmation] = useState<Confirmation>(null);
    const [suspensionReason, setSuspensionReason] = useState('');
    const [temporaryPassword, setTemporaryPassword] = useState<string | null>(
        null,
    );
    const [actionProcessing, setActionProcessing] = useState(false);
    const isFirstRender = useRef(true);
    const detailsRequest = useHttp<Record<string, never>, ResidentResponse>({});
    const editForm = useForm({
        first_name: '',
        middle_name: '',
        last_name: '',
        suffix: '',
        email: '',
        contact_number: '',
        username: '',
        house_no_street: '',
        barangay_id: '',
    });

    const navigate = (overrides: Partial<typeof filters>) => {
        router.get(
            residentsIndex().url,
            {
                search: searchValue,
                barangay: filters.barangay,
                status: filters.status,
                ...overrides,
            },
            { preserveState: true, replace: true },
        );
    };

    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;

            return;
        }

        const timeout = setTimeout(
            () => navigate({ search: searchValue }),
            400,
        );

        return () => clearTimeout(timeout);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [searchValue]);

    const seedEditForm = (resident: ResidentProfile) => {
        editForm.setData({
            first_name: resident.first_name,
            middle_name: resident.middle_name ?? '',
            last_name: resident.last_name,
            suffix: resident.suffix ?? '',
            email: resident.email,
            contact_number: resident.contact_number ?? '',
            username: resident.username ?? '',
            house_no_street: resident.house_no_street ?? '',
            barangay_id: resident.barangay_id
                ? String(resident.barangay_id)
                : '',
        });
    };

    const loadResident = (residentId: number) => {
        setDetails(null);
        detailsRequest.get(show(residentId).url, {
            onSuccess: (response) => {
                setDetails(response);
                seedEditForm(response.resident);
            },
            onHttpException: () => {
                toast.error("Couldn't load this resident's details.");

                return false;
            },
        });
    };

    const openResident = (resident: ResidentRow) => {
        setSelected(resident);
        setTab('profile');
        setEditing(false);
        setTemporaryPassword(null);
        loadResident(resident.id);
    };

    const closeModal = () => {
        detailsRequest.cancel();
        setSelected(null);
        setDetails(null);
        setEditing(false);
        setConfirmation(null);
        editForm.clearErrors();
    };

    const submitUpdate = (event: React.FormEvent) => {
        event.preventDefault();

        if (!details) {
            return;
        }

        editForm.patch(update(details.resident.id).url, {
            preserveScroll: true,
            onSuccess: () => {
                setEditing(false);
                loadResident(details.resident.id);
            },
        });
    };

    const completeAction = () => {
        if (!details || !confirmation) {
            return;
        }

        setActionProcessing(true);
        const options = {
            preserveScroll: true,
            onSuccess: (page: { props: Record<string, unknown> }) => {
                if (confirmation === 'reset') {
                    const flash = page.props.flash as
                        { residentTemporaryPassword?: string } | undefined;
                    setTemporaryPassword(
                        flash?.residentTemporaryPassword ?? null,
                    );
                    setTab('security');
                } else {
                    loadResident(details.resident.id);
                }

                setConfirmation(null);
                setSuspensionReason('');
            },
            onError: (errors: Record<string, string>) => {
                toast.error(
                    Object.values(errors)[0] ??
                        'The account could not be updated.',
                );
            },
            onFinish: () => setActionProcessing(false),
        };

        if (confirmation === 'reset') {
            router.post(resetPassword(details.resident.id).url, {}, options);
        } else if (confirmation === 'suspend') {
            router.post(
                suspend(details.resident.id).url,
                { reason: suspensionReason },
                options,
            );
        } else {
            router.post(reactivate(details.resident.id).url, {}, options);
        }
    };

    const copyTemporaryPassword = async () => {
        if (!temporaryPassword) {
            return;
        }

        await navigator.clipboard.writeText(temporaryPassword);
        toast.success('Temporary password copied.');
    };

    return (
        <PortalLayout title="Resident Accounts">
            <div className="space-y-4">
                <div>
                    <h1 className="text-2xl font-bold text-brand-navy">
                        Resident Accounts
                    </h1>
                    <p className="mt-1 text-sm text-brand-muted">
                        {totalCount} residents registered in FireSight
                    </p>
                </div>

                <PortalCard className="p-4">
                    <div className="flex flex-wrap items-center gap-3">
                        <div className="relative min-w-64 flex-1">
                            <Search
                                size={14}
                                className="absolute top-1/2 left-3 -translate-y-1/2 text-brand-muted"
                            />
                            <input
                                value={searchValue}
                                onChange={(event) =>
                                    setSearchValue(event.target.value)
                                }
                                placeholder="Search residents…"
                                className={`${inputClass} pl-9`}
                            />
                        </div>
                        <select
                            aria-label="Filter by barangay"
                            value={filters.barangay}
                            onChange={(event) =>
                                navigate({ barangay: event.target.value })
                            }
                            className={`${inputClass} w-52`}
                        >
                            <option value="all">All barangays</option>
                            {barangays.map((barangay) => (
                                <option
                                    key={barangay.barangay_id}
                                    value={barangay.barangay_id}
                                >
                                    {barangay.barangay_name}
                                </option>
                            ))}
                        </select>
                        <select
                            aria-label="Filter by status"
                            value={filters.status}
                            onChange={(event) =>
                                navigate({ status: event.target.value })
                            }
                            className={`${inputClass} w-40`}
                        >
                            <option value="all">All statuses</option>
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </PortalCard>

                <PortalCard>
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="border-b border-brand-navy/10 bg-slate-50">
                                <tr>
                                    {[
                                        'Name',
                                        'Email',
                                        'Contact',
                                        'Barangay',
                                        'Reports',
                                        'Status',
                                        'Joined',
                                        'Actions',
                                    ].map((heading) => (
                                        <th
                                            key={heading}
                                            className="px-4 py-3 text-left text-xs font-semibold tracking-wider text-brand-muted uppercase"
                                        >
                                            {heading}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {residents.data.length === 0 && (
                                    <tr>
                                        <td
                                            colSpan={8}
                                            className="px-4 py-12 text-center text-sm text-brand-muted"
                                        >
                                            No residents match these filters.
                                        </td>
                                    </tr>
                                )}
                                {residents.data.map((resident) => (
                                    <tr
                                        key={resident.id}
                                        className="border-t border-brand-navy/5"
                                    >
                                        <td className="px-4 py-3 font-semibold text-brand-navy">
                                            {resident.name}
                                        </td>
                                        <td className="px-4 py-3 text-xs text-brand-muted">
                                            {resident.email}
                                        </td>
                                        <td className="px-4 py-3 font-mono text-xs text-brand-muted">
                                            {resident.contact_number ?? '—'}
                                        </td>
                                        <td className="px-4 py-3 text-xs text-brand-navy">
                                            {resident.barangay ?? '—'}
                                        </td>
                                        <td className="px-4 py-3 text-xs font-semibold text-brand-navy">
                                            {resident.report_count}
                                        </td>
                                        <td className="px-4 py-3">
                                            <ResidentStatusBadge
                                                status={resident.status}
                                            />
                                        </td>
                                        <td className="px-4 py-3 text-xs text-brand-muted">
                                            {formatDateTime(
                                                resident.created_at,
                                            )}
                                        </td>
                                        <td className="px-4 py-3">
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    openResident(resident)
                                                }
                                                className="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-brand-blue hover:bg-blue-50"
                                            >
                                                <Eye size={14} /> View
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <PaginationBar paginated={residents} />
                </PortalCard>
            </div>

            <Dialog
                open={selected !== null}
                onOpenChange={(open) => !open && closeModal()}
            >
                <DialogContent className="max-h-[90vh] overflow-y-auto bg-white text-brand-navy sm:max-w-4xl">
                    <DialogHeader>
                        <DialogTitle className="text-brand-navy">
                            {selected?.name ?? 'Resident details'}
                        </DialogTitle>
                    </DialogHeader>

                    {detailsRequest.processing || !details ? (
                        <ModalSkeleton />
                    ) : (
                        <>
                            <div className="flex flex-wrap gap-2 border-b border-brand-navy/10 pb-3">
                                {(
                                    [
                                        ['profile', 'Profile'],
                                        ['security', 'Security'],
                                        ['status', 'Account Status'],
                                        [
                                            'reports',
                                            `Reports (${details.reports.length})`,
                                        ],
                                    ] as const
                                ).map(([value, label]) => (
                                    <button
                                        key={value}
                                        type="button"
                                        onClick={() => setTab(value)}
                                        className={`rounded-lg px-3 py-2 text-sm font-semibold ${
                                            tab === value
                                                ? 'bg-brand-navy text-white'
                                                : 'text-brand-muted hover:bg-slate-50'
                                        }`}
                                    >
                                        {label}
                                    </button>
                                ))}
                            </div>

                            {tab === 'profile' && (
                                <section className="space-y-4">
                                    <div className="flex items-center justify-between gap-3">
                                        <p className="text-xs text-brand-muted">
                                            Details last updated:{' '}
                                            {formatDateTime(
                                                details.resident
                                                    .details_updated_at,
                                            )}
                                        </p>
                                        <button
                                            type="button"
                                            onClick={() =>
                                                setEditing((value) => !value)
                                            }
                                            className="inline-flex items-center gap-1.5 rounded-lg border border-brand-navy/15 px-3 py-2 text-xs font-semibold"
                                        >
                                            <Edit3 size={14} />
                                            {editing
                                                ? 'Cancel editing'
                                                : 'Edit'}
                                        </button>
                                    </div>

                                    {editing ? (
                                        <form
                                            onSubmit={submitUpdate}
                                            className="space-y-4"
                                        >
                                            <div className="grid gap-4 sm:grid-cols-2">
                                                {[
                                                    [
                                                        'first_name',
                                                        'First name',
                                                    ],
                                                    [
                                                        'middle_name',
                                                        'Middle name',
                                                    ],
                                                    ['last_name', 'Last name'],
                                                    ['suffix', 'Suffix'],
                                                    ['email', 'Email'],
                                                    [
                                                        'contact_number',
                                                        'Contact number',
                                                    ],
                                                    ['username', 'Username'],
                                                    [
                                                        'house_no_street',
                                                        'House no. / Street',
                                                    ],
                                                ].map(([field, label]) => (
                                                    <div
                                                        key={field}
                                                        className="space-y-1.5"
                                                    >
                                                        <Label>{label}</Label>
                                                        <input
                                                            type={
                                                                field ===
                                                                'email'
                                                                    ? 'email'
                                                                    : 'text'
                                                            }
                                                            value={
                                                                editForm.data[
                                                                    field as keyof typeof editForm.data
                                                                ]
                                                            }
                                                            onChange={(event) =>
                                                                editForm.setData(
                                                                    field as keyof typeof editForm.data,
                                                                    event.target
                                                                        .value,
                                                                )
                                                            }
                                                            className={
                                                                inputClass
                                                            }
                                                        />
                                                        {editForm.errors[
                                                            field as keyof typeof editForm.errors
                                                        ] && (
                                                            <p className="text-xs text-brand-red">
                                                                {
                                                                    editForm
                                                                        .errors[
                                                                        field as keyof typeof editForm.errors
                                                                    ]
                                                                }
                                                            </p>
                                                        )}
                                                    </div>
                                                ))}
                                                <div className="space-y-1.5 sm:col-span-2">
                                                    <Label>Barangay</Label>
                                                    <select
                                                        value={
                                                            editForm.data
                                                                .barangay_id
                                                        }
                                                        onChange={(event) =>
                                                            editForm.setData(
                                                                'barangay_id',
                                                                event.target
                                                                    .value,
                                                            )
                                                        }
                                                        className={inputClass}
                                                    >
                                                        <option value="">
                                                            Select barangay
                                                        </option>
                                                        {barangays.map(
                                                            (barangay) => (
                                                                <option
                                                                    key={
                                                                        barangay.barangay_id
                                                                    }
                                                                    value={
                                                                        barangay.barangay_id
                                                                    }
                                                                >
                                                                    {
                                                                        barangay.barangay_name
                                                                    }
                                                                </option>
                                                            ),
                                                        )}
                                                    </select>
                                                    {editForm.errors
                                                        .barangay_id && (
                                                        <p className="text-xs text-brand-red">
                                                            {
                                                                editForm.errors
                                                                    .barangay_id
                                                            }
                                                        </p>
                                                    )}
                                                </div>
                                            </div>
                                            <div className="flex justify-end">
                                                <button
                                                    type="submit"
                                                    disabled={
                                                        editForm.processing
                                                    }
                                                    className="rounded-lg bg-brand-navy px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
                                                >
                                                    {editForm.processing
                                                        ? 'Saving…'
                                                        : 'Save changes'}
                                                </button>
                                            </div>
                                        </form>
                                    ) : (
                                        <div className="grid gap-4 rounded-xl border border-brand-navy/10 p-4 sm:grid-cols-2">
                                            {[
                                                [
                                                    'First name',
                                                    details.resident.first_name,
                                                ],
                                                [
                                                    'Middle name',
                                                    details.resident
                                                        .middle_name,
                                                ],
                                                [
                                                    'Last name',
                                                    details.resident.last_name,
                                                ],
                                                [
                                                    'Suffix',
                                                    details.resident.suffix,
                                                ],
                                                [
                                                    'Email',
                                                    details.resident.email,
                                                ],
                                                [
                                                    'Username',
                                                    details.resident.username,
                                                ],
                                                [
                                                    'Contact',
                                                    details.resident
                                                        .contact_number,
                                                ],
                                                [
                                                    'Address',
                                                    [
                                                        details.resident
                                                            .house_no_street,
                                                        details.resident
                                                            .barangay,
                                                        details.resident
                                                            .municipality,
                                                        details.resident
                                                            .province,
                                                    ]
                                                        .filter(Boolean)
                                                        .join(', '),
                                                ],
                                                [
                                                    'Verification',
                                                    details.resident.is_verified
                                                        ? 'Verified'
                                                        : 'Not verified',
                                                ],
                                                [
                                                    'Joined',
                                                    formatDateTime(
                                                        details.resident
                                                            .joined_at,
                                                    ),
                                                ],
                                            ].map(([label, value]) => (
                                                <div key={label}>
                                                    <p className="text-xs font-semibold tracking-wide text-brand-muted uppercase">
                                                        {label}
                                                    </p>
                                                    <p className="mt-1 text-sm text-brand-navy">
                                                        {value || '—'}
                                                    </p>
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                </section>
                            )}

                            {tab === 'security' && (
                                <section className="space-y-4">
                                    <div className="rounded-xl border border-brand-navy/10 p-4">
                                        <p className="text-xs font-semibold tracking-wide text-brand-muted uppercase">
                                            Password last changed
                                        </p>
                                        <p className="mt-1 text-sm font-semibold text-brand-navy">
                                            {formatDateTime(
                                                details.resident
                                                    .password_changed_at,
                                            )}
                                        </p>
                                        {details.resident.is_google_linked && (
                                            <p className="mt-3 text-sm text-brand-muted">
                                                This account is linked to Google
                                                and normally signs in with
                                                Google.
                                            </p>
                                        )}
                                    </div>

                                    {temporaryPassword && (
                                        <div className="rounded-xl border border-amber-300 bg-amber-50 p-4">
                                            <p className="text-sm font-semibold text-amber-900">
                                                Copy this temporary password
                                                now. It will not be shown again.
                                            </p>
                                            <div className="mt-3 flex items-center gap-2">
                                                <code className="flex-1 rounded-lg bg-white px-3 py-2 font-mono text-base text-brand-navy">
                                                    {temporaryPassword}
                                                </code>
                                                <button
                                                    type="button"
                                                    onClick={
                                                        copyTemporaryPassword
                                                    }
                                                    className="inline-flex items-center gap-1.5 rounded-lg bg-brand-navy px-3 py-2 text-xs font-semibold text-white"
                                                >
                                                    <Clipboard size={14} /> Copy
                                                </button>
                                            </div>
                                        </div>
                                    )}

                                    <button
                                        type="button"
                                        onClick={() => setConfirmation('reset')}
                                        className="inline-flex items-center gap-2 rounded-lg bg-brand-navy px-4 py-2.5 text-sm font-semibold text-white"
                                    >
                                        <KeyRound size={15} /> Reset Password
                                    </button>
                                </section>
                            )}

                            {tab === 'status' && (
                                <section className="space-y-4">
                                    <div className="rounded-xl border border-brand-navy/10 p-4">
                                        <ResidentStatusBadge
                                            status={details.resident.status}
                                        />
                                        {details.resident.status ===
                                            'suspended' && (
                                            <div className="mt-4 space-y-2 text-sm">
                                                <p>
                                                    <span className="font-semibold">
                                                        Suspended:
                                                    </span>{' '}
                                                    {formatDateTime(
                                                        details.resident
                                                            .suspended_at,
                                                    )}
                                                </p>
                                                <p>
                                                    <span className="font-semibold">
                                                        Reason:
                                                    </span>{' '}
                                                    {details.resident
                                                        .suspension_reason ||
                                                        'No reason provided'}
                                                </p>
                                            </div>
                                        )}
                                    </div>

                                    {details.resident.status === 'active' ? (
                                        <button
                                            type="button"
                                            onClick={() =>
                                                setConfirmation('suspend')
                                            }
                                            className="inline-flex items-center gap-2 rounded-lg bg-brand-red px-4 py-2.5 text-sm font-semibold text-white"
                                        >
                                            <ShieldAlert size={15} /> Suspend
                                            Account
                                        </button>
                                    ) : (
                                        <button
                                            type="button"
                                            onClick={() =>
                                                setConfirmation('reactivate')
                                            }
                                            className="inline-flex items-center gap-2 rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white"
                                        >
                                            <ShieldCheck size={15} /> Reactivate
                                            Account
                                        </button>
                                    )}
                                </section>
                            )}

                            {tab === 'reports' && (
                                <section>
                                    {details.reports.length === 0 ? (
                                        <div className="rounded-xl border border-dashed border-brand-navy/20 py-12 text-center text-sm text-brand-muted">
                                            This resident has not submitted any
                                            reports.
                                        </div>
                                    ) : (
                                        <div className="overflow-x-auto rounded-xl border border-brand-navy/10">
                                            <table className="w-full text-sm">
                                                <thead className="bg-slate-50">
                                                    <tr>
                                                        {[
                                                            'Reference',
                                                            'Type',
                                                            'Barangay',
                                                            'Status',
                                                            'Date',
                                                            '',
                                                        ].map(
                                                            (
                                                                heading,
                                                                index,
                                                            ) => (
                                                                <th
                                                                    key={`${heading}-${index}`}
                                                                    className="px-3 py-2 text-left text-xs font-semibold text-brand-muted uppercase"
                                                                >
                                                                    {heading}
                                                                </th>
                                                            ),
                                                        )}
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {details.reports.map(
                                                        (report) => (
                                                            <tr
                                                                key={report.id}
                                                                className="border-t border-brand-navy/10"
                                                            >
                                                                <td className="px-3 py-3 font-mono text-xs font-semibold">
                                                                    {
                                                                        report.reference
                                                                    }
                                                                </td>
                                                                <td className="px-3 py-3 text-xs">
                                                                    {
                                                                        report.incident_type
                                                                    }
                                                                </td>
                                                                <td className="px-3 py-3 text-xs">
                                                                    {report.barangay ??
                                                                        '—'}
                                                                </td>
                                                                <td className="px-3 py-3">
                                                                    <StatusBadge
                                                                        status={
                                                                            report.status
                                                                        }
                                                                    />
                                                                </td>
                                                                <td className="px-3 py-3 text-xs text-brand-muted">
                                                                    {formatDateTime(
                                                                        report.created_at,
                                                                    )}
                                                                </td>
                                                                <td className="px-3 py-3 text-right">
                                                                    <Link
                                                                        href={showIncident(
                                                                            report.id,
                                                                        )}
                                                                        className="text-xs font-semibold text-brand-blue hover:underline"
                                                                    >
                                                                        View
                                                                        Report
                                                                    </Link>
                                                                </td>
                                                            </tr>
                                                        ),
                                                    )}
                                                </tbody>
                                            </table>
                                        </div>
                                    )}
                                </section>
                            )}
                        </>
                    )}
                </DialogContent>
            </Dialog>

            <Dialog
                open={confirmation !== null}
                onOpenChange={(open) => !open && setConfirmation(null)}
            >
                <DialogContent className="bg-white text-brand-navy sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>
                            {confirmation === 'reset'
                                ? 'Reset resident password?'
                                : confirmation === 'suspend'
                                  ? 'Suspend resident account?'
                                  : 'Reactivate resident account?'}
                        </DialogTitle>
                    </DialogHeader>
                    <p className="text-sm text-brand-muted">
                        {confirmation === 'reset'
                            ? 'This revokes every mobile session and creates a one-time temporary password.'
                            : confirmation === 'suspend'
                              ? 'This signs the resident out immediately. Their past reports will remain available.'
                              : 'The resident will be able to sign in again.'}
                    </p>
                    {confirmation === 'suspend' && (
                        <div className="space-y-1.5">
                            <Label>Reason (optional)</Label>
                            <textarea
                                value={suspensionReason}
                                onChange={(event) =>
                                    setSuspensionReason(event.target.value)
                                }
                                maxLength={255}
                                rows={3}
                                className="w-full rounded-md border border-brand-navy/15 p-3 text-sm outline-none"
                                placeholder="Reason shown to the resident"
                            />
                        </div>
                    )}
                    <DialogFooter>
                        <button
                            type="button"
                            onClick={() => setConfirmation(null)}
                            className="rounded-lg px-4 py-2 text-sm font-semibold text-brand-muted"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            onClick={completeAction}
                            disabled={actionProcessing}
                            className="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-navy px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
                        >
                            {actionProcessing ? (
                                'Working…'
                            ) : (
                                <>
                                    <Check size={14} /> Confirm
                                </>
                            )}
                        </button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </PortalLayout>
    );
}
