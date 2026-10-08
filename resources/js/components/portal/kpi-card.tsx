import { Link } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';

export function KPICard({
    label,
    value,
    sub,
    icon: Icon,
    accent,
    bgAccent,
    href,
}: {
    label: string;
    value: string | number;
    sub?: string;
    icon: LucideIcon;
    accent: string;
    bgAccent?: string;
    href?: string;
}) {
    const content = (
        <>
            <div className="flex items-center gap-3.5">
                <div
                    className="flex size-12 shrink-0 items-center justify-center rounded-2xl transition-transform duration-200 group-hover:scale-105"
                    style={{
                        backgroundColor: bgAccent || `${accent}18`,
                        color: accent,
                    }}
                >
                    <Icon size={24} strokeWidth={2} />
                </div>
                <p className="font-sans text-4xl font-extrabold tracking-tight text-brand-navy">
                    {value}
                </p>
            </div>
            <div className="mt-3">
                <p className="text-sm font-semibold text-brand-muted">
                    {label}
                </p>
                {sub && (
                    <p className="mt-1 text-xs text-brand-muted/75">{sub}</p>
                )}
            </div>
        </>
    );

    const baseClasses =
        'group surface-glass animate-fade-up flex flex-col justify-between rounded-2xl p-5 md:p-6 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[var(--shadow-soft-lg)]';

    if (href) {
        return (
            <Link href={href} className={baseClasses}>
                {content}
            </Link>
        );
    }

    return <div className={baseClasses}>{content}</div>;
}
