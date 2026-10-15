<?php

namespace App\Support;

use App\Models\Page;

/** Finite M57 data policy. Unreviewed hashes deliberately cannot load a release. */
final class M57ContinuousPerfectPassivesPackage
{
    public const BASE_SHA = 'b64e338e7dd0e515d4fcb72794c38a8c4f721936';
    public const MASTER = 'docs/content/m57-continuous-perfect-passives.v1.0.0.json';
    public const MASTER_SHA = '37e3e95007325cb18b5624b3297dc64813483bda5d93e8d719431661c813987a';
    public const BEFORE = 'database/content-patches/m57-continuous-perfect-passives-before.v1.json';
    public const BEFORE_SHA = '636fd63174a3f193abb1fea7303c025e03597e39fca82593c06e2aa03eaec915';
    public const SOURCE = 'database/content-patches/m57-continuous-perfect-passives.v1.json';
    public const SOURCE_SHA = 'ef01d5c8b58e3fc04b5df04297bc86445eb590bf927a36d1e4be188130f78e11';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m57-continuous-perfect-passives.v1.json';
    public const CONTRACT_SHA = '790b015f58773626de9b19848fd93d119e579e7e4c986b5fcefefc7315d23fdb';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePresentContinuousTheorySeeder',
        'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePastContinuousTheorySeeder',
        'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePresentPerfectTheorySeeder',
    ];
    public const SLUGS = ['theory-passive-voice-present-continuous', 'theory-passive-voice-past-continuous', 'theory-passive-voice-present-perfect'];
    public const PAGE_IDS = [111, 108, 112];
    public const CATEGORY_IDS = [18, 18, 18];
    public const ANCESTRIES = [['passive-voice', 'passive-voice-tenses'], ['passive-voice', 'passive-voice-tenses'], ['passive-voice', 'passive-voice-tenses']];
    public const LEVELS = ['B1', 'B1', 'B1'];
    public const DISPLAY_LEVELS = ['B1', 'B1', 'B1'];
    public const CONTINUING_COUNTS = [12, 12, 12];
    public const CONTINUING_LEVELS = [
        ['B1', 'B1', 'B1', 'B1', 'B1', 'B1', 'B1', 'B1', 'B1', 'B1', 'B1', null],
        ['B1', 'B1', 'B1', 'B1', 'B1', 'B1', 'B1', 'B1', 'B1', 'B1', 'B1', null],
        ['B1', 'B1', 'B1', 'B1', 'B1', 'B1', 'B1', 'B1', 'B1', 'B1', 'B1', null],
    ];
    public const RENDERED_TITLES = ['Present Continuous Passive', 'Past Continuous Passive', 'Present Perfect Passive'];

    public static function profile(): array
    {
        $bindings = [];
        foreach (self::OWNERS as $i => $owner) {
            $bindings[] = ['identity' => $owner, 'slug' => self::SLUGS[$i], 'page_id' => self::PAGE_IDS[$i],
                'category_id' => self::CATEGORY_IDS[$i], 'ancestry' => self::ANCESTRIES[$i],
                'local_url' => 'http://gramlyze.loc/theory/'.implode('/', self::ANCESTRIES[$i]).'/'.self::SLUGS[$i],
                'practice_level' => self::LEVELS[$i], 'display_level' => self::DISPLAY_LEVELS[$i],
                'rendered_title' => self::RENDERED_TITLES[$i], 'continuing_levels' => self::CONTINUING_LEVELS[$i]];
        }
        return ['label' => 'M57', 'base_sha' => self::BASE_SHA, 'version' => '1.0.0',
            'marker' => 'm57_native_v1', 'detail_marker' => 'm57_detail_key', 'uuid_prefix' => 'm57:', 'design' => 'm57-native-v1',
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
