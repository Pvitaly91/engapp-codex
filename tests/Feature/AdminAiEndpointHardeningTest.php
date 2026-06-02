<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Services\ChatGPTService;
use App\Services\GeminiService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminAiEndpointHardeningTest extends TestCase
{
    private Question $question;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureSchema();
        $this->resetData();
        $this->question = $this->createQuestion('Where {a1} my shoes?', 'are', ['is']);
        app()->setLocale('uk');
    }

    public function test_question_hint_route_is_throttled_after_twelve_requests_per_minute(): void
    {
        $this->mock(ChatGPTService::class, function ($mock) {
            $mock->shouldReceive('hintSentenceStructure')
                ->once()
                ->andReturn('Hint text');
        });
        $this->mock(GeminiService::class, function ($mock) {
            $mock->shouldReceive('hintSentenceStructure')
                ->once()
                ->andReturn('Hint text');
        });

        for ($attempt = 0; $attempt < 12; $attempt++) {
            $this->withSession(['admin_authenticated' => true])
                ->withServerVariables(['REMOTE_ADDR' => '127.0.0.10'])
                ->postJson(route('question.hint'), ['question_id' => $this->question->id])
                ->assertOk();
        }

        $this->withSession(['admin_authenticated' => true])
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.10'])
            ->postJson(route('question.hint'), ['question_id' => $this->question->id])
            ->assertStatus(429);
    }

    public function test_question_explain_route_is_throttled_after_twelve_requests_per_minute(): void
    {
        ChatGPTExplanation::create([
            'question' => $this->question->question,
            'wrong_answer' => 'is',
            'correct_answer' => 'are',
            'language' => 'uk',
            'explanation' => 'Use "are" with plural nouns.',
        ]);

        for ($attempt = 0; $attempt < 12; $attempt++) {
            $this->withSession(['admin_authenticated' => true])
                ->withServerVariables(['REMOTE_ADDR' => '127.0.0.11'])
                ->postJson(route('question.explain'), [
                    'question_id' => $this->question->id,
                    'answer' => 'is',
                ])
                ->assertOk()
                ->assertJson([
                    'correct' => false,
                    'explanation' => 'Use "are" with plural nouns.',
                ]);
        }

        $this->withSession(['admin_authenticated' => true])
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.11'])
            ->postJson(route('question.explain'), [
                'question_id' => $this->question->id,
                'answer' => 'is',
            ])
            ->assertStatus(429);
    }

    public function test_question_hint_sanitizes_ai_output_before_response_and_persistence(): void
    {
        $raw = '<script>alert(1)</script><p>Line 1</p><p>Line 2 <a href="javascript:alert(1)" onclick="alert(1)">click</a></p><img src=x onerror=alert(1)><div>Tail</div>';
        $expected = "Line 1\n\nLine 2 click\n\nTail";

        $this->mock(ChatGPTService::class, function ($mock) use ($raw) {
            $mock->shouldReceive('hintSentenceStructure')
                ->once()
                ->andReturn($raw);
        });
        $this->mock(GeminiService::class, function ($mock) use ($raw) {
            $mock->shouldReceive('hintSentenceStructure')
                ->once()
                ->andReturn($raw);
        });

        $this->withSession(['admin_authenticated' => true])
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.12'])
            ->postJson(route('question.hint'), ['question_id' => $this->question->id])
            ->assertOk()
            ->assertJson([
                'chatgpt' => $expected,
                'gemini' => $expected,
            ]);

        $this->assertDatabaseHas('question_hints', [
            'question_id' => $this->question->id,
            'provider' => 'chatgpt',
            'hint' => $expected,
        ]);
        $this->assertDatabaseHas('question_hints', [
            'question_id' => $this->question->id,
            'provider' => 'gemini',
            'hint' => $expected,
        ]);
    }

    public function test_question_explain_returns_sanitized_stored_explanations(): void
    {
        DB::table('chatgpt_explanations')->insert([
            'question' => $this->question->question,
            'wrong_answer' => 'is',
            'correct_answer' => 'are',
            'language' => 'uk',
            'explanation' => '<script>alert(1)</script><p>Use <strong>are</strong>.</p><p>Plural subject.</p>',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withSession(['admin_authenticated' => true])
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.13'])
            ->postJson(route('question.explain'), [
                'question_id' => $this->question->id,
                'answer' => 'is',
            ])
            ->assertOk()
            ->assertJson([
                'correct' => false,
                'explanation' => "Use are.\n\nPlural subject.",
            ]);
    }

    private function createQuestion(string $text, string $correctOption, array $otherOptions): Question
    {
        $category = Category::create(['name' => 'Grammar']);

        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => $text,
            'difficulty' => 1,
            'flag' => 0,
            'category_id' => $category->id,
            'level' => 'A1',
        ]);

        $optionRecords = [];
        $options = array_values(array_unique(array_merge([$correctOption], $otherOptions)));

        foreach ($options as $value) {
            $record = QuestionOption::firstOrCreate(['option' => $value]);
            $optionRecords[$value] = $record;

            DB::table('question_option_question')->insert([
                'question_id' => $question->id,
                'option_id' => $record->id,
                'flag' => null,
            ]);
        }

        QuestionAnswer::create([
            'question_id' => $question->id,
            'option_id' => $optionRecords[$correctOption]->id,
            'marker' => 'a1',
        ]);

        return $question;
    }

    private function ensureSchema(): void
    {
        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('questions')) {
            Schema::create('questions', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->text('question');
                $table->unsignedTinyInteger('difficulty')->default(1);
                $table->boolean('flag')->default(0);
                $table->unsignedBigInteger('category_id');
                $table->string('level', 2)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('question_options')) {
            Schema::create('question_options', function (Blueprint $table) {
                $table->id();
                $table->string('option')->unique();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('question_option_question')) {
            Schema::create('question_option_question', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('question_id');
                $table->unsignedBigInteger('option_id');
                $table->tinyInteger('flag')->nullable();
                $table->unique(['question_id', 'option_id', 'flag'], 'qoq_question_option_flag_unique');
            });
        }

        if (! Schema::hasTable('question_answers')) {
            Schema::create('question_answers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('question_id');
                $table->unsignedBigInteger('option_id');
                $table->string('marker');
                $table->timestamps();
                $table->unique(['question_id', 'marker', 'option_id'], 'question_marker_option_unique');
            });
        }

        if (! Schema::hasTable('question_hints')) {
            Schema::create('question_hints', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('question_id');
                $table->string('provider', 32);
                $table->string('locale', 5)->default('uk');
                $table->text('hint');
                $table->timestamps();
                $table->unique(['question_id', 'provider', 'locale'], 'question_hints_unique');
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

    private function resetData(): void
    {
        foreach ([
            'question_hints',
            'chatgpt_explanations',
            'question_option_question',
            'question_answers',
            'questions',
            'question_options',
            'categories',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }
    }
}
