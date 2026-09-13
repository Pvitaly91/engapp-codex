$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest
$repo = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot '../..'))
. (Join-Path $repo 'tools/diagnostics/xampp-startup/runtime-transaction.ps1')
$private = Join-Path $repo 'storage/app/seo-m9-4-startup-local'
$testRoot = Join-Path $private ('transaction-test-'+[guid]::NewGuid().ToString('N'))
Assert-StartupPath $testRoot
New-Item -ItemType Directory -Path $testRoot | Out-Null
$passed = 0
function New-Fixture([string] $Name, [int] $Count = 3) {
    $directory = Join-Path $testRoot $Name
    New-Item -ItemType Directory -Path $directory | Out-Null
    @(1..$Count | ForEach-Object {
        $source = Join-Path $directory ("candidate-$_.dll")
        $target = Join-Path $directory ("target-$_.dll")
        $backup = Join-Path $directory ("backup-$_.dll")
        [IO.File]::WriteAllText($source,"new-$_")
        [IO.File]::WriteAllText($target,"old-$_")
        [IO.File]::WriteAllText($backup,"old-$_")
        [pscustomobject]@{Source=$source;Target=$target;Backup=$backup;Before=(Get-StartupHash $backup);After=(Get-StartupHash $source)}
    })
}
function Expect-Failure([scriptblock] $Action, [string] $Pattern) {
    try { & $Action } catch {
        if ($_.Exception.Message -notmatch $Pattern) { throw }
        return
    }
    throw 'Expected rejection did not occur'
}
function Assert-Original($Items) {
    foreach ($item in $Items) { if ((Get-StartupHash $item.Target) -ne $item.Before) { throw 'Original target changed' } }
}
try {
    $items = @(New-Fixture 'success')
    Invoke-VerifiedRuntimeReplacement $items
    foreach ($item in $items) { if ((Get-StartupHash $item.Target) -ne $item.After) { throw 'Success did not install all candidates' } }
    $passed++

    $items = @(New-Fixture 'bad-third-source')
    [IO.File]::WriteAllText($items[2].Source,'corrupt')
    Expect-Failure { Invoke-VerifiedRuntimeReplacement $items } 'precondition'
    Assert-Original $items
    $passed++

    $items = @(New-Fixture 'stale-target')
    [IO.File]::WriteAllText($items[1].Target,'external-edit')
    Expect-Failure { Invoke-VerifiedRuntimeReplacement $items } 'precondition'
    if ([IO.File]::ReadAllText($items[1].Target) -cne 'external-edit') { throw 'External edit overwritten' }
    Assert-Original @($items[0],$items[2])
    $passed++

    # A real sharing violation on the third DLL occurs after the first two copies.
    # Rollback must restore the first two and leave the locked original untouched.
    $items = @(New-Fixture 'locked-third')
    $lock = [IO.File]::Open($items[2].Target,[IO.FileMode]::Open,[IO.FileAccess]::Read,[IO.FileShare]::Read)
    try { Expect-Failure { Invoke-VerifiedRuntimeReplacement $items } 'all original files restored' }
    finally { $lock.Dispose() }
    Assert-Original $items
    $passed++

    $items = @(New-Fixture 'damaged-backup')
    [IO.File]::WriteAllText($items[2].Backup,'corrupt')
    Expect-Failure { Invoke-VerifiedRuntimeReplacement $items } 'precondition'
    Assert-Original $items
    $passed++

    $items = @(New-Fixture 'wrong-count')
    Expect-Failure { Invoke-VerifiedRuntimeReplacement @($items[0],$items[1]) } 'Exactly three'
    Assert-Original $items
    $passed++
    $items = @(New-Fixture 'five-file-success' 5)
    Invoke-VerifiedRuntimeReplacement $items
    foreach ($item in $items) { if ((Get-StartupHash $item.Target) -ne $item.After) { throw 'Five-file installation incomplete' } }
    $passed++

    $items = @(New-Fixture 'locked-fifth' 5)
    $lock = [IO.File]::Open($items[4].Target,[IO.FileMode]::Open,[IO.FileAccess]::Read,[IO.FileShare]::Read)
    try { Expect-Failure { Invoke-VerifiedRuntimeReplacement $items } 'all original files restored' }
    finally { $lock.Dispose() }
    Assert-Original $items
    $passed++
    Write-Output "Runtime transaction tests: $passed/8 PASS"
} finally {
    $resolved = [IO.Path]::GetFullPath($testRoot)
    if ([IO.Path]::GetDirectoryName($resolved) -ine $private -or [IO.Path]::GetFileName($resolved) -notmatch '^transaction-test-[0-9a-f]{32}$') { throw 'Unexpected cleanup target' }
    Assert-StartupPath $resolved
    Remove-Item -LiteralPath $resolved -Recurse -Force
}
