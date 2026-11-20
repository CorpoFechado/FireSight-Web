/* eslint-disable no-restricted-globals */

self.addEventListener('install', () => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('push', (event) => {
    let payload = {};

    if (event.data) {
        try {
            payload = event.data.json();
        } catch {
            payload = {
                title: 'FireSight Alert',
                body: event.data.text(),
            };
        }
    }

    const title = payload.title || 'New Fire Report';
    const targetUrl =
        payload.url ||
        (payload.report_id ? `/incidents/${payload.report_id}` : '/notifications');

    const notificationOptions = {
        body: payload.body || 'A new fire report is awaiting verification.',
        icon: payload.icon || '/favicon.png?v=3',
        badge: payload.badge || '/favicon.png?v=3',
        tag: payload.tag || (payload.report_id ? `firesight-report-${payload.report_id}` : 'firesight-alert'),
        renotify: true,
        requireInteraction: payload.requireInteraction ?? false,
        vibrate: [200, 100, 200],
        data: {
            url: targetUrl,
            report_id: payload.report_id ?? null,
            type: payload.type ?? 'new_report',
        },
    };

    event.waitUntil(
        (async () => {
            await self.registration.showNotification(title, notificationOptions);

            const windowClients = await self.clients.matchAll({
                type: 'window',
                includeUncontrolled: true,
            });

            for (const client of windowClients) {
                client.postMessage({
                    type: 'FIRESIGHT_PUSH_NOTIFICATION',
                    payload: {
                        title,
                        body: notificationOptions.body,
                        url: targetUrl,
                        report_id: payload.report_id ?? null,
                        notification_type: payload.type ?? 'new_report',
                    },
                });
            }
        })(),
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const data = event.notification.data || {};
    const relativeUrl =
        data.url ||
        (data.report_id ? `/incidents/${data.report_id}` : '/notifications');
    const targetUrl = new URL(relativeUrl, self.location.origin).href;

    event.waitUntil(
        (async () => {
            const windowClients = await self.clients.matchAll({
                type: 'window',
                includeUncontrolled: true,
            });

            for (const client of windowClients) {
                if (client.url.startsWith(self.location.origin) && 'focus' in client) {
                    await client.focus();
                    if ('navigate' in client) {
                        await client.navigate(targetUrl);
                    }
                    return;
                }
            }

            if (self.clients.openWindow) {
                await self.clients.openWindow(targetUrl);
            }
        })(),
    );
});
