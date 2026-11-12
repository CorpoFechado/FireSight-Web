[CmdletBinding()]
param(
    [ValidateSet('Verify', 'Serve', 'Migrate')][string]$Action = 'Verify',
    [string]$Php = 'php'
)

$ErrorActionPreference = 'Stop'
$project = Split-Path -Parent $PSScriptRoot
if (-not (Test-Path -LiteralPath (Join-Path $project '.env.jmeter') -PathType Leaf)) { throw 'Missing .env.jmeter; refusing fallback to .env.' }
if (Test-Path -LiteralPath (Join-Path $project 'bootstrap/cache/jmeter-config.php')) { throw 'The separate JMeter configuration cache exists; remove it only after reviewing its ownership.' }
$phpCommand = (Get-Command $Php -ErrorAction Stop).Source
$saved = @{}
$pattern = '^(APP_|DB_|DATABASE_URL$|MYSQL_ATTR_|SESSION_|CACHE_|QUEUE_|MAIL_|FIRESIGHT_JMETER_|LARAVEL_STORAGE_PATH$)'
Get-ChildItem Env: | Where-Object { $_.Name -match $pattern } | ForEach-Object {
    $saved[$_.Name] = $_.Value
    [Environment]::SetEnvironmentVariable($_.Name, $null, 'Process')
}
try {
    $env:APP_ENV = 'jmeter'
    $env:APP_CONFIG_CACHE = 'bootstrap/cache/jmeter-config.php'
    $env:FIRESIGHT_JMETER_APPROVED_DB = 'firesight_jmeter_test'
    Push-Location $project
    try {
        $verifyArguments = @('artisan', 'jmeter:verify-environment', '--env=jmeter', '--no-interaction')
        if ($Action -eq 'Migrate') { $verifyArguments += '--require-empty' }
        & $phpCommand @verifyArguments
        if ($LASTEXITCODE -ne 0) { throw 'Verification failed; no further command was started.' }
        if ($Action -eq 'Serve') {
            & $phpCommand artisan serve --env=jmeter --host=127.0.0.1 --port=8001 --no-reload --no-interaction
            if ($LASTEXITCODE -ne 0) { throw 'The isolated server exited with an error.' }
        } elseif ($Action -eq 'Migrate') {
            & $phpCommand artisan migrate --env=jmeter --no-interaction
            if ($LASTEXITCODE -ne 0) { throw 'Migration failed; inspect the isolated database before retrying.' }
        }
    } finally {
        Pop-Location
    }
} finally {
    Get-ChildItem Env: | Where-Object { $_.Name -match $pattern } | ForEach-Object { [Environment]::SetEnvironmentVariable($_.Name, $null, 'Process') }
    foreach ($name in $saved.Keys) { [Environment]::SetEnvironmentVariable($name, $saved[$name], 'Process') }
}
