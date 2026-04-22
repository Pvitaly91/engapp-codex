<?php

namespace App\Services;

use App\Models\Page;
use App\Support\CodexPromptEnvelopeFormatter;
use App\Support\Database\JsonPageDefinitionIndex;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class PolyglotV3PromptGeneratorService
{
    private const DEFAULT_PROMPT_ID_PREFIX = 'GLZ-PROMPT-';

    private const CANONICAL_PUBLIC_BASE_URL = 'https://gramlyze.com';

    private const LEVEL_DIFFICULTY = [
        'A1' => 1,
        'A2' => 2,
        'B1' => 3,
        'B2' => 4,
        'C1' => 5,
        'C2' => 5,
    ];

    private const REFERENCE_LESSON_BASE_NAMES = [
        'PolyglotToBeLessonSeeder',
        'PolyglotThereIsThereAreLessonSeeder',
    ];

    /**
     * @var array<string, mixed>|null
     */
    private ?array $pageDefinitionIndexCache = null;

    /**
     * @var array<int, array<string, mixed>>|null
     */
    private ?array $referenceLessonsCache = null;

    public function __construct(
        private JsonPageDefinitionIndex $pageDefinitionIndex,
        private CodexPromptEnvelopeFormatter $codexPromptEnvelopeFormatter,
    ) {
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function generate(array $input): array
    {
        $normalized = $this->normalizeInput($input);
        $referenceLessons = $this->referenceLessons();
        $theoryContext = $this->resolveTheoryContext(
            $normalized['theory_category_slug'],
            $normalized['theory_page_slug']
        );
        $targetPaths = $this->buildTargetPaths($normalized['seeder_class_base_name']);
        $promptId = $normalized['prompt_id'] ?? $this->generatePromptId($normalized, $theoryContext);
        $summary = $this->buildSummary($normalized, $theoryContext);
        $promptText = $this->buildPromptText(
            $promptId,
            $summary,
            $normalized,
            $theoryContext,
            $targetPaths,
            $referenceLessons,
        );

        return [
            'prompt_id' => $promptId,
            'prompt_text' => $promptText,
            'summary' => $summary,
            'meta' => [
                'lesson_slug' => $normalized['lesson_slug'],
                'lesson_order' => $normalized['lesson_order'],
                'lesson_title' => $normalized['lesson_title'],
                'topic' => $normalized['topic'],
                'course_slug' => $normalized['course_slug'],
                'level' => $normalized['level'],
                'items_count' => $normalized['items_count'],
                'previous_lesson_slug' => $normalized['previous_lesson_slug'],
                'next_lesson_slug' => $normalized['next_lesson_slug'],
                'seeder_class_base_name' => $normalized['seeder_class_base_name'],
            ],
            'input' => $normalized,
            'theory_context' => $theoryContext,
            'target_paths' => $targetPaths,
            'reference_lessons' => $referenceLessons,
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function normalizeInput(array $input): array
    {
        $theoryCategorySlug = $this->normalizeSlug($input['theory_category_slug'] ?? null, 'theory category slug');
        $theoryPageSlug = $this->normalizeSlug($input['theory_page_slug'] ?? null, 'theory page slug');
        $lessonSlug = $this->normalizeSlug($input['lesson_slug'] ?? null, 'lesson slug');
        $lessonOrder = (int) ($input['lesson_order'] ?? 0);
        $itemsCount = (int) ($input['items_count'] ?? 24);

        if ($lessonOrder <= 0) {
            throw new RuntimeException('Lesson order must be a positive integer.');
        }

        if ($itemsCount <= 0) {
            throw new RuntimeException('Items count must be a positive integer.');
        }

        $lessonTitle = trim((string) ($input['lesson_title'] ?? $input['title'] ?? ''));
        if ($lessonTitle === '') {
            $lessonTitle = Str::of($lessonSlug)->replace('-', ' ')->headline()->toString();
        }

        $topic = trim((string) ($input['topic'] ?? ''));
        if ($topic === '') {
            $topic = $lessonTitle;
        }

        $level = strtoupper(trim((string) ($input['level'] ?? 'A1')));
        if ($level === '') {
            $level = 'A1';
        }

        $courseSlug = $this->normalizeSlug($input['course_slug'] ?? 'polyglot-english-a1', 'course slug');
        $seederClassBaseName = trim((string) ($input['seeder_class_base_name'] ?? $input['seeder'] ?? ''));
        if ($seederClassBaseName === '') {
            $seederClassBaseName = Str::studly($lessonSlug);
        }

        if (! Str::endsWith($seederClassBaseName, 'Seeder')) {
            $seederClassBaseName .= 'Seeder';
        }

        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $seederClassBaseName) !== 1) {
            throw new RuntimeException('Seeder class base name must be a valid PHP class basename.');
        }

        $promptId = $this->normalizePromptId($input['prompt_id'] ?? null);

        return [
            'theory_category_slug' => $theoryCategorySlug,
            'theory_page_slug' => $theoryPageSlug,
            'lesson_slug' => $lessonSlug,
            'lesson_order' => $lessonOrder,
            'lesson_title' => $lessonTitle,
            'topic' => $topic,
            'level' => $level,
            'course_slug' => $courseSlug,
            'course_name' => Str::of($courseSlug)->replace('-', ' ')->headline()->toString(),
            'previous_lesson_slug' => $this->normalizeOptionalSlug($input['previous_lesson_slug'] ?? $input['previous'] ?? null),
            'next_lesson_slug' => $this->normalizeOptionalSlug($input['next_lesson_slug'] ?? $input['next'] ?? null),
            'items_count' => $itemsCount,
            'prompt_id' => $promptId,
            'seeder_class_base_name' => $seederClassBaseName,
            'seeder_class' => 'Database\\Seeders\\V3\\Polyglot\\' . $seederClassBaseName,
        ];
    }

    private function normalizeSlug(mixed $value, string $label): string
    {
        $normalized = trim((string) $value);

        if ($normalized === '') {
            throw new RuntimeException(sprintf('%s is required.', ucfirst($label)));
        }

        $slug = Str::slug($normalized);

        if ($slug === '') {
            throw new RuntimeException(sprintf('%s must contain at least one valid slug segment.', ucfirst($label)));
        }

        return $slug;
    }

    private function normalizeOptionalSlug(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        if ($normalized === '') {
            return null;
        }

        $slug = Str::slug($normalized);

        return $slug !== '' ? $slug : null;
    }

    private function normalizePromptId(mixed $value): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        if ($normalized === '') {
            return null;
        }

        return strtoupper($normalized);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function referenceLessons(): array
    {
        if ($this->referenceLessonsCache !== null) {
            return $this->referenceLessonsCache;
        }

        $this->referenceLessonsCache = array_map(function (string $baseName) {
            $definitionRelativePath = sprintf('database/seeders/V3/Polyglot/%s/definition.json', $baseName);
            $definition = $this->loadJsonFromRelativePath($definitionRelativePath);
            $filters = Arr::get($definition, 'saved_test.filters', []);
            $theoryBinding = Arr::get($filters, 'prompt_generator.theory_page', []);
            $slug = trim((string) Arr::get($definition, 'saved_test.slug', ''));
            $composePath = $slug !== ''
                ? localized_route('test.step-compose', $slug, false, 'uk')
                : null;

            return [
                'base_name' => $baseName,
                'seeder_class' => trim((string) Arr::get($definition, 'seeder.class', '')),
                'loader_relative_path' => sprintf('database/seeders/V3/Polyglot/%s.php', $baseName),
                'real_seeder_relative_path' => sprintf('database/seeders/V3/Polyglot/%s/%s.php', $baseName, $baseName),
                'definition_relative_path' => $definitionRelativePath,
                'localization_relative_paths' => [
                    sprintf('database/seeders/V3/Polyglot/%s/localizations/uk.json', $baseName),
                    sprintf('database/seeders/V3/Polyglot/%s/localizations/en.json', $baseName),
                    sprintf('database/seeders/V3/Polyglot/%s/localizations/pl.json', $baseName),
                ],
                'lesson_slug' => $slug,
                'lesson_order' => (int) Arr::get($filters, 'lesson_order', 0),
                'course_slug' => trim((string) Arr::get($filters, 'course_slug', '')),
                'test_name' => trim((string) Arr::get($definition, 'saved_test.name', '')),
                'topic' => trim((string) Arr::get($filters, 'topic', '')),
                'theory_binding' => is_array($theoryBinding) ? $theoryBinding : [],
                'compose_route_path' => $composePath,
            ];
        }, self::REFERENCE_LESSON_BASE_NAMES);

        return $this->referenceLessonsCache;
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveTheoryContext(string $categorySlug, string $pageSlug): array
    {
        $index = $this->pageDefinitionIndex();
        $normalizedPageSlug = Str::lower(trim($pageSlug));
        $normalizedCategorySlug = Str::lower(trim($categorySlug));

        $matches = array_values(array_filter(
            $index['pages'],
            function (array $page) use ($normalizedCategorySlug, $normalizedPageSlug) {
                if ($page['page_slug'] !== $normalizedPageSlug) {
                    return false;
                }

                if ($page['route_category_slug'] === $normalizedCategorySlug) {
                    return true;
                }

                if ($page['category_slug_path'] === $normalizedCategorySlug) {
                    return true;
                }

                return Str::endsWith($page['category_slug_path'], '/' . $normalizedCategorySlug);
            }
        ));

        if ($matches === []) {
            throw new RuntimeException(sprintf(
                'Theory page [%s/%s] was not found in database/seeders/Page_V3.',
                $categorySlug,
                $pageSlug
            ));
        }

        $resolved = $matches[0];
        $databasePageId = $this->resolveDatabasePageId(
            $resolved['route_category_slug'],
            $resolved['page_slug']
        );
        $routePath = localized_route('theory.show', [
            'category' => $resolved['route_category_slug'],
            'pageSlug' => $resolved['page_slug'],
        ], false, 'uk');
        $routeUrl = $this->buildCanonicalUrl($routePath);
        $bindingPayload = [
            'source_type' => 'theory_page',
            'theory_page' => [
                'slug' => $resolved['page_slug'],
                'title' => $resolved['page_title'],
                'category_slug_path' => $resolved['category_slug_path'],
                'page_seeder_class' => $resolved['page_seeder_class'],
                'url' => $routeUrl,
            ],
        ];

        return array_merge($resolved, [
            'database_page_id' => $databasePageId,
            'route_name' => 'theory.show',
            'route_path' => $routePath,
            'route_url' => $routeUrl,
            'prompt_generator' => $bindingPayload,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function pageDefinitionIndex(): array
    {
        if ($this->pageDefinitionIndexCache !== null) {
            return $this->pageDefinitionIndexCache;
        }

        $categories = [];
        $pages = [];
        $root = database_path('seeders/Page_V3');

        if (! File::isDirectory($root)) {
            throw new RuntimeException('database/seeders/Page_V3 directory was not found.');
        }

        foreach (File::allFiles($root) as $file) {
            if (Str::lower($file->getFilename()) !== 'definition.json') {
                continue;
            }

            $relativePath = $this->toRelativeProjectPath($file->getPathname());

            if (str_contains($relativePath, '/localizations/')) {
                continue;
            }

            $definition = json_decode(File::get($file->getPathname()), true, 512, JSON_THROW_ON_ERROR);

            if (! is_array($definition)) {
                continue;
            }

            $contentType = $this->pageDefinitionIndex->resolveContentType($definition);
            $config = $this->pageDefinitionIndex->resolveContentConfig($definition);
            $seederClass = trim((string) $this->pageDefinitionIndex->resolveSeederClassName($definition));
            $slug = Str::lower(trim((string) ($definition['slug'] ?? $config['slug'] ?? '')));

            if ($slug === '') {
                continue;
            }

            if ($contentType === 'category') {
                $categories[$slug] = [
                    'slug' => $slug,
                    'title' => trim((string) Arr::get(
                        $definition,
                        'category.title',
                        Arr::get($config, 'category_title', Arr::get($config, 'title', $slug))
                    )),
                    'parent_slug' => Str::lower(trim((string) Arr::get($definition, 'category.parent_slug', ''))),
                    'seeder_class' => $seederClass,
                    'definition_relative_path' => $relativePath,
                ];

                continue;
            }

            $pageType = trim((string) ($definition['type'] ?? Arr::get($config, 'type', '')));
            if ($pageType !== 'theory') {
                continue;
            }

            $categorySlug = Str::lower(trim((string) Arr::get($config, 'category.slug', Arr::get($definition, 'category.slug', ''))));

            if ($categorySlug === '') {
                continue;
            }

            $pages[] = [
                'page_slug' => $slug,
                'page_title' => trim((string) ($config['title'] ?? $slug)),
                'page_seeder_class' => $seederClass,
                'page_definition_relative_path' => $relativePath,
                'page_php_relative_path' => dirname($relativePath) . '/' . class_basename($seederClass) . '.php',
                'route_category_slug' => $categorySlug,
            ];
        }

        $pages = array_map(function (array $page) use ($categories) {
            $categorySlugPath = $this->categoryPathValues($page['route_category_slug'], $categories, 'slug');
            $categoryTitlePath = $this->categoryPathValues($page['route_category_slug'], $categories, 'title');
            $categoryDefinitionRelativePaths = $this->categoryPathValues(
                $page['route_category_slug'],
                $categories,
                'definition_relative_path'
            );
            $categorySeederClasses = $this->categoryPathValues(
                $page['route_category_slug'],
                $categories,
                'seeder_class'
            );

            return array_merge($page, [
                'category_slug_path' => implode('/', array_filter($categorySlugPath)),
                'category_title_path' => implode(' / ', array_filter($categoryTitlePath)),
                'category_definition_relative_paths' => array_values(array_filter($categoryDefinitionRelativePaths)),
                'category_seeder_classes' => array_values(array_filter($categorySeederClasses)),
            ]);
        }, $pages);

        return $this->pageDefinitionIndexCache = [
            'categories' => $categories,
            'pages' => $pages,
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $categories
     * @return array<int, string>
     */
    private function categoryPathValues(string $slug, array $categories, string $field): array
    {
        $values = [];
        $current = Str::lower(trim($slug));
        $depth = 0;
        $visited = [];

        while ($current !== '' && $depth < 10 && ! isset($visited[$current])) {
            $visited[$current] = true;
            $record = $categories[$current] ?? null;

            if (! is_array($record)) {
                array_unshift($values, $current);
                break;
            }

            $value = trim((string) ($record[$field] ?? ''));
            array_unshift($values, $value !== '' ? $value : $current);

            $current = Str::lower(trim((string) ($record['parent_slug'] ?? '')));
            $depth++;
        }

        return array_values(array_filter($values));
    }

    private function resolveDatabasePageId(string $categorySlug, string $pageSlug): ?int
    {
        try {
            if (! Schema::hasTable('pages') || ! Schema::hasTable('page_categories')) {
                return null;
            }

            return Page::query()
                ->forType('theory')
                ->where('slug', $pageSlug)
                ->whereHas('category', fn ($query) => $query->where('slug', $categorySlug))
                ->value('id');
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return array<string, string>
     */
    private function buildTargetPaths(string $seederClassBaseName): array
    {
        $loaderRelativePath = sprintf('database/seeders/V3/Polyglot/%s.php', $seederClassBaseName);
        $packageRelativePath = sprintf('database/seeders/V3/Polyglot/%s', $seederClassBaseName);
        $realSeederRelativePath = $packageRelativePath . '/' . $seederClassBaseName . '.php';
        $definitionRelativePath = $packageRelativePath . '/definition.json';
        $ukRelativePath = $packageRelativePath . '/localizations/uk.json';
        $enRelativePath = $packageRelativePath . '/localizations/en.json';
        $plRelativePath = $packageRelativePath . '/localizations/pl.json';
        $v2BridgeRelativePath = sprintf('database/seeders/V2/Polyglot/%s.php', $seederClassBaseName);

        return [
            'loader_relative_path' => $loaderRelativePath,
            'loader_absolute_path' => base_path($loaderRelativePath),
            'package_relative_path' => $packageRelativePath,
            'package_absolute_path' => base_path($packageRelativePath),
            'real_seeder_relative_path' => $realSeederRelativePath,
            'real_seeder_absolute_path' => base_path($realSeederRelativePath),
            'definition_relative_path' => $definitionRelativePath,
            'definition_absolute_path' => base_path($definitionRelativePath),
            'uk_relative_path' => $ukRelativePath,
            'uk_absolute_path' => base_path($ukRelativePath),
            'en_relative_path' => $enRelativePath,
            'en_absolute_path' => base_path($enRelativePath),
            'pl_relative_path' => $plRelativePath,
            'pl_absolute_path' => base_path($plRelativePath),
            'v2_bridge_relative_path' => $v2BridgeRelativePath,
            'v2_bridge_absolute_path' => base_path($v2BridgeRelativePath),
        ];
    }

    /**
     * @param  array<string, mixed>  $normalized
     * @param  array<string, mixed>  $theoryContext
     * @return array<string, string>
     */
    private function buildSummary(array $normalized, array $theoryContext): array
    {
        return [
            'goal' => sprintf(
                'Створити канонічний V3 Polyglot lesson package для theory page "%s" у курсі %s.',
                $theoryContext['page_title'],
                $normalized['course_slug']
            ),
            'work' => sprintf(
                'Згенерувати lesson package %s (%s, %d items) з V3 seeder package, definition.json, localizations/en|pl|uk, theory binding metadata і тестами.',
                $normalized['lesson_slug'],
                $normalized['level'],
                $normalized['items_count']
            ),
            'constraints' => 'Не додавати нові таблиці, не ламати compose/course/theory runtime, тримати database/seeders/V3/Polyglot як canonical source of truth і використовувати чинний prompt_generator.theory_page contract.',
            'result' => sprintf(
                'Готовий до seeding V3 Polyglot package, прив’язаний до %s (%s) і сумісний з tests by topic, course landing та duplicate-token compose payload.',
                $theoryContext['route_path'],
                $theoryContext['page_seeder_class']
            ),
        ];
    }

    /**
     * @param  array<string, mixed>  $normalized
     * @param  array<string, mixed>  $theoryContext
     * @param  array<string, string>  $targetPaths
     * @param  array<int, array<string, mixed>>  $referenceLessons
     * @param  array<string, string>  $summary
     */
    private function buildPromptText(
        string $promptId,
        array $summary,
        array $normalized,
        array $theoryContext,
        array $targetPaths,
        array $referenceLessons,
    ): string {
        $promptIdLine = $this->formatPromptIdLine($promptId);

        $formatSection = [
            'FORMAT OF YOUR RESPONSE — REQUIRED',
            'У фінальній відповіді ТИ ОБОВ’ЯЗКОВО маєш:',
            '1. Першим рядком вивести:',
            '   ' . $promptIdLine,
            '2. Одразу після цього вивести:',
            '   Codex Summary (Top):',
            '   ' . $promptIdLine,
            '   - Мета:',
            '   - Що саме зробити:',
            '   - Ключові обмеження / адаптації:',
            '   - Підсумковий результат:',
            '3. Потім вивести основний результат зі списком змінених файлів, поясненням і командами.',
            '4. Перед останнім рядком ще раз вивести:',
            '   Codex Summary (Bottom):',
            '   ' . $promptIdLine,
            '   - Мета:',
            '   - Що саме зробити:',
            '   - Ключові обмеження / адаптації:',
            '   - Підсумковий результат:',
            '5. Останнім рядком знову вивести:',
            '   ' . $promptIdLine,
            '',
            'Не пропускай цей формат.',
        ];

        $contextSection = [
            'CONTEXT',
            'У проєкті вже є:',
            '- canonical V3 Polyglot lessons у `database/seeders/V3/Polyglot`:',
        ];

        foreach ($referenceLessons as $referenceLesson) {
            $contextSection[] = sprintf(
                '  %s -> slug `%s`, lesson_order %d, compose route `%s`.',
                $referenceLesson['base_name'],
                $referenceLesson['lesson_slug'],
                $referenceLesson['lesson_order'],
                $referenceLesson['compose_route_path']
            );
        }

        $contextSection = array_merge($contextSection, [
            '- current routes already working:',
            '  ' . localized_route('courses.show', 'polyglot-english-a1', false, 'uk'),
            '  ' . ($referenceLessons[0]['compose_route_path'] ?? '/test/polyglot-to-be-a1/step/compose'),
            '  ' . ($referenceLessons[1]['compose_route_path'] ?? '/test/polyglot-there-is-there-are-a1/step/compose'),
            '- selected theory page resolved from real `database/seeders/Page_V3` definitions:',
            sprintf('  category slug: `%s`', $theoryContext['route_category_slug']),
            sprintf('  category slug path: `%s`', $theoryContext['category_slug_path']),
            sprintf('  theory page slug: `%s`', $theoryContext['page_slug']),
            sprintf('  theory page title: `%s`', $theoryContext['page_title']),
            sprintf('  theory route: `%s`', $theoryContext['route_path']),
            sprintf('  page seeder class: `%s`', $theoryContext['page_seeder_class']),
            sprintf('  page definition: `%s`', $theoryContext['page_definition_relative_path']),
            '- canonical theory binding metadata already accepted by current direct Polyglot lessons:',
            '  `saved_test.filters.prompt_generator.source_type = theory_page`',
            sprintf('  `saved_test.filters.prompt_generator.theory_page = %s`', $this->jsonInline($theoryContext['prompt_generator']['theory_page'] ?? [])),
            '- `database/seeders/V2/Polyglot/*` are thin bridges only; `database/seeders/V3/Polyglot/*` remains the canonical source of truth.',
        ]);

        $filesToInspect = [
            'FILES TO INSPECT',
            '- V3 Polyglot canonical references:',
        ];

        foreach ($referenceLessons as $referenceLesson) {
            $filesToInspect[] = sprintf('  `%s`', $referenceLesson['loader_relative_path']);
            $filesToInspect[] = sprintf('  `%s`', $referenceLesson['real_seeder_relative_path']);
            $filesToInspect[] = sprintf('  `%s`', $referenceLesson['definition_relative_path']);
            foreach ($referenceLesson['localization_relative_paths'] as $localizationPath) {
                $filesToInspect[] = sprintf('  `%s`', $localizationPath);
            }
        }

        $filesToInspect = array_merge($filesToInspect, [
            '- V2 Polyglot thin bridges (historical compatibility only):',
            '  `database/seeders/V2/Polyglot/PolyglotToBeLessonSeeder.php`',
            '  `database/seeders/V2/Polyglot/PolyglotThereIsThereAreLessonSeeder.php`',
            '- Theory page definitions and category chain for the selected lesson:',
            sprintf('  `%s`', $theoryContext['page_php_relative_path']),
            sprintf('  `%s`', $theoryContext['page_definition_relative_path']),
        ]);

        foreach ($theoryContext['category_definition_relative_paths'] as $categoryDefinitionPath) {
            $filesToInspect[] = sprintf('  `%s`', $categoryDefinitionPath);
        }

        $filesToInspect = array_merge($filesToInspect, [
            '- Runtime / binding / CLI source of truth:',
            '  `app/Support/Database/JsonTestSeeder.php`',
            '  `app/Services/PolyglotLessonImportService.php`',
            '  `app/Services/QuestionMetaSyncService.php`',
            '  `app/Services/PolyglotCourseManifestService.php`',
            '  `app/Services/TheoryPagePromptLinkedTestsService.php`',
            '  `app/Http/Controllers/PageController.php`',
            '  `app/Http/Controllers/TheoryController.php`',
            '  `docs/prompts/polyglot-v3-lesson-generator.md`',
            '- Current tests that must stay green:',
            '  `tests/Feature/PolyglotV3SeedersTest.php`',
            '  `tests/Feature/PolyglotTheoryPageTest.php`',
            '  `tests/Feature/PolyglotCourseLandingPageTest.php`',
            '  `tests/Feature/PolyglotComposeModeTest.php`',
            '  `tests/Unit/ComposePayloadBuilderTest.php`',
        ]);

        $requiredOutputArtifacts = [
            'REQUIRED OUTPUT ARTIFACTS',
            '- Top-level V3 loader stub: `' . $targetPaths['loader_relative_path'] . '`',
            '- Real V3 seeder class: `' . $targetPaths['real_seeder_relative_path'] . '`',
            '- V3 definition: `' . $targetPaths['definition_relative_path'] . '`',
            '- Locale JSON files:',
            '  `' . $targetPaths['uk_relative_path'] . '`',
            '  `' . $targetPaths['en_relative_path'] . '`',
            '  `' . $targetPaths['pl_relative_path'] . '`',
            '- Polyglot theory binding metadata inside `saved_test.filters.prompt_generator`.',
            '- Tests covering the new lesson package and no-regression for current Polyglot runtime.',
            '- Thin V2 bridge only if the real repo flow requires one; do not treat V2 as the canonical path.',
        ];

        $theoryBindingRequirements = [
            'THEORY BINDING REQUIREMENTS',
            '- Use the current accepted direct-link shape already present in canonical Polyglot V3 lessons.',
            '- Do not invent a second theory-link system.',
            '- Match the selected theory route and seeder class exactly:',
            sprintf('  theory route: `%s`', $theoryContext['route_path']),
            sprintf('  theory page seeder: `%s`', $theoryContext['page_seeder_class']),
            '- Persist this payload under `saved_test.filters.prompt_generator`:',
            '```json',
            $this->jsonPretty($theoryContext['prompt_generator']),
            '```',
            '- The new lesson must stay visible in the theory-page "tests by topic" block through the existing `TheoryPagePromptLinkedTestsService` contract.',
        ];

        $lessonJsonContract = [
            'LESSON JSON CONTRACT',
            '- When drafting sentence items before mapping them into `definition.json`, use the real Polyglot import/source contract already enforced by `PolyglotLessonSchemaValidator` and `PolyglotLessonImportService`.',
            '- Every authored item must carry:',
            '  `source_text_uk`',
            '  `target_text`',
            '  `tokens_correct`',
            '  `distractors`',
            '  `hint_uk`',
            '  `grammar_tags`',
            '  optional `distractor_explanations_uk`',
            '- Compose questions must remain compatible with `question_type = 4` / `compose_tokens`.',
            '- Duplicate token support must remain compatible with the current compose payload builder: preserve duplicates in the ordered correct sequence, even if the token bank deduplicates text values.',
            sprintf('- Generate exactly `%d` sentence items for lesson `%s` unless the real repo contract forces a different count.', $normalized['items_count'], $normalized['lesson_slug']),
        ];

        $localeRequirements = [
            'V3 LOCALE JSON REQUIREMENTS',
            '- Follow the package-local V3 localization pattern already used in `database/seeders/V3/Polyglot/*/localizations/*.json`.',
            '- Do not invent a new locale file layout.',
            '- In the current repo convention, localized hints and distractor explanations live in `localizations/*.json`.',
            '- `saved_test.name`, `saved_test.description`, routing metadata and structural lesson metadata stay in `definition.json`, not in locale JSON.',
            '- `uk.json` is the authoritative authored file for Polyglot hints/explanations; `en.json` and `pl.json` may stay with empty `questions` arrays if translated feedback is not authored yet.',
            '- Keep `target.definition_path = ../definition.json` and `target.seeder_class` aligned with the Polyglot V3 seeder class.',
        ];

        $acceptanceCriteria = [
            'ACCEPTANCE CRITERIA',
            sprintf('- The lesson slug is `%s` and lesson order is `%d`.', $normalized['lesson_slug'], $normalized['lesson_order']),
            sprintf('- The V3 seeder class is `%s` and the files live under `%s`.', $normalized['seeder_class'], $targetPaths['package_relative_path']),
            sprintf('- The selected theory page `%s` is linked through the existing prompt_generator metadata contract.', $theoryContext['page_title']),
            '- No new database tables are introduced.',
            '- Existing compose runtime, course flow, theory rendering and tests-by-topic flow stay intact.',
            '- The final package follows the same V3/Polyglot file layout and localization pattern as lesson 1 and lesson 2.',
            '- The prompt output and final answer both keep the same `CODEX PROMPT ID:` line + Codex Summary at the top and bottom.',
            '- `php artisan test --filter=Polyglot`, `php artisan test --filter=ComposePayloadBuilderTest` and `php artisan view:cache` must pass.',
        ];

        $manualVerificationCommands = [
            'MANUAL VERIFICATION COMMANDS',
            '```bash',
            'composer dump-autoload',
            sprintf('php artisan db:seed --class="%s"', $normalized['seeder_class']),
            'php artisan test --filter=Polyglot',
            'php artisan test --filter=ComposePayloadBuilderTest',
            'php artisan view:cache',
            '```',
            'Open and verify:',
            sprintf('- `%s`', localized_route('courses.show', $normalized['course_slug'], false, 'uk')),
            sprintf('- `%s`', localized_route('test.step-compose', $normalized['lesson_slug'], false, 'uk')),
            sprintf('- `%s`', $theoryContext['route_path']),
        ];

        $mainGoal = [
            'MAIN GOAL',
            sprintf(
                'Створити новий Polyglot V3 lesson package `%s` (%s, order %d) для theory page `%s`.',
                $normalized['lesson_slug'],
                $normalized['level'],
                $normalized['lesson_order'],
                $theoryContext['page_slug']
            ),
            'Використовуй найменш інвазивне рішення і спирайся на реальні Polyglot V3 conventions у repo.',
            'Не викликай AI API, не роби admin UI, не ламай existing compose runtime, не додавай нову паралельну V3 схему.',
        ];

        $sections = array_merge(
            ['Ти senior Laravel/Blade engineer, який продовжує роботу в поточному репозиторії Gramlyze.'],
            [''],
            $formatSection,
            [''],
            $contextSection,
            [''],
            $filesToInspect,
            [''],
            $mainGoal,
            [''],
            $requiredOutputArtifacts,
            [''],
            $theoryBindingRequirements,
            [''],
            $lessonJsonContract,
            [''],
            $localeRequirements,
            [''],
            $acceptanceCriteria,
            [''],
            $manualVerificationCommands
        );

        return $this->codexPromptEnvelopeFormatter->wrapPrompt(
            $promptId,
            $summary,
            implode("\n", $sections)
        );
    }

    public function formatPromptIdLine(string $promptId): string
    {
        return $this->codexPromptEnvelopeFormatter->formatPromptIdLine($promptId);
    }

    /**
     * @param  array<string, string>  $summary
     */
    public function formatSummaryBlock(string $position, string $promptId, array $summary): string
    {
        return $this->codexPromptEnvelopeFormatter->formatSummaryBlock($position, $promptId, $summary);
    }

    /**
     * @param  array<string, mixed>  $normalized
     * @param  array<string, mixed>  $theoryContext
     */
    private function generatePromptId(array $normalized, array $theoryContext): string
    {
        $seed = implode('|', [
            $normalized['theory_category_slug'],
            $normalized['theory_page_slug'],
            $normalized['lesson_slug'],
            $normalized['lesson_order'],
            $normalized['lesson_title'],
            $normalized['topic'],
            $normalized['level'],
            $normalized['course_slug'],
            $normalized['previous_lesson_slug'] ?? '',
            $normalized['next_lesson_slug'] ?? '',
            $normalized['items_count'],
            $normalized['seeder_class_base_name'],
            $theoryContext['page_seeder_class'],
            $theoryContext['category_slug_path'],
        ]);

        return self::DEFAULT_PROMPT_ID_PREFIX . strtoupper(substr(sha1($seed), 0, 8));
    }

    private function buildCanonicalUrl(string $path): string
    {
        return rtrim($this->publicBaseUrl(), '/') . '/' . ltrim($path, '/');
    }

    private function publicBaseUrl(): string
    {
        foreach ($this->referenceLessons() as $referenceLesson) {
            $url = trim((string) Arr::get($referenceLesson, 'theory_binding.url', ''));

            if ($url === '') {
                continue;
            }

            $parts = parse_url($url);
            $scheme = trim((string) ($parts['scheme'] ?? ''));
            $host = trim((string) ($parts['host'] ?? ''));

            if ($scheme !== '' && $host !== '') {
                return $scheme . '://' . $host;
            }
        }

        return self::CANONICAL_PUBLIC_BASE_URL;
    }

    private function jsonPretty(mixed $payload): string
    {
        $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return is_string($encoded) ? $encoded : '{}';
    }

    private function jsonInline(mixed $payload): string
    {
        $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return is_string($encoded) ? $encoded : '{}';
    }

    private function loadJsonFromRelativePath(string $relativePath): array
    {
        $absolutePath = base_path($relativePath);

        if (! File::exists($absolutePath)) {
            throw new RuntimeException(sprintf('Expected reference file [%s] was not found.', $relativePath));
        }

        $decoded = json_decode(File::get($absolutePath), true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            throw new RuntimeException(sprintf('Expected JSON object in [%s].', $relativePath));
        }

        return $decoded;
    }

    private function toRelativeProjectPath(string $path): string
    {
        $normalizedPath = str_replace('\\', '/', $path);
        $normalizedRoot = rtrim(str_replace('\\', '/', base_path()), '/');

        return ltrim(Str::after($normalizedPath, $normalizedRoot), '/');
    }
}
