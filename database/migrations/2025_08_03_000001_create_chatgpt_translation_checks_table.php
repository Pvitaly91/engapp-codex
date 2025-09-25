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
            $table->string('unique_hash', 64)
                ->storedAs("sha2(concat_ws(':', `original`, `reference`, `user_text`, `language`), 256)");
            $table->unique('unique_hash', 'chatgpt_translation_checks_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatgpt_translation_checks');
    }
};
