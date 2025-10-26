<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_grammar_tests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->json('filters');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('saved_grammar_test_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saved_grammar_test_id')->constrained('saved_grammar_tests')->cascadeOnDelete();
            $table->uuid('question_uuid');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index('question_uuid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_grammar_test_questions');
        Schema::dropIfExists('saved_grammar_tests');
    }
};
