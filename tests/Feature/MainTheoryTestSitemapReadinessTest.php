<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\SavedGrammarTest;
use App\Models\Tag;
use App\Models\Test;
use App\Models\TextBlock;
use App\Services\CourseSitemapMetadataService;
use App\Services\GrammarTestFilterService;
use App\Services\SavedTestResolver;
use App\Services\TagAggregationService;
use App\Services\TheoryPagePromptLinkedTestsService;
use App\Support\VirtualTestRegistry;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class MainTheoryTestSitemapReadinessTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private const HOST = 'https://seo.production.test';

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        config([
            'app.debug' => false,
            'app.locale' => 'uk',
            'coming-soon.enabled' => true,
            'coming-soon.prefixes' => ['/test/'],
            'site-mode.production_domains' => ['seo.production.test'],
            'site-mode.production_origin' => 'https://gramlyze.com',
            'site-mode.production_locales' => ['uk'],
            'site-mode.response_cache.enabled' => false,
        ]);
        app()->setLocale('uk');
        $courses = \Mockery::mock(CourseSitemapMetadataService::class);
        $courses->shouldReceive('eligiblePaths')->andReturn([]);
        $this->app->instance(CourseSitemapMetadataService::class, $courses);
    }

    public function test_main_candidates_match_existing_builder_without_cache_session_or_registry_writes(): void
    {
        $page = $this->lesson('future-perfect', 'forms');
        $other = $this->lesson('present-perfect-continuous', 'questions');
        $service = app(TheoryPagePromptLinkedTestsService::class);
        $expected = $service->buildForPage($page)->first(fn ($test) => $test->public_slug === 'future-perfect/forms');
        Cache::flush();
        session()->flush();
        $writes = [];
        Event::listen(KeyWritten::class, function (KeyWritten $event) use (&$writes): void {
            $writes[] = $event->key;
        });
        DB::enableQueryLog();
        DB::flushQueryLog();
        $tests = $service->mainSitemapTests(collect([$other, $page]));
        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        $this->assertSame(['future-perfect/forms', 'present-perfect-continuous/questions'], $tests->pluck('public_slug')->all());
        $this->assertSame($expected->filters, $tests->first()->filters);
        $this->assertSame($expected->totalQuestionsAvailable, $tests->first()->totalQuestionsAvailable);
        $this->assertSame([], $writes);
        $this->assertSame([], session()->all());
        $this->assertLessThanOrEqual(8, count($queries), 'Metadata and readiness aggregates are batched, not issued per page.');
        foreach ($queries as $query) {
            $this->assertDoesNotMatchRegularExpression('/select\s+["`]?questions["`]?\.\*|select\s+\*\s+from\s+["`]?questions["`]?/i', $query['query']);
            $this->assertStringNotContainsString('saved_grammar_test_questions', $query['query']);
        }
        $this->assertFalse(VirtualTestRegistry::has('future-perfect/forms'));
    }

    public function test_cold_direct_and_cold_sitemap_then_direct_are_indexable_on_two_topics_including_questions(): void
    {
        $paths = [];
        foreach (['future-perfect', 'present-perfect-continuous'] as $topic) {
            foreach (['forms', 'negatives', 'questions', 'time-expressions'] as $segment) {
                $this->lesson($topic, $segment);
                $paths[] = '/test/'.$topic.'/'.$segment;
            }
        }
        foreach ([false, true] as $sitemapFirst) {
            foreach ($paths as $path) {
                Cache::flush();
                session()->flush();
                if ($sitemapFirst) {
                    $this->get(self::HOST.'/sitemap.xml')->assertOk()->assertSee('https://gramlyze.com'.$path, false);
                    $this->assertFalse(VirtualTestRegistry::has(substr($path, 6)));
                }
                $response = $this->get(self::HOST.$path, ['Accept' => 'text/html']);
                $response->assertOk()->assertViewIs('test-show')->assertSee('She', false);
                $this->assertStringNotContainsString('noindex', (string) $response->headers->get('X-Robots-Tag'));
                $this->assertDoesNotMatchRegularExpression('/<meta\b[^>]*name=["\']robots["\'][^>]*noindex/i', $response->getContent());
                $document = new \DOMDocument;
                @$document->loadHTML($response->getContent());
                $canonical = (new \DOMXPath($document))->query('//link[@rel="canonical"]');
                $this->assertCount(1, $canonical);
                $this->assertSame('https://gramlyze.com'.$path, $canonical->item(0)->getAttribute('href'));
            }
        }
    }

    public function test_questions_html_and_technical_data_endpoint_remain_distinct_even_with_a_parent_saved_test(): void
    {
        $this->lesson('future-perfect', 'questions');
        Test::create(['name' => 'Parent', 'slug' => 'future-perfect', 'questions' => []]);
        $this->get(self::HOST.'/sitemap.xml')->assertOk()
            ->assertSee('https://gramlyze.com/test/future-perfect/questions</loc>', false)
            ->assertDontSee('/questions/questions</loc>', false);
        $this->get(self::HOST.'/test/future-perfect/questions', ['Accept' => 'text/html'])
            ->assertOk()->assertViewIs('test-show');
        $this->get(self::HOST.'/test/future-perfect/questions/questions?mode=saved-test-js-v2', ['Accept' => 'application/json'])
            ->assertOk()->assertJsonCount(2, 'questions')->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }

    public function test_empty_filtered_unusable_standard_only_and_cache_only_candidates_are_excluded(): void
    {
        $ready = $this->lesson('future-perfect', 'forms');
        $empty = $this->lesson('future-perfect', 'empty', ['difficulty_from' => 9]);
        $unusable = $this->lesson('future-perfect', 'unusable', [], false);
        $standardOnly = $this->lesson('future-perfect', 'standard-only', [], true, false);
        $unlinked = Page::create(['slug' => 'future-perfect-cache-only', 'title' => 'Cache only', 'type' => 'theory', 'page_category_id' => $ready->page_category_id]);
        VirtualTestRegistry::registerStatic('future-perfect/cache-only', 'Temporary', [
            'seeder_classes' => ['anything'], 'num_questions' => 1, '__meta' => ['mode' => 'filters'],
        ]);
        $this->assertSame(['future-perfect/forms'], app(TheoryPagePromptLinkedTestsService::class)
            ->mainSitemapTests(collect([$ready, $empty, $unusable, $standardOnly, $unlinked]))->pluck('public_slug')->all());
        $this->get(self::HOST.'/sitemap.xml')->assertOk()->assertDontSee('/test/future-perfect/cache-only</loc>', false);
        Cache::flush();
        session()->flush();
        $this->get(self::HOST.'/test/future-perfect/cache-only', ['Accept' => 'text/html'])->assertNotFound();
    }

    public function test_candidates_ignore_registry_referer_and_source_query_and_omit_persisted_slug_collisions(): void
    {
        $page = $this->lesson('future-perfect', 'forms');
        $other = $this->lesson('present-perfect-continuous', 'forms');
        $service = app(TheoryPagePromptLinkedTestsService::class);
        $expected = $service->mainSitemapTests(collect([$page, $other]))->pluck('public_slug')->all();
        VirtualTestRegistry::registerStatic('future-perfect/forms', 'Unrelated temporary payload', ['__meta' => ['mode' => 'filters'], 'num_questions' => 1]);
        session(['coming_soon.allowed_theory_test_slugs' => ['fake' => true], 'locale' => 'en']);
        $response = $this->get(self::HOST.'/sitemap.xml?source=theory&filters=bogus', ['Referer' => 'http://gramlyze.loc/theory/fake']);
        $response->assertOk();
        foreach ($expected as $slug) {
            $response->assertSee('https://gramlyze.com/test/'.$slug.'</loc>', false);
        }
        Test::create(['name' => 'Shadow', 'slug' => 'future-perfect/forms', 'questions' => []]);
        SavedGrammarTest::create(['uuid' => (string) Str::uuid(), 'name' => 'Shadow', 'slug' => 'present-perfect-continuous/forms', 'filters' => []]);
        $this->assertCount(0, $service->mainSitemapTests(collect([$page, $other])));
    }

    public function test_exact_resolver_roundtrip_avoids_prefixed_page_aliases_and_noncanonical_slug_normalization(): void
    {
        $main = $this->lesson('future-perfect', 'forms');
        $alias = $this->lesson('future-perfect', 'alias');
        $alias->update(['slug' => 'forms']);
        $odd = $this->lesson('future-perfect', 'odd');
        $odd->update(['slug' => 'odd?query']);
        $this->assertSame(['future-perfect/forms'], app(TheoryPagePromptLinkedTestsService::class)
            ->mainSitemapTests(collect([$main, $alias, $odd]))->pluck('public_slug')->all());
    }

    public function test_explicit_uk_locale_override_matches_cold_resolver_without_mutating_application_locale(): void
    {
        $page = $this->lesson('future-perfect', 'forms');
        $linked = SavedGrammarTest::query()->where('filters->prompt_generator->theory_page_id', $page->id)->get();
        $mapping = [];
        foreach ($linked as $saved) {
            $old = $saved->filters['seeder_classes'][0];
            $new = str_replace('Fixture', 'UkrainianFixture', $old);
            $mapping[$old] = $new;
            $original = Question::where('seeder', $old)->first();
            $replacement = Question::withoutEvents(fn () => $original->replicate());
            $replacement->uuid = (string) Str::uuid();
            $replacement->seeder = $new;
            Question::withoutEvents(fn () => $replacement->save());
            $replacement->answers()->create(['marker' => 'a1', 'option_id' => $original->answers->first()->option_id]);
        }
        foreach ($linked as $saved) {
            $filters = $saved->filters;
            $filters['__meta']['theory_page_mixed_locale_seeder_overrides']['uk'] = $mapping;
            $saved->update(['filters' => $filters]);
        }
        app()->setLocale('uk');
        $cold = app(SavedTestResolver::class)->resolveTheoryPageSlug('future-perfect/forms');
        app()->setLocale('en');
        $candidate = app(TheoryPagePromptLinkedTestsService::class)->mainSitemapTests(collect([$page]), 'uk')->first();
        $this->assertNotNull($candidate);
        $this->assertSame($cold->model->filters, $candidate->filters);
        $this->assertSame('en', app()->getLocale());
    }

    public function test_shared_filter_predicate_preserves_flags_and_applies_actual_difficulty_type_and_blank_filters(): void
    {
        $page = $this->lesson('future-perfect', 'forms');
        $seeders = Question::query()->pluck('seeder')->all();
        Question::withoutEvents(fn () => Question::query()->update(['flag' => 2]));
        $filters = ['seeder_classes' => $seeders, 'levels' => ['A1'], 'theory_category_page_test' => true];
        $service = app(GrammarTestFilterService::class);
        $this->assertFalse($service->matchingQuestionsQuery($filters)->exists());
        $this->assertCount(0, $service->questionsFromFilters($filters));
        $filters['aggregated_theory_page_test'] = true;
        $this->assertSame(2, $service->matchingQuestionsQuery($filters)->count());
        $this->assertCount(2, $service->questionsFromFilters($filters));
        foreach ([['difficulty_from' => 8], ['question_types' => ['4']], ['blank_count_from' => 2], ['categories' => [999]]] as $restriction) {
            $this->assertFalse($service->matchingQuestionsQuery(array_merge($filters, $restriction))->exists());
            $this->assertCount(0, $service->questionsFromFilters(array_merge($filters, $restriction)));
        }
    }

    public function test_typed_candidate_queries_scale_by_bounded_chunks_not_by_pages(): void
    {
        $pages = collect();
        $measurements = [];
        foreach ([2, 45] as $target) {
            while ($pages->count() < $target) {
                $pages->push($this->lesson('scale-topic', 'lesson-'.$pages->count(), ['question_types' => ['0']]));
            }
            DB::enableQueryLog();
            DB::flushQueryLog();
            $started = hrtime(true);
            $ready = app(TheoryPagePromptLinkedTestsService::class)->mainSitemapTests($pages);
            $elapsed = (hrtime(true) - $started) / 1000000;
            $queries = DB::getQueryLog();
            DB::disableQueryLog();
            $this->assertCount($target, $ready);
            $this->assertLessThanOrEqual($target === 2 ? 8 : 10, count($queries));
            $readinessQueries = collect($queries)->filter(fn (array $query): bool => str_contains($query['query'], 'AS matched_0'));
            $this->assertCount((int) ceil($target / 40), $readinessQueries);
            foreach ($readinessQueries as $query) {
                $this->assertStringNotContainsString('union', strtolower($query['query']), 'Readiness scans questions once per chunk, not per candidate.');
                $this->assertStringContainsString('MIN(CASE WHEN', $query['query']);
                $this->assertStringNotContainsString('AS unusable_', $query['query'], 'One matching predicate per candidate, not two.');
            }
            foreach ($queries as $query) {
                $this->assertDoesNotMatchRegularExpression('/select\s+["`]?questions["`]?\.\*|select\s+\*\s+from\s+["`]?questions["`]?/i', $query['query']);
            }
            DB::enableQueryLog();
            DB::flushQueryLog();
            $sitemapStarted = hrtime(true);
            $response = $this->get(self::HOST.'/sitemap.xml');
            $sitemapElapsed = (hrtime(true) - $sitemapStarted) / 1000000;
            $sitemapQueries = DB::getQueryLog();
            DB::disableQueryLog();
            $response->assertOk();
            foreach ($sitemapQueries as $query) {
                $this->assertDoesNotMatchRegularExpression('/select\s+["`]?questions["`]?\.\*|select\s+\*\s+from\s+["`]?questions["`]?/i', $query['query']);
            }
            if ($measurements !== []) {
                $this->assertLessThanOrEqual($measurements[0]['sitemapQueries'] + 4, count($sitemapQueries));
            }
            $measurements[] = ['pages' => $target, 'ready' => $ready->count(), 'queries' => count($queries), 'milliseconds' => round($elapsed, 3),
                'sitemapQueries' => count($sitemapQueries), 'sitemapMilliseconds' => round($sitemapElapsed, 3), 'profile' => 'isolated-production-kernel'];
        }
        fwrite(STDOUT, "\nM4_MAIN_METADATA_SCALE ".json_encode($measurements, JSON_UNESCAPED_SLASHES)."\n");
    }

    public function test_single_scan_readiness_preserves_filter_bindings_and_usable_answer_semantics(): void
    {
        $strict = $this->lesson('aggregate-topic', 'strict', [
            'difficulty_from' => 1, 'difficulty_to' => 3, 'question_types' => ['0'],
            'blank_count_from' => 1, 'blank_count_to' => 1, 'only_ai_v2' => true, 'categories' => [123],
        ]);
        $seeders = SavedGrammarTest::query()->where('filters->prompt_generator->theory_page_id', $strict->id)
            ->get()->flatMap(fn (SavedGrammarTest $test): array => $test->filters['seeder_classes'])->all();
        Question::withoutEvents(fn () => Question::whereIn('seeder', $seeders)->update(['flag' => 2, 'category_id' => 123]));
        // An unusable row that fails the same difficulty/type/flag/blank predicate
        // must not invalidate this test merely because its seeder matches.
        $outside = Question::whereIn('seeder', $seeders)->first()->replicate();
        $outside->uuid = (string) Str::uuid();
        $outside->difficulty = 9;
        $outside->type = '4';
        $outside->flag = 0;
        Question::withoutEvents(fn () => $outside->save());

        $multipleAnswers = $this->lesson('aggregate-topic', 'multiple-answers');
        $empty = $this->lesson('aggregate-topic', 'empty', ['difficulty_from' => 8, 'question_types' => ['4']]);
        $badMarker = $this->lesson('aggregate-topic', 'bad-marker');
        $badOption = $this->lesson('aggregate-topic', 'bad-option');
        $badQuestion = $this->lesson('aggregate-topic', 'bad-question');
        foreach ([$multipleAnswers, $badMarker, $badOption, $badQuestion] as $page) {
            $seeder = SavedGrammarTest::where('filters->prompt_generator->theory_page_id', $page->id)->first()->filters['seeder_classes'][0];
            $question = Question::where('seeder', $seeder)->first();
            if ($page->is($multipleAnswers)) {
                $question->answers()->create(['marker' => 'a2', 'option_id' => $question->answers->first()->option_id]);
            } elseif ($page->is($badMarker)) {
                $question->answers()->update(['marker' => '   ']);
            } elseif ($page->is($badOption)) {
                $option = QuestionOption::create(['option' => '   ']);
                $question->answers()->update(['option_id' => $option->id]);
            } else {
                Question::withoutEvents(fn () => $question->update(['question' => '   ']));
            }
        }

        $pages = collect([$strict, $multipleAnswers, $empty, $badMarker, $badOption, $badQuestion]);
        $service = app(TheoryPagePromptLinkedTestsService::class);
        $filters = app(GrammarTestFilterService::class);
        $expected = $pages->map(fn (Page $page) => $service->buildForPage($page)
            ->first(fn ($test): bool => $test->filters['theory_page_mixed_all_levels'] ?? false))
            ->filter(function ($test) use ($filters): bool {
                $matching = $filters->matchingQuestionsQuery($test->filters);

                return $filters->constrainUsableQuestions(clone $matching)->exists()
                    && ! (clone $matching)->whereNot(fn ($query) => $filters->constrainUsableQuestions($query))->exists();
            })->pluck('public_slug')->sort()->values()->all();
        $this->assertSame(['aggregate-topic/multiple-answers', 'aggregate-topic/strict'], $expected);
        $this->assertSame($expected, $service->mainSitemapTests($pages)->pluck('public_slug')->all());
    }

    public function test_main_candidates_do_not_enable_a_locale_disallowed_in_production(): void
    {
        $page = $this->lesson('future-perfect', 'forms');
        config(['site-mode.production_locales' => ['pl']]);
        $this->assertCount(0, app(TheoryPagePromptLinkedTestsService::class)->mainSitemapTests(collect([$page]), 'uk'));
    }

    public function test_route_identity_excludes_ui_mode_collisions_but_preserves_questions_and_future_simple_exception(): void
    {
        $pages = collect();
        foreach (['forms', 'questions', 'step', 'manual', 'input', 'select', 'drag-drop', 'match', 'dialogue'] as $segment) {
            $pages->push($this->lesson('future-perfect', $segment));
        }
        $pages->push($this->lesson('future-simple', 'time-expressions'));
        $this->assertSame(['future-perfect/forms', 'future-perfect/questions', 'future-simple/time-expressions'],
            app(TheoryPagePromptLinkedTestsService::class)->mainSitemapTests($pages)->pluck('public_slug')->all());
    }

    public function test_normalized_legacy_link_fallback_preserves_natural_id_reference_precedence(): void
    {
        $page = $this->lesson('future-perfect', 'forms');
        $linked = SavedGrammarTest::query()->orderBy('id')->get();
        foreach ($linked as $index => $saved) {
            $filters = $saved->filters;
            // Legacy PromptGeneratorFilterNormalizer accepts key=value pairs,
            // not a JSON document wrapped in a string.
            $filters['prompt_generator'] = 'source_type=theory_page; theory_page_id='.$page->id;
            $filters['difficulty_to'] = $index === 0 ? 4 : 8;
            $saved->filters = $filters;
            $saved->updated_at = $index === 0 ? '2020-01-01 00:00:00' : '2025-01-01 00:00:00';
            $saved->save();
        }
        $service = app(TheoryPagePromptLinkedTestsService::class);
        $expected = $service->buildForPage($page)->first(fn ($test) => $test->public_slug === 'future-perfect/forms');
        $actual = $service->mainSitemapTests(collect([$page]))->first();
        $this->assertNotNull($expected);
        $this->assertNotNull($actual);
        $this->assertSame(4, $actual->filters['difficulty_to']);
        $this->assertSame($expected->filters, $actual->filters);
    }

    public function test_a_usable_question_beyond_the_selected_prefix_does_not_make_an_unusable_bank_ready(): void
    {
        $page = $this->lesson('future-perfect', 'forms', [], false);
        $originals = Question::query()->get();
        foreach ($originals as $original) {
            for ($index = 0; $index < 15; $index++) {
                $question = $original->replicate();
                $question->uuid = (string) Str::uuid();
                Question::withoutEvents(fn () => $question->save());
            }
        }
        $question = $originals->first()->replicate();
        $question->uuid = (string) Str::uuid();
        Question::withoutEvents(fn () => $question->save());
        $option = QuestionOption::firstOrCreate(['option' => 'will have finished']);
        $question->answers()->create(['marker' => 'a1', 'option_id' => $option->id]);
        $resolved = app(SavedTestResolver::class)->resolveTheoryPageSlug('future-perfect/forms');
        $this->assertCount(14, $resolved->questionIds);
        $this->assertFalse($resolved->questionIds->contains($question->id));
        $this->assertFalse(Question::query()->whereIn('id', $resolved->questionIds)->whereHas('answers')->exists());
        $this->assertCount(0, app(TheoryPagePromptLinkedTestsService::class)->mainSitemapTests(collect([$page])));
    }

    public function test_nonpublic_orphaned_and_cyclic_category_ancestry_is_excluded_only_from_new_test_candidates(): void
    {
        $root = PageCategory::create(['title' => 'Root', 'slug' => 'root', 'language' => 'en', 'type' => 'theory']);
        $foreignAncestor = $this->lesson('nested-topic', 'forms');
        $foreignAncestor->category->update(['parent_id' => $root->id]);
        $orphan = $this->lesson('orphan-topic', 'forms');
        $orphan->category->update(['parent_id' => 999999]);
        $cycle = $this->lesson('cyclic-topic', 'forms');
        $cycle->category->update(['parent_id' => $cycle->page_category_id]);
        $good = $this->lesson('future-perfect', 'forms');
        $this->assertSame(['future-perfect/forms'], app(TheoryPagePromptLinkedTestsService::class)
            ->mainSitemapTests(collect([$foreignAncestor, $orphan, $cycle, $good]))->pluck('public_slug')->all());
    }

    public function test_request_local_partitions_preserve_overlapping_seeders_and_reflect_changes_and_deletions(): void
    {
        $a = $this->lesson('partition-topic', 'first', ['difficulty_to' => 3]);
        $b = $this->lesson('partition-topic', 'second', ['difficulty_from' => 5]);
        $aTests = SavedGrammarTest::where('filters->prompt_generator->theory_page_id', $a->id)->get();
        $shared = $aTests->flatMap(fn ($test) => $test->filters['seeder_classes'])->all();
        foreach (SavedGrammarTest::where('filters->prompt_generator->theory_page_id', $b->id)->get() as $saved) {
            $filters = $saved->filters;
            $filters['seeder_classes'] = $shared;
            $saved->update(['filters' => $filters]);
        }
        $service = app(TheoryPagePromptLinkedTestsService::class);
        $pages = collect([$b, $a]);
        $this->assertSame(['partition-topic/first'], $service->mainSitemapTests($pages)->pluck('public_slug')->all());
        Question::withoutEvents(fn () => Question::whereIn('seeder', $shared)->update(['difficulty' => 6]));
        $this->assertSame(['partition-topic/second'], $service->mainSitemapTests($pages)->pluck('public_slug')->all());
        Question::withoutEvents(fn () => Question::whereIn('seeder', $shared)->delete());
        $this->assertSame([], $service->mainSitemapTests($pages)->pluck('public_slug')->all());
    }

    public function test_partitioned_metadata_matches_slow_reference_with_tags_sources_and_answer_multiplicity(): void
    {
        $aggregation = \Mockery::mock(TagAggregationService::class);
        $aggregation->shouldReceive('getAggregations')->andReturn([
            ['main_tag' => 'group', 'similar_tags' => ['similar']],
        ]);
        $this->app->instance(TagAggregationService::class, $aggregation);
        $page = $this->lesson('differential-topic', 'forms', [
            'tags' => ['first', 'second'], 'aggregated_tags' => ['group'],
            'sources' => [57], 'categories' => [91], 'difficulty_from' => 1, 'difficulty_to' => 3,
            'blank_count_from' => 2, 'blank_count_to' => 2, 'question_types' => ['0'],
        ]);
        $questions = Question::all();
        foreach ($questions as $question) {
            Question::withoutEvents(fn () => $question->update(['source_id' => 57, 'category_id' => 91]));
            foreach (['first', 'second', 'similar'] as $tag) {
                $question->tags()->attach(Tag::firstOrCreate(['name' => $tag])->id);
            }
            // Distinct options let both markers become blank later without
            // violating the fixture's (question, marker, option) unique key.
            $question->answers()->create(['marker' => 'a2', 'option_id' => QuestionOption::firstOrCreate(['option' => 'will have completed'])->id]);
        }
        $service = app(TheoryPagePromptLinkedTestsService::class);
        $filters = app(GrammarTestFilterService::class);
        $reference = $service->buildForPage($page)->first(fn ($test) => $test->public_slug === 'differential-topic/forms');
        $this->assertNotNull($reference);
        $matching = $filters->matchingQuestionsQuery($reference->filters);
        $this->assertSame(2, $matching->count(), 'Multiple matching tags/answers must not multiply questions.');
        $this->assertSame(2, $filters->constrainUsableQuestions(clone $matching)->count());
        $actual = $service->mainSitemapTests(collect([$page]))->first();
        $this->assertNotNull($actual);
        $this->assertSame($reference->filters, $actual->filters);
        foreach (['source_id' => 99, 'category_id' => 99, 'difficulty' => 8, 'type' => '4', 'flag' => 7] as $field => $value) {
            $outside = $questions->first()->replicate();
            $outside->uuid = (string) Str::uuid();
            $outside->{$field} = $value;
            Question::withoutEvents(fn () => $outside->save());
            // No answers/tags; this unrelated unusable row cannot veto readiness.
            $this->assertCount(1, $service->mainSitemapTests(collect([$page])));
        }
        $questions->first()->answers()->update(['marker' => '   ']);
        $this->assertSame(1, (clone $matching)->whereNot(fn ($query) => $filters->constrainUsableQuestions($query))->count());
        $this->assertCount(0, $service->mainSitemapTests(collect([$page])));
    }

    public function test_nullable_and_empty_readiness_fields_follow_the_same_reference_predicate(): void
    {
        // Nullable compatibility schema exists only inside the guarded test DB.
        foreach (['question_answers', 'question_options', 'questions'] as $table) {
            Schema::drop($table);
        }
        Schema::create('questions', function ($table) {
            $table->id();
            foreach (['uuid', 'question', 'level', 'seeder', 'type', 'options_by_marker'] as $name) {
                $table->text($name)->nullable();
            }
            foreach (['difficulty', 'flag', 'category_id', 'source_id'] as $name) {
                $table->integer($name)->nullable();
            }
            $table->timestamps();
        });
        Schema::create('question_options', function ($table) {
            $table->id();
            $table->text('option')->nullable();
            $table->timestamps();
        });
        Schema::create('question_answers', function ($table) {
            $table->id();
            $table->integer('question_id');
            $table->integer('option_id')->nullable();
            $table->text('marker')->nullable();
            $table->timestamps();
        });
        $page = $this->lesson('nullable-topic', 'forms');
        $service = app(TheoryPagePromptLinkedTestsService::class);
        $filter = app(GrammarTestFilterService::class);
        $question = Question::first();
        foreach (['uuid', 'question', 'marker', 'option'] as $field) {
            $table = match ($field) {
                'marker' => 'question_answers', 'option' => 'question_options', default => 'questions'
            };
            $id = match ($table) {
                'question_answers' => $question->answers->first()->id,
                'question_options' => $question->answers->first()->option_id,
                default => $question->id,
            };
            $original = DB::table($table)->where('id', $id)->value($field);
            foreach ([null, '', '   '] as $value) {
                DB::table($table)->where('id', $id)->update([$field => $value]);
                $this->assertFalse($filter->constrainUsableQuestions(Question::whereKey($question->id))->exists(), $field);
                $this->assertCount(0, $service->mainSitemapTests(collect([$page])), $field);
            }
            DB::table($table)->where('id', $id)->update([$field => $original]);
            $this->assertCount(1, $service->mainSitemapTests(collect([$page])));
        }
    }

    public function test_primary_link_prefilter_remains_a_superset_of_the_original_sql_predicate(): void
    {
        $pages = collect();
        $cases = ['id', 'nested', 'ids', 'string-id', 'float-id', 'scalar-ids', 'seeder', 'slug', 'wrong-category', 'unicode', 'legacy'];
        foreach ($cases as $case) {
            $page = $this->lesson('link-topic', $case);
            $page->update(['seeder' => 'Database\\Seeders\\Page_V3\\Link'.$page->id]);
            foreach (SavedGrammarTest::where('filters->prompt_generator->theory_page_id', $page->id)->get() as $saved) {
                $filters = $saved->filters;
                $filters['prompt_generator'] = match ($case) {
                    'id' => ['theory_page_id' => $page->id],
                    'nested' => ['theory_page' => ['id' => $page->id]],
                    'ids' => ['theory_page_ids' => [$page->id, 99999]],
                    'string-id' => ['theory_page_id' => (string) $page->id],
                    'float-id' => ['theory_page_id' => $page->id + 0.0],
                    'scalar-ids' => ['theory_page_ids' => $page->id],
                    'seeder' => ['theory_page' => ['page_seeder_class' => $page->seeder]],
                    'slug' => ['theory_page' => ['slug' => $page->slug, 'category_slug_path' => 'root/link-topic']],
                    'wrong-category' => ['theory_page' => ['slug' => $page->slug, 'category_slug_path' => 'wrong']],
                    'unicode' => ['theory_page_id' => $page->id, 'theory_page' => ['slug' => 'незвична-назва']],
                    'legacy' => 'source_type=theory_page; theory_page_id='.$page->id,
                };
                $saved->update(['filters' => $filters]);
            }
            $pages->push($page);
        }
        $reference = new class extends TheoryPagePromptLinkedTestsService
        {
            public function primaryIds(Page $page): array
            {
                return $this->linkedTestsQueryForPage($page)->pluck('id')->sort()->values()->all();
            }
        };
        $prefilter = new \ReflectionMethod(TheoryPagePromptLinkedTestsService::class, 'possiblePrimarySitemapLinks');
        $candidateIds = $prefilter->invoke($reference, $pages, SavedGrammarTest::all());
        foreach ($pages as $page) {
            $oldIds = $reference->primaryIds($page);
            $this->assertSame([], array_diff($oldIds, $candidateIds[$page->id]), $page->slug);
        }
        $expected = $pages->map(fn ($page) => $reference->buildForPage($page)
            ->first(fn ($test) => $test->filters['theory_page_mixed_all_levels'] ?? false))
            ->filter()->pluck('public_slug')->sort()->values()->all();
        $this->assertSame($expected, $reference->mainSitemapTests($pages)->pluck('public_slug')->all());
        // Removing persistent links must be visible even on the same service instance.
        SavedGrammarTest::query()->delete();
        $this->assertSame([], $reference->mainSitemapTests($pages)->all());
    }

    private function lesson(string $topic, string $segment, array $extraFilters = [], bool $answers = true, bool $mixed = true): Page
    {
        $category = PageCategory::firstOrCreate(['slug' => $topic], ['title' => $topic, 'language' => 'uk', 'type' => 'theory']);
        $page = Page::create(['title' => $topic.' '.$segment, 'slug' => $topic.'-'.$segment, 'type' => 'theory', 'page_category_id' => $category->id]);
        TextBlock::create(['uuid' => (string) Str::uuid(), 'page_id' => $page->id, 'page_category_id' => $category->id, 'locale' => 'uk', 'type' => 'box', 'body' => '<p>Навчальна теорія для цього тесту.</p>']);
        foreach ($mixed ? ['Tenses\\M4', 'Polyglot'] : ['Tenses\\M4'] as $kind) {
            $seeder = 'Database\\Seeders\\V3\\'.$kind.'\\Fixture'.$page->id.'Seeder';
            $question = Question::withoutEvents(fn () => Question::create([
                'uuid' => (string) Str::uuid(), 'question' => 'She {a1} her work by noon.', 'difficulty' => 1, 'level' => 'A1',
                'type' => '0', 'flag' => 0, 'seeder' => $seeder, 'options_by_marker' => ['a1' => ['will have finished', 'will finish']],
            ]));
            if ($answers) {
                $option = QuestionOption::firstOrCreate(['option' => 'will have finished']);
                $question->answers()->create(['marker' => 'a1', 'option_id' => $option->id]);
            }
            $saved = SavedGrammarTest::create([
                'uuid' => (string) Str::uuid(), 'name' => $page->title.' '.$kind, 'slug' => 'm4-fixture-'.$question->id,
                'filters' => array_merge(['seeder_classes' => [$seeder], 'levels' => ['A1'], 'num_questions' => 2,
                    'prompt_generator' => ['source_type' => 'theory_page', 'theory_page_id' => $page->id]], $extraFilters),
            ]);
            $saved->questionLinks()->create(['question_uuid' => $question->uuid, 'position' => 1]);
        }

        return $page;
    }
}
