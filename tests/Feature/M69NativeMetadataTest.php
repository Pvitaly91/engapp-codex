<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M69AdjectivesAdverbs as Package;
use App\Support\TheoryContentSource;
use App\Support\TheoryNativeContentRelease;
use App\Support\TheoryPresentation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Real native policy on in-memory models; no SQL fixtures, custom view or old metadata migration. */
class M69NativeMetadataTest extends TestCase
{
    private function block(array $target, array $source, int $slot): TextBlock
    {
        $block = new TextBlock;
        $block->setRawAttributes(['id' => 6900000 + $target['page_binding']['id'] * 100 + $slot,
            'uuid' => M26DetailPackage::uuid($target['identity'], $source, $slot + 1),
            'seeder' => $target['identity'], 'locale' => 'uk', 'page_id' => $target['page_binding']['id'],
            'page_category_id' => $target['page_binding']['page_category_id'], 'sort_order' => $slot + 1,
            'type' => $source['type'], 'column' => $source['column'], 'heading' => $source['heading'] ?? null,
            'css_class' => $source['css_class'] ?? null, 'level' => $source['level'] ?? null, 'body' => $source['body']], true);
        $block->setRelation('tags', new Collection); $block->setRelation('page', null);
        return $block;
    }

    public function test_all_twenty_three_continuing_metadata_slots_and_explicit_subtitle_uuid_transition_remain_exact(): void
    {
        [$before, $patch, $master] = Package::load(); $release = new TheoryNativeContentRelease(Package::profile());
        $count = 0;
        foreach ($before['targets'] as $i => $target) {
            $old = $target['before']; $projected = $release->projectedBefore($old);
            self::assertSame($i === 2, $release->subtitleWasAbsent($old));
            if ($i < 2) { self::assertSame($old, $projected); }
            foreach ($old['page']['blocks'] as $slot => $source) {
                ++$count; $after = $patch['targets'][$i]['after']['page']['blocks'][$slot];
                self::assertSame(Package::CONTINUING_TYPES[$i][$slot], $source['type']);
                self::assertSame(Package::CONTINUING_LEVELS[$i][$slot], $source['level'] ?? null);
                self::assertSame(array_key_exists('level', $source), array_key_exists('level', $after));
                self::assertSame($source['level'] ?? null, $after['level'] ?? null);
                self::assertSame($source['column'], $after['column']);
                if ($i === 0 || $slot === 5) {
                    self::assertArrayNotHasKey('level', $source); self::assertArrayNotHasKey('level', $after);
                }
                $expected = $projected['page']['blocks'][$slot];
                $counter = $i === 2 ? $slot : $slot + 1;
                self::assertSame(M26DetailPackage::uuid($target['identity'], $source, $counter),
                    M26DetailPackage::uuid($target['identity'], $after, $slot + 1));
                if ($i === 2) {
                    self::assertArrayNotHasKey('uuid', $source);
                    self::assertSame(Package::EDING_CONTINUING_UUIDS[$slot], $after['uuid']);
                    self::assertSame($source + ['uuid' => Package::EDING_CONTINUING_UUIDS[$slot]], $expected);
                }
                unset($expected['body'], $after['body']);
                self::assertSame($expected, $after, 'No hidden heading, type, column, level or arbitrary UUID changes.');
            }
            self::assertSame(['A2', 'B1', 'A2'][$i], $master['lessons'][$i]['blocks'][[11, 6, 6][$i]]['level']);
        }
        self::assertSame(23, $count);
        self::assertArrayNotHasKey('subtitle_html', $before['targets'][2]['before']['page']);
        self::assertArrayNotHasKey('subtitle_text', $before['targets'][2]['before']['page']);
        $subtitle = $patch['targets'][2]['after']['page']['subtitle_html'];
        $binding = Package::profile()['bindings'][2]['after_subtitle'];
        self::assertSame(['mode' => 'plain-fallback',
            'sha256' => '89407f6847f0c4fd2fa484096afffac6c9ffd17b83b57a4b398013006283f644',
            'extracted_title' => null, 'h1' => '-ed / -ing Adjectives'], $binding);
        self::assertSame($binding['sha256'], hash('sha256', $subtitle));
        $controller = (new \ReflectionClass(\App\Http\Controllers\PageController::class))->newInstanceWithoutConstructor();
        $title = (new \ReflectionMethod(\App\Http\Controllers\PageController::class, 'extractLocalizedTitleFromSubtitle'))
            ->invoke($controller, $subtitle);
        self::assertNull($title);
        self::assertSame($binding['h1'], $title ?? $before['targets'][2]['before']['page']['title']);
        self::assertNotSame('', trim($patch['targets'][2]['after']['page']['subtitle_text']));
    }

    public function test_missing_subtitle_capture_requires_exact_absence_and_six_unique_old_counter_uuid_pins(): void
    {
        [$before] = Package::load(); $old = $before['targets'][2]['before'];
        $profile = Package::profile(); $unchanged = $old;
        self::assertTrue((new TheoryNativeContentRelease($profile))->subtitleWasAbsent($old));
        self::assertSame($unchanged, $old);
        foreach (['empty-source-field', 'empty-page-text', 'invented-row', 'wrong-owner', 'wrong-category',
            'short-vector', 'duplicate-vector', 'wrong-vector-order', 'wrong-level-vector'] as $case) {
            $p = $profile; $b = $old;
            if ($case === 'empty-source-field') { $b['page']['subtitle_text'] = ''; }
            if ($case === 'empty-page-text') { $p['bindings'][2]['before_subtitle']['page_text'] = ''; }
            if ($case === 'invented-row') { $p['bindings'][2]['before_subtitle']['subtitle_rows'] = 1; }
            if ($case === 'wrong-owner') { $b['seeder']['class'] = 'Database\\Seeders\\Page_V3\\Adjectives\\ForeignOwner'; }
            if ($case === 'wrong-category') { $b['page']['category']['slug'] = 'foreign'; }
            if ($case === 'short-vector') { array_pop($p['bindings'][2]['continuing_uuids']); }
            if ($case === 'duplicate-vector') { $p['bindings'][2]['continuing_uuids'][5] = $p['bindings'][2]['continuing_uuids'][0]; }
            if ($case === 'wrong-vector-order') { $p['bindings'][2]['continuing_uuids'] = array_reverse($p['bindings'][2]['continuing_uuids']); }
            if ($case === 'wrong-level-vector') { array_pop($p['bindings'][2]['continuing_levels']); }
            $caught = null;
            try { (new TheoryNativeContentRelease($p))->projectedBefore($b); } catch (RuntimeException $error) { $caught = $error; }
            self::assertInstanceOf(RuntimeException::class, $caught, $case);
        }
    }

    public function test_current_canonical_resolver_owns_all_twenty_six_exact_presentations_without_queries_or_model_mutation(): void
    {
        app()->setLocale('uk'); [$before, $patch, $master] = Package::load();
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        $count = 0;
        try {
            foreach ($patch['targets'] as $i => $target) {
                foreach ($target['after']['page']['blocks'] as $slot => $source) {
                    ++$count; $block = $this->block($target, $source, $slot); $attributes = $block->getAttributes();
                    $data = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR); $copy = $data;
                    $presentation = Package::presentation($block, $data);
                    self::assertNotNull($presentation); self::assertTrue(Package::validDesign($presentation['m42_native_design']));
                    self::assertSame($source['type'], $presentation['m42_native_design']['component']);
                    self::assertArrayNotHasKey('native_view', $presentation, 'Learning content cannot choose a view.');
                    self::assertArrayNotHasKey('stylesheet', $presentation);
                    self::assertSame($master['lessons'][$i]['blocks'][$slot]['data'] + ['m69_native_v1' => $data['m69_native_v1']], $presentation['data']);
                    $resolved = TheoryContentSource::resolve($block, ['m42StyleContext' => true]);
                    self::assertSame(TheoryPresentation::nativeView($source['type']), $resolved['view']);
                    if (in_array($source['type'], ['hero', 'navigation-chips'], true)) {
                        self::assertNull($resolved['presentation'], 'Caller-owned hero and footer stay outside content-block rendering.');
                    } else { self::assertSame($presentation, $resolved['presentation']); }
                    self::assertNull(TheoryContentSource::resolve($block)['presentation'], 'Explicit theory style context is required.');
                    if ($source['type'] === 'usage-panels') {
                        $panels = Package::pointPanels($presentation['m42_native_design']); self::assertNotNull($panels);
                        self::assertCount(count($master['lessons'][$i]['blocks'][$slot]['data']['sections']), $panels['points_by_index']);
                    } else { self::assertNull(Package::pointPanels($presentation['m42_native_design'])); }
                    self::assertSame($copy, $data); self::assertSame($attributes, $block->getAttributes()); self::assertSame([], $block->getDirty());
                }
            }
            self::assertSame(26, $count); self::assertSame([], DB::connection()->getQueryLog());
        } finally { DB::connection()->disableQueryLog(); app()->setLocale('uk'); }
    }

    public function test_foreign_owner_locale_page_category_vector_body_and_design_fall_back_without_changing_models(): void
    {
        app()->setLocale('uk'); [, $patch] = Package::load(); $target = $patch['targets'][0];
        $source = $target['after']['page']['blocks'][1]; $block = $this->block($target, $source, 1);
        $data = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR);
        self::assertNotNull(Package::presentation($block, $data));
        foreach (['seeder' => Package::OWNERS[1], 'locale' => 'en', 'page_id' => 52, 'page_category_id' => 279,
            'uuid' => 'foreign', 'sort_order' => 1, 'type' => 'usage-panels', 'column' => 'right',
            'heading' => 'Manual heading', 'css_class' => 'manual-style', 'level' => 'A2', 'body' => $source['body']."\n"] as $field => $value) {
            $foreign = clone $block; $foreign->$field = $value; $attrs = $foreign->getAttributes(); $dirty = $foreign->getDirty();
            self::assertNull(Package::presentation($foreign, $data), $field);
            self::assertSame($attrs, $foreign->getAttributes()); self::assertSame($dirty, $foreign->getDirty());
        }
        foreach (['en', 'pl'] as $locale) {
            app()->setLocale($locale); self::assertNull(Package::presentation($block, $data));
        }
        app()->setLocale('uk'); $badData = $data; $badData['rows'][0]['ua'] .= ' Ручна правка.';
        self::assertNull(Package::presentation($block, $badData), 'Matching model cannot authorize a different decoded payload.');
        $design = Package::presentation($block, $data)['m42_native_design'];
        foreach (['identity' => Package::OWNERS[1], 'locale' => 'en', 'uuid' => 'foreign', 'source_index' => 99,
            'body_sha256' => str_repeat('0', 64), 'component' => 'm69-custom-section'] as $field => $value) {
            $foreign = $design; $foreign[$field] = $value; self::assertFalse(Package::validDesign($foreign), $field);
        }
    }

    public function test_component_map_binds_exact_native_types_columns_levels_bodies_and_point_owners(): void
    {
        [, $patch, $master] = Package::load();
        $map = json_decode(File::get(base_path('docs/content/m69-adjectives-adverbs.components.v1.json')), true, flags: JSON_THROW_ON_ERROR)['component_map'];
        self::assertCount(26, $map); $offset = 0;
        foreach ($patch['targets'] as $i => $target) {
            foreach ($target['after']['page']['blocks'] as $slot => $source) {
                $entry = $map[$offset++]; $authored = $master['lessons'][$i]['blocks'][$slot];
                self::assertSame($target['identity'], $entry['identity']); self::assertSame($slot, $entry['source_index']);
                self::assertSame($authored['key'], $entry['key']); self::assertSame($source['type'], $entry['type']);
                self::assertSame($source['column'], $entry['column']); self::assertSame($source['level'] ?? null, $entry['level']);
                self::assertSame(M26DetailPackage::uuid($target['identity'], $source, $slot + 1), $entry['uuid']);
                self::assertSame(hash('sha256', $source['body']), $entry['body_sha256']);
                $details = array_values(array_filter($master['lessons'][$i]['details'], static fn ($d) => $d['owner_block_key'] === $authored['key']));
                self::assertSame($details, $entry['detail_owners']);
                if (array_key_exists('native_view', $entry)) { self::assertSame(TheoryPresentation::nativeView($source['type']), $entry['native_view']); }
                if (array_key_exists('semantic_component', $entry)) { self::assertSame($source['type'], $entry['semantic_component']); }
            }
        }
    }
}
