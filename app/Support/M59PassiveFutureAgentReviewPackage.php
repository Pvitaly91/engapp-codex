<?php

namespace App\Support;

use App\Models\Page;

/** Finite M59 data policy. Unreviewed hashes deliberately cannot load a release. */
final class M59PassiveFutureAgentReviewPackage
{
    public const BASE_SHA = '5c314ccfedaba52934cdfd7cae3aef3ce7f1aa89';
    public const MASTER = 'docs/content/m59-passive-future-agent-review.v1.0.0.json';
    public const MASTER_SHA = '8d00aa44ff6d97c45c394b5693f3b25ce3d9b0d01c840733094315de3f0669e8';
    public const BEFORE = 'database/content-patches/m59-passive-future-agent-review-before.v1.json';
    public const BEFORE_SHA = '7ec7848cb6e75af866fd0407c6634f6d899040b7ee2f8f17f7ee677f0547cf52';
    public const SOURCE = 'database/content-patches/m59-passive-future-agent-review.v1.json';
    public const SOURCE_SHA = '9c6cee80f68867718f68ca2b0c2ad1d69370b917b82c91a3d718f7db3f514575';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m59-passive-future-agent-review.v1.json';
    public const CONTRACT_SHA = '7746c3e0f55354269e87351af71b592db18894266fed2b00b17b83c734c19429';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoiceFutureContinuousTheorySeeder',
        'Database\\Seeders\\Page_V3\\PassiveVoice\\SpecialCases\\PassiveVoiceByPhraseTheorySeeder',
        'Database\\Seeders\\Page_V3\\PassiveVoice\\AdvancedPassiveVoiceTheorySeeder',
    ];
    public const SLUGS = ['theory-passive-voice-future-continuous', 'theory-passive-voice-by-phrase', 'advanced-passive-voice'];
    public const PAGE_IDS = [105, 104, 282];
    public const CATEGORY_IDS = [18, 1, 1];
    public const ANCESTRIES = [['passive-voice', 'passive-voice-tenses'], ['passive-voice'], ['passive-voice']];
    public const LEVELS = ['B2', 'B1', 'B2'];
    public const DISPLAY_LEVELS = ['B2', 'B1', 'B2'];
    public const CONTINUING_COUNTS = [11, 8, 2];
    public const CONTINUING_LEVELS = [
        ['B2', 'B2', 'B2', 'B2', 'B2', 'B2', 'B2', 'B2', 'B2', 'B2', null],
        ['B1', 'B1', 'B1', 'B2', 'B1', 'B1', 'B1', null],
        ['B2', null],
    ];
    public const RENDERED_TITLES = ['Future Continuous Passive', 'By-phrase', 'Advanced Passive Voice'];

    public static function profile(): array
    {
        $bindings = [];
        foreach (self::OWNERS as $i => $owner) {
            $bindings[] = ['identity' => $owner, 'slug' => self::SLUGS[$i], 'page_id' => self::PAGE_IDS[$i],
                'category_id' => self::CATEGORY_IDS[$i], 'ancestry' => self::ANCESTRIES[$i],
                'local_url' => 'http://gramlyze.loc/theory/'.implode('/', self::ANCESTRIES[$i]).'/'.self::SLUGS[$i],
                'practice_level' => self::LEVELS[$i], 'display_level' => self::DISPLAY_LEVELS[$i],
                'rendered_title' => self::RENDERED_TITLES[$i], 'continuing_levels' => self::CONTINUING_LEVELS[$i]];
            if ($i === 2) {
                $bindings[$i]['continuing_metadata'] = [1 => [
                    'column' => 'left',
                    'before' => ['type' => 'box', 'heading' => 'B2 focus', 'level' => null],
                    'after' => ['type' => 'usage-panels', 'heading' => null, 'level' => 'B2'],
                ]];
                // Explicit reviewed Advanced teaching/navigation/practice slot vector.
                $bindings[$i]['appended_blocks'] = [
                    ['key' => 'advanced-forms', 'type' => 'forms-grid', 'column' => 'left', 'level' => 'B2'],
                    ['key' => 'advanced-aspect', 'type' => 'usage-panels', 'column' => 'left', 'level' => 'B2'],
                    ['key' => 'advanced-modals', 'type' => 'usage-panels', 'column' => 'left', 'level' => 'B2'],
                    ['key' => 'advanced-get', 'type' => 'usage-panels', 'column' => 'left', 'level' => 'B2'],
                    ['key' => 'advanced-reporting', 'type' => 'usage-panels', 'column' => 'left', 'level' => 'B2'],
                    ['key' => 'advanced-comparison', 'type' => 'comparison-table', 'column' => 'left', 'level' => 'B2'],
                    ['key' => 'advanced-summary', 'type' => 'summary-list', 'column' => 'left', 'level' => 'B2'],
                    ['key' => 'advanced-navigation', 'type' => 'navigation-chips', 'column' => 'footer', 'level' => null],
                    ['key' => 'advanced-practice', 'type' => 'practice-set', 'column' => 'left', 'level' => 'B2'],
                ];
            }
        }
        return ['label' => 'M59', 'base_sha' => self::BASE_SHA, 'version' => '1.0.0',
            'marker' => 'm59_native_v1', 'detail_marker' => 'm59_detail_key', 'uuid_prefix' => 'm59:', 'design' => 'm59-native-v1',
            'artifacts' => [self::BEFORE => self::BEFORE_SHA, self::SOURCE => self::SOURCE_SHA,
                self::MASTER => self::MASTER_SHA, self::CONTRACT => self::CONTRACT_SHA], 'bindings' => $bindings];
    }

    private static function release(): TheoryNativeContentRelease { return new TheoryNativeContentRelease(self::profile()); }
    public static function load(?string $root = null): array { return self::release()->load($root); }
    public static function definition(array $lesson, array $before): array { return self::release()->definition($lesson, $before); }
    public static function validate(array $before, array $patch, array $master, ?array $contract = null): void { self::release()->validate($before, $patch, $master, $contract); }
    public static function presentation(object $block, array $data): ?array { return self::release()->presentation($block, $data); }
    public static function validDesign(?array $design): bool { return self::release()->validDesign($design); }
    public static function pointPanels(?array $design): ?array { return self::release()->pointPanels($design); }
    public static function correction(array $item, ?array $design): ?array { return self::release()->correction($item, $design); }
    public static function preserveExcludedTextBlock(object $block, string $caller): ?object { return self::release()->preserveExcludedTextBlock($block, $caller); }
    public static function preserveCoursePage(?Page $page): ?Page { return self::release()->preserveCoursePage($page); }
}
