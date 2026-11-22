# Real Win32 operations on disposable fixtures, using the actual Apache
# worker's token. No live paths, elevated token or arbitrary command execution.
[CmdletBinding()]
param(
    [Parameter(Mandatory)][string] $RuntimeRoot,
    [Parameter(Mandatory)][ValidateRange(1,2147483647)][int] $ApachePid,
    [switch] $Suspended
)
Set-StrictMode -Version 3.0
$ErrorActionPreference = 'Stop'
if ($RuntimeRoot -cnotmatch '^C:\\Gramlyze\.PERF30\.[a-f0-9]{16,64}$') { throw 'Expected private PERF-30 runtime.' }
$receiptFile = if ($Suspended) { 'control/process-created.json' } else { 'control/ready.json' }
$receipt = Get-Content -Raw -LiteralPath (Join-Path $RuntimeRoot $receiptFile) | ConvertFrom-Json
if ($receipt.pid -ne $ApachePid -or ($Suspended -and $receipt.phase -cne 'suspended')) {
    throw 'PID or phase does not match the private launch receipt.'
}
$fixtures = Join-Path $RuntimeRoot 'negative'
foreach ($item in @((Get-Item -LiteralPath $RuntimeRoot), (Get-Item -LiteralPath $fixtures)) + @(Get-ChildItem -LiteralPath $fixtures -Force)) {
    if (($item.Attributes -band [IO.FileAttributes]::ReparsePoint) -or $item.LinkType -eq 'HardLink') {
        throw 'Unsafe private fixture.'
    }
}

Add-Type -TypeDefinition @'
using System;
using System.IO;
using System.Text;
using System.Security.Principal;
using System.Runtime.InteropServices;
using System.ComponentModel;
using System.Collections.Generic;
using Microsoft.Win32.SafeHandles;

namespace Gramlyze.Perf30 {
  public static class WorkerProbe {
    [DllImport("kernel32.dll", SetLastError=true)] static extern IntPtr OpenProcess(uint rights, bool inherit, int pid);
    [DllImport("kernel32.dll", SetLastError=true)] static extern bool CloseHandle(IntPtr h);
    [DllImport("kernel32.dll", SetLastError=true, CharSet=CharSet.Unicode)]
    static extern bool QueryFullProcessImageName(IntPtr h, int flags, StringBuilder buf, ref int size);
    [DllImport("advapi32.dll", SetLastError=true)]
    static extern bool OpenProcessToken(IntPtr proc, uint desired, out SafeAccessTokenHandle t);
    [DllImport("advapi32.dll", SetLastError=true)]
    static extern bool DuplicateTokenEx(SafeAccessTokenHandle src, uint desired, IntPtr attrs,
        int level, int type, out SafeAccessTokenHandle dest);
    [DllImport("advapi32.dll")] static extern bool IsTokenRestricted(SafeAccessTokenHandle t);
    [DllImport("kernel32.dll", SetLastError=true, CharSet=CharSet.Unicode)]
    static extern SafeFileHandle CreateFile(string name, uint rights, uint share,
        IntPtr sa, uint disposition, uint flags, IntPtr template);
    [DllImport("kernel32.dll", SetLastError=true, CharSet=CharSet.Unicode)]
    static extern bool DeleteFile(string name);
    [DllImport("kernel32.dll", SetLastError=true, CharSet=CharSet.Unicode)]
    static extern bool MoveFileEx(string name, string to, uint flags);
    [DllImport("kernel32.dll", SetLastError=true, CharSet=CharSet.Unicode)]
    static extern bool CreateHardLink(string name, string target, IntPtr sa);
    [DllImport("kernel32.dll", SetLastError=true, CharSet=CharSet.Unicode)]
    [return: MarshalAs(UnmanagedType.I1)]
    static extern bool CreateSymbolicLink(string name, string target, uint flags);
    [DllImport("kernel32.dll", SetLastError=true)] static extern bool SetFileTime(SafeFileHandle h, IntPtr c, IntPtr a, ref long last);
    [DllImport("advapi32.dll", CharSet=CharSet.Unicode)]
    static extern uint GetNamedSecurityInfo(string name, uint type, uint infos, out IntPtr owner,
      out IntPtr group, out IntPtr dacl, out IntPtr sacl, out IntPtr descriptor);
    [DllImport("advapi32.dll", CharSet=CharSet.Unicode)]
    static extern uint SetNamedSecurityInfo(string name, uint type, uint infos, IntPtr owner,
      IntPtr group, IntPtr dacl, IntPtr sacl);
    [DllImport("kernel32.dll")] static extern IntPtr LocalFree(IntPtr descriptor);

    public class Row {
      public string operation { get; set; }
      public uint win32 { get; set; }
      public bool expected { get; set; }
    }
    static uint Result(bool ok) { return ok ? 0u : (uint) Marshal.GetLastWin32Error(); }
    static uint Open(string file, uint rights, uint create = 3) {
      using(var h = CreateFile(file, rights, 7, IntPtr.Zero, create, 0x02000000u, IntPtr.Zero))
        return Result(!h.IsInvalid);
    }
    static uint Timestamp(string file) {
      using(var h = CreateFile(file, 0x100, 7, IntPtr.Zero, 3, 0, IntPtr.Zero)) {
        if(h.IsInvalid) return (uint) Marshal.GetLastWin32Error();
        long last = DateTime.UtcNow.AddMinutes(-2).ToFileTimeUtc();
        return Result(SetFileTime(h, IntPtr.Zero, IntPtr.Zero, ref last));
      }
    }
    static bool Add(List<Row> rows, string operation, uint result, params uint[] wanted) {
      var ok = Array.IndexOf(wanted, result) >= 0;
      rows.Add(new Row { operation=operation, win32=result, expected=ok });
      return ok;
    }
    public static List<Row> Run(int pid, string root) {
      var proc = OpenProcess(0x1000u, false, pid);
      if(proc == IntPtr.Zero) throw new Win32Exception(Marshal.GetLastWin32Error());
      IntPtr descriptor = IntPtr.Zero;
      try {
        int size = 1024;
        var image = new StringBuilder(size);
        if(!QueryFullProcessImageName(proc, 0, image, ref size))
          throw new Win32Exception(Marshal.GetLastWin32Error());
        if(!String.Equals(image.ToString(), Path.Combine(root, @"server\bin\httpd.exe"), StringComparison.OrdinalIgnoreCase))
          throw new InvalidOperationException("Not the private Apache image.");
        if(!OpenProcessToken(proc, 0xau, out var token)) throw new Win32Exception(Marshal.GetLastWin32Error());
        using(token) {
          if(!IsTokenRestricted(token)) throw new InvalidOperationException("Exact worker token is not restricted.");
          if(!DuplicateTokenEx(token, 0xcu, IntPtr.Zero, 2, 2, out var borrowed))
            throw new Win32Exception(Marshal.GetLastWin32Error());
          using(borrowed) {
            var dir = Path.Combine(root, "negative");
            var body = Path.Combine(dir, "body.css");
            var gz = Path.Combine(dir, "body.css.gz");
            var donor = Path.Combine(root, @"run\probe-donor.tmp");
            using(var d = File.Open(donor, FileMode.CreateNew)) d.WriteByte(42);
            var rc = GetNamedSecurityInfo(body, 1, 5, out var owner, out var group, out var dacl, out var sacl, out descriptor);
            if(rc != 0) throw new Win32Exception((int)rc);
            return WindowsIdentity.RunImpersonated(borrowed, () => {
              var rows = new List<Row>();
              if(!Add(rows, "read CSS", Open(body, 1), 0)) return rows;
              if(!Add(rows, "read gzip", Open(gz, 1), 0)) return rows;
              if(!Add(rows, "read synthetic publisher-only fixture", Open(Path.Combine(root, @"control\publisher-only.txt"), 1), 5)) return rows;
              if(!Add(rows, "create allowed private runtime file", Open(Path.Combine(root, @"run\worker-proof.tmp"), 2, 1), 0)) return rows;
              if(!Add(rows, "open config for write", Open(Path.Combine(root, "server.conf"), 2), 5)) return rows;
              if(!Add(rows, "write immutable fixture", Open(body, 2), 5)) return rows;
              if(!Add(rows, "append immutable fixture", Open(body, 4), 5)) return rows;
              if(!Add(rows, "replace sidecar", Result(MoveFileEx(donor, gz, 1)), 5)) return rows;
              if(!Add(rows, "delete immutable fixture", Result(DeleteFile(body)), 5)) return rows;
              if(!Add(rows, "rename immutable fixture", Result(MoveFileEx(body, body + ".moved", 0)), 5)) return rows;
              if(!Add(rows, "change timestamp", Timestamp(body), 5)) return rows;
              if(!Add(rows, "set fixture DACL", SetNamedSecurityInfo(body, 1, 4, IntPtr.Zero, IntPtr.Zero, dacl, IntPtr.Zero), 5)) return rows;
              if(!Add(rows, "set fixture owner", SetNamedSecurityInfo(body, 1, 1, owner, IntPtr.Zero, IntPtr.Zero, IntPtr.Zero), 5, 1307, 1314)) return rows;
              if(!Add(rows, "create hardlink in sealed parent", Result(CreateHardLink(Path.Combine(dir, "hardlink.dat"), donor, IntPtr.Zero)), 5)) return rows;
              if(!Add(rows, "create file reparse link in sealed parent", Result(CreateSymbolicLink(Path.Combine(dir, "reparse.dat"), donor, 2)), 5, 1314)) return rows;
              if(!Add(rows, "open sealed parent for create", Open(dir, 2), 5)) return rows;
              if(!Add(rows, "open sealed parent for delete-child", Open(dir, 0x40), 5)) return rows;
              if(!Add(rows, "open sealed parent for changing DACL", Open(dir, 0x40000), 5)) return rows;
              Add(rows, "open sealed parent for changing owner", Open(dir, 0x80000), 5);
              return rows;
            });
          }
        }
      } finally {
        if(descriptor != IntPtr.Zero) LocalFree(descriptor);
        CloseHandle(proc);
      }
    }
  }
}
'@

function Inventory {
    @(Get-ChildItem -LiteralPath $fixtures -File -Force | Sort-Object Name | ForEach-Object {
        [ordered]@{name=$_.Name; updated=$_.LastWriteTimeUtc.Ticks;
            sha=(Get-FileHash -LiteralPath $_.FullName -Algorithm SHA256).Hash}
    }) | ConvertTo-Json -Compress
}
$before = Inventory
$rows = @([Gramlyze.Perf30.WorkerProbe]::Run($ApachePid, $RuntimeRoot))
$after = Inventory
$ok = $rows.Count -eq 19 -and @($rows | Where-Object { -not $_.expected }).Count -eq 0 -and $before -ceq $after
$result = [ordered]@{schema='gramlyze-perf30-actual-worker-access/v1'; pid=$ApachePid;
    passed=$ok; unchanged=($before -ceq $after); operations=$rows} | ConvertTo-Json -Depth 4
$out = Join-Path $RuntimeRoot 'control/negative-access.json'
if (Test-Path -LiteralPath $out) { throw 'A receipt already exists; do not overwrite it.' }
[IO.File]::WriteAllText($out, $result)
$result
if (-not $ok) { exit 1 }
