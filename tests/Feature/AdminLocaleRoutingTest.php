<?php

namespace Tests\Feature;

use App\Modules\LanguageManager\Services\LocaleService;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class AdminLocaleRoutingTest extends TestCase
{
    /** @test */
    public function admin_routes_are_available_without_locale_prefix(): void
    {
        $response = $this->withSession(['admin_authenticated' => true])
            ->get('/admin/set-locale?lang=en', ['HTTP_REFERER' => '/admin']);

        $response->assertStatus(302);
        $response->assertRedirect('/admin');
    }

    /** @test */
    public function localized_admin_urls_redirect_to_non_localized_paths(): void
    {
        $response = $this->get('/en/admin/words?filter=1');

        $response->assertStatus(301);
        $response->assertRedirect('/admin/words?filter=1');
    }

    /** @test */
    public function locale_switch_route_falls_back_to_config_when_language_database_is_unavailable(): void
    {
        config([
            'app.locale' => 'uk',
            'app.supported_locales' => ['uk', 'en', 'pl'],
        ]);

        LocaleService::clearCache();

        Schema::partialMock()
            ->shouldReceive('hasTable')
            ->with('languages')
            ->andThrow(new RuntimeException('Database unavailable'));

        $response = $this->get('/set-locale?lang=pl', [
            'HTTP_REFERER' => 'http://localhost/',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('locale', 'pl');
        $this->assertStringEndsWith('/pl', (string) $response->headers->get('Location'));
    }
}
