import { MapPin, PlayCircle } from 'lucide-react';
import { ComingSoonButton } from './coming-soon-button';
import { PhoneMockup } from './phone-mockup';

export function HeroSection() {
    const showHowItWorks = () => {
        document
            .getElementById('how-it-works')
            ?.scrollIntoView({ behavior: 'smooth' });
    };

    return (
        <section
            id="home"
            aria-labelledby="hero-heading"
            className="landing-section relative overflow-hidden bg-gradient-to-br from-firesight-navy via-firesight-navy-mid to-firesight-navy-highlight pt-28 pb-16 text-white lg:pt-32 lg:pb-24"
        >
            <div
                className="absolute top-0 right-[-12rem] size-[36rem] rounded-full bg-firesight-orange/30 blur-3xl"
                aria-hidden="true"
            />
            <div
                className="absolute top-20 left-[-16rem] size-[30rem] rounded-full bg-white/5"
                aria-hidden="true"
            />

            <svg
                className="pointer-events-none absolute inset-x-0 bottom-0 h-56 w-full"
                viewBox="0 0 1440 260"
                preserveAspectRatio="none"
                aria-hidden="true"
            >
                <path
                    d="M0 186C180 164 320 174 480 188s320 9 480-5 302-10 480 7v70H0z"
                    fill="#081735"
                    opacity=".72"
                />
                <path
                    d="M0 210c210-30 382-17 572 2 207 20 402-26 568-16 118 7 213 23 300 14v50H0z"
                    fill="#06132e"
                    opacity=".88"
                />
                <g fill="#07142f" opacity=".9">
                    <path d="M92 184h72v43H92z" />
                    <path d="m80 184 48-32 48 32z" />
                    <path d="M122 199h15v28h-15z" fill="#0f2552" />
                    <path d="M224 195h88v36h-88z" />
                    <path d="m212 195 56-30 56 30z" />
                    <path d="M278 207h18v24h-18z" fill="#0f2552" />
                    <path d="M1110 188h78v42h-78z" />
                    <path d="m1097 188 52-34 52 34z" />
                    <path d="M1123 202h16v28h-16z" fill="#0f2552" />
                    <path d="M1250 198h92v34h-92z" />
                    <path d="m1238 198 58-29 58 29z" />
                </g>
                <g fill="#06132e">
                    <path d="M36 224c0-17 8-27 18-27s18 10 18 27z" />
                    <path d="M52 194h5v38h-5z" />
                    <path d="M354 225c0-20 10-32 22-32s22 12 22 32z" />
                    <path d="M373 188h6v43h-6z" />
                    <path d="M1025 224c0-18 9-29 20-29s20 11 20 29z" />
                    <path d="M1042 190h6v40h-6z" />
                    <path d="M1370 225c0-21 10-34 23-34s23 13 23 34z" />
                    <path d="M1390 186h6v46h-6z" />
                </g>
                <g fill="none" stroke="#13306b" strokeWidth="2" opacity=".72">
                    <path d="M0 222c260-25 480-5 720 10s460 5 720-16" />
                    <path d="M0 238c270-19 505-2 720 9s462 2 720-12" />
                    <path d="M470 260c36-34 78-57 124-68" />
                    <path d="M620 260c16-34 35-58 58-74" />
                    <path d="M818 260c-16-34-35-58-58-74" />
                    <path d="M968 260c-36-34-78-57-124-68" />
                </g>
                <path
                    d="M0 251c210-9 428-5 720 2 291 7 514 5 720-4v11H0z"
                    fill="#041027"
                />
            </svg>

            <div className="relative mx-auto grid max-w-7xl items-center gap-16 px-4 sm:px-6 lg:grid-cols-12 lg:gap-8 lg:px-8">
                <div className="lg:col-span-7">
                    <p className="flex items-center gap-2 text-sm font-medium text-white/80">
                        <MapPin
                            className="size-4 text-firesight-orange"
                            aria-hidden="true"
                        />
                        Lian, Batangas
                    </p>
                    <h1
                        id="hero-heading"
                        className="mt-5 text-5xl leading-[1.05] font-extrabold tracking-tight lg:text-6xl"
                    >
                        <span className="block text-firesight-orange">
                            FireSight
                        </span>
                        <span className="block text-white">
                            Detect. Alert. Protect.
                        </span>
                    </h1>
                    <p className="mt-6 max-w-xl text-base leading-8 text-white/80 sm:text-lg">
                        A community reporting, fire monitoring, and emergency
                        response system built to help create a safer and more
                        resilient Lian, Batangas.
                    </p>
                    <div className="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <ComingSoonButton />
                        <button
                            type="button"
                            onClick={showHowItWorks}
                            className="inline-flex items-center justify-center gap-2.5 rounded-full border border-white/40 px-6 py-3.5 text-sm font-bold text-white transition hover:bg-white/10 focus-visible:ring-2 focus-visible:ring-white focus-visible:outline-none"
                        >
                            <PlayCircle className="size-5" aria-hidden="true" />
                            Learn More
                        </button>
                    </div>
                </div>

                <div className="relative z-10 flex justify-center lg:col-span-5 lg:justify-end">
                    <PhoneMockup />
                </div>
            </div>
        </section>
    );
}
