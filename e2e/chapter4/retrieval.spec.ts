import { expect, test, type Page } from '@playwright/test';
import { query } from './database';
import {
    incidentDetails,
    verifyIncidentDetail,
    verifyExtendedIncidentDetail,
} from './incident-detail';

// Detail pages can contain personal information and authenticated sessions.
test.use({ trace: 'off', screenshot: 'off' });

const email = process.env.FIRESIGHT_ADMIN_EMAIL;
const password = process.env.FIRESIGHT_ADMIN_PASSWORD;
let cookies: Awaited<
    ReturnType<import('@playwright/test').BrowserContext['cookies']>
> = [];

async function signIn(page: Page) {
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
}

type Incident = {
    id: number;
    name: string;
    contact: string;
    description: string | null;
    barangayId: number;
    barangay: string;
    day: string;
};
function sample(): Incident {
    const rows = query<Incident>(
        `SELECT JSON_OBJECT('id', r.report_id, 'name', r.reporter_name, 'contact', r.contact_number, 'description', r.description, 'barangayId', r.barangay_id, 'barangay', b.barangay_name, 'day', DATE_FORMAT(r.created_at, '%Y-%m-%d')) FROM community_report r JOIN barangay b ON b.barangay_id = r.barangay_id WHERE r.status='pending' ORDER BY r.created_at DESC LIMIT 1`,
    );
    expect(
        rows.length,
        'An existing report with a barangay is required',
    ).toBeGreaterThan(0);
    return rows[0];
}

test.beforeAll(async ({ browser, baseURL }) => {
    test.skip(
        !email || !password,
        'Set FIRESIGHT_ADMIN_EMAIL and FIRESIGHT_ADMIN_PASSWORD.',
    );
    const context = await browser.newContext({ baseURL });
    const page = await context.newPage();
    await signIn(page);
    cookies = await context.cookies();
    await context.close();
});

test.beforeEach(async ({ context }) => {
    await context.addCookies(cookies);
});

test('RET-002 Retrieve a specific incident and compare its contents with MySQL', async ({
    page,
}, testInfo) => {
    const record = sample();
    await page.goto('/incidents');
    await page
        .locator('tbody tr')
        .filter({ hasText: record.name })
        .first()
        .click();
    await expect(page).toHaveURL(
        new RegExp(`/incidents/${record.id}(?:\\?|$)`),
    );
    await expect(page.getByText(record.name).first()).toBeVisible();
    await expect(page.getByText(record.contact).first()).toBeVisible();
    await expect(
        page.getByText(record.barangay, { exact: true }).first(),
    ).toBeVisible();
    if (record.description)
        await expect(
            page.getByText(record.description, { exact: true }).first(),
        ).toBeVisible();
    const [detail] = incidentDetails([record.id]);
    await verifyIncidentDetail(page, detail);
    const unavailable = await verifyExtendedIncidentDetail(page, detail.id);
    if (unavailable.length && testInfo.errors.length === 0)
        test.skip(
            true,
            `Blocked: local images unavailable for reports ${unavailable.join(', ')}`,
        );
});

test('RET-011 Compare twenty incident detail pages with MySQL (web sample)', async ({
    page,
}, testInfo) => {
    test.setTimeout(180_000);
    const ids = query<{ id: number }>(
        `SELECT JSON_OBJECT('id',r.report_id) FROM community_report r JOIN incident_record i ON i.report_id=r.report_id WHERE r.status='resolved' ORDER BY r.created_at DESC LIMIT 20`,
    ).map((row) => row.id);
    expect(ids).toHaveLength(20);
    await testInfo.attach('incident-sample-ids.json', {
        body: JSON.stringify(ids),
        contentType: 'application/json',
    });
    const details = incidentDetails(ids);
    expect(details).toHaveLength(20);
    const unavailable: number[] = [];
    for (const detail of details) {
        await page.goto(`/incidents/${detail.id}`);
        await verifyIncidentDetail(page, detail);
        unavailable.push(
            ...(await verifyExtendedIncidentDetail(page, detail.id)),
        );
    }
    if (unavailable.length && testInfo.errors.length === 0)
        test.skip(
            true,
            `Blocked: local images unavailable for reports ${unavailable.join(', ')}`,
        );
});

test('RET-003 Filter five populated barangays and compare all displayed records with SQL', async ({
    page,
}, testInfo) => {
    const barangays = query<{ id: number; name: string; count: number }>(
        `SELECT JSON_OBJECT('id', b.barangay_id, 'name', b.barangay_name, 'count', COUNT(DISTINCT r.report_id)) FROM barangay b JOIN community_report r ON r.barangay_id=b.barangay_id OR EXISTS (SELECT 1 FROM incident_record i WHERE i.report_id=r.report_id AND i.barangay_id=b.barangay_id) WHERE r.status='resolved' GROUP BY b.barangay_id,b.barangay_name HAVING COUNT(DISTINCT r.report_id) <= 15 ORDER BY COUNT(DISTINCT r.report_id),b.barangay_id LIMIT 5`,
    );
    expect(
        barangays.length,
        'Five populated barangays with at most 15 resolved reports are required',
    ).toBe(5);
    await testInfo.attach('barangay-samples.json', {
        body: JSON.stringify(barangays, null, 2),
        contentType: 'application/json',
    });
    await page.goto('/incidents');
    await page.getByRole('button', { name: 'Resolved', exact: true }).click();
    await expect(page).toHaveURL(/status=resolved/);
    for (const barangay of barangays) {
        const records = query<{
            name: string;
            contact: string;
            barangay: string;
            date: string;
        }>(
            `SELECT JSON_OBJECT('name', r.reporter_name, 'contact', r.contact_number, 'barangay', COALESCE(b.barangay_name,bi.barangay_name,'—'), 'date', DATE_FORMAT(r.created_at,'%Y-%m-%d %H:%i')) FROM community_report r LEFT JOIN incident_record i ON i.report_id=r.report_id LEFT JOIN barangay b ON b.barangay_id=r.barangay_id LEFT JOIN barangay bi ON bi.barangay_id=i.barangay_id WHERE r.status='resolved' AND (r.barangay_id=${Number(barangay.id)} OR i.barangay_id=${Number(barangay.id)}) ORDER BY r.created_at DESC`,
        );
        await page.locator('select').first().selectOption(String(barangay.id));
        await expect(
            page.getByText(new RegExp(`showing ${barangay.count}(?:\\D|$)`)),
        ).toBeVisible();
        const rows = page.locator('tbody tr');
        await expect(rows).toHaveCount(records.length);
        for (let i = 0; i < records.length; i++) {
            await expect(rows.nth(i).locator('td').nth(0)).toContainText(
                records[i].name,
            );
            await expect(rows.nth(i).locator('td').nth(0)).toContainText(
                records[i].contact,
            );
            await expect(rows.nth(i).locator('td').nth(1)).toHaveText(
                barangay.name,
            );
            await expect(rows.nth(i).locator('td').last()).toHaveText(
                records[i].date,
            );
        }
    }
});

test('RET-004 Filter incidents by date with inclusive boundary dates', async ({
    page,
}) => {
    const record = sample();
    const [{ count }] = query<{ count: number }>(
        `SELECT JSON_OBJECT('count', COUNT(*)) FROM community_report WHERE status='pending' AND DATE(created_at)='${record.day}'`,
    );
    await page.goto('/incidents');
    await page.locator('input[type=date]').first().fill(record.day);
    await expect(page).toHaveURL(/date_from=/);
    await page.locator('input[type=date]').nth(1).fill(record.day);
    await expect(
        page.getByText(new RegExp(`showing ${count}(?:\\D|$)`)),
    ).toBeVisible();
    for (const row of await page.locator('tbody tr').all())
        await expect(row.locator('td').last()).toContainText(record.day);
});

test('RET-005 Retrieve incident status history in database order', async ({
    page,
}) => {
    const reports = query<{ id: number }>(
        `SELECT JSON_OBJECT('id', report_id) FROM report_status_history GROUP BY report_id HAVING COUNT(*) >= 2 ORDER BY report_id LIMIT 1`,
    );
    expect(
        reports.length,
        'An incident with at least two stored history entries is required',
    ).toBeGreaterThan(0);
    const history = query<{ time: string }>(
        `SELECT JSON_OBJECT('time', DATE_FORMAT(created_at, '%Y-%m-%d %H:%i')) FROM report_status_history WHERE report_id=${Number(reports[0].id)} ORDER BY created_at`,
    );
    await page.goto(`/incidents/${reports[0].id}`);
    const section = page
        .getByText('Status History', { exact: true })
        .locator('..')
        .locator('..');
    await expect(section).toContainText(`${history.length} entries`);
    const actual = await section.locator('span.font-mono').allTextContents();
    expect(actual).toEqual(history.map((item) => item.time));
});

test('RET-012 Invalid reference ID is safely rejected by reference lookup', async ({
    page,
}) => {
    const record = sample();
    const [{ reference }] = query<{ reference: string }>(
        `SELECT JSON_OBJECT('reference', CONCAT('INC-',YEAR(created_at),'-',LPAD(report_id,4,'0'))) FROM community_report WHERE report_id=${Number(record.id)}`,
    );
    await page.goto('/incidents');
    const search = page.getByPlaceholder(/Search reporter, contact, barangay/);
    await search.fill('FS-000000');
    await expect(
        page.getByText('No reports match these filters.'),
    ).toBeVisible();
    await expect(page.locator('body')).not.toContainText('Server Error');
    await search.fill(reference);
    await expect
        .poll(
            async () =>
                page
                    .locator('tbody tr')
                    .filter({ hasText: record.name })
                    .count(),
            {
                message:
                    'A reference lookup must also retrieve an existing reference',
            },
        )
        .toBe(1);
});

test('IA-006 Filter incident information by barangay and date', async ({
    page,
}, testInfo) => {
    const record = sample();
    const records = query<{ name: string; contact: string; day: string }>(
        `SELECT JSON_OBJECT('name', r.reporter_name, 'contact', r.contact_number, 'day', DATE_FORMAT(r.created_at,'%Y-%m-%d')) FROM community_report r LEFT JOIN incident_record i ON i.report_id=r.report_id WHERE r.status='pending' AND (r.barangay_id=${Number(record.barangayId)} OR i.barangay_id=${Number(record.barangayId)}) AND DATE(r.created_at)='${record.day}'`,
    );
    expect(records.length).toBeGreaterThan(0);
    await testInfo.attach('filter-sample.json', {
        body: JSON.stringify({
            barangay: record.barangay,
            date: record.day,
            count: records.length,
        }),
        contentType: 'application/json',
    });
    await page.goto('/incidents');
    await page
        .locator('select')
        .first()
        .selectOption(String(record.barangayId));
    await expect(page).toHaveURL(
        new RegExp(`barangay_id=${record.barangayId}`),
    );
    await page.locator('input[type=date]').first().fill(record.day);
    await expect(page).toHaveURL(/date_from=/);
    await page.locator('input[type=date]').nth(1).fill(record.day);
    await expect(
        page.getByText(new RegExp(`showing ${records.length}(?:\\D|$)`)),
    ).toBeVisible();
    const rows = page.locator('tbody tr');
    await expect(rows).toHaveCount(records.length);
    for (let i = 0; i < records.length; i++) {
        await expect(rows.nth(i).locator('td').nth(0)).toContainText(
            records[i].name,
        );
        await expect(rows.nth(i).locator('td').nth(0)).toContainText(
            records[i].contact,
        );
        await expect(rows.nth(i).locator('td').nth(1)).toHaveText(
            record.barangay,
        );
        await expect(rows.nth(i).locator('td').last()).toContainText(
            record.day,
        );
    }
});

test('IA-005 Display selected incident details against stored data (web sample)', async ({
    page,
}, testInfo) => {
    const ids = query<{ id: number }>(
        `SELECT JSON_OBJECT('id', report_id) FROM community_report WHERE status='resolved' ORDER BY created_at DESC LIMIT 1`,
    );
    expect(ids).toHaveLength(1);
    await testInfo.attach('selected-incident.json', {
        body: JSON.stringify(ids[0]),
        contentType: 'application/json',
    });
    const [detail] = incidentDetails([ids[0].id]);
    await page.goto(`/incidents/${detail.id}`);
    await verifyIncidentDetail(page, detail);
    const unavailable = await verifyExtendedIncidentDetail(page, detail.id);
    if (unavailable.length && testInfo.errors.length === 0)
        test.skip(
            true,
            `Blocked: local images unavailable for reports ${unavailable.join(', ')}`,
        );
});

test('REL-001 Repeat fixed web subset three times (partial)', async ({
    page,
}, testInfo) => {
    test.setTimeout(120_000);
    const record = sample();
    const [detail] = incidentDetails([record.id]);
    const results: Array<{ run: number; test: string; result: string }> = [];
    for (let run = 1; run <= 3; run++) {
        for (const name of [
            'incident-detail',
            'barangay-filter',
            'risk-layer',
        ]) {
            try {
                if (name === 'incident-detail') {
                    await page.goto(`/incidents/${record.id}`);
                    await verifyIncidentDetail(page, detail);
                } else if (name === 'barangay-filter') {
                    await page.goto(
                        `/incidents?status=pending&barangay_id=${record.barangayId}&date_from=${record.day}&date_to=${record.day}`,
                    );
                    await expect(
                        page
                            .locator('tbody tr')
                            .filter({ hasText: record.name }),
                    ).toBeVisible();
                } else {
                    await page.goto('/map');
                    await page
                        .getByRole('button', { name: 'Risk Map', exact: true })
                        .click();
                    await expect(
                        page.getByText('Barangay Risk', { exact: true }),
                    ).toBeVisible();
                }
                results.push({ run, test: name, result: 'Pass' });
            } catch {
                results.push({ run, test: name, result: 'Fail' });
            }
        }
    }
    const csv = [
        'run_id,test_id,result',
        ...results.map((item) => `${item.run},${item.test},${item.result}`),
    ].join('\n');
    await testInfo.attach('RUNS_Playwright_subset.csv', {
        body: csv,
        contentType: 'text/csv',
    });
    expect(
        results.filter((item) => item.result !== 'Pass'),
        'All three runs must pass to count as identical passing results',
    ).toEqual([]);
});

test('IA-001 Analytics charts render on the web dashboard', async ({
    page,
}, testInfo) => {
    const totals = query<{ type: string; count: number; percentage: number }>(
        `SELECT JSON_OBJECT('type', incident_type, 'count', COUNT(*), 'percentage', ROUND(COUNT(*) * 100 / (SELECT COUNT(*) FROM incident_record WHERE incident_type IS NOT NULL AND incident_datetime >= '2026-01-01' AND incident_datetime < '2027-01-01'))) FROM incident_record WHERE incident_type IS NOT NULL AND incident_datetime >= '2026-01-01' AND incident_datetime < '2027-01-01' GROUP BY incident_type ORDER BY COUNT(*) DESC`,
    );
    expect(totals.length).toBeGreaterThan(0);
    await testInfo.attach('analytics-type-aggregates.json', {
        body: JSON.stringify(totals, null, 2),
        contentType: 'application/json',
    });
    await page.goto(
        '/analytics?period=custom&date_from=2026-01-01&date_to=2026-12-31&trend_year=2026',
    );
    for (const heading of [
        'Incidents by Type',
        'Monthly Incident Trend',
        'Incidents by Alarm Level',
        'Barangays with Most Incidents',
    ]) {
        await expect(
            page.getByRole('heading', { name: heading }),
        ).toBeVisible();
    }
    await expect(page.locator('.recharts-surface').first()).toBeVisible();
    const labels: Record<string, string> = {
        residential_fire: 'Residential Fire',
        commercial_fire: 'Commercial Fire',
        vehicular_fire: 'Vehicular Fire',
        storage_fire: 'Storage Fire',
        rubbish_fire: 'Rubbish Fire',
        others: 'Others',
    };
    const panel = page
        .getByRole('heading', { name: 'Incidents by Type' })
        .locator('../..');
    for (const item of totals) {
        await expect(
            panel.getByText(labels[item.type], { exact: true }).locator('..'),
        ).toContainText(`${item.percentage}%`);
    }
    const sectors = panel.locator('.recharts-pie-sector');
    await expect(sectors).toHaveCount(totals.length);
    for (let index = 0; index < totals.length; index++) {
        await sectors.nth(index).dispatchEvent('mouseover');
        const tooltip = panel.locator('.recharts-tooltip-wrapper');
        await expect(tooltip).toContainText(labels[totals[index].type]);
        await expect(tooltip).toContainText(String(totals[index].count));
    }
    const monthly = query<{ month: number; count: number }>(
        `SELECT JSON_OBJECT('month', MONTH(incident_datetime), 'count', COUNT(*)) FROM incident_record WHERE incident_datetime >= '2026-01-01' AND incident_datetime < '2027-01-01' GROUP BY MONTH(incident_datetime)`,
    );
    await testInfo.attach('analytics-monthly-aggregates.json', {
        body: JSON.stringify(monthly, null, 2),
        contentType: 'application/json',
    });
    const monthlyPanel = page
        .getByRole('heading', { name: 'Monthly Incident Trend' })
        .locator('../..');
    const dots = monthlyPanel.locator('circle.recharts-area-dot');
    await expect(dots).toHaveCount(
        new Date().getFullYear() === 2026 ? new Date().getMonth() + 1 : 12,
    );
    for (let month = 1; month <= (await dots.count()); month++) {
        await dots.nth(month - 1).hover();
        const expected = Number(
            monthly.find((item) => Number(item.month) === month)?.count ?? 0,
        );
        await expect(
            monthlyPanel.locator('.recharts-tooltip-wrapper'),
        ).toContainText(`${expected} incident`);
    }
    const barangayTotals = query<{ name: string; count: number }>(
        `SELECT JSON_OBJECT('name',b.barangay_name,'count',COUNT(i.incident_id)) FROM incident_record i JOIN barangay b ON b.barangay_id=i.barangay_id WHERE i.incident_datetime >= '2026-01-01' AND i.incident_datetime < '2027-01-01' GROUP BY b.barangay_id,b.barangay_name ORDER BY COUNT(i.incident_id) DESC,b.barangay_name LIMIT 5`,
    );
    await testInfo.attach('analytics-barangay-aggregates.json', {
        body: JSON.stringify(barangayTotals, null, 2),
        contentType: 'application/json',
    });
    const barangayPanel = page
        .getByRole('heading', { name: 'Barangays with Most Incidents' })
        .locator('../..');
    const bars = barangayPanel.locator('.recharts-bar-rectangle path');
    await expect(bars).toHaveCount(barangayTotals.length);
    for (let index = 0; index < barangayTotals.length; index++) {
        await bars.nth(index).hover();
        const tooltip = barangayPanel.locator('.recharts-tooltip-wrapper');
        await expect(tooltip).toContainText(barangayTotals[index].name);
        await expect(tooltip).toContainText(
            `${barangayTotals[index].count} incident`,
        );
    }
    const alarmTotals = query<{ level: string; count: number }>(
        `SELECT JSON_OBJECT('level',alarm_level,'count',COUNT(*)) FROM incident_record WHERE alarm_level IS NOT NULL AND incident_datetime >= '2026-01-01' AND incident_datetime < '2027-01-01' GROUP BY alarm_level`,
    );
    await testInfo.attach('analytics-alarm-aggregates.json', {
        body: JSON.stringify(alarmTotals, null, 2),
        contentType: 'application/json',
    });
    const levels = [
        'verification',
        '1st_alarm',
        '2nd_alarm',
        '3rd_alarm',
        '4th_alarm',
        '5th_alarm',
        'task_force_alpha',
        'task_force_bravo',
        'task_force_charlie',
        'task_force_delta',
        'task_force_echo',
        'task_force_hotel',
        'task_force_india',
        'general_alarm',
    ];
    const alarmPanel = page
        .getByRole('heading', { name: 'Incidents by Alarm Level' })
        .locator('../..');
    const alarmBars = alarmPanel.locator('.recharts-bar-rectangle path');
    const nonzeroAlarms = alarmTotals
        .slice()
        .sort((a, b) => levels.indexOf(a.level) - levels.indexOf(b.level));
    await expect(alarmBars).toHaveCount(nonzeroAlarms.length);
    for (let index = 0; index < nonzeroAlarms.length; index++) {
        const item = nonzeroAlarms[index];
        const bar = await alarmBars.nth(index).boundingBox();
        const chart = await alarmPanel
            .locator('.recharts-surface')
            .boundingBox();
        expect(bar).not.toBeNull();
        expect(chart).not.toBeNull();
        await page.mouse.move(
            bar!.x + bar!.width / 2,
            chart!.y + chart!.height / 2,
        );
        await expect(
            alarmPanel.locator('.recharts-tooltip-wrapper'),
        ).toContainText(String(item.count));
    }
});

test('IA-003 GIS-005 Risk polygon colors and classifications match MySQL (web)', async ({
    page,
}, testInfo) => {
    test.setTimeout(90_000);
    const expected = query<{ name: string; level: string | null }>(
        `SELECT JSON_OBJECT('name', b.barangay_name, 'level', (SELECT r.risk_level FROM risk_assessment r WHERE r.barangay_id=b.barangay_id ORDER BY r.date DESC LIMIT 1)) FROM barangay b ORDER BY b.barangay_name`,
    );
    expect(expected.length).toBeGreaterThan(0);
    await testInfo.attach('risk-assessments.json', {
        body: JSON.stringify(expected, null, 2),
        contentType: 'application/json',
    });
    await page.goto('/map');
    await page.getByRole('button', { name: 'Incidents', exact: true }).click();
    await page.getByRole('button', { name: 'Risk Map', exact: true }).click();
    await expect(
        page.getByText('Barangay Risk', { exact: true }),
    ).toBeVisible();
    for (const label of ['High', 'Moderate', 'Mild']) {
        await expect(
            page.getByText(label, { exact: true }).last(),
        ).toBeVisible();
    }
    const legend = page
        .getByText('Barangay Risk', { exact: true })
        .locator('..');
    for (const [label, color] of [
        ['High', 'rgb(220, 38, 38)'],
        ['Moderate', 'rgb(249, 115, 22)'],
        ['Mild', 'rgb(234, 179, 8)'],
    ]) {
        await expect(
            legend
                .getByText(label, { exact: true })
                .locator('..')
                .locator('span')
                .first(),
        ).toHaveCSS('background-color', color);
    }
    const polygons = page.locator(
        '.leaflet-overlay-pane path.leaflet-interactive',
    );
    await expect.poll(async () => polygons.count()).toBeGreaterThan(0);
    const observed = new Set<string>();
    const colors: Record<string, string> = {
        high: '#DC2626',
        moderate: '#F97316',
        mild: '#EAB308',
    };
    for (const polygon of await polygons.all()) {
        await polygon.dispatchEvent('click');
        const popup = page.locator('.leaflet-popup-content').last();
        await expect(popup).toBeVisible();
        const name = (await popup.locator('p').first().innerText()).trim();
        const record = expected.find((item) => item.name === name);
        expect(
            record,
            `Map polygon ${name} should have a barangay database record`,
        ).toBeDefined();
        if (!record) continue;
        observed.add(name);
        const label = record.level
            ? record.level[0].toUpperCase() + record.level.slice(1)
            : 'No risk data';
        await expect(popup).toContainText(`${label} risk`);
        await expect(polygon).toHaveAttribute(
            'fill',
            record.level ? colors[record.level] : '#6B7A8D',
        );
    }
    expect(
        [...observed].sort(),
        'Every stored barangay must have a classified polygon',
    ).toEqual(expected.map((item) => item.name).sort());
});

test('GIS-003 Filter map markers for five barangays and compare with MySQL', async ({
    page,
}, testInfo) => {
    test.setTimeout(120_000);
    const barangays = query<{ id: number; name: string; count: number }>(
        `SELECT JSON_OBJECT('id', b.barangay_id, 'name', b.barangay_name, 'count', COUNT(DISTINCT r.report_id)) FROM barangay b JOIN community_report r ON r.barangay_id=b.barangay_id OR EXISTS (SELECT 1 FROM incident_record i WHERE i.report_id=r.report_id AND i.barangay_id=b.barangay_id) WHERE r.status='resolved' AND r.created_at >= '2000-01-01' AND r.created_at < '2101-01-01' GROUP BY b.barangay_id,b.barangay_name HAVING COUNT(DISTINCT r.report_id) BETWEEN 1 AND 15 ORDER BY COUNT(DISTINCT r.report_id),b.barangay_id LIMIT 5`,
    );
    expect(barangays.length, 'Five populated barangays are required').toBe(5);
    await testInfo.attach('gis-barangays.json', {
        body: JSON.stringify(barangays, null, 2),
        contentType: 'application/json',
    });
    await page.goto(
        '/map?period=custom&date_from=2000-01-01&date_to=2100-12-31',
    );
    const filter = page.locator('#map-barangay-filter');
    await expect(filter).toBeVisible();
    for (const barangay of barangays) {
        const records = query<{
            reference: string;
            date: string;
            barangay: string;
        }>(
            `SELECT JSON_OBJECT('reference',CONCAT('INC-',YEAR(r.created_at),'-',LPAD(r.report_id,4,'0')),'date',DATE_FORMAT(r.created_at,'%Y-%m-%d %H:%i'),'barangay',COALESCE(bi.barangay_name,b.barangay_name)) FROM community_report r LEFT JOIN incident_record i ON i.report_id=r.report_id LEFT JOIN barangay b ON b.barangay_id=r.barangay_id LEFT JOIN barangay bi ON bi.barangay_id=i.barangay_id WHERE r.status='resolved' AND r.created_at >= '2000-01-01' AND r.created_at < '2101-01-01' AND (r.barangay_id=${Number(barangay.id)} OR i.barangay_id=${Number(barangay.id)}) ORDER BY r.created_at DESC`,
        );
        await filter.selectOption(String(barangay.id));
        await expect(page).toHaveURL(
            new RegExp(`barangay_id=${barangay.id}(?:&|$)`),
        );
        const sidebar = page
            .getByRole('heading', { name: 'Incidents', exact: true })
            .locator('../..');
        await expect(sidebar.locator('p.font-mono')).toHaveCount(
            Number(barangay.count),
        );
        await expect(
            page.locator('.leaflet-overlay-pane path[fill-opacity="0.9"]'),
        ).toHaveCount(Number(barangay.count));
        for (const record of records) {
            await expect(record.barangay).toBe(barangay.name);
            await sidebar.getByText(record.reference, { exact: true }).click();
            const popup = page
                .locator('.leaflet-popup-content')
                .filter({ hasText: record.reference });
            await expect(popup).toContainText(record.barangay);
            await expect(popup).toContainText(record.date);
        }
    }
});

test('ADM-004 Compare barangay risk ranking with stored assessments', async ({
    page,
}) => {
    const risk = query<{ name: string; level: string; score: number }>(
        `SELECT JSON_OBJECT('name', b.barangay_name, 'level', r.risk_level, 'score', ROUND(r.prediction_score * 100)) FROM barangay b JOIN risk_assessment r ON r.risk_id = (SELECT r2.risk_id FROM risk_assessment r2 WHERE r2.barangay_id=b.barangay_id ORDER BY r2.date DESC LIMIT 1)`,
    );
    expect(risk.length, 'Stored assessments are required').toBeGreaterThan(0);
    await page.goto('/fire-prone');
    const ranking = page
        .getByRole('heading', { name: 'Barangay Risk Ranking' })
        .locator('../..');
    const colors: Record<string, string> = {
        high: 'rgb(220, 38, 38)',
        moderate: 'rgb(249, 115, 22)',
        mild: 'rgb(234, 179, 8)',
    };
    for (const item of risk) {
        const row = ranking
            .getByText(item.name, { exact: true })
            .locator('../..');
        await expect(row).toContainText(String(item.score));
        await expect(row.locator('div[style*="background"]').last()).toHaveCSS(
            'background-color',
            colors[item.level],
        );
    }
});

test('GIS-006 Compare the densest barangay with the risk map (partial)', async ({
    page,
}, testInfo) => {
    const rows = query<{ name: string; count: number; level: string }>(
        `SELECT JSON_OBJECT('name', b.barangay_name, 'count', COUNT(i.incident_id), 'level', (SELECT r.risk_level FROM risk_assessment r WHERE r.barangay_id=b.barangay_id ORDER BY r.date DESC LIMIT 1)) FROM barangay b JOIN incident_record i ON i.barangay_id=b.barangay_id GROUP BY b.barangay_id, b.barangay_name ORDER BY COUNT(i.incident_id) DESC LIMIT 1`,
    );
    expect(rows.length).toBeGreaterThan(0);
    await page.goto('/fire-prone');
    await expect(
        page.getByRole('heading', { name: /Risk Heatmap/ }),
    ).toBeVisible();
    const ranking = page
        .getByRole('heading', { name: 'Barangay Risk Ranking' })
        .locator('../..');
    const distribution = query<{
        name: string;
        count: number;
        level: string | null;
    }>(
        `SELECT JSON_OBJECT('name', b.barangay_name, 'count', COUNT(i.incident_id), 'level', (SELECT r.risk_level FROM risk_assessment r WHERE r.barangay_id=b.barangay_id ORDER BY r.date DESC LIMIT 1)) FROM barangay b LEFT JOIN incident_record i ON i.barangay_id=b.barangay_id GROUP BY b.barangay_id,b.barangay_name ORDER BY COUNT(i.incident_id) DESC,b.barangay_name`,
    );
    await testInfo.attach('barangay-incident-distribution.json', {
        body: JSON.stringify(distribution, null, 2),
        contentType: 'application/json',
    });
    for (const item of distribution) {
        const itemRow = ranking
            .getByText(item.name, { exact: true })
            .locator('../..');
        await expect(itemRow).toContainText(
            `${item.count} incident${Number(item.count) === 1 ? '' : 's'}`,
        );
    }
    const row = ranking
        .getByText(rows[0].name, { exact: true })
        .locator('../..');
    await expect(row).toContainText(String(rows[0].count));
    await expect(row.locator('div[style*="background"]').last()).toHaveCSS(
        'background-color',
        'rgb(220, 38, 38)',
    );
    expect(
        rows[0].level,
        'The barangay with the most incidents should appear in the highest heatmap risk category',
    ).toBe('high');
    await page.goto('/map');
    await page.getByRole('button', { name: 'Incidents', exact: true }).click();
    await page.getByRole('button', { name: 'Risk Map', exact: true }).click();
    const polygons = page.locator(
        '.leaflet-overlay-pane path.leaflet-interactive',
    );
    await expect.poll(async () => polygons.count()).toBeGreaterThan(0);
    let found = false;
    for (const polygon of await polygons.all()) {
        await polygon.dispatchEvent('click');
        const popup = page
            .locator('.leaflet-popup-content')
            .filter({ hasText: rows[0].name });
        if (await popup.isVisible()) {
            await expect(popup).toContainText('High risk');
            await expect(polygon).toHaveAttribute('fill', '#DC2626');
            found = true;
            break;
        }
    }
    expect(
        found,
        'The densest barangay must be present on the risk layer',
    ).toBe(true);
});

test('GIS-004 Map date filter matches resolved incident count from MySQL', async ({
    page,
}, testInfo) => {
    test.setTimeout(180_000);
    const days = query<{ day: string; count: number }>(
        `SELECT JSON_OBJECT('day', DATE_FORMAT(created_at, '%Y-%m-%d'), 'count', COUNT(*)) FROM community_report WHERE status='resolved' GROUP BY DATE(created_at) ORDER BY MAX(created_at) DESC LIMIT 3`,
    );
    expect(
        days.length,
        'Three distinct dates with resolved incidents are required',
    ).toBe(3);
    await testInfo.attach('date-ranges.json', {
        body: JSON.stringify(days, null, 2),
        contentType: 'application/json',
    });
    await page.goto('/map');
    await page.getByRole('button', { name: 'Custom', exact: true }).click();
    for (const day of days) {
        // Clear the upper boundary before selecting another date.
        await page.locator('#map-date-to').fill('');
        await page.locator('#map-date-from').fill(day.day);
        await expect(page).toHaveURL(new RegExp(`date_from=${day.day}`));
        await page.locator('#map-date-to').fill(day.day);
        await expect(
            page.getByText(new RegExp(`^${day.count} incidents? ·`)),
        ).toBeVisible();
        await expect(
            page.locator('.leaflet-overlay-pane path.leaflet-interactive'),
        ).toHaveCount(day.count);
        const expected = query<{ reference: string; date: string }>(
            `SELECT JSON_OBJECT('reference', CONCAT('INC-',YEAR(created_at),'-',LPAD(report_id,4,'0')), 'date', DATE_FORMAT(created_at,'%Y-%m-%d %H:%i')) FROM community_report WHERE status='resolved' AND DATE(created_at)='${day.day}' ORDER BY created_at DESC`,
        );
        await testInfo.attach(`map-records-${day.day}.json`, {
            body: JSON.stringify(expected, null, 2),
            contentType: 'application/json',
        });
        const sidebar = page
            .getByRole('heading', { name: 'Incidents', exact: true })
            .locator('../..');
        for (const incident of expected) {
            await expect(
                sidebar.getByText(incident.reference, { exact: true }),
            ).toBeVisible();
            await sidebar
                .getByText(incident.reference, { exact: true })
                .click();
            await expect(
                page
                    .locator('.leaflet-popup-content')
                    .filter({ hasText: incident.reference }),
            ).toContainText(incident.date);
        }
    }
});

test('GIS-007 Compare ten incident marker popups with their stored records (web)', async ({
    page,
}, testInfo) => {
    test.setTimeout(90_000);
    const rows = query<{
        id: number;
        reference: string;
        barangay: string;
        day: string;
        date: string;
        type: string | null;
        alarm: string | null;
    }>(
        `SELECT JSON_OBJECT('id', r.report_id, 'reference', CONCAT('INC-', YEAR(r.created_at), '-', LPAD(r.report_id, 4, '0')), 'barangay', b.barangay_name, 'day', DATE_FORMAT(r.created_at, '%Y-%m-%d'), 'date', DATE_FORMAT(r.created_at, '%Y-%m-%d %H:%i'), 'type', i.incident_type, 'alarm', i.alarm_level) FROM community_report r JOIN incident_record i ON i.report_id=r.report_id JOIN barangay b ON b.barangay_id=i.barangay_id WHERE r.status='resolved' ORDER BY r.created_at DESC LIMIT 10`,
    );
    expect(rows.length, 'A resolved incident with a barangay is required').toBe(
        10,
    );
    await testInfo.attach('marker-samples.json', {
        body: JSON.stringify(rows, null, 2),
        contentType: 'application/json',
    });
    const types: Record<string, string> = {
        residential_fire: 'Residential Fire',
        commercial_fire: 'Commercial Fire',
        vehicular_fire: 'Vehicular Fire',
        storage_fire: 'Storage Fire',
        rubbish_fire: 'Rubbish Fire',
        others: 'Others',
    };
    const alarms: Record<string, string> = {
        verification: 'Verification',
        '1st_alarm': '1st Alarm',
        '2nd_alarm': '2nd Alarm',
        '3rd_alarm': '3rd Alarm',
        '4th_alarm': '4th Alarm',
        '5th_alarm': '5th Alarm',
        task_force_alpha: 'Task Force Alpha',
        task_force_bravo: 'Task Force Bravo',
        task_force_charlie: 'Task Force Charlie',
        task_force_delta: 'Task Force Delta',
        task_force_echo: 'Task Force Echo',
        task_force_hotel: 'Task Force Hotel',
        task_force_india: 'Task Force India',
        general_alarm: 'General Alarm',
    };
    const first = rows[0].day;
    const last = rows[rows.length - 1].day;
    await page.goto(`/map?period=custom&date_from=${last}&date_to=${first}`);
    const sidebar = page
        .getByRole('heading', { name: 'Incidents', exact: true })
        .locator('../..');
    for (const record of rows) {
        await sidebar.getByText(record.reference, { exact: true }).click();
        const popup = page
            .locator('.leaflet-popup-content')
            .filter({ hasText: record.reference });
        await expect(popup).toContainText(record.reference);
        await expect(popup).toContainText(record.barangay);
        await expect(popup).toContainText(record.date);
        await expect(popup).toContainText(
            record.type
                ? (types[record.type] ?? 'Unclassified')
                : 'Unclassified',
        );
        if (record.alarm)
            await expect(popup).toContainText(alarms[record.alarm]);
    }
});

test('GIS-001 Compare ten marker positions with MySQL coordinates', async ({
    page,
}, testInfo) => {
    test.setTimeout(120_000);
    const records = query<{
        id: number;
        reference: string;
        day: string;
        lat: number;
        lng: number;
    }>(
        `SELECT JSON_OBJECT('id',report_id,'reference',CONCAT('INC-',YEAR(created_at),'-',LPAD(report_id,4,'0')),'day',DATE_FORMAT(created_at,'%Y-%m-%d'),'lat',CAST(latitude AS DOUBLE),'lng',CAST(longitude AS DOUBLE)) FROM community_report WHERE status='resolved' ORDER BY created_at DESC LIMIT 10`,
    );
    expect(records).toHaveLength(10);
    await testInfo.attach('coordinate-samples.json', {
        body: JSON.stringify(records, null, 2),
        contentType: 'application/json',
    });
    await page.goto(
        `/map?period=custom&date_from=${records[9].day}&date_to=${records[0].day}`,
    );
    const sidebar = page
        .getByRole('heading', { name: 'Incidents', exact: true })
        .locator('../..');
    for (const record of records) {
        await sidebar.getByText(record.reference, { exact: true }).click();
        await expect(
            page
                .locator('.leaflet-popup-content')
                .filter({ hasText: record.reference }),
        ).toBeVisible();
        // Translate the visible map centre from a rendered slippy-map tile back to WGS84.
        await expect
            .poll(
                async () => {
                    const center = await page
                        .locator('.leaflet-container')
                        .evaluate((map) => {
                            const tile = [
                                ...map.querySelectorAll<HTMLImageElement>(
                                    'img.leaflet-tile',
                                ),
                            ].find((image) =>
                                /\/(\d+)\/(\d+)\/(\d+)\.png/.test(image.src),
                            );
                            const match = tile?.src.match(
                                /\/(\d+)\/(\d+)\/(\d+)\.png/,
                            );
                            if (!tile || !match) return null;
                            const mapBox = map.getBoundingClientRect();
                            const tileBox = tile.getBoundingClientRect();
                            const zoom = Number(match[1]),
                                scale = 256 * 2 ** zoom;
                            const x =
                                Number(match[2]) * 256 +
                                mapBox.left +
                                mapBox.width / 2 -
                                tileBox.left;
                            const y =
                                Number(match[3]) * 256 +
                                mapBox.top +
                                mapBox.height / 2 -
                                tileBox.top;
                            return {
                                lng: (x / scale) * 360 - 180,
                                lat:
                                    (Math.atan(
                                        Math.sinh(
                                            Math.PI * (1 - (2 * y) / scale),
                                        ),
                                    ) *
                                        180) /
                                    Math.PI,
                            };
                        });
                    if (!center) return Infinity;
                    const radians = Math.PI / 180;
                    const deltaLat = (center.lat - record.lat) * radians;
                    const deltaLng = (center.lng - record.lng) * radians;
                    const a =
                        Math.sin(deltaLat / 2) ** 2 +
                        Math.cos(center.lat * radians) *
                            Math.cos(record.lat * radians) *
                            Math.sin(deltaLng / 2) ** 2;
                    return 12742000 * Math.asin(Math.sqrt(a));
                },
                {
                    timeout: 5000,
                    message: `Map centre for ${record.reference} should match stored coordinates within 10 m`,
                },
            )
            .toBeLessThanOrEqual(10);
        await expect
            .poll(
                async () => {
                    const map = await page
                        .locator('.leaflet-container')
                        .boundingBox();
                    if (!map) return Infinity;
                    const paths = await page
                        .locator(
                            '.leaflet-overlay-pane path.leaflet-interactive',
                        )
                        .all();
                    const distances = await Promise.all(
                        paths.map(async (path) => {
                            const box = await path.boundingBox();
                            if (
                                !box ||
                                box.width < 19 ||
                                box.width > 26 ||
                                box.height < 19 ||
                                box.height > 26
                            )
                                return Infinity;
                            return Math.hypot(
                                box.x + box.width / 2 - (map.x + map.width / 2),
                                box.y +
                                    box.height / 2 -
                                    (map.y + map.height / 2),
                            );
                        }),
                    );
                    return Math.min(...distances);
                },
                {
                    timeout: 5000,
                    message:
                        'The selected marker should be drawn at the map centre',
                },
            )
            .toBeLessThan(12);
    }
});
