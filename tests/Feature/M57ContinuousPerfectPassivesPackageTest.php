<?php

namespace Tests\Feature;

use App\Support\M57ContinuousPerfectPassivesPackage as Package;
use App\Support\TheoryNativeContentRelease;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\TestCase;

/** Independent finite editorial/data contract, never a working DB or browser. */
class M57ContinuousPerfectPassivesPackageTest extends TestCase
{
    private function rejects(callable $operation, string $case): void
    {
        $error = null;
        try { $operation(); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error, $case);
    }

    public function test_frozen_sources_project_exactly_into_12_continuing_slots_and_one_practice_per_owner(): void
    {
        [$before, $patch, $master, $contract] = Package::load();
        Package::validate($before, $patch, $master, $contract);
        self::assertSame('b64e338e7dd0e515d4fcb72794c38a8c4f721936', $master['base_sha']);
        self::assertSame([111, 108, 112], Package::PAGE_IDS);
        self::assertSame([18, 18, 18], Package::CATEGORY_IDS);
        self::assertSame([12, 12, 12], Package::CONTINUING_COUNTS);
        foreach ($patch['targets'] as $i => $target) {
            $lesson = $master['lessons'][$i]; $old = $before['targets'][$i]['before'];
            self::assertSame(Package::OWNERS[$i], $target['identity']);
            self::assertSame(['passive-voice', 'passive-voice-tenses'], $target['ancestry']);
            self::assertSame('uk', $old['page']['locale']);
            self::assertSame('passive-voice-tenses', $old['page']['category']['slug']);
            self::assertSame(Package::definition($lesson, $old), $target['after']);
            self::assertSame($target['after'], json_decode(File::get(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR));
            self::assertCount(13, $lesson['blocks']);
            self::assertSame([...range(0, 11), null], array_column($lesson['blocks'], 'existing_source_index'));
            self::assertSame('B1', $lesson['technical_block_level']);
            foreach ($old['page']['blocks'] as $slot => $block) {
                $new = $target['after']['page']['blocks'][$slot]; unset($block['body'], $new['body']);
                self::assertSame($block, $new, 'Continuing metadata is not editorial data.');
            }
            $practice = $lesson['blocks'][12];
            self::assertSame('practice-set', $practice['type']); self::assertSame('B1', $practice['level']);
            foreach (['selects', 'choices', 'inputs'] as $group) {
                self::assertCount(2, $practice['data'][$group]);
                foreach ($practice['data'][$group] as $task) {
                    self::assertContains($task['answer'], $task['accepted']);
                    self::assertNotEmpty($task['feedback']);
                }
            }
            foreach ($target['after']['page']['blocks'] as $config) { self::assertLessThanOrEqual(65535, strlen($config['body'])); }
            self::assertSame($contract['targets'][$i]['bank_seeders'], $practice['data']['linked_practice']['seeder_classes']);
        }
    }

    public function test_foreign_owner_locale_flat_ancestry_category_page_and_unregistered_semantics_are_rejected(): void
    {
        [$before, $patch, $master, $contract] = Package::load();
        foreach (['owner', 'locale', 'flat-ancestry', 'short-category', 'before-category', 'after-category', 'binding', 'metadata', 'unsupported-component', 'missing-bank'] as $case) {
            $b = $before; $p = $patch; $m = $master; $c = $contract;
            if ($case === 'owner') { $p['targets'][0]['identity'] = 'UnplannedFourthOwner'; }
            if ($case === 'locale') { $m['lessons'][0]['locale'] = 'en'; }
            if ($case === 'flat-ancestry') { $p['targets'][0]['ancestry'] = ['passive-voice']; }
            if ($case === 'short-category') { $p['targets'][1]['ancestry'] = ['passive-voice', 'tenses']; }
            if ($case === 'before-category') { $b['targets'][0]['before']['content_type'] = 'category'; }
            if ($case === 'after-category') { $p['targets'][0]['after']['content_type'] = 'category'; }
            if ($case === 'binding') { $p['targets'][2]['page_binding']['page_category_id'] = 1; }
            if ($case === 'metadata') { $p['targets'][0]['after']['page']['blocks'][0]['level'] = 'A2'; }
            if ($case === 'unsupported-component') { $m['lessons'][0]['blocks'][3]['semantic_component'] = 'new-m57-view'; }
            if ($case === 'missing-bank') { $c['targets'][0]['bank_seeders'] = []; }
            $this->rejects(fn () => Package::validate($b, $p, $m, $c), $case);
        }
    }

    public function test_all_supported_titles_remain_strong_and_historical_plain_guards_are_not_copied_to_m57(): void
    {
        [$before, $patch, $master, $contract] = Package::load();
        foreach (Package::profile()['bindings'] as $i => $binding) {
            self::assertArrayNotHasKey('before_subtitle', $binding);
            self::assertArrayNotHasKey('component_overrides', $binding);
            self::assertStringStartsWith('<p><strong>'.Package::RENDERED_TITLES[$i].'</strong>', $before['targets'][$i]['before']['page']['subtitle_html']);
            $m = $master; $m['lessons'][$i]['subtitle_html'] = '<p>'.Package::RENDERED_TITLES[$i].' plain title</p>';
            $p = $patch; $p['targets'][$i]['after'] = Package::definition($m['lessons'][$i], $before['targets'][$i]['before']);
            $this->rejects(fn () => Package::validate($before, $p, $m, $contract), 'plain AFTER '.$i);
        }
        $profile = Package::profile(); $profile['artifacts'][Package::MASTER] = str_repeat('0', 64);
        $this->rejects(fn () => (new TheoryNativeContentRelease($profile))->load(), 'stale raw source');
    }
}
