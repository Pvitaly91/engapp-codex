<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class ConditionalsZeroToSecondWorksheetV2Seeder extends QuestionSeeder
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
        'result_zero_present' => "Час: Zero Conditional.\nФормула: **Present Simple** (наприклад, «%subject% %verb%»).\nПояснення: Результат загального правила також стоїть у Present Simple.\nПриклад: *%example%*",
        'result_zero_negative' => "Час: Zero Conditional.\nФормула: **do/does not + V1** (наприклад, «do not %verb%»).\nПояснення: Заперечення у нульовому умовному будуємо через do/does not.\nПриклад: *%example%*",
        'if_first_present' => "Час: First Conditional.\nФормула: **if + Present Simple** (наприклад, «if + %verb%»).\nПояснення: Умовну частину про реальне майбутнє пишемо в Present Simple.\nПриклад: *%example%*",
        'result_first_will' => "Час: First Conditional.\nФормула: **will + V1** (наприклад, «will %verb%»).\nПояснення: Результат реальної умови виражаємо через will + базове дієслово.\nПриклад: *%example%*",
        'result_first_have_to' => "Час: First Conditional.\nФормула: **will have to + V1**.\nПояснення: Для необхідності в майбутньому використовуємо will have to + базове дієслово.\nПриклад: *%example%*",
        'if_second_past' => "Час: Second Conditional.\nФормула: **if + Past Simple**.\nПояснення: Уявні ситуації в умовній частині виражаємо Past Simple.\nПриклад: *%example%*",
        'result_second_would' => "Час: Second Conditional.\nФормула: **would + V1**.\nПояснення: Результат уявної ситуації будуємо через would + базове дієслово.\nПриклад: *%example%*",
        'result_second_negative' => "Час: Second Conditional.\nФормула: **would not / wouldn't + V1**.\nПояснення: Заперечення у результаті другого умовного створюємо через wouldn't + V1.\nПриклад: *%example%*",
    ];

    private array $explanationTemplates = [
        'if_zero_present' => [
            'correct' => "✅ «%option%» — Present Simple у нульовому умовному для %subject%.\nПриклад: *%example%*",
            'future' => "❌ «%option%» має will, але Zero Conditional використовує Present Simple.\nПриклад: *%example%*",
            'would' => "❌ «%option%» належить до другого умовного з would.\nПриклад: *%example%*",
            'past' => "❌ «%option%» — Past Simple; для загальної істини потрібен Present Simple.\nПриклад: *%example%*",
            'continuous' => "❌ «%option%» — тривала форма; в Zero Conditional потрібна проста форма.\nПриклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
        'result_zero_present' => [
            'correct' => "✅ «%option%» — Present Simple показує результат загального правила.\nПриклад: *%example%*",
            'future' => "❌ «%option%» містить will, але у нульовому умовному результат теж у Present Simple.\nПриклад: *%example%*",
            'would' => "❌ «%option%» належить до Second Conditional; тут потрібен Present Simple.\nПриклад: *%example%*",
            'past' => "❌ «%option%» — Past Simple; лишаємо Present Simple.\nПриклад: *%example%*",
            'present' => "❌ «%option%» не має потрібного закінчення/форми Present Simple для %subject%.\nПриклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
        'result_zero_negative' => [
            'correct' => "✅ «%option%» — правильне заперечення do/does not + V1 у Zero Conditional.\nПриклад: *%example%*",
            'future' => "❌ «%option%» має will, але нульовий умовний не використовує will.\nПриклад: *%example%*",
            'would' => "❌ «%option%» належить до другого умовного.\nПриклад: *%example%*",
            'present' => "❌ «%option%» — ствердна форма; потрібне заперечення з do/does not.\nПриклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
        'if_first_present' => [
            'correct' => "✅ «%option%» — Present Simple у підрядній частині першого умовного.\nПриклад: *%example%*",
            'future' => "❌ «%option%» містить will, але в if-клауза типу 1 використовуємо Present Simple.\nПриклад: *%example%*",
            'past' => "❌ «%option%» — Past Simple; для реальної умови потрібен Present Simple.\nПриклад: *%example%*",
            'would' => "❌ «%option%» належить до другого умовного.\nПриклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
        'result_first_will' => [
            'correct' => "✅ «%option%» — правильна форма will + V1 у першому умовному.\nПриклад: *%example%*",
            'present' => "❌ «%option%» — Present Simple; потрібне will + V1.\nПриклад: *%example%*",
            'past' => "❌ «%option%» — Past Simple; результат має містити will.\nПриклад: *%example%*",
            'would' => "❌ «%option%» належить до другого умовного.\nПриклад: *%example%*",
            'continuous' => "❌ «%option%» — тривала форма; вправа тренує will + V1.\nПриклад: *%example%*",
            'extra_will' => "❌ «%option%» дублює will, хоча допоміжне слово вже є у реченні.\nПриклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
        'result_first_have_to' => [
            'correct' => "✅ «%option%» — правильна конструкція will have to + V1.\nПриклад: *%example%*",
            'would' => "❌ «%option%» належить до другого умовного; потрібно will have to.\nПриклад: *%example%*",
            'present' => "❌ «%option%» — Present Simple; виражай необхідність як will have to.\nПриклад: *%example%*",
            'past' => "❌ «%option%» — Past Simple; тут говоримо про майбутнє.\nПриклад: *%example%*",
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
        $categoryId = Category::firstOrCreate(['name' => 'Conditionals'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Worksheet: Conditionals 0-1-2 (V2)'])->id;

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Conditionals Type 0-1-2 Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Mixed Conditional Forms Worksheet'],
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
                'level' => 'A2',
                'tenses' => ['Zero Conditional'],
                'question' => 'If the sun {a1} high, it {a2} hot.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_zero_present',
                        'subject' => 'the sun',
                        'verb' => 'rise',
                        'verb_hint' => 'rise',
                        'answer' => 'rises',
                        'options' => [
                            ['value' => 'rises', 'reason' => 'correct'],
                            ['value' => 'will rise', 'reason' => 'future'],
                            ['value' => 'rose', 'reason' => 'past'],
                            ['value' => 'would rise', 'reason' => 'would'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_zero_present',
                        'subject' => 'it',
                        'verb' => 'turn',
                        'verb_hint' => 'turn',
                        'answer' => 'turns',
                        'options' => [
                            ['value' => 'turns', 'reason' => 'correct'],
                            ['value' => 'will turn', 'reason' => 'future'],
                            ['value' => 'turned', 'reason' => 'past'],
                            ['value' => 'would turn', 'reason' => 'would'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'tenses' => ['Zero Conditional'],
                'question' => 'Plants {a1} water, or they {a2}.',
                'markers' => [
                    'a1' => [
                        'type' => 'result_zero_present',
                        'subject' => 'plants',
                        'verb' => 'need',
                        'verb_hint' => 'need',
                        'answer' => 'need',
                        'options' => [
                            ['value' => 'need', 'reason' => 'correct'],
                            ['value' => 'needs', 'reason' => 'present'],
                            ['value' => 'needed', 'reason' => 'past'],
                            ['value' => 'will need', 'reason' => 'future'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_zero_negative',
                        'subject' => 'they',
                        'verb' => 'grow',
                        'verb_hint' => 'not grow',
                        'answer' => "do not grow",
                        'options' => [
                            ['value' => "do not grow", 'reason' => 'correct'],
                            ['value' => "won't grow", 'reason' => 'future'],
                            ['value' => "wouldn't grow", 'reason' => 'would'],
                            ['value' => 'grow', 'reason' => 'present'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'tenses' => ['Zero Conditional'],
                'question' => 'If it {a1}, people {a2} umbrellas.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_zero_present',
                        'subject' => 'it',
                        'verb' => 'rain',
                        'verb_hint' => 'rain',
                        'answer' => 'rains',
                        'options' => [
                            ['value' => 'rains', 'reason' => 'correct'],
                            ['value' => 'will rain', 'reason' => 'future'],
                            ['value' => 'rained', 'reason' => 'past'],
                            ['value' => 'would rain', 'reason' => 'would'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_zero_present',
                        'subject' => 'people',
                        'verb' => 'use',
                        'verb_hint' => 'use',
                        'answer' => 'use',
                        'options' => [
                            ['value' => 'use', 'reason' => 'correct'],
                            ['value' => 'uses', 'reason' => 'present'],
                            ['value' => 'will use', 'reason' => 'future'],
                            ['value' => 'used', 'reason' => 'past'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'tenses' => ['Zero Conditional'],
                'question' => 'If Priya {a1} sports, he or she always {a2} fit.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_zero_present',
                        'subject' => 'Priya',
                        'verb' => 'practise',
                        'verb_hint' => 'practise',
                        'answer' => 'practises',
                        'options' => [
                            ['value' => 'practises', 'reason' => 'correct'],
                            ['value' => 'practise', 'reason' => 'present'],
                            ['value' => 'will practise', 'reason' => 'future'],
                            ['value' => 'practised', 'reason' => 'past'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_zero_present',
                        'subject' => 'he or she',
                        'verb' => 'feel',
                        'verb_hint' => 'feel',
                        'answer' => 'feels',
                        'options' => [
                            ['value' => 'feels', 'reason' => 'correct'],
                            ['value' => 'feel', 'reason' => 'present'],
                            ['value' => 'will feel', 'reason' => 'future'],
                            ['value' => 'felt', 'reason' => 'past'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'tenses' => ['First Conditional'],
                'question' => 'If you {a1} the supper, I {a2} the dishes.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_first_present',
                        'subject' => 'you',
                        'verb' => 'cook',
                        'verb_hint' => 'cook',
                        'answer' => 'cook',
                        'options' => [
                            ['value' => 'cook', 'reason' => 'correct'],
                            ['value' => 'cooks', 'reason' => 'present'],
                            ['value' => 'cooked', 'reason' => 'past'],
                            ['value' => 'will cook', 'reason' => 'future'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_first_will',
                        'subject' => 'I',
                        'verb' => 'wash',
                        'verb_hint' => 'wash',
                        'answer' => 'will wash',
                        'options' => [
                            ['value' => 'will wash', 'reason' => 'correct'],
                            ['value' => 'wash', 'reason' => 'present'],
                            ['value' => 'washed', 'reason' => 'past'],
                            ['value' => 'would wash', 'reason' => 'would'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'tenses' => ['First Conditional'],
                'question' => 'If I {a1} a million dollars, I {a2} a big house.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_first_present',
                        'subject' => 'I',
                        'verb' => 'have',
                        'verb_hint' => 'have',
                        'answer' => 'have',
                        'options' => [
                            ['value' => 'have', 'reason' => 'correct'],
                            ['value' => 'has', 'reason' => 'present'],
                            ['value' => 'had', 'reason' => 'past'],
                            ['value' => 'will have', 'reason' => 'future'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_first_will',
                        'subject' => 'I',
                        'verb' => 'buy',
                        'verb_hint' => 'buy',
                        'answer' => 'will buy',
                        'options' => [
                            ['value' => 'will buy', 'reason' => 'correct'],
                            ['value' => 'buy', 'reason' => 'present'],
                            ['value' => 'bought', 'reason' => 'past'],
                            ['value' => 'would buy', 'reason' => 'would'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'tenses' => ['First Conditional'],
                'question' => 'If Juan {a1} his friend, he {a2} to the park.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_first_present',
                        'subject' => 'Juan',
                        'verb' => 'see',
                        'verb_hint' => 'see',
                        'answer' => 'sees',
                        'options' => [
                            ['value' => 'sees', 'reason' => 'correct'],
                            ['value' => 'see', 'reason' => 'present'],
                            ['value' => 'saw', 'reason' => 'past'],
                            ['value' => 'will see', 'reason' => 'future'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_first_will',
                        'subject' => 'he',
                        'verb' => 'go',
                        'verb_hint' => 'go',
                        'answer' => 'will go',
                        'options' => [
                            ['value' => 'will go', 'reason' => 'correct'],
                            ['value' => 'goes', 'reason' => 'present'],
                            ['value' => 'went', 'reason' => 'past'],
                            ['value' => 'would go', 'reason' => 'would'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'tenses' => ['First Conditional'],
                'question' => 'Paula {a1} sad if Juan {a2} away.',
                'markers' => [
                    'a1' => [
                        'type' => 'result_first_will',
                        'subject' => 'Paula',
                        'verb' => 'be',
                        'verb_hint' => 'be',
                        'answer' => 'will be',
                        'options' => [
                            ['value' => 'will be', 'reason' => 'correct'],
                            ['value' => 'is', 'reason' => 'present'],
                            ['value' => 'was', 'reason' => 'past'],
                            ['value' => 'would be', 'reason' => 'would'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'if_first_present',
                        'subject' => 'Juan',
                        'verb' => 'move',
                        'verb_hint' => 'move',
                        'answer' => 'moves',
                        'options' => [
                            ['value' => 'moves', 'reason' => 'correct'],
                            ['value' => 'move', 'reason' => 'present'],
                            ['value' => 'will move', 'reason' => 'future'],
                            ['value' => 'moved', 'reason' => 'past'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tenses' => ['Second Conditional'],
                'question' => 'If I {a1} a million dollars, I {a2} a big house.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_second_past',
                        'subject' => 'I',
                        'verb' => 'have',
                        'verb_hint' => 'have',
                        'answer' => 'had',
                        'options' => [
                            ['value' => 'had', 'reason' => 'correct'],
                            ['value' => 'have', 'reason' => 'present'],
                            ['value' => 'would have', 'reason' => 'would'],
                            ['value' => 'had had', 'reason' => 'perfect'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_second_would',
                        'subject' => 'I',
                        'verb' => 'buy',
                        'verb_hint' => 'buy',
                        'answer' => 'would buy',
                        'options' => [
                            ['value' => 'would buy', 'reason' => 'correct'],
                            ['value' => 'will buy', 'reason' => 'will'],
                            ['value' => 'buy', 'reason' => 'present'],
                            ['value' => 'bought', 'reason' => 'past'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tenses' => ['Second Conditional'],
                'question' => 'If he {a1} a bird, he {a2} to you.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_second_past',
                        'subject' => 'he',
                        'verb' => 'be',
                        'verb_hint' => 'be',
                        'answer' => 'were',
                        'options' => [
                            ['value' => 'were', 'reason' => 'correct'],
                            ['value' => 'was', 'reason' => 'present'],
                            ['value' => 'is', 'reason' => 'present'],
                            ['value' => 'would be', 'reason' => 'would'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_second_would',
                        'subject' => 'he',
                        'verb' => 'fly',
                        'verb_hint' => 'fly',
                        'answer' => 'would fly',
                        'options' => [
                            ['value' => 'would fly', 'reason' => 'correct'],
                            ['value' => 'will fly', 'reason' => 'will'],
                            ['value' => 'flies', 'reason' => 'present'],
                            ['value' => 'flew', 'reason' => 'past'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tenses' => ['Second Conditional'],
                'question' => 'If I {a1} the answer, I {a2} it to you.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_second_past',
                        'subject' => 'I',
                        'verb' => 'know',
                        'verb_hint' => 'know',
                        'answer' => 'knew',
                        'options' => [
                            ['value' => 'knew', 'reason' => 'correct'],
                            ['value' => 'know', 'reason' => 'present'],
                            ['value' => 'will know', 'reason' => 'future'],
                            ['value' => 'had known', 'reason' => 'perfect'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_second_would',
                        'subject' => 'I',
                        'verb' => 'show',
                        'verb_hint' => 'show',
                        'answer' => 'would show',
                        'options' => [
                            ['value' => 'would show', 'reason' => 'correct'],
                            ['value' => 'will show', 'reason' => 'will'],
                            ['value' => 'show', 'reason' => 'present'],
                            ['value' => 'showed', 'reason' => 'past'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tenses' => ['Second Conditional'],
                'question' => 'If we {a1} on the same street, I {a2} you every day.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_second_past',
                        'subject' => 'we',
                        'verb' => 'live',
                        'verb_hint' => 'live',
                        'answer' => 'lived',
                        'options' => [
                            ['value' => 'lived', 'reason' => 'correct'],
                            ['value' => 'live', 'reason' => 'present'],
                            ['value' => 'will live', 'reason' => 'future'],
                            ['value' => 'had lived', 'reason' => 'perfect'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_second_would',
                        'subject' => 'I',
                        'verb' => 'visit',
                        'verb_hint' => 'visit',
                        'answer' => 'would visit',
                        'options' => [
                            ['value' => 'would visit', 'reason' => 'correct'],
                            ['value' => 'will visit', 'reason' => 'will'],
                            ['value' => 'visit', 'reason' => 'present'],
                            ['value' => 'visited', 'reason' => 'past'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'tenses' => ['Zero Conditional'],
                'question' => 'If you {a1} water, it {a2} to steam.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_zero_present',
                        'subject' => 'you',
                        'verb' => 'boil',
                        'verb_hint' => 'boil',
                        'answer' => 'boil',
                        'options' => [
                            ['value' => 'boil', 'reason' => 'correct'],
                            ['value' => 'boils', 'reason' => 'present'],
                            ['value' => 'will boil', 'reason' => 'future'],
                            ['value' => 'boiled', 'reason' => 'past'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_zero_present',
                        'subject' => 'it',
                        'verb' => 'turn',
                        'verb_hint' => 'turn',
                        'answer' => 'turns',
                        'options' => [
                            ['value' => 'turns', 'reason' => 'correct'],
                            ['value' => 'turn', 'reason' => 'present'],
                            ['value' => 'will turn', 'reason' => 'future'],
                            ['value' => 'turned', 'reason' => 'past'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'tenses' => ['First Conditional'],
                'question' => 'If I {a1} some fresh vegetables tomorrow, I {a2} a salad for you.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_first_present',
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
                    'a2' => [
                        'type' => 'result_first_will',
                        'subject' => 'I',
                        'verb' => 'make',
                        'verb_hint' => 'make',
                        'answer' => 'will make',
                        'options' => [
                            ['value' => 'will make', 'reason' => 'correct'],
                            ['value' => 'make', 'reason' => 'present'],
                            ['value' => 'made', 'reason' => 'past'],
                            ['value' => 'would make', 'reason' => 'would'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'tenses' => ['First Conditional'],
                'question' => 'If he {a1} around this evening, I {a2} him to the movies.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_first_present',
                        'subject' => 'he',
                        'verb' => 'come',
                        'verb_hint' => 'come',
                        'answer' => 'comes',
                        'options' => [
                            ['value' => 'comes', 'reason' => 'correct'],
                            ['value' => 'come', 'reason' => 'present'],
                            ['value' => 'came', 'reason' => 'past'],
                            ['value' => 'will come', 'reason' => 'future'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_first_will',
                        'subject' => 'I',
                        'verb' => 'take',
                        'verb_hint' => 'take',
                        'answer' => 'will take',
                        'options' => [
                            ['value' => 'will take', 'reason' => 'correct'],
                            ['value' => 'take', 'reason' => 'present'],
                            ['value' => 'took', 'reason' => 'past'],
                            ['value' => 'would take', 'reason' => 'would'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'tenses' => ['First Conditional'],
                'question' => 'If the weather {a1} sunny at the weekend, we {a2} to the mountains.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_first_present',
                        'subject' => 'the weather',
                        'verb' => 'be',
                        'verb_hint' => 'be',
                        'answer' => 'is',
                        'options' => [
                            ['value' => 'is', 'reason' => 'correct'],
                            ['value' => 'will be', 'reason' => 'future'],
                            ['value' => 'was', 'reason' => 'past'],
                            ['value' => 'are', 'reason' => 'present'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_first_will',
                        'subject' => 'we',
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
                'level' => 'A2',
                'tenses' => ['First Conditional'],
                'question' => 'If we {a1} hard, we {a2} the exam.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_first_present',
                        'subject' => 'we',
                        'verb' => 'study',
                        'verb_hint' => 'study',
                        'answer' => 'study',
                        'options' => [
                            ['value' => 'study', 'reason' => 'correct'],
                            ['value' => 'studies', 'reason' => 'present'],
                            ['value' => 'studied', 'reason' => 'past'],
                            ['value' => 'will study', 'reason' => 'future'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_first_will',
                        'subject' => 'we',
                        'verb' => 'pass',
                        'verb_hint' => 'pass',
                        'answer' => 'will pass',
                        'options' => [
                            ['value' => 'will pass', 'reason' => 'correct'],
                            ['value' => 'pass', 'reason' => 'present'],
                            ['value' => 'passed', 'reason' => 'past'],
                            ['value' => 'would pass', 'reason' => 'would'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tenses' => ['Second Conditional'],
                'question' => 'If we {a1} harder, we {a2} the exam.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_second_past',
                        'subject' => 'we',
                        'verb' => 'study',
                        'verb_hint' => 'study',
                        'answer' => 'studied',
                        'options' => [
                            ['value' => 'studied', 'reason' => 'correct'],
                            ['value' => 'study', 'reason' => 'present'],
                            ['value' => 'will study', 'reason' => 'future'],
                            ['value' => 'had studied', 'reason' => 'perfect'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_second_would',
                        'subject' => 'we',
                        'verb' => 'pass',
                        'verb_hint' => 'pass',
                        'answer' => 'would pass',
                        'options' => [
                            ['value' => 'would pass', 'reason' => 'correct'],
                            ['value' => 'will pass', 'reason' => 'will'],
                            ['value' => 'pass', 'reason' => 'present'],
                            ['value' => 'passed', 'reason' => 'past'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tenses' => ['Second Conditional'],
                'question' => 'If I {a1} more, I {a2} better grades.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_second_past',
                        'subject' => 'I',
                        'verb' => 'study',
                        'verb_hint' => 'study',
                        'answer' => 'studied',
                        'options' => [
                            ['value' => 'studied', 'reason' => 'correct'],
                            ['value' => 'study', 'reason' => 'present'],
                            ['value' => 'will study', 'reason' => 'future'],
                            ['value' => 'had studied', 'reason' => 'perfect'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_second_would',
                        'subject' => 'I',
                        'verb' => 'get',
                        'verb_hint' => 'get',
                        'answer' => 'would get',
                        'options' => [
                            ['value' => 'would get', 'reason' => 'correct'],
                            ['value' => 'will get', 'reason' => 'will'],
                            ['value' => 'get', 'reason' => 'present'],
                            ['value' => 'got', 'reason' => 'past'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'tenses' => ['Zero Conditional'],
                'question' => 'If she {a1} to bed early, she {a2} up on time.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_zero_present',
                        'subject' => 'she',
                        'verb' => 'go',
                        'verb_hint' => 'go',
                        'answer' => 'goes',
                        'options' => [
                            ['value' => 'goes', 'reason' => 'correct'],
                            ['value' => 'go', 'reason' => 'present'],
                            ['value' => 'will go', 'reason' => 'future'],
                            ['value' => 'went', 'reason' => 'past'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_zero_present',
                        'subject' => 'she',
                        'verb' => 'wake',
                        'verb_hint' => 'wake',
                        'answer' => 'wakes',
                        'options' => [
                            ['value' => 'wakes', 'reason' => 'correct'],
                            ['value' => 'wake', 'reason' => 'present'],
                            ['value' => 'will wake', 'reason' => 'future'],
                            ['value' => 'woke', 'reason' => 'past'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'tenses' => ['First Conditional'],
                'question' => 'If the snow {a1} any worse, we {a2} go to the doctor\'s.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_first_present',
                        'subject' => 'the snow',
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
                    'a2' => [
                        'type' => 'result_first_have_to',
                        'subject' => 'we',
                        'verb' => 'go',
                        'verb_hint' => 'have to',
                        'answer' => 'will have to',
                        'options' => [
                            ['value' => 'will have to', 'reason' => 'correct'],
                            ['value' => 'have to', 'reason' => 'present'],
                            ['value' => 'would have to', 'reason' => 'would'],
                            ['value' => 'had to', 'reason' => 'past'],
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
