<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class ThirdConditionalPracticeV2Seeder extends QuestionSeeder
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
        'if_past_perfect' => "Час: Third Conditional.\nФормула: **if + had + V3** (або **had not + V3** для заперечення).\nПояснення: умовна частина описує нереальний минулий результат, тому потрібен Past Perfect від «%verb%».\nПриклад: *%example%*",
        'result_would_have' => "Час: Third Conditional.\nФормула: **would have + V3**.\nПояснення: головна частина показує результат у минулому, тому використовуємо would have + третю форму дієслова «%verb%».\nПриклад: *%example%*",
        'result_would_not_have' => "Час: Third Conditional.\nФормула: **would not have + V3** (wouldn't have + V3).\nПояснення: заперечний результат утворюємо через would not have + третю форму дієслова «%verb%».\nПриклад: *%example%*",
    ];

    private array $explanationTemplates = [
        'if_past_perfect' => [
            'correct' => "✅ «%option%» правильно, бо в if-клаузі Third Conditional для %subject% потрібна форма **had + V3** (або had not + V3).\nПриклад: *%example%*",
            'past_simple' => "❌ «%option%» — Past Simple. Умовна частина Third Conditional вимагає **had + V3**, щоб показати попередню дію.\nПриклад: *%example%*",
            'present_perfect' => "❌ «%option%» — Present Perfect. Потрібен Past Perfect із had + V3 для минулої умови.\nПриклад: *%example%*",
            'result_clause' => "❌ «%option%» містить would, а це форма для головної частини. В if-клаузі слід ужити **had + V3**.\nПриклад: *%example%*",
            'present_simple' => "❌ «%option%» — Present Simple. Третій умовний описує минуле й потребує **had + V3**.\nПриклад: *%example%*",
            'continuous' => "❌ «%option%» — форма Continuous. Для умовної частини Third Conditional необхідний простий Past Perfect.\nПриклад: *%example%*",
            'default' => "❌ Неправильна форма для if-клауза Third Conditional.\nПриклад: *%example%*",
        ],
        'result_would_have' => [
            'correct' => "✅ «%option%» правильно, бо результат Third Conditional будується як would have + V3 для %subject%.\nПриклад: *%example%*",
            'first_conditional' => "❌ «%option%» — це модель First Conditional з will. Нам потрібна форма would have + V3 про минулий результат.\nПриклад: *%example%*",
            'past_simple' => "❌ «%option%» — Past Simple. У головній частині Third Conditional слід ужити would have + V3.\nПриклад: *%example%*",
            'second_conditional' => "❌ «%option%» — Second Conditional (would + V1) для теперішніх/майбутніх гіпотез. Тут потрібне would have + V3.\nПриклад: *%example%*",
            'present_perfect' => "❌ «%option%» — Present Perfect. Для умовного результату в минулому треба would have + V3.\nПриклад: *%example%*",
            'continuous' => "❌ «%option%» — форма Continuous. Стандартний результат третього умовного – would have + V3.\nПриклад: *%example%*",
            'default' => "❌ Форма не відповідає результату Third Conditional.\nПриклад: *%example%*",
        ],
        'result_would_not_have' => [
            'correct' => "✅ «%option%» правильно, бо заперечення третього умовного утворюємо як would not have + V3.\nПриклад: *%example%*",
            'first_conditional' => "❌ «%option%» — це Future/First Conditional із will not. Для минулого результату потрібне would not have + V3.\nПриклад: *%example%*",
            'past_simple' => "❌ «%option%» — Past Simple. Третій умовний вимагає would not have + V3.\nПриклад: *%example%*",
            'missing_have' => "❌ «%option%» пропускає have після would not, тож не утворює Third Conditional.\nПриклад: *%example%*",
            'present_simple' => "❌ «%option%» — Present Simple. Результат третього умовного описує минуле через would not have + V3.\nПриклад: *%example%*",
            'default' => "❌ Неправильна форма для заперечного результату Third Conditional.\nПриклад: *%example%*",
        ],
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Conditionals'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Custom: Third Conditional Practice V2'])->id;

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Third Conditional Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Third Conditional Sentence Completion'],
            ['category' => 'English Grammar Detail']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'Third Conditional Sentences'],
            ['category' => 'English Grammar Structure']
        )->id;

        $tenseTagId = Tag::firstOrCreate(
            ['name' => 'Third Conditional'],
            ['category' => 'Tenses']
        )->id;

        $items = [];
        $meta = [];

        foreach ($this->questionEntries() as $index => $entry) {
            $answersMap = [];
            $verbHints = [];
            foreach ($entry['markers'] as $marker => $markerData) {
                $answersMap[$marker] = $markerData['answer'];
                if (isset($markerData['verb_hint'])) {
                    $verbHints[$marker] = $markerData['verb_hint'];
                }
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
                'level' => 'B1',
                'question' => 'If I {a1} my wallet, I {a2} at the opera on time.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'I',
                        'verb' => 'lose',
                        'verb_hint' => 'not lose',
                        'answer' => "hadn't lost",
                        'options' => [
                            ['value' => "hadn't lost", 'reason' => 'correct'],
                            ['value' => "didn't lose", 'reason' => 'past_simple'],
                            ['value' => "haven't lost", 'reason' => 'present_perfect'],
                            ['value' => "wouldn't lose", 'reason' => 'result_clause'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_would_have',
                        'subject' => 'I',
                        'verb' => 'arrive',
                        'verb_hint' => 'arrive',
                        'answer' => 'would have arrived',
                        'options' => [
                            ['value' => 'would have arrived', 'reason' => 'correct'],
                            ['value' => 'will arrive', 'reason' => 'first_conditional'],
                            ['value' => 'arrived', 'reason' => 'past_simple'],
                            ['value' => 'would arrive', 'reason' => 'second_conditional'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'Sally {a1} so upset if she {a2} the driving test.',
                'markers' => [
                    'a1' => [
                        'type' => 'result_would_not_have',
                        'subject' => 'Sally',
                        'verb' => 'be',
                        'verb_hint' => 'not be',
                        'answer' => "wouldn't have been",
                        'options' => [
                            ['value' => "wouldn't have been", 'reason' => 'correct'],
                            ['value' => "won't be", 'reason' => 'first_conditional'],
                            ['value' => 'was not', 'reason' => 'past_simple'],
                            ['value' => "wouldn't be", 'reason' => 'missing_have'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'she',
                        'verb' => 'pass',
                        'verb_hint' => 'pass',
                        'answer' => 'had passed',
                        'options' => [
                            ['value' => 'had passed', 'reason' => 'correct'],
                            ['value' => 'passed', 'reason' => 'past_simple'],
                            ['value' => 'has passed', 'reason' => 'present_perfect'],
                            ['value' => 'would pass', 'reason' => 'result_clause'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'If Mary {a1} hungry, she {a2} something to eat.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'Mary',
                        'verb' => 'be',
                        'verb_hint' => 'be',
                        'answer' => 'had been',
                        'options' => [
                            ['value' => 'had been', 'reason' => 'correct'],
                            ['value' => 'was', 'reason' => 'past_simple'],
                            ['value' => 'has been', 'reason' => 'present_perfect'],
                            ['value' => 'would have been', 'reason' => 'result_clause'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_would_have',
                        'subject' => 'she',
                        'verb' => 'have',
                        'verb_hint' => 'have',
                        'answer' => 'would have had',
                        'options' => [
                            ['value' => 'would have had', 'reason' => 'correct'],
                            ['value' => 'will have', 'reason' => 'first_conditional'],
                            ['value' => 'had', 'reason' => 'past_simple'],
                            ['value' => 'would have', 'reason' => 'second_conditional'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'My parents {a1} the house if they {a2} the money.',
                'markers' => [
                    'a1' => [
                        'type' => 'result_would_not_have',
                        'subject' => 'my parents',
                        'verb' => 'buy',
                        'verb_hint' => 'not buy',
                        'answer' => "wouldn't have bought",
                        'options' => [
                            ['value' => "wouldn't have bought", 'reason' => 'correct'],
                            ['value' => "won't buy", 'reason' => 'first_conditional'],
                            ['value' => 'did not buy', 'reason' => 'past_simple'],
                            ['value' => "wouldn't buy", 'reason' => 'missing_have'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'they',
                        'verb' => 'have',
                        'verb_hint' => 'not have',
                        'answer' => "hadn't had",
                        'options' => [
                            ['value' => "hadn't had", 'reason' => 'correct'],
                            ['value' => "didn't have", 'reason' => 'past_simple'],
                            ['value' => "haven't had", 'reason' => 'present_perfect'],
                            ['value' => "wouldn't have", 'reason' => 'result_clause'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'Peter {a1} her birthday if I {a2} him.',
                'markers' => [
                    'a1' => [
                        'type' => 'result_would_have',
                        'subject' => 'Peter',
                        'verb' => 'forget',
                        'verb_hint' => 'forget',
                        'answer' => 'would have forgotten',
                        'options' => [
                            ['value' => 'would have forgotten', 'reason' => 'correct'],
                            ['value' => 'will forget', 'reason' => 'first_conditional'],
                            ['value' => 'forgot', 'reason' => 'past_simple'],
                            ['value' => 'would forget', 'reason' => 'second_conditional'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'I',
                        'verb' => 'remind',
                        'verb_hint' => 'not remind',
                        'answer' => "hadn't reminded",
                        'options' => [
                            ['value' => "hadn't reminded", 'reason' => 'correct'],
                            ['value' => "didn't remind", 'reason' => 'past_simple'],
                            ['value' => "haven't reminded", 'reason' => 'present_perfect'],
                            ['value' => "wouldn't remind", 'reason' => 'result_clause'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'I {a1} you if I {a2} your number.',
                'markers' => [
                    'a1' => [
                        'type' => 'result_would_have',
                        'subject' => 'I',
                        'verb' => 'phone',
                        'verb_hint' => 'phone',
                        'answer' => 'would have phoned',
                        'options' => [
                            ['value' => 'would have phoned', 'reason' => 'correct'],
                            ['value' => 'will phone', 'reason' => 'first_conditional'],
                            ['value' => 'phoned', 'reason' => 'past_simple'],
                            ['value' => 'would phone', 'reason' => 'second_conditional'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'I',
                        'verb' => 'have',
                        'verb_hint' => 'have',
                        'answer' => 'had had',
                        'options' => [
                            ['value' => 'had had', 'reason' => 'correct'],
                            ['value' => 'had', 'reason' => 'past_simple'],
                            ['value' => 'have had', 'reason' => 'present_perfect'],
                            ['value' => 'would have had', 'reason' => 'result_clause'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'Tim {a1} a taxi if he {a2} his wallet at home.',
                'markers' => [
                    'a1' => [
                        'type' => 'result_would_have',
                        'subject' => 'Tim',
                        'verb' => 'get',
                        'verb_hint' => 'get',
                        'answer' => 'would have got',
                        'options' => [
                            ['value' => 'would have got', 'reason' => 'correct'],
                            ['value' => 'will get', 'reason' => 'first_conditional'],
                            ['value' => 'got', 'reason' => 'past_simple'],
                            ['value' => 'would get', 'reason' => 'second_conditional'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'he',
                        'verb' => 'forget',
                        'verb_hint' => 'not forget',
                        'answer' => "hadn't forgotten",
                        'options' => [
                            ['value' => "hadn't forgotten", 'reason' => 'correct'],
                            ['value' => "didn't forget", 'reason' => 'past_simple'],
                            ['value' => "hasn't forgotten", 'reason' => 'present_perfect'],
                            ['value' => "wouldn't forget", 'reason' => 'result_clause'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'If the weather {a1} nicer, we {a2} to the beach.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'the weather',
                        'verb' => 'be',
                        'verb_hint' => 'be',
                        'answer' => 'had been',
                        'options' => [
                            ['value' => 'had been', 'reason' => 'correct'],
                            ['value' => 'was', 'reason' => 'past_simple'],
                            ['value' => 'has been', 'reason' => 'present_perfect'],
                            ['value' => 'would have been', 'reason' => 'result_clause'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_would_have',
                        'subject' => 'we',
                        'verb' => 'go',
                        'verb_hint' => 'go',
                        'answer' => 'would have gone',
                        'options' => [
                            ['value' => 'would have gone', 'reason' => 'correct'],
                            ['value' => 'will go', 'reason' => 'first_conditional'],
                            ['value' => 'went', 'reason' => 'past_simple'],
                            ['value' => 'would go', 'reason' => 'second_conditional'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'I {a1} my golden ring if I {a2} it to my niece.',
                'markers' => [
                    'a1' => [
                        'type' => 'result_would_have',
                        'subject' => 'I',
                        'verb' => 'sell',
                        'verb_hint' => 'sell',
                        'answer' => 'would have sold',
                        'options' => [
                            ['value' => 'would have sold', 'reason' => 'correct'],
                            ['value' => 'will sell', 'reason' => 'first_conditional'],
                            ['value' => 'sold', 'reason' => 'past_simple'],
                            ['value' => 'would sell', 'reason' => 'second_conditional'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'I',
                        'verb' => 'give',
                        'verb_hint' => 'not give',
                        'answer' => "hadn't given",
                        'options' => [
                            ['value' => "hadn't given", 'reason' => 'correct'],
                            ['value' => "didn't give", 'reason' => 'past_simple'],
                            ['value' => "haven't given", 'reason' => 'present_perfect'],
                            ['value' => "wouldn't give", 'reason' => 'result_clause'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'If Lisa {a1} so tired, she {a2} to the concert last night.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'Lisa',
                        'verb' => 'be',
                        'verb_hint' => 'not be',
                        'answer' => "hadn't been",
                        'options' => [
                            ['value' => "hadn't been", 'reason' => 'correct'],
                            ['value' => "wasn't", 'reason' => 'past_simple'],
                            ['value' => "hasn't been", 'reason' => 'present_perfect'],
                            ['value' => "wouldn't have been", 'reason' => 'result_clause'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_would_have',
                        'subject' => 'she',
                        'verb' => 'go',
                        'verb_hint' => 'go',
                        'answer' => 'would have gone',
                        'options' => [
                            ['value' => 'would have gone', 'reason' => 'correct'],
                            ['value' => 'will go', 'reason' => 'first_conditional'],
                            ['value' => 'went', 'reason' => 'past_simple'],
                            ['value' => 'would go', 'reason' => 'second_conditional'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'I {a1} the contest if I {a2} all the answers.',
                'markers' => [
                    'a1' => [
                        'type' => 'result_would_have',
                        'subject' => 'I',
                        'verb' => 'win',
                        'verb_hint' => 'win',
                        'answer' => 'would have won',
                        'options' => [
                            ['value' => 'would have won', 'reason' => 'correct'],
                            ['value' => 'will win', 'reason' => 'first_conditional'],
                            ['value' => 'won', 'reason' => 'past_simple'],
                            ['value' => 'would win', 'reason' => 'second_conditional'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'I',
                        'verb' => 'know',
                        'verb_hint' => 'know',
                        'answer' => 'had known',
                        'options' => [
                            ['value' => 'had known', 'reason' => 'correct'],
                            ['value' => 'knew', 'reason' => 'past_simple'],
                            ['value' => 'have known', 'reason' => 'present_perfect'],
                            ['value' => 'would know', 'reason' => 'result_clause'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'You {a1} that film if you {a2} with us.',
                'markers' => [
                    'a1' => [
                        'type' => 'result_would_have',
                        'subject' => 'you',
                        'verb' => 'see',
                        'verb_hint' => 'see',
                        'answer' => 'would have seen',
                        'options' => [
                            ['value' => 'would have seen', 'reason' => 'correct'],
                            ['value' => 'will see', 'reason' => 'first_conditional'],
                            ['value' => 'saw', 'reason' => 'past_simple'],
                            ['value' => 'would see', 'reason' => 'second_conditional'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'you',
                        'verb' => 'come',
                        'verb_hint' => 'come',
                        'answer' => 'had come',
                        'options' => [
                            ['value' => 'had come', 'reason' => 'correct'],
                            ['value' => 'came', 'reason' => 'past_simple'],
                            ['value' => 'have come', 'reason' => 'present_perfect'],
                            ['value' => 'would come', 'reason' => 'result_clause'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'If he {a1} more free time, he {a2} his homework yesterday.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'he',
                        'verb' => 'have',
                        'verb_hint' => 'have',
                        'answer' => 'had had',
                        'options' => [
                            ['value' => 'had had', 'reason' => 'correct'],
                            ['value' => 'had', 'reason' => 'past_simple'],
                            ['value' => 'has had', 'reason' => 'present_perfect'],
                            ['value' => 'would have had', 'reason' => 'result_clause'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_would_have',
                        'subject' => 'he',
                        'verb' => 'do',
                        'verb_hint' => 'do',
                        'answer' => 'would have done',
                        'options' => [
                            ['value' => 'would have done', 'reason' => 'correct'],
                            ['value' => 'will do', 'reason' => 'first_conditional'],
                            ['value' => 'did', 'reason' => 'past_simple'],
                            ['value' => 'would do', 'reason' => 'second_conditional'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'If Mary {a1} the train, she {a2} Tom.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'Mary',
                        'verb' => 'miss',
                        'verb_hint' => 'not miss',
                        'answer' => "hadn't missed",
                        'options' => [
                            ['value' => "hadn't missed", 'reason' => 'correct'],
                            ['value' => "didn't miss", 'reason' => 'past_simple'],
                            ['value' => "hasn't missed", 'reason' => 'present_perfect'],
                            ['value' => "wouldn't miss", 'reason' => 'result_clause'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_would_have',
                        'subject' => 'she',
                        'verb' => 'see',
                        'verb_hint' => 'see',
                        'answer' => 'would have seen',
                        'options' => [
                            ['value' => 'would have seen', 'reason' => 'correct'],
                            ['value' => 'will see', 'reason' => 'first_conditional'],
                            ['value' => 'saw', 'reason' => 'past_simple'],
                            ['value' => 'would see', 'reason' => 'second_conditional'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'My sister {a1} happier if she {a2} her first love.',
                'markers' => [
                    'a1' => [
                        'type' => 'result_would_have',
                        'subject' => 'my sister',
                        'verb' => 'be',
                        'verb_hint' => 'be',
                        'answer' => 'would have been',
                        'options' => [
                            ['value' => 'would have been', 'reason' => 'correct'],
                            ['value' => 'will be', 'reason' => 'first_conditional'],
                            ['value' => 'was', 'reason' => 'past_simple'],
                            ['value' => 'would be', 'reason' => 'second_conditional'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'she',
                        'verb' => 'marry',
                        'verb_hint' => 'marry',
                        'answer' => 'had married',
                        'options' => [
                            ['value' => 'had married', 'reason' => 'correct'],
                            ['value' => 'married', 'reason' => 'past_simple'],
                            ['value' => 'has married', 'reason' => 'present_perfect'],
                            ['value' => 'would marry', 'reason' => 'result_clause'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'If Tony {a1} some money, he {a2} to Paris last summer.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'Tony',
                        'verb' => 'save',
                        'verb_hint' => 'save',
                        'answer' => 'had saved',
                        'options' => [
                            ['value' => 'had saved', 'reason' => 'correct'],
                            ['value' => 'saved', 'reason' => 'past_simple'],
                            ['value' => 'has saved', 'reason' => 'present_perfect'],
                            ['value' => 'would save', 'reason' => 'result_clause'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_would_have',
                        'subject' => 'he',
                        'verb' => 'travel',
                        'verb_hint' => 'travel',
                        'answer' => 'would have travelled',
                        'options' => [
                            ['value' => 'would have travelled', 'reason' => 'correct'],
                            ['value' => 'will travel', 'reason' => 'first_conditional'],
                            ['value' => 'travelled', 'reason' => 'past_simple'],
                            ['value' => 'would travel', 'reason' => 'second_conditional'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'You {a1} Chinese if you {a2} longer in China.',
                'markers' => [
                    'a1' => [
                        'type' => 'result_would_have',
                        'subject' => 'you',
                        'verb' => 'learn',
                        'verb_hint' => 'learn',
                        'answer' => 'would have learnt',
                        'options' => [
                            ['value' => 'would have learnt', 'reason' => 'correct'],
                            ['value' => 'will learn', 'reason' => 'first_conditional'],
                            ['value' => 'learnt', 'reason' => 'past_simple'],
                            ['value' => 'would learn', 'reason' => 'second_conditional'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'you',
                        'verb' => 'stay',
                        'verb_hint' => 'stay',
                        'answer' => 'had stayed',
                        'options' => [
                            ['value' => 'had stayed', 'reason' => 'correct'],
                            ['value' => 'stayed', 'reason' => 'past_simple'],
                            ['value' => 'have stayed', 'reason' => 'present_perfect'],
                            ['value' => 'would stay', 'reason' => 'result_clause'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'If I {a1} my aunt, I {a2} her the news.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'I',
                        'verb' => 'see',
                        'verb_hint' => 'see',
                        'answer' => 'had seen',
                        'options' => [
                            ['value' => 'had seen', 'reason' => 'correct'],
                            ['value' => 'saw', 'reason' => 'past_simple'],
                            ['value' => 'have seen', 'reason' => 'present_perfect'],
                            ['value' => 'would see', 'reason' => 'result_clause'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_would_have',
                        'subject' => 'I',
                        'verb' => 'tell',
                        'verb_hint' => 'tell',
                        'answer' => 'would have told',
                        'options' => [
                            ['value' => 'would have told', 'reason' => 'correct'],
                            ['value' => 'will tell', 'reason' => 'first_conditional'],
                            ['value' => 'told', 'reason' => 'past_simple'],
                            ['value' => 'would tell', 'reason' => 'second_conditional'],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function buildHint(string $type, array $context, string $example): string
    {
        $template = $this->hintTemplates[$type] ?? "Час: Third Conditional.\nПояснення: оберіть форму, що відповідає правилу had + V3 та would have + V3.\nПриклад: *%example%*";

        return $this->renderTemplate($template, [
            'subject' => $context['subject'] ?? '',
            'verb' => $context['verb'] ?? '',
            'example' => $example,
            'option' => '',
        ]);
    }

    private function buildExplanation(string $type, string $reason, string $option, array $context, string $example): string
    {
        $templates = $this->explanationTemplates[$type] ?? [];
        $template = $templates[$reason] ?? ($templates['default'] ?? "❌ Неправильна форма.\nПриклад: *%example%*");

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
