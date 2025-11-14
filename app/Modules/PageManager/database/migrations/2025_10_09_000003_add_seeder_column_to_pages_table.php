<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pages', 'seeder')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->string('seeder')->nullable()->after('text');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pages', 'seeder')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->dropColumn('seeder');
            });
        }
    }
};
