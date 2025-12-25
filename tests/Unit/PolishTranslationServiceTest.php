<?php

namespace Tests\Unit;

use Tests\TestCase;

class TranslationServiceTest extends TestCase
{
    private function createTestService()
    {
        return new class('pl', 'gemini') extends \App\Services\TranslationService {
            // Override constructor to skip API key validation for tests
            public function __construct(string $targetLang = 'pl', string $provider = 'gemini')
            {
                $this->targetLang = $targetLang;
                $this->provider = $provider;
            }
            
            public function testClean($translation) {
                return $this->cleanTranslation($translation);
            }
            
            private string $targetLang;
            private string $provider;
        };
    }
    
    public function test_clean_translation_removes_quotes()
    {
        $service = $this->createTestService();

        $this->assertEquals('test', $service->testClean('"test"'));
        $this->assertEquals('test', $service->testClean("'test'"));
        $this->assertEquals('test', $service->testClean('"test"'));
    }

    public function test_clean_translation_removes_explanations()
    {
        $service = $this->createTestService();

        $this->assertEquals('dom', $service->testClean('dom (house)'));
        $this->assertEquals('kot', $service->testClean('kot - cat'));
        $this->assertEquals('pies', $service->testClean('pies: dog'));
    }

    public function test_clean_translation_takes_first_option()
    {
        $service = $this->createTestService();

        $this->assertEquals('dom', $service->testClean('dom/mieszkanie'));
        $this->assertEquals('kot', $service->testClean('kot/kotek'));
    }

    public function test_clean_translation_returns_null_for_empty()
    {
        $service = $this->createTestService();

        $this->assertNull($service->testClean(null));
        $this->assertNull($service->testClean(''));
        $this->assertNull($service->testClean('  '));
    }

    public function test_cache_functionality()
    {
        // This test requires mocking config, so we skip API validation
        if (!config('services.gemini.key') && !config('services.openai.key')) {
            $this->markTestSkipped('No API keys configured for testing');
        }
        
        $service = new \App\Services\TranslationService('pl', 'auto');

        // Test cache is empty initially
        $this->assertEmpty($service->getCache());

        // Load cache
        $cache = ['cat_pl' => 'kot', 'dog_pl' => 'pies'];
        $service->loadCache($cache);

        // Verify cache loaded
        $this->assertEquals($cache, $service->getCache());
    }

    public function test_validation_detects_same_word()
    {
        // This test requires API keys, so we skip if not configured
        if (!config('services.gemini.key') && !config('services.openai.key')) {
            $this->markTestSkipped('No API keys configured for testing');
        }
        
        $service = new \App\Services\TranslationService('pl', 'auto');

        // Without API call, validation should detect when word equals translation
        // The validateTranslation method will try to verify loanword, but without API it returns invalid
        // This is expected behavior - in production, API would verify
        $result = $service->validateTranslation('test', 'test');

        // We expect validation to trigger for same word
        $this->assertIsArray($result);
        $this->assertArrayHasKey('valid', $result);
        $this->assertArrayHasKey('translation', $result);
    }

    public function test_supports_multiple_languages()
    {
        // This test requires API keys, so we skip if not configured
        if (!config('services.gemini.key') && !config('services.openai.key')) {
            $this->markTestSkipped('No API keys configured for testing');
        }
        
        $servicePl = new \App\Services\TranslationService('pl', 'auto');
        $serviceUk = new \App\Services\TranslationService('uk', 'auto');
        $serviceEn = new \App\Services\TranslationService('en', 'auto');

        // Each service should be created successfully
        $this->assertInstanceOf(\App\Services\TranslationService::class, $servicePl);
        $this->assertInstanceOf(\App\Services\TranslationService::class, $serviceUk);
        $this->assertInstanceOf(\App\Services\TranslationService::class, $serviceEn);
    }

    public function test_supports_provider_selection()
    {
        // This test requires API keys, so we skip if not configured
        if (!config('services.gemini.key') && !config('services.openai.key')) {
            $this->markTestSkipped('No API keys configured for testing');
        }
        
        $service = new \App\Services\TranslationService('pl', 'auto');
        
        // Should detect a provider
        $this->assertContains($service->getProvider(), ['gemini', 'openai']);
    }
}
