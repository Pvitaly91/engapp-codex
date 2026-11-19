<?php

namespace App\Services;

use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use RuntimeException;

/** Explicit, read-only web/CLI proof for the three existing M49 local owners. */
class M49LocalTargetGuard extends M11LocalTargetGuard
{
    protected const LABEL = 'M49';
    protected const PRIVATE_DIRECTORY = 'seo-m49-local';
    protected const ENDPOINT = 'm49-target-';
    public const SCOPE = [
        ['identity' => 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsHadBetterTheorySeeder', 'locale' => 'uk', 'path' => '/theory/conditionals/had-better'],
        ['identity' => 'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsCanCouldTheorySeeder', 'locale' => 'uk', 'path' => '/theory/modal-verbs/can-could'],
        ['identity' => 'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsMayMightTheorySeeder', 'locale' => 'uk', 'path' => '/theory/modal-verbs/may-might'],
    ];

    // Each owner has its own exact category/definition/level binding.
    public const BINDINGS = [
        ['identity' => 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsHadBetterTheorySeeder', 'slug' => 'had-better', 'ancestry' => ['conditionals'],
            'category_title' => 'Умовні речення', 'definition' => 'database/seeders/Page_V3/Conditionals/ConditionalsHadBetterTheorySeeder/definition.json',
            'technical_level' => 'A2', 'display_level' => 'A2-B1'],
        ['identity' => 'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsCanCouldTheorySeeder', 'slug' => 'can-could', 'ancestry' => ['modal-verbs'],
            'category_title' => 'Модальні дієслова', 'definition' => 'database/seeders/Page_V3/ModalVerbs/ModalVerbsCanCouldTheorySeeder/definition.json',
            'technical_level' => 'A1', 'display_level' => 'A1-A2'],
        ['identity' => 'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsMayMightTheorySeeder', 'slug' => 'may-might', 'ancestry' => ['modal-verbs'],
            'category_title' => 'Модальні дієслова', 'definition' => 'database/seeders/Page_V3/ModalVerbs/ModalVerbsMayMightTheorySeeder/definition.json',
            'technical_level' => 'A2', 'display_level' => 'A2-B1'],
    ];

    public static function assertConfiguredDatabase(Connection $db): void
    {
        if ($db->getDriverName() !== 'mysql' || !in_array($db->getConfig('host'), ['localhost', '127.0.0.1', '::1'], true)
            || $db->getConfig('database') !== 'gr2' || !in_array($db->getConfig('port'), [3306, '3306'], true)
            || $db->getConfig('url') || $db->getConfig('unix_socket') || $db->getConfig('read') || $db->getConfig('write')) {
            throw new RuntimeException('M49 requires the inspected single loopback MySQL gr2:3306; no query permitted.');
        }
    }

    public function verify(Connection $db, string $target, string $directory, ?string $proofName, array $physical): void
    {
        self::assertConfiguredDatabase($db);
        if ($target !== 'gramlyze.loc' || ($physical['db'] ?? $physical['database'] ?? null) !== 'gr2') {
            throw new RuntimeException('M49 local target differs.');
        }
        $proof = self::readFreshProof($proofName);
        foreach ($proof['physical_hashes'] as $path => $sha) {
            if (!is_file($path) || is_link($path) || !hash_equals($sha, hash_file('sha256', $path))) {
                throw new RuntimeException('M49 physical target changed since proof.');
            }
        }
        parent::verify($db, $target, $directory, $proofName, $physical);
    }

    public static function privateDirectory(): string { return self::ROOT.'/storage/app/seo-m49-local'; }

    public static function readFreshProof(?string $name): array
    {
        if (!is_string($name) || !preg_match('/^local-proof-[a-f0-9]{32}\.json$/D', $name)) {
            throw new RuntimeException('M49 requires a nonce-bound private proof basename.');
        }
        $path = self::privateDirectory().'/'.$name;
        if (!is_file($path) || is_link($path)) { throw new RuntimeException('M49 proof is unavailable.'); }
        $proof = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        self::assertFreshProof($proof, $name);
        return $proof;
    }

    /** Pure proof policy; isolated tests do not touch the working evidence directory. */
    public static function assertFreshProof(array $proof, string $name, ?int $now = null): void
    {
        $now ??= time();
        $at = strtotime($proof['created_at'] ?? '');
        if (!preg_match('/^local-proof-[a-f0-9]{32}\\.json$/D', $name)
            || ($proof['target'] ?? null) !== 'gramlyze.loc' || ($proof['scope'] ?? null) !== self::SCOPE
            || $name !== 'local-proof-'.($proof['nonce'] ?? '').'.json'
            || !preg_match('/^[a-f0-9]{32}$/D', $proof['nonce'] ?? '')
            || !$at || $at > $now + 60 || $now - $at > 1800
            || array_keys($proof['physical_hashes'] ?? []) !== [
                'C:/Program Files/xampp/apache/conf/httpd.conf',
                'C:/Program Files/xampp/apache/conf/extra/httpd-vhosts.conf',
                'C:/Program Files/xampp/apache/conf/extra/gramlyze-fastcgi.conf', self::ROOT.'/public/index.php',
            ]) { throw new RuntimeException('M49 proof scope, time or physical evidence differs.'); }
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
            && $r->getPathInfo() === '/api/_local/m49-target-'.$nonce && $r->getQueryString() === null
            && $normal($applicationRoot) === $normal(self::ROOT)
            && $normal($documentRoot) === $normal(self::ROOT.'/public');
    }

    public static function publicIdentity(Connection $db, string $documentRoot): array
    {
        self::assertConfiguredDatabase($db);
        $actual = (array) $db->selectOne('SELECT DATABASE() AS db, @@port AS port');
        return ['environment' => app()->environment(), 'site_mode' => app(\App\Support\SiteMode::class)->forHost('gramlyze.loc'),
            'application_root' => strtolower(str_replace('\\', '/', (string) realpath(base_path()))),
            'document_root' => strtolower(str_replace('\\', '/', (string) realpath($documentRoot))),
            'db_driver' => $db->getDriverName(), 'db_host' => $db->getConfig('host'),
            'db_port' => (int) $actual['port'], 'database' => $actual['db']];
    }

    protected function webProof(string $nonce): array
    {
        $curl = curl_init('http://gramlyze.loc/api/_local/m49-target-'.$nonce);
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 15, CURLOPT_PROXY => '',
            CURLOPT_RESOLVE => ['gramlyze.loc:80:127.0.0.1'], CURLOPT_HTTPHEADER => ['Accept: application/json']]);
        $body = curl_exec($curl); $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE); $ip = curl_getinfo($curl, CURLINFO_PRIMARY_IP);
        if ($body === false || $status !== 200 || $ip !== '127.0.0.1') { throw new RuntimeException('M49 loopback proof unavailable; no writes.'); }
        $web = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        if (($web['runtime'] ?? null) !== self::publicIdentity($this->proofConnection(), self::ROOT.'/public')
            || !is_array($web['proof'] ?? null)) { throw new RuntimeException('M49 web/CLI runtime identity differs; no writes.'); }
        return $web['proof'];
    }

    private function proofConnection(): Connection { return \Illuminate\Support\Facades\DB::connection(); }

    public function physicalEvidence(): array { return $this->windowsEvidence(3306); }
}
