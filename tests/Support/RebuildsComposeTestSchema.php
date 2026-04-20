<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait RebuildsComposeTestSchema
{
    protected function rebuildComposeTestSchema(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'seed_runs',
            'saved_grammar_test_questions',
            'saved_grammar_tests',
            'tests',
            'chatgpt_explanations',
            'question_hints',
            'question_variants',
            'verb_hints',
            'question_answers',
            'question_marker_tag',
            'question_option_question',
            'question_options',
            'question_tag',
            'questions',
            'sources',
            'categories',
            'pages',
            'page_categories',
            'tag_word',
            'translates',
            'words',
            'tags',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('category')->nullable();
            $table->timestamps();
        });

        Schema::create('page_categories', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('language')->nullable();
            $table->string('type')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('seeder')->nullable();
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_category_id')->nullable();
            $table->string('slug');
            $table->string('title');
            $table->text('text')->nullable();
            $table->string('type')->nullable();
            $table->string('seeder')->nullable();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('sources', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->text('question');
            $table->unsignedTinyInteger('difficulty')->default(1);
            $table->string('level', 8)->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedTinyInteger('flag')->default(0);
            $table->string('type')->nullable();
            $table->json('options_by_marker')->nullable();
            $table->string('theory_text_block_uuid', 36)->nullable();
            $table->string('seeder')->nullable();
            $table->timestamps();
        });

        Schema::create('question_tag', function (Blueprint $table) {
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('tag_id');
            $table->unique(['question_id', 'tag_id']);
        });

        Schema::create('question_marker_tag', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('tag_id');
            $table->string('marker');
            $table->timestamps();
            $table->unique(['question_id', 'tag_id', 'marker'], 'question_marker_tag_unique');
        });

        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->string('option')->unique();
            $table->timestamps();
        });

        Schema::create('question_option_question', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('option_id');
            $table->tinyInteger('flag')->nullable();
            $table->unique(['question_id', 'option_id', 'flag'], 'qoq_question_option_flag_unique');
        });

        Schema::create('question_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('option_id');
            $table->string('marker');
            $table->timestamps();
            $table->unique(['question_id', 'marker', 'option_id'], 'question_marker_option_unique');
        });

        Schema::create('verb_hints', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('option_id')->nullable();
            $table->string('marker')->nullable();
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
            $table->string('locale', 5);
            $table->text('hint');
            $table->timestamps();
            $table->unique(['question_id', 'provider', 'locale']);
        });

        Schema::create('chatgpt_explanations', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->text('wrong_answer');
            $table->text('correct_answer');
            $table->string('language')->default('uk');
            $table->text('explanation');
            $table->timestamps();
            $table->unique(
                ['question', 'wrong_answer', 'correct_answer', 'language'],
                'chatgpt_explanations_unique'
            );
        });

        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->json('filters')->nullable();
            $table->json('questions');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('saved_grammar_tests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->json('filters')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('saved_grammar_test_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('saved_grammar_test_id');
            $table->uuid('question_uuid');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['saved_grammar_test_id', 'question_uuid'], 'saved_grammar_test_questions_unique');
        });

        Schema::create('words', function (Blueprint $table) {
            $table->id();
            $table->string('word');
            $table->string('type')->nullable();
            $table->timestamps();
        });

        Schema::create('translates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('word_id');
            $table->string('lang', 8);
            $table->string('translation');
            $table->timestamps();
        });

        Schema::create('tag_word', function (Blueprint $table) {
            $table->unsignedBigInteger('tag_id');
            $table->unsignedBigInteger('word_id');
            $table->unique(['tag_id', 'word_id']);
        });

        Schema::create('seed_runs', function (Blueprint $table) {
            $table->id();
            $table->string('class_name')->unique();
            $table->timestamp('ran_at')->nullable();
        });
    }
}
