<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Word;
use App\Models\Translate;
use Illuminate\Support\Facades\DB;

class WordsWithTranslationsSeeder extends Seeder
{
    public function run()
    {
        $path = storage_path('app/words_uk.csv');
        $rows = array_map('str_getcsv', file($path));
        $header = array_shift($rows);

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                $en = trim($row[0]);
                $uk = trim($row[1]);
                if (empty($en) || empty($uk)) continue;

                // Створюємо слово
                $word = Word::firstOrCreate(['word' => $en]);

                // Додаємо переклад
                Translate::updateOrCreate(
                    [
                        'word_id' => $word->id,
                        'lang' => 'uk',
                    ],
                    [
                        'translation' => $uk,
                    ]
                );
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
