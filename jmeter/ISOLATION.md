# Isolated JMeter environment

`.env.jmeter` is an ignored local configuration created from `.env.example` with a separate generated app key. The existing `.env` is unchanged. It selects the local `firesight_jmeter_test` database, a separate session cookie and cache prefix, and the `array` mailer. Its database password is intentionally blank: create a dedicated account and set a strong password in the ignored file before verification. Restrict access to the file with Windows ACLs; Git ignore is not access control. Do not print the file or share its contents.

The separate environment is `APP_ENV=jmeter`. Laravel selects `.env.jmeter` when the helper passes `--env=jmeter` to Artisan and exports `APP_ENV=jmeter` to the server process. This is necessary: `--env=jmeter` alone applies only to the Artisan process, and the child web process must select the same environment on every request. The account seeder accepts `jmeter` in addition to its existing local/testing values while retaining its exact database, loopback, port and explicit-approval guards.

`isolated.ps1` temporarily removes inherited app/database/session/cache/mail environment overrides, sets `APP_ENV=jmeter` and an isolated `APP_CONFIG_CACHE=bootstrap/cache/jmeter-config.php`, and restores the caller's environment on exit. It refuses a cached JMeter configuration file and does not clear or overwrite the existing Herd configuration cache. The read-only verification checks the selected file, runtime environment, cache path, session/cache connections, configured and connected database identity, and effective database account. It cannot prove that the account has no global grants or that the database contains no sensitive data; review both manually. The script uses the values in `.env.jmeter`, not `DB_*` variables set in the calling shell.

Before running any helper:

1. In phpMyAdmin, create a dedicated MariaDB account named `firesight_jmeter` with a strong password and access restricted to the `firesight_jmeter_test` schema. Do not grant global privileges. Migration requires schema creation/alteration/index/reference privileges and normal data privileges; review exact grants with a database administrator. Use the appropriate localhost host entry for the MariaDB server's account matching. Put the credentials only in `.env.jmeter`. Review `SHOW GRANTS` in phpMyAdmin without sharing the output if it contains sensitive details.
2. Confirm the database is genuinely isolated and empty. It was observed empty during preparation, but that can change. No data was copied. Any test dataset must be independently approved and sanitized.
3. Ensure PHP/Herd can read the new file and protect its ACL. Confirm port 8001 is free. The local FireSight verification-service example also used port 8001, so `.env.jmeter` points that service at an intentionally unavailable local endpoint; configure a separate approved service if the test needs it. The admin GET workload does not invoke verification.

From the repository root, these commands use Herd PHP if it is on PATH. If it is not, append `-Php 'C:\Users\ayodr\.config\herd\bin\php84\php.exe'` to each helper invocation. If your PowerShell execution policy blocks scripts, run the helper in a separate PowerShell process with `-ExecutionPolicy Bypass` after reviewing local policy.

```powershell
# Read-only: verifies the actual connected DB and prints no password.
.\jmeter\isolated.ps1 -Action Verify

# After migration approval only. Verifies identity and requires zero visible tables/views.
.\jmeter\isolated.ps1 -Action Migrate

# After the database is migrated and the server is authorized to run.
.\jmeter\isolated.ps1 -Action Serve
```

Serve stays in the foreground at `http://127.0.0.1:8001`; Ctrl+C stops it. Its startup check is a CLI check, so verify the first web requests and runtime environment independently before provisioning or testing. Point the dedicated JMeter plan at port 8001 (`-Jport=8001`); do not use the existing Herd hostname. A server running on Windows PHP's built-in server is normally single-threaded, so it is suitable for functional isolation checks but is not a representative server for the planned 50-concurrent-user performance evaluation. Decide on an isolated, capable server before PERF-001.

The application directory, public assets, `public/hot`, storage files, compiled views and logs are still shared with the Herd installation. Different app keys and cookie names do not isolate the filesystem. Do not use this server for workflows that write files or invoke real integrations without additional isolation. The helper isolates the *configuration-cache path*, not every Laravel cache artifact; do not build or clear shared route/view/event caches while either environment is active. The JMeter test database and database-backed session and cache stores are separate when the checks pass.

The prior dedicated-account instructions in README use a different process-environment workflow. Do not mix them with the helper or use raw `artisan migrate` against an unverified environment. A later account-provisioning step must also retain the private-manifest ACL safeguards and explicitly select the same `.env.jmeter` and separate config-cache path. The seeder and migration have not been run.
