<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\{Category, Source, Tag};
use Illuminate\Support\Str;

class ThereIsThereAreImageTestSeeder extends Seeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'present'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'There is/There are Test 1'])->id;
        $themeTag = Tag::firstOrCreate(['name' => 'There is/There are'], ['category' => 'Grammar']);

        $phraseCategory = 'There is/There are details';
        $phraseTags = [
            'there_is'    => Tag::firstOrCreate(['name' => 'There is'], ['category' => $phraseCategory]),
            'there_are'   => Tag::firstOrCreate(['name' => 'There are'], ['category' => $phraseCategory]),
            'is_there'    => Tag::firstOrCreate(['name' => 'Is there'], ['category' => $phraseCategory]),
            'are_there'   => Tag::firstOrCreate(['name' => 'Are there'], ['category' => $phraseCategory]),
            "there_isnt"  => Tag::firstOrCreate(['name' => "There isn't"], ['category' => $phraseCategory]),
            "there_arent" => Tag::firstOrCreate(['name' => "There aren't"], ['category' => $phraseCategory]),
            'there_were'  => Tag::firstOrCreate(['name' => 'There were'], ['category' => $phraseCategory]),
            'were_there'  => Tag::firstOrCreate(['name' => 'Were there'], ['category' => $phraseCategory]),
        ];

        $data = [
            [
                'question' => '{a1} a nice restaurant nearby. It has great pizza!',
                'answers' => [['marker' => 'a1', 'answer' => 'There is']],
                'options' => ['There is', 'There are', 'There were'],
                'tag_ids' => [$phraseTags['there_is']->id],
                'level' => 'A1',
            ],
            [
                'question' => 'Do you know if {a1} a test tomorrow?',
                'answers' => [['marker' => 'a1', 'answer' => 'there is']],
                'options' => ['there is', 'there are', 'there were'],
                'tag_ids' => [$phraseTags['there_is']->id],
                'level' => 'A1',
            ],
            [
                'question' => 'Are you going to the parade? I hear {a1} going to be horses!',
                'answers' => [['marker' => 'a1', 'answer' => 'there are']],
                'options' => ['there is', 'there are', 'there were'],
                'tag_ids' => [$phraseTags['there_are']->id],
                'level' => 'A1',
            ],
            [
                'question' => '{a1} any rain in the forecast tomorrow?',
                'answers' => [['marker' => 'a1', 'answer' => 'Is there']],
                'options' => ['Is there', 'Are there', 'Were there'],
                'tag_ids' => [$phraseTags['is_there']->id],
                'level' => 'A1',
            ],
            [
                'question' => '{a1} any ice cream left. Let\'s go to the store!',
                'answers' => [['marker' => 'a1', 'answer' => "There isn't"]],
                'options' => ["There isn't", "There aren't", 'There were'],
                'tag_ids' => [$phraseTags['there_isnt']->id],
                'level' => 'A1',
            ],
            [
                'question' => '{a1} any tickets left for the concert next week?',
                'answers' => [['marker' => 'a1', 'answer' => 'Are there']],
                'options' => ['Is there', 'Are there', 'Were there'],
                'tag_ids' => [$phraseTags['are_there']->id],
                'level' => 'A1',
            ],
            [
                'question' => 'Is there any pizza left? {a1} five slices left last night.',
                'answers' => [['marker' => 'a1', 'answer' => 'There were']],
                'options' => ['There is', 'There are', 'There were'],
                'tag_ids' => [$phraseTags['there_were']->id],
                'level' => 'A1',
            ],
            [
                'question' => 'Please remind me. {a1} a good chance I will forget to pick up milk.',
                'answers' => [['marker' => 'a1', 'answer' => 'There is']],
                'options' => ['There is', 'There are', 'There were'],
                'tag_ids' => [$phraseTags['there_is']->id],
                'level' => 'A1',
            ],
            [
                'question' => 'How many cookies {a1} in a box?',
                'answers' => [['marker' => 'a1', 'answer' => 'are there']],
                'options' => ['is there', 'are there', 'were there'],
                'tag_ids' => [$phraseTags['are_there']->id],
                'level' => 'A1',
            ],
            [
                'question' => 'No, {a1} any bagels in the cupboard.',
                'answers' => [['marker' => 'a1', 'answer' => "there aren't"]],
                'options' => ["there isn't", "there aren't", 'there were'],
                'tag_ids' => [$phraseTags['there_arent']->id],
                'level' => 'A1',
            ],
            [
                'question' => 'I don\'t know if {a1} any hotdogs ready, but I will check.',
                'answers' => [['marker' => 'a1', 'answer' => 'there are']],
                'options' => ['there is', 'there are', 'there were'],
                'tag_ids' => [$phraseTags['there_are']->id],
                'level' => 'A1',
            ],
            [
                'question' => '{a1} ten cans of soda in the cooler, but now they are all gone.',
                'answers' => [['marker' => 'a1', 'answer' => 'There were']],
                'options' => ["There isn't", "There aren't", 'There were'],
                'tag_ids' => [$phraseTags['there_were']->id],
                'level' => 'A1',
            ],
            [
                'question' => '{a1} going to be food at the party? Or should I eat before I go?',
                'answers' => [['marker' => 'a1', 'answer' => 'Is there']],
                'options' => ['Is there', 'Are there', 'Were there'],
                'tag_ids' => [$phraseTags['is_there']->id],
                'level' => 'A1',
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
            $d['category_id'] = $categoryId;
            $d['difficulty']  = $d['difficulty'] ?? 1;
            $d['source_id']   = $sourceId;
            $d['flag']        = 0;
            $d['tag_ids']     = array_merge([$themeTag->id], $d['tag_ids']);

            $items[] = $d;
        }

        $service->seed($items);
    }
}
