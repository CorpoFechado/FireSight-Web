import { ArrowRight, FileText, Flame, ShieldCheck, Users } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

type ProcessStep = { title: string; description: string; icon: LucideIcon };

const steps: ProcessStep[] = [
    {
        title: 'Report',
        icon: FileText,
        description:
            'An incident is submitted with its location and supporting details.',
    },
    {
        title: 'Verify',
        icon: ShieldCheck,
        description: 'BFP personnel review and validate the report.',
    },
    {
        title: 'Coordinate',
        icon: Users,
        description: 'Responders and resources are assigned to the incident.',
    },
    {
        title: 'Respond',
        icon: Flame,
        description:
            'Teams take action while the incident status is monitored.',
    },
];

export function HowItWorksSection() {
    return (
        <section
            id="how-it-works"
            aria-labelledby="how-it-works-heading"
            className="landing-section relative overflow-hidden bg-white py-16 lg:py-24"
        >
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div className="mx-auto max-w-2xl text-center">
                    <p className="text-xs font-semibold tracking-[0.18em] text-firesight-orange uppercase">
                        Simple Steps
                    </p>
                    <h2
                        id="how-it-works-heading"
                        className="mt-3 text-3xl font-bold tracking-tight text-firesight-navy lg:text-4xl"
                    >
                        How It Works
                    </h2>
                    <p className="mt-4 text-base text-firesight-copy sm:text-lg">
                        From report to response — in just a few steps.
                    </p>
                </div>
                <div className="mt-14 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4 lg:gap-10">
                    {steps.map(({ title, description, icon: Icon }, index) => (
                        <article key={title} className="relative text-center">
                            <div className="relative mx-auto flex size-20 items-center justify-center rounded-full bg-firesight-blue-pale text-firesight-navy-highlight">
                                <Icon className="size-8" aria-hidden="true" />
                                <span className="absolute -top-1 -left-1 flex size-7 items-center justify-center rounded-full bg-firesight-orange text-xs font-extrabold text-white ring-4 ring-white">
                                    {index + 1}
                                </span>
                            </div>
                            <h3 className="mt-5 text-lg font-bold text-firesight-navy">
                                {title}
                            </h3>
                            <p className="mx-auto mt-2 max-w-xs text-sm leading-6 text-firesight-copy">
                                {description}
                            </p>
                            {index < steps.length - 1 && (
                                <ArrowRight
                                    className="absolute top-8 -right-7 hidden size-5 text-firesight-orange lg:block"
                                    aria-hidden="true"
                                />
                            )}
                        </article>
                    ))}
                </div>
            </div>
        </section>
    );
}
