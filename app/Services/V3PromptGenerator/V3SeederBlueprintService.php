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
        $reservedSegments = ['Concerns', 'definitions', 'json', 'localizations'];

        if (! File::isDirectory($root)) {
            return ['AI\\ChatGptPro', 'AI\\ChatGpt', 'V2'];
        }

        $namespaces = collect(File::allFiles($root))
            ->filter(fn ($file) => strtolower($file->getExtension()) === 'php')
            ->map(function ($file) use ($root, $reservedSegments) {
                $relativeDirectory = trim(str_replace('\\', '/', Str::after($file->getPath(), $root)), '/');

                if ($relativeDirectory === '') {
                    return null;
                }

                $segments = array_values(array_filter(explode('/', $relativeDirectory)));

                if ($segments === [] || array_intersect($segments, $reservedSegments) !== []) {
                    return null;
                }

                return implode('\\', $segments);
            })
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        return $namespaces !== [] ? $namespaces : ['AI\\ChatGptPro', 'AI\\ChatGpt', 'V2'];
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
        $definitionFileName = $slug . ($useQuestionsOnlySuffix ? '_v3_questions_only.json' : '_v3.json');
        $relativeNamespacePath = str_replace('\\', '/', $normalizedNamespace);
        $seederRelativePath = 'database/seeders/V3/' . $relativeNamespacePath . '/' . $className . '.php';
        $definitionRelativePath = 'database/seeders/V3/definitions/' . $relativeNamespacePath . '/' . $definitionFileName;

        return [
            'target_namespace' => $normalizedNamespace,
            'topic_slug' => $slug,
            'class_name' => $className,
            'fully_qualified_class_name' => 'Database\\Seeders\\V3\\' . $normalizedNamespace . '\\' . $className,
            'seeder_relative_path' => $seederRelativePath,
            'seeder_absolute_path' => base_path(str_replace('/', DIRECTORY_SEPARATOR, $seederRelativePath)),
            'definition_relative_path' => $definitionRelativePath,
            'definition_absolute_path' => base_path(str_replace('/', DIRECTORY_SEPARATOR, $definitionRelativePath)),
            'uses_questions_only_suffix' => $useQuestionsOnlySuffix,
        ];
    }

    /**
     * @return array<int, string>
     */
    public function referenceFiles(string $namespace): array
    {
        $normalizedNamespace = $this->normalizeNamespace($namespace);
        $namespacePath = str_replace('\\', DIRECTORY_SEPARATOR, $normalizedNamespace);

        $paths = collect([
            app_path('Support/Database/JsonTestSeeder.php'),
            database_path('seeders/V3/' . $namespacePath),
            database_path('seeders/V3/definitions/' . $namespacePath),
            database_path('seeders/V3/localizations/en/' . $namespacePath),
            database_path('seeders/V3/localizations/pl/' . $namespacePath),
        ]);

        $files = collect();

        foreach ($paths as $path) {
            if (File::isFile($path)) {
                $files->push($this->toRelativeProjectPath($path));

                continue;
            }

            if (! File::isDirectory($path)) {
                continue;
            }

            foreach (File::files($path) as $file) {
                if (! $file->isFile()) {
                    continue;
                }

                $files->push($this->toRelativeProjectPath($file->getPathname()));
            }
        }

        if ($files->isEmpty()) {
            $fallback = [
                'app/Support/Database/JsonTestSeeder.php',
                'database/seeders/V3/AI/ChatGptPro/PluralNounsSEsIesV3QuestionsOnlySeeder.php',
                'database/seeders/V3/definitions/AI/ChatGptPro/plural_nouns_s_es_ies_v3_questions_only.json',
                'database/seeders/V3/V2/BasicWordOrderPracticeV3Seeder.php',
                'database/seeders/V3/definitions/V2/basic_word_order_practice_v2.json',
            ];

            return array_values(array_unique(array_filter($fallback)));
        }

        return $files
            ->unique()
            ->sort()
            ->take(8)
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

    protected function toRelativeProjectPath(string $path): string
    {
        $normalized = str_replace('\\', '/', $path);
        $projectRoot = str_replace('\\', '/', base_path());

        return ltrim(Str::after($normalized, $projectRoot), '/');
    }
}
