<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M46ConditionalsPackage as Package;
use App\Support\TheoryContentRenderer;
use App\Support\TheoryContentSource;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Js;
use RuntimeException;
use Tests\TestCase;

/** Independent author-master expectations; unsaved models and isolated SQLite only. */
class M46ConditionalsPackageTest extends TestCase
{
    private const CONTENT = ['forms-grid', 'usage-panels', 'comparison-table', 'mistakes-grid', 'summary-list'];

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('uk');
        $this->withoutVite();
    }

    private function master(): array
    {
        // Do not use definition()/presentation() to generate expected learning content.
        return json_decode(file_get_contents(base_path('docs/content/m46-conditionals.v1.0.1.json')), true, flags: JSON_THROW_ON_ERROR);
    }

    private function blocks(array $definition): Collection
    {
        $owner = $definition['seeder']['class'];
        $source = $definition['page'];
        $subtitle = ['type' => 'subtitle', 'column' => 'header', 'body' => $source['subtitle_html'],
            'level' => $source['subtitle_level'] ?? null, 'uuid_key' => $source['subtitle_uuid_key'] ?? 'subtitle'];
        $blocks = [];
        foreach ([$subtitle, ...$source['blocks']] as $position => $config) {
            $uuid = M26DetailPackage::uuid($owner, $config, $position);
            $block = new TextBlock;
            $block->setRawAttributes([
                'id' => 460000 + (int) hexdec(substr(str_replace('-', '', $uuid), 0, 6)),
                'page_id' => 460, 'page_category_id' => 46,
                'uuid' => $uuid, 'seeder' => $owner, 'locale' => 'uk',
                'type' => $config['type'], 'column' => $config['column'] ?? null,
                'heading' => $config['heading'] ?? null, 'level' => $config['level'] ?? null,
                'css_class' => $config['css_class'] ?? null, 'sort_order' => $position, 'body' => $config['body'],
                'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00',
            ], true);
            $block->setRelation('tags', new Collection);
            $block->setRelation('page', null);
            $blocks[] = $block;
        }

        return new Collection($blocks);
    }

    private function page(array $definition, ?Collection $blocks = null): Page
    {
        $page = new Page;
        $page->setRawAttributes(['id' => 460, 'page_category_id' => 46, 'type' => 'theory',
            'seeder' => $definition['seeder']['class'], 'slug' => $definition['slug'],
            'title' => $definition['page']['title'], 'text' => $definition['page']['subtitle_text']], true);
        $page->setRelation('textBlocks', $blocks ?? $this->blocks($definition));
        $page->setRelation('tags', new Collection);
        $page->setRelation('category', null);

        return $page;
    }

    private function block(array $target, int $slot): TextBlock
    {
        return $this->blocks($target['after'])[$slot + 1];
    }

    private function render(TextBlock $block, bool $context = true): string
    {
        app('view')->flushState();

        return view('theory.partials.content-block', [
            'block' => $block, 'theoryCanonical' => true, 'm42StyleContext' => $context,
            'practiceQuestions' => collect(), 'lessonLinks' => [],
        ])->render();
    }

    private function xpath(string $html): DOMXPath
    {
        // libxml's HTML4 parser does not reliably preserve Alpine's @ prefix.
        // Rename only the attribute name for inspection, never its raw JS value.
        $html = preg_replace('/(?<=\\s)@click(?=\\s*=)/', 'data-test-click', $html);
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<!doctype html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return new DOMXPath($document);
    }

    /** Whitespace only: punctuation, case, formulas and words remain exact. */
    private function text(DOMNode|string $value): string
    {
        if (is_string($value)) {
            $value = $this->xpath($value)->query('//body')->item(0);
        }
        $parts = [];
        $walk = function (DOMNode $node) use (&$walk, &$parts): void {
            if ($node->nodeType === XML_TEXT_NODE) { $parts[] = $node->textContent; return; }
            if ($node instanceof DOMElement && in_array($node->tagName, ['script', 'style'], true)) { return; }
            foreach ($node->childNodes as $child) { $walk($child); }
        };
        $walk($value);

        return trim(preg_replace('/\s+/u', ' ', implode(' ', $parts)));
    }

    private function assertLearningFields(DOMNode $rendered, array $fields, string $context): void
    {
        $actual = $this->text($rendered);
        $missing = [];
        foreach ($fields as $path => $field) {
            if ($field !== '' && !str_contains($actual, $this->text($field))) { $missing[$path] = $field; }
        }
        self::assertSame([], $missing, $context.' lost or changed author fields');
    }

    private function fields(array $data, array $names): array
    {
        return array_intersect_key($data, array_flip($names));
    }

    public function test_frozen_master_projects_only_the_three_existing_uk_owners_and_keeps_old_slots(): void
    {
        [$before, $source, $loadedMaster] = Package::load();
        $master = $this->master();
        self::assertSame($master, $loadedMaster);
        self::assertSame('1.0.1', $master['version']);
        self::assertSame(['zero-conditional', 'first-conditional', 'second-conditional'], array_column($master['lessons'], 'slug'));
        Package::validate($before, $source, $master);

        foreach ($master['lessons'] as $i => $lesson) {
            $old = $before['targets'][$i]['before'];
            $target = $source['targets'][$i];
            self::assertSame($target['after'], Package::definition($lesson, $old));
            self::assertSame(array_diff_key($old['page'], array_flip(['blocks', 'subtitle_html', 'subtitle_text'])),
                array_diff_key($target['after']['page'], array_flip(['blocks', 'subtitle_html', 'subtitle_text'])));
            foreach ($lesson['blocks'] as $slot => $authorBlock) {
                $config = $target['after']['page']['blocks'][$slot];
                $data = json_decode($config['body'], true, flags: JSON_THROW_ON_ERROR);
                self::assertSame($authorBlock['key'], $data['m46_native_v1']['key']);
                $originalSlot = $authorBlock['existing_source_index'] ?? null;
                if (is_int($originalSlot)) {
                    self::assertSame($originalSlot, $slot, 'Existing source slots are not reordered');
                    self::assertSame(array_diff_key($old['page']['blocks'][$originalSlot], ['body' => true]),
                        array_diff_key($config, ['body' => true]), 'Continuing UUID/column/level/tags stay exact');
                }
            }
        }
        foreach (['owner', 'body', 'key', 'locale'] as $change) {
            $badSource = $source; $badMaster = $master;
            if ($change === 'owner') { $badSource['targets'][0]['identity'] = 'ForeignOwner'; }
            if ($change === 'body') { $badSource['targets'][0]['after']['page']['blocks'][1]['body'] = '{}'; }
            if ($change === 'key') { $badSource['targets'][0]['block_keys'][1] = 'other-form'; }
            if ($change === 'locale') { $badMaster['lessons'][0]['locale'] = 'en'; }
            try {
                Package::validate($before, $badSource, $badMaster);
                self::fail('Changed '.$change.' passed immutable projection validation');
            } catch (RuntimeException $error) {
                self::assertStringContainsString('M46', $error->getMessage());
            }
        }
    }

    public function test_actual_universal_render_keeps_every_form_point_table_translation_and_summary(): void
    {
        [, $source] = Package::load();
        $totals = [];
        foreach ($this->master()['lessons'] as $i => $lesson) {
            $count = ['forms' => 0, 'usage' => 0, 'table_rows' => []];
            foreach ($lesson['blocks'] as $slot => $authorBlock) {
                if (!in_array($authorBlock['type'], self::CONTENT, true)) { continue; }
                $block = $this->block($source['targets'][$i], $slot);
                $snapshot = $block->getAttributes();
                $html = $this->render($block);
                $xp = $this->xpath($html);
                $section = $xp->query('//section[@data-theory-component="section" and @id="block-'.$block->id.'"]')->item(0);
                self::assertNotNull($section, $authorBlock['key'].' must use the actual universal section');
                self::assertSame(1, substr_count($html, '<!-- theory.content:node -->'));
                self::assertStringNotContainsString('data-theory-render-fallback=', $html);
                $data = $authorBlock['data'];
                $this->assertLearningFields($section, $this->fields($data, ['title', 'intro', 'outro', 'warning']), $authorBlock['key']);

                if ($authorBlock['type'] === 'forms-grid') {
                    $cards = $xp->query('.//*[@data-theory-component="form"]', $section);
                    self::assertCount(count($data['items']), $cards);
                    foreach ($data['items'] as $j => $item) {
                        $fields = $this->fields($item, ['label', 'title', 'subtitle']);
                        foreach ($item['rules'] ?? [] as $k => $rule) {
                            foreach ($this->fields($rule, ['label', 'formula', 'text', 'example']) as $key => $value) {
                                $fields['rules/'.$k.'/'.$key] = $value;
                            }
                        }
                        $this->assertLearningFields($cards->item($j), $fields, $authorBlock['key'].' form '.$j);
                    }
                    $count['forms'] += $cards->length;
                } elseif ($authorBlock['type'] === 'usage-panels') {
                    self::assertSame('native', $section->getAttribute('data-theory-reference'));
                    $panels = $xp->query('.//*[@data-theory-point-panel]', $section);
                    self::assertCount(count($data['sections']), $panels);
                    foreach ($data['sections'] as $j => $point) {
                        $panel = $panels->item($j);
                        self::assertSame($point['color'], $panel->getAttribute('data-theory-accent'));
                        $fields = $this->fields($point, ['label', 'description', 'note']);
                        foreach ($point['examples'] ?? [] as $k => $example) {
                            $fields['examples/'.$k.'/en'] = $example['en'];
                            $fields['examples/'.$k.'/ua'] = $example['ua'];
                        }
                        $this->assertLearningFields($panel, $fields, $authorBlock['key'].' point '.$j);
                        $en = $xp->query('.//*[@data-theory-component="example"]//p[@lang="en"]', $panel);
                        $uk = $xp->query('.//*[@data-theory-component="example"]//p[@lang="uk"]', $panel);
                        self::assertSame(array_column($point['examples'] ?? [], 'en'), array_map(fn ($n) => $this->text($n), iterator_to_array($en)));
                        self::assertSame(array_column($point['examples'] ?? [], 'ua'), array_map(fn ($n) => $this->text($n), iterator_to_array($uk)));
                    }
                    $count['usage'] += $panels->length;
                } elseif ($authorBlock['type'] === 'comparison-table') {
                    $rows = $xp->query('.//*[@data-theory-component="table"]//tbody/tr', $section);
                    self::assertCount(count($data['rows']), $rows);
                    foreach ($data['rows'] as $j => $row) {
                        $cells = $xp->query('./td', $rows->item($j));
                        self::assertSame(array_map(fn ($value) => $this->text($value), [$row['en'], $row['ua'], $row['note']]),
                            array_map(fn ($n) => $this->text($n), iterator_to_array($cells)), $authorBlock['key'].' table row '.$j);
                    }
                    $count['table_rows'][] = $rows->length;
                } elseif ($authorBlock['type'] === 'mistakes-grid') {
                    $cards = $xp->query('.//*[@data-theory-component="mistake"]', $section);
                    self::assertCount(count($data['items']), $cards);
                    foreach ($data['items'] as $j => $item) {
                        $this->assertLearningFields($cards->item($j),
                            $this->fields($item, ['label', 'title', 'wrong_en', 'wrong_ua', 'right_en', 'right_ua', 'hint']),
                            $authorBlock['key'].' correction '.$j);
                        self::assertSame([$item['wrong_ua'], $item['right_ua']],
                            array_map(fn ($n) => $this->text($n), iterator_to_array($xp->query('.//p[@lang="uk"]', $cards->item($j)))));
                    }
                } else {
                    $this->assertLearningFields($section, $data['items'], $authorBlock['key'].' summary');
                }
                self::assertSame($snapshot, $block->getAttributes(), 'Rendering does not mutate source models');
                self::assertFalse($block->isDirty());
            }
            $totals[$lesson['slug']] = $count;
        }
        self::assertSame([
            'zero-conditional' => ['forms' => 4, 'usage' => 6, 'table_rows' => [2]],
            'first-conditional' => ['forms' => 6, 'usage' => 4, 'table_rows' => []],
            'second-conditional' => ['forms' => 4, 'usage' => 8, 'table_rows' => [3, 2]],
        ], $totals);
    }

    public function test_full_theory_page_retains_the_master_hero_examples_and_the_single_owned_closed_detail(): void
    {
        [, $source] = Package::load();
        $detailCounts = [];
        foreach ($this->master()['lessons'] as $i => $lesson) {
            $page = $this->page($source['targets'][$i]['after']);
            app('view')->flushState();
            $html = view('theory.show', ['page' => $page, 'categories' => collect(),
                'categoryPages' => collect(), 'topicTests' => collect(), 'selectedCategory' => null])->render();
            $xp = $this->xpath($html);
            $hero = array_values(array_filter($lesson['blocks'], fn ($b) => $b['type'] === 'hero'))[0]['data'];
            $heroNode = $xp->query('//section[contains(concat(" ", normalize-space(@class), " "), " theory-hero ")]')->item(0);
            self::assertNotNull($heroNode);
            $this->assertLearningFields($heroNode, ['intro' => $hero['intro']], $lesson['slug'].' hero');
            $cards = $xp->query('//section[contains(concat(" ", normalize-space(@class), " "), " theory-rules ")]/article');
            self::assertCount(count($hero['rules']), $cards);
            foreach ($hero['rules'] as $j => $rule) {
                $this->assertLearningFields($cards->item($j), $this->fields($rule, ['label', 'text', 'example']), $lesson['slug'].' hero rule '.$j);
            }
            $details = $xp->query('//details[@data-theory-details]');
            $detailCounts[] = $details->length;
            foreach ($lesson['details'] as $detail) {
                self::assertSame('/items/0', $detail['owner_pointer']);
                $owned = $xp->query('//*[@data-theory-section="'.$detail['key'].'"]/details[@data-theory-details]');
                self::assertCount(1, $owned);
                self::assertFalse($owned->item(0)->hasAttribute('open'));
                self::assertCount(1, $xp->query('ancestor::*[@data-theory-component="form"]', $owned->item(0)));
                $fragment = $detail['fragment']['data']['sections'][0];
                $fields = $this->fields($fragment, ['label', 'description', 'note']);
                foreach ($fragment['examples'] as $j => $example) {
                    $fields['example/'.$j.'/en'] = $example['en']; $fields['example/'.$j.'/ua'] = $example['ua'];
                }
                $this->assertLearningFields($owned->item(0), $fields, 'Only the matching form owns the complete willingness explanation');
                self::assertCount(1, $xp->query('.//*[@id="'.$detail['key'].'-detail"]', $owned->item(0)));
            }
        }
        self::assertSame([0, 1, 0], $detailCounts);
    }

    public function test_all_eighteen_tasks_keep_their_raw_controls_aliases_and_fully_translated_feedback(): void
    {
        [, $source] = Package::load();
        $taskCount = 0; $optionCount = 0;
        foreach ($this->master()['lessons'] as $i => $lesson) {
            foreach ($lesson['blocks'] as $slot => $authorBlock) {
                if ($authorBlock['type'] !== 'practice-set') { continue; }
                $data = $authorBlock['data'];
                $block = $this->block($source['targets'][$i], $slot);
                $xp = $this->xpath($this->render($block));
                $factories = $xp->query('//*[@x-data and starts-with(@x-data, "theoryPracticeSet(")]');
                self::assertCount(1, $factories);
                $factory = $factories->item(0);
                self::assertSame(1, preg_match("/\\AtheoryPracticeSet\\(JSON\\.parse\\('(.*)'\\)\\)\\z/s", $factory->getAttribute('x-data'), $matches));
                $json = json_decode('"'.$matches[1].'"', true, flags: JSON_THROW_ON_ERROR);
                $payload = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
                self::assertSame($data['selects'], $payload['selects'], 'Select raw values, aliases and feedback cannot be display-rewritten');
                self::assertSame($data['choices'], $payload['choices'], 'Choice raw values, aliases and feedback cannot be display-rewritten');
                self::assertSame([], $payload['inputs']);
                self::assertSame([], $payload['rephrase']);
                self::assertCount(6, $xp->query('.//label', $factory));

                foreach (['selects' => 'selectAnswers', 'choices' => 'choiceAnswers'] as $group => $answers) {
                    foreach ($data[$group] as $j => $task) {
                        $taskCount++;
                        self::assertContains($task['answer'], $task['accepted']);
                        $this->assertLearningFields($factory, $this->fields($task, ['label', 'prompt']), $lesson['slug'].' task '.$task['source_index']);
                        foreach ($task['options'] ?? $data['choice_options'] as $option) {
                            $optionCount++;
                            $key = mb_strtolower(trim($option));
                            self::assertArrayHasKey($key, $task['feedback']);
                            self::assertMatchesRegularExpression('/[А-ЯІЇЄҐа-яіїєґ]/u', $task['feedback'][$key]);
                            self::assertStringContainsString('Переклад', $task['feedback'][$key], 'Every offered answer, not only the correct one, has its translation');
                            $expectedClick = $answers.'['.$j.'] = '.Js::from($option);
                            $matched = [];
                            foreach ($xp->query('.//button', $factory) as $button) {
                                if ($button->getAttribute('data-test-click') === $expectedClick) { $matched[] = $button; }
                            }
                            self::assertCount(1, $matched, $lesson['slug'].' raw option '.$option);
                            self::assertSame($option, $this->text($matched[0]));
                        }
                        $foundFeedback = [];
                        foreach ($xp->query('.//*[@x-text]', $factory) as $feedback) {
                            if ($feedback->getAttribute('x-text') === "feedbackText('".$group."', ".$j.")") { $foundFeedback[] = $feedback; }
                        }
                        self::assertCount(1, $foundFeedback);
                        self::assertSame("isChecked('".$group."') && hasAnswer('".$group."', ".$j.")",
                            $foundFeedback[0]->parentNode->getAttribute('x-show'));
                    }
                }
                $clicks = [];
                foreach ($xp->query('.//button', $factory) as $button) { $clicks[] = $button->getAttribute('data-test-click'); }
                foreach (["check('selects')", "resetGroup('selects')", "check('choices')", "resetGroup('choices')"] as $action) {
                    self::assertContains($action, $clicks);
                }
            }
        }
        self::assertSame(18, $taskCount);
        self::assertSame(45, $optionCount);
    }

    public function test_foreign_bindings_and_designs_are_rejected_and_never_hide_the_stored_detail_rule(): void
    {
        [, $source] = Package::load();
        $master = $this->master();
        $first = $source['targets'][1];
        $slot = array_search('m46-first-forms', array_column($master['lessons'][1]['blocks'], 'key'), true);
        $block = $this->block($first, $slot);
        $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
        $presentation = Package::presentation($block, $data);
        self::assertNotNull($presentation);
        self::assertSame([0], array_keys($presentation['points']));
        $detail = $master['lessons'][1]['details'][0]['fragment']['data']['sections'][0];
        $detailFields = $this->fields($detail, ['description', 'note']);
        foreach ($detail['examples'] as $i => $example) {
            $detailFields['example/'.$i.'/en'] = $example['en']; $detailFields['example/'.$i.'/ua'] = $example['ua'];
        }
        foreach (['seeder' => 'ForeignOwner', 'locale' => 'en', 'uuid' => 'foreign-uuid',
            'sort_order' => 99, 'column' => 'footer', 'heading' => 'Changed heading', 'level' => 'C2', 'css_class' => 'other'] as $field => $value) {
            $foreign = clone $block; $foreign->setAttribute($field, $value);
            self::assertNull(Package::presentation($foreign, $data), 'Reject foreign '.$field);
            $xp = $this->xpath($this->render($foreign));
            self::assertCount(0, $xp->query('//details[@data-theory-details]'));
            $this->assertLearningFields($xp->query('//body')->item(0), $detailFields, 'Complete fallback for '.$field);
        }
        foreach (['body', 'key', 'missing-marker'] as $change) {
            $changed = $data;
            if ($change === 'body') { $changed['items'][0]['subtitle'] .= ' Додана умова.'; }
            if ($change === 'key') { $changed['m46_native_v1']['key'] = 'foreign-key'; }
            if ($change === 'missing-marker') { unset($changed['m46_native_v1']); }
            $foreign = clone $block; $foreign->body = M26DetailPackage::json($changed);
            self::assertNull(Package::presentation($foreign, $changed), 'Reject changed '.$change);
            $xp = $this->xpath($this->render($foreign));
            self::assertCount(0, $xp->query('//details[@data-theory-details]'));
            $this->assertLearningFields($xp->query('//body')->item(0), $detailFields, 'Complete fallback for '.$change);
        }
        self::assertNull(TheoryContentSource::resolve($block)['presentation'], 'Theory opt-in belongs to the caller');
        self::assertNull(TheoryContentRenderer::nativeNode('engram.theory.blocks-v3.forms-grid', [
            'block' => $block, 'data' => $data, 'theoryCanonical' => false,
        ]), 'A package marker cannot opt a course into canonical theory rendering');
        $xp = $this->xpath($this->render($block, false));
        self::assertCount(0, $xp->query('//details[@data-theory-details]'));
        $this->assertLearningFields($xp->query('//body')->item(0), $detailFields, 'Complete text without caller opt-in');

        $design = $presentation['m42_native_design'];
        self::assertTrue(Package::validDesign($design));
        foreach (['package' => 'm42', 'identity' => 'ForeignOwner', 'locale' => 'pl', 'uuid' => 'foreign',
            'source_index' => 999, 'body_sha256' => str_repeat('0', 64), 'component' => 'usage-panels'] as $field => $value) {
            $foreign = array_replace($design, [$field => $value]);
            self::assertFalse(Package::validDesign($foreign), 'Reject forged design '.$field);
            self::assertNull(Package::pointPanels($foreign));
            self::assertNull(Package::correction([], $foreign));
        }
        self::assertTrue(Package::validDesign($design), 'Rejected input cannot poison valid cached bindings');
    }

    public function test_courses_restore_exact_old_uk_rows_and_keep_non_uk_rows_and_input_models_untouched(): void
    {
        [$before, $source] = Package::load();
        foreach ($source['targets'] as $i => $target) {
            $old = $before['targets'][$i]['before'];
            $oldBlocks = $this->blocks($old);
            $current = $this->blocks($target['after']);
            foreach (['en', 'pl'] as $locale) {
                foreach ($oldBlocks as $original) {
                    $other = clone $original;
                    $other->locale = $locale;
                    $other->uuid = $locale.'-'.$other->uuid;
                    $other->id += $locale === 'en' ? 20000000 : 40000000;
                    $other->syncOriginal();
                    $current->push($other);
                }
            }
            $page = $this->page($target['after'], $current);
            $pageBefore = $page->getAttributes(); $rawBefore = $page->getRawOriginal();
            $rowsBefore = $current->map(fn ($row) => $row->getAttributes())->all();
            $preserved = Package::preserveCoursePage($page);
            self::assertNotSame($page, $preserved);
            self::assertSame($old['page']['subtitle_text'], $preserved->getRawOriginal('text'));
            $uk = $preserved->textBlocks->where('locale', 'uk')->values();
            self::assertCount(7, $uk);
            self::assertSame($oldBlocks->map(fn ($b) => $b->getAttributes())->all(), $uk->map(fn ($b) => $b->getAttributes())->all());
            foreach (['en', 'pl'] as $locale) {
                $originals = $current->where('locale', $locale)->keyBy('uuid');
                $copies = $preserved->textBlocks->where('locale', $locale)->keyBy('uuid');
                self::assertSame($originals->keys()->all(), $copies->keys()->all());
                foreach ($originals as $uuid => $original) { self::assertSame($original, $copies[$uuid], 'Non-UK models are not rewritten'); }
            }
            foreach ($uk as $copy) { self::assertNotSame($current->firstWhere('uuid', $copy->uuid), $copy); }
            // Exercise the unchanged course partial with the caller-owned restored page.
            $oldPage = $this->page($old, $oldBlocks);
            $coursePage = clone $preserved; $coursePage->setRelation('textBlocks', $uk);
            app('view')->flushState();
            $expected = view('courses.partials.theory-page-content', ['page' => $oldPage])->render();
            app('view')->flushState();
            self::assertSame($expected, view('courses.partials.theory-page-content', ['page' => $coursePage])->render());
            self::assertSame($pageBefore, $page->getAttributes());
            self::assertSame($rawBefore, $page->getRawOriginal());
            self::assertSame($rowsBefore, $current->map(fn ($b) => $b->getAttributes())->all());
            self::assertFalse($page->isDirty());

            foreach (['owner', 'subtitle', 'body', 'missing-row', 'duplicate-row'] as $change) {
                $foreignPage = clone $page;
                $foreignBlocks = new Collection($current->map(fn ($row) => clone $row)->all());
                if ($change === 'owner') {
                    $foreignPage->setRawAttributes(array_replace($pageBefore, ['seeder' => 'ForeignOwner']), true);
                } elseif ($change === 'subtitle') {
                    $foreignBlocks->first(fn ($b) => $b->locale === 'uk' && $b->type === 'subtitle')->body .= ' changed';
                } elseif ($change === 'body') {
                    $foreignBlocks->first(fn ($b) => $b->locale === 'uk' && $b->type === 'forms-grid')->body = '{}';
                } elseif ($change === 'missing-row') {
                    $foreignBlocks = $foreignBlocks->slice(1)->values();
                } else {
                    $foreignBlocks->push(clone $foreignBlocks->first());
                }
                $foreignPage->setRelation('textBlocks', $foreignBlocks);
                self::assertSame($foreignPage, Package::preserveCoursePage($foreignPage), 'Course guard rejects '.$change);
            }
        }
        self::assertNull(Package::preserveCoursePage(null));
    }
}
