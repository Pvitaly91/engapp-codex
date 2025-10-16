<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class MixedConditionalsBridgePracticeV2Seeder extends QuestionSeeder
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
        'if_past_simple' => "Час: Second / Mixed Conditional.\nФормула: **if + Past Simple** (або **did + not + base verb**).\nПояснення: умовна частина показує гіпотетичну ситуацію, тому дієслово «%verb%» ставимо у Past Simple.\nПриклад: *%example%*",
        'if_past_simple_subjunctive' => "Час: Second Conditional.\nФормула: **if + were** з усіма особами.\nПояснення: для нереальних теперішніх умов із дієсловом to be використовуємо were навіть із I/he/she/it.\nПриклад: *%example%*",
        'result_would' => "Час: Second / Mixed Conditional.\nФормула: **would + base verb**.\nПояснення: головна частина описує нереальний теперішній результат, тому вживаємо would + початкову форму дієслова «%verb%».\nПриклад: *%example%*",
        'result_would_negative' => "Час: Second / Mixed Conditional.\nФормула: **would not (wouldn't) + base verb**.\nПояснення: заперечний результат у теперішньому утворюємо через would not + базову форму дієслова «%verb%».\nПриклад: *%example%*",
        'result_would_have' => "Час: Third Conditional.\nФормула: **would have + V3**.\nПояснення: головна частина показує результат у минулому, тому використовуємо would have + третю форму дієслова «%verb%».\nПриклад: *%example%*",
        'result_would_not_have' => "Час: Third Conditional.\nФормула: **would not have + V3** (wouldn't have + V3).\nПояснення: заперечний результат утворюємо через would not have + третю форму дієслова «%verb%».\nПриклад: *%example%*",
        'result_modal_might' => "Час: Mixed Conditional.\nФормула: **might + base verb**.\nПояснення: модальний результат у теперішньому/майбутньому виражаємо через might + базову форму «%verb%».\nПриклад: *%example%*",
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
        'if_past_simple' => [
            'correct' => "✅ «%option%» правильно, бо в другому/мішаному умовному в if-клаузі ставимо Past Simple для %subject% із дієсловом «%verb%».\nПриклад: *%example%*",
            'present_simple' => "❌ «%option%» — Present Simple. Нереальна теперішня умова потребує Past Simple.\nПриклад: *%example%*",
            'past_perfect' => "❌ «%option%» — Past Perfect. Тут треба простий Past Simple без had.\nПриклад: *%example%*",
            'modal_future' => "❌ «%option%» — форма з will. Умовні речення після if не використовують will.\nПриклад: *%example%*",
            'default' => "❌ Форма не підходить для if-клауза Second/Mixed Conditional.\nПриклад: *%example%*",
        ],
        'if_past_simple_subjunctive' => [
            'correct' => "✅ «%option%» правильно, бо в Second Conditional для дієслова to be після if використовуємо were з усіма особами.\nПриклад: *%example%*",
            'singular_agreement' => "❌ «%option%» виглядає граматично, але в гіпотетичному if-реченні потрібно were навіть із I/he/she/it.\nПриклад: *%example%*",
            'past_perfect' => "❌ «%option%» — Past Perfect. Для теперішньої нереальної умови потрібен Past Simple (were).\nПриклад: *%example%*",
            'present_simple' => "❌ «%option%» — Present Simple. Другий умовний вимагає Past Simple (were).\nПриклад: *%example%*",
            'default' => "❌ Форма не відповідає if-клауза Second Conditional.\nПриклад: *%example%*",
        ],
        'result_would' => [
            'correct' => "✅ «%option%» правильно, бо результат нереальної теперішньої ситуації виражаємо як would + базова форма дієслова «%verb%».\nПриклад: *%example%*",
            'third_conditional' => "❌ «%option%» — форма would have, що підходить для минулого результату. Тут потрібен простий would + V1.\nПриклад: *%example%*",
            'past_simple' => "❌ «%option%» — Past Simple. Результат Mixed/Second Conditional має містити would.\nПриклад: *%example%*",
            'future' => "❌ «%option%» — майбутня форма з will. Ми говоримо про нереальний теперішній результат, тому потрібен would.\nПриклад: *%example%*",
            'default' => "❌ Неправильна форма для результату Second/Mixed Conditional.\nПриклад: *%example%*",
        ],
        'result_would_negative' => [
            'correct' => "✅ «%option%» правильно, бо заперечний теперішній результат утворюємо як would not (wouldn't) + базова форма «%verb%».\nПриклад: *%example%*",
            'third_conditional' => "❌ «%option%» — would not have, тобто результат у минулому. Тут потрібна теперішня форма would not + V1.\nПриклад: *%example%*",
            'past_simple' => "❌ «%option%» — Past Simple. Ми описуємо гіпотетичний теперішній результат, тому потрібне would not.\nПриклад: *%example%*",
            'positive' => "❌ «%option%» — ствердна форма. У реченні потрібне заперечення would not.\nПриклад: *%example%*",
            'default' => "❌ Неправильна форма для заперечного результату Second/Mixed Conditional.\nПриклад: *%example%*",
        ],
        'result_would_have' => [
            'correct' => "✅ «%option%» правильно, бо результат Third Conditional будується як would have + V3 для %subject%.\nПриклад: *%example%*",
            'first_conditional' => "❌ «%option%» — модель First Conditional з will. Нам потрібна форма would have + V3 про минулий результат.\nПриклад: *%example%*",
            'past_simple' => "❌ «%option%» — Past Simple. У головній частині Third Conditional слід ужити would have + V3.\nПриклад: *%example%*",
            'second_conditional' => "❌ «%option%» — Second Conditional (would + V1) для теперішніх/майбутніх гіпотез. Тут потрібне would have + V3.\nПриклад: *%example%*",
            'present_perfect' => "❌ «%option%» — Present Perfect. Для умовного результату в минулому треба would have + V3.\nПриклад: *%example%*",
            'continuous' => "❌ «%option%» — форма Continuous. Стандартний результат третього умовного – would have + V3.\nПриклад: *%example%*",
            'default' => "❌ Форма не відповідає результату Third Conditional.\nПриклад: *%example%*",
        ],
        'result_would_not_have' => [
            'correct' => "✅ «%option%» правильно, бо заперечення Third Conditional утворюємо як would not have + V3.\nПриклад: *%example%*",
            'first_conditional' => "❌ «%option%» — це Future/First Conditional із will not. Для минулого результату потрібне would not have + V3.\nПриклад: *%example%*",
            'past_simple' => "❌ «%option%» — Past Simple. Третій умовний вимагає would not have + V3.\nПриклад: *%example%*",
            'missing_have' => "❌ «%option%» пропускає have після would not, тож не утворює Third Conditional.\nПриклад: *%example%*",
            'present_simple' => "❌ «%option%» — Present Simple. Результат третього умовного описує минуле через would not have + V3.\nПриклад: *%example%*",
            'default' => "❌ Неправильна форма для заперечного результату Third Conditional.\nПриклад: *%example%*",
        ],
        'result_modal_might' => [
            'correct' => "✅ «%option%» правильно, бо модальний результат у змішаному умовному виражається як might + базова форма «%verb%».\nПриклад: *%example%*",
            'third_conditional' => "❌ «%option%» — форма might have, що описує минулий результат. Ми говоримо про теперішній наслідок.\nПриклад: *%example%*",
            'past_simple' => "❌ «%option%» — Past Simple. Потрібна модальна конструкція з might.\nПриклад: *%example%*",
            'default' => "❌ Невірна форма для модального результату в змішаному умовному.\nПриклад: *%example%*",
        ],
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Conditionals'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Custom: Mixed Conditionals Bridge Practice V2'])->id;

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Mixed Conditionals Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Mixed Conditional Sentence Completion'],
            ['category' => 'English Grammar Detail']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'Mixed Conditional Sentences'],
            ['category' => 'English Grammar Structure']
        )->id;

        $tenseTagIds = [
            'Second Conditional' => Tag::firstOrCreate(['name' => 'Second Conditional'], ['category' => 'Tenses'])->id,
            'Third Conditional' => Tag::firstOrCreate(['name' => 'Third Conditional'], ['category' => 'Tenses'])->id,
            'Mixed Conditional' => Tag::firstOrCreate(['name' => 'Mixed Conditional'], ['category' => 'Tenses'])->id,
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

    private function buildHint(string $type, array $context, string $example): string
    {
        $template = $this->hintTemplates[$type] ?? '';

        return strtr($template, [
            '%verb%' => $context['verb'] ?? '',
            '%subject%' => $context['subject'] ?? '',
            '%example%' => $example,
        ]);
    }

    private function buildExplanation(string $type, string $reason, string $option, array $context, string $example): string
    {
        $template = $this->explanationTemplates[$type][$reason] ?? $this->explanationTemplates[$type]['default'] ?? '';

        return strtr($template, [
            '%option%' => $option,
            '%verb%' => $context['verb'] ?? '',
            '%subject%' => $context['subject'] ?? '',
            '%example%' => $example,
        ]);
    }

    private function questionEntries(): array
    {
        return array_merge(
            ...[
                $this->exerciseOneEntries(),
                $this->exerciseTwoEntries(),
                $this->exerciseThreeEntries(),
                $this->exerciseFourEntries(),
            ]
        );
    }

    private function exerciseOneEntries(): array
    {
        return [
            [
                'level' => 'B1',
                'tense_tags' => ['Mixed Conditional', 'Second Conditional', 'Third Conditional'],
                'question' => "If I hadn't fought for our relationship, we {a1} together now.",
                'markers' => [
                    'a1' => [
                        'type' => 'result_would_negative',
                        'subject' => 'we',
                        'verb' => 'be',
                        'answer' => "wouldn't be",
                        'options' => [
                            ['value' => "wouldn't be", 'reason' => 'correct'],
                            ['value' => "wouldn't have been", 'reason' => 'third_conditional'],
                            ['value' => 'weren\'t', 'reason' => 'past_simple'],
                            ['value' => 'would be', 'reason' => 'positive'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tense_tags' => ['Mixed Conditional', 'Third Conditional'],
                'question' => "I would be happier if I {a1} 'yes' when she asked me to marry her.",
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'I',
                        'verb' => 'say',
                        'answer' => 'had said',
                        'options' => [
                            ['value' => 'had said', 'reason' => 'correct'],
                            ['value' => 'said', 'reason' => 'past_simple'],
                            ['value' => 'have said', 'reason' => 'present_perfect'],
                            ['value' => 'would say', 'reason' => 'result_clause'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tense_tags' => ['Mixed Conditional', 'Third Conditional'],
                'question' => "If you weren\'t such a jerk, they {a1} you to yesterday's party.",
                'markers' => [
                    'a1' => [
                        'type' => 'result_would_have',
                        'subject' => 'they',
                        'verb' => 'invite',
                        'answer' => 'would have invited',
                        'options' => [
                            ['value' => 'would have invited', 'reason' => 'correct'],
                            ['value' => 'would invite', 'reason' => 'second_conditional'],
                            ['value' => 'invited', 'reason' => 'past_simple'],
                            ['value' => 'will have invited', 'reason' => 'first_conditional'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tense_tags' => ['Mixed Conditional', 'Third Conditional'],
                'question' => "If you {a1} a map, as I told you, we wouldn't be lost now.",
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'you',
                        'verb' => 'take',
                        'answer' => 'had taken',
                        'options' => [
                            ['value' => 'had taken', 'reason' => 'correct'],
                            ['value' => 'took', 'reason' => 'past_simple'],
                            ['value' => 'would have taken', 'reason' => 'result_clause'],
                            ['value' => 'take', 'reason' => 'present_simple'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tense_tags' => ['Mixed Conditional', 'Third Conditional'],
                'question' => "If I didn't love you, I {a1} you last year.",
                'markers' => [
                    'a1' => [
                        'type' => 'result_would_not_have',
                        'subject' => 'I',
                        'verb' => 'marry',
                        'answer' => "wouldn't have married",
                        'options' => [
                            ['value' => "wouldn't have married", 'reason' => 'correct'],
                            ['value' => "wouldn't marry", 'reason' => 'present_simple'],
                            ['value' => "hadn't married", 'reason' => 'missing_have'],
                            ['value' => "didn't marry", 'reason' => 'past_simple'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tense_tags' => ['Mixed Conditional', 'Third Conditional'],
                'question' => "If I had more time, I {a1} you last night.",
                'markers' => [
                    'a1' => [
                        'type' => 'result_would_have',
                        'subject' => 'I',
                        'verb' => 'call',
                        'answer' => 'would have called',
                        'options' => [
                            ['value' => 'would have called', 'reason' => 'correct'],
                            ['value' => 'would call', 'reason' => 'second_conditional'],
                            ['value' => 'called', 'reason' => 'past_simple'],
                            ['value' => 'will have called', 'reason' => 'first_conditional'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tense_tags' => ['Mixed Conditional', 'Third Conditional'],
                'question' => "If you {a1} that job, you would be miserable now.",
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'you',
                        'verb' => 'accept',
                        'answer' => 'had accepted',
                        'options' => [
                            ['value' => 'had accepted', 'reason' => 'correct'],
                            ['value' => 'would have accepted', 'reason' => 'result_clause'],
                            ['value' => 'accepted', 'reason' => 'past_simple'],
                            ['value' => 'would accept', 'reason' => 'modal_future'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tense_tags' => ['Mixed Conditional', 'Second Conditional'],
                'question' => "If I {a1} afraid of flying, we'd have travelled by plane.",
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_simple_subjunctive',
                        'subject' => 'I',
                        'verb' => 'be',
                        'answer' => "weren't",
                        'options' => [
                            ['value' => "weren't", 'reason' => 'correct'],
                            ['value' => "wasn't", 'reason' => 'singular_agreement'],
                            ['value' => 'had been', 'reason' => 'past_perfect'],
                            ['value' => 'am not', 'reason' => 'present_simple'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tense_tags' => ['Mixed Conditional', 'Third Conditional'],
                'question' => "If you {a1} Dad's car without permission last night, you might be in trouble.",
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'you',
                        'verb' => 'take',
                        'answer' => 'had taken',
                        'options' => [
                            ['value' => 'had taken', 'reason' => 'correct'],
                            ['value' => 'took', 'reason' => 'past_simple'],
                            ['value' => 'would have taken', 'reason' => 'result_clause'],
                            ['value' => 'have taken', 'reason' => 'present_perfect'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'tense_tags' => ['Mixed Conditional', 'Third Conditional'],
                'question' => "You wouldn't have this job if I hadn't {a1} you for the interview.",
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'I',
                        'verb' => 'prepare',
                        'answer' => 'prepared',
                        'options' => [
                            ['value' => 'prepared', 'reason' => 'correct'],
                            ['value' => 'prepare', 'reason' => 'present_simple'],
                            ['value' => 'have prepared', 'reason' => 'present_perfect'],
                            ['value' => 'would have prepared', 'reason' => 'result_clause'],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function exerciseTwoEntries(): array
    {
        return [
            [
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional', 'Third Conditional'],
                'question' => "He says he's your friend, but he didn't help you. If he were your friend, he {a1} you.",
                'markers' => [
                    'a1' => [
                        'type' => 'result_would_have',
                        'subject' => 'he',
                        'verb' => 'help',
                        'answer' => 'would have helped',
                        'options' => [
                            ['value' => 'would have helped', 'reason' => 'correct'],
                            ['value' => 'would help', 'reason' => 'second_conditional'],
                            ['value' => 'helped', 'reason' => 'past_simple'],
                            ['value' => 'will have helped', 'reason' => 'first_conditional'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional', 'Third Conditional'],
                'question' => "I feel fine because I took the medicine. If I {a1} the medicine, I would still be in pain.",
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'I',
                        'verb' => 'take',
                        'answer' => "hadn't taken",
                        'options' => [
                            ['value' => "hadn't taken", 'reason' => 'correct'],
                            ['value' => "didn't take", 'reason' => 'past_simple'],
                            ['value' => "wouldn't have taken", 'reason' => 'result_clause'],
                            ['value' => "haven't taken", 'reason' => 'present_perfect'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional', 'Third Conditional'],
                'question' => "I'm not patient, and I didn't wait for them. If I were more patient, I {a1} for them.",
                'markers' => [
                    'a1' => [
                        'type' => 'result_would_have',
                        'subject' => 'I',
                        'verb' => 'wait',
                        'answer' => 'would have waited',
                        'options' => [
                            ['value' => 'would have waited', 'reason' => 'correct'],
                            ['value' => 'would wait', 'reason' => 'second_conditional'],
                            ['value' => 'waited', 'reason' => 'past_simple'],
                            ['value' => 'will have waited', 'reason' => 'first_conditional'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional', 'Third Conditional'],
                'question' => "I didn't know that you were there because I'm not a psychic. I {a1} that you were there if I were a psychic.",
                'markers' => [
                    'a1' => [
                        'type' => 'result_would_have',
                        'subject' => 'I',
                        'verb' => 'know',
                        'answer' => 'would have known',
                        'options' => [
                            ['value' => 'would have known', 'reason' => 'correct'],
                            ['value' => 'would know', 'reason' => 'second_conditional'],
                            ['value' => 'knew', 'reason' => 'past_simple'],
                            ['value' => 'will know', 'reason' => 'first_conditional'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional'],
                'question' => "I am where I am today because you helped me. I {a1} where I am today if you hadn't helped me.",
                'markers' => [
                    'a1' => [
                        'type' => 'result_would',
                        'subject' => 'I',
                        'verb' => 'be',
                        'answer' => "wouldn't be",
                        'options' => [
                            ['value' => "wouldn't be", 'reason' => 'correct'],
                            ['value' => "wouldn't have been", 'reason' => 'third_conditional'],
                            ['value' => "wasn't", 'reason' => 'past_simple'],
                            ['value' => 'will not be', 'reason' => 'future'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional', 'Third Conditional'],
                'question' => "I should be your coach. If I {a1} your coach, you would have won many more trophies.",
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'I',
                        'verb' => 'be',
                        'answer' => 'had been',
                        'options' => [
                            ['value' => 'had been', 'reason' => 'correct'],
                            ['value' => 'were', 'reason' => 'past_simple'],
                            ['value' => 'have been', 'reason' => 'present_perfect'],
                            ['value' => 'would be', 'reason' => 'result_clause'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional', 'Third Conditional'],
                'question' => "If I were ill, I {a1} to work yesterday.",
                'markers' => [
                    'a1' => [
                        'type' => 'result_would_not_have',
                        'subject' => 'I',
                        'verb' => 'go',
                        'answer' => "wouldn't have gone",
                        'options' => [
                            ['value' => "wouldn't have gone", 'reason' => 'correct'],
                            ['value' => "wouldn't go", 'reason' => 'present_simple'],
                            ['value' => "hadn't gone", 'reason' => 'missing_have'],
                            ['value' => "didn't go", 'reason' => 'past_simple'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional'],
                'question' => "If he hadn't been wearing a helmet, he {a1} dead now.",
                'markers' => [
                    'a1' => [
                        'type' => 'result_modal_might',
                        'subject' => 'he',
                        'verb' => 'be',
                        'answer' => 'might be',
                        'options' => [
                            ['value' => 'might be', 'reason' => 'correct'],
                            ['value' => 'might have been', 'reason' => 'third_conditional'],
                            ['value' => 'were', 'reason' => 'past_simple'],
                            ['value' => 'had been', 'reason' => 'past_perfect'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional', 'Third Conditional'],
                'question' => "I didn't understand his letter because I don't speak Russian. If I {a1} Russian, I would have understood his letter.",
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'I',
                        'verb' => 'speak',
                        'answer' => 'had spoken',
                        'options' => [
                            ['value' => 'had spoken', 'reason' => 'correct'],
                            ['value' => 'spoke', 'reason' => 'past_simple'],
                            ['value' => 'have spoken', 'reason' => 'present_perfect'],
                            ['value' => 'would speak', 'reason' => 'modal_future'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional'],
                'question' => "I'm tired because I went to bed late. If I hadn't gone to bed so late, I {a1} tired now.",
                'markers' => [
                    'a1' => [
                        'type' => 'result_would',
                        'subject' => 'I',
                        'verb' => 'be',
                        'answer' => "wouldn't be",
                        'options' => [
                            ['value' => "wouldn't be", 'reason' => 'correct'],
                            ['value' => "wouldn't have been", 'reason' => 'third_conditional'],
                            ['value' => "wasn't", 'reason' => 'past_simple'],
                            ['value' => 'will not be', 'reason' => 'future'],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function exerciseThreeEntries(): array
    {
        return [
            [
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional', 'Third Conditional'],
                'question' => "If you {a1} so much, you wouldn't be feeling sick now.",
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'you',
                        'verb' => 'eat',
                        'verb_hint' => 'not eat',
                        'answer' => "hadn't eaten",
                        'options' => [
                            ['value' => "hadn't eaten", 'reason' => 'correct'],
                            ['value' => "didn't eat", 'reason' => 'past_simple'],
                            ['value' => "don't eat", 'reason' => 'present_simple'],
                            ['value' => "weren't eating", 'reason' => 'continuous'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional', 'Third Conditional'],
                'question' => "If we {a1} money, we would have moved to a bigger house years ago.",
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_simple',
                        'subject' => 'we',
                        'verb' => 'have',
                        'answer' => 'had',
                        'options' => [
                            ['value' => 'had', 'reason' => 'correct'],
                            ['value' => 'have had', 'reason' => 'present_perfect'],
                            ['value' => 'had had', 'reason' => 'past_perfect'],
                            ['value' => 'would have', 'reason' => 'modal_future'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional', 'Third Conditional'],
                'question' => "If I {a1} you, I would have acted differently. Your behaviour was unacceptable.",
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_simple_subjunctive',
                        'subject' => 'I',
                        'verb' => 'be',
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
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional'],
                'question' => "We are second now. We {a1} top of the league if we hadn't lost our last match.",
                'markers' => [
                    'a1' => [
                        'type' => 'result_would',
                        'subject' => 'we',
                        'verb' => 'be',
                        'answer' => 'would be',
                        'options' => [
                            ['value' => 'would be', 'reason' => 'correct'],
                            ['value' => 'would have been', 'reason' => 'third_conditional'],
                            ['value' => 'were', 'reason' => 'past_simple'],
                            ['value' => 'will be', 'reason' => 'future'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional', 'Third Conditional'],
                'question' => "If he {a1} so lazy, he would have finished the assignment when it was due.",
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_simple_subjunctive',
                        'subject' => 'he',
                        'verb' => 'be',
                        'answer' => "weren't",
                        'options' => [
                            ['value' => "weren't", 'reason' => 'correct'],
                            ['value' => "wasn't", 'reason' => 'singular_agreement'],
                            ['value' => "hadn't been", 'reason' => 'past_perfect'],
                            ['value' => "isn't", 'reason' => 'present_simple'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional'],
                'question' => "I would be equally proud of you if you {a1} so many things in your life.",
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'you',
                        'verb' => 'achieve',
                        'answer' => "hadn't achieved",
                        'options' => [
                            ['value' => "hadn't achieved", 'reason' => 'correct'],
                            ['value' => "didn't achieve", 'reason' => 'past_simple'],
                            ['value' => "haven't achieved", 'reason' => 'present_perfect'],
                            ['value' => "wouldn't have achieved", 'reason' => 'result_clause'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional', 'Second Conditional'],
                'question' => "If I {a1} so hard, I wouldn't have got the job I have.",
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_simple',
                        'subject' => 'I',
                        'verb' => 'work',
                        'answer' => "didn't work",
                        'options' => [
                            ['value' => "didn't work", 'reason' => 'correct'],
                            ['value' => "don't work", 'reason' => 'present_simple'],
                            ['value' => "hadn't worked", 'reason' => 'past_perfect'],
                            ['value' => "wasn't working", 'reason' => 'continuous'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional', 'Third Conditional'],
                'question' => "If he {a1} anything wrong, we would know it by now.",
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'he',
                        'verb' => 'do',
                        'answer' => 'had done',
                        'options' => [
                            ['value' => 'had done', 'reason' => 'correct'],
                            ['value' => 'did', 'reason' => 'past_simple'],
                            ['value' => 'has done', 'reason' => 'present_perfect'],
                            ['value' => 'would do', 'reason' => 'result_clause'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional', 'Third Conditional'],
                'question' => "He'd still be here if you {a1} him away.",
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'you',
                        'verb' => 'scare',
                        'answer' => "hadn't scared",
                        'options' => [
                            ['value' => "hadn't scared", 'reason' => 'correct'],
                            ['value' => "didn't scare", 'reason' => 'past_simple'],
                            ['value' => "haven't scared", 'reason' => 'present_perfect'],
                            ['value' => "wouldn't have scared", 'reason' => 'result_clause'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional', 'Third Conditional'],
                'question' => "I would have a better job if I {a1} to university.",
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'I',
                        'verb' => 'go',
                        'answer' => 'had gone',
                        'options' => [
                            ['value' => 'had gone', 'reason' => 'correct'],
                            ['value' => 'went', 'reason' => 'past_simple'],
                            ['value' => 'have gone', 'reason' => 'present_perfect'],
                            ['value' => 'would go', 'reason' => 'result_clause'],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function exerciseFourEntries(): array
    {
        return [
            [
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional'],
                'question' => "If she had listened to my advice, she {a1} in such a mess now.",
                'markers' => [
                    'a1' => [
                        'type' => 'result_would_negative',
                        'subject' => 'she',
                        'verb' => 'be',
                        'answer' => "wouldn't be",
                        'options' => [
                            ['value' => "wouldn't be", 'reason' => 'correct'],
                            ['value' => "wouldn't have been", 'reason' => 'third_conditional'],
                            ['value' => "wasn't", 'reason' => 'past_simple'],
                            ['value' => 'would be', 'reason' => 'positive'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional', 'Third Conditional'],
                'question' => "If they {a1} earlier, they would have caught the first train.",
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'they',
                        'verb' => 'leave',
                        'answer' => 'had left',
                        'options' => [
                            ['value' => 'had left', 'reason' => 'correct'],
                            ['value' => 'left', 'reason' => 'past_simple'],
                            ['value' => 'have left', 'reason' => 'present_perfect'],
                            ['value' => 'would leave', 'reason' => 'result_clause'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional', 'Third Conditional'],
                'question' => "If I {a1} your phone number, I would have called you last night.",
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'I',
                        'verb' => 'know',
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
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional', 'Third Conditional'],
                'question' => "If we {a1} so tired, we would have solved this problem by now.",
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_simple_subjunctive',
                        'subject' => 'we',
                        'verb' => 'be',
                        'answer' => "weren't",
                        'options' => [
                            ['value' => "weren't", 'reason' => 'correct'],
                            ['value' => "wasn't", 'reason' => 'singular_agreement'],
                            ['value' => "hadn't been", 'reason' => 'past_perfect'],
                            ['value' => "aren't", 'reason' => 'present_simple'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional'],
                'question' => "If you had taken the medicine, you {a1} better now.",
                'markers' => [
                    'a1' => [
                        'type' => 'result_would',
                        'subject' => 'you',
                        'verb' => 'feel',
                        'answer' => 'would feel',
                        'options' => [
                            ['value' => 'would feel', 'reason' => 'correct'],
                            ['value' => 'would have felt', 'reason' => 'third_conditional'],
                            ['value' => 'felt', 'reason' => 'past_simple'],
                            ['value' => 'will feel', 'reason' => 'future'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional'],
                'question' => "If he hadn't spent all his savings, he {a1} to buy a new car now.",
                'markers' => [
                    'a1' => [
                        'type' => 'result_would',
                        'subject' => 'he',
                        'verb' => 'be able to',
                        'answer' => 'would be able',
                        'options' => [
                            ['value' => 'would be able', 'reason' => 'correct'],
                            ['value' => 'would have been able', 'reason' => 'third_conditional'],
                            ['value' => 'was able', 'reason' => 'past_simple'],
                            ['value' => 'will be able', 'reason' => 'future'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional', 'Third Conditional'],
                'question' => "If the team {a1} the final, the city would be celebrating now.",
                'markers' => [
                    'a1' => [
                        'type' => 'if_past_perfect',
                        'subject' => 'the team',
                        'verb' => 'win',
                        'answer' => 'had won',
                        'options' => [
                            ['value' => 'had won', 'reason' => 'correct'],
                            ['value' => 'won', 'reason' => 'past_simple'],
                            ['value' => 'have won', 'reason' => 'present_perfect'],
                            ['value' => 'would win', 'reason' => 'result_clause'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional'],
                'question' => "If you weren't allergic to nuts, you {a1} the cake at the party.",
                'markers' => [
                    'a1' => [
                        'type' => 'result_would',
                        'subject' => 'you',
                        'verb' => 'eat',
                        'answer' => 'would eat',
                        'options' => [
                            ['value' => 'would eat', 'reason' => 'correct'],
                            ['value' => 'would have eaten', 'reason' => 'third_conditional'],
                            ['value' => 'ate', 'reason' => 'past_simple'],
                            ['value' => 'will eat', 'reason' => 'future'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional', 'Third Conditional'],
                'question' => "If you weren't so stubborn, you {a1} to apologise yesterday.",
                'markers' => [
                    'a1' => [
                        'type' => 'result_would_have',
                        'subject' => 'you',
                        'verb' => 'agree',
                        'answer' => 'would have agreed',
                        'options' => [
                            ['value' => 'would have agreed', 'reason' => 'correct'],
                            ['value' => 'would agree', 'reason' => 'second_conditional'],
                            ['value' => 'agreed', 'reason' => 'past_simple'],
                            ['value' => 'will have agreed', 'reason' => 'first_conditional'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'tense_tags' => ['Mixed Conditional'],
                'question' => "If the storm had been stronger, the town {a1} without power now.",
                'markers' => [
                    'a1' => [
                        'type' => 'result_modal_might',
                        'subject' => 'the town',
                        'verb' => 'be',
                        'answer' => 'might be',
                        'options' => [
                            ['value' => 'might be', 'reason' => 'correct'],
                            ['value' => 'might have been', 'reason' => 'third_conditional'],
                            ['value' => 'was', 'reason' => 'past_simple'],
                            ['value' => 'had been', 'reason' => 'past_perfect'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
