<?php

namespace Tests\Unit;

use App\Support\M91FutureSimpleCompletionContinuous as Package;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Pure M91 metadata and two-retained/four-appended binding policy; no app/SQL/HTTP. */
class M91FutureSimpleCompletionContinuousBindingsTest extends TestCase
{
    private const EXISTING = [
        'ec3bed0d-bb72-5f4e-89b4-6303cfe166a5',
        '412badfc-e254-5b74-b2ef-5cdb4a41819d',
    ];

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
                'appended_blocks' => $i < 2 ? [] : [['key' => Package::SLUGS[$i].'-practice', 'type' => 'practice-set', 'column' => 'left', 'level' => null]],
                'practice_contract' => ['counts' => ['selects' => 2, 'choices' => 2, 'inputs' => 2],
                    'sentence_input_indices' => [0, 1]]];
            if ($i < 2) {
                $out[$i]['existing_practice'] = ['source_index' => 5, 'uuid' => self::EXISTING[$i], 'level' => null];
            }
        }
        return $out;
    }

    private function refused(array $bindings, string $why): void
    {
        $caught = null;
        try { Package::assertBindings($bindings); } catch (RuntimeException $e) { $caught = $e; }
        self::assertInstanceOf(RuntimeException::class, $caught, $why);
        self::assertStringStartsWith('M91 ', $caught->getMessage());
    }

    public function test_fresh_six_owner_metadata_and_null_sql_are_not_editorial_focus_levels(): void
    {
        self::assertSame('03a72ee95374385995b29e00d7961ee396632fde', Package::BASE_SHA);
        self::assertSame([344, 345, 54, 55, 56, 57], Package::PAGE_IDS);
        self::assertSame([62, 62, 11, 11, 11, 11], Package::CATEGORY_IDS);
        self::assertSame([7, 7, 7, 7, 7, 7], Package::CONTINUING_COUNTS);
        self::assertSame([5, 5, 7, 7, 7, 7], Package::PRACTICE_SOURCE_INDICES);
        self::assertSame([6, 6, 6, 6, 6, 6], Package::NAVIGATION_SOURCE_INDICES);
        self::assertSame(['A1', 'A1', 'B1', 'B1', 'B1', 'B1'], Package::FOCUS_LEVELS);
        self::assertSame(array_fill(0, 6, null), Package::LEVELS);
        self::assertSame(['A1–A2', 'A1–A2', 'B1', 'B1', 'B1', 'B1'], Package::DISPLAY_LEVELS);
        self::assertSame([
            ['maibutni-formy', 'future-simple'], ['maibutni-formy', 'future-simple'],
            ['maibutni-formy', 'future-continuous'], ['maibutni-formy', 'future-continuous'],
            ['maibutni-formy', 'future-continuous'], ['maibutni-formy', 'future-continuous'],
        ], Package::ANCESTRIES);
        self::assertCount(6, array_unique(Package::OWNERS)); self::assertCount(6, array_unique(Package::BANKS));
        $bindings = $this->bindings(); Package::assertBindings($bindings); $profile = Package::draftProfile($bindings);
        self::assertSame('m91_native_v1', $profile['marker']); self::assertSame('m91-native-v1', $profile['design']);
        self::assertSame($bindings, $profile['bindings']); self::assertArrayNotHasKey('artifacts', $profile);
    }

    public function test_wrong_owner_page_category_path_locale_and_missing_or_reordered_targets_are_refused(): void
    {
        foreach (range(0, 5) as $i) {
            foreach (['owner', 'page', 'category', 'url', 'ancestry'] as $case) {
                $b = $this->bindings();
                if ($case === 'owner') { $b[$i]['identity'] = Package::OWNERS[($i + 1) % 6]; }
                elseif ($case === 'page') { $b[$i]['page_id'] = Package::PAGE_IDS[($i + 1) % 6]; }
                elseif ($case === 'category') { $b[$i]['category_id'] = 8; }
                elseif ($case === 'url') { $b[$i]['local_url'] = 'http://gramlyze.loc/theory/tenses/'.Package::SLUGS[$i]; }
                else { $b[$i]['ancestry'] = ['en', 'tenses', 'future-simple']; }
                $this->refused($b, $i.':'.$case);
            }
        }
        $b = $this->bindings(); array_pop($b); $this->refused($b, 'Missing owner.');
        $b = $this->bindings(); [$b[0], $b[1]] = [$b[1], $b[0]]; $this->refused($b, 'Reordered targets.');
    }

    public function test_existing_two_practices_keep_exact_source_five_owner_uuid_without_duplicate_append(): void
    {
        foreach (range(0, 1) as $i) {
            $b = $this->bindings(); unset($b[$i]['existing_practice']); $this->refused($b, 'Existing practice lost.');
            $b = $this->bindings(); $b[$i]['existing_practice']['uuid'] = self::EXISTING[1 - $i]; $this->refused($b, 'Foreign existing UUID.');
            $b = $this->bindings(); $b[$i]['existing_practice']['source_index'] = 7; $this->refused($b, 'Existing practice moved.');
            $b = $this->bindings(); $b[$i]['appended_blocks'] = [['key' => 'practice', 'type' => 'practice-set', 'column' => 'left', 'level' => null]];
            $this->refused($b, 'A/B cannot receive a duplicate practice.');
        }
    }

    public function test_four_new_practices_are_exact_one_left_null_append_without_invented_old_uuid(): void
    {
        foreach (range(2, 5) as $i) {
            foreach (['missing', 'duplicate', 'foreign-key', 'column', 'level', 'existing'] as $case) {
                $b = $this->bindings();
                if ($case === 'missing') { $b[$i]['appended_blocks'] = []; }
                elseif ($case === 'duplicate') { $b[$i]['appended_blocks'][] = $b[$i]['appended_blocks'][0]; }
                elseif ($case === 'foreign-key') { $b[$i]['appended_blocks'][0]['key'] = 'm90-foreign-practice'; }
                elseif ($case === 'column') { $b[$i]['appended_blocks'][0]['column'] = 'right'; }
                elseif ($case === 'level') { $b[$i]['appended_blocks'][0]['level'] = 'B1'; }
                else { $b[$i]['existing_practice'] = ['source_index' => 5, 'uuid' => self::EXISTING[0], 'level' => null]; }
                $this->refused($b, $i.':'.$case);
            }
        }
    }

    public function test_display_or_focus_cannot_replace_null_sql_or_exact_two_two_two_contract(): void
    {
        foreach (range(0, 5) as $i) {
            foreach (['sql', 'display', 'index', 'continuing', 'counts'] as $case) {
                $b = $this->bindings();
                if ($case === 'sql') { $b[$i]['practice_level'] = Package::FOCUS_LEVELS[$i]; }
                elseif ($case === 'display') { $b[$i]['display_level'] = $i < 2 ? 'A2' : 'A1–C2'; }
                elseif ($case === 'index') { $b[$i]['practice_contract']['sentence_input_indices'][] = 99; }
                elseif ($case === 'continuing') { $b[$i]['continuing_levels'][] = null; }
                else { $b[$i]['practice_contract']['counts']['choices'] = 3; }
                $this->refused($b, $i.':'.$case);
            }
        }
    }
}
