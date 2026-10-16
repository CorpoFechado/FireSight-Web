import { Link } from '@inertiajs/react';
import { ArrowLeft, MapPin } from 'lucide-react';
import { LowlandLandscape } from '@/components/landing/lowland-landscape';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

export default function LoginLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div className="grid min-h-svh bg-firesight-blue-soft lg:grid-cols-[minmax(0,1.05fr)_minmax(28rem,0.95fr)]">
            <section className="relative flex min-h-[23rem] overflow-hidden bg-gradient-to-br from-firesight-navy via-firesight-navy-mid to-firesight-navy-highlight px-6 py-14 text-white sm:min-h-[28rem] lg:min-h-svh lg:px-12">
                <div
                    className="absolute -top-36 -left-36 size-96 rounded-full border border-firesight-orange/20 bg-firesight-orange/10"
                    aria-hidden="true"
                />
                <div
                    className="absolute top-1/4 right-[-10rem] size-[30rem] rounded-full bg-firesight-orange/15 blur-3xl"
                    aria-hidden="true"
                />
                <LowlandLandscape className="pointer-events-none absolute inset-x-0 bottom-0 h-44 w-full sm:h-56 lg:h-64" />

                <div className="relative z-10 m-auto flex max-w-2xl flex-col items-center text-center">
                    <Link
                        href={home()}
                        className="flex items-center justify-center gap-4 rounded-xl focus-visible:ring-2 focus-visible:ring-firesight-orange focus-visible:ring-offset-4 focus-visible:ring-offset-firesight-navy focus-visible:outline-none"
                        aria-label="FireSight home"
                    >
                        <span className="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
                            <span className="text-firesight-orange">Fire</span>
                            <span className="text-white">Sight</span>
                        </span>
                        <img
                            src="/images/firesight-logo.png"
                            alt=""
                            className="h-16 w-auto object-contain drop-shadow-2xl sm:h-20 lg:h-24"
                            aria-hidden="true"
                        />
                    </Link>

                    <div className="mt-8 flex items-center gap-2 text-lg font-semibold text-white/85 sm:text-xl">
                        <MapPin
                            className="size-5 text-firesight-orange"
                            aria-hidden="true"
                        />
                        Lian, Batangas
                    </div>

                    <p className="mt-4 max-w-xl text-3xl leading-tight font-extrabold tracking-tight sm:text-4xl lg:text-5xl">
                        Smarter Monitoring,
                        <span className="block text-firesight-orange">
                            Safer Tomorrow.
                        </span>
                    </p>
                </div>
            </section>

            <main className="relative flex items-center justify-center overflow-hidden px-4 py-12 sm:px-8 lg:px-12">
                <div
                    className="absolute -top-28 -right-28 size-96 rotate-12 bg-blue-100/60 [clip-path:polygon(48%_0,100%_0,100%_100%,0_70%)]"
                    aria-hidden="true"
                />
                <div
                    className="absolute -right-24 -bottom-36 size-80 rotate-12 bg-blue-100/50 [clip-path:polygon(50%_0,100%_55%,72%_100%,0_100%)]"
                    aria-hidden="true"
                />

                <Link
                    href={home()}
                    className="absolute top-5 left-5 z-20 inline-flex items-center gap-2 rounded-full px-3 py-2 text-sm font-bold text-firesight-navy transition hover:bg-white/80 hover:text-firesight-orange focus-visible:ring-2 focus-visible:ring-firesight-orange focus-visible:outline-none sm:top-7 sm:left-7"
                >
                    <ArrowLeft className="size-4" aria-hidden="true" />
                    Back to home
                </Link>

                <section
                    className="auth-card-light animate-fade-up relative z-10 w-full max-w-md rounded-3xl border border-white/80 bg-white/95 p-6 shadow-[0_24px_70px_-24px_rgba(11,27,63,0.28)] backdrop-blur-xl sm:p-9"
                    aria-labelledby="login-heading"
                >
                    <div className="mb-8 text-center">
                        <div
                            className="flex items-center gap-4"
                            aria-hidden="true"
                        >
                            <span className="h-px flex-1 bg-slate-200" />
                            <span className="text-[0.65rem] font-bold tracking-[0.28em] text-firesight-copy uppercase">
                                Authorized Access
                            </span>
                            <span className="h-px flex-1 bg-slate-200" />
                        </div>
                        <h1
                            id="login-heading"
                            className="mt-7 text-3xl font-extrabold tracking-tight text-firesight-navy"
                        >
                            {title}
                        </h1>
                        <p className="mt-2 text-sm leading-6 text-firesight-copy">
                            {description}
                        </p>
                    </div>

                    {children}
                </section>
            </main>
        </div>
    );
}
