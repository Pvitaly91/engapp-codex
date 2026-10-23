<?php

namespace Tests\Feature;

use App\Support\M26DetailPackage as Digest;
use App\Support\M80NegativesTransformations as Package;
use App\Support\TheoryNativeContentRelease;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Frozen content contract only: no Laravel bootstrap, working SQL, HTTP or browser storage. */
class M80NegativesTransformationsPackageTest extends TestCase
{
    private const LEVELS = [
        ['A1-B1', 'A1-B1', 'A2-B2', 'A2-B2', 'A1-B2'],
        ['A1-B1', 'A1-B1', 'A2-B2', 'A2-B2', 'A1-B2'],
        ['B1', null, null, 'B1', 'B1', null],
        ['B1', null, null, 'B1', 'B1', null],
        ['B1', null, null, 'B1', 'B1', null],
        ['B1', null, null, 'B1', 'B1', null],
    ];

    /** Literal independent teaching expectations, not copied from AFTER during a test. */
    private const ABC_INPUTS = [
        ['He promised not to be late.', 'Marko did not call us yesterday.'],
        ['We did not meet anyone yesterday.', 'Nobody called us after the meeting.'],
        ['Oleh has been learning French for three years.', 'By the time we arrived, the film had started.'],
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

    public function test_six_exact_owners_keep_thirty_four_blocks_and_append_six_native_practices(): void
    {
        [$before, $patch, $master, $contract] = $this->release();
        self::assertSame('b56f29df4b3409edc1ad6cfb34b7fedd901e893d', Package::BASE_SHA);
        self::assertSame('1.0.0', $master['version']);
        self::assertSame([331, 332, 152, 148, 149, 151], Package::PAGE_IDS);
        self::assertSame([21, 21, 23, 23, 23, 23], Package::CATEGORY_IDS);
        self::assertSame([5, 5, 6, 6, 6, 6], Package::CONTINUING_COUNTS);
        self::assertSame([5, 5, 6, 6, 6, 6], Package::PRACTICE_SOURCE_INDICES);
        self::assertSame(['A2', 'B1', 'B1', 'B1', 'B1', 'B1'], Package::LEVELS);
        foreach ([$before['targets'], $patch['targets'], $master['lessons'], $contract['targets']] as $rows) {
            self::assertSame(Package::OWNERS, array_column($rows, 'identity'));
        }
        $native = new TheoryNativeContentRelease(Package::profile());
        $continuing = $added = $nullBoxes = $links = 0;
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
            self::assertSame($old, $protected, 'No implicit title/category/owner/locale/tag transition.');
            self::assertCount(Package::CONTINUING_COUNTS[$i] + 1, $lesson['blocks']);
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
                self::assertSame($metadata, $after);
                if ($i >= 2 && in_array($slot, [1, 2], true)) {
                    ++$nullBoxes; self::assertSame('box', $source['type']);
                    self::assertSame($slot === 1 ? 'usage-panels' : 'comparison-table', $after['type']);
                    self::assertNull($after['level'] ?? null); self::assertSame('left', $after['column']);
                } else { self::assertSame($source['type'], $after['type']); }
            }
            self::assertSame('right', $lesson['blocks'][3]['column']); self::assertSame('mistakes-grid', $lesson['blocks'][3]['type']);
            self::assertSame('right', $lesson['blocks'][4]['column']); self::assertSame('summary-list', $lesson['blocks'][4]['type']);
            $practice = $lesson['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]; ++$added;
            self::assertNull($practice['existing_source_index']); self::assertSame('left', $practice['column']);
            self::assertSame(Package::LEVELS[$i], $practice['level']);
            if ($i >= 2) {
                $oldNav = json_decode($old['page']['blocks'][5]['body'], true, flags: JSON_THROW_ON_ERROR)['items'];
                $newNav = $lesson['blocks'][5]['data']['items']; self::assertCount(8, $newNav);
                foreach ($newNav as $j => $link) {
                    ++$links; self::assertSame($oldNav[$j]['label'], $link['label']); self::assertSame($oldNav[$j]['current'], $link['current']);
                    self::assertMatchesRegularExpression('~\A/theory/sentence-transformations/[a-z0-9-]+\z~', $link['url']);
                }
            }
            foreach ($target['after']['page']['blocks'] as $block) {
                self::assertLessThanOrEqual(65535, strlen($block['body']));
                self::assertNotSame('box', $block['type']);
            }
            foreach ($lesson['blocks'] as $block) { $this->assertLearningData($block['data']); }
        }
        self::assertSame(34, $continuing); self::assertSame(6, $added); self::assertSame(8, $nullBoxes); self::assertSame(32, $links);
        foreach (Package::profile()['artifacts'] as $path => $pin) {
            self::assertMatchesRegularExpression('/\A[a-f0-9]{64}\z/', $pin);
            self::assertSame($pin, hash_file('sha256', $this->root().'/'.$path));
        }
    }

    public function test_thirty_six_own_tasks_have_visible_criteria_plain_feedback_and_no_normalized_collisions(): void
    {
        [, , $master, $contract] = $this->release(); $ids = []; $full = 0;
        foreach ($master['lessons'] as $i => $lesson) {
            $data = $this->practice($master, $i);
            self::assertSame([], $data['rephrase']);
            self::assertDoesNotMatchRegularExpression('/поліглот/iu', implode(' ', array_intersect_key($data, array_flip(['title', 'select_title', 'choice_title', 'input_title']))));
            self::assertSame('theory_links', $data['linked_practice']['source']); self::assertSame(['4'], $data['linked_practice']['question_types']);
            self::assertSame($contract['targets'][$i]['bank_seeders'], $data['linked_practice']['seeder_classes']);
            foreach (['selects', 'choices', 'inputs'] as $group) {
                self::assertCount(2, $data[$group]);
                foreach ($data[$group] as $index => $task) {
                    $ids[] = $task['id']; self::assertMatchesRegularExpression('/\Am80-[a-z0-9-]+\z/', $task['id']);
                    self::assertSame(['selects' => 1, 'choices' => 3, 'inputs' => 5][$group] + $index, $task['source_index']);
                    self::assertContains($task['answer'], $task['accepted']);
                    $keys = array_map(Package::comparisonKey(...), $task['accepted']);
                    self::assertNotContains('', $keys); self::assertCount(count($keys), array_unique($keys));
                    foreach (['hint', 'placeholder', 'strict', 'punctuation_sensitive', 'm38_semantic_checks'] as $field) { self::assertArrayNotHasKey($field, $task); }
                    $visible = $group === 'inputs' ? $task['after'] : $task['label'];
                    self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', strip_tags($visible));
                    if ($i >= 2) { self::assertMatchesRegularExpression('/<p lang="en">[^<]*[A-Za-z]{2,}/u', $visible, 'An EN source/stimulus is really in the rendered label/after field.'); }
                    $options = $group === 'inputs' ? [] : $task['options'];
                    $optionKeys = array_map(Package::comparisonKey(...), $options);
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
                self::assertStringNotContainsString(Package::comparisonKey($task['answer']), Package::comparisonKey(strip_tags($task['after'])), 'The instruction is not a ready-answer hint.');
                self::assertGreaterThanOrEqual(2, count($chunks)); self::assertMatchesRegularExpression('/\A[A-Z].*[.!?]\z/', $task['answer']);
                foreach ($chunks as $chunk) { self::assertGreaterThanOrEqual(1, count(preg_split('/\s+/u', $chunk))); self::assertLessThanOrEqual(3, count(preg_split('/\s+/u', $chunk))); }
                self::assertNotSame(Package::comparisonKey($task['answer']), Package::comparisonKey(implode(' ', $chunks)));
                $words = static function (string $text): array { $result = array_count_values(preg_split('/\s+/u', Package::comparisonKey($text), flags: PREG_SPLIT_NO_EMPTY)); ksort($result); return $result; };
                self::assertSame($words($task['answer']), $words(implode(' ', $chunks)), 'Exact canonical token multiset.');
            }
        }
        self::assertCount(36, $ids); self::assertCount(36, array_unique($ids)); self::assertSame(12, $full);
    }

    public function test_literal_abc_facts_scope_aliases_and_four_tense_rows_survive(): void
    {
        [, , $master] = $this->release();
        foreach (self::ABC_INPUTS as $i => $answers) { self::assertSame($answers, array_column($this->practice($master, $i)['inputs'], 'answer')); }
        $a = $this->practice($master, 0); $b = $this->practice($master, 1); $c = $this->practice($master, 2);
        self::assertSame(['is not', 'does not work'], array_column($a['selects'], 'answer'));
        self::assertSame(['knows', 'anyone'], array_column($b['selects'], 'answer'));
        self::assertSame(['had started', 'since'], array_column($c['selects'], 'answer'));
        self::assertContains("Marko didn't phone us yesterday.", $a['inputs'][1]['accepted']);
        self::assertContains('We did not meet anybody yesterday.', $b['inputs'][0]['accepted']);
        self::assertContains('No one phoned us after the meeting.', $b['inputs'][1]['accepted']);
        self::assertContains('Oleh has been studying French for three years.', $c['inputs'][0]['accepted']);
        self::assertContains('By the time we arrived, the film had already started.', $c['inputs'][1]['accepted']);
        self::assertStringContainsString('1 вересня 2024', $c['inputs'][0]['after']);
        self::assertStringContainsString('1 вересня 2021', $c['inputs'][0]['after']);
        self::assertStringContainsString('Present Perfect Continuous', $c['inputs'][0]['after']);
        self::assertStringContainsString('The film started before we arrived.', $c['inputs'][1]['after']);
        $table = $master['lessons'][2]['blocks'][2]['data']; self::assertCount(4, $table['rows']);
        self::assertStringContainsString('The film started before we arrived.', $table['rows'][2]['cells'][0]);
        self::assertStringContainsString('By the time we arrived, the film had started.', $table['rows'][2]['cells'][1]);
        foreach ($table['rows'] as $row) { self::assertCount(3, $row['cells']); foreach ([0, 1] as $side) { self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $row['cells'][$side]); } }
        self::assertCount(0, $master['lessons'][0]['details']); self::assertCount(1, $master['lessons'][1]['details']); self::assertCount(1, $master['lessons'][2]['details']);
        self::assertStringContainsString('to not', json_encode($master['lessons'][0]['blocks'][2]['data'], JSON_UNESCAPED_UNICODE));
        self::assertSame('He promised not be late.', $master['lessons'][0]['blocks'][3]['data']['items'][2]['wrong_en']);
        self::assertStringContainsString('neither', mb_strtolower(json_encode($master['lessons'][1]['blocks'][2]['data'], JSON_UNESCAPED_UNICODE)));
    }

    public function test_foreign_stale_hidden_and_incomplete_packages_fail_closed(): void
    {
        [$before, $patch, $master, $contract] = $this->release();
        $mutations = [
            'missing-owner' => static function (&$b, &$p, &$m, &$c): void { array_pop($m['lessons']); },
            'foreign-owner' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['identity'] = 'Foreign\\Owner'; },
            'foreign-locale' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['locale'] = 'en'; },
            'foreign-category' => static function (&$b, &$p, &$m, &$c): void { $p['targets'][2]['page_binding']['page_category_id'] = 22; },
            'range-as-filter' => static function (&$b, &$p, &$m, &$c): void { $c['targets'][0]['level'] = 'A1-B1'; },
            'unknown-filter' => static function (&$b, &$p, &$m, &$c): void { $c['targets'][1]['level'] = null; },
            'foreign-bank' => static function (&$b, &$p, &$m, &$c): void { $c['targets'][2]['bank_seeders'] = ['Foreign\\Bank']; },
            'right-mistakes-moved' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['blocks'][3]['column'] = 'left'; },
            'null-box-level-guessed' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][3]['blocks'][1]['level'] = 'B1'; },
            'missing-task' => static function (&$b, &$p, &$m, &$c): void { array_pop($m['lessons'][0]['blocks'][5]['data']['inputs']); },
            'missing-id' => static function (&$b, &$p, &$m, &$c): void { unset($m['lessons'][1]['blocks'][5]['data']['selects'][0]['id']); },
            'hidden-prompt-only' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['blocks'][6]['data']['selects'][0]['label'] = ''; },
            'hidden-full-context' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['blocks'][6]['data']['inputs'][0]['after'] = ''; },
            'ready-answer-hint' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][5]['data']['inputs'][0]['hint'] = 'He promised not to be late.'; },
            'empty-alias' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][5]['data']['inputs'][0]['accepted'][] = ''; },
            'normalized-option-collision' => static function (&$b, &$p, &$m, &$c): void { $t = &$m['lessons'][0]['blocks'][5]['data']['selects'][0]; $t['options'][] = 'IS NOT!'; $t['feedback']['is not!'] = 'Повторений нормалізований варіант.'; },
            'html-feedback' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][5]['data']['inputs'][0]['feedback']['we did not meet anyone yesterday.'] = '<b>Правильно.</b>'; },
            'visual-payload' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][1]['data']['stylesheet'] = 'm80.css'; },
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
