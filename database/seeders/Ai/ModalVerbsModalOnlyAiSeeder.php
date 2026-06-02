<?php

namespace Database\Seeders\Ai;

class ModalVerbsModalOnlyAiSeeder extends ModalVerbsComprehensiveAiSeeder
{
    protected function getLevelData(): array
    {
        return [
            'A1' => [
                [
                    'theme' => 'ability',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '{a1} your brother climb trees easily?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Can',
                            'options' => ['Can', 'Could', 'Must'],
                            'verb_hint' => 'ability cue',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '{a1} we leave the classroom now, teacher?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'May',
                            'options' => ['May', 'Must', 'Should'],
                            'verb_hint' => 'permission cue',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'question',
                    'tense' => 'future',
                    'question' => '{a1} we finish the homework before the movie tonight?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Must',
                            'options' => ['Must', 'Can', 'Might'],
                            'verb_hint' => 'obligation focus',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '{a1} I take an umbrella to school today?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Should',
                            'options' => ['Should', 'Must', 'Can'],
                            'verb_hint' => 'advice cue',
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '{a1} this be the right bus to the zoo?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Could',
                            'options' => ['Could', 'Must', 'Should'],
                            'verb_hint' => 'deduction clue',
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'question',
                    'tense' => 'past',
                    'question' => '{a1} your grandfather swim when he was five?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Could',
                            'options' => ['Could', 'Can', 'May'],
                            'verb_hint' => 'past ability',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'The sign says we {a1} feed the ducks.',
                    'markers' => [
                        'a1' => [
                            'answer' => "Mustn't",
                            'options' => ["Mustn't", "Can't", "Shouldn't"],
                            'verb_hint' => 'prohibition cue',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'You {a1} wear a tie to this picnic; it is casual.',
                    'markers' => [
                        'a1' => [
                            'answer' => "Needn't",
                            'options' => ["Needn't", "Mustn't", "Shouldn't"],
                            'verb_hint' => 'no necessity',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'You {a1} eat so many sweets before dinner.',
                    'markers' => [
                        'a1' => [
                            'answer' => "Shouldn't",
                            'options' => ["Shouldn't", "Mustn't", "Might not"],
                            'verb_hint' => 'negative advice',
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'This {a1} be Nina’s coat; it is too big for her.',
                    'markers' => [
                        'a1' => [
                            'answer' => "Can't",
                            'options' => ["Can't", 'Might', 'Should'],
                            'verb_hint' => 'strong deduction',
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'negative',
                    'tense' => 'past',
                    'question' => 'Lily {a1} reach the top shelf yesterday, so I helped.',
                    'markers' => [
                        'a1' => [
                            'answer' => "Couldn't",
                            'options' => ["Couldn't", "Mustn't", "Shouldn't"],
                            'verb_hint' => 'past inability',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'negative',
                    'tense' => 'future',
                    'question' => 'We {a1} stay out late on school nights next week.',
                    'markers' => [
                        'a1' => [
                            'answer' => "Won't",
                            'options' => ["Won't", 'Might not', "Shouldn't"],
                            'verb_hint' => 'future restriction',
                        ],
                    ],
                ],
            ],
            'A2' => [
                [
                    'theme' => 'ability',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '{a1} firefighters enter the building safely now?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Can',
                            'options' => ['Can', 'Might', 'Must'],
                            'verb_hint' => 'ability focus',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '{a1} visitors take photos inside the gallery?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'May',
                            'options' => ['May', 'Must', 'Should'],
                            'verb_hint' => 'permission reminder',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '{a1} cyclists stop at the red light?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Must',
                            'options' => ['Must', 'Might', 'Could'],
                            'verb_hint' => 'legal requirement',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '{a1} we leave earlier to avoid traffic?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Should',
                            'options' => ['Should', 'Must', 'Would'],
                            'verb_hint' => 'advice signal',
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'This noise {a1} be the old heater starting up.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Must',
                            'options' => ['Must', 'Might', 'Could'],
                            'verb_hint' => 'strong deduction',
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'When she was young, Nora {a1} run five kilometers without stopping.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Could',
                            'options' => ['Could', 'Might', 'Would'],
                            'verb_hint' => 'past ability',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'Employees {a1} enter the lab without safety glasses.',
                    'markers' => [
                        'a1' => [
                            'answer' => "Mustn't",
                            'options' => ["Mustn't", "Can't", "Shouldn't"],
                            'verb_hint' => 'safety rule',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'You {a1} bring a gift; it is optional.',
                    'markers' => [
                        'a1' => [
                            'answer' => "Needn't",
                            'options' => ["Needn't", "Mustn't", "Shouldn't"],
                            'verb_hint' => 'no obligation',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'We {a1} ignore the coach’s instructions.',
                    'markers' => [
                        'a1' => [
                            'answer' => "Shouldn't",
                            'options' => ["Shouldn't", "Mustn't", 'Might not'],
                            'verb_hint' => 'advice warning',
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'That {a1} be the director calling; it is too late.',
                    'markers' => [
                        'a1' => [
                            'answer' => "Can't",
                            'options' => ["Can't", 'Might', 'Should'],
                            'verb_hint' => 'logical deduction',
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'After training, they {a1} lift heavier weights.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Will',
                            'options' => ['Will', 'Might', 'Should'],
                            'verb_hint' => 'future ability',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'If we finish early, we {a1} leave before sunset.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'May',
                            'options' => ['May', 'Might', 'Must'],
                            'verb_hint' => 'future permission',
                        ],
                    ],
                ],
            ],
            'B1' => [
                [
                    'theme' => 'ability',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Even with the noise, I {a1} concentrate on my book.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Can',
                            'options' => ['Can', 'Could', 'Might'],
                            'verb_hint' => 'current ability',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Guests {a1} use the private elevator without a pass.',
                    'markers' => [
                        'a1' => [
                            'answer' => "Mustn't",
                            'options' => ["Mustn't", "Can't", "Shouldn't"],
                            'verb_hint' => 'restriction notice',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Each applicant {a1} submit two references.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Must',
                            'options' => ['Must', 'Should', 'Might'],
                            'verb_hint' => 'formal requirement',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'You {a1} back up your files before the update.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Should',
                            'options' => ['Should', 'Must', 'Would'],
                            'verb_hint' => 'advice cue',
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'The lights are off, so they {a1} be at home.',
                    'markers' => [
                        'a1' => [
                            'answer' => "Can't",
                            'options' => ["Can't", 'Might', 'Could'],
                            'verb_hint' => 'negative deduction',
                        ],
                    ],
                    'fix_tags' => ['Must -> Can\'t (answer)', 'logical deduction -> negative deduction (verb_hint)'],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'Despite the injury, he {a1} finish the race.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Could',
                            'options' => ['Could', 'Would', 'Might'],
                            'verb_hint' => 'past achievement',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'Once approved, you {a1} access the archives.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Will',
                            'options' => ['Will', 'Might', 'Should'],
                            'verb_hint' => 'future access',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'To meet the deadline, we {a1} submit the forms by Monday.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Must',
                            'options' => ['Must', 'Should', 'Might'],
                            'verb_hint' => 'upcoming obligation',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'You {a1} have asked for help earlier.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Should',
                            'options' => ['Should', 'Must', 'Could'],
                            'verb_hint' => 'missed advice',
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'The footprints are fresh; someone {a1} have left recently.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Must',
                            'options' => ['Must', 'Might', 'Could'],
                            'verb_hint' => 'past deduction',
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'I {a1} possibly finish all these reports tonight.',
                    'markers' => [
                        'a1' => [
                            'answer' => "Can't",
                            'options' => ["Can't", 'Should', 'Might'],
                            'verb_hint' => 'limited ability',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'negative',
                    'tense' => 'future',
                    'question' => 'Without clearance, they {a1} attend the briefing tomorrow.',
                    'markers' => [
                        'a1' => [
                            'answer' => "Won't",
                            'options' => ["Won't", "Can't", "Shouldn't"],
                            'verb_hint' => 'future restriction',
                        ],
                    ],
                ],
            ],
            'B2' => [
                [
                    'theme' => 'ability',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'With the new software, analysts {a1} process data twice as fast.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Can',
                            'options' => ['Can', 'Could', 'Might'],
                            'verb_hint' => 'enhanced ability',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Only supervisors {a1} authorize refunds over this amount.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'May',
                            'options' => ['May', 'Must', 'Should'],
                            'verb_hint' => 'policy note',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Researchers {a1} follow the new ethical guidelines strictly.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Must',
                            'options' => ['Must', 'Should', 'Might'],
                            'verb_hint' => 'strict duty',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'You {a1} consult the manual before adjusting the settings.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Should',
                            'options' => ['Should', 'Must', 'Would'],
                            'verb_hint' => 'professional advice',
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Judging by the evidence, the witness {a1} be telling the truth.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Must',
                            'options' => ['Must', 'Might', 'Could'],
                            'verb_hint' => 'evidence-based deduction',
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'Thanks to years of practice, she {a1} negotiate complex deals.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Could',
                            'options' => ['Could', 'Would', 'Might'],
                            'verb_hint' => 'developed ability',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'After certification, the team {a1} operate independently.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Will',
                            'options' => ['Will', 'Might', 'Should'],
                            'verb_hint' => 'future autonomy',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'To meet the deadline, we {a1} work over the weekend.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Must',
                            'options' => ['Must', 'Should', 'Might'],
                            'verb_hint' => 'strong obligation',
                        ],
                    ],
                    'fix_tags' => ['Will -> Must (answer)', 'inevitable duty -> strong obligation (verb_hint)'],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'You {a1} have checked the figures more carefully.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Should',
                            'options' => ['Should', 'Must', 'Could'],
                            'verb_hint' => 'retrospective advice',
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'Given the delay, the shipment {a1} have been stuck at customs.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Must',
                            'options' => ['Must', 'Might', 'Could'],
                            'verb_hint' => 'analytical deduction',
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'They {a1} possibly finish the audit in a single day.',
                    'markers' => [
                        'a1' => [
                            'answer' => "Can't",
                            'options' => ["Can't", 'Should', 'Might'],
                            'verb_hint' => 'ability limit',
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'The reports contradict; this {a1} be the final version.',
                    'markers' => [
                        'a1' => [
                            'answer' => "Can't",
                            'options' => ["Can't", 'Might', 'Should'],
                            'verb_hint' => 'contradiction clue',
                        ],
                    ],
                ],
            ],
            'C1' => [
                [
                    'theme' => 'ability',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Few negotiators {a1} balance empathy with firmness so effectively.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Can',
                            'options' => ['Can', 'Could', 'Might'],
                            'verb_hint' => 'refined ability',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Under the new charter, members {a1} propose amendments during any session.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'May',
                            'options' => ['May', 'Must', 'Should'],
                            'verb_hint' => 'charter rule',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Directors {a1} disclose any conflicts of interest immediately.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Must',
                            'options' => ['Must', 'Should', 'Might'],
                            'verb_hint' => 'ethical duty',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'You {a1} consider delegating tasks to preserve strategic focus.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Should',
                            'options' => ['Should', 'Must', 'Would'],
                            'verb_hint' => 'executive advice',
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Given the market reaction, investors {a1} anticipate a merger.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Must',
                            'options' => ['Must', 'Might', 'Could'],
                            'verb_hint' => 'market inference',
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'Even under pressure, the committee {a1} formulate a balanced response.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Could',
                            'options' => ['Could', 'Would', 'Might'],
                            'verb_hint' => 'past capacity',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'Upon ratification, regional offices {a1} implement the policy autonomously.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Will',
                            'options' => ['Will', 'Might', 'Should'],
                            'verb_hint' => 'future authorization',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'To maintain compliance, we {a1} file quarterly reports without delay.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Will',
                            'options' => ['Will', 'Should', 'Might'],
                            'verb_hint' => 'forward-looking duty',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'You {a1} have consulted stakeholders before announcing the change.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Should',
                            'options' => ['Should', 'Must', 'Could'],
                            'verb_hint' => 'strategic hindsight',
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'The figures align so closely that auditors {a1} have coordinated.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Must',
                            'options' => ['Must', 'Might', 'Could'],
                            'verb_hint' => 'forensic deduction',
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'They {a1} realistically absorb another acquisition this quarter.',
                    'markers' => [
                        'a1' => [
                            'answer' => "Can't",
                            'options' => ["Can't", 'Should', 'Might'],
                            'verb_hint' => 'capacity limit',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'negative',
                    'tense' => 'future',
                    'question' => 'Until the audit concludes, departments {a1} authorize new spending.',
                    'markers' => [
                        'a1' => [
                            'answer' => "Won't",
                            'options' => ["Won't", "Can't", "Shouldn't"],
                            'verb_hint' => 'authorization hold',
                        ],
                    ],
                ],
            ],
        ];
    }
}
