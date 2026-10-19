<?php

namespace Tests\Feature;

use App\Support\M26DetailPackage;
use App\Support\M65PurposeGerundInfinitivePackage as Package;
use App\Support\TheoryNativeContentRelease;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\TestCase;

class M65NativeTransitionTest extends TestCase
{
    public function test_five_explicit_purpose_box_transitions_preserve_null_levels_columns_uuids_and_old_bytes(): void
    {
        $before = json_decode(File::get(base_path(Package::BEFORE)), true, flags: JSON_THROW_ON_ERROR);
        $release = new TheoryNativeContentRelease(Package::profile());
        $types = [0 => [1 => 'forms-grid', 2 => 'usage-panels', 3 => 'comparison-table', 4 => 'mistakes-grid', 5 => 'summary-list']];
        $count = 0;
        foreach ($before['targets'] as $i => $target) {
            $old = $target['before']; $unchanged = $old; $after = $release->projectedBefore($old);
            foreach ($old['page']['blocks'] as $slot => $block) {
                $new = $after['page']['blocks'][$slot];
                self::assertSame($block['body'], $new['body']); self::assertSame($block['column'], $new['column']);
                self::assertSame($block['level'] ?? null, $new['level'] ?? null);
                self::assertSame(M26DetailPackage::uuid($target['identity'], $block, $slot + 1), M26DetailPackage::uuid($target['identity'], $new, $slot + 1));
                if (isset($types[$i][$slot])) { ++$count; self::assertSame('box', $block['type']); self::assertSame($types[$i][$slot], $new['type']); self::assertArrayNotHasKey('heading', $new); }
                else { self::assertSame($block, $new); }
            }
            self::assertSame($unchanged, $old);
        }
        self::assertSame(5, $count);
    }

    public function test_unregistered_null_level_promotion_column_and_heading_are_refused(): void
    {
        $before = json_decode(File::get(base_path(Package::BEFORE)), true, flags: JSON_THROW_ON_ERROR);
        foreach (['column', 'promoted', 'heading', 'owner'] as $case) {
            $old = $before['targets'][0]['before'];
            if ($case === 'column') { $old['page']['blocks'][1]['column'] = 'right'; }
            if ($case === 'promoted') { $old['page']['blocks'][1]['level'] = 'B2'; }
            if ($case === 'heading') { $old['page']['blocks'][1]['heading'] = 'Generic'; }
            if ($case === 'owner') { $old['seeder']['class'] = 'Foreign\\UnregisteredOwner'; }
            $caught = null; try { (new TheoryNativeContentRelease(Package::profile()))->projectedBefore($old); } catch (RuntimeException $error) { $caught = $error; }
            self::assertInstanceOf(RuntimeException::class, $caught, $case);
        }
    }
}
