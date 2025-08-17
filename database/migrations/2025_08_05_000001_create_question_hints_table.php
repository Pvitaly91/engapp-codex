<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('question_hints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->string('locale',5);
            $table->text('hint');
            $table->timestamps();
            $table->unique(['question_id','provider','locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_hints');
    }
};
