<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chatgpt_explanations', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->text('wrong_answer');
            $table->text('correct_answer');
            $table->string('language')->default('uk');
            $table->text('explanation');
            $table->timestamps();
        });

        DB::statement(
            'ALTER TABLE `chatgpt_explanations` ADD UNIQUE KEY `chatgpt_explanations_unique` ' .
            '(`question`(100), `wrong_answer`(100), `correct_answer`(100), `language`(50))'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('chatgpt_explanations');
    }
};
