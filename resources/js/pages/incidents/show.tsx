import { Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Building2,
    Camera,
    Edit,
    FileText,
    MapPin,
    Phone,
    ShieldCheck,
    UserCheck,
    ZoomIn,
    X,
} from 'lucide-react';
import { useState } from 'react';
import { AiVerificationCard } from '@/components/incidents/ai-verification-card';
import { EditDetailsModal } from '@/components/incidents/edit-details-modal';
import { IncidentActions } from '@/components/incidents/incident-actions';
import { PortalCard } from '@/components/portal/portal-card';
import { SinglePointMap } from '@/components/portal/single-point-map';
import {
    AiFireBadge,
    AlarmLevelBadge,
    StatusBadge,
} from '@/components/portal/status-badge';
import PortalLayout from '@/layouts/portal-layout';
import { STATUS_CFG } from '@/lib/fire-status';
import type { AlarmLevel, ReportStatus } from '@/lib/fire-status';
import type { StatusHistoryEntry } from '@/lib/status-history';
import {
    index as incidentsIndex,
    show as showIncident,
} from '@/routes/incidents';

type ReportDetail = {
    report_id: number;
    reference: string;
    reporter_name: string;
    contact_number: string;
    barangay: string | null;
    type: string | null;
    alarm_level: AlarmLevel | null;
    status: ReportStatus;
    dateTime: string;
    description: string | null;
    report_image: string | null;
    evidence_photos?: Array<{
        evidence_id: number;
        image_path: string;
        caption: string | null;
        created_at?: string | null;
    }>;
    latitude: number;
    longitude: number;
    raw_incident_type: string | null;
    raw_alarm_level: string | null;
    cause_of_fire: string | null;
    casualties: number | null;
    notes: string | null;
    ai_fire_label: string | null;
    ai_fire_confidence: number | null;
    ai_verified_at: string | null;
    barangay_contact_person: string | null;
    barangay_contact_number: string | null;
    barangay_contact_role: string | null;
    barangay_contacts?: Array<{
        contact_id: number;
        name: string;
        role: string;
        phone_number: string;
    }>;
};

type LinkedReport = {
    report_id: number;
    reference: string;
    status: ReportStatus;
};

function resolveStorageUrl(path?: string | null): string {
    if (!path) return '';
    if (
        path.startsWith('http://') ||
        path.startsWith('https://') ||
        path.startsWith('data:')
    ) {
        return path;
    }
    const clean = path.replace(/^\/?(storage\/)?/, '');
    return `/storage/${clean}`;
}

function ReportPhoto({
    path,
    label,
    caption,
}: {
    path: string;
    label?: string;
    caption?: string | null;
}) {
    const [failed, setFailed] = useState(false);
    const [expanded, setExpanded] = useState(false);
    const url = resolveStorageUrl(path);

    if (failed) {
        return (
            <div
                className="flex h-36 flex-col items-center justify-center gap-1 rounded-lg border border-red-200 bg-red-50/50 p-2 text-center"
            >
                <Camera size={18} className="text-brand-red" />
                <p className="text-xs text-brand-red">Image unavailable</p>
                {label && (
                    <span className="text-[10px] text-brand-muted">{label}</span>
                )}
            </div>
        );
    }

    return (
        <>
            <div className="group relative overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                <img
                    src={url}
                    alt={caption || label || 'Fire scene photo'}
                    onError={() => setFailed(true)}
                    onClick={() => setExpanded(true)}
                    className="h-36 w-full cursor-pointer object-cover transition-transform duration-200 group-hover:scale-105"
                />
                {label && (
                    <span className="absolute top-2 left-2 rounded bg-black/75 px-1.5 py-0.5 text-[10px] font-bold text-white shadow-xs backdrop-blur-xs">
                        {label}
                    </span>
                )}
                <button
                    type="button"
                    onClick={() => setExpanded(true)}
                    className="absolute right-2 bottom-2 rounded bg-white/90 p-1 text-brand-navy shadow-xs backdrop-blur-xs transition hover:bg-white"
                    title="Click to view full image"
                >
                    <ZoomIn size={13} />
                </button>
            </div>
            {caption ? (
                <p className="mt-1 text-[11px] text-brand-muted">{caption}</p>
            ) : null}

            {expanded && (
                <div
                    className="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 backdrop-blur-xs"
                    onClick={() => setExpanded(false)}
                >
                    <div
                        className="relative max-h-[90vh] max-w-4xl overflow-hidden rounded-xl bg-black p-2"
                        onClick={(e) => e.stopPropagation()}
                    >
                        <button
                            type="button"
                            onClick={() => setExpanded(false)}
                            className="absolute top-4 right-4 z-10 rounded-full bg-white/20 p-2 text-white hover:bg-white/40"
                        >
                            <X size={18} />
                        </button>
                        <img
                            src={url}
                            alt={caption || label || 'Full photo'}
                            className="max-h-[80vh] w-auto max-w-full rounded-lg object-contain"
                        />
                        {(label || caption) && (
                            <div className="p-2 text-center text-white">
                                {label && (
                                    <span className="mr-2 text-xs font-bold text-amber-400">
                                        [{label}]
                                    </span>
                                )}
                                {caption && (
                                    <span className="text-xs text-gray-200">
                                        {caption}
                                    </span>
                                )}
                            </div>
                        )}
                    </div>
                </div>
            )}
        </>
    );
}
type Barangay = { barangay_id: number; barangay_name: string };

export default function IncidentShow({
    report,
    linkedReports,
    statusHistory = [],
}: {
    report: ReportDetail;
    linkedReports: LinkedReport[];
    barangays: Barangay[];
    suggestedBarangayId: number | null;
    statusHistory?: StatusHistoryEntry[];
}) {
    const [editOpen, setEditOpen] = useState(false);
    const assessmentValues = {
        incident_type: report.raw_incident_type,
        alarm_level: report.raw_alarm_level,
        cause_of_fire: report.cause_of_fire,
        casualties: report.casualties,
        notes: report.notes,
    };
    const timeline =
        statusHistory && statusHistory.length > 0
            ? statusHistory
            : [
                  {
                      id: `initial-${report.report_id}`,
                      status: report.status,
                      notes: 'Incident report logged in FireSight.',
                      changed_at: report.dateTime,
                      changed_by: report.reporter_name
                          ? `${report.reporter_name} (Reporter)`
                          : 'Citizen Reporter',
                      personnel: null,
                  },
              ];
    const hasAssessment = Boolean(
        report.raw_incident_type ||
        report.raw_alarm_level ||
        report.cause_of_fire ||
        report.notes ||
        report.casualties !== null,
    );

    return (
        <PortalLayout
            title={report.reference}
            subtitle={`${report.type ?? 'Unclassified'} · ${report.barangay ?? 'Barangay not yet assigned'}`}
        >
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
                            <div
                                className="rounded-lg p-2"
                                style={{ background: 'rgba(247,127,0,0.13)' }}
                            >
                                <FileText
                                    size={16}
                                    className="text-brand-orange"
                                />
                            </div>
                            <div>
                                <p className="text-sm font-bold text-brand-navy">
                                    {report.reference}
                                </p>
                                <p className="text-xs text-brand-muted">
                                    {report.type ?? 'Unclassified'} ·{' '}
                                    {report.barangay ??
                                        'Barangay not yet assigned'}
                                </p>
                            </div>
                        </div>
                        <div className="flex flex-wrap items-center gap-2">
                            <StatusBadge status={report.status} />
                            {report.alarm_level && (
                                <AlarmLevelBadge
                                    alarmLevel={report.alarm_level}
                                />
                            )}
                            <AiFireBadge
                                label={report.ai_fire_label}
                                confidence={report.ai_fire_confidence}
                                showConfidence
                            />
                        </div>
                    </div>

                    <div className="px-6 pt-4">
                        <IncidentActions
                            reportId={report.report_id}
                            status={report.status}
                            assessmentValues={assessmentValues}
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
                                        [
                                            'Barangay',
                                            report.barangay ??
                                                'Not yet assigned',
                                        ],
                                    ].map(([label, value]) => (
                                        <div
                                            key={label}
                                            className="flex items-start gap-2"
                                        >
                                            <p className="w-24 flex-shrink-0 text-xs text-brand-muted">
                                                {label}
                                            </p>
                                            <p className="text-xs font-semibold text-brand-navy">
                                                {value}
                                            </p>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            {/* Barangay Contact Information */}
                            <div className="rounded-xl border border-[rgba(43,45,66,0.1)] bg-brand-bg p-3.5">
                                <div className="mb-2 flex items-center justify-between">
                                    <p className="flex items-center gap-1.5 text-xs font-semibold tracking-wider text-brand-muted uppercase">
                                        <Building2
                                            size={13}
                                            className="text-brand-navy"
                                        />
                                        Barangay Local Contact
                                    </p>
                                    <span className="text-[11px] font-semibold text-brand-blue">
                                        {report.barangay ?? 'Unassigned'}
                                    </span>
                                </div>
                                {report.barangay_contact_person ? (
                                    <div className="space-y-2 text-xs">
                                        <div className="flex items-start justify-between gap-2">
                                            <span className="text-brand-muted">
                                                Contact Person
                                            </span>
                                            <span className="text-right font-semibold text-brand-navy">
                                                {report.barangay_contact_person}
                                                {report.barangay_contact_role ? (
                                                    <span className="ml-1 text-[11px] font-normal text-brand-muted">
                                                        (
                                                        {
                                                            report.barangay_contact_role
                                                        }
                                                        )
                                                    </span>
                                                ) : null}
                                            </span>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <span className="text-brand-muted">
                                                Contact Number
                                            </span>
                                            {report.barangay_contact_number ? (
                                                <a
                                                    href={`tel:${report.barangay_contact_number}`}
                                                    className="flex items-center gap-1 font-mono font-bold text-brand-blue hover:underline"
                                                >
                                                    <Phone size={12} />
                                                    {
                                                        report.barangay_contact_number
                                                    }
                                                </a>
                                            ) : (
                                                <span className="text-brand-muted">
                                                    —
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                ) : (
                                    <p className="text-xs text-brand-muted">
                                        {report.barangay
                                            ? `No official contact details registered for Brgy. ${report.barangay}.`
                                            : 'Assign a barangay to view local official emergency contacts.'}
                                    </p>
                                )}
                            </div>

                            <div>
                                <p className="mb-2 text-xs font-semibold tracking-wider text-brand-muted uppercase">
                                    Incident Description
                                </p>
                                <p className="rounded-lg bg-brand-bg p-3 text-sm leading-relaxed text-brand-navy">
                                    {report.description ||
                                        'No description provided.'}
                                </p>
                            </div>

                            {/* Status Change History */}
                            <div>
                                <div className="mb-3 flex flex-wrap items-center justify-between gap-2">
                                    <p className="text-xs font-semibold tracking-wider text-brand-muted uppercase">
                                        Status History
                                    </p>
                                    <span className="inline-flex items-center gap-1 rounded border border-[rgba(43,45,66,0.08)] bg-brand-bg px-2 py-0.5 text-[11px] font-medium text-brand-navy">
                                        <ShieldCheck
                                            size={11}
                                            className="text-brand-blue"
                                        />
                                        Verified DB Audit Trail ·{' '}
                                        {timeline.length}{' '}
                                        {timeline.length === 1
                                            ? 'entry'
                                            : 'entries'}
                                    </span>
                                </div>

                                <div className="rounded-xl border border-[rgba(43,45,66,0.08)] bg-brand-bg/70 p-4">
                                    <div className="space-y-1">
                                        {timeline.map((item, index) => {
                                            const isLatest =
                                                index === timeline.length - 1;
                                            const isLast =
                                                index === timeline.length - 1;
                                            const dotColor =
                                                STATUS_CFG[item.status]?.text ??
                                                '#1d3557';

                                            return (
                                                <div
                                                    key={item.id}
                                                    className="flex items-start gap-3"
                                                >
                                                    {/* Dot & connecting line */}
                                                    <div className="flex flex-col items-center pt-1">
                                                        <span
                                                            className={`size-3 rounded-full border-2 border-white shadow-xs ${
                                                                isLatest
                                                                    ? 'ring-2 ring-brand-navy/30'
                                                                    : ''
                                                            }`}
                                                            style={{
                                                                background:
                                                                    dotColor,
                                                            }}
                                                        />
                                                        {!isLast && (
                                                            <div className="my-1 min-h-[22px] w-0.5 flex-1 bg-gray-300/80" />
                                                        )}
                                                    </div>

                                                    {/* Entry content */}
                                                    <div
                                                        className={`flex-1 ${!isLast ? 'pb-2.5' : 'pb-0'}`}
                                                    >
                                                        <div className="flex flex-wrap items-center justify-between gap-2">
                                                            <StatusBadge
                                                                status={
                                                                    item.status
                                                                }
                                                            />
                                                            <span className="font-mono text-xs text-brand-muted">
                                                                {
                                                                    item.changed_at
                                                                }
                                                            </span>
                                                        </div>
                                                        <p className="mt-1 flex items-center gap-1.5 text-xs text-brand-muted">
                                                            <UserCheck
                                                                size={12}
                                                                className="shrink-0 text-brand-blue"
                                                            />
                                                            <span className="font-medium text-brand-navy">
                                                                {
                                                                    item.changed_by
                                                                }
                                                            </span>
                                                        </p>
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Right column */}
                        <div className="space-y-5">
                            <div>
                                <p className="mb-2 text-xs font-semibold tracking-wider text-brand-muted uppercase">
                                    Incident Location
                                </p>
                                <div className="relative">
                                    <SinglePointMap
                                        latitude={report.latitude}
                                        longitude={report.longitude}
                                    />
                                    <div className="absolute top-2 left-2 z-[20] flex items-center gap-1.5 rounded bg-white/90 px-2 py-1">
                                        <MapPin
                                            size={10}
                                            className="text-brand-red"
                                        />
                                        <p className="text-xs font-semibold text-brand-navy">
                                            {report.barangay ??
                                                'Lian, Batangas'}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div className="mb-2 flex items-center justify-between">
                                    <p className="text-xs font-semibold tracking-wider text-brand-muted uppercase">
                                        Fire Scene Photos
                                    </p>
                                    <span className="text-[11px] text-brand-muted">
                                        {(report.report_image ? 1 : 0) +
                                            (report.evidence_photos?.length ?? 0)}{' '}
                                        total
                                    </span>
                                </div>
                                {report.report_image ||
                                (report.evidence_photos &&
                                    report.evidence_photos.length > 0) ? (
                                    <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        {report.report_image && (
                                            <ReportPhoto
                                                path={report.report_image}
                                                label="Reported Photo"
                                            />
                                        )}
                                        {report.evidence_photos?.map((ev) => (
                                            <ReportPhoto
                                                key={ev.evidence_id}
                                                path={ev.image_path}
                                                label="Fire Scene Photo"
                                                caption={ev.caption}
                                            />
                                        ))}
                                    </div>
                                ) : (
                                    <div
                                        className="flex h-24 flex-col items-center justify-center gap-1 rounded-lg"
                                        style={{ background: '#F8F9FA' }}
                                    >
                                        <Camera
                                            size={16}
                                            className="text-brand-muted"
                                        />
                                        <p className="text-xs text-brand-muted">
                                            No photos submitted or attached
                                        </p>
                                    </div>
                                )}
                            </div>

                            <div>
                                <p className="mb-2 text-xs font-semibold tracking-wider text-brand-muted uppercase">
                                    AI Image Verification
                                </p>
                                <AiVerificationCard
                                    label={report.ai_fire_label}
                                    confidence={report.ai_fire_confidence}
                                    verifiedAt={report.ai_verified_at}
                                />
                            </div>

                            <div>
                                <p className="mb-2 text-xs font-semibold tracking-wider text-brand-muted uppercase">
                                    Linked / Related Reports
                                </p>
                                {linkedReports.length === 0 ? (
                                    <div className="rounded-lg bg-brand-bg p-3">
                                        <p className="text-xs text-brand-muted">
                                            No linked reports for this incident.
                                        </p>
                                    </div>
                                ) : (
                                    <div className="space-y-2">
                                        {linkedReports.map((lr) => (
                                            <Link
                                                key={lr.report_id}
                                                href={showIncident(
                                                    lr.report_id,
                                                )}
                                                className="flex items-center justify-between rounded-lg bg-brand-bg p-3 transition-colors hover:bg-gray-100"
                                            >
                                                <span className="flex items-center gap-2 font-mono text-xs font-medium text-brand-blue">
                                                    <FileText size={11} />
                                                    {lr.reference}
                                                </span>
                                                <StatusBadge
                                                    status={lr.status}
                                                />
                                            </Link>
                                        ))}
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* BFP Assessment Section (Full Width) */}
                    {hasAssessment && (
                        <div
                            className="border-t px-6 py-6"
                            style={{ borderColor: 'rgba(43,45,66,0.1)' }}
                        >
                            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                                <div className="flex items-center gap-2.5">
                                    <div
                                        className="rounded-lg p-2"
                                        style={{
                                            background: 'rgba(29,53,87,0.08)',
                                        }}
                                    >
                                        <ShieldCheck
                                            size={18}
                                            className="text-brand-navy"
                                        />
                                    </div>
                                    <div>
                                        <h3 className="text-sm font-bold text-brand-navy">
                                            BFP Assessment
                                        </h3>
                                        <p className="text-xs text-brand-muted">
                                            Official investigation findings and
                                            casualty report
                                        </p>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    onClick={() => setEditOpen(true)}
                                    className="flex items-center gap-1.5 rounded-lg border border-[rgba(43,45,66,0.18)] bg-white px-3 py-1.5 text-xs font-semibold text-brand-navy shadow-xs transition hover:border-brand-navy/30 hover:bg-brand-bg"
                                >
                                    <Edit
                                        size={13}
                                        className="text-brand-blue"
                                    />
                                    Edit Details
                                </button>
                            </div>

                            <div className="space-y-4">
                                <div className="grid gap-4 md:grid-cols-3">
                                    {report.cause_of_fire && (
                                        <div
                                            className={`flex flex-col justify-between rounded-xl border border-[rgba(43,45,66,0.1)] bg-brand-bg p-4 ${
                                                report.casualties !== null
                                                    ? 'md:col-span-2'
                                                    : 'md:col-span-3'
                                            }`}
                                        >
                                            <p className="text-xs font-semibold tracking-wider text-brand-muted uppercase">
                                                Cause of Fire
                                            </p>
                                            <p className="my-2 text-base font-bold text-brand-navy sm:text-lg">
                                                {report.cause_of_fire}
                                            </p>
                                            <p className="text-xs text-brand-muted">
                                                Identified cause determined by
                                                responding BFP investigation
                                                team
                                            </p>
                                        </div>
                                    )}

                                    {report.casualties !== null && (
                                        <div
                                            className={`flex flex-col justify-between rounded-xl border border-[rgba(43,45,66,0.1)] bg-brand-bg p-4 ${
                                                !report.cause_of_fire
                                                    ? 'md:col-span-3'
                                                    : 'md:col-span-1'
                                            }`}
                                        >
                                            <div className="flex items-center justify-between">
                                                <p className="text-xs font-semibold tracking-wider text-brand-muted uppercase">
                                                    Casualties
                                                </p>
                                                <span
                                                    className={`size-2 rounded-full ${
                                                        report.casualties > 0
                                                            ? 'animate-pulse bg-brand-red'
                                                            : 'bg-brand-green'
                                                    }`}
                                                />
                                            </div>
                                            <div className="my-2 flex items-baseline gap-2">
                                                <span
                                                    className={`font-mono text-3xl font-extrabold ${
                                                        report.casualties > 0
                                                            ? 'text-brand-red'
                                                            : 'text-brand-navy'
                                                    }`}
                                                >
                                                    {report.casualties}
                                                </span>
                                                <span className="text-xs font-medium text-brand-muted">
                                                    {report.casualties === 1
                                                        ? 'person'
                                                        : 'persons'}
                                                </span>
                                            </div>
                                            <p className="text-xs text-brand-muted">
                                                {report.casualties > 0
                                                    ? 'Casualties recorded on scene'
                                                    : 'Zero casualties recorded'}
                                            </p>
                                        </div>
                                    )}
                                </div>

                                {report.notes && (
                                    <div className="rounded-xl border border-[rgba(43,45,66,0.1)] bg-brand-bg p-4">
                                        <p className="mb-2 text-xs font-semibold tracking-wider text-brand-muted uppercase">
                                            Investigation Notes & Officer
                                            Remarks
                                        </p>
                                        <p className="text-sm leading-relaxed whitespace-pre-line text-brand-navy">
                                            {report.notes}
                                        </p>
                                    </div>
                                )}
                            </div>
                        </div>
                    )}
                </PortalCard>

                {hasAssessment && (
                    <EditDetailsModal
                        reportId={report.report_id}
                        open={editOpen}
                        onOpenChange={setEditOpen}
                        initialValues={assessmentValues}
                    />
                )}
            </div>
        </PortalLayout>
    );
}
