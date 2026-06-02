<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_polyglot_answer_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('course_slug');
            $table->string('lesson_slug');
            $table->string('question_uuid')->nullable();
            $table->decimal('rating', 3, 2);
            $table->boolean('is_correct')->nullable();
            $table->json('answer_payload')->nullable();
            $table->string('client_attempt_uuid')->nullable();
            $table->timestamp('answered_at');
            $table->timestamps();

            $table->index('user_id', 'upaa_user_idx');
            $table->index('course_slug', 'upaa_course_idx');
            $table->index('lesson_slug', 'upaa_lesson_idx');
            $table->index('question_uuid', 'upaa_question_idx');
            $table->unique(['user_id', 'client_attempt_uuid'], 'upaa_user_client_attempt_unique');
            $table->index(['user_id', 'course_slug', 'lesson_slug', 'answered_at'], 'upaa_user_course_lesson_answered_idx');
            $table->foreign('user_id', 'upaa_user_fk')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_polyglot_answer_attempts');
    }
};
