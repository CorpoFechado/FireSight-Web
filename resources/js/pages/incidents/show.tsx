import { Link, usePage } from '@inertiajs/react';
import { ArrowLeft, Camera, FileText, MapPin } from 'lucide-react';
import { useState } from 'react';
import { IncidentActions } from '@/components/incidents/incident-actions';
import { PortalCard } from '@/components/portal/portal-card';
import { SeverityBadge, StatusBadge } from '@/components/portal/status-badge';
import { SinglePointMap } from '@/components/portal/single-point-map';
import PortalLayout from '@/layouts/portal-layout';
import type { ReportStatus, SeverityLevel } from '@/lib/fire-status';
import { index as incidentsIndex, show as showIncident } from '@/routes/incidents';

type ReportDetail = {
    report_id: number;
    reference: string;
    reporter_name: string;
    contact_number: string;
    barangay: string | null;
    type: string | null;
    severity: SeverityLevel | null;
    status: ReportStatus;
    dateTime: string;
    description: string | null;
    report_image: string | null;
    latitude: number;
    longitude: number;
    cause_of_fire: string | null;
    casualties: number | null;
    notes: string | null;
};

type LinkedReport = {
    report_id: number;
    reference: string;
    status: ReportStatus;
};

function ReportPhoto({ path }: { path: string }) {
    const [failed, setFailed] = useState(false);

    if (failed) {
        return (
            <div
                className="flex h-36 flex-col items-center justify-center gap-1.5 rounded-lg"
                style={{ background: 'rgba(230,57,70,0.07)', border: '1px solid rgba(230,57,70,0.18)' }}
            >
                <Camera size={18} className="text-brand-red" />
                <p className="text-xs text-brand-red">Image unavailable</p>
            </div>
        );
    }

    return (
        <img
            src={`/storage/${path}`}
            alt="Report evidence"
            onError={() => setFailed(true)}
            className="h-36 w-full rounded-lg object-cover"
        />
    );
}

type Barangay = { barangay_id: number; barangay_name: string };

export default function IncidentShow({
    report,
    linkedReports,
    barangays,
    suggestedBarangayId,
}: {
    report: ReportDetail;
    linkedReports: LinkedReport[];
    barangays: Barangay[];
    suggestedBarangayId: number | null;
}) {
    const { auth } = usePage().props;

    return (
        <PortalLayout title={report.reference} subtitle={`${report.type ?? 'Unclassified'} · ${report.barangay ?? 'Barangay not yet assigned'}`}>
            <div className="space-y-4">
                <Link
                    href={incidentsIndex()}
                    className="flex w-fit items-center gap-1.5 text-xs font-semibold text-brand-blue"
                >
                    <ArrowLeft size={13} /> Back to Incident Reports
                </Link>

                <PortalCard>
                    <div
                        className="flex flex-wrap items-center justify-between gap-3 border-b px-6 py-4"
                        style={{ borderColor: 'rgba(43,45,66,0.1)' }}
                    >
                        <div className="flex items-center gap-3">
                            <div className="rounded-lg p-2" style={{ background: 'rgba(247,127,0,0.13)' }}>
                                <FileText size={16} className="text-brand-orange" />
                            </div>
                            <div>
                                <p className="text-sm font-bold text-brand-navy">{report.reference}</p>
                                <p className="text-xs text-brand-muted">
                                    {report.type ?? 'Unclassified'} · {report.barangay ?? 'Barangay not yet assigned'}
                                </p>
                            </div>
                        </div>
                        <div className="flex items-center gap-2">
                            <StatusBadge status={report.status} />
                            {report.severity && <SeverityBadge severity={report.severity} />}
                        </div>
                    </div>

                    <div className="px-6 pt-4">
                        <IncidentActions
                            reportId={report.report_id}
                            status={report.status}
                            role={auth.user.role}
                            barangays={barangays}
                            suggestedBarangayId={suggestedBarangayId}
                        />
                    </div>

                    <div className="grid gap-6 p-6 lg:grid-cols-2">
                        {/* Left column */}
                        <div className="space-y-5">
                            <div>
                                <p className="mb-3 text-xs font-semibold tracking-wider text-brand-muted uppercase">
                                    Reporter Information
                                </p>
                                <div className="space-y-2">
                                    {[
                                        ['Name', report.reporter_name],
                                        ['Contact', report.contact_number],
                                        ['Submitted', report.dateTime],
                                        ['Barangay', report.barangay ?? 'Not yet assigned'],
                                    ].map(([label, value]) => (
                                        <div key={label} className="flex items-start gap-2">
                                            <p className="w-24 flex-shrink-0 text-xs text-brand-muted">{label}</p>
                                            <p className="text-xs font-semibold text-brand-navy">{value}</p>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            <div>
                                <p className="mb-2 text-xs font-semibold tracking-wider text-brand-muted uppercase">
                                    Incident Description
                                </p>
                                <p className="rounded-lg bg-brand-bg p-3 text-sm leading-relaxed text-brand-navy">
                                    {report.description || 'No description provided.'}
                                </p>
                            </div>

                            {(report.cause_of_fire || report.notes || report.casualties !== null) && (
                                <div>
                                    <p className="mb-2 text-xs font-semibold tracking-wider text-brand-muted uppercase">
                                        BFP Assessment
                                    </p>
                                    <div className="space-y-2">
                                        {report.cause_of_fire && (
                                            <div className="flex items-start gap-2">
                                                <p className="w-24 flex-shrink-0 text-xs text-brand-muted">Cause</p>
                                                <p className="text-xs font-semibold text-brand-navy">{report.cause_of_fire}</p>
                                            </div>
                                        )}
                                        {report.casualties !== null && (
                                            <div className="flex items-start gap-2">
                                                <p className="w-24 flex-shrink-0 text-xs text-brand-muted">Casualties</p>
                                                <p className="text-xs font-semibold text-brand-navy">{report.casualties}</p>
                                            </div>
                                        )}
                                        {report.notes && (
                                            <div className="flex items-start gap-2">
                                                <p className="w-24 flex-shrink-0 text-xs text-brand-muted">Notes</p>
                                                <p className="text-xs text-brand-navy">{report.notes}</p>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            )}
                        </div>

                        {/* Right column */}
                        <div className="space-y-5">
                            <div>
                                <p className="mb-2 text-xs font-semibold tracking-wider text-brand-muted uppercase">
                                    Incident Location
                                </p>
                                <div className="relative">
                                    <SinglePointMap latitude={report.latitude} longitude={report.longitude} />
                                    <div className="absolute top-2 left-2 z-[20] flex items-center gap-1.5 rounded bg-white/90 px-2 py-1">
                                        <MapPin size={10} className="text-brand-red" />
                                        <p className="text-xs font-semibold text-brand-navy">{report.barangay ?? 'Lian, Batangas'}</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <p className="mb-2 text-xs font-semibold tracking-wider text-brand-muted uppercase">
                                    Photo Evidence
                                </p>
                                {report.report_image ? (
                                    <ReportPhoto path={report.report_image} />
                                ) : (
                                    <div
                                        className="flex h-24 flex-col items-center justify-center gap-1 rounded-lg"
                                        style={{ background: '#F8F9FA' }}
                                    >
                                        <Camera size={16} className="text-brand-muted" />
                                        <p className="text-xs text-brand-muted">No photo submitted</p>
                                    </div>
                                )}
                            </div>

                            <div>
                                <p className="mb-2 text-xs font-semibold tracking-wider text-brand-muted uppercase">
                                    Linked / Related Reports
                                </p>
                                {linkedReports.length === 0 ? (
                                    <div className="rounded-lg bg-brand-bg p-3">
                                        <p className="text-xs text-brand-muted">No linked reports for this incident.</p>
                                    </div>
                                ) : (
                                    <div className="space-y-2">
                                        {linkedReports.map((lr) => (
                                            <Link
                                                key={lr.report_id}
                                                href={showIncident(lr.report_id)}
                                                className="flex items-center justify-between rounded-lg bg-brand-bg p-3 transition-colors hover:bg-gray-100"
                                            >
                                                <span className="flex items-center gap-2 font-mono text-xs font-medium text-brand-blue">
                                                    <FileText size={11} />
                                                    {lr.reference}
                                                </span>
                                                <StatusBadge status={lr.status} />
                                            </Link>
                                        ))}
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </PortalCard>
            </div>
        </PortalLayout>
    );
}
