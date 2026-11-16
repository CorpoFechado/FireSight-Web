import {
    AlertTriangle,
    BellOff,
    BellRing,
    CheckCircle2,
    Send,
} from 'lucide-react';
import { toast } from 'sonner';
import { PortalCard } from '@/components/portal/portal-card';
import { useWebPush } from '@/hooks/use-web-push';

export function PushNotificationsCard() {
    const {
        permission,
        isSupported,
        requiresHttps,
        isConfigured,
        isSubscribed,
        isLoading,
        isTesting,
        error,
        subscribe,
        unsubscribe,
        sendTestAlert,
    } = useWebPush();

    const handleEnable = async () => {
        const ok = await subscribe();

        if (ok) {
            toast.success(
                'Browser push notifications enabled for this device.',
            );
        }
    };

    const handleDisable = async () => {
        const ok = await unsubscribe();

        if (ok) {
            toast.info('Browser push notifications disabled on this device.');
        }
    };

    const handleTest = async () => {
        const result = await sendTestAlert();

        if (result.ok) {
            toast.success(result.message);
        } else {
            toast.error(result.message);
        }
    };

    return (
        <PortalCard className="p-4 sm:p-5">
            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div className="flex items-start gap-3">
                    <span
                        className="flex size-10 flex-shrink-0 items-center justify-center rounded-xl"
                        style={{
                            background: isSubscribed
                                ? 'rgba(42,157,143,0.12)'
                                : permission === 'denied'
                                  ? 'rgba(230,57,70,0.12)'
                                  : 'rgba(247,127,0,0.12)',
                        }}
                    >
                        {isSubscribed ? (
                            <CheckCircle2
                                size={18}
                                className="text-brand-green"
                            />
                        ) : permission === 'denied' ? (
                            <AlertTriangle
                                size={18}
                                className="text-brand-red"
                            />
                        ) : (
                            <BellRing size={18} className="text-brand-orange" />
                        )}
                    </span>

                    <div>
                        <div className="flex flex-wrap items-center gap-2">
                            <h2 className="text-sm font-bold text-brand-navy">
                                Browser Push Notifications
                            </h2>
                            <StatusPill
                                isSupported={isSupported}
                                requiresHttps={requiresHttps}
                                isConfigured={isConfigured}
                                permission={permission}
                                isSubscribed={isSubscribed}
                            />
                        </div>
                        <p className="mt-1 text-xs text-brand-muted">
                            {requiresHttps
                                ? 'Web Push requires HTTPS or localhost. Secure this site in Laravel Herd ("herd secure") or open FireSight via http://localhost.'
                                : !isSupported
                                  ? 'This browser does not support Web Push notifications.'
                                  : permission === 'denied'
                                    ? 'Notifications are blocked in your browser settings. Allow notifications for this site and reload to enable alerts.'
                                    : !isConfigured
                                      ? 'VAPID keys are not configured on the server yet.'
                                      : isSubscribed
                                        ? 'This browser will receive OS alerts for incoming fire reports even when the FireSight tab is closed.'
                                        : 'Enable push alerts on this browser to get notified immediately whenever a new fire report arrives — even if this tab is closed.'}
                        </p>
                        {error && (
                            <p className="mt-1.5 text-xs font-medium text-brand-red">
                                {error}
                            </p>
                        )}
                    </div>
                </div>

                {isSupported && (
                    <div className="flex flex-wrap items-center gap-2 sm:flex-shrink-0">
                        {isSubscribed ? (
                            <>
                                <button
                                    type="button"
                                    onClick={handleTest}
                                    disabled={isTesting || isLoading}
                                    className="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border px-3 py-2 text-xs font-semibold text-brand-navy transition-colors hover:bg-black/5 disabled:cursor-not-allowed disabled:opacity-50"
                                    style={{
                                        borderColor: 'rgba(43,45,66,0.15)',
                                    }}
                                >
                                    <Send size={13} />
                                    {isTesting
                                        ? 'Sending...'
                                        : 'Send test alert'}
                                </button>
                                <button
                                    type="button"
                                    onClick={handleDisable}
                                    disabled={isLoading || isTesting}
                                    className="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border px-3 py-2 text-xs font-semibold text-brand-muted transition-colors hover:bg-black/5 disabled:cursor-not-allowed disabled:opacity-50"
                                    style={{
                                        borderColor: 'rgba(43,45,66,0.15)',
                                    }}
                                >
                                    <BellOff size={13} />
                                    Disable
                                </button>
                            </>
                        ) : (
                            <button
                                type="button"
                                onClick={handleEnable}
                                disabled={
                                    isLoading ||
                                    !isConfigured ||
                                    permission === 'denied'
                                }
                                className="inline-flex cursor-pointer items-center gap-1.5 rounded-lg bg-brand-navy px-3.5 py-2 text-xs font-semibold text-white transition-colors hover:bg-brand-navy-hover disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <BellRing size={13} />
                                {isLoading
                                    ? 'Checking...'
                                    : 'Enable push alerts'}
                            </button>
                        )}
                    </div>
                )}
            </div>
        </PortalCard>
    );
}

function StatusPill({
    isSupported,
    requiresHttps,
    isConfigured,
    permission,
    isSubscribed,
}: {
    isSupported: boolean;
    requiresHttps: boolean;
    isConfigured: boolean;
    permission: string;
    isSubscribed: boolean;
}) {
    if (requiresHttps) {
        return (
            <span className="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700">
                HTTPS Required
            </span>
        );
    }

    if (!isSupported) {
        return (
            <span className="rounded-full bg-black/5 px-2 py-0.5 text-[11px] font-semibold text-brand-muted">
                Unsupported
            </span>
        );
    }

    if (permission === 'denied') {
        return (
            <span className="rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-semibold text-brand-red">
                Blocked
            </span>
        );
    }

    if (!isConfigured) {
        return (
            <span className="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700">
                Not configured
            </span>
        );
    }

    if (isSubscribed) {
        return (
            <span className="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-brand-green">
                Active on this device
            </span>
        );
    }

    return (
        <span className="rounded-full bg-orange-50 px-2 py-0.5 text-[11px] font-semibold text-brand-orange">
            Off on this device
        </span>
    );
}
