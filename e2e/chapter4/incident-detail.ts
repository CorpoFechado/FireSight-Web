import { expect, type Page } from '@playwright/test';
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
};

export function incidentDetails(ids: number[]): Detail[] {
    if (!ids.length) return [];
    return query<Detail>(
        `SELECT JSON_OBJECT('id',r.report_id,'reference',CONCAT('INC-',YEAR(r.created_at),'-',LPAD(r.report_id,4,'0')),'name',r.reporter_name,'contact',r.contact_number,'submitted',DATE_FORMAT(r.created_at,'%Y-%m-%d %H:%i'),'barangay',COALESCE(b.barangay_name,bi.barangay_name),'description',r.description,'status',r.status,'type',i.incident_type,'alarm',i.alarm_level,'cause',i.cause_of_fire,'notes',i.notes,'establishment',i.establishment_name,'owner',i.owner_name,'occupant',i.occupant_name,'injuries',i.injuries,'fatalities',i.fatalities,'photos',(r.report_image IS NOT NULL AND r.report_image <> '')+(SELECT COUNT(*) FROM report_evidence e WHERE e.report_id=r.report_id)) FROM community_report r LEFT JOIN incident_record i ON i.report_id=r.report_id LEFT JOIN barangay b ON b.barangay_id=r.barangay_id LEFT JOIN barangay bi ON bi.barangay_id=i.barangay_id WHERE r.report_id IN (${ids.map(Number).join(',')}) ORDER BY r.created_at DESC`,
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
