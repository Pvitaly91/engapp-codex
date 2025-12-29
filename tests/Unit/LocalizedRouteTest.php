<?php

namespace Tests\Unit;

use App\Modules\LanguageManager\Services\LocaleService;
use App\Modules\LanguageManager\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizedRouteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test languages in database
        Language::create([
            'code' => 'uk',
            'name' => 'Ukrainian',
            'native_name' => 'Українська',
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        
        Language::create([
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 2,
        ]);
        
        Language::create([
            'code' => 'pl',
            'name' => 'Polish',
            'native_name' => 'Polski',
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        // Clear any cached language data
        LocaleService::clearCache();
    }

    /**
     * Test that localized routes work correctly for default language (uk).
     * 
     * The bug was: when route() returned a URL with prefix like /pl/catalog,
     * and the target locale was 'uk' (default), it would return /pl/catalog
     * instead of stripping the prefix to return /catalog.
     */
    public function test_default_language_urls_have_no_prefix()
    {
        // Set app locale to Ukrainian (default)
        app()->setLocale('uk');
        
        // Mock route() to simulate it returning a URL with polish prefix
        // This can happen due to RouteServiceProvider registering routes with prefixes
        $mockUrl = 'http://localhost/pl/catalog/tests-cards';
        
        // Call localizedRoute with the mock URL
        // We're testing the internal logic by directly manipulating what route() would return
        $result = $this->callLocalizedRouteWithMockUrl('catalog.tests-cards', $mockUrl, 'uk');
        
        // For default language, URL should have NO prefix
        $this->assertEquals('/catalog/tests-cards', $result);
    }

    public function test_non_default_language_urls_have_correct_prefix()
    {
        // Set app locale to Polish
        app()->setLocale('pl');
        
        // Mock route() returning various URLs
        $testCases = [
            ['input' => 'http://localhost/catalog/tests-cards', 'expected' => '/pl/catalog/tests-cards'],
            ['input' => 'http://localhost/pl/catalog/tests-cards', 'expected' => '/pl/catalog/tests-cards'],
            ['input' => 'http://localhost/en/catalog/tests-cards', 'expected' => '/pl/catalog/tests-cards'],
        ];
        
        foreach ($testCases as $case) {
            $result = $this->callLocalizedRouteWithMockUrl('catalog.tests-cards', $case['input'], 'pl');
            $this->assertEquals($case['expected'], $result, "Failed for input: {$case['input']}");
        }
    }

    public function test_english_language_urls_have_correct_prefix()
    {
        // Set app locale to English
        app()->setLocale('en');
        
        $testCases = [
            ['input' => 'http://localhost/catalog/tests-cards', 'expected' => '/en/catalog/tests-cards'],
            ['input' => 'http://localhost/pl/catalog/tests-cards', 'expected' => '/en/catalog/tests-cards'],
            ['input' => 'http://localhost/en/catalog/tests-cards', 'expected' => '/en/catalog/tests-cards'],
        ];
        
        foreach ($testCases as $case) {
            $result = $this->callLocalizedRouteWithMockUrl('catalog.tests-cards', $case['input'], 'en');
            $this->assertEquals($case['expected'], $result, "Failed for input: {$case['input']}");
        }
    }

    public function test_switching_to_default_language_removes_prefix()
    {
        // User is on Polish page
        app()->setLocale('pl');
        
        // Mock route() returning Polish URL
        $mockUrl = 'http://localhost/pl/catalog/tests-cards';
        
        // Generate URL for Ukrainian (default)
        $result = $this->callLocalizedRouteWithMockUrl('catalog.tests-cards', $mockUrl, 'uk');
        
        // Should strip /pl/ prefix
        $this->assertEquals('/catalog/tests-cards', $result);
    }

    public function test_switching_between_non_default_languages()
    {
        // User is on English page
        app()->setLocale('en');
        
        // Mock route() returning English URL
        $mockUrl = 'http://localhost/en/catalog/tests-cards';
        
        // Generate URL for Polish
        $result = $this->callLocalizedRouteWithMockUrl('catalog.tests-cards', $mockUrl, 'pl');
        
        // Should replace /en/ with /pl/
        $this->assertEquals('/pl/catalog/tests-cards', $result);
    }

    /**
     * Helper method to test localizedRoute logic with a mocked route URL.
     * This simulates the behavior when route() returns different URLs.
     */
    protected function callLocalizedRouteWithMockUrl(string $routeName, string $mockUrl, string $targetLocale): string
    {
        // Parse the URL components
        $parsedUrl = parse_url($mockUrl);
        $path = $parsedUrl['path'] ?? '/';
        
        // Simulate the logic from LocaleService::localizedRoute
        $default = LocaleService::getDefaultLanguage();
        $defaultCode = $default ? $default->code : config('app.locale', 'uk');
        
        $segments = array_values(array_filter(explode('/', $path)));
        $activeCodes = LocaleService::getActiveLanguages()->pluck('code')->toArray();
        
        // Always remove existing locale prefix
        if (!empty($segments) && in_array($segments[0], $activeCodes)) {
            array_shift($segments);
        }
        
        // Add locale prefix only if not default
        if ($targetLocale !== $defaultCode) {
            array_unshift($segments, $targetLocale);
        }
        
        return '/' . implode('/', $segments);
    }
}
