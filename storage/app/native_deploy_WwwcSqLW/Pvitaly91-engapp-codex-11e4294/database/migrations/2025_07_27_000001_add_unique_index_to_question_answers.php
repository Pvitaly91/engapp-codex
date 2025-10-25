<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_answers', function (Blueprint $table) {
            $table->unique(['question_id', 'marker', 'answer'], 'question_marker_answer_unique');
        });
    }

    public function down(): void
    {
        Schema::table('question_answers', function (Blueprint $table) {
            $table->dropUnique('question_marker_answer_unique');
        });
    }
};
