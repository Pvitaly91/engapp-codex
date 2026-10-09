<?php

namespace App\Services;

use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use RuntimeException;

/** Explicit, fresh, read-only proof for the three M45 UK owners on the working local vhost. */
class M45LocalTargetGuard extends M11LocalTargetGuard
{
    protected const LABEL = 'M45';
    protected const PRIVATE_DIRECTORY = 'seo-m45-local';
    protected const ENDPOINT = 'm45-target-';
    public const MAX_AGE_SECONDS = 1800;
    public const SCOPE = [
        ['identity' => 'Database\\Seeders\\Page_V3\\FutureForms\\FutureFormsFuturePerfectVsFutureContinuousTheorySeeder', 'locale' => 'uk', 'path' => '/theory/maibutni-formy/future-perfect-vs-future-continuous'],
        ['identity' => 'Database\\Seeders\\Page_V3\\FutureForms\\FutureFormsFuturePerfectVsFuturePerfectContinuousTheorySeeder', 'locale' => 'uk', 'path' => '/theory/maibutni-formy/future-perfect-vs-future-perfect-continuous'],
        ['identity' => 'Database\\Seeders\\Page_V3\\FutureForms\\FutureFormsFutureContinuousVsFuturePerfectContinuousTheorySeeder', 'locale' => 'uk', 'path' => '/theory/maibutni-formy/future-continuous-vs-future-perfect-continuous'],
    ];
    public const PHYSICAL_FILES = [
        'C:/Program Files/xampp/apache/conf/httpd.conf',
        'C:/Program Files/xampp/apache/conf/extra/httpd-vhosts.conf',
        'C:/Program Files/xampp/apache/conf/extra/gramlyze-fastcgi.conf',
        self::ROOT.'/public/index.php',
    ];

    public function verify(Connection $db, string $target, string $privateDirectory, ?string $proofName, array $physical): void
    {
        if ($target !== 'gramlyze.loc' || PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows'
            || self::normalPath($privateDirectory) !== self::normalPath(self::privateDirectory())) {
            throw new RuntimeException('M45 requires the exact working local CLI target and private evidence directory.');
        }
        self::assertConfiguredDatabase($db);
        if (($physical['db'] ?? $physical['database'] ?? null) !== 'gr2'
            || (isset($physical['db'], $physical['database']) && $physical['db'] !== $physical['database'])
            || (int) ($physical['port'] ?? 0) !== 3306) {
            throw new RuntimeException('M45 physical database identity differs from the inspected local target.');
        }
        self::readFreshProof($proofName);
        parent::verify($db, $target, $privateDirectory, $proofName, $physical);
    }

    /** Reject unsupported connection configuration before obtaining PDO or issuing a query. */
    public static function assertConfiguredDatabase(Connection $db): void
    {
        if ($db->getDriverName() !== 'mysql' || !in_array($db->getConfig('host'), ['localhost', '127.0.0.1', '::1'], true)
            || $db->getConfig('database') !== 'gr2' || (int) $db->getConfig('port') !== 3306
            || $db->getConfig('url') || $db->getConfig('unix_socket') || $db->getConfig('read') || $db->getConfig('write')) {
            throw new RuntimeException('M45 requires the inspected single local TCP MySQL database gr2:3306; no query permitted.');
        }
    }

    /** Laravel aliases GET routes to HEAD; explicitly deny HEAD before any proof SELECT. */
    public static function allowsProofRequest(Request $request, string $nonce, string $applicationRoot, string $documentRoot): bool
    {
        foreach ($request->headers->keys() as $header) {
            if ($header === 'forwarded' || str_starts_with($header, 'x-forwarded-')
                || in_array($header, ['x-real-ip', 'via', 'authorization', 'cookie', 'referer'], true)) {
                return false;
            }
        }
        $root = self::normalPath(self::ROOT);
        $public = self::normalPath(self::ROOT.'/public');
        return $root !== null && $public !== null
            && preg_match('/^[a-f0-9]{32}$/D', $nonce) === 1
            && $request->getMethod() === 'GET' && $request->server('REQUEST_METHOD') === 'GET'
            && in_array($request->server('HTTP_HOST'), ['gramlyze.loc', 'gramlyze.loc:80'], true)
            && $request->getHost() === 'gramlyze.loc' && $request->getScheme() === 'http' && $request->getPort() === 80
            && in_array($request->server('REMOTE_ADDR'), ['127.0.0.1', '::1'], true)
            && $request->path() === 'api/_local/'.self::ENDPOINT.$nonce && $request->getQueryString() === null
            && self::normalPath($applicationRoot) === $root && self::normalPath($documentRoot) === $public;
    }

    /** Only safe allowlisted identity fields; credentials/configuration remain inside runtimeProof's HMAC. */
    public static function publicIdentity(Connection $db, string $documentRoot): array
    {
        $actual = (array) $db->selectOne('SELECT DATABASE() AS db, @@port AS port');
        return ['environment' => app()->environment(), 'site_mode' => app(\App\Support\SiteMode::class)->forHost('gramlyze.loc'),
            'application_root' => self::normalPath(base_path()), 'document_root' => self::normalPath($documentRoot),
            'db_driver' => $db->getDriverName(), 'db_host' => $db->getConfig('host'),
            'db_port' => (int) $actual['port'], 'database' => $actual['db']];
    }

    public static function privateDirectory(): string
    {
        return self::ROOT.'/storage/app/'.self::PRIVATE_DIRECTORY;
    }

    /** Read existing evidence only. The capture script must run explicitly before preparing a nonce. */
    public static function loadPhysicalCapture(string $name): array
    {
        if (!preg_match('/^physical-target-before-v[1-9][0-9]*\.json$/D', $name)) {
            throw new RuntimeException('M45 needs a fresh physical-target-before-vN.json basename.');
        }
        $file = self::evidenceFile($name);
        $bytes = file_get_contents($file);
        $capture = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        self::assertPhysicalCapture($capture);
        foreach (self::PHYSICAL_FILES as $path) {
            if (!is_file($path) || is_link($path) || !hash_equals($capture['hashes'][$path], hash_file('sha256', $path))) {
                throw new RuntimeException('M45 physical configuration/document identity changed since capture.');
            }
        }
        return ['capture' => $capture, 'sha256' => hash('sha256', $bytes)];
    }

    /** Pure policy for isolated negative fixtures, with no Apache, HTTP or database access. */
    public static function assertPhysicalCapture(array $capture, ?int $now = null): void
    {
        self::assertFreshTime($capture['at'] ?? null, $now ?? time());
        if (($capture['localHost'] ?? null) !== 'gramlyze.loc' || ($capture['scheme'] ?? null) !== 'http'
            || ($capture['documentRoot'] ?? null) !== self::ROOT.'/public'
            || ($capture['vhostSource'] ?? null) !== self::PHYSICAL_FILES[1] || ($capture['vhostLine'] ?? 0) < 1
            || ($capture['configWrites'] ?? null) !== false || ($capture['routeAdded'] ?? null) !== false || ($capture['dbWrites'] ?? null) !== false
            || array_keys($capture['hashes'] ?? []) !== self::PHYSICAL_FILES
            || !self::isSha($capture['effectiveVhostSha256'] ?? null)) {
            throw new RuntimeException('M45 physical capture is not the exact unchanged local HTTP document target.');
        }
        foreach ($capture['hashes'] as $hash) {
            if (!self::isSha($hash)) { throw new RuntimeException('Invalid M45 physical file digest.'); }
        }
        $runtime = $capture['runtime'] ?? [];
        if (empty($runtime['dns']) || empty($runtime['ports']) || empty($runtime['processes'])) {
            throw new RuntimeException('M45 physical capture lacks DNS, listener or process evidence.');
        }
        foreach ($runtime['dns'] as $row) {
            if (($row['Name'] ?? null) !== 'gramlyze.loc' || !in_array($row['IPAddress'] ?? null, ['127.0.0.1', '::1'], true)) {
                throw new RuntimeException('M45 captured DNS is not exclusively local.');
            }
        }
        $apachePids = [];
        foreach ($runtime['processes'] as $row) {
            if (($row['Name'] ?? null) !== 'httpd.exe' || !is_int($row['ProcessId'] ?? null) || $row['ProcessId'] < 1) {
                throw new RuntimeException('M45 captured web process is not Apache.');
            }
            $apachePids[] = $row['ProcessId'];
        }
        foreach ($runtime['ports'] as $row) {
            if (($row['LocalPort'] ?? null) !== 80 || !in_array($row['OwningProcess'] ?? null, $apachePids, true)) {
                throw new RuntimeException('M45 captured HTTP listener does not belong to Apache.');
            }
        }
    }

    public static function assertProofMetadata(array $proof, string $name, ?int $now = null): void
    {
        if (($proof['target'] ?? null) !== 'gramlyze.loc' || !is_string($proof['nonce'] ?? null)
            || !preg_match('/^[a-f0-9]{32}$/D', $proof['nonce']) || $name !== 'local-proof-'.$proof['nonce'].'.json'
            || ($proof['scope'] ?? null) !== self::SCOPE
            || !is_string($proof['physical_basename'] ?? null)
            || !preg_match('/^physical-target-before-v[1-9][0-9]*\.json$/D', $proof['physical_basename'])
            || !self::isSha($proof['physical_sha256'] ?? null) || !self::isSha($proof['routes_before_sha256'] ?? null)) {
            throw new RuntimeException('M45 proof metadata, nonce or exact three-owner UK scope differs.');
        }
        self::assertFreshTime($proof['created_at'] ?? null, $now ?? time());
    }

    public static function readFreshProof(?string $name): array
    {
        if (!is_string($name) || !preg_match('/^local-proof-[a-f0-9]{32}\.json$/D', $name)) {
            throw new RuntimeException('M45 requires a private local-proof basename.');
        }
        $proof = json_decode(file_get_contents(self::evidenceFile($name)), true, flags: JSON_THROW_ON_ERROR);
        self::assertProofMetadata($proof, $name);
        $physical = self::loadPhysicalCapture($proof['physical_basename']);
        $routes = self::evidenceFile('routes-before-'.$proof['nonce'].'.bin');
        if (!hash_equals($proof['physical_sha256'], $physical['sha256'])
            || !hash_equals($proof['routes_before_sha256'], hash_file('sha256', $routes))) {
            throw new RuntimeException('M45 nonce-bound physical evidence or route backup changed.');
        }
        return $proof;
    }

    protected function webProof(string $nonce): array
    {
        $curl = curl_init('http://gramlyze.loc/api/_local/'.self::ENDPOINT.$nonce);
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 15, CURLOPT_PROXY => '',
            CURLOPT_RESOLVE => ['gramlyze.loc:80:127.0.0.1'], CURLOPT_HTTPHEADER => ['Accept: application/json']]);
        $body = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $ip = curl_getinfo($curl, CURLINFO_PRIMARY_IP);
        if ($body === false || $status !== 200 || $ip !== '127.0.0.1') {
            throw new RuntimeException('M45 live loopback proof unavailable; no writes.');
        }
        $web = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        if (($web['runtime'] ?? null) !== self::publicIdentity(\Illuminate\Support\Facades\DB::connection(), self::ROOT.'/public')
            || !is_array($web['proof'] ?? null)) {
            throw new RuntimeException('M45 safe web/CLI runtime identity differs; no writes.');
        }
        return $web['proof'];
    }

    private static function evidenceFile(string $name): string
    {
        $directory = self::privateDirectory();
        $file = $directory.'/'.$name;
        if (basename($name) !== $name || is_link($directory) || !is_dir($directory) || is_link($file) || !is_file($file)
            || self::normalPath(dirname($file)) !== self::normalPath($directory)) {
            throw new RuntimeException('M45 private evidence file is missing or redirected.');
        }
        return $file;
    }

    private static function assertFreshTime(mixed $stamp, int $now): void
    {
        $parsed = is_string($stamp) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|\+00:00)$/D', $stamp) ? strtotime($stamp) : false;
        if ($parsed === false || $parsed > $now + 60 || $now - $parsed > self::MAX_AGE_SECONDS) {
            throw new RuntimeException('M45 proof/capture is missing, future-dated or older than 30 minutes.');
        }
    }

    private static function isSha(mixed $value): bool
    {
        return is_string($value) && preg_match('/^[a-f0-9]{64}$/D', $value) === 1;
    }

    private static function normalPath(string $path): ?string
    {
        $resolved = realpath($path);
        return $resolved === false ? null : rtrim(strtolower(str_replace('\\', '/', $resolved)), '/');
    }
}
