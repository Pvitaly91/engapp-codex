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

class ModalObligationNecessityV2Seeder extends QuestionSeeder
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
        'expected_rule_present' => [
            'detail' => 'expectation',
            'structure_key' => 'be_supposed_to',
            'hint_formula' => 'be supposed to + base verb',
            'hint_usage' => 'використовуємо для правил або очікуваної поведінки',
            'markers' => 'rule, expected, supposed to',
        ],
        'expectation_general' => [
            'detail' => 'expectation',
            'structure_key' => 'be_supposed_to',
            'hint_formula' => 'be supposed/meant to + base verb',
            'hint_usage' => 'пояснюємо призначення чи нормальний перебіг подій',
            'markers' => 'meant to, supposed to, expectation',
        ],
        'expectation_past' => [
            'detail' => 'expectation',
            'structure_key' => 'be_supposed_to',
            'hint_formula' => 'was/were supposed to + base verb',
            'hint_usage' => 'говоримо про очікування у минулому',
            'markers' => 'supposed to, meant to, expectation in the past',
        ],
        'lack_obligation_present' => [
            'detail' => 'lack_obligation',
            'structure_key' => 'no_obligation',
            'hint_formula' => "do/does not have to або needn't + base verb",
            'hint_usage' => 'коли дія необов’язкова у теперішньому/загальному часі',
            'markers' => "no need, optional, don't have to",
        ],
        'lack_obligation_future' => [
            'detail' => 'lack_obligation',
            'structure_key' => 'no_obligation',
            'hint_formula' => "won't have to / won't need to + base verb",
            'hint_usage' => 'коли обов’язок зникне у майбутньому',
            'markers' => 'future, relief, no obligation',
        ],
        'lack_obligation_past' => [
            'detail' => 'lack_obligation',
            'structure_key' => 'no_obligation',
            'hint_formula' => "didn't need to / didn't have to + base verb",
            'hint_usage' => 'коли у минулому не було необхідності',
            'markers' => 'yesterday, last, past, no need',
        ],
        'unnecessary_past_action' => [
            'detail' => 'regret',
            'structure_key' => 'neednt_have',
            'hint_formula' => "needn't have + V3",
            'hint_usage' => 'вказуємо, що дія була непотрібною, але відбулася',
            'markers' => 'already, unnecessary action, regret',
        ],
        'neednt_gift' => [
            'detail' => 'lack_obligation',
            'structure_key' => 'no_obligation',
            'hint_formula' => "needn't (have) + base verb",
            'hint_usage' => 'ввічливо кажемо, що подарунок чи дія не були потрібні',
            'markers' => 'gift, politeness, no need',
        ],
        'regret_past' => [
            'detail' => 'regret',
            'structure_key' => 'should_have',
            'hint_formula' => "shouldn't have + V3",
            'hint_usage' => 'виражаємо докір щодо минулої дії',
            'markers' => 'mistake, regret, past action',
        ],
        'permission_prohibition' => [
            'detail' => 'permission',
            'structure_key' => 'be_allowed_to',
            'hint_formula' => 'be allowed/permitted to + base verb',
            'hint_usage' => 'говоримо про дозволи або заборони',
            'markers' => 'rule, regulation, allowed',
        ],
        'permission_status' => [
            'detail' => 'permission',
            'structure_key' => 'allowed_status',
            'hint_formula' => 'be allowed/permitted',
            'hint_usage' => 'описуємо статус дозволу чи заборони без інфінітива',
            'markers' => 'allowed, permitted, forbidden',
        ],
        'past_necessity' => [
            'detail' => 'necessity',
            'structure_key' => 'had_to',
            'hint_formula' => 'had to / needed to + base verb',
            'hint_usage' => 'пояснюємо вимушену дію у минулому',
            'markers' => 'had to, needed, obligation',
        ],
        'obligation_present' => [
            'detail' => 'obligation',
            'structure_key' => 'need_to',
            'hint_formula' => 'have to / must + base verb',
            'hint_usage' => 'описуємо теперішній обов’язок',
            'markers' => 'duty, must, have to',
        ],
        'future_obligation' => [
            'detail' => 'necessity',
            'structure_key' => 'need_to',
            'hint_formula' => 'will have to / need to + base verb',
            'hint_usage' => 'коли дія стане необхідною у майбутньому',
            'markers' => 'future plan, obligation',
        ],
        'advice_present' => [
            'detail' => 'advice',
            'structure_key' => 'should_ought_to',
            'hint_formula' => 'should / ought to + base verb',
            'hint_usage' => 'даємо пораду або м’яку необхідність',
            'markers' => 'advice, recommendation',
        ],
        'past_expectation' => [
            'detail' => 'expectation',
            'structure_key' => 'should_have',
            'hint_formula' => 'should / ought to have + V3',
            'hint_usage' => 'очікування у минулому, яке не виправдали',
            'markers' => 'should have, regret, expectation',
        ],
        'past_obligation_expectation' => [
            'detail' => 'obligation',
            'structure_key' => 'had_to',
            'hint_formula' => 'had to / were supposed to + base verb',
            'hint_usage' => 'правила або обов’язки у минулому',
            'markers' => 'school rules, duty in the past',
        ],
        'strong_prohibition' => [
            'detail' => 'prohibition',
            'structure_key' => 'mustnt',
            'hint_formula' => "mustn't + base verb",
            'hint_usage' => 'сувора заборона',
            'markers' => 'safety, strong rule',
        ],
        'strong_advice' => [
            'detail' => 'advice',
            'structure_key' => 'had_better',
            'hint_formula' => 'had better + base verb',
            'hint_usage' => 'строга порада з попередженням про наслідки',
            'markers' => 'warning, better, advice',
        ],
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Modal Verbs Obligation Practice V2'])->id;
        $sourceIds = [
            'exercise1' => Source::firstOrCreate(['name' => 'Custom: Modal Verbs Obligation Practice V2 - Exercise 1'])->id,
            'exercise2' => Source::firstOrCreate(['name' => 'Custom: Modal Verbs Obligation Practice V2 - Exercise 2'])->id,
            'exercise3' => Source::firstOrCreate(['name' => 'Custom: Modal Verbs Obligation Practice V2 - Exercise 3'])->id,
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
            'expectation' => Tag::firstOrCreate(['name' => 'Modal Expectation Focus'], ['category' => 'English Grammar Detail'])->id,
            'lack_obligation' => Tag::firstOrCreate(['name' => 'Modal No Obligation Focus'], ['category' => 'English Grammar Detail'])->id,
            'regret' => Tag::firstOrCreate(['name' => 'Modal Regret Focus'], ['category' => 'English Grammar Detail'])->id,
            'permission' => Tag::firstOrCreate(['name' => 'Modal Permission Focus'], ['category' => 'English Grammar Detail'])->id,
            'necessity' => Tag::firstOrCreate(['name' => 'Modal Necessity Focus'], ['category' => 'English Grammar Detail'])->id,
            'advice' => Tag::firstOrCreate(['name' => 'Modal Advice Focus'], ['category' => 'English Grammar Detail'])->id,
            'prohibition' => Tag::firstOrCreate(['name' => 'Modal Prohibition Focus'], ['category' => 'English Grammar Detail'])->id,
            'obligation' => Tag::firstOrCreate(['name' => 'Modal Obligation Focus'], ['category' => 'English Grammar Detail'])->id,
        ];

        $structureTags = [
            'be_supposed_to' => Tag::firstOrCreate(['name' => 'Structure: be supposed/meant to'], ['category' => 'English Grammar Structure'])->id,
            'no_obligation' => Tag::firstOrCreate(['name' => 'Structure: no obligation forms'], ['category' => 'English Grammar Structure'])->id,
            'neednt_have' => Tag::firstOrCreate(['name' => "Structure: needn't have + V3"], ['category' => 'English Grammar Structure'])->id,
            'should_have' => Tag::firstOrCreate(['name' => 'Structure: should / ought to have + V3'], ['category' => 'English Grammar Structure'])->id,
            'be_allowed_to' => Tag::firstOrCreate(['name' => 'Structure: be allowed/permitted to'], ['category' => 'English Grammar Structure'])->id,
            'allowed_status' => Tag::firstOrCreate(['name' => 'Structure: allowed/permitted status'], ['category' => 'English Grammar Structure'])->id,
            'had_to' => Tag::firstOrCreate(['name' => 'Structure: had to / needed to'], ['category' => 'English Grammar Structure'])->id,
            'mustnt' => Tag::firstOrCreate(['name' => "Structure: mustn't"], ['category' => 'English Grammar Structure'])->id,
            'had_better' => Tag::firstOrCreate(['name' => 'Structure: had better'], ['category' => 'English Grammar Structure'])->id,
            'need_to' => Tag::firstOrCreate(['name' => 'Structure: need / have to'], ['category' => 'English Grammar Structure'])->id,
            'should_ought_to' => Tag::firstOrCreate(['name' => 'Structure: should / ought to'], ['category' => 'English Grammar Structure'])->id,
        ];

        $questions = [
            [
                'question' => "You'd better not {a1} this product without protection.",
                'level' => 'B2',
                'source' => 'exercise1',
                'parts' => [
                    'a1' => [
                        'pattern' => 'expected_rule_present',
                        'answers' => ["aren't supposed to use"],
                        'options' => [
                            'are not meant to use',
                            "aren't supposed to use",
                            'must not use',
                            "shouldn't use",
                        ],
                        'verb_hint' => 'be supposed to + base verb',
                    ],
                ],
            ],
            [
                'question' => "You {a1} go to the ceremony if you don’t feel like it. It’ll be very boring anyway.",
                'level' => 'B1',
                'source' => 'exercise1',
                'parts' => [
                    'a1' => [
                        'pattern' => 'lack_obligation_present',
                        'answers' => ["don't have to"],
                        'options' => [
                            "mustn't",
                            "don't have to",
                            "haven't to",
                            "needn't",
                        ],
                        'verb_hint' => 'lack of obligation (do + not + have to)',
                    ],
                ],
            ],
            [
                'question' => 'We took too much risk. We {a1} that decision.',
                'level' => 'B2',
                'source' => 'exercise1',
                'parts' => [
                    'a1' => [
                        'pattern' => 'unnecessary_past_action',
                        'answers' => ["needn't have made"],
                        'options' => [
                            "needn't have made",
                            "shouldn't make",
                            "mustn't have made",
                            "shouldn't have made",
                        ],
                        'verb_hint' => "needn't have + V3",
                    ],
                ],
            ],
            [
                'question' => 'He {a1} the bus because his brother picked him up at the station.',
                'level' => 'B1',
                'source' => 'exercise1',
                'parts' => [
                    'a1' => [
                        'pattern' => 'lack_obligation_past',
                        'answers' => ["didn't need to take"],
                        'options' => [
                            "needn't have taken",
                            "shouldn't have taken",
                            "didn't need to take",
                            "mustn't have taken",
                        ],
                        'verb_hint' => "didn't need to + base verb",
                    ],
                ],
            ],
            [
                'question' => 'He {a1} so much money on the trip last summer.',
                'level' => 'B1',
                'source' => 'exercise1',
                'parts' => [
                    'a1' => [
                        'pattern' => 'regret_past',
                        'answers' => ["shouldn't have spent"],
                        'options' => [
                            "shouldn't spend",
                            'had better not spend',
                            "shouldn't have spent",
                            "mustn't have spent",
                        ],
                        'verb_hint' => "shouldn't have + V3",
                    ],
                ],
            ],
            [
                'question' => 'The refugees {a1} work outside the camp.',
                'level' => 'B1',
                'source' => 'exercise1',
                'parts' => [
                    'a1' => [
                        'pattern' => 'permission_prohibition',
                        'answers' => ['are not allowed to'],
                        'options' => [
                            'had better not',
                            "mustn't",
                            'are not allowed to',
                            'are allowed to',
                        ],
                        'verb_hint' => 'be allowed to + base verb',
                    ],
                ],
            ],
            [
                'question' => 'We couldn’t find a hotel so we {a1} in the car. It was so uncomfortable!',
                'level' => 'A2',
                'source' => 'exercise1',
                'parts' => [
                    'a1' => [
                        'pattern' => 'past_necessity',
                        'answers' => ['had to sleep'],
                        'options' => [
                            'must have slept',
                            'had to sleep',
                            'should have slept',
                            'would have slept',
                        ],
                        'verb_hint' => 'had to + base verb',
                    ],
                ],
            ],
            [
                'question' => 'You {a1} forget to take your medicine.',
                'level' => 'A2',
                'source' => 'exercise1',
                'parts' => [
                    'a1' => [
                        'pattern' => 'strong_prohibition',
                        'answers' => ["mustn't"],
                        'options' => [
                            "mustn't",
                            "don't have to",
                            'must',
                            'ought not to',
                        ],
                        'verb_hint' => "mustn't + base verb",
                    ],
                ],
            ],
            [
                'question' => 'We {a1} get up early because it was a holiday, so we slept till late.',
                'level' => 'A2',
                'source' => 'exercise1',
                'parts' => [
                    'a1' => [
                        'pattern' => 'lack_obligation_past',
                        'answers' => ["didn't have to"],
                        'options' => [
                            "didn't have to",
                            "needn't get up",
                            "needn't have got up",
                            "didn't need to",
                        ],
                        'verb_hint' => "didn't have to + base verb",
                    ],
                ],
            ],
            [
                'question' => 'You {a1} call me as soon as you arrived. I was very worried!',
                'level' => 'B2',
                'source' => 'exercise1',
                'parts' => [
                    'a1' => [
                        'pattern' => 'expectation_past',
                        'answers' => ['were meant to'],
                        'options' => [
                            'were meant to',
                            'were supposed to',
                            'must',
                            'had to',
                        ],
                        'verb_hint' => 'was/were supposed to + base verb',
                    ],
                ],
            ],
            [
                'question' => "It’s beautiful! Thanks, but you {a1} anything for me.",
                'level' => 'B1',
                'source' => 'exercise2',
                'parts' => [
                    'a1' => [
                        'pattern' => 'neednt_gift',
                        'answers' => ["needn't have bought", "needn't buy"],
                        'options' => [
                            "needn't have bought",
                            "needn't buy",
                            "mustn't have bought",
                            "shouldn't have bought",
                        ],
                        'verb_hint' => "needn't (have) + base verb",
                    ],
                ],
            ],
            [
                'question' => 'We’ll {a1} be more careful about what we say in the future.',
                'level' => 'B1',
                'source' => 'exercise2',
                'parts' => [
                    'a1' => [
                        'pattern' => 'future_obligation',
                        'answers' => ['have to', 'need to'],
                        'options' => [
                            'have to',
                            'must',
                            'need to',
                            'be able to',
                        ],
                        'verb_hint' => 'will + have/need to + base verb',
                    ],
                ],
            ],
            [
                'question' => 'You {a1} see a specialist to check that knee.',
                'level' => 'B1',
                'source' => 'exercise2',
                'parts' => [
                    'a1' => [
                        'pattern' => 'advice_present',
                        'answers' => ['need', 'ought to'],
                        'options' => [
                            'need',
                            'ought to',
                            'should have',
                            "'d better",
                        ],
                        'verb_hint' => 'modal advice (need / ought to)',
                    ],
                ],
            ],
            [
                'question' => 'When you get a new employee, you {a1} work so hard any longer.',
                'level' => 'B1',
                'source' => 'exercise2',
                'parts' => [
                    'a1' => [
                        'pattern' => 'lack_obligation_future',
                        'answers' => ["won't have to", "won't need to"],
                        'options' => [
                            "won't must",
                            "won't have to",
                            "shouldn't",
                            "won't need to",
                        ],
                        'verb_hint' => "won't have/need to + base verb",
                    ],
                ],
            ],
            [
                'question' => 'The effects {a1} last 6 to 8 hours.',
                'level' => 'B1',
                'source' => 'exercise2',
                'parts' => [
                    'a1' => [
                        'pattern' => 'expectation_general',
                        'answers' => ['are meant to', 'are supposed to'],
                        'options' => [
                            'ought to',
                            'are meant to',
                            'are supposed to',
                            'had better',
                        ],
                        'verb_hint' => 'be supposed/meant to + base verb',
                    ],
                ],
            ],
            [
                'question' => 'They {a1} us a discount because we couldn’t sleep in the room that we had booked.',
                'level' => 'B2',
                'source' => 'exercise2',
                'parts' => [
                    'a1' => [
                        'pattern' => 'past_expectation',
                        'answers' => ['ought to have offered', 'should have offered'],
                        'options' => [
                            'must have offered',
                            "'d better offered",
                            'ought to have offered',
                            'should have offered',
                        ],
                        'verb_hint' => 'should/ought to have + V3',
                    ],
                ],
            ],
            [
                'question' => 'If we {a1} to have midnight snacks, why is there a light in the fridge?',
                'level' => 'B1',
                'source' => 'exercise2',
                'parts' => [
                    'a1' => [
                        'pattern' => 'expected_rule_present',
                        'answers' => ["aren't meant", "aren't supposed"],
                        'options' => [
                            "aren't meant",
                            "shouldn't",
                            "aren't supposed",
                            "don't have",
                        ],
                        'verb_hint' => 'be supposed/meant to + base verb',
                    ],
                ],
            ],
            [
                'question' => 'When I was at school, we {a1} wear a uniform.',
                'level' => 'B1',
                'source' => 'exercise2',
                'parts' => [
                    'a1' => [
                        'pattern' => 'past_obligation_expectation',
                        'answers' => ['had to', 'were supposed to'],
                        'options' => [
                            'ought to',
                            'had to',
                            'were supposed to',
                            'must',
                        ],
                        'verb_hint' => 'had to / were supposed to + base verb',
                    ],
                ],
            ],
            [
                'question' => 'We {a1} do the dishes today; we can leave them for tomorrow.',
                'level' => 'A2',
                'source' => 'exercise2',
                'parts' => [
                    'a1' => [
                        'pattern' => 'lack_obligation_present',
                        'answers' => ["needn't", "don't have to", "don't need to"],
                        'options' => [
                            "needn't",
                            "don't have to",
                            "mustn't",
                            "don't need to",
                        ],
                        'verb_hint' => 'no obligation (needn\'t / don\'t have to)',
                    ],
                ],
            ],
            [
                'question' => 'Visitors {a1} stay at night.',
                'level' => 'B1',
                'source' => 'exercise2',
                'parts' => [
                    'a1' => [
                        'pattern' => 'permission_prohibition',
                        'answers' => ["aren't permitted", "aren't allowed to"],
                        'options' => [
                            "aren't permitted",
                            "aren't allowed to",
                            "hadn't better",
                            "mustn't",
                        ],
                        'verb_hint' => 'be allowed/permitted to + base verb',
                    ],
                ],
            ],
            [
                'question' => 'TEACHER: What are you doing here? You are not {a1} to be here.',
                'level' => 'B1',
                'source' => 'exercise3',
                'parts' => [
                    'a1' => [
                        'pattern' => 'permission_prohibition',
                        'answers' => ['permitted'],
                        'options' => [
                            'permitted',
                            'invited',
                            'supposed',
                            'expected',
                        ],
                        'verb_hint' => 'permitted / allowed',
                    ],
                ],
            ],
            [
                'question' => 'STUDENT: Sorry, I know students {a1} not go into the staffroom, but I really need to speak with Mr. Donovan.',
                'level' => 'B1',
                'source' => 'exercise3',
                'parts' => [
                    'a1' => [
                        'pattern' => 'permission_prohibition',
                        'answers' => ["aren't allowed to"],
                        'options' => [
                            'must',
                            "mustn't",
                            "don't have to",
                            "aren't allowed to",
                        ],
                        'verb_hint' => 'be allowed to + base verb',
                    ],
                ],
            ],
            [
                'question' => 'TEACHER: Well, you {a1} have come in here to talk to him.',
                'level' => 'B1',
                'source' => 'exercise3',
                'parts' => [
                    'a1' => [
                        'pattern' => 'unnecessary_past_action',
                        'answers' => ["needn't"],
                        'options' => [
                            "needn't",
                            "mustn't",
                            "shouldn't",
                            "couldn't",
                        ],
                        'verb_hint' => "needn't have + V3",
                    ],
                ],
            ],
            [
                'question' => 'It wasn’t really necessary. You could have gone to the principal’s office and asked him to call Mr. Donovan. Actually, that’s what you {a1} have done.',
                'level' => 'B1',
                'source' => 'exercise3',
                'parts' => [
                    'a1' => [
                        'pattern' => 'past_expectation',
                        'answers' => ['should'],
                        'options' => [
                            'should',
                            'ought',
                            'had better',
                            'ought to',
                        ],
                        'verb_hint' => 'should have + V3',
                    ],
                ],
            ],
            [
                'question' => 'STUDENT: I’m sorry, I wasn’t thinking straight. I really {a1} to talk to him urgently.',
                'level' => 'B1',
                'source' => 'exercise3',
                'parts' => [
                    'a1' => [
                        'pattern' => 'past_necessity',
                        'answers' => ['needed'],
                        'options' => [
                            'had',
                            'ought',
                            'needed',
                            'must',
                        ],
                        'verb_hint' => 'needed to + base verb',
                    ],
                ],
            ],
            [
                'question' => 'TEACHER: Well, you’d {a1} think straight the next time, OK? Now, if you want to talk to Mr. Donovan, you don’t {a2} to go to the principal’s office.',
                'level' => 'B1',
                'source' => 'exercise3',
                'parts' => [
                    'a1' => [
                        'pattern' => 'strong_advice',
                        'answers' => ['better'],
                        'options' => [
                            'better',
                            'ought to',
                            "mustn't",
                            "needn't",
                        ],
                        'verb_hint' => 'had better + base verb',
                    ],
                    'a2' => [
                        'pattern' => 'lack_obligation_present',
                        'answers' => ['need'],
                        'options' => [
                            'need',
                            'have',
                            'must',
                            'ought',
                        ],
                        'verb_hint' => 'need + to',
                    ],
                ],
            ],
            [
                'question' => 'And, please, you {a1} take that chewing-gum out of your mouth. You know that chewing-gum is not {a2} on school premises.',
                'level' => 'B1',
                'source' => 'exercise3',
                'parts' => [
                    'a1' => [
                        'pattern' => 'obligation_present',
                        'answers' => ['have to'],
                        'options' => [
                            'have to',
                            "don't have to",
                            'had better',
                            'must',
                        ],
                        'verb_hint' => 'have to + base verb',
                    ],
                    'a2' => [
                        'pattern' => 'permission_status',
                        'answers' => ['allowed'],
                        'options' => [
                            'allowed',
                            'welcome',
                            'encouraged',
                            'required',
                        ],
                        'verb_hint' => 'allowed (status)',
                    ],
                ],
            ],
            [
                'question' => 'You {a1} better not do it again, or I’ll have to send you to detention.',
                'level' => 'B1',
                'source' => 'exercise3',
                'parts' => [
                    'a1' => [
                        'pattern' => 'strong_advice',
                        'answers' => ['had', "'d"],
                        'options' => [
                            'had',
                            'should',
                            "'d",
                            'would',
                        ],
                        'verb_hint' => 'had better + base verb',
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
            'expected_rule_present', 'expectation_general', 'expectation_past' => "✅ «{$option}» показує правило або очікувану дію. Приклад: *{$example}*.",
            'lack_obligation_present' => match ($type) {
                'dont_have_to', 'dont_need_to' => "✅ «{$option}» означає, що обов'язку немає. Приклад: *{$example}*.",
                'neednt' => "✅ «{$option}» — коротка форма, що підкреслює відсутність необхідності. Приклад: *{$example}*.",
                'need' => "✅ «{$option}» утворює конструкцію «don't need to», яка знімає обов'язок. Приклад: *{$example}*.",
                default => "✅ Форма передає відсутність обов'язку. Приклад: *{$example}*.",
            },
            'neednt_gift' => match ($type) {
                'neednt_have' => "✅ «{$option}» показує, що дія була непотрібною, хоч і відбулася. Приклад: *{$example}*.",
                'neednt' => "✅ «{$option}» ввічливо каже, що подарунок не був обов'язковим. Приклад: *{$example}*.",
                default => "✅ Конструкція коректно знімає обов'язок. Приклад: *{$example}*.",
            },
            'unnecessary_past_action' => "✅ «{$option}» наголошує, що дія у минулому була зайвою. Приклад: *{$example}*.",
            'lack_obligation_past' => match ($type) {
                'didnt_need_to', 'didnt_have_to' => "✅ «{$option}» показує, що робити щось було не потрібно. Приклад: *{$example}*.",
                default => "✅ Форма правильно передає відсутність минулої необхідності. Приклад: *{$example}*.",
            },
            'regret_past' => "✅ «{$option}» виражає докір щодо минулої дії. Приклад: *{$example}*.",
            'permission_prohibition' => match ($type) {
                'not_allowed', 'not_permitted', 'permitted' => "✅ «{$option}» чітко позначає заборону. Приклад: *{$example}*.",
                default => "✅ Конструкція коректно вказує на заборону. Приклад: *{$example}*.",
            },
            'permission_status' => "✅ «{$option}» утворює природне словосполучення зі словом not. Приклад: *{$example}*.",
            'past_necessity' => match ($type) {
                'had_to', 'needed' => "✅ «{$option}» описує вимушений обов'язок. Приклад: *{$example}*.",
                default => "✅ Конструкція показує необхідність. Приклад: *{$example}*.",
            },
            'obligation_present' => match ($type) {
                'have_to', 'must', 'had_better' => "✅ «{$option}» наголошує на обов'язку. Приклад: *{$example}*.",
                default => "✅ Форма передає необхідність. Приклад: *{$example}*.",
            },
            'future_obligation' => match ($type) {
                'have_to', 'need_to' => "✅ «{$option}» показує майбутню потребу. Приклад: *{$example}*.",
                default => "✅ Форма говорить про обов'язок у майбутньому. Приклад: *{$example}*.",
            },
            'advice_present' => match ($type) {
                'need', 'need_to' => "✅ «{$option}» передає м'яку необхідність звернутися. Приклад: *{$example}*.",
                'ought' => "✅ «{$option}» звучить як сильна порада. Приклад: *{$example}*.",
                default => "✅ Форма дає слушну пораду. Приклад: *{$example}*.",
            },
            'past_expectation' => match ($type) {
                'should', 'should_have', 'ought_to_have' => "✅ «{$option}» говорить про очікувану, але невиконану дію. Приклад: *{$example}*.",
                default => "✅ Конструкція передає очікування у минулому. Приклад: *{$example}*.",
            },
            'past_obligation_expectation' => match ($type) {
                'had_to', 'are_supposed', 'supposed' => "✅ «{$option}» описує шкільні правила. Приклад: *{$example}*.",
                default => "✅ Форма підкреслює минулий обов'язок. Приклад: *{$example}*.",
            },
            'strong_prohibition' => "✅ «{$option}» суворо забороняє дію. Приклад: *{$example}*.",
            'strong_advice' => match ($type) {
                'had_better', 'had', 'd' => "✅ «{$option}» передає сильну пораду з попередженням. Приклад: *{$example}*.",
                default => "✅ Форма звучить як переконлива порада. Приклад: *{$example}*.",
            },
            default => "✅ Правильна відповідь. Приклад: *{$example}*.",
        };
    }
    private function buildWrongExplanation(string $pattern, string $option, string $example): string
    {
        $type = $this->classifyOption($option);

        return match ($pattern) {
            'expected_rule_present', 'expectation_general', 'expectation_past' => match ($type) {
                'mustnt' => "❌ «{$option}» означає сувору заборону, а тут підкреслюється правило/очікування. Правильний зразок: *{$example}*.",
                'must' => "❌ «{$option}» говорить про власну вимогу, а не про встановлене правило. Дивись приклад: *{$example}*.",
                'shouldnt' => "❌ «{$option}» — лише порада, а не вимога правила. Подивись правильний варіант: *{$example}*.",
                'are_meant', 'meant' => "❌ «{$option}» натякає на призначення, але в реченні йдеться про зовнішнє правило. Приклад: *{$example}*.",
                default => "❌ Ця форма не передає потрібного правила. Перевір правильний приклад: *{$example}*.",
            },
            'lack_obligation_present' => match ($type) {
                'must', 'mustnt' => "❌ «{$option}» виражає обов'язок або заборону, а нам треба показати, що дія необов'язкова. Правильний приклад: *{$example}*.",
                'should' => "❌ «{$option}» радить зробити дію, а потрібно показати, що її можна пропустити. Подивись: *{$example}*.",
                default => "❌ Цей варіант не знімає обов'язку. Правильний зразок: *{$example}*.",
            },
            'neednt_gift' => match ($type) {
                'must_have' => "❌ «{$option}» означає, що дія точно відбулася, а ми пояснюємо, що вона була зайвою. Приклад: *{$example}*.",
                'shouldnt_have' => "❌ «{$option}» дорікає за дію, а не підкреслює її необов'язковість. Правильна відповідь: *{$example}*.",
                default => "❌ Варіант не звучить як ввічливе зняття обов'язку. Подивись: *{$example}*.",
            },
            'unnecessary_past_action' => match ($type) {
                'shouldnt' => "❌ «{$option}» говорить про загальну пораду, а не про те, що дія вже була зайвою. Правильний приклад: *{$example}*.",
                'mustnt_have' => "❌ «{$option}» натякає на заборону, а не на непотрібність. Перевір: *{$example}*.",
                default => "❌ Форма не показує, що дія була даремною. Подивись: *{$example}*.",
            },
            'lack_obligation_past' => match ($type) {
                'neednt_have' => "❌ «{$option}» означає, що дія таки відбулася, хоча була не потрібна. У реченні йдеться, що її взагалі не робили. Приклад: *{$example}*.",
                'shouldnt_have' => "❌ «{$option}» — це докір, а не відсутність обов'язку. Дивись правильний зразок: *{$example}*.",
                default => "❌ Варіант не показує, що дія була необов'язкова. Подивись: *{$example}*.",
            },
            'regret_past' => match ($type) {
                'shouldnt' => "❌ «{$option}» лише радить на майбутнє, а треба висловити докір за минуле. Приклад: *{$example}*.",
                'mustnt_have' => "❌ «{$option}» про заборону, а не про помилку. Правильний варіант: *{$example}*.",
                default => "❌ Форма не передає жалю. Перевір приклад: *{$example}*.",
            },
            'permission_prohibition' => match ($type) {
                'had_better' => "❌ «{$option}» звучить як порада, а нам потрібно посилатися на правило. Приклад: *{$example}*.",
                'mustnt' => "❌ «{$option}» забороняє абсолютно, але контекст про адміністративну заборону з allowed. Перевір: *{$example}*.",
                'allowed' => "❌ «{$option}» дозволяє дію, а у реченні йдеться про заборону. Подивись: *{$example}*.",
                default => "❌ Варіант не передає заборону від правил. Приклад: *{$example}*.",
            },
            'permission_status' => match ($type) {
                'welcome', 'encouraged', 'required' => "❌ «{$option}» не передає заборону. Правильний зразок: *{$example}*.",
                default => "❌ Слово не підходить для виразу «not ...». Перевір: *{$example}*.",
            },
            'past_necessity' => match ($type) {
                'must_have' => "❌ «{$option}» означає припущення, а не реальну необхідність. Правильний приклад: *{$example}*.",
                'should_have' => "❌ «{$option}» радить, але не показує вимушену дію. Дивись: *{$example}*.",
                default => "❌ Форма не показує, що дія була вимушеною. Приклад: *{$example}*.",
            },
            'obligation_present' => match ($type) {
                'dont_have_to', 'dont_need_to' => "❌ «{$option}» знімає обов'язок, а нам треба його підкреслити. Подивись: *{$example}*.",
                default => "❌ Варіант не звучить як обов'язок. Правильний приклад: *{$example}*.",
            },
            'future_obligation' => match ($type) {
                'must' => "❌ «{$option}» не підкреслює план чи вимушеність у майбутньому. Перевір правильний зразок: *{$example}*.",
                'be_able' => "❌ «{$option}» говорить про можливість, а не обов'язок. Дивись: *{$example}*.",
                default => "❌ Форма не описує майбутній обов'язок. Приклад: *{$example}*.",
            },
            'advice_present' => match ($type) {
                'should_have' => "❌ «{$option}» стосується минулого, а потрібна порада на теперішнє. Подивись: *{$example}*.",
                'had_better' => "❌ «{$option}» звучить занадто загрозливо для медичної рекомендації. Приклад: *{$example}*.",
                default => "❌ Варіант не дає доречної поради. Перевір: *{$example}*.",
            },
            'past_expectation' => match ($type) {
                'must_have' => "❌ «{$option}» припускає, що дія вже відбулася, а нам потрібно говорити про очікування. Приклад: *{$example}*.",
                'had_better' => "❌ «{$option}» граматично некоректний у минулому. Подивись правильний варіант: *{$example}*.",
                'ought' => "❌ «{$option}» без have не створює минулого часу. Приклад: *{$example}*.",
                default => "❌ Форма не описує минуле очікування. Дивись: *{$example}*.",
            },
            'past_obligation_expectation' => match ($type) {
                'ought' => "❌ «{$option}» не показує чітку вимогу чи правило. Подивись правильний приклад: *{$example}*.",
                'must' => "❌ «{$option}» звучить як сильна вимога, але не зазначає правила. Дивись: *{$example}*.",
                default => "❌ Варіант не відтворює обов'язок у минулому. Приклад: *{$example}*.",
            },
            'strong_prohibition' => match ($type) {
                'must', 'need' => "❌ «{$option}» не містить заборони. Подивись правильний варіант: *{$example}*.",
                default => "❌ Форма не застерігає від дії. Приклад: *{$example}*.",
            },
            'strong_advice' => match ($type) {
                'should' => "❌ «{$option}» занадто слабке для попередження. Перевір: *{$example}*.",
                'would' => "❌ «{$option}» не створює конструкцію had better. Подивись: *{$example}*.",
                default => "❌ Варіант не передає термінової поради. Правильний приклад: *{$example}*.",
            },
            default => "❌ Невірна форма. Правильний приклад: *{$example}*.",
        };
    }
    private function classifyOption(string $option): string
    {
        $normalized = str_replace(['’', '‘'], "'", mb_strtolower($option));
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return match (true) {
            str_contains($normalized, "aren't permitted") || str_contains($normalized, 'are not permitted') => 'not_permitted',
            str_contains($normalized, "aren't allowed") || str_contains($normalized, 'are not allowed') => 'not_allowed',
            str_contains($normalized, 'allowed') => 'allowed',
            str_contains($normalized, 'permitted') => 'permitted',
            str_contains($normalized, "mustn't") || str_contains($normalized, 'must not') => 'mustnt',
            str_contains($normalized, 'must have') => 'must_have',
            str_contains($normalized, 'must') => 'must',
            str_contains($normalized, "shouldn't have") => 'shouldnt_have',
            str_contains($normalized, "shouldn't") => 'shouldnt',
            str_contains($normalized, 'should have') => 'should_have',
            str_contains($normalized, 'should') => 'should',
            str_contains($normalized, "needn't have") => 'neednt_have',
            str_contains($normalized, "needn't") => 'neednt',
            str_contains($normalized, "don't have to") || str_contains($normalized, 'do not have to') => 'dont_have_to',
            str_contains($normalized, "don't need to") || str_contains($normalized, 'do not need to') => 'dont_need_to',
            str_contains($normalized, "didn't have to") => 'didnt_have_to',
            str_contains($normalized, "didn't need to") => 'didnt_need_to',
            str_contains($normalized, "won't have to") => 'wont_have_to',
            str_contains($normalized, "won't need to") => 'wont_need_to',
            str_contains($normalized, 'need to') => 'need_to',
            $normalized === 'need' => 'need',
            str_contains($normalized, 'needed') => 'needed',
            str_contains($normalized, 'had to') => 'had_to',
            $normalized === 'had' => 'had',
            str_contains($normalized, "'d better") => 'had_better',
            str_contains($normalized, 'had better') => 'had_better',
            $normalized === "'d" || $normalized === 'd' => 'd',
            str_contains($normalized, 'ought to have') => 'ought_to_have',
            str_contains($normalized, 'ought to') => 'ought',
            str_contains($normalized, 'ought') => 'ought',
            str_contains($normalized, 'are meant') => 'are_meant',
            str_contains($normalized, 'meant') => 'meant',
            str_contains($normalized, 'are supposed') => 'are_supposed',
            str_contains($normalized, 'supposed') => 'supposed',
            str_contains($normalized, "won't must") => 'invalid',
            str_contains($normalized, 'be able') => 'be_able',
            str_contains($normalized, 'encouraged') => 'encouraged',
            str_contains($normalized, 'welcome') => 'welcome',
            str_contains($normalized, 'required') => 'required',
            str_contains($normalized, 'invited') => 'invited',
            str_contains($normalized, 'expected') => 'expected',
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
