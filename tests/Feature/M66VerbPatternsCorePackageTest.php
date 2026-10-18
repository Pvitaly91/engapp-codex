<?php

namespace Tests\Feature;

use App\Services\M66ContentPatch;
use App\Support\M26DetailPackage;
use App\Support\M66VerbPatternsCorePackage as Package;
use App\Support\TheoryNativeContentRelease;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\TestCase;

/** Frozen native data policy only; no working database or HTTP. */
class M66VerbPatternsCorePackageTest extends TestCase
{
    private function rejects(callable $call, string $case): void
    {
        $error = null;
        try { $call(); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error, $case);
    }

    public function test_three_native_owners_keep_six_source_slots_and_append_one_complete_practice_each(): void
    {
        [$before, $patch, $master, $contract] = Package::load();
        Package::validate($before, $patch, $master, $contract);
        self::assertSame('c96e6b4e0c042f98fb0c105f61225f72d85a4850', $master['base_sha']);
        self::assertSame('M66_source_before', $before['content_type']);
        self::assertSame('M66_Verb_Patterns_Core', $patch['content_type']);
        self::assertSame([193, 196, 199], Package::PAGE_IDS);
        self::assertSame([33, 33, 33], Package::CATEGORY_IDS);
        self::assertSame([6, 6, 6], Package::CONTINUING_COUNTS);
        self::assertSame(['A2', 'B1', 'B1'], Package::LEVELS);
        self::assertSame(['A2-B1', 'B1', 'B1'], Package::DISPLAY_LEVELS);
        $native = ['hero', 'forms-grid', 'usage-panels', 'mistakes-grid', 'summary-list', 'navigation-chips', 'practice-set'];
        $columns = ['header', 'left', 'left', 'right', 'right', 'footer', 'left'];
        $ids = [];
        foreach ($patch['targets'] as $i => $target) {
            $old = $before['targets'][$i]['before']; $lesson = $master['lessons'][$i];
            $types = $native; if ($i === 1) { $types[1] = 'comparison-table'; }
            self::assertSame($types, array_column($lesson['blocks'], 'type'));
            self::assertSame($columns, array_column($lesson['blocks'], 'column'));
            self::assertSame([0, 1, 2, 3, 4, 5, null], array_column($lesson['blocks'], 'existing_source_index'));
            self::assertSame(['verb-patterns'], $target['ancestry']);
            self::assertSame(Package::definition($lesson, $old), $target['after']);
            self::assertSame($target['after'], json_decode(File::get(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR));
            M66ContentPatch::assertDefinitionScope($old, $target['after'], $target, $i);
            $protected = $target['after']; $protected['page']['blocks'] = $old['page']['blocks'];
            foreach (['subtitle_html', 'subtitle_text'] as $field) { $protected['page'][$field] = $old['page'][$field]; }
            self::assertSame($old, $protected, 'No title/category/tags/owner/locale metadata mutation.');
            foreach ($old['page']['blocks'] as $slot => $source) {
                $after = $target['after']['page']['blocks'][$slot];
                self::assertSame(M26DetailPackage::uuid($target['identity'], $source, $slot + 1),
                    M26DetailPackage::uuid($target['identity'], $after, $slot + 1));
                if ($slot === 5) { self::assertSame(json_decode($source['body'], true), $lesson['blocks'][5]['data']); }
                unset($source['body'], $after['body']); self::assertSame($source, $after, 'No native metadata transition.');
            }
            $practice = $lesson['blocks'][6]; self::assertSame(Package::LEVELS[$i], $practice['level']);
            self::assertSame($contract['targets'][$i]['bank_seeders'], $practice['data']['linked_practice']['seeder_classes']);
            foreach (['selects', 'choices', 'inputs'] as $group) {
                self::assertCount(2, $practice['data'][$group]);
                foreach ($practice['data'][$group] as $task) {
                    $ids[] = $task['id']; self::assertContains($task['answer'], $task['accepted']);
                    self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $task[$group === 'inputs' ? 'after' : 'label']);
                }
            }
            foreach ($target['after']['page']['blocks'] as $source) { self::assertLessThanOrEqual(65535, strlen($source['body'])); }
        }
        self::assertCount(18, $ids); self::assertCount(18, array_unique($ids));
    }

    public function test_foreign_owners_categories_locales_cefr_native_metadata_and_unplanned_appends_are_rejected(): void
    {
        [$before, $patch, $master, $contract] = Package::load();
        foreach (['owner', 'unregistered-owner', 'category-owner', 'category-title', 'locale', 'ancestry', 'page-id',
            'category-id', 'technical-level', 'display-level', 'column', 'changed-type', 'old-heading', 'extra-slot',
            'duplicate-practice', 'new-key', 'missing-bank', 'foreign-view', 'before-provenance', 'patch-provenance',
            'stale-after', 'stale-before', 'duplicate-task', 'detail-owner'] as $case) {
            $b = $before; $p = $patch; $m = $master; $c = $contract;
            if ($case === 'owner') { $p['targets'][0]['identity'] = Package::OWNERS[2]; }
            if ($case === 'unregistered-owner') { $m['lessons'][0]['identity'] = 'Foreign\\UnregisteredM66Owner'; }
            if ($case === 'category-owner') { $b['targets'][0]['before']['content_type'] = 'category'; }
            if ($case === 'category-title') { $p['targets'][1]['after']['page']['category']['title'] = 'Unapproved category'; }
            if ($case === 'locale') { $m['lessons'][1]['locale'] = 'pl'; }
            if ($case === 'ancestry') { $p['targets'][1]['ancestry'] = ['relative-clauses']; }
            if ($case === 'page-id') { $p['targets'][0]['page_binding']['id'] = 999999; }
            if ($case === 'category-id') { $p['targets'][2]['page_binding']['page_category_id'] = 999999; }
            if ($case === 'technical-level') { $m['lessons'][0]['blocks'][1]['level'] = 'B1'; }
            if ($case === 'display-level') { $m['lessons'][0]['blocks'][0]['data']['level'] = 'A2'; }
            if ($case === 'column') { $m['lessons'][2]['blocks'][3]['column'] = 'left'; }
            if ($case === 'changed-type') { $m['lessons'][1]['blocks'][1]['type'] = 'forms-grid'; }
            if ($case === 'old-heading') { $p['targets'][0]['after']['page']['blocks'][1]['heading'] = 'Unapproved heading'; }
            if ($case === 'extra-slot' || $case === 'duplicate-practice') { $m['lessons'][0]['blocks'][] = $m['lessons'][0]['blocks'][6]; }
            if ($case === 'new-key') { $m['lessons'][0]['blocks'][6]['key'] = 'unapproved-practice'; }
            if ($case === 'missing-bank') { $c['targets'][2]['bank_seeders'] = []; }
            if ($case === 'foreign-view') { $m['lessons'][0]['blocks'][1]['data']['native_view'] = 'm66-private-design'; }
            if ($case === 'before-provenance') { $b['content_type'] = 'M65_source_before'; }
            if ($case === 'patch-provenance') { $p['content_type'] = 'M65_Purpose_Gerund_Infinitive'; }
            if ($case === 'stale-after') { $p['targets'][2]['after']['page']['blocks'][2]['body'] .= "\n"; }
            if ($case === 'stale-before') { $b['targets'][0]['before']['slug'] = 'later-local-edit'; }
            if ($case === 'duplicate-task') { $m['lessons'][1]['blocks'][6]['data']['inputs'][1]['id'] = $m['lessons'][0]['blocks'][6]['data']['inputs'][0]['id']; }
            if ($case === 'detail-owner') { $m['lessons'][2]['details'][0]['owner_block_key'] = 'foreign-point'; }
            $this->rejects(fn () => Package::validate($b, $p, $m, $c), $case);
        }
    }

    public function test_frozen_raw_bytes_and_independent_strong_subtitle_titles_remain_exact(): void
    {
        [$before, $patch, $master, $contract] = Package::load();
        foreach (Package::OWNERS as $i => $owner) {
            $m = $master; $m['lessons'][$i]['subtitle_html'] = '<p>Foreign plain display title</p>';
            $p = $patch; $p['targets'][$i]['after'] = Package::definition($m['lessons'][$i], $before['targets'][$i]['before']);
            $this->rejects(fn () => Package::validate($before, $p, $m, $contract), $owner);
        }
        $profile = Package::profile(); $profile['artifacts'][Package::MASTER] = str_repeat('0', 64);
        $this->rejects(fn () => (new TheoryNativeContentRelease($profile))->load(), 'raw checksum cannot be relaxed');
    }
}
