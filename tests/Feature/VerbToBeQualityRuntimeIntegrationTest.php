<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Services\GrammarTestFilterService;
use App\Services\SavedTestResolver;
use App\Services\TheoryPagePromptLinkedTestsService;
use App\Support\SentenceReorderQuestionFactory as Factory;
use App\Support\VerbToBePracticeQuality as Quality;
use Illuminate\Support\Arr;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Real saved-test/filter integration in isolated SQLite, never the working database. */
class VerbToBeQualityRuntimeIntegrationTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        config(['coming-soon.enabled' => false, 'tests.tech_info_enabled' => false]);
    }

    public function test_extra_sonate_page_banks_are_overridden_only_in_uk_mixed_without_rewriting_their_saved_rows(): void
    {
        foreach (['present', 'future'] as $topic) {
            $map = Quality::mapping($topic);
            app()->setLocale('en');
            $extra = $map['additional_originals'][0];
            $this->seed([$map['standard']['old_class'], $map['builder']['old_class'], $extra['class']]);
            $source = json_decode(file_get_contents(dirname(__DIR__, 2).'/'.$extra['path']), true, flags: JSON_THROW_ON_ERROR);
            $sonate = SavedGrammarTest::where('uuid', $source['saved_test']['uuid'])->firstOrFail();
            $originalAttributes = $sonate->getAttributes();
            $oldIds = Question::where('seeder', $extra['class'])->pluck('id')->all();
            $this->seed([$map['standard']['class'], $map['builder']['class']]);
            foreach (['standard', 'builder'] as $kind) {
                $uuid = $map[$kind]['old_saved_test_uuid'];
                SavedGrammarTest::where('uuid', $uuid)->firstOrFail()->update(['filters' => Quality::metadataPatches()[$uuid]['after']['filters']]);
            }
            $category = PageCategory::find(8) ?? PageCategory::forceCreate(['id' => 8, 'title' => 'Verb to Be', 'slug' => 'verb-to-be']);
            $prompt = Quality::originalDefinition($topic, 'standard')['saved_test']['filters']['prompt_generator']['theory_page'];
            $page = Page::forceCreate(['page_category_id' => $category->id, 'slug' => $prompt['slug'], 'title' => $prompt['title'], 'seeder' => $prompt['page_seeder_class']]);
            app()->setLocale('uk');
            $mixed = app(TheoryPagePromptLinkedTestsService::class)->buildForPage($page)
                ->first(fn ($test) => (bool) data_get($test->filters, '__meta.theory_page_mixed_all_levels_test'));
            $this->assertNotNull($mixed, $topic);
            $this->assertEqualsCanonicalizing([$map['standard']['class'], $map['builder']['class']], $mixed->filters['seeder_classes']);
            $selected = app(GrammarTestFilterService::class)->questionsFromFilters($mixed->filters);
            $this->assertCount(54, $selected);
            $this->assertSame([], array_intersect($oldIds, $selected->pluck('id')->all()));
            $this->assertNotNull(Quality::stateNamespace($mixed->filters, 'uk', $mixed->uuid));
            $this->assertSame($originalAttributes, $sonate->fresh()->getAttributes(), 'Sonate standalone test must be unchanged.');
            app()->setLocale('en');
            $english = app(TheoryPagePromptLinkedTestsService::class)->buildForPage($page)
                ->first(fn ($test) => (bool) data_get($test->filters, '__meta.theory_page_mixed_all_levels_test'));
            $this->assertContains($extra['class'], $english->filters['seeder_classes']);
            $this->assertNotContains($map['standard']['class'], $english->filters['seeder_classes']);
            $this->assertSame($originalAttributes, $sonate->fresh()->getAttributes());
        }
    }

    public function test_locale_projection_is_finite_copy_only_and_retains_foreign_metadata(): void
    {
        foreach (Quality::metadataPatches() as $uuid => $patch) {
            $before = $patch['before']['filters']; $after = $patch['after']['filters'];
            foreach (['en', 'pl'] as $locale) {
                $this->assertSame($before, Quality::filtersForLocale($uuid, $after, $locale));
                $extended = $after;
                $extended['unrelated_selection'] = ['keep'];
                $extended['__meta']['foreign_revision'] = 'keep';
                $extended['__meta']['theory_page_mixed_locale_seeder_overrides']['pl'] = ['source' => 'replacement'];
                $extended['__meta']['theory_page_mixed_locale_seeder_overrides']['uk']['foreign-bank'] = 'foreign-new';
                $expected = $before;
                $expected['unrelated_selection'] = ['keep'];
                $expected['__meta']['foreign_revision'] = 'keep';
                $expected['__meta']['theory_page_mixed_locale_seeder_overrides']['pl'] = ['source' => 'replacement'];
                $expected['__meta']['theory_page_mixed_locale_seeder_overrides']['uk']['foreign-bank'] = 'foreign-new';
                $this->assertEquals($expected, Quality::filtersForLocale($uuid, $extended, $locale));
                foreach (['seeder_classes', 'num_questions', 'theory_page_mixed_type_profile',
                    'theory_page_mixed_interleave_question_types', '__meta.mode',
                    '__meta.verb_to_be_quality_revision', '__meta.verb_to_be_quality_topic'] as $path) {
                    $stale = $after; Arr::set($stale, $path, 'foreign');
                    $this->assertSame($stale, Quality::filtersForLocale($uuid, $stale, $locale));
                }
                $unbound = $after; Arr::forget($unbound, '__meta.theory_page_mixed_locale_seeder_overrides.uk');
                $this->assertSame($unbound, Quality::filtersForLocale($uuid, $unbound, $locale));
                $course = $after; $course['course_slug'] = 'polyglot-course-td';
                $this->assertSame($course, Quality::filtersForLocale($uuid, $course, $locale));
                $this->assertSame($after, Quality::filtersForLocale('foreign-uuid', $after, $locale));
                $this->assertSame($before, Quality::filtersForLocale($uuid, $before, $locale));
            }
            $this->assertSame($after, Quality::filtersForLocale($uuid, $after, 'uk'));
            $this->assertSame($after, Quality::filtersForLocale($uuid, $after, 'ua'));
        }
    }

    public function test_real_old_saved_routes_use_new_uk_banks_but_original_en_pl_links_without_persisting_projection(): void
    {
        $map = Quality::mapping('present');
        app()->setLocale('en');
        $this->seed([$map['standard']['old_class'], $map['builder']['old_class']]);
        $oldUuids = [];
        foreach (['standard', 'builder'] as $kind) {
            $saved = SavedGrammarTest::where('uuid', $map[$kind]['old_saved_test_uuid'])->firstOrFail();
            $oldUuids[$kind] = $saved->questionLinks->sortBy('position')->pluck('question_uuid')->all();
        }
        $this->seed([$map['standard']['class'], $map['builder']['class']]);
        $patches = Quality::metadataPatches();
        foreach (['standard', 'builder'] as $kind) {
            $uuid = $map[$kind]['old_saved_test_uuid'];
            SavedGrammarTest::where('uuid', $uuid)->firstOrFail()->update(['filters' => $patches[$uuid]['after']['filters']]);
        }
        $category = PageCategory::forceCreate(['id' => 8, 'title' => 'Verb to Be', 'slug' => 'verb-to-be']);
        $prompt = $patches[$map['standard']['old_saved_test_uuid']]['before']['filters']['prompt_generator']['theory_page'];
        $page = Page::forceCreate(['page_category_id' => $category->id, 'slug' => $prompt['slug'],
            'title' => $prompt['title'], 'seeder' => $prompt['page_seeder_class']]);

        foreach (['uk', 'en', 'pl'] as $locale) {
            app()->setLocale($locale);
            foreach (['standard', 'builder'] as $kind) {
                $uuid = $map[$kind]['old_saved_test_uuid']; $patch = $patches[$uuid];
                $resolved = app(SavedTestResolver::class)->resolve($patch['before']['slug']);
                if ($locale === 'uk') {
                    $this->assertCount($kind === 'standard' ? 36 : 18, $resolved->questionUuids);
                    $this->assertTrue($resolved->isFilterBased);
                    $this->assertSame([$map[$kind]['class']], $resolved->model->filters['seeder_classes']);
                    $this->assertSame([$map[$kind]['class']], $resolved->preloadedQuestions->pluck('seeder')->unique()->values()->all());
                } else {
                    $this->assertSame($oldUuids[$kind], $resolved->questionUuids->all());
                    $this->assertFalse($resolved->isFilterBased);
                    $this->assertSame([$map[$kind]['old_class']], $resolved->model->filters['seeder_classes']);
                }
                $this->assertSame($patch['after']['filters'], SavedGrammarTest::where('uuid', $uuid)->firstOrFail()->filters);
            }
            $linked = app(TheoryPagePromptLinkedTestsService::class)->findForPage($page);
            $this->assertCount(2, $linked);
            $this->assertEqualsCanonicalizing(array_map(fn ($kind) => $map[$kind][$locale === 'uk' ? 'class' : 'old_class'], ['standard', 'builder']),
                $linked->flatMap(fn ($test) => $test->filters['seeder_classes'])->all());
            // Use the actual service shape: its aggregation flag is metadata-only.
            $mixed = app(TheoryPagePromptLinkedTestsService::class)->buildForPage($page)
                ->first(fn ($test) => (bool) data_get($test->filters, '__meta.theory_page_mixed_all_levels_test'));
            $this->assertNotNull($mixed);
            $this->assertArrayNotHasKey('aggregated_theory_page_test', $mixed->filters);
            $this->assertTrue($mixed->filters['__meta']['aggregated_theory_page_test']);
            $namespace = Quality::stateNamespace($mixed->filters, $locale, $mixed->uuid);
            $this->assertNotNull($namespace, 'The real page-service virtual mixed context must opt into the finite namespace.');
            $publicUrl = ($locale === 'uk' ? '' : '/'.$locale).'/test/'.$mixed->getAttribute('public_slug');
            $rendered = $this->get($publicUrl)->assertOk();
            $this->assertRenderedNamespace($rendered->getContent(), $namespace);
        }
        app()->setLocale('uk');
        $builder = $patches[$map['builder']['old_saved_test_uuid']]['before'];
        $payload = $this->getJson('/test/'.$builder['slug'].'/questions')->assertOk()->json('questions');
        $this->assertCount(18, $payload, 'Retained builder tags must resolve the new bank, not an empty route.');
        $this->assertSame(['4'], collect($payload)->pluck('type')->map(fn ($type) => (string) $type)->unique()->values()->all());
        foreach (['en', 'pl'] as $locale) {
            app()->setLocale($locale);
            $legacyPayload = $this->getJson('/'.$locale.'/test/'.$builder['slug'].'/questions')->assertOk()->json('questions');
            $this->assertEqualsCanonicalizing($oldUuids['builder'], array_column($legacyPayload, 'uuid'));
        }
    }

    public function test_all_six_verb_to_be_category_levels_select_nine_per_page_and_interleave_all_three_formats(): void
    {
        app()->setLocale('uk');
        $category = PageCategory::forceCreate(['id' => 8, 'title' => 'Verb to Be', 'slug' => 'verb-to-be']);
        $patches = Quality::metadataPatches(); $pages = collect(); $extraSavedRows = [];
        foreach (array_keys(Quality::TOPICS) as $topic) {
            $map = Quality::mapping($topic);
            foreach ($map['additional_originals'] ?? [] as $extra) {
                $this->seed($extra['class']);
                $source = json_decode(file_get_contents(dirname(__DIR__, 2).'/'.$extra['path']), true, flags: JSON_THROW_ON_ERROR);
                $saved = SavedGrammarTest::where('uuid', $source['saved_test']['uuid'])->firstOrFail();
                $extraSavedRows[$saved->uuid] = $saved->getAttributes();
            }
            $this->seed([$map['standard']['class'], $map['builder']['class']]);
            $prompt = $patches[$map['standard']['old_saved_test_uuid']]['before']['filters']['prompt_generator']['theory_page'];
            $pages->push(Page::forceCreate(['page_category_id' => $category->id, 'slug' => $prompt['slug'],
                'title' => $prompt['title'], 'seeder' => $prompt['page_seeder_class']]));
            foreach (['standard', 'builder'] as $kind) {
                $data = $patches[$map[$kind]['old_saved_test_uuid']]['after'];
                SavedGrammarTest::create($data);
            }
        }
        $service = app(TheoryPagePromptLinkedTestsService::class);
        $tests = $service->buildForCategory($category, $pages);
        $this->assertCount(6, $tests);
        $this->assertSame(Quality::LEVELS, $tests->map(fn ($test) => $test->filters['levels'][0])->all());
        foreach ($tests as $test) {
            $filters = $test->filters;
            $this->assertSame(54, $filters['num_questions']);
            $this->assertSame(9, $filters['theory_category_questions_per_page']);
            $this->assertSame(Quality::REVISION, $filters['theory_page_mixed_type_profile']);
            $this->assertTrue($filters['include_ai_v2'], 'Flag-two standard banks must remain selectable even when a builder supplied the base filters.');
            $this->assertCount(12, $filters['seeder_classes'], 'Extra Sonate sources must resolve to the same six exact bank pairs.');
            $selected = app(GrammarTestFilterService::class)->questionsFromFilters($filters);
            $this->assertCount(54, $selected);
            $this->assertSame(36, $selected->where('type', '0')->count());
            $this->assertSame(18, $selected->where('type', '4')->count());
            $payload = $selected->map(function (Question $row): array {
                $source = Quality::questions()[$row->uuid];
                return Quality::decorate(['uuid' => $row->uuid, 'id' => $row->id, 'question' => $row->question,
                    'level' => $row->level, 'type' => $row->type,
                    'answer_map' => array_map(fn ($marker) => $marker['answer'], $source['markers'])]);
            })->all();
            $rendered = Factory::addToMixedTheoryTest($payload, $filters);
            $counts = ['gap' => 0, 'compose' => 0, 'order' => 0]; $previous = null; $perSource = [];
            foreach ($rendered as $row) {
                $kind = ($row['presentation'] ?? '') === Factory::PRESENTATION ? 'order' : ((string) $row['type'] === '4' ? 'compose' : 'gap');
                $this->assertNotSame($previous, $kind); $counts[$kind]++; $previous = $kind;
                $source = Quality::questions()[$row['uuid']]['source'];
                $perSource[$source] ??= ['gap' => 0, 'compose' => 0, 'order' => 0];
                $perSource[$source][$kind]++;
                if ($kind === 'order') { $this->assertTrue($row['reorder_manual_input']); }
            }
            $this->assertSame(['gap' => 18, 'compose' => 18, 'order' => 18], $counts);
            $this->assertCount(6, $perSource);
            foreach ($perSource as $sourceCounts) { $this->assertSame(['gap' => 3, 'compose' => 3, 'order' => 3], $sourceCounts); }
            $url = '/test/'.$test->slug.'/questions?'.http_build_query(['filters' => base64_encode(json_encode($filters))]);
            $httpPayload = $this->getJson($url)->assertOk()->json('questions');
            $this->assertCount(54, $httpPayload);
            $httpCounts = ['gap' => 0, 'compose' => 0, 'order' => 0]; $previous = null;
            foreach ($httpPayload as $row) {
                $kind = ($row['presentation'] ?? '') === Factory::PRESENTATION ? 'order' : ((string) $row['type'] === '4' ? 'compose' : 'gap');
                $this->assertNotSame($previous, $kind); $httpCounts[$kind]++; $previous = $kind;
                $this->assertSame($filters['levels'][0], $row['level']);
                $this->assertArrayHasKey('psp_content_revision', $row);
            }
            $this->assertSame(['gap' => 18, 'compose' => 18, 'order' => 18], $httpCounts);
            $namespace = Quality::stateNamespace($filters, 'uk', $test->uuid);
            $this->assertNotNull($namespace);
            $pageUrl = '/test/'.$test->slug.'?'.http_build_query(['filters' => base64_encode(json_encode($filters))]);
            $rendered = $this->followingRedirects()->get($pageUrl)->assertOk();
            $this->assertRenderedNamespace($rendered->getContent(), $namespace);
        }
        $unrelated = clone $category; $unrelated->id = 9;
        foreach ($service->buildForCategory($unrelated, $pages) as $test) {
            $this->assertSame(24, $test->filters['num_questions']);
            $this->assertSame(4, $test->filters['theory_category_questions_per_page']);
            $this->assertNotSame(Quality::REVISION, $test->filters['theory_page_mixed_type_profile'] ?? '');
            $this->assertCount(24, app(GrammarTestFilterService::class)->questionsFromFilters($test->filters));
        }
        $changed = SavedGrammarTest::where('uuid', Quality::mapping('there')['standard']['old_saved_test_uuid'])->firstOrFail();
        $filters = $changed->filters;
        $filters['theory_page_mixed_type_profile'] = 'verb-to-be-v2'; $changed->update(['filters' => $filters]);
        foreach ($service->buildForCategory($category, $pages) as $test) {
            $this->assertSame(4, $test->filters['theory_category_questions_per_page']);
        }
        foreach ($extraSavedRows as $uuid => $attributes) {
            $this->assertSame($attributes, SavedGrammarTest::where('uuid', $uuid)->firstOrFail()->getAttributes());
        }
    }

    private function assertRenderedNamespace(string $html, string $namespace): void
    {
        $this->assertSame(1, preg_match('/storageKey:\s*("(?:\\\\.|[^"\\\\])*")/', $html, $key));
        $storageKey = json_decode($key[1], true, flags: JSON_THROW_ON_ERROR);
        $this->assertStringEndsWith(':'.$namespace, $storageKey);
        $this->assertSame(1, preg_match('/storageKeys:\s*(\[[^\r\n]+\])/', $html, $keys));
        $this->assertSame([$storageKey], json_decode($keys[1], true, flags: JSON_THROW_ON_ERROR));
    }
}


