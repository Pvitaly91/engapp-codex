<?php

namespace App\Support;

use App\Models\Page;
use RuntimeException;

/** Finite M62 data policy. Pending hashes cannot activate a draft release. */
final class M62ReportedReviewRelativeBasicsPackage
{
    public const BASE_SHA = '17572e80bad9b968fd40f394d012cb0414a35ccf';
    public const MASTER = 'docs/content/m62-reported-review-relative-basics.v1.0.0.json';
    public const MASTER_SHA = 'd269da15989c7e3c89eb3cda133901b7333e66b82cf2d1fe1dd2596c522353fe';
    public const BEFORE = 'database/content-patches/m62-reported-review-relative-basics-before.v1.json';
    public const BEFORE_SHA = '59cddc12d615922751eaac37e35fb40fcc0a44730dda1b535a831fbe127b35b1';
    public const SOURCE = 'database/content-patches/m62-reported-review-relative-basics.v1.json';
    public const SOURCE_SHA = 'ba6bab4ae430976097d60835bb5ad5dffdf927211e9a9c27b81b8f5ee76c1904';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m62-reported-review-relative-basics.v1.json';
    public const CONTRACT_SHA = '77a99a050162e14ec8370545e2f291eeeb90fdc26ca47285c65aced08a2004c6';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\ReportedSpeech\\AdvancedReportedSpeechTheorySeeder',
        'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsRelativeClausesTheorySeeder',
        'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsDefiningRelativeClausesTheorySeeder',
    ];
    public const SLUGS = ['advanced-reported-speech', 'relative-clauses', 'defining-relative-clauses'];
    public const PAGE_IDS = [283, 202, 31];
    public const CATEGORY_IDS = [3, 34, 34];
    public const ANCESTRIES = [['reported-speech'], ['clauses-and-linking-words'], ['clauses-and-linking-words']];
    public const LEVELS = ['B2', 'B1', 'B1'];
    public const DISPLAY_LEVELS = ['B2', 'B1-B2', 'B1-B2'];
    public const CONTINUING_COUNTS = [2, 7, 7];
    public const CONTINUING_LEVELS = [
        ['B2', null],
        ['B1', null, null, null, null, null, null],
        ['B1', null, null, null, null, null, null],
    ];
    public const RENDERED_TITLES = ['Advanced Reported Speech', 'Relative Clauses', 'Defining Relative Clauses'];

    public static function profile(): array
    {
        $bindings = [];
        $slots = [
            [1 => ['left', 'B2 focus', 'forms-grid']],
            [
                1 => ['left', '1. Що таке relative clause', 'usage-panels'],
                2 => ['left', '2. Основні слова', 'comparison-table'],
                3 => ['right', '3. Subject clause vs object clause', 'usage-panels'],
                4 => ['right', '4. Типові помилки', 'mistakes-grid'],
                5 => ['right', '5. Коротке правило', 'summary-list'],
            ],
            [
                1 => ['left', '1. Коли використовуємо defining clause', 'usage-panels'],
                2 => ['left', '2. Типова форма', 'forms-grid'],
                3 => ['right', '3. Як змінюється значення', 'comparison-table'],
                4 => ['right', '4. Типові помилки', 'mistakes-grid'],
                5 => ['right', '5. Коротке правило', 'summary-list'],
            ],
        ];
        foreach (self::OWNERS as $i => $owner) {
            $binding = ['identity' => $owner, 'slug' => self::SLUGS[$i], 'page_id' => self::PAGE_IDS[$i],
                'category_id' => self::CATEGORY_IDS[$i], 'ancestry' => self::ANCESTRIES[$i],
                'local_url' => 'http://gramlyze.loc/theory/'.implode('/', self::ANCESTRIES[$i]).'/'.self::SLUGS[$i],
                'practice_level' => self::LEVELS[$i], 'display_level' => self::DISPLAY_LEVELS[$i],
                'rendered_title' => self::RENDERED_TITLES[$i], 'continuing_levels' => self::CONTINUING_LEVELS[$i]];
            foreach ($slots[$i] as $slot => [$column, $heading, $type]) {
                $binding['continuing_metadata'][$slot] = ['column' => $column,
                    'before' => ['type' => 'box', 'heading' => $heading, 'level' => null],
                    'after' => ['type' => $type, 'heading' => null, 'level' => null]];
                $binding['preserved_native_metadata'][$slot] = ['column' => $column, 'level' => null];
            }
            $binding['appended_blocks'] = $i === 0 ? [
                ['key' => 'advanced-reported-decisions', 'type' => 'usage-panels', 'column' => 'left', 'level' => 'B2'],
                ['key' => 'advanced-reported-precision', 'type' => 'mistakes-grid', 'column' => 'left', 'level' => 'B2'],
                ['key' => 'advanced-reported-summary', 'type' => 'summary-list', 'column' => 'left', 'level' => 'B2'],
                ['key' => 'advanced-reported-navigation', 'type' => 'navigation-chips', 'column' => 'footer', 'level' => null],
                ['key' => 'advanced-reported-practice', 'type' => 'practice-set', 'column' => 'left', 'level' => 'B2'],
            ] : [['key' => ($i === 1 ? 'relative' : 'defining').'-practice',
                'type' => 'practice-set', 'column' => 'left', 'level' => 'B1']];
            $bindings[] = $binding;
        }
        return ['label' => 'M62', 'base_sha' => self::BASE_SHA, 'version' => '1.0.0',
            'marker' => 'm62_native_v1', 'detail_marker' => 'm62_detail_key', 'uuid_prefix' => 'm62:', 'design' => 'm62-native-v1',
            'artifacts' => [self::BEFORE => self::BEFORE_SHA, self::SOURCE => self::SOURCE_SHA,
                self::MASTER => self::MASTER_SHA, self::CONTRACT => self::CONTRACT_SHA], 'bindings' => $bindings];
    }

    private static function release(): TheoryNativeContentRelease { return new TheoryNativeContentRelease(self::profile()); }
    public static function load(?string $root = null): array
    {
        $data = self::release()->load($root);
        foreach ($data[2]['lessons'] ?? [] as $lesson) { self::plainFeedback($lesson); }
        return $data;
    }
    public static function definition(array $lesson, array $before): array
    {
        self::plainFeedback($lesson);
        return self::release()->definition($lesson, $before);
    }
    public static function validate(array $before, array $patch, array $master, ?array $contract = null): void
    {
        if (($before['content_type'] ?? null) !== 'M62_source_before'
            || ($patch['content_type'] ?? null) !== 'M62_Reported_Review_Relative_Basics') {
            throw new RuntimeException('M62 new provenance/content_type must name this exact release.');
        }
        foreach ($master['lessons'] ?? [] as $lesson) { self::plainFeedback($lesson); }
        self::release()->validate($before, $patch, $master, $contract);
    }

    /** Author-source rejection only: no tag stripping, HTML decoding or runtime data mutation. */
    private static function plainFeedback(array $lesson): void
    {
        foreach ($lesson['blocks'] ?? [] as $block) {
            if (($block['type'] ?? null) !== 'practice-set') { continue; }
            foreach (['selects', 'choices', 'inputs'] as $group) {
                foreach ($block['data'][$group] ?? [] as $task) {
                    foreach ($task['feedback'] ?? [] as $text) {
                        if (!is_string($text)
                            || preg_match('/<(?:\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu', $text)) {
                            throw new RuntimeException('M62 native x-text task.feedback must be plain text, without tags or entities.');
                        }
                    }
                }
            }
        }
    }

    public static function presentation(object $block, array $data): ?array { return self::release()->presentation($block, $data); }
    public static function validDesign(?array $design): bool { return self::release()->validDesign($design); }
    public static function pointPanels(?array $design): ?array { return self::release()->pointPanels($design); }
    public static function correction(array $item, ?array $design): ?array { return self::release()->correction($item, $design); }
    public static function preserveExcludedTextBlock(object $block, string $caller): ?object { return self::release()->preserveExcludedTextBlock($block, $caller); }
    public static function preserveCoursePage(?Page $page): ?Page { return self::release()->preserveCoursePage($page); }
}
