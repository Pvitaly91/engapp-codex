<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_theory_text_blocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->string('text_block_uuid', 36);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['question_id', 'text_block_uuid'], 'qttb_unique_question_block');
            $table->index('text_block_uuid', 'qttb_text_block_uuid_idx');

            $table->foreign('question_id')
                ->references('id')
                ->on('questions')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_theory_text_blocks');
    }
};
