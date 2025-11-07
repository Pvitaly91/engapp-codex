<?php

declare(strict_types=1);

namespace Database\Seeders\Ai\Modals;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class ModalVerbsSubthemesLeveledModalsAiSeeder extends QuestionSeeder
{
    private array $levelDifficulty = [
        'A1' => 1,
        'A2' => 2,
        'B1' => 3,
        'B2' => 4,
        'C1' => 5,
        'C2' => 5,
    ];

    private array $subthemes = [
        'ability' => [
            'label' => 'Modal Ability Focus',
            'options' => ['can', 'could', 'be able to', 'should be able to', 'ought to be able to'],
        ],
        'permission' => [
            'label' => 'Modal Permission Focus',
            'options' => ['may', 'can', 'could', "mustn't", 'be allowed to'],
        ],
        'obligation' => [
            'label' => 'Modal Obligation & Necessity',
            'options' => ['must', 'have to', 'need to', "don’t have to", "mustn’t"],
        ],
        'advice' => [
            'label' => 'Modal Advice & Suggestions',
            'options' => ['should', 'ought to', "had better", "shouldn’t", 'could'],
        ],
        'possibility' => [
            'label' => 'Modal Possibility & Probability',
            'options' => ['might', 'may', 'could', 'might have', 'could have'],
        ],
        'deduction' => [
            'label' => 'Modal Deduction & Inference',
            'options' => ['must', "can’t", 'might', 'must have', "can’t have"],
        ],
    ];

    private array $modalPairMap = [
        'can_could' => ['can', "can't", 'could', "couldn’t", 'be able to', 'will be able to', 'should be able to', 'ought to be able to'],
        'may_might' => ['may', 'might', 'may not', 'might not', 'might have', 'may have'],
        'must_have_to' => ['must', "mustn't", 'have to', 'has to', 'had to', "don’t have to", "doesn’t have to"],
        'should_ought_to' => ['should', "shouldn’t", 'should have', "shouldn’t have", 'ought to', 'ought not to'],
        'need_need_to' => ['need to', 'needs to', 'needed to', "needn’t", "needn’t have"],
        'will_would' => ['will', "won’t", 'would', "wouldn’t", 'will be able to'],
        'supposed_to' => ['be supposed to', 'supposed to'],
    ];

    private array $bankQuestions = [
        'ability' => [
            [
                'level' => 'A1',
                'question' => 'He {a1} ride a bike without help.',
                'answers' => [
                    'a1' => ['value' => 'can', 'hint' => 'show ability'],
                ],
                'options' => ['can', 'could', 'may', 'must'],
            ],
            [
                'level' => 'A1',
                'question' => 'We {a1} swim across the river this summer.',
                'answers' => [
                    'a1' => ['value' => 'can', 'hint' => 'confidence'],
                ],
                'options' => ['can', 'could', 'might', 'must'],
            ],
            [
                'level' => 'A2',
                'question' => 'After training, she {a1} {a2} heavy boxes alone.',
                'answers' => [
                    'a1' => ['value' => 'can', 'hint' => 'new skill'],
                    'a2' => ['value' => 'lift', 'hint' => 'raise'],
                ],
                'options' => ['can', 'could', 'lift', 'carry', 'might'],
            ],
            [
                'level' => 'A2',
                'question' => 'The interns {a1} {a2} basic code reviews already.',
                'answers' => [
                    'a1' => ['value' => 'can', 'hint' => 'competence'],
                    'a2' => ['value' => 'perform', 'hint' => 'execute'],
                ],
                'options' => ['can', 'could', 'perform', 'debug', 'might'],
            ],
            [
                'level' => 'B1',
                'question' => 'When I was seven, I {a1} solve riddles in minutes.',
                'answers' => [
                    'a1' => ['value' => 'could', 'hint' => 'past skill'],
                ],
                'options' => ['could', 'can', 'might', 'must'],
            ],
            [
                'level' => 'B1',
                'question' => 'Our team {a1} {a2} multiple tasks simultaneously.',
                'answers' => [
                    'a1' => ['value' => 'can', 'hint' => 'capacity'],
                    'a2' => ['value' => 'manage', 'hint' => 'coordinate'],
                ],
                'options' => ['can', 'could', 'manage', 'delegate', 'might'],
            ],
            [
                'level' => 'B2',
                'question' => 'Next month, the drone will {a1} {a2} parcels to rooftops.',
                'answers' => [
                    'a1' => ['value' => 'be able to', 'hint' => 'future skill'],
                    'a2' => ['value' => 'deliver', 'hint' => 'drop off'],
                ],
                'options' => ['be able to', 'can', 'deliver', 'drop', 'could'],
            ],
            [
                'level' => 'C1',
                'question' => 'Experienced pilots {a1} {a2} turbulent winds calmly.',
                'answers' => [
                    'a1' => ['value' => 'can', 'hint' => 'professional skill'],
                    'a2' => ['value' => 'withstand', 'hint' => 'endure'],
                ],
                'options' => ['can', 'could', 'withstand', 'ignore', 'might'],
            ],
            [
                'level' => 'C1',
                'question' => 'Research teams {a1} {a2} datasets with millions of entries.',
                'answers' => [
                    'a1' => ['value' => 'can', 'hint' => 'capacity'],
                    'a2' => ['value' => 'analyze', 'hint' => 'study'],
                ],
                'options' => ['can', 'could', 'analyze', 'archive', 'might'],
            ],
            [
                'level' => 'C2',
                'question' => 'Within seconds, linguists {a1} {a2} rare dialects accurately.',
                'answers' => [
                    'a1' => ['value' => 'should be able to', 'hint' => 'expected skill'],
                    'a2' => ['value' => 'interpret', 'hint' => 'decode meaning'],
                ],
                'options' => ['should be able to', 'can', 'interpret', 'translate', 'could'],
            ],
        ],
        'permission' => [
            [
                'level' => 'A1',
                'question' => 'Teacher, {a1} I open the window?',
                'answers' => [
                    'a1' => ['value' => 'May', 'hint' => 'polite ask'],
                ],
                'options' => ['May', 'Can', 'Must', 'Should'],
            ],
            [
                'level' => 'A1',
                'question' => 'Kids, you {a1} stay in the library quietly.',
                'answers' => [
                    'a1' => ['value' => 'may', 'hint' => 'allowed'],
                ],
                'options' => ['may', 'must', 'should', 'could'],
            ],
            [
                'level' => 'A2',
                'question' => 'Staff {a1} {a2} personal laptops during breaks.',
                'answers' => [
                    'a1' => ['value' => 'may', 'hint' => 'workplace rule'],
                    'a2' => ['value' => 'use', 'hint' => 'operate'],
                ],
                'options' => ['may', 'can', 'use', 'inspect', "mustn't"],
            ],
            [
                'level' => 'A2',
                'question' => 'Guests {a1} {a2} the rooftop until 10 p.m.',
                'answers' => [
                    'a1' => ['value' => 'may', 'hint' => 'access granted'],
                    'a2' => ['value' => 'access', 'hint' => 'enter'],
                ],
                'options' => ['may', 'can', 'access', 'block', 'must'],
            ],
            [
                'level' => 'B1',
                'question' => 'You {a1} leave early if your work is finished.',
                'answers' => [
                    'a1' => ['value' => 'may', 'hint' => 'supervisor approval'],
                ],
                'options' => ['may', 'must', 'should', 'could'],
            ],
            [
                'level' => 'B1',
                'question' => 'Reporters {a1} {a2} the spokesperson for one follow-up question.',
                'answers' => [
                    'a1' => ['value' => 'may', 'hint' => 'press protocol'],
                    'a2' => ['value' => 'ask', 'hint' => 'request'],
                ],
                'options' => ['may', 'can', 'ask', 'interrupt', 'should'],
            ],
            [
                'level' => 'B2',
                'question' => 'Employees {a1} {a2} remote work twice a week with approval.',
                'answers' => [
                    'a1' => ['value' => 'may', 'hint' => 'formal permission'],
                    'a2' => ['value' => 'request', 'hint' => 'seek'],
                ],
                'options' => ['may', 'can', 'request', 'demand', 'must'],
            ],
            [
                'level' => 'C1',
                'question' => 'Contractors {a1} {a2} safety equipment without signing the log.',
                'answers' => [
                    'a1' => ['value' => "mustn't", 'hint' => 'strict prohibition'],
                    'a2' => ['value' => 'remove', 'hint' => 'take away'],
                ],
                'options' => ["mustn't", 'may', 'remove', 'inspect', 'should'],
            ],
            [
                'level' => 'C1',
                'question' => 'Delegates {a1} {a2} the chair for an additional vote.',
                'answers' => [
                    'a1' => ['value' => 'may', 'hint' => 'formal ask'],
                    'a2' => ['value' => 'petition', 'hint' => 'submit request'],
                ],
                'options' => ['may', 'can', 'petition', 'pressure', 'must'],
            ],
            [
                'level' => 'C2',
                'question' => 'Under the charter, regional directors {a1} {a2} policy amendments with board consent.',
                'answers' => [
                    'a1' => ['value' => 'may', 'hint' => 'charter allowance'],
                    'a2' => ['value' => 'propose', 'hint' => 'suggest'],
                ],
                'options' => ['may', 'can', 'propose', 'impose', 'must'],
            ],
        ],
        'obligation' => [
            [
                'level' => 'A1',
                'question' => 'You {a1} wear a seat belt in the car.',
                'answers' => [
                    'a1' => ['value' => 'must', 'hint' => 'legal rule'],
                ],
                'options' => ['must', 'may', 'can', 'could'],
            ],
            [
                'level' => 'A1',
                'question' => 'Students {a1} hand in homework on Monday.',
                'answers' => [
                    'a1' => ['value' => 'must', 'hint' => 'deadline'],
                ],
                'options' => ['must', 'may', 'should', 'might'],
            ],
            [
                'level' => 'A2',
                'question' => 'Employees {a1} {a2} safety shoes in the workshop.',
                'answers' => [
                    'a1' => ['value' => 'have to', 'hint' => 'external rule'],
                    'a2' => ['value' => 'wear', 'hint' => 'put on'],
                ],
                'options' => ['have to', 'must', 'wear', 'store', 'may'],
            ],
            [
                'level' => 'A2',
                'question' => 'Visitors {a1} {a2} their badges at all times.',
                'answers' => [
                    'a1' => ['value' => 'must', 'hint' => 'security demand'],
                    'a2' => ['value' => 'display', 'hint' => 'show'],
                ],
                'options' => ['must', 'may', 'display', 'hide', 'could'],
            ],
            [
                'level' => 'B1',
                'question' => 'We {a1} finish the audit by Friday.',
                'answers' => [
                    'a1' => ['value' => 'have to', 'hint' => 'deadline'],
                ],
                'options' => ['have to', 'should', 'may', 'might'],
            ],
            [
                'level' => 'B1',
                'question' => 'You {a1} {a2} the alarm if you see smoke.',
                'answers' => [
                    'a1' => ['value' => 'must', 'hint' => 'safety rule'],
                    'a2' => ['value' => 'activate', 'hint' => 'turn on'],
                ],
                'options' => ['must', 'may', 'activate', 'silence', 'should'],
            ],
            [
                'level' => 'B2',
                'question' => 'Managers {a1} {a2} staff schedules weekly.',
                'answers' => [
                    'a1' => ['value' => 'need to', 'hint' => 'regular duty'],
                    'a2' => ['value' => 'review', 'hint' => 'check'],
                ],
                'options' => ['need to', 'may', 'review', 'skip', 'could'],
            ],
            [
                'level' => 'C1',
                'question' => 'Contractors {a1} {a2} compliance reports quarterly.',
                'answers' => [
                    'a1' => ['value' => 'must', 'hint' => 'contract term'],
                    'a2' => ['value' => 'submit', 'hint' => 'hand in'],
                ],
                'options' => ['must', 'may', 'submit', 'delay', 'could'],
            ],
            [
                'level' => 'C1',
                'question' => 'Researchers {a1} {a2} anomalies immediately.',
                'answers' => [
                    'a1' => ['value' => 'must', 'hint' => 'protocol'],
                    'a2' => ['value' => 'report', 'hint' => 'notify'],
                ],
                'options' => ['must', 'may', 'report', 'ignore', 'could'],
            ],
            [
                'level' => 'C2',
                'question' => 'Under international law, states {a1} {a2} environmental data transparently.',
                'answers' => [
                    'a1' => ['value' => 'must', 'hint' => 'treaty duty'],
                    'a2' => ['value' => 'share', 'hint' => 'disclose'],
                ],
                'options' => ['must', 'may', 'share', 'withhold', 'could'],
            ],
        ],
        'advice' => [
            [
                'level' => 'A1',
                'question' => 'You {a1} drink more water.',
                'answers' => [
                    'a1' => ['value' => 'should', 'hint' => 'healthy tip'],
                ],
                'options' => ['should', 'must', 'may', 'can'],
            ],
            [
                'level' => 'A1',
                'question' => 'He {a1} rest if he feels sick.',
                'answers' => [
                    'a1' => ['value' => 'should', 'hint' => 'caring advice'],
                ],
                'options' => ['should', 'must', 'might', 'can'],
            ],
            [
                'level' => 'A2',
                'question' => 'You {a1} {a2} a jacket; it gets cold at night.',
                'answers' => [
                    'a1' => ['value' => 'should', 'hint' => 'recommendation'],
                    'a2' => ['value' => 'bring', 'hint' => 'carry'],
                ],
                'options' => ['should', 'must', 'bring', 'forget', 'might'],
            ],
            [
                'level' => 'A2',
                'question' => 'They {a1} {a2} junk food so often.',
                'answers' => [
                    'a1' => ['value' => "shouldn't", 'hint' => 'gentle warning'],
                    'a2' => ['value' => 'eat', 'hint' => 'consume'],
                ],
                'options' => ["shouldn't", 'should', 'eat', 'buy', 'might'],
            ],
            [
                'level' => 'B1',
                'question' => 'You {a1} {a2} your notes before the exam.',
                'answers' => [
                    'a1' => ['value' => 'ought to', 'hint' => 'strong advice'],
                    'a2' => ['value' => 'review', 'hint' => 'look over'],
                ],
                'options' => ['ought to', 'should', 'review', 'ignore', 'might'],
            ],
            [
                'level' => 'B1',
                'question' => 'He {a1} apologize for the mistake.',
                'answers' => [
                    'a1' => ['value' => 'should', 'hint' => 'social advice'],
                ],
                'options' => ['should', 'must', 'may', 'could'],
            ],
            [
                'level' => 'B2',
                'question' => 'You {a1} {a2} a second opinion before investing.',
                'answers' => [
                    'a1' => ['value' => 'ought to', 'hint' => 'prudent step'],
                    'a2' => ['value' => 'seek', 'hint' => 'look for'],
                ],
                'options' => ['ought to', 'should', 'seek', 'skip', 'might'],
            ],
            [
                'level' => 'C1',
                'question' => 'We {a1} {a2} the contract clauses to avoid disputes.',
                'answers' => [
                    'a1' => ['value' => 'should', 'hint' => 'prevent issue'],
                    'a2' => ['value' => 'clarify', 'hint' => 'make clear'],
                ],
                'options' => ['should', 'must', 'clarify', 'ignore', 'might'],
            ],
            [
                'level' => 'C1',
                'question' => 'She {a1} {a2} the team about the delay sooner.',
                'answers' => [
                    'a1' => ['value' => 'should have', 'hint' => 'missed advice'],
                    'a2' => ['value' => 'told', 'hint' => 'informed'],
                ],
                'options' => ['should have', 'must have', 'told', 'hidden', 'might have'],
            ],
            [
                'level' => 'C2',
                'question' => 'You {a1} {a2} promises you cannot keep.',
                'answers' => [
                    'a1' => ['value' => 'should never', 'hint' => 'strong warning'],
                    'a2' => ['value' => 'make', 'hint' => 'offer'],
                ],
                'options' => ['should never', 'should', 'make', 'delay', 'might'],
            ],
        ],
        'possibility' => [
            [
                'level' => 'A1',
                'question' => 'It {a1} rain later.',
                'answers' => [
                    'a1' => ['value' => 'might', 'hint' => 'uncertain'],
                ],
                'options' => ['might', 'must', 'can', 'should'],
            ],
            [
                'level' => 'A1',
                'question' => 'She {a1} be at home now.',
                'answers' => [
                    'a1' => ['value' => 'may', 'hint' => 'uncertain'],
                ],
                'options' => ['may', 'must', 'can', 'should'],
            ],
            [
                'level' => 'A2',
                'question' => 'They {a1} {a2} the train if traffic is light.',
                'answers' => [
                    'a1' => ['value' => 'might', 'hint' => 'possibility'],
                    'a2' => ['value' => 'catch', 'hint' => 'make it'],
                ],
                'options' => ['might', 'may', 'catch', 'miss', 'must'],
            ],
            [
                'level' => 'A2',
                'question' => 'He {a1} {a2} the meeting tonight.',
                'answers' => [
                    'a1' => ['value' => 'might', 'hint' => 'uncertain'],
                    'a2' => ['value' => 'skip', 'hint' => 'miss on purpose'],
                ],
                'options' => ['might', 'may', 'skip', 'attend', 'must'],
            ],
            [
                'level' => 'B1',
                'question' => 'You {a1} {a2} the answer in the manual.',
                'answers' => [
                    'a1' => ['value' => 'might', 'hint' => 'possible'],
                    'a2' => ['value' => 'find', 'hint' => 'locate'],
                ],
                'options' => ['might', 'must', 'find', 'lose', 'should'],
            ],
            [
                'level' => 'B1',
                'question' => 'The package {a1} arrive tomorrow.',
                'answers' => [
                    'a1' => ['value' => 'might', 'hint' => 'uncertain'],
                ],
                'options' => ['might', 'must', 'can', 'should'],
            ],
            [
                'level' => 'B2',
                'question' => 'She {a1} {a2} the bus; the weather slowed traffic.',
                'answers' => [
                    'a1' => ['value' => 'might have', 'hint' => 'past possibility'],
                    'a2' => ['value' => 'missed', 'hint' => 'failed to catch'],
                ],
                'options' => ['might have', 'must have', 'missed', 'caught', 'could have'],
            ],
            [
                'level' => 'B2',
                'question' => 'They {a1} {a2} the update by now.',
                'answers' => [
                    'a1' => ['value' => 'may have', 'hint' => 'past possibility'],
                    'a2' => ['value' => 'released', 'hint' => 'sent out'],
                ],
                'options' => ['may have', 'must have', 'released', 'paused', 'might have'],
            ],
            [
                'level' => 'C1',
                'question' => 'The data {a1} {a2} a pattern we have not seen yet.',
                'answers' => [
                    'a1' => ['value' => 'might', 'hint' => 'possible'],
                    'a2' => ['value' => 'suggest', 'hint' => 'hint at'],
                ],
                'options' => ['might', 'must', 'suggest', 'confirm', 'should'],
            ],
            [
                'level' => 'C2',
                'question' => 'Analysts {a1} overlooked the anomaly.',
                'answers' => [
                    'a1' => ['value' => 'could have', 'hint' => 'past possibility'],
                ],
                'options' => ['could have', 'must have', 'should have', 'may'],
            ],
        ],
    ];

    private array $leveledQuestions = [
        'ability' => [
            'A1' => [
                [
                    'question' => 'I {a1} use a computer.',
                    'answers' => [
                        'a1' => ['value' => 'can', 'hint' => 'basic skill'],
                    ],
                    'options' => ['can', 'could', 'may', 'must'],
                ],
                [
                    'question' => 'She {a1} draw simple maps.',
                    'answers' => [
                        'a1' => ['value' => 'can', 'hint' => 'simple ability'],
                    ],
                    'options' => ['can', 'could', 'might', 'must'],
                ],
                [
                    'question' => 'They {a1} climb small hills.',
                    'answers' => [
                        'a1' => ['value' => 'can', 'hint' => 'outdoor skill'],
                    ],
                    'options' => ['can', 'could', 'may', 'should'],
                ],
                [
                    'question' => 'My brother {a1} cook soup.',
                    'answers' => [
                        'a1' => ['value' => 'can', 'hint' => 'kitchen skill'],
                    ],
                    'options' => ['can', 'could', 'may', 'must'],
                ],
                [
                    'question' => 'We {a1} play the drums a little.',
                    'answers' => [
                        'a1' => ['value' => 'can', 'hint' => 'hobby skill'],
                    ],
                    'options' => ['can', 'could', 'may', 'must'],
                ],
                [
                    'question' => 'The baby {a1} crawl quickly now.',
                    'answers' => [
                        'a1' => ['value' => 'can', 'hint' => 'new milestone'],
                    ],
                    'options' => ['can', 'could', 'may', 'must'],
                ],
            ],
            'A2' => [
                [
                    'question' => 'She {a1} {a2} long distances without stopping now.',
                    'answers' => [
                        'a1' => ['value' => 'can', 'hint' => 'stamina'],
                        'a2' => ['value' => 'run', 'hint' => 'dash'],
                    ],
                    'options' => ['can', 'could', 'run', 'pause', 'might'],
                ],
                [
                    'question' => 'We {a1} {a2} short dialogues in Italian after lessons.',
                    'answers' => [
                        'a1' => ['value' => 'can', 'hint' => 'practice'],
                        'a2' => ['value' => 'perform', 'hint' => 'act out'],
                    ],
                    'options' => ['can', 'could', 'perform', 'translate', 'might'],
                ],
                [
                    'question' => 'Do you think he {a1} repair the printer alone?',
                    'answers' => [
                        'a1' => ['value' => 'can', 'hint' => 'self-fix'],
                    ],
                    'options' => ['can', 'could', 'may', 'should'],
                ],
                [
                    'question' => 'My grandparents {a1} {a2} emails on their own.',
                    'answers' => [
                        'a1' => ['value' => 'can', 'hint' => 'confidence'],
                        'a2' => ['value' => 'compose', 'hint' => 'draft'],
                    ],
                    'options' => ['can', 'could', 'compose', 'forward', 'might'],
                ],
                [
                    'question' => 'The volunteers {a1} {a2} first-aid drills confidently.',
                    'answers' => [
                        'a1' => ['value' => 'can', 'hint' => 'trained'],
                        'a2' => ['value' => 'perform', 'hint' => 'execute'],
                    ],
                    'options' => ['can', 'could', 'perform', 'skip', 'might'],
                ],
                [
                    'question' => 'New divers {a1} equalize their ears gently now.',
                    'answers' => [
                        'a1' => ['value' => 'can', 'hint' => 'underwater skill'],
                    ],
                    'options' => ['can', 'could', 'may', 'must'],
                ],
            ],
            'B1' => [
                [
                    'question' => 'Before the upgrade, the laptop {a1} {a2} heavy simulations.',
                    'answers' => [
                        'a1' => ['value' => 'could', 'hint' => 'past capability'],
                        'a2' => ['value' => 'run', 'hint' => 'execute'],
                    ],
                    'options' => ['could', 'can', 'run', 'launch', 'might'],
                ],
                [
                    'question' => 'We {a1} troubleshoot network issues without help now.',
                    'answers' => [
                        'a1' => ['value' => 'can', 'hint' => 'diagnose'],
                    ],
                    'options' => ['can', 'could', 'may', 'must'],
                ],
                [
                    'question' => 'When he was young, he {a1} memorize entire plays.',
                    'answers' => [
                        'a1' => ['value' => 'could', 'hint' => 'past memory'],
                    ],
                    'options' => ['could', 'can', 'might', 'must'],
                ],
                [
                    'question' => 'Project leads {a1} {a2} overlapping schedules successfully.',
                    'answers' => [
                        'a1' => ['value' => 'can', 'hint' => 'coordinate'],
                        'a2' => ['value' => 'manage', 'hint' => 'arrange'],
                    ],
                    'options' => ['can', 'could', 'manage', 'avoid', 'might'],
                ],
                [
                    'question' => 'After installing the patch, users {a1} {a2} encrypted backups.',
                    'answers' => [
                        'a1' => ['value' => 'can', 'hint' => 'access'],
                        'a2' => ['value' => 'restore', 'hint' => 'recover'],
                    ],
                    'options' => ['can', 'could', 'restore', 'ignore', 'might'],
                ],
                [
                    'question' => 'Skaters {a1} {a2} quadruple jumps this season.',
                    'answers' => [
                        'a1' => ['value' => 'can', 'hint' => 'skillful'],
                        'a2' => ['value' => 'land', 'hint' => 'complete'],
                    ],
                    'options' => ['can', 'could', 'land', 'attempt', 'might'],
                ],
            ],
            'B2' => [
                [
                    'question' => 'New analysts will {a1} {a2} complex forecasts by winter.',
                    'answers' => [
                        'a1' => ['value' => 'be able to', 'hint' => 'future ability'],
                        'a2' => ['value' => 'produce', 'hint' => 'create'],
                    ],
                    'options' => ['be able to', 'can', 'produce', 'revise', 'could'],
                ],
                [
                    'question' => 'In emergencies, coordinators {a1} {a2} simultaneous alerts.',
                    'answers' => [
                        'a1' => ['value' => 'can', 'hint' => 'respond'],
                        'a2' => ['value' => 'process', 'hint' => 'handle'],
                    ],
                    'options' => ['can', 'could', 'process', 'delay', 'might'],
                ],
                [
                    'question' => 'By rehearsal time, dancers will {a1} {a2} aerial turns.',
                    'answers' => [
                        'a1' => ['value' => 'will be able to', 'hint' => 'future skill'],
                        'a2' => ['value' => 'execute', 'hint' => 'carry out'],
                    ],
                    'options' => ['will be able to', 'can', 'execute', 'review', 'could'],
                ],
                [
                    'question' => 'When focused, I {a1} {a2} detailed sketches quickly.',
                    'answers' => [
                        'a1' => ['value' => 'could', 'hint' => 'past potential'],
                        'a2' => ['value' => 'produce', 'hint' => 'generate'],
                    ],
                    'options' => ['could', 'can', 'produce', 'erase', 'might'],
                ],
                [
                    'question' => 'After the firmware patch, drones {a1} {a2} obstacles autonomously.',
                    'answers' => [
                        'a1' => ['value' => 'can', 'hint' => 'navigate'],
                        'a2' => ['value' => 'avoid', 'hint' => 'steer clear'],
                    ],
                    'options' => ['can', 'could', 'avoid', 'trigger', 'might'],
                ],
                [
                    'question' => 'Experienced climbers {a1} {a2} icy ledges in darkness.',
                    'answers' => [
                        'a1' => ['value' => 'can', 'hint' => 'competence'],
                        'a2' => ['value' => 'navigate', 'hint' => 'find path'],
                    ],
                    'options' => ['can', 'could', 'navigate', 'hesitate', 'might'],
                ],
            ],
            'C1' => [
                [
                    'question' => 'Specialists {a1} {a2} multilingual negotiations without interpreters.',
                    'answers' => [
                        'a1' => ['value' => 'can', 'hint' => 'expertise'],
                        'a2' => ['value' => 'conduct', 'hint' => 'run'],
                    ],
                    'options' => ['can', 'could', 'conduct', 'delay', 'might'],
                ],
                [
                    'question' => 'Veteran firefighters {a1} {a2} collapsed beams safely.',
                    'answers' => [
                        'a1' => ['value' => 'can', 'hint' => 'trained'],
                        'a2' => ['value' => 'stabilize', 'hint' => 'secure'],
                    ],
                    'options' => ['can', 'could', 'stabilize', 'ignore', 'might'],
                ],
                [
                    'question' => 'The research AI {a1} {a2} voiceprints with 99% accuracy.',
                    'answers' => [
                        'a1' => ['value' => 'can', 'hint' => 'technical skill'],
                        'a2' => ['value' => 'identify', 'hint' => 'spot'],
                    ],
                    'options' => ['can', 'could', 'identify', 'distort', 'might'],
                ],
                [
                    'question' => 'Consultants {a1} {a2} huge datasets in real time.',
                    'answers' => [
                        'a1' => ['value' => 'can', 'hint' => 'capable'],
                        'a2' => ['value' => 'synthesize', 'hint' => 'combine'],
                    ],
                    'options' => ['can', 'could', 'synthesize', 'abandon', 'might'],
                ],
                [
                    'question' => 'After certification, surgeons {a1} {a2} complex transplants.',
                    'answers' => [
                        'a1' => ['value' => 'can', 'hint' => 'qualified'],
                        'a2' => ['value' => 'perform', 'hint' => 'carry out'],
                    ],
                    'options' => ['can', 'could', 'perform', 'delay', 'might'],
                ],
                [
                    'question' => 'He {a1} {a2} theoretical proofs under pressure.',
                    'answers' => [
                        'a1' => ['value' => 'can', 'hint' => 'mental agility'],
                        'a2' => ['value' => 'derive', 'hint' => 'work out'],
                    ],
                    'options' => ['can', 'could', 'derive', 'question', 'might'],
                ],
            ],
            'C2' => [
                [
                    'question' => 'Seasoned linguists {a1} {a2} extinct grammar patterns intuitively.',
                    'answers' => [
                        'a1' => ['value' => 'should be able to', 'hint' => 'expected mastery'],
                        'a2' => ['value' => 'reconstruct', 'hint' => 'rebuild'],
                    ],
                    'options' => ['should be able to', 'can', 'reconstruct', 'guess', 'could'],
                ],
                [
                    'question' => 'Given archival footage, analysts {a1} {a2} micro-expressions flawlessly.',
                    'answers' => [
                        'a1' => ['value' => 'ought to be able to', 'hint' => 'anticipated skill'],
                        'a2' => ['value' => 'decode', 'hint' => 'unpack'],
                    ],
                    'options' => ['ought to be able to', 'can', 'decode', 'dismiss', 'could'],
                ],
                [
                    'question' => 'In theoretical scenarios, pilots {a1} {a2} blind approaches on instinct.',
                    'answers' => [
                        'a1' => ['value' => 'might be able to', 'hint' => 'possible skill'],
                        'a2' => ['value' => 'attempt', 'hint' => 'try'],
                    ],
                    'options' => ['might be able to', 'can', 'attempt', 'avoid', 'could'],
                ],
                [
                    'question' => 'Once calibrated, the robot {a1} {a2} intricate surgical motions unaided.',
                    'answers' => [
                        'a1' => ['value' => 'will be able to', 'hint' => 'future competence'],
                        'a2' => ['value' => 'replicate', 'hint' => 'copy'],
                    ],
                    'options' => ['will be able to', 'can', 'replicate', 'halt', 'could'],
                ],
                [
                    'question' => 'Grandmasters {a1} {a2} nine simultaneous matches without notes.',
                    'answers' => [
                        'a1' => ['value' => 'can', 'hint' => 'elite skill'],
                        'a2' => ['value' => 'coordinate', 'hint' => 'handle'],
                    ],
                    'options' => ['can', 'could', 'coordinate', 'stumble', 'might'],
                ],
                [
                    'question' => 'Top analysts {a1} {a2} contradictory evidence without bias.',
                    'answers' => [
                        'a1' => ['value' => 'are expected to be able to', 'hint' => 'duty to manage'],
                        'a2' => ['value' => 'reconcile', 'hint' => 'harmonize'],
                    ],
                    'options' => ['are expected to be able to', 'can', 'reconcile', 'ignore', 'could'],
                ],
            ],
        ],
        'permission' => [
            'A1' => [
                [
                    'question' => 'Parents, {a1} we sit here?',
                    'answers' => [
                        'a1' => ['value' => 'Can', 'hint' => 'friendly request'],
                    ],
                    'options' => ['Can', 'May', 'Must', 'Should'],
                ],
                [
                    'question' => 'You {a1} go outside after lunch.',
                    'answers' => [
                        'a1' => ['value' => 'may', 'hint' => 'allowed now'],
                    ],
                    'options' => ['may', 'must', 'should', 'could'],
                ],
                [
                    'question' => 'Excuse me, {a1} I borrow your scissors?',
                    'answers' => [
                        'a1' => ['value' => 'May', 'hint' => 'polite tone'],
                    ],
                    'options' => ['May', 'Can', 'Must', 'Might'],
                ],
                [
                    'question' => 'Students {a1} stay in the gym until the bell.',
                    'answers' => [
                        'a1' => ['value' => 'can', 'hint' => 'permitted'],
                    ],
                    'options' => ['can', 'must', 'should', 'might'],
                ],
                [
                    'question' => 'Visitors {a1} enter at noon.',
                    'answers' => [
                        'a1' => ['value' => 'may', 'hint' => 'opening time'],
                    ],
                    'options' => ['may', 'must', 'should', 'could'],
                ],
                [
                    'question' => 'Little Tim, you {a1} play on the swings now.',
                    'answers' => [
                        'a1' => ['value' => 'can', 'hint' => 'permission granted'],
                    ],
                    'options' => ['can', 'may', 'must', 'might'],
                ],
            ],
            'A2' => [
                [
                    'question' => 'Employees {a1} {a2} personal calls during breaks.',
                    'answers' => [
                        'a1' => ['value' => 'may', 'hint' => 'policy'],
                        'a2' => ['value' => 'make', 'hint' => 'dial'],
                    ],
                    'options' => ['may', 'can', 'make', 'delay', 'must'],
                ],
                [
                    'question' => 'Could we {a1} the lab after 5 p.m.?',
                    'answers' => [
                        'a1' => ['value' => 'use', 'hint' => 'access'],
                    ],
                    'options' => ['use', 'enter', 'borrow', 'close', 'lock'],
                ],
                [
                    'question' => 'Students {a1} {a2} snacks in the lounge now.',
                    'answers' => [
                        'a1' => ['value' => 'may', 'hint' => 'rule update'],
                        'a2' => ['value' => 'bring', 'hint' => 'carry'],
                    ],
                    'options' => ['may', 'can', 'bring', 'ban', 'must'],
                ],
                [
                    'question' => 'Residents {a1} use the garden after dusk.',
                    'answers' => [
                        'a1' => ['value' => 'may not', 'hint' => 'restriction'],
                    ],
                    'options' => ['may not', 'may', 'must', 'should'],
                ],
                [
                    'question' => 'Library members {a1} renew books online.',
                    'answers' => [
                        'a1' => ['value' => 'can', 'hint' => 'service option'],
                    ],
                    'options' => ['can', 'may', 'must', 'might'],
                ],
                [
                    'question' => 'Guests {a1} {a2} quiet music in the lobby.',
                    'answers' => [
                        'a1' => ['value' => 'may', 'hint' => 'allowed'],
                        'a2' => ['value' => 'play', 'hint' => 'perform softly'],
                    ],
                    'options' => ['may', 'can', 'play', 'mute', 'must'],
                ],
            ],
            'B1' => [
                [
                    'question' => 'You {a1} {a2} the meeting notes with the team.',
                    'answers' => [
                        'a1' => ['value' => 'may', 'hint' => 'authorised'],
                        'a2' => ['value' => 'share', 'hint' => 'distribute'],
                    ],
                    'options' => ['may', 'can', 'share', 'withhold', 'must'],
                ],
                [
                    'question' => 'Journalists {a1} {a2} inside the lobby before 8 a.m.',
                    'answers' => [
                        'a1' => ['value' => 'may', 'hint' => 'press access'],
                        'a2' => ['value' => 'film', 'hint' => 'record'],
                    ],
                    'options' => ['may', 'can', 'film', 'leave', 'must'],
                ],
                [
                    'question' => 'If the manager agrees, we {a1} schedule a shorter shift.',
                    'answers' => [
                        'a1' => ['value' => 'may', 'hint' => 'conditional approval'],
                    ],
                    'options' => ['may', 'must', 'should', 'could'],
                ],
                [
                    'question' => 'Could I {a1} the reserved workstation?',
                    'answers' => [
                        'a1' => ['value' => 'use', 'hint' => 'occupation'],
                    ],
                    'options' => ['use', 'move', 'lock', 'buy', 'swap'],
                ],
                [
                    'question' => 'Residents {a1} {a2} guests overnight without registering.',
                    'answers' => [
                        'a1' => ['value' => 'may not', 'hint' => 'policy limit'],
                        'a2' => ['value' => 'host', 'hint' => 'accommodate'],
                    ],
                    'options' => ['may not', 'may', 'host', 'dismiss', 'must'],
                ],
                [
                    'question' => 'Members {a1} {a2} the studio on weekends with prior notice.',
                    'answers' => [
                        'a1' => ['value' => 'may', 'hint' => 'club rule'],
                        'a2' => ['value' => 'book', 'hint' => 'reserve'],
                    ],
                    'options' => ['may', 'can', 'book', 'cancel', 'must'],
                ],
            ],
            'B2' => [
                [
                    'question' => 'Contract staff {a1} {a2} remote access once the security check clears.',
                    'answers' => [
                        'a1' => ['value' => 'may', 'hint' => 'authorization'],
                        'a2' => ['value' => 'request', 'hint' => 'ask formally'],
                    ],
                    'options' => ['may', 'can', 'request', 'enforce', 'must'],
                ],
                [
                    'question' => 'Teams {a1} {a2} budget reallocation with written consent.',
                    'answers' => [
                        'a1' => ['value' => 'may', 'hint' => 'financial approval'],
                        'a2' => ['value' => 'seek', 'hint' => 'pursue'],
                    ],
                    'options' => ['may', 'can', 'seek', 'ignore', 'must'],
                ],
                [
                    'question' => 'Passengers {a1} {a2} the premium lounge with a day pass.',
                    'answers' => [
                        'a1' => ['value' => 'may', 'hint' => 'travel policy'],
                        'a2' => ['value' => 'enter', 'hint' => 'go inside'],
                    ],
                    'options' => ['may', 'can', 'enter', 'vacate', 'must'],
                ],
                [
                    'question' => 'Without clearance, technicians {a1} {a2} sealed components.',
                    'answers' => [
                        'a1' => ['value' => 'must not', 'hint' => 'ban'],
                        'a2' => ['value' => 'open', 'hint' => 'unseal'],
                    ],
                    'options' => ['must not', 'may', 'open', 'inspect', 'should'],
                ],
                [
                    'question' => 'Supervisors {a1} {a2} flexible shifts for their teams.',
                    'answers' => [
                        'a1' => ['value' => 'may', 'hint' => 'management choice'],
                        'a2' => ['value' => 'approve', 'hint' => 'confirm'],
                    ],
                    'options' => ['may', 'can', 'approve', 'refuse', 'must'],
                ],
                [
                    'question' => 'According to policy, interns {a1} {a2} sensitive client data.',
                    'answers' => [
                        'a1' => ['value' => 'may not', 'hint' => 'restriction'],
                        'a2' => ['value' => 'handle', 'hint' => 'process'],
                    ],
                    'options' => ['may not', 'may', 'handle', 'forward', 'must'],
                ],
            ],
            'C1' => [
                [
                    'question' => 'Consultants {a1} {a2} confidential memos only in secure rooms.',
                    'answers' => [
                        'a1' => ['value' => 'may', 'hint' => 'protocol'],
                        'a2' => ['value' => 'review', 'hint' => 'study'],
                    ],
                    'options' => ['may', 'can', 'review', 'copy', 'must'],
                ],
                [
                    'question' => 'Delegates {a1} {a2} private sessions with the mediator.',
                    'answers' => [
                        'a1' => ['value' => 'may', 'hint' => 'formal allowance'],
                        'a2' => ['value' => 'request', 'hint' => 'apply for'],
                    ],
                    'options' => ['may', 'can', 'request', 'demand', 'must'],
                ],
                [
                    'question' => 'Auditors {a1} {a2} backups without the compliance officer present.',
                    'answers' => [
                        'a1' => ['value' => 'must not', 'hint' => 'strict ban'],
                        'a2' => ['value' => 'inspect', 'hint' => 'examine'],
                    ],
                    'options' => ['must not', 'may', 'inspect', 'ignore', 'should'],
                ],
                [
                    'question' => 'Team leads {a1} {a2} an exception by notifying legal counsel.',
                    'answers' => [
                        'a1' => ['value' => 'may', 'hint' => 'procedural'],
                        'a2' => ['value' => 'grant', 'hint' => 'allow'],
                    ],
                    'options' => ['may', 'can', 'grant', 'deny', 'must'],
                ],
                [
                    'question' => 'Researchers {a1} {a2} test subjects without signed consent.',
                    'answers' => [
                        'a1' => ['value' => 'may not', 'hint' => 'ethical ban'],
                        'a2' => ['value' => 'contact', 'hint' => 'reach'],
                    ],
                    'options' => ['may not', 'may', 'contact', 'avoid', 'must'],
                ],
                [
                    'question' => 'Editors {a1} {a2} embargoed articles before the release date.',
                    'answers' => [
                        'a1' => ['value' => 'may not', 'hint' => 'publication rule'],
                        'a2' => ['value' => 'publish', 'hint' => 'release'],
                    ],
                    'options' => ['may not', 'may', 'publish', 'draft', 'must'],
                ],
            ],
            'C2' => [
                [
                    'question' => 'Under the charter, governors {a1} {a2} extraordinary meetings upon petition.',
                    'answers' => [
                        'a1' => ['value' => 'may', 'hint' => 'statutory power'],
                        'a2' => ['value' => 'convene', 'hint' => 'call'],
                    ],
                    'options' => ['may', 'can', 'convene', 'delay', 'must'],
                ],
                [
                    'question' => 'Unless overridden, directors {a1} {a2} interim guidelines for local offices.',
                    'answers' => [
                        'a1' => ['value' => 'may', 'hint' => 'discretion'],
                        'a2' => ['value' => 'issue', 'hint' => 'publish'],
                    ],
                    'options' => ['may', 'can', 'issue', 'withdraw', 'must'],
                ],
                [
                    'question' => 'Security officers {a1} {a2} biometric data without a warrant.',
                    'answers' => [
                        'a1' => ['value' => 'shall not', 'hint' => 'legal ban'],
                        'a2' => ['value' => 'collect', 'hint' => 'gather'],
                    ],
                    'options' => ['shall not', 'may', 'collect', 'ignore', 'must'],
                ],
                [
                    'question' => 'By statute, citizens {a1} {a2} policy drafts during consultation periods.',
                    'answers' => [
                        'a1' => ['value' => 'may', 'hint' => 'civic right'],
                        'a2' => ['value' => 'examine', 'hint' => 'review'],
                    ],
                    'options' => ['may', 'can', 'examine', 'conceal', 'must'],
                ],
                [
                    'question' => 'Diplomats {a1} {a2} cultural exemptions when protocols clash.',
                    'answers' => [
                        'a1' => ['value' => 'may', 'hint' => 'diplomatic option'],
                        'a2' => ['value' => 'request', 'hint' => 'seek'],
                    ],
                    'options' => ['may', 'can', 'request', 'refuse', 'must'],
                ],
                [
                    'question' => 'Arbitrators {a1} {a2} sealed evidence if both parties agree.',
                    'answers' => [
                        'a1' => ['value' => 'may', 'hint' => 'mediated allowance'],
                        'a2' => ['value' => 'review', 'hint' => 'study'],
                    ],
                    'options' => ['may', 'can', 'review', 'suppress', 'must'],
                ],
            ],
        ],
        'obligation' => [
            'A1' => [
                [
                    'question' => 'I {a1} tidy my room every day.',
                    'answers' => [
                        'a1' => ['value' => 'must', 'hint' => 'house rule'],
                    ],
                    'options' => ['must', 'may', 'can', 'might'],
                ],
                [
                    'question' => 'We {a1} arrive on time for class.',
                    'answers' => [
                        'a1' => ['value' => 'have to', 'hint' => 'punctuality'],
                    ],
                    'options' => ['have to', 'can', 'might', 'may'],
                ],
                [
                    'question' => 'You {a1} do your chores before dinner.',
                    'answers' => [
                        'a1' => ['value' => 'must', 'hint' => 'parent rule'],
                    ],
                    'options' => ['must', 'may', 'could', 'might'],
                ],
                [
                    'question' => 'They {a1} listen to the teacher carefully.',
                    'answers' => [
                        'a1' => ['value' => 'must', 'hint' => 'classroom rule'],
                    ],
                    'options' => ['must', 'may', 'can', 'might'],
                ],
                [
                    'question' => 'He {a1} brush his teeth twice a day.',
                    'answers' => [
                        'a1' => ['value' => 'has to', 'hint' => 'health routine'],
                    ],
                    'options' => ['has to', 'may', 'should', 'could'],
                ],
                [
                    'question' => 'She {a1} feed the cat every morning.',
                    'answers' => [
                        'a1' => ['value' => 'must', 'hint' => 'pet care'],
                    ],
                    'options' => ['must', 'may', 'can', 'might'],
                ],
            ],
            'A2' => [
                [
                    'question' => 'We {a1} {a2} our ID cards at the entrance.',
                    'answers' => [
                        'a1' => ['value' => 'have to', 'hint' => 'security'],
                        'a2' => ['value' => 'show', 'hint' => 'present'],
                    ],
                    'options' => ['have to', 'must', 'show', 'pocket', 'may'],
                ],
                [
                    'question' => 'I {a1} finish this form tonight.',
                    'answers' => [
                        'a1' => ['value' => 'have to', 'hint' => 'deadline'],
                    ],
                    'options' => ['have to', 'may', 'could', 'might'],
                ],
                [
                    'question' => 'Drivers {a1} {a2} seat belts in the city.',
                    'answers' => [
                        'a1' => ['value' => 'must', 'hint' => 'law'],
                        'a2' => ['value' => 'wear', 'hint' => 'put on'],
                    ],
                    'options' => ['must', 'may', 'wear', 'fold', 'might'],
                ],
                [
                    'question' => 'Volunteers {a1} {a2} the classroom after the event.',
                    'answers' => [
                        'a1' => ['value' => 'must', 'hint' => 'cleanup duty'],
                        'a2' => ['value' => 'clean', 'hint' => 'tidy'],
                    ],
                    'options' => ['must', 'may', 'clean', 'scatter', 'might'],
                ],
                [
                    'question' => 'You {a1} come to the meeting at nine.',
                    'answers' => [
                        'a1' => ['value' => 'have to', 'hint' => 'schedule'],
                    ],
                    'options' => ['have to', 'may', 'could', 'might'],
                ],
                [
                    'question' => 'Employees {a1} {a2} the training video each spring.',
                    'answers' => [
                        'a1' => ['value' => 'must', 'hint' => 'policy'],
                        'a2' => ['value' => 'watch', 'hint' => 'view'],
                    ],
                    'options' => ['must', 'may', 'watch', 'skip', 'might'],
                ],
            ],
            'B1' => [
                [
                    'question' => 'Team leads {a1} {a2} progress reports every Friday.',
                    'answers' => [
                        'a1' => ['value' => 'have to', 'hint' => 'routine'],
                        'a2' => ['value' => 'submit', 'hint' => 'turn in'],
                    ],
                    'options' => ['have to', 'may', 'submit', 'delay', 'might'],
                ],
                [
                    'question' => 'We {a1} {a2} the clients within 24 hours.',
                    'answers' => [
                        'a1' => ['value' => 'must', 'hint' => 'service standard'],
                        'a2' => ['value' => 'update', 'hint' => 'inform'],
                    ],
                    'options' => ['must', 'may', 'update', 'ignore', 'could'],
                ],
                [
                    'question' => 'You {a1} {a2} the evacuation plan.',
                    'answers' => [
                        'a1' => ['value' => 'must', 'hint' => 'safety'],
                        'a2' => ['value' => 'follow', 'hint' => 'adhere'],
                    ],
                    'options' => ['must', 'may', 'follow', 'draft', 'might'],
                ],
                [
                    'question' => 'Contractors {a1} {a2} expired passes immediately.',
                    'answers' => [
                        'a1' => ['value' => 'must', 'hint' => 'security'],
                        'a2' => ['value' => 'return', 'hint' => 'hand back'],
                    ],
                    'options' => ['must', 'may', 'return', 'keep', 'might'],
                ],
                [
                    'question' => 'Auditors {a1} {a2} each invoice carefully.',
                    'answers' => [
                        'a1' => ['value' => 'need to', 'hint' => 'diligence'],
                        'a2' => ['value' => 'check', 'hint' => 'examine'],
                    ],
                    'options' => ['need to', 'may', 'check', 'skip', 'could'],
                ],
                [
                    'question' => 'I {a1} attend the briefing tomorrow.',
                    'answers' => [
                        'a1' => ['value' => 'have to', 'hint' => 'obligation'],
                    ],
                    'options' => ['have to', 'may', 'could', 'might'],
                ],
            ],
            'B2' => [
                [
                    'question' => 'Supervisors {a1} {a2} compliance logs for every shift.',
                    'answers' => [
                        'a1' => ['value' => 'must', 'hint' => 'oversight'],
                        'a2' => ['value' => 'sign', 'hint' => 'endorse'],
                    ],
                    'options' => ['must', 'may', 'sign', 'erase', 'might'],
                ],
                [
                    'question' => 'We {a1} {a2} the safety drills twice a year.',
                    'answers' => [
                        'a1' => ['value' => 'have to', 'hint' => 'schedule'],
                        'a2' => ['value' => 'run', 'hint' => 'conduct'],
                    ],
                    'options' => ['have to', 'may', 'run', 'cancel', 'could'],
                ],
                [
                    'question' => 'Analysts {a1} {a2} anomalies before closing the ticket.',
                    'answers' => [
                        'a1' => ['value' => 'must', 'hint' => 'procedure'],
                        'a2' => ['value' => 'document', 'hint' => 'record'],
                    ],
                    'options' => ['must', 'may', 'document', 'dismiss', 'might'],
                ],
                [
                    'question' => 'You {a1} {a2} all expenses by Monday morning.',
                    'answers' => [
                        'a1' => ['value' => 'need to', 'hint' => 'finance'],
                        'a2' => ['value' => 'reconcile', 'hint' => 'balance'],
                    ],
                    'options' => ['need to', 'may', 'reconcile', 'spend', 'could'],
                ],
                [
                    'question' => 'Engineers {a1} {a2} the prototype according to the spec.',
                    'answers' => [
                        'a1' => ['value' => 'must', 'hint' => 'precision'],
                        'a2' => ['value' => 'assemble', 'hint' => 'build'],
                    ],
                    'options' => ['must', 'may', 'assemble', 'skip', 'might'],
                ],
                [
                    'question' => 'Vendors {a1} {a2} insurance certificates annually.',
                    'answers' => [
                        'a1' => ['value' => 'must', 'hint' => 'policy'],
                        'a2' => ['value' => 'provide', 'hint' => 'supply'],
                    ],
                    'options' => ['must', 'may', 'provide', 'withhold', 'might'],
                ],
            ],
            'C1' => [
                [
                    'question' => 'Project directors {a1} {a2} regulatory audits without delay.',
                    'answers' => [
                        'a1' => ['value' => 'must', 'hint' => 'leadership duty'],
                        'a2' => ['value' => 'coordinate', 'hint' => 'organise'],
                    ],
                    'options' => ['must', 'may', 'coordinate', 'postpone', 'might'],
                ],
                [
                    'question' => 'Researchers {a1} {a2} adverse events to the ethics board.',
                    'answers' => [
                        'a1' => ['value' => 'must', 'hint' => 'compliance'],
                        'a2' => ['value' => 'report', 'hint' => 'notify'],
                    ],
                    'options' => ['must', 'may', 'report', 'ignore', 'might'],
                ],
                [
                    'question' => 'Consultants {a1} {a2} data retention clauses precisely.',
                    'answers' => [
                        'a1' => ['value' => 'must', 'hint' => 'contract'],
                        'a2' => ['value' => 'observe', 'hint' => 'respect'],
                    ],
                    'options' => ['must', 'may', 'observe', 'revise', 'might'],
                ],
                [
                    'question' => 'We {a1} {a2} remedial plans when compliance gaps appear.',
                    'answers' => [
                        'a1' => ['value' => 'must', 'hint' => 'response'],
                        'a2' => ['value' => 'draft', 'hint' => 'prepare'],
                    ],
                    'options' => ['must', 'may', 'draft', 'ignore', 'might'],
                ],
                [
                    'question' => 'Licensors {a1} {a2} royalty statements quarterly.',
                    'answers' => [
                        'a1' => ['value' => 'must', 'hint' => 'agreement'],
                        'a2' => ['value' => 'deliver', 'hint' => 'send'],
                    ],
                    'options' => ['must', 'may', 'deliver', 'retain', 'might'],
                ],
                [
                    'question' => 'Senior staff {a1} {a2} conflicts of interest annually.',
                    'answers' => [
                        'a1' => ['value' => 'have to', 'hint' => 'ethics'],
                        'a2' => ['value' => 'declare', 'hint' => 'state'],
                    ],
                    'options' => ['have to', 'may', 'declare', 'hide', 'might'],
                ],
            ],
            'C2' => [
                [
                    'question' => 'Governments {a1} {a2} humanitarian corridors during crises.',
                    'answers' => [
                        'a1' => ['value' => 'must', 'hint' => 'international duty'],
                        'a2' => ['value' => 'maintain', 'hint' => 'keep open'],
                    ],
                    'options' => ['must', 'may', 'maintain', 'abandon', 'might'],
                ],
                [
                    'question' => 'Signatories {a1} {a2} compliance metrics under the treaty.',
                    'answers' => [
                        'a1' => ['value' => 'must', 'hint' => 'treaty duty'],
                        'a2' => ['value' => 'publish', 'hint' => 'release'],
                    ],
                    'options' => ['must', 'may', 'publish', 'suppress', 'might'],
                ],
                [
                    'question' => 'Corporations {a1} {a2} beneficial ownership details transparently.',
                    'answers' => [
                        'a1' => ['value' => 'must', 'hint' => 'regulatory'],
                        'a2' => ['value' => 'disclose', 'hint' => 'reveal'],
                    ],
                    'options' => ['must', 'may', 'disclose', 'conceal', 'might'],
                ],
                [
                    'question' => 'Judges {a1} {a2} written opinions within statutory deadlines.',
                    'answers' => [
                        'a1' => ['value' => 'must', 'hint' => 'legal duty'],
                        'a2' => ['value' => 'issue', 'hint' => 'deliver'],
                    ],
                    'options' => ['must', 'may', 'issue', 'delay', 'might'],
                ],
                [
                    'question' => 'Arbitrators {a1} {a2} impartiality declarations before proceedings.',
                    'answers' => [
                        'a1' => ['value' => 'must', 'hint' => 'ethics'],
                        'a2' => ['value' => 'file', 'hint' => 'submit'],
                    ],
                    'options' => ['must', 'may', 'file', 'discard', 'might'],
                ],
                [
                    'question' => 'International agencies {a1} {a2} crisis alerts to member states instantly.',
                    'answers' => [
                        'a1' => ['value' => 'must', 'hint' => 'protocol'],
                        'a2' => ['value' => 'broadcast', 'hint' => 'send'],
                    ],
                    'options' => ['must', 'may', 'broadcast', 'shelve', 'might'],
                ],
            ],
        ],
        'advice' => [
            'A1' => [
                [
                    'question' => 'You {a1} eat more fruit.',
                    'answers' => [
                        'a1' => ['value' => 'should', 'hint' => 'health tip'],
                    ],
                    'options' => ['should', 'must', 'may', 'can'],
                ],
                [
                    'question' => 'He {a1} go to bed early.',
                    'answers' => [
                        'a1' => ['value' => 'should', 'hint' => 'rest advice'],
                    ],
                    'options' => ['should', 'must', 'might', 'can'],
                ],
                [
                    'question' => 'They {a1} be kind to new students.',
                    'answers' => [
                        'a1' => ['value' => 'should', 'hint' => 'social tip'],
                    ],
                    'options' => ['should', 'must', 'may', 'could'],
                ],
                [
                    'question' => 'We {a1} take an umbrella today.',
                    'answers' => [
                        'a1' => ['value' => 'should', 'hint' => 'weather advice'],
                    ],
                    'options' => ['should', 'must', 'may', 'might'],
                ],
                [
                    'question' => 'She {a1} study for the test.',
                    'answers' => [
                        'a1' => ['value' => 'should', 'hint' => 'study tip'],
                    ],
                    'options' => ['should', 'must', 'may', 'could'],
                ],
                [
                    'question' => 'I {a1} call my grandma more often.',
                    'answers' => [
                        'a1' => ['value' => 'should', 'hint' => 'gentle reminder'],
                    ],
                    'options' => ['should', 'must', 'may', 'might'],
                ],
            ],
            'A2' => [
                [
                    'question' => 'You {a1} {a2} a warm coat tonight.',
                    'answers' => [
                        'a1' => ['value' => 'should', 'hint' => 'recommendation'],
                        'a2' => ['value' => 'wear', 'hint' => 'put on'],
                    ],
                    'options' => ['should', 'must', 'wear', 'pack', 'might'],
                ],
                [
                    'question' => 'They {a1} {a2} sugary drinks every day.',
                    'answers' => [
                        'a1' => ['value' => "shouldn't", 'hint' => 'warning'],
                        'a2' => ['value' => 'drink', 'hint' => 'consume'],
                    ],
                    'options' => ["shouldn't", 'should', 'drink', 'store', 'might'],
                ],
                [
                    'question' => 'He {a1} {a2} for help when unsure.',
                    'answers' => [
                        'a1' => ['value' => 'should', 'hint' => 'guidance'],
                        'a2' => ['value' => 'ask', 'hint' => 'request'],
                    ],
                    'options' => ['should', 'must', 'ask', 'ignore', 'might'],
                ],
                [
                    'question' => 'We {a1} {a2} our tickets in advance.',
                    'answers' => [
                        'a1' => ['value' => 'should', 'hint' => 'planning'],
                        'a2' => ['value' => 'book', 'hint' => 'reserve'],
                    ],
                    'options' => ['should', 'must', 'book', 'forget', 'might'],
                ],
                [
                    'question' => 'You {a1} visit the dentist twice a year.',
                    'answers' => [
                        'a1' => ['value' => 'should', 'hint' => 'health'],
                    ],
                    'options' => ['should', 'must', 'may', 'might'],
                ],
                [
                    'question' => 'Students {a1} {a2} the summary after each lecture.',
                    'answers' => [
                        'a1' => ['value' => 'should', 'hint' => 'study habit'],
                        'a2' => ['value' => 'write', 'hint' => 'note down'],
                    ],
                    'options' => ['should', 'must', 'write', 'erase', 'might'],
                ],
            ],
            'B1' => [
                [
                    'question' => 'You {a1} {a2} your goals for the quarter.',
                    'answers' => [
                        'a1' => ['value' => 'ought to', 'hint' => 'strong advice'],
                        'a2' => ['value' => 'define', 'hint' => 'set'],
                    ],
                    'options' => ['ought to', 'should', 'define', 'skip', 'might'],
                ],
                [
                    'question' => 'We {a1} {a2} sooner to avoid traffic.',
                    'answers' => [
                        'a1' => ['value' => 'should', 'hint' => 'planning'],
                        'a2' => ['value' => 'leave', 'hint' => 'depart'],
                    ],
                    'options' => ['should', 'must', 'leave', 'wait', 'might'],
                ],
                [
                    'question' => 'She {a1} {a2} that old software anymore.',
                    'answers' => [
                        'a1' => ['value' => "shouldn't", 'hint' => 'gentle warning'],
                        'a2' => ['value' => 'use', 'hint' => 'rely on'],
                    ],
                    'options' => ["shouldn't", 'should', 'use', 'install', 'might'],
                ],
                [
                    'question' => 'They {a1} {a2} all feedback carefully.',
                    'answers' => [
                        'a1' => ['value' => 'should', 'hint' => 'careful review'],
                        'a2' => ['value' => 'consider', 'hint' => 'think about'],
                    ],
                    'options' => ['should', 'must', 'consider', 'dismiss', 'might'],
                ],
                [
                    'question' => 'You {a1} {a2} your manager if plans change.',
                    'answers' => [
                        'a1' => ['value' => 'should', 'hint' => 'communication'],
                        'a2' => ['value' => 'notify', 'hint' => 'inform'],
                    ],
                    'options' => ['should', 'must', 'notify', 'delay', 'might'],
                ],
                [
                    'question' => 'He {a1} {a2} the draft with legal first.',
                    'answers' => [
                        'a1' => ['value' => 'should', 'hint' => 'smart move'],
                        'a2' => ['value' => 'review', 'hint' => 'check'],
                    ],
                    'options' => ['should', 'must', 'review', 'ignore', 'might'],
                ],
            ],
            'B2' => [
                [
                    'question' => 'You {a1} {a2} a mentor about this transition.',
                    'answers' => [
                        'a1' => ['value' => 'ought to', 'hint' => 'smart guidance'],
                        'a2' => ['value' => 'consult', 'hint' => 'speak with'],
                    ],
                    'options' => ['ought to', 'should', 'consult', 'avoid', 'might'],
                ],
                [
                    'question' => 'We {a1} {a2} the scope creep early.',
                    'answers' => [
                        'a1' => ['value' => 'had better', 'hint' => 'urgent advice'],
                        'a2' => ['value' => 'address', 'hint' => 'deal with'],
                    ],
                    'options' => ['had better', 'should', 'address', 'ignore', 'might'],
                ],
                [
                    'question' => 'They {a1} {a2} stakeholders weekly.',
                    'answers' => [
                        'a1' => ['value' => 'should', 'hint' => 'communication'],
                        'a2' => ['value' => 'update', 'hint' => 'inform'],
                    ],
                    'options' => ['should', 'must', 'update', 'exclude', 'might'],
                ],
                [
                    'question' => 'You {a1} {a2} the budget before signing.',
                    'answers' => [
                        'a1' => ['value' => 'should', 'hint' => 'due diligence'],
                        'a2' => ['value' => 'double-check', 'hint' => 'verify'],
                    ],
                    'options' => ['should', 'must', 'double-check', 'assume', 'might'],
                ],
                [
                    'question' => 'She {a1} {a2} more context in the brief.',
                    'answers' => [
                        'a1' => ['value' => 'should', 'hint' => 'quality'],
                        'a2' => ['value' => 'include', 'hint' => 'add'],
                    ],
                    'options' => ['should', 'must', 'include', 'omit', 'might'],
                ],
                [
                    'question' => 'Consultants {a1} {a2} bold claims without evidence.',
                    'answers' => [
                        'a1' => ['value' => "shouldn't", 'hint' => 'professional caution'],
                        'a2' => ['value' => 'make', 'hint' => 'state'],
                    ],
                    'options' => ["shouldn't", 'should', 'make', 'record', 'might'],
                ],
            ],
            'C1' => [
                [
                    'question' => 'You {a1} {a2} the contract clauses with counsel.',
                    'answers' => [
                        'a1' => ['value' => 'should', 'hint' => 'legal advice'],
                        'a2' => ['value' => 'review', 'hint' => 'examine'],
                    ],
                    'options' => ['should', 'must', 'review', 'skip', 'might'],
                ],
                [
                    'question' => 'The team {a1} {a2} a post-mortem after launch.',
                    'answers' => [
                        'a1' => ['value' => 'should', 'hint' => 'learning'],
                        'a2' => ['value' => 'conduct', 'hint' => 'do'],
                    ],
                    'options' => ['should', 'must', 'conduct', 'avoid', 'might'],
                ],
                [
                    'question' => 'She {a1} {a2} the board about the risk immediately.',
                    'answers' => [
                        'a1' => ['value' => 'should', 'hint' => 'timely action'],
                        'a2' => ['value' => 'inform', 'hint' => 'tell'],
                    ],
                    'options' => ['should', 'must', 'inform', 'delay', 'might'],
                ],
                [
                    'question' => 'They {a1} {a2} their assumptions with data.',
                    'answers' => [
                        'a1' => ['value' => 'should', 'hint' => 'evidence'],
                        'a2' => ['value' => 'support', 'hint' => 'back up'],
                    ],
                    'options' => ['should', 'must', 'support', 'drop', 'might'],
                ],
                [
                    'question' => 'You {a1} {a2} a contingency plan ready.',
                    'answers' => [
                        'a1' => ['value' => 'should', 'hint' => 'preparedness'],
                        'a2' => ['value' => 'have', 'hint' => 'keep'],
                    ],
                    'options' => ['should', 'must', 'have', 'discard', 'might'],
                ],
                [
                    'question' => 'Managers {a1} {a2} personal issues respectfully.',
                    'answers' => [
                        'a1' => ['value' => 'should', 'hint' => 'professional'],
                        'a2' => ['value' => 'handle', 'hint' => 'manage'],
                    ],
                    'options' => ['should', 'must', 'handle', 'ignore', 'might'],
                ],
            ],
            'C2' => [
                [
                    'question' => 'He {a1} {a2} the investors earlier.',
                    'answers' => [
                        'a1' => ['value' => 'should have', 'hint' => 'missed advice'],
                        'a2' => ['value' => 'briefed', 'hint' => 'updated'],
                    ],
                    'options' => ['should have', 'must have', 'briefed', 'avoided', 'might have'],
                ],
                [
                    'question' => 'We {a1} {a2} clearer governance structures.',
                    'answers' => [
                        'a1' => ['value' => 'ought to have', 'hint' => 'reflection'],
                        'a2' => ['value' => 'established', 'hint' => 'set up'],
                    ],
                    'options' => ['ought to have', 'should have', 'established', 'ignored', 'might have'],
                ],
                [
                    'question' => 'They {a1} {a2} confidence they could deliver.',
                    'answers' => [
                        'a1' => ['value' => 'should never have', 'hint' => 'strong caution'],
                        'a2' => ['value' => 'promised', 'hint' => 'pledged'],
                    ],
                    'options' => ['should never have', 'should have', 'promised', 'withheld', 'might have'],
                ],
                [
                    'question' => 'You {a1} {a2} all assumptions with evidence.',
                    'answers' => [
                        'a1' => ['value' => 'would be wise to', 'hint' => 'strategic'],
                        'a2' => ['value' => 'support', 'hint' => 'back up'],
                    ],
                    'options' => ['would be wise to', 'should', 'support', 'drop', 'could'],
                ],
                [
                    'question' => 'The committee {a1} {a2} the dissenting data set.',
                    'answers' => [
                        'a1' => ['value' => 'should have', 'hint' => 'missed review'],
                        'a2' => ['value' => 'considered', 'hint' => 'weighed'],
                    ],
                    'options' => ['should have', 'must have', 'considered', 'ignored', 'might have'],
                ],
                [
                    'question' => 'Senior leaders {a1} {a2} tone carefully during crises.',
                    'answers' => [
                        'a1' => ['value' => 'ought to', 'hint' => 'strategic advice'],
                        'a2' => ['value' => 'moderate', 'hint' => 'manage'],
                    ],
                    'options' => ['ought to', 'should', 'moderate', 'inflame', 'might'],
                ],
            ],
        ],
        'possibility' => [
            'A1' => [
                [
                    'question' => 'It {a1} snow tonight.',
                    'answers' => [
                        'a1' => ['value' => 'might', 'hint' => 'uncertain weather'],
                    ],
                    'options' => ['might', 'must', 'can', 'should'],
                ],
                [
                    'question' => 'They {a1} be late.',
                    'answers' => [
                        'a1' => ['value' => 'may', 'hint' => 'possibility'],
                    ],
                    'options' => ['may', 'must', 'can', 'should'],
                ],
                [
                    'question' => 'She {a1} call later.',
                    'answers' => [
                        'a1' => ['value' => 'might', 'hint' => 'uncertain plan'],
                    ],
                    'options' => ['might', 'must', 'can', 'should'],
                ],
                [
                    'question' => 'We {a1} see a rainbow.',
                    'answers' => [
                        'a1' => ['value' => 'might', 'hint' => 'small chance'],
                    ],
                    'options' => ['might', 'must', 'can', 'should'],
                ],
                [
                    'question' => 'He {a1} be hungry now.',
                    'answers' => [
                        'a1' => ['value' => 'may', 'hint' => 'uncertain state'],
                    ],
                    'options' => ['may', 'must', 'can', 'should'],
                ],
                [
                    'question' => 'I {a1} visit tomorrow.',
                    'answers' => [
                        'a1' => ['value' => 'might', 'hint' => 'unsure plan'],
                    ],
                    'options' => ['might', 'must', 'can', 'should'],
                ],
            ],
            'A2' => [
                [
                    'question' => 'The bus {a1} {a2} a few minutes late.',
                    'answers' => [
                        'a1' => ['value' => 'might', 'hint' => 'possible delay'],
                        'a2' => ['value' => 'arrive', 'hint' => 'reach'],
                    ],
                    'options' => ['might', 'must', 'arrive', 'leave', 'should'],
                ],
                [
                    'question' => 'She {a1} {a2} us if the call ends early.',
                    'answers' => [
                        'a1' => ['value' => 'may', 'hint' => 'uncertain plan'],
                        'a2' => ['value' => 'join', 'hint' => 'participate'],
                    ],
                    'options' => ['may', 'must', 'join', 'cancel', 'might'],
                ],
                [
                    'question' => 'They {a1} {a2} the file by mistake.',
                    'answers' => [
                        'a1' => ['value' => 'might', 'hint' => 'possible error'],
                        'a2' => ['value' => 'delete', 'hint' => 'remove'],
                    ],
                    'options' => ['might', 'must', 'delete', 'save', 'could'],
                ],
                [
                    'question' => 'He {a1} {a2} your message later.',
                    'answers' => [
                        'a1' => ['value' => 'may', 'hint' => 'uncertain'],
                        'a2' => ['value' => 'reply', 'hint' => 'answer'],
                    ],
                    'options' => ['may', 'must', 'reply', 'ignore', 'might'],
                ],
                [
                    'question' => 'The meeting {a1} move to Friday.',
                    'answers' => [
                        'a1' => ['value' => 'might', 'hint' => 'schedule change'],
                    ],
                    'options' => ['might', 'must', 'can', 'should'],
                ],
                [
                    'question' => 'You {a1} {a2} a taxi instead of walking.',
                    'answers' => [
                        'a1' => ['value' => 'could', 'hint' => 'available option'],
                        'a2' => ['value' => 'take', 'hint' => 'use'],
                    ],
                    'options' => ['could', 'must', 'take', 'carry', 'should'],
                ],
            ],
            'B1' => [
                [
                    'question' => 'She {a1} {a2} the news already.',
                    'answers' => [
                        'a1' => ['value' => 'may have', 'hint' => 'past possibility'],
                        'a2' => ['value' => 'heard', 'hint' => 'learned'],
                    ],
                    'options' => ['may have', 'must have', 'heard', 'missed', 'might have'],
                ],
                [
                    'question' => 'They {a1} {a2} another supplier soon.',
                    'answers' => [
                        'a1' => ['value' => 'might', 'hint' => 'uncertain'],
                        'a2' => ['value' => 'choose', 'hint' => 'select'],
                    ],
                    'options' => ['might', 'must', 'choose', 'reject', 'should'],
                ],
                [
                    'question' => 'You {a1} {a2} more time if you ask.',
                    'answers' => [
                        'a1' => ['value' => 'might', 'hint' => 'possible'],
                        'a2' => ['value' => 'get', 'hint' => 'receive'],
                    ],
                    'options' => ['might', 'must', 'get', 'lose', 'should'],
                ],
                [
                    'question' => 'The printer {a1} {a2} out of ink.',
                    'answers' => [
                        'a1' => ['value' => 'might', 'hint' => 'possible issue'],
                        'a2' => ['value' => 'run', 'hint' => 'become'],
                    ],
                    'options' => ['might', 'must', 'run', 'print', 'could'],
                ],
                [
                    'question' => 'We {a1} {a2} extra chairs if more guests arrive.',
                    'answers' => [
                        'a1' => ['value' => 'might', 'hint' => 'contingency'],
                        'a2' => ['value' => 'need', 'hint' => 'require'],
                    ],
                    'options' => ['might', 'must', 'need', 'store', 'should'],
                ],
                [
                    'question' => 'He {a1} {a2} the wrong platform.',
                    'answers' => [
                        'a1' => ['value' => 'may have', 'hint' => 'past possibility'],
                        'a2' => ['value' => 'checked', 'hint' => 'looked at'],
                    ],
                    'options' => ['may have', 'must have', 'checked', 'ignored', 'might have'],
                ],
            ],
            'B2' => [
                [
                    'question' => 'Analysts {a1} {a2} a market shift soon.',
                    'answers' => [
                        'a1' => ['value' => 'may', 'hint' => 'possibility'],
                        'a2' => ['value' => 'predict', 'hint' => 'forecast'],
                    ],
                    'options' => ['may', 'must', 'predict', 'dismiss', 'should'],
                ],
                [
                    'question' => 'The courier {a1} {a2} your building already.',
                    'answers' => [
                        'a1' => ['value' => 'might have', 'hint' => 'past possibility'],
                        'a2' => ['value' => 'left', 'hint' => 'departed'],
                    ],
                    'options' => ['might have', 'must have', 'left', 'waited', 'could have'],
                ],
                [
                    'question' => 'We {a1} {a2} delays because of the strike.',
                    'answers' => [
                        'a1' => ['value' => 'may', 'hint' => 'chance'],
                        'a2' => ['value' => 'face', 'hint' => 'experience'],
                    ],
                    'options' => ['may', 'must', 'face', 'avoid', 'might'],
                ],
                [
                    'question' => 'She {a1} {a2} her train if traffic clears.',
                    'answers' => [
                        'a1' => ['value' => 'might still', 'hint' => 'remaining chance'],
                        'a2' => ['value' => 'catch', 'hint' => 'make'],
                    ],
                    'options' => ['might still', 'must', 'catch', 'miss', 'should'],
                ],
                [
                    'question' => 'They {a1} {a2} extra funding this quarter.',
                    'answers' => [
                        'a1' => ['value' => 'could', 'hint' => 'possible'],
                        'a2' => ['value' => 'secure', 'hint' => 'obtain'],
                    ],
                    'options' => ['could', 'must', 'secure', 'lose', 'might'],
                ],
                [
                    'question' => 'The server {a1} {a2} offline after midnight.',
                    'answers' => [
                        'a1' => ['value' => 'might', 'hint' => 'possible'],
                        'a2' => ['value' => 'go', 'hint' => 'turn'],
                    ],
                    'options' => ['might', 'must', 'go', 'stay', 'should'],
                ],
            ],
            'C1' => [
                [
                    'question' => 'The audit {a1} {a2} deeper issues.',
                    'answers' => [
                        'a1' => ['value' => 'might', 'hint' => 'possible'],
                        'a2' => ['value' => 'reveal', 'hint' => 'expose'],
                    ],
                    'options' => ['might', 'must', 'reveal', 'hide', 'could'],
                ],
                [
                    'question' => 'He {a1} {a2} the figures; let us verify.',
                    'answers' => [
                        'a1' => ['value' => 'may have', 'hint' => 'past possibility'],
                        'a2' => ['value' => 'misread', 'hint' => 'misinterpreted'],
                    ],
                    'options' => ['may have', 'must have', 'misread', 'confirmed', 'might have'],
                ],
                [
                    'question' => 'We {a1} {a2} a temporary solution if resources shrink.',
                    'answers' => [
                        'a1' => ['value' => 'might', 'hint' => 'possible'],
                        'a2' => ['value' => 'deploy', 'hint' => 'roll out'],
                    ],
                    'options' => ['might', 'must', 'deploy', 'abandon', 'could'],
                ],
                [
                    'question' => 'Stakeholders {a1} {a2} the proposal cautiously.',
                    'answers' => [
                        'a1' => ['value' => 'may', 'hint' => 'uncertain reaction'],
                        'a2' => ['value' => 'treat', 'hint' => 'handle'],
                    ],
                    'options' => ['may', 'must', 'treat', 'endorse', 'might'],
                ],
                [
                    'question' => 'They {a1} {a2} the launch should testing slip.',
                    'answers' => [
                        'a1' => ['value' => 'could', 'hint' => 'possible'],
                        'a2' => ['value' => 'delay', 'hint' => 'push back'],
                    ],
                    'options' => ['could', 'must', 'delay', 'advance', 'might'],
                ],
                [
                    'question' => 'The model {a1} {a2} biases we have not mapped.',
                    'answers' => [
                        'a1' => ['value' => 'might', 'hint' => 'possible issue'],
                        'a2' => ['value' => 'contain', 'hint' => 'hold'],
                    ],
                    'options' => ['might', 'must', 'contain', 'avoid', 'could'],
                ],
            ],
            'C2' => [
                [
                    'question' => 'Investors {a1} {a2} the signals as uncertainty.',
                    'answers' => [
                        'a1' => ['value' => 'could well', 'hint' => 'likely possibility'],
                        'a2' => ['value' => 'read', 'hint' => 'interpret'],
                    ],
                    'options' => ['could well', 'must', 'read', 'ignore', 'might'],
                ],
                [
                    'question' => 'The algorithm {a1} {a2} latent trends we cannot see yet.',
                    'answers' => [
                        'a1' => ['value' => 'may well', 'hint' => 'plausible'],
                        'a2' => ['value' => 'surface', 'hint' => 'bring up'],
                    ],
                    'options' => ['may well', 'must', 'surface', 'bury', 'might'],
                ],
                [
                    'question' => 'She {a1} {a2} the briefing notes unintentionally.',
                    'answers' => [
                        'a1' => ['value' => 'might have', 'hint' => 'past possibility'],
                        'a2' => ['value' => 'omitted', 'hint' => 'left out'],
                    ],
                    'options' => ['might have', 'must have', 'omitted', 'included', 'could have'],
                ],
                [
                    'question' => 'We {a1} {a2} unforeseen legal hurdles.',
                    'answers' => [
                        'a1' => ['value' => 'could', 'hint' => 'possible'],
                        'a2' => ['value' => 'encounter', 'hint' => 'face'],
                    ],
                    'options' => ['could', 'must', 'encounter', 'avoid', 'might'],
                ],
                [
                    'question' => 'The treaty {a1} {a2} earlier if negotiations accelerate.',
                    'answers' => [
                        'a1' => ['value' => 'might', 'hint' => 'contingent'],
                        'a2' => ['value' => 'conclude', 'hint' => 'finish'],
                    ],
                    'options' => ['might', 'must', 'conclude', 'stall', 'could'],
                ],
                [
                    'question' => 'Observers {a1} {a2} the move as a strategic pause.',
                    'answers' => [
                        'a1' => ['value' => 'might', 'hint' => 'interpretation'],
                        'a2' => ['value' => 'interpret', 'hint' => 'see'],
                    ],
                    'options' => ['might', 'must', 'interpret', 'reject', 'could'],
                ],
            ],
        ],
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'AI Modal Verbs Subthemes Comprehensive'])->id;

        $sources = [];
        foreach ($this->subthemes as $key => $config) {
            $label = $config['label'];
            $sources[$key] = [
                'bank' => Source::firstOrCreate(['name' => "AI Modals {$label} — Bank"])->id,
                'leveled' => Source::firstOrCreate(['name' => "AI Modals {$label} — Leveled"])->id,
            ];
        }

        $modalThemeTagId = Tag::firstOrCreate(
            ['name' => 'Modal Verbs'],
            ['category' => 'English Grammar Theme']
        )->id;

        $subthemeTagIds = [];
        foreach ($this->subthemes as $key => $config) {
            $subthemeTagIds[$key] = Tag::firstOrCreate(
                ['name' => $config['label']],
                ['category' => 'English Grammar Theme']
            )->id;
        }

        $pairTagIds = [];
        foreach ($this->modalPairMap as $pairKey => $variants) {
            $pairTagIds[$pairKey] = Tag::firstOrCreate(
                ['name' => $this->pairName($pairKey)],
                ['category' => 'English Grammar Modal Pair']
            )->id;
        }

        $entries = $this->collectEntries();

        $items = [];
        $meta = [];

        foreach ($entries as $index => $entry) {
            $subKey = $entry['subtheme'];
            $level = $entry['level'];
            $question = $entry['question'];
            $answersConfig = $entry['answers'];
            $bucket = $entry['bucket'];

            $answerList = [];
            $plainAnswers = [];
            foreach ($answersConfig as $marker => $data) {
                $value = (string) $data['value'];
                $hint = $this->normalizeVerbHint($data['hint'] ?? null, $value);

                $answerList[] = [
                    'marker' => $marker,
                    'answer' => $value,
                    'verb_hint' => $hint,
                ];

                $plainAnswers[$marker] = $value;
            }

            $options = array_values(array_unique(array_map('strval', $entry['options'] ?? [])));
            foreach ($plainAnswers as $value) {
                if (!in_array($value, $options, true)) {
                    $options[] = $value;
                }
            }

            $optionMarkers = [];
            foreach ($options as $opt) {
                $marker = array_search($opt, $plainAnswers, true);
                $optionMarkers[$opt] = $marker === false ? array_key_first($plainAnswers) : $marker;
            }

            $tags = [$modalThemeTagId, $subthemeTagIds[$subKey]];
            $tags = array_values(array_unique(array_merge($tags, $this->detectPairTags($options, $pairTagIds))));

            $uuid = $this->generateQuestionUuid($level, $subKey, $bucket, $index + 1);

            $items[] = [
                'uuid' => $uuid,
                'question' => $question,
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$level] ?? 3,
                'source_id' => $sources[$subKey][$bucket],
                'flag' => 2,
                'type' => 0,
                'level' => $level,
                'tag_ids' => $tags,
                'answers' => $answerList,
                'options' => $options,
                'variants' => [$question],
            ];

            $meta[] = [
                'uuid' => $uuid,
                'answers' => $plainAnswers,
                'option_markers' => $optionMarkers,
                'hints' => $this->buildHintBlocks($subKey, $level, $plainAnswers),
                'explanations' => $this->buildExplanations($subKey, $level, $options, $plainAnswers),
            ];
        }

        $this->seedQuestionData($items, $meta);
    }

    private function collectEntries(): array
    {
        $entries = [];

        foreach ($this->bankQuestions as $subKey => $questions) {
            foreach ($questions as $question) {
                $entries[] = $this->normalizeEntry($question + ['subtheme' => $subKey, 'bucket' => 'bank']);
            }
        }

        foreach ($this->leveledQuestions as $subKey => $levels) {
            foreach ($levels as $level => $questions) {
                foreach ($questions as $question) {
                    $entries[] = $this->normalizeEntry($question + [
                        'subtheme' => $subKey,
                        'bucket' => 'leveled',
                        'level' => $level,
                    ]);
                }
            }
        }

        return $entries;
    }

    private function normalizeEntry(array $entry): array
    {
        if (!isset($entry['question'], $entry['answers']) || !is_array($entry['answers'])) {
            return $entry;
        }

        $entry = $this->mergeAdjacentMarkers($entry);
        $entry['options'] = array_values(array_unique(array_map('strval', $entry['options'] ?? [])));

        return $entry;
    }

    private function mergeAdjacentMarkers(array $entry): array
    {
        $question = $entry['question'];
        $answers = $entry['answers'];
        $options = $entry['options'] ?? [];

        $pattern = '/\{(a\d+)\}(\s+)\{(a\d+)\}/';

        while (preg_match($pattern, $question, $matches)) {
            $fullMatch = $matches[0];
            $firstMarker = $matches[1];
            $secondMarker = $matches[3];

            if (!isset($answers[$firstMarker], $answers[$secondMarker])) {
                $question = str_replace($fullMatch, '{' . $firstMarker . '}', $question);
                continue;
            }

            $firstData = $answers[$firstMarker];
            $secondData = $answers[$secondMarker];

            $combinedValue = trim(($firstData['value'] ?? '') . ' ' . ($secondData['value'] ?? ''));
            $combinedHint = $this->mergeHints($firstData['hint'] ?? null, $secondData['hint'] ?? null);

            $answers[$firstMarker]['value'] = $combinedValue;
            if ($combinedHint !== null) {
                $answers[$firstMarker]['hint'] = $combinedHint;
            }

            unset($answers[$secondMarker]);

            $options = $this->updateOptionsForMergedValues($options, $firstData['value'] ?? '', $secondData['value'] ?? '', $combinedValue);

            $question = str_replace($fullMatch, '{' . $firstMarker . '}', $question);
        }

        $entry['question'] = preg_replace('/\s{2,}/', ' ', trim($question));
        $entry['answers'] = $answers;
        $entry['options'] = $options;

        return $entry;
    }

    private function mergeHints(?string $first, ?string $second): ?string
    {
        if ($first === null) {
            return $second;
        }

        if ($second === null) {
            return $first;
        }

        if (strcasecmp($first, $second) === 0) {
            return $first;
        }

        return $first . '; ' . $second;
    }

    private function updateOptionsForMergedValues(array $options, string $firstValue, string $secondValue, string $combinedValue): array
    {
        $normalized = [];
        foreach ($options as $option) {
            if (is_scalar($option)) {
                $normalized[] = (string) $option;
            }
        }

        $filtered = [];
        foreach ($normalized as $option) {
            if ($secondValue !== '' && strcasecmp($option, $secondValue) === 0) {
                continue;
            }

            $filtered[] = $option;
        }

        if ($combinedValue !== '' && !in_array($combinedValue, $filtered, true)) {
            $filtered[] = $combinedValue;
        }

        return array_values($filtered);
    }

    private function buildHintBlocks(string $subtheme, string $level, array $answers): array
    {
        $focusMap = [
            'ability' => 'Поміркуй, яке слово демонструє здатність або навичку.',
            'permission' => 'Зверни увагу, чи дає речення дозвіл або описує його межі.',
            'obligation' => 'Потрібно показати обов’язок, необхідність або її відсутність.',
            'advice' => 'Речення просить поради або рекомендації без суворого обов’язку.',
            'possibility' => 'Мова про ймовірність чи припущення, не про факт.',
            'deduction' => 'Йдеться про логічний висновок на основі доказів.',
        ];

        $structure = $this->structureHint($subtheme, $answers);
        $example = $this->hintExample($subtheme, $level);

        return [
            ($focusMap[$subtheme] ?? 'Оціни модальне значення у реченні.') . " Рівень: {$level}.",
            $structure,
            'Приклад: ' . $example,
        ];
    }

    private function buildExplanations(string $subtheme, string $level, array $options, array $answers): array
    {
        $correctValues = array_values($answers);
        $explanations = [];

        foreach ($options as $option) {
            $isCorrect = in_array($option, $correctValues, true);
            $class = $this->classifyOption($option);

            if ($isCorrect) {
                $explanations[$option] = $this->correctExplanation($subtheme, $level, $class) .
                    '\nОрієнтир: ' . $this->explanationExample($subtheme);

                continue;
            }

            $explanations[$option] = $this->wrongExplanation($option, $subtheme, $class) .
                '\nОрієнтир: ' . $this->explanationExample($subtheme);
        }

        return $explanations;
    }

    private function structureHint(string $subtheme, array $answers): string
    {
        $usesPerfect = false;
        foreach ($answers as $value) {
            if (str_contains($value, 'have ')) {
                $usesPerfect = true;
                break;
            }
        }

        return match ($subtheme) {
            'ability' => 'Структура: модальне слово + базовий інфінітив (за потреби у майбутньому додаємо "be able to").',
            'permission' => 'Структура: модальне дієслово для дозволу + базовий інфінітив. Зверни увагу на ввічливість.',
            'obligation' => $usesPerfect
                ? 'Структура: модальне дієслово обов’язку + have + V3 для минулих вимог.'
                : 'Структура: модальне дієслово обов’язку (must / have to / need to) + базовий інфінітив.',
            'advice' => 'Структура: should / ought to / had better + базовий інфінітив для рекомендації.',
            'possibility' => $usesPerfect
                ? 'Структура: модальне слово ймовірності + have + V3 для минулого припущення.'
                : 'Структура: модальне слово ймовірності + базовий інфінітив.',
            'deduction' => $usesPerfect
                ? 'Структура: модальне слово висновку + have + V3, щоб зробити висновок про минуле.'
                : 'Структура: must / might / can’t + базовий інфінітив для висновків про теперішнє.',
            default => 'Структура: модальне слово + базовий інфінітив.',
        };
    }

    private function hintExample(string $subtheme, string $level): string
    {
        $examples = [
            'ability' => '«Our guide ___ cross the canyon bridge when needed.»',
            'permission' => '«Guests asked politely: ___ we enter the gallery now?»',
            'obligation' => '«Team members ___ submit the incident log before they leave.»',
            'advice' => '«If you feel tired, ___ consider a short break.»',
            'possibility' => match ($level) {
                'C1', 'C2' => '«The lights are off; the team ___ have finished for today.»',
                default => '«It looks cloudy; it ___ rain later.»',
            },
            'deduction' => match ($level) {
                'C1', 'C2' => '«All evidence aligns; the analyst ___ have missed nothing.»',
                default => '«The office is dark; they ___ be home already.»',
            },
        ];

        return $examples[$subtheme] ?? '«___ choose the modal that fits the situation.»';
    }

    private function explanationExample(string $subtheme): string
    {
        $examples = [
            'ability' => '*Example check: «The rescue crew ___ scale the wall in seconds.»*',
            'permission' => '*Example check: «___ I connect my device to the projector?»*',
            'obligation' => '*Example check: «Staff ___ file the report before closing.»*',
            'advice' => '*Example check: «You ___ review the plan once more.»*',
            'possibility' => '*Example check: «The meeting ___ be postponed if the plane is late.»*',
            'deduction' => '*Example check: «The floor is wet; someone ___ have cleaned recently.»*',
        ];

        return $examples[$subtheme] ?? '*Example check: «___ reflect the modal meaning here.»*';
    }

    private function classifyOption(string $option): string
    {
        $normalized = strtolower(trim($option));

        return match (true) {
            str_contains($normalized, 'can’t') || str_contains($normalized, "can't") => "can't",
            str_contains($normalized, 'cannot') => "can't",
            str_contains($normalized, 'can') => 'can',
            str_contains($normalized, 'could') => 'could',
            str_contains($normalized, 'may') => 'may',
            str_contains($normalized, 'might have') => 'might_have',
            str_contains($normalized, 'might') => 'might',
            str_contains($normalized, 'mustn') => "mustn't",
            str_contains($normalized, 'must have') => 'must_have',
            str_contains($normalized, 'must') => 'must',
            str_contains($normalized, 'have to') || str_contains($normalized, 'has to') => 'have_to',
            str_contains($normalized, 'need to') => 'need_to',
            str_contains($normalized, 'needn') => "needn't",
            str_contains($normalized, 'ought to') => 'ought_to',
            str_contains($normalized, 'should have') => 'should_have',
            str_contains($normalized, 'should') => 'should',
            str_contains($normalized, 'had better') => 'had_better',
            str_contains($normalized, 'be allowed to') => 'allowed',
            str_contains($normalized, 'allowed') => 'allowed',
            str_contains($normalized, 'be able to') => 'be_able',
            str_contains($normalized, 'able to') => 'be_able',
            str_contains($normalized, 'supposed to') => 'supposed_to',
            default => 'other',
        };
    }

    private function correctExplanation(string $subtheme, string $level, string $class): string
    {
        $levelNote = match ($level) {
            'A1' => 'Це базова конструкція для початкового рівня.',
            'A2' => 'Добре показує розширений базовий рівень.',
            'B1' => 'Пасує до середнього рівня, де важлива точність.',
            'B2' => 'Відповідає впевненому використанню на верхньо-середньому рівні.',
            'C1' => 'Розкриває складні нюанси продвинутого рівня.',
            'C2' => 'Підкреслює майже бездоганне володіння мовою.',
            default => '',
        };

        return match ($subtheme) {
            'ability' => 'Цей вибір коректно передає потрібну здатність у зазначеному контексті. ' . $levelNote,
            'permission' => 'Цей варіант акуратно виражає дозвіл у потрібному регістрі. ' . $levelNote,
            'obligation' => 'Цей варіант відображає необхідний ступінь зобов’язання. ' . $levelNote,
            'advice' => 'Цей вибір звучить як доречна рекомендація без надмірного тиску. ' . $levelNote,
            'possibility' => 'Цей варіант точно показує ступінь імовірності. ' . $levelNote,
            'deduction' => 'Цей вибір логічно поєднує факти й висновок. ' . $levelNote,
            default => 'Цей варіант природно звучить у контексті. ' . $levelNote,
        };
    }

    private function wrongExplanation(string $option, string $subtheme, string $class): string
    {
        $base = "«{$option}»";

        return match ($subtheme) {
            'ability' => match ($class) {
                'must' => $base . ' передає зобов’язання, а не здатність.',
                'may' => $base . ' звучить як дозвіл, а не реальна навичка.',
                'might' => $base . ' виражає лише припущення, а не впевнене вміння.',
                'other' => $base . ' не показує потрібної впевненості у навичці.',
                default => $base . ' не демонструє саме ту здатність, яку підказує контекст.',
            },
            'permission' => match ($class) {
                'must' => $base . ' виражає зобов’язання, а не дозвіл.',
                'mustn' => $base . ' забороняє дію, хоча текст натякає на дозвіл.',
                'might' => $base . ' занадто невпевнене для цього контексту.',
                'have_to' => $base . ' говорить про необхідність, а не про дозвіл.',
                default => $base . ' не створює правильного відтінку дозволу.',
            },
            'obligation' => match ($class) {
                'may' => $base . ' лише дає дозвіл, але не примушує.',
                'might' => $base . ' звучить невпевнено і не передає обов’язку.',
                'other' => $base . ' не показує необхідної сили вимоги.',
                default => $base . ' не відповідає рівню зобов’язання, потрібного в реченні.',
            },
            'advice' => match ($class) {
                'must' => $base . ' надто суворе як для поради.',
                'may' => $base . ' лише дає дозвіл, не радить.',
                'might' => $base . ' звучить як припущення, а не порада.',
                default => $base . ' не підходить для м’якої рекомендації.',
            },
            'possibility' => match ($class) {
                'must' => $base . ' звучить упевнено, без простору для сумнівів.',
                'can' => $base . ' описує здатність, а не ймовірність.',
                'should' => $base . ' натякає на пораду, а не припущення.',
                default => $base . ' не відповідає ступеню невпевненості в ситуації.',
            },
            'deduction' => match ($class) {
                'may' => $base . ' занадто слабке як для логічного висновку.',
                'should' => $base . ' звучить як порада, а не висновок.',
                'can' => $base . ' описує здатність, не висновок із фактів.',
                default => $base . ' не формує потрібного висновку з доказів.',
            },
            default => $base . ' не підсилює значення, яке очікує речення.',
        };
    }

    private function detectPairTags(array $options, array $pairTagIds): array
    {
        $matched = [];

        foreach ($this->modalPairMap as $pairKey => $variants) {
            foreach ($options as $option) {
                $lower = strtolower($option);
                foreach ($variants as $variant) {
                    if ($variant === '') {
                        continue;
                    }

                    if (str_contains($lower, strtolower($variant))) {
                        $matched[] = $pairTagIds[$pairKey] ?? null;
                        break 2;
                    }
                }
            }
        }

        return array_values(array_filter(array_unique($matched)));
    }

    private function pairName(string $key): string
    {
        return match ($key) {
            'can_could' => 'Can / Could',
            'may_might' => 'May / Might',
            'must_have_to' => 'Must / Have to',
            'should_ought_to' => 'Should / Ought to',
            'need_need_to' => 'Need / Need to',
            'will_would' => 'Will / Would',
            'supposed_to' => 'Be Supposed To',
            default => 'Modal Pair',
        };
    }

    private function normalizeVerbHint(?string $hint, string $answer): ?string
    {
        if ($hint === null) {
            return null;
        }

        $trimmed = trim($hint);

        if (strcasecmp($trimmed, $answer) === 0) {
            return 'подумай про функцію цього модального слова';
        }

        return $trimmed;
    }
}
