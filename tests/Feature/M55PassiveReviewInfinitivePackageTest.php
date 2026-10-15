<?php

namespace Tests\Feature;

use App\Http\Controllers\PageController;
use App\Http\Controllers\TheoryController;
use App\Http\Middleware\AddStructuredData;
use App\Models\Page;
use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M55PassiveReviewInfinitivePackage as Package;
use App\Support\TheoryContentSource;
use App\Support\TheorySentenceBuilderAcceptance as Acceptance;
use DOMDocument;
use DOMXPath;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** All assertions run with isolated SQLite/array defaults and the real shared views. */
class M55PassiveReviewInfinitivePackageTest extends TestCase
{
    private const COUNTS = [11, 10, 9];
    private const DISPLAY = ['A2–B2', 'B2–C1', 'B2–C1'];
    private const H1 = ['Приклади вживання та типові помилки', 'Формальність та стиль пасиву', 'Passive Infinitive'];
    private const BANKS = [
        ['Database\\Seeders\\V3\\Polyglot\\PolyglotPassiveVoiceCommonMistakesAllLevelsLessonSeeder'],
        ['Database\\Seeders\\V3\\Polyglot\\PolyglotPassiveVoiceFormalityStyleAllLevelsLessonSeeder'],
        ['Database\\Seeders\\V3\\Polyglot\\PolyglotPassiveVoicePassiveInfinitiveAllLevelsLessonSeeder'],
    ];

    protected function setUp(): void
    {
        parent::setUp(); app()->setLocale('uk'); $this->withoutVite();
        self::assertSame('testing', app()->environment()); self::assertNotSame(base_path(), app()->environmentPath());
        self::assertSame('sqlite', DB::connection()->getDriverName()); self::assertSame(':memory:', DB::connection()->getDatabaseName());
        self::assertSame('array', config('cache.default')); self::assertSame('array', config('session.driver'));
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
    }

    private function blocks(array $target): Collection
    {
        $definition = $target['after'];
        $subtitle = ['type' => 'subtitle', 'column' => 'header', 'body' => $definition['page']['subtitle_html'],
            'level' => $definition['page']['subtitle_level'] ?? null, 'uuid_key' => $definition['page']['subtitle_uuid_key'] ?? 'subtitle'];
        $rows = [];
        foreach ([$subtitle, ...$definition['page']['blocks']] as $position => $source) {
            $block = new TextBlock;
            $block->setRawAttributes(['id' => 5400000 + $target['page_binding']['id'] * 100 + $position,
                'page_id' => $target['page_binding']['id'], 'page_category_id' => $target['page_binding']['page_category_id'],
                'uuid' => M26DetailPackage::uuid($target['identity'], $source, $position), 'seeder' => $target['identity'], 'locale' => 'uk',
                'type' => $source['type'], 'column' => $source['column'] ?? null, 'level' => $source['level'] ?? null,
                'heading' => $source['heading'] ?? null, 'css_class' => $source['css_class'] ?? null,
                'body' => $source['body'], 'sort_order' => $position, 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00'], true);
            $block->setRelation('tags', new Collection); $block->setRelation('page', null); $rows[] = $block;
        }
        return new Collection($rows);
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
    private function xpath(string $html): DOMXPath
    {
        $dom = new DOMDocument('1.0', 'UTF-8'); $previous = libxml_use_internal_errors(true);
        try { $dom->loadHTML('<meta charset="UTF-8">'.$html, LIBXML_NONET); }
        finally { libxml_clear_errors(); libxml_use_internal_errors($previous); }
        return new DOMXPath($dom);
    }
    private function plain(string $value): string
    {
        $value = preg_replace('/<(?:br\s*\/?|\/(?:p|div|li|section))>/i', ' ', $value);
        $value = preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        return trim(preg_replace('/\s+([,.!?;:])/u', '$1', $value));
    }
    private function text(string $html): string
    {
        $parts = [];
        foreach ($this->xpath($html)->query('//text()[not(ancestor::script) and not(ancestor::style)]') as $node) { $parts[] = $node->textContent; }
        return $this->plain(implode(' ', $parts));
    }
    private function render(TextBlock $block): string
    {
        app('view')->flushState();
        return view('theory.partials.content-block', ['block' => $block, 'theoryCanonical' => true, 'm42StyleContext' => true,
            'practiceQuestions' => collect(), 'lessonLinks' => []])->render();
    }
    private function contains(string $text, mixed $expected, string $label): void
    {
        if (is_string($expected) && $this->plain($expected) !== '') { self::assertStringContainsString($this->plain($expected), $text, $label); }
    }

    public function test_frozen_scope_preserves_exact_owners_levels_slots_uuids_and_new_dynamic_discovery(): void
    {
        [$before, $patch, $master, $contract] = Package::load(); Package::validate($before, $patch, $master, $contract);
        self::assertSame('frozen', $master['status']); self::assertSame('1.0.0', $master['version']);
        self::assertSame('0cf633036f4c91f2376976651ec0519d80ec6191', $master['base_sha']);
        self::assertSame([93, 94, 103], array_column(array_column($patch['targets'], 'page_binding'), 'id'));
        $catalogue = array_column(Acceptance::discoverTargets(base_path()), null, 'identity');
        foreach (Package::profile()['artifacts'] as $path => $sha) {
            self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D', $sha); self::assertSame($sha, hash_file('sha256', base_path($path)));
        }
        foreach ($patch['targets'] as $i => $target) {
            self::assertSame([], $catalogue[$target['identity']]['errors']);
            self::assertSame(self::BANKS[$i], $contract['targets'][$i]['bank_seeders']); self::assertSame(Package::LEVELS[$i], $contract['targets'][$i]['level']);
            $old = $before['targets'][$i]['before']; $lesson = $master['lessons'][$i];
            self::assertSame($target['after'], Package::definition($lesson, $old));
            self::assertSame($target['after'], json_decode(File::get(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR));
            self::assertCount(self::COUNTS[$i], $old['page']['blocks']); self::assertCount(self::COUNTS[$i] + 1, $lesson['blocks']);
            self::assertSame(self::DISPLAY[$i], $lesson['display_level']); self::assertSame(Package::LEVELS[$i], $lesson['technical_block_level']);
            foreach ($old['page']['blocks'] as $slot => $source) {
                $next = $target['after']['page']['blocks'][$slot];
                self::assertSame(array_diff_key($source, ['body' => true]), array_diff_key($next, ['body' => true]));
                self::assertSame(M26DetailPackage::uuid($target['identity'], $source, $slot + 1), M26DetailPackage::uuid($target['identity'], $next, $slot + 1));
            }
            $practice = $lesson['blocks'][self::COUNTS[$i]];
            self::assertSame('practice-set', $practice['type']); self::assertNull($practice['existing_source_index']);
            self::assertSame(self::BANKS[$i], $practice['data']['linked_practice']['seeder_classes']);
            foreach (['selects', 'choices', 'inputs'] as $group) { self::assertCount(2, $practice['data'][$group]); }
        }
    }

    public function test_shared_native_renderer_renders_all_basic_fields_and_exact_multi_point_detail_owners(): void
    {
        [, $patch, $master] = Package::load();
        foreach ($patch['targets'] as $i => $target) {
            $blocks = $this->blocks($target); $lesson = $master['lessons'][$i];
            foreach ($lesson['blocks'] as $slot => $author) {
                if (in_array($author['type'], ['hero', 'navigation-chips'], true)) { continue; }
                $component = Package::profile()['bindings'][$i]['component_overrides'][$slot] ?? $author['type'];
                $block = $blocks[$slot + 1]; $before = $block->getAttributes(); $html = $this->render($block); $xp = $this->xpath($html); $text = $this->text($html); $data = $author['data'];
                self::assertSame($before, $block->getAttributes());
                foreach (['m55-section', 'm55-detail', 'm55-styles', '<style'] as $forbidden) { self::assertStringNotContainsString($forbidden, $html); }
                foreach (['title', 'intro', 'warning', 'outro'] as $field) { $this->contains($text, $data[$field] ?? null, $author['key'].'/'.$field); }
                if ($component === 'forms-grid') {
                    foreach ($data['items'] as $item) {
                        foreach (['label', 'title', 'subtitle'] as $field) { $this->contains($text, $item[$field] ?? null, $author['key'].'/'.$field); }
                        foreach ($item['rules'] ?? [] as $rule) {
                            foreach (['label', 'formula', 'text', 'example'] as $field) { $this->contains($text, $rule[$field] ?? null, $author['key'].'/rules/'.$field); }
                        }
                    }
                } elseif ($component === 'usage-panels') {
                    $points = $xp->query('//*[@data-theory-point-panel]'); self::assertCount(count($data['sections']), $points);
                    foreach ($data['sections'] as $n => $section) {
                        self::assertSame($section['color'], $points->item($n)->getAttribute('data-theory-accent'));
                        foreach (['label', 'description', 'note'] as $field) { $this->contains($text, $section[$field] ?? null, $author['key'].'/'.$field); }
                        foreach ($section['examples'] ?? [] as $example) { foreach (['en', 'ua'] as $field) { $this->contains($text, $example[$field], $author['key'].'/'.$field); } }
                    }
                } elseif ($component === 'comparison-table') {
                    self::assertCount(1, $xp->query('//table')); self::assertCount(count($data['rows']), $xp->query('//tbody/tr'));
                    foreach ($data['headers'] as $header) { $this->contains($text, $header, 'table header'); }
                    foreach ($data['rows'] as $row) { foreach ($row['cells'] as $cell) { $this->contains($text, $cell, 'table cell'); } }
                } elseif ($component === 'mistakes-grid') {
                    foreach ($data['items'] as $item) { foreach (['label', 'title', 'wrong_en', 'wrong_ua', 'right_en', 'right_ua', 'note'] as $field) { $this->contains($text, $item[$field], 'correction/'.$field); } }
                } elseif ($component === 'summary-list') {
                    foreach ($data['items'] as $item) { $this->contains($text, $item, 'summary'); }
                } elseif ($component === 'practice-set') {
                    self::assertCount(3, $xp->query('//*[contains(concat(" ",normalize-space(@class)," ")," theory-exercise ")]'));
                    self::assertCount(2, $xp->query('//*[@data-word-suggestion-input and starts-with(@data-word-suggestion-input,"inputs-")]'));
                    foreach ($data['inputs'] as $input) { $this->contains($text, $input['after'], 'visible input condition'); }
                    foreach (['selects', 'choices'] as $group) { foreach ($data[$group] as $task) { $this->contains($text, $task['label'], 'visible task condition'); } }
                    foreach ($data['choices'] as $task) { $this->contains($text, $task['prompt'] ?? null, 'visible meaning stimulus'); }
                    foreach (['select_title', 'select_intro', 'choice_title', 'choice_intro', 'input_title', 'input_intro'] as $field) {
                        $this->contains($text, $data[$field], 'practice/'.$field);
                    }
                }
                $details = array_values(array_filter($lesson['details'], static fn ($detail) => $detail['owner_block_key'] === $author['key']));
                self::assertCount(count($details), $xp->query('//details[@data-theory-details and not(@open)]'));
                foreach ($details as $detail) {
                    $point = explode('/', $detail['owner_pointer'])[2];
                    $owned = $xp->query('//*[@data-theory-section="'.$detail['key'].'" and @data-theory-point-index="'.$point.'"]//details[@data-theory-details]');
                    self::assertCount(1, $owned);
                    foreach ($detail['fragment']['data']['sections'] as $section) {
                        foreach (['label', 'description', 'note'] as $field) { $this->contains($text, $section[$field] ?? null, 'detail/'.$field); }
                        foreach ($section['examples'] as $example) { foreach (['en', 'ua'] as $field) { $this->contains($text, $example[$field], 'detail/'.$field); } }
                    }
                }
            }
        }
    }

    public function test_supported_title_extractor_retains_independent_h1_breadcrumb_and_learning_resource(): void
    {
        [, $patch, $master] = Package::load();
        $controller = (new \ReflectionClass(TheoryController::class))->newInstanceWithoutConstructor();
        $extract = new \ReflectionMethod(PageController::class, 'extractLocalizedTitleFromSubtitle');
        $localize = new \ReflectionMethod(PageController::class, 'applyLocalizedTitlesToModels');
        foreach ($patch['targets'] as $i => $target) {
            $page = $this->page($target, $this->blocks($target)); $raw = $page->getRawOriginal();
            $candidate = $extract->invoke($controller, $target['after']['page']['subtitle_html']); self::assertSame(self::H1[$i], $candidate);
            $localize->invoke($controller, collect([$page]), [$page->id => ['uk' => $candidate]], 'uk', 'uk');
            app('view')->flushState();
            $view = view('theory.show', ['page' => $page, 'categories' => collect(), 'categoryPages' => collect(), 'topicTests' => collect(), 'selectedCategory' => null]);
            $request = Request::create($target['local_url'], 'GET');
            $route = (new Route(['GET'], 'theory/{categoryPath}/{pageSlug}', [TheoryController::class, 'showByCategoryPath']))->name('theory.show');
            $request->setRouteResolver(static fn () => $route);
            $html = (new AddStructuredData)->handle($request, static fn () => new Response($view, 200, ['Content-Type' => 'text/html']))->getContent();
            $xp = $this->xpath($html); self::assertCount(1, $xp->query('//h1')); self::assertSame($candidate, trim($xp->query('//h1')->item(0)->textContent));
            $pageText = $this->text($html); $hero = $master['lessons'][$i]['blocks'][0]['data'];
            foreach (['level', 'intro'] as $field) { $this->contains($pageText, $hero[$field], 'hero/'.$field); }
            foreach ($hero['rules'] as $rule) { foreach (['label', 'text', 'example'] as $field) { $this->contains($pageText, $rule[$field], 'hero/rule/'.$field); } }
            $footer = array_values(array_filter($master['lessons'][$i]['blocks'], static fn ($block) => $block['type'] === 'navigation-chips'))[0]['data'];
            $this->contains($pageText, $footer['title'], 'navigation/title');
            foreach ($footer['items'] as $item) {
                $this->contains($pageText, $item['label'], 'navigation/label');
                if (!($item['current'] ?? false)) { self::assertGreaterThan(0, $xp->query('//*[@href="'.$item['url'].'"]')->length); }
            }
            $crumb = $xp->query('//div[contains(concat(" ",normalize-space(@class)," ")," nd-page ")]/nav[@aria-label]/span[last()]');
            self::assertCount(1, $crumb); self::assertSame($candidate, trim($crumb->item(0)->textContent));
            $learning = [];
            foreach ($xp->query('//script[@type="application/ld+json"]') as $node) {
                $json = json_decode($node->textContent, true, flags: JSON_THROW_ON_ERROR);
                foreach ($json['@graph'] ?? [] as $item) { if (($item['@type'] ?? null) === 'LearningResource') { $learning[] = $item; } }
            }
            self::assertCount(1, $learning); self::assertSame($candidate, $learning[0]['name']); self::assertSame($candidate, $learning[0]['headline']);
            self::assertSame($raw, $page->getRawOriginal());
        }
        self::assertStringNotContainsString('M55', File::get(base_path('app/Http/Controllers/PageController.php')));
    }

    public function test_foreign_or_stale_binding_rejects_presentation_and_excluded_consumers_are_cloned(): void
    {
        [$before, $patch, $master, $contract] = Package::load();
        foreach ($patch['targets'] as $i => $target) {
            $blocks = $this->blocks($target);
            foreach ($blocks->slice(1) as $block) {
                $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR); self::assertNotNull(Package::presentation($block, $data));
                foreach (['page_id' => 99999, 'page_category_id' => 99999, 'seeder' => 'ForeignOwner', 'locale' => 'en', 'uuid' => 'foreign',
                    'type' => 'box', 'sort_order' => 99999, 'heading' => 'foreign', 'level' => 'C2', 'css_class' => 'foreign', 'body' => $block->body."\n"] as $field => $value) {
                    $foreign = clone $block; $foreign->$field = $value; self::assertNull(Package::presentation($foreign, $data), $field);
                }
                self::assertNull(TheoryContentSource::resolve($block)['presentation']);
                $snapshot = $block->getAttributes(); $old = Package::preserveExcludedTextBlock($block, 'standalone_test');
                self::assertSame($snapshot, $block->getAttributes());
                if ($block->type === 'practice-set') { self::assertNull($old); }
                else { self::assertNotSame($block, $old); self::assertSame($before['targets'][$i]['before']['page']['blocks'][$block->sort_order - 1]['body'], $old->body); }
            }
            $page = $this->page($target, $blocks); $original = $page->getAttributes();
            $restored = Package::preserveCoursePage($page); self::assertNotSame($page, $restored); self::assertSame($original, $page->getAttributes());
            self::assertSame($before['targets'][$i]['before']['page']['subtitle_text'], $restored->text);
            self::assertSame(self::COUNTS[$i] + 1, $restored->textBlocks->count());
        }
        foreach (['locale', 'ancestry', 'nested-ancestry', 'missing-contract', 'wrong-bank', 'presentation'] as $case) {
            $badMaster = $master; $badPatch = $patch; $badContract = $contract;
            if ($case === 'locale') { $badMaster['lessons'][0]['locale'] = 'en'; }
            if ($case === 'ancestry') { $badPatch['targets'][0]['ancestry'] = ['other']; }
            if ($case === 'nested-ancestry') { $badPatch['targets'][2]['ancestry'] = ['passive-voice-infinitives-gerund']; }
            if ($case === 'missing-contract') { $badContract['targets'] = []; }
            if ($case === 'wrong-bank') { $badContract['targets'][0]['bank_seeders'] = self::BANKS[1]; }
            if ($case === 'presentation') { $badMaster['lessons'][0]['blocks'][1]['data']['view'] = 'foreign'; }
            $badPatch['targets'][0]['after'] = Package::definition($badMaster['lessons'][0], $before['targets'][0]['before']);
            $caught = null; try { Package::validate($before, $badPatch, $badMaster, $badContract); } catch (RuntimeException $error) { $caught = $error; }
            self::assertInstanceOf(RuntimeException::class, $caught, $case);
        }
    }
}
