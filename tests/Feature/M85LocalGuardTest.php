<?php

namespace Tests\Feature;

use App\Services\M85LocalTargetGuard as Guard;
use App\Support\M85VerbToBe as Package;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use PDO;
use RuntimeException;
use Tests\TestCase;

/** Pure policy and mocked identity only: never inspect a listener, read .env or contact MySQL/HTTP. */
class M85LocalGuardTest extends TestCase
{
    private function refused(callable $call): void
    {
        $caught = null;
        try { $call(); } catch (RuntimeException $error) { $caught = $error; }
        self::assertInstanceOf(RuntimeException::class, $caught);
    }

    private function request(string $url, string $method = 'GET', array $server = []): Request
    {
        $request = Request::create($url, $method, [], [], [], [
            'REMOTE_ADDR' => '127.0.0.1', 'REQUEST_METHOD' => $method,
        ]);
        $request->server->add($server);
        $request->headers->replace($request->server->getHeaders());
        return $request;
    }

    private function proof(int $now): array
    {
        return ['target' => 'gramlyze.loc', 'nonce' => str_repeat('a', 32),
            'created_at' => gmdate('c', $now), 'scope' => Guard::scope(),
            'physical_hashes' => array_fill_keys([
                'C:/Program Files/xampp/apache/conf/httpd.conf',
                'C:/Program Files/xampp/apache/conf/extra/httpd-vhosts.conf',
                'C:/Program Files/xampp/apache/conf/extra/gramlyze-fastcgi.conf',
                Guard::ROOT.'/public/index.php',
            ], str_repeat('b', 64))];
    }

    public function test_scope_is_exactly_six_local_verb_to_be_owners(): void
    {
        $paths = array_map(static fn ($slug) => '/theory/basic-grammar/verb-to-be/'.$slug, Package::SLUGS);
        $expected = [];
        foreach (Package::OWNERS as $i => $owner) { $expected[] = ['identity' => $owner, 'locale' => 'uk', 'path' => $paths[$i]]; }
        self::assertSame($expected, Guard::scope());
        self::assertSame('D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m85-local', Guard::privateDirectory());
        self::assertStringNotContainsString('AdvancedFrontingAndEmphasisTheorySeeder', json_encode(Guard::scope(), JSON_THROW_ON_ERROR));
        self::assertNotSame(realpath(base_path()), realpath(app()->environmentPath()));
        self::assertTrue(app()->environment('testing'));
    }

    public function test_proof_requires_exact_scope_nonce_freshness_and_ordered_physical_hash_inventory(): void
    {
        $now = 1780000000; $proof = $this->proof($now); $name = 'local-proof-'.$proof['nonce'].'.json';
        Guard::assertFreshProof($proof, $name, $now);
        Guard::assertFreshProof(array_replace($proof, ['created_at' => gmdate('c', $now - 1800)]), $name, $now);
        Guard::assertFreshProof(array_replace($proof, ['created_at' => gmdate('c', $now + 60)]), $name, $now);
        $this->addToAssertionCount(3);
        foreach ([
            static function (&$p) { $p['target'] = 'gramlyze.com'; },
            static function (&$p) { $p['target'] = 'gramlyze.ub'; },
            static function (&$p) { array_pop($p['scope']); },
            static function (&$p) { $p['scope'][] = $p['scope'][0]; },
            static function (&$p) { $p['scope'][0]['locale'] = 'en'; },
            static function (&$p) { $p['scope'][0]['path'] = '/theory/verb-to-be/verb-to-be-present'; },
            static function (&$p) { $p['scope'][4]['identity'] = 'Database\\Seeders\\Page_V3\\AdvancedFrontingAndEmphasisTheorySeeder'; },
            static function (&$p) { $p['nonce'] = str_repeat('c', 32); },
            static function (&$p) { $p['nonce'] = str_repeat('A', 32); },
            static function (&$p) use ($now) { $p['created_at'] = gmdate('c', $now - 1801); },
            static function (&$p) use ($now) { $p['created_at'] = gmdate('c', $now + 61); },
            static function (&$p) { $p['created_at'] = 'not-a-date'; },
            static function (&$p) { $p['physical_hashes'] = array_reverse($p['physical_hashes'], true); },
            static function (&$p) { unset($p['physical_hashes'][Guard::ROOT.'/public/index.php']); },
            static function (&$p) { $p['physical_hashes']['unexpected'] = str_repeat('b', 64); },
            static function (&$p) { $p['physical_hashes'][Guard::ROOT.'/public/index.php'] = 'pending'; },
        ] as $mutate) { $bad = $proof; $mutate($bad); $this->refused(fn () => Guard::assertFreshProof($bad, $name, $now)); }
        foreach (['../'.$name, '/'.$name, str_replace('.json', '.JSON', $name), 'local-proof-'.str_repeat('a', 31).'.json'] as $badName) {
            $this->refused(fn () => Guard::assertFreshProof($proof, $badName, $now));
            $this->refused(fn () => Guard::readFreshProof($badName));
        }
    }

    public function test_proof_request_is_only_plain_http_exact_host_nonce_path_and_loopback_get_not_head(): void
    {
        $nonce = str_repeat('a', 32); $url = 'http://gramlyze.loc/api/_local/m85-target-'.$nonce;
        $check = static fn ($request) => Guard::allowsProofRequest($request, $nonce, Guard::ROOT, Guard::ROOT.'/public');
        self::assertTrue($check($this->request($url)));
        self::assertTrue($check($this->request($url, 'GET', ['REMOTE_ADDR' => '::1'])));
        self::assertTrue($check($this->request(str_replace('gramlyze.loc/', 'gramlyze.loc:80/', $url))));
        foreach (['POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD'] as $method) { self::assertFalse($check($this->request($url, $method)), $method); }
        foreach (['https://gramlyze.loc', 'http://gramlyze.com', 'http://gramlyze.ub',
            'http://127.0.0.1', 'http://gramlyze.loc:8080', 'http://other.gramlyze.loc'] as $origin) {
            self::assertFalse($check($this->request($origin.'/api/_local/m85-target-'.$nonce)), $origin);
        }
        foreach ([$url.'?x=1', $url.'/', str_replace('m85-target-', 'm84-target-', $url), str_replace($nonce, str_repeat('c', 32), $url)] as $bad) {
            self::assertFalse($check($this->request($bad)), $bad);
        }
        foreach (['192.168.0.104', '0.0.0.0', '::ffff:127.0.0.1'] as $address) {
            self::assertFalse($check($this->request($url, 'GET', ['REMOTE_ADDR' => $address])));
        }
        self::assertFalse($check($this->request($url, 'GET', ['REQUEST_METHOD' => 'HEAD'])));
        self::assertFalse(Guard::allowsProofRequest($this->request($url), 'invalid', Guard::ROOT, Guard::ROOT.'/public'));
        self::assertFalse(Guard::allowsProofRequest($this->request($url), $nonce, Guard::ROOT.'/public', Guard::ROOT));
    }

    public function test_forwarded_credentials_cookies_referer_and_origin_headers_cannot_authorize_proof(): void
    {
        $nonce = str_repeat('a', 32); $url = 'http://gramlyze.loc/api/_local/m85-target-'.$nonce;
        foreach (['HTTP_COOKIE' => 'fixture=not-a-secret', 'HTTP_AUTHORIZATION' => 'fixture',
            'HTTP_REFERER' => 'http://gramlyze.loc/', 'HTTP_FORWARDED' => 'for=127.0.0.1',
            'HTTP_X_FORWARDED_HOST' => 'gramlyze.loc', 'HTTP_X_FORWARDED_FOR' => '127.0.0.1',
            'HTTP_X_FORWARDED_PROTO' => 'http', 'HTTP_X_REAL_IP' => '127.0.0.1', 'HTTP_VIA' => 'fixture',
            'HTTP_ORIGIN' => 'https://foreign.invalid'] as $header => $value) {
            self::assertFalse(Guard::allowsProofRequest($this->request($url, 'GET', [$header => $value]), $nonce, Guard::ROOT, Guard::ROOT.'/public'), $header);
        }
        foreach (['null', 'http://gramlyze.loc'] as $origin) {
            self::assertFalse(Guard::allowsProofRequest($this->request($url, 'GET', ['HTTP_ORIGIN' => $origin]), $nonce, Guard::ROOT, Guard::ROOT.'/public'), 'Origin '.$origin);
        }
    }

    private function connectionMock(array $overrides = [], string $driver = 'mysql', bool $identityQuery = false): Connection
    {
        $config = array_replace(['host' => 'localhost', 'database' => 'gr2', 'port' => 3306,
            'url' => null, 'unix_socket' => null, 'read' => null, 'write' => null,
            'password' => 'synthetic-password-do-not-expose'], $overrides);
        $db = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()
            ->onlyMethods(['getDriverName', 'getConfig', 'selectOne', 'getPdo', 'statement', 'insert', 'update', 'delete'])->getMock();
        $db->method('getDriverName')->willReturn($driver);
        $db->method('getConfig')->willReturnCallback(static fn ($key = null) => $key === null ? $config : ($config[$key] ?? null));
        if ($identityQuery) { $db->expects(self::once())->method('selectOne')->with('SELECT DATABASE() AS db, @@port AS port')->willReturn((object) ['db' => 'gr2', 'port' => 3306]); }
        else { $db->expects(self::never())->method('selectOne'); }
        foreach (['statement', 'insert', 'update', 'delete'] as $method) { $db->expects(self::never())->method($method); }
        return $db;
    }

    public function test_database_target_rejects_remote_split_socket_url_and_wrong_database_before_any_query(): void
    {
        foreach ([['host' => '192.168.0.104'], ['host' => 'gramlyze.ub'], ['database' => 'production'], ['port' => 3307],
            ['url' => 'mysql://fixture.invalid/gr2'], ['unix_socket' => '/synthetic/mysql.sock'],
            ['read' => ['host' => 'localhost']], ['write' => ['host' => 'localhost']]] as $config) {
            $this->refused(fn () => Guard::assertConfiguredDatabase($this->connectionMock($config)));
        }
        $this->refused(fn () => Guard::assertConfiguredDatabase($this->connectionMock([], 'sqlite')));
        foreach (['localhost', '127.0.0.1', '::1'] as $host) { Guard::assertConfiguredDatabase($this->connectionMock(['host' => $host, 'port' => '3306'])); }
        $this->addToAssertionCount(3);
    }

    public function test_public_identity_is_read_only_and_omits_password_key_cookie_and_token_fields(): void
    {
        config(['app.key' => 'synthetic-application-key-do-not-expose']);
        $identity = Guard::publicIdentity($this->connectionMock(identityQuery: true), Guard::ROOT.'/public');
        self::assertSame(['environment', 'site_mode', 'application_root', 'document_root', 'db_driver', 'db_host', 'db_port', 'database'], array_keys($identity));
        self::assertSame('testing', $identity['environment']);
        self::assertSame('development', $identity['site_mode']);
        self::assertSame('mysql', $identity['db_driver']); self::assertSame('gr2', $identity['database']);
        self::assertSame(3306, $identity['db_port']);
        $serialized = json_encode($identity, JSON_THROW_ON_ERROR);
        foreach (['synthetic-password-do-not-expose', 'synthetic-application-key-do-not-expose', 'APP_KEY', 'password', 'cookie', 'token'] as $secret) {
            self::assertStringNotContainsString($secret, $serialized);
        }
    }

    public function test_physical_and_runtime_proof_are_exact_without_inspecting_actual_processes(): void
    {
        $guard = new Guard(); $root = strtolower(Guard::ROOT);
        $evidence = ['addresses' => ['127.0.0.1', '::1'], 'document_root' => $root.'/public', 'active_vhosts' => 1,
            'apache_name' => 'httpd.exe', 'apache_pid' => 1234, 'apache_pid_file' => 1234,
            'mysql_listeners' => [['port' => 3306, 'pid' => 5678, 'name' => 'mysqld.exe']]];
        $guard->assertEvidence($evidence, $root, 5678, 3306); $this->addToAssertionCount(1);
        foreach ([
            static function (&$e) { $e['addresses'][] = '192.168.0.104'; },
            static function (&$e) { $e['addresses'] = []; },
            static function (&$e) { $e['document_root'] .= '/other'; },
            static function (&$e) { $e['active_vhosts'] = 2; },
            static function (&$e) { $e['apache_pid_file']++; },
            static function (&$e) { $e['mysql_listeners'][0]['port'] = 3307; },
            static function (&$e) { $e['mysql_listeners'][0]['pid']++; },
            static function (&$e) { $e['mysql_listeners'][0]['name'] = 'ssh.exe'; },
        ] as $mutate) { $bad = $evidence; $mutate($bad); $this->refused(fn () => $guard->assertEvidence($bad, $root, 5678, 3306)); }
        $proof = ['target' => 'gramlyze.loc', 'nonce' => str_repeat('a', 32), 'sha256' => str_repeat('b', 64)];
        $guard->assertRuntimeProof($proof, $proof); $this->addToAssertionCount(1);
        foreach (['target' => 'gramlyze.ub', 'nonce' => str_repeat('c', 32), 'sha256' => str_repeat('c', 64)] as $field => $value) {
            $this->refused(fn () => $guard->assertRuntimeProof($proof, array_replace($proof, [$field => $value])));
        }
    }

    public function test_runtime_digest_carries_only_nonce_target_and_fingerprint_not_its_secret_inputs(): void
    {
        config(['app.key' => 'synthetic-runtime-key-do-not-expose']);
        $pdo = $this->getMockBuilder(PDO::class)->disableOriginalConstructor()->onlyMethods(['getAttribute'])->getMock();
        $pdo->expects(self::exactly(2))->method('getAttribute')->willReturnCallback(static fn ($attribute) => $attribute === PDO::ATTR_DRIVER_NAME ? 'mysql' : 'synthetic-loopback-status');
        $db = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->onlyMethods(['selectOne', 'getConfig', 'getPdo'])->getMock();
        $db->expects(self::once())->method('selectOne')->with('SELECT DATABASE() AS db, @@hostname AS server, @@port AS port, @@pid_file AS pid_file')
            ->willReturn((object) ['db' => 'gr2', 'server' => 'synthetic', 'port' => 3306, 'pid_file' => 'synthetic.pid']);
        $db->method('getConfig')->willReturn(['password' => 'synthetic-runtime-password-do-not-expose']);
        $db->method('getPdo')->willReturn($pdo);
        $proof = Guard::runtimeProof($db, str_repeat('a', 32), Guard::ROOT.'/public');
        self::assertSame(['target', 'nonce', 'sha256'], array_keys($proof));
        self::assertSame('gramlyze.loc', $proof['target']); self::assertSame(str_repeat('a', 32), $proof['nonce']);
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D', $proof['sha256']);
        $serialized = json_encode($proof, JSON_THROW_ON_ERROR);
        foreach (['synthetic-runtime-key-do-not-expose', 'synthetic-runtime-password-do-not-expose', 'password', 'APP_KEY', 'synthetic.pid'] as $secret) {
            self::assertStringNotContainsString($secret, $serialized);
        }
    }
}
