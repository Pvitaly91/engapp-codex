<?php

namespace App\Services;

final class PpcQualityLocalTargetGuard extends M11LocalTargetGuard
{
    protected const LABEL = 'PPC practice quality';
    protected const PRIVATE_DIRECTORY = 'ppc-quality-local';
    protected const ENDPOINT = 'ppc-quality-target-';

    /** Physical guard for SELECT-only export; never authorizes a DB mutation. */
    public function verifyReadOnlyExport(\Illuminate\Database\Connection $db, string $target, string $privateDirectory, array $physical): void
    {
        if ($target !== 'gramlyze.loc' || PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows') {
            throw new \RuntimeException('PPC read-only export requires Windows CLI on gramlyze.loc.');
        }
        $path = static fn (string $value): string => rtrim(strtolower(str_replace('\\', '/', (string) realpath($value))), '/');
        $root = $path(self::ROOT);
        if ($path(base_path()) !== $root || $path(app()->environmentPath()) !== $root
            || $path($privateDirectory) !== $root.'/storage/app/'.self::PRIVATE_DIRECTORY) {
            throw new \RuntimeException('PPC read-only export does not match the working vhost/environment.');
        }
        if ($db->getDriverName() !== 'mysql' || $db->getConfig('url') || $db->getConfig('unix_socket')
            || $db->getConfig('read') || $db->getConfig('write') || $db->getReadPdo() !== $db->getPdo()
            || ! in_array($db->getConfig('host'), ['localhost', '127.0.0.1', '::1'], true)
            || (int) $db->getConfig('port') !== 3306 || (int) ($physical['port'] ?? 0) !== 3306
            || ($physical['db'] ?? null) !== 'gr2' || strcasecmp($physical['server'] ?? '', gethostname()) !== 0) {
            throw new \RuntimeException('PPC read-only export requires the existing local gr2 TCP target.');
        }
        $server = (array) $db->selectOne('SELECT @@pid_file AS pid_file, @@datadir AS datadir, @@version_compile_os AS os');
        $pidPath = $path((string) $server['pid_file']);
        $dataPath = $path((string) $server['datadir']);
        $pid = trim((string) @file_get_contents($server['pid_file']));
        if (! str_starts_with((string) $server['os'], 'Win') || ! ctype_digit($pid)
            || ! str_starts_with($pidPath, $dataPath.'/') || str_starts_with($dataPath, '//')) {
            throw new \RuntimeException('PPC read-only export cannot corroborate the local Windows MySQL PID.');
        }
        $this->assertEvidence($this->windowsEvidence(3306), $root, (int) $pid, 3306);
    }
}
