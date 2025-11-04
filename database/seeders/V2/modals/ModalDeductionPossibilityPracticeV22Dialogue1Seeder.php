<?php

namespace Database\Seeders\V2\Modals;

use App\Models\Category;
use App\Models\Question;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class ModalDeductionPossibilityPracticeV22Dialogue1Seeder extends QuestionSeeder
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
        $sourceId = Source::firstOrCreate(['name' => 'Custom: Modal Deduction Possibility V2.2 - Dialogue 1'])->id;

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
                'speaker' => 'A',
                'text' => "Oh dear, what's that noise? Here it comes again. Sophia, {a1} you hear it?",
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => 'can',
                        'verb_hint' => 'can',
                        'hint' => 'Використовуємо "can" для запитання про здатність почути щось.',
                        'explanation' => '"Can you hear it?" запитує про здатність почути шум у цей момент.',
                    ],
                ],
                'level' => 'A2',
            ],
            [
                'id' => 2,
                'speaker' => 'B',
                'text' => "No, I {a1} hear anything. It {a2} be the wind. It's very windy tonight. {a3} you please shut the door?",
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => "can't",
                        'verb_hint' => "can't",
                        'hint' => 'Використовуємо "can\'t" щоб сказати, що не можу почути.',
                        'explanation' => '"Can\'t hear" виражає неможливість почути щось.',
                    ],
                    [
                        'marker' => 'a2',
                        'answer' => 'must',
                        'verb_hint' => 'must',
                        'hint' => 'Використовуємо "must" для логічного висновку або впевненості.',
                        'explanation' => '"Must be" виражає впевнене припущення, що це вітер.',
                    ],
                    [
                        'marker' => 'a3',
                        'answer' => 'will / can',
                        'verb_hint' => 'will / can',
                        'hint' => 'Для ввічливого прохання можна використати "will" або "can".',
                        'explanation' => '"Will you" або "Can you please" - це ввічливе прохання зачинити двері.',
                    ],
                ],
                'level' => 'B1',
            ],
            [
                'id' => 3,
                'speaker' => 'A',
                'text' => 'Yeah, sure... {a1} we play Monopoly? We {a2} call our neighbours.',
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => 'shall',
                        'verb_hint' => 'shall',
                        'hint' => 'Використовуємо "shall" для пропозиції зробити щось разом.',
                        'explanation' => '"Shall we" пропонує грати в Монополію разом.',
                    ],
                    [
                        'marker' => 'a2',
                        'answer' => 'could',
                        'verb_hint' => 'could',
                        'hint' => 'Використовуємо "could" для вираження можливості або пропозиції.',
                        'explanation' => '"Could call" виражає можливість або пропозицію подзвонити сусідам.',
                    ],
                ],
                'level' => 'B1',
            ],
            [
                'id' => 4,
                'speaker' => 'B',
                'text' => "Yeah, good idea! {a1} you call them or {a2} I? I'm not sure my mum {a3}...",
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => 'will',
                        'verb_hint' => 'will',
                        'hint' => 'Використовуємо "will" для питання про готовність зробити щось.',
                        'explanation' => '"Will you" запитує, чи хочеш ти подзвонити.',
                    ],
                    [
                        'marker' => 'a2',
                        'answer' => 'shall',
                        'verb_hint' => 'shall',
                        'hint' => 'Використовуємо "shall I" для пропозиції зробити щось самому.',
                        'explanation' => '"Shall I" пропонує зробити це самому.',
                    ],
                    [
                        'marker' => 'a3',
                        'answer' => 'will',
                        'verb_hint' => 'will',
                        'hint' => 'Використовуємо "will" для припущення про майбутню дію.',
                        'explanation' => '"Will" виражає невпевненість щодо того, що мама дозволить.',
                    ],
                ],
                'level' => 'B1',
            ],
            [
                'id' => 5,
                'speaker' => 'A',
                'text' => "You {a1} do it while I look for the game. I {a2} find it. I {a3} have left it at grandma's. Why didn't I listen to mum? She said I {a4} take my books instead.",
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => 'can',
                        'verb_hint' => 'can',
                        'hint' => 'Використовуємо "can" для вираження можливості зробити щось.',
                        'explanation' => '"Can do" означає, що є можливість зателефонувати.',
                    ],
                    [
                        'marker' => 'a2',
                        'answer' => "can't",
                        'verb_hint' => "can't",
                        'hint' => 'Використовуємо "can\'t" для неможливості знайти щось зараз.',
                        'explanation' => '"Can\'t find" виражає неможливість знайти гру.',
                    ],
                    [
                        'marker' => 'a3',
                        'answer' => 'must / might',
                        'verb_hint' => 'must / might',
                        'hint' => 'Використовуємо "must" для впевненого припущення або "might" для можливості.',
                        'explanation' => '"Must have left" або "might have left" виражає припущення про те, що гра залишилася у бабусі.',
                    ],
                    [
                        'marker' => 'a4',
                        'answer' => 'should',
                        'verb_hint' => 'should',
                        'hint' => 'Використовуємо "should" для поради або рекомендації.',
                        'explanation' => '"Should take" виражає пораду мами взяти книжки.',
                    ],
                ],
                'level' => 'B2',
            ],
            [
                'id' => 6,
                'speaker' => 'B',
                'text' => "It's OK! We {a1} make some popcorn and watch a movie!",
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => 'can',
                        'verb_hint' => 'can',
                        'hint' => 'Використовуємо "can" для вираження можливості або пропозиції.',
                        'explanation' => '"Can make" пропонує зробити попкорн і подивитися фільм.',
                    ],
                ],
                'level' => 'A2',
            ],
            [
                'id' => 7,
                'speaker' => 'A',
                'text' => "Great! Let's do that!",
                'answers' => [],
                'level' => 'A2',
            ],
        ];

        $questionPayloads = [];
        $meta = [];

        foreach ($questions as $index => $entry) {
            $questionText = $entry['text'];
            $uuid = $this->generateQuestionUuid('dialogue1', $entry['id'], $questionText);
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
