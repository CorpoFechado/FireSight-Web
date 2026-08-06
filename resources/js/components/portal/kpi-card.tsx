import type { LucideIcon } from 'lucide-react';

export function KPICard({
    label,
    value,
    sub,
    icon: Icon,
    accent,
}: {
    label: string;
    value: string | number;
    sub?: string;
    icon: LucideIcon;
    accent: string;
}) {
    return (
        <div className="surface-glass press-scale animate-fade-up flex items-start gap-4 rounded-2xl p-5 hover:shadow-[var(--shadow-soft-lg)]">
            <div
                className="flex size-11 flex-shrink-0 items-center justify-center rounded-xl"
                style={{
                    background: `linear-gradient(135deg, ${accent}26 0%, ${accent}0d 100%)`,
                    boxShadow: `inset 0 1px 0 0 ${accent}20`,
                }}
            >
                <Icon size={20} style={{ color: accent }} />
            </div>
            <div className="min-w-0">
                <p className="text-xs font-medium tracking-wider text-brand-muted uppercase">{label}</p>
                <p className="mt-0.5 font-mono text-2xl leading-none font-bold text-brand-navy">{value}</p>
                {sub && <p className="mt-1 text-xs text-brand-muted">{sub}</p>}
            </div>
        </div>
    );
}
