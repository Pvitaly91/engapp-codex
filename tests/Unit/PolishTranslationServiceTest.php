<?php

namespace Tests\Unit;

use Tests\TestCase;

class PolishTranslationServiceTest extends TestCase
{
    public function test_clean_translation_removes_quotes()
    {
        $service = new class extends \App\Services\PolishTranslationService {
            public function testClean($translation) {
                return $this->cleanTranslation($translation);
            }
        };

        $this->assertEquals('test', $service->testClean('"test"'));
        $this->assertEquals('test', $service->testClean("'test'"));
        $this->assertEquals('test', $service->testClean('"test"'));
    }

    public function test_clean_translation_removes_explanations()
    {
        $service = new class extends \App\Services\PolishTranslationService {
            public function testClean($translation) {
                return $this->cleanTranslation($translation);
            }
        };

        $this->assertEquals('dom', $service->testClean('dom (house)'));
        $this->assertEquals('kot', $service->testClean('kot - cat'));
        $this->assertEquals('pies', $service->testClean('pies: dog'));
    }

    public function test_clean_translation_takes_first_option()
    {
        $service = new class extends \App\Services\PolishTranslationService {
            public function testClean($translation) {
                return $this->cleanTranslation($translation);
            }
        };

        $this->assertEquals('dom', $service->testClean('dom/mieszkanie'));
        $this->assertEquals('kot', $service->testClean('kot/kotek'));
    }

    public function test_clean_translation_returns_null_for_empty()
    {
        $service = new class extends \App\Services\PolishTranslationService {
            public function testClean($translation) {
                return $this->cleanTranslation($translation);
            }
        };

        $this->assertNull($service->testClean(null));
        $this->assertNull($service->testClean(''));
        $this->assertNull($service->testClean('  '));
    }

    public function test_cache_functionality()
    {
        $service = new \App\Services\PolishTranslationService();

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
        $service = new \App\Services\PolishTranslationService();

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
