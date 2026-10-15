import { landingNavLinks } from './landing-header';

function scrollToSection(id: string) {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' });
}

export function LandingFooter() {
    return (
        <footer className="bg-firesight-navy text-white/65">
            <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
                <div className="flex flex-col items-center justify-between gap-7 border-b border-white/10 pb-8 text-center md:flex-row md:text-left">
                    <div className="flex flex-col items-center gap-3 sm:flex-row md:items-center">
                        <button
                            type="button"
                            onClick={() => scrollToSection('home')}
                            className="rounded-lg focus-visible:ring-2 focus-visible:ring-firesight-orange focus-visible:outline-none"
                            aria-label="FireSight, return to home"
                        >
                            <img
                                src="/images/firesight-logo.png"
                                alt="FireSight"
                                className="h-8 w-auto"
                            />
                        </button>
                        <span
                            className="hidden h-6 w-px bg-white/15 sm:block"
                            aria-hidden="true"
                        />
                        <p className="text-sm">
                            Smarter Monitoring, Safer Tomorrow.
                        </p>
                    </div>
                    <nav
                        className="flex flex-wrap justify-center gap-x-6 gap-y-3"
                        aria-label="Footer navigation"
                    >
                        {landingNavLinks.map((link) => (
                            <button
                                key={link.id}
                                type="button"
                                onClick={() => scrollToSection(link.id)}
                                className="text-sm transition hover:text-white focus-visible:ring-2 focus-visible:ring-firesight-orange focus-visible:outline-none"
                            >
                                {link.label}
                            </button>
                        ))}
                    </nav>
                </div>
                <div className="flex flex-col items-center justify-between gap-3 pt-6 text-center text-xs sm:flex-row sm:text-left">
                    <p>
                        © {new Date().getFullYear()} FireSight. All rights
                        reserved.
                    </p>
                    <p>
                        Lian, Batangas{' '}
                        <span
                            className="px-2 text-firesight-orange"
                            aria-hidden="true"
                        >
                            |
                        </span>{' '}
                        <span className="italic">
                            Sa Ligtas na Lian, Sama-sama.
                        </span>
                    </p>
                </div>
            </div>
        </footer>
    );
}
