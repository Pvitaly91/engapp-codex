<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Models\QuestionVariant;
use App\Models\Source;
use App\Models\Tag;
use App\Models\VerbHint;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class QuestionExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();
        Schema::dropAllTables();
        Schema::enableForeignKeyConstraints();

        $this->createTables();
    }

    public function test_question_update_exports_related_data_to_json(): void
    {
        $this->cleanExportDirectory();

        $category = Category::create(['name' => 'Grammar']);
        $source = Source::create(['name' => 'Manual']);
        $tag = Tag::create(['name' => 'present-simple', 'category' => 'tense']);
        $option = QuestionOption::create(['option' => 'runs']);

        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'She {a1} every morning.',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
            'source_id' => $source->id,
            'flag' => 0,
        ]);

        $question->tags()->attach($tag->id);
        $question->options()->attach($option->id, ['flag' => null]);

        QuestionAnswer::create([
            'question_id' => $question->id,
            'marker' => 'A1',
            'option_id' => $option->id,
        ]);

        VerbHint::create([
            'question_id' => $question->id,
            'marker' => 'A1',
            'option_id' => $option->id,
        ]);

        QuestionVariant::create([
            'question_id' => $question->id,
            'text' => 'She runs every morning.',
        ]);

        QuestionHint::create([
            'question_id' => $question->id,
            'provider' => 'system',
            'locale' => 'en',
            'hint' => 'Use the third person singular.',
        ]);

        ChatGPTExplanation::create([
            'question' => 'She {a1} after breakfast.',
            'wrong_answer' => 'eat',
            'correct_answer' => 'eats',
            'language' => 'uk',
            'explanation' => 'Use the third person singular form.',
        ]);

        $question->update(['question' => 'She {a1} after breakfast.']);

        $payload = $this->readExport($question);

        $this->assertEquals($question->uuid, $payload['question']['uuid']);
        $this->assertSame('She {a1} after breakfast.', $payload['question']['question']);
        $this->assertCount(1, $payload['tags']);
        $this->assertCount(1, $payload['options']);
        $this->assertCount(1, $payload['answers']);
        $this->assertCount(1, $payload['verb_hints']);
        $this->assertCount(1, $payload['variants']);
        $this->assertCount(1, $payload['hints']);
        $this->assertCount(1, $payload['chatgpt_explanations']);

        File::delete($this->exportPathFor($question));
    }

    public function test_updating_question_hint_exports_json_snapshot(): void
    {
        $this->cleanExportDirectory();

        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'Initial question text',
            'difficulty' => 1,
        ]);

        $hint = QuestionHint::create([
            'question_id' => $question->id,
            'provider' => 'system',
            'locale' => 'en',
            'hint' => 'Old hint',
        ]);

        $this->putJson(route('question-hints.update', $hint), [
            'hint' => 'Updated hint text',
        ])->assertOk();

        $payload = $this->readExport($question);

        $this->assertSame('Updated hint text', $payload['hints'][0]['hint']);

        File::delete($this->exportPathFor($question));
    }

    public function test_creating_and_deleting_verb_hint_refreshes_snapshot(): void
    {
        $this->cleanExportDirectory();

        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => 'Verb hint question',
            'difficulty' => 1,
        ]);

        $this->postJson(route('verb-hints.store'), [
            'question_id' => $question->id,
            'marker' => 'a1',
            'hint' => 'run',
        ])->assertOk();

        $payloadAfterCreate = $this->readExport($question);

        $this->assertCount(1, $payloadAfterCreate['verb_hints']);
        $this->assertSame('run', $payloadAfterCreate['verb_hints'][0]['option']['option']);

        $verbHintId = VerbHint::query()->where('question_id', $question->id)->value('id');

        $this->deleteJson(route('verb-hints.destroy', $verbHintId))
            ->assertOk()
            ->assertJsonPath('data.id', $question->id);

        $payloadAfterDelete = $this->readExport($question);

        $this->assertSame([], $payloadAfterDelete['verb_hints']);

        File::delete($this->exportPathFor($question));
    }

    private function createTables(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('question');
            $table->unsignedTinyInteger('difficulty')->default(1);
            $table->string('level')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->integer('flag')->default(0);
            $table->timestamps();
        });

        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->string('option');
            $table->timestamps();
        });

        Schema::create('question_option_question', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('option_id');
            $table->tinyInteger('flag')->nullable();
            $table->unique(['question_id', 'option_id', 'flag']);
        });

        Schema::create('question_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->string('marker');
            $table->unsignedBigInteger('option_id')->nullable();
            $table->timestamps();
        });

        Schema::create('verb_hints', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->string('marker');
            $table->unsignedBigInteger('option_id')->nullable();
            $table->timestamps();
        });

        Schema::create('question_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->text('text');
            $table->timestamps();
        });

        Schema::create('question_hints', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->string('provider');
            $table->string('locale', 8);
            $table->text('hint');
            $table->timestamps();
        });

        Schema::create('question_tag', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tag_id');
            $table->unsignedBigInteger('question_id');
            $table->timestamps();
            $table->unique(['tag_id', 'question_id']);
        });

        Schema::create('chatgpt_explanations', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->text('wrong_answer');
            $table->text('correct_answer');
            $table->string('language');
            $table->text('explanation');
            $table->timestamps();
        });
    }

    private function exportDirectory(): string
    {
        return database_path('seeders/questions');
    }

    private function exportPathFor(Question $question): string
    {
        return $this->exportDirectory() . DIRECTORY_SEPARATOR . $question->uuid . '.json';
    }

    private function cleanExportDirectory(): void
    {
        $directory = $this->exportDirectory();

        if (File::exists($directory)) {
            File::cleanDirectory($directory);
        }
    }

    private function readExport(Question $question): array
    {
        $path = $this->exportPathFor($question);

        $this->assertFileExists($path);

        return json_decode(File::get($path), true);
    }
}

