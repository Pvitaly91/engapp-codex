<?php

namespace App\Support;

use InvalidArgumentException;

/** Finite UK page-owned Some/Any banks; historical questions and courses stay intact. */
final class SomeAnyPracticeQuality extends FinitePracticeQualityCatalog
{
    public const REVISION = 'saq-v1';
    public const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
    public const TOPICS = ['people' => 'SomeAnyPeople', 'places' => 'SomeAnyPlaces', 'things' => 'SomeAnyThings'];
    protected const UUID_PREFIX = 'saq-';
    protected const SOURCE_PREFIX = 'some_any_quality_';
    protected const NAMESPACE_PREFIX = 'some-any-quality-';
    protected const LABEL = 'Some / Any Quality';
    protected const META_PREFIX = 'some_any_quality';
    public const CATEGORY_ID = 50;
    public const CATEGORY_SLUG = 'some-any';
    public const CATEGORY_TOPICS = ['people', 'places', 'things'];
    public const SAVED_TEST_COUNT = 6;

    private const ORIGINALS = [
        'people' => [
            'standard' => ['d57fca81-fc48-5132-a5d3-1485b2b17476', '448228130b7490054eab2489d300f3fbb162156cd1a77ccdd93db572a9f3204b'],
            'builder' => ['59a6fa21-0f55-53e2-80d6-cbb55464e177', 'b4c1d3b8bd6b27d8e7b1ae7067d3d616bb10d02329d7d57689d425f77c8c0fda']],
        'places' => [
            'standard' => ['8482bf67-91fe-518b-8b8a-f5097aaa1c0c', '5bc94b6969f1757589aaf0f32398f7912e78d3e91fb0a27844650c204182878d'],
            'builder' => ['82592174-2e44-551e-9e53-574ee3ebb05c', '958eb4f2b87c0b55596305da30b842734fc1ed3c3c3d86f547b279fdd4899bab']],
        'things' => [
            'standard' => ['9ea3a2e2-0a89-5a99-8bf4-d489eec41df7', '0904e620e9d464a0be0ea2ca9344d7073071ca59e5c3eee2ace7cbd3f4efae61'],
            'builder' => ['8cc77922-9fb1-5d1a-af24-5a9dd86bd1ba', '56184679bb5ef2c3468dcfb04a219099703144ecb87cf941c2ece3c33fd4bb32']],
    ];

    public static function mapping(string $topic): array
    {
        if (!isset(self::TOPICS[$topic])) { throw new InvalidArgumentException('Unknown Some/Any practice topic.'); }
        $stem = self::TOPICS[$topic];
        $map = ['topic' => $topic, 'stem' => $stem, 'author' => 'docs/content/some-any-quality/'.$topic.'.v1.json'];
        foreach (['standard', 'builder'] as $kind) {
            $namespace = $kind === 'standard' ? 'SomeAny' : 'Polyglot';
            $name = 'UkrainianMixed'.$stem.'Quality'.($kind === 'standard' ? 'Standard' : 'Builder').'Seeder';
            $directory = 'database/seeders/V3/'.$namespace.'/'.$name;
            $oldName = $kind === 'standard' ? $stem.'AllLevelsV3Seeder' : 'Polyglot/Polyglot'.$stem.'AllLevelsLessonSeeder';
            $map[$kind] = ['class' => 'Database\\Seeders\\V3\\'.$namespace.'\\'.$name,
                'definition' => $directory.'/definition.json', 'loader' => $directory.'.php',
                'implementation' => $directory.'/'.$name.'.php',
                'old_definition' => 'database/seeders/V3/'.$oldName.'/definition.json',
                'old_class' => 'Database\\Seeders\\V3\\'.str_replace('/', '\\', $oldName),
                'old_saved_test_uuid' => self::ORIGINALS[$topic][$kind][0], 'old_sha256' => self::ORIGINALS[$topic][$kind][1]];
        }
        return $map;
    }

    protected static function composeSlots(array $author): array { return FinitePracticeChoiceOptions::reviewedSlots($author); }

    public static function compile(string $topic, string $kind): array
    {
        static $compiled = [];
        $source = self::source($topic); self::originalDefinition($topic, $kind);
        $key = $topic.'|'.$kind.'|'.hash('sha256', json_encode($source, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        if (isset($compiled[$key])) { return $compiled[$key]; }
        $definition = parent::compileAuthored($topic, $kind);
        return $compiled[$key] = $kind === 'builder'
            ? FinitePracticeChoiceOptions::compile($definition, $source, $topic, self::composeSlots(...)) : $definition;
    }

    private static function sourceBound(array $payload, array $source): bool
    {
        return ($payload['question'] ?? null) === $source['question']
            && (string) ($payload['type'] ?? '') === (string) $source['type']
            && ($payload['psp_content_revision'] ?? null) === hash('sha256', json_encode($source, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            && ($payload['answer_map'] ?? null) === array_map(static fn ($m) => $m['answer'], $source['markers']);
    }

    /** Exact authored Ukrainian clues, also after cached DB-hint localization. */
    public static function restoreClarifiedHints(array $payload, string $locale): array
    {
        if ($locale !== 'uk' || !is_string($payload['uuid'] ?? null)
            || !str_starts_with($payload['uuid'], self::UUID_PREFIX)) { return $payload; }
        $source = self::questions()[$payload['uuid'] ?? ''] ?? null;
        if (!$source || !self::sourceBound($payload, $source)) { return $payload; }
        $payload['verb_hint'] = $source['verb_hint'];
        $payload['verb_hints'] = array_fill_keys(array_keys($source['markers']), $source['verb_hint']);
        if ((string) $source['type'] === '4') {
            $payload['hint'] = $source['verb_hint'];
            if (array_key_exists('hintUk', $payload)) { $payload['hintUk'] = $source['verb_hint']; }
        }
        return $payload;
    }

    public static function decorate(array $payload): array
    {
        $payload = parent::decorate($payload);
        $source = self::questions()[$payload['uuid'] ?? ''] ?? null;
        if ($source && self::sourceBound($payload, $source)) {
            if ((string) $source['type'] === '0') { $payload['passive_phrase_word_inputs'] = true; }
            // Display only: canonical punctuation and all accepted answers remain untouched.
            $payload['saq_option_caption_revision'] = $payload['psp_content_revision'];
            $payload = self::restoreClarifiedHints($payload, 'uk');
        }
        return $payload;
    }

    public static function cachedNeedsRefresh(array $payloads): bool
    {
        if (parent::cachedNeedsRefresh($payloads)) { return true; }
        $sources = self::questions();
        foreach ($payloads as $payload) {
            $source = $sources[$payload['uuid'] ?? ''] ?? null;
            if (!$source) { continue; }
            if (($payload['saq_option_caption_revision'] ?? null) !== hash('sha256', json_encode($source, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))) { return true; }
            if ((string) $source['type'] === '0' && ($payload['passive_phrase_word_inputs'] ?? null) !== true) { return true; }
            $expected = self::restoreClarifiedHints($payload, 'uk');
            foreach (['verb_hint', 'verb_hints', 'hint'] as $field) {
                if (array_key_exists($field, $expected) && ($payload[$field] ?? null) !== $expected[$field]) { return true; }
            }
        }
        return false;
    }

    public static function savedBuilderDisplayCount(string $uuid, string $slug, array $filters, string $locale): ?int
    {
        if ($locale !== 'uk') { return null; }
        foreach (array_keys(self::TOPICS) as $topic) {
            if ($uuid !== self::ORIGINALS[$topic]['builder'][0]) { continue; }
            try {
                $old = self::originalDefinition($topic, 'builder')['saved_test'];
                if ($slug !== $old['slug'] || self::stateNamespace($filters, $locale, $uuid) === null) { return null; }
                return count(self::compile($topic, 'builder')['questions']);
            } catch (\Throwable) { return null; }
        }
        return null;
    }
}
