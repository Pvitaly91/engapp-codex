<?php

namespace Tests\Feature;

use App\Services\M71ContentPatch;
use App\Support\M26DetailPackage;
use App\Support\M70IntensifiersNounBasics;
use App\Support\M71ArticlesQuantifiers as Package;
use App\Support\TheoryNativeContentRelease;
use App\Support\TheorySentenceBuilderAcceptance as Acceptance;
use Normalizer;
use RuntimeException;
use Tests\TestCase;

/** Frozen authored data and pure guards; no application SQL, network or working .env. */
class M71AuthoredReleaseTest extends TestCase
{
    private function refuses(callable $call, string $case): void
    {
        $caught = null;
        try { $call(); } catch (RuntimeException $error) { $caught = $error; }
        self::assertInstanceOf(RuntimeException::class, $caught, $case);
    }

    /** Comparison alphabet of the unchanged native factory, not a replacement grader. */
    private static function normal(string $value): string
    {
        $value = Normalizer::normalize($value, Normalizer::FORM_KD);
        self::assertIsString($value);
        $value = mb_strtolower($value);
        $value = preg_replace('/[\x{0300}-\x{036f}]/u', '', $value);
        $value = preg_replace('/[\x{2018}\x{2019}\x{201b}\x{2032}`´ʼ\x{0027}]/u', '', $value);
        $value = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value);
        return trim(preg_replace('/\s+/u', ' ', $value));
    }

    public function test_frozen_release_preserves_all_thirty_nine_slots_and_three_null_level_practice_identities(): void
    {
        [$before, $patch, $master, $contract] = Package::load();
        Package::validate($before, $patch, $master, $contract);
        self::assertSame('1291b576b47dc28e5ba9e0c58faa052f6e49460e', $master['base_sha']);
        self::assertSame([82, 91, 90], Package::PAGE_IDS);
        self::assertSame([16, 16, 16], Package::CATEGORY_IDS);
        self::assertSame([12, 14, 13], Package::CONTINUING_COUNTS);
        self::assertSame([10, 12, 11], Package::PRACTICE_SOURCE_INDICES);
        self::assertSame([null, null, null], array_column($contract['targets'], 'level'));
        $slots = 0; $ownTasks = 0; $retainedTasks = 0; $newTaskIds = [];
        foreach ($patch['targets'] as $i => $target) {
            $old = $before['targets'][$i]['before']; $lesson = $master['lessons'][$i];
            self::assertSame([], Package::profile()['bindings'][$i]['appended_blocks']);
            self::assertSame(Package::definition($lesson, $old), $target['after']);
            self::assertSame($target['after'], json_decode(file_get_contents(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR));
            M71ContentPatch::assertDefinitionScope($old, $target['after'], $target, $i);
            self::assertCount(Package::CONTINUING_COUNTS[$i], $lesson['blocks']);
            self::assertSame([], $lesson['details'], 'No artificial short disclosures.');
            self::assertNotEmpty($lesson['point_detail_decisions']);
            foreach ($lesson['point_detail_decisions'] as $decision) {
                self::assertSame('visible-basic', $decision['placement']);
                self::assertNotSame('', trim($decision['reason']));
            }
            $protected = $target['after']; $protected['page']['blocks'] = $old['page']['blocks'];
            foreach (['subtitle_html', 'subtitle_text'] as $field) { $protected['page'][$field] = $old['page'][$field]; }
            self::assertSame($old, $protected, 'Raw title, owner, category, tags and source metadata remain exact.');
            foreach ($old['page']['blocks'] as $slot => $source) {
                ++$slots; $new = $target['after']['page']['blocks'][$slot];
                self::assertSame($slot, $lesson['blocks'][$slot]['existing_source_index']);
                self::assertNull($lesson['blocks'][$slot]['level']);
                self::assertNull($source['level'] ?? null); self::assertNull($new['level'] ?? null);
                self::assertSame(array_key_exists('level', $source), array_key_exists('level', $new));
                self::assertSame(M26DetailPackage::uuid($target['identity'], $source, $slot + 1), M26DetailPackage::uuid($target['identity'], $new, $slot + 1));
                self::assertLessThanOrEqual(65535, strlen($new['body']), 'UTF-8 serialized body stays inside TEXT capacity.');
                unset($source['body'], $new['body']); self::assertSame($source, $new);
            }
            $practices = array_values(array_filter($lesson['blocks'], static fn ($block) => $block['type'] === 'practice-set'));
            self::assertCount(1, $practices);
            $data = $practices[0]['data'];
            $practiceSlot = Package::PRACTICE_SOURCE_INDICES[$i];
            self::assertSame(Package::PRACTICE_UUIDS[$i], M26DetailPackage::uuid($target['identity'], $target['after']['page']['blocks'][$practiceSlot], $practiceSlot + 1));
            foreach (['selects' => 4, 'choices' => 2, 'inputs' => 5, 'rephrase' => 3] as $group => $count) {
                self::assertCount($count, $data[$group]);
                foreach ($data[$group] as $index => $task) {
                    if ($group === 'rephrase' && $index === 0) {
                        self::assertNotEmpty($task['example_original']); self::assertNotEmpty($task['example_target']);
                        self::assertArrayNotHasKey('answer', $task, 'The old demonstration is not a scored question.');
                        continue;
                    }
                    ++$ownTasks;
                    self::assertNotSame('', trim($task['answer'])); self::assertContains($task['answer'], $task['accepted']);
                    foreach ($task['accepted'] as $accepted) {
                        self::assertNotSame('', self::normal($accepted));
                        self::assertArrayHasKey(mb_strtolower(trim($accepted)), $task['feedback']);
                    }
                    foreach ($task['feedback'] as $text) {
                        self::assertIsString($text);
                        self::assertDoesNotMatchRegularExpression('/<(?:\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu', $text);
                    }
                    if ($group !== 'choices' && ($group !== 'inputs' || $index < 3)) {
                        ++$retainedTasks; self::assertArrayNotHasKey('id', $task); self::assertArrayNotHasKey('source_index', $task);
                    } else {
                        self::assertMatchesRegularExpression('/^m71-[a-z0-9-]+$/D', $task['id']); $newTaskIds[] = $task['id'];
                    }
                }
            }
            foreach ([3, 4] as $index) {
                $input = $data['inputs'][$index];
                self::assertStringContainsString('/', $input['before']);
                self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $input['after']);
                self::assertGreaterThanOrEqual(5, count(explode(' ', trim($input['answer']))));
                self::assertDoesNotMatchRegularExpression('/Ø|Без артикля/u', $input['answer']);
            }
            $oldNav = json_decode($old['page']['blocks'][count($old['page']['blocks']) - 1]['body'], true, flags: JSON_THROW_ON_ERROR)['items'];
            $newNav = $lesson['blocks'][count($lesson['blocks']) - 1]['data']['items'];
            self::assertCount(9, $newNav);
            self::assertSame($oldNav, array_map(static fn ($item) => array_intersect_key($item, array_flip(['label', 'current'])), $newNav));
            foreach ($newNav as $item) { self::assertMatchesRegularExpression('~^/theory/imennyky-artykli-ta-kilkist/[a-z0-9-]+$~D', $item['url']); }
        }
        self::assertSame(39, $slots); self::assertSame(39, $ownTasks); self::assertSame(27, $retainedTasks);
        self::assertCount(12, $newTaskIds); self::assertCount(12, array_unique($newTaskIds));
        foreach (Package::profile()['artifacts'] as $path => $sha) {
            self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D', $sha);
            self::assertSame($sha, hash_file('sha256', base_path($path)));
        }
        $checksums = json_decode(file_get_contents(base_path(Package::CHECKSUMS)), true, flags: JSON_THROW_ON_ERROR);
        foreach ($checksums['artifacts'] as $artifact) { self::assertSame($artifact['sha256'], hash_file('sha256', base_path($artifact['path']))); }
    }

    public function test_actual_comparison_alphabet_keeps_all_options_distinct_and_distractors_outside_accepted_answers(): void
    {
        [, , $master] = Package::load();
        self::assertSame('a lot', self::normal('A-lot'));
        self::assertSame('cafe', self::normal('Café'));
        self::assertSame('cant', self::normal("can't"));
        self::assertSame('', self::normal('   ...   '));
        self::assertNotSame('', self::normal('Без артикля'));
        foreach ($master['lessons'] as $i => $lesson) {
            $data = $lesson['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['data'];
            foreach (['selects', 'choices'] as $group) {
                foreach ($data[$group] as $task) {
                    self::assertContains($task['answer'], $task['options']);
                    $options = array_map(self::normal(...), $task['options']);
                    self::assertCount(count($task['options']), array_unique($options));
                    self::assertNotContains('', $options);
                    $accepted = array_map(self::normal(...), $task['accepted']);
                    foreach ($task['options'] as $option) {
                        self::assertArrayHasKey(mb_strtolower(trim($option)), $task['feedback']);
                        if (!in_array($option, $task['accepted'], true)) { self::assertNotContains(self::normal($option), $accepted); }
                    }
                }
            }
        }
    }

    public function test_zero_article_transfers_the_two_gap_format_without_accepting_an_empty_or_symbol_answer(): void
    {
        [$before, , $master] = Package::load();
        $old = json_decode($before['targets'][1]['before']['page']['blocks'][12]['body'], true, flags: JSON_THROW_ON_ERROR);
        $practice = $master['lessons'][1]['blocks'][12]['data'];
        self::assertSame('', $old['inputs'][0]['answer']); self::assertSame('', $old['inputs'][1]['answer']);
        self::assertSame('I go to work by bus.', $practice['inputs'][0]['answer']);
        self::assertArrayNotHasKey('id', $practice['inputs'][0], 'The transferred retained position keeps its identity absence.');
        self::assertSame('Без артикля', $practice['inputs'][1]['answer']);
        self::assertSame(['Без артикля'], $practice['inputs'][1]['accepted']);
        foreach (['selects', 'choices', 'inputs', 'rephrase'] as $group) {
            foreach ($practice[$group] as $index => $task) {
                if ($group === 'rephrase' && $index === 0) { continue; }
                self::assertNotSame('', self::normal($task['answer'])); self::assertNotContains('', $task['accepted']);
                foreach ($task['accepted'] as $accepted) { self::assertNotSame('Ø', $accepted); }
            }
        }
        foreach ([0, 3, 4] as $index) {
            $input = $practice['inputs'][$index];
            self::assertStringContainsString('/', $input['before']);
            self::assertDoesNotMatchRegularExpression('/Ø|Без артикля/u', $input['answer']);
            foreach (array_map('trim', explode('/', $input['before'])) as $token) { self::assertNotSame('', $token); self::assertNotSame('Ø', $token); }
        }
    }

    public function test_context_translated_corrections_and_nonautomatic_quantity_meanings_are_visible_authored_data(): void
    {
        [, , $master] = Package::load();
        foreach ($master['lessons'] as $lesson) {
            foreach ($lesson['blocks'] as $block) {
                foreach ($block['data']['sections'] ?? [] as $point) {
                    self::assertNotSame('', trim($point['label'])); self::assertNotSame('', trim($point['description']));
                    foreach ($point['examples'] ?? [] as $example) {
                        self::assertNotSame('', trim($example['en'])); self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $example['ua']);
                        self::assertDoesNotMatchRegularExpression('/[\x{0400}-\x{04ff}]/u', $example['en']);
                    }
                }
                if ($block['type'] !== 'mistakes-grid') { continue; }
                foreach ($block['data']['items'] as $item) {
                    foreach (['wrong_en', 'right_en'] as $field) { self::assertNotSame('', trim($item[$field])); self::assertDoesNotMatchRegularExpression('/[\x{0400}-\x{04ff}]/u', $item[$field]); }
                    foreach (['wrong_ua', 'right_ua', 'note'] as $field) { self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $item[$field]); }
                }
            }
        }
        $articles = $master['lessons'][0]['blocks'][10]['data'];
        self::assertSame(['a university', 'an honest answer', 'a European country'], array_column(array_slice($articles['inputs'], 0, 3), 'answer'));
        foreach (array_slice($articles['inputs'], 0, 3) as $task) { self::assertMatchesRegularExpression('/всю|усю/u', $task['prompt']); }
        self::assertStringContainsString('загальне вподобання', $articles['rephrase_intro']);
        self::assertSame('I love the music.', $articles['rephrase'][1]['original']);
        $few = $master['lessons'][2]['blocks'][5]['data']; $little = $master['lessons'][2]['blocks'][6]['data'];
        self::assertStringContainsString('не обіцяє', strip_tags($few['sections'][1]['description']));
        self::assertStringContainsString('Достатність', strip_tags($little['sections'][1]['description']));
        self::assertContains('The report contains few mistakes.', array_column($few['sections'][2]['examples'], 'en'));
        self::assertContains('We have a few problems to solve.', array_column($few['sections'][2]['examples'], 'en'));
        self::assertContains('We have a little time, but not enough to finish the report.', array_column($little['sections'][2]['examples'], 'en'));
    }

    public function test_identity_level_uuid_practice_and_choice_mutations_fail_even_when_reprojected(): void
    {
        [$before, $patch, $master, $contract] = Package::load();
        $mutations = [
            'owner' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['identity'] = Package::OWNERS[1]; },
            'locale' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['locale'] = 'en'; },
            'category' => static function (&$b, &$p, &$m, &$c): void { $p['targets'][1]['page_binding']['page_category_id'] = 52; },
            'page-binding' => static function (&$b, &$p, &$m, &$c): void { $p['targets'][1]['page_binding']['id'] = Package::PAGE_IDS[0]; },
            'before-category' => static function (&$b, &$p, &$m, &$c): void { $b['targets'][1]['before']['page']['category']['slug'] = 'foreign'; },
            'null-level' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][12]['level'] = 'A2'; },
            'practice-uuid' => static function (&$b, &$p, &$m, &$c): void { $b['targets'][1]['before']['page']['blocks'][12]['uuid_key'] = 'foreign'; },
            'stale' => static function (&$b, &$p, &$m, &$c): void { $p['targets'][0]['after']['page']['blocks'][1]['body'] .= "\n"; },
            'retained-id' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][10]['data']['selects'][0]['id'] = 'new-identity'; },
            'retained-task' => static function (&$b, &$p, &$m, &$c): void { array_pop($m['lessons'][0]['blocks'][10]['data']['selects']); },
            'retained-rephrase' => static function (&$b, &$p, &$m, &$c): void { array_pop($m['lessons'][2]['blocks'][11]['data']['rephrase']); },
            'extra-practice' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][] = $m['lessons'][1]['blocks'][12]; },
            'empty-answer' => static function (&$b, &$p, &$m, &$c): void { $task = &$m['lessons'][1]['blocks'][12]['data']['inputs'][1]; $task['answer'] = ''; $task['accepted'] = ['']; },
            'missing-select-option' => static function (&$b, &$p, &$m, &$c): void { $task = &$m['lessons'][0]['blocks'][10]['data']['selects'][0]; $task['answer'] = 'missing'; $task['accepted'] = ['missing']; $task['feedback']['missing'] = 'Невідповідний ключ.'; },
            'missing-choice-option' => static function (&$b, &$p, &$m, &$c): void { $task = &$m['lessons'][0]['blocks'][10]['data']['choices'][0]; $task['answer'] = 'Невидима відповідь'; $task['accepted'] = ['Невидима відповідь']; $task['feedback']['невидима відповідь'] = 'Невідповідний ключ.'; },
            'case-collision' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][10]['data']['selects'][0]['options'][] = 'A'; },
            'punctuation-collision' => static function (&$b, &$p, &$m, &$c): void { $task = &$m['lessons'][0]['blocks'][10]['data']['selects'][0]; $task['options'][] = 'a!'; $task['feedback']['a!'] = 'Колізія.'; },
            'nfkd-collision' => static function (&$b, &$p, &$m, &$c): void { $task = &$m['lessons'][0]['blocks'][10]['data']['choices'][0]; $task['options'][] = 'Café'; $task['options'][] = 'Cafe'; $task['feedback']['café'] = 'Колізія.'; $task['feedback']['cafe'] = 'Колізія.'; },
            'word-boundary-collision' => static function (&$b, &$p, &$m, &$c): void { $task = &$m['lessons'][0]['blocks'][10]['data']['choices'][0]; $task['options'][] = 'Тест-фраза'; $task['options'][] = 'Тест фраза'; $task['feedback']['тест-фраза'] = 'Колізія.'; $task['feedback']['тест фраза'] = 'Колізія.'; },
            'empty-option' => static function (&$b, &$p, &$m, &$c): void { $task = &$m['lessons'][0]['blocks'][10]['data']['selects'][0]; $task['options'][] = '...'; $task['feedback']['...'] = 'Порожня нормалізована відповідь.'; },
            'html-feedback' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][10]['data']['selects'][0]['feedback']['an'] = '<span>Не plain text</span>'; },
            'bank' => static function (&$b, &$p, &$m, &$c): void { $c['targets'][1]['bank_seeders'] = []; },
            'view' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][1]['data']['native_view'] = 'foreign'; },
        ];
        foreach ($mutations as $case => $mutate) {
            $b = $before; $p = $patch; $m = $master; $c = $contract; $mutate($b, $p, $m, $c);
            $this->refuses(function () use ($b, $p, $m, $c, $case): void {
                if (!in_array($case, ['stale', 'practice-uuid'], true)) {
                    foreach ($m['lessons'] as $i => $lesson) { $p['targets'][$i]['after'] = Package::definition($lesson, $b['targets'][$i]['before']); }
                }
                Package::validate($b, $p, $m, $c);
            }, $case);
        }
        $profile = Package::profile(); $profile['bindings'][1]['existing_practice']['level'] = 'A2';
        $this->refuses(fn () => (new TheoryNativeContentRelease($profile))->existingPractice($profile['bindings'][1], $before['targets'][1]['before']), 'NULL practice is not A2');
    }

    public function test_dynamic_registry_keeps_prior_contracts_and_adds_each_m71_owner_without_a_permanent_count_limit(): void
    {
        [, $patch, , $contract] = Package::load();
        $contracts = Acceptance::contracts(base_path()); $targets = array_column(Acceptance::discoverTargets(base_path()), null, 'identity');
        self::assertSame([], array_values(array_diff(array_keys($contracts), array_keys($targets))));
        $previous = array_diff_key($contracts, array_flip(Package::OWNERS));
        self::assertGreaterThanOrEqual(133, count($previous), 'Accepted earlier owners cannot disappear; later packages may add more.');
        foreach (M70IntensifiersNounBasics::load()[3]['targets'] as $oldContract) { self::assertSame($oldContract, $contracts[$oldContract['identity']]); }
        foreach ($contract['targets'] as $i => $declaration) {
            self::assertSame($declaration, $contracts[Package::OWNERS[$i]]);
            self::assertSame([], $targets[Package::OWNERS[$i]]['errors']);
            self::assertSame($patch['targets'][$i]['local_url'], $declaration['local_url']);
            self::assertNull($declaration['level']);
        }
    }
}
