import { useForm } from '@inertiajs/react';
import { ImagePlus, Sparkles, Trash2, X } from 'lucide-react';
import { useEffect, useRef, useState, type ChangeEvent, type FormEvent } from 'react';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { store, update } from '@/routes/fireEducation';
import type { FireEducationCategory } from '@/lib/fire-status';

export type FireEducationRow = {
    id: number;
    title: string;
    category: FireEducationCategory;
    summary: string;
    body: string;
    image_path: string | null;
    read_minutes: number;
    is_featured: boolean;
    created_at: string;
    created_date: string;
};

const CATEGORY_OPTIONS: { value: FireEducationCategory; label: string }[] = [
    { value: 'prevention', label: 'Fire Prevention' },
    { value: 'emergency_response', label: 'Emergency Response' },
    { value: 'awareness', label: 'Community Awareness' },
];

export function FireEducationFormModal({
    mode,
    content,
    open,
    onOpenChange,
}: {
    mode: 'create' | 'edit';
    content?: FireEducationRow;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const fileInputRef = useRef<HTMLInputElement>(null);
    const [imagePreview, setImagePreview] = useState<string | null>(null);

    const {
        data,
        setData,
        post,
        processing,
        errors,
        reset,
        clearErrors,
    } = useForm({
        _method: mode === 'edit' ? 'PATCH' : 'POST',
        title: content?.title ?? '',
        category: content?.category ?? ('prevention' as FireEducationCategory),
        read_minutes: content?.read_minutes ?? 3,
        is_featured: content?.is_featured ?? false,
        summary: content?.summary ?? '',
        body: content?.body ?? '',
        image: null as File | null,
        image_url: content?.image_path?.startsWith('http') ? content.image_path : '',
        remove_image: false,
    });

    useEffect(() => {
        if (open) {
            setData({
                _method: mode === 'edit' ? 'PATCH' : 'POST',
                title: content?.title ?? '',
                category: content?.category ?? 'prevention',
                read_minutes: content?.read_minutes ?? 3,
                is_featured: content?.is_featured ?? false,
                summary: content?.summary ?? '',
                body: content?.body ?? '',
                image: null,
                image_url: content?.image_path?.startsWith('http') ? content.image_path : '',
                remove_image: false,
            });

            if (content?.image_path) {
                setImagePreview(
                    content.image_path.startsWith('http')
                        ? content.image_path
                        : `/storage/${content.image_path}`,
                );
            } else {
                setImagePreview(null);
            }

            clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, content?.id]);

    const handleFileChange = (e: ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            setData((prev) => ({
                ...prev,
                image: file,
                remove_image: false,
            }));
            const url = URL.createObjectURL(file);
            setImagePreview(url);
        }
    };

    const handleRemoveImage = () => {
        setData((prev) => ({
            ...prev,
            image: null,
            image_url: '',
            remove_image: true,
        }));
        setImagePreview(null);
        if (fileInputRef.current) {
            fileInputRef.current.value = '';
        }
    };

    const close = () => {
        reset();
        setImagePreview(null);
        onOpenChange(false);
    };

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();

        if (mode === 'create') {
            post(store().url, {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: close,
            });
        } else if (content) {
            // Using POST with _method=PATCH for multipart/form-data upload in PHP
            post(update(content.id).url, {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: close,
            });
        }
    };

    const isValid =
        data.title.trim() !== '' &&
        data.summary.trim() !== '' &&
        data.body.trim() !== '' &&
        data.read_minutes > 0;

    return (
        <Dialog open={open} onOpenChange={close}>
            <DialogContent className="max-h-[90vh] overflow-y-auto bg-white text-brand-navy sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle className="text-xl font-bold text-brand-navy">
                        {mode === 'create'
                            ? 'Create Fire Education Content'
                            : 'Edit Fire Education Content'}
                    </DialogTitle>
                </DialogHeader>

                <form onSubmit={handleSubmit} className="space-y-4">
                    {/* Title */}
                    <div className="space-y-1.5">
                        <Label htmlFor="content-title">
                            Title <span className="text-brand-red">*</span>
                        </Label>
                        <input
                            id="content-title"
                            value={data.title}
                            onChange={(e) => setData('title', e.target.value)}
                            placeholder="e.g. Philippines Fire Season Safety Guide 2026"
                            className="h-10 w-full rounded-lg border px-3 text-sm font-medium outline-none transition-colors focus:border-brand-blue"
                            style={{ borderColor: 'rgba(43,45,66,0.18)' }}
                        />
                        {errors.title && (
                            <p className="text-xs text-brand-red">{errors.title}</p>
                        )}
                    </div>

                    {/* Category, Read Minutes, Featured */}
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div className="space-y-1.5">
                            <Label>
                                Category <span className="text-brand-red">*</span>
                            </Label>
                            <Select
                                value={data.category}
                                onValueChange={(v) =>
                                    setData('category', v as FireEducationCategory)
                                }
                            >
                                <SelectTrigger className="h-10 w-full">
                                    <SelectValue placeholder="Select category" />
                                </SelectTrigger>
                                <SelectContent className="bg-white text-brand-navy">
                                    {CATEGORY_OPTIONS.map((c) => (
                                        <SelectItem key={c.value} value={c.value}>
                                            {c.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.category && (
                                <p className="text-xs text-brand-red">
                                    {errors.category}
                                </p>
                            )}
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="content-read-time">
                                Read Time (Minutes) <span className="text-brand-red">*</span>
                            </Label>
                            <input
                                id="content-read-time"
                                type="number"
                                min={1}
                                max={120}
                                value={data.read_minutes}
                                onChange={(e) =>
                                    setData(
                                        'read_minutes',
                                        Math.max(1, parseInt(e.target.value) || 1),
                                    )
                                }
                                className="h-10 w-full rounded-lg border px-3 text-sm font-medium outline-none transition-colors focus:border-brand-blue"
                                style={{ borderColor: 'rgba(43,45,66,0.18)' }}
                            />
                            {errors.read_minutes && (
                                <p className="text-xs text-brand-red">
                                    {errors.read_minutes}
                                </p>
                            )}
                        </div>

                        <div className="flex flex-col justify-end">
                            <label className="flex h-10 cursor-pointer items-center gap-2.5 rounded-lg border px-3.5 text-sm font-semibold transition-colors hover:bg-brand-bg select-none"
                                style={{
                                    borderColor: data.is_featured
                                        ? 'rgba(244,162,97,0.8)'
                                        : 'rgba(43,45,66,0.18)',
                                    background: data.is_featured
                                        ? 'rgba(244,162,97,0.1)'
                                        : undefined,
                                }}
                            >
                                <input
                                    type="checkbox"
                                    checked={data.is_featured}
                                    onChange={(e) =>
                                        setData('is_featured', e.target.checked)
                                    }
                                    className="size-4 accent-amber-500"
                                />
                                <span className="flex items-center gap-1.5 text-brand-navy">
                                    <Sparkles size={14} className="text-amber-500" />
                                    Featured Article
                                </span>
                            </label>
                        </div>
                    </div>

                    {/* Summary */}
                    <div className="space-y-1.5">
                        <Label htmlFor="content-summary">
                            Summary / Teaser <span className="text-brand-red">*</span>
                        </Label>
                        <input
                            id="content-summary"
                            value={data.summary}
                            onChange={(e) => setData('summary', e.target.value)}
                            placeholder="Brief 1-2 sentence overview shown in app list preview cards"
                            maxLength={255}
                            className="h-10 w-full rounded-lg border px-3 text-sm outline-none transition-colors focus:border-brand-blue"
                            style={{ borderColor: 'rgba(43,45,66,0.18)' }}
                        />
                        <div className="flex justify-between text-[11px] text-brand-muted">
                            <span>{errors.summary && <span className="text-brand-red">{errors.summary}</span>}</span>
                            <span>{data.summary.length}/255</span>
                        </div>
                    </div>

                    {/* Body */}
                    <div className="space-y-1.5">
                        <Label htmlFor="content-body">
                            Full Article Content <span className="text-brand-red">*</span>
                        </Label>
                        <textarea
                            id="content-body"
                            value={data.body}
                            onChange={(e) => setData('body', e.target.value)}
                            placeholder="Write comprehensive safety instructions, steps, or procedures for residents…"
                            rows={8}
                            className="w-full resize-y rounded-lg border p-3 text-sm leading-relaxed outline-none transition-colors focus:border-brand-blue"
                            style={{ borderColor: 'rgba(43,45,66,0.18)' }}
                        />
                        {errors.body && (
                            <p className="text-xs text-brand-red">{errors.body}</p>
                        )}
                    </div>

                    {/* Cover Image Upload & Preview */}
                    <div className="space-y-2">
                        <Label>Cover Photo (Optional)</Label>
                        {imagePreview ? (
                            <div className="relative overflow-hidden rounded-xl border border-gray-200">
                                <img
                                    src={imagePreview}
                                    alt="Preview"
                                    className="h-44 w-full object-cover"
                                />
                                <button
                                    type="button"
                                    onClick={handleRemoveImage}
                                    className="absolute top-2 right-2 flex size-8 items-center justify-center rounded-full bg-white/90 text-brand-red shadow backdrop-blur transition-transform hover:scale-105"
                                    title="Remove photo"
                                >
                                    <Trash2 size={16} />
                                </button>
                            </div>
                        ) : (
                            <div className="flex flex-col gap-2 sm:flex-row">
                                <button
                                    type="button"
                                    onClick={() => fileInputRef.current?.click()}
                                    className="flex flex-1 items-center justify-center gap-2 rounded-lg border border-dashed border-gray-300 bg-gray-50/60 p-4 text-sm font-medium text-brand-muted transition-colors hover:border-brand-blue hover:text-brand-blue"
                                >
                                    <ImagePlus size={18} />
                                    <span>Upload Image File (PNG, JPG, WebP)</span>
                                </button>
                                <input
                                    ref={fileInputRef}
                                    type="file"
                                    accept="image/*"
                                    className="hidden"
                                    onChange={handleFileChange}
                                />
                            </div>
                        )}
                        {errors.image && (
                            <p className="text-xs text-brand-red">{errors.image}</p>
                        )}
                    </div>

                    {/* Modal Footer */}
                    <DialogFooter className="pt-2">
                        <button
                            type="button"
                            onClick={close}
                            className="rounded-lg px-4 py-2 text-sm font-semibold text-brand-muted hover:text-brand-navy"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            disabled={processing || !isValid}
                            className="rounded-lg bg-brand-navy px-5 py-2 text-sm font-semibold text-white shadow transition-all hover:bg-brand-navy/90 disabled:opacity-50"
                        >
                            {processing
                                ? mode === 'create'
                                    ? 'Publishing…'
                                    : 'Saving…'
                                : mode === 'create'
                                  ? 'Publish Article'
                                  : 'Save Changes'}
                        </button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
