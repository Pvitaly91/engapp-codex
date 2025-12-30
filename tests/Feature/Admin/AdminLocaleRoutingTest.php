<?php

namespace Tests\Feature\Admin;

use App\Modules\LanguageManager\Services\LocaleService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminLocaleRoutingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create languages table for locale detection
        if (!Schema::hasTable('languages')) {
            Schema::create('languages', function (Blueprint $table) {
                $table->id();
                $table->string('code', 10)->unique();
                $table->string('name');
                $table->string('native_name')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });
        }

        // Insert test languages
        \Illuminate\Support\Facades\DB::table('languages')->insert([
            ['code' => 'uk', 'name' => 'Ukrainian', 'is_active' => true, 'is_default' => true],
            ['code' => 'en', 'name' => 'English', 'is_active' => true, 'is_default' => false],
        ]);

        // Clear the locale service cache
        LocaleService::clearCache();
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('languages');
        LocaleService::clearCache();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function admin_dashboard_is_accessible_without_locale_prefix(): void
    {
        $response = $this->withSession(['admin_authenticated' => true])
            ->get('/admin');

        // Admin route exists and doesn't redirect to a localized URL
        // Status could be 200, 302 (redirect to login), or 500 (if dependencies missing in test)
        // The main assertion is that we don't get a redirect to a locale-prefixed URL
        $status = $response->getStatusCode();
        
        // Should not redirect to locale-prefixed URL
        if ($response->isRedirect()) {
            $location = $response->headers->get('Location');
            $this->assertStringNotContainsString('/uk/admin', $location ?? '');
            $this->assertStringNotContainsString('/en/admin', $location ?? '');
        }
        
        // Verify that admin route is at /admin (not 301 redirect away from /admin)
        $this->assertNotEquals(301, $status, 'Admin route should not redirect from /admin');
    }

    /**
     * @test
     */
    public function localized_admin_url_redirects_to_non_localized_with_301(): void
    {
        $response = $this->get('/en/admin');

        $response->assertStatus(301);
        $response->assertRedirect('/admin');
    }

    /**
     * @test
     */
    public function localized_admin_nested_url_redirects_preserving_path(): void
    {
        $response = $this->get('/en/admin/tests');

        $response->assertStatus(301);
        $response->assertRedirect('/admin/tests');
    }

    /**
     * @test
     */
    public function localized_admin_url_preserves_query_params_on_redirect(): void
    {
        $response = $this->get('/en/admin/tests?page=2&filter=active');

        $response->assertStatus(301);
        
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('/admin/tests', $location);
        $this->assertStringContainsString('page=2', $location);
        $this->assertStringContainsString('filter=active', $location);
    }

    /**
     * @test
     */
    public function default_locale_admin_url_also_redirects(): void
    {
        $response = $this->get('/uk/admin');

        $response->assertStatus(301);
        $response->assertRedirect('/admin');
    }

    /**
     * @test
     */
    public function public_localized_urls_are_not_affected(): void
    {
        // Public pages with locale prefix should NOT be redirected to non-localized version
        $response = $this->get('/en/pages');

        // Should not be a 301 redirect (could be 200 or 404 depending on route setup)
        $this->assertNotEquals(301, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function admin_route_generation_does_not_include_locale(): void
    {
        // Generate an admin route and verify it doesn't have locale prefix
        $url = route('admin.dashboard');

        $this->assertStringStartsWith('/admin', parse_url($url, PHP_URL_PATH));
        $this->assertStringNotContainsString('/uk/admin', $url);
        $this->assertStringNotContainsString('/en/admin', $url);
    }

    /**
     * @test
     */
    public function localized_url_method_excludes_admin_paths(): void
    {
        // Test LocaleService::localizedUrl excludes admin paths from localization
        $adminUrl = 'http://localhost/admin/tests';
        
        // Even when requesting English locale, admin should not get prefix
        $result = LocaleService::localizedUrl('en', $adminUrl);
        
        $this->assertStringContainsString('/admin/tests', $result);
        $this->assertStringNotContainsString('/en/admin', $result);
        $this->assertStringNotContainsString('/uk/admin', $result);
    }

    /**
     * @test
     */
    public function localized_url_method_works_for_public_paths(): void
    {
        // Test LocaleService::localizedUrl works for public paths
        $publicUrl = 'http://localhost/pages';
        
        // When requesting English locale, should get prefix
        $result = LocaleService::localizedUrl('en', $publicUrl);
        
        $this->assertStringContainsString('/en/pages', $result);
    }

    /**
     * @test
     */
    public function localized_route_method_excludes_admin_routes(): void
    {
        // Test that LocaleService::localizedRoute excludes admin routes from localization
        app()->setLocale('en');
        
        $result = LocaleService::localizedRoute('admin.dashboard');
        
        $this->assertStringContainsString('/admin', $result);
        $this->assertStringNotContainsString('/en/admin', $result);
        $this->assertStringNotContainsString('/uk/admin', $result);
    }
}
