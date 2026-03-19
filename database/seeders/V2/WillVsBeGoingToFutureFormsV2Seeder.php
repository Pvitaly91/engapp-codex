<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class WillVsBeGoingToFutureFormsV2Seeder extends QuestionSeeder
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
        'will_reaction' => "Форма: **will + V1**.\nВживаємо **will**, коли вирішуємо щось у момент мовлення або одразу пропонуємо допомогу.\nПриклад: *%example%*",
        'will_prediction' => "Форма: **will + V1**.\nВживаємо **will** для думки, припущення або нейтрального передбачення без прямого видимого доказу.\nПриклад: *%example%*",
        'will_invitation' => "Форма: **Will + subject + V1?**\nУ запрошеннях, проханнях і питаннях про готовність щось зробити часто вживаємо **will**.\nПриклад: *%example%*",
        'will_promise' => "Форма: **will + V1**.\nКороткі обіцянки, запевнення та готовність щось зробити зазвичай передаємо через **will**.\nПриклад: *%example%*",
        'will_warning' => "Форма: **will + V1**.\nУ коротких попередженнях про можливий наслідок у цій моделі вживаємо **will**.\nПриклад: *%example%*",
        'going_to_plan' => "Форма: **be going to + V1**.\nВживаємо **be going to**, коли план або намір уже існував до моменту мовлення.\nПриклад: *%example%*",
        'going_to_plan_question' => "Форма: **be + subject + going to + V1**.\nПитаємо **be going to**, коли хочемо дізнатися про чийсь план або намір.\nПриклад: *%example%*",
        'going_to_prediction' => "Форма: **be going to + V1**.\nВживаємо **be going to**, коли є теперішній знак або доказ майбутньої події.\nПриклад: *%example%*",
    ];

    private array $correctExplanationTemplates = [
        'will_reaction' => "✅ «%option%» правильно, бо це спонтанне рішення або пропозиція в момент мовлення. Тут уживаємо **will + V1**.\nПриклад: *%example%*",
        'will_prediction' => "✅ «%option%» правильно, бо це думка, припущення або нейтральне передбачення без прямого доказу. Тут уживаємо **will + V1**.\nПриклад: *%example%*",
        'will_invitation' => "✅ «%option%» правильно, бо це запрошення або питання про готовність щось зробити. У такому значенні природно вживаємо **Will you ...?**\nПриклад: *%example%*",
        'will_promise' => "✅ «%option%» правильно, бо це обіцянка або запевнення. У таких коротких відповідях уживаємо **will**.\nПриклад: *%example%*",
        'will_warning' => "✅ «%option%» правильно, бо це попередження про наслідок. У цій вправі для такого значення вживаємо **will**.\nПриклад: *%example%*",
        'going_to_plan' => "✅ «%option%» правильно, бо дія вже запланована або намір уже існує. Тут потрібне **be going to + V1**.\nПриклад: *%example%*",
        'going_to_plan_question' => "✅ «%option%» правильно, бо питання стосується плану або наміру. Для цього вживаємо **be going to**.\nПриклад: *%example%*",
        'going_to_prediction' => "✅ «%option%» правильно, бо є видимий доказ або теперішня ознака майбутньої події. Тут уживаємо **be going to**.\nПриклад: *%example%*",
    ];

    private array $reasonTemplates = [
        'need_will_reaction' => "❌ «%option%» не підходить, бо рішення приймається прямо зараз як реакція на ситуацію. У цій вправі для такого випадку потрібне **will**.\nПриклад: *%example%*",
        'need_will_prediction' => "❌ «%option%» не підходить, бо це думка або припущення без прямого доказу. У такій ситуації вживаємо **will**.\nПриклад: *%example%*",
        'need_will_invitation' => "❌ «%option%» змінює зміст на питання про план. У цій вправі потрібне запрошення або готовність, тому обираємо **Will you ...?**\nПриклад: *%example%*",
        'need_will_promise' => "❌ «%option%» не підходить, бо тут є обіцянка або запевнення. Для таких коротких відповідей уживаємо **will**.\nПриклад: *%example%*",
        'need_will_warning' => "❌ «%option%» не підходить, бо це попередження про можливий наслідок. У цій вправі для такого значення обираємо **will**.\nПриклад: *%example%*",
        'need_going_to_plan' => "❌ «%option%» не підходить, бо дія вже запланована або намір уже є. Тут потрібне **be going to**.\nПриклад: *%example%*",
        'need_going_to_plan_question' => "❌ «%option%» не підходить, бо питання стосується чийогось плану або наміру. Тут потрібна форма **be going to**.\nПриклад: *%example%*",
        'need_going_to_evidence' => "❌ «%option%» не підходить, бо є теперішній знак або доказ майбутньої події. У такому випадку вживаємо **be going to**.\nПриклад: *%example%*",
        'missing_be' => "❌ «%option%» неправильно, бо в конструкції **be going to** обов'язково потрібна форма дієслова **be**: *am / is / are*.\nПриклад: *%example%*",
        'missing_base_form_after_will' => "❌ «%option%» неправильно, бо після **will** вживаємо базову форму дієслова без **to**.\nПриклад: *%example%*",
        'wrong_tense_form' => "❌ «%option%» не відповідає потрібній майбутній формі в цьому реченні.\nПриклад: *%example%*",
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Future'])->id;

        $sourceIds = [
            'page1' => Source::firstOrCreate(['name' => 'Custom: Will vs Be Going To (A2) - Page 1'])->id,
            'page2' => Source::firstOrCreate(['name' => 'Custom: Will vs Be Going To (A2) - Page 2'])->id,
            'page3' => Source::firstOrCreate(['name' => 'Custom: Will vs Be Going To (A2) - Page 3'])->id,
        ];

        $tagMap = [
            'theme' => Tag::firstOrCreate(['name' => 'Future Forms Practice'], ['category' => 'English Grammar Theme'])->id,
            'detail' => Tag::firstOrCreate(['name' => 'Will vs Be Going To'], ['category' => 'English Grammar Detail'])->id,
            'structure' => Tag::firstOrCreate(['name' => 'Future Form Choice'], ['category' => 'English Grammar Structure'])->id,
            'plans' => Tag::firstOrCreate(['name' => 'Plans and Intentions'], ['category' => 'English Grammar Focus'])->id,
            'prediction' => Tag::firstOrCreate(['name' => 'Predictions and Evidence'], ['category' => 'English Grammar Focus'])->id,
            'spontaneous' => Tag::firstOrCreate(['name' => 'Spontaneous Decisions and Offers'], ['category' => 'English Grammar Focus'])->id,
            'future_simple' => Tag::firstOrCreate(['name' => 'Future Simple'], ['category' => 'Tenses'])->id,
            'be_going_to' => Tag::firstOrCreate(['name' => 'Be Going To'], ['category' => 'Tenses'])->id,
        ];

        $entries = $this->questionEntries();
        $items = [];
        $meta = [];

        foreach ($entries as $entry) {
            $answersMap = [];
            foreach ($entry['markers'] as $marker => $markerData) {
                $answersMap[$marker] = $markerData['answer'];
            }

            $example = $this->formatExample($entry['question'], $answersMap);
            $answers = [];
            $optionsByMarker = [];
            $hints = [];
            $explanations = [];
            $markerTypes = [];

            foreach ($entry['markers'] as $marker => $markerData) {
                $answers[] = [
                    'marker' => $marker,
                    'answer' => $markerData['answer'],
                    'verb_hint' => $this->normalizeHint($markerData['verb_hint'] ?? null),
                ];

                $markerTypes[] = $markerData['type'];
                $hints[$marker] = $this->buildHint($markerData['type'], $example);
                $optionsByMarker[$marker] = [];

                foreach ($markerData['options'] as $optionData) {
                    $value = $optionData['value'];
                    $optionsByMarker[$marker][] = $value;
                    $explanations[$marker][$value] = $this->buildExplanation(
                        $markerData['type'],
                        $optionData['reason'],
                        $value,
                        $example
                    );
                }
            }

            $uuid = $this->generateQuestionUuid($entry['source'], $entry['id'], $entry['question']);

            $items[] = [
                'uuid' => $uuid,
                'question' => $entry['question'],
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$entry['level']] ?? 2,
                'source_id' => $sourceIds[$entry['source']] ?? reset($sourceIds),
                'flag' => 0,
                'type' => 0,
                'level' => $entry['level'],
                'tag_ids' => $this->buildTagIds($markerTypes, $tagMap),
                'answers' => $answers,
                'options_by_marker' => $optionsByMarker,
                'options' => $this->flattenOptions($optionsByMarker),
                'variants' => [$entry['variant'] ?? $entry['question']],
            ];

            $meta[] = [
                'uuid' => $uuid,
                'answers' => $answersMap,
                'hints' => $hints,
                'explanations' => $explanations,
            ];
        }

        $this->seedQuestionData($items, []);
        $this->attachHintsAndExplanations($meta);
    }

    private function attachHintsAndExplanations(array $meta): void
    {
        foreach ($meta as $data) {
            $question = Question::where('uuid', $data['uuid'])->first();

            if (! $question) {
                continue;
            }

            $hintText = $this->formatHints($data['hints'] ?? []);
            if ($hintText !== null) {
                QuestionHint::updateOrCreate(
                    ['question_id' => $question->id, 'provider' => 'chatgpt', 'locale' => 'uk'],
                    ['hint' => $hintText]
                );
            }

            foreach ($data['explanations'] ?? [] as $marker => $markerExplanations) {
                $correct = $data['answers'][$marker] ?? reset($data['answers']);
                $correct = is_string($correct) ? $correct : (string) $correct;

                foreach ($markerExplanations as $option => $text) {
                    ChatGPTExplanation::updateOrCreate(
                        [
                            'question' => $question->question,
                            'wrong_answer' => $option,
                            'correct_answer' => $correct,
                            'language' => 'ua',
                        ],
                        ['explanation' => $text]
                    );
                }
            }
        }
    }

    private function buildTagIds(array $markerTypes, array $tagMap): array
    {
        $tagIds = [$tagMap['theme'], $tagMap['detail'], $tagMap['structure']];

        foreach ($markerTypes as $type) {
            foreach ($this->tagsForType($type, $tagMap) as $tagId) {
                $tagIds[] = $tagId;
            }
        }

        return array_values(array_unique($tagIds));
    }

    private function tagsForType(string $type, array $tagMap): array
    {
        return match ($type) {
            'will_reaction', 'will_invitation', 'will_promise' => [$tagMap['future_simple'], $tagMap['spontaneous']],
            'will_prediction', 'will_warning' => [$tagMap['future_simple'], $tagMap['prediction']],
            'going_to_plan', 'going_to_plan_question' => [$tagMap['be_going_to'], $tagMap['plans']],
            'going_to_prediction' => [$tagMap['be_going_to'], $tagMap['prediction']],
            default => [],
        };
    }

    private function flattenOptions(array $optionsByMarker): array
    {
        $flat = [];

        foreach ($optionsByMarker as $markerOptions) {
            foreach ($markerOptions as $option) {
                $value = trim((string) $option);
                if ($value === '' || in_array($value, $flat, true)) {
                    continue;
                }

                $flat[] = $value;
            }
        }

        return $flat;
    }

    private function buildHint(string $type, string $example): string
    {
        $template = $this->hintTemplates[$type] ?? "Оберіть форму майбутнього часу, яка відповідає правилу.\nПриклад: *%example%*";

        return trim(strtr($template, ['%example%' => $example]));
    }

    private function buildExplanation(string $type, string $reason, string $option, string $example): string
    {
        if ($reason === 'correct') {
            $template = $this->correctExplanationTemplates[$type] ?? "✅ «%option%» правильно.\nПриклад: *%example%*";
        } else {
            $template = $this->reasonTemplates[$reason] ?? "❌ «%option%» не підходить.\nПриклад: *%example%*";
        }

        return trim(strtr($template, [
            '%option%' => $option,
            '%example%' => $example,
        ]));
    }

    private function questionEntries(): array
    {
        return [
            // Page 1
            [
                'id' => 1,
                'source' => 'page1',
                'level' => 'A2',
                'question' => "A: We're out of milk.\nB: Don't worry, I {a1} buy some on the way home.",
                'variant' => "A: We're out of milk.\nB: Don't worry, I _____ buy some on the way home.",
                'markers' => [
                    'a1' => [
                        'type' => 'will_reaction',
                        'verb_hint' => 'buy',
                        'answer' => "'ll",
                        'options' => [
                            ['value' => "'ll", 'reason' => 'correct'],
                            ['value' => "'m going to", 'reason' => 'need_will_reaction'],
                            ['value' => 'will to', 'reason' => 'missing_base_form_after_will'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 2,
                'source' => 'page1',
                'level' => 'A2',
                'question' => "A: Why are you wearing sports clothes?\nB: I {a1} play tennis after work.",
                'variant' => "A: Why are you wearing sports clothes?\nB: I _____ play tennis after work.",
                'markers' => [
                    'a1' => [
                        'type' => 'going_to_plan',
                        'verb_hint' => 'play',
                        'answer' => "'m going to",
                        'options' => [
                            ['value' => 'going to', 'reason' => 'missing_be'],
                            ['value' => "'m going to", 'reason' => 'correct'],
                            ['value' => "'ll", 'reason' => 'need_going_to_plan'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 3,
                'source' => 'page1',
                'level' => 'A2',
                'question' => 'Look at those clouds! It {a1} rain.',
                'variant' => 'Look at those clouds! It _____ rain.',
                'markers' => [
                    'a1' => [
                        'type' => 'going_to_prediction',
                        'verb_hint' => 'rain',
                        'answer' => "'s going to",
                        'options' => [
                            ['value' => "'ll", 'reason' => 'need_going_to_evidence'],
                            ['value' => "'s going to", 'reason' => 'correct'],
                            ['value' => 'will to', 'reason' => 'missing_base_form_after_will'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 4,
                'source' => 'page1',
                'level' => 'A2',
                'question' => "A: I can't open this jar.\nB: I {a1} help you.",
                'variant' => "A: I can't open this jar.\nB: I _____ help you.",
                'markers' => [
                    'a1' => [
                        'type' => 'will_reaction',
                        'verb_hint' => 'help',
                        'answer' => "'ll",
                        'options' => [
                            ['value' => "'ll", 'reason' => 'correct'],
                            ['value' => "'m going to", 'reason' => 'need_will_reaction'],
                            ['value' => 'will to', 'reason' => 'missing_base_form_after_will'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 5,
                'source' => 'page1',
                'level' => 'A2',
                'question' => "A: Have you booked your holiday?\nB: Yes, we {a1} visit Lisbon in July.",
                'variant' => "A: Have you booked your holiday?\nB: Yes, we _____ visit Lisbon in July.",
                'markers' => [
                    'a1' => [
                        'type' => 'going_to_plan',
                        'verb_hint' => 'visit',
                        'answer' => "'re going to",
                        'options' => [
                            ['value' => "'ll", 'reason' => 'need_going_to_plan'],
                            ['value' => "'re going to", 'reason' => 'correct'],
                            ['value' => 'going to', 'reason' => 'missing_be'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 6,
                'source' => 'page1',
                'level' => 'A2',
                'question' => "A: My phone battery is 1%.\nB: I {a1} lend you my charger.",
                'variant' => "A: My phone battery is 1%.\nB: I _____ lend you my charger.",
                'markers' => [
                    'a1' => [
                        'type' => 'will_reaction',
                        'verb_hint' => 'lend',
                        'answer' => "'ll",
                        'options' => [
                            ['value' => "'ll", 'reason' => 'correct'],
                            ['value' => "'m going to", 'reason' => 'need_will_reaction'],
                            ['value' => 'will to', 'reason' => 'missing_base_form_after_will'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 7,
                'source' => 'page1',
                'level' => 'A2',
                'question' => "A: Why is the car packed?\nB: We {a1} move house this weekend.",
                'variant' => "A: Why is the car packed?\nB: We _____ move house this weekend.",
                'markers' => [
                    'a1' => [
                        'type' => 'going_to_plan',
                        'verb_hint' => 'move',
                        'answer' => "'re going to",
                        'options' => [
                            ['value' => "'re going to", 'reason' => 'correct'],
                            ['value' => "'ll", 'reason' => 'need_going_to_plan'],
                            ['value' => 'going to', 'reason' => 'missing_be'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 8,
                'source' => 'page1',
                'level' => 'A2',
                'question' => "A: The doorbell is ringing.\nB: I {a1} answer it.",
                'variant' => "A: The doorbell is ringing.\nB: I _____ answer it.",
                'markers' => [
                    'a1' => [
                        'type' => 'will_reaction',
                        'verb_hint' => 'answer',
                        'answer' => "'ll",
                        'options' => [
                            ['value' => "'m going to", 'reason' => 'need_will_reaction'],
                            ['value' => "'ll", 'reason' => 'correct'],
                            ['value' => 'will to', 'reason' => 'missing_base_form_after_will'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 9,
                'source' => 'page1',
                'level' => 'A2',
                'question' => 'This chart shows that prices {a1} rise next year.',
                'variant' => 'This chart shows that prices _____ rise next year.',
                'markers' => [
                    'a1' => [
                        'type' => 'going_to_prediction',
                        'verb_hint' => 'rise',
                        'answer' => "'re going to",
                        'options' => [
                            ['value' => "'ll", 'reason' => 'need_going_to_evidence'],
                            ['value' => "'re going to", 'reason' => 'correct'],
                            ['value' => 'will to', 'reason' => 'missing_base_form_after_will'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 10,
                'source' => 'page1',
                'level' => 'A2',
                'question' => "A: I'm thirsty.\nB: I {a1} get you some water.",
                'variant' => "A: I'm thirsty.\nB: I _____ get you some water.",
                'markers' => [
                    'a1' => [
                        'type' => 'will_reaction',
                        'verb_hint' => 'get',
                        'answer' => "'ll",
                        'options' => [
                            ['value' => "'ll", 'reason' => 'correct'],
                            ['value' => "'m going to", 'reason' => 'need_will_reaction'],
                            ['value' => 'going to', 'reason' => 'missing_be'],
                        ],
                    ],
                ],
            ],

            // Page 2
            [
                'id' => 11,
                'source' => 'page2',
                'level' => 'A2',
                'question' => "A: I don't think she {a1} come.\nB: She told me she {a2} come.",
                'variant' => "A: I don't think she _____ come.\nB: She told me she _____ come.",
                'markers' => [
                    'a1' => [
                        'type' => 'will_prediction',
                        'verb_hint' => 'come',
                        'answer' => 'will',
                        'options' => [
                            ['value' => 'will', 'reason' => 'correct'],
                            ['value' => 'is going to', 'reason' => 'need_will_prediction'],
                            ['value' => 'comes', 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'going_to_plan',
                        'verb_hint' => 'come',
                        'answer' => 'is going to',
                        'options' => [
                            ['value' => 'will', 'reason' => 'need_going_to_plan'],
                            ['value' => 'is going to', 'reason' => 'correct'],
                            ['value' => 'comes', 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 12,
                'source' => 'page2',
                'level' => 'A2',
                'question' => "A: I bought ingredients because I {a1} cook dinner.\nB: Great! I'm sure it {a2} delicious.",
                'variant' => "A: I bought ingredients because I _____ cook dinner.\nB: Great! I'm sure it _____ delicious.",
                'markers' => [
                    'a1' => [
                        'type' => 'going_to_plan',
                        'verb_hint' => 'cook',
                        'answer' => "'m going to",
                        'options' => [
                            ['value' => "'m going to", 'reason' => 'correct'],
                            ['value' => 'will', 'reason' => 'need_going_to_plan'],
                            ['value' => 'cook', 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'will_prediction',
                        'verb_hint' => 'be',
                        'answer' => 'will be',
                        'options' => [
                            ['value' => 'will be', 'reason' => 'correct'],
                            ['value' => 'is going to be', 'reason' => 'need_will_prediction'],
                            ['value' => 'is', 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 13,
                'source' => 'page2',
                'level' => 'A2',
                'question' => "A: What {a1} do after the lesson?\nB: I {a2} meet my friend.",
                'variant' => "A: What _____ do after the lesson?\nB: I _____ meet my friend.",
                'markers' => [
                    'a1' => [
                        'type' => 'going_to_plan_question',
                        'verb_hint' => 'do',
                        'answer' => 'are you going to',
                        'options' => [
                            ['value' => 'will you', 'reason' => 'need_going_to_plan_question'],
                            ['value' => 'are you going to', 'reason' => 'correct'],
                            ['value' => 'do you', 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'going_to_plan',
                        'verb_hint' => 'meet',
                        'answer' => "'m going to",
                        'options' => [
                            ['value' => "'m going to", 'reason' => 'correct'],
                            ['value' => 'will', 'reason' => 'need_going_to_plan'],
                            ['value' => 'meet', 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 14,
                'source' => 'page2',
                'level' => 'A2',
                'question' => "A: {a1} join us tonight?\nB: Sorry, I {a2} stay in.",
                'variant' => "A: _____ join us tonight?\nB: Sorry, I _____ stay in.",
                'markers' => [
                    'a1' => [
                        'type' => 'will_invitation',
                        'verb_hint' => 'join',
                        'answer' => 'Will you',
                        'options' => [
                            ['value' => 'Will you', 'reason' => 'correct'],
                            ['value' => 'Are you going to', 'reason' => 'need_will_invitation'],
                            ['value' => 'Do you', 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'going_to_plan',
                        'verb_hint' => 'stay',
                        'answer' => "'m going to",
                        'options' => [
                            ['value' => "'m going to", 'reason' => 'correct'],
                            ['value' => 'will', 'reason' => 'need_going_to_plan'],
                            ['value' => 'stay', 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 15,
                'source' => 'page2',
                'level' => 'A2',
                'question' => "A: I need a pen because I {a1} sign this form.\nB: Here, I {a2} lend you one.",
                'variant' => "A: I need a pen because I _____ sign this form.\nB: Here, I _____ lend you one.",
                'markers' => [
                    'a1' => [
                        'type' => 'going_to_plan',
                        'verb_hint' => 'sign',
                        'answer' => "'m going to",
                        'options' => [
                            ['value' => "'m going to", 'reason' => 'correct'],
                            ['value' => "'ll", 'reason' => 'need_going_to_plan'],
                            ['value' => 'sign', 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'will_reaction',
                        'verb_hint' => 'lend',
                        'answer' => "'ll",
                        'options' => [
                            ['value' => "'ll", 'reason' => 'correct'],
                            ['value' => "'m going to", 'reason' => 'need_will_reaction'],
                            ['value' => 'lend', 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 16,
                'source' => 'page2',
                'level' => 'A2',
                'question' => "A: {a1} drive to work today?\nB: No, I {a2} take the metro.",
                'variant' => "A: _____ drive to work today?\nB: No, I _____ take the metro.",
                'markers' => [
                    'a1' => [
                        'type' => 'going_to_plan_question',
                        'verb_hint' => 'drive',
                        'answer' => 'Are you going to',
                        'options' => [
                            ['value' => 'Will you', 'reason' => 'need_going_to_plan_question'],
                            ['value' => 'Are you going to', 'reason' => 'correct'],
                            ['value' => 'Do you', 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'going_to_plan',
                        'verb_hint' => 'take',
                        'answer' => "'m going to",
                        'options' => [
                            ['value' => "'m going to", 'reason' => 'correct'],
                            ['value' => 'will', 'reason' => 'need_going_to_plan'],
                            ['value' => 'take', 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 17,
                'source' => 'page2',
                'level' => 'A2',
                'question' => "A: Hurry! You {a1} miss the start.\nB: Relax, I {a2} call a taxi.",
                'variant' => "A: Hurry! You _____ miss the start.\nB: Relax, I _____ call a taxi.",
                'markers' => [
                    'a1' => [
                        'type' => 'going_to_prediction',
                        'verb_hint' => 'miss',
                        'answer' => 'are going to',
                        'options' => [
                            ['value' => 'are going to', 'reason' => 'correct'],
                            ['value' => 'will', 'reason' => 'need_going_to_evidence'],
                            ['value' => 'are', 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'will_reaction',
                        'verb_hint' => 'call',
                        'answer' => 'will',
                        'options' => [
                            ['value' => 'will', 'reason' => 'correct'],
                            ['value' => 'are going to', 'reason' => 'need_will_reaction'],
                            ['value' => 'call', 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 18,
                'source' => 'page2',
                'level' => 'A2',
                'question' => "A: According to these polls, he {a1} win.\nB: What {a2} do first?",
                'variant' => "A: According to these polls, he _____ win.\nB: What _____ do first?",
                'markers' => [
                    'a1' => [
                        'type' => 'going_to_prediction',
                        'verb_hint' => 'win',
                        'answer' => 'is going to',
                        'options' => [
                            ['value' => 'will', 'reason' => 'need_going_to_evidence'],
                            ['value' => 'is going to', 'reason' => 'correct'],
                            ['value' => 'wins', 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'going_to_plan_question',
                        'verb_hint' => 'do',
                        'answer' => 'are you going to',
                        'options' => [
                            ['value' => 'will you', 'reason' => 'need_going_to_plan_question'],
                            ['value' => 'are you going to', 'reason' => 'correct'],
                            ['value' => 'do you', 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 19,
                'source' => 'page2',
                'level' => 'A2',
                'question' => "A: They're leading with 10 seconds left. They {a1} win.\nB: Maybe, but I think the other team {a2} score again.",
                'variant' => "A: They're leading with 10 seconds left. They _____ win.\nB: Maybe, but I think the other team _____ score again.",
                'markers' => [
                    'a1' => [
                        'type' => 'going_to_prediction',
                        'verb_hint' => 'win',
                        'answer' => 'are going to',
                        'options' => [
                            ['value' => 'are going to', 'reason' => 'correct'],
                            ['value' => 'will', 'reason' => 'need_going_to_evidence'],
                            ['value' => 'win', 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'will_prediction',
                        'verb_hint' => 'score',
                        'answer' => 'will',
                        'options' => [
                            ['value' => 'will', 'reason' => 'correct'],
                            ['value' => 'are going to', 'reason' => 'need_will_prediction'],
                            ['value' => 'scores', 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 20,
                'source' => 'page2',
                'level' => 'A2',
                'question' => "A: Be quiet! You {a1} wake the baby.\nB: Sorry, I {a2} be careful.",
                'variant' => "A: Be quiet! You _____ wake the baby.\nB: Sorry, I _____ be careful.",
                'markers' => [
                    'a1' => [
                        'type' => 'will_warning',
                        'verb_hint' => 'wake',
                        'answer' => 'will',
                        'options' => [
                            ['value' => 'will', 'reason' => 'correct'],
                            ['value' => 'are going to', 'reason' => 'need_will_warning'],
                            ['value' => 'wake', 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                    'a2' => [
                        'type' => 'will_promise',
                        'verb_hint' => 'be',
                        'answer' => 'will',
                        'options' => [
                            ['value' => 'will', 'reason' => 'correct'],
                            ['value' => "'m going to", 'reason' => 'need_will_promise'],
                            ['value' => 'am', 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                ],
            ],

            // Page 3
            [
                'id' => 21,
                'source' => 'page3',
                'level' => 'A2',
                'question' => 'Have you decided? Yes, {a1} a new laptop.',
                'variant' => 'Have you decided? Yes, I (buy) a new laptop.',
                'markers' => [
                    'a1' => [
                        'type' => 'going_to_plan',
                        'verb_hint' => 'buy',
                        'answer' => "I'm going to buy",
                        'options' => [
                            ['value' => "I'm going to buy", 'reason' => 'correct'],
                            ['value' => "I'll buy", 'reason' => 'need_going_to_plan'],
                            ['value' => 'I buy', 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 22,
                'source' => 'page3',
                'level' => 'A2',
                'question' => 'This suitcase is heavy. {a1} it for you.',
                'variant' => 'This suitcase is heavy. I (carry) it for you.',
                'markers' => [
                    'a1' => [
                        'type' => 'will_reaction',
                        'verb_hint' => 'carry',
                        'answer' => "I'll carry",
                        'options' => [
                            ['value' => "I'll carry", 'reason' => 'correct'],
                            ['value' => "I'm going to carry", 'reason' => 'need_will_reaction'],
                            ['value' => 'I carry', 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 23,
                'source' => 'page3',
                'level' => 'A2',
                'question' => "He's driving too fast! {a1} an accident!",
                'variant' => "He's driving too fast! He (have) an accident!",
                'markers' => [
                    'a1' => [
                        'type' => 'going_to_prediction',
                        'verb_hint' => 'have',
                        'answer' => "He's going to have",
                        'options' => [
                            ['value' => "He's going to have", 'reason' => 'correct'],
                            ['value' => "He'll have", 'reason' => 'need_going_to_evidence'],
                            ['value' => 'He has', 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 24,
                'source' => 'page3',
                'level' => 'A2',
                'question' => 'I think {a1} this movie.',
                'variant' => 'I think you (love) this movie.',
                'markers' => [
                    'a1' => [
                        'type' => 'will_prediction',
                        'verb_hint' => 'love',
                        'answer' => "You'll love",
                        'options' => [
                            ['value' => "You'll love", 'reason' => 'correct'],
                            ['value' => "You're going to love", 'reason' => 'need_will_prediction'],
                            ['value' => 'You love', 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 25,
                'source' => 'page3',
                'level' => 'A2',
                'question' => "{a1} out tonight; we're exhausted.",
                'variant' => "We (not/go) out tonight; we're exhausted.",
                'markers' => [
                    'a1' => [
                        'type' => 'going_to_plan',
                        'verb_hint' => 'not go',
                        'answer' => "We aren't going to go",
                        'options' => [
                            ['value' => "We aren't going to go", 'reason' => 'correct'],
                            ['value' => "We won't go", 'reason' => 'need_going_to_plan'],
                            ['value' => "We don't go", 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 26,
                'source' => 'page3',
                'level' => 'A2',
                'question' => '{a1} on time?',
                'variant' => 'Do you think they (finish) on time?',
                'markers' => [
                    'a1' => [
                        'type' => 'will_prediction',
                        'verb_hint' => 'finish',
                        'answer' => 'Will they finish',
                        'options' => [
                            ['value' => 'Will they finish', 'reason' => 'correct'],
                            ['value' => 'Are they going to finish', 'reason' => 'need_will_prediction'],
                            ['value' => 'They finish', 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 27,
                'source' => 'page3',
                'level' => 'A2',
                'question' => 'Look! {a1}!',
                'variant' => 'Look! The glass (fall)!',
                'markers' => [
                    'a1' => [
                        'type' => 'going_to_prediction',
                        'verb_hint' => 'fall',
                        'answer' => "It's going to fall",
                        'options' => [
                            ['value' => "It's going to fall", 'reason' => 'correct'],
                            ['value' => "It'll fall", 'reason' => 'need_going_to_evidence'],
                            ['value' => 'It falls', 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 28,
                'source' => 'page3',
                'level' => 'A2',
                'question' => "A: I don't have cash.\nB: Don't worry, {a1} for your ticket.",
                'variant' => "A: I don't have cash.\nB: Don't worry, I (pay) for your ticket.",
                'markers' => [
                    'a1' => [
                        'type' => 'will_reaction',
                        'verb_hint' => 'pay',
                        'answer' => "I'll pay",
                        'options' => [
                            ['value' => "I'll pay", 'reason' => 'correct'],
                            ['value' => "I'm going to pay", 'reason' => 'need_will_reaction'],
                            ['value' => 'I pay', 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 29,
                'source' => 'page3',
                'level' => 'A2',
                'question' => 'Next year {a1} at a different university.',
                'variant' => 'Next year I (study) at a different university.',
                'markers' => [
                    'a1' => [
                        'type' => 'going_to_plan',
                        'verb_hint' => 'study',
                        'answer' => "I'm going to study",
                        'options' => [
                            ['value' => "I'm going to study", 'reason' => 'correct'],
                            ['value' => "I'll study", 'reason' => 'need_going_to_plan'],
                            ['value' => 'I study', 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 30,
                'source' => 'page3',
                'level' => 'A2',
                'question' => "A: My printer doesn't work.\nB: OK, {a1} a look at it.",
                'variant' => "A: My printer doesn't work.\nB: OK, I (take) a look at it.",
                'markers' => [
                    'a1' => [
                        'type' => 'will_reaction',
                        'verb_hint' => 'take',
                        'answer' => "I'll take",
                        'options' => [
                            ['value' => "I'll take", 'reason' => 'correct'],
                            ['value' => "I'm going to take", 'reason' => 'need_will_reaction'],
                            ['value' => 'I take', 'reason' => 'wrong_tense_form'],
                        ],
                    ],
                ],
            ],
            ];
    }
}
