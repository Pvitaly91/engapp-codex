[CmdletBinding()]
param(
    [Parameter(Mandatory)][ValidatePattern('(?i)^[^\\]+\\admin$')][string] $ExpectedOwner,
    [Parameter(Mandatory)][ValidatePattern('^[a-zA-Z0-9][a-zA-Z0-9_-]{0,63}$')][string] $Label
)
$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest
# Restore the existing console model under its original named account. This
# creates no service/task, changes no config/ACL, and performs no stop/restart.
# Original Apache token elevation was not measured; owner equality alone does
# not establish equal integrity/elevation. Record that limitation explicitly.
$apacheRoot = 'C:\Program Files\xampp\apache'
$apacheExe = Join-Path $apacheRoot 'bin\httpd.exe'
$apacheConfig = Join-Path $apacheRoot 'conf\httpd.conf'
$privateRoot = 'D:\DEV\htdocs\gramlyze.loc\storage\app\seo-m9-4-local\start'
$evidence = [ordered]@{
    schema = 'gramlyze-m9-4-stopped-apache-start-v1'; label = $Label
    started_at_utc = [DateTime]::UtcNow.ToString('o'); success = $false
    executable = $apacheExe; server_root = $apacheRoot; config = $apacheConfig
    expected_owner = $ExpectedOwner; launcher_owner = $null; launcher_elevated = $null
    original_token_elevation = 'not-measured; owner-match-is-not-token-equivalence'
    old_error_mode = $null; child_error_mode = $null; launcher_error_mode_restored = $false
    syntax_pid = $null; syntax_exit_code = $null
    apache_pid = $null; apache_created_utc = $null; apache_owner = $null
    apache_exit_code = $null; error = $null; win32_error = $null
    readiness = 'not-checked; HTTP-and-worker-acceptance-required-separately'
}

function Assert-NoReparse([string] $Path) {
    $cursor = [IO.Path]::GetFullPath($Path)
    while ($cursor) {
        if ((Test-Path -LiteralPath $cursor) -and ((Get-Item -LiteralPath $cursor -Force).Attributes -band [IO.FileAttributes]::ReparsePoint)) { throw "reparse-point-refused:$cursor" }
        $cursor = [IO.Path]::GetDirectoryName($cursor)
    }
}
function Assert-Stopped {
    if (@(Get-CimInstance Win32_Process -Filter "Name = 'httpd.exe'").Count) { throw 'an-httpd-process-already-exists; no-launch' }
    if (@(Get-NetTCPConnection -State Listen | Where-Object { $_.LocalPort -in @(80, 443) }).Count) { throw 'port-80-or-443-already-listening; no-launch' }
}

foreach ($path in @($apacheExe, $apacheConfig, $privateRoot)) { Assert-NoReparse $path }
$evidenceDirectory = Join-Path $privateRoot ($Label + '-' + (Get-Date -Format 'yyyyMMdd-HHmmss-fff') + '-' + [guid]::NewGuid().ToString('N').Substring(0, 8))
New-Item -ItemType Directory -Path $evidenceDirectory -Force | Out-Null
$resultPath = Join-Path $evidenceDirectory 'result.json'
$syntaxHandle = [IntPtr]::Zero
$apacheHandle = [IntPtr]::Zero
$modeChanged = $false
try {
    $identity = [Security.Principal.WindowsIdentity]::GetCurrent()
    $evidence.launcher_owner = $identity.Name
    $evidence.launcher_elevated = ([Security.Principal.WindowsPrincipal]::new($identity)).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
    if ($identity.Name -ine $ExpectedOwner) { throw 'launcher-owner-mismatch; no-launch' }
    Assert-Stopped
    if (-not ('GramlyzeM94ConsoleStart' -as [type])) {
        Add-Type -TypeDefinition @'
using System;
using System.Text;
using System.ComponentModel;
using System.Runtime.InteropServices;
public static class GramlyzeM94ConsoleStart {
    [StructLayout(LayoutKind.Sequential)] private struct SecurityAttributes {
        public int length; public IntPtr descriptor; [MarshalAs(UnmanagedType.Bool)] public bool inherit;
    }
    [StructLayout(LayoutKind.Sequential, CharSet = CharSet.Unicode)] private struct StartupInfo {
        public int cb; public string reserved, desktop, title;
        public uint x, y, xSize, ySize, xChars, yChars, fill, flags;
        public ushort showWindow, reservedSize; public IntPtr reservedBytes, input, output, error;
    }
    [StructLayout(LayoutKind.Sequential)] private struct ProcessInfo {
        public IntPtr process, thread; public uint pid, tid;
    }
    public sealed class StartedProcess { public IntPtr Handle; public uint Pid; }
    [DllImport("kernel32.dll")] public static extern uint GetErrorMode();
    [DllImport("kernel32.dll")] public static extern uint SetErrorMode(uint mode);
    [DllImport("kernel32.dll", CharSet = CharSet.Unicode, SetLastError = true)]
    private static extern IntPtr CreateFileW(string name, uint access, uint share, ref SecurityAttributes security, uint creation, uint flags, IntPtr template);
    [DllImport("kernel32.dll", CharSet = CharSet.Unicode, SetLastError = true)]
    [return: MarshalAs(UnmanagedType.Bool)]
    private static extern bool CreateProcessW(string application, StringBuilder command, IntPtr processAttributes, IntPtr threadAttributes,
        bool inheritHandles, uint flags, IntPtr environment, string directory, ref StartupInfo startup, out ProcessInfo process);
    [DllImport("kernel32.dll", SetLastError = true)]
    [return: MarshalAs(UnmanagedType.Bool)] public static extern bool CloseHandle(IntPtr handle);
    [DllImport("kernel32.dll", SetLastError = true)] public static extern uint WaitForSingleObject(IntPtr handle, uint milliseconds);
    [DllImport("kernel32.dll", SetLastError = true)]
    [return: MarshalAs(UnmanagedType.Bool)] private static extern bool GetExitCodeProcess(IntPtr handle, out uint code);
    [DllImport("kernel32.dll", SetLastError = true)]
    [return: MarshalAs(UnmanagedType.Bool)] private static extern bool GetProcessTimes(IntPtr handle, out long creation, out long exit, out long kernel, out long user);
    private static IntPtr OpenFile(string path, bool input) {
        var security = new SecurityAttributes { length = Marshal.SizeOf(typeof(SecurityAttributes)), descriptor = IntPtr.Zero, inherit = true };
        // CREATE_NEW prevents replacing any existing log; NUL is opened read-only.
        IntPtr handle = CreateFileW(path, input ? 0x80000000u : 0x40000000u, 3, ref security, input ? 3u : 1u, 0x80, IntPtr.Zero);
        if (handle == new IntPtr(-1)) throw new Win32Exception(Marshal.GetLastWin32Error(), "CreateFileW failed");
        return handle;
    }
    public static StartedProcess Start(string executable, string commandLine, string directory, string stdout, string stderr) {
        IntPtr input = IntPtr.Zero, output = IntPtr.Zero, error = IntPtr.Zero;
        try {
            input = OpenFile("NUL", true); output = OpenFile(stdout, false); error = OpenFile(stderr, false);
            var startup = new StartupInfo { cb = Marshal.SizeOf(typeof(StartupInfo)), flags = 0x101, showWindow = 0, input = input, output = output, error = error };
            ProcessInfo process;
            // CREATE_NO_WINDOW. Deliberately no CREATE_DEFAULT_ERROR_MODE,
            // alternate user/token, job breakaway, debugger or service flags.
            if (!CreateProcessW(executable, new StringBuilder(commandLine), IntPtr.Zero, IntPtr.Zero, true,
                0x08000000, IntPtr.Zero, directory, ref startup, out process)) {
                throw new Win32Exception(Marshal.GetLastWin32Error(), "CreateProcessW failed");
            }
            CloseHandle(process.thread);
            return new StartedProcess { Handle = process.process, Pid = process.pid };
        } finally {
            // The child retains its own file handles after the launcher exits.
            if (input != IntPtr.Zero) CloseHandle(input);
            if (output != IntPtr.Zero) CloseHandle(output);
            if (error != IntPtr.Zero) CloseHandle(error);
        }
    }
    public static uint ExitCode(IntPtr handle) {
        uint code; if (!GetExitCodeProcess(handle, out code)) throw new Win32Exception(Marshal.GetLastWin32Error()); return code;
    }
    public static DateTime CreationUtc(IntPtr handle) {
        long creation, exit, kernel, user;
        if (!GetProcessTimes(handle, out creation, out exit, out kernel, out user)) throw new Win32Exception(Marshal.GetLastWin32Error());
        return DateTime.FromFileTimeUtc(creation);
    }
}
'@
    }
    $commandLine = '"' + $apacheExe + '" -d "' + $apacheRoot + '" -f "' + $apacheConfig + '"'
    $evidence.command_line = $commandLine
    $evidence.old_error_mode = [GramlyzeM94ConsoleStart]::GetErrorMode()
    $evidence.child_error_mode = $evidence.old_error_mode -bor 0x0001
    [void] [GramlyzeM94ConsoleStart]::SetErrorMode($evidence.child_error_mode)
    $modeChanged = $true
    # Only SEM_FAILCRITICALERRORS is added. Loader errors return to the caller;
    # raw stderr, Apache/PHP logs and exit codes are retained, never filtered.
    $syntax = [GramlyzeM94ConsoleStart]::Start($apacheExe, $commandLine + ' -t', $apacheRoot,
        (Join-Path $evidenceDirectory 'syntax-stdout.log'), (Join-Path $evidenceDirectory 'syntax-stderr.log'))
    $syntaxHandle = $syntax.Handle
    $evidence.syntax_pid = $syntax.Pid
    $waitResult = [GramlyzeM94ConsoleStart]::WaitForSingleObject($syntaxHandle, 30000)
    if ($waitResult -eq 0xFFFFFFFF) { throw [ComponentModel.Win32Exception]::new([Runtime.InteropServices.Marshal]::GetLastWin32Error()) }
    if ($waitResult -ne 0) { throw 'syntax-check-still-running; no-server-launch; inspect-recorded-syntax-pid' }
    $evidence.syntax_exit_code = [GramlyzeM94ConsoleStart]::ExitCode($syntaxHandle)
    $syntaxText = [IO.File]::ReadAllText((Join-Path $evidenceDirectory 'syntax-stdout.log')) + [IO.File]::ReadAllText((Join-Path $evidenceDirectory 'syntax-stderr.log'))
    if ($evidence.syntax_exit_code -ne 0 -or $syntaxText -notmatch '(?m)^Syntax OK\s*$') { throw 'syntax-check-not-ok; no-server-launch' }
    Assert-Stopped
    $started = [GramlyzeM94ConsoleStart]::Start($apacheExe, $commandLine, $apacheRoot,
        (Join-Path $evidenceDirectory 'apache-stdout.log'), (Join-Path $evidenceDirectory 'apache-stderr.log'))
    $apacheHandle = $started.Handle
    $evidence.apache_pid = $started.Pid
    $evidence.apache_created_utc = [GramlyzeM94ConsoleStart]::CreationUtc($apacheHandle).ToString('o')
    # Restore the launcher's mode immediately. The already-created child keeps
    # its inherited process-local setting; no Windows-wide setting is changed.
    [void] [GramlyzeM94ConsoleStart]::SetErrorMode($evidence.old_error_mode)
    $evidence.launcher_error_mode_restored = [GramlyzeM94ConsoleStart]::GetErrorMode() -eq $evidence.old_error_mode
    $modeChanged = $false
    $evidence.apache_exit_code = [GramlyzeM94ConsoleStart]::ExitCode($apacheHandle)
    if ($evidence.apache_exit_code -ne 259) { throw 'apache-exited-during-launch; inspect-raw-startup-logs' }
    $process = Get-CimInstance Win32_Process -Filter ("ProcessId = " + $started.Pid)
    if (-not $process -or $process.ExecutablePath -ine $apacheExe -or $process.CommandLine -cne $commandLine) { throw 'started-apache-identity-not-confirmed' }
    $owner = Invoke-CimMethod -InputObject $process -MethodName GetOwner
    if ($owner.ReturnValue -ne 0) { throw 'started-apache-owner-unavailable' }
    $evidence.apache_owner = $owner.Domain + '\' + $owner.User
    if ($evidence.apache_owner -ine $ExpectedOwner) { throw 'started-apache-owner-mismatch' }
    $evidence.success = $true
} catch {
    $exception = $_.Exception.GetBaseException()
    $evidence.error = $exception.Message
    if ($exception -is [ComponentModel.Win32Exception]) { $evidence.win32_error = $exception.NativeErrorCode }
} finally {
    if ($modeChanged) {
        [void] [GramlyzeM94ConsoleStart]::SetErrorMode($evidence.old_error_mode)
        $evidence.launcher_error_mode_restored = [GramlyzeM94ConsoleStart]::GetErrorMode() -eq $evidence.old_error_mode
    }
    if ($syntaxHandle -ne [IntPtr]::Zero) { [void] [GramlyzeM94ConsoleStart]::CloseHandle($syntaxHandle) }
    if ($apacheHandle -ne [IntPtr]::Zero) { [void] [GramlyzeM94ConsoleStart]::CloseHandle($apacheHandle) }
    $evidence.finished_at_utc = [DateTime]::UtcNow.ToString('o')
    [IO.File]::WriteAllText($resultPath, ($evidence | ConvertTo-Json -Depth 6), [Text.UTF8Encoding]::new($false))
}
$evidence | ConvertTo-Json -Depth 6
Write-Output "Private startup evidence: $resultPath"
if (-not $evidence.success) { throw $evidence.error }
