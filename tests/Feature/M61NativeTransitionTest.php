<?php

namespace Tests\Feature;

use App\Support\M26DetailPackage;
use App\Support\M61ReportedSpeechChangesPackage as Package;
use App\Support\TheoryNativeContentRelease;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\TestCase;

/** Source-independent expected semantics for M61's twelve existing box transitions. */
class M61NativeTransitionTest extends TestCase
{
    public function test_existing_table_boxes_remain_comparison_tables_and_only_reviewed_null_metadata_changes(): void
    {
        $before = json_decode(File::get(base_path(Package::BEFORE)), true, flags: JSON_THROW_ON_ERROR);
        $release = new TheoryNativeContentRelease(Package::profile());
        $types = [
            ['comparison-table', 'usage-panels', 'mistakes-grid', 'summary-list'],
            ['comparison-table', 'usage-panels', 'mistakes-grid', 'summary-list'],
            ['forms-grid', 'comparison-table', 'mistakes-grid', 'summary-list'],
        ];
        foreach ($before['targets'] as $i => $target) {
            $old = $target['before']; $unchanged = $old; $after = $release->projectedBefore($old);
            foreach ($old['page']['blocks'] as $slot => $block) {
                $new = $after['page']['blocks'][$slot];
                self::assertSame($block['body'], $new['body']); self::assertSame($block['column'], $new['column']);
                self::assertSame($block['level'] ?? null, $new['level'] ?? null);
                self::assertSame(M26DetailPackage::uuid($target['identity'], $block, $slot + 1),
                    M26DetailPackage::uuid($target['identity'], $new, $slot + 1));
                if ($slot >= 1 && $slot <= 4) {
                    self::assertSame('box', $block['type']); self::assertSame($types[$i][$slot - 1], $new['type']);
                    self::assertArrayNotHasKey('heading', $new);
                } else { self::assertSame($block, $new); }
            }
            self::assertSame($unchanged, $old);
            self::assertSame([['key' => ['backshift-practice', 'time-place-practice', 'say-tell-ask-practice'][$i],
                'type' => 'practice-set', 'column' => 'left', 'level' => 'B1']], Package::profile()['bindings'][$i]['appended_blocks']);
        }
    }

    public function test_unregistered_column_or_nonnull_level_is_not_authorized_by_existing_null_preservation(): void
    {
        $before = json_decode(File::get(base_path(Package::BEFORE)), true, flags: JSON_THROW_ON_ERROR);
        foreach (['right-column', 'promoted-b1', 'old-heading', 'foreign-owner'] as $case) {
            $source = $before['targets'][0]['before'];
            if ($case === 'right-column') { $source['page']['blocks'][1]['column'] = 'right'; }
            if ($case === 'promoted-b1') { $source['page']['blocks'][3]['level'] = 'B1'; }
            if ($case === 'old-heading') { $source['page']['blocks'][1]['heading'] = 'Generic forms'; }
            if ($case === 'foreign-owner') { $source['seeder']['class'] = Package::OWNERS[1]; }
            $caught = null;
            try { (new TheoryNativeContentRelease(Package::profile()))->projectedBefore($source); }
            catch (RuntimeException $error) { $caught = $error; }
            self::assertInstanceOf(RuntimeException::class, $caught, $case);
        }
    }
}
