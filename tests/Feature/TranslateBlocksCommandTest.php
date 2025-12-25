<?php

namespace Tests\Feature;

use App\Services\Translate\TranslationProviderFactory;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class TranslateBlocksCommandTest extends TestCase
{
    /**
     * Test that the pages:translate-blocks command is registered
     */
    public function test_translate_blocks_command_is_registered(): void
    {
        $commands = Artisan::all();

        $this->assertArrayHasKey('pages:translate-blocks', $commands);
    }

    /**
     * Test that the command validates provider option
     */
    public function test_command_validates_invalid_provider(): void
    {
        $exitCode = Artisan::call('pages:translate-blocks', [
            'targetLocale' => 'en',
            '--provider' => 'invalid_provider',
            '--dry-run' => true,
        ]);

        $this->assertEquals(1, $exitCode);

        $output = Artisan::output();
        $this->assertStringContainsString('Invalid provider', $output);
        $this->assertStringContainsString('auto, openai, gemini', $output);
    }

    /**
     * Test that the command requires a filter option
     */
    public function test_command_requires_filter_option(): void
    {
        $exitCode = Artisan::call('pages:translate-blocks', [
            'targetLocale' => 'en',
            '--dry-run' => true,
        ]);

        $output = Artisan::output();
        $this->assertStringContainsString('Please specify one of', $output);
    }

    /**
     * Test that the TranslationProviderFactory supports auto provider
     */
    public function test_translation_provider_factory_supports_auto(): void
    {
        $allowedProviders = TranslationProviderFactory::getAllowedProviders();

        $this->assertContains('auto', $allowedProviders);
        $this->assertContains('openai', $allowedProviders);
        $this->assertContains('gemini', $allowedProviders);
    }

    /**
     * Test that invalid provider throws exception from factory
     */
    public function test_translation_provider_factory_throws_on_invalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid provider');

        TranslationProviderFactory::make('invalid_provider');
    }
}
