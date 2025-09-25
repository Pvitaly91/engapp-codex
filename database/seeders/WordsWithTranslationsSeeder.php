<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\Translate;
use App\Models\Word;
use Illuminate\Database\Seeder;
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
            $popularTag = Tag::firstOrCreate(['name' => '1000_most_popular']);
            foreach ($rows as $row) {
                if (count($row) < 2) {
                    continue;
                }

                $en = trim($row[0]);
                $uk = trim($row[1]);
                if (empty($en) || empty($uk)) {
                    continue;
                }

                // Створюємо слово
                $word = Word::firstOrCreate(['word' => $en]);

                // Додаємо переклад якщо такого ще немає
                if (! Translate::where('word_id', $word->id)
                    ->where('lang', 'uk')
                    ->where('translation', $uk)
                    ->exists()) {
                    Translate::create([
                        'word_id' => $word->id,
                        'lang' => 'uk',
                        'translation' => $uk,
                    ]);
                }

                // Прив'язуємо тег
                $word->tags()->syncWithoutDetaching([$popularTag->id]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
