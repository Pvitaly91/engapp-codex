<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('page_categories')) {
            Schema::create('page_categories', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('title');
                $table->string('language', 8)->default('uk');
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('pages', 'page_category_id')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->foreignId('page_category_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('page_categories')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pages', 'page_category_id')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->dropConstrainedForeignId('page_category_id');
            });
        }

        Schema::dropIfExists('page_categories');
    }
};
