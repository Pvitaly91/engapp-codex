[CmdletBinding()]
param([Parameter(Mandatory)][string] $RunDirectory, [Parameter(Mandatory)][string] $ExpectedOwner,
    [ValidatePattern('^[a-z0-9-]{1,32}$')][string] $LaunchLabel = 'acceptance')
$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest
. (Join-Path $PSScriptRoot 'runtime-transaction.ps1')
Add-Type -Path (Join-Path $PSScriptRoot 'startup-native.cs')
$privateRoot = 'D:\DEV\htdocs\gramlyze.loc\storage\app\seo-m9-4-startup-local'
$run = [IO.Path]::GetFullPath($RunDirectory)
if ([IO.Path]::GetDirectoryName($run) -ine $privateRoot -or [IO.Path]::GetFileName($run) -notmatch '^apply-[0-9-]+-[a-f0-9]{8}$') { throw 'Unexpected run directory' }
Assert-StartupPath $run
$identity = [Security.Principal.WindowsIdentity]::GetCurrent()
$elevated = ([Security.Principal.WindowsPrincipal]::new($identity)).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
$mode = [GramlyzeStartupNative]::GetErrorMode()
if ($identity.Name -ine $ExpectedOwner -or -not $elevated -or $mode -ne 0) { throw 'Console must have expected admin identity and default error mode 0' }
$bat = 'C:\Program Files\xampp\apache_start.bat'
$manifest = Get-Content -LiteralPath (Join-Path $run 'manifest.json') -Raw | ConvertFrom-Json
$batRow = @($manifest.files | Where-Object { $_.relative -ceq 'apache_start.bat' })
if ($batRow.Count -ne 1 -or (Get-StartupHash $bat) -ne $batRow[0].sha256) { throw 'Bat changed since backup' }
if (@(Get-CimInstance Win32_Process -Filter "Name = 'httpd.exe'" | Where-Object { -not $_.ExecutablePath -or $_.ExecutablePath -ieq 'C:\Program Files\xampp\apache\bin\httpd.exe' }).Count) { throw 'Target Apache already running' }
if (@(Get-NetTCPConnection -State Listen | Where-Object { $_.LocalPort -in @(80,443) }).Count) { throw 'Apache ports already listening' }
$evidence = [ordered]@{at=[DateTime]::UtcNow.ToString('o');pid=$PID;owner=$identity.Name;elevated=$elevated;error_mode=$mode;bat=$bat;stdio='ordinary visible console, no redirection';status='calling-bat'}
$evidence.environment = [ordered]@{}
foreach ($name in @('PATH','PHPRC','PHP_INI_SCAN_DIR','OPENSSL_CONF','OPENSSL_MODULES','SystemRoot','TEMP','TMP')) {
    $evidence.environment[$name] = [Environment]::GetEnvironmentVariable($name,'Process')
}
$output = Join-Path $run ('console-start-'+$LaunchLabel+'.json')
if (Test-Path -LiteralPath $output) { throw 'Console evidence already exists; no duplicate start' }
[IO.File]::WriteAllText($output, ($evidence | ConvertTo-Json), [Text.UTF8Encoding]::new($false))
Write-Host 'Gramlyze startup acceptance: administrative console, default error mode 0.'
# Execute the actual user launcher unmodified, in this new visible console.
# No output capture, dialog dismissal or Windows error mode mutation.
& $bat
$evidence.status = 'bat-returned'
$evidence.exit_code = $LASTEXITCODE
$evidence.finished_at = [DateTime]::UtcNow.ToString('o')
[IO.File]::WriteAllText((Join-Path $run ('console-exit-'+$LaunchLabel+'.json')), ($evidence | ConvertTo-Json), [Text.UTF8Encoding]::new($false))
exit $LASTEXITCODE
