<?php

namespace Database\Seeders\V1\Modals;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use App\Services\QuestionSeedingService;
use App\Support\Database\Seeder;
use Illuminate\Support\Str;

class CanCantAbilityExercise2Seeder extends Seeder
{
    public function run()
    {
        $cat_present = Category::firstOrCreate(['name' => 'present'])->id;
        $sourceId = Source::firstOrCreate([
            'name' => "Write affirmative (+) and negative (-) sentences using can, can't with the words below.",
        ])->id;

        $canTag = Tag::firstOrCreate(['name' => 'Can'], ['category' => 'Modals']);
        $cantTag = Tag::firstOrCreate(['name' => "Can't"], ['category' => 'Modals']);
        $themeTag = Tag::firstOrCreate(['name' => 'can_cant_ability_exercise_2']);

        $data = [
            [
                'question' => 'She / dance / very well. ⇒ She {a1}. (+)',
                'answers' => [['marker' => 'a1', 'answer' => 'can dance']],
                'options' => ['can dance', "can't dance", 'dances', 'dance'],
            ],
            [
                'question' => 'I / finish / the composition for tomorrow. ⇒ I {a1} for tomorrow. (-)',
                'answers' => [['marker' => 'a1', 'answer' => "can't finish"]],
                'options' => ['can finish', "can't finish", "won't finish", "don't finish"],
            ],
            [
                'question' => 'You / park / your car here. ⇒ You {a1} here. (-)',
                'answers' => [['marker' => 'a1', 'answer' => "can't park"]],
                'options' => ['can park', "can't park", "don't park", "won't park"],
            ],
            [
                'question' => 'Jim / come / to the concert with us. ⇒ Jim {a1} with us. (+)',
                'answers' => [['marker' => 'a1', 'answer' => 'can come']],
                'options' => ['can come', "can't come", 'comes', 'come'],
            ],
            [
                'question' => 'She / meet / you at 7. ⇒ She {a1}. (-)',
                'answers' => [['marker' => 'a1', 'answer' => "can't meet"]],
                'options' => ['can meet', "can't meet", 'meet', 'meets'],
            ],
            [
                'question' => 'You / use / this app to view documents. ⇒ You {a1} to view documents. (+)',
                'answers' => [['marker' => 'a1', 'answer' => 'can use']],
                'options' => ['can use', "can't use", 'use', 'uses'],
            ],
            [
                'question' => 'We / lose / this match. ⇒ We {a1}. (-)',
                'answers' => [['marker' => 'a1', 'answer' => "can't lose"]],
                'options' => ['can lose', "can't lose", 'lose', 'loses'],
            ],
            [
                'question' => 'They / understand / my decision. ⇒ They {a1}. (-)',
                'answers' => [['marker' => 'a1', 'answer' => "can't understand"]],
                'options' => ['can understand', "can't understand", 'understand', 'understands'],
            ],
            [
                'question' => 'She / stay / at my house tonight. ⇒ She {a1} tonight. (+)',
                'answers' => [['marker' => 'a1', 'answer' => 'can stay']],
                'options' => ['can stay', "can't stay", 'stay', 'stays'],
            ],
            [
                'question' => 'You / see / the sea from the balcony. ⇒ You {a1} from the balcony. (-)',
                'answers' => [['marker' => 'a1', 'answer' => "can't see"]],
                'options' => ['can see', "can't see", 'see', 'sees'],
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
            $d['difficulty'] = 2;
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
