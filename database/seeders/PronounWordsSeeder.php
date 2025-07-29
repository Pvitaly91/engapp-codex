<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\TagCategory;
use App\Models\Translate;
use App\Models\Word;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PronounWordsSeeder extends Seeder
{
    public function run(): void
    {
        $pronouns = [
            'personal_subject' => [
                ['en' => 'I', 'uk' => 'я'],
                ['en' => 'you', 'uk' => 'ти'],
                ['en' => 'he', 'uk' => 'він'],
                ['en' => 'she', 'uk' => 'вона'],
                ['en' => 'it', 'uk' => 'воно'],
                ['en' => 'we', 'uk' => 'ми'],
                ['en' => 'they', 'uk' => 'вони'],
            ],
            'personal_object' => [
                ['en' => 'me', 'uk' => 'мене'],
                ['en' => 'you', 'uk' => 'тебе'],
                ['en' => 'him', 'uk' => 'його'],
                ['en' => 'her', 'uk' => 'її'],
                ['en' => 'it', 'uk' => 'його'],
                ['en' => 'us', 'uk' => 'нас'],
                ['en' => 'them', 'uk' => 'їх'],
            ],
            'possessive_adjective' => [
                ['en' => 'my', 'uk' => 'мій'],
                ['en' => 'your', 'uk' => 'твій'],
                ['en' => 'his', 'uk' => 'його'],
                ['en' => 'her', 'uk' => 'її'],
                ['en' => 'its', 'uk' => 'його'],
                ['en' => 'our', 'uk' => 'наш'],
                ['en' => 'their', 'uk' => 'їх'],
            ],
            'possessive_pronoun' => [
                ['en' => 'mine', 'uk' => 'моє'],
                ['en' => 'yours', 'uk' => 'твоє'],
                ['en' => 'his', 'uk' => 'його'],
                ['en' => 'hers', 'uk' => 'її'],
                ['en' => 'its', 'uk' => 'його'],
                ['en' => 'ours', 'uk' => 'наш'],
                ['en' => 'theirs', 'uk' => 'їх'],
            ],
            'reflexive' => [
                ['en' => 'myself', 'uk' => 'себе'],
                ['en' => 'yourself', 'uk' => 'себе'],
                ['en' => 'himself', 'uk' => 'себе'],
                ['en' => 'herself', 'uk' => 'себе'],
                ['en' => 'itself', 'uk' => 'себе'],
                ['en' => 'ourselves', 'uk' => 'себе'],
                ['en' => 'themselves', 'uk' => 'себе'],
            ],
        ];

        DB::transaction(function () use ($pronouns) {
            $category = TagCategory::firstOrCreate(['name' => 'Words']);
            $pronounTag = Tag::firstOrCreate(['name' => 'pronouns'], ['tag_category_id' => $category->id]);

            foreach ($pronouns as $group => $items) {
                $groupTag = Tag::firstOrCreate(['name' => $group], ['tag_category_id' => $category->id]);

                foreach ($items as $item) {
                    $word = Word::firstOrCreate(['word' => $item['en']]);

                    if (! Translate::where('word_id', $word->id)
                        ->where('lang', 'uk')
                        ->where('translation', $item['uk'])
                        ->exists()) {
                        Translate::create([
                            'word_id' => $word->id,
                            'lang' => 'uk',
                            'translation' => $item['uk'],
                        ]);
                    }

                    $word->tags()->syncWithoutDetaching([
                        $pronounTag->id,
                        $groupTag->id,
                    ]);
                }
            }
        });
    }
}
