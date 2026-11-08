<?php

namespace App\Support;

/** Finite Future Simple Passive catalog; frozen M58 widget questions stay separate. */
final class FutureSimplePassivePracticeQuality
{
    public const STANDARD = 'database/seeders/V3/PassiveVoice/PassiveVoiceFutureSimpleAllLevelsV3Seeder/definition.json';
    public const COMPOSE = 'database/seeders/V3/Polyglot/PolyglotPassiveVoiceFutureSimpleAllLevelsLessonSeeder/definition.json';
    public const COMPACT_GAPS = 'docs/content/future-simple-passive-gap-compactness.v1.json';
    public const COMPACT_GAPS_SHA = 'c1e00c133f15863562069f2dd2330d762ef7f02ac9039788b5ff700041192963';
    public const PRACTICE = 'docs/content/future-simple-passive-practice-quality.v1.json';
    public const PRACTICE_SHA = '88e0602cbf50c02a98833d92fb3aec8703c7c91eae7593f3164a48649e8c24d1';
    public const WIDGET = 'docs/content/future-simple-passive-widget-quality.v1.json';
    public const WIDGET_SHA = '89b48bb81e01a41286a98a08eb33c7f9f27ed6b9d37ac3c127d0b06447b6df74';

    private static function root(): string { return dirname(__DIR__, 2); }

    public static function questions(): array
    {
        static $entries;
        if ($entries !== null) { return $entries; }
        $entries = [];
        foreach ([self::STANDARD, self::COMPOSE] as $path) {
            $source = json_decode(file_get_contents(self::root().'/'.$path), true, flags: JSON_THROW_ON_ERROR);
            foreach ($source['questions'] as $question) {
                if (($question['quality_revision'] ?? null) === 'future-simple-passive-v1') { $entries[$question['uuid']] = $question; }
            }
        }
        return $entries;
    }

    public static function decorate(array $payload): array
    {
        $source = self::questions()[$payload['uuid'] ?? ''] ?? null;
        $decorated = PassivePracticeQuality::decorate($payload, $source);
        $expected = $source ? array_map(static fn ($marker) => $marker['answer'], $source['markers']) : [];
        if ($source && (string) ($source['type'] ?? '0') === '0' && (string) ($decorated['type'] ?? '') === '0'
            && ($decorated['uuid'] ?? null) === $source['uuid'] && ($decorated['question'] ?? null) === $source['question']
            && ($decorated['answer_map'] ?? null) === $expected
            && ($decorated['psp_content_revision'] ?? null) === hash('sha256', json_encode($source, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))) {
            // Only this exact Future-passive catalog opts into per-word phrase
            // entry; unrelated passive/word-order callers keep their own input.
            $decorated['passive_phrase_word_inputs'] = true;
            $change = self::compactGaps()[$source['uuid']] ?? null;
            if ($change) {
                $decorated['gap_previous_question'] = $change['previous_question'];
                $decorated['gap_previous_answer'] = $change['previous_answer'];
                $decorated['gap_previous_accepted_answers'] = $change['previous_to_current_answers'];
                $decorated['gap_compactness_revision'] = self::COMPACT_GAPS_SHA;
            }
        }
        return FutureSimplePassiveComposeOptions::decorate($decorated, $source);
    }

    public static function cachedNeedsRefresh(array $payloads): bool
    {
        if (PassivePracticeQuality::cachedNeedsRefresh($payloads, self::questions())) { return true; }
        $changes = self::compactGaps();
        foreach ($payloads as $payload) {
            $source = self::questions()[$payload['uuid'] ?? ''] ?? null;
            if (!$source || (string) ($source['type'] ?? '0') !== '0') { continue; }
            if (($payload['passive_phrase_word_inputs'] ?? null) !== true) { return true; }
            $change = $changes[$source['uuid']] ?? null;
            if ($change && (($payload['gap_compactness_revision'] ?? null) !== self::COMPACT_GAPS_SHA
                || ($payload['gap_previous_question'] ?? null) !== $change['previous_question']
                || ($payload['gap_previous_answer'] ?? null) !== $change['previous_answer']
                || ($payload['gap_previous_accepted_answers'] ?? null) !== $change['previous_to_current_answers'])) { return true; }
        }
        return FutureSimplePassiveComposeOptions::cachedNeedsRefresh($payloads, self::questions());
    }

    /** Exact finite old/new contracts for preserving saved answers on eleven WH rows. */
    public static function compactGaps(): array
    {
        static $entries;
        if ($entries !== null) { return $entries; }
        $entries = [];
        $path = self::root().'/'.self::COMPACT_GAPS;
        if (!is_file($path) || is_link($path) || !hash_equals(self::COMPACT_GAPS_SHA, hash_file('sha256', $path))) { return []; }
        $document = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        if (($document['schema_version'] ?? null) !== 1
            || ($document['quality_revision'] ?? null) !== 'future-simple-passive-gap-compactness-v1'
            || ($document['source_path'] ?? null) !== self::STANDARD || count($document['entries'] ?? []) !== 11) { return []; }
        $validated = [];
        foreach ($document['entries'] as $entry) {
            $source = self::questions()[$entry['uuid'] ?? ''] ?? null;
            if (!$source || isset($validated[$entry['uuid']]) || (string) ($source['type'] ?? '0') !== '0'
                || array_keys($source['markers'] ?? []) !== ['a1']
                || ($entry['question'] ?? null) !== $source['question']
                || ($entry['answer'] ?? null) !== ($source['markers']['a1']['answer'] ?? null)
                || !is_string($entry['previous_question'] ?? null) || !is_string($entry['previous_answer'] ?? null)
                || !is_array($entry['previous_to_current_answers'] ?? null)
                || ($entry['previous_to_current_answers'][$entry['previous_answer']] ?? null) !== $entry['answer']
                || array_values($entry['previous_to_current_answers']) !== array_merge([$entry['answer']], $source['answer_synonyms']['a1'] ?? [])
                || str_replace('{a1}', $entry['previous_answer'], $entry['previous_question']) !== ($entry['full_sentence'] ?? null)
                || str_replace('{a1}', $entry['answer'], $entry['question']) !== $entry['full_sentence']
                || implode(' ', $source['reorder_groups'] ?? []) !== $entry['full_sentence']) { return []; }
            foreach ($entry['previous_to_current_answers'] as $previous => $current) {
                if (!is_string($previous) || !is_string($current)
                    || str_replace('{a1}', $previous, $entry['previous_question']) !== str_replace('{a1}', $current, $entry['question'])) { return []; }
            }
            $validated[$entry['uuid']] = $entry;
        }
        return $entries = $validated;
    }

    /** Render-only successor for the five immutable M58 B1 widget rows. */
    public static function widgetQuality(): ?array
    {
        $path = self::root().'/'.self::WIDGET;
        $frozen = self::root().'/'.M58QuestionSupplement::SOURCE;
        if (!is_file($path) || is_link($path) || !hash_equals(self::WIDGET_SHA, hash_file('sha256', $path))
            || !is_file($frozen) || is_link($frozen) || !hash_equals(M58QuestionSupplement::SOURCE_SHA, hash_file('sha256', $frozen))) { return null; }
        $projection = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        $bank = json_decode(file_get_contents($frozen), true, flags: JSON_THROW_ON_ERROR)['banks'][1] ?? [];
        if (($projection['schema_version'] ?? null) !== 1 || ($projection['quality_revision'] ?? null) !== 'future-simple-passive-widget-v1'
            || ($projection['source_path'] ?? null) !== M58QuestionSupplement::SOURCE
            || ($projection['source_sha256'] ?? null) !== M58QuestionSupplement::SOURCE_SHA
            || ($projection['bank_seeder'] ?? null) !== M58QuestionSupplement::BANKS[1]
            || ($projection['page_id'] ?? null) !== 107 || ($projection['category_id'] ?? null) !== 45
            || ($projection['source_id'] ?? null) !== 345 || ($projection['level'] ?? null) !== 'B1'
            || ($projection['type'] ?? null) !== 4 || count($projection['questions'] ?? []) !== 5) { return null; }
        foreach ($projection['questions'] as $index => $entry) {
            $original = $bank['questions'][$index] ?? [];
            if (($entry['uuid'] ?? null) !== ($original['uuid'] ?? null)
                || ($entry['original_question'] ?? null) !== ($original['question'] ?? null)
                || ($entry['original_tokens'] ?? null) !== ($original['tokens_correct'] ?? null)
                || ($entry['accepted_target_texts'][0] ?? null) !== ($original['target_text'] ?? null)
                || !is_string($entry['verb_hint'] ?? null) || trim($entry['verb_hint']) === ''
                || preg_match('/[A-Za-z<>]|\{[^}]*\}/u', $entry['verb_hint']) === 1) { return null; }
        }
        return $projection;
    }

    public static function widgetSource(object $question): ?array
    {
        $projection = self::widgetQuality();
        if (!$projection || app()->getLocale() !== 'uk' || ($question->seeder ?? null) !== $projection['bank_seeder']
            || (string) ($question->type ?? '') !== '4' || ($question->level ?? null) !== 'B1'
            || (int) ($question->category_id ?? 0) !== $projection['category_id']
            || (int) ($question->source_id ?? 0) !== $projection['source_id']
            || !method_exists($question, 'relationLoaded') || !$question->relationLoaded('answers')) { return null; }
        $entry = collect($projection['questions'])->firstWhere('uuid', $question->uuid ?? null);
        if (!$entry || ($question->question ?? null) !== $entry['original_question']) { return null; }
        $actual = [];
        foreach ($question->getRelation('answers') as $answer) {
            if (!method_exists($answer, 'relationLoaded') || !$answer->relationLoaded('option')) { return null; }
            $marker = (string) $answer->marker;
            if (array_key_exists($marker, $actual)) { return null; }
            $actual[$marker] = (string) ($answer->getRelation('option')->option ?? '');
        }
        $markers = [];
        foreach ($entry['original_tokens'] as $index => $token) { $markers['a'.($index + 1)] = ['answer' => $token]; }
        $normalize = static fn ($value) => mb_strtolower(\Illuminate\Support\Str::ascii($value));
        if (count($actual) !== count($markers)) { return null; }
        foreach ($markers as $marker => $expected) {
            if (!array_key_exists($marker, $actual) || $normalize($actual[$marker]) !== $normalize($expected['answer'])) { return null; }
        }
        return ['uuid' => $entry['uuid'], 'question' => $entry['original_question'], 'type' => 4,
            'markers' => $markers, 'tokens_correct' => $entry['original_tokens'],
            'target_text' => $entry['accepted_target_texts'][0], 'accepted_target_texts' => $entry['accepted_target_texts'],
            'verb_hint' => $entry['verb_hint'], 'additional_options' => $entry['additional_options'],
            'token_labels' => $entry['token_labels'], 'widget_quality_revision' => self::WIDGET_SHA];
    }

    public static function supplement(): ?array
    {
        $path = self::root().'/'.self::PRACTICE;
        if (!is_file($path) || !hash_equals(self::PRACTICE_SHA, hash_file('sha256', $path))) { return null; }
        $entry = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        foreach (['before', 'after'] as $state) {
            if (!hash_equals($entry[$state.'_sha256'], hash('sha256', $entry[$state.'_body']))) { return null; }
        }
        return $entry;
    }

    public static function legacyBlock(object $block): object
    {
        $entry = self::supplement();
        if (!$entry || ($block->seeder ?? null) !== $entry['identity'] || ($block->locale ?? null) !== 'uk'
            || ($block->page_id ?? null) !== $entry['page_id'] || ($block->page_category_id ?? null) !== $entry['category_id']
            || ($block->uuid ?? null) !== $entry['practice_uuid'] || ($block->type ?? null) !== 'practice-set'
            || ($block->column ?? null) !== 'left' || ($block->level ?? null) !== 'B1'
            || ($block->heading ?? null) !== null || ($block->css_class ?? null) !== null
            || (int) ($block->sort_order ?? -1) !== 12 || ($block->body ?? null) !== $entry['after_body']) { return $block; }
        $copy = clone $block;
        if (method_exists($copy, 'setRawAttributes')) { $copy->setRawAttributes(array_replace($copy->getAttributes(), ['body'=>$entry['before_body']]), true); }
        else { $copy->body = $entry['before_body']; }
        return $copy;
    }

    public static function inputTokenLabels(object $block, array $data): array
    {
        if (self::legacyBlock($block) === $block || $data !== json_decode($block->body, true)) { return []; }
        return self::supplement()['input_token_labels'] ?? [];
    }
}
