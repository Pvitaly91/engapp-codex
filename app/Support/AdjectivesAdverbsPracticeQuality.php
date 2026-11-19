<?php

namespace App\Support;

use InvalidArgumentException;

/** Exact UK-only adjective/adverb practice banks; historical rows and courses survive. */
final class AdjectivesAdverbsPracticeQuality extends FinitePracticeQualityCatalog
{
    public const REVISION = 'aaq-v1';
    public const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
    public const TOPICS = [
        'adverbs' => 'AdjectivesVsAdverbs', 'degrees' => 'DegreesOfComparison',
        'comparative' => 'ComparativeVsSuperlative', 'equality' => 'EqualityComparisonAsAsNotAsAs',
        'order' => 'OrderOfAdjectives', 'eding' => 'EdIngAdjectives', 'intensifiers' => 'Intensifiers',
    ];
    protected const UUID_PREFIX = 'aaq-';
    protected const SOURCE_PREFIX = 'adjectives_adverbs_quality_';
    protected const NAMESPACE_PREFIX = 'adjectives-adverbs-quality-';
    protected const LABEL = 'Adjectives and Adverbs Quality';
    protected const META_PREFIX = 'adjectives_adverbs_quality';
    public const CATEGORY_ID = 52;
    public const CATEGORY_SLUG = 'prykmetniky-ta-pryslinknyky';
    public const CATEGORY_TOPICS = ['adverbs', 'degrees', 'comparative', 'equality', 'order', 'eding', 'intensifiers'];
    public const SAVED_TEST_COUNT = 14;

    private const ORIGINALS = [
        'adverbs' => [
            'standard' => ['adjvsadv-v3-all', 'dfa689ee0bac1b9166c56d0621f6d9e166bcc5aa0c7430318064a4ff4c7ef862'],
            'builder' => ['poly-adjvsadv-all', '7cb8352f69ab85f09a3c707f3c695e1030dedf0634097c4cebde3942e07a9a33']],
        'degrees' => [
            'standard' => ['degcomp-v3-all', 'd25bed2136fc81731617868cfa7addf0f44c53730621b6058b280f96f663b9e2'],
            'builder' => ['poly-degcomp-all', '800ed0c1f098c3f2ce8e922d6368dd591c12cff30620dae0012aefcc94d30765']],
        'comparative' => [
            'standard' => ['compvssup-v3-all', '746f134544fcf322ee5a5655b567b048719430b9e0d01fca6d46139cd748598f'],
            'builder' => ['poly-compvssup-all', '4d8e6a6a2b7853706f87b80a2a670a4dba3ba504e38b5255de562e6a7d6ac3f7']],
        'equality' => [
            'standard' => ['eqcomp-v3-all', '609a53dee653d171170be9048a1018581d83d578ab5c91d749f44da804b36490'],
            'builder' => ['poly-eqcomp-all', '7640f87bc22c8699355c846c873b387416be6a0f8c63a7316bc9e03faa59d7c7']],
        'order' => [
            'standard' => ['ordadj-v3-all', '510eb82d8d06e9586e39672fef55f2479395a646448960c74cc9d02f7541a26d'],
            'builder' => ['poly-ordadj-all', '65be1168718dc03380cba44cc1467d12b60763a2576493de2f3bad29978d3dd1']],
        'eding' => [
            'standard' => ['eding-v3-all', '4b562a21a6d9d8a1fbc474890ae415597b18a90aff64a2330590a63c34ca4554'],
            'builder' => ['poly-eding-all', '0e8e2fbba17d472d61c54baaab4ed3eae3748d07b0cb91335168a9ca7693968b']],
        'intensifiers' => [
            'standard' => ['intens-v3-all', '152e4aff04274a4d74f20c0f42acfacf2d9a86327a9e9973a8b049a8017fbe32'],
            'builder' => ['poly-intens-all', 'ccb808bcbe43afd4c721a48a2160542679210f2ce1104fd734fcdbbd3d6e9e65']],
    ];

    public static function mapping(string $topic): array
    {
        if (!isset(self::TOPICS[$topic])) { throw new InvalidArgumentException('Unknown adjective/adverb practice topic.'); }
        $stem = self::TOPICS[$topic];
        $map = ['topic' => $topic, 'stem' => $stem, 'author' => 'docs/content/adjectives-adverbs-quality/'.$topic.'.v1.json'];
        foreach (['standard', 'builder'] as $kind) {
            $namespace = $kind === 'standard' ? 'Adjectives' : 'Polyglot';
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
        $extra = match ($topic) {
            'degrees' => ['PolyglotComparativesLessonSeeder', '7e46c84c59fc77903da0795664fa03838366f24cc1429a5d5317b262cb82a9d2'],
            'comparative' => ['PolyglotSuperlativesLessonSeeder', 'cea5e52898927281ed0c2ee4a5aaa861ed9f6e15cf666fcbf81ace5d9d8e20ee'],
            default => null,
        };
        if ($extra !== null) {
            $class = 'Database\\Seeders\\V3\\Polyglot\\'.$extra[0];
            $map['additional_originals'] = [['path' => 'database/seeders/V3/Polyglot/'.$extra[0].'/definition.json',
                'class' => $class, 'sha256' => $extra[1], 'kind' => 'builder']];
            // These are page-only locale overrides, never metadata patches of the course tests.
            $map['additional_locale_overrides'] = [$class => $map['builder']['class']];
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

    /** Exact authored Ukrainian lexical clues, including after cached DB hint localization. */
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
            // Display-only opt-in: canonical tokens, answers and aliases retain every byte.
            $payload['aaq_option_caption_revision'] = $payload['psp_content_revision'];
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
            if (($payload['aaq_option_caption_revision'] ?? null) !== hash('sha256', json_encode($source, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))) { return true; }
            if ((string) $source['type'] === '0' && ($payload['passive_phrase_word_inputs'] ?? null) !== true) { return true; }
            $expected = self::restoreClarifiedHints($payload, 'uk');
            foreach (['verb_hint', 'verb_hints', 'hint'] as $field) {
                if (array_key_exists($field, $expected) && ($payload[$field] ?? null) !== $expected[$field]) { return true; }
            }
        }
        return false;
    }

    /** Page-owned builder cards display the active finite count, not historical link-row count. */
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
