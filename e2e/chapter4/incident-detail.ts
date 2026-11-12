import { expect, type Page } from '@playwright/test';
import { statSync } from 'node:fs';
import { resolve, sep } from 'node:path';
import { query } from './database';

type Detail = {
    id: number;
    reference: string;
    name: string;
    contact: string;
    submitted: string;
    barangay: string | null;
    description: string | null;
    status: string;
    type: string | null;
    alarm: string | null;
    cause: string | null;
    notes: string | null;
    establishment: string | null;
    owner: string | null;
    occupant: string | null;
    injuries: number | null;
    fatalities: number | null;
    photos: number;
    started: string | null;
    extinguished: string | null;
    ai: string | null;
    confidence: number | null;
    verified: string | null;
};

export function incidentDetails(ids: number[]): Detail[] {
    if (!ids.length) return [];
    return query<Detail>(
        `SELECT JSON_OBJECT('id',r.report_id,'reference',CONCAT('INC-',YEAR(r.created_at),'-',LPAD(r.report_id,4,'0')),'name',r.reporter_name,'contact',r.contact_number,'submitted',DATE_FORMAT(r.created_at,'%Y-%m-%d %H:%i'),'barangay',COALESCE(b.barangay_name,bi.barangay_name),'description',r.description,'status',r.status,'type',i.incident_type,'alarm',i.alarm_level,'cause',i.cause_of_fire,'notes',i.notes,'establishment',i.establishment_name,'owner',i.owner_name,'occupant',i.occupant_name,'injuries',i.injuries,'fatalities',i.fatalities,'photos',(r.report_image IS NOT NULL AND r.report_image <> '')+(SELECT COUNT(*) FROM report_evidence e WHERE e.report_id=r.report_id),'started',DATE_FORMAT(COALESCE(i.time_fire_started,r.created_at),'%l:%i %p'),'extinguished',DATE_FORMAT(i.time_fire_out,'%l:%i %p'),'ai',r.ai_fire_label,'confidence',r.ai_fire_confidence,'verified',DATE_FORMAT(r.ai_verified_at,'%Y-%m-%d %H:%i')) FROM community_report r LEFT JOIN incident_record i ON i.report_id=r.report_id LEFT JOIN barangay b ON b.barangay_id=r.barangay_id LEFT JOIN barangay bi ON bi.barangay_id=i.barangay_id WHERE r.report_id IN (${ids.map(Number).join(',')}) ORDER BY r.created_at DESC`,
    );
}

export async function verifyIncidentDetail(
    page: Page,
    detail: Detail,
): Promise<void> {
    const row = (label: string) =>
        page.getByText(label, { exact: true }).first().locator('..');
    await expect(
        page.getByText(detail.reference, { exact: true }).first(),
    ).toBeVisible();
    await expect(row('Name')).toContainText(detail.name);
    await expect(row('Contact')).toContainText(detail.contact);
    await expect(row('Submitted')).toContainText(detail.submitted);
    await expect(row('Barangay')).toContainText(
        detail.barangay ?? 'Not yet assigned',
    );
    await expect(
        page
            .getByText(detail.description || 'No description provided.', {
                exact: true,
            })
            .first(),
    ).toBeVisible();
    await expect(
        page
            .getByText(
                detail.status.charAt(0).toUpperCase() + detail.status.slice(1),
                { exact: true },
            )
            .first(),
    ).toBeVisible();
    const photos = page
        .getByText('Fire Scene Photos', { exact: true })
        .locator('..');
    await expect(photos).toContainText(`${detail.photos} total`);
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
    if (detail.type)
        await expect(page.getByText(types[detail.type]).first()).toBeVisible();
    if (detail.alarm)
        await expect(
            page.getByText(alarms[detail.alarm], { exact: true }).first(),
        ).toBeVisible();
    const ai = page
        .getByText('AI Image Verification', { exact: true })
        .locator('..');
    if (!detail.ai) {
        await expect(ai).toContainText('Unverified');
    } else {
        const labels: Record<string, string> = {
            fire: 'Fire Detected',
            smoke: 'Smoke Detected',
            no_fire: 'No Fire Detected',
        };
        await expect(ai).toContainText(
            labels[detail.ai] ?? detail.ai.replaceAll('_', ' '),
        );
        if (detail.confidence !== null)
            await expect(ai).toContainText(
                `${(Number(detail.confidence) * 100).toFixed(1)}%`,
            );
        await expect(ai).toContainText(
            detail.verified
                ? `Verified: ${detail.verified}`
                : 'Timestamp not recorded',
        );
    }
    if (
        detail.type ||
        detail.alarm ||
        detail.cause ||
        detail.notes ||
        detail.establishment ||
        detail.owner ||
        detail.injuries ||
        detail.fatalities
    ) {
        await expect(
            page.getByRole('heading', { name: 'BFP Assessment' }),
        ).toBeVisible();
        await expect(row('Establishment')).toContainText(
            detail.establishment || 'None',
        );
        await expect(row('Owner / Occupant')).toContainText(
            [detail.owner, detail.occupant].filter(Boolean).join(' · ') ||
                'Unspecified',
        );
        await expect(row('Cause of Fire')).toContainText(
            detail.cause || 'Not yet determined',
        );
        await expect(row('Time Fire Started')).toContainText(
            detail.started ?? 'Unspecified',
        );
        await expect(row('Time Fire Out')).toContainText(
            detail.extinguished ?? 'Unspecified',
        );
        await expect(row('Injuries')).toContainText(
            String(detail.injuries ?? 0),
        );
        await expect(row('Fatalities')).toContainText(
            String(detail.fatalities ?? 0),
        );
        if (detail.notes)
            await expect(
                page.getByText(detail.notes, { exact: true }).first(),
            ).toBeVisible();
    }
}

/** Additional database-backed checks for the information displayed on the detail page. */
export async function verifyExtendedIncidentDetail(
    page: Page,
    id: number,
): Promise<number[]> {
    const [record] = query<{
        lat: number;
        lng: number;
        deviceLat: number | null;
        deviceLng: number | null;
        image: string | null;
        contactName: string | null;
        contactRole: string | null;
        contactPhone: string | null;
    }>(
        `SELECT JSON_OBJECT('lat',CAST(r.latitude AS DOUBLE),'lng',CAST(r.longitude AS DOUBLE),'deviceLat',CAST(r.device_latitude AS DOUBLE),'deviceLng',CAST(r.device_longitude AS DOUBLE),'image',r.report_image,'contactName',(SELECT c.name FROM barangay_contact c WHERE c.barangay_id=COALESCE(r.barangay_id,i.barangay_id) ORDER BY c.contact_id LIMIT 1),'contactRole',(SELECT c.role FROM barangay_contact c WHERE c.barangay_id=COALESCE(r.barangay_id,i.barangay_id) ORDER BY c.contact_id LIMIT 1),'contactPhone',(SELECT c.phone_number FROM barangay_contact c WHERE c.barangay_id=COALESCE(r.barangay_id,i.barangay_id) ORDER BY c.contact_id LIMIT 1)) FROM community_report r LEFT JOIN incident_record i ON i.report_id=r.report_id WHERE r.report_id=${Number(id)}`,
    );
    expect(record).toBeDefined();
    const contacts = page
        .getByText('Barangay Local Contact', { exact: true })
        .locator('../..');
    if (record.contactName) {
        await expect(contacts).toContainText(record.contactName);
        if (record.contactRole)
            await expect(contacts).toContainText(record.contactRole);
        if (record.contactPhone)
            await expect(contacts.locator('a[href^="tel:"]')).toHaveAttribute(
                'href',
                `tel:${record.contactPhone}`,
            );
    } else {
        await expect(contacts).toContainText(
            /No official contact details registered|Assign a barangay/,
        );
    }
    const links = query<{ reference: string; status: string }>(
        `SELECT DISTINCT JSON_OBJECT('reference',CONCAT('INC-',YEAR(r.created_at),'-',LPAD(r.report_id,4,'0')),'status',r.status) FROM report_link l JOIN community_report r ON r.report_id=IF(l.main_report_id=${Number(id)},l.related_report_id,l.main_report_id) WHERE l.main_report_id=${Number(id)} OR l.related_report_id=${Number(id)}`,
    );
    const related = page
        .getByText('Linked / Related Reports', { exact: true })
        .locator('..');
    if (links.length === 0)
        await expect(related).toContainText(
            'No linked reports for this incident.',
        );
    for (const link of links) {
        const row = related.getByRole('link', {
            name: new RegExp(link.reference),
        });
        await expect(row).toContainText(
            link.status.charAt(0).toUpperCase() + link.status.slice(1),
        );
    }
    const images = query<{ path: string; caption: string | null }>(
        `SELECT JSON_OBJECT('path', image_path, 'caption', caption) FROM report_evidence WHERE report_id=${Number(id)} ORDER BY created_at`,
    );
    if (record.image) images.unshift({ path: record.image, caption: null });
    const photoArea = page
        .getByText('Fire Scene Photos', { exact: true })
        .locator('..')
        .locator('..');
    const unavailable: number[] = [];
    for (const image of images) {
        const url = /^https?:\/\//.test(image.path)
            ? image.path
            : `/storage/${image.path.replace(/^\/?(storage\/)?/, '')}`;
        if (!/^https?:\/\//.test(image.path)) {
            const root = resolve('storage/app/public');
            const file = resolve(
                root,
                image.path.replace(/^\/?(storage\/)?/, ''),
            );
            expect(
                file.startsWith(root + sep),
                'Stored image path must be inside public storage',
            ).toBe(true);
            try {
                expect(
                    statSync(file).isFile(),
                    'Stored image path must identify a file',
                ).toBe(true);
            } catch (error) {
                if ((error as NodeJS.ErrnoException).code !== 'ENOENT')
                    throw error;
                unavailable.push(id);
                continue;
            }
        }
        const response = await page.request.get(url);
        expect
            .soft(
                response.ok(),
                `Stored image for report ${id} should be accessible (HTTP ${response.status()})`,
            )
            .toBe(true);
        if (response.ok()) {
            const sources = await photoArea
                .locator('img')
                .evaluateAll((elements) =>
                    elements.map((element) => element.getAttribute('src')),
                );
            expect
                .soft(
                    sources,
                    `Displayed photo source should match stored path for report ${id}`,
                )
                .toContain(url);
        }
        if (image.caption)
            await expect
                .soft(photoArea.getByText(image.caption, { exact: true }))
                .toBeVisible();
    }
    if (unavailable.length)
        await expect(
            photoArea.getByText('Image unavailable', { exact: true }),
        ).toHaveCount(unavailable.length);
    const dispatches = query<{ count: number }>(
        `SELECT JSON_OBJECT('count',COUNT(*)) FROM incident_dispatch WHERE report_id=${Number(id)}`,
    );
    if (
        Number(dispatches[0].count) === 0 &&
        ['dispatched', 'resolved'].includes(await queryStatus(id))
    ) {
        await expect(
            page.getByText('Sample data', { exact: true }),
        ).toBeVisible();
    }
    const location = page
        .getByText('Incident Location', { exact: true })
        .locator('..');
    await expect(location.locator('.leaflet-container')).toBeVisible();
    const markers = location.locator('.leaflet-marker-icon');
    await expect(markers).toHaveCount(
        record.deviceLat !== null &&
            record.deviceLng !== null &&
            (Number(record.lat) !== Number(record.deviceLat) ||
                Number(record.lng) !== Number(record.deviceLng))
            ? 2
            : 1,
    );
    const expectedLocations = [
        { lat: Number(record.lat), lng: Number(record.lng) },
    ];
    if (
        record.deviceLat !== null &&
        record.deviceLng !== null &&
        (Number(record.lat) !== Number(record.deviceLat) ||
            Number(record.lng) !== Number(record.deviceLng))
    ) {
        expectedLocations.push({
            lat: Number(record.deviceLat),
            lng: Number(record.deviceLng),
        });
    }
    for (let index = 0; index < expectedLocations.length; index++) {
        await expect
            .poll(
                async () => {
                    const actual = await location
                        .locator('.leaflet-container')
                        .evaluate((map, markerIndex) => {
                            const marker = map.querySelectorAll(
                                '.leaflet-marker-icon',
                            )[markerIndex];
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
                            if (!marker || !tile || !match) return null;
                            const markerBox = marker.getBoundingClientRect();
                            const tileBox = tile.getBoundingClientRect();
                            const scale = 256 * 2 ** Number(match[1]);
                            const x =
                                Number(match[2]) * 256 +
                                markerBox.left +
                                markerBox.width / 2 -
                                tileBox.left;
                            const y =
                                Number(match[3]) * 256 +
                                markerBox.top +
                                markerBox.height / 2 -
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
                        }, index);
                    if (!actual) return Infinity;
                    const target = expectedLocations[index];
                    const radians = Math.PI / 180;
                    const dLat = (actual.lat - target.lat) * radians;
                    const dLng = (actual.lng - target.lng) * radians;
                    const a =
                        Math.sin(dLat / 2) ** 2 +
                        Math.cos(actual.lat * radians) *
                            Math.cos(target.lat * radians) *
                            Math.sin(dLng / 2) ** 2;
                    return 12742000 * Math.asin(Math.sqrt(a));
                },
                {
                    message: `Displayed map marker ${index + 1} should match stored coordinates within 25 m`,
                },
            )
            .toBeLessThanOrEqual(25);
    }
    return unavailable;
}

async function queryStatus(id: number): Promise<string> {
    const [row] = query<{ status: string }>(
        `SELECT JSON_OBJECT('status',status) FROM community_report WHERE report_id=${Number(id)}`,
    );
    return row.status;
}
