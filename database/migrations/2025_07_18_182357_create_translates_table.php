<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('translates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('word_id')->constrained()->onDelete('cascade');
            $table->string('lang', 8)->default('uk'); // наприклад: uk, ru, pl...
            $table->string('translation'); // переклад слова
            $table->timestamps();
            
            $table->unique(['word_id', 'lang']); // 1 переклад на 1 мову для 1 слова
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translates');
    }
};
