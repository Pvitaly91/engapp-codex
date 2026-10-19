<?php

namespace Tests\Feature;

use App\Support\M55PassiveReviewInfinitivePackage;
use App\Support\M56GerundSimplePassivesPackage as Package;
use App\Support\TheoryNativeContentRelease;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Data-policy negatives only; no working database, source or presentation mutation. */
class M56NativeCompatibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp(); app()->setLocale('uk'); $this->withoutVite();
        self::assertSame('testing', app()->environment());
        self::assertNotSame(base_path(), app()->environmentPath());
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
    }

    private function rejects(callable $operation, string $case): void
    {
        $error = null;
        try { $operation(); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error, $case);
    }

    public function test_m55_plain_subtitle_neutral_projection_and_frozen_artifacts_remain_accepted(): void
    {
        [$before, $patch, $master, $contract] = M55PassiveReviewInfinitivePackage::load();
        M55PassiveReviewInfinitivePackage::validate($before, $patch, $master, $contract);
        $old = M55PassiveReviewInfinitivePackage::profile(); $current = Package::profile();
        self::assertSame([7 => 'usage-panels'], $old['bindings'][1]['component_overrides']);
        self::assertSame('captured-plain', $old['bindings'][2]['before_subtitle']['mode']);
        self::assertSame('0fa42a349d06ce69204e4531678883d4a5cbe3c15fdd9f5e1f91d8bbc7b4fb4e', $old['bindings'][2]['before_subtitle']['sha256']);
        foreach ($current['bindings'] as $binding) { self::assertArrayNotHasKey('component_overrides', $binding); }
        self::assertNotSame($old['bindings'][2]['before_subtitle']['sha256'], $current['bindings'][0]['before_subtitle']['sha256']);
    }

    public function test_gerund_plain_before_is_exact_independent_capture_not_a_global_plain_title_fallback(): void
    {
        [$before, $patch, $master, $contract] = Package::load(); $profile = Package::profile();
        $capture = $profile['bindings'][0]['before_subtitle'];
        self::assertSame('fbc30cdfc88a1916b9763c4c53f132947f4341bde9b76e03f99d5eb51b42660e', $capture['sha256']);
        self::assertSame($capture['sha256'], hash('sha256', $before['targets'][0]['before']['page']['subtitle_html']));
        self::assertSame('Passive Gerund', $capture['extracted_title']);
        self::assertSame('Passive Gerund', $capture['h1']);
        foreach (['missing-opt-in', 'sha256', 'raw_page_title', 'extracted_title', 'h1', 'mode', 'editorial-h1', 'after-title'] as $case) {
            $p = $profile; $b = $before; $m = $master; $target = $patch;
            if ($case === 'missing-opt-in') { unset($p['bindings'][0]['before_subtitle']); }
            elseif ($case === 'editorial-h1') { $m['editorial_review']['pre_apply_metadata'][0]['h1'] = 'Foreign'; }
            elseif ($case === 'after-title') { $m['lessons'][0]['subtitle_html'] = '<p>Passive Gerund — Plain AFTER is not allowed.</p>'; }
            else { $p['bindings'][0]['before_subtitle'][$case] = $case === 'sha256' ? str_repeat('0', 64) : 'Foreign'; }
            $release = new TheoryNativeContentRelease($p);
            $target['targets'][0]['after'] = $release->definition($m['lessons'][0], $b['targets'][0]['before']);
            $this->rejects(fn () => $release->validate($b, $target, $m, $contract), $case);
        }
    }

    public function test_page_category_ancestry_and_nonvisual_policy_are_separate_exact_boundaries(): void
    {
        [$before, $patch, $master, $contract] = Package::load();
        foreach (['category-before', 'category-after', 'gerund-flat', 'present-false-tenses', 'past-false-tenses', 'foreign-owner', 'foreign-locale'] as $case) {
            $b = $before; $p = $patch; $m = $master;
            if ($case === 'category-before') { $b['targets'][0]['before']['content_type'] = 'category'; }
            if ($case === 'category-after') { $p['targets'][0]['after']['content_type'] = 'category'; }
            if ($case === 'gerund-flat') { $p['targets'][0]['ancestry'] = ['passive-voice']; }
            if ($case === 'present-false-tenses') { $p['targets'][1]['ancestry'] = ['passive-voice', 'tenses']; }
            if ($case === 'past-false-tenses') { $p['targets'][2]['ancestry'] = ['passive-voice', 'tenses']; }
            if ($case === 'foreign-owner') { $p['targets'][0]['identity'] = M55PassiveReviewInfinitivePackage::OWNERS[2]; }
            if ($case === 'foreign-locale') { $m['lessons'][0]['locale'] = 'en'; }
            $this->rejects(fn () => Package::validate($b, $p, $m, $contract), $case);
        }
    }
}
