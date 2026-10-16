<?php

namespace App\Support;

use App\Models\TextBlock;
use RuntimeException;

/** Exact theory-only opt-in for the existing linked-bank widget; no stored data changes. */
final class PracticeCompletionLinkedPractice
{
    public const MANIFEST = 'docs/content/practice-completion-linked-widgets.v1.json';
    public const MANIFEST_SHA = 'af9883ee79793d3f0182fe2bd41e5846c905722de1e1b1f380073079c3f2ccc2';
    public const BANKS = [
        'Database\\Seeders\\V3\\Polyglot\\PolyglotZeroConditionalAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotFirstConditionalAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotSecondConditionalAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotThirdConditionalAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotMixedConditionalsAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotUnlessProvidedAsLongAsAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotInCaseAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotWishIfOnlyAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotWouldRatherAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotHadBetterAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotCanCouldAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotModalVerbsMayMightAllLevelsLessonSeeder',
    ];
    private const FIELDS = ['page_id', 'page_category_id', 'locale', 'type', 'column',
        'heading', 'level', 'css_class', 'sort_order', 'uuid'];

    /** Validate the complete closed manifest against the already frozen supplemental AFTER. */
    public static function load(?string $root = null): array
    {
        $root ??= base_path();
        $read = static function (string $path, string $sha) use ($root): array {
            $file = $root.'/'.$path;
            if (!is_file($file) || is_link($file)) {
                throw new RuntimeException('Linked practice source missing or linked: '.$path);
            }
            $bytes = file_get_contents($file);
            if (!is_string($bytes) || !hash_equals($sha, hash('sha256', $bytes))) {
                throw new RuntimeException('Linked practice source hash differs: '.$path);
            }
            $data = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
            if (!is_array($data)) { throw new RuntimeException('Linked practice source is not an object.'); }
            return $data;
        };
        $manifest = $read(self::MANIFEST, self::MANIFEST_SHA);
        $source = $read(PracticeCompletionPackage::SOURCE, PracticeCompletionPackage::SOURCE_SHA);
        if (array_column($source['targets'] ?? [], 'identity') !== PracticeCompletionPackage::OWNERS) {
            throw new RuntimeException('Linked practice source owner scope differs.');
        }
        $targets = [];
        foreach ($source['targets'] as $i => $target) {
            $practice = $target['after']['practice'];
            $targets[] = [
                'identity' => $target['identity'], 'slug' => $target['slug'],
                'page_id' => $target['page']['id'], 'page_category_id' => $target['page']['page_category_id'],
                'locale' => 'uk', 'type' => $practice['type'], 'column' => $practice['column'],
                'heading' => $practice['heading'], 'level' => $practice['level'],
                'css_class' => $practice['css_class'], 'sort_order' => $practice['sort_order'],
                'uuid' => $practice['uuid'], 'body_sha256' => hash('sha256', $practice['body']),
                'linked_practice' => [
                    'source' => 'theory_links', 'question_types' => ['4'], 'seeder_classes' => [self::BANKS[$i]],
                    'title' => 'Вправа 4. Побудуй речення',
                    'intro' => 'Склади англійське речення за українською умовою, клікаючи на слова.',
                    'footer' => 'Завдання підібрані лише для цієї теми.',
                ],
            ];
        }
        $expected = ['schema_version' => 1, 'source' => PracticeCompletionPackage::SOURCE,
            'source_sha256' => PracticeCompletionPackage::SOURCE_SHA, 'targets' => $targets];
        if ($manifest !== $expected) { throw new RuntimeException('Linked practice closed manifest differs.'); }
        return $manifest;
    }

    private static function config(object $block, array $data, bool $canonicalTheory): ?array
    {
        if (!$canonicalTheory || app()->getLocale() !== 'uk' || ($block->locale ?? null) !== 'uk'
            || ($block->type ?? null) !== 'practice-set'
            || !in_array($block->seeder ?? null, PracticeCompletionPackage::OWNERS, true)) {
            return null;
        }
        try {
            $body = $block->body ?? null;
            if (!is_string($body) || $data !== json_decode($body, true, flags: JSON_THROW_ON_ERROR)) { return null; }
            $i = array_search($block->seeder, PracticeCompletionPackage::OWNERS, true);
            $target = self::load()['targets'][$i];
            foreach (self::FIELDS as $field) {
                $actual = $field === 'sort_order' ? (int) ($block->$field ?? -1) : ($block->$field ?? null);
                if ($actual !== $target[$field]) { return null; }
            }
            if (!hash_equals($target['body_sha256'], hash('sha256', $body))) { return null; }
            return $target['linked_practice'];
        } catch (\Throwable) {
            return null;
        }
    }

    /** Presentation data only: the historical and supplemental learning payload stays exact. */
    public static function presentationData(object $block, array $data, bool $canonicalTheory = true): array
    {
        $config = self::config($block, $data, $canonicalTheory);
        if ($config !== null) { $data['linked_practice'] = $config; }
        return $data;
    }

    /** The controller passes only this unsaved copy to the unchanged page-wide matcher. */
    public static function matchingBlock(TextBlock $block, bool $canonicalTheory): TextBlock
    {
        $block = M56BuilderSelectionPresentation::matchingBlock($block, $canonicalTheory);
        $block = M59BuilderSelectionPresentation::matchingBlock($block, $canonicalTheory);
        $block = M60BuilderSelectionPresentation::matchingBlock($block, $canonicalTheory);
        $block = M61BuilderSelectionPresentation::matchingBlock($block, $canonicalTheory);
        $block = M62BuilderSelectionPresentation::matchingBlock($block, $canonicalTheory);
        try {
            $data = json_decode((string) $block->body, true, flags: JSON_THROW_ON_ERROR);
            if (!is_array($data)) { return $block; }
            $config = self::config($block, $data, $canonicalTheory);
            if ($config === null) { return $block; }
            $data['linked_practice'] = $config;
            $copy = clone $block;
            $copy->setRawAttributes(array_replace($block->getAttributes(), [
                'body' => M26DetailPackage::json($data),
            ]), true);
            return $copy;
        } catch (\Throwable) {
            return $block;
        }
    }
}
