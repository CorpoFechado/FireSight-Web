import type { ReactNode } from 'react';

export function PortalCard({ children, className = '' }: { children: ReactNode; className?: string }) {
    return (
        <div className={`surface-glass animate-fade-up overflow-hidden rounded-2xl ${className}`}>
            {children}
        </div>
    );
}
