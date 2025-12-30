<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\Test;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class TestJsV2OptionsShuffleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('tests')) {
            Artisan::call('migrate', ['--path' => 'database/migrations/2025_07_20_184450_create_tests_table.php']);
            Artisan::call('migrate', ['--path' => 'database/migrations/2025_08_04_000002_add_description_to_tests_table.php']);
        }

        $this->ensureQuestionSchema();
        $this->resetQuestionData();

        session()->start();
        session()->flush();

        Test::query()->delete();
    }

    private function ensureQuestionSchema(): void
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

        if (! Schema::hasTable('verb_hints')) {
            Schema::create('verb_hints', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('question_id');
                $table->unsignedBigInteger('option_id')->nullable();
                $table->string('marker')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('question_variants')) {
            Schema::create('question_variants', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('question_id');
                $table->text('text');
                $table->timestamps();
            });
        }
    }

    private function resetQuestionData(): void
    {
        foreach ([
            'question_option_question',
            'question_answers',
            'question_variants',
            'verb_hints',
            'questions',
            'question_options',
            'categories',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }
    }

    private function createQuestionWithOptions(int $optionCount = 4): Question
    {
        $category = Category::create(['name' => 'Test Category']);

        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'Choose the correct answer: {a1}',
            'difficulty' => 1,
            'flag' => 0,
            'category_id' => $category->id,
            'level' => 'B1',
        ]);

        $options = [];
        for ($i = 1; $i <= $optionCount; $i++) {
            $opt = QuestionOption::create(['option' => "Option {$i}"]);
            DB::table('question_option_question')->insert([
                'question_id' => $question->id,
                'option_id' => $opt->id,
                'flag' => null,
            ]);
            $options[] = $opt;
        }

        // First option is the correct answer
        QuestionAnswer::create([
            'question_id' => $question->id,
            'marker' => 'a1',
            'option_id' => $options[0]->id,
        ]);

        return $question;
    }

    /**
     * @test
     */
    public function options_are_shuffled_in_question_data(): void
    {
        $question = $this->createQuestionWithOptions(4);

        $test = Test::create([
            'name' => 'Sample Test',
            'slug' => 'sample-test-' . uniqid(),
            'filters' => [],
            'questions' => [$question->id],
        ]);

        // Collect option orders from multiple calls to confirm shuffling
        $optionsOrders = [];
        $attempts = 10;

        for ($i = 0; $i < $attempts; $i++) {
            session()->start();
            session()->flush();

            $response = $this->get(route('test.show', $test->slug));
            $response->assertStatus(200);

            // Extract questionData from the response
            $content = $response->getContent();

            // Find the JSON data in the response
            if (preg_match('/window\.__INITIAL_JS_TEST_QUESTIONS__\s*=\s*(\[.*?\]);/s', $content, $matches)) {
                $questionData = json_decode($matches[1], true);
                if ($questionData && isset($questionData[0]['options'])) {
                    $optionsOrders[] = $questionData[0]['options'];
                }
            }
        }

        // Verify we got some data
        $this->assertNotEmpty($optionsOrders, 'Failed to extract question options from response');

        // Check that not all option orders are the same (indicating shuffling)
        $uniqueOrders = array_unique(array_map(fn ($order) => implode(',', $order), $optionsOrders));

        // With 4 options and 10 attempts, we should have more than 1 unique order
        // if shuffling is working properly
        $this->assertGreaterThan(
            1,
            count($uniqueOrders),
            'Options should be shuffled - expected more than 1 unique order across ' . $attempts . ' attempts'
        );
    }

    /**
     * @test
     */
    public function options_include_correct_answer(): void
    {
        $question = $this->createQuestionWithOptions(4);

        $test = Test::create([
            'name' => 'Sample Test',
            'slug' => 'sample-test-' . uniqid(),
            'filters' => [],
            'questions' => [$question->id],
        ]);

        session()->start();
        session()->flush();

        $response = $this->get(route('test.show', $test->slug));
        $response->assertStatus(200);

        $content = $response->getContent();

        if (preg_match('/window\.__INITIAL_JS_TEST_QUESTIONS__\s*=\s*(\[.*?\]);/s', $content, $matches)) {
            $questionData = json_decode($matches[1], true);

            $this->assertNotNull($questionData);
            $this->assertNotEmpty($questionData);

            $options = $questionData[0]['options'] ?? [];
            $answer = $questionData[0]['answer'] ?? '';

            // Verify correct answer is in options
            $this->assertContains($answer, $options, 'Correct answer should be included in options');
        } else {
            $this->fail('Could not extract question data from response');
        }
    }
}
