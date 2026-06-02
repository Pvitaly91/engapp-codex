<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_tree_variants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_base')->default(false);
            $table->timestamps();
        });

        // Add variant_id to site_tree_items
        Schema::table('site_tree_items', function (Blueprint $table) {
            $table->foreignId('variant_id')->nullable()->after('id')->constrained('site_tree_variants')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('site_tree_items', function (Blueprint $table) {
            $table->dropForeign(['variant_id']);
            $table->dropColumn('variant_id');
        });

        Schema::dropIfExists('site_tree_variants');
    }
};
