<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('question_answers', function (Blueprint $table) {
            $table->string('verb_hint')->nullable()->after('answer');
        });
    }
    public function down(): void {
        Schema::table('question_answers', function (Blueprint $table) {
            $table->dropColumn('verb_hint');
        });
    }
};
