import { Head, usePage } from '@inertiajs/react';
import { AppCtaSection } from '@/components/landing/app-cta-section';
import { AudienceSection } from '@/components/landing/audience-section';
import { FeaturesSection } from '@/components/landing/features-section';
import { HeroSection } from '@/components/landing/hero-section';
import { HowItWorksSection } from '@/components/landing/how-it-works-section';
import { LandingFooter } from '@/components/landing/landing-footer';
import { LandingHeader } from '@/components/landing/landing-header';
import type { User } from '@/types';

type WelcomePageProps = {
    auth?: {
        user?: User | null;
    };
};

export default function Welcome() {
    const { auth } = usePage<WelcomePageProps>().props;

    return (
        <div className="min-h-screen overflow-x-clip bg-white font-sans antialiased selection:bg-firesight-orange selection:text-white">
            <Head title="FireSight — Detect. Alert. Protect. | Lian, Batangas" />
            <LandingHeader user={auth?.user} />
            <main>
                <HeroSection />
                <FeaturesSection />
                <AudienceSection />
                <HowItWorksSection />
                <AppCtaSection />
            </main>
            <LandingFooter />
        </div>
    );
}
