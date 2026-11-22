# Private PERF-30 Apache runtime ACLs, not a replacement for seal-release.ps1.
[CmdletBinding()]
param(
    [Parameter(Mandatory)][string] $RuntimeRoot,
    [Parameter(Mandatory)][string] $WorkerRoleSid
)

Set-StrictMode -Version 3.0
$ErrorActionPreference = 'Stop'
if ($RuntimeRoot -cnotmatch '^C:\\Gramlyze\.PERF30\.[a-f0-9]{16,64}$') { throw 'Expected an exclusive private C: runtime.' }
if ($WorkerRoleSid -cnotmatch '^S-1-5-21-(?:[1-9][0-9]{0,9}-){3}[1-9][0-9]{0,9}$') { throw 'Expected a unique restricting role SID.' }
$identity = [Security.Principal.WindowsIdentity]::GetCurrent()
$owner = $identity.User.Value
$role = [Security.Principal.SecurityIdentifier]::new($WorkerRoleSid)
if ($role.Value -eq $owner -or $role.AccountDomainSid.Value -eq $identity.User.AccountDomainSid.Value) {
    throw 'A restricting-only role must not be a local account or group.'
}
$admins = [Security.Principal.SecurityIdentifier]::new('S-1-5-32-544')
$security = [Security.Principal.WindowsPrincipal]::new($identity)
if ($security.IsInRole($admins)) { throw 'Run as an ordinary, non-elevated publisher.' }
if ([IO.DriveInfo]::new('C:\').DriveFormat -ne 'NTFS') { throw 'The private volume must be NTFS.' }
if (-not (Test-Path -LiteralPath (Join-Path $RuntimeRoot 'control/private-runtime.json') -PathType Leaf)) {
    throw 'No private runtime staging receipt.'
}

function Tree([string] $LiteralPath) {
    $item = Get-Item -LiteralPath $LiteralPath -Force
    if (($item.Attributes -band [IO.FileAttributes]::ReparsePoint) -or
        (-not $item.PSIsContainer -and $item.LinkType -eq 'HardLink')) {
        throw "No reparse points or hard links in the private runtime: $LiteralPath"
    }
    if ($item.PSIsContainer) {
        foreach ($child in Get-ChildItem -LiteralPath $LiteralPath -Force) { Tree $child.FullName }
    }
    $item
}

function Descriptor($Item) {
    Get-Acl -LiteralPath $Item.FullName
}

$items = @(Tree $RuntimeRoot)
foreach ($item in $items) {
    if ((Descriptor $item).GetOwner([Security.Principal.SecurityIdentifier]).Value -ne $owner) {
        throw "Publisher does not own $($item.FullName)"
    }
}

$rows = foreach ($item in $items) {
    $relative = if ($item.FullName -eq $RuntimeRoot) { '.' } else { $item.FullName.Substring($RuntimeRoot.Length + 1) }
    $access = 'read'
    $base = "D:P(A;;RC;;;OW)(A;;FA;;;SY)(A;;FA;;;BA)(A;;FA;;;$owner)"
    if ($relative -eq 'control' -or $relative.StartsWith('control\', [StringComparison]::OrdinalIgnoreCase)) {
        $access = 'publisher-only'
        $sddl = $base
    } elseif ($relative -eq 'run' -or $relative.StartsWith('run\', [StringComparison]::OrdinalIgnoreCase)) {
        $access = 'runtime'
        # The role may create its own operational files in run (no WRITE_DAC
        # or WRITE_OWNER on the runtime directories themselves). No directives
        # can be read from this writable part of the tree.
        $inheritance = if ($item.PSIsContainer) { 'OICI' } else { '' }
        $sddl = $base + "(A;${inheritance};0x1301bf;;;$WorkerRoleSid)"
        if ($item.PSIsContainer) {
            $sddl += "(A;OICIIO;FA;;;$owner)(A;OICIIO;RC;;;OW)(A;OICIIO;FA;;;SY)(A;OICIIO;FA;;;BA)"
        }
    } else {
        $sddl = $base + "(A;;0x1200a9;;;$WorkerRoleSid)"
    }
    $acl = Descriptor $item
    $acl.SetSecurityDescriptorSddlForm($sddl, [Security.AccessControl.AccessControlSections]::Access)
    Set-Acl -LiteralPath $item.FullName -AclObject $acl
    $final = Descriptor $item
    if (-not $final.AreAccessRulesProtected) { throw "Private DACL not protected: $($item.FullName)" }
    $rules = @($final.GetAccessRules($true, $true, [Security.Principal.SecurityIdentifier]) |
        Where-Object { $_.IdentityReference.Value -eq $WorkerRoleSid })
    switch ($access) {
        'publisher-only' { if ($rules.Count) { throw "Role can read publisher-only $relative" } }
        'runtime' { if ($rules.Count -ne 1 -or [int]$rules[0].FileSystemRights -ne 0x1301bf) { throw "Role DACL drift: $relative" } }
        'read' { if ($rules.Count -ne 1 -or [int]$rules[0].FileSystemRights -ne 0x1200a9) { throw "Role DACL drift: $relative" } }
    }
    [ordered]@{path = $relative.Replace('\', '/'); access = $access}
}
[ordered]@{schema='gramlyze-perf30-runtime-acl/v1'; root=$RuntimeRoot; roleSid=$WorkerRoleSid; objects=@($rows)} |
    ConvertTo-Json -Depth 4 -Compress
