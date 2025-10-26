<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('question_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->string('marker'); // a1, a2
            $table->string('answer');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('question_answers');
    }
};
