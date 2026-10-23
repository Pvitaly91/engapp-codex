<?php

namespace Tests\Feature;

use App\Support\M26DetailPackage as Digest;
use App\Support\M81TransformationsCommonMistakes as Package;
use App\Support\TheoryNativeContentRelease;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Finite reviewed content contract; no Laravel bootstrap, working SQL, HTTP or browser storage. */
class M81TransformationsCommonMistakesPackageTest extends TestCase
{
    private const LEVELS = [
        ['B1', null, null, 'B1', 'B1', null],
        ['B1', null, null, 'B1', 'B1', null],
        ['B1', null, null, 'B1', 'B1', null],
        ['B2', null, null, 'B2', 'B2', null],
        ['A2', null, null, 'A2', 'A2', null],
        ['A2', null, null, 'A2', 'A2', null],
    ];

    /** Reviewed literal facts, specified before freeze, never inferred from a runtime AFTER. */
    private const INPUTS = [
        ['I’d rather you didn’t call after ten tonight.', 'You had better not touch this wire because it may be live.'],
        ['The office which I visited was empty.', 'Despite being tired, Danylo finished the report.'],
        ['I found no errors in the report.', 'She gave me exactly one useful piece of advice about packing.'],
        ['It was Tom who broke the printer, not me.', 'Only after I read the example did I understand the rule.'],
        ['I saw a teacher at the station.', 'I love coffee, but I do not drink it at night.'],
        ['Can you tell me where the station is?', 'He does not like spicy food.'],
    ];

    private function root(): string { return dirname(__DIR__, 2); }
    private function release(): array
    {
        $data = Package::load($this->root());
        Package::validate($data[0], $data[1], $data[2], $data[3]);
        return $data;
    }
    private function practice(array $master, int $i): array
    {
        return $master['lessons'][$i]['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['data'];
    }
    private function refuses(callable $action, string $case): void
    {
        $caught = null;
        try { $action(); } catch (RuntimeException $exception) { $caught = $exception; }
        self::assertInstanceOf(RuntimeException::class, $caught, $case);
    }
    private function assertLearningData(array $data): void
    {
        foreach ($data as $key => $value) {
            self::assertNotContains(mb_strtolower((string) $key), ['style', 'class', 'css_class', 'stylesheet', 'view', 'native_view']);
            if (is_array($value)) { $this->assertLearningData($value); continue; }
            if (is_string($value)) {
                self::assertDoesNotMatchRegularExpression('/\{a\d+\}|&lt;\/?(?:p|span|strong|em)\b|<\/?(?:script|style|iframe)\b|<[^>]+\s(?:class|style|on[a-z]+)\s*=/iu', $value);
            }
        }
    }

    public function test_six_exact_owners_keep_thirty_six_blocks_and_twelve_explicit_native_transitions(): void
    {
        [$before, $patch, $master, $contract] = $this->release();
        self::assertSame('36753e6bd0bd1f8257908f3b30af9856d21e8e9f', Package::BASE_SHA);
        self::assertSame('1.0.0', $master['version']);
        self::assertSame([147, 150, 146, 153, 38, 43], Package::PAGE_IDS);
        self::assertSame([23, 23, 23, 23, 9, 9], Package::CATEGORY_IDS);
        self::assertSame([6, 6, 6, 6, 6, 6], Package::CONTINUING_COUNTS);
        self::assertSame([6, 6, 6, 6, 6, 6], Package::PRACTICE_SOURCE_INDICES);
        self::assertSame(['B1', 'B1', 'B1', 'B2', 'A2', 'A2'], Package::LEVELS);
        self::assertSame(['B1-B2', 'B1-B2', 'B1-B2', 'B2-C1', 'A2-B1', 'A2-B1'], Package::DISPLAY_LEVELS);
        foreach ([$before['targets'], $patch['targets'], $master['lessons'], $contract['targets']] as $rows) {
            self::assertSame(Package::OWNERS, array_column($rows, 'identity'));
        }
        $native = new TheoryNativeContentRelease(Package::profile());
        $continuing = $added = $transitions = $links = 0;
        foreach ($master['lessons'] as $i => $lesson) {
            $old = $before['targets'][$i]['before']; $target = $patch['targets'][$i];
            self::assertSame(['id' => Package::PAGE_IDS[$i], 'page_category_id' => Package::CATEGORY_IDS[$i]], $target['page_binding']);
            self::assertSame(Package::ANCESTRIES[$i], $target['ancestry']);
            self::assertSame(Package::RENDERED_TITLES[$i], $lesson['preserved_h1']);
            self::assertSame($old['page']['title'], $lesson['raw_title']);
            self::assertSame(Package::DISPLAY_LEVELS[$i], $lesson['display_level']);
            self::assertSame('uk', $lesson['locale']);
            self::assertSame($target['after'], Package::definition($lesson, $old));
            self::assertSame($target['after'], json_decode(file_get_contents($this->root().'/'.$target['path']), true, flags: JSON_THROW_ON_ERROR));
            $protected = $target['after']; $protected['page']['blocks'] = $old['page']['blocks'];
            foreach (['subtitle_html', 'subtitle_text'] as $field) { $protected['page'][$field] = $old['page'][$field]; }
            self::assertSame($old, $protected, 'No implicit owner/title/category/locale/tag transition.');
            self::assertSame(1, preg_match('~<strong>\s*(.*?)\s*</strong>~is', $target['after']['page']['subtitle_html'], $strong));
            self::assertSame(Package::RENDERED_TITLES[$i], html_entity_decode(strip_tags($strong[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            self::assertCount(7, $lesson['blocks']);
            self::assertCount(1, array_filter($lesson['blocks'], static fn ($b) => $b['type'] === 'practice-set'));
            $projected = $native->projectedBefore($old);
            foreach ($old['page']['blocks'] as $slot => $source) {
                ++$continuing; $after = $target['after']['page']['blocks'][$slot];
                self::assertSame($slot, $lesson['blocks'][$slot]['existing_source_index']);
                self::assertSame(self::LEVELS[$i][$slot], $source['level'] ?? null);
                self::assertSame(self::LEVELS[$i][$slot], $after['level'] ?? null);
                self::assertSame($source['column'], $after['column']);
                self::assertSame(Digest::uuid($target['identity'], $source, $slot + 1), Digest::uuid($target['identity'], $after, $slot + 1));
                $metadata = $projected['page']['blocks'][$slot]; unset($metadata['body'], $after['body']);
                self::assertSame($metadata, $after, 'Only reviewed type/heading transitions may change old metadata.');
                if (in_array($slot, [1, 2], true)) {
                    ++$transitions; self::assertSame('box', $source['type']);
                    self::assertSame($slot === 1 || $i >= 4 ? 'usage-panels' : 'comparison-table', $after['type']);
                    self::assertNull($after['level'] ?? null); self::assertSame('left', $after['column']);
                } else { self::assertSame($source['type'], $after['type']); }
            }
            self::assertSame('right', $lesson['blocks'][3]['column']); self::assertSame('mistakes-grid', $lesson['blocks'][3]['type']);
            self::assertSame('right', $lesson['blocks'][4]['column']); self::assertSame('summary-list', $lesson['blocks'][4]['type']);
            $practice = $lesson['blocks'][6]; ++$added;
            self::assertNull($practice['existing_source_index']); self::assertSame('left', $practice['column']);
            self::assertSame(Package::LEVELS[$i], $practice['level']);
            $oldNav = json_decode($old['page']['blocks'][5]['body'], true, flags: JSON_THROW_ON_ERROR)['items'];
            $newNav = $lesson['blocks'][5]['data']['items']; self::assertCount($i < 4 ? 8 : 6, $newNav);
            self::assertCount(1, array_filter($newNav, static fn ($item) => $item['current'] === true));
            foreach ($newNav as $j => $link) {
                ++$links; self::assertSame($oldNav[$j]['label'], $link['label']); self::assertSame($oldNav[$j]['current'], $link['current']);
                self::assertMatchesRegularExpression('~\A/theory/'.($i < 4 ? 'sentence-transformations' : 'common-mistakes').'/[a-z0-9-]+\z~', $link['url']);
            }
            foreach ($target['after']['page']['blocks'] as $block) {
                self::assertLessThanOrEqual(65535, strlen($block['body'])); self::assertNotSame('box', $block['type']);
            }
            foreach ($lesson['blocks'] as $block) { $this->assertLearningData($block['data']); }
        }
        self::assertSame(36, $continuing); self::assertSame(6, $added); self::assertSame(12, $transitions); self::assertSame(44, $links);
        foreach (Package::profile()['artifacts'] as $path => $pin) {
            self::assertMatchesRegularExpression('/\A[a-f0-9]{64}\z/', $pin); self::assertSame($pin, hash_file('sha256', $this->root().'/'.$path));
        }
    }

    public function test_thirty_six_own_tasks_use_supported_visible_fields_plain_feedback_and_collision_free_keys(): void
    {
        [, , $master, $contract] = $this->release(); $ids = []; $full = 0;
        foreach ($master['lessons'] as $i => $lesson) {
            $data = $this->practice($master, $i);
            self::assertSame([], $data['rephrase']); self::assertArrayNotHasKey('intro', $data);
            foreach (['select_intro', 'choice_intro', 'input_intro'] as $field) {
                self::assertNotSame('', trim(strip_tags($data[$field]))); self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $data[$field]);
            }
            self::assertDoesNotMatchRegularExpression('/поліглот/iu', implode(' ', array_intersect_key($data, array_flip(['title', 'select_title', 'choice_title', 'input_title']))));
            self::assertSame('theory_links', $data['linked_practice']['source']); self::assertSame(['4'], $data['linked_practice']['question_types']);
            self::assertSame($contract['targets'][$i]['bank_seeders'], $data['linked_practice']['seeder_classes']);
            self::assertSame(Package::LEVELS[$i], $contract['targets'][$i]['level']);
            foreach (['selects', 'choices', 'inputs'] as $group) {
                self::assertCount(2, $data[$group]);
                foreach ($data[$group] as $index => $task) {
                    $ids[] = $task['id']; self::assertMatchesRegularExpression('/\Am81-[a-z0-9-]+\z/', $task['id']);
                    self::assertSame(['selects' => 1, 'choices' => 3, 'inputs' => 5][$group] + $index, $task['source_index']);
                    if ($group === 'selects') { self::assertArrayNotHasKey('prompt', $task); }
                    self::assertContains($task['answer'], $task['accepted']);
                    $keys = array_map(Package::comparisonKey(...), $task['accepted']);
                    self::assertNotContains('', $keys); self::assertCount(count($keys), array_unique($keys));
                    foreach (['hint', 'placeholder', 'strict', 'punctuation_sensitive', 'm38_semantic_checks'] as $field) { self::assertArrayNotHasKey($field, $task); }
                    $visible = $group === 'inputs' ? $task['after'] : $task['label'];
                    self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', strip_tags($visible));
                    self::assertMatchesRegularExpression('/<p lang="en">[^<]*[A-Za-z]{2,}/u', $visible, 'The EN source/stimulus is in a supported visible field.');
                    $options = $group === 'inputs' ? [] : $task['options']; $optionKeys = array_map(Package::comparisonKey(...), $options);
                    self::assertCount(count($optionKeys), array_unique($optionKeys)); self::assertNotContains('', $optionKeys);
                    foreach ([...$task['accepted'], ...$options] as $option) { self::assertArrayHasKey(mb_strtolower(trim($option)), $task['feedback']); }
                    foreach ($task['feedback'] as $key => $text) {
                        self::assertSame(mb_strtolower(trim($key)), $key); self::assertNotSame('', trim($text));
                        self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $text);
                        self::assertDoesNotMatchRegularExpression('/<(?:\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);|\{a\d+\}/iu', $text);
                    }
                }
            }
            foreach ($data['inputs'] as $task) {
                ++$full; $chunks = array_map('trim', explode('/', $task['before']));
                self::assertStringNotContainsString(Package::comparisonKey($task['answer']), Package::comparisonKey(strip_tags($task['after'])), 'The visible instruction is not a ready-answer hint.');
                self::assertGreaterThanOrEqual(2, count($chunks)); self::assertMatchesRegularExpression('/\A[A-Z].*[.!?]\z/', $task['answer']);
                foreach ($chunks as $chunk) { self::assertGreaterThanOrEqual(1, count(preg_split('/\s+/u', $chunk))); self::assertLessThanOrEqual(3, count(preg_split('/\s+/u', $chunk))); }
                self::assertNotSame(Package::comparisonKey($task['answer']), Package::comparisonKey(implode(' ', $chunks)));
                $words = static function (string $text): array { $out = array_count_values(preg_split('/\s+/u', Package::comparisonKey($text), flags: PREG_SPLIT_NO_EMPTY)); ksort($out); return $out; };
                self::assertSame($words($task['answer']), $words(implode(' ', $chunks)), 'Exact canonical token multiset, including repeated instances.');
            }
        }
        self::assertCount(36, $ids); self::assertCount(36, array_unique($ids)); self::assertSame(12, $full);
    }

    public function test_reviewed_facts_aliases_bilingual_transformation_rows_and_finite_detail_owners_survive(): void
    {
        [, , $master] = $this->release();
        foreach (self::INPUTS as $i => $answers) { self::assertSame($answers, array_column($this->practice($master, $i)['inputs'], 'answer')); }
        self::assertSame([1, 1, 1, 1, 0, 0], array_map(static fn ($lesson) => count($lesson['details']), $master['lessons']));
        foreach (array_slice($master['lessons'], 0, 4) as $i => $lesson) {
            $table = $lesson['blocks'][2]['data']; self::assertCount(4, $table['rows']);
            foreach ($table['rows'] as $row) {
                self::assertCount(3, $row['cells']);
                foreach ([0, 1] as $side) {
                    self::assertMatchesRegularExpression('/<p lang="en">[^<]*[A-Za-z]{2,}/u', $row['cells'][$side]);
                    self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $row['cells'][$side], 'Both source and target have their own UK translation.');
                }
            }
            $detail = $lesson['details'][0]; self::assertSame($lesson['blocks'][1]['key'], $detail['owner_block_key']);
            self::assertSame(['/sections/0', '/sections/2', '/sections/0', '/sections/0'][$i], $detail['owner_pointer']);
        }
        [$a, $b, $c, $d, $e, $f] = array_map(fn ($i) => $this->practice($master, $i), range(0, 5));
        self::assertSame(['hurry', 'had studied'], array_column($a['selects'], 'answer'));
        self::assertSame(['which', 'because of'], array_column($b['selects'], 'answer'));
        self::assertSame(['pieces of advice', 'any'], array_column($c['selects'], 'answer'));
        self::assertSame(['did I understand', 'does call'], array_column($d['selects'], 'answer'));
        self::assertSame(['an', 'Без артикля'], array_column($e['selects'], 'answer'));
        self::assertSame(['are you', 'is not'], array_column($f['selects'], 'answer'));
        self::assertContains('I would rather you did not call after ten tonight.', $a['inputs'][0]['accepted']);
        self::assertContains('The office that I visited was empty.', $b['inputs'][0]['accepted']);
        self::assertContains('In spite of being tired, Danylo finished the report.', $b['inputs'][1]['accepted']);
        self::assertContains('She gave me just one useful piece of advice about packing.', $c['inputs'][1]['accepted']);
        self::assertContains('She gave me only one useful piece of advice about packing.', $c['inputs'][1]['accepted']);
        self::assertContains('It was Tom that broke the printer, not me.', $d['inputs'][0]['accepted']);
        self::assertContains("I love coffee, but I don't drink it at night.", $e['inputs'][1]['accepted']);
        self::assertContains("He doesn't like spicy food.", $f['inputs'][1]['accepted']);
        self::assertStringContainsString('не виключає інших перешкод', $a['choices'][0]['answer']);
        self::assertStringContainsString('Марта', $b['choices'][0]['label']); self::assertStringContainsString('Олена', $b['choices'][0]['label']);
        self::assertStringContainsString('She gave me some useful advice.', $c['choices'][0]['label']);
        self::assertStringContainsString('невідомий факт', $c['choices'][0]['answer']);
        self::assertStringContainsString('Only Tom', $d['choices'][0]['label']);
        self::assertStringContainsString('Залишити без змін', $e['choices'][0]['answer']);
        self::assertStringContainsString('Залишити без змін', $f['choices'][0]['answer']);
        self::assertStringContainsString('Do you know who called?', $f['choices'][1]['label']);
    }

    public function test_foreign_stale_hidden_duplicate_and_incomplete_packages_fail_closed(): void
    {
        [$before, $patch, $master, $contract] = $this->release();
        $mutations = [
            'missing-owner' => static function (&$b, &$p, &$m, &$c): void { array_pop($m['lessons']); },
            'foreign-owner' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['identity'] = 'Foreign\\Owner'; },
            'foreign-locale' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['locale'] = 'en'; },
            'foreign-category' => static function (&$b, &$p, &$m, &$c): void { $p['targets'][2]['page_binding']['page_category_id'] = 22; },
            'range-as-filter' => static function (&$b, &$p, &$m, &$c): void { $c['targets'][0]['level'] = 'B1-B2'; },
            'unknown-filter' => static function (&$b, &$p, &$m, &$c): void { $c['targets'][1]['level'] = null; },
            'foreign-bank' => static function (&$b, &$p, &$m, &$c): void { $c['targets'][2]['bank_seeders'] = ['Foreign\\Bank']; },
            'right-mistakes-moved' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['blocks'][3]['column'] = 'left'; },
            'null-box-level-guessed' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][3]['blocks'][1]['level'] = 'B2'; },
            'unapproved-type-transition' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][4]['blocks'][2]['type'] = 'comparison-table'; },
            'missing-task' => static function (&$b, &$p, &$m, &$c): void { array_pop($m['lessons'][0]['blocks'][6]['data']['inputs']); },
            'missing-id' => static function (&$b, &$p, &$m, &$c): void { unset($m['lessons'][1]['blocks'][6]['data']['selects'][0]['id']); },
            'unsupported-generic-intro' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][6]['data']['intro'] = 'Не покладайся на невідображуваний критерій.'; },
            'unsupported-select-prompt' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][6]['data']['selects'][0]['prompt'] = 'Критерій у непідтриманому полі.'; },
            'hidden-prompt-only' => static function (&$b, &$p, &$m, &$c): void { $t = &$m['lessons'][2]['blocks'][6]['data']['selects'][0]; $t['label'] = ''; $t['prompt'] = 'Критерій лише тут.'; },
            'hidden-full-context' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['blocks'][6]['data']['inputs'][0]['after'] = ''; },
            'ready-answer-hint' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][6]['data']['inputs'][0]['hint'] = self::INPUTS[0][0]; },
            'empty-alias' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][6]['data']['inputs'][0]['accepted'][] = ''; },
            'normalized-option-collision' => static function (&$b, &$p, &$m, &$c): void { $t = &$m['lessons'][0]['blocks'][6]['data']['selects'][0]; $t['options'][] = 'HURRY!'; $t['feedback']['hurry!'] = 'Повторений нормалізований варіант.'; },
            'html-feedback' => static function (&$b, &$p, &$m, &$c): void { $t = &$m['lessons'][1]['blocks'][6]['data']['inputs'][0]; $t['feedback'][mb_strtolower(trim($t['answer']))] = '<b>Правильно.</b>'; },
            'visual-payload' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][1]['data']['stylesheet'] = 'm81.css'; },
            'duplicate-practice' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][] = $m['lessons'][0]['blocks'][6]; },
            'stale-body' => static function (&$b, &$p, &$m, &$c): void { $p['targets'][0]['after']['page']['blocks'][0]['body'] .= "\n"; },
        ];
        foreach ($mutations as $case => $change) {
            $b = $before; $p = $patch; $m = $master; $c = $contract; $change($b, $p, $m, $c);
            $this->refuses(static function () use ($b, $p, $m, $c, $case): void {
                if (!in_array($case, ['stale-body', 'foreign-category'], true)) {
                    foreach ($m['lessons'] as $i => $lesson) { $p['targets'][$i]['after'] = Package::definition($lesson, $b['targets'][$i]['before']); }
                }
                Package::validate($b, $p, $m, $c);
            }, $case);
        }
        $profile = Package::profile(); $first = array_key_first($profile['artifacts']); $profile['artifacts'][$first] = str_repeat('0', 64);
        $this->refuses(fn () => (new TheoryNativeContentRelease($profile))->load($this->root()), 'stale frozen byte pin');
        self::assertSame([$before, $patch, $master, $contract], array_slice($this->release(), 0, 4));
    }
}
