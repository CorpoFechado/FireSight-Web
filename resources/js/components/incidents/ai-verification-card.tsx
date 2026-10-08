import { Bot, CheckCircle2, Clock, Flame, Info, ShieldAlert, ShieldCheck, Sparkles } from 'lucide-react';
import { AI_FIRE_LABEL_CFG, type AiFireLabel } from '@/lib/fire-status';

interface AiVerificationCardProps {
    label: string | null;
    confidence: number | null;
    verifiedAt: string | null;
}

export function AiVerificationCard({
    label,
    confidence,
    verifiedAt,
}: AiVerificationCardProps) {
    if (!label) {
        return (
            <div className="rounded-xl border border-[rgba(43,45,66,0.1)] bg-brand-bg p-4">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                        <div className="flex size-7 items-center justify-center rounded-lg bg-gray-100 text-brand-muted">
                            <Bot size={15} />
                        </div>
                        <div>
                            <p className="text-xs font-bold text-brand-navy">AI Fire Verification</p>
                            <p className="text-[11px] text-brand-muted">Mobile App Detection Model</p>
                        </div>
                    </div>
                    <span className="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-brand-muted">
                        Unverified
                    </span>
                </div>
                <div className="mt-3 flex items-start gap-2 rounded-lg bg-white/70 p-2.5 text-xs text-brand-muted">
                    <Info size={14} className="mt-0.5 shrink-0 text-brand-muted" />
                    <p>
                        No automated AI classification received from the mobile app backend for this report.
                    </p>
                </div>
            </div>
        );
    }

    const key = label.toLowerCase();
    const isFire = key === 'fire';
    const isSmoke = key === 'smoke';
    const isNoFire = key === 'no_fire';

    const cfg = AI_FIRE_LABEL_CFG[key] ?? {
        label: label.replace(/_/g, ' '),
        bg: 'rgba(43, 45, 66, 0.05)',
        text: '#1D3557',
        border: 'rgba(43, 45, 66, 0.2)',
        badgeBg: '#F3F4F6',
        badgeText: '#4B5563',
    };

    const confidencePct =
        confidence !== null && confidence !== undefined
            ? Math.min(Math.max(confidence * 100, 0), 100)
            : null;

    return (
        <div
            className="rounded-xl border p-4 transition-all"
            style={{
                borderColor: cfg.border,
                backgroundColor: '#FFFFFF',
            }}
        >
            {/* Header */}
            <div className="flex items-center justify-between border-b pb-3" style={{ borderColor: 'rgba(43,45,66,0.08)' }}>
                <div className="flex items-center gap-2">
                    <div
                        className="flex size-7 items-center justify-center rounded-lg"
                        style={{ background: cfg.bg, color: cfg.text }}
                    >
                        {isFire ? (
                            <Flame size={16} />
                        ) : isSmoke ? (
                            <ShieldAlert size={16} />
                        ) : (
                            <ShieldCheck size={16} />
                        )}
                    </div>
                    <div>
                        <div className="flex items-center gap-1.5">
                            <p className="text-xs font-bold text-brand-navy">AI Fire Verification</p>
                            <span className="flex items-center gap-0.5 text-[10px] font-semibold text-brand-blue">
                                <Sparkles size={10} /> Mobile AI
                            </span>
                        </div>
                        <p className="text-[11px] text-brand-muted">Mobile App Automated Model</p>
                    </div>
                </div>

                <span
                    className="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold shadow-xs"
                    style={{ background: cfg.badgeBg, color: cfg.badgeText }}
                >
                    <span className="size-1.5 rounded-full" style={{ background: cfg.badgeText }} />
                    {cfg.label}
                </span>
            </div>

            {/* Metrics & Confidence */}
            <div className="mt-3 space-y-3">
                {confidencePct !== null ? (
                    <div>
                        <div className="flex items-center justify-between text-xs">
                            <span className="font-medium text-brand-muted">Model Confidence</span>
                            <span className="font-mono font-bold text-brand-navy">
                                {confidencePct.toFixed(1)}%
                            </span>
                        </div>
                        <div className="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                            <div
                                className="h-full rounded-full transition-all duration-500"
                                style={{
                                    width: `${confidencePct}%`,
                                    backgroundColor: cfg.text,
                                }}
                            />
                        </div>
                    </div>
                ) : (
                    <div className="text-xs text-brand-muted">Confidence score not specified</div>
                )}

                {/* Verification Timestamp & Source Note */}
                <div className="flex flex-wrap items-center justify-between gap-2 pt-1 text-[11px] text-brand-muted">
                    <div className="flex items-center gap-1">
                        <Clock size={12} className="text-brand-muted" />
                        <span>{verifiedAt ? `Verified: ${verifiedAt}` : 'Timestamp not recorded'}</span>
                    </div>
                    <span className="inline-flex items-center gap-1 text-[10px] font-medium text-brand-navy/70">
                        <CheckCircle2 size={11} className="text-brand-blue" />
                        Processed on mobile upload
                    </span>
                </div>
            </div>
        </div>
    );
}
