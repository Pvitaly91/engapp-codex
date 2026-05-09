<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->updateTextColumns('saved_grammar_tests', ['name', 'description']);
        $this->updateTextColumns('tests', ['name', 'description']);
        $this->updateTextColumns('categories', ['name']);
        $this->updateTextColumns('pages', ['title', 'text']);
        $this->updateTextColumns('text_blocks', ['heading', 'body']);
        $this->updateTextColumns('page_categories', ['title', 'name']);
        $this->updateTextColumns('sources', ['name']);

        $this->updateTags();
    }

    public function down(): void
    {
        // Intentionally no-op: reverting public branding would reintroduce the legacy course name.
    }

    private function updateTextColumns(string $table, array $columns): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'id')) {
            return;
        }

        $columns = array_values(array_filter(
            $columns,
            fn (string $column): bool => Schema::hasColumn($table, $column)
        ));

        if ($columns === []) {
            return;
        }

        DB::table($table)
            ->select(array_merge(['id'], $columns))
            ->where(function ($query) use ($columns) {
                foreach ($columns as $column) {
                    $query->orWhere($column, 'like', '%Polyglot%')
                        ->orWhere($column, 'like', '%Poliglot%')
                        ->orWhere($column, 'like', '%Поліглот%')
                        ->orWhere($column, 'like', '%Полиглот%')
                        ->orWhere($column, 'like', '%polihlot%');
                }
            })
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($table, $columns) {
                foreach ($rows as $row) {
                    $updates = [];

                    foreach ($columns as $column) {
                        $original = $row->{$column} ?? null;
                        $updated = $this->publicText($original);

                        if ($updated !== (string) ($original ?? '')) {
                            $updates[$column] = $updated;
                        }
                    }

                    if ($updates !== []) {
                        DB::table($table)->where('id', $row->id)->update($updates);
                    }
                }
            });
    }

    private function updateTags(): void
    {
        if (! Schema::hasTable('tags') || ! Schema::hasColumn('tags', 'id') || ! Schema::hasColumn('tags', 'name')) {
            return;
        }

        DB::table('tags')
            ->select(['id', 'name'])
            ->where('name', 'like', '%Polyglot%')
            ->orWhere('name', 'like', '%Poliglot%')
            ->orWhere('name', 'like', '%Поліглот%')
            ->orWhere('name', 'like', '%Полиглот%')
            ->orWhere('name', 'like', '%polihlot%')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $name = $this->publicText($row->name ?? null);

                    if ($name === '' || $name === (string) ($row->name ?? '')) {
                        continue;
                    }

                    $duplicateExists = DB::table('tags')
                        ->where('name', $name)
                        ->where('id', '!=', $row->id)
                        ->exists();

                    if (! $duplicateExists) {
                        DB::table('tags')->where('id', $row->id)->update(['name' => $name]);
                    }
                }
            });
    }

    private function publicText(mixed $value): string
    {
        $text = trim((string) ($value ?? ''));

        if ($text === '') {
            return '';
        }

        foreach (['A1', 'A2', 'B1', 'B2', 'C1', 'C2'] as $level) {
            $text = str_replace("Polyglot English {$level}", "English Sentence Builder {$level}", $text);
            $text = str_replace("Poliglot English {$level}", "English Sentence Builder {$level}", $text);
            $text = str_replace("Polyglot {$level}", "Sentence Builder {$level}", $text);
            $text = str_replace("Поліглот {$level}", "Sentence Builder {$level}", $text);
            $text = str_replace("Полиглот {$level}", "Sentence Builder {$level}", $text);
        }

        $text = strtr($text, [
            'Polyglot 16' => 'Sentence Builder',
            'Поліглот 16' => 'Sentence Builder',
            'Polyglot English' => 'English Sentence Builder',
            'Poliglot English' => 'English Sentence Builder',
            'Polyglot-style' => 'sentence-building',
            'polyglot-style' => 'sentence-building',
            'Poliglot-style' => 'sentence-building',
            'poliglot-style' => 'sentence-building',
            'polihlot-style' => 'sentence-building',
            'Polyglot course' => 'Sentence Builder course',
            'polyglot course' => 'Sentence Builder course',
            'Poliglot course' => 'Sentence Builder course',
            'Polyglot exercises' => 'Sentence Builder exercises',
            'polyglot exercises' => 'Sentence Builder exercises',
            'Polyglot lesson' => 'Sentence Builder lesson',
            'Polyglot tests' => 'Sentence Builder tests',
            'Polyglot drill' => 'Sentence Builder drill',
            'Polyglot compose tokens' => 'Sentence Builder compose tokens',
            'V3 / Polyglot' => 'V3 / Sentence Builder',
            'Polyglot unlock flow' => 'Sentence Builder unlock flow',
            'Курс Поліглот' => 'Курс Sentence Builder',
            'Вправи Поліглот' => 'Вправи-конструктор речень',
            'у стилі Поліглот' => 'у форматі конструктора речень',
            'Polyglot:' => 'Sentence Builder:',
            'Poliglot:' => 'Sentence Builder:',
            'Поліглот:' => 'Sentence Builder:',
            'Полиглот:' => 'Sentence Builder:',
            'Polyglot' => 'Sentence Builder',
            'Poliglot' => 'Sentence Builder',
            'Поліглот' => 'Sentence Builder',
            'поліглот' => 'Sentence Builder',
            'Полиглот' => 'Sentence Builder',
            'полиглот' => 'Sentence Builder',
            'polihlot' => 'Sentence Builder',
        ]);

        return preg_replace_callback('/Sentence Builder: ([a-z][^\\r\\n]*)/u', function (array $matches): string {
            $parts = preg_split('/(\\s+\\([A-C][12]\\).*)/u', $matches[1], 2, PREG_SPLIT_DELIM_CAPTURE);
            $title = $parts[0] ?? $matches[1];
            $suffix = $parts[1] ?? '';

            return 'Sentence Builder: '.mb_convert_case($title, MB_CASE_TITLE, 'UTF-8').$suffix;
        }, $text) ?? $text;
    }
};
