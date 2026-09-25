<?php

namespace Tests\Feature\Diagnostics;

use App\Http\Controllers\LocalCurlTlsProbeController;
use App\Support\Diagnostics\LocalCurlTlsControl;
use App\Support\Diagnostics\LocalCurlTlsProbe;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LocalCurlTlsProbeTest extends TestCase
{
    private const NONCE = '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef';

    private const PATH = '/_tests/m9-4-curl-tls';

    protected function setUp(): void
    {
        parent::setUp();

        $this->writeControl();
        $this->app->instance(LocalCurlTlsProbe::class, new class extends LocalCurlTlsProbe
        {
            public function run(): array
            {
                return [
                    'ok' => true,
                    'runtime' => [
                        'php_version' => '8.5.10',
                        'sapi' => 'cgi-fcgi',
                        'thread_safe' => false,
                        'architecture' => 'x64',
                        'curl_loaded' => true,
                        'curl_init' => true,
                        'curl_version' => '8.0.0',
                        'curl_tls_backend' => 'OpenSSL/3.5.7',
                        'openssl_loaded' => true,
                        'openssl_version' => 'OpenSSL 3.5.7',
                    ],
                    'direct_curl' => ['name' => 'php-net', 'ok' => true, 'http_status' => 200, 'errno' => 0, 'duration_ms' => 1.2, 'failure' => null],
                    'direct_curl_secondary' => ['name' => 'getcomposer', 'ok' => true, 'http_status' => 200, 'errno' => 0, 'duration_ms' => 1.2, 'failure' => null],
                    'laravel_http' => ['name' => 'php-net', 'ok' => true, 'http_status' => 200, 'duration_ms' => 1.1, 'handler' => 'GuzzleHttp\\Handler\\CurlHandler', 'verification' => true, 'failure' => null],
                    'fixture' => ['configured' => true, 'ok' => true, 'positive' => ['name' => 'local-fixture-positive', 'ok' => true, 'http_status' => 204, 'errno' => 0, 'duration_ms' => 1, 'failure' => null], 'negative' => ['name' => 'local-fixture-negative', 'ok' => false, 'http_status' => 0, 'errno' => 60, 'duration_ms' => 1, 'failure' => 'tls-verification-failed'], 'negative_tls_verified' => true],
                ];
            }
        });

        // Test registration keeps the production endpoint disposable: after M9.4
        // acceptance the real API route can be removed while this policy remains tested.
        Route::middleware(['api', 'diagnostic.loopback', 'site.dev'])
            ->get(self::PATH, LocalCurlTlsProbeController::class);
    }

    protected function tearDown(): void
    {
        $fixture = LocalCurlTlsControl::directory().DIRECTORY_SEPARATOR.'fixture';
        @unlink($fixture.DIRECTORY_SEPARATOR.'ca.pem');
        @rmdir($fixture);
        @unlink(LocalCurlTlsControl::path());
        @rmdir(LocalCurlTlsControl::directory());

        parent::tearDown();
    }

    public function test_exact_loopback_request_with_private_nonce_returns_only_safe_evidence(): void
    {
        $response = $this->probe();

        $response->assertOk()
            ->assertHeaderContains('Cache-Control', 'no-store')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertJsonPath('ok', true)
            ->assertJsonPath('runtime.sapi', 'cgi-fcgi')
            ->assertJsonPath('direct_curl_secondary.name', 'getcomposer')
            ->assertJsonPath('fixture.negative.errno', 60)
            ->assertJsonMissingPath('app_key')
            ->assertJsonMissingPath('php_ini')
            ->assertJsonMissingPath('php_binary')
            ->assertJsonMissingPath('fixture.ca_path');

        $this->assertStringNotContainsString(self::NONCE, $response->getContent());
        $this->assertStringNotContainsString(storage_path(), $response->getContent());
    }

    public function test_raw_remote_address_cannot_be_spoofed_by_forwarded_headers(): void
    {
        $this->probe([
            'REMOTE_ADDR' => '203.0.113.10',
            'HTTP_X_FORWARDED_FOR' => '127.0.0.1',
            'HTTP_FORWARDED' => 'for=127.0.0.1',
        ])->assertNotFound();
    }

    public function test_missing_or_invalid_nonce_and_query_are_hidden_as_not_found(): void
    {
        $this->probe([], null)->assertNotFound();
        $this->probe([], str_repeat('f', 64))->assertNotFound();
        $this->probe([], self::NONCE, self::PATH.'?url=https%3A%2F%2Fexample.invalid')->assertNotFound();
    }

    public function test_development_only_gate_hides_probe_for_a_production_host(): void
    {
        $this->probe(['REMOTE_ADDR' => '127.0.0.1', 'HTTP_HOST' => 'gramlyze.com'], self::NONCE, 'http://gramlyze.com'.self::PATH)
            ->assertNotFound();
    }

    public function test_fixture_control_accepts_only_fixed_private_loopback_targets(): void
    {
        $directory = LocalCurlTlsControl::directory().DIRECTORY_SEPARATOR.'fixture';
        mkdir($directory, 0700, true);
        $ca = $directory.DIRECTORY_SEPARATOR.'ca.pem';
        file_put_contents($ca, 'test certificate');
        $this->writeControl([
            'fixture' => [
                'positive_url' => 'https://localhost:9443/ok',
                'negative_url' => 'https://127.0.0.1:9443/ok',
                'ca_path' => $ca,
            ],
        ]);

        $fixture = LocalCurlTlsControl::fixture();
        $this->assertSame(9443, $fixture['port']);
        $this->assertSame(realpath($ca), $fixture['ca_path']);

        $this->writeControl([
            'fixture' => [
                'positive_url' => 'https://example.com:9443/ok',
                'negative_url' => 'https://127.0.0.1:9443/ok',
                'ca_path' => $ca,
            ],
        ]);
        $this->assertNull(LocalCurlTlsControl::fixture());
    }

    public function test_tls_options_are_explicit_and_never_use_a_stream_fallback(): void
    {
        if (! extension_loaded('curl')) {
            $this->markTestSkipped('CLI cURL extension is required to inspect cURL constants.');
        }

        $direct = LocalCurlTlsProbe::directCurlOptions();
        $fixture = LocalCurlTlsProbe::directCurlOptions('/private/fixture/ca.pem', ['localhost:9443:127.0.0.1']);
        $laravel = LocalCurlTlsProbe::laravelOptions();

        $this->assertTrue($direct[CURLOPT_SSL_VERIFYPEER]);
        $this->assertSame(2, $direct[CURLOPT_SSL_VERIFYHOST]);
        $this->assertFalse($direct[CURLOPT_FOLLOWLOCATION]);
        $this->assertSame('', $direct[CURLOPT_PROXY]);
        $this->assertArrayNotHasKey(CURLOPT_CAINFO, $direct);
        $this->assertSame('/private/fixture/ca.pem', $fixture[CURLOPT_CAINFO]);
        $this->assertSame(['localhost:9443:127.0.0.1'], $fixture[CURLOPT_RESOLVE]);
        $this->assertTrue($laravel['verify']);
        $this->assertFalse($laravel['allow_redirects']);
        $this->assertSame('', $laravel['proxy']);
        $this->assertFalse($laravel['cookies']);
    }

    public function test_both_fixed_direct_https_targets_are_required_for_success(): void
    {
        $probe = new class extends LocalCurlTlsProbe
        {
            public array $targets = [];

            public bool $secondaryOk = true;

            protected function runtime(): array
            {
                return ['curl_loaded' => true, 'curl_init' => true, 'openssl_loaded' => true];
            }

            protected function directGet(string $name, string $url, ?string $caPath = null, ?array $resolve = null): array
            {
                $this->targets[$name] = $url;

                return ['ok' => $name !== 'getcomposer' || $this->secondaryOk];
            }

            protected function laravelGet(): array
            {
                return ['ok' => true];
            }

            protected function fixture(): array
            {
                return ['ok' => true];
            }
        };

        $this->assertTrue($probe->run()['ok']);
        $this->assertSame(['php-net' => 'https://www.php.net/', 'getcomposer' => 'https://getcomposer.org/'], $probe->targets);
        $probe->secondaryOk = false;
        $this->assertFalse($probe->run()['ok']);
    }

    public function test_real_runtime_diagnostics_return_typed_safe_checks_without_network(): void
    {
        $probe = new class extends LocalCurlTlsProbe
        {
            public function inspectRuntime(): array
            {
                return parent::runtime();
            }
        };

        // Exercise the real collection, including array_all callbacks. Stubbing
        // run() would miss a callback arity exception that makes the WEB probe 503.
        $runtime = $probe->inspectRuntime();

        $this->assertSame([
            'php_version', 'sapi', 'thread_safe', 'architecture', 'curl_loaded',
            'curl_init', 'curl_version', 'curl_tls_backend', 'openssl_loaded',
            'openssl_version', 'web_config_checks',
        ], array_keys($runtime));
        $this->assertSame(PHP_VERSION, $runtime['php_version']);
        $this->assertSame(PHP_SAPI, $runtime['sapi']);
        $this->assertSame((bool) PHP_ZTS, $runtime['thread_safe']);
        $this->assertSame(PHP_INT_SIZE === 8 ? 'x64' : 'x86', $runtime['architecture']);
        $this->assertSame(extension_loaded('curl'), $runtime['curl_loaded']);
        $this->assertSame(function_exists('curl_init'), $runtime['curl_init']);
        $this->assertSame(extension_loaded('openssl'), $runtime['openssl_loaded']);
        foreach (['curl_version', 'curl_tls_backend', 'openssl_version'] as $key) {
            $this->assertIsString($runtime[$key]);
        }

        $checks = $runtime['web_config_checks'];
        $this->assertSame([
            'own_binary', 'own_ini', 'no_extra_ini', 'own_extension_dir',
            'curl_ca_file', 'openssl_ca_file', 'front_controller', 'document_root',
            'cgi_fix_pathinfo_disabled', 'required_extensions', 'runtime_directories_writable',
            'session_path_preserved', 'session_serialization_preserved',
            'php_children_disabled', 'php_max_requests',
        ], array_keys($checks));
        foreach ($checks as $name => $passed) {
            // Protected CLI tests do not share the WEB runtime configuration.
            $this->assertIsBool($passed, $name);
        }
    }

    /** @param array<string, string> $server */
    private function probe(array $server = [], ?string $nonce = self::NONCE, string $uri = self::PATH)
    {
        $headers = [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_HOST' => 'gramlyze.loc',
            'HTTP_ACCEPT' => 'application/json',
        ];

        if ($nonce !== null) {
            $headers['HTTP_X_GRAMLYZE_M94_PROBE'] = $nonce;
        }

        return $this->call('GET', $uri, [], [], [], array_merge($headers, $server));
    }

    /** @param array<string, mixed> $override */
    private function writeControl(array $override = []): void
    {
        $directory = LocalCurlTlsControl::directory();
        if (! is_dir($directory)) {
            mkdir($directory, 0700, true);
        }

        $value = array_replace_recursive([
            'schema' => LocalCurlTlsControl::SCHEMA,
            'nonce' => self::NONCE,
        ], $override);
        file_put_contents(LocalCurlTlsControl::path(), json_encode($value, JSON_THROW_ON_ERROR));
    }
}
