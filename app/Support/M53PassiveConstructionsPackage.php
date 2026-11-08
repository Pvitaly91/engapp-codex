<?php

namespace App\Support;

use App\Models\Page;

/** Finite M53 declaration; rendering, serialisation and preservation are shared. */
final class M53PassiveConstructionsPackage
{
    public const BASE_SHA = 'ba489be880d9a7c2b3dbc1698e887a05eb166fbe';
    public const MASTER = 'docs/content/m53-passive-constructions.v1.0.0.json';
    public const MASTER_SHA = 'b140410b36b3e619fb6672cade249f9cf7fc1913a63993c88e35262b260f54d3';
    public const BEFORE = 'database/content-patches/m53-passive-constructions-before.v1.json';
    public const BEFORE_SHA = '052e8c1390339103d8a3e9d144e5361696213b57ce75ae52722dc58319017883';
    public const SOURCE = 'database/content-patches/m53-passive-constructions.v1.json';
    public const SOURCE_SHA = 'bf386f8f1880921e7dc144dec695e30d9da0335bf277b08aef6f35d37e0dca7d';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m53-passive-constructions.v1.json';
    public const CONTRACT_SHA = '883241d932a2f8dfeee5089ac3c0a4b55cb621e6cf941150415c21fa1dd57531';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceCausativeTheorySeeder',
        'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceGetPassiveTheorySeeder',
        'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceImpersonalPassiveTheorySeeder',
    ];
    public const SLUGS = ['theory-passive-voice-causative', 'theory-passive-voice-get-passive', 'theory-passive-voice-impersonal-passive'];
    public const PAGE_IDS = [92, 95, 96]; // New independent local BEFORE, not source-order guesses.
    public const CATEGORY_IDS = [1, 1, 1];
    public const LEVELS = ['B1', 'B1', 'B2'];
    public const DISPLAY_LEVELS = ['B1–B2', 'B1', 'B2–C1'];
    public const CONTINUING_COUNTS = [10, 9, 10];
    public const CONTINUING_LEVELS = [
        ['B1', 'B1', 'B1', 'B1', 'B1', 'B2', 'B1', 'B1', 'B1', null],
        ['B1', 'B1', 'B1', 'B1', 'B2', 'B1', 'B1', 'B1', null],
        ['B2', 'B2', 'B2', 'B2', 'B2', 'B2', 'B2', 'B2', 'B2', null],
    ];
    public const RENDERED_TITLES = ['Каузатив (Causative)', 'Get-passive', 'Безособовий пасив (Impersonal Passive)'];

    public static function profile(): array
    {
        $bindings = [];
        foreach (self::OWNERS as $i => $owner) {
            $bindings[] = ['identity' => $owner, 'slug' => self::SLUGS[$i], 'page_id' => self::PAGE_IDS[$i],
                'category_id' => self::CATEGORY_IDS[$i], 'ancestry' => ['passive-voice'],
                'local_url' => 'http://gramlyze.loc/theory/passive-voice/'.self::SLUGS[$i],
                'practice_level' => self::LEVELS[$i], 'display_level' => self::DISPLAY_LEVELS[$i],
                'rendered_title' => self::RENDERED_TITLES[$i], 'continuing_levels' => self::CONTINUING_LEVELS[$i]];
        }
        return ['label' => 'M53', 'base_sha' => self::BASE_SHA, 'version' => '1.0.0',
            'marker' => 'm53_native_v1', 'detail_marker' => 'm53_detail_key', 'uuid_prefix' => 'm53:', 'design' => 'm53-native-v1',
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
