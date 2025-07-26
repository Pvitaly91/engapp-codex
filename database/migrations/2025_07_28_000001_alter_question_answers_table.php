<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_answers', function (Blueprint $table) {
            if (Schema::hasColumn('question_answers', 'marker') && Schema::hasColumn('question_answers', 'answer')) {
                $table->dropUnique('question_marker_answer_unique');
            }
        });
        Schema::table('question_answers', function (Blueprint $table) {
            if (Schema::hasColumn('question_answers', 'answer')) {
                $table->dropColumn('answer');
            }
            if (!Schema::hasColumn('question_answers', 'option_id')) {
                $table->foreignId('option_id')->after('question_id')->constrained('question_options')->cascadeOnDelete();
            }
        });
        Schema::table('question_answers', function (Blueprint $table) {
            if (Schema::hasColumn('question_answers', 'marker') && Schema::hasColumn('question_answers', 'option_id')) {
                $table->unique(['question_id','marker','option_id'],'question_marker_option_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('question_answers', function (Blueprint $table) {
            $table->dropUnique('question_marker_option_unique');
            $table->dropConstrainedForeignId('option_id');
            $table->string('answer')->nullable();
        });
        Schema::table('question_answers', function (Blueprint $table) {
            $table->unique(['question_id','marker','answer'],'question_marker_answer_unique');
        });
    }
};
