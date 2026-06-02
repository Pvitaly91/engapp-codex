<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('text_blocks', 'page_category_id')) {
            Schema::table('text_blocks', function (Blueprint $table) {
                $table->foreignId('page_category_id')
                    ->nullable()
                    ->after('page_id')
                    ->constrained('page_categories')
                    ->cascadeOnDelete();
            });
        }

        Schema::table('text_blocks', function (Blueprint $table) {
            $table->dropForeign(['page_id']);
        });

        DB::statement('ALTER TABLE text_blocks MODIFY page_id BIGINT UNSIGNED NULL');

        Schema::table('text_blocks', function (Blueprint $table) {
            $table->foreign('page_id')
                ->references('id')
                ->on('pages')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('text_blocks', function (Blueprint $table) {
            $table->dropForeign(['page_id']);

            if (Schema::hasColumn('text_blocks', 'page_category_id')) {
                $table->dropForeign(['page_category_id']);
                $table->dropColumn('page_category_id');
            }
        });

        DB::table('text_blocks')->whereNull('page_id')->delete();

        DB::statement('ALTER TABLE text_blocks MODIFY page_id BIGINT UNSIGNED NOT NULL');

        Schema::table('text_blocks', function (Blueprint $table) {
            $table->foreign('page_id')
                ->references('id')
                ->on('pages')
                ->cascadeOnDelete();
        });
    }
};
