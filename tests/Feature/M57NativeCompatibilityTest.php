<?php

namespace Tests\Feature;

use App\Support\M55PassiveReviewInfinitivePackage as M55;
use App\Support\M56GerundSimplePassivesPackage as M56;
use App\Support\M57ContinuousPerfectPassivesPackage as M57;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class M57NativeCompatibilityTest extends TestCase
{
    public function test_m55_plain_guard_neutral_component_and_current_m56_href_successor_remain_exact(): void
    {
        foreach ([M55::class, M56::class] as $package) { $all = $package::load(); $package::validate(...$all); }
        self::assertSame([7 => 'usage-panels'], M55::profile()['bindings'][1]['component_overrides']);
        self::assertSame('captured-plain', M55::profile()['bindings'][2]['before_subtitle']['mode']);
        self::assertSame('captured-plain', M56::profile()['bindings'][0]['before_subtitle']['mode']);
        self::assertSame('1.0.1', M56::profile()['version']);
        self::assertSame('e0d9563ba73aa78140f5e1e473a41949985970a65ab70c9db60aecc2b516e3fb', M56::MASTER_SHA);
        self::assertSame('3d2b4757255d9dde7ecb9826b1e37ef23e71cf3d985bed3a06479a3d978ef1ab', M56::SOURCE_SHA);
        foreach (M57::profile()['bindings'] as $binding) { self::assertArrayNotHasKey('before_subtitle', $binding); }
        $manifest = json_decode(File::get(base_path('docs/content/m56-gerund-simple-passives-checksums.v1.0.1.json')), true, flags: JSON_THROW_ON_ERROR);
        self::assertCount(15, $manifest['sha256']);
        foreach ($manifest['sha256'] as $path => $sha) { self::assertSame($sha, hash_file('sha256', base_path($path)), $path); }
        [, $patch] = M56::load(); $flat = '"/theory/passive-voice/theory-passive-voice-';
        foreach ([1, 2] as $i) {
            foreach ($patch['targets'][$i]['after']['page']['blocks'] as $block) {
                if ($block['type'] === 'navigation-chips') {
                    $data = json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR);
                    foreach ($data['items'] as $item) {
                        if (isset($item['url']) && preg_match('/(?:present-continuous|past-continuous|present-perfect|past-perfect|future-simple|future-continuous|future-perfect)$/D', $item['url'])) {
                            self::assertStringContainsString('/passive-voice/passive-voice-tenses/', $item['url']);
                        }
                    }
                }
            }
        }
    }
}
