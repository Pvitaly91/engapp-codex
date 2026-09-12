[CmdletBinding()]
param(
    [Parameter(Mandatory)][ValidateRange(1, 2147483647)][int] $ExpectedParentPid,
    [Parameter(Mandatory)][DateTimeOffset] $ExpectedParentCreationUtc,
    [Parameter(Mandatory)][ValidateRange(1, 2147483647)][int] $ExpectedChildPid,
    [Parameter(Mandatory)][string] $ExpectedChildCommandLine,
    [Parameter(Mandatory)][string] $ExpectedOwner,
    [Parameter(Mandatory)][ValidatePattern('^[a-zA-Z0-9][a-zA-Z0-9_-]{0,63}$')][string] $Label,
    [switch] $ObserveOnly
)
$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest

# Apache 2.4.58's WinNT MPM natively waits for ap<PARENT_PID>_restart.
# This signals that existing event once. It neither creates an event/service
# nor starts, stops or replaces the existing Apache parent or its owner.
# Source: https://raw.githubusercontent.com/apache/httpd/2.4.58/server/mpm/winnt/mpm_winnt.c
$xamppRoot = 'C:\Program Files\xampp'
$apacheRoot = Join-Path $xamppRoot 'apache'
$apacheExe = Join-Path $apacheRoot 'bin\httpd.exe'
$apacheConfig = Join-Path $apacheRoot 'conf\httpd.conf'
$phpExe = Join-Path $xamppRoot 'php-8.5.10-nts-gramlyze\php-cgi.exe'
$evidenceRoot = 'D:\DEV\htdocs\gramlyze.loc\storage\app\seo-m9-4-local\restart'
$evidence = [ordered]@{
    schema = 'gramlyze-m9-4-native-apache-restart-v1'
    started_at_utc = [DateTime]::UtcNow.ToString('o')
    label = $Label
    observation_only = [bool] $ObserveOnly
    config = $apacheConfig
    server_root = $apacheRoot
    event_name = 'ap' + $ExpectedParentPid + '_restart'
    signaled = $false
    success = $false
    before = $null
    after = $null
    syntax_exit_code = $null
    syntax_output = @()
    vanished_process_ids = @()
    error = $null
}

function Assert-NoReparse([string] $Path) {
    $cursor = [IO.Path]::GetFullPath($Path)
    while ($cursor) {
        if ((Test-Path -LiteralPath $cursor) -and ((Get-Item -LiteralPath $cursor -Force).Attributes -band [IO.FileAttributes]::ReparsePoint)) {
            throw "reparse-point-refused:$cursor"
        }
        $cursor = [IO.Path]::GetDirectoryName($cursor)
    }
}

function Get-ProcessEvidence($Process, [switch] $AllowVanished) {
    try {
        $owner = Invoke-CimMethod -InputObject $Process -MethodName GetOwner
    } catch {
        $cimFailure = $_.Exception
        while ($cimFailure -and $cimFailure -isnot [Microsoft.Management.Infrastructure.CimException]) {
            $cimFailure = $cimFailure.InnerException
        }
        $notFound = $cimFailure -and (
            $cimFailure.NativeErrorCode -eq [Microsoft.Management.Infrastructure.NativeErrorCode]::NotFound -or
            $cimFailure.HResult -eq -2147217406 -or $cimFailure.StatusCode -eq 2147749890
        )
        # Only enumerated children/workers may exit between enumeration and
        # GetOwner. Never hide parent loss, access failures or an extant PID.
        if (-not $AllowVanished -or -not $notFound) { throw }
        $stillPresent = Get-CimInstance Win32_Process -Filter ("ProcessId = " + [int] $Process.ProcessId)
        if ($stillPresent) { throw }
        $evidence.vanished_process_ids += [int] $Process.ProcessId
        return
    }
    if ($owner.ReturnValue -ne 0) { throw "cannot-read-process-owner:$($Process.ProcessId)" }
    [pscustomobject][ordered]@{
        pid = [int] $Process.ProcessId
        parent_pid = [int] $Process.ParentProcessId
        created_utc = $Process.CreationDate.ToUniversalTime().ToString('o')
        executable = $Process.ExecutablePath
        command_line = $Process.CommandLine
        owner = $owner.Domain + '\' + $owner.User
    }
}

function Get-RequiredProcess([int] $ProcessId) {
    $process = Get-CimInstance Win32_Process -Filter "ProcessId = $ProcessId"
    if (-not $process) { throw "expected-process-missing:$ProcessId" }
    Get-ProcessEvidence $process
}

function Assert-Parent($Parent) {
    if ($Parent.pid -ne $ExpectedParentPid -or $Parent.executable -ine $apacheExe -or
        ([DateTimeOffset] $Parent.created_utc).UtcDateTime -ne $ExpectedParentCreationUtc.UtcDateTime -or
        $Parent.owner -ine $ExpectedOwner) { throw 'parent-identity-mismatch' }
}

function Assert-Child($Child) {
    if ($Child.parent_pid -ne $ExpectedParentPid -or $Child.executable -ine $apacheExe -or
        $Child.owner -ine $ExpectedOwner -or $Child.command_line -cne $ExpectedChildCommandLine) {
        throw 'child-identity-or-command-line-mismatch'
    }
    $arguments = [GramlyzeM94ApacheControl]::SplitCommandLine($Child.command_line)
    if ($arguments.Count -lt 3 -or [IO.Path]::GetFullPath($arguments[0]) -ine $apacheExe) { throw 'unexpected-child-executable-argument' }
    $rootArgumentCount = 0
    $seenConfig = $false
    for ($index = 1; $index -lt $arguments.Count; $index += 2) {
        if ($index + 1 -ge $arguments.Count) { throw 'unexpected-child-argument-count' }
        # Apache may prepend its normalized ServerRoot to the parent's explicit
        # -d. Permit at most two, both the same fixed root; the entire expected
        # command line must still match exactly before parsing these arguments.
        if ($arguments[$index] -ceq '-d' -and $rootArgumentCount -lt 2) {
            if ([IO.Path]::GetFullPath($arguments[$index + 1]).TrimEnd('\') -ine $apacheRoot) { throw 'unexpected-child-server-root' }
            $rootArgumentCount++
        } elseif ($arguments[$index] -ceq '-f' -and -not $seenConfig) {
            $configArgument = $arguments[$index + 1]
            if (-not [IO.Path]::IsPathRooted($configArgument)) { $configArgument = Join-Path $apacheRoot $configArgument }
            if ([IO.Path]::GetFullPath($configArgument) -ine $apacheConfig) { throw 'unexpected-child-config' }
            $seenConfig = $true
        } else { throw 'unexpected-child-argument' }
    }
    if ($rootArgumentCount -eq 0) { throw 'explicit-child-server-root-required' }
    if (-not $seenConfig -and -not $script:compiledDefaultConfirmed) { throw 'compiled-default-config-not-confirmed' }
}

function Get-Snapshot {
    $parent = Get-RequiredProcess $ExpectedParentPid
    Assert-Parent $parent
    $children = @(Get-CimInstance Win32_Process -Filter "ParentProcessId = $ExpectedParentPid AND Name = 'httpd.exe'" |
        ForEach-Object { Get-ProcessEvidence $_ -AllowVanished })
    if ($children.Count -gt 4) { throw 'unexpected-apache-child-count' }
    foreach ($child in $children) { Assert-Child $child }
    $workers = @(Get-CimInstance Win32_Process -Filter "Name = 'php-cgi.exe'" |
        Where-Object { $_.ExecutablePath -ieq $phpExe } | ForEach-Object { Get-ProcessEvidence $_ -AllowVanished })
    if ($workers.Count -gt 8) { throw 'unexpected-gramlyze-worker-count' }
    [pscustomobject][ordered]@{ parent = $parent; children = $children; gramlyze_php_workers = $workers }
}

Assert-NoReparse $evidenceRoot
New-Item -ItemType Directory -Force -Path $evidenceRoot | Out-Null
$evidenceFile = Join-Path $evidenceRoot ($Label + '-' + (Get-Date -Format 'yyyyMMdd-HHmmss-fff') + '-' + [guid]::NewGuid().ToString('N').Substring(0, 8) + '.json')
$script:compiledDefaultConfirmed = $false
try {
    foreach ($path in @($apacheExe, $apacheConfig)) { Assert-NoReparse $path }
    if (-not ('GramlyzeM94ApacheControl' -as [type])) {
        Add-Type -TypeDefinition @'
using System;
using System.ComponentModel;
using System.Runtime.InteropServices;
public static class GramlyzeM94ApacheControl {
    [DllImport("kernel32.dll", CharSet = CharSet.Unicode, SetLastError = true)]
    public static extern IntPtr OpenEvent(uint access, bool inheritHandle, string name);
    [DllImport("kernel32.dll", SetLastError = true)]
    [return: MarshalAs(UnmanagedType.Bool)]
    public static extern bool SetEvent(IntPtr handle);
    [DllImport("kernel32.dll", SetLastError = true)]
    [return: MarshalAs(UnmanagedType.Bool)]
    public static extern bool CloseHandle(IntPtr handle);
    [DllImport("shell32.dll", CharSet = CharSet.Unicode, SetLastError = true)]
    private static extern IntPtr CommandLineToArgvW(string commandLine, out int argumentCount);
    [DllImport("kernel32.dll")]
    private static extern IntPtr LocalFree(IntPtr handle);
    public static string[] SplitCommandLine(string commandLine) {
        int count;
        IntPtr buffer = CommandLineToArgvW(commandLine, out count);
        if (buffer == IntPtr.Zero) throw new Win32Exception(Marshal.GetLastWin32Error());
        try {
            string[] result = new string[count];
            for (int i = 0; i < count; i++) result[i] = Marshal.PtrToStringUni(Marshal.ReadIntPtr(buffer, i * IntPtr.Size));
            return result;
        } finally { LocalFree(buffer); }
    }
}
'@
    }
    $parent = Get-RequiredProcess $ExpectedParentPid
    Assert-Parent $parent
    if (-not $ObserveOnly) {
        $child = Get-RequiredProcess $ExpectedChildPid
        # The accepted process pair is checked before starting even a syntax probe.
        if ($child.parent_pid -ne $ExpectedParentPid -or $child.executable -ine $apacheExe -or
            $child.owner -ine $ExpectedOwner -or $child.command_line -cne $ExpectedChildCommandLine) { throw 'initial-child-identity-mismatch' }
    }

    $buildOutput = @(& $apacheExe -d $apacheRoot -f $apacheConfig -V 2>&1 | ForEach-Object { $_.ToString() })
    if ($LASTEXITCODE -ne 0) { throw 'apache-build-config-check-failed' }
    $script:compiledDefaultConfirmed = (($buildOutput -join "`n") -match '(?m)SERVER_CONFIG_FILE="conf/httpd\.conf"')
    $evidence.before = Get-Snapshot
    if (-not $ObserveOnly) {
        Assert-Child $child
        if (@($evidence.before.children).Count -ne 1 -or $evidence.before.children[0].pid -ne $ExpectedChildPid) { throw 'initial-child-set-changed' }
        $evidence.syntax_output = @(& $apacheExe -d $apacheRoot -f $apacheConfig -t 2>&1 | ForEach-Object { $_.ToString() })
        $evidence.syntax_exit_code = $LASTEXITCODE
        if ($evidence.syntax_exit_code -ne 0 -or ($evidence.syntax_output -join "`n") -notmatch '(?m)^Syntax OK\s*$') { throw 'apache-syntax-not-ok; restart-not-signaled' }

        # Revalidate after syntax checks, then again after acquiring the existing
        # event handle. PID+creation time prevents signaling a reused process ID.
        Assert-Parent (Get-RequiredProcess $ExpectedParentPid)
        $currentChild = Get-RequiredProcess $ExpectedChildPid
        Assert-Child $currentChild
        if ($currentChild.created_utc -cne $child.created_utc) { throw 'child-pid-reused' }
        $handle = [GramlyzeM94ApacheControl]::OpenEvent(0x0002, $false, $evidence.event_name)
        if ($handle -eq [IntPtr]::Zero) { throw ('open-existing-restart-event-failed:win32=' + [Runtime.InteropServices.Marshal]::GetLastWin32Error()) }
        try {
            Assert-Parent (Get-RequiredProcess $ExpectedParentPid)
            if (-not [GramlyzeM94ApacheControl]::SetEvent($handle)) { throw ('set-restart-event-failed:win32=' + [Runtime.InteropServices.Marshal]::GetLastWin32Error()) }
            $evidence.signaled = $true
            $evidence.signaled_at_utc = [DateTime]::UtcNow.ToString('o')
        } finally { [void] [GramlyzeM94ApacheControl]::CloseHandle($handle) }
    }

    # Slow shared Apache startup can outlast one observation window. Reinvoke
    # with -ObserveOnly and the ORIGINAL expected child PID to continue watching
    # without opening/signaling the restart event again. HTTP readiness is separate.
    $watch = [Diagnostics.Stopwatch]::StartNew()
    do {
        Start-Sleep -Milliseconds 250
        $evidence.after = Get-Snapshot
        $newChildren = @($evidence.after.children | Where-Object { $_.pid -ne $ExpectedChildPid })
        $oldChildren = @($evidence.after.children | Where-Object { $_.pid -eq $ExpectedChildPid })
        if ($newChildren.Count -eq 1 -and $oldChildren.Count -eq 0) {
            $evidence.success = $true
            break
        }
    } while ($watch.Elapsed.TotalSeconds -lt 30)
    $evidence.waited_seconds = [Math]::Round($watch.Elapsed.TotalSeconds, 3)
    if (-not $evidence.success) { throw 'restart-observation-timeout; no-second-signal-sent' }
} catch {
    $evidence.error = $_.Exception.Message
} finally {
    $evidence.finished_at_utc = [DateTime]::UtcNow.ToString('o')
    [IO.File]::WriteAllText($evidenceFile, ($evidence | ConvertTo-Json -Depth 8), [Text.UTF8Encoding]::new($false))
}
$evidence | ConvertTo-Json -Depth 8
Write-Output "Private evidence: $evidenceFile"
if (-not $evidence.success) { throw $evidence.error }
