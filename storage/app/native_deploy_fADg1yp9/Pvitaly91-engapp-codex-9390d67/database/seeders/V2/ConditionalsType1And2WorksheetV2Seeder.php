<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class ConditionalsType1And2WorksheetV2Seeder extends QuestionSeeder
{
    private array $levelDifficulty = [
        'A1' => 1,
        'A2' => 2,
        'B1' => 3,
        'B2' => 4,
        'C1' => 5,
        'C2' => 5,
    ];

    private array $hintTemplates = [
        'if_present_simple' => "Час: First Conditional.\nФормула: **if + Present Simple**.\nПояснення: Умовна частина типу 1 описує реальну можливість, тому використовуємо Present Simple (наприклад, форма від «%verb%» для %subject%).\nПриклад: *%example%*",
        'if_past_simple' => "Час: Second Conditional.\nФормула: **if + Past Simple**.\nПояснення: Уявну ситуацію у другому умовному передаємо Past Simple від дієслова «%verb%».\nПриклад: *%example%*",
        'result_will_simple' => "Час: First Conditional.\nФормула: **will + V1** (наприклад, «will %verb%»).\nПояснення: Результат у типі 1 виражаємо через will + базове дієслово.\nПриклад: *%example%*",
        'result_will_negative' => "Час: First Conditional.\nФормула: **will not / won't + V1**.\nПояснення: Для заперечення в головному реченні типу 1 вживаємо won't + базове дієслово «%verb%».\nПриклад: *%example%*",
        'result_would_simple' => "Час: Second Conditional.\nФормула: **would + V1** (наприклад, «would %verb%»).\nПояснення: У результаті другого умовного використовуємо would + базове дієслово.\nПриклад: *%example%*",
        'result_would_negative' => "Час: Second Conditional.\nФормула: **would not / wouldn't + V1**.\nПояснення: Заперечення у другому умовному будуємо через wouldn't + базове дієслово «%verb%».\nПриклад: *%example%*",
        'result_modal_must_not' => "Час: First Conditional з модальним дієсловом must.\nФормула: **must not + V1** (наприклад, «must not %verb%»).\nПояснення: Заборону в результаті типу 1 передаємо через must not + базове дієслово.\nПриклад: *%example%*",
    ];

    private array $explanationTemplates = [
        'if_present_simple' => [
            'correct' => "✅ «%option%» — правильна форма Present Simple у підрядному реченні для %subject%.\nПриклад: *%example%*",
            'future' => "❌ «%option%» містить will, але в if-клауза типу 1 ми не використовуємо will.\nПриклад: *%example%*",
            'past' => "❌ «%option%» — Past Simple; для реальної умови потрібен Present Simple.\nПриклад: *%example%*",
            'would' => "❌ «%option%» належить до другого умовного з would, а не до першого.\nПриклад: *%example%*",
            'continuous' => "❌ «%option%» — тривала форма, але if-клауза типу 1 потребує Present Simple.\nПриклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
        'if_past_simple' => [
            'correct' => "✅ «%option%» — правильна форма Past Simple у підрядному реченні другого умовного для %subject%.\nПриклад: *%example%*",
            'present' => "❌ «%option%» — Present Simple, але друга умова потребує Past Simple.\nПриклад: *%example%*",
            'future' => "❌ «%option%» містить will, що притаманно першому умовному. Тут потрібен Past Simple.\nПриклад: *%example%*",
            'perfect' => "❌ «%option%» — форма Third Conditional (had + V3). У другому умовному вона не використовується.\nПриклад: *%example%*",
            'would' => "❌ «%option%» — результат з would, а не частина з if.\nПриклад: *%example%*",
            'was' => "❌ «%option%» — форма was; у другому умовному для будь-якого підмета вживаємо were.\nПриклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
        'result_will_simple' => [
            'correct' => "✅ «%option%» — правильна модель will + V1 для результату першого умовного.\nПриклад: *%example%*",
            'would' => "❌ «%option%» використовує would, а це друга умова. У типі 1 потрібен will.\nПриклад: *%example%*",
            'present' => "❌ «%option%» — Present Simple; результат має містити will.\nПриклад: *%example%*",
            'past' => "❌ «%option%» — Past Simple, а нам потрібен will + базове дієслово.\nПриклад: *%example%*",
            'continuous' => "❌ «%option%» — тривала форма; завдання перевіряє просту конструкцію will + V1.\nПриклад: *%example%*",
            'extra_will' => "❌ «%option%» повторює will, хоча допоміжне слово вже є у реченні. Дивись приклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
        'result_will_negative' => [
            'correct' => "✅ «%option%» — правильне заперечення won't + V1 для результату типу 1.\nПриклад: *%example%*",
            'would' => "❌ «%option%» має would, що властиво другому умовному. Тут потрібно won't.\nПриклад: *%example%*",
            'present' => "❌ «%option%» — Present Simple без will; у першому умовному результат формуємо через won't.\nПриклад: *%example%*",
            'past' => "❌ «%option%» — Past Simple; правильне заперечення — won't + V1.\nПриклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
        'result_would_simple' => [
            'correct' => "✅ «%option%» — правильна конструкція would + V1 для результату другого умовного.\nПриклад: *%example%*",
            'will' => "❌ «%option%» з will належить до першого умовного, а тут потрібен would.\nПриклад: *%example%*",
            'present' => "❌ «%option%» — Present Simple; у другому умовному результат описуємо через would.\nПриклад: *%example%*",
            'past' => "❌ «%option%» — Past Simple; потрібно would + V1.\nПриклад: *%example%*",
            'continuous' => "❌ «%option%» — тривала форма; ми тренуємо просту модель would + V1.\nПриклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
        'result_would_negative' => [
            'correct' => "✅ «%option%» — правильне заперечення wouldn't + V1 у другому умовному.\nПриклад: *%example%*",
            'will' => "❌ «%option%» з won't відповідає першому умовному, а в другому треба wouldn't.\nПриклад: *%example%*",
            'present' => "❌ «%option%» — Present Simple; уявний результат вимагає wouldn't + V1.\nПриклад: *%example%*",
            'past' => "❌ «%option%» — Past Simple; у другому умовному потрібна форма wouldn't + V1.\nПриклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
        'result_modal_must_not' => [
            'correct' => "✅ «%option%» — правильна модальна форма must not + V1 для результату першого умовного.\nПриклад: *%example%*",
            'will' => "❌ «%option%» містить will, але тут потрібна заборона must not.\nПриклад: *%example%*",
            'present' => "❌ «%option%» — Present Simple без модального дієслова must.\nПриклад: *%example%*",
            'would' => "❌ «%option%» належить до другого умовного з would. Тут вживаємо must not.\nПриклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Conditionals'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Worksheet: Conditionals Type 1 & 2 (V2)'])->id;

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Mixed Conditionals Practice (Type 1 & 2)'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Conditional Sentence Gap Fill'],
            ['category' => 'English Grammar Detail']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'Conditional Sentences (Type 1 & 2)'],
            ['category' => 'English Grammar Structure']
        )->id;

        $tenseTags = [
            'First Conditional' => Tag::firstOrCreate(['name' => 'First Conditional'], ['category' => 'English Grammar Tense'])->id,
            'Second Conditional' => Tag::firstOrCreate(['name' => 'Second Conditional'], ['category' => 'English Grammar Tense'])->id,
        ];

        $entries = $this->questionEntries();

        $items = [];
        $meta = [];

        foreach ($entries as $index => $entry) {
            $answersMap = [];
            $verbHints = [];
            $optionSets = [];
            $optionMarkerMap = [];
            $optionReasons = [];

            foreach ($entry['markers'] as $marker => $markerData) {
                $answersMap[$marker] = $this->normalizeValue($markerData['answer']);
                $verbHints[$marker] = $markerData['verb_hint'] ?? ($markerData['verb'] ?? null);

                $options = [];
                foreach ($markerData['options'] as $option) {
                    $value = $this->normalizeValue($option['value']);
                    $options[] = $value;
                    $optionMarkerMap[$value] = $marker;
                    $optionReasons[$marker][$value] = $option['reason'] ?? 'default';
                }

                $optionSets[$marker] = array_values(array_unique($options));
            }

            $example = $this->formatExample($entry['question'], $answersMap);

            $explanations = [];
            foreach ($optionMarkerMap as $value => $marker) {
                $context = $entry['markers'][$marker] ?? [];
                $reason = $optionReasons[$marker][$value] ?? 'default';
                $explanations[$value] = $this->buildExplanation(
                    $context['type'] ?? '',
                    $reason,
                    $value,
                    $context,
                    $example
                );
            }

            $hints = [];
            foreach ($entry['markers'] as $marker => $markerData) {
                $hints[$marker] = $this->buildHint($markerData['type'], $markerData, $example);
            }

            $answers = [];
            foreach ($answersMap as $marker => $answer) {
                $answers[] = [
                    'marker' => $marker,
                    'answer' => $answer,
                    'verb_hint' => $this->normalizeHint($verbHints[$marker] ?? null),
                ];
            }

            $flatOptions = [];
            foreach ($optionSets as $options) {
                foreach ($options as $value) {
                    $flatOptions[] = $value;
                }
            }
            $flatOptions = array_values(array_unique($flatOptions));

            $tagIds = [$themeTagId, $detailTagId, $structureTagId];
            foreach ($entry['tenses'] as $tense) {
                if (isset($tenseTags[$tense])) {
                    $tagIds[] = $tenseTags[$tense];
                }
            }

            $uuid = $this->generateQuestionUuid($index + 1, $entry['question']);

            $items[] = [
                'uuid' => $uuid,
                'question' => $entry['question'],
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$entry['level']] ?? 3,
                'source_id' => $sourceId,
                'flag' => 0,
                'level' => $entry['level'],
                'tag_ids' => array_values(array_unique($tagIds)),
                'answers' => $answers,
                'options' => $flatOptions,
                'variants' => [$entry['question']],
            ];

            $meta[] = [
                'uuid' => $uuid,
                'answers' => $answersMap,
                'option_markers' => $optionMarkerMap,
                'hints' => $hints,
                'explanations' => $explanations,
            ];
        }

        $this->seedQuestionData($items, $meta);
    }

    private function questionEntries(): array
    {
        return [
            [
                'level' => 'B1',
                'tenses' => ['Second Conditional'],
                'question' => 'If John gave me the money, I {a1} it on clothes.',
                'markers' => [
                    'a1' => [
                        'type' => 'result_would_simple',
                        'subject' => 'I',
                        'verb' => 'spend',
                        'verb_hint' => 'spend',
                        'answer' => 'would spend',
                        'options' => [
                            ['value' => 'would spend', 'reason' => 'correct'],
                            ['value' => 'will spend', 'reason' => 'will'],
                            ['value' => 'spend', 'reason' => 'present'],
                            ['value' => 'spent', 'reason' => 'past'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'tenses' => ['First Conditional'],
                'question' => 'Cathy will be upset if she {a1} Michael at the party.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_present_simple',
                        'subject' => 'she',
                        'verb' => 'see',
                        'verb_hint' => 'see',
                        'answer' => 'sees',
                        'options' => [
                            ['value' => 'sees', 'reason' => 'correct'],
                            ['value' => 'will see', 'reason' => 'future'],
                            ['value' => 'saw', 'reason' => 'past'],
                            ['value' => 'would see', 'reason' => 'would'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tenses' => ['Second Conditional'],
                'question' => 'Rosemary would buy the house if she {a1} the money.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_simple',
                        'subject' => 'she',
                        'verb' => 'have',
                        'verb_hint' => 'have',
                        'answer' => 'had',
                        'options' => [
                            ['value' => 'had', 'reason' => 'correct'],
                            ['value' => 'has', 'reason' => 'present'],
                            ['value' => 'would have', 'reason' => 'would'],
                            ['value' => 'have had', 'reason' => 'perfect'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'tenses' => ['First Conditional'],
                'question' => 'I won\'t call him if I {a1} to.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_present_simple',
                        'subject' => 'I',
                        'verb' => 'not want',
                        'verb_hint' => 'not want',
                        'answer' => "don't want",
                        'options' => [
                            ['value' => "don't want", 'reason' => 'correct'],
                            ['value' => "won't want", 'reason' => 'future'],
                            ['value' => "didn't want", 'reason' => 'past'],
                            ['value' => "wouldn't want", 'reason' => 'would'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'tenses' => ['First Conditional'],
                'question' => 'If you have the treasure map, you {a1} it to us.',
                'markers' => [
                    'a1' => [
                        'type' => 'result_modal_must_not',
                        'subject' => 'you',
                        'verb' => 'show',
                        'verb_hint' => 'not show',
                        'answer' => 'must not show',
                        'options' => [
                            ['value' => 'must not show', 'reason' => 'correct'],
                            ['value' => 'will not show', 'reason' => 'will'],
                            ['value' => 'do not show', 'reason' => 'present'],
                            ['value' => 'would not show', 'reason' => 'would'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tenses' => ['Second Conditional'],
                'question' => 'If you {a1} the lottery, what would you buy?',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_simple',
                        'subject' => 'you',
                        'verb' => 'win',
                        'verb_hint' => 'win',
                        'answer' => 'won',
                        'options' => [
                            ['value' => 'won', 'reason' => 'correct'],
                            ['value' => 'win', 'reason' => 'present'],
                            ['value' => 'will win', 'reason' => 'future'],
                            ['value' => 'would win', 'reason' => 'would'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tenses' => ['Second Conditional'],
                'question' => 'If I {a1} you, I {a2} the same in your place.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_simple',
                        'subject' => 'I',
                        'verb' => 'be',
                        'verb_hint' => 'be',
                        'answer' => 'were',
                        'options' => [
                            ['value' => 'were', 'reason' => 'correct'],
                            ['value' => 'was', 'reason' => 'was'],
                            ['value' => 'am', 'reason' => 'present'],
                            ['value' => 'would be', 'reason' => 'would'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_would_simple',
                        'subject' => 'I',
                        'verb' => 'do',
                        'verb_hint' => 'do',
                        'answer' => 'would do',
                        'options' => [
                            ['value' => 'would do', 'reason' => 'correct'],
                            ['value' => 'will do', 'reason' => 'will'],
                            ['value' => 'did', 'reason' => 'past'],
                            ['value' => 'do', 'reason' => 'present'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'tenses' => ['Second Conditional'],
                'question' => 'If I {a1} to the cinema, I would be extremely sad.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_simple',
                        'subject' => 'I',
                        'verb' => 'not go',
                        'verb_hint' => 'not go',
                        'answer' => "didn't go",
                        'options' => [
                            ['value' => "didn't go", 'reason' => 'correct'],
                            ['value' => "don't go", 'reason' => 'present'],
                            ['value' => "won't go", 'reason' => 'future'],
                            ['value' => "hadn't gone", 'reason' => 'perfect'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tenses' => ['Second Conditional'],
                'question' => 'If I {a1} too tired, I {a2} her to go to the cinema with me.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_simple',
                        'subject' => 'I',
                        'verb' => 'be',
                        'verb_hint' => 'be',
                        'answer' => 'were',
                        'options' => [
                            ['value' => 'were', 'reason' => 'correct'],
                            ['value' => 'was', 'reason' => 'was'],
                            ['value' => 'am', 'reason' => 'present'],
                            ['value' => 'would be', 'reason' => 'would'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_would_simple',
                        'subject' => 'I',
                        'verb' => 'ask',
                        'verb_hint' => 'ask',
                        'answer' => 'would ask',
                        'options' => [
                            ['value' => 'would ask', 'reason' => 'correct'],
                            ['value' => 'will ask', 'reason' => 'will'],
                            ['value' => 'ask', 'reason' => 'present'],
                            ['value' => 'asked', 'reason' => 'past'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'tenses' => ['First Conditional'],
                'question' => 'If my train isn\'t late, I {a1} in school on time.',
                'markers' => [
                    'a1' => [
                        'type' => 'result_will_simple',
                        'subject' => 'I',
                        'verb' => 'be',
                        'verb_hint' => 'be',
                        'answer' => 'will be',
                        'options' => [
                            ['value' => 'will be', 'reason' => 'correct'],
                            ['value' => 'would be', 'reason' => 'would'],
                            ['value' => 'am', 'reason' => 'present'],
                            ['value' => 'was', 'reason' => 'past'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'tenses' => ['Second Conditional'],
                'question' => 'You {a1} so unfit if you {a2} more exercise.',
                'markers' => [
                    'a1' => [
                        'type' => 'result_would_negative',
                        'subject' => 'you',
                        'verb' => 'not be',
                        'verb_hint' => 'not be',
                        'answer' => "wouldn't be",
                        'options' => [
                            ['value' => "wouldn't be", 'reason' => 'correct'],
                            ['value' => "won't be", 'reason' => 'will'],
                            ['value' => "aren't", 'reason' => 'present'],
                            ['value' => "weren't", 'reason' => 'past'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'if_past_simple',
                        'subject' => 'you',
                        'verb' => 'take',
                        'verb_hint' => 'take',
                        'answer' => 'took',
                        'options' => [
                            ['value' => 'took', 'reason' => 'correct'],
                            ['value' => 'take', 'reason' => 'present'],
                            ['value' => 'will take', 'reason' => 'future'],
                            ['value' => 'taken', 'reason' => 'perfect'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tenses' => ['First Conditional'],
                'question' => 'If he doesn\'t work harder, he {a1} his job.',
                'markers' => [
                    'a1' => [
                        'type' => 'result_will_negative',
                        'subject' => 'he',
                        'verb' => 'keep',
                        'verb_hint' => 'not keep',
                        'answer' => "won't keep",
                        'options' => [
                            ['value' => "won't keep", 'reason' => 'correct'],
                            ['value' => "wouldn't keep", 'reason' => 'would'],
                            ['value' => 'keeps', 'reason' => 'present'],
                            ['value' => 'kept', 'reason' => 'past'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tenses' => ['First Conditional'],
                'question' => 'If he doesn\'t pass the exam, will you {a1} him?',
                'markers' => [
                    'a1' => [
                        'type' => 'result_will_simple',
                        'subject' => 'you',
                        'verb' => 'help',
                        'verb_hint' => 'help',
                        'answer' => 'help',
                        'options' => [
                            ['value' => 'help', 'reason' => 'correct'],
                            ['value' => 'will help', 'reason' => 'extra_will'],
                            ['value' => 'helped', 'reason' => 'past'],
                            ['value' => 'would help', 'reason' => 'would'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'tenses' => ['Second Conditional'],
                'question' => 'If he {a1} his scholarship, his friends would visit him more often.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_simple',
                        'subject' => 'he',
                        'verb' => 'not lose',
                        'verb_hint' => 'not lose',
                        'answer' => "didn't lose",
                        'options' => [
                            ['value' => "didn't lose", 'reason' => 'correct'],
                            ['value' => "doesn't lose", 'reason' => 'present'],
                            ['value' => "wouldn't lose", 'reason' => 'would'],
                            ['value' => 'had not lost', 'reason' => 'perfect'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tenses' => ['Second Conditional'],
                'question' => 'If he {a1} so far, his friends would visit him every weekend.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_simple',
                        'subject' => 'he',
                        'verb' => 'not live',
                        'verb_hint' => 'not live',
                        'answer' => "didn't live",
                        'options' => [
                            ['value' => "didn't live", 'reason' => 'correct'],
                            ['value' => "doesn't live", 'reason' => 'present'],
                            ['value' => 'lived', 'reason' => 'past'],
                            ['value' => "wouldn't live", 'reason' => 'would'],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function buildHint(string $type, array $context, string $example): string
    {
        $template = $this->hintTemplates[$type] ?? "Час: Conditionals.\nПояснення: Оберіть форму, що відповідає правилу.\nПриклад: *%example%*";

        return $this->renderTemplate($template, [
            'subject' => $context['subject'] ?? '',
            'verb' => $context['verb'] ?? '',
            'example' => $example,
        ]);
    }

    private function buildExplanation(string $type, string $reason, string $option, array $context, string $example): string
    {
        $templates = $this->explanationTemplates[$type] ?? [];
        $template = $templates[$reason] ?? ($templates['default'] ?? "❌ Неправильний варіант.\nПриклад: *%example%*");

        return $this->renderTemplate($template, [
            'option' => $option,
            'subject' => $context['subject'] ?? '',
            'verb' => $context['verb'] ?? '',
            'example' => $example,
        ]);
    }

    private function renderTemplate(string $template, array $context): string
    {
        $replacements = [
            '%option%' => $context['option'] ?? '',
            '%verb%' => $context['verb'] ?? '',
            '%example%' => $context['example'] ?? '',
            '%subject%' => $this->subjectPhrase($context['subject'] ?? ''),
        ];

        return trim(strtr($template, $replacements));
    }

    private function subjectPhrase(?string $subject): string
    {
        $subject = trim((string) $subject);

        if ($subject === '') {
            return 'цього підмета';
        }

        return match (mb_strtolower($subject, 'UTF-8')) {
            'i' => 'займенника «I»',
            'you' => 'займенника «you»',
            'he' => 'займенника «he»',
            'she' => 'займенника «she»',
            'it' => 'займенника «it»',
            'we' => 'займенника «we»',
            'they' => 'займенника «they»',
            default => 'підмета «' . $subject . '»',
        };
    }

    private function normalizeValue(string $value): string
    {
        $value = str_replace(['’', '‘', '‛', 'ʻ'], "'", $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }
}
