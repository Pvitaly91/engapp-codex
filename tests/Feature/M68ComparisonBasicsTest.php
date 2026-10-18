<?php

namespace Tests\Feature;

use App\Services\M68ContentPatch;
use App\Support\M26DetailPackage;
use App\Support\M68ComparisonBasics as Package;
use App\Support\TheoryNativeContentRelease;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\TestCase;

/** Frozen three-owner policy; source count/metadata come from independent native inventory, not M67 slots. */
class M68ComparisonBasicsTest extends TestCase
{
    private function refuses(callable $call, string $case): void
    {
        $caught = null;
        try { $call(); } catch (RuntimeException $error) { $caught = $error; }
        self::assertInstanceOf(RuntimeException::class, $caught, $case);
    }

    public function test_exact_three_page_owners_preserve_thirty_two_native_slots_and_append_three_a2_practices(): void
    {
        [$before, $patch, $master, $contract] = Package::load();
        Package::validate($before, $patch, $master, $contract);
        self::assertSame('2161cb0b03afd203933ad022ccb4e77e8e8ca363', $master['base_sha']);
        self::assertSame('M68_source_before', $before['content_type']);
        self::assertSame('M68_Comparison_Basics', $patch['content_type']);
        self::assertSame([274, 273, 276], Package::PAGE_IDS); self::assertSame([52, 52, 52], Package::CATEGORY_IDS);
        self::assertSame([9, 11, 12], Package::CONTINUING_COUNTS);
        self::assertSame(['A2', 'A2', 'A2'], Package::LEVELS);
        self::assertSame(['A2', 'A2–B1', 'A2–B1'], Package::DISPLAY_LEVELS);
        $ids = []; $slots = 0;
        foreach ($patch['targets'] as $i => $target) {
            $old = $before['targets'][$i]['before']; $lesson = $master['lessons'][$i]; $n = Package::CONTINUING_COUNTS[$i];
            self::assertSame(Package::OWNERS[$i], $target['identity']);
            self::assertSame(['prykmetniky-ta-pryslinknyky'], $target['ancestry']);
            self::assertSame(Package::SLUGS[$i], $target['slug']);
            self::assertSame(Package::PAGE_IDS[$i], $target['page_binding']['id']);
            self::assertSame(52, $target['page_binding']['page_category_id']);
            self::assertCount($n + 1, $lesson['blocks']);
            self::assertSame([...Package::CONTINUING_TYPES[$i], 'practice-set'], array_column($lesson['blocks'], 'type'));
            self::assertSame([...range(0, $n - 1), null], array_column($lesson['blocks'], 'existing_source_index'));
            self::assertSame(Package::definition($lesson, $old), $target['after']);
            self::assertSame($target['after'], json_decode(File::get(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR));
            M68ContentPatch::assertDefinitionScope($old, $target['after'], $target, $i);
            $protected = $target['after']; $protected['page']['blocks'] = $old['page']['blocks'];
            foreach (['subtitle_html', 'subtitle_text'] as $field) { $protected['page'][$field] = $old['page'][$field]; }
            self::assertSame($old, $protected, 'Category description/title/owner/locale/tags are not teaching targets.');
            foreach ($old['page']['blocks'] as $slot => $source) {
                ++$slots; $after = $target['after']['page']['blocks'][$slot];
                self::assertSame(M26DetailPackage::uuid($target['identity'], $source, $slot + 1),
                    M26DetailPackage::uuid($target['identity'], $after, $slot + 1));
                unset($source['body'], $after['body']); self::assertSame($source, $after);
            }
            $practice = $lesson['blocks'][$n]; self::assertSame('A2', $practice['level']); self::assertSame('left', $practice['column']);
            self::assertSame(Package::PRACTICE_KEYS[$i], $practice['key']);
            self::assertSame($contract['targets'][$i]['bank_seeders'], $practice['data']['linked_practice']['seeder_classes']);
            self::assertSame(['4'], $practice['data']['linked_practice']['question_types']);
            foreach (['selects', 'choices', 'inputs'] as $group) {
                self::assertCount(2, $practice['data'][$group]);
                foreach ($practice['data'][$group] as $task) {
                    $ids[] = $task['id']; self::assertContains($task['answer'], $task['accepted']);
                    self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $task[$group === 'inputs' ? 'after' : 'label']);
                }
            }
            foreach ($target['after']['page']['blocks'] as $source) { self::assertLessThanOrEqual(65535, strlen($source['body'])); }
        }
        self::assertSame(32, $slots); self::assertCount(18, $ids); self::assertCount(18, array_unique($ids));
    }

    public function test_foreign_page_category_legacy_owner_metadata_or_stale_source_cannot_expand_the_release(): void
    {
        [$before, $patch, $master, $contract] = Package::load();
        foreach (['owner', 'category-source', 'category-title', 'locale', 'ancestry', 'page-id', 'category-id',
            'technical-level', 'display-level', 'column', 'type', 'heading', 'extra-slot', 'new-practice-key',
            'missing-bank', 'foreign-view', 'before-provenance', 'patch-provenance', 'stale-after', 'stale-before',
            'duplicate-task', 'detail-owner', 'legacy-top-owner', 'legacy-block-owner', 'explicit-null-level'] as $case) {
            $b = $before; $p = $patch; $m = $master; $c = $contract;
            if ($case === 'owner') { $m['lessons'][0]['identity'] = Package::OWNERS[2]; }
            if ($case === 'category-source') { $b['targets'][0]['before']['content_type'] = 'category'; }
            if ($case === 'category-title') { $p['targets'][1]['after']['page']['category']['title'] = 'Unapproved category'; }
            if ($case === 'locale') { $m['lessons'][1]['locale'] = 'en'; }
            if ($case === 'ancestry') { $p['targets'][1]['ancestry'] = ['adjectives']; }
            if ($case === 'page-id') { $p['targets'][0]['page_binding']['id'] = 52; }
            if ($case === 'category-id') { $p['targets'][2]['page_binding']['page_category_id'] = 276; }
            if ($case === 'technical-level') { $m['lessons'][0]['blocks'][1]['level'] = 'B2'; }
            if ($case === 'display-level') { $m['lessons'][1]['blocks'][0]['data']['level'] = 'C2'; }
            if ($case === 'column') { $m['lessons'][2]['blocks'][5]['column'] = 'left'; }
            if ($case === 'type') { $m['lessons'][1]['blocks'][8]['type'] = 'usage-panels'; }
            if ($case === 'heading') { $p['targets'][0]['after']['page']['blocks'][1]['heading'] = 'Unapproved heading'; }
            if ($case === 'extra-slot') { $m['lessons'][0]['blocks'][] = $m['lessons'][0]['blocks'][9]; }
            if ($case === 'new-practice-key') { $m['lessons'][0]['blocks'][9]['key'] = 'foreign-practice'; }
            if ($case === 'missing-bank') { $c['targets'][2]['bank_seeders'] = []; }
            if ($case === 'foreign-view') { $m['lessons'][0]['blocks'][1]['data']['native_view'] = 'm68-private-design'; }
            if ($case === 'before-provenance') { $b['content_type'] = 'M67_source_before'; }
            if ($case === 'patch-provenance') { $p['content_type'] = 'M67_Verb_Patterns_Completion'; }
            if ($case === 'stale-after') { $p['targets'][2]['after']['page']['blocks'][2]['body'] .= "\n"; }
            if ($case === 'stale-before') { $b['targets'][0]['before']['slug'] = 'later-local-edit'; }
            if ($case === 'duplicate-task') { $m['lessons'][1]['blocks'][11]['data']['inputs'][1]['id'] = $m['lessons'][0]['blocks'][9]['data']['inputs'][0]['id']; }
            if ($case === 'detail-owner') { $m['lessons'][1]['details'][0]['owner_block_key'] = 'foreign-point'; }
            if ($case === 'legacy-top-owner') { $m['lessons'][2]['identity'] = 'Database\\Seeders\\Page_v2\\Adjectives\\AdjectivesEqualityComparisonAsAsNotAsAsTheorySeeder'; }
            if ($case === 'legacy-block-owner') { $p['targets'][2]['after']['page']['blocks'][0]['seeder'] = Package::OWNERS[2]; }
            if ($case === 'explicit-null-level') { $p['targets'][0]['after']['page']['blocks'][1]['level'] = null; }
            $this->refuses(fn () => Package::validate($b, $p, $m, $c), $case);
        }
    }

    public function test_frozen_bytes_cannot_be_rebound_by_a_changed_checksum(): void
    {
        $profile = Package::profile(); $profile['artifacts'][Package::MASTER] = str_repeat('0', 64);
        $this->refuses(fn () => (new TheoryNativeContentRelease($profile))->load(), 'Exact frozen bytes, not decoded equivalence.');
        foreach (Package::BEFORE_SUBTITLES as $source) {
            self::assertSame('captured-plain', $source['mode']);
            self::assertSame($source['raw_page_title'], $source['h1']);
            self::assertSame($source['raw_page_title'], $source['extracted_title']);
        }
    }
}
