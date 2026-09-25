[CmdletBinding()]
param([Parameter(Mandatory)][string] $InstalledBackupDirectory)
$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest
# Resume the already-owned M9.4 runtime after restoring the original handler.
# No extraction, runtime/CLI modification, deletion, service or restart action.
$xamppRoot = 'C:\Program Files\xampp'
$workspace = 'D:\DEV\htdocs\gramlyze.loc'
$sourceDirectory = Join-Path $workspace 'tools\diagnostics\xampp-fastcgi'
$backupRoot = Join-Path $workspace 'storage\app\seo-m9-4-local\backups'
$expectedInstall = Join-Path $backupRoot 'install-20260913-003246-939-7cdee6b7'
if ([IO.Path]::GetFullPath($InstalledBackupDirectory).TrimEnd('\') -ine $expectedInstall) { throw 'unexpected-existing-install-backup' }
$installer = Join-Path $sourceDirectory 'install-fastcgi.ps1'
$template = Join-Path $sourceDirectory 'gramlyze-fastcgi.conf'
$rollback = Join-Path $sourceDirectory 'rollback-fastcgi.ps1'
$include = Join-Path $xamppRoot 'apache\conf\extra\gramlyze-fastcgi.conf'
$httpd = Join-Path $xamppRoot 'apache\conf\httpd.conf'
$vhosts = Join-Path $xamppRoot 'apache\conf\extra\httpd-vhosts.conf'
$ssl = Join-Path $xamppRoot 'apache\conf\extra\httpd-ssl.conf'
$owned = @((Join-Path $xamppRoot 'apache\modules\mod_fcgid.so'), $include,
    (Join-Path $xamppRoot 'php-8.5.10-nts-gramlyze\php.ini'),
    (Join-Path $xamppRoot 'php-8.5.10-nts-gramlyze\extras\ssl\cacert.pem'))
$utf8 = [Text.UTF8Encoding]::new($false)
$changed = [Collections.Generic.List[string]]::new()
$freshBackup = $null

# Reuse only five reviewed function definitions from the pinned installer AST;
# its top-level install body is never invoked or dot-sourced.
if ((Get-FileHash -LiteralPath $installer -Algorithm SHA256).Hash -ine '215e2a3e3c88b244bde3f916c157e9506dd58e5488cb225ced8b4315d643d7e0') { throw 'reviewed-installer-hash-mismatch' }
$tokens = $null; $parseErrors = $null
$ast = [Management.Automation.Language.Parser]::ParseFile($installer, [ref] $tokens, [ref] $parseErrors)
if ($parseErrors.Count) { throw 'reviewed-installer-parse-failed' }
$functions = @($ast.FindAll({ param($node) $node -is [Management.Automation.Language.FunctionDefinitionAst] -and
    $node.Name -in @('Get-Sha256', 'Assert-Hash', 'Assert-NoReparse', 'Write-Json', 'Add-VhostInclude') }, $false))
if ($functions.Count -ne 5) { throw 'reviewed-function-count-mismatch' }
foreach ($definition in $functions) { . ([scriptblock]::Create($definition.Extent.Text)) }

try {
    $principal = [Security.Principal.WindowsPrincipal]::new([Security.Principal.WindowsIdentity]::GetCurrent())
    if (-not $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) { throw 'elevated-token-required' }
    foreach ($path in @($expectedInstall, $template, $rollback, $installer) + $owned + @($httpd, $vhosts, $ssl)) { Assert-NoReparse $path }
    $oldManifestPath = Join-Path $expectedInstall 'manifest.json'
    Assert-Hash $oldManifestPath '483ee8eed2ac08c75a62a5ad45fd0e5f9dafa01c80435ea4ee0f345242eb44cb'
    Assert-Hash $template '1e0bc2b3a3ee79d3a3d4c15dbb844f67dec95cdc2b9cefcc653c5bd734a52b81'
    Assert-Hash $rollback '6f9c2e255658d273bd8a1ae0730fef2f58af4defe1798706a3d5115352f16c5f'
    $old = Get-Content -Raw -LiteralPath $oldManifestPath | ConvertFrom-Json
    if (-not $old.success -or $old.schema -ne 'gramlyze-m9-4-fastcgi-install-v2' -or $old.backup_directory -ine $expectedInstall) { throw 'unexpected-owned-install-manifest' }
    if (@($old.configs).Count -ne 3 -or @($old.created_files).Count -ne 4) { throw 'unexpected-owned-install-file-count' }
    foreach ($entry in $old.configs) {
        if ($entry.path -notin @($httpd, $vhosts, $ssl) -or $entry.backup_name -cne [IO.Path]::GetFileName($entry.path)) { throw 'unexpected-original-config-path' }
        Assert-Hash $entry.path $entry.before_sha256
        Assert-Hash (Join-Path $expectedInstall $entry.backup_name) $entry.before_sha256
    }
    foreach ($entry in $old.created_files) {
        if ($entry.path -notin $owned) { throw 'unexpected-owned-file' }
        Assert-Hash $entry.path $entry.sha256
    }
    $desired = [ordered]@{}
    $text = [IO.File]::ReadAllText($httpd)
    $needle = '#LoadModule proxy_fcgi_module modules/mod_proxy_fcgi.so'
    if ([regex]::Matches($text, [regex]::Escape($needle)).Count -ne 1 -or $text -match '(?mi)^\s*LoadModule\s+fcgid_module\b') { throw 'unexpected-module-directive-state' }
    $desired[$httpd] = $text.Replace($needle, "LoadModule fcgid_module modules/mod_fcgid.so`r`n" + $needle)
    $desired[$vhosts] = Add-VhostInclude ([IO.File]::ReadAllText($vhosts)) '80'
    $desired[$ssl] = Add-VhostInclude ([IO.File]::ReadAllText($ssl)) '443'
    # Fresh before-images of exactly four files this attempt will change.
    $freshBackup = Join-Path $backupRoot ('apply-' + (Get-Date -Format 'yyyyMMdd-HHmmss-fff') + '-' + [guid]::NewGuid().ToString('N').Substring(0, 8))
    Assert-NoReparse $freshBackup
    New-Item -ItemType Directory -Path $freshBackup | Out-Null
    $beforeFiles = @()
    foreach ($path in @($httpd, $vhosts, $ssl, $include)) {
        $oldEntry = @($old.configs + $old.created_files | Where-Object { $_.path -ieq $path })[0]
        $expectedHash = if ($path -ieq $include) { $oldEntry.sha256 } else { $oldEntry.before_sha256 }
        Assert-Hash $path $expectedHash
        $backupName = [IO.Path]::GetFileName($path)
        Copy-Item -LiteralPath $path -Destination (Join-Path $freshBackup $backupName)
        Assert-Hash (Join-Path $freshBackup $backupName) $expectedHash
        $beforeFiles += [ordered]@{ path = $path; backup_name = $backupName; before_sha256 = $expectedHash }
    }
    Copy-Item -LiteralPath $rollback -Destination (Join-Path $freshBackup 'rollback-fastcgi.ps1')
    $manifest = [ordered]@{
        schema = 'gramlyze-m9-4-fastcgi-install-v2'; backup_directory = $freshBackup
        created_at_utc = [DateTime]::UtcNow.ToString('o'); inherited_owned_install = $expectedInstall
        audit = [ordered]@{ apply_script_sha256 = Get-Sha256 $PSCommandPath; template_sha256 = Get-Sha256 $template; rollback_sha256 = Get-Sha256 $rollback; modifies = @($httpd, $vhosts, $ssl, $include); starts_or_restarts = @() }
        configs = @($old.configs); additional_before_files = @($beforeFiles | Where-Object { $_.path -ieq $include })
        created_files = @($old.created_files); runtime = $old.runtime; success = $false
    }
    Write-Json (Join-Path $freshBackup 'manifest.json') $manifest
    foreach ($entry in $beforeFiles) {
        Assert-Hash $entry.path $entry.before_sha256
        $changed.Add($entry.path)
        if ($entry.path -ieq $include) { Copy-Item -LiteralPath $template -Destination $include -Force }
        else { [IO.File]::WriteAllText($entry.path, $desired[$entry.path], $utf8) }
    }
    foreach ($entry in $manifest.configs) { Assert-Hash $entry.path $entry.installed_sha256 }
    $manifest.created_files = @($owned | ForEach-Object { [ordered]@{ path = $_; sha256 = Get-Sha256 $_ } })
    Write-Json (Join-Path $freshBackup 'manifest.json') $manifest
    & (Join-Path $xamppRoot 'apache\bin\httpd.exe') -d (Join-Path $xamppRoot 'apache') -f $httpd -t
    if ($LASTEXITCODE -ne 0) { throw 'apache-syntax-check-failed' }
    $manifest.success = $true
    Write-Json (Join-Path $freshBackup 'manifest.json') $manifest
    Write-Output "Prepared handler applied and Syntax OK; no restart. Fresh four-file backup: $freshBackup"
} catch {
    $failure = $_.Exception.Message
    try {
        foreach ($path in $changed) {
            Assert-NoReparse $path
            $entry = @($beforeFiles | Where-Object { $_.path -ieq $path })[0]
            $saved = Join-Path $freshBackup $entry.backup_name
            Assert-Hash $saved $entry.before_sha256
            Copy-Item -LiteralPath $saved -Destination $path -Force
            Assert-Hash $path $entry.before_sha256
        }
        if ($changed.Count) {
            & (Join-Path $xamppRoot 'apache\bin\httpd.exe') -d (Join-Path $xamppRoot 'apache') -f $httpd -t
            if ($LASTEXITCODE -ne 0) { throw 'restored-apache-syntax-failed' }
        }
    } catch { $failure += '; four-file-restore-failed:' + $_.Exception.Message }
    if ($freshBackup) { Write-Json (Join-Path $freshBackup 'failure.json') ([ordered]@{ success = $false; error = $failure }) }
    throw $failure
}
