<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theory_variants', function (Blueprint $table) {
            $table->id();
            $table->string('variantable_type');
            $table->unsignedBigInteger('variantable_id');
            $table->string('locale', 12);
            $table->string('variant_key');
            $table->string('label');
            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->string('status')->default('ready');
            $table->json('payload');
            $table->string('source_hash')->nullable();
            $table->string('prompt_version')->nullable();
            $table->string('seeder_class')->nullable();
            $table->timestamps();

            $table->unique(
                ['variantable_type', 'variantable_id', 'locale', 'variant_key'],
                'theory_variants_variantable_locale_key_unique'
            );
            $table->index(
                ['variantable_type', 'variantable_id', 'locale', 'status'],
                'theory_variants_lookup_index'
            );
        });

        Schema::create('theory_variant_selections', function (Blueprint $table) {
            $table->id();
            $table->string('variantable_type');
            $table->unsignedBigInteger('variantable_id');
            $table->string('locale', 12);
            $table->foreignId('theory_variant_id')
                ->nullable()
                ->constrained('theory_variants')
                ->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['variantable_type', 'variantable_id', 'locale'],
                'theory_variant_selections_variantable_locale_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theory_variant_selections');
        Schema::dropIfExists('theory_variants');
    }
};
