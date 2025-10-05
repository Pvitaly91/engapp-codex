<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use App\Services\QuestionSeedingService;
use App\Support\Database\Seeder;
use Illuminate\Support\Str;

class ThereIsThereAreWorksheetSeeder extends Seeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'present'])->id;
        $sources = [
            'A' => Source::firstOrCreate(['name' => 'There is/There are Worksheet A'])->id,
            'B' => Source::firstOrCreate(['name' => 'There is/There are Worksheet B'])->id,
            'C' => Source::firstOrCreate(['name' => 'There is/There are Worksheet C'])->id,
            'D' => Source::firstOrCreate(['name' => 'There is/There are Worksheet D'])->id,
        ];
        $themeTag = Tag::firstOrCreate(['name' => 'There is/There are'], ['category' => 'Grammar']);

        $phraseCategory = 'There is/There are details';
        $phraseTags = [
            'there_is' => Tag::firstOrCreate(['name' => 'There is'], ['category' => $phraseCategory]),
            'there_are' => Tag::firstOrCreate(['name' => 'There are'], ['category' => $phraseCategory]),
            'is_there' => Tag::firstOrCreate(['name' => 'Is there'], ['category' => $phraseCategory]),
            'are_there' => Tag::firstOrCreate(['name' => 'Are there'], ['category' => $phraseCategory]),
            'there_isnt' => Tag::firstOrCreate(['name' => "There isn't"], ['category' => $phraseCategory]),
            'there_arent' => Tag::firstOrCreate(['name' => "There aren't"], ['category' => $phraseCategory]),
        ];

        $data = [
            // Section A
            [
                'question' => '{a1} books on the table.',
                'answers' => [['marker' => 'a1', 'answer' => 'There are']],
                'options' => ['There is', 'There are'],
                'tag_ids' => [$phraseTags['there_are']->id],
                'level' => 'A1',
                'source_id' => $sources['A'],
            ],
            [
                'question' => '{a1} many cars in the street.',
                'answers' => [['marker' => 'a1', 'answer' => 'There are']],
                'options' => ['There is', 'There are'],
                'tag_ids' => [$phraseTags['there_are']->id],
                'level' => 'A1',
                'source_id' => $sources['A'],
            ],
            [
                'question' => '{a1} a picture on the wall.',
                'answers' => [['marker' => 'a1', 'answer' => 'There is']],
                'options' => ['There is', 'There are'],
                'tag_ids' => [$phraseTags['there_is']->id],
                'level' => 'A1',
                'source_id' => $sources['A'],
            ],
            [
                'question' => '{a1} a pencil near the book.',
                'answers' => [['marker' => 'a1', 'answer' => 'There is']],
                'options' => ['There is', 'There are'],
                'tag_ids' => [$phraseTags['there_is']->id],
                'level' => 'A1',
                'source_id' => $sources['A'],
            ],
            [
                'question' => '{a1} many trees in the forest.',
                'answers' => [['marker' => 'a1', 'answer' => 'There are']],
                'options' => ['There is', 'There are'],
                'tag_ids' => [$phraseTags['there_are']->id],
                'level' => 'A1',
                'source_id' => $sources['A'],
            ],
            [
                'question' => '{a1} a cat under the table.',
                'answers' => [['marker' => 'a1', 'answer' => 'There is']],
                'options' => ['There is', 'There are'],
                'tag_ids' => [$phraseTags['there_is']->id],
                'level' => 'A1',
                'source_id' => $sources['A'],
            ],
            [
                'question' => '{a1} seven apples in the fridge.',
                'answers' => [['marker' => 'a1', 'answer' => 'There are']],
                'options' => ['There is', 'There are'],
                'tag_ids' => [$phraseTags['there_are']->id],
                'level' => 'A1',
                'source_id' => $sources['A'],
            ],
            [
                'question' => '{a1} a dog in the house.',
                'answers' => [['marker' => 'a1', 'answer' => 'There is']],
                'options' => ['There is', 'There are'],
                'tag_ids' => [$phraseTags['there_is']->id],
                'level' => 'A1',
                'source_id' => $sources['A'],
            ],
            [
                'question' => '{a1} a monkey on the tree.',
                'answers' => [['marker' => 'a1', 'answer' => 'There is']],
                'options' => ['There is', 'There are'],
                'tag_ids' => [$phraseTags['there_is']->id],
                'level' => 'A1',
                'source_id' => $sources['A'],
            ],
            [
                'question' => '{a1} many rooms in my house.',
                'answers' => [['marker' => 'a1', 'answer' => 'There are']],
                'options' => ['There is', 'There are'],
                'tag_ids' => [$phraseTags['there_are']->id],
                'level' => 'A1',
                'source_id' => $sources['A'],
            ],

            // Section B
            [
                'question' => '{a1} an opera in our city?',
                'answers' => [['marker' => 'a1', 'answer' => 'Is there']],
                'options' => ['Is there', 'Are there'],
                'tag_ids' => [$phraseTags['is_there']->id],
                'level' => 'A1',
                'source_id' => $sources['B'],
            ],
            [
                'question' => '{a1} many girls in your class?',
                'answers' => [['marker' => 'a1', 'answer' => 'Are there']],
                'options' => ['Is there', 'Are there'],
                'tag_ids' => [$phraseTags['are_there']->id],
                'level' => 'A1',
                'source_id' => $sources['B'],
            ],
            [
                'question' => '{a1} a police-station near the hospital?',
                'answers' => [['marker' => 'a1', 'answer' => 'Is there']],
                'options' => ['Is there', 'Are there'],
                'tag_ids' => [$phraseTags['is_there']->id],
                'level' => 'A1',
                'source_id' => $sources['B'],
            ],
            [
                'question' => '{a1} any bananas in the basket?',
                'answers' => [['marker' => 'a1', 'answer' => 'Are there']],
                'options' => ['Is there', 'Are there'],
                'tag_ids' => [$phraseTags['are_there']->id],
                'level' => 'A1',
                'source_id' => $sources['B'],
            ],
            [
                'question' => '{a1} trees in the park?',
                'answers' => [['marker' => 'a1', 'answer' => 'Are there']],
                'options' => ['Is there', 'Are there'],
                'tag_ids' => [$phraseTags['are_there']->id],
                'level' => 'A1',
                'source_id' => $sources['B'],
            ],

            // Section C
            [
                'question' => '{a1} any people in the streets.',
                'answers' => [['marker' => 'a1', 'answer' => "There aren't"]],
                'options' => ["There isn't", "There aren't"],
                'tag_ids' => [$phraseTags['there_arent']->id],
                'level' => 'A1',
                'source_id' => $sources['C'],
            ],
            [
                'question' => '{a1} any butter in the fridge.',
                'answers' => [['marker' => 'a1', 'answer' => "There isn't"]],
                'options' => ["There isn't", "There aren't"],
                'tag_ids' => [$phraseTags['there_isnt']->id],
                'level' => 'A1',
                'source_id' => $sources['C'],
            ],
            [
                'question' => '{a1} any plates on the table.',
                'answers' => [['marker' => 'a1', 'answer' => "There aren't"]],
                'options' => ["There isn't", "There aren't"],
                'tag_ids' => [$phraseTags['there_arent']->id],
                'level' => 'A1',
                'source_id' => $sources['C'],
            ],
            [
                'question' => '{a1} any milk in the bottle.',
                'answers' => [['marker' => 'a1', 'answer' => "There isn't"]],
                'options' => ["There isn't", "There aren't"],
                'tag_ids' => [$phraseTags['there_isnt']->id],
                'level' => 'A1',
                'source_id' => $sources['C'],
            ],
            [
                'question' => '{a1} any coffee in the cup.',
                'answers' => [['marker' => 'a1', 'answer' => "There isn't"]],
                'options' => ["There isn't", "There aren't"],
                'tag_ids' => [$phraseTags['there_isnt']->id],
                'level' => 'A1',
                'source_id' => $sources['C'],
            ],

            // Section D
            [
                'question' => '{a1} someone looking at me.',
                'answers' => [['marker' => 'a1', 'answer' => 'There is']],
                'options' => ['There is', 'There are', 'Is there', 'Are there', "There isn't", "There aren't"],
                'tag_ids' => [$phraseTags['there_is']->id],
                'level' => 'A1',
                'source_id' => $sources['D'],
            ],
            [
                'question' => '{a1} many boys in the school?',
                'answers' => [['marker' => 'a1', 'answer' => 'Are there']],
                'options' => ['There is', 'There are', 'Is there', 'Are there', "There isn't", "There aren't"],
                'tag_ids' => [$phraseTags['are_there']->id],
                'level' => 'A1',
                'source_id' => $sources['D'],
            ],
            [
                'question' => '{a1} some photos in her bag.',
                'answers' => [['marker' => 'a1', 'answer' => 'There are']],
                'options' => ['There is', 'There are', 'Is there', 'Are there', "There isn't", "There aren't"],
                'tag_ids' => [$phraseTags['there_are']->id],
                'level' => 'A1',
                'source_id' => $sources['D'],
            ],
            [
                'question' => '{a1} any eggs in the fridge.',
                'answers' => [['marker' => 'a1', 'answer' => "There aren't"]],
                'options' => ['There is', 'There are', 'Is there', 'Are there', "There isn't", "There aren't"],
                'tag_ids' => [$phraseTags['are_there']->id],
                'level' => 'A1',
                'source_id' => $sources['D'],
            ],
            [
                'question' => '{a1} any soccer stadium here?',
                'answers' => [['marker' => 'a1', 'answer' => 'Is there']],
                'options' => ['There is', 'There are', 'Is there', 'Are there', "There isn't", "There aren't"],
                'tag_ids' => [$phraseTags['is_there']->id],
                'level' => 'A1',
                'source_id' => $sources['D'],
            ],
        ];

        $service = new QuestionSeedingService;
        $items = [];
        foreach ($data as $i => $d) {
            $index = $i + 1;
            $slug = Str::slug(class_basename(self::class));
            $max = 36 - strlen((string) $index) - 1;
            $uuid = substr($slug, 0, $max).'-'.$index;

            $d['uuid'] = $uuid;
            $d['category_id'] = $categoryId;
            $d['difficulty'] = $d['difficulty'] ?? 1;
            $d['flag'] = 0;
            $d['tag_ids'] = array_merge([$themeTag->id], $d['tag_ids']);

            $items[] = $d;
        }

        $service->seed($items);
    }
}
