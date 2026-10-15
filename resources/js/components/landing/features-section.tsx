import {
    Activity,
    BarChart3,
    BellRing,
    CalendarClock,
    Map,
    ScanSearch,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

type Feature = {
    title: string;
    description: string;
    icon: LucideIcon;
};

const features: Feature[] = [
    {
        title: 'AI-Assisted Fire Detection',
        icon: ScanSearch,
        description:
            'Identify possible fire and smoke events early and help responders prioritize incidents.',
    },
    {
        title: 'Live Incident Map',
        icon: Map,
        description:
            'View reported incidents, affected barangays, and response activity on an interactive map.',
    },
    {
        title: 'Instant Alerts and Notifications',
        icon: BellRing,
        description:
            'Keep authorized personnel informed about new incidents and important status changes.',
    },
    {
        title: 'Response Tracking',
        icon: Activity,
        description:
            'Follow incidents from initial reporting and verification through coordination and resolution.',
    },
    {
        title: 'Barangay Risk Analytics',
        icon: BarChart3,
        description:
            'Review vulnerability and risk information to support preparedness and resource planning.',
    },
    {
        title: 'Personnel and Duty Coordination',
        icon: CalendarClock,
        description:
            'Manage responder availability, duty schedules, and operational assignments.',
    },
];

export function FeaturesSection() {
    return (
        <section
            id="features"
            aria-labelledby="features-heading"
            className="landing-section relative overflow-hidden bg-firesight-blue-soft py-16 lg:py-24"
        >
            <div
                className="absolute top-16 -left-28 size-64 rounded-full bg-[#dfe9ff]"
                aria-hidden="true"
            />
            <div
                className="absolute right-[-7rem] bottom-4 size-72 rounded-full bg-white/70"
                aria-hidden="true"
            />
            <div className="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div className="grid gap-6 lg:grid-cols-2 lg:items-end">
                    <div>
                        <p className="text-xs font-semibold tracking-[0.18em] text-firesight-orange uppercase">
                            Key Features
                        </p>
                        <h2
                            id="features-heading"
                            className="mt-3 max-w-xl text-3xl font-bold tracking-tight text-firesight-navy lg:text-4xl"
                        >
                            Essential Tools for Faster, Safer Response
                        </h2>
                    </div>
                    <p className="max-w-xl text-base leading-7 text-firesight-copy lg:justify-self-end">
                        FireSight brings incident monitoring, response
                        coordination, and community preparedness together in one
                        platform.
                    </p>
                </div>

                <div className="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {features.map(({ title, description, icon: Icon }) => (
                        <article
                            key={title}
                            className="landing-card rounded-2xl border border-slate-100 bg-white p-6 shadow-[0_10px_30px_-12px_rgba(11,27,63,0.15)] transition hover:-translate-y-1"
                        >
                            <span className="flex size-12 items-center justify-center rounded-xl bg-firesight-orange-pale text-firesight-orange">
                                <Icon className="size-6" aria-hidden="true" />
                            </span>
                            <h3 className="mt-5 text-lg font-bold text-firesight-navy">
                                {title}
                            </h3>
                            <p className="mt-2 text-sm leading-6 text-firesight-copy">
                                {description}
                            </p>
                        </article>
                    ))}
                </div>
            </div>
        </section>
    );
}
