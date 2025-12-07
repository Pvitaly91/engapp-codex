<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_tree_items', function (Blueprint $table) {
            $table->string('link_method')->nullable()->after('linked_page_url')->comment('Method used to establish link: exact_title, seeder_name, slug_match, manual');
        });
    }

    public function down(): void
    {
        Schema::table('site_tree_items', function (Blueprint $table) {
            $table->dropColumn('link_method');
        });
    }
};
