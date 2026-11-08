<?php

namespace App\Support;

use App\Models\Page;
use RuntimeException;

/** Finite M69 data policy. Canonical components and accepted native primitives own presentation. */
final class M69AdjectivesAdverbs
{
    public const BASE_SHA = 'c41f65749e39768a69e62dff4e4b942ffa751fe4';
    public const MASTER = 'docs/content/m69-adjectives-adverbs.v1.0.1.json';
    public const MASTER_SHA = '662451a7f19cea5b59d5ca182bbae58b841251275cac8e48613b57d943222ea4';
    public const BEFORE = 'docs/content/m69-before-source.v1.json';
    public const BEFORE_SHA = '9e0ac9bb0da1ba1c0af834f876239a89bef369123c1f6c146955e44f3aa8430c';
    public const SOURCE = 'database/content-patches/m69-adjectives-adverbs.v1.0.1.json';
    public const SOURCE_SHA = '419d3bd54ec9f3d9a9222a1033e86783639147cf3d420b56d56dc6a5933206ac';
    public const CONTRACT = 'docs/content/theory-sentence-builders/m69-adjectives-adverbs.v1.json';
    public const CONTRACT_SHA = '129f0cf79228dc8859a19a29813b8981454d798202bc57c72cfbb7804806a1bc';
    public const CHECKSUMS = 'docs/content/m69-source-checksums.v1.0.1.json';
    public const CHECKSUMS_SHA = '6a7dff78c0592c8f0d9138a50568215cc63496d1381df035a569f750872aae3c';
    public const OWNERS = [
        'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesVsAdverbsTheorySeeder',
        'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesOrderOfAdjectivesTheorySeeder',
        'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesEdIngAdjectivesTheorySeeder',
    ];
    public const SLUGS = ['adjectives-vs-adverbs', 'order-of-adjectives', 'ed-ing-adjectives'];
    public const PAGE_IDS = [279, 278, 275];
    public const CATEGORY_IDS = [52, 52, 52];
    public const ANCESTRIES = [['prykmetniky-ta-pryslinknyky'], ['prykmetniky-ta-pryslinknyky'], ['prykmetniky-ta-pryslinknyky']];
    public const LEVELS = ['A2', 'B1', 'A2'];
    public const DISPLAY_LEVELS = ['A2–B1', 'B1', 'A2'];
    public const CONTINUING_COUNTS = [11, 6, 6];
    public const CONTINUING_LEVELS = [[null,null,null,null,null,null,null,null,null,null,null],
        ['B1','B1','B1','B1','B1',null], ['A2','A2','A2','A2','A2',null]];
    public const CONTINUING_TYPES = [
        ['hero','comparison-table','usage-panels','usage-panels','comparison-table','usage-panels',
            'usage-panels','comparison-table','forms-grid','usage-panels','navigation-chips'],
        ['hero','forms-grid','usage-panels','mistakes-grid','summary-list','navigation-chips'],
        ['hero','forms-grid','usage-panels','mistakes-grid','summary-list','navigation-chips'],
    ];
    public const RENDERED_TITLES = ['Adjectives vs Adverbs', 'Order of Adjectives', '-ed / -ing Adjectives'];
    public const PRACTICE_KEYS = ['adverbs-practice', 'order-practice', 'eding-practice'];
    public const BEFORE_SUBTITLES = [
        ['mode'=>'captured-plain', 'sha256'=>'48d22b765f50817259985c51bbfa688737c515d9e81a501887f8e315946a6b30',
            'raw_page_title'=>'Adjectives vs Adverbs — Прикметники й прислівники',
            'extracted_title'=>'Adjectives vs Adverbs', 'h1'=>'Adjectives vs Adverbs'],
        ['mode'=>'captured-plain', 'sha256'=>'b8b13ab3c4bb16effe820526c09f9ab2403db799ad006121234a373704be0a1a',
            'raw_page_title'=>'Order of Adjectives', 'extracted_title'=>'Order of Adjectives', 'h1'=>'Order of Adjectives'],
        ['mode'=>'captured-absent', 'source_html_present'=>false, 'source_text_present'=>false,
            'page_text'=>null, 'subtitle_rows'=>0, 'raw_page_title'=>'-ed / -ing Adjectives',
            'extracted_title'=>null, 'h1'=>'-ed / -ing Adjectives'],
    ];
    public const EDING_CONTINUING_UUIDS = ['0a02f7ae-2ec7-5d2e-9c51-db26555f5399',
        '3b1b7594-5b90-554d-aee0-52edb21ff743', 'dc206a89-c512-54e1-8e05-af43faf9cc93',
        'b2dbf466-24bd-51f6-a4a9-6648f8ceb27f', '16ccf335-824b-5d64-8fc8-45e9fa73e514',
        '322f2cef-ad93-5e79-b9de-b0501f4da3a5'];

    public static function profile(): array
    {
        $bindings = [];
        foreach (self::OWNERS as $i => $owner) {
            $bindings[] = ['identity'=>$owner, 'slug'=>self::SLUGS[$i], 'page_id'=>self::PAGE_IDS[$i],
                'category_id'=>52, 'ancestry'=>self::ANCESTRIES[$i],
                'local_url'=>'http://gramlyze.loc/theory/prykmetniky-ta-pryslinknyky/'.self::SLUGS[$i],
                'practice_level'=>self::LEVELS[$i], 'display_level'=>self::DISPLAY_LEVELS[$i],
                'rendered_title'=>self::RENDERED_TITLES[$i], 'continuing_levels'=>self::CONTINUING_LEVELS[$i],
                'before_subtitle'=>self::BEFORE_SUBTITLES[$i],
                'appended_blocks'=>[['key'=>self::PRACTICE_KEYS[$i], 'type'=>'practice-set', 'column'=>'left', 'level'=>self::LEVELS[$i]]]]
                + ($i === 2 ? ['continuing_uuids'=>self::EDING_CONTINUING_UUIDS,
                    'after_subtitle'=>['mode'=>'plain-fallback',
                        'sha256'=>'89407f6847f0c4fd2fa484096afffac6c9ffd17b83b57a4b398013006283f644',
                        'extracted_title'=>null, 'h1'=>'-ed / -ing Adjectives']] : []);
        }
        return ['label'=>'M69', 'base_sha'=>self::BASE_SHA, 'version'=>'1.0.1',
            'marker'=>'m69_native_v1', 'detail_marker'=>'m69_detail_key', 'uuid_prefix'=>'m69:', 'design'=>'m69-native-v1',
            'artifacts'=>[self::BEFORE=>self::BEFORE_SHA, self::SOURCE=>self::SOURCE_SHA, self::MASTER=>self::MASTER_SHA,
                self::CONTRACT=>self::CONTRACT_SHA, self::CHECKSUMS=>self::CHECKSUMS_SHA], 'bindings'=>$bindings];
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
        if (($before['content_type'] ?? null) !== 'M69_source_before'
            || ($patch['content_type'] ?? null) !== 'M69_Adjectives_Adverbs') { throw new RuntimeException('M69 exact provenance differs.'); }
        foreach ($master['lessons'] ?? [] as $i => $lesson) {
            self::plainFeedback($lesson);
            if (array_column($before['targets'][$i]['before']['page']['blocks'] ?? [], 'type') !== (self::CONTINUING_TYPES[$i] ?? null)
                || array_column(array_slice($lesson['blocks'] ?? [], 0, self::CONTINUING_COUNTS[$i]), 'type') !== self::CONTINUING_TYPES[$i]) {
                throw new RuntimeException('M69 actual per-owner native continuing types differ.');
            }
        }
        self::release()->validate($before, $patch, $master, $contract);
    }
    private static function plainFeedback(array $lesson): void
    {
        foreach ($lesson['blocks'] ?? [] as $block) {
            if (($block['type'] ?? null) !== 'practice-set') { continue; }
            foreach (['selects','choices','inputs'] as $group) {
                foreach ($block['data'][$group] ?? [] as $task) {
                    foreach ($task['feedback'] ?? [] as $text) {
                        if (!is_string($text) || preg_match('/<(?:\\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu', $text)) {
                            throw new RuntimeException('M69 x-text feedback must be plain text without tags/entities.');
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
