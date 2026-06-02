<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\TextBlock;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TheoryPageTestsUnificationAuditService
{
    private const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

    /**
     * @var array<string, array<string, mixed>>|null
     */
    private ?array $manifestIndex = null;

    public function __construct(
        private TheoryPagePromptLinkedTestsService $linkedTestsService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $manifestIndex = $this->manifestIndex();
        $pages = Page::query()
            ->with('category.parent.parent.parent.parent.parent')
            ->where('type', 'theory')
            ->orderBy('id')
            ->get()
            ->filter(fn (Page $page): bool => $this->isPageV3Seeder((string) $page->seeder))
            ->values();

        $rows = $pages
            ->map(fn (Page $page): array => $this->buildPageRow($page, $manifestIndex))
            ->values()
            ->all();

        $summary = $this->summary($rows);

        return [
            'generated_at' => now()->toIso8601String(),
            'prompt_id' => 'GLZ-CODEX-THEORY-PAGE-TESTS-UNIFICATION-001',
            'summary' => $summary,
            'pages' => $rows,
            'backlog' => $this->backlog($rows),
        ];
    }

    /**
     * @return array{json_path: string, markdown_path: string, audit: array<string, mixed>}
     */
    public function write(?string $jsonPath = null, ?string $markdownPath = null): array
    {
        $audit = $this->build();
        $jsonPath ??= storage_path('app/content-audits/theory-page-tests-unification-audit.json');
        $markdownPath ??= storage_path('app/content-audits/theory-page-tests-unification-audit.md');

        File::ensureDirectoryExists(dirname($jsonPath));
        File::ensureDirectoryExists(dirname($markdownPath));
        File::put($jsonPath, json_encode($audit, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL);
        File::put($markdownPath, $this->toMarkdown($audit));

        return [
            'json_path' => $jsonPath,
            'markdown_path' => $markdownPath,
            'audit' => $audit,
        ];
    }

    /**
     * @param  array<string, mixed>  $audit
     */
    public function toMarkdown(array $audit): string
    {
        $summary = (array) ($audit['summary'] ?? []);
        $rows = array_values((array) ($audit['pages'] ?? []));
        $lines = [
            '# Theory Page Tests Unification Audit',
            '',
            '- Generated at: `' . (string) ($audit['generated_at'] ?? '') . '`',
            '- Total theory pages audited: ' . (int) ($summary['total_theory_pages'] ?? 0),
            '- Pages already OK: ' . (int) ($summary['pages_ok'] ?? 0),
            '- Pages missing only theory links: ' . (int) ($summary['pages_missing_only_theory_links'] ?? 0),
            '- Pages missing Sentence Builder: ' . (int) ($summary['pages_missing_sentence_builder'] ?? 0),
            '- Pages missing V3: ' . (int) ($summary['pages_missing_v3'] ?? 0),
            '- Pages missing both tests: ' . (int) ($summary['pages_missing_both_tests'] ?? 0),
            '',
            '| Route | Status | Has Sentence Builder | Has Mixed A1-C2 | V3 seeder | Polyglot seeder | Has theory links | Missing |',
            '|---|---|---|---|---|---|---|---|',
        ];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $lines[] = sprintf(
                '| `%s` | `%s` | %s | %s | %s | %s | %s | %s |',
                $this->escapeMarkdownCell((string) ($row['route'] ?? '')),
                $this->escapeMarkdownCell((string) ($row['status'] ?? '')),
                $this->yesNo((bool) ($row['has_sentence_builder'] ?? false)),
                $this->yesNo((bool) ($row['has_mixed_a1_c2'] ?? false)),
                $this->inlineList((array) ($row['v3_standard_seeders'] ?? [])),
                $this->inlineList((array) ($row['polyglot_seeders'] ?? [])),
                $this->yesNo((bool) ($row['has_theory_links'] ?? false)),
                $this->inlineList((array) ($row['missing'] ?? [])),
            );
        }

        $lines[] = '';
        $lines[] = '## Backlog';
        $lines[] = '';

        $backlog = (array) ($audit['backlog'] ?? []);
        if ($backlog === []) {
            $lines[] = 'No backlog items.';

            return implode(PHP_EOL, $lines) . PHP_EOL;
        }

        foreach ($backlog as $status => $items) {
            $lines[] = '### `' . $this->escapeMarkdownCell((string) $status) . '`';
            $lines[] = '';

            foreach ((array) $items as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $lines[] = sprintf(
                    '- `%s` - missing: %s',
                    $this->escapeMarkdownCell((string) ($item['route'] ?? '')),
                    $this->inlineList((array) ($item['missing'] ?? []))
                );
            }

            $lines[] = '';
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * @param  array<string, mixed>  $manifestIndex
     * @return array<string, mixed>
     */
    private function buildPageRow(Page $page, array $manifestIndex): array
    {
        $categorySlugPath = $this->categorySlugPath($page);
        $route = '/theory/' . trim($categorySlugPath . '/' . (string) $page->slug, '/');
        $manifest = $this->manifestForPage($page, $route, $manifestIndex);
        $route = $this->canonicalRouteForPage($route, $manifest);
        $tests = $this->linkedTestsService->buildForPage($page);
        $directTests = $tests
            ->filter(fn (mixed $test): bool => $test instanceof SavedGrammarTest && $this->isSentenceBuilderDirectTest($test))
            ->values();
        $mixedTest = $tests->first(fn (mixed $test): bool => $this->isMixedAllLevelsTest($test, (int) $page->id));

        $directSeederClasses = $directTests
            ->flatMap(fn (SavedGrammarTest $test): array => $this->seederClassesFromFilters($test->filters ?? []))
            ->values();
        $mixedSeederClasses = $mixedTest
            ? collect($this->seederClassesFromFilters($mixedTest->filters ?? []))
            : collect();

        $v3Seeders = $mixedSeederClasses
            ->filter(fn (string $className): bool => $this->isStandardV3SeederClass($className))
            ->unique()
            ->values()
            ->all();
        $polyglotSeeders = $mixedSeederClasses
            ->merge($directSeederClasses)
            ->filter(fn (string $className): bool => $this->isPolyglotSeederClass($className))
            ->unique()
            ->values()
            ->all();
        $allSeeders = collect($v3Seeders)->merge($polyglotSeeders)->unique()->values()->all();

        $standardCoverage = $this->questionCoverage($v3Seeders);
        $polyglotCoverage = $this->questionCoverage($polyglotSeeders);
        $allCoverage = $this->questionCoverage($allSeeders);
        $missing = $this->missingItems(
            $directTests->isNotEmpty(),
            $mixedTest !== null,
            $v3Seeders,
            $polyglotSeeders,
            $standardCoverage,
            $polyglotCoverage,
            $allCoverage
        );

        $status = $this->statusFor($missing, $directTests->isNotEmpty(), $mixedTest !== null, $standardCoverage, $polyglotCoverage);

        return [
            'page_id' => (int) $page->id,
            'category_slug' => (string) ($page->category?->slug ?? ''),
            'category_slug_path' => $categorySlugPath,
            'page_slug' => (string) $page->slug,
            'route' => $route,
            'page_seeder_class' => (string) $page->seeder,
            'text_block_count' => $this->textBlockCount($page),
            'has_sentence_builder' => $directTests->isNotEmpty(),
            'direct_sentence_builder_slugs' => $directTests->pluck('slug')->filter()->values()->all(),
            'has_mixed_a1_c2' => $mixedTest !== null,
            'mixed_a1_c2_slug' => $mixedTest?->slug,
            'v3_standard_seeders' => $v3Seeders,
            'polyglot_seeders' => $polyglotSeeders,
            'question_link_coverage' => [
                'standard_v3' => $standardCoverage,
                'polyglot' => $polyglotCoverage,
                'all' => $allCoverage,
            ],
            'has_question_theory_text_blocks' => (bool) ($allCoverage['all_questions_have_pivot_rows'] ?? false),
            'has_questions_theory_text_block_uuid' => (bool) ($allCoverage['all_questions_have_legacy_uuid'] ?? false),
            'has_theory_links' => (bool) ($allCoverage['all_questions_have_theory_links'] ?? false),
            'has_polyglot_theory_links' => (bool) ($polyglotCoverage['all_questions_have_theory_links'] ?? false),
            'has_json_theory_links_manifest' => $manifest !== null,
            'json_theory_links_manifest' => $manifest['manifest_path'] ?? null,
            'has_php_theory_links_seeder' => $manifest !== null && filled($manifest['php_seeder_class'] ?? null),
            'php_theory_links_seeder' => $manifest['php_seeder_class'] ?? null,
            'manifest_missing' => $this->manifestMissingItems($manifest),
            'manifest_expected_standard_seeders' => $manifest['expected_standard_seeders'] ?? [],
            'manifest_expected_polyglot_seeders' => $manifest['expected_polyglot_seeders'] ?? [],
            'status' => $status,
            'missing' => $missing,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, mixed>
     */
    private function summary(array $rows): array
    {
        $collection = collect($rows);

        return [
            'total_theory_pages' => $collection->count(),
            'pages_ok' => $collection->where('status', 'OK')->count(),
            'pages_missing_only_theory_links' => $collection
                ->filter(fn (array $row): bool => $this->isOnlyTheoryLinksMissing((array) ($row['missing'] ?? [])))
                ->count(),
            'pages_missing_sentence_builder' => $collection
                ->filter(fn (array $row): bool => in_array('sentence_builder', (array) ($row['missing'] ?? []), true))
                ->count(),
            'pages_missing_v3' => $collection
                ->filter(fn (array $row): bool => in_array('v3_questions', (array) ($row['missing'] ?? []), true))
                ->count(),
            'pages_missing_both_tests' => $collection
                ->filter(fn (array $row): bool => in_array('sentence_builder', (array) ($row['missing'] ?? []), true)
                    && in_array('mixed_a1_c2', (array) ($row['missing'] ?? []), true))
                ->count(),
            'status_counts' => $collection
                ->countBy('status')
                ->sortKeys()
                ->all(),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function backlog(array $rows): array
    {
        return collect($rows)
            ->reject(fn (array $row): bool => (string) ($row['status'] ?? '') === 'OK')
            ->groupBy('status')
            ->map(fn ($items) => $items
                ->map(fn (array $row): array => [
                    'route' => $row['route'],
                    'page_id' => $row['page_id'],
                    'page_seeder_class' => $row['page_seeder_class'],
                    'missing' => $row['missing'],
                    'next_action' => $this->nextActionFor((array) $row['missing']),
                ])
                ->values()
                ->all())
            ->all();
    }

    /**
     * @param  array<int, string>  $missing
     */
    private function nextActionFor(array $missing): string
    {
        if (in_array('sentence_builder', $missing, true) || in_array('polyglot_questions', $missing, true)) {
            return 'Create or attach the page-local Polyglot/Sentence Builder seeder and direct saved test.';
        }

        if (in_array('mixed_a1_c2', $missing, true) || in_array('v3_questions', $missing, true)) {
            return 'Create or attach the page-local standard V3 questions seeder so the mixed A1-C2 virtual test can include both pools.';
        }

        if ($this->hasTheoryLinkMissing($missing)) {
            return 'Create or update the JSON theory-links manifest and JsonTheoryLinksSeederBase seeder.';
        }

        return 'Review manually.';
    }

    /**
     * @param  array<int, string>  $seeders
     * @return array<string, mixed>
     */
    private function questionCoverage(array $seeders): array
    {
        $seeders = collect($seeders)->filter()->unique()->values()->all();
        if ($seeders === [] || ! Schema::hasTable('questions')) {
            return $this->emptyCoverage($seeders);
        }

        $questions = Question::query()
            ->whereIn('seeder', $seeders)
            ->get(['uuid', 'seeder', 'theory_text_block_uuid']);
        $questionUuids = $questions->pluck('uuid')->filter()->values();

        if ($questions->isEmpty() || $questionUuids->isEmpty()) {
            return $this->emptyCoverage($seeders);
        }

        $pivotRows = Schema::hasTable('question_theory_text_blocks')
            ? DB::table('question_theory_text_blocks')
                ->whereIn('question_uuid', $questionUuids->all())
                ->get(['question_uuid', 'text_block_uuid'])
            : collect();
        $linkedQuestionCount = $pivotRows->pluck('question_uuid')->unique()->count();
        $legacyQuestionCount = $questions->whereNotNull('theory_text_block_uuid')->count();
        $linkedBlockUuids = $pivotRows->pluck('text_block_uuid')->filter()->unique()->values();
        $existingLinkedBlockCount = $linkedBlockUuids->isEmpty() || ! Schema::hasTable('text_blocks')
            ? 0
            : TextBlock::query()->whereIn('uuid', $linkedBlockUuids->all())->count();
        $allBlocksExist = $linkedBlockUuids->isNotEmpty() && $existingLinkedBlockCount === $linkedBlockUuids->count();
        $allHavePivot = $linkedQuestionCount === $questions->count();
        $allHaveLegacy = $legacyQuestionCount === $questions->count();

        return [
            'seeders' => $seeders,
            'question_count' => $questions->count(),
            'question_count_by_seeder' => $questions->countBy('seeder')->all(),
            'questions_with_pivot_rows' => $linkedQuestionCount,
            'questions_with_legacy_uuid' => $legacyQuestionCount,
            'linked_text_block_uuid_count' => $linkedBlockUuids->count(),
            'linked_text_block_uuid_existing_count' => $existingLinkedBlockCount,
            'all_questions_have_pivot_rows' => $allHavePivot,
            'all_questions_have_legacy_uuid' => $allHaveLegacy,
            'all_linked_text_blocks_exist' => $allBlocksExist,
            'all_questions_have_theory_links' => $allHavePivot && $allHaveLegacy && $allBlocksExist,
        ];
    }

    /**
     * @param  array<int, string>  $seeders
     * @return array<string, mixed>
     */
    private function emptyCoverage(array $seeders): array
    {
        return [
            'seeders' => $seeders,
            'question_count' => 0,
            'question_count_by_seeder' => [],
            'questions_with_pivot_rows' => 0,
            'questions_with_legacy_uuid' => 0,
            'linked_text_block_uuid_count' => 0,
            'linked_text_block_uuid_existing_count' => 0,
            'all_questions_have_pivot_rows' => false,
            'all_questions_have_legacy_uuid' => false,
            'all_linked_text_blocks_exist' => false,
            'all_questions_have_theory_links' => false,
        ];
    }

    /**
     * @param  array<int, string>  $v3Seeders
     * @param  array<int, string>  $polyglotSeeders
     * @param  array<string, mixed>  $standardCoverage
     * @param  array<string, mixed>  $polyglotCoverage
     * @param  array<string, mixed>  $allCoverage
     * @return array<int, string>
     */
    private function missingItems(
        bool $hasSentenceBuilder,
        bool $hasMixed,
        array $v3Seeders,
        array $polyglotSeeders,
        array $standardCoverage,
        array $polyglotCoverage,
        array $allCoverage
    ): array {
        $missing = [];

        if (! $hasSentenceBuilder) {
            $missing[] = 'sentence_builder';
        }

        if (! $hasMixed) {
            $missing[] = 'mixed_a1_c2';
        }

        if ($v3Seeders === [] || (int) ($standardCoverage['question_count'] ?? 0) === 0) {
            $missing[] = 'v3_questions';
        }

        if ($polyglotSeeders === [] || (int) ($polyglotCoverage['question_count'] ?? 0) === 0) {
            $missing[] = 'polyglot_questions';
        }

        if ((int) ($standardCoverage['question_count'] ?? 0) > 0 && ! (bool) ($standardCoverage['all_questions_have_theory_links'] ?? false)) {
            $missing[] = 'theory_links';
        }

        if ((int) ($polyglotCoverage['question_count'] ?? 0) > 0 && ! (bool) ($polyglotCoverage['all_questions_have_theory_links'] ?? false)) {
            $missing[] = 'polyglot_theory_links';
        }

        if ((int) ($allCoverage['question_count'] ?? 0) > 0 && ! (bool) ($allCoverage['all_questions_have_theory_links'] ?? false)) {
            $missing[] = 'question_theory_text_blocks';
        }

        return array_values(array_unique($missing));
    }

    /**
     * @param  array<string, mixed>|null  $manifest
     * @return array<int, string>
     */
    private function manifestMissingItems(?array $manifest): array
    {
        if ($manifest === null) {
            return ['json_manifest', 'php_theory_links_seeder'];
        }

        if (! filled($manifest['php_seeder_class'] ?? null)) {
            return ['php_theory_links_seeder'];
        }

        return [];
    }

    /**
     * @param  array<int, string>  $missing
     * @param  array<string, mixed>  $standardCoverage
     * @param  array<string, mixed>  $polyglotCoverage
     */
    private function statusFor(
        array $missing,
        bool $hasSentenceBuilder,
        bool $hasMixed,
        array $standardCoverage,
        array $polyglotCoverage
    ): string {
        if ($missing === []) {
            return 'OK';
        }

        if (
            ! $hasSentenceBuilder
            && ! $hasMixed
            && (int) ($standardCoverage['question_count'] ?? 0) === 0
            && (int) ($polyglotCoverage['question_count'] ?? 0) === 0
        ) {
            return 'missing_all';
        }

        if (in_array('sentence_builder', $missing, true)) {
            return 'missing_sentence_builder';
        }

        if (in_array('mixed_a1_c2', $missing, true)) {
            return 'missing_mixed_test';
        }

        if (in_array('v3_questions', $missing, true)) {
            return 'missing_v3_questions';
        }

        if (in_array('polyglot_questions', $missing, true)) {
            return 'missing_polyglot_questions';
        }

        if (in_array('polyglot_theory_links', $missing, true)) {
            return 'missing_polyglot_theory_links';
        }

        if ($this->hasTheoryLinkMissing($missing)) {
            return 'missing_theory_links';
        }

        return 'missing_all';
    }

    /**
     * @param  array<int, string>  $missing
     */
    private function hasTheoryLinkMissing(array $missing): bool
    {
        return collect($missing)->intersect([
            'theory_links',
            'polyglot_theory_links',
            'question_theory_text_blocks',
        ])->isNotEmpty();
    }

    /**
     * @param  array<int, string>  $missing
     */
    private function isOnlyTheoryLinksMissing(array $missing): bool
    {
        if ($missing === []) {
            return false;
        }

        return collect($missing)->diff([
            'theory_links',
            'polyglot_theory_links',
            'question_theory_text_blocks',
        ])->isEmpty();
    }

    private function textBlockCount(Page $page): int
    {
        if (! Schema::hasTable('text_blocks')) {
            return 0;
        }

        return TextBlock::query()
            ->where(function ($query) use ($page): void {
                $query->where('page_id', (int) $page->id);

                $seeder = trim((string) $page->seeder);
                if ($seeder !== '') {
                    $query->orWhere('seeder', $seeder);
                }
            })
            ->count();
    }

    private function isSentenceBuilderDirectTest(SavedGrammarTest $test): bool
    {
        $filters = is_array($test->filters) ? $test->filters : [];
        $courseSlug = Str::lower(trim((string) ($filters['course_slug'] ?? '')));

        if ($courseSlug !== 'polyglot-theory-pages') {
            return false;
        }

        $mode = Str::lower(trim((string) ($filters['mode'] ?? '')));
        $questionType = (string) ($filters['question_type'] ?? '');
        $questionTypes = $filters['question_types'] ?? [];

        return $mode === 'compose_tokens'
            || $questionType === Question::TYPE_COMPOSE_TOKENS
            || (is_array($questionTypes) && in_array(Question::TYPE_COMPOSE_TOKENS, array_map('strval', $questionTypes), true));
    }

    private function isMixedAllLevelsTest(mixed $test, int $pageId): bool
    {
        if (! is_object($test) || ! method_exists($test, 'isVirtual') || ! $test->isVirtual()) {
            return false;
        }

        if ((string) ($test->slug ?? '') !== 'theory-page-' . $pageId . '-mixed-a1-c2') {
            return false;
        }

        $filters = is_array($test->filters ?? null) ? $test->filters : [];
        $levels = array_values(array_map('strval', (array) ($filters['levels'] ?? [])));

        return $levels === self::LEVELS
            && (bool) Arr::get($filters, '__meta.theory_page_mixed_all_levels_test', false);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, string>
     */
    private function seederClassesFromFilters(array $filters): array
    {
        return collect($filters['seeder_classes'] ?? [])
            ->map(fn (mixed $className): string => trim((string) $className))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function isPolyglotSeederClass(string $className): bool
    {
        return Str::contains(Str::lower($className), '\\v3\\polyglot\\');
    }

    private function isStandardV3SeederClass(string $className): bool
    {
        $normalized = Str::lower($className);

        return Str::contains($normalized, '\\v3\\')
            && ! Str::contains($normalized, '\\v3\\polyglot\\');
    }

    private function isPageV3Seeder(string $seederClass): bool
    {
        return Str::startsWith($seederClass, 'Database\\Seeders\\Page_V3\\');
    }

    private function categorySlugPath(Page $page): string
    {
        $segments = [];
        $category = $page->category;
        $depth = 0;

        while ($category && $depth < 10) {
            $slug = trim((string) $category->slug);
            if ($slug !== '') {
                array_unshift($segments, $slug);
            }

            $category = $category->parent;
            $depth++;
        }

        return implode('/', $segments);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function manifestIndex(): array
    {
        if ($this->manifestIndex !== null) {
            return $this->manifestIndex;
        }

        $phpSeedersByManifest = $this->phpSeedersByManifestBasename();
        $byPageSeeder = [];
        $byRoute = [];
        $byCategoryAndSlug = [];

        foreach (File::glob(database_path('seeders/V3/TheoryLinks/data/*-theory-links.json')) ?: [] as $path) {
            $decoded = json_decode((string) File::get($path), true);
            if (! is_array($decoded)) {
                continue;
            }

            $relativePath = $this->relativePath($path);
            $basename = basename($path);
            $entry = [
                'manifest_path' => $relativePath,
                'manifest_basename' => $basename,
                'php_seeder_class' => $phpSeedersByManifest[$basename] ?? null,
                'page_seeder_class' => (string) Arr::get($decoded, 'page.page_seeder_class', ''),
                'route' => (string) Arr::get($decoded, 'page.route', ''),
                'category_slug' => (string) Arr::get($decoded, 'page.category_slug', ''),
                'page_slug' => (string) Arr::get($decoded, 'page.slug', ''),
                'expected_standard_seeders' => array_values((array) Arr::get($decoded, 'expected_standard_seeders', Arr::get($decoded, 'tests_on_page.1.expected_standard_seeders', []))),
                'expected_polyglot_seeders' => array_values((array) Arr::get($decoded, 'expected_polyglot_seeders', Arr::get($decoded, 'tests_on_page.1.expected_polyglot_seeders', []))),
            ];

            if ($entry['page_seeder_class'] !== '') {
                $byPageSeeder[$entry['page_seeder_class']] = $entry;
            }

            if ($entry['route'] !== '') {
                $byRoute[$this->normalizeRoute($entry['route'])] = $entry;
            }

            if ($entry['category_slug'] !== '' && $entry['page_slug'] !== '') {
                $byCategoryAndSlug[$this->normalizeRoute($entry['category_slug'] . '/' . $entry['page_slug'])] = $entry;
            }
        }

        return $this->manifestIndex = [
            'by_page_seeder' => $byPageSeeder,
            'by_route' => $byRoute,
            'by_category_and_slug' => $byCategoryAndSlug,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function phpSeedersByManifestBasename(): array
    {
        $map = [];

        foreach (File::glob(database_path('seeders/V3/TheoryLinks/*.php')) ?: [] as $path) {
            $contents = (string) File::get($path);

            if (! preg_match('/class\s+([A-Za-z0-9_]+)/', $contents, $classMatch)) {
                continue;
            }

            if (! preg_match('/\/data\/([^\'"]+\.json)/', $contents, $manifestMatch)) {
                continue;
            }

            $map[$manifestMatch[1]] = 'Database\\Seeders\\V3\\TheoryLinks\\' . $classMatch[1];
        }

        return $map;
    }

    /**
     * @param  array<string, mixed>  $manifestIndex
     * @return array<string, mixed>|null
     */
    private function manifestForPage(Page $page, string $route, array $manifestIndex): ?array
    {
        $pageSeeder = trim((string) $page->seeder);
        if ($pageSeeder !== '' && isset($manifestIndex['by_page_seeder'][$pageSeeder])) {
            return $manifestIndex['by_page_seeder'][$pageSeeder];
        }

        $normalizedRoute = $this->normalizeRoute($route);
        if (isset($manifestIndex['by_route'][$normalizedRoute])) {
            return $manifestIndex['by_route'][$normalizedRoute];
        }

        $categoryAndSlug = $this->normalizeRoute($this->categorySlugPath($page) . '/' . (string) $page->slug);

        return $manifestIndex['by_category_and_slug'][$categoryAndSlug] ?? null;
    }

    /**
     * @param  array<string, mixed>|null  $manifest
     */
    private function canonicalRouteForPage(string $fallbackRoute, ?array $manifest): string
    {
        $manifestRoute = trim((string) ($manifest['route'] ?? ''));

        if ($manifestRoute === '') {
            return $fallbackRoute;
        }

        return '/' . $this->normalizeRoute($manifestRoute);
    }

    private function normalizeRoute(string $route): string
    {
        return trim(Str::lower(str_replace('\\', '/', $route)), '/');
    }

    private function yesNo(bool $value): string
    {
        return $value ? 'yes' : 'no';
    }

    /**
     * @param  array<int, mixed>  $values
     */
    private function inlineList(array $values): string
    {
        $values = array_values(array_filter(array_map(fn (mixed $value): string => trim((string) $value), $values)));

        if ($values === []) {
            return '-';
        }

        return collect($values)
            ->map(fn (string $value): string => '`' . $this->escapeMarkdownCell(class_basename($value) ?: $value) . '`')
            ->implode('<br>');
    }

    private function escapeMarkdownCell(string $value): string
    {
        return str_replace('|', '\\|', $value);
    }

    private function relativePath(string $path): string
    {
        $normalizedPath = str_replace('\\', '/', $path);
        $normalizedRoot = rtrim(str_replace('\\', '/', base_path()), '/');

        return ltrim((string) Str::after($normalizedPath, $normalizedRoot), '/');
    }
}
