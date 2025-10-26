<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('pages', 'text')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->dropColumn('text');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('pages', 'text')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->text('text')->nullable();
            });
        }
    }
};
