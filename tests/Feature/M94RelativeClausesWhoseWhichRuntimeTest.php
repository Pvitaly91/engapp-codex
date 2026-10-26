<?php

namespace Tests\Feature;

use App\Http\Controllers\PageController;
use App\Models\Page;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\TextBlock;
use App\Support\M26DetailPackage as Digest;
use App\Support\M94RelativeClausesWhoseWhich as Package;
use App\Support\SiteMode;
use App\Support\TheoryContentSource;
use App\Support\TheoryPresentation;
use DOMDocument;
use DOMXPath;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use ReflectionMethod;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Isolated unsaved-model SSR. These are not working DB, browser or real-bank acceptance receipts. */
class M94RelativeClausesWhoseWhichRuntimeTest extends TestCase
{
    private function release(): array
    {
        $r = Package::load(dirname(__DIR__, 2)); Package::validate($r[0], $r[1], $r[2], $r[3]);
        foreach (Package::profile()['artifacts'] as $path => $sha) { self::assertSame($sha, hash_file('sha256', base_path($path))); }
        return $r;
    }

    private function block(array $target, int $slot): TextBlock
    {
        $source = $target['after']['page']['blocks'][$slot]; $b = new TextBlock;
        $b->setRawAttributes(['id' => $slot < 2 ? Package::CONTINUING_IDS[$slot] : 8940000 + $slot,
            'uuid' => Digest::uuid(Package::OWNERS[0], $source, $slot + 1), 'seeder' => Package::OWNERS[0], 'locale' => 'uk',
            'page_id' => 284, 'page_category_id' => 34, 'sort_order' => $slot + 1, 'type' => $source['type'],
            'column' => $source['column'], 'heading' => $source['heading'] ?? null, 'css_class' => $source['css_class'] ?? null,
            'level' => $source['level'] ?? null, 'body' => $source['body']], true);
        $b->setRelation('tags', new Collection); $b->setRelation('page', null);
        return $b;
    }

    private function subtitle(array $target): TextBlock
    {
        $b = new TextBlock;
        $b->setRawAttributes(['id' => Package::SUBTITLE_ID, 'uuid' => Package::SUBTITLE_UUID,
            'seeder' => Package::OWNERS[0], 'locale' => 'uk', 'page_id' => 284, 'page_category_id' => 34,
            'sort_order' => 0, 'type' => 'subtitle', 'column' => 'header', 'heading' => null, 'css_class' => null,
            'level' => null, 'body' => $target['after']['page']['subtitle_html']], true);
        $b->setRelation('tags', new Collection); return $b;
    }

    private function page(array $target): Page
    {
        $page = new Page;
        $page->setRawAttributes(['id' => 284, 'page_category_id' => 34, 'seeder' => Package::OWNERS[0],
            'slug' => Package::SLUGS[0], 'title' => Package::RENDERED_TITLES[0], 'type' => 'theory',
            'text' => $target['after']['page']['subtitle_text']], true);
        $blocks = [$this->subtitle($target)];
        foreach (range(0, 7) as $slot) { $blocks[] = $this->block($target, $slot); }
        $page->setRelation('textBlocks', new Collection($blocks)); return $page;
    }

    private function noQueries(callable $callback): void
    {
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        self::assertSame('testing', app()->environment()); self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName()); self::assertSame('array', config('cache.default'));
        self::assertSame('array', config('session.driver')); self::assertNotSame(realpath(base_path()), realpath(app()->environmentPath()));
        DB::connection()->enableQueryLog(); DB::connection()->flushQueryLog();
        try { $callback(); self::assertSame([], DB::connection()->getQueryLog(), 'Loaded clone/render operations must not query SQL.'); }
        finally { DB::connection()->disableQueryLog(); app()->setLocale('uk'); }
    }

    private function dom(string $html): DOMXPath
    {
        $doc = new DOMDocument('1.0', 'UTF-8'); $previous = libxml_use_internal_errors(true);
        try { self::assertTrue($doc->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET)); }
        finally { libxml_clear_errors(); libxml_use_internal_errors($previous); }
        return new DOMXPath($doc);
    }

    private static function text(string $html): string
    {
        $html = preg_replace('~</(?:p|div|li|h[1-6]|section|article)>|<br\s*/?>~iu', ' ', $html);
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private function visible(DOMXPath $xp): string
    {
        $s = ''; foreach ($xp->query('//body//text()[not(ancestor::script) and not(ancestor::style) and not(ancestor::template)]') as $n) { $s .= ' '.$n->textContent; }
        return self::text($s);
    }

    private function rendered(TextBlock $block): array
    {
        $html = view('theory.partials.content-block', ['block' => $block, 'theoryCanonical' => true,
            'm42StyleContext' => true, 'practiceQuestions' => new Collection, 'lessonLinks' => []])->render();
        return [$html, $this->dom($html)];
    }

    public function test_exact_hero_and_seven_other_slots_respect_json_versus_literal_html_without_mutation(): void
    {
        app()->setLocale('uk'); [, $patch, $master] = $this->release(); $target = $patch['targets'][0];
        $this->noQueries(function () use ($target, $master): void {
            foreach (range(0, 7) as $slot) {
                $b = $this->block($target, $slot); $attrs = $b->getAttributes();
                if ($slot === 1) {
                    self::assertSame('box', $b->type); self::assertNull($b->level);
                    self::assertNull(Package::presentation($b, []));
                    $source = TheoryContentSource::resolve($b, ['m42StyleContext' => true]);
                    self::assertNull($source['stored']); self::assertNull($source['view']); self::assertNull($source['design']);
                    self::assertStringStartsWith('<p>', $b->body);
                } else {
                    $data = json_decode($b->body, true, flags: JSON_THROW_ON_ERROR); $p = Package::presentation($b, $data);
                    self::assertNotNull($p); self::assertTrue(Package::validDesign($p['m42_native_design']));
                    self::assertSame('m94-native-v1', $p['m42_native_design']['package']);
                    self::assertSame($master['lessons'][0]['blocks'][$slot]['data'], array_diff_key($p['data'], ['m94_native_v1' => true]));
                    self::assertCount($slot === 3 ? 1 : 0, $p['points']);
                    $source = TheoryContentSource::resolve($b, ['m42StyleContext' => true]);
                    if (in_array($slot, [0, 6], true)) { self::assertNull($source['view']); self::assertNull($source['design']); }
                    else { self::assertSame(TheoryPresentation::nativeView($b->type), $source['view']); self::assertSame($p['m42_native_design'], $source['design']); }
                    foreach ([[], ['m42StyleContext' => false]] as $caller) {
                        $fallback = TheoryContentSource::resolve($b, $caller); self::assertNull($fallback['design']); self::assertSame($data, $fallback['data']);
                    }
                }
                self::assertSame($attrs, $b->getAttributes()); self::assertSame([], $b->getDirty()); self::assertFalse($b->exists);
            }
        });
    }

    public function test_legacy_box_uses_existing_card_once_and_never_json_or_an_inner_heading(): void
    {
        app()->setLocale('uk'); [, $patch, $master] = $this->release();
        $this->noQueries(function () use ($patch, $master): void {
            $b = $this->block($patch['targets'][0], 1); [$html, $xp] = $this->rendered($b);
            self::assertSame(1, $xp->query('//*[@id="block-8741"]')->length);
            self::assertSame(1, $xp->query('//*[@id="block-8741"]//h2')->length);
            self::assertSame(Package::BOX_HEADING, self::text($xp->query('//*[@id="block-8741"]//h2')->item(0)->textContent));
            self::assertSame(1, $xp->query('//*[@id="block-8741"]/article')->length);
            $content = $xp->query('//*[@id="block-8741"]//*[contains(concat(" ",normalize-space(@class)," ")," theory-html-content ")]')->item(0);
            // Preserve paragraph boundaries; DOM textContent concatenates adjacent p nodes.
            self::assertSame(self::text($master['lessons'][0]['blocks'][1]['data']['html']), self::text($content->ownerDocument->saveHTML($content)));
            self::assertSame(0, $xp->query('//*[@data-theory-render-fallback]')->length);
            self::assertStringNotContainsString('m94_native_v1', $html); self::assertStringNotContainsString('m94-section', $html);
        });
    }

    public function test_four_native_sections_show_all_paired_notes_and_one_meaningful_detail(): void
    {
        app()->setLocale('uk'); [, $patch, $master] = $this->release();
        $this->noQueries(function () use ($patch, $master): void {
            $details = $notes = 0;
            foreach ([2, 3, 4, 5] as $slot) {
                $b = $this->block($patch['targets'][0], $slot); [$html, $xp] = $this->rendered($b); $visible = $this->visible($xp);
                self::assertSame(1, $xp->query('//*[@data-theory-component="section" and @id="block-'.$b->id.'"]')->length);
                self::assertSame(0, $xp->query('//*[@data-theory-render-fallback]')->length);
                $details += $xp->query('//details')->length;
                self::assertSame($slot === 3 ? 1 : 0, $xp->query('//details')->length);
                self::assertDoesNotMatchRegularExpression('/\{a\d+\}|&lt;\/?(?:p|span|strong)\b/u', $visible);
                self::assertStringNotContainsString('m94-styles', $html);
                $data = $master['lessons'][0]['blocks'][$slot]['data'];
                if ($slot === 3) {
                    self::assertSame(4, $xp->query('//*[@data-theory-point-panel]')->length);
                    foreach ($data['sections'] as $point) { foreach ($point['examples'] as $example) { foreach (['en', 'ua'] as $field) { self::assertStringContainsString(self::text($example[$field]), $visible); } } }
                    foreach ($master['lessons'][0]['details'][0]['fragment']['data']['sections'][0]['examples'] as $example) { foreach (['en', 'ua'] as $field) { self::assertStringContainsString(self::text($example[$field]), $visible); } }
                } elseif ($slot === 4) {
                    $items = $xp->query('//*[@data-theory-component="mistake"]'); self::assertCount(4, $items);
                    foreach ($data['items'] as $n => $item) { ++$notes; foreach (['wrong_en', 'wrong_ua', 'right_en', 'right_ua', 'note'] as $field) { self::assertStringContainsString(self::text($item[$field]), self::text($items->item($n)->textContent)); } }
                } elseif ($slot === 2) { self::assertSame(3, $xp->query('//*[@data-theory-component="form"]')->length); }
            }
            self::assertSame([1, 4], [$details, $notes]);
        });
    }

    public function test_stale_or_foreign_metadata_fails_closed_without_mutating_original_models(): void
    {
        app()->setLocale('uk'); [, $patch] = $this->release(); $target = $patch['targets'][0];
        $this->noQueries(function () use ($target): void {
            foreach ([0, 1, 3, 7] as $slot) {
                $valid = $this->block($target, $slot); $data = $slot === 1 ? [] : json_decode($valid->body, true, flags: JSON_THROW_ON_ERROR);
                foreach (['id' => -1, 'uuid' => 'foreign', 'locale' => 'en', 'seeder' => 'Foreign\\Owner', 'page_id' => 285,
                    'page_category_id' => 35, 'sort_order' => 50, 'type' => 'comparison-table', 'column' => 'right',
                    'level' => 'C2', 'heading' => 'Unreviewed heading', 'css_class' => 'm94-custom', 'body' => $valid->body."\n"] as $field => $value) {
                    $bad = clone $valid; $bad->$field = $value; $attrs = $bad->getAttributes(); $dirty = $bad->getDirty();
                    self::assertNull(Package::presentation($bad, $data), $slot.':'.$field);
                    self::assertSame($bad, Package::preserveExcludedTextBlock($bad, 'standalone_test'));
                    self::assertSame($attrs, $bad->getAttributes()); self::assertSame($dirty, $bad->getDirty());
                }
            }
            $b = $this->block($target, 3); $data = json_decode($b->body, true, flags: JSON_THROW_ON_ERROR);
            unset($data['m94_native_v1']); self::assertNull(Package::presentation($b, $data));
            foreach (['en', 'pl'] as $locale) { app()->setLocale($locale); self::assertNull(Package::presentation($b, json_decode($b->body, true))); }
        });
    }

    public function test_standalone_clones_restore_both_old_blocks_and_subtitle_and_hide_only_six_new_blocks(): void
    {
        app()->setLocale('uk'); [$before, $patch] = $this->release(); $target = $patch['targets'][0];
        $this->noQueries(function () use ($before, $target): void {
            $old = $before['targets'][0]['before']; $hidden = 0;
            foreach (range(0, 7) as $slot) {
                $b = $this->block($target, $slot); $attrs = $b->getAttributes();
                foreach (['theory', 'course', 'unknown'] as $caller) { self::assertSame($b, Package::preserveExcludedTextBlock($b, $caller)); }
                $copy = Package::preserveExcludedTextBlock($b, 'standalone_test');
                if ($slot >= 2) { self::assertNull($copy); ++$hidden; }
                else {
                    self::assertNotSame($b, $copy); self::assertSame($old['page']['blocks'][$slot]['body'], $copy->body);
                    foreach (['type', 'column', 'heading', 'level', 'css_class'] as $field) { self::assertSame($old['page']['blocks'][$slot][$field] ?? null, $copy->$field); }
                    self::assertSame($b->id, $copy->id); self::assertSame($b->uuid, $copy->uuid); self::assertSame([], $copy->getDirty());
                }
                self::assertSame($attrs, $b->getAttributes()); self::assertSame([], $b->getDirty());
            }
            self::assertSame(6, $hidden); $subtitle = $this->subtitle($target); $attrs = $subtitle->getAttributes();
            $copy = Package::preserveExcludedTextBlock($subtitle, 'standalone_test');
            self::assertNotSame($subtitle, $copy); self::assertSame($old['page']['subtitle_html'], $copy->body);
            self::assertSame($attrs, $subtitle->getAttributes()); self::assertSame(Package::SUBTITLE_UUID, $copy->uuid);
        });
    }

    public function test_course_clone_requires_all_nine_loaded_uk_models_and_preserves_foreign_locale_identity(): void
    {
        app()->setLocale('uk'); [$before, $patch] = $this->release(); $target = $patch['targets'][0];
        $this->noQueries(function () use ($before, $target): void {
            $page = $this->page($target); $foreign = clone $page->textBlocks[1];
            $foreign->setRawAttributes(array_replace($foreign->getAttributes(), ['id' => 8940999, 'locale' => 'en', 'uuid' => 'm94-foreign-english', 'sort_order' => 90, 'body' => 'Unchanged foreign content.']), true);
            $page->getRelation('textBlocks')->push($foreign); $attrs = $page->getAttributes(); $original = $page->textBlocks->toArray();
            $copy = Package::preserveCoursePage($page); self::assertNotSame($page, $copy);
            self::assertSame($before['targets'][0]['before']['page']['subtitle_text'], $copy->text);
            self::assertCount(3, $copy->textBlocks->where('locale', 'uk')); self::assertCount(4, $copy->textBlocks);
            self::assertSame($foreign, $copy->textBlocks->firstWhere('locale', 'en'));
            foreach ([0, 1] as $slot) {
                $restored = $copy->textBlocks->firstWhere('uuid', Package::CONTINUING_UUIDS[$slot]);
                self::assertNotSame($page->textBlocks[$slot + 1], $restored);
                self::assertSame($before['targets'][0]['before']['page']['blocks'][$slot]['body'], $restored->body);
            }
            self::assertSame('B2 focus', $copy->textBlocks->firstWhere('uuid', Package::CONTINUING_UUIDS[1])->heading);
            self::assertSame($attrs, $page->getAttributes()); self::assertSame($original, $page->textBlocks->toArray());
            $partial = clone $page; $partial->setRelation('textBlocks', new Collection($page->textBlocks->slice(1)->all()));
            self::assertSame($partial, Package::preserveCoursePage($partial));
            $duplicate = clone $page; $duplicate->setRelation('textBlocks', new Collection([...$page->textBlocks->all(), clone $page->textBlocks[1]]));
            self::assertSame($duplicate, Package::preserveCoursePage($duplicate));
            $stale = clone $page; $models = $page->textBlocks->map(static fn ($b) => clone $b); $models[2]->body .= "\n";
            $stale->setRelation('textBlocks', new Collection($models->all())); self::assertSame($stale, Package::preserveCoursePage($stale));
            $dirty = clone $page; $dirty->text .= ' Unreviewed.'; self::assertSame($dirty, Package::preserveCoursePage($dirty));
            $unloaded = new Page; $unloaded->setRawAttributes($page->getAttributes(), true);
            self::assertSame($unloaded, Package::preserveCoursePage($unloaded)); self::assertNull(Package::preserveCoursePage(null));
        });
    }

    /** Synthetic rows verify SSR only, never source-bank eligibility or actual five-question acceptance. */
    private function syntheticQuestions(): Collection
    {
        $rows = [];
        foreach (range(0, 4) as $i) {
            $q = new Question; $q->setRawAttributes(['id' => 8941000 + $i, 'uuid' => 'm94-isolated-widget-'.$i,
                'seeder' => 'Database\\Seeders\\V3\\Polyglot\\M94IsolatedProbe', 'type' => '4', 'level' => 'B2',
                'question' => 'Склади повне речення про папку, яку ми знайшли.'], true);
            $answers = $options = [];
            foreach (['The folder', 'which we found', 'is empty.'] as $n => $token) {
                $option = new QuestionOption; $option->setRawAttributes(['id' => 8942000 + $i * 10 + $n, 'option' => $token], true);
                $a = new QuestionAnswer; $a->setRawAttributes(['id' => 8943000 + $i * 10 + $n, 'question_id' => $q->id, 'marker' => 'a'.($n + 1), 'option_id' => $option->id], true);
                $a->setRelation('option', $option); $answers[] = $a; $options[] = clone $option;
            }
            $q->setRelation('answers', new Collection($answers)); $q->setRelation('options', new Collection($options));
            foreach (['tags', 'hints', 'verbHints'] as $relation) { $q->setRelation($relation, new Collection); }
            $rows[] = $q;
        }
        return new Collection($rows);
    }

    public function test_practice_ssr_exposes_six_controls_visible_criteria_and_native_manual_token_fields(): void
    {
        app()->setLocale('uk'); [, $patch, $master] = $this->release();
        $this->noQueries(function () use ($patch, $master): void {
            $b = $this->block($patch['targets'][0], 7); $data = $master['lessons'][0]['blocks'][7]['data'];
            $questions = $this->syntheticQuestions(); $original = $questions->toArray();
            $html = view('theory.partials.content-block', ['block' => $b, 'theoryCanonical' => true, 'm42StyleContext' => true,
                'practiceQuestions' => $questions, 'lessonLinks' => []])->render();
            $xp = $this->dom($html); $visible = $this->visible($xp);
            self::assertSame(3, $xp->query('//*[contains(concat(" ",normalize-space(@class)," ")," theory-exercise ")]')->length);
            self::assertSame(0, $xp->query('//select')->length, 'The native UI uses option buttons, not select dropdowns.');
            self::assertSame(6, preg_match_all('/@click="selectAnswers\[[01]\] = /u', $html), 'Both native form controls expose three real option buttons.');
            self::assertSame(2, $xp->query('//*[@data-word-suggestion-input and starts-with(@data-word-suggestion-input,"inputs-")]')->length);
            foreach (['selects', 'choices'] as $group) { foreach ($data[$group] as $task) { self::assertStringContainsString(self::text($task['label']), $visible); } }
            foreach ($data['inputs'] as $task) { self::assertStringContainsString(self::text($task['after']), $visible); }
            foreach ($xp->query('//*[@data-word-suggestion-input]') as $field) { self::assertSame('off', $field->getAttribute('autocomplete')); }
            self::assertSame(1, $xp->query('//*[@data-sentence-builder]')->length);
            self::assertSame(0, $xp->query('//*[@data-theory-render-fallback]')->length);
            self::assertSame($original, $questions->toArray()); self::assertFalse($b->exists);
        });
    }

    public function test_native_h1_and_isolated_production_profile_preserve_the_same_teaching_content(): void
    {
        app()->setLocale('uk'); [$before, $patch] = $this->release(); $target = $patch['targets'][0];
        $controller = (new ReflectionClass(PageController::class))->newInstanceWithoutConstructor();
        $extract = new ReflectionMethod(PageController::class, 'extractLocalizedTitleFromSubtitle');
        foreach ([$before['targets'][0]['before']['page']['subtitle_html'], $target['after']['page']['subtitle_html']] as $html) {
            self::assertSame('Relative Clauses with Whose and Which', $extract->invoke($controller, $html));
        }
        $request = app('request'); $domains = config('site-mode.production_domains'); $visible = [];
        config(['site-mode.production_domains' => ['m94-production.test']]);
        try {
            foreach (['m94-development.test' => SiteMode::DEVELOPMENT, 'm94-production.test' => SiteMode::PRODUCTION] as $host => $mode) {
                app()->instance('request', Request::create('http://'.$host.'/theory/m94-isolated'));
                self::assertSame($mode, app(SiteMode::class)->current());
                $this->noQueries(function () use ($target, $mode, &$visible): void {
                    foreach ([1, 2, 3, 4, 5] as $slot) { [, $xp] = $this->rendered($this->block($target, $slot)); $visible[$mode][$slot] = $this->visible($xp); }
                });
            }
            self::assertSame($visible[SiteMode::DEVELOPMENT], $visible[SiteMode::PRODUCTION]);
        } finally { app()->instance('request', $request); config(['site-mode.production_domains' => $domains]); }
    }
}
