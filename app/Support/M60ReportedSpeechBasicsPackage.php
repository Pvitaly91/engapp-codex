<?php

namespace App\Support;

use App\Models\Page;

/** Finite M60 data policy. Unreviewed hashes deliberately cannot load a release. */
final class M60ReportedSpeechBasicsPackage
{
    public const BASE_SHA = '69994e34da410c66d6807a0921b588f85b81908c';
    public const MASTER = 'docs/content/m60-reported-speech-basics.v1.0.1.json';
    public const MASTER_SHA = 'aabc1bdc0501b1a495799c75c3f22cc93835cf838a6c32fa9937465cea17ab01';
    public const BEFORE = 'database/content-patches/m60-reported-speech-basics-before.v1.json';
    public const BEFORE_SHA = '34b4dd07f1d216f8a74c7123af1bab17f7f1e6bf5a97bcf7e94f80ee33bbdaee';
    public const SOURCE = 'database/content-patches/m60-reported-speech-basics.v1.0.1.json';
    public const SOURCE_SHA = '4807643565ffedd36b56a2fe201ceae27837e9b5b455fff4105a83b51f4c9f26';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m60-reported-speech-basics.v1.json';
    public const CONTRACT_SHA = '58ca869b0f34adb0c97f494780e9e267b6bec3f949fa7aabbf48516024647785';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechStatementsTheorySeeder',
        'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechQuestionsTheorySeeder',
        'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechCommandsRequestsTheorySeeder',
    ];
    public const SLUGS = ['reported-statements', 'reported-questions', 'reported-commands-and-requests'];
    public const PAGE_IDS = [3, 143, 142];
    public const CATEGORY_IDS = [3, 3, 3];
    public const ANCESTRIES = [['reported-speech'], ['reported-speech'], ['reported-speech']];
    public const LEVELS = ['B1', 'B1', 'B1'];
    public const DISPLAY_LEVELS = ['B1-B2', 'B1-B2', 'B1-B2'];
    public const CONTINUING_COUNTS = [6, 6, 6];
    public const CONTINUING_LEVELS = [
        ['B1', null, null, null, null, null],
        ['B1', null, null, null, null, null],
        ['B1', null, null, null, null, null],
    ];
    public const RENDERED_TITLES = ['Reported Statements', 'Reported Questions', 'Reported Commands and Requests'];

    public static function profile(): array
    {
        $bindings = []; $prefixes = ['statements', 'questions', 'commands'];
        $slots = [
            1 => ['column' => 'left', 'heading' => '1. Форма і структура', 'type' => 'forms-grid'],
            2 => ['column' => 'left', 'heading' => '2. Основні випадки вживання і приклади', 'type' => 'comparison-table'],
            3 => ['column' => 'right', 'heading' => '3. Типові помилки', 'type' => 'mistakes-grid'],
            4 => ['column' => 'right', 'heading' => '4. Коротке правило', 'type' => 'summary-list'],
        ];
        foreach (self::OWNERS as $i => $owner) {
            $binding = ['identity' => $owner, 'slug' => self::SLUGS[$i], 'page_id' => self::PAGE_IDS[$i],
                'category_id' => self::CATEGORY_IDS[$i], 'ancestry' => self::ANCESTRIES[$i],
                'local_url' => 'http://gramlyze.loc/theory/reported-speech/'.self::SLUGS[$i],
                'practice_level' => self::LEVELS[$i], 'display_level' => self::DISPLAY_LEVELS[$i],
                'rendered_title' => self::RENDERED_TITLES[$i], 'continuing_levels' => self::CONTINUING_LEVELS[$i]];
            foreach ($slots as $slot => $config) {
                $binding['continuing_metadata'][$slot] = ['column' => $config['column'],
                    'before' => ['type' => 'box', 'heading' => $config['heading'], 'level' => null],
                    'after' => ['type' => $config['type'], 'heading' => null, 'level' => null]];
                $binding['preserved_native_metadata'][$slot] = ['column' => $config['column'], 'level' => null];
            }
            $binding['appended_blocks'] = [
                ['key' => $prefixes[$i].'-context', 'type' => 'usage-panels', 'column' => 'left', 'level' => 'B1'],
                ['key' => $prefixes[$i].'-practice', 'type' => 'practice-set', 'column' => 'left', 'level' => 'B1'],
            ];
            $bindings[] = $binding;
        }
        return ['label' => 'M60', 'base_sha' => self::BASE_SHA, 'version' => '1.0.1',
            'marker' => 'm60_native_v1', 'detail_marker' => 'm60_detail_key', 'uuid_prefix' => 'm60:', 'design' => 'm60-native-v1',
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
