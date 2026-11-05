import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
    testDir: './e2e/chapter4',
    fullyParallel: false,
    workers: 1,
    retries: 0,
    outputDir: 'test-results/chapter4',
    reporter: [
        ['list'],
        ['html', { outputFolder: 'playwright-report/chapter4', open: 'never' }],
    ],
    use: {
        baseURL:
            process.env.FIRESIGHT_TEST_URL || 'http://firesight-web-v2.test',
        screenshot: 'only-on-failure',
        trace: 'retain-on-failure',
    },
    projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],
});
