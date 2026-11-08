<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\SavedGrammarTest;
use App\Services\GrammarTestFilterService;
use App\Services\TheoryPagePromptLinkedTestsService;
use App\Support\FutureContinuousComparisonPracticeQuality as Quality;
use App\Support\SentenceReorderQuestionFactory as Factory;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Real service/controllers, isolated SQLite schema only. */
final class FutureContinuousComparisonQualityRuntimeTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        config(['coming-soon.enabled' => false, 'tests.tech_info_enabled' => false, 'cache.default' => 'array']);
    }

    private function setupTopic(): Page
    {
        app()->setLocale('uk');
        $map = Quality::mapping('comparison');
        foreach (['standard', 'builder'] as $kind) { $this->seed($map[$kind]['class']); }
        foreach (Quality::metadataPatches() as $patch) {
            SavedGrammarTest::forceCreate(array_intersect_key($patch['after'], array_flip(['uuid', 'slug', 'name', 'description', 'filters'])));
        }
        $category = PageCategory::forceCreate(['id' => 9001, 'title' => 'Майбутні форми', 'slug' => 'maibutni-formy']);
        $prompt = Quality::originalDefinition('comparison', 'standard')['saved_test']['filters']['prompt_generator']['theory_page'];
        return Page::forceCreate(['page_category_id' => $category->id, 'slug' => $prompt['slug'], 'title' => $prompt['title'], 'seeder' => $prompt['page_seeder_class']]);
    }

    public function test_actual_page_mixed_endpoint_interleaves_all_three_formats_on_all_six_levels(): void
    {
        $page = $this->setupTopic();
        $mixed = app(TheoryPagePromptLinkedTestsService::class)->buildForPage($page)
            ->first(fn ($test) => (bool) data_get($test->filters, '__meta.theory_page_mixed_all_levels_test'));
        self::assertNotNull($mixed);
        self::assertSame(Quality::REVISION, $mixed->filters['theory_page_mixed_type_profile']);
        self::assertSame(54, $mixed->filters['num_questions']);
        self::assertCount(54, app(GrammarTestFilterService::class)->questionsFromFilters($mixed->filters));
        $url = '/test/'.$mixed->getAttribute('public_slug');
        $payloads = $this->getJson($url.'/questions')->assertOk()->json('questions');
        self::assertCount(54, $payloads);
        self::assertCount(54, array_unique(array_column($payloads, 'uuid')));
        self::assertSame(Quality::LEVELS, collect($payloads)->pluck('level')->unique()->values()->all());
        $previous = null;
        foreach (collect($payloads)->groupBy('level') as $level => $rows) {
            $counts = ['gap' => 0, 'compose' => 0, 'order' => 0];
            foreach ($rows as $q) {
                $kind = ($q['presentation'] ?? '') === Factory::PRESENTATION ? 'order' : ((string) $q['type'] === '4' ? 'compose' : 'gap');
                ++$counts[$kind]; self::assertNotSame($previous, $kind, $level); $previous = $kind;
                $source = Quality::questions()[$q['uuid']];
                self::assertSame(array_column($source['markers'], 'answer'), $q['answers']);
                self::assertArrayHasKey('psp_content_revision', $q);
                if ($kind === 'gap') {
                    self::assertTrue($q['passive_gap_phrase_input']); self::assertTrue($q['passive_phrase_word_inputs']);
                    self::assertLessThanOrEqual(4, count(preg_split('/\s+/u', $q['answers'][0])));
                }
                if ($kind === 'order') {
                    self::assertTrue($q['reorder_manual_input']);
                    self::assertSame($source['target_text'], $q['reorder_answer']);
                    self::assertNotSame($q['reorder_groups'], $q['reorder_tokens']);
                    self::assertEqualsCanonicalizing($q['reorder_groups'], $q['reorder_tokens']);
                }
                self::assertNotEmpty($q['verb_hints']);
                foreach ($q['verb_hints'] as $hint) {
                    self::assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $hint);
                    self::assertDoesNotMatchRegularExpression('/[A-Za-z]|<[^>]*>|\{a\d+\}/u', $hint);
                }
            }
            self::assertSame(['gap' => 3, 'compose' => 3, 'order' => 3], $counts, $level);
        }
    }

    public function test_two_saved_test_urls_keep_identity_and_resolve_only_their_new_banks(): void
    {
        $this->setupTopic();
        foreach (Quality::metadataPatches() as $uuid => $patch) {
            $rows = $this->getJson('/test/'.$patch['before']['slug'].'/questions')->assertOk()->json('questions');
            self::assertCount($patch['kind'] === 'standard' ? 36 : 18, $rows, $uuid);
            self::assertSame($patch['after']['filters'], SavedGrammarTest::where('uuid', $uuid)->firstOrFail()->filters);
            foreach ($rows as $q) {
                self::assertArrayHasKey($q['uuid'], Quality::questions());
                if ((string) $q['type'] !== '4') { continue; }
                foreach ($q['options_by_marker'] as $options) {
                    self::assertCount(4, $options);
                    foreach ($options as $option) { self::assertLessThanOrEqual(3, count(preg_split('/\s+/u', $option))); }
                }
            }
        }
    }

    public function test_parent_future_category_and_sibling_owner_cannot_inherit_the_page_profile(): void
    {
        $page = $this->setupTopic();
        $tests = app(TheoryPagePromptLinkedTestsService::class)->buildForCategory(PageCategory::findOrFail($page->page_category_id), new \Illuminate\Database\Eloquent\Collection([$page]));
        foreach ($tests as $test) { self::assertNotSame(Quality::REVISION, $test->filters['theory_page_mixed_type_profile'] ?? null); }
        $map = Quality::mapping('comparison');
        $filters = ['seeder_classes' => [$map['standard']['class'], $map['builder']['class']],
            'theory_page_mixed_type_profile' => Quality::REVISION, 'aggregated_theory_page_test' => true,
            'prompt_generator' => ['theory_page' => ['page_seeder_class' => 'Database\\Seeders\\Page_V3\\FutureForms\\FuturePerfect\\FuturePerfectFormsTheorySeeder']],
            '__meta' => ['mode' => 'filters', 'theory_page_mixed_polyglot_test' => true]];
        self::assertNull(Quality::stateNamespace($filters, 'uk'));
        self::assertFalse(Quality::testContextNeedsRefresh([], $filters, 'uk'));
    }
}
