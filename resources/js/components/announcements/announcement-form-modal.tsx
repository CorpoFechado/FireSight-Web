import { useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import type { FormEvent } from 'react';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { store, update } from '@/routes/announcements';

export type AnnouncementRow = {
    id: number;
    title: string;
    content: string;
    date: string;
    publishedBy: string;
};

export function AnnouncementFormModal({
    mode,
    announcement,
    open,
    onOpenChange,
}: {
    mode: 'create' | 'edit';
    announcement?: AnnouncementRow;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const [confirming, setConfirming] = useState(false);

    const {
        data,
        setData,
        post,
        patch,
        processing,
        errors,
        reset,
        clearErrors,
    } = useForm({
        title: announcement?.title ?? '',
        content: announcement?.content ?? '',
    });

    // Re-seed the form whenever the modal is (re)opened, so stale edits
    // from a previous open don't leak in.
    useEffect(() => {
        if (open) {
            setData({
                title: announcement?.title ?? '',
                content: announcement?.content ?? '',
            });
            // eslint-disable-next-line react-hooks/set-state-in-effect
            setConfirming(false);
            clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, announcement?.id]);

    const isValid = data.title.trim() !== '' && data.content.trim() !== '';

    const close = () => {
        reset();
        onOpenChange(false);
    };

    const submitCreate = () => {
        post(store().url, {
            preserveScroll: true,
            onSuccess: close,
            onError: () => setConfirming(false),
        });
    };

    const submitUpdate = (e: FormEvent) => {
        e.preventDefault();

        if (!announcement) {
            return;
        }

        patch(update(announcement.id).url, {
            preserveScroll: true,
            onSuccess: close,
        });
    };

    const handlePrimarySubmit = (e: FormEvent) => {
        e.preventDefault();

        if (mode === 'edit') {
            submitUpdate(e);

            return;
        }

        setConfirming(true);
    };

    return (
        <Dialog open={open} onOpenChange={close}>
            <DialogContent className="bg-white text-brand-navy sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle className="text-brand-navy">
                        {mode === 'create'
                            ? 'Create Announcement'
                            : 'Edit Announcement'}
                    </DialogTitle>
                </DialogHeader>

                <form onSubmit={handlePrimarySubmit} className="space-y-4">
                    <div className="space-y-1.5">
                        <Label>Title</Label>
                        <input
                            value={data.title}
                            onChange={(e) => setData('title', e.target.value)}
                            placeholder="Announcement title"
                            className="h-9 w-full rounded-md border px-3 text-sm outline-none"
                            style={{ borderColor: 'rgba(43,45,66,0.15)' }}
                        />
                        {errors.title && (
                            <p className="text-xs text-brand-red">
                                {errors.title}
                            </p>
                        )}
                    </div>

                    <div className="space-y-1.5">
                        <Label>Content</Label>
                        <textarea
                            value={data.content}
                            onChange={(e) => setData('content', e.target.value)}
                            placeholder="Write announcement content…"
                            rows={5}
                            className="w-full resize-none rounded-md border px-3 py-2 text-sm outline-none"
                            style={{ borderColor: 'rgba(43,45,66,0.15)' }}
                        />
                        {errors.content && (
                            <p className="text-xs text-brand-red">
                                {errors.content}
                            </p>
                        )}
                    </div>

                    {confirming && mode === 'create' ? (
                        <div
                            className="flex items-center justify-between rounded-lg px-4 py-3"
                            style={{
                                background: '#FFFBEB',
                                border: '1px solid rgba(244,162,97,0.35)',
                            }}
                        >
                            <p className="text-sm font-semibold text-brand-navy">
                                Publish this announcement now?
                            </p>
                            <div className="flex gap-2">
                                <button
                                    type="button"
                                    onClick={() => setConfirming(false)}
                                    className="rounded-lg px-3 py-1.5 text-xs font-semibold text-brand-muted"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="button"
                                    onClick={submitCreate}
                                    disabled={processing}
                                    className="rounded-lg bg-brand-navy px-3 py-1.5 text-xs font-semibold text-white disabled:opacity-50"
                                >
                                    {processing ? 'Publishing…' : 'Confirm'}
                                </button>
                            </div>
                        </div>
                    ) : (
                        <DialogFooter>
                            <button
                                type="button"
                                onClick={close}
                                className="rounded-lg px-4 py-2 text-sm font-semibold text-brand-muted"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                disabled={processing || !isValid}
                                className="rounded-lg bg-brand-navy px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
                            >
                                {processing
                                    ? mode === 'create'
                                        ? 'Publishing…'
                                        : 'Saving…'
                                    : mode === 'create'
                                      ? 'Publish Announcement'
                                      : 'Save Changes'}
                            </button>
                        </DialogFooter>
                    )}
                </form>
            </DialogContent>
        </Dialog>
    );
}
