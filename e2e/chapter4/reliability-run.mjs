import { createHash } from 'node:crypto';
import { execFileSync, spawnSync } from 'node:child_process';
import {
    mkdirSync,
    readFileSync,
    readdirSync,
    statSync,
    writeFileSync,
} from 'node:fs';
import { join } from 'node:path';

// Run the selected suite in separate processes; never overwrite its HTML report.
const runCount = Number(process.env.FIRESIGHT_RELIABILITY_RUNS || '2');
const assetEligibleOnly =
    process.env.FIRESIGHT_RELIABILITY_EXCLUDE_ASSETS === '1';
const excludedCases = assetEligibleOnly
    ? ['RET-012', 'RET-002', 'RET-011']
    : ['RET-012'];
if (!Number.isInteger(runCount) || runCount < 2)
    throw new Error('FIRESIGHT_RELIABILITY_RUNS must be at least 2.');
if (!process.env.FIRESIGHT_ADMIN_EMAIL || !process.env.FIRESIGHT_ADMIN_PASSWORD)
    throw new Error(
        'Set the administrator credential environment variables first.',
    );
const folder = join(
    'test-results',
    'chapter4-reliability',
    new Date().toISOString().replace(/[:.]/g, '-'),
);
mkdirSync(folder, { recursive: true });

function hashSources() {
    const hash = createHash('sha256');
    function visit(path) {
        if (!statSync(path).isDirectory()) {
            if (path.endsWith('results.md')) return;
            hash.update(path).update(readFileSync(path));
            return;
        }
        for (const entry of readdirSync(path).sort()) visit(join(path, entry));
    }
    for (const path of [
        'app',
        'resources/js',
        'routes',
        'e2e/chapter4',
        'playwright.chap4.config.ts',
        'package-lock.json',
    ])
        visit(path);
    return hash.digest('hex');
}

function hashData() {
    const config = Object.fromEntries(
        readFileSync('.env', 'utf8')
            .split(/\r?\n/)
            .flatMap((line) => {
                const match = line.match(/^([A-Z_]+)=(.*)$/);
                return match
                    ? [[match[1], match[2].replace(/^["']|["']$/g, '')]]
                    : [];
            }),
    );
    const tables = [
        'community_report',
        'incident_record',
        'barangay',
        'barangay_contact',
        'risk_assessment',
        'report_evidence',
        'report_link',
        'report_status_history',
        'incident_dispatch',
        'incident_dispatch_truck',
        'incident_dispatch_personnel',
        'users',
        'bfp_personnel_details',
    ];
    const result = execFileSync(
        process.env.FIRESIGHT_MYSQL_CLIENT || 'D:/xampp/mysql/bin/mysql.exe',
        [
            '-h',
            config.DB_HOST,
            '-P',
            config.DB_PORT,
            '-u',
            config.DB_USERNAME,
            config.DB_DATABASE,
            '--batch',
            '--skip-column-names',
            '-e',
            `CHECKSUM TABLE ${tables.join(',')}`,
        ],
        {
            encoding: 'utf8',
            env: { ...process.env, MYSQL_PWD: config.DB_PASSWORD || '' },
        },
    );
    return createHash('sha256').update(result).digest('hex');
}

function collect(suites) {
    const output = [];
    function walk(suite) {
        for (const spec of suite.specs || [])
            for (const test of spec.tests || []) {
                output.push({
                    title: spec.title,
                    result: test.results?.at(-1)?.status || 'not-run',
                    durationMs: test.results?.at(-1)?.duration ?? null,
                });
            }
        for (const nested of suite.suites || []) walk(nested);
    }
    for (const suite of suites) walk(suite);
    return output.sort((a, b) => a.title.localeCompare(b.title));
}

const initial = { source: hashSources(), data: hashData() };
const runs = [];
function writeEvidence() {
    const rows = ['run_id,test_id,result,duration_ms'];
    for (const run of runs)
        for (const test of run.tests)
            rows.push(
                `${run.run},"${test.title.replaceAll('"', '""')}",${test.result},${test.durationMs ?? ''}`,
            );
    writeFileSync(
        join(folder, 'RUNS_Playwright_v01.csv'),
        rows.join('\n') + '\n',
    );
    const completed = runs.filter((run) => run.completed);
    const expectedTitles = runs[0]?.tests.map((test) => test.title) ?? [];
    const sameSelection = (run) =>
        JSON.stringify(run.tests.map((test) => test.title)) ===
        JSON.stringify(expectedTitles);
    const allPassing = completed.filter(
        (run) =>
            run.stable &&
            sameSelection(run) &&
            run.tests.length > 0 &&
            run.tests.every((test) => test.result === 'passed'),
    ).length;
    const perCheck = expectedTitles.map((title) => ({
        title,
        passed: completed.filter((run) =>
            run.tests.some(
                (test) => test.title === title && test.result === 'passed',
            ),
        ).length,
        completedRuns: completed.length,
    }));
    const summary = {
        scope: assetEligibleOnly ? 'eligible subset' : 'active suite',
        excludedCases,
        requestedRuns: runCount,
        completedRuns: completed.length,
        initial,
        allPassing,
        consistencyPercent: completed.length
            ? (allPassing / completed.length) * 100
            : null,
        runs: runs.map(({ tests, ...run }) => ({
            ...run,
            passed: tests.filter((test) => test.result === 'passed').length,
            failed: tests.filter(
                (test) =>
                    test.result === 'failed' || test.result === 'timedOut',
            ).length,
            skipped: tests.filter((test) => test.result === 'skipped').length,
            checks: tests.length,
        })),
        perCheck,
    };
    writeFileSync(
        join(folder, 'summary.json'),
        JSON.stringify(summary, null, 2),
    );
    return summary;
}
for (let run = 1; run <= runCount; run++) {
    console.log(
        `Starting ${assetEligibleOnly ? 'eligible subset' : 'active suite'} run ${run}/${runCount}.`,
    );
    const report = join(folder, `run-${run}.json`);
    const started = Date.now();
    const result = spawnSync(
        process.execPath,
        [
            'node_modules/@playwright/test/cli.js',
            'test',
            '--config=playwright.chap4.config.ts',
            `--grep-invert=${excludedCases.join('|')}`,
            '--reporter=./e2e/chapter4/privacy-reporter.mjs,json',
            `--output=${join(folder, `artifacts-${run}`)}`,
        ],
        {
            stdio: 'inherit',
            env: { ...process.env, PLAYWRIGHT_JSON_OUTPUT_NAME: report },
        },
    );
    let tests = [];
    try {
        tests = collect(JSON.parse(readFileSync(report, 'utf8')).suites || []);
    } catch {
        /* Missing report denotes an interrupted run. */
    }
    const current = { source: hashSources(), data: hashData() };
    const stable =
        current.source === initial.source && current.data === initial.data;
    const completed =
        !result.error &&
        result.status !== null &&
        tests.length > 0 &&
        tests.every(
            (test) => !['not-run', 'interrupted'].includes(test.result),
        );
    runs.push({
        run,
        exitCode: result.status,
        signal: result.signal,
        stable,
        fingerprints: current,
        completed,
        durationMs: Date.now() - started,
        tests,
    });
    writeEvidence();
    console.log(
        `Run ${run}: ${tests.filter((test) => test.result === 'passed').length}/${tests.length} passed; source and data stable: ${stable}.`,
    );
    if (!stable || !completed) break;
}
const summary = writeEvidence();
console.log(
    `Reliability evidence: ${folder}; completed: ${summary.completedRuns}; consistency: ${summary.consistencyPercent ?? 'undefined'}.`,
);
if (
    summary.completedRuns !== runCount ||
    runs.some((run) => !run.stable) ||
    summary.allPassing !== runCount
)
    process.exitCode = 1;
