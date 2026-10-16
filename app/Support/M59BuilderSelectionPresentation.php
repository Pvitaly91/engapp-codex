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

/** Reviewed M59 reuse only: existing exclusion/clone seams, never queries or persisted edits. */
final class M59BuilderSelectionPresentation
{
    public const AUDIT = 'docs/content/m59-passive-future-agent-review-bank-audit.v1.json';
    public const AUDIT_SHA = 'ee6404dc7c61db91b0ec544135c9f2df60d2f04eadc2d6fd6a01607a7940622a';
    private const SELECTED = [
        [51651, 51653, 51656, 51657, 51658],
        [38247, 38250, 38251, 38252, 38256],
        [17064, 17066, 17042, 17031, 38767],
    ];
    private const EXCLUDED_COUNTS = [7, 7, 55];
    private const BANKS = [
        ['Database\\Seeders\\V3\\Polyglot\\PolyglotPassiveVoiceFutureContinuousAllLevelsLessonSeeder'],
        ['Database\\Seeders\\V3\\Polyglot\\PolyglotPassiveVoiceByPhraseAllLevelsLessonSeeder'],
        ['Database\\Seeders\\V3\\Polyglot\\PolyglotAdvancedPassiveVoiceB2LessonSeeder',
            'Database\\Seeders\\V3\\Polyglot\\PolyglotAdvancedPassiveVoiceAllLevelsLessonSeeder'],
    ];
    private const RESTORATIONS = [51653 => ['a12' => [5009, 'Progress', 'progress']]];
    private const PRESERVED_VARIANTS = [
        38247 => [['id' => 28030, 'question_id' => 38247, 'text' => 'Картину написав відомий художник.']],
        38250 => [['id' => 28033, 'question_id' => 38250, 'text' => 'Мою машину вкрали минулої ночі.']],
        38251 => [['id' => 28034, 'question_id' => 38251, 'text' => 'Рішення ухвалив комітет.']],
        38252 => [['id' => 28035, 'question_id' => 38252, 'text' => 'Торт розрізали ножем.']],
        38256 => [['id' => 28039, 'question_id' => 38256, 'text' => 'Дерево вдарила блискавка.']],
        38767 => [['id' => 28550, 'question_id' => 38767, 'text' => 'Вважають, що політика зменшує ризик.']],
    ];
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

    /** Preserve every caller exclusion; curated selection remains the unchanged page-wide matcher. */
    public static function exclusions(TextBlock $block, array $config, array $callerExclusions): array
    {
        if (self::$canonicalClones === null || !isset(self::$canonicalClones[$block])) { return $callerExclusions; }
        $binding = self::binding($block);
        if ($binding === null) { return $callerExclusions; }
        [$target] = $binding;
        if ($config !== ['source' => 'theory_links', 'question_types' => ['4'], 'seeder_classes' => $target['bank_seeders']]
            || array_intersect($callerExclusions, $target['expected_existing_selected_ids']) !== []) { return $callerExclusions; }
        foreach ($target['excluded_question_ids'] as $id) {
            if (!in_array($id, $callerExclusions, true)) { $callerExclusions[] = $id; }
        }
        return $callerExclusions;
    }

    /** All five original snapshots must match before any context or answer-option copy is made. */
    public static function bindings(array $bindings): array
    {
        $block = $bindings['block'] ?? null; $questions = $bindings['practiceQuestions'] ?? null;
        if (($bindings['theoryCanonical'] ?? false) !== true || !$block instanceof TextBlock
            || !$questions instanceof Collection || $questions->count() !== 5) { return $bindings; }
        try {
            $binding = self::binding($block);
            if ($binding === null) { return $bindings; }
            [$target] = $binding;
            $copy = self::questions($questions, $target);
            if ($copy !== null) { $bindings['practiceQuestions'] = $copy; }
        } catch (Throwable) {
            // Stale, foreign or incomplete input retains the entire original collection.
        }
        return $bindings;
    }

    /** Pure writer preflight: final practice bindings and the reviewed reuse-only scope. */
    public static function validateAudit(array $audit, array $patch, array $contract): void
    {
        if (($audit['schema_version'] ?? null) !== 1 || ($audit['package'] ?? null) !== 'm59-passive-future-agent-review-bank-audit'
            || ($audit['version'] ?? null) !== '1.0.0' || ($audit['status'] ?? null) !== 'frozen'
            || ($audit['locale'] ?? null) !== 'uk' || ($audit['pending'] ?? null) !== []
            || ($audit['independent_before_sha256'] ?? null) !== '27fbf37afc8efb9152b0f0a90d73dfab7b2114f72a15a9ceaf4c336b678d036d'
            || ($audit['scope'] ?? null) !== ['selected_questions' => 15, 'reused_questions' => 15, 'new_questions' => 0,
                'question_writes' => 0, 'option_writes' => 0, 'answer_writes' => 0, 'link_writes' => 0,
                'old_hint_writes' => 0, 'grader_changes' => 0, 'progress_writes' => 0]
            || count($audit['targets'] ?? []) !== 3 || count($audit['practice_bindings'] ?? []) !== 3
            || ($contract['schema_version'] ?? null) !== 1 || count($contract['targets'] ?? []) !== 3
            || count($patch['targets'] ?? []) !== 3) { throw new RuntimeException('M59 reuse audit schema/scope differs.'); }
        foreach (M59PassiveFutureAgentReviewPackage::OWNERS as $i => $owner) {
            $target = $audit['targets'][$i]; $practice = $audit['practice_bindings'][$i]; $declaration = $contract['targets'][$i];
            $projected = $patch['targets'][$i]; $index = $practice['source_index'] ?? null;
            $block = is_int($index) ? ($projected['after']['page']['blocks'][$index] ?? null) : null;
            if (($target['identity'] ?? null) !== $owner || ($target['page_id'] ?? null) !== M59PassiveFutureAgentReviewPackage::PAGE_IDS[$i]
                || ($target['category_id'] ?? null) !== M59PassiveFutureAgentReviewPackage::CATEGORY_IDS[$i]
                || ($target['locale'] ?? null) !== 'uk' || ($target['level'] ?? null) !== M59PassiveFutureAgentReviewPackage::LEVELS[$i]
                || ($target['type'] ?? null) !== 4 || ($target['local_url'] ?? null) !== ($declaration['local_url'] ?? null)
                || ($declaration['identity'] ?? null) !== $owner || ($declaration['level'] ?? null) !== $target['level']
                || ($target['bank_seeders'] ?? null) !== ($declaration['bank_seeders'] ?? null)
                || ($target['bank_seeders'] ?? null) !== self::BANKS[$i]
                || ($declaration['definition'] ?? null) !== ($projected['path'] ?? null)
                || ($projected['identity'] ?? null) !== $owner || ($projected['local_url'] ?? null) !== $target['local_url']
                || array_column($target['category_ancestry'] ?? [], 'slug') !== M59PassiveFutureAgentReviewPackage::ANCESTRIES[$i]
                || array_column($target['category_ancestry'] ?? [], 'id') !== ($i === 0 ? [1, 18] : [1])
                || array_column($target['category_ancestry'] ?? [], 'parent_id') !== ($i === 0 ? [null, 1] : [null])
                || array_column($target['category_ancestry'] ?? [], 'type') !== ($i === 0 ? ['theory', 'theory'] : ['theory'])
                || array_column($target['category_ancestry'] ?? [], 'language') !== ($i === 0 ? ['uk', 'uk'] : ['uk'])
                || ($target['expected_existing_selected_ids'] ?? null) !== self::SELECTED[$i]
                || count($target['excluded_question_ids'] ?? []) !== self::EXCLUDED_COUNTS[$i]
                || !is_array($block) || ($block['type'] ?? null) !== 'practice-set' || ($block['level'] ?? null) !== $target['level']
                || ($practice['identity'] ?? null) !== $owner || ($practice['page_id'] ?? null) !== $target['page_id']
                || ($practice['category_id'] ?? null) !== $target['category_id'] || ($practice['locale'] ?? null) !== 'uk'
                || ($practice['type'] ?? null) !== 'practice-set' || ($practice['level'] ?? null) !== $target['level']
                || ($practice['uuid'] ?? null) !== M26DetailPackage::uuid($owner, $block, $index + 1)
                || ($practice['column'] ?? null) !== ($block['column'] ?? null) || ($practice['heading'] ?? null) !== ($block['heading'] ?? null)
                || ($practice['css_class'] ?? null) !== ($block['css_class'] ?? null)
                || ($practice['body_sha256'] ?? null) !== hash('sha256', (string) ($block['body'] ?? ''))) {
                throw new RuntimeException('M59 exact owner/category/level/practice source binding differs.');
            }
            $body = json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR);
            if (($practice['linked_practice'] ?? null) !== ($body['linked_practice'] ?? null)
                || ($body['linked_practice']['source'] ?? null) !== 'theory_links'
                || ($body['linked_practice']['question_types'] ?? null) !== ['4']
                || ($body['linked_practice']['seeder_classes'] ?? null) !== $target['bank_seeders']) {
                throw new RuntimeException('M59 exact linked practice configuration differs.');
            }
            $pool = []; $selected = []; $uuids = [];
            foreach ($target['expected_old_pool_ids_by_bank'] ?? [] as $bank => $ids) {
                if (!in_array($bank, $target['bank_seeders'], true) || !is_array($ids) || !array_is_list($ids)) {
                    throw new RuntimeException('M59 foreign thematic bank pool.');
                }
                foreach ($ids as $id) { if (!is_int($id) || isset($pool[$id])) { throw new RuntimeException('M59 duplicate/invalid pool ID.'); } $pool[$id] = true; }
            }
            if (array_keys($target['expected_old_pool_ids_by_bank'] ?? []) !== $target['bank_seeders']
                || count($target['first_five'] ?? []) !== 5) { throw new RuntimeException('M59 complete thematic pool/five missing.'); }
            $reviewedPool = array_column($target['all_eligible_review'] ?? [], 'id'); sort($reviewedPool);
            $poolIds = array_keys($pool); sort($poolIds);
            if ($reviewedPool !== $poolIds || count(array_unique($reviewedPool)) !== count($reviewedPool)) {
                throw new RuntimeException('M59 actual eligible pool review is incomplete.');
            }
            foreach ($target['first_five'] as $sample) {
                $id = $sample['id'] ?? null; $prefix = $sample['context_prefix_uk'] ?? null;
                if (!is_int($id) || !isset($pool[$id]) || isset($selected[$id])
                    || !in_array($sample['bank_seeder'] ?? null, $target['bank_seeders'], true)
                    || ($sample['type'] ?? null) !== '4' || ($sample['level'] ?? null) !== $target['level']
                    || !is_string($prefix) || trim($prefix) !== $prefix || $prefix === ''
                    || preg_match('/[А-Яа-яІіЇїЄєҐґ]/u', $prefix) !== 1 || preg_match('/[<>]/', $prefix) === 1
                    || ($sample['visible_condition_uk'] ?? null) !== $prefix.' '.($sample['condition_uk'] ?? '')
                    || ($sample['verb_hints_snapshot'] ?? null) !== [] || !is_array($sample['variants_snapshot'] ?? null)) {
                    throw new RuntimeException('M59 reviewed condition/five differs.');
                }
                $selected[$id] = true; $uuids[] = $sample['uuid'];
                $variants = array_map(static fn (array $row): array => array_intersect_key($row,
                    array_fill_keys(['id', 'question_id', 'text'], true)), $sample['variants_snapshot']);
                if ($variants !== (self::PRESERVED_VARIANTS[$id] ?? [])) {
                    throw new RuntimeException('M59 preserved source variant differs.');
                }
                $tokens = array_column($sample['ordered_answer_snapshot'] ?? [], 'option');
                $tokens = ComposeTokenCase::normalize($tokens);
                foreach (self::RESTORATIONS[$id] ?? [] as $marker => [$optionId, $before, $after]) {
                    $n = (int) substr($marker, 1) - 1; $answer = $sample['ordered_answer_snapshot'][$n] ?? [];
                    if (($answer['marker'] ?? null) !== $marker || ($answer['option_id'] ?? null) !== $optionId
                        || ($answer['option'] ?? null) !== $before) { throw new RuntimeException('M59 exact case restoration differs.'); }
                    $tokens[$n] = $after;
                }
                if ($tokens !== ($sample['correct_tokens'] ?? null) || implode(' ', $tokens) !== ($sample['expected_word_key'] ?? null)) {
                    throw new RuntimeException('M59 strict reviewed answer case/multiplicity differs.');
                }
            }
            $excluded = array_values(array_diff(array_keys($pool), array_keys($selected))); sort($excluded);
            if (array_keys($selected) !== self::SELECTED[$i] || $uuids !== ($target['expected_selected_uuids'] ?? null)
                || $excluded !== ($target['excluded_question_ids'] ?? null)) { throw new RuntimeException('M59 exact curated reuse partition differs.'); }
        }
    }

    private static function binding(TextBlock $block): ?array
    {
        $i = array_search($block->seeder, M59PassiveFutureAgentReviewPackage::OWNERS, true);
        if (app()->getLocale() !== 'uk' || $i === false || $block->page_id !== M59PassiveFutureAgentReviewPackage::PAGE_IDS[$i]
            || $block->page_category_id !== M59PassiveFutureAgentReviewPackage::CATEGORY_IDS[$i]
            || $block->locale !== 'uk' || $block->level !== M59PassiveFutureAgentReviewPackage::LEVELS[$i]
            || $block->type !== 'practice-set') { return null; }
        try {
            $data = json_decode((string) $block->body, true, flags: JSON_THROW_ON_ERROR);
            if (!is_array($data) || M59PassiveFutureAgentReviewPackage::presentation($block, $data) === null) { return null; }
            $audit = self::audit(); $practice = $audit['practice_bindings'][$i];
            if ($block->uuid !== $practice['uuid'] || $block->sort_order - 1 !== $practice['source_index']
                || $block->column !== $practice['column'] || $block->heading !== $practice['heading']
                || $block->css_class !== $practice['css_class'] || hash('sha256', (string) $block->body) !== $practice['body_sha256']) { return null; }
            return [$audit['targets'][$i], $practice];
        } catch (Throwable) { return null; }
    }

    private static function audit(): array
    {
        $file = base_path(self::AUDIT);
        if (!is_file($file) || is_link($file) || !hash_equals(self::AUDIT_SHA, hash_file('sha256', $file))) {
            throw new RuntimeException('M59 frozen bank audit missing or changed.');
        }
        $audit = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
        $contract = base_path(M59PassiveFutureAgentReviewPackage::CONTRACT);
        if (!is_file($contract) || is_link($contract) || !hash_equals(M59PassiveFutureAgentReviewPackage::CONTRACT_SHA, hash_file('sha256', $contract))) {
            throw new RuntimeException('M59 frozen bank contract missing or changed.');
        }
        $release = M59PassiveFutureAgentReviewPackage::load();
        self::validateAudit($audit, $release[1], json_decode(file_get_contents($contract), true, flags: JSON_THROW_ON_ERROR));
        return $audit;
    }

    private static function questions(Collection $questions, array $target): ?Collection
    {
        if ($questions->pluck('id')->all() !== $target['expected_existing_selected_ids']
            || $questions->pluck('uuid')->all() !== $target['expected_selected_uuids']) { return null; }
        foreach ($questions->values() as $i => $question) {
            $sample = $target['first_five'][$i];
            if (!$question instanceof Question || $question->id !== $sample['id'] || $question->uuid !== $sample['uuid']
                || $question->seeder !== $sample['bank_seeder'] || (string) $question->type !== '4' || $question->level !== $sample['level']
                || $question->question !== $sample['condition_uk'] || $question->category_id !== $sample['category_id']
                || $question->source_id !== $sample['source_id'] || !$question->relationLoaded('answers') || !$question->relationLoaded('options')
                || !$question->relationLoaded('hints') || !$question->relationLoaded('verbHints')
                || !$question->getRelation('answers') instanceof Collection || !$question->getRelation('options') instanceof Collection
                || !$question->getRelation('hints') instanceof Collection || !$question->getRelation('verbHints') instanceof Collection
                || self::answerSnapshot($question) !== $sample['ordered_answer_snapshot']
                || self::optionSnapshot($question) !== $sample['original_options_snapshot']
                || self::hintSnapshot($question) !== $sample['hints_snapshot']
                || $question->getRelation('verbHints')->isNotEmpty()
                || !is_string($question->getAttributes()['options_by_marker'] ?? null)
                || hash('sha256', $question->getAttributes()['options_by_marker']) !== $sample['question_options_by_marker_sha256']) { return null; }
        }
        return $questions->values()->map(static function (Question $question, int $i) use ($target): Question {
            $copy = clone $question;
            $copy->setRawAttributes(array_replace($question->getAttributes(), ['question' => $target['first_five'][$i]['visible_condition_uk']]), true);
            if (!isset(self::RESTORATIONS[$question->id])) { return $copy; }
            $copy->setRelation('answers', $question->getRelation('answers')->map(static function (QuestionAnswer $answer) use ($question): QuestionAnswer {
                $plan = self::RESTORATIONS[$question->id][$answer->marker] ?? null;
                if ($plan === null) { return $answer; }
                $cloned = clone $answer; $option = clone $answer->getRelation('option');
                $option->setRawAttributes(array_replace($option->getAttributes(), ['option' => $plan[2]]), true);
                $cloned->setRelation('option', $option);
                return $cloned;
            }));
            return $copy;
        });
    }

    private static function answerSnapshot(Question $question): ?array
    {
        $rows = [];
        foreach ($question->getRelation('answers') as $answer) {
            if (!$answer instanceof QuestionAnswer || !$answer->relationLoaded('option') || $answer->question_id !== $question->id) { return null; }
            $option = $answer->getRelation('option');
            if (!$option instanceof QuestionOption || $option->id !== $answer->option_id) { return null; }
            $rows[] = ['id' => $answer->id, 'marker' => $answer->marker, 'position' => $answer->position, 'option_id' => $answer->option_id, 'option' => $option->option];
        }
        usort($rows, static fn (array $a, array $b): int => strnatcasecmp($a['marker'], $b['marker']));
        return $rows;
    }

    private static function optionSnapshot(Question $question): ?array
    {
        $rows = [];
        foreach ($question->getRelation('options') as $option) {
            if (!$option instanceof QuestionOption || !is_int($option->id) || !is_string($option->option)) { return null; }
            $rows[] = ['id' => $option->id, 'option' => $option->option];
        }
        usort($rows, static fn (array $a, array $b): int => $a['id'] <=> $b['id']);
        return $rows;
    }

    private static function hintSnapshot(Question $question): array
    {
        return $question->getRelation('hints')->map(static fn ($hint): array => [
            'id' => $hint->id, 'question_id' => $hint->question_id, 'provider' => $hint->provider, 'locale' => $hint->locale, 'hint' => $hint->hint,
        ])->values()->all();
    }
}
