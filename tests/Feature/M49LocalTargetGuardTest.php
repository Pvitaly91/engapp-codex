<?php

namespace Tests\Feature;

use App\Services\M49LocalTargetGuard as Guard;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Policy fixtures only: requests are never dispatched and Connection is a
 * no-I/O double. ROOT/public realpath checks read directory identity only.
 */
class M49LocalTargetGuardTest extends TestCase
{
    private const NONCE = '0123456789abcdef0123456789abcdef';

    public function test_scope_is_exactly_one_conditional_and_two_existing_uk_modal_owners(): void
    {
        self::assertSame([
            [
                'identity' => 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsHadBetterTheorySeeder',
                'locale' => 'uk',
                'path' => '/theory/conditionals/had-better',
            ],
            [
                'identity' => 'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsCanCouldTheorySeeder',
                'locale' => 'uk',
                'path' => '/theory/modal-verbs/can-could',
            ],
            [
                'identity' => 'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsMayMightTheorySeeder',
                'locale' => 'uk',
                'path' => '/theory/modal-verbs/may-might',
            ],
        ], Guard::SCOPE);
    }

    public function test_category_level_and_definition_bindings_are_per_owner_not_a_uniform_conditional_profile(): void
    {
        self::assertSame(['had-better', 'can-could', 'may-might'], array_column(Guard::BINDINGS, 'slug'));
        self::assertSame([['conditionals'], ['modal-verbs'], ['modal-verbs']], array_column(Guard::BINDINGS, 'ancestry'));
        self::assertSame(['A2', 'A1', 'A2'], array_column(Guard::BINDINGS, 'technical_level'));
        self::assertSame(['A2-B1', 'A1-A2', 'A2-B1'], array_column(Guard::BINDINGS, 'display_level'));
        self::assertSame(array_column(Guard::SCOPE, 'identity'), array_column(Guard::BINDINGS, 'identity'));
        self::assertSame([
            'database/seeders/Page_V3/Conditionals/ConditionalsHadBetterTheorySeeder/definition.json',
            'database/seeders/Page_V3/ModalVerbs/ModalVerbsCanCouldTheorySeeder/definition.json',
            'database/seeders/Page_V3/ModalVerbs/ModalVerbsMayMightTheorySeeder/definition.json',
        ], array_column(Guard::BINDINGS, 'definition'));
    }

    public function test_only_exact_loopback_http_get_nonce_and_physical_roots_are_accepted(): void
    {
        // Do not turn two unresolved realpaths into a positive fixture.
        self::assertNotFalse(realpath(Guard::ROOT));
        self::assertNotFalse(realpath(Guard::ROOT.'/public'));

        foreach ([
            ['gramlyze.loc', '127.0.0.1'],
            ['gramlyze.loc:80', '127.0.0.1'],
            ['gramlyze.loc', '::1'],
        ] as [$host, $peer]) {
            $request = self::request(host: $host, peer: $peer);
            self::assertTrue(Guard::allowsProofRequest(
                $request, self::NONCE, Guard::ROOT, Guard::ROOT.'/public'
            ), $host.' / '.$peer);
        }
    }

    public static function forbiddenRequests(): array
    {
        return [
            'HEAD is not a read-only proof GET' => [['method' => 'HEAD']],
            'POST' => [['method' => 'POST']],
            'PUT' => [['method' => 'PUT']],
            'DELETE' => [['method' => 'DELETE']],
            'PATCH' => [['method' => 'PATCH']],
            'OPTIONS' => [['method' => 'OPTIONS']],
            'TRACE' => [['method' => 'TRACE']],
            'production com' => [['host' => 'gramlyze.com']],
            'other deployment ub' => [['host' => 'gramlyze.ub']],
            'host suffix spoof' => [['host' => 'gramlyze.loc.example.test']],
            'numeric host is not the vhost' => [['host' => '127.0.0.1']],
            'different local port' => [['host' => 'gramlyze.loc:8080']],
            'HTTPS is outside this local proof' => [['scheme' => 'https']],
            'nonce path trailing slash' => [['path' => '/api/_local/m49-target-'.self::NONCE.'/']],
            'LAN peer' => [['peer' => '192.168.0.104']],
            'non-loopback IPv6 peer' => [['peer' => '2001:db8::46']],
            'forwarded' => [['headers' => ['HTTP_FORWARDED' => 'for=127.0.0.1']]],
            'forwarded for' => [['headers' => ['HTTP_X_FORWARDED_FOR' => '127.0.0.1']]],
            'forwarded host' => [['headers' => ['HTTP_X_FORWARDED_HOST' => 'gramlyze.loc']]],
            'forwarded proto' => [['headers' => ['HTTP_X_FORWARDED_PROTO' => 'http']]],
            'forwarded port' => [['headers' => ['HTTP_X_FORWARDED_PORT' => '80']]],
            'other forwarded header' => [['headers' => ['HTTP_X_FORWARDED_PREFIX' => '/']]],
            'real IP' => [['headers' => ['HTTP_X_REAL_IP' => '127.0.0.1']]],
            'via' => [['headers' => ['HTTP_VIA' => 'fixture-proxy']]],
            'authorization' => [['headers' => ['HTTP_AUTHORIZATION' => 'Bearer fixture-not-a-secret']]],
            'cookie' => [['headers' => ['HTTP_COOKIE' => 'fixture=value']]],
            'referer' => [['headers' => ['HTTP_REFERER' => 'http://gramlyze.loc/']]],
        ];
    }

    #[DataProvider('forbiddenRequests')]
    public function test_request_policy_rejects_foreign_method_host_transport_peer_or_headers(array $changes): void
    {
        $request = self::request(...$changes);
        self::assertFalse(Guard::allowsProofRequest(
            $request, self::NONCE, Guard::ROOT, Guard::ROOT.'/public'
        ));
    }

    public function test_method_override_cannot_turn_post_into_an_allowed_get(): void
    {
        $request = self::request(method: 'POST', headers: ['HTTP_X_HTTP_METHOD_OVERRIDE' => 'GET']);
        // Symfony itself refuses a GET override. The guard must also reject
        // a separately interpreted GET when the original server method is POST.
        self::assertSame('POST', $request->getMethod());
        self::assertSame('POST', $request->server('REQUEST_METHOD'));
        self::assertFalse(Guard::allowsProofRequest(
            $request, self::NONCE, Guard::ROOT, Guard::ROOT.'/public'
        ));

        $interpreted = self::request();
        self::assertSame('GET', $interpreted->getMethod());
        $interpreted->server->set('REQUEST_METHOD', 'POST');
        self::assertSame('GET', $interpreted->getMethod());
        self::assertFalse(Guard::allowsProofRequest(
            $interpreted, self::NONCE, Guard::ROOT, Guard::ROOT.'/public'
        ));
    }

    public function test_nonce_endpoint_and_query_must_match_without_broadening_the_route(): void
    {
        $request = self::request();
        foreach (['', str_repeat('a', 31), str_repeat('a', 33), str_repeat('A', 32), str_repeat('g', 32),
            '../'.self::NONCE, str_repeat('b', 32)] as $nonce) {
            self::assertFalse(Guard::allowsProofRequest(
                $request, $nonce, Guard::ROOT, Guard::ROOT.'/public'
            ), 'Rejected nonce: '.$nonce);
        }

        foreach ([
            '/api/_local/m46-target-'.self::NONCE,
            '/api/_local/m47-target-'.self::NONCE,
            '/api/_local/m48-target-'.self::NONCE,
            '/api/_local/m49-target-'.str_repeat('b', 32),
            '/api/_local/m49-target-'.self::NONCE.'/extra',
            '/api/_local/m49-target-'.self::NONCE.'?extra=1',
        ] as $path) {
            self::assertFalse(Guard::allowsProofRequest(
                self::request(path: $path), self::NONCE, Guard::ROOT, Guard::ROOT.'/public'
            ), $path);
        }
    }

    public function test_application_and_public_root_cannot_be_forged_or_swapped(): void
    {
        foreach ([
            [Guard::ROOT.'/public', Guard::ROOT.'/public'],
            [Guard::ROOT, Guard::ROOT],
            [Guard::ROOT.'/app', Guard::ROOT.'/public'],
            [Guard::ROOT, Guard::ROOT.'/storage'],
            [Guard::ROOT.'/__missing_m49_fixture__', Guard::ROOT.'/public'],
            [Guard::ROOT, Guard::ROOT.'/__missing_m49_public__'],
            ['D:/__missing_m49_application__', 'D:/__missing_m49_document__'],
        ] as [$application, $document]) {
            self::assertFalse(Guard::allowsProofRequest(
                self::request(), self::NONCE, $application, $document
            ), $application.' / '.$document);
        }
    }

    public function test_exact_single_loopback_mysql_config_does_not_obtain_pdo_or_query(): void
    {
        foreach (['localhost', '127.0.0.1', '::1'] as $host) {
            Guard::assertConfiguredDatabase($this->connection(['host' => $host]));
        }
        Guard::assertConfiguredDatabase($this->connection(['port' => '3306']));
    }

    public static function forbiddenDatabaseConfigurations(): array
    {
        return [
            'SQLite fixture is never working DB' => [['driver' => 'sqlite']],
            'different database driver' => [['driver' => 'pgsql']],
            'LAN DB server' => [['host' => '192.168.0.104']],
            'production hostname' => [['host' => 'gramlyze.com']],
            'array host routing' => [['host' => ['127.0.0.1']]],
            'missing host' => [['host' => null]],
            'other local database' => [['database' => 'gramlyze']],
            'case changed database' => [['database' => 'GR2']],
            'missing database' => [['database' => null]],
            'different port' => [['port' => 3307]],
            'malformed port string' => [['port' => '3306junk']],
            'fractional port' => [['port' => 3306.5]],
            'missing port' => [['port' => null]],
            'URL override' => [['url' => 'mysql://127.0.0.1/gr2']],
            'socket override' => [['unix_socket' => '/tmp/m49-fixture.sock']],
            'read routing even when local' => [['read' => ['host' => 'localhost']]],
            'write routing even when local' => [['write' => ['host' => 'localhost']]],
        ];
    }

    #[DataProvider('forbiddenDatabaseConfigurations')]
    public function test_database_config_is_rejected_before_any_pdo_or_query(array $changes): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('M49 requires the inspected single loopback MySQL gr2:3306');
        Guard::assertConfiguredDatabase($this->connection($changes));
    }

    public function test_verify_rejects_wrong_database_before_reading_private_proof(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('M49 requires the inspected single loopback MySQL gr2:3306');
        (new Guard)->verify($this->connection(['driver' => 'sqlite']), 'gramlyze.loc', '', null, ['db' => 'gr2']);
    }

    public function test_verify_rejects_wrong_target_before_private_proof_or_query(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('M49 local target differs.');
        (new Guard)->verify($this->connection(), 'gramlyze.com', '', null, ['db' => 'gr2']);
    }

    public function test_verify_rejects_mismatched_physical_database_before_private_proof_or_query(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('M49 local target differs.');
        (new Guard)->verify($this->connection(), 'gramlyze.loc', '', null, ['db' => 'other']);
    }


    public function test_private_proof_basename_rejects_paths_and_non_nonce_names_before_any_read(): void
    {
        foreach ([null, '', '../local-proof-'.self::NONCE.'.json', 'local-proof-'.str_repeat('A', 32).'.json',
            'local-proof-'.self::NONCE.'.json.bak', Guard::ROOT.'/local-proof-'.self::NONCE.'.json'] as $name) {
            $caught = null;
            try { Guard::readFreshProof($name); } catch (RuntimeException $error) { $caught = $error; }
            self::assertInstanceOf(RuntimeException::class, $caught);
            self::assertSame('M49 requires a nonce-bound private proof basename.', $caught->getMessage());
        }
    }

    public function test_runtime_proof_requires_exact_nonce_target_digest_and_shape(): void
    {
        $expected = ['target' => 'gramlyze.loc', 'nonce' => self::NONCE, 'sha256' => str_repeat('a', 64)];
        $guard = new Guard;
        $guard->assertRuntimeProof($expected, $expected);
        foreach ([
            array_replace($expected, ['target' => 'gramlyze.ub']),
            array_replace($expected, ['nonce' => str_repeat('b', 32)]),
            array_replace($expected, ['sha256' => str_repeat('b', 64)]),
            array_diff_key($expected, ['nonce' => true]),
            $expected + ['extra' => 'not part of the proof'],
        ] as $actual) {
            $caught = null;
            try { $guard->assertRuntimeProof($expected, $actual); } catch (RuntimeException $error) { $caught = $error; }
            self::assertInstanceOf(RuntimeException::class, $caught);
            self::assertSame('M49 live vhost database/runtime proof differs from the CLI working connection.', $caught->getMessage());
        }
    }

    public function test_physical_evidence_is_bound_to_one_vhost_and_matching_mysql_process(): void
    {
        $root = strtolower(str_replace('\\', '/', Guard::ROOT));
        $evidence = [
            'addresses' => ['127.0.0.1', '::1'], 'document_root' => $root.'/public',
            'active_vhosts' => 1, 'apache_name' => 'httpd.exe', 'apache_pid' => 11, 'apache_pid_file' => 11,
            'mysql_listeners' => [['port' => 3306, 'pid' => 22, 'name' => 'mysqld.exe']],
        ];
        $guard = new Guard;
        $guard->assertEvidence($evidence, $root, 22, 3306);
        foreach ([
            ['addresses' => ['127.0.0.1', '192.168.0.104']],
            ['document_root' => $root.'/storage'],
            ['active_vhosts' => 2],
            ['apache_pid_file' => 12],
            ['apache_name' => 'proxy.exe'],
            ['mysql_listeners' => [['port' => 3307, 'pid' => 22, 'name' => 'mysqld.exe']]],
            ['mysql_listeners' => [['port' => 3306, 'pid' => 23, 'name' => 'mysqld.exe']]],
            ['mysql_listeners' => [['port' => 3306, 'pid' => 22, 'name' => 'proxy.exe']]],
        ] as $change) {
            $caught = null;
            try { $guard->assertEvidence(array_replace($evidence, $change), $root, 22, 3306); }
            catch (RuntimeException $error) { $caught = $error; }
            self::assertInstanceOf(RuntimeException::class, $caught);
        }
    }


    public function test_fresh_proof_timing_and_exact_scope_are_checked_without_working_files(): void
    {
        $now = strtotime('2026-10-12T12:00:00Z');
        $name = 'local-proof-'.self::NONCE.'.json';
        $proof = [
            'target' => 'gramlyze.loc', 'nonce' => self::NONCE, 'scope' => Guard::SCOPE,
            'created_at' => gmdate('c', $now),
            'physical_hashes' => [
                'C:/Program Files/xampp/apache/conf/httpd.conf' => str_repeat('a', 64),
                'C:/Program Files/xampp/apache/conf/extra/httpd-vhosts.conf' => str_repeat('b', 64),
                'C:/Program Files/xampp/apache/conf/extra/gramlyze-fastcgi.conf' => str_repeat('c', 64),
                Guard::ROOT.'/public/index.php' => str_repeat('d', 64),
            ],
        ];
        foreach ([0, -1800, 60] as $offset) {
            Guard::assertFreshProof(array_replace($proof, ['created_at' => gmdate('c', $now + $offset)]), $name, $now);
            $this->addToAssertionCount(1);
        }
        $wrongLocale = Guard::SCOPE; $wrongLocale[0]['locale'] = 'en';
        $wrongOwner = Guard::SCOPE;
        $wrongOwner[2]['identity'] = 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsFirstTheorySeeder';
        $wrongNeighbor = Guard::SCOPE;
        $wrongNeighbor[2]['path'] = '/theory/conditionals/third-conditional';
        $swappedCanCategory = Guard::SCOPE;
        $swappedCanCategory[1]['path'] = '/theory/conditionals/can-could';
        $swappedMayCategory = Guard::SCOPE;
        $swappedMayCategory[2]['path'] = '/theory/conditionals/may-might';
        $swappedHadCategory = Guard::SCOPE;
        $swappedHadCategory[0]['path'] = '/theory/modal-verbs/had-better';
        $swappedOwners = Guard::SCOPE;
        [$swappedOwners[1]['identity'], $swappedOwners[2]['identity']] =
            [$swappedOwners[2]['identity'], $swappedOwners[1]['identity']];
        $physical = $proof['physical_hashes'];
        unset($physical[Guard::ROOT.'/public/index.php']);
        foreach ([
            ['created_at' => gmdate('c', $now - 1801)],
            ['created_at' => gmdate('c', $now + 61)],
            ['created_at' => 'not a date'],
            ['created_at' => ''],
            ['target' => 'gramlyze.ub'],
            ['nonce' => str_repeat('b', 32)],
            ['nonce' => str_repeat('A', 32)],
            ['scope' => $wrongLocale],
            ['scope' => $wrongOwner],
            ['scope' => $wrongNeighbor],
            ['scope' => $swappedCanCategory],
            ['scope' => $swappedMayCategory],
            ['scope' => $swappedHadCategory],
            ['scope' => $swappedOwners],
            ['scope' => array_reverse(Guard::SCOPE)],
            ['scope' => array_slice(Guard::SCOPE, 0, 2)],
            ['physical_hashes' => $physical],
            ['physical_hashes' => array_reverse($proof['physical_hashes'], true)],
            ['physical_hashes' => $proof['physical_hashes'] + [Guard::ROOT.'/.env' => str_repeat('e', 64)]],
        ] as $change) {
            $caught = null;
            try { Guard::assertFreshProof(array_replace($proof, $change), $name, $now); }
            catch (RuntimeException $error) { $caught = $error; }
            self::assertInstanceOf(RuntimeException::class, $caught);
            self::assertSame('M49 proof scope, time or physical evidence differs.', $caught->getMessage());
        }
        foreach (['../'.$name, 'local-proof-'.str_repeat('b', 32).'.json', $name.'.bak'] as $badName) {
            $caught = null;
            try { Guard::assertFreshProof($proof, $badName, $now); }
            catch (RuntimeException $error) { $caught = $error; }
            self::assertInstanceOf(RuntimeException::class, $caught);
        }
    }

    private static function request(
        string $method = 'GET',
        string $host = 'gramlyze.loc',
        string $peer = '127.0.0.1',
        string $scheme = 'http',
        array $headers = [],
        ?string $path = null,
    ): Request {
        return Request::create(
            $scheme.'://'.$host.($path ?? '/api/_local/m49-target-'.self::NONCE),
            $method,
            server: array_replace(['REMOTE_ADDR' => $peer, 'HTTP_HOST' => $host], $headers),
        );
    }

    private function connection(array $changes = []): Connection
    {
        $config = array_replace([
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'gr2',
            'port' => 3306,
        ], $changes);
        $connection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getDriverName', 'getConfig', 'getPdo', 'getReadPdo',
                'select', 'selectOne', 'statement', 'affectingStatement', 'beginTransaction'])
            ->getMock();
        $connection->method('getDriverName')->willReturn($config['driver']);
        $connection->method('getConfig')->willReturnCallback(
            static fn (?string $key = null) => $key === null ? $config : ($config[$key] ?? null)
        );
        foreach (['getPdo', 'getReadPdo', 'select', 'selectOne', 'statement', 'affectingStatement', 'beginTransaction'] as $method) {
            $connection->expects(self::never())->method($method);
        }

        return $connection;
    }
}
