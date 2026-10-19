<?php

namespace Tests\Feature;

use App\Services\M52LocalTargetGuard as Guard;
use App\Support\M52PassiveBasicsPackage as Package;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use RuntimeException;
use Tests\TestCase;

/** Pure fixtures: no MySQL/PDO, HTTP, physical inspection, routes or working data writes. */
class M52LocalTargetGuardTest extends TestCase
{
    public function test_exact_connection_shape_is_checked_without_connecting_or_issuing_queries(): void
    {
        $config = ['driver' => 'mysql', 'host' => '127.0.0.1', 'port' => 3306, 'database' => 'gr2'];
        foreach (['127.0.0.1', 'localhost', '::1'] as $host) {
            Guard::assertConfiguredDatabase(new Connection(null, 'gr2', '', array_replace($config, ['host' => $host])));
        }
        Guard::assertConfiguredDatabase(new Connection(null, 'gr2', '', array_replace($config, ['port' => '3306'])));
        foreach (['driver' => 'sqlite', 'host' => 'remote.invalid', 'port' => 3307, 'database' => 'other',
            'url' => 'mysql://remote.invalid', 'unix_socket' => '/tmp/mysql.sock', 'read' => ['host' => 'other'],
            'write' => ['host' => 'other']] as $field => $value) {
            $error = null;
            try { Guard::assertConfiguredDatabase(new Connection(null, 'gr2', '', array_replace($config, [$field => $value]))); }
            catch (RuntimeException $caught) { $error = $caught; }
            self::assertInstanceOf(RuntimeException::class, $error, $field);
        }
    }

    private function proof(int $at): array
    {
        return ['target' => 'gramlyze.loc', 'nonce' => str_repeat('a', 32), 'created_at' => gmdate('c', $at), 'scope' => Guard::scope(),
            'physical_hashes' => array_fill_keys(['C:/Program Files/xampp/apache/conf/httpd.conf',
                'C:/Program Files/xampp/apache/conf/extra/httpd-vhosts.conf',
                'C:/Program Files/xampp/apache/conf/extra/gramlyze-fastcgi.conf', Guard::ROOT.'/public/index.php'], str_repeat('b', 64))];
    }

    public function test_m52_scope_is_the_three_exact_uk_owners_and_canonical_local_paths(): void
    {
        $owners = [
            'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceFormationRulesTheorySeeder',
            'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceNegativesQuestionsTheorySeeder',
            'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceModalVerbsTheorySeeder',
        ];
        $slugs = ['theory-passive-voice-formation-rules', 'theory-passive-voice-negatives-questions', 'theory-passive-voice-modal-verbs'];
        self::assertSame($owners, Package::OWNERS);
        self::assertSame($slugs, Package::SLUGS);
        self::assertSame([
            ['identity' => $owners[0], 'locale' => 'uk', 'path' => '/theory/passive-voice/theory-passive-voice-formation-rules'],
            ['identity' => $owners[1], 'locale' => 'uk', 'path' => '/theory/passive-voice/theory-passive-voice-negatives-questions'],
            ['identity' => $owners[2], 'locale' => 'uk', 'path' => '/theory/passive-voice/theory-passive-voice-modal-verbs'],
        ], Guard::scope());
        self::assertSame(Guard::ROOT.'/storage/app/seo-m52-local', Guard::privateDirectory());
    }

    public function test_fresh_proof_scope_time_nonce_and_exact_physical_inventory_are_closed(): void
    {
        $now = 1760000000; $proof = $this->proof($now); $name = 'local-proof-'.$proof['nonce'].'.json';
        Guard::assertFreshProof($proof, $name, $now);
        Guard::assertFreshProof($this->proof($now - 1800), $name, $now);
        Guard::assertFreshProof($this->proof($now + 60), $name, $now);
        foreach (['target', 'owner', 'locale', 'path', 'scope-order', 'scope-extra', 'nonce', 'malformed-nonce',
            'stale', 'future', 'invalid-time', 'physical-extra', 'physical-missing', 'physical-order', 'sha', 'sha-case'] as $change) {
            $bad = $proof;
            if ($change === 'target') { $bad['target'] = 'gramlyze.ub'; }
            if ($change === 'owner') { $bad['scope'][0]['identity'] = 'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalPerfectAndDeductionTheorySeeder'; }
            if ($change === 'locale') { $bad['scope'][0]['locale'] = 'en'; }
            if ($change === 'path') { $bad['scope'][0]['path'] = '/theory/passive-voice/modal-perfect-and-deduction'; }
            if ($change === 'scope-order') { $bad['scope'] = array_reverse($bad['scope']); }
            if ($change === 'scope-extra') { $bad['scope'][] = $bad['scope'][0]; }
            if ($change === 'nonce') { $bad['nonce'] = str_repeat('c', 32); }
            if ($change === 'malformed-nonce') { $bad['nonce'] = str_repeat('A', 32); }
            if ($change === 'stale') { $bad['created_at'] = gmdate('c', $now - 1801); }
            if ($change === 'future') { $bad['created_at'] = gmdate('c', $now + 61); }
            if ($change === 'invalid-time') { $bad['created_at'] = 'invalid'; }
            if ($change === 'physical-extra') { $bad['physical_hashes']['foreign'] = str_repeat('b', 64); }
            if ($change === 'physical-missing') { unset($bad['physical_hashes'][Guard::ROOT.'/public/index.php']); }
            if ($change === 'physical-order') { $bad['physical_hashes'] = array_reverse($bad['physical_hashes'], true); }
            if ($change === 'sha') { $bad['physical_hashes'][Guard::ROOT.'/public/index.php'] = 'invalid'; }
            if ($change === 'sha-case') { $bad['physical_hashes'][Guard::ROOT.'/public/index.php'] = str_repeat('B', 64); }
            $error = null; try { Guard::assertFreshProof($bad, $name, $now); } catch (RuntimeException $caught) { $error = $caught; }
            self::assertInstanceOf(RuntimeException::class, $error, $change);
        }
        foreach (['../'.$name, strtoupper($name), 'local-proof-'.str_repeat('a', 31).'.json'] as $badName) {
            $error = null; try { Guard::assertFreshProof($proof, $badName, $now); } catch (RuntimeException $caught) { $error = $caught; }
            self::assertInstanceOf(RuntimeException::class, $error, $badName);
        }
    }

    private function request(string $url, string $method = 'GET', array $server = []): Request
    {
        $request = Request::create($url, $method, [], [], [], ['REMOTE_ADDR' => '127.0.0.1', 'REQUEST_METHOD' => $method]);
        // URI values win inside Request::create; restore deliberate malformed-server fixtures afterwards.
        $request->server->add($server);
        $request->headers->replace($request->server->getHeaders());

        return $request;
    }

    public function test_temporary_proof_request_must_be_exact_local_get_loopback_without_secrets_or_forwarding(): void
    {
        $nonce = str_repeat('a', 32); $url = 'http://gramlyze.loc/api/_local/m52-target-'.$nonce;
        self::assertTrue(Guard::allowsProofRequest($this->request($url), $nonce, Guard::ROOT, Guard::ROOT.'/public'));
        self::assertTrue(Guard::allowsProofRequest($this->request($url, 'GET', ['REMOTE_ADDR' => '::1', 'HTTP_HOST' => 'gramlyze.loc:80']), $nonce, Guard::ROOT, Guard::ROOT.'/public'));
        foreach ([$this->request(str_replace('gramlyze.loc', 'gramlyze.ub', $url)),
            $this->request(str_replace('gramlyze.loc', 'gramlyze.com', $url)),
            $this->request(str_replace('gramlyze.loc', 'gramlyze.loc.evil.invalid', $url)),
            $this->request(str_replace('http:', 'https:', $url)),
            $this->request(str_replace('gramlyze.loc', 'gramlyze.loc:8080', $url)),
            $this->request(str_replace('m52-target-', 'm50-target-', $url)),
            $this->request($url.'?extra=1'), $this->request($url, 'POST'), $this->request($url, 'HEAD'),
            $this->request($url, 'GET', ['REMOTE_ADDR' => '192.168.0.104']),
            $this->request($url, 'GET', ['REMOTE_ADDR' => '127.0.0.2']),
            $this->request($url, 'GET', ['HTTP_HOST' => '127.0.0.1']),
            $this->request($url, 'GET', ['HTTP_COOKIE' => 'synthetic=fixture']),
            $this->request($url, 'GET', ['HTTP_AUTHORIZATION' => 'synthetic']),
            $this->request($url, 'GET', ['HTTP_REFERER' => 'http://gramlyze.loc/']),
            $this->request($url, 'GET', ['HTTP_FORWARDED' => 'for=127.0.0.1']),
            $this->request($url, 'GET', ['HTTP_X_FORWARDED_HOST' => 'gramlyze.loc']),
            $this->request($url, 'GET', ['HTTP_X_FORWARDED_PROTO' => 'http']),
            $this->request($url, 'GET', ['HTTP_X_REAL_IP' => '127.0.0.1']),
            $this->request($url, 'GET', ['HTTP_VIA' => 'synthetic'])] as $request) {
            self::assertFalse(Guard::allowsProofRequest($request, $nonce, Guard::ROOT, Guard::ROOT.'/public'));
        }
        self::assertFalse(Guard::allowsProofRequest($this->request($url), $nonce, Guard::ROOT.'/public', Guard::ROOT));
        self::assertFalse(Guard::allowsProofRequest($this->request($url), $nonce, Guard::ROOT, Guard::ROOT));
        self::assertFalse(Guard::allowsProofRequest($this->request($url), 'malformed', Guard::ROOT, Guard::ROOT.'/public'));
        self::assertFalse(Guard::allowsProofRequest($this->request($url), str_repeat('c', 32), Guard::ROOT, Guard::ROOT.'/public'));
    }

    public function test_public_runtime_identity_exposes_only_safe_fields_without_pdo_or_secret_values(): void
    {
        $config = ['driver' => 'mysql', 'host' => '127.0.0.1', 'port' => 3306, 'database' => 'gr2',
            'password' => 'synthetic-password-m52', 'username' => 'synthetic-user-m52', 'token' => 'synthetic-token-m52'];
        $db = new class(null, 'gr2', '', $config) extends Connection {
            public int $identitySelectCalls = 0;
            public function selectOne($query, $bindings = [], $useReadPdo = true)
            {
                if ($query !== 'SELECT DATABASE() AS db, @@port AS port' || $bindings !== [] || $useReadPdo !== true) {
                    throw new RuntimeException('Unexpected synthetic identity query.');
                }
                $this->identitySelectCalls++;

                return (object) ['db' => 'gr2', 'port' => 3306];
            }
        };
        $oldKey = config('app.key'); config(['app.key' => 'synthetic-app-key-m52']);
        try { $identity = Guard::publicIdentity($db, Guard::ROOT.'/public'); }
        finally { config(['app.key' => $oldKey]); }
        self::assertSame(1, $db->identitySelectCalls);
        self::assertSame(['environment', 'site_mode', 'application_root', 'document_root',
            'db_driver', 'db_host', 'db_port', 'database'], array_keys($identity));
        self::assertSame('mysql', $identity['db_driver']);
        self::assertSame('127.0.0.1', $identity['db_host']);
        self::assertSame(3306, $identity['db_port']);
        self::assertSame('gr2', $identity['database']);
        $encoded = json_encode($identity, JSON_THROW_ON_ERROR);
        foreach (['synthetic-password-m52', 'synthetic-user-m52', 'synthetic-token-m52', 'synthetic-app-key-m52'] as $secret) {
            self::assertStringNotContainsString($secret, $encoded);
        }
    }
}
