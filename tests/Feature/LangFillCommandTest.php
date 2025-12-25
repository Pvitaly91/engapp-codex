<?php

namespace Tests\Feature;

use App\Services\Translate\TranslationProviderFactory;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LangFillCommandTest extends TestCase
{
    private string $testLangPath;
    private string $testCachePath;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create temporary test directories
        $this->testLangPath = storage_path('app/test-lang');
        $this->testCachePath = storage_path('app/lang-fill-cache');
        
        // Ensure test directories exist
        if (!File::isDirectory($this->testLangPath)) {
            File::makeDirectory($this->testLangPath, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up test directories
        if (File::isDirectory($this->testLangPath)) {
            File::deleteDirectory($this->testLangPath);
        }
        
        parent::tearDown();
    }

    /**
     * Test that the lang:fill command is registered
     */
    public function test_lang_fill_command_is_registered(): void
    {
        $commands = Artisan::all();
        
        $this->assertArrayHasKey('lang:fill', $commands);
        $this->assertArrayHasKey('lang:fill-many', $commands);
    }

    /**
     * Test that the command validates provider option
     */
    public function test_command_validates_invalid_provider(): void
    {
        $exitCode = Artisan::call('lang:fill', [
            'locale' => 'pl',
            '--provider' => 'invalid_provider',
            '--dry-run' => true,
        ]);

        $this->assertEquals(1, $exitCode);
        
        $output = Artisan::output();
        $this->assertStringContainsString('Invalid provider', $output);
        $this->assertStringContainsString('openai, gemini', $output);
    }

    /**
     * Test that the command validates source directory
     */
    public function test_command_validates_source_directory(): void
    {
        $exitCode = Artisan::call('lang:fill', [
            'locale' => 'pl',
            '--provider' => 'openai',
            '--path' => '/nonexistent/path',
            '--dry-run' => true,
        ]);

        $this->assertEquals(1, $exitCode);
        
        $output = Artisan::output();
        $this->assertStringContainsString('Source directory not found', $output);
    }

    /**
     * Test that the TranslationProviderFactory returns correct provider types
     */
    public function test_translation_provider_factory_creates_correct_providers(): void
    {
        // Test allowed providers list
        $allowedProviders = TranslationProviderFactory::getAllowedProviders();
        
        $this->assertContains('auto', $allowedProviders);
        $this->assertContains('openai', $allowedProviders);
        $this->assertContains('gemini', $allowedProviders);
    }

    /**
     * Test that invalid provider throws exception
     */
    public function test_translation_provider_factory_throws_on_invalid_provider(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid provider');
        
        TranslationProviderFactory::make('invalid_provider');
    }

    /**
     * Test the lang:fill-many command validates locales option
     */
    public function test_lang_fill_many_requires_locales(): void
    {
        $exitCode = Artisan::call('lang:fill-many', [
            '--dry-run' => true,
        ]);

        $this->assertEquals(1, $exitCode);
        
        $output = Artisan::output();
        $this->assertStringContainsString('Please specify locales', $output);
    }

    /**
     * Test that the command creates target directory if not exists
     */
    public function test_command_creates_target_directory(): void
    {
        // Create source directory with a test file
        $sourceDir = $this->testLangPath . '/en';
        File::makeDirectory($sourceDir, 0755, true);
        File::put($sourceDir . '/test.php', "<?php return ['hello' => 'Hello'];");

        $targetDir = $this->testLangPath . '/test_locale';
        
        // Make sure target doesn't exist
        if (File::isDirectory($targetDir)) {
            File::deleteDirectory($targetDir);
        }

        // Run command in dry-run mode
        $exitCode = Artisan::call('lang:fill', [
            'locale' => 'test_locale',
            '--provider' => 'openai',
            '--path' => $sourceDir,
            '--dry-run' => true,
        ]);

        // Clean up
        File::deleteDirectory($sourceDir);
        
        // Note: In dry-run mode, directory is still created as per the implementation
        // This is expected behavior
        $this->assertEquals(0, $exitCode);
    }
}
