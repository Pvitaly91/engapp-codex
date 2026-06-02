<?php

namespace Database\Seeders\Ai;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use App\Services\QuestionSeedingService;
use App\Support\Database\Seeder;
use Illuminate\Support\Str;

class NegativePresentPerfectHabitsTestSeeder extends Seeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Present Perfect'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'AI: Present Perfect Negative Habits'])->id;

        $tenseTag = Tag::firstOrCreate(['name' => 'Present Perfect'], ['category' => 'Tenses']);
        $grammarTag = Tag::firstOrCreate(['name' => 'Negative Statements'], ['category' => 'Grammar Focus']);
        $fixedTag = Tag::firstOrCreate(['name' => 'fixed'], ['category' => 'Question Status']);
        $verbHintChangeTag = Tag::firstOrCreate(
            ['name' => 'old verb_hint: verb/not → new verb_hint: negative form'],
            ['category' => 'Seeder Fix Notes']
        );

        $vocabularyTags = [
            'food_drink' => Tag::firstOrCreate(['name' => 'Food & Drink'], ['category' => 'Vocabulary'])->id,
            'media' => Tag::firstOrCreate(['name' => 'Entertainment & Media'], ['category' => 'Vocabulary'])->id,
            'social' => Tag::firstOrCreate(['name' => 'Social Life'], ['category' => 'Vocabulary'])->id,
            'health' => Tag::firstOrCreate(['name' => 'Health & Lifestyle'], ['category' => 'Vocabulary'])->id,
            'fitness' => Tag::firstOrCreate(['name' => 'Fitness & Sports'], ['category' => 'Vocabulary'])->id,
            'study' => Tag::firstOrCreate(['name' => 'School & Study'], ['category' => 'Vocabulary'])->id,
            'hobbies' => Tag::firstOrCreate(['name' => 'Hobbies & Leisure'], ['category' => 'Vocabulary'])->id,
            'communication' => Tag::firstOrCreate(['name' => 'Communication'], ['category' => 'Vocabulary'])->id,
        ];

        $questions = [
            [
                'question' => 'Emma {a1} chocolate this week – only fruit.',
                'answer' => "hasn't eaten",
                'verb_hint' => 'negative form',
                'options' => ["hasn't eaten", "hasn't been eating", "didn't eat", "wasn't eating"],
                'variants' => [
                    'Sophia {a1} bread today – she chose rice instead.',
                    'Lucas {a1} cake this week – he prefers fruit salad.',
                    'Isabella {a1} pizza for months – she’s been on a diet.',
                    'Ethan {a1} meat recently – he became vegetarian.',
                    'Olivia {a1} sweets all day – only vegetables and nuts.',
                ],
                'vocab' => 'food_drink',
            ],
            [
                'question' => 'We {a1} TV lately, just a few short videos online.',
                'answer' => "haven't watched",
                'verb_hint' => 'negative form',
                'options' => ["haven't watched", "haven't been watching", "didn't watch", "weren't watching"],
                'variants' => [
                    'We {a1} movies lately – only some short clips.',
                    'We {a1} the news today – just scrolled headlines.',
                    'We {a1} Netflix this month – too much work.',
                    'We {a1} cartoons in weeks – the kids are busy.',
                    'We {a1} any films since summer – no time for cinema.',
                ],
                'vocab' => 'media',
            ],
            [
                'question' => 'Tom {a1} his friends for weeks. He’s been busy with exams.',
                'answer' => "hasn't seen",
                'verb_hint' => 'negative form',
                'options' => ["hasn't seen", "hasn't been seeing", "didn't see", "wasn't seeing"],
                'variants' => [
                    'Daniel {a1} his cousins for a year – they live abroad.',
                    'Mia {a1} her classmates this month – lessons moved online.',
                    'James {a1} his best friend since Christmas – they lost contact.',
                    'Lily {a1} her teacher for weeks – school was closed.',
                    'Benjamin {a1} his parents much recently – he’s been traveling.',
                ],
                'vocab' => 'social',
            ],
            [
                'question' => 'I {a1} much coffee this morning – only half a cup.',
                'answer' => "haven't drunk",
                'verb_hint' => 'negative form',
                'options' => ["haven't drunk", "haven't been drinking", "didn't drink", "wasn't drinking"],
                'variants' => [
                    'I {a1} tea today – I felt like juice instead.',
                    'I {a1} juice this week – only plain water.',
                    'I {a1} milk recently – my fridge is empty.',
                    'I {a1} enough water this morning – just one glass.',
                    'I {a1} cola since last month – I’m trying to stay healthy.',
                ],
                'vocab' => 'food_drink',
            ],
            [
                'question' => 'They {a1} the gym for months, that’s why they lost shape.',
                'answer' => "haven't gone",
                'verb_hint' => 'negative form',
                'options' => ["haven't gone", "haven't been going", "didn't go", "weren't going"],
                'variants' => [
                    'Noah and Emma {a1} swimming for a long time – the pool is closed.',
                    'Ava and Liam {a1} dancing classes recently – the teacher left.',
                    'Sophia and Jack {a1} hiking since last year – the weather was bad.',
                    'Oliver and Grace {a1} jogging this week – they’re both sick.',
                    'Ella and Henry {a1} yoga for months – too busy at work.',
                ],
                'vocab' => 'fitness',
            ],
            [
                'question' => 'She {a1} enough sleep recently – she looks really tired.',
                'answer' => "hasn't slept",
                'verb_hint' => 'negative form',
                'options' => ["hasn't slept", "hasn't been sleeping", "didn't sleep", "wasn't sleeping"],
                'variants' => [
                    'Amelia {a1} well this week – always waking up at night.',
                    'Matthew {a1} at night recently – too much stress.',
                    'Hannah {a1} properly for days – exams are coming.',
                    'Jacob {a1} much since Monday – too many projects.',
                    'Emily {a1} peacefully lately – bad dreams disturb her.',
                ],
                'vocab' => 'health',
            ],
            [
                'question' => 'I {a1} my English homework today – I was too busy.',
                'answer' => "haven't done",
                'verb_hint' => 'negative form',
                'options' => ["haven't done", "haven't been doing", "didn't do", "wasn't doing"],
                'variants' => [
                    'I {a1} the dishes today – I left them in the sink.',
                    'I {a1} the shopping yet – the store was closed.',
                    'I {a1} my project this week – the deadline moved.',
                    'I {a1} the cleaning – the room is still messy.',
                    'I {a1} my assignment – I completely forgot.',
                ],
                'vocab' => 'study',
            ],
            [
                'question' => 'David {a1} his guitar for weeks – he needs to practice.',
                'answer' => "hasn't played",
                'verb_hint' => 'negative form',
                'options' => ["hasn't played", "hasn't been playing", "didn't play", "wasn't playing"],
                'variants' => [
                    'Oliver {a1} the piano for weeks – he lost motivation.',
                    'Sophia {a1} football recently – her leg hurts.',
                    'William {a1} the drums this month – his set is broken.',
                    'Chloe {a1} video games for days – no free time.',
                    'Alexander {a1} tennis lately – it’s been raining.',
                ],
                'vocab' => 'hobbies',
            ],
            [
                'question' => 'We {a1} any news from Anna since the party.',
                'answer' => "haven't heard",
                'verb_hint' => 'negative form',
                'options' => ["haven't heard", "haven't been hearing", "didn't hear", "weren't hearing"],
                'variants' => [
                    'We {a1} from Peter since April – he moved abroad.',
                    'We {a1} any news lately – the group chat is silent.',
                    'We {a1} anything from Sarah – no emails came.',
                    'We {a1} from John for a long time – he deleted social media.',
                    'We {a1} any updates this week – nothing happened.',
                ],
                'vocab' => 'communication',
            ],
            [
                'question' => 'I {a1} running these days – only walking in the park.',
                'answer' => "haven't been running",
                'verb_hint' => 'negative form',
                'options' => ["haven't run", "haven't been running", "didn't run", "wasn't running"],
                'variants' => [
                    'I {a1} swimming these days – the pool is under repair.',
                    'I {a1} cycling this month – my bike has a flat tire.',
                    'I {a1} exercising recently – I caught a cold.',
                    'I {a1} working out this week – too much office work.',
                    'I {a1} training much lately – my schedule is full.',
                ],
                'vocab' => 'fitness',
            ],
        ];

        $service = new QuestionSeedingService;
        $items = [];

        foreach ($questions as $data) {
            $items[] = [
                'uuid' => (string) Str::uuid(),
                'question' => $data['question'],
                'category_id' => $categoryId,
                'difficulty' => 3,
                'source_id' => $sourceId,
                'flag' => 2,
                'level' => 'B1',
                'tag_ids' => [
                    $tenseTag->id,
                    $grammarTag->id,
                    $vocabularyTags[$data['vocab']],
                    $fixedTag->id,
                    $verbHintChangeTag->id,
                ],
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => $data['answer'],
                        'verb_hint' => $data['verb_hint'],
                    ],
                ],
                'options' => $data['options'],
                'variants' => $data['variants'],
            ];
        }

        $service->seed($items);
    }
}
