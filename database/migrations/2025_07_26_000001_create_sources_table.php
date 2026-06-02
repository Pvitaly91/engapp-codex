<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sources', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('source_id')->nullable()->after('flag')->constrained('sources')->nullOnDelete();
            $table->dropColumn('source');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('source')->nullable()->after('flag');
            $table->dropConstrainedForeignId('source_id');
        });

        Schema::dropIfExists('sources');
    }
};
