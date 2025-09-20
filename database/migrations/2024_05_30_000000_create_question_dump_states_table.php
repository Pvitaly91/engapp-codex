<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_dump_states', function (Blueprint $table) {
            $table->id();
            $table->uuid('question_uuid')->unique();
            $table->string('file_hash', 64)->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_dump_states');
    }
};
