<?php

namespace Tests\Feature;

use App\Support\AcceptedAnswerVariants;
use App\Support\M26DetailPackage as Digest;
use App\Support\M78PhrasalQuestionBasics as Package;
use App\Support\TheoryNativeContentRelease;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Frozen teaching-source contract only: no application bootstrap, SQL, HTTP or browser acceptance. */
class M78PhrasalQuestionBasicsPackageTest extends TestCase
{
    private const LEVEL_VECTORS = [
        ['B1', null, null, null, null, null],
        ['B1', null, null, null, null, null],
        [null, null, null, null, null, null, null, null, null, null, null, null, null],
        ['A1', 'A1', 'A1', 'A1', 'A2', 'A2', 'B1', 'A2', 'A1', 'A1', 'A1', 'A1'],
        ['A1', 'A1', 'A2', 'A1', 'A1', 'A1', 'A2', 'A2', 'A2', 'B1', 'A2', 'A1', 'A1', 'A1'],
        ['A1', 'A1', 'A1', 'A1', 'A2', 'A2', 'B1', 'A2', 'A2', 'A2', 'A2', 'A1', 'A1', 'A1'],
    ];

    /** Independently reviewed native children, not a runtime word-count or layout heuristic. */
    private const CHILD_COUNTS = [
        [1 => ['items', 6], 2 => ['rows', 12], 3 => ['items', 3], 4 => ['items', 6], 5 => ['items', 7]],
        [1 => ['sections', 3], 2 => ['sections', 3], 3 => ['sections', 3], 4 => ['items', 6], 5 => ['items', 7]],
        [1 => ['items', 6], 2 => ['sections', 4], 3 => ['sections', 3], 4 => ['sections', 3],
            5 => ['sections', 4], 6 => ['sections', 5], 7 => ['rows', 6], 8 => ['rows', 5],
            9 => ['items', 6], 10 => ['items', 6], 12 => ['items', 7]],
        [1 => ['items', 3], 2 => ['sections', 3], 3 => ['sections', 3], 4 => ['sections', 3],
            5 => ['sections', 2], 6 => ['sections', 3], 7 => ['rows', 6], 8 => ['items', 4],
            9 => ['items', 7], 11 => ['items', 7]],
        [1 => ['items', 3], 2 => ['sections', 3], 3 => ['sections', 3], 4 => ['sections', 2],
            5 => ['sections', 2], 6 => ['sections', 3], 7 => ['sections', 3], 8 => ['rows', 6],
            9 => ['sections', 3], 10 => ['items', 4], 11 => ['items', 7], 13 => ['items', 7]],
        [1 => ['items', 3], 2 => ['sections', 3], 3 => ['sections', 3], 4 => ['sections', 3],
            5 => ['sections', 3], 6 => ['sections', 3], 7 => ['sections', 3], 8 => ['sections', 2],
            9 => ['rows', 5], 10 => ['items', 4], 11 => ['items', 10], 13 => ['items', 7]],
    ];

    private const INPUT_KEYS = [
        ['She picked the box up.', 'Did you write it down?'],
        ['We set off for Lviv at dawn.', 'She didn’t log in to the system.'],
        ['Does she speak French?', 'Are they students?', 'Can he drive?', 'Where did you go?'],
        ['Does she like tea?', 'Are they happy?', 'Can he dance?', 'Do you have a car?'],
        ['Where do you live?', 'How old is she?', 'Where did he go?', 'When did they arrive?'],
        ['I do', "she isn't", 'they can', "I didn't", 'Yes, I am ready.', 'No, he does not work here.'],
    ];

    private const TOPICAL_EXPRESSIONS = [
        'get up', 'wake up', 'put on', 'take off', 'clean up', 'hurry up', 'go out', 'head out',
        'get in', 'get out', 'set off', 'pick up', 'check in',
        'drop by', 'hang out', 'show up', 'break up', 'get along with',
        'hand in', 'find out', 'catch up on', 'carry on', 'look over',
        'log in', 'sign up', 'turn on', 'shut down', 'back up',
        'sort out', 'work out', 'deal with', 'calm down', 'cut down on', 'speak up',
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
        try { $call(); } catch (RuntimeException $error) { $caught = $error; }
        self::assertInstanceOf(RuntimeException::class, $caught, $case);
    }

    public function test_exact_six_owner_source_projection_preserves_metadata_and_adds_only_two_practice_blocks(): void
    {
        [$before, $patch, $master, $contract] = $this->release();
        $profile = Package::profile(); $primitive = new TheoryNativeContentRelease($profile);
        self::assertSame('fe61b3634af5d307cc32167a16e224c1d5fb1591', Package::BASE_SHA);
        self::assertSame([117, 114, 131, 140, 139, 133], Package::PAGE_IDS);
        self::assertSame([19, 19, 21, 22, 22, 22], Package::CATEGORY_IDS);
        self::assertSame([6, 6, 13, 12, 14, 14], Package::CONTINUING_COUNTS);
        self::assertSame([6, 6, 11, 10, 12, 12], Package::PRACTICE_SOURCE_INDICES);
        self::assertSame(['B1', 'B1', null, 'A1', 'A1', 'A1'], Package::LEVELS);
        self::assertSame(['B1', 'B1', 'A1', 'A1', 'A1', 'A1'], Package::FOCUS_LEVELS);
        foreach ([$before['targets'], $patch['targets'], $master['lessons'], $contract['targets']] as $targets) {
            self::assertSame(Package::OWNERS, array_column($targets, 'identity'));
        }
        self::assertSame(Package::LEVELS, array_column($contract['targets'], 'level'));
        self::assertSame(['schema_version', 'base_sha', 'content_type', 'status', 'targets'], array_keys($patch));
        self::assertSame('M78_Phrasal_Question_Basics', $patch['content_type']);
        $continuing = $appended = $allBlocks = 0;
        foreach ($patch['targets'] as $i => $target) {
            $old = $before['targets'][$i]['before']; $oldCopy = $old; $lesson = $master['lessons'][$i];
            self::assertSame(['identity', 'slug', 'path', 'ancestry', 'local_url', 'page_binding', 'block_keys', 'after'], array_keys($target));
            self::assertSame(['schema_version', 'content_type', 'slug', 'page', 'seeder', 'type'], array_keys($target['after']), 'This source package has no question/answer/option write family; SQL preservation is tested separately.');
            $projected = $primitive->projectedBefore($old);
            self::assertSame($oldCopy, $old, 'The pure metadata projection cannot mutate independent BEFORE.');
            self::assertSame(['id' => Package::PAGE_IDS[$i], 'page_category_id' => Package::CATEGORY_IDS[$i]], $target['page_binding']);
            self::assertSame(Package::ANCESTRIES[$i], $target['ancestry']);
            self::assertSame(Package::SLUGS[$i], $lesson['slug']);
            self::assertSame(Package::RENDERED_TITLES[$i], $lesson['preserved_h1']);
            self::assertSame($old['page']['title'], $lesson['raw_title']);
            self::assertSame('uk', $lesson['locale']); self::assertSame('uk', $old['page']['locale']);
            self::assertSame(Package::DISPLAY_LEVELS[$i], $lesson['display_level']);
            self::assertSame(Package::DISPLAY_LEVELS[$i], $lesson['blocks'][0]['data']['level']);
            self::assertSame(Package::LEVELS[$i], $lesson['technical_block_level']);
            self::assertSame(Package::FOCUS_LEVELS[$i], $lesson['intended_own_practice_level']);
            self::assertSame($target['after'], Package::definition($lesson, $old));
            self::assertSame($target['after'], json_decode(file_get_contents($this->root().'/'.$target['path']), true, flags: JSON_THROW_ON_ERROR));
            self::assertSame('page', $target['after']['content_type']);
            self::assertSame('theory', $target['after']['type']);
            self::assertStringStartsWith('database/seeders/Page_V3/', $target['path']);
            $protected = $target['after']; $protected['page']['blocks'] = $old['page']['blocks'];
            foreach (['subtitle_html', 'subtitle_text'] as $field) { $protected['page'][$field] = $old['page'][$field]; }
            self::assertSame($old, $protected, 'Title, slug, category, base/subtitle tags and unrelated source metadata stay exact.');
            self::assertCount(Package::CONTINUING_COUNTS[$i] + ($i < 2 ? 1 : 0), $lesson['blocks']);
            self::assertCount(1, array_filter($lesson['blocks'], static fn ($b) => $b['type'] === 'practice-set'));
            self::assertCount(1, array_filter($lesson['blocks'], static fn ($b) => $b['type'] === 'navigation-chips'));
            foreach ($old['page']['blocks'] as $slot => $source) {
                ++$continuing; $new = $target['after']['page']['blocks'][$slot];
                self::assertSame(self::LEVEL_VECTORS[$i][$slot], $source['level'] ?? null);
                self::assertSame(self::LEVEL_VECTORS[$i][$slot], $new['level'] ?? null);
                self::assertSame($slot, $lesson['blocks'][$slot]['existing_source_index']);
                self::assertSame($source['column'], $new['column']);
                self::assertSame(Digest::uuid($target['identity'], $source, $slot + 1), Digest::uuid($target['identity'], $new, $slot + 1));
                $metadata = $projected['page']['blocks'][$slot]; unset($metadata['body'], $new['body']);
                self::assertSame($metadata, $new, 'Every finite continuing metadata transition must be explicit.');
                if ($i >= 3) {
                    self::assertStringStartsWith('Database\\Seeders\\Page_v2\\', $source['seeder']);
                    foreach (['seeder', 'uuid_key', 'tags', 'inherit_base_tags'] as $field) {
                        self::assertSame(array_key_exists($field, $source), array_key_exists($field, $new));
                        if (array_key_exists($field, $source)) { self::assertSame($source[$field], $new[$field]); }
                    }
                }
            }
            if ($i < 2) {
                ++$appended;
                self::assertSame(['header', 'left', 'left', 'right', 'right', 'footer', 'left'], array_column($lesson['blocks'], 'column'));
                self::assertSame($i === 0
                    ? ['hero', 'forms-grid', 'comparison-table', 'mistakes-grid', 'summary-list', 'navigation-chips', 'practice-set']
                    : ['hero', 'usage-panels', 'usage-panels', 'usage-panels', 'summary-list', 'navigation-chips', 'practice-set'], array_column($lesson['blocks'], 'type'));
                self::assertNull($lesson['blocks'][6]['existing_source_index']);
                self::assertSame('B1', $target['after']['page']['blocks'][6]['level']);
                self::assertSame([], $profile['bindings'][$i]['legacy_box_projection'] ?? []);
            } else {
                self::assertSame([], $profile['bindings'][$i]['appended_blocks']);
                self::assertSame(array_column($old['page']['blocks'], 'type'), array_column($lesson['blocks'], 'type'));
            }
            foreach ($target['after']['page']['blocks'] as $source) {
                ++$allBlocks; self::assertLessThanOrEqual(65535, strlen($source['body']));
                self::assertContains($source['type'], ['hero', 'forms-grid', 'usage-panels', 'comparison-table', 'mistakes-grid', 'summary-list', 'practice-set', 'navigation-chips']);
                foreach (['layout', 'hero_image', 'content', 'columns', 'exercises', 'mistakes', 'summary_points', 'links'] as $field) {
                    self::assertArrayNotHasKey($field, $source, 'No competing historical body or new view payload.');
                }
            }
        }
        self::assertSame(65, $continuing); self::assertSame(2, $appended); self::assertSame(67, $allBlocks);
        foreach ($profile['artifacts'] as $path => $sha) {
            self::assertMatchesRegularExpression('/\A[a-f0-9]{64}\z/', $sha); self::assertNotSame(str_repeat('0', 64), $sha);
            self::assertSame($sha, hash_file('sha256', $this->root().'/'.$path));
        }
    }

    public function test_sixty_seven_graded_tasks_retain_forty_five_functions_and_add_twenty_two_without_counting_four_demos(): void
    {
        [$before, , $master, $contract] = $this->release();
        $graded = $retained = $new = $demos = 0; $newIds = [];
        foreach ($master['lessons'] as $i => $lesson) {
            $data = $this->practice($master, $i); $ownGraded = 0;
            $previous = $i < 2 ? [] : json_decode($before['targets'][$i]['before']['page']['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['body'], true, flags: JSON_THROW_ON_ERROR);
            foreach (['selects', 'choices', 'inputs', 'rephrase'] as $group) {
                foreach ($data[$group] as $n => $task) {
                    $oldTask = $previous[$group][$n] ?? null;
                    if (array_key_exists('example_original', $task)) {
                        ++$demos; self::assertIsArray($oldTask); self::assertArrayHasKey('example_original', $oldTask);
                        self::assertNotSame('', trim($task['example_original'])); self::assertNotSame('', trim($task['example_target']));
                        self::assertArrayNotHasKey('answer', $task); self::assertArrayNotHasKey('accepted', $task);
                        self::assertArrayNotHasKey('id', $task); self::assertArrayNotHasKey('source_index', $task);
                        continue;
                    }
                    ++$graded; ++$ownGraded;
                    self::assertIsArray($task['accepted']); self::assertContains($task['answer'], $task['accepted']);
                    self::assertNotSame('', Package::comparisonKey($task['answer']));
                    if (is_array($oldTask)) {
                        ++$retained;
                        foreach (['id', 'source_index'] as $field) {
                            self::assertSame(array_key_exists($field, $oldTask), array_key_exists($field, $task));
                            if (array_key_exists($field, $oldTask)) { self::assertSame($oldTask[$field], $task[$field]); }
                        }
                        if ($i === 5 && $group === 'rephrase') {
                            self::assertSame([1 => 'Yes, I can.', 2 => 'Yes, she is.', 3 => 'Yes, I do.'][$n], $task['answer']);
                            self::assertSame('Q: '.['Can you swim?', 'Is she happy?', 'Do you like it?'][$n - 1].' A: '.$task['answer'], $oldTask['answer']);
                        } else {
                            self::assertContains($oldTask['answer'], $task['accepted'], 'Retained functions must retain the canonical old answer.');
                        }
                    } else {
                        ++$new; self::assertMatchesRegularExpression('/\Am78-[a-z0-9-]+\z/', $task['id']);
                        self::assertArrayNotHasKey('source_index', $task); $newIds[] = $task['id'];
                    }
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
                        $literalKey = mb_strtolower(trim($value)); self::assertArrayHasKey($literalKey, $task['feedback']);
                        self::assertNotSame('', trim($task['feedback'][$literalKey]));
                    }
                    foreach ($task['feedback'] as $key => $text) {
                        self::assertSame(mb_strtolower(trim($key)), $key); self::assertIsString($text);
                        self::assertDoesNotMatchRegularExpression('/<(?:\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);|\{a\d+\}/iu', $text);
                    }
                }
                self::assertGreaterThanOrEqual(count($previous[$group] ?? []), count($data[$group]), 'No old task or demo position disappears.');
            }
            self::assertSame([6, 6, 14, 13, 13, 15][$i], $ownGraded);
            self::assertCount($i < 2 ? 2 : 4, $data['selects']); self::assertCount(2, $data['choices']);
            self::assertSame('theory_links', $data['linked_practice']['source']); self::assertSame(['4'], $data['linked_practice']['question_types']);
            self::assertSame($contract['targets'][$i]['bank_seeders'], $data['linked_practice']['seeder_classes']);
            self::assertSame($i < 2 ? 'Вправа 4. Побудуй речення' : ($i === 5 ? 'Вправа 5. Побудуй відповідь' : 'Вправа 5. Побудуй речення'), $data['linked_practice']['title']);
            self::assertDoesNotMatchRegularExpression('/поліглот/iu', implode(' ', array_intersect_key($data, array_flip(['title', 'select_title', 'choice_title', 'input_title', 'rephrase_title']))));
        }
        self::assertSame(67, $graded); self::assertSame(45, $retained); self::assertSame(22, $new); self::assertSame(4, $demos);
        self::assertCount(22, $newIds); self::assertCount(22, array_unique($newIds));
    }

    public function test_eighteen_full_sentence_inputs_are_not_confused_with_four_retained_partial_answer_gaps(): void
    {
        [, , $master] = $this->release(); $full = $partial = 0;
        foreach ($master['lessons'] as $i => $lesson) {
            $data = $this->practice($master, $i);
            self::assertSame(self::INPUT_KEYS[$i], array_column($data['inputs'], 'answer'));
            self::assertSame([[0, 1], [0, 1], [0, 1, 2, 3], [0, 1, 2, 3], [0, 1, 2, 3], [4, 5]][$i], Package::SENTENCE_INPUT_INDICES[$i]);
            foreach ($data['inputs'] as $n => $task) {
                self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $task['after']);
                if ($i === 5 && $n < 4) {
                    ++$partial; self::assertStringNotContainsString('/', $task['before']);
                    self::assertMatchesRegularExpression('/(?:Yes|No),\s*$/', $task['before']);
                    self::assertDoesNotMatchRegularExpression('/\b(?:Yes|No)\b|Q:|A:/', $task['answer']);
                    self::assertArrayNotHasKey('id', $task); continue;
                }
                ++$full; self::assertArrayNotHasKey('prompt', $task);
                self::assertMatchesRegularExpression('/\A[A-Z].*[.!?]\z/', $task['answer']);
                self::assertDoesNotMatchRegularExpression('/Q:|A:|\{a\d+\}/', $task['answer']);
                $groups = array_map('trim', explode('/', $task['before'])); self::assertGreaterThanOrEqual(2, count($groups));
                foreach ($groups as $group) {
                    $words = preg_split('/\s+/u', $group, flags: PREG_SPLIT_NO_EMPTY);
                    self::assertGreaterThanOrEqual(1, count($words)); self::assertLessThanOrEqual(3, count($words));
                }
                self::assertNotSame(Package::comparisonKey($task['answer']), Package::comparisonKey(implode(' ', $groups)), 'Chunks are authored shuffled, not already solved.');
                $words = static fn (string $text): array => preg_split('/\s+/u', Package::comparisonKey($text), flags: PREG_SPLIT_NO_EMPTY);
                $answerCounts = array_count_values($words($task['answer'])); $bankCounts = array_count_values($words(implode(' ', $groups)));
                ksort($answerCounts); ksort($bankCounts); self::assertSame($answerCounts, $bankCounts, 'All canonical words and multiplicities must be available.');
            }
        }
        self::assertSame(18, $full); self::assertSame(4, $partial);
        $answers = $this->practice($master, 5);
        self::assertSame(['I do', "she isn't", 'they can', "I didn't"], array_slice(array_column($answers['inputs'], 'answer'), 0, 4));
        self::assertContains("she's not", $answers['inputs'][1]['accepted']);
        self::assertContains('she is not', $answers['inputs'][1]['accepted']);
        self::assertContains('I did not', $answers['inputs'][3]['accepted']);
        self::assertContains("Yes, I'm ready.", $answers['inputs'][4]['accepted']);
        self::assertContains("No, he doesn't work here.", $answers['inputs'][5]['accepted']);
        self::assertContains('No, they did not', $answers['selects'][3]['accepted']);
        self::assertTrue(AcceptedAnswerVariants::matches('She didn’t log in to the system.', 'She did not log in to the system.'));
        self::assertTrue(AcceptedAnswerVariants::matches('No, he does not work here.', "No, he doesn't work here."));
        self::assertTrue(AcceptedAnswerVariants::matches('Yes, I am ready.', "Yes, I'm ready."));
        self::assertFalse(AcceptedAnswerVariants::matches('Yes, she is.', "Yes, she's."), 'A final positive auxiliary is not contracted without its complement.');
        self::assertFalse(AcceptedAnswerVariants::matches('I do', 'Yes, I do'), 'The already visible Yes cannot be entered a second time.');
        self::assertContains('She picked up the box.', $this->practice($master, 0)['inputs'][0]['accepted']);
        self::assertContains('Have you got a car?', $this->practice($master, 3)['inputs'][3]['accepted']);
    }

    public function test_visible_teaching_covers_concrete_models_all_thirty_four_topical_expressions_and_seven_footer_links(): void
    {
        [$before, , $master] = $this->release(); $links = 0;
        foreach ($master['lessons'] as $i => $lesson) {
            self::assertSame([], $lesson['details']); self::assertNotEmpty($lesson['point_detail_decisions']);
            foreach (self::CHILD_COUNTS[$i] as $slot => [$field, $count]) {
                self::assertCount($count, $lesson['blocks'][$slot]['data'][$field], 'The finite reviewed teaching points and table rows must not disappear.');
            }
            $keys = array_column($lesson['blocks'], null, 'key');
            foreach ($lesson['point_detail_decisions'] as $decision) {
                self::assertArrayHasKey($decision['block_key'], $keys); self::assertSame('visible_basic', $decision['decision']);
                self::assertNotSame('', trim($decision['reason']));
            }
            $slot = $i < 2 ? 5 : Package::CONTINUING_COUNTS[$i] - 1;
            $old = json_decode($before['targets'][$i]['before']['page']['blocks'][$slot]['body'], true, flags: JSON_THROW_ON_ERROR)['items'];
            $new = $lesson['blocks'][$slot]['data']['items']; self::assertCount(7, $new);
            foreach ($new as $n => $item) {
                ++$links; self::assertSame($old[$n]['label'], $item['label']); self::assertSame($old[$n]['current'], $item['current']);
                self::assertMatchesRegularExpression('~\A/theory/[a-z0-9-]+(?:/[a-z0-9-]+)*\z~', $item['url']);
            }
            if ($i < 2) { self::assertSame($old, $new); }
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
        self::assertSame(42, $links); self::assertCount(34, self::TOPICAL_EXPRESSIONS);
        $teaching = mb_strtolower(json_encode(array_slice($master['lessons'][1]['blocks'], 0, 5), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        foreach (self::TOPICAL_EXPRESSIONS as $expression) { self::assertStringContainsString($expression, $teaching); }
        foreach (['catch up with', 'backup', 'sign-up', 'login', 'need to sort'] as $qualification) { self::assertStringContainsString($qualification, $teaching); }
        $separable = json_encode(array_slice($master['lessons'][0]['blocks'], 0, 5), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        foreach (['turn it off', 'look after', 'run into', 'come across', 'deal with', 'put up with', 'get on with', 'The plane took off at noon.', 'What did you turn off?'] as $model) {
            self::assertStringContainsString($model, $separable);
        }
        self::assertCount(3, $master['lessons'][1]['blocks'][3]['data']['sections']);
        self::assertSame('usage-panels', $master['lessons'][1]['blocks'][3]['type']);
        self::assertSame('right', $master['lessons'][1]['blocks'][3]['column']);
        self::assertSame('Who called you?', $this->practice($master, 2)['rephrase'][4]['answer']);
        self::assertSame('Who lives here?', $this->practice($master, 4)['rephrase'][2]['answer']);
        self::assertStringContainsString('needn’t', $this->practice($master, 5)['choices'][0]['label']);
    }

    public function test_foreign_stale_incomplete_and_semantically_colliding_packages_fail_closed(): void
    {
        [$before, $patch, $master, $contract] = $this->release(); $original = [$before, $patch, $master, $contract];
        $mutations = [
            'foreign-owner' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['identity'] = 'Foreign\\Owner'; },
            'foreign-locale' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['locale'] = 'en'; },
            'stale-category' => static function (&$b, &$p, &$m, &$c): void { $p['targets'][3]['page_binding']['page_category_id'] = 21; },
            'foreign-ancestry' => static function (&$b, &$p, &$m, &$c): void { $p['targets'][4]['ancestry'] = ['types-of-questions']; },
            'null-filter-filled' => static function (&$b, &$p, &$m, &$c): void { $c['targets'][2]['level'] = 'A1'; },
            'range-as-filter' => static function (&$b, &$p, &$m, &$c): void { $c['targets'][0]['level'] = 'B1-B2'; },
            'continuing-null-level-filled' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['blocks'][3]['level'] = 'A1'; },
            'right-recommendation-moved-left' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][3]['column'] = 'left'; },
            'right-advice-falsely-made-corrections' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][3]['type'] = 'mistakes-grid'; },
            'old-page-v2-seeder-changed' => static function (&$b, &$p, &$m, &$c): void { $b['targets'][3]['before']['page']['blocks'][1]['seeder'] = Package::OWNERS[3]; },
            'old-tags-rewritten' => static function (&$b, &$p, &$m, &$c): void { $p['targets'][4]['after']['page']['base_tags'] = []; },
            'retained-task-lost' => static function (&$b, &$p, &$m, &$c): void { array_pop($m['lessons'][2]['blocks'][11]['data']['rephrase']); },
            'retained-id-invented' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][3]['blocks'][10]['data']['selects'][0]['id'] = 'm78-invented-old'; },
            'demo-graded' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][4]['blocks'][12]['data']['rephrase'][0]['answer'] = 'What do you want?'; },
            'duplicate-practice' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][] = $m['lessons'][0]['blocks'][6]; },
            'appended-reindexed' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][6]['existing_source_index'] = 5; },
            'missing-point-label' => static function (&$b, &$p, &$m, &$c): void { unset($m['lessons'][1]['blocks'][1]['data']['sections'][0]['label']); },
            'whole-useful-point-lost' => static function (&$b, &$p, &$m, &$c): void { array_pop($m['lessons'][1]['blocks'][1]['data']['sections']); },
            'punctuation-option-collision' => static function (&$b, &$p, &$m, &$c): void {
                $task = &$m['lessons'][0]['blocks'][6]['data']['selects'][0]; $task['options'][] = 'TURN IT OFF!';
                $task['feedback']['turn it off!'] = 'Повторення того самого ключа після нормалізації.';
            },
            'apostrophe-only-option-collision' => static function (&$b, &$p, &$m, &$c): void {
                $task = &$m['lessons'][1]['blocks'][6]['data']['choices'][0];
                $task['options'] = ['each others', "each other's"]; $task['answer'] = 'each others'; $task['accepted'] = ['each others'];
                $task['feedback'] = ['each others' => 'Перший варіант.', "each other's" => 'Другий варіант.'];
            },
            'contraction-replaces-retained-negative-function' => static function (&$b, &$p, &$m, &$c): void {
                $task = &$m['lessons'][5]['blocks'][12]['data']['inputs'][1]; $task['answer'] = "she's"; $task['accepted'] = ["she's"];
                $task['feedback'] = ["she's" => 'Неповна стверджувальна форма не передає вихідне заперечення.'];
            },
            'empty-key' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][5]['blocks'][12]['data']['inputs'][0]['accepted'][] = ''; },
            'alias-feedback-missing' => static function (&$b, &$p, &$m, &$c): void { unset($m['lessons'][5]['blocks'][12]['data']['inputs'][4]['feedback']["yes, i'm ready."]); },
            'html-feedback' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][6]['data']['selects'][0]['feedback']['turn it off'] = '<b>Правильно.</b>'; },
            'hidden-full-input-condition' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][3]['blocks'][10]['data']['inputs'][0]['after'] = ''; },
            'new-grader-opt-in' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][5]['blocks'][12]['data']['inputs'][5]['punctuation_sensitive'] = true; },
            'stored-design-selector' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][1]['data']['stylesheet'] = 'm78-custom.css'; },
            'stale-after-body' => static function (&$b, &$p, &$m, &$c): void { $p['targets'][2]['after']['page']['blocks'][1]['body'] .= "\n"; },
        ];
        foreach ($mutations as $case => $change) {
            $b = $before; $p = $patch; $m = $master; $c = $contract; $change($b, $p, $m, $c);
            $this->refuses(static function () use ($case, $b, $p, $m, $c): void {
                if (!in_array($case, ['stale-after-body', 'stale-category', 'old-tags-rewritten'], true)) {
                    foreach ($m['lessons'] as $i => $lesson) { $p['targets'][$i]['after'] = Package::definition($lesson, $b['targets'][$i]['before']); }
                }
                Package::validate($b, $p, $m, $c);
            }, $case);
        }
        self::assertSame($original, array_slice($this->release(), 0, 4));
        $profile = Package::profile(); $path = array_key_first($profile['artifacts']); $profile['artifacts'][$path] = str_repeat('0', 64);
        $this->refuses(fn () => (new TheoryNativeContentRelease($profile))->load($this->root()), 'stale frozen artifact pin');
    }
}
