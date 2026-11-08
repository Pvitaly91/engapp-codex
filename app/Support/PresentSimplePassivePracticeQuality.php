<?php

namespace App\Support;

/** Finite source-bound question and practice supplement; never a global vocabulary rule. */
final class PresentSimplePassivePracticeQuality
{
    public const STANDARD = 'database/seeders/V3/PassiveVoice/PassiveVoicePresentSimpleAllLevelsV3Seeder/definition.json';
    public const COMPOSE = 'database/seeders/V3/Polyglot/PolyglotPassiveVoicePresentSimpleAllLevelsLessonSeeder/definition.json';
    public const PRACTICE = 'docs/content/present-simple-passive-practice-quality.v1.json';
    public const PRACTICE_SHA = '1beca871cafcb5db588894fec24702246a292c06bc4ffd740ddebca869fb8a50';

    private static function root(): string { return dirname(__DIR__, 2); }

    public static function questions(): array
    {
        static $entries;
        if ($entries !== null) { return $entries; }
        $entries = [];
        foreach ([self::STANDARD, self::COMPOSE] as $path) {
            $source = json_decode(file_get_contents(self::root().'/'.$path), true, flags: JSON_THROW_ON_ERROR);
            foreach ($source['questions'] as $question) {
                if (($question['quality_revision'] ?? null) === 'present-simple-passive-v1') { $entries[$question['uuid']] = $question; }
            }
        }
        return $entries;
    }

    public static function decorate(array $payload): array
    {
        return PassivePracticeQuality::decorate($payload, self::questions()[$payload['uuid'] ?? ''] ?? null);
    }

    public static function cachedNeedsRefresh(array $payloads): bool
    {
        return PassivePracticeQuality::cachedNeedsRefresh($payloads, self::questions());
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
            || ($block->column ?? null) !== 'left' || ($block->level ?? null) !== 'A2'
            || ($block->heading ?? null) !== null || ($block->css_class ?? null) !== null
            || (int) ($block->sort_order ?? -1) !== 10 || ($block->body ?? null) !== $entry['after_body']) { return $block; }
        $copy = clone $block;
        if (method_exists($copy, 'setRawAttributes')) { $copy->setRawAttributes(array_replace($copy->getAttributes(), ['body'=>$entry['before_body']]), true); }
        else { $copy->body = $entry['before_body']; }
        return $copy;
    }

    /** Display only; clicking still inserts the canonical token value. */
    public static function inputTokenLabels(object $block, array $data): array
    {
        if (self::legacyBlock($block) === $block || $data !== json_decode($block->body, true)) { return []; }
        $item = $data['inputs'][3] ?? [];
        if (($item['id'] ?? '') !== 'psp-translate-repair'
            || !in_array('Are bicycles repaired here?', $item['accepted'] ?? [], true)
            || !in_array('Are bicycles fixed here?', $item['accepted'] ?? [], true)) { return []; }
        return [3 => ['repaired' => 'repaired (fixed)']];
    }
}
