import { Calendar, Clock, Edit3, Sparkles } from 'lucide-react';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { EducationCategoryBadge } from '@/components/portal/status-badge';
import type { FireEducationRow } from './fire-education-form-modal';

export function FireEducationViewModal({
    content,
    open,
    onOpenChange,
    onEdit,
}: {
    content: FireEducationRow | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
    onEdit?: (content: FireEducationRow) => void;
}) {
    if (!content) {
        return null;
    }

    const imageUrl = content.image_path
        ? content.image_path.startsWith('http')
            ? content.image_path
            : `/storage/${content.image_path}`
        : null;

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-h-[90vh] overflow-y-auto bg-white p-0 text-brand-navy sm:max-w-2xl">
                {imageUrl && (
                    <div className="relative h-48 w-full overflow-hidden bg-gray-100 sm:h-64">
                        <img
                            src={imageUrl}
                            alt={content.title}
                            className="h-full w-full object-cover"
                        />
                        <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent" />
                    </div>
                )}

                <div className="p-6">
                    <DialogHeader className="mb-4 text-left">
                        <div className="flex flex-wrap items-center gap-2">
                            <EducationCategoryBadge category={content.category} />

                            {content.is_featured && (
                                <span className="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700">
                                    <Sparkles size={12} className="text-amber-500" />
                                    Featured Article
                                </span>
                            )}

                            <span className="flex items-center gap-1 text-xs text-brand-muted">
                                <Clock size={12} />
                                {content.read_minutes} min read
                            </span>

                            <span className="flex items-center gap-1 text-xs text-brand-muted">
                                <Calendar size={12} />
                                {content.created_date}
                            </span>
                        </div>

                        <DialogTitle className="mt-2 text-2xl font-bold leading-tight text-brand-navy">
                            {content.title}
                        </DialogTitle>
                    </DialogHeader>

                    {/* Summary box */}
                    <div className="mb-6 rounded-xl border border-blue-100 bg-blue-50/50 p-4 text-sm font-medium leading-relaxed text-brand-blue">
                        <p>{content.summary}</p>
                    </div>

                    {/* Full Body */}
                    <div className="prose max-w-none text-sm leading-relaxed text-gray-700">
                        {content.body.split('\n\n').map((paragraph, idx) => (
                            <p key={idx} className="mb-3 whitespace-pre-line">
                                {paragraph}
                            </p>
                        ))}
                    </div>

                    <div className="mt-8 flex items-center justify-between border-t border-gray-100 pt-4">
                        <span className="text-xs text-brand-muted">
                            Article #{content.id} · Visible in Lian FireSight Mobile
                        </span>
                        <div className="flex gap-2">
                            {onEdit && (
                                <button
                                    type="button"
                                    onClick={() => {
                                        onOpenChange(false);
                                        onEdit(content);
                                    }}
                                    className="flex items-center gap-1.5 rounded-lg border border-gray-200 px-3.5 py-1.5 text-xs font-semibold text-brand-navy hover:bg-gray-50"
                                >
                                    <Edit3 size={13} />
                                    Edit Article
                                </button>
                            )}
                            <button
                                type="button"
                                onClick={() => onOpenChange(false)}
                                className="rounded-lg bg-brand-navy px-4 py-1.5 text-xs font-semibold text-white hover:bg-brand-navy/90"
                            >
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    );
}
