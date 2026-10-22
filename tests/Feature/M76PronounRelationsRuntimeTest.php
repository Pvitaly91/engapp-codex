<?php

namespace Tests\Feature;

use App\Http\Controllers\PageController;
use App\Models\Page;
use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M75PronounForms;
use App\Support\M76PronounRelations as Package;
use App\Support\TheoryContentSource;
use App\Support\TheoryPresentation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use ReflectionMethod;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Frozen package + unsaved models only: no migrations, queries, working .env, HTTP or progress writes. */
class M76PronounRelationsRuntimeTest extends TestCase
{
    private function release(): array
    {
        $root = dirname(__DIR__, 2); $release = Package::load($root);
        Package::validate($release[0], $release[1], $release[2], $release[3]);
        foreach (Package::profile()['artifacts'] as $path => $sha) {
            self::assertSame($sha, hash_file('sha256', base_path($path)), 'Runtime must use the reviewed frozen bytes: '.$path);
        }
        return $release;
    }

    private function block(array $target, array $source, int $slot): TextBlock
    {
        $block = new TextBlock;
        $block->setRawAttributes([
            'id' => 7600000 + $target['page_binding']['id'] * 100 + $slot,
            'uuid' => M26DetailPackage::uuid($target['identity'], $source, $slot + 1),
            'seeder' => $target['identity'], 'locale' => 'uk', 'page_id' => $target['page_binding']['id'],
            'page_category_id' => $target['page_binding']['page_category_id'], 'sort_order' => $slot + 1,
            'type' => $source['type'], 'column' => $source['column'], 'heading' => $source['heading'] ?? null,
            'css_class' => $source['css_class'] ?? null, 'level' => $source['level'] ?? null, 'body' => $source['body'],
        ], true);
        $block->setRelation('tags', new Collection); $block->setRelation('page', null);
        return $block;
    }

    private function subtitle(array $target, array $old): TextBlock
    {
        $source = ['type' => 'subtitle', 'column' => 'header', 'heading' => null, 'css_class' => null,
            'level' => $old['page']['subtitle_level'] ?? null, 'uuid_key' => $old['page']['subtitle_uuid_key'] ?? 'subtitle'];
        $block = $this->block($target, $source + ['body' => $target['after']['page']['subtitle_html']], -1);
        return $block;
    }

    private function page(array $target, array $old): Page
    {
        $page = new Page;
        $page->setRawAttributes(['id' => $target['page_binding']['id'], 'page_category_id' => $target['page_binding']['page_category_id'],
            'seeder' => $target['identity'], 'slug' => $target['slug'], 'title' => $old['page']['title'],
            'type' => 'theory', 'text' => $target['after']['page']['subtitle_text']], true);
        $blocks = [$this->subtitle($target, $old)];
        foreach ($target['after']['page']['blocks'] as $slot => $source) { $blocks[] = $this->block($target, $source, $slot); }
        $foreign = clone $blocks[1];
        $foreign->setRawAttributes(array_replace($foreign->getAttributes(), [
            'id' => 7699000 + $target['page_binding']['id'], 'uuid' => 'm76-isolated-en-'.$target['page_binding']['id'],
            'locale' => 'en', 'sort_order' => 99, 'body' => 'Unchanged synthetic English content.',
        ]), true);
        $blocks[] = $foreign; $page->setRelation('textBlocks', new Collection($blocks));
        return $page;
    }

    private function withoutQueries(callable $callback): void
    {
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        self::assertSame('testing', app()->environment());
        self::assertNotSame(realpath(base_path()), realpath(app()->environmentPath()));
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        self::assertSame('array', config('cache.default')); self::assertSame('array', config('session.driver'));
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        try {
            $callback();
            self::assertSame([], DB::connection()->getQueryLog(), 'Presentation and consumer preservation must remain queryless.');
        } finally { DB::connection()->disableQueryLog(); app()->setLocale('uk'); }
    }

    public function test_exact_native_models_keep_null_levels_canonical_components_and_theory_caller_boundary(): void
    {
        app()->setLocale('uk'); [, $patch, $master] = $this->release();
        $this->withoutQueries(function () use ($patch, $master): void {
            $seen = 0;
            foreach ($patch['targets'] as $i => $target) {
                self::assertCount(1, array_filter($target['after']['page']['blocks'], static fn ($source) => $source['type'] === 'navigation-chips'),
                    'The actual theory caller renders firstWhere(navigation-chips), so a second group would be hidden.');
                if ($i === 2) {
                    $footer = $target['after']['page']['blocks'][10];
                    self::assertSame('forms-grid', $footer['type']); self::assertSame('footer', $footer['column']);
                    $links = json_decode($footer['body'], true, flags: JSON_THROW_ON_ERROR)['items']; self::assertCount(2, $links);
                    self::assertSame(['← Reflexive Pronouns', 'Each / Every / All →'], array_column($links, 'label'));
                    foreach ($links as $link) { self::assertNotEmpty($link['url']); self::assertNotEmpty($link['title']); self::assertNotEmpty($link['subtitle']); }
                }
                foreach ($target['after']['page']['blocks'] as $slot => $source) {
                    ++$seen; $block = $this->block($target, $source, $slot); $attrs = $block->getAttributes();
                    $data = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR); $unchanged = $data;
                    self::assertNull($block->level, 'Displayed pedagogical level must not replace technical SQL NULL.');
                    $presentation = Package::presentation($block, $data); self::assertNotNull($presentation);
                    $design = $presentation['m42_native_design']; self::assertTrue(Package::validDesign($design));
                    self::assertSame('m76-native-v1', $design['package']); self::assertSame($source['type'], $design['component']);
                    self::assertSame($master['lessons'][$i]['blocks'][$slot]['data'],
                        array_diff_key($presentation['data'], ['m76_native_v1' => true]));
                    $canonical = TheoryContentSource::resolve($block, ['m42StyleContext' => true]);
                    if (TheoryPresentation::nativeView($source['type']) === null) {
                        self::assertContains($source['type'], ['hero', 'navigation-chips']);
                        self::assertNull($canonical['view']); self::assertNull($canonical['design']); self::assertSame($data, $canonical['data']);
                    } else {
                        self::assertSame(TheoryPresentation::nativeView($source['type']), $canonical['view']);
                        self::assertSame($design, $canonical['design']); self::assertSame($presentation['data'], $canonical['data']);
                    }
                    foreach ([[], ['m42StyleContext' => false]] as $context) {
                        $other = TheoryContentSource::resolve($block, $context);
                        self::assertNull($other['design']); self::assertSame($data, $other['stored']); self::assertSame($data, $other['data']);
                    }
                    self::assertSame($unchanged, $data); self::assertSame($attrs, $block->getAttributes());
                    self::assertSame([], $block->getDirty()); self::assertFalse($block->exists);
                }
            }
            self::assertSame(36, $seen);
        });
    }

    public function test_foreign_and_stale_bindings_return_full_stored_fallback_without_queries_or_mutation(): void
    {
        app()->setLocale('uk'); [, $patch] = $this->release();
        $this->withoutQueries(function () use ($patch): void {
            foreach ($patch['targets'] as $i => $target) {
                $slot = Package::PRACTICE_SOURCE_INDICES[$i]; $source = $target['after']['page']['blocks'][$slot];
                $block = $this->block($target, $source, $slot); $data = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR);
                $exact = Package::presentation($block, $data); self::assertNotNull($exact);
                foreach (['seeder' => 'Foreign\\Owner', 'locale' => 'pl', 'page_id' => 1, 'page_category_id' => 16,
                    'uuid' => 'foreign', 'sort_order' => 99, 'type' => 'box', 'column' => 'right', 'heading' => 'Later heading',
                    'css_class' => 'unapproved', 'level' => 'A2', 'body' => $source['body']."\n"] as $field => $value) {
                    $foreign = clone $block; $foreign->$field = $value; $attrs = $foreign->getAttributes(); $dirty = $foreign->getDirty();
                    self::assertNull(Package::presentation($foreign, $data), $field);
                    $fallback = TheoryContentSource::resolve($foreign, ['m42StyleContext' => true]);
                    self::assertNull($fallback['design'], $field); self::assertSame($data, $fallback['stored'], $field);
                    self::assertSame($data, $fallback['data'], 'Full stored content, including all practice tasks, must remain visible: '.$field);
                    self::assertSame($attrs, $foreign->getAttributes()); self::assertSame($dirty, $foreign->getDirty());
                }
                foreach (['unknown_decoration' => null, 'm76_native_v1' => ['key' => 'foreign', 'detail_keys' => []]] as $field => $value) {
                    $changed = $data; $changed[$field] = $value; self::assertNull(Package::presentation($block, $changed));
                }
                $unmarked = $data; unset($unmarked['m76_native_v1']); self::assertNull(Package::presentation($block, $unmarked));
                foreach (['en', 'pl'] as $locale) { app()->setLocale($locale); self::assertNull(Package::presentation($block, $data)); }
                app()->setLocale('uk');
                foreach (['package' => 'foreign', 'identity' => 'Foreign', 'locale' => 'en', 'uuid' => 'foreign',
                    'source_index' => 999, 'body_sha256' => str_repeat('0', 64), 'component' => 'm76-section'] as $field => $value) {
                    $bad = $exact['m42_native_design']; $bad[$field] = $value;
                    self::assertFalse(Package::validDesign($bad)); self::assertNull(Package::pointPanels($bad));
                }
            }
            self::assertFalse(Package::validDesign(null)); self::assertNull(Package::pointPanels(null));
        });
    }

    public function test_excluded_test_clones_restore_every_raw_legacy_body_heading_type_and_null_metadata(): void
    {
        app()->setLocale('uk'); [$before, $patch] = $this->release();
        $this->withoutQueries(function () use ($before, $patch): void {
            $legacyBoxes = $oldHeadings = 0;
            foreach ($patch['targets'] as $i => $target) {
                $old = $before['targets'][$i]['before'];
                foreach ($target['after']['page']['blocks'] as $slot => $source) {
                    $block = $this->block($target, $source, $slot); $attrs = $block->getAttributes();
                    foreach (['theory', 'course', 'unknown'] as $caller) { self::assertSame($block, Package::preserveExcludedTextBlock($block, $caller)); }
                    $restored = Package::preserveExcludedTextBlock($block, 'standalone_test');
                    self::assertInstanceOf(TextBlock::class, $restored); self::assertNotSame($block, $restored);
                    self::assertSame($old['page']['blocks'][$slot]['body'], $restored->body);
                    foreach (['type', 'column', 'heading', 'level', 'css_class'] as $field) {
                        self::assertSame($old['page']['blocks'][$slot][$field] ?? null, $restored->$field);
                    }
                    self::assertSame($block->uuid, $restored->uuid); self::assertSame($block->sort_order, $restored->sort_order);
                    self::assertSame($attrs, $block->getAttributes()); self::assertSame([], $block->getDirty()); self::assertSame([], $restored->getDirty());
                    if ($i === 2 && $restored->type === 'box') { ++$legacyBoxes; if ($restored->heading !== null) { ++$oldHeadings; } }
                    foreach (['body' => $source['body']."\n", 'sort_order' => 99, 'level' => 'A2', 'seeder' => 'Foreign\\Owner'] as $field => $value) {
                        $foreign = clone $block; $foreign->$field = $value;
                        self::assertSame($foreign, Package::preserveExcludedTextBlock($foreign, 'standalone_test'), $field);
                    }
                }
                $subtitle = $this->subtitle($target, $old); $attrs = $subtitle->getAttributes();
                $restored = Package::preserveExcludedTextBlock($subtitle, 'standalone_test');
                self::assertNotSame($subtitle, $restored); self::assertSame($old['page']['subtitle_html'], $restored->body);
                self::assertSame($attrs, $subtitle->getAttributes());
            }
            self::assertSame(10, $legacyBoxes); self::assertSame(4, $oldHeadings);
            $unrelated = (object) ['type' => null, 'locale' => null, 'seeder' => null];
            self::assertSame($unrelated, Package::preserveExcludedTextBlock($unrelated, 'standalone_test'));
            self::assertNull(Package::preserveCoursePage(null));
        });
    }

    public function test_complete_course_clone_restores_old_sources_and_foreign_locales_without_mutating_original_models(): void
    {
        app()->setLocale('uk'); [$before, $patch] = $this->release();
        $this->withoutQueries(function () use ($before, $patch): void {
            foreach ($patch['targets'] as $i => $target) {
                $old = $before['targets'][$i]['before']; $page = $this->page($target, $old);
                $attrs = $page->getAttributes(); $originalBlocks = $page->textBlocks->toArray(); $foreignLocale = $page->textBlocks->firstWhere('locale', 'en');
                $restored = Package::preserveCoursePage($page); self::assertInstanceOf(Page::class, $restored); self::assertNotSame($page, $restored);
                self::assertSame($old['page']['subtitle_text'], $restored->text); self::assertSame([], $restored->getDirty());
                $uk = $restored->textBlocks->where('locale', 'uk')->values(); self::assertCount(13, $uk);
                self::assertSame($old['page']['subtitle_html'], $uk->firstWhere('type', 'subtitle')->body);
                foreach ($old['page']['blocks'] as $slot => $source) {
                    $uuid = M26DetailPackage::uuid($target['identity'], $source, $slot + 1); $block = $uk->firstWhere('uuid', $uuid);
                    self::assertNotNull($block); self::assertSame($source['body'], $block->body);
                    foreach (['type', 'column', 'heading', 'level', 'css_class'] as $field) { self::assertSame($source[$field] ?? null, $block->$field); }
                    self::assertSame([], $block->getDirty());
                }
                self::assertSame($foreignLocale, $restored->textBlocks->firstWhere('locale', 'en'));
                self::assertSame($attrs, $page->getAttributes()); self::assertSame($originalBlocks, $page->textBlocks->toArray()); self::assertSame([], $page->getDirty());
                foreach (['title' => 'Foreign', 'slug' => 'foreign', 'seeder' => 'Foreign\\Owner', 'page_category_id' => 16, 'text' => 'Later text.'] as $field => $value) {
                    $foreign = clone $page; $foreign->$field = $value; self::assertSame($foreign, Package::preserveCoursePage($foreign), $field);
                }
                $partial = clone $page; $partial->setRelation('textBlocks', $partial->textBlocks->slice(1)->values());
                self::assertSame($partial, Package::preserveCoursePage($partial));
                foreach (['body' => $target['after']['page']['blocks'][0]['body']."\n", 'sort_order' => 99, 'level' => 'A2'] as $field => $value) {
                    $stale = $this->page($target, $old); $stale->textBlocks[1]->$field = $value;
                    self::assertSame($stale, Package::preserveCoursePage($stale), $field);
                }
                $unloaded = clone $page; $unloaded->unsetRelation('textBlocks'); self::assertSame($unloaded, Package::preserveCoursePage($unloaded));
            }
        });
    }

    public function test_native_point_and_correction_adapters_preserve_exact_bilingual_author_data(): void
    {
        app()->setLocale('uk'); [, $patch] = $this->release();
        $this->withoutQueries(function () use ($patch): void {
            foreach ($patch['targets'] as $target) {
                foreach ($target['after']['page']['blocks'] as $slot => $source) {
                    $data = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR);
                    $presentation = Package::presentation($this->block($target, $source, $slot), $data); self::assertNotNull($presentation);
                    $design = $presentation['m42_native_design']; $panels = Package::pointPanels($design);
                    if ($source['type'] === 'usage-panels') {
                        self::assertCount(count($data['sections']), $panels['points_by_index']);
                        foreach ($data['sections'] as $n => $section) {
                            self::assertSame($section['label'], $panels['points_by_index'][$n]['label']);
                            self::assertSame($section['color'], $panels['points_by_index'][$n]['accent']);
                            self::assertSame([hash('sha256', $section['description'])], $panels['points_by_index'][$n]['description_sha256']);
                        }
                    } else { self::assertNull($panels); }
                    if ($source['type'] !== 'mistakes-grid') { continue; }
                    foreach ($data['items'] as $item) {
                        $correction = Package::correction($item, $design); self::assertNotNull($correction);
                        foreach (['wrong_en' => 'wrong_en', 'wrong_ua' => 'wrong_uk', 'right_en' => 'right_en', 'right_ua' => 'right_uk'] as $from => $to) {
                            self::assertSame($item[$from], (string) $correction[$to]);
                        }
                        $stale = $item; $stale['right_ua'] .= ' Непогоджена правка.'; self::assertNull(Package::correction($stale, $design));
                    }
                    self::assertNull(Package::correction($data['items'][0], null));
                }
            }
        });
    }

    public function test_existing_title_extractor_preserves_all_raw_titles_and_rendered_h1_values(): void
    {
        [$before, $patch, $master] = $this->release();
        $controller = (new ReflectionClass(PageController::class))->newInstanceWithoutConstructor();
        $method = new ReflectionMethod(PageController::class, 'extractLocalizedTitleFromSubtitle');
        foreach ($patch['targets'] as $i => $target) {
            self::assertSame($before['targets'][$i]['before']['page']['title'], $target['after']['page']['title']);
            foreach ([$before['targets'][$i]['before']['page']['subtitle_html'], $target['after']['page']['subtitle_html']] as $subtitle) {
                self::assertSame(Package::RENDERED_TITLES[$i], $method->invoke($controller, $subtitle));
            }
            self::assertSame(Package::RENDERED_TITLES[$i], $master['lessons'][$i]['preserved_h1']);
        }
        self::assertMatchesRegularExpression('/^<p>[^<>]+<\/p>$/suD', $master['lessons'][1]['subtitle_html']);
        self::assertStringContainsString('<strong>Reciprocal pronouns</strong>', $master['lessons'][2]['subtitle_html']);
    }

    public function test_m75_frozen_package_is_unchanged_and_m76_is_not_an_engine_or_view_branch(): void
    {
        $root = dirname(__DIR__, 2);
        foreach (M75PronounForms::profile()['artifacts'] as $path => $sha) { self::assertSame($sha, hash_file('sha256', $root.'/'.$path)); }
        foreach (['resources/views/engram/theory/blocks-v3/practice-set.blade.php',
            'resources/views/components/text-block-practice-questions.blade.php', 'resources/css/theory-unified-design.css'] as $path) {
            self::assertStringNotContainsString('m76', mb_strtolower(file_get_contents($root.'/'.$path)),
                'Content migration must not introduce a release-specific renderer, engine or design branch: '.$path);
        }
        foreach (Package::AFTER_TYPES as $types) {
            foreach ($types as $type) { self::assertDoesNotMatchRegularExpression('/^m[0-9]+-/', $type); }
        }
    }
}
