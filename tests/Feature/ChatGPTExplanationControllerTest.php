<?php

namespace Tests\Feature;

use App\Models\ChatGPTExplanation;
use App\Models\Question;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ChatGPTExplanationControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureSchema();
    }

    /** @test */
    public function it_stores_and_deletes_explanations(): void
    {
        $question = Question::create([
            'uuid' => 'q-explain',
            'question' => 'Why {a1}?',
            'difficulty' => 1,
        ]);

        $response = $this->from('/back')->post(route('chatgpt-explanations.store'), [
            'question_id' => $question->id,
            'language' => 'uk',
            'wrong_answer' => 'because',
            'correct_answer' => 'why not',
            'explanation' => 'Because it is fun.',
            'question' => $question->question,
            'from' => '/back',
        ]);

        $response->assertRedirect('/back');

        $explanation = ChatGPTExplanation::where('language', 'uk')->first();
        $this->assertNotNull($explanation);

        $this->assertDatabaseHas('chatgpt_explanations', [
            'id' => $explanation->id,
            'correct_answer' => 'why not',
        ]);

        $deleteResponse = $this->from('/back')->delete(route('chatgpt-explanations.destroy', $explanation), [
            'from' => '/back',
        ]);

        $deleteResponse->assertRedirect('/back');

        $this->assertDatabaseMissing('chatgpt_explanations', [
            'id' => $explanation->id,
        ]);
    }

    private function ensureSchema(): void
    {
        if (! Schema::hasTable('questions')) {
            Schema::create('questions', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->text('question');
                $table->unsignedTinyInteger('difficulty')->default(1);
                $table->string('level', 2)->nullable();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('chatgpt_explanations')) {
            Schema::create('chatgpt_explanations', function (Blueprint $table) {
                $table->id();
                $table->text('question');
                $table->text('wrong_answer')->nullable();
                $table->text('correct_answer');
                $table->string('language');
                $table->text('explanation');
                $table->timestamps();
                $table->unique(['question', 'wrong_answer', 'correct_answer', 'language'], 'chatgpt_explanations_unique');
            });
        }
    }
}
