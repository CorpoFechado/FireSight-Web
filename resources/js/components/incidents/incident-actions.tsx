import { CheckCircle, Edit, Smartphone } from 'lucide-react';
import { useState } from 'react';
import type { ReportStatus } from '@/lib/fire-status';
import { EditDetailsModal } from './edit-details-modal';
import { ResolveReportModal } from './resolve-report-modal';

export function IncidentActions({
    reportId,
    status,
    assessmentValues,
}: {
    reportId: number;
    status: ReportStatus;
    assessmentValues?: {
        incident_type: string | null;
        alarm_level: string | null;
        cause_of_fire: string | null;
        casualties: number | null;
        notes: string | null;
    };
}) {
    const [resolveOpen, setResolveOpen] = useState(false);
    const [editOpen, setEditOpen] = useState(false);
    const hasAssessmentDetails = Boolean(
        assessmentValues?.incident_type ||
        assessmentValues?.alarm_level ||
        assessmentValues?.cause_of_fire ||
        assessmentValues?.notes ||
        (assessmentValues?.casualties !== null &&
            assessmentValues?.casualties !== undefined),
    );

    if (status === 'invalid') {
        return null;
    }

    if (status === 'pending') {
        return (
            <div className="flex items-center gap-2 rounded-lg border border-[rgba(43,45,66,0.08)] bg-brand-bg px-3.5 py-2 text-xs text-brand-muted">
                <Smartphone size={14} className="shrink-0 text-brand-blue" />
                <span>
                    Pending triage & dispatch are managed by field personnel on
                    the FireSight Mobile app.
                </span>
            </div>
        );
    }

    if (status === 'accepted') {
        return (
            <div className="flex items-center gap-2 rounded-lg border border-[rgba(43,45,66,0.08)] bg-brand-bg px-3.5 py-2 text-xs text-brand-muted">
                <Smartphone size={14} className="shrink-0 text-brand-blue" />
                <span>
                    Report accepted · Unit dispatching is managed by field
                    personnel on the mobile app.
                </span>
            </div>
        );
    }

    if (status === 'dispatched') {
        return (
            <div className="flex flex-wrap items-center gap-2">
                <button
                    onClick={() => setResolveOpen(true)}
                    className="flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs font-semibold text-white shadow-xs transition-opacity hover:opacity-90"
                    style={{ background: '#2A9D8F' }}
                >
                    <CheckCircle size={14} /> Mark as Resolved
                </button>
                <ResolveReportModal
                    reportId={reportId}
                    open={resolveOpen}
                    onOpenChange={setResolveOpen}
                />
            </div>
        );
    }

    if (status === 'resolved') {
        return (
            <div className="flex flex-wrap items-center gap-2">
                <button
                    onClick={() => setEditOpen(true)}
                    className="flex items-center gap-1.5 rounded-lg border border-[rgba(43,45,66,0.18)] bg-white px-3.5 py-2 text-xs font-semibold text-brand-navy shadow-xs transition hover:border-brand-navy/30 hover:bg-brand-bg"
                >
                    <Edit size={14} className="text-brand-blue" />{' '}
                    {hasAssessmentDetails
                        ? 'Edit Incident Details'
                        : 'Add Incident Details'}
                </button>
                {assessmentValues && (
                    <EditDetailsModal
                        reportId={reportId}
                        open={editOpen}
                        onOpenChange={setEditOpen}
                        initialValues={assessmentValues}
                    />
                )}
            </div>
        );
    }

    return null;
}
