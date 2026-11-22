[CmdletBinding()]
param(
    [Parameter(Mandatory)][string] $SealedRoot,
    [string] $UnsealedRoot,
    [ValidateRange(0, 2147483647)][int] $ApachePid = 0,
    [string] $WorkerRoleSid
)

$ErrorActionPreference = 'Stop'
$seal = Join-Path $PSScriptRoot 'seal-release.ps1'

function Assert-Fails([scriptblock] $Operation, [string] $Expected) {
    try { & $Operation | Out-Null } catch {
        if ($_.Exception.Message -notmatch $Expected) { throw }
        return
    }
    throw "Expected a rejection matching $Expected"
}

# Only read-only verification; this test never builds, unseals or deletes a vault.
Assert-Fails { & $seal -Mode Verify -ReleaseRoot $PSScriptRoot } 'unique.*vault'
if ($UnsealedRoot) {
    Assert-Fails { & $seal -Mode Verify -ReleaseRoot $UnsealedRoot } 'protected DACL mismatch'
}
$argsForProof = @{ Mode = 'Verify'; ReleaseRoot = $SealedRoot; ApachePid = $ApachePid }
if ($WorkerRoleSid) {
    Assert-Fails { & $seal -Mode Verify -ReleaseRoot $SealedRoot } 'protected DACL mismatch'
    Assert-Fails { & $seal -Mode Verify -ReleaseRoot $SealedRoot -WorkerRoleSid 'S-1-5-32-544' } 'dedicated synthetic'
    if ($ApachePid) {
        $wrongRole = if ($WorkerRoleSid.EndsWith('-30001')) { '30002' } else { '30001' }
        $wrongSid = $WorkerRoleSid -replace '-[0-9]+$', ('-' + $wrongRole)
        Assert-Fails { & $seal -Mode Verify -ReleaseRoot $SealedRoot -WorkerRoleSid $wrongSid -ApachePid $ApachePid } 'pinned restricting-only role'
    }
    $argsForProof.WorkerRoleSid = $WorkerRoleSid
}
$proof = & $seal @argsForProof | ConvertFrom-Json
if ($proof.schema -ne 'gramlyze.ntfs-seal/v1' -or -not $proof.immutable -or $proof.root -ne $SealedRoot -or
    1 -ne @($proof.inventory | Where-Object { $_.path -eq '.' -and $_.type -eq 'directory' }).Count -or
    0 -eq @($proof.inventory | Where-Object { $_.type -eq 'file' }).Count) {
    throw 'Unexpected sealed-vault verification result.'
}
if ($WorkerRoleSid -and ($proof.workerRoleSid -cne $WorkerRoleSid -or
    ($ApachePid -and ($proof.apacheRestrictingSids.Count -ne 1 -or $proof.apacheRestrictingSids[0] -cne $WorkerRoleSid)))) {
    throw 'The proof did not bind the pinned role to the requested Apache worker.'
}

# Exercise the same native DACL check that Verify uses for actual Apache, with
# the test's own known filtered token. No mutation, HTTP or extra process launch.
$access = [Gramlyze.Perf29.Ntfs+ProcessAccess]::new($PID)
try {
    $parentAcl = (Get-Acl -LiteralPath 'C:\').GetSecurityDescriptorBinaryForm()
    $vaultAcl = (Get-Acl -LiteralPath $SealedRoot).GetSecurityDescriptorBinaryForm()
    if ($access.Allows($parentAcl, 0x40) -or $access.Allows($vaultAcl, 0x4) -or
        $access.Allows($vaultAcl, 0x40000) -or -not $access.Allows($vaultAcl, 1)) {
        throw 'The native access check did not deny replacement and ACL changes while allowing read.'
    }
} finally {
    $access.Dispose()
}

[ordered]@{
    status = 'PASS'; readOnly = $true; entries = $proof.inventory.Count
    apachePid = $proof.apachePid; apacheIntegritySid = $proof.apacheIntegritySid
    workerRoleSid = $proof.workerRoleSid
    testedUnsealedRoot = [bool]$UnsealedRoot
} | ConvertTo-Json -Compress
