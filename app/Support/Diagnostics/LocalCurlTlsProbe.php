<?php

namespace App\Support\Diagnostics;

use GuzzleHttp\Handler\CurlHandler;
use Illuminate\Http\Client\Factory as HttpFactory;
use Throwable;

/**
 * Fixed-target diagnostics for the PHP process handling the local web request.
 *
 * This is intentionally not a general HTTP client: public and fixture targets
 * are constants or validated private control data, and no request value can
 * alter an outbound destination or TLS option.
 */
class LocalCurlTlsProbe
{
    private const EXTERNAL_TARGET = 'https://www.php.net/';

    private const EXTERNAL_NAME = 'php-net';

    private const SECONDARY_TARGET = 'https://getcomposer.org/';

    private const SECONDARY_NAME = 'getcomposer';

    public function __construct(private ?HttpFactory $http = null) {}

    /** @return array<string, mixed> */
    public function run(): array
    {
        $runtime = $this->runtime();

        if (! $runtime['curl_loaded'] || ! $runtime['curl_init']) {
            return [
                'ok' => false,
                'runtime' => $runtime,
                'direct_curl' => $this->unavailable(self::EXTERNAL_NAME),
                'direct_curl_secondary' => $this->unavailable(self::SECONDARY_NAME),
                'laravel_http' => $this->unavailable(self::EXTERNAL_NAME),
                'fixture' => $this->fixtureUnavailable('curl-unavailable'),
            ];
        }

        $direct = $this->directGet(self::EXTERNAL_NAME, self::EXTERNAL_TARGET);
        $secondary = $this->directGet(self::SECONDARY_NAME, self::SECONDARY_TARGET);
        $laravel = $this->laravelGet();
        $fixture = $this->fixture();

        return [
            'ok' => $runtime['openssl_loaded'] && $direct['ok'] && $secondary['ok'] && $laravel['ok'] && $fixture['ok'],
            'runtime' => $runtime,
            'direct_curl' => $direct,
            'direct_curl_secondary' => $secondary,
            'laravel_http' => $laravel,
            'fixture' => $fixture,
        ];
    }

    /**
     * Public so the regression test can assert that TLS verification cannot be
     * silently weakened without performing a network request.
     *
     * @param  array<int, string>|null  $resolve
     * @return array<int, mixed>
     */
    public static function directCurlOptions(?string $caPath = null, ?array $resolve = null): array
    {
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_MAXREDIRS => 0,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_PROXY => '',
            CURLOPT_HTTPHEADER => ['Accept: text/plain'],
            CURLOPT_USERAGENT => 'Gramlyze-M9.4-local-curl-tls/1.0',
        ];

        if (defined('CURLOPT_PROTOCOLS_STR')) {
            $options[constant('CURLOPT_PROTOCOLS_STR')] = 'https';
        } elseif (defined('CURLOPT_PROTOCOLS') && defined('CURLPROTO_HTTPS')) {
            $options[constant('CURLOPT_PROTOCOLS')] = constant('CURLPROTO_HTTPS');
        }

        if (defined('CURLOPT_REDIR_PROTOCOLS_STR')) {
            $options[constant('CURLOPT_REDIR_PROTOCOLS_STR')] = 'https';
        } elseif (defined('CURLOPT_REDIR_PROTOCOLS') && defined('CURLPROTO_HTTPS')) {
            $options[constant('CURLOPT_REDIR_PROTOCOLS')] = constant('CURLPROTO_HTTPS');
        }

        if ($caPath !== null) {
            $options[CURLOPT_CAINFO] = $caPath;
        }

        if ($resolve !== null) {
            $options[CURLOPT_RESOLVE] = $resolve;
        }

        return $options;
    }

    /** @return array<string, mixed> */
    public static function laravelOptions(): array
    {
        return [
            'verify' => true,
            'allow_redirects' => false,
            'connect_timeout' => 5,
            'timeout' => 10,
            'http_errors' => false,
            'proxy' => '',
            'cookies' => false,
            'headers' => [
                'Accept' => 'text/plain',
                'User-Agent' => 'Gramlyze-M9.4-local-curl-tls/1.0',
            ],
        ];
    }

    /** @return array<string, mixed> */
    protected function runtime(): array
    {
        $curlLoaded = extension_loaded('curl');
        $opensslLoaded = extension_loaded('openssl');
        $curl = $curlLoaded && function_exists('curl_version') ? curl_version() : [];

        return [
            'php_version' => PHP_VERSION,
            'sapi' => PHP_SAPI,
            'thread_safe' => (bool) PHP_ZTS,
            'architecture' => PHP_INT_SIZE === 8 ? 'x64' : 'x86',
            'curl_loaded' => $curlLoaded,
            'curl_init' => function_exists('curl_init'),
            'curl_version' => is_array($curl) ? (string) ($curl['version'] ?? '') : '',
            'curl_tls_backend' => is_array($curl) ? (string) ($curl['ssl_version'] ?? '') : '',
            'openssl_loaded' => $opensslLoaded,
            'openssl_version' => $opensslLoaded && defined('OPENSSL_VERSION_TEXT') ? OPENSSL_VERSION_TEXT : '',
            'web_config_checks' => $this->webConfigChecks(),
        ];
    }

    /** Fixed local expectations only; no paths, environment values or secrets leave PHP. */
    private function webConfigChecks(): array
    {
        $runtime = 'C:/Program Files/xampp/php-8.5.10-nts-gramlyze';
        $samePath = static function (string $actual, string $expected): bool {
            $left = realpath($actual);
            $right = realpath($expected);

            return $left !== false && $right !== false
                && strcasecmp(str_replace('\\', '/', $left), str_replace('\\', '/', $right)) === 0;
        };
        $extensions = ['bz2', 'curl', 'fileinfo', 'gd', 'gettext', 'intl', 'mbstring',
            'mysqli', 'openssl', 'pdo_mysql', 'pdo_sqlite', 'sqlite3', 'zip'];
        $writable = [storage_path('framework/sessions'), storage_path('framework/views'),
            storage_path('framework/cache'), storage_path('logs'), base_path('bootstrap/cache')];

        return [
            'own_binary' => $samePath(PHP_BINARY, $runtime.'/php-cgi.exe'),
            'own_ini' => $samePath((string) php_ini_loaded_file(), $runtime.'/php.ini'),
            'no_extra_ini' => trim((string) php_ini_scanned_files()) === '',
            'own_extension_dir' => $samePath((string) ini_get('extension_dir'), $runtime.'/ext'),
            'curl_ca_file' => $samePath((string) ini_get('curl.cainfo'), $runtime.'/extras/ssl/cacert.pem'),
            'openssl_ca_file' => $samePath((string) ini_get('openssl.cafile'), $runtime.'/extras/ssl/cacert.pem'),
            'front_controller' => $samePath((string) ($_SERVER['SCRIPT_FILENAME'] ?? ''), public_path('index.php')),
            'document_root' => $samePath((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), public_path()),
            'cgi_fix_pathinfo_disabled' => ini_get('cgi.fix_pathinfo') === '0',
            'required_extensions' => array_all($extensions, static fn (string $extension): bool => extension_loaded($extension)),
            'runtime_directories_writable' => array_all($writable, static fn (string $path): bool => is_writable($path)),
            'session_path_preserved' => $samePath((string) ini_get('session.save_path'), 'C:/Program Files/xampp/tmp'),
            'session_serialization_preserved' => ini_get('session.serialize_handler') === 'php',
            'php_children_disabled' => getenv('PHP_FCGI_CHILDREN') === '0',
            'php_max_requests' => getenv('PHP_FCGI_MAX_REQUESTS') === '1000',
        ];
    }

    /** @return array<string, mixed> */
    protected function directGet(string $name, string $url, ?string $caPath = null, ?array $resolve = null): array
    {
        $handle = curl_init($url);

        if ($handle === false) {
            return $this->failure($name, 0, 0, 'curl-init-failed');
        }

        $started = hrtime(true);

        try {
            if (! curl_setopt_array($handle, self::directCurlOptions($caPath, $resolve))) {
                return $this->failure($name, 0, 0, 'curl-options-rejected');
            }

            $body = curl_exec($handle);
            $errno = curl_errno($handle);
            $status = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
            $verifyResult = defined('CURLINFO_SSL_VERIFYRESULT')
                ? (int) curl_getinfo($handle, constant('CURLINFO_SSL_VERIFYRESULT'))
                : null;
            $duration = round((hrtime(true) - $started) / 1_000_000, 3);

            if ($body !== false && $errno === 0 && $status >= 200 && $status < 400 && ($verifyResult === null || $verifyResult === 0)) {
                return [
                    'name' => $name,
                    'ok' => true,
                    'http_status' => $status,
                    'errno' => 0,
                    'duration_ms' => $duration,
                    'ssl_verify_result' => $verifyResult,
                    'failure' => null,
                ];
            }

            return $this->failure($name, $status, $errno, $this->failureType($errno), $duration, $verifyResult);
        } catch (Throwable) {
            return $this->failure($name, 0, 0, 'curl-exception', round((hrtime(true) - $started) / 1_000_000, 3));
        } finally {
            curl_close($handle);
        }
    }

    /** @return array<string, mixed> */
    protected function laravelGet(): array
    {
        $started = hrtime(true);

        try {
            $response = ($this->http ?? app(HttpFactory::class))
                ->withOptions(self::laravelOptions())
                ->setHandler(new CurlHandler)
                ->get(self::EXTERNAL_TARGET);
            $status = $response->status();

            return [
                'name' => self::EXTERNAL_NAME,
                'ok' => $status >= 200 && $status < 400,
                'http_status' => $status,
                'duration_ms' => round((hrtime(true) - $started) / 1_000_000, 3),
                'handler' => CurlHandler::class,
                'verification' => true,
                'failure' => $status >= 200 && $status < 400 ? null : 'http-status-failed',
            ];
        } catch (Throwable) {
            return [
                'name' => self::EXTERNAL_NAME,
                'ok' => false,
                'http_status' => 0,
                'duration_ms' => round((hrtime(true) - $started) / 1_000_000, 3),
                'handler' => CurlHandler::class,
                'verification' => true,
                'failure' => 'laravel-transport-failed',
            ];
        }
    }

    /** @return array<string, mixed> */
    protected function fixture(): array
    {
        $fixture = LocalCurlTlsControl::fixture();

        if ($fixture === null) {
            return $this->fixtureUnavailable('fixture-not-configured');
        }

        $positive = $this->directGet(
            'local-fixture-positive',
            $fixture['positive_url'],
            $fixture['ca_path'],
            [sprintf('localhost:%d:127.0.0.1', $fixture['port'])]
        );
        $negative = $this->directGet('local-fixture-negative', $fixture['negative_url'], $fixture['ca_path']);
        $negativeVerified = ! $negative['ok']
            && $negative['failure'] === 'tls-verification-failed'
            && $negative['errno'] === 60;

        return [
            'configured' => true,
            'ok' => $positive['ok'] && $positive['http_status'] === 204 && $negativeVerified,
            'positive' => $positive,
            'negative' => $negative,
            'negative_tls_verified' => $negativeVerified,
        ];
    }

    /** @return array<string, mixed> */
    protected function unavailable(string $name): array
    {
        return $this->failure($name, 0, 0, 'curl-unavailable');
    }

    /** @return array<string, mixed> */
    protected function fixtureUnavailable(string $failure): array
    {
        return [
            'configured' => false,
            'ok' => false,
            'positive' => null,
            'negative' => null,
            'negative_tls_verified' => false,
            'failure' => $failure,
        ];
    }

    /** @return array<string, mixed> */
    private function failure(string $name, int $status, int $errno, string $failure, float $duration = 0, ?int $verifyResult = null): array
    {
        return [
            'name' => $name,
            'ok' => false,
            'http_status' => $status,
            'errno' => $errno,
            'duration_ms' => $duration,
            'ssl_verify_result' => $verifyResult,
            'failure' => $failure,
        ];
    }

    private function failureType(int $errno): string
    {
        return $errno === 60 ? 'tls-verification-failed' : 'curl-transport-failed';
    }
}
