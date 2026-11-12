[CmdletBinding()]
param(
    [ValidateSet('Debug', 'Load', 'Endurance')][string]$Mode = 'Debug',
    [Parameter(Mandatory)][string]$HostName,
    [Parameter(Mandatory)][ValidateRange(1, 65535)][int]$Port,
    [ValidateSet('http', 'https')][string]$Protocol = 'http',
    [Parameter(Mandatory)][string]$Accounts,
    [string]$JMeter = 'jmeter.bat',
    [int]$EnduranceUsers = 0,
    [ValidateRange(1800, 3600)][int]$EnduranceSeconds = 1800,
    [switch]$AuthorizedLoad
)

$ErrorActionPreference = 'Stop'
if ($Mode -ne 'Debug' -and -not $AuthorizedLoad) { throw 'Load and endurance runs require explicit approval; pass -AuthorizedLoad only after approval.' }
if ($Mode -eq 'Endurance' -and $EnduranceUsers -lt 1) { throw 'Specify a positive -EnduranceUsers count.' }
$accountsPath = (Resolve-Path -LiteralPath $Accounts).Path
$accountsRows = @(Import-Csv -LiteralPath $accountsPath)
$needed = if ($Mode -eq 'Load') { 200 } elseif ($Mode -eq 'Endurance') { $EnduranceUsers } else { 1 }
if ($accountsRows.Count -lt $needed) { throw "At least $needed distinct administrator accounts are required." }
if (@($accountsRows | Where-Object { -not $_.email -or -not $_.password }).Count -gt 0) { throw 'CSV must have email,password headers and nonempty values.' }
if (@($accountsRows.email | ForEach-Object { $_.Trim().ToLowerInvariant() } | Select-Object -Unique).Count -ne $accountsRows.Count) { throw 'Every CSV account must have a distinct email.' }
$jmeterCommand = (Get-Command $JMeter -ErrorAction Stop).Source
$plan = Join-Path $PSScriptRoot 'FireSight-Admin.jmx'
$runId = Get-Date -Format 'yyyyMMdd-HHmmss-ffff'
$outputRoot = Join-Path $PSScriptRoot "results/$runId-$Mode"
New-Item -ItemType Directory -Path $outputRoot -ErrorAction Stop | Out-Null
$levels = if ($Mode -eq 'Load') { @(50, 100, 200) } elseif ($Mode -eq 'Endurance') { @($EnduranceUsers) } else { @(1) }
$repeats = if ($Mode -eq 'Load') { 3 } else { 1 }
$duration = if ($Mode -eq 'Load') { 60 } elseif ($Mode -eq 'Endurance') { $EnduranceSeconds } else { 15 }
$ramp = if ($Mode -eq 'Debug') { 1 } else { 10 }
foreach ($level in $levels) {
    foreach ($iteration in 1..$repeats) {
        $directory = Join-Path $outputRoot "users-$level-run-$iteration"
        New-Item -ItemType Directory -Path $directory | Out-Null
        $jtl = Join-Path $directory 'results.jtl'
        $report = Join-Path $directory 'html'
        $log = Join-Path $directory 'jmeter.log'
        & $jmeterCommand -n -t $plan -l $jtl -j $log -e -o $report "-Jhost=$HostName" "-Jport=$Port" "-Jprotocol=$Protocol" "-Jaccounts=$accountsPath" "-Jthreads=$level" "-Jramp=$ramp" "-Jduration=$duration"
        if ($LASTEXITCODE -ne 0) { throw "JMeter exited with code $LASTEXITCODE. Inspect $log." }
        $samples = @(Import-Csv -LiteralPath $jtl)
        if ($samples.Count -eq 0) { throw "No samples in $jtl; inspect the JMeter log." }
        $success = @($samples | Where-Object { $_.success -eq 'true' }).Count
        $first = ($samples | Measure-Object -Property timeStamp -Minimum).Minimum
        $last = ($samples | ForEach-Object { [double]$_.timeStamp + [double]$_.elapsed } | Measure-Object -Maximum).Maximum
        $seconds = ($last - $first) / 1000
        $case = if ($Mode -eq 'Debug') { 'Debug' } elseif ($Mode -eq 'Endurance') { 'REL-003' } elseif ($level -eq 50) { 'PERF-001' } elseif ($level -eq 100) { 'PERF-002' } else { 'PERF-003' }
        [pscustomobject]@{ TestCaseID=$case; ConcurrentUsers=$level; Run=$iteration; ConfiguredSeconds=$duration; ObservedSeconds=$seconds; TotalRequests=$samples.Count; SuccessfulRequests=$success; FailedRequests=($samples.Count-$success); AverageResponseTimeMs=($samples | Measure-Object -Property elapsed -Average).Average; ThroughputSuccessfulPerSecond=if ($seconds -gt 0) { $success/$seconds } else { '' }; ErrorRatePercent=100*($samples.Count-$success)/$samples.Count; AvailabilityPercent=if ($Mode -eq 'Endurance') { 100*$success/$samples.Count } else { '' } } | Export-Csv -LiteralPath (Join-Path $directory 'summary.csv') -NoTypeInformation
    }
}
if ($Mode -eq 'Load') {
    $summaries = foreach ($level in $levels) {
        $runs = @(1..$repeats | ForEach-Object { Import-Csv -LiteralPath (Join-Path $outputRoot "users-$level-run-$_/summary.csv") })
        foreach ($metric in @('TotalRequests', 'SuccessfulRequests', 'FailedRequests', 'AverageResponseTimeMs', 'ThroughputSuccessfulPerSecond', 'ErrorRatePercent')) {
            $values = @($runs | ForEach-Object { [double]$_.$metric })
            $stats = $values | Measure-Object -Average -Minimum -Maximum
            [pscustomobject]@{ TestCaseID='PERF-004'; ConcurrentUsers=$level; Metric=$metric; Run1=$values[0]; Run2=$values[1]; Run3=$values[2]; Mean=$stats.Average; Minimum=$stats.Minimum; Maximum=$stats.Maximum }
        }
    }
    $summaries | Export-Csv -LiteralPath (Join-Path $outputRoot 'PERF-004-summary.csv') -NoTypeInformation
}
Write-Host "Results saved to $outputRoot"
