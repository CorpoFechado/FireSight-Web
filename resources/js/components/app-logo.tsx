import { usePage } from '@inertiajs/react';

export default function AppLogo() {
    const { name } = usePage().props;

    return (
        <>
            <img
                src="/images/firesight-logo.png"
                alt="FireSight Logo"
                className="h-8 w-auto object-contain flex-shrink-0"
            />
            <div className="ml-1 grid flex-1 text-left text-sm">
                <span className="mb-0.5 truncate leading-tight font-semibold">
                    {name ?? 'FireSight'}
                </span>
            </div>
        </>
    );
}
