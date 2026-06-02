<?php

namespace Database\Seeders\Ai;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class ConditionalsModalComprehensiveAiSeeder extends QuestionSeeder
{
    private array $levelDifficulty = [
        'A1' => 1,
        'A2' => 2,
        'B1' => 3,
        'B2' => 4,
        'C1' => 5,
        'C2' => 5,
    ];

    private array $sourceConfig = [
        'past_question' => [
            'type' => 'question',
            'tense_group' => 'past',
            'tense_labels' => ['Third Conditional'],
        ],
        'past_negative' => [
            'type' => 'negative',
            'tense_group' => 'past',
            'tense_labels' => ['Third Conditional'],
        ],
        'present_question' => [
            'type' => 'question',
            'tense_group' => 'present',
            'tense_labels' => ['Second Conditional'],
        ],
        'present_negative' => [
            'type' => 'negative',
            'tense_group' => 'present',
            'tense_labels' => ['Second Conditional'],
        ],
        'future_question' => [
            'type' => 'question',
            'tense_group' => 'future',
            'tense_labels' => ['First Conditional'],
        ],
        'future_negative' => [
            'type' => 'negative',
            'tense_group' => 'future',
            'tense_labels' => ['First Conditional'],
        ],
    ];

    private array $modalTagConfig = [
        'will_would' => [
            'name' => 'Modal: Will / Would',
            'keywords' => [
                'will',
                "won't",
                'will have to',
                "won't have to",
                'would',
                "wouldn't",
                'would have',
                "wouldn't have",
                'would have to',
                "wouldn't have to",
            ],
        ],
        'can_could' => [
            'name' => 'Modal: Can / Could',
            'keywords' => [
                'can',
                "can't",
                'could',
                "couldn't",
                'could have',
                "couldn't have",
            ],
        ],
        'may_might' => [
            'name' => 'Modal: May / Might',
            'keywords' => [
                'may',
                'may not',
                'may have',
                'may not have',
                'might',
                'might not',
                'might have',
                'might not have',
            ],
        ],
        'must_have_to' => [
            'name' => 'Modal: Must / Have to',
            'keywords' => [
                'must',
                "mustn't",
                'have to',
                'has to',
                'had to',
                "don't have to",
                "doesn't have to",
                "didn't have to",
                'will have to',
                "won't have to",
                'would have to',
                "wouldn't have to",
            ],
        ],
        'should_ought_to' => [
            'name' => 'Modal: Should / Ought to',
            'keywords' => [
                'should',
                "shouldn't",
                'should have',
                "shouldn't have",
                'ought to',
                'ought not to',
                'ought to have',
                'ought not to have',
            ],
        ],
        'need_need_to' => [
            'name' => 'Modal: Need / Need to',
            'keywords' => [
                'need',
                'need to',
                'needs to',
                'needed to',
                "needn't",
                'need not',
                "needn't have",
            ],
        ],
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Conditionals'])->id;

        $sourceMap = [
            'past_question' => Source::firstOrCreate(['name' => 'AI Conditional Modals: Past Questions'])->id,
            'past_negative' => Source::firstOrCreate(['name' => 'AI Conditional Modals: Past Negatives'])->id,
            'present_question' => Source::firstOrCreate(['name' => 'AI Conditional Modals: Present Questions'])->id,
            'present_negative' => Source::firstOrCreate(['name' => 'AI Conditional Modals: Present Negatives'])->id,
            'future_question' => Source::firstOrCreate(['name' => 'AI Conditional Modals: Future Questions'])->id,
            'future_negative' => Source::firstOrCreate(['name' => 'AI Conditional Modals: Future Negatives'])->id,
        ];

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'AI Conditional Modal Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Conditional Modal Completion'],
            ['category' => 'English Grammar Detail']
        )->id;

        $typeTagIds = [
            'question' => Tag::firstOrCreate(['name' => 'Conditional Modal Question Form'], ['category' => 'English Grammar Structure'])->id,
            'negative' => Tag::firstOrCreate(['name' => 'Conditional Modal Negative Form'], ['category' => 'English Grammar Structure'])->id,
        ];

        $tenseTagIds = [
            'First Conditional' => Tag::firstOrCreate(['name' => 'First Conditional'], ['category' => 'Conditional'])->id,
            'Second Conditional' => Tag::firstOrCreate(['name' => 'Second Conditional'], ['category' => 'Conditional'])->id,
            'Third Conditional' => Tag::firstOrCreate(['name' => 'Third Conditional'], ['category' => 'Conditional'])->id,
        ];

        $modalTagIds = [];
        foreach ($this->modalTagConfig as $key => $config) {
            $modalTagIds[$key] = Tag::firstOrCreate(
                ['name' => $config['name']],
                ['category' => 'Modal Verb']
            )->id;
        }

        $questions = $this->buildQuestionBank();

        $items = [];
        $meta = [];

        foreach ($questions as $index => $question) {
            $answers = [];
            $answersMap = [];
            $verbHints = [];
            $optionsPerMarker = [];
            $optionMarkerMap = [];
            $questionModalTagIds = [];

            foreach ($question['markers'] as $marker => $markerData) {
                $answer = (string) ($markerData['answer'] ?? '');
                $answersMap[$marker] = $answer;
                $verbHints[$marker] = $this->normalizeHint($markerData['verb_hint'] ?? null);

                $answers[] = [
                    'marker' => $marker,
                    'answer' => $answer,
                    'verb_hint' => $verbHints[$marker],
                ];

                $options = array_values(array_unique(array_map('strval', $markerData['options'] ?? [])));
                $optionsPerMarker[$marker] = $options;

                foreach ($options as $option) {
                    $optionMarkerMap[$option] = $marker;
                }

                $modalKeys = $this->determineModalTagKeys($answer, $markerData['modal_key'] ?? null);
                foreach ($modalKeys as $modalKey) {
                    if (isset($modalTagIds[$modalKey])) {
                        $questionModalTagIds[] = $modalTagIds[$modalKey];
                    }
                }
            }

            $optionBuckets = array_values($optionsPerMarker);
            $flattenedOptions = $optionBuckets !== []
                ? array_values(array_unique(array_merge(...$optionBuckets)))
                : [];

            $tagIds = [$themeTagId, $detailTagId];
            $config = $this->sourceConfig[$question['source_key']];
            $tagIds[] = $typeTagIds[$config['type']];
            foreach ($config['tense_labels'] as $tenseName) {
                if (isset($tenseTagIds[$tenseName])) {
                    $tagIds[] = $tenseTagIds[$tenseName];
                }
            }

            if ($questionModalTagIds !== []) {
                $tagIds = array_merge($tagIds, $questionModalTagIds);
            }

            $uuid = $this->generateQuestionUuid($question['level'], $question['source_key'], $index + 1);

            $items[] = [
                'uuid' => $uuid,
                'question' => $question['question'],
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$question['level']] ?? 3,
                'source_id' => $sourceMap[$question['source_key']],
                'flag' => 2,
                'level' => $question['level'],
                'tag_ids' => array_values(array_unique($tagIds)),
                'answers' => $answers,
                'options' => $flattenedOptions,
                'variants' => [$question['question']],
            ];

            $meta[] = [
                'uuid' => $uuid,
                'answers' => $answersMap,
                'option_markers' => $optionMarkerMap,
                'hints' => $verbHints,
                'explanations' => [],
            ];
        }

        $this->seedQuestionData($items, $meta);
    }

    private function buildQuestionBank(): array
    {
        $levels = $this->getLevelData();
        $questions = [];

        foreach ($levels as $level => $groups) {
            foreach ($groups as $sourceKey => $entries) {
                foreach ($entries as $entry) {
                    $questions[] = array_merge($entry, [
                        'level' => $level,
                        'source_key' => $sourceKey,
                    ]);
                }
            }
        }

        return $questions;
    }

    private function getLevelData(): array
    {
        return [
            'A1' => [
                'future_question' => [
                    [
                        'question' => 'If it rains tomorrow, {a1} you take an umbrella?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'will',
                                'options' => ['will', 'might', 'should'],
                                'verb_hint' => 'certainty',
                                'modal_key' => 'will_would',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the lights go out, {a1} we use candles?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'should',
                                'options' => ['should', 'might', 'can'],
                                'verb_hint' => 'advice',
                                'modal_key' => 'should_ought_to',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If we miss the bus, {a1} we call a taxi?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'can',
                                'options' => ['can', 'might', 'will'],
                                'verb_hint' => 'ability',
                                'modal_key' => 'can_could',
                            ],
                        ],
                    ],
                ],
                'future_negative' => [
                    [
                        'question' => 'If it stays warm, we {a1} need coats.',
                        'markers' => [
                            'a1' => [
                                'answer' => "won't",
                                'options' => ["won't", "can't", "shouldn't"],
                                'verb_hint' => 'definite negative',
                                'modal_key' => 'will_would',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the cafe is closed, you {a1} wait outside.',
                        'markers' => [
                            'a1' => [
                                'answer' => "can't",
                                'options' => ["can't", "won't", "mustn't"],
                                'verb_hint' => 'inability',
                                'modal_key' => 'can_could',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the soup is too hot, you {a1} eat it yet.',
                        'markers' => [
                            'a1' => [
                                'answer' => "shouldn't",
                                'options' => ["shouldn't", "won't", "can't"],
                                'verb_hint' => 'discouraged action',
                                'modal_key' => 'should_ought_to',
                            ],
                        ],
                    ],
                ],
                'present_question' => [
                    [
                        'question' => 'If you had a free ticket, {a1} you go to the concert?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'would',
                                'options' => ['would', 'might', 'could'],
                                'verb_hint' => 'hypothetical decision',
                                'modal_key' => 'will_would',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If she knew the answer, {a1} she tell us?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'would',
                                'options' => ['would', 'might', 'could'],
                                'verb_hint' => 'hypothetical decision',
                                'modal_key' => 'will_would',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If I were taller, {a1} I reach the shelf?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'could',
                                'options' => ['could', 'would', 'might'],
                                'verb_hint' => 'potential ability',
                                'modal_key' => 'can_could',
                            ],
                        ],
                    ],
                ],
                'present_negative' => [
                    [
                        'question' => 'If I were you, I {a1} ignore the rules.',
                        'markers' => [
                            'a1' => [
                                'answer' => "wouldn't",
                                'options' => ["wouldn't", "couldn't", "shouldn't"],
                                'verb_hint' => 'hypothetical refusal',
                                'modal_key' => 'will_would',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If he liked the idea, he {a1} refuse.',
                        'markers' => [
                            'a1' => [
                                'answer' => "wouldn't",
                                'options' => ["wouldn't", "couldn't", "might not"],
                                'verb_hint' => 'hypothetical refusal',
                                'modal_key' => 'will_would',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If we lived nearby, we {a1} miss class.',
                        'markers' => [
                            'a1' => [
                                'answer' => "wouldn't",
                                'options' => ["wouldn't", "couldn't", "shouldn't"],
                                'verb_hint' => 'hypothetical refusal',
                                'modal_key' => 'will_would',
                            ],
                        ],
                    ],
                ],
                'past_question' => [
                    [
                        'question' => 'If she had left earlier, {a1} she have caught the train?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'could have',
                                'options' => ['could have', 'would have', 'might have'],
                                'verb_hint' => 'missed opportunity',
                                'modal_key' => 'can_could',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If they had checked the door, {a1} they have stopped the theft?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'might have',
                                'options' => ['might have', 'could have', 'would have'],
                                'verb_hint' => 'past possibility',
                                'modal_key' => 'may_might',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If I had known, {a1} I have helped?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'would have',
                                'options' => ['would have', 'could have', 'might have'],
                                'verb_hint' => 'imagined past outcome',
                                'modal_key' => 'will_would',
                            ],
                        ],
                    ],
                ],
                'past_negative' => [
                    [
                        'question' => 'If you had listened, you {a1} have made that mistake.',
                        'markers' => [
                            'a1' => [
                                'answer' => "wouldn't have",
                                'options' => ["wouldn't have", "couldn't have", "shouldn't have"],
                                'verb_hint' => 'avoided outcome',
                                'modal_key' => 'will_would',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the door had been locked, they {a1} have entered.',
                        'markers' => [
                            'a1' => [
                                'answer' => "couldn't have",
                                'options' => ["couldn't have", "wouldn't have", "might not have"],
                                'verb_hint' => 'past impossibility',
                                'modal_key' => 'can_could',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If she had noticed the sign, she {a1} have turned wrong.',
                        'markers' => [
                            'a1' => [
                                'answer' => "wouldn't have",
                                'options' => ["wouldn't have", "couldn't have", "shouldn't have"],
                                'verb_hint' => 'avoided outcome',
                                'modal_key' => 'will_would',
                            ],
                        ],
                    ],
                ],
            ],
            'A2' => [
                'future_question' => [
                    [
                        'question' => 'If the forecast warns of storms, {a1} we cancel the picnic?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'should',
                                'options' => ['should', 'might', 'can'],
                                'verb_hint' => 'advice',
                                'modal_key' => 'should_ought_to',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the train is delayed again, {a1} we try the bus instead?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'could',
                                'options' => ['could', 'may', 'should'],
                                'verb_hint' => 'potential ability',
                                'modal_key' => 'can_could',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the manager approves it, {a1} we start the campaign early?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'may',
                                'options' => ['may', 'might', 'can'],
                                'verb_hint' => 'tentative possibility',
                                'modal_key' => 'may_might',
                            ],
                        ],
                    ],
                ],
                'future_negative' => [
                    [
                        'question' => 'If the sky clears up, we {a1} need umbrellas.',
                        'markers' => [
                            'a1' => [
                                'answer' => "won't",
                                'options' => ["won't", "mustn't", "can't"],
                                'verb_hint' => 'definite negative',
                                'modal_key' => 'will_would',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If you follow the recipe, you {a1} burn the cake.',
                        'markers' => [
                            'a1' => [
                                'answer' => "shouldn't",
                                'options' => ["shouldn't", "can't", "mustn't"],
                                'verb_hint' => 'discouraged action',
                                'modal_key' => 'should_ought_to',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the doctor calls, you {a1} ignore the instructions.',
                        'markers' => [
                            'a1' => [
                                'answer' => "mustn't",
                                'options' => ["mustn't", "shouldn't", "can't"],
                                'verb_hint' => 'prohibition',
                                'modal_key' => 'must_have_to',
                            ],
                        ],
                    ],
                ],
                'present_question' => [
                    [
                        'question' => 'If you saved enough money, {a1} you travel for a year?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'could',
                                'options' => ['could', 'might', 'would'],
                                'verb_hint' => 'potential ability',
                                'modal_key' => 'can_could',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If she spoke French, {a1} she work in Paris?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'would',
                                'options' => ['would', 'could', 'might'],
                                'verb_hint' => 'hypothetical decision',
                                'modal_key' => 'will_would',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If they knew the code, {a1} they open the door?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'might',
                                'options' => ['might', 'would', 'could'],
                                'verb_hint' => 'uncertain possibility',
                                'modal_key' => 'may_might',
                            ],
                        ],
                    ],
                ],
                'present_negative' => [
                    [
                        'question' => 'If he trusted them, he {a1} doubt every answer.',
                        'markers' => [
                            'a1' => [
                                'answer' => "wouldn't",
                                'options' => ["wouldn't", "couldn't", "shouldn't"],
                                'verb_hint' => 'hypothetical refusal',
                                'modal_key' => 'will_would',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If you had more time, you {a1} feel so stressed.',
                        'markers' => [
                            'a1' => [
                                'answer' => "wouldn't",
                                'options' => ["wouldn't", "might not", "couldn't"],
                                'verb_hint' => 'hypothetical refusal',
                                'modal_key' => 'will_would',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the road looked dangerous, I {a1} drive that way.',
                        'markers' => [
                            'a1' => [
                                'answer' => 'might not',
                                'options' => ['might not', "wouldn't", "couldn't"],
                                'verb_hint' => 'possible negative',
                                'modal_key' => 'may_might',
                            ],
                        ],
                    ],
                ],
                'past_question' => [
                    [
                        'question' => 'If the pilot had reacted sooner, {a1} he have avoided the storm?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'could have',
                                'options' => ['could have', 'might have', 'would have'],
                                'verb_hint' => 'missed opportunity',
                                'modal_key' => 'can_could',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If they had checked the schedule, {a1} they have prevented the delay?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'might have',
                                'options' => ['might have', 'could have', 'would have'],
                                'verb_hint' => 'past possibility',
                                'modal_key' => 'may_might',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If I had bought tickets earlier, {a1} I have chosen better seats?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'would have',
                                'options' => ['would have', 'could have', 'might have'],
                                'verb_hint' => 'imagined past outcome',
                                'modal_key' => 'will_would',
                            ],
                        ],
                    ],
                ],
                'past_negative' => [
                    [
                        'question' => 'If she had read the manual, she {a1} have broken the device.',
                        'markers' => [
                            'a1' => [
                                'answer' => "wouldn't have",
                                'options' => ["wouldn't have", "couldn't have", "shouldn't have"],
                                'verb_hint' => 'avoided outcome',
                                'modal_key' => 'will_would',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the guards had stayed awake, they {a1} have missed the alarm.',
                        'markers' => [
                            'a1' => [
                                'answer' => "couldn't have",
                                'options' => ["couldn't have", "wouldn't have", "might not have"],
                                'verb_hint' => 'past impossibility',
                                'modal_key' => 'can_could',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If we had checked the list, we {a1} have forgotten anyone.',
                        'markers' => [
                            'a1' => [
                                'answer' => "shouldn't have",
                                'options' => ["shouldn't have", "wouldn't have", "couldn't have"],
                                'verb_hint' => 'regretful advice',
                                'modal_key' => 'should_ought_to',
                            ],
                        ],
                    ],
                ],
            ],
            'B1' => [
                'future_question' => [
                    [
                        'question' => 'If the investors like the pitch, {a1} we secure funding?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'could',
                                'options' => ['could', 'might', 'should'],
                                'verb_hint' => 'potential ability',
                                'modal_key' => 'can_could',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the lab confirms the results, {a1} we publish immediately?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'should',
                                'options' => ['should', 'might', 'can'],
                                'verb_hint' => 'advice',
                                'modal_key' => 'should_ought_to',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the negotiations go well, {a1} we sign the contract tomorrow?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'can',
                                'options' => ['can', 'may', 'should'],
                                'verb_hint' => 'ability',
                                'modal_key' => 'can_could',
                            ],
                        ],
                    ],
                ],
                'future_negative' => [
                    [
                        'question' => 'If the forecast changes, we {a1} postpone the launch.',
                        'markers' => [
                            'a1' => [
                                'answer' => "won't",
                                'options' => ["won't", "mustn't", "can't"],
                                'verb_hint' => 'definite negative',
                                'modal_key' => 'will_would',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the safety checks are complete, you {a1} enter the area without gear.',
                        'markers' => [
                            'a1' => [
                                'answer' => "mustn't",
                                'options' => ["mustn't", "shouldn't", "can't"],
                                'verb_hint' => 'prohibition',
                                'modal_key' => 'must_have_to',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the team meets its goals, they {a1} work this weekend.',
                        'markers' => [
                            'a1' => [
                                'answer' => "won't have to",
                                'options' => ["won't have to", "mustn't", "shouldn't"],
                                'verb_hint' => 'future freedom',
                                'modal_key' => 'must_have_to',
                            ],
                        ],
                    ],
                ],
                'present_question' => [
                    [
                        'question' => 'If the company offered you a transfer, {a1} you accept?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'would',
                                'options' => ['would', 'could', 'might'],
                                'verb_hint' => 'hypothetical decision',
                                'modal_key' => 'will_would',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If we had more staff, {a1} we finish sooner?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'could',
                                'options' => ['could', 'would', 'might'],
                                'verb_hint' => 'potential ability',
                                'modal_key' => 'can_could',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the data looked uncertain, {a1} you delay the release?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'might',
                                'options' => ['might', 'would', 'could'],
                                'verb_hint' => 'uncertain possibility',
                                'modal_key' => 'may_might',
                            ],
                        ],
                    ],
                ],
                'present_negative' => [
                    [
                        'question' => 'If the plan seemed unfair, they {a1} agree.',
                        'markers' => [
                            'a1' => [
                                'answer' => "wouldn't",
                                'options' => ["wouldn't", "couldn't", "shouldn't"],
                                'verb_hint' => 'hypothetical refusal',
                                'modal_key' => 'will_would',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If I valued comfort, I {a1} choose that design.',
                        'markers' => [
                            'a1' => [
                                'answer' => "wouldn't",
                                'options' => ["wouldn't", "couldn't", "might not"],
                                'verb_hint' => 'hypothetical refusal',
                                'modal_key' => 'will_would',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If you saw the risks, you {a1} ignore them.',
                        'markers' => [
                            'a1' => [
                                'answer' => "couldn't",
                                'options' => ["couldn't", "wouldn't", "shouldn't"],
                                'verb_hint' => 'impossibility',
                                'modal_key' => 'can_could',
                            ],
                        ],
                    ],
                ],
                'past_question' => [
                    [
                        'question' => 'If the analyst had double-checked, {a1} she have caught the error?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'could have',
                                'options' => ['could have', 'might have', 'would have'],
                                'verb_hint' => 'missed opportunity',
                                'modal_key' => 'can_could',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the team had rehearsed more, {a1} they have impressed the client?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'might have',
                                'options' => ['might have', 'could have', 'would have'],
                                'verb_hint' => 'past possibility',
                                'modal_key' => 'may_might',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If I had trusted my instincts, {a1} I have avoided the loss?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'would have',
                                'options' => ['would have', 'could have', 'might have'],
                                'verb_hint' => 'imagined past outcome',
                                'modal_key' => 'will_would',
                            ],
                        ],
                    ],
                ],
                'past_negative' => [
                    [
                        'question' => 'If the guard had secured the gate, thieves {a1} have entered.',
                        'markers' => [
                            'a1' => [
                                'answer' => "couldn't have",
                                'options' => ["couldn't have", "wouldn't have", "might not have"],
                                'verb_hint' => 'past impossibility',
                                'modal_key' => 'can_could',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If they had read the fine print, they {a1} have signed that deal.',
                        'markers' => [
                            'a1' => [
                                'answer' => "wouldn't have",
                                'options' => ["wouldn't have", "couldn't have", "shouldn't have"],
                                'verb_hint' => 'avoided outcome',
                                'modal_key' => 'will_would',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If you had backed up the files, you {a1} have lost anything.',
                        'markers' => [
                            'a1' => [
                                'answer' => "wouldn't have",
                                'options' => ["wouldn't have", "couldn't have", "might not have"],
                                'verb_hint' => 'avoided outcome',
                                'modal_key' => 'will_would',
                            ],
                        ],
                    ],
                ],
            ],
            'B2' => [
                'future_question' => [
                    [
                        'question' => 'If the market shifts again, {a1} we revise the proposal?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'should',
                                'options' => ['should', 'might', 'could'],
                                'verb_hint' => 'advice',
                                'modal_key' => 'should_ought_to',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the committee hesitates, {a1} we offer more data?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'might',
                                'options' => ['might', 'could', 'may'],
                                'verb_hint' => 'uncertain possibility',
                                'modal_key' => 'may_might',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the community expects transparency, {a1} we release the minutes?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'ought to',
                                'options' => ['ought to', 'must', 'should'],
                                'verb_hint' => 'moral duty',
                                'modal_key' => 'should_ought_to',
                            ],
                        ],
                    ],
                ],
                'future_negative' => [
                    [
                        'question' => 'If the legal team approves the clause, we {a1} fear a lawsuit.',
                        'markers' => [
                            'a1' => [
                                'answer' => "needn't",
                                'options' => ["needn't", "mustn't", "can't"],
                                'verb_hint' => 'lack of necessity',
                                'modal_key' => 'need_need_to',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the shipment arrives today, you {a1} stay late.',
                        'markers' => [
                            'a1' => [
                                'answer' => "won't have to",
                                'options' => ["won't have to", "mustn't", "shouldn't"],
                                'verb_hint' => 'future freedom',
                                'modal_key' => 'must_have_to',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the sensors keep working, we {a1} reboot the system.',
                        'markers' => [
                            'a1' => [
                                'answer' => "shouldn't",
                                'options' => ["shouldn't", "mustn't", "can't"],
                                'verb_hint' => 'discouraged action',
                                'modal_key' => 'should_ought_to',
                            ],
                        ],
                    ],
                ],
                'present_question' => [
                    [
                        'question' => 'If the board valued innovation, {a1} they invest in startups?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'might',
                                'options' => ['might', 'would', 'could'],
                                'verb_hint' => 'uncertain possibility',
                                'modal_key' => 'may_might',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If you were less cautious, {a1} you take that gamble?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'might',
                                'options' => ['might', 'would', 'could'],
                                'verb_hint' => 'uncertain possibility',
                                'modal_key' => 'may_might',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the researchers had more freedom, {a1} they test radical ideas?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'could',
                                'options' => ['could', 'might', 'would'],
                                'verb_hint' => 'potential ability',
                                'modal_key' => 'can_could',
                            ],
                        ],
                    ],
                ],
                'present_negative' => [
                    [
                        'question' => 'If ethics mattered most, the firm {a1} approve that project.',
                        'markers' => [
                            'a1' => [
                                'answer' => "wouldn't",
                                'options' => ["wouldn't", "might not", "couldn't"],
                                'verb_hint' => 'hypothetical refusal',
                                'modal_key' => 'will_would',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the risks seemed inevitable, we {a1} ignore them.',
                        'markers' => [
                            'a1' => [
                                'answer' => "couldn't",
                                'options' => ["couldn't", "wouldn't", "shouldn't"],
                                'verb_hint' => 'impossibility',
                                'modal_key' => 'can_could',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the evidence felt weak, they {a1} proceed.',
                        'markers' => [
                            'a1' => [
                                'answer' => 'might not',
                                'options' => ['might not', "wouldn't", "couldn't"],
                                'verb_hint' => 'possible negative',
                                'modal_key' => 'may_might',
                            ],
                        ],
                    ],
                ],
                'past_question' => [
                    [
                        'question' => 'If the auditors had dug deeper, {a1} they have uncovered the fraud?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'might have',
                                'options' => ['might have', 'could have', 'would have'],
                                'verb_hint' => 'past possibility',
                                'modal_key' => 'may_might',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the engineers had run simulations, {a1} they have prevented the failure?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'could have',
                                'options' => ['could have', 'might have', 'would have'],
                                'verb_hint' => 'missed opportunity',
                                'modal_key' => 'can_could',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the negotiators had paused, {a1} they have secured better terms?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'might have',
                                'options' => ['might have', 'could have', 'would have'],
                                'verb_hint' => 'past possibility',
                                'modal_key' => 'may_might',
                            ],
                        ],
                    ],
                ],
                'past_negative' => [
                    [
                        'question' => 'If the alarms had functioned, the leak {a1} have gone unnoticed.',
                        'markers' => [
                            'a1' => [
                                'answer' => "couldn't have",
                                'options' => ["couldn't have", "wouldn't have", "might not have"],
                                'verb_hint' => 'past impossibility',
                                'modal_key' => 'can_could',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If we had respected the protocol, we {a1} have triggered the shutdown.',
                        'markers' => [
                            'a1' => [
                                'answer' => "wouldn't have",
                                'options' => ["wouldn't have", "couldn't have", "shouldn't have"],
                                'verb_hint' => 'avoided outcome',
                                'modal_key' => 'will_would',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If she had trusted her instincts, she {a1} have approved that plan.',
                        'markers' => [
                            'a1' => [
                                'answer' => 'might not have',
                                'options' => ['might not have', "wouldn't have", "couldn't have"],
                                'verb_hint' => 'uncertain past negative',
                                'modal_key' => 'may_might',
                            ],
                        ],
                    ],
                ],
            ],
            'C1' => [
                'future_question' => [
                    [
                        'question' => 'If stakeholder pressure grows, {a1} we disclose the preliminary data?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'ought to',
                                'options' => ['ought to', 'should', 'might'],
                                'verb_hint' => 'moral duty',
                                'modal_key' => 'should_ought_to',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the regulator signals approval, {a1} we accelerate the rollout?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'should',
                                'options' => ['should', 'might', 'must'],
                                'verb_hint' => 'advice',
                                'modal_key' => 'should_ought_to',
                            ],
                        ],
                    ],
                ],
                'future_negative' => [
                    [
                        'question' => 'If collaboration remains stable, we {a1} impose new controls.',
                        'markers' => [
                            'a1' => [
                                'answer' => "needn't",
                                'options' => ["needn't", "mustn't", "can't"],
                                'verb_hint' => 'lack of necessity',
                                'modal_key' => 'need_need_to',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the partners honour the contract, we {a1} renegotiate terms.',
                        'markers' => [
                            'a1' => [
                                'answer' => "won't",
                                'options' => ["won't", "mustn't", "shouldn't"],
                                'verb_hint' => 'definite negative',
                                'modal_key' => 'will_would',
                            ],
                        ],
                    ],
                ],
                'present_question' => [
                    [
                        'question' => 'If the board valued dissent, {a1} they invite critical voices?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'might',
                                'options' => ['might', 'would', 'could'],
                                'verb_hint' => 'uncertain possibility',
                                'modal_key' => 'may_might',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the analysts had richer data, {a1} they forecast disruptions better?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'could',
                                'options' => ['could', 'might', 'would'],
                                'verb_hint' => 'potential ability',
                                'modal_key' => 'can_could',
                            ],
                        ],
                    ],
                ],
                'present_negative' => [
                    [
                        'question' => 'If transparency were paramount, leadership {a1} withhold the memo.',
                        'markers' => [
                            'a1' => [
                                'answer' => 'ought not to',
                                'options' => ['ought not to', "wouldn't", "might not"],
                                'verb_hint' => 'ethical avoidance',
                                'modal_key' => 'should_ought_to',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the timeline looked unrealistic, we {a1} commit to it.',
                        'markers' => [
                            'a1' => [
                                'answer' => "might not",
                                'options' => ["might not", "wouldn't", "couldn't"],
                                'verb_hint' => 'possible negative',
                                'modal_key' => 'may_might',
                            ],
                        ],
                    ],
                ],
                'past_question' => [
                    [
                        'question' => 'If the auditors had widened their scope, {a1} they have exposed the fraud earlier?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'might have',
                                'options' => ['might have', 'could have', 'would have'],
                                'verb_hint' => 'past possibility',
                                'modal_key' => 'may_might',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the scientists had questioned the model, {a1} they have avoided confirmation bias?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'could have',
                                'options' => ['could have', 'might have', 'would have'],
                                'verb_hint' => 'missed opportunity',
                                'modal_key' => 'can_could',
                            ],
                        ],
                    ],
                ],
                'past_negative' => [
                    [
                        'question' => 'If the committee had awaited peer review, it {a1} have endorsed the draft so quickly.',
                        'markers' => [
                            'a1' => [
                                'answer' => 'ought not to have',
                                'options' => ['ought not to have', "wouldn't have", "couldn't have"],
                                'verb_hint' => 'moral regret',
                                'modal_key' => 'should_ought_to',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If we had shared the dataset earlier, we {a1} have sparked that backlash.',
                        'markers' => [
                            'a1' => [
                                'answer' => "needn't have",
                                'options' => ["needn't have", "shouldn't have", "wouldn't have"],
                                'verb_hint' => 'unnecessary action',
                                'modal_key' => 'need_need_to',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function determineModalTagKeys(string $answer, ?string $explicitKey = null): array
    {
        if ($explicitKey !== null && isset($this->modalTagConfig[$explicitKey])) {
            return [$explicitKey];
        }

        $normalized = $this->normalizeModalPhrase($answer);
        $keys = [];

        foreach ($this->modalTagConfig as $key => $config) {
            foreach ($config['keywords'] as $keyword) {
                if ($normalized === $this->normalizeModalPhrase($keyword)) {
                    $keys[] = $key;
                    break;
                }
            }
        }

        return array_values(array_unique($keys));
    }

    private function normalizeModalPhrase(string $value): string
    {
        $value = trim(mb_strtolower($value));
        $value = preg_replace('/\s+/', ' ', $value);

        return is_string($value) ? $value : '';
    }
}
