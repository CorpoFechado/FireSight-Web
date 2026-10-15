import { Link } from '@inertiajs/react';
import { Settings, ShieldCheck, Users } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { login } from '@/routes';
import { ComingSoonButton } from './coming-soon-button';

type Audience = {
    title: string;
    description: string;
    icon: LucideIcon;
    badgeClass: string;
};

const audiences: Audience[] = [
    {
        title: 'Residents',
        icon: Users,
        badgeClass: 'bg-firesight-orange',
        description:
            'Report incidents, receive safety information, and follow community updates.',
    },
    {
        title: 'BFP Personnel',
        icon: ShieldCheck,
        badgeClass: 'bg-firesight-navy-highlight',
        description:
            'Verify incidents, coordinate response operations, and monitor active situations.',
    },
    {
        title: 'Administrators',
        icon: Settings,
        badgeClass: 'bg-gradient-to-br from-purple-500 to-indigo-700',
        description:
            'Manage personnel and resources, publish announcements, and review operational analytics.',
    },
];

export function AudienceSection() {
    return (
        <section
            id="about"
            aria-labelledby="audience-heading"
            className="landing-section relative overflow-hidden bg-firesight-blue-pale py-16 lg:py-24"
        >
            <div
                className="absolute top-1/2 right-[-12rem] size-[34rem] -translate-y-1/2 rounded-full bg-white/70"
                aria-hidden="true"
            />
            <div className="relative mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-12 lg:items-center lg:px-8">
                <div className="lg:col-span-4">
                    <p className="text-xs font-semibold tracking-[0.18em] text-firesight-orange uppercase">
                        Built for Everyone
                    </p>
                    <h2
                        id="audience-heading"
                        className="mt-3 text-3xl font-bold tracking-tight text-firesight-navy lg:text-4xl"
                    >
                        A Safer Community Through Collaboration
                    </h2>
                    <p className="mt-5 text-base leading-7 text-firesight-copy">
                        FireSight connects residents, BFP personnel, and
                        administrators so reports move quickly from the
                        community to verified, coordinated action.
                    </p>
                    <div className="mt-7 flex flex-col items-start gap-4">
                        <ComingSoonButton />
                        <Link
                            href={login()}
                            className="text-sm font-bold text-firesight-navy underline decoration-firesight-orange decoration-2 underline-offset-4 transition hover:text-firesight-orange focus-visible:ring-2 focus-visible:ring-firesight-orange focus-visible:outline-none"
                        >
                            Admin Portal
                        </Link>
                    </div>
                </div>

                <div className="grid gap-6 md:grid-cols-3 lg:col-span-8">
                    {audiences.map(
                        ({ title, description, icon: Icon, badgeClass }) => (
                            <article
                                key={title}
                                className="landing-card rounded-2xl border border-slate-100 bg-white p-6 shadow-[0_10px_30px_-12px_rgba(11,27,63,0.15)] transition hover:-translate-y-1"
                            >
                                <span
                                    className={`flex size-14 items-center justify-center rounded-full text-white shadow-lg ${badgeClass}`}
                                >
                                    <Icon
                                        className="size-6"
                                        aria-hidden="true"
                                    />
                                </span>
                                <h3 className="mt-6 text-lg font-bold text-firesight-navy">
                                    {title}
                                </h3>
                                <p className="mt-3 text-sm leading-6 text-firesight-copy">
                                    {description}
                                </p>
                            </article>
                        ),
                    )}
                </div>
            </div>
        </section>
    );
}
