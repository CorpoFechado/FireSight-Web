import { usePage } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';
import {
    destroy as destroyPushSubscription,
    store as storePushSubscription,
    test as testPushSubscription,
} from '@/routes/notifications/pushSubscriptions';

export type WebPushPermission = 'unsupported' | 'prompt' | 'granted' | 'denied';

export interface UseWebPushReturn {
    permission: WebPushPermission;
    isSupported: boolean;
    requiresHttps: boolean;
    isConfigured: boolean;
    isSubscribed: boolean;
    isLoading: boolean;
    isTesting: boolean;
    error: string | null;
    subscribe: () => Promise<boolean>;
    unsubscribe: () => Promise<boolean>;
    sendTestAlert: () => Promise<{ ok: boolean; message: string }>;
}

const SERVICE_WORKER_URL = '/sw.js';

function isSecureOrigin(): boolean {
    return typeof window === 'undefined' ? true : window.isSecureContext;
}

function isPushApiSupported(): boolean {
    return (
        typeof window !== 'undefined' &&
        'serviceWorker' in navigator &&
        'PushManager' in window &&
        'Notification' in window
    );
}

function mapBrowserPermission(): WebPushPermission {
    if (!isPushApiSupported()) {
        return 'unsupported';
    }

    if (Notification.permission === 'default') {
        return 'prompt';
    }

    return Notification.permission;
}

function urlBase64ToUint8Array(base64String: string): Uint8Array<ArrayBuffer> {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding)
        .replace(/-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(new ArrayBuffer(rawData.length));

    for (let i = 0; i < rawData.length; i += 1) {
        outputArray[i] = rawData.charCodeAt(i);
    }

    return outputArray;
}

function getXsrfToken(): string | null {
    if (typeof document === 'undefined') {
        return null;
    }

    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : null;
}

async function jsonRequest(
    url: string,
    method: 'POST' | 'DELETE',
    body?: Record<string, unknown>,
): Promise<{ ok: boolean; status: number; data: Record<string, unknown> }> {
    const headers: Record<string, string> = {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    };

    const xsrfToken = getXsrfToken();

    if (xsrfToken) {
        headers['X-XSRF-TOKEN'] = xsrfToken;
    }

    const response = await fetch(url, {
        method,
        credentials: 'same-origin',
        headers,
        body: body ? JSON.stringify(body) : undefined,
    });

    let data: Record<string, unknown> = {};

    try {
        data = (await response.json()) as Record<string, unknown>;
    } catch {
        data = {};
    }

    return { ok: response.ok, status: response.status, data };
}

export function registerServiceWorker(): Promise<ServiceWorkerRegistration | null> {
    if (!isPushApiSupported()) {
        return Promise.resolve(null);
    }

    return navigator.serviceWorker
        .register(SERVICE_WORKER_URL)
        .then(() => navigator.serviceWorker.ready);
}

export function useWebPush(): UseWebPushReturn {
    const { vapidPublicKey } = usePage().props;
    const requiresHttps = !isSecureOrigin();
    const isSupported = isPushApiSupported();
    const isConfigured =
        typeof vapidPublicKey === 'string' && vapidPublicKey.trim().length > 0;

    const [permission, setPermission] = useState<WebPushPermission>(() =>
        mapBrowserPermission(),
    );
    const [isSubscribed, setIsSubscribed] = useState(false);
    const [isLoading, setIsLoading] = useState(isSupported);
    const [isTesting, setIsTesting] = useState(false);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (!isSupported) {
            return;
        }

        let cancelled = false;

        registerServiceWorker()
            .then((registration) => {
                if (!registration) {
                    return null;
                }

                return registration.pushManager.getSubscription();
            })
            .then((subscription) => {
                if (cancelled) {
                    return;
                }

                setPermission(mapBrowserPermission());
                setIsSubscribed(subscription !== null);
            })
            .catch(() => {
                if (!cancelled) {
                    setError('Unable to check browser push status.');
                }
            })
            .finally(() => {
                if (!cancelled) {
                    setIsLoading(false);
                }
            });

        return () => {
            cancelled = true;
        };
    }, [isSupported]);

    const subscribe = useCallback(async (): Promise<boolean> => {
        setError(null);

        if (!isSupported) {
            setError('This browser does not support Web Push notifications.');

            return false;
        }

        if (!isConfigured || !vapidPublicKey) {
            setError(
                'Push notifications are not configured on the server yet (missing VAPID keys).',
            );

            return false;
        }

        setIsLoading(true);

        try {
            const result = await Notification.requestPermission();
            const mapped =
                result === 'default' ? 'prompt' : (result as WebPushPermission);
            setPermission(mapped);

            if (result !== 'granted') {
                setError(
                    result === 'denied'
                        ? 'Notifications are blocked in your browser settings. Please allow notifications for this site.'
                        : 'Notification permission was dismissed.',
                );

                return false;
            }

            const registration = await registerServiceWorker();

            if (!registration) {
                setError('Service worker registration failed.');

                return false;
            }

            // Always unsubscribe any existing browser subscription first.
            // This guarantees the browser regenerates a fresh endpoint
            // matching the server's current VAPID public key.
            const existingSubscription =
                await registration.pushManager.getSubscription();

            if (existingSubscription) {
                try {
                    await existingSubscription.unsubscribe();
                } catch {
                    // Ignore unsubscription failure
                }
            }

            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(vapidPublicKey),
            });

            const json = subscription.toJSON();
            const p256dh = json.keys?.p256dh;
            const auth = json.keys?.auth;

            if (!json.endpoint || !p256dh || !auth) {
                setError('Browser returned an incomplete push subscription.');

                return false;
            }

            const encodings = (
                PushManager as unknown as {
                    supportedContentEncodings?: string[];
                }
            ).supportedContentEncodings;
            const contentEncoding =
                encodings && encodings.length > 0 ? encodings[0] : 'aes128gcm';

            const response = await jsonRequest(
                storePushSubscription().url,
                'POST',
                {
                    endpoint: json.endpoint,
                    keys: {
                        p256dh,
                        auth,
                    },
                    content_encoding: contentEncoding,
                },
            );

            if (!response.ok) {
                setError(
                    typeof response.data.message === 'string'
                        ? response.data.message
                        : 'Failed to save push subscription on the server.',
                );

                return false;
            }

            setIsSubscribed(true);

            return true;
        } catch (err) {
            const rawMessage =
                err instanceof Error
                    ? err.message
                    : 'Unable to enable browser push notifications.';

            if (rawMessage.toLowerCase().includes('push service error')) {
                setError(
                    `${rawMessage} If you are using Brave, enable "Use Google services for push messaging" in brave://settings/privacy.`,
                );
            } else {
                setError(rawMessage);
            }

            return false;
        } finally {
            setIsLoading(false);
        }
    }, [isConfigured, isSupported, vapidPublicKey]);

    const unsubscribe = useCallback(async (): Promise<boolean> => {
        setError(null);

        if (!isSupported) {
            return false;
        }

        setIsLoading(true);

        try {
            const registration = await registerServiceWorker();
            const subscription = registration
                ? await registration.pushManager.getSubscription()
                : null;

            if (subscription) {
                const endpoint = subscription.endpoint;
                await subscription.unsubscribe();
                await jsonRequest(destroyPushSubscription().url, 'DELETE', {
                    endpoint,
                });
            }

            setIsSubscribed(false);

            return true;
        } catch (err) {
            setError(
                err instanceof Error
                    ? err.message
                    : 'Unable to disable browser push notifications.',
            );

            return false;
        } finally {
            setIsLoading(false);
        }
    }, [isSupported]);

    const sendTestAlert = useCallback(async (): Promise<{
        ok: boolean;
        message: string;
    }> => {
        setError(null);
        setIsTesting(true);

        try {
            const response = await jsonRequest(
                testPushSubscription().url,
                'POST',
            );
            const message =
                typeof response.data.message === 'string'
                    ? response.data.message
                    : response.ok
                      ? 'Test push alert dispatched.'
                      : 'Failed to send test push alert.';

            if (!response.ok) {
                setError(message);
                if (response.status === 422) {
                    setIsSubscribed(false);
                }
            }

            return { ok: response.ok, message };
        } catch {
            const message = 'Unable to send test push alert right now.';
            setError(message);

            return { ok: false, message };
        } finally {
            setIsTesting(false);
        }
    }, []);

    return {
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
    };
}

/**
 * Automatically ensures the browser is subscribed and synced with the backend
 * whenever permission is already granted. Completely silent and hands-free.
 */
export async function autoEnrollPushNotifications(
    vapidPublicKey?: string | null,
): Promise<boolean> {
    if (
        typeof window === 'undefined' ||
        !isPushApiSupported() ||
        !vapidPublicKey ||
        vapidPublicKey.trim().length === 0 ||
        Notification.permission !== 'granted'
    ) {
        return false;
    }

    try {
        const registration = await registerServiceWorker();

        if (!registration) {
            return false;
        }

        let subscription = await registration.pushManager.getSubscription();

        if (!subscription) {
            subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(vapidPublicKey),
            });
        }

        const json = subscription.toJSON();
        const p256dh = json.keys?.p256dh;
        const auth = json.keys?.auth;

        if (!json.endpoint || !p256dh || !auth) {
            return false;
        }

        const encodings = (
            PushManager as unknown as { supportedContentEncodings?: string[] }
        ).supportedContentEncodings;
        const contentEncoding =
            encodings && encodings.length > 0 ? encodings[0] : 'aes128gcm';

        const response = await jsonRequest(
            storePushSubscription().url,
            'POST',
            {
                endpoint: json.endpoint,
                keys: { p256dh, auth },
                content_encoding: contentEncoding,
            },
        );

        return response.ok;
    } catch {
        return false;
    }
}
