<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Models\QuestionVariant;
use App\Models\Test;
use App\Models\VerbHint;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class SavedTestTechnicalPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureSchema();
        $this->resetData();
    }

    /** @test */
    public function it_displays_detailed_information_for_each_question(): void
    {
        $category = Category::create(['name' => 'Present Simple']);

        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'I {a1} to school every day.',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);

        $optionGo = QuestionOption::create(['option' => 'go']);
        $optionGoes = QuestionOption::create(['option' => 'goes']);
        $optionWent = QuestionOption::create(['option' => 'went']);

        $question->options()->attach([$optionGo->id, $optionGoes->id, $optionWent->id]);

        QuestionAnswer::create([
            'question_id' => $question->id,
            'marker' => 'a1',
            'option_id' => $optionGo->id,
        ]);

        VerbHint::create([
            'question_id' => $question->id,
            'marker' => 'a1',
            'option_id' => $optionGo->id,
        ]);

        QuestionHint::create([
            'question_id' => $question->id,
            'provider' => 'chatgpt',
            'locale' => 'uk',
            'hint' => 'Використовуйте базову форму дієслова.',
        ]);

        QuestionHint::create([
            'question_id' => $question->id,
            'provider' => 'gemini',
            'locale' => 'uk',
            'hint' => 'Порада від Gemini.',
        ]);

        if (Schema::hasTable('question_variants')) {
            QuestionVariant::create([
                'question_id' => $question->id,
                'text' => 'They {a1} to school together.',
            ]);
        }

        ChatGPTExplanation::create([
            'question' => 'I {a1} to school every day.',
            'wrong_answer' => 'goes',
            'correct_answer' => 'go',
            'language' => 'uk',
            'explanation' => 'Форма go вживається з I.',
        ]);

        $test = Test::create([
            'name' => 'Simple test',
            'slug' => 'simple-test',
            'filters' => [],
            'questions' => [$question->id],
        ]);

        $response = $this->get(route('saved-test.tech', $test->slug));

        $response->assertOk();
        $response->assertDontSee('<details open', false);
        $response->assertSee('Правильні відповіді');
        $response->assertSee('Показати ▼');
        $response->assertSee('I <mark class="rounded bg-emerald-100 px-1 py-0.5 font-semibold text-emerald-800">go</mark> to school every day.', false);
        $response->assertSee('Варіанти відповіді');
        $response->assertSee('goes');
        $response->assertSee('Verb hint');
        $response->assertSee('Question hints');
        $response->assertSee('ChatGPT explanations');
        $response->assertSee('Форма go вживається з I.');
        $response->assertSeeInOrder([
            'Варіанти запитання',
            'Правильні відповіді',
        ]);

        if (Schema::hasTable('question_variants')) {
            $response->assertSee('They <mark class="rounded bg-emerald-100 px-1 py-0.5 font-semibold text-emerald-800">go</mark> to school together.', false);
        }
    }

    /** @test */
    public function it_shows_edit_actions_for_question_elements(): void
    {
        $category = Category::create(['name' => 'Present Simple']);

        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'I {a1} to school every day.',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);

        $optionGo = QuestionOption::create(['option' => 'go']);
        $optionGoes = QuestionOption::create(['option' => 'goes']);
        $optionWent = QuestionOption::create(['option' => 'went']);

        $question->options()->attach([$optionGo->id, $optionGoes->id, $optionWent->id]);

        $answer = QuestionAnswer::create([
            'question_id' => $question->id,
            'marker' => 'a1',
            'option_id' => $optionGo->id,
        ]);

        $verbHint = VerbHint::create([
            'question_id' => $question->id,
            'marker' => 'a1',
            'option_id' => $optionGo->id,
        ]);

        $hint = QuestionHint::create([
            'question_id' => $question->id,
            'provider' => 'chatgpt',
            'locale' => 'uk',
            'hint' => 'Використовуйте базову форму дієслова.',
        ]);

        $variant = null;
        if (Schema::hasTable('question_variants')) {
            $variant = QuestionVariant::create([
                'question_id' => $question->id,
                'text' => 'They {a1} to school together.',
            ]);
        }

        $test = Test::create([
            'name' => 'Simple test',
            'slug' => 'simple-test',
            'filters' => [],
            'questions' => [$question->id],
        ]);

        $response = $this->get(route('saved-test.tech', $test->slug));

        $response->assertOk();
        $response->assertSee('Редагувати питання');
        $response->assertSee('Видалити питання');
        $response->assertSee('Змінити');
        $response->assertSee('Редагувати відповідь');
        $response->assertSee("techEditor.editQuestion({$question->id})", false);
        $response->assertSee("techEditor.deleteQuestion({$question->id})", false);
        $response->assertSee("techEditor.editQuestionLevel({$question->id})", false);
        $response->assertSee("techEditor.editAnswer({$question->id}, {$answer->id})", false);
        $response->assertSee("techEditor.editOption({$question->id}, {$optionGo->id})", false);
        $response->assertSee("techEditor.editQuestionHint({$question->id}, {$hint->id})", false);
        $response->assertSee("techEditor.editVerbHint({$question->id}, {$verbHint->id})", false);

        if ($variant) {
            $response->assertSee("techEditor.editVariant({$question->id}, {$variant->id})", false);
        }
    }

    /** @test */
    public function it_deletes_a_question_and_removes_unused_relations(): void
    {
        $category = Category::create(['name' => 'Present Simple']);

        $sharedOption = QuestionOption::create(['option' => 'go']);
        $uniqueOption = QuestionOption::create(['option' => 'goes']);

        $questionToKeep = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'They {a1} to school every day.',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);

        $questionToKeep->options()->attach([$sharedOption->id]);

        QuestionAnswer::create([
            'question_id' => $questionToKeep->id,
            'marker' => 'a1',
            'option_id' => $sharedOption->id,
        ]);

        $questionToDelete = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'I {a1} to school every day.',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);

        $questionToDelete->options()->attach([$sharedOption->id, $uniqueOption->id]);

        QuestionAnswer::create([
            'question_id' => $questionToDelete->id,
            'marker' => 'a1',
            'option_id' => $uniqueOption->id,
        ]);

        VerbHint::create([
            'question_id' => $questionToDelete->id,
            'marker' => 'a1',
            'option_id' => $uniqueOption->id,
        ]);

        QuestionHint::create([
            'question_id' => $questionToDelete->id,
            'provider' => 'chatgpt',
            'locale' => 'uk',
            'hint' => 'Використовуйте базову форму дієслова.',
        ]);

        if (Schema::hasTable('question_variants')) {
            QuestionVariant::create([
                'question_id' => $questionToDelete->id,
                'text' => 'We {a1} to school every day.',
            ]);
        }

        ChatGPTExplanation::create([
            'question' => 'I {a1} to school every day.',
            'wrong_answer' => 'goes',
            'correct_answer' => 'go',
            'language' => 'uk',
            'explanation' => 'Форма go вживається з I.',
        ]);

        $test = Test::create([
            'name' => 'Simple test',
            'slug' => 'simple-test',
            'filters' => [],
            'questions' => [$questionToDelete->id, $questionToKeep->id],
        ]);

        $response = $this->deleteJson(route('saved-test.question.destroy', [$test->slug, $questionToDelete->id]));

        $response->assertOk();
        $this->assertDatabaseMissing('questions', ['id' => $questionToDelete->id]);
        $this->assertDatabaseHas('questions', ['id' => $questionToKeep->id]);
        $this->assertDatabaseMissing('question_answers', ['question_id' => $questionToDelete->id]);
        $this->assertDatabaseMissing('verb_hints', ['question_id' => $questionToDelete->id]);
        $this->assertDatabaseMissing('question_hints', ['question_id' => $questionToDelete->id]);

        if (Schema::hasTable('question_variants')) {
            $this->assertDatabaseMissing('question_variants', ['question_id' => $questionToDelete->id]);
        }

        $this->assertDatabaseMissing('chatgpt_explanations', ['question' => 'I {a1} to school every day.']);
        $this->assertDatabaseMissing('question_option_question', [
            'question_id' => $questionToDelete->id,
            'option_id' => $sharedOption->id,
        ]);
        $this->assertDatabaseHas('question_option_question', [
            'question_id' => $questionToKeep->id,
            'option_id' => $sharedOption->id,
        ]);
        $this->assertDatabaseMissing('question_option_question', [
            'question_id' => $questionToDelete->id,
            'option_id' => $uniqueOption->id,
        ]);
        $this->assertDatabaseMissing('question_options', ['id' => $uniqueOption->id]);
        $this->assertDatabaseHas('question_options', ['id' => $sharedOption->id]);

        $updatedTest = $test->fresh();
        $this->assertNotContains($questionToDelete->id, $updatedTest->questions);
        $this->assertContains($questionToKeep->id, $updatedTest->questions);
    }

    /** @test */
    public function it_deletes_all_questions_from_saved_test(): void
    {
        $category = Category::create(['name' => 'Present Simple']);

        $optionGo = QuestionOption::create(['option' => 'go']);
        $optionGoes = QuestionOption::create(['option' => 'goes']);

        $firstQuestion = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'I {a1} to school every day.',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);

        $secondQuestion = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'They {a1} to school together.',
            'difficulty' => 2,
            'level' => 'A2',
            'category_id' => $category->id,
        ]);

        $firstQuestion->options()->attach([$optionGo->id, $optionGoes->id]);
        $secondQuestion->options()->attach([$optionGo->id]);

        QuestionAnswer::create([
            'question_id' => $firstQuestion->id,
            'marker' => 'a1',
            'option_id' => $optionGo->id,
        ]);

        QuestionAnswer::create([
            'question_id' => $secondQuestion->id,
            'marker' => 'a1',
            'option_id' => $optionGo->id,
        ]);

        VerbHint::create([
            'question_id' => $firstQuestion->id,
            'marker' => 'a1',
            'option_id' => $optionGo->id,
        ]);

        VerbHint::create([
            'question_id' => $secondQuestion->id,
            'marker' => 'a1',
            'option_id' => $optionGo->id,
        ]);

        QuestionHint::create([
            'question_id' => $firstQuestion->id,
            'provider' => 'chatgpt',
            'locale' => 'uk',
            'hint' => 'Використовуйте базову форму дієслова.',
        ]);

        QuestionHint::create([
            'question_id' => $secondQuestion->id,
            'provider' => 'chatgpt',
            'locale' => 'uk',
            'hint' => 'Порада для другого питання.',
        ]);

        ChatGPTExplanation::create([
            'question' => 'I {a1} to school every day.',
            'wrong_answer' => 'goes',
            'correct_answer' => 'go',
            'language' => 'uk',
            'explanation' => 'Форма go вживається з I.',
        ]);

        ChatGPTExplanation::create([
            'question' => 'They {a1} to school together.',
            'wrong_answer' => 'goes',
            'correct_answer' => 'go',
            'language' => 'uk',
            'explanation' => 'Для they використовуємо go.',
        ]);

        $test = Test::create([
            'name' => 'Simple test',
            'slug' => 'simple-test',
            'filters' => [],
            'questions' => [$firstQuestion->id, $secondQuestion->id],
        ]);

        $response = $this->deleteJson(route('saved-test.questions.destroy-all', $test->slug));

        $response->assertOk();
        $this->assertEqualsCanonicalizing(
            [$firstQuestion->id, $secondQuestion->id],
            $response->json('deleted_ids') ?? []
        );

        $this->assertDatabaseMissing('questions', ['id' => $firstQuestion->id]);
        $this->assertDatabaseMissing('questions', ['id' => $secondQuestion->id]);
        $this->assertDatabaseMissing('question_answers', ['question_id' => $firstQuestion->id]);
        $this->assertDatabaseMissing('question_answers', ['question_id' => $secondQuestion->id]);
        $this->assertDatabaseMissing('verb_hints', ['question_id' => $firstQuestion->id]);
        $this->assertDatabaseMissing('verb_hints', ['question_id' => $secondQuestion->id]);
        $this->assertDatabaseMissing('question_hints', ['question_id' => $firstQuestion->id]);
        $this->assertDatabaseMissing('question_hints', ['question_id' => $secondQuestion->id]);
        $this->assertDatabaseMissing('chatgpt_explanations', ['question' => 'I {a1} to school every day.']);
        $this->assertDatabaseMissing('chatgpt_explanations', ['question' => 'They {a1} to school together.']);

        $this->assertDatabaseMissing('question_option_question', ['question_id' => $firstQuestion->id]);
        $this->assertDatabaseMissing('question_option_question', ['question_id' => $secondQuestion->id]);
        $this->assertDatabaseMissing('question_options', ['id' => $optionGo->id]);
        $this->assertDatabaseMissing('question_options', ['id' => $optionGoes->id]);

        $this->assertSame([], $test->fresh()->questions);
    }

    /** @test */
    public function it_keeps_shared_resources_when_duplicate_questions_exist(): void
    {
        $category = Category::create(['name' => 'Present Simple']);

        $questionText = 'I {a1} to school every day.';

        $firstQuestion = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => $questionText,
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);

        $secondQuestion = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => $questionText,
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
        ]);

        ChatGPTExplanation::create([
            'question' => $questionText,
            'wrong_answer' => 'goes',
            'correct_answer' => 'go',
            'language' => 'uk',
            'explanation' => 'Форма go вживається з I.',
        ]);

        $test = Test::create([
            'name' => 'Simple test',
            'slug' => 'simple-test',
            'filters' => [],
            'questions' => [$firstQuestion->id, $secondQuestion->id],
        ]);

        $response = $this->deleteJson(route('saved-test.question.destroy', [$test->slug, $firstQuestion->id]));

        $response->assertOk();

        $this->assertDatabaseMissing('questions', ['id' => $firstQuestion->id]);
        $this->assertDatabaseHas('questions', ['id' => $secondQuestion->id]);
        $this->assertDatabaseHas('chatgpt_explanations', ['question' => $questionText]);

        $updatedTest = $test->fresh();
        $this->assertNotContains($firstQuestion->id, $updatedTest->questions);
        $this->assertContains($secondQuestion->id, $updatedTest->questions);
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
                $table->string('level', 2)->nullable();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('tags')) {
            Schema::create('tags', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('category')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('question_tag')) {
            Schema::create('question_tag', function (Blueprint $table) {
                $table->unsignedBigInteger('question_id');
                $table->unsignedBigInteger('tag_id');
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
                $table->string('marker');
                $table->unsignedBigInteger('question_id');
                $table->unsignedBigInteger('option_id');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('question_hints')) {
            Schema::create('question_hints', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('question_id');
                $table->string('provider');
                $table->string('locale', 5);
                $table->text('hint');
                $table->timestamps();
                $table->unique(['question_id', 'provider', 'locale']);
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

        if (! Schema::hasTable('tests')) {
            Schema::create('tests', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->json('filters')->nullable();
                $table->json('questions');
                $table->text('description')->nullable();
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

    private function resetData(): void
    {
        foreach ([
            'chatgpt_explanations',
            'question_hints',
            'verb_hints',
            'question_variants',
            'question_option_question',
            'question_answers',
            'question_options',
            'questions',
            'categories',
            'tests',
            'question_tag',
            'tags',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }
    }
}
