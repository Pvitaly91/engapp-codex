<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_polyglot_lesson_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('course_slug');
            $table->string('lesson_slug');
            $table->unsignedSmallInteger('lesson_index')->nullable();
            $table->unsignedInteger('answered_count')->default(0);
            $table->unsignedSmallInteger('last_100_count')->default(0);
            $table->decimal('last_100_average', 4, 2)->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('unlocked_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('user_id', 'uplp_user_idx');
            $table->index('course_slug', 'uplp_course_idx');
            $table->index('lesson_slug', 'uplp_lesson_idx');
            $table->unique(['user_id', 'course_slug', 'lesson_slug'], 'uplp_user_course_lesson_unique');
            $table->foreign('user_id', 'uplp_user_fk')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_polyglot_lesson_progress');
    }
};
