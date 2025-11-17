<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_usage_history', function (Blueprint $table) {
            $table->id();
            $table->string('branch_name');
            $table->string('action'); // deploy, push, create_and_push, etc.
            $table->text('description')->nullable();
            $table->timestamp('used_at');
            $table->timestamps();

            $table->index(['used_at']);
            $table->index(['branch_name', 'used_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_usage_history');
    }
};
