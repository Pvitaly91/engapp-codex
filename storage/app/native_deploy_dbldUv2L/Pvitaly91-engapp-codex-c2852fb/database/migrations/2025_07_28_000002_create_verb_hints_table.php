<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verb_hints', function (Blueprint $table) {
            $table->id();
            $table->string('marker');
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->foreignId('option_id')->constrained('question_options')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['question_id','marker','option_id'],'verb_hint_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verb_hints');
    }
};
