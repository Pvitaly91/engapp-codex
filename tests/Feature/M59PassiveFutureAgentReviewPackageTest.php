<?php

namespace Tests\Feature;

use App\Services\M59ContentPatch;
use App\Support\M26DetailPackage;
use App\Support\M59PassiveFutureAgentReviewPackage as Package;
use App\Support\TheoryNativeContentRelease;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\TestCase;

/** Independent finite M59 identities/metadata/fidelity, never a working DB or browser. */
class M59PassiveFutureAgentReviewPackageTest extends TestCase
{
    private function rejects(callable $operation, string $case): void
    {
        $error = null; try { $operation(); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error, $case);
    }

    public function test_actual_mixed_ancestry_levels_and_finite_advanced_transition_project_exactly(): void
    {
        [$before, $patch, $master, $contract] = Package::load(); Package::validate($before, $patch, $master, $contract);
        self::assertSame('5c314ccfedaba52934cdfd7cae3aef3ce7f1aa89', $master['base_sha']);
        self::assertSame([105, 104, 282], Package::PAGE_IDS); self::assertSame([18, 1, 1], Package::CATEGORY_IDS);
        self::assertSame([11, 8, 2], Package::CONTINUING_COUNTS); self::assertSame(['B2', 'B1', 'B2'], Package::LEVELS);
        foreach ($patch['targets'] as $i => $target) {
            $lesson = $master['lessons'][$i]; $old = $before['targets'][$i]['before']; $binding = Package::profile()['bindings'][$i];
            self::assertSame(Package::ANCESTRIES[$i], $target['ancestry']); self::assertSame('uk', $old['page']['locale']);
            self::assertSame(Package::definition($lesson, $old), $target['after']);
            self::assertSame($target['after'], json_decode(File::get(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR));
            M59ContentPatch::assertDefinitionScope($old, $target['after'], $target, $i);
            $count = Package::CONTINUING_COUNTS[$i]; $added = count($binding['appended_blocks'] ?? [null]);
            self::assertCount($count + $added, $lesson['blocks']);
            self::assertSame([...range(0, $count - 1), ...array_fill(0, $added, null)], array_column($lesson['blocks'], 'existing_source_index'));
            foreach ($old['page']['blocks'] as $slot => $source) {
                $new = $target['after']['page']['blocks'][$slot];
                self::assertSame(M26DetailPackage::uuid($target['identity'], $source, $slot + 1),
                    M26DetailPackage::uuid($target['identity'], $new, $slot + 1));
                if ($i === 2 && $slot === 1) {
                    self::assertSame('box', $source['type']); self::assertSame('B2 focus', $source['heading']);
                    self::assertArrayNotHasKey('level', $source); self::assertSame('usage-panels', $new['type']);
                    self::assertSame('B2', $new['level']); self::assertArrayNotHasKey('heading', $new);
                } else { unset($source['body'], $new['body']); self::assertSame($source, $new); }
            }
            $practices = array_values(array_filter($lesson['blocks'], static fn ($block) => $block['type'] === 'practice-set'));
            self::assertCount(1, $practices); self::assertSame(end($lesson['blocks']), $practices[0]);
            foreach (['selects', 'choices', 'inputs'] as $group) {
                self::assertCount(2, $practices[0]['data'][$group]);
                foreach ($practices[0]['data'][$group] as $task) {
                    self::assertContains($task['answer'], $task['accepted']); self::assertNotEmpty($task['feedback']);
                }
            }
            self::assertSame($contract['targets'][$i]['bank_seeders'], $practices[0]['data']['linked_practice']['seeder_classes']);
            foreach ($target['after']['page']['blocks'] as $source) { self::assertLessThanOrEqual(65535, strlen($source['body'])); }
            self::assertArrayNotHasKey('before_subtitle', $binding); self::assertArrayNotHasKey('component_overrides', $binding);
        }
    }

    public function test_foreign_category_locale_ancestry_levels_metadata_and_unregistered_slots_fail_closed(): void
    {
        [$before, $patch, $master, $contract] = Package::load();
        foreach (['owner', 'category-owner', 'locale', 'flat-future', 'nested-by', 'nested-advanced', 'binding',
            'by-b2-overwritten', 'advanced-still-box', 'advanced-heading', 'advanced-null-level', 'extra-new-slot',
            'changed-new-type', 'changed-new-key', 'duplicate-practice', 'missing-bank', 'foreign-view'] as $case) {
            $b = $before; $p = $patch; $m = $master; $c = $contract;
            if ($case === 'owner') { $p['targets'][0]['identity'] = Package::OWNERS[2]; }
            if ($case === 'category-owner') { $b['targets'][2]['before']['content_type'] = 'category'; }
            if ($case === 'locale') { $m['lessons'][1]['locale'] = 'en'; }
            if ($case === 'flat-future') { $p['targets'][0]['ancestry'] = ['passive-voice']; }
            if ($case === 'nested-by') { $p['targets'][1]['ancestry'][] = 'passive-voice-tenses'; }
            if ($case === 'nested-advanced') { $p['targets'][2]['ancestry'][] = 'passive-voice-tenses'; }
            if ($case === 'binding') { $p['targets'][2]['page_binding']['page_category_id'] = 18; }
            if ($case === 'by-b2-overwritten') { $m['lessons'][1]['blocks'][3]['level'] = 'B1'; }
            if ($case === 'advanced-still-box') { $m['lessons'][2]['blocks'][1]['type'] = 'box'; }
            if ($case === 'advanced-heading') { $p['targets'][2]['after']['page']['blocks'][1]['heading'] = 'B2 focus'; }
            if ($case === 'advanced-null-level') { $m['lessons'][2]['blocks'][1]['level'] = null; }
            if ($case === 'extra-new-slot') { $m['lessons'][2]['blocks'][] = $m['lessons'][2]['blocks'][2]; }
            if ($case === 'changed-new-type') { $m['lessons'][2]['blocks'][2]['type'] = 'usage-panels'; }
            if ($case === 'changed-new-key') { $m['lessons'][2]['blocks'][2]['key'] = 'unregistered-teaching'; }
            if ($case === 'duplicate-practice') { $m['lessons'][2]['blocks'][2]['type'] = 'practice-set'; }
            if ($case === 'missing-bank') { $c['targets'][2]['bank_seeders'] = []; }
            if ($case === 'foreign-view') { $m['lessons'][0]['blocks'][1]['data']['native_view'] = 'own-m59-view'; }
            $this->rejects(fn () => Package::validate($b, $p, $m, $c), $case);
        }
    }

    public function test_frozen_byte_guards_and_supported_strong_titles_are_not_relaxed(): void
    {
        [$before, $patch, $master, $contract] = Package::load();
        foreach (Package::OWNERS as $i => $owner) {
            self::assertStringStartsWith('<p><strong>'.Package::RENDERED_TITLES[$i].'</strong>', $before['targets'][$i]['before']['page']['subtitle_html']);
            $m = $master; $m['lessons'][$i]['subtitle_html'] = '<p>Plain title</p>';
            $p = $patch; $p['targets'][$i]['after'] = Package::definition($m['lessons'][$i], $before['targets'][$i]['before']);
            $this->rejects(fn () => Package::validate($before, $p, $m, $contract), 'plain subtitle '.$owner);
        }
        $profile = Package::profile(); $profile['artifacts'][Package::MASTER] = str_repeat('0', 64);
        $this->rejects(fn () => (new TheoryNativeContentRelease($profile))->load(), 'stale raw source');
    }
}
