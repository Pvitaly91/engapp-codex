<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_sync_states', function (Blueprint $table): void {
            $table->id();
            $table->string('domain')->unique();
            $table->string('last_successful_ref')->nullable();
            $table->timestamp('last_successful_applied_at')->nullable();
            $table->string('last_attempted_base_ref')->nullable();
            $table->string('last_attempted_head_ref')->nullable();
            $table->string('last_attempted_status')->nullable();
            $table->timestamp('last_attempted_at')->nullable();
            $table->json('last_attempt_meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_sync_states');
    }
};
