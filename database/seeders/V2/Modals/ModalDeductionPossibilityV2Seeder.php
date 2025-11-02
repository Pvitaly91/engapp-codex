<?php

namespace Database\Seeders\V2\Modals;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Source;
use App\Models\Tag;
use App\Services\QuestionSeedingService;
use Database\Seeders\QuestionSeeder;
use Illuminate\Support\Facades\Schema;

class ModalDeductionPossibilityV2Seeder extends QuestionSeeder
{
    private array $levelDifficulty = [
        'A1' => 1,
        'A2' => 2,
        'B1' => 3,
        'B2' => 4,
        'C1' => 5,
        'C2' => 5,
    ];

    private array $patternConfig = [
        'possibility_progressive' => [
            'detail' => 'possibility',
            'structure_key' => 'modal_be_ing',
            'hint_formula' => 'modal (might/may/could) + be + V-ing',
            'hint_usage' => 'припускаємо дію, що триває просто зараз',
            'markers' => 'seems, acting strangely, right now',
            'correct_reason' => 'виражає припущення про дію, що відбувається у момент мовлення',
        ],
        'state_possibility' => [
            'detail' => 'possibility',
            'structure_key' => 'modal_be',
            'hint_formula' => 'modal (might/may/could) + be + прикметник/іменник',
            'hint_usage' => 'описуємо можливий стан або факт без упевненості',
            'markers' => 'maybe, I think, possible',
            'correct_reason' => 'дає м\'яку здогадку про стан без твердої впевненості',
        ],
        'general_possibility' => [
            'detail' => 'possibility',
            'structure_key' => 'modal_base',
            'hint_formula' => 'modal (might/may/could) + base verb',
            'hint_usage' => 'говоримо про реальну, але не гарантовану дію',
            'markers' => 'perhaps, sometimes, could happen',
            'correct_reason' => 'залишаємо дію можливою, але без гарантії',
        ],
        'uncertain_negative' => [
            'detail' => 'possibility',
            'structure_key' => 'modal_negative',
            'hint_formula' => 'modal (might/may/could) + not + base verb',
            'hint_usage' => 'припускаємо, що дія може не відбутися',
            'markers' => 'traffic, doubt, worried',
            'correct_reason' => 'дає обережне заперечення без категоричності',
        ],
        'strong_certainty' => [
            'detail' => 'certainty',
            'structure_key' => 'modal_be',
            'hint_formula' => 'must + base verb / be',
            'hint_usage' => 'коли є вагомі докази і ми впевнені у висновку',
            'markers' => 'sure, never lies, clear evidence',
            'correct_reason' => 'виражає впевненість на основі очевидних доказів',
        ],
        'impossibility' => [
            'detail' => 'impossibility',
            'structure_key' => 'modal_negative',
            'hint_formula' => "can't + base verb / be",
            'hint_usage' => 'суперечить фактам, тож дія неможлива',
            'markers' => 'no way, impossible, different facts',
            'correct_reason' => 'повністю заперечуємо можливість через суперечливі факти',
        ],
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Modal Deduction & Possibility Practice V2'])->id;
        $sourceIds = [
            'exercise1' => Source::firstOrCreate(['name' => 'Custom: Modal Deduction Possibility V2 - Exercise 1'])->id,
            'exercise2' => Source::firstOrCreate(['name' => 'Custom: Modal Deduction Possibility V2 - Exercise 2'])->id,
            'exercise3' => Source::firstOrCreate(['name' => 'Custom: Modal Deduction Possibility V2 - Exercise 3'])->id,
        ];

        $defaultSourceId = reset($sourceIds);

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Modal Verbs Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $modalsTagId = Tag::firstOrCreate(
            ['name' => 'Modal Verbs'],
            ['category' => 'Modals']
        )->id;

        $detailTags = [
            'possibility' => Tag::firstOrCreate(['name' => 'Modal Possibility Focus'], ['category' => 'English Grammar Detail'])->id,
            'certainty' => Tag::firstOrCreate(['name' => 'Modal Certainty Focus'], ['category' => 'English Grammar Detail'])->id,
            'impossibility' => Tag::firstOrCreate(['name' => 'Modal Impossibility Focus'], ['category' => 'English Grammar Detail'])->id,
        ];

        $structureTags = [
            'modal_base' => Tag::firstOrCreate(['name' => 'Structure: modal + base verb'], ['category' => 'English Grammar Structure'])->id,
            'modal_be' => Tag::firstOrCreate(['name' => 'Structure: modal + be'], ['category' => 'English Grammar Structure'])->id,
            'modal_be_ing' => Tag::firstOrCreate(['name' => 'Structure: modal + be + V-ing'], ['category' => 'English Grammar Structure'])->id,
            'modal_negative' => Tag::firstOrCreate(['name' => 'Structure: modal + not'], ['category' => 'English Grammar Structure'])->id,
        ];

        $questions = [
            [
                'question' => 'Paul is behaving in a very unusual way. I think he {a1} again.',
                'level' => 'B1',
                'source' => 'exercise1',
                'parts' => [
                    'a1' => [
                        'pattern' => 'possibility_progressive',
                        'answers' => ['might be drinking'],
                        'options' => [
                            'might be drinking',
                            'must be drinking',
                            "can't be drinking",
                            'could be drinking',
                        ],
                        'verb_hint' => 'modal of possibility + be + V-ing',
                    ],
                ],
            ],
            [
                'question' => 'I think there {a1} a mistake in your tax return. You should check it.',
                'level' => 'B1',
                'source' => 'exercise1',
                'parts' => [
                    'a1' => [
                        'pattern' => 'state_possibility',
                        'answers' => ['might be', 'may be'],
                        'options' => [
                            'might be',
                            'may be',
                            "can't be",
                            'must be',
                        ],
                        'verb_hint' => 'modal of possibility + be',
                    ],
                ],
            ],
            [
                'question' => 'If Suzan said that, it {a1} true. She never lies.',
                'level' => 'B1',
                'source' => 'exercise1',
                'parts' => [
                    'a1' => [
                        'pattern' => 'strong_certainty',
                        'answers' => ['must be'],
                        'options' => [
                            'must be',
                            'might be',
                            'could be',
                            "can't be",
                        ],
                        'verb_hint' => 'modal of certainty + be',
                    ],
                ],
            ],
            [
                'question' => "Sorry, but I'm not Connor. You {a1} me for someone else.",
                'level' => 'B1',
                'source' => 'exercise1',
                'parts' => [
                    'a1' => [
                        'pattern' => 'strong_certainty',
                        'answers' => ['must be confusing'],
                        'options' => [
                            'must be confusing',
                            'might be confusing',
                            "can't be confusing",
                            'must confuse',
                        ],
                        'verb_hint' => 'modal of certainty + be + adjective',
                    ],
                ],
            ],
            [
                'question' => "I'm not sure I trust Peter. He {a1} the person we think he is.",
                'level' => 'B1',
                'source' => 'exercise1',
                'parts' => [
                    'a1' => [
                        'pattern' => 'impossibility',
                        'answers' => ["can't be"],
                        'options' => [
                            "can't be",
                            'might be',
                            'must be',
                            'might not be',
                        ],
                        'verb_hint' => 'modal of impossibility + be',
                    ],
                ],
            ],
            [
                'question' => 'You have been walking for ten hours. You {a1} exhausted.',
                'level' => 'B1',
                'source' => 'exercise1',
                'parts' => [
                    'a1' => [
                        'pattern' => 'strong_certainty',
                        'answers' => ['must be'],
                        'options' => [
                            'must be',
                            'might be',
                            "can't be",
                            'could be',
                        ],
                        'verb_hint' => 'modal of certainty + be',
                    ],
                ],
            ],
            [
                'question' => 'He {a1} his son, they look completely different.',
                'level' => 'B1',
                'source' => 'exercise1',
                'parts' => [
                    'a1' => [
                        'pattern' => 'impossibility',
                        'answers' => ["can't be"],
                        'options' => [
                            "can't be",
                            'must be',
                            'might be',
                            'may be',
                        ],
                        'verb_hint' => 'modal of impossibility + be',
                    ],
                ],
            ],
            [
                'question' => "There's a bit of traffic, so I {a1} arrive in time.",
                'level' => 'B1',
                'source' => 'exercise1',
                'parts' => [
                    'a1' => [
                        'pattern' => 'uncertain_negative',
                        'answers' => ['might not', 'may not'],
                        'options' => [
                            'might not',
                            'may not',
                            'must',
                            "can't",
                        ],
                        'verb_hint' => 'modal of possibility + not + base verb',
                    ],
                ],
            ],
            [
                'question' => 'He {a1} be very proud of you right now. You disappointed him.',
                'level' => 'B1',
                'source' => 'exercise1',
                'parts' => [
                    'a1' => [
                        'pattern' => 'impossibility',
                        'answers' => ["can't be"],
                        'options' => [
                            "can't be",
                            'must be',
                            'might be',
                            'should be',
                        ],
                        'verb_hint' => 'modal of impossibility + be',
                    ],
                ],
            ],
            [
                'question' => "I wouldn't swim in this river if I were you. It {a1} dangerous.",
                'level' => 'B1',
                'source' => 'exercise1',
                'parts' => [
                    'a1' => [
                        'pattern' => 'state_possibility',
                        'answers' => ['might be', 'may be'],
                        'options' => [
                            'might be',
                            'may be',
                            'must be',
                            'could be',
                        ],
                        'verb_hint' => 'modal of possibility + be',
                    ],
                ],
            ],
            [
                'question' => 'The phone is ringing. It {a1} be Charlotte; she said she would call this morning.',
                'level' => 'B1',
                'source' => 'exercise2',
                'parts' => [
                    'a1' => [
                        'pattern' => 'strong_certainty',
                        'answers' => ['must'],
                        'options' => ['must', 'may', 'might', 'could', "can't"],
                        'verb_hint' => 'strong certainty modal',
                    ],
                ],
            ],
            [
                'question' => 'She {a1} be in love with him. She told me she hates him.',
                'level' => 'B1',
                'source' => 'exercise2',
                'parts' => [
                    'a1' => [
                        'pattern' => 'impossibility',
                        'answers' => ["can't"],
                        'options' => ['must', 'may', 'might', 'could', "can't"],
                        'verb_hint' => 'impossibility modal',
                    ],
                ],
            ],
            [
                'question' => 'He {a1} be at the gym right now. Sometimes he goes there at this time.',
                'level' => 'B1',
                'source' => 'exercise2',
                'parts' => [
                    'a1' => [
                        'pattern' => 'general_possibility',
                        'answers' => ['might'],
                        'options' => ['must', 'may', 'might', 'could', "can't"],
                        'verb_hint' => 'possibility modal',
                    ],
                ],
            ],
            [
                'question' => 'This is not possible. You {a1} be serious!',
                'level' => 'B1',
                'source' => 'exercise2',
                'parts' => [
                    'a1' => [
                        'pattern' => 'impossibility',
                        'answers' => ["can't"],
                        'options' => ['must', 'may', 'might', 'could', "can't"],
                        'verb_hint' => 'impossibility modal',
                    ],
                ],
            ],
            [
                'question' => 'If he drives a Jaguar, he {a1} be quite rich.',
                'level' => 'B1',
                'source' => 'exercise2',
                'parts' => [
                    'a1' => [
                        'pattern' => 'strong_certainty',
                        'answers' => ['must'],
                        'options' => ['must', 'may', 'might', 'could', "can't"],
                        'verb_hint' => 'strong certainty modal',
                    ],
                ],
            ],
            [
                'question' => 'You should pick up the phone. It {a1} be an important call.',
                'level' => 'B1',
                'source' => 'exercise2',
                'parts' => [
                    'a1' => [
                        'pattern' => 'general_possibility',
                        'answers' => ['might'],
                        'options' => ['must', 'may', 'might', 'could', "can't"],
                        'verb_hint' => 'possibility modal',
                    ],
                ],
            ],
            [
                'question' => "He {a1} know the answer, he's the best in the class.",
                'level' => 'B1',
                'source' => 'exercise2',
                'parts' => [
                    'a1' => [
                        'pattern' => 'strong_certainty',
                        'answers' => ['must'],
                        'options' => ['must', 'may', 'might', 'could', "can't"],
                        'verb_hint' => 'strong certainty modal',
                    ],
                ],
            ],
            [
                'question' => 'You {a1} be right, but it’s better if we check.',
                'level' => 'B1',
                'source' => 'exercise2',
                'parts' => [
                    'a1' => [
                        'pattern' => 'general_possibility',
                        'answers' => ['might'],
                        'options' => ['must', 'may', 'might', 'could', "can't"],
                        'verb_hint' => 'possibility modal',
                    ],
                ],
            ],
            [
                'question' => 'He {a1} be in class. I saw him at the library a minute ago.',
                'level' => 'B1',
                'source' => 'exercise2',
                'parts' => [
                    'a1' => [
                        'pattern' => 'impossibility',
                        'answers' => ["can't"],
                        'options' => ['must', 'may', 'might', 'could', "can't"],
                        'verb_hint' => 'impossibility modal',
                    ],
                ],
            ],
            [
                'question' => 'A: Who’s at the door? B: I don’t know; it {a1} be John.',
                'level' => 'B1',
                'source' => 'exercise2',
                'parts' => [
                    'a1' => [
                        'pattern' => 'general_possibility',
                        'answers' => ['could'],
                        'options' => ['must', 'may', 'might', 'could', "can't"],
                        'verb_hint' => 'open possibility modal',
                    ],
                ],
            ],
            [
                'question' => 'Someone is knocking at the door. — It {a1} be the pizza delivery man. I ordered a pizza.',
                'level' => 'B1',
                'source' => 'exercise3',
                'parts' => [
                    'a1' => [
                        'pattern' => 'strong_certainty',
                        'answers' => ['must'],
                        'options' => ['must', 'might', 'might not', "can't"],
                        'verb_hint' => 'strong certainty modal',
                    ],
                ],
            ],
            [
                'question' => 'I know you ordered a pizza. But it {a1} be him, because you ordered the pizza five minutes ago.',
                'level' => 'B1',
                'source' => 'exercise3',
                'parts' => [
                    'a1' => [
                        'pattern' => 'impossibility',
                        'answers' => ["can't"],
                        'options' => ['must', 'might', 'might not', "can't"],
                        'verb_hint' => 'impossibility modal',
                    ],
                ],
            ],
            [
                'question' => 'Then it {a1} be your sister.',
                'level' => 'B1',
                'source' => 'exercise3',
                'parts' => [
                    'a1' => [
                        'pattern' => 'general_possibility',
                        'answers' => ['might'],
                        'options' => ['must', 'might', 'might not', "can't"],
                        'verb_hint' => 'possibility modal',
                    ],
                ],
            ],
            [
                'question' => "No, it {a1} be my sister, she's out of town.",
                'level' => 'B1',
                'source' => 'exercise3',
                'parts' => [
                    'a1' => [
                        'pattern' => 'impossibility',
                        'answers' => ["can't"],
                        'options' => ['must', 'might', 'might not', "can't"],
                        'verb_hint' => 'impossibility modal',
                    ],
                ],
            ],
            [
                'question' => 'Well, then just open the door; it {a1} be important.',
                'level' => 'B1',
                'source' => 'exercise3',
                'parts' => [
                    'a1' => [
                        'pattern' => 'general_possibility',
                        'answers' => ['might'],
                        'options' => ['must', 'might', 'might not', "can't"],
                        'verb_hint' => 'possibility modal',
                    ],
                ],
            ],
            [
                'question' => 'I should tell you something and I think you {a1} like it.',
                'level' => 'B1',
                'source' => 'exercise3',
                'parts' => [
                    'a1' => [
                        'pattern' => 'uncertain_negative',
                        'answers' => ['might not'],
                        'options' => ['must', 'might', 'might not', "can't"],
                        'verb_hint' => 'modal of possibility + not + base verb',
                    ],
                ],
            ],
            [
                'question' => 'Come on! Tell me what it is. It {a1} be that bad.',
                'level' => 'B1',
                'source' => 'exercise3',
                'parts' => [
                    'a1' => [
                        'pattern' => 'impossibility',
                        'answers' => ["can't"],
                        'options' => ['must', 'might', 'might not', "can't"],
                        'verb_hint' => 'impossibility modal',
                    ],
                ],
            ],
            [
                'question' => "I'm afraid you {a1} react badly.",
                'level' => 'B1',
                'source' => 'exercise3',
                'parts' => [
                    'a1' => [
                        'pattern' => 'general_possibility',
                        'answers' => ['might'],
                        'options' => ['must', 'might', 'might not', "can't"],
                        'verb_hint' => 'possibility modal',
                    ],
                ],
            ],
            [
                'question' => "Then I'm sure it {a1} be serious. Please, tell me.",
                'level' => 'B1',
                'source' => 'exercise3',
                'parts' => [
                    'a1' => [
                        'pattern' => 'strong_certainty',
                        'answers' => ['must'],
                        'options' => ['must', 'might', 'might not', "can't"],
                        'verb_hint' => 'strong certainty modal',
                    ],
                ],
            ],
            [
                'question' => "I know you {a1} be upset right now, but…",
                'level' => 'B1',
                'source' => 'exercise3',
                'parts' => [
                    'a1' => [
                        'pattern' => 'strong_certainty',
                        'answers' => ['must'],
                        'options' => ['must', 'might', 'might not', "can't"],
                        'verb_hint' => 'strong certainty modal',
                    ],
                ],
            ],
        ];

        $service = new QuestionSeedingService();
        $items = [];
        $meta = [];

        foreach ($questions as $index => $questionData) {
            $questionText = $questionData['question'];
            $parts = $questionData['parts'];
            $uuid = $this->generateQuestionUuid($index + 1, $questionText);

            $defaultAnswers = [];
            foreach ($parts as $marker => $config) {
                $defaultAnswers[$marker] = $config['answers'][0] ?? '';
            }

            $baseExample = $this->formatExample($questionText, $defaultAnswers);

            [$options, $optionMarkers] = $this->prepareOptions($parts);
            $answerEntries = $this->buildAnswerEntries($parts);

            $hints = [];
            $explanations = [];
            $correctLookup = [];
            $tagIds = [$themeTagId, $modalsTagId];

            foreach ($parts as $marker => $config) {
                $pattern = $config['pattern'];
                $patternSettings = $this->patternConfig[$pattern] ?? null;
                $exampleForMarker = $this->formatExample($questionText, array_merge($defaultAnswers, [$marker => $config['answers'][0] ?? '']));

                if ($patternSettings) {
                    $hints[$marker] = $this->buildHint($patternSettings, $exampleForMarker);

                    $detailKey = $patternSettings['detail'] ?? null;
                    if ($detailKey && isset($detailTags[$detailKey])) {
                        $tagIds[] = $detailTags[$detailKey];
                    }

                    $structureKey = $patternSettings['structure_key'] ?? null;
                    if ($structureKey && isset($structureTags[$structureKey])) {
                        $tagIds[] = $structureTags[$structureKey];
                    }
                } else {
                    $hints[$marker] = $this->buildHint([
                        'hint_formula' => '',
                        'hint_usage' => '',
                        'markers' => '',
                        'correct_reason' => '',
                    ], $exampleForMarker);
                }

                $answersForMarker = $config['answers'];
                foreach ($config['options'] as $option) {
                    $optionExample = $this->formatExample($questionText, array_merge($defaultAnswers, [$marker => $option]));

                    if (in_array($option, $answersForMarker, true)) {
                        $explanations[$option] = $this->buildCorrectExplanation($pattern, $option, $optionExample, $patternSettings ?? []);
                        $correctLookup[$option] = $option;
                    } else {
                        $explanations[$option] = $this->buildWrongExplanation($pattern, $option, $baseExample, $patternSettings ?? []);
                    }
                }
            }

            $items[] = [
                'uuid' => $uuid,
                'question' => $questionText,
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$questionData['level']] ?? 3,
                'source_id' => $sourceIds[$questionData['source']] ?? $defaultSourceId,
                'flag' => 0,
                'level' => $questionData['level'],
                'tag_ids' => array_values(array_unique($tagIds)),
                'answers' => $answerEntries,
                'options' => $options,
                'variants' => [$questionText],
                'seeder' => static::class,
            ];

            $meta[] = [
                'uuid' => $uuid,
                'hints' => $hints,
                'answers' => array_map(fn ($config) => $config['answers'][0] ?? '', $parts),
                'explanations' => $explanations,
                'option_markers' => $optionMarkers,
                'correct_lookup' => $correctLookup,
            ];
        }

        $service->seed($items);

        foreach ($meta as $data) {
            $question = Question::where('uuid', $data['uuid'])->first();
            if (! $question) {
                continue;
            }

            if (Schema::hasColumn('questions', 'seeder') && empty($question->seeder)) {
                $question->forceFill(['seeder' => static::class])->save();
            }

            $hintText = $this->formatHintBlocks($data['hints']);
            if ($hintText !== null) {
                QuestionHint::updateOrCreate(
                    ['question_id' => $question->id, 'provider' => 'chatgpt', 'locale' => 'uk'],
                    ['hint' => $hintText]
                );
            }

            foreach ($data['explanations'] as $option => $text) {
                $marker = $data['option_markers'][$option] ?? array_key_first($data['answers']);
                $correct = $data['correct_lookup'][$option] ?? ($marker ? ($data['answers'][$marker] ?? reset($data['answers'])) : reset($data['answers']));

                if (! is_string($correct)) {
                    $correct = (string) $correct;
                }

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

    private function prepareOptions(array $parts): array
    {
        $options = [];
        $markers = [];

        foreach ($parts as $marker => $config) {
            foreach ($config['options'] as $option) {
                if (! array_key_exists($option, $markers)) {
                    $options[] = $option;
                }

                $markers[$option] = $marker;
            }
        }

        return [$options, $markers];
    }

    private function buildAnswerEntries(array $parts): array
    {
        $entries = [];

        foreach ($parts as $marker => $config) {
            foreach ($config['answers'] as $answer) {
                $entries[] = [
                    'marker' => $marker,
                    'answer' => $answer,
                    'verb_hint' => $this->formatVerbHint($config['verb_hint'] ?? null),
                ];
            }
        }

        return $entries;
    }

    private function buildHint(array $config, string $example): string
    {
        $parts = [];

        if (! empty($config['hint_formula'])) {
            $parts[] = 'Формула: **' . $config['hint_formula'] . '**.';
        }

        if (! empty($config['hint_usage'])) {
            $parts[] = 'Використання: ' . $config['hint_usage'] . '.';
        }

        if (! empty($config['markers'])) {
            $parts[] = 'Маркери: ' . $config['markers'] . '.';
        }

        $parts[] = 'Приклад: *' . $example . '*.';

        return implode(' ', $parts);
    }

    private function buildCorrectExplanation(string $pattern, string $option, string $example, array $config): string
    {
        $reason = $config['correct_reason'] ?? 'передає потрібне значення';

        return "✅ «{$option}» {$reason}. Приклад: *{$example}*.";
    }

    private function buildWrongExplanation(string $pattern, string $option, string $baseExample, array $config): string
    {
        $modalType = $this->classifyModal($option);

        return match ($pattern) {
            'strong_certainty' => match ($modalType) {
                'must' => "❌ «{$option}» не підходить у цій позиції або потребує іншої форми, зверніть увагу на приклад: *{$baseExample}*.",
                'might', 'may', 'could' => "❌ «{$option}» занадто невпевнене, а контекст дає чіткі докази. Приклад: *{$baseExample}*.",
                "can't" => "❌ «{$option}» заперечує ситуацію, хоча ми впевнені у протилежному. Приклад: *{$baseExample}*.",
                default => "❌ Форма «{$option}» не передає впевненість, яку вимагає контекст. Приклад: *{$baseExample}*.",
            },
            'impossibility' => match ($modalType) {
                'must' => "❌ «{$option}» звучить як впевнене ствердження, але факти роблять ситуацію неможливою. Приклад: *{$baseExample}*.",
                'might', 'may', 'could' => "❌ «{$option}» лише допускає можливість, тоді як факти її заперечують. Приклад: *{$baseExample}*.",
                'should' => "❌ «{$option}» висловлює пораду, а не неможливість. Приклад: *{$baseExample}*.",
                default => "❌ «{$option}» не показує неможливість, потрібне сильне заперечення. Приклад: *{$baseExample}*.",
            },
            'possibility_progressive', 'state_possibility', 'general_possibility' => match ($modalType) {
                'must' => "❌ «{$option}» надто категоричне; ми робимо лише припущення. Приклад: *{$baseExample}*.",
                "can't" => "❌ «{$option}» заперечує дію, хоча ми не впевнені. Приклад: *{$baseExample}*.",
                'might not', 'may not' => "❌ «{$option}» заперечує можливість, а контекст говорить про ймовірність. Приклад: *{$baseExample}*.",
                'can' => "❌ «{$option}» описує загальну здатність, а нам потрібна модальна ймовірність. Приклад: *{$baseExample}*.",
                default => "❌ Форма «{$option}» не передає потрібного ступеня ймовірності. Приклад: *{$baseExample}*.",
            },
            'uncertain_negative' => match ($modalType) {
                'must' => "❌ «{$option}» занадто впевнене; нам потрібне м'яке заперечення. Приклад: *{$baseExample}*.",
                'might' => "❌ «{$option}» без not не передає сумнів у негативному результаті. Приклад: *{$baseExample}*.",
                "can't" => "❌ «{$option}» звучить як абсолютна неможливість, а не обережний сумнів. Приклад: *{$baseExample}*.",
                default => "❌ «{$option}» не виражає обережного заперечення. Приклад: *{$baseExample}*.",
            },
            default => "❌ Варіант «{$option}» не відповідає контексту. Приклад: *{$baseExample}*.",
        };
    }

    private function classifyModal(string $value): string
    {
        $normalized = strtolower(str_replace('’', "'", trim($value)));
        $normalized = preg_replace('/\s+/', ' ', $normalized ?? '');

        return match (true) {
            str_starts_with($normalized, "can't") || str_starts_with($normalized, 'cannot') => "can't",
            str_starts_with($normalized, 'must not') || str_starts_with($normalized, "mustn't") => 'must not',
            str_starts_with($normalized, 'must') => 'must',
            str_starts_with($normalized, 'might not') => 'might not',
            str_starts_with($normalized, 'may not') => 'may not',
            str_starts_with($normalized, 'might') => 'might',
            str_starts_with($normalized, 'may') => 'may',
            str_starts_with($normalized, "couldn't") || str_starts_with($normalized, 'could not') => 'could not',
            str_starts_with($normalized, 'could') => 'could',
            str_starts_with($normalized, 'should') => 'should',
            str_starts_with($normalized, 'can') => 'can',
            default => 'other',
        };
    }

    private function formatHintBlocks(array $hints): ?string
    {
        if (empty($hints)) {
            return null;
        }

        $parts = [];
        foreach ($hints as $marker => $text) {
            $clean = trim((string) $text);

            if ($clean === '') {
                continue;
            }

            $parts[] = '{' . $marker . '} ' . $clean;
        }

        if (empty($parts)) {
            return null;
        }

        return implode("\n", $parts);
    }

    private function formatVerbHint(?string $hint): ?string
    {
        if ($hint === null) {
            return null;
        }

        return trim($hint);
    }
}