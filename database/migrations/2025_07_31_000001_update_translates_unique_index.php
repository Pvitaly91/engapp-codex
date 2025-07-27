<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('translates', function (Blueprint $table) {
            $table->dropUnique(['word_id', 'lang']);
            $table->unique(['word_id', 'lang', 'translation']);
        });
    }

    public function down(): void
    {
        Schema::table('translates', function (Blueprint $table) {
            $table->dropUnique(['word_id', 'lang', 'translation']);
            $table->unique(['word_id', 'lang']);
        });
    }
};
