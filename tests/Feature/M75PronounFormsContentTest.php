<?php

namespace Tests\Feature;

use App\Support\M26DetailPackage;
use App\Support\M74OtherPronounsBasics;
use App\Support\M75PronounForms as Package;
use App\Support\TheoryNativeContentRelease;
use RuntimeException;
use Tests\TestCase;

/** Frozen teaching-data checks. No working database, HTTP, browser or engine replacement. */
class M75PronounFormsContentTest extends TestCase
{
    private function sourceRoot(): string
    {
        return dirname(__DIR__, 2);
    }

    private function release(): array
    {
        $release = Package::load($this->sourceRoot());
        Package::validate($release[0], $release[1], $release[2], $release[3]);
        return $release;
    }

    private static function practice(array $master, int $owner): array
    {
        return $master['lessons'][$owner]['blocks'][Package::PRACTICE_SOURCE_INDICES[$owner]]['data'];
    }

    private function refuses(callable $call, string $case): void
    {
        $caught = null;
        try { $call(); } catch (RuntimeException $error) { $caught = $error; }
        self::assertInstanceOf(RuntimeException::class, $caught, $case);
    }

    private static function words(string $value): array
    {
        return preg_split('/\s+/u', trim($value), flags: PREG_SPLIT_NO_EMPTY);
    }

    public function test_exact_frozen_scope_preserves_all_continuing_metadata_and_three_practice_identities(): void
    {
        [$before, $patch, $master, $contract] = $this->release();
        self::assertSame('67ebba5ed98fae05ca6022cac802870cfacf0d52', Package::BASE_SHA);
        self::assertSame([125, 128, 122], Package::PAGE_IDS);
        self::assertSame([20, 20, 20], Package::CATEGORY_IDS);
        self::assertSame([11, 11, 12], Package::CONTINUING_COUNTS);
        self::assertSame([9, 9, 10], Package::PRACTICE_SOURCE_INDICES);
        self::assertSame([null, null, null], Package::LEVELS);
        self::assertSame([null, null, null], array_column($contract['targets'], 'level'));
        self::assertSame(['A1–A2', 'A2–B1', 'A2–B1'], Package::DISPLAY_LEVELS);
        $slots = 0;
        foreach ($patch['targets'] as $i => $target) {
            $old = $before['targets'][$i]['before'];
            $lesson = $master['lessons'][$i];
            self::assertCount(Package::CONTINUING_COUNTS[$i], $lesson['blocks']);
            self::assertSame(Package::definition($lesson, $old), $target['after']);
            self::assertSame($target['after'], json_decode(file_get_contents($this->sourceRoot().'/'.$target['path']), true, flags: JSON_THROW_ON_ERROR));
            $protected = $target['after'];
            $protected['page']['blocks'] = $old['page']['blocks'];
            foreach (['subtitle_html', 'subtitle_text'] as $field) { $protected['page'][$field] = $old['page'][$field]; }
            self::assertSame($old, $protected, 'Raw title, slug, category, tags and other source metadata stay outside the allowed body/subtitle delta.');
            self::assertSame($target['local_url'], $lesson['local_url']);
            self::assertSame('uk', $lesson['locale']);
            self::assertSame('A2', $lesson['intended_own_practice_level']);
            self::assertNull($lesson['technical_block_level']);
            self::assertSame(Package::DISPLAY_LEVELS[$i], $lesson['blocks'][0]['data']['level']);
            foreach ($old['page']['blocks'] as $slot => $source) {
                ++$slots;
                $new = $target['after']['page']['blocks'][$slot];
                self::assertSame($slot, $lesson['blocks'][$slot]['existing_source_index']);
                self::assertSame(array_key_exists('level', $source), array_key_exists('level', $new));
                self::assertSame($source['level'] ?? null, $new['level'] ?? null);
                self::assertSame(M26DetailPackage::uuid($target['identity'], $source, $slot + 1), M26DetailPackage::uuid($target['identity'], $new, $slot + 1));
                self::assertLessThanOrEqual(65535, strlen($new['body']), 'An existing TEXT column does not require a schema change.');
                unset($source['body'], $new['body']);
                self::assertSame($source, $new);
            }
            $practiceSlot = Package::PRACTICE_SOURCE_INDICES[$i];
            $practice = $target['after']['page']['blocks'][$practiceSlot];
            self::assertSame('practice-set', $practice['type']);
            self::assertSame('left', $practice['column']);
            self::assertNull($practice['level'] ?? null);
            self::assertSame(Package::PRACTICE_UUIDS[$i], M26DetailPackage::uuid($target['identity'], $practice, $practiceSlot + 1));
            self::assertCount(1, array_filter($lesson['blocks'], static fn ($block) => $block['type'] === 'practice-set'));
        }
        self::assertSame(34, $slots);
        foreach (Package::profile()['artifacts'] as $relative => $sha) {
            self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D', $sha, 'A pending artifact is not accepted content.');
            self::assertSame($sha, hash_file('sha256', $this->sourceRoot().'/'.$relative));
        }
    }

    public function test_forty_eight_graded_tasks_preserve_thirty_six_old_functions_and_three_ungraded_demos(): void
    {
        [$before, , $master] = $this->release();
        $graded = $retained = $demos = 0;
        $newIds = [];
        foreach ($master['lessons'] as $i => $lesson) {
            $data = self::practice($master, $i);
            $old = json_decode($before['targets'][$i]['before']['page']['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['body'], true, flags: JSON_THROW_ON_ERROR);
            foreach (['selects' => 5, 'choices' => 2, 'inputs' => 6, 'rephrase' => 4] as $group => $count) {
                self::assertCount($count, $data[$group]);
                foreach ($data[$group] as $index => $task) {
                    $oldCount = ['selects' => 5, 'inputs' => 4, 'rephrase' => 4][$group] ?? 0;
                    if ($index < $oldCount) {
                        self::assertArrayNotHasKey('id', $old[$group][$index]);
                        self::assertArrayNotHasKey('source_index', $old[$group][$index]);
                        self::assertArrayNotHasKey('id', $task);
                        self::assertArrayNotHasKey('source_index', $task);
                    } else {
                        self::assertMatchesRegularExpression('/^m75-(?:possessive|reflexive|indefinite)-[a-z0-9-]+$/D', $task['id']);
                        $newIds[] = $task['id'];
                    }
                    if ($group === 'rephrase' && $index === 0) {
                        ++$demos;
                        self::assertNotEmpty($task['example_original']);
                        self::assertNotEmpty($task['example_target']);
                        self::assertArrayNotHasKey('answer', $task);
                        self::assertArrayNotHasKey('accepted', $task);
                        continue;
                    }
                    ++$graded;
                    if ($index < $oldCount) { ++$retained; }
                    self::assertNotSame('', trim($task['answer']));
                    self::assertNotSame('', Package::comparisonKey($task['answer']));
                    self::assertContains($task['answer'], $task['accepted']);
                    self::assertArrayNotHasKey('punctuation_sensitive', $task);
                    self::assertArrayNotHasKey('strict', $task);
                }
            }
            self::assertSame(12, count($old['selects']) + count($old['inputs']) + count(array_filter($old['rephrase'], static fn ($task) => isset($task['answer']))));
            for ($n = 0; $n < 4; ++$n) {
                self::assertStringNotContainsString('/', $data['inputs'][$n]['before'], 'Retained short gaps do not become four nominal sentence builders.');
                self::assertStringContainsString('___', $data['inputs'][$n]['before']);
            }
        }
        self::assertSame(48, $graded);
        self::assertSame(36, $retained);
        self::assertSame(3, $demos);
        self::assertCount(12, $newIds);
        self::assertCount(12, array_unique($newIds));
    }

    public function test_every_literal_option_and_accepted_value_has_plain_feedback_without_hidden_normalized_collisions(): void
    {
        [, , $master] = $this->release();
        foreach ($master['lessons'] as $i => $lesson) {
            $data = self::practice($master, $i);
            foreach (['selects', 'choices', 'inputs', 'rephrase'] as $group) {
                foreach ($data[$group] as $index => $task) {
                    if ($group === 'rephrase' && $index === 0) { continue; }
                    $acceptedKeys = array_map(Package::comparisonKey(...), $task['accepted']);
                    self::assertNotContains('', $acceptedKeys);
                    foreach ([...$task['accepted'], ...($task['options'] ?? [])] as $value) {
                        $literal = mb_strtolower(trim($value));
                        self::assertArrayHasKey($literal, $task['feedback'], $lesson['slug'].':'.$group.':'.$index.':'.$literal);
                        self::assertNotSame('', trim($task['feedback'][$literal]));
                    }
                    foreach ($task['feedback'] as $key => $text) {
                        self::assertSame(mb_strtolower(trim($key)), $key);
                        self::assertDoesNotMatchRegularExpression('/<(?:\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu', $text);
                        self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $text);
                    }
                    if (!in_array($group, ['selects', 'choices'], true)) { continue; }
                    self::assertGreaterThanOrEqual(2, count($task['options']));
                    self::assertContains($task['answer'], $task['options']);
                    $keys = array_map(Package::comparisonKey(...), $task['options']);
                    self::assertCount(count($keys), array_unique($keys));
                    self::assertNotContains('', $keys);
                    foreach ($task['options'] as $option) {
                        if (!in_array($option, $task['accepted'], true)) {
                            self::assertNotContains(Package::comparisonKey($option), $acceptedKeys, 'A displayed distractor cannot normalize to a declared correct answer.');
                        }
                    }
                    $visible = strip_tags(($task['label'] ?? '').' '.($task['prompt'] ?? ''));
                    self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $visible, 'Critical context belongs in a field the native control renders.');
                }
            }
        }
        $indefinite = self::practice($master, 2);
        self::assertContains('No one', $indefinite['inputs'][2]['accepted']);
        self::assertArrayHasKey('no one', $indefinite['inputs'][2]['feedback'], 'An internal space survives the actual literal trim-lower feedback lookup.');
    }

    public function test_six_full_sentence_inputs_have_shuffled_one_to_three_word_chunks_and_exact_multiplicity(): void
    {
        [, , $master] = $this->release();
        $expected = [
            'm75-possessive-sentence-blue-bag' => ['This is my new blue bag.'],
            'm75-possessive-sentence-own-room' => ['I need a room of my own.', 'I need my own room.'],
            'm75-reflexive-sentence-beneficiary' => ['I bought this notebook for myself.', 'I bought this notepad for myself.'],
            'm75-reflexive-sentence-without-help' => ['We carried the sofa upstairs by ourselves.', 'We carried the couch upstairs by ourselves.', 'We carried the sofa upstairs without help.', 'We carried the couch upstairs without help.'],
            'm75-indefinite-sentence-their-agreement' => ['Everyone has their ticket.', 'Everybody has their ticket.'],
            'm75-indefinite-sentence-nobody' => ['Nobody knows the answer.', 'No one knows the answer.', 'No-one knows the answer.'],
        ];
        $seen = [];
        foreach ($master['lessons'] as $i => $lesson) {
            foreach (array_slice(self::practice($master, $i)['inputs'], 4, 2) as $input) {
                $seen[] = $input['id'];
                self::assertArrayHasKey($input['id'], $expected);
                self::assertSame($expected[$input['id']], $input['accepted'], 'Finite reviewed alternatives preserve the teaching purpose; no generated permutations or unrelated synonyms.');
                self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $input['after']);
                self::assertStringNotContainsString($input['answer'], $input['after'], 'The manual condition must not reveal the complete ordered answer.');
                $chunks = array_map(trim(...), explode('/', $input['before']));
                self::assertGreaterThanOrEqual(2, count($chunks));
                $bank = [];
                foreach ($chunks as $chunk) {
                    $words = self::words($chunk);
                    self::assertGreaterThanOrEqual(1, count($words));
                    self::assertLessThanOrEqual(3, count($words));
                    array_push($bank, ...$words);
                }
                $answer = self::words($input['answer']);
                self::assertGreaterThanOrEqual(4, count($answer));
                self::assertNotSame(implode(' ', $answer), implode(' ', $bank), 'The initial token-bank order is not the solution.');
                self::assertSame(array_count_values($answer), $this->orderedCounts($answer, $bank), 'Every repeated word is a distinct required instance.');
                foreach ($input['accepted'] as $alias) {
                    self::assertMatchesRegularExpression('/^[A-Z].+[.!?]$/uD', $alias);
                    self::assertDoesNotMatchRegularExpression('/\{a\d+\}|<[^>]+>|[\x{0400}-\x{04ff}]|—/u', $alias);
                    self::assertArrayHasKey(mb_strtolower(trim($alias)), $input['feedback']);
                }
            }
        }
        self::assertSame(array_keys($expected), $seen);
    }

    /** Preserve answer-key insertion order while comparing the complete word multiset. */
    private function orderedCounts(array $answer, array $bank): array
    {
        $counts = array_count_values($bank);
        $out = [];
        foreach (array_keys(array_count_values($answer)) as $word) { $out[$word] = $counts[$word] ?? 0; unset($counts[$word]); }
        return $out + $counts;
    }

    public function test_apostrophe_semantic_transfer_and_no_pronoun_are_not_empty_or_punctuation_only_answers(): void
    {
        [$before, , $master] = $this->release();
        $old = json_decode($before['targets'][0]['before']['page']['blocks'][9]['body'], true, flags: JSON_THROW_ON_ERROR);
        self::assertStringContainsString("It's", $old['rephrase'][3]['answer']);
        self::assertSame(Package::comparisonKey('Its a beautiful day.'), Package::comparisonKey("It's a beautiful day."), 'The unchanged native comparison alphabet is not an apostrophe examiner.');
        $apostrophe = self::practice($master, 0)['rephrase'][3];
        self::assertSame('речення Б', $apostrophe['answer']);
        self::assertSame(['речення Б', 'Б'], $apostrophe['accepted']);
        self::assertStringContainsString('Its', $apostrophe['original']);
        self::assertStringContainsString("It's", $apostrophe['original']);
        foreach (['речення А', 'А', '', ' ', "It's a beautiful day.", 'Its a beautiful day.'] as $wrong) {
            self::assertNotContains(Package::comparisonKey($wrong), array_map(Package::comparisonKey(...), $apostrophe['accepted']));
        }
        self::assertSame('', Package::comparisonKey('—'));
        $reflexive = self::practice($master, 1);
        foreach ([2, 4] as $index) {
            $task = $reflexive['selects'][$index];
            self::assertSame('Займенник не потрібен', $task['answer']);
            self::assertSame(['Займенник не потрібен'], $task['accepted']);
            self::assertContains('Займенник не потрібен', $task['options']);
            $accepted = array_map(Package::comparisonKey(...), $task['accepted']);
            foreach (['', ' ', "\t\n", '—', 'myself', 'themselves'] as $unansweredOrWrong) {
                self::assertNotContains(Package::comparisonKey($unansweredOrWrong), $accepted, 'Untouched, whitespace or reset-to-empty data never represents the deliberate choice.');
            }
        }
        self::assertStringContainsString('самостійно', $reflexive['selects'][1]['label']);
        self::assertStringContainsString('іншу жінку', $reflexive['selects'][1]['label']);
    }

    public function test_navigation_translation_fields_and_explicit_visible_basic_decisions_use_only_native_data(): void
    {
        [$before, , $master] = $this->release();
        $slugs = ['pronouns-theory', 'personal-object-pronouns', 'possessive-adjectives-vs-pronouns-my-mine-your-yours',
            'reflexive-pronouns-myself-yourself-themselves', 'indefinite-pronouns-someone-anyone-nobody-nothing',
            'relative-pronouns-who-which-that-whose', 'each-every-all', 'reciprocal-pronouns-each-other-one-another', 'one-ones'];
        foreach ($master['lessons'] as $i => $lesson) {
            self::assertSame([], $lesson['details']);
            self::assertNotEmpty($lesson['point_detail_decisions']);
            $byKey = array_column($lesson['blocks'], null, 'key');
            foreach ($lesson['point_detail_decisions'] as $decision) {
                self::assertArrayHasKey($decision['block_key'], $byKey);
                self::assertSame('visible_basic', $decision['decision']);
                self::assertNotSame('', trim($decision['reason']));
            }
            foreach ($lesson['blocks'] as $block) {
                self::assertStringNotContainsString('m75-section', json_encode($block, JSON_UNESCAPED_UNICODE));
                foreach ($block['data']['sections'] ?? [] as $section) {
                    self::assertContains($section['color'], ['blue', 'emerald', 'sky', 'amber', 'rose', 'slate']);
                    foreach ($section['examples'] ?? [] as $example) {
                        self::assertNotSame('', trim($example['en']));
                        self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $example['ua']);
                        self::assertDoesNotMatchRegularExpression('/[\x{0400}-\x{04ff}]/u', $example['en'], 'English examples must not occupy the Ukrainian translation field or mix editorial explanations into English.');
                    }
                }
                if ($block['type'] === 'comparison-table') {
                    self::assertNotEmpty($block['data']['rows']);
                    foreach ($block['data']['rows'] as $row) {
                        self::assertNotSame('', trim($row['en']));
                        self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $row['ua']);
                        if (preg_match('/[a-z]{2,}[.!?]/i', $row['note'] ?? '') === 1) {
                            self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $row['note']);
                        }
                    }
                }
                if ($block['type'] === 'mistakes-grid') {
                    foreach ($block['data']['items'] as $item) {
                        foreach (['wrong_en', 'wrong_ua', 'right_en', 'right_ua'] as $field) { self::assertNotSame('', trim($item[$field])); }
                        self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $item['right_ua']);
                    }
                }
            }
            $footer = Package::CONTINUING_COUNTS[$i] - 1;
            $oldNav = json_decode($before['targets'][$i]['before']['page']['blocks'][$footer]['body'], true, flags: JSON_THROW_ON_ERROR)['items'];
            $nav = $lesson['blocks'][$footer]['data']['items'];
            self::assertCount(9, $nav);
            foreach ($nav as $n => $item) {
                self::assertSame($oldNav[$n]['label'], $item['label']);
                self::assertSame($oldNav[$n]['current'], $item['current']);
                self::assertSame('/theory/zaimennyky-ta-vkazivni-slova/'.$slugs[$n], $item['url']);
                self::assertNotSame('#', $item['url']);
            }
        }
    }

    public function test_mutated_or_empty_teaching_contracts_fail_closed_without_mutating_the_captured_fixture(): void
    {
        [$before, $patch, $master, $contract] = $this->release();
        $mutations = [
            'foreign-owner' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['identity'] = Package::OWNERS[0]; },
            'foreign-locale' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['locale'] = 'en'; },
            'foreign-category' => static function (&$b, &$p, &$m, &$c): void { $p['targets'][1]['page_binding']['page_category_id'] = 16; },
            'fake-exact-A2' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][9]['level'] = 'A2'; },
            'range-as-SQL-filter' => static function (&$b, &$p, &$m, &$c): void { $c['targets'][1]['level'] = 'A2–B1'; },
            'retained-task-ID' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][9]['data']['inputs'][0]['id'] = 'm75-new-replacement'; },
            'retained-source-index' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['blocks'][10]['data']['selects'][0]['source_index'] = 1; },
            'missing-old-task' => static function (&$b, &$p, &$m, &$c): void { array_pop($m['lessons'][0]['blocks'][9]['data']['selects']); },
            'graded-demo' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['blocks'][10]['data']['rephrase'][0]['answer'] = 'A'; },
            'lost-sentence-input' => static function (&$b, &$p, &$m, &$c): void { array_pop($m['lessons'][1]['blocks'][9]['data']['inputs']); },
            'unrendered-sentence-context' => static function (&$b, &$p, &$m, &$c): void { $t = &$m['lessons'][1]['blocks'][9]['data']['inputs'][4]; $t['prompt'] = $t['after']; $t['after'] = ''; },
            'empty-answer' => static function (&$b, &$p, &$m, &$c): void { $t = &$m['lessons'][0]['blocks'][9]['data']['inputs'][0]; $t['answer'] = ''; $t['accepted'] = ['']; },
            'whitespace-answer' => static function (&$b, &$p, &$m, &$c): void { $t = &$m['lessons'][0]['blocks'][9]['data']['inputs'][0]; $t['answer'] = '  '; $t['accepted'] = ['  ']; },
            'punctuation-accepted' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['blocks'][10]['data']['inputs'][0]['accepted'][] = '—'; },
            'dash-as-no-pronoun' => static function (&$b, &$p, &$m, &$c): void { $t = &$m['lessons'][1]['blocks'][9]['data']['selects'][2]; $t['answer'] = '—'; $t['accepted'] = ['—']; $t['options'][0] = '—'; $t['feedback']['—'] = 'Немає займенника.'; },
            'key-not-an-option' => static function (&$b, &$p, &$m, &$c): void { $t = &$m['lessons'][0]['blocks'][9]['data']['selects'][0]; $t['answer'] = 'unlisted'; $t['accepted'] = ['unlisted']; $t['feedback']['unlisted'] = 'Непоказаний ключ.'; },
            'apostrophe-option-collision' => static function (&$b, &$p, &$m, &$c): void { $t = &$m['lessons'][0]['blocks'][9]['data']['selects'][0]; $t['options'] = ['Its', "It's"]; $t['answer'] = 'Its'; $t['accepted'] = ['Its']; $t['feedback'] = ['its' => 'Присвійна форма.', "it's" => 'Скорочення.']; },
            'apostrophe-transfer-undone' => static function (&$b, &$p, &$m, &$c): void { $t = &$m['lessons'][0]['blocks'][9]['data']['rephrase'][3]; $t['answer'] = "It's a beautiful day."; $t['accepted'] = ["It's a beautiful day."]; $t['feedback']["it's a beautiful day."] = 'Неправильне повернення до collision.'; },
            'literal-space-feedback-lost' => static function (&$b, &$p, &$m, &$c): void { unset($m['lessons'][2]['blocks'][10]['data']['inputs'][2]['feedback']['no one']); },
            'HTML-feedback' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][9]['data']['inputs'][0]['feedback']['myself'] = '<b>Саме мовець.</b>'; },
            'styled-learning-payload' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][2]['data']['sections'][0]['description'] = '<p style="color:red">Правило.</p>'; },
            'unsupported-usage-accent' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][2]['data']['sections'][0]['color'] = 'purple'; },
            'translation-missing' => static function (&$b, &$p, &$m, &$c): void { unset($m['lessons'][1]['blocks'][2]['data']['sections'][0]['examples'][0]['ua']); },
            'duplicate-practice' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][] = $m['lessons'][1]['blocks'][9]; },
            'orphan-detail' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['details'][] = ['key' => 'orphan', 'owner_block_key' => 'absent', 'owner_pointer' => '/sections/0']; },
            'footer-hash-link' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][10]['data']['items'][0]['url'] = '#'; },
            'stale-after-body' => static function (&$b, &$p, &$m, &$c): void { $p['targets'][2]['after']['page']['blocks'][0]['body'] .= "\n"; },
        ];
        foreach ($mutations as $case => $mutate) {
            $b = $before; $p = $patch; $m = $master; $c = $contract;
            $mutate($b, $p, $m, $c);
            $this->refuses(static function () use ($case, $b, $p, $m, $c): void {
                if ($case !== 'stale-after-body') {
                    foreach ($m['lessons'] as $i => $lesson) { $p['targets'][$i]['after'] = Package::definition($lesson, $b['targets'][$i]['before']); }
                }
                Package::validate($b, $p, $m, $c);
            }, $case);
        }
        self::assertSame([$before, $patch, $master, $contract], array_slice($this->release(), 0, 4), 'Negative probes never rewrite frozen sources or the independent BEFORE.');
    }

    public function test_loader_refuses_missing_roots_and_stale_pins_and_keeps_active_m74_successor(): void
    {
        $profile = Package::profile();
        $this->refuses(fn () => (new TheoryNativeContentRelease($profile))->load($this->sourceRoot().'/does-not-exist-m75'), 'Missing explicit source root');
        $first = array_key_first($profile['artifacts']);
        $profile['artifacts'][$first] = str_repeat('0', 64);
        $this->refuses(fn () => (new TheoryNativeContentRelease($profile))->load($this->sourceRoot()), 'Stale frozen pin');
        self::assertSame('1.0.1', M74OtherPronounsBasics::profile()['version']);
        self::assertSame('docs/content/m74-other-pronouns-basics.v1.0.1.json', M74OtherPronounsBasics::MASTER);
        self::assertSame('118d754ab5fc36c8e941a285240ec9156b4f214f9d9bd760a231cc26a6697fd2', hash_file('sha256', $this->sourceRoot().'/'.M74OtherPronounsBasics::MASTER));
        foreach (M74OtherPronounsBasics::profile()['artifacts'] as $path => $sha) { self::assertSame($sha, hash_file('sha256', $this->sourceRoot().'/'.$path)); }
        [, , $m74] = M74OtherPronounsBasics::load($this->sourceRoot());
        self::assertSame('1.0.1', $m74['version']);
        $own = 0;
        foreach ($m74['lessons'] as $lesson) {
            foreach ($lesson['blocks'] as $block) {
                if ($block['type'] !== 'practice-set') { continue; }
                foreach (['selects', 'choices', 'inputs', 'rephrase'] as $group) {
                    $own += count(array_filter($block['data'][$group] ?? [], static fn ($task) => isset($task['answer'])));
                }
            }
        }
        self::assertSame(35, $own, 'M75 does not substitute M74 historical1.0.0 expectations for active1.0.1.');
    }
}
