<?php

namespace Tests\Unit;

use App\Support\M27LinkingWordsPackage;
use App\Support\M39PracticeUiPackage;
use App\Support\M42NativeDesignPackage as Design;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Only byte-for-byte fixture copies are changed; no working source, SQL writes or HTTP. */
final class M42RequestArtifactMemoTest extends TestCase
{
    public static function artifacts(): array
    {
        return [
            'native map' => ['load', Design::SOURCE, 'M42 finite native presentation bytes differ'],
            'accepted target sources' => ['sourceTargets', M27LinkingWordsPackage::SOURCE, 'M27 immutable source differs'],
            'native map transitively checks accepted sources' => ['load', M27LinkingWordsPackage::SOURCE, 'M27 immutable source differs'],
        ];
    }

    #[DataProvider('artifacts')]
    public function test_routed_request_reuses_only_its_own_verified_artifact_while_fresh_checks_fail_closed(
        string $reader,
        string $tamperedArtifact,
        string $guardMessage
    ): void {
        $app = $this->app;
        $originalRoot = $app->basePath();
        $originalRequest = $app->bound('request') ? $app->make('request') : null;
        $fixture = $this->copyFrozenSources($originalRoot);
        try {
            $app->setBasePath($fixture);
            $request = $this->routedRequest();
            $app->instance('request', $request);
            $verified = Design::$reader();
            self::assertCount(42, $reader === 'load' ? $verified['targets'] : $verified);
            self::assertSame($verified, Design::$reader($fixture));

            // A different byte sequence, still valid JSON, cannot pass the source SHA guard.
            $path = $fixture.'/'.$tamperedArtifact;
            self::assertSame(1, file_put_contents($path, "\n", FILE_APPEND));
            clearstatcache(true, $path);
            self::assertSame($verified, Design::$reader(), 'One request retains its already verified immutable artifact.');
            $this->assertRejected(static fn () => Design::$reader($fixture), $guardMessage);

            $next = $this->routedRequest();
            self::assertNotSame($request, $next);
            $app->instance('request', $next);
            $this->assertRejected(static fn () => Design::$reader(), $guardMessage);

            // Other callers must not keep a snapshot across repeated reads.
            self::assertTrue(copy($originalRoot.'/'.$tamperedArtifact, $path));
            $app->instance('request', $this->routedRequest('test.show'));
            self::assertSame($verified, Design::$reader());
            self::assertSame(1, file_put_contents($path, "\n", FILE_APPEND));
            clearstatcache(true, $path);
            $this->assertRejected(static fn () => Design::$reader(), $guardMessage);
        } finally {
            $app->setBasePath($originalRoot);
            if ($originalRequest !== null) {
                $app->instance('request', $originalRequest);
            } else {
                $app->forgetInstance('request');
            }
        }
    }

    private function copyFrozenSources(string $originalRoot): string
    {
        $fixture = storage_path('app/m42-artifact-memo-'.bin2hex(random_bytes(8)));
        self::assertTrue(mkdir($fixture));
        IsolatedTestEnvironment::assertOwnedPath($fixture);
        $paths = [Design::REGISTRY, Design::SOURCE, M39PracticeUiPackage::SOURCE];
        foreach ((new ReflectionClass(Design::class))->getConstant('PACKAGES') as $package) {
            foreach (['BEFORE', 'SOURCE', 'MASTER_PATH', 'NOTES_PATH'] as $key) {
                if (!defined($package.'::'.$key)) {
                    continue;
                }
                $path = constant($package.'::'.$key);
                if (in_array($key, ['MASTER_PATH', 'NOTES_PATH'], true) && !is_file($originalRoot.'/'.$path)) {
                    continue;
                }
                $paths[] = $path;
            }
        }
        foreach (array_unique($paths) as $path) {
            self::assertMatchesRegularExpression('~\A(?:database|docs)/[A-Za-z0-9_./-]+\.(?:json|md)\z~', $path);
            self::assertStringNotContainsString('..', $path);
            $source = $originalRoot.'/'.$path;
            $destination = $fixture.'/'.$path;
            if (!is_dir(dirname($destination))) self::assertTrue(mkdir(dirname($destination), 0777, true));
            self::assertTrue(copy($source, $destination));
        }

        return $fixture;
    }

    private function routedRequest(string $name = 'theory.show'): Request
    {
        $request = Request::create('http://gramlyze.loc/theory/fixture/fixture', 'GET');
        $route = (new Route(['GET'], 'theory/{categoryPath}/{pageSlug}', static fn () => null))->name($name);
        $request->setRouteResolver(static fn () => $route);

        return $request;
    }

    private function assertRejected(callable $read, string $message): void
    {
        try {
            $read();
            self::fail('A fresh verification accepted a tampered M42 artifact.');
        } catch (RuntimeException $error) {
            self::assertStringContainsString($message, $error->getMessage());
        }
    }
}
