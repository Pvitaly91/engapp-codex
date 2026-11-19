<?php

namespace App\Services;

use App\Support\PracticeCompletionPackage as Package;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use RuntimeException;

/** Explicit twelve-owner local proof; reuses the accepted M11 physical/runtime primitives. */
class PracticeCompletionLocalTargetGuard extends M11LocalTargetGuard
{
    protected const LABEL = 'Practice completion';
    protected const PRIVATE_DIRECTORY = 'practice-completion-local';
    protected const ENDPOINT = 'practice-completion-target-';

    public static function scope(): array
    {
        return array_map(static fn ($b) => ['identity' => $b['identity'], 'locale' => 'uk',
            'path' => '/theory/'.implode('/', $b['ancestry']).'/'.$b['slug']], Package::BINDINGS);
    }

    public static function assertConfiguredDatabase(Connection $db): void
    {
        M49LocalTargetGuard::assertConfiguredDatabase($db);
    }

    public static function privateDirectory(): string { return self::ROOT.'/storage/app/'.self::PRIVATE_DIRECTORY; }
    public static function publicIdentity(Connection $db, string $documentRoot): array
    {
        return M49LocalTargetGuard::publicIdentity($db, $documentRoot);
    }

    public function physicalEvidence(): array { return $this->windowsEvidence(3306); }

    public function verify(Connection $db, string $target, string $directory, ?string $name, array $physical): void
    {
        self::assertConfiguredDatabase($db);
        if ($target !== 'gramlyze.loc' || ($physical['db'] ?? $physical['database'] ?? null) !== 'gr2') {
            throw new RuntimeException('Practice completion local database target differs.');
        }
        $proof = self::readFreshProof($name);
        foreach ($proof['physical_hashes'] as $path => $sha) {
            if (!is_file($path) || is_link($path) || !hash_equals($sha, hash_file('sha256', $path))) {
                throw new RuntimeException('Practice completion physical target changed since proof.');
            }
        }
        parent::verify($db, $target, $directory, $name, $physical);
    }

    public static function readFreshProof(?string $name): array
    {
        if (!is_string($name) || !preg_match('/^local-proof-[a-f0-9]{32}\\.json$/D', $name)) {
            throw new RuntimeException('Practice completion requires a private nonce proof basename.');
        }
        $path = self::privateDirectory().'/'.$name;
        if (!is_file($path) || is_link($path)) { throw new RuntimeException('Practice completion proof missing.'); }
        $proof = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        self::assertFreshProof($proof, $name);
        return $proof;
    }

    public static function assertFreshProof(array $proof, string $name, ?int $now = null): void
    {
        $now ??= time(); $at = strtotime($proof['created_at'] ?? '');
        if (!preg_match('/^local-proof-[a-f0-9]{32}\\.json$/D', $name)
            || ($proof['target'] ?? null) !== 'gramlyze.loc' || ($proof['scope'] ?? null) !== self::scope()
            || $name !== 'local-proof-'.($proof['nonce'] ?? '').'.json'
            || !preg_match('/^[a-f0-9]{32}$/D', $proof['nonce'] ?? '') || !$at || $at > $now + 60 || $now - $at > 1800
            || array_keys($proof['physical_hashes'] ?? []) !== [
                'C:/Program Files/xampp/apache/conf/httpd.conf',
                'C:/Program Files/xampp/apache/conf/extra/httpd-vhosts.conf',
                'C:/Program Files/xampp/apache/conf/extra/gramlyze-fastcgi.conf', self::ROOT.'/public/index.php',
            ]) { throw new RuntimeException('Practice completion proof scope/time/physical inventory differs.'); }
        foreach ($proof['physical_hashes'] as $sha) {
            if (!is_string($sha) || !preg_match('/^[a-f0-9]{64}$/D', $sha)) {
                throw new RuntimeException('Practice completion physical SHA-256 shape differs.');
            }
        }
    }

    public static function allowsProofRequest(Request $r, string $nonce, string $applicationRoot, string $documentRoot): bool
    {
        foreach ($r->headers->keys() as $header) {
            if ($header === 'forwarded' || str_starts_with($header, 'x-forwarded-')
                || in_array($header, ['x-real-ip', 'via', 'authorization', 'cookie', 'referer'], true)) { return false; }
        }
        $normal = static fn ($path) => strtolower(str_replace('\\', '/', (string) realpath($path)));
        return preg_match('/^[a-f0-9]{32}$/D', $nonce) === 1
            && $r->getMethod() === 'GET' && $r->server('REQUEST_METHOD') === 'GET'
            && in_array($r->server('HTTP_HOST'), ['gramlyze.loc', 'gramlyze.loc:80'], true)
            && $r->getHost() === 'gramlyze.loc' && $r->getScheme() === 'http' && $r->getPort() === 80
            && in_array($r->server('REMOTE_ADDR'), ['127.0.0.1', '::1'], true)
            && $r->getPathInfo() === '/api/_local/practice-completion-target-'.$nonce && $r->getQueryString() === null
            && $normal($applicationRoot) === $normal(self::ROOT) && $normal($documentRoot) === $normal(self::ROOT.'/public');
    }

    protected function webProof(string $nonce): array
    {
        $curl = curl_init('http://gramlyze.loc/api/_local/practice-completion-target-'.$nonce);
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 15, CURLOPT_PROXY => '',
            CURLOPT_RESOLVE => ['gramlyze.loc:80:127.0.0.1'], CURLOPT_HTTPHEADER => ['Accept: application/json']]);
        $body = curl_exec($curl); $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE); $ip = curl_getinfo($curl, CURLINFO_PRIMARY_IP);
        if ($body === false || $status !== 200 || $ip !== '127.0.0.1') { throw new RuntimeException('Practice completion web proof unavailable; no writes.'); }
        $web = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        if (($web['runtime'] ?? null) !== self::publicIdentity(\Illuminate\Support\Facades\DB::connection(), self::ROOT.'/public')
            || !is_array($web['proof'] ?? null)) { throw new RuntimeException('Practice completion web/CLI identity differs.'); }
        return $web['proof'];
    }
}
