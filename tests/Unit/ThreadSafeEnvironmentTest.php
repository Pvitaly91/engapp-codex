<?php

namespace Tests\Unit;

use App\Support\ThreadSafeEnvironment;
use Dotenv\Loader\Loader;
use Dotenv\Parser\Parser;
use Illuminate\Config\Repository;
use Illuminate\Encryption\EncryptionServiceProvider;
use Illuminate\Encryption\MissingAppKeyException;
use Illuminate\Foundation\Application;
use Illuminate\Support\Env;
use PHPUnit\Framework\TestCase;

class ThreadSafeEnvironmentTest extends TestCase
{
    private array $previous;

    protected function setUp(): void
    {
        $this->previous = [getenv('APP_KEY'), $_ENV['APP_KEY'] ?? null, $_SERVER['APP_KEY'] ?? null];
        unset($_ENV['APP_KEY'], $_SERVER['APP_KEY']);
        putenv('APP_KEY');
        Env::enablePutenv();
    }

    protected function tearDown(): void
    {
        [$process, $env, $server] = $this->previous;
        putenv($process === false ? 'APP_KEY' : 'APP_KEY='.$process);
        unset($_ENV['APP_KEY'], $_SERVER['APP_KEY']);
        if ($env !== null) $_ENV['APP_KEY'] = $env;
        if ($server !== null) $_SERVER['APP_KEY'] = $server;
        Env::enablePutenv();
    }

    private function loadIsolatedEnvironment(string $key): void
    {
        // Entirely in-memory fixture: never reads or changes the working .env.
        (new Loader)->load(Env::getRepository(), (new Parser)->parse('APP_KEY='.$key));
    }

    private function resolveEncrypter(): object
    {
        $app = new Application;
        $app->instance('config', new Repository(['app' => ['key' => Env::get('APP_KEY'), 'cipher' => 'AES-256-CBC']]));
        (new EncryptionServiceProvider($app))->register();

        return $app->make('encrypter');
    }

    public function test_unsafe_adapter_reproduces_missing_key_when_another_request_restores_process_environment(): void
    {
        $key = 'base64:'.base64_encode(random_bytes(32));
        putenv('APP_KEY='.$key); // An overlapping request loaded the same .env.
        $this->loadIsolatedEnvironment($key);
        $this->assertArrayNotHasKey('APP_KEY', $_ENV);
        $this->assertArrayNotHasKey('APP_KEY', $_SERVER);
        putenv('APP_KEY'); // That request ends before this request loads config.

        $this->expectException(MissingAppKeyException::class);
        $this->resolveEncrypter();
    }

    public function test_threaded_apache_keeps_request_local_key_after_process_value_disappears(): void
    {
        $key = 'base64:'.base64_encode(random_bytes(32));
        putenv('APP_KEY='.$key);
        ThreadSafeEnvironment::configure(true, 'apache2handler');
        $this->loadIsolatedEnvironment($key);
        putenv('APP_KEY');
        $this->assertTrue(hash_equals($key, Env::get('APP_KEY')));
        $encrypter = $this->resolveEncrypter();
        $this->assertSame('round-trip', $encrypter->decryptString($encrypter->encryptString('round-trip')));
    }

    public function test_apache_server_override_is_preserved(): void
    {
        $override = 'base64:'.base64_encode(random_bytes(32));
        $_SERVER['APP_KEY'] = $override;
        ThreadSafeEnvironment::configure(true, 'apache2handler');
        $this->loadIsolatedEnvironment('base64:'.base64_encode(random_bytes(32)));
        $this->assertTrue(hash_equals($override, Env::get('APP_KEY')));
    }

    public function test_cli_and_non_threaded_web_keep_the_existing_environment_contract(): void
    {
        foreach ([[true, 'cli'], [false, 'fpm-fcgi']] as [$threaded, $sapi]) {
            Env::enablePutenv();
            ThreadSafeEnvironment::configure($threaded, $sapi);
            $value = 'base64:'.base64_encode(random_bytes(32));
            putenv('APP_KEY='.$value);
            $this->assertTrue(hash_equals($value, Env::get('APP_KEY')));
        }
    }
}
