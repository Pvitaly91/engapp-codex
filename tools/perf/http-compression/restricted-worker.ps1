[CmdletBinding()]
param(
    [Parameter(Mandatory)][ValidateSet('Start', 'Validate')][string] $Mode,
    [Parameter(Mandatory)][string] $RuntimeRoot,
    [Parameter(Mandatory)][string] $ConfigPath,
    [Parameter(Mandatory)][string] $ConfigSha256,
    [Parameter(Mandatory)][ValidateRange(1024, 65535)][int] $Port,
    [Parameter(Mandatory)][string] $WorkerRoleSid,
    [string] $ImmutableSha256,
    [switch] $ProbeActualWorker,
    [string] $VerifyRoleVault
)

$ErrorActionPreference = 'Stop'
Set-StrictMode -Version 3.0
if ($env:OS -ne 'Windows_NT' -or $RuntimeRoot -cnotmatch '^C:\\Gramlyze\.PERF30\.[a-f0-9]{16,64}\z' -or
    $WorkerRoleSid -cnotmatch '^S-1-5-21-(?:[1-9][0-9]{0,9}-){3}[1-9][0-9]{3,9}\z' -or
    $ConfigSha256 -cnotmatch '^[a-f0-9]{64}\z') {
    throw 'Expected a private C:\Gramlyze.PERF30.<hex> runtime, a dedicated restricting SID and a pinned configuration.'
}
[void][Security.Principal.SecurityIdentifier]::new($WorkerRoleSid)
if ($VerifyRoleVault -and $VerifyRoleVault -cnotmatch '^C:\\Gramlyze\.PERF29\.[0-9a-f]{16,64}\z') {
    throw 'The optional role vault must be private and recognized by the existing seal verifier.'
}
$root = $RuntimeRoot.Replace('\', '/')
$server = Join-Path $RuntimeRoot 'server'
$runtime = Join-Path $RuntimeRoot 'run'
$control = Join-Path $RuntimeRoot 'control'
$tmp = Join-Path $runtime 'tmp'
$exe = Join-Path $server 'bin\httpd.exe'
$config = Join-Path $RuntimeRoot 'server.conf'
$include = Join-Path $RuntimeRoot 'immutable.conf'
$startupLog = Join-Path $runtime 'launch-errors.log'
$stop = Join-Path $control 'stop'
$ready = Join-Path $control 'ready.json'
$finish = Join-Path $control 'finish.json'
$syntax = Join-Path $control 'syntax.json'
$created = Join-Path $control 'process-created.json'
$accessReceipt = Join-Path $control 'access-probes.json'
$vaultReceipt = Join-Path $control 'vault-probe.json'
$probeReceipt = Join-Path $control 'negative-access.json'
$validationCreated = Join-Path $control 'validation-process-created.json'
$exePin = '7bd994ae100f079c7c2e36414ec40c059b38d440f0c48e89c0946896c8b0aeea'
$launcherPin = (Get-FileHash -Algorithm SHA256 -LiteralPath $PSCommandPath).Hash.ToLowerInvariant()
if ([IO.Path]::GetFullPath($ConfigPath) -cne $config) { throw 'The pinned config must be the private server.conf.' }

function Assert-Physical([string] $Path, [bool] $Directory) {
    $item = Get-Item -Force -LiteralPath $Path
    if ($item.PSIsContainer -ne $Directory -or ($item.Attributes -band [IO.FileAttributes]::ReparsePoint) -or
        $item.FullName -cne $Path) { throw ('Unexpected physical path: ' + $Path) }
}
function Read-Pinned([string] $Path, [string] $Pin) {
    Assert-Physical $Path $false
    $data = [IO.File]::ReadAllBytes($Path)
    $actual = [Convert]::ToHexString([Security.Cryptography.SHA256]::HashData($data)).ToLowerInvariant()
    if ($actual -cne $Pin) { throw ('Input changed: ' + $Path) }
    [Text.UTF8Encoding]::new($false, $true).GetString($data)
}
function Read-Directives([string] $Value) {
    if ($Value -match '[\x00-\x08\x0b-\x0c\x0e-\x1f]' -or $Value.Contains('$' + '{')) {
        throw 'Unsupported control characters or variable expansion.'
    }
    $lines = @($Value -split '\r?\n' | ForEach-Object { $_.Trim() } | Where-Object { $_ -and -not $_.StartsWith('#') })
    if (@($lines | Where-Object { $_.EndsWith('\') }).Count) { throw 'Line continuations are not supported.' }
    $lines
}
function Assert-One($Lines, [string] $Name, [string] $Expected) {
    $list = @($Lines | Where-Object { $_ -match ('^' + $Name + '(?:\s|$)') })
    if ($list.Count -ne 1 -or $list[0] -cne $Expected) { throw ('Unexpected ' + $Name) }
}
function Write-Receipt([string] $Path, $Row) {
    $file = [IO.File]::Open($Path, [IO.FileMode]::CreateNew, [IO.FileAccess]::Write, [IO.FileShare]::Read)
    try {
        $bytes = [Text.UTF8Encoding]::new($false).GetBytes(($Row | ConvertTo-Json -Compress -Depth 8) + [Environment]::NewLine)
        $file.Write($bytes, 0, $bytes.Length)
        $file.Flush($true)
    } finally { $file.Dispose() }
}
foreach ($d in @($RuntimeRoot, $server, (Join-Path $server 'bin'), $runtime, $tmp, $control)) {
    Assert-Physical $d $true
}
Assert-Physical $exe $false
if ((Get-FileHash -Algorithm SHA256 -LiteralPath $exe).Hash.ToLowerInvariant() -cne $exePin) {
    throw 'Apache executable differs from the exact XAMPP 2.4.58 PERF-29 binary.'
}
$lines = @(Read-Directives (Read-Pinned $config $ConfigSha256))
Assert-One $lines 'Listen' "Listen 127.0.0.1:$Port"
Assert-One $lines 'ServerRoot' ('ServerRoot "' + $root + '/server"')
Assert-One $lines 'DefaultRuntimeDir' ('DefaultRuntimeDir "' + $root + '/run"')
Assert-One $lines 'PidFile' ('PidFile "' + $root + '/run/httpd.pid"')
if (@($lines | Where-Object { $_ -match '^(?:Define|UnDefine|IncludeOptional|LoadFile|ScriptAlias|Action|ExtFilterDefine)(?:\s|$)' }).Count) {
    throw 'Unsupported dynamic configuration directive.'
}
if (@($lines | Where-Object { $_ -match '^LoadModule(?:\s|$)' -and $_ -cnotmatch '^LoadModule [a-z_]+_module modules/mod_[a-z_]+\.so$' }).Count) {
    throw 'Use only named modules from the staged, pinned private server.'
}
$includes = @($lines | Where-Object { $_ -match '^Include(?:\s|$)' })
if ($includes.Count) {
    if ($includes.Count -ne 1 -or $includes[0] -cne ('Include "' + $root + '/immutable.conf"') -or
        $ImmutableSha256 -cnotmatch '^[a-f0-9]{64}\z') {
        throw 'Only the pinned local immutable include may be used.'
    }
    $rendered = @(Read-Directives (Read-Pinned $include $ImmutableSha256))
    if (@($rendered | Where-Object {
        $_ -cnotmatch '^(?:RewriteEngine|RewriteRule|RewriteCond|Header|AllowOverride|Options|Require|FileETag|SetHandler|AddEncoding|ForceType|</?(?:LocationMatch|Directory|FilesMatch))\b'
    }).Count) { throw 'The include contains directives not produced by immutable delivery.' }
} elseif ($ImmutableSha256) {
    throw 'Unused immutable include digest.'
}
if (@(@($ready, $stop, $finish, $accessReceipt, $vaultReceipt, $probeReceipt, $validationCreated) |
        Where-Object { Test-Path -LiteralPath $_ }).Count) {
    throw 'Each startup needs a private runtime without older startup or fixture-probe receipts.'
}
if ($Mode -eq 'Validate') {
    if ((Test-Path -LiteralPath $syntax) -or (Test-Path -LiteralPath $created)) {
        throw 'Each validation needs a fresh private control directory.'
    }
} else {
    if (-not $ProbeActualWorker) {
        throw 'Start requires the actual suspended-worker fixtures.'
    }
    if ($includes.Count) {
        if (-not $VerifyRoleVault) { throw 'Start with immutable assets requires exact role-vault verification.' }
        $vaultPath = $VerifyRoleVault.Replace('\', '/')
        $quotedVault = [regex]::Escape($vaultPath)
        $releasePattern = $quotedVault + '/gen-[a-f0-9]{32}'
        $directories = @($rendered | Where-Object { $_ -match '^<Directory(?:\s|>)' })
        $rules = @($rendered | Where-Object { $_ -match '^RewriteRule(?:\s|$)' })
        if ($directories.Count -lt 2 -or @($directories | Where-Object {
            $_ -cnotmatch ('^<Directory "' + $releasePattern + '/(?:gzip|identity)">$')
        }).Count -or @($rules | Where-Object {
            $_ -cnotmatch ('^RewriteRule "[^"]+" "(?:-|' + $releasePattern + '/(?:gzip\$1\.gz|identity\$1))" \[[^\]]+\]$')
        }).Count) {
            throw 'All immutable mappings must point inside the verified vault; multi-vault activation is unsupported.'
        }
    }
    Assert-Physical $syntax $false
    Assert-Physical $created $false
    $previous = Get-Content -LiteralPath $syntax -Raw | ConvertFrom-Json
    $started = Get-Content -LiteralPath $created -Raw | ConvertFrom-Json
    if ($previous.schema -cne 'gramlyze.perf30.worker/v1' -or $previous.mode -cne 'Validate' -or
        $previous.phase -cne 'validation-finished' -or $previous.exitCode -ne 0 -or $previous.port -ne $Port -or
        $previous.configSha256 -cne $ConfigSha256 -or $previous.immutableSha256 -cne $ImmutableSha256 -or
        $previous.launcherSha256 -cne $launcherPin -or $previous.executableSha256 -cne $exePin -or
        @($previous.token.RestrictingSids).Count -ne 1 -or $previous.token.RestrictingSids[0] -cne $WorkerRoleSid -or
        $previous.pid -ne $started.pid -or $started.phase -cne 'suspended' -or $started.mode -cne 'Validate') {
        throw 'No successful, matching restricted Apache syntax receipt for this Start.'
    }
    # Keep the syntax process receipt in this private directory, without
    # overwriting it, before issuing the suspended Start process receipt.
    [IO.File]::Move($created, $validationCreated)
}

Add-Type -TypeDefinition @'
using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices;
using System.Security.Principal;
using System.Text;
using Microsoft.Win32.SafeHandles;

namespace Gramlyze.Perf30 {
    public sealed class RestrictedWorker : IDisposable {
        [StructLayout(LayoutKind.Sequential)]
        struct SID_AND_ATTRIBUTES { public IntPtr Sid; public uint Attributes; }
        [StructLayout(LayoutKind.Sequential, CharSet=CharSet.Unicode)]
        struct STARTUPINFO {
            public int cb;
            public string Reserved, Desktop, Title;
            public uint X, Y, XSize, YSize, XChars, YChars, FillAttribute, Flags;
            public ushort ShowWindow, Reserved2;
            public IntPtr ReservedPointer, StdInput, StdOutput, StdError;
        }
        [StructLayout(LayoutKind.Sequential)]
        struct STARTUPINFOEX { public STARTUPINFO Startup; public IntPtr Attributes; }
        [StructLayout(LayoutKind.Sequential)]
        struct PROCESS_INFORMATION { public IntPtr Process, Thread; public uint ProcessId, ThreadId; }
        [StructLayout(LayoutKind.Sequential)]
        struct JOBOBJECT_BASIC_LIMIT_INFORMATION {
            public long PerProcessUserTimeLimit, PerJobUserTimeLimit;
            public uint LimitFlags;
            public UIntPtr MinimumWorkingSetSize, MaximumWorkingSetSize;
            public uint ActiveProcessLimit;
            public UIntPtr Affinity;
            public uint PriorityClass, SchedulingClass;
        }
        [StructLayout(LayoutKind.Sequential)]
        struct JOBOBJECT_EXTENDED_LIMIT_INFORMATION {
            public JOBOBJECT_BASIC_LIMIT_INFORMATION Basic;
            public ulong ReadOps, WriteOps, OtherOps, ReadBytes, WriteBytes, OtherBytes;
            public UIntPtr ProcessMemoryLimit, JobMemoryLimit, PeakProcessMemory, PeakJobMemory;
        }

        [DllImport("kernel32.dll")]
        static extern IntPtr GetCurrentProcess();
        [DllImport("advapi32.dll", SetLastError=true)]
        static extern bool OpenProcessToken(IntPtr process, uint access, out SafeAccessTokenHandle token);
        [DllImport("advapi32.dll", SetLastError=true)]
        static extern bool CreateRestrictedToken(SafeAccessTokenHandle current, uint flags, uint disableCount,
            IntPtr disableSids, uint deleteCount, IntPtr deletePrivileges, uint restrictCount,
            ref SID_AND_ATTRIBUTES restrictSid, out SafeAccessTokenHandle token);
        [DllImport("advapi32.dll", SetLastError=true)]
        static extern bool IsTokenRestricted(SafeAccessTokenHandle token);
        [DllImport("advapi32.dll", SetLastError=true)]
        static extern bool GetTokenInformation(SafeAccessTokenHandle token, int info, IntPtr value, int length, out int needed);
        [DllImport("advapi32.dll", EntryPoint="LookupPrivilegeNameW", CharSet=CharSet.Unicode, SetLastError=true)]
        static extern bool LookupPrivilegeName(string system, IntPtr luid, StringBuilder name, ref int size);
        [DllImport("advapi32.dll", EntryPoint="CreateProcessAsUserW", CharSet=CharSet.Unicode, SetLastError=true)]
        static extern bool CreateProcessAsUser(SafeAccessTokenHandle token, string app, StringBuilder cmd,
            IntPtr processAttrs, IntPtr threadAttrs, bool inherit, uint flags, IntPtr environment, string workingDirectory,
            ref STARTUPINFOEX startup, out PROCESS_INFORMATION child);
        [DllImport("kernel32.dll", SetLastError=true)]
        static extern bool InitializeProcThreadAttributeList(IntPtr values, int count, uint flags, ref IntPtr size);
        [DllImport("kernel32.dll", SetLastError=true)]
        static extern bool UpdateProcThreadAttribute(IntPtr values, uint flags, IntPtr attribute, IntPtr value,
            IntPtr valueSize, IntPtr previous, IntPtr returned);
        [DllImport("kernel32.dll")]
        static extern void DeleteProcThreadAttributeList(IntPtr values);
        [DllImport("kernel32.dll", SetLastError=true)]
        static extern bool GetProcessMitigationPolicy(IntPtr process, int policy, out uint buffer, UIntPtr size);
        [DllImport("kernel32.dll", EntryPoint="CreateJobObjectW", CharSet=CharSet.Unicode, SetLastError=true)]
        static extern IntPtr CreateJobObject(IntPtr security, string name);
        [DllImport("kernel32.dll", SetLastError=true)]
        static extern bool SetInformationJobObject(IntPtr job, int info, ref JOBOBJECT_EXTENDED_LIMIT_INFORMATION value, uint size);
        [DllImport("kernel32.dll", SetLastError=true)]
        static extern bool AssignProcessToJobObject(IntPtr job, IntPtr child);
        [DllImport("kernel32.dll", SetLastError=true)]
        static extern bool CloseHandle(IntPtr handle);
        [DllImport("kernel32.dll", SetLastError=true)]
        static extern bool TerminateProcess(IntPtr process, uint status);
        [DllImport("kernel32.dll", SetLastError=true)]
        static extern uint ResumeThread(IntPtr thread);
        [DllImport("kernel32.dll", SetLastError=true)]
        static extern uint WaitForSingleObject(IntPtr handle, uint milliseconds);
        [DllImport("kernel32.dll", SetLastError=true)]
        static extern bool GetExitCodeProcess(IntPtr process, out uint code);
        [DllImport("kernel32.dll", EntryPoint="CreateFileW", CharSet=CharSet.Unicode, SetLastError=true)]
        static extern SafeFileHandle CreateFile(string path, uint access, uint share,
            IntPtr attrs, uint disposition, uint flags, IntPtr template);

        static Exception Win32(string name) {
            int code = Marshal.GetLastWin32Error();
            return new Win32Exception(code, name + ": Win32 " + code + " (" + new Win32Exception(code).Message + ")");
        }
        static T Info<T>(SafeAccessTokenHandle token, int info, Func<IntPtr,T> read) {
            int n;
            GetTokenInformation(token, info, IntPtr.Zero, 0, out n);
            if (n < 1) throw Win32("GetTokenInformation(size)");
            var p = Marshal.AllocHGlobal(n);
            try {
                if (!GetTokenInformation(token, info, p, n, out n)) throw Win32("GetTokenInformation");
                return read(p);
            } finally { Marshal.FreeHGlobal(p); }
        }
        static string Sid(IntPtr p) { return new SecurityIdentifier(Marshal.ReadIntPtr(p)).Value; }
        static Dictionary<string,uint> Groups(IntPtr p) {
            var rows = new Dictionary<string,uint>();
            for (int i=0; i<Marshal.ReadInt32(p); i++) {
                var entry = IntPtr.Add(p, IntPtr.Size + i * IntPtr.Size * 2);
                rows.Add(Sid(entry), unchecked((uint)Marshal.ReadInt32(entry, IntPtr.Size)));
            }
            return rows;
        }
        static Dictionary<string,uint> Privileges(IntPtr p) {
            var rows = new Dictionary<string,uint>();
            for (int i=0; i<Marshal.ReadInt32(p); i++) {
                var entry = IntPtr.Add(p, 4 + i * 12);
                var name = new StringBuilder(256); int size = name.Capacity;
                if (!LookupPrivilegeName(null, entry, name, ref size)) throw Win32("LookupPrivilegeName");
                rows.Add(name.ToString(), unchecked((uint)Marshal.ReadInt32(entry, 8)));
            }
            return rows;
        }
        public sealed class TokenDetails {
            public string UserSid { get; set; }
            public string IntegritySid { get; set; }
            public bool Elevated { get; set; }
            public string Administrators { get; set; }
            public string[] RestrictingSids { get; set; }
            public string[] PresentPrivileges { get; set; }
            public string[] EnabledPrivileges { get; set; }
        }
        static TokenDetails Read(SafeAccessTokenHandle token) {
            var groups = Info(token, 2, Groups);
            uint admin;
            var adminState = !groups.TryGetValue("S-1-5-32-544", out admin) ? "absent" :
                (admin & 4) != 0 ? "enabled" : (admin & 16) != 0 ? "deny-only" : "disabled";
            var privileges = Info(token, 3, Privileges);
            return new TokenDetails {
                UserSid=Info(token, 1, Sid), IntegritySid=Info(token, 25, Sid),
                Elevated=Info(token, 20, p => Marshal.ReadInt32(p) != 0), Administrators=adminState,
                RestrictingSids=Info(token, 11, Groups).Keys.ToArray(),
                PresentPrivileges=privileges.Keys.ToArray(), EnabledPrivileges=privileges.Where(x => (x.Value & 2) != 0).Select(x => x.Key).ToArray()
            };
        }
        static void AssertWorker(TokenDetails info, string role, string user) {
            if (info.UserSid != user || info.UserSid == role || info.Elevated ||
                (info.Administrators != "absent" && info.Administrators != "deny-only") ||
                (info.IntegritySid != "S-1-16-8192" && info.IntegritySid != "S-1-16-4096") ||
                !info.RestrictingSids.SequenceEqual(new[] { role }) ||
                info.PresentPrivileges.Any(priv => priv != "SeChangeNotifyPrivilege"))
                throw new InvalidOperationException("Apache token does not satisfy the dedicated read-constrained policy.");
        }
        static SafeAccessTokenHandle Restrict(string role, out string user) {
            SafeAccessTokenHandle current;
            if (!OpenProcessToken(GetCurrentProcess(), 0x0f, out current)) throw Win32("OpenProcessToken(parent)");
            using (current) {
                var baseToken = Read(current);
                if (baseToken.UserSid == "S-1-5-18" || baseToken.UserSid == "S-1-5-19" || baseToken.UserSid == "S-1-5-20" ||
                    baseToken.Elevated || (baseToken.Administrators != "absent" && baseToken.Administrators != "deny-only") ||
                    baseToken.IntegritySid != "S-1-16-8192" || IsTokenRestricted(current) ||
                    Info(current, 2, Groups).ContainsKey(role) || baseToken.UserSid == role)
                    throw new InvalidOperationException("Use an ordinary, filtered non-administrative publisher shell.");
                user = baseToken.UserSid;
                var sid = new SecurityIdentifier(role);
                var bytes = new byte[sid.BinaryLength];
                sid.GetBinaryForm(bytes, 0);
                var memory = Marshal.AllocHGlobal(bytes.Length);
                try {
                    Marshal.Copy(bytes, 0, memory, bytes.Length);
                    var row = new SID_AND_ATTRIBUTES { Sid=memory };
                    SafeAccessTokenHandle restricted;
                    // This is full two-pass restriction, not WRITE_RESTRICTED.
                    if (!CreateRestrictedToken(current, 1, 0, IntPtr.Zero, 0, IntPtr.Zero, 1, ref row, out restricted))
                        throw Win32("CreateRestrictedToken");
                    try {
                        if (!IsTokenRestricted(restricted)) throw new InvalidOperationException("Restricted SID was not enforced.");
                        AssertWorker(Read(restricted), role, user);
                        return restricted;
                    } catch { restricted.Dispose(); throw; }
                } finally { Marshal.FreeHGlobal(memory); }
            }
        }
        static int OpenCode(string path, uint access) {
            using (var opened = CreateFile(path, access, 7, IntPtr.Zero, 3, 0x02200000, IntPtr.Zero)) {
                return opened.IsInvalid ? Marshal.GetLastWin32Error() : 0;
            }
        }
        static void AssertPrivateControl(SafeAccessTokenHandle token, string[] controls, string[] readOnly) {
            WindowsIdentity.RunImpersonated(token, () => {
                foreach (string path in controls)
                    if (OpenCode(path, 1) != 5) throw new InvalidOperationException("Worker can read its publisher control directory.");
                foreach (string path in controls.Concat(readOnly)) {
                    foreach (uint mask in new uint[] {2, 4, 0x10, 0x40, 0x100, 0x10000, 0x40000, 0x80000}) {
                        if (OpenCode(path, mask) != 5)
                            throw new InvalidOperationException("Worker write/delete/DACL guard is not proven for " + path + " rights " + mask);
                    }
                }
                foreach (string path in readOnly)
                    if (OpenCode(path, 1) != 0) throw new InvalidOperationException("Worker cannot read " + path);
            });
        }

        IntPtr process, thread, job;
        public uint Pid { get; private set; }
        public TokenDetails Token { get; private set; }
        public bool NoWin32k { get; private set; }
        public bool Wait(uint ms) {
            uint result = WaitForSingleObject(process, ms);
            if (result != 0 && result != 258) throw Win32("WaitForSingleObject");
            return result == 0;
        }
        public uint ExitCode() {
            uint code;
            if (!GetExitCodeProcess(process, out code)) throw Win32("GetExitCodeProcess");
            return code;
        }
        public void Resume() {
            if (thread == IntPtr.Zero) throw new InvalidOperationException("Private Apache was already resumed.");
            uint count = ResumeThread(thread);
            if (count == 0xffffffffu) throw Win32("ResumeThread");
            if (count != 1) throw new InvalidOperationException("Unexpected Apache initial thread suspend count.");
            CloseHandle(thread);
            thread = IntPtr.Zero;
        }
        public void Stop() {
            if (!Wait(0) && !TerminateProcess(process, 0xe030u)) throw Win32("TerminateProcess(own Apache)");
            if (!Wait(10000)) throw new InvalidOperationException("Our private Apache did not finish after stop.");
        }
        public void Dispose() {
            if (job != IntPtr.Zero) { CloseHandle(job); job = IntPtr.Zero; }
            if (thread != IntPtr.Zero) { CloseHandle(thread); thread = IntPtr.Zero; }
            if (process != IntPtr.Zero) { CloseHandle(process); process = IntPtr.Zero; }
        }

        public static RestrictedWorker Launch(string role, string exe, string cmd, string cwd, string env,
            string[] controls, string[] readOnly) {
            string user;
            using (var token = Restrict(role, out user)) {
                AssertPrivateControl(token, controls, readOnly);
                IntPtr size=IntPtr.Zero, attributes=IntPtr.Zero, mitigation=IntPtr.Zero, environment=IntPtr.Zero;
                var child = new PROCESS_INFORMATION();
                bool initialized = false, returned = false;
                var worker = new RestrictedWorker();
                try {
                    InitializeProcThreadAttributeList(IntPtr.Zero, 1, 0, ref size);
                    if (size.ToInt64() < 1) throw Win32("InitializeProcThreadAttributeList(size)");
                    attributes = Marshal.AllocHGlobal(size);
                    if (!InitializeProcThreadAttributeList(attributes, 1, 0, ref size)) throw Win32("InitializeProcThreadAttributeList");
                    initialized = true;
                    mitigation = Marshal.AllocHGlobal(8);
                    // A service-like console child receives no interactive desktop access via Win32k.
                    Marshal.WriteInt64(mitigation, 1L << 28);
                    if (!UpdateProcThreadAttribute(attributes, 0, new IntPtr(0x20007), mitigation, new IntPtr(8), IntPtr.Zero, IntPtr.Zero))
                        throw Win32("UpdateProcThreadAttribute(Win32k disabled)");
                    environment = Marshal.StringToHGlobalUni(env);
                    var si = new STARTUPINFOEX { Attributes=attributes, Startup=new STARTUPINFO { cb=Marshal.SizeOf<STARTUPINFOEX>() } };
                    worker.job = CreateJobObject(IntPtr.Zero, null);
                    if (worker.job == IntPtr.Zero) throw Win32("CreateJobObject");
                    var limits = new JOBOBJECT_EXTENDED_LIMIT_INFORMATION {
                        // ACTIVE_PROCESS | KILL_ON_JOB_CLOSE. The launcher never permits a second child.
                        Basic=new JOBOBJECT_BASIC_LIMIT_INFORMATION { LimitFlags=0x2008, ActiveProcessLimit=1 }
                    };
                    if (!SetInformationJobObject(worker.job, 9, ref limits, (uint)Marshal.SizeOf<JOBOBJECT_EXTENDED_LIMIT_INFORMATION>()))
                        throw Win32("SetInformationJobObject");
                    // CREATE_SUSPENDED | CREATE_UNICODE_ENVIRONMENT | EXTENDED_STARTUPINFO_PRESENT | CREATE_NO_WINDOW
                    if (!CreateProcessAsUser(token, exe, new StringBuilder(cmd), IntPtr.Zero, IntPtr.Zero, false, 0x08080404,
                            environment, cwd, ref si, out child))
                        throw Win32("CreateProcessAsUser(restricted Apache)");
                    worker.process = child.Process;
                    worker.Pid = child.ProcessId;
                    if (!AssignProcessToJobObject(worker.job, child.Process)) throw Win32("AssignProcessToJobObject");
                    SafeAccessTokenHandle primary;
                    if (!OpenProcessToken(child.Process, 8, out primary)) throw Win32("OpenProcessToken(actual Apache)");
                    using (primary) {
                        worker.Token = Read(primary);
                        AssertWorker(worker.Token, role, user);
                    }
                    uint policy;
                    if (!GetProcessMitigationPolicy(child.Process, 4, out policy, new UIntPtr(4))) throw Win32("GetProcessMitigationPolicy");
                    worker.NoWin32k = (policy & 1) != 0;
                    if (!worker.NoWin32k) throw new InvalidOperationException("Win32k restriction was not inherited by Apache.");
                    worker.thread = child.Thread;
                    child.Thread = IntPtr.Zero;
                    returned=true;
                    return worker;
                } finally {
                    if (!returned) {
                        if (child.Process != IntPtr.Zero) TerminateProcess(child.Process, 0xe030u);
                        worker.Dispose();
                    }
                    if (child.Thread != IntPtr.Zero) CloseHandle(child.Thread);
                    if (environment != IntPtr.Zero) Marshal.FreeHGlobal(environment);
                    if (initialized) DeleteProcThreadAttributeList(attributes);
                    if (attributes != IntPtr.Zero) Marshal.FreeHGlobal(attributes);
                    if (mitigation != IntPtr.Zero) Marshal.FreeHGlobal(mitigation);
                }
            }
        }
    }
}
'@

$vars = [ordered]@{
    PATH = "$server\bin;$env:SystemRoot\System32"
    SystemRoot = $env:SystemRoot
    TEMP = $tmp
    TMP = $tmp
    WINDIR = $env:SystemRoot
}
$environment = (($vars.Keys | Sort-Object | ForEach-Object { "$_=$($vars[$_])" }) -join [char]0) + [char]0 + [char]0
$arguments = @(('"' + $exe + '"'), '-d', ('"' + $server + '"'), '-f', ('"' + $config + '"'), '-E', ('"' + $startupLog + '"'))
$arguments += if ($Mode -eq 'Start') { '-X' } else { '-t' }
$readOnly = @($RuntimeRoot, $server, (Join-Path $server 'bin'), $exe, $config)
if ($includes.Count) { $readOnly += $include }

$worker = $null
try {
    $worker = [Gramlyze.Perf30.RestrictedWorker]::Launch($WorkerRoleSid, $exe, ($arguments -join ' '), $server,
        $environment, @($control), $readOnly)
    $receipt = [ordered]@{
        schema='gramlyze.perf30.worker/v1'; mode=$Mode; pid=$worker.Pid; port=$Port; configSha256=$ConfigSha256
        executableSha256=$exePin; immutableSha256=$ImmutableSha256; launcherSha256=$launcherPin
        win32kDisabled=$worker.NoWin32k; token=$worker.Token; phase='suspended'; utc=[DateTime]::UtcNow.ToString('o')
    }
    Write-Receipt $created $receipt
    if ($ProbeActualWorker) {
        $json = & (Join-Path $PSScriptRoot 'restricted-access.test.ps1') -RuntimeRoot $RuntimeRoot -ApachePid $worker.Pid -Suspended
        if (-not $json) { throw 'Actual token fixture test returned no result.' }
        $decoded = $json | ConvertFrom-Json
        Write-Receipt $accessReceipt $decoded
        if (-not $decoded.passed -or -not $decoded.unchanged -or $decoded.pid -ne $worker.Pid) {
            throw 'Actual suspended-worker fixture tests did not pass.'
        }
    }
    if ($VerifyRoleVault) {
        $json = & (Join-Path $PSScriptRoot 'seal-release.ps1') -Mode Verify -ReleaseRoot $VerifyRoleVault -WorkerRoleSid $WorkerRoleSid -ApachePid $worker.Pid
        $decoded = $json | ConvertFrom-Json
        if (-not $decoded.immutable -or $decoded.apachePid -ne $worker.Pid) { throw 'Role vault proof missing for the actual Apache PID.' }
        Write-Receipt $vaultReceipt $decoded
    }
    $worker.Resume()
    $receipt.phase = 'resumed-not-http-ready'
    if ($Mode -eq 'Validate') {
        if (-not $worker.Wait(20000)) { $worker.Stop(); throw 'Syntax check did not finish within 20 seconds.' }
        $receipt.phase = 'validation-finished'
        $receipt.exitCode = $worker.ExitCode()
        Write-Receipt $syntax $receipt
        $receipt | ConvertTo-Json -Compress -Depth 8
        if ($receipt.exitCode -ne 0) { throw ('Private syntax process returned 0x{0:x8}.' -f $receipt.exitCode) }
        return
    }
    Write-Receipt $ready $receipt
    $receipt | ConvertTo-Json -Compress -Depth 8
    $requested = $false
    while (-not $worker.Wait(250)) {
        if (Test-Path -LiteralPath $stop -PathType Leaf) {
            $requested = $true
            $worker.Stop()
            break
        }
    }
    $last = $worker.ExitCode()
    Write-Receipt $finish ([ordered]@{ schema='gramlyze.perf30.worker/v1'; pid=$worker.Pid; requested=$requested
        exitCode=$last; utc=[DateTime]::UtcNow.ToString('o') })
    if (-not $requested) { throw ('Private Apache exited unexpectedly: 0x{0:x8}.' -f $last) }
} finally {
    if ($worker) {
        try {
            if (-not $worker.Wait(0)) { $worker.Stop() }
        } finally { $worker.Dispose() }
    }
}
