<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('text_blocks', function (Blueprint $table) {
            if (! Schema::hasColumn('text_blocks', 'uuid')) {
                $table->uuid('uuid')->nullable()->after('id')->unique();
            }
        });

        if (Schema::hasColumn('text_blocks', 'uuid')) {
            DB::table('text_blocks')
                ->whereNull('uuid')
                ->orderBy('id')
                ->chunkById(100, function ($rows) {
                    foreach ($rows as $row) {
                        DB::table('text_blocks')
                            ->where('id', $row->id)
                            ->update(['uuid' => (string) Str::uuid()]);
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::table('text_blocks', function (Blueprint $table) {
            if (Schema::hasColumn('text_blocks', 'uuid')) {
                $table->dropUnique(['uuid']);
                $table->dropColumn('uuid');
            }
        });
    }
};
