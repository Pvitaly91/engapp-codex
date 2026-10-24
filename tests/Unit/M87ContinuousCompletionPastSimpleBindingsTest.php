<?php

namespace Tests\Unit;

use App\Support\M87ContinuousCompletionPastSimple as Package;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Pure finite bindings; no application, environment, database or HTTP bootstrap. */
class M87ContinuousCompletionPastSimpleBindingsTest extends TestCase
{
    private function bindings(): array
    {
        $out = [];
        foreach (Package::OWNERS as $i => $owner) {
            $out[] = ['identity' => $owner, 'slug' => Package::SLUGS[$i], 'page_id' => Package::PAGE_IDS[$i],
                'category_id' => Package::CATEGORY_IDS[$i], 'ancestry' => Package::ANCESTRIES[$i],
                'local_url' => 'http://gramlyze.loc/theory/'.implode('/', Package::ANCESTRIES[$i]).'/'.Package::SLUGS[$i],
                'practice_level' => null, 'display_level' => 'A1–A2', 'rendered_title' => Package::RENDERED_TITLES[$i],
                'continuing_levels' => array_fill(0, Package::CONTINUING_COUNTS[$i], null),
                'appended_blocks' => $i >= 2 ? [] : [['key' => 'm87-'.Package::SLUGS[$i].'-practice',
                    'type' => 'practice-set', 'column' => 'left', 'level' => null]],
                'practice_contract' => ['counts' => $i >= 2 ? ['selects' => 3, 'choices' => $i === 5 ? 3 : 2, 'inputs' => 3]
                    : ['selects' => 2, 'choices' => 2, 'inputs' => 2],
                    'sentence_input_indices' => $i >= 2 ? [0, 1, 2] : [0, 1]]];
            if ($i >= 2) {
                $out[$i]['existing_practice'] = ['source_index' => 6, 'uuid' => Package::EXISTING_PRACTICE_UUIDS[$i], 'level' => null];
            }
        }
        return $out;
    }

    private function refused(array $bindings, string $message): void
    {
        try { Package::assertBindings($bindings); self::fail('Changed binding was not refused: '.$message); }
        catch (RuntimeException $e) { self::assertStringStartsWith('M87 ', $e->getMessage(), $message); }
    }

    public function test_independent_source_metadata_vectors_and_thin_native_profile(): void
    {
        self::assertSame('443032b85caf8a27fffb372bb81bc2f0b023314b', Package::BASE_SHA);
        self::assertSame([172, 173, 338, 339, 340, 341], Package::PAGE_IDS);
        self::assertSame([28, 28, 61, 61, 61, 61], Package::CATEGORY_IDS);
        self::assertSame([8, 7, 8, 8, 9, 8], Package::CONTINUING_COUNTS);
        self::assertSame([8, 7, 6, 6, 6, 6], Package::PRACTICE_SOURCE_INDICES);
        self::assertSame([6, 6, 7, 7, 7, 7], Package::NAVIGATION_SOURCE_INDICES);
        self::assertSame(['A1', 'A2', 'A1', 'A1', 'A1', 'A2'], Package::FOCUS_LEVELS);
        self::assertSame(array_fill(0, 6, null), Package::LEVELS);
        self::assertCount(6, array_unique(Package::OWNERS)); self::assertCount(6, array_unique(Package::BANKS));
        $bindings = $this->bindings(); Package::assertBindings($bindings); $profile = Package::draftProfile($bindings);
        self::assertSame('m87_native_v1', $profile['marker']); self::assertSame('m87-native-v1', $profile['design']);
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

    public function test_legacy_shared_uuid_key_does_not_make_four_practices_interchangeable(): void
    {
        self::assertSame([null, null, '2688b187-0d3a-5a4c-82fe-113aabeeebbc', 'd3209c7b-66ee-5a9a-bf6f-a0e35bd96a84',
            '07c67d3a-196b-5b7f-b226-071d110482e9', '86248bae-9513-56a1-b98c-426b0a57785c'],
            Package::EXISTING_PRACTICE_UUIDS);
        foreach (range(2, 5) as $i) {
            $b = $this->bindings(); $b[$i]['existing_practice']['uuid'] = Package::EXISTING_PRACTICE_UUIDS[2 + (($i - 2 + 1) % 4)];
            $b[$i]['uuid_key'] = 'past-simple-practice'; $this->refused($b, 'shared key with foreign UUID');
            $b = $this->bindings(); $b[$i]['existing_practice']['source_index'] = 7; $this->refused($b, 'moved continuing practice');
        }
    }

    public function test_display_range_cannot_replace_null_sql_or_finite_sentence_indices(): void
    {
        foreach (range(0, 5) as $i) {
            foreach (['sql', 'range', 'index', 'count'] as $kind) {
                $b = $this->bindings();
                if ($kind === 'sql') { $b[$i]['practice_level'] = 'A1–A2'; }
                elseif ($kind === 'range') { $b[$i]['display_level'] = 'A1'; }
                elseif ($kind === 'index') { $b[$i]['practice_contract']['sentence_input_indices'][] = 99; }
                else { $b[$i]['continuing_levels'][] = null; }
                $this->refused($b, $i.':'.$kind);
            }
        }
    }

    public function test_existing_practice_cannot_be_duplicated_and_missing_practice_cannot_be_invented_as_continuing(): void
    {
        $b = $this->bindings(); $b[2]['appended_blocks'] = $b[0]['appended_blocks']; $this->refused($b, 'duplicate source practice');
        foreach ([0, 1] as $i) {
            $b = $this->bindings(); $b[$i]['existing_practice'] = ['source_index' => 6, 'uuid' => Package::EXISTING_PRACTICE_UUIDS[2], 'level' => null];
            $this->refused($b, 'invented old practice');
            $b = $this->bindings(); $b[$i]['appended_blocks'][] = $b[$i]['appended_blocks'][0]; $this->refused($b, 'two appended practices');
        }
    }
}
