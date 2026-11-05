import { expect, test } from '@playwright/test';

const email = process.env.FIRESIGHT_ADMIN_EMAIL;
const password = process.env.FIRESIGHT_ADMIN_PASSWORD;

// Login traces and snapshots may contain credential values.
test.use({ trace: 'off', screenshot: 'off' });

async function signIn(page: import('@playwright/test').Page) {
    await page.goto('/login');
    await page.getByLabel('Email address').fill(email!);
    await page.getByLabel('Password', { exact: true }).fill(password!);
    try {
        await Promise.all([
            page.waitForResponse(
                (response) =>
                    response.url().endsWith('/login') &&
                    response.request().method() === 'POST',
            ),
            page.getByRole('button', { name: 'Sign In' }).click(),
        ]);
    } finally {
        if (await page.getByLabel('Password', { exact: true }).isVisible()) {
            await page.getByLabel('Password', { exact: true }).fill('');
        }
    }
    await expect(page).toHaveURL(/\/dashboard/);
}

test('AUTH-003 Administrator valid login (web)', async ({ page }) => {
    test.skip(
        !email || !password,
        'Set FIRESIGHT_ADMIN_EMAIL and FIRESIGHT_ADMIN_PASSWORD for the existing test account.',
    );
    await signIn(page);
    await expect(
        page.getByRole('link', { name: /analytics/i }).first(),
    ).toBeVisible();
});

test('AUTH-004 Wrong password is rejected', async ({ page }) => {
    test.skip(!email, 'Set FIRESIGHT_ADMIN_EMAIL to an existing account.');
    await page.goto('/login');
    await page.getByLabel('Email address').fill(email!);
    await page.getByLabel('Password', { exact: true }).fill('WrongPass#1');
    await page.getByRole('button', { name: 'Sign In' }).click();
    await expect(
        page.getByText(/credentials.*(match|incorrect)|invalid credentials/i),
    ).toBeVisible();
    await page.goto('/dashboard');
    await expect(page).toHaveURL(/\/login/);
});

test('AUTH-009 Logout ends the admin web session', async ({ page }) => {
    test.skip(
        !email || !password,
        'Set FIRESIGHT_ADMIN_EMAIL and FIRESIGHT_ADMIN_PASSWORD for the existing test account.',
    );
    await signIn(page);
    await page.getByRole('button', { name: 'Sign Out' }).click();
    await expect(page).toHaveURL(/\/($|login)/);
    await page.goBack();
    await page.reload();
    await expect(page).toHaveURL(/\/login/);
});
