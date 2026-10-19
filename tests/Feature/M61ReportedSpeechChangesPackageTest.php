<?php

namespace Tests\Feature;

use App\Services\M61ContentPatch;
use App\Support\M26DetailPackage;
use App\Support\M61ReportedSpeechChangesPackage as Package;
use App\Support\TheoryNativeContentRelease;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\TestCase;

/** Independent M61 source/metadata fidelity; no working DB or browser. */
class M61ReportedSpeechChangesPackageTest extends TestCase
{
    private function rejects(callable $operation, string $case): void
    {
        $error = null; try { $operation(); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error, $case);
    }

    public function test_actual_reported_speech_scope_twelve_box_transitions_and_three_appended_slots_project_exactly(): void
    {
        [$before, $patch, $master, $contract] = Package::load(); Package::validate($before, $patch, $master, $contract);
        self::assertSame('d60a288565f841dceefc16ac6ee10fa5c8c55395', $master['base_sha']);
        self::assertSame([141, 145, 144], Package::PAGE_IDS); self::assertSame([3, 3, 3], Package::CATEGORY_IDS);
        self::assertSame([6, 6, 6], Package::CONTINUING_COUNTS); self::assertSame(['B1', 'B1', 'B1'], Package::LEVELS);
        self::assertSame(['B1-B2', 'B1-B2', 'B1-B2'], Package::DISPLAY_LEVELS);
        foreach ($patch['targets'] as $i => $target) {
            $lesson = $master['lessons'][$i]; $old = $before['targets'][$i]['before']; $binding = Package::profile()['bindings'][$i];
            self::assertSame(['reported-speech'], $target['ancestry']); self::assertSame('uk', $old['page']['locale']);
            self::assertSame(Package::definition($lesson, $old), $target['after']);
            self::assertSame($target['after'], json_decode(File::get(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR));
            M61ContentPatch::assertDefinitionScope($old, $target['after'], $target, $i);
            self::assertCount(7, $lesson['blocks']);
            self::assertSame([0, 1, 2, 3, 4, 5, null], array_column($lesson['blocks'], 'existing_source_index'));
            self::assertSame($old['page']['category'], $target['after']['page']['category'], 'No category rename/reseed.');
            self::assertSame($old['page']['title'], $target['after']['page']['title']);
            foreach ($old['page']['blocks'] as $slot => $source) {
                $new = $target['after']['page']['blocks'][$slot];
                self::assertSame(M26DetailPackage::uuid($target['identity'], $source, $slot + 1),
                    M26DetailPackage::uuid($target['identity'], $new, $slot + 1));
                self::assertSame($source['column'], $new['column']);
                self::assertSame($source['level'] ?? null, $new['level'] ?? null);
                if ($slot >= 1 && $slot <= 4) {
                    self::assertSame('box', $source['type']); self::assertNull($new['level'] ?? null);
                    self::assertArrayNotHasKey('heading', $new);
                    self::assertSame(['column' => $source['column'], 'level' => null], $binding['preserved_native_metadata'][$slot]);
                    self::assertSame([
                        [1 => 'comparison-table', 2 => 'usage-panels', 3 => 'mistakes-grid', 4 => 'summary-list'],
                        [1 => 'comparison-table', 2 => 'usage-panels', 3 => 'mistakes-grid', 4 => 'summary-list'],
                        [1 => 'forms-grid', 2 => 'comparison-table', 3 => 'mistakes-grid', 4 => 'summary-list'],
                    ][$i][$slot], $new['type']);
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

    public function test_foreign_owner_category_locale_levels_columns_and_unregistered_appended_slots_fail_closed(): void
    {
        [$before, $patch, $master, $contract] = Package::load();
        foreach (['owner', 'category-owner', 'category-title', 'locale', 'ancestry', 'binding', 'box-promoted-b1',
            'hero-b2', 'display-b1', 'still-box', 'old-heading', 'right-moved-left', 'extra-new-slot',
            'changed-new-type', 'changed-new-key', 'duplicate-practice', 'missing-bank', 'foreign-view'] as $case) {
            $b = $before; $p = $patch; $m = $master; $c = $contract;
            if ($case === 'owner') { $p['targets'][0]['identity'] = Package::OWNERS[2]; }
            if ($case === 'category-owner') { $b['targets'][2]['before']['content_type'] = 'category'; }
            if ($case === 'category-title') { $p['targets'][0]['after']['page']['category']['title'] = 'Непряма мова'; }
            if ($case === 'locale') { $m['lessons'][1]['locale'] = 'en'; }
            if ($case === 'ancestry') { $p['targets'][0]['ancestry'] = ['passive-voice']; }
            if ($case === 'binding') { $p['targets'][2]['page_binding']['page_category_id'] = 1; }
            if ($case === 'box-promoted-b1') { $m['lessons'][1]['blocks'][3]['level'] = 'B1'; }
            if ($case === 'hero-b2') { $m['lessons'][1]['blocks'][0]['level'] = 'B2'; }
            if ($case === 'display-b1') { $m['lessons'][1]['blocks'][0]['data']['level'] = 'B1'; }
            if ($case === 'still-box') { $m['lessons'][2]['blocks'][1]['type'] = 'box'; }
            if ($case === 'old-heading') { $p['targets'][2]['after']['page']['blocks'][1]['heading'] = '1. Форма і структура'; }
            if ($case === 'right-moved-left') { $m['lessons'][2]['blocks'][3]['column'] = 'left'; }
            if ($case === 'extra-new-slot') { $m['lessons'][2]['blocks'][] = $m['lessons'][2]['blocks'][6]; }
            if ($case === 'changed-new-type') { $m['lessons'][2]['blocks'][6]['type'] = 'forms-grid'; }
            if ($case === 'changed-new-key') { $m['lessons'][2]['blocks'][6]['key'] = 'unregistered-teaching'; }
            if ($case === 'duplicate-practice') { $m['lessons'][2]['blocks'][] = $m['lessons'][2]['blocks'][6]; }
            if ($case === 'missing-bank') { $c['targets'][2]['bank_seeders'] = []; }
            if ($case === 'foreign-view') { $m['lessons'][0]['blocks'][1]['data']['native_view'] = 'own-m61-view'; }
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
