<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_review_results', function (Blueprint $table) {
            $table->json('original_tags')->nullable()->after('tags');
        });
    }

    public function down(): void
    {
        Schema::table('question_review_results', function (Blueprint $table) {
            $table->dropColumn('original_tags');
        });
    }
};
