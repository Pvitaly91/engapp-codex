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
        'polite_request_would' => [
            'detail' => 'polite_request',
            'structure_key' => 'modal_polite_request',
            'hint_formula' => 'would you mind + V-ing',
            'hint_usage' => 'ввічливо просимо когось припинити дію або зробити щось',
            'markers' => 'would you mind, excuse me, polite request',
            'correct_reason' => 'створює ввічливе прохання у формулі «Would you mind ...?»',
            'wrong_focus' => 'не підходить до ввічливої формули «Would you mind ...?»',
        ],
        'polite_request_could' => [
            'detail' => 'polite_request',
            'structure_key' => 'modal_polite_request',
            'hint_formula' => 'could you + base verb',
            'hint_usage' => 'просимо людину про послугу чемно і м\'яко',
            'markers' => 'could you please, polite request',
            'correct_reason' => 'звучить чемно та природно для прохання допомоги',
            'wrong_focus' => 'не виражає чемного прохання з «could you»',
        ],
        'ability_can' => [
            'detail' => 'ability',
            'structure_key' => 'modal_ability',
            'hint_formula' => 'can + base verb',
            'hint_usage' => 'говоримо про теперішні здібності чи навички',
            'markers' => 'can, be able to',
            'correct_reason' => 'виражає реальну здібність виконувати дію',
            'wrong_focus' => 'не передає теперішньої здібності',
        ],
        'inability_cant' => [
            'detail' => 'inability',
            'structure_key' => 'modal_inability',
            'hint_formula' => 'can\'t + base verb',
            'hint_usage' => 'пояснюємо, що дія неможлива або хтось не здатен її зробити',
            'markers' => 'can\'t, unable to',
            'correct_reason' => 'чітко показує, що дія неможлива',
            'wrong_focus' => 'не пояснює неможливість або неспроможність',
        ],
        'possibility_might' => [
            'detail' => 'possibility',
            'structure_key' => 'modal_possibility',
            'hint_formula' => 'might + base verb',
            'hint_usage' => 'робимо обережне припущення без впевненості',
            'markers' => 'might, may, could',
            'correct_reason' => 'виражає обережну можливість, що пояснює ситуацію',
            'wrong_focus' => 'або занадто сильне твердження, або не про можливість',
        ],
        'advice_should' => [
            'detail' => 'advice',
            'structure_key' => 'modal_advice',
            'hint_formula' => 'should + base verb',
            'hint_usage' => 'даємо пораду або м\'який обов\'язок',
            'markers' => 'should, ought to',
            'correct_reason' => 'пропонує доречну пораду чи рекомендацію',
            'wrong_focus' => 'не передає корисну пораду у цій ситуації',
        ],
        'advice_shouldnt' => [
            'detail' => 'advice',
            'structure_key' => 'modal_advice_negative',
            'hint_formula' => 'shouldn\'t + base verb',
            'hint_usage' => 'радимо уникнути або припинити дію',
            'markers' => 'shouldn\'t, ought not to',
            'correct_reason' => 'радить уникати небажаної дії',
            'wrong_focus' => 'не попереджає проти цієї дії',
        ],
        'prohibition_mustnt' => [
            'detail' => 'prohibition',
            'structure_key' => 'modal_prohibition',
            'hint_formula' => 'mustn\'t + base verb',
            'hint_usage' => 'суворо забороняємо дію згідно з правилами',
            'markers' => 'mustn\'t, must not',
            'correct_reason' => 'чітко передає сувору заборону',
            'wrong_focus' => 'не звучить як сувора заборона',
        ],
        'refusal_wont' => [
            'detail' => 'refusal',
            'structure_key' => 'modal_future_negative',
            'hint_formula' => 'won\'t + base verb',
            'hint_usage' => 'виражає відмову або небажання робити щось',
            'markers' => 'won\'t, will not',
            'correct_reason' => 'показує відмову або обіцянку не виконувати дію',
            'wrong_focus' => 'не виражає відмову чи обіцянку не робити дію',
        ],
        'suggestion_shall' => [
            'detail' => 'suggestion',
            'structure_key' => 'modal_suggestion',
            'hint_formula' => 'shall + we/I + base verb',
            'hint_usage' => 'пропонуємо спільну дію або ідею',
            'markers' => 'shall we, shall I',
            'correct_reason' => 'створює дружню пропозицію зробити щось',
            'wrong_focus' => 'не формулює пропозицію у ввічливій формі',
        ],
        'necessity_must' => [
            'detail' => 'necessity',
            'structure_key' => 'modal_necessity',
            'hint_formula' => 'must + base verb',
            'hint_usage' => 'говоримо про сильну необхідність або вимогу',
            'markers' => 'must, really have to',
            'correct_reason' => 'наголошує на нагальній потребі діяти',
            'wrong_focus' => 'не передає важливості або обов\'язку',
        ],
        'strong_suggestion_must' => [
            'detail' => 'strong_suggestion',
            'structure_key' => 'modal_necessity',
            'hint_formula' => 'must + base verb',
            'hint_usage' => 'даємо наполегливу рекомендацію, майже вимогу',
            'markers' => 'must, absolutely have to',
            'correct_reason' => 'звучить як сильна наполеглива пропозиція',
            'wrong_focus' => 'надто слабке або невиразне для наполегливої поради',
        ],
        'obligation_must' => [
            'detail' => 'obligation',
            'structure_key' => 'modal_necessity',
            'hint_formula' => 'must + base verb',
            'hint_usage' => 'пояснюємо обов\'язок чи необхідність виконати дію',
            'markers' => 'must, have to',
            'correct_reason' => 'виражає особистий обов\'язок зробити дію',
            'wrong_focus' => 'не показує справжній обов\'язок',
        ],
        'permission_may' => [
            'detail' => 'permission',
            'structure_key' => 'modal_permission',
            'hint_formula' => 'may + base verb',
            'hint_usage' => 'просимо або даємо дозвіл у формальному стилі',
            'markers' => 'may I, may we',
            'correct_reason' => 'ввічливо просить дозвіл або перевіряє його',
            'wrong_focus' => 'не підходить для ввічливого запиту про дозвіл',
        ],
        'intention_will' => [
            'detail' => 'intention',
            'structure_key' => 'modal_future_intention',
            'hint_formula' => 'will + base verb',
            'hint_usage' => 'передає рішення або обіцянку зробити дію',
            'markers' => 'I\'ll, I will',
            'correct_reason' => 'чітко виражає намір або обіцянку',
            'wrong_focus' => 'не демонструє чіткий намір',
        ],
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Modal Verbs Mixed Practice V2'])->id;

        $sourceIds = [
            'sectionA' => Source::firstOrCreate(['name' => 'Custom: Modal Verbs Mixed Practice V2 - Section A'])->id,
            'sectionB' => Source::firstOrCreate(['name' => 'Custom: Modal Verbs Mixed Practice V2 - Section B'])->id,
        ];

        $defaultSourceId = reset($sourceIds);

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Modal Verbs Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $dialogueTagId = Tag::firstOrCreate(
            ['name' => 'Modal Verb Dialogues'],
            ['category' => 'English Grammar Theme']
        )->id;

        $modalsTagId = Tag::firstOrCreate(
            ['name' => 'Modal Verbs'],
            ['category' => 'Modals']
        )->id;

        $detailTags = [
            'polite_request' => Tag::firstOrCreate(['name' => 'Modal Polite Request Focus'], ['category' => 'English Grammar Detail'])->id,
            'ability' => Tag::firstOrCreate(['name' => 'Modal Ability Focus'], ['category' => 'English Grammar Detail'])->id,
            'possibility' => Tag::firstOrCreate(['name' => 'Modal Possibility Focus'], ['category' => 'English Grammar Detail'])->id,
            'advice' => Tag::firstOrCreate(['name' => 'Modal Advice Focus'], ['category' => 'English Grammar Detail'])->id,
            'prohibition' => Tag::firstOrCreate(['name' => 'Modal Prohibition Focus'], ['category' => 'English Grammar Detail'])->id,
            'refusal' => Tag::firstOrCreate(['name' => 'Modal Refusal Focus'], ['category' => 'English Grammar Detail'])->id,
            'suggestion' => Tag::firstOrCreate(['name' => 'Modal Suggestions Focus'], ['category' => 'English Grammar Detail'])->id,
            'inability' => Tag::firstOrCreate(['name' => 'Modal Inability Focus'], ['category' => 'English Grammar Detail'])->id,
            'necessity' => Tag::firstOrCreate(['name' => 'Modal Necessity Focus'], ['category' => 'English Grammar Detail'])->id,
            'permission' => Tag::firstOrCreate(['name' => 'Modal Permission Focus'], ['category' => 'English Grammar Detail'])->id,
            'strong_suggestion' => Tag::firstOrCreate(['name' => 'Modal Strong Suggestion Focus'], ['category' => 'English Grammar Detail'])->id,
            'obligation' => Tag::firstOrCreate(['name' => 'Modal Obligation Focus'], ['category' => 'English Grammar Detail'])->id,
            'intention' => Tag::firstOrCreate(['name' => 'Modal Intention Focus'], ['category' => 'English Grammar Detail'])->id,
        ];

        $structureTags = [
            'modal_polite_request' => Tag::firstOrCreate(['name' => 'Structure: Modal Polite Request'], ['category' => 'English Grammar Structure'])->id,
            'modal_ability' => Tag::firstOrCreate(['name' => 'Structure: can + base verb'], ['category' => 'English Grammar Structure'])->id,
            'modal_possibility' => Tag::firstOrCreate(['name' => 'Structure: modal + base verb (possibility)'], ['category' => 'English Grammar Structure'])->id,
            'modal_advice' => Tag::firstOrCreate(['name' => 'Structure: should + base verb'], ['category' => 'English Grammar Structure'])->id,
            'modal_advice_negative' => Tag::firstOrCreate(['name' => 'Structure: should not + base verb'], ['category' => 'English Grammar Structure'])->id,
            'modal_prohibition' => Tag::firstOrCreate(['name' => 'Structure: mustn\'t + base verb'], ['category' => 'English Grammar Structure'])->id,
            'modal_future_negative' => Tag::firstOrCreate(['name' => 'Structure: will/won’t + base verb'], ['category' => 'English Grammar Structure'])->id,
            'modal_suggestion' => Tag::firstOrCreate(['name' => 'Structure: shall + base verb'], ['category' => 'English Grammar Structure'])->id,
            'modal_inability' => Tag::firstOrCreate(['name' => 'Structure: can\'t + base verb'], ['category' => 'English Grammar Structure'])->id,
            'modal_necessity' => Tag::firstOrCreate(['name' => 'Structure: must + base verb'], ['category' => 'English Grammar Structure'])->id,
            'modal_permission' => Tag::firstOrCreate(['name' => 'Structure: may + base verb'], ['category' => 'English Grammar Structure'])->id,
            'modal_future_intention' => Tag::firstOrCreate(['name' => 'Structure: will + base verb (intention)'], ['category' => 'English Grammar Structure'])->id,
        ];

        $questions = [
            [
                'question' => 'Mike, {a1} you mind not doing that?',
                'level' => 'B1',
                'source' => 'sectionA',
                'parts' => [
                    'a1' => [
                        'pattern' => 'polite_request_would',
                        'answers' => ['would'],
                        'options' => ['can', 'will', 'would', 'shall'],
                        'verb_hint' => '(modal for polite request)',
                    ],
                ],
            ],
            [
                'question' => 'I {a1} speak six languages.',
                'level' => 'B1',
                'source' => 'sectionA',
                'parts' => [
                    'a1' => [
                        'pattern' => 'ability_can',
                        'answers' => ['can'],
                        'options' => ['may', 'can', 'must', 'should'],
                        'verb_hint' => '(modal for ability)',
                    ],
                ],
            ],
            [
                'question' => 'Why hasn’t she arrived yet? — She {a1} be ill.',
                'level' => 'B1',
                'source' => 'sectionA',
                'parts' => [
                    'a1' => [
                        'pattern' => 'possibility_might',
                        'answers' => ['might'],
                        'options' => ['must', 'might', 'can’t', 'should'],
                        'verb_hint' => '(modal for possibility)',
                    ],
                ],
            ],
            [
                'question' => '{a1} you please help me with these bags?',
                'level' => 'B1',
                'source' => 'sectionA',
                'parts' => [
                    'a1' => [
                        'pattern' => 'polite_request_could',
                        'answers' => ['could'],
                        'options' => ['could', 'can', 'shall', 'will'],
                        'verb_hint' => '(modal for polite request)',
                    ],
                ],
            ],
            [
                'question' => 'You {a1} have told him the truth.',
                'level' => 'B1',
                'source' => 'sectionA',
                'parts' => [
                    'a1' => [
                        'pattern' => 'advice_should',
                        'answers' => ['should'],
                        'options' => ['should', 'must', 'might', 'can'],
                        'verb_hint' => '(modal for advice or criticism)',
                    ],
                ],
            ],
            [
                'question' => 'You {a1} bring animals into England.',
                'level' => 'B1',
                'source' => 'sectionA',
                'parts' => [
                    'a1' => [
                        'pattern' => 'prohibition_mustnt',
                        'answers' => ['mustn’t'],
                        'options' => ['can’t', 'mustn’t', 'shouldn’t', 'may not'],
                        'verb_hint' => '(modal for prohibition)',
                    ],
                ],
            ],
            [
                'question' => 'The piano is so heavy. It {a1} move.',
                'level' => 'B1',
                'source' => 'sectionA',
                'parts' => [
                    'a1' => [
                        'pattern' => 'refusal_wont',
                        'answers' => ['won’t'],
                        'options' => ['can’t', 'won’t', 'mustn’t', 'shouldn’t'],
                        'verb_hint' => '(modal for refusal/future negative)',
                    ],
                ],
            ],
            [
                'question' => '{a1} we invite the Smiths for the party?',
                'level' => 'B1',
                'source' => 'sectionA',
                'parts' => [
                    'a1' => [
                        'pattern' => 'suggestion_shall',
                        'answers' => ['shall'],
                        'options' => ['should', 'will', 'shall', 'must'],
                        'verb_hint' => '(modal for suggestion)',
                    ],
                ],
            ],
            [
                'question' => 'I’m afraid I {a1} go with you.',
                'level' => 'B1',
                'source' => 'sectionA',
                'parts' => [
                    'a1' => [
                        'pattern' => 'inability_cant',
                        'answers' => ['can’t'],
                        'options' => ['won’t', 'mustn’t', 'can’t', 'shouldn’t'],
                        'verb_hint' => '(modal for inability)',
                    ],
                ],
            ],
            [
                'question' => 'My parents say I {a1} eat so many sweets.',
                'level' => 'B1',
                'source' => 'sectionA',
                'parts' => [
                    'a1' => [
                        'pattern' => 'advice_shouldnt',
                        'answers' => ['shouldn’t'],
                        'options' => ['mustn’t', 'can’t', 'shouldn’t', 'won’t'],
                        'verb_hint' => '(modal for advice or criticism)',
                    ],
                ],
            ],
            [
                'question' => 'I have a wedding party at the weekend. I {a1} buy a new dress and a pair of shoes.',
                'level' => 'B1',
                'source' => 'sectionA',
                'parts' => [
                    'a1' => [
                        'pattern' => 'necessity_must',
                        'answers' => ['must'],
                        'options' => ['must', 'should', 'might', 'may'],
                        'verb_hint' => '(modal for necessity)',
                    ],
                ],
            ],
            [
                'question' => 'I was wondering if I {a1} ask you for a favour.',
                'level' => 'B1',
                'source' => 'sectionA',
                'parts' => [
                    'a1' => [
                        'pattern' => 'permission_may',
                        'answers' => ['may'],
                        'options' => ['may', 'might', 'could', 'can'],
                        'verb_hint' => '(modal for polite permission)',
                    ],
                ],
            ],
            [
                'question' => 'Ariel: Ethan, Clint Eastwood’s *Hereafter* is at the Odeon this weekend. We {a1} see it.',
                'level' => 'B1',
                'source' => 'sectionB',
                'parts' => [
                    'a1' => [
                        'pattern' => 'strong_suggestion_must',
                        'answers' => ['must'],
                        'options' => ['must', 'can', 'might', 'should'],
                        'verb_hint' => '(modal for strong suggestion or necessity)',
                    ],
                ],
            ],
            [
                'question' => 'Ethan: Yeah, but I’m afraid I {a1} this weekend. I {a2} go to Ipswich. It’s my grandmother’s birthday.',
                'level' => 'B1',
                'source' => 'sectionB',
                'parts' => [
                    'a1' => [
                        'pattern' => 'inability_cant',
                        'answers' => ['can’t'],
                        'options' => ['can’t', 'mustn’t', 'shouldn’t', 'won’t'],
                        'verb_hint' => '(modal for inability)',
                    ],
                    'a2' => [
                        'pattern' => 'obligation_must',
                        'answers' => ['must'],
                        'options' => ['must', 'should', 'may', 'will'],
                        'verb_hint' => '(modal for obligation)',
                    ],
                ],
            ],
            [
                'question' => 'Ariel: Well, I {a1} call David. I’m sure he wants to see it.',
                'level' => 'B1',
                'source' => 'sectionB',
                'parts' => [
                    'a1' => [
                        'pattern' => 'intention_will',
                        'answers' => ['will'],
                        'options' => ['will', 'shall', 'must', 'can'],
                        'verb_hint' => '(modal for decision or intention)',
                    ],
                ],
            ],
            [
                'question' => 'Ethan: Yeah, but you {a1} call him right away. You know how busy he is at the weekends.',
                'level' => 'B1',
                'source' => 'sectionB',
                'parts' => [
                    'a1' => [
                        'pattern' => 'advice_should',
                        'answers' => ['should'],
                        'options' => ['must', 'should', 'may', 'can'],
                        'verb_hint' => '(modal for advice or recommendation)',
                    ],
                ],
            ],
            [
                'question' => 'Ariel: Ahh! You’re right, but I {a1} talk to him tonight at Chris’s birthday party.',
                'level' => 'B1',
                'source' => 'sectionB',
                'parts' => [
                    'a1' => [
                        'pattern' => 'ability_can',
                        'answers' => ['can'],
                        'options' => ['can', 'must', 'should', 'might'],
                        'verb_hint' => '(modal for ability or possibility)',
                    ],
                ],
            ],
            [
                'question' => 'Ariel: Thanks! I {a1} be right back. Hey, you {a2} eat my biscuits, will you?',
                'level' => 'B1',
                'source' => 'sectionB',
                'parts' => [
                    'a1' => [
                        'pattern' => 'intention_will',
                        'answers' => ['will'],
                        'options' => ['will', 'might', 'can', 'must'],
                        'verb_hint' => '(modal for a promise)',
                    ],
                    'a2' => [
                        'pattern' => 'refusal_wont',
                        'answers' => ['won’t'],
                        'options' => ['won’t', 'can’t', 'shouldn’t', 'mustn’t'],
                        'verb_hint' => '(negative request)',
                    ],
                ],
            ],
        ];

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

            if ($questionData['source'] === 'sectionB') {
                $tagIds[] = $dialogueTagId;
            }

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
        $config = $this->patternConfig[$pattern] ?? [];
        $reason = $config['correct_reason'] ?? 'утворює правильну модальну форму';

        return "✅ «{$option}» {$reason}. Приклад: *{$example}*.";
    }

    private function buildWrongExplanation(string $pattern, string $option, string $example): string
    {
        $config = $this->patternConfig[$pattern] ?? [];
        $focus = $config['wrong_focus'] ?? 'не відповідає вимогам цієї модальної конструкції';

        return "❌ «{$option}» {$focus}. Зверни увагу на приклад: *{$example}*.";
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
