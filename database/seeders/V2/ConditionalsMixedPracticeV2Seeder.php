<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class ConditionalsMixedPracticeV2Seeder extends QuestionSeeder
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
        'if_present_simple' => "Час: First Conditional.\nФормула: **if + Present Simple** (для заперечення використовуємо do/does + not).\nПояснення: умовна частина описує можливу ситуацію, тому дієслово «%verb%» ставимо в Present Simple для %subject%.\nПриклад: *%example%*",
        'result_will' => "Час: First Conditional.\nФормула: **will + base verb**.\nПояснення: результат у First Conditional виражаємо через will + початкову форму дієслова «%verb%».\nПриклад: *%example%*",
        'result_will_negative' => "Час: First Conditional.\nФормула: **will not / won't + base verb**.\nПояснення: заперечення у головній частині First Conditional утворюємо через will not (won't) + базову форму «%verb%».\nПриклад: *%example%*",
        'result_will_question' => "Час: First Conditional.\nФормула: **will + subject + base verb** у питальному реченні.\nПояснення: у запитанні результату потрібно використати will перед підметом та базову форму «%verb%».\nПриклад: *%example%*",
        'result_would' => "Час: Second Conditional.\nФормула: **would + base verb**.\nПояснення: у другому умовному результат виражаємо через would + початкову форму дієслова «%verb%».\nПриклад: *%example%*",
        'if_past_simple_subjunctive' => "Час: Second Conditional.\nФормула: **if + Past Simple** (для to be використовуємо were з усіма особами).\nПояснення: умовна частина описує нереальну теперішню ситуацію, тож дієслово «%verb%» ставимо в Past Simple / were.\nПриклад: *%example%*",
        'if_past_perfect' => "Час: Third Conditional.\nФормула: **if + had + V3** (або **had not + V3** для заперечення).\nПояснення: умовна частина описує нереальний минулий результат, тому потрібен Past Perfect від «%verb%».\nПриклад: *%example%*",
        'result_would_have' => "Час: Third Conditional.\nФормула: **would have + V3**.\nПояснення: головна частина показує результат у минулому, тому використовуємо would have + третю форму дієслова «%verb%».\nПриклад: *%example%*",
        'result_would_not_have' => "Час: Third Conditional.\nФормула: **would not have + V3** (wouldn't have + V3).\nПояснення: заперечний результат утворюємо через would not have + третю форму дієслова «%verb%».\nПриклад: *%example%*",
    ];

    private array $explanationTemplates = [
        'if_present_simple' => [
            'correct' => "✅ «%option%» правильно, бо в if-клаузі First Conditional для %subject% використовуємо Present Simple дієслова «%verb%».\nПриклад: *%example%*",
            'past_simple' => "❌ «%option%» — Past Simple. У First Conditional в if-клаузі потрібен Present Simple.\nПриклад: *%example%*",
            'past_perfect' => "❌ «%option%» — Past Perfect. Для можливої умови в теперішньому вживаємо Present Simple.\nПриклад: *%example%*",
            'modal_future' => "❌ «%option%» містить will, але will уживаємо лише в головній частині, не після if.\nПриклад: *%example%*",
            'present_continuous' => "❌ «%option%» — форма Continuous, а First Conditional потребує простого Present Simple.\nПриклад: *%example%*",
            'present_perfect' => "❌ «%option%» — Present Perfect. Умовна частина First Conditional вимагає простого Present Simple.\nПриклад: *%example%*",
        ],
        'result_will' => [
            'correct' => "✅ «%option%» правильно, бо результат First Conditional утворюємо як will + базова форма дієслова «%verb%».\nПриклад: *%example%*",
            'second_conditional' => "❌ «%option%» — модель Second Conditional з would. Нам потрібне will для реальної майбутньої ситуації.\nПриклад: *%example%*",
            'third_conditional' => "❌ «%option%» належить до Third Conditional (would have). У First Conditional використовуємо will + V1.\nПриклад: *%example%*",
            'present_simple' => "❌ «%option%» — Present Simple. Результат First Conditional має містити will.\nПриклад: *%example%*",
        ],
        'result_will_negative' => [
            'correct' => "✅ «%option%» правильно, бо заперечення у головній частині First Conditional будується як will not / won't + базова форма «%verb%».\nПриклад: *%example%*",
            'second_conditional' => "❌ «%option%» — Second Conditional з would. Для реальної майбутньої ситуації потрібне will not / won't.\nПриклад: *%example%*",
            'third_conditional' => "❌ «%option%» — Third Conditional (would not have). У First Conditional використовуємо просту форму will not + V1.\nПриклад: *%example%*",
            'present_simple' => "❌ «%option%» — Present Simple. Результат First Conditional має містити will (not).\nПриклад: *%example%*",
        ],
        'result_will_question' => [
            'correct' => "✅ «%option%» правильно, бо у питальній формі First Conditional ставимо will перед підметом «%subject%» та вживаємо базову форму «%verb%».\nПриклад: *%example%*",
            'second_conditional' => "❌ «%option%» — Second Conditional з would. Для ймовірного майбутнього запитання потрібне will.\nПриклад: *%example%*",
            'third_conditional' => "❌ «%option%» — Third Conditional (would have). У First Conditional питання будуємо з will.\nПриклад: *%example%*",
            'present_simple' => "❌ «%option%» — Present Simple. Питання результату First Conditional потребує will + підмет.\nПриклад: *%example%*",
        ],
        'result_would' => [
            'correct' => "✅ «%option%» правильно, бо у Second Conditional результат виражаємо через would + базову форму «%verb%».\nПриклад: *%example%*",
            'first_conditional' => "❌ «%option%» використовує will, що характерно для First Conditional. Тут потрібне would.\nПриклад: *%example%*",
            'third_conditional' => "❌ «%option%» — Third Conditional (would have). У Second Conditional беремо would + V1.\nПриклад: *%example%*",
            'present_simple' => "❌ «%option%» — Present Simple. Результат Second Conditional формується за допомогою would.\nПриклад: *%example%*",
        ],
        'if_past_simple_subjunctive' => [
            'correct' => "✅ «%option%» правильно, бо в Second Conditional для дієслова to be після if використовуємо were з усіма особами.\nПриклад: *%example%*",
            'singular_agreement' => "❌ «%option%» виглядає граматично, але в гіпотетичному if-реченні потрібно were навіть із I/he/she/it.\nПриклад: *%example%*",
            'past_perfect' => "❌ «%option%» — Past Perfect. Для теперішньої нереальної умови потрібен Past Simple (were).\nПриклад: *%example%*",
            'present_simple' => "❌ «%option%» — Present Simple. Другий умовний вимагає Past Simple (were).\nПриклад: *%example%*",
        ],
        'if_past_perfect' => [
            'correct' => "✅ «%option%» правильно, бо в if-клаузі Third Conditional для %subject% потрібна форма **had + V3** (або had not + V3).\nПриклад: *%example%*",
            'past_simple' => "❌ «%option%» — Past Simple. Умовна частина Third Conditional вимагає **had + V3**, щоб показати попередню дію.\nПриклад: *%example%*",
            'present_perfect' => "❌ «%option%» — Present Perfect. Потрібен Past Perfect із had + V3 для минулої умови.\nПриклад: *%example%*",
            'result_clause' => "❌ «%option%» містить would, а це форма для головної частини. В if-клаузі слід ужити **had + V3**.\nПриклад: *%example%*",
            'present_simple' => "❌ «%option%» — Present Simple. Третій умовний описує минуле й потребує **had + V3**.\nПриклад: *%example%*",
            'continuous' => "❌ «%option%» — форма Continuous. Для умовної частини Third Conditional необхідний простий Past Perfect.\nПриклад: *%example%*",
        ],
        'result_would_have' => [
            'correct' => "✅ «%option%» правильно, бо результат Third Conditional будується як would have + V3 для %subject%.\nПриклад: *%example%*",
            'first_conditional' => "❌ «%option%» — це модель First Conditional з will. Нам потрібна форма would have + V3 про минулий результат.\nПриклад: *%example%*",
            'past_simple' => "❌ «%option%» — Past Simple. У головній частині Third Conditional слід ужити would have + V3.\nПриклад: *%example%*",
            'second_conditional' => "❌ «%option%» — Second Conditional (would + V1) для теперішніх/майбутніх гіпотез. Тут потрібне would have + V3.\nПриклад: *%example%*",
            'present_perfect' => "❌ «%option%» — Present Perfect. Для умовного результату в минулому треба would have + V3.\nПриклад: *%example%*",
            'continuous' => "❌ «%option%» — форма Continuous. Стандартний результат третього умовного – would have + V3.\nПриклад: *%example%*",
        ],
        'result_would_not_have' => [
            'correct' => "✅ «%option%» правильно, бо заперечення третього умовного утворюємо як would not have + V3.\nПриклад: *%example%*",
            'first_conditional' => "❌ «%option%» — це Future/First Conditional із will not. Для минулого результату потрібне would not have + V3.\nПриклад: *%example%*",
            'past_simple' => "❌ «%option%» — Past Simple. Третій умовний вимагає would not have + V3.\nПриклад: *%example%*",
            'missing_have' => "❌ «%option%» пропускає have після would not, тож не утворює Third Conditional.\nПриклад: *%example%*",
            'present_simple' => "❌ «%option%» — Present Simple. Результат третього умовного описує минуле через would not have + V3.\nПриклад: *%example%*",
        ],
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Conditionals'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Custom: Mixed Conditionals Practice V2'])->id;

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Mixed Conditionals Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Mixed Conditional Sentence Completion'],
            ['category' => 'English Grammar Detail']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'Conditional Sentences Type 1-3'],
            ['category' => 'English Grammar Structure']
        )->id;

        $tenseTagIds = [
            'First Conditional' => Tag::firstOrCreate(['name' => 'First Conditional'], ['category' => 'Tenses'])->id,
            'Second Conditional' => Tag::firstOrCreate(['name' => 'Second Conditional'], ['category' => 'Tenses'])->id,
            'Third Conditional' => Tag::firstOrCreate(['name' => 'Third Conditional'], ['category' => 'Tenses'])->id,
        ];

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

            $tagIds = [$themeTagId, $detailTagId, $structureTagId];
            foreach ($entry['tense_tags'] as $tagName) {
                if (isset($tenseTagIds[$tagName])) {
                    $tagIds[] = $tenseTagIds[$tagName];
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
                'tense_tags' => ['First Conditional'],
                'question' => 'If you {a1} to my birthday party tonight, I {a2} very upset. I want you to come!',
                'markers' => [
                    'a1' => [
                        'type' => 'if_present_simple',
                        'subject' => 'you',
                        'verb' => 'come',
                        'verb_hint' => 'not come',
                        'answer' => "don't come",
                        'options' => [
                            ['value' => "don't come", 'reason' => 'correct'],
                            ['value' => "didn't come", 'reason' => 'past_simple'],
                            ['value' => "hadn't come", 'reason' => 'past_perfect'],
                            ['value' => "won't come", 'reason' => 'modal_future'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_will',
                        'subject' => 'I',
                        'verb' => 'be',
                        'verb_hint' => 'be',
                        'answer' => 'will be',
                        'options' => [
                            ['value' => 'will be', 'reason' => 'correct'],
                            ['value' => 'would be', 'reason' => 'second_conditional'],
                            ['value' => 'would have been', 'reason' => 'third_conditional'],
                            ['value' => 'am', 'reason' => 'present_simple'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tense_tags' => ['Third Conditional'],
                'question' => 'I {a1} Emma’s 29 years old if you {a2} me. She looks much older! Thank God you told me!',
                'markers' => [
                    'a1' => [
                        'type' => 'result_would',
                        'subject' => 'I',
                        'verb' => 'say',
                        'verb_hint' => 'not say',
                        'answer' => "wouldn't say",
                        'options' => [
                            ['value' => "wouldn't say", 'reason' => 'correct'],
                            ['value' => "won't say", 'reason' => 'first_conditional'],
                            ['value' => "wouldn't have said", 'reason' => 'third_conditional'],
                            ['value' => "don't say", 'reason' => 'present_simple'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'you',
                        'verb' => 'tell',
                        'verb_hint' => 'not tell',
                        'answer' => "hadn't told",
                        'options' => [
                            ['value' => "hadn't told", 'reason' => 'correct'],
                            ['value' => "didn't tell", 'reason' => 'past_simple'],
                            ['value' => "don't tell", 'reason' => 'present_simple'],
                            ['value' => "wouldn't tell", 'reason' => 'result_clause'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tense_tags' => ['First Conditional'],
                'question' => 'I don’t think he’ll phone you but if he {a1}, {a2} to him?',
                'markers' => [
                    'a1' => [
                        'type' => 'if_present_simple',
                        'subject' => 'he',
                        'verb' => 'do',
                        'verb_hint' => 'do',
                        'answer' => 'does',
                        'options' => [
                            ['value' => 'does', 'reason' => 'correct'],
                            ['value' => 'did', 'reason' => 'past_simple'],
                            ['value' => 'had done', 'reason' => 'past_perfect'],
                            ['value' => 'will do', 'reason' => 'modal_future'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_will_question',
                        'subject' => 'you',
                        'verb' => 'talk',
                        'verb_hint' => 'talk',
                        'answer' => 'will you talk',
                        'options' => [
                            ['value' => 'will you talk', 'reason' => 'correct'],
                            ['value' => 'would you talk', 'reason' => 'second_conditional'],
                            ['value' => 'would you have talked', 'reason' => 'third_conditional'],
                            ['value' => 'do you talk', 'reason' => 'present_simple'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tense_tags' => ['First Conditional'],
                'question' => 'You {a1} weight if you {a2} to the diet the doctor has told you to follow.',
                'markers' => [
                    'a1' => [
                        'type' => 'result_will',
                        'subject' => 'you',
                        'verb' => 'lose',
                        'verb_hint' => 'lose',
                        'answer' => 'will lose',
                        'options' => [
                            ['value' => 'will lose', 'reason' => 'correct'],
                            ['value' => 'would lose', 'reason' => 'second_conditional'],
                            ['value' => 'would have lost', 'reason' => 'third_conditional'],
                            ['value' => 'lose', 'reason' => 'present_simple'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'if_present_simple',
                        'subject' => 'you',
                        'verb' => 'stick',
                        'verb_hint' => 'stick',
                        'answer' => 'stick',
                        'options' => [
                            ['value' => 'stick', 'reason' => 'correct'],
                            ['value' => 'stuck', 'reason' => 'past_simple'],
                            ['value' => 'had stuck', 'reason' => 'past_perfect'],
                            ['value' => 'are sticking', 'reason' => 'present_continuous'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tense_tags' => ['Third Conditional'],
                'question' => 'If I {a1} more, I {a2} the exam. Now it’s too late! I got a D!',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'I',
                        'verb' => 'study',
                        'verb_hint' => 'study',
                        'answer' => 'had studied',
                        'options' => [
                            ['value' => 'had studied', 'reason' => 'correct'],
                            ['value' => 'studied', 'reason' => 'past_simple'],
                            ['value' => 'study', 'reason' => 'present_simple'],
                            ['value' => 'would study', 'reason' => 'result_clause'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_would_have',
                        'subject' => 'I',
                        'verb' => 'pass',
                        'verb_hint' => 'pass',
                        'answer' => 'would have passed',
                        'options' => [
                            ['value' => 'would have passed', 'reason' => 'correct'],
                            ['value' => 'would pass', 'reason' => 'second_conditional'],
                            ['value' => 'will pass', 'reason' => 'first_conditional'],
                            ['value' => 'passed', 'reason' => 'past_simple'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tense_tags' => ['Second Conditional'],
                'question' => 'I {a1} more healthily if I {a2} you.',
                'markers' => [
                    'a1' => [
                        'type' => 'result_would',
                        'subject' => 'I',
                        'verb' => 'eat',
                        'verb_hint' => 'eat',
                        'answer' => 'would eat',
                        'options' => [
                            ['value' => 'would eat', 'reason' => 'correct'],
                            ['value' => 'will eat', 'reason' => 'first_conditional'],
                            ['value' => 'would have eaten', 'reason' => 'third_conditional'],
                            ['value' => 'eat', 'reason' => 'present_simple'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'if_past_simple_subjunctive',
                        'subject' => 'I',
                        'verb' => 'be',
                        'verb_hint' => 'be',
                        'answer' => 'were',
                        'options' => [
                            ['value' => 'were', 'reason' => 'correct'],
                            ['value' => 'was', 'reason' => 'singular_agreement'],
                            ['value' => 'had been', 'reason' => 'past_perfect'],
                            ['value' => 'am', 'reason' => 'present_simple'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tense_tags' => ['Third Conditional'],
                'question' => 'We’ve been married for 10 years. If I {a1} to that party, I {a2} him.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'I',
                        'verb' => 'go',
                        'verb_hint' => 'not go',
                        'answer' => "hadn't gone",
                        'options' => [
                            ['value' => "hadn't gone", 'reason' => 'correct'],
                            ['value' => "didn't go", 'reason' => 'past_simple'],
                            ['value' => "don't go", 'reason' => 'present_simple'],
                            ['value' => "wouldn't go", 'reason' => 'result_clause'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'result_would_not_have',
                        'subject' => 'I',
                        'verb' => 'meet',
                        'verb_hint' => 'never meet',
                        'answer' => 'would never have met',
                        'options' => [
                            ['value' => 'would never have met', 'reason' => 'correct'],
                            ['value' => 'would never meet', 'reason' => 'second_conditional'],
                            ['value' => 'will never meet', 'reason' => 'first_conditional'],
                            ['value' => 'never met', 'reason' => 'past_simple'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tense_tags' => ['First Conditional'],
                'question' => 'You {a1} your final exams next week if you {a2} harder.',
                'markers' => [
                    'a1' => [
                        'type' => 'result_will',
                        'subject' => 'you',
                        'verb' => 'fail',
                        'verb_hint' => 'fail',
                        'answer' => 'will fail',
                        'options' => [
                            ['value' => 'will fail', 'reason' => 'correct'],
                            ['value' => 'would fail', 'reason' => 'second_conditional'],
                            ['value' => 'would have failed', 'reason' => 'third_conditional'],
                            ['value' => 'fail', 'reason' => 'present_simple'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'if_present_simple',
                        'subject' => 'you',
                        'verb' => 'study',
                        'verb_hint' => 'not study',
                        'answer' => "don't study",
                        'options' => [
                            ['value' => "don't study", 'reason' => 'correct'],
                            ['value' => "didn't study", 'reason' => 'past_simple'],
                            ['value' => "hadn't studied", 'reason' => 'past_perfect'],
                            ['value' => "won't study", 'reason' => 'modal_future'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tense_tags' => ['Third Conditional'],
                'question' => 'If I {a1} about this, I {a2} you, but I promise I didn’t know Robert was coming to the party.',
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'I',
                        'verb' => 'know',
                        'verb_hint' => 'know',
                        'answer' => 'had known',
                        'options' => [
                            ['value' => 'had known', 'reason' => 'correct'],
                            ['value' => 'knew', 'reason' => 'past_simple'],
                            ['value' => 'know', 'reason' => 'present_simple'],
                            ['value' => 'would know', 'reason' => 'result_clause'],
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
                            ['value' => 'would tell', 'reason' => 'second_conditional'],
                            ['value' => 'will tell', 'reason' => 'first_conditional'],
                            ['value' => 'told', 'reason' => 'past_simple'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tense_tags' => ['First Conditional'],
                'question' => 'Richard {a1} these sneakers if they {a2} on sale. He will only get them for an affordable price.',
                'markers' => [
                    'a1' => [
                        'type' => 'result_will_negative',
                        'subject' => 'Richard',
                        'verb' => 'buy',
                        'verb_hint' => 'not buy',
                        'answer' => "won't buy",
                        'options' => [
                            ['value' => "won't buy", 'reason' => 'correct'],
                            ['value' => "wouldn't buy", 'reason' => 'second_conditional'],
                            ['value' => "wouldn't have bought", 'reason' => 'third_conditional'],
                            ['value' => "doesn't buy", 'reason' => 'present_simple'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'if_present_simple',
                        'subject' => 'they',
                        'verb' => 'be',
                        'verb_hint' => 'not be',
                        'answer' => "aren't",
                        'options' => [
                            ['value' => "aren't", 'reason' => 'correct'],
                            ['value' => "weren't", 'reason' => 'past_simple'],
                            ['value' => "won't be", 'reason' => 'modal_future'],
                            ['value' => "haven't been", 'reason' => 'present_perfect'],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function buildHint(string $type, array $context, string $example): string
    {
        $template = $this->hintTemplates[$type] ?? "Час: Conditionals.\nПояснення: оберіть форму, що відповідає правилу для цього типу умовного.\nПриклад: *%example%*";

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
