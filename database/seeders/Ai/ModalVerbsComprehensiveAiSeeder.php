<?php

namespace Database\Seeders\Ai;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class ModalVerbsComprehensiveAiSeeder extends QuestionSeeder
{
    private array $levelDifficulty = [
        'A1' => 1,
        'A2' => 2,
        'B1' => 3,
        'B2' => 4,
        'C1' => 5,
        'C2' => 5,
    ];

    private array $themes = [
        'ability' => [
            'label' => 'Modal Ability and Possibility',
            'tag' => 'Modal Ability Focus',
        ],
        'permission' => [
            'label' => 'Modal Permission and Requests',
            'tag' => 'Modal Permission Focus',
        ],
        'obligation' => [
            'label' => 'Modal Obligation and Necessity',
            'tag' => 'Modal Obligation Focus',
        ],
        'advice' => [
            'label' => 'Modal Advice and Suggestions',
            'tag' => 'Modal Advice Focus',
        ],
        'deduction' => [
            'label' => 'Modal Probability and Deduction',
            'tag' => 'Modal Deduction Focus',
        ],
    ];

    private array $typeConfig = [
        'question' => [
            'label' => 'Interrogative Focus',
            'tag' => 'Modal Question Form',
        ],
        'negative' => [
            'label' => 'Negative Focus',
            'tag' => 'Modal Negative Form',
        ],
        'past' => [
            'label' => 'Past Statement Focus',
            'tag' => 'Modal Past Usage',
        ],
        'present' => [
            'label' => 'Present Statement Focus',
            'tag' => 'Modal Present Usage',
        ],
        'future' => [
            'label' => 'Future Statement Focus',
            'tag' => 'Modal Future Usage',
        ],
    ];

    private array $tenseTagMap = [
        'past' => 'Tense: Past',
        'present' => 'Tense: Present',
        'future' => 'Tense: Future',
    ];

    private array $modalTagConfig = [
        'can_could' => [
            'name' => 'Can / Could',
            'keywords' => [
                'can',
                'cannot',
                "can't",
                'can have',
                "can't have",
                'could',
                "couldn't",
                'could have',
                "couldn't have",
            ],
        ],
        'may_might' => [
            'name' => 'May / Might',
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
            'name' => 'Must / Have to',
            'keywords' => [
                'must',
                "mustn't",
                'must have',
                "mustn't have",
                'have to',
                'has to',
                'had to',
                "don't have to",
                "doesn't have to",
                "didn't have to",
            ],
        ],
        'need_need_to' => [
            'name' => 'Need / Need to',
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
        'should_ought_to' => [
            'name' => 'Should / Ought to',
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
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Modal Verbs Comprehensive AI Practice'])->id;

        $sourceMap = [];
        foreach ($this->themes as $themeKey => $themeData) {
            foreach ($this->typeConfig as $typeKey => $typeData) {
                $sourceName = sprintf(
                    'AI Modals %s - %s',
                    $themeData['label'],
                    $typeData['label']
                );

                $sourceMap[$themeKey][$typeKey] = Source::firstOrCreate(['name' => $sourceName])->id;
            }
        }

        $themeTagIds = [];
        foreach ($this->themes as $themeKey => $themeData) {
            $themeTagIds[$themeKey] = Tag::firstOrCreate(
                ['name' => $themeData['tag']],
                ['category' => 'English Grammar Theme']
            )->id;
        }

        $typeTagIds = [];
        foreach ($this->typeConfig as $typeKey => $typeData) {
            $typeTagIds[$typeKey] = Tag::firstOrCreate(
                ['name' => $typeData['tag']],
                ['category' => 'English Grammar Structure']
            )->id;
        }

        $tenseTagIds = [];
        foreach ($this->tenseTagMap as $tenseKey => $tagName) {
            $tenseTagIds[$tenseKey] = Tag::firstOrCreate(
                ['name' => $tagName],
                ['category' => 'English Grammar Tense']
            )->id;
        }

        $modalPairTagIds = [];
        foreach ($this->modalTagConfig as $modalKey => $modalData) {
            $modalPairTagIds[$modalKey] = Tag::firstOrCreate(
                ['name' => $modalData['name']],
                ['category' => 'English Grammar Modal Pair']
            )->id;
        }

        $modalsTagId = Tag::firstOrCreate(
            ['name' => 'Modal Verbs'],
            ['category' => 'English Grammar Theme']
        )->id;

        $questions = $this->buildQuestionBank();

        $items = [];
        $meta = [];

        foreach ($questions as $index => $entry) {
            $level = $entry['level'];
            $typeKey = $entry['type'];
            $themeKey = $entry['theme'];
            $tenseKey = $entry['tense'];

            $answers = [];
            $answersMap = [];
            $verbHints = [];
            $optionsPerMarker = [];
            $optionMarkerMap = [];

            foreach ($entry['markers'] as $marker => $markerData) {
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

            $tagIds = array_filter([
                $modalsTagId,
                $themeTagIds[$themeKey] ?? null,
                $typeTagIds[$typeKey] ?? null,
                $tenseTagIds[$tenseKey] ?? null,
            ]);

            $modalTagMatches = $this->determineModalTagIds($entry, $modalPairTagIds);
            $tagIds = array_values(array_unique(array_merge($tagIds, $modalTagMatches)));

            $uuid = $this->generateQuestionUuid($level, $themeKey, $typeKey, $index + 1);

            $items[] = [
                'uuid' => $uuid,
                'question' => $entry['question'],
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$level] ?? 3,
                'source_id' => $sourceMap[$themeKey][$typeKey] ?? reset($sourceMap[$themeKey]),
                'flag' => 2,
                'level' => $level,
                'tag_ids' => $tagIds,
                'answers' => $answers,
                'options' => $flattenedOptions,
                'variants' => [$entry['question']],
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

        foreach ($levels as $level => $entries) {
            foreach ($entries as $entry) {
                $questions[] = array_merge($entry, ['level' => $level]);
            }
        }

        return $questions;
    }

    private function determineModalTagIds(array $entry, array $modalPairTagIds): array
    {
        $answers = [];

        foreach ($entry['markers'] ?? [] as $markerData) {
            $answer = trim((string) ($markerData['answer'] ?? ''));

            if ($answer === '') {
                continue;
            }

            $answers[] = mb_strtolower($answer);
        }

        if ($answers === []) {
            return [];
        }

        $matched = [];

        foreach ($this->modalTagConfig as $modalKey => $modalConfig) {
            $keywords = array_map('mb_strtolower', $modalConfig['keywords'] ?? []);

            if ($keywords === []) {
                continue;
            }

            if ($this->answersMatchKeywords($answers, $keywords) && isset($modalPairTagIds[$modalKey])) {
                $matched[] = $modalPairTagIds[$modalKey];
            }
        }

        return array_values(array_unique($matched));
    }

    private function answersMatchKeywords(array $answers, array $keywords): bool
    {
        foreach ($answers as $answer) {
            foreach ($keywords as $keyword) {
                $keyword = trim($keyword);

                if ($keyword === '') {
                    continue;
                }

                if ($this->keywordMatches($answer, $keyword)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function keywordMatches(string $text, string $keyword): bool
    {
        if (str_contains($keyword, ' ')) {
            return str_contains($text, $keyword);
        }

        $pattern = '/\\b' . preg_quote($keyword, '/') . '\\b/u';

        if (preg_match($pattern, $text) === 1) {
            return true;
        }

        return str_contains($text, $keyword);
    }

    private function getLevelData(): array
    {
        return [
            'A1' => [
                [
                    'theme' => 'ability',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '_____ your sister ride a bike without training wheels yet?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Can',
                            'options' => ['Can', 'Could', 'Must'],
                            'verb_hint' => 'she',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '_____ we leave the classroom now, teacher?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'May',
                            'options' => ['May', 'Must', 'Should'],
                            'verb_hint' => 'we',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'question',
                    'tense' => 'future',
                    'question' => '_____ we finish the homework before the movie tonight?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Must',
                            'options' => ['Must', 'Can', 'Might'],
                            'verb_hint' => 'we',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '_____ I take an umbrella to school today?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Should',
                            'options' => ['Should', 'Must', 'Can'],
                            'verb_hint' => 'I',
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '_____ this be the right bus to the zoo?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Could',
                            'options' => ['Could', 'Must', 'Should'],
                            'verb_hint' => 'it',
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'question',
                    'tense' => 'past',
                    'question' => '_____ your grandfather swim when he was five?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Could',
                            'options' => ['Could', 'Can', 'Should'],
                            'verb_hint' => 'he',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'The sign says we _____ feed the ducks.',
                    'markers' => [
                        'a1' => [
                            'answer' => "mustn't",
                            'options' => ["mustn't", "shouldn't", "can't"],
                            'verb_hint' => 'not we',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'You _____ wear a tie to this picnic; it is casual.',
                    'markers' => [
                        'a1' => [
                            'answer' => "don't have to",
                            'options' => ["don't have to", "mustn't", "shouldn't"],
                            'verb_hint' => 'not you',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'You _____ eat so many sweets before dinner.',
                    'markers' => [
                        'a1' => [
                            'answer' => "shouldn't",
                            'options' => ["shouldn't", "mustn't", "might not"],
                            'verb_hint' => 'not you',
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'This _____ be Nina’s coat; it is too big for her.',
                    'markers' => [
                        'a1' => [
                            'answer' => "can't",
                            'options' => ["can't", "might not", "shouldn't"],
                            'verb_hint' => 'not it',
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'negative',
                    'tense' => 'past',
                    'question' => 'Lily _____ reach the top shelf yesterday, so I helped.',
                    'markers' => [
                        'a1' => [
                            'answer' => "couldn't",
                            'options' => ["couldn't", "mustn't", "shouldn't"],
                            'verb_hint' => 'not she',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'negative',
                    'tense' => 'future',
                    'question' => 'We _____ stay out late on school nights next week.',
                    'markers' => [
                        'a1' => [
                            'answer' => "won't be allowed to",
                            'options' => ["won't be allowed to", "shouldn't", "might not"],
                            'verb_hint' => 'not we',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'Yesterday I _____ help my dad fix the fence.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'needed to',
                            'options' => ['needed to', 'had to', 'should'],
                            'verb_hint' => 'I',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'Grandma said we _____ call her when we got home.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'could', 'may'],
                            'verb_hint' => 'we',
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'When the lights went out, we still _____ find the door easily.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'could',
                            'options' => ['could', 'can', 'may'],
                            'verb_hint' => 'we',
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'The door was open; someone _____ left it after lunch.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must have',
                            'options' => ['must have', 'might', 'should'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'At this museum you _____ take photos without flash.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'may',
                            'options' => ['may', 'must', 'should'],
                            'verb_hint' => 'you',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'We _____ wear helmets when we ride our bikes in the park.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'may', 'might'],
                            'verb_hint' => 'we',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'You _____ drink water during the game to stay hydrated.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'may'],
                            'verb_hint' => 'you',
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'The sky is dark; it _____ rain soon.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'might',
                            'options' => ['might', 'must', 'can'],
                            'verb_hint' => 'it',
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'Next year Emma _____ join the advanced dance group.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will be able to',
                            'options' => ['will be able to', 'may', 'must'],
                            'verb_hint' => 'she',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'After the test we _____ go outside to play.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will be allowed to',
                            'options' => ['will be allowed to', 'might', 'have to'],
                            'verb_hint' => 'we',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'We _____ clean the classroom after the art project tomorrow.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will have to',
                            'options' => ['will have to', 'might', 'could'],
                            'verb_hint' => 'we',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'If it is sunny tomorrow, you _____ wear a hat.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'may'],
                            'verb_hint' => 'you',
                        ],
                    ],
                ],
                
            ],
            'A2' => [
                [
                    'theme' => 'ability',
                    'type' => 'question',
                    'tense' => 'future',
                    'question' => '_____ you _____ join the video call if the bus is delayed?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Will',
                            'options' => ['Will', 'Should', 'Must'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'have to', 'ought to'],
                            'verb_hint' => 'you',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '_____ we _____ invite a guest to the members-only lounge?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'May',
                            'options' => ['May', 'Must', 'Should'],
                            'verb_hint' => 'we',
                        ],
                        'a2' => [
                            'answer' => 'also',
                            'options' => ['also', 'only', 'never'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '_____ I _____ submit the report before lunch or is the afternoon fine?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Do',
                            'options' => ['Do', 'Must', 'Should'],
                            'verb_hint' => 'I',
                        ],
                        'a2' => [
                            'answer' => 'need to',
                            'options' => ['need to', 'ought to', 'be able to'],
                            'verb_hint' => 'I',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'question',
                    'tense' => 'future',
                    'question' => '_____ we _____ bring anything special to the networking dinner tomorrow?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Should',
                            'options' => ['Should', 'Must', 'Could'],
                            'verb_hint' => 'we',
                        ],
                        'a2' => [
                            'answer' => 'probably',
                            'options' => ['probably', 'never', 'hardly'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'question',
                    'tense' => 'past',
                    'question' => '_____ this _____ been the train that arrived early this morning?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Could',
                            'options' => ['Could', 'Must', 'Should'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '_____ your colleagues _____ solve this coding issue without the documentation?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Can',
                            'options' => ['Can', 'Should', 'Must'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'already',
                            'options' => ['already', 'barely', 'never'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'Staff _____ _____ use personal email accounts during client meetings.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'should', 'could'],
                            'verb_hint' => 'not they',
                        ],
                        'a2' => [
                            'answer' => 'not',
                            'options' => ['not', 'ever', 'always'],
                            'verb_hint' => 'not they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'negative',
                    'tense' => 'future',
                    'question' => 'You _____ _____ attend the optional workshop next Friday.',
                    'markers' => [
                        'a1' => [
                            'answer' => "won't",
                            'options' => ["won't", 'must', 'should'],
                            'verb_hint' => 'not you',
                        ],
                        'a2' => [
                            'answer' => 'have to',
                            'options' => ['have to', 'ought to', 'need to'],
                            'verb_hint' => 'not you',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'They _____ _____ rely on last year’s data for this forecast.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'may'],
                            'verb_hint' => 'not they',
                        ],
                        'a2' => [
                            'answer' => 'not',
                            'options' => ['not', 'always', 'rarely'],
                            'verb_hint' => 'not they',
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'negative',
                    'tense' => 'past',
                    'question' => 'This _____ _____ been Paul’s laptop; his is silver, not black.',
                    'markers' => [
                        'a1' => [
                            'answer' => "can't",
                            'options' => ["can't", "shouldn't", 'might not'],
                            'verb_hint' => 'not it',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'negative',
                    'tense' => 'future',
                    'question' => 'Maria _____ _____ attend the rehearsal because of her flight schedule.',
                    'markers' => [
                        'a1' => [
                            'answer' => "won't",
                            'options' => ["won't", 'should', 'may'],
                            'verb_hint' => 'not she',
                        ],
                        'a2' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'have to', 'need to'],
                            'verb_hint' => 'not she',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'Last year interns _____ _____ work remotely on Fridays.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'were',
                            'options' => ['were', 'could', 'must'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'allowed to',
                            'options' => ['allowed to', 'supposed to', 'able to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'We _____ _____ complete safety training before entering the lab.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'had',
                            'options' => ['had', 'must', 'should'],
                            'verb_hint' => 'we',
                        ],
                        'a2' => [
                            'answer' => 'to',
                            'options' => ['to', 'have to', 'ought to'],
                            'verb_hint' => 'we',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'She _____ _____ talk to the mentor before accepting the offer.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'could', 'may'],
                            'verb_hint' => 'she',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => 'she',
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'From the footprints, the hikers _____ _____ taken the northern trail.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'might', 'should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Engineers _____ _____ adapt the prototype quickly when feedback arrives.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'might', 'may'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'need to', 'ought to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Guests _____ _____ bring small pets into the outdoor café area.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'may',
                            'options' => ['may', 'must', 'should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'now',
                            'options' => ['now', 'never', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'All drivers _____ _____ keep their documents in the vehicle.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'have',
                            'options' => ['have', 'should', 'might'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'to',
                            'options' => ['to', 'able to', 'ought to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'You _____ _____ break big tasks into smaller steps for clarity.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'could'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'always',
                            'options' => ['always', 'never', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'With all the lights off, they _____ _____ already left the office.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'might',
                            'options' => ['might', 'must', 'can'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'By winter we _____ _____ operate the new machinery safely.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'should', 'might'],
                            'verb_hint' => 'we',
                        ],
                        'a2' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'have to', 'need to'],
                            'verb_hint' => 'we',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'Residents _____ _____ extend their leases after the renovation.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'should', 'might'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'be allowed to',
                            'options' => ['be allowed to', 'have to', 'need to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'Next quarter we _____ _____ report progress every Monday.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'might', 'could'],
                            'verb_hint' => 'we',
                        ],
                        'a2' => [
                            'answer' => 'have to',
                            'options' => ['have to', 'be able to', 'ought to'],
                            'verb_hint' => 'we',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'If the forecast changes, you _____ _____ adjust the schedule.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'could'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'quickly',
                            'options' => ['quickly', 'rarely', 'never'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                
            ],
            'B1' => [
                [
                    'theme' => 'ability',
                    'type' => 'question',
                    'tense' => 'future',
                    'question' => '_____ your team _____ _____ a backup plan if negotiations fail?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Will',
                            'options' => ['Will', 'Should', 'Must'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'have to', 'need to'],
                            'verb_hint' => 'they',
                        ],
                        'a3' => [
                            'answer' => 'draft',
                            'options' => ['draft', 'decide', 'avoid'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '_____ we _____ _____ use the auditorium for an unscheduled rehearsal?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'May',
                            'options' => ['May', 'Must', 'Should'],
                            'verb_hint' => 'we',
                        ],
                        'a2' => [
                            'answer' => 'please',
                            'options' => ['please', 'never', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'still',
                            'options' => ['still', 'already', 'hardly'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'question',
                    'tense' => 'past',
                    'question' => '_____ the contractors _____ _____ finish the wiring by Friday?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Did',
                            'options' => ['Did', 'Must', 'Should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'need to',
                            'options' => ['need to', 'have to', 'ought to'],
                            'verb_hint' => 'they',
                        ],
                        'a3' => [
                            'answer' => 'actually',
                            'options' => ['actually', 'barely', 'never'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '_____ I _____ _____ the itinerary so the guests know what to expect?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Should',
                            'options' => ['Should', 'Must', 'Could'],
                            'verb_hint' => 'I',
                        ],
                        'a2' => [
                            'answer' => 'go over',
                            'options' => ['go over', 'delay', 'skip'],
                            'verb_hint' => 'I',
                        ],
                        'a3' => [
                            'answer' => 'again',
                            'options' => ['again', 'barely', 'never'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '_____ this _____ _____ the server that keeps crashing?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Could',
                            'options' => ['Could', 'Must', 'Should'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'possibly',
                            'options' => ['possibly', 'hardly', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'be',
                            'options' => ['be', 'have', 'do'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'question',
                    'tense' => 'past',
                    'question' => '_____ your mentor _____ _____ new software so quickly last quarter?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'How',
                            'options' => ['How', 'Could', 'Should'],
                            'verb_hint' => 'he',
                        ],
                        'a2' => [
                            'answer' => 'manage to',
                            'options' => ['manage to', 'need to', 'have to'],
                            'verb_hint' => 'he',
                        ],
                        'a3' => [
                            'answer' => 'learn',
                            'options' => ['learn', 'delay', 'refuse'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'negative',
                    'tense' => 'future',
                    'question' => 'Volunteers _____ _____ _____ enter the archive without supervision next week.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'should', 'might'],
                            'verb_hint' => 'not they',
                        ],
                        'a2' => [
                            'answer' => 'not',
                            'options' => ['not', 'ever', 'rarely'],
                            'verb_hint' => 'not they',
                        ],
                        'a3' => [
                            'answer' => 'be allowed to',
                            'options' => ['be allowed to', 'need to', 'have to'],
                            'verb_hint' => 'not they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'Consultants _____ _____ _____ send daily updates during the maintenance window.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'do',
                            'options' => ['do', 'must', 'should'],
                            'verb_hint' => 'not they',
                        ],
                        'a2' => [
                            'answer' => "n't",
                            'options' => ["n't", 'ever', 'always'],
                            'verb_hint' => 'not they',
                        ],
                        'a3' => [
                            'answer' => 'have to',
                            'options' => ['have to', 'ought to', 'need to'],
                            'verb_hint' => 'not they',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'negative',
                    'tense' => 'future',
                    'question' => 'You _____ _____ _____ underestimate the client’s expectations again.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'might'],
                            'verb_hint' => 'not you',
                        ],
                        'a2' => [
                            'answer' => 'absolutely',
                            'options' => ['absolutely', 'hardly', 'scarcely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'not',
                            'options' => ['not', 'ever', 'always'],
                            'verb_hint' => 'not you',
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'That explanation _____ _____ _____ correct because the logs disagree.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'cannot',
                            'options' => ['cannot', "shouldn't", 'might not'],
                            'verb_hint' => 'not it',
                        ],
                        'a2' => [
                            'answer' => 'possibly',
                            'options' => ['possibly', 'always', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'be',
                            'options' => ['be', 'have', 'do'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'negative',
                    'tense' => 'past',
                    'question' => 'They _____ _____ _____ reach consensus despite several mediation rounds.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'could',
                            'options' => ['could', 'should', 'must'],
                            'verb_hint' => 'not they',
                        ],
                        'a2' => [
                            'answer' => 'not',
                            'options' => ['not', 'ever', 'always'],
                            'verb_hint' => 'not they',
                        ],
                        'a3' => [
                            'answer' => 'manage to',
                            'options' => ['manage to', 'need to', 'have to'],
                            'verb_hint' => 'not they',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'During the pilot phase, analysts _____ _____ _____ access confidential files after hours.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'were',
                            'options' => ['were', 'could', 'must'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'temporarily',
                            'options' => ['temporarily', 'never', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'allowed to',
                            'options' => ['allowed to', 'supposed to', 'able to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'We _____ _____ _____ attend every stakeholder briefing last season.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'had',
                            'options' => ['had', 'must', 'should'],
                            'verb_hint' => 'we',
                        ],
                        'a2' => [
                            'answer' => 'to',
                            'options' => ['to', 'able to', 'need to'],
                            'verb_hint' => 'we',
                        ],
                        'a3' => [
                            'answer' => 'personally',
                            'options' => ['personally', 'rarely', 'never'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'You _____ _____ _____ double-check the invoices before authorizing them.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'could', 'may'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => 'you',
                        ],
                        'a3' => [
                            'answer' => 'already',
                            'options' => ['already', 'never', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'The timeline _____ _____ _____ adjusted earlier to meet the launch date.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'might', 'should'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => 'it',
                        ],
                        'a3' => [
                            'answer' => 'been',
                            'options' => ['been', 'done', 'set'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Our system _____ _____ _____ handle multilingual input smoothly now.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'might'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'have to', 'need to'],
                            'verb_hint' => 'it',
                        ],
                        'a3' => [
                            'answer' => 'consistently',
                            'options' => ['consistently', 'rarely', 'hardly'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Members _____ _____ _____ reserve collaboration rooms via the new app.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'may',
                            'options' => ['may', 'must', 'should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'now',
                            'options' => ['now', 'never', 'hardly'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'freely',
                            'options' => ['freely', 'rarely', 'barely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'All contractors _____ _____ _____ follow the updated safety protocol.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'should', 'could'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'at all times',
                            'options' => ['at all times', 'seldom', 'never'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'strictly',
                            'options' => ['strictly', 'casually', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'You _____ _____ _____ involve the support team before launching updates.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'might'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'always',
                            'options' => ['always', 'rarely', 'never'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'consult',
                            'options' => ['consult', 'ignore', 'postpone'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Given the empty parking lot, the committee _____ _____ _____ concluded early.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'might', 'could'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => 'they',
                        ],
                        'a3' => [
                            'answer' => 'already',
                            'options' => ['already', 'barely', 'never'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'With the new funding, the lab _____ _____ _____ develop custom sensors.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'might', 'should'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'have to', 'need to'],
                            'verb_hint' => 'it',
                        ],
                        'a3' => [
                            'answer' => 'independently',
                            'options' => ['independently', 'rarely', 'hesitantly'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'After accreditation, students _____ _____ _____ enroll in evening clinics.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'might', 'could'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'finally',
                            'options' => ['finally', 'hardly', 'never'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'be allowed to',
                            'options' => ['be allowed to', 'need to', 'have to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'Project leads _____ _____ _____ submit risk assessments every quarter.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'might', 'could'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'still',
                            'options' => ['still', 'barely', 'never'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'have to',
                            'options' => ['have to', 'be able to', 'need to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'If supply issues continue, you _____ _____ _____ diversify vendors quickly.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'might'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'probably',
                            'options' => ['probably', 'barely', 'never'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'plan to',
                            'options' => ['plan to', 'refuse to', 'delay to'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                
            ],
            'B2' => [
                [
                    'theme' => 'ability',
                    'type' => 'question',
                    'tense' => 'future',
                    'question' => '_____ the research unit _____ _____ prototype a solution within six weeks?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Will',
                            'options' => ['Will', 'Should', 'Must'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'still be able to',
                            'options' => ['still be able to', 'have to', 'need to'],
                            'verb_hint' => 'they',
                        ],
                        'a3' => [
                            'answer' => 'rapidly',
                            'options' => ['rapidly', 'rarely', 'reluctantly'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '_____ visiting fellows _____ _____ access the advanced analytics lab after hours?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'May',
                            'options' => ['May', 'Must', 'Should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'temporarily',
                            'options' => ['temporarily', 'hardly', 'never'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'be admitted to',
                            'options' => ['be admitted to', 'have to', 'need to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'question',
                    'tense' => 'future',
                    'question' => '_____ we _____ _____ escalate every variance to the board next quarter?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Will',
                            'options' => ['Will', 'Must', 'Should'],
                            'verb_hint' => 'we',
                        ],
                        'a2' => [
                            'answer' => 'have to',
                            'options' => ['have to', 'ought to', 'be able to'],
                            'verb_hint' => 'we',
                        ],
                        'a3' => [
                            'answer' => 'immediately',
                            'options' => ['immediately', 'rarely', 'slowly'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '_____ I _____ _____ stakeholders before locking the sprint goals?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Should',
                            'options' => ['Should', 'Must', 'Could'],
                            'verb_hint' => 'I',
                        ],
                        'a2' => [
                            'answer' => 'consult',
                            'options' => ['consult', 'sideline', 'delay'],
                            'verb_hint' => 'I',
                        ],
                        'a3' => [
                            'answer' => 'each time',
                            'options' => ['each time', 'rarely', 'hardly ever'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'question',
                    'tense' => 'past',
                    'question' => '_____ the auditors _____ _____ uncovered this discrepancy last year?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Could',
                            'options' => ['Could', 'Must', 'Should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'possibly',
                            'options' => ['possibly', 'barely', 'never'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '_____ your analysts _____ _____ adapt the model when the dataset doubles overnight?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Can',
                            'options' => ['Can', 'Should', 'Must'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'quickly',
                            'options' => ['quickly', 'rarely', 'reluctantly'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'retrain it',
                            'options' => ['retrain it', 'ignore it', 'delay it'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'Contractors _____ _____ _____ disclose client data outside secured channels.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'should', 'could'],
                            'verb_hint' => 'not they',
                        ],
                        'a2' => [
                            'answer' => 'never',
                            'options' => ['never', 'barely', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'be allowed to',
                            'options' => ['be allowed to', 'need to', 'have to'],
                            'verb_hint' => 'not they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'negative',
                    'tense' => 'future',
                    'question' => 'Our division _____ _____ _____ submit duplicate compliance reports next cycle.',
                    'markers' => [
                        'a1' => [
                            'answer' => "won't",
                            'options' => ["won't", 'must', 'should'],
                            'verb_hint' => 'not we',
                        ],
                        'a2' => [
                            'answer' => 'need to',
                            'options' => ['need to', 'have to', 'ought to'],
                            'verb_hint' => 'not we',
                        ],
                        'a3' => [
                            'answer' => 'anymore',
                            'options' => ['anymore', 'always', 'seldom'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'You _____ _____ _____ rely solely on intuition for these investment calls.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'might'],
                            'verb_hint' => 'not you',
                        ],
                        'a2' => [
                            'answer' => 'never',
                            'options' => ['never', 'rarely', 'hardly'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'again',
                            'options' => ['again', 'soon', 'always'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'negative',
                    'tense' => 'past',
                    'question' => 'This spike _____ _____ _____ resulted from user activity; the logs show maintenance scripts.',
                    'markers' => [
                        'a1' => [
                            'answer' => "can't",
                            'options' => ["can't", "shouldn't", 'might not'],
                            'verb_hint' => 'not it',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'possibly',
                            'options' => ['possibly', 'always', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'negative',
                    'tense' => 'future',
                    'question' => 'Our suppliers _____ _____ _____ guarantee next-day shipping during peak season.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'may',
                            'options' => ['may', 'must', 'should'],
                            'verb_hint' => 'not they',
                        ],
                        'a2' => [
                            'answer' => 'not',
                            'options' => ['not', 'ever', 'always'],
                            'verb_hint' => 'not they',
                        ],
                        'a3' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'have to', 'need to'],
                            'verb_hint' => 'not they',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'Before the merger, teams _____ _____ _____ file expenses without pre-approval.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'could',
                            'options' => ['could', 'must', 'should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'routinely',
                            'options' => ['routinely', 'rarely', 'never'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'be allowed to',
                            'options' => ['be allowed to', 'supposed to', 'able to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'We _____ _____ _____ deliver weekly dashboards during the pilot rollout.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'had',
                            'options' => ['had', 'must', 'should'],
                            'verb_hint' => 'we',
                        ],
                        'a2' => [
                            'answer' => 'to',
                            'options' => ['to', 'need to', 'able to'],
                            'verb_hint' => 'we',
                        ],
                        'a3' => [
                            'answer' => 'meticulously',
                            'options' => ['meticulously', 'rarely', 'casually'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'You _____ _____ _____ brief the legal team before signing the vendor agreement.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'could', 'may'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => 'you',
                        ],
                        'a3' => [
                            'answer' => 'definitely',
                            'options' => ['definitely', 'hardly', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'Given the timestamps, the contractors _____ _____ _____ completed the audit overnight.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'might', 'should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => 'they',
                        ],
                        'a3' => [
                            'answer' => 'already',
                            'options' => ['already', 'barely', 'never'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Our platform _____ _____ _____ process cross-border payments in real time now.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'should', 'might'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'have to', 'need to'],
                            'verb_hint' => 'it',
                        ],
                        'a3' => [
                            'answer' => 'reliably',
                            'options' => ['reliably', 'rarely', 'sporadically'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Partners _____ _____ _____ access the beta features under the new licensing plan.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'may',
                            'options' => ['may', 'must', 'should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'now',
                            'options' => ['now', 'never', 'hardly'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'freely',
                            'options' => ['freely', 'rarely', 'barely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Every branch _____ _____ _____ comply with the revised transparency charter.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'should', 'could'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'fully',
                            'options' => ['fully', 'barely', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'adhere',
                            'options' => ['adhere', 'question', 'delay'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'You _____ _____ _____ archive your notes in the shared knowledge base.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'might'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'consistently',
                            'options' => ['consistently', 'rarely', 'sporadically'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'document',
                            'options' => ['document', 'dismiss', 'delay'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Given the silence, the panel _____ _____ _____ reached a unanimous verdict already.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'might', 'could'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => 'they',
                        ],
                        'a3' => [
                            'answer' => 'just',
                            'options' => ['just', 'never', 'hardly'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'After the upgrade, the support bots _____ _____ _____ resolve billing issues autonomously.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'might', 'should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'have to', 'need to'],
                            'verb_hint' => 'they',
                        ],
                        'a3' => [
                            'answer' => 'swiftly',
                            'options' => ['swiftly', 'rarely', 'reluctantly'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'Once certified, trainees _____ _____ _____ lead onsite inspections.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'might', 'could'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'finally',
                            'options' => ['finally', 'hardly', 'never'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'be allowed to',
                            'options' => ['be allowed to', 'need to', 'have to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'Regional offices _____ _____ _____ submit ESG metrics alongside financial statements.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'might', 'could'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'now',
                            'options' => ['now', 'barely', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'have to',
                            'options' => ['have to', 'be able to', 'need to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'If competition intensifies, you _____ _____ _____ reposition the product narrative.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'might'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'quickly',
                            'options' => ['quickly', 'slowly', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'work to',
                            'options' => ['work to', 'refuse to', 'forget to'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                
            ],
            'C1' => [
                [
                    'theme' => 'ability',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '_____ the crisis team _____ _____ pivot the strategy without executive sign-off?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Can',
                            'options' => ['Can', 'Could', 'Should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'credibly',
                            'options' => ['credibly', 'barely', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'claim to',
                            'options' => ['claim to', 'hesitate to', 'refuse to'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'question',
                    'tense' => 'future',
                    'question' => '_____ the consortium _____ _____ publish interim results before peer review concludes?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'May',
                            'options' => ['May', 'Might', 'Must'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'ever',
                            'options' => ['ever', 'rarely', 'hardly'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'be authorised to',
                            'options' => ['be authorised to', 'need to', 'have to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'question',
                    'tense' => 'past',
                    'question' => '_____ the regulator _____ _____ impose stricter capital buffers after the audit?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Did',
                            'options' => ['Did', 'Must', 'Should'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'need to',
                            'options' => ['need to', 'have to', 'ought to'],
                            'verb_hint' => 'it',
                        ],
                        'a3' => [
                            'answer' => 'unilaterally',
                            'options' => ['unilaterally', 'rarely', 'reluctantly'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '_____ we _____ _____ disclose the risk scenarios to reassure investors?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Should',
                            'options' => ['Should', 'Must', 'Might'],
                            'verb_hint' => 'we',
                        ],
                        'a2' => [
                            'answer' => 'proactively',
                            'options' => ['proactively', 'rarely', 'begrudgingly'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'share them',
                            'options' => ['share them', 'withhold them', 'delay them'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'question',
                    'tense' => 'past',
                    'question' => '_____ the cyberattack _____ _____ originated from an insider, given the access logs?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Could',
                            'options' => ['Could', 'Must', 'Should'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'credibly',
                            'options' => ['credibly', 'barely', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'question',
                    'tense' => 'future',
                    'question' => '_____ the biotech firm _____ _____ ramp production if regulators approve tomorrow?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Will',
                            'options' => ['Will', 'Should', 'Might'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'have to', 'need to'],
                            'verb_hint' => 'it',
                        ],
                        'a3' => [
                            'answer' => 'immediately',
                            'options' => ['immediately', 'slowly', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'External counsel _____ _____ _____ circulate draft agreements beyond the legal board.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'should', 'may'],
                            'verb_hint' => 'not they',
                        ],
                        'a2' => [
                            'answer' => 'not',
                            'options' => ['not', 'ever', 'rarely'],
                            'verb_hint' => 'not they',
                        ],
                        'a3' => [
                            'answer' => 'be permitted to',
                            'options' => ['be permitted to', 'need to', 'have to'],
                            'verb_hint' => 'not they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'negative',
                    'tense' => 'future',
                    'question' => 'The task force _____ _____ _____ submit redundant status reports under the new policy.',
                    'markers' => [
                        'a1' => [
                            'answer' => "won't",
                            'options' => ["won't", 'must', 'should'],
                            'verb_hint' => 'not they',
                        ],
                        'a2' => [
                            'answer' => 'have to',
                            'options' => ['have to', 'need to', 'ought to'],
                            'verb_hint' => 'not they',
                        ],
                        'a3' => [
                            'answer' => 'any longer',
                            'options' => ['any longer', 'always', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'You _____ _____ _____ prioritise vanity metrics over retention signals again.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'might'],
                            'verb_hint' => 'not you',
                        ],
                        'a2' => [
                            'answer' => 'under no circumstances',
                            'options' => ['under no circumstances', 'rarely', 'occasionally'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'ever',
                            'options' => ['ever', 'seldom', 'always'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'negative',
                    'tense' => 'past',
                    'question' => 'That valuation _____ _____ _____ emerged from independent analysts; the language matches marketing decks.',
                    'markers' => [
                        'a1' => [
                            'answer' => "can't",
                            'options' => ["can't", "shouldn't", 'might not'],
                            'verb_hint' => 'not it',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'plausibly',
                            'options' => ['plausibly', 'barely', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'negative',
                    'tense' => 'future',
                    'question' => 'Our distributed teams _____ _____ _____ synchronise daily once the firewall restrictions tighten.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'may',
                            'options' => ['may', 'must', 'should'],
                            'verb_hint' => 'not they',
                        ],
                        'a2' => [
                            'answer' => 'not',
                            'options' => ['not', 'ever', 'always'],
                            'verb_hint' => 'not they',
                        ],
                        'a3' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'need to', 'have to'],
                            'verb_hint' => 'not they',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'Senior fellows _____ _____ _____ invite external reviewers during the confidential deliberations.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'were',
                            'options' => ['were', 'could', 'should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'never',
                            'options' => ['never', 'rarely', 'hardly'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'allowed to',
                            'options' => ['allowed to', 'supposed to', 'able to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'We _____ _____ _____ convene emergency sessions throughout the litigation.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'had',
                            'options' => ['had', 'must', 'should'],
                            'verb_hint' => 'we',
                        ],
                        'a2' => [
                            'answer' => 'to',
                            'options' => ['to', 'need to', 'able to'],
                            'verb_hint' => 'we',
                        ],
                        'a3' => [
                            'answer' => 'repeatedly',
                            'options' => ['repeatedly', 'rarely', 'casually'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'You _____ _____ _____ outline clear contingencies before pitching the acquisition.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'could', 'might'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => 'you',
                        ],
                        'a3' => [
                            'answer' => 'explicitly',
                            'options' => ['explicitly', 'vaguely', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'The negotiation team _____ _____ _____ secured concessions overnight; the terms barely shifted.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must not have',
                            'options' => ['must not have', 'could not', 'should not'],
                            'verb_hint' => 'not they',
                        ],
                        'a2' => [
                            'answer' => 'actually',
                            'options' => ['actually', 'hardly', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'won',
                            'options' => ['won', 'altered', 'abandoned'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Our analytics stack _____ _____ _____ parse multilingual sentiment in near real time now.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'might'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'need to', 'have to'],
                            'verb_hint' => 'it',
                        ],
                        'a3' => [
                            'answer' => 'consistently',
                            'options' => ['consistently', 'sporadically', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Advisory partners _____ _____ _____ join the confidential steering meetings under NDA.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'may',
                            'options' => ['may', 'must', 'should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'now',
                            'options' => ['now', 'never', 'hardly'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'formally',
                            'options' => ['formally', 'informally', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Each subsidiary _____ _____ _____ implement the whistleblower safeguards without delay.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'should', 'could'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'fully',
                            'options' => ['fully', 'partially', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'comply',
                            'options' => ['comply', 'question', 'delay'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'You _____ _____ _____ publish transparent post-mortems after each incident.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'ought to',
                            'options' => ['ought to', 'have to', 'might'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'consistently',
                            'options' => ['consistently', 'sporadically', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'release them',
                            'options' => ['release them', 'withhold them', 'delay them'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'The silence in chat _____ _____ _____ mean the deployment failed; logs show success.',
                    'markers' => [
                        'a1' => [
                            'answer' => "can't",
                            'options' => ["can't", 'might', 'should'],
                            'verb_hint' => 'not it',
                        ],
                        'a2' => [
                            'answer' => 'really',
                            'options' => ['really', 'barely', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'signify',
                            'options' => ['signify', 'ignore', 'delay'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'With the AI overhaul, the platform _____ _____ _____ diagnose anomalies before outages.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'might', 'could'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'need to', 'have to'],
                            'verb_hint' => 'it',
                        ],
                        'a3' => [
                            'answer' => 'autonomously',
                            'options' => ['autonomously', 'rarely', 'reluctantly'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'After ratification, regional leads _____ _____ _____ negotiate bespoke pricing tiers.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'might', 'could'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'finally',
                            'options' => ['finally', 'barely', 'never'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'be allowed to',
                            'options' => ['be allowed to', 'need to', 'have to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'Boards _____ _____ _____ certify ESG disclosures alongside audited statements.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'might', 'could'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'now',
                            'options' => ['now', 'barely', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'have to',
                            'options' => ['have to', 'be able to', 'need to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'If macro signals deteriorate, you _____ _____ _____ recalibrate hiring plans immediately.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'might'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'probably',
                            'options' => ['probably', 'barely', 'never'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'move to',
                            'options' => ['move to', 'avoid to', 'forget to'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                
            ],
            'C2' => [
                [
                    'theme' => 'ability',
                    'type' => 'question',
                    'tense' => 'past',
                    'question' => '_____ your predecessor _____ _____ rescued the merger talks without the emergency loan facility?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Could',
                            'options' => ['Could', 'Might', 'Should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'possibly have',
                            'options' => ['possibly have', 'ever', 'barely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'salvaged',
                            'options' => ['salvaged', 'abandoned', 'ignored'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'question',
                    'tense' => 'future',
                    'question' => '_____ the oversight board _____ _____ waive the embargo if the leak investigation concludes today?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Might',
                            'options' => ['Might', 'Must', 'Should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'lawfully',
                            'options' => ['lawfully', 'rarely', 'hardly'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'be empowered to',
                            'options' => ['be empowered to', 'need to', 'have to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '_____ I _____ _____ escalate every whistleblower allegation to the audit chair immediately?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Must',
                            'options' => ['Must', 'Should', 'Could'],
                            'verb_hint' => 'I',
                        ],
                        'a2' => [
                            'answer' => 'without fail',
                            'options' => ['without fail', 'rarely', 'begrudgingly'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'notify',
                            'options' => ['notify', 'delay', 'dismiss'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'question',
                    'tense' => 'future',
                    'question' => '_____ we _____ _____ hedge aggressively if the central bank hints at tapering?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Should',
                            'options' => ['Should', 'Must', 'Might'],
                            'verb_hint' => 'we',
                        ],
                        'a2' => [
                            'answer' => 'perhaps',
                            'options' => ['perhaps', 'never', 'hardly'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'rebalance',
                            'options' => ['rebalance', 'delay', 'ignore'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '_____ this spike _____ _____ signalling regulatory scrutiny, given the unusual data requests?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Could',
                            'options' => ['Could', 'Must', 'Should'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'realistically be',
                            'options' => ['realistically be', 'barely be', 'never be'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'quietly',
                            'options' => ['quietly', 'loudly', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                
                [
                    'theme' => 'permission',
                    'type' => 'negative',
                    'tense' => 'future',
                    'question' => 'The ethics panel _____ _____ _____ authorise undisclosed data sharing under any scenario.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'should', 'might'],
                            'verb_hint' => 'not they',
                        ],
                        'a2' => [
                            'answer' => 'never',
                            'options' => ['never', 'rarely', 'hardly'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'be permitted to',
                            'options' => ['be permitted to', 'need to', 'have to'],
                            'verb_hint' => 'not they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'Directors _____ _____ _____ convene extraordinary sessions if thresholds are not breached.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'do',
                            'options' => ['do', 'must', 'should'],
                            'verb_hint' => 'not they',
                        ],
                        'a2' => [
                            'answer' => "n't",
                            'options' => ["n't", 'ever', 'always'],
                            'verb_hint' => 'not they',
                        ],
                        'a3' => [
                            'answer' => 'need to',
                            'options' => ['need to', 'have to', 'ought to'],
                            'verb_hint' => 'not they',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'negative',
                    'tense' => 'future',
                    'question' => 'You _____ _____ _____ chase yield with leveraged bets once volatility spikes again.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'might'],
                            'verb_hint' => 'not you',
                        ],
                        'a2' => [
                            'answer' => 'categorically',
                            'options' => ['categorically', 'rarely', 'hesitantly'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'avoid',
                            'options' => ['avoid', 'seek', 'ignore'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'negative',
                    'tense' => 'past',
                    'question' => 'Those numbers _____ _____ _____ come from finance; the rounding mimics sales projections.',
                    'markers' => [
                        'a1' => [
                            'answer' => "can't",
                            'options' => ["can't", "shouldn't", 'might not'],
                            'verb_hint' => 'not they',
                        ],
                        'a2' => [
                            'answer' => 'possibly have',
                            'options' => ['possibly have', 'ever have', 'rarely have'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'originated',
                            'options' => ['originated', 'drifted', 'collapsed'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'negative',
                    'tense' => 'future',
                    'question' => 'The legacy platform _____ _____ _____ handle real-time fraud scoring once the user base triples.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'might', 'could'],
                            'verb_hint' => 'not it',
                        ],
                        'a2' => [
                            'answer' => 'no longer',
                            'options' => ['no longer', 'barely', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'need to', 'have to'],
                            'verb_hint' => 'not it',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'Under the emergency decree, ministers _____ _____ _____ bypass procurement rules entirely.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'were briefly',
                            'options' => ['were briefly', 'might', 'should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'allowed to',
                            'options' => ['allowed to', 'supposed to', 'able to'],
                            'verb_hint' => 'they',
                        ],
                        'a3' => [
                            'answer' => 'lawfully',
                            'options' => ['lawfully', 'reluctantly', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'We _____ _____ _____ notify antitrust regulators before concluding that strategic alliance.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should have',
                            'options' => ['should have', 'could have', 'might have'],
                            'verb_hint' => 'we',
                        ],
                        'a2' => [
                            'answer' => 'formally',
                            'options' => ['formally', 'vaguely', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'briefed them',
                            'options' => ['briefed them', 'ignored them', 'delayed them'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'You _____ _____ _____ diversified your suppliers before sanctions were announced.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'ought to have',
                            'options' => ['ought to have', 'might have', 'could have'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'strategically',
                            'options' => ['strategically', 'randomly', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'rebalanced',
                            'options' => ['rebalanced', 'hoarded', 'ignored'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'The committee _____ _____ _____ leaked the memo; the watermark belongs to legal.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must have',
                            'options' => ['must have', 'might have', 'should have'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'inadvertently',
                            'options' => ['inadvertently', 'deliberately', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'released it',
                            'options' => ['released it', 'destroyed it', 'ignored it'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Our quantum prototype _____ _____ _____ model systemic shocks beyond traditional Monte Carlo.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'might'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'now be able to',
                            'options' => ['now be able to', 'need to', 'have to'],
                            'verb_hint' => 'it',
                        ],
                        'a3' => [
                            'answer' => 'efficiently',
                            'options' => ['efficiently', 'rarely', 'slowly'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Institutional clients _____ _____ _____ deploy the sandbox under the premium covenant.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'may',
                            'options' => ['may', 'must', 'should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'explicitly',
                            'options' => ['explicitly', 'rarely', 'barely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'opt to',
                            'options' => ['opt to', 'refuse to', 'forget to'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Every portfolio manager _____ _____ _____ certify ESG exposure with audited evidence.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'should', 'could'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'personally',
                            'options' => ['personally', 'rarely', 'casually'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'attest',
                            'options' => ['attest', 'question', 'delay'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'You _____ _____ _____ interrogate leading indicators rather than lagging ones.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'might'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'habitually',
                            'options' => ['habitually', 'rarely', 'sporadically'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'analyse them',
                            'options' => ['analyse them', 'ignore them', 'delay them'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'The absence of volatility _____ _____ _____ indicate complacency; hedges are active.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should not',
                            'options' => ['should not', 'must', 'might'],
                            'verb_hint' => 'not it',
                        ],
                        'a2' => [
                            'answer' => 'necessarily',
                            'options' => ['necessarily', 'barely', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'imply',
                            'options' => ['imply', 'erase', 'delay'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                
                [
                    'theme' => 'permission',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'Post-ratification, independent directors _____ _____ _____ veto related-party deals outright.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'might', 'could'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'explicitly',
                            'options' => ['explicitly', 'rarely', 'barely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'be authorised to',
                            'options' => ['be authorised to', 'need to', 'have to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'Supervisors _____ _____ _____ certify climate risk assumptions alongside stress tests.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'might', 'could'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'soon',
                            'options' => ['soon', 'rarely', 'barely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'have to',
                            'options' => ['have to', 'be able to', 'need to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'If liquidity evaporates, you _____ _____ _____ unwind exposure before markets seize.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'might'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'immediately',
                            'options' => ['immediately', 'slowly', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'move to',
                            'options' => ['move to', 'choose to', 'forget to'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                
            ],
        ];
    }
}
