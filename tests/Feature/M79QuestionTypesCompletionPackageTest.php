<?php

namespace Tests\Feature;

use App\Support\AcceptedAnswerVariants;
use App\Support\M26DetailPackage as Digest;
use App\Support\M79QuestionTypesCompletion as Package;
use App\Support\TheoryNativeContentRelease;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Frozen sources only: no application bootstrap, working .env, SQL, HTTP or browser storage. */
class M79QuestionTypesCompletionPackageTest extends TestCase
{
    private const LEVELS = [
        ['A2', 'A2', 'A2', 'A2', 'A2', 'B1', 'B1', 'A2', 'B1', 'B1', 'A2', 'A2', 'A2'],
        ['B1', 'B1', 'B1', 'B1', 'B1', 'B1', 'B1', 'B2', 'B2', 'B1', 'B1', 'B1', 'B1', 'B1'],
        ['B1', 'B1', 'B1', 'B1', 'B1', 'B2', 'B2', 'B2', 'B1', 'B1', 'B1', 'B1', 'B1'],
        ['A2–B1', 'A2', 'A2–B1', 'A2', 'A2', 'A2'],
        ['B1', 'B1', 'B1', 'B1', 'B1', null],
        ['B1', 'B1', 'B1', 'B1', 'B1', null],
    ];

    private const INPUTS = [
        ['Does she like cats or dogs?', 'Are you from Spain or Italy?', 'Will they come today or tomorrow?', 'Is it big or small?'],
        ["isn't she", 'do you', "can't he", 'will they', "Marta works here, doesn't she?", "There isn't a bus, is there?"],
        ["Don't you know?", "Isn't she coming?", "Can't they help?", "Didn't you see it?"],
        ['How many children live here?', 'Who is waiting outside?'],
        ["I don't know what Lina does.", 'Do you know if the door is locked?'],
        ['Could you tell me where you work?', 'I was wondering if the room is available tonight.'],
    ];

    private function root(): string { return dirname(__DIR__, 2); }

    private function release(): array
    {
        $release = Package::load($this->root());
        Package::validate($release[0], $release[1], $release[2], $release[3]);
        return $release;
    }

    private function practice(array $master, int $owner): array
    {
        return $master['lessons'][$owner]['blocks'][Package::PRACTICE_SOURCE_INDICES[$owner]]['data'];
    }

    private function refuses(callable $call, string $case): void
    {
        $caught = null;
        try { $call(); } catch (RuntimeException $exception) { $caught = $exception; }
        self::assertInstanceOf(RuntimeException::class, $caught, $case);
    }

    public function test_six_exact_owners_continue_fifty_eight_structured_blocks_and_append_only_three_practices(): void
    {
        [$before, $patch, $master, $contract] = $this->release();
        self::assertSame('1.0.1', $master['version']);
        self::assertSame('1941b5bc4976d9526145e32216f429b5bde81a6776718c894634a0e0ee9f532f',
            hash_file('sha256', $this->root().'/docs/content/m79-question-types-completion.v1.json'));
        self::assertSame('f33d1a0e2eff08e66044d9b746a46bc33f207bce', Package::BASE_SHA);
        self::assertSame([132, 137, 136, 138, 134, 135], Package::PAGE_IDS);
        self::assertSame([22, 22, 22, 22, 22, 22], Package::CATEGORY_IDS);
        self::assertSame([13, 14, 13, 6, 6, 6], Package::CONTINUING_COUNTS);
        self::assertSame([11, 12, 11, 6, 6, 6], Package::PRACTICE_SOURCE_INDICES);
        self::assertSame(['A2', 'B1', 'B1', 'A2', 'B1', 'B1'], Package::LEVELS);
        foreach ([$before['targets'], $patch['targets'], $master['lessons'], $contract['targets']] as $targets) {
            self::assertSame(Package::OWNERS, array_column($targets, 'identity'));
        }
        self::assertSame(Package::LEVELS, array_column($contract['targets'], 'level'));
        $primitive = new TheoryNativeContentRelease(Package::profile());
        $continuing = $appended = $blocks = 0;
        foreach ($patch['targets'] as $i => $target) {
            $old = $before['targets'][$i]['before']; $lesson = $master['lessons'][$i];
            self::assertSame(['id' => Package::PAGE_IDS[$i], 'page_category_id' => 22], $target['page_binding']);
            self::assertSame(['pytalni-rechennia-ta-zaperechennia', 'types-of-questions'], $target['ancestry']);
            self::assertSame('uk', $old['page']['locale']); self::assertSame('uk', $lesson['locale']);
            self::assertSame($old['page']['title'], $lesson['raw_title']);
            self::assertSame(Package::RENDERED_TITLES[$i], $lesson['preserved_h1']);
            self::assertSame(Package::DISPLAY_LEVELS[$i], $lesson['display_level']);
            self::assertSame(Package::LEVELS[$i], $lesson['technical_block_level']);
            self::assertSame($target['after'], Package::definition($lesson, $old));
            self::assertSame($target['after'], json_decode(file_get_contents($this->root().'/'.$target['path']), true, flags: JSON_THROW_ON_ERROR));
            $protected = $target['after']; $protected['page']['blocks'] = $old['page']['blocks'];
            foreach (['subtitle_html', 'subtitle_text'] as $field) { $protected['page'][$field] = $old['page'][$field]; }
            self::assertSame($old, $protected, 'No implicit title, locale, category, tags, seeder or slug transition.');
            self::assertCount(Package::CONTINUING_COUNTS[$i] + ($i >= 3 ? 1 : 0), $lesson['blocks']);
            self::assertCount(1, array_filter($lesson['blocks'], static fn ($block) => $block['type'] === 'practice-set'));
            $projected = $primitive->projectedBefore($old);
            foreach ($old['page']['blocks'] as $slot => $source) {
                ++$continuing; $new = $target['after']['page']['blocks'][$slot];
                self::assertSame($slot, $lesson['blocks'][$slot]['existing_source_index']);
                self::assertSame(self::LEVELS[$i][$slot], $source['level'] ?? null);
                self::assertSame(self::LEVELS[$i][$slot], $new['level'] ?? null);
                self::assertSame($source['type'], $new['type']); self::assertSame($source['column'], $new['column']);
                self::assertSame(Digest::uuid($target['identity'], $source, $slot + 1), Digest::uuid($target['identity'], $new, $slot + 1));
                $metadata = $projected['page']['blocks'][$slot]; unset($metadata['body'], $new['body']);
                self::assertSame($metadata, $new, 'Continuing metadata remain exact.');
                if ($i < 3) {
                    self::assertStringStartsWith('Database\\Seeders\\Page_v2\\', $source['seeder']);
                    foreach (['seeder', 'uuid_key', 'tags', 'inherit_base_tags'] as $field) {
                        self::assertSame(array_key_exists($field, $source), array_key_exists($field, $new));
                        if (array_key_exists($field, $source)) { self::assertSame($source[$field], $new[$field]); }
                    }
                }
            }
            if ($i >= 3) {
                ++$appended; self::assertNull($lesson['blocks'][6]['existing_source_index']);
                self::assertSame(['header', 'left', 'left', 'right', 'right', 'footer', 'left'], array_column($lesson['blocks'], 'column'));
                self::assertSame(['hero', 'forms-grid', 'usage-panels', 'mistakes-grid', 'summary-list', 'navigation-chips', 'practice-set'], array_column($lesson['blocks'], 'type'));
                self::assertSame(Package::LEVELS[$i], $target['after']['page']['blocks'][6]['level']);
            } else {
                self::assertSame([], Package::profile()['bindings'][$i]['appended_blocks']);
            }
            foreach ($target['after']['page']['blocks'] as $source) {
                ++$blocks; self::assertLessThanOrEqual(65535, strlen($source['body']));
                self::assertContains($source['type'], ['hero', 'forms-grid', 'usage-panels', 'comparison-table', 'mistakes-grid', 'summary-list', 'navigation-chips', 'practice-set']);
            }
        }
        self::assertSame(58, $continuing); self::assertSame(3, $appended); self::assertSame(61, $blocks);
        foreach (Package::profile()['artifacts'] as $path => $pin) {
            self::assertMatchesRegularExpression('/\A[a-f0-9]{64}\z/', $pin);
            self::assertSame($pin, hash_file('sha256', $this->root().'/'.$path));
        }
    }

    public function test_thirty_one_retained_functions_and_three_demos_are_not_replaced_by_twenty_six_new_tasks(): void
    {
        [$before, , $master, $contract] = $this->release(); $graded = $retained = $new = $demos = 0; $ids = [];
        foreach ($master['lessons'] as $i => $lesson) {
            $data = $this->practice($master, $i); $own = 0;
            $previous = $i < 3 ? json_decode($before['targets'][$i]['before']['page']['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['body'], true, flags: JSON_THROW_ON_ERROR) : [];
            foreach (['selects', 'choices', 'inputs', 'rephrase'] as $group) {
                self::assertGreaterThanOrEqual(count($previous[$group] ?? []), count($data[$group]));
                foreach ($data[$group] as $index => $task) {
                    $old = $previous[$group][$index] ?? null;
                    if (isset($task['example_original'])) {
                        ++$demos; self::assertIsArray($old); self::assertArrayHasKey('example_original', $old);
                        self::assertNotEmpty($task['example_original']); self::assertNotEmpty($task['example_target']);
                        foreach (['answer', 'accepted', 'id', 'source_index'] as $field) { self::assertArrayNotHasKey($field, $task); }
                        continue;
                    }
                    ++$graded; ++$own;
                    if (is_array($old)) {
                        ++$retained;
                        foreach (['id', 'source_index'] as $field) {
                            self::assertSame(array_key_exists($field, $old), array_key_exists($field, $task));
                            if (array_key_exists($field, $old)) { self::assertSame($old[$field], $task[$field]); }
                        }
                        self::assertContains($old['answer'], $task['accepted'], 'The useful old canonical function must survive.');
                    } else {
                        ++$new; self::assertMatchesRegularExpression('/\Am79-[a-z0-9-]+\z/', $task['id']);
                        $ids[] = $task['id'];
                    }
                    self::assertContains($task['answer'], $task['accepted']);
                    self::assertNotSame('', Package::comparisonKey($task['answer']));
                    foreach (['strict', 'punctuation_sensitive', 'm38_semantic_checks'] as $field) { self::assertArrayNotHasKey($field, $task); }
                    $options = in_array($group, ['selects', 'choices'], true)
                        ? ($task['options'] ?? ($group === 'selects' ? $data['options'] : ($data['choice_options'] ?? ['a', 'b']))) : [];
                    if ($options !== []) {
                        $keys = array_map(Package::comparisonKey(...), $options);
                        self::assertGreaterThanOrEqual(2, count($options)); self::assertNotContains('', $keys);
                        self::assertCount(count($keys), array_unique($keys));
                        foreach ($task['accepted'] as $answer) { self::assertContains($answer, $options); }
                    }
                    foreach ([...$task['accepted'], ...$options] as $value) {
                        self::assertArrayHasKey(mb_strtolower(trim($value)), $task['feedback']);
                    }
                    foreach ($task['feedback'] as $key => $text) {
                        self::assertSame(mb_strtolower(trim($key)), $key); self::assertIsString($text); self::assertNotSame('', trim($text));
                        self::assertDoesNotMatchRegularExpression('/<(?:\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);|\{a\d+\}/iu', $text);
                    }
                }
            }
            self::assertSame([12, 15, 12, 6, 6, 6][$i], $own);
            self::assertCount([3, 4, 3, 2, 2, 2][$i], $data['selects']); self::assertCount(2, $data['choices']);
            self::assertSame('theory_links', $data['linked_practice']['source']); self::assertSame(['4'], $data['linked_practice']['question_types']);
            self::assertSame($contract['targets'][$i]['bank_seeders'], $data['linked_practice']['seeder_classes']);
            self::assertDoesNotMatchRegularExpression('/поліглот/iu', implode(' ', array_intersect_key($data, array_flip(['title', 'select_title', 'choice_title', 'input_title', 'rephrase_title']))));
        }
        self::assertSame(57, $graded); self::assertSame(31, $retained); self::assertSame(26, $new); self::assertSame(3, $demos);
        self::assertCount(26, $ids); self::assertCount(26, array_unique($ids));
    }

    public function test_sixteen_full_inputs_have_shuffled_one_to_three_word_groups_and_four_tag_gaps_stay_partial(): void
    {
        [, , $master] = $this->release(); $full = $partial = 0;
        foreach ($master['lessons'] as $i => $lesson) {
            $data = $this->practice($master, $i); self::assertSame(self::INPUTS[$i], array_column($data['inputs'], 'answer'));
            foreach ($data['inputs'] as $index => $task) {
                self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $task['after']);
                if ($i === 1 && $index < 4) {
                    ++$partial; self::assertStringNotContainsString('/', $task['before']); self::assertMatchesRegularExpression('/,\s*$/', $task['before']);
                    self::assertDoesNotMatchRegularExpression('/[,?.!]$/', $task['answer']); self::assertArrayNotHasKey('id', $task); continue;
                }
                ++$full; self::assertMatchesRegularExpression('/\A[A-Z].*[.!?]\z/', $task['answer']);
                $chunks = array_map('trim', explode('/', $task['before'])); self::assertGreaterThanOrEqual(2, count($chunks));
                foreach ($chunks as $chunk) {
                    $words = preg_split('/\s+/u', $chunk, flags: PREG_SPLIT_NO_EMPTY);
                    self::assertGreaterThanOrEqual(1, count($words)); self::assertLessThanOrEqual(3, count($words));
                }
                self::assertNotSame(Package::comparisonKey($task['answer']), Package::comparisonKey(implode(' ', $chunks)));
                $words = static fn (string $value): array => preg_split('/\s+/u', Package::comparisonKey($value), flags: PREG_SPLIT_NO_EMPTY);
                $correct = array_count_values($words($task['answer'])); $bank = array_count_values($words(implode(' ', $chunks)));
                ksort($correct); ksort($bank); self::assertSame($correct, $bank, 'Repeated you/is/not remain distinct instances.');
            }
        }
        self::assertSame(16, $full); self::assertSame(4, $partial);
        self::assertContains('am I not', $this->practice($master, 1)['selects'][3]['accepted']);
        self::assertContains('I am right, am I not?', $this->practice($master, 1)['rephrase'][3]['accepted']);
        self::assertContains('Do you not know?', $this->practice($master, 2)['inputs'][0]['accepted']);
        self::assertContains('Do you know whether the door is locked?', $this->practice($master, 4)['inputs'][1]['accepted']);
        self::assertContains('I was wondering whether the room is free tonight.', $this->practice($master, 5)['inputs'][1]['accepted']);
        self::assertTrue(AcceptedAnswerVariants::matches("Don't you know?", 'Do you not know?'));
        self::assertFalse(AcceptedAnswerVariants::matches("Don't you know?", 'Do not you know?'));
        self::assertFalse(AcceptedAnswerVariants::matches("There isn't a bus, is there?", 'There is a bus, is there?'));
        self::assertFalse(AcceptedAnswerVariants::matches('I know what he does.', 'I know what he.'));
    }

    public function test_four_meaningful_details_have_exact_native_owners_and_all_forty_six_footer_labels_survive(): void
    {
        [$before, , $master] = $this->release(); $details = $links = 0; $detailKeys = [];
        foreach ($master['lessons'] as $i => $lesson) {
            self::assertCount([0, 1, 0, 1, 1, 1][$i], $lesson['details']); self::assertNotEmpty($lesson['point_detail_decisions']);
            $blocks = array_column($lesson['blocks'], null, 'key');
            foreach ($lesson['point_detail_decisions'] as $decision) {
                self::assertArrayHasKey($decision['block_key'], $blocks); self::assertNotEmpty($decision['reason']);
                self::assertContains($decision['decision'], ['visible_basic', $i === 1 ? 'meaningful_point_detail' : 'meaningful_detail']);
            }
            foreach ($lesson['details'] as $detail) {
                ++$details; $detailKeys[] = $detail['key'];
                self::assertArrayHasKey($detail['owner_block_key'], $blocks); self::assertSame('usage-panels', $detail['fragment']['type']);
                $pointer = explode('/', $detail['owner_pointer']); self::assertSame('sections', $pointer[1]);
                self::assertArrayHasKey((int) $pointer[2], $blocks[$detail['owner_block_key']]['data']['sections']);
                foreach ($detail['fragment']['data']['sections'] as $section) {
                    self::assertNotEmpty($section['description']); self::assertNotEmpty($section['examples']);
                    foreach ($section['examples'] as $example) { self::assertNotEmpty($example['en']); self::assertNotEmpty($example['ua']); }
                }
            }
            $slot = $i < 3 ? Package::CONTINUING_COUNTS[$i] - 1 : 5;
            $old = json_decode($before['targets'][$i]['before']['page']['blocks'][$slot]['body'], true, flags: JSON_THROW_ON_ERROR)['items'];
            $new = $lesson['blocks'][$slot]['data']['items']; self::assertCount([7, 7, 7, 9, 8, 8][$i], $new);
            foreach ($new as $index => $link) {
                ++$links; self::assertSame($old[$index]['label'], $link['label']); self::assertSame($old[$index]['current'], $link['current']);
                self::assertMatchesRegularExpression('~\A/theory/pytalni-rechennia-ta-zaperechennia/(?:question-forms|types-of-questions/[a-z0-9-]+)\z~', $link['url']);
            }
            foreach ($lesson['blocks'] as $block) {
                $walk = function (array $data) use (&$walk): void {
                    foreach ($data as $key => $value) {
                        if (is_array($value)) { $walk($value); continue; } if (!is_string($value)) { continue; }
                        self::assertNotContains(mb_strtolower((string) $key), ['style', 'class', 'css_class', 'stylesheet', 'view', 'native_view']);
                        self::assertDoesNotMatchRegularExpression('/\{a\d+\}|&lt;\/?(?:p|span|strong|em)\b|<\/?(?:script|style|iframe)\b|<[^>]+\s(?:class|style|on[a-z]+)\s*=/iu', $value);
                    }
                }; $walk($block['data']);
                foreach ($block['data']['sections'] ?? [] as $section) {
                    foreach ($section['examples'] ?? [] as $example) { self::assertNotEmpty($example['en']); self::assertNotEmpty($example['ua']); }
                }
            }
        }
        self::assertSame(4, $details); self::assertCount(4, array_unique($detailKeys)); self::assertSame(46, $links);
        self::assertSame(['m79-tags-same-polarity', 'subject-do-context', 'embedded-whether-positions', 'indirect-time-and-reporting'], $detailKeys);
        self::assertSame('Who contacted Anna?', $master['lessons'][3]['blocks'][2]['data']['sections'][2]['examples'][0]['en']);
        self::assertStringContainsString('Ні: це предмет у пасиві', $this->practice($master, 3)['choices'][1]['answer']);
        self::assertSame('does', $this->practice($master, 4)['selects'][0]['answer']);
        self::assertSame("didn't come", $this->practice($master, 4)['selects'][1]['answer']);
    }

    public function test_foreign_stale_incomplete_and_colliding_source_packages_fail_closed(): void
    {
        [$before, $patch, $master, $contract] = $this->release(); $original = [$before, $patch, $master, $contract];
        $mutations = [
            'missing-owner' => static function (&$b, &$p, &$m, &$c): void { array_pop($m['lessons']); },
            'foreign-owner' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['identity'] = 'Foreign\\Owner'; },
            'foreign-locale' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][3]['locale'] = 'pl'; },
            'foreign-category' => static function (&$b, &$p, &$m, &$c): void { $p['targets'][4]['page_binding']['page_category_id'] = 21; },
            'wrong-parent' => static function (&$b, &$p, &$m, &$c): void { $p['targets'][5]['ancestry'] = ['types-of-questions']; },
            'range-as-filter' => static function (&$b, &$p, &$m, &$c): void { $c['targets'][3]['level'] = 'A2–B1'; },
            'unknown-filter' => static function (&$b, &$p, &$m, &$c): void { $c['targets'][4]['level'] = null; },
            'legacy-page-v2-owner' => static function (&$b, &$p, &$m, &$c): void { $b['targets'][0]['before']['page']['blocks'][1]['seeder'] = Package::OWNERS[0]; },
            'source-uuid-key' => static function (&$b, &$p, &$m, &$c): void { $b['targets'][1]['before']['page']['blocks'][1]['uuid_key'] = 'foreign-key'; },
            'source-level' => static function (&$b, &$p, &$m, &$c): void { $b['targets'][2]['before']['page']['blocks'][1]['level'] = 'A1'; },
            'source-owner-category' => static function (&$b, &$p, &$m, &$c): void { $b['targets'][4]['before']['page']['category']['slug'] = 'foreign'; },
            'right-mistakes-moved' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][3]['blocks'][3]['column'] = 'left'; },
            'retained-task-lost' => static function (&$b, &$p, &$m, &$c): void { array_pop($m['lessons'][0]['blocks'][11]['data']['inputs']); },
            'retained-id-invented' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][12]['data']['inputs'][0]['id'] = 'm79-invented'; },
            'retained-source-index-invented' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['blocks'][11]['data']['selects'][0]['source_index'] = 1; },
            'demo-graded' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][11]['data']['rephrase'][0]['answer'] = 'Does she like tea?'; },
            'new-id-missing' => static function (&$b, &$p, &$m, &$c): void { unset($m['lessons'][4]['blocks'][6]['data']['selects'][0]['id']); },
            'empty-meaning-group' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][5]['blocks'][6]['data']['choices'] = []; },
            'duplicate-practice' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][3]['blocks'][] = $m['lessons'][3]['blocks'][6]; },
            'missing-detail-owner' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][4]['details'][0]['owner_pointer'] = '/sections/999'; },
            'duplicate-detail' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][5]['details'][] = $m['lessons'][5]['details'][0]; },
            'hidden-input-context' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][3]['blocks'][6]['data']['inputs'][0]['after'] = ''; },
            'empty-alias' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][4]['blocks'][6]['data']['inputs'][0]['accepted'][] = ''; },
            'html-feedback' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][5]['blocks'][6]['data']['selects'][1]['feedback']['you arrived'] = '<b>Правильно.</b>'; },
            'new-grader-flag' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][12]['data']['inputs'][5]['punctuation_sensitive'] = true; },
            'visual-payload' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][3]['blocks'][1]['data']['stylesheet'] = 'm79.css'; },
            'option-normalization-collision' => static function (&$b, &$p, &$m, &$c): void {
                $task = &$m['lessons'][3]['blocks'][6]['data']['selects'][0]; $task['options'][] = 'CONTACTED!';
                $task['feedback']['contacted!'] = 'Повторення того самого ключа.';
            },
            'stale-body' => static function (&$b, &$p, &$m, &$c): void { $p['targets'][0]['after']['page']['blocks'][0]['body'] .= "\n"; },
        ];
        foreach ($mutations as $case => $change) {
            $b = $before; $p = $patch; $m = $master; $c = $contract; $change($b, $p, $m, $c);
            $this->refuses(static function () use ($case, $b, $p, $m, $c): void {
                if (!in_array($case, ['stale-body', 'foreign-category'], true)) {
                    foreach ($m['lessons'] as $i => $lesson) { $p['targets'][$i]['after'] = Package::definition($lesson, $b['targets'][$i]['before']); }
                }
                Package::validate($b, $p, $m, $c);
            }, $case);
        }
        self::assertSame($original, array_slice($this->release(), 0, 4));
        $profile = Package::profile(); $first = array_key_first($profile['artifacts']); $profile['artifacts'][$first] = str_repeat('0', 64);
        $this->refuses(fn () => (new TheoryNativeContentRelease($profile))->load($this->root()), 'stale immutable artifact pin');
    }
}
