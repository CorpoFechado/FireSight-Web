import { useFlashToast } from '@/hooks/use-flash-toast';
import { Toaster as Sonner, type ToasterProps } from 'sonner';

function Toaster({ ...props }: ToasterProps) {
    useFlashToast();

    return (
        <Sonner
            theme="light"
            className="toaster group"
            position="bottom-right"
            style={
                {
                    '--normal-bg': '#fff',
                    '--normal-text': '#1D3557',
                    '--normal-border': 'rgba(43,45,66,0.1)',
                    '--success-bg': '#E0F5F3',
                    '--success-text': '#1B7A72',
                    '--success-border': 'rgba(27,122,114,0.25)',
                    '--error-bg': '#FDE8EA',
                    '--error-text': '#B91C2C',
                    '--error-border': 'rgba(185,28,44,0.25)',
                    '--warning-bg': '#FFFBEB',
                    '--warning-text': '#92400E',
                    '--warning-border': 'rgba(244,162,97,0.35)',
                } as React.CSSProperties
            }
            {...props}
        />
    );
}

export { Toaster };
