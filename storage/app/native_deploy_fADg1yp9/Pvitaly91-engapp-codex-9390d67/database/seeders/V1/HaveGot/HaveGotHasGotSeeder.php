<?php

namespace Database\Seeders\V1\HaveGot;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use App\Services\QuestionSeedingService;
use App\Support\Database\Seeder;
use Illuminate\Support\Str;

class HaveGotHasGotSeeder extends Seeder
{
    public function run()
    {
        $categoryId = Category::firstOrCreate(['name' => 'present'])->id;
        $sourceId = Source::firstOrCreate([
            'name' => 'Complete the sentences with have got or has got.',
        ])->id;

        $themeTag = Tag::firstOrCreate(['name' => 'have_has_got']);
        $modalTag = Tag::firstOrCreate(['name' => 'Have Has Got'], ['category' => 'Modals']);

        $data = [
            [
                'question' => 'I {a1} a new phone.',
                'answers' => [['marker' => 'a1', 'answer' => 'have got']],
                'options' => ['have got', 'has got'],
            ],
            [
                'question' => 'She {a1} long hair.',
                'answers' => [['marker' => 'a1', 'answer' => 'has got']],
                'options' => ['have got', 'has got'],
            ],
            [
                'question' => 'They {a1} three children.',
                'answers' => [['marker' => 'a1', 'answer' => 'have got']],
                'options' => ['have got', 'has got'],
            ],
            [
                'question' => 'He {a1} a dog.',
                'answers' => [['marker' => 'a1', 'answer' => 'has got']],
                'options' => ['have got', 'has got'],
            ],
            [
                'question' => 'We {a1} a garden.',
                'answers' => [['marker' => 'a1', 'answer' => 'have got']],
                'options' => ['have got', 'has got'],
            ],
            [
                'question' => 'The car {a1} four doors.',
                'answers' => [['marker' => 'a1', 'answer' => 'has got']],
                'options' => ['have got', 'has got'],
            ],
            [
                'question' => 'You {a1} a nice smile.',
                'answers' => [['marker' => 'a1', 'answer' => 'have got']],
                'options' => ['have got', 'has got'],
            ],
            [
                'question' => 'My friend {a1} two cats.',
                'answers' => [['marker' => 'a1', 'answer' => 'has got']],
                'options' => ['have got', 'has got'],
            ],
            [
                'question' => 'They {a1} a new house.',
                'answers' => [['marker' => 'a1', 'answer' => 'have got']],
                'options' => ['have got', 'has got'],
            ],
            [
                'question' => 'It {a1} big eyes.',
                'answers' => [['marker' => 'a1', 'answer' => 'has got']],
                'options' => ['have got', 'has got'],
            ],
        ];

        $service = new QuestionSeedingService;
        $items = [];
        foreach ($data as $i => $d) {
            $index = $i + 1;
            $slug = Str::slug(class_basename(self::class));
            $max = 36 - strlen((string) $index) - 1;
            $uuid = substr($slug, 0, $max).'-'.$index;

            $tagIds = [$themeTag->id, $modalTag->id];

            $items[] = [
                'uuid' => $uuid,
                'question' => $d['question'],
                'category_id' => $categoryId,
                'difficulty' => 1,
                'source_id' => $sourceId,
                'flag' => 0,
                'tag_ids' => $tagIds,
                'answers' => $d['answers'],
                'options' => $d['options'],
            ];
        }

        $service->seed($items);
    }
}
