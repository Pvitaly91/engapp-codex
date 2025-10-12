<?php

namespace Database\Seeders\Ai;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class ConditionalsComprehensiveAiSeeder extends QuestionSeeder
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

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Conditionals'])->id;

        $sourceMap = [
            'past_question' => Source::firstOrCreate(['name' => 'AI Conditionals: Past Questions'])->id,
            'past_negative' => Source::firstOrCreate(['name' => 'AI Conditionals: Past Negatives'])->id,
            'present_question' => Source::firstOrCreate(['name' => 'AI Conditionals: Present Questions'])->id,
            'present_negative' => Source::firstOrCreate(['name' => 'AI Conditionals: Present Negatives'])->id,
            'future_question' => Source::firstOrCreate(['name' => 'AI Conditionals: Future Questions'])->id,
            'future_negative' => Source::firstOrCreate(['name' => 'AI Conditionals: Future Negatives'])->id,
        ];

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'AI Conditional Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Conditional Sentence Completion'],
            ['category' => 'English Grammar Detail']
        )->id;

        $typeTagIds = [
            'question' => Tag::firstOrCreate(['name' => 'Conditional Question Form'], ['category' => 'English Grammar Structure'])->id,
            'negative' => Tag::firstOrCreate(['name' => 'Conditional Negative Form'], ['category' => 'English Grammar Structure'])->id,
        ];

        $tenseTagIds = [
            'First Conditional' => Tag::firstOrCreate(['name' => 'First Conditional'], ['category' => 'Tenses'])->id,
            'Second Conditional' => Tag::firstOrCreate(['name' => 'Second Conditional'], ['category' => 'Tenses'])->id,
            'Third Conditional' => Tag::firstOrCreate(['name' => 'Third Conditional'], ['category' => 'Tenses'])->id,
        ];

        $questions = $this->buildQuestionBank();

        $items = [];
        $meta = [];

        foreach ($questions as $index => $question) {
            $answers = [];
            $answersMap = [];
            $verbHints = [];
            $optionsPerMarker = [];
            $optionMarkerMap = [];

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
                        'question' => 'If it rains tonight, will we {a1} the picnic?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'cancel',
                                'options' => ['cancel', 'cancelled', 'cancels'],
                                'verb_hint' => 'cancel',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If you feel tired, will you {a1} the class early?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'leave',
                                'options' => ['leave', 'leaves', 'left'],
                                'verb_hint' => 'leave',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the bus is late, will they {a1} for us?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'wait',
                                'options' => ['wait', 'waited', 'waiting'],
                                'verb_hint' => 'wait',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If Anna calls later, will I {a1} to her?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'talk',
                                'options' => ['talk', 'talks', 'talked'],
                                'verb_hint' => 'talk',
                            ],
                        ],
                    ],
                ],
                'future_negative' => [
                    [
                        'question' => 'If you close the window, you {a1} cold.',
                        'markers' => [
                            'a1' => [
                                'answer' => "won't feel",
                                'options' => ["won't feel", "don't feel", "didn't feel"],
                                'verb_hint' => 'not feel',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If they arrive early, we {a1} the meeting.',
                        'markers' => [
                            'a1' => [
                                'answer' => "won't delay",
                                'options' => ["won't delay", "don't delay", "didn't delay"],
                                'verb_hint' => 'not delay',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If I cook tonight, you {a1} hungry.',
                        'markers' => [
                            'a1' => [
                                'answer' => "won't be",
                                'options' => ["won't be", "aren't", "weren't"],
                                'verb_hint' => 'not be',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the shop is closed, I {a1} bread.',
                        'markers' => [
                            'a1' => [
                                'answer' => "won't buy",
                                'options' => ["won't buy", "don't buy", "didn't buy"],
                                'verb_hint' => 'not buy',
                            ],
                        ],
                    ],
                ],
                'present_question' => [
                    [
                        'question' => 'If you won a free ticket, would you {a1} to the concert?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'go',
                                'options' => ['go', 'went', 'going'],
                                'verb_hint' => 'go',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If Ben had more time, would he {a1} another language?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'learn',
                                'options' => ['learn', 'learned', 'learning'],
                                'verb_hint' => 'learn',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the cafe were quiet, would we {a1} there?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'study',
                                'options' => ['study', 'studied', 'studying'],
                                'verb_hint' => 'study',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If I saw a wallet, would I {a1} it to the desk?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'take',
                                'options' => ['take', 'took', 'taking'],
                                'verb_hint' => 'take',
                            ],
                        ],
                    ],
                ],
                'present_negative' => [
                    [
                        'question' => 'If I {a1} so busy, I would call you every day.',
                        'markers' => [
                            'a1' => [
                                'answer' => "weren't",
                                'options' => ["weren't", "wasn't", "hadn't been"],
                                'verb_hint' => 'not be',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If he knew your secret, he {a1} silent.',
                        'markers' => [
                            'a1' => [
                                'answer' => "wouldn't stay",
                                'options' => ["wouldn't stay", "won't stay", "would stay"],
                                'verb_hint' => 'not stay',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If they lived closer, they {a1} late so often.',
                        'markers' => [
                            'a1' => [
                                'answer' => "wouldn't arrive",
                                'options' => ["wouldn't arrive", "won't arrive", "would arrive"],
                                'verb_hint' => 'not arrive',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If we had a car, we {a1} the bus.',
                        'markers' => [
                            'a1' => [
                                'answer' => "wouldn't take",
                                'options' => ["wouldn't take", "won't take", "would take"],
                                'verb_hint' => 'not take',
                            ],
                        ],
                    ],
                ],
                'past_question' => [
                    [
                        'question' => 'If you had missed the train, would you {a1} a taxi?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'have taken',
                                'options' => ['have taken', 'take', 'took'],
                                'verb_hint' => 'take',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If I had known earlier, would I {a1} you?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'have called',
                                'options' => ['have called', 'call', 'called'],
                                'verb_hint' => 'call',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If they had seen the sign, would they {a1} that road?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'have chosen',
                                'options' => ['have chosen', 'choose', 'chose'],
                                'verb_hint' => 'choose',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If Mia had saved more, would she {a1} the trip?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'have booked',
                                'options' => ['have booked', 'book', 'booked'],
                                'verb_hint' => 'book',
                            ],
                        ],
                    ],
                ],
                'past_negative' => [
                    [
                        'question' => 'If I {a1} my keys, I would have arrived on time.',
                        'markers' => [
                            'a1' => [
                                'answer' => "hadn't lost",
                                'options' => ["hadn't lost", "didn't lose", "haven't lost"],
                                'verb_hint' => 'not lose',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If he had checked the map, he {a1} in the woods.',
                        'markers' => [
                            'a1' => [
                                'answer' => "wouldn't have got lost",
                                'options' => ["wouldn't have got lost", "wouldn't get lost", "won't get lost"],
                                'verb_hint' => 'not get lost',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If they had listened to the warning, they {a1} the roof.',
                        'markers' => [
                            'a1' => [
                                'answer' => "wouldn't have damaged",
                                'options' => ["wouldn't have damaged", "wouldn't damage", "won't damage"],
                                'verb_hint' => 'not damage',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If we {a1} the deadline, the client would have been calm.',
                        'markers' => [
                            'a1' => [
                                'answer' => "hadn't missed",
                                'options' => ["hadn't missed", "didn't miss", "haven't missed"],
                                'verb_hint' => 'not miss',
                            ],
                        ],
                    ],
                ],
            ],
            'A2' => [
                'future_question' => [
                    [
                        'question' => 'If the courier {a1} before noon, will we {a2} the documents today?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'arrives',
                                'options' => ['arrives', 'arrive', 'arrived'],
                                'verb_hint' => 'arrive',
                            ],
                            'a2' => [
                                'answer' => 'sign',
                                'options' => ['sign', 'signed', 'signing'],
                                'verb_hint' => 'sign',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If you {a1} the form now, will the clerk {a2} it for you?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'complete',
                                'options' => ['complete', 'completes', 'completed'],
                                'verb_hint' => 'complete',
                            ],
                            'a2' => [
                                'answer' => 'check',
                                'options' => ['check', 'checks', 'checked'],
                                'verb_hint' => 'check',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If Nina {a1} the tickets online, will you {a2} the seats together?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'buys',
                                'options' => ['buys', 'buy', 'bought'],
                                'verb_hint' => 'buy',
                            ],
                            'a2' => [
                                'answer' => 'choose',
                                'options' => ['choose', 'chooses', 'chose'],
                                'verb_hint' => 'choose',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If they {a1} the report tonight, will the manager {a2} it tomorrow?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'finish',
                                'options' => ['finish', 'finishes', 'finished'],
                                'verb_hint' => 'finish',
                            ],
                            'a2' => [
                                'answer' => 'approve',
                                'options' => ['approve', 'approves', 'approved'],
                                'verb_hint' => 'approve',
                            ],
                        ],
                    ],
                ],
                'future_negative' => [
                    [
                        'question' => 'If the roads {a1} icy, we {a2} the mountain drive.',
                        'markers' => [
                            'a1' => [
                                'answer' => 'get',
                                'options' => ['get', 'gets', 'got'],
                                'verb_hint' => 'get',
                            ],
                            'a2' => [
                                'answer' => "won't risk",
                                'options' => ["won't risk", "don't risk", "didn't risk"],
                                'verb_hint' => 'not risk',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If you {a1} your ticket, you {a2} the concert.',
                        'markers' => [
                            'a1' => [
                                'answer' => 'forget',
                                'options' => ['forget', 'forgets', 'forgot'],
                                'verb_hint' => 'forget',
                            ],
                            'a2' => [
                                'answer' => "won't enter",
                                'options' => ["won't enter", "don't enter", "didn't enter"],
                                'verb_hint' => 'not enter',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If they {a1} the warning signs, the hikers {a2} the cliff path.',
                        'markers' => [
                            'a1' => [
                                'answer' => 'ignore',
                                'options' => ['ignore', 'ignores', 'ignored'],
                                'verb_hint' => 'ignore',
                            ],
                            'a2' => [
                                'answer' => "won't take",
                                'options' => ["won't take", "don't take", "didn't take"],
                                'verb_hint' => 'not take',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If I {a1} enough rest, I {a2} tonight\'s meeting.',
                        'markers' => [
                            'a1' => [
                                'answer' => "don't get",
                                'options' => ["don't get", 'get', 'got'],
                                'verb_hint' => 'not get',
                            ],
                            'a2' => [
                                'answer' => "won't focus",
                                'options' => ["won't focus", "don't focus", "didn't focus"],
                                'verb_hint' => 'not focus',
                            ],
                        ],
                    ],
                ],
                'present_question' => [
                    [
                        'question' => 'If the team {a1} more support, would they {a2} the project on time?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'had',
                                'options' => ['had', 'have', 'has'],
                                'verb_hint' => 'have',
                            ],
                            'a2' => [
                                'answer' => 'finish',
                                'options' => ['finish', 'finished', 'finishing'],
                                'verb_hint' => 'finish',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If Maya {a1} closer to campus, would she {a2} to cycle to class?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'lived',
                                'options' => ['lived', 'lives', 'live'],
                                'verb_hint' => 'live',
                            ],
                            'a2' => [
                                'answer' => 'decide',
                                'options' => ['decide', 'decided', 'deciding'],
                                'verb_hint' => 'decide',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If you {a1} a different job, would you {a2} happier?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'took',
                                'options' => ['took', 'take', 'taken'],
                                'verb_hint' => 'take',
                            ],
                            'a2' => [
                                'answer' => 'feel',
                                'options' => ['feel', 'felt', 'feeling'],
                                'verb_hint' => 'feel',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the museum {a1} late hours, would we {a2} after work?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'offered',
                                'options' => ['offered', 'offers', 'offer'],
                                'verb_hint' => 'offer',
                            ],
                            'a2' => [
                                'answer' => 'visit',
                                'options' => ['visit', 'visited', 'visiting'],
                                'verb_hint' => 'visit',
                            ],
                        ],
                    ],
                ],
                'present_negative' => [
                    [
                        'question' => 'If I {a1} allergic to cats, I wouldn\'t keep one at home.',
                        'markers' => [
                            'a1' => [
                                'answer' => "weren't",
                                'options' => ["weren't", "wasn't", "hadn't been"],
                                'verb_hint' => 'not be',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If they {a1} so stubborn, they wouldn\'t argue every day.',
                        'markers' => [
                            'a1' => [
                                'answer' => "didn't act",
                                'options' => ["didn't act", "don't act", "wouldn't act"],
                                'verb_hint' => 'not act',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the city {a1} so noisy, we wouldn\'t need earplugs.',
                        'markers' => [
                            'a1' => [
                                'answer' => "didn't stay",
                                'options' => ["didn't stay", "doesn't stay", "wouldn't stay"],
                                'verb_hint' => 'not stay',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If you {a1} so late, you wouldn\'t feel exhausted.',
                        'markers' => [
                            'a1' => [
                                'answer' => "didn't stay up",
                                'options' => ["didn't stay up", "don't stay up", "wouldn't stay up"],
                                'verb_hint' => 'not stay up',
                            ],
                        ],
                    ],
                ],
                'past_question' => [
                    [
                        'question' => 'If the pilot had seen the storm, would he {a1} the flight?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'have delayed',
                                'options' => ['have delayed', 'delay', 'delayed'],
                                'verb_hint' => 'delay',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If we had saved more, would we {a1} a bigger apartment?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'have rented',
                                'options' => ['have rented', 'rent', 'rented'],
                                'verb_hint' => 'rent',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If Lara had worn boots, would she {a1} the puddle?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'have avoided',
                                'options' => ['have avoided', 'avoid', 'avoided'],
                                'verb_hint' => 'avoid',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If they had remembered the address, would they {a1} on time?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'have arrived',
                                'options' => ['have arrived', 'arrive', 'arrived'],
                                'verb_hint' => 'arrive',
                            ],
                        ],
                    ],
                ],
                'past_negative' => [
                    [
                        'question' => 'If I {a1} the alarm, I wouldn\'t have missed the train.',
                        'markers' => [
                            'a1' => [
                                'answer' => "hadn't ignored",
                                'options' => ["hadn't ignored", "didn't ignore", "haven't ignored"],
                                'verb_hint' => 'not ignore',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If she had packed earlier, she {a1} her passport.',
                        'markers' => [
                            'a1' => [
                                'answer' => "wouldn't have forgotten",
                                'options' => ["wouldn't have forgotten", "wouldn't forget", "won't forget"],
                                'verb_hint' => 'not forget',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the team had rehearsed more, they {a1} the opening night.',
                        'markers' => [
                            'a1' => [
                                'answer' => "wouldn't have spoiled",
                                'options' => ["wouldn't have spoiled", "wouldn't spoil", "won't spoil"],
                                'verb_hint' => 'not spoil',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If we {a1} the instructions, we wouldn\'t have broken the device.',
                        'markers' => [
                            'a1' => [
                                'answer' => "hadn't skipped",
                                'options' => ["hadn't skipped", "didn't skip", "haven't skipped"],
                                'verb_hint' => 'not skip',
                            ],
                        ],
                    ],
                ],
            ],
            'B1' => [
                'future_question' => [
                    [
                        'question' => 'If the investors {a1} positive feedback, will the team {a2} the prototype this week?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'give',
                                'options' => ['give', 'gives', 'gave'],
                                'verb_hint' => 'give',
                            ],
                            'a2' => [
                                'answer' => 'launch',
                                'options' => ['launch', 'launched', 'launches'],
                                'verb_hint' => 'launch',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If our hotel {a1} an upgrade, will you {a2} it for the clients?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'offers',
                                'options' => ['offers', 'offer', 'offered'],
                                'verb_hint' => 'offer',
                            ],
                            'a2' => [
                                'answer' => 'accept',
                                'options' => ['accept', 'accepted', 'accepts'],
                                'verb_hint' => 'accept',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the editor {a1} the chapter tonight, will we {a2} the proofs tomorrow?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'reviews',
                                'options' => ['reviews', 'review', 'reviewed'],
                                'verb_hint' => 'review',
                            ],
                            'a2' => [
                                'answer' => 'send',
                                'options' => ['send', 'sent', 'sending'],
                                'verb_hint' => 'send',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If you {a1} the visa today, will the embassy {a2} it by Friday?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'submit',
                                'options' => ['submit', 'submits', 'submitted'],
                                'verb_hint' => 'submit',
                            ],
                            'a2' => [
                                'answer' => 'approve',
                                'options' => ['approve', 'approves', 'approved'],
                                'verb_hint' => 'approve',
                            ],
                        ],
                    ],
                ],
                'future_negative' => [
                    [
                        'question' => 'If the supplier {a1} the shipment late, we {a2} the launch date.',
                        'markers' => [
                            'a1' => [
                                'answer' => 'delivers',
                                'options' => ['delivers', 'deliver', 'delivered'],
                                'verb_hint' => 'deliver',
                            ],
                            'a2' => [
                                'answer' => "won't move",
                                'options' => ["won't move", "don't move", "didn't move"],
                                'verb_hint' => 'not move',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If you {a1} the updated manual, the technicians {a2} the new settings.',
                        'markers' => [
                            'a1' => [
                                'answer' => 'ignore',
                                'options' => ['ignore', 'ignores', 'ignored'],
                                'verb_hint' => 'ignore',
                            ],
                            'a2' => [
                                'answer' => "won't understand",
                                'options' => ["won't understand", "don't understand", "didn't understand"],
                                'verb_hint' => 'not understand',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the lab {a1} the results late, we {a2} the medication tomorrow.',
                        'markers' => [
                            'a1' => [
                                'answer' => 'sends',
                                'options' => ['sends', 'send', 'sent'],
                                'verb_hint' => 'send',
                            ],
                            'a2' => [
                                'answer' => "won't release",
                                'options' => ["won't release", "don't release", "didn't release"],
                                'verb_hint' => 'not release',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If I {a1} a taxi now, I {a2} the ceremony.',
                        'markers' => [
                            'a1' => [
                                'answer' => "don't get",
                                'options' => ["don't get", 'get', 'got'],
                                'verb_hint' => 'not get',
                            ],
                            'a2' => [
                                'answer' => "won't reach",
                                'options' => ["won't reach", "don't reach", "didn't reach"],
                                'verb_hint' => 'not reach',
                            ],
                        ],
                    ],
                ],
                'present_question' => [
                    [
                        'question' => 'If the committee {a1} your proposal, would you {a2} the extra budget wisely?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'approved',
                                'options' => ['approved', 'approves', 'approve'],
                                'verb_hint' => 'approve',
                            ],
                            'a2' => [
                                'answer' => 'use',
                                'options' => ['use', 'used', 'using'],
                                'verb_hint' => 'use',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the interns {a1} more mentoring, would they {a2} faster?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'received',
                                'options' => ['received', 'receive', 'receives'],
                                'verb_hint' => 'receive',
                            ],
                            'a2' => [
                                'answer' => 'progress',
                                'options' => ['progress', 'progressed', 'progressing'],
                                'verb_hint' => 'progress',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If Mia {a1} a flexible schedule, would she {a2} later?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'had',
                                'options' => ['had', 'has', 'have'],
                                'verb_hint' => 'have',
                            ],
                            'a2' => [
                                'answer' => 'work',
                                'options' => ['work', 'worked', 'working'],
                                'verb_hint' => 'work',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the town {a1} a new gallery, would tourists {a2} longer?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'built',
                                'options' => ['built', 'builds', 'build'],
                                'verb_hint' => 'build',
                            ],
                            'a2' => [
                                'answer' => 'stay',
                                'options' => ['stay', 'stayed', 'staying'],
                                'verb_hint' => 'stay',
                            ],
                        ],
                    ],
                ],
                'present_negative' => [
                    [
                        'question' => 'If I {a1} so cautious, I wouldn\'t ignore their advice.',
                        'markers' => [
                            'a1' => [
                                'answer' => "weren't",
                                'options' => ["weren't", "wasn't", "hadn't been"],
                                'verb_hint' => 'not be',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the board {a1} the risks, it wouldn\'t delay decisions.',
                        'markers' => [
                            'a1' => [
                                'answer' => "didn't fear",
                                'options' => ["didn't fear", "doesn't fear", "wouldn't fear"],
                                'verb_hint' => 'not fear',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the neighbors {a1} loud music every night, we wouldn\'t wear earplugs.',
                        'markers' => [
                            'a1' => [
                                'answer' => "didn't play",
                                'options' => ["didn't play", "don't play", "wouldn't play"],
                                'verb_hint' => 'not play',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If you {a1} every detail, you wouldn\'t overthink everything.',
                        'markers' => [
                            'a1' => [
                                'answer' => "didn't question",
                                'options' => ["didn't question", "don't question", "wouldn't question"],
                                'verb_hint' => 'not question',
                            ],
                        ],
                    ],
                ],
                'past_question' => [
                    [
                        'question' => 'If the auditors had found errors, would they {a1} the report?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'have rewritten',
                                'options' => ['have rewritten', 'rewrite', 'rewrote'],
                                'verb_hint' => 'rewrite',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If we had booked earlier, would we {a1} a balcony room?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'have secured',
                                'options' => ['have secured', 'secure', 'secured'],
                                'verb_hint' => 'secure',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If Clara had studied law, would she {a1} a different career?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'have chosen',
                                'options' => ['have chosen', 'choose', 'chose'],
                                'verb_hint' => 'choose',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the team had trusted the data, would they {a1} the project?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'have continued',
                                'options' => ['have continued', 'continue', 'continued'],
                                'verb_hint' => 'continue',
                            ],
                        ],
                    ],
                ],
                'past_negative' => [
                    [
                        'question' => 'If I {a1} your warning, I wouldn\'t have invested.',
                        'markers' => [
                            'a1' => [
                                'answer' => "hadn't dismissed",
                                'options' => ["hadn't dismissed", "didn't dismiss", "haven't dismissed"],
                                'verb_hint' => 'not dismiss',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the driver had rested, he {a1} the barrier.',
                        'markers' => [
                            'a1' => [
                                'answer' => "wouldn't have hit",
                                'options' => ["wouldn't have hit", "wouldn't hit", "won't hit"],
                                'verb_hint' => 'not hit',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If they had set two alarms, they {a1} their flight.',
                        'markers' => [
                            'a1' => [
                                'answer' => "wouldn't have missed",
                                'options' => ["wouldn't have missed", "wouldn't miss", "won't miss"],
                                'verb_hint' => 'not miss',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If we {a1} the contract, we wouldn\'t have faced penalties.',
                        'markers' => [
                            'a1' => [
                                'answer' => "hadn't breached",
                                'options' => ["hadn't breached", "didn't breach", "haven't breached"],
                                'verb_hint' => 'not breach',
                            ],
                        ],
                    ],
                ],
            ],
            'B2' => [
                'future_question' => [
                    [
                        'question' => 'If the research team {a1} conclusive evidence, will the board {a2} the new policy this quarter?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'collects',
                                'options' => ['collects', 'collect', 'collected'],
                                'verb_hint' => 'collect',
                            ],
                            'a2' => [
                                'answer' => 'adopt',
                                'options' => ['adopt', 'adopts', 'adopted'],
                                'verb_hint' => 'adopt',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If our partners {a1} the draft tonight, will you {a2} the announcement tomorrow?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'finalize',
                                'options' => ['finalize', 'finalizes', 'finalized'],
                                'verb_hint' => 'finalize',
                            ],
                            'a2' => [
                                'answer' => 'schedule',
                                'options' => ['schedule', 'schedules', 'scheduled'],
                                'verb_hint' => 'schedule',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the conference {a1} enough speakers, will we {a2} the registration fee?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'secures',
                                'options' => ['secures', 'secure', 'secured'],
                                'verb_hint' => 'secure',
                            ],
                            'a2' => [
                                'answer' => 'reduce',
                                'options' => ['reduce', 'reduces', 'reduced'],
                                'verb_hint' => 'reduce',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If you {a1} the stakeholders early, will they {a2} their expectations?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'brief',
                                'options' => ['brief', 'briefs', 'briefed'],
                                'verb_hint' => 'brief',
                            ],
                            'a2' => [
                                'answer' => 'adjust',
                                'options' => ['adjust', 'adjusts', 'adjusted'],
                                'verb_hint' => 'adjust',
                            ],
                        ],
                    ],
                ],
                'future_negative' => [
                    [
                        'question' => 'If the negotiation {a1} today, we {a2} the contract this week.',
                        'markers' => [
                            'a1' => [
                                'answer' => 'fails',
                                'options' => ['fails', 'fail', 'failed'],
                                'verb_hint' => 'fail',
                            ],
                            'a2' => [
                                'answer' => "won't sign",
                                'options' => ["won't sign", "don't sign", "didn't sign"],
                                'verb_hint' => 'not sign',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the shipment {a1} customs, the retailers {a2} the promotion.',
                        'markers' => [
                            'a1' => [
                                'answer' => 'gets stuck',
                                'options' => ['gets stuck', 'get stuck', 'got stuck'],
                                'verb_hint' => 'get stuck',
                            ],
                            'a2' => [
                                'answer' => "won't launch",
                                'options' => ["won't launch", "don't launch", "didn't launch"],
                                'verb_hint' => 'not launch',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If our CFO {a1} the forecast, investors {a2} reassurance.',
                        'markers' => [
                            'a1' => [
                                'answer' => "doesn't share",
                                'options' => ["doesn't share", 'shares', 'shared'],
                                'verb_hint' => 'not share',
                            ],
                            'a2' => [
                                'answer' => "won't get",
                                'options' => ["won't get", "don't get", "didn't get"],
                                'verb_hint' => 'not get',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If I {a1} the grant application, the lab {a2} new equipment.',
                        'markers' => [
                            'a1' => [
                                'answer' => "don't finish",
                                'options' => ["don't finish", 'finish', 'finished'],
                                'verb_hint' => 'not finish',
                            ],
                            'a2' => [
                                'answer' => "won't receive",
                                'options' => ["won't receive", "don't receive", "didn't receive"],
                                'verb_hint' => 'not receive',
                            ],
                        ],
                    ],
                ],
                'present_question' => [
                    [
                        'question' => 'If the analysts {a1} more context, would they {a2} the trend differently?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'had',
                                'options' => ['had', 'have', 'has'],
                                'verb_hint' => 'have',
                            ],
                            'a2' => [
                                'answer' => 'interpret',
                                'options' => ['interpret', 'interpreted', 'interpreting'],
                                'verb_hint' => 'interpret',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If your mentor {a1} more time, would you {a2} a different topic?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'had',
                                'options' => ['had', 'has', 'have'],
                                'verb_hint' => 'have',
                            ],
                            'a2' => [
                                'answer' => 'pursue',
                                'options' => ['pursue', 'pursued', 'pursuing'],
                                'verb_hint' => 'pursue',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the council {a1} a larger grant, would the artists {a2} bigger installations?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'provided',
                                'options' => ['provided', 'provides', 'provide'],
                                'verb_hint' => 'provide',
                            ],
                            'a2' => [
                                'answer' => 'build',
                                'options' => ['build', 'built', 'building'],
                                'verb_hint' => 'build',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If we {a1} an extra engineer, would the rollout {a2} smoother?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'hired',
                                'options' => ['hired', 'hire', 'hires'],
                                'verb_hint' => 'hire',
                            ],
                            'a2' => [
                                'answer' => 'be',
                                'options' => ['be', 'was', 'been'],
                                'verb_hint' => 'be',
                            ],
                        ],
                    ],
                ],
                'present_negative' => [
                    [
                        'question' => 'If the firm {a1} so risk-averse, it wouldn\'t ignore emerging markets.',
                        'markers' => [
                            'a1' => [
                                'answer' => "weren't",
                                'options' => ["weren't", "wasn't", "hadn't been"],
                                'verb_hint' => 'not be',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the students {a1} assignments late, they wouldn\'t lose participation points.',
                        'markers' => [
                            'a1' => [
                                'answer' => "didn't submit",
                                'options' => ["didn't submit", "don't submit", "wouldn't submit"],
                                'verb_hint' => 'not submit',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If you {a1} every rumour, you wouldn\'t stress the team.',
                        'markers' => [
                            'a1' => [
                                'answer' => "didn't repeat",
                                'options' => ["didn't repeat", "don't repeat", "wouldn't repeat"],
                                'verb_hint' => 'not repeat',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If our neighbors {a1} so early, we wouldn\'t miss sleep.',
                        'markers' => [
                            'a1' => [
                                'answer' => "didn't start",
                                'options' => ["didn't start", "don't start", "wouldn't start"],
                                'verb_hint' => 'not start',
                            ],
                        ],
                    ],
                ],
                'past_question' => [
                    [
                        'question' => 'If the panel had heard her pitch, would they {a1} the funding?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'have approved',
                                'options' => ['have approved', 'approve', 'approved'],
                                'verb_hint' => 'approve',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If we had mapped the risks, would we {a1} a backup supplier?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'have chosen',
                                'options' => ['have chosen', 'choose', 'chose'],
                                'verb_hint' => 'choose',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the director had rehearsed longer, would the cast {a1} fewer mistakes?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'have made',
                                'options' => ['have made', 'make', 'made'],
                                'verb_hint' => 'make',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If they had tracked the spending, would they {a1} the budget overrun?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'have avoided',
                                'options' => ['have avoided', 'avoid', 'avoided'],
                                'verb_hint' => 'avoid',
                            ],
                        ],
                    ],
                ],
                'past_negative' => [
                    [
                        'question' => 'If I {a1} the backup files, we wouldn\'t have lost the footage.',
                        'markers' => [
                            'a1' => [
                                'answer' => "hadn't deleted",
                                'options' => ["hadn't deleted", "didn't delete", "haven't deleted"],
                                'verb_hint' => 'not delete',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the airline had notified us, we {a1} hours at the airport.',
                        'markers' => [
                            'a1' => [
                                'answer' => "wouldn't have waited",
                                'options' => ["wouldn't have waited", "wouldn't wait", "won't wait"],
                                'verb_hint' => 'not wait',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If our chef had checked the oven, the pastries {a1} burnt.',
                        'markers' => [
                            'a1' => [
                                'answer' => "wouldn't have been",
                                'options' => ["wouldn't have been", "wouldn't be", "won't be"],
                                'verb_hint' => 'not be',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the engineers {a1} the faulty valve, the factory wouldn\'t have shut down.',
                        'markers' => [
                            'a1' => [
                                'answer' => "hadn't ignored",
                                'options' => ["hadn't ignored", "didn't ignore", "haven't ignored"],
                                'verb_hint' => 'not ignore',
                            ],
                        ],
                    ],
                ],
            ],
            'C1' => [
                'future_question' => [
                    [
                        'question' => 'If the ethics panel {a1} the revised protocol, will the institute {a2} the trial next month?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'endorses',
                                'options' => ['endorses', 'endorse', 'endorsed'],
                                'verb_hint' => 'endorse',
                            ],
                            'a2' => [
                                'answer' => 'open',
                                'options' => ['open', 'opens', 'opened'],
                                'verb_hint' => 'open',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If your team {a1} the migration script tonight, will operations {a2} the switchover at dawn?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'completes',
                                'options' => ['completes', 'complete', 'completed'],
                                'verb_hint' => 'complete',
                            ],
                            'a2' => [
                                'answer' => 'trigger',
                                'options' => ['trigger', 'triggers', 'triggered'],
                                'verb_hint' => 'trigger',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the curator {a1} the loan agreement, will the gallery {a2} the rare collection?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'signs',
                                'options' => ['signs', 'sign', 'signed'],
                                'verb_hint' => 'sign',
                            ],
                            'a2' => [
                                'answer' => 'display',
                                'options' => ['display', 'displays', 'displayed'],
                                'verb_hint' => 'display',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If we {a1} the climate report in time, will the council {a2} emergency measures?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'deliver',
                                'options' => ['deliver', 'delivers', 'delivered'],
                                'verb_hint' => 'deliver',
                            ],
                            'a2' => [
                                'answer' => 'declare',
                                'options' => ['declare', 'declares', 'declared'],
                                'verb_hint' => 'declare',
                            ],
                        ],
                    ],
                ],
                'future_negative' => [
                    [
                        'question' => 'If the auditors {a1} a discrepancy, we {a2} the quarterly statement.',
                        'markers' => [
                            'a1' => [
                                'answer' => "don't flag",
                                'options' => ["don't flag", 'flag', 'flagged'],
                                'verb_hint' => 'not flag',
                            ],
                            'a2' => [
                                'answer' => "won't release",
                                'options' => ["won't release", "don't release", "didn't release"],
                                'verb_hint' => 'not release',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the venue {a1} accessible, the organizers {a2} the booking.',
                        'markers' => [
                            'a1' => [
                                'answer' => "isn't",
                                'options' => ["isn't", 'is', 'was'],
                                'verb_hint' => 'not be',
                            ],
                            'a2' => [
                                'answer' => "won't confirm",
                                'options' => ["won't confirm", "don't confirm", "didn't confirm"],
                                'verb_hint' => 'not confirm',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If I {a1} the strategic memo, the board {a2} immediate approval.',
                        'markers' => [
                            'a1' => [
                                'answer' => "don't submit",
                                'options' => ["don't submit", 'submit', 'submitted'],
                                'verb_hint' => 'not submit',
                            ],
                            'a2' => [
                                'answer' => "won't grant",
                                'options' => ["won't grant", "don't grant", "didn't grant"],
                                'verb_hint' => 'not grant',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If our sponsors {a1} their pledge, we {a2} the satellite launch.',
                        'markers' => [
                            'a1' => [
                                'answer' => 'withdraw',
                                'options' => ['withdraw', 'withdraws', 'withdrew'],
                                'verb_hint' => 'withdraw',
                            ],
                            'a2' => [
                                'answer' => "won't fund",
                                'options' => ["won't fund", "don't fund", "didn't fund"],
                                'verb_hint' => 'not fund',
                            ],
                        ],
                    ],
                ],
                'present_question' => [
                    [
                        'question' => 'If the lab {a1} unlimited funding, would its researchers {a2} breakthroughs sooner?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'had',
                                'options' => ['had', 'has', 'have'],
                                'verb_hint' => 'have',
                            ],
                            'a2' => [
                                'answer' => 'deliver',
                                'options' => ['deliver', 'delivered', 'delivering'],
                                'verb_hint' => 'deliver',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If your firm {a1} a bilingual workforce, would it {a2} the Latin American market?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'employed',
                                'options' => ['employed', 'employs', 'employ'],
                                'verb_hint' => 'employ',
                            ],
                            'a2' => [
                                'answer' => 'dominate',
                                'options' => ['dominate', 'dominated', 'dominating'],
                                'verb_hint' => 'dominate',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the negotiators {a1} more transparency, would the talks {a2} more swiftly?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'demanded',
                                'options' => ['demanded', 'demands', 'demand'],
                                'verb_hint' => 'demand',
                            ],
                            'a2' => [
                                'answer' => 'progress',
                                'options' => ['progress', 'progressed', 'progressing'],
                                'verb_hint' => 'progress',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the archive {a1} more volunteers, would they {a2} the fragile manuscripts?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'recruited',
                                'options' => ['recruited', 'recruits', 'recruit'],
                                'verb_hint' => 'recruit',
                            ],
                            'a2' => [
                                'answer' => 'preserve',
                                'options' => ['preserve', 'preserved', 'preserving'],
                                'verb_hint' => 'preserve',
                            ],
                        ],
                    ],
                ],
                'present_negative' => [
                    [
                        'question' => 'If I {a1} tied to deadlines, I wouldn\'t postpone travel.',
                        'markers' => [
                            'a1' => [
                                'answer' => "weren't",
                                'options' => ["weren't", "wasn't", "hadn't been"],
                                'verb_hint' => 'not be',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the analysts {a1} proprietary models, they wouldn\'t guard their code.',
                        'markers' => [
                            'a1' => [
                                'answer' => "didn't rely",
                                'options' => ["didn't rely", "don't rely", "wouldn't rely"],
                                'verb_hint' => 'not rely',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If our publisher {a1} quarterly profits, it wouldn\'t kill bold ideas.',
                        'markers' => [
                            'a1' => [
                                'answer' => "didn't chase",
                                'options' => ["didn't chase", "doesn't chase", "wouldn't chase"],
                                'verb_hint' => 'not chase',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If you {a1} so defensive, you wouldn\'t reject feedback.',
                        'markers' => [
                            'a1' => [
                                'answer' => "didn't feel",
                                'options' => ["didn't feel", "don't feel", "wouldn't feel"],
                                'verb_hint' => 'not feel',
                            ],
                        ],
                    ],
                ],
                'past_question' => [
                    [
                        'question' => 'If the task force had issued the alert, would the residents {a1} sooner?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'have evacuated',
                                'options' => ['have evacuated', 'evacuate', 'evacuated'],
                                'verb_hint' => 'evacuate',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If we had coded automated tests, would we {a1} the outage?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'have avoided',
                                'options' => ['have avoided', 'avoid', 'avoided'],
                                'verb_hint' => 'avoid',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the playwright had accepted edits, would the critics {a1} differently?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'have responded',
                                'options' => ['have responded', 'respond', 'responded'],
                                'verb_hint' => 'respond',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the explorers had carried more supplies, would they {a1} another week?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'have survived',
                                'options' => ['have survived', 'survive', 'survived'],
                                'verb_hint' => 'survive',
                            ],
                        ],
                    ],
                ],
                'past_negative' => [
                    [
                        'question' => 'If I {a1} the encryption keys, the breach wouldn\'t have happened.',
                        'markers' => [
                            'a1' => [
                                'answer' => "hadn't shared",
                                'options' => ["hadn't shared", "didn't share", "haven't shared"],
                                'verb_hint' => 'not share',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the agency had vetted the ad, the brand {a1} backlash.',
                        'markers' => [
                            'a1' => [
                                'answer' => "wouldn't have faced",
                                'options' => ["wouldn't have faced", "wouldn't face", "won't face"],
                                'verb_hint' => 'not face',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If our researchers had tracked anomalies, they {a1} the dataset.',
                        'markers' => [
                            'a1' => [
                                'answer' => "wouldn't have corrupted",
                                'options' => ["wouldn't have corrupted", "wouldn't corrupt", "won't corrupt"],
                                'verb_hint' => 'not corrupt',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the pilots {a1} the storm warnings, the flight wouldn\'t have been diverted.',
                        'markers' => [
                            'a1' => [
                                'answer' => "hadn't overridden",
                                'options' => ["hadn't overridden", "didn't override", "haven't overridden"],
                                'verb_hint' => 'not override',
                            ],
                        ],
                    ],
                ],
            ],
            'C2' => [
                'future_question' => [
                    [
                        'question' => 'If the tribunal {a1} the appeal tomorrow, will the regulators {a2} the suspension or {a3} further sanctions?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'hears',
                                'options' => ['hears', 'hear', 'heard'],
                                'verb_hint' => 'hear',
                            ],
                            'a2' => [
                                'answer' => 'lift',
                                'options' => ['lift', 'lifts', 'lifted'],
                                'verb_hint' => 'lift',
                            ],
                            'a3' => [
                                'answer' => 'impose',
                                'options' => ['impose', 'imposes', 'imposed'],
                                'verb_hint' => 'impose',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If your consortium {a1} the final patent, will investors {a2} fresh capital and {a3} the valuation?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'secures',
                                'options' => ['secures', 'secure', 'secured'],
                                'verb_hint' => 'secure',
                            ],
                            'a2' => [
                                'answer' => 'commit',
                                'options' => ['commit', 'commits', 'committed'],
                                'verb_hint' => 'commit',
                            ],
                            'a3' => [
                                'answer' => 'boost',
                                'options' => ['boost', 'boosts', 'boosted'],
                                'verb_hint' => 'boost',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the orchestra {a1} the premiere flawlessly, will critics {a2} rave reviews and {a3} our reputation?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'delivers',
                                'options' => ['delivers', 'deliver', 'delivered'],
                                'verb_hint' => 'deliver',
                            ],
                            'a2' => [
                                'answer' => 'publish',
                                'options' => ['publish', 'publishes', 'published'],
                                'verb_hint' => 'publish',
                            ],
                            'a3' => [
                                'answer' => 'enhance',
                                'options' => ['enhance', 'enhances', 'enhanced'],
                                'verb_hint' => 'enhance',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If we {a1} the emergency blueprint in March, will the ministry {a2} rapid funding or {a3} another audit?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'submit',
                                'options' => ['submit', 'submits', 'submitted'],
                                'verb_hint' => 'submit',
                            ],
                            'a2' => [
                                'answer' => 'release',
                                'options' => ['release', 'releases', 'released'],
                                'verb_hint' => 'release',
                            ],
                            'a3' => [
                                'answer' => 'order',
                                'options' => ['order', 'orders', 'ordered'],
                                'verb_hint' => 'order',
                            ],
                        ],
                    ],
                ],
                'future_negative' => [
                    [
                        'question' => 'If the arbitration {a1} before July, we {a2} the merger or {a3} staff transfers.',
                        'markers' => [
                            'a1' => [
                                'answer' => "doesn't conclude",
                                'options' => ["doesn't conclude", 'concludes', 'concluded'],
                                'verb_hint' => 'not conclude',
                            ],
                            'a2' => [
                                'answer' => "won't finalize",
                                'options' => ["won't finalize", "don't finalize", "didn't finalize"],
                                'verb_hint' => 'not finalize',
                            ],
                            'a3' => [
                                'answer' => "won't initiate",
                                'options' => ["won't initiate", "don't initiate", "didn't initiate"],
                                'verb_hint' => 'not initiate',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the expedition {a1} safe weather windows, the crew {a2} the ascent and {a3} a new route.',
                        'markers' => [
                            'a1' => [
                                'answer' => "doesn't find",
                                'options' => ["doesn't find", 'finds', 'found'],
                                'verb_hint' => 'not find',
                            ],
                            'a2' => [
                                'answer' => "won't attempt",
                                'options' => ["won't attempt", "don't attempt", "didn't attempt"],
                                'verb_hint' => 'not attempt',
                            ],
                            'a3' => [
                                'answer' => "won't map",
                                'options' => ["won't map", "don't map", "didn't map"],
                                'verb_hint' => 'not map',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If our donors {a1} the transparency report, we {a2} promised grants nor {a3} the scholarship fund.',
                        'markers' => [
                            'a1' => [
                                'answer' => 'reject',
                                'options' => ['reject', 'rejects', 'rejected'],
                                'verb_hint' => 'reject',
                            ],
                            'a2' => [
                                'answer' => "won't receive",
                                'options' => ["won't receive", "don't receive", "didn't receive"],
                                'verb_hint' => 'not receive',
                            ],
                            'a3' => [
                                'answer' => "won't expand",
                                'options' => ["won't expand", "don't expand", "didn't expand"],
                                'verb_hint' => 'not expand',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If I {a1} the cyber audit today, the board {a2} interim safeguards and {a3} the launch window.',
                        'markers' => [
                            'a1' => [
                                'answer' => "don't schedule",
                                'options' => ["don't schedule", 'schedule', 'scheduled'],
                                'verb_hint' => 'not schedule',
                            ],
                            'a2' => [
                                'answer' => "won't approve",
                                'options' => ["won't approve", "don't approve", "didn't approve"],
                                'verb_hint' => 'not approve',
                            ],
                            'a3' => [
                                'answer' => "won't extend",
                                'options' => ["won't extend", "don't extend", "didn't extend"],
                                'verb_hint' => 'not extend',
                            ],
                        ],
                    ],
                ],
                'present_question' => [
                    [
                        'question' => 'If the legislature {a1} sweeping reforms, would lobbyists {a2} their strategy or {a3} the proposal?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'passed',
                                'options' => ['passed', 'passes', 'pass'],
                                'verb_hint' => 'pass',
                            ],
                            'a2' => [
                                'answer' => 'revise',
                                'options' => ['revise', 'revised', 'revising'],
                                'verb_hint' => 'revise',
                            ],
                            'a3' => [
                                'answer' => 'oppose',
                                'options' => ['oppose', 'opposed', 'opposing'],
                                'verb_hint' => 'oppose',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If your lab {a1} quantum processors, would you {a2} commercial partners and {a3} new markets?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'developed',
                                'options' => ['developed', 'develops', 'develop'],
                                'verb_hint' => 'develop',
                            ],
                            'a2' => [
                                'answer' => 'attract',
                                'options' => ['attract', 'attracted', 'attracting'],
                                'verb_hint' => 'attract',
                            ],
                            'a3' => [
                                'answer' => 'enter',
                                'options' => ['enter', 'entered', 'entering'],
                                'verb_hint' => 'enter',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the museum {a1} indefinite loans, would curators {a2} riskier exhibits or {a3} their catalogue?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'secured',
                                'options' => ['secured', 'secures', 'secure'],
                                'verb_hint' => 'secure',
                            ],
                            'a2' => [
                                'answer' => 'stage',
                                'options' => ['stage', 'staged', 'staging'],
                                'verb_hint' => 'stage',
                            ],
                            'a3' => [
                                'answer' => 'rethink',
                                'options' => ['rethink', 'rethought', 'rethinking'],
                                'verb_hint' => 'rethink',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If we {a1} a multilingual newsroom, would readers {a2} premium subscriptions and {a3} loyalty?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'built',
                                'options' => ['built', 'build', 'builds'],
                                'verb_hint' => 'build',
                            ],
                            'a2' => [
                                'answer' => 'purchase',
                                'options' => ['purchase', 'purchased', 'purchasing'],
                                'verb_hint' => 'purchase',
                            ],
                            'a3' => [
                                'answer' => 'sustain',
                                'options' => ['sustain', 'sustained', 'sustaining'],
                                'verb_hint' => 'sustain',
                            ],
                        ],
                    ],
                ],
                'present_negative' => [
                    [
                        'question' => 'If I {a1} tied to quarterly reports, I {a2} ambitious pilots or {a3} experimental teams.',
                        'markers' => [
                            'a1' => [
                                'answer' => "weren't",
                                'options' => ["weren't", "wasn't", "hadn't been"],
                                'verb_hint' => 'not be',
                            ],
                            'a2' => [
                                'answer' => "wouldn't cancel",
                                'options' => ["wouldn't cancel", "won't cancel", "would cancel"],
                                'verb_hint' => 'not cancel',
                            ],
                            'a3' => [
                                'answer' => "wouldn't dissolve",
                                'options' => ["wouldn't dissolve", "won't dissolve", "would dissolve"],
                                'verb_hint' => 'not dissolve',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the agency {a1} obsessed with metrics, it {a2} daring campaigns nor {a3} every risk.',
                        'markers' => [
                            'a1' => [
                                'answer' => "didn't stay",
                                'options' => ["didn't stay", "doesn't stay", "wouldn't stay"],
                                'verb_hint' => 'not stay',
                            ],
                            'a2' => [
                                'answer' => "wouldn't reject",
                                'options' => ["wouldn't reject", "won't reject", "would reject"],
                                'verb_hint' => 'not reject',
                            ],
                            'a3' => [
                                'answer' => "wouldn't micromanage",
                                'options' => ["wouldn't micromanage", "won't micromanage", "would micromanage"],
                                'verb_hint' => 'not micromanage',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If your partners {a1} purely on profit, they {a2} community grants or {a3} local artists.',
                        'markers' => [
                            'a1' => [
                                'answer' => "didn't focus",
                                'options' => ["didn't focus", "don't focus", "wouldn't focus"],
                                'verb_hint' => 'not focus',
                            ],
                            'a2' => [
                                'answer' => "wouldn't cut",
                                'options' => ["wouldn't cut", "won't cut", "would cut"],
                                'verb_hint' => 'not cut',
                            ],
                            'a3' => [
                                'answer' => "wouldn't overlook",
                                'options' => ["wouldn't overlook", "won't overlook", "would overlook"],
                                'verb_hint' => 'not overlook',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the think tank {a1} beholden to donors, it {a2} bold research or {a3} controversial findings.',
                        'markers' => [
                            'a1' => [
                                'answer' => "weren't",
                                'options' => ["weren't", "wasn't", "hadn't been"],
                                'verb_hint' => 'not be',
                            ],
                            'a2' => [
                                'answer' => "wouldn't suppress",
                                'options' => ["wouldn't suppress", "won't suppress", "would suppress"],
                                'verb_hint' => 'not suppress',
                            ],
                            'a3' => [
                                'answer' => "wouldn't soften",
                                'options' => ["wouldn't soften", "won't soften", "would soften"],
                                'verb_hint' => 'not soften',
                            ],
                        ],
                    ],
                ],
                'past_question' => [
                    [
                        'question' => 'If the diplomats had coordinated sooner, would they {a1} the ceasefire, {a2} new concessions, or {a3} the summit?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'have secured',
                                'options' => ['have secured', 'secure', 'secured'],
                                'verb_hint' => 'secure',
                            ],
                            'a2' => [
                                'answer' => 'have extracted',
                                'options' => ['have extracted', 'extract', 'extracted'],
                                'verb_hint' => 'extract',
                            ],
                            'a3' => [
                                'answer' => 'have extended',
                                'options' => ['have extended', 'extend', 'extended'],
                                'verb_hint' => 'extend',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If we had archived the raw data, would the reviewers {a1} the findings or {a2} the methodology?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'have endorsed',
                                'options' => ['have endorsed', 'endorse', 'endorsed'],
                                'verb_hint' => 'endorse',
                            ],
                            'a2' => [
                                'answer' => 'have questioned',
                                'options' => ['have questioned', 'question', 'questioned'],
                                'verb_hint' => 'question',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the artist had retained control, would publishers {a1} the series or {a2} the ending?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'have continued',
                                'options' => ['have continued', 'continue', 'continued'],
                                'verb_hint' => 'continue',
                            ],
                            'a2' => [
                                'answer' => 'have altered',
                                'options' => ['have altered', 'alter', 'altered'],
                                'verb_hint' => 'alter',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the expedition had secured mapping rights, would it {a1} the valley or {a2} rival claims?',
                        'markers' => [
                            'a1' => [
                                'answer' => 'have charted',
                                'options' => ['have charted', 'chart', 'charted'],
                                'verb_hint' => 'chart',
                            ],
                            'a2' => [
                                'answer' => 'have prevented',
                                'options' => ['have prevented', 'prevent', 'prevented'],
                                'verb_hint' => 'prevent',
                            ],
                        ],
                    ],
                ],
                'past_negative' => [
                    [
                        'question' => 'If I {a1} the briefing memo, the council {a2} the proposal nor {a3} our timetable.',
                        'markers' => [
                            'a1' => [
                                'answer' => "hadn't misplaced",
                                'options' => ["hadn't misplaced", "didn't misplace", "haven't misplaced"],
                                'verb_hint' => 'not misplace',
                            ],
                            'a2' => [
                                'answer' => "wouldn't have delayed",
                                'options' => ["wouldn't have delayed", "wouldn't delay", "won't delay"],
                                'verb_hint' => 'not delay',
                            ],
                            'a3' => [
                                'answer' => "wouldn't have questioned",
                                'options' => ["wouldn't have questioned", "wouldn't question", "won't question"],
                                'verb_hint' => 'not question',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the network {a1} maintenance alerts, the engineers {a2} service or {a3} customer trust.',
                        'markers' => [
                            'a1' => [
                                'answer' => "hadn't ignored",
                                'options' => ["hadn't ignored", "didn't ignore", "haven't ignored"],
                                'verb_hint' => 'not ignore',
                            ],
                            'a2' => [
                                'answer' => "wouldn't have lost",
                                'options' => ["wouldn't have lost", "wouldn't lose", "won't lose"],
                                'verb_hint' => 'not lose',
                            ],
                            'a3' => [
                                'answer' => "wouldn't have damaged",
                                'options' => ["wouldn't have damaged", "wouldn't damage", "won't damage"],
                                'verb_hint' => 'not damage',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the editors {a1} the leak, the newspaper {a2} lawsuits nor {a3} its sources.',
                        'markers' => [
                            'a1' => [
                                'answer' => "hadn't published",
                                'options' => ["hadn't published", "didn't publish", "haven't published"],
                                'verb_hint' => 'not publish',
                            ],
                            'a2' => [
                                'answer' => "wouldn't have faced",
                                'options' => ["wouldn't have faced", "wouldn't face", "won't face"],
                                'verb_hint' => 'not face',
                            ],
                            'a3' => [
                                'answer' => "wouldn't have compromised",
                                'options' => ["wouldn't have compromised", "wouldn't compromise", "won't compromise"],
                                'verb_hint' => 'not compromise',
                            ],
                        ],
                    ],
                    [
                        'question' => 'If the pilots {a1} the safety ceiling, the mission {a2} mid-flight or {a3} its payload.',
                        'markers' => [
                            'a1' => [
                                'answer' => "hadn't broken",
                                'options' => ["hadn't broken", "didn't break", "haven't broken"],
                                'verb_hint' => 'not break',
                            ],
                            'a2' => [
                                'answer' => "wouldn't have aborted",
                                'options' => ["wouldn't have aborted", "wouldn't abort", "won't abort"],
                                'verb_hint' => 'not abort',
                            ],
                            'a3' => [
                                'answer' => "wouldn't have jettisoned",
                                'options' => ["wouldn't have jettisoned", "wouldn't jettison", "won't jettison"],
                                'verb_hint' => 'not jettison',
                            ],
                        ],
                    ],
                ],
            ],
            // Additional levels will be appended here.
        ];
    }
}
