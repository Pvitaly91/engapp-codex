<?php

namespace Tests\Feature;

use App\Services\M44LocalTargetGuard as Guard;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class M44LocalTargetGuardTest extends TestCase
{
    private const NOW = 1800000000;

    public static function capture(): array
    {
        return ['at' => gmdate('c', self::NOW), 'localHost' => 'gramlyze.loc', 'scheme' => 'http',
            'documentRoot' => Guard::ROOT.'/public', 'vhostSource' => Guard::PHYSICAL_FILES[1], 'vhostLine' => 10,
            'configWrites' => false, 'routeAdded' => false, 'dbWrites' => false,
            'hashes' => array_fill_keys(Guard::PHYSICAL_FILES, hash('sha256', 'fixture')),
            'effectiveVhostSha256' => hash('sha256', 'fixture-vhost'),
            'runtime' => ['dns' => [['Name' => 'gramlyze.loc', 'IPAddress' => '127.0.0.1']],
                'ports' => [['LocalAddress' => '0.0.0.0', 'LocalPort' => 80, 'OwningProcess' => 100]],
                'processes' => [['Name' => 'httpd.exe', 'ProcessId' => 100]]]];
    }

    public static function proof(): array
    {
        return ['target' => 'gramlyze.loc', 'nonce' => str_repeat('a', 32), 'created_at' => gmdate('c', self::NOW),
            'scope' => Guard::SCOPE, 'physical_basename' => 'physical-target-before-v1.json',
            'physical_sha256' => hash('sha256', 'fixture-capture'), 'routes_before_sha256' => hash('sha256', 'fixture-routes')];
    }

    public function test_fresh_capture_and_exact_three_owner_proof_are_accepted_without_host_access(): void
    {
        Guard::assertPhysicalCapture(self::capture(), self::NOW);
        Guard::assertProofMetadata(self::proof(), 'local-proof-'.str_repeat('a', 32).'.json', self::NOW);
        self::assertCount(3, Guard::SCOPE);
        self::assertSame(['uk', 'uk', 'uk'], array_column(Guard::SCOPE, 'locale'));
        self::assertSame(['/theory/maibutni-formy/future-simple/will-vs-be-going-to', '/theory/maibutni-formy/present-continuous-for-future', '/theory/maibutni-formy/choosing-the-right-future-form'], array_column(Guard::SCOPE, 'path'));
    }

    public static function invalidCaptures(): array
    {
        $cases = [];
        foreach ([['localHost', 'gramlyze.com'], ['localHost', 'gramlyze.ub'], ['scheme', 'https'],
            ['documentRoot', 'D:/other/public'], ['vhostSource', 'C:/other.conf'], ['vhostLine', 0],
            ['configWrites', true], ['routeAdded', true], ['dbWrites', true], ['hashes', []],
            ['effectiveVhostSha256', 'not-a-digest'], ['at', gmdate('c', self::NOW - 1801)],
            ['at', gmdate('c', self::NOW + 61)], ['at', 'now']] as [$key, $value]) {
            $row = self::capture(); $row[$key] = $value; $cases[] = [$row];
        }
        foreach (['dns', 'ports', 'processes'] as $part) {
            $row = self::capture(); $row['runtime'][$part] = []; $cases[] = [$row];
        }
        $row = self::capture(); $row['runtime']['dns'][] = ['Name' => 'gramlyze.loc', 'IPAddress' => '192.168.0.104']; $cases[] = [$row];
        $row = self::capture(); $row['runtime']['ports'][0]['LocalPort'] = 443; $cases[] = [$row];
        $row = self::capture(); $row['runtime']['ports'][0]['OwningProcess'] = 999; $cases[] = [$row];
        $row = self::capture(); $row['runtime']['processes'][0]['Name'] = 'ssh.exe'; $cases[] = [$row];
        $row = self::capture(); $row['hashes'][Guard::PHYSICAL_FILES[0]] = 'broken'; $cases[] = [$row];
        return $cases;
    }

    #[DataProvider('invalidCaptures')]
    public function test_stale_remote_changed_or_incomplete_physical_capture_is_rejected(array $capture): void
    {
        $this->expectException(RuntimeException::class);
        Guard::assertPhysicalCapture($capture, self::NOW);
    }

    public static function invalidProofs(): array
    {
        $cases = [];
        foreach ([['target', 'gramlyze.ub'], ['nonce', str_repeat('b', 32)], ['scope', []],
            ['created_at', gmdate('c', self::NOW - 1801)], ['created_at', gmdate('c', self::NOW + 61)],
            ['physical_basename', '../physical-target-before-v1.json'], ['physical_basename', 'physical-target-after-v1.json'],
            ['physical_sha256', 'invalid'], ['routes_before_sha256', 'invalid']] as [$key, $value]) {
            $row = self::proof(); $row[$key] = $value; $cases[] = [$row];
        }
        $row = self::proof(); $row['scope'][0]['locale'] = 'en'; $cases[] = [$row];
        $row = self::proof(); $row['scope'][0]['path'] = '/theory/future-simple/will-vs-be-going-to'; $cases[] = [$row];
        $row = self::proof(); $row['scope'][0]['identity'] = 'Database\\Seeders\\OtherSeeder'; $cases[] = [$row];
        $row = self::proof(); $row['scope'][] = $row['scope'][0]; $cases[] = [$row];
        return $cases;
    }

    #[DataProvider('invalidProofs')]
    public function test_proof_cannot_broaden_scope_reuse_stale_capture_or_change_nonce(array $proof): void
    {
        $this->expectException(RuntimeException::class);
        Guard::assertProofMetadata($proof, 'local-proof-'.str_repeat('a', 32).'.json', self::NOW);
    }

    public function test_public_identity_is_allowlisted_and_does_not_serialize_secrets(): void
    {
        config(['app.key' => 'never-return-this-secret']);
        $db = \Mockery::mock(Connection::class);
        $db->shouldReceive('selectOne')->once()->with('SELECT DATABASE() AS db, @@port AS port')->andReturn((object) ['db' => 'fixture', 'port' => 3306]);
        $db->shouldReceive('getDriverName')->once()->andReturn('mysql');
        $db->shouldReceive('getConfig')->once()->with('host')->andReturn('localhost');
        $identity = Guard::publicIdentity($db, base_path('public'));
        self::assertSame(['environment', 'site_mode', 'application_root', 'document_root', 'db_driver', 'db_host', 'db_port', 'database'], array_keys($identity));
        self::assertStringNotContainsString('never-return-this-secret', json_encode($identity));
        self::assertSame('development', $identity['site_mode']);
    }

    public function test_exact_config_is_accepted_without_connecting_to_mysql(): void
    {
        $db = new Connection(null, 'gr2', '', ['driver' => 'mysql', 'host' => 'localhost', 'database' => 'gr2', 'port' => 3306]);
        Guard::assertConfiguredDatabase($db);
        self::assertTrue(true);
    }

    public static function invalidConfigurations(): array
    {
        return array_map(static fn ($row) => [$row], [['driver' => 'sqlite'], ['host' => '192.168.0.104'],
            ['database' => 'other'], ['port' => 3307], ['url' => 'mysql://remote'], ['unix_socket' => '/tmp/mysql.sock'],
            ['read' => ['host' => 'localhost']], ['write' => ['host' => 'localhost']]]);
    }

    #[DataProvider('invalidConfigurations')]
    public function test_wrong_config_is_rejected_before_pdo_or_select(array $changes): void
    {
        $db = new Connection(null, 'fixture', '', array_replace(['driver' => 'mysql', 'host' => 'localhost', 'database' => 'gr2', 'port' => 3306], $changes));
        $this->expectException(RuntimeException::class);
        Guard::assertConfiguredDatabase($db);
    }

    public function test_fixture_database_and_production_are_never_working_targets(): void
    {
        self::assertSame('sqlite', DB::connection()->getDriverName());
        $this->expectException(RuntimeException::class);
        (new Guard)->verify(DB::connection(), 'gramlyze.loc', storage_path('app/seo-m44-local'), null, ['db' => 'gr2', 'port' => 3306]);
    }

    public function test_production_optin_is_rejected_before_any_query(): void
    {
        $this->expectException(RuntimeException::class);
        (new Guard)->verify(DB::connection(), 'gramlyze.com', storage_path(), null, []);
    }

    public function test_inherited_runtime_hmac_and_live_listener_checks_remain_strict(): void
    {
        $guard = new Guard;
        $guard->assertEvidence(M11LocalTargetGuardTest::evidence(), 'd:/dev/htdocs/gramlyze.loc', 200, 3306);
        $expected = ['target' => 'gramlyze.loc', 'nonce' => str_repeat('a', 32), 'sha256' => hash('sha256', 'fixture-runtime')];
        $guard->assertRuntimeProof($expected, $expected);
        $this->expectException(RuntimeException::class);
        $guard->assertRuntimeProof($expected, array_replace($expected, ['sha256' => hash('sha256', 'foreign-runtime')]));
    }

    public static function forbiddenRequests(): array
    {
        return [['HEAD'], ['POST'], ['PUT'], ['DELETE'], ['GET', 'gramlyze.com'], ['GET', 'gramlyze.ub'],
            ['GET', 'gramlyze.loc', '192.168.0.104'], ['GET', 'gramlyze.loc', '127.0.0.1', 'https'],
            ['GET', 'gramlyze.loc:8080'],
            ['GET', 'gramlyze.loc', '127.0.0.1', 'http', 'HTTP_FORWARDED'],
            ['GET', 'gramlyze.loc', '127.0.0.1', 'http', 'HTTP_X_FORWARDED_FOR'],
            ['GET', 'gramlyze.loc', '127.0.0.1', 'http', 'HTTP_X_FORWARDED_HOST'],
            ['GET', 'gramlyze.loc', '127.0.0.1', 'http', 'HTTP_X_REAL_IP'],
            ['GET', 'gramlyze.loc', '127.0.0.1', 'http', 'HTTP_VIA'],
            ['GET', 'gramlyze.loc', '127.0.0.1', 'http', 'HTTP_AUTHORIZATION'],
            ['GET', 'gramlyze.loc', '127.0.0.1', 'http', 'HTTP_COOKIE'],
            ['GET', 'gramlyze.loc', '127.0.0.1', 'http', 'HTTP_REFERER']];
    }

    #[DataProvider('forbiddenRequests')]
    public function test_route_rejects_head_foreign_hosts_forwarding_auth_and_cookies(string $method, string $host = 'gramlyze.loc', string $peer = '127.0.0.1', string $scheme = 'http', ?string $header = null): void
    {
        $nonce = str_repeat('a', 32);
        $server = ['REMOTE_ADDR' => $peer, 'HTTP_HOST' => $host];
        if ($header !== null) { $server[$header] = 'forbidden'; }
        $request = Request::create($scheme.'://'.$host.'/api/_local/m44-target-'.$nonce, $method, server: $server);
        self::assertFalse(Guard::allowsProofRequest($request, $nonce, Guard::ROOT, Guard::ROOT.'/public'));
    }

    public function test_route_accepts_only_exact_nonce_host_root_and_document_identity(): void
    {
        $nonce = str_repeat('a', 32);
        $url = 'http://gramlyze.loc/api/_local/m44-target-'.$nonce;
        $server = ['REMOTE_ADDR' => '127.0.0.1', 'HTTP_HOST' => 'gramlyze.loc'];
        $request = Request::create($url, 'GET', server: $server);
        self::assertTrue(Guard::allowsProofRequest($request, $nonce, Guard::ROOT, Guard::ROOT.'/public'));
        self::assertFalse(Guard::allowsProofRequest($request, str_repeat('b', 32), Guard::ROOT, Guard::ROOT.'/public'));
        self::assertFalse(Guard::allowsProofRequest($request, $nonce, base_path(), base_path('public')));
        self::assertFalse(Guard::allowsProofRequest($request, $nonce, Guard::ROOT, Guard::ROOT));
        self::assertFalse(Guard::allowsProofRequest($request, $nonce, 'missing-m44-directory', 'missing-m44-public'));
        self::assertFalse(Guard::allowsProofRequest(Request::create($url.'?extra=1', 'GET', server: $server), $nonce, Guard::ROOT, Guard::ROOT.'/public'));
    }
}
