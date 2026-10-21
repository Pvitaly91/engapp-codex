<?php

namespace Tests\Feature;

use App\Http\Controllers\PageController;
use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M73GenericIrregularCollective as Package;
use App\Support\TheoryContentRenderer;
use App\Support\TheoryContentSource;
use App\Support\TheoryNativeContentRelease;
use App\Support\TheoryPresentation;
use DOMDocument;
use DOMXPath;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use ReflectionMethod;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Shared Blade/adapter checks on isolated models; not live browser acceptance. */
class M73NativeMetadataTest extends TestCase
{
    private function block(array $target, array $source, int $slot): TextBlock
    {
        $block = new TextBlock;
        $block->setRawAttributes(['id' => 7300000 + $target['page_binding']['id'] * 100 + $slot,
            'uuid' => M26DetailPackage::uuid($target['identity'], $source, $slot + 1),
            'seeder' => $target['identity'], 'locale' => 'uk', 'page_id' => $target['page_binding']['id'],
            'page_category_id' => $target['page_binding']['page_category_id'], 'sort_order' => $slot + 1,
            'type' => $source['type'], 'column' => $source['column'], 'heading' => $source['heading'] ?? null,
            'css_class' => $source['css_class'] ?? null, 'level' => $source['level'] ?? null, 'body' => $source['body']], true);
        $block->setRelation('tags', new Collection); $block->setRelation('page', null); return $block;
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

    public function test_continuing_columns_levels_uuid_and_footer_positions_are_not_normalized_to_new_practice_level(): void
    {
        [$before, $patch] = Package::load(); $release = new TheoryNativeContentRelease(Package::profile());
        $expectedLevels = [array_fill(0, 13, null), ['A1–A2', 'A1', 'A1–A2', 'A1–A2', 'A1', 'A1–A2', 'A1–A2'],
            ['B1', 'B1', 'B1', 'B1', 'B1', null]];
        $count = 0;
        foreach ($before['targets'] as $i => $target) {
            $old = $target['before']; self::assertFalse($release->subtitleWasAbsent($old));
            self::assertSame($old, $release->projectedBefore($old));
            foreach ($old['page']['blocks'] as $slot => $source) {
                ++$count; $after = $patch['targets'][$i]['after']['page']['blocks'][$slot];
                self::assertSame($expectedLevels[$i][$slot], $source['level'] ?? null);
                self::assertSame($source['level'] ?? null, $after['level'] ?? null);
                self::assertSame(array_key_exists('level', $source), array_key_exists('level', $after));
                self::assertSame(M26DetailPackage::uuid($target['identity'], $source, $slot + 1), M26DetailPackage::uuid($target['identity'], $after, $slot + 1));
                unset($source['body'], $after['body']); self::assertSame($source, $after);
            }
            $footerSlot = [12, 6, 5][$i];
            self::assertSame('navigation-chips', $patch['targets'][$i]['after']['page']['blocks'][$footerSlot]['type']);
            self::assertSame('footer', $patch['targets'][$i]['after']['page']['blocks'][$footerSlot]['column']);
        }
        self::assertSame(26, $count);
        foreach ([[1, 3], [1, 4], [1, 5], [2, 3], [2, 4]] as [$i, $slot]) {
            self::assertSame('right', $patch['targets'][$i]['after']['page']['blocks'][$slot]['column']);
        }
        self::assertSame('A2', $patch['targets'][1]['after']['page']['blocks'][7]['level']);
        self::assertSame('B1', $patch['targets'][2]['after']['page']['blocks'][6]['level']);
        self::assertNull($patch['targets'][0]['after']['page']['blocks'][11]['level'] ?? null);
    }

    public function test_existing_first_strong_title_extractor_keeps_generic_long_h1_and_raw_titles(): void
    {
        [$before, $patch] = Package::load();
        $controller = (new ReflectionClass(PageController::class))->newInstanceWithoutConstructor();
        $extract = new ReflectionMethod(PageController::class, 'extractLocalizedTitleFromSubtitle');
        $expected = ['Generic Reference: the rich, a tiger, Ø people', 'Irregular Plurals', 'Collective Nouns'];
        foreach ($patch['targets'] as $i => $target) {
            $old = $before['targets'][$i]['before']; $after = $target['after'];
            self::assertSame($old['page']['title'], $after['page']['title']); self::assertSame($old['slug'], $after['slug']);
            self::assertSame($old['page']['category'], $after['page']['category']);
            foreach ([$old['page']['subtitle_html'], $after['page']['subtitle_html']] as $subtitle) {
                self::assertSame($expected[$i], $extract->invoke($controller, $subtitle));
                [, $xp] = self::dom($subtitle); self::assertSame($expected[$i], $xp->query('//p/strong[1]')->item(0)?->textContent);
            }
            self::assertNotSame('', trim($after['page']['subtitle_text']));
            self::assertSame('http://gramlyze.loc/theory/imennyky-artykli-ta-kilkist/'.$target['slug'], $target['local_url']);
        }
        self::assertNotSame($before['targets'][0]['before']['page']['title'], $expected[0]);
    }

    public function test_exact_canonical_presentations_use_shared_views_without_queries_or_original_model_mutation(): void
    {
        app()->setLocale('uk'); [, $patch, $master] = Package::load();
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection()); DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog(); $count = 0;
        try {
            foreach ($patch['targets'] as $i => $target) { foreach ($target['after']['page']['blocks'] as $slot => $source) {
                ++$count; $block = $this->block($target, $source, $slot); $attributes = $block->getAttributes();
                $data = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR); $unchanged = $data;
                $presentation = Package::presentation($block, $data); self::assertNotNull($presentation);
                self::assertTrue(Package::validDesign($presentation['m42_native_design']));
                self::assertSame($source['type'], $presentation['m42_native_design']['component']);
                $marker = Package::profile()['marker'];
                self::assertSame($master['lessons'][$i]['blocks'][$slot]['data'] + [$marker => $data[$marker]], $presentation['data']);
                self::assertArrayNotHasKey('native_view', $presentation); self::assertArrayNotHasKey('stylesheet', $presentation);
                $resolved = TheoryContentSource::resolve($block, ['m42StyleContext' => true]);
                self::assertSame(TheoryPresentation::nativeView($source['type']), $resolved['view']);
                if (in_array($source['type'], ['hero', 'navigation-chips'], true)) { self::assertNull($resolved['presentation']); }
                else { self::assertSame($presentation, $resolved['presentation']); }
                self::assertNull(TheoryContentSource::resolve($block)['presentation']);
                self::assertSame($unchanged, $data); self::assertSame($attributes, $block->getAttributes()); self::assertSame([], $block->getDirty());
            }}
            self::assertSame(28, $count); self::assertSame([], DB::connection()->getQueryLog());
        } finally { DB::connection()->disableQueryLog(); }
    }

    public function test_foreign_owner_locale_identity_metadata_range_transition_and_stale_data_fall_back(): void
    {
        app()->setLocale('uk'); [, $patch] = Package::load();
        foreach ($patch['targets'] as $target) {
            $source = $target['after']['page']['blocks'][1]; $block = $this->block($target, $source, 1);
            $data = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR); self::assertNotNull(Package::presentation($block, $data));
            foreach (['seeder' => 'Foreign\\Owner', 'locale' => 'en', 'page_id' => 999, 'page_category_id' => 52,
                'uuid' => 'foreign', 'sort_order' => 1, 'type' => 'usage-panels', 'column' => 'right',
                'heading' => 'Later heading', 'css_class' => 'later-style', 'level' => 'A2', 'body' => $source['body']."\n"] as $field => $value) {
                $foreign = clone $block; $foreign->$field = $value; $attributes = $foreign->getAttributes(); $dirty = $foreign->getDirty();
                self::assertNull(Package::presentation($foreign, $data), $field);
                self::assertSame($attributes, $foreign->getAttributes()); self::assertSame($dirty, $foreign->getDirty());
            }
            foreach (['en', 'pl'] as $locale) { app()->setLocale($locale); self::assertNull(Package::presentation($block, $data)); }
            app()->setLocale('uk'); $changed = $data; $changed['unreviewed'] = 'Later text'; self::assertNull(Package::presentation($block, $changed));
            $design = Package::presentation($block, $data)['m42_native_design'];
            foreach (['identity' => 'Foreign', 'locale' => 'en', 'uuid' => 'foreign', 'source_index' => 99,
                'body_sha256' => str_repeat('0', 64), 'component' => 'm73-custom-section'] as $field => $value) {
                $bad = $design; $bad[$field] = $value; self::assertFalse(Package::validDesign($bad), $field);
            }
        }
    }

    public function test_real_shared_blade_displays_bilingual_examples_corrected_table_and_zero_disclosures(): void
    {
        app()->setLocale('uk'); [, $patch, $master] = Package::load();
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection()); DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog(); $rendered = 0;
        try {
            foreach ($patch['targets'] as $i => $target) { foreach ($target['after']['page']['blocks'] as $slot => $source) {
                if (in_array($source['type'], ['hero', 'navigation-chips', 'practice-set'], true)) { continue; }
                ++$rendered; $block = $this->block($target, $source, $slot); [$html, , $xp] = $this->rendered($block);
                $data = $master['lessons'][$i]['blocks'][$slot]['data'];
                self::assertSame(1, substr_count($html, '<!-- theory.content:node -->'));
                self::assertSame(1, $xp->query('//*[@data-theory-component="section" and @id="block-'.$block->id.'"]')->length);
                self::assertSame(0, $xp->query('//details')->length); self::assertSame(0, $xp->query('//*[@data-theory-render-fallback]')->length);
                self::assertStringNotContainsString('m73-section', $html); self::assertStringNotContainsString('m73-styles', $html);
                if ($source['type'] === 'usage-panels') {
                    self::assertSame(1, $xp->query('//*[@data-theory-reference="native"]')->length);
                    self::assertSame(count($data['sections']), $xp->query('//*[@data-theory-point-panel]')->length);
                    $ens = array_map(static fn ($n) => self::text($n->textContent), iterator_to_array($xp->query('//*[@data-theory-component="example"]//*[@lang="en"]')));
                    $uks = array_map(static fn ($n) => self::text($n->textContent), iterator_to_array($xp->query('//*[@data-theory-component="example"]//*[@lang="uk"]')));
                    foreach ($data['sections'] as $section) { foreach ($section['examples'] ?? [] as $example) {
                        self::assertContains(self::text($example['en']), $ens); self::assertContains(self::text($example['ua']), $uks);
                    }}
                }
                if ($source['type'] === 'mistakes-grid') {
                    $items = $xp->query('//*[@data-theory-component="mistake"]'); self::assertCount(count($data['items']), $items);
                    foreach ($data['items'] as $n => $item) { foreach (['wrong_en', 'wrong_ua', 'right_en', 'right_ua', 'note'] as $field) {
                        self::assertStringContainsString(self::text($item[$field]), self::text($items->item($n)->textContent));
                    }}
                }
                if ($i === 1 && $source['type'] === 'comparison-table') {
                    $text = self::text($html);
                    foreach ($data['rows'] as $row) { self::assertStringContainsString(self::text($row['en']), $text); self::assertStringContainsString(self::text($row['ua']), $text); }
                }
            }}
            self::assertSame(19, $rendered); self::assertSame([], DB::connection()->getQueryLog());
        } finally { DB::connection()->disableQueryLog(); }
    }

    public function test_practice_ssr_displays_all_supported_groups_and_visible_uk_conditions_not_only_serialized_config(): void
    {
        app()->setLocale('uk'); [, $patch, $master] = Package::load();
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection()); DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        try {
            foreach ($patch['targets'] as $i => $target) {
                $slot = Package::PRACTICE_SOURCE_INDICES[$i]; $source = $target['after']['page']['blocks'][$slot];
                [$html, , $xp] = $this->rendered($this->block($target, $source, $slot)); $data = $master['lessons'][$i]['blocks'][$slot]['data'];
                self::assertSame($i === 0 ? 4 : 3, $xp->query('//*[contains(concat(" ",normalize-space(@class)," ")," theory-exercise ")]')->length);
                self::assertSame($i === 0 ? 5 : 2, $xp->query('//*[@data-word-suggestion-input and starts-with(@data-word-suggestion-input,"inputs-")]')->length);
                self::assertSame($i === 0 ? 2 : 0, $xp->query('//*[@data-word-suggestion-input and starts-with(@data-word-suggestion-input,"rephrase-")]')->length);
                $labels = array_map(static fn ($node) => self::text($node->textContent), iterator_to_array($xp->query('//label')));
                foreach (['selects', 'choices'] as $group) { foreach ($data[$group] as $task) { self::assertContains(self::text($task['label']), $labels); } }
                $visible = '';
                foreach ($xp->query('//body//text()[not(ancestor::script) and not(ancestor::style) and not(ancestor::template)]') as $n) { $visible .= ' '.$n->textContent; }
                $visible = self::text($visible);
                foreach ($data['choices'] as $task) {
                    $prompt = preg_replace('~</(?:p|div|li|h[1-6]|tr)>|<br\s*/?>~i', ' ', $task['prompt']);
                    self::assertStringContainsString(self::text($prompt), $visible);
                }
                foreach ($data['inputs'] as $task) {
                    self::assertArrayNotHasKey('prompt', $task); self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $task['after']);
                    self::assertStringContainsString(self::text($task['after']), $visible);
                }
                self::assertStringContainsString(self::text($data['input_intro']), $visible);
                foreach (array_slice($data['rephrase'] ?? [], 1) as $task) { self::assertStringContainsString(self::text($task['original']), $visible); }
                self::assertStringNotContainsString('m73-practice-ui', $html);
            }
            self::assertSame([], DB::connection()->getQueryLog());
        } finally { DB::connection()->disableQueryLog(); }
    }
}
