<?php

namespace App\Support;

use InvalidArgumentException;
use Illuminate\Support\Arr;
use Throwable;

/** One UK comparison page only. The rest of Future Forms never opts in. */
final class FutureContinuousComparisonPracticeQuality extends FinitePracticeQualityCatalog
{
    public const REVISION = 'fccq-v1';
    public const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
    public const TOPICS = ['comparison' => 'FutureContinuousVsFuturePerfectContinuous'];
    protected const UUID_PREFIX = 'fccq-';
    protected const SOURCE_PREFIX = 'future_continuous_comparison_quality_';
    protected const NAMESPACE_PREFIX = 'future-continuous-comparison-quality-';
    protected const LABEL = 'Future Continuous Comparison Quality';
    protected const META_PREFIX = 'future_continuous_comparison_quality';
    // No actual category can match this deliberately non-database identity.
    public const CATEGORY_ID = -1;
    public const CATEGORY_SLUG = 'future-continuous-comparison-page-only';
    public const CATEGORY_TOPICS = [];
    public const SAVED_TEST_COUNT = 2;

    private const ORIGINALS = [
        'standard' => ['future-fcvfpc-v3-all', '9af9e01fcd90e2032561d9e029b4f67c62d9705c76ace1f80c8c079ace6357f0'],
        'builder' => ['future-fcvfpc-poly-all', 'c74fa87b00b104af469cd807def9394a6d1c6d0b2a8d45d2de8132568e2afd4d'],
    ];

    public static function mapping(string $topic): array
    {
        if (!isset(self::TOPICS[$topic])) { throw new InvalidArgumentException('Unknown Future Continuous comparison topic.'); }
        $stem = self::TOPICS[$topic];
        $map = ['topic' => $topic, 'stem' => $stem,
            'author' => 'docs/content/future-continuous-comparison-quality/comparison.v1.json'];
        foreach (['standard', 'builder'] as $kind) {
            $namespace = $kind === 'standard' ? 'FutureForms' : 'Polyglot';
            $name = 'UkrainianMixed'.$stem.'Quality'.($kind === 'standard' ? 'Standard' : 'Builder').'Seeder';
            $directory = 'database/seeders/V3/'.$namespace.'/'.$name;
            $oldName = $kind === 'standard' ? 'FutureForms/'.$stem.'AllLevelsV3Seeder'
                : 'Polyglot/Polyglot'.$stem.'AllLevelsLessonSeeder';
            $map[$kind] = ['class' => 'Database\\Seeders\\V3\\'.$namespace.'\\'.$name,
                'definition' => $directory.'/definition.json', 'loader' => $directory.'.php',
                'implementation' => $directory.'/'.$name.'.php',
                'old_definition' => 'database/seeders/V3/'.$oldName.'/definition.json',
                'old_class' => 'Database\\Seeders\\V3\\'.str_replace('/', '\\', $oldName),
                'old_saved_test_uuid' => self::ORIGINALS[$kind][0], 'old_sha256' => self::ORIGINALS[$kind][1]];
        }
        return $map;
    }

    protected static function composeSlots(array $author): array
    {
        return FinitePracticeChoiceOptions::reviewedSlots($author);
    }

    public static function compile(string $topic, string $kind): array
    {
        static $compiled = [];
        $source = self::source($topic); self::originalDefinition($topic, $kind);
        $key = $topic.'|'.$kind.'|'.hash('sha256', json_encode($source, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        if (isset($compiled[$key])) { return $compiled[$key]; }
        $definition = parent::compileAuthored($topic, $kind);
        return $compiled[$key] = $kind === 'builder'
            ? FinitePracticeChoiceOptions::compile($definition, $source, $topic, self::composeSlots(...))
            : $definition;
    }

    public static function decorate(array $payload): array
    {
        $payload = parent::decorate($payload);
        $source = self::questions()[$payload['uuid'] ?? ''] ?? null;
        if ($source && (string) $source['type'] === '0' && ($payload['question'] ?? null) === $source['question']
            && ($payload['psp_content_revision'] ?? null) === hash('sha256', json_encode($source, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            && ($payload['answer_map'] ?? null) === array_map(static fn ($marker) => $marker['answer'], $source['markers'])) {
            // The existing shared engine then requires every word of the phrase.
            $payload['passive_phrase_word_inputs'] = true;
        }
        return $payload;
    }

    public static function cachedNeedsRefresh(array $payloads): bool
    {
        if (parent::cachedNeedsRefresh($payloads)) { return true; }
        $sources = self::questions();
        foreach ($payloads as $payload) {
            $source = $sources[$payload['uuid'] ?? ''] ?? null;
            if ($source && (string) $source['type'] === '0' && ($payload['passive_phrase_word_inputs'] ?? null) !== true) { return true; }
        }
        return false;
    }

    /** The old72 link rows remain historical; only this exact new UK card displays its active18-row bank. */
    public static function savedBuilderDisplayCount(string $uuid, string $slug, array $filters, string $locale): ?int
    {
        if ($locale !== 'uk' || $uuid !== self::ORIGINALS['builder'][0]) { return null; }
        try {
            $original = self::originalDefinition('comparison', 'builder')['saved_test'];
            if ($slug !== $original['slug'] || self::stateNamespace($filters, $locale, $uuid) === null) { return null; }
            foreach (['slug', 'category_slug_path', 'page_seeder_class'] as $key) {
                $path = 'prompt_generator.theory_page.'.$key;
                if (Arr::get($filters, $path) !== Arr::get($original['filters'], $path)) { return null; }
            }
            return count(self::compile('comparison', 'builder')['questions']);
        } catch (Throwable) { return null; }
    }
}
