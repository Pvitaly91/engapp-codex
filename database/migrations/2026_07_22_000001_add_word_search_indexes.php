<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Older dumps can contain the tables without the indexes declared by
        // the original migrations. Keep this migration safe for fresh installs
        // where those indexes already exist.
        if (! Schema::hasIndex('words', ['word'])) {
            Schema::table('words', function (Blueprint $table) {
                $table->unique('word', 'words_word_search_unique');
            });
        }

        if (! Schema::hasIndex('translates', ['word_id', 'lang'])) {
            Schema::table('translates', function (Blueprint $table) {
                $table->index(['word_id', 'lang'], 'translates_word_locale_search_index');
            });
        }

        if (! Schema::hasIndex('translates', ['lang', 'translation', 'word_id'])) {
            Schema::table('translates', function (Blueprint $table) {
                $table->index(
                    ['lang', 'translation', 'word_id'],
                    'translates_locale_translation_word_search_index'
                );
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('translates', 'translates_locale_translation_word_search_index')) {
            Schema::table('translates', function (Blueprint $table) {
                $table->dropIndex('translates_locale_translation_word_search_index');
            });
        }

        if (Schema::hasIndex('translates', 'translates_word_locale_search_index')) {
            Schema::table('translates', function (Blueprint $table) {
                $table->dropIndex('translates_word_locale_search_index');
            });
        }

        if (Schema::hasIndex('words', 'words_word_search_unique')) {
            Schema::table('words', function (Blueprint $table) {
                $table->dropUnique('words_word_search_unique');
            });
        }
    }
};
