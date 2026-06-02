<?php

namespace Database\Seeders\V2\Modals;

use App\Models\Category;
use App\Models\Question;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class ModalDeductionPossibilityPracticeV22DialogueSeeder extends QuestionSeeder
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
        $sourceId = Source::firstOrCreate(['name' => 'Custom: Modal Deduction Possibility V2.2 - Dialogue'])->id;

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Modal Verbs Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $modalsTagId = Tag::firstOrCreate(
            ['name' => 'Modal Verbs'],
            ['category' => 'Modals']
        )->id;

        $questions = [
            [
                'question' => 'A: Hi, Tom. Would you like to come to the cinema with me this evening?',
                'level' => 'B1',
                'answers' => [],
                'hint' => null,
                'explanations' => [],
            ],
            [
                'question' => 'B: No, sorry, I can’t. We’re going to my sister’s graduation. We {a1} (take) the train this evening because the ceremony is early tomorrow morning.',
                'level' => 'B1',
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => 'are taking',
                        'verb_hint' => 'take',
                        'hint' => 'Запланована подорож на найближчий час оформлюється теперішнім тривалим часом.',
                        'explanation' => 'Є конкретний розклад на найближчий час, тому використовуємо present continuous: "are taking".',
                    ],
                ],
            ],
            [
                'question' => 'A: Well, that should be good. What’s her degree in?',
                'level' => 'B1',
                'answers' => [],
                'hint' => null,
                'explanations' => [],
            ],
            [
                'question' => 'B: She’s been studying archaeology for the last two years. She was studying history too and {a1} (do) really well in it, but then she decided to specialise.',
                'level' => 'B1',
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => 'did',
                        'verb_hint' => 'do',
                        'hint' => 'Йдеться про завершений успіх у минулому, тож потрібен Past Simple.',
                        'explanation' => 'Результат у минулому описуємо Past Simple: "did really well".',
                    ],
                ],
            ],
            [
                'question' => 'A: What’s she going to do next?',
                'level' => 'B1',
                'answers' => [],
                'hint' => null,
                'explanations' => [],
            ],
            [
                'question' => 'B: She’s really lucky. She speaks Italian and she {a1} (work) on a dig in Rome when I see her next.',
                'level' => 'B2',
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => 'will be working',
                        'verb_hint' => 'work',
                        'hint' => 'Дія триватиме в конкретний момент у майбутньому — вживаємо Future Continuous.',
                        'explanation' => 'На момент наступної зустрічі дія буде в процесі, тому Future Continuous: "will be working".',
                    ],
                ],
            ],
            [
                'question' => 'B: We’re all going to visit her there in the summer holidays. Then I think she wants to do a master’s in Italy as well. By the time she finishes, she {a1} (study) for six years. I think she wants to be an academic.',
                'level' => 'B2',
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => 'will have been studying',
                        'verb_hint' => 'study',
                        'hint' => 'Для тривалості до певного моменту в майбутньому використовуємо Future Perfect Continuous.',
                        'explanation' => 'До моменту завершення магістратури процес триватиме шість років, тому Future Perfect Continuous.',
                    ],
                ],
            ],
            [
                'question' => 'A: Isn’t it great that she {a1} (find) something she really wants to do?',
                'level' => 'B1',
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => 'has found',
                        'verb_hint' => 'find',
                        'hint' => 'Результат, важливий зараз, виражаємо Present Perfect.',
                        'explanation' => 'Вона вже має результат, важливий зараз, — використовуємо Present Perfect: "has found".',
                    ],
                ],
            ],
            [
                'question' => 'B: Definitely. My parents are really proud.',
                'level' => 'B1',
                'answers' => [],
                'hint' => null,
                'explanations' => [],
            ],
        ];

        $questionPayloads = [];
        $meta = [];

        foreach ($questions as $index => $entry) {
            $questionText = $entry['question'];
            $uuid = $this->generateQuestionUuid('dialogue', $index + 1, $questionText);
            $level = $entry['level'];

            $answers = collect($entry['answers'] ?? [])->map(function (array $answer) {
                return [
                    'marker' => $answer['marker'],
                    'answer' => $answer['answer'],
                    'verb_hint' => $answer['verb_hint'] ?? null,
                ];
            })->values()->all();

            $questionPayloads[] = [
                'uuid' => $uuid,
                'question' => $questionText,
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$level] ?? 3,
                'source_id' => $sourceId,
                'flag' => 0,
                'level' => $level,
                'tag_ids' => [$themeTagId, $modalsTagId],
                'answers' => $answers,
                'options' => [],
                'variants' => [$questionText],
                'type' => Question::TYPE_DIALOGUE,
            ];

            $answerMap = collect($entry['answers'] ?? [])->mapWithKeys(fn ($answer) => [$answer['marker'] => $answer['answer']])->all();
            $optionMarkers = collect($entry['answers'] ?? [])->mapWithKeys(fn ($answer) => [$answer['answer'] => $answer['marker']])->all();
            $explanations = collect($entry['answers'] ?? [])->mapWithKeys(fn ($answer) => [
                $answer['answer'] => $answer['explanation'] ?? '',
            ])->filter()->all();

            $hints = collect($entry['answers'] ?? [])
                ->pluck('hint')
                ->filter()
                ->values()
                ->all();

            if (! empty($entry['hint'])) {
                $hints[] = $entry['hint'];
            }

            $meta[] = [
                'uuid' => $uuid,
                'hints' => $hints,
                'answers' => $answerMap,
                'explanations' => $explanations,
                'option_markers' => $optionMarkers,
            ];
        }

        $this->seedQuestionData($questionPayloads, $meta);
    }
}
