<?php

namespace Tests\Unit;

use App\Support\M90PastPerfectFutureSimpleBasics as Package;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Pure finite bindings; no application, environment, database or HTTP bootstrap. */
class M90PastPerfectFutureSimpleBasicsBindingsTest extends TestCase
{
    private function bindings(): array
    {
        $out = [];
        foreach (Package::OWNERS as $i => $owner) {
            $out[] = ['identity' => $owner, 'slug' => Package::SLUGS[$i], 'page_id' => Package::PAGE_IDS[$i],
                'category_id' => Package::CATEGORY_IDS[$i], 'ancestry' => Package::ANCESTRIES[$i],
                'local_url' => 'http://gramlyze.loc/theory/'.implode('/', Package::ANCESTRIES[$i]).'/'.Package::SLUGS[$i],
                'practice_level' => null, 'display_level' => Package::DISPLAY_LEVELS[$i],
                'rendered_title' => Package::RENDERED_TITLES[$i],
                'continuing_levels' => array_fill(0, Package::CONTINUING_COUNTS[$i], null),
                'appended_blocks' => [],
                'practice_contract' => ['counts' => ['selects' => 2, 'choices' => 2, 'inputs' => 2],
                    'sentence_input_indices' => [0, 1]]];
            $out[$i]['existing_practice'] = ['source_index' => Package::PRACTICE_SOURCE_INDICES[$i], 'uuid' => Package::EXISTING_PRACTICE_UUIDS[$i], 'level' => null];
        }
        return $out;
    }

    private function refused(array $bindings, string $message): void
    {
        try { Package::assertBindings($bindings); self::fail('Changed binding was not refused: '.$message); }
        catch (RuntimeException $e) { self::assertStringStartsWith('M90 ', $e->getMessage(), $message); }
    }

    public function test_independent_source_metadata_vectors_and_thin_native_profile(): void
    {
        self::assertSame('9607ebefbbf870f4b303bbc05a0c6c0ed0341a17', Package::BASE_SHA);
        self::assertSame([158, 159, 160, 161, 342, 343], Package::PAGE_IDS);
        self::assertSame([25, 25, 25, 25, 62, 62], Package::CATEGORY_IDS);
        self::assertSame([7, 7, 7, 7, 7, 7], Package::CONTINUING_COUNTS);
        self::assertSame([5, 5, 5, 5, 5, 5], Package::PRACTICE_SOURCE_INDICES);
        self::assertSame([6, 6, 6, 6, 6, 6], Package::NAVIGATION_SOURCE_INDICES);
        self::assertSame(['A2', 'A2', 'A2', 'A2', 'A1', 'A1'], Package::FOCUS_LEVELS);
        self::assertSame(array_fill(0, 6, null), Package::LEVELS);
        self::assertSame(['A2–B1', 'A2–B1', 'A2–B1', 'A2–B1', 'A1–A2', 'A1–A2'], Package::DISPLAY_LEVELS);
        self::assertCount(6, array_unique(Package::OWNERS)); self::assertCount(6, array_unique(Package::BANKS));
        $bindings = $this->bindings(); Package::assertBindings($bindings); $profile = Package::draftProfile($bindings);
        self::assertSame('m90_native_v1', $profile['marker']); self::assertSame('m90-native-v1', $profile['design']);
        self::assertSame($bindings, $profile['bindings']); self::assertArrayNotHasKey('artifacts', $profile);
    }

    public function test_missing_swapped_foreign_owners_and_wrong_categories_are_refused_for_each_owner(): void
    {
        foreach (range(0, 5) as $i) {
            foreach (['owner', 'category', 'page', 'url', 'locale_path'] as $kind) {
                $b = $this->bindings();
                if ($kind === 'owner') { $b[$i]['identity'] = Package::OWNERS[($i + 1) % 6]; }
                elseif ($kind === 'category') { $b[$i]['category_id'] = 8; }
                elseif ($kind === 'page') { $b[$i]['page_id'] = Package::PAGE_IDS[($i + 1) % 6]; }
                elseif ($kind === 'url') { $b[$i]['local_url'] = 'http://gramlyze.loc/theory/'.Package::SLUGS[$i]; }
                else { $b[$i]['ancestry'] = ['en', 'tenses', 'present-simple']; }
                $this->refused($b, $i.':'.$kind);
            }
        }
        $b = $this->bindings(); array_pop($b); $this->refused($b, 'missing owner');
        $b = $this->bindings(); [$b[0], $b[1]] = [$b[1], $b[0]]; $this->refused($b, 'reordered owners');
    }

    public function test_all_six_existing_practices_have_exact_owner_uuids_not_a_shared_key(): void
    {
        self::assertSame(['1d3f76f9-51b0-58fb-94ea-ee34fe20e320', 'cb34f0f6-5995-5e27-8b2b-4d41323c445a',
            'd21a1e3d-2a43-566a-99ef-9685e2f74afe', '8602672d-8ade-5551-adc8-c40bd8fa22b4',
            'd98377db-0351-538b-b152-fec103a88fe3', 'f2317f57-bd1d-5c99-87a0-4563d0b13fa5'], Package::EXISTING_PRACTICE_UUIDS);
        foreach (range(0, 5) as $i) {
            $b = $this->bindings(); $b[$i]['existing_practice']['uuid'] = Package::EXISTING_PRACTICE_UUIDS[($i + 1) % 6];
            $b[$i]['uuid_key'] = 'practice'; $this->refused($b, 'Shared key cannot substitute a foreign owner UUID.');
            $b = $this->bindings(); $b[$i]['existing_practice']['source_index'] = Package::PRACTICE_SOURCE_INDICES[$i] + 1; $this->refused($b, 'Moved continuing practice');
        }
    }

    public function test_display_range_cannot_replace_null_sql_or_finite_sentence_indices(): void
    {
        foreach (range(0, 5) as $i) {
            foreach (['sql', 'range', 'index', 'count'] as $kind) {
                $b = $this->bindings();
                if ($kind === 'sql') { $b[$i]['practice_level'] = 'A2'; }
                elseif ($kind === 'range') { $b[$i]['display_level'] = $i < 4 ? 'A1' : 'A2'; }
                elseif ($kind === 'index') { $b[$i]['practice_contract']['sentence_input_indices'][] = 99; }
                else { $b[$i]['continuing_levels'][] = null; }
                $this->refused($b, $i.':'.$kind);
            }
        }
    }

    public function test_no_existing_practice_can_be_missing_or_duplicated_as_an_append(): void
    {
        foreach (range(0, 5) as $i) {
            $b = $this->bindings(); unset($b[$i]['existing_practice']); $this->refused($b, 'Missing owner-bound practice');
            $b = $this->bindings(); $b[$i]['appended_blocks'] = [['key' => 'm90-duplicate-practice',
                'type' => 'practice-set', 'column' => 'left', 'level' => null]];
            $this->refused($b, 'No seventh/new practice is authorised.');
            $b = $this->bindings(); $b[$i]['practice_contract']['counts']['choices'] = 3;
            $this->refused($b, 'Exactly two new meaning controls are required for every owner.');
        }
    }
}
