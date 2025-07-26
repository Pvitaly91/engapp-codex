<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_answers', function (Blueprint $table) {
            $table->unique(['question_id', 'marker', 'option_id'], 'question_marker_option_unique');
        });
    }

    public function down(): void
    {
        Schema::table('question_answers', function (Blueprint $table) {
            $table->dropUnique('question_marker_option_unique');
        });
    }
};
