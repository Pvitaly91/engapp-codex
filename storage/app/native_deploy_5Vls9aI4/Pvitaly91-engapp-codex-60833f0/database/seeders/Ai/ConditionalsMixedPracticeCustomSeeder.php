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
        'if_past_perfect_mixed' => "Час: Mixed Conditional (past condition → present result).\nФормула: **if + had + V3** (для заперечення had not + V3).\nПояснення: умовна частина описує минулу нереальну дію, тому дієслово «%verb%» ставимо у Past Perfect.\nПриклад: *%example%*",
        'if_past_simple_mixed' => "Час: Mixed Conditional (present condition → past result).\nФормула: **if + Past Simple**.\nПояснення: умовна частина показує нереальний теперішній стан, тому дієслово «%verb%» вживаємо у Past Simple.\nПриклад: *%example%*",
        'if_past_simple_were' => "Час: Mixed Conditional (present condition → past result).\nФормула: **if + were** з усіма особами.\nПояснення: для умовного were використовуємо форму were навіть із I/he/she/it.\nПриклад: *%example%*",
        'result_would_present' => "Час: Mixed Conditional (past condition → present result).\nФормула: **would + base verb**.\nПояснення: головна частина описує теперішній наслідок, тому беремо would + початкову форму «%verb%».\nПриклад: *%example%*",
        'result_would_present_negative' => "Час: Mixed Conditional (past condition → present результат).\nФормула: **would not + base verb**.\nПояснення: заперечення утворюємо через would not (wouldn't) + базову форму «%verb%».\nПриклад: *%example%*",
        'result_would_present_question' => "Час: Mixed Conditional (past condition → present result).\nФормула: **would + subject + base verb**.\nПояснення: у запитанні ставимо would перед підметом і використовуємо базову форму «%verb%».\nПриклад: *%example%*",
        'result_would_have_past' => "Час: Mixed Conditional (present condition → past result).\nФормула: **would have + V3**.\nПояснення: результат у минулому передаємо через would have + третю форму дієслова «%verb%».\nПриклад: *%example%*",
        'result_would_have_negative' => "Час: Mixed Conditional (present condition → past result).\nФормула: **would not have + V3**.\nПояснення: для заперечення додаємо would not (wouldn't) перед have + V3 дієслова «%verb%».\nПриклад: *%example%*",
        'result_would_have_question' => "Час: Mixed Conditional (present condition → past result).\nФормула: **would + subject + have + V3**.\nПояснення: у питальній формі ставимо would перед підметом і додаємо have + V3 «%verb%».\nПриклад: *%example%*",
    ];

    private array $explanationTemplates = [
        'if_past_perfect_mixed' => [
            'correct' => "✅ «%option%» правильно: Past Perfect потрібен в умові, що описує минулий факт.\nПриклад: *%example%*",
            'past_simple' => "❌ «%option%» — це Past Simple. Для змішаного умовного потрібен Past Perfect.\nПриклад: *%example%*",
            'present_simple' => "❌ «%option%» — Present Simple. Умова має показувати завершену минулу дію.\nПриклад: *%example%*",
            'modal_result' => "❌ «%option%» належить до головної частини (would have), а не до if-клауза.\nПриклад: *%example%*",
            'did_not' => "❌ «%option%» — конструкція з did not, тобто Past Simple. Нам потрібен had not + V3.\nПриклад: *%example%*",
        ],
        'if_past_simple_mixed' => [
            'correct' => "✅ «%option%» правильно: умовна частина описує теперішню нереальність, тож потрібен Past Simple.\nПриклад: *%example%*",
            'present_simple' => "❌ «%option%» — Present Simple. Для змішаного умовного використовуємо Past Simple.\nПриклад: *%example%*",
            'past_perfect' => "❌ «%option%» — Past Perfect. Тут треба простий Past Simple.\nПриклад: *%example%*",
            'modal_result' => "❌ «%option%» — форма результату з would. В if-клаузі її не використовуємо.\nПриклад: *%example%*",
            'did_not' => "❌ «%option%» — форма з did not. Для ствердження потрібна проста минула форма.\nПриклад: *%example%*",
        ],
        'if_past_simple_were' => [
            'correct' => "✅ «%option%» правильно: у нереальних умовах використовуємо were з усіма особами.\nПриклад: *%example%*",
            'present_simple' => "❌ «%option%» — Present Simple. Потрібно were для нереальної умови.\nПриклад: *%example%*",
            'past_perfect' => "❌ «%option%» — Past Perfect. У цьому типі потрібне лише were.\nПриклад: *%example%*",
            'modal_result' => "❌ «%option%» — частина головного речення з would. В if-клаузі вживаємо were.\nПриклад: *%example%*",
        ],
        'result_would_present' => [
            'correct' => "✅ «%option%» правильно: теперішній наслідок змішаного умовного передаємо через would + V1.\nПриклад: *%example%*",
            'past_result' => "❌ «%option%» — форма з would have, яка показує минулий результат. Тут ідеться про теперішній наслідок.\nПриклад: *%example%*",
            'first_conditional' => "❌ «%option%» — конструкція з will, типова для First Conditional. Нам потрібен would.\nПриклад: *%example%*",
            'present_simple' => "❌ «%option%» — Present Simple. Змішане умовне потребує would + V1.\nПриклад: *%example%*",
        ],
        'result_would_present_negative' => [
            'correct' => "✅ «%option%» правильно: заперечний теперішній наслідок подаємо як would not + V1.\nПриклад: *%example%*",
            'past_result' => "❌ «%option%» — форма would not have, яка описує минулий результат.\nПриклад: *%example%*",
            'first_conditional' => "❌ «%option%» — конструкція will not, характерна для First Conditional.\nПриклад: *%example%*",
            'present_simple' => "❌ «%option%» — Present Simple, а нам потрібне would not + V1.\nПриклад: *%example%*",
        ],
        'result_would_present_question' => [
            'correct' => "✅ «%option%» правильно: у запитанні ставимо would перед підметом і вживаємо базову форму.\nПриклад: *%example%*",
            'past_result' => "❌ «%option%» — форма would have, яка стосується минулого результату.\nПриклад: *%example%*",
            'first_conditional' => "❌ «%option%» — конструкція will, характерна для First Conditional.\nПриклад: *%example%*",
            'present_simple' => "❌ «%option%» — Present Simple без допоміжного would.\nПриклад: *%example%*",
        ],
        'result_would_have_past' => [
            'correct' => "✅ «%option%» правильно: минулий наслідок виражаємо як would have + V3.\nПриклад: *%example%*",
            'present_result' => "❌ «%option%» — форма would + V1 показує теперішній результат.\nПриклад: *%example%*",
            'first_conditional' => "❌ «%option%» — will + V1 належить до First Conditional.\nПриклад: *%example%*",
            'past_simple' => "❌ «%option%» — Past Simple. Для результату потрібне would have + V3.\nПриклад: *%example%*",
        ],
        'result_would_have_negative' => [
            'correct' => "✅ «%option%» правильно: заперечний минулий результат утворюємо як would not have + V3.\nПриклад: *%example%*",
            'present_result' => "❌ «%option%» — форма would not + V1 описує теперішній наслідок.\nПриклад: *%example%*",
            'first_conditional' => "❌ «%option%» — will not + V1, характерна для First Conditional.\nПриклад: *%example%*",
            'missing_have' => "❌ «%option%» — пропущено have, тому конструкція не формує минулий результат.\nПриклад: *%example%*",
        ],
        'result_would_have_question' => [
            'correct' => "✅ «%option%» правильно: у запитанні до минулого результату ставимо would перед підметом і додаємо have + V3.\nПриклад: *%example%*",
            'present_result' => "❌ «%option%» — форма would + V1 описує теперішній наслідок.\nПриклад: *%example%*",
            'first_conditional' => "❌ «%option%» — конструкція will, характерна для First Conditional.\nПриклад: *%example%*",
            'present_simple' => "❌ «%option%» — Present Simple без допоміжного would.\nПриклад: *%example%*",
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
            ['name' => 'Mixed Conditional Type 4'],
            ['category' => 'English Grammar Structure']
        )->id;

        $tenseTagIds = [
            'Mixed Conditional (Third→Second)' => Tag::firstOrCreate(['name' => 'Mixed Conditional (Third→Second)'], ['category' => 'Tenses'])->id,
            'Mixed Conditional (Second→Third)' => Tag::firstOrCreate(['name' => 'Mixed Conditional (Second→Third)'], ['category' => 'Tenses'])->id,
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
        $template = $this->hintTemplates[$type] ?? "Час: Mixed Conditional.\nПояснення: доберіть форму, що відповідає правилу цього типу умовного.\nПриклад: *%example%*";

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

        // Negative focus (6)
        $entries[] = $this->makeEntry(
            'A1',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If I {a1} my umbrella yesterday, I {a2} wet now.',
            [
                'a1' => $this->ifPastPerfectMixed('I', $this->forms('take', 'took', 'taken')),
                'a2' => $this->resultWouldPresent('I', $this->forms('be', 'was', 'been'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If he {a1} breakfast, he {a2} hungry now.',
            [
                'a1' => $this->ifPastPerfectMixed('he', $this->forms('eat', 'ate', 'eaten')),
                'a2' => $this->resultWouldPresent('he', $this->forms('be', 'was', 'been'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If we {a1} the door last night, we {a2} worried now.',
            [
                'a1' => $this->ifPastPerfectMixed('we', $this->forms('lock', 'locked', 'locked')),
                'a2' => $this->resultWouldPresent('we', $this->forms('be', 'were', 'been'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If they {a1} the lights off last night, they {a2} a big bill now.',
            [
                'a1' => $this->ifPastPerfectMixed('they', $this->forms('turn', 'turned', 'turned')),
                'a2' => $this->resultWouldPresent('they', $this->forms('owe', 'owed', 'owed'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If Sara {a1} the note, she {a2} confused now.',
            [
                'a1' => $this->ifPastPerfectMixed('Sara', $this->forms('read', 'read', 'read')),
                'a2' => $this->resultWouldPresent('she', $this->forms('feel', 'felt', 'felt'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If the bus driver {a1} attention yesterday, the passengers {a2} nervous now.',
            [
                'a1' => $this->ifPastPerfectMixed('the bus driver', $this->forms('pay', 'paid', 'paid')),
                'a2' => $this->resultWouldPresent('the passengers', $this->forms('feel', 'felt', 'felt'), true),
            ]
        );

        // Question focus (6)
        $entries[] = $this->makeEntry(
            'A1',
            'question',
            ['Mixed Conditional (Third→Second)'],
            'If you {a1} earlier, {a2} enough time now?',
            [
                'a1' => $this->ifPastPerfectMixed('you', $this->forms('leave', 'left', 'left')),
                'a2' => $this->resultWouldPresentQuestion('you', $this->forms('have', 'had', 'had')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'question',
            ['Mixed Conditional (Second→Third)'],
            'If he {a1} more careful, {a2} his keys yesterday?',
            [
                'a1' => $this->ifPastSimpleWere('he'),
                'a2' => $this->resultWouldHaveQuestion('he', $this->forms('lose', 'lost', 'lost')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'question',
            ['Mixed Conditional (Second→Third)'],
            'If the weather {a1} warmer, {a2} the picnic last weekend?',
            [
                'a1' => $this->ifPastSimpleWere('it'),
                'a2' => $this->resultWouldHaveQuestion('we', $this->forms('cancel', 'cancelled', 'cancelled')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'question',
            ['Mixed Conditional (Third→Second)'],
            'If Lisa {a1} her lessons, {a2} confident now?',
            [
                'a1' => $this->ifPastPerfectMixed('Lisa', $this->forms('finish', 'finished', 'finished')),
                'a2' => $this->resultWouldPresentQuestion('Lisa', $this->forms('feel', 'felt', 'felt')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'question',
            ['Mixed Conditional (Third→Second)'],
            'If they {a1} the map, {a2} lost now?',
            [
                'a1' => $this->ifPastPerfectMixed('they', $this->forms('check', 'checked', 'checked')),
                'a2' => $this->resultWouldPresentQuestion('they', $this->forms('be', 'were', 'been')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'question',
            ['Mixed Conditional (Second→Third)'],
            'If we {a1} more polite, {a2} an apology yesterday?',
            [
                'a1' => $this->ifPastSimpleWere('we'),
                'a2' => $this->resultWouldHaveQuestion('we', $this->forms('receive', 'received', 'received')),
            ]
        );

        // Present-result focus (6)
        $entries[] = $this->makeEntry(
            'A1',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If I {a1} earlier, I {a2} ready now.',
            [
                'a1' => $this->ifPastPerfectMixed('I', $this->forms('wake', 'woke', 'woken')),
                'a2' => $this->resultWouldPresent('I', $this->forms('be', 'was', 'been')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If you {a1} the bus on time, you {a2} at home now.',
            [
                'a1' => $this->ifPastPerfectMixed('you', $this->forms('catch', 'caught', 'caught')),
                'a2' => $this->resultWouldPresent('you', $this->forms('be', 'were', 'been')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If Mia {a1} the words, she {a2} happy now.',
            [
                'a1' => $this->ifPastPerfectMixed('Mia', $this->forms('learn', 'learned', 'learned')),
                'a2' => $this->resultWouldPresent('she', $this->forms('feel', 'felt', 'felt')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If the team {a1} earlier, they {a2} calm now.',
            [
                'a1' => $this->ifPastPerfectMixed('the team', $this->forms('arrive', 'arrived', 'arrived')),
                'a2' => $this->resultWouldPresent('the team', $this->forms('be', 'was', 'been')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If Tom {a1} his bike last week, he {a2} to school now.',
            [
                'a1' => $this->ifPastPerfectMixed('Tom', $this->forms('fix', 'fixed', 'fixed')),
                'a2' => $this->resultWouldPresent('he', $this->forms('ride', 'rode', 'ridden')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If the shop {a1} more bread, it {a2} customers now.',
            [
                'a1' => $this->ifPastPerfectMixed('the shop', $this->forms('order', 'ordered', 'ordered')),
                'a2' => $this->resultWouldPresent('it', $this->forms('have', 'had', 'had')),
            ]
        );

        // Past-result focus (6)
        $entries[] = $this->makeEntry(
            'A1',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If she {a1} kinder, she {a2} that argument yesterday.',
            [
                'a1' => $this->ifPastSimpleWere('she'),
                'a2' => $this->resultWouldHave('she', $this->forms('avoid', 'avoided', 'avoided')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If they {a1} more careful, they {a2} the crack yesterday.',
            [
                'a1' => $this->ifPastSimpleMixed('they', $this->forms('listen', 'listened', 'listened')),
                'a2' => $this->resultWouldHave('they', $this->forms('notice', 'noticed', 'noticed')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If we {a1} taller, we {a2} the shelf yesterday.',
            [
                'a1' => $this->ifPastSimpleWere('we'),
                'a2' => $this->resultWouldHave('we', $this->forms('reach', 'reached', 'reached')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If I {a1} more confident, I {a2} the question yesterday.',
            [
                'a1' => $this->ifPastSimpleMixed('I', $this->forms('feel', 'felt', 'felt')),
                'a2' => $this->resultWouldHave('I', $this->forms('ask', 'asked', 'asked')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If the teacher {a1} patient, the class {a2} the test yesterday.',
            [
                'a1' => $this->ifPastSimpleWere('the teacher'),
                'a2' => $this->resultWouldHave('the class', $this->forms('finish', 'finished', 'finished')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A1',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If your brother {a1} polite, he {a2} the door for grandma yesterday.',
            [
                'a1' => $this->ifPastSimpleWere('your brother'),
                'a2' => $this->resultWouldHave('he', $this->forms('open', 'opened', 'opened')),
            ]
        );

        return $entries;
    }

    private function buildA2Questions(): array
    {
        $entries = [];

        // Negative focus (6)
        $entries[] = $this->makeEntry(
            'A2',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If I {a1} two alarms, I {a2} late now.',
            [
                'a1' => $this->ifPastPerfectMixed('I', $this->forms('set', 'set', 'set')),
                'a2' => $this->resultWouldPresent('I', $this->forms('be', 'was', 'been'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If you {a1} your phone, it {a2} silent now.',
            [
                'a1' => $this->ifPastPerfectMixed('you', $this->forms('charge', 'charged', 'charged')),
                'a2' => $this->resultWouldPresent('it', $this->forms('be', 'was', 'been'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If Nina {a1} the address, she {a2} lost now.',
            [
                'a1' => $this->ifPastPerfectMixed('Nina', $this->forms('save', 'saved', 'saved')),
                'a2' => $this->resultWouldPresent('she', $this->forms('be', 'was', 'been'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If the children {a1} the table, they {a2} stressed now.',
            [
                'a1' => $this->ifPastPerfectMixed('the children', $this->forms('clear', 'cleared', 'cleared')),
                'a2' => $this->resultWouldPresent('they', $this->forms('feel', 'felt', 'felt'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If our coach {a1} the tactic, we {a2} confused now.',
            [
                'a1' => $this->ifPastPerfectMixed('our coach', $this->forms('explain', 'explained', 'explained')),
                'a2' => $this->resultWouldPresent('we', $this->forms('be', 'were', 'been'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If the train operator {a1} the brakes, the passengers {a2} afraid now.',
            [
                'a1' => $this->ifPastPerfectMixed('the train operator', $this->forms('check', 'checked', 'checked')),
                'a2' => $this->resultWouldPresent('the passengers', $this->forms('feel', 'felt', 'felt'), true),
            ]
        );

        // Question focus (6)
        $entries[] = $this->makeEntry(
            'A2',
            'question',
            ['Mixed Conditional (Third→Second)'],
            'If you {a1} the invitation, {a2} ready now?',
            [
                'a1' => $this->ifPastPerfectMixed('you', $this->forms('read', 'read', 'read')),
                'a2' => $this->resultWouldPresentQuestion('you', $this->forms('be', 'were', 'been')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'question',
            ['Mixed Conditional (Second→Third)'],
            'If Martin {a1} more polite, {a2} an apology yesterday?',
            [
                'a1' => $this->ifPastSimpleWere('Martin'),
                'a2' => $this->resultWouldHaveQuestion('Martin', $this->forms('send', 'sent', 'sent')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'question',
            ['Mixed Conditional (Second→Third)'],
            'If the roads {a1} clear, {a2} the delivery yesterday?',
            [
                'a1' => $this->ifPastSimpleWere('the roads'),
                'a2' => $this->resultWouldHaveQuestion('the driver', $this->forms('make', 'made', 'made')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'question',
            ['Mixed Conditional (Third→Second)'],
            'If we {a1} earlier, {a2} inside the museum now?',
            [
                'a1' => $this->ifPastPerfectMixed('we', $this->forms('arrive', 'arrived', 'arrived')),
                'a2' => $this->resultWouldPresentQuestion('we', $this->forms('be', 'were', 'been')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'question',
            ['Mixed Conditional (Third→Second)'],
            'If Ana {a1} her notes, {a2} the answer now?',
            [
                'a1' => $this->ifPastPerfectMixed('Ana', $this->forms('organize', 'organized', 'organized')),
                'a2' => $this->resultWouldPresentQuestion('she', $this->forms('remember', 'remembered', 'remembered')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'question',
            ['Mixed Conditional (Second→Third)'],
            'If the manager {a1} helpful, {a2} a refund yesterday?',
            [
                'a1' => $this->ifPastSimpleWere('the manager'),
                'a2' => $this->resultWouldHaveQuestion('you', $this->forms('receive', 'received', 'received')),
            ]
        );

        // Present-result focus (6)
        $entries[] = $this->makeEntry(
            'A2',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If I {a1} the bus earlier, I {a2} relaxed now.',
            [
                'a1' => $this->ifPastPerfectMixed('I', $this->forms('catch', 'caught', 'caught')),
                'a2' => $this->resultWouldPresent('I', $this->forms('feel', 'felt', 'felt')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If you {a1} your tasks yesterday, you {a2} free now.',
            [
                'a1' => $this->ifPastPerfectMixed('you', $this->forms('complete', 'completed', 'completed')),
                'a2' => $this->resultWouldPresent('you', $this->forms('be', 'were', 'been')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If Elena {a1} extra practice, she {a2} confident now.',
            [
                'a1' => $this->ifPastPerfectMixed('Elena', $this->forms('do', 'did', 'done')),
                'a2' => $this->resultWouldPresent('she', $this->forms('feel', 'felt', 'felt')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If the shop {a1} early, it {a2} quiet now.',
            [
                'a1' => $this->ifPastPerfectMixed('the shop', $this->forms('open', 'opened', 'opened')),
                'a2' => $this->resultWouldPresent('it', $this->forms('be', 'was', 'been')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If the twins {a1} the clue, they {a2} sure now.',
            [
                'a1' => $this->ifPastPerfectMixed('the twins', $this->forms('spot', 'spotted', 'spotted')),
                'a2' => $this->resultWouldPresent('they', $this->forms('be', 'were', 'been')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If our dog {a1} the gate closed, it {a2} inside now.',
            [
                'a1' => $this->ifPastPerfectMixed('our dog', $this->forms('notice', 'noticed', 'noticed')),
                'a2' => $this->resultWouldPresent('it', $this->forms('stay', 'stayed', 'stayed')),
            ]
        );

        // Past-result focus (6)
        $entries[] = $this->makeEntry(
            'A2',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If she {a1} more patient, she {a2} the promise yesterday.',
            [
                'a1' => $this->ifPastSimpleWere('she'),
                'a2' => $this->resultWouldHave('she', $this->forms('keep', 'kept', 'kept')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If the guards {a1} more alert, they {a2} the alarm last night.',
            [
                'a1' => $this->ifPastSimpleMixed('the guards', $this->forms('stay', 'stayed', 'stayed')),
                'a2' => $this->resultWouldHave('they', $this->forms('hear', 'heard', 'heard')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If we {a1} less shy, we {a2} the host yesterday.',
            [
                'a1' => $this->ifPastSimpleWere('we'),
                'a2' => $this->resultWouldHave('we', $this->forms('thank', 'thanked', 'thanked')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If I {a1} better records, I {a2} the bill yesterday.',
            [
                'a1' => $this->ifPastSimpleMixed('I', $this->forms('keep', 'kept', 'kept')),
                'a2' => $this->resultWouldHave('I', $this->forms('pay', 'paid', 'paid')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If the team {a1} united, they {a2} the match yesterday.',
            [
                'a1' => $this->ifPastSimpleWere('the team'),
                'a2' => $this->resultWouldHave('they', $this->forms('win', 'won', 'won')),
            ]
        );

        $entries[] = $this->makeEntry(
            'A2',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If your cousin {a1} honest, he {a2} the mistake yesterday.',
            [
                'a1' => $this->ifPastSimpleWere('your cousin'),
                'a2' => $this->resultWouldHave('he', $this->forms('admit', 'admitted', 'admitted')),
            ]
        );

        return $entries;
    }

    private function buildB1Questions(): array
    {
        $entries = [];

        // Negative focus (6)
        $entries[] = $this->makeEntry(
            'B1',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If I {a1} the lecture, I {a2} lost now.',
            [
                'a1' => $this->ifPastPerfectMixed('I', $this->forms('record', 'recorded', 'recorded')),
                'a2' => $this->resultWouldPresent('I', $this->forms('feel', 'felt', 'felt'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If you {a1} the taxi, you {a2} stranded now.',
            [
                'a1' => $this->ifPastPerfectMixed('you', $this->forms('schedule', 'scheduled', 'scheduled')),
                'a2' => $this->resultWouldPresent('you', $this->forms('be', 'were', 'been'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If the chef {a1} the jars, we {a2} unsure now.',
            [
                'a1' => $this->ifPastPerfectMixed('the chef', $this->forms('label', 'labeled', 'labeled')),
                'a2' => $this->resultWouldPresent('we', $this->forms('feel', 'felt', 'felt'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If Mira {a1} the calendar, she {a2} double-booked now.',
            [
                'a1' => $this->ifPastPerfectMixed('Mira', $this->forms('update', 'updated', 'updated')),
                'a2' => $this->resultWouldPresent('she', $this->forms('be', 'was', 'been'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If the volunteers {a1} the kits, they {a2} rushing now.',
            [
                'a1' => $this->ifPastPerfectMixed('the volunteers', $this->forms('pack', 'packed', 'packed')),
                'a2' => $this->resultWouldPresent('they', $this->forms('be', 'were', 'been'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If our neighbors {a1} the gate, their dog {a2} loose now.',
            [
                'a1' => $this->ifPastPerfectMixed('our neighbors', $this->forms('close', 'closed', 'closed')),
                'a2' => $this->resultWouldPresent('their dog', $this->forms('be', 'was', 'been'), true),
            ]
        );

        // Question focus (6)
        $entries[] = $this->makeEntry(
            'B1',
            'question',
            ['Mixed Conditional (Third→Second)'],
            'If you {a1} the policy, {a2} secure now?',
            [
                'a1' => $this->ifPastPerfectMixed('you', $this->forms('check', 'checked', 'checked')),
                'a2' => $this->resultWouldPresentQuestion('you', $this->forms('feel', 'felt', 'felt')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'question',
            ['Mixed Conditional (Second→Third)'],
            'If Daniel {a1} more attentive, {a2} the cue yesterday?',
            [
                'a1' => $this->ifPastSimpleWere('Daniel'),
                'a2' => $this->resultWouldHaveQuestion('he', $this->forms('notice', 'noticed', 'noticed')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'question',
            ['Mixed Conditional (Second→Third)'],
            'If the lights {a1} brighter, {a2} the report yesterday?',
            [
                'a1' => $this->ifPastSimpleWere('the lights'),
                'a2' => $this->resultWouldHaveQuestion('we', $this->forms('finish', 'finished', 'finished')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'question',
            ['Mixed Conditional (Third→Second)'],
            'If Carla {a1} the form, {a2} ready now?',
            [
                'a1' => $this->ifPastPerfectMixed('Carla', $this->forms('submit', 'submitted', 'submitted')),
                'a2' => $this->resultWouldPresentQuestion('she', $this->forms('be', 'was', 'been')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'question',
            ['Mixed Conditional (Third→Second)'],
            'If the ferry {a1} on time, {a2} there now?',
            [
                'a1' => $this->ifPastPerfectMixed('the ferry', $this->forms('depart', 'departed', 'departed')),
                'a2' => $this->resultWouldPresentQuestion('we', $this->forms('be', 'were', 'been')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'question',
            ['Mixed Conditional (Second→Third)'],
            'If they {a1} more cautious, {a2} the warning yesterday?',
            [
                'a1' => $this->ifPastSimpleWere('they'),
                'a2' => $this->resultWouldHaveQuestion('they', $this->forms('ignore', 'ignored', 'ignored')),
            ]
        );

        // Present-result focus (6)
        $entries[] = $this->makeEntry(
            'B1',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If I {a1} the blueprint, I {a2} calm now.',
            [
                'a1' => $this->ifPastPerfectMixed('I', $this->forms('review', 'reviewed', 'reviewed')),
                'a2' => $this->resultWouldPresent('I', $this->forms('feel', 'felt', 'felt')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If you {a1} your budget, you {a2} ready now.',
            [
                'a1' => $this->ifPastPerfectMixed('you', $this->forms('plan', 'planned', 'planned')),
                'a2' => $this->resultWouldPresent('you', $this->forms('be', 'were', 'been')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If the analysts {a1} the data, they {a2} sure now.',
            [
                'a1' => $this->ifPastPerfectMixed('the analysts', $this->forms('analyze', 'analyzed', 'analyzed')),
                'a2' => $this->resultWouldPresent('they', $this->forms('be', 'were', 'been')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If Leila {a1} the shortcut, she {a2} home now.',
            [
                'a1' => $this->ifPastPerfectMixed('Leila', $this->forms('find', 'found', 'found')),
                'a2' => $this->resultWouldPresent('she', $this->forms('be', 'was', 'been')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If the museum {a1} the display, it {a2} full now.',
            [
                'a1' => $this->ifPastPerfectMixed('the museum', $this->forms('promote', 'promoted', 'promoted')),
                'a2' => $this->resultWouldPresent('it', $this->forms('be', 'was', 'been')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If our team {a1} weekly drills, we {a2} confident now.',
            [
                'a1' => $this->ifPastPerfectMixed('our team', $this->forms('complete', 'completed', 'completed')),
                'a2' => $this->resultWouldPresent('we', $this->forms('feel', 'felt', 'felt')),
            ]
        );

        // Past-result focus (6)
        $entries[] = $this->makeEntry(
            'B1',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If she {a1} more decisive, she {a2} the contract yesterday.',
            [
                'a1' => $this->ifPastSimpleWere('she'),
                'a2' => $this->resultWouldHave('she', $this->forms('sign', 'signed', 'signed')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If the auditors {a1} stricter, they {a2} the error last quarter.',
            [
                'a1' => $this->ifPastSimpleMixed('the auditors', $this->forms('act', 'acted', 'acted')),
                'a2' => $this->resultWouldHave('they', $this->forms('catch', 'caught', 'caught')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If we {a1} more generous, we {a2} the donation yesterday.',
            [
                'a1' => $this->ifPastSimpleWere('we'),
                'a2' => $this->resultWouldHave('we', $this->forms('double', 'doubled', 'doubled')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If I {a1} braver, I {a2} the presentation yesterday.',
            [
                'a1' => $this->ifPastSimpleMixed('I', $this->forms('feel', 'felt', 'felt')),
                'a2' => $this->resultWouldHave('I', $this->forms('deliver', 'delivered', 'delivered')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If the committee {a1} balanced, it {a2} the decision yesterday.',
            [
                'a1' => $this->ifPastSimpleWere('the committee'),
                'a2' => $this->resultWouldHave('it', $this->forms('approve', 'approved', 'approved')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B1',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If your mentor {a1} strict, you {a2} the errors yesterday.',
            [
                'a1' => $this->ifPastSimpleWere('your mentor'),
                'a2' => $this->resultWouldHave('you', $this->forms('spot', 'spotted', 'spotted')),
            ]
        );

        return $entries;
    }

    private function buildB2Questions(): array
    {
        $entries = [];

        // Negative focus (6)
        $entries[] = $this->makeEntry(
            'B2',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If I {a1} the invoices, I {a2} unsure now.',
            [
                'a1' => $this->ifPastPerfectMixed('I', $this->forms('archive', 'archived', 'archived')),
                'a2' => $this->resultWouldPresent('I', $this->forms('feel', 'felt', 'felt'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If you {a1} the files, you {a2} exposed now.',
            [
                'a1' => $this->ifPastPerfectMixed('you', $this->forms('encrypt', 'encrypted', 'encrypted')),
                'a2' => $this->resultWouldPresent('you', $this->forms('be', 'were', 'been'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If the curator {a1} the pieces, we {a2} uncertain now.',
            [
                'a1' => $this->ifPastPerfectMixed('the curator', $this->forms('catalog', 'cataloged', 'cataloged')),
                'a2' => $this->resultWouldPresent('we', $this->forms('feel', 'felt', 'felt'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If Helena {a1} the briefing, she {a2} hesitant now.',
            [
                'a1' => $this->ifPastPerfectMixed('Helena', $this->forms('rehearse', 'rehearsed', 'rehearsed')),
                'a2' => $this->resultWouldPresent('she', $this->forms('be', 'was', 'been'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If the engineers {a1} the valves, they {a2} anxious now.',
            [
                'a1' => $this->ifPastPerfectMixed('the engineers', $this->forms('inspect', 'inspected', 'inspected')),
                'a2' => $this->resultWouldPresent('they', $this->forms('feel', 'felt', 'felt'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If our partners {a1} the venue, the team {a2} restless now.',
            [
                'a1' => $this->ifPastPerfectMixed('our partners', $this->forms('confirm', 'confirmed', 'confirmed')),
                'a2' => $this->resultWouldPresent('the team', $this->forms('be', 'was', 'been'), true),
            ]
        );

        // Question focus (6)
        $entries[] = $this->makeEntry(
            'B2',
            'question',
            ['Mixed Conditional (Third→Second)'],
            'If you {a1} the deadline chart, {a2} confident now?',
            [
                'a1' => $this->ifPastPerfectMixed('you', $this->forms('audit', 'audited', 'audited')),
                'a2' => $this->resultWouldPresentQuestion('you', $this->forms('feel', 'felt', 'felt')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'question',
            ['Mixed Conditional (Second→Third)'],
            'If Marcus {a1} more flexible, {a2} the client yesterday?',
            [
                'a1' => $this->ifPastSimpleWere('Marcus'),
                'a2' => $this->resultWouldHaveQuestion('he', $this->forms('reassure', 'reassured', 'reassured')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'question',
            ['Mixed Conditional (Second→Third)'],
            'If the routers {a1} stable, {a2} the outage yesterday?',
            [
                'a1' => $this->ifPastSimpleWere('the routers'),
                'a2' => $this->resultWouldHaveQuestion('the team', $this->forms('prevent', 'prevented', 'prevented')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'question',
            ['Mixed Conditional (Third→Second)'],
            'If Lila {a1} the dossier, {a2} calm now?',
            [
                'a1' => $this->ifPastPerfectMixed('Lila', $this->forms('review', 'reviewed', 'reviewed')),
                'a2' => $this->resultWouldPresentQuestion('she', $this->forms('feel', 'felt', 'felt')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'question',
            ['Mixed Conditional (Third→Second)'],
            'If the delegation {a1} earlier, {a2} in the hall now?',
            [
                'a1' => $this->ifPastPerfectMixed('the delegation', $this->forms('arrive', 'arrived', 'arrived')),
                'a2' => $this->resultWouldPresentQuestion('they', $this->forms('be', 'were', 'been')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'question',
            ['Mixed Conditional (Second→Third)'],
            'If the board {a1} united, {a2} the vote yesterday?',
            [
                'a1' => $this->ifPastSimpleWere('the board'),
                'a2' => $this->resultWouldHaveQuestion('they', $this->forms('delay', 'delayed', 'delayed')),
            ]
        );

        // Present-result focus (6)
        $entries[] = $this->makeEntry(
            'B2',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If I {a1} the metrics, I {a2} decisive now.',
            [
                'a1' => $this->ifPastPerfectMixed('I', $this->forms('analyze', 'analyzed', 'analyzed')),
                'a2' => $this->resultWouldPresent('I', $this->forms('feel', 'felt', 'felt')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If you {a1} the backup plan, you {a2} composed now.',
            [
                'a1' => $this->ifPastPerfectMixed('you', $this->forms('draft', 'drafted', 'drafted')),
                'a2' => $this->resultWouldPresent('you', $this->forms('feel', 'felt', 'felt')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If the analysts {a1} the forecast, they {a2} aligned now.',
            [
                'a1' => $this->ifPastPerfectMixed('the analysts', $this->forms('refine', 'refined', 'refined')),
                'a2' => $this->resultWouldPresent('they', $this->forms('be', 'were', 'been')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If Sofia {a1} the itinerary, she {a2} composed now.',
            [
                'a1' => $this->ifPastPerfectMixed('Sofia', $this->forms('finalize', 'finalized', 'finalized')),
                'a2' => $this->resultWouldPresent('she', $this->forms('feel', 'felt', 'felt')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If the bureau {a1} the permits, it {a2} confident now.',
            [
                'a1' => $this->ifPastPerfectMixed('the bureau', $this->forms('process', 'processed', 'processed')),
                'a2' => $this->resultWouldPresent('it', $this->forms('be', 'was', 'been')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If our mentors {a1} weekly feedback, we {a2} steady now.',
            [
                'a1' => $this->ifPastPerfectMixed('our mentors', $this->forms('provide', 'provided', 'provided')),
                'a2' => $this->resultWouldPresent('we', $this->forms('feel', 'felt', 'felt')),
            ]
        );

        // Past-result focus (6)
        $entries[] = $this->makeEntry(
            'B2',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If she {a1} more diplomatic, she {a2} the partnership yesterday.',
            [
                'a1' => $this->ifPastSimpleWere('she'),
                'a2' => $this->resultWouldHave('she', $this->forms('secure', 'secured', 'secured')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If the auditors {a1} vigilant, they {a2} the fraud last quarter.',
            [
                'a1' => $this->ifPastSimpleMixed('the auditors', $this->forms('remain', 'remained', 'remained')),
                'a2' => $this->resultWouldHave('they', $this->forms('detect', 'detected', 'detected')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If we {a1} less rigid, we {a2} the compromise yesterday.',
            [
                'a1' => $this->ifPastSimpleWere('we'),
                'a2' => $this->resultWouldHave('we', $this->forms('reach', 'reached', 'reached')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If I {a1} more assertive, I {a2} the proposal yesterday.',
            [
                'a1' => $this->ifPastSimpleMixed('I', $this->forms('feel', 'felt', 'felt')),
                'a2' => $this->resultWouldHave('I', $this->forms('present', 'presented', 'presented')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If the network {a1} resilient, it {a2} the crash yesterday.',
            [
                'a1' => $this->ifPastSimpleWere('the network'),
                'a2' => $this->resultWouldHave('it', $this->forms('avoid', 'avoided', 'avoided')),
            ]
        );

        $entries[] = $this->makeEntry(
            'B2',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If your advisor {a1} candid, you {a2} the risk yesterday.',
            [
                'a1' => $this->ifPastSimpleWere('your advisor'),
                'a2' => $this->resultWouldHave('you', $this->forms('spot', 'spotted', 'spotted')),
            ]
        );

        return $entries;
    }

    private function buildC1Questions(): array
    {
        $entries = [];

        // Negative focus (6)
        $entries[] = $this->makeEntry(
            'C1',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If I {a1} the statutes, I {a2} uncertain now.',
            [
                'a1' => $this->ifPastPerfectMixed('I', $this->forms('consult', 'consulted', 'consulted')),
                'a2' => $this->resultWouldPresent('I', $this->forms('feel', 'felt', 'felt'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If you {a1} the compliance memo, you {a2} exposed now.',
            [
                'a1' => $this->ifPastPerfectMixed('you', $this->forms('circulate', 'circulated', 'circulated')),
                'a2' => $this->resultWouldPresent('you', $this->forms('be', 'were', 'been'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If Dr. Rao {a1} the sensors, we {a2} anxious now.',
            [
                'a1' => $this->ifPastPerfectMixed('Dr. Rao', $this->forms('calibrate', 'calibrated', 'calibrated')),
                'a2' => $this->resultWouldPresent('we', $this->forms('feel', 'felt', 'felt'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If the editorial board {a1} the sources, the newsroom {a2} skeptical now.',
            [
                'a1' => $this->ifPastPerfectMixed('the editorial board', $this->forms('vet', 'vetted', 'vetted')),
                'a2' => $this->resultWouldPresent('the newsroom', $this->forms('be', 'was', 'been'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If Lina {a1} the deposition, she {a2} hesitant now.',
            [
                'a1' => $this->ifPastPerfectMixed('Lina', $this->forms('rehearse', 'rehearsed', 'rehearsed')),
                'a2' => $this->resultWouldPresent('she', $this->forms('be', 'was', 'been'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If the orchestra {a1} the strings, the conductor {a2} uneasy now.',
            [
                'a1' => $this->ifPastPerfectMixed('the orchestra', $this->forms('tune', 'tuned', 'tuned')),
                'a2' => $this->resultWouldPresent('the conductor', $this->forms('feel', 'felt', 'felt'), true),
            ]
        );

        // Question focus (6)
        $entries[] = $this->makeEntry(
            'C1',
            'question',
            ['Mixed Conditional (Third→Second)'],
            'If you {a1} the fiscal models, {a2} decisive now?',
            [
                'a1' => $this->ifPastPerfectMixed('you', $this->forms('simulate', 'simulated', 'simulated')),
                'a2' => $this->resultWouldPresentQuestion('you', $this->forms('feel', 'felt', 'felt')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'question',
            ['Mixed Conditional (Second→Third)'],
            'If Marcus {a1} more transparent, {a2} the investors yesterday?',
            [
                'a1' => $this->ifPastSimpleWere('Marcus'),
                'a2' => $this->resultWouldHaveQuestion('he', $this->forms('reassure', 'reassured', 'reassured')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'question',
            ['Mixed Conditional (Second→Third)'],
            'If the archive {a1} accessible, {a2} the brief yesterday?',
            [
                'a1' => $this->ifPastSimpleWere('the archive'),
                'a2' => $this->resultWouldHaveQuestion('we', $this->forms('complete', 'completed', 'completed')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'question',
            ['Mixed Conditional (Third→Second)'],
            'If the auditors {a1} earlier, {a2} in the chamber now?',
            [
                'a1' => $this->ifPastPerfectMixed('the auditors', $this->forms('arrive', 'arrived', 'arrived')),
                'a2' => $this->resultWouldPresentQuestion('they', $this->forms('be', 'were', 'been')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'question',
            ['Mixed Conditional (Third→Second)'],
            'If Daria {a1} her testimony, {a2} confident now?',
            [
                'a1' => $this->ifPastPerfectMixed('Daria', $this->forms('revise', 'revised', 'revised')),
                'a2' => $this->resultWouldPresentQuestion('she', $this->forms('feel', 'felt', 'felt')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'question',
            ['Mixed Conditional (Second→Third)'],
            'If the envoy {a1} more flexible, {a2} the treaty yesterday?',
            [
                'a1' => $this->ifPastSimpleWere('the envoy'),
                'a2' => $this->resultWouldHaveQuestion('they', $this->forms('sign', 'signed', 'signed')),
            ]
        );

        // Present-result focus (6)
        $entries[] = $this->makeEntry(
            'C1',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If I {a1} the litigation timeline, I {a2} calm now.',
            [
                'a1' => $this->ifPastPerfectMixed('I', $this->forms('map', 'mapped', 'mapped')),
                'a2' => $this->resultWouldPresent('I', $this->forms('feel', 'felt', 'felt')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If you {a1} the stakeholder survey, you {a2} persuasive now.',
            [
                'a1' => $this->ifPastPerfectMixed('you', $this->forms('synthesize', 'synthesized', 'synthesized')),
                'a2' => $this->resultWouldPresent('you', $this->forms('sound', 'sounded', 'sounded')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If the research team {a1} the anomalies, they {a2} confident now.',
            [
                'a1' => $this->ifPastPerfectMixed('the research team', $this->forms('flag', 'flagged', 'flagged')),
                'a2' => $this->resultWouldPresent('they', $this->forms('be', 'were', 'been')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If Mira {a1} the panel brief, she {a2} composed now.',
            [
                'a1' => $this->ifPastPerfectMixed('Mira', $this->forms('draft', 'drafted', 'drafted')),
                'a2' => $this->resultWouldPresent('she', $this->forms('feel', 'felt', 'felt')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If the consortium {a1} the funding, it {a2} stable now.',
            [
                'a1' => $this->ifPastPerfectMixed('the consortium', $this->forms('secure', 'secured', 'secured')),
                'a2' => $this->resultWouldPresent('it', $this->forms('be', 'was', 'been')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If our analysts {a1} the pilot data, we {a2} decisive now.',
            [
                'a1' => $this->ifPastPerfectMixed('our analysts', $this->forms('interpret', 'interpreted', 'interpreted')),
                'a2' => $this->resultWouldPresent('we', $this->forms('be', 'were', 'been')),
            ]
        );

        // Past-result focus (6)
        $entries[] = $this->makeEntry(
            'C1',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If she {a1} more meticulous, she {a2} the anomaly yesterday.',
            [
                'a1' => $this->ifPastSimpleWere('she'),
                'a2' => $this->resultWouldHave('she', $this->forms('catch', 'caught', 'caught')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If the board {a1} less divided, it {a2} the merger yesterday.',
            [
                'a1' => $this->ifPastSimpleWere('the board'),
                'a2' => $this->resultWouldHave('it', $this->forms('approve', 'approved', 'approved')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If we {a1} more courageous, we {a2} the reform yesterday.',
            [
                'a1' => $this->ifPastSimpleWere('we'),
                'a2' => $this->resultWouldHave('we', $this->forms('enact', 'enacted', 'enacted')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If I {a1} more strategic, I {a2} the negotiation yesterday.',
            [
                'a1' => $this->ifPastSimpleMixed('I', $this->forms('think', 'thought', 'thought')),
                'a2' => $this->resultWouldHave('I', $this->forms('secure', 'secured', 'secured')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If the regulator {a1} proactive, it {a2} the breach last month.',
            [
                'a1' => $this->ifPastSimpleWere('the regulator'),
                'a2' => $this->resultWouldHave('it', $this->forms('prevent', 'prevented', 'prevented')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C1',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If your counsel {a1} candid, you {a2} the clause yesterday.',
            [
                'a1' => $this->ifPastSimpleWere('your counsel'),
                'a2' => $this->resultWouldHave('you', $this->forms('revise', 'revised', 'revised')),
            ]
        );

        return $entries;
    }

    private function buildC2Questions(): array
    {
        $entries = [];

        // Negative focus (6)
        $entries[] = $this->makeEntry(
            'C2',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If I {a1} the testimonies, I {a2} uncertain now.',
            [
                'a1' => $this->ifPastPerfectMixed('I', $this->forms('triangulate', 'triangulated', 'triangulated')),
                'a2' => $this->resultWouldPresent('I', $this->forms('feel', 'felt', 'felt'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If you {a1} the dossier, you {a2} exposed now.',
            [
                'a1' => $this->ifPastPerfectMixed('you', $this->forms('redact', 'redacted', 'redacted')),
                'a2' => $this->resultWouldPresent('you', $this->forms('be', 'were', 'been'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If Professor Lin {a1} the algorithm, we {a2} anxious now.',
            [
                'a1' => $this->ifPastPerfectMixed('Professor Lin', $this->forms('validate', 'validated', 'validated')),
                'a2' => $this->resultWouldPresent('we', $this->forms('feel', 'felt', 'felt'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If the ethics panel {a1} the witnesses, the committee {a2} skeptical now.',
            [
                'a1' => $this->ifPastPerfectMixed('the ethics panel', $this->forms('interrogate', 'interrogated', 'interrogated')),
                'a2' => $this->resultWouldPresent('the committee', $this->forms('be', 'was', 'been'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If Serena {a1} the delegation, she {a2} hesitant now.',
            [
                'a1' => $this->ifPastPerfectMixed('Serena', $this->forms('brief', 'briefed', 'briefed')),
                'a2' => $this->resultWouldPresent('she', $this->forms('be', 'was', 'been'), true),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'negative',
            ['Mixed Conditional (Third→Second)'],
            'If the symphony {a1} the acoustics, the maestro {a2} uneasy now.',
            [
                'a1' => $this->ifPastPerfectMixed('the symphony', $this->forms('adjust', 'adjusted', 'adjusted')),
                'a2' => $this->resultWouldPresent('the maestro', $this->forms('feel', 'felt', 'felt'), true),
            ]
        );

        // Question focus (6)
        $entries[] = $this->makeEntry(
            'C2',
            'question',
            ['Mixed Conditional (Third→Second)'],
            'If you {a1} the contingency matrices, {a2} decisive now?',
            [
                'a1' => $this->ifPastPerfectMixed('you', $this->forms('construct', 'constructed', 'constructed')),
                'a2' => $this->resultWouldPresentQuestion('you', $this->forms('feel', 'felt', 'felt')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'question',
            ['Mixed Conditional (Second→Third)'],
            'If Chancellor Mehta {a1} more conciliatory, {a2} the accord yesterday?',
            [
                'a1' => $this->ifPastSimpleWere('Chancellor Mehta'),
                'a2' => $this->resultWouldHaveQuestion('she', $this->forms('salvage', 'salvaged', 'salvaged')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'question',
            ['Mixed Conditional (Second→Third)'],
            'If the archive servers {a1} stable, {a2} the audit yesterday?',
            [
                'a1' => $this->ifPastSimpleWere('the archive servers'),
                'a2' => $this->resultWouldHaveQuestion('we', $this->forms('finalize', 'finalized', 'finalized')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'question',
            ['Mixed Conditional (Third→Second)'],
            'If the arbitration team {a1} earlier, {a2} in chambers now?',
            [
                'a1' => $this->ifPastPerfectMixed('the arbitration team', $this->forms('arrive', 'arrived', 'arrived')),
                'a2' => $this->resultWouldPresentQuestion('they', $this->forms('be', 'were', 'been')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'question',
            ['Mixed Conditional (Third→Second)'],
            'If Director Cho {a1} her notes, {a2} composed now?',
            [
                'a1' => $this->ifPastPerfectMixed('Director Cho', $this->forms('distill', 'distilled', 'distilled')),
                'a2' => $this->resultWouldPresentQuestion('she', $this->forms('feel', 'felt', 'felt')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'question',
            ['Mixed Conditional (Second→Third)'],
            'If the envoy corps {a1} more adaptable, {a2} the sanctions yesterday?',
            [
                'a1' => $this->ifPastSimpleWere('the envoy corps'),
                'a2' => $this->resultWouldHaveQuestion('they', $this->forms('lift', 'lifted', 'lifted')),
            ]
        );

        // Present-result focus (6)
        $entries[] = $this->makeEntry(
            'C2',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If I {a1} the precedents, I {a2} resolute now.',
            [
                'a1' => $this->ifPastPerfectMixed('I', $this->forms('sift', 'sifted', 'sifted')),
                'a2' => $this->resultWouldPresent('I', $this->forms('feel', 'felt', 'felt')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If you {a1} the donor analytics, you {a2} persuasive now.',
            [
                'a1' => $this->ifPastPerfectMixed('you', $this->forms('parse', 'parsed', 'parsed')),
                'a2' => $this->resultWouldPresent('you', $this->forms('sound', 'sounded', 'sounded')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If the think tank {a1} the simulations, it {a2} credible now.',
            [
                'a1' => $this->ifPastPerfectMixed('the think tank', $this->forms('model', 'modeled', 'modeled')),
                'a2' => $this->resultWouldPresent('it', $this->forms('be', 'was', 'been')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If Amina {a1} the summit brief, she {a2} composed now.',
            [
                'a1' => $this->ifPastPerfectMixed('Amina', $this->forms('synthesize', 'synthesized', 'synthesized')),
                'a2' => $this->resultWouldPresent('she', $this->forms('feel', 'felt', 'felt')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If the law firm {a1} the precedent bank, it {a2} agile now.',
            [
                'a1' => $this->ifPastPerfectMixed('the law firm', $this->forms('curate', 'curated', 'curated')),
                'a2' => $this->resultWouldPresent('it', $this->forms('be', 'was', 'been')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'present',
            ['Mixed Conditional (Third→Second)'],
            'If our negotiators {a1} the counterarguments, we {a2} confident now.',
            [
                'a1' => $this->ifPastPerfectMixed('our negotiators', $this->forms('anticipate', 'anticipated', 'anticipated')),
                'a2' => $this->resultWouldPresent('we', $this->forms('feel', 'felt', 'felt')),
            ]
        );

        // Past-result focus (6)
        $entries[] = $this->makeEntry(
            'C2',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If she {a1} more dispassionate, she {a2} the tribunal yesterday.',
            [
                'a1' => $this->ifPastSimpleWere('she'),
                'a2' => $this->resultWouldHave('she', $this->forms('persuade', 'persuaded', 'persuaded')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If the council {a1} less partisan, it {a2} the budget yesterday.',
            [
                'a1' => $this->ifPastSimpleWere('the council'),
                'a2' => $this->resultWouldHave('it', $this->forms('ratify', 'ratified', 'ratified')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If we {a1} more audacious, we {a2} the amendment yesterday.',
            [
                'a1' => $this->ifPastSimpleWere('we'),
                'a2' => $this->resultWouldHave('we', $this->forms('advance', 'advanced', 'advanced')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If I {a1} more analytical, I {a2} the loophole yesterday.',
            [
                'a1' => $this->ifPastSimpleMixed('I', $this->forms('probe', 'probed', 'probed')),
                'a2' => $this->resultWouldHave('I', $this->forms('spot', 'spotted', 'spotted')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If the regulator {a1} less cautious, it {a2} the embargo yesterday.',
            [
                'a1' => $this->ifPastSimpleWere('the regulator'),
                'a2' => $this->resultWouldHave('it', $this->forms('lift', 'lifted', 'lifted')),
            ]
        );

        $entries[] = $this->makeEntry(
            'C2',
            'past',
            ['Mixed Conditional (Second→Third)'],
            'If your advisor {a1} more direct, you {a2} the clause yesterday.',
            [
                'a1' => $this->ifPastSimpleWere('your advisor'),
                'a2' => $this->resultWouldHave('you', $this->forms('amend', 'amended', 'amended')),
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

    private function hintVerb(string $subject, string $verb, bool $negative = false): string
    {
        $subject = trim($subject) === '' ? '' : ' (' . $subject . ')';
        $prefix = $negative ? 'not ' : '';

        return trim($prefix . $verb . $subject);
    }

    private function ifPastPerfectMixed(string $subject, array $forms, bool $negative = false): array
    {
        $answer = $negative ? "hadn't " . $forms['participle'] : 'had ' . $forms['participle'];

        $options = $negative
            ? [
                ['value' => $answer, 'reason' => 'correct'],
                ['value' => "didn't " . $forms['base'], 'reason' => 'did_not'],
                ['value' => $forms['base'], 'reason' => 'present_simple'],
                ['value' => 'would have ' . $forms['participle'], 'reason' => 'modal_result'],
            ]
            : [
                ['value' => $answer, 'reason' => 'correct'],
                ['value' => $forms['past'], 'reason' => 'past_simple'],
                ['value' => $forms['base'], 'reason' => 'present_simple'],
                ['value' => 'would have ' . $forms['participle'], 'reason' => 'modal_result'],
            ];

        return [
            'type' => 'if_past_perfect_mixed',
            'subject' => $subject,
            'verb' => $forms['base'],
            'verb_hint' => $this->hintVerb($subject, $forms['base'], $negative),
            'answer' => $answer,
            'options' => $options,
        ];
    }

    private function ifPastSimpleMixed(string $subject, array $forms, bool $negative = false): array
    {
        $answer = $negative ? "didn't " . $forms['base'] : $forms['past'];

        $options = $negative
            ? [
                ['value' => $answer, 'reason' => 'correct'],
                ['value' => "don't " . $forms['base'], 'reason' => 'present_simple'],
                ['value' => 'had ' . $forms['participle'], 'reason' => 'past_perfect'],
                ['value' => 'would ' . $forms['base'], 'reason' => 'modal_result'],
            ]
            : [
                ['value' => $answer, 'reason' => 'correct'],
                ['value' => $forms['base'], 'reason' => 'present_simple'],
                ['value' => 'had ' . $forms['participle'], 'reason' => 'past_perfect'],
                ['value' => 'would ' . $forms['base'], 'reason' => 'modal_result'],
            ];

        return [
            'type' => 'if_past_simple_mixed',
            'subject' => $subject,
            'verb' => $forms['base'],
            'verb_hint' => $this->hintVerb($subject, $forms['base'], $negative),
            'answer' => $answer,
            'options' => $options,
        ];
    }

    private function ifPastSimpleWere(string $subject, bool $negative = false): array
    {
        $answer = $negative ? "weren't" : 'were';

        $options = $negative
            ? [
                ['value' => $answer, 'reason' => 'correct'],
                ['value' => "aren't", 'reason' => 'present_simple'],
                ['value' => 'had been', 'reason' => 'past_perfect'],
                ['value' => 'would be', 'reason' => 'modal_result'],
            ]
            : [
                ['value' => $answer, 'reason' => 'correct'],
                ['value' => 'was', 'reason' => 'present_simple'],
                ['value' => 'had been', 'reason' => 'past_perfect'],
                ['value' => 'would be', 'reason' => 'modal_result'],
            ];

        return [
            'type' => 'if_past_simple_were',
            'subject' => $subject,
            'verb' => 'be',
            'verb_hint' => $this->hintVerb($subject, 'be', $negative),
            'answer' => $answer,
            'options' => $options,
        ];
    }

    private function resultWouldPresent(string $subject, array $forms, bool $negative = false): array
    {
        $answer = $negative ? 'would not ' . $forms['base'] : 'would ' . $forms['base'];

        $options = $negative
            ? [
                ['value' => $answer, 'reason' => 'correct'],
                ['value' => 'would not have ' . $forms['participle'], 'reason' => 'past_result'],
                ['value' => 'will not ' . $forms['base'], 'reason' => 'first_conditional'],
                ['value' => $forms['base'], 'reason' => 'present_simple'],
            ]
            : [
                ['value' => $answer, 'reason' => 'correct'],
                ['value' => 'would have ' . $forms['participle'], 'reason' => 'past_result'],
                ['value' => 'will ' . $forms['base'], 'reason' => 'first_conditional'],
                ['value' => $forms['base'], 'reason' => 'present_simple'],
            ];

        return [
            'type' => $negative ? 'result_would_present_negative' : 'result_would_present',
            'subject' => $subject,
            'verb' => $forms['base'],
            'verb_hint' => $this->hintVerb($subject, $forms['base'], $negative),
            'answer' => $answer,
            'options' => $options,
        ];
    }

    private function resultWouldPresentQuestion(string $subject, array $forms): array
    {
        $subjectLower = strtolower($subject);
        $answer = 'would ' . $subjectLower . ' ' . $forms['base'];

        return [
            'type' => 'result_would_present_question',
            'subject' => $subject,
            'verb' => $forms['base'],
            'verb_hint' => $this->hintVerb($subject, $forms['base'], false),
            'answer' => $answer,
            'options' => [
                ['value' => $answer, 'reason' => 'correct'],
                ['value' => 'would ' . $subjectLower . ' have ' . $forms['participle'], 'reason' => 'past_result'],
                ['value' => 'will ' . $subjectLower . ' ' . $forms['base'], 'reason' => 'first_conditional'],
                ['value' => $subjectLower . ' ' . $forms['base'], 'reason' => 'present_simple'],
            ],
        ];
    }

    private function resultWouldHave(string $subject, array $forms, bool $negative = false): array
    {
        $answer = $negative ? 'would not have ' . $forms['participle'] : 'would have ' . $forms['participle'];

        $options = $negative
            ? [
                ['value' => $answer, 'reason' => 'correct'],
                ['value' => 'would not ' . $forms['base'], 'reason' => 'present_result'],
                ['value' => 'will not ' . $forms['base'], 'reason' => 'first_conditional'],
                ['value' => 'would not ' . $forms['participle'], 'reason' => 'missing_have'],
            ]
            : [
                ['value' => $answer, 'reason' => 'correct'],
                ['value' => 'would ' . $forms['base'], 'reason' => 'present_result'],
                ['value' => 'will ' . $forms['base'], 'reason' => 'first_conditional'],
                ['value' => $forms['past'], 'reason' => 'past_simple'],
            ];

        return [
            'type' => $negative ? 'result_would_have_negative' : 'result_would_have_past',
            'subject' => $subject,
            'verb' => $forms['base'],
            'verb_hint' => $this->hintVerb($subject, $forms['base'], $negative),
            'answer' => $answer,
            'options' => $options,
        ];
    }

    private function resultWouldHaveQuestion(string $subject, array $forms): array
    {
        $subjectLower = strtolower($subject);
        $answer = 'would ' . $subjectLower . ' have ' . $forms['participle'];

        return [
            'type' => 'result_would_have_question',
            'subject' => $subject,
            'verb' => $forms['base'],
            'verb_hint' => $this->hintVerb($subject, $forms['base'], false),
            'answer' => $answer,
            'options' => [
                ['value' => $answer, 'reason' => 'correct'],
                ['value' => 'would ' . $subjectLower . ' ' . $forms['base'], 'reason' => 'present_result'],
                ['value' => 'will ' . $subjectLower . ' ' . $forms['base'], 'reason' => 'first_conditional'],
                ['value' => $subjectLower . ' ' . $forms['base'], 'reason' => 'present_simple'],
            ],
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

