# FireSight Chapter 4 JMeter workflow

The current dedicated-account workflow uses the existing Herd site and `firesight_db2`, selected by the normal `.env`. The abandoned isolation files remain for now and must not be used for this workflow. The original `FireSight-Admin.jmx` and `FireSight-Admin-PERF001.jmx` are preserved. `run.ps1` is the original multi-level runner; it is not the command for this dedicated PERF-001 run.

`FireSight-Admin-Dedicated.jmx` defaults to HTTP `firesight-web-v2.test:80`, 50 users, a 10-second ramp-up and a 60-second scheduler duration. It reads a shared UTF-8 `email,password` CSV, skips its header, does not recycle and stops on EOF. One outer iteration assigns one row per thread; login runs once and the inner workload repeats. Cookies are independent per thread. A 500 ms timer applies before each workload sampler. Duration includes ramp-up and authentication, not 60 seconds at full concurrency. Individual requests can finish after the deadline.

## Scope and assertions

| Request | Successful response | Access |
|---|---|---|
| GET `/login` | 200; `canResetPassword` in Inertia login HTML; establishes session/XSRF cookies | Guest |
| POST `/login` | Form email/password, URL-decoded cookie in `X-XSRF-TOKEN`; redirects followed to 200 with `recentIncidents` | Guest, valid session and XSRF token |
| GET `/dashboard` | 200; `recentIncidents` | BFP staff |
| GET `/incidents` | 200; `reports` | BFP staff |
| GET `/analytics` | 200; `incidentsByType` | BFP admin |
| GET `/map` | 200; `barangayRisk` | BFP staff |
| GET `/geo/barangay_boundaries_lian.json` | 200; `FeatureCollection` | Public local asset |

Status assertions match 200 exactly; body assertions use substring checks. Failed login stops that thread; workload failures remain recorded. Indicative content checks do not replace complete data verification. The workload does not execute browser JavaScript, load third-party map tiles, or exercise mobile APIs or business writes. Login and subsequent web requests write session/cache state. No authentication, CSRF or rate limits are disabled. The five-per-minute login limit is keyed by email and IP, so unique emails do not share one account's bucket.

## Provisioning and private evidence

`JmeterTestAdminSeeder` is only invoked explicitly. It targets the approved local `firesight_db2` database and normal `.env`, checks account schema and InnoDB engines, refuses account-table triggers, and uses a transaction. Direct table insertion avoids registration/model notification events. It creates active, verified `bfp_admin` users and one unique personnel record per account. Passwords are random and only hashes are stored in MySQL.

`jmeter/private/manifest.json` contains private credentials, random ownership markers and exact created user IDs. `accounts.csv` contains the JMeter credentials. `preprovision-baseline.json` and `provisioning-result.json` contain private baseline evidence, including original-data fingerprints and reviewed backup/error-log checksums. All files under `jmeter/private/` are ignored. Keep the Windows ACL restricted and never print, commit, attach or share these files. Git ignore is not an ACL. Do not enable sensitive SQL/request/trace logging. A private filesystem operation lock prevents simultaneous provisioning/cleanup invocations.

Rerunning provisioning recognizes matching emails, markers, hashes and IDs without replacing existing accounts. It refuses collisions. After a commit interrupted before manifest checkpointing, a provisioning retry can recover IDs using the pre-persisted private markers. A damaged/missing manifest requires manual review; do not generate a replacement over existing accounts. Provisioning records and compares pre-existing user/personnel and non-transient business fingerprints before committing. Do not run it concurrently with business activity.

Database writes require explicit `FIRESIGHT_JMETER_APPROVED_DB=firesight_db2`, `FIRESIGHT_JMETER_RISK_ACK=reviewed-current-innodb-risk`, a readable backup matching the supplied SHA-256, and a database log exactly matching the reviewed checksum. If the log changes, the operation stops rather than approving the new entries automatically. These are risk acknowledgement and change-detection guards, not proof of database health. The existing InnoDB LSN/tablespace warnings remain unresolved; the user explicitly accepted proceeding on this development instance. No repair or migration is part of this workflow.

## Completed dedicated-account preflight

On 9 November 2026, 50 test administrators and 50 personnel records were provisioned. All 23 original non-transient table fingerprints remained unchanged. The dedicated five-user preflight used a five-second ramp and 20-second duration: 123 top-level samples, zero failures, five successful login POSTs, five distinct threads and a maximum sampled active count of five. Mean response time was 257.39 ms and throughput was 6.108 samples/sec. No HTTP 429 responses or new MariaDB error-log entries were observed. This is not PERF-001 evidence and does not establish 50-user reliability.

Evidence: `results/dedicated-5users-20261109-231401-3673/` contains the JTL, JMeter log, HTML report and aggregate summary. The CLI disabled redirect sub-result saving to avoid counting both a login parent and its child HTTP steps as separate logical samples.

## PERF-001: execute manually when ready

From the repository root, with JMeter on PATH:

```powershell
$runId = Get-Date -Format 'yyyyMMdd-HHmmss-ffff'
$resultDir = Join-Path (Get-Location) "jmeter/results/PERF-001-$runId"
New-Item -ItemType Directory -Path $resultDir | Out-Null
$accountsPath = (Resolve-Path .\jmeter\private\accounts.csv).Path
jmeter.bat -n -t .\jmeter\FireSight-Admin-Dedicated.jmx `
  -l "$resultDir/results.jtl" -j "$resultDir/jmeter.log" `
  -e -o "$resultDir/html" `
  -Jhost=firesight-web-v2.test -Jprotocol=http -Jport=80 `
  -Jthreads=50 -Jramp=10 -Jduration=60 `
  "-Jaccounts=$accountsPath" -Jjmeter.save.saveservice.subresults=false
Start-Process "$resultDir/html/index.html"
```

Use `D:\apache-jmeter-5.6.3\bin\jmeter.bat` if JMeter is not on PATH. To inspect the plan in GUI, use File → Open. Its default is now 50 users: do not press Start for debugging without overriding the thread properties. GUI listeners are disabled for load runs.

For a separately authorized short validation, use the same command with a new result folder and `-Jthreads=5 -Jramp=5 -Jduration=20`. The first five CSV rows are distinct accounts. Do not repeat rapid sign-ins unnecessarily; each account still has a login limit.

Check the results rather than treating a zero process exit code as success:

```powershell
$samples = Import-Csv "$resultDir/results.jtl"
$samples | Group-Object label,success,responseCode | Select-Object Count,Name
$samples | Measure-Object grpThreads -Maximum
($samples.threadName | Select-Object -Unique).Count
$samples | Where-Object label -eq 'AUTH POST login' | Group-Object success,responseCode
```

For PERF-001, require 50 successful login parent samples, 50 distinct thread names and evidence of 50 active threads after ramp-up, with workload samples from every thread. Active threads include think time and are not the same as simultaneously in-flight HTTP requests. A one-minute graph bucket can obscure the peak: inspect JTL `grpThreads` and JMeter's thread start/finish records as well. Record actual achieved concurrency and duration. Monitor 429/419/500 responses, failed content assertions (including HTTP-200 fatal text), lost threads, MariaDB/PHP logs, CPU, RAM, connection counts and server restarts. Do not automatically retry or hide failures.

Report average elapsed time over the stated sample population, errors and successful throughput over observed elapsed time. Retain raw evidence. This is the web-admin subset of Chapter 4; it excludes mobile flows and data mutation. PERF-004 needs three separately retained runs per level; no repeat, larger-load or endurance test was executed in this task.

## Safe cleanup after explicit authorization

Preview only:

```powershell
$env:FIRESIGHT_JMETER_APPROVED_DB = 'firesight_db2'
$env:FIRESIGHT_JMETER_PRIVATE_DIR_READY = '1'
php artisan jmeter:cleanup-test-accounts --dry-run --no-interaction
```

The preview validates manifest IDs, emails, passwords and personnel ownership markers and reports dependent business references. `jmeter:inspect-test-accounts` is also a read-only preview. Cleanup scans foreign keys only in the connected database and conservatively inspects common user-reference columns without FKs. Arbitrary undocumented references remain a limitation: do not create business activity with these accounts, and finish all sessions/test traffic before deleting them.

After the user explicitly authorizes deletion, review current MariaDB logs. If they still match the reviewed baseline, the following command can use that baseline; a mismatch deliberately blocks deletion and requires investigation, not an automatic checksum refresh:

```powershell
$review = Get-Content .\jmeter\private\preprovision-baseline.json -Raw | ConvertFrom-Json
$env:FIRESIGHT_JMETER_RISK_ACK = 'reviewed-current-innodb-risk'
$env:FIRESIGHT_JMETER_BACKUP = $review.backup_path
$env:FIRESIGHT_JMETER_BACKUP_SHA256 = $review.backup_sha256
$env:FIRESIGHT_JMETER_APPROVED_LOG_SHA256 = $review.error_log_sha256
php artisan jmeter:cleanup-test-accounts --execute `
  --confirm=delete-manifest-owned-test-accounts --no-interaction
```

Deletion requires both explicit command options and write guards. It locks the recorded users, refuses any business references, deletes only their sessions, personnel and user rows in a transaction, and verifies unrelated-data fingerprints before commit. It checkpoints cleanup in the manifest and empties the credential CSV. It never deletes reports, notifications or other business references to make deletion succeed. Keep the private audit manifest securely until retention is complete. Live cleanup has not been executed; actual delete behavior was tested only in SQLite memory.
