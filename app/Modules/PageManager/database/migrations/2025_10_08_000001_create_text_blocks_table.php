<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('text_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 8)->default('uk');
            $table->string('type', 32)->default('box');
            $table->string('column', 32)->nullable();
            $table->string('heading')->nullable();
            $table->string('css_class')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->longText('body')->nullable();
            $table->string('seeder')->nullable();
            $table->timestamps();

            $table->index(['page_id', 'column']);
            $table->index('seeder');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('text_blocks');
    }
};
