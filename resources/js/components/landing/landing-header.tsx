import { Link } from '@inertiajs/react';
import { Menu, ShieldCheck, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { dashboard, login } from '@/routes';
import type { User } from '@/types';

export const landingNavLinks = [
    { label: 'Home', href: '#home', id: 'home' },
    { label: 'Features', href: '#features', id: 'features' },
    { label: 'About', href: '#about', id: 'about' },
    { label: 'How It Works', href: '#how-it-works', id: 'how-it-works' },
] as const;

type LandingHeaderProps = {
    user?: User | null;
};

function scrollToSection(id: string) {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' });
}

export function LandingHeader({ user }: LandingHeaderProps) {
    const [activeSection, setActiveSection] = useState('home');
    const [isMenuOpen, setIsMenuOpen] = useState(false);
    const [hasScrolled, setHasScrolled] = useState(false);
    const menuToggleRef = useRef<HTMLButtonElement>(null);

    useEffect(() => {
        const handleScroll = () => setHasScrolled(window.scrollY > 24);
        handleScroll();
        window.addEventListener('scroll', handleScroll, { passive: true });

        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    useEffect(() => {
        const observer = new IntersectionObserver(
            (entries) => {
                const visibleSection = entries
                    .filter((entry) => entry.isIntersecting)
                    .sort(
                        (first, second) =>
                            second.intersectionRatio - first.intersectionRatio,
                    )[0];

                if (visibleSection) {
                    setActiveSection(visibleSection.target.id);
                }
            },
            { rootMargin: '-20% 0px -60% 0px', threshold: [0.1, 0.4, 0.7] },
        );

        landingNavLinks.forEach(({ id }) => {
            const section = document.getElementById(id);

            if (section) {
                observer.observe(section);
            }
        });

        return () => observer.disconnect();
    }, []);

    useEffect(() => {
        if (!isMenuOpen) {
            return;
        }

        const handleEscape = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                setIsMenuOpen(false);
                menuToggleRef.current?.focus();
            }
        };

        document.addEventListener('keydown', handleEscape);

        return () => document.removeEventListener('keydown', handleEscape);
    }, [isMenuOpen]);

    const closeMenuAndScroll = (id: string) => {
        setIsMenuOpen(false);
        scrollToSection(id);
    };

    const portalRoute = user ? dashboard() : login();
    const portalLabel = user ? 'Dashboard' : 'Admin Portal';

    return (
        <header
            className={`sticky top-0 z-50 -mb-20 w-full transition-all duration-300 ${
                hasScrolled
                    ? 'border-b border-white/10 bg-firesight-navy/95 shadow-lg shadow-black/10 backdrop-blur-xl'
                    : 'border-b border-transparent bg-transparent'
            }`}
        >
            <div className="mx-auto flex h-20 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <button
                    type="button"
                    onClick={() => scrollToSection('home')}
                    className="flex items-center gap-2.5 rounded-lg focus-visible:ring-2 focus-visible:ring-firesight-orange focus-visible:ring-offset-2 focus-visible:ring-offset-firesight-navy focus-visible:outline-none"
                    aria-label="FireSight, return to home"
                >
                    <img
                        src="/images/firesight-logo.png"
                        alt=""
                        className="h-9 w-auto object-contain"
                        aria-hidden="true"
                    />
                    <span className="text-xl font-extrabold tracking-tight sm:text-5xl">
                        <span className="text-firesight-orange">Fire</span>
                        <span className="text-white">Sight</span>
                    </span>
                </button>

                <nav
                    className="hidden items-center gap-8 md:flex"
                    aria-label="Main navigation"
                >
                    {landingNavLinks.map((link) => (
                        <button
                            key={link.id}
                            type="button"
                            onClick={() => scrollToSection(link.id)}
                            className={`relative py-2 text-sm font-semibold transition-colors focus-visible:ring-2 focus-visible:ring-firesight-orange focus-visible:outline-none ${
                                activeSection === link.id
                                    ? 'text-white'
                                    : 'text-white/70 hover:text-white'
                            }`}
                        >
                            {link.label}
                            <span
                                className={`absolute inset-x-0 bottom-0 h-0.5 rounded-full bg-firesight-orange transition-transform ${
                                    activeSection === link.id
                                        ? 'scale-x-100'
                                        : 'scale-x-0'
                                }`}
                            />
                        </button>
                    ))}
                </nav>

                <Link
                    href={portalRoute}
                    className="hidden items-center gap-2 rounded-full bg-gradient-to-r from-firesight-orange-light to-firesight-orange px-5 py-2.5 text-sm font-bold text-white shadow-[0_10px_25px_-8px_rgba(255,106,19,0.75)] transition hover:brightness-110 focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-firesight-navy focus-visible:outline-none md:inline-flex"
                >
                    <ShieldCheck className="size-4" aria-hidden="true" />
                    {portalLabel}
                </Link>

                <button
                    ref={menuToggleRef}
                    type="button"
                    onClick={() => setIsMenuOpen((open) => !open)}
                    className="rounded-xl p-2.5 text-white transition hover:bg-white/10 focus-visible:ring-2 focus-visible:ring-firesight-orange focus-visible:outline-none md:hidden"
                    aria-label={
                        isMenuOpen
                            ? 'Close navigation menu'
                            : 'Open navigation menu'
                    }
                    aria-expanded={isMenuOpen}
                    aria-controls="mobile-menu"
                >
                    {isMenuOpen ? (
                        <X aria-hidden="true" />
                    ) : (
                        <Menu aria-hidden="true" />
                    )}
                </button>
            </div>

            {isMenuOpen && (
                <div
                    id="mobile-menu"
                    className="border-t border-white/10 bg-firesight-navy px-4 py-5 md:hidden"
                >
                    <nav
                        className="mx-auto flex max-w-7xl flex-col gap-2"
                        aria-label="Mobile navigation"
                    >
                        {landingNavLinks.map((link) => (
                            <button
                                key={link.id}
                                type="button"
                                onClick={() => closeMenuAndScroll(link.id)}
                                className={`rounded-xl px-4 py-3 text-left text-sm font-semibold transition focus-visible:ring-2 focus-visible:ring-firesight-orange focus-visible:outline-none ${
                                    activeSection === link.id
                                        ? 'bg-white/10 text-white'
                                        : 'text-white/75 hover:bg-white/5 hover:text-white'
                                }`}
                            >
                                {link.label}
                            </button>
                        ))}
                        <Link
                            href={portalRoute}
                            onClick={() => setIsMenuOpen(false)}
                            className="mt-2 inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-firesight-orange-light to-firesight-orange px-5 py-3 text-sm font-bold text-white focus-visible:ring-2 focus-visible:ring-white focus-visible:outline-none"
                        >
                            <ShieldCheck
                                className="size-4"
                                aria-hidden="true"
                            />
                            {portalLabel}
                        </Link>
                    </nav>
                </div>
            )}
        </header>
    );
}
