Set-StrictMode -Version Latest

function Assert-StartupPath([string] $Path) {
    $cursor = [IO.Path]::GetFullPath($Path)
    while ($cursor) {
        if ((Test-Path -LiteralPath $cursor) -and ((Get-Item -LiteralPath $cursor -Force).Attributes -band [IO.FileAttributes]::ReparsePoint)) {
            throw 'Reparse point refused'
        }
        $cursor = [IO.Path]::GetDirectoryName($cursor)
    }
}

function Get-StartupHash([string] $Path) {
    (Get-FileHash -LiteralPath $Path -Algorithm SHA256 -ErrorAction Stop).Hash.ToLowerInvariant()
}

function Invoke-VerifiedRuntimeReplacement([object[]] $Files) {
    if ($Files.Count -notin @(3,5)) { throw 'Exactly three DLLs or three DLLs plus certificate/key required' }
    if (@($Files.Target | Select-Object -Unique).Count -ne $Files.Count) { throw 'Duplicate target refused' }
    # Validate every input before writing any. The caller must separately ensure that
    # Apache is stopped, the fixed paths/pins are correct, and configs are unchanged.
    foreach ($item in $Files) {
        foreach ($path in @($item.Source, $item.Target, $item.Backup)) { Assert-StartupPath $path }
        if ((Get-StartupHash $item.Source) -ne $item.After -or
            (Get-StartupHash $item.Backup) -ne $item.Before -or
            (Get-StartupHash $item.Target) -ne $item.Before) { throw 'Runtime precondition hash mismatch' }
    }
    try {
        foreach ($item in $Files) {
            Copy-Item -LiteralPath $item.Source -Destination $item.Target -Force -ErrorAction Stop
            if ((Get-StartupHash $item.Target) -ne $item.After) { throw 'Installed file hash mismatch' }
        }
    } catch {
        $applyError = $_
        $rollbackErrors = @()
        foreach ($item in $Files) {
            try {
                if (-not (Test-Path -LiteralPath $item.Target -PathType Leaf) -or (Get-StartupHash $item.Target) -ne $item.Before) {
                    Copy-Item -LiteralPath $item.Backup -Destination $item.Target -Force -ErrorAction Stop
                }
                if ((Get-StartupHash $item.Target) -ne $item.Before) { throw 'Rollback hash mismatch' }
            } catch { $rollbackErrors += $_.Exception.Message }
        }
        if ($rollbackErrors.Count) { throw ('Runtime apply failed; rollback incomplete; keep Apache stopped: ' + ($rollbackErrors -join '; ')) }
        throw ('Runtime apply failed; all original files restored: ' + $applyError.Exception.Message)
    }
}
