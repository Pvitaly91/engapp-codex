[CmdletBinding()]
param(
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]] $ComposerArgs
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

# This launcher deliberately keeps Composer itself out of version control. The
# verified PHAR is project-private and ignored by Git; see the M9.3 acceptance
# report for its provenance and hash.
$RepositoryRoot = Split-Path -Parent $PSScriptRoot
$ComposerPhar = Join-Path $RepositoryRoot 'storage\app\gramlyze-composer\composer-2.10.3.phar'
$ExpectedComposerSha256 = '7A2D379D5B8FFDAA028580EF26494C36D2FEEF4B178D3DD1473A4DBC5E17C8D6'
$ExpectedComposerVersion = '2.10.3'

$PhpExecutable = $env:GRAMLYZE_PHP
if ([string]::IsNullOrWhiteSpace($PhpExecutable)) {
    $PhpExecutable = Join-Path $env:ProgramFiles 'xampp\php\php.exe'
}

function Stop-ComposerLauncher([string] $Message) {
    [Console]::Error.WriteLine("Gramlyze Composer launcher: $Message")
    exit 1
}

function Get-Sha256([string] $Path) {
    $algorithm = [System.Security.Cryptography.SHA256]::Create()
    $stream = [System.IO.File]::OpenRead($Path)

    try {
        return ([System.BitConverter]::ToString($algorithm.ComputeHash($stream))).Replace('-', '')
    }
    finally {
        $stream.Dispose()
        $algorithm.Dispose()
    }
}

if (-not (Test-Path -LiteralPath $PhpExecutable -PathType Leaf)) {
    Stop-ComposerLauncher "XAMPP PHP was not found at '$PhpExecutable'. Set GRAMLYZE_PHP to the active XAMPP php.exe and retry."
}

if (-not (Test-Path -LiteralPath $ComposerPhar -PathType Leaf)) {
    Stop-ComposerLauncher "The verified Composer $ExpectedComposerVersion PHAR is missing at '$ComposerPhar'. Restore it from the approved local setup before running Composer."
}

$actualHash = Get-Sha256 $ComposerPhar
if ($actualHash -ne $ExpectedComposerSha256) {
    Stop-ComposerLauncher "The Composer PHAR checksum does not match the recorded Composer $ExpectedComposerVersion checksum."
}

$phpVersion = (& $PhpExecutable -r 'echo PHP_VERSION;').Trim()
if ($LASTEXITCODE -ne 0 -or $phpVersion -notmatch '^8\.5\.\d+$') {
    Stop-ComposerLauncher "The selected PHP must be XAMPP PHP 8.5.x; received '$phpVersion'."
}

$composerStderr = [System.IO.Path]::GetTempFileName()
$originalErrorActionPreference = $ErrorActionPreference
try {
    # Composer intentionally prints PHP diagnostics on STDERR even when its
    # version command succeeds. Do not turn that native STDERR into a
    # PowerShell exception before we can inspect the process exit code.
    $ErrorActionPreference = 'Continue'
    $composerStdout = (& $PhpExecutable $ComposerPhar --version --no-ansi 2> $composerStderr | Out-String).Trim()
    $composerExitCode = $LASTEXITCODE
    $composerVersionOutput = ($composerStdout + "`n" + ((Get-Content -LiteralPath $composerStderr | Out-String).Trim())).Trim()
}
finally {
    $ErrorActionPreference = $originalErrorActionPreference
    Remove-Item -LiteralPath $composerStderr -Force -ErrorAction SilentlyContinue
}

if ($composerExitCode -ne 0 -or $composerVersionOutput -notmatch "Composer version $([regex]::Escape($ExpectedComposerVersion))(?:\s|$)") {
    Stop-ComposerLauncher "The verified PHAR did not report Composer $ExpectedComposerVersion."
}

$originalErrorActionPreference = $ErrorActionPreference
try {
    $ErrorActionPreference = 'Continue'
    & $PhpExecutable $ComposerPhar @ComposerArgs
    $composerCommandExitCode = $LASTEXITCODE
}
finally {
    $ErrorActionPreference = $originalErrorActionPreference
}

exit $composerCommandExitCode
