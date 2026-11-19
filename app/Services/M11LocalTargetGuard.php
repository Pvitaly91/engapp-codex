<?php

namespace App\Services;

use Illuminate\Database\Connection;
use PDO;
use RuntimeException;
use Symfony\Component\Process\Process;

/** Explicit M11-only opt-in for the verified working Windows vhost; never a force flag. */
class M11LocalTargetGuard
{
    public const ROOT = 'D:/DEV/htdocs/gramlyze.loc';
    protected const LABEL = 'M11';
    protected const PRIVATE_DIRECTORY = 'seo-m11-local';
    protected const ENDPOINT = 'm11-target-';

    public function verify(Connection $db, string $target, string $privateDirectory, ?string $proofName, array $physical): void
    {
        if ($target !== 'gramlyze.loc' || PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows') {
            throw new RuntimeException(static::LABEL.' local-target requires CLI on this Windows gramlyze.loc copy.');
        }
        $root = $this->path(self::ROOT);
        if ($this->path(base_path()) !== $root || $this->path(app()->environmentPath()) !== $root
            || $this->path($privateDirectory) !== $root.'/storage/app/'.static::PRIVATE_DIRECTORY) {
            throw new RuntimeException(static::LABEL.' working checkout/environment/storage does not match the vhost target.');
        }
        if ($db->getDriverName() !== 'mysql' || $db->getConfig('url') || $db->getConfig('unix_socket')
            || $db->getConfig('read') || $db->getConfig('write') || $db->getReadPdo() !== $db->getPdo()
            || !in_array($db->getConfig('host'), ['localhost', '127.0.0.1', '::1'], true)
            || (int) $db->getConfig('port') !== ($physical['port'] ?? 0)) {
            throw new RuntimeException(static::LABEL.' requires one local TCP MySQL connection, without URL/socket/split/forwarding.');
        }
        $server = (array) $db->selectOne('SELECT @@pid_file AS pid_file, @@basedir AS basedir, @@datadir AS datadir, @@version_compile_os AS os');
        $evidence = $this->windowsEvidence((int) $physical['port']);
        $pidPath = $this->path($server['pid_file']);
        $dataPath = $this->path($server['datadir']);
        $mysqlPid = trim((string) @file_get_contents($server['pid_file']));
        if (!str_starts_with((string) $server['os'], 'Win') || !ctype_digit($mysqlPid)
            || !str_starts_with($pidPath, $dataPath.'/') || str_starts_with($dataPath, '//')
            || strcasecmp($physical['server'] ?? '', gethostname()) !== 0) {
            throw new RuntimeException(static::LABEL.' could not corroborate the MySQL server with its local Windows PID file.');
        }
        $this->assertEvidence($evidence, $root, (int) $mysqlPid, (int) $physical['port']);
        $proof = $this->readProof($privateDirectory, $proofName);
        $web = $this->webProof($proof['nonce']);
        $expected = self::runtimeProof($db, $proof['nonce'], self::ROOT.'/public');
        $this->assertRuntimeProof($expected, $web);
    }

    /** A temporary read-only local diagnostic returns only a nonce-bound digest, never credentials. */
    public static function runtimeProof(Connection $db, string $nonce, string $documentRoot): array
    {
        $actual = (array) $db->selectOne('SELECT DATABASE() AS db, @@hostname AS server, @@port AS port, @@pid_file AS pid_file');
        $payload = ['root' => strtolower(str_replace('\\', '/', (string) realpath(base_path()))),
            'document_root' => strtolower(str_replace('\\', '/', (string) realpath($documentRoot))),
            'environment' => app()->environment(), 'key' => config('app.key'),
            'config' => $db->getConfig(), 'actual' => $actual,
            'pdo' => $db->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME),
            'status' => $db->getPdo()->getAttribute(PDO::ATTR_CONNECTION_STATUS)];
        return ['target' => 'gramlyze.loc', 'nonce' => $nonce,
            'sha256' => hash_hmac('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), $nonce)];
    }

    /** Kept separate so negative fixtures exercise the policy without probing the host. */
    public function assertEvidence(array $e, string $root, int $mysqlPid, int $port): void
    {
        $addresses = $e['addresses'] ?? [];
        if (!$addresses || array_filter($addresses, fn ($ip) => !in_array($ip, ['127.0.0.1', '::1'], true))) {
            throw new RuntimeException(static::LABEL.' gramlyze.loc does not resolve exclusively to loopback.');
        }
        if (($e['document_root'] ?? '') !== $root.'/public' || ($e['active_vhosts'] ?? 0) !== 1
            || ($e['apache_name'] ?? '') !== 'httpd.exe' || ($e['apache_pid'] ?? 0) < 1
            || ($e['apache_pid'] ?? 0) !== ($e['apache_pid_file'] ?? -1)) {
            throw new RuntimeException(static::LABEL.' live Apache/vhost mapping is unconfirmed or ambiguous.');
        }
        $listeners = $e['mysql_listeners'] ?? [];
        if (!$listeners || $mysqlPid < 1) { throw new RuntimeException(static::LABEL.' local MySQL listener is unconfirmed.'); }
        foreach ($listeners as $listener) {
            if (($listener['port'] ?? 0) !== $port || ($listener['pid'] ?? 0) !== $mysqlPid
                || !in_array($listener['name'] ?? '', ['mysqld.exe', 'mariadbd.exe'], true)) {
                throw new RuntimeException(static::LABEL.' MySQL listener belongs to a different process or forwarding target.');
            }
        }
    }

    public function assertRuntimeProof(array $expected, array $web): void
    {
        if ($web !== $expected) {
            throw new RuntimeException(static::LABEL.' live vhost database/runtime proof differs from the CLI working connection.');
        }
    }

    protected function windowsEvidence(int $port): array
    {
        if ($port < 1 || $port > 65535) { throw new RuntimeException('Invalid local MySQL port.'); }
        $script = dirname(__DIR__, 2).'/tools/diagnostics/inspect-m11-local-target.ps1';
        // Run the fixed read-only commands without changing Windows execution policy.
        $commands = '& {'.file_get_contents($script).'} -DatabasePort '.$port;
        $process = new Process(['C:/Windows/System32/WindowsPowerShell/v1.0/powershell.exe', '-NoProfile',
            '-NonInteractive', '-Command', $commands]);
        $process->setTimeout(20);
        $process->run();
        if (!$process->isSuccessful()) { throw new RuntimeException(static::LABEL.' Windows listener/vhost inspection could not be completed.'); }
        return json_decode(trim($process->getOutput()), true, flags: JSON_THROW_ON_ERROR);
    }

    private function readProof(string $directory, ?string $name): array
    {
        if (!is_string($name) || !preg_match('/^local-proof-[a-f0-9]{32}\.json$/D', $name)) {
            throw new RuntimeException(static::LABEL.' local-target needs a fresh private local-proof basename.');
        }
        $path = $directory.'/'.$name;
        if (is_link($path) || !is_file($path)) { throw new RuntimeException(static::LABEL.' local-target proof is missing.'); }
        $proof = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        if (($proof['target'] ?? '') !== 'gramlyze.loc' || !preg_match('/^[a-f0-9]{32}$/D', $proof['nonce'] ?? '')
            || $name !== 'local-proof-'.$proof['nonce'].'.json') { throw new RuntimeException('Invalid '.static::LABEL.' local proof.'); }
        return $proof;
    }

    protected function webProof(string $nonce): array
    {
        $curl = curl_init('http://gramlyze.loc/api/_local/'.static::ENDPOINT.$nonce);
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 15, CURLOPT_PROXY => '',
            CURLOPT_RESOLVE => ['gramlyze.loc:80:127.0.0.1'], CURLOPT_HTTPHEADER => ['Accept: application/json']]);
        $body = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $ip = curl_getinfo($curl, CURLINFO_PRIMARY_IP);
        if ($body === false || $status !== 200 || $ip !== '127.0.0.1') {
            throw new RuntimeException(static::LABEL.' live loopback vhost proof is unavailable; no write is allowed.');
        }
        return json_decode($body, true, flags: JSON_THROW_ON_ERROR);
    }

    private function path(string $path): string
    {
        $resolved = realpath($path);
        if ($resolved === false) { throw new RuntimeException('M11 local path is missing.'); }
        return rtrim(strtolower(str_replace('\\', '/', $resolved)), '/');
    }
}
