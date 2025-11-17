<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('backup_branches', function (Blueprint $table) {
            $table->string('action')->nullable()->after('pushed_at'); // deploy, push, backup, etc.
            $table->text('description')->nullable()->after('action');
            $table->timestamp('used_at')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('backup_branches', function (Blueprint $table) {
            $table->dropColumn(['action', 'description', 'used_at']);
        });
    }
};
