import { ComingSoonButton } from './coming-soon-button';
import { PhoneMockup } from './phone-mockup';

export function AppCtaSection() {
    return (
        <section
            aria-labelledby="app-cta-heading"
            className="relative overflow-hidden bg-gradient-to-r from-firesight-navy via-firesight-navy-mid to-firesight-navy-highlight py-16 text-white lg:py-20"
        >
            <div
                className="absolute -right-20 -bottom-48 size-[34rem] rounded-full bg-firesight-orange/25 blur-3xl"
                aria-hidden="true"
            />
            <svg
                className="absolute inset-x-0 bottom-0 h-28 w-full opacity-60"
                viewBox="0 0 1440 180"
                preserveAspectRatio="none"
                aria-hidden="true"
            >
                <path
                    d="M0 180v-52c190-18 346-12 520 4 198 19 362-20 546-13 150 5 262 24 374 11v50z"
                    fill="#07152f"
                />
                <g fill="#06132e">
                    <path d="M80 125h86v45H80z" />
                    <path d="m67 125 56-35 56 35z" />
                    <path d="M1260 132h96v38h-96z" />
                    <path d="m1246 132 62-33 62 33z" />
                    <path d="M213 150c0-22 11-36 24-36s24 14 24 36z" />
                    <path d="M233 106h7v55h-7z" />
                </g>
                <g fill="none" stroke="#13306b" strokeWidth="2" opacity=".8">
                    <path d="M0 145c254-18 485-1 720 12 235 12 462 4 720-14" />
                    <path d="M530 180c30-30 67-48 112-59" />
                    <path d="M910 180c-30-30-67-48-112-59" />
                </g>
            </svg>
            <div className="relative mx-auto grid max-w-7xl items-center gap-10 px-4 sm:px-6 md:grid-cols-12 lg:px-8">
                <div className="hidden md:col-span-3 md:block">
                    <PhoneMockup compact />
                </div>
                <div className="md:col-span-5">
                    <p className="text-xs font-semibold tracking-[0.18em] text-firesight-orange uppercase">
                        Get the App
                    </p>
                    <h2
                        id="app-cta-heading"
                        className="mt-3 text-3xl font-bold tracking-tight lg:text-4xl"
                    >
                        Download FireSight Soon
                    </h2>
                    <p className="mt-4 max-w-lg leading-7 text-white/75">
                        The resident mobile app is in development and will soon
                        make community reporting and safety updates easier to
                        access.
                    </p>
                    <div className="mt-7">
                        <ComingSoonButton />
                    </div>
                </div>
                <div className="md:col-span-4 md:justify-self-end">
                    <img
                        src="/images/firesight-logo.png"
                        alt="FireSight"
                        className="h-12 w-auto"
                    />
                    <p className="mt-4 text-xl font-semibold text-white italic">
                        Sa Ligtas na Lian, Sama-sama.
                    </p>
                    <svg
                        className="mt-2 h-3 w-64 max-w-full"
                        viewBox="0 0 260 12"
                        aria-hidden="true"
                    >
                        <path
                            d="M3 8C68 2 184 2 257 6"
                            fill="none"
                            stroke="#ff6a13"
                            strokeLinecap="round"
                            strokeWidth="4"
                        />
                    </svg>
                </div>
            </div>
        </section>
    );
}
