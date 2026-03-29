<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionMethod;
use SplFileInfo;
use Tests\TestCase;

class PassiveVoiceSeederMergeTest extends TestCase
{
    public function test_page_v2_passive_voice_seeders_are_consolidated_under_single_namespace(): void
    {
        $legacyDirectory = database_path('seeders/Page_v2/PassiveVoiceV2');

        $legacyFiles = File::isDirectory($legacyDirectory)
            ? collect(File::allFiles($legacyDirectory))
                ->filter(fn (SplFileInfo $file) => $file->getExtension() === 'php')
                ->values()
            : collect();

        $this->assertCount(0, $legacyFiles, 'Page_v2 should not keep PassiveVoiceV2 PHP seeders after the merge.');

        $reflections = collect(File::allFiles(database_path('seeders/Page_v2/PassiveVoice')))
            ->filter(fn (SplFileInfo $file) => $file->getExtension() === 'php')
            ->map(fn (SplFileInfo $file) => $this->resolveSeederClass($file))
            ->filter(fn (string $class) => class_exists($class))
            ->map(fn (string $class) => new ReflectionClass($class))
            ->reject(fn (ReflectionClass $reflection) => $reflection->isAbstract())
            ->filter(fn (ReflectionClass $reflection) => $reflection->hasMethod('slug'))
            ->values();

        $slugs = $reflections
            ->map(fn (ReflectionClass $reflection) => $this->invokeProtected($reflection, 'slug'))
            ->filter(fn (?string $slug) => is_string($slug) && $slug !== '')
            ->values();

        $classNames = $reflections->map(fn (ReflectionClass $reflection) => $reflection->getName());

        $this->assertTrue(
            $classNames->contains('Database\\Seeders\\Page_v2\\PassiveVoice\\SpecialCases\\PassiveVoiceByPhraseTheorySeeder'),
            'By-phrase topic should remain available inside merged Page_v2 PassiveVoice seeders.'
        );

        $this->assertSame(
            $slugs->count(),
            $slugs->unique()->count(),
            'Merged Page_v2 PassiveVoice seeders should not expose duplicate slugs.'
        );
    }

    public function test_page_v3_passive_voice_seeders_and_definitions_are_consolidated(): void
    {
        $legacyWrapperDirectory = database_path('seeders/Page_V3/PassiveVoiceV2');
        $legacyDefinitionFiles = collect(File::files(database_path('seeders/Page_V3/definitions')))
            ->filter(fn (SplFileInfo $file) => str_contains($file->getFilename(), 'passive_voice_v2_'));
        $legacyLocalizationFiles = collect(['en', 'pl'])
            ->flatMap(fn (string $locale) => File::files(database_path("seeders/Page_V3/localizations/{$locale}")))
            ->filter(fn (SplFileInfo $file) => str_contains($file->getFilename(), 'passive_voice_v2_'));

        $legacyWrappers = File::isDirectory($legacyWrapperDirectory)
            ? collect(File::allFiles($legacyWrapperDirectory))
                ->filter(fn (SplFileInfo $file) => $file->getExtension() === 'php')
            : collect();

        $this->assertCount(0, $legacyWrappers, 'Page_V3 should not keep PassiveVoiceV2 wrapper classes after the merge.');
        $this->assertCount(0, $legacyDefinitionFiles, 'Page_V3 should not keep passive_voice_v2_* definitions after the merge.');
        $this->assertCount(0, $legacyLocalizationFiles, 'Page_V3 should not keep passive_voice_v2_* localizations after the merge.');

        $this->assertFileExists(
            database_path('seeders/Page_V3/PassiveVoice/SpecialCases/PassiveVoiceByPhraseTheorySeeder.php')
        );
        $this->assertFileExists(
            database_path('seeders/Page_V3/definitions/passive_voice_by_phrase_theory.json')
        );
    }

    private function resolveSeederClass(SplFileInfo $file): string
    {
        $relativePath = str_replace(
            ['/', '\\'],
            '\\',
            ltrim(str_replace(database_path('seeders'), '', $file->getPathname()), '\\/')
        );

        return 'Database\\Seeders\\' . preg_replace('/\.php$/', '', $relativePath);
    }

    private function invokeProtected(ReflectionClass $reflection, string $methodName): mixed
    {
        $instance = $reflection->newInstance();
        $method = $reflection->getMethod($methodName);

        if ($method instanceof ReflectionMethod) {
            $method->setAccessible(true);
        }

        return $method->invoke($instance);
    }
}
