<?php

namespace App\Services;

use App\Support\M51ModalMeaningsPackage as Package;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use RuntimeException;

/** Three-owner scope around the unchanged M11 physical/runtime proof primitives. */
class M51LocalTargetGuard extends M11LocalTargetGuard
{
    protected const LABEL = 'M51';
    protected const PRIVATE_DIRECTORY = 'seo-m51-local';
    protected const ENDPOINT = 'm51-target-';

    public static function scope(): array
    {
        return array_map(static fn ($owner, $slug) => ['identity' => $owner, 'locale' => 'uk',
            'path' => '/theory/modal-verbs/'.$slug], Package::OWNERS, Package::SLUGS);
    }

    public static function assertConfiguredDatabase(Connection $db): void
    {
        M49LocalTargetGuard::assertConfiguredDatabase($db);
    }

    public static function privateDirectory(): string { return self::ROOT.'/storage/app/'.self::PRIVATE_DIRECTORY; }
    public static function publicIdentity(Connection $db, string $documentRoot): array { return M49LocalTargetGuard::publicIdentity($db, $documentRoot); }
    public function physicalEvidence(): array { return $this->windowsEvidence(3306); }

    public function verify(Connection $db, string $target, string $directory, ?string $name, array $physical): void
    {
        self::assertConfiguredDatabase($db);
        if ($target !== 'gramlyze.loc' || ($physical['db'] ?? $physical['database'] ?? null) !== 'gr2') {
            throw new RuntimeException('M51 inspected local database target differs.');
        }
        $proof = self::readFreshProof($name);
        foreach ($proof['physical_hashes'] as $path => $sha) {
            if (!is_file($path) || is_link($path) || !hash_equals($sha, hash_file('sha256', $path))) {
                throw new RuntimeException('M51 physical target changed since fresh proof.');
            }
        }
        parent::verify($db, $target, $directory, $name, $physical);
    }

    public static function readFreshProof(?string $name): array
    {
        if (!is_string($name) || preg_match('/^local-proof-[a-f0-9]{32}\.json$/D', $name) !== 1) {
            throw new RuntimeException('M51 fresh private proof basename is required.');
        }
        $file = self::privateDirectory().'/'.$name;
        if (!is_file($file) || is_link($file)) { throw new RuntimeException('M51 proof is missing or linked.'); }
        $proof = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
        self::assertFreshProof($proof, $name);

        return $proof;
    }

    public static function assertFreshProof(array $proof, string $name, ?int $now = null): void
    {
        $now ??= time(); $at = strtotime($proof['created_at'] ?? '');
        if (preg_match('/^local-proof-[a-f0-9]{32}\.json$/D', $name) !== 1
            || ($proof['target'] ?? null) !== 'gramlyze.loc' || ($proof['scope'] ?? null) !== self::scope()
            || $name !== 'local-proof-'.($proof['nonce'] ?? '').'.json'
            || preg_match('/^[a-f0-9]{32}$/D', $proof['nonce'] ?? '') !== 1
            || !$at || $at > $now + 60 || $now - $at > 1800
            || array_keys($proof['physical_hashes'] ?? []) !== [
                'C:/Program Files/xampp/apache/conf/httpd.conf',
                'C:/Program Files/xampp/apache/conf/extra/httpd-vhosts.conf',
                'C:/Program Files/xampp/apache/conf/extra/gramlyze-fastcgi.conf', self::ROOT.'/public/index.php',
            ]) { throw new RuntimeException('M51 proof scope/time/physical inventory differs.'); }
        foreach ($proof['physical_hashes'] as $sha) {
            if (!is_string($sha) || preg_match('/^[a-f0-9]{64}$/D', $sha) !== 1) {
                throw new RuntimeException('M51 physical SHA shape differs.');
            }
        }
    }

    /** Reject request shape before a proof route may read a connection or issue SELECT. */
    public static function allowsProofRequest(Request $request, string $nonce, string $applicationRoot, string $documentRoot): bool
    {
        foreach ($request->headers->keys() as $header) {
            if ($header === 'forwarded' || str_starts_with($header, 'x-forwarded-')
                || in_array($header, ['x-real-ip', 'via', 'authorization', 'cookie', 'referer'], true)) { return false; }
        }
        $normal = static fn ($path) => strtolower(str_replace('\\', '/', (string) realpath($path)));

        return preg_match('/^[a-f0-9]{32}$/D', $nonce) === 1
            && $request->getMethod() === 'GET' && $request->server('REQUEST_METHOD') === 'GET'
            && in_array($request->server('HTTP_HOST'), ['gramlyze.loc', 'gramlyze.loc:80'], true)
            && $request->getHost() === 'gramlyze.loc' && $request->getScheme() === 'http' && $request->getPort() === 80
            && in_array($request->server('REMOTE_ADDR'), ['127.0.0.1', '::1'], true)
            && $request->getPathInfo() === '/api/_local/m51-target-'.$nonce && $request->getQueryString() === null
            && $normal($applicationRoot) === $normal(self::ROOT) && $normal($documentRoot) === $normal(self::ROOT.'/public');
    }

    protected function webProof(string $nonce): array
    {
        $curl = curl_init('http://gramlyze.loc/api/_local/m51-target-'.$nonce);
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 15, CURLOPT_PROXY => '',
            CURLOPT_RESOLVE => ['gramlyze.loc:80:127.0.0.1'], CURLOPT_HTTPHEADER => ['Accept: application/json']]);
        try {
            $body = curl_exec($curl);
            if (!is_string($body) || curl_getinfo($curl, CURLINFO_RESPONSE_CODE) !== 200
                || curl_getinfo($curl, CURLINFO_PRIMARY_IP) !== '127.0.0.1') {
                throw new RuntimeException('M51 web proof is unavailable; no writes.');
            }
            $web = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
            if (($web['runtime'] ?? null) !== self::publicIdentity(\Illuminate\Support\Facades\DB::connection(), self::ROOT.'/public')
                || !is_array($web['proof'] ?? null)) { throw new RuntimeException('M51 web/CLI runtime identity differs.'); }

            return $web['proof'];
        } finally { curl_close($curl); }
    }
}
