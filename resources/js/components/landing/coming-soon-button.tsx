import { Smartphone } from 'lucide-react';

type ComingSoonButtonProps = {
    className?: string;
};

export function ComingSoonButton({ className = '' }: ComingSoonButtonProps) {
    return (
        <button
            type="button"
            disabled
            aria-disabled="true"
            aria-label="FireSight mobile app coming soon"
            className={`inline-flex cursor-not-allowed items-center justify-center gap-2.5 rounded-full bg-gradient-to-r from-firesight-orange-light to-firesight-orange px-6 py-3.5 text-sm font-bold text-white opacity-80 shadow-[0_12px_28px_-10px_rgba(255,106,19,0.8)] focus-visible:ring-2 focus-visible:ring-white focus-visible:outline-none ${className}`}
        >
            <Smartphone className="size-4" aria-hidden="true" />
            Mobile App Coming Soon
        </button>
    );
}
