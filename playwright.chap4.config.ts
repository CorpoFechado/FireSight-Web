import { defineConfig, devices } from '@playwright/test';

// Playwright's failure snapshots can include personal information from incidents.
process.env.PLAYWRIGHT_NO_COPY_PROMPT = '1';

export default defineConfig({
    testDir: './e2e/chapter4',
    fullyParallel: false,
    workers: 1,
    retries: 0,
    // RET-012 is deferred until the reference-search requirement is decided.
    // Set FIRESIGHT_INCLUDE_DEFERRED=1 to run it again when needed.
    grepInvert:
        process.env.FIRESIGHT_INCLUDE_DEFERRED === '1'
            ? undefined
            : /RET-012\b/,
    outputDir: 'test-results/chapter4',
    reporter: [
        ['list'],
        ['./e2e/chapter4/privacy-reporter.mjs'],
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
