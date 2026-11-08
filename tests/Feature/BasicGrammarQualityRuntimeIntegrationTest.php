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
use App\Support\BasicGrammarPracticeQuality as Quality;
use Illuminate\Support\Arr;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Real saved-test/filter integration in isolated SQLite, never the working database. */
class BasicGrammarQualityRuntimeIntegrationTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        config(['coming-soon.enabled' => false, 'tests.tech_info_enabled' => false]);
    }

    public function test_actual_question_endpoint_retains_reviewed_compose_groups_and_natural_contractions(): void
    {
        $fixtures = [];
        foreach ([['have-got', 'A1-c01', "My father's"], ['imperatives', 'A2-c02', "Let's"]] as [$topic, $id, $contracted]) {
            $map = Quality::mapping($topic); app()->setLocale('en');
            $this->seed([$map['builder']['old_class'], $map['builder']['class']]);
            $uuid = $map['builder']['old_saved_test_uuid']; $patch = Quality::metadataPatches()[$uuid];
            SavedGrammarTest::where('uuid', $uuid)->firstOrFail()->update(['filters' => $patch['after']['filters']]);
            app()->setLocale('uk');
            $payloads = $this->getJson('/test/'.$patch['before']['slug'].'/questions')->assertOk()->json('questions');
            $source = Quality::questions()[Quality::questionUuid($topic, $id)];
            $choices = array_column($source['markers'], 'answer'); $choices[0] = $contracted;
            $payload = collect($payloads)->firstWhere('uuid', $source['uuid']);
            $this->assertNotNull($payload);
            $this->assertSame(array_column($source['markers'], 'answer'), $payload['answers']);
            $this->assertSame(array_keys($source['markers']), $payload['markers']);
            $this->assertCount(count($choices), $payload['answers']);
            $this->assertArrayHasKey('compose_content_revision', $payload);
            $fixtures[] = ['payload' => $payload, 'choices' => $choices];
        }
        $script = <<<'JS'
const fs=require('node:fs'),vm=require('node:vm'),assert=require('node:assert/strict'),path=require('node:path');
const fixtures=JSON.parse(fs.readFileSync(0,'utf8')),variants=require(path.join(process.cwd(),'tests/Browser/load-answer-variants.cjs'));
const blade=fs.readFileSync('resources/views/components/saved-test-js-helpers.blade.php','utf8');
const start=blade.indexOf('function canonicalTestAnswer('),end=blade.indexOf('function isPassivePhraseGapQuestion(',start);
const context=vm.createContext({EnglishAnswerVariants:variants});vm.runInContext(blade.slice(start,end),context,{timeout:1000});
for(const {payload,choices} of fixtures){const q={...payload,chosen:[]};for(let slot=0;slot<choices.length;slot++){
 assert.equal(context.testAnswerMatches(q,slot,choices[slot]),true,q.uuid+' contraction/group slot '+slot);q.chosen[slot]=choices[slot];}}
process.stdout.write('actual-http-payload-contractions-pass');
JS;
        $process = new \Symfony\Component\Process\Process([getenv('NODE_BINARY') ?: 'node', '-e', $script], dirname(__DIR__, 2), timeout: 30);
        $process->setInput(json_encode($fixtures, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        $process->run();
        $this->assertSame(0, $process->getExitCode(), $process->getOutput().$process->getErrorOutput());
        $this->assertSame('actual-http-payload-contractions-pass', $process->getOutput());
    }

    public function test_extra_sonate_page_banks_are_overridden_only_in_uk_mixed_without_rewriting_their_saved_rows(): void
    {
        foreach (['parts-of-speech', 'conjunctions'] as $topic) {
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
            $category = PageCategory::find(2) ?? PageCategory::forceCreate(['id' => 2, 'title' => 'Basic Grammar', 'slug' => 'basic-grammar']);
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
                    '__meta.basic_grammar_quality_revision', '__meta.basic_grammar_quality_topic'] as $path) {
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
        $map = Quality::mapping('parts-of-speech');
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
        $category = PageCategory::forceCreate(['id' => 2, 'title' => 'Basic Grammar', 'slug' => 'basic-grammar']);
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

    private function assertRenderedNamespace(string $html, string $namespace): void
    {
        $this->assertSame(1, preg_match('/storageKey:\s*("(?:\\\\.|[^"\\\\])*")/', $html, $key));
        $storageKey = json_decode($key[1], true, flags: JSON_THROW_ON_ERROR);
        $this->assertStringEndsWith(':'.$namespace, $storageKey);
        $this->assertSame(1, preg_match('/storageKeys:\s*(\[[^\r\n]+\])/', $html, $keys));
        $this->assertSame([$storageKey], json_decode($keys[1], true, flags: JSON_THROW_ON_ERROR));
    }
}




