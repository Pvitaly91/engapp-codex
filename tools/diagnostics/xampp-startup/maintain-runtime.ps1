[CmdletBinding()]
param(
    [Parameter(Mandatory)][ValidateSet('Prepare','Stop','StopCurrent','Wait','Apply','Restore','Start','Status')][string] $Phase,
    [string] $RunDirectory,
    [string] $IsolationEvidence,
    [string] $CertificateEvidence,
    [string] $StoppedBaselineFrom,
    [ValidatePattern('^[a-z0-9-]{1,32}$')][string] $LaunchLabel = 'acceptance',
    [Parameter(Mandatory)][ValidatePattern('^[^\\]+\\admin$')][string] $ExpectedOwner
)
$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest
. (Join-Path $PSScriptRoot 'runtime-transaction.ps1')
Add-Type -Path (Join-Path $PSScriptRoot 'startup-native.cs')
$xampp = 'C:\Program Files\xampp'
$apache = Join-Path $xampp 'apache\bin\httpd.exe'
$nts = Join-Path $xampp 'php-8.5.10-nts-gramlyze\php-cgi.exe'
$private = 'D:\DEV\htdocs\gramlyze.loc\storage\app\seo-m9-4-startup-local'
$zip = 'D:\DEV\htdocs\gramlyze.loc\storage\app\seo-m9-2-local\php-8.5.10-ts-x64\php-8.5.10-Win32-vs17-x64.zip'
$zipHash = 'a6bc8b2f3d7bfb397ccb973db2f959e61e530e0986c9cea262dd4a317ec599d8'
$pins = @(
    @{name='libssl-3-x64.dll';before='cf0e4d008a748057a3dd638496a2c4c033a7d33ea4f4cd96f2f747a61f9d4748';after='dd76bebb8a13731a1bd047232c77299e7fa1fe9c8ef6d1563ca059473088630a'},
    @{name='libcrypto-3-x64.dll';before='8bb143f97cb31089d50f59be1846a8e163fa8ac215792e5fa51dc92a7b5152e9';after='4978b06f18c1d092e4f7c8c864cc814db2ff4535baa2de54937348ffe14aae5e'},
    @{name='libssh2.dll';before='82702e6806ca1b2ea0022c036be67744ffe172c8de67d86923011c58d5592941';after='a521fa2c78f5cb15136459ad6d5e6372112a49d920a859efab16f32fc625f4f3'}
)
$configs = @('apache_start.bat','apache/conf/httpd.conf','apache/conf/extra/httpd-xampp.conf',
    'apache/conf/extra/httpd-vhosts.conf','apache/conf/extra/httpd-ssl.conf',
    'apache/conf/extra/gramlyze-fastcgi.conf','php/php.ini','php-8.5.10-nts-gramlyze/php.ini')
foreach ($pin in $pins) { $pin.relative = 'apache/bin/'+$pin.name }
$identity = [Security.Principal.WindowsIdentity]::GetCurrent()
if ($identity.Name -ine $ExpectedOwner -or -not ([Security.Principal.WindowsPrincipal]::new($identity)).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) { throw 'Expected named elevated administrator required' }
foreach ($path in @($xampp,$private,$zip,$PSScriptRoot)) { Assert-StartupPath $path }

function Save-Json([string] $Name, $Value) {
    $path = Join-Path $script:run $Name
    if (Test-Path -LiteralPath $path) { throw 'Evidence exists; refusing overwrite' }
    [IO.File]::WriteAllText($path, ($Value | ConvertTo-Json -Depth 12), [Text.UTF8Encoding]::new($false))
}
function Get-Identity($Process, [switch] $AllowVanished) {
    try { $owner = Invoke-CimMethod -InputObject $Process -MethodName GetOwner }
    catch {
        $failure = $_.Exception
        while ($failure -and $failure -isnot [Microsoft.Management.Infrastructure.CimException]) { $failure = $failure.InnerException }
        $notFound = $failure -and ($failure.NativeErrorCode -eq [Microsoft.Management.Infrastructure.NativeErrorCode]::NotFound -or $failure.HResult -eq -2147217406)
        if (-not $AllowVanished -or -not $notFound) { throw }
        if (Get-CimInstance Win32_Process -Filter ('ProcessId = '+[int]$Process.ProcessId)) { throw }
        return
    }
    if ($owner.ReturnValue -ne 0) { throw 'Cannot read process owner' }
    [pscustomobject]@{pid=[int]$Process.ProcessId;parent=[int]$Process.ParentProcessId;
        created=$Process.CreationDate.ToUniversalTime().ToString('o');exe=$Process.ExecutablePath;
        command=$Process.CommandLine;owner=$owner.Domain+'\'+$owner.User}
}
function Get-Targets {
    @(Get-CimInstance Win32_Process -Filter "Name = 'httpd.exe' OR Name = 'php-cgi.exe'" |
        Where-Object { $_.ExecutablePath -ieq $apache -or $_.ExecutablePath -ieq $nts } |
        ForEach-Object { Get-Identity $_ -AllowVanished })
}
function Get-DatabaseIdentity {
    @(Get-CimInstance Win32_Process -Filter "Name = 'mysqld.exe'" | ForEach-Object {
        [pscustomobject]@{pid=[int]$_.ProcessId;created=$_.CreationDate.ToUniversalTime().ToString('o');exe=$_.ExecutablePath}
    })
}
function Get-LogLength([string] $Path) {
    if (Test-Path -LiteralPath $Path -PathType Leaf) { return (Get-Item -LiteralPath $Path).Length }
    return 0
}
function Assert-Stopped {
    if (@(Get-Targets).Count) { throw 'Target Apache/PHP processes remain; no file changes or launch' }
    if (@(Get-NetTCPConnection -State Listen | Where-Object { $_.LocalPort -in @(80,443) }).Count) { throw 'Ports 80/443 occupied' }
}
function Assert-Configs {
    foreach ($relative in $configs) {
        $rows = @($script:manifest.files | Where-Object { $_.relative -ceq $relative })
        if ($rows.Count -ne 1) { throw 'Unexpected backup manifest' }
        $current = Join-Path $xampp $relative
        $backup = Join-Path $script:run ('backup/'+$relative)
        foreach ($path in @($current,$backup)) {
            Assert-StartupPath $path
            if ((Get-StartupHash $path) -ne $rows[0].sha256) { throw ('Configuration changed: '+$relative) }
        }
    }
}
function Assert-Instance($Expected) {
    $observed = Get-CimInstance Win32_Process -Filter ('ProcessId = '+[int]$Expected.pid)
    if (-not $observed) { throw 'Expected process no longer exists' }
    $actual = Get-Identity $observed
    foreach ($field in @('pid','parent','exe','command','owner')) {
        if ($actual.$field -cne $Expected.$field) { throw ('Process identity changed: '+$field) }
    }
    if (([DateTimeOffset]$actual.created).UtcDateTime -ne ([DateTimeOffset]$Expected.created).UtcDateTime) { throw 'Process creation time changed' }
}
function Check-Syntax {
    Push-Location $xampp
    try {
        $output = @(& $apache -t 2>&1 | ForEach-Object { $_.ToString() })
        if ($LASTEXITCODE -ne 0 -or ($output -join "`n") -notmatch '(?m)^Syntax OK\s*$') { throw 'Actual bat configuration syntax failed' }
        $output
    } finally { Pop-Location }
}

function Read-CertificateEvidence([string] $Path) {
    $resolved = [IO.Path]::GetFullPath($Path)
    if (-not $resolved.StartsWith($private+'\',[StringComparison]::OrdinalIgnoreCase)) { throw 'Certificate evidence must be private' }
    Assert-StartupPath $resolved
    $evidence = Get-Content -LiteralPath $resolved -Raw | ConvertFrom-Json
    if ($evidence.schema -cne 'gramlyze-local-certificate-candidate-v1' -or $evidence.complete_config_success -ne $true -or $evidence.trust_store_changed -ne $false) { throw 'Full actual-config certificate acceptance required' }
    $resultPath = [IO.Path]::GetFullPath($evidence.full_config_result_path)
    if (-not $resultPath.StartsWith($private+'\',[StringComparison]::OrdinalIgnoreCase)) { throw 'Full-config evidence must be private' }
    Assert-StartupPath $resultPath
    if ((Get-StartupHash $resultPath) -cne $evidence.full_config_result_sha256) { throw 'Full-config result changed' }
    $result = Get-Content -LiteralPath $resultPath -Raw | ConvertFrom-Json
    if ($result.schema -cne 'gramlyze-full-config-startup-v1' -or $result.success -ne $true -or
        $result.active_inputs_unchanged -ne $true -or $result.temporary_endpoints_removed -ne $true -or
        $result.certificate.sha256 -cne $evidence.candidate_cert_sha256 -or $result.certificate.key_sha256 -cne $evidence.candidate_key_sha256) { throw 'Full-config proof does not match candidate' }
    foreach ($pin in $script:pins | Where-Object { $_.relative.StartsWith('apache/bin/') }) {
        if ($result.candidate_hashes.($pin.name) -cne $pin.after) { throw 'Full-config DLL mismatch' }
    }
    foreach ($kind in @('cert','key')) {
        $candidate = [IO.Path]::GetFullPath($evidence.('candidate_'+$kind))
        if (-not $candidate.StartsWith($private+'\',[StringComparison]::OrdinalIgnoreCase)) { throw 'Candidate must remain private' }
        Assert-StartupPath $candidate
        if ($evidence.('candidate_'+$kind+'_sha256') -cnotmatch '^[a-f0-9]{64}$' -or
            $evidence.('original_'+$kind+'_sha256') -cnotmatch '^[a-f0-9]{64}$' -or
            (Get-StartupHash $candidate) -cne $evidence.('candidate_'+$kind+'_sha256')) { throw 'Certificate/key evidence hash mismatch' }
    }
    $evidence
}
function Get-CertificatePins($Evidence) {
    @(
        @{name='server.crt';relative='apache/conf/ssl.crt/server.crt';before=$Evidence.original_cert_sha256;after=$Evidence.candidate_cert_sha256;source=$Evidence.candidate_cert},
        @{name='server.key';relative='apache/conf/ssl.key/server.key';before=$Evidence.original_key_sha256;after=$Evidence.candidate_key_sha256;source=$Evidence.candidate_key}
    )
}

if ($Phase -eq 'Prepare') {
    if ($RunDirectory) { throw 'Prepare creates its own unique directory' }
    $isolationPath = [IO.Path]::GetFullPath($IsolationEvidence)
    if (-not $isolationPath.StartsWith($private+'\',[StringComparison]::OrdinalIgnoreCase)) { throw 'Isolation evidence must be private local evidence' }
    Assert-StartupPath $isolationPath
    $isolated = Get-Content -LiteralPath $isolationPath -Raw | ConvertFrom-Json
    if ($isolated.schema -cne 'gramlyze-isolated-openssl-v1' -or $isolated.success -ne $true -or
        $isolated.archive_sha256 -cne $zipHash -or $isolated.shared_ini_sha256 -cne (Get-StartupHash (Join-Path $xampp 'php/php.ini'))) { throw 'Passing matching isolation evidence required' }
    foreach ($pin in $pins) { if ($isolated.candidate_hashes.($pin.name) -cne $pin.after) { throw 'Isolated candidate mismatch' } }
    if ((Get-StartupHash $zip) -ne $zipHash) { throw 'Official source ZIP hash mismatch' }
    foreach ($pin in $pins) {
        if ((Get-StartupHash (Join-Path $xampp ('apache/bin/'+$pin.name))) -ne $pin.before) { throw 'Original Apache DLL pin mismatch' }
    }
    $certificatePath = $null
    $certificateHash = $null
    if ($CertificateEvidence) {
        $certificatePath = [IO.Path]::GetFullPath($CertificateEvidence)
        $certificate = Read-CertificateEvidence $certificatePath
        $certificateHash = Get-StartupHash $certificatePath
        $pins += @(Get-CertificatePins $certificate)
        foreach ($pin in $pins) {
            if ((Get-StartupHash (Join-Path $xampp $pin.relative)) -cne $pin.before) { throw 'Original replacement target mismatch' }
        }
    }
    $files = $configs + @($pins | ForEach-Object { $_.relative })
    $processes = @(Get-Targets)
    $parent = $null
    $child = @($null)
    $nativeParentCreated = $null
    if ($StoppedBaselineFrom) {
        Assert-Stopped
        $priorRun = [IO.Path]::GetFullPath($StoppedBaselineFrom)
        if ([IO.Path]::GetDirectoryName($priorRun) -ine $private -or [IO.Path]::GetFileName($priorRun) -notmatch '^apply-[0-9-]+-[a-f0-9]{8}$') { throw 'Unexpected stopped baseline directory' }
        Assert-StartupPath $priorRun
        $priorManifest = Get-Content -LiteralPath (Join-Path $priorRun 'manifest.json') -Raw | ConvertFrom-Json
        if ($priorManifest.schema -ne 1 -or $priorManifest.owner -ine $ExpectedOwner -or
            -not (Test-Path -LiteralPath (Join-Path $priorRun 'stop.json')) -or
            -not @(Get-ChildItem -LiteralPath $priorRun -Filter 'restore-*.json').Count) { throw 'Recorded stopped/restored baseline required' }
        foreach ($row in $priorManifest.files) {
            if ($row.relative -notin $files) { throw 'Unexpected prior backup path' }
            if ((Get-StartupHash (Join-Path $xampp $row.relative)) -cne $row.sha256 -or
                (Get-StartupHash (Join-Path $priorRun ('backup/'+$row.relative))) -cne $row.sha256) { throw 'Stopped baseline differs from restored original' }
        }
    } else {
    $httpd = @($processes | Where-Object { $_.exe -ieq $apache })
    $parents = @($httpd | Where-Object { $_.parent -notin $httpd.pid })
    if ($httpd.Count -ne 2 -or $parents.Count -ne 1) { throw 'Expected one known console Apache parent/child pair' }
    if (@($processes | Where-Object { $_.owner -ine $ExpectedOwner }).Count) { throw 'Unexpected Apache/PHP owner' }
    $parent = $parents[0]
    $nativeParentCreated = [GramlyzeStartupNative]::CreationFileTime([uint32]$parent.pid)
    # WMI/CIM dates truncate to microseconds; retain the full native 100 ns value
    # for the later held-handle identity check. Do not round during shutdown.
    $cimParentCreated = ([DateTimeOffset]$parent.created).UtcDateTime.ToFileTimeUtc()
    if (($nativeParentCreated - ($nativeParentCreated % 10)) -ne $cimParentCreated) { throw 'Native/CIM parent identity mismatch' }
    Assert-Instance $parent
    if ($parent.command -cne 'apache\bin\httpd.exe') { throw 'Current parent is not the audited bat launch' }
    $child = @($httpd | Where-Object { $_.parent -eq $parent.pid })
    if ($child.Count -ne 1 -or $child[0].command -cne '"C:\Program Files\xampp\apache\bin\httpd.exe" -d "C:/Program Files/xampp/apache"') { throw 'Unexpected child config arguments' }
    }
    $script:run = Join-Path $private ('apply-'+(Get-Date -Format 'yyyyMMdd-HHmmss-fff')+'-'+[guid]::NewGuid().ToString('N').Substring(0,8))
    New-Item -ItemType Directory -Path $script:run | Out-Null
    New-Item -ItemType Directory -Path (Join-Path $script:run 'candidate') | Out-Null
    $rows = foreach ($relative in $files) {
        $source = Join-Path $xampp $relative
        $destination = Join-Path $script:run ('backup/'+$relative)
        Assert-StartupPath $source
        New-Item -ItemType Directory -Path (Split-Path -Parent $destination) -Force | Out-Null
        $hash = Get-StartupHash $source
        Copy-Item -LiteralPath $source -Destination $destination
        if ((Get-StartupHash $destination) -ne $hash -or (Get-StartupHash $source) -ne $hash) { throw 'Backup consistency failed' }
        [pscustomobject]@{relative=$relative;sha256=$hash}
    }
    Add-Type -AssemblyName System.IO.Compression.FileSystem
    $archive = [IO.Compression.ZipFile]::OpenRead($zip)
    try {
        foreach ($pin in $pins) {
            if ($pin.ContainsKey('source')) {
                $destination = Join-Path $script:run ('candidate/'+$pin.name)
                Copy-Item -LiteralPath $pin.source -Destination $destination
                if ((Get-StartupHash $destination) -cne $pin.after) { throw 'Certificate/key copy hash mismatch' }
                continue
            }
            $entry = $archive.GetEntry($pin.name)
            if (-not $entry) { throw 'Pinned source DLL absent from ZIP' }
            $destination = Join-Path $script:run ('candidate/'+$pin.name)
            [IO.Compression.ZipFileExtensions]::ExtractToFile($entry,$destination,$false)
            if ((Get-StartupHash $destination) -ne $pin.after) { throw 'Candidate DLL hash mismatch' }
        }
    } finally { $archive.Dispose() }
    $script:manifest = [pscustomobject]@{schema=1;created=[DateTime]::UtcNow.ToString('o');owner=$ExpectedOwner;
        source_zip_sha256=$zipHash;isolation_evidence=$isolationPath;isolation_sha256=(Get-StartupHash $isolationPath);certificate_evidence=$certificatePath;certificate_evidence_sha256=$certificateHash;stopped_baseline=$StoppedBaselineFrom;files=@($rows);processes=$processes;parent=$parent;native_parent_created_filetime=$nativeParentCreated;child=$child[0];
        database=@(Get-DatabaseIdentity);syntax=@(Check-Syntax);
        apache_log_offset=(Get-LogLength (Join-Path $xampp 'apache/logs/error.log'));
        gramlyze_ssl_log_offset=(Get-LogLength (Join-Path $xampp 'apache/logs/gramlyze.loc-ssl-error.log'));
        shared_php_log_offset=(Get-LogLength (Join-Path $xampp 'php/logs/php_error_log'))}
    Save-Json 'manifest.json' $script:manifest
    Write-Output $script:run
    Write-Output ('Fresh verified backup and '+$pins.Count+' pinned candidates prepared; installed files unchanged.')
    exit 0
}

$script:run = [IO.Path]::GetFullPath($RunDirectory)
if ([IO.Path]::GetDirectoryName($script:run) -ine $private -or [IO.Path]::GetFileName($script:run) -notmatch '^apply-[0-9-]+-[a-f0-9]{8}$') { throw 'Unexpected run directory' }
Assert-StartupPath $script:run
$script:manifest = Get-Content -LiteralPath (Join-Path $script:run 'manifest.json') -Raw | ConvertFrom-Json
if ($script:manifest.schema -ne 1 -or $script:manifest.owner -ine $ExpectedOwner) { throw 'Unexpected manifest identity' }
if ($script:manifest.PSObject.Properties.Name -contains 'certificate_evidence' -and $script:manifest.certificate_evidence) {
    $appliedPath = Join-Path $script:run 'apply.json'
    if ($Phase -ne 'Apply' -and (Test-Path -LiteralPath $appliedPath)) {
        # Completed transaction evidence makes rollback self-contained. Prototype
        # isolation fixtures may be removed without losing the verified backups.
        $applied = Get-Content -LiteralPath $appliedPath -Raw | ConvertFrom-Json
        if ($applied.success -ne $true) { throw 'Completed apply evidence required' }
        foreach ($relative in @('apache/conf/ssl.crt/server.crt','apache/conf/ssl.key/server.key')) {
            $rows = @($applied.files | Where-Object { $_.relative -ceq $relative })
            $backups = @($script:manifest.files | Where-Object { $_.relative -ceq $relative })
            if ($rows.Count -ne 1 -or $backups.Count -ne 1 -or $rows[0].before -cne $backups[0].sha256 -or
                $rows[0].after -cnotmatch '^[a-f0-9]{64}$') { throw 'Recorded certificate pin mismatch' }
            $pins += @{name=[IO.Path]::GetFileName($relative);relative=$relative;before=$rows[0].before;after=$rows[0].after}
        }
    } else {
        if ((Get-StartupHash $script:manifest.certificate_evidence) -cne $script:manifest.certificate_evidence_sha256) { throw 'Certificate evidence changed' }
        $pins += @(Get-CertificatePins (Read-CertificateEvidence $script:manifest.certificate_evidence))
    }
}
$pair = @($pins | ForEach-Object { [pscustomobject]@{Source=(Join-Path $script:run ('candidate/'+$_.name));Target=(Join-Path $xampp $_.relative);Backup=(Join-Path $script:run ('backup/'+$_.relative));Before=$_.before;After=$_.after} })

switch ($Phase) {
    'StopCurrent' {
        # Addressed rollback of the newly started instance, including transactions
        # prepared while stopped. Capture fresh identity; never reuse recorded PIDs.
        Assert-Configs
        foreach ($item in $pair) {
            if ((Get-StartupHash $item.Target) -cne $item.After) { throw 'Installed candidate set changed; refusing rollback shutdown' }
        }
        $current = @(Get-Targets)
        $httpd = @($current | Where-Object { $_.exe -ieq $apache })
        $parents = @($httpd | Where-Object { $_.parent -notin $httpd.pid })
        if ($httpd.Count -ne 2 -or $parents.Count -ne 1 -or @($current | Where-Object { $_.owner -ine $ExpectedOwner }).Count) { throw 'Expected one current administrator Apache pair' }
        $parent = $parents[0]
        $children = @($httpd | Where-Object { $_.parent -eq $parent.pid })
        if ($parent.command -cne 'apache\bin\httpd.exe' -or $children.Count -ne 1 -or
            $children[0].command -cne '"C:\Program Files\xampp\apache\bin\httpd.exe" -d "C:/Program Files/xampp/apache"') { throw 'Unexpected current config arguments' }
        $batParent = Get-CimInstance Win32_Process -Filter ('ProcessId = '+$parent.parent)
        if (-not $batParent -or $batParent.Name -ine 'cmd.exe' -or $batParent.CommandLine -notmatch '(?i)apache_start\.bat') { throw 'Current Apache does not have the bat parent' }
        $nativeCreated = [GramlyzeStartupNative]::CreationFileTime([uint32]$parent.pid)
        if (($nativeCreated - ($nativeCreated % 10)) -ne ([DateTimeOffset]$parent.created).UtcDateTime.ToFileTimeUtc()) { throw 'Current native/CIM identity mismatch' }
        Assert-Instance $parent
        Assert-Instance $children[0]
        [GramlyzeStartupNative]::Shutdown([uint32]$parent.pid, [long]$nativeCreated)
        Save-Json ('stop-current-'+(Get-Date -Format 'yyyyMMdd-HHmmss-fff')+'.json') @{at=[DateTime]::UtcNow.ToString('o');processes=$current;parent=$parent;native_created_filetime=$nativeCreated}
        Write-Output 'Verified current bat Apache signaled; use Wait, then Restore.'
    }
    'Stop' {
        Assert-Configs
        if (-not $script:manifest.parent -or -not $script:manifest.child) { throw 'Offline preparation has no original process to stop' }
        if (Test-Path -LiteralPath (Join-Path $script:run 'stop.json')) { throw 'Shutdown already signaled; use Wait' }
        Assert-Instance $script:manifest.parent
        Assert-Instance $script:manifest.child
        $syntax = @(Check-Syntax)
        Assert-Instance $script:manifest.parent
        Assert-Instance $script:manifest.child
        $before = @(Get-Targets)
        [GramlyzeStartupNative]::Shutdown([uint32]$script:manifest.parent.pid, [long]$script:manifest.native_parent_created_filetime)
        Save-Json 'stop.json' @{at=[DateTime]::UtcNow.ToString('o');event=('ap'+$script:manifest.parent.pid+'_shutdown');processes=$before;syntax=$syntax}
        Write-Output 'Native shutdown signaled only to verified Apache parent; use Wait before Apply.'
    }
    'Wait' {
        if (-not (Test-Path -LiteralPath (Join-Path $script:run 'stop.json')) -and -not @(Get-ChildItem -LiteralPath $script:run -Filter 'stop-current-*.json').Count) { throw 'No recorded shutdown' }
        $deadline = [DateTime]::UtcNow.AddSeconds(25)
        do {
            $remaining = @(Get-Targets)
            if (-not $remaining.Count) { Assert-Stopped; Write-Output 'Target Apache and its NTS workers fully stopped; ports free.'; exit 0 }
            Start-Sleep -Seconds 1
        } while ([DateTime]::UtcNow -lt $deadline)
        $remaining | ConvertTo-Json -Depth 3
        Write-Output 'Still shutting down; no DLL mutation. Repeat Wait.'
        exit 2
    }
    'Apply' {
        Assert-Stopped
        Assert-Configs
        if (Test-Path -LiteralPath (Join-Path $script:run 'apply.json')) { throw 'Apply already recorded; prepare a new transaction' }
        Invoke-VerifiedRuntimeReplacement $pair
        try { $syntax = @(Check-Syntax) }
        catch {
            $failure = $_
            foreach ($item in $pair) { Copy-Item -LiteralPath $item.Backup -Destination $item.Target -Force }
            foreach ($item in $pair) { if ((Get-StartupHash $item.Target) -ne $item.Before) { throw 'Syntax failure rollback incomplete; keep Apache stopped' } }
            throw $failure
        }
        Save-Json 'apply.json' @{at=[DateTime]::UtcNow.ToString('o');files=@($pins);syntax=$syntax;config_diff='empty';success=$true}
        Write-Output ($pair.Count.ToString()+' pinned files replaced; syntax passed; Apache still stopped.')
    }
    'Restore' {
        Assert-Stopped
        Assert-Configs
        foreach ($item in $pair) {
            if ((Get-StartupHash $item.Backup) -ne $item.Before -or (Get-StartupHash $item.Target) -notin @($item.Before,$item.After)) { throw 'Rollback hash guard failed' }
        }
        foreach ($item in $pair) { Copy-Item -LiteralPath $item.Backup -Destination $item.Target -Force }
        foreach ($item in $pair) { if ((Get-StartupHash $item.Target) -ne $item.Before) { throw 'Rollback verification failed; keep Apache stopped' } }
        Save-Json ('restore-'+(Get-Date -Format 'yyyyMMdd-HHmmss-fff')+'.json') @{at=[DateTime]::UtcNow.ToString('o');success=$true;syntax=@(Check-Syntax)}
        Write-Output ('All '+$pair.Count+' original files restored and verified; Apache still stopped.')
    }
    'Start' {
        Assert-Stopped
        Assert-Configs
        if (Test-Path -LiteralPath (Join-Path $script:run ('launch-'+$LaunchLabel+'.json'))) { throw 'Launch label already exists; inspect existing console' }
        $allBefore = $true; $allAfter = $true
        foreach ($item in $pair) {
            $hash = Get-StartupHash $item.Target
            $allBefore = $allBefore -and $hash -eq $item.Before
            $allAfter = $allAfter -and $hash -eq $item.After
        }
        if (-not $allBefore -and -not $allAfter) { throw 'Unexpected or mixed installed DLL set' }
        $shell = 'C:\Program Files\PowerShell\7\pwsh.exe'
        $scriptFile = Join-Path $PSScriptRoot 'start-bat-console.ps1'
        $command = '"'+$shell+'" -NoProfile -File "'+$scriptFile+'" -RunDirectory "'+$script:run+'" -ExpectedOwner "'+$ExpectedOwner+'" -LaunchLabel '+$LaunchLabel
        $launcherPid = [GramlyzeStartupNative]::StartConsole($shell,$command,$xampp)
        Save-Json ('launch-'+$LaunchLabel+'.json') @{at=[DateTime]::UtcNow.ToString('o');console_pid=$launcherPid;flags='CREATE_NEW_CONSOLE | CREATE_DEFAULT_ERROR_MODE | CREATE_UNICODE_ENVIRONMENT';environment='CreateEnvironmentBlock current user, inherit=false';parent_error_mode=[GramlyzeStartupNative]::GetErrorMode();command=$command}
        Write-Output ('Fresh visible administrative console PID '+$launcherPid+' started; check console-start evidence and live acceptance.')
    }
    'Status' {
        $processes = @(Get-Targets)
        $modules = foreach ($process in $processes) {
            foreach ($module in (Get-Process -Id $process.pid).Modules) {
                if ($module.ModuleName -match '^(php8(?:ts)?\.dll|php8apache2_4\.dll|php_(?:curl|openssl)\.dll|lib(?:ssl|crypto)-3-x64\.dll|libssh2\.dll|mod_ssl\.so)$') {
                    [pscustomobject]@{pid=$process.pid;name=$module.ModuleName;path=$module.FileName;sha256=(Get-StartupHash $module.FileName);version=$module.FileVersionInfo.FileVersion}
                }
            }
        }
        $db = @(Get-DatabaseIdentity)
        $dbPreserved = $db.Count -eq @($script:manifest.database).Count
        foreach ($originalDb in @($script:manifest.database)) {
            $matchedDb = @($db | Where-Object { $_.pid -eq $originalDb.pid -and $_.exe -ceq $originalDb.exe -and
                ([DateTimeOffset]$_.created).UtcDateTime -eq ([DateTimeOffset]$originalDb.created).UtcDateTime })
            $dbPreserved = $dbPreserved -and $matchedDb.Count -eq 1
        }
        $result = @{at=[DateTime]::UtcNow.ToString('o');processes=$processes;modules=@($modules);database=$db;
            database_preserved=$dbPreserved;
            listeners=@(Get-NetTCPConnection -State Listen | Where-Object { $_.LocalPort -in @(80,443) } | Select-Object LocalAddress,LocalPort,OwningProcess)}
        Save-Json ('status-'+(Get-Date -Format 'yyyyMMdd-HHmmss-fff')+'.json') $result
        $result | ConvertTo-Json -Depth 8
    }
}
