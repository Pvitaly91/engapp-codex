<?php

namespace App\Services\V3PromptGenerator;

use App\Support\Scaffold\AbstractSkeletonWriter;
use Illuminate\Support\Str;
use RuntimeException;

class V3SkeletonWriterService extends AbstractSkeletonWriter
{
    /**
     * @param  array<string, mixed>  $generated
     * @return array<int, string>
     */
    public function plannedFiles(array $generated): array
    {
        $preview = (array) ($generated['preview'] ?? []);

        return array_values(array_filter([
            $this->previewPath($preview, 'seeder_absolute_path', 'seeder_relative_path'),
            $this->previewPath($preview, 'real_seeder_absolute_path', 'real_seeder_relative_path'),
            $this->previewPath($preview, 'definition_absolute_path', 'definition_relative_path'),
            $this->previewPath($preview, 'localization_uk_absolute_path', 'localization_uk_relative_path'),
            $this->previewPath($preview, 'localization_en_absolute_path', 'localization_en_relative_path'),
            $this->previewPath($preview, 'localization_pl_absolute_path', 'localization_pl_relative_path'),
        ]));
    }

    /**
     * @param  array<string, mixed>  $generated
     * @return array<string, mixed>
     */
    public function write(array $generated, bool $force = false): array
    {
        return $this->writeFiles($this->fileContents($generated), $force);
    }

    /**
     * @param  array<string, mixed>  $generated
     * @return array<string, string>
     */
    private function fileContents(array $generated): array
    {
        $preview = (array) ($generated['preview'] ?? []);
        $source = (array) ($generated['source'] ?? []);
        $distribution = (array) ($generated['distribution'] ?? []);

        $className = trim((string) ($preview['class_name'] ?? ''));
        $fullyQualifiedClassName = trim((string) ($preview['fully_qualified_class_name'] ?? ''));
        $targetNamespace = trim((string) ($preview['target_namespace'] ?? ''));
        $topicSlug = trim((string) ($preview['topic_slug'] ?? ''));

        if ($className === '' || $fullyQualifiedClassName === '' || $targetNamespace === '' || $topicSlug === '') {
            throw new RuntimeException('V3 scaffold preview is incomplete. Re-run prompt generation first.');
        }

        $sourceTopic = trim((string) ($source['topic'] ?? ''));
        $sourceLabel = trim((string) ($source['source_label'] ?? $source['source_type'] ?? 'Manual topic'));
        $sourceName = trim((string) ($source['title'] ?? $sourceTopic));
        $sourceName = $sourceName !== '' ? $sourceName : Str::headline(str_replace('_', ' ', $topicSlug));
        $sourceKey = $this->sourceKey($source, $topicSlug);
        $topicTagKey = $topicSlug !== '' ? $topicSlug : 'topic_scaffold';
        $savedTestSlug = $this->savedTestSlug($topicSlug, $targetNamespace);
        $levels = array_keys($distribution);
        $totalQuestions = (int) ($generated['total_questions'] ?? array_sum($distribution));

        $definition = [
            'schema_version' => 1,
            'seeder' => [
                'class' => $fullyQualifiedClassName,
                'uuid_namespace' => $className,
            ],
            'defaults' => [
                'default_locale' => 'uk',
                'flag' => 0,
                'type' => 0,
                'level_difficulty' => [
                    'A1' => 1,
                    'A2' => 2,
                    'B1' => 3,
                    'B2' => 4,
                    'C1' => 5,
                    'C2' => 5,
                ],
            ],
            'category' => [
                'name' => $sourceTopic !== '' ? $sourceTopic : $sourceName,
            ],
            'sources' => [
                $sourceKey => [
                    'name' => $sourceName,
                ],
            ],
            'tags' => [
                $topicTagKey => [
                    'name' => $sourceTopic !== '' ? $sourceTopic : $sourceName,
                    'category' => 'topic',
                ],
            ],
            'default_tag_keys' => [$topicTagKey],
            'questions' => [],
            'saved_test' => array_filter([
                'uuid' => $this->savedTestUuid($savedTestSlug),
                'slug' => $savedTestSlug,
                'name' => ($sourceTopic !== '' ? $sourceTopic : $sourceName) . ' V3 Questions Test',
                'description' => sprintf(
                    'Scaffold saved test for %s (%s). Fill questions and ordered links before seeding.',
                    $sourceTopic !== '' ? $sourceTopic : $sourceName,
                    $sourceLabel
                ),
                'question_uuids' => [],
                'filters' => array_filter([
                    'num_questions' => $totalQuestions,
                    'levels' => $levels,
                    'level_distribution' => $distribution,
                    'seeder_classes' => [$fullyQualifiedClassName],
                    'prompt_generator' => $this->theoryPagePromptGenerator($source),
                ], static fn (mixed $value): bool => $value !== null),
            ], static fn (mixed $value): bool => $value !== null),
        ];

        return [
            $this->previewPath($preview, 'seeder_absolute_path', 'seeder_relative_path')
                => $this->loaderStubContents($className),
            $this->previewPath($preview, 'real_seeder_absolute_path', 'real_seeder_relative_path')
                => $this->realSeederContents($targetNamespace, $className),
            $this->previewPath($preview, 'definition_absolute_path', 'definition_relative_path')
                => $this->encodeJson($definition),
            $this->previewPath($preview, 'localization_uk_absolute_path', 'localization_uk_relative_path')
                => $this->localizationContents($targetNamespace, $className, $fullyQualifiedClassName, 'uk'),
            $this->previewPath($preview, 'localization_en_absolute_path', 'localization_en_relative_path')
                => $this->localizationContents($targetNamespace, $className, $fullyQualifiedClassName, 'en'),
            $this->previewPath($preview, 'localization_pl_absolute_path', 'localization_pl_relative_path')
                => $this->localizationContents($targetNamespace, $className, $fullyQualifiedClassName, 'pl'),
        ];
    }

    /**
     * @param  array<string, mixed>  $preview
     */
    private function previewPath(array $preview, string $absoluteKey, string $relativeKey): string
    {
        $absolutePath = trim((string) ($preview[$absoluteKey] ?? ''));

        if ($absolutePath !== '') {
            return $absolutePath;
        }

        $relativePath = trim((string) ($preview[$relativeKey] ?? ''));

        if ($relativePath !== '') {
            return $this->absolutePath($relativePath);
        }

        throw new RuntimeException(sprintf('V3 scaffold preview is missing `%s`.', $relativeKey));
    }

    /**
     * @param  array<string, mixed>  $source
     * @return array<string, mixed>|null
     */
    private function theoryPagePromptGenerator(array $source): ?array
    {
        if (($source['source_type'] ?? null) !== 'theory_page' || empty($source['id'])) {
            return null;
        }

        return [
            'source_type' => 'theory_page',
            'theory_page_id' => (int) $source['id'],
            'theory_page_ids' => [(int) $source['id']],
            'theory_page' => [
                'id' => (int) $source['id'],
                'slug' => (string) ($source['slug'] ?? ''),
                'title' => (string) ($source['title'] ?? ''),
                'category_slug_path' => (string) ($source['category_slug_path'] ?? ''),
                'page_seeder_class' => (string) ($source['page_seeder_class'] ?? ''),
                'url' => (string) ($source['url'] ?? ''),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $source
     */
    private function sourceKey(array $source, string $topicSlug): string
    {
        return match ($source['source_type'] ?? null) {
            'theory_page' => 'theory_page_' . Str::snake((string) ($source['slug'] ?? $topicSlug)),
            'external_url' => 'external_url_' . Str::snake((string) ($source['topic'] ?? $topicSlug)),
            default => 'manual_topic_' . Str::snake($topicSlug),
        };
    }

    private function savedTestSlug(string $topicSlug, string $targetNamespace): string
    {
        $namespaceSuffix = Str::snake(str_replace('\\', ' ', $targetNamespace));

        return trim($topicSlug . '_' . $namespaceSuffix, '_');
    }

    private function savedTestUuid(string $savedTestSlug): string
    {
        $candidate = Str::slug($savedTestSlug . '-saved-test');

        if (strlen($candidate) <= 36) {
            return $candidate;
        }

        $hash = substr(sha1($savedTestSlug), 0, 6);
        $base = substr($candidate, 0, max(1, 36 - 7));

        return rtrim($base, '-') . '-' . $hash;
    }

    private function loaderStubContents(string $className): string
    {
        return <<<PHP
<?php

require_once __DIR__ . '/{$className}/{$className}.php';
PHP;
    }

    private function realSeederContents(string $targetNamespace, string $className): string
    {
        return <<<PHP
<?php

namespace Database\Seeders\V3\\{$targetNamespace};

use App\Support\Database\JsonTestSeeder;

class {$className} extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
PHP;
    }

    private function localizationContents(
        string $targetNamespace,
        string $className,
        string $targetSeederClass,
        string $locale,
    ): string {
        $localeNamespace = Str::ucfirst(strtolower($locale));
        $localizationClass = Str::replaceLast('Seeder', 'LocalizationSeeder', $className);
        $localizationNamespace = trim($targetNamespace, '\\');
        $localizationFqcn = 'Database\\Seeders\\V3\\Localizations\\' . $localeNamespace . '\\'
            . ($localizationNamespace !== '' ? $localizationNamespace . '\\' : '')
            . $localizationClass;

        return $this->encodeJson([
            'schema_version' => 1,
            'seeder' => [
                'class' => $localizationFqcn,
            ],
            'target' => [
                'seeder_class' => $targetSeederClass,
                'definition_path' => '../definition.json',
            ],
            'locale' => strtolower($locale),
            'hint_provider' => 'chatgpt',
            'questions' => [],
        ]);
    }
}
