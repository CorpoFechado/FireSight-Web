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
        severity_level: string | null;
        cause_of_fire: string | null;
        casualties: number | null;
        notes: string | null;
    };
}) {
    const [resolveOpen, setResolveOpen] = useState(false);
    const [editOpen, setEditOpen] = useState(false);

    if (status === 'invalid') {
        return null;
    }

    if (status === 'pending') {
        return (
            <div className="flex items-center gap-2 rounded-lg bg-brand-bg px-3.5 py-2 text-xs text-brand-muted border border-[rgba(43,45,66,0.08)]">
                <Smartphone size={14} className="text-brand-blue shrink-0" />
                <span>
                    Pending triage & dispatch are managed by field personnel on the FireSight Mobile app.
                </span>
            </div>
        );
    }

    if (status === 'accepted') {
        return (
            <div className="flex items-center gap-2 rounded-lg bg-brand-bg px-3.5 py-2 text-xs text-brand-muted border border-[rgba(43,45,66,0.08)]">
                <Smartphone size={14} className="text-brand-blue shrink-0" />
                <span>
                    Report accepted · Unit dispatching is managed by field personnel on the mobile app.
                </span>
            </div>
        );
    }

    if (status === 'dispatched') {
        return (
            <div className="flex flex-wrap items-center gap-2">
                <button
                    onClick={() => setResolveOpen(true)}
                    className="flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs font-semibold text-white shadow-xs hover:opacity-90 transition-opacity"
                    style={{ background: '#2A9D8F' }}
                >
                    <CheckCircle size={14} /> Mark as Resolved / Add Details
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
                    className="flex items-center gap-1.5 rounded-lg border border-[rgba(43,45,66,0.18)] bg-white px-3.5 py-2 text-xs font-semibold text-brand-navy shadow-xs transition hover:bg-brand-bg hover:border-brand-navy/30"
                >
                    <Edit size={14} className="text-brand-blue" /> Edit Incident Details
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
