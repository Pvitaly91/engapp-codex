<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_operation_runs', function (Blueprint $table): void {
            if (! Schema::hasColumn('content_operation_runs', 'replayed_from_run_id')) {
                $table->unsignedBigInteger('replayed_from_run_id')->nullable()->after('id');
                $table->index('replayed_from_run_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('content_operation_runs', function (Blueprint $table): void {
            if (Schema::hasColumn('content_operation_runs', 'replayed_from_run_id')) {
                $table->dropIndex(['replayed_from_run_id']);
                $table->dropColumn('replayed_from_run_id');
            }
        });
    }
};
