<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_options', function (Blueprint $table) {
            if (Schema::hasColumn('question_options', 'question_id')) {
                $table->dropForeign(['question_id']);
                $table->dropColumn('question_id');
            }
            if (!Schema::hasColumn('question_options', 'option')) {
                $table->string('option');
            }
            $table->unique('option');
        });
    }

    public function down(): void
    {
        Schema::table('question_options', function (Blueprint $table) {
            $table->dropUnique(['option']);
            if (!Schema::hasColumn('question_options', 'question_id')) {
                $table->foreignId('question_id')->after('id')->constrained('questions')->cascadeOnDelete();
            }
        });
    }
};
