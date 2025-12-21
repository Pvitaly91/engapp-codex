<?php

namespace Tests\Feature;

use App\Models\Word;
use App\Models\Translate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWordsTestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create words with valid Ukrainian translations
        $word1 = Word::create(['word' => 'apple', 'type' => 'noun']);
        Translate::create(['word_id' => $word1->id, 'lang' => 'uk', 'translation' => 'яблуко']);
        
        $word2 = Word::create(['word' => 'book', 'type' => 'noun']);
        Translate::create(['word_id' => $word2->id, 'lang' => 'uk', 'translation' => 'книга']);
        
        $word3 = Word::create(['word' => 'cat', 'type' => 'noun']);
        Translate::create(['word_id' => $word3->id, 'lang' => 'uk', 'translation' => 'кіт']);
        
        $word4 = Word::create(['word' => 'dog', 'type' => 'noun']);
        Translate::create(['word_id' => $word4->id, 'lang' => 'uk', 'translation' => 'собака']);
        
        $word5 = Word::create(['word' => 'house', 'type' => 'noun']);
        Translate::create(['word_id' => $word5->id, 'lang' => 'uk', 'translation' => 'будинок']);
    }

    public function test_public_words_test_page_loads(): void
    {
        $response = $this->get('/words/test');

        $response->assertStatus(200);
        $response->assertViewIs('words.public-test');
    }

    public function test_public_words_test_state_endpoint_returns_json(): void
    {
        $response = $this->getJson('/words/test/state');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'completed',
            'stats' => ['correct', 'wrong', 'total'],
            'percentage',
            'totalCount',
            'answeredCount',
        ]);
    }

    public function test_public_words_test_state_returns_question(): void
    {
        $response = $this->getJson('/words/test/state');

        $response->assertStatus(200);
        $response->assertJson(['completed' => false]);
        $response->assertJsonStructure([
            'question' => [
                'word_id',
                'question_type',
                'question_text',
                'options',
            ],
        ]);
    }

    public function test_answer_endpoint_accepts_correct_answer(): void
    {
        // First get the current question
        $stateResponse = $this->getJson('/words/test/state');
        $state = $stateResponse->json();
        
        $questionType = $state['question']['question_type'];
        $wordId = $state['question']['word_id'];
        
        // Find the correct answer
        $word = Word::with(['translates' => fn($q) => $q->where('lang', 'uk')])->find($wordId);
        $correctAnswer = $questionType === 'en_to_uk' 
            ? $word->translates->first()->translation 
            : $word->word;
        
        // Submit the correct answer
        $response = $this->postJson('/words/test/answer', [
            'word_id' => $wordId,
            'answer' => $correctAnswer,
            'question_type' => $questionType,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['isCorrect' => true]);
        $response->assertJsonStructure([
            'isCorrect',
            'correctAnswer',
            'userAnswer',
            'stats',
            'percentage',
        ]);
    }

    public function test_answer_endpoint_handles_wrong_answer(): void
    {
        // First get the current question
        $stateResponse = $this->getJson('/words/test/state');
        $state = $stateResponse->json();
        
        $questionType = $state['question']['question_type'];
        $wordId = $state['question']['word_id'];
        
        // Submit a wrong answer
        $response = $this->postJson('/words/test/answer', [
            'word_id' => $wordId,
            'answer' => 'definitely_wrong_answer',
            'question_type' => $questionType,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['isCorrect' => false]);
    }

    public function test_reset_endpoint_clears_progress(): void
    {
        // First answer some questions
        $stateResponse = $this->getJson('/words/test/state');
        $state = $stateResponse->json();
        
        $this->postJson('/words/test/answer', [
            'word_id' => $state['question']['word_id'],
            'answer' => 'test_answer',
            'question_type' => $state['question']['question_type'],
        ]);
        
        // Reset the test
        $response = $this->postJson('/words/test/reset');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Verify progress is reset
        $newStateResponse = $this->getJson('/words/test/state');
        $newState = $newStateResponse->json();
        
        $this->assertEquals(0, $newState['stats']['total']);
        $this->assertEquals(0, $newState['stats']['correct']);
        $this->assertEquals(0, $newState['stats']['wrong']);
    }

    public function test_refresh_does_not_skip_question(): void
    {
        // Get initial state
        $firstResponse = $this->getJson('/words/test/state');
        $firstQuestion = $firstResponse->json()['question'];
        
        // Simulate page refresh by calling state again
        $secondResponse = $this->getJson('/words/test/state');
        $secondQuestion = $secondResponse->json()['question'];
        
        // The question should be the same (not skipped)
        $this->assertEquals($firstQuestion['word_id'], $secondQuestion['word_id']);
    }

    public function test_words_without_translation_are_excluded(): void
    {
        // Create a word without translation
        Word::create(['word' => 'orphan_word', 'type' => 'noun']);
        
        // Create a word with empty translation
        $wordEmpty = Word::create(['word' => 'empty_translation', 'type' => 'noun']);
        Translate::create(['word_id' => $wordEmpty->id, 'lang' => 'uk', 'translation' => '']);
        
        // Reset and get state
        $this->postJson('/words/test/reset');
        $response = $this->getJson('/words/test/state');
        $state = $response->json();
        
        // Total count should only include words with valid translations (5 from setUp)
        $this->assertEquals(5, $state['totalCount']);
    }
}
