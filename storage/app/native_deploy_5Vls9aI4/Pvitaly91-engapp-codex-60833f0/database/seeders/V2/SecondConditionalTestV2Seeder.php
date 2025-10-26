<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class SecondConditionalTestV2Seeder extends QuestionSeeder
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
        'if_past_simple' => "Час: Second Conditional.\nФормула: **if + Past Simple**.\nПояснення: Умовна частина описує нереальну ситуацію, тому дієслово ставимо у Past Simple (наприклад, від «%verb%» беремо минулу форму).\nПриклад: *%example%*",
        'if_were' => "Час: Second Conditional.\nФормула: **if + were** для всіх осіб.\nПояснення: З дієсловом to be у другому умовному використовуємо «were», навіть із «I» або «she».\nПриклад: *%example%*",
        'if_modal_could' => "Час: Second Conditional.\nФормула: **if + could + V1** (наприклад, «could %verb%»).\nПояснення: «could» — минула форма від «can», яку беремо, щоб показати уявну можливість.\nПриклад: *%example%*",
        'result_would_simple' => "Час: Second Conditional.\nФормула: **would + V1** (наприклад, «would + %verb%»).\nПояснення: Результат описуємо модальним would та базовим дієсловом без to.\nПриклад: *%example%*",
        'result_would_be_able' => "Час: Second Conditional.\nФормула: **would + be able to + V1** (наприклад, «would be able to %verb%»).\nПояснення: Щоб говорити про можливість як результат, використовуємо be able to після would.\nПриклад: *%example%*",
        'result_would_negative' => "Час: Second Conditional.\nФормула: **would not + V1** (наприклад, «would not %verb%»).\nПояснення: Заперечення результату утворюємо через would not (wouldn't) та базове дієслово.\nПриклад: *%example%*",
    ];

    private array $explanationTemplates = [
        'if_past_simple' => [
            'correct' => "✅ «%option%» правильно, бо в if-клаузі другого умовного для %subject% потрібна форма Past Simple від «%verb%».\nПриклад: *%example%*",
            'present_simple' => "❌ «%option%» — Present Simple; умова другого умовного вимагає Past Simple.\nПриклад: *%example%*",
            'third_person' => "❌ «%option%» — Present Simple з -s; потрібна форма Past Simple для уявної умови.\nПриклад: *%example%*",
            'past_perfect' => "❌ «%option%» — Past Perfect; цю форму використовують у Third Conditional, не тут.\nПриклад: *%example%*",
            'result_form' => "❌ «%option%» належить до результату з would, а в if-клаузі ми вживаємо Past Simple.\nПриклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
        'if_were' => [
            'correct' => "✅ «%option%» правильно, бо з дієсловом to be у другому умовному для %subject% уживаємо «were».\nПриклад: *%example%*",
            'was' => "❌ «%option%» — розмовна форма; граматично в другому умовному слід ставити «were» для всіх осіб.\nПриклад: *%example%*",
            'present_simple' => "❌ «%option%» показує теперішній час; у if-клауза потрібен Past Simple.\nПриклад: *%example%*",
            'result_form' => "❌ «%option%» належить до частини з would, а не до умовної частини.\nПриклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
        'if_modal_could' => [
            'correct' => "✅ «%option%» правильно, бо уявну можливість у другому умовному передаємо «could» + V1.\nПриклад: *%example%*",
            'present_modal' => "❌ «%option%» — теперішня форма «can»; треба Past Simple «could».\nПриклад: *%example%*",
            'bare_was_able' => "❌ «%option%» звучить як минулий факт «was able» і ще потребує «to» перед дієсловом; нам потрібне модальне «could».\nПриклад: *%example%*",
            'modal_stack' => "❌ «%option%» неправильно поєднує два модальні дієслова разом.\nПриклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
        'result_would_simple' => [
            'correct' => "✅ «%option%» правильно, бо результат другого умовного будуємо як would + базове дієслово.\nПриклад: *%example%*",
            'first_conditional' => "❌ «%option%» з «will» утворює First Conditional про реальні події, а тут потрібен Second Conditional.\nПриклад: *%example%*",
            'present_simple' => "❌ «%option%» — Present Simple; уявний результат вимагає would + V1.\nПриклад: *%example%*",
            'third_person' => "❌ «%option%» — Present Simple з -s; після would дієслово не отримує закінчень.\nПриклад: *%example%*",
            'missing_would' => "❌ «%option%» — форма без «would», тому не показує умовний результат.\nПриклад: *%example%*",
            'past_simple' => "❌ «%option%» — Past Simple, а треба would + V1 для результату.\nПриклад: *%example%*",
            'continuous' => "❌ «%option%» — форма Continuous; вправа тренує просту модель would + V1.\nПриклад: *%example%*",
            'third_conditional' => "❌ «%option%» — структура Third Conditional (would have + V3), а ми працюємо з Second Conditional.\nПриклад: *%example%*",
            'agreement' => "❌ «%option%» має закінчення -s після would, що граматично неправильно.\nПриклад: *%example%*",
            'extra_word' => "❌ «%option%» містить зайвий елемент і не відповідає простій формулі would + V1.\nПриклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
        'result_would_be_able' => [
            'correct' => "✅ «%option%» правильно, бо конструкція можливості у другому умовному — would + be able to + V1.\nПриклад: *%example%*",
            'first_conditional' => "❌ «%option%» з «will» створює First Conditional про реальне майбутнє.\nПриклад: *%example%*",
            'present_simple' => "❌ «%option%» — теперішній час; результат другого умовного має would + be able to.\nПриклад: *%example%*",
            'third_conditional' => "❌ «%option%» — форма Third Conditional (would have been able).\nПриклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
        'result_would_negative' => [
            'correct' => "✅ «%option%» правильно, бо заперечення формуємо як would not + V1.\nПриклад: *%example%*",
            'first_conditional' => "❌ «%option%» з «will not» говорить про реальне майбутнє (First Conditional).\nПриклад: *%example%*",
            'present_simple' => "❌ «%option%» — Present Simple; уявний результат потребує would + V1.\nПриклад: *%example%*",
            'continuous' => "❌ «%option%» — тривала форма; у вправі потрібна проста конструкція would + V1.\nПриклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Conditionals'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Custom: Second Conditional Test V2'])->id;

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Second Conditional Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Second Conditional Sentence Completion'],
            ['category' => 'English Grammar Detail']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'Second Conditional Sentences'],
            ['category' => 'English Grammar Structure']
        )->id;

        $tenseTagId = Tag::firstOrCreate(
            ['name' => 'Second Conditional'],
            ['category' => 'English Grammar Tense']
        )->id;

        $entries = $this->questionEntries();

        $items = [];
        $meta = [];

        foreach ($entries as $index => $entry) {
            $answersMap = [];
            $verbHints = [];
            foreach ($entry['markers'] as $marker => $markerData) {
                $answersMap[$marker] = $markerData['answer'];
                $verbHints[$marker] = $markerData['verb_hint'] ?? ($markerData['verb'] ?? null);
            }

            $example = $this->formatExample($entry['question'], $answersMap);

            $options = [];
            $optionMarkerMap = [];
            $explanations = [];
            $hints = [];

            foreach ($entry['markers'] as $marker => $markerData) {
                $options[$marker] = [];
                foreach ($markerData['options'] as $option) {
                    $value = $option['value'];
                    $reason = $option['reason'] ?? 'default';

                    $options[$marker][] = $value;
                    $optionMarkerMap[$value] = $marker;
                    $explanations[$value] = $this->buildExplanation(
                        $markerData['type'],
                        $reason,
                        $value,
                        [
                            'subject' => $markerData['subject'] ?? '',
                            'verb' => $markerData['verb'] ?? '',
                        ],
                        $example
                    );
                }

                $hints[$marker] = $this->buildHint(
                    $markerData['type'],
                    [
                        'subject' => $markerData['subject'] ?? '',
                        'verb' => $markerData['verb'] ?? '',
                    ],
                    $example
                );
            }

            $flatOptions = [];
            foreach ($options as $markerOptions) {
                foreach ($markerOptions as $value) {
                    $flatOptions[] = $value;
                }
            }

            $answers = [];
            foreach ($answersMap as $marker => $answer) {
                $answers[] = [
                    'marker' => $marker,
                    'answer' => $answer,
                    'verb_hint' => $this->normalizeHint($verbHints[$marker] ?? null),
                ];
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
                'tag_ids' => array_values(array_unique([$themeTagId, $detailTagId, $structureTagId, $tenseTagId])),
                'answers' => $answers,
                'options' => $flatOptions,
                'variants' => [],
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
                'question' => 'If I {a1} a new job, I {a2} very happy.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_simple',
                        'subject' => 'I',
                        'verb' => 'get',
                        'verb_hint' => 'get',
                        'answer' => 'got',
                        'options' => [
                            ['value' => 'got', 'reason' => 'correct'],
                            ['value' => 'get', 'reason' => 'present_simple'],
                            ['value' => 'gets', 'reason' => 'third_person'],
                            ['value' => 'had got', 'reason' => 'past_perfect'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_would_simple',
                        'subject' => 'I',
                        'verb' => 'be',
                        'verb_hint' => 'be',
                        'answer' => 'would be',
                        'options' => [
                            ['value' => 'would be', 'reason' => 'correct'],
                            ['value' => 'will be', 'reason' => 'first_conditional'],
                            ['value' => 'am', 'reason' => 'present_simple'],
                            ['value' => 'was', 'reason' => 'missing_would'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'If I {a1} younger, I {a2} more.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_were',
                        'subject' => 'I',
                        'verb' => 'be',
                        'verb_hint' => 'be',
                        'answer' => 'were',
                        'options' => [
                            ['value' => 'were', 'reason' => 'correct'],
                            ['value' => 'was', 'reason' => 'was'],
                            ['value' => 'are', 'reason' => 'present_simple'],
                            ['value' => 'would be', 'reason' => 'result_form'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_would_simple',
                        'subject' => 'I',
                        'verb' => 'travel',
                        'verb_hint' => 'travel',
                        'answer' => 'would travel',
                        'options' => [
                            ['value' => 'would travel', 'reason' => 'correct'],
                            ['value' => 'will travel', 'reason' => 'first_conditional'],
                            ['value' => 'travel', 'reason' => 'present_simple'],
                            ['value' => 'would be travelling', 'reason' => 'continuous'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'If Sally {a1} more, she {a2} a better grade.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_simple',
                        'subject' => 'Sally',
                        'verb' => 'study',
                        'verb_hint' => 'study',
                        'answer' => 'studied',
                        'options' => [
                            ['value' => 'studied', 'reason' => 'correct'],
                            ['value' => 'study', 'reason' => 'present_simple'],
                            ['value' => 'studies', 'reason' => 'third_person'],
                            ['value' => 'had studied', 'reason' => 'past_perfect'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_would_simple',
                        'subject' => 'she',
                        'verb' => 'have',
                        'verb_hint' => 'have',
                        'answer' => 'would have',
                        'options' => [
                            ['value' => 'would have', 'reason' => 'correct'],
                            ['value' => 'will have', 'reason' => 'first_conditional'],
                            ['value' => 'have', 'reason' => 'present_simple'],
                            ['value' => 'would be having', 'reason' => 'continuous'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'If I {a1} a new car, I {a2} a bigger one.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_simple',
                        'subject' => 'I',
                        'verb' => 'buy',
                        'verb_hint' => 'buy',
                        'answer' => 'bought',
                        'options' => [
                            ['value' => 'bought', 'reason' => 'correct'],
                            ['value' => 'buy', 'reason' => 'present_simple'],
                            ['value' => 'buys', 'reason' => 'third_person'],
                            ['value' => 'had bought', 'reason' => 'past_perfect'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_would_simple',
                        'subject' => 'I',
                        'verb' => 'buy',
                        'verb_hint' => 'buy',
                        'answer' => 'would buy',
                        'options' => [
                            ['value' => 'would buy', 'reason' => 'correct'],
                            ['value' => 'will buy', 'reason' => 'first_conditional'],
                            ['value' => 'would be buying', 'reason' => 'continuous'],
                            ['value' => 'would have bought', 'reason' => 'third_conditional'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'If Amélie {a1} buy a new super power, she {a2} super human strength.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_modal_could',
                        'subject' => 'Amélie',
                        'verb' => 'buy',
                        'verb_hint' => 'can',
                        'answer' => 'could',
                        'options' => [
                            ['value' => 'could', 'reason' => 'correct'],
                            ['value' => 'can', 'reason' => 'present_modal'],
                            ['value' => 'was able', 'reason' => 'bare_was_able'],
                            ['value' => 'would can', 'reason' => 'modal_stack'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_would_simple',
                        'subject' => 'she',
                        'verb' => 'have',
                        'verb_hint' => 'have',
                        'answer' => 'would have',
                        'options' => [
                            ['value' => 'would have', 'reason' => 'correct'],
                            ['value' => 'will have', 'reason' => 'first_conditional'],
                            ['value' => 'has', 'reason' => 'third_person'],
                            ['value' => 'would has', 'reason' => 'agreement'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'If he {a1} the lottery, he {a2} the world.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_simple',
                        'subject' => 'he',
                        'verb' => 'win',
                        'verb_hint' => 'win',
                        'answer' => 'won',
                        'options' => [
                            ['value' => 'won', 'reason' => 'correct'],
                            ['value' => 'win', 'reason' => 'present_simple'],
                            ['value' => 'wins', 'reason' => 'third_person'],
                            ['value' => 'had won', 'reason' => 'past_perfect'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_would_simple',
                        'subject' => 'he',
                        'verb' => 'travel',
                        'verb_hint' => 'travel',
                        'answer' => 'would travel',
                        'options' => [
                            ['value' => 'would travel', 'reason' => 'correct'],
                            ['value' => 'will travel', 'reason' => 'first_conditional'],
                            ['value' => 'travels', 'reason' => 'third_person'],
                            ['value' => 'would be travelling', 'reason' => 'continuous'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'If we {a1} a new car, we {a2} to drive to the beach every day.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_simple',
                        'subject' => 'we',
                        'verb' => 'have',
                        'verb_hint' => 'have',
                        'answer' => 'had',
                        'options' => [
                            ['value' => 'had', 'reason' => 'correct'],
                            ['value' => 'have', 'reason' => 'present_simple'],
                            ['value' => 'has', 'reason' => 'third_person'],
                            ['value' => 'would have', 'reason' => 'result_form'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_would_be_able',
                        'subject' => 'we',
                        'verb' => 'drive',
                        'verb_hint' => 'be able to',
                        'answer' => 'would be able',
                        'options' => [
                            ['value' => 'would be able', 'reason' => 'correct'],
                            ['value' => 'will be able', 'reason' => 'first_conditional'],
                            ['value' => 'are able', 'reason' => 'present_simple'],
                            ['value' => 'would have been able', 'reason' => 'third_conditional'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'If they {a1} perfect English, they {a2} a good job.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_simple',
                        'subject' => 'they',
                        'verb' => 'speak',
                        'verb_hint' => 'speak',
                        'answer' => 'spoke',
                        'options' => [
                            ['value' => 'spoke', 'reason' => 'correct'],
                            ['value' => 'speak', 'reason' => 'present_simple'],
                            ['value' => 'speaks', 'reason' => 'third_person'],
                            ['value' => 'had spoken', 'reason' => 'past_perfect'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_would_simple',
                        'subject' => 'they',
                        'verb' => 'have',
                        'verb_hint' => 'have',
                        'answer' => 'would have',
                        'options' => [
                            ['value' => 'would have', 'reason' => 'correct'],
                            ['value' => 'will have', 'reason' => 'first_conditional'],
                            ['value' => 'have', 'reason' => 'present_simple'],
                            ['value' => 'would be having', 'reason' => 'continuous'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'If I {a1} in Mexico, I {a2} Spanish.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_simple',
                        'subject' => 'I',
                        'verb' => 'live',
                        'verb_hint' => 'live',
                        'answer' => 'lived',
                        'options' => [
                            ['value' => 'lived', 'reason' => 'correct'],
                            ['value' => 'live', 'reason' => 'present_simple'],
                            ['value' => 'lives', 'reason' => 'third_person'],
                            ['value' => 'had lived', 'reason' => 'past_perfect'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_would_simple',
                        'subject' => 'I',
                        'verb' => 'speak',
                        'verb_hint' => 'speak',
                        'answer' => 'would speak',
                        'options' => [
                            ['value' => 'would speak', 'reason' => 'correct'],
                            ['value' => 'will speak', 'reason' => 'first_conditional'],
                            ['value' => 'speak', 'reason' => 'present_simple'],
                            ['value' => 'would be speaking', 'reason' => 'continuous'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'If she {a1} to the party, she {a2} some friends.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_simple',
                        'subject' => 'she',
                        'verb' => 'go',
                        'verb_hint' => 'go',
                        'answer' => 'went',
                        'options' => [
                            ['value' => 'went', 'reason' => 'correct'],
                            ['value' => 'go', 'reason' => 'present_simple'],
                            ['value' => 'goes', 'reason' => 'third_person'],
                            ['value' => 'had gone', 'reason' => 'past_perfect'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_would_simple',
                        'subject' => 'she',
                        'verb' => 'meet',
                        'verb_hint' => 'meet',
                        'answer' => 'would meet',
                        'options' => [
                            ['value' => 'would meet', 'reason' => 'correct'],
                            ['value' => 'will meet', 'reason' => 'first_conditional'],
                            ['value' => 'meets', 'reason' => 'third_person'],
                            ['value' => 'would be meeting', 'reason' => 'continuous'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'If they {a1} more, they {a2} more intelligent.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_simple',
                        'subject' => 'they',
                        'verb' => 'study',
                        'verb_hint' => 'study',
                        'answer' => 'studied',
                        'options' => [
                            ['value' => 'studied', 'reason' => 'correct'],
                            ['value' => 'study', 'reason' => 'present_simple'],
                            ['value' => 'studies', 'reason' => 'third_person'],
                            ['value' => 'had studied', 'reason' => 'past_perfect'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_would_simple',
                        'subject' => 'they',
                        'verb' => 'be',
                        'verb_hint' => 'be',
                        'answer' => 'would be',
                        'options' => [
                            ['value' => 'would be', 'reason' => 'correct'],
                            ['value' => 'will be', 'reason' => 'first_conditional'],
                            ['value' => 'are', 'reason' => 'present_simple'],
                            ['value' => 'were', 'reason' => 'missing_would'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'If we {a1} a house, we {a2} a bigger one.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_simple',
                        'subject' => 'we',
                        'verb' => 'buy',
                        'verb_hint' => 'buy',
                        'answer' => 'bought',
                        'options' => [
                            ['value' => 'bought', 'reason' => 'correct'],
                            ['value' => 'buy', 'reason' => 'present_simple'],
                            ['value' => 'buys', 'reason' => 'third_person'],
                            ['value' => 'had bought', 'reason' => 'past_perfect'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_would_simple',
                        'subject' => 'we',
                        'verb' => 'buy',
                        'verb_hint' => 'buy',
                        'answer' => 'would buy',
                        'options' => [
                            ['value' => 'would buy', 'reason' => 'correct'],
                            ['value' => 'will buy', 'reason' => 'first_conditional'],
                            ['value' => 'would keep buying', 'reason' => 'extra_word'],
                            ['value' => 'would be buying', 'reason' => 'continuous'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'If my parents {a1} to dinner, we {a2} a good time.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_simple',
                        'subject' => 'my parents',
                        'verb' => 'come',
                        'verb_hint' => 'come',
                        'answer' => 'came',
                        'options' => [
                            ['value' => 'came', 'reason' => 'correct'],
                            ['value' => 'come', 'reason' => 'present_simple'],
                            ['value' => 'comes', 'reason' => 'third_person'],
                            ['value' => 'had come', 'reason' => 'past_perfect'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_would_simple',
                        'subject' => 'we',
                        'verb' => 'have',
                        'verb_hint' => 'have',
                        'answer' => 'would have',
                        'options' => [
                            ['value' => 'would have', 'reason' => 'correct'],
                            ['value' => 'will have', 'reason' => 'first_conditional'],
                            ['value' => 'have', 'reason' => 'present_simple'],
                            ['value' => 'would be having', 'reason' => 'continuous'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'If he {a1} his number, he {a2} her.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_simple',
                        'subject' => 'he',
                        'verb' => 'know',
                        'verb_hint' => 'know',
                        'answer' => 'knew',
                        'options' => [
                            ['value' => 'knew', 'reason' => 'correct'],
                            ['value' => 'know', 'reason' => 'present_simple'],
                            ['value' => 'knows', 'reason' => 'third_person'],
                            ['value' => 'had known', 'reason' => 'past_perfect'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_would_simple',
                        'subject' => 'he',
                        'verb' => 'phone',
                        'verb_hint' => 'phone',
                        'answer' => 'would phone',
                        'options' => [
                            ['value' => 'would phone', 'reason' => 'correct'],
                            ['value' => 'will phone', 'reason' => 'first_conditional'],
                            ['value' => 'phones', 'reason' => 'third_person'],
                            ['value' => 'would be phoning', 'reason' => 'continuous'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'I {a1} to Spain on holiday if they {a2} cheap flights.',
                'markers' => [
                    'a1' => [
                        'type' => 'result_would_simple',
                        'subject' => 'I',
                        'verb' => 'go',
                        'verb_hint' => 'go',
                        'answer' => 'would go',
                        'options' => [
                            ['value' => 'would go', 'reason' => 'correct'],
                            ['value' => 'go', 'reason' => 'present_simple'],
                            ['value' => 'will go', 'reason' => 'first_conditional'],
                            ['value' => 'went', 'reason' => 'past_simple'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'if_past_simple',
                        'subject' => 'they',
                        'verb' => 'have',
                        'verb_hint' => 'have',
                        'answer' => 'had',
                        'options' => [
                            ['value' => 'had', 'reason' => 'correct'],
                            ['value' => 'have', 'reason' => 'present_simple'],
                            ['value' => 'would have', 'reason' => 'result_form'],
                            ['value' => 'will have', 'reason' => 'first_conditional'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'They {a1} to the party if they {a2} too much homework.',
                'markers' => [
                    'a1' => [
                        'type' => 'result_would_negative',
                        'subject' => 'they',
                        'verb' => 'go',
                        'verb_hint' => 'not go',
                        'answer' => "wouldn't go",
                        'options' => [
                            ['value' => "wouldn't go", 'reason' => 'correct'],
                            ['value' => "won't go", 'reason' => 'first_conditional'],
                            ['value' => "don't go", 'reason' => 'present_simple'],
                            ['value' => "wouldn't be going", 'reason' => 'continuous'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'if_past_simple',
                        'subject' => 'they',
                        'verb' => 'have',
                        'verb_hint' => 'have',
                        'answer' => 'had',
                        'options' => [
                            ['value' => 'had', 'reason' => 'correct'],
                            ['value' => 'have', 'reason' => 'present_simple'],
                            ['value' => 'would have', 'reason' => 'result_form'],
                            ['value' => 'will have', 'reason' => 'first_conditional'],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function buildHint(string $type, array $context, string $example): string
    {
        $template = $this->hintTemplates[$type] ?? "Час: Second Conditional.
Пояснення: Оберіть форму, що відповідає правилу.
Приклад: *%example%*";

        return $this->renderTemplate($template, [
            'option' => '',
            'subject' => $context['subject'] ?? '',
            'verb' => $context['verb'] ?? '',
            'example' => $example,
        ]);
    }

    private function buildExplanation(string $type, string $reason, string $option, array $context, string $example): string
    {
        $templates = $this->explanationTemplates[$type] ?? [];
        $template = $templates[$reason] ?? ($templates['default'] ?? "❌ Неправильний варіант.
Приклад: *%example%*");

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

        $normalized = mb_strtolower($subject, 'UTF-8');

        return match ($normalized) {
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
}
