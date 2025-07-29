<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use App\Services\QuestionSeedingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CanCantAbilityExercise3Seeder extends Seeder
{
    public function run()
    {
        $cat_present = Category::firstOrCreate(['name' => 'present'])->id;
        $sourceId = Source::firstOrCreate([
            'name' => "Complete this job interview with can, can't and the words in brackets.",
        ])->id;

        $canTag = Tag::firstOrCreate(['name' => 'Can'], ['category' => 'Modals']);
        $cantTag = Tag::firstOrCreate(['name' => "Can't"], ['category' => 'Modals']);
        $themeTag = Tag::firstOrCreate(['name' => 'can_cant_ability_exercise_3']);

        $data = [
            [
                'question' => 'Interviewer: {a1} (you/drive)?',
                'answers' => [['marker' => 'a1', 'answer' => 'Can you drive']],
                'options' => ['Can you drive', "Can't you drive", 'Do you drive'],
            ],
            [
                'question' => 'Ann: Yes, I {a1}. I have a driving license and a car.',
                'answers' => [['marker' => 'a1', 'answer' => 'can']],
                'options' => ['can', "can't"],
            ],
            [
                'question' => 'Interviewer: {a1} (you/accept) criticism?',
                'answers' => [['marker' => 'a1', 'answer' => 'Can you accept criticism']],
                'options' => ['Can you accept criticism', "Can't you accept criticism", 'Do you accept criticism'],
            ],
            [
                'question' => 'Ann: I {a1} (not accept) negative criticism, but I {a2} (accept) constructive criticism.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => "can't"],
                    ['marker' => 'a2', 'answer' => 'can'],
                ],
                'options' => ['can', "can't"],
            ],
            [
                'question' => 'Interviewer: {a1} (you/use) the Microsoft Office Suite?',
                'answers' => [['marker' => 'a1', 'answer' => 'Can you use the Microsoft Office Suite']],
                'options' => ['Can you use the Microsoft Office Suite', "Can't you use the Microsoft Office Suite", 'Do you use the Microsoft Office Suite'],
            ],
            [
                'question' => 'Ann: Yes, I {a1}. I have a lot of experience using Word, Excel, and PowerPoint.',
                'answers' => [['marker' => 'a1', 'answer' => 'can']],
                'options' => ['can', "can't"],
            ],
            [
                'question' => 'Interviewer: {a1} (speak) German or French?',
                'answers' => [['marker' => 'a1', 'answer' => 'Can you speak German or French']],
                'options' => ['Can you speak German or French', "Can't you speak German or French", 'Do you speak German or French'],
            ],
            [
                'question' => 'Ann: I {a1} (speak) German very well, but I {a2} (not speak) French very fluently.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'can'],
                    ['marker' => 'a2', 'answer' => "can't"],
                ],
                'options' => ['can', "can't"],
            ],
            [
                'question' => 'Interviewer: When {a1} (you/start)?',
                'answers' => [['marker' => 'a1', 'answer' => 'can you start']],
                'options' => ['can you start', "can't you start", 'do you start'],
            ],
            [
                'question' => 'Ann: I {a1} (be) here tomorrow morning if you want.',
                'answers' => [['marker' => 'a1', 'answer' => 'can']],
                'options' => ['can', "can't"],
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
