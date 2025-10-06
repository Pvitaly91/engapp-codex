<?php

namespace Database\Seeders\Ai;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class SecondConditionalComprehensiveAiSeeder extends QuestionSeeder
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
        'result_would_simple' => "Час: Second Conditional.\nФормула: **would + V1** (наприклад, «would + %verb%»).\nПояснення: Результат описуємо модальним would та базовим дієсловом без to.\нПриклад: *%example%*",
        'result_would_be_able' => "Час: Second Conditional.\nФормула: **would + be able to + V1** (наприклад, «would be able to %verb%»).\nПояснення: Щоб говорити про можливість як результат, використовуємо be able to після would.\nПриклад: *%example%*",
        'result_would_negative' => "Час: Second Conditional.\nФормула: **would not + V1** (наприклад, «would not %verb%»).\nПояснення: Заперечення результату утворюємо через would not (wouldn't) та базове дієслово.\nПриклад: *%example%*",
        'result_would_have_to' => "Час: Second Conditional.\nФормула: **would have to + V1**.\nПояснення: Щоб описати вимушену дію в результаті, використовуємо have to після would.\nПриклад: *%example%*",
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
        'result_would_have_to' => [
            'correct' => "✅ «%option%» правильно, бо виражає вимушеність через would have to + V1.\nПриклад: *%example%*",
            'first_conditional' => "❌ «%option%» з «will have to» описує реальне майбутнє, не уявну ситуацію.\nПриклад: *%example%*",
            'present_simple' => "❌ «%option%» — Present Simple; другий умовний потребує would have to.\nПриклад: *%example%*",
            'third_conditional' => "❌ «%option%» — модель Third Conditional (would have had to).\nПриклад: *%example%*",
            'default' => "❌ Неправильний варіант.\nПриклад: *%example%*",
        ],
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'AI Second Conditional'])->id;
 
        $sourceMap = [
            'negative_past' => Source::firstOrCreate(['name' => 'AI Second Conditional Negative Past'])->id,
            'negative_present' => Source::firstOrCreate(['name' => 'AI Second Conditional Negative Present'])->id,
            'negative_future' => Source::firstOrCreate(['name' => 'AI Second Conditional Negative Future'])->id,
            'interrogative_past' => Source::firstOrCreate(['name' => 'AI Second Conditional Interrogative Past'])->id,
            'interrogative_present' => Source::firstOrCreate(['name' => 'AI Second Conditional Interrogative Present'])->id,
            'interrogative_future' => Source::firstOrCreate(['name' => 'AI Second Conditional Interrogative Future'])->id,
        ];

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
                'source_id' => $sourceMap[$entry['group']] ?? null,
                'flag' => 2,
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

    private function buildHint(string $type, array $context, string $example): string
    {
        $template = $this->hintTemplates[$type] ?? null;

        if (! $template) {
            return '';
        }

        return strtr($template, [
            '%subject%' => $context['subject'] ?? '',
            '%verb%' => $context['verb'] ?? '',
            '%example%' => $example,
        ]);
    }

    private function buildExplanation(string $type, string $reason, string $option, array $context, string $example): string
    {
        $template = $this->explanationTemplates[$type][$reason] ?? $this->explanationTemplates[$type]['default'] ?? '';

        return strtr($template, [
            '%option%' => $option,
            '%subject%' => $context['subject'] ?? '',
            '%verb%' => $context['verb'] ?? '',
            '%example%' => $example,
        ]);
    }

    private function questionEntries(): array
    {
        $entries = [];

        // A1 level entries (single-blank focus for simpler practice)
        $entries = array_merge($entries, [
            [
                'level' => 'A1',
                'group' => 'negative_past',
                'question' => 'If Mia {a1} her keys yesterday, she {a2} the club meeting today.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('Mia', 'lose', 'lost'),
                    'a2' => $this->markerResultWouldNegative('she', 'reach', 'would not reach'),
                ],
            ],
            [
                'level' => 'A1',
                'group' => 'negative_past',
                'question' => 'If Leo {a1} the bus last night, he {a2} on time for breakfast today.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('Leo', 'miss', 'missed'),
                    'a2' => $this->markerResultWouldNegative('he', 'be', 'would not be'),
                ],
            ],
            [
                'level' => 'A1',
                'group' => 'negative_past',
                'question' => 'If Sara {a1} her notes yesterday, she {a2} so nervous now.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('Sara', 'forget', 'forgot'),
                    'a2' => $this->markerResultWouldNegative('she', 'feel', 'would not feel'),
                ],
            ],
            [
                'level' => 'A1',
                'group' => 'negative_past',
                'question' => 'If Amir {a1} the storm warning last week, he {a2} relaxed about the trip today.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('Amir', 'hear', 'heard'),
                    'a2' => $this->markerResultWouldNegative('he', 'stay', 'would not stay'),
                ],
            ],
            [
                'level' => 'A1',
                'group' => 'negative_present',
                'question' => 'If Mia {a1} stuck at work today, she {a2} the picnic with us.',
                'markers' => [
                    'a1' => $this->markerIfWere('Mia'),
                    'a2' => $this->markerResultWouldNegative('she', 'join', 'would not join'),
                ],
            ],
            [
                'level' => 'A1',
                'group' => 'negative_present',
                'question' => 'If we {a1} so tired this afternoon, we {a2} our bikes in the park.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('we', 'feel', 'felt'),
                    'a2' => $this->markerResultWouldNegative('we', 'ride', 'would not ride'),
                ],
            ],
            [
                'level' => 'A1',
                'group' => 'negative_present',
                'question' => 'If the cafe {a1} closed today, locals {a2} their usual lunch inside.',
                'markers' => [
                    'a1' => $this->markerIfWere('the cafe'),
                    'a2' => $this->markerResultWouldNegative('they', 'eat', 'would not eat'),
                ],
            ],
            [
                'level' => 'A1',
                'group' => 'negative_present',
                'question' => 'If the weather {a1} terrible today, the kids {a2} outside.',
                'markers' => [
                    'a1' => $this->markerIfWere('it'),
                    'a2' => $this->markerResultWouldNegative('they', 'play', 'would not play'),
                ],
            ],
            [
                'level' => 'A1',
                'group' => 'negative_future',
                'question' => 'If Julia {a1} late tomorrow, she {a2} the museum tour.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('Julia', 'arrive', 'arrived'),
                    'a2' => $this->markerResultWouldNegative('she', 'join', 'would not join'),
                ],
            ],
            [
                'level' => 'A1',
                'group' => 'negative_future',
                'question' => 'If the bus {a1} late tomorrow morning, we {a2} the early meeting.',
                'markers' => [
                    'a1' => $this->markerIfWere('the bus'),
                    'a2' => $this->markerResultWouldNegative('we', 'make', 'would not make'),
                ],
            ],
            [
                'level' => 'A1',
                'group' => 'negative_future',
                'question' => 'If our coach {a1} sick tomorrow, the team {a2} the new play.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('our coach', 'feel', 'felt'),
                    'a2' => $this->markerResultWouldNegative('they', 'practice', 'would not practice'),
                ],
            ],
            [
                'level' => 'A1',
                'group' => 'negative_future',
                'question' => 'If the weather {a1} stormy tomorrow night, the neighbors {a2} their lanterns outside.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('it', 'turn', 'turned'),
                    'a2' => $this->markerResultWouldNegative('they', 'leave', 'would not leave'),
                ],
            ],
            [
                'level' => 'A1',
                'group' => 'interrogative_past',
                'question' => 'If you found an old diary yesterday, would you {a1} it with your friend?',
                'markers' => [
                    'a1' => $this->markerResultWouldSimple('you', 'share', 'share', null, $this->baseVerbOptions('share', 'shared')),
                ],
            ],
            [
                'level' => 'A1',
                'group' => 'interrogative_past',
                'question' => 'If your brother saw a shooting star last night, would he {a1} a wish?',
                'markers' => [
                    'a1' => $this->markerResultWouldSimple('he', 'make', 'make', null, $this->baseVerbOptions('make', 'made')),
                ],
            ],
            [
                'level' => 'A1',
                'group' => 'interrogative_past',
                'question' => 'If they discovered a lost puppy yesterday, would they {a1} it home?',
                'markers' => [
                    'a1' => $this->markerResultWouldSimple('they', 'take', 'take', null, $this->baseVerbOptions('take', 'took')),
                ],
            ],
            [
                'level' => 'A1',
                'group' => 'interrogative_past',
                'question' => 'If Maria heard a rumor yesterday, would she {a1} it to you?',
                'markers' => [
                    'a1' => $this->markerResultWouldSimple('she', 'tell', 'tell', null, $this->baseVerbOptions('tell', 'told')),
                ],
            ],
            [
                'level' => 'A1',
                'group' => 'interrogative_present',
                'question' => 'If you were free today, would you {a1} a picnic in the park?',
                'markers' => [
                    'a1' => $this->markerResultWouldSimple('you', 'plan', 'plan', null, $this->baseVerbOptions('plan', 'planned')),
                ],
            ],
            [
                'level' => 'A1',
                'group' => 'interrogative_present',
                'question' => 'If the teacher were absent today, would we {a1} quietly in class?',
                'markers' => [
                    'a1' => $this->markerResultWouldSimple('we', 'sit', 'sit', null, $this->baseVerbOptions('sit', 'sat')),
                ],
            ],
            [
                'level' => 'A1',
                'group' => 'interrogative_present',
                'question' => 'If the shop offered discounts today, would you {a1} new shoes?',
                'markers' => [
                    'a1' => $this->markerResultWouldSimple('you', 'buy', 'buy', null, $this->baseVerbOptions('buy', 'bought')),
                ],
            ],
            [
                'level' => 'A1',
                'group' => 'interrogative_present',
                'question' => 'If it snowed right now, would the children {a1} outside?',
                'markers' => [
                    'a1' => $this->markerResultWouldSimple('they', 'play', 'play', null, $this->baseVerbOptions('play', 'played')),
                ],
            ],
            [
                'level' => 'A1',
                'group' => 'interrogative_future',
                'question' => 'If you won a ticket tomorrow, would you {a1} to the concert?',
                'markers' => [
                    'a1' => $this->markerResultWouldSimple('you', 'go', 'go', null, $this->baseVerbOptions('go', 'went')),
                ],
            ],
            [
                'level' => 'A1',
                'group' => 'interrogative_future',
                'question' => 'If our team faced a strong rival next week, would we {a1} extra practice?',
                'markers' => [
                    'a1' => $this->markerResultWouldSimple('we', 'do', 'do', null, [
                        ['value' => 'do', 'reason' => 'correct'],
                        ['value' => 'does', 'reason' => 'third_person'],
                        ['value' => 'will do', 'reason' => 'first_conditional'],
                        ['value' => 'did', 'reason' => 'past_simple'],
                    ]),
                ],
            ],
            [
                'level' => 'A1',
                'group' => 'interrogative_future',
                'question' => 'If the city built a new pool next year, would families {a1} there every weekend?',
                'markers' => [
                    'a1' => $this->markerResultWouldSimple('they', 'swim', 'swim', null, $this->baseVerbOptions('swim', 'swam')),
                ],
            ],
            [
                'level' => 'A1',
                'group' => 'interrogative_future',
                'question' => 'If Lena got the day off tomorrow, would she {a1} a short trip?',
                'markers' => [
                    'a1' => $this->markerResultWouldSimple('she', 'take', 'take', null, $this->baseVerbOptions('take', 'took')),
                ],
            ],
        ]);

        // A2 level entries (introduce dual blanks for richer control practice)
        $entries = array_merge($entries, [
            [
                'level' => 'A2',
                'group' => 'negative_past',
                'question' => 'If Liza {a1} the library yesterday, she {a2} lost in her project today.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('Liza', 'visit', 'visited'),
                    'a2' => $this->markerResultWouldNegative('she', 'feel', 'would not feel'),
                ],
            ],
            [
                'level' => 'A2',
                'group' => 'negative_past',
                'question' => 'If the volunteers {a1} the warning last night, they {a2} so calm this morning.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the volunteers', 'notice', 'noticed'),
                    'a2' => $this->markerResultWouldNegative('they', 'be', 'would not be'),
                ],
            ],
            [
                'level' => 'A2',
                'group' => 'negative_past',
                'question' => 'If Hugo {a1} a map yesterday, he {a2} worried about the trail now.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('Hugo', 'bring', 'brought'),
                    'a2' => $this->markerResultWouldNegative('he', 'feel', 'would not feel'),
                ],
            ],
            [
                'level' => 'A2',
                'group' => 'negative_past',
                'question' => 'If the choir {a1} their rehearsal last week, they {a2} unsure before the concert.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the choir', 'finish', 'finished'),
                    'a2' => $this->markerResultWouldNegative('they', 'sound', 'would not sound'),
                ],
            ],
            [
                'level' => 'A2',
                'group' => 'negative_present',
                'question' => 'If Carla {a1} more confident today, she {a2} anxious about speaking.',
                'markers' => [
                    'a1' => $this->markerIfWere('Carla'),
                    'a2' => $this->markerResultWouldNegative('she', 'feel', 'would not feel'),
                ],
            ],
            [
                'level' => 'A2',
                'group' => 'negative_present',
                'question' => 'If the team {a1} extra rest now, they {a2} ready for the game.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the team', 'have', 'had'),
                    'a2' => $this->markerResultWouldNegative('they', 'be', 'would not be'),
                ],
            ],
            [
                'level' => 'A2',
                'group' => 'negative_present',
                'question' => 'If I {a1} so many tasks today, I {a2} stressed about the evening.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('I', 'have', 'had'),
                    'a2' => $this->markerResultWouldNegative('I', 'be', 'would not be'),
                ],
            ],
            [
                'level' => 'A2',
                'group' => 'negative_present',
                'question' => 'If the printer {a1} jammed right now, we {a2} our files on time.',
                'markers' => [
                    'a1' => $this->markerIfWere('the printer'),
                    'a2' => $this->markerResultWouldNegative('we', 'send', 'would not send'),
                ],
            ],
            [
                'level' => 'A2',
                'group' => 'negative_future',
                'question' => 'If Mira {a1} late tomorrow evening, she {a2} the welcome speech.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('Mira', 'arrive', 'arrived'),
                    'a2' => $this->markerResultWouldNegative('she', 'give', 'would not give'),
                ],
            ],
            [
                'level' => 'A2',
                'group' => 'negative_future',
                'question' => 'If the ferry {a1} canceled tomorrow morning, tourists {a2} the island tour.',
                'markers' => [
                    'a1' => $this->markerIfWere('the ferry'),
                    'a2' => $this->markerResultWouldNegative('they', 'take', 'would not take'),
                ],
            ],
            [
                'level' => 'A2',
                'group' => 'negative_future',
                'question' => 'If we {a1} a backup plan next week, we {a2} calm before the event.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('we', 'have', 'had'),
                    'a2' => $this->markerResultWouldNegative('we', 'stay', 'would not stay'),
                ],
            ],
            [
                'level' => 'A2',
                'group' => 'negative_future',
                'question' => 'If the chef {a1} sick tomorrow, the restaurant {a2} the new menu.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the chef', 'feel', 'felt'),
                    'a2' => $this->markerResultWouldNegative('it', 'serve', 'would not serve'),
                ],
            ],
            [
                'level' => 'A2',
                'group' => 'interrogative_past',
                'question' => 'If you {a1} a hidden letter yesterday, would you {a2} it to your sister?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('you', 'find', 'found'),
                    'a2' => $this->markerResultWouldSimple('you', 'show', 'show', null, $this->baseVerbOptions('show', 'showed')),
                ],
            ],
            [
                'level' => 'A2',
                'group' => 'interrogative_past',
                'question' => 'If Marco {a1} an injured bird yesterday, would he {a2} help from a vet?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('Marco', 'see', 'saw'),
                    'a2' => $this->markerResultWouldSimple('he', 'seek', 'seek', null, $this->baseVerbOptions('seek', 'sought')),
                ],
            ],
            [
                'level' => 'A2',
                'group' => 'interrogative_past',
                'question' => 'If they {a1} a secret path last night, would they {a2} anyone?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('they', 'discover', 'discovered'),
                    'a2' => $this->markerResultWouldSimple('they', 'tell', 'tell', null, $this->baseVerbOptions('tell', 'told')),
                ],
            ],
            [
                'level' => 'A2',
                'group' => 'interrogative_past',
                'question' => 'If Elena {a1} a strange message yesterday, would she {a2} it with the group?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('Elena', 'receive', 'received'),
                    'a2' => $this->markerResultWouldSimple('she', 'share', 'share', null, $this->baseVerbOptions('share', 'shared')),
                ],
            ],
            [
                'level' => 'A2',
                'group' => 'interrogative_present',
                'question' => 'If the manager {a1} here today, would we {a2} the plan differently?',
                'markers' => [
                    'a1' => $this->markerIfWere('the manager'),
                    'a2' => $this->markerResultWouldSimple('we', 'discuss', 'discuss', null, $this->baseVerbOptions('discuss', 'discussed')),
                ],
            ],
            [
                'level' => 'A2',
                'group' => 'interrogative_present',
                'question' => 'If you {a1} extra time this afternoon, would you {a2} the report?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('you', 'have', 'had'),
                    'a2' => $this->markerResultWouldSimple('you', 'finish', 'finish', null, $this->baseVerbOptions('finish', 'finished')),
                ],
            ],
            [
                'level' => 'A2',
                'group' => 'interrogative_present',
                'question' => 'If the students {a1} confident today, would they {a2} harder questions?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the students', 'feel', 'felt'),
                    'a2' => $this->markerResultWouldSimple('they', 'attempt', 'attempt', null, $this->baseVerbOptions('attempt', 'attempted')),
                ],
            ],
            [
                'level' => 'A2',
                'group' => 'interrogative_present',
                'question' => 'If the cafe {a1} quieter right now, would writers {a2} longer?',
                'markers' => [
                    'a1' => $this->markerIfWere('the cafe'),
                    'a2' => $this->markerResultWouldSimple('they', 'stay', 'stay', null, $this->baseVerbOptions('stay', 'stayed')),
                ],
            ],
            [
                'level' => 'A2',
                'group' => 'interrogative_future',
                'question' => 'If you {a1} a free weekend next month, would you {a2} a hiking trip?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('you', 'have', 'had'),
                    'a2' => $this->markerResultWouldSimple('you', 'plan', 'plan', null, $this->baseVerbOptions('plan', 'planned')),
                ],
            ],
            [
                'level' => 'A2',
                'group' => 'interrogative_future',
                'question' => 'If the company {a1} new funding next quarter, would it {a2} scholarships?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the company', 'receive', 'received'),
                    'a2' => $this->markerResultWouldSimple('it', 'offer', 'offer', null, $this->baseVerbOptions('offer', 'offered')),
                ],
            ],
            [
                'level' => 'A2',
                'group' => 'interrogative_future',
                'question' => 'If the festival {a1} bigger next summer, would visitors {a2} extra days?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the festival', 'grow', 'grew'),
                    'a2' => $this->markerResultWouldSimple('they', 'stay', 'stay', null, $this->baseVerbOptions('stay', 'stayed')),
                ],
            ],
            [
                'level' => 'A2',
                'group' => 'interrogative_future',
                'question' => 'If Nadia {a1} an invitation tomorrow, would she {a2} a speech?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('Nadia', 'get', 'got'),
                    'a2' => $this->markerResultWouldSimple('she', 'prepare', 'prepare', null, $this->baseVerbOptions('prepare', 'prepared')),
                ],
            ],
        ]);

        // B1 level entries (mix of triple blanks for nuanced reasoning)
        $entries = array_merge($entries, [
            [
                'level' => 'B1',
                'group' => 'negative_past',
                'question' => 'If Dario {a1} the contract last month, he {a2} so cautious now, and his partners {a3} so relaxed.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('Dario', 'review', 'reviewed'),
                    'a2' => $this->markerResultWouldNegative('he', 'feel', 'would not feel'),
                    'a3' => $this->markerResultWouldNegative('they', 'stay', 'would not stay'),
                ],
            ],
            [
                'level' => 'B1',
                'group' => 'negative_past',
                'question' => 'If the analysts {a1} the warning signs yesterday, their team {a2} surprised today, and the clients {a3} anxious.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the analysts', 'notice', 'noticed'),
                    'a2' => $this->markerResultWouldNegative('they', 'be', 'would not be'),
                    'a3' => $this->markerResultWouldNegative('they', 'feel', 'would not feel'),
                ],
            ],
            [
                'level' => 'B1',
                'group' => 'negative_past',
                'question' => 'If Lila {a1} her budget report last week, she {a2} stressed now, and her manager {a3} extra meetings.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('Lila', 'update', 'updated'),
                    'a2' => $this->markerResultWouldNegative('she', 'be', 'would not be'),
                    'a3' => $this->markerResultWouldNegative('he', 'schedule', 'would not schedule'),
                ],
            ],
            [
                'level' => 'B1',
                'group' => 'negative_past',
                'question' => 'If the architects {a1} the soil test last month, the project {a2} risky today, and investors {a3} uneasy.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the architects', 'complete', 'completed'),
                    'a2' => $this->markerResultWouldNegative('it', 'seem', 'would not seem'),
                    'a3' => $this->markerResultWouldNegative('they', 'feel', 'would not feel'),
                ],
            ],
            [
                'level' => 'B1',
                'group' => 'negative_present',
                'question' => 'If Victor {a1} more confident right now, he {a2} worried about presenting, and he {a3} the demo slides again.',
                'markers' => [
                    'a1' => $this->markerIfWere('Victor'),
                    'a2' => $this->markerResultWouldNegative('he', 'feel', 'would not feel'),
                    'a3' => $this->markerResultWouldNegative('he', 'review', 'would not review'),
                ],
            ],
            [
                'level' => 'B1',
                'group' => 'negative_present',
                'question' => 'If the interns {a1} their tasks today, the supervisor {a2} nervous, and the deadline {a3} uncertain.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the interns', 'organize', 'organized'),
                    'a2' => $this->markerResultWouldNegative('he', 'be', 'would not be'),
                    'a3' => $this->markerResultWouldNegative('it', 'seem', 'would not seem'),
                ],
            ],
            [
                'level' => 'B1',
                'group' => 'negative_present',
                'question' => 'If the city {a1} calmer today, residents {a2} uneasy, and tourists {a3} confused.',
                'markers' => [
                    'a1' => $this->markerIfWere('the city'),
                    'a2' => $this->markerResultWouldNegative('they', 'feel', 'would not feel'),
                    'a3' => $this->markerResultWouldNegative('they', 'get', 'would not get'),
                ],
            ],
            [
                'level' => 'B1',
                'group' => 'negative_present',
                'question' => 'If I {a1} such tight deadlines now, I {a2} tense, and my teammates {a3} extra support.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('I', 'have', 'had'),
                    'a2' => $this->markerResultWouldNegative('I', 'be', 'would not be'),
                    'a3' => $this->markerResultWouldNegative('they', 'need', 'would not need'),
                ],
            ],
            [
                'level' => 'B1',
                'group' => 'negative_future',
                'question' => 'If the crew {a1} late tomorrow night, the broadcast {a2} smooth, and viewers {a3} satisfied.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the crew', 'arrive', 'arrived'),
                    'a2' => $this->markerResultWouldNegative('it', 'look', 'would not look'),
                    'a3' => $this->markerResultWouldNegative('they', 'feel', 'would not feel'),
                ],
            ],
            [
                'level' => 'B1',
                'group' => 'negative_future',
                'question' => 'If the supplier {a1} the shipment next week, the store {a2} ready, and customers {a3} patient.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the supplier', 'delay', 'delayed'),
                    'a2' => $this->markerResultWouldNegative('it', 'be', 'would not be'),
                    'a3' => $this->markerResultWouldNegative('they', 'stay', 'would not stay'),
                ],
            ],
            [
                'level' => 'B1',
                'group' => 'negative_future',
                'question' => 'If our team {a1} approval tomorrow, we {a2} confident, and our partners {a3} supportive.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('our team', 'receive', 'received'),
                    'a2' => $this->markerResultWouldNegative('we', 'feel', 'would not feel'),
                    'a3' => $this->markerResultWouldNegative('they', 'remain', 'would not remain'),
                ],
            ],
            [
                'level' => 'B1',
                'group' => 'negative_future',
                'question' => 'If the engineers {a1} tired next week, the prototype {a2} steady, and investors {a3} relaxed.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the engineers', 'become', 'became'),
                    'a2' => $this->markerResultWouldNegative('it', 'stay', 'would not stay'),
                    'a3' => $this->markerResultWouldNegative('they', 'feel', 'would not feel'),
                ],
            ],
            [
                'level' => 'B1',
                'group' => 'interrogative_past',
                'question' => 'If you {a1} a rare book yesterday, would you {a2} it online or {a3} it for yourself?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('you', 'find', 'found'),
                    'a2' => $this->markerResultWouldSimple('you', 'sell', 'sell', null, $this->baseVerbOptions('sell', 'sold')),
                    'a3' => $this->markerResultWouldSimple('you', 'keep', 'keep', null, $this->baseVerbOptions('keep', 'kept')),
                ],
            ],
            [
                'level' => 'B1',
                'group' => 'interrogative_past',
                'question' => 'If Mira {a1} an urgent email yesterday, would she {a2} the client or {a3} the team?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('Mira', 'receive', 'received'),
                    'a2' => $this->markerResultWouldSimple('she', 'call', 'call', null, $this->baseVerbOptions('call', 'called')),
                    'a3' => $this->markerResultWouldSimple('she', 'inform', 'inform', null, $this->baseVerbOptions('inform', 'informed')),
                ],
            ],
            [
                'level' => 'B1',
                'group' => 'interrogative_past',
                'question' => 'If they {a1} a hidden trail last night, would they {a2} photos or {a3} it secret?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('they', 'spot', 'spotted'),
                    'a2' => $this->markerResultWouldSimple('they', 'take', 'take', null, $this->baseVerbOptions('take', 'took')),
                    'a3' => $this->markerResultWouldSimple('they', 'keep', 'keep', null, $this->baseVerbOptions('keep', 'kept')),
                ],
            ],
            [
                'level' => 'B1',
                'group' => 'interrogative_past',
                'question' => 'If he {a1} the last train yesterday, would he {a2} a taxi or {a3} a friend?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('he', 'miss', 'missed'),
                    'a2' => $this->markerResultWouldSimple('he', 'book', 'book', null, $this->baseVerbOptions('book', 'booked')),
                    'a3' => $this->markerResultWouldSimple('he', 'call', 'call', null, $this->baseVerbOptions('call', 'called')),
                ],
            ],
            [
                'level' => 'B1',
                'group' => 'interrogative_present',
                'question' => 'If the director {a1} available today, would we {a2} the proposal now or {a3} for tomorrow?',
                'markers' => [
                    'a1' => $this->markerIfWere('the director'),
                    'a2' => $this->markerResultWouldSimple('we', 'present', 'present', null, $this->baseVerbOptions('present', 'presented')),
                    'a3' => $this->markerResultWouldSimple('we', 'save', 'save', null, $this->baseVerbOptions('save', 'saved')),
                ],
            ],
            [
                'level' => 'B1',
                'group' => 'interrogative_present',
                'question' => 'If you {a1} more energy right now, would you {a2} another class or {a3} a break?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('you', 'have', 'had'),
                    'a2' => $this->markerResultWouldSimple('you', 'attend', 'attend', null, $this->baseVerbOptions('attend', 'attended')),
                    'a3' => $this->markerResultWouldSimple('you', 'take', 'take', null, $this->baseVerbOptions('take', 'took')),
                ],
            ],
            [
                'level' => 'B1',
                'group' => 'interrogative_present',
                'question' => 'If the cafe {a1} quieter today, would writers {a2} longer or {a3} home earlier?',
                'markers' => [
                    'a1' => $this->markerIfWere('the cafe'),
                    'a2' => $this->markerResultWouldSimple('they', 'stay', 'stay', null, $this->baseVerbOptions('stay', 'stayed')),
                    'a3' => $this->markerResultWouldSimple('they', 'leave', 'leave', null, $this->baseVerbOptions('leave', 'left')),
                ],
            ],
            [
                'level' => 'B1',
                'group' => 'interrogative_present',
                'question' => 'If the kids {a1} bored right now, would they {a2} a game or {a3} for tablets?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the kids', 'feel', 'felt'),
                    'a2' => $this->markerResultWouldSimple('they', 'start', 'start', null, $this->baseVerbOptions('start', 'started')),
                    'a3' => $this->markerResultWouldSimple('they', 'ask', 'ask', null, $this->baseVerbOptions('ask', 'asked')),
                ],
            ],
            [
                'level' => 'B1',
                'group' => 'interrogative_future',
                'question' => 'If you {a1} a long weekend next month, would you {a2} a trip or {a3} at home?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('you', 'get', 'got'),
                    'a2' => $this->markerResultWouldSimple('you', 'plan', 'plan', null, $this->baseVerbOptions('plan', 'planned')),
                    'a3' => $this->markerResultWouldSimple('you', 'rest', 'rest', null, $this->baseVerbOptions('rest', 'rested')),
                ],
            ],
            [
                'level' => 'B1',
                'group' => 'interrogative_future',
                'question' => 'If the city {a1} new funding next year, would it {a2} a park or {a3} a museum?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the city', 'secure', 'secured'),
                    'a2' => $this->markerResultWouldSimple('it', 'build', 'build', null, $this->baseVerbOptions('build', 'built')),
                    'a3' => $this->markerResultWouldSimple('it', 'open', 'open', null, $this->baseVerbOptions('open', 'opened')),
                ],
            ],
            [
                'level' => 'B1',
                'group' => 'interrogative_future',
                'question' => 'If the choir {a1} invited next summer, would they {a2} abroad or {a3} local shows?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the choir', 'get', 'got'),
                    'a2' => $this->markerResultWouldSimple('they', 'tour', 'tour', null, $this->baseVerbOptions('tour', 'toured')),
                    'a3' => $this->markerResultWouldSimple('they', 'keep', 'keep', null, $this->baseVerbOptions('keep', 'kept')),
                ],
            ],
            [
                'level' => 'B1',
                'group' => 'interrogative_future',
                'question' => 'If Lena {a1} more savings next month, would she {a2} a car or {a3} investing?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('Lena', 'have', 'had'),
                    'a2' => $this->markerResultWouldSimple('she', 'buy', 'buy', null, $this->baseVerbOptions('buy', 'bought')),
                    'a3' => $this->markerResultWouldSimple('she', 'continue', 'continue', null, $this->baseVerbOptions('continue', 'continued')),
                ],
            ],
        ]);

        // B2 level entries (introduce modal contrasts and scenario layering)
        $entries = array_merge($entries, [
            [
                'level' => 'B2',
                'group' => 'negative_past',
                'question' => 'If Serena {a1} the figures yesterday, her team {a2} so defensive now, and the auditors {a3} skeptical.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('Serena', 'recheck', 'rechecked'),
                    'a2' => $this->markerResultWouldNegative('they', 'feel', 'would not feel'),
                    'a3' => $this->markerResultWouldNegative('they', 'be', 'would not be'),
                ],
            ],
            [
                'level' => 'B2',
                'group' => 'negative_past',
                'question' => 'If the committee {a1} the poll results last week, the mayor {a2} pressured today, and the press {a3} relentless.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the committee', 'analyze', 'analyzed'),
                    'a2' => $this->markerResultWouldNegative('he', 'feel', 'would not feel'),
                    'a3' => $this->markerResultWouldNegative('they', 'stay', 'would not stay'),
                ],
            ],
            [
                'level' => 'B2',
                'group' => 'negative_past',
                'question' => 'If Pavel {a1} the client briefing yesterday, his proposal {a2} uncertain now, and stakeholders {a3} doubtful.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('Pavel', 'attend', 'attended'),
                    'a2' => $this->markerResultWouldNegative('it', 'seem', 'would not seem'),
                    'a3' => $this->markerResultWouldNegative('they', 'feel', 'would not feel'),
                ],
            ],
            [
                'level' => 'B2',
                'group' => 'negative_past',
                'question' => 'If the lab {a1} the control samples last month, the data {a2} suspect today, and reviewers {a3} uneasy.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the lab', 'calibrate', 'calibrated'),
                    'a2' => $this->markerResultWouldNegative('it', 'look', 'would not look'),
                    'a3' => $this->markerResultWouldNegative('they', 'feel', 'would not feel'),
                ],
            ],
            [
                'level' => 'B2',
                'group' => 'negative_present',
                'question' => 'If Lina {a1} trust her instincts today, she {a2} stuck on minor details, and she {a3} every sentence again.',
                'markers' => [
                    'a1' => $this->markerIfModalCould('Lina', 'trust'),
                    'a2' => $this->markerResultWouldNegative('she', 'get', 'would not get'),
                    'a3' => $this->markerResultWouldNegative('she', 'revisit', 'would not revisit'),
                ],
            ],
            [
                'level' => 'B2',
                'group' => 'negative_present',
                'question' => 'If the consultants {a1} see the dashboard now, they {a2} hesitant, and the director {a3} updates hourly.',
                'markers' => [
                    'a1' => $this->markerIfModalCould('the consultants', 'see'),
                    'a2' => $this->markerResultWouldNegative('they', 'feel', 'would not feel'),
                    'a3' => $this->markerResultWouldNegative('he', 'request', 'would not request'),
                ],
            ],
            [
                'level' => 'B2',
                'group' => 'negative_present',
                'question' => 'If the town {a1} calmer this week, residents {a2} uneasy, and visitors {a3} alternative plans.',
                'markers' => [
                    'a1' => $this->markerIfWere('the town'),
                    'a2' => $this->markerResultWouldNegative('they', 'feel', 'would not feel'),
                    'a3' => $this->markerResultWouldNegative('they', 'make', 'would not make'),
                ],
            ],
            [
                'level' => 'B2',
                'group' => 'negative_present',
                'question' => 'If we {a1} more clarity today, we {a2} tense, and our partners {a3} reassurance.',
                'markers' => [
                    'a1' => $this->markerIfModalCould('we', 'gain'),
                    'a2' => $this->markerResultWouldNegative('we', 'be', 'would not be'),
                    'a3' => $this->markerResultWouldNegative('they', 'request', 'would not request'),
                ],
            ],
            [
                'level' => 'B2',
                'group' => 'negative_future',
                'question' => 'If the board {a1} a decision tomorrow, the department {a2} waiting, and suppliers {a3} worried.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the board', 'deliver', 'delivered'),
                    'a2' => $this->markerResultWouldNegative('it', 'keep', 'would not keep'),
                    'a3' => $this->markerResultWouldNegative('they', 'feel', 'would not feel'),
                ],
            ],
            [
                'level' => 'B2',
                'group' => 'negative_future',
                'question' => 'If the orchestra {a1} late next weekend, the gala {a2} polished, and donors {a3} confident.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the orchestra', 'arrive', 'arrived'),
                    'a2' => $this->markerResultWouldNegative('it', 'sound', 'would not sound'),
                    'a3' => $this->markerResultWouldNegative('they', 'stay', 'would not stay'),
                ],
            ],
            [
                'level' => 'B2',
                'group' => 'negative_future',
                'question' => 'If the cloud service {a1} tomorrow night, our team {a2} awake, and clients {a3} reassured.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the cloud service', 'fail', 'failed'),
                    'a2' => $this->markerResultWouldNegative('we', 'stay', 'would not stay'),
                    'a3' => $this->markerResultWouldNegative('they', 'feel', 'would not feel'),
                ],
            ],
            [
                'level' => 'B2',
                'group' => 'negative_future',
                'question' => 'If the editor {a1} revisions next week, the author {a2} calm, and the publisher {a3} patient.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the editor', 'request', 'requested'),
                    'a2' => $this->markerResultWouldNegative('he', 'be', 'would not be'),
                    'a3' => $this->markerResultWouldNegative('it', 'remain', 'would not remain'),
                ],
            ],
            [
                'level' => 'B2',
                'group' => 'interrogative_past',
                'question' => 'If you {a1} a leaked memo yesterday, would you {a2} the press or {a3} silent?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('you', 'discover', 'discovered'),
                    'a2' => $this->markerResultWouldSimple('you', 'inform', 'inform', null, $this->baseVerbOptions('inform', 'informed')),
                    'a3' => $this->markerResultWouldSimple('you', 'stay', 'stay', null, $this->baseVerbOptions('stay', 'stayed')),
                ],
            ],
            [
                'level' => 'B2',
                'group' => 'interrogative_past',
                'question' => 'If Raj {a1} an empty studio yesterday, would he {a2} a workshop or {a3} it closed?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('Raj', 'find', 'found'),
                    'a2' => $this->markerResultWouldSimple('he', 'host', 'host', null, $this->baseVerbOptions('host', 'hosted')),
                    'a3' => $this->markerResultWouldSimple('he', 'leave', 'leave', null, $this->baseVerbOptions('leave', 'left')),
                ],
            ],
            [
                'level' => 'B2',
                'group' => 'interrogative_past',
                'question' => 'If they {a1} a flaw last night, would they {a2} admit it or {a3} ignore it?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('they', 'detect', 'detected'),
                    'a2' => $this->markerResultWouldSimple('they', 'acknowledge', 'acknowledge', null, $this->baseVerbOptions('acknowledge', 'acknowledged')),
                    'a3' => $this->markerResultWouldSimple('they', 'ignore', 'ignore', null, $this->baseVerbOptions('ignore', 'ignored')),
                ],
            ],
            [
                'level' => 'B2',
                'group' => 'interrogative_past',
                'question' => 'If Sofia {a1} the final bus yesterday, would she {a2} a ride-share or {a3} a coworker?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('Sofia', 'miss', 'missed'),
                    'a2' => $this->markerResultWouldSimple('she', 'book', 'book', null, $this->baseVerbOptions('book', 'booked')),
                    'a3' => $this->markerResultWouldSimple('she', 'text', 'text', null, $this->baseVerbOptions('text', 'texted')),
                ],
            ],
            [
                'level' => 'B2',
                'group' => 'interrogative_present',
                'question' => 'If the advisory board {a1} here today, would we {a2} revise the plan or {a3} defend the draft?',
                'markers' => [
                    'a1' => $this->markerIfWere('the advisory board'),
                    'a2' => $this->markerResultWouldSimple('we', 'revise', 'revise', null, $this->baseVerbOptions('revise', 'revised')),
                    'a3' => $this->markerResultWouldSimple('we', 'defend', 'defend', null, $this->baseVerbOptions('defend', 'defended')),
                ],
            ],
            [
                'level' => 'B2',
                'group' => 'interrogative_present',
                'question' => 'If you {a1} clearer data now, would you {a2} pivot the project or {a3} stay the course?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('you', 'have', 'had'),
                    'a2' => $this->markerResultWouldSimple('you', 'pivot', 'pivot', null, $this->baseVerbOptions('pivot', 'pivoted')),
                    'a3' => $this->markerResultWouldSimple('you', 'stay', 'stay', null, $this->baseVerbOptions('stay', 'stayed')),
                ],
            ],
            [
                'level' => 'B2',
                'group' => 'interrogative_present',
                'question' => 'If the newsroom {a1} quiet today, would reporters {a2} long-form pieces or {a3} quick updates?',
                'markers' => [
                    'a1' => $this->markerIfWere('the newsroom'),
                    'a2' => $this->markerResultWouldSimple('they', 'draft', 'draft', null, $this->baseVerbOptions('draft', 'drafted')),
                    'a3' => $this->markerResultWouldSimple('they', 'file', 'file', null, $this->baseVerbOptions('file', 'filed')),
                ],
            ],
            [
                'level' => 'B2',
                'group' => 'interrogative_present',
                'question' => 'If the volunteers {a1} energized right now, would they {a2} extra calls or {a3} the list to tomorrow?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the volunteers', 'feel', 'felt'),
                    'a2' => $this->markerResultWouldSimple('they', 'make', 'make', null, $this->baseVerbOptions('make', 'made')),
                    'a3' => $this->markerResultWouldSimple('they', 'push', 'push', null, $this->baseVerbOptions('push', 'pushed')),
                ],
            ],
            [
                'level' => 'B2',
                'group' => 'interrogative_future',
                'question' => 'If you {a1} a sabbatical next year, would you {a2} research abroad or {a3} in your hometown?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('you', 'secure', 'secured'),
                    'a2' => $this->markerResultWouldSimple('you', 'conduct', 'conduct', null, $this->baseVerbOptions('conduct', 'conducted')),
                    'a3' => $this->markerResultWouldSimple('you', 'remain', 'remain', null, $this->baseVerbOptions('remain', 'remained')),
                ],
            ],
            [
                'level' => 'B2',
                'group' => 'interrogative_future',
                'question' => 'If the firm {a1} a merger next quarter, would it {a2} hire new staff or {a3} pause recruiting?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the firm', 'announce', 'announced'),
                    'a2' => $this->markerResultWouldSimple('it', 'bring', 'bring', null, $this->baseVerbOptions('bring', 'brought')),
                    'a3' => $this->markerResultWouldSimple('it', 'pause', 'pause', null, $this->baseVerbOptions('pause', 'paused')),
                ],
            ],
            [
                'level' => 'B2',
                'group' => 'interrogative_future',
                'question' => 'If the festival {a1} more sponsors next summer, would organizers {a2} expand stages or {a3} ticket prices?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the festival', 'secure', 'secured'),
                    'a2' => $this->markerResultWouldSimple('they', 'expand', 'expand', null, $this->baseVerbOptions('expand', 'expanded')),
                    'a3' => $this->markerResultWouldSimple('they', 'freeze', 'freeze', null, $this->baseVerbOptions('freeze', 'froze')),
                ],
            ],
            [
                'level' => 'B2',
                'group' => 'interrogative_future',
                'question' => 'If Mara {a1} a grant tomorrow, would she {a2} equip her lab or {a3} create scholarships?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('Mara', 'win', 'won'),
                    'a2' => $this->markerResultWouldSimple('she', 'equip', 'equip', null, $this->baseVerbOptions('equip', 'equipped')),
                    'a3' => $this->markerResultWouldSimple('she', 'create', 'create', null, $this->baseVerbOptions('create', 'created')),
                ],
            ],
        ]);

        // C1 level entries (emphasize professional nuance and layered hypotheticals)
        $entries = array_merge($entries, [
            [
                'level' => 'C1',
                'group' => 'negative_past',
                'question' => 'If the research team {a1} the peer feedback last quarter, the article {a2} controversial now, and the panel {a3} critical.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the research team', 'integrate', 'integrated'),
                    'a2' => $this->markerResultWouldNegative('it', 'remain', 'would not remain'),
                    'a3' => $this->markerResultWouldNegative('they', 'sound', 'would not sound'),
                ],
            ],
            [
                'level' => 'C1',
                'group' => 'negative_past',
                'question' => 'If Amira {a1} the diplomatic memo yesterday, the delegation {a2} uncertain now, and sponsors {a3} doubtful.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('Amira', 'circulate', 'circulated'),
                    'a2' => $this->markerResultWouldNegative('they', 'be', 'would not be'),
                    'a3' => $this->markerResultWouldNegative('they', 'feel', 'would not feel'),
                ],
            ],
            [
                'level' => 'C1',
                'group' => 'negative_past',
                'question' => 'If the curators {a1} the provenance documents last month, the exhibition {a2} under review today, and collectors {a3} anxious.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the curators', 'verify', 'verified'),
                    'a2' => $this->markerResultWouldNegative('it', 'stay', 'would not stay'),
                    'a3' => $this->markerResultWouldNegative('they', 'feel', 'would not feel'),
                ],
            ],
            [
                'level' => 'C1',
                'group' => 'negative_past',
                'question' => 'If Jonas {a1} the encryption flaw last week, the firm {a2} defensive now, and regulators {a3} suspicious.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('Jonas', 'flag', 'flagged'),
                    'a2' => $this->markerResultWouldNegative('it', 'sound', 'would not sound'),
                    'a3' => $this->markerResultWouldNegative('they', 'remain', 'would not remain'),
                ],
            ],
            [
                'level' => 'C1',
                'group' => 'negative_present',
                'question' => 'If the advisory council {a1} aligned today, ministers {a2} divided, and analysts {a3} for clarifications.',
                'markers' => [
                    'a1' => $this->markerIfWere('the advisory council'),
                    'a2' => $this->markerResultWouldNegative('they', 'appear', 'would not appear'),
                    'a3' => $this->markerResultWouldNegative('they', 'ask', 'would not ask'),
                ],
            ],
            [
                'level' => 'C1',
                'group' => 'negative_present',
                'question' => 'If Leila {a1} trust the translation now, she {a2} hesitant to publish, and editors {a3} emergency meetings.',
                'markers' => [
                    'a1' => $this->markerIfModalCould('Leila', 'trust'),
                    'a2' => $this->markerResultWouldNegative('she', 'stay', 'would not stay'),
                    'a3' => $this->markerResultWouldNegative('they', 'call', 'would not call'),
                ],
            ],
            [
                'level' => 'C1',
                'group' => 'negative_present',
                'question' => 'If the laboratories {a1} share their notes today, reviewers {a2} skeptical, and journals {a3} warnings.',
                'markers' => [
                    'a1' => $this->markerIfModalCould('the laboratories', 'share'),
                    'a2' => $this->markerResultWouldNegative('they', 'remain', 'would not remain'),
                    'a3' => $this->markerResultWouldNegative('they', 'issue', 'would not issue'),
                ],
            ],
            [
                'level' => 'C1',
                'group' => 'negative_present',
                'question' => 'If we {a1} more reliable forecasts now, we {a2} restrained, and investors {a3} guidance.',
                'markers' => [
                    'a1' => $this->markerIfModalCould('we', 'obtain'),
                    'a2' => $this->markerResultWouldNegative('we', 'stay', 'would not stay'),
                    'a3' => $this->markerResultWouldNegative('they', 'request', 'would not request'),
                ],
            ],
            [
                'level' => 'C1',
                'group' => 'negative_future',
                'question' => 'If the coalition {a1} consensus next week, negotiations {a2} stalled, and observers {a3} skeptical.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the coalition', 'forge', 'forged'),
                    'a2' => $this->markerResultWouldNegative('they', 'remain', 'would not remain'),
                    'a3' => $this->markerResultWouldNegative('they', 'stay', 'would not stay'),
                ],
            ],
            [
                'level' => 'C1',
                'group' => 'negative_future',
                'question' => 'If the airline {a1} routes next month, travelers {a2} stranded, and agents {a3} overwhelmed.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the airline', 'revise', 'revised'),
                    'a2' => $this->markerResultWouldNegative('they', 'be', 'would not be'),
                    'a3' => $this->markerResultWouldNegative('they', 'feel', 'would not feel'),
                ],
            ],
            [
                'level' => 'C1',
                'group' => 'negative_future',
                'question' => 'If the museum {a1} the opening tomorrow, donors {a2} impatient, and guides {a3} anxious.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the museum', 'postpone', 'postponed'),
                    'a2' => $this->markerResultWouldNegative('they', 'grow', 'would not grow'),
                    'a3' => $this->markerResultWouldNegative('they', 'stay', 'would not stay'),
                ],
            ],
            [
                'level' => 'C1',
                'group' => 'negative_future',
                'question' => 'If our startup {a1} funding next quarter, we {a2} restrained, and partners {a3} distant.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('our startup', 'secure', 'secured'),
                    'a2' => $this->markerResultWouldNegative('we', 'remain', 'would not remain'),
                    'a3' => $this->markerResultWouldNegative('they', 'grow', 'would not grow'),
                ],
            ],
            [
                'level' => 'C1',
                'group' => 'interrogative_past',
                'question' => 'If you {a1} a confidential cable yesterday, would you {a2} brief the ambassador or {a3} archive it?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('you', 'intercept', 'intercepted'),
                    'a2' => $this->markerResultWouldSimple('you', 'brief', 'brief', null, $this->baseVerbOptions('brief', 'briefed')),
                    'a3' => $this->markerResultWouldSimple('you', 'archive', 'archive', null, $this->baseVerbOptions('archive', 'archived')),
                ],
            ],
            [
                'level' => 'C1',
                'group' => 'interrogative_past',
                'question' => 'If the scientists {a1} a breakthrough yesterday, would they {a2} announce it or {a3} wait for peer review?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the scientists', 'record', 'recorded'),
                    'a2' => $this->markerResultWouldSimple('they', 'announce', 'announce', null, $this->baseVerbOptions('announce', 'announced')),
                    'a3' => $this->markerResultWouldSimple('they', 'wait', 'wait', null, $this->baseVerbOptions('wait', 'waited')),
                ],
            ],
            [
                'level' => 'C1',
                'group' => 'interrogative_past',
                'question' => 'If Mara {a1} an error last night, would she {a2} halt the release or {a3} push forward?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('Mara', 'detect', 'detected'),
                    'a2' => $this->markerResultWouldSimple('she', 'halt', 'halt', null, $this->baseVerbOptions('halt', 'halted')),
                    'a3' => $this->markerResultWouldSimple('she', 'push', 'push', null, $this->baseVerbOptions('push', 'pushed')),
                ],
            ],
            [
                'level' => 'C1',
                'group' => 'interrogative_past',
                'question' => 'If the auditors {a1} irregularities yesterday, would they {a2} notify investors or {a3} keep silent?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the auditors', 'trace', 'traced'),
                    'a2' => $this->markerResultWouldSimple('they', 'notify', 'notify', null, $this->baseVerbOptions('notify', 'notified')),
                    'a3' => $this->markerResultWouldSimple('they', 'keep', 'keep', null, $this->baseVerbOptions('keep', 'kept')),
                ],
            ],
            [
                'level' => 'C1',
                'group' => 'interrogative_present',
                'question' => 'If the council {a1} present today, would we {a2} restructure the agenda or {a3} maintain the schedule?',
                'markers' => [
                    'a1' => $this->markerIfWere('the council'),
                    'a2' => $this->markerResultWouldSimple('we', 'restructure', 'restructure', null, $this->baseVerbOptions('restructure', 'restructured')),
                    'a3' => $this->markerResultWouldSimple('we', 'maintain', 'maintain', null, $this->baseVerbOptions('maintain', 'maintained')),
                ],
            ],
            [
                'level' => 'C1',
                'group' => 'interrogative_present',
                'question' => 'If you {a1} unlimited access right now, would you {a2} audit the files or {a3} delegate it?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('you', 'have', 'had'),
                    'a2' => $this->markerResultWouldSimple('you', 'audit', 'audit', null, $this->baseVerbOptions('audit', 'audited')),
                    'a3' => $this->markerResultWouldSimple('you', 'delegate', 'delegate', null, $this->baseVerbOptions('delegate', 'delegated')),
                ],
            ],
            [
                'level' => 'C1',
                'group' => 'interrogative_present',
                'question' => 'If the newsroom {a1} calm today, would editors {a2} craft long reads or {a3} chase breaking alerts?',
                'markers' => [
                    'a1' => $this->markerIfWere('the newsroom'),
                    'a2' => $this->markerResultWouldSimple('they', 'craft', 'craft', null, $this->baseVerbOptions('craft', 'crafted')),
                    'a3' => $this->markerResultWouldSimple('they', 'chase', 'chase', null, $this->baseVerbOptions('chase', 'chased')),
                ],
            ],
            [
                'level' => 'C1',
                'group' => 'interrogative_present',
                'question' => 'If the negotiators {a1} optimistic this afternoon, would they {a2} sign the draft or {a3} table it?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the negotiators', 'feel', 'felt'),
                    'a2' => $this->markerResultWouldSimple('they', 'sign', 'sign', null, $this->baseVerbOptions('sign', 'signed')),
                    'a3' => $this->markerResultWouldSimple('they', 'table', 'table', null, $this->baseVerbOptions('table', 'tabled')),
                ],
            ],
            [
                'level' => 'C1',
                'group' => 'interrogative_future',
                'question' => 'If you {a1} the fellowship next year, would you {a2} publish abroad or {a3} mentor locally?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('you', 'win', 'won'),
                    'a2' => $this->markerResultWouldSimple('you', 'publish', 'publish', null, $this->baseVerbOptions('publish', 'published')),
                    'a3' => $this->markerResultWouldSimple('you', 'mentor', 'mentor', null, $this->baseVerbOptions('mentor', 'mentored')),
                ],
            ],
            [
                'level' => 'C1',
                'group' => 'interrogative_future',
                'question' => 'If the firm {a1} regulatory approval next quarter, would it {a2} expand globally or {a3} consolidate at home?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the firm', 'secure', 'secured'),
                    'a2' => $this->markerResultWouldSimple('it', 'expand', 'expand', null, $this->baseVerbOptions('expand', 'expanded')),
                    'a3' => $this->markerResultWouldSimple('it', 'consolidate', 'consolidate', null, $this->baseVerbOptions('consolidate', 'consolidated')),
                ],
            ],
            [
                'level' => 'C1',
                'group' => 'interrogative_future',
                'question' => 'If the choir {a1} a world tour next summer, would they {a2} partner with orchestras or {a3} perform solo sets?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the choir', 'plan', 'planned'),
                    'a2' => $this->markerResultWouldSimple('they', 'partner', 'partner', null, $this->baseVerbOptions('partner', 'partnered')),
                    'a3' => $this->markerResultWouldSimple('they', 'perform', 'perform', null, $this->baseVerbOptions('perform', 'performed')),
                ],
            ],
            [
                'level' => 'C1',
                'group' => 'interrogative_future',
                'question' => 'If Elena {a1} new investors next month, would she {a2} launch the app or {a3} refine the prototype?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('Elena', 'attract', 'attracted'),
                    'a2' => $this->markerResultWouldSimple('she', 'launch', 'launch', null, $this->baseVerbOptions('launch', 'launched')),
                    'a3' => $this->markerResultWouldSimple('she', 'refine', 'refine', null, $this->baseVerbOptions('refine', 'refined')),
                ],
            ],
        ]);

        // C2 level entries (complex policy contexts with layered obligations)
        $entries = array_merge($entries, [
            [
                'level' => 'C2',
                'group' => 'negative_past',
                'question' => 'If the constitutional committee {a1} the clause last year, the court {a2} debating it now, lawmakers {a3} justify every amendment, and the oversight {a4} be able to fast-track reforms.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the constitutional committee', 'revise', 'revised'),
                    'a2' => $this->markerResultWouldNegative('it', 'keep', 'would not keep'),
                    'a3' => $this->markerResultWouldNegative('they', 'have to justify', 'would not have to justify', 'not justify', $this->negativeHaveToOptions('justify')),
                    'a4' => $this->markerResultWouldBeAble('it', 'fast-track', 'would be able to fast-track'),
                ],
            ],
            [
                'level' => 'C2',
                'group' => 'negative_past',
                'question' => 'If Helena {a1} the whistleblower dossier yesterday, the agency {a2} defensive now, directors {a3} reassure stakeholders, and crisis managers {a4} have to issue statements.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('Helena', 'compile', 'compiled'),
                    'a2' => $this->markerResultWouldNegative('it', 'sound', 'would not sound'),
                    'a3' => $this->markerResultWouldNegative('they', 'have to reassure', 'would not have to reassure', 'not reassure', $this->negativeHaveToOptions('reassure')),
                    'a4' => $this->markerResultWouldHaveTo('they', 'issue', 'would have to issue'),
                ],
            ],
            [
                'level' => 'C2',
                'group' => 'negative_past',
                'question' => 'If the consortium {a1} the audit trail last quarter, investors {a2} restless today, analysts {a3} frame urgent questions, and regulators {a4} be able to endorse the report.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the consortium', 'preserve', 'preserved'),
                    'a2' => $this->markerResultWouldNegative('they', 'stay', 'would not stay'),
                    'a3' => $this->markerResultWouldNegative('they', 'have to frame', 'would not have to frame', 'not frame', $this->negativeHaveToOptions('frame')),
                    'a4' => $this->markerResultWouldBeAble('they', 'endorse', 'would be able to endorse'),
                ],
            ],
            [
                'level' => 'C2',
                'group' => 'negative_past',
                'question' => 'If diplomats {a1} the ceasefire terms last month, border towns {a2} tense now, mediators {a3} convene daily briefings, and peacekeepers {a4} have to reinforce patrols.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('diplomats', 'ratify', 'ratified'),
                    'a2' => $this->markerResultWouldNegative('they', 'be', 'would not be'),
                    'a3' => $this->markerResultWouldNegative('they', 'have to convene', 'would not have to convene', 'not convene', $this->negativeHaveToOptions('convene')),
                    'a4' => $this->markerResultWouldHaveTo('they', 'reinforce', 'would have to reinforce'),
                ],
            ],
            [
                'level' => 'C2',
                'group' => 'negative_present',
                'question' => 'If the oversight board {a1} united today, ministries {a2} fragmented, citizens {a3} constant assurances, and international partners {a4} be able to align quickly.',
                'markers' => [
                    'a1' => $this->markerIfWere('the oversight board'),
                    'a2' => $this->markerResultWouldNegative('they', 'remain', 'would not remain'),
                    'a3' => $this->markerResultWouldNegative('they', 'demand', 'would not demand'),
                    'a4' => $this->markerResultWouldBeAble('they', 'align', 'would be able to align'),
                ],
            ],
            [
                'level' => 'C2',
                'group' => 'negative_present',
                'question' => 'If Theo {a1} interpret the encrypted brief now, he {a2} second-guessing his choices, he {a3} consult external counsel, and auditors {a4} have to delay their verdict.',
                'markers' => [
                    'a1' => $this->markerIfModalCould('Theo', 'interpret'),
                    'a2' => $this->markerResultWouldNegative('he', 'keep', 'would not keep'),
                    'a3' => $this->markerResultWouldNegative('he', 'have to consult', 'would not have to consult', 'not consult', $this->negativeHaveToOptions('consult')),
                    'a4' => $this->markerResultWouldHaveTo('they', 'delay', 'would have to delay'),
                ],
            ],
            [
                'level' => 'C2',
                'group' => 'negative_present',
                'question' => 'If the laboratories {a1} aggregate the genomes today, regulators {a2} alarmed, ethicists {a3} draft emergency statements, and review boards {a4} be able to endorse provisional use.',
                'markers' => [
                    'a1' => $this->markerIfModalCould('the laboratories', 'aggregate'),
                    'a2' => $this->markerResultWouldNegative('they', 'stay', 'would not stay'),
                    'a3' => $this->markerResultWouldNegative('they', 'have to draft', 'would not have to draft', 'not draft', $this->negativeHaveToOptions('draft')),
                    'a4' => $this->markerResultWouldBeAble('they', 'endorse', 'would be able to endorse'),
                ],
            ],
            [
                'level' => 'C2',
                'group' => 'negative_present',
                'question' => 'If we {a1} full interoperability now, we {a2} improvising workarounds, partners {a3} escalate support tickets, and our clients {a4} be able to deploy instantly.',
                'markers' => [
                    'a1' => $this->markerIfModalCould('we', 'achieve'),
                    'a2' => $this->markerResultWouldNegative('we', 'keep', 'would not keep'),
                    'a3' => $this->markerResultWouldNegative('they', 'have to escalate', 'would not have to escalate', 'not escalate', $this->negativeHaveToOptions('escalate')),
                    'a4' => $this->markerResultWouldBeAble('they', 'deploy', 'would be able to deploy'),
                ],
            ],
            [
                'level' => 'C2',
                'group' => 'negative_future',
                'question' => 'If the senate {a1} the reform package next session, agencies {a2} paralyzed later, unions {a3} threaten walkouts, and watchdogs {a4} be able to report swift progress.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the senate', 'endorse', 'endorsed'),
                    'a2' => $this->markerResultWouldNegative('they', 'remain', 'would not remain'),
                    'a3' => $this->markerResultWouldNegative('they', 'have to threaten', 'would not have to threaten', 'not threaten', $this->negativeHaveToOptions('threaten')),
                    'a4' => $this->markerResultWouldBeAble('they', 'report', 'would be able to report'),
                ],
            ],
            [
                'level' => 'C2',
                'group' => 'negative_future',
                'question' => 'If the expedition {a1} late next winter, the research timeline {a2} jeopardized, sponsors {a3} demand penalties, and logistics teams {a4} have to renegotiate routes.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the expedition', 'launch', 'launched'),
                    'a2' => $this->markerResultWouldNegative('it', 'be', 'would not be'),
                    'a3' => $this->markerResultWouldNegative('they', 'have to demand', 'would not have to demand', 'not demand', $this->negativeHaveToOptions('demand')),
                    'a4' => $this->markerResultWouldHaveTo('they', 'renegotiate', 'would have to renegotiate'),
                ],
            ],
            [
                'level' => 'C2',
                'group' => 'negative_future',
                'question' => 'If our studio {a1} the prototype tomorrow, clients {a2} skeptical, reviewers {a3} request clarifications, and distributors {a4} be able to finalize schedules.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('our studio', 'finalize', 'finalized'),
                    'a2' => $this->markerResultWouldNegative('they', 'remain', 'would not remain'),
                    'a3' => $this->markerResultWouldNegative('they', 'have to request', 'would not have to request', 'not request', $this->negativeHaveToOptions('request')),
                    'a4' => $this->markerResultWouldBeAble('they', 'finalize', 'would be able to finalize'),
                ],
            ],
            [
                'level' => 'C2',
                'group' => 'negative_future',
                'question' => 'If the think tank {a1} new evidence next quarter, policymakers {a2} hesitant, journalists {a3} craft contingency stories, and regional leaders {a4} have to delay commitments.',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the think tank', 'uncover', 'uncovered'),
                    'a2' => $this->markerResultWouldNegative('they', 'stay', 'would not stay'),
                    'a3' => $this->markerResultWouldNegative('they', 'need to craft', 'would not need to craft', 'not craft', $this->negativeHaveToOptions('need to craft')),
                    'a4' => $this->markerResultWouldHaveTo('they', 'delay', 'would have to delay'),
                ],
            ],
            [
                'level' => 'C2',
                'group' => 'interrogative_past',
                'question' => 'If you {a1} a multinational leak yesterday, would you {a2} secure the archive, {a3} be able to brief the senate, or {a4} escalate it to allies?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('you', 'uncover', 'uncovered'),
                    'a2' => $this->markerResultWouldSimple('you', 'secure', 'secure', null, $this->baseVerbOptions('secure', 'secured')),
                    'a3' => $this->markerResultWouldBeAble('you', 'brief', 'would be able to brief'),
                    'a4' => $this->markerResultWouldSimple('you', 'escalate', 'escalate', null, $this->baseVerbOptions('escalate', 'escalated')),
                ],
            ],
            [
                'level' => 'C2',
                'group' => 'interrogative_past',
                'question' => 'If Marisol {a1} classified footage yesterday, would she {a2} redact it, {a3} be able to present it to prosecutors, or {a4} shelve the case?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('Marisol', 'obtain', 'obtained'),
                    'a2' => $this->markerResultWouldSimple('she', 'redact', 'redact', null, $this->baseVerbOptions('redact', 'redacted')),
                    'a3' => $this->markerResultWouldBeAble('she', 'present', 'would be able to present'),
                    'a4' => $this->markerResultWouldSimple('she', 'shelve', 'shelve', null, $this->baseVerbOptions('shelve', 'shelved')),
                ],
            ],
            [
                'level' => 'C2',
                'group' => 'interrogative_past',
                'question' => 'If the delegates {a1} a secret addendum last night, would they {a2} publish it, {a3} be able to renegotiate quietly, or {a4} ignore the findings?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the delegates', 'discover', 'discovered'),
                    'a2' => $this->markerResultWouldSimple('they', 'publish', 'publish', null, $this->baseVerbOptions('publish', 'published')),
                    'a3' => $this->markerResultWouldBeAble('they', 'renegotiate', 'would be able to renegotiate'),
                    'a4' => $this->markerResultWouldSimple('they', 'ignore', 'ignore', null, $this->baseVerbOptions('ignore', 'ignored')),
                ],
            ],
            [
                'level' => 'C2',
                'group' => 'interrogative_past',
                'question' => 'If auditors {a1} a forged ledger yesterday, would they {a2} freeze the accounts, {a3} be able to brief regulators overnight, or {a4} postpone the announcement?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('auditors', 'spot', 'spotted'),
                    'a2' => $this->markerResultWouldSimple('they', 'freeze', 'freeze', null, $this->baseVerbOptions('freeze', 'froze')),
                    'a3' => $this->markerResultWouldBeAble('they', 'brief', 'would be able to brief'),
                    'a4' => $this->markerResultWouldSimple('they', 'postpone', 'postpone', null, $this->baseVerbOptions('postpone', 'postponed')),
                ],
            ],
            [
                'level' => 'C2',
                'group' => 'interrogative_present',
                'question' => 'If the arbitration panel {a1} available today, would we {a2} draft a compromise, {a3} be able to ratify clauses tonight, or {a4} defer talks again?',
                'markers' => [
                    'a1' => $this->markerIfWere('the arbitration panel'),
                    'a2' => $this->markerResultWouldSimple('we', 'draft', 'draft', null, $this->baseVerbOptions('draft', 'drafted')),
                    'a3' => $this->markerResultWouldBeAble('we', 'ratify', 'would be able to ratify'),
                    'a4' => $this->markerResultWouldSimple('we', 'defer', 'defer', null, $this->baseVerbOptions('defer', 'deferred')),
                ],
            ],
            [
                'level' => 'C2',
                'group' => 'interrogative_present',
                'question' => 'If you {a1} unrestricted access right now, would you {a2} encrypt the cache, {a3} be able to audit compliance instantly, or {a4} delegate the duty?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('you', 'have', 'had'),
                    'a2' => $this->markerResultWouldSimple('you', 'encrypt', 'encrypt', null, $this->baseVerbOptions('encrypt', 'encrypted')),
                    'a3' => $this->markerResultWouldBeAble('you', 'audit', 'would be able to audit'),
                    'a4' => $this->markerResultWouldSimple('you', 'delegate', 'delegate', null, $this->baseVerbOptions('delegate', 'delegated')),
                ],
            ],
            [
                'level' => 'C2',
                'group' => 'interrogative_present',
                'question' => 'If the newsroom {a1} calm today, would editors {a2} curate longform coverage, {a3} be able to mentor junior staff on style, or {a4} chase trending updates?',
                'markers' => [
                    'a1' => $this->markerIfWere('the newsroom'),
                    'a2' => $this->markerResultWouldSimple('they', 'curate', 'curate', null, $this->baseVerbOptions('curate', 'curated')),
                    'a3' => $this->markerResultWouldBeAble('they', 'mentor', 'would be able to mentor'),
                    'a4' => $this->markerResultWouldSimple('they', 'chase', 'chase', null, $this->baseVerbOptions('chase', 'chased')),
                ],
            ],
            [
                'level' => 'C2',
                'group' => 'interrogative_present',
                'question' => 'If negotiators {a1} optimistic this afternoon, would they {a2} finalize the addendum, {a3} be able to brief both delegations tonight, or {a4} postpone the session?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('negotiators', 'feel', 'felt'),
                    'a2' => $this->markerResultWouldSimple('they', 'finalize', 'finalize', null, $this->baseVerbOptions('finalize', 'finalized')),
                    'a3' => $this->markerResultWouldBeAble('they', 'brief', 'would be able to brief'),
                    'a4' => $this->markerResultWouldSimple('they', 'postpone', 'postpone', null, $this->baseVerbOptions('postpone', 'postponed')),
                ],
            ],
            [
                'level' => 'C2',
                'group' => 'interrogative_future',
                'question' => 'If you {a1} the global fellowship next year, would you {a2} lead a task force, {a3} be able to found your institute abroad, or {a4} remain a consultant?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('you', 'win', 'won'),
                    'a2' => $this->markerResultWouldSimple('you', 'lead', 'lead', null, $this->baseVerbOptions('lead', 'led')),
                    'a3' => $this->markerResultWouldBeAble('you', 'found', 'would be able to found'),
                    'a4' => $this->markerResultWouldSimple('you', 'remain', 'remain', null, $this->baseVerbOptions('remain', 'remained')),
                ],
            ],
            [
                'level' => 'C2',
                'group' => 'interrogative_future',
                'question' => 'If the firm {a1} sweeping approval next quarter, would it {a2} restructure supply lines, {a3} be able to hire globally within weeks, or {a4} scale cautiously?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the firm', 'secure', 'secured'),
                    'a2' => $this->markerResultWouldSimple('it', 'restructure', 'restructure', null, $this->baseVerbOptions('restructure', 'restructured')),
                    'a3' => $this->markerResultWouldBeAble('it', 'hire', 'would be able to hire'),
                    'a4' => $this->markerResultWouldSimple('it', 'scale', 'scale', null, $this->baseVerbOptions('scale', 'scaled')),
                ],
            ],
            [
                'level' => 'C2',
                'group' => 'interrogative_future',
                'question' => 'If the consortium {a1} rare patents next summer, would they {a2} license them, {a3} be able to accelerate open-source projects, or {a4} restrict collaborations?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('the consortium', 'secure', 'secured'),
                    'a2' => $this->markerResultWouldSimple('they', 'license', 'license', null, $this->baseVerbOptions('license', 'licensed')),
                    'a3' => $this->markerResultWouldBeAble('they', 'accelerate', 'would be able to accelerate'),
                    'a4' => $this->markerResultWouldSimple('they', 'restrict', 'restrict', null, $this->baseVerbOptions('restrict', 'restricted')),
                ],
            ],
            [
                'level' => 'C2',
                'group' => 'interrogative_future',
                'question' => 'If Amara {a1} international backing next month, would she {a2} launch the initiative, {a3} be able to mentor regional leaders, or {a4} archive the strategy?',
                'markers' => [
                    'a1' => $this->markerIfPastSimple('Amara', 'gain', 'gained'),
                    'a2' => $this->markerResultWouldSimple('she', 'launch', 'launch', null, $this->baseVerbOptions('launch', 'launched')),
                    'a3' => $this->markerResultWouldBeAble('she', 'mentor', 'would be able to mentor'),
                    'a4' => $this->markerResultWouldSimple('she', 'archive', 'archive', null, $this->baseVerbOptions('archive', 'archived')),
                ],
            ],
        ]);

        return $entries;
    }

    private function markerIfPastSimple(string $subject, string $verb, string $answer, ?string $hint = null, array $options = []): array
    {
        if (empty($options)) {
            $options = [
                ['value' => $answer, 'reason' => 'correct'],
                ['value' => $verb, 'reason' => 'present_simple'],
                ['value' => $verb . 's', 'reason' => 'third_person'],
                ['value' => 'had ' . $answer, 'reason' => 'past_perfect'],
            ];
        }

        return [
            'type' => 'if_past_simple',
            'subject' => $subject,
            'verb' => $verb,
            'verb_hint' => $hint ?? $verb,
            'answer' => $answer,
            'options' => $options,
        ];
    }

    private function markerIfWere(string $subject, ?string $hint = null, array $options = []): array
    {
        if (empty($options)) {
            $options = [
                ['value' => 'were', 'reason' => 'correct'],
                ['value' => 'was', 'reason' => 'was'],
                ['value' => 'are', 'reason' => 'present_simple'],
                ['value' => 'would be', 'reason' => 'result_form'],
            ];
        }

        return [
            'type' => 'if_were',
            'subject' => $subject,
            'verb' => 'be',
            'verb_hint' => $hint ?? 'be',
            'answer' => 'were',
            'options' => $options,
        ];
    }

    private function markerIfModalCould(string $subject, string $verb, ?string $hint = null, array $options = []): array
    {
        if (empty($options)) {
            $options = [
                ['value' => 'could', 'reason' => 'correct'],
                ['value' => 'can', 'reason' => 'present_modal'],
                ['value' => 'was able', 'reason' => 'bare_was_able'],
                ['value' => 'would can', 'reason' => 'modal_stack'],
            ];
        }

        return [
            'type' => 'if_modal_could',
            'subject' => $subject,
            'verb' => $verb,
            'verb_hint' => $hint ?? 'can',
            'answer' => 'could',
            'options' => $options,
        ];
    }

    private function markerResultWouldSimple(string $subject, string $verb, string $answer, ?string $hint = null, array $options = []): array
    {
        if (empty($options)) {
            if (str_starts_with($answer, 'would ')) {
                $options = [
                    ['value' => $answer, 'reason' => 'correct'],
                    ['value' => str_replace('would ', 'will ', $answer), 'reason' => 'first_conditional'],
                    ['value' => $verb, 'reason' => 'present_simple'],
                    ['value' => str_replace('would ', '', $answer), 'reason' => 'missing_would'],
                ];
            } else {
                $options = [
                    ['value' => $answer, 'reason' => 'correct'],
                    ['value' => $verb . 's', 'reason' => 'third_person'],
                    ['value' => 'will ' . $verb, 'reason' => 'first_conditional'],
                    ['value' => $verb . 'ed', 'reason' => 'past_simple'],
                ];
            }
        }

        return [
            'type' => 'result_would_simple',
            'subject' => $subject,
            'verb' => $verb,
            'verb_hint' => $hint ?? $verb,
            'answer' => $answer,
            'options' => $options,
        ];
    }

    private function markerResultWouldNegative(string $subject, string $verb, string $answer, ?string $hint = null, array $options = []): array
    {
        if (empty($options)) {
            $options = [
                ['value' => $answer, 'reason' => 'correct'],
                ['value' => str_replace('would not', 'will not', $answer), 'reason' => 'first_conditional'],
                ['value' => $verb, 'reason' => 'present_simple'],
                ['value' => 'would be ' . $verb . 'ing', 'reason' => 'continuous'],
            ];
        }

        return [
            'type' => 'result_would_negative',
            'subject' => $subject,
            'verb' => $verb,
            'verb_hint' => $hint ?? ('not ' . $verb),
            'answer' => $answer,
            'options' => $options,
        ];
    }

    private function markerResultWouldBeAble(string $subject, string $verb, string $answer, ?string $hint = null, array $options = []): array
    {
        if (empty($options)) {
            $options = [
                ['value' => $answer, 'reason' => 'correct'],
                ['value' => str_replace('would', 'will', $answer), 'reason' => 'first_conditional'],
                ['value' => $verb, 'reason' => 'present_simple'],
                ['value' => str_replace('would', 'would have', $answer), 'reason' => 'third_conditional'],
            ];
        }

        return [
            'type' => 'result_would_be_able',
            'subject' => $subject,
            'verb' => $verb,
            'verb_hint' => $hint ?? $verb,
            'answer' => $answer,
            'options' => $options,
        ];
    }

    private function markerResultWouldHaveTo(string $subject, string $verb, string $answer, ?string $hint = null, array $options = []): array
    {
        if (empty($options)) {
            $options = [
                ['value' => $answer, 'reason' => 'correct'],
                ['value' => str_replace('would', 'will', $answer), 'reason' => 'first_conditional'],
                ['value' => $verb, 'reason' => 'present_simple'],
                ['value' => str_replace('have to', 'have had to', $answer), 'reason' => 'third_conditional'],
            ];
        }

        return [
            'type' => 'result_would_have_to',
            'subject' => $subject,
            'verb' => $verb,
            'verb_hint' => $hint ?? $verb,
            'answer' => $answer,
            'options' => $options,
        ];
    }

    private function titleCaseIfNeeded(string $verb, string $subject): string
    {
        return $verb;
    }

    private function baseVerbOptions(string $verb, string $past): array
    {
        return [
            ['value' => $verb, 'reason' => 'correct'],
            ['value' => $verb . 's', 'reason' => 'third_person'],
            ['value' => 'will ' . $verb, 'reason' => 'first_conditional'],
            ['value' => $past, 'reason' => 'past_simple'],
        ];
    }

    private function negativeHaveToOptions(string $verb): array
    {
        return [
            ['value' => 'would not have to ' . $verb, 'reason' => 'correct'],
            ['value' => 'will not have to ' . $verb, 'reason' => 'first_conditional'],
            ['value' => 'have to ' . $verb, 'reason' => 'present_simple'],
            ['value' => 'would have had to ' . $verb, 'reason' => 'third_conditional'],
        ];
    }
}
