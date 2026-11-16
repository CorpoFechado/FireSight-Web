/**
 * Plays a short, subtle notification chime via the Web Audio API.
 * Gracefully no-ops if Web Audio is unavailable or blocked by browser autoplay policy.
 */
export function playSubtleAlertChime(): void {
    if (typeof window === 'undefined') {
        return;
    }

    const AudioCtx =
        window.AudioContext ||
        (window as unknown as { webkitAudioContext?: typeof AudioContext })
            .webkitAudioContext;

    if (!AudioCtx) {
        return;
    }

    try {
        const ctx = new AudioCtx();
        const now = ctx.currentTime;

        const playTone = (
            frequency: number,
            startOffset: number,
            duration: number,
        ) => {
            const oscillator = ctx.createOscillator();
            const gain = ctx.createGain();

            oscillator.type = 'sine';
            oscillator.frequency.setValueAtTime(frequency, now + startOffset);

            gain.gain.setValueAtTime(0.0001, now + startOffset);
            gain.gain.exponentialRampToValueAtTime(
                0.08,
                now + startOffset + 0.02,
            );
            gain.gain.exponentialRampToValueAtTime(
                0.0001,
                now + startOffset + duration,
            );

            oscillator.connect(gain);
            gain.connect(ctx.destination);

            oscillator.start(now + startOffset);
            oscillator.stop(now + startOffset + duration);
        };

        playTone(587.33, 0, 0.16);
        playTone(880, 0.12, 0.22);

        window.setTimeout(() => {
            void ctx.close().catch(() => {});
        }, 600);
    } catch {
        // Autoplay or audio context restricted; ignore gracefully.
    }
}
