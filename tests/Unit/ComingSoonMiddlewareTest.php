<?php

namespace Tests\Unit;

use App\Http\Middleware\ComingSoonMiddleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class ComingSoonMiddlewareTest extends TestCase
{
    protected function tearDown(): void
    {
        // Reset config after each test
        config(['coming-soon.enabled' => false]);
        config(['coming-soon.routes' => []]);
        config(['coming-soon.prefixes' => []]);
        config(['coming-soon.development_bypass_prefixes' => []]);
        config(['coming-soon.production_admin_blocked_prefixes' => []]);
        parent::tearDown();
    }

    public function test_middleware_does_nothing_when_disabled(): void
    {
        config(['coming-soon.enabled' => false]);
        config(['coming-soon.prefixes' => ['/pricing']]);

        $request = Request::create('/pricing', 'GET');
        $middleware = new ComingSoonMiddleware;

        $response = $middleware->handle($request, function ($req) {
            return new Response('OK', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_middleware_returns_503_for_matching_prefix_when_enabled(): void
    {
        config(['coming-soon.enabled' => true]);
        config(['coming-soon.prefixes' => ['/pricing']]);
        config(['coming-soon.retry_after' => 86400]);

        $request = Request::create('/pricing', 'GET');
        $middleware = new ComingSoonMiddleware;

        $response = $middleware->handle($request, function ($req) {
            return new Response('OK', 200);
        });

        $this->assertEquals(503, $response->getStatusCode());
        $this->assertEquals('86400', $response->headers->get('Retry-After'));
    }

    public function test_middleware_allows_non_matching_paths(): void
    {
        config(['coming-soon.enabled' => true]);
        config(['coming-soon.prefixes' => ['/pricing']]);

        $request = Request::create('/home', 'GET');
        $middleware = new ComingSoonMiddleware;

        $response = $middleware->handle($request, function ($req) {
            return new Response('OK', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_middleware_matches_prefix_subpaths(): void
    {
        config(['coming-soon.enabled' => true]);
        config(['coming-soon.prefixes' => ['/features']]);
        config(['coming-soon.retry_after' => 3600]);

        $request = Request::create('/features/advanced', 'GET');
        $middleware = new ComingSoonMiddleware;

        $response = $middleware->handle($request, function ($req) {
            return new Response('OK', 200);
        });

        $this->assertEquals(503, $response->getStatusCode());
        $this->assertEquals('3600', $response->headers->get('Retry-After'));
    }

    public function test_middleware_allows_admin_users(): void
    {
        config(['coming-soon.enabled' => true]);
        config(['coming-soon.prefixes' => ['/catalog/tests-cards', '/test/']]);

        $request = Request::create('/catalog/tests-cards', 'GET');
        $request->setLaravelSession(session());
        session(['admin_authenticated' => true]);

        $middleware = new ComingSoonMiddleware;

        $response = $middleware->handle($request, function ($req) {
            return new Response('OK', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_middleware_blocks_non_admin_users_on_protected_paths(): void
    {
        config(['coming-soon.enabled' => true]);
        config(['coming-soon.prefixes' => ['/catalog/tests-cards', '/test/']]);

        $request = Request::create('/test/some-slug', 'GET');
        $request->setLaravelSession(session());
        session(['admin_authenticated' => false]);

        $middleware = new ComingSoonMiddleware;

        $response = $middleware->handle($request, function ($req) {
            return new Response('OK', 200);
        });

        $this->assertEquals(503, $response->getStatusCode());
    }

    public function test_catalog_bypasses_coming_soon_on_development_host(): void
    {
        config(['coming-soon.enabled' => true]);
        config(['coming-soon.prefixes' => ['/catalog/tests-cards']]);
        config(['coming-soon.development_bypass_prefixes' => ['/catalog/tests-cards']]);

        $request = Request::create('http://engapp-codex.loc/catalog/tests-cards', 'GET');
        $middleware = new ComingSoonMiddleware;

        $response = $middleware->handle($request, fn () => new Response('OK', 200));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getContent());
    }

    public function test_catalog_remains_coming_soon_on_production_host(): void
    {
        config(['coming-soon.enabled' => true]);
        config(['coming-soon.prefixes' => ['/catalog/tests-cards']]);
        config(['coming-soon.development_bypass_prefixes' => ['/catalog/tests-cards']]);
        config(['site-mode.production_domains' => ['gramlyze.com', 'gramlyze.ub']]);

        $request = Request::create('http://gramlyze.ub/catalog/tests-cards', 'GET');
        $middleware = new ComingSoonMiddleware;

        $response = $middleware->handle($request, fn () => new Response('OK', 200));

        $this->assertSame(503, $response->getStatusCode());
    }

    public function test_catalog_is_blocked_for_admin_on_production_host(): void
    {
        config(['coming-soon.enabled' => true]);
        config(['coming-soon.prefixes' => ['/catalog/tests-cards']]);
        config(['coming-soon.development_bypass_prefixes' => ['/catalog/tests-cards']]);
        config(['coming-soon.production_admin_blocked_prefixes' => ['/catalog/tests-cards']]);
        config(['site-mode.production_domains' => ['gramlyze.com', 'gramlyze.ub']]);

        $request = Request::create('http://gramlyze.ub/catalog/tests-cards', 'GET');
        $request->setLaravelSession(session());
        session(['admin_authenticated' => true]);

        $middleware = new ComingSoonMiddleware;
        $response = $middleware->handle($request, fn () => new Response('OK', 200));

        $this->assertSame(503, $response->getStatusCode());
    }
}
