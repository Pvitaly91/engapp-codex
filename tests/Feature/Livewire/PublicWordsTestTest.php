<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Words\PublicTest;
use App\Models\Tag;
use App\Models\Translate;
use App\Models\Word;
use App\Services\WordsTestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PublicWordsTestTest extends TestCase
{
    use RefreshDatabase;

    public function test_submit_answer_advances_or_completes_queue(): void
    {
        $this->seedWords();

        $component = Livewire::test(PublicTest::class);

        $initialWordId = $component->get('wordId');
        $options = $component->get('options');

        $this->assertNotNull($initialWordId);
        $this->assertNotEmpty($options);

        $component->call('submitAnswer', $options[0]);

        $this->assertTrue(
            $component->get('isComplete') || $component->get('wordId') !== $initialWordId,
            'Component should move to next word or mark completion.'
        );
    }

    public function test_filters_reinitialize_queue_and_totals(): void
    {
        [$tagA, $tagB] = $this->createTags();
        $this->createWord('sun', 'сонце', $tagA);
        $this->createWord('moon', 'місяць', $tagA);
        $this->createWord('star', 'зірка', $tagB);

        $component = Livewire::test(PublicTest::class);

        $initialTotal = session(WordsTestService::SESSION_TOTAL_COUNT);

        $component->set('selectedTags', [$tagB->name]);
        $component->call('applyFilter');

        $this->assertSame(1, session(WordsTestService::SESSION_TOTAL_COUNT));

        $component->call('resetFilter');

        $this->assertEquals(3, session(WordsTestService::SESSION_TOTAL_COUNT));
        $this->assertSame([], $component->get('selectedTags'));
        $this->assertNotSame($initialTotal, session(WordsTestService::SESSION_TOTAL_COUNT));
    }

    private function seedWords(): void
    {
        [$tagA] = $this->createTags();

        $this->createWord('apple', 'яблуко', $tagA);
        $this->createWord('banana', 'банан', $tagA);
        $this->createWord('cherry', 'вишня', $tagA);
    }

    private function createTags(): array
    {
        $tagA = Tag::create(['name' => 'fruits']);
        $tagB = Tag::create(['name' => 'space']);

        return [$tagA, $tagB];
    }

    private function createWord(string $word, string $translation, Tag $tag): Word
    {
        $wordModel = Word::create([
            'word' => $word,
            'type' => 'noun',
        ]);

        Translate::create([
            'word_id' => $wordModel->id,
            'lang' => 'uk',
            'translation' => $translation,
        ]);

        $wordModel->tags()->attach($tag->id);

        return $wordModel;
    }
}
