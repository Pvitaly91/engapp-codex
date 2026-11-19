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
# Passive Voice task-specific PID/root/loopback-accept corroboration.
# Keep exact bound endpoints/PIDs and independently require a live loopback accept.
# This does not alter the shared M11 inspector or accept an arbitrary remote endpoint.
$listeners = @(Get-NetTCPConnection -LocalPort 80,$DatabasePort | Where-Object {
    $_.RemotePort -eq 0 -and $_.RemoteAddress -in @('0.0.0.0','::') -and
    $_.LocalAddress -in @('0.0.0.0','::','127.0.0.1','::1') -and $_.OwningProcess -gt 0
})
foreach ($port in @(80,$DatabasePort)) {
    $probe = [System.Net.Sockets.TcpClient]::new()
    try {
        $connect = $probe.ConnectAsync('127.0.0.1',$port)
        if (!$connect.Wait(3000) -or !$probe.Connected) { throw 'Local TCP accept unavailable' }
    } finally { $probe.Dispose() }
}
$apacheListeners = @($listeners | Where-Object { $_.LocalPort -eq 80 } | Select-Object -ExpandProperty OwningProcess -Unique)
if ($apacheListeners.Count -ne 1) { throw 'Ambiguous Apache listener' }
$apacheProcess = Get-CimInstance Win32_Process -Filter "ProcessId=$($apacheListeners[0])"
$mysql = @($listeners | Where-Object { $_.LocalPort -eq $DatabasePort } | ForEach-Object {
    $process = Get-CimInstance Win32_Process -Filter "ProcessId=$($_.OwningProcess)"
    @{port=[int]$_.LocalPort;pid=[int]$_.OwningProcess;name=$process.Name}
})
$rawStates = @($listeners | ForEach-Object { [int]$_.CimInstanceProperties['State'].Value })
$addresses = @([System.Net.Dns]::GetHostAddresses('gramlyze.loc') | ForEach-Object { $_.IPAddressToString })
@{addresses=$addresses; document_root=$documentRoot; active_vhosts=$matches.Count;
  apache_name=$apacheProcess.Name; apache_pid=[int]$apacheListeners[0];
  apache_pid_file=[int](Get-Content -LiteralPath "$apacheRoot/logs/httpd.pid" -Raw).Trim();
  mysql_listeners=$mysql; listener_evidence='bound-endpoint-pid-and-live-loopback-connect'; cim_raw_states=$rawStates} | ConvertTo-Json -Depth 5 -Compress






