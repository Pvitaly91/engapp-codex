<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class ActiveAndPassiveV2Seeder extends QuestionSeeder
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
        $categoryId = Category::firstOrCreate(['name' => 'Passive Voice'])->id;
        $sourceIds = [
            'page1' => Source::firstOrCreate(['name' => 'Custom: Active and Passive V2 (Page 1)'])->id,
        ];

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Active vs Passive Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Active and Passive'],
            ['category' => 'English Grammar Detail']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'Voice Choice'],
            ['category' => 'English Grammar Structure']
        )->id;

        $focusTagId = Tag::firstOrCreate(
            ['name' => 'Passive Voice Forms'],
            ['category' => 'English Grammar Focus']
        )->id;

        $questions = $this->questionEntries();

        $tagIds = [
            $themeTagId,
            $detailTagId,
            $structureTagId,
            $focusTagId,
        ];

        $items = [];
        $meta = [];

        foreach ($questions as $index => $entry) {
            $answers = [];
            foreach ($entry['answers'] as $marker => $answer) {
                $answers[] = [
                    'marker' => $marker,
                    'answer' => $answer,
                    'verb_hint' => $entry['verb_hints'][$marker] ?? null,
                ];
            }

            $options = $this->flattenOptions($entry['options']);

            $uuid = $this->generateQuestionUuid($index + 1, $entry['question']);

            $items[] = [
                'uuid' => $uuid,
                'question' => $entry['question'],
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$entry['level']] ?? 1,
                'source_id' => $sourceIds[$entry['source']] ?? reset($sourceIds),
                'flag' => 0,
                'type' => 0,
                'level' => $entry['level'],
                'tag_ids' => $tagIds,
                'answers' => $answers,
                'options' => $options,
                'variants' => [$entry['question']],
            ];

            $meta[] = [
                'uuid' => $uuid,
                'answers' => $entry['answers'],
                'hints' => $entry['hints'] ?? [],
                'explanations' => $entry['explanations'] ?? [],
            ];
        }

        $this->seedQuestionData($items, []);
        $this->attachHintsAndExplanations($meta);
    }

    private function attachHintsAndExplanations(array $meta): void
    {
        foreach ($meta as $data) {
            $question = Question::where('uuid', $data['uuid'])->first();

            if (! $question) {
                continue;
            }

            $hintText = $this->formatHints($data['hints'] ?? []);
            if ($hintText !== null) {
                QuestionHint::updateOrCreate(
                    ['question_id' => $question->id, 'provider' => 'chatgpt', 'locale' => 'uk'],
                    ['hint' => $hintText]
                );
            }

            $answers = $data['answers'] ?? [];
            foreach ($data['explanations'] ?? [] as $marker => $options) {
                if (! isset($answers[$marker])) {
                    $fallback = reset($answers);
                    $answers[$marker] = is_string($fallback) ? $fallback : (string) $fallback;
                }

                $correct = $answers[$marker];
                if (! is_string($correct)) {
                    $correct = (string) $correct;
                }

                foreach ($options as $option => $text) {
                    ChatGPTExplanation::updateOrCreate(
                        [
                            'question' => $question->question,
                            'wrong_answer' => $option,
                            'correct_answer' => $correct,
                            'language' => 'ua',
                        ],
                        ['explanation' => $text]
                    );
                }
            }
        }
    }

    private function questionEntries(): array
    {
        return [
            [
                'question' => 'This problem {a1} by your brother yesterday.',
                'options' => [
                    'a1' => ['was solved', 'will be solved', 'is solved', 'solves'],
                ],
                'answers' => ['a1' => 'was solved'],
                'level' => 'A1',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'solve'],
                'hints' => [
                    '**Past Simple Passive**: was/were + past participle.',
                    'Слово "yesterday" вказує на минулий час.',
                ],
                'explanations' => [
                    'a1' => [
                        'was solved' => '✅ Past Simple Passive: *was solved* — дія в минулому, виконана кимось (brother).',
                        'will be solved' => '❌ Future Passive не підходить, бо є "yesterday".',
                        'is solved' => '❌ Present Simple Passive не відповідає минулому часу.',
                        'solves' => '❌ Активний стан: підмет мав би виконувати дію сам.',
                    ],
                ],
            ],
            [
                'question' => 'My father wrote this book. It {a1} by my father.',
                'options' => [
                    'a1' => ['will be wrote', 'was write', 'was written', 'is written'],
                ],
                'answers' => ['a1' => 'was written'],
                'level' => 'A1',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'write'],
                'hints' => [
                    'Перетворюємо активний Past Simple в пасив: was/were + past participle.',
                    'Past participle від *write* — *written*.',
                ],
                'explanations' => [
                    'a1' => [
                        'was written' => '✅ Правильний пасив у минулому: *was written*.',
                        'will be wrote' => '❌ Неправильний час (future) і форма participle (має бути *written*).',
                        'was write' => '❌ Потрібен past participle: *written*, а не *write*.',
                        'is written' => '❌ Present Simple Passive, але мова про минулу дію.',
                    ],
                ],
            ],
            [
                'question' => 'This clock {a1} in 1750.',
                'options' => [
                    'a1' => ['is made', 'was made', 'is making', 'will be made'],
                ],
                'answers' => ['a1' => 'was made'],
                'level' => 'A1',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'make'],
                'hints' => [
                    'Дата в минулому → Past Simple Passive.',
                    'Формула: was/were + past participle.',
                ],
                'explanations' => [
                    'a1' => [
                        'was made' => '✅ Past Simple Passive відповідає минулому часу (1750).',
                        'is made' => '❌ Present Simple Passive не підходить для минулої дати.',
                        'is making' => '❌ Active Present Continuous — не пасив.',
                        'will be made' => '❌ Future Passive, але дія вже в минулому.',
                    ],
                ],
            ],
            [
                'question' => 'Bronson scored a goal. Yes, a goal {a1} by Bronson.',
                'options' => [
                    'a1' => ['is scored', 'scored', 'will be scored', 'was scored'],
                ],
                'answers' => ['a1' => 'was scored'],
                'level' => 'A1',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'score'],
                'hints' => [
                    'Past Simple Passive: was/were + past participle.',
                    'Є активне речення в минулому: *scored*.',
                ],
                'explanations' => [
                    'a1' => [
                        'was scored' => '✅ Пасив у минулому: *a goal was scored*.',
                        'is scored' => '❌ Present Simple Passive, не узгоджується з *scored* в минулому.',
                        'scored' => '❌ Потрібен допоміжний дієслово *was* для пасиву.',
                        'will be scored' => '❌ Future Passive не підходить для минулої дії.',
                    ],
                ],
            ],
            [
                'question' => 'This job {a1} by my friend next week.',
                'options' => [
                    'a1' => ['is done', 'did', 'will be done', 'was done'],
                ],
                'answers' => ['a1' => 'will be done'],
                'level' => 'A1',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'do'],
                'hints' => [
                    'Future Passive: will be + past participle.',
                    'Next week → майбутній час.',
                ],
                'explanations' => [
                    'a1' => [
                        'will be done' => '✅ Future Passive правильно передає майбутню дію.',
                        'is done' => '❌ Present Simple Passive, але є "next week".',
                        'did' => '❌ Активний Past Simple без пасиву.',
                        'was done' => '❌ Past Simple Passive не відповідає майбутньому.',
                    ],
                ],
            ],
            [
                'question' => 'This house was {a1} my grandfather.',
                'options' => [
                    'a1' => ['build for', 'build by', 'built for', 'built by'],
                ],
                'answers' => ['a1' => 'built by'],
                'level' => 'A1',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'build'],
                'hints' => [
                    'Passive: was + past participle.',
                    'Виконавець дії вводиться прийменником **by**.',
                ],
                'explanations' => [
                    'a1' => [
                        'built by' => '✅ Потрібен past participle *built* і прийменник *by*.',
                        'build for' => '❌ Неправильна форма дієслова і прийменник не вказує виконавця.',
                        'build by' => '❌ Потрібен past participle *built*.',
                        'built for' => '❌ *For* означає користь/призначення, а не виконавця.',
                    ],
                ],
            ],
            [
                'question' => 'This exercise will {a1} at home by me.',
                'options' => [
                    'a1' => ['be doing', 'do', 'have done', 'be done'],
                ],
                'answers' => ['a1' => 'be done'],
                'level' => 'A1',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'do'],
                'hints' => [
                    'Future Passive: will be + past participle.',
                    'Потрібна форма *be done* після will.',
                ],
                'explanations' => [
                    'a1' => [
                        'be done' => '✅ Правильна форма пасиву в майбутньому: *will be done*.',
                        'be doing' => '❌ Це активний тривалий час, не пасив.',
                        'do' => '❌ Після will у пасиві треба *be + past participle*.',
                        'have done' => '❌ Future Perfect Active, не пасив.',
                    ],
                ],
            ],
            [
                'question' => 'Was the window pane {a1} the children?',
                'options' => [
                    'a1' => ['broke', 'broken by', 'broke for', 'broken for'],
                ],
                'answers' => ['a1' => 'broken by'],
                'level' => 'A1',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'break'],
                'hints' => [
                    'Past Simple Passive у питанні: Was + subject + past participle?',
                    'Виконавець дії вводиться через **by**.',
                ],
                'explanations' => [
                    'a1' => [
                        'broken by' => '✅ Правильна пасивна форма в питанні: *Was it broken by...?*',
                        'broke' => '❌ Активна форма, не пасив.',
                        'broke for' => '❌ Неправильна форма і прийменник.',
                        'broken for' => '❌ *For* не позначає виконавця дії.',
                    ],
                ],
            ],
            [
                'question' => 'All the beds were {a1} my grandmother.',
                'options' => [
                    'a1' => ['made by', 'made for', 'make by', 'make for'],
                ],
                'answers' => ['a1' => 'made by'],
                'level' => 'A1',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'make'],
                'hints' => [
                    'Past Simple Passive: were + past participle.',
                    'Виконавець дії → **by**.',
                ],
                'explanations' => [
                    'a1' => [
                        'made by' => '✅ Past participle *made* + *by* для виконавця.',
                        'made for' => '❌ *For* означає призначення, а не виконавця.',
                        'make by' => '❌ Потрібна форма past participle (*made*).',
                        'make for' => '❌ Неправильна форма дієслова і прийменник.',
                    ],
                ],
            ],
            [
                'question' => 'Many writers were {a1} Shakespeare.',
                'options' => [
                    'a1' => ['influence by', 'influenced by', 'influence for', 'influenced for'],
                ],
                'answers' => ['a1' => 'influenced by'],
                'level' => 'A2',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'influence'],
                'hints' => [
                    'Past Simple Passive: were + past participle.',
                    'Виконавець/джерело впливу позначається **by**.',
                ],
                'explanations' => [
                    'a1' => [
                        'influenced by' => '✅ Потрібен past participle *influenced* + *by*.',
                        'influence by' => '❌ Немає past participle.',
                        'influence for' => '❌ Неправильна форма і прийменник.',
                        'influenced for' => '❌ *For* не вживається для виконавця.',
                    ],
                ],
            ],
            [
                'question' => 'The money {a1} stolen by the thieves if you leave it there.',
                'options' => [
                    'a1' => ['was', 'will be', 'has been', 'is'],
                ],
                'answers' => ['a1' => 'will be'],
                'level' => 'A2',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'steal'],
                'hints' => [
                    'Умова "if you leave it there" вказує на майбутнє.',
                    'Future Passive: will be + past participle.',
                ],
                'explanations' => [
                    'a1' => [
                        'will be' => '✅ Майбутній пасив: *will be stolen*.',
                        'was' => '❌ Минуле не підходить для умови про майбутнє.',
                        'has been' => '❌ Present Perfect Passive, але тут прогноз майбутнього.',
                        'is' => '❌ Present Simple Passive не підходить у цій умові.',
                    ],
                ],
            ],
            [
                'question' => 'Mr Johnson {a1} this book.',
                'options' => [
                    'a1' => ['is translated', 'translated by', 'translated', 'was translated'],
                ],
                'answers' => ['a1' => 'translated'],
                'level' => 'A1',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'translate'],
                'hints' => [
                    'Підмет виконує дію → Active Voice.',
                    'Past Simple Active: V2 (translated).',
                ],
                'explanations' => [
                    'a1' => [
                        'translated' => '✅ Активний Past Simple: *Mr Johnson translated this book*.',
                        'is translated' => '❌ Пасив: підмет не виконує дію.',
                        'translated by' => '❌ Неповна конструкція без виконавця після *by*.',
                        'was translated' => '❌ Пасив, а тут потрібен актив.',
                    ],
                ],
            ],
            [
                'question' => 'This policeman {a1} that man.',
                'options' => [
                    'a1' => ['was arrested by', 'arrested for', 'arrested', 'will be arrested'],
                ],
                'answers' => ['a1' => 'arrested'],
                'level' => 'A1',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'arrest'],
                'hints' => [
                    'Підмет виконує дію → Active Voice.',
                    'Past Simple Active: V2 (arrested).',
                ],
                'explanations' => [
                    'a1' => [
                        'arrested' => '✅ Активний Past Simple: *The policeman arrested the man*.',
                        'was arrested by' => '❌ Пасив; підметом мав би бути той, кого заарештували.',
                        'arrested for' => '❌ Неповна конструкція без об’єкта після *for*.',
                        'will be arrested' => '❌ Future Passive не підходить за змістом.',
                    ],
                ],
            ],
            [
                'question' => 'We will {a1} by that teacher.',
                'options' => [
                    'a1' => ['be teached', 'have taught', 'be taught', 'been taught'],
                ],
                'answers' => ['a1' => 'be taught'],
                'level' => 'A1',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'teach'],
                'hints' => [
                    'Future Passive: will be + past participle.',
                    'Past participle від *teach* — *taught*.',
                ],
                'explanations' => [
                    'a1' => [
                        'be taught' => '✅ Правильно: *will be taught*.',
                        'be teached' => '❌ Неправильна форма: *taught*, не *teached*.',
                        'have taught' => '❌ Future Perfect Active, не пасив.',
                        'been taught' => '❌ Потрібен допоміжний *be* після *will*.',
                    ],
                ],
            ],
            [
                'question' => 'Many things {a1} in this house.',
                'options' => [
                    'a1' => ['is said', 'are said', 'they say', 'they are said'],
                ],
                'answers' => ['a1' => 'are said'],
                'level' => 'A2',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'say'],
                'hints' => [
                    'Present Simple Passive: am/is/are + past participle.',
                    'Підмет у множині → *are*.',
                ],
                'explanations' => [
                    'a1' => [
                        'are said' => '✅ Present Simple Passive у множині.',
                        'is said' => '❌ Однина, не узгоджується з *many things*.',
                        'they say' => '❌ Активний стан без пасиву.',
                        'they are said' => '❌ Невідповідна структура і зайвий підмет.',
                    ],
                ],
            ],
            [
                'question' => 'This mansion {a1} in 1750.',
                'options' => [
                    'a1' => ['is built', 'was built', 'will be built', 'builded'],
                ],
                'answers' => ['a1' => 'was built'],
                'level' => 'A1',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'build'],
                'hints' => [
                    'Дата в минулому → Past Simple Passive.',
                    'Past participle від *build* — *built*.',
                ],
                'explanations' => [
                    'a1' => [
                        'was built' => '✅ Past Simple Passive відповідає минулому часу.',
                        'is built' => '❌ Present Simple Passive не підходить для 1750.',
                        'will be built' => '❌ Future Passive, але дія вже відбулася.',
                        'builded' => '❌ Неправильна форма дієслова (має бути *built*).',
                    ],
                ],
            ],
            [
                'question' => 'They will {a1} this car soon.',
                'options' => [
                    'a1' => ['be bought', 'buying', 'buy', 'is bought'],
                ],
                'answers' => ['a1' => 'buy'],
                'level' => 'A1',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'buy'],
                'hints' => [
                    'Active Voice в майбутньому: will + base verb.',
                    'Підмет виконує дію сам.',
                ],
                'explanations' => [
                    'a1' => [
                        'buy' => '✅ Активне речення: *They will buy this car*.',
                        'be bought' => '❌ Це пасив; потрібен актив.',
                        'buying' => '❌ Після *will* використовується базова форма дієслова.',
                        'is bought' => '❌ Present Simple Passive не відповідає майбутньому.',
                    ],
                ],
            ],
            [
                'question' => 'This car will {a1} soon.',
                'options' => [
                    'a1' => ['be bought', 'be buying', 'buy', 'is bought'],
                ],
                'answers' => ['a1' => 'be bought'],
                'level' => 'A1',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'buy'],
                'hints' => [
                    'Future Passive: will be + past participle.',
                    'Підмет не виконує дію, а зазнає її.',
                ],
                'explanations' => [
                    'a1' => [
                        'be bought' => '✅ Правильна форма пасиву в майбутньому.',
                        'be buying' => '❌ Active Continuous, не пасив.',
                        'buy' => '❌ Потрібно *be + past participle* для пасиву.',
                        'is bought' => '❌ Present Simple Passive не відповідає майбутньому.',
                    ],
                ],
            ],
            [
                'question' => 'The jar {a1} by the maid.',
                'options' => [
                    'a1' => ['was broken', 'broke', 'is broken', 'breaks'],
                ],
                'answers' => ['a1' => 'was broken'],
                'level' => 'A1',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'break'],
                'hints' => [
                    'Past Simple Passive: was/were + past participle.',
                    'Є виконавець дії (*by the maid*).',
                ],
                'explanations' => [
                    'a1' => [
                        'was broken' => '✅ Пасив у минулому з виконавцем дії.',
                        'broke' => '❌ Активний Past Simple; підмет не виконує дію.',
                        'is broken' => '❌ Present Simple Passive не підходить для минулого.',
                        'breaks' => '❌ Active Present Simple.',
                    ],
                ],
            ],
            [
                'question' => 'The centre forward {a1} a goal.',
                'options' => [
                    'a1' => ['was scored', 'scores', 'is scored', 'has scored'],
                ],
                'answers' => ['a1' => 'scores'],
                'level' => 'A2',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'score'],
                'hints' => [
                    'Active Voice для звички/факту: Present Simple.',
                    'Підмет виконує дію → *scores*.',
                ],
                'explanations' => [
                    'a1' => [
                        'scores' => '✅ Present Simple Active для загального факту.',
                        'was scored' => '❌ Пасив у минулому; підмет не зазнає дії.',
                        'is scored' => '❌ Пасив у теперішньому, але підмет виконує дію.',
                        'has scored' => '❌ Present Perfect вказує на результат, але без контексту потрібен Present Simple.',
                    ],
                ],
            ],
        ];
    }
}
