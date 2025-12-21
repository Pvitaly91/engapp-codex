<?php

namespace Tests\Feature;

use App\Livewire\WordsTest;
use Database\Seeders\PronounWordsSeeder;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class WordsTestComponentTest extends TestCase
{
    /** @test */
    public function it_loads_next_question_after_answering(): void
    {
        $this->prepareWordsData();

        $component = Livewire::test(WordsTest::class);

        $firstWordId = $component->get('wordId');
        $this->assertNotNull($firstWordId, 'Initial question should be loaded');
        $this->assertNotEmpty($component->get('options'));

        $component->call('submitAnswerByIndex', 0);

        $nextWordId = $component->get('wordId');
        $this->assertTrue(
            $component->get('isComplete') || $nextWordId !== $firstWordId,
            'Component should move to the next question or complete the test'
        );
        $this->assertSame(1, $component->get('stats')['total']);
    }

    /** @test */
    public function it_rebuilds_queue_after_filtering(): void
    {
        $this->prepareWordsData();

        $component = Livewire::test(WordsTest::class);
        $firstWordId = $component->get('wordId');

        $component->call('applyFilter', ['personal_subject']);

        $this->assertSame(['personal_subject'], $component->get('selectedTags'));

        $filteredWordId = $component->get('wordId');
        $this->assertNotNull($filteredWordId, 'Question should be loaded after applying filter');
        $this->assertGreaterThan(0, $component->get('totalCount'));
        $this->assertFalse($component->get('isComplete'));
        $this->assertSame(0, $component->get('stats')['total']);
    }

    /** @test */
    public function it_restarts_after_resetting_progress(): void
    {
        $this->prepareWordsData();

        $component = Livewire::test(WordsTest::class);
        $firstWordId = $component->get('wordId');

        $component->call('submitAnswerByIndex', 0);

        $component->call('resetProgress');

        $this->assertSame(0, $component->get('stats')['total']);
        $this->assertNotNull($component->get('wordId'));
        $this->assertNotSame($component->get('wordId'), $firstWordId);
        $this->assertFalse($component->get('isComplete'));
    }

    private function prepareWordsData(): void
    {
        Artisan::call('migrate', ['--path' => 'database/migrations/2025_07_18_182347_create_words_table.php']);
        Artisan::call('migrate', ['--path' => 'database/migrations/2025_07_18_182357_create_translates_table.php']);
        Artisan::call('migrate', ['--path' => 'database/migrations/2025_07_31_000001_update_translates_unique_index.php']);
        Artisan::call('migrate', ['--path' => 'database/migrations/2025_07_30_000001_create_tags_table.php']);
        Artisan::call('migrate', ['--path' => 'database/migrations/2025_07_30_000002_create_tag_word_table.php']);

        $this->seed(PronounWordsSeeder::class);
    }
}
