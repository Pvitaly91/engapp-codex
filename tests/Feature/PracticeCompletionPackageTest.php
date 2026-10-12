<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M46ConditionalsPackage;
use App\Support\M47ConditionalsPackage;
use App\Support\M48ConditionalsPackage;
use App\Support\M49AdviceModalsPackage;
use App\Support\PracticeCompletionPackage as Package;
use App\Support\TheoryContentSource;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Js;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Supplemental data and unsaved-model rendering only; no working database or environment. */
class PracticeCompletionPackageTest extends TestCase
{
    private const SLUGS = [
        'zero-conditional', 'first-conditional', 'second-conditional',
        'third-conditional', 'mixed-conditionals', 'unless-provided-as-long-as',
        'in-case', 'wish-if-only', 'would-rather', 'had-better', 'can-could', 'may-might',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('uk');
        $this->withoutVite();
    }

    private function release(int $index): array
    {
        $provider = match (true) {
            $index < 3 => M46ConditionalsPackage::class,
            $index < 6 => M47ConditionalsPackage::class,
            $index < 9 => M48ConditionalsPackage::class,
            default => M49AdviceModalsPackage::class,
        };
        [$before, $source, $master] = $provider::load();
        $provider::validate($before, $source, $master);
        $slot = $index % 3;

        return [$provider, $source['targets'][$slot]['after']];
    }

    private function block(array $target, bool $after = true): TextBlock
    {
        $source = $target[$after ? 'after' : 'before']['practice'];
        $block = new TextBlock;
        $block->setRawAttributes($source + [
            'id' => 1000000 + $target['page']['id'],
            'page_id' => $target['page']['id'], 'page_category_id' => $target['page']['page_category_id'],
            'seeder' => $target['identity'], 'locale' => 'uk',
            'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00',
        ], true);
        $block->setRelation('tags', new Collection);
        $block->setRelation('page', null);

        return $block;
    }

    private function page(array $target, int $index, bool $after): Page
    {
        [, $definition] = $this->release($index);
        $source = $definition['page'];
        $configs = [['type' => 'subtitle', 'column' => 'header', 'body' => $source['subtitle_html'],
            'level' => $source['subtitle_level'] ?? null, 'uuid_key' => $source['subtitle_uuid_key'] ?? 'subtitle'],
            ...$source['blocks']];
        $blocks = [];
        foreach ($configs as $position => $config) {
            $uuid = M26DetailPackage::uuid($target['identity'], $config, $position);
            $block = new TextBlock;
            $block->setRawAttributes([
                'id' => 2000000 + $target['page']['id'] * 100 + $position,
                'uuid' => $uuid, 'page_id' => $target['page']['id'],
                'page_category_id' => $target['page']['page_category_id'],
                'seeder' => $target['identity'], 'locale' => 'uk',
                'type' => $config['type'], 'column' => $config['column'] ?? null,
                'heading' => $config['heading'] ?? null, 'level' => $config['level'] ?? null,
                'css_class' => $config['css_class'] ?? null, 'sort_order' => $position,
                'body' => $after && $uuid === $target['before']['practice']['uuid']
                    ? $target['after']['practice']['body'] : $config['body'],
                'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00',
            ], true);
            $block->setRelation('tags', new Collection);
            $block->setRelation('page', null);
            $blocks[] = $block;
        }
        $page = new Page;
        $page->setRawAttributes(array_diff_key($target['page'], ['locale' => true]) + [
            'seeder' => $target['identity'], 'text' => $source['subtitle_text'],
        ], true);
        $page->setRelation('textBlocks', new Collection($blocks));
        $page->setRelation('tags', new Collection);
        $page->setRelation('category', null);

        return $page;
    }

    private function projection(array $before, array $master): array
    {
        $targets = [];
        foreach ($before['targets'] as $i => $original) {
            $target = $original; unset($target['practice']);
            $after = $original['practice'];
            $after['body'] = Package::afterBody($original['practice']['body'], $master['lessons'][$i]);
            $target['before'] = ['practice' => $original['practice']];
            $target['after'] = ['practice' => $after];
            $targets[] = $target;
        }

        return ['schema_version' => 1, 'base_sha' => Package::BASE_SHA, 'targets' => $targets];
    }

    private function rejects(callable $operation): void
    {
        $caught = null;
        try { $operation(); } catch (RuntimeException $error) { $caught = $error; }
        self::assertInstanceOf(RuntimeException::class, $caught, 'An unsupported supplement was accepted.');
        self::assertStringContainsString('Practice completion', $caught->getMessage());
    }

    private function render(TextBlock $block): string
    {
        app('view')->flushState();

        return view('theory.partials.content-block', [
            'block' => $block, 'theoryCanonical' => true, 'm42StyleContext' => true,
            'practiceQuestions' => collect(), 'lessonLinks' => [],
        ])->render();
    }

    private function xpath(string $html): DOMXPath
    {
        $html = preg_replace('/(?<=\\s)@click(?=\\s*=)/', 'data-test-click', $html);
        $html = preg_replace('/(?<=\\s)@input(?=\\s*=)/', 'data-test-input', $html);
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

    private function text(DOMNode $node): string
    {
        $parts = [];
        $walk = function (DOMNode $value) use (&$walk, &$parts): void {
            if ($value->nodeType === XML_TEXT_NODE) { $parts[] = $value->textContent; return; }
            if ($value instanceof DOMElement && in_array($value->tagName, ['script', 'style'], true)) { return; }
            foreach ($value->childNodes as $child) { $walk($child); }
        };
        $walk($node);

        return trim(preg_replace('/\s+/u', ' ', implode(' ', $parts)));
    }

    public function test_exact_twelve_supplements_preserve_all_seventy_two_historical_tasks(): void
    {
        [$before, $patch, $master] = Package::load();
        Package::validate($before, $patch, $master);
        self::assertSame(self::SLUGS, array_column($patch['targets'], 'slug'));
        self::assertSame(Package::BASE_SHA, $master['base_sha']);
        self::assertSame('1.0.1', $master['version']);
        self::assertSame($patch, $this->projection($before, $master));
        $oldTasks = 0; $newTasks = 0; $ids = [];
        foreach ($patch['targets'] as $i => $target) {
            [, $definition] = $this->release($i);
            $historicalPractice = array_values(array_filter($definition['page']['blocks'],
                fn ($block) => $block['type'] === 'practice-set'));
            self::assertCount(1, $historicalPractice);
            self::assertSame($historicalPractice[0]['body'], $target['before']['practice']['body']);
            self::assertSame($i < 10 ? ['conditionals'] : ['modal-verbs'], $target['ancestry']);
            self::assertIsInt($target['page']['id']);
            self::assertGreaterThan(0, $target['page']['id']);
            self::assertSame(array_diff_key($target['before']['practice'], ['body' => true]),
                array_diff_key($target['after']['practice'], ['body' => true]));

            $old = json_decode($target['before']['practice']['body'], true, flags: JSON_THROW_ON_ERROR);
            $new = json_decode($target['after']['practice']['body'], true, flags: JSON_THROW_ON_ERROR);
            self::assertSame([], $old['inputs'] ?? []);
            self::assertArrayNotHasKey('input_title', $old);
            self::assertArrayNotHasKey('input_intro', $old);
            self::assertSame($old['selects'], $new['selects']);
            self::assertSame($old['choices'], $new['choices']);
            self::assertSame(range(1, 6), array_column([...$new['selects'], ...$new['choices']], 'source_index'));
            $restored = $new;
            unset($restored['input_title'], $restored['input_intro'], $restored['inputs'], $restored['practice_completion_v1']);
            if (array_key_exists('inputs', $old)) { $restored['inputs'] = $old['inputs']; }
            self::assertEquals($old, $restored, 'Only the four explicit supplemental keys are added.');
            self::assertSame($master['lessons'][$i]['inputs'], $new['inputs']);
            self::assertSame([7, 8], array_column($new['inputs'], 'source_index'));
            self::assertSame(['version' => 1, 'source_before_sha256' => hash('sha256', $target['before']['practice']['body']),
                'package' => Package::PACKAGE], $new['practice_completion_v1']);
            foreach ($new['inputs'] as $input) {
                $ids[] = $input['id'];
                self::assertContains($input['answer'], $input['accepted']);
                foreach ($input['accepted'] as $answer) {
                    self::assertArrayHasKey(mb_strtolower(trim($answer)), $input['feedback']);
                    self::assertMatchesRegularExpression('/[А-ЯІЇЄҐа-яіїєґ]/u', $input['feedback'][mb_strtolower(trim($answer))]);
                }
            }
            $oldTasks += count($old['selects']) + count($old['choices']);
            $newTasks += count($new['inputs']);
        }
        self::assertSame(72, $oldTasks);
        self::assertSame(24, $newTasks);
        self::assertCount(24, array_unique($ids));
    }

    public function test_closed_fields_missing_inputs_and_untranslated_aliases_fail_even_with_coherent_projection(): void
    {
        [$before, , $master] = Package::load();
        foreach (['lesson-extra', 'before-extra', 'page-extra', 'input-extra', 'input-view', 'input-style',
            'input-missing', 'input-count', 'input-source-index', 'id-collision', 'alias-no-feedback',
            'empty-bank', 'empty-condition', 'page-id-missing', 'page-id-invalid'] as $change) {
            $badBefore = $before; $badMaster = $master;
            if ($change === 'lesson-extra') { $badMaster['lessons'][0]['editorial'] = 'Not a runtime field'; }
            if ($change === 'before-extra') { $badBefore['targets'][0]['unknown'] = 'Not captured scope'; }
            if ($change === 'page-extra') { $badBefore['targets'][0]['page']['unknown'] = 'Not a page binding'; }
            if ($change === 'input-extra') { $badMaster['lessons'][0]['inputs'][0]['hint'] = 'Answer leak'; }
            if ($change === 'input-view') { $badMaster['lessons'][0]['inputs'][0]['view'] = 'theory.other'; }
            if ($change === 'input-style') { $badMaster['lessons'][0]['inputs'][0]['after'] = '<span style="color:red">Умова</span>'; }
            if ($change === 'input-missing') { unset($badMaster['lessons'][0]['inputs'][0]['after']); }
            if ($change === 'input-count') { array_pop($badMaster['lessons'][0]['inputs']); }
            if ($change === 'input-source-index') { $badMaster['lessons'][0]['inputs'][0]['source_index'] = 1; }
            if ($change === 'id-collision') { $badMaster['lessons'][0]['inputs'][1]['id'] = $badMaster['lessons'][0]['inputs'][0]['id']; }
            if ($change === 'alias-no-feedback') { $badMaster['lessons'][0]['inputs'][0]['accepted'][] = 'A new unchecked alias.'; }
            if ($change === 'empty-bank') { $badMaster['lessons'][0]['inputs'][0]['before'] = ''; }
            if ($change === 'empty-condition') { $badMaster['lessons'][0]['inputs'][0]['after'] = ''; }
            if ($change === 'page-id-missing') { unset($badBefore['targets'][0]['page']['id']); }
            if ($change === 'page-id-invalid') { $badBefore['targets'][0]['page']['id'] = 0; }
            $badPatch = $this->projection($badBefore, $badMaster);
            $this->rejects(fn () => Package::validate($badBefore, $badPatch, $badMaster));
        }
        $existing = json_decode($before['targets'][0]['practice']['body'], true);
        $existing['inputs'] = [['answer' => 'Existing manual task']];
        $this->rejects(fn () => Package::afterBody(M26DetailPackage::json($existing), $master['lessons'][0]));
        $this->rejects(fn () => Package::afterBody(
            Package::afterBody($before['targets'][0]['practice']['body'], $master['lessons'][0]), $master['lessons'][0]));
    }

    public function test_hash_binding_rejects_real_source_mutation_without_touching_frozen_files(): void
    {
        $root = storage_path('app/practice-completion-package-'.bin2hex(random_bytes(8)));
        File::makeDirectory($root, 0700, true);
        IsolatedTestEnvironment::assertOwnedPath($root);
        $paths = [Package::BEFORE => Package::BEFORE_SHA, Package::SOURCE => Package::SOURCE_SHA, Package::MASTER => Package::MASTER_SHA];
        foreach ($paths as $path => $hash) {
            self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $hash);
            self::assertSame($hash, hash_file('sha256', base_path($path)));
            File::makeDirectory(dirname($root.'/'.$path), 0700, true, true);
            File::copy(base_path($path), $root.'/'.$path);
        }
        self::assertSame(Package::load(), Package::load($root));
        foreach ($paths as $path => $hash) {
            $bytes = File::get($root.'/'.$path);
            $value = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
            if ($path === Package::MASTER) { $value['lessons'][0]['inputs'][0]['after'] .= ' Інша умова.'; }
            elseif ($path === Package::BEFORE) { $value['targets'][0]['page']['id']++; }
            else { $value['targets'][0]['after']['practice']['body'] .= ' changed'; }
            File::put($root.'/'.$path, M26DetailPackage::json($value));
            $this->rejects(fn () => Package::load($root));
            File::put($root.'/'.$path, $bytes);
            self::assertSame($hash, hash_file('sha256', base_path($path)));
        }
    }

    public function test_exact_after_binding_is_caller_owned_and_rejects_foreign_page_metadata_or_content(): void
    {
        [, $patch] = Package::load();
        foreach ($patch['targets'] as $i => $target) {
            $block = $this->block($target);
            $beforeAttributes = $block->getAttributes(); $beforeOriginal = $block->getRawOriginal();
            $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
            $presentation = Package::presentation($block, $data);
            self::assertNotNull($presentation, $target['slug']);
            self::assertSame($data, $presentation['data']);
            self::assertSame([], $presentation['points']);
            self::assertSame(Package::PACKAGE, $presentation['m42_native_design']['package']);
            self::assertSame('practice-set', $presentation['m42_native_design']['component']);
            self::assertNull(TheoryContentSource::resolve($block)['presentation']);
            $resolved = TheoryContentSource::resolve($block, ['m42StyleContext' => true]);
            self::assertSame($presentation, $resolved['presentation']);
            self::assertSame($data, $resolved['data']);
            self::assertSame('engram.theory.blocks-v3.practice-set', $resolved['view']);

            foreach (['page_id' => $target['page']['id'] + 10000,
                'page_category_id' => $target['page']['page_category_id'] + 10000,
                'seeder' => Package::OWNERS[($i + 1) % 12], 'locale' => 'en', 'uuid' => 'foreign-uuid',
                'type' => 'forms-grid', 'sort_order' => 999, 'heading' => 'Foreign title',
                'column' => 'footer', 'level' => 'C2', 'css_class' => 'foreign-style'] as $field => $value) {
                $foreign = clone $block; $foreign->$field = $value;
                self::assertNull(Package::presentation($foreign, $data), $target['slug'].' rejects '.$field);
            }
            $foreign = clone $block; $foreign->locale = 'pl';
            self::assertNull(Package::presentation($foreign, $data));
            foreach (['condition', 'old-answer', 'alias', 'marker', 'formatting'] as $change) {
                $foreign = clone $block; $altered = $data;
                if ($change === 'condition') { $altered['inputs'][0]['after'] .= ' Змінена умова.'; }
                if ($change === 'old-answer') { $altered['selects'][0]['answer'] = 'Unreviewed answer'; }
                if ($change === 'alias') { $altered['inputs'][0]['accepted'][] = 'Unreviewed alias'; }
                if ($change === 'marker') { $altered['practice_completion_v1']['source_before_sha256'] = str_repeat('0', 64); }
                $foreign->body = $change === 'formatting' ? $block->body."\n" : M26DetailPackage::json($altered);
                self::assertNull(Package::presentation($foreign, $altered), 'Supplemental raw AFTER binding rejects '.$change);
            }
            self::assertNull(Package::presentation($block, array_replace($data, ['input_title' => 'Changed decoded data'])));
            $old = $this->block($target, false);
            self::assertNull(Package::presentation($old, json_decode($old->body, true)));
            $oldResolved = TheoryContentSource::resolve($old, ['m42StyleContext' => true]);
            self::assertNotNull($oldResolved['presentation']);
            self::assertNotSame(Package::PACKAGE, $oldResolved['design']['package']);
            self::assertSame($beforeAttributes, $block->getAttributes());
            self::assertSame($beforeOriginal, $block->getRawOriginal());
        }
    }

    public function test_unchanged_native_renderer_adds_only_third_green_card_with_two_manual_token_tasks(): void
    {
        [, $patch, $master] = Package::load();
        $oldTaskTotal = 0; $newFieldTotal = 0;
        foreach ($patch['targets'] as $i => $target) {
            $block = $this->block($target);
            $old = json_decode($target['before']['practice']['body'], true, flags: JSON_THROW_ON_ERROR);
            $after = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
            $xp = $this->xpath($this->render($block));
            $factories = $xp->query('//*[@x-data and starts-with(@x-data, "theoryPracticeSet(")]');
            self::assertCount(1, $factories);
            $factory = $factories->item(0);
            self::assertSame(1, preg_match("/\\AtheoryPracticeSet\\(JSON\\.parse\\('(.*)'\\)\\)\\z/s", $factory->getAttribute('x-data'), $match));
            $json = json_decode('"'.$match[1].'"', true, flags: JSON_THROW_ON_ERROR);
            $payload = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
            self::assertSame($old['selects'], $payload['selects']);
            self::assertSame($old['choices'], $payload['choices']);
            self::assertSame($master['lessons'][$i]['inputs'], $payload['inputs']);
            self::assertSame([], $payload['rephrase']);
            $cards = $xp->query('.//*[contains(concat(" ", normalize-space(@class), " "), " theory-exercise ")]', $factory);
            self::assertCount(3, $cards);
            $green = $cards->item(2);
            self::assertStringContainsString('border-emerald-100', $green->getAttribute('class'));
            self::assertCount(1, $xp->query('.//h3/span[normalize-space(.)="3"]', $green));
            self::assertStringContainsString($master['lessons'][$i]['input_title'], $this->text($green));
            self::assertStringContainsString($master['lessons'][$i]['input_intro'], $this->text($green));
            self::assertCount(6, $xp->query('.//label', $factory), 'The six original selected-response labels are unchanged.');
            self::assertCount(2, $xp->query('.//input[@type="text"]', $green));
            self::assertCount(0, $xp->query('.//textarea', $green));
            foreach ($after['inputs'] as $j => $task) {
                $field = $xp->query('.//input[@x-model="inputAnswers['.$j.']"]', $green);
                self::assertCount(1, $field);
                self::assertSame('syncInputTokenBank('.$j.')', $field->item(0)->getAttribute('data-test-input'));
                self::assertSame('...', $field->item(0)->getAttribute('placeholder'));
                self::assertFalse($field->item(0)->hasAttribute('value'));
                self::assertCount(1, $xp->query('.//template[@x-for="token in inputTokenBank('.$j.')"]', $green));
                self::assertCount(1, $xp->query('.//button[@data-test-click="appendInputToken(\'inputs\', '.$j.', token)"]', $green));
                self::assertStringContainsString($this->text($this->xpath($task['after'])->query('//body')->item(0)),
                    $this->text($green), 'Full UK task condition stays initially visible.');
                $feedback = $xp->query('.//*[@x-text="feedbackText(\'inputs\', '.$j.')"]', $green);
                self::assertCount(1, $feedback);
                self::assertSame("isChecked('inputs') && hasAnswer('inputs', ".$j.")", $feedback->item(0)->getAttribute('x-show'));
                self::assertSame('', trim($feedback->item(0)->textContent), 'No answer is pre-filled as visible feedback.');
            }
            foreach (['selects' => 'selectAnswers', 'choices' => 'choiceAnswers'] as $group => $answers) {
                foreach ($old[$group] as $j => $task) {
                    foreach ($task['options'] ?? $old['choice_options'] as $option) {
                        $expected = $answers.'['.$j.'] = '.Js::from($option);
                        $found = [];
                        foreach ($xp->query('.//button', $factory) as $button) {
                            if ($button->getAttribute('data-test-click') === $expected) { $found[] = $button; }
                        }
                        self::assertCount(1, $found);
                        self::assertSame($option, $this->text($found[0]));
                    }
                    $oldTaskTotal++;
                }
            }
            $clicks = [];
            foreach ($xp->query('.//button', $green) as $button) { $clicks[] = $button->getAttribute('data-test-click'); }
            self::assertContains("check('inputs')", $clicks);
            self::assertContains("resetGroup('inputs')", $clicks);
            $newFieldTotal += 2;
        }
        self::assertSame(72, $oldTaskTotal);
        self::assertSame(24, $newFieldTotal);
    }

    public function test_course_clones_restore_legacy_pages_without_mutating_theory_or_non_uk_models(): void
    {
        [, $patch] = Package::load();
        foreach ($patch['targets'] as $i => $target) {
            [$provider] = $this->release($i);
            $page = $this->page($target, $i, true);
            $oldPage = $this->page($target, $i, false);
            foreach (['en', 'pl'] as $locale) {
                $other = clone $page->textBlocks->firstWhere('type', 'forms-grid');
                $other->setRawAttributes(array_replace($other->getAttributes(), [
                    'id' => $other->id + ($locale === 'en' ? 2000000 : 4000000), 'locale' => $locale,
                    'uuid' => $locale.'-'.$other->uuid, 'body' => 'Independent '.$locale.' teaching content.',
                ]), true);
                $page->textBlocks->push($other); $oldPage->textBlocks->push($other);
            }
            $expected = $provider::preserveCoursePage($oldPage);
            self::assertNotSame($oldPage, $expected, 'The unchanged historical course guard must accept the baseline.');
            $before = $page->getAttributes(); $raw = $page->getRawOriginal();
            $rows = $page->textBlocks->map(fn ($b) => $b->getAttributes())->all();
            $restored = Package::preserveCoursePage($page);
            self::assertNotSame($page, $restored);
            self::assertSame($expected->getAttributes(), $restored->getAttributes());
            self::assertSame($expected->textBlocks->map(fn ($b) => $b->getAttributes())->all(),
                $restored->textBlocks->map(fn ($b) => $b->getAttributes())->all());
            foreach (['en', 'pl'] as $locale) {
                self::assertSame($page->textBlocks->firstWhere('locale', $locale), $restored->textBlocks->firstWhere('locale', $locale));
            }
            self::assertSame($before, $page->getAttributes());
            self::assertSame($raw, $page->getRawOriginal());
            self::assertSame($rows, $page->textBlocks->map(fn ($b) => $b->getAttributes())->all());
            self::assertSame($oldPage, Package::preserveCoursePage($oldPage), 'BEFORE pages remain owned by their old course helper.');

            foreach (['page-id', 'page-category', 'owner', 'subtitle', 'sibling-body', 'missing-row', 'duplicate-practice', 'non-uk', 'unloaded'] as $change) {
                $foreign = clone $page;
                $blocks = new Collection($page->textBlocks->map(fn ($block) => clone $block)->all());
                if ($change === 'page-id') { $foreign->setRawAttributes(array_replace($before, ['id' => $before['id'] + 10000]), true); }
                if ($change === 'page-category') { $foreign->setRawAttributes(array_replace($before, ['page_category_id' => $before['page_category_id'] + 10000]), true); }
                if ($change === 'owner') { $foreign->setRawAttributes(array_replace($before, ['seeder' => 'ForeignOwner']), true); }
                if ($change === 'subtitle') { $blocks->firstWhere('type', 'subtitle')->body .= ' changed'; }
                if ($change === 'sibling-body') { $blocks->firstWhere('type', 'forms-grid')->body = '{}'; }
                if ($change === 'missing-row') { $blocks = $blocks->slice(1)->values(); }
                if ($change === 'duplicate-practice') { $blocks->push(clone $blocks->firstWhere('type', 'practice-set')); }
                if ($change === 'non-uk') { $blocks->firstWhere('type', 'practice-set')->locale = 'pl'; }
                $foreign->setRelation('textBlocks', $blocks);
                if ($change === 'unloaded') { $foreign->unsetRelation('textBlocks'); }
                $snapshot = $foreign->getAttributes();
                $snapshotRows = $blocks->map(fn ($b) => $b->getAttributes())->all();
                self::assertSame($foreign, Package::preserveCoursePage($foreign), 'All-or-nothing course fallback: '.$change);
                self::assertSame($snapshot, $foreign->getAttributes());
                self::assertSame($snapshotRows, $blocks->map(fn ($b) => $b->getAttributes())->all());
            }
        }
        self::assertNull(Package::preserveCoursePage(null));
    }
}
