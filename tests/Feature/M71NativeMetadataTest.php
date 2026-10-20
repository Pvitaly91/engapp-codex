<?php

namespace Tests\Feature;

use App\Http\Controllers\PageController;
use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M71ArticlesQuantifiers as Package;
use App\Support\TheoryContentRenderer;
use App\Support\TheoryContentSource;
use App\Support\TheoryNativeContentRelease;
use App\Support\TheoryPresentation;
use DOMDocument;
use DOMXPath;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionMethod;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Actual shared native policy and Blade rendering on isolated in-memory models. */
class M71NativeMetadataTest extends TestCase
{
    private function block(array $target, array $source, int $slot): TextBlock
    {
        $block = new TextBlock;
        $block->setRawAttributes(['id' => 7100000 + $target['page_binding']['id'] * 100 + $slot,
            'uuid' => M26DetailPackage::uuid($target['identity'], $source, $slot + 1),
            'seeder' => $target['identity'], 'locale' => 'uk', 'page_id' => $target['page_binding']['id'],
            'page_category_id' => $target['page_binding']['page_category_id'], 'sort_order' => $slot + 1,
            'type' => $source['type'], 'column' => $source['column'], 'heading' => $source['heading'] ?? null,
            'css_class' => $source['css_class'] ?? null, 'level' => $source['level'] ?? null, 'body' => $source['body']], true);
        $block->setRelation('tags', new Collection); $block->setRelation('page', null);
        return $block;
    }

    private static function text(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private static function dom(string $html): array
    {
        $dom = new DOMDocument('1.0', 'UTF-8'); $previous = libxml_use_internal_errors(true);
        try { self::assertTrue($dom->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET)); }
        finally { libxml_clear_errors(); libxml_use_internal_errors($previous); }
        return [$dom, new DOMXPath($dom)];
    }

    private function rendered(TextBlock $block): array
    {
        $resolved = TheoryContentSource::resolve($block, ['m42StyleContext' => true]);
        self::assertNotNull($resolved['presentation']);
        $html = TheoryContentRenderer::render($resolved['view'], ['theoryCanonical' => true,
            'block' => $block, 'data' => $resolved['data'], 'm42Design' => $resolved['design'],
            'practiceQuestions' => collect(), 'lessonLinks' => []]);
        return [$html, ...self::dom($html)];
    }

    public function test_all_thirty_nine_continuing_slots_have_no_metadata_transition_or_new_subtitle_identity(): void
    {
        [$before, $patch, $master] = Package::load(); $release = new TheoryNativeContentRelease(Package::profile());
        $count = 0;
        foreach ($before['targets'] as $i => $target) {
            $old = $target['before'];
            self::assertFalse($release->subtitleWasAbsent($old)); self::assertSame($old, $release->projectedBefore($old));
            foreach ($old['page']['blocks'] as $slot => $source) {
                ++$count; $after = $patch['targets'][$i]['after']['page']['blocks'][$slot];
                self::assertSame($source['type'], $after['type']); self::assertSame($source['column'], $after['column']);
                self::assertNull($source['level'] ?? null); self::assertNull($after['level'] ?? null);
                self::assertSame(array_key_exists('level', $source), array_key_exists('level', $after));
                self::assertNull($master['lessons'][$i]['blocks'][$slot]['level']);
                self::assertSame(M26DetailPackage::uuid($target['identity'], $source, $slot + 1), M26DetailPackage::uuid($target['identity'], $after, $slot + 1));
                unset($source['body'], $after['body']); self::assertSame($source, $after);
            }
            $practice = $patch['targets'][$i]['after']['page']['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]];
            self::assertSame(Package::PRACTICE_UUIDS[$i], M26DetailPackage::uuid($target['identity'], $practice, Package::PRACTICE_SOURCE_INDICES[$i] + 1));
        }
        self::assertSame(39, $count);
    }

    public function test_existing_title_extractor_keeps_first_strong_display_titles_and_protected_raw_page_titles(): void
    {
        [$before, $patch, $master] = Package::load();
        $controller = (new ReflectionClass(PageController::class))->newInstanceWithoutConstructor();
        $extract = new ReflectionMethod(PageController::class, 'extractLocalizedTitleFromSubtitle');
        $rawTitles = ['Articles — A / An / The', 'Zero Article — Нульовий артикль', 'Quantifiers — much, many, few, little'];
        self::assertSame(['Articles: a / an / the', 'Zero article', 'Quantifiers: much, many, few, little'], Package::RENDERED_TITLES);
        foreach ($patch['targets'] as $i => $target) {
            $old = $before['targets'][$i]['before']; $after = $target['after'];
            self::assertSame($rawTitles[$i], $old['page']['title']); self::assertSame($rawTitles[$i], $after['page']['title']);
            self::assertNotSame($rawTitles[$i], Package::RENDERED_TITLES[$i]);
            foreach ([$old['page']['subtitle_html'], $after['page']['subtitle_html']] as $subtitle) {
                self::assertSame(Package::RENDERED_TITLES[$i], $extract->invoke($controller, $subtitle));
                [, $xp] = self::dom($subtitle);
                self::assertSame(Package::RENDERED_TITLES[$i], $xp->query('//p/strong[1]')->item(0)?->textContent);
            }
            self::assertSame($master['lessons'][$i]['subtitle_html'], $after['page']['subtitle_html']);
            self::assertNotSame('', trim($after['page']['subtitle_text']));
            self::assertSame($old['slug'], $after['slug']); self::assertSame($old['page']['category'], $after['page']['category']);
            self::assertSame('http://gramlyze.loc/theory/imennyky-artykli-ta-kilkist/'.Package::SLUGS[$i], $target['local_url']);
            self::assertSame(['imennyky-artykli-ta-kilkist'], $target['ancestry']);
        }
    }

    public function test_canonical_resolver_owns_thirty_nine_exact_presentations_without_queries_or_mutation(): void
    {
        app()->setLocale('uk'); [, $patch, $master] = Package::load();
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog(); $count = 0;
        try {
            foreach ($patch['targets'] as $i => $target) {
                foreach ($target['after']['page']['blocks'] as $slot => $source) {
                    ++$count; $block = $this->block($target, $source, $slot); $attributes = $block->getAttributes();
                    $data = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR); $unchanged = $data;
                    $presentation = Package::presentation($block, $data);
                    self::assertNotNull($presentation); self::assertTrue(Package::validDesign($presentation['m42_native_design']));
                    self::assertSame($source['type'], $presentation['m42_native_design']['component']);
                    self::assertArrayNotHasKey('native_view', $presentation); self::assertArrayNotHasKey('stylesheet', $presentation);
                    self::assertSame($master['lessons'][$i]['blocks'][$slot]['data'] + ['m71_native_v1' => $data['m71_native_v1']], $presentation['data']);
                    $resolved = TheoryContentSource::resolve($block, ['m42StyleContext' => true]);
                    self::assertSame(TheoryPresentation::nativeView($source['type']), $resolved['view']);
                    if (in_array($source['type'], ['hero', 'navigation-chips'], true)) { self::assertNull($resolved['presentation']); }
                    else { self::assertSame($presentation, $resolved['presentation']); }
                    self::assertNull(TheoryContentSource::resolve($block)['presentation'], 'Caller-owned theory context is required.');
                    if ($source['type'] === 'usage-panels') {
                        $panels = Package::pointPanels($presentation['m42_native_design']); self::assertNotNull($panels);
                        self::assertCount(count($master['lessons'][$i]['blocks'][$slot]['data']['sections']), $panels['points_by_index']);
                    } else { self::assertNull(Package::pointPanels($presentation['m42_native_design'])); }
                    self::assertSame($unchanged, $data); self::assertSame($attributes, $block->getAttributes()); self::assertSame([], $block->getDirty());
                }
            }
            self::assertSame(39, $count); self::assertSame([], DB::connection()->getQueryLog());
        } finally { DB::connection()->disableQueryLog(); app()->setLocale('uk'); }
    }

    public function test_foreign_owner_locale_page_category_level_stale_data_and_design_fall_back_without_mutation(): void
    {
        app()->setLocale('uk'); [, $patch] = Package::load();
        foreach ($patch['targets'] as $target) {
            $source = $target['after']['page']['blocks'][1]; $block = $this->block($target, $source, 1);
            $data = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR);
            self::assertNotNull(Package::presentation($block, $data));
            foreach (['seeder' => 'Database\\Seeders\\Page_V3\\ForeignOwner', 'locale' => 'en', 'page_id' => 999,
                'page_category_id' => 52, 'uuid' => 'foreign', 'sort_order' => 1, 'type' => 'usage-panels',
                'column' => 'right', 'heading' => 'Manual heading', 'css_class' => 'manual-style',
                'level' => 'A2', 'body' => $source['body']."\n"] as $field => $value) {
                $foreign = clone $block; $foreign->$field = $value; $attributes = $foreign->getAttributes(); $dirty = $foreign->getDirty();
                self::assertNull(Package::presentation($foreign, $data), $field);
                self::assertSame($attributes, $foreign->getAttributes()); self::assertSame($dirty, $foreign->getDirty());
            }
            foreach (['en', 'pl'] as $locale) { app()->setLocale($locale); self::assertNull(Package::presentation($block, $data)); }
            app()->setLocale('uk'); $badData = $data; $badData['unreviewed'] = 'Ручна правка.';
            self::assertNull(Package::presentation($block, $badData));
            $design = Package::presentation($block, $data)['m42_native_design'];
            foreach (['identity' => 'foreign', 'locale' => 'en', 'uuid' => 'foreign', 'source_index' => 99,
                'body_sha256' => str_repeat('0', 64), 'component' => 'm71-custom-section'] as $field => $value) {
                $foreign = $design; $foreign[$field] = $value; self::assertFalse(Package::validDesign($foreign), $field);
            }
        }
    }

    public function test_component_map_binds_all_continuing_native_slots_and_no_metadata_or_detail_transition(): void
    {
        [, $patch, $master] = Package::load();
        $map = json_decode(File::get(base_path('docs/content/m71-articles-quantifiers.components.v1.json')), true, flags: JSON_THROW_ON_ERROR)['component_map'];
        self::assertCount(39, $map); $offset = 0;
        foreach ($patch['targets'] as $i => $target) {
            foreach ($target['after']['page']['blocks'] as $slot => $source) {
                $entry = $map[$offset++]; $authored = $master['lessons'][$i]['blocks'][$slot];
                self::assertSame($target['identity'], $entry['identity']); self::assertSame($slot, $entry['source_index']);
                self::assertSame($authored['key'], $entry['key']); self::assertSame($source['type'], $entry['type']);
                self::assertSame($source['column'], $entry['column']); self::assertNull($entry['level']);
                self::assertSame(M26DetailPackage::uuid($target['identity'], $source, $slot + 1), $entry['uuid']);
                self::assertSame(hash('sha256', $source['body']), $entry['body_sha256']);
                self::assertTrue($entry['continuing']); self::assertFalse($entry['metadata_transition']); self::assertSame(0, $entry['details']);
                if (array_key_exists('native_view', $entry)) { self::assertSame(TheoryPresentation::nativeView($source['type']), $entry['native_view']); }
                if (array_key_exists('semantic_component', $entry)) { self::assertSame($source['type'], $entry['semantic_component']); }
            }
        }
    }

    public function test_actual_shared_blade_ssr_contains_bilingual_examples_corrections_and_native_point_groups(): void
    {
        app()->setLocale('uk'); [, $patch, $master] = Package::load();
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog(); $rendered = 0;
        try {
            foreach ($patch['targets'] as $i => $target) {
                foreach ($target['after']['page']['blocks'] as $slot => $source) {
                    if (in_array($source['type'], ['hero', 'navigation-chips', 'practice-set'], true)) { continue; }
                    ++$rendered; $block = $this->block($target, $source, $slot); $attributes = $block->getAttributes();
                    [$html, , $xp] = $this->rendered($block); $data = $master['lessons'][$i]['blocks'][$slot]['data'];
                    self::assertSame(1, substr_count($html, '<!-- theory.content:node -->'));
                    self::assertSame(1, $xp->query('//*[@data-theory-component="section" and @id="block-'.$block->id.'"]')->length);
                    self::assertSame(0, $xp->query('//*[@data-theory-render-fallback]')->length);
                    self::assertSame(0, $xp->query('//details')->length, 'All reviewed basic content remains visible.');
                    self::assertStringNotContainsString('m71-section', $html); self::assertStringNotContainsString('m71-styles', $html);
                    if ($source['type'] === 'usage-panels') {
                        self::assertSame(1, $xp->query('//*[@data-theory-reference="native"]')->length);
                        self::assertSame(count($data['sections']), $xp->query('//*[@data-theory-point-panel]')->length);
                        $examplesEn = array_map(static fn ($node) => self::text($node->textContent), iterator_to_array($xp->query('//*[@data-theory-component="example"]//*[@lang="en"]')));
                        $examplesUk = array_map(static fn ($node) => self::text($node->textContent), iterator_to_array($xp->query('//*[@data-theory-component="example"]//*[@lang="uk"]')));
                        foreach ($data['sections'] as $point) {
                            foreach ($point['examples'] ?? [] as $example) {
                                self::assertContains(self::text($example['en']), $examplesEn);
                                self::assertContains(self::text($example['ua']), $examplesUk);
                            }
                        }
                    }
                    if ($source['type'] === 'mistakes-grid') {
                        $items = $xp->query('//*[@data-theory-component="mistake"]'); self::assertSame(count($data['items']), $items->length);
                        foreach ($data['items'] as $index => $item) {
                            $text = self::text($items->item($index)->textContent);
                            foreach (['wrong_en', 'wrong_ua', 'right_en', 'right_ua', 'note'] as $field) { self::assertStringContainsString(self::text($item[$field]), $text, $field); }
                        }
                    }
                    self::assertSame($attributes, $block->getAttributes()); self::assertSame([], $block->getDirty());
                }
            }
            self::assertSame(30, $rendered); self::assertSame([], DB::connection()->getQueryLog());
        } finally { DB::connection()->disableQueryLog(); }
    }

    public function test_practice_ssr_really_displays_all_four_groups_and_supported_stimuli_not_just_config_json(): void
    {
        app()->setLocale('uk'); [, $patch, $master] = Package::load();
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        try {
            foreach ($patch['targets'] as $i => $target) {
                $slot = Package::PRACTICE_SOURCE_INDICES[$i]; $source = $target['after']['page']['blocks'][$slot];
                $block = $this->block($target, $source, $slot); $data = $master['lessons'][$i]['blocks'][$slot]['data'];
                [$html, , $xp] = $this->rendered($block);
                self::assertSame(4, $xp->query('//*[contains(concat(" ",normalize-space(@class)," ")," theory-exercise ")]')->length);
                self::assertSame(5, $xp->query('//*[@data-word-suggestion-input and starts-with(@data-word-suggestion-input,"inputs-")]')->length);
                self::assertSame(2, $xp->query('//*[@data-word-suggestion-input and starts-with(@data-word-suggestion-input,"rephrase-")]')->length);
                $labels = array_map(static fn ($node) => self::text($node->textContent), iterator_to_array($xp->query('//label')));
                foreach (['selects', 'choices'] as $group) {
                    foreach ($data[$group] as $task) { self::assertContains(self::text($task['label']), $labels); }
                }
                $prompts = array_map(static fn ($node) => self::text($node->textContent), iterator_to_array($xp->query('//*[@data-practice-input-prompt]')));
                foreach ($data['inputs'] as $task) { if (!empty($task['prompt'])) { self::assertContains(self::text($task['prompt']), $prompts); } }
                $text = '';
                foreach ($xp->query('//body//text()[not(ancestor::script) and not(ancestor::style) and not(ancestor::template)]') as $node) { $text .= ' '.$node->textContent; }
                $text = self::text($text);
                foreach ($data['choices'] as $task) { self::assertStringContainsString(self::text($task['prompt']), $text); }
                foreach ([3, 4] as $index) { self::assertStringContainsString(self::text($data['inputs'][$index]['after']), $text); }
                foreach (array_slice($data['rephrase'], 1) as $task) { self::assertStringContainsString(self::text($task['original']), $text); }
                self::assertStringNotContainsString('m71-practice-ui', $html);
            }
            self::assertSame([], DB::connection()->getQueryLog());
        } finally { DB::connection()->disableQueryLog(); }
    }
}
