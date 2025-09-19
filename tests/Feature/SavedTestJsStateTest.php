<?php

namespace Tests\Feature;

use App\Http\Controllers\GrammarTestController;
use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\QuestionVariant;
use App\Models\Test;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class SavedTestJsStateTest extends TestCase
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

    private function createSavedTest(array $questionIds = []): Test
    {
        return Test::create([
            'name' => 'Sample Test',
            'slug' => uniqid('sample-test-', true),
            'filters' => [],
            'questions' => $questionIds,
        ]);
    }

    private function createQuestionWithVariants(): array
    {
        $category = Category::create(['name' => 'Perfect Tenses']);
        $questionText = 'Emma {a1} chocolate this week – only fruit.';

        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => $questionText,
            'difficulty' => 1,
            'flag' => 2,
            'category_id' => $category->id,
            'level' => 'B1',
        ]);

        $options = collect([
            "hasn't eaten",
            "hasn't been eating",
            "didn't eat",
            "wasn't eating",
        ])->map(fn(string $text) => QuestionOption::create(['option' => $text]));

        foreach ($options as $option) {
            DB::table('question_option_question')->insert([
                'question_id' => $question->id,
                'option_id' => $option->id,
                'flag' => null,
            ]);
        }

        QuestionAnswer::create([
            'question_id' => $question->id,
            'marker' => 'a1',
            'option_id' => $options->first()->id,
        ]);

        $variantTexts = [
            'Sophia {a1} bread today – she chose rice instead.',
            'Lucas {a1} cake this week – he prefers fruit salad.',
        ];

        foreach ($variantTexts as $text) {
            QuestionVariant::create([
                'question_id' => $question->id,
                'text' => $text,
            ]);
        }

        return [$question, array_merge([$questionText], $variantTexts)];
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

    private function postState(Test $test, array $payload, array $session = [])
    {
        $session = array_merge(['_token' => 'test-token'], $session);

        return $this->withSession($session)->postJson(
            route('saved-test.js.state', $test->slug),
            $payload,
            ['X-CSRF-TOKEN' => 'test-token']
        );
    }

    public function test_it_stores_state_in_session(): void
    {
        $test = $this->createSavedTest();

        $payload = [
            'mode' => 'saved-test-js',
            'state' => ['foo' => 'bar'],
        ];

        $response = $this->postState($test, $payload);

        $response->assertNoContent();

        $key = sprintf('saved_test_js_state:%s:%s', $test->slug, 'saved-test-js');
        $this->assertSame($payload['state'], session($key));
    }

    public function test_it_clears_state_when_null(): void
    {
        $test = $this->createSavedTest();
        $key = sprintf('saved_test_js_state:%s:%s', $test->slug, 'saved-test-js');

        $response = $this->postState($test, [
            'mode' => 'saved-test-js',
            'state' => null,
        ], [
            $key => ['foo' => 'bar'],
        ]);

        $response->assertNoContent();
        $this->assertNull(session($key));
    }

    public function test_it_rejects_invalid_payload(): void
    {
        $test = $this->createSavedTest();

        $this->postState($test, [
            'mode' => 'not-valid',
            'state' => ['allowed' => true],
        ])->assertStatus(422);

        $invalidKey = sprintf('saved_test_js_state:%s:%s', $test->slug, 'not-valid');
        $this->assertNull(session($invalidKey));

        $this->postState($test, [
            'mode' => 'saved-test-js',
            'state' => 'nope',
        ])->assertStatus(422);

        $validKey = sprintf('saved_test_js_state:%s:%s', $test->slug, 'saved-test-js');
        $this->assertNull(session($validKey));
    }

    public function test_it_fetches_fresh_questions_and_clears_state(): void
    {
        $test = $this->createSavedTest();
        $key = sprintf('saved_test_js_state:%s:%s', $test->slug, 'saved-test-js');

        $response = $this->withSession([$key => ['foo' => 'bar']])->getJson(
            route('saved-test.js.questions', $test->slug) . '?mode=saved-test-js'
        );

        $response->assertOk()->assertJson(['questions' => []]);
        $this->assertNull(session($key));
    }

    public function test_it_rejects_invalid_mode_when_fetching_questions(): void
    {
        $test = $this->createSavedTest();

        $this->getJson(route('saved-test.js.questions', $test->slug) . '?mode=invalid')
            ->assertStatus(422);
    }

    public function test_it_rotates_variants_when_retrying_js_test(): void
    {
        if (! Schema::hasTable('question_variants')) {
            $this->markTestSkipped('Question variants table is missing.');
        }

        [$question, $variants] = $this->createQuestionWithVariants();
        $test = $this->createSavedTest([$question->id]);

        /** @var GrammarTestController $controller */
        $controller = app(GrammarTestController::class);

        session()->start();

        $requestOne = Request::create(
            "/test/{$test->slug}/js/questions",
            'GET',
            ['mode' => 'saved-test-js']
        );

        $firstPayload = $controller->fetchSavedTestJsQuestions($requestOne, $test->slug)->getData(true);
        $firstQuestion = $firstPayload['questions'][0]['question'] ?? null;

        $requestTwo = Request::create(
            "/test/{$test->slug}/js/questions",
            'GET',
            ['mode' => 'saved-test-js']
        );

        $secondPayload = $controller->fetchSavedTestJsQuestions($requestTwo, $test->slug)->getData(true);
        $secondQuestion = $secondPayload['questions'][0]['question'] ?? null;

        $this->assertNotNull($firstQuestion);
        $this->assertNotNull($secondQuestion);

        $this->assertNotSame($firstQuestion, $secondQuestion);
        $this->assertContains($firstQuestion, $variants);
        $this->assertContains($secondQuestion, $variants);
    }
}
