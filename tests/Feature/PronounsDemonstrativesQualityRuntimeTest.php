<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\SavedGrammarTest;
use App\Services\GrammarTestFilterService;
use App\Services\TheoryPagePromptLinkedTestsService;
use App\Support\PronounsDemonstrativesPracticeQuality as Quality;
use App\Support\SentenceReorderQuestionFactory as Factory;
use App\Support\VirtualTestRegistry;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Exercises the real saved-test service/controller in isolated SQLite only. */
final class PronounsDemonstrativesQualityRuntimeTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        config(['coming-soon.enabled' => false, 'tests.tech_info_enabled' => false, 'cache.default' => 'array']);
    }

    private function setupTopic(string $topic): Page
    {
        $map = Quality::mapping($topic);
        app()->setLocale('uk');
        foreach (['standard', 'builder'] as $kind) { $this->seed($map[$kind]['class']); }
        foreach (Quality::metadataPatches() as $patch) {
            if ($patch['topic'] !== $topic) { continue; }
            SavedGrammarTest::forceCreate(array_intersect_key($patch['after'], array_flip(['uuid', 'slug', 'name', 'description', 'filters'])));
        }
        $category = PageCategory::find(20) ?? PageCategory::forceCreate(['id' => 20, 'title' => 'Займенники та вказівні слова', 'slug' => Quality::CATEGORY_SLUG]);
        $prompt = Quality::originalDefinition($topic, 'standard')['saved_test']['filters']['prompt_generator']['theory_page'];
        return Page::forceCreate(['page_category_id' => $category->id, 'slug' => $prompt['slug'], 'title' => $prompt['title'], 'seeder' => $prompt['page_seeder_class']]);
    }

    public function test_all_ten_actual_mixed_endpoints_have_balanced_interleaved_formats_and_complete_hints(): void
    {
        foreach (array_keys(Quality::TOPICS) as $topic) {
            $page = $this->setupTopic($topic);
            $mixed = app(TheoryPagePromptLinkedTestsService::class)->buildForPage($page)
                ->first(fn ($test) => (bool) data_get($test->filters, '__meta.theory_page_mixed_all_levels_test'));
            $this->assertNotNull($mixed, $topic);
            $this->assertSame(Quality::REVISION, $mixed->filters['theory_page_mixed_type_profile']);
            $this->assertSame(54, $mixed->filters['num_questions']);
            $selected = app(GrammarTestFilterService::class)->questionsFromFilters($mixed->filters);
            $this->assertCount(54, $selected, $topic);
            $url = '/test/'.$mixed->getAttribute('public_slug');
            $payloads = $this->getJson($url.'/questions')->assertOk()->json('questions');
            $this->assertCount(54, $payloads, $url);
            $this->assertCount(54, array_unique(array_column($payloads, 'uuid')));
            $previous = null;
            foreach (collect($payloads)->groupBy('level') as $level => $rows) {
                $counts = ['gap' => 0, 'compose' => 0, 'order' => 0];
                foreach ($rows as $q) {
                    $kind = ($q['presentation'] ?? '') === Factory::PRESENTATION ? 'order' : ((string) $q['type'] === '4' ? 'compose' : 'gap');
                    ++$counts[$kind]; $this->assertNotSame($previous, $kind, $topic.'/'.$level); $previous = $kind;
                    $source = Quality::questions()[$q['uuid']];
                    $this->assertSame(array_column($source['markers'], 'answer'), $q['answers']);
                    $this->assertArrayHasKey('psp_content_revision', $q);
                    if ($kind === 'gap') { $this->assertTrue($q['passive_gap_phrase_input']); $this->assertTrue($q['passive_phrase_word_inputs']); }
                    if ($kind === 'order') {
                        $this->assertTrue($q['reorder_manual_input']);
                        $this->assertSame($source['target_text'], $q['reorder_answer']);
                        $this->assertNotSame($q['reorder_groups'], $q['reorder_tokens']);
                        $this->assertEqualsCanonicalizing($q['reorder_groups'], $q['reorder_tokens']);
                        $this->assertNotContains('-', $q['reorder_tokens']);
                    }
                }
                $this->assertSame(['gap' => 3, 'compose' => 3, 'order' => 3], $counts, $topic.'/'.$level);
            }
            $this->assertSame(Quality::LEVELS, collect($payloads)->pluck('level')->unique()->values()->all());
        }
    }

    public function test_all_twenty_one_saved_descriptors_resolve_their_new_owned_bank_without_long_options(): void
    {
        foreach (array_keys(Quality::TOPICS) as $topic) { $this->setupTopic($topic); }
        foreach (Quality::metadataPatches() as $uuid => $patch) {
            $questions = $this->getJson('/test/'.$patch['before']['slug'].'/questions')->assertOk()->json('questions');
            $this->assertCount($patch['kind'] === 'standard' ? 36 : 18, $questions, $uuid);
            foreach ($questions as $q) {
                $this->assertArrayHasKey($q['uuid'], Quality::questions(), $uuid);
                $this->assertSame($patch['after']['filters'], SavedGrammarTest::where('uuid', $uuid)->firstOrFail()->filters);
                foreach ($q['answer_synonyms_by_marker'] ?? [] as $aliases) {
                    foreach ($aliases as $alias) { $this->assertLessThan(100, mb_strlen($alias)); }
                }
            }
        }
    }

    public function test_compact_profile_is_finite_and_cannot_change_an_unrelated_test(): void
    {
        $this->assertTrue(Factory::isCompactQualityProfile(Quality::REVISION));
        $this->assertFalse(Factory::isPassiveQualityProfile(Quality::REVISION));
        $this->assertFalse(Factory::isCompactQualityProfile(Quality::REVISION.'-unknown'));
        $payload = ['uuid' => 'foreign', 'question' => 'A foreign {a1} sentence.', 'type' => '0', 'answer_map' => ['a1' => 'test'],
            'chosen' => ['saved'], 'done' => false, 'attemptsBySlot' => [2], 'manualInputsBySlot' => ['draft']];
        $this->assertSame($payload, Quality::decorate($payload));
        $this->assertSame([$payload], Factory::addToMixedTheoryTest([$payload], ['theory_page_mixed_type_profile' => Quality::REVISION]));
    }

    public function test_all_six_category_levels_use_only_owned_balanced_page_pairs(): void
    {
        $pages = collect();
        foreach (array_keys(Quality::TOPICS) as $topic) { $pages->push($this->setupTopic($topic)); }
        $category = PageCategory::findOrFail(Quality::CATEGORY_ID);
        $tests = app(TheoryPagePromptLinkedTestsService::class)->buildForCategory($category, new \Illuminate\Database\Eloquent\Collection($pages->all()));
        $this->assertCount(6, $tests);
        foreach ($tests as $test) {
            $this->assertSame(Quality::REVISION, $test->filters['theory_page_mixed_type_profile']);
            // Category cards register the transient filter hash before linking;
            // unlike static page tests their provisional model has no public_slug.
            $slug = VirtualTestRegistry::register($test->slug, $test->name, $test->filters, $test->description, $test->totalQuestionsAvailable);
            $rows = $this->getJson('/test/'.$slug.'/questions')->assertOk()->json('questions');
            $this->assertCount(90, $rows);
            $counts = ['gap' => 0, 'compose' => 0, 'order' => 0]; $previous = null;
            foreach ($rows as $row) {
                $kind = ($row['presentation'] ?? '') === Factory::PRESENTATION ? 'order' : ((string) $row['type'] === '4' ? 'compose' : 'gap');
                ++$counts[$kind]; $this->assertNotSame($previous, $kind); $previous = $kind;
                $this->assertArrayHasKey($row['uuid'], Quality::questions());
            }
            $this->assertSame(['gap' => 30, 'compose' => 30, 'order' => 30], $counts);
            $this->assertCount(1, array_unique(array_column($rows, 'level')));
        }
    }
}
