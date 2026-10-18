<?php

namespace Tests\Feature;

use App\Support\M69AdjectivesAdverbs as Package;
use App\Support\TheoryNativeContentRelease;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\TestCase;

/** Frozen release data only: no working-DB fixtures, browser claim or historical assertion changes. */
class M69AuthoredReleaseTest extends TestCase
{
    private const OWNERS = [
        'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesVsAdverbsTheorySeeder',
        'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesOrderOfAdjectivesTheorySeeder',
        'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesEdIngAdjectivesTheorySeeder',
    ];

    private function refuses(callable $call, string $case): void
    {
        $caught = null;
        try { $call(); } catch (RuntimeException $error) { $caught = $error; }
        self::assertInstanceOf(RuntimeException::class, $caught, $case);
    }

    public function test_frozen_three_page_scope_preserves_twenty_three_slots_and_three_distinct_practice_levels(): void
    {
        [$before, $patch, $master, $contract] = Package::load();
        Package::validate($before, $patch, $master, $contract);
        self::assertSame('c41f65749e39768a69e62dff4e4b942ffa751fe4', $master['base_sha']);
        self::assertSame('1.0.1', $master['version']);
        self::assertSame('docs/content/m69-adjectives-adverbs.v1.0.1.json', Package::MASTER);
        self::assertSame('database/content-patches/m69-adjectives-adverbs.v1.0.1.json', Package::SOURCE);
        self::assertSame('docs/content/m69-source-checksums.v1.0.1.json', Package::CHECKSUMS);
        self::assertSame('M69_source_before', $before['content_type']);
        self::assertSame('M69_Adjectives_Adverbs', $patch['content_type']);
        self::assertSame(self::OWNERS, Package::OWNERS);
        self::assertSame([279, 278, 275], Package::PAGE_IDS);
        self::assertSame([52, 52, 52], Package::CATEGORY_IDS);
        self::assertSame([11, 6, 6], Package::CONTINUING_COUNTS);
        self::assertSame(['A2', 'B1', 'A2'], Package::LEVELS);
        self::assertSame(['A2–B1', 'B1', 'A2'], Package::DISPLAY_LEVELS);
        $taskIds = []; $slots = 0;
        foreach ($patch['targets'] as $i => $target) {
            $old = $before['targets'][$i]['before']; $lesson = $master['lessons'][$i]; $n = [11, 6, 6][$i];
            self::assertSame(self::OWNERS[$i], $target['identity']);
            self::assertSame(['prykmetniky-ta-pryslinknyky'], $target['ancestry']);
            self::assertSame(['id' => [279, 278, 275][$i], 'page_category_id' => 52], $target['page_binding']);
            self::assertSame('page', $old['content_type']); self::assertSame('theory', $old['type']);
            self::assertSame('uk', $old['page']['locale']); self::assertSame('uk', $lesson['locale']);
            self::assertCount($n + 1, $lesson['blocks']);
            self::assertSame([...range(0, $n - 1), null], array_column($lesson['blocks'], 'existing_source_index'));
            self::assertSame(Package::definition($lesson, $old), $target['after']);
            self::assertSame($target['after'], json_decode(File::get(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR));
            $protected = $target['after']; $protected['page']['blocks'] = $old['page']['blocks'];
            foreach (['subtitle_html', 'subtitle_text'] as $field) {
                if (array_key_exists($field, $old['page'])) { $protected['page'][$field] = $old['page'][$field]; }
                else { unset($protected['page'][$field]); }
            }
            self::assertSame($old, $protected, 'Only declared teaching bodies/subtitles may differ, not Page/category/title/tags.');
            $slots += count($old['page']['blocks']);
            $practice = $lesson['blocks'][$n];
            self::assertSame(['adverbs-practice', 'order-practice', 'eding-practice'][$i], $practice['key']);
            self::assertSame('practice-set', $practice['type']); self::assertSame('left', $practice['column']);
            self::assertSame(['A2', 'B1', 'A2'][$i], $practice['level']);
            self::assertSame(['4'], $practice['data']['linked_practice']['question_types']);
            self::assertNotEmpty($contract['targets'][$i]['bank_seeders']);
            self::assertSame($contract['targets'][$i]['bank_seeders'], $practice['data']['linked_practice']['seeder_classes']);
            foreach (['selects', 'choices', 'inputs'] as $group) {
                self::assertCount(2, $practice['data'][$group]);
                foreach ($practice['data'][$group] as $task) {
                    $taskIds[] = $task['id']; self::assertContains($task['answer'], $task['accepted']);
                    self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $task[$group === 'inputs' ? 'after' : 'label']);
                }
            }
            foreach ($target['after']['page']['blocks'] as $block) { self::assertLessThanOrEqual(65535, strlen($block['body'])); }
        }
        self::assertSame(23, $slots); self::assertCount(18, $taskIds); self::assertCount(18, array_unique($taskIds));
        foreach (['adverbs', 'order', 'eding'] as $prefix) {
            foreach (['form-1', 'form-2', 'meaning-1', 'meaning-2', 'build-1', 'build-2'] as $suffix) {
                self::assertContains('m69-'.$prefix.'-'.$suffix, $taskIds);
            }
        }
    }

    public function test_identity_metadata_vectors_foreign_presentation_and_stale_sources_are_rejected(): void
    {
        [$before, $patch, $master, $contract] = Package::load();
        foreach (['foreign-owner', 'category-instead-of-page', 'locale', 'category-slug', 'page-id', 'category-id',
            'technical-level', 'display-level', 'old-null-to-a2', 'wrong-type', 'wrong-column', 'heading',
            'extra-slot', 'missing-practice', 'new-practice-key', 'duplicate-task', 'detail-owner',
            'native-view', 'inline-style', 'script', 'missing-bank', 'duplicate-bank', 'foreign-url',
            'before-provenance', 'patch-provenance', 'stale-before', 'stale-after',
            'invented-old-subtitle', 'missing-absence-record'] as $case) {
            $b = $before; $p = $patch; $m = $master; $c = $contract;
            if ($case === 'foreign-owner') { $m['lessons'][0]['identity'] = self::OWNERS[2]; }
            if ($case === 'category-instead-of-page') { $b['targets'][0]['before']['content_type'] = 'category'; }
            if ($case === 'locale') { $m['lessons'][1]['locale'] = 'en'; }
            if ($case === 'category-slug') { $p['targets'][1]['after']['page']['category']['slug'] = 'foreign-category'; }
            if ($case === 'page-id') { $p['targets'][0]['page_binding']['id'] = 52; }
            if ($case === 'category-id') { $p['targets'][2]['page_binding']['page_category_id'] = 275; }
            if ($case === 'technical-level') { $m['lessons'][1]['technical_block_level'] = 'A2'; }
            if ($case === 'display-level') { $m['lessons'][0]['blocks'][0]['data']['level'] = 'B2'; }
            if ($case === 'old-null-to-a2') {
                $m['lessons'][0]['blocks'][1]['level'] = 'A2';
                $this->refuses(function () use ($b, $p, $m, $c): void {
                    $p['targets'][0]['after'] = Package::definition($m['lessons'][0], $b['targets'][0]['before']);
                    Package::validate($b, $p, $m, $c);
                }, $case);
                continue;
            }
            if ($case === 'wrong-type') { $m['lessons'][1]['blocks'][1]['type'] = 'usage-panels'; }
            if ($case === 'wrong-column') { $m['lessons'][2]['blocks'][5]['column'] = 'left'; }
            if ($case === 'heading') { $p['targets'][0]['after']['page']['blocks'][1]['heading'] = 'Unapproved heading'; }
            if ($case === 'extra-slot') { $m['lessons'][0]['blocks'][] = $m['lessons'][0]['blocks'][1]; }
            if ($case === 'missing-practice') { array_pop($m['lessons'][2]['blocks']); }
            if ($case === 'new-practice-key') { $m['lessons'][1]['blocks'][6]['key'] = 'foreign-practice'; }
            if ($case === 'duplicate-task') { $m['lessons'][2]['blocks'][6]['data']['inputs'][1]['id'] = $m['lessons'][0]['blocks'][11]['data']['inputs'][0]['id']; }
            if ($case === 'detail-owner') { $m['lessons'][1]['details'][0]['owner_pointer'] = '/sections/99'; }
            if ($case === 'native-view') { $m['lessons'][0]['blocks'][1]['data']['native_view'] = 'm69-private-view'; }
            if ($case === 'inline-style') { $m['lessons'][2]['blocks'][2]['data']['sections'][0]['note'] = '<span style="color:red">Changed</span>'; }
            if ($case === 'script') { $m['lessons'][1]['blocks'][2]['data']['sections'][0]['note'] = '<script>alert(1)</script>'; }
            if ($case === 'missing-bank') { $c['targets'][2]['bank_seeders'] = []; }
            if ($case === 'duplicate-bank') { $c['targets'][1]['bank_seeders'][] = $c['targets'][1]['bank_seeders'][0]; }
            if ($case === 'foreign-url') { $p['targets'][0]['local_url'] = 'https://example.invalid/theory'; }
            if ($case === 'before-provenance') { $b['content_type'] = 'M68_source_before'; }
            if ($case === 'patch-provenance') { $p['content_type'] = 'M68_Comparison_Basics'; }
            if ($case === 'stale-before') { $b['targets'][1]['before']['slug'] = 'manual-local-edit'; }
            if ($case === 'stale-after') { $p['targets'][2]['after']['page']['blocks'][2]['body'] .= "\n"; }
            if ($case === 'invented-old-subtitle') { $b['targets'][2]['before']['page']['subtitle_html'] = ''; }
            if ($case === 'missing-absence-record') { array_pop($m['editorial_review']['pre_apply_metadata']); }
            // A matching re-projection must not hide the substantive policy violation behind stale-AFTER detection.
            $reproject = ['native-view' => 0, 'inline-style' => 2, 'script' => 1, 'duplicate-task' => 2];
            if (array_key_exists($case, $reproject)) {
                $i = $reproject[$case];
                $p['targets'][$i]['after'] = Package::definition($m['lessons'][$i], $b['targets'][$i]['before']);
            }
            $this->refuses(fn () => Package::validate($b, $p, $m, $c), $case);
        }
    }

    public function test_no_release_bytes_can_be_rebound_by_decoded_equivalence_or_an_unfrozen_pin(): void
    {
        Package::load();
        foreach (Package::profile()['artifacts'] as $path => $sha) {
            self::assertNotSame(str_repeat('0', 64), $sha, 'M69 must be frozen before these tests run.');
            self::assertSame($sha, hash_file('sha256', base_path($path)));
        }
        $profile = Package::profile(); $profile['artifacts'][Package::MASTER] = str_repeat('0', 64);
        $this->refuses(fn () => (new TheoryNativeContentRelease($profile))->load(), 'Raw immutable master bytes');
    }

    public function test_active_101_retains_every_frozen_100_artifact_and_all_twenty_six_native_teaching_blocks(): void
    {
        $historicalPath = 'docs/content/m69-source-checksums.v1.json';
        self::assertSame('fcf442ace35913c61277a25d5ab2da5d7f6329bdcdb40dfc88cbbd4b695bfa3a',
            hash_file('sha256', base_path($historicalPath)));
        $historical = json_decode(File::get(base_path($historicalPath)), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('1.0.0', $historical['version']); self::assertSame('frozen', $historical['status']);
        self::assertCount(8, $historical['artifacts']);
        foreach ($historical['artifacts'] as $artifact) {
            self::assertSame($artifact['sha256'], hash_file('sha256', base_path($artifact['path'])), $artifact['path']);
            self::assertSame($artifact['bytes'], filesize(base_path($artifact['path'])), $artifact['path']);
        }
        $old = json_decode(File::get(base_path('docs/content/m69-adjectives-adverbs.v1.0.0.json')), true, flags: JSON_THROW_ON_ERROR);
        [, , $active] = Package::load();
        self::assertSame('1.0.0', $old['version']); self::assertSame('1.0.1', $active['version']);
        self::assertSame(array_column($old['lessons'], 'identity'), array_column($active['lessons'], 'identity'));
        self::assertSame(array_column($old['lessons'], 'blocks'), array_column($active['lessons'], 'blocks'),
            'The new title-fallback subtitle revision cannot alter any native teaching/practice/navigation data.');
        self::assertSame(26, array_sum(array_map(static fn ($l) => count($l['blocks']), $active['lessons'])));
    }


    public function test_navigation_retains_four_seven_seven_source_items_with_only_finite_missing_hrefs_added(): void
    {
        [$before, , $master] = Package::load();
        $expectedAdverbsUrls = [
            '/theory/prykmetniky-ta-pryslinknyky/theory-degrees-of-comparison',
            '/theory/prykmetniky-ta-pryslinknyky/comparative-vs-superlative',
            '/theory/prykmetniky-ta-pryslinknyky/equality-comparison-asas-not-asas',
        ];
        foreach ($master['lessons'] as $i => $lesson) {
            $slot = [10, 5, 5][$i];
            $old = json_decode($before['targets'][$i]['before']['page']['blocks'][$slot]['body'], true, flags: JSON_THROW_ON_ERROR)['items'];
            $new = $lesson['blocks'][$slot]['data']['items'];
            self::assertCount([4, 7, 7][$i], $old); self::assertCount([4, 7, 7][$i], $new);
            if ($i === 0) {
                foreach ($new as $n => $item) {
                    $copy = $item; unset($copy['url']); self::assertSame($old[$n], $copy);
                    if ($n < 3) { self::assertSame($expectedAdverbsUrls[$n], $item['url']); }
                }
            } else { self::assertSame($old, $new, 'All explicit source label/url/current triples remain literal.'); }
            self::assertSame(1, count(array_filter($new, static fn ($item) => $item['current'] ?? false)));
        }
    }
}
