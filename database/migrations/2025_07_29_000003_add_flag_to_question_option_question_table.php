<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_option_question', function (Blueprint $table) {
            $table->tinyInteger('flag')->nullable()->after('option_id');
            $table->dropUnique('question_option_question_question_id_option_id_unique');
            $table->unique(['question_id', 'option_id', 'flag']);
        });
    }

    public function down(): void
    {
        Schema::table('question_option_question', function (Blueprint $table) {
            $table->dropUnique(['question_id', 'option_id', 'flag']);
            $table->dropColumn('flag');
            $table->unique(['question_id', 'option_id']);
        });
    }
};
