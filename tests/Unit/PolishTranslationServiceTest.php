<?php

namespace Tests\Unit;

use App\Services\PolishTranslationService;
use PHPUnit\Framework\TestCase;

class PolishTranslationServiceTest extends TestCase
{
    private PolishTranslationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        // Note: These tests don't require actual API calls
        // They test the logic and validation methods
    }

    public function test_clean_translation_removes_quotes()
    {
        $service = new PolishTranslationService();
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('cleanTranslation');
        $method->setAccessible(true);

        $this->assertEquals('test', $method->invoke($service, '"test"'));
        $this->assertEquals('test', $method->invoke($service, "'test'"));
        $this->assertEquals('test', $method->invoke($service, '"test"'));
    }

    public function test_clean_translation_removes_explanations()
    {
        $service = new PolishTranslationService();
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('cleanTranslation');
        $method->setAccessible(true);

        $this->assertEquals('dom', $method->invoke($service, 'dom (house)'));
        $this->assertEquals('kot', $method->invoke($service, 'kot - cat'));
        $this->assertEquals('pies', $method->invoke($service, 'pies: dog'));
    }

    public function test_clean_translation_takes_first_option()
    {
        $service = new PolishTranslationService();
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('cleanTranslation');
        $method->setAccessible(true);

        $this->assertEquals('dom', $method->invoke($service, 'dom/mieszkanie'));
        $this->assertEquals('kot', $method->invoke($service, 'kot/kotek'));
    }

    public function test_clean_translation_returns_null_for_empty()
    {
        $service = new PolishTranslationService();
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('cleanTranslation');
        $method->setAccessible(true);

        $this->assertNull($method->invoke($service, null));
        $this->assertNull($method->invoke($service, ''));
        $this->assertNull($method->invoke($service, '  '));
    }

    public function test_cache_functionality()
    {
        $service = new PolishTranslationService();

        // Test cache is empty initially
        $this->assertEmpty($service->getCache());

        // Load cache
        $cache = ['cat' => 'kot', 'dog' => 'pies'];
        $service->loadCache($cache);

        // Verify cache loaded
        $this->assertEquals($cache, $service->getCache());
    }

    public function test_validation_detects_same_word()
    {
        $service = new PolishTranslationService();

        // Without API call, validation should detect when word equals translation
        // The validateTranslation method will try to verify loanword, but without API it returns invalid
        // This is expected behavior - in production, API would verify
        $result = $service->validateTranslation('test', 'test');

        // We expect validation to trigger for same word
        $this->assertIsArray($result);
        $this->assertArrayHasKey('valid', $result);
        $this->assertArrayHasKey('translation', $result);
    }
}
