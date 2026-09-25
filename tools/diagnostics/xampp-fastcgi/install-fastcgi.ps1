[CmdletBinding()]
param([switch] $AuditOnly)
$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest

# Deliberately fixed scope and versions. This installer performs no download,
# dependency/DB operation, service installation, process restart or ACL change.
$xamppRoot = 'C:\Program Files\xampp'
$workspace = 'D:\DEV\htdocs\gramlyze.loc'
$stage = Join-Path $workspace 'storage\app\seo-m9-4-local\staging'
$sourceDirectory = Join-Path $workspace 'tools\diagnostics\xampp-fastcgi'
$phpArchive = Join-Path $stage 'php-8.5.10-nts-Win32-vs17-x64.zip'
$moduleArchive = Join-Path $stage 'mod_fcgid-2.3.10-win64-VS17.zip'
$moduleSource = Join-Path $stage 'mod_fcgid-2.3.10-win64-VS17\mod_fcgid.so'
$caSource = Join-Path $stage 'cacert.pem'
$caSha256 = 'f66dff1bdf8f96060b8177976f8b7d9254bc89bc4db933d769f7384d28480bc9'
$template = Join-Path $sourceDirectory 'gramlyze-fastcgi.conf'
$rollbackScript = Join-Path $sourceDirectory 'rollback-fastcgi.ps1'
$iniSource = Join-Path $xamppRoot 'php\php.ini'
$phpTarget = Join-Path $xamppRoot 'php-8.5.10-nts-gramlyze'
$caTarget = Join-Path $phpTarget 'extras\ssl\cacert.pem'
$moduleTarget = Join-Path $xamppRoot 'apache\modules\mod_fcgid.so'
$includeTarget = Join-Path $xamppRoot 'apache\conf\extra\gramlyze-fastcgi.conf'
$httpdConf = Join-Path $xamppRoot 'apache\conf\httpd.conf'
$vhostsConf = Join-Path $xamppRoot 'apache\conf\extra\httpd-vhosts.conf'
$sslConf = Join-Path $xamppRoot 'apache\conf\extra\httpd-ssl.conf'
$utf8 = [Text.UTF8Encoding]::new($false)
$createdTargets = [Collections.Generic.List[string]]::new()
$changedConfigs = [Collections.Generic.List[string]]::new()
$backupDirectory = $null
$manifest = $null

function Get-Sha256([string] $Path) { (Get-FileHash -LiteralPath $Path -Algorithm SHA256).Hash.ToLowerInvariant() }
function Assert-Hash([string] $Path, [string] $Expected) {
    if ((Get-Sha256 $Path) -ine $Expected) { throw "sha256-mismatch:$Path" }
}
function Assert-NoReparse([string] $Path, [switch] $Tree) {
    $cursor = [IO.Path]::GetFullPath($Path)
    while ($cursor) {
        if ((Test-Path -LiteralPath $cursor) -and ((Get-Item -LiteralPath $cursor -Force).Attributes -band [IO.FileAttributes]::ReparsePoint)) {
            throw "reparse-point-refused:$cursor"
        }
        $cursor = [IO.Path]::GetDirectoryName($cursor)
    }
    if ($Tree -and (Test-Path -LiteralPath $Path)) {
        foreach ($item in Get-ChildItem -LiteralPath $Path -Recurse -Force) {
            if ($item.Attributes -band [IO.FileAttributes]::ReparsePoint) { throw "reparse-point-refused:$($item.FullName)" }
        }
    }
}
function Write-Json([string] $Path, $Value) {
    [IO.File]::WriteAllText($Path, ($Value | ConvertTo-Json -Depth 8), $utf8)
}
function Add-VhostInclude([string] $Text, [string] $Port) {
    $pattern = '(?ms)^[ \t]*<VirtualHost[ \t]+\*:' + $Port + '>[\s\S]*?^[ \t]*</VirtualHost>'
    $blocks = @([regex]::Matches($Text, $pattern) | Where-Object {
        $_.Value -match ('(?m)^[ \t]*ServerName[ \t]+gramlyze\.loc(?::' + $Port + ')?[ \t]*\r?$')
    })
    if ($blocks.Count -ne 1) { throw "unexpected-gramlyze-vhost-count:$Port" }
    $block = $blocks[0]
    if ($block.Value -match '(?i)Fcgid|gramlyze-fastcgi|SetHandler|ProxyPass') { throw "existing-handler-requires-review:$Port" }
    if ($block.Value -notmatch '(?mi)^[ \t]*DocumentRoot[ \t]+"D:[/\\]DEV[/\\]htdocs[/\\]gramlyze\.loc[/\\]public"[ \t]*\r?$') {
        throw "unexpected-document-root:$Port"
    }
    $newline = if ($block.Value.Contains("`r`n")) { "`r`n" } else { "`n" }
    $addition = '    # M9.4: isolated PHP handler for this existing Gramlyze vhost only.' + $newline +
        '    Include "C:/Program Files/xampp/apache/conf/extra/gramlyze-fastcgi.conf"' + $newline
    $replacement = [regex]::Replace($block.Value, '(?m)^[ \t]*(?=</VirtualHost>)', $addition)
    $Text.Substring(0, $block.Index) + $replacement + $Text.Substring($block.Index + $block.Length)
}
function Set-IniValue([string] $Text, [string] $Name, [string] $Value) {
    $pattern = '(?m)^[ \t]*' + [regex]::Escape($Name) + '[ \t]*=[^\r\n]*'
    $line = $Name + ' = ' + $Value
    if ([regex]::IsMatch($Text, $pattern)) {
        return [regex]::Replace($Text, $pattern, [Text.RegularExpressions.MatchEvaluator]{ param($match) $line })
    }
    return $Text.TrimEnd() + "`r`n" + $line + "`r`n"
}
function Restore-ThisAttempt {
    # Called only before any controlled restart. Restore only files we touched.
    foreach ($path in $changedConfigs) {
        $entry = @($manifest.configs | Where-Object { $_.path -ieq $path })[0]
        Assert-NoReparse $path
        $saved = Join-Path $backupDirectory $entry.backup_name
        Assert-Hash $saved $entry.before_sha256
        Copy-Item -LiteralPath $saved -Destination $path -Force
        Assert-Hash $path $entry.before_sha256
    }
    foreach ($path in $createdTargets) {
        $absolute = [IO.Path]::GetFullPath($path)
        if ($absolute -notin @($phpTarget, $moduleTarget, $includeTarget) -or
            -not $absolute.StartsWith($xamppRoot + '\', [StringComparison]::OrdinalIgnoreCase)) { throw 'rollback-target-outside-scope' }
        Assert-NoReparse $absolute -Tree
    }
    foreach ($path in $createdTargets) {
        if (Test-Path -LiteralPath $path) {
            if ($path -ieq $phpTarget) { Remove-Item -LiteralPath $path -Recurse -Force }
            else { Remove-Item -LiteralPath $path -Force }
        }
    }
    if ($changedConfigs.Count -gt 0) {
        & (Join-Path $xamppRoot 'apache\bin\httpd.exe') -d (Join-Path $xamppRoot 'apache') -f $httpdConf -t
        if ($LASTEXITCODE -ne 0) { throw 'restored-apache-syntax-failed' }
    }
}

try {
    foreach ($path in @($phpArchive, $moduleArchive, $moduleSource, $caSource, $template, $rollbackScript, $iniSource, $httpdConf, $vhostsConf, $sslConf)) {
        if (-not (Test-Path -LiteralPath $path -PathType Leaf)) { throw "required-file-missing:$path" }
        Assert-NoReparse $path
    }
    foreach ($path in @($phpTarget, $moduleTarget, $includeTarget)) {
        Assert-NoReparse $path
        if (Test-Path -LiteralPath $path) { throw "target-already-exists; inspect-state-before-continuing:$path" }
    }
    Assert-Hash $phpArchive '22ec430195984d233eb9e62c637a945bbcda06efca2f392d9d96d62c6acd34f8'
    Assert-Hash $moduleArchive 'cf3ded8953863c68fc522ee48f516565e67e18b180b9e5b147668c04fa5dc46d'
    Assert-Hash $moduleSource '47d2a1bccff5f2560a5b1534af4fa3c9534fd471e2e4d8b160c4a93c58e1af4d'
    Assert-Hash $caSource $caSha256
    Add-Type -AssemblyName System.IO.Compression.FileSystem
    $archive = [IO.Compression.ZipFile]::OpenRead($phpArchive)
    try {
        foreach ($entry in $archive.Entries) {
            $destination = [IO.Path]::GetFullPath((Join-Path $phpTarget $entry.FullName))
            if (-not $destination.StartsWith($phpTarget + '\', [StringComparison]::OrdinalIgnoreCase)) { throw 'archive-entry-outside-runtime' }
            if ($entry.FullName -match '(?i)(^|[/\\])php8ts\.dll$') { throw 'thread-safe-library-in-nts-archive' }
        }
    } finally { $archive.Dispose() }

    $originals = [ordered]@{}
    foreach ($path in @($httpdConf, $vhostsConf, $sslConf)) { $originals[$path] = [IO.File]::ReadAllText($path) }
    $httpd = $originals[$httpdConf]
    $needle = '#LoadModule proxy_fcgi_module modules/mod_proxy_fcgi.so'
    if ([regex]::Matches($httpd, [regex]::Escape($needle)).Count -ne 1 -or $httpd -match '(?mi)^[ \t]*LoadModule[ \t]+fcgid_module\b') {
        throw 'unexpected-httpd-module-state'
    }
    $desired = [ordered]@{}
    $desired[$httpdConf] = $httpd.Replace($needle, "LoadModule fcgid_module modules/mod_fcgid.so`r`n" + $needle)
    $desired[$vhostsConf] = Add-VhostInclude $originals[$vhostsConf] '80'
    $desired[$sslConf] = Add-VhostInclude $originals[$sslConf] '443'
    $ini = [IO.File]::ReadAllText($iniSource)
    if ($ini -match '(?mi)^[ \t]*(?:zend_)?extension[ \t]*=[ \t]*["'']?[A-Za-z]:') { throw 'absolute-extension-source-requires-review' }
    $ini = Set-IniValue $ini 'extension_dir' '"C:/Program Files/xampp/php-8.5.10-nts-gramlyze/ext"'
    $ini = Set-IniValue $ini 'error_log' '"C:/Program Files/xampp/php-8.5.10-nts-gramlyze/logs/php_error_log"'
    $ini = Set-IniValue $ini 'cgi.fix_pathinfo' '0'
    $ini = Set-IniValue $ini 'curl.cainfo' '"C:/Program Files/xampp/php-8.5.10-nts-gramlyze/extras/ssl/cacert.pem"'
    $ini = Set-IniValue $ini 'openssl.cafile' '"C:/Program Files/xampp/php-8.5.10-nts-gramlyze/extras/ssl/cacert.pem"'
    # Preserve sessions; use the reviewed curl.se Mozilla CA bundle for OpenSSL.
    # This does not import certificates into Windows or weaken verification.
    $principal = [Security.Principal.WindowsPrincipal]::new([Security.Principal.WindowsIdentity]::GetCurrent())
    $elevated = $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
    $audit = [ordered]@{
        installer_sha256 = Get-Sha256 $PSCommandPath
        template_sha256 = Get-Sha256 $template
        rollback_sha256 = Get-Sha256 $rollbackScript
        source_ini_sha256 = Get-Sha256 $iniSource
        ca_source = 'https://curl.se/ca/cacert.pem'
        ca_source_sha256 = Get-Sha256 $caSource
        ca_target = $caTarget
        elevated = $elevated
        modifies = @($desired.Keys)
        creates = @($phpTarget, $moduleTarget, $includeTarget)
        starts_or_restarts = @()
    }
    if ($AuditOnly) { $audit | ConvertTo-Json -Depth 5; exit 0 }
    if (-not $elevated) { throw 'elevated-token-required' }

    # Fresh, unique, exact before-image directly before the first XAMPP mutation.
    $backupRoot = Join-Path $workspace 'storage\app\seo-m9-4-local\backups'
    Assert-NoReparse $backupRoot
    $backupDirectory = Join-Path $backupRoot ('install-' + (Get-Date -Format 'yyyyMMdd-HHmmss-fff') + '-' + [guid]::NewGuid().ToString('N').Substring(0, 8))
    New-Item -ItemType Directory -Path $backupDirectory | Out-Null
    $configEntries = @()
    foreach ($path in $desired.Keys) {
        if ([IO.File]::ReadAllText($path) -cne $originals[$path]) { throw "config-changed-during-preflight:$path" }
        $beforeHash = Get-Sha256 $path
        $name = [IO.Path]::GetFileName($path)
        Copy-Item -LiteralPath $path -Destination (Join-Path $backupDirectory $name)
        Assert-Hash (Join-Path $backupDirectory $name) $beforeHash
        $sha = [Security.Cryptography.SHA256]::Create()
        try { $afterHash = [BitConverter]::ToString($sha.ComputeHash($utf8.GetBytes($desired[$path]))).Replace('-', '').ToLowerInvariant() }
        finally { $sha.Dispose() }
        $configEntries += [ordered]@{ path = $path; backup_name = $name; before_sha256 = $beforeHash; installed_sha256 = $afterHash }
    }
    $manifest = [ordered]@{
        schema = 'gramlyze-m9-4-fastcgi-install-v2'
        backup_directory = $backupDirectory
        created_at_utc = [DateTime]::UtcNow.ToString('o')
        audit = $audit
        configs = $configEntries
        created_files = @()
        success = $false
    }
    Write-Json (Join-Path $backupDirectory 'manifest.json') $manifest
    Copy-Item -LiteralPath $rollbackScript -Destination (Join-Path $backupDirectory 'rollback-fastcgi.ps1')

    # Record ownership before operations that could leave a partial artifact.
    $createdTargets.Add($phpTarget)
    Expand-Archive -LiteralPath $phpArchive -DestinationPath $phpTarget
    foreach ($name in @('logs', 'conf.d')) { New-Item -ItemType Directory -Path (Join-Path $phpTarget $name) | Out-Null }
    New-Item -ItemType Directory -Force -Path (Join-Path $phpTarget 'extras\ssl') | Out-Null
    Copy-Item -LiteralPath $caSource -Destination $caTarget
    Assert-Hash $caTarget $caSha256
    $iniPath = Join-Path $phpTarget 'php.ini'
    [IO.File]::WriteAllText($iniPath, $ini, $utf8)
    $php = Join-Path $phpTarget 'php.exe'
    $phpCgi = Join-Path $phpTarget 'php-cgi.exe'
    if (-not (Test-Path -LiteralPath $phpCgi)) { throw 'php-cgi-missing-after-extract' }
    $savedScanDir = $env:PHP_INI_SCAN_DIR
    try {
        $env:PHP_INI_SCAN_DIR = Join-Path $phpTarget 'conf.d'
        $runtimeInfo = & $php -c $iniPath -r 'echo json_encode(["version"=>PHP_VERSION,"zts"=>PHP_ZTS,"bits"=>PHP_INT_SIZE*8,"extensions"=>get_loaded_extensions(),"curl"=>function_exists("curl_init"),"tls"=>curl_version()["ssl_version"],"curl_ca"=>ini_get("curl.cainfo"),"openssl_ca"=>ini_get("openssl.cafile"),"ca_readable"=>is_readable(ini_get("curl.cainfo"))]);'
        if ($LASTEXITCODE -ne 0) { throw 'nts-runtime-check-failed' }
        $runtimeCheck = $runtimeInfo | ConvertFrom-Json
        if ($runtimeCheck.version -ne '8.5.10' -or $runtimeCheck.zts -ne 0 -or $runtimeCheck.bits -ne 64 -or -not $runtimeCheck.curl) { throw 'unexpected-runtime-build' }
        $requiredExtensions = @('bz2', 'curl', 'fileinfo', 'gd', 'gettext', 'intl', 'mbstring', 'mysqli', 'openssl', 'pdo_mysql', 'pdo_sqlite', 'sqlite3', 'zip')
        foreach ($extension in $requiredExtensions) {
            if ($extension -notin $runtimeCheck.extensions) { throw "required-extension-not-loaded:$extension" }
        }
        if ($runtimeCheck.tls -cne 'OpenSSL/3.5.7') { throw 'unexpected-curl-tls-backend' }
        if ([IO.Path]::GetFullPath($runtimeCheck.curl_ca) -ine $caTarget -or
            [IO.Path]::GetFullPath($runtimeCheck.openssl_ca) -ine $caTarget -or
            -not $runtimeCheck.ca_readable) { throw 'runtime-ca-configuration-check-failed' }
        $cgiModules = & $phpCgi -c $iniPath -m
        if ($LASTEXITCODE -ne 0) { throw 'nts-cgi-extension-check-failed' }
        foreach ($extension in $requiredExtensions) {
            if ($extension -notin $cgiModules) { throw "required-cgi-extension-not-loaded:$extension" }
        }
    } finally { $env:PHP_INI_SCAN_DIR = $savedScanDir }

    $createdTargets.Add($moduleTarget)
    Copy-Item -LiteralPath $moduleSource -Destination $moduleTarget
    Assert-Hash $moduleTarget '47d2a1bccff5f2560a5b1534af4fa3c9534fd471e2e4d8b160c4a93c58e1af4d'
    $createdTargets.Add($includeTarget)
    Copy-Item -LiteralPath $template -Destination $includeTarget
    foreach ($entry in $configEntries) {
        Assert-Hash $entry.path $entry.before_sha256
        $changedConfigs.Add($entry.path)
        [IO.File]::WriteAllText($entry.path, $desired[$entry.path], $utf8)
        Assert-Hash $entry.path $entry.installed_sha256
    }
    $manifest.created_files = @($moduleTarget, $includeTarget, $iniPath, $caTarget) | ForEach-Object { [ordered]@{ path = $_; sha256 = Get-Sha256 $_ } }
    Write-Json (Join-Path $backupDirectory 'manifest.json') $manifest
    & (Join-Path $xamppRoot 'apache\bin\httpd.exe') -d (Join-Path $xamppRoot 'apache') -f $httpdConf -t
    if ($LASTEXITCODE -ne 0) { throw 'apache-syntax-check-failed' }
    $manifest.success = $true
    $manifest.runtime = $runtimeCheck
    Write-Json (Join-Path $backupDirectory 'manifest.json') $manifest
    Write-Json (Join-Path $stage 'install-fastcgi-result.json') ([ordered]@{
        success = $true; backup_directory = $backupDirectory; installer_sha256 = $audit.installer_sha256
        message = 'Installed and syntax validated. Controlled restart of the identified Apache is a separate action.'
    })
    Write-Output "Installed and Syntax OK. Fresh backup: $backupDirectory. Apache has not been restarted."
    exit 0
} catch {
    $failure = $_.Exception.Message
    try { Restore-ThisAttempt } catch { $failure += '; rollback-failed:' + $_.Exception.Message }
    if ($backupDirectory) {
        Write-Json (Join-Path $backupDirectory 'failure.json') ([ordered]@{ success = $false; error = $failure })
    }
    throw $failure
}
