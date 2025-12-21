<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Words\PublicTest;
use App\Models\Tag;
use App\Models\Word;
use App\Models\Translate;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class PublicWordsTestTest extends TestCase
{
    private string $testId;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Generate unique ID for this test run
        $this->testId = Str::random(8);

        // Run only the required migrations for words (fresh for each test)
        $migrations = [
            '2025_07_18_182347_create_words_table.php',
            '2025_07_18_182357_create_translates_table.php',
            '2025_07_30_000001_create_tags_table.php',
            '2025_07_30_000002_create_tag_word_table.php',
            '2025_09_05_195812_add_type_to_words_table.php',
        ];

        foreach ($migrations as $file) {
            Artisan::call('migrate', ['--path' => 'database/migrations/' . $file, '--force' => true]);
        }
        
        // Clear existing data to ensure clean state
        DB::table('tag_word')->delete();
        DB::table('translates')->delete();
        DB::table('words')->delete();
        DB::table('tags')->delete();

        // Clear session before each test
        session()->forget(['words_selected_tags', 'words_test_stats', 'words_queue', 'words_total_count']);
    }

    /**
     * Create test words with translations using unique names.
     */
    private function createTestWords(int $count = 5, ?Tag $tag = null): array
    {
        $words = [];
        // Use current word count to ensure uniqueness across multiple calls
        $startIndex = Word::count() + 1;
        
        for ($i = 0; $i < $count; $i++) {
            $idx = $startIndex + $i;
            $word = Word::create([
                'word' => "en_{$this->testId}_{$idx}",
                'type' => 'noun',
            ]);
            Translate::create([
                'word_id' => $word->id,
                'lang' => 'uk',
                'translation' => "uk_{$this->testId}_{$idx}",
            ]);
            if ($tag) {
                $word->tags()->attach($tag);
            }
            $words[] = $word;
        }
        return $words;
    }

    public function test_public_words_test_page_renders(): void
    {
        $this->createTestWords(3);

        $response = $this->get(route('words.public.test'));

        $response->assertStatus(200);
        // The component should render with question content
        $response->assertSee('Тест слів');
    }

    public function test_uses_engram_layout(): void
    {
        $this->createTestWords(3);

        $response = $this->get(route('words.public.test'));

        $response->assertStatus(200);
        // The engram layout has this specific header
        $response->assertSee('Gramlyze');
    }

    public function test_after_submit_answer_loads_next_question_or_completes(): void
    {
        // Create only 2 words for quick test completion
        $words = $this->createTestWords(2);

        // Get the word translations that were created
        $word1 = $words[0]->translates->first();
        $word2 = $words[1]->translates->first();

        Livewire::test(PublicTest::class)
            ->assertSet('isComplete', false)
            // Submit answers - doesn't matter if correct, just need to progress
            ->call('submitAnswer', 'any_answer_1')
            ->assertSet('isComplete', false)
            ->call('submitAnswer', 'any_answer_2')
            ->assertSet('isComplete', true);
    }

    public function test_correct_answer_increments_correct_stat(): void
    {
        $words = $this->createTestWords(3);

        $component = Livewire::test(PublicTest::class);

        // Get current word and question type to determine correct answer
        $wordId = $component->get('wordId');
        $questionType = $component->get('questionType');
        $word = $component->get('word');

        $correctAnswer = $questionType === 'en_to_uk'
            ? $word['translation']
            : $word['word'];

        $component
            ->assertSet('stats.correct', 0)
            ->call('submitAnswer', $correctAnswer)
            ->assertSet('stats.correct', 1)
            ->assertSet('feedback.type', 'success');
    }

    public function test_wrong_answer_increments_wrong_stat(): void
    {
        $words = $this->createTestWords(3);

        $component = Livewire::test(PublicTest::class);

        // Submit a definitely wrong answer
        $component
            ->assertSet('stats.wrong', 0)
            ->call('submitAnswer', 'definitely_wrong_answer_xyz')
            ->assertSet('stats.wrong', 1)
            ->assertSet('feedback.type', 'error');
    }

    public function test_apply_filter_reinitializes_queue(): void
    {
        $tag1 = Tag::create(['name' => 'Tag1']);
        $tag2 = Tag::create(['name' => 'Tag2']);

        $this->createTestWords(3, $tag1);
        $this->createTestWords(2, $tag2);

        $component = Livewire::test(PublicTest::class)
            ->assertSet('totalCount', 5) // All words initially
            ->set('selectedTags', ['Tag1'])
            ->call('applyFilter')
            ->assertSet('totalCount', 3) // Only Tag1 words
            ->assertSet('stats.total', 0) // Stats reset
            ->assertSet('isComplete', false);
    }

    public function test_reset_filter_shows_all_words(): void
    {
        $tag = Tag::create(['name' => 'TestTag']);
        $this->createTestWords(2, $tag);
        $this->createTestWords(3); // Words without tag

        // Start with filter applied
        session(['words_selected_tags' => ['TestTag']]);

        $component = Livewire::test(PublicTest::class)
            ->assertSet('totalCount', 2) // Only tagged words
            ->call('resetFilter')
            ->assertSet('totalCount', 5) // All words
            ->assertSet('selectedTags', [])
            ->assertSet('stats.total', 0);
    }

    public function test_reset_progress_keeps_filter_but_resets_stats(): void
    {
        $tag = Tag::create(['name' => 'TestTag']);
        $this->createTestWords(3, $tag);

        $component = Livewire::test(PublicTest::class)
            ->set('selectedTags', ['TestTag'])
            ->call('applyFilter')
            // Answer a question
            ->call('submitAnswer', 'any_answer')
            ->assertSet('stats.total', 1)
            // Reset progress
            ->call('resetProgress')
            ->assertSet('stats.total', 0)
            ->assertSet('stats.correct', 0)
            ->assertSet('stats.wrong', 0)
            ->assertSet('selectedTags', ['TestTag']) // Filter preserved
            ->assertSet('isComplete', false);
    }

    public function test_empty_state_when_no_words_match_filter(): void
    {
        $tag1 = Tag::create(['name' => 'ExistingTag']);
        $tag2 = Tag::create(['name' => 'EmptyTag']);

        $this->createTestWords(3, $tag1);
        // No words with EmptyTag

        // When filtering to a tag with no words, totalCount should be 0
        // The component shows empty state, not complete state
        Livewire::test(PublicTest::class)
            ->set('selectedTags', ['EmptyTag'])
            ->call('applyFilter')
            ->assertSet('totalCount', 0);
    }

    public function test_clear_feedback(): void
    {
        $this->createTestWords(3);

        Livewire::test(PublicTest::class)
            ->call('submitAnswer', 'any_answer')
            ->assertNotSet('feedback', null) // Feedback is set
            ->call('clearFeedback')
            ->assertSet('feedback', null);
    }

    public function test_progress_percent_calculation(): void
    {
        // Create exactly 4 words
        $this->createTestWords(4);

        $component = Livewire::test(PublicTest::class)
            ->assertSet('progressPercent', 0)
            ->call('submitAnswer', 'answer1')
            ->assertSet('progressPercent', 25) // 1/4 = 25%
            ->call('submitAnswer', 'answer2')
            ->assertSet('progressPercent', 50); // 2/4 = 50%
    }
}
