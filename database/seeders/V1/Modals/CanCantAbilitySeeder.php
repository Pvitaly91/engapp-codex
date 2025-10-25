<?php

namespace Database\Seeders\V1\Modals;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use App\Services\QuestionSeedingService;
use App\Support\Database\Seeder;
use Illuminate\Support\Str;

class CanCantAbilitySeeder extends Seeder
{
    public function run()
    {
        $cat_present = Category::firstOrCreate(['name' => 'present'])->id;
        $sourceId = Source::firstOrCreate([
            'name' => 'Choose the correct option to complete the sentences.',
        ])->id;

        $canTag = Tag::firstOrCreate(['name' => 'Can'], ['category' => 'Modals']);
        $cantTag = Tag::firstOrCreate(['name' => "Can't"], ['category' => 'Modals']);
        $themeTag = Tag::firstOrCreate(['name' => 'can_cant_ability']);

        $data = [
            [
                'question' => '{a1} ask you a question?',
                'answers' => [['marker' => 'a1', 'answer' => 'Can']],
                'options' => ['Can', "Can't"],
            ],
            [
                'question' => 'I {a1}. The music is too loud.',
                'answers' => [['marker' => 'a1', 'answer' => "can't"]],
                'options' => ['can', "can't"],
            ],
            [
                'question' => '\'Can you play the piano?\' \'Yes, I {a1}.\'',
                'answers' => [['marker' => 'a1', 'answer' => 'can']],
                'options' => ['can', "can't"],
            ],
            [
                'question' => 'He {a1} four languages.',
                'answers' => [['marker' => 'a1', 'answer' => 'can']],
                'options' => ['can', "can't"],
            ],
            [
                'question' => 'He says that he {a1} me.',
                'answers' => [['marker' => 'a1', 'answer' => "can't"]],
                'options' => ['can', "can't"],
            ],
            [
                'question' => '{a1} a ham and cheese pizza, please?',
                'answers' => [['marker' => 'a1', 'answer' => 'Can I have']],
                'options' => ['Can I have', "Can't I have"],
            ],
            [
                'question' => '\'Can I smoke here?\' \'No, you {a1}.\'',
                'answers' => [['marker' => 'a1', 'answer' => "can't"]],
                'options' => ['can', "can't"],
            ],
            [
                'question' => 'We {a1} use our phones in class.',
                'answers' => [['marker' => 'a1', 'answer' => "can't"]],
                'options' => ['can', "can't"],
            ],
            [
                'question' => 'He {a1} my car if he needs it.',
                'answers' => [['marker' => 'a1', 'answer' => 'can use']],
                'options' => ['can use', "can't use"],
            ],
            [
                'question' => '{a1} the window, please?',
                'answers' => [['marker' => 'a1', 'answer' => 'Can you open']],
                'options' => ['Can you open', "Can't you open"],
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
            $d['category_id'] = $cat_present;
            $d['difficulty'] = 1;
            $d['source_id'] = $sourceId;
            $d['flag'] = 0;
            $answersText = implode(' ', array_column($d['answers'], 'answer'));
            $tagIds = [$themeTag->id];
            if (str_contains($answersText, "can't")) {
                $tagIds[] = $cantTag->id;
            } else {
                $tagIds[] = $canTag->id;
            }
            $d['tag_ids'] = $tagIds;

            $items[] = $d;
        }

        $service->seed($items);
    }
}
