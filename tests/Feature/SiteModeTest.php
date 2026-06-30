<?php

namespace Tests\Feature;

use App\Support\SiteMode;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SiteModeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'site-mode.production_domains' => ['gramlyze.com', 'www.gramlyze.com', 'gramlyze.ub', '*.prod.example'],
            'site-mode.production_locales' => ['uk'],
            'site-mode.development_features' => ['mode-inspector', 'experimental-ui'],
            'site-mode.response_cache.enabled' => true,
            'site-mode.response_cache.ttl' => 300,
            'site-mode.response_cache.excluded_paths' => [],
            'site-mode.expose_mode_header' => true,
        ]);

        Route::middleware('web')->get('/_site-mode/html', fn () => response('<html>mode</html>'));
        Route::middleware(['web', 'site.dev:experimental-ui'])
            ->get('/_site-mode/experimental', fn () => response('experimental'));
        Route::middleware('web')->get('/en/_site-mode/locale', fn () => response(app()->getLocale()));
        Route::middleware('web')->get('/pl/_site-mode/locale', fn () => response(app()->getLocale()));
    }

    public function test_configured_production_domains_use_production_mode(): void
    {
        $siteMode = app(SiteMode::class);

        $this->assertSame(SiteMode::PRODUCTION, $siteMode->forHost('gramlyze.com'));
        $this->assertSame(SiteMode::PRODUCTION, $siteMode->forHost('WWW.GRAMLYZE.COM'));
        $this->assertSame(SiteMode::PRODUCTION, $siteMode->forHost('gramlyze.ub'));
        $this->assertSame(SiteMode::PRODUCTION, $siteMode->forHost('preview.prod.example'));
        $this->assertSame(SiteMode::DEVELOPMENT, $siteMode->forHost('prod.example'));
        $this->assertSame(SiteMode::DEVELOPMENT, $siteMode->forHost('engapp-codex.loc'));
    }

    public function test_production_mode_enables_browser_caching(): void
    {
        $response = $this->get('http://gramlyze.com/_site-mode/html');

        $response->assertOk();
        $response->assertHeader('X-Site-Mode', SiteMode::PRODUCTION);
        $response->assertHeader('Vary', 'Host');
        $this->assertStringContainsString('max-age=300', (string) $response->headers->get('Cache-Control'));
        $response->assertHeaderMissing('X-Robots-Tag');
    }

    public function test_other_domains_use_uncached_noindex_development_mode(): void
    {
        $response = $this->get('http://engapp-codex.loc/_site-mode/html');

        $response->assertOk();
        $response->assertHeader('X-Site-Mode', SiteMode::DEVELOPMENT);
        $cacheControl = (string) $response->headers->get('Cache-Control');
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('no-cache', $cacheControl);
        $this->assertStringContainsString('max-age=0', $cacheControl);
        $response->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }

    public function test_development_feature_route_is_hidden_in_production(): void
    {
        $this->get('http://gramlyze.com/_site-mode/experimental')
            ->assertNotFound();

        $this->get('http://dev.gramlyze.test/_site-mode/experimental')
            ->assertOk()
            ->assertSeeText('experimental');
    }

    public function test_disabled_development_feature_is_hidden_on_development_domain(): void
    {
        config(['site-mode.development_features' => ['mode-inspector']]);

        $this->get('http://dev.gramlyze.test/_site-mode/experimental')
            ->assertNotFound();
    }

    public function test_mode_inspector_is_available_only_in_development(): void
    {
        $this->get('http://engapp-codex.loc/dev/site-mode')
            ->assertOk()
            ->assertJsonPath('mode', SiteMode::DEVELOPMENT)
            ->assertJsonPath('features.0', 'mode-inspector');

        $this->get('http://gramlyze.com/dev/site-mode')
            ->assertNotFound();
    }

    public function test_production_redirects_a_disabled_locale_to_ukrainian_url(): void
    {
        $this->get('http://gramlyze.com/en/_site-mode/locale?source=test')
            ->assertRedirect('/_site-mode/locale?source=test');
    }

    public function test_language_can_be_enabled_for_production_in_config(): void
    {
        config(['site-mode.production_locales' => ['uk', 'en']]);

        $this->get('http://gramlyze.com/en/_site-mode/locale')
            ->assertOk()
            ->assertSeeText('en');

        $this->assertSame('en', session('locale'));
    }

    public function test_development_domains_keep_all_active_languages(): void
    {
        $this->get('http://engapp-codex.loc/pl/_site-mode/locale')
            ->assertOk()
            ->assertSeeText('pl');

        $this->assertSame('pl', session('locale'));
    }

    public function test_manual_locale_switch_cannot_enable_disabled_production_language(): void
    {
        $this->get('http://gramlyze.com/set-locale?lang=pl')
            ->assertRedirect();

        $this->assertSame('uk', session('locale'));
    }
}
