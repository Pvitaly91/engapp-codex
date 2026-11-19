<?php

namespace App\Services;

use Illuminate\Support\Str;
use RuntimeException;

/** Pure fail-closed rules used by the explicit CLI updater and isolated fixtures. */
final class FiniteBuilderWordSlotsLocalPolicy
{
    public static function option(string $value): string { return mb_strtolower(Str::ascii(trim($value))); }

    public static function assertAuthor(array $before, array $after, array $edits): void
    {
        $oldRows = $before['questions']; $newRows = $after['questions'];
        unset($before['questions'], $after['questions']);
        if ($before !== $after || array_column($oldRows, 'id') !== array_column($newRows, 'id')) {
            throw new RuntimeException('Unapproved author header/row inventory change.');
        }
        foreach ($oldRows as $i => $old) {
            $new = $newRows[$i]; $edit = $edits[$old['id']] ?? null;
            if ($edit !== null) {
                if (($old['kind'] ?? null) !== 'compose') { throw new RuntimeException('Only compose prompt edits are authorized.'); }
                foreach (['question' => 'question', 'hint_uk' => 'hint', 'uk' => 'uk'] as $field => $key) {
                    if (($old[$field] ?? null) !== ($edit[$key.'_before'] ?? null)
                        || ($new[$field] ?? null) !== ($edit[$key.'_after'] ?? null)) {
                        throw new RuntimeException('Exact reviewed prompt/hint/translation edit differs.');
                    }
                    unset($old[$field], $new[$field]);
                }
            }
            if ($old !== $new) { throw new RuntimeException('Unapproved author content changed.'); }
        }
    }

    public static function assertQuestion(array $before, array $after): void
    {
        $mutable = ['question', 'translation_uk', 'source_text_uk', 'markers', 'verb_hint', 'tokens_correct',
            'options', 'options_by_marker', 'answer_synonyms', 'display_answer_synonyms', 'slot_roles', 'builder_word_slots_version'];
        $old = $before; $new = $after;
        foreach ($mutable as $key) { unset($old[$key], $new[$key]); }
        if ($old !== $new || ($after['type'] ?? null) !== 4 || ($after['question'] ?? null) !== ($after['translation_uk'] ?? null)) {
            throw new RuntimeException('Source correction exceeds exact compose projection.');
        }
        $words = \App\Support\FiniteBuilderWordSlots::words($after['target_text']);
        if (array_values(array_column($after['markers'], 'answer')) !== $words
            || array_keys($after['markers']) !== array_map(static fn ($n) => 'a'.$n, range(1, count($words)))
            || count($after['markers']) < count($before['markers'])) {
            throw new RuntimeException('Every canonical word must have its own retained-position marker.');
        }
        if (($after['builder_word_slots_version'] ?? null) !== 1) { throw new RuntimeException('Finite word-slot version missing.'); }
        foreach ($after['markers'] as $key => $marker) {
            if (($marker['verb_hint'] ?? null) !== $after['verb_hint']) { throw new RuntimeException('Marker hint differs.'); }
            if (count($marker['options'] ?? []) !== 4 || count(array_unique($marker['options'])) !== 4
                || !in_array(\App\Support\FiniteBuilderWordSlots::clean($marker['answer']), $marker['options'], true)
                || ($after['options_by_marker'][$key] ?? null) !== $marker['options']) {
                throw new RuntimeException('Exact four choices per word marker required.');
            }
            foreach ($marker['options'] ?? [] as $option) {
                if (count(preg_split('/\s+/u', trim($option))) !== 1) { throw new RuntimeException('Multiword option in a word slot.'); }
            }
        }
    }

    public static function persistence(array $row): array
    {
        return ['question' => $row['question'], 'markers' => array_map(static fn ($m) => ['answer'=>$m['answer'], 'verb_hint'=>$m['verb_hint']], $row['markers']),
            'options' => self::options($row), 'options_by_marker' => self::optionsByMarker($row), 'verb_hint'=>$row['verb_hint']];
    }

    /** Mirror JsonTestSeeder's explicit choices plus exact canonical answer rows. */
    public static function options(array $row): array
    {
        return array_values(array_unique(array_merge($row['options'], array_column($row['markers'], 'answer'))));
    }

    /** Old finite word banks omit this field; the canonical seeder infers a singleton per marker. */
    public static function optionsByMarker(array $row): array
    {
        if (!empty($row['options_by_marker'])) { return $row['options_by_marker']; }
        return array_map(static fn ($m) => array_values(array_unique(array_merge($m['options'] ?? [], [$m['answer']]))), $row['markers']);
    }

    public static function related(array $focus, string $table, int $id): array
    {
        return array_values(array_filter($focus['relations'][$table], static fn ($row) => (int)$row['question_id'] === $id));
    }

    public static function matches(array $focus, array $row, array $expected): bool
    {
        $id = (int)$row['id']; $options = array_column($focus['options'], 'option', 'id');
        $stored = $row['options_by_marker'] === null ? null : json_decode($row['options_by_marker'], true, flags: JSON_THROW_ON_ERROR);
        if ($row['question'] !== $expected['question'] || $stored !== self::optionsByMarker($expected)) { return false; }
        foreach (['question_answers'=>'answer', 'verb_hints'=>'verb_hint'] as $table=>$field) {
            $found=[];
            foreach (self::related($focus,$table,$id) as $entry) {
                $marker=$entry['marker'];
                if (isset($found[$marker]) || !isset($expected['markers'][$marker])
                    || ($table==='verb_hints' && !in_array($entry['locale'],['','uk'],true))) { return false; }
                $found[$marker]=self::option($options[$entry['option_id']]??'');
            }
            $want=array_map(static fn ($m)=>self::option($m[$field]),$expected['markers']); ksort($found);ksort($want);
            if ($found!==$want) { return false; }
        }
        $variants=self::related($focus,'question_variants',$id);$hints=self::related($focus,'question_hints',$id);
        if (count($variants)!==1 || $variants[0]['text']!==$expected['question'] || count($hints)!==1
            || $hints[0]['provider']!=='compose_prompt' || $hints[0]['locale']!=='uk' || $hints[0]['hint']!==$expected['question']) { return false; }
        $actual=[];
        foreach (self::related($focus,'question_option_question',$id) as $pivot) {
            $actual[]=($pivot['flag']===null?'option':((int)$pivot['flag']===1?'hint':'foreign')).'|'.self::option($options[$pivot['option_id']]??'');
        }
        $want=array_map(static fn ($value)=>'option|'.self::option($value),self::options($expected));
        $want[]='hint|'.self::option($expected['verb_hint']);$want=array_values(array_unique($want));sort($actual);sort($want);
        return $actual===$want;
    }
}
