<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class ThereIsThereAreWorksheetSeeder extends Seeder
{
    public function run()
    {
        $cat_present = Category::firstOrCreate(['name' => 'present'])->id;
        $sourceId = Source::firstOrCreate([
            'name' => 'There is there are worksheet'
        ])->id;

        $themeTag = Tag::firstOrCreate(['name' => 'There is there are'], ['category' => 'Grammar']);

        $phraseCategory = 'There is there are details';
        $phraseTags = [
            'there_is'   => Tag::firstOrCreate(['name' => 'There is'], ['category' => $phraseCategory]),
            'there_are'  => Tag::firstOrCreate(['name' => 'There are'], ['category' => $phraseCategory]),
            'is_there'   => Tag::firstOrCreate(['name' => 'Is there'], ['category' => $phraseCategory]),
            'are_there'  => Tag::firstOrCreate(['name' => 'Are there'], ['category' => $phraseCategory]),
            'there_isnt' => Tag::firstOrCreate(['name' => "There isn't"], ['category' => $phraseCategory]),
            'there_arent'=> Tag::firstOrCreate(['name' => "There aren't"], ['category' => $phraseCategory]),
        ];

        $data = [
            // A. Complete the sentences using "there is" or "there are".
            [
                'question' => '{a1} books on the table.',
                'answers' => [['marker' => 'a1', 'answer' => 'There are']],
                'options' => ['There is', 'There are'],
                'tag_ids' => [$phraseTags['there_are']->id],
                'level' => 'A1',
            ],
            [
                'question' => '{a1} many cars in the street.',
                'answers' => [['marker' => 'a1', 'answer' => 'There are']],
                'options' => ['There is', 'There are'],
                'tag_ids' => [$phraseTags['there_are']->id],
                'level' => 'A1',
            ],
            [
                'question' => '{a1} a picture on the wall.',
                'answers' => [['marker' => 'a1', 'answer' => 'There is']],
                'options' => ['There is', 'There are'],
                'tag_ids' => [$phraseTags['there_is']->id],
                'level' => 'A1',
            ],
            [
                'question' => '{a1} three men in the forest.',
                'answers' => [['marker' => 'a1', 'answer' => 'There are']],
                'options' => ['There is', 'There are'],
                'tag_ids' => [$phraseTags['there_are']->id],
                'level' => 'A1',
            ],
            [
                'question' => '{a1} seven apples in the fridge.',
                'answers' => [['marker' => 'a1', 'answer' => 'There are']],
                'options' => ['There is', 'There are'],
                'tag_ids' => [$phraseTags['there_are']->id],
                'level' => 'A1',
            ],
            [
                'question' => '{a1} a dog in the house.',
                'answers' => [['marker' => 'a1', 'answer' => 'There is']],
                'options' => ['There is', 'There are'],
                'tag_ids' => [$phraseTags['there_is']->id],
                'level' => 'A1',
            ],
            [
                'question' => '{a1} a monkey on the tree.',
                'answers' => [['marker' => 'a1', 'answer' => 'There is']],
                'options' => ['There is', 'There are'],
                'tag_ids' => [$phraseTags['there_is']->id],
                'level' => 'A1',
            ],
            [
                'question' => '{a1} a computer in the classroom.',
                'answers' => [['marker' => 'a1', 'answer' => 'There is']],
                'options' => ['There is', 'There are'],
                'tag_ids' => [$phraseTags['there_is']->id],
                'level' => 'A1',
            ],
            [
                'question' => '{a1} a butterfly in your room.',
                'answers' => [['marker' => 'a1', 'answer' => 'There is']],
                'options' => ['There is', 'There are'],
                'tag_ids' => [$phraseTags['there_is']->id],
                'level' => 'A1',
            ],
            [
                'question' => '{a1} much water in the cup.',
                'answers' => [['marker' => 'a1', 'answer' => 'There is']],
                'options' => ['There is', 'There are'],
                'tag_ids' => [$phraseTags['there_is']->id],
                'level' => 'A1',
            ],

            // B. Complete the sentences using "Is there" or "Are there".
            [
                'question' => '{a1} any girls in your class?',
                'answers' => [['marker' => 'a1', 'answer' => 'Are there']],
                'options' => ['Is there', 'Are there'],
                'tag_ids' => [$phraseTags['are_there']->id],
                'level' => 'A1',
            ],
            [
                'question' => '{a1} a police-station near here?',
                'answers' => [['marker' => 'a1', 'answer' => 'Is there']],
                'options' => ['Is there', 'Are there'],
                'tag_ids' => [$phraseTags['is_there']->id],
                'level' => 'A1',
            ],
            [
                'question' => '{a1} any bananas in the basket?',
                'answers' => [['marker' => 'a1', 'answer' => 'Are there']],
                'options' => ['Is there', 'Are there'],
                'tag_ids' => [$phraseTags['are_there']->id],
                'level' => 'A1',
            ],
            [
                'question' => '{a1} an ATM in the bank?',
                'answers' => [['marker' => 'a1', 'answer' => 'Is there']],
                'options' => ['Is there', 'Are there'],
                'tag_ids' => [$phraseTags['is_there']->id],
                'level' => 'A1',
            ],
            [
                'question' => '{a1} any birds in the tree?',
                'answers' => [['marker' => 'a1', 'answer' => 'Are there']],
                'options' => ['Is there', 'Are there'],
                'tag_ids' => [$phraseTags['are_there']->id],
                'level' => 'A1',
            ],

            // C. Complete the sentences using "there isn't" or "there aren't".
            [
                'question' => '{a1} an opera in our town.',
                'answers' => [['marker' => 'a1', 'answer' => "There isn't"]],
                'options' => ["There isn't", "There aren't"],
                'tag_ids' => [$phraseTags['there_isnt']->id],
                'level' => 'A1',
            ],
            [
                'question' => '{a1} any girls in your class.',
                'answers' => [['marker' => 'a1', 'answer' => "There aren't"]],
                'options' => ["There isn't", "There aren't"],
                'tag_ids' => [$phraseTags['there_arent']->id],
                'level' => 'A1',
            ],
            [
                'question' => '{a1} a pencil near the desk.',
                'answers' => [['marker' => 'a1', 'answer' => "There isn't"]],
                'options' => ["There isn't", "There aren't"],
                'tag_ids' => [$phraseTags['there_isnt']->id],
                'level' => 'A1',
            ],
            [
                'question' => '{a1} any cheese in the fridge.',
                'answers' => [['marker' => 'a1', 'answer' => "There isn't"]],
                'options' => ["There isn't", "There aren't"],
                'tag_ids' => [$phraseTags['there_isnt']->id],
                'level' => 'A1',
            ],
            [
                'question' => '{a1} a hospital in our town.',
                'answers' => [['marker' => 'a1', 'answer' => "There isn't"]],
                'options' => ["There isn't", "There aren't"],
                'tag_ids' => [$phraseTags['there_isnt']->id],
                'level' => 'A1',
            ],

            // D. Complete the sentences using various forms.
            [
                'question' => '{a1} someone looking at me.',
                'answers' => [['marker' => 'a1', 'answer' => 'There is']],
                'options' => ['There is', 'There are', 'Is there', 'Are there', "There isn't", "There aren't"],
                'tag_ids' => [$phraseTags['there_is']->id],
                'level' => 'A2',
            ],
            [
                'question' => '{a1} any children at school?',
                'answers' => [['marker' => 'a1', 'answer' => 'Are there']],
                'options' => ['There is', 'There are', 'Is there', 'Are there', "There isn't", "There aren't"],
                'tag_ids' => [$phraseTags['are_there']->id],
                'level' => 'A2',
            ],
            [
                'question' => '{a1} many boys in class?',
                'answers' => [['marker' => 'a1', 'answer' => 'Are there']],
                'options' => ['There is', 'There are', 'Is there', 'Are there', "There isn't", "There aren't"],
                'tag_ids' => [$phraseTags['are_there']->id],
                'level' => 'A2',
            ],
            [
                'question' => '{a1} any eggs in the fridge?',
                'answers' => [['marker' => 'a1', 'answer' => 'Are there']],
                'options' => ['There is', 'There are', 'Is there', 'Are there', "There isn't", "There aren't"],
                'tag_ids' => [$phraseTags['are_there']->id],
                'level' => 'A2',
            ],
            [
                'question' => '{a1} my soccer stadium near here?',
                'answers' => [['marker' => 'a1', 'answer' => 'Is there']],
                'options' => ['There is', 'There are', 'Is there', 'Are there', "There isn't", "There aren't"],
                'tag_ids' => [$phraseTags['is_there']->id],
                'level' => 'A2',
            ],
        ];

        $service = new QuestionSeedingService();
        $items = [];
        foreach ($data as $i => $d) {
            $index = $i + 1;
            $slug  = Str::slug(class_basename(self::class));
            $max   = 36 - strlen((string) $index) - 1;
            $uuid  = substr($slug, 0, $max) . '-' . $index;

            $d['uuid']        = $uuid;
            $d['category_id'] = $cat_present;
            $d['difficulty']  = $d['difficulty'] ?? 1;
            $d['source_id']   = $sourceId;
            $d['flag']        = 0;
            $d['level']       = $d['level'] ?? 'A1';
            $d['tag_ids']     = array_merge([$themeTag->id], $d['tag_ids']);

            $items[] = $d;
        }

        $service->seed($items);
    }
}
