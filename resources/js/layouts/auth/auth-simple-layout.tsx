import { Link } from '@inertiajs/react';
import { AuroraBackground } from '@/components/aurora-background';
import { login } from '@/routes';
import type { AuthLayoutProps } from '@/types';

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div
            className="relative isolate flex min-h-svh flex-col items-center justify-center overflow-hidden p-6 md:p-10"
            style={{
                background: 'linear-gradient(165deg, #1d3557 0%, #16294a 55%, #10192e 100%)',
            }}
        >
            <AuroraBackground variant="dark" className="!fixed inset-0" />

            <div className="relative z-10 w-full max-w-sm">
                <div className="flex flex-col gap-8">
                    <div className="flex flex-col items-center gap-4">
                        <Link
                            href={login()}
                            className="flex flex-col items-center gap-3 font-medium"
                        >
                            <img
                                src="/images/firesight-logo.png"
                                alt="FireSight Logo"
                                className="h-16 w-auto object-contain drop-shadow-lg"
                            />
                            <span className="sr-only">{title}</span>
                        </Link>

                        <div className="space-y-1.5 text-center">
                            <h1 className="text-xl font-semibold text-white">{title}</h1>
                            <p className="text-center text-sm text-white/60">
                                {description}
                            </p>
                        </div>
                    </div>

                    <div
                        className="auth-card-light animate-fade-up rounded-3xl p-6"
                        style={{
                            background: 'rgba(255, 255, 255, 0.92)',
                            backdropFilter: 'blur(24px) saturate(160%)',
                            WebkitBackdropFilter: 'blur(24px) saturate(160%)',
                            border: '1px solid rgba(255, 255, 255, 0.6)',
                            boxShadow: 'var(--shadow-soft-lg)',
                            color: '#1d3557',
                        }}
                    >
                        {children}
                    </div>
                </div>
            </div>
        </div>
    );
}
