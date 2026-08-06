/**
 * Purely decorative background layer: a handful of large, blurred gradient
 * "aurora" circles sat behind page content. Never intercepts clicks, never
 * affects layout, never changes brand colors — just adds depth.
 *
 * `variant="light"` → for white/brand-bg portal surfaces (navy/indigo/blue
 * blobs with a hint of orange).
 * `variant="dark"` → for the navy auth screens (brighter blobs against the
 * dark navy so they read as a glow rather than a smudge).
 */
export function AuroraBackground({
    variant = 'light',
    className = '',
}: {
    variant?: 'light' | 'dark';
    className?: string;
}) {
    const isDark = variant === 'dark';

    return (
        <div
            aria-hidden
            className={`pointer-events-none absolute inset-0 -z-10 overflow-hidden ${className}`}
        >
            <div
                className="aurora-blob"
                style={{
                    top: '-12%',
                    left: '-10%',
                    width: 420,
                    height: 420,
                    background: isDark
                        ? 'radial-gradient(circle, rgba(69,123,157,0.55) 0%, rgba(69,123,157,0) 70%)'
                        : 'radial-gradient(circle, rgba(29,53,87,0.16) 0%, rgba(29,53,87,0) 70%)',
                }}
            />
            <div
                className="aurora-blob"
                style={{
                    top: '8%',
                    right: '-14%',
                    width: 380,
                    height: 380,
                    background: isDark
                        ? 'radial-gradient(circle, rgba(87,79,196,0.4) 0%, rgba(87,79,196,0) 70%)'
                        : 'radial-gradient(circle, rgba(69,123,157,0.14) 0%, rgba(69,123,157,0) 70%)',
                }}
            />
            <div
                className="aurora-blob"
                style={{
                    bottom: '-16%',
                    left: '18%',
                    width: 460,
                    height: 460,
                    background: isDark
                        ? 'radial-gradient(circle, rgba(29,53,87,0.6) 0%, rgba(29,53,87,0) 70%)'
                        : 'radial-gradient(circle, rgba(87,79,196,0.10) 0%, rgba(87,79,196,0) 70%)',
                }}
            />
            <div
                className="aurora-blob"
                style={{
                    bottom: '-10%',
                    right: '-8%',
                    width: 340,
                    height: 340,
                    background: isDark
                        ? 'radial-gradient(circle, rgba(247,127,0,0.28) 0%, rgba(247,127,0,0) 70%)'
                        : 'radial-gradient(circle, rgba(247,127,0,0.08) 0%, rgba(247,127,0,0) 70%)',
                }}
            />
        </div>
    );
}
