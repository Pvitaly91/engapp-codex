<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('page_slug');
            $table->string('locale', 5)->default('uk');
            $table->string('area')->default('main');
            $table->string('type')->default('text');
            $table->string('label')->nullable();
            $table->json('content')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['page_slug', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_blocks');
    }
};
