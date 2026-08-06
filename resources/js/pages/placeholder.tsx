import { Construction } from 'lucide-react';
import PortalLayout from '@/layouts/portal-layout';

export default function Placeholder({ title }: { title: string }) {
    return (
        <PortalLayout title={title}>
            <div className="flex h-full min-h-[60vh] flex-col items-center justify-center gap-3 rounded-xl border border-dashed border-brand-navy/15 text-center">
                <Construction className="text-brand-muted" size={28} />
                <p className="font-semibold text-brand-navy">{title}</p>
                <p className="max-w-sm text-sm text-brand-muted">
                    This screen is next up — the layout and navigation are wired, the content is coming in a
                    following increment.
                </p>
            </div>
        </PortalLayout>
    );
}
