<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\SavedGrammarTest;
use App\Services\GrammarTestFilterService;
use App\Services\TheoryPagePromptLinkedTestsService;
use App\Support\NounsArticlesQuantityPracticeQuality as Quality;
use App\Support\SentenceReorderQuestionFactory as Factory;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Exercises the real saved-test service/controller in isolated SQLite only. */
final class NounsArticlesQuantityQualityRuntimeTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        config(['coming-soon.enabled' => false, 'tests.tech_info_enabled' => false]);
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
        $category = PageCategory::find(16) ?? PageCategory::forceCreate(['id' => 16, 'title' => 'Іменники, артиклі й кількість', 'slug' => Quality::CATEGORY_SLUG]);
        $prompt = Quality::originalDefinition($topic, 'standard')['saved_test']['filters']['prompt_generator']['theory_page'];
        return Page::forceCreate(['page_category_id' => $category->id, 'slug' => $prompt['slug'], 'title' => $prompt['title'], 'seeder' => $prompt['page_seeder_class']]);
    }

    public function test_all_twelve_actual_mixed_endpoints_have_balanced_interleaved_formats_and_complete_hints(): void
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

    public function test_all_thirty_four_saved_descriptors_resolve_their_new_owned_bank_without_long_options(): void
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

    public function test_omission_reconstruction_requires_exact_full_source_binding_and_never_changes_other_profiles(): void
    {
        $this->setupTopic('zero-article');
        $omission = collect(Quality::questions())->first(fn ($q) => (string) $q['type'] === '0' && $q['markers']['a1']['answer'] === '-');
        $raw = ['uuid' => $omission['uuid'], 'question' => $omission['question'], 'type' => '0', 'answer_map' => ['a1' => '-']];
        $this->assertNull(Quality::reorderSentence($raw));
        $decorated = Quality::decorate($raw);
        $this->assertSame($omission['target_text'], Quality::reorderSentence($decorated));
        $this->assertSame($omission['accepted_target_texts'], $decorated['reorder_accepted_answers']);
        foreach (['uuid' => 'foreign', 'question' => $raw['question'].' changed', 'answer_map' => ['a1' => 'the'], 'psp_content_revision' => str_repeat('0', 64)] as $key => $value) {
            $this->assertNull(Quality::reorderSentence(array_replace($decorated, [$key => $value])), $key);
        }
        $this->assertTrue(Factory::isCompactQualityProfile(Quality::REVISION));
        $this->assertFalse(Factory::isPassiveQualityProfile(Quality::REVISION));
        $this->assertFalse(Factory::isCompactQualityProfile(Quality::REVISION.'-unknown'));
        $this->assertSame([$decorated], Factory::addToMixedTheoryTest([$decorated], ['theory_page_mixed_type_profile' => Quality::REVISION]));
    }
}
