<?php

namespace Tests\Feature;

use App\Services\M65ContentPatch;
use App\Support\M26DetailPackage;
use App\Support\M65PurposeGerundInfinitivePackage as Package;
use App\Support\TheoryNativeContentRelease;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\TestCase;

/** Exact M65 single-category source projection; no working database or browser. */
class M65PurposeGerundInfinitivePackageTest extends TestCase
{
    private function rejects(callable $call, string $case): void
    {
        $caught = null; try { $call(); } catch (RuntimeException $error) { $caught = $error; }
        self::assertInstanceOf(RuntimeException::class, $caught, $case);
    }

    public function test_seven_slots_per_owner_and_three_explicit_appends_use_only_existing_native_components(): void
    {
        [$before, $patch, $master, $contract] = Package::load(); Package::validate($before, $patch, $master, $contract);
        self::assertSame('8371a96ad8b0254d5e88d2265dff57d91cb87190', $master['base_sha']);
        self::assertSame('M65_source_before', $before['content_type']);
        self::assertSame('M65_Purpose_Gerund_Infinitive', $patch['content_type']);
        self::assertSame([35, 195, 198], Package::PAGE_IDS); self::assertSame([34, 33, 33], Package::CATEGORY_IDS);
        self::assertSame([7, 6, 6], Package::CONTINUING_COUNTS); self::assertSame(['B1', 'A2', 'A2'], Package::LEVELS);
        self::assertSame(['B1-B2', 'A2-B1', 'A2-B1'], Package::DISPLAY_LEVELS);
        $expected = [
            ['hero','forms-grid','usage-panels','comparison-table','mistakes-grid','summary-list','navigation-chips','practice-set'],
            ['hero','forms-grid','usage-panels','mistakes-grid','summary-list','navigation-chips','practice-set'],
            ['hero','forms-grid','usage-panels','mistakes-grid','summary-list','navigation-chips','practice-set'],
        ];
        $transitions = 0; $appended = 0;
        foreach ($patch['targets'] as $i => $target) {
            $lesson = $master['lessons'][$i]; $old = $before['targets'][$i]['before'];
            self::assertSame(Package::ANCESTRIES[$i], $target['ancestry']); self::assertSame('uk', $old['page']['locale']);
            self::assertSame(Package::definition($lesson, $old), $target['after']);
            self::assertSame($target['after'], json_decode(File::get(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR));
            M65ContentPatch::assertDefinitionScope($old, $target['after'], $target, $i);
            self::assertSame($expected[$i], array_column($lesson['blocks'], 'type'));
            self::assertSame($old['page']['category'], $target['after']['page']['category']);
            self::assertSame($old['page']['title'], $target['after']['page']['title']);
            foreach ($old['page']['blocks'] as $slot => $source) {
                $new = $target['after']['page']['blocks'][$slot];
                self::assertSame($source['column'], $new['column']); self::assertSame($source['level'] ?? null, $new['level'] ?? null);
                self::assertSame(M26DetailPackage::uuid($target['identity'], $source, $slot + 1),
                    M26DetailPackage::uuid($target['identity'], $new, $slot + 1));
                if ($source['type'] === 'box') {
                    ++$transitions; self::assertArrayNotHasKey('heading', $new); self::assertNull($new['level'] ?? null);
                } else { unset($source['body'], $new['body']); self::assertSame($source, $new); }
            }
            $appended += count($target['after']['page']['blocks']) - count($old['page']['blocks']);
            $practices = array_values(array_filter($lesson['blocks'], static fn ($block) => $block['type'] === 'practice-set'));
            self::assertCount(1, $practices); self::assertSame(end($lesson['blocks']), $practices[0]);
            foreach (['selects', 'choices', 'inputs'] as $group) {
                self::assertCount(2, $practices[0]['data'][$group]);
                foreach ($practices[0]['data'][$group] as $task) { self::assertContains($task['answer'], $task['accepted']); }
            }
            self::assertSame($contract['targets'][$i]['bank_seeders'], $practices[0]['data']['linked_practice']['seeder_classes']);
            foreach ($target['after']['page']['blocks'] as $source) { self::assertLessThanOrEqual(65535, strlen($source['body'])); }
        }
        self::assertSame(5, $transitions); self::assertSame(3, $appended);
    }

    public function test_foreign_owner_wrong_relative_category_null_level_changes_and_new_slot_drift_fail_closed(): void
    {
        [$before, $patch, $master, $contract] = Package::load();
        foreach (['owner', 'category-owner', 'category-title', 'locale', 'relative-category', 'binding', 'box-promoted',
            'hero-level', 'display-level', 'still-box', 'old-heading', 'column', 'extra-new-slot', 'new-type', 'new-key',
            'duplicate-practice', 'missing-bank', 'foreign-view', 'old-provenance', 'patch-provenance'] as $case) {
            $b = $before; $p = $patch; $m = $master; $c = $contract;
            if ($case === 'owner') { $p['targets'][0]['identity'] = Package::OWNERS[2]; }
            if ($case === 'category-owner') { $b['targets'][2]['before']['content_type'] = 'category'; }
            if ($case === 'category-title') { $p['targets'][1]['after']['page']['category']['title'] = 'Relative Clauses'; }
            if ($case === 'locale') { $m['lessons'][1]['locale'] = 'pl'; }
            if ($case === 'relative-category') { $p['targets'][1]['ancestry'] = ['relative-clauses']; }
            if ($case === 'binding') { $p['targets'][2]['page_binding']['page_category_id'] = 3; }
            if ($case === 'box-promoted') { $m['lessons'][0]['blocks'][1]['level'] = 'B2'; }
            if ($case === 'hero-level') { $m['lessons'][1]['blocks'][0]['level'] = 'B2'; }
            if ($case === 'display-level') { $m['lessons'][1]['blocks'][0]['data']['level'] = 'C2'; }
            if ($case === 'still-box') { $m['lessons'][2]['blocks'][1]['type'] = 'box'; }
            if ($case === 'old-heading') { $p['targets'][0]['after']['page']['blocks'][1]['heading'] = '1. Коли використовуємо non-defining clause'; }
            if ($case === 'column') { $m['lessons'][2]['blocks'][3]['column'] = 'left'; }
            if ($case === 'extra-new-slot' || $case === 'duplicate-practice') { $m['lessons'][0]['blocks'][] = $m['lessons'][0]['blocks'][7]; }
            if ($case === 'new-type') { $m['lessons'][0]['blocks'][2]['type'] = 'forms-grid'; }
            if ($case === 'new-key') { $m['lessons'][0]['blocks'][7]['key'] = 'foreign-key'; }
            if ($case === 'missing-bank') { $c['targets'][2]['bank_seeders'] = []; }
            if ($case === 'foreign-view') { $m['lessons'][0]['blocks'][1]['data']['native_view'] = 'm65-independent-view'; }
            if ($case === 'old-provenance') { $b['content_type'] = 'M61_source_before'; }
            if ($case === 'patch-provenance') { $p['content_type'] = 'M61_Reported_Speech_Changes'; }
            $this->rejects(fn () => Package::validate($b, $p, $m, $c), $case);
        }
    }

    public function test_frozen_raw_bytes_and_strong_subtitle_title_guards_are_not_relaxed(): void
    {
        [$before, $patch, $master, $contract] = Package::load();
        foreach (Package::OWNERS as $i => $owner) {
            self::assertStringStartsWith('<p><strong>'.Package::RENDERED_TITLES[$i].'</strong>', $before['targets'][$i]['before']['page']['subtitle_html']);
            $m = $master; $m['lessons'][$i]['subtitle_html'] = '<p>Plain title</p>';
            $p = $patch; $p['targets'][$i]['after'] = Package::definition($m['lessons'][$i], $before['targets'][$i]['before']);
            $this->rejects(fn () => Package::validate($before, $p, $m, $contract), $owner);
        }
        $profile = Package::profile(); $profile['artifacts'][Package::MASTER] = str_repeat('0', 64);
        $this->rejects(fn () => (new TheoryNativeContentRelease($profile))->load(), 'stale raw source');
    }
}
