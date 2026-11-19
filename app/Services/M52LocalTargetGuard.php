<?php

namespace App\Services;

use App\Support\M52PassiveBasicsPackage as Package;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use RuntimeException;

/** Finite M52 proof scope; M11 Windows/runtime evidence and M49 DB policy stay unchanged. */
class M52LocalTargetGuard extends M11LocalTargetGuard
{
    protected const LABEL = 'M52';
    protected const PRIVATE_DIRECTORY = 'seo-m52-local';
    protected const ENDPOINT = 'm52-target-';

    public static function scope(): array
    {
        return array_map(static fn ($owner, $slug) => ['identity' => $owner, 'locale' => 'uk',
            'path' => '/theory/passive-voice/'.$slug], Package::OWNERS, Package::SLUGS);
    }

    public static function privateDirectory(): string { return self::ROOT.'/storage/app/'.self::PRIVATE_DIRECTORY; }
    public static function assertConfiguredDatabase(Connection $db): void { M49LocalTargetGuard::assertConfiguredDatabase($db); }
    public static function publicIdentity(Connection $db, string $documentRoot): array { return M49LocalTargetGuard::publicIdentity($db, $documentRoot); }
    public function physicalEvidence(): array { return $this->windowsEvidence(3306); }

    public function verify(Connection $db, string $target, string $directory, ?string $name, array $physical): void
    {
        self::assertConfiguredDatabase($db);
        if ($target !== 'gramlyze.loc' || ($physical['db'] ?? $physical['database'] ?? null) !== 'gr2') {
            throw new RuntimeException('M52 inspected local database target differs.');
        }
        $proof = self::readFreshProof($name);
        foreach ($proof['physical_hashes'] as $path => $sha) {
            if (!is_file($path) || is_link($path) || !hash_equals($sha, hash_file('sha256', $path))) {
                throw new RuntimeException('M52 physical target changed after the fresh proof.');
            }
        }
        parent::verify($db, $target, $directory, $name, $physical);
    }

    public static function readFreshProof(?string $name): array
    {
        if (!is_string($name) || preg_match('/^local-proof-[a-f0-9]{32}\.json$/D', $name) !== 1) {
            throw new RuntimeException('M52 fresh private nonce-bound proof basename is required.');
        }
        $path = self::privateDirectory().'/'.$name;
        if (!is_file($path) || is_link($path)) { throw new RuntimeException('M52 private proof is unavailable.'); }
        $proof = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
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
            ]) { throw new RuntimeException('M52 proof scope/time/physical inventory differs.'); }
        foreach ($proof['physical_hashes'] as $sha) {
            if (!is_string($sha) || preg_match('/^[a-f0-9]{64}$/D', $sha) !== 1) {
                throw new RuntimeException('M52 physical hash shape differs.');
            }
        }
    }

    /** A proof GET cannot carry authentication, cookies, query parameters or forwarding headers. */
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
            && $request->getPathInfo() === '/api/_local/m52-target-'.$nonce && $request->getQueryString() === null
            && $normal($applicationRoot) === $normal(self::ROOT) && $normal($documentRoot) === $normal(self::ROOT.'/public');
    }

    protected function webProof(string $nonce): array
    {
        $curl = curl_init('http://gramlyze.loc/api/_local/m52-target-'.$nonce);
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 15, CURLOPT_PROXY => '',
            CURLOPT_RESOLVE => ['gramlyze.loc:80:127.0.0.1'], CURLOPT_HTTPHEADER => ['Accept: application/json']]);
        try {
            $body = curl_exec($curl);
            if (!is_string($body) || curl_getinfo($curl, CURLINFO_RESPONSE_CODE) !== 200
                || curl_getinfo($curl, CURLINFO_PRIMARY_IP) !== '127.0.0.1'
                || curl_getinfo($curl, CURLINFO_EFFECTIVE_URL) !== 'http://gramlyze.loc/api/_local/m52-target-'.$nonce
                || curl_getinfo($curl, CURLINFO_REDIRECT_COUNT) !== 0) {
                throw new RuntimeException('M52 fixed-loopback web proof is unavailable; no writes.');
            }
            $web = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
            if (($web['runtime'] ?? null) !== self::publicIdentity(\Illuminate\Support\Facades\DB::connection(), self::ROOT.'/public')
                || !is_array($web['proof'] ?? null)) { throw new RuntimeException('M52 web/CLI runtime identity differs.'); }

            return $web['proof'];
        } finally { curl_close($curl); }
    }
}
