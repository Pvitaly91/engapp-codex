<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_tree_items', function (Blueprint $table) {
            $table->string('linked_page_title')->nullable()->after('title');
            $table->string('linked_page_url')->nullable()->after('linked_page_title');
        });
    }

    public function down(): void
    {
        Schema::table('site_tree_items', function (Blueprint $table) {
            $table->dropColumn(['linked_page_title', 'linked_page_url']);
        });
    }
};
