<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        });

        DB::statement(
            'ALTER TABLE `chatgpt_translation_checks` ADD UNIQUE KEY `chatgpt_translation_checks_unique` ' .
            '(`original`(150), `reference`(150), `user_text`(150), `language`(50))'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('chatgpt_translation_checks');
    }
};
