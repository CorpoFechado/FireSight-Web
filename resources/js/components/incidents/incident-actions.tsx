import { router } from '@inertiajs/react';
import { CheckCircle, Navigation, XCircle } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import { accept, invalidate, updateStatus } from '@/routes/incidents';
import type { BfpRole } from '@/types/auth';
import type { ReportStatus } from '@/lib/fire-status';
import { AcceptReportModal } from './accept-report-modal';
import { ResolveReportModal } from './resolve-report-modal';

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
    const [acceptOpen, setAcceptOpen] = useState(false);
    const [resolveOpen, setResolveOpen] = useState(false);
    const [confirming, setConfirming] = useState<'invalidate' | 'dispatch' | null>(null);
    const [processing, setProcessing] = useState(false);

    const isAdmin = role === 'bfp_admin';

    const runInvalidate = () => {
        setProcessing(true);
        router.post(invalidate(reportId).url, {}, {
            preserveScroll: true,
            onError: (errors) => toast.error(Object.values(errors)[0] ?? 'Could not mark this report as invalid.'),
            onFinish: () => {
                setProcessing(false);
                setConfirming(null);
            },
        });
    };

    const runStatusUpdate = (next: 'dispatched') => {
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

    if (status === 'resolved' || status === 'invalid') {
        return null;
    }

    if (confirming) {
        const copy = {
            invalidate: { label: 'Mark as Invalid', color: '#E63946', run: runInvalidate },
            dispatch: { label: 'Mark as Dispatched', color: '#457B9D', run: () => runStatusUpdate('dispatched') },
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
                        onClick={() => setAcceptOpen(true)}
                        className="flex items-center gap-1.5 rounded-lg bg-brand-navy px-3 py-2 text-xs font-semibold text-white hover:bg-brand-navy/90"
                    >
                        <CheckCircle size={14} /> Accept Report
                    </button>
                    <button
                        onClick={() => setConfirming('invalidate')}
                        className="flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold text-white hover:opacity-90"
                        style={{ background: '#E63946' }}
                    >
                        <XCircle size={14} /> Mark as Invalid
                    </button>
                    <AcceptReportModal
                        reportId={reportId}
                        barangays={barangays}
                        suggestedBarangayId={suggestedBarangayId}
                        open={acceptOpen}
                        onOpenChange={setAcceptOpen}
                    />
                </>
            )}

            {status === 'pending' && !isAdmin && (
                <p className="text-xs text-brand-muted">Waiting on a BFP administrator to accept this report.</p>
            )}

            {status === 'accepted' && (
                <button
                    onClick={() => setConfirming('dispatch')}
                    className="flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold text-white hover:opacity-90"
                    style={{ background: '#457B9D' }}
                >
                    <Navigation size={14} /> Mark as Dispatched
                </button>
            )}

            {status === 'dispatched' && (
                <>
                    <button
                        onClick={() => setResolveOpen(true)}
                        className="flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold text-white hover:opacity-90"
                        style={{ background: '#2A9D8F' }}
                    >
                        <CheckCircle size={14} /> Mark as Resolved
                    </button>
                    <ResolveReportModal
                        reportId={reportId}
                        open={resolveOpen}
                        onOpenChange={setResolveOpen}
                    />
                </>
            )}
        </div>
    );
}
