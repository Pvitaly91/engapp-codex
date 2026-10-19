<?php

namespace App\Support;

use App\Models\Page;
use RuntimeException;

/** Finite M63 data policy. Pending hashes cannot activate a draft release. */
final class M63RelativeCompletionLinkingPackage
{
    public const BASE_SHA = 'c8502bc75b9d4258d79824a9be0b343ffbfc07f4';
    public const MASTER = 'docs/content/m63-relative-completion-linking.v1.0.0.json';
    public const MASTER_SHA = '3e9db52c673b8a5c3c04b04d59fdcf395a14371c9d5e37a8f7b7048a64d19e0c';
    public const BEFORE = 'database/content-patches/m63-relative-completion-linking-before.v1.json';
    public const BEFORE_SHA = 'c89d4606e03cb639f94a482586701a8de2933b68d04a3a845f17c7ea8f322d2f';
    public const SOURCE = 'database/content-patches/m63-relative-completion-linking.v1.json';
    public const SOURCE_SHA = 'b1351893c3669e9946e311c13fae07e6f378640bbc120b71256b2bcceb740a01';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m63-relative-completion-linking.v1.json';
    public const CONTRACT_SHA = '8676bb5932f1fe1efda6c97f20e17f010ffa87d2fd4b5e8e5fb62978e29fd96c';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsNonDefiningRelativeClausesTheorySeeder',
        'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsOmittingRelativePronounsTheorySeeder',
        'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsLinkingWordsTheorySeeder',
    ];
    public const SLUGS = ['non-defining-relative-clauses', 'omitting-relative-pronouns', 'linking-words'];
    public const PAGE_IDS = [33, 34, 32];
    public const CATEGORY_IDS = [34, 34, 34];
    public const ANCESTRIES = [['clauses-and-linking-words'], ['clauses-and-linking-words'], ['clauses-and-linking-words']];
    public const LEVELS = ['B1', 'B1', 'B1'];
    public const DISPLAY_LEVELS = ['B1-B2', 'B1-B2', 'B1-B2'];
    public const CONTINUING_COUNTS = [7, 7, 7];
    public const CONTINUING_LEVELS = [
        ['B1', null, null, null, null, null, null],
        ['B1', null, null, null, null, null, null],
        ['B1', null, null, null, null, null, null],
    ];
    public const RENDERED_TITLES = ['Non-defining Relative Clauses', 'Omitting Relative Pronouns', 'Linking Words'];

    public static function profile(): array
    {
        $bindings = [];
        $slots = [
            [
                1 => ['left', '1. Коли використовуємо non-defining clause', 'usage-panels'],
                2 => ['left', '2. Типова форма', 'forms-grid'],
                3 => ['right', '3. Як читати коми', 'comparison-table'],
                4 => ['right', '4. Типові помилки', 'mistakes-grid'],
                5 => ['right', '5. Коротке правило', 'summary-list'],
            ],
            [
                1 => ['left', '1. Коли omission можливий', 'usage-panels'],
                2 => ['left', '2. Коли omission неможливий', 'forms-grid'],
                3 => ['right', '3. Порівняй приклади', 'comparison-table'],
                4 => ['right', '4. Типові помилки', 'mistakes-grid'],
                5 => ['right', '5. Коротке правило', 'summary-list'],
            ],
            [
                1 => ['left', '1. Основні сімейства linking words', 'usage-panels'],
                2 => ['left', '2. Позиція в реченні', 'forms-grid'],
                3 => ['right', '3. Пунктуація і стиль', 'comparison-table'],
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
            $binding['appended_blocks'] = [['key' => ['nondefining', 'omitting', 'linking'][$i].'-practice',
                'type' => 'practice-set', 'column' => 'left', 'level' => 'B1']];
            $bindings[] = $binding;
        }
        return ['label' => 'M63', 'base_sha' => self::BASE_SHA, 'version' => '1.0.0',
            'marker' => 'm63_native_v1', 'detail_marker' => 'm63_detail_key', 'uuid_prefix' => 'm63:', 'design' => 'm63-native-v1',
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
        if (($before['content_type'] ?? null) !== 'M63_source_before'
            || ($patch['content_type'] ?? null) !== 'M63_Relative_Completion_Linking') {
            throw new RuntimeException('M63 new provenance/content_type must name this exact release.');
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
                            throw new RuntimeException('M63 native x-text task.feedback must be plain text, without tags or entities.');
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
