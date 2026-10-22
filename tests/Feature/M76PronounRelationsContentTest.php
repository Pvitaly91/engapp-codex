<?php

namespace Tests\Feature;

use App\Support\M26DetailPackage;
use App\Support\M75PronounForms;
use App\Support\M76PronounRelations as Package;
use App\Support\TheoryNativeContentRelease;
use RuntimeException;
use Tests\TestCase;

/** Frozen public teaching data only; no working database, browser or fixture reseeding. */
class M76PronounRelationsContentTest extends TestCase
{
    private function sourceRoot(): string { return dirname(__DIR__, 2); }

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

    public function test_exact_scope_keeps_thirty_six_slots_null_levels_titles_and_three_practice_uuids(): void
    {
        [$before, $patch, $master, $contract] = $this->release();
        self::assertSame('e0720938bad114c7e43e8e6efafa8355c1993fc1', Package::BASE_SHA);
        self::assertSame([129, 121, 127], Package::PAGE_IDS);
        self::assertSame([20, 20, 20], Package::CATEGORY_IDS);
        self::assertSame([12, 12, 12], Package::CONTINUING_COUNTS);
        self::assertSame([10, 10, 9], Package::PRACTICE_SOURCE_INDICES);
        self::assertSame([null, null, null], Package::LEVELS);
        self::assertSame([null, null, null], array_column($contract['targets'], 'level'));
        self::assertSame(['B1–B2', 'A2–B1', 'A2–B1'], Package::DISPLAY_LEVELS);
        $primitive = new TheoryNativeContentRelease(Package::profile()); $slots = 0;
        foreach ($patch['targets'] as $i => $target) {
            $old = $before['targets'][$i]['before']; $oldSnapshot = $old;
            $lesson = $master['lessons'][$i]; $projected = $primitive->projectedBefore($old);
            self::assertSame($oldSnapshot, $old, 'Projection cannot mutate the independent raw BEFORE.');
            self::assertCount(12, $lesson['blocks']);
            self::assertSame(Package::AFTER_TYPES[$i], array_column($lesson['blocks'], 'type'));
            self::assertSame(Package::definition($lesson, $old), $target['after']);
            self::assertSame($target['after'], json_decode(file_get_contents($this->sourceRoot().'/'.$target['path']), true, flags: JSON_THROW_ON_ERROR));
            $protected = $target['after']; $protected['page']['blocks'] = $old['page']['blocks'];
            foreach (['subtitle_html', 'subtitle_text'] as $field) { $protected['page'][$field] = $old['page'][$field]; }
            self::assertSame($old, $protected, 'Raw title, slug, category, tags and unrelated metadata are not M76 content deltas.');
            self::assertSame(Package::RENDERED_TITLES[$i], $lesson['preserved_h1']);
            self::assertSame($i === 0 ? 'B1' : 'A2', $lesson['intended_own_practice_level']);
            self::assertSame('uk', $lesson['locale']);
            self::assertNull($lesson['technical_block_level']);
            self::assertSame(Package::DISPLAY_LEVELS[$i], $lesson['blocks'][0]['data']['level']);
            foreach ($old['page']['blocks'] as $slot => $source) {
                ++$slots; $new = $target['after']['page']['blocks'][$slot];
                self::assertSame($slot, $lesson['blocks'][$slot]['existing_source_index']);
                self::assertSame(array_key_exists('level', $source), array_key_exists('level', $new));
                self::assertNull($new['level'] ?? null);
                self::assertSame(M26DetailPackage::uuid($target['identity'], $source, $slot + 1), M26DetailPackage::uuid($target['identity'], $new, $slot + 1));
                self::assertLessThanOrEqual(65535, strlen($new['body']), 'The existing TEXT schema remains sufficient.');
                $expected = $projected['page']['blocks'][$slot]; unset($expected['body'], $new['body']);
                self::assertSame($expected, $new, 'Only an explicit finite metadata projection may differ from raw source.');
            }
            $slot = Package::PRACTICE_SOURCE_INDICES[$i]; $practice = $target['after']['page']['blocks'][$slot];
            self::assertSame('practice-set', $practice['type']);
            self::assertSame('left', $practice['column']);
            self::assertNull($practice['level'] ?? null);
            self::assertSame(Package::PRACTICE_UUIDS[$i], M26DetailPackage::uuid($target['identity'], $practice, $slot + 1));
            self::assertCount(1, array_filter($lesson['blocks'], static fn ($block) => $block['type'] === 'practice-set'));
        }
        self::assertSame(36, $slots);
        foreach (Package::profile()['artifacts'] as $path => $sha) {
            self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D', $sha);
            self::assertNotSame(str_repeat('0', 64), $sha, 'Draft placeholder pins are not an accepted release.');
            self::assertSame($sha, hash_file('sha256', $this->sourceRoot().'/'.$path));
        }
    }

    public function test_forty_six_graded_tasks_retain_twenty_four_and_transfer_ten_source_functions_without_inventing_demos(): void
    {
        [$before, , $master] = $this->release(); $graded = $retained = $transferred = $demos = 0; $newIds = [];
        foreach ($master['lessons'] as $i => $lesson) {
            $data = self::practice($master, $i); $oldBlock = $before['targets'][$i]['before']['page']['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]];
            $old = $i < 2 ? json_decode($oldBlock['body'], true, flags: JSON_THROW_ON_ERROR) : null;
            foreach (['selects' => 5, 'choices' => 2, 'inputs' => $i === 2 ? 2 : 6, 'rephrase' => $i === 2 ? 5 : 4] as $group => $count) {
                self::assertCount($count, $data[$group]);
                foreach ($data[$group] as $index => $task) {
                    $isOld = $i < 2 ? ($group !== 'choices' && $index < ($group === 'selects' ? 5 : 4)) : in_array($group, ['selects', 'rephrase'], true);
                    if ($isOld) {
                        foreach (['id', 'source_index'] as $field) {
                            $oldItem = $i < 2 ? $old[$group][$index] : [];
                            self::assertSame(array_key_exists($field, $oldItem), array_key_exists($field, $task));
                            if (array_key_exists($field, $oldItem)) { self::assertSame($oldItem[$field], $task[$field]); }
                        }
                    } else {
                        self::assertMatchesRegularExpression('/^m76-(?:relative|each|reciprocal)-[a-z0-9-]+$/D', $task['id']);
                        self::assertArrayNotHasKey('source_index', $task); $newIds[] = $task['id'];
                    }
                    if ($i < 2 && $group === 'rephrase' && $index === 0) {
                        ++$demos; self::assertNotEmpty($task['example_original']); self::assertNotEmpty($task['example_target']);
                        self::assertArrayNotHasKey('answer', $task); self::assertArrayNotHasKey('accepted', $task); continue;
                    }
                    ++$graded;
                    if ($isOld) { $i < 2 ? ++$retained : ++$transferred; }
                    self::assertNotSame('', Package::comparisonKey($task['answer']));
                    self::assertContains($task['answer'], $task['accepted']);
                    self::assertArrayNotHasKey('strict', $task); self::assertArrayNotHasKey('punctuation_sensitive', $task);
                }
            }
            self::assertSame('Вправа 5. Побудуй речення', $data['linked_practice']['title']);
            if ($i === 2) {
                self::assertSame('box', $oldBlock['type']); self::assertCount(2, $oldBlock['exercises']);
                foreach ($oldBlock['exercises'] as $exercise) { self::assertCount(5, $exercise['items']); }
                foreach ($oldBlock['exercises'][1]['items'] as $n => $stimulus) { self::assertStringContainsString($stimulus, $data['rephrase'][$n]['original']); }
            }
        }
        self::assertSame(46, $graded); self::assertSame(24, $retained); self::assertSame(10, $transferred); self::assertSame(2, $demos);
        self::assertCount(12, $newIds); self::assertCount(12, array_unique($newIds));
    }

    public function test_all_literal_options_and_accepted_values_have_plain_feedback_and_distinct_nonempty_comparison_keys(): void
    {
        [, , $master] = $this->release();
        foreach ($master['lessons'] as $i => $lesson) {
            foreach (['selects', 'choices', 'inputs', 'rephrase'] as $group) {
                foreach (self::practice($master, $i)[$group] as $index => $task) {
                    if (!isset($task['answer'])) { continue; }
                    $acceptedKeys = array_map(Package::comparisonKey(...), $task['accepted']); self::assertNotContains('', $acceptedKeys);
                    foreach ([...$task['accepted'], ...($task['options'] ?? [])] as $value) {
                        $key = mb_strtolower(trim($value)); self::assertArrayHasKey($key, $task['feedback']); self::assertNotSame('', trim($task['feedback'][$key]));
                    }
                    foreach ($task['feedback'] as $key => $text) {
                        self::assertSame(mb_strtolower(trim($key)), $key);
                        self::assertDoesNotMatchRegularExpression('/<(?:\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu', $text);
                        self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $text);
                    }
                    if (!in_array($group, ['selects', 'choices'], true)) { continue; }
                    self::assertGreaterThanOrEqual(2, count($task['options']));
                    $keys = array_map(Package::comparisonKey(...), $task['options']);
                    self::assertCount(count($keys), array_unique($keys)); self::assertNotContains('', $keys);
                    foreach ($task['options'] as $option) {
                        if (!in_array($option, $task['accepted'], true)) { self::assertNotContains(Package::comparisonKey($option), $acceptedKeys); }
                    }
                    self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', strip_tags(($task['label'] ?? '').' '.($task['prompt'] ?? '')));
                }
            }
        }
    }

    public function test_six_full_sentence_inputs_have_exact_shuffled_chunks_and_reviewed_manual_alternatives(): void
    {
        [, , $master] = $this->release();
        $expected = [
            ['The man who he called is my boss.', 'The man whom he called is my boss.', 'The man that he called is my boss.', 'The man he called is my boss.', 'The man who he called is my manager.', 'The man whom he called is my manager.', 'The man that he called is my manager.', 'The man he called is my manager.'],
            ['I know the woman whose new bike is blue.', 'I know the woman whose new bicycle is blue.'],
            ['Each of us has a ticket.', 'Every one of us has a ticket.', 'We each have a ticket.', 'We all have a ticket.', 'All of us have a ticket.'],
            ['All the books are on the table.', 'All of the books are on the table.'],
            ['We listen to each other.', 'We listen to one another.'],
            ["Do they know each other's names?", "Do they know one another's names?"],
        ];
        $seen = 0;
        foreach ($master['lessons'] as $i => $lesson) {
            foreach (Package::SENTENCE_INPUT_INDICES[$i] as $index) {
                $input = self::practice($master, $i)['inputs'][$index];
                self::assertSame($expected[$seen++], $input['accepted']);
                self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $input['after']);
                self::assertStringNotContainsString($input['answer'], $input['after']);
                $chunks = array_map(trim(...), explode('/', $input['before'])); self::assertGreaterThanOrEqual(2, count($chunks)); $bank = [];
                foreach ($chunks as $chunk) {
                    $words = self::words($chunk); self::assertGreaterThanOrEqual(1, count($words)); self::assertLessThanOrEqual(3, count($words)); array_push($bank, ...$words);
                }
                $answer = self::words($input['answer']); self::assertGreaterThanOrEqual(4, count($answer));
                self::assertNotSame(implode(' ', $answer), implode(' ', $bank));
                $answerCounts = array_count_values($answer); $bankCounts = array_count_values($bank); ksort($answerCounts); ksort($bankCounts);
                self::assertSame($answerCounts, $bankCounts, 'Each repeated word keeps its complete token multiplicity.');
                foreach ($input['accepted'] as $alias) {
                    self::assertMatchesRegularExpression('/^[A-Z].+[.!?]$/uD', $alias);
                    self::assertDoesNotMatchRegularExpression('/\{a\d+\}|<[^>]+>|[\x{0400}-\x{04ff}]|—/u', $alias);
                    self::assertArrayHasKey(mb_strtolower(trim($alias)), $input['feedback']);
                }
            }
        }
        self::assertSame(6, $seen);
    }

    public function test_relative_role_and_omission_and_reciprocal_apostrophe_boundaries_are_not_punctuation_only_guesses(): void
    {
        [, , $master] = $this->release(); $relative = self::practice($master, 0); $each = self::practice($master, 1); $reciprocal = self::practice($master, 2);
        self::assertNotSame(Package::comparisonKey('whose'), Package::comparisonKey("who's"));
        self::assertNotContains(Package::comparisonKey('The man who called is my boss.'), array_map(Package::comparisonKey(...), $relative['inputs'][4]['accepted']));
        self::assertNotContains(Package::comparisonKey("The man who's car was stolen called the police."), array_map(Package::comparisonKey(...), $relative['rephrase'][3]['accepted']));
        self::assertSame(['that', 'Займенник не потрібен'], $relative['inputs'][3]['accepted']);
        self::assertStringContainsString('he', $relative['inputs'][4]['after']);
        foreach (['', ' ', "\t\n", '—'] as $empty) {
            self::assertNotContains(Package::comparisonKey($empty), array_map(Package::comparisonKey(...), $relative['inputs'][3]['accepted']));
        }
        self::assertSame(['who', 'that'], $relative['selects'][0]['accepted']);
        self::assertSame(['which', 'that'], $relative['selects'][1]['accepted']);
        self::assertSame(['that', 'which'], $relative['selects'][3]['accepted']);
        self::assertSame(['each', 'every'], $each['selects'][0]['accepted']);
        self::assertSame(['every', 'each'], $each['selects'][2]['accepted']);
        self::assertSame(['all', 'each'], $each['selects'][4]['accepted']);
        self::assertSame(['All', 'All of'], $each['inputs'][1]['accepted']);
        self::assertSame(Package::comparisonKey('We respect each others opinions.'), Package::comparisonKey("We respect each other's opinions."));
        $semantic = $reciprocal['rephrase'][1]; self::assertSame(['речення Б', 'Б'], $semantic['accepted']);
        foreach (['', ' ', 'А', 'речення А', 'We respect each others opinions.', "We respect each other's opinions."] as $wrong) {
            self::assertNotContains(Package::comparisonKey($wrong), array_map(Package::comparisonKey(...), $semantic['accepted']));
        }
        self::assertSame('She and I understand each other very well.', $reciprocal['rephrase'][2]['answer']);
        self::assertSame('He talked to himself.', $reciprocal['rephrase'][4]['answer']);
    }

    public function test_native_translation_decisions_and_both_reciprocal_navigation_identities_are_explicit(): void
    {
        [$before, , $master] = $this->release(); $linkCount = 0;
        $slugs = ['pronouns-theory', 'personal-object-pronouns', 'possessive-adjectives-vs-pronouns-my-mine-your-yours', 'reflexive-pronouns-myself-yourself-themselves',
            'indefinite-pronouns-someone-anyone-nobody-nothing', 'relative-pronouns-who-which-that-whose', 'each-every-all', 'reciprocal-pronouns-each-other-one-another', 'one-ones'];
        foreach ($master['lessons'] as $i => $lesson) {
            self::assertSame([], $lesson['details']); self::assertNotEmpty($lesson['point_detail_decisions']);
            $byKey = array_column($lesson['blocks'], null, 'key');
            foreach ($lesson['point_detail_decisions'] as $decision) {
                self::assertArrayHasKey($decision['block_key'], $byKey); self::assertSame('visible_basic', $decision['decision']); self::assertNotSame('', trim($decision['reason']));
            }
            foreach ($lesson['blocks'] as $block) {
                self::assertStringNotContainsString('m76-section', json_encode($block, JSON_UNESCAPED_UNICODE));
                foreach ($block['data']['sections'] ?? [] as $section) {
                    self::assertContains($section['color'], ['blue', 'emerald', 'sky', 'amber', 'rose', 'slate']);
                    foreach ($section['examples'] ?? [] as $example) {
                        self::assertNotSame('', trim($example['en'])); self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $example['ua']);
                        self::assertDoesNotMatchRegularExpression('/[\x{0400}-\x{04ff}]/u', $example['en']);
                    }
                }
                if ($block['type'] === 'comparison-table') {
                    foreach ($block['data']['rows'] as $row) { self::assertNotSame('', trim($row['en'])); self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $row['ua']); }
                }
                if ($block['type'] === 'mistakes-grid') {
                    foreach ($block['data']['items'] as $item) { foreach (['wrong_en', 'wrong_ua', 'right_en', 'right_ua'] as $field) { self::assertNotSame('', trim($item[$field])); } }
                }
            }
            $oldNav = json_decode($before['targets'][$i]['before']['page']['blocks'][11]['body'], true, flags: JSON_THROW_ON_ERROR)['items'];
            $nav = $lesson['blocks'][11]['data']['items']; self::assertCount(9, $nav); $linkCount += count($nav);
            foreach ($nav as $n => $item) {
                self::assertSame($oldNav[$n]['label'], $item['label']); self::assertSame($oldNav[$n]['current'], $item['current']);
                self::assertSame('/theory/zaimennyky-ta-vkazivni-slova/'.$slugs[$n], $item['url']);
            }
        }
        $legacy = $master['lessons'][2]['blocks'][10]; self::assertSame('forms-grid', $legacy['type']);
        self::assertSame('footer', $legacy['column']); self::assertCount(2, $legacy['data']['items']);
        self::assertCount(1, array_filter($master['lessons'][2]['blocks'], static fn ($block) => $block['type'] === 'navigation-chips'));
        foreach ($legacy['data']['items'] as $item) { self::assertNotSame('', trim($item['title'])); self::assertNotSame('', trim($item['subtitle'])); self::assertFalse($item['current']); }
        self::assertSame(['← Reflexive Pronouns', 'Each / Every / All →'], array_column($legacy['data']['items'], 'label'));
        self::assertSame(['/theory/zaimennyky-ta-vkazivni-slova/reflexive-pronouns-myself-yourself-themselves', '/theory/zaimennyky-ta-vkazivni-slova/each-every-all'], array_column($legacy['data']['items'], 'url'));
        self::assertSame(29, $linkCount + count($legacy['data']['items']));
    }

    public function test_mutated_scope_and_teaching_contracts_fail_closed_without_rewriting_the_before(): void
    {
        [$before, $patch, $master, $contract] = $this->release();
        $mutations = [
            'foreign-owner' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['identity'] = Package::OWNERS[0]; },
            'foreign-locale' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['locale'] = 'en'; },
            'foreign-category' => static function (&$b, &$p, &$m, &$c): void { $p['targets'][0]['page_binding']['page_category_id'] = 16; },
            'display-range-as-SQL-filter' => static function (&$b, &$p, &$m, &$c): void { $c['targets'][1]['level'] = 'A2–B1'; },
            'technical-A2-added' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][10]['level'] = 'A2'; },
            'lost-old-position' => static function (&$b, &$p, &$m, &$c): void { array_pop($m['lessons'][0]['blocks'][10]['data']['selects']); },
            'invented-old-ID' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['blocks'][9]['data']['rephrase'][0]['id'] = 'm76-invented-old'; },
            'invented-old-source-index' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][10]['data']['inputs'][0]['source_index'] = 1; },
            'old-demo-graded' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][10]['data']['rephrase'][0]['answer'] = 'who'; },
            'reciprocal-demo-invented' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['blocks'][9]['data']['rephrase'][0]['example_original'] = 'New demo'; },
            'static-stimulus-moved' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['blocks'][9]['data']['rephrase'][4]['original'] = 'Змінений учасник: They talked to each other.'; },
            'lost-sentence-input' => static function (&$b, &$p, &$m, &$c): void { array_pop($m['lessons'][2]['blocks'][9]['data']['inputs']); },
            'unrendered-condition' => static function (&$b, &$p, &$m, &$c): void { $t = &$m['lessons'][0]['blocks'][10]['data']['inputs'][4]; $t['prompt'] = $t['after']; $t['after'] = ''; },
            'whitespace-answer' => static function (&$b, &$p, &$m, &$c): void { $t = &$m['lessons'][1]['blocks'][10]['data']['inputs'][0]; $t['answer'] = ' '; $t['accepted'] = [' ']; },
            'empty-omission' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][10]['data']['inputs'][3]['accepted'][] = ''; },
            'punctuation-only-alias' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['blocks'][9]['data']['inputs'][0]['accepted'][] = '—'; },
            'apostrophe-option-collision' => static function (&$b, &$p, &$m, &$c): void { $t = &$m['lessons'][2]['blocks'][9]['data']['selects'][0]; $t['options'] = ['each others', "each other's"]; $t['answer'] = 'each others'; $t['accepted'] = ['each others']; $t['feedback'] = ['each others' => 'Немає апострофа.', "each other's" => 'Присвійна форма.']; },
            'best-film-unique-key' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][10]['data']['selects'][3]['accepted'] = ['that']; },
            'all-of-alias-lost' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][10]['data']['inputs'][1]['accepted'] = ['All']; },
            'semantic-apostrophe-undone' => static function (&$b, &$p, &$m, &$c): void { $t = &$m['lessons'][2]['blocks'][9]['data']['rephrase'][1]; $t['answer'] = "We respect each other's opinions."; $t['accepted'] = [$t['answer']]; $t['feedback'][mb_strtolower($t['answer'])] = 'Непідтримана сувора перевірка апострофа.'; },
            'literal-feedback-lost' => static function (&$b, &$p, &$m, &$c): void { unset($m['lessons'][1]['blocks'][10]['data']['inputs'][1]['feedback']['all of']); },
            'HTML-feedback' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][10]['data']['inputs'][0]['feedback']['who'] = '<b>Людина.</b>'; },
            'strict-grader-opt-in' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['blocks'][9]['data']['inputs'][1]['punctuation_sensitive'] = true; },
            'styled-content' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][2]['data']['sections'][0]['description'] = '<p style="color:red">Правило.</p>'; },
            'unsupported-color' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][2]['data']['sections'][0]['color'] = 'purple'; },
            'translation-missing' => static function (&$b, &$p, &$m, &$c): void { unset($m['lessons'][2]['blocks'][2]['data']['sections'][0]['examples'][0]['ua']); },
            'duplicate-practice' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['blocks'][] = $m['lessons'][2]['blocks'][9]; },
            'footer-hash-link' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['blocks'][10]['data']['items'][0]['url'] = '#'; },
            'stale-after-body' => static function (&$b, &$p, &$m, &$c): void { $p['targets'][2]['after']['page']['blocks'][0]['body'] .= "\n"; },
        ];
        foreach ($mutations as $case => $mutate) {
            $b = $before; $p = $patch; $m = $master; $c = $contract; $mutate($b, $p, $m, $c);
            $this->refuses(static function () use ($case, $b, $p, $m, $c): void {
                if ($case !== 'stale-after-body') { foreach ($m['lessons'] as $i => $lesson) { $p['targets'][$i]['after'] = Package::definition($lesson, $b['targets'][$i]['before']); } }
                Package::validate($b, $p, $m, $c);
            }, $case);
        }
        self::assertSame([$before, $patch, $master, $contract], array_slice($this->release(), 0, 4));
    }

    public function test_loader_refuses_stale_pins_and_m75_stays_frozen_on_the_accepted_base(): void
    {
        $profile = Package::profile();
        $this->refuses(fn () => (new TheoryNativeContentRelease($profile))->load($this->sourceRoot().'/does-not-exist-m76'), 'Missing root');
        $first = array_key_first($profile['artifacts']); $profile['artifacts'][$first] = str_repeat('0', 64);
        $this->refuses(fn () => (new TheoryNativeContentRelease($profile))->load($this->sourceRoot()), 'Stale pin');
        foreach (M75PronounForms::profile()['artifacts'] as $path => $sha) { self::assertSame($sha, hash_file('sha256', $this->sourceRoot().'/'.$path)); }
        [$oldBefore, $oldPatch, $oldMaster, $oldContract] = M75PronounForms::load($this->sourceRoot());
        M75PronounForms::validate($oldBefore, $oldPatch, $oldMaster, $oldContract);
        $graded = 0;
        foreach ($oldMaster['lessons'] as $lesson) {
            foreach ($lesson['blocks'] as $block) {
                if ($block['type'] !== 'practice-set') { continue; }
                foreach (['selects', 'choices', 'inputs', 'rephrase'] as $group) { $graded += count(array_filter($block['data'][$group] ?? [], static fn ($task) => isset($task['answer']))); }
            }
        }
        self::assertSame(48, $graded);
    }
}
