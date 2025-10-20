<?php

namespace Database\Seeders\Ai;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class ConditionalsMixedPracticeCustomSeeder extends QuestionSeeder
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
            'correct' => "✅ «%option%» правильно, бо результат Third Conditional будуємо як would have + V3.\nПриклад: *%example%*",
            'first_conditional' => "❌ «%option%» — форма will, що характерна для First Conditional. Тут потрібне would have + V3.\nПриклад: *%example%*",
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

        $sourceIds = [
            'negative' => Source::firstOrCreate(['name' => 'Mixed Conditionals Practice - Negative Forms'])->id,
            'question' => Source::firstOrCreate(['name' => 'Mixed Conditionals Practice - Questions'])->id,
            'past' => Source::firstOrCreate(['name' => 'Mixed Conditionals Practice - Past Focus'])->id,
            'present' => Source::firstOrCreate(['name' => 'Mixed Conditionals Practice - Present Focus'])->id,
            'future' => Source::firstOrCreate(['name' => 'Mixed Conditionals Practice - Future Focus'])->id,
        ];

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Mixed Conditionals Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Mixed Conditional Sentence Creation'],
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
                'source_id' => $sourceIds[$entry['source']] ?? $sourceIds['future'],
                'flag' => 2,
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

    private function questionEntries(): array
    {
        return array_merge(
            $this->buildA1Questions(),
            $this->buildA2Questions(),
            $this->buildB1Questions(),
            $this->buildB2Questions(),
            $this->buildC1Questions(),
            $this->buildC2Questions(),
        );
    }

    private function buildA1Questions(): array
    {
        $entries = [];

        // Negative tasks (6)
        $entries[] = $this->makeEntry(
            'A1',
            'negative',
            ['First Conditional'],
            'If it {a1} tonight, we {a2} outside.',
            [
                'a1' => $this->ifPresentSimple('it', $this->forms('rain', 'rained', 'rained')),
                'a2' => $this->resultWillNegative('we', $this->forms('play', 'played', 'played')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'negative',
            ['First Conditional'],
            'If you {a1} your lunch, you {a2} hungry later.',
            [
                'a1' => $this->ifPresentSimple('you', $this->forms('forget', 'forgot', 'forgotten')),
                'a2' => $this->resultWillNegative('you', $this->forms('feel', 'felt', 'felt')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'negative',
            ['Second Conditional'],
            'If I {a1} rich, I {a2} in this tiny flat.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('I'),
                'a2' => $this->resultWould('I', $this->forms('live', 'lived', 'lived'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'negative',
            ['Second Conditional'],
            'If she {a1} more patient, she {a2} upset so often.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('she'),
                'a2' => $this->resultWould('she', $this->forms('get', 'got', 'gotten'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'negative',
            ['Third Conditional'],
            'If they {a1} earlier, they {a2} the first train.',
            [
                'a1' => $this->ifPastPerfect('they', $this->forms('leave', 'left', 'left')),
                'a2' => $this->resultWouldHave('they', $this->forms('miss', 'missed', 'missed'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'negative',
            ['Third Conditional'],
            'If we {a1} the alarm, we {a2} late for school.',
            [
                'a1' => $this->ifPastPerfect('we', $this->forms('set', 'set', 'set')),
                'a2' => $this->resultWouldHave('we', $this->forms('be', 'were', 'been'), true),
            ]
        );

        // Question tasks (6)
        $entries[] = $this->makeEntry(
            'A1',
            'question',
            ['First Conditional'],
            'If he {a1} the meeting, {a2} the online call?',
            [
                'a1' => $this->ifPresentSimple('he', $this->forms('cancel', 'canceled', 'canceled')),
                'a2' => $this->resultWillQuestion('you', $this->forms('join', 'joined', 'joined')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'question',
            ['First Conditional'],
            'If you {a1} the answer, {a2} me right away?',
            [
                'a1' => $this->ifPresentSimple('you', $this->forms('find', 'found', 'found')),
                'a2' => $this->resultWillQuestion('you', $this->forms('call', 'called', 'called')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'question',
            ['Second Conditional'],
            'If he {a1} taller, would you ask him to reach the shelf?',
            [
                'a1' => $this->ifPastSimpleSubjunctive('he'),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'question',
            ['Second Conditional'],
            'If I {a1} free this weekend, would you study with me?',
            [
                'a1' => $this->ifPastSimpleSubjunctive('I'),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'question',
            ['Third Conditional'],
            'If they {a1} earlier, would you have opened the shop?',
            [
                'a1' => $this->ifPastPerfect('they', $this->forms('arrive', 'arrived', 'arrived')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'question',
            ['Third Conditional'],
            'If she {a1} the warning, would you have felt safer?',
            [
                'a1' => $this->ifPastPerfect('she', $this->forms('hear', 'heard', 'heard')),
            ]
        );

        // Future-focused affirmatives (4)
        $entries[] = $this->makeEntry(
            'A1',
            'future',
            ['First Conditional'],
            'If you {a1} your ticket now, you {a2} a seat near the stage.',
            [
                'a1' => $this->ifPresentSimple('you', $this->forms('buy', 'bought', 'bought')),
                'a2' => $this->resultWill('you', $this->forms('get', 'got', 'gotten')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'future',
            ['First Conditional'],
            'If we {a1} the map, we {a2} to the museum easily.',
            [
                'a1' => $this->ifPresentSimple('we', $this->forms('follow', 'followed', 'followed')),
                'a2' => $this->resultWill('we', $this->forms('walk', 'walked', 'walked')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'future',
            ['First Conditional'],
            'If he {a1} help, I {a2} a spare pencil.',
            [
                'a1' => $this->ifPresentSimple('he', $this->forms('need', 'needed', 'needed')),
                'a2' => $this->resultWill('I', $this->forms('lend', 'lent', 'lent')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'future',
            ['First Conditional'],
            'If they {a1} early, they {a2} breakfast together.',
            [
                'a1' => $this->ifPresentSimple('they', $this->forms('arrive', 'arrived', 'arrived')),
                'a2' => $this->resultWill('they', $this->forms('have', 'had', 'had')),
            ]
        );

        // Present-focused hypotheticals (4)
        $entries[] = $this->makeEntry(
            'A1',
            'present',
            ['Second Conditional'],
            'If I {a1} free this afternoon, I {a2} a guitar lesson online.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('I'),
                'a2' => $this->resultWould('I', $this->forms('take', 'took', 'taken')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'present',
            ['Second Conditional'],
            'If he {a1} the boss, he {a2} shorter meetings.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('he'),
                'a2' => $this->resultWould('he', $this->forms('schedule', 'scheduled', 'scheduled')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'present',
            ['Second Conditional'],
            'If they {a1} closer to us, we {a2} tea every week.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('they'),
                'a2' => $this->resultWould('we', $this->forms('share', 'shared', 'shared')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'present',
            ['Second Conditional'],
            'If she {a1} more confident, she {a2} her ideas clearly.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('she'),
                'a2' => $this->resultWould('she', $this->forms('explain', 'explained', 'explained')),
            ]
        );

        // Past-focused conditionals (4)
        $entries[] = $this->makeEntry(
            'A1',
            'past',
            ['Third Conditional'],
            'If they {a1} the sign, they {a2} the wrong turn.',
            [
                'a1' => $this->ifPastPerfect('they', $this->forms('notice', 'noticed', 'noticed')),
                'a2' => $this->resultWouldHave('they', $this->forms('avoid', 'avoided', 'avoided')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'past',
            ['Third Conditional'],
            'If I {a1} earlier, I {a2} breakfast with you.',
            [
                'a1' => $this->ifPastPerfect('I', $this->forms('wake', 'woke', 'woken')),
                'a2' => $this->resultWouldHave('I', $this->forms('eat', 'ate', 'eaten')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'past',
            ['Third Conditional'],
            'If she {a1} the number, she {a2} you back.',
            [
                'a1' => $this->ifPastPerfect('she', $this->forms('save', 'saved', 'saved')),
                'a2' => $this->resultWouldHave('she', $this->forms('call', 'called', 'called')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'past',
            ['Third Conditional'],
            'If we {a1} the tickets, we {a2} the concert.',
            [
                'a1' => $this->ifPastPerfect('we', $this->forms('buy', 'bought', 'bought')),
                'a2' => $this->resultWouldHave('we', $this->forms('enjoy', 'enjoyed', 'enjoyed')),
            ]
        );

        return $entries;
    }

    private function buildA2Questions(): array
    {
        $entries = [];

        // Negative tasks (6)
        $entries[] = $this->makeEntry(
            'A2',
            'negative',
            ['First Conditional'],
            'If the team members {a1} the strategy today, they {a2} the match tomorrow.',
            [
                'a1' => $this->ifPresentSimple('they', $this->forms('review', 'reviewed', 'reviewed'), true),
                'a2' => $this->resultWillNegative('they', $this->forms('win', 'won', 'won')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'negative',
            ['First Conditional'],
            'If you {a1} the blinds before bed, the plants {a2} enough warmth.',
            [
                'a1' => $this->ifPresentSimple('you', $this->forms('close', 'closed', 'closed')),
                'a2' => $this->resultWillNegative('they', $this->forms('receive', 'received', 'received')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'negative',
            ['Second Conditional'],
            'If I {a1} more patient, I {a2} the project timeline.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('I'),
                'a2' => $this->resultWould('I', $this->forms('rush', 'rushed', 'rushed'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'negative',
            ['Second Conditional'],
            'If she {a1} calmer during feedback, she {a2} every rumour.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('she'),
                'a2' => $this->resultWould('she', $this->forms('repeat', 'repeated', 'repeated'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'negative',
            ['Third Conditional'],
            'If they {a1} the contract carefully, they {a2} the hidden fee.',
            [
                'a1' => $this->ifPastPerfect('they', $this->forms('read', 'read', 'read')),
                'a2' => $this->resultWouldHave('they', $this->forms('miss', 'missed', 'missed'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'negative',
            ['Third Conditional'],
            'If we {a1} the alarms last week, we {a2} so confused.',
            [
                'a1' => $this->ifPastPerfect('we', $this->forms('test', 'tested', 'tested')),
                'a2' => $this->resultWouldHave('we', $this->forms('feel', 'felt', 'felt'), true),
            ]
        );

        // Question tasks (6)
        $entries[] = $this->makeEntry(
            'A2',
            'question',
            ['First Conditional'],
            'If your mentor {a1} you tomorrow, {a2} the presentation?',
            [
                'a1' => $this->ifPresentSimple('he', $this->forms('call', 'called', 'called')),
                'a2' => $this->resultWillQuestion('you', $this->forms('finish', 'finished', 'finished')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'question',
            ['First Conditional'],
            'If the forecast {a1} snow, {a2} classes move online?',
            [
                'a1' => $this->ifPresentSimple('it', $this->forms('show', 'showed', 'shown')),
                'a2' => $this->resultWillQuestion('they', $this->forms('move', 'moved', 'moved')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'question',
            ['Second Conditional'],
            'If she {a1} less shy, would you invite her to speak?',
            [
                'a1' => $this->ifPastSimpleSubjunctive('she'),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'question',
            ['Second Conditional'],
            'If we {a1} closer, would you visit more often?',
            [
                'a1' => $this->ifPastSimpleSubjunctive('we'),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'question',
            ['Third Conditional'],
            'If they {a1} the alarm, would you have stayed longer?',
            [
                'a1' => $this->ifPastPerfect('they', $this->forms('hear', 'heard', 'heard')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'question',
            ['Third Conditional'],
            'If I {a1} your message earlier, would you have joined the call?',
            [
                'a1' => $this->ifPastPerfect('I', $this->forms('see', 'saw', 'seen')),
            ]
        );

        // Future-focused affirmatives (4)
        $entries[] = $this->makeEntry(
            'A2',
            'future',
            ['First Conditional'],
            'If you {a1} the rehearsal schedule, you {a2} time for a break.',
            [
                'a1' => $this->ifPresentSimple('you', $this->forms('organize', 'organized', 'organized')),
                'a2' => $this->resultWill('you', $this->forms('have', 'had', 'had')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'future',
            ['First Conditional'],
            'If they {a1} the donations today, they {a2} supplies to the shelter.',
            [
                'a1' => $this->ifPresentSimple('they', $this->forms('collect', 'collected', 'collected')),
                'a2' => $this->resultWill('they', $this->forms('deliver', 'delivered', 'delivered')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'future',
            ['First Conditional'],
            'If he {a1} the invitation, he {a2} the seminar on time.',
            [
                'a1' => $this->ifPresentSimple('he', $this->forms('accept', 'accepted', 'accepted')),
                'a2' => $this->resultWill('he', $this->forms('attend', 'attended', 'attended')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'future',
            ['First Conditional'],
            'If we {a1} the cooking tonight, we {a2} early.',
            [
                'a1' => $this->ifPresentSimple('we', $this->forms('finish', 'finished', 'finished')),
                'a2' => $this->resultWill('we', $this->forms('leave', 'left', 'left')),
            ]
        );

        // Present-focused hypotheticals (4)
        $entries[] = $this->makeEntry(
            'A2',
            'present',
            ['Second Conditional'],
            'If I {a1} fluent in Spanish, I {a2} clients from Madrid.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('I'),
                'a2' => $this->resultWould('I', $this->forms('support', 'supported', 'supported')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'present',
            ['Second Conditional'],
            'If she {a1} less stressed, she {a2} more creatively.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('she'),
                'a2' => $this->resultWould('she', $this->forms('think', 'thought', 'thought')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'present',
            ['Second Conditional'],
            'If they {a1} more reliable, we {a2} bigger projects.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('they'),
                'a2' => $this->resultWould('we', $this->forms('accept', 'accepted', 'accepted')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'present',
            ['Second Conditional'],
            'If he {a1} in the office, he {a2} lunch with us.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('he'),
                'a2' => $this->resultWould('he', $this->forms('share', 'shared', 'shared')),
            ]
        );

        // Past-focused conditionals (4)
        $entries[] = $this->makeEntry(
            'A2',
            'past',
            ['Third Conditional'],
            'If they {a1} the deadline, they {a2} the bonus.',
            [
                'a1' => $this->ifPastPerfect('they', $this->forms('meet', 'met', 'met')),
                'a2' => $this->resultWouldHave('they', $this->forms('receive', 'received', 'received')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'past',
            ['Third Conditional'],
            'If she {a1} the ingredients, she {a2} the cake.',
            [
                'a1' => $this->ifPastPerfect('she', $this->forms('measure', 'measured', 'measured')),
                'a2' => $this->resultWouldHave('she', $this->forms('bake', 'baked', 'baked')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'past',
            ['Third Conditional'],
            'If we {a1} the detour, we {a2} the view.',
            [
                'a1' => $this->ifPastPerfect('we', $this->forms('take', 'took', 'taken')),
                'a2' => $this->resultWouldHave('we', $this->forms('enjoy', 'enjoyed', 'enjoyed')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'past',
            ['Third Conditional'],
            'If I {a1} his advice, I {a2} the mistake.',
            [
                'a1' => $this->ifPastPerfect('I', $this->forms('follow', 'followed', 'followed')),
                'a2' => $this->resultWouldHave('I', $this->forms('avoid', 'avoided', 'avoided')),
            ]
        );

        return $entries;
    }

    private function buildB1Questions(): array
    {
        $entries = [];

        // Negative tasks (6)
        $entries[] = $this->makeEntry(
            'B1',
            'negative',
            ['First Conditional'],
            'If the marketing crew {a1} the newsletter today, subscribers {a2} the launch tomorrow.',
            [
                'a1' => $this->ifPresentSimple('they', $this->forms('send', 'sent', 'sent'), true),
                'a2' => $this->resultWillNegative('they', $this->forms('hear', 'heard', 'heard')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'negative',
            ['First Conditional'],
            'If you {a1} the security code, the system {a2} your access.',
            [
                'a1' => $this->ifPresentSimple('you', $this->forms('enter', 'entered', 'entered')),
                'a2' => $this->resultWillNegative('it', $this->forms('allow', 'allowed', 'allowed')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'negative',
            ['Second Conditional'],
            'If I {a1} overly cautious, I {a2} bold proposals.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('I'),
                'a2' => $this->resultWould('I', $this->forms('reject', 'rejected', 'rejected'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'negative',
            ['Second Conditional'],
            'If she {a1} more trusting, she {a2} every colleague.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('she'),
                'a2' => $this->resultWould('she', $this->forms('question', 'questioned', 'questioned'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'negative',
            ['Third Conditional'],
            'If they {a1} the storm warning, they {a2} on the ferry.',
            [
                'a1' => $this->ifPastPerfect('they', $this->forms('check', 'checked', 'checked')),
                'a2' => $this->resultWouldHave('they', $this->forms('stay', 'stayed', 'stayed'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'negative',
            ['Third Conditional'],
            'If we {a1} the server backups, we {a2} desperate at midnight.',
            [
                'a1' => $this->ifPastPerfect('we', $this->forms('prepare', 'prepared', 'prepared')),
                'a2' => $this->resultWouldHave('we', $this->forms('feel', 'felt', 'felt'), true),
            ]
        );

        // Question tasks (6)
        $entries[] = $this->makeEntry(
            'B1',
            'question',
            ['First Conditional'],
            'If the director {a1} the budget, {a2} the project begin next week?',
            [
                'a1' => $this->ifPresentSimple('he', $this->forms('approve', 'approved', 'approved')),
                'a2' => $this->resultWillQuestion('it', $this->forms('start', 'started', 'started')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'question',
            ['First Conditional'],
            'If you {a1} a delay, {a2} the clients immediately?',
            [
                'a1' => $this->ifPresentSimple('you', $this->forms('anticipate', 'anticipated', 'anticipated')),
                'a2' => $this->resultWillQuestion('you', $this->forms('inform', 'informed', 'informed')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'question',
            ['Second Conditional'],
            'If he {a1} on site, would you brief the interns yourself?',
            [
                'a1' => $this->ifPastSimpleSubjunctive('he'),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'question',
            ['Second Conditional'],
            'If they {a1} less confident, would you still trust their plan?',
            [
                'a1' => $this->ifPastSimpleSubjunctive('they'),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'question',
            ['Third Conditional'],
            'If she {a1} the map, would you have reached the valley?',
            [
                'a1' => $this->ifPastPerfect('she', $this->forms('study', 'studied', 'studied')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'question',
            ['Third Conditional'],
            'If we {a1} the keynote earlier, would you have reserved more seats?',
            [
                'a1' => $this->ifPastPerfect('we', $this->forms('announce', 'announced', 'announced')),
            ]
        );

        // Future-focused affirmatives (4)
        $entries[] = $this->makeEntry(
            'B1',
            'future',
            ['First Conditional'],
            'If you {a1} the slides this afternoon, you {a2} the panel tomorrow.',
            [
                'a1' => $this->ifPresentSimple('you', $this->forms('finalize', 'finalized', 'finalized')),
                'a2' => $this->resultWill('you', $this->forms('impress', 'impressed', 'impressed')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'future',
            ['First Conditional'],
            'If they {a1} the campsite early, they {a2} the sunrise.',
            [
                'a1' => $this->ifPresentSimple('they', $this->forms('reach', 'reached', 'reached')),
                'a2' => $this->resultWill('they', $this->forms('watch', 'watched', 'watched')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'future',
            ['First Conditional'],
            'If he {a1} the budget sheet, he {a2} the expense trend.',
            [
                'a1' => $this->ifPresentSimple('he', $this->forms('study', 'studied', 'studied')),
                'a2' => $this->resultWill('he', $this->forms('notice', 'noticed', 'noticed')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'future',
            ['First Conditional'],
            'If we {a1} the guest list tonight, we {a2} personalized notes.',
            [
                'a1' => $this->ifPresentSimple('we', $this->forms('update', 'updated', 'updated')),
                'a2' => $this->resultWill('we', $this->forms('write', 'wrote', 'written')),
            ]
        );

        // Present-focused hypotheticals (4)
        $entries[] = $this->makeEntry(
            'B1',
            'present',
            ['Second Conditional'],
            'If I {a1} in charge, I {a2} flexible deadlines.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('I'),
                'a2' => $this->resultWould('I', $this->forms('set', 'set', 'set')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'present',
            ['Second Conditional'],
            'If she {a1} less busy, she {a2} a mentoring circle.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('she'),
                'a2' => $this->resultWould('she', $this->forms('organize', 'organized', 'organized')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'present',
            ['Second Conditional'],
            'If they {a1} closer partners, we {a2} shared dashboards.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('they'),
                'a2' => $this->resultWould('we', $this->forms('build', 'built', 'built')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'present',
            ['Second Conditional'],
            'If he {a1} more confident, he {a2} bold pitches.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('he'),
                'a2' => $this->resultWould('he', $this->forms('deliver', 'delivered', 'delivered')),
            ]
        );

        // Past-focused conditionals (4)
        $entries[] = $this->makeEntry(
            'B1',
            'past',
            ['Third Conditional'],
            'If they {a1} the legal clause, they {a2} trouble.',
            [
                'a1' => $this->ifPastPerfect('they', $this->forms('notice', 'noticed', 'noticed')),
                'a2' => $this->resultWouldHave('they', $this->forms('avoid', 'avoided', 'avoided')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'past',
            ['Third Conditional'],
            'If she {a1} the violin daily, she {a2} the audition.',
            [
                'a1' => $this->ifPastPerfect('she', $this->forms('practice', 'practiced', 'practiced')),
                'a2' => $this->resultWouldHave('she', $this->forms('ace', 'aced', 'aced')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'past',
            ['Third Conditional'],
            'If we {a1} the contract earlier, we {a2} a better vendor.',
            [
                'a1' => $this->ifPastPerfect('we', $this->forms('review', 'reviewed', 'reviewed')),
                'a2' => $this->resultWouldHave('we', $this->forms('secure', 'secured', 'secured')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'past',
            ['Third Conditional'],
            'If I {a1} the warning, I {a2} that path.',
            [
                'a1' => $this->ifPastPerfect('I', $this->forms('heed', 'heeded', 'heeded')),
                'a2' => $this->resultWouldHave('I', $this->forms('avoid', 'avoided', 'avoided')),
            ]
        );

        return $entries;
    }

    private function buildB2Questions(): array
    {
        $entries = [];

        // Negative tasks (6)
        $entries[] = $this->makeEntry(
            'B2',
            'negative',
            ['First Conditional'],
            'If the research unit {a1} the raw data today, investors {a2} tomorrow\'s briefing.',
            [
                'a1' => $this->ifPresentSimple('they', $this->forms('upload', 'uploaded', 'uploaded'), true),
                'a2' => $this->resultWillNegative('they', $this->forms('trust', 'trusted', 'trusted')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'negative',
            ['First Conditional'],
            'If you {a1} the travel request, HR {a2} your tickets.',
            [
                'a1' => $this->ifPresentSimple('you', $this->forms('submit', 'submitted', 'submitted')),
                'a2' => $this->resultWillNegative('they', $this->forms('approve', 'approved', 'approved')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'negative',
            ['Second Conditional'],
            'If I {a1} so skeptical, I {a2} new partnerships.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('I'),
                'a2' => $this->resultWould('I', $this->forms('dismiss', 'dismissed', 'dismissed'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'negative',
            ['Second Conditional'],
            'If she {a1} less defensive, she {a2} every suggestion.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('she'),
                'a2' => $this->resultWould('she', $this->forms('block', 'blocked', 'blocked'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'negative',
            ['Third Conditional'],
            'If they {a1} the compliance memo, they {a2} those penalties.',
            [
                'a1' => $this->ifPastPerfect('they', $this->forms('read', 'read', 'read')),
                'a2' => $this->resultWouldHave('they', $this->forms('pay', 'paid', 'paid'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'negative',
            ['Third Conditional'],
            'If we {a1} the evacuation drill, we {a2} in the stairwell.',
            [
                'a1' => $this->ifPastPerfect('we', $this->forms('rehearse', 'rehearsed', 'rehearsed')),
                'a2' => $this->resultWouldHave('we', $this->forms('freeze', 'froze', 'frozen'), true),
            ]
        );

        // Question tasks (6)
        $entries[] = $this->makeEntry(
            'B2',
            'question',
            ['First Conditional'],
            'If the board {a1} the merger, {a2} the press conference happen on Friday?',
            [
                'a1' => $this->ifPresentSimple('they', $this->forms('confirm', 'confirmed', 'confirmed')),
                'a2' => $this->resultWillQuestion('it', $this->forms('occur', 'occurred', 'occurred')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'question',
            ['First Conditional'],
            'If you {a1} an outage, {a2} the regional managers?',
            [
                'a1' => $this->ifPresentSimple('you', $this->forms('detect', 'detected', 'detected')),
                'a2' => $this->resultWillQuestion('you', $this->forms('alert', 'alerted', 'alerted')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'question',
            ['Second Conditional'],
            'If he {a1} more transparent, would you endorse his proposal?',
            [
                'a1' => $this->ifPastSimpleSubjunctive('he'),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'question',
            ['Second Conditional'],
            'If they {a1} less motivated, would you extend the deadline?',
            [
                'a1' => $this->ifPastSimpleSubjunctive('they'),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'question',
            ['Third Conditional'],
            'If she {a1} the satellite images, would you have diverted the flight?',
            [
                'a1' => $this->ifPastPerfect('she', $this->forms('compare', 'compared', 'compared')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'question',
            ['Third Conditional'],
            'If we {a1} the donor list, would you have doubled the order?',
            [
                'a1' => $this->ifPastPerfect('we', $this->forms('update', 'updated', 'updated')),
            ]
        );

        // Future-focused affirmatives (4)
        $entries[] = $this->makeEntry(
            'B2',
            'future',
            ['First Conditional'],
            'If you {a1} the compliance checklist, you {a2} delays at customs.',
            [
                'a1' => $this->ifPresentSimple('you', $this->forms('complete', 'completed', 'completed')),
                'a2' => $this->resultWill('you', $this->forms('avoid', 'avoided', 'avoided')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'future',
            ['First Conditional'],
            'If they {a1} the prototype tonight, they {a2} it at dawn.',
            [
                'a1' => $this->ifPresentSimple('they', $this->forms('assemble', 'assembled', 'assembled')),
                'a2' => $this->resultWill('they', $this->forms('launch', 'launched', 'launched')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'future',
            ['First Conditional'],
            'If he {a1} the legal brief, he {a2} the loophole.',
            [
                'a1' => $this->ifPresentSimple('he', $this->forms('review', 'reviewed', 'reviewed')),
                'a2' => $this->resultWill('he', $this->forms('identify', 'identified', 'identified')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'future',
            ['First Conditional'],
            'If we {a1} the survey links, we {a2} feedback by noon.',
            [
                'a1' => $this->ifPresentSimple('we', $this->forms('send', 'sent', 'sent')),
                'a2' => $this->resultWill('we', $this->forms('collect', 'collected', 'collected')),
            ]
        );

        // Present-focused hypotheticals (4)
        $entries[] = $this->makeEntry(
            'B2',
            'present',
            ['Second Conditional'],
            'If I {a1} head of design, I {a2} a modular approach.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('I'),
                'a2' => $this->resultWould('I', $this->forms('adopt', 'adopted', 'adopted')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'present',
            ['Second Conditional'],
            'If she {a1} in Lisbon, she {a2} our remote hub.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('she'),
                'a2' => $this->resultWould('she', $this->forms('lead', 'led', 'led')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'present',
            ['Second Conditional'],
            'If they {a1} more data, we {a2} the forecast.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('they'),
                'a2' => $this->resultWould('we', $this->forms('refine', 'refined', 'refined')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'present',
            ['Second Conditional'],
            'If he {a1} less cautious, he {a2} the pilot program.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('he'),
                'a2' => $this->resultWould('he', $this->forms('launch', 'launched', 'launched')),
            ]
        );

        // Past-focused conditionals (4)
        $entries[] = $this->makeEntry(
            'B2',
            'past',
            ['Third Conditional'],
            'If they {a1} the supply chain earlier, they {a2} shortages.',
            [
                'a1' => $this->ifPastPerfect('they', $this->forms('map', 'mapped', 'mapped')),
                'a2' => $this->resultWouldHave('they', $this->forms('prevent', 'prevented', 'prevented')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'past',
            ['Third Conditional'],
            'If she {a1} the patent office, she {a2} the filing window.',
            [
                'a1' => $this->ifPastPerfect('she', $this->forms('visit', 'visited', 'visited')),
                'a2' => $this->resultWouldHave('she', $this->forms('meet', 'met', 'met')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'past',
            ['Third Conditional'],
            'If we {a1} the customer survey, we {a2} the warning signs.',
            [
                'a1' => $this->ifPastPerfect('we', $this->forms('conduct', 'conducted', 'conducted')),
                'a2' => $this->resultWouldHave('we', $this->forms('spot', 'spotted', 'spotted')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'past',
            ['Third Conditional'],
            'If I {a1} the figures twice, I {a2} the typo.',
            [
                'a1' => $this->ifPastPerfect('I', $this->forms('check', 'checked', 'checked')),
                'a2' => $this->resultWouldHave('I', $this->forms('catch', 'caught', 'caught')),
            ]
        );

        return $entries;
    }

    private function buildC1Questions(): array
    {
        $entries = [];

        // Negative tasks (6)
        $entries[] = $this->makeEntry(
            'C1',
            'negative',
            ['First Conditional'],
            'If the innovation team {a1} the prototype dossier today, stakeholders {a2} tomorrow\'s demo.',
            [
                'a1' => $this->ifPresentSimple('they', $this->forms('circulate', 'circulated', 'circulated'), true),
                'a2' => $this->resultWillNegative('they', $this->forms('endorse', 'endorsed', 'endorsed')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'negative',
            ['First Conditional'],
            'If you {a1} the compliance clause, the regulator {a2} the licence renewal.',
            [
                'a1' => $this->ifPresentSimple('you', $this->forms('omit', 'omitted', 'omitted')),
                'a2' => $this->resultWillNegative('it', $this->forms('grant', 'granted', 'granted')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'negative',
            ['Second Conditional'],
            'If I {a1} so risk-averse, I {a2} pilot projects.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('I'),
                'a2' => $this->resultWould('I', $this->forms('cancel', 'cancelled', 'cancelled'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'negative',
            ['Second Conditional'],
            'If she {a1} less defensive in meetings, she {a2} every dissenting voice.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('she'),
                'a2' => $this->resultWould('she', $this->forms('silence', 'silenced', 'silenced'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'negative',
            ['Third Conditional'],
            'If they {a1} the contingency plan, they {a2} all night.',
            [
                'a1' => $this->ifPastPerfect('they', $this->forms('rehearse', 'rehearsed', 'rehearsed')),
                'a2' => $this->resultWouldHave('they', $this->forms('scramble', 'scrambled', 'scrambled'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'negative',
            ['Third Conditional'],
            'If we {a1} the legal update, we {a2} the injunction.',
            [
                'a1' => $this->ifPastPerfect('we', $this->forms('review', 'reviewed', 'reviewed')),
                'a2' => $this->resultWouldHave('we', $this->forms('face', 'faced', 'faced'), true),
            ]
        );

        // Question tasks (6)
        $entries[] = $this->makeEntry(
            'C1',
            'question',
            ['First Conditional'],
            'If the ethics board {a1} the report, {a2} postponed for another quarter?',
            [
                'a1' => $this->ifPresentSimple('they', $this->forms('endorse', 'endorsed', 'endorsed')),
                'a2' => $this->resultWillQuestion('it', $this->forms('be', 'was', 'been')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'question',
            ['First Conditional'],
            'If you {a1} dissent in the chat, {a2} a follow-up forum?',
            [
                'a1' => $this->ifPresentSimple('you', $this->forms('detect', 'detected', 'detected')),
                'a2' => $this->resultWillQuestion('you', $this->forms('schedule', 'scheduled', 'scheduled')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'question',
            ['Second Conditional'],
            'If he {a1} more candid, would you delegate strategic calls to him?',
            [
                'a1' => $this->ifPastSimpleSubjunctive('he'),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'question',
            ['Second Conditional'],
            'If they {a1} less aligned, would you still ratify their roadmap?',
            [
                'a1' => $this->ifPastSimpleSubjunctive('they'),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'question',
            ['Third Conditional'],
            'If she {a1} the audit trail, would you have flagged the anomaly?',
            [
                'a1' => $this->ifPastPerfect('she', $this->forms('trace', 'traced', 'traced')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'question',
            ['Third Conditional'],
            'If we {a1} the charter earlier, would you have challenged the clause?',
            [
                'a1' => $this->ifPastPerfect('we', $this->forms('draft', 'drafted', 'drafted')),
            ]
        );

        // Future-focused affirmatives (4)
        $entries[] = $this->makeEntry(
            'C1',
            'future',
            ['First Conditional'],
            'If you {a1} the migration scripts tonight, you {a2} downtime at dawn.',
            [
                'a1' => $this->ifPresentSimple('you', $this->forms('validate', 'validated', 'validated')),
                'a2' => $this->resultWill('you', $this->forms('prevent', 'prevented', 'prevented')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'future',
            ['First Conditional'],
            'If they {a1} the investor memo, they {a2} reassurance quickly.',
            [
                'a1' => $this->ifPresentSimple('they', $this->forms('publish', 'published', 'published')),
                'a2' => $this->resultWill('they', $this->forms('gain', 'gained', 'gained')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'future',
            ['First Conditional'],
            'If he {a1} the analytics dashboard, he {a2} emerging trends.',
            [
                'a1' => $this->ifPresentSimple('he', $this->forms('monitor', 'monitored', 'monitored')),
                'a2' => $this->resultWill('he', $this->forms('capture', 'captured', 'captured')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'future',
            ['First Conditional'],
            'If we {a1} contingency funds, we {a2} supply shocks.',
            [
                'a1' => $this->ifPresentSimple('we', $this->forms('allocate', 'allocated', 'allocated')),
                'a2' => $this->resultWill('we', $this->forms('absorb', 'absorbed', 'absorbed')),
            ]
        );

        // Present-focused hypotheticals (4)
        $entries[] = $this->makeEntry(
            'C1',
            'present',
            ['Second Conditional'],
            'If I {a1} chief curator, I {a2} radical installations.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('I'),
                'a2' => $this->resultWould('I', $this->forms('commission', 'commissioned', 'commissioned')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'present',
            ['Second Conditional'],
            'If she {a1} stationed in Nairobi, she {a2} the field office.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('she'),
                'a2' => $this->resultWould('she', $this->forms('direct', 'directed', 'directed')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'present',
            ['Second Conditional'],
            'If they {a1} more multilingual staff, we {a2} global support instantly.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('they'),
                'a2' => $this->resultWould('we', $this->forms('scale', 'scaled', 'scaled')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'present',
            ['Second Conditional'],
            'If he {a1} less entrenched, he {a2} outsider perspectives.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('he'),
                'a2' => $this->resultWould('he', $this->forms('embrace', 'embraced', 'embraced')),
            ]
        );

        // Past-focused conditionals (4)
        $entries[] = $this->makeEntry(
            'C1',
            'past',
            ['Third Conditional'],
            'If they {a1} the whistleblower memo, they {a2} that lawsuit.',
            [
                'a1' => $this->ifPastPerfect('they', $this->forms('honor', 'honored', 'honored')),
                'a2' => $this->resultWouldHave('they', $this->forms('avoid', 'avoided', 'avoided')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'past',
            ['Third Conditional'],
            'If she {a1} the carbon data, she {a2} the policy draft.',
            [
                'a1' => $this->ifPastPerfect('she', $this->forms('model', 'modeled', 'modeled')),
                'a2' => $this->resultWouldHave('she', $this->forms('rewrite', 'rewrote', 'rewritten')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'past',
            ['Third Conditional'],
            'If we {a1} the tender criteria, we {a2} a stronger consortium.',
            [
                'a1' => $this->ifPastPerfect('we', $this->forms('clarify', 'clarified', 'clarified')),
                'a2' => $this->resultWouldHave('we', $this->forms('attract', 'attracted', 'attracted')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'past',
            ['Third Conditional'],
            'If I {a1} the procurement chain, I {a2} the recall.',
            [
                'a1' => $this->ifPastPerfect('I', $this->forms('audit', 'audited', 'audited')),
                'a2' => $this->resultWouldHave('I', $this->forms('prevent', 'prevented', 'prevented')),
            ]
        );

        return $entries;
    }

    private function buildC2Questions(): array
    {
        $entries = [];

        // Negative tasks (6)
        $entries[] = $this->makeEntry(
            'C2',
            'negative',
            ['First Conditional'],
            'If the oversight committee {a1} the whistleblower summary today, regulators {a2} the midnight briefing.',
            [
                'a1' => $this->ifPresentSimple('they', $this->forms('release', 'released', 'released'), true),
                'a2' => $this->resultWillNegative('they', $this->forms('authorize', 'authorized', 'authorized')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'negative',
            ['First Conditional'],
            'If you {a1} the arbitration clause, the consortium {a2} the partnership extension.',
            [
                'a1' => $this->ifPresentSimple('you', $this->forms('ignore', 'ignored', 'ignored')),
                'a2' => $this->resultWillNegative('they', $this->forms('endorse', 'endorsed', 'endorsed')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'negative',
            ['Second Conditional'],
            'If I {a1} so doctrinaire, I {a2} experimental pilots.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('I'),
                'a2' => $this->resultWould('I', $this->forms('stifle', 'stifled', 'stifled'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'negative',
            ['Second Conditional'],
            'If she {a1} less territorial, she {a2} colleagues\' initiatives.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('she'),
                'a2' => $this->resultWould('she', $this->forms('undermine', 'undermined', 'undermined'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'negative',
            ['Third Conditional'],
            'If they {a1} the due-diligence file, they {a2} those sanctions.',
            [
                'a1' => $this->ifPastPerfect('they', $this->forms('scrutinize', 'scrutinized', 'scrutinized')),
                'a2' => $this->resultWouldHave('they', $this->forms('incur', 'incurred', 'incurred'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'negative',
            ['Third Conditional'],
            'If we {a1} the encrypted backups, we {a2} ransom negotiations.',
            [
                'a1' => $this->ifPastPerfect('we', $this->forms('rotate', 'rotated', 'rotated')),
                'a2' => $this->resultWouldHave('we', $this->forms('enter', 'entered', 'entered'), true),
            ]
        );

        // Question tasks (6)
        $entries[] = $this->makeEntry(
            'C2',
            'question',
            ['First Conditional'],
            'If the tribunal {a1} the compliance roadmap, {a2} deferred again?',
            [
                'a1' => $this->ifPresentSimple('they', $this->forms('ratify', 'ratified', 'ratified')),
                'a2' => $this->resultWillQuestion('it', $this->forms('be', 'was', 'been')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'question',
            ['First Conditional'],
            'If you {a1} a whistleblower ping, {a2} an emergency caucus?',
            [
                'a1' => $this->ifPresentSimple('you', $this->forms('register', 'registered', 'registered')),
                'a2' => $this->resultWillQuestion('you', $this->forms('convene', 'convened', 'convened')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'question',
            ['Second Conditional'],
            'If he {a1} more forthright, would you entrust him with plenary voting rights?',
            [
                'a1' => $this->ifPastSimpleSubjunctive('he'),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'question',
            ['Second Conditional'],
            'If they {a1} less cohesive, would you reopen the risk model?',
            [
                'a1' => $this->ifPastSimpleSubjunctive('they'),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'question',
            ['Third Conditional'],
            'If she {a1} the discovery trove, would you have refiled the case?',
            [
                'a1' => $this->ifPastPerfect('she', $this->forms('parse', 'parsed', 'parsed')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'question',
            ['Third Conditional'],
            'If we {a1} the concession draft, would you have renegotiated the clause?',
            [
                'a1' => $this->ifPastPerfect('we', $this->forms('circulate', 'circulated', 'circulated')),
            ]
        );

        // Future-focused affirmatives (4)
        $entries[] = $this->makeEntry(
            'C2',
            'future',
            ['First Conditional'],
            'If you {a1} the interoperability patch tonight, you {a2} a cascading outage.',
            [
                'a1' => $this->ifPresentSimple('you', $this->forms('deploy', 'deployed', 'deployed')),
                'a2' => $this->resultWill('you', $this->forms('avert', 'averted', 'averted')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'future',
            ['First Conditional'],
            'If they {a1} the humanitarian brief, they {a2} donor confidence.',
            [
                'a1' => $this->ifPresentSimple('they', $this->forms('issue', 'issued', 'issued')),
                'a2' => $this->resultWill('they', $this->forms('reinforce', 'reinforced', 'reinforced')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'future',
            ['First Conditional'],
            'If he {a1} the predictive ledger, he {a2} anomalies early.',
            [
                'a1' => $this->ifPresentSimple('he', $this->forms('audit', 'audited', 'audited')),
                'a2' => $this->resultWill('he', $this->forms('detect', 'detected', 'detected')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'future',
            ['First Conditional'],
            'If we {a1} discretionary reserves, we {a2} geopolitical shocks.',
            [
                'a1' => $this->ifPresentSimple('we', $this->forms('hedge', 'hedged', 'hedged')),
                'a2' => $this->resultWill('we', $this->forms('withstand', 'withstood', 'withstood')),
            ]
        );

        // Present-focused hypotheticals (4)
        $entries[] = $this->makeEntry(
            'C2',
            'present',
            ['Second Conditional'],
            'If I {a1} secretary-general, I {a2} sweeping institutional reforms.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('I'),
                'a2' => $this->resultWould('I', $this->forms('champion', 'championed', 'championed')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'present',
            ['Second Conditional'],
            'If she {a1} stationed in Geneva, she {a2} multilateral negotiations.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('she'),
                'a2' => $this->resultWould('she', $this->forms('broker', 'brokered', 'brokered')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'present',
            ['Second Conditional'],
            'If they {a1} broader data sovereignty, we {a2} policy frameworks faster.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('they'),
                'a2' => $this->resultWould('we', $this->forms('codify', 'codified', 'codified')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'present',
            ['Second Conditional'],
            'If he {a1} less beholden to legacy systems, he {a2} open protocols.',
            [
                'a1' => $this->ifPastSimpleSubjunctive('he'),
                'a2' => $this->resultWould('he', $this->forms('adopt', 'adopted', 'adopted')),
            ]
        );

        // Past-focused conditionals (4)
        $entries[] = $this->makeEntry(
            'C2',
            'past',
            ['Third Conditional'],
            'If they {a1} the amicus brief, they {a2} the precedent.',
            [
                'a1' => $this->ifPastPerfect('they', $this->forms('file', 'filed', 'filed')),
                'a2' => $this->resultWouldHave('they', $this->forms('shape', 'shaped', 'shaped')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'past',
            ['Third Conditional'],
            'If she {a1} the climate model, she {a2} the mitigation slate.',
            [
                'a1' => $this->ifPastPerfect('she', $this->forms('stress-test', 'stress-tested', 'stress-tested')),
                'a2' => $this->resultWouldHave('she', $this->forms('adjust', 'adjusted', 'adjusted')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'past',
            ['Third Conditional'],
            'If we {a1} the arbitration panel earlier, we {a2} a fairer mandate.',
            [
                'a1' => $this->ifPastPerfect('we', $this->forms('convene', 'convened', 'convened')),
                'a2' => $this->resultWouldHave('we', $this->forms('secure', 'secured', 'secured')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'past',
            ['Third Conditional'],
            'If I {a1} the procurement ledger, I {a2} the bribery trail.',
            [
                'a1' => $this->ifPastPerfect('I', $this->forms('reconcile', 'reconciled', 'reconciled')),
                'a2' => $this->resultWouldHave('I', $this->forms('expose', 'exposed', 'exposed')),
            ]
        );

        return $entries;
    }

    private function forms(string $base, ?string $past = null, ?string $participle = null): array
    {
        $past = $past ?? $base . 'ed';
        $participle = $participle ?? $past;

        return [
            'base' => $base,
            'past' => $past,
            'participle' => $participle,
        ];
    }

    private function presentSimplePositive(string $subject, string $base): string
    {
        $subjectLower = strtolower($subject);
        if (in_array($subjectLower, ['he', 'she', 'it'])) {
            return $base . 's';
        }

        return $base;
    }

    private function presentSimpleNegative(string $subject, string $base): string
    {
        $subjectLower = strtolower($subject);
        $auxiliary = in_array($subjectLower, ['he', 'she', 'it']) ? "doesn't" : "don't";

        return $auxiliary . ' ' . $base;
    }

    private function ifPresentSimple(string $subject, array $forms, bool $negative = false, ?string $verbHint = null): array
    {
        $answer = $negative
            ? $this->presentSimpleNegative($subject, $forms['base'])
            : $this->presentSimplePositive($subject, $forms['base']);

        $options = $negative
            ? [
                ['value' => $answer, 'reason' => 'correct'],
                ['value' => "didn't " . $forms['base'], 'reason' => 'past_simple'],
                ['value' => "hadn't " . $forms['participle'], 'reason' => 'past_perfect'],
                ['value' => "won't " . $forms['base'], 'reason' => 'modal_future'],
            ]
            : [
                ['value' => $answer, 'reason' => 'correct'],
                ['value' => $forms['past'], 'reason' => 'past_simple'],
                ['value' => 'had ' . $forms['participle'], 'reason' => 'past_perfect'],
                ['value' => 'will ' . $forms['base'], 'reason' => 'modal_future'],
            ];

        return [
            'type' => 'if_present_simple',
            'subject' => $subject,
            'verb' => $forms['base'],
            'verb_hint' => $verbHint ?? ($negative ? 'not ' . $forms['base'] : $forms['base']),
            'answer' => $answer,
            'options' => $options,
        ];
    }

    private function ifPastPerfect(string $subject, array $forms, bool $negative = false, ?string $verbHint = null): array
    {
        $answer = $negative ? "hadn't " . $forms['participle'] : 'had ' . $forms['participle'];

        $options = $negative
            ? [
                ['value' => $answer, 'reason' => 'correct'],
                ['value' => "didn't " . $forms['base'], 'reason' => 'past_simple'],
                ['value' => $forms['base'], 'reason' => 'present_simple'],
                ['value' => 'would ' . $forms['base'], 'reason' => 'result_clause'],
            ]
            : [
                ['value' => $answer, 'reason' => 'correct'],
                ['value' => $forms['past'], 'reason' => 'past_simple'],
                ['value' => $forms['base'], 'reason' => 'present_simple'],
                ['value' => 'would ' . $forms['base'], 'reason' => 'result_clause'],
            ];

        return [
            'type' => 'if_past_perfect',
            'subject' => $subject,
            'verb' => $forms['base'],
            'verb_hint' => $verbHint ?? ($negative ? 'not ' . $forms['base'] : $forms['base']),
            'answer' => $answer,
            'options' => $options,
        ];
    }

    private function ifPastSimpleSubjunctive(string $subject, bool $negative = false, ?string $verbHint = null): array
    {
        $answer = $negative ? "weren't" : 'were';

        $options = $negative
            ? [
                ['value' => $answer, 'reason' => 'correct'],
                ['value' => "wasn't", 'reason' => 'singular_agreement'],
                ['value' => 'had been', 'reason' => 'past_perfect'],
                ['value' => 'are', 'reason' => 'present_simple'],
            ]
            : [
                ['value' => $answer, 'reason' => 'correct'],
                ['value' => 'was', 'reason' => 'singular_agreement'],
                ['value' => 'had been', 'reason' => 'past_perfect'],
                ['value' => 'are', 'reason' => 'present_simple'],
            ];

        return [
            'type' => 'if_past_simple_subjunctive',
            'subject' => $subject,
            'verb' => 'be',
            'verb_hint' => $verbHint ?? ($negative ? 'not be' : 'be'),
            'answer' => $answer,
            'options' => $options,
        ];
    }

    private function resultWill(string $subject, array $forms): array
    {
        $answer = 'will ' . $forms['base'];

        return [
            'type' => 'result_will',
            'subject' => $subject,
            'verb' => $forms['base'],
            'verb_hint' => $forms['base'],
            'answer' => $answer,
            'options' => [
                ['value' => $answer, 'reason' => 'correct'],
                ['value' => 'would ' . $forms['base'], 'reason' => 'second_conditional'],
                ['value' => 'would have ' . $forms['participle'], 'reason' => 'third_conditional'],
                ['value' => $this->presentSimplePositive($subject, $forms['base']), 'reason' => 'present_simple'],
            ],
        ];
    }

    private function resultWillNegative(string $subject, array $forms): array
    {
        $answer = "won't " . $forms['base'];

        return [
            'type' => 'result_will_negative',
            'subject' => $subject,
            'verb' => $forms['base'],
            'verb_hint' => 'not ' . $forms['base'],
            'answer' => $answer,
            'options' => [
                ['value' => $answer, 'reason' => 'correct'],
                ['value' => 'would not ' . $forms['base'], 'reason' => 'second_conditional'],
                ['value' => 'would not have ' . $forms['participle'], 'reason' => 'third_conditional'],
                ['value' => $this->presentSimpleNegative($subject, $forms['base']), 'reason' => 'present_simple'],
            ],
        ];
    }

    private function resultWillQuestion(string $subject, array $forms): array
    {
        $subjectLower = strtolower($subject);
        $answer = 'will ' . $subjectLower . ' ' . $forms['base'];

        return [
            'type' => 'result_will_question',
            'subject' => $subject,
            'verb' => $forms['base'],
            'verb_hint' => $forms['base'],
            'answer' => $answer,
            'options' => [
                ['value' => $answer, 'reason' => 'correct'],
                ['value' => 'would ' . $subjectLower . ' ' . $forms['base'], 'reason' => 'second_conditional'],
                ['value' => 'would ' . $subjectLower . ' have ' . $forms['participle'], 'reason' => 'third_conditional'],
                ['value' => $subjectLower . ' ' . $forms['base'], 'reason' => 'present_simple'],
            ],
        ];
    }

    private function resultWould(string $subject, array $forms, bool $negative = false): array
    {
        $answer = $negative ? 'would not ' . $forms['base'] : 'would ' . $forms['base'];

        return [
            'type' => 'result_would',
            'subject' => $subject,
            'verb' => $forms['base'],
            'verb_hint' => $negative ? 'not ' . $forms['base'] : $forms['base'],
            'answer' => $answer,
            'options' => $negative
                ? [
                    ['value' => $answer, 'reason' => 'correct'],
                    ['value' => 'will not ' . $forms['base'], 'reason' => 'first_conditional'],
                    ['value' => 'would not have ' . $forms['participle'], 'reason' => 'third_conditional'],
                    ['value' => $this->presentSimpleNegative($subject, $forms['base']), 'reason' => 'present_simple'],
                ]
                : [
                    ['value' => $answer, 'reason' => 'correct'],
                    ['value' => 'will ' . $forms['base'], 'reason' => 'first_conditional'],
                    ['value' => 'would have ' . $forms['participle'], 'reason' => 'third_conditional'],
                    ['value' => $this->presentSimplePositive($subject, $forms['base']), 'reason' => 'present_simple'],
                ],
        ];
    }

    private function resultWouldHave(string $subject, array $forms, bool $negative = false): array
    {
        $answer = $negative ? 'would not have ' . $forms['participle'] : 'would have ' . $forms['participle'];

        $options = $negative
            ? [
                ['value' => $answer, 'reason' => 'correct'],
                ['value' => 'will not ' . $forms['base'], 'reason' => 'first_conditional'],
                ['value' => $forms['past'], 'reason' => 'past_simple'],
                ['value' => 'would not ' . $forms['participle'], 'reason' => 'missing_have'],
            ]
            : [
                ['value' => $answer, 'reason' => 'correct'],
                ['value' => 'would ' . $forms['base'], 'reason' => 'second_conditional'],
                ['value' => 'will ' . $forms['base'], 'reason' => 'first_conditional'],
                ['value' => $forms['past'], 'reason' => 'past_simple'],
            ];

        return [
            'type' => $negative ? 'result_would_not_have' : 'result_would_have',
            'subject' => $subject,
            'verb' => $forms['base'],
            'verb_hint' => $negative ? 'not ' . $forms['base'] : $forms['base'],
            'answer' => $answer,
            'options' => $options,
        ];
    }

    private function makeEntry(string $level, string $source, array $tenseTags, string $question, array $markers): array
    {
        return [
            'level' => $level,
            'source' => $source,
            'tense_tags' => $tenseTags,
            'question' => $question,
            'markers' => $markers,
        ];
    }
}
