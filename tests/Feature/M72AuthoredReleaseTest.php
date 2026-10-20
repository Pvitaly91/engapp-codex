<?php

namespace Tests\Feature;

use App\Services\M72ContentPatch;
use App\Support\M26DetailPackage;
use App\Support\M70IntensifiersNounBasics;
use App\Support\M71ArticlesQuantifiers;
use App\Support\M72PartitivesNegativesGeography as Package;
use App\Support\TheoryNativeContentRelease;
use App\Support\TheorySentenceBuilderAcceptance as Acceptance;
use Normalizer;
use RuntimeException;
use Tests\TestCase;

/** Frozen authored data and pure guards; no application SQL, network or working .env. */
class M72AuthoredReleaseTest extends TestCase
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

    public function test_frozen_release_preserves_all_forty_two_slots_and_three_null_level_practice_identities(): void
    {
        [$before, $patch, $master, $contract] = Package::load();
        Package::validate($before, $patch, $master, $contract);
        self::assertSame('2bcd468b25af3c44dcf34ccee0e012f3f683335d', $master['base_sha']);
        self::assertSame([88, 87, 83], Package::PAGE_IDS);
        self::assertSame([16, 16, 16], Package::CATEGORY_IDS);
        self::assertSame([14, 13, 15], Package::CONTINUING_COUNTS);
        self::assertSame([12, 11, 13], Package::PRACTICE_SOURCE_INDICES);
        self::assertSame([null, null, null], array_column($contract['targets'], 'level'));
        $slots = 0; $ownTasks = 0; $retainedTasks = 0; $newTaskIds = [];
        foreach ($patch['targets'] as $i => $target) {
            $old = $before['targets'][$i]['before']; $lesson = $master['lessons'][$i];
            self::assertSame([], Package::profile()['bindings'][$i]['appended_blocks']);
            self::assertSame(Package::definition($lesson, $old), $target['after']);
            self::assertSame($target['after'], json_decode(file_get_contents(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR));
            M72ContentPatch::assertDefinitionScope($old, $target['after'], $target, $i);
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
                        self::assertMatchesRegularExpression('/^m72-[a-z0-9-]+$/D', $task['id']); $newTaskIds[] = $task['id'];
                    }
                }
            }
            foreach ([3, 4] as $index) {
                $input = $data['inputs'][$index];
                self::assertStringContainsString('/', $input['before']);
                self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $input['after']);
                self::assertGreaterThanOrEqual(3, count(explode(' ', trim($input['answer']))));
                self::assertDoesNotMatchRegularExpression('/Ø|Без артикля/u', $input['answer']);
            }
            $oldNav = json_decode($old['page']['blocks'][count($old['page']['blocks']) - 1]['body'], true, flags: JSON_THROW_ON_ERROR)['items'];
            $newNav = $lesson['blocks'][count($lesson['blocks']) - 1]['data']['items'];
            self::assertCount(9, $newNav);
            self::assertSame($oldNav, array_map(static fn ($item) => array_intersect_key($item, array_flip(['label', 'current'])), $newNav));
            foreach ($newNav as $item) { self::assertMatchesRegularExpression('~^/theory/imennyky-artykli-ta-kilkist/[a-z0-9-]+$~D', $item['url']); }
        }
        self::assertSame(42, $slots); self::assertSame(39, $ownTasks); self::assertSame(27, $retainedTasks);
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

    public function test_geography_repairs_two_empty_keys_with_complete_sentences_and_explicit_zero_article_choices(): void
    {
        [$before, , $master] = Package::load();
        $slot = Package::PRACTICE_SOURCE_INDICES[2];
        $old = json_decode($before['targets'][2]['before']['page']['blocks'][$slot]['body'], true, flags: JSON_THROW_ON_ERROR);
        $practice = $master['lessons'][2]['blocks'][$slot]['data'];
        self::assertSame('', $old['inputs'][0]['answer']);
        self::assertSame('', $old['inputs'][2]['answer']);
        self::assertSame(['I live in Ukraine.', 'The Dnipro is a beautiful river.', 'I climbed Mount Everest.'],
            array_column(array_slice($practice['inputs'], 0, 3), 'answer'));
        self::assertSame('Без артикля', $practice['selects'][1]['answer']);
        self::assertSame(['Без артикля'], $practice['selects'][1]['accepted']);
        foreach ($practice['inputs'] as $index => $input) {
            self::assertStringContainsString('/', $input['before']);
            self::assertArrayNotHasKey('prompt', $input, 'Native input conditions use the supported after field.');
            self::assertMatchesRegularExpression('/[\\x{0400}-\\x{04ff}]/u', $input['after']);
            self::assertDoesNotMatchRegularExpression('/Ø|Без артикля/u', $input['answer']);
            self::assertGreaterThanOrEqual(4, count(explode(' ', trim($input['answer']))));
            if ($index < 3) { self::assertArrayNotHasKey('id', $input); }
            foreach ($input['accepted'] as $accepted) {
                self::assertNotSame('', self::normal($accepted));
                self::assertNotContains(self::normal($accepted), ['', 'the', 'ø', self::normal('Без артикля')]);
            }
            foreach (array_map('trim', explode('/', $input['before'])) as $token) {
                self::assertNotSame('', $token);
                self::assertNotSame('Ø', $token);
                self::assertNotSame('Без артикля', $token);
            }
        }
        self::assertSame(['Thames is a long river.', 'I visited the Mount Everest.'],
            array_column(array_slice($practice['rephrase'], 1), 'original'));
        self::assertStringContainsString('Темза', $practice['rephrase_intro']);
        foreach (array_slice($practice['rephrase'], 1) as $task) {
            self::assertDoesNotMatchRegularExpression('/[\\x{0400}-\\x{04ff}]/u', $task['original']);
        }
    }

    public function test_bilingual_examples_and_corrections_keep_comments_outside_english_fields(): void
    {
        [, , $master] = Package::load();
        foreach ($master['lessons'] as $lesson) {
            foreach ($lesson['blocks'] as $block) {
                foreach ($block['data']['sections'] ?? [] as $point) {
                    self::assertNotSame('', trim($point['label']));
                    self::assertNotSame('', trim($point['description']));
                    foreach ($point['examples'] ?? [] as $example) {
                        self::assertNotSame('', trim($example['en']));
                        self::assertMatchesRegularExpression('/[\\x{0400}-\\x{04ff}]/u', $example['ua']);
                        self::assertDoesNotMatchRegularExpression('/[\\x{0400}-\\x{04ff}]/u', $example['en']);
                    }
                }
                if ($block['type'] !== 'mistakes-grid') { continue; }
                foreach ($block['data']['items'] as $item) {
                    foreach (['wrong_en', 'right_en'] as $field) {
                        self::assertNotSame('', trim($item[$field]));
                        self::assertDoesNotMatchRegularExpression('/[\\x{0400}-\\x{04ff}]/u', $item[$field]);
                    }
                    foreach (['wrong_ua', 'right_ua', 'note'] as $field) {
                        self::assertMatchesRegularExpression('/[\\x{0400}-\\x{04ff}]/u', $item[$field]);
                    }
                }
            }
        }
    }

    public function test_authored_meanings_reject_mechanical_container_negation_and_geographic_algorithms(): void
    {
        [, , $master] = Package::load();
        $partitives = $master['lessons'][0]['blocks'];
        self::assertStringContainsString('не тільки', $partitives[6]['data']['sections'][2]['description']);
        self::assertStringContainsString('a carton of eggs', json_encode($partitives[6]['data'], JSON_UNESCAPED_UNICODE));
        self::assertStringContainsString('a bit of information', $partitives[2]['data']['sections'][1]['note']);
        self::assertStringContainsString('не гарантує', $partitives[2]['data']['sections'][2]['note']);
        self::assertStringContainsString('універсального об’єму', $partitives[3]['data']['sections'][1]['note']);
        $partitivePractice = $partitives[12]['data'];
        self::assertSame(['a piece of', 'a cup of', 'a jar of'], array_column(array_slice($partitivePractice['inputs'], 0, 3), 'answer'));
        self::assertSame(['There are two bottles of water here.', 'We need three cups of tea.'],
            array_column(array_slice($partitivePractice['inputs'], 3), 'answer'));

        $negatives = $master['lessons'][1]['blocks'];
        self::assertContains('None of the students is here. / None of the students are here.',
            array_column($negatives[7]['data']['sections'][1]['examples'], 'en'));
        self::assertContains('Neither of them is here. / Neither of them are here.',
            array_column($negatives[7]['data']['sections'][2]['examples'], 'en'));
        $negativePractice = $negatives[11]['data'];
        self::assertSame(['None', 'no', 'Neither'], array_column(array_slice($negativePractice['inputs'], 0, 3), 'answer'));
        self::assertSame(['Neither key works.', 'You can use either entrance.'],
            array_column(array_slice($negativePractice['inputs'], 3), 'answer'));
        self::assertStringContainsString('чи хтось уже взяв книжку, не сказано', $negativePractice['choices'][0]['answer']);
        self::assertStringContainsString('четверо склали', $negativePractice['choices'][1]['answer']);
        self::assertSame('Neither of them came.', $negativePractice['rephrase'][1]['answer']);

        $geography = $master['lessons'][2]['blocks'];
        $hague = $geography[3]['data']['sections'][1];
        self::assertStringContainsString('столиця країни — Амстердам', $hague['description']);
        self::assertStringContainsString('уряд і парламент', $hague['description']);
        self::assertContains('Гаага', array_column($hague['examples'], 'ua'));
        self::assertStringContainsString('не стверджуй, що це єдине місто', $hague['description']);
        self::assertStringContainsString('не вимагає the', $geography[7]['data']['sections'][1]['description']);
        self::assertContains('Lake Superior / Superior', array_column($geography[7]['data']['sections'][1]['examples'], 'en'));
        self::assertStringContainsString('не штучний canal', $geography[9]['data']['sections'][1]['description']);
        self::assertContains('Hudson Bay', array_column($geography[9]['data']['sections'][2]['examples'], 'en'));
        self::assertContains('the Matterhorn', array_column($geography[6]['data']['sections'][0]['examples'], 'en'));
        self::assertStringContainsString('чинна усталена назва', $geography[2]['data']['sections'][2]['note']);
        self::assertContains('Did she visit the Netherlands?', array_column($geography[2]['data']['sections'][1]['examples'], 'en'));
        self::assertContains("We didn't visit Lake Geneva.", array_column($geography[7]['data']['sections'][0]['examples'], 'en'));
        self::assertSame(['She lives in the Netherlands.', 'We visited Lake Geneva.'],
            array_column(array_slice($geography[13]['data']['inputs'], 3), 'answer'));
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
            'null-level' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][11]['level'] = 'A2'; },
            'practice-uuid' => static function (&$b, &$p, &$m, &$c): void { $b['targets'][1]['before']['page']['blocks'][11]['uuid_key'] = 'foreign'; },
            'stale' => static function (&$b, &$p, &$m, &$c): void { $p['targets'][0]['after']['page']['blocks'][1]['body'] .= "\n"; },
            'retained-id' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][12]['data']['selects'][0]['id'] = 'new-identity'; },
            'retained-task' => static function (&$b, &$p, &$m, &$c): void { array_pop($m['lessons'][0]['blocks'][12]['data']['selects']); },
            'retained-rephrase' => static function (&$b, &$p, &$m, &$c): void { array_pop($m['lessons'][2]['blocks'][13]['data']['rephrase']); },
            'extra-practice' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][] = $m['lessons'][1]['blocks'][11]; },
            'empty-answer' => static function (&$b, &$p, &$m, &$c): void { $task = &$m['lessons'][1]['blocks'][11]['data']['inputs'][1]; $task['answer'] = ''; $task['accepted'] = ['']; },
            'geography-empty-answer' => static function (&$b, &$p, &$m, &$c): void { $task = &$m['lessons'][2]['blocks'][13]['data']['inputs'][0]; $task['answer'] = ''; $task['accepted'] = ['']; },
            'geography-symbol-answer' => static function (&$b, &$p, &$m, &$c): void { $task = &$m['lessons'][2]['blocks'][13]['data']['inputs'][0]; $task['answer'] = 'Ø'; $task['accepted'] = ['Ø']; $task['feedback']['ø'] = 'Позначка не є реченням.'; },
            'geography-bare-article-answer' => static function (&$b, &$p, &$m, &$c): void { $task = &$m['lessons'][2]['blocks'][13]['data']['inputs'][1]; $task['answer'] = 'the'; $task['accepted'] = ['the']; $task['feedback']['the'] = 'Потрібне повне речення.'; },
            'geography-zero-label-answer' => static function (&$b, &$p, &$m, &$c): void { $task = &$m['lessons'][2]['blocks'][13]['data']['inputs'][0]; $task['answer'] = 'Без артикля'; $task['accepted'] = ['Без артикля']; $task['feedback']['без артикля'] = 'Позначка не є реченням.'; },
            'missing-select-option' => static function (&$b, &$p, &$m, &$c): void { $task = &$m['lessons'][0]['blocks'][12]['data']['selects'][0]; $task['answer'] = 'missing'; $task['accepted'] = ['missing']; $task['feedback']['missing'] = 'Невідповідний ключ.'; },
            'missing-choice-option' => static function (&$b, &$p, &$m, &$c): void { $task = &$m['lessons'][0]['blocks'][12]['data']['choices'][0]; $task['answer'] = 'Невидима відповідь'; $task['accepted'] = ['Невидима відповідь']; $task['feedback']['невидима відповідь'] = 'Невідповідний ключ.'; },
            'case-collision' => static function (&$b, &$p, &$m, &$c): void { $task = &$m['lessons'][0]['blocks'][12]['data']['selects'][0]; $task['options'][] = mb_strtoupper($task['options'][0]); },
            'punctuation-collision' => static function (&$b, &$p, &$m, &$c): void { $task = &$m['lessons'][0]['blocks'][12]['data']['selects'][0]; $option = $task['options'][0].'!'; $task['options'][] = $option; $task['feedback'][mb_strtolower($option)] = 'Колізія.'; },
            'nfkd-collision' => static function (&$b, &$p, &$m, &$c): void { $task = &$m['lessons'][0]['blocks'][12]['data']['choices'][0]; $task['options'][] = 'Café'; $task['options'][] = 'Cafe'; $task['feedback']['café'] = 'Колізія.'; $task['feedback']['cafe'] = 'Колізія.'; },
            'word-boundary-collision' => static function (&$b, &$p, &$m, &$c): void { $task = &$m['lessons'][0]['blocks'][12]['data']['choices'][0]; $task['options'][] = 'Тест-фраза'; $task['options'][] = 'Тест фраза'; $task['feedback']['тест-фраза'] = 'Колізія.'; $task['feedback']['тест фраза'] = 'Колізія.'; },
            'empty-option' => static function (&$b, &$p, &$m, &$c): void { $task = &$m['lessons'][0]['blocks'][12]['data']['selects'][0]; $task['options'][] = '...'; $task['feedback']['...'] = 'Порожня нормалізована відповідь.'; },
            'html-feedback' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][12]['data']['selects'][0]['feedback']['an'] = '<span>Не plain text</span>'; },
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

    public function test_dynamic_registry_keeps_prior_contracts_and_adds_each_m72_owner_without_a_permanent_count_limit(): void
    {
        [, $patch, , $contract] = Package::load();
        $contracts = Acceptance::contracts(base_path()); $targets = array_column(Acceptance::discoverTargets(base_path()), null, 'identity');
        self::assertSame([], array_values(array_diff(array_keys($contracts), array_keys($targets))));
        $previous = array_diff_key($contracts, array_flip(Package::OWNERS));
        self::assertGreaterThanOrEqual(136, count($previous), 'Accepted earlier owners cannot disappear; later packages may add more.');
        foreach ([M70IntensifiersNounBasics::load()[3], M71ArticlesQuantifiers::load()[3]] as $prior) {
            foreach ($prior['targets'] as $oldContract) { self::assertSame($oldContract, $contracts[$oldContract['identity']]); }
        }
        foreach ($contract['targets'] as $i => $declaration) {
            self::assertSame($declaration, $contracts[Package::OWNERS[$i]]);
            self::assertSame([], $targets[Package::OWNERS[$i]]['errors']);
            self::assertSame($patch['targets'][$i]['local_url'], $declaration['local_url']);
            self::assertNull($declaration['level']);
        }
    }
}
