<?php

namespace Tests\Feature\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait EnsuresQuestionSchema
{
    protected function ensureQuestionSchema(): void
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

        if (! Schema::hasTable('chatgpt_explanations')) {
            Schema::create('chatgpt_explanations', function (Blueprint $table) {
                $table->id();
                $table->text('question');
                $table->text('wrong_answer');
                $table->text('correct_answer');
                $table->string('language')->default('uk');
                $table->text('explanation');
                $table->timestamps();
            });
        }
    }
}
