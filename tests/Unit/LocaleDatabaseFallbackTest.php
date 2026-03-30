<?php

namespace Tests\Unit;

use App\Modules\LanguageManager\Services\LocaleService;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class LocaleDatabaseFallbackTest extends TestCase
{
    protected function tearDown(): void
    {
        LocaleService::clearCache();

        parent::tearDown();
    }

    public function test_locale_service_uses_config_when_languages_table_check_throws(): void
    {
        config([
            'app.locale' => 'uk',
            'app.supported_locales' => ['uk', 'en', 'pl'],
        ]);

        Schema::partialMock()
            ->shouldReceive('hasTable')
            ->with('languages')
            ->andThrow(new RuntimeException('Database unavailable'));

        LocaleService::clearCache();

        $this->assertFalse(LocaleService::hasLanguagesTable());
        $this->assertNull(LocaleService::getDefaultLanguage());
        $this->assertSame([], LocaleService::getActiveLanguages()->all());
        $this->assertSame('uk', LocaleService::getDefaultLocaleCode());
        $this->assertSame(['uk', 'en', 'pl'], LocaleService::getSupportedLocaleCodes());
        $this->assertSame(
            'https://example.com/pl/catalog/tests-cards',
            LocaleService::localizedUrl('pl', 'https://example.com/catalog/tests-cards')
        );
    }

    public function test_route_service_provider_uses_config_locales_when_language_database_is_unavailable(): void
    {
        config([
            'app.locale' => 'uk',
            'app.supported_locales' => ['uk', 'en', 'pl'],
        ]);

        Schema::partialMock()
            ->shouldReceive('hasTable')
            ->with('languages')
            ->andThrow(new RuntimeException('Database unavailable'));

        $provider = new class($this->app) extends RouteServiceProvider
        {
            public function localeConfigForTest(): array
            {
                return $this->getLocaleConfig();
            }
        };

        $config = $provider->localeConfigForTest();

        $this->assertSame('uk', $config['default']);
        $this->assertSame(['uk', 'en', 'pl'], $config['active']);
        $this->assertSame([], $config['database_locales']);
    }
}
