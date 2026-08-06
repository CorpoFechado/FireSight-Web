import { router } from '@inertiajs/react';
import { useEffect } from 'react';
import { toast } from 'sonner';
import type { FlashToast } from '@/types/ui';

export function useFlashToast(): void {
    useEffect(() => {
        return router.on('success', (event) => {
            const props = event.detail.page.props as {
                flash?: { toast?: FlashToast };
            };
            const data = props.flash?.toast;

            if (!data) {
                return;
            }

            toast[data.type](
                data.message,
                data.duration ? { duration: data.duration } : undefined,
            );
        });
    }, []);
}
