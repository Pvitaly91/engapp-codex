<?php

namespace Database\Seeders\V2\Modals;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Source;
use App\Models\Tag;
use App\Services\QuestionSeedingService;
use Database\Seeders\QuestionSeeder;
use Illuminate\Support\Facades\Schema;

class ModalVerbsMixedPracticeV2Seeder extends QuestionSeeder
{
    private array $levelDifficulty = [
        'A1' => 1,
        'A2' => 2,
        'B1' => 3,
        'B2' => 4,
        'C1' => 5,
        'C2' => 5,
    ];

    private array $patternConfig = [
        'polite_request' => [
            'detail' => 'request',
            'structure_key' => 'polite_modals',
            'hint_formula' => 'would/could/shall/can + base verb',
            'hint_usage' => 'використовуємо для ввічливих прохань та пропозицій',
            'markers' => 'please, mind, polite request',
        ],
        'ability' => [
            'detail' => 'ability',
            'structure_key' => 'can_ability',
            'hint_formula' => 'can + base verb',
            'hint_usage' => 'виражаємо здатність або вміння',
            'markers' => 'speak, ability, skill',
        ],
        'possibility' => [
            'detail' => 'possibility',
            'structure_key' => 'might_possibility',
            'hint_formula' => 'might/may/could + base verb',
            'hint_usage' => 'виражаємо можливість або припущення',
            'markers' => 'maybe, perhaps, possibly',
        ],
        'advice_criticism' => [
            'detail' => 'advice',
            'structure_key' => 'should_advice',
            'hint_formula' => 'should (have) + base verb / V3',
            'hint_usage' => 'даємо пораду або критикуємо минулу дію',
            'markers' => 'advice, should have, criticism',
        ],
        'prohibition' => [
            'detail' => 'prohibition',
            'structure_key' => 'mustnt_prohibition',
            'hint_formula' => "mustn't / can't + base verb",
            'hint_usage' => 'виражаємо сувору заборону',
            'markers' => 'forbidden, prohibited, not allowed',
        ],
        'refusal' => [
            'detail' => 'refusal',
            'structure_key' => 'wont_refusal',
            'hint_formula' => "won't + base verb",
            'hint_usage' => 'виражаємо відмову або небажання',
            'markers' => 'refusal, unwilling, resistance',
        ],
        'suggestion' => [
            'detail' => 'suggestion',
            'structure_key' => 'shall_suggestion',
            'hint_formula' => 'shall we + base verb',
            'hint_usage' => 'робимо пропозицію або запитуємо думку',
            'markers' => 'suggestion, proposal, invite',
        ],
        'inability' => [
            'detail' => 'inability',
            'structure_key' => 'cant_inability',
            'hint_formula' => "can't / cannot + base verb",
            'hint_usage' => 'виражаємо неможливість або невміння',
            'markers' => 'unable, impossible, cannot',
        ],
        'necessity' => [
            'detail' => 'necessity',
            'structure_key' => 'must_necessity',
            'hint_formula' => 'must / have to + base verb',
            'hint_usage' => 'виражаємо необхідність або обов\'язок',
            'markers' => 'necessary, must, have to',
        ],
        'polite_permission' => [
            'detail' => 'permission',
            'structure_key' => 'may_permission',
            'hint_formula' => 'may / might / could + base verb',
            'hint_usage' => 'ввічливо просимо дозволу',
            'markers' => 'permission, polite, formal',
        ],
        'strong_suggestion' => [
            'detail' => 'suggestion',
            'structure_key' => 'must_suggestion',
            'hint_formula' => 'must + base verb',
            'hint_usage' => 'даємо сильну пораду або рекомендацію',
            'markers' => 'strong advice, recommendation, must see',
        ],
        'obligation' => [
            'detail' => 'obligation',
            'structure_key' => 'must_obligation',
            'hint_formula' => 'must / have to + base verb',
            'hint_usage' => 'виражаємо обов\'язок або вимушену необхідність',
            'markers' => 'obligation, duty, required',
        ],
        'decision' => [
            'detail' => 'decision',
            'structure_key' => 'will_decision',
            'hint_formula' => 'will + base verb',
            'hint_usage' => 'виражаємо спонтанне рішення або намір',
            'markers' => 'decision, intention, spontaneous',
        ],
        'recommendation' => [
            'detail' => 'advice',
            'structure_key' => 'should_recommendation',
            'hint_formula' => 'should + base verb',
            'hint_usage' => 'даємо пораду або рекомендацію',
            'markers' => 'recommendation, advice, better',
        ],
        'promise' => [
            'detail' => 'promise',
            'structure_key' => 'will_promise',
            'hint_formula' => 'will + base verb',
            'hint_usage' => 'даємо обіцянку',
            'markers' => 'promise, assurance, will do',
        ],
        'negative_request' => [
            'detail' => 'request',
            'structure_key' => 'wont_negative_request',
            'hint_formula' => "won't + base verb, will you?",
            'hint_usage' => 'просимо когось не робити щось',
            'markers' => 'request, negative, please don\'t',
        ],
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Modal Verbs Mixed Practice V2'])->id;
        $sourceIds = [
            'sectionA' => Source::firstOrCreate(['name' => 'Custom: Modal Verbs Mixed Practice V2 - Section A'])->id,
            'sectionB' => Source::firstOrCreate(['name' => 'Custom: Modal Verbs Mixed Practice V2 - Section B (Dialogue)'])->id,
        ];

        $defaultSourceId = reset($sourceIds);

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Modal Verbs Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $modalsTagId = Tag::firstOrCreate(
            ['name' => 'Modal Verbs'],
            ['category' => 'Modals']
        )->id;

        $detailTags = [
            'request' => Tag::firstOrCreate(['name' => 'Modal Request Focus'], ['category' => 'English Grammar Detail'])->id,
            'ability' => Tag::firstOrCreate(['name' => 'Modal Ability Focus'], ['category' => 'English Grammar Detail'])->id,
            'possibility' => Tag::firstOrCreate(['name' => 'Modal Possibility Focus'], ['category' => 'English Grammar Detail'])->id,
            'advice' => Tag::firstOrCreate(['name' => 'Modal Advice Focus'], ['category' => 'English Grammar Detail'])->id,
            'prohibition' => Tag::firstOrCreate(['name' => 'Modal Prohibition Focus'], ['category' => 'English Grammar Detail'])->id,
            'refusal' => Tag::firstOrCreate(['name' => 'Modal Refusal Focus'], ['category' => 'English Grammar Detail'])->id,
            'suggestion' => Tag::firstOrCreate(['name' => 'Modal Suggestion Focus'], ['category' => 'English Grammar Detail'])->id,
            'inability' => Tag::firstOrCreate(['name' => 'Modal Inability Focus'], ['category' => 'English Grammar Detail'])->id,
            'necessity' => Tag::firstOrCreate(['name' => 'Modal Necessity Focus'], ['category' => 'English Grammar Detail'])->id,
            'permission' => Tag::firstOrCreate(['name' => 'Modal Permission Focus'], ['category' => 'English Grammar Detail'])->id,
            'obligation' => Tag::firstOrCreate(['name' => 'Modal Obligation Focus'], ['category' => 'English Grammar Detail'])->id,
            'decision' => Tag::firstOrCreate(['name' => 'Modal Decision Focus'], ['category' => 'English Grammar Detail'])->id,
            'promise' => Tag::firstOrCreate(['name' => 'Modal Promise Focus'], ['category' => 'English Grammar Detail'])->id,
        ];

        $structureTags = [
            'polite_modals' => Tag::firstOrCreate(['name' => 'Structure: would/could/shall/can'], ['category' => 'English Grammar Structure'])->id,
            'can_ability' => Tag::firstOrCreate(['name' => 'Structure: can for ability'], ['category' => 'English Grammar Structure'])->id,
            'might_possibility' => Tag::firstOrCreate(['name' => 'Structure: might/may for possibility'], ['category' => 'English Grammar Structure'])->id,
            'should_advice' => Tag::firstOrCreate(['name' => 'Structure: should for advice'], ['category' => 'English Grammar Structure'])->id,
            'mustnt_prohibition' => Tag::firstOrCreate(['name' => "Structure: mustn't for prohibition"], ['category' => 'English Grammar Structure'])->id,
            'wont_refusal' => Tag::firstOrCreate(['name' => "Structure: won't for refusal"], ['category' => 'English Grammar Structure'])->id,
            'shall_suggestion' => Tag::firstOrCreate(['name' => 'Structure: shall for suggestion'], ['category' => 'English Grammar Structure'])->id,
            'cant_inability' => Tag::firstOrCreate(['name' => "Structure: can't for inability"], ['category' => 'English Grammar Structure'])->id,
            'must_necessity' => Tag::firstOrCreate(['name' => 'Structure: must for necessity'], ['category' => 'English Grammar Structure'])->id,
            'may_permission' => Tag::firstOrCreate(['name' => 'Structure: may for permission'], ['category' => 'English Grammar Structure'])->id,
            'must_suggestion' => Tag::firstOrCreate(['name' => 'Structure: must for strong suggestion'], ['category' => 'English Grammar Structure'])->id,
            'must_obligation' => Tag::firstOrCreate(['name' => 'Structure: must for obligation'], ['category' => 'English Grammar Structure'])->id,
            'will_decision' => Tag::firstOrCreate(['name' => 'Structure: will for decision'], ['category' => 'English Grammar Structure'])->id,
            'should_recommendation' => Tag::firstOrCreate(['name' => 'Structure: should for recommendation'], ['category' => 'English Grammar Structure'])->id,
            'will_promise' => Tag::firstOrCreate(['name' => 'Structure: will for promise'], ['category' => 'English Grammar Structure'])->id,
            'wont_negative_request' => Tag::firstOrCreate(['name' => "Structure: won't for negative request"], ['category' => 'English Grammar Structure'])->id,
        ];

        // SECTION A — Complete the sentences with a modal verb from the bubble
        $sectionA = [
            [
                'question' => "Mike, {a1} you mind not doing that?",
                'level' => 'B1',
                'source' => 'sectionA',
                'parts' => [
                    'a1' => [
                        'pattern' => 'polite_request',
                        'answers' => ['would'],
                        'options' => ['can', 'will', 'would', 'shall'],
                        'verb_hint' => 'would + base verb (polite request)',
                    ],
                ],
            ],
            [
                'question' => "I {a1} speak six languages.",
                'level' => 'A2',
                'source' => 'sectionA',
                'parts' => [
                    'a1' => [
                        'pattern' => 'ability',
                        'answers' => ['can'],
                        'options' => ['may', 'can', 'must', 'should'],
                        'verb_hint' => 'can + base verb (ability)',
                    ],
                ],
            ],
            [
                'question' => "Why hasn't she arrived yet? — She {a1} be ill.",
                'level' => 'B1',
                'source' => 'sectionA',
                'parts' => [
                    'a1' => [
                        'pattern' => 'possibility',
                        'answers' => ['might'],
                        'options' => ['must', 'might', "can't", 'should'],
                        'verb_hint' => 'might + base verb (possibility)',
                    ],
                ],
            ],
            [
                'question' => "{a1} you please help me with these bags?",
                'level' => 'A2',
                'source' => 'sectionA',
                'parts' => [
                    'a1' => [
                        'pattern' => 'polite_request',
                        'answers' => ['could'],
                        'options' => ['could', 'can', 'shall', 'will'],
                        'verb_hint' => 'could + base verb (polite request)',
                    ],
                ],
            ],
            [
                'question' => "You {a1} have told him the truth.",
                'level' => 'B2',
                'source' => 'sectionA',
                'parts' => [
                    'a1' => [
                        'pattern' => 'advice_criticism',
                        'answers' => ['should'],
                        'options' => ['should', 'must', 'might', 'can'],
                        'verb_hint' => 'should have + V3 (advice/criticism)',
                    ],
                ],
            ],
            [
                'question' => "You {a1} bring animals into England.",
                'level' => 'B1',
                'source' => 'sectionA',
                'parts' => [
                    'a1' => [
                        'pattern' => 'prohibition',
                        'answers' => ['mustn\'t'],
                        'options' => ["can't", "mustn't", "shouldn't", 'may not'],
                        'verb_hint' => "mustn't + base verb (prohibition)",
                    ],
                ],
            ],
            [
                'question' => "The piano is so heavy. It {a1} move.",
                'level' => 'B1',
                'source' => 'sectionA',
                'parts' => [
                    'a1' => [
                        'pattern' => 'refusal',
                        'answers' => ['won\'t'],
                        'options' => ["can't", "won't", "mustn't", "shouldn't"],
                        'verb_hint' => "won't + base verb (refusal/future negative)",
                    ],
                ],
            ],
            [
                'question' => "{a1} we invite the Smiths for the party?",
                'level' => 'B1',
                'source' => 'sectionA',
                'parts' => [
                    'a1' => [
                        'pattern' => 'suggestion',
                        'answers' => ['shall'],
                        'options' => ['should', 'will', 'shall', 'must'],
                        'verb_hint' => 'shall we + base verb (suggestion)',
                    ],
                ],
            ],
            [
                'question' => "I'm afraid I {a1} go with you.",
                'level' => 'A2',
                'source' => 'sectionA',
                'parts' => [
                    'a1' => [
                        'pattern' => 'inability',
                        'answers' => ['can\'t'],
                        'options' => ["won't", "mustn't", "can't", "shouldn't"],
                        'verb_hint' => "can't + base verb (inability)",
                    ],
                ],
            ],
            [
                'question' => "My parents say I {a1} eat so many sweets.",
                'level' => 'B1',
                'source' => 'sectionA',
                'parts' => [
                    'a1' => [
                        'pattern' => 'advice_criticism',
                        'answers' => ['shouldn\'t'],
                        'options' => ["mustn't", "can't", "shouldn't", "won't"],
                        'verb_hint' => "shouldn't + base verb (advice)",
                    ],
                ],
            ],
            [
                'question' => "I have a wedding party at the weekend. I {a1} buy a new dress and a pair of shoes.",
                'level' => 'B1',
                'source' => 'sectionA',
                'parts' => [
                    'a1' => [
                        'pattern' => 'necessity',
                        'answers' => ['must'],
                        'options' => ['must', 'should', 'might', 'may'],
                        'verb_hint' => 'must + base verb (necessity)',
                    ],
                ],
            ],
            [
                'question' => "I was wondering if I {a1} ask you for a favour.",
                'level' => 'B2',
                'source' => 'sectionA',
                'parts' => [
                    'a1' => [
                        'pattern' => 'polite_permission',
                        'answers' => ['may'],
                        'options' => ['may', 'might', 'could', 'can'],
                        'verb_hint' => 'may + base verb (polite permission)',
                    ],
                ],
            ],
        ];

        // SECTION B — Complete the dialogue with a suitable modal verb
        $sectionB = [
            [
                'question' => "Ariel: Ethan, Clint Eastwood's *Hereafter* is at the Odeon this weekend. We {a1} see it.",
                'level' => 'B1',
                'source' => 'sectionB',
                'parts' => [
                    'a1' => [
                        'pattern' => 'strong_suggestion',
                        'answers' => ['must'],
                        'options' => ['must', 'can', 'might', 'should'],
                        'verb_hint' => 'must + base verb (strong suggestion)',
                    ],
                ],
            ],
            [
                'question' => "Ethan: Yeah, but I'm afraid I {a1} this weekend. I {a2} go to Ipswich. It's my grandmother's birthday.",
                'level' => 'B1',
                'source' => 'sectionB',
                'parts' => [
                    'a1' => [
                        'pattern' => 'inability',
                        'answers' => ['can\'t'],
                        'options' => ["can't", "mustn't", "shouldn't", "won't"],
                        'verb_hint' => "can't + base verb (inability)",
                    ],
                    'a2' => [
                        'pattern' => 'obligation',
                        'answers' => ['must'],
                        'options' => ['must', 'should', 'may', 'will'],
                        'verb_hint' => 'must + base verb (obligation)',
                    ],
                ],
            ],
            [
                'question' => "Ariel: Well, I {a1} call David. I'm sure he wants to see it.",
                'level' => 'B1',
                'source' => 'sectionB',
                'parts' => [
                    'a1' => [
                        'pattern' => 'decision',
                        'answers' => ['will'],
                        'options' => ['will', 'shall', 'must', 'can'],
                        'verb_hint' => 'will + base verb (decision)',
                    ],
                ],
            ],
            [
                'question' => "Ethan: Yeah, but you {a1} call him right away. You know how busy he is at the weekends.",
                'level' => 'B1',
                'source' => 'sectionB',
                'parts' => [
                    'a1' => [
                        'pattern' => 'recommendation',
                        'answers' => ['should'],
                        'options' => ['must', 'should', 'may', 'can'],
                        'verb_hint' => 'should + base verb (recommendation)',
                    ],
                ],
            ],
            [
                'question' => "Ariel: Ahh! You're right, but I {a1} talk to him tonight at Chris's birthday party.",
                'level' => 'A2',
                'source' => 'sectionB',
                'parts' => [
                    'a1' => [
                        'pattern' => 'ability',
                        'answers' => ['can'],
                        'options' => ['can', 'must', 'should', 'might'],
                        'verb_hint' => 'can + base verb (ability/possibility)',
                    ],
                ],
            ],
            [
                'question' => "Ariel: Thanks! I {a1} be right back. Hey, you {a2} eat my biscuits, will you?",
                'level' => 'B1',
                'source' => 'sectionB',
                'parts' => [
                    'a1' => [
                        'pattern' => 'promise',
                        'answers' => ['will'],
                        'options' => ['will', 'might', 'can', 'must'],
                        'verb_hint' => 'will + base verb (promise)',
                    ],
                    'a2' => [
                        'pattern' => 'negative_request',
                        'answers' => ['won\'t'],
                        'options' => ["won't", "can't", "shouldn't", "mustn't"],
                        'verb_hint' => "won't + base verb (negative request)",
                    ],
                ],
            ],
        ];

        $questions = array_merge($sectionA, $sectionB);

        $service = new QuestionSeedingService();
        $items = [];
        $meta = [];

        foreach ($questions as $index => $questionData) {
            $questionText = $questionData['question'];
            $parts = $questionData['parts'];
            $uuid = $this->generateQuestionUuid($index + 1, $questionText);

            $defaultAnswers = [];
            foreach ($parts as $marker => $config) {
                $defaultAnswers[$marker] = $config['answers'][0] ?? '';
            }

            $baseExample = $this->formatExample($questionText, $defaultAnswers);

            [$options, $optionMarkers] = $this->prepareOptions($parts);
            $answerEntries = $this->buildAnswerEntries($parts);

            $hints = [];
            $explanations = [];
            $correctLookup = [];
            $tagIds = [$themeTagId, $modalsTagId];

            foreach ($parts as $marker => $config) {
                $pattern = $config['pattern'];
                $patternSettings = $this->patternConfig[$pattern] ?? null;
                $exampleForMarker = $this->formatExample($questionText, array_merge($defaultAnswers, [$marker => $config['answers'][0] ?? '']));

                if ($patternSettings) {
                    $hints[$marker] = $this->buildHint($patternSettings, $exampleForMarker);

                    $detailKey = $patternSettings['detail'] ?? null;
                    if ($detailKey && isset($detailTags[$detailKey])) {
                        $tagIds[] = $detailTags[$detailKey];
                    }

                    $structureKey = $patternSettings['structure_key'] ?? null;
                    if ($structureKey && isset($structureTags[$structureKey])) {
                        $tagIds[] = $structureTags[$structureKey];
                    }
                } else {
                    $hints[$marker] = $this->buildHint([
                        'hint_formula' => '',
                        'hint_usage' => '',
                        'markers' => '',
                    ], $exampleForMarker);
                }

                $answersForMarker = $config['answers'];
                foreach ($config['options'] as $option) {
                    $optionExample = $this->formatExample($questionText, array_merge($defaultAnswers, [$marker => $option]));

                    if (in_array($option, $answersForMarker, true)) {
                        $explanations[$option] = $this->buildCorrectExplanation($pattern, $option, $optionExample);
                        $correctLookup[$option] = $option;
                    } else {
                        $explanations[$option] = $this->buildWrongExplanation($pattern, $option, $baseExample);
                    }
                }
            }

            $items[] = [
                'uuid' => $uuid,
                'question' => $questionText,
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$questionData['level']] ?? 3,
                'source_id' => $sourceIds[$questionData['source']] ?? $defaultSourceId,
                'flag' => 0,
                'level' => $questionData['level'],
                'tag_ids' => array_values(array_unique($tagIds)),
                'answers' => $answerEntries,
                'options' => $options,
                'variants' => [$questionText],
                'seeder' => static::class,
            ];

            $meta[] = [
                'uuid' => $uuid,
                'hints' => $hints,
                'answers' => array_map(fn ($config) => $config['answers'][0] ?? '', $parts),
                'explanations' => $explanations,
                'option_markers' => $optionMarkers,
                'correct_lookup' => $correctLookup,
            ];
        }

        $service->seed($items);

        foreach ($meta as $data) {
            $question = Question::where('uuid', $data['uuid'])->first();
            if (! $question) {
                continue;
            }

            if (Schema::hasColumn('questions', 'seeder') && empty($question->seeder)) {
                $question->forceFill(['seeder' => static::class])->save();
            }

            $hintText = $this->formatHintBlocks($data['hints']);
            if ($hintText !== null) {
                QuestionHint::updateOrCreate(
                    ['question_id' => $question->id, 'provider' => 'chatgpt', 'locale' => 'uk'],
                    ['hint' => $hintText]
                );
            }

            foreach ($data['explanations'] as $option => $text) {
                $marker = $data['option_markers'][$option] ?? array_key_first($data['answers']);
                $correct = $data['correct_lookup'][$option] ?? ($marker ? ($data['answers'][$marker] ?? reset($data['answers'])) : reset($data['answers']));

                if (! is_string($correct)) {
                    $correct = (string) $correct;
                }

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

    private function prepareOptions(array $parts): array
    {
        $options = [];
        $markers = [];

        foreach ($parts as $marker => $config) {
            foreach ($config['options'] as $option) {
                if (! array_key_exists($option, $markers)) {
                    $options[] = $option;
                }

                $markers[$option] = $marker;
            }
        }

        return [$options, $markers];
    }

    private function buildAnswerEntries(array $parts): array
    {
        $entries = [];

        foreach ($parts as $marker => $config) {
            foreach ($config['answers'] as $answer) {
                $entries[] = [
                    'marker' => $marker,
                    'answer' => $answer,
                    'verb_hint' => $this->formatVerbHint($config['verb_hint'] ?? null),
                ];
            }
        }

        return $entries;
    }

    private function buildHint(array $config, string $example): string
    {
        $parts = [];

        if (! empty($config['hint_formula'])) {
            $parts[] = 'Формула: **' . $config['hint_formula'] . '**.';
        }

        if (! empty($config['hint_usage'])) {
            $parts[] = 'Використання: ' . $config['hint_usage'] . '.';
        }

        if (! empty($config['markers'])) {
            $parts[] = 'Маркери: ' . $config['markers'] . '.';
        }

        $parts[] = 'Приклад: *' . $example . '*.';

        return implode(' ', $parts);
    }

    private function buildCorrectExplanation(string $pattern, string $option, string $example): string
    {
        $type = $this->classifyOption($option);

        return match ($pattern) {
            'polite_request' => "✅ «{$option}» — ввічлива форма для прохання. Приклад: *{$example}*.",
            'ability' => "✅ «{$option}» виражає здатність або вміння. Приклад: *{$example}*.",
            'possibility' => "✅ «{$option}» показує можливість або припущення. Приклад: *{$example}*.",
            'advice_criticism' => match ($type) {
                'should', 'shouldnt' => "✅ «{$option}» виражає пораду або критику. Приклад: *{$example}*.",
                default => "✅ Форма передає пораду. Приклад: *{$example}*.",
            },
            'prohibition' => "✅ «{$option}» виражає сувору заборону. Приклад: *{$example}*.",
            'refusal' => "✅ «{$option}» показує відмову або небажання. Приклад: *{$example}*.",
            'suggestion' => "✅ «{$option}» робить пропозицію або запитує думку. Приклад: *{$example}*.",
            'inability' => "✅ «{$option}» виражає неможливість. Приклад: *{$example}*.",
            'necessity' => "✅ «{$option}» показує необхідність. Приклад: *{$example}*.",
            'polite_permission' => "✅ «{$option}» — ввічлива форма для прохання дозволу. Приклад: *{$example}*.",
            'strong_suggestion' => "✅ «{$option}» дає сильну пораду або рекомендацію. Приклад: *{$example}*.",
            'obligation' => "✅ «{$option}» виражає обов'язок. Приклад: *{$example}*.",
            'decision' => "✅ «{$option}» показує спонтанне рішення. Приклад: *{$example}*.",
            'recommendation' => "✅ «{$option}» дає пораду або рекомендацію. Приклад: *{$example}*.",
            'promise' => "✅ «{$option}» виражає обіцянку. Приклад: *{$example}*.",
            'negative_request' => "✅ «{$option}» просить когось не робити щось. Приклад: *{$example}*.",
            default => "✅ Правильна відповідь. Приклад: *{$example}*.",
        };
    }

    private function buildWrongExplanation(string $pattern, string $option, string $example): string
    {
        $type = $this->classifyOption($option);

        return match ($pattern) {
            'polite_request' => match ($type) {
                'can' => "❌ «{$option}» — менш ввічлива форма. Використовуйте would/could. Приклад: *{$example}*.",
                'will' => "❌ «{$option}» звучить як наказ, а не прохання. Приклад: *{$example}*.",
                default => "❌ Варіант не підходить для ввічливого прохання. Приклад: *{$example}*.",
            },
            'ability' => match ($type) {
                'may', 'might' => "❌ «{$option}» виражає дозвіл або можливість, а не здатність. Приклад: *{$example}*.",
                'must' => "❌ «{$option}» показує обов'язок, а не вміння. Приклад: *{$example}*.",
                default => "❌ Варіант не виражає здатність. Приклад: *{$example}*.",
            },
            'possibility' => match ($type) {
                'must' => "❌ «{$option}» виражає впевненість, а не припущення. Приклад: *{$example}*.",
                'cant' => "❌ «{$option}» означає неможливість, а не припущення. Приклад: *{$example}*.",
                default => "❌ Варіант не підходить для вираження можливості. Приклад: *{$example}*.",
            },
            'advice_criticism' => "❌ Варіант не передає пораду належним чином. Приклад: *{$example}*.",
            'prohibition' => match ($type) {
                'cant' => "❌ «{$option}» означає неможливість, а не заборону. Приклад: *{$example}*.",
                'shouldnt' => "❌ «{$option}» — це порада, а не сувора заборона. Приклад: *{$example}*.",
                default => "❌ Варіант не виражає сувору заборону. Приклад: *{$example}*.",
            },
            'refusal' => "❌ Варіант не показує відмову або небажання. Приклад: *{$example}*.",
            'suggestion' => "❌ Варіант не підходить для пропозиції. Приклад: *{$example}*.",
            'inability' => match ($type) {
                'wont' => "❌ «{$option}» виражає відмову, а не неможливість. Приклад: *{$example}*.",
                'mustnt' => "❌ «{$option}» означає заборону, а не неможливість. Приклад: *{$example}*.",
                default => "❌ Варіант не виражає неможливість. Приклад: *{$example}*.",
            },
            'necessity' => "❌ Варіант не показує необхідність належним чином. Приклад: *{$example}*.",
            'polite_permission' => "❌ Варіант менш ввічливий для прохання дозволу. Приклад: *{$example}*.",
            'strong_suggestion' => "❌ Варіант не передає силу рекомендації. Приклад: *{$example}*.",
            'obligation' => "❌ Варіант не виражає обов'язок. Приклад: *{$example}*.",
            'decision' => "❌ Варіант не показує спонтанне рішення. Приклад: *{$example}*.",
            'recommendation' => "❌ Варіант не підходить для рекомендації. Приклад: *{$example}*.",
            'promise' => "❌ Варіант не виражає обіцянку. Приклад: *{$example}*.",
            'negative_request' => "❌ Варіант не підходить для негативного прохання. Приклад: *{$example}*.",
            default => "❌ Невірна форма. Правильний приклад: *{$example}*.",
        };
    }

    private function classifyOption(string $option): string
    {
        $normalized = str_replace([''', '''], "'", mb_strtolower($option));
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return match (true) {
            str_contains($normalized, 'would') => 'would',
            str_contains($normalized, 'could') => 'could',
            str_contains($normalized, 'should have') => 'should_have',
            str_contains($normalized, "shouldn't") || str_contains($normalized, 'should not') => 'shouldnt',
            str_contains($normalized, 'should') => 'should',
            str_contains($normalized, 'might') => 'might',
            str_contains($normalized, 'may') => 'may',
            str_contains($normalized, "mustn't") || str_contains($normalized, 'must not') => 'mustnt',
            str_contains($normalized, 'must') => 'must',
            str_contains($normalized, "can't") || str_contains($normalized, 'cannot') => 'cant',
            str_contains($normalized, 'can') => 'can',
            str_contains($normalized, "won't") || str_contains($normalized, 'will not') => 'wont',
            str_contains($normalized, 'will') => 'will',
            str_contains($normalized, 'shall') => 'shall',
            default => 'other',
        };
    }

    private function formatHintBlocks(array $hints): ?string
    {
        if (empty($hints)) {
            return null;
        }

        $parts = [];
        foreach ($hints as $marker => $text) {
            $clean = trim((string) $text);

            if ($clean === '') {
                continue;
            }

            $parts[] = '{' . $marker . '} ' . $clean;
        }

        if (empty($parts)) {
            return null;
        }

        return implode("\n", $parts);
    }

    private function formatVerbHint(?string $hint): ?string
    {
        if ($hint === null) {
            return null;
        }

        return trim($hint);
    }
}
