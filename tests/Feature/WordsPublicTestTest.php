<?php

namespace Tests\Feature;

use App\Livewire\Words\PublicTest;
use App\Models\Tag;
use App\Models\Translate;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WordsPublicTestTest extends TestCase
{
    use RefreshDatabase;

    public function test_submit_answer_moves_to_next_question_and_finishes(): void
    {
        $firstWord = Word::factory()->create(['word' => 'cat']);
        Translate::factory()->for($firstWord)->state(['translation' => 'кіт'])->create();

        $secondWord = Word::factory()->create(['word' => 'dog']);
        Translate::factory()->for($secondWord)->state(['translation' => 'пес'])->create();

        session(['words_queue' => [$firstWord->id, $secondWord->id], 'words_total_count' => 2]);

        $component = Livewire::test(PublicTest::class);

        $firstQuestionId = $component->get('wordId');
        $firstCorrectAnswer = $component->get('questionType') === 'en_to_uk'
            ? $component->get('word')['translation']
            : $component->get('word')['word'];

        $component->call('submitAnswer', $firstCorrectAnswer);

        $component->assertSet('isComplete', false);
        $this->assertNotSame($firstQuestionId, $component->get('wordId'));

        $secondCorrectAnswer = $component->get('questionType') === 'en_to_uk'
            ? $component->get('word')['translation']
            : $component->get('word')['word'];

        $component->call('submitAnswer', $secondCorrectAnswer);

        $component->assertSet('isComplete', true);
    }

    public function test_apply_and_reset_filter_reinitialize_queue_without_freezing(): void
    {
        $travelTag = Tag::create(['name' => 'travel']);
        $foodTag = Tag::create(['name' => 'food']);

        $travelWord = Word::factory()->create(['word' => 'plane']);
        Translate::factory()->for($travelWord)->state(['translation' => 'літак'])->create();
        $travelWord->tags()->attach($travelTag);

        $foodWord = Word::factory()->create(['word' => 'bread']);
        Translate::factory()->for($foodWord)->state(['translation' => 'хліб'])->create();
        $foodWord->tags()->attach($foodTag);

        $component = Livewire::test(PublicTest::class);

        $component->set('selectedTags', [$travelTag->name])->call('applyFilter');

        $component->assertSet('selectedTags', [$travelTag->name]);
        $component->assertSet('isComplete', false);
        $this->assertSame(1, $component->get('totalCount'));

        $component->set('selectedTags', [])->call('resetFilter');

        $component->assertSet('selectedTags', []);
        $this->assertSame(2, $component->get('totalCount'));
    }
}
