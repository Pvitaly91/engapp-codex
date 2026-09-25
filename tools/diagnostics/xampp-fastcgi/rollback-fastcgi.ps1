[CmdletBinding()]
param(
    [Parameter(Mandatory)][string] $BackupDirectory,
    [ValidateSet('RestoreConfig', 'Cleanup')][string] $Phase = 'RestoreConfig'
)
$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest
$xamppRoot = 'C:\Program Files\xampp'
$backupRoot = 'D:\DEV\htdocs\gramlyze.loc\storage\app\seo-m9-4-local\backups'
$runtime = Join-Path $xamppRoot 'php-8.5.10-nts-gramlyze'
$module = Join-Path $xamppRoot 'apache\modules\mod_fcgid.so'
$include = Join-Path $xamppRoot 'apache\conf\extra\gramlyze-fastcgi.conf'
$configPaths = @(
    (Join-Path $xamppRoot 'apache\conf\httpd.conf'),
    (Join-Path $xamppRoot 'apache\conf\extra\httpd-vhosts.conf'),
    (Join-Path $xamppRoot 'apache\conf\extra\httpd-ssl.conf')
)

function Assert-NoReparse([string] $Path, [switch] $Tree) {
    $absolute = [IO.Path]::GetFullPath($Path)
    $cursor = $absolute
    while ($cursor) {
        if (Test-Path -LiteralPath $cursor) {
            if ((Get-Item -LiteralPath $cursor -Force).Attributes -band [IO.FileAttributes]::ReparsePoint) {
                throw "reparse-point-refused:$cursor"
            }
        }
        $cursor = [IO.Path]::GetDirectoryName($cursor)
    }
    if ($Tree -and (Test-Path -LiteralPath $absolute)) {
        foreach ($item in Get-ChildItem -LiteralPath $absolute -Force -Recurse) {
            if ($item.Attributes -band [IO.FileAttributes]::ReparsePoint) { throw "reparse-point-refused:$($item.FullName)" }
        }
    }
}

$principal = [Security.Principal.WindowsPrincipal]::new([Security.Principal.WindowsIdentity]::GetCurrent())
if (-not $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) { throw 'elevated-token-required' }
$BackupDirectory = [IO.Path]::GetFullPath($BackupDirectory).TrimEnd('\')
if ([IO.Path]::GetDirectoryName($BackupDirectory) -ine $backupRoot) { throw 'unexpected-backup-directory' }
Assert-NoReparse $BackupDirectory -Tree
$manifest = Get-Content -Raw -LiteralPath (Join-Path $BackupDirectory 'manifest.json') | ConvertFrom-Json
if ($manifest.schema -ne 'gramlyze-m9-4-fastcgi-install-v2' -or $manifest.backup_directory -ine $BackupDirectory) { throw 'unexpected-manifest' }
if (@($manifest.configs).Count -ne 3) { throw 'unexpected-config-count' }
foreach ($entry in $manifest.configs) {
    if ($entry.path -notin $configPaths) { throw 'unexpected-config-path' }
    if ($entry.backup_name -cne [IO.Path]::GetFileName($entry.path)) { throw 'unexpected-backup-name' }
    Assert-NoReparse $entry.path
    $saved = Join-Path $BackupDirectory $entry.backup_name
    if ((Get-FileHash -LiteralPath $saved -Algorithm SHA256).Hash -ine $entry.before_sha256) { throw "backup-hash-mismatch:$saved" }
    $currentHash = (Get-FileHash -LiteralPath $entry.path -Algorithm SHA256).Hash
    if ($currentHash -ine $entry.before_sha256 -and $currentHash -ine $entry.installed_sha256) { throw "later-config-change-refused:$($entry.path)" }
}

if ($Phase -eq 'RestoreConfig') {
    foreach ($entry in $manifest.configs) {
        Copy-Item -LiteralPath (Join-Path $BackupDirectory $entry.backup_name) -Destination $entry.path -Force
        if ((Get-FileHash -LiteralPath $entry.path -Algorithm SHA256).Hash -ine $entry.before_sha256) { throw 'restore-verification-failed' }
    }
    & (Join-Path $xamppRoot 'apache\bin\httpd.exe') -d (Join-Path $xamppRoot 'apache') -f $configPaths[0] -t
    if ($LASTEXITCODE -ne 0) { throw 'restored-apache-syntax-failed' }
    Write-Output 'Configuration restored. Restart the previously identified XAMPP Apache instance separately; then run -Phase Cleanup.'
    exit 0
}

foreach ($entry in $manifest.configs) {
    if ((Get-FileHash -LiteralPath $entry.path -Algorithm SHA256).Hash -ine $entry.before_sha256) { throw 'restore-config-before-cleanup' }
}
if (Get-CimInstance Win32_Process | Where-Object { $_.ExecutablePath -and $_.ExecutablePath.StartsWith($runtime + '\', [StringComparison]::OrdinalIgnoreCase) }) {
    throw 'runtime-worker-still-active; restart-the-identified-Apache-first'
}
foreach ($process in Get-Process -Name httpd -ErrorAction SilentlyContinue) {
    # Fail closed if a process cannot be inspected; never kill unrelated Apache.
    foreach ($loaded in $process.Modules) {
        if ($loaded.FileName -ieq $module) { throw 'fcgid-module-still-loaded; controlled-Apache-stop-start-required-before-cleanup' }
    }
}
foreach ($target in @($include, $module, $runtime)) {
    $absolute = [IO.Path]::GetFullPath($target)
    if ($absolute -notin @($include, $module, $runtime) -or -not $absolute.StartsWith($xamppRoot + '\', [StringComparison]::OrdinalIgnoreCase)) {
        throw 'cleanup-target-outside-explicit-scope'
    }
    Assert-NoReparse $absolute -Tree
}
foreach ($entry in $manifest.created_files) {
    if ($entry.path -notin @($include, $module, (Join-Path $runtime 'php.ini'), (Join-Path $runtime 'extras\ssl\cacert.pem'))) { throw 'unexpected-created-file' }
    if ((Test-Path -LiteralPath $entry.path) -and (Get-FileHash -LiteralPath $entry.path -Algorithm SHA256).Hash -ine $entry.sha256) { throw "later-created-file-change-refused:$($entry.path)" }
}
foreach ($target in @($include, $module)) {
    if (Test-Path -LiteralPath $target) { Remove-Item -LiteralPath $target -Force }
}
if (Test-Path -LiteralPath $runtime) { Remove-Item -LiteralPath $runtime -Recurse -Force }
Write-Output 'Only M9.4 added files removed. Backup retained; no processes stopped by this script.'
