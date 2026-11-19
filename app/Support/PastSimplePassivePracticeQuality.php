<?php

namespace App\Support;

/** Exact Past Simple Passive catalog and native-practice supplement. */
final class PastSimplePassivePracticeQuality
{
    public const STANDARD = 'database/seeders/V3/PassiveVoice/PassiveVoicePastSimpleAllLevelsV3Seeder/definition.json';
    public const COMPOSE = 'database/seeders/V3/Polyglot/PolyglotPassiveVoicePastSimpleAllLevelsLessonSeeder/definition.json';
    public const PRACTICE = 'docs/content/past-simple-passive-practice-quality.v1.json';
    public const PRACTICE_SHA = '00feb2236a6efcddb07e25dba482dc05a1269abc89a4d4d54e1c5702ffdbbdcf';

    private static function root(): string { return dirname(__DIR__, 2); }

    public static function questions(): array
    {
        static $entries;
        if ($entries !== null) { return $entries; }
        $entries = [];
        foreach ([self::STANDARD, self::COMPOSE] as $path) {
            $source = json_decode(file_get_contents(self::root().'/'.$path), true, flags: JSON_THROW_ON_ERROR);
            foreach ($source['questions'] as $question) {
                if (($question['quality_revision'] ?? null) === 'past-simple-passive-v1') { $entries[$question['uuid']] = $question; }
            }
        }
        return $entries;
    }

    public static function decorate(array $payload): array
    { return PassivePracticeQuality::decorate($payload, self::questions()[$payload['uuid'] ?? ''] ?? null); }

    public static function cachedNeedsRefresh(array $payloads): bool
    { return PassivePracticeQuality::cachedNeedsRefresh($payloads, self::questions()); }

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
            || (int) ($block->sort_order ?? -1) !== 11 || ($block->body ?? null) !== $entry['after_body']) { return $block; }
        $copy = clone $block;
        if (method_exists($copy, 'setRawAttributes')) { $copy->setRawAttributes(array_replace($copy->getAttributes(), ['body'=>$entry['before_body']]), true); }
        else { $copy->body = $entry['before_body']; }
        return $copy;
    }

    public static function inputTokenLabels(object $block, array $data): array
    {
        if (self::legacyBlock($block) === $block || $data !== json_decode($block->body, true)) { return []; }
        $item = $data['inputs'][3] ?? [];
        if (($item['id'] ?? '') !== 'pasp-translate-window'
            || !in_array('Was the window repaired in the morning?', $item['accepted'] ?? [], true)
            || !in_array('Was the window fixed in the morning?', $item['accepted'] ?? [], true)) { return []; }
        return [3 => ['repaired' => 'repaired (fixed)']];
    }
}
