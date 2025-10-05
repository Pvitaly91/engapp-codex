<?php

namespace Database\Seeders;

use App\Support\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class PresentPerfectExercisesSeeder extends Seeder
{
    public function run()
    {
        $categoryId = Category::firstOrCreate(['name' => 'Present Perfect'])->id;
        $tenseTag   = Tag::firstOrCreate(['name' => 'Present Perfect'], ['category' => 'Tenses']);

        $detailTags = [
            1 => Tag::firstOrCreate(['name' => 'For or Since'], ['category' => 'Grammar']),
            2 => Tag::firstOrCreate(['name' => 'Present Perfect Forms'], ['category' => 'Grammar']),
            3 => Tag::firstOrCreate(['name' => 'Present Perfect Sentences'], ['category' => 'Grammar']),
            4 => Tag::firstOrCreate(['name' => 'Present Perfect Completion'], ['category' => 'Grammar']),
        ];

        $exercises = [
            [
                'exercise' => 1,
                'title'    => 'Present perfect: for / since',
                'questions' => [
                    ['question' => "I've had this car {a1} years.", 'answer' => 'for',   'options' => ['for', 'since']],
                    ['question' => "We\\'ve known each other {a1} we were in school.", 'answer' => 'since', 'options' => ['for', 'since']],
                    ['question' => "I haven\\'t eaten anything {a1} this morning.", 'answer' => 'since', 'options' => ['for', 'since']],
                    ['question' => "She hasn\\'t slept {a1} two days.", 'answer' => 'for',   'options' => ['for', 'since']],
                    ['question' => "I have wanted to be a writer {a1} I was a child.", 'answer' => 'since', 'options' => ['for', 'since']],
                    ['question' => "We\\'ve been together {a1} over ten years.", 'answer' => 'for',   'options' => ['for', 'since']],
                    ['question' => "I haven\\'t seen John {a1} last July.", 'answer' => 'since', 'options' => ['for', 'since']],
                    ['question' => "It hasn\\'t rained {a1} a long time.", 'answer' => 'for',   'options' => ['for', 'since']],
                    ['question' => "She\\'s worked in this bank {a1} 25 years.", 'answer' => 'for',   'options' => ['for', 'since']],
                    ['question' => "She\\'s worked in this bank {a1} it first opened.", 'answer' => 'since', 'options' => ['for', 'since']],
                ],
            ],
            [
                'exercise' => 2,
                'title'    => 'Choose the most appropriate present perfect forms',
                'questions' => [
                    [
                        'question' => '{a1} to Rome?',
                        'answer'   => 'Have you ever been',
                        'options'  => ['Have ever you been', 'Have you ever been', 'Have you been ever'],
                    ],
                    [
                        'question' => 'Have you had lunch {a1}?',
                        'answer'   => 'yet',
                        'options'  => ['yet', 'just', 'already'],
                    ],
                    [
                        'question' => 'I {a1} the keys that I lost yet.',
                        'answer'   => "haven't found",
                        'options'  => ["haven't find", "haven't finded", "haven't found"],
                    ],
                    [
                        'question' => 'I {a1} Peter since I was 5 years old.',
                        'answer'   => "'ve known",
                        'options'  => ['know', "'ve known'", "'ve knew"],
                    ],
                    [
                        'question' => 'I {a1} my pen. Can I use yours?',
                        'answer'   => 'have lost',
                        'options'  => ["'ve losed", 'lose', 'have lost'],
                    ],
                    [
                        'question' => "A: 'Where's Celine?' B: 'She {a1}.'",
                        'answer'   => "'s just left",
                        'options'  => ["'s just left", 'just has left', 'has left just'],
                    ],
                    [
                        'question' => "I've been here {a1}.",
                        'answer'   => 'for a week',
                        'options'  => ['since a week ago', 'since a week', 'for a week'],
                    ],
                    [
                        'question' => 'We {a1} insects before.',
                        'answer'   => "'ve never eaten",
                        'options'  => ['never have ate', "'ve never eaten", 'never have eaten'],
                    ],
                    [
                        'question' => "We've known each other since we {a1} children.",
                        'answer'   => 'were',
                        'options'  => ['were', 'have been', 'are'],
                    ],
                    [
                        'question' => '{a1} raining yet?',
                        'answer'   => 'Has it stopped',
                        'options'  => ['Has it stop', 'Does it stopped', 'Has it stopped'],
                    ],
                ],
            ],
            [
                'exercise' => 3,
                'title'    => 'Write sentences in the present perfect',
                'questions' => [
                    ['question' => 'He {a1} his keys.',                 'answer' => 'has lost',        'verb_hint' => 'lose'],
                    ['question' => 'I {a1} coffee this morning.',       'answer' => "haven't had",   'verb_hint' => 'not/have'],
                    ['question' => 'She {a1} to us.',                   'answer' => 'has never lied', 'verb_hint' => 'never/lie'],
                    ['question' => 'You {a1} that newspaper yet?',      'answer' => 'Have you read',  'verb_hint' => 'read'],
                    ['question' => 'We {a1} to England many times.',    'answer' => 'have been',      'verb_hint' => 'be'],
                    ['question' => 'Where {a1}?',                       'answer' => 'have you been',  'verb_hint' => 'you/be'],
                    ['question' => 'David {a1} the competition.',       'answer' => 'has won',        'verb_hint' => 'win'],
                    ['question' => 'My son {a1} his leg.',              'answer' => 'has broken',     'verb_hint' => 'break'],
                    ['question' => 'I {a1} her before.',                'answer' => 'have never seen','verb_hint' => 'never/see'],
                    ['question' => 'They {a1} a book.',                 'answer' => 'have written',   'verb_hint' => 'write'],
                ],
            ],
            [
                'exercise' => 4,
                'title'    => 'Complete with present perfect forms',
                'questions' => [
                    ['question' => 'I {a1} breakfast yet.',              'answer' => "haven't had",        'verb_hint' => 'not have'],
                    ['question' => 'I {a1} to the Himalayas...',         'answer' => 'have never been',    'verb_hint' => 'never be'],
                    ['question' => 'We {a1} the truth since the beginning.',    'answer' => 'have known',       'verb_hint' => 'know'],
                    ['question' => 'They {a1} a very expensive car.',    'answer' => 'have bought',        'verb_hint' => 'buy'],
                    ['question' => 'They {a1} married.',                  'answer' => 'have just got',      'verb_hint' => 'just get'],
                    ['question' => 'What {a1}?',                          'answer' => 'have you done',      'verb_hint' => 'you/do'],
                    ['question' => '{a1} abroad?',                        'answer' => 'Have you ever worked','verb_hint' => 'you/ever/work'],
                    ['question' => 'She {a1} a solution.',                'answer' => 'has already found',  'verb_hint' => 'already find'],
                    ['question' => 'I think I {a1} this picture before.', 'answer' => 'have seen',          'verb_hint' => 'see'],
                    ['question' => '{a1} a celebrity?',                   'answer' => 'Have you ever met',  'verb_hint' => 'you/ever/meet'],
                ],
            ],
        ];

        $service = new QuestionSeedingService();
        $items   = [];
        $index   = 1;

        foreach ($exercises as $exercise) {
            $sourceId   = Source::firstOrCreate(['name' => $exercise['title']])->id;
            $detailTag  = $detailTags[$exercise['exercise']];
            $level      = $exercise['exercise'] <= 2 ? 'A2' : 'B1';

            foreach ($exercise['questions'] as $q) {
                $slug = Str::slug(class_basename(self::class));
                $max  = 36 - strlen((string) $index) - 1;
                $uuid = substr($slug, 0, $max) . '-' . $index;

                $items[] = [
                    'uuid'        => $uuid,
                    'question'    => $q['question'],
                    'difficulty'  => $level === 'A1' ? 1 : 2,
                    'category_id' => $categoryId,
                    'flag'        => 0,
                    'source_id'   => $sourceId,
                    'tag_ids'     => [$tenseTag->id, $detailTag->id],
                    'level'       => $level,
                    'answers'     => [
                        ['marker' => 'a1', 'answer' => $q['answer'], 'verb_hint' => $q['verb_hint'] ?? null],
                    ],
                    'options'     => $q['options'] ?? [],
                ];

                $index++;
            }
        }

        $service->seed($items);
    }
}
