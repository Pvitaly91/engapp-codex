<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class IfClausesType012WorksheetV2Seeder extends QuestionSeeder
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
        'if_zero_present' => "Час: Zero Conditional.\nФормула: **if + Present Simple** (наприклад, «if + %verb%»).\nПояснення: Для загальних фактів в обох частинах вживаємо Present Simple.\nПриклад: *%example%*",
        'if_zero_negative' => "Час: Zero Conditional.\nФормула: **if + do/does not + V1** (наприклад, «if ... do not %verb%»).\nПояснення: Заперечення у нульовому умовному виражаємо через do/does not + дієслово.\nПриклад: *%example%*",
        'result_zero_present' => "Час: Zero Conditional.\nФормула: **Present Simple** (наприклад, «%subject% %verb%»).\nПояснення: Результат загального правила також стоїть у Present Simple.\nПриклад: *%example%*",
        'result_zero_negative' => "Час: Zero Conditional.\nФормула: **do/does not + V1** (наприклад, «do not %verb%»).\nПояснення: Заперечення у нульовому умовному будуємо через do/does not.\nПриклад: *%example%*",
        'if_first_present' => "Час: First Conditional.\nФормула: **if + Present Simple** (наприклад, «if + %verb%»).\nПояснення: Умовну частину про реальне майбутнє пишемо в Present Simple.\nПриклад: *%example%*",
        'if_first_negative' => "Час: First Conditional.\nФормула: **if + do/does not + V1**.\nПояснення: Для заперечення у першому умовному використовуємо do/does not + дієслово.\nПриклад: *%example%*",
        'result_first_will' => "Час: First Conditional.\nФормула: **will + V1** (наприклад, «will %verb%»).\nПояснення: Результат реальної умови виражаємо через will + базове дієслово.\nПриклад: *%example%*",
        'result_first_negative' => "Час: First Conditional.\nФормула: **will not / won't + V1**.\nПояснення: Для заперечення в результаті першого умовного використовуємо will not + V1.\nПриклад: *%example%*",
        'if_second_past' => "Час: Second Conditional.\nФормула: **if + Past Simple**.\nПояснення: Уявні ситуації в умовній частині виражаємо Past Simple.\nПриклад: *%example%*",
        'result_second_would' => "Час: Second Conditional.\nФормула: **would + V1**.\nПояснення: Результат уявної ситуації будуємо через would + базове дієслово.\nПриклад: *%example%*",
        'result_second_negative' => "Час: Second Conditional.\nФормула: **would not / wouldn't + V1**.\nПояснення: Заперечення у результаті другого умовного створюємо через wouldn't + V1.\nПриклад: *%example%*",
    ];

    private array $explanationTemplates = [
        'if_zero_present' => [
            'correct' => "✅ «%option%» — Present Simple у нульовому умовному для %subject%.\nПриклад: *%example%*",
            'future' => "❌ «%option%» містить will, але Zero Conditional використовує Present Simple.\nПриклад: *%example%*",
            'would' => "❌ «%option%» належить до другого умовного з would.\nПриклад: *%example%*",
            'past' => "❌ «%option%» — Past Simple; для загальної істини потрібен Present Simple.\nПриклад: *%example%*",
            'continuous' => "❌ «%option%» — тривала форма; у Zero Conditional потрібна проста форма.\nПриклад: *%example%*",
            'present' => "❌ «%option%» не має потрібного закінчення для %subject%.\nПриклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
        'if_zero_negative' => [
            'correct' => "✅ «%option%» — заперечення do/does not + V1 у нульовому умовному.\nПриклад: *%example%*",
            'future' => "❌ «%option%» має will, але Zero Conditional не використовує will.\nПриклад: *%example%*",
            'would' => "❌ «%option%» належить до другого умовного.\nПриклад: *%example%*",
            'present' => "❌ «%option%» — ствердна форма; потрібно заперечення.\nПриклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
        'result_zero_present' => [
            'correct' => "✅ «%option%» — Present Simple показує результат загального правила.\nПриклад: *%example%*",
            'future' => "❌ «%option%» містить will, але у нульовому умовному результат теж у Present Simple.\nПриклад: *%example%*",
            'would' => "❌ «%option%» належить до Second Conditional; тут потрібен Present Simple.\nПриклад: *%example%*",
            'past' => "❌ «%option%» — Past Simple; лишаємо Present Simple.\nПриклад: *%example%*",
            'present' => "❌ «%option%» не узгоджується з %subject% у Present Simple.\nПриклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
        'result_zero_negative' => [
            'correct' => "✅ «%option%» — правильне заперечення do/does not + V1 у Zero Conditional.\nПриклад: *%example%*",
            'future' => "❌ «%option%» має will, але нульовий умовний не використовує will.\nПриклад: *%example%*",
            'would' => "❌ «%option%» належить до другого умовного.\nПриклад: *%example%*",
            'present' => "❌ «%option%» — ствердна форма; потрібне заперечення.\nПриклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
        'if_first_present' => [
            'correct' => "✅ «%option%» — Present Simple у підрядній частині першого умовного.\nПриклад: *%example%*",
            'future' => "❌ «%option%» містить will, але в if-клауза типу 1 використовуємо Present Simple.\nПриклад: *%example%*",
            'past' => "❌ «%option%» — Past Simple; для реальної умови потрібен Present Simple.\nПриклад: *%example%*",
            'would' => "❌ «%option%» належить до другого умовного.\nПриклад: *%example%*",
            'present' => "❌ «%option%» не узгоджується з %subject% у Present Simple.\nПриклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
        'if_first_negative' => [
            'correct' => "✅ «%option%» — заперечення do/does not + V1 у першому умовному.\nПриклад: *%example%*",
            'future' => "❌ «%option%» містить will; у частині if його не ставимо.\nПриклад: *%example%*",
            'would' => "❌ «%option%» належить до другого умовного.\nПриклад: *%example%*",
            'past' => "❌ «%option%» — Past Simple; ми говоримо про реальну умову.\nПриклад: *%example%*",
            'present' => "❌ «%option%» — ствердна форма; потрібне заперечення.\nПриклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
        'result_first_will' => [
            'correct' => "✅ «%option%» — правильна форма will + V1 у першому умовному.\nПриклад: *%example%*",
            'present' => "❌ «%option%» — Present Simple; потрібне will + V1.\nПриклад: *%example%*",
            'past' => "❌ «%option%» — Past Simple; результат має містити will.\nПриклад: *%example%*",
            'would' => "❌ «%option%» належить до другого умовного.\nПриклад: *%example%*",
            'continuous' => "❌ «%option%» — тривала форма; вправа тренує will + V1.\nПриклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
        'result_first_negative' => [
            'correct' => "✅ «%option%» — правильне заперечення will not + V1 у першому умовному.\nПриклад: *%example%*",
            'future_positive' => "❌ «%option%» — ствердження; завдання потребує заперечення з will not.\nПриклад: *%example%*",
            'past' => "❌ «%option%» — Past Simple; потрібен will not + V1.\nПриклад: *%example%*",
            'would' => "❌ «%option%» належить до другого умовного.\nПриклад: *%example%*",
            'present' => "❌ «%option%» — Present Simple; результат першого умовного має містити will.\nПриклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
        'if_second_past' => [
            'correct' => "✅ «%option%» — Past Simple у підрядній частині другого умовного.\nПриклад: *%example%*",
            'present' => "❌ «%option%» — Present Simple; у другому умовному потрібен Past Simple.\nПриклад: *%example%*",
            'future' => "❌ «%option%» містить will; друга умова використовує Past Simple.\nПриклад: *%example%*",
            'perfect' => "❌ «%option%» — Past Perfect; це вже третій умовний.\nПриклад: *%example%*",
            'would' => "❌ «%option%» належить до результату з would.\nПриклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
        'result_second_would' => [
            'correct' => "✅ «%option%» — правильна модель would + V1 для результату другого умовного.\nПриклад: *%example%*",
            'will' => "❌ «%option%» з will утворює перший умовний.\nПриклад: *%example%*",
            'present' => "❌ «%option%» — Present Simple; для уявних результатів потрібне would + V1.\nПриклад: *%example%*",
            'past' => "❌ «%option%» — Past Simple; потрібен would + V1.\nПриклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
        'result_second_negative' => [
            'correct' => "✅ «%option%» — правильне заперечення wouldn't + V1 у другому умовному.\nПриклад: *%example%*",
            'will' => "❌ «%option%» з won't — перший умовний.\nПриклад: *%example%*",
            'present' => "❌ «%option%» — Present Simple; потрібно wouldn't + V1.\nПриклад: *%example%*",
            'past' => "❌ «%option%» — Past Simple; потрібна форма wouldn't + V1.\nПриклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'If Clauses Type 0-1-2 Worksheet V2'])->id;

        $sourceMap = [
            'zero' => Source::firstOrCreate(['name' => 'Worksheet Image: If Clauses Type 0-1-2 - Zero Conditional'])->id,
            'first' => Source::firstOrCreate(['name' => 'Worksheet Image: If Clauses Type 0-1-2 - First Conditional'])->id,
            'second' => Source::firstOrCreate(['name' => 'Worksheet Image: If Clauses Type 0-1-2 - Second Conditional'])->id,
            'mixed' => Source::firstOrCreate(['name' => 'Worksheet Image: If Clauses Type 0-1-2 - Mixed Practice'])->id,
        ];

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Conditionals Type 0-1-2 Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'If Clauses Worksheet'],
            ['category' => 'English Grammar Detail']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'Conditional Sentences (Zero to Second)'],
            ['category' => 'English Grammar Structure']
        )->id;

        $tenseTags = [
            'Zero Conditional' => Tag::firstOrCreate(['name' => 'Zero Conditional'], ['category' => 'English Grammar Tense'])->id,
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
            $sourceId = $sourceMap[$entry['source']] ?? reset($sourceMap);

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
                'source' => 'zero',
                'level' => 'A2',
                'tenses' => ['Zero Conditional'],
                'question' => 'If the sun {a1} shining, the grass {a2} quickly.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_zero_present',
                        'subject' => 'the sun',
                        'verb' => 'be',
                        'verb_hint' => 'be',
                        'answer' => 'is',
                        'options' => [
                            ['value' => 'is', 'reason' => 'correct'],
                            ['value' => 'was', 'reason' => 'past'],
                            ['value' => 'will be', 'reason' => 'future'],
                            ['value' => 'would be', 'reason' => 'would'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_zero_present',
                        'subject' => 'the grass',
                        'verb' => 'grow',
                        'verb_hint' => 'grow',
                        'answer' => 'grows',
                        'options' => [
                            ['value' => 'grows', 'reason' => 'correct'],
                            ['value' => 'grow', 'reason' => 'present'],
                            ['value' => 'will grow', 'reason' => 'future'],
                            ['value' => 'grew', 'reason' => 'past'],
                        ],
                    ],
                ],
            ],
            [
                'source' => 'zero',
                'level' => 'A2',
                'tenses' => ['Zero Conditional'],
                'question' => 'If you {a1} ice, it {a2}.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_zero_present',
                        'subject' => 'you',
                        'verb' => 'heat',
                        'verb_hint' => 'heat',
                        'answer' => 'heat',
                        'options' => [
                            ['value' => 'heat', 'reason' => 'correct'],
                            ['value' => 'heated', 'reason' => 'past'],
                            ['value' => 'will heat', 'reason' => 'future'],
                            ['value' => 'would heat', 'reason' => 'would'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_zero_present',
                        'subject' => 'it',
                        'verb' => 'melt',
                        'verb_hint' => 'melt',
                        'answer' => 'melts',
                        'options' => [
                            ['value' => 'melts', 'reason' => 'correct'],
                            ['value' => 'melt', 'reason' => 'present'],
                            ['value' => 'will melt', 'reason' => 'future'],
                            ['value' => 'melted', 'reason' => 'past'],
                        ],
                    ],
                ],
            ],
            [
                'source' => 'zero',
                'level' => 'A2',
                'tenses' => ['Zero Conditional'],
                'question' => 'If I {a1} late, my teacher {a2} angry.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_zero_present',
                        'subject' => 'I',
                        'verb' => 'be',
                        'verb_hint' => 'be',
                        'answer' => 'am',
                        'options' => [
                            ['value' => 'am', 'reason' => 'correct'],
                            ['value' => 'was', 'reason' => 'past'],
                            ['value' => 'will be', 'reason' => 'future'],
                            ['value' => 'would be', 'reason' => 'would'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_zero_present',
                        'subject' => 'my teacher',
                        'verb' => 'get',
                        'verb_hint' => 'get',
                        'answer' => 'gets',
                        'options' => [
                            ['value' => 'gets', 'reason' => 'correct'],
                            ['value' => 'get', 'reason' => 'present'],
                            ['value' => 'will get', 'reason' => 'future'],
                            ['value' => 'got', 'reason' => 'past'],
                        ],
                    ],
                ],
            ],
            [
                'source' => 'zero',
                'level' => 'A2',
                'tenses' => ['Zero Conditional'],
                'question' => 'If my sister {a1} coffee, she {a2}.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_zero_present',
                        'subject' => 'my sister',
                        'verb' => 'drink',
                        'verb_hint' => 'drink',
                        'answer' => 'drinks',
                        'options' => [
                            ['value' => 'drinks', 'reason' => 'correct'],
                            ['value' => 'drink', 'reason' => 'present'],
                            ['value' => 'will drink', 'reason' => 'future'],
                            ['value' => 'drank', 'reason' => 'past'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_zero_negative',
                        'subject' => 'she',
                        'verb' => 'sleep',
                        'verb_hint' => 'not sleep',
                        'answer' => 'does not sleep',
                        'options' => [
                            ['value' => 'does not sleep', 'reason' => 'correct'],
                            ['value' => "won't sleep", 'reason' => 'future'],
                            ['value' => "wouldn't sleep", 'reason' => 'would'],
                            ['value' => 'sleeps', 'reason' => 'present'],
                        ],
                    ],
                ],
            ],
            [
                'source' => 'zero',
                'level' => 'A2',
                'tenses' => ['Zero Conditional'],
                'question' => 'If I {a1} anything, I {a2} hungry.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_zero_negative',
                        'subject' => 'I',
                        'verb' => 'eat',
                        'verb_hint' => 'not eat',
                        'answer' => 'do not eat',
                        'options' => [
                            ['value' => 'do not eat', 'reason' => 'correct'],
                            ['value' => 'will not eat', 'reason' => 'future'],
                            ['value' => 'would not eat', 'reason' => 'would'],
                            ['value' => 'eat', 'reason' => 'present'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_zero_present',
                        'subject' => 'I',
                        'verb' => 'get',
                        'verb_hint' => 'get',
                        'answer' => 'get',
                        'options' => [
                            ['value' => 'get', 'reason' => 'correct'],
                            ['value' => 'gets', 'reason' => 'present'],
                            ['value' => 'will get', 'reason' => 'future'],
                            ['value' => 'got', 'reason' => 'past'],
                        ],
                    ],
                ],
            ],
            [
                'source' => 'first',
                'level' => 'A2',
                'tenses' => ['First Conditional'],
                'question' => 'If I can, I {a1} for a picnic by the river.',
                'markers' => [
                    'a1' => [
                        'type' => 'result_first_will',
                        'subject' => 'I',
                        'verb' => 'go',
                        'verb_hint' => 'go',
                        'answer' => 'will go',
                        'options' => [
                            ['value' => 'will go', 'reason' => 'correct'],
                            ['value' => 'go', 'reason' => 'present'],
                            ['value' => 'went', 'reason' => 'past'],
                            ['value' => 'would go', 'reason' => 'would'],
                        ],
                    ],
                ],
            ],
            [
                'source' => 'first',
                'level' => 'A2',
                'tenses' => ['First Conditional'],
                'question' => 'If it {a1}, we {a2} Europe next year.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_first_present',
                        'subject' => 'it',
                        'verb' => 'rain',
                        'verb_hint' => 'rain',
                        'answer' => 'rains',
                        'options' => [
                            ['value' => 'rains', 'reason' => 'correct'],
                            ['value' => 'rain', 'reason' => 'present'],
                            ['value' => 'will rain', 'reason' => 'future'],
                            ['value' => 'rained', 'reason' => 'past'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_first_negative',
                        'subject' => 'we',
                        'verb' => 'visit',
                        'verb_hint' => 'not visit',
                        'answer' => 'will not visit',
                        'options' => [
                            ['value' => 'will not visit', 'reason' => 'correct'],
                            ['value' => 'will visit', 'reason' => 'future_positive'],
                            ['value' => 'visited', 'reason' => 'past'],
                            ['value' => 'would not visit', 'reason' => 'would'],
                        ],
                    ],
                ],
            ],
            [
                'source' => 'first',
                'level' => 'A2',
                'tenses' => ['First Conditional'],
                'question' => 'If he {a1} the exam, he {a2} his money.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_first_present',
                        'subject' => 'he',
                        'verb' => 'pass',
                        'verb_hint' => 'pass',
                        'answer' => 'passes',
                        'options' => [
                            ['value' => 'passes', 'reason' => 'correct'],
                            ['value' => 'pass', 'reason' => 'present'],
                            ['value' => 'passed', 'reason' => 'past'],
                            ['value' => 'will pass', 'reason' => 'future'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_first_will',
                        'subject' => 'he',
                        'verb' => 'spend',
                        'verb_hint' => 'spend',
                        'answer' => 'will spend',
                        'options' => [
                            ['value' => 'will spend', 'reason' => 'correct'],
                            ['value' => 'spends', 'reason' => 'present'],
                            ['value' => 'spent', 'reason' => 'past'],
                            ['value' => 'would spend', 'reason' => 'would'],
                        ],
                    ],
                ],
            ],
            [
                'source' => 'first',
                'level' => 'A2',
                'tenses' => ['First Conditional'],
                'question' => 'If she {a1} enough money, she {a2} this club.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_first_present',
                        'subject' => 'she',
                        'verb' => 'have',
                        'verb_hint' => 'have',
                        'answer' => 'has',
                        'options' => [
                            ['value' => 'has', 'reason' => 'correct'],
                            ['value' => 'have', 'reason' => 'present'],
                            ['value' => 'had', 'reason' => 'past'],
                            ['value' => 'will have', 'reason' => 'future'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_first_will',
                        'subject' => 'she',
                        'verb' => 'join',
                        'verb_hint' => 'join',
                        'answer' => 'will join',
                        'options' => [
                            ['value' => 'will join', 'reason' => 'correct'],
                            ['value' => 'joins', 'reason' => 'present'],
                            ['value' => 'joined', 'reason' => 'past'],
                            ['value' => 'would join', 'reason' => 'would'],
                        ],
                    ],
                ],
            ],
            [
                'source' => 'first',
                'level' => 'A2',
                'tenses' => ['First Conditional'],
                'question' => 'If I {a1} enough money, I {a2} my bills.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_first_negative',
                        'subject' => 'I',
                        'verb' => 'earn',
                        'verb_hint' => 'not earn',
                        'answer' => 'do not earn',
                        'options' => [
                            ['value' => 'do not earn', 'reason' => 'correct'],
                            ['value' => 'will not earn', 'reason' => 'future'],
                            ['value' => 'earned', 'reason' => 'past'],
                            ['value' => 'would not earn', 'reason' => 'would'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_first_negative',
                        'subject' => 'I',
                        'verb' => 'pay',
                        'verb_hint' => 'not pay',
                        'answer' => 'will not pay',
                        'options' => [
                            ['value' => 'will not pay', 'reason' => 'correct'],
                            ['value' => 'will pay', 'reason' => 'future_positive'],
                            ['value' => 'paid', 'reason' => 'past'],
                            ['value' => 'would not pay', 'reason' => 'would'],
                        ],
                    ],
                ],
            ],
            [
                'source' => 'second',
                'level' => 'B1',
                'tenses' => ['Second Conditional'],
                'question' => 'If I {a1} a famous person, I {a2} any privacy.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_second_past',
                        'subject' => 'I',
                        'verb' => 'be',
                        'verb_hint' => 'be',
                        'answer' => 'were',
                        'options' => [
                            ['value' => 'were', 'reason' => 'correct'],
                            ['value' => 'was', 'reason' => 'present'],
                            ['value' => 'am', 'reason' => 'present'],
                            ['value' => 'would be', 'reason' => 'would'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_second_negative',
                        'subject' => 'I',
                        'verb' => 'have',
                        'verb_hint' => 'not have',
                        'answer' => 'would not have',
                        'options' => [
                            ['value' => 'would not have', 'reason' => 'correct'],
                            ['value' => 'will not have', 'reason' => 'will'],
                            ['value' => 'do not have', 'reason' => 'present'],
                            ['value' => 'did not have', 'reason' => 'past'],
                        ],
                    ],
                ],
            ],
            [
                'source' => 'second',
                'level' => 'B1',
                'tenses' => ['Second Conditional'],
                'question' => 'If he {a1} her, she {a2} very happy.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_second_past',
                        'subject' => 'he',
                        'verb' => 'marry',
                        'verb_hint' => 'marry',
                        'answer' => 'married',
                        'options' => [
                            ['value' => 'married', 'reason' => 'correct'],
                            ['value' => 'marries', 'reason' => 'present'],
                            ['value' => 'will marry', 'reason' => 'future'],
                            ['value' => 'had married', 'reason' => 'perfect'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_second_would',
                        'subject' => 'she',
                        'verb' => 'be',
                        'verb_hint' => 'be',
                        'answer' => 'would be',
                        'options' => [
                            ['value' => 'would be', 'reason' => 'correct'],
                            ['value' => 'will be', 'reason' => 'will'],
                            ['value' => 'is', 'reason' => 'present'],
                            ['value' => 'was', 'reason' => 'past'],
                        ],
                    ],
                ],
            ],
            [
                'source' => 'second',
                'level' => 'B1',
                'tenses' => ['Second Conditional'],
                'question' => 'If I {a1} Bob now, I {a2} him what I think.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_second_past',
                        'subject' => 'I',
                        'verb' => 'see',
                        'verb_hint' => 'see',
                        'answer' => 'saw',
                        'options' => [
                            ['value' => 'saw', 'reason' => 'correct'],
                            ['value' => 'see', 'reason' => 'present'],
                            ['value' => 'will see', 'reason' => 'future'],
                            ['value' => 'had seen', 'reason' => 'perfect'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_second_would',
                        'subject' => 'I',
                        'verb' => 'tell',
                        'verb_hint' => 'tell',
                        'answer' => 'would tell',
                        'options' => [
                            ['value' => 'would tell', 'reason' => 'correct'],
                            ['value' => 'will tell', 'reason' => 'will'],
                            ['value' => 'tell', 'reason' => 'present'],
                            ['value' => 'told', 'reason' => 'past'],
                        ],
                    ],
                ],
            ],
            [
                'source' => 'second',
                'level' => 'B1',
                'tenses' => ['Second Conditional'],
                'question' => 'If John {a1} in New York, he {a2} a Porsche.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_second_past',
                        'subject' => 'John',
                        'verb' => 'live',
                        'verb_hint' => 'live',
                        'answer' => 'lived',
                        'options' => [
                            ['value' => 'lived', 'reason' => 'correct'],
                            ['value' => 'lives', 'reason' => 'present'],
                            ['value' => 'will live', 'reason' => 'future'],
                            ['value' => 'had lived', 'reason' => 'perfect'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_second_would',
                        'subject' => 'he',
                        'verb' => 'buy',
                        'verb_hint' => 'buy',
                        'answer' => 'would buy',
                        'options' => [
                            ['value' => 'would buy', 'reason' => 'correct'],
                            ['value' => 'will buy', 'reason' => 'will'],
                            ['value' => 'buys', 'reason' => 'present'],
                            ['value' => 'bought', 'reason' => 'past'],
                        ],
                    ],
                ],
            ],
            [
                'source' => 'second',
                'level' => 'B1',
                'tenses' => ['Second Conditional'],
                'question' => 'If we {a1} enough money, we {a2} on a holiday.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_second_past',
                        'subject' => 'we',
                        'verb' => 'have',
                        'verb_hint' => 'have',
                        'answer' => 'had',
                        'options' => [
                            ['value' => 'had', 'reason' => 'correct'],
                            ['value' => 'have', 'reason' => 'present'],
                            ['value' => 'will have', 'reason' => 'future'],
                            ['value' => 'had had', 'reason' => 'perfect'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_second_would',
                        'subject' => 'we',
                        'verb' => 'go',
                        'verb_hint' => 'go',
                        'answer' => 'would go',
                        'options' => [
                            ['value' => 'would go', 'reason' => 'correct'],
                            ['value' => 'will go', 'reason' => 'will'],
                            ['value' => 'go', 'reason' => 'present'],
                            ['value' => 'went', 'reason' => 'past'],
                        ],
                    ],
                ],
            ],
            [
                'source' => 'mixed',
                'level' => 'B1',
                'tenses' => ['First Conditional'],
                'question' => 'If my friends {a1} over, we {a2} a movie.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_first_present',
                        'subject' => 'my friends',
                        'verb' => 'come',
                        'verb_hint' => 'come',
                        'answer' => 'come',
                        'options' => [
                            ['value' => 'come', 'reason' => 'correct'],
                            ['value' => 'comes', 'reason' => 'present'],
                            ['value' => 'came', 'reason' => 'past'],
                            ['value' => 'will come', 'reason' => 'future'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_first_will',
                        'subject' => 'we',
                        'verb' => 'watch',
                        'verb_hint' => 'watch',
                        'answer' => 'will watch',
                        'options' => [
                            ['value' => 'will watch', 'reason' => 'correct'],
                            ['value' => 'watch', 'reason' => 'present'],
                            ['value' => 'watched', 'reason' => 'past'],
                            ['value' => 'would watch', 'reason' => 'would'],
                        ],
                    ],
                ],
            ],
            [
                'source' => 'mixed',
                'level' => 'B1',
                'tenses' => ['First Conditional'],
                'question' => 'If you {a1}, you {a2} the exam.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_first_negative',
                        'subject' => 'you',
                        'verb' => 'study',
                        'verb_hint' => 'not study',
                        'answer' => 'do not study',
                        'options' => [
                            ['value' => 'do not study', 'reason' => 'correct'],
                            ['value' => 'will not study', 'reason' => 'future'],
                            ['value' => 'studied', 'reason' => 'past'],
                            ['value' => 'study', 'reason' => 'present'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_first_will',
                        'subject' => 'you',
                        'verb' => 'fail',
                        'verb_hint' => 'fail',
                        'answer' => 'will fail',
                        'options' => [
                            ['value' => 'will fail', 'reason' => 'correct'],
                            ['value' => 'fail', 'reason' => 'present'],
                            ['value' => 'failed', 'reason' => 'past'],
                            ['value' => 'would fail', 'reason' => 'would'],
                        ],
                    ],
                ],
            ],
            [
                'source' => 'mixed',
                'level' => 'B1',
                'tenses' => ['First Conditional'],
                'question' => 'If he {a1} the book, he {a2} the story.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_first_negative',
                        'subject' => 'he',
                        'verb' => 'read',
                        'verb_hint' => 'read',
                        'answer' => 'does not read',
                        'options' => [
                            ['value' => 'does not read', 'reason' => 'correct'],
                            ['value' => 'will not read', 'reason' => 'future'],
                            ['value' => 'read', 'reason' => 'present'],
                            ['value' => 'would not read', 'reason' => 'would'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_first_negative',
                        'subject' => 'he',
                        'verb' => 'understand',
                        'verb_hint' => 'not understand',
                        'answer' => 'will not understand',
                        'options' => [
                            ['value' => 'will not understand', 'reason' => 'correct'],
                            ['value' => 'will understand', 'reason' => 'future_positive'],
                            ['value' => 'understood', 'reason' => 'past'],
                            ['value' => 'would not understand', 'reason' => 'would'],
                        ],
                    ],
                ],
            ],
            [
                'source' => 'mixed',
                'level' => 'B1',
                'tenses' => ['First Conditional'],
                'question' => 'If I {a1} your phone, I {a2} you.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_first_present',
                        'subject' => 'I',
                        'verb' => 'find',
                        'verb_hint' => 'find',
                        'answer' => 'find',
                        'options' => [
                            ['value' => 'find', 'reason' => 'correct'],
                            ['value' => 'finds', 'reason' => 'present'],
                            ['value' => 'found', 'reason' => 'past'],
                            ['value' => 'will find', 'reason' => 'future'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_first_will',
                        'subject' => 'I',
                        'verb' => 'tell',
                        'verb_hint' => 'tell',
                        'answer' => 'will tell',
                        'options' => [
                            ['value' => 'will tell', 'reason' => 'correct'],
                            ['value' => 'tell', 'reason' => 'present'],
                            ['value' => 'told', 'reason' => 'past'],
                            ['value' => 'would tell', 'reason' => 'would'],
                        ],
                    ],
                ],
            ],
            [
                'source' => 'mixed',
                'level' => 'B1',
                'tenses' => ['First Conditional'],
                'question' => 'If he {a1} the lottery, he {a2} around the world.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_first_present',
                        'subject' => 'he',
                        'verb' => 'win',
                        'verb_hint' => 'win',
                        'answer' => 'wins',
                        'options' => [
                            ['value' => 'wins', 'reason' => 'correct'],
                            ['value' => 'win', 'reason' => 'present'],
                            ['value' => 'won', 'reason' => 'past'],
                            ['value' => 'will win', 'reason' => 'future'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_first_will',
                        'subject' => 'he',
                        'verb' => 'travel',
                        'verb_hint' => 'travel',
                        'answer' => 'will travel',
                        'options' => [
                            ['value' => 'will travel', 'reason' => 'correct'],
                            ['value' => 'travels', 'reason' => 'present'],
                            ['value' => 'traveled', 'reason' => 'past'],
                            ['value' => 'would travel', 'reason' => 'would'],
                        ],
                    ],
                ],
            ],
            [
                'source' => 'mixed',
                'level' => 'B1',
                'tenses' => ['Second Conditional'],
                'question' => 'If I {a1} president, I {a2} a lot of changes.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_second_past',
                        'subject' => 'I',
                        'verb' => 'be',
                        'verb_hint' => 'be',
                        'answer' => 'were',
                        'options' => [
                            ['value' => 'were', 'reason' => 'correct'],
                            ['value' => 'am', 'reason' => 'present'],
                            ['value' => 'was', 'reason' => 'present'],
                            ['value' => 'would be', 'reason' => 'would'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_second_would',
                        'subject' => 'I',
                        'verb' => 'make',
                        'verb_hint' => 'make',
                        'answer' => 'would make',
                        'options' => [
                            ['value' => 'would make', 'reason' => 'correct'],
                            ['value' => 'will make', 'reason' => 'will'],
                            ['value' => 'make', 'reason' => 'present'],
                            ['value' => 'made', 'reason' => 'past'],
                        ],
                    ],
                ],
            ],
            [
                'source' => 'mixed',
                'level' => 'B1',
                'tenses' => ['Second Conditional'],
                'question' => 'If he {a1} more money, he {a2} a Porsche.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_second_past',
                        'subject' => 'he',
                        'verb' => 'have',
                        'verb_hint' => 'have',
                        'answer' => 'had',
                        'options' => [
                            ['value' => 'had', 'reason' => 'correct'],
                            ['value' => 'has', 'reason' => 'present'],
                            ['value' => 'will have', 'reason' => 'future'],
                            ['value' => 'had had', 'reason' => 'perfect'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_second_would',
                        'subject' => 'he',
                        'verb' => 'buy',
                        'verb_hint' => 'buy',
                        'answer' => 'would buy',
                        'options' => [
                            ['value' => 'would buy', 'reason' => 'correct'],
                            ['value' => 'will buy', 'reason' => 'will'],
                            ['value' => 'buys', 'reason' => 'present'],
                            ['value' => 'bought', 'reason' => 'past'],
                        ],
                    ],
                ],
            ],
            [
                'source' => 'mixed',
                'level' => 'B1',
                'tenses' => ['Second Conditional'],
                'question' => 'What {a1} you {a2} if you {a3} a wallet in the street?',
                'markers' => [
                    'a1' => [
                        'type' => 'result_second_would',
                        'subject' => 'you',
                        'verb' => 'do',
                        'answer' => 'would',
                        'options' => [
                            ['value' => 'would', 'reason' => 'correct'],
                            ['value' => 'will', 'reason' => 'will'],
                            ['value' => 'do', 'reason' => 'present'],
                            ['value' => 'did', 'reason' => 'past'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_second_would',
                        'subject' => 'you',
                        'verb' => 'do',
                        'verb_hint' => 'do',
                        'answer' => 'do',
                        'options' => [
                            ['value' => 'do', 'reason' => 'correct'],
                            ['value' => 'does', 'reason' => 'present'],
                            ['value' => 'did', 'reason' => 'past'],
                            ['value' => 'will do', 'reason' => 'will'],
                        ],
                    ],
                    'a3' => [
                        'type' => 'if_second_past',
                        'subject' => 'you',
                        'verb' => 'find',
                        'verb_hint' => 'find',
                        'answer' => 'found',
                        'options' => [
                            ['value' => 'found', 'reason' => 'correct'],
                            ['value' => 'find', 'reason' => 'present'],
                            ['value' => 'will find', 'reason' => 'future'],
                            ['value' => 'had found', 'reason' => 'perfect'],
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
