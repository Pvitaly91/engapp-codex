<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Question;
use App\Models\TextBlock;
use App\Http\Controllers\TestJsV2Controller;
use App\Http\Controllers\PageController;
use App\Http\Controllers\TheoryController;
use App\Http\Middleware\AddStructuredData;
use App\Support\M26DetailPackage;
use App\Support\M50ObligationAdvicePackage as Package;
use App\Support\TheoryContentSource;
use App\Support\TheorySentenceBuilderAcceptance;
use DOMDocument;
use DOMXPath;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class M50ObligationAdvicePackageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp(); app()->setLocale('uk'); $this->withoutVite();
    }

    private function blocks(array $target, array $definition): Collection
    {
        $subtitle = ['type' => 'subtitle', 'column' => 'header', 'body' => $definition['page']['subtitle_html'],
            'level' => $definition['page']['subtitle_level'] ?? null, 'uuid_key' => $definition['page']['subtitle_uuid_key'] ?? 'subtitle'];
        $blocks = [];
        foreach ([$subtitle, ...$definition['page']['blocks']] as $position => $config) {
            $block = new TextBlock;
            $block->setRawAttributes(['id' => 5000000 + $target['page_binding']['id'] * 100 + $position,
                'page_id' => $target['page_binding']['id'], 'page_category_id' => $target['page_binding']['page_category_id'],
                'uuid' => M26DetailPackage::uuid($target['identity'], $config, $position), 'seeder' => $target['identity'], 'locale' => 'uk',
                'type' => $config['type'], 'column' => $config['column'] ?? null, 'level' => $config['level'] ?? null,
                'heading' => $config['heading'] ?? null, 'css_class' => $config['css_class'] ?? null, 'body' => $config['body'],
                'sort_order' => $position, 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00'], true);
            $block->setRelation('tags', new Collection); $block->setRelation('page', null); $blocks[] = $block;
        }

        return new Collection($blocks);
    }

    private function render(TextBlock $block): string
    {
        app('view')->flushState();

        return view('theory.partials.content-block', ['block' => $block, 'theoryCanonical' => true,
            'm42StyleContext' => true, 'practiceQuestions' => collect(), 'lessonLinks' => []])->render();
    }

    private function xpath(string $html): DOMXPath
    {
        $dom = new DOMDocument('1.0', 'UTF-8'); $previous = libxml_use_internal_errors(true);
        try { $dom->loadHTML('<!doctype html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET); }
        finally { libxml_clear_errors(); libxml_use_internal_errors($previous); }

        return new DOMXPath($dom);
    }

    private function page(array $target, Collection $blocks): Page
    {
        $page = new Page;
        $page->setRawAttributes(['id' => $target['page_binding']['id'], 'page_category_id' => $target['page_binding']['page_category_id'],
            'type' => 'theory', 'seeder' => $target['identity'], 'slug' => $target['slug'], 'title' => $target['after']['page']['title'],
            'text' => $target['after']['page']['subtitle_text']], true);
        $page->setRelation('textBlocks', $blocks); $page->setRelation('tags', new Collection); $page->setRelation('category', null);

        return $page;
    }

    public function test_frozen_projection_keeps_three_owners_continuing_uuids_native_groups_and_independent_banks(): void
    {
        [$before, $patch, $master] = Package::load(); Package::validate($before, $patch, $master);
        self::assertSame([75, 79, 76], array_column(array_column($patch['targets'], 'page_binding'), 'id'));
        self::assertSame(Package::OWNERS, array_column($master['lessons'], 'identity'));
        foreach ([Package::BEFORE => Package::BEFORE_SHA, Package::SOURCE => Package::SOURCE_SHA, Package::MASTER => Package::MASTER_SHA] as $path => $sha) {
            self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D', $sha); self::assertSame($sha, hash_file('sha256', base_path($path)));
        }
        $contracts = TheorySentenceBuilderAcceptance::contracts(base_path());
        $found = array_column(TheorySentenceBuilderAcceptance::discoverTargets(base_path()), null, 'identity');
        foreach ($patch['targets'] as $i => $target) {
            self::assertSame($target['after'], json_decode(file_get_contents(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR));
            self::assertSame($target['after'], Package::definition($master['lessons'][$i], $before['targets'][$i]['before']));
            self::assertSame([], $found[$target['identity']]['errors']);
            self::assertSame(Package::BANK_SETS[$i], $contracts[$target['identity']]['bank_seeders']);
            self::assertSame('A2', $contracts[$target['identity']]['level']);
            self::assertCount(7, $target['after']['page']['blocks']);
            foreach ($before['targets'][$i]['before']['page']['blocks'] as $slot => $old) {
                $new = $target['after']['page']['blocks'][$slot];
                self::assertSame(array_diff_key($old, ['body' => true]), array_diff_key($new, ['body' => true]));
                self::assertSame(M26DetailPackage::uuid($target['identity'], $old, $slot + 1), M26DetailPackage::uuid($target['identity'], $new, $slot + 1));
            }
            $practice = json_decode($target['after']['page']['blocks'][6]['body'], true, flags: JSON_THROW_ON_ERROR);
            foreach (['selects', 'choices', 'inputs'] as $group) { self::assertCount(2, $practice[$group]); }
            self::assertSame('theory_links', $practice['linked_practice']['source']);
            self::assertSame(['4'], $practice['linked_practice']['question_types']);
            self::assertSame(Package::BANK_SETS[$i], $practice['linked_practice']['seeder_classes']);
            self::assertLessThanOrEqual(65535, strlen($target['after']['page']['blocks'][6]['body']));
        }
    }

    public function test_actual_standard_render_keeps_named_neutral_points_bilingual_corrections_and_visible_inputs(): void
    {
        [, $patch, $master] = Package::load();
        foreach ($patch['targets'] as $i => $target) {
            $blocks = $this->blocks($target, $target['after']);
            foreach ($master['lessons'][$i]['blocks'] as $slot => $author) {
                $block = $blocks[$slot + 1]; $snapshot = $block->getAttributes();
                $html = $this->render($block); $xp = $this->xpath($html);
                self::assertSame($snapshot, $block->getAttributes());
                self::assertStringNotContainsString('m50-section', $html); self::assertStringNotContainsString('m50-styles', $html);
                if ($author['type'] === 'usage-panels') {
                    $panels = $xp->query('//*[@data-theory-point-panel]');
                    self::assertCount(count($author['data']['sections']), $panels);
                    foreach ($author['data']['sections'] as $n => $section) {
                        self::assertStringContainsString($section['label'], $panels[$n]->textContent);
                        foreach ($section['examples'] as $example) {
                            self::assertStringContainsString(strip_tags($example['en']), $html);
                            self::assertStringContainsString(strip_tags($example['ua']), $html);
                        }
                    }
                }
                if ($author['type'] === 'mistakes-grid') {
                    $text = preg_replace('/\s+/u', ' ', $xp->query('//body')->item(0)->textContent);
                    foreach ($author['data']['items'] as $item) {
                        foreach (['wrong_en', 'wrong_ua', 'right_en', 'right_ua'] as $field) {
                            self::assertStringContainsString(preg_replace('/\s+/u', ' ', strip_tags($item[$field])), $text);
                        }
                    }
                }
                if ($author['type'] === 'practice-set') {
                    self::assertCount(3, $xp->query('//*[contains(concat(" ",normalize-space(@class)," ")," theory-exercise ")]'));
                    self::assertCount(2, $xp->query('//*[@data-word-suggestion-input and starts-with(@data-word-suggestion-input,"inputs-")]'));
                    foreach ($author['data']['inputs'] as $input) { self::assertStringContainsString($input['after'], $html); }
                }
            }
            // Hero and navigation are owned by theory.show, not native content-block views.
            $page = $this->page($target, $blocks); app('view')->flushState();
            $pageHtml = view('theory.show', ['page' => $page, 'categories' => collect(), 'categoryPages' => collect(),
                'topicTests' => collect(), 'selectedCategory' => null])->render();
            $pageXp = $this->xpath($pageHtml);
            self::assertCount(1, $pageXp->query('//h1'));
            self::assertSame($target['after']['page']['title'], trim($pageXp->query('//h1')->item(0)->textContent));
            $hero = $master['lessons'][$i]['blocks'][0]['data'];
            $heroNodes = $pageXp->query('//section[contains(concat(" ",normalize-space(@class)," ")," theory-hero ")]');
            self::assertCount(1, $heroNodes);
            self::assertStringContainsString(strip_tags($hero['intro']), preg_replace('/\s+/u', ' ', $heroNodes->item(0)->textContent));
            $cards = $pageXp->query('//section[contains(concat(" ",normalize-space(@class)," ")," theory-rules ")]/article');
            self::assertCount(count($hero['rules']), $cards);
            foreach ($hero['rules'] as $n => $rule) {
                $cardText = preg_replace('/\s+/u', ' ', $cards->item($n)->textContent);
                foreach (['label', 'text'] as $field) { self::assertStringContainsString(strip_tags($rule[$field]), $cardText); }
                $expectedXp = $this->xpath($rule['example']);
                $codes = $pageXp->query('./code', $cards->item($n)); self::assertCount(1, $codes);
                $actualParts = $pageXp->query('./span', $codes->item(0)); self::assertCount(2, $actualParts);
                foreach (['en', 'uk'] as $position => $language) {
                    $expected = $expectedXp->query('//*[@lang="'.$language.'"]');
                    self::assertCount(1, $expected);
                    self::assertSame($expected->item(0)->textContent, $actualParts->item($position)->textContent,
                        'Inline sanitizer removes lang attributes, not the exact EN/UK teaching text or its order');
                }
                $tags = static fn ($nodes) => array_map(static fn ($node) => $node->nodeName, iterator_to_array($nodes));
                self::assertSame(['span', 'br', 'span'], $tags($expectedXp->query('//body/*')));
                self::assertSame(['span', 'br', 'span'], $tags($pageXp->query('./*', $codes->item(0))));
                self::assertSame($expectedXp->query('//body')->item(0)->textContent, $codes->item(0)->textContent);
            }
            $navigation = $master['lessons'][$i]['blocks'][5]['data'];
            foreach ($navigation['items'] as $link) {
                if ($link['current'] ?? false) {
                    self::assertContains($link['label'], array_map(static fn ($node) => trim($node->textContent),
                        iterator_to_array($pageXp->query('//nav//span'))));
                } else {
                    $links = $pageXp->query('//nav//a[@href="'.$link['url'].'"]');
                    self::assertGreaterThanOrEqual(1, $links->length);
                    self::assertContains($link['label'], array_map(static fn ($node) => trim($node->textContent), iterator_to_array($links)));
                }
            }
        }
    }

    public function test_details_are_owned_by_single_points_full_basic_is_restored_and_all_guards_fail_closed(): void
    {
        [, $patch, $master] = Package::load();
        foreach ($patch['targets'] as $i => $target) {
            $blocks = $this->blocks($target, $target['after']);
            foreach ($master['lessons'][$i]['blocks'] as $slot => $author) {
                $block = $blocks[$slot + 1]; $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
                $presented = Package::presentation($block, $data); self::assertNotNull($presented);
                $basic = $presented['data']; unset($basic['m50_native_v1']); self::assertSame($author['data'], $basic);
                foreach (['page_id' => 9999, 'page_category_id' => 9999, 'seeder' => Package::OWNERS[($i + 1) % 3],
                    'locale' => 'en', 'uuid' => 'foreign', 'sort_order' => 999, 'heading' => 'foreign', 'level' => 'C2', 'css_class' => 'foreign'] as $field => $value) {
                    $foreign = clone $block; $foreign->$field = $value; self::assertNull(Package::presentation($foreign, $data), $field);
                }
                $spaced = clone $block; $spaced->body .= "\n";
                self::assertNull(Package::presentation($spaced, $data), 'Exact raw body, not only decoded content');
                self::assertNull(TheoryContentSource::resolve($block)['presentation']);
                $resolved = TheoryContentSource::resolve($block, ['m42StyleContext' => true]);
                if (in_array($block->type, ['hero', 'navigation-chips'], true)) {
                    self::assertNull($resolved['view']); self::assertNull($resolved['presentation'],
                        'Auxiliary data is rendered by theory.show, not the native content-block registry');
                } else { self::assertNotNull($resolved['presentation']); }
            }
            foreach ($master['lessons'][$i]['details'] as $detail) {
                $slot = array_search($detail['owner_block_key'], array_column($master['lessons'][$i]['blocks'], 'key'), true);
                $block = $blocks[$slot + 1]; $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
                $presented = Package::presentation($block, $data); $index = (int) explode('/', $detail['owner_pointer'])[2];
                self::assertSame([$index], array_keys($presented['points']));
                self::assertSame($detail['fragment']['data']['sections'], array_column($presented['points'][$index]['fragments'], 'value'));
                $html = $this->render($block);
                self::assertCount(1, $this->xpath($html)->query('//details[@data-theory-details]'));
                foreach ($detail['fragment']['data']['sections'] as $section) {
                    foreach ($section['examples'] as $example) { self::assertStringContainsString($example['en'], $html); self::assertStringContainsString($example['ua'], $html); }
                }
            }
        }
        $block = $this->blocks($patch['targets'][0], $patch['targets'][0]['after'])[1];
        app()->setLocale('pl'); self::assertNull(Package::presentation($block, json_decode($block->body, true)));
    }

    public function test_foreign_bank_empty_inputs_unknown_view_and_styled_learning_payload_are_rejected(): void
    {
        [$before, $patch, $master] = Package::load();
        foreach (['bank', 'inputs', 'view', 'styled'] as $change) {
            $bad = $master;
            if ($change === 'bank') { $bad['lessons'][0]['blocks'][6]['data']['linked_practice']['seeder_classes'] = [Package::BANKS[1]]; }
            if ($change === 'inputs') { $bad['lessons'][0]['blocks'][6]['data']['inputs'] = []; }
            if ($change === 'view') { $bad['lessons'][0]['blocks'][1]['data']['view'] = 'theory.foreign'; }
            if ($change === 'styled') { $bad['lessons'][0]['blocks'][1]['data']['intro'] = '<p class="other">Пояснення</p>'; }
            $changed = $patch; $changed['targets'][0]['after'] = Package::definition($bad['lessons'][0], $before['targets'][0]['before']);
            $error = null; try { Package::validate($before, $changed, $bad); } catch (RuntimeException $caught) { $error = $caught; }
            self::assertInstanceOf(RuntimeException::class, $error, $change);
        }
    }

    public function test_courses_keep_exact_before_all_other_locales_and_original_models_untouched(): void
    {
        [$before, $patch] = Package::load();
        foreach ($patch['targets'] as $i => $target) {
            $blocks = $this->blocks($target, $target['after']); $old = $this->blocks($target, $before['targets'][$i]['before']);
            foreach (['en', 'pl'] as $locale) {
                foreach ($old as $original) { $other = clone $original; $other->locale = $locale; $other->uuid = $locale.'-'.$other->uuid; $other->syncOriginal(); $blocks->push($other); }
            }
            $page = new Page; $page->setRawAttributes(['id' => $target['page_binding']['id'],
                'page_category_id' => $target['page_binding']['page_category_id'], 'type' => 'theory', 'slug' => $target['slug'],
                'seeder' => $target['identity'], 'title' => $target['after']['page']['title'], 'text' => $target['after']['page']['subtitle_text']], true);
            $page->setRelation('textBlocks', $blocks); $snapshot = $page->getAttributes(); $blockSnapshot = $blocks->map(static fn ($b) => $b->getAttributes())->all();
            foreach (['uk', 'en', 'pl'] as $locale) {
                app()->setLocale($locale); $copy = Package::preserveCoursePage($page); self::assertNotSame($page, $copy);
                self::assertSame($before['targets'][$i]['before']['page']['subtitle_text'], $copy->text);
                self::assertSame($old->map(static fn ($b) => $b->getAttributes())->all(), $copy->textBlocks->where('locale', 'uk')->values()->map(static fn ($b) => $b->getAttributes())->all());
                foreach ($blocks->reject(static fn ($b) => $b->locale === 'uk') as $other) { self::assertSame($other, $copy->textBlocks->firstWhere('uuid', $other->uuid)); }
            }
            self::assertSame($snapshot, $page->getAttributes()); self::assertSame($blockSnapshot, $blocks->map(static fn ($b) => $b->getAttributes())->all());
            $foreign = clone $page; $foreign->setRawAttributes(array_replace($snapshot, ['id' => 9999]), true);
            self::assertSame($foreign, Package::preserveCoursePage($foreign));
            DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
            foreach (['uuid' => 'foreign-subtitle', 'page_id' => 99999, 'page_category_id' => 99999, 'seeder' => 'ForeignOwner',
                'locale' => 'en', 'sort_order' => 1, 'type' => 'box', 'column' => 'left', 'heading' => 'Changed heading',
                'level' => 'C2', 'css_class' => 'changed', 'body' => $blocks[0]->body."\n"] as $field => $value) {
                $subtitle = clone $blocks[0]; $subtitle->setAttribute($field, $value);
                $foreign = clone $page; $foreign->setRelation('textBlocks', new Collection([$subtitle, ...array_slice($blocks->all(), 1)]));
                $foreignAttributes = $foreign->getAttributes();
                $foreignBlocks = $foreign->textBlocks->map(static fn ($b) => $b->getAttributes())->all();
                self::assertSame($foreign, Package::preserveCoursePage($foreign), 'Edited subtitle '.$field.' must keep complete fallback');
                self::assertSame($foreignAttributes, $foreign->getAttributes()); self::assertSame($foreignBlocks, $foreign->textBlocks->map(static fn ($b) => $b->getAttributes())->all());
            }
            foreach (['id' => 99999, 'page_category_id' => 99999, 'seeder' => 'ForeignOwner', 'type' => 'course',
                'slug' => 'changed', 'title' => 'Changed title', 'text' => 'Changed preview'] as $field => $value) {
                $foreign = clone $page; $foreign->setAttribute($field, $value);
                $foreignAttributes = $foreign->getAttributes(); $foreignRaw = $foreign->getRawOriginal();
                self::assertSame($foreign, Package::preserveCoursePage($foreign), 'Edited current Page '.$field.' must keep complete fallback');
                self::assertSame($foreignAttributes, $foreign->getAttributes()); self::assertSame($foreignRaw, $foreign->getRawOriginal());
            }
            self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
            self::assertSame($snapshot, $page->getAttributes()); self::assertSame($blockSnapshot, $blocks->map(static fn ($b) => $b->getAttributes())->all());
        }
    }

    public function test_existing_standalone_controller_serialization_keeps_before_help_and_excludes_only_exact_new_practices(): void
    {
        [$before, $patch] = Package::load();
        $controller = (new \ReflectionClass(TestJsV2Controller::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod(TestJsV2Controller::class, 'resolveTheoryBlocksForQuestion');
        foreach ($patch['targets'] as $i => $target) {
            $old = $this->blocks($target, $before['targets'][$i]['before']); $after = $this->blocks($target, $target['after']);
            $question = new Question; $question->setRawAttributes(['id' => 7000000 + $i, 'theory_text_block_uuid' => null], true);
            $question->setRelation('theoryTextBlocks', $old);
            $expected = $method->invoke($controller, $question); $question->setRelation('theoryTextBlocks', $after);
            $snapshot = $after->map(static fn ($b) => $b->getAttributes())->all();
            foreach (['uk', 'en', 'pl'] as $locale) {
                app()->setLocale($locale);
                self::assertSame($expected, $method->invoke($controller, $question));
            }
            self::assertSame($snapshot, $after->map(static fn ($b) => $b->getAttributes())->all());
            foreach ($after as $position => $block) {
                if ($position === 7) { self::assertNull(Package::preserveExcludedTextBlock($block, 'standalone_test')); }
                else {
                    $copy = Package::preserveExcludedTextBlock($block, 'standalone_test');
                    self::assertNotSame($block, $copy); self::assertSame($old[$position]->body, $copy->body);
                    self::assertSame(array_diff_key($block->getAttributes(), ['body' => true]), array_diff_key($copy->getAttributes(), ['body' => true]));
                }
                self::assertSame($block, Package::preserveExcludedTextBlock($block, 'theory'));
                foreach (['page_id' => 99999, 'page_category_id' => 99999, 'locale' => 'en', 'seeder' => 'ForeignOwner',
                    'uuid' => 'foreign', 'level' => 'C2', 'sort_order' => 99999, 'body' => $block->body."\n"] as $field => $value) {
                    $foreign = clone $block; $foreign->$field = $value;
                    self::assertSame($foreign, Package::preserveExcludedTextBlock($foreign, 'standalone_test'), $field);
                }
            }
            $foreign = clone $after[2]; $foreign->body = 'Foreign changed help'; $foreign->uuid = 'foreign-help';
            $question->setRelation('theoryTextBlocks', new Collection([$foreign]));
            self::assertSame([['uuid' => 'foreign-help', 'type' => $foreign->type, 'body' => 'Foreign changed help', 'level' => $foreign->level]],
                $method->invoke($controller, $question));
        }
    }

    public function test_actual_theory_title_localizer_preserves_h1_breadcrumb_and_learning_resource_without_raw_mutation(): void
    {
        [$before, $patch] = Package::load();
        $controller = (new \ReflectionClass(TheoryController::class))->newInstanceWithoutConstructor();
        $extract = new \ReflectionMethod(PageController::class, 'extractLocalizedTitleFromSubtitle');
        $localize = new \ReflectionMethod(PageController::class, 'applyLocalizedTitlesToModels');
        $baselineDrift = [
            'Must і have',
            'Should і ought to: порада й очікуваний результат, заперечення, питання та різниця у формі',
            "Need to, needn't і don't have to: необхідність, необов’язковість, питання та різниця із забороною",
        ];
        foreach ($patch['targets'] as $i => $target) {
            $blocks = $this->blocks($target, $target['after']); $page = $this->page($target, $blocks);
            $expected = $before['targets'][$i]['before']['page']['title'];
            $attributes = $page->getAttributes(); $raw = $page->getRawOriginal(); $originalBlocks = $blocks->map(static fn ($b) => $b->getAttributes())->all();
            $candidate = $extract->invoke($controller, $target['after']['page']['subtitle_html']);
            self::assertSame($baselineDrift[$i], $candidate, 'Reproduce the unchanged generic subtitle-title algorithm, not a synthetic already-correct title');
            DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
            self::assertSame($expected, Package::displayTitle($page, 'uk', 'theory'));
            self::assertSame([], DB::connection()->getQueryLog(), 'Title preservation must never query missing blocks');
            self::assertSame($attributes, $page->getAttributes()); self::assertSame($raw, $page->getRawOriginal());
            $localize->invoke($controller, collect([$page]), [$page->getKey() => ['uk' => $candidate]], 'uk', 'uk');
            self::assertSame($expected, $page->title); self::assertSame([], DB::connection()->getQueryLog());
            DB::connection()->disableQueryLog();
            self::assertSame($attributes, $page->getAttributes()); self::assertSame($raw, $page->getRawOriginal());
            self::assertSame($originalBlocks, $blocks->map(static fn ($b) => $b->getAttributes())->all());

            app('view')->flushState();
            $view = view('theory.show', ['page' => $page, 'categories' => collect(), 'categoryPages' => collect(),
                'topicTests' => collect(), 'selectedCategory' => null]);
            $request = Request::create($target['local_url'], 'GET');
            $route = (new Route(['GET'], 'theory/{categoryPath}/{pageSlug}', [TheoryController::class, 'showByCategoryPath']))->name('theory.show');
            $request->setRouteResolver(static fn () => $route);
            $response = (new AddStructuredData)->handle($request, static fn () => new Response($view, 200, ['Content-Type' => 'text/html']));
            $xp = $this->xpath($response->getContent());
            self::assertCount(1, $xp->query('//h1')); self::assertSame($expected, trim($xp->query('//h1')->item(0)->textContent));
            $breadcrumb = $xp->query('//div[contains(concat(" ",normalize-space(@class)," ")," nd-page ")]/nav[@aria-label]/span[last()]');
            self::assertCount(1, $breadcrumb); self::assertSame($expected, trim($breadcrumb->item(0)->textContent));
            $graphs = [];
            foreach ($xp->query('//script[@type="application/ld+json"]') as $script) {
                $payload = json_decode($script->textContent, true, flags: JSON_THROW_ON_ERROR);
                foreach ($payload['@graph'] ?? [] as $node) { $graphs[$node['@type']][] = $node; }
            }
            self::assertCount(1, $graphs['LearningResource'] ?? []); self::assertCount(1, $graphs['BreadcrumbList'] ?? []);
            self::assertSame($expected, $graphs['LearningResource'][0]['name']); self::assertSame($expected, $graphs['LearningResource'][0]['headline']);
            $items = $graphs['BreadcrumbList'][0]['itemListElement'];
            self::assertSame($expected, $items[count($items) - 1]['name']);
            self::assertSame($raw, $page->getRawOriginal()); self::assertSame($originalBlocks, $blocks->map(static fn ($b) => $b->getAttributes())->all());
        }
    }

    public function test_title_preservation_is_exact_theory_uk_loaded_after_only_and_foreign_localizers_are_unchanged(): void
    {
        [$before, $patch] = Package::load();
        $localize = new \ReflectionMethod(PageController::class, 'applyLocalizedTitlesToModels');
        $extract = new \ReflectionMethod(PageController::class, 'extractLocalizedTitleFromSubtitle');
        $foreignController = (new \ReflectionClass(PageController::class))->newInstanceWithoutConstructor();
        foreach ($patch['targets'] as $i => $target) {
            $blocks = $this->blocks($target, $target['after']); $page = $this->page($target, $blocks);
            $attributes = $page->getAttributes(); $raw = $page->getRawOriginal();
            DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
            foreach (['pages', 'course', 'standalone_test', ''] as $caller) { self::assertNull(Package::displayTitle($page, 'uk', $caller)); }
            foreach (['en', 'pl'] as $locale) {
                self::assertNull(Package::displayTitle($page, $locale, 'theory'));
                app()->setLocale($locale); self::assertNull(Package::displayTitle($page, 'uk', 'theory')); app()->setLocale('uk');
            }
            foreach (['id' => 99999, 'page_category_id' => 99999, 'seeder' => 'ForeignOwner', 'type' => 'course',
                'slug' => 'foreign', 'title' => 'Manually revised title', 'text' => 'Manually revised text'] as $field => $value) {
                $foreign = clone $page; $foreign->setRawAttributes(array_replace($attributes, [$field => $value]), true);
                self::assertNull(Package::displayTitle($foreign, 'uk', 'theory'), $field);
            }
            $unloaded = clone $page; $unloaded->unsetRelation('textBlocks'); self::assertNull(Package::displayTitle($unloaded, 'uk', 'theory'));
            $missing = clone $page; $missing->setRelation('textBlocks', $blocks->reject(static fn ($b) => $b->type === 'subtitle')->values());
            self::assertNull(Package::displayTitle($missing, 'uk', 'theory'));
            $duplicate = clone $page; $duplicate->setRelation('textBlocks', new Collection([...$blocks->all(), clone $blocks[0]]));
            self::assertNull(Package::displayTitle($duplicate, 'uk', 'theory'));
            $old = clone $page; $old->setRawAttributes(array_replace($attributes, ['text' => $before['targets'][$i]['before']['page']['subtitle_text']]), true);
            $old->setRelation('textBlocks', $this->blocks($target, $before['targets'][$i]['before']));
            self::assertNull(Package::displayTitle($old, 'uk', 'theory'));
            foreach (['uuid' => 'foreign', 'page_id' => 99999, 'page_category_id' => 99999, 'seeder' => 'ForeignOwner', 'locale' => 'en',
                'sort_order' => 1, 'type' => 'box', 'column' => 'left', 'heading' => 'foreign', 'css_class' => 'foreign',
                'level' => 'C2', 'body' => $blocks[0]->body."\n"] as $field => $value) {
                $foreign = clone $page; $subtitle = clone $blocks[0]; $subtitle->$field = $value;
                $foreign->setRelation('textBlocks', new Collection([$subtitle, ...array_slice($blocks->all(), 1)]));
                self::assertNull(Package::displayTitle($foreign, 'uk', 'theory'), $field);
            }
            self::assertSame([], DB::connection()->getQueryLog()); DB::connection()->disableQueryLog();
            self::assertSame($attributes, $page->getAttributes()); self::assertSame($raw, $page->getRawOriginal());
            $candidate = $extract->invoke($foreignController, $target['after']['page']['subtitle_html']);
            $foreignPage = clone $page;
            $localize->invoke($foreignController, collect([$foreignPage]), [$foreignPage->getKey() => ['uk' => $candidate]], 'uk', 'uk');
            self::assertSame($candidate, $foreignPage->title, 'Non-theory callers retain the existing localization algorithm');
            self::assertSame($raw, $foreignPage->getRawOriginal()); self::assertSame($attributes, $page->getAttributes());
        }
    }
}
