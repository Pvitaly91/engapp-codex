<?php

namespace App\Support;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\TextBlock;
use Illuminate\Support\Collection;
use Throwable;
use WeakMap;

/** Exact canonical Gerund curation; existing bank selection and grading remain authoritative. */
final class M56BuilderSelectionPresentation
{
    public const AUDIT = 'docs/content/m56-gerund-simple-passives-bank-audit.v1.json';
    public const AUDIT_SHA = 'ee7caf9cc71f872d1a2cf5a97e3f80488016b67b3ba9ff68931873a625c34faf';
    private const CONTRACT = 'docs/content/theory-sentence-builders/m56-gerund-simple-passives.v1.json';
    private const OWNER = 'Database\\Seeders\\Page_V3\\PassiveVoice\\InfinitivesGerund\\PassiveVoicePassiveGerundTheorySeeder';
    private const BANK = 'Database\\Seeders\\V3\\Polyglot\\PolyglotPassiveVoicePassiveGerundAllLevelsLessonSeeder';
    private const SELECTED = [39123, 39125, 39126, 39128, 39130];
    private const EXCLUDED = [39124, 39127, 39129];
    private static ?WeakMap $canonicalClones = null;

    /** Mark only a theory caller's fresh unsaved copy; never persist a selector in block data. */
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

    /** Reuse the matcher's existing exclusion seam; no query, replacement pool or widened filter. */
    public static function exclusions(TextBlock $block, array $config, array $callerExclusions): array
    {
        if (self::$canonicalClones === null || !isset(self::$canonicalClones[$block])
            || self::binding($block) === null
            || $config !== ['source' => 'theory_links', 'question_types' => ['4'], 'seeder_classes' => [self::BANK]]
            || array_intersect($callerExclusions, self::SELECTED) !== []) {
            return $callerExclusions;
        }
        // Keep caller order, values and duplicate semantics intact.
        foreach (self::EXCLUDED as $id) {
            if (!in_array($id, $callerExclusions, true)) { $callerExclusions[] = $id; }
        }
        return $callerExclusions;
    }

    /** Four short UK construction requirements; all five raw bank snapshots must match first. */
    public static function bindings(array $bindings): array
    {
        $block = $bindings['block'] ?? null;
        $questions = $bindings['practiceQuestions'] ?? null;
        if (($bindings['theoryCanonical'] ?? false) !== true || !$block instanceof TextBlock
            || !$questions instanceof Collection || $questions->count() !== 5) { return $bindings; }
        try {
            $binding = self::binding($block);
            if ($binding === null) { return $bindings; }
            [$target, , $projection] = $binding;
            $copy = self::questions($questions, $target, $projection);
            if ($copy !== null) { $bindings['practiceQuestions'] = $copy; }
        } catch (Throwable) {
            // Unknown, incomplete or stale data keeps the complete original collection.
        }
        return $bindings;
    }

    private static function binding(TextBlock $block): ?array
    {
        if (app()->getLocale() !== 'uk' || $block->seeder !== self::OWNER
            || $block->page_id !== 102 || $block->page_category_id !== 17
            || $block->locale !== 'uk' || $block->level !== 'B2' || $block->type !== 'practice-set') {
            return null;
        }
        try {
            $audit = self::audit();
            $target = $audit['targets'][0] ?? null;
            $practice = $audit['practice_bindings'][0] ?? null;
            $selection = $audit['selection_projection'] ?? null;
            $projection = $audit['condition_projection'] ?? null;
            if (($audit['pending'] ?? null) !== [] || !is_array($target) || !is_array($practice)
                || !is_array($selection) || !is_array($projection)
                || ($target['identity'] ?? null) !== self::OWNER || ($target['locale'] ?? null) !== 'uk'
                || ($target['page_id'] ?? null) !== 102 || ($target['category_id'] ?? null) !== 17
                || ($target['level'] ?? null) !== 'B2' || ($target['bank_seeders'] ?? null) !== [self::BANK]
                || array_column($target['category_ancestry'] ?? [], 'slug') !== ['passive-voice', 'passive-voice-infinitives-gerund']
                || array_column($target['category_ancestry'] ?? [], 'id') !== [1, 17]
                || array_column($target['category_ancestry'] ?? [], 'parent_id') !== [null, 1]
                || array_column($target['category_ancestry'] ?? [], 'type') !== ['theory', 'theory']
                || array_column($target['category_ancestry'] ?? [], 'language') !== ['uk', 'uk']
                || ($target['expected_existing_selected_ids'] ?? null) !== self::SELECTED
                || ($selection['scope'] ?? null) !== 'theory-canonical-reviewed-exclusions-only'
                || ($selection['excluded_question_ids'] ?? null) !== self::EXCLUDED
                || ($selection['expected_selected_ids'] ?? null) !== self::SELECTED
                || ($selection['ancestry_slugs'] ?? null) !== ['passive-voice', 'passive-voice-infinitives-gerund']
                || ($projection['scope'] ?? null) !== 'theory-canonical-question-context-only'
                || ($projection['expected_selected_ids'] ?? null) !== self::SELECTED) { return null; }
            foreach ([$selection, $projection] as $record) {
                if (($record['identity'] ?? null) !== self::OWNER || ($record['page_id'] ?? null) !== 102
                    || ($record['category_id'] ?? null) !== 17 || ($record['locale'] ?? null) !== 'uk'
                    || ($record['level'] ?? null) !== 'B2' || ($record['bank_seeder'] ?? null) !== self::BANK) { return null; }
            }
            if (($practice['identity'] ?? null) !== self::OWNER || ($practice['page_id'] ?? null) !== 102
                || ($practice['category_id'] ?? null) !== 17 || ($practice['locale'] ?? null) !== 'uk'
                || ($practice['level'] ?? null) !== 'B2' || ($practice['type'] ?? null) !== 'practice-set'
                || ($practice['uuid'] ?? null) !== $block->uuid
                || ($practice['source_index'] ?? null) !== $block->sort_order - 1
                || ($practice['column'] ?? null) !== $block->column
                || ($practice['heading'] ?? null) !== $block->heading
                || ($practice['css_class'] ?? null) !== $block->css_class
                || ($practice['body_sha256'] ?? null) !== hash('sha256', (string) $block->body)) { return null; }
            $data = json_decode((string) $block->body, true, flags: JSON_THROW_ON_ERROR);
            if (!is_array($data) || ($data['linked_practice'] ?? null) !== ($practice['linked_practice'] ?? null)) { return null; }
            return [$target, $practice, $projection];
        } catch (Throwable) {
            return null;
        }
    }

    private static function audit(): array
    {
        $path = base_path(self::AUDIT);
        if (!is_file($path) || is_link($path)) { return []; }
        $bytes = file_get_contents($path);
        if (!is_string($bytes) || !hash_equals(self::AUDIT_SHA, hash('sha256', $bytes))) { return []; }
        $audit = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        if (!is_array($audit) || ($audit['schema_version'] ?? null) !== 1
            || ($audit['builder_contract']['path'] ?? null) !== self::CONTRACT) { return []; }
        $contractPath = base_path(self::CONTRACT);
        $expectedSha = $audit['builder_contract']['sha256'] ?? null;
        if (!is_string($expectedSha) || preg_match('/^[a-f0-9]{64}$/D', $expectedSha) !== 1
            || !is_file($contractPath) || is_link($contractPath)) { return []; }
        $contractBytes = file_get_contents($contractPath);
        if (!is_string($contractBytes) || !hash_equals($expectedSha, hash('sha256', $contractBytes))) { return []; }
        $contract = json_decode($contractBytes, true, flags: JSON_THROW_ON_ERROR);
        if (($contract['schema_version'] ?? null) !== 1 || count($contract['targets'] ?? []) !== 3
            || ($contract['targets'][0] ?? null) !== [
                'identity' => self::OWNER,
                'definition' => 'database/seeders/Page_V3/PassiveVoice/InfinitivesGerund/PassiveVoicePassiveGerundTheorySeeder/definition.json',
                'local_url' => 'http://gramlyze.loc/theory/passive-voice/passive-voice-infinitives-gerund/theory-passive-voice-passive-gerund',
                'level' => 'B2', 'bank_seeders' => [self::BANK],
            ]) { return []; }
        return $audit;
    }

    private static function questions(Collection $questions, array $target, array $projection): ?Collection
    {
        $samples = $target['first_five'] ?? [];
        $records = $projection['records'] ?? [];
        if (count($samples) !== 5 || count($records) !== 4 || $questions->count() !== 5
            || $questions->pluck('id')->all() !== self::SELECTED
            || $questions->pluck('uuid')->all() !== ($target['expected_selected_uuids'] ?? null)) { return null; }
        $byId = [];
        foreach ($records as $record) {
            $id = $record['question_id'] ?? null;
            if (!is_int($id) || isset($byId[$id]) || !in_array($id, self::SELECTED, true)) { return null; }
            $byId[$id] = $record;
        }
        if (array_keys($byId) !== [39123, 39126, 39128, 39130]) { return null; }
        foreach ($questions->values() as $i => $question) {
            $sample = $samples[$i];
            if (!$question instanceof Question || $question->id !== ($sample['id'] ?? null)
                || $question->uuid !== ($sample['uuid'] ?? null) || $question->seeder !== self::BANK
                || (string) $question->type !== Question::TYPE_COMPOSE_TOKENS || $question->level !== 'B2'
                || $question->question !== ($sample['condition_uk'] ?? null)
                || !$question->relationLoaded('answers') || !$question->relationLoaded('options')
                || !$question->relationLoaded('hints') || !$question->relationLoaded('verbHints')
                || self::answerSnapshot($question) !== ($sample['ordered_answer_snapshot'] ?? null)
                || self::optionSnapshot($question) !== ($sample['original_options_snapshot'] ?? null)
                || !$question->getRelation('hints') instanceof Collection || $question->getRelation('hints')->isNotEmpty()
                || !$question->getRelation('verbHints') instanceof Collection || $question->getRelation('verbHints')->isNotEmpty()) { return null; }
            $metadata = $question->getAttributes()['options_by_marker'] ?? null;
            if (!is_string($metadata) || hash('sha256', $metadata) !== ($sample['question_options_by_marker_sha256'] ?? null)) { return null; }
            if (!isset($byId[$question->id])) { continue; }
            $record = $byId[$question->id];
            $prefix = $record['context_prefix_uk'] ?? null;
            if (($record['question_uuid'] ?? null) !== $question->uuid
                || ($record['before_condition_uk'] ?? null) !== $question->question
                || !is_string($prefix) || trim($prefix) !== $prefix || $prefix === ''
                || preg_match('/[А-Яа-яІіЇїЄєҐґ]/u', $prefix) !== 1 || preg_match('/[<>]/', $prefix) === 1
                || ($record['condition_uk'] ?? null) !== $prefix.' '.$question->question) { return null; }
        }
        return $questions->values()->map(static function (Question $question) use ($byId): Question {
            if (!isset($byId[$question->id])) { return $question; }
            $copy = clone $question;
            $copy->setRawAttributes(array_replace($question->getAttributes(), ['question' => $byId[$question->id]['condition_uk']]), true);
            return $copy;
        });
    }

    private static function answerSnapshot(Question $question): ?array
    {
        $answers = $question->getRelation('answers');
        if (!$answers instanceof Collection) { return null; }
        $rows = [];
        foreach ($answers as $answer) {
            if (!$answer instanceof QuestionAnswer || !$answer->relationLoaded('option')
                || $answer->question_id !== $question->id) { return null; }
            $option = $answer->getRelation('option');
            if (!$option instanceof QuestionOption || $option->id !== $answer->option_id) { return null; }
            $rows[] = ['id' => $answer->id, 'marker' => $answer->marker, 'position' => $answer->position,
                'option_id' => $answer->option_id, 'option' => $option->option];
        }
        usort($rows, static fn (array $a, array $b): int => strnatcasecmp($a['marker'], $b['marker']));
        return $rows;
    }

    private static function optionSnapshot(Question $question): ?array
    {
        $options = $question->getRelation('options');
        if (!$options instanceof Collection) { return null; }
        $rows = [];
        foreach ($options as $option) {
            if (!$option instanceof QuestionOption || !is_int($option->id) || !is_string($option->option)) { return null; }
            $rows[] = ['id' => $option->id, 'option' => $option->option];
        }
        usort($rows, static fn (array $a, array $b): int => $a['id'] <=> $b['id']);
        return $rows;
    }
}
