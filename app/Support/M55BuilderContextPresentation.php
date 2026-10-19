<?php

namespace App\Support;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\TextBlock;
use Illuminate\Support\Collection;
use Throwable;

/** Five reviewed UK focus prefixes in canonical M55 Formality only; no writer, case map or grader. */
final class M55BuilderContextPresentation
{
    public const AUDIT = 'docs/content/m55-passive-review-infinitive-bank-audit.v1.json';
    public const AUDIT_SHA = '861d36dc270f4a016951e57c42255b59b97606bb87229f22b97765394f68a848';
    private const OWNER = 'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceFormalityStyleTheorySeeder';
    private const BANK = 'Database\\Seeders\\V3\\Polyglot\\PolyglotPassiveVoiceFormalityStyleAllLevelsLessonSeeder';
    private const IDS = [36243, 36244, 36245, 36246, 36247];

    public static function bindings(array $bindings): array
    {
        $block = $bindings['block'] ?? null;
        $questions = $bindings['practiceQuestions'] ?? null;
        if (($bindings['theoryCanonical'] ?? false) !== true || app()->getLocale() !== 'uk'
            || !$block instanceof TextBlock || $block->seeder !== self::OWNER
            || $block->page_id !== 94 || $block->page_category_id !== 1
            || $block->locale !== 'uk' || $block->level !== 'B2' || $block->type !== 'practice-set'
            || !$questions instanceof Collection || $questions->count() !== 5) {
            return $bindings;
        }

        try {
            $audit = self::audit();
            $target = $audit['targets'][1] ?? null;
            $practice = $audit['practice_bindings'][1] ?? null;
            $projection = $audit['condition_projection'] ?? null;
            if (!is_array($target) || !is_array($practice) || !is_array($projection)
                || ($audit['pending'] ?? null) !== []
                || ($target['identity'] ?? null) !== self::OWNER
                || ($target['page_id'] ?? null) !== 94 || ($target['category_id'] ?? null) !== 1
                || ($target['locale'] ?? null) !== 'uk' || ($target['level'] ?? null) !== 'B2'
                || ($target['bank_seeders'] ?? null) !== [self::BANK]
                || ($practice['identity'] ?? null) !== self::OWNER
                || ($practice['page_id'] ?? null) !== 94 || ($practice['category_id'] ?? null) !== 1
                || ($practice['locale'] ?? null) !== 'uk' || ($practice['level'] ?? null) !== 'B2'
                || ($practice['type'] ?? null) !== 'practice-set'
                || ($practice['uuid'] ?? null) !== $block->uuid
                || ($practice['source_index'] ?? null) !== $block->sort_order - 1
                || ($practice['column'] ?? null) !== $block->column
                || ($practice['heading'] ?? null) !== $block->heading
                || ($practice['css_class'] ?? null) !== $block->css_class
                || ($practice['body_sha256'] ?? null) !== hash('sha256', (string) $block->body)
                || ($projection['scope'] ?? null) !== 'theory-canonical-question-context-only'
                || ($projection['identity'] ?? null) !== self::OWNER
                || ($projection['page_id'] ?? null) !== 94 || ($projection['category_id'] ?? null) !== 1
                || ($projection['locale'] ?? null) !== 'uk' || ($projection['level'] ?? null) !== 'B2'
                || ($projection['bank_seeder'] ?? null) !== self::BANK
                || ($projection['expected_selected_ids'] ?? null) !== self::IDS) {
                return $bindings;
            }
            $copy = self::questions($questions, $target, $projection);
            if ($copy !== null) { $bindings['practiceQuestions'] = $copy; }
        } catch (Throwable) {
            // Drift or incomplete relations preserve the entire original payload, never a partial prefix.
        }
        return $bindings;
    }

    private static function audit(): array
    {
        $path = base_path(self::AUDIT);
        if (preg_match('/^[a-f0-9]{64}$/D', self::AUDIT_SHA) !== 1 || !is_file($path) || is_link($path)) {
            return [];
        }
        $bytes = file_get_contents($path);
        if (!is_string($bytes) || !hash_equals(self::AUDIT_SHA, hash('sha256', $bytes))) { return []; }
        $data = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        return is_array($data) && ($data['schema_version'] ?? null) === 1 ? $data : [];
    }

    /** Verify all five before cloning only question attributes; no lazy loading or model writes. */
    private static function questions(Collection $questions, array $target, array $projection): ?Collection
    {
        $samples = $target['first_five'] ?? [];
        $records = $projection['records'] ?? [];
        if (count($samples) !== 5 || count($records) !== 5 || $questions->count() !== 5
            || $questions->pluck('id')->all() !== self::IDS
            || ($target['expected_existing_selected_ids'] ?? null) !== self::IDS
            || $questions->pluck('uuid')->all() !== ($target['expected_selected_uuids'] ?? null)) {
            return null;
        }
        foreach ($questions->values() as $i => $question) {
            $sample = $samples[$i]; $record = $records[$i];
            if (!$question instanceof Question || $question->id !== ($sample['id'] ?? null)
                || $question->uuid !== ($sample['uuid'] ?? null)
                || $question->id !== ($record['question_id'] ?? null)
                || $question->uuid !== ($record['question_uuid'] ?? null)
                || $question->seeder !== self::BANK || (string) $question->type !== Question::TYPE_COMPOSE_TOKENS
                || $question->level !== 'B2' || $question->question !== ($sample['condition_uk'] ?? null)
                || $question->question !== ($record['before_condition_uk'] ?? null)
                || !$question->relationLoaded('answers') || !$question->relationLoaded('options')
                || !$question->relationLoaded('hints') || !$question->relationLoaded('verbHints')
                || self::answerSnapshot($question) !== ($sample['ordered_answer_snapshot'] ?? null)
                || self::optionSnapshot($question) !== ($sample['original_options_snapshot'] ?? null)
                || !$question->getRelation('hints') instanceof Collection || $question->getRelation('hints')->isNotEmpty()
                || !$question->getRelation('verbHints') instanceof Collection || $question->getRelation('verbHints')->isNotEmpty()) {
                return null;
            }
            $metadata = $question->getAttributes()['options_by_marker'] ?? null;
            $prefix = $record['context_prefix_uk'] ?? null;
            $condition = $record['condition_uk'] ?? null;
            if (!is_string($metadata) || hash('sha256', $metadata) !== ($sample['question_options_by_marker_sha256'] ?? null)
                || !is_string($prefix) || $prefix === '' || trim($prefix) !== $prefix
                || preg_match('/[А-Яа-яІіЇїЄєҐґ]/u', $prefix) !== 1 || preg_match('/[<>]/', $prefix) === 1
                || !is_string($condition) || $condition !== $prefix.' '.$question->question) {
                return null;
            }
        }

        return $questions->values()->map(static function (Question $question, int $i) use ($records): Question {
            $copy = clone $question;
            $copy->setRawAttributes(array_replace($question->getAttributes(), ['question' => $records[$i]['condition_uk']]), true);
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
