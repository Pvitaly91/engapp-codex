<?php

namespace Database\Seeders\V2\Modals;

use App\Models\Category;
use App\Models\Question;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class ModalDeductionPossibilityPracticeV22MatchSeeder extends QuestionSeeder
{
    private array $levelDifficulty = [
        'A1' => 1,
        'A2' => 2,
        'B1' => 3,
        'B2' => 4,
        'C1' => 5,
        'C2' => 5,
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Modal Deduction & Possibility Practice V2-2'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Custom: Modal Deduction Possibility V2.2 - Match'])->id;

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Modal Verbs Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $modalsTagId = Tag::firstOrCreate(
            ['name' => 'Modal Verbs'],
            ['category' => 'Modals']
        )->id;

        $items = [
            [
                'question' => "I can’t speak Spanish but I might go with you.",
                'answer' => "It’s possible, but not certain.",
                'level' => 'B1',
                'hint' => 'Модальне "might" показує, що дія лише можлива, але не гарантована.',
                'explanation' => 'Фраза "might go" виражає невпевненість — рішення не остаточне, тому підходить значення про можливість без гарантії.',
            ],
            [
                'question' => "I’m free on Tuesday, mum. I can take you to the mall.",
                'answer' => "It’s possible for me to do it.",
                'level' => 'A2',
                'hint' => 'Дієслово "can" описує здатність або можливість зробити дію.',
                'explanation' => '"Can take" означає, що мовець має можливість і готовий допомогти, тому правильне пояснення про можливість.',
            ],
            [
                'question' => "Ben’s parents ought not to let him go to bed so late.",
                'answer' => "You’re talking critically. You consider it wrong.",
                'level' => 'B2',
                'hint' => 'Конструкція "ought not to" виражає осуд або пораду виправити поведінку.',
                'explanation' => '"Ought not to" показує, що говорять, як має бути, і засуджують теперішню ситуацію.',
            ],
            [
                'question' => "You should save some money.",
                'answer' => "You’re giving some advice.",
                'level' => 'A2',
                'hint' => 'Модальне "should" часто вживається для рекомендацій та порад.',
                'explanation' => '"Should" пропонує дію як корисну пораду, що відповідає поясненню про надання поради.',
            ],
            [
                'question' => "Excuse me, could you tell me the time, please?",
                'answer' => "You’re making a request.",
                'level' => 'A2',
                'hint' => 'Форма "could you" — ввічливий спосіб попросити когось про щось.',
                'explanation' => 'Речення — ввічливе прохання сказати котру годину, тож підходить пояснення про запит.',
            ],
            [
                'question' => "I was wondering if I might leave earlier.",
                'answer' => "You’re asking permission.",
                'level' => 'B2',
                'hint' => 'Конструкція "might" після "I was wondering" м’яко просить дозволу.',
                'explanation' => 'Фраза обережно запитує, чи можна піти раніше, тобто прохання про дозвіл.',
            ],
            [
                'question' => "I’m not going with you. I can’t swim.",
                'answer' => "You don’t know how to do it.",
                'level' => 'A2',
                'hint' => '"Can’t" у цьому контексті означає відсутність уміння.',
                'explanation' => 'Відмова пов’язана з тим, що людина не вміє плавати, отже пояснення про невміння правильне.',
            ],
            [
                'question' => "You mustn’t drive on the right in England.",
                'answer' => "It is forbidden to do it.",
                'level' => 'B1',
                'hint' => '"Mustn’t" позначає сувору заборону.',
                'explanation' => 'Речення описує правило, яке забороняє діяти певним чином, тобто відповідь про заборону.',
            ],
        ];

        $allAnswers = collect($items)->pluck('answer')->unique()->values()->all();

        $questionPayloads = [];
        $meta = [];

        foreach ($items as $index => $item) {
            $question = $item['question'];
            $uuid = $this->generateQuestionUuid('match', $index + 1, $question);
            $level = $item['level'];
            $answer = $item['answer'];

            $questionPayloads[] = [
                'uuid' => $uuid,
                'question' => $question,
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$level] ?? 3,
                'source_id' => $sourceId,
                'flag' => 0,
                'level' => $level,
                'tag_ids' => [$themeTagId, $modalsTagId],
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => $answer,
                    ],
                ],
                'options' => $allAnswers,
                'variants' => [$question],
                'type' => Question::TYPE_MATCH,
            ];

            $meta[] = [
                'uuid' => $uuid,
                'hints' => [$item['hint']],
                'answers' => ['a1' => $answer],
                'explanations' => [
                    $answer => $item['explanation'],
                ],
                'option_markers' => [
                    $answer => 'a1',
                ],
            ];
        }

        $this->seedQuestionData($questionPayloads, $meta);
    }
}
