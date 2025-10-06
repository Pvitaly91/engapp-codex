<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('page_blocks') && ! Schema::hasColumn('page_blocks', 'seeder')) {
            Schema::table('page_blocks', function (Blueprint $table) {
                $table->string('seeder')->nullable()->after('position');
                $table->index('seeder');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('page_blocks') && Schema::hasColumn('page_blocks', 'seeder')) {
            Schema::table('page_blocks', function (Blueprint $table) {
                $table->dropIndex(['seeder']);
                $table->dropColumn('seeder');
            });
        }
    }
};
