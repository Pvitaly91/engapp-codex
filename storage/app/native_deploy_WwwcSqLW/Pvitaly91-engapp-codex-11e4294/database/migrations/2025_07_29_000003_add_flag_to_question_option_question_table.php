<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_option_question', function (Blueprint $table) {
            // drop constraints that rely on the old unique index so we can rebuild it
            $table->dropForeign(['question_id']);
            $table->dropForeign(['option_id']);
            $table->dropUnique('question_option_question_question_id_option_id_unique');
        });

        Schema::table('question_option_question', function (Blueprint $table) {
            $table->tinyInteger('flag')->nullable()->after('option_id');
            $table->unique(['question_id', 'option_id', 'flag'], 'question_option_question_question_id_option_id_flag_unique');

            $table->foreign('question_id')->references('id')->on('questions')->cascadeOnDelete();
            $table->foreign('option_id')->references('id')->on('question_options')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('question_option_question', function (Blueprint $table) {
            $table->dropForeign(['question_id']);
            $table->dropForeign(['option_id']);
            $table->dropUnique('question_option_question_question_id_option_id_flag_unique');
            $table->dropColumn('flag');
            $table->unique(['question_id', 'option_id']);

            $table->foreign('question_id')->references('id')->on('questions')->cascadeOnDelete();
            $table->foreign('option_id')->references('id')->on('question_options')->cascadeOnDelete();
        });
    }
};
