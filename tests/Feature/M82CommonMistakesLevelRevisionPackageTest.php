<?php

namespace Tests\Feature;

use App\Support\M26DetailPackage as Digest;
use App\Support\M82CommonMistakesLevelRevision as Package;
use App\Support\TheoryNativeContentRelease;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Finite reviewed content contract; no Laravel bootstrap, working SQL, HTTP or browser storage. */
class M82CommonMistakesLevelRevisionPackageTest extends TestCase
{
    private const LEVELS = [
        ['A2', null, null, 'A2', 'A2', null],
        ['A2', null, null, 'A2', 'A2', null],
        ['A2', null, null, 'A2', 'A2', null],
        ['B1', null, null, 'B1', 'B1', null],
        ['A1', 'A1', 'A1', 'A1'],
        ['A2', 'A2', 'A2', 'A2'],
    ];

    private const REVIEWED_NAVIGATION_URLS = [
        4 => [
            '/theory/basic-grammar/verb-to-be/verb-to-be-present',
            '/theory/basic-grammar/verb-to-be/there-is-there-are',
            '/theory/some-any/theory-some-any-things',
            '/theory/prykmetniky-ta-pryslinknyky/comparative-vs-superlative',
        ],
        5 => [
            '/theory/tenses/present-perfect/present-perfect-forms',
            '/theory/conditionals/first-conditional',
            '/theory/reported-speech/reported-statements',
            '/theory/conditionals/second-conditional',
        ],
    ];

    /** Reviewed literal facts, specified before freeze, never inferred from a runtime AFTER. */
    private const INPUTS = [
        ['My sister has been to Paris three times.', 'I have not finished the report yet.'],
        ['I need one piece of advice.', 'She gave me a lot of information.'],
        ['We arrived at the airport late.', 'She walked into the room.'],
        ['You should have called me last night.', 'Rarely do we see such clear feedback.'],
        ['Yesterday, we went to the park.', 'I will buy some apples tomorrow.'],
        ['Olia said that she was tired.', 'The windows are cleaned by Ivan every Friday.'],
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

    public function test_six_exact_owners_keep_thirty_two_blocks_and_eight_explicit_native_transitions(): void
    {
        [$before, $patch, $master, $contract] = $this->release();
        self::assertSame('b8fecb29492038dc01d374955b61c500c718fa4c', Package::BASE_SHA);
        self::assertSame('1.0.2', $master['version']);
        self::assertSame([42, 40, 41, 39, 325, 326], Package::PAGE_IDS);
        self::assertSame([9, 9, 9, 9, 59, 59], Package::CATEGORY_IDS);
        self::assertSame([6, 6, 6, 6, 4, 4], Package::CONTINUING_COUNTS);
        self::assertSame([6, 6, 6, 6, 4, 4], Package::PRACTICE_SOURCE_INDICES);
        self::assertSame(['A2', 'A2', 'A2', 'B1', 'A1', 'A2'], Package::LEVELS);
        self::assertSame(['A2-B1', 'A2-B1', 'A2-B1', 'B1-B2', 'A1', 'A2'], Package::DISPLAY_LEVELS);
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
                self::assertSame($metadata, $after, 'Only reviewed type/heading transitions may change old metadata.');
                if ($i < 4 && in_array($slot, [1, 2], true)) {
                    ++$transitions; self::assertSame('box', $source['type']);
                    self::assertSame('usage-panels', $after['type']);
                    self::assertNull($after['level'] ?? null); self::assertSame('left', $after['column']);
                } else { self::assertSame($source['type'], $after['type']); }
            }
            if ($i < 4) {
                self::assertSame('right', $lesson['blocks'][3]['column']); self::assertSame('mistakes-grid', $lesson['blocks'][3]['type']);
                self::assertSame('right', $lesson['blocks'][4]['column']); self::assertSame('summary-list', $lesson['blocks'][4]['type']);
            } else {
                self::assertSame(['hero','summary-list','forms-grid','navigation-chips'], array_column(array_slice($lesson['blocks'],0,4),'type'));
            }
            $practice = $lesson['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]; ++$added;
            self::assertNull($practice['existing_source_index']); self::assertSame('left', $practice['column']);
            self::assertSame(Package::LEVELS[$i], $practice['level']);
            $navSlot=$i < 4 ? 5 : 3;
            $oldNav = json_decode($old['page']['blocks'][$navSlot]['body'], true, flags: JSON_THROW_ON_ERROR)['items'];
            $newNav = $lesson['blocks'][$navSlot]['data']['items']; self::assertCount($i < 4 ? 6 : 5, $newNav);
            if ($i < 4) { self::assertSame($oldNav,$newNav,'Existing Common Mistakes labels and explicit hrefs stay exact.'); }
            self::assertCount(1, array_filter($newNav, static fn ($item) => $item['current'] === true));
            foreach ($newNav as $j => $link) {
                ++$links; self::assertSame($oldNav[$j]['label'], $link['label']); self::assertSame($oldNav[$j]['current'], $link['current']);
                if ($i < 4) { self::assertMatchesRegularExpression('~\\A/theory/common-mistakes/[a-z0-9-]+\\z~', $link['url']); }
                elseif ($j < 4) { self::assertSame(self::REVIEWED_NAVIGATION_URLS[$i][$j], $link['url']); }
                else { self::assertSame($oldNav[$j], $link, 'Current fifth chip stays a span; no invented self-link.'); }
            }
            foreach ($target['after']['page']['blocks'] as $block) {
                self::assertLessThanOrEqual(65535, strlen($block['body'])); self::assertNotSame('box', $block['type']);
            }
            foreach ($lesson['blocks'] as $block) { $this->assertLearningData($block['data']); }
        }
        self::assertSame(32, $continuing); self::assertSame(6, $added); self::assertSame(8, $transitions); self::assertSame(34, $links);
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
                    $ids[] = $task['id']; self::assertMatchesRegularExpression('/\Am82-[a-z0-9-]+\z/', $task['id']);
                    self::assertSame(['selects' => 1, 'choices' => 3, 'inputs' => 5][$group] + $index, $task['source_index']);
                    if ($group === 'selects') { self::assertArrayNotHasKey('prompt', $task); }
                    self::assertContains($task['answer'], $task['accepted']);
                    $keys = array_map(Package::comparisonKey(...), $task['accepted']);
                    self::assertNotContains('', $keys); self::assertCount(count($keys), array_unique($keys));
                    foreach (['hint', 'placeholder', 'strict', 'punctuation_sensitive', 'm38_semantic_checks'] as $field) { self::assertArrayNotHasKey($field, $task); }
                    $visible = $group === 'inputs' ? $task['after'] : $task['label'];
                    self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', strip_tags($visible));
                    if ($i < 4 || $group !== 'inputs') {
                        self::assertMatchesRegularExpression('/<(?:p|span) lang="en">[^<]*[A-Za-z]{2,}/u', $visible, 'Source or contrast is in a supported visible field.');
                    }
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

    public function test_reviewed_literal_sentences_aliases_and_meaningful_detail_owners_survive(): void
    {
        [, , $master] = $this->release();
        foreach (self::INPUTS as $i => $answers) { self::assertSame($answers, array_column($this->practice($master, $i)['inputs'], 'answer')); }
        self::assertSame([1,0,1,1,0,0], array_map(static fn ($lesson) => count($lesson['details']), $master['lessons']));
        foreach ([0=>[2,'/sections/1'],2=>[1,'/sections/3'],3=>[1,'/sections/3']] as $i=>[$slot,$pointer]) {
            $lesson=$master['lessons'][$i];$detail=$lesson['details'][0];
            self::assertSame($lesson['blocks'][$slot]['key'],$detail['owner_block_key']);self::assertSame($pointer,$detail['owner_pointer']);
            self::assertSame('usage-panels',$detail['fragment']['type']);
            foreach ($detail['fragment']['data']['sections'] as $section) {
                self::assertNotSame('',trim($section['description']));
                foreach ($section['examples'] as $example) { self::assertNotSame('',trim($example['en']));self::assertMatchesRegularExpression('/[\\x{0400}-\\x{04ff}]/u',$example['ua']); }
            }
        }
        [$a,$b,$c,$d,$e,$f]=array_map(fn($i)=>$this->practice($master,$i),range(0,5));
        self::assertSame(['has just finished','since'],array_column($a['selects'],'answer'));
        self::assertSame(['many','is'],array_column($b['selects'],'answer'));
        self::assertSame(['Без прийменника','on'],array_column($c['selects'],'answer'));
        self::assertSame(['knew','must have gone'],array_column($d['selects'],'answer'));
        self::assertSame(['much','has'],array_column($e['selects'],'answer'));
        self::assertSame(['Have',"can't she"],array_column($f['selects'],'answer'));
        self::assertContains("My sister's been to Paris three times.",$a['inputs'][0]['accepted']);
        self::assertContains("I haven't finished the report yet.",$a['inputs'][1]['accepted']);
        self::assertContains("I've not finished the report yet.",$a['inputs'][1]['accepted']);
        self::assertSame(['I need one piece of advice.'],$b['inputs'][0]['accepted']);
        self::assertContains('She gave me lots of information.',$b['inputs'][1]['accepted']);
        self::assertContains("I'll buy some apples tomorrow.",$e['inputs'][1]['accepted']);
        self::assertContains('Olia said she was tired.',$f['inputs'][0]['accepted']);
        self::assertContains('The windows are cleaned every Friday by Ivan.',$f['inputs'][1]['accepted']);
        self::assertStringContainsString('Залишити без змін',$a['choices'][0]['answer']);
        self::assertStringContainsString('Залишити',$b['choices'][0]['answer']);
        self::assertStringContainsString('three',$b['choices'][1]['label']);
        foreach ([4,5] as $i) {
            self::assertSame(['mixed-revision'],$master['lessons'][$i]['ancestry']??Package::ANCESTRIES[$i]);
            self::assertStringContainsString('/theory/mixed-revision/',$master['lessons'][$i]['local_url']);
            self::assertStringNotContainsString('/theory/basic-grammar/',$master['lessons'][$i]['local_url']);
        }
    }

    public function test_foreign_stale_hidden_duplicate_and_incomplete_packages_fail_closed(): void
    {
        [$before, $patch, $master, $contract] = $this->release();
        $mutations = [
            'missing-owner' => static function (&$b, &$p, &$m, &$c): void { array_pop($m['lessons']); },
            'foreign-owner' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['identity'] = 'Foreign\\Owner'; },
            'foreign-locale' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['locale'] = 'en'; },
            'foreign-category' => static function (&$b, &$p, &$m, &$c): void { $p['targets'][2]['page_binding']['page_category_id'] = 22; },
            'range-as-filter' => static function (&$b, &$p, &$m, &$c): void { $c['targets'][0]['level'] = 'A2-B1'; },
            'unknown-filter' => static function (&$b, &$p, &$m, &$c): void { $c['targets'][1]['level'] = null; },
            'foreign-bank' => static function (&$b, &$p, &$m, &$c): void { $c['targets'][2]['bank_seeders'] = ['Foreign\\Bank']; },
            'right-mistakes-moved' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['blocks'][3]['column'] = 'left'; },
            'null-box-level-guessed' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][3]['blocks'][1]['level'] = 'B2'; },
            'unapproved-type-transition' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][4]['blocks'][2]['type'] = 'usage-panels'; },
            'basicgrammar-as-category' => static function (&$b, &$p, &$m, &$c): void { $p['targets'][4]['ancestry'] = ['basic-grammar']; },
            'foreign-mixed-bank-same-level' => static function (&$b, &$p, &$m, &$c): void { $c['targets'][4]['bank_seeders'] = ['Database\\Seeders\\V3\\Polyglot\\PolyglotA2MixedRevisionAllLevelsLessonSeeder']; },
            'missing-task' => static function (&$b, &$p, &$m, &$c): void { array_pop($m['lessons'][0]['blocks'][6]['data']['inputs']); },
            'missing-id' => static function (&$b, &$p, &$m, &$c): void { unset($m['lessons'][1]['blocks'][6]['data']['selects'][0]['id']); },
            'unsupported-generic-intro' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][6]['data']['intro'] = 'Не покладайся на невідображуваний критерій.'; },
            'unsupported-select-prompt' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][6]['data']['selects'][0]['prompt'] = 'Критерій у непідтриманому полі.'; },
            'hidden-prompt-only' => static function (&$b, &$p, &$m, &$c): void { $t = &$m['lessons'][2]['blocks'][6]['data']['selects'][0]; $t['label'] = ''; $t['prompt'] = 'Критерій лише тут.'; },
            'hidden-full-context' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][2]['blocks'][6]['data']['inputs'][0]['after'] = ''; },
            'ready-answer-hint' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][6]['data']['inputs'][0]['hint'] = self::INPUTS[0][0]; },
            'empty-alias' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][1]['blocks'][6]['data']['inputs'][0]['accepted'][] = ''; },
            'normalized-option-collision' => static function (&$b, &$p, &$m, &$c): void { $t = &$m['lessons'][0]['blocks'][6]['data']['selects'][0]; $t['options'][] = 'HAS JUST FINISHED!'; $t['feedback']['has just finished!'] = 'Повторений нормалізований варіант.'; },
            'html-feedback' => static function (&$b, &$p, &$m, &$c): void { $t = &$m['lessons'][1]['blocks'][6]['data']['inputs'][0]; $t['feedback'][mb_strtolower(trim($t['answer']))] = '<b>Правильно.</b>'; },
            'visual-payload' => static function (&$b, &$p, &$m, &$c): void { $m['lessons'][0]['blocks'][1]['data']['stylesheet'] = 'm82.css'; },
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

    public function test_course_and_standalone_consumers_receive_before_clones_not_theory_mutations(): void
    {
        [$before,$patch]=$this->release();
        $previousContainer = \Illuminate\Container\Container::getInstance();
        try {
            // Supply only the path/locale services used by the pure preservation helper.
            // No kernel bootstrap, environment loading or database connection is performed.
            $application = new \Illuminate\Foundation\Application($this->root());
            $application->instance('config', new \Illuminate\Config\Repository(['app' => ['locale' => 'uk']]));
            foreach ([4,5] as $i) {
                $target=$patch['targets'][$i];$old=$before['targets'][$i]['before'];$new=$target['after'];
                $page=new \App\Models\Page();
                $page->setRawAttributes(['id'=>Package::PAGE_IDS[$i],'page_category_id'=>Package::CATEGORY_IDS[$i],
                    'seeder'=>Package::OWNERS[$i],'slug'=>Package::SLUGS[$i],'title'=>$old['page']['title'],
                    'type'=>'theory','text'=>$new['page']['subtitle_text']],true);
                $subtitle=['type'=>'subtitle','column'=>'header','body'=>$new['page']['subtitle_html'],
                    'level'=>$old['page']['subtitle_level']??null,'uuid_key'=>$old['page']['subtitle_uuid_key']??'subtitle'];
                $rows=[];
                foreach ([$subtitle,...$new['page']['blocks']] as $position=>$config) {
                    $row=new \App\Models\TextBlock();$row->setRawAttributes(['id'=>900000+$i*100+$position,
                        'uuid'=>Digest::uuid(Package::OWNERS[$i],$config,$position),'page_id'=>Package::PAGE_IDS[$i],
                        'page_category_id'=>Package::CATEGORY_IDS[$i],'seeder'=>Package::OWNERS[$i],'locale'=>'uk',
                        'sort_order'=>$position,'type'=>$config['type'],'column'=>$config['column'],
                        'heading'=>$config['heading']??null,'css_class'=>$config['css_class']??null,
                        'level'=>$config['level']??null,'body'=>$config['body']],true);$rows[]=$row;
                }
                $page->setRelation('textBlocks',new \Illuminate\Database\Eloquent\Collection($rows));
                $attributes=$page->getAttributes();$rawBlocks=array_map(static fn($b)=>$b->getAttributes(),$rows);
                $restored=Package::preserveCoursePage($page);self::assertNotSame($page,$restored);
                self::assertSame($old['page']['subtitle_text'],$restored->getRawOriginal('text'));
                self::assertCount(5,$restored->getRelation('textBlocks'));self::assertCount(6,$page->getRelation('textBlocks'));
                self::assertSame([$old['page']['subtitle_html'],...array_column($old['page']['blocks'],'body')],
                    $restored->getRelation('textBlocks')->pluck('body')->all());
                self::assertSame($attributes,$page->getAttributes());
                self::assertSame($rawBlocks,array_map(static fn($b)=>$b->getAttributes(),$rows),'Original models never mutate.');
                foreach ($rows as $position=>$row) {
                    self::assertSame($row,Package::preserveExcludedTextBlock($row,'course_other'));
                    $clone=Package::preserveExcludedTextBlock($row,'standalone_test');
                    if ($position===5) { self::assertNull($clone,'New theory widget does not become a course/standalone final drill.'); }
                    else {
                        self::assertNotSame($row,$clone);
                        self::assertSame($position===0?$old['page']['subtitle_html']:$old['page']['blocks'][$position-1]['body'],$clone->body);
                    }
                    self::assertSame($rawBlocks[$position],$row->getAttributes());
                }
                $stale=clone $page;$staleRows=new \Illuminate\Database\Eloquent\Collection(array_map(static fn($r)=>clone $r,$rows));
                $staleRows[1]->body.=' stale';$stale->setRelation('textBlocks',$staleRows);
                self::assertSame($stale,Package::preserveCoursePage($stale),'Unknown content is not normalized into old progress consumers.');
            }
        } finally {
            \Illuminate\Container\Container::setInstance($previousContainer);
        }
    }

    public function test_eight_reviewed_footer_data_urls_keep_native_functions_labels_current_and_resolver_unchanged(): void
    {
        [$before,$patch,$master,$contract]=$this->release();$profile=Package::profile();$added=0;$reviewed=[];
        foreach ([4,5] as $i) {
            self::assertArrayNotHasKey('preserved_label_navigation',$profile['bindings'][$i]);
            $old=json_decode($before['targets'][$i]['before']['page']['blocks'][3]['body'],true,flags:JSON_THROW_ON_ERROR)['items'];
            $new=$master['lessons'][$i]['blocks'][3]['data']['items'];
            self::assertSame(['hero','summary-list','forms-grid','navigation-chips'],array_column(array_slice($master['lessons'][$i]['blocks'],0,4),'type'));
            self::assertSame(['header','left','left','footer'],array_column(array_slice($master['lessons'][$i]['blocks'],0,4),'column'));
            self::assertCount(5,$old);self::assertCount(5,$new);
            foreach ($new as $j=>$item) {
                self::assertSame($old[$j]['label'],$item['label']);self::assertSame($old[$j]['current'],$item['current']);
                if ($j===4) { self::assertSame($old[$j],$item);self::assertTrue($item['current']);continue; }
                ++$added;self::assertSame(['label','current','url'],array_keys($item));
                self::assertSame(self::REVIEWED_NAVIGATION_URLS[$i][$j],$item['url']);
                self::assertMatchesRegularExpression('~\A/theory/[a-z0-9/-]+\z~',$item['url']);
                $reviewed[]=['owner'=>Package::OWNERS[$i],'source_index'=>3,'item_index'=>$j,
                    'label'=>$item['label'],'current'=>false,'url'=>$item['url']];
                foreach (['missing-url','hash-url','wrong-valid-url','changed-label','changed-current'] as $case) {
                    $m=$master;$a=$patch;
                    if ($case==='missing-url') { unset($m['lessons'][$i]['blocks'][3]['data']['items'][$j]['url']); }
                    if ($case==='hash-url') { $m['lessons'][$i]['blocks'][3]['data']['items'][$j]['url']='#'; }
                    if ($case==='wrong-valid-url') { $m['lessons'][$i]['blocks'][3]['data']['items'][$j]['url']='/theory/conditionals/third-conditional'; }
                    if ($case==='changed-label') { $m['lessons'][$i]['blocks'][3]['data']['items'][$j]['label']='Foreign label'; }
                    if ($case==='changed-current') { $m['lessons'][$i]['blocks'][3]['data']['items'][$j]['current']=true; }
                    $this->refuses(static function()use($before,$a,$m,$contract,$i):void {
                        $a['targets'][$i]['after']=Package::definition($m['lessons'][$i],$before['targets'][$i]['before']);
                        Package::validate($before,$a,$m,$contract);
                    },$i.':'.$j.':'.$case);
                }
            }
        }
        self::assertSame(8,$added);
        $mapping=json_decode(file_get_contents($this->root().'/docs/content/m82-navigation-review.v1.json'),true,flags:JSON_THROW_ON_ERROR);
        self::assertSame($reviewed,array_map(static fn($row)=>array_intersect_key($row,array_flip(['owner','source_index','item_index','label','current','url'])),$mapping['targets']));
        self::assertFalse($mapping['verification']['native_view_or_resolver_changed']);
        self::assertFalse($mapping['verification']['current_fifth_items_gain_url']);
        self::assertSame('b7cd364247c702d916a3d8777ac6b36c8808da1a751996a1d0fa87068ebcdb43',
            hash('sha256',str_replace("\r\n","\n",file_get_contents($this->root().'/resources/views/theory/show.blade.php'))));
        // Historical profiles still require explicit destination data; no shared opt-in was added.
        $oldProfile=\App\Support\M81TransformationsCommonMistakes::profile();
        [$b,$p,$m,$c]=\App\Support\M81TransformationsCommonMistakes::load($this->root());
        self::assertArrayNotHasKey('preserved_label_navigation',$oldProfile['bindings'][0]);
        unset($m['lessons'][0]['blocks'][5]['data']['items'][1]['url']);
        $this->refuses(static function()use($oldProfile,$b,$p,$m,$c):void {
            $native=new TheoryNativeContentRelease($oldProfile);
            $p['targets'][0]['after']=$native->definition($m['lessons'][0],$b['targets'][0]['before']);
            $native->validate($b,$p,$m,$c);
        },'Historical URL requirement remains active.');
    }
}
