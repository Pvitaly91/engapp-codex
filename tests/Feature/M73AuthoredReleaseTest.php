<?php

namespace Tests\Feature;

use App\Services\M73ContentPatch;
use App\Support\M26DetailPackage;
use App\Support\M71ArticlesQuantifiers;
use App\Support\M72BuilderSelectionPresentation;
use App\Support\M72PartitivesNegativesGeography;
use App\Support\M73GenericIrregularCollective as Package;
use App\Support\TheoryNativeContentRelease;
use App\Support\TheorySentenceBuilderAcceptance as Acceptance;
use Normalizer;
use RuntimeException;
use Tests\TestCase;

/** Frozen educational data and pure negative guards; no live database or network. */
class M73AuthoredReleaseTest extends TestCase
{
    private function refuses(callable $call, string $case): void
    {
        $caught = null;
        try { $call(); } catch (RuntimeException $error) { $caught = $error; }
        self::assertInstanceOf(RuntimeException::class, $caught, $case);
    }

    /** The accepted factory's comparison alphabet, not a new application grader. */
    private static function normal(string $value): string
    {
        $value = Normalizer::normalize($value, Normalizer::FORM_KD);
        self::assertIsString($value);
        $value = preg_replace('/[\x{0300}-\x{036f}]/u', '', mb_strtolower($value));
        $value = preg_replace('/[\x{2018}\x{2019}\x{201b}\x{2032}`´ʼ\x{0027}]/u', '', $value);
        return trim(preg_replace('/\s+/u', ' ', preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value)));
    }

    public function test_release_preserves_twenty_six_continuing_slots_and_appends_only_two_practices(): void
    {
        [$before, $patch, $master, $contract] = Package::load();
        Package::validate($before, $patch, $master, $contract);
        self::assertSame('cb55b329827e52a56cb6d41edb21e70768cf86f4', Package::BASE_SHA);
        self::assertSame(Package::BASE_SHA, $master['base_sha']);
        self::assertSame([80, 86, 84], Package::PAGE_IDS);
        self::assertSame([16, 16, 16], Package::CATEGORY_IDS);
        self::assertSame([13, 7, 6], Package::CONTINUING_COUNTS);
        self::assertSame([11, 7, 6], Package::PRACTICE_SOURCE_INDICES);
        self::assertSame([null, 'A2', 'B1'], array_column($contract['targets'], 'level'));
        $continuing = 0; $own = 0; $retained = 0; $newIds = [];
        foreach ($patch['targets'] as $i => $target) {
            $old = $before['targets'][$i]['before']; $lesson = $master['lessons'][$i];
            $added = $i === 0 ? 0 : 1;
            self::assertCount($added, Package::profile()['bindings'][$i]['appended_blocks']);
            self::assertCount(Package::CONTINUING_COUNTS[$i] + $added, $lesson['blocks']);
            self::assertSame(Package::definition($lesson, $old), $target['after']);
            self::assertSame($target['after'], json_decode(file_get_contents(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR));
            M73ContentPatch::assertDefinitionScope($old, $target['after'], $target, $i);
            $protected = $target['after']; $protected['page']['blocks'] = $old['page']['blocks'];
            foreach (['subtitle_html', 'subtitle_text'] as $field) { $protected['page'][$field] = $old['page'][$field]; }
            self::assertSame($old, $protected, 'Owner, title, category, tags and other source metadata remain exact.');
            self::assertSame([], $lesson['details']); self::assertNotEmpty($lesson['point_detail_decisions']);
            foreach ($lesson['point_detail_decisions'] as $decision) {
                self::assertSame('visible-basic', $decision['placement']); self::assertNotSame('', trim($decision['reason']));
            }
            foreach ($old['page']['blocks'] as $slot => $source) {
                ++$continuing; $new = $target['after']['page']['blocks'][$slot];
                self::assertSame($slot, $lesson['blocks'][$slot]['existing_source_index']);
                self::assertSame(array_key_exists('level', $source), array_key_exists('level', $new));
                self::assertSame($source['level'] ?? null, $new['level'] ?? null);
                self::assertSame(M26DetailPackage::uuid($target['identity'], $source, $slot + 1), M26DetailPackage::uuid($target['identity'], $new, $slot + 1));
                unset($source['body'], $new['body']); self::assertSame($source, $new);
            }
            foreach ($target['after']['page']['blocks'] as $block) { self::assertLessThanOrEqual(65535, strlen($block['body'])); }
            $slot = Package::PRACTICE_SOURCE_INDICES[$i]; $practice = $target['after']['page']['blocks'][$slot];
            self::assertSame('practice-set', $practice['type']); self::assertSame('left', $practice['column']);
            self::assertSame([null, 'A2', 'B1'][$i], $practice['level'] ?? null);
            self::assertCount(1, array_filter($lesson['blocks'], static fn ($b) => $b['type'] === 'practice-set'));
            if ($i === 0) {
                self::assertSame('4ec54834-db1f-55f8-983a-e6537527e477', M26DetailPackage::uuid($target['identity'], $practice, 12));
            } else {
                self::assertSame('m73:'.$target['slug'].':'.['', 'irregular-practice', 'collective-practice'][$i], $practice['uuid_key']);
            }
            $data = $lesson['blocks'][$slot]['data'];
            foreach (['selects' => ($i === 0 ? 4 : 2), 'choices' => 2, 'inputs' => ($i === 0 ? 5 : 2)] as $group => $count) {
                self::assertCount($count, $data[$group]);
            }
            self::assertCount($i === 0 ? 3 : 0, $data['rephrase'] ?? []);
            foreach (['selects', 'choices', 'inputs', 'rephrase'] as $group) {
                foreach ($data[$group] ?? [] as $n => $task) {
                    if ($group === 'rephrase' && $n === 0) {
                        self::assertNotEmpty($task['example_original']); self::assertNotEmpty($task['example_target']);
                        self::assertArrayNotHasKey('answer', $task); continue;
                    }
                    ++$own; self::assertNotSame('', trim($task['answer'])); self::assertContains($task['answer'], $task['accepted']);
                    foreach ($task['accepted'] as $answer) {
                        self::assertNotSame('', self::normal($answer));
                        self::assertArrayHasKey(mb_strtolower(trim($answer)), $task['feedback']);
                    }
                    foreach ($task['feedback'] as $feedback) {
                        self::assertDoesNotMatchRegularExpression('/<(?:\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu', $feedback);
                    }
                    $isRetained = $i === 0 && $group !== 'choices' && ($group !== 'inputs' || $n < 3);
                    if ($isRetained) {
                        ++$retained; self::assertArrayNotHasKey('id', $task); self::assertArrayNotHasKey('source_index', $task);
                    } else {
                        self::assertMatchesRegularExpression('/^m73-[a-z0-9-]+$/D', $task['id']); $newIds[] = $task['id'];
                    }
                }
            }
            foreach ($i === 0 ? [3, 4] : [0, 1] as $n) {
                $input = $data['inputs'][$n];
                self::assertStringContainsString('/', $input['before']); self::assertArrayNotHasKey('prompt', $input);
                self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $input['after']);
                self::assertGreaterThanOrEqual(4, count(explode(' ', trim($input['answer']))));
                self::assertDoesNotMatchRegularExpression('/Ø|Без артикля/u', $input['answer']);
            }
        }
        self::assertSame(26, $continuing); self::assertSame(25, $own); self::assertSame(9, $retained);
        self::assertCount(16, $newIds); self::assertCount(16, array_unique($newIds));
        foreach (Package::profile()['artifacts'] as $path => $sha) {
            self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D', $sha);
            self::assertSame($sha, hash_file('sha256', base_path($path)));
        }
    }

    public function test_all_visible_selects_and_choices_have_distinct_nonempty_supported_keys(): void
    {
        [, , $master] = Package::load();
        self::assertSame('', self::normal(' ... ')); self::assertNotSame('', self::normal('Без артикля'));
        foreach ($master['lessons'] as $i => $lesson) {
            $data = $lesson['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['data'];
            foreach (['selects', 'choices'] as $group) { foreach ($data[$group] as $task) {
                self::assertContains($task['answer'], $task['options']);
                $options = array_map(self::normal(...), $task['options']);
                self::assertCount(count($options), array_unique($options)); self::assertNotContains('', $options);
                foreach ($task['options'] as $option) {
                    self::assertArrayHasKey(mb_strtolower(trim($option)), $task['feedback']);
                    if (!in_array($option, $task['accepted'], true)) {
                        self::assertNotContains(self::normal($option), array_map(self::normal(...), $task['accepted']));
                    }
                }
            }}
        }
    }

    public function test_generic_repairs_old_empty_key_and_ordinal_options_without_replacing_nine_functions(): void
    {
        [$before, , $master] = Package::load();
        $old = json_decode($before['targets'][0]['before']['page']['blocks'][11]['body'], true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('', $old['inputs'][2]['answer']);
        self::assertSame(['Перший варіант', 'Другий варіант'], $old['options']);
        $data = $master['lessons'][0]['blocks'][11]['data'];
        foreach ($data['selects'] as $task) {
            self::assertContains($task['answer'], $task['options']);
            self::assertNotContains('Перший варіант', $task['options']); self::assertNotContains('Другий варіант', $task['options']);
        }
        $zero = $data['inputs'][2]; self::assertNotSame('', self::normal($zero['answer']));
        if ($zero['answer'] === 'Без артикля') { self::assertSame(['Без артикля'], $zero['accepted']); }
        else {
            self::assertGreaterThanOrEqual(4, count(explode(' ', $zero['answer'])));
            self::assertDoesNotMatchRegularExpression('/Ø|Без артикля/u', $zero['answer']);
        }
        foreach (array_slice($data['inputs'], 0, 3) as $task) { self::assertArrayNotHasKey('id', $task); }
        self::assertArrayNotHasKey('answer', $data['rephrase'][0]);
    }

    public function test_irregular_table_translates_all_eleven_source_pairs_and_collective_retains_reviewed_alias(): void
    {
        [, , $master] = Package::load();
        $table = $master['lessons'][1]['blocks'][3]['data'];
        self::assertSame(['man → men', 'woman → women', 'child → children', 'person → people', 'foot → feet',
            'tooth → teeth', 'mouse → mice', 'goose → geese', 'sheep → sheep', 'deer → deer', 'ox → oxen'], array_column($table['rows'], 'en'));
        foreach ($table['rows'] as $row) {
            self::assertDoesNotMatchRegularExpression('/[\x{0400}-\x{04ff}]/u', $row['en']);
            self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $row['ua']); self::assertNotSame('', trim($row['note']));
        }
        $task = $master['lessons'][2]['blocks'][6]['data']['inputs'][1];
        self::assertSame('The team are packing their bags.', $task['answer']);
        self::assertSame(['The team are packing their bags.', 'The team is packing its bags.'], $task['accepted']);
        self::assertNotContains(self::normal('The team is packing their bags.'), array_map(self::normal(...), $task['accepted']));
        foreach ($task['accepted'] as $answer) { self::assertArrayHasKey(mb_strtolower($answer), $task['feedback']); }
        self::assertStringContainsString('are', $task['after']); self::assertStringContainsString('its', $task['after']);
    }

    public function test_guard_rejects_foreign_metadata_missing_tasks_zero_keys_and_normalized_collisions(): void
    {
        [$before, $patch, $master, $contract] = Package::load();
        $mutations = [
            'owner' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['identity'] = Package::OWNERS[1]; },
            'locale' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['locale'] = 'en'; },
            'category' => static function (&$b, &$p, &$m, &$c): void { $p['targets'][0]['page_binding']['page_category_id'] = 52; },
            'page-binding' => static function (&$b, &$p, &$m, &$c): void { $p['targets'][0]['page_binding']['id'] = 999; },
            'null-level' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][11]['level'] = 'B2'; },
            'range-practice-level' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][7]['level'] = 'A1–A2'; },
            'old-A1-level' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][1]['level'] = 'A2'; },
            'right-column' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['blocks'][3]['column'] = 'left'; },
            'retained-id' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][11]['data']['selects'][0]['id'] = 'replacement'; },
            'retained-task' => static function (&$b, &$p, &$m, &$c): void { array_pop($m['lessons'][0]['blocks'][11]['data']['selects']); },
            'missing-sentence-input' => static function (&$b, &$p, &$m, &$c): void { array_pop($m['lessons'][1]['blocks'][7]['data']['inputs']); },
            'duplicate-practice' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['blocks'][] = $m['lessons'][2]['blocks'][6]; },
            'empty-zero-key' => static function (&$b, &$p, &$m, &$c): void { $t = &$m['lessons'][0]['blocks'][11]['data']['inputs'][2]; $t['answer'] = ''; $t['accepted'] = ['']; },
            'option-key' => static function (&$b, &$p, &$m, &$c): void { $t = &$m['lessons'][1]['blocks'][7]['data']['selects'][0]; $t['answer'] = 'unlisted'; $t['accepted'] = ['unlisted']; $t['feedback']['unlisted'] = 'Невидимий ключ.'; },
            'case-collision' => static function (&$b, &$p, &$m, &$c): void { $t = &$m['lessons'][2]['blocks'][6]['data']['selects'][0]; $t['options'][] = mb_strtoupper($t['options'][0]); },
            'punctuation-collision' => static function (&$b, &$p, &$m, &$c): void { $t = &$m['lessons'][1]['blocks'][7]['data']['selects'][0]; $t['options'][] = $t['options'][0].'!'; },
            'html-feedback' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['blocks'][6]['data']['inputs'][0]['feedback']['bad'] = '<b>Не plain text</b>'; },
            'missing-bank' => static function (&$b, &$p, &$m, &$c): void { $c['targets'][1]['bank_seeders'] = []; },
            'foreign-view' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][2]['data']['native_view'] = 'foreign'; },
        ];
        foreach ($mutations as $case => $mutate) {
            $b = $before; $p = $patch; $m = $master; $c = $contract; $mutate($b, $p, $m, $c);
            $this->refuses(static function () use ($b, $p, $m, $c): void {
                foreach ($m['lessons'] as $i => $lesson) { $p['targets'][$i]['after'] = Package::definition($lesson, $b['targets'][$i]['before']); }
                Package::validate($b, $p, $m, $c);
            }, $case);
        }
        $profile = Package::profile(); $profile['bindings'][0]['existing_practice']['level'] = 'B2';
        $this->refuses(fn () => (new TheoryNativeContentRelease($profile))->existingPractice($profile['bindings'][0], $before['targets'][0]['before']), 'NULL is not B2');
    }

    public function test_m72_frozen_teaching_and_active_bank_revision_stay_exact_and_dynamic_contracts_keep_prior_owners(): void
    {
        foreach (M72PartitivesNegativesGeography::profile()['artifacts'] as $path => $sha) {
            self::assertSame($sha, hash_file('sha256', base_path($path)));
        }
        foreach ([M72BuilderSelectionPresentation::AUDIT => M72BuilderSelectionPresentation::AUDIT_SHA,
            M72BuilderSelectionPresentation::REVISION => M72BuilderSelectionPresentation::REVISION_SHA] as $path => $sha) {
            self::assertSame($sha, hash_file('sha256', base_path($path)));
        }
        $oldAudit = json_decode(file_get_contents(base_path(M72BuilderSelectionPresentation::AUDIT)), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame([32775, 32778, 32779, 32780, 32788], $oldAudit['targets'][1]['expected_selected_ids']);
        self::assertNotContains(32781, $oldAudit['targets'][1]['expected_selected_ids']);
        $contracts = Acceptance::contracts(base_path()); $targets = array_column(Acceptance::discoverTargets(base_path()), null, 'identity');
        self::assertSame([], array_values(array_diff(array_keys($contracts), array_keys($targets))));
        self::assertGreaterThanOrEqual(139, count(array_diff_key($contracts, array_flip(Package::OWNERS))));
        foreach ([M71ArticlesQuantifiers::load()[3], M72PartitivesNegativesGeography::load()[3]] as $prior) {
            foreach ($prior['targets'] as $entry) { self::assertSame($entry, $contracts[$entry['identity']]); }
        }
        foreach (Package::load()[3]['targets'] as $entry) {
            self::assertSame($entry, $contracts[$entry['identity']]); self::assertSame([], $targets[$entry['identity']]['errors']);
        }
    }
}
