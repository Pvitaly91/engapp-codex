<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class ThereIsThereAreSeeder extends Seeder
{
    public function run()
    {
        $cat_present = Category::firstOrCreate(['name' => 'present'])->id;
        $sourceId = Source::firstOrCreate([
            'name' => 'There is There are. Circle the correct phrase for each statement.'
        ])->id;

        $themeTag = Tag::firstOrCreate(['name' => 'There is/There are'], ['category' => 'Grammar']);

        $phraseTags = [
            'there_is' => Tag::firstOrCreate(['name' => 'There is'], ['category' => 'Grammar']),
            'there_are' => Tag::firstOrCreate(['name' => 'There are'], ['category' => 'Grammar']),
            'is_there' => Tag::firstOrCreate(['name' => 'Is there'], ['category' => 'Grammar']),
            'are_there' => Tag::firstOrCreate(['name' => 'Are there'], ['category' => 'Grammar']),
            'there_isnt' => Tag::firstOrCreate(['name' => "There isn't"], ['category' => 'Grammar']),
            'there_arent' => Tag::firstOrCreate(['name' => "There aren't"], ['category' => 'Grammar']),
            'there_arent_any' => Tag::firstOrCreate(['name' => "There aren't any"], ['category' => 'Grammar']),
            'there_isnt_any' => Tag::firstOrCreate(['name' => "There isn't any"], ['category' => 'Grammar']),
        ];

        $data = [
            [
                'question' => '{a1} a nice restaurant nearby. It has great pizza!',
                'answers' => [['marker' => 'a1', 'answer' => 'There is']],
                'options' => ['There is', 'There are', 'There'],
                'tag_ids' => [$phraseTags['there_is']->id],
            ],
            [
                'question' => 'Do you know if {a1} a test tomorrow?',
                'answers' => [['marker' => 'a1', 'answer' => 'there is']],
                'options' => ['There is', 'There are', 'There'],
                'tag_ids' => [$phraseTags['there_is']->id],
            ],
            [
                'question' => 'Are you going to the parade? I hear there {a1} going to be horses!',
                'answers' => [['marker' => 'a1', 'answer' => 'are']],
                'options' => ['are', 'is', 'were'],
                'tag_ids' => [$phraseTags['there_are']->id],
            ],
            [
                'question' => '{a1} any rain in the forecast tomorrow?',
                'answers' => [['marker' => 'a1', 'answer' => 'Is there']],
                'options' => ['Is there', 'Are there', 'There is'],
                'tag_ids' => [$phraseTags['is_there']->id],
            ],
            [
                'question' => '{a1} any ice cream left. It was so good.',
                'answers' => [['marker' => 'a1', 'answer' => 'there isn\'t']],
                'options' => ['there is', 'there isn\'t', 'there are'],
                'tag_ids' => [$phraseTags['there_isnt']->id],
            ],
            [
                'question' => '{a1} any tickets left for the concert?',
                'answers' => [['marker' => 'a1', 'answer' => 'There are']],
                'options' => ['There is', 'There are', 'Were there'],
                'tag_ids' => [$phraseTags['there_are']->id],
            ],
            [
                'question' => 'Please remind me. {a1} a good chance I will forget.',
                'answers' => [['marker' => 'a1', 'answer' => 'there is']],
                'options' => ['there is', 'there are', 'there'],
                'tag_ids' => [$phraseTags['there_is']->id],
            ],
            [
                'question' => '{a1} any room in the fridge? I will have to put my milk in the freezer.',
                'answers' => [['marker' => 'a1', 'answer' => 'Is there']],
                'options' => ["There's", 'There are', 'Is there'],
                'tag_ids' => [$phraseTags['is_there']->id],
            ],
            [
                'question' => 'How many cookies {a1} in your bag?',
                'answers' => [['marker' => 'a1', 'answer' => 'are there']],
                'options' => ['is there', 'are there', 'there are'],
                'tag_ids' => [$phraseTags['are_there']->id],
            ],
            [
                'question' => 'No, {a1} any lemons in the cupboard.',
                'answers' => [['marker' => 'a1', 'answer' => 'there aren\'t']],
                'options' => ['there isn\'t', 'there aren\'t', 'there isn\'t any'],
                'tag_ids' => [$phraseTags['there_arent']->id],
            ],
            [
                'question' => 'I can\'t find my cell. {a1} it in your purse?',
                'answers' => [['marker' => 'a1', 'answer' => 'Is there']],
                'options' => ['Is there', 'Are there', 'There is'],
                'tag_ids' => [$phraseTags['is_there']->id],
            ],
            [
                'question' => 'I have cans of soda in the cooler, but {a1} any bottles left.',
                'answers' => [['marker' => 'a1', 'answer' => 'there aren\'t any']],
                'options' => ['there are', 'there aren\'t', 'there aren\'t any'],
                'tag_ids' => [$phraseTags['there_arent_any']->id],
            ],
            [
                'question' => '{a1} going to be food at the party? Or should I eat before I go?',
                'answers' => [['marker' => 'a1', 'answer' => 'Is there']],
                'options' => ['Is there', 'Are there', 'There is'],
                'tag_ids' => [$phraseTags['is_there']->id],
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
            $d['level']       = 'A1';
            $d['tag_ids']     = array_merge([$themeTag->id], $d['tag_ids']);

            $items[] = $d;
        }

        $service->seed($items);
    }
}
