<?php

namespace App\Support;

use App\Models\Page;
use RuntimeException;

/** Finite M61 data policy. Pending hashes cannot activate a draft release. */
final class M61ReportedSpeechChangesPackage
{
    public const BASE_SHA = 'd60a288565f841dceefc16ac6ee10fa5c8c55395';
    public const MASTER = 'docs/content/m61-reported-speech-changes.v1.0.0.json';
    public const MASTER_SHA = 'b7bc95d9e8a2aedf5303d5c44b995ceaaa49eeec56ecf7804562c04f6d57afa4';
    public const BEFORE = 'database/content-patches/m61-reported-speech-changes-before.v1.json';
    public const BEFORE_SHA = 'da392078eead44cb058e98b1d45496771cc44352f57eb4c9ce4c771372b0b6af';
    public const SOURCE = 'database/content-patches/m61-reported-speech-changes.v1.json';
    public const SOURCE_SHA = '9abc29dd8b2ec736e62ec5b9ccbecaf011526b8ab89e91624cd4c2e1263ca79c';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m61-reported-speech-changes.v1.json';
    public const CONTRACT_SHA = 'cfc3290513297530d6d4848b844e7bf1614023e3fbec808cacac4570978f95cf';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechBackshiftTheorySeeder',
        'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechTimePlaceChangesTheorySeeder',
        'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechSayTellAskTheorySeeder',
    ];
    public const SLUGS = ['backshift-of-tenses', 'time-and-place-changes', 'say-tell-ask'];
    public const PAGE_IDS = [141, 145, 144];
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
    public const RENDERED_TITLES = ['Backshift of Tenses', 'Time and Place Changes', 'Say / Tell / Ask'];

    public static function profile(): array
    {
        $bindings = []; $prefixes = ['backshift', 'time-place', 'say-tell-ask'];
        $first = [
            ['1. Найтиповіші зміни', 'comparison-table', '2. Коли backshift є типовим, а коли ні', 'usage-panels'],
            ['1. Найуживаніші зміни', 'comparison-table', '2. Коли зміни справді потрібні', 'usage-panels'],
            ['1. Основна різниця', 'forms-grid', '2. Приклади вживання', 'comparison-table'],
        ];
        foreach (self::OWNERS as $i => $owner) {
            $binding = ['identity' => $owner, 'slug' => self::SLUGS[$i], 'page_id' => self::PAGE_IDS[$i],
                'category_id' => self::CATEGORY_IDS[$i], 'ancestry' => self::ANCESTRIES[$i],
                'local_url' => 'http://gramlyze.loc/theory/reported-speech/'.self::SLUGS[$i],
                'practice_level' => self::LEVELS[$i], 'display_level' => self::DISPLAY_LEVELS[$i],
                'rendered_title' => self::RENDERED_TITLES[$i], 'continuing_levels' => self::CONTINUING_LEVELS[$i]];
            $slots = [
                1 => ['column' => 'left', 'heading' => $first[$i][0], 'type' => $first[$i][1]],
                2 => ['column' => 'left', 'heading' => $first[$i][2], 'type' => $first[$i][3]],
                3 => ['column' => 'right', 'heading' => '3. Типові помилки', 'type' => 'mistakes-grid'],
                4 => ['column' => 'right', 'heading' => '4. Коротке правило', 'type' => 'summary-list'],
            ];
            foreach ($slots as $slot => $config) {
                $binding['continuing_metadata'][$slot] = ['column' => $config['column'],
                    'before' => ['type' => 'box', 'heading' => $config['heading'], 'level' => null],
                    'after' => ['type' => $config['type'], 'heading' => null, 'level' => null]];
                $binding['preserved_native_metadata'][$slot] = ['column' => $config['column'], 'level' => null];
            }
            $binding['appended_blocks'] = [
                ['key' => $prefixes[$i].'-practice', 'type' => 'practice-set', 'column' => 'left', 'level' => 'B1'],
            ];
            $bindings[] = $binding;
        }
        return ['label' => 'M61', 'base_sha' => self::BASE_SHA, 'version' => '1.0.0',
            'marker' => 'm61_native_v1', 'detail_marker' => 'm61_detail_key', 'uuid_prefix' => 'm61:', 'design' => 'm61-native-v1',
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
                            throw new RuntimeException('M61 native x-text task.feedback must be plain text, without tags or entities.');
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
