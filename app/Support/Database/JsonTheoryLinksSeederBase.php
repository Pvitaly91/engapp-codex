<?php

namespace App\Support\Database;

use App\Models\Question;
use App\Models\TextBlock;
use Illuminate\Database\Seeder as LaravelSeeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

abstract class JsonTheoryLinksSeederBase extends LaravelSeeder
{
    /**
     * @var array{
     *     questions: int,
     *     pivot_rows: int,
     *     fallback_questions: int,
     *     polyglot_fallback_questions: int,
     *     missing_questions: array<int, string>,
     *     missing_seeders: array<int, string>,
     *     polyglot_fallback_uuids: array<int, string>
     * }
     */
    protected array $stats = [
        'questions' => 0,
        'pivot_rows' => 0,
        'fallback_questions' => 0,
        'polyglot_fallback_questions' => 0,
        'missing_questions' => [],
        'missing_seeders' => [],
        'polyglot_fallback_uuids' => [],
    ];

    abstract protected function manifestPath(): string;

    public function run(): void
    {
        $this->stats = $this->initialStats();

        $manifestPath = $this->manifestPath();
        $manifest = $this->loadManifest($manifestPath);
        $blocksByAlias = $this->resolveTheoryTextBlocks($manifest, $manifestPath);
        $bundles = $this->normalizeBundles(Arr::get($manifest, 'bundles', []));
        $linkedQuestionUuids = [];

        $testDefinitions = collect(Arr::get($manifest, 'tests_on_page', []))
            ->filter(fn ($testDefinition): bool => is_array($testDefinition))
            ->values();

        foreach ($testDefinitions as $testDefinition) {
            if ((string) Arr::get($testDefinition, 'strategy', '') !== 'explicit_question_uuid_map') {
                continue;
            }

            $this->linkExplicitQuestions(
                $testDefinition,
                $blocksByAlias,
                $bundles,
                $linkedQuestionUuids,
                $manifestPath
            );
        }

        foreach ($testDefinitions as $testDefinition) {
            if (! is_array($testDefinition)) {
                continue;
            }

            if ((string) Arr::get($testDefinition, 'strategy', '') === 'explicit_question_uuid_map') {
                continue;
            }

            $this->linkMappedQuestions(
                $testDefinition,
                $blocksByAlias,
                $bundles,
                $linkedQuestionUuids,
                $manifestPath
            );
        }

        if ($this->stats['missing_questions'] !== [] && $this->command !== null) {
            $this->command->warn(sprintf(
                'Skipped %d missing explicit question(s): %s',
                count($this->stats['missing_questions']),
                implode(', ', array_slice($this->stats['missing_questions'], 0, 8))
                    . (count($this->stats['missing_questions']) > 8 ? '...' : '')
            ));
        }

        if ($this->stats['missing_seeders'] !== [] && $this->command !== null) {
            $this->command->warn(sprintf(
                'No questions found for seeder(s): %s. Run source question seeders first.',
                implode(', ', $this->stats['missing_seeders'])
            ));
        }

        if ($this->stats['polyglot_fallback_uuids'] !== [] && $this->command !== null) {
            $this->command->warn(sprintf(
                'Polyglot fallback classification used for %d unlisted question(s): %s',
                count($this->stats['polyglot_fallback_uuids']),
                implode(', ', array_slice($this->stats['polyglot_fallback_uuids'], 0, 8))
                    . (count($this->stats['polyglot_fallback_uuids']) > 8 ? '...' : '')
            ));
        }

        if ($this->command !== null) {
            $this->command->info(sprintf(
                'Linked %d question(s) with %d pivot row(s); fallback bundle used for %d question(s), including %d Polyglot question(s).',
                $this->stats['questions'],
                $this->stats['pivot_rows'],
                $this->stats['fallback_questions'],
                $this->stats['polyglot_fallback_questions']
            ));
        }
    }

    /**
     * @return array{
     *     questions: int,
     *     pivot_rows: int,
     *     fallback_questions: int,
     *     polyglot_fallback_questions: int,
     *     missing_questions: array<int, string>,
     *     missing_seeders: array<int, string>,
     *     polyglot_fallback_uuids: array<int, string>
     * }
     */
    protected function initialStats(): array
    {
        return [
            'questions' => 0,
            'pivot_rows' => 0,
            'fallback_questions' => 0,
            'polyglot_fallback_questions' => 0,
            'missing_questions' => [],
            'missing_seeders' => [],
            'polyglot_fallback_uuids' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function loadManifest(string $manifestPath): array
    {
        if (! File::exists($manifestPath)) {
            throw new RuntimeException('Theory links manifest not found: ' . $manifestPath);
        }

        $manifest = json_decode(File::get($manifestPath), true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($manifest)) {
            throw new RuntimeException('Theory links manifest must decode to an object: ' . $manifestPath);
        }

        return $manifest;
    }

    /**
     * @param  array<string, mixed>  $manifest
     * @return array<string, string>
     */
    protected function resolveTheoryTextBlocks(array $manifest, string $manifestPath): array
    {
        $pageSeederClass = trim((string) (
            Arr::get($manifest, 'page.page_seeder_class')
            ?: Arr::get($manifest, 'page.seeder_class')
            ?: Arr::get($manifest, 'page.seeder.class')
            ?: ''
        ));

        $resolved = [];
        $cache = [];

        foreach (Arr::get($manifest, 'theory_text_block_aliases', []) as $alias => $definition) {
            [$seederClass, $sortOrder] = $this->blockSpecFromDefinition($definition, $pageSeederClass);

            if ($seederClass === '' || $sortOrder <= 0) {
                throw new RuntimeException(sprintf(
                    'Invalid theory block alias `%s` in JSON `%s`.',
                    (string) $alias,
                    $manifestPath
                ));
            }

            $cacheKey = $seederClass . '#' . $sortOrder;
            if (! array_key_exists($cacheKey, $cache)) {
                $cache[$cacheKey] = TextBlock::query()
                    ->where('seeder', $seederClass)
                    ->where('sort_order', $sortOrder)
                    ->first();
            }

            $block = $cache[$cacheKey];
            if (! $block) {
                throw new RuntimeException(sprintf(
                    'Theory text block missing: seeder=%s, sort_order=%d. Run page seeder first.',
                    $seederClass,
                    $sortOrder
                ));
            }

            $resolved[$this->normalizeKey((string) $alias)] = (string) $block->uuid;
        }

        return $resolved;
    }

    /**
     * @return array{0: string, 1: int}
     */
    protected function blockSpecFromDefinition(mixed $definition, string $defaultSeederClass): array
    {
        if (is_array($definition)) {
            return [
                trim((string) Arr::get($definition, 'seeder_class', $defaultSeederClass)),
                (int) Arr::get($definition, 'sort_order', 0),
            ];
        }

        return [$defaultSeederClass, (int) $definition];
    }

    /**
     * @param  array<string, mixed>  $rawBundles
     * @return array<string, array<int, string>>
     */
    protected function normalizeBundles(array $rawBundles): array
    {
        $bundles = [];

        foreach ($rawBundles as $bundle => $aliases) {
            if (! is_array($aliases)) {
                continue;
            }

            $bundles[$this->normalizeKey((string) $bundle)] = array_values(array_filter(
                array_map(fn ($alias): string => trim((string) $alias), $aliases),
                fn (string $alias): bool => $alias !== ''
            ));
        }

        return $bundles;
    }

    /**
     * @param  array<string, mixed>  $testDefinition
     * @param  array<string, string>  $blocksByAlias
     * @param  array<string, array<int, string>>  $bundles
     * @param  array<string, bool>  $linkedQuestionUuids
     */
    protected function linkExplicitQuestions(
        array $testDefinition,
        array $blocksByAlias,
        array $bundles,
        array &$linkedQuestionUuids,
        string $manifestPath
    ): void {
        $seederClass = trim((string) Arr::get($testDefinition, 'seeder_class', ''));
        $resolver = app(QuestionUuidResolver::class);

        foreach (Arr::get($testDefinition, 'question_links', []) as $questionUuid => $bundleAliases) {
            $persistentUuid = $resolver->toPersistent((string) $questionUuid);
            $query = Question::query()->where('uuid', $persistentUuid);

            if ($seederClass !== '') {
                $query->where('seeder', $seederClass);
            }

            $question = $query->first();
            if (! $question) {
                $this->stats['missing_questions'][] = $persistentUuid;

                continue;
            }

            $blockUuids = $this->resolveBundlesToBlockUuids(
                is_array($bundleAliases) ? array_values($bundleAliases) : [],
                $blocksByAlias,
                $bundles,
                $manifestPath
            );

            $written = $this->rewriteQuestionLinks($question, $blockUuids);
            $linkedQuestionUuids[(string) $question->uuid] = true;
            $this->stats['questions']++;
            $this->stats['pivot_rows'] += $written;
        }
    }

    /**
     * @param  array<string, mixed>  $testDefinition
     * @param  array<string, string>  $blocksByAlias
     * @param  array<string, array<int, string>>  $bundles
     * @param  array<string, bool>  $linkedQuestionUuids
     */
    protected function linkMappedQuestions(
        array $testDefinition,
        array $blocksByAlias,
        array $bundles,
        array &$linkedQuestionUuids,
        string $manifestPath
    ): void {
        $seederClasses = collect(Arr::get($testDefinition, 'expected_seeder_classes', []))
            ->map(fn ($className): string => trim((string) $className))
            ->filter()
            ->values()
            ->all();

        if ($seederClasses === []) {
            return;
        }

        $questions = Question::query()
            ->with(['tags', 'answers.option', 'options'])
            ->whereIn('seeder', $seederClasses)
            ->orderBy('id')
            ->get();

        foreach ($seederClasses as $seederClass) {
            if ($questions->where('seeder', $seederClass)->isEmpty()) {
                $this->stats['missing_seeders'][] = $seederClass;
            }
        }

        $tagToBundle = $this->normalizeMap(Arr::get($testDefinition, 'tag_key_to_bundle', []));
        $answerToBundle = $this->normalizeMap(Arr::get($testDefinition, 'answer_value_to_bundle_fallback', []));
        $defaultBundle = $this->normalizeKey((string) Arr::get($testDefinition, 'default_bundle', 'general_overview'));

        foreach ($questions as $question) {
            if (isset($linkedQuestionUuids[(string) $question->uuid])) {
                continue;
            }

            $isPolyglot = $this->isPolyglotQuestion($question);
            [$bundleNames, $usedFallback] = $this->bundleNamesForQuestion(
                $question,
                $tagToBundle,
                $answerToBundle,
                $defaultBundle
            );

            $blockUuids = $this->resolveBundlesToBlockUuids(
                $bundleNames,
                $blocksByAlias,
                $bundles,
                $manifestPath
            );

            $written = $this->rewriteQuestionLinks($question, $blockUuids);
            $linkedQuestionUuids[(string) $question->uuid] = true;
            $this->stats['questions']++;
            $this->stats['pivot_rows'] += $written;

            if ($usedFallback) {
                $this->stats['fallback_questions']++;
            }

            if ($isPolyglot) {
                $this->stats['polyglot_fallback_questions']++;
                $this->stats['polyglot_fallback_uuids'][] = (string) $question->uuid;
            }
        }
    }

    /**
     * @param  array<string, string>  $tagToBundle
     * @param  array<string, string>  $answerToBundle
     * @return array{0: array<int, string>, 1: bool}
     */
    protected function bundleNamesForQuestion(
        Question $question,
        array $tagToBundle,
        array $answerToBundle,
        string $defaultBundle
    ): array {
        $tagKeys = $question->tags
            ->map(fn ($tag): string => $this->normalizeKey((string) ($tag->name ?? '')))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $matchedBundles = $this->bundleNamesFromNormalizedValues($tagKeys, $tagToBundle);
        if ($matchedBundles !== []) {
            return [$matchedBundles, false];
        }

        $acceptedAnswerValues = $this->acceptedAnswerValues($question)
            ->map(fn (string $value): string => $this->normalizeKey($value))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $matchedBundles = $this->bundleNamesFromNormalizedValues($acceptedAnswerValues, $answerToBundle);
        if ($matchedBundles !== []) {
            return [$matchedBundles, true];
        }

        $optionValues = $this->optionFallbackValues($question)
            ->map(fn (string $value): string => $this->normalizeKey($value))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $matchedBundles = $this->bundleNamesFromNormalizedValues($optionValues, $answerToBundle);
        if ($matchedBundles !== []) {
            return [$matchedBundles, true];
        }

        return [[$defaultBundle], true];
    }

    /**
     * @param  array<int, string>  $values
     * @param  array<string, string>  $map
     * @return array<int, string>
     */
    protected function bundleNamesFromNormalizedValues(array $values, array $map): array
    {
        $valueSet = array_fill_keys($values, true);
        $bundles = [];

        foreach ($map as $candidate => $bundle) {
            if (isset($valueSet[$candidate]) && ! in_array($bundle, $bundles, true)) {
                $bundles[] = $bundle;
            }
        }

        return $bundles;
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    protected function acceptedAnswerValues(Question $question)
    {
        return $question->answers
            ->map(fn ($answer): string => (string) ($answer->option?->option ?? ''))
            ->map(fn (string $value): string => trim($value))
            ->filter()
            ->values();
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    protected function optionFallbackValues(Question $question)
    {
        $values = $question->options
            ->map(fn ($option): string => (string) ($option->option ?? ''))
            ->all();

        $optionsByMarker = is_array($question->options_by_marker) ? $question->options_by_marker : [];
        foreach ($optionsByMarker as $options) {
            if (! is_array($options)) {
                continue;
            }

            foreach ($options as $option) {
                $values[] = (string) $option;
            }
        }

        return collect($values)
            ->map(fn (string $value): string => trim($value))
            ->filter()
            ->values();
    }

    /**
     * @param  array<int, string>  $bundleNames
     * @param  array<string, string>  $blocksByAlias
     * @param  array<string, array<int, string>>  $bundles
     * @return array<int, string>
     */
    protected function resolveBundlesToBlockUuids(
        array $bundleNames,
        array $blocksByAlias,
        array $bundles,
        string $manifestPath
    ): array {
        $blockAliases = [];

        foreach ($bundleNames as $bundleName) {
            $this->appendBlockAliasesFromManifestName(
                (string) $bundleName,
                $blocksByAlias,
                $bundles,
                $blockAliases,
                $manifestPath
            );
        }

        $uuids = [];
        $seen = [];

        foreach ($blockAliases as $blockAlias) {
            $normalizedBlockAlias = $this->normalizeKey((string) $blockAlias);
            $uuid = $blocksByAlias[$normalizedBlockAlias] ?? null;

            if (! is_string($uuid) || $uuid === '') {
                throw new RuntimeException(sprintf(
                    'Unknown theory block alias `%s` in JSON `%s`.',
                    (string) $blockAlias,
                    $manifestPath
                ));
            }

            if (isset($seen[$uuid])) {
                continue;
            }

            $seen[$uuid] = true;
            $uuids[] = $uuid;
        }

        return $uuids;
    }

    /**
     * @param  array<string, string>  $blocksByAlias
     * @param  array<string, array<int, string>>  $bundles
     * @param  array<int, string>  $blockAliases
     * @param  array<string, bool>  $stack
     */
    protected function appendBlockAliasesFromManifestName(
        string $name,
        array $blocksByAlias,
        array $bundles,
        array &$blockAliases,
        string $manifestPath,
        array $stack = []
    ): void {
        $normalizedName = $this->normalizeKey($name);

        if ($normalizedName === '') {
            return;
        }

        if (isset($stack[$normalizedName]) && isset($blocksByAlias[$normalizedName])) {
            $blockAliases[] = $normalizedName;

            return;
        }

        if (isset($stack[$normalizedName])) {
            throw new RuntimeException(sprintf(
                'Circular theory bundle reference `%s` in JSON `%s`.',
                $name,
                $manifestPath
            ));
        }

        $fallbackAliases = $this->aliasFallbacks()[$normalizedName] ?? null;
        if ($fallbackAliases !== null) {
            foreach ($fallbackAliases as $alias) {
                $this->appendBlockAliasesFromManifestName(
                    (string) $alias,
                    $blocksByAlias,
                    $bundles,
                    $blockAliases,
                    $manifestPath,
                    $stack
                );
            }

            return;
        }

        if (isset($bundles[$normalizedName])) {
            $stack[$normalizedName] = true;

            foreach ($bundles[$normalizedName] as $alias) {
                $this->appendBlockAliasesFromManifestName(
                    (string) $alias,
                    $blocksByAlias,
                    $bundles,
                    $blockAliases,
                    $manifestPath,
                    $stack
                );
            }

            return;
        }

        if (isset($blocksByAlias[$normalizedName])) {
            $blockAliases[] = $normalizedName;

            return;
        }

        throw new RuntimeException(sprintf(
            'Unknown theory block alias `%s` in JSON `%s`.',
            $name,
            $manifestPath
        ));
    }

    protected function rewriteQuestionLinks(Question $question, array $blockUuids): int
    {
        DB::table('question_theory_text_blocks')
            ->where('question_uuid', $question->uuid)
            ->delete();

        $rows = [];
        $now = now();

        foreach ($blockUuids as $position => $blockUuid) {
            $rows[] = [
                'question_uuid' => $question->uuid,
                'text_block_uuid' => $blockUuid,
                'position' => $position,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows !== []) {
            DB::table('question_theory_text_blocks')->insert($rows);
        }

        $question->theory_text_block_uuid = $blockUuids[0] ?? null;
        $question->save();

        return count($rows);
    }

    /**
     * Compatibility aliases used only when a manifest references an
     * intentionally local note that is not a standalone text block.
     *
     * @return array<string, array<int, string>>
     */
    protected function aliasFallbacks(): array
    {
        return [
            'article_or_determiner_note' => ['overview_8_parts', 'summary_table'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function normalizeMap(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        $map = [];

        foreach ($payload as $key => $value) {
            $normalizedKey = $this->normalizeKey((string) $key);
            $normalizedValue = $this->normalizeKey((string) $value);

            if ($normalizedKey !== '' && $normalizedValue !== '') {
                $map[$normalizedKey] = $normalizedValue;
            }
        }

        return $map;
    }

    protected function normalizeKey(string $value): string
    {
        $normalized = Str::lower(trim($value));
        $normalized = str_replace(["'", '"', '`', '´', '’', '‘', '“', '”'], '', $normalized);
        $normalized = preg_replace('/[^\pL\pN]+/u', '_', $normalized) ?? $normalized;
        $normalized = preg_replace('/_+/u', '_', $normalized) ?? $normalized;

        return trim($normalized, '_');
    }

    protected function isPolyglotQuestion(Question $question): bool
    {
        return Str::startsWith((string) $question->seeder, 'Database\\Seeders\\V3\\Polyglot\\');
    }
}
