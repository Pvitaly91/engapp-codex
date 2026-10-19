<?php

namespace Tests\Feature;

use App\Services\PracticeCompletionLocalTargetGuard as Guard;
use App\Support\PracticeCompletionPackage as Package;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Pure policy fixtures: no environment boot, HTTP dispatch, PDO, working files or Windows probes. */
class PracticeCompletionLocalTargetGuardTest extends TestCase
{
    private const NONCE = '0123456789abcdef0123456789abcdef';
    private const ENDPOINT = '/api/_local/practice-completion-target-';

    public function test_scope_is_exactly_the_twelve_existing_uk_owners_and_mixed_categories(): void
    {
        $conditionals = [
            ['ConditionalsZeroTheorySeeder', 'zero-conditional'],
            ['ConditionalsFirstTheorySeeder', 'first-conditional'],
            ['ConditionalsSecondTheorySeeder', 'second-conditional'],
            ['ConditionalsThirdTheorySeeder', 'third-conditional'],
            ['ConditionalsMixedTheorySeeder', 'mixed-conditionals'],
            ['ConditionalsUnlessProvidedAsLongAsTheorySeeder', 'unless-provided-as-long-as'],
            ['ConditionalsInCaseTheorySeeder', 'in-case'],
            ['ConditionalsWishIfOnlyTheorySeeder', 'wish-if-only'],
            ['ConditionalsWouldRatherTheorySeeder', 'would-rather'],
            ['ConditionalsHadBetterTheorySeeder', 'had-better'],
        ];
        $expected = [];
        foreach ($conditionals as [$name, $slug]) {
            $expected[] = ['identity' => 'Database\\Seeders\\Page_V3\\Conditionals\\'.$name,
                'locale' => 'uk', 'path' => '/theory/conditionals/'.$slug];
        }
        foreach ([['ModalVerbsCanCouldTheorySeeder', 'can-could'], ['ModalVerbsMayMightTheorySeeder', 'may-might']] as [$name, $slug]) {
            $expected[] = ['identity' => 'Database\\Seeders\\Page_V3\\ModalVerbs\\'.$name,
                'locale' => 'uk', 'path' => '/theory/modal-verbs/'.$slug];
        }
        self::assertSame($expected, Guard::scope());
        self::assertSame(array_column($expected, 'identity'), Package::OWNERS);
        self::assertCount(12, array_unique(array_column(Guard::scope(), 'identity')));
        self::assertSame(Guard::ROOT.'/storage/app/practice-completion-local', Guard::privateDirectory());
    }

    public function test_only_exact_local_raw_get_and_loopback_roots_are_accepted(): void
    {
        self::assertNotFalse(realpath(Guard::ROOT));
        self::assertNotFalse(realpath(Guard::ROOT.'/public'));
        foreach ([['gramlyze.loc', '127.0.0.1'], ['gramlyze.loc:80', '127.0.0.1'],
            ['gramlyze.loc', '::1']] as [$host, $peer]) {
            self::assertTrue(Guard::allowsProofRequest(self::request(host: $host, peer: $peer),
                self::NONCE, Guard::ROOT, Guard::ROOT.'/public'));
        }
    }

    public static function forbiddenRequests(): array
    {
        return [
            'HEAD' => [['method' => 'HEAD']],
            'POST' => [['method' => 'POST']],
            'PUT' => [['method' => 'PUT']],
            'DELETE' => [['method' => 'DELETE']],
            'PATCH' => [['method' => 'PATCH']],
            'OPTIONS' => [['method' => 'OPTIONS']],
            'TRACE' => [['method' => 'TRACE']],
            'production com' => [['host' => 'gramlyze.com']],
            'other deployment ub' => [['host' => 'gramlyze.ub']],
            'host suffix' => [['host' => 'gramlyze.loc.example.test']],
            'numeric host' => [['host' => '127.0.0.1']],
            'uppercase raw host' => [['host' => 'GRAMLYZE.LOC']],
            'other port' => [['host' => 'gramlyze.loc:8080']],
            'HTTPS' => [['scheme' => 'https']],
            'trailing slash' => [['path' => self::ENDPOINT.self::NONCE.'/']],
            'LAN peer' => [['peer' => '192.168.0.104']],
            'remote IPv6' => [['peer' => '2001:db8::1']],
            'forwarded' => [['headers' => ['HTTP_FORWARDED' => 'for=127.0.0.1']]],
            'forwarded for' => [['headers' => ['HTTP_X_FORWARDED_FOR' => '127.0.0.1']]],
            'forwarded host' => [['headers' => ['HTTP_X_FORWARDED_HOST' => 'gramlyze.loc']]],
            'forwarded proto' => [['headers' => ['HTTP_X_FORWARDED_PROTO' => 'http']]],
            'forwarded port' => [['headers' => ['HTTP_X_FORWARDED_PORT' => '80']]],
            'forwarded prefix' => [['headers' => ['HTTP_X_FORWARDED_PREFIX' => '/']]],
            'real IP' => [['headers' => ['HTTP_X_REAL_IP' => '127.0.0.1']]],
            'via' => [['headers' => ['HTTP_VIA' => 'fixture-proxy']]],
            'authorization' => [['headers' => ['HTTP_AUTHORIZATION' => 'Bearer fixture-not-a-secret']]],
            'cookie' => [['headers' => ['HTTP_COOKIE' => 'fixture=value']]],
            'referer' => [['headers' => ['HTTP_REFERER' => 'http://gramlyze.loc/']]],
        ];
    }

    #[DataProvider('forbiddenRequests')]
    public function test_foreign_request_is_denied_without_dispatch(array $changes): void
    {
        self::assertFalse(Guard::allowsProofRequest(self::request(...$changes),
            self::NONCE, Guard::ROOT, Guard::ROOT.'/public'));
    }

    public function test_interpreted_get_cannot_hide_the_original_post_method(): void
    {
        $post = self::request(method: 'POST', headers: ['HTTP_X_HTTP_METHOD_OVERRIDE' => 'GET']);
        self::assertSame('POST', $post->getMethod());
        self::assertFalse(Guard::allowsProofRequest($post, self::NONCE, Guard::ROOT, Guard::ROOT.'/public'));
        $cached = self::request();
        self::assertSame('GET', $cached->getMethod());
        $cached->server->set('REQUEST_METHOD', 'POST');
        self::assertSame('GET', $cached->getMethod());
        self::assertFalse(Guard::allowsProofRequest($cached, self::NONCE, Guard::ROOT, Guard::ROOT.'/public'));
    }

    public function test_nonce_path_and_query_are_exact_without_accepting_another_release_endpoint(): void
    {
        foreach (['', str_repeat('a', 31), str_repeat('a', 33), str_repeat('A', 32),
            str_repeat('g', 32), '../'.self::NONCE, str_repeat('b', 32)] as $nonce) {
            self::assertFalse(Guard::allowsProofRequest(self::request(), $nonce, Guard::ROOT, Guard::ROOT.'/public'));
        }
        foreach ([
            '/api/_local/m46-target-'.self::NONCE,
            '/api/_local/m47-target-'.self::NONCE,
            '/api/_local/m48-target-'.self::NONCE,
            '/api/_local/m49-target-'.self::NONCE,
            self::ENDPOINT.str_repeat('b', 32),
            self::ENDPOINT.self::NONCE.'/extra',
            self::ENDPOINT.self::NONCE.'?extra=1',
        ] as $path) {
            self::assertFalse(Guard::allowsProofRequest(self::request(path: $path),
                self::NONCE, Guard::ROOT, Guard::ROOT.'/public'), $path);
        }
    }

    public function test_application_and_document_root_are_real_and_not_swapped(): void
    {
        foreach ([
            [Guard::ROOT.'/public', Guard::ROOT.'/public'],
            [Guard::ROOT, Guard::ROOT],
            [Guard::ROOT.'/app', Guard::ROOT.'/public'],
            [Guard::ROOT, Guard::ROOT.'/storage'],
            [Guard::ROOT.'/__missing_completion_fixture__', Guard::ROOT.'/public'],
            [Guard::ROOT, Guard::ROOT.'/__missing_completion_public__'],
            ['D:/__missing_completion_application__', 'D:/__missing_completion_document__'],
        ] as [$application, $document]) {
            self::assertFalse(Guard::allowsProofRequest(self::request(), self::NONCE, $application, $document));
        }
    }

    public function test_exact_database_config_never_opens_pdo_or_runs_a_query(): void
    {
        foreach (['localhost', '127.0.0.1', '::1'] as $host) {
            Guard::assertConfiguredDatabase($this->connection(['host' => $host]));
        }
        Guard::assertConfiguredDatabase($this->connection(['port' => '3306']));
    }

    public static function forbiddenDatabaseConfigurations(): array
    {
        return [
            'SQLite is not working MySQL' => [['driver' => 'sqlite']],
            'another driver' => [['driver' => 'pgsql']],
            'LAN server' => [['host' => '192.168.0.104']],
            'production host' => [['host' => 'gramlyze.com']],
            'array routing' => [['host' => ['127.0.0.1']]],
            'missing host' => [['host' => null]],
            'another database' => [['database' => 'gramlyze']],
            'database case changed' => [['database' => 'GR2']],
            'missing database' => [['database' => null]],
            'another port' => [['port' => 3307]],
            'port suffix' => [['port' => '3306junk']],
            'fractional port' => [['port' => 3306.5]],
            'missing port' => [['port' => null]],
            'URL override' => [['url' => 'mysql://127.0.0.1/gr2']],
            'socket override' => [['unix_socket' => '/tmp/completion-fixture.sock']],
            'read routing' => [['read' => ['host' => 'localhost']]],
            'write routing' => [['write' => ['host' => 'localhost']]],
        ];
    }

    #[DataProvider('forbiddenDatabaseConfigurations')]
    public function test_foreign_database_config_is_rejected_before_any_query(array $changes): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('single loopback MySQL gr2:3306');
        Guard::assertConfiguredDatabase($this->connection($changes));
    }

    public function test_verify_refuses_bad_database_or_target_before_private_files_or_windows_probe(): void
    {
        $guard = new Guard;
        foreach ([
            ['config' => ['driver' => 'sqlite'], 'target' => 'gramlyze.loc', 'physical' => ['db' => 'gr2']],
            ['config' => [], 'target' => 'gramlyze.com', 'physical' => ['db' => 'gr2']],
            ['config' => [], 'target' => 'gramlyze.ub', 'physical' => ['db' => 'gr2']],
            ['config' => [], 'target' => 'gramlyze.loc', 'physical' => ['db' => 'other']],
            ['config' => [], 'target' => 'gramlyze.loc', 'physical' => []],
        ] as $case) {
            $this->rejects(fn () => $guard->verify($this->connection($case['config']),
                $case['target'], '', null, $case['physical']));
        }
    }

    public function test_proof_basename_rejects_external_paths_before_reading_any_file(): void
    {
        foreach ([null, '', '../local-proof-'.self::NONCE.'.json',
            'local-proof-'.str_repeat('A', 32).'.json',
            'local-proof-'.self::NONCE.'.json.bak', Guard::ROOT.'/local-proof-'.self::NONCE.'.json'] as $name) {
            $this->rejects(fn () => Guard::readFreshProof($name), 'private nonce proof basename');
        }
    }

    public function test_runtime_digest_requires_exact_nonce_target_and_shape(): void
    {
        $guard = new Guard;
        $expected = ['target' => 'gramlyze.loc', 'nonce' => self::NONCE, 'sha256' => str_repeat('a', 64)];
        $guard->assertRuntimeProof($expected, $expected);
        foreach ([
            array_replace($expected, ['target' => 'gramlyze.ub']),
            array_replace($expected, ['nonce' => str_repeat('b', 32)]),
            array_replace($expected, ['sha256' => str_repeat('b', 64)]),
            array_diff_key($expected, ['nonce' => true]),
            $expected + ['extra' => 'not part of runtime proof'],
        ] as $actual) {
            $this->rejects(fn () => $guard->assertRuntimeProof($expected, $actual), 'Practice completion live vhost');
        }
    }

    public function test_physical_evidence_matches_one_vhost_and_mysql_process_without_probing_windows(): void
    {
        $root = strtolower(str_replace('\\', '/', Guard::ROOT));
        $evidence = ['addresses' => ['127.0.0.1', '::1'], 'document_root' => $root.'/public',
            'active_vhosts' => 1, 'apache_name' => 'httpd.exe', 'apache_pid' => 11, 'apache_pid_file' => 11,
            'mysql_listeners' => [['port' => 3306, 'pid' => 22, 'name' => 'mysqld.exe']]];
        $guard = new Guard;
        $guard->assertEvidence($evidence, $root, 22, 3306);
        foreach ([
            ['addresses' => []],
            ['addresses' => ['127.0.0.1', '192.168.0.104']],
            ['document_root' => $root.'/storage'],
            ['active_vhosts' => 2],
            ['apache_pid_file' => 12],
            ['apache_name' => 'proxy.exe'],
            ['mysql_listeners' => []],
            ['mysql_listeners' => [['port' => 3307, 'pid' => 22, 'name' => 'mysqld.exe']]],
            ['mysql_listeners' => [['port' => 3306, 'pid' => 23, 'name' => 'mysqld.exe']]],
            ['mysql_listeners' => [['port' => 3306, 'pid' => 22, 'name' => 'proxy.exe']]],
        ] as $change) {
            $this->rejects(fn () => $guard->assertEvidence(array_replace($evidence, $change), $root, 22, 3306));
        }
    }

    public function test_fresh_proof_has_thirty_minute_lifetime_and_exact_twelve_owner_scope(): void
    {
        $now = strtotime('2026-10-12T12:00:00Z');
        $name = 'local-proof-'.self::NONCE.'.json';
        $proof = self::proof($now);
        foreach ([0, -1800, 60] as $offset) {
            Guard::assertFreshProof(array_replace($proof, ['created_at' => gmdate('c', $now + $offset)]), $name, $now);
            $this->addToAssertionCount(1);
        }
        $wrongLocale = Guard::scope(); $wrongLocale[0]['locale'] = 'en';
        $wrongOwner = Guard::scope(); $wrongOwner[0]['identity'] = 'ForeignOwner';
        $wrongCategory = Guard::scope(); $wrongCategory[10]['path'] = '/theory/conditionals/can-could';
        $wrongNeighbour = Guard::scope(); $wrongNeighbour[0]['path'] = '/theory/conditionals/unless';
        $missingPhysical = $proof['physical_hashes']; unset($missingPhysical[Guard::ROOT.'/public/index.php']);
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
            ['scope' => $wrongCategory],
            ['scope' => $wrongNeighbour],
            ['scope' => array_reverse(Guard::scope())],
            ['scope' => array_slice(Guard::scope(), 0, 11)],
            ['scope' => [...Guard::scope(), Guard::scope()[0]]],
            ['physical_hashes' => $missingPhysical],
            ['physical_hashes' => array_reverse($proof['physical_hashes'], true)],
            ['physical_hashes' => $proof['physical_hashes'] + [Guard::ROOT.'/.env' => str_repeat('e', 64)]],
        ] as $change) {
            $this->rejects(fn () => Guard::assertFreshProof(array_replace($proof, $change), $name, $now));
        }
        foreach (['../'.$name, 'local-proof-'.str_repeat('b', 32).'.json', $name.'.bak'] as $badName) {
            $this->rejects(fn () => Guard::assertFreshProof($proof, $badName, $now));
        }
    }

    public function test_every_physical_hash_is_an_explicit_lowercase_sha256_string(): void
    {
        $now = strtotime('2026-10-12T12:00:00Z');
        $proof = self::proof($now);
        foreach (array_keys($proof['physical_hashes']) as $path) {
            foreach (['', str_repeat('a', 63), str_repeat('a', 65), str_repeat('A', 64),
                str_repeat('g', 64), 123, null, []] as $value) {
                $changed = $proof; $changed['physical_hashes'][$path] = $value;
                $this->rejects(fn () => Guard::assertFreshProof($changed, 'local-proof-'.self::NONCE.'.json', $now));
            }
        }
    }

    private static function proof(int $now): array
    {
        return ['target' => 'gramlyze.loc', 'nonce' => self::NONCE, 'scope' => Guard::scope(),
            'created_at' => gmdate('c', $now), 'physical_hashes' => [
                'C:/Program Files/xampp/apache/conf/httpd.conf' => str_repeat('a', 64),
                'C:/Program Files/xampp/apache/conf/extra/httpd-vhosts.conf' => str_repeat('b', 64),
                'C:/Program Files/xampp/apache/conf/extra/gramlyze-fastcgi.conf' => str_repeat('c', 64),
                Guard::ROOT.'/public/index.php' => str_repeat('d', 64),
            ]];
    }

    public function test_body_capacity_counts_utf8_bytes_without_database_or_environment(): void
    {
        foreach ([str_repeat('a', 65535), str_repeat('ї', 32767).'x'] as $body) {
            \App\Services\PracticeCompletionPatch::assertBodyCapacity($body, 65535);
            self::assertSame(65535, strlen($body));
        }
        foreach ([str_repeat('a', 65536), str_repeat('ї', 32768)] as $body) {
            $this->rejects(fn () => \App\Services\PracticeCompletionPatch::assertBodyCapacity($body, 65535), 'column byte capacity');
        }
        $this->rejects(fn () => \App\Services\PracticeCompletionPatch::assertBodyCapacity('', 0), 'column byte capacity');
    }

    public function test_plan_and_save_plan_guard_before_pdo_capacity_queries_or_source_reads(): void
    {
        $missingRoot = __DIR__.'/__missing_practice_completion_guard_order__';
        self::assertDirectoryDoesNotExist($missingRoot);
        $proof = 'local-proof-'.self::NONCE.'.json';
        $cases = [
            [['host' => 'gramlyze.com'], 'gramlyze.loc', $proof, 'single loopback MySQL gr2:3306'],
            [['database' => 'other'], 'gramlyze.loc', $proof, 'single loopback MySQL gr2:3306'],
            [['read' => ['host' => 'localhost']], 'gramlyze.loc', $proof, 'single loopback MySQL gr2:3306'],
            [['write' => ['host' => 'localhost']], 'gramlyze.loc', $proof, 'single loopback MySQL gr2:3306'],
            [['url' => 'mysql://127.0.0.1/gr2'], 'gramlyze.loc', $proof, 'single loopback MySQL gr2:3306'],
            [[], null, $proof, 'explicit local target and fresh proof'],
            [[], 'gramlyze.com', $proof, 'explicit local target and fresh proof'],
            [[], 'gramlyze.ub', $proof, 'explicit local target and fresh proof'],
            [[], 'gramlyze.loc', null, 'explicit local target and fresh proof'],
        ];
        foreach (['plan', 'savePlan'] as $method) {
            foreach ($cases as [$config, $target, $localProof, $message]) {
                $patch = new \App\Services\PracticeCompletionPatch($this->connection($config),
                    $missingRoot.'/database', $missingRoot.'/private', $target, $localProof);
                $operation = $method === 'plan'
                    ? fn () => $patch->plan()
                    : fn () => $patch->savePlan($missingRoot.'/private/plan.json');
                // A missing source would throw a different error if package/file IO ran first.
                // The connection mock also forbids every PDO/query/schema entry point.
                $this->rejects($operation, $message);
            }
        }
        self::assertDirectoryDoesNotExist($missingRoot);
    }

    private function rejects(callable $operation, ?string $message = null): void
    {
        $caught = null;
        try { $operation(); } catch (RuntimeException $error) { $caught = $error; }
        self::assertInstanceOf(RuntimeException::class, $caught);
        if ($message !== null) { self::assertStringContainsString($message, $caught->getMessage()); }
    }

    private static function request(string $method = 'GET', string $host = 'gramlyze.loc',
        string $peer = '127.0.0.1', string $scheme = 'http', array $headers = [], ?string $path = null): Request
    {
        return Request::create($scheme.'://'.$host.($path ?? self::ENDPOINT.self::NONCE),
            $method, server: array_replace(['REMOTE_ADDR' => $peer, 'HTTP_HOST' => $host], $headers));
    }

    private function connection(array $changes = []): Connection
    {
        $config = array_replace(['driver' => 'mysql', 'host' => 'localhost', 'database' => 'gr2', 'port' => 3306], $changes);
        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()
            ->onlyMethods(['getDriverName', 'getConfig', 'getDatabaseName', 'getSchemaBuilder', 'getPdo', 'getReadPdo', 'select', 'selectOne',
                'statement', 'affectingStatement', 'beginTransaction'])->getMock();
        $connection->method('getDriverName')->willReturn($config['driver']);
        $connection->method('getConfig')->willReturnCallback(
            static fn (?string $key = null) => $key === null ? $config : ($config[$key] ?? null));
        foreach (['getDatabaseName', 'getSchemaBuilder', 'getPdo', 'getReadPdo', 'select', 'selectOne', 'statement', 'affectingStatement', 'beginTransaction'] as $method) {
            $connection->expects(self::never())->method($method);
        }

        return $connection;
    }
}
