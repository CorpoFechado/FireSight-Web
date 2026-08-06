import { Form, usePage } from '@inertiajs/react';
import { Lock, Save } from 'lucide-react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import { PortalCard } from '@/components/portal/portal-card';
import {
    PersonnelStatusBadge,
    RoleBadge,
} from '@/components/portal/status-badge';
import { Label } from '@/components/ui/label';
import { useInitials } from '@/hooks/use-initials';
import PortalLayout from '@/layouts/portal-layout';
import type { PersonnelRole, PersonnelStatus } from '@/lib/fire-status';

type PersonnelSummary = {
    rank: string | null;
    employee_number: string | null;
    station_assigned: string | null;
    role: PersonnelRole;
    status: PersonnelStatus;
};

const fieldClass = 'h-9 w-full rounded-md border px-3 text-sm outline-none';
const fieldStyle = { borderColor: 'rgba(43,45,66,0.15)' };
const disabledFieldStyle = {
    borderColor: 'rgba(43,45,66,0.1)',
    background: '#FAFBFC',
    color: '#6B7A8D',
};

export default function ProfileIndex({
    mustVerifyEmail,
    status,
    personnel,
}: {
    mustVerifyEmail: boolean;
    status?: string;
    personnel: PersonnelSummary | null;
}) {
    const { auth } = usePage().props;
    const getInitials = useInitials();

    return (
        <PortalLayout title="Profile">
            <div className="space-y-4">
                {/* Page header */}
                <div>
                    <h1 className="text-2xl font-bold text-brand-navy">
                        Profile & Settings
                    </h1>
                    <p className="mt-1 text-sm text-brand-muted">
                        Manage your account information and preferences
                    </p>
                </div>

                {/* Horizontal summary bar */}
                <PortalCard className="flex flex-wrap items-center gap-x-10 gap-y-4 p-5">
                    <div className="flex items-center gap-3">
                        <span
                            className="flex size-14 flex-shrink-0 items-center justify-center rounded-full text-lg font-semibold text-white"
                            style={{ background: '#1D3557' }}
                        >
                            {getInitials(auth.user.name)}
                        </span>
                        <div>
                            <p className="text-base font-bold text-brand-navy">
                                {auth.user.name}
                            </p>
                            {personnel?.rank && (
                                <p className="text-xs text-brand-muted">
                                    {personnel.rank}
                                </p>
                            )}
                        </div>
                    </div>

                    {personnel && <RoleBadge role={personnel.role} />}

                    <div className="flex flex-wrap items-center gap-x-10 gap-y-3 lg:ml-auto">
                        {personnel && (
                            <>
                                <SummaryField
                                    label="Employee No."
                                    value={personnel.employee_number ?? '—'}
                                />
                                <SummaryField
                                    label="Station"
                                    value={personnel.station_assigned ?? '—'}
                                />
                                <div>
                                    <p className="text-xs font-semibold tracking-wide text-brand-muted uppercase">
                                        Status
                                    </p>
                                    <div className="mt-1">
                                        <PersonnelStatusBadge
                                            status={personnel.status}
                                        />
                                    </div>
                                </div>
                            </>
                        )}
                    </div>
                </PortalCard>

                <div className="grid gap-4 lg:grid-cols-2">
                    {/* Personal Information */}
                    <PortalCard className="p-5">
                        <h2 className="text-sm font-bold text-brand-navy">
                            Personal Information
                        </h2>

                        <Form
                            {...ProfileController.update.form()}
                            options={{ preserveScroll: true }}
                            className="mt-4 space-y-4"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid grid-cols-2 gap-3">
                                        <div className="space-y-1.5">
                                            <Label>Full Name</Label>
                                            <input
                                                name="name"
                                                defaultValue={auth.user.name}
                                                required
                                                autoComplete="name"
                                                className={fieldClass}
                                                style={fieldStyle}
                                            />
                                            {errors.name && (
                                                <p className="text-xs text-brand-red">
                                                    {errors.name}
                                                </p>
                                            )}
                                        </div>

                                        <div className="space-y-1.5">
                                            <Label>Rank / Position</Label>
                                            <input
                                                name="rank"
                                                defaultValue={
                                                    personnel?.rank ?? ''
                                                }
                                                disabled={!personnel}
                                                className={fieldClass}
                                                style={
                                                    personnel
                                                        ? fieldStyle
                                                        : disabledFieldStyle
                                                }
                                            />
                                            {errors.rank && (
                                                <p className="text-xs text-brand-red">
                                                    {errors.rank}
                                                </p>
                                            )}
                                        </div>

                                        <div className="space-y-1.5">
                                            <Label>Employee Number</Label>
                                            <input
                                                defaultValue={
                                                    personnel?.employee_number ??
                                                    '—'
                                                }
                                                disabled
                                                className={fieldClass}
                                                style={disabledFieldStyle}
                                            />
                                        </div>

                                        <div className="space-y-1.5">
                                            <Label>Contact Number</Label>
                                            <input
                                                name="contact_number"
                                                defaultValue={
                                                    auth.user.contact_number ??
                                                    ''
                                                }
                                                autoComplete="tel"
                                                className={fieldClass}
                                                style={fieldStyle}
                                            />
                                            {errors.contact_number && (
                                                <p className="text-xs text-brand-red">
                                                    {errors.contact_number}
                                                </p>
                                            )}
                                        </div>

                                        <div className="space-y-1.5">
                                            <Label>Email Address</Label>
                                            <input
                                                type="email"
                                                name="email"
                                                defaultValue={auth.user.email}
                                                required
                                                autoComplete="username"
                                                className={fieldClass}
                                                style={fieldStyle}
                                            />
                                            {errors.email && (
                                                <p className="text-xs text-brand-red">
                                                    {errors.email}
                                                </p>
                                            )}
                                        </div>

                                        <div className="space-y-1.5">
                                            <Label>Station</Label>
                                            <input
                                                defaultValue={
                                                    personnel?.station_assigned ??
                                                    '—'
                                                }
                                                disabled
                                                className={fieldClass}
                                                style={disabledFieldStyle}
                                            />
                                        </div>
                                    </div>

                                    {mustVerifyEmail &&
                                        auth.user.email_verified_at ===
                                            null && (
                                            <p className="text-xs text-brand-amber">
                                                Your email address is
                                                unverified.
                                                {status ===
                                                    'verification-link-sent' &&
                                                    ' A new verification link has been sent.'}
                                            </p>
                                        )}

                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="flex items-center gap-1.5 rounded-lg bg-brand-navy px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
                                    >
                                        <Save size={14} />{' '}
                                        {processing
                                            ? 'Saving…'
                                            : 'Save Changes'}
                                    </button>
                                </>
                            )}
                        </Form>
                    </PortalCard>

                    {/* Change Password */}
                    <PortalCard className="p-5">
                        <h2 className="text-sm font-bold text-brand-navy">
                            Change Password
                        </h2>

                        <Form
                            {...SecurityController.update.form()}
                            options={{ preserveScroll: true }}
                            resetOnError={[
                                'password',
                                'password_confirmation',
                                'current_password',
                            ]}
                            resetOnSuccess
                            className="mt-4 space-y-4"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="space-y-1.5">
                                        <Label>Current Password</Label>
                                        <input
                                            type="password"
                                            name="current_password"
                                            autoComplete="current-password"
                                            className={fieldClass}
                                            style={fieldStyle}
                                        />
                                        {errors.current_password && (
                                            <p className="text-xs text-brand-red">
                                                {errors.current_password}
                                            </p>
                                        )}
                                    </div>

                                    <div className="grid grid-cols-2 gap-3">
                                        <div className="space-y-1.5">
                                            <Label>New Password</Label>
                                            <input
                                                type="password"
                                                name="password"
                                                autoComplete="new-password"
                                                className={fieldClass}
                                                style={fieldStyle}
                                            />
                                            {errors.password && (
                                                <p className="text-xs text-brand-red">
                                                    {errors.password}
                                                </p>
                                            )}
                                        </div>

                                        <div className="space-y-1.5">
                                            <Label>Confirm New Password</Label>
                                            <input
                                                type="password"
                                                name="password_confirmation"
                                                autoComplete="new-password"
                                                className={fieldClass}
                                                style={fieldStyle}
                                            />
                                            {errors.password_confirmation && (
                                                <p className="text-xs text-brand-red">
                                                    {
                                                        errors.password_confirmation
                                                    }
                                                </p>
                                            )}
                                        </div>
                                    </div>

                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="flex items-center gap-1.5 rounded-lg bg-brand-navy px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
                                    >
                                        <Lock size={14} />{' '}
                                        {processing
                                            ? 'Updating…'
                                            : 'Update Password'}
                                    </button>
                                </>
                            )}
                        </Form>
                    </PortalCard>
                </div>
            </div>
        </PortalLayout>
    );
}

function SummaryField({ label, value }: { label: string; value: string }) {
    return (
        <div>
            <p className="text-xs font-semibold tracking-wide text-brand-muted uppercase">
                {label}
            </p>
            <p className="mt-0.5 text-sm font-medium text-brand-navy">
                {value}
            </p>
        </div>
    );
}
