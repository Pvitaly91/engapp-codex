[CmdletBinding()]
param(
    [Parameter(Mandatory)][ValidateSet('Init', 'Seal', 'Verify')][string] $Mode,
    [Parameter(Mandatory)][string] $ReleaseRoot,
    [ValidateRange(0, 2147483647)][int] $ApachePid = 0
)

$ErrorActionPreference = 'Stop'
Set-StrictMode -Version 3.0

# Intentionally narrow: a mutable ancestor could rename the whole otherwise
# locked tree. Existing XAMPP paths, non-NTFS volumes and links are unsupported.
if ($env:OS -ne 'Windows_NT' -or $ReleaseRoot -cnotmatch '^C:\\Gramlyze\.PERF29\.[0-9a-f]{16,64}\z') {
    throw 'An absolute, unique C:\Gramlyze.PERF29.<16–64 lowercase hex> vault is required.'
}
if ($ApachePid -and $Mode -ne 'Verify') { throw 'ApachePid is available only with Verify.' }

Add-Type -TypeDefinition @'
using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Runtime.InteropServices;
using System.Security.Principal;
using System.Text;
using Microsoft.Win32.SafeHandles;

namespace Gramlyze.Perf29 {
    public static class Ntfs {
        [StructLayout(LayoutKind.Sequential)]
        private struct FileInfo {
            public uint attributes, creationLow, creationHigh, accessLow, accessHigh, writeLow, writeHigh;
            public uint volume, sizeHigh, sizeLow, links, indexHigh, indexLow;
        }
        [StructLayout(LayoutKind.Sequential)]
        private struct GenericMapping {
            public uint read, write, execute, all;
        }
        [DllImport("kernel32.dll", CharSet=CharSet.Unicode, SetLastError=true, ExactSpelling=true)]
        private static extern SafeFileHandle CreateFileW(string path, uint access, uint share, IntPtr security,
            uint disposition, uint flags, IntPtr template);
        [DllImport("kernel32.dll", CharSet=CharSet.Unicode, SetLastError=true, ExactSpelling=true)]
        private static extern bool CreateDirectoryW(string path, IntPtr security);
        [DllImport("kernel32.dll", SetLastError=true)]
        private static extern bool GetFileInformationByHandle(SafeFileHandle handle, out FileInfo info);
        [DllImport("kernel32.dll", SetLastError=true)]
        private static extern SafeProcessHandle OpenProcess(uint access, bool inherit, int pid);
        [DllImport("kernel32.dll")]
        private static extern IntPtr GetCurrentProcess();
        [DllImport("advapi32.dll", SetLastError=true)]
        private static extern bool OpenProcessToken(IntPtr process, uint access, out SafeAccessTokenHandle token);
        [DllImport("advapi32.dll", SetLastError=true)]
        private static extern bool DuplicateToken(SafeAccessTokenHandle token, int level, out SafeAccessTokenHandle duplicate);
        [DllImport("advapi32.dll", SetLastError=true)]
        private static extern bool AccessCheck(byte[] descriptor, SafeAccessTokenHandle token, uint access,
            ref GenericMapping mapping, IntPtr privileges, ref uint size, out uint granted, [MarshalAs(UnmanagedType.Bool)] out bool allowed);
        [DllImport("advapi32.dll", SetLastError=true)]
        private static extern bool GetTokenInformation(SafeAccessTokenHandle token, int part, IntPtr output,
            int length, out int needed);
        [DllImport("advapi32.dll", CharSet=CharSet.Unicode, SetLastError=true, ExactSpelling=true)]
        private static extern bool LookupPrivilegeNameW(string system, IntPtr luid, StringBuilder name, ref int length);

        private static SafeFileHandle Open(string path, uint access, uint share) {
            return CreateFileW(path, access, share, IntPtr.Zero, 3, 0x02200000, IntPtr.Zero);
        }
        public static int TryOpen(string path, uint access) {
            using (var file = Open(path, access, 7)) {
                return file.IsInvalid ? Marshal.GetLastWin32Error() : 0;
            }
        }
        public static void CreateExclusiveDirectory(string path) {
            if (!CreateDirectoryW(path, IntPtr.Zero)) throw new Win32Exception();
        }
        public static uint Inspect(string path, bool readWithoutWriters) {
            // OPEN_REPARSE_POINT: never follow a link, even for metadata. A
            // read-sharing-only file open fails if a writer is still holding it.
            using (var file = Open(path, readWithoutWriters ? 0x81u : 0x80u, readWithoutWriters ? 1u : 7u)) {
                if (file.IsInvalid) throw new Win32Exception();
                FileInfo info;
                if (!GetFileInformationByHandle(file, out info)) throw new Win32Exception();
                if ((info.attributes & 0x400) != 0) throw new InvalidOperationException("reparse point: " + path);
                return info.links;
            }
        }
        private static T Get<T>(SafeAccessTokenHandle token, int part, Func<IntPtr,T> convert) {
            int length;
            GetTokenInformation(token, part, IntPtr.Zero, 0, out length);
            if (length <= 0) throw new Win32Exception();
            IntPtr data = Marshal.AllocHGlobal(length);
            try {
                if (!GetTokenInformation(token, part, data, length, out length)) throw new Win32Exception();
                return convert(data);
            } finally { Marshal.FreeHGlobal(data); }
        }
        private static string Sid(IntPtr data) { return new SecurityIdentifier(Marshal.ReadIntPtr(data)).Value; }
        private static bool HasTrustedGroupEnabled(IntPtr data) {
            int count = Marshal.ReadInt32(data);
            for (int i = 0; i < count; i++) {
                IntPtr entry = IntPtr.Add(data, IntPtr.Size + i * 2 * IntPtr.Size);
                string sid = Sid(entry);
                int attributes = Marshal.ReadInt32(entry, IntPtr.Size);
                if ((attributes & 4) != 0 && (sid == "S-1-5-32-544" || sid == "S-1-5-18")) return true;
            }
            return false;
        }
        private static string[] PrivilegeNames(IntPtr data) {
            var names = new List<string>();
            int count = Marshal.ReadInt32(data);
            for (int i = 0; i < count; i++) {
                int length = 128;
                var name = new StringBuilder(length);
                if (!LookupPrivilegeNameW(null, IntPtr.Add(data, 4 + 12 * i), name, ref length)) throw new Win32Exception();
                names.Add(name.ToString());
            }
            return names.ToArray();
        }
        public sealed class Token {
            public string Sid, IntegritySid;
            public bool Elevated, TrustedGroupEnabled;
            public string[] Privileges;
        }
        private static Token ReadToken(IntPtr process) {
            SafeAccessTokenHandle token;
            if (!OpenProcessToken(process, 8, out token)) throw new Win32Exception();
            using (token) return ReadToken(token);
        }
        private static Token ReadToken(SafeAccessTokenHandle token) {
            return new Token {
                Sid = Get(token, 1, Sid), IntegritySid = Get(token, 25, Sid),
                Elevated = Get(token, 20, p => Marshal.ReadInt32(p) != 0),
                TrustedGroupEnabled = Get(token, 2, HasTrustedGroupEnabled),
                Privileges = Get(token, 3, PrivilegeNames)
            };
        }
        public static Token ReadToken(int pid) {
            if (pid == 0) return ReadToken(GetCurrentProcess());
            using (var process = OpenProcess(0x1000, false, pid)) {
                if (process.IsInvalid) throw new Win32Exception();
                return ReadToken(process.DangerousGetHandle());
            }
        }
        public sealed class ProcessAccess : IDisposable {
            private readonly SafeAccessTokenHandle impersonation;
            public readonly Token PrimaryToken;
            public ProcessAccess(int pid) {
                using (var process = OpenProcess(0x1000, false, pid)) {
                    if (process.IsInvalid) throw new Win32Exception();
                    SafeAccessTokenHandle primary;
                    if (!OpenProcessToken(process.DangerousGetHandle(), 10, out primary)) throw new Win32Exception();
                    using (primary) {
                        PrimaryToken = ReadToken(primary);
                        if (!DuplicateToken(primary, 1, out impersonation)) throw new Win32Exception();
                    }
                }
            }
            // Only an identification-level copy is used, never an impersonation
            // of the caller. This asks Windows for the DACL access decision.
            public bool Allows(byte[] descriptor, uint access) {
                var mapping = new GenericMapping { read = 0x120089, write = 0x120116, execute = 0x1200a0, all = 0x1f01ff };
                uint size = 1024, granted;
                bool allowed;
                IntPtr privileges = Marshal.AllocHGlobal((int)size);
                try {
                    if (!AccessCheck(descriptor, impersonation, access, ref mapping, privileges, ref size, out granted, out allowed))
                        throw new Win32Exception();
                    return allowed;
                } finally { Marshal.FreeHGlobal(privileges); }
            }
            public void Dispose() { impersonation.Dispose(); }
        }
    }
}
'@

$trustedOwners = @('S-1-5-18', 'S-1-5-32-544', 'S-1-5-80-956008885-3418522649-1831038044-1853292631-2271478464')
$dangerous = @('SeTakeOwnershipPrivilege', 'SeBackupPrivilege', 'SeRestorePrivilege', 'SeManageVolumePrivilege',
    'SeDebugPrivilege', 'SeImpersonatePrivilege', 'SeTcbPrivilege', 'SeAssignPrimaryTokenPrivilege', 'SeLoadDriverPrivilege')

function Assert-FilteredToken($Token, [string] $Context) {
    $bypass = @($Token.Privileges | Where-Object { $_ -in $dangerous })
    if ($Token.Elevated -or $Token.TrustedGroupEnabled -or $Token.Sid -in $trustedOwners -or
        $Token.IntegritySid -notin @('S-1-16-4096', 'S-1-16-8192') -or $bypass.Count -ne 0) {
        throw ("{0} can bypass the filtered-user boundary: integrity={1}; elevated={2}; trusted group enabled={3}; trusted owner SID={4}; privileges=[{5}]." -f
            $Context, $Token.IntegritySid, $Token.Elevated, $Token.TrustedGroupEnabled, ($Token.Sid -in $trustedOwners), ($bypass -join ','))
    }
}

function Assert-AccessDenied([string] $Path, [uint32[]] $Accesses, $ApacheAccess) {
    $descriptor = if ($ApacheAccess) { (Get-Acl -LiteralPath $Path).GetSecurityDescriptorBinaryForm() } else { $null }
    foreach ($access in $Accesses) {
        $result = [Gramlyze.Perf29.Ntfs]::TryOpen($Path, $access)
        if ($result -ne 5) {
            throw ('Expected Win32 ACCESS_DENIED for desired access 0x{0:x} on {1}; got {2}.' -f $access, $Path, $result)
        }
        if ($ApacheAccess -and $ApacheAccess.Allows($descriptor, $access)) {
            throw ('Apache DACL grants forbidden access 0x{0:x} on {1}.' -f $access, $Path)
        }
    }
}

function Get-Descriptor([string] $Path) {
    $acl = Get-Acl -LiteralPath $Path
    return [Security.AccessControl.RawSecurityDescriptor]::new($acl.GetSecurityDescriptorBinaryForm(), 0)
}

$token = [Gramlyze.Perf29.Ntfs]::ReadToken(0)
Assert-FilteredToken $token 'Publisher process'
$sid = $token.Sid
$apacheToken = $null
if ($ApachePid) {
    if ((Get-Process -Id $ApachePid).ProcessName -ne 'httpd') { throw 'The supplied PID is not Apache httpd.' }
    $apacheToken = [Gramlyze.Perf29.Ntfs]::ReadToken($ApachePid)
    Assert-FilteredToken $apacheToken "Apache PID $ApachePid"
    if ($apacheToken.Sid -ne $sid) { throw 'Apache has a different user SID from the verified read-only principal.' }
}

$drive = [IO.DriveInfo]::new('C:\')
if ($drive.DriveType -ne [IO.DriveType]::Fixed -or $drive.DriveFormat -ne 'NTFS' -or
    (Get-Descriptor 'C:\').Owner.Value -notin $trustedOwners) {
    throw 'Expected fixed NTFS volume and a trusted owner on the immediate parent.'
}
[void][Gramlyze.Perf29.Ntfs]::Inspect('C:\', $false)
# Parent FILE_DELETE_CHILD is an independent rename/unlink route even if the target denies DELETE.
Assert-AccessDenied 'C:\' @(0x2, 0x10, 0x40, 0x100, 0x10000, 0x40000, 0x80000) $null

if ($Mode -eq 'Init') {
    [Gramlyze.Perf29.Ntfs]::CreateExclusiveDirectory($ReleaseRoot)
    [ordered]@{ root = $ReleaseRoot; immutable = $false } | ConvertTo-Json -Compress
    return
}

function Get-Inventory([string] $Path) {
    $item = Get-Item -Force -LiteralPath $Path
    if ($item.Attributes -band [IO.FileAttributes]::ReparsePoint) { throw "Reparse point in vault: $Path" }
    $linkCount = [Gramlyze.Perf29.Ntfs]::Inspect($item.FullName, ($Mode -eq 'Verify' -and -not $item.PSIsContainer))
    if (-not $item.PSIsContainer -and $linkCount -ne 1) { throw "Non-exclusive file link: $Path" }
    if ($item.PSIsContainer) {
        foreach ($child in (Get-ChildItem -Force -LiteralPath $item.FullName | Sort-Object Name)) {
            Get-Inventory $child.FullName
        }
    }
    $item # Descendants first so the vault is sealed last.
}

$items = @(Get-Inventory $ReleaseRoot)
if (-not $items[-1].PSIsContainer -or $items.Count -le 1) { throw 'Vault must contain a prepared tree.' }
$ownerRights = 'S-1-3-4'
$expected = @{ $ownerRights = 0x20000; $sid = 0x1200a9; 'S-1-5-18' = 0x1f01ff; 'S-1-5-32-544' = 0x1f01ff }

if ($Mode -eq 'Seal') {
    foreach ($item in $items) {
        if ((Get-Descriptor $item.FullName).Owner.Value -ne $sid) { throw "Unexpected owner, refusing to seal: $($item.FullName)" }
    }
    # OWNER RIGHTS limits the owner's implicit WRITE_DAC; inherited ACEs are removed.
    $dacl = "D:P(A;;RC;;;$ownerRights)(A;;0x1200a9;;;$sid)(A;;FA;;;SY)(A;;FA;;;BA)"
    foreach ($item in $items) {
        $acl = Get-Acl -LiteralPath $item.FullName
        $acl.SetSecurityDescriptorSddlForm($dacl, [Security.AccessControl.AccessControlSections]::Access)
        Set-Acl -LiteralPath $item.FullName -AclObject $acl
    }
    $Mode = 'Verify'
    $items = @(Get-Inventory $ReleaseRoot)
}

$apacheAccess = if ($ApachePid) { [Gramlyze.Perf29.Ntfs+ProcessAccess]::new($ApachePid) } else { $null }
try {
    if ($apacheAccess) {
        $apacheToken = $apacheAccess.PrimaryToken
        Assert-FilteredToken $apacheToken "Apache PID $ApachePid during DACL check"
        if ($apacheToken.Sid -ne $sid) { throw 'Apache token SID changed before its DACL check.' }
        Assert-AccessDenied 'C:\' @(0x2, 0x10, 0x40, 0x100, 0x10000, 0x40000, 0x80000) $apacheAccess
    }
    $inventory = foreach ($item in $items) {
        $path = $item.FullName
        $acl = Get-Descriptor $path
        if ($acl.Owner.Value -ne $sid -or
            -not ($acl.ControlFlags -band [Security.AccessControl.ControlFlags]::DiscretionaryAclProtected) -or
            $null -eq $acl.DiscretionaryAcl -or $acl.DiscretionaryAcl.Count -ne $expected.Count) {
            throw "Owner or protected DACL mismatch: $path"
        }
        $seen = @{}
        foreach ($ace in $acl.DiscretionaryAcl) {
            if ($ace -isnot [Security.AccessControl.CommonAce] -or
                $ace.AceType -ne [Security.AccessControl.AceType]::AccessAllowed -or
                $ace.AceFlags -ne [Security.AccessControl.AceFlags]::None) { throw "Unexpected access rule: $path" }
            $ruleSid = $ace.SecurityIdentifier.Value
            if ($seen.ContainsKey($ruleSid) -or -not $expected.ContainsKey($ruleSid) -or
                $ace.AccessMask -ne $expected[$ruleSid]) { throw "Unexpected access mask: $path" }
            $seen[$ruleSid] = $true
        }
        $accesses = @(0x2, 0x4, 0x10, 0x100, 0x10000, 0x40000, 0x80000)
        if ($item.PSIsContainer) { $accesses += 0x40 }
        Assert-AccessDenied $path $accesses $apacheAccess
        if ([Gramlyze.Perf29.Ntfs]::TryOpen($path, 1) -ne 0) { throw "Read/list open failed: $path" }
        if ($apacheAccess -and -not $apacheAccess.Allows((Get-Acl -LiteralPath $path).GetSecurityDescriptorBinaryForm(), 1)) {
            throw "Apache DACL does not grant read/list access: $path"
        }
        [ordered]@{
            path = if ($path -eq $ReleaseRoot) { '.' } else { $path.Substring($ReleaseRoot.Length + 1).Replace('\', '/') }
            type = if ($item.PSIsContainer) { 'directory' } else { 'file' }
        }
    }
} finally {
    if ($apacheAccess) { $apacheAccess.Dispose() }
}

[ordered]@{
    schema = 'gramlyze.ntfs-seal/v1'; root = $ReleaseRoot; sid = $sid; integritySid = $token.IntegritySid
    principal = 'current-process'; immutable = $true; inventory = @($inventory)
    apachePid = if ($ApachePid) { $ApachePid } else { $null }
    apacheIntegritySid = if ($apacheToken) { $apacheToken.IntegritySid } else { $null }
} | ConvertTo-Json -Compress -Depth 4
