<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M70IntensifiersNounBasics as Package;
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
class M70NativeMetadataTest extends TestCase
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

    public function test_current_canonical_resolver_owns_all_thirty_three_exact_presentations_without_queries_or_model_mutation(): void
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
                    self::assertSame($master['lessons'][$i]['blocks'][$slot]['data'] + ['m70_native_v1' => $data['m70_native_v1']], $presentation['data']);
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
            self::assertSame(33, $count); self::assertSame([], DB::connection()->getQueryLog());
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
        app()->setLocale('uk'); $badData = $data; $badData['unreviewed'] = 'Ручна правка.';
        self::assertNull(Package::presentation($block, $badData), 'Matching model cannot authorize a different decoded payload.');
        $design = Package::presentation($block, $data)['m42_native_design'];
        foreach (['identity' => Package::OWNERS[1], 'locale' => 'en', 'uuid' => 'foreign', 'source_index' => 99,
            'body_sha256' => str_repeat('0', 64), 'component' => 'm70-custom-section'] as $field => $value) {
            $foreign = $design; $foreign[$field] = $value; self::assertFalse(Package::validDesign($foreign), $field);
        }
    }

    public function test_component_map_binds_exact_native_types_columns_levels_bodies_and_point_owners(): void
    {
        [, $patch, $master] = Package::load();
        $map = json_decode(File::get(base_path('docs/content/m70-intensifiers-noun-basics.components.v1.json')), true, flags: JSON_THROW_ON_ERROR)['component_map'];
        self::assertCount(33, $map); $offset = 0;
        foreach ($patch['targets'] as $i => $target) {
            foreach ($target['after']['page']['blocks'] as $slot => $source) {
                $entry = $map[$offset++]; $authored = $master['lessons'][$i]['blocks'][$slot];
                self::assertSame($target['identity'], $entry['identity']); self::assertSame($slot, $entry['source_index']);
                self::assertSame($authored['key'], $entry['key']); self::assertSame($source['type'], $entry['type']);
                self::assertSame($source['column'], $entry['column']); self::assertSame($source['level'] ?? null, $entry['level']);
                self::assertSame(M26DetailPackage::uuid($target['identity'], $source, $slot + 1), $entry['uuid']);
                self::assertSame(hash('sha256', $source['body']), $entry['body_sha256']);
                $details = array_values(array_filter($master['lessons'][$i]['details'], static fn ($d) => $d['owner_block_key'] === $authored['key']));
                self::assertSame(count($details), $entry['details']);
                if (array_key_exists('native_view', $entry)) { self::assertSame(TheoryPresentation::nativeView($source['type']), $entry['native_view']); }
                if (array_key_exists('semantic_component', $entry)) { self::assertSame($source['type'], $entry['semantic_component']); }
            }
        }
    }
}
