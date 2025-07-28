<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
            $table->unique(['question', 'wrong_answer', 'correct_answer', 'language'], 'chatgpt_explanations_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatgpt_explanations');
    }
};
