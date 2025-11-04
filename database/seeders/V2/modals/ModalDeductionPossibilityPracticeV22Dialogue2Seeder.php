<?php

namespace Database\Seeders\V2\Modals;

use App\Models\Category;
use App\Models\Question;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class ModalDeductionPossibilityPracticeV22Dialogue2Seeder extends QuestionSeeder
{
    private array $levelDifficulty = [
        'A1' => 1,
        'A2' => 2,
        'B1' => 3,
        'B2' => 4,
        'C1' => 5,
        'C2' => 5,
    ];

    /**
     * Parse answer string and return array of correct answers.
     * Handles multiple answers separated by ' / '.
     */
    private function parseCorrectAnswers(string $answer): array
    {
        return array_map('trim', explode('/', $answer));
    }

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Modal Deduction & Possibility Practice V2-2'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Custom: Modal Deduction Possibility V2.2 - Dialogue 2'])->id;

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
                'id' => 1,
                'speaker' => 'Ariel',
                'text' => "Ethan, Clint Eastwood's *Hereafter* is at the Odeon this weekend. We {a1} see it.",
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => 'must',
                        'verb_hint' => 'strong necessity / obligation',
                        'hint' => 'Використовуємо "must" для вираження сильної необхідності або бажання.',
                        'explanation' => '"Must see" виражає сильне бажання або необхідність подивитися фільм.',
                    ],
                ],
                'level' => 'B1',
            ],
            [
                'id' => 2,
                'speaker' => 'Ethan',
                'text' => "Yeah, but I'm afraid I {a1} this weekend. I {a2} go to Ipswich. It's my grandmother's birthday.",
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => "can't",
                        'verb_hint' => 'inability / impossibility',
                        'hint' => 'Використовуємо "can\'t" для вираження неможливості зробити щось.',
                        'explanation' => '"Can\'t" показує, що не можу це зробити цих вихідних.',
                    ],
                    [
                        'marker' => 'a2',
                        'answer' => 'must',
                        'verb_hint' => 'strong necessity / obligation',
                        'hint' => 'Використовуємо "must" для вираження обов\'язку або необхідності.',
                        'explanation' => '"Must go" виражає обов\'язок поїхати до Іпсвіча на день народження.',
                    ],
                ],
                'level' => 'B1',
            ],
            [
                'id' => 3,
                'speaker' => 'Ariel',
                'text' => "Well, I {a1} call David. I'm sure he wants to see it.",
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => 'will',
                        'verb_hint' => 'spontaneous decision',
                        'hint' => 'Використовуємо "will" для вираження спонтанного рішення.',
                        'explanation' => '"Will call" виражає спонтанне рішення подзвонити Девіду.',
                    ],
                ],
                'level' => 'B1',
            ],
            [
                'id' => 4,
                'speaker' => 'Ethan',
                'text' => 'Yeah, but you {a1} call him right away. You know how busy he is at the weekends...',
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => 'should',
                        'verb_hint' => 'advice / recommendation',
                        'hint' => 'Використовуємо "should" для поради або рекомендації.',
                        'explanation' => '"Should call" дає пораду зателефонувати негайно.',
                    ],
                ],
                'level' => 'B1',
            ],
            [
                'id' => 5,
                'speaker' => 'Ariel',
                'text' => "Ahah!! You're right, but I {a1} talk to him tonight at Chris's birthday party. Ethan, {a2} you mind keeping this for me while I go to the toilet?",
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => 'can',
                        'verb_hint' => 'ability / possibility',
                        'hint' => 'Використовуємо "can" для вираження можливості.',
                        'explanation' => '"Can talk" виражає можливість поговорити з ним сьогодні ввечері.',
                    ],
                    [
                        'marker' => 'a2',
                        'answer' => 'would',
                        'verb_hint' => 'polite request',
                        'hint' => 'Використовуємо "would you mind" для дуже ввічливого прохання.',
                        'explanation' => '"Would you mind" - це дуже ввічливий спосіб попросити про щось.',
                    ],
                ],
                'level' => 'B2',
            ],
            [
                'id' => 6,
                'speaker' => 'Ethan',
                'text' => 'Oh, sure!',
                'answers' => [],
                'level' => 'A2',
            ],
            [
                'id' => 7,
                'speaker' => 'Ariel',
                'text' => 'Thanks! I {a1} be right back. Hey, you {a2} eat my biscuits, {a3} you?',
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => 'will',
                        'verb_hint' => 'promise / future',
                        'hint' => 'Використовуємо "will" для обіцянки або впевненості в майбутній дії.',
                        'explanation' => '"Will be" виражає обіцянку швидко повернутися.',
                    ],
                    [
                        'marker' => 'a2',
                        'answer' => "won't",
                        'verb_hint' => 'negative prediction',
                        'hint' => 'Використовуємо "won\'t" в tag question для підтвердження.',
                        'explanation' => '"Won\'t eat" в tag question шукає підтвердження, що він не з\'їсть печиво.',
                    ],
                    [
                        'marker' => 'a3',
                        'answer' => 'will',
                        'verb_hint' => 'tag question',
                        'hint' => 'В tag question після негативного "won\'t" ставимо позитивне "will".',
                        'explanation' => 'Tag question з "will you?" після "won\'t eat" шукає підтвердження.',
                    ],
                ],
                'level' => 'B1',
            ],
        ];

        $questionPayloads = [];
        $meta = [];

        foreach ($questions as $index => $entry) {
            $questionText = $entry['text'];
            $uuid = $this->generateQuestionUuid('dialogue2', $entry['id'], $questionText);
            $level = $entry['level'];

            $answers = collect($entry['answers'] ?? [])->map(function (array $answer) {
                $correctAnswers = $this->parseCorrectAnswers($answer['answer']);

                return [
                    'marker' => $answer['marker'],
                    'answer' => $correctAnswers[0],
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

            $answerMap = collect($entry['answers'] ?? [])->mapWithKeys(function ($answer) {
                $correctAnswers = $this->parseCorrectAnswers($answer['answer']);

                return [$answer['marker'] => $correctAnswers[0]];
            })->all();

            $optionMarkers = collect($entry['answers'] ?? [])->mapWithKeys(function ($answer) {
                $correctAnswers = $this->parseCorrectAnswers($answer['answer']);

                return [$correctAnswers[0] => $answer['marker']];
            })->all();

            $explanations = collect($entry['answers'] ?? [])->mapWithKeys(function ($answer) {
                $correctAnswers = $this->parseCorrectAnswers($answer['answer']);

                return [
                    $correctAnswers[0] => $answer['explanation'] ?? '',
                ];
            })->filter()->all();

            $hints = collect($entry['answers'] ?? [])
                ->pluck('hint')
                ->filter()
                ->values()
                ->all();

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
