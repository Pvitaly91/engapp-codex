<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class PolyglotV3SkeletonWriterService
{
    /**
     * @param  array<string, mixed>  $generated
     * @return array<int, string>
     */
    public function plannedFiles(array $generated): array
    {
        $paths = $generated['target_paths'] ?? [];

        return array_values(array_filter([
            $paths['loader_absolute_path'] ?? null,
            $paths['real_seeder_absolute_path'] ?? null,
            $paths['definition_absolute_path'] ?? null,
            $paths['uk_absolute_path'] ?? null,
            $paths['en_absolute_path'] ?? null,
            $paths['pl_absolute_path'] ?? null,
        ]));
    }

    /**
     * @param  array<string, mixed>  $generated
     * @return array<string, mixed>
     */
    public function write(array $generated, bool $force = false): array
    {
        $files = $this->fileContents($generated);
        $existing = array_values(array_filter(array_keys($files), fn (string $path) => File::exists($path)));

        if ($existing !== [] && ! $force) {
            throw new RuntimeException(sprintf(
                'Skeleton files already exist. Re-run with --force to overwrite: %s',
                implode(', ', array_map([$this, 'relativePath'], $existing))
            ));
        }

        foreach ($files as $path => $contents) {
            File::ensureDirectoryExists(dirname($path));
            File::put($path, $contents);
        }

        return [
            'written' => array_map([$this, 'relativePath'], array_keys($files)),
            'count' => count($files),
        ];
    }

    /**
     * @param  array<string, mixed>  $generated
     * @return array<string, string>
     */
    private function fileContents(array $generated): array
    {
        $input = $generated['input'] ?? [];
        $theory = $generated['theory_context'] ?? [];
        $paths = $generated['target_paths'] ?? [];

        $seederClassBaseName = (string) ($input['seeder_class_base_name'] ?? '');
        $seederClass = (string) ($input['seeder_class'] ?? '');
        $courseName = (string) ($input['course_name'] ?? Str::of((string) ($input['course_slug'] ?? ''))->replace('-', ' ')->headline());
        $level = (string) ($input['level'] ?? 'A1');
        $topic = (string) ($input['topic'] ?? '');
        $lessonSlug = (string) ($input['lesson_slug'] ?? '');
        $lessonTitle = (string) ($input['lesson_title'] ?? '');
        $lessonOrder = (int) ($input['lesson_order'] ?? 1);
        $sourceKey = 'theory_page_' . str_replace('-', '_', (string) ($theory['page_slug'] ?? 'theory_page'));
        $topicTagKey = (string) Str::of($topic !== '' ? $topic : $lessonSlug)
            ->replace(['/', '_', '-'], ' ')
            ->squish()
            ->snake();
        $levelTagKey = strtolower($level);
        $savedTestUuid = $this->savedTestUuid($lessonSlug);

        $definition = [
            'schema_version' => 1,
            'seeder' => [
                'class' => $seederClass,
                'uuid_namespace' => $seederClassBaseName,
            ],
            'defaults' => [
                'default_locale' => 'uk',
                'flag' => 0,
                'type' => 4,
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
                'name' => $courseName,
            ],
            'sources' => [
                $sourceKey => [
                    'name' => (string) ($theory['page_title'] ?? $topic),
                ],
            ],
            'tags' => array_filter([
                'polyglot_compose_tokens' => [
                    'name' => 'Polyglot compose tokens',
                    'category' => 'mode',
                ],
                $topicTagKey !== '' ? $topicTagKey : null => [
                    'name' => $topic !== '' ? $topic : $lessonTitle,
                    'category' => 'topic',
                ],
                $levelTagKey !== '' ? $levelTagKey : null => [
                    'name' => $level,
                    'category' => 'level',
                ],
            ], fn ($value, $key) => $key !== '' && $key !== '0', ARRAY_FILTER_USE_BOTH),
            'default_tag_keys' => ['polyglot_compose_tokens'],
            'questions' => [],
            'saved_test' => [
                'uuid' => $savedTestUuid,
                'slug' => $lessonSlug,
                'name' => $lessonTitle,
                'description' => null,
                'question_uuids' => [],
                'filters' => [
                    'mode' => 'compose_tokens',
                    'question_type' => 4,
                    'lesson_type' => 'polyglot',
                    'payload_version' => 2,
                    'import_format' => 'gramlyze_v3_polyglot',
                    'supports_duplicate_tokens' => true,
                    'course_slug' => (string) ($input['course_slug'] ?? 'polyglot-english-a1'),
                    'lesson_order' => $lessonOrder,
                    'previous_lesson_slug' => $input['previous_lesson_slug'] ?? null,
                    'next_lesson_slug' => $input['next_lesson_slug'] ?? null,
                    'completion' => [
                        'rolling_window' => 100,
                        'min_rating' => 4.5,
                    ],
                    'interface_locale' => 'uk',
                    'study_locale' => 'uk',
                    'target_locale' => 'en',
                    'topic' => $topic,
                    'level' => $level,
                    'levels' => [$level],
                    'tags' => array_values(array_filter([
                        'Polyglot',
                        'Compose Tokens',
                        $topic !== '' ? Str::of($topic)->replace(['/', '_', '-'], ' ')->headline()->toString() : null,
                        $level,
                    ])),
                    'prompt_generator' => $theory['prompt_generator'] ?? [
                        'source_type' => 'theory_page',
                        'theory_page' => [],
                    ],
                ],
            ],
        ];

        return [
            (string) $paths['loader_absolute_path'] => $this->loaderStubContents($seederClassBaseName),
            (string) $paths['real_seeder_absolute_path'] => $this->realSeederContents($seederClassBaseName),
            (string) $paths['definition_absolute_path'] => $this->encodeJson($definition),
            (string) $paths['uk_absolute_path'] => $this->localizationContents($seederClassBaseName, 'uk', 'Uk'),
            (string) $paths['en_absolute_path'] => $this->localizationContents($seederClassBaseName, 'en', 'En'),
            (string) $paths['pl_absolute_path'] => $this->localizationContents($seederClassBaseName, 'pl', 'Pl'),
        ];
    }

    private function loaderStubContents(string $seederClassBaseName): string
    {
        return <<<PHP
<?php

require_once __DIR__ . '/{$seederClassBaseName}/{$seederClassBaseName}.php';
PHP;
    }

    private function realSeederContents(string $seederClassBaseName): string
    {
        return <<<PHP
<?php

namespace Database\Seeders\V3\Polyglot;

use App\Support\Database\JsonTestSeeder;

class {$seederClassBaseName} extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
PHP;
    }

    private function localizationContents(string $seederClassBaseName, string $locale, string $namespaceSegment): string
    {
        $localizationSeederBaseName = Str::of($seederClassBaseName)
            ->replaceLast('Seeder', '')
            ->append('LocalizationSeeder')
            ->toString();

        return $this->encodeJson([
            'schema_version' => 1,
            'seeder' => [
                'class' => sprintf(
                    'Database\\Seeders\\V3\\Localizations\\%s\\Polyglot\\%sLocalizationSeeder',
                    $namespaceSegment,
                    Str::beforeLast($localizationSeederBaseName, 'LocalizationSeeder')
                ),
            ],
            'target' => [
                'seeder_class' => sprintf('Database\\Seeders\\V3\\Polyglot\\%s', $seederClassBaseName),
                'definition_path' => '../definition.json',
            ],
            'locale' => $locale,
            'hint_provider' => 'polyglot-v3',
            'questions' => [],
        ]);
    }

    private function encodeJson(array $payload): string
    {
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (! is_string($json)) {
            throw new RuntimeException('Unable to encode scaffold JSON payload.');
        }

        return $json . PHP_EOL;
    }

    private function savedTestUuid(string $lessonSlug): string
    {
        $candidate = Str::slug($lessonSlug . '-saved-test');

        if (strlen($candidate) <= 36) {
            return $candidate;
        }

        $hash = substr(sha1($lessonSlug), 0, 6);
        $base = substr($candidate, 0, max(1, 36 - 7));

        return rtrim($base, '-') . '-' . $hash;
    }

    private function relativePath(string $absolutePath): string
    {
        $normalizedPath = str_replace('\\', '/', $absolutePath);
        $normalizedRoot = rtrim(str_replace('\\', '/', base_path()), '/');

        return ltrim(Str::after($normalizedPath, $normalizedRoot), '/');
    }
}
