import { expect, test } from '@playwright/test';

test.use({ trace: 'off', screenshot: 'off' });

test('resident details retain their size across tabs', async ({ page }) => {
    const email = process.env.FIRESIGHT_ADMIN_EMAIL;
    const password = process.env.FIRESIGHT_ADMIN_PASSWORD;
    test.skip(!email || !password, 'Set the administrator test credentials.');

    await page.goto('/login');
    await page.getByLabel('Email address').fill(email!);
    await page.getByLabel('Password', { exact: true }).fill(password!);
    await page.getByRole('button', { name: 'Sign In', exact: true }).click();
    await expect(page).toHaveURL(/\/dashboard/);
    await page.goto('/residents');
    const viewButton = page
        .getByRole('button', { name: 'View', exact: true })
        .first();
    test.skip(
        (await viewButton.count()) === 0,
        'At least one resident is required.',
    );
    await viewButton.click();

    const dialog = page.getByRole('dialog');
    await expect(dialog.getByRole('button', { name: 'Profile' })).toBeVisible();
    const initialSize = await dialog.boundingBox();
    expect(initialSize).not.toBeNull();

    for (const name of ['Security', 'Account Status', 'Reports']) {
        await dialog.getByRole('button', { name }).click();
        const size = await dialog.boundingBox();
        expect(size?.width).toBeCloseTo(initialSize!.width, 0);
        expect(size?.height).toBeCloseTo(initialSize!.height, 0);
    }
});
