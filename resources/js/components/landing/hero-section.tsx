import { MapPin, PlayCircle } from 'lucide-react';
import { ComingSoonButton } from './coming-soon-button';
import { LowlandLandscape } from './lowland-landscape';
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

            <LowlandLandscape className="pointer-events-none absolute inset-x-0 bottom-0 h-56 w-full" />

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
                        Detect. Alert. Protect.
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
