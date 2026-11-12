import { expect, test } from '@playwright/test';
import { query } from './database';
import { incidentDetails, verifyIncidentDetail } from './incident-detail';

test.use({ trace: 'off', screenshot: 'off' });
const email = process.env.FIRESIGHT_ADMIN_EMAIL;
const password = process.env.FIRESIGHT_ADMIN_PASSWORD;
let cookies: Awaited<
    ReturnType<import('@playwright/test').BrowserContext['cookies']>
> = [];

test.beforeAll(async ({ browser, baseURL }) => {
    test.skip(
        !email || !password,
        'Set FIRESIGHT_ADMIN_EMAIL and FIRESIGHT_ADMIN_PASSWORD.',
    );
    const context = await browser.newContext({ baseURL });
    const page = await context.newPage();
    await page.goto('/login');
    await page.getByLabel('Email address').fill(email!);
    await page.getByLabel('Password', { exact: true }).fill(password!);
    await Promise.all([
        page.waitForResponse(
            (response) =>
                response.url().endsWith('/login') &&
                response.request().method() === 'POST',
        ),
        page.getByRole('button', { name: 'Sign In', exact: true }).click(),
    ]);
    if (await page.getByLabel('Password', { exact: true }).isVisible())
        await page.getByLabel('Password', { exact: true }).fill('');
    await expect(page).toHaveURL(/\/dashboard/);
    cookies = await context.cookies();
    await context.close();
});
test.beforeEach(async ({ context }) => {
    await context.addCookies(cookies);
});
function sample() {
    const [record] = query<{
        id: number;
        name: string;
        barangayId: number;
        barangay: string;
        day: string;
    }>(
        `SELECT JSON_OBJECT('id',r.report_id,'name',r.reporter_name,'barangayId',r.barangay_id,'barangay',b.barangay_name,'day',DATE_FORMAT(r.created_at,'%Y-%m-%d')) FROM community_report r JOIN barangay b ON b.barangay_id=r.barangay_id WHERE r.status='pending' ORDER BY r.created_at DESC LIMIT 1`,
    );
    expect(record, 'An existing pending report is required').toBeDefined();
    return record;
}

test('IA-007 Repeat 100 web retrieval attempts (mobile outstanding)', async ({
    page,
}, testInfo) => {
    test.setTimeout(600_000);
    const context = page.context();
    let activePage = page;
    activePage.setDefaultTimeout(8000);
    activePage.setDefaultNavigationTimeout(15000);
    const record = sample();
    const [detail] = incidentDetails([record.id]);
    const results: Array<{
        attempt: number;
        scenario: string;
        status: string;
        elapsedMs: number;
        error?: string;
    }> = [];
    const scenarios = ['IA-001', 'IA-003', 'IA-005', 'IA-006'];
    for (let index = 0; index < 100; index++) {
        if (activePage.isClosed()) break;
        if (index > 0 && index % 10 === 0) {
            await activePage.close();
            activePage = await context.newPage();
            activePage.setDefaultTimeout(8000);
            activePage.setDefaultNavigationTimeout(15000);
        }
        const scenario = scenarios[index % scenarios.length];
        const started = performance.now();
        try {
            if (scenario === 'IA-001') {
                await activePage.goto('/analytics');
                await expect(
                    activePage.getByRole('heading', {
                        name: 'Monthly Incident Trend',
                    }),
                ).toBeVisible();
                await expect(
                    activePage.locator('.recharts-surface').first(),
                ).toBeVisible();
            } else if (scenario === 'IA-003') {
                const [geoResponse, mapResponse] = await Promise.all([
                    activePage.waitForResponse(
                        (response) =>
                            new URL(response.url()).pathname ===
                            '/geo/barangay_boundaries_lian.json',
                        { timeout: 10_000 },
                    ),
                    activePage.goto('/map'),
                ]);
                expect(mapResponse?.ok(), 'Map response should succeed').toBe(
                    true,
                );
                expect(
                    geoResponse.ok(),
                    'Barangay boundary response should succeed',
                ).toBe(true);
                await expect(
                    activePage.getByRole('button', {
                        name: 'Risk Map',
                        exact: true,
                    }),
                ).toBeVisible();
                if (
                    !(await activePage
                        .getByText('Barangay Risk', { exact: true })
                        .isVisible())
                )
                    await activePage
                        .getByRole('button', { name: 'Risk Map', exact: true })
                        .click();
                await expect(
                    activePage.getByText('Barangay Risk', { exact: true }),
                ).toBeVisible();
                await expect(
                    activePage.locator('.leaflet-container'),
                ).toBeVisible();
                await expect
                    .poll(async () =>
                        activePage
                            .locator(
                                '.leaflet-overlay-pane path[fill-opacity="0.22"]',
                            )
                            .count(),
                    )
                    .toBeGreaterThan(0);
            } else if (scenario === 'IA-005') {
                await activePage.goto(`/incidents/${record.id}`);
                await verifyIncidentDetail(activePage, detail);
            } else {
                await activePage.goto(
                    `/incidents?status=pending&barangay_id=${record.barangayId}&date_from=${record.day}&date_to=${record.day}`,
                );
                await expect(
                    activePage
                        .locator('tbody tr')
                        .filter({ hasText: record.name }),
                ).toBeVisible();
                await expect(
                    activePage
                        .locator('tbody tr')
                        .filter({ hasText: record.name })
                        .locator('td')
                        .nth(1),
                ).toHaveText(record.barangay);
                await expect(
                    activePage
                        .locator('tbody tr')
                        .filter({ hasText: record.name })
                        .locator('td')
                        .last(),
                ).toContainText(record.day);
            }
            results.push({
                attempt: index + 1,
                scenario,
                status: 'Pass',
                elapsedMs: Math.round(performance.now() - started),
            });
        } catch (error) {
            if (activePage.isClosed()) break;
            results.push({
                attempt: index + 1,
                scenario,
                status: 'Fail',
                elapsedMs: Math.round(performance.now() - started),
                error:
                    error instanceof Error
                        ? error.message
                              .replaceAll(record.name, '[redacted]')
                              .slice(0, 500)
                        : String(error),
            });
        }
        if ((index + 1) % 10 === 0)
            console.log(`IA-007: ${index + 1} attempts completed`);
    }
    await testInfo.attach('ia-007-web-attempts.json', {
        body: JSON.stringify(results, null, 2),
        contentType: 'application/json',
    });
    const failures = results.filter((item) => item.status === 'Fail');
    expect(results.length, 'All 100 attempts must actually execute').toBe(100);
    expect(
        failures,
        `${results.length - failures.length}/${results.length} web attempts passed; mobile attempts remain outstanding`,
    ).toEqual([]);
});

// Explicitly enabled for diagnostics, never part of the frozen production suite.
if (process.env.FIRESIGHT_RISK_DIAGNOSTIC === '1') {
    test('IA-003 diagnostic repeated risk-layer rendering', async ({
        page,
    }, testInfo) => {
        test.setTimeout(240_000);
        const [{ count }] = query<{ count: number }>(
            "SELECT JSON_OBJECT('count', COUNT(*)) FROM barangay",
        );
        expect(Number(count)).toBeGreaterThan(0);
        const attempts: Array<Record<string, unknown>> = [];
        for (let attempt = 1; attempt <= 25; attempt++) {
            const started = performance.now();
            let geoStatus: number | null = null;
            let geoMs: number | null = null;
            let pageErrors = 0;
            let consoleErrors = 0;
            let failedRequests = 0;
            const onResponse = (
                response: import('@playwright/test').Response,
            ) => {
                if (
                    new URL(response.url()).pathname ===
                    '/geo/barangay_boundaries_lian.json'
                ) {
                    geoStatus = response.status();
                    const timing = response.request().timing();
                    if (
                        timing.requestStart >= 0 &&
                        timing.responseEnd >= timing.requestStart
                    )
                        geoMs = Math.round(
                            timing.responseEnd - timing.requestStart,
                        );
                }
            };
            const onPageError = () => {
                pageErrors++;
            };
            const onConsole = (
                message: import('@playwright/test').ConsoleMessage,
            ) => {
                if (message.type() === 'error') consoleErrors++;
            };
            const onRequestFailed = () => {
                failedRequests++;
            };
            page.on('response', onResponse);
            page.on('pageerror', onPageError);
            page.on('console', onConsole);
            page.on('requestfailed', onRequestFailed);
            let navigationMs: number | null = null;
            let polygonMs: number | null = null;
            let mapStatus: number | null = null;
            let failure: string | null = null;
            try {
                const response = await page.goto('/map');
                mapStatus = response?.status() ?? null;
                navigationMs = Math.round(performance.now() - started);
                expect(response?.ok()).toBe(true);
                await expect(
                    page.getByRole('button', { name: 'Risk Map', exact: true }),
                ).toBeVisible();
                if (
                    !(await page
                        .getByText('Barangay Risk', { exact: true })
                        .isVisible())
                )
                    await page
                        .getByRole('button', { name: 'Risk Map', exact: true })
                        .click();
                await expect(
                    page.getByText('Barangay Risk', { exact: true }),
                ).toBeVisible();
                await expect(page.locator('.leaflet-container')).toBeVisible();
                await expect(
                    page.locator(
                        '.leaflet-overlay-pane path[fill-opacity="0.22"]',
                    ),
                ).toHaveCount(Number(count), { timeout: 5000 });
                polygonMs = Math.round(performance.now() - started);
                expect(geoStatus).toBe(200);
            } catch (error) {
                failure =
                    error instanceof Error
                        ? error.message.split('\n')[0]
                        : 'Unknown failure';
            } finally {
                page.off('response', onResponse);
                page.off('pageerror', onPageError);
                page.off('console', onConsole);
                page.off('requestfailed', onRequestFailed);
            }
            attempts.push({
                attempt,
                status: failure ? 'Fail' : 'Pass',
                navigationMs,
                polygonMs,
                elapsedMs: Math.round(performance.now() - started),
                mapStatus,
                geoStatus,
                geoMs,
                pageErrors,
                consoleErrors,
                failedRequests,
                failure,
            });
        }
        await testInfo.attach('risk-layer-diagnostic.json', {
            body: JSON.stringify(attempts, null, 2),
            contentType: 'application/json',
        });
        expect(
            attempts.filter((attempt) => attempt.status === 'Fail'),
            'Risk diagnostic failures',
        ).toEqual([]);
    });
}
