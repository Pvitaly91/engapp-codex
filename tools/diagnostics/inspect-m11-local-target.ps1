param([Parameter(Mandatory=$true)][ValidateRange(1,65535)][int]$DatabasePort)
$ErrorActionPreference = 'Stop'
$root = 'd:/dev/htdocs/gramlyze.loc'
$apacheRoot = 'C:/Program Files/xampp/apache'
$info = New-Object System.Diagnostics.ProcessStartInfo
$info.FileName = "$apacheRoot/bin/httpd.exe"
$info.Arguments = '-S'
$info.UseShellExecute = $false
$info.CreateNoWindow = $true
$info.RedirectStandardOutput = $true
$info.RedirectStandardError = $true
$apacheCheck = [System.Diagnostics.Process]::Start($info)
$dump = $apacheCheck.StandardOutput.ReadToEnd() + $apacheCheck.StandardError.ReadToEnd()
if (!$apacheCheck.WaitForExit(10000) -or $apacheCheck.ExitCode -ne 0) { throw 'Apache config cannot be verified' }
$matches = [regex]::Matches($dump, 'port 80 namevhost gramlyze\.loc \((.+):([0-9]+)\)')
if ($matches.Count -ne 1) { throw 'Ambiguous/missing gramlyze.loc vhost' }
$match = $matches[0]
$lines = Get-Content -LiteralPath $match.Groups[1].Value
$start = [int]$match.Groups[2].Value - 1
$block = [regex]::Split(($lines[$start..($lines.Count-1)] -join "`n"), '</VirtualHost>', 2)[0]
$documentRoot = [regex]::Match($block, '(?m)^\s*DocumentRoot\s+"([^"]+)"\s*$').Groups[1].Value
if (!$documentRoot) { throw 'Missing vhost DocumentRoot' }
$documentRoot = (Resolve-Path -LiteralPath $documentRoot).Path.Replace('\','/').ToLowerInvariant().TrimEnd('/')
$listeners = @(Get-NetTCPConnection -State Listen)
$apacheListeners = @($listeners | Where-Object { $_.LocalPort -eq 80 } | Select-Object -ExpandProperty OwningProcess -Unique)
if ($apacheListeners.Count -ne 1) { throw 'Ambiguous Apache listener' }
$apacheProcess = Get-CimInstance Win32_Process -Filter "ProcessId=$($apacheListeners[0])"
$mysql = @($listeners | Where-Object { $_.LocalPort -eq $DatabasePort } | ForEach-Object {
    $process = Get-CimInstance Win32_Process -Filter "ProcessId=$($_.OwningProcess)"
    @{port=[int]$_.LocalPort;pid=[int]$_.OwningProcess;name=$process.Name}
})
$addresses = @([System.Net.Dns]::GetHostAddresses('gramlyze.loc') | ForEach-Object { $_.IPAddressToString })
@{addresses=$addresses; document_root=$documentRoot; active_vhosts=$matches.Count;
  apache_name=$apacheProcess.Name; apache_pid=[int]$apacheListeners[0];
  apache_pid_file=[int](Get-Content -LiteralPath "$apacheRoot/logs/httpd.pid" -Raw).Trim();
  mysql_listeners=$mysql} | ConvertTo-Json -Depth 5 -Compress
