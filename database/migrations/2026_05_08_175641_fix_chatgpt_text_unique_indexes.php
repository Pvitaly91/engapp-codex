<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->replaceUniqueIndex(
            table: 'chatgpt_explanations',
            index: 'chatgpt_explanations_unique',
            prefixSql: '`question`(100), `wrong_answer`(100), `correct_answer`(100), `language`(50)'
        );

        $this->replaceUniqueIndex(
            table: 'chatgpt_translation_checks',
            index: 'chatgpt_translation_checks_unique',
            prefixSql: '`original`(150), `reference`(150), `user_text`(150), `language`(50)'
        );
    }

    public function down(): void
    {
        // Restoring the old HASH index would reintroduce dumps that fail on MySQL imports.
    }

    private function replaceUniqueIndex(string $table, string $index, string $prefixSql): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $this->dropIndexIfExists($table, $index);

        if (Schema::hasColumn($table, 'unique_hash')) {
            DB::statement("ALTER TABLE `{$table}` DROP COLUMN `unique_hash`");
        }

        DB::statement("ALTER TABLE `{$table}` ADD UNIQUE KEY `{$index}` ({$prefixSql})");
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $exists = DB::selectOne(
            'SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1',
            [$table, $index]
        );

        if ($exists !== null) {
            DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$index}`");
        }
    }
};
