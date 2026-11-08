<?php

namespace App\Support;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\TextBlock;
use Illuminate\Support\Collection;
use RuntimeException;
use Throwable;
use WeakMap;

/** Finite M64 clone-only teaching conditions; complete original snapshots are opaque-hash bound, never published as raw diagnostic rows. */
final class M64BuilderSelectionPresentation
{
    public const AUDIT = 'docs/content/m64-reason-result-contrast-bank-audit.v1.json';
    public const AUDIT_SHA = '2982c20a18baf5d896506c1ee5f766560cba7b783a6d043f5669b5ef755e4f59';
    private const TARGETS_SHA = '2cbd04648c55b42b480b55fda1cca9c830d0917ab242c58a872180ddd4e707f1';
    private const BEFORE_SHA = '46296d1b282d4f5c5c54b4eeaf1640680a173bb3e81482f5109ded710609f63d';
    private const SCOPE = ['selected_questions' => 15, 'reused_questions' => 7, 'new_questions' => 8, 'question_writes' => 8, 'option_writes' => 0, 'answer_writes' => 85, 'option_link_writes' => 103, 'link_writes' => 8, 'old_hint_writes' => 0, 'old_question_updates' => 0, 'old_option_updates' => 0, 'old_answer_updates' => 0, 'grader_changes' => 0, 'progress_writes' => 0];
    private static ?WeakMap $canonicalClones = null;

    public static function matchingBlock(TextBlock $block, bool $canonicalTheory): TextBlock
    {
        if (!$canonicalTheory || self::binding($block) === null) {
            if (self::$canonicalClones !== null && isset(self::$canonicalClones[$block])) { unset(self::$canonicalClones[$block]); }
            return $block;
        }
        $copy = clone $block;
        self::$canonicalClones ??= new WeakMap;
        self::$canonicalClones[$copy] = true;
        return $copy;
    }

    /** Existing page-wide matcher still selects; this only preserves and extends caller exclusions. */
    public static function exclusions(TextBlock $block, array $config, array $callerExclusions): array
    {
        if (self::$canonicalClones === null || !isset(self::$canonicalClones[$block])) { return $callerExclusions; }
        $target = self::binding($block);
        if ($target === null || $config !== self::config($target)
            || array_intersect($callerExclusions, $target['expected_selected_ids']) !== []) { return $callerExclusions; }
        foreach ($target['excluded_question_ids'] as $id) {
            if (!in_array($id, $callerExclusions, true)) { $callerExclusions[] = $id; }
        }
        return $callerExclusions;
    }

    /** Refuse all five if any original question, answer, physical option or loaded hint is stale. */
    public static function bindings(array $bindings): array
    {
        $block = $bindings['block'] ?? null; $questions = $bindings['practiceQuestions'] ?? null;
        if (($bindings['theoryCanonical'] ?? false) !== true || !$block instanceof TextBlock
            || !$questions instanceof Collection || $questions->count() !== 5) { return $bindings; }
        try {
            $target = self::binding($block);
            if ($target === null || !self::matchesQuestions($questions, $target)) { return $bindings; }
            $bindings['practiceQuestions'] = $questions->values()->map(static function (Question $question, int $index) use ($target): Question {
                $sample = $target['first_five'][$index]; $copy = clone $question;
                $copy->setRawAttributes(array_replace($question->getAttributes(), ['question' => $sample['visible_condition_uk']]), true);
                if ($sample['token_presentations'] === []) { return $copy; }
                $plans = array_column($sample['token_presentations'], null, 'marker');
                $optionPlans = array_column($sample['option_presentations'], null, 'option_id');
                $options = $question->getRelation('options')->map(static function (QuestionOption $option) use ($optionPlans): QuestionOption {
                    $plan = $optionPlans[$option->id] ?? null;
                    if ($plan === null) { return $option; }
                    $cloned = clone $option;
                    $cloned->setRawAttributes(array_replace($option->getAttributes(), ['option' => $plan['rendered']]), true);
                    return $cloned;
                });
                $byId = $options->keyBy('id'); $copy->setRelation('options', $options);
                $copy->setRelation('answers', $question->getRelation('answers')->map(static function (QuestionAnswer $answer) use ($plans, $byId): QuestionAnswer {
                    if (!isset($plans[$answer->marker])) { return $answer; }
                    $cloned = clone $answer; $cloned->setRelation('option', $byId[$answer->option_id]);
                    return $cloned;
                }));
                return $copy;
            });
        } catch (Throwable) {
            // Foreign/incomplete/stale data retains the original entire collection.
        }
        return $bindings;
    }

    /** Writer preflight is pure: immutable reviewed data plus exact final native practice bindings. */
    public static function validateAudit(array $audit, array $patch, array $contract): void
    {
        if (($audit['schema_version'] ?? null) !== 1 || ($audit['package'] ?? null) !== 'm64-reason-result-contrast-bank-audit'
            || ($audit['version'] ?? null) !== '1.0.0' || ($audit['status'] ?? null) !== 'frozen'
            || ($audit['locale'] ?? null) !== 'uk' || ($audit['pending'] ?? null) !== []
            || ($audit['independent_before_sha256'] ?? null) !== self::BEFORE_SHA || ($audit['scope'] ?? null) !== self::SCOPE
            || count($audit['targets'] ?? []) !== 3 || count($audit['practice_bindings'] ?? []) !== 3
            || M26DetailPackage::digest($audit['targets']) !== self::TARGETS_SHA
            || ($contract['schema_version'] ?? null) !== 1 || count($contract['targets'] ?? []) !== 3
            || count($patch['targets'] ?? []) !== 3) { throw new RuntimeException('M64 frozen reuse audit/scope differs.'); }
        foreach (M64ReasonResultContrastPackage::OWNERS as $i => $owner) {
            $target = $audit['targets'][$i]; $practice = $audit['practice_bindings'][$i];
            $declaration = $contract['targets'][$i]; $projected = $patch['targets'][$i];
            $index = $practice['source_index'] ?? null;
            $source = is_int($index) ? ($projected['after']['page']['blocks'][$index] ?? null) : null;
            if ($target['identity'] !== $owner || $target['page_id'] !== M64ReasonResultContrastPackage::PAGE_IDS[$i]
                || $target['category_id'] !== M64ReasonResultContrastPackage::CATEGORY_IDS[$i]
                || $target['locale'] !== 'uk' || $target['level'] !== M64ReasonResultContrastPackage::LEVELS[$i] || $target['type'] !== 4
                || array_column($target['category_ancestry'], 'slug') !== M64ReasonResultContrastPackage::ANCESTRIES[$i]
                || array_column($target['category_ancestry'], 'id') !== [M64ReasonResultContrastPackage::CATEGORY_IDS[$i]]
                || array_column($target['category_ancestry'], 'parent_id') !== [null]
                || array_column($target['category_ancestry'], 'type') !== ['theory']
                || array_column($target['category_ancestry'], 'language') !== ['uk']
                || ($declaration['identity'] ?? null) !== $owner || ($declaration['level'] ?? null) !== M64ReasonResultContrastPackage::LEVELS[$i]
                || ($declaration['bank_seeders'] ?? null) !== $target['bank_seeders']
                || ($declaration['local_url'] ?? null) !== $target['local_url']
                || ($projected['identity'] ?? null) !== $owner || ($projected['path'] ?? null) !== ($declaration['definition'] ?? null)
                || ($projected['local_url'] ?? null) !== $target['local_url']
                || !is_array($source) || ($source['type'] ?? null) !== 'practice-set' || ($source['level'] ?? null) !== M64ReasonResultContrastPackage::LEVELS[$i]
                || ($practice['identity'] ?? null) !== $owner || ($practice['page_id'] ?? null) !== $target['page_id']
                || ($practice['category_id'] ?? null) !== M64ReasonResultContrastPackage::CATEGORY_IDS[$i] || ($practice['locale'] ?? null) !== 'uk'
                || ($practice['type'] ?? null) !== 'practice-set' || ($practice['level'] ?? null) !== M64ReasonResultContrastPackage::LEVELS[$i]
                || ($practice['uuid'] ?? null) !== M26DetailPackage::uuid($owner, $source, $index + 1)
                || ($practice['column'] ?? null) !== ($source['column'] ?? null)
                || ($practice['heading'] ?? null) !== ($source['heading'] ?? null)
                || ($practice['css_class'] ?? null) !== ($source['css_class'] ?? null)
                || ($practice['body_sha256'] ?? null) !== hash('sha256', (string) ($source['body'] ?? ''))) {
                throw new RuntimeException('M64 exact owner/practice binding differs.');
            }
            $body = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR);
            if (($practice['linked_practice'] ?? null) !== ($body['linked_practice'] ?? null)
                || array_intersect_key($body['linked_practice'] ?? [], array_flip(['source', 'question_types', 'seeder_classes'])) !== self::config($target)) {
                throw new RuntimeException('M64 exact thematic linked practice differs.');
            }
            $pool = array_merge(...array_values($target['expected_pool_ids_by_bank']));
            $selected = array_column($target['first_five'], 'id');
            $excluded = array_values(array_diff($pool, $selected)); sort($excluded);
            $review = array_column($target['all_eligible_review'], 'id'); sort($review); $sortedPool = $pool; sort($sortedPool);
            if (count($selected) !== 5 || count(array_unique($selected)) !== 5 || count(array_unique($pool)) !== count($pool)
                || array_keys($target['expected_pool_ids_by_bank']) !== $target['bank_seeders']
                || $selected !== $target['expected_selected_ids']
                || array_column($target['first_five'], 'uuid') !== $target['expected_selected_uuids']
                || $review !== $sortedPool || $excluded !== $target['excluded_question_ids']) {
                throw new RuntimeException('M64 finite pool partition differs.');
            }
            foreach ($target['first_five'] as $sample) {
                $fields = ['question_snapshot','ordered_answer_snapshot','original_options_snapshot','hints_snapshot','verb_hints_snapshot','loaded_option_pivots'];
                if (array_keys($sample['snapshot_hashes'] ?? []) !== $fields
                    || ($sample['question_attribute_keys'] ?? null) !== M51QuestionSupplement::ROW_COLUMNS['questions']
                    || preg_match('/^[a-f0-9]{64}$/D', $sample['full_private_snapshot_sha256'] ?? '') !== 1) {
                    throw new RuntimeException('M64 complete opaque snapshot declaration differs.');
                }
                foreach ($sample['snapshot_hashes'] as $sha) {
                    if (!is_string($sha) || preg_match('/^[a-f0-9]{64}$/D', $sha) !== 1) { throw new RuntimeException('M64 opaque snapshot hash is missing.'); }
                }
                $tokens = $sample['correct_tokens'] ?? [];
                if (!is_array($tokens) || !array_is_list($tokens) || count($tokens) < 4
                    || ComposeTokenCase::normalize($tokens) !== $tokens
                    || implode(' ', $tokens) !== $sample['expected_word_key']
                    || preg_replace('/\s+([,.!?;:])/', '$1', implode(' ', $tokens)) !== $sample['expected_display_sentence']
                    || $sample['visible_condition_uk'] !== $sample['context_prefix_uk'].' '.$sample['displayed_condition_uk']
                    || preg_match('/[А-Яа-яІіЇїЄєҐґ]/u', $sample['context_prefix_uk']) !== 1
                    || !is_bool($sample['is_new'] ?? null)) { throw new RuntimeException('M64 finite teaching condition/display differs.'); }
                $optionPlans = array_column($sample['option_presentations'], null, 'option_id');
                if (count($optionPlans) !== count($sample['option_presentations'])
                    || count($optionPlans) !== count($sample['token_presentations'])
                    || ($sample['is_new'] && $optionPlans !== [])) { throw new RuntimeException('M64 finite punctuation plan cardinality differs.'); }
                $markers = [];
                foreach ($sample['token_presentations'] as $plan) {
                    $n = (int) substr($plan['marker'] ?? '', 1) - 1;
                    if (preg_match('/^a[1-9][0-9]*$/D', $plan['marker'] ?? '') !== 1 || isset($markers[$plan['marker']])
                        || !is_int($plan['id'] ?? null) || $plan['id'] < 1 || ($plan['position'] ?? null) !== null
                        || !is_int($plan['option_id'] ?? null) || $plan['option_id'] < 1
                        || hash('sha256', (string) ($plan['option'] ?? '')) !== ($plan['raw_option_sha256'] ?? null)
                        || ($tokens[$n] ?? null) !== ($plan['rendered'] ?? null)
                        || self::words($plan['option']) !== self::words($plan['rendered'])
                        || !in_array($plan['purpose'] ?? null, ['punctuation_display','case_display'], true)
                        || ($optionPlans[$plan['option_id']] ?? null) !== ['option_id'=>$plan['option_id'],'option'=>$plan['option'],
                            'raw_option_sha256'=>$plan['raw_option_sha256'],'rendered'=>$plan['rendered']]) {
                        throw new RuntimeException('M64 exact word-preserving punctuation instance differs.');
                    }
                    $markers[$plan['marker']] = true;
                }
            }
        }
    }

    /** Pure finite-plan integrity check only, never used for learner grading or global normalization. */
    private static function words(string $text): string
    {
        return trim((string) preg_replace('/[^\\p{L}\\p{N}]+/u', ' ', mb_strtolower($text)));
    }

    private static function config(array $target): array
    {
        return ['source' => 'theory_links', 'question_types' => ['4'], 'seeder_classes' => $target['bank_seeders']];
    }

    private static function binding(TextBlock $block): ?array
    {
        $i = array_search($block->seeder, M64ReasonResultContrastPackage::OWNERS, true);
        if (app()->getLocale() !== 'uk' || $i === false || $block->locale !== 'uk' || $block->level !== M64ReasonResultContrastPackage::LEVELS[$i]
            || $block->page_id !== M64ReasonResultContrastPackage::PAGE_IDS[$i]
            || $block->page_category_id !== M64ReasonResultContrastPackage::CATEGORY_IDS[$i] || $block->type !== 'practice-set') { return null; }
        try {
            $body = json_decode((string) $block->body, true, flags: JSON_THROW_ON_ERROR);
            if (!is_array($body) || M64ReasonResultContrastPackage::presentation($block, $body) === null) { return null; }
            $audit = self::audit(); $practice = $audit['practice_bindings'][$i];
            if ($block->uuid !== $practice['uuid'] || $block->sort_order - 1 !== $practice['source_index']
                || $block->column !== $practice['column'] || $block->heading !== $practice['heading']
                || $block->css_class !== $practice['css_class']
                || hash('sha256', (string) $block->body) !== $practice['body_sha256']) { return null; }
            return $audit['targets'][$i];
        } catch (Throwable) { return null; }
    }

    private static function audit(): array
    {
        $file = base_path(self::AUDIT); $contract = base_path(M64ReasonResultContrastPackage::CONTRACT);
        if (!is_file($file) || is_link($file) || !hash_equals(self::AUDIT_SHA, hash_file('sha256', $file))
            || !is_file($contract) || is_link($contract)
            || !hash_equals(M64ReasonResultContrastPackage::CONTRACT_SHA, hash_file('sha256', $contract))) {
            throw new RuntimeException('M64 reviewed audit/contract unavailable.');
        }
        $audit = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
        [, $patch] = M64ReasonResultContrastPackage::load();
        self::validateAudit($audit, $patch, json_decode(file_get_contents($contract), true, flags: JSON_THROW_ON_ERROR));
        return $audit;
    }

    private static function matchesQuestions(Collection $questions, array $target): bool
    {
        if ($questions->pluck('id')->all() !== $target['expected_selected_ids']
            || $questions->pluck('uuid')->all() !== $target['expected_selected_uuids']) { return false; }
        foreach ($questions->values() as $i => $question) {
            $sample = $target['first_five'][$i];
            if (!$question instanceof Question) { return false; }
            $attributes = $question->getAttributes(); $orderedAttributes = [];
            $expectedKeys = $sample['question_attribute_keys'];
            // Existing explicit-link matcher decorates models after hydration; these are not DB columns.
            $decorations = array_diff_key($attributes, array_flip($expectedKeys));
            if ($decorations !== [] && (array_keys($decorations) !== ['match_score', 'matched_tag_ids', 'marker_tags']
                || $decorations['match_score'] !== null || $decorations['matched_tag_ids'] !== []
                || !is_array($decorations['marker_tags']))) { return false; }
            $keys = array_keys(array_diff_key($attributes, $decorations)); sort($keys); $sorted = $expectedKeys; sort($sorted);
            if ($keys !== $sorted) { return false; }
            foreach ($expectedKeys as $field) { $orderedAttributes[$field] = $attributes[$field]; }
            if (M26DetailPackage::digest($orderedAttributes) !== $sample['snapshot_hashes']['question_snapshot']
                || !$question->relationLoaded('answers') || !$question->relationLoaded('options')
                || !$question->relationLoaded('hints') || !$question->relationLoaded('verbHints')
                || !$question->getRelation('answers') instanceof Collection || !$question->getRelation('options') instanceof Collection
                || !$question->getRelation('hints') instanceof Collection || !$question->getRelation('verbHints') instanceof Collection
                || $question->getRelation('verbHints')->isNotEmpty()) { return false; }
            $answers = self::answers($question); $options = self::options($question); $pivots = self::optionPivots($question);
            if ($answers === null || $options === null || $pivots === null
                || M26DetailPackage::digest($answers) !== $sample['snapshot_hashes']['ordered_answer_snapshot']
                || M26DetailPackage::digest($options) !== $sample['snapshot_hashes']['original_options_snapshot']
                || M26DetailPackage::digest($pivots) !== $sample['snapshot_hashes']['loaded_option_pivots']
                || M26DetailPackage::digest(self::hints($question)) !== $sample['snapshot_hashes']['hints_snapshot']
                || M26DetailPackage::digest([]) !== $sample['snapshot_hashes']['verb_hints_snapshot']) { return false; }
            foreach ($sample['token_presentations'] as $plan) {
                $row = $answers[(int) substr($plan['marker'], 1) - 1] ?? [];
                if ($row !== ['id'=>$plan['id'],'marker'=>$plan['marker'],'position'=>$plan['position'],
                    'option_id'=>$plan['option_id'],'option'=>$plan['option']]) { return false; }
            }
        }
        return true;
    }

    private static function answers(Question $question): ?array
    {
        $rows = [];
        foreach ($question->getRelation('answers') as $answer) {
            if (!$answer instanceof QuestionAnswer || !$answer->relationLoaded('option') || $answer->question_id !== $question->id) { return null; }
            $option = $answer->getRelation('option');
            if (!$option instanceof QuestionOption || $option->id !== $answer->option_id) { return null; }
            $rows[] = ['id' => $answer->id, 'marker' => $answer->marker, 'position' => $answer->position,
                'option_id' => $answer->option_id, 'option' => $option->option];
        }
        usort($rows, static fn (array $a, array $b): int => strnatcasecmp($a['marker'], $b['marker']));
        return $rows;
    }

    private static function options(Question $question): ?array
    {
        $rows = [];
        foreach ($question->getRelation('options') as $option) {
            if (!$option instanceof QuestionOption || !is_int($option->id) || !is_string($option->option)) { return null; }
            $rows[] = ['id' => $option->id, 'option' => $option->option];
        }
        usort($rows, static fn (array $a, array $b): int => $a['id'] <=> $b['id']);
        return $rows;
    }

    private static function hints(Question $question): array
    {
        return $question->getRelation('hints')->map(static fn ($hint): array => [
            'id' => $hint->id, 'question_id' => $hint->question_id, 'provider' => $hint->provider,
            'locale' => $hint->locale, 'hint' => $hint->hint,
        ])->values()->all();
    }

    /** The existing ORM loads these three pivot fields, not the physical pivot ID; no additional query. */
    private static function optionPivots(Question $question): ?array
    {
        $rows = [];
        foreach ($question->getRelation('options') as $option) {
            if (!$option->relationLoaded('pivot')) { return null; }
            $pivot = $option->getRelation('pivot');
            if (!is_object($pivot) || !method_exists($pivot, 'getAttributes')) { return null; }
            $attrs = $pivot->getAttributes();
            if (!array_key_exists('flag', $attrs) || ($attrs['question_id'] ?? null) !== $question->id
                || ($attrs['option_id'] ?? null) !== $option->id) { return null; }
            $rows[] = ['question_id' => $attrs['question_id'], 'option_id' => $attrs['option_id'], 'flag' => $attrs['flag']];
        }
        usort($rows, static fn (array $a, array $b): int => $a['option_id'] <=> $b['option_id']);
        return $rows;
    }

}
