<?php

namespace App\Support;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\TextBlock;
use Illuminate\Support\Collection;
use Throwable;

/** Exact, render-only UK translations of five historical hints; never a grader or writer. */
final class M53BuilderHintPresentation
{
    public const AUDIT = 'docs/content/m53-passive-constructions-bank-audit.v1.json';
    public const AUDIT_SHA = '48e3b188b791ac0e98538da82fb564c01181fe2c60ed42fa238f2a31992add67';

    public static function bindings(array $bindings): array
    {
        $block = $bindings['block'] ?? null;
        $questions = $bindings['practiceQuestions'] ?? null;
        if (($bindings['theoryCanonical'] ?? false) !== true || app()->getLocale() !== 'uk'
            || !$block instanceof TextBlock || $block->type !== 'practice-set'
            || $block->seeder !== M53PassiveConstructionsPackage::OWNERS[0]
            || $block->page_id !== 92 || $block->page_category_id !== 1
            || $block->locale !== 'uk' || $block->level !== 'B1'
            || !$questions instanceof Collection || $questions->count() !== 5) {
            return $bindings;
        }

        try {
            $raw = json_decode((string) $block->body, true, flags: JSON_THROW_ON_ERROR);
            if (!is_array($raw) || !isset($raw['m53_native_v1'])
                || M53PassiveConstructionsPackage::presentation($block, $raw) === null) {
                return $bindings;
            }
            $audit = self::audit();
            $target = $audit['targets'][0] ?? null;
            $projection = $audit['hint_projection'] ?? null;
            if (!is_array($target) || !is_array($projection)
                || ($target['identity'] ?? null) !== $block->seeder
                || ($target['page_id'] ?? null) !== $block->page_id
                || ($target['category_id'] ?? null) !== $block->page_category_id
                || ($target['level'] ?? null) !== $block->level
                || ($projection['scope'] ?? null) !== 'theory-canonical-question-hints-only'
                || ($projection['identity'] ?? null) !== $block->seeder
                || ($projection['page_id'] ?? null) !== $block->page_id
                || ($projection['category_id'] ?? null) !== $block->page_category_id
                || ($projection['locale'] ?? null) !== 'uk'
                || ($projection['practice_level'] ?? null) !== 'B1'
                || ($target['bank_seeders'] ?? null) !== [$projection['bank_seeder'] ?? null]
                || ($projection['expected_selected_ids'] ?? null) !== ($target['expected_existing_selected_ids'] ?? null)) {
                return $bindings;
            }
            $copy = self::questions($questions, $target, $projection);
            if ($copy !== null) { $bindings['practiceQuestions'] = $copy; }
        } catch (Throwable) {
            // A stale release or incomplete relation preserves the complete historical payload.
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
        $audit = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);

        return is_array($audit) && ($audit['schema_version'] ?? null) === 1 ? $audit : [];
    }

    /** All-or-nothing: no partial translation, lazy loading, model mutation, or answer normalisation. */
    private static function questions(Collection $questions, array $target, array $projection): ?Collection
    {
        $samples = $target['first_five'] ?? [];
        $records = $projection['records'] ?? [];
        if (count($samples) !== 5 || count($records) !== 5 || $questions->count() !== 5
            || $questions->pluck('id')->all() !== ($projection['expected_selected_ids'] ?? null)
            || $questions->pluck('uuid')->all() !== ($target['expected_selected_uuids'] ?? null)) {
            return null;
        }

        foreach ($questions->values() as $i => $question) {
            $sample = $samples[$i]; $record = $records[$i];
            if (!$question instanceof Question || $question->id !== ($sample['id'] ?? null)
                || $question->uuid !== ($sample['uuid'] ?? null)
                || $question->id !== ($record['question_id'] ?? null)
                || $question->uuid !== ($record['question_uuid'] ?? null)
                || $question->seeder !== ($projection['bank_seeder'] ?? null)
                || (string) $question->type !== Question::TYPE_COMPOSE_TOKENS
                || $question->level !== 'B1' || $question->question !== ($sample['condition_uk'] ?? null)
                || !$question->relationLoaded('answers') || !$question->relationLoaded('hints')
                || self::answerSnapshot($question) !== ($sample['ordered_answer_snapshot'] ?? null)
                || !is_string($record['hint_uk'] ?? null)
                || preg_match('/[А-Яа-яІіЇїЄєҐґ]/u', $record['hint_uk']) !== 1
                || preg_match('/[<>]/', $record['hint_uk']) === 1) {
                return null;
            }
            $hints = $question->getRelation('hints');
            if (!$hints instanceof Collection || $hints->count() !== 1) { return null; }
            $hint = $hints->first();
            if (!$hint instanceof QuestionHint || self::hintSnapshot($hint) !== ($record['before'] ?? null)
                || $hint->question_id !== $question->id) {
                return null;
            }
        }

        return $questions->values()->map(static function (Question $question, int $i) use ($records): Question {
            $copy = clone $question;
            $copy->setRelation('hints', $question->getRelation('hints')->map(static function (QuestionHint $hint) use ($records, $i): QuestionHint {
                $translated = clone $hint;
                $translated->setRawAttributes(array_replace($hint->getAttributes(), ['hint' => $records[$i]['hint_uk']]), true);

                return $translated;
            }));

            return $copy;
        });
    }

    private static function answerSnapshot(Question $question): ?array
    {
        $answers = $question->getRelation('answers');
        if (!$answers instanceof Collection) { return null; }
        $snapshot = [];
        foreach ($answers as $answer) {
            if (!$answer instanceof QuestionAnswer || !$answer->relationLoaded('option')
                || $answer->getRelation('option') === null || $answer->question_id !== $question->id) {
                return null;
            }
            $snapshot[] = ['id' => $answer->id, 'marker' => $answer->marker, 'position' => $answer->position,
                'option_id' => $answer->option_id, 'option' => $answer->getRelation('option')->option];
        }
        usort($snapshot, static fn (array $a, array $b): int => strnatcasecmp($a['marker'], $b['marker']));

        return $snapshot;
    }

    private static function hintSnapshot(QuestionHint $hint): array
    {
        return ['id' => $hint->id, 'question_id' => $hint->question_id, 'provider' => $hint->provider,
            'locale' => $hint->locale, 'hint' => $hint->getAttributes()['hint'] ?? null];
    }
}
