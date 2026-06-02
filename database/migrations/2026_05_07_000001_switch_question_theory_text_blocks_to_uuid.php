<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Re-key the question_theory_text_blocks pivot table from question_id to
 * question_uuid. The id is reset every time a V3 question seeder is rerun
 * (drop & recreate semantics), which silently broke every theory link;
 * the uuid is stable across reseeds because seeders use a deterministic
 * uuid_namespace + persistent UUID resolver.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_theory_text_blocks', function (Blueprint $table) {
            $table->string('question_uuid', 36)->nullable()->after('id');
        });

        // Backfill the new column from the existing question_id join.
        DB::statement('UPDATE question_theory_text_blocks qttb
            INNER JOIN questions q ON q.id = qttb.question_id
            SET qttb.question_uuid = q.uuid');

        // Drop orphan rows whose questions no longer exist (couldn't backfill).
        DB::table('question_theory_text_blocks')->whereNull('question_uuid')->delete();

        // Drop the old constraints + column.
        Schema::table('question_theory_text_blocks', function (Blueprint $table) {
            $table->dropForeign(['question_id']);
            $table->dropUnique('qttb_unique_question_block');
            $table->dropColumn('question_id');
        });

        // Tighten the new column + indexes.
        DB::statement('ALTER TABLE question_theory_text_blocks
            MODIFY question_uuid VARCHAR(36) NOT NULL');

        Schema::table('question_theory_text_blocks', function (Blueprint $table) {
            $table->index('question_uuid', 'qttb_question_uuid_idx');
            $table->unique(
                ['question_uuid', 'text_block_uuid'],
                'qttb_unique_question_block_uuid'
            );
        });
    }

    public function down(): void
    {
        Schema::table('question_theory_text_blocks', function (Blueprint $table) {
            $table->unsignedBigInteger('question_id')->nullable()->after('id');
        });

        DB::statement('UPDATE question_theory_text_blocks qttb
            INNER JOIN questions q ON q.uuid = qttb.question_uuid
            SET qttb.question_id = q.id');

        DB::table('question_theory_text_blocks')->whereNull('question_id')->delete();

        Schema::table('question_theory_text_blocks', function (Blueprint $table) {
            $table->dropUnique('qttb_unique_question_block_uuid');
            $table->dropIndex('qttb_question_uuid_idx');
            $table->dropColumn('question_uuid');
        });

        DB::statement('ALTER TABLE question_theory_text_blocks
            MODIFY question_id BIGINT UNSIGNED NOT NULL');

        Schema::table('question_theory_text_blocks', function (Blueprint $table) {
            $table->unique(
                ['question_id', 'text_block_uuid'],
                'qttb_unique_question_block'
            );
            $table->foreign('question_id')
                ->references('id')
                ->on('questions')
                ->cascadeOnDelete();
        });
    }
};
