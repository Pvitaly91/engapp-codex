<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class PresentSimpleOrContinuousV2Seeder extends QuestionSeeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Present Simple or Continuous V2'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Custom: Present Simple or Continuous V2'])->id;

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Present Simple vs Continuous'],
            ['category' => 'English Grammar Theme']
        )->id;

        $tenseTags = [
            'Present Simple' => Tag::firstOrCreate(['name' => 'Present Simple'], ['category' => 'Tenses'])->id,
            'Present Continuous' => Tag::firstOrCreate(['name' => 'Present Continuous'], ['category' => 'Tenses'])->id,
        ];

        $patternConfig = [
            'present_simple_question_aux' => [
                'structure' => 'Present Simple Question Form',
                'tense' => ['Present Simple'],
            ],
            'present_simple_base' => [
                'structure' => 'Present Simple Base Form',
                'tense' => ['Present Simple'],
            ],
            'present_simple_statement' => [
                'structure' => 'Present Simple Statement',
                'tense' => ['Present Simple'],
            ],
            'present_simple_negative' => [
                'structure' => 'Present Simple Negative',
                'tense' => ['Present Simple'],
            ],
            'present_continuous_statement' => [
                'structure' => 'Present Continuous Statement',
                'tense' => ['Present Continuous'],
            ],
            'present_continuous_negative' => [
                'structure' => 'Present Continuous Negative',
                'tense' => ['Present Continuous'],
            ],
            'present_continuous_question_aux' => [
                'structure' => 'Present Continuous Question Form',
                'tense' => ['Present Continuous'],
            ],
            'present_continuous_ing' => [
                'structure' => 'Present Continuous Verb-ing Form',
                'tense' => ['Present Continuous'],
            ],
        ];

        $structureTagIds = [];
        foreach ($patternConfig as $config) {
            $structure = $config['structure'];
            if (! isset($structureTagIds[$structure])) {
                $structureTagIds[$structure] = Tag::firstOrCreate(
                    ['name' => $structure],
                    ['category' => 'English Grammar Structure']
                )->id;
            }
        }

        $levelDifficulty = [
            'A1' => 1,
            'A2' => 2,
            'B1' => 3,
            'B2' => 4,
            'C1' => 5,
            'C2' => 5,
        ];

        $questions = [
            [
                'question' => 'Listen! Somebody {a1} the violin.',
                'level' => 'A2',
                'tense' => ['Present Continuous'],
                'slots' => [
                    'a1' => [
                        'type' => 'present_continuous_statement',
                        'forms' => $this->forms('he_she_it', 'play', 'playing', 'plays'),
                        'verb_hint' => 'play',
                    ],
                ],
            ],
            [
                'question' => 'How often {a1} you {a2} to the cinema?',
                'level' => 'A2',
                'tense' => ['Present Simple'],
                'slots' => [
                    'a1' => [
                        'type' => 'present_simple_question_aux',
                        'forms' => $this->forms('you', 'go', 'going', 'goes'),
                        'verb_hint' => 'question auxiliary',
                    ],
                    'a2' => [
                        'type' => 'present_simple_base',
                        'forms' => $this->forms('you', 'go', 'going', 'goes'),
                        'verb_hint' => 'go',
                    ],
                ],
            ],
            [
                'question' => 'Why {a1} you {a2} at me now?',
                'level' => 'A2',
                'tense' => ['Present Continuous'],
                'slots' => [
                    'a1' => [
                        'type' => 'present_continuous_question_aux',
                        'forms' => $this->forms('you', 'shout', 'shouting', 'shouts'),
                        'verb_hint' => 'be (question)',
                    ],
                    'a2' => [
                        'type' => 'present_continuous_ing',
                        'forms' => $this->forms('you', 'shout', 'shouting', 'shouts'),
                        'verb_hint' => 'shout',
                    ],
                ],
            ],
            [
                'question' => 'Shh! The baby {a1} asleep.',
                'level' => 'A2',
                'tense' => ['Present Continuous'],
                'slots' => [
                    'a1' => [
                        'type' => 'present_continuous_statement',
                        'forms' => $this->forms('he_she_it', 'fall', 'falling', 'falls'),
                        'verb_hint' => 'fall',
                    ],
                ],
            ],
            [
                'question' => '{a1} you {a2} who I am?',
                'level' => 'A2',
                'tense' => ['Present Simple'],
                'slots' => [
                    'a1' => [
                        'type' => 'present_simple_question_aux',
                        'forms' => $this->forms('you', 'know', 'knowing', 'knows'),
                        'verb_hint' => 'question auxiliary',
                        'capitalize' => true,
                    ],
                    'a2' => [
                        'type' => 'present_simple_base',
                        'forms' => $this->forms('you', 'know', 'knowing', 'knows'),
                        'verb_hint' => 'know',
                    ],
                ],
            ],
            [
                'question' => 'How much coffee {a1} your parents {a2}?',
                'level' => 'A2',
                'tense' => ['Present Simple'],
                'slots' => [
                    'a1' => [
                        'type' => 'present_simple_question_aux',
                        'forms' => $this->forms('they', 'drink', 'drinking', 'drinks'),
                        'verb_hint' => 'question auxiliary',
                    ],
                    'a2' => [
                        'type' => 'present_simple_base',
                        'forms' => $this->forms('they', 'drink', 'drinking', 'drinks'),
                        'verb_hint' => 'drink',
                    ],
                ],
            ],
            [
                'question' => 'Look! The boys {a1} football.',
                'level' => 'A2',
                'tense' => ['Present Continuous'],
                'slots' => [
                    'a1' => [
                        'type' => 'present_continuous_statement',
                        'forms' => $this->forms('they', 'play', 'playing', 'plays'),
                        'verb_hint' => 'play',
                    ],
                ],
            ],
            [
                'question' => 'Quick! They {a1}.',
                'level' => 'A2',
                'tense' => ['Present Continuous'],
                'slots' => [
                    'a1' => [
                        'type' => 'present_continuous_negative',
                        'forms' => $this->forms('they', 'look', 'looking', 'looks'),
                        'verb_hint' => 'not/look',
                    ],
                ],
            ],
            [
                'question' => "My best friend {a1} meat. She's a vegetarian.",
                'level' => 'A2',
                'tense' => ['Present Simple'],
                'slots' => [
                    'a1' => [
                        'type' => 'present_simple_negative',
                        'forms' => $this->forms('he_she_it', 'eat', 'eating', 'eats'),
                        'verb_hint' => 'not/eat',
                    ],
                ],
            ],
            [
                'question' => 'Who {a1} you {a2} to now?',
                'level' => 'A2',
                'tense' => ['Present Continuous'],
                'slots' => [
                    'a1' => [
                        'type' => 'present_continuous_question_aux',
                        'forms' => $this->forms('you', 'talk', 'talking', 'talks'),
                        'verb_hint' => 'be (question)',
                    ],
                    'a2' => [
                        'type' => 'present_continuous_ing',
                        'forms' => $this->forms('you', 'talk', 'talking', 'talks'),
                        'verb_hint' => 'talk',
                    ],
                ],
            ],
            [
                'question' => 'Shh! Dad {a1} the news.',
                'level' => 'A2',
                'tense' => ['Present Continuous'],
                'slots' => [
                    'a1' => [
                        'type' => 'present_continuous_statement',
                        'forms' => $this->forms('he_she_it', 'watch', 'watching', 'watches'),
                        'verb_hint' => 'watch',
                    ],
                ],
            ],
            [
                'question' => 'Where {a1} you {a2}?',
                'level' => 'A2',
                'tense' => ['Present Continuous'],
                'slots' => [
                    'a1' => [
                        'type' => 'present_continuous_question_aux',
                        'forms' => $this->forms('you', 'go', 'going', 'goes'),
                        'verb_hint' => 'be (question)',
                    ],
                    'a2' => [
                        'type' => 'present_continuous_ing',
                        'forms' => $this->forms('you', 'go', 'going', 'goes'),
                        'verb_hint' => 'go',
                    ],
                ],
            ],
            [
                'question' => '{a1} Sue {a2} chocolate?',
                'level' => 'A2',
                'tense' => ['Present Simple'],
                'slots' => [
                    'a1' => [
                        'type' => 'present_simple_question_aux',
                        'forms' => $this->forms('he_she_it', 'like', 'liking', 'likes'),
                        'verb_hint' => 'question auxiliary',
                        'capitalize' => true,
                    ],
                    'a2' => [
                        'type' => 'present_simple_base',
                        'forms' => $this->forms('he_she_it', 'like', 'liking', 'likes'),
                        'verb_hint' => 'like',
                    ],
                ],
            ],
            [
                'question' => 'We {a1} on a school trip today.',
                'level' => 'A2',
                'tense' => ['Present Continuous'],
                'slots' => [
                    'a1' => [
                        'type' => 'present_continuous_statement',
                        'forms' => $this->forms('we', 'go', 'going', 'goes'),
                        'verb_hint' => 'go',
                    ],
                ],
            ],
            [
                'question' => 'I {a1} a bike every day. Only at weekends.',
                'level' => 'A2',
                'tense' => ['Present Simple'],
                'slots' => [
                    'a1' => [
                        'type' => 'present_simple_negative',
                        'forms' => $this->forms('i', 'ride', 'riding', 'rides'),
                        'verb_hint' => 'not/ride',
                    ],
                ],
            ],
            [
                'question' => "I {a1} to school this week. I'm ill.",
                'level' => 'A2',
                'tense' => ['Present Continuous'],
                'slots' => [
                    'a1' => [
                        'type' => 'present_continuous_negative',
                        'forms' => $this->forms('i', 'go', 'going', 'goes'),
                        'verb_hint' => 'not/go',
                        'negative_form' => 'full',
                    ],
                ],
            ],
            [
                'question' => 'We usually {a1} the weekends in the mountains in our summer house.',
                'level' => 'A2',
                'tense' => ['Present Simple'],
                'slots' => [
                    'a1' => [
                        'type' => 'present_simple_statement',
                        'forms' => $this->forms('we', 'spend', 'spending', 'spends'),
                        'verb_hint' => 'spend',
                        'form' => 'base',
                    ],
                ],
            ],
            [
                'question' => 'You {a1} to me again!',
                'level' => 'A2',
                'tense' => ['Present Continuous'],
                'slots' => [
                    'a1' => [
                        'type' => 'present_continuous_negative',
                        'forms' => $this->forms('you', 'listen', 'listening', 'listens'),
                        'verb_hint' => 'not/listen',
                    ],
                ],
            ],
            [
                'question' => 'Rebecca {a1} books. She {a2} films.',
                'level' => 'A2',
                'tense' => ['Present Simple'],
                'slots' => [
                    'a1' => [
                        'type' => 'present_simple_negative',
                        'forms' => $this->forms('he_she_it', 'read', 'reading', 'reads'),
                        'verb_hint' => 'not/read',
                    ],
                    'a2' => [
                        'type' => 'present_simple_statement',
                        'forms' => $this->forms('he_she_it', 'prefer', 'preferring', 'prefers'),
                        'verb_hint' => 'prefer',
                        'form' => 's',
                    ],
                ],
            ],
            [
                'question' => 'Look! Who {a1} she {a2} to?',
                'level' => 'A2',
                'tense' => ['Present Continuous'],
                'slots' => [
                    'a1' => [
                        'type' => 'present_continuous_question_aux',
                        'forms' => $this->forms('he_she_it', 'talk', 'talking', 'talks'),
                        'verb_hint' => 'be (question)',
                    ],
                    'a2' => [
                        'type' => 'present_continuous_ing',
                        'forms' => $this->forms('he_she_it', 'talk', 'talking', 'talks'),
                        'verb_hint' => 'talk',
                    ],
                ],
            ],
            [
                'question' => 'They {a1} tennis at the moment.',
                'level' => 'A2',
                'tense' => ['Present Continuous'],
                'slots' => [
                    'a1' => [
                        'type' => 'present_continuous_statement',
                        'forms' => $this->forms('they', 'play', 'playing', 'plays'),
                        'verb_hint' => 'play',
                    ],
                ],
            ],
        ];

        $items = [];
        $meta = [];

        foreach ($questions as $index => $question) {
            $uuid = $this->generateQuestionUuid($index + 1, $question['question']);

            $answersForSeeder = [];
            $answersForMeta = [];
            $optionMarkers = [];
            $options = [];
            $hints = [];

            foreach ($question['slots'] as $marker => $slot) {
                $answer = $this->buildAnswer($slot['type'], $slot['forms'], $slot);
                $answersForSeeder[] = [
                    'marker' => $marker,
                    'answer' => $answer,
                    'verb_hint' => $this->normalizeHint($slot['verb_hint'] ?? ''),
                ];
                $answersForMeta[$marker] = $answer;

                $slotOptions = $this->buildOptions($slot['type'], $slot['forms'], $slot);
                foreach ($slotOptions as $option) {
                    $optionMarkers[$option] = $marker;
                }
                $options = array_values(array_unique(array_merge($options, $slotOptions)));
            }

            $options = $this->ensureOptions($options, array_values($answersForMeta));

            $example = $this->formatExample($question['question'], $answersForMeta);

            foreach ($question['slots'] as $marker => $slot) {
                $hints[$marker] = $this->buildHint($slot['type'], $slot['forms'], $example);
            }

            $explanations = $this->buildExplanations($question['slots'], $options, $answersForMeta, $example);

            $tagIds = [$themeTagId];
            foreach ($question['tense'] as $tense) {
                if (isset($tenseTags[$tense])) {
                    $tagIds[] = $tenseTags[$tense];
                }
            }

            foreach ($question['slots'] as $slot) {
                $structure = $patternConfig[$slot['type']]['structure'] ?? null;
                if ($structure && isset($structureTagIds[$structure])) {
                    $tagIds[] = $structureTagIds[$structure];
                }
            }

            $items[] = [
                'uuid' => $uuid,
                'question' => $question['question'],
                'difficulty' => $levelDifficulty[$question['level']] ?? 2,
                'category_id' => $categoryId,
                'source_id' => $sourceId,
                'flag' => 0,
                'level' => $question['level'],
                'tag_ids' => array_values(array_unique($tagIds)),
                'answers' => $answersForSeeder,
                'options' => $options,
                'variants' => [],
            ];

            $meta[] = [
                'uuid' => $uuid,
                'answers' => $answersForMeta,
                'option_markers' => $optionMarkers,
                'hints' => $hints,
                'explanations' => $explanations,
            ];
        }

        $this->seedQuestionData($items, $meta);
    }

    private function forms(string $role, string $base, string $ing, string $thirdPerson): array
    {
        return [
            'role' => $role,
            'verb' => [
                'base' => $base,
                'ing' => $ing,
                'third' => $thirdPerson,
            ],
        ];
    }

    private function beForms(string $role): array
    {
        return match ($role) {
            'i' => [
                'statement' => 'am',
                'question' => 'am',
                'negative_full' => 'am not',
                'negative_contracted' => 'am not',
            ],
            'you', 'we', 'they' => [
                'statement' => 'are',
                'question' => 'are',
                'negative_full' => 'are not',
                'negative_contracted' => "aren't",
            ],
            default => [
                'statement' => 'is',
                'question' => 'is',
                'negative_full' => 'is not',
                'negative_contracted' => "isn't",
            ],
        };
    }

    private function doForms(string $role): array
    {
        $aux = in_array($role, ['he_she_it'], true) ? 'does' : 'do';

        return [
            'question' => $aux,
            'negative_full' => $aux . ' not',
            'negative_contracted' => in_array($role, ['he_she_it'], true) ? "doesn't" : "don't",
        ];
    }

    private function buildAnswer(string $type, array $forms, array $config): string
    {
        $be = $this->beForms($forms['role']);
        $do = $this->doForms($forms['role']);
        $verb = $forms['verb'];

        return match ($type) {
            'present_continuous_statement' => $be['statement'] . ' ' . $verb['ing'],
            'present_continuous_negative' => (($config['negative_form'] ?? 'contracted') === 'full'
                ? $be['negative_full']
                : $be['negative_contracted']) . ' ' . $verb['ing'],
            'present_continuous_question_aux' => ($config['capitalize'] ?? false)
                ? $this->titleCase($be['question'])
                : $be['question'],
            'present_continuous_ing' => $verb['ing'],
            'present_simple_question_aux' => ($config['capitalize'] ?? false)
                ? $this->titleCase($do['question'])
                : $do['question'],
            'present_simple_base' => $verb['base'],
            'present_simple_statement' => ($config['form'] ?? 'base') === 's'
                ? $verb['third']
                : $verb['base'],
            'present_simple_negative' => (($config['contracted'] ?? true) ? $do['negative_contracted'] : $do['negative_full'])
                . ' ' . $verb['base'],
            default => $verb['base'],
        };
    }

    private function buildOptions(string $type, array $forms, array $config): array
    {
        $be = $this->beForms($forms['role']);
        $do = $this->doForms($forms['role']);
        $verb = $forms['verb'];
        $answer = $this->buildAnswer($type, $forms, $config);

        $options = match ($type) {
            'present_continuous_statement' => [
                $answer,
                $verb['third'],
                $verb['base'],
                $be['statement'] . ' ' . $verb['base'],
            ],
            'present_continuous_negative' => [
                $answer,
                $be['statement'] . ' ' . $verb['ing'],
                $do['negative_contracted'] . ' ' . $verb['base'],
                $be['negative_full'] . ' ' . $verb['base'],
            ],
            'present_continuous_question_aux' => [
                $answer,
                $do['question'],
                $be['statement'],
                $this->oppositeBe($forms['role'], $be['question']),
            ],
            'present_continuous_ing' => [
                $answer,
                $verb['base'],
                $verb['third'],
                'to ' . $verb['base'],
            ],
            'present_simple_question_aux' => [
                $answer,
                $be['question'],
                $be['statement'],
                $this->oppositeDo($forms['role'], $do['question']),
            ],
            'present_simple_base' => [
                $answer,
                $verb['third'],
                $verb['ing'],
                $be['statement'] . ' ' . $verb['ing'],
            ],
            'present_simple_statement' => [
                $answer,
                $verb['third'],
                $be['statement'] . ' ' . $verb['ing'],
                $do['negative_contracted'] . ' ' . $verb['base'],
            ],
            'present_simple_negative' => [
                $answer,
                $be['negative_contracted'] . ' ' . $verb['ing'],
                $be['statement'] . ' ' . $verb['ing'],
                $do['question'] . ' ' . $verb['base'],
            ],
            default => [$answer],
        };

        return array_values(array_filter(array_unique(array_map('trim', $options))));
    }

    private function buildHint(string $type, array $forms, string $example): string
    {
        $verb = $forms['verb'];

        return match ($type) {
            'present_continuous_statement' => "Present Continuous = **be + V-ing**. Дія відбувається зараз. Приклад: *{$example}*.",
            'present_continuous_negative' => "Present Continuous negative = **be + not + V-ing**. Використовуємо, щоб показати, що дія не триває. Приклад: *{$example}*.",
            'present_continuous_question_aux' => "Present Continuous question = **be + subject + V-ing?**. Питаємо про дію у процесі. Приклад: *{$example}*.",
            'present_continuous_ing' => "Для Present Continuous потрібен V-ing: **{$verb['ing']}**. Приклад: *{$example}*.",
            'present_simple_question_aux' => "Present Simple question = **do/does + subject + V1?**. Підходить для звичок чи фактів. Приклад: *{$example}*.",
            'present_simple_base' => "Present Simple після do/does вимагає базову форму дієслова (**{$verb['base']}**). Приклад: *{$example}*.",
            'present_simple_statement' => "Present Simple = **V1** (для he/she/it додаємо -s). Приклад: *{$example}*.",
            'present_simple_negative' => "Present Simple negative = **do/does + not + V1**. Приклад: *{$example}*.",
            default => '',
        };
    }

    private function buildExplanations(array $slots, array $options, array $answers, string $example): array
    {
        $explanations = [];

        foreach ($options as $option) {
            $marker = array_key_first($answers);
            foreach ($answers as $key => $value) {
                if ($value === $option) {
                    $marker = $key;
                    break;
                }
            }

            $slot = $marker !== null && isset($slots[$marker]) ? $slots[$marker] : reset($slots);
            $answer = $answers[$marker] ?? reset($answers);

            $explanations[$option] = $this->explainOption($slot['type'], $slot['forms'], $option, $answer, $example);
        }

        return $explanations;
    }

    private function explainOption(string $type, array $forms, string $option, string $answer, string $example): string
    {
        if ($option === $answer) {
            return "✅ Правильно! {$this->correctExplanation($type, $forms, $example)}";
        }

        $verb = $forms['verb'];
        $be = $this->beForms($forms['role']);
        $do = $this->doForms($forms['role']);

        return match ($type) {
            'present_continuous_statement' => match ($option) {
                $verb['base'] => "❌ Це базова форма Present Simple. Для ситуації " .
                    'зараз потрібен be + V-ing.',
                $verb['third'] => "❌ Закінчення -s утворює Present Simple, а ми описуємо дію в момент мовлення.",
                $be['statement'] . ' ' . $verb['base'] => "❌ Після be потрібна форма V-ing, а не V1.",
                default => "❌ Неправильна форма для Present Continuous.",
            },
            'present_continuous_negative' => match ($option) {
                $be['statement'] . ' ' . $verb['ing'] => "❌ Це ствердна форма. Нам потрібно додати not.",
                $do['negative_contracted'] . ' ' . $verb['base'] => "❌ Допоміжне do/does не використовується у Present Continuous.",
                $be['negative_full'] . ' ' . $verb['base'] => "❌ Після be з not потрібен V-ing, а не V1.",
                default => "❌ Невірна форма заперечення для Present Continuous.",
            },
            'present_continuous_question_aux' => match ($option) {
                $do['question'] => "❌ Допоміжне do/does використовується у Present Simple, а не у Present Continuous.",
                $be['statement'] => "❌ На початку запитання потрібен інверсійний порядок: be перед підметом.",
                default => "❌ Неправильна допоміжна форма для питання в Present Continuous.",
            },
            'present_continuous_ing' => match ($option) {
                $verb['base'] => "❌ Після be треба використати форму V-ing, а не базову форму.",
                $verb['third'] => "❌ Закінчення -s вказує на Present Simple, а не на Continuous.",
                'to ' . $verb['base'] => "❌ Інфінітив не використовується після be у Present Continuous.",
                default => "❌ Потрібна форма V-ing для Present Continuous.",
            },
            'present_simple_question_aux' => match ($option) {
                $be['question'], $be['statement'] => "❌ У Present Simple питання будуємо з do/does, а не з be.",
                $this->oppositeDo($forms['role'], $do['question']) => "❌ Обираємо неправильну форму допоміжного дієслова для цього підмета.",
                default => "❌ Це не підходить для Present Simple питання.",
            },
            'present_simple_base' => match ($option) {
                $verb['third'] => "❌ Після do/does дієслово має бути в базовій формі без -s.",
                $verb['ing'] => "❌ Форма V-ing належить до Continuous, а не до Present Simple.",
                $be['statement'] . ' ' . $verb['ing'] => "❌ Це структура Present Continuous, а не Present Simple.",
                default => "❌ Невірна форма дієслова в Present Simple.",
            },
            'present_simple_statement' => match ($option) {
                $verb['third'] => "❌ Тут не потрібне -s, бо підмет не третя особа однини.",
                $be['statement'] . ' ' . $verb['ing'] => "❌ Це Present Continuous, а нам потрібен Present Simple.",
                $do['negative_contracted'] . ' ' . $verb['base'] => "❌ Це заперечна форма, а речення стверджувальне.",
                default => "❌ Неправильна форма для Present Simple.",
            },
            'present_simple_negative' => match ($option) {
                $be['negative_contracted'] . ' ' . $verb['ing'] => "❌ Це Present Continuous, а не Present Simple.",
                $be['statement'] . ' ' . $verb['ing'] => "❌ Ствердна форма Present Continuous, а нам потрібно заперечення Present Simple.",
                $do['question'] . ' ' . $verb['base'] => "❌ Це початок питання, а не заперечна форма.",
                default => "❌ Невдала форма заперечення в Present Simple.",
            },
            default => '❌ Невірний вибір.',
        };
    }

    private function correctExplanation(string $type, array $forms, string $example): string
    {
        return match ($type) {
            'present_continuous_statement' => "Present Continuous описує дію в момент мовлення. Формула: be + V-ing. Приклад: *{$example}*.",
            'present_continuous_negative' => "Present Continuous negative = be + not + V-ing. Приклад: *{$example}*.",
            'present_continuous_question_aux' => "Питання в Present Continuous починаємо з be. Приклад: *{$example}*.",
            'present_continuous_ing' => "Після be використовуємо форму V-ing. Приклад: *{$example}*.",
            'present_simple_question_aux' => "Present Simple питання будуємо з do/does. Приклад: *{$example}*.",
            'present_simple_base' => "Після do/does дієслово залишається в базовій формі. Приклад: *{$example}*.",
            'present_simple_statement' => "Present Simple передає звичні дії. Приклад: *{$example}*.",
            'present_simple_negative' => "Заперечення в Present Simple: do/does + not + V1. Приклад: *{$example}*.",
            default => "Приклад: *{$example}*.",
        };
    }

    private function ensureOptions(array $options, array $answers): array
    {
        $options = array_values(array_unique(array_map('trim', $options)));

        foreach ($answers as $answer) {
            if (! in_array($answer, $options, true)) {
                array_unshift($options, $answer);
            }
        }

        while (count($options) < 4 && ! empty($answers)) {
            $options[] = $answers[array_rand($answers)];
            $options = array_values(array_unique($options));
        }

        if (count($options) > 6) {
            $preferred = $answers;
            foreach ($options as $option) {
                if (! in_array($option, $preferred, true)) {
                    $preferred[] = $option;
                }
                if (count($preferred) >= 6) {
                    break;
                }
            }

            $options = array_slice($preferred, 0, 6);
        }

        return $options;
    }

    private function oppositeBe(string $role, string $current): string
    {
        $alternatives = [
            'am' => 'are',
            'are' => in_array($role, ['he_she_it'], true) ? 'is' : 'am',
            'is' => 'are',
        ];

        return $alternatives[$current] ?? 'be';
    }

    private function oppositeDo(string $role, string $current): string
    {
        if ($current === 'do') {
            return 'does';
        }

        return in_array($role, ['he_she_it'], true) ? 'do' : 'does';
    }
}
