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

/** Finite M62 presentation of reviewed existing questions; no queries or persisted mutations. */
final class M62BuilderSelectionPresentation
{
    public const AUDIT = 'docs/content/m62-reported-review-relative-basics-bank-audit.v1.json';
    public const AUDIT_SHA = '8fdc925b5d6c83604131c8bb833bc4aebe4bab5fe7eb45bf123a92aa9448cd65';
    private const TARGETS_SHA = '8208a92461777da4aba95ea1bcc9cd2231999ccbe70386c21fdfc39e368d63e5';
    private const BEFORE_SHA = 'c1d652a764102b5abc9b5dd8a8c9226cd79126302dce58a69e52aab7a9b3c55d';
    private const SCOPE = ['selected_questions' => 15, 'reused_questions' => 15, 'new_questions' => 0,
        'question_writes' => 0, 'option_writes' => 0, 'answer_writes' => 0, 'link_writes' => 0,
        'old_hint_writes' => 0, 'grader_changes' => 0, 'progress_writes' => 0];
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
            || array_intersect($callerExclusions, $target['expected_existing_selected_ids']) !== []) { return $callerExclusions; }
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
                if ($sample['case_restorations'] === []) { return $copy; }
                $plans = array_column($sample['case_restorations'], null, 'marker');
                $copy->setRelation('answers', $question->getRelation('answers')->map(static function (QuestionAnswer $answer) use ($plans): QuestionAnswer {
                    $plan = $plans[$answer->marker] ?? null;
                    if ($plan === null) { return $answer; }
                    $clonedAnswer = clone $answer; $clonedOption = clone $answer->getRelation('option');
                    $clonedOption->setRawAttributes(array_replace($clonedOption->getAttributes(), ['option' => $plan['rendered']]), true);
                    $clonedAnswer->setRelation('option', $clonedOption);
                    return $clonedAnswer;
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
        if (($audit['schema_version'] ?? null) !== 1 || ($audit['package'] ?? null) !== 'm62-reported-review-relative-basics-bank-audit'
            || ($audit['version'] ?? null) !== '1.0.0' || ($audit['status'] ?? null) !== 'frozen'
            || ($audit['locale'] ?? null) !== 'uk' || ($audit['pending'] ?? null) !== []
            || ($audit['independent_before_sha256'] ?? null) !== self::BEFORE_SHA || ($audit['scope'] ?? null) !== self::SCOPE
            || count($audit['targets'] ?? []) !== 3 || count($audit['practice_bindings'] ?? []) !== 3
            || M26DetailPackage::digest($audit['targets']) !== self::TARGETS_SHA
            || ($contract['schema_version'] ?? null) !== 1 || count($contract['targets'] ?? []) !== 3
            || count($patch['targets'] ?? []) !== 3) { throw new RuntimeException('M62 frozen reuse audit/scope differs.'); }
        foreach (M62ReportedReviewRelativeBasicsPackage::OWNERS as $i => $owner) {
            $target = $audit['targets'][$i]; $practice = $audit['practice_bindings'][$i];
            $declaration = $contract['targets'][$i]; $projected = $patch['targets'][$i];
            $index = $practice['source_index'] ?? null;
            $source = is_int($index) ? ($projected['after']['page']['blocks'][$index] ?? null) : null;
            if ($target['identity'] !== $owner || $target['page_id'] !== M62ReportedReviewRelativeBasicsPackage::PAGE_IDS[$i]
                || $target['category_id'] !== M62ReportedReviewRelativeBasicsPackage::CATEGORY_IDS[$i]
                || $target['locale'] !== 'uk' || $target['level'] !== M62ReportedReviewRelativeBasicsPackage::LEVELS[$i] || $target['type'] !== 4
                || array_column($target['category_ancestry'], 'slug') !== M62ReportedReviewRelativeBasicsPackage::ANCESTRIES[$i]
                || array_column($target['category_ancestry'], 'id') !== [M62ReportedReviewRelativeBasicsPackage::CATEGORY_IDS[$i]]
                || array_column($target['category_ancestry'], 'parent_id') !== [null]
                || array_column($target['category_ancestry'], 'type') !== ['theory']
                || array_column($target['category_ancestry'], 'language') !== ['uk']
                || ($declaration['identity'] ?? null) !== $owner || ($declaration['level'] ?? null) !== M62ReportedReviewRelativeBasicsPackage::LEVELS[$i]
                || ($declaration['bank_seeders'] ?? null) !== $target['bank_seeders']
                || ($declaration['local_url'] ?? null) !== $target['local_url']
                || ($projected['identity'] ?? null) !== $owner || ($projected['path'] ?? null) !== ($declaration['definition'] ?? null)
                || ($projected['local_url'] ?? null) !== $target['local_url']
                || !is_array($source) || ($source['type'] ?? null) !== 'practice-set' || ($source['level'] ?? null) !== M62ReportedReviewRelativeBasicsPackage::LEVELS[$i]
                || ($practice['identity'] ?? null) !== $owner || ($practice['page_id'] ?? null) !== $target['page_id']
                || ($practice['category_id'] ?? null) !== M62ReportedReviewRelativeBasicsPackage::CATEGORY_IDS[$i] || ($practice['locale'] ?? null) !== 'uk'
                || ($practice['type'] ?? null) !== 'practice-set' || ($practice['level'] ?? null) !== M62ReportedReviewRelativeBasicsPackage::LEVELS[$i]
                || ($practice['uuid'] ?? null) !== M26DetailPackage::uuid($owner, $source, $index + 1)
                || ($practice['column'] ?? null) !== ($source['column'] ?? null)
                || ($practice['heading'] ?? null) !== ($source['heading'] ?? null)
                || ($practice['css_class'] ?? null) !== ($source['css_class'] ?? null)
                || ($practice['body_sha256'] ?? null) !== hash('sha256', (string) ($source['body'] ?? ''))) {
                throw new RuntimeException('M62 exact owner/practice binding differs.');
            }
            $body = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR);
            if (($practice['linked_practice'] ?? null) !== ($body['linked_practice'] ?? null)
                || array_intersect_key($body['linked_practice'] ?? [], array_flip(['source', 'question_types', 'seeder_classes'])) !== self::config($target)) {
                throw new RuntimeException('M62 exact thematic linked practice differs.');
            }
            $pool = array_merge(...array_values($target['expected_old_pool_ids_by_bank']));
            $selected = array_column($target['first_five'], 'id');
            $excluded = array_values(array_diff($pool, $selected)); sort($excluded);
            $review = array_column($target['all_eligible_review'], 'id'); sort($review); $sortedPool = $pool; sort($sortedPool);
            if (count($selected) !== 5 || count(array_unique($selected)) !== 5 || count(array_unique($pool)) !== count($pool)
                || array_keys($target['expected_old_pool_ids_by_bank']) !== $target['bank_seeders']
                || $selected !== $target['expected_existing_selected_ids']
                || array_column($target['first_five'], 'uuid') !== $target['expected_selected_uuids']
                || $review !== $sortedPool || $excluded !== $target['excluded_question_ids']) {
                throw new RuntimeException('M62 finite pool partition differs.');
            }
            foreach ($target['first_five'] as $sample) {
                $tokens = array_column($sample['ordered_answer_snapshot'], 'option');
                foreach ($sample['case_restorations'] as $case) {
                    $n = (int) substr($case['marker'], 1) - 1; $row = $sample['ordered_answer_snapshot'][$n] ?? [];
                    if (($row['id'] ?? null) !== $case['id'] || ($row['option_id'] ?? null) !== $case['option_id']
                        || ($row['marker'] ?? null) !== $case['marker'] || ($row['option'] ?? null) !== $case['option']
                        || hash('sha256', (string) ($row['option'] ?? '')) !== $case['raw_option_sha256']
                        || !in_array([$case['option'], $case['rendered']], [['Who', 'who'], ['Where', 'where']], true)) {
                        throw new RuntimeException('M62 exact wh-case instance differs.');
                    }
                    $tokens[$n] = $case['rendered'];
                }
                $tokens = ComposeTokenCase::normalize($tokens);
                $bank = ComposeTokenCase::mergeOptions(array_column($sample['original_options_snapshot'], 'option'), $tokens);
                $counts = array_count_values($bank);
                if ($tokens !== $sample['correct_tokens'] || implode(' ', $tokens) !== $sample['expected_word_key']
                    || $sample['visible_condition_uk'] !== $sample['context_prefix_uk'].' '.$sample['condition_uk']
                    || preg_match('/[А-Яа-яІіЇїЄєҐґ]/u', $sample['context_prefix_uk']) !== 1) {
                    throw new RuntimeException('M62 strict reviewed condition/tokens differ.');
                }
                foreach (array_count_values($tokens) as $token => $needed) {
                    if (($counts[$token] ?? 0) !== $needed) { throw new RuntimeException('M62 repeated-token supply differs.'); }
                }
            }
        }
    }

    private static function config(array $target): array
    {
        return ['source' => 'theory_links', 'question_types' => ['4'], 'seeder_classes' => $target['bank_seeders']];
    }

    private static function binding(TextBlock $block): ?array
    {
        $i = array_search($block->seeder, M62ReportedReviewRelativeBasicsPackage::OWNERS, true);
        if (app()->getLocale() !== 'uk' || $i === false || $block->locale !== 'uk' || $block->level !== M62ReportedReviewRelativeBasicsPackage::LEVELS[$i]
            || $block->page_id !== M62ReportedReviewRelativeBasicsPackage::PAGE_IDS[$i]
            || $block->page_category_id !== M62ReportedReviewRelativeBasicsPackage::CATEGORY_IDS[$i] || $block->type !== 'practice-set') { return null; }
        try {
            $body = json_decode((string) $block->body, true, flags: JSON_THROW_ON_ERROR);
            if (!is_array($body) || M62ReportedReviewRelativeBasicsPackage::presentation($block, $body) === null) { return null; }
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
        $file = base_path(self::AUDIT); $contract = base_path(M62ReportedReviewRelativeBasicsPackage::CONTRACT);
        if (!is_file($file) || is_link($file) || !hash_equals(self::AUDIT_SHA, hash_file('sha256', $file))
            || !is_file($contract) || is_link($contract)
            || !hash_equals(M62ReportedReviewRelativeBasicsPackage::CONTRACT_SHA, hash_file('sha256', $contract))) {
            throw new RuntimeException('M62 reviewed audit/contract unavailable.');
        }
        $audit = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
        [, $patch] = M62ReportedReviewRelativeBasicsPackage::load();
        self::validateAudit($audit, $patch, json_decode(file_get_contents($contract), true, flags: JSON_THROW_ON_ERROR));
        return $audit;
    }

    private static function matchesQuestions(Collection $questions, array $target): bool
    {
        if ($questions->pluck('id')->all() !== $target['expected_existing_selected_ids']
            || $questions->pluck('uuid')->all() !== $target['expected_selected_uuids']) { return false; }
        foreach ($questions->values() as $i => $question) {
            $sample = $target['first_five'][$i];
            if (!$question instanceof Question) { return false; }
            $attributes = $question->getAttributes();
            foreach ($sample['question_snapshot'] as $field => $expected) {
                if (!array_key_exists($field, $attributes) || $attributes[$field] !== $expected) { return false; }
            }
            if (!$question->relationLoaded('answers') || !$question->relationLoaded('options')
                || !$question->relationLoaded('hints') || !$question->relationLoaded('verbHints')
                || !$question->getRelation('answers') instanceof Collection || !$question->getRelation('options') instanceof Collection
                || !$question->getRelation('hints') instanceof Collection || !$question->getRelation('verbHints') instanceof Collection
                || self::answers($question) !== $sample['ordered_answer_snapshot']
                || self::options($question) !== $sample['original_options_snapshot']
                || self::hints($question) !== $sample['hints_snapshot']
                || $question->getRelation('verbHints')->isNotEmpty()) { return false; }
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
}
