import { router } from '@inertiajs/react';
import { CheckCircle, ClipboardCheck, Navigation, XCircle } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import { reject, updateStatus } from '@/routes/incidents';
import type { BfpRole } from '@/types/auth';
import type { ReportStatus } from '@/lib/fire-status';
import { CompleteReportModal } from './complete-report-modal';
import { VerifyReportModal } from './verify-report-modal';

type Barangay = { barangay_id: number; barangay_name: string };

export function IncidentActions({
    reportId,
    status,
    role,
    barangays,
    suggestedBarangayId,
}: {
    reportId: number;
    status: ReportStatus;
    role: BfpRole;
    barangays: Barangay[];
    suggestedBarangayId: number | null;
}) {
    const [verifyOpen, setVerifyOpen] = useState(false);
    const [completeOpen, setCompleteOpen] = useState(false);
    const [confirming, setConfirming] = useState<'reject' | 'dispatch' | 'resolve' | null>(null);
    const [processing, setProcessing] = useState(false);

    const isAdmin = role === 'bfp_admin';

    const runReject = () => {
        setProcessing(true);
        router.post(reject(reportId).url, {}, {
            preserveScroll: true,
            onError: (errors) => toast.error(Object.values(errors)[0] ?? 'Could not reject this report.'),
            onFinish: () => {
                setProcessing(false);
                setConfirming(null);
            },
        });
    };

    const runStatusUpdate = (next: 'dispatched' | 'resolved') => {
        setProcessing(true);
        router.patch(updateStatus(reportId).url, { status: next }, {
            preserveScroll: true,
            onError: (errors) => toast.error(Object.values(errors)[0] ?? 'Could not update the status.'),
            onFinish: () => {
                setProcessing(false);
                setConfirming(null);
            },
        });
    };

    if (status === 'completed' || status === 'rejected') {
        return null;
    }

    if (confirming) {
        const copy = {
            reject: { label: 'Reject', color: '#E63946', run: runReject },
            dispatch: { label: 'Mark as Dispatched', color: '#457B9D', run: () => runStatusUpdate('dispatched') },
            resolve: { label: 'Mark as Resolved', color: '#2A9D8F', run: () => runStatusUpdate('resolved') },
        }[confirming];

        return (
            <div
                className="flex items-center justify-between rounded-lg px-4 py-3"
                style={{ background: '#FFFBEB', border: '1px solid rgba(244,162,97,0.35)' }}
            >
                <p className="text-sm font-semibold text-brand-navy">
                    Confirm: <span style={{ color: copy.color }}>{copy.label}</span> this report?
                </p>
                <div className="flex gap-2">
                    <button
                        onClick={() => setConfirming(null)}
                        className="rounded-lg px-3 py-1.5 text-xs font-semibold text-brand-muted"
                    >
                        Cancel
                    </button>
                    <button
                        onClick={copy.run}
                        disabled={processing}
                        className="rounded-lg px-3 py-1.5 text-xs font-semibold text-white disabled:opacity-50"
                        style={{ background: copy.color }}
                    >
                        Confirm
                    </button>
                </div>
            </div>
        );
    }

    return (
        <div className="flex flex-wrap items-center gap-2">
            {status === 'pending' && isAdmin && (
                <>
                    <button
                        onClick={() => setVerifyOpen(true)}
                        className="flex items-center gap-1.5 rounded-lg bg-brand-navy px-3 py-2 text-xs font-semibold text-white"
                    >
                        <CheckCircle size={14} /> Verify Report
                    </button>
                    <button
                        onClick={() => setConfirming('reject')}
                        className="flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold text-white"
                        style={{ background: '#E63946' }}
                    >
                        <XCircle size={14} /> Reject
                    </button>
                    <VerifyReportModal
                        reportId={reportId}
                        barangays={barangays}
                        suggestedBarangayId={suggestedBarangayId}
                        open={verifyOpen}
                        onOpenChange={setVerifyOpen}
                    />
                </>
            )}

            {status === 'pending' && !isAdmin && (
                <p className="text-xs text-brand-muted">Waiting on a BFP administrator to verify this report.</p>
            )}

            {status === 'verified' && (
                <button
                    onClick={() => setConfirming('dispatch')}
                    className="flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold text-white"
                    style={{ background: '#457B9D' }}
                >
                    <Navigation size={14} /> Mark as Dispatched
                </button>
            )}

            {status === 'dispatched' && (
                <button
                    onClick={() => setConfirming('resolve')}
                    className="flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold text-white"
                    style={{ background: '#2A9D8F' }}
                >
                    <CheckCircle size={14} /> Mark as Resolved
                </button>
            )}

            {status === 'resolved' && (
                <>
                    <button
                        onClick={() => setCompleteOpen(true)}
                        className="flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold text-white"
                        style={{ background: '#2A9D8F' }}
                    >
                        <ClipboardCheck size={14} /> Mark as Complete
                    </button>
                    <CompleteReportModal
                        reportId={reportId}
                        open={completeOpen}
                        onOpenChange={setCompleteOpen}
                    />
                </>
            )}
        </div>
    );
}
