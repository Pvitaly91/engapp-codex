<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verb_hints', function (Blueprint $table) {
            if (! Schema::hasColumn('verb_hints', 'locale')) {
                $table->string('locale', 5)->default('')->after('marker');
            }
        });

        Schema::table('verb_hints', function (Blueprint $table) {
            $legacyUnique = $this->uniqueIndexName(['question_id', 'marker', 'option_id']);

            if ($legacyUnique !== null) {
                $table->dropUnique($legacyUnique);
            }
        });

        Schema::table('verb_hints', function (Blueprint $table) {
            if ($this->uniqueIndexName(['question_id', 'marker', 'locale', 'option_id']) === null) {
                $table->unique(['question_id', 'marker', 'locale', 'option_id'], 'verb_hint_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('verb_hints', function (Blueprint $table) {
            $localizedUnique = $this->uniqueIndexName(['question_id', 'marker', 'locale', 'option_id']);

            if ($localizedUnique !== null) {
                $table->dropUnique($localizedUnique);
            }
        });

        Schema::table('verb_hints', function (Blueprint $table) {
            if ($this->uniqueIndexName(['question_id', 'marker', 'option_id']) === null) {
                $table->unique(['question_id', 'marker', 'option_id'], 'verb_hint_unique');
            }
        });

        Schema::table('verb_hints', function (Blueprint $table) {
            if (Schema::hasColumn('verb_hints', 'locale')) {
                $table->dropColumn('locale');
            }
        });
    }

    private function uniqueIndexName(array $columns): ?string
    {
        $expected = array_map('strtolower', $columns);
        $indexes = [];

        foreach (DB::select('SHOW INDEX FROM verb_hints') as $index) {
            if ((int) $index->Non_unique !== 0 || $index->Key_name === 'PRIMARY') {
                continue;
            }

            $indexes[$index->Key_name][(int) $index->Seq_in_index] = strtolower((string) $index->Column_name);
        }

        foreach ($indexes as $name => $indexColumns) {
            ksort($indexColumns);

            if (array_values($indexColumns) === $expected) {
                return $name;
            }
        }

        return null;
    }
};
