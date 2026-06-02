<?php

namespace App\Services\V3PromptGenerator;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class V3SeederBlueprintService
{
    /**
     * @return array<int, string>
     */
    public function namespaceSuggestions(): array
    {
        $root = database_path('seeders/V3');
        $reservedSegments = ['Concerns', 'Localizations'];
        $defaultNamespaces = ['AI', 'AI\\ChatGpt', 'AI\\ChatGptPro', 'AI\\Gemini', 'AI\\Claude', 'V2'];

        if (! File::isDirectory($root)) {
            return $defaultNamespaces;
        }

        $namespaces = collect($defaultNamespaces)
            ->merge(collect(File::allFiles($root))
                ->filter(fn ($file) => strtolower($file->getExtension()) === 'php')
                ->map(function ($file) use ($root, $reservedSegments) {
                    $relativePath = ltrim(str_replace('\\', '/', Str::after($file->getPathname(), $root)), '/');
                    $relativeDirectory = trim(str_replace('\\', '/', dirname($relativePath)), '/.');
                    $segments = $relativeDirectory === ''
                        ? []
                        : array_values(array_filter(explode('/', $relativeDirectory)));

                    if ($segments === [] || array_intersect($segments, $reservedSegments) !== []) {
                        return null;
                    }

                    $fileBaseName = pathinfo($relativePath, PATHINFO_FILENAME);

                    // Package-local real seeders live in <Namespace>/<SeederClass>/<SeederClass>.php.
                    if (($segments[count($segments) - 1] ?? null) === $fileBaseName) {
                        array_pop($segments);
                    }

                    if ($segments === [] || array_intersect($segments, $reservedSegments) !== []) {
                        return null;
                    }

                    return implode('\\', $segments);
                }))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        return $namespaces !== [] ? $namespaces : $defaultNamespaces;
    }

    public function normalizeNamespace(string $namespace): string
    {
        $normalized = trim($namespace);
        $normalized = str_replace('/', '\\', $normalized);
        $normalized = preg_replace('/\\\\+/', '\\\\', $normalized) ?? $normalized;

        return trim($normalized, "\\ \t\n\r\0\x0B");
    }

    public function buildPreview(string $namespace, ?string $topic): array
    {
        $normalizedNamespace = $this->normalizeNamespace($namespace);
        $slug = $this->topicSlug($topic);
        $stem = $this->topicStem($topic);
        $useQuestionsOnlySuffix = $this->shouldUseQuestionsOnlySuffix($normalizedNamespace);

        $className = $stem . ($useQuestionsOnlySuffix ? 'V3QuestionsOnlySeeder' : 'V3Seeder');
        $relativeNamespacePath = str_replace('\\', '/', $normalizedNamespace);
        $seederRelativePath = 'database/seeders/V3/' . $relativeNamespacePath . '/' . $className . '.php';
        $packageRelativePath = 'database/seeders/V3/' . $relativeNamespacePath . '/' . $className;
        $realSeederRelativePath = $packageRelativePath . '/' . $className . '.php';
        $definitionRelativePath = $packageRelativePath . '/definition.json';
        $ukLocalizationRelativePath = $packageRelativePath . '/localizations/uk.json';
        $enLocalizationRelativePath = $packageRelativePath . '/localizations/en.json';
        $plLocalizationRelativePath = $packageRelativePath . '/localizations/pl.json';

        return [
            'target_namespace' => $normalizedNamespace,
            'topic_slug' => $slug,
            'class_name' => $className,
            'fully_qualified_class_name' => 'Database\\Seeders\\V3\\' . $normalizedNamespace . '\\' . $className,
            'seeder_relative_path' => $seederRelativePath,
            'seeder_absolute_path' => base_path(str_replace('/', DIRECTORY_SEPARATOR, $seederRelativePath)),
            'package_relative_path' => $packageRelativePath,
            'package_absolute_path' => base_path(str_replace('/', DIRECTORY_SEPARATOR, $packageRelativePath)),
            'real_seeder_relative_path' => $realSeederRelativePath,
            'real_seeder_absolute_path' => base_path(str_replace('/', DIRECTORY_SEPARATOR, $realSeederRelativePath)),
            'definition_relative_path' => $definitionRelativePath,
            'definition_absolute_path' => base_path(str_replace('/', DIRECTORY_SEPARATOR, $definitionRelativePath)),
            'localization_uk_relative_path' => $ukLocalizationRelativePath,
            'localization_uk_absolute_path' => base_path(str_replace('/', DIRECTORY_SEPARATOR, $ukLocalizationRelativePath)),
            'localization_en_relative_path' => $enLocalizationRelativePath,
            'localization_en_absolute_path' => base_path(str_replace('/', DIRECTORY_SEPARATOR, $enLocalizationRelativePath)),
            'localization_pl_relative_path' => $plLocalizationRelativePath,
            'localization_pl_absolute_path' => base_path(str_replace('/', DIRECTORY_SEPARATOR, $plLocalizationRelativePath)),
            'uses_questions_only_suffix' => $useQuestionsOnlySuffix,
        ];
    }

    /**
     * @return array<int, string>
     */
    public function referenceFiles(string $namespace): array
    {
        $normalizedNamespace = $this->normalizeNamespace($namespace);
        $files = collect([
            'app/Support/Database/JsonTestSeeder.php',
            'app/Support/Database/JsonTestDirectorySeeder.php',
            'app/Support/Database/JsonTestLocalizationManager.php',
        ]);

        foreach ($this->stubSeederRelativePaths($normalizedNamespace) as $seederRelativePath) {
            $files->push($seederRelativePath);

            $realSeederPath = $this->realSeederRelativePathFromSeeder($seederRelativePath);

            if ($realSeederPath !== null) {
                $files->push($realSeederPath);
            }

            $definitionPath = $this->definitionRelativePathFromSeeder($seederRelativePath);

            if ($definitionPath !== null) {
                $files->push($definitionPath);
            }

            foreach ($this->localizationRelativePathsFromSeeder($seederRelativePath) as $localizationPath) {
                $files->push($localizationPath);
            }
        }

        if ($files->count() <= 3) {
            foreach ($this->fallbackReferenceFiles() as $relativePath) {
                $files->push($relativePath);
            }
        }

        return $files
            ->filter(fn (string $relativePath) => File::exists(base_path($relativePath)))
            ->unique()
            ->values()
            ->all();
    }

    public function topicSlug(?string $topic): string
    {
        $normalized = trim((string) $topic);

        if ($normalized === '') {
            return 'new_v3_topic';
        }

        $slug = Str::slug($normalized, '_');

        return $slug !== '' ? $slug : 'new_v3_topic';
    }

    public function topicStem(?string $topic): string
    {
        return Str::studly(str_replace('_', ' ', $this->topicSlug($topic))) ?: 'NewV3Topic';
    }

    public function topicFromExternalUrl(string $url): string
    {
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

        if ($path === '') {
            return 'External Theory Topic';
        }

        $lastSegment = last(array_values(array_filter(explode('/', $path))));
        $normalized = str_replace(['-', '_'], ' ', (string) $lastSegment);
        $normalized = trim($normalized);

        return $normalized !== '' ? Str::title($normalized) : 'External Theory Topic';
    }

    protected function shouldUseQuestionsOnlySuffix(string $namespace): bool
    {
        $lowerNamespace = Str::lower($namespace);

        foreach (['chatgpt', 'gpt', 'gemini', 'claude', 'anthropic', 'openai', 'llm', 'ia\\', '\\ia', 'ai\\', '\\ai'] as $token) {
            if (str_contains($lowerNamespace, $token)) {
                return true;
            }
        }

        return Str::startsWith($lowerNamespace, 'ia');
    }

    /**
     * @return array<int, string>
     */
    protected function stubSeederRelativePaths(string $namespace): array
    {
        $namespacePath = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
        $directory = database_path('seeders/V3/' . $namespacePath);

        if (! File::isDirectory($directory)) {
            return [];
        }

        return collect(File::files($directory))
            ->filter(fn ($file) => strtolower($file->getExtension()) === 'php')
            ->sortBy(fn ($file) => str_replace('\\', '/', $file->getPathname()))
            ->map(fn ($file) => $this->toRelativeProjectPath($file->getPathname()))
            ->take(2)
            ->values()
            ->all();
    }

    protected function definitionRelativePathFromSeeder(string $seederRelativePath): ?string
    {
        $packageDirectory = $this->packageDirectoryRelativePathFromSeeder($seederRelativePath);

        if ($packageDirectory === null) {
            return null;
        }

        $definitionPath = $packageDirectory . '/definition.json';

        return File::exists(base_path($definitionPath)) ? $definitionPath : null;
    }

    protected function realSeederRelativePathFromSeeder(string $seederRelativePath): ?string
    {
        $packageDirectory = $this->packageDirectoryRelativePathFromSeeder($seederRelativePath);

        if ($packageDirectory === null) {
            return null;
        }

        $className = pathinfo($seederRelativePath, PATHINFO_FILENAME);
        $realSeederPath = $packageDirectory . '/' . $className . '.php';

        return File::exists(base_path($realSeederPath)) ? $realSeederPath : null;
    }

    /**
     * @return array<int, string>
     */
    protected function localizationRelativePathsFromSeeder(string $seederRelativePath): array
    {
        $packageDirectory = $this->packageDirectoryRelativePathFromSeeder($seederRelativePath);

        if ($packageDirectory === null) {
            return [];
        }

        return collect(['uk', 'en', 'pl'])
            ->map(fn (string $locale) => $packageDirectory . '/localizations/' . $locale . '.json')
            ->filter(fn (string $path) => File::exists(base_path($path)))
            ->values()
            ->all();
    }

    protected function packageDirectoryRelativePathFromSeeder(string $seederRelativePath): ?string
    {
        $className = pathinfo($seederRelativePath, PATHINFO_FILENAME);
        $directory = trim(str_replace('\\', '/', dirname($seederRelativePath)), '/');

        if ($directory === '' || $className === '') {
            return null;
        }

        $packageDirectory = $directory . '/' . $className;

        return File::isDirectory(base_path($packageDirectory)) ? $packageDirectory : null;
    }

    /**
     * @return array<int, string>
     */
    protected function fallbackReferenceFiles(): array
    {
        return [
            'database/seeders/V3/AI/ChatGptPro/PluralNounsSEsIesV3QuestionsOnlySeeder.php',
            'database/seeders/V3/AI/ChatGptPro/PluralNounsSEsIesV3QuestionsOnlySeeder/PluralNounsSEsIesV3QuestionsOnlySeeder.php',
            'database/seeders/V3/AI/ChatGptPro/PluralNounsSEsIesV3QuestionsOnlySeeder/definition.json',
            'database/seeders/V3/AI/ChatGptPro/PluralNounsSEsIesV3QuestionsOnlySeeder/localizations/uk.json',
            'database/seeders/V3/AI/ChatGptPro/PluralNounsSEsIesV3QuestionsOnlySeeder/localizations/en.json',
            'database/seeders/V3/AI/ChatGptPro/PluralNounsSEsIesV3QuestionsOnlySeeder/localizations/pl.json',
            'database/seeders/V3/V2/BasicWordOrderPracticeV3Seeder.php',
            'database/seeders/V3/V2/BasicWordOrderPracticeV3Seeder/BasicWordOrderPracticeV3Seeder.php',
            'database/seeders/V3/V2/BasicWordOrderPracticeV3Seeder/definition.json',
            'database/seeders/V3/V2/BasicWordOrderPracticeV3Seeder/localizations/uk.json',
            'database/seeders/V3/V2/BasicWordOrderPracticeV3Seeder/localizations/en.json',
            'database/seeders/V3/V2/BasicWordOrderPracticeV3Seeder/localizations/pl.json',
        ];
    }

    protected function toRelativeProjectPath(string $path): string
    {
        $normalized = str_replace('\\', '/', $path);
        $projectRoot = str_replace('\\', '/', base_path());

        return ltrim(Str::after($normalized, $projectRoot), '/');
    }
}
