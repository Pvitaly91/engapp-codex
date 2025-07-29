<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chatgpt_translation_checks', function (Blueprint $table) {
            $table->id();
            $table->text('original');
            $table->text('reference');
            $table->text('user_text');
            $table->string('language')->default('uk');
            $table->boolean('is_correct');
            $table->text('explanation')->nullable();
            $table->timestamps();
            $table->unique(['original', 'reference', 'user_text', 'language'], 'chatgpt_translation_checks_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatgpt_translation_checks');
    }
};
