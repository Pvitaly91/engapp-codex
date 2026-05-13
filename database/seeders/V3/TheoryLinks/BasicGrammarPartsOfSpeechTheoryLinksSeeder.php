<?php

namespace Database\Seeders\V3\TheoryLinks;

use App\Models\Question;
use App\Models\TextBlock;
use App\Support\Database\QuestionUuidResolver;
use App\Support\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class BasicGrammarPartsOfSpeechTheoryLinksSeeder extends Seeder
{
    private const MANIFEST_PATH = __DIR__ . '/data/basic-grammar-parts-of-speech-theory-links.json';

    private const ARTICLE_OR_DETERMINER_ALIAS = 'article_or_determiner_note';

    /**
     * Compound tags should win over simple POS tags when both are attached.
     *
     * @var array<int, string>
     */
    private const BUNDLE_PRIORITY = [
        'adverb_vs_adjective',
        'word_form_derivation',
        'pos_context_choice',
        'pos_ambiguity',
    ];

    public function run(): void
    {
        $manifest = $this->loadManifest();
        $blocksByAlias = $this->resolveTheoryTextBlocks($manifest);
        $bundles = $this->normalizeBundles(Arr::get($manifest, 'bundles', []));
        $linkedQuestionUuids = [];
        $stats = [
            'questions' => 0,
            'pivot_rows' => 0,
            'missing_questions' => [],
            'fallback_questions' => 0,
        ];

        foreach (Arr::get($manifest, 'tests_on_page', []) as $testDefinition) {
            $strategy = (string) Arr::get($testDefinition, 'strategy', '');

            if ($strategy === 'explicit_question_uuid_map') {
                $this->linkExplicitQuestions(
                    $testDefinition,
                    $blocksByAlias,
                    $bundles,
                    $linkedQuestionUuids,
                    $stats
                );

                continue;
            }

            if ($strategy === 'map_by_question_tag_keys_and_marker_answer') {
                $this->linkMappedQuestions(
                    $testDefinition,
                    $blocksByAlias,
                    $bundles,
                    $linkedQuestionUuids,
                    $stats
                );
            }
        }

        if ($stats['missing_questions'] !== [] && $this->command !== null) {
            $this->command->warn(sprintf(
                'Skipped %d missing question(s): %s',
                count($stats['missing_questions']),
                implode(', ', array_slice($stats['missing_questions'], 0, 8))
                    . (count($stats['missing_questions']) > 8 ? '...' : '')
            ));
        }

        if ($this->command !== null) {
            $this->command->info(sprintf(
                'Linked %d Parts of Speech questions with %d pivot rows; fallback bundle used for %d question(s).',
                $stats['questions'],
                $stats['pivot_rows'],
                $stats['fallback_questions']
            ));
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function loadManifest(): array
    {
        if (! File::exists(self::MANIFEST_PATH)) {
            throw new RuntimeException('Theory links manifest not found: ' . self::MANIFEST_PATH);
        }

        $manifest = json_decode(File::get(self::MANIFEST_PATH), true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($manifest)) {
            throw new RuntimeException('Theory links manifest must decode to an object.');
        }

        return $manifest;
    }

    /**
     * @param  array<string, mixed>  $manifest
     * @return array<string, string>
     */
    private function resolveTheoryTextBlocks(array $manifest): array
    {
        $resolved = [];
        $cache = [];

        foreach (Arr::get($manifest, 'theory_text_block_aliases', []) as $alias => $definition) {
            if (! is_array($definition)) {
                continue;
            }

            $seederClass = trim((string) Arr::get($definition, 'seeder_class', ''));
            $sortOrder = (int) Arr::get($definition, 'sort_order', 0);

            if ($seederClass === '' || $sortOrder <= 0) {
                continue;
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
                    'Theory text block missing for alias [%s]: seeder=%s sort_order=%d. Run the page seeder first.',
                    (string) $alias,
                    $seederClass,
                    $sortOrder
                ));
            }

            $resolved[$this->normalizeKey((string) $alias)] = (string) $block->uuid;
        }

        return $resolved;
    }

    /**
     * @param  array<string, mixed>  $rawBundles
     * @return array<string, array<int, string>>
     */
    private function normalizeBundles(array $rawBundles): array
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
     * @param  array<string, mixed>  $stats
     */
    private function linkExplicitQuestions(
        array $testDefinition,
        array $blocksByAlias,
        array $bundles,
        array &$linkedQuestionUuids,
        array &$stats
    ): void {
        $resolver = app(QuestionUuidResolver::class);
        $seederClass = trim((string) Arr::get($testDefinition, 'seeder_class', ''));

        foreach (Arr::get($testDefinition, 'question_links', []) as $questionUuid => $bundleAliases) {
            $persistentUuid = $resolver->toPersistent((string) $questionUuid);
            $query = Question::query()->where('uuid', $persistentUuid);

            if ($seederClass !== '') {
                $query->where('seeder', $seederClass);
            }

            $question = $query->first();
            if (! $question) {
                $stats['missing_questions'][] = $persistentUuid;

                continue;
            }

            $blockUuids = $this->resolveBundleAliasesToBlockUuids(
                is_array($bundleAliases) ? $bundleAliases : [],
                $blocksByAlias,
                $bundles
            );

            $written = $this->rewriteQuestionLinks($question, $blockUuids);
            $linkedQuestionUuids[(string) $question->uuid] = true;
            $stats['questions']++;
            $stats['pivot_rows'] += $written;
        }
    }

    /**
     * @param  array<string, mixed>  $testDefinition
     * @param  array<string, string>  $blocksByAlias
     * @param  array<string, array<int, string>>  $bundles
     * @param  array<string, bool>  $linkedQuestionUuids
     * @param  array<string, mixed>  $stats
     */
    private function linkMappedQuestions(
        array $testDefinition,
        array $blocksByAlias,
        array $bundles,
        array &$linkedQuestionUuids,
        array &$stats
    ): void {
        $seederClasses = collect(Arr::get($testDefinition, 'expected_seeder_classes', []))
            ->map(fn ($className): string => trim((string) $className))
            ->filter()
            ->values()
            ->all();

        if ($seederClasses === []) {
            return;
        }

        $tagToBundle = $this->normalizeMap(Arr::get($testDefinition, 'tag_key_to_bundle', []));
        $answerToBundle = $this->normalizeMap(Arr::get($testDefinition, 'answer_value_to_bundle_fallback', []));
        $defaultBundle = $this->normalizeKey((string) Arr::get($testDefinition, 'default_bundle', 'general_overview'));

        $questions = Question::query()
            ->with(['tags', 'answers.option', 'options'])
            ->whereIn('seeder', $seederClasses)
            ->orderBy('id')
            ->get();

        foreach ($questions as $question) {
            if (isset($linkedQuestionUuids[(string) $question->uuid])) {
                continue;
            }

            [$bundle, $usedFallback] = $this->bundleForQuestion(
                $question,
                $tagToBundle,
                $answerToBundle,
                $defaultBundle
            );

            $blockUuids = $this->resolveBundleAliasesToBlockUuids([$bundle], $blocksByAlias, $bundles);
            $written = $this->rewriteQuestionLinks($question, $blockUuids);
            $linkedQuestionUuids[(string) $question->uuid] = true;
            $stats['questions']++;
            $stats['pivot_rows'] += $written;

            if ($usedFallback) {
                $stats['fallback_questions']++;
            }
        }
    }

    /**
     * @param  array<string, string>  $tagToBundle
     * @param  array<string, string>  $answerToBundle
     * @return array{0: string, 1: bool}
     */
    private function bundleForQuestion(
        Question $question,
        array $tagToBundle,
        array $answerToBundle,
        string $defaultBundle
    ): array {
        $matchedBundles = $question->tags
            ->map(fn ($tag): string => $this->normalizeKey((string) ($tag->name ?? '')))
            ->map(fn (string $tag): ?string => $tagToBundle[$tag] ?? null)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($matchedBundles !== []) {
            foreach (self::BUNDLE_PRIORITY as $priorityBundle) {
                if (in_array($priorityBundle, $matchedBundles, true)) {
                    return [$priorityBundle, false];
                }
            }

            return [(string) $matchedBundles[0], false];
        }

        foreach ($this->candidateAnswerValues($question) as $value) {
            $normalizedValue = $this->normalizeKey($value);

            if (isset($answerToBundle[$normalizedValue])) {
                return [$answerToBundle[$normalizedValue], true];
            }
        }

        return [$defaultBundle, true];
    }

    /**
     * @return array<int, string>
     */
    private function candidateAnswerValues(Question $question): array
    {
        $values = [];

        foreach ($question->answers as $answer) {
            $values[] = (string) ($answer->option?->option ?? '');
        }

        foreach ($question->options as $option) {
            $values[] = (string) ($option->option ?? '');
        }

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
            ->unique(fn (string $value): string => Str::lower($value))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, mixed>  $aliases
     * @param  array<string, string>  $blocksByAlias
     * @param  array<string, array<int, string>>  $bundles
     * @return array<int, string>
     */
    private function resolveBundleAliasesToBlockUuids(array $aliases, array $blocksByAlias, array $bundles): array
    {
        $blockAliases = [];

        foreach ($aliases as $alias) {
            $normalizedAlias = $this->normalizeKey((string) $alias);

            if ($normalizedAlias === '') {
                continue;
            }

            if ($normalizedAlias === self::ARTICLE_OR_DETERMINER_ALIAS) {
                array_push($blockAliases, 'overview_8_parts', 'summary_table');

                continue;
            }

            if (isset($bundles[$normalizedAlias])) {
                array_push($blockAliases, ...$bundles[$normalizedAlias]);

                continue;
            }

            if (array_key_exists($normalizedAlias, $blocksByAlias)) {
                $blockAliases[] = $normalizedAlias;

                continue;
            }

            if ($this->command !== null) {
                $this->command->warn(sprintf('Unknown Parts of Speech theory-link alias skipped: %s', (string) $alias));
            }
        }

        $uuids = [];
        $seen = [];

        foreach ($blockAliases as $blockAlias) {
            $normalizedBlockAlias = $this->normalizeKey($blockAlias);
            $uuid = $blocksByAlias[$normalizedBlockAlias] ?? null;

            if (! is_string($uuid) || $uuid === '' || isset($seen[$uuid])) {
                continue;
            }

            $seen[$uuid] = true;
            $uuids[] = $uuid;
        }

        return $uuids;
    }

    /**
     * @return array<string, string>
     */
    private function normalizeMap(mixed $payload): array
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

    private function rewriteQuestionLinks(Question $question, array $blockUuids): int
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

    private function normalizeKey(string $value): string
    {
        $normalized = Str::lower(trim($value));
        $normalized = preg_replace('/[\s\-_]+/u', '_', $normalized) ?? $normalized;

        return trim($normalized, '_');
    }
}
