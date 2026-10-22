<?php

namespace Tests\Feature;

use App\Http\Controllers\PageController;
use App\Models\Page;
use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M74OtherPronounsBasics;
use App\Support\M75PronounForms as Package;
use App\Support\TheoryContentSource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

/** Unsaved exact models only. No schema fixtures, working .env, HTTP, browser or progress writes. */
class M75PronounFormsRuntimeTest extends TestCase
{
    /** Accepted M74 visual/interaction bytes, compared after only existing CRLF/LF transport normalization. */
    private const NATIVE_LF_PINS = [
        'resources/views/engram/theory/blocks-v3/practice-set.blade.php' => 'c15696702486805e66fd3fb15937f724f294ea518dfb918951747919cffe758d',
        'resources/views/components/text-block-practice-questions.blade.php' => 'c224042fcc5fe767064a5b175369c020de4cb61ecfbdf7f73f9e5679385795f6',
        'resources/views/theory/content.blade.php' => 'b1d48407722b3e5542b141c2ef0f7dba02c1d681d2d52698972c2d734eb0059e',
        'resources/css/theory-unified-design.css' => '9ec6881a0878036a9ebeed1f43c7a15f4323377721ec8046df2c605855030ef8',
        'resources/views/theory/components/usage.blade.php' => '5320ce28cf15b1f27129e9db7d1c34e0d4626c3f49b3fa00de5bec6462ca920a',
        'resources/views/theory/components/table.blade.php' => '573308e4c38d1a02ad3ef05f98a1897f1e51d16b8d9e80dee98302c051da2bd9',
        'resources/views/theory/components/mistake.blade.php' => '604803be11a35f3233ee1f7ca8b83d61ef2a06dab0542f01196157bca32ef7ba',
        'resources/views/theory/components/form.blade.php' => '53f3061ebb8660fb7405786f8430dadd71278b45b88fc63b5f6e05887b4ab855',
        'resources/views/components/theory-practice-option.blade.php' => 'b1be20733701a61ffb3e04dba128f01fe491212a9924a15e4936477c1665befa',
        'resources/views/components/theory-practice-input.blade.php' => '0ff9ebf6f806269e4a3f77268e021ed0f9df0d50e783c0a4c75c9a87cd24ceb6',
        'resources/views/components/theory-practice-token.blade.php' => 'a94cecf6b38a359280250b48fac079df30420681fb7cfc76cd2be1b76a77fe1d',
    ];

    /** Served ROOT had later accepted/foreign work before M75; preserve this complete independently captured profile too. */
    private const SERVED_BEFORE_LF_PINS = [
        'resources/views/engram/theory/blocks-v3/practice-set.blade.php' => 'a419b43d853126d88bc43683bddeb4d8efd190b08d1bfcf60944a005ad122ba7',
        'resources/views/components/text-block-practice-questions.blade.php' => 'e1aa01056889ae4396f8208c645a4e0a82f15ebb1824efb5f66bd0a48a4e8a55',
        'resources/views/theory/content.blade.php' => 'b1d48407722b3e5542b141c2ef0f7dba02c1d681d2d52698972c2d734eb0059e',
        'resources/css/theory-unified-design.css' => '9ec6881a0878036a9ebeed1f43c7a15f4323377721ec8046df2c605855030ef8',
        'resources/views/theory/components/usage.blade.php' => '5320ce28cf15b1f27129e9db7d1c34e0d4626c3f49b3fa00de5bec6462ca920a',
        'resources/views/theory/components/table.blade.php' => '573308e4c38d1a02ad3ef05f98a1897f1e51d16b8d9e80dee98302c051da2bd9',
        'resources/views/theory/components/mistake.blade.php' => '604803be11a35f3233ee1f7ca8b83d61ef2a06dab0542f01196157bca32ef7ba',
        'resources/views/theory/components/form.blade.php' => '53f3061ebb8660fb7405786f8430dadd71278b45b88fc63b5f6e05887b4ab855',
        'resources/views/components/theory-practice-option.blade.php' => 'b1be20733701a61ffb3e04dba128f01fe491212a9924a15e4936477c1665befa',
        'resources/views/components/theory-practice-input.blade.php' => '0ff9ebf6f806269e4a3f77268e021ed0f9df0d50e783c0a4c75c9a87cd24ceb6',
        'resources/views/components/theory-practice-token.blade.php' => 'a94cecf6b38a359280250b48fac079df30420681fb7cfc76cd2be1b76a77fe1d',
    ];

    private function sourceRoot(): string
    {
        return dirname(__DIR__, 2);
    }

    private function release(): array
    {
        $release = Package::load($this->sourceRoot());
        Package::validate($release[0], $release[1], $release[2], $release[3]);
        // The isolated application may be bootstrapped from served ROOT while tests reside in WT.
        // Runtime methods resolve base_path(), so do not silently substitute different artifact bytes.
        foreach (Package::profile()['artifacts'] as $path => $sha) {
            self::assertSame($sha, hash_file('sha256', base_path($path)), 'Runtime root must mirror this explicit frozen source: '.$path);
        }
        return $release;
    }

    private function block(array $target, array $source, int $slot): TextBlock
    {
        $block = new TextBlock;
        $block->setRawAttributes([
            'id' => 7500000 + $target['page_binding']['id'] * 100 + $slot,
            'uuid' => M26DetailPackage::uuid($target['identity'], $source, $slot + 1),
            'seeder' => $target['identity'], 'locale' => 'uk',
            'page_id' => $target['page_binding']['id'], 'page_category_id' => $target['page_binding']['page_category_id'],
            'sort_order' => $slot + 1, 'type' => $source['type'], 'column' => $source['column'],
            'heading' => $source['heading'] ?? null, 'css_class' => $source['css_class'] ?? null,
            'level' => $source['level'] ?? null, 'body' => $source['body'],
        ], true);
        $block->setRelation('tags', new Collection);
        $block->setRelation('page', null);
        return $block;
    }

    private function subtitle(array $target, array $old): TextBlock
    {
        $source = ['type' => 'subtitle', 'column' => 'header', 'heading' => null, 'css_class' => null,
            'level' => $old['page']['subtitle_level'] ?? null, 'uuid_key' => $old['page']['subtitle_uuid_key'] ?? 'subtitle'];
        $block = new TextBlock;
        $block->setRawAttributes([
            'id' => 7500000 + $target['page_binding']['id'] * 100,
            'uuid' => M26DetailPackage::uuid($target['identity'], $source, 0),
            'seeder' => $target['identity'], 'locale' => 'uk',
            'page_id' => $target['page_binding']['id'], 'page_category_id' => $target['page_binding']['page_category_id'],
            'sort_order' => 0, 'type' => 'subtitle', 'column' => 'header', 'heading' => null,
            'css_class' => null, 'level' => $source['level'], 'body' => $target['after']['page']['subtitle_html'],
        ], true);
        $block->setRelation('tags', new Collection);
        $block->setRelation('page', null);
        return $block;
    }

    private function page(array $target, array $old): Page
    {
        $page = new Page;
        $page->setRawAttributes([
            'id' => $target['page_binding']['id'], 'page_category_id' => $target['page_binding']['page_category_id'],
            'seeder' => $target['identity'], 'slug' => $target['slug'], 'title' => $old['page']['title'],
            'type' => 'theory', 'text' => $target['after']['page']['subtitle_text'],
        ], true);
        $blocks = [$this->subtitle($target, $old)];
        foreach ($target['after']['page']['blocks'] as $slot => $source) { $blocks[] = $this->block($target, $source, $slot); }
        $foreign = clone $blocks[1];
        $foreign->setRawAttributes(array_replace($foreign->getAttributes(), [
            'id' => 7599000 + $target['page_binding']['id'], 'uuid' => 'm75-isolated-foreign-locale-'.$target['page_binding']['id'],
            'locale' => 'en', 'sort_order' => 99, 'body' => 'Unchanged synthetic English content.',
        ]), true);
        $blocks[] = $foreign;
        $page->setRelation('textBlocks', new Collection($blocks));
        return $page;
    }

    /** Query logging makes accidental lazy-loading visible without creating any schema/rows. */
    private function withoutQueries(callable $callback): void
    {
        self::assertSame('testing', app()->environment());
        self::assertNotSame(realpath(base_path()), realpath(app()->environmentPath()));
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        self::assertSame('array', config('cache.default'));
        self::assertSame('array', config('session.driver'));
        DB::connection()->enableQueryLog();
        DB::connection()->flushQueryLog();
        try {
            $callback();
            self::assertSame([], DB::connection()->getQueryLog(), 'Unsaved presentation/preservation cannot query SQL or write progress.');
        } finally {
            DB::connection()->disableQueryLog();
            app()->setLocale('uk');
        }
    }

    public function test_exact_models_reach_canonical_theory_only_without_mutating_any_model_or_source_data(): void
    {
        app()->setLocale('uk');
        [, $patch, $master] = $this->release();
        $this->withoutQueries(function () use ($patch, $master): void {
            $seen = 0;
            foreach ($patch['targets'] as $i => $target) {
                foreach ($target['after']['page']['blocks'] as $slot => $source) {
                    ++$seen;
                    $block = $this->block($target, $source, $slot);
                    $original = $block->getAttributes();
                    $data = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR);
                    $unchanged = $data;
                    $presentation = Package::presentation($block, $data);
                    self::assertNotNull($presentation);
                    self::assertTrue(Package::validDesign($presentation['m42_native_design']));
                    self::assertSame('m75-native-v1', $presentation['m42_native_design']['package']);
                    self::assertSame($source['type'], $presentation['m42_native_design']['component']);
                    self::assertSame([], $presentation['points']);
                    self::assertSame($master['lessons'][$i]['blocks'][$slot]['data'], array_diff_key($presentation['data'], ['m75_native_v1' => true]));
                    $canonical = TheoryContentSource::resolve($block, ['m42StyleContext' => true]);
                    if (\App\Support\TheoryPresentation::nativeView($source['type']) === null) {
                        self::assertContains($source['type'], ['hero', 'navigation-chips']);
                        self::assertNull($canonical['view'], 'Existing auxiliary views are dispatched by the caller, not this native registry.');
                        self::assertNull($canonical['design']);
                        self::assertSame($data, $canonical['data']);
                    } else {
                        self::assertSame($presentation['m42_native_design'], $canonical['design']);
                        self::assertSame($presentation['data'], $canonical['data']);
                    }
                    foreach ([[], ['m42StyleContext' => false]] as $context) {
                        $otherCaller = TheoryContentSource::resolve($block, $context);
                        self::assertNull($otherCaller['design'], 'The theory caller opt-in does not leak to courses or standalone consumers.');
                        self::assertSame($data, $otherCaller['data']);
                    }
                    self::assertSame($unchanged, $data);
                    self::assertSame($original, $block->getAttributes());
                    self::assertSame([], $block->getDirty());
                    self::assertFalse($block->exists);
                }
            }
            self::assertSame(34, $seen);
        });
    }

    public function test_foreign_owner_locale_uuid_metadata_body_and_unknown_data_fall_back_without_queries_or_mutation(): void
    {
        app()->setLocale('uk');
        [, $patch] = $this->release();
        $this->withoutQueries(function () use ($patch): void {
            foreach ($patch['targets'] as $i => $target) {
                $slot = Package::PRACTICE_SOURCE_INDICES[$i];
                $source = $target['after']['page']['blocks'][$slot];
                $block = $this->block($target, $source, $slot);
                $data = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR);
                $presentation = Package::presentation($block, $data);
                self::assertNotNull($presentation);
                foreach ([
                    'seeder' => 'Foreign\\Owner', 'locale' => 'pl', 'page_id' => 1, 'page_category_id' => 16,
                    'uuid' => 'foreign', 'sort_order' => 99, 'type' => 'box', 'column' => 'right',
                    'heading' => 'Later heading', 'css_class' => 'unreviewed', 'level' => 'A2', 'body' => $source['body']."\n",
                ] as $field => $value) {
                    $foreign = clone $block;
                    $foreign->$field = $value;
                    $attrs = $foreign->getAttributes(); $dirty = $foreign->getDirty();
                    self::assertNull(Package::presentation($foreign, $data), $field);
                    self::assertSame($attrs, $foreign->getAttributes(), $field);
                    self::assertSame($dirty, $foreign->getDirty(), $field);
                }
                $unknown = $data; $unknown['unknown_decoration'] = null;
                self::assertNull(Package::presentation($block, $unknown));
                $missingMarker = $data; unset($missingMarker['m75_native_v1']);
                self::assertNull(Package::presentation($block, $missingMarker));
                foreach (['en', 'pl'] as $locale) {
                    app()->setLocale($locale);
                    self::assertNull(Package::presentation($block, $data));
                }
                app()->setLocale('uk');
                foreach ([
                    'package' => 'foreign-package', 'identity' => 'Foreign', 'locale' => 'en', 'uuid' => 'foreign',
                    'source_index' => 999, 'body_sha256' => str_repeat('0', 64), 'component' => 'm75-section',
                ] as $field => $value) {
                    $bad = $presentation['m42_native_design']; $bad[$field] = $value;
                    self::assertFalse(Package::validDesign($bad), $field);
                    self::assertNull(Package::pointPanels($bad), $field);
                }
            }
            self::assertFalse(Package::validDesign(null));
            self::assertNull(Package::pointPanels(null));
        });
    }

    public function test_standalone_help_restores_exact_old_teaching_and_practice_on_clones_only(): void
    {
        app()->setLocale('uk');
        [$before, $patch] = $this->release();
        $this->withoutQueries(function () use ($before, $patch): void {
            foreach ($patch['targets'] as $i => $target) {
                $old = $before['targets'][$i]['before'];
                foreach ($target['after']['page']['blocks'] as $slot => $source) {
                    $block = $this->block($target, $source, $slot);
                    $attrs = $block->getAttributes();
                    self::assertSame($block, Package::preserveExcludedTextBlock($block, 'theory'));
                    self::assertSame($block, Package::preserveExcludedTextBlock($block, 'course'));
                    $restored = Package::preserveExcludedTextBlock($block, 'standalone_test');
                    self::assertInstanceOf(TextBlock::class, $restored);
                    self::assertNotSame($block, $restored);
                    self::assertSame($old['page']['blocks'][$slot]['body'], $restored->body);
                    self::assertSame($block->uuid, $restored->uuid);
                    foreach (['type', 'column', 'heading', 'level', 'css_class'] as $field) {
                        self::assertSame($old['page']['blocks'][$slot][$field] ?? null, $restored->$field);
                    }
                    self::assertSame([], $restored->getDirty());
                    self::assertSame($attrs, $block->getAttributes());
                    self::assertSame([], $block->getDirty());
                    $foreign = clone $block; $foreign->body .= "\n";
                    self::assertSame($foreign, Package::preserveExcludedTextBlock($foreign, 'standalone_test'), 'Stale unknown bodies never get silently reverted.');
                }
                $subtitle = $this->subtitle($target, $old);
                $attrs = $subtitle->getAttributes();
                $restored = Package::preserveExcludedTextBlock($subtitle, 'standalone_test');
                self::assertNotSame($subtitle, $restored);
                self::assertSame($old['page']['subtitle_html'], $restored->body);
                self::assertSame($attrs, $subtitle->getAttributes());
                foreach (['seeder' => 'Foreign\\Owner', 'locale' => 'en', 'page_category_id' => 16] as $field => $value) {
                    $foreign = clone $subtitle; $foreign->$field = $value;
                    self::assertSame($foreign, Package::preserveExcludedTextBlock($foreign, 'standalone_test'));
                }
            }
            // Object-only helpers are never passed null by this dispatch probe.
            $unrelated = (object) ['type' => null, 'locale' => null, 'seeder' => null];
            self::assertSame($unrelated, Package::preserveExcludedTextBlock($unrelated, 'standalone_test'));
            self::assertNull(Package::preserveCoursePage(null));
        });
    }

    public function test_complete_course_clone_restores_old_uk_sources_and_keeps_foreign_locales_and_original_page_untouched(): void
    {
        app()->setLocale('uk');
        [$before, $patch] = $this->release();
        $this->withoutQueries(function () use ($before, $patch): void {
            foreach ($patch['targets'] as $i => $target) {
                $old = $before['targets'][$i]['before'];
                $page = $this->page($target, $old);
                $attrs = $page->getAttributes(); $originalBlocks = $page->textBlocks->toArray();
                $foreignLocale = $page->textBlocks->firstWhere('locale', 'en');
                $restored = Package::preserveCoursePage($page);
                self::assertInstanceOf(Page::class, $restored);
                self::assertNotSame($page, $restored);
                self::assertSame($old['page']['subtitle_text'], $restored->text);
                $uk = $restored->textBlocks->where('locale', 'uk')->values();
                self::assertCount(Package::CONTINUING_COUNTS[$i] + 1, $uk);
                self::assertSame($old['page']['subtitle_html'], $uk->firstWhere('type', 'subtitle')->body);
                foreach ($old['page']['blocks'] as $slot => $source) {
                    $uuid = M26DetailPackage::uuid($target['identity'], $source, $slot + 1);
                    self::assertSame($source['body'], $uk->firstWhere('uuid', $uuid)->body);
                }
                self::assertSame($foreignLocale, $restored->textBlocks->firstWhere('locale', 'en'));
                self::assertSame($attrs, $page->getAttributes());
                self::assertSame($originalBlocks, $page->textBlocks->toArray());
                self::assertSame([], $page->getDirty());
                foreach (['title' => 'Foreign', 'slug' => 'foreign', 'seeder' => 'Foreign\\Owner', 'page_category_id' => 16, 'text' => 'Unreviewed later text.'] as $field => $value) {
                    $foreign = clone $page; $foreign->$field = $value;
                    self::assertSame($foreign, Package::preserveCoursePage($foreign), $field);
                }
                $partial = clone $page;
                $partial->setRelation('textBlocks', $partial->textBlocks->slice(1)->values());
                self::assertSame($partial, Package::preserveCoursePage($partial), 'Incomplete relations cannot authorize restoration.');
                $stale = $this->page($target, $old);
                $stale->textBlocks[1]->body .= "\n";
                self::assertSame($stale, Package::preserveCoursePage($stale), 'One changed block makes the whole page fall back.');
                $unloaded = clone $page; $unloaded->unsetRelation('textBlocks');
                self::assertSame($unloaded, Package::preserveCoursePage($unloaded));
            }
        });
    }

    public function test_native_point_and_correction_adapters_keep_paired_translations_without_orphan_disclosures(): void
    {
        app()->setLocale('uk');
        [, $patch] = $this->release();
        $this->withoutQueries(function () use ($patch): void {
            foreach ($patch['targets'] as $target) {
                foreach ($target['after']['page']['blocks'] as $slot => $source) {
                    $block = $this->block($target, $source, $slot);
                    $data = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR);
                    $presentation = Package::presentation($block, $data);
                    self::assertNotNull($presentation);
                    self::assertSame([], $presentation['points']);
                    $design = $presentation['m42_native_design'];
                    $points = Package::pointPanels($design);
                    if ($source['type'] !== 'usage-panels') {
                        self::assertNull($points);
                    } else {
                        self::assertCount(count($data['sections']), $points['points_by_index']);
                        foreach ($data['sections'] as $n => $section) {
                            self::assertSame($section['label'], $points['points_by_index'][$n]['label']);
                            self::assertSame($section['color'], $points['points_by_index'][$n]['accent']);
                            self::assertSame([hash('sha256', $section['description'])], $points['points_by_index'][$n]['description_sha256']);
                        }
                    }
                    if ($source['type'] !== 'mistakes-grid') { continue; }
                    foreach ($data['items'] as $item) {
                        $correction = Package::correction($item, $design);
                        self::assertNotNull($correction);
                        self::assertSame($item['wrong_en'], (string) $correction['wrong_en']);
                        self::assertSame($item['wrong_ua'], (string) $correction['wrong_uk']);
                        self::assertSame($item['right_en'], (string) $correction['right_en']);
                        self::assertSame($item['right_ua'], (string) $correction['right_uk']);
                        $bad = $item; $bad['right_ua'] .= ' Пізніша правка.';
                        self::assertNull(Package::correction($bad, $design));
                    }
                    self::assertNull(Package::correction($data['items'][0], null));
                }
            }
        });
    }

    public function test_existing_title_extractor_preserves_plain_possessive_and_first_strong_other_h1_values(): void
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
        self::assertMatchesRegularExpression('/^<p>[^<>]+<\/p>$/suD', $master['lessons'][0]['subtitle_html']);
        self::assertStringNotContainsString('<strong>', $master['lessons'][0]['subtitle_html']);
        self::assertStringContainsString('<strong>Зворотні займенники</strong>', $master['lessons'][1]['subtitle_html']);
        self::assertStringContainsString('<strong>Indefinite pronouns</strong>', $master['lessons'][2]['subtitle_html']);
        self::assertSame('captured-plain', Package::profile()['bindings'][0]['before_subtitle']['mode']);
    }

    public function test_native_visual_and_interaction_sources_and_active_m74_artifacts_are_unchanged(): void
    {
        $current = [];
        foreach (self::NATIVE_LF_PINS as $path => $sha) {
            $bytes = file_get_contents($this->sourceRoot().'/'.$path);
            $current[$path] = hash('sha256', str_replace("\r\n", "\n", $bytes));
        }
        self::assertTrue($current === self::NATIVE_LF_PINS || $current === self::SERVED_BEFORE_LF_PINS,
            'Only one complete accepted-M74 or independently captured served-BEFORE visual/interaction profile is allowed; no arbitrary or mixed bytes.');
        self::assertSame('1.0.1', M74OtherPronounsBasics::profile()['version']);
        foreach (M74OtherPronounsBasics::profile()['artifacts'] as $path => $sha) {
            self::assertSame($sha, hash_file('sha256', $this->sourceRoot().'/'.$path));
        }
        $native = file_get_contents($this->sourceRoot().'/resources/views/engram/theory/blocks-v3/practice-set.blade.php');
        self::assertStringNotContainsString('m75', mb_strtolower($native), 'The new teaching package is not implemented by a practice engine branch.');
    }
}
