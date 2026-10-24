<?php

namespace Tests\Feature;

use App\Http\Controllers\PageController;
use App\Models\Page;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\TextBlock;
use App\Support\M26DetailPackage as Digest;
use App\Support\M85BuilderSelectionPresentation as Selection;
use App\Support\M85VerbToBe as Package;
use App\Support\SiteMode;
use App\Support\TheoryContentRenderer;
use App\Support\TheoryContentSource;
use App\Support\TheoryPresentation;
use App\Support\TheorySentenceBuilderAcceptance;
use DOMDocument;
use DOMXPath;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use ReflectionMethod;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Canonical SSR on unsaved exact-bound models. No working DB/HTTP or live acceptance claim. */
class M85VerbToBeRuntimeTest extends TestCase
{
    private function release(): array
    {
        $r = Package::load(dirname(__DIR__, 2)); Package::validate($r[0], $r[1], $r[2], $r[3]);
        foreach (Package::profile()['artifacts'] as $path => $sha) {
            self::assertSame($sha, hash_file('sha256', base_path($path)), 'Public runtime and test teaching sources must be the same frozen package.');
        }
        return $r;
    }

    private function block(array $target, array $source, int $slot): TextBlock
    {
        $b = new TextBlock;
        $b->setRawAttributes(['id' => 8500000 + $target['page_binding']['id'] * 100 + $slot,
            'uuid' => Digest::uuid($target['identity'], $source, $slot + 1), 'seeder' => $target['identity'], 'locale' => 'uk',
            'page_id' => $target['page_binding']['id'], 'page_category_id' => $target['page_binding']['page_category_id'],
            'sort_order' => $slot + 1, 'type' => $source['type'], 'column' => $source['column'], 'heading' => $source['heading'] ?? null,
            'css_class' => $source['css_class'] ?? null, 'level' => $source['level'] ?? null, 'body' => $source['body']], true);
        $b->setRelation('tags', new Collection); $b->setRelation('page', null);
        return $b;
    }

    private function subtitle(array $target, array $old): TextBlock
    {
        return $this->block($target, ['type' => 'subtitle', 'column' => 'header', 'heading' => null, 'css_class' => null,
            'level' => $old['page']['subtitle_level'] ?? null, 'uuid_key' => $old['page']['subtitle_uuid_key'] ?? 'subtitle',
            'body' => $target['after']['page']['subtitle_html']], -1);
    }

    private function noQueries(callable $callback): void
    {
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        self::assertSame('testing', app()->environment()); self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName()); self::assertSame('array', config('cache.default'));
        self::assertSame('array', config('session.driver')); self::assertNotSame(realpath(base_path()), realpath(app()->environmentPath()));
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        try { $callback(); self::assertSame([], DB::connection()->getQueryLog(), 'Unsaved canonical rendering must not select or write SQL.'); }
        finally { DB::connection()->disableQueryLog(); app()->setLocale('uk'); }
    }

    private static function text(string $value): string
    {
        $value = preg_replace('~</(?:p|div|li|h[1-6]|section|article)>|<br\s*/?>~iu', ' ', $value);
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private function dom(string $html): DOMXPath
    {
        $d = new DOMDocument('1.0', 'UTF-8'); $old = libxml_use_internal_errors(true);
        try { self::assertTrue($d->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET)); }
        finally { libxml_clear_errors(); libxml_use_internal_errors($old); }
        return new DOMXPath($d);
    }

    private function visible(DOMXPath $xp): string
    {
        $text = '';
        foreach ($xp->query('//body//text()[not(ancestor::script) and not(ancestor::style) and not(ancestor::template)]') as $n) { $text .= ' '.$n->textContent; }
        return self::text($text);
    }

    private function rendered(TextBlock $b, ?Collection $questions = null): array
    {
        $r = TheoryContentSource::resolve($b, ['m42StyleContext' => true]);
        self::assertNotNull($r['presentation']); self::assertNotNull($r['view']);
        $html = TheoryContentRenderer::render($r['view'], ['theoryCanonical' => true, 'block' => $b,
            'data' => $r['data'], 'm42Design' => $r['design'], 'pointSections' => $r['presentation']['points'],
            'practiceQuestions' => $questions ?? new Collection, 'lessonLinks' => []]);
        return [$html, $this->dom($html)];
    }

    public function test_51_exact_models_keep_canonical_caller_boundary_null_levels_and_no_model_mutation(): void
    {
        app()->setLocale('uk'); [, $patch, $master] = $this->release();
        $this->noQueries(function () use ($patch, $master): void {
            $seen = 0;
            foreach ($patch['targets'] as $i => $target) {
                foreach ($target['after']['page']['blocks'] as $slot => $source) {
                    ++$seen; $b = $this->block($target, $source, $slot); $attrs = $b->getAttributes();
                    $data = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR); $untouched = $data;
                    $p = Package::presentation($b, $data); self::assertNotNull($p); $design = $p['m42_native_design'];
                    self::assertTrue(Package::validDesign($design)); self::assertSame('m85-native-v1', $design['package']);
                    self::assertSame($source['type'], $design['component']); self::assertSame([], $p['points']);
                    self::assertSame($master['lessons'][$i]['blocks'][$slot]['data'], array_diff_key($p['data'], ['m85_native_v1' => true]));
                    $r = TheoryContentSource::resolve($b, ['m42StyleContext' => true]);
                    if (TheoryPresentation::nativeView($source['type']) === null) {
                        self::assertContains($source['type'], ['hero', 'navigation-chips']);
                        self::assertNull($r['view']); self::assertNull($r['design']); self::assertSame($data, $r['data']);
                    } else {
                        self::assertSame(TheoryPresentation::nativeView($source['type']), $r['view']);
                        self::assertSame($design, $r['design']); self::assertSame($p['data'], $r['data']);
                    }
                    foreach ([[], ['m42StyleContext' => false]] as $caller) {
                        $foreign = TheoryContentSource::resolve($b, $caller);
                        self::assertNull($foreign['design']); self::assertSame($data, $foreign['stored']); self::assertSame($data, $foreign['data']);
                    }
                    self::assertNull($b->level); self::assertSame($untouched, $data);
                    self::assertSame($attrs, $b->getAttributes()); self::assertSame([], $b->getDirty()); self::assertFalse($b->exists);
                }
            }
            self::assertSame(51, $seen);
        });
    }

    public function test_missing_foreign_or_stale_metadata_uses_full_stored_fallback_without_repairing_it(): void
    {
        app()->setLocale('uk'); [, $patch] = $this->release();
        $this->noQueries(function () use ($patch): void {
            foreach ($patch['targets'] as $i => $target) {
                $slot = Package::PRACTICE_SOURCE_INDICES[$i]; $source = $target['after']['page']['blocks'][$slot];
                $b = $this->block($target, $source, $slot); $data = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR);
                $p = Package::presentation($b, $data); self::assertNotNull($p);
                foreach (['seeder' => 'Foreign\\Owner', 'locale' => 'en', 'page_id' => 999, 'page_category_id' => 999,
                    'uuid' => 'foreign', 'sort_order' => 99, 'type' => 'box', 'column' => 'right', 'heading' => 'Later heading',
                    'css_class' => 'custom-style', 'level' => 'A2', 'body' => $source['body']."\n"] as $field => $value) {
                    $foreign = clone $b; $foreign->$field = $value; $attrs = $foreign->getAttributes(); $dirty = $foreign->getDirty();
                    self::assertNull(Package::presentation($foreign, $data), $field);
                    $fallback = TheoryContentSource::resolve($foreign, ['m42StyleContext' => true]);
                    self::assertNull($fallback['design'], $field); self::assertSame($data, $fallback['stored']); self::assertSame($data, $fallback['data']);
                    self::assertSame($attrs, $foreign->getAttributes()); self::assertSame($dirty, $foreign->getDirty());
                }
                $missing = clone $b; $missing->offsetUnset('seeder'); self::assertNull(Package::presentation($missing, $data));
                $range = clone $b; $range->level = Package::DISPLAY_LEVELS[$i]; self::assertNull(Package::presentation($range, $data));
                $unmarked = $data; unset($unmarked['m85_native_v1']); self::assertNull(Package::presentation($b, $unmarked));
                $extra = $data; $extra['secondary_body'] = 'Unreviewed competing body'; self::assertNull(Package::presentation($b, $extra));
                foreach (['en', 'pl'] as $locale) { app()->setLocale($locale); self::assertNull(Package::presentation($b, $data)); }
                app()->setLocale('uk');
                foreach (['package' => 'foreign', 'identity' => 'Foreign', 'locale' => 'en', 'uuid' => 'foreign',
                    'source_index' => 99, 'body_sha256' => str_repeat('0', 64), 'component' => 'm85-section'] as $field => $value) {
                    $bad = $p['m42_native_design']; $bad[$field] = $value; self::assertFalse(Package::validDesign($bad), $field); self::assertNull(Package::pointPanels($bad));
                }
            }
            self::assertFalse(Package::validDesign(null));
        });
    }

    public function test_32_content_sections_use_native_examples_tables_forms_mistakes_and_visible_basic(): void
    {
        app()->setLocale('uk'); [, $patch, $master] = $this->release();
        $this->noQueries(function () use ($patch, $master): void {
            $count = 0;
            foreach ($patch['targets'] as $i => $target) {
                foreach ($target['after']['page']['blocks'] as $slot => $source) {
                    if (in_array($source['type'], ['hero', 'navigation-chips', 'practice-set'], true)) { continue; }
                    ++$count; $b = $this->block($target, $source, $slot); [$html, $xp] = $this->rendered($b);
                    $data = $master['lessons'][$i]['blocks'][$slot]['data']; $visible = $this->visible($xp);
                    self::assertSame(1, substr_count($html, '<!-- theory.content:node -->'));
                    self::assertSame(1, $xp->query('//*[@data-theory-component="section" and @id="block-'.$b->id.'"]')->length);
                    self::assertSame(0, $xp->query('//details')->length); self::assertSame(0, $xp->query('//*[@data-theory-render-fallback]')->length);
                    self::assertStringNotContainsString('m85-section', $html); self::assertStringNotContainsString('m85-styles', $html);
                    self::assertDoesNotMatchRegularExpression('/\{a\d+\}|&lt;\/?(?:p|span|strong)\b|<\/?(?:p|span|strong)\b/u', $visible);
                    if ($source['type'] === 'usage-panels') {
                        self::assertSame(count($data['sections']), $xp->query('//*[@data-theory-point-panel]')->length);
                        foreach ($data['sections'] as $section) {
                            foreach ($section['examples'] ?? [] as $example) {
                                foreach (['en', 'ua'] as $field) { self::assertStringContainsString(self::text($example[$field]), $visible); }
                            }
                        }
                    } elseif ($source['type'] === 'comparison-table') {
                        self::assertSame(count($data['rows']), $xp->query('//*[@data-theory-component="table"]//tbody/tr')->length);
                        foreach ($data['rows'] as $row) { foreach (['en', 'ua'] as $field) { if (isset($row[$field])) { self::assertStringContainsString(self::text($row[$field]), $visible); } } }
                    } elseif ($source['type'] === 'mistakes-grid') {
                        $items = $xp->query('//*[@data-theory-component="mistake"]'); self::assertCount(count($data['items']), $items);
                        foreach ($data['items'] as $n => $item) {
                            foreach (['wrong_en', 'wrong_ua', 'right_en', 'right_ua', 'note'] as $field) {
                                if (isset($item[$field])) { self::assertStringContainsString(self::text($item[$field]), self::text($items->item($n)->textContent)); }
                            }
                        }
                    } elseif ($source['type'] === 'forms-grid') {
                        self::assertSame(count($data['items']), $xp->query('//*[@data-theory-component="form"]')->length);
                        foreach ($data['items'] as $item) {
                            self::assertStringContainsString(self::text($item['title']), $visible);
                            if (isset($item['subtitle'])) { self::assertStringContainsString(self::text($item['subtitle']), $visible); }
                        }
                    }
                }
            }
            self::assertSame(32, $count);
        });
    }

    /** Artificial IDs/rows exercise native SSR only; exact actual bank selection is tested separately. */
    private function syntheticQuestions(int $owner): Collection
    {
        $audit=Selection::loadAudit(dirname(__DIR__,2));
        $samples=array_map(static fn($q)=>[
            'question'=>$q['visible_condition_uk'],'correct'=>$q['correct_tokens'],
            'bank'=>$q['token_bank_with_multiplicity'],
        ],$audit['targets'][$owner]['first_five']);
        self::assertCount(5,$samples);
        return new Collection(array_map(static function (array $sample, int $i) use ($owner): Question {
            $q = new Question; $q->setRawAttributes(['id' => 85900000 + $owner * 10 + $i, 'uuid' => 'm85-offline-widget-'.$owner.'-'.$i,
                'seeder' => 'Database\\Seeders\\V3\\Polyglot\\M85IsolatedTeachingProbe', 'type' => '4', 'level' => Package::FOCUS_LEVELS[$owner],
                'question' => $sample['question']], true);
            $answers = [];
            foreach ($sample['correct'] as $n => $token) {
                $option = new QuestionOption; $option->setRawAttributes(['id' => 85910000 + $owner * 1000 + $i * 50 + $n, 'option' => $token], true);
                $a = new QuestionAnswer; $a->setRawAttributes(['id' => 85920000 + $owner * 1000 + $i * 50 + $n,
                    'question_id' => $q->id, 'marker' => 'a'.($n + 1), 'option_id' => $option->id], true);
                $a->setRelation('option', $option); $answers[] = $a;
            }
            $options = [];
            foreach ($sample['bank'] as $n => $token) {
                $o = new QuestionOption; $o->setRawAttributes(['id' => 85930000 + $owner * 1000 + $i * 100 + $n, 'option' => $token], true); $options[] = $o;
            }
            $q->setRelation('answers', new Collection($answers)); $q->setRelation('options', new Collection($options));
            foreach (['tags', 'hints', 'verbHints'] as $relation) { $q->setRelation($relation, new Collection); }
            return $q;
        }, $samples, array_keys($samples)));
    }

    public function test_six_practice_ssrs_show_36_own_tasks_12_full_inputs_and_six_separate_widgets(): void
    {
        app()->setLocale('uk'); [, $patch, $master] = $this->release();
        $this->noQueries(function () use ($patch, $master): void {
            $own = $widgets = $demos = $inputs = 0;
            foreach ($patch['targets'] as $i => $target) {
                $slot = Package::PRACTICE_SOURCE_INDICES[$i]; $b = $this->block($target, $target['after']['page']['blocks'][$slot], $slot);
                $data = $master['lessons'][$i]['blocks'][$slot]['data']; $questions = $this->syntheticQuestions($i); $untouched = $questions->toArray();
                [$html, $xp] = $this->rendered($b, $questions); $visible = $this->visible($xp);
                self::assertSame(3, $xp->query('//*[contains(concat(" ", normalize-space(@class), " "), " theory-exercise ")]')->length);
                self::assertSame(2, $xp->query('//*[@data-word-suggestion-input and starts-with(@data-word-suggestion-input,"inputs-")]')->length);
                self::assertSame(0, $xp->query('//*[@data-word-suggestion-input and starts-with(@data-word-suggestion-input,"rephrase-")]')->length);
                $labels = array_map(static fn ($n) => self::text($n->ownerDocument->saveHTML($n)), iterator_to_array($xp->query('//label')));
                foreach (['selects', 'choices'] as $group) { foreach ($data[$group] as $task) { self::assertContains(self::text($task['label']), $labels); ++$own; } }
                foreach ($data['inputs'] as $task) { self::assertStringContainsString(self::text($task['after']), $visible); ++$own; ++$inputs; }
                foreach ($data['rephrase'] ?? [] as $task) {
                    if (isset($task['answer'])) { self::assertStringContainsString(self::text($task['original']), $visible); ++$own; }
                    else { self::assertStringContainsString(self::text($task['example_original']), $visible); self::assertStringContainsString(self::text($task['example_target']), $visible); ++$demos; }
                }
                foreach ($xp->query('//*[@data-word-suggestion-input and starts-with(@data-word-suggestion-input,"inputs-")]') as $field) { self::assertSame('off', $field->getAttribute('autocomplete')); }
                self::assertSame(1, $xp->query('//*[@data-sentence-builder]')->length); ++$widgets;
                self::assertSame(1, $xp->query('//*[@id="block-'.$b->id.'"]//*[@data-sentence-builder]')->length);
                self::assertStringContainsString('Вправа 4. Побудуй речення', $visible);
                $payload = TheorySentenceBuilderAcceptance::payload($html, $b->uuid); self::assertIsArray($payload); self::assertCount(5, $payload);
                self::assertSame($questions->pluck('id')->all(), array_column($payload, 'id'));
                foreach ($payload as $n => $q) { self::assertSame('4', $q['type']); self::assertSame($questions[$n]->question, $q['question']); self::assertNotSame([], $q['options']); }
                self::assertSame($untouched, $questions->toArray()); self::assertFalse($b->exists);
                self::assertDoesNotMatchRegularExpression('/\{a\d+\}|&lt;\/?(?:p|span|strong)\b/u', $visible);
                self::assertSame(0, $xp->query('//*[@data-theory-render-fallback]')->length);
            }
            self::assertSame([36, 0, 12, 6], [$own, $demos, $inputs, $widgets]);
        });
    }

    public function test_course_and_standalone_clones_restore_old_sources_and_hide_all_six_new_practices(): void
    {
        app()->setLocale('uk'); [$before, $patch] = $this->release();
        $this->noQueries(function () use ($before, $patch): void {
            $hidden = 0;
            foreach ($patch['targets'] as $i => $target) {
                $old = $before['targets'][$i]['before']; $blocks = [$this->subtitle($target, $old)];
                foreach ($target['after']['page']['blocks'] as $slot => $source) {
                    $b = $this->block($target, $source, $slot); $blocks[] = $b; $attrs = $b->getAttributes();
                    foreach (['theory', 'course', 'unknown'] as $caller) { self::assertSame($b, Package::preserveExcludedTextBlock($b, $caller)); }
                    $restored = Package::preserveExcludedTextBlock($b, 'standalone_test');
                    if ($slot >= count($old['page']['blocks'])) { self::assertNull($restored); ++$hidden; }
                    else {
                        self::assertNotSame($b, $restored); self::assertSame($old['page']['blocks'][$slot]['body'], $restored->body);
                        foreach (['type', 'column', 'heading', 'level', 'css_class'] as $field) { self::assertSame($old['page']['blocks'][$slot][$field] ?? null, $restored->$field); }
                    }
                    self::assertSame($attrs, $b->getAttributes()); self::assertSame([], $b->getDirty());
                }
                $foreign = clone $blocks[1]; $foreign->setRawAttributes(array_replace($foreign->getAttributes(), ['id' => 8599000 + $i, 'locale' => 'en', 'uuid' => 'm85-en-'.$i, 'sort_order' => 99, 'body' => 'Unchanged English probe.']), true); $blocks[] = $foreign;
                $page = new Page; $page->setRawAttributes(['id' => $target['page_binding']['id'], 'page_category_id' => $target['page_binding']['page_category_id'],
                    'seeder' => $target['identity'], 'slug' => $target['slug'], 'title' => $old['page']['title'], 'type' => 'theory', 'text' => $target['after']['page']['subtitle_text']], true);
                $page->setRelation('textBlocks', new Collection($blocks)); $attrs = $page->getAttributes(); $original = $page->textBlocks->toArray();
                $restored = Package::preserveCoursePage($page); self::assertNotSame($page, $restored); self::assertSame($old['page']['subtitle_text'], $restored->text);
                $uk = $restored->textBlocks->where('locale', 'uk')->values(); self::assertCount(count($old['page']['blocks']) + 1, $uk);
                foreach ($old['page']['blocks'] as $slot => $source) { self::assertSame($source['body'], $uk->firstWhere('uuid', Digest::uuid($target['identity'], $source, $slot + 1))->body); }
                self::assertSame($foreign, $restored->textBlocks->firstWhere('locale', 'en'));
                self::assertSame($attrs, $page->getAttributes()); self::assertSame($original, $page->textBlocks->toArray());
                $partial = clone $page; $partial->setRelation('textBlocks', $partial->textBlocks->slice(1)->values()); self::assertSame($partial, Package::preserveCoursePage($partial));
                $stale = clone $page; $copies = $page->textBlocks->map(static fn ($b) => clone $b); $copies[1]->body .= "\n";
                $stale->setRelation('textBlocks', $copies); self::assertSame($stale, Package::preserveCoursePage($stale));
            }
            self::assertSame(6, $hidden); self::assertNull(Package::preserveCoursePage(null));
        });
    }

    public function test_isolated_production_profile_keeps_identical_learning_content_without_http(): void
    {
        app()->setLocale('uk'); [, $patch] = $this->release();
        $request = app('request'); $domains = config('site-mode.production_domains'); $visible = [];
        config(['site-mode.production_domains' => ['m85-production.test']]);
        try {
            foreach (['m85-development.test' => SiteMode::DEVELOPMENT, 'm85-production.test' => SiteMode::PRODUCTION] as $host => $mode) {
                // In-memory Request objects only: no HTTP client, server or real production domain.
                app()->instance('request', Request::create('http://'.$host.'/theory/m85-isolated-profile'));
                self::assertSame($mode, app(SiteMode::class)->current());
                $this->noQueries(function () use ($patch, $mode, &$visible): void {
                    $count = 0;
                    foreach ($patch['targets'] as $i => $target) {
                        foreach ($target['after']['page']['blocks'] as $slot => $source) {
                            if (in_array($source['type'], ['hero', 'navigation-chips'], true)) { continue; }
                            [, $xp] = $this->rendered($this->block($target, $source, $slot)); ++$count;
                            self::assertSame(0, $xp->query('//*[@data-theory-render-fallback]')->length);
                            $visible[$mode][$i.':'.$slot] = $this->visible($xp);
                        }
                    }
                    self::assertSame(38, $count);
                });
            }
            self::assertSame($visible[SiteMode::DEVELOPMENT], $visible[SiteMode::PRODUCTION]);
        } finally { app()->instance('request', $request); config(['site-mode.production_domains' => $domains]); }
    }

    public function test_actual_first_strong_h1_extraction_preserves_six_titles_and_bilingual_inline_separators(): void
    {
        [$before, $patch, $master] = $this->release();
        $controller = (new ReflectionClass(PageController::class))->newInstanceWithoutConstructor();
        $extract = new ReflectionMethod(PageController::class, 'extractLocalizedTitleFromSubtitle'); $separators = 0;
        foreach ($patch['targets'] as $i => $target) {
            self::assertSame($before['targets'][$i]['before']['page']['title'], $target['after']['page']['title']);
            foreach ([$before['targets'][$i]['before']['page']['subtitle_html'], $target['after']['page']['subtitle_html']] as $subtitle) {
                self::assertSame(Package::RENDERED_TITLES[$i], $extract->invoke($controller, $subtitle));
            }
            self::assertSame(Package::RENDERED_TITLES[$i], $master['lessons'][$i]['preserved_h1']);
            foreach ($master['lessons'][$i]['blocks'] as $block) {
                if ($block['type'] !== 'forms-grid') { continue; }
                foreach ($block['data']['items'] as $item) {
                    if (!str_contains($item['subtitle'] ?? '', '<br>')) { continue; }
                    ++$separators; self::assertStringNotContainsString('</p><p', $item['subtitle']);
                    $xp = $this->dom($item['subtitle']); self::assertGreaterThan(0, $xp->query('//br')->length);
                    foreach (explode('<br>', $item['subtitle']) as $part) { self::assertNotSame('', self::text($part)); }
                }
            }
        }
        self::assertGreaterThanOrEqual(3, $separators, 'Multiple English/UK models retain supported literal bilingual separators, never paragraph tags stripped into glued text.');
    }
}
