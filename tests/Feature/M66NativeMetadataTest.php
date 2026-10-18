<?php

namespace Tests\Feature;

use App\Support\M66VerbPatternsCorePackage as Package;
use App\Support\TheoryNativeContentRelease;
use Tests\TestCase;

/** M65's old box transitions are not implicitly inherited by M66 native owners. */
class M66NativeMetadataTest extends TestCase
{
    public function test_projected_before_is_literal_identity_for_all_eighteen_native_source_blocks(): void
    {
        [$before] = Package::load(); $release = new TheoryNativeContentRelease(Package::profile());
        $count = 0;
        foreach (Package::profile()['bindings'] as $binding) {
            self::assertArrayNotHasKey('continuing_metadata', $binding);
            self::assertArrayNotHasKey('preserved_native_metadata', $binding);
        }
        foreach ($before['targets'] as $target) {
            $old = $target['before']; self::assertSame($old, $release->projectedBefore($old));
            $count += count($old['page']['blocks']);
        }
        self::assertSame(18, $count);
    }

    public function test_metadata_projection_refuses_a_truly_unregistered_owner_not_accepted_gerund_m65(): void
    {
        [$before] = Package::load(); $old = $before['targets'][0]['before'];
        $old['seeder']['class'] = 'Foreign\\NeverRegisteredM66Owner';
        $this->expectException(\RuntimeException::class);
        (new TheoryNativeContentRelease(Package::profile()))->projectedBefore($old);
    }
}
