<?php

namespace Database\Seeders\AI\Claude;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class ClaudeDoDoesIsAreAuxiliaryVerbsSeeder extends QuestionSeeder
{
    private array $optionVariantMap = [
        'do' => [
            ['value' => 'do not', 'type' => 'full'],
            ['value' => "don't", 'type' => 'contracted'],
            ['value' => 'does', 'type' => 'related'],
            ['value' => 'does not', 'type' => 'full'],
            ['value' => "doesn't", 'type' => 'contracted'],
        ],
        'do not' => [
            ['value' => 'do', 'type' => 'related'],
            ['value' => "don't", 'type' => 'contracted'],
            ['value' => 'does not', 'type' => 'full'],
            ['value' => "doesn't", 'type' => 'contracted'],
        ],
        "don't" => [
            ['value' => 'do', 'type' => 'related'],
            ['value' => 'do not', 'type' => 'full'],
            ['value' => 'does', 'type' => 'related'],
            ['value' => 'does not', 'type' => 'full'],
            ['value' => "doesn't", 'type' => 'contracted'],
        ],
        'does' => [
            ['value' => 'do', 'type' => 'related'],
            ['value' => 'do not', 'type' => 'full'],
            ['value' => "don't", 'type' => 'contracted'],
            ['value' => 'does not', 'type' => 'full'],
            ['value' => "doesn't", 'type' => 'contracted'],
        ],
        'does not' => [
            ['value' => 'does', 'type' => 'related'],
            ['value' => "doesn't", 'type' => 'contracted'],
            ['value' => 'do', 'type' => 'related'],
            ['value' => "don't", 'type' => 'contracted'],
        ],
        "doesn't" => [
            ['value' => 'does', 'type' => 'related'],
            ['value' => 'does not', 'type' => 'full'],
            ['value' => 'do', 'type' => 'related'],
            ['value' => 'do not', 'type' => 'full'],
        ],
        'did' => [
            ['value' => 'did not', 'type' => 'full'],
            ['value' => "didn't", 'type' => 'contracted'],
            ['value' => 'do', 'type' => 'related'],
            ['value' => 'does', 'type' => 'related'],
        ],
        'did not' => [
            ['value' => 'did', 'type' => 'related'],
            ['value' => "didn't", 'type' => 'contracted'],
            ['value' => 'do', 'type' => 'related'],
            ['value' => "don't", 'type' => 'contracted'],
        ],
        "didn't" => [
            ['value' => 'did', 'type' => 'related'],
            ['value' => 'did not', 'type' => 'full'],
            ['value' => 'do', 'type' => 'related'],
            ['value' => 'do not', 'type' => 'full'],
        ],
        'am' => [
            ['value' => 'am not', 'type' => 'full'],
            ['value' => "I'm", 'type' => 'contracted'],
            ['value' => "I'm not", 'type' => 'contracted'],
            ['value' => 'are', 'type' => 'related'],
            ['value' => "aren't", 'type' => 'contracted'],
        ],
        'am not' => [
            ['value' => "I'm not", 'type' => 'contracted'],
            ['value' => "I'm", 'type' => 'contracted'],
            ['value' => 'are', 'type' => 'related'],
            ['value' => "aren't", 'type' => 'contracted'],
        ],
        "I'm" => [
            ['value' => 'am', 'type' => 'related'],
            ['value' => 'am not', 'type' => 'full'],
            ['value' => "I'm not", 'type' => 'contracted'],
        ],
        "I'm not" => [
            ['value' => 'am not', 'type' => 'full'],
            ['value' => 'am', 'type' => 'related'],
        ],
        'is' => [
            ['value' => 'is not', 'type' => 'full'],
            ['value' => "isn't", 'type' => 'contracted'],
            ['value' => 'are', 'type' => 'related'],
            ['value' => "aren't", 'type' => 'contracted'],
        ],
        'is not' => [
            ['value' => 'is', 'type' => 'related'],
            ['value' => "isn't", 'type' => 'contracted'],
        ],
        "isn't" => [
            ['value' => 'is', 'type' => 'related'],
            ['value' => 'is not', 'type' => 'full'],
            ['value' => 'are', 'type' => 'related'],
            ['value' => "aren't", 'type' => 'contracted'],
        ],
        'are' => [
            ['value' => 'are not', 'type' => 'full'],
            ['value' => "aren't", 'type' => 'contracted'],
            ['value' => 'am', 'type' => 'related'],
            ['value' => 'am not', 'type' => 'full'],
        ],
        'are not' => [
            ['value' => 'are', 'type' => 'related'],
            ['value' => "aren't", 'type' => 'contracted'],
        ],
        "aren't" => [
            ['value' => 'are', 'type' => 'related'],
            ['value' => 'are not', 'type' => 'full'],
            ['value' => 'am', 'type' => 'related'],
            ['value' => 'am not', 'type' => 'full'],
        ],
        'was' => [
            ['value' => 'was not', 'type' => 'full'],
            ['value' => "wasn't", 'type' => 'contracted'],
            ['value' => 'were', 'type' => 'related'],
            ['value' => "weren't", 'type' => 'contracted'],
        ],
        'was not' => [
            ['value' => 'was', 'type' => 'related'],
            ['value' => "wasn't", 'type' => 'contracted'],
        ],
        "wasn't" => [
            ['value' => 'was', 'type' => 'related'],
            ['value' => 'was not', 'type' => 'full'],
            ['value' => 'were', 'type' => 'related'],
            ['value' => "weren't", 'type' => 'contracted'],
        ],
        'were' => [
            ['value' => 'were not', 'type' => 'full'],
            ['value' => "weren't", 'type' => 'contracted'],
            ['value' => 'was', 'type' => 'related'],
            ['value' => "wasn't", 'type' => 'contracted'],
        ],
        'were not' => [
            ['value' => 'were', 'type' => 'related'],
            ['value' => "weren't", 'type' => 'contracted'],
        ],
        "weren't" => [
            ['value' => 'were', 'type' => 'related'],
            ['value' => 'were not', 'type' => 'full'],
            ['value' => 'was', 'type' => 'related'],
            ['value' => 'was not', 'type' => 'full'],
        ],
        'will' => [
            ['value' => 'will be', 'type' => 'related'],
            ['value' => 'will not', 'type' => 'full'],
            ['value' => 'will not be', 'type' => 'full'],
            ['value' => "won't", 'type' => 'contracted'],
            ['value' => "won't be", 'type' => 'contracted'],
        ],
        'will not' => [
            ['value' => 'will', 'type' => 'related'],
            ['value' => 'will be', 'type' => 'related'],
            ['value' => 'will not be', 'type' => 'full'],
            ['value' => "won't", 'type' => 'contracted'],
            ['value' => "won't be", 'type' => 'contracted'],
        ],
        "won't" => [
            ['value' => 'will', 'type' => 'related'],
            ['value' => 'will not', 'type' => 'full'],
            ['value' => 'will be', 'type' => 'related'],
            ['value' => 'will not be', 'type' => 'full'],
            ['value' => "won't be", 'type' => 'contracted'],
        ],
        'will be' => [
            ['value' => 'will', 'type' => 'related'],
            ['value' => 'will not', 'type' => 'full'],
            ['value' => 'will not be', 'type' => 'full'],
            ['value' => "won't", 'type' => 'contracted'],
            ['value' => "won't be", 'type' => 'contracted'],
        ],
        'will not be' => [
            ['value' => 'will be', 'type' => 'related'],
            ['value' => 'will', 'type' => 'related'],
            ['value' => 'will not', 'type' => 'full'],
            ['value' => "won't", 'type' => 'contracted'],
            ['value' => "won't be", 'type' => 'contracted'],
        ],
        "won't be" => [
            ['value' => 'will be', 'type' => 'related'],
            ['value' => 'will', 'type' => 'related'],
            ['value' => 'will not', 'type' => 'full'],
            ['value' => 'will not be', 'type' => 'full'],
            ['value' => "won't", 'type' => 'contracted'],
        ],
    ];

    private array $levelDifficulty = [
        'A1' => 1,
        'A2' => 2,
        'B1' => 3,
        'B2' => 4,
        'C1' => 5,
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Auxiliary Verbs Do Does Is Are'])->id;

        $sourceId = Source::firstOrCreate([
            'name' => 'Do / Does vs. Is / Are — Auxiliary Verbs (gramlyze.com)',
        ])->id;

        $sectionSources = [
            'present' => Source::firstOrCreate(['name' => 'Claude: Present Do/Does/To Be Practice'])->id,
            'past' => Source::firstOrCreate(['name' => 'Claude: Past Did/Was/Were Practice'])->id,
            'future' => Source::firstOrCreate(['name' => 'Claude: Future Will/Be Practice'])->id,
        ];

        $themeTags = [
            'present' => Tag::firstOrCreate(['name' => 'Present Auxiliaries'], ['category' => 'English Grammar Theme'])->id,
            'past' => Tag::firstOrCreate(['name' => 'Past Auxiliaries'], ['category' => 'English Grammar Theme'])->id,
            'future' => Tag::firstOrCreate(['name' => 'Future Auxiliaries'], ['category' => 'English Grammar Theme'])->id,
        ];

        $detailTags = [
            'do_does_choice' => Tag::firstOrCreate(['name' => 'Do/Does Auxiliary Choice'], ['category' => 'English Grammar Detail'])->id,
            'to_be_choice' => Tag::firstOrCreate(['name' => 'To Be Auxiliary Choice'], ['category' => 'English Grammar Detail'])->id,
            'did_choice' => Tag::firstOrCreate(['name' => 'Did Auxiliary Choice'], ['category' => 'English Grammar Detail'])->id,
            'was_were_choice' => Tag::firstOrCreate(['name' => 'Was/Were Choice'], ['category' => 'English Grammar Detail'])->id,
            'will_choice' => Tag::firstOrCreate(['name' => 'Will Auxiliary Choice'], ['category' => 'English Grammar Detail'])->id,
            'will_be_choice' => Tag::firstOrCreate(['name' => 'Will Be Choice'], ['category' => 'English Grammar Detail'])->id,
        ];

        $formTags = [
            'question' => Tag::firstOrCreate(['name' => 'Interrogative Form'], ['category' => 'English Grammar Form'])->id,
            'negative' => Tag::firstOrCreate(['name' => 'Negative Form'], ['category' => 'English Grammar Form'])->id,
            'affirmative' => Tag::firstOrCreate(['name' => 'Affirmative Form'], ['category' => 'English Grammar Form'])->id,
        ];

        $tenseTags = [
            'Present Simple' => Tag::firstOrCreate(['name' => 'Present Simple'], ['category' => 'English Grammar Tense'])->id,
            'Past Simple' => Tag::firstOrCreate(['name' => 'Past Simple'], ['category' => 'English Grammar Tense'])->id,
            'Future Simple' => Tag::firstOrCreate(['name' => 'Future Simple'], ['category' => 'English Grammar Tense'])->id,
        ];

        $aiSourceTag = Tag::firstOrCreate(['name' => 'Claude AI Generated'], ['category' => 'Source Type'])->id;
        $auxiliaryVerbTag = Tag::firstOrCreate(['name' => 'Auxiliary Verbs'], ['category' => 'English Grammar Topic'])->id;

        $patternConfig = [
            'present_do_question' => [
                'section' => 'present',
                'tense' => 'Present Simple',
                'option_pool' => ['do', 'does', 'am', 'is', 'are'],
                'detail' => 'do_does_choice',
                'form' => 'question',
                'hint_short' => 'Present Simple question',
                'verb_hint' => 'auxiliary for action',
                'markers' => 'every day, usually, often, always',
            ],
            'present_do_negative' => [
                'section' => 'present',
                'tense' => 'Present Simple',
                'option_pool' => ["don't", "doesn't", 'am not', "isn't", "aren't"],
                'detail' => 'do_does_choice',
                'form' => 'negative',
                'hint_short' => 'Present Simple negative',
                'verb_hint' => 'negation for action',
                'markers' => 'never, not usually, rarely',
            ],
            'present_be_question' => [
                'section' => 'present',
                'tense' => 'Present Simple',
                'option_pool' => ['am', 'is', 'are', 'do', 'does'],
                'detail' => 'to_be_choice',
                'form' => 'question',
                'hint_short' => 'To be question (present)',
                'verb_hint' => 'state or description',
                'markers' => 'now, today, at the moment',
            ],
            'present_be_negative' => [
                'section' => 'present',
                'tense' => 'Present Simple',
                'option_pool' => ['am not', "isn't", "aren't", "don't", "doesn't"],
                'detail' => 'to_be_choice',
                'form' => 'negative',
                'hint_short' => 'To be negative (present)',
                'verb_hint' => 'negative state',
                'markers' => 'now, today, these days',
            ],
            'past_do_question' => [
                'section' => 'past',
                'tense' => 'Past Simple',
                'option_pool' => ['did', 'do', 'does', 'was', 'were'],
                'detail' => 'did_choice',
                'form' => 'question',
                'hint_short' => 'Past Simple question',
                'verb_hint' => 'past auxiliary for action',
                'markers' => 'yesterday, last night, ago',
            ],
            'past_do_negative' => [
                'section' => 'past',
                'tense' => 'Past Simple',
                'option_pool' => ["didn't", "don't", "doesn't", "wasn't", "weren't"],
                'detail' => 'did_choice',
                'form' => 'negative',
                'hint_short' => 'Past Simple negative',
                'verb_hint' => 'past negation for action',
                'markers' => 'yesterday, last week, ago',
            ],
            'past_be_question' => [
                'section' => 'past',
                'tense' => 'Past Simple',
                'option_pool' => ['was', 'were', 'is', 'are', 'did'],
                'detail' => 'was_were_choice',
                'form' => 'question',
                'hint_short' => 'To be question (past)',
                'verb_hint' => 'past state',
                'markers' => 'yesterday, last night, last year',
            ],
            'past_be_negative' => [
                'section' => 'past',
                'tense' => 'Past Simple',
                'option_pool' => ["wasn't", "weren't", "isn't", "aren't", "didn't"],
                'detail' => 'was_were_choice',
                'form' => 'negative',
                'hint_short' => 'To be negative (past)',
                'verb_hint' => 'past negative state',
                'markers' => 'yesterday, last week, ago',
            ],
            'future_do_question' => [
                'section' => 'future',
                'tense' => 'Future Simple',
                'option_pool' => ['will', 'do', 'does', 'did', 'is', 'are'],
                'detail' => 'will_choice',
                'form' => 'question',
                'hint_short' => 'Future Simple question',
                'verb_hint' => 'future auxiliary for action',
                'markers' => 'tomorrow, next week, soon',
            ],
            'future_do_negative' => [
                'section' => 'future',
                'tense' => 'Future Simple',
                'option_pool' => ["won't", 'will not', "don't", "doesn't", "didn't"],
                'detail' => 'will_choice',
                'form' => 'negative',
                'hint_short' => 'Future Simple negative',
                'verb_hint' => 'future negation for action',
                'markers' => 'tomorrow, next month, later',
            ],
            'future_be_question' => [
                'section' => 'future',
                'tense' => 'Future Simple',
                'option_pool' => ['will be', 'will', 'is', 'are', 'was', 'were'],
                'detail' => 'will_be_choice',
                'form' => 'question',
                'hint_short' => 'To be question (future)',
                'verb_hint' => 'future state',
                'markers' => 'tomorrow, next week, soon',
            ],
            'future_be_negative' => [
                'section' => 'future',
                'tense' => 'Future Simple',
                'option_pool' => ["won't be", 'will not be', "isn't", "aren't", "wasn't"],
                'detail' => 'will_be_choice',
                'form' => 'negative',
                'hint_short' => 'To be negative (future)',
                'verb_hint' => 'future negative state',
                'markers' => 'tomorrow, next year, later',
            ],
        ];

        $entries = $this->getQuestionEntries();

        $items = [];
        $meta = [];

        foreach ($entries as $index => $entry) {
            $config = $patternConfig[$entry['pattern']];
            $answer = $entry['answer'];
            $example = $this->formatExample($entry['question'], $answer);

            $options = $this->buildOptions($config['option_pool'], $answer);

            $uuid = $this->generateQuestionUuid($entry['level'], $config['section'], $index, $entry['question']);

            $tagIds = [
                $themeTags[$config['section']],
                $detailTags[$config['detail']],
                $formTags[$config['form']],
                $tenseTags[$config['tense']],
                $aiSourceTag,
                $auxiliaryVerbTag,
            ];

            $answers = [
                [
                    'marker' => 'a1',
                    'answer' => $answer,
                    'verb_hint' => $config['verb_hint'],
                ],
            ];

            $optionMarkerMap = [];
            foreach ($options as $option) {
                $optionMarkerMap[$option] = 'a1';
            }

            $items[] = [
                'uuid' => $uuid,
                'question' => $entry['question'],
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$entry['level']] ?? 3,
                'source_id' => $sectionSources[$config['section']],
                'flag' => 2,
                'type' => 0,
                'level' => $entry['level'],
                'tag_ids' => $tagIds,
                'answers' => $answers,
                'options' => $options,
                'variants' => [],
            ];

            $meta[] = [
                'uuid' => $uuid,
                'answers' => ['a1' => $answer],
                'option_markers' => $optionMarkerMap,
                'hints' => ['a1' => $this->buildHint($entry['pattern'], $entry, $answer, $example, $config)],
                'explanations' => $this->buildExplanations($entry['pattern'], $entry, $options, $answer, $example, $config['tense']),
            ];
        }

        $this->seedQuestionData($items, $meta);
    }

    private function getQuestionEntries(): array
    {
        return [
            // A1 - 12 questions (basic everyday situations)
            $this->entry('A1', 'present_do_question', '{a1} you speak English?', 'do', 'you', 'you'),
            $this->entry('A1', 'present_do_question', '{a1} she like ice cream?', 'does', 'she', 'third_singular'),
            $this->entry('A1', 'present_do_negative', 'I {a1} eat fish.', "don't", 'I', 'i'),
            $this->entry('A1', 'present_do_negative', 'He {a1} drink coffee.', "doesn't", 'He', 'third_singular'),
            $this->entry('A1', 'present_be_question', '{a1} you happy today?', 'are', 'you', 'you'),
            $this->entry('A1', 'present_be_question', '{a1} the dog hungry?', 'is', 'the dog', 'third_singular'),
            $this->entry('A1', 'present_be_negative', 'They {a1} at home now.', "aren't", 'They', 'plural'),
            $this->entry('A1', 'past_do_question', '{a1} you go to school yesterday?', 'did', 'you', 'you'),
            $this->entry('A1', 'past_be_question', '{a1} she sick last week?', 'was', 'she', 'third_singular'),
            $this->entry('A1', 'past_be_negative', 'We {a1} late for the bus.', "weren't", 'We', 'plural'),
            $this->entry('A1', 'future_do_question', '{a1} you come to the party?', 'will', 'you', 'you'),
            $this->entry('A1', 'future_be_negative', 'It {a1} cold tomorrow.', "won't be", 'It', 'third_singular'),

            // A2 - 12 questions (expanded everyday situations)
            $this->entry('A2', 'present_do_question', '{a1} your parents work in an office?', 'do', 'your parents', 'plural'),
            $this->entry('A2', 'present_do_question', '{a1} this train stop at the next station?', 'does', 'this train', 'third_singular'),
            $this->entry('A2', 'present_do_negative', 'My friends {a1} live near the beach.', "don't", 'My friends', 'plural'),
            $this->entry('A2', 'present_do_negative', 'The shop {a1} open on Sundays.', "doesn't", 'The shop', 'third_singular'),
            $this->entry('A2', 'present_be_question', '{a1} the children ready for dinner?', 'are', 'the children', 'plural'),
            $this->entry('A2', 'present_be_negative', 'This exercise {a1} difficult for me.', "isn't", 'This exercise', 'third_singular'),
            $this->entry('A2', 'past_do_question', '{a1} they finish their homework on time?', 'did', 'they', 'plural'),
            $this->entry('A2', 'past_do_negative', 'She {a1} call me back yesterday.', "didn't", 'She', 'third_singular'),
            $this->entry('A2', 'past_be_question', '{a1} the concert good last night?', 'was', 'the concert', 'third_singular'),
            $this->entry('A2', 'past_be_negative', 'The tickets {a1} expensive.', "weren't", 'The tickets', 'plural'),
            $this->entry('A2', 'future_do_question', '{a1} they arrive before noon?', 'will', 'they', 'plural'),
            $this->entry('A2', 'future_be_question', 'Where {a1} the meeting tomorrow?', 'will be', 'the meeting', 'third_singular'),

            // B1 - 12 questions (intermediate social and work contexts)
            $this->entry('B1', 'present_do_question', '{a1} you usually check your emails in the morning?', 'do', 'you', 'you'),
            $this->entry('B1', 'present_do_question', '{a1} the company offer training programs?', 'does', 'the company', 'third_singular'),
            $this->entry('B1', 'present_do_negative', 'We {a1} accept credit cards at this location.', "don't", 'We', 'plural'),
            $this->entry('B1', 'present_be_question', '{a1} the new policies clear to everyone?', 'are', 'the new policies', 'plural'),
            $this->entry('B1', 'present_be_negative', 'I {a1} sure about the exact date.', 'am not', 'I', 'i'),
            $this->entry('B1', 'past_do_question', '{a1} you receive my message last night?', 'did', 'you', 'you'),
            $this->entry('B1', 'past_do_negative', 'The manager {a1} approve the budget.', "didn't", 'The manager', 'third_singular'),
            $this->entry('B1', 'past_be_question', '{a1} they satisfied with the service?', 'were', 'they', 'plural'),
            $this->entry('B1', 'past_be_negative', 'The hotel {a1} as clean as expected.', "wasn't", 'The hotel', 'third_singular'),
            $this->entry('B1', 'future_do_question', '{a1} you attend the conference next month?', 'will', 'you', 'you'),
            $this->entry('B1', 'future_do_negative', 'She {a1} miss the important deadline.', "won't", 'She', 'third_singular'),
            $this->entry('B1', 'future_be_question', 'Who {a1} responsible for the presentation?', 'will be', 'Who', 'third_singular'),

            // B2 - 12 questions (advanced professional and academic contexts)
            $this->entry('B2', 'present_do_question', '{a1} the research team consider alternative methods?', 'does', 'the research team', 'third_singular'),
            $this->entry('B2', 'present_do_negative', 'The regulations {a1} allow such exceptions.', "don't", 'The regulations', 'plural'),
            $this->entry('B2', 'present_be_question', '{a1} the proposed changes feasible within the budget?', 'are', 'the proposed changes', 'plural'),
            $this->entry('B2', 'present_be_negative', 'This solution {a1} compatible with older systems.', "isn't", 'This solution', 'third_singular'),
            $this->entry('B2', 'past_do_question', '{a1} the committee review all the applications?', 'did', 'the committee', 'third_singular'),
            $this->entry('B2', 'past_do_negative', 'They {a1} anticipate such high demand.', "didn't", 'They', 'plural'),
            $this->entry('B2', 'past_be_question', '{a1} the findings consistent with previous studies?', 'were', 'the findings', 'plural'),
            $this->entry('B2', 'past_be_negative', 'I {a1} aware of the policy change at that time.', "wasn't", 'I', 'i'),
            $this->entry('B2', 'future_do_question', '{a1} the board approve the merger proposal?', 'will', 'the board', 'third_singular'),
            $this->entry('B2', 'future_do_negative', 'We {a1} proceed without proper authorization.', "won't", 'We', 'plural'),
            $this->entry('B2', 'future_be_question', 'What {a1} the implications for the industry?', 'will be', 'the implications', 'plural'),
            $this->entry('B2', 'future_be_negative', 'The transition {a1} smooth without adequate preparation.', "won't be", 'The transition', 'third_singular'),

            // C1 - 12 questions (expert-level complex structures)
            $this->entry('C1', 'present_do_question', '{a1} the current framework adequately address emerging challenges?', 'does', 'the current framework', 'third_singular'),
            $this->entry('C1', 'present_do_negative', 'The preliminary results {a1} support the initial hypothesis.', "don't", 'The preliminary results', 'plural'),
            $this->entry('C1', 'present_be_question', '{a1} the underlying assumptions valid in this context?', 'are', 'the underlying assumptions', 'plural'),
            $this->entry('C1', 'present_be_negative', 'This interpretation {a1} consistent with the established doctrine.', "isn't", 'This interpretation', 'third_singular'),
            $this->entry('C1', 'past_do_question', '{a1} the auditors identify any material discrepancies?', 'did', 'the auditors', 'plural'),
            $this->entry('C1', 'past_do_negative', 'The stakeholders {a1} foresee the market volatility.', "didn't", 'The stakeholders', 'plural'),
            $this->entry('C1', 'past_be_question', '{a1} the delegation prepared for the negotiations?', 'was', 'the delegation', 'third_singular'),
            $this->entry('C1', 'past_be_negative', 'We {a1} accountable for the oversight during that period.', "weren't", 'We', 'plural'),
            $this->entry('C1', 'future_do_question', '{a1} the regulatory body enforce stricter compliance measures?', 'will', 'the regulatory body', 'third_singular'),
            $this->entry('C1', 'future_do_negative', 'She {a1} compromise on the ethical standards.', "won't", 'She', 'third_singular'),
            $this->entry('C1', 'future_be_question', 'Who {a1} liable for the contractual obligations?', 'will be', 'Who', 'third_singular'),
            $this->entry('C1', 'future_be_negative', 'The proposed legislation {a1} effective without bipartisan support.', "won't be", 'The proposed legislation', 'third_singular'),
        ];
    }

    private function entry(string $level, string $pattern, string $question, string $answer, string $subject, string $subjectCategory): array
    {
        return [
            'level' => $level,
            'pattern' => $pattern,
            'question' => $question,
            'answer' => $answer,
            'subject' => $subject,
            'subject_category' => $subjectCategory,
        ];
    }

    private function buildOptions(array $baseOptions, string $answer): array
    {
        $pool = [];
        $types = [];

        $addOption = function (string $value, string $type = 'base') use (&$pool, &$types): bool {
            $value = trim($value);

            if ($value === '') {
                return false;
            }

            if (! isset($types[$value])) {
                $types[$value] = [$type];
                $pool[] = $value;

                return true;
            }

            if (! in_array($type, $types[$value], true)) {
                $types[$value][] = $type;
            }

            return false;
        };

        foreach ($baseOptions as $baseOption) {
            $addOption($baseOption);
        }

        $addOption($answer, 'answer');

        $queue = $pool;
        $index = 0;

        while ($index < count($queue)) {
            $current = $queue[$index++];

            foreach ($this->optionVariantMap[$current] ?? [] as $variant) {
                $value = is_array($variant) ? ($variant['value'] ?? '') : $variant;
                $type = is_array($variant) ? ($variant['type'] ?? 'variant') : 'variant';

                if ($addOption($value, $type)) {
                    $queue[] = $value;
                }
            }
        }

        $answerVariants = $this->optionVariantMap[$answer] ?? [];
        $fullVariants = [];
        $contractedVariants = [];
        $relatedVariants = [];

        foreach ($answerVariants as $variant) {
            $value = is_array($variant) ? ($variant['value'] ?? '') : $variant;
            $type = is_array($variant) ? ($variant['type'] ?? 'related') : 'related';

            if ($value === '' || $value === $answer) {
                continue;
            }

            if (! isset($types[$value])) {
                continue;
            }

            if ($type === 'full') {
                $fullVariants[] = $value;
            } elseif ($type === 'contracted') {
                $contractedVariants[] = $value;
            } else {
                $relatedVariants[] = $value;
            }
        }

        $targetCount = 4;
        $selected = [$answer];
        $used = [$answer => true];

        if ($fullChoice = $this->pickRandom($fullVariants)) {
            $selected[] = $fullChoice;
            $used[$fullChoice] = true;
        }

        if ($contractedChoice = $this->pickRandom($contractedVariants)) {
            if (! isset($used[$contractedChoice])) {
                $selected[] = $contractedChoice;
                $used[$contractedChoice] = true;
            }
        }

        if (count($selected) < $targetCount && ! empty($relatedVariants)) {
            $relatedChoice = $this->pickRandom(array_values(array_filter($relatedVariants, fn ($value) => ! isset($used[$value]))));

            if ($relatedChoice !== null) {
                $selected[] = $relatedChoice;
                $used[$relatedChoice] = true;
            }
        }

        $remaining = array_values(array_filter($pool, fn ($value) => ! isset($used[$value])));
        shuffle($remaining);

        while (count($selected) < $targetCount && ! empty($remaining)) {
            $value = array_shift($remaining);

            if (! isset($used[$value])) {
                $selected[] = $value;
                $used[$value] = true;
            }
        }

        if (count($selected) < $targetCount) {
            foreach ($pool as $value) {
                if (isset($used[$value])) {
                    continue;
                }

                $selected[] = $value;
                $used[$value] = true;

                if (count($selected) >= $targetCount) {
                    break;
                }
            }
        }

        $selected = array_values(array_unique($selected));
        shuffle($selected);

        return $selected;
    }

    private function pickRandom(array $values): ?string
    {
        $values = array_values(array_unique(array_filter($values, fn ($value) => $value !== '')));

        if (empty($values)) {
            return null;
        }

        if (count($values) === 1) {
            return $values[0];
        }

        $index = random_int(0, count($values) - 1);

        return $values[$index];
    }

    private function buildHint(string $pattern, array $entry, string $answer, string $example, array $config): string
    {
        $subject = $entry['subject'];
        $markers = $config['markers'];

        $hintTemplates = [
            'present_do_question' => "**Час**: {$config['tense']}\n**Формула**: Auxiliary (do/does) + Subject + V1 + ...?\n**Правило**: Для питань у Present Simple використовуємо допоміжне дієслово «do» (I/you/we/they) або «does» (he/she/it).\n**Підмет**: «{$subject}» — визначте, чи це однина третьої особи.\n**Маркери часу**: {$markers}\n**Приклад**: *{$example}*",
            'present_do_negative' => "**Час**: {$config['tense']}\n**Формула**: Subject + do/does + not + V1\n**Правило**: Заперечення у Present Simple формуємо через «do not» (don't) або «does not» (doesn't).\n**Підмет**: «{$subject}» — визначте форму допоміжного дієслова.\n**Маркери часу**: {$markers}\n**Приклад**: *{$example}*",
            'present_be_question' => "**Час**: {$config['tense']}\n**Формула**: To Be (am/is/are) + Subject + Adjective/Noun?\n**Правило**: Для опису стану чи якості використовуємо дієслово «to be».\n**Підмет**: «{$subject}» — оберіть відповідну форму am/is/are.\n**Маркери часу**: {$markers}\n**Приклад**: *{$example}*",
            'present_be_negative' => "**Час**: {$config['tense']}\n**Формула**: Subject + am/is/are + not + Adjective/Noun\n**Правило**: Заперечення з «to be» формуємо додаванням «not» після дієслова.\n**Підмет**: «{$subject}» — оберіть правильну форму заперечення.\n**Маркери часу**: {$markers}\n**Приклад**: *{$example}*",
            'past_do_question' => "**Час**: {$config['tense']}\n**Формула**: Did + Subject + V1 + ...?\n**Правило**: Для питань у Past Simple завжди використовуємо «did», незалежно від особи.\n**Підмет**: «{$subject}»\n**Маркери часу**: {$markers}\n**Приклад**: *{$example}*",
            'past_do_negative' => "**Час**: {$config['tense']}\n**Формула**: Subject + did not (didn't) + V1\n**Правило**: Заперечення у Past Simple формуємо через «did not» для всіх осіб.\n**Підмет**: «{$subject}»\n**Маркери часу**: {$markers}\n**Приклад**: *{$example}*",
            'past_be_question' => "**Час**: {$config['tense']}\n**Формула**: Was/Were + Subject + Adjective/Noun?\n**Правило**: Для опису минулого стану використовуємо «was» (I/he/she/it) або «were» (you/we/they).\n**Підмет**: «{$subject}» — визначте число підмета.\n**Маркери часу**: {$markers}\n**Приклад**: *{$example}*",
            'past_be_negative' => "**Час**: {$config['tense']}\n**Формула**: Subject + was/were + not + Adjective/Noun\n**Правило**: Заперечення з «to be» у минулому формуємо через «was not» або «were not».\n**Підмет**: «{$subject}» — оберіть правильну форму.\n**Маркери часу**: {$markers}\n**Приклад**: *{$example}*",
            'future_do_question' => "**Час**: {$config['tense']}\n**Формула**: Will + Subject + V1 + ...?\n**Правило**: Для питань про майбутні дії використовуємо «will» перед підметом.\n**Підмет**: «{$subject}»\n**Маркери часу**: {$markers}\n**Приклад**: *{$example}*",
            'future_do_negative' => "**Час**: {$config['tense']}\n**Формула**: Subject + will not (won't) + V1\n**Правило**: Заперечення у Future Simple формуємо через «will not» або скорочено «won't».\n**Підмет**: «{$subject}»\n**Маркери часу**: {$markers}\n**Приклад**: *{$example}*",
            'future_be_question' => "**Час**: {$config['tense']}\n**Формула**: Will + Subject + be + Adjective/Noun?\n**Правило**: Для опису майбутнього стану використовуємо «will be».\n**Підмет**: «{$subject}»\n**Маркери часу**: {$markers}\n**Приклад**: *{$example}*",
            'future_be_negative' => "**Час**: {$config['tense']}\n**Формула**: Subject + will not be (won't be) + Adjective/Noun\n**Правило**: Заперечення майбутнього стану формуємо через «will not be».\n**Підмет**: «{$subject}»\n**Маркери часу**: {$markers}\n**Приклад**: *{$example}*",
        ];

        return $hintTemplates[$pattern] ?? '';
    }

    private function buildExplanations(string $pattern, array $entry, array $options, string $answer, string $example, string $tenseLabel): array
    {
        $subjectPhrase = $this->subjectPhrase($entry['subject_category'], $entry['subject']);

        $explanations = [];
        foreach ($options as $option) {
            if ($option === $answer) {
                $explanations[$option] = $this->buildCorrectExplanation($pattern, $subjectPhrase, $answer, $example, $tenseLabel);
            } else {
                $explanations[$option] = $this->buildWrongExplanation($pattern, $subjectPhrase, $option, $answer, $tenseLabel);
            }
        }

        return $explanations;
    }

    private function buildCorrectExplanation(string $pattern, string $subjectPhrase, string $answer, string $example, string $tenseLabel): string
    {
        $explanationTemplates = [
            'present_do_question' => "✅ Правильно! «{$answer}» використовується для формування питань у {$tenseLabel} з {$subjectPhrase}.\n\n**Формула**: {$answer} + Subject + V1 + ...?\n**Приклад**: *{$example}*",
            'present_do_negative' => "✅ Правильно! «{$answer}» — це заперечна форма допоміжного дієслова для {$subjectPhrase} у {$tenseLabel}.\n\n**Формула**: Subject + {$answer} + V1\n**Приклад**: *{$example}*",
            'present_be_question' => "✅ Правильно! «{$answer}» — це форма дієслова «to be» для {$subjectPhrase} у {$tenseLabel} питанні.\n\n**Формула**: {$answer} + Subject + Adjective?\n**Приклад**: *{$example}*",
            'present_be_negative' => "✅ Правильно! «{$answer}» — заперечна форма «to be» для {$subjectPhrase} у {$tenseLabel}.\n\n**Формула**: Subject + {$answer} + Adjective\n**Приклад**: *{$example}*",
            'past_do_question' => "✅ Правильно! У {$tenseLabel} питанні завжди використовуємо «{$answer}» незалежно від особи.\n\n**Формула**: {$answer} + Subject + V1 + ...?\n**Приклад**: *{$example}*",
            'past_do_negative' => "✅ Правильно! «{$answer}» — заперечна форма для {$tenseLabel} з усіма підметами.\n\n**Формула**: Subject + {$answer} + V1\n**Приклад**: *{$example}*",
            'past_be_question' => "✅ Правильно! «{$answer}» — форма «to be» у минулому часі для {$subjectPhrase}.\n\n**Формула**: {$answer} + Subject + Adjective?\n**Приклад**: *{$example}*",
            'past_be_negative' => "✅ Правильно! «{$answer}» — заперечна форма «to be» у минулому для {$subjectPhrase}.\n\n**Формула**: Subject + {$answer} + Adjective\n**Приклад**: *{$example}*",
            'future_do_question' => "✅ Правильно! «{$answer}» використовується для формування питань у {$tenseLabel}.\n\n**Формула**: {$answer} + Subject + V1 + ...?\n**Приклад**: *{$example}*",
            'future_do_negative' => "✅ Правильно! «{$answer}» — заперечна форма для {$tenseLabel}.\n\n**Формула**: Subject + {$answer} + V1\n**Приклад**: *{$example}*",
            'future_be_question' => "✅ Правильно! «{$answer}» використовується для опису майбутнього стану.\n\n**Формула**: {$answer} + Subject + Adjective?\n**Приклад**: *{$example}*",
            'future_be_negative' => "✅ Правильно! «{$answer}» — заперечна форма майбутнього стану.\n\n**Формула**: Subject + {$answer} + Adjective\n**Приклад**: *{$example}*",
        ];

        return $explanationTemplates[$pattern] ?? "✅ Правильно! *{$example}*";
    }

    private function buildWrongExplanation(string $pattern, string $subjectPhrase, string $option, string $answer, string $tenseLabel): string
    {
        // Determine why this option is wrong
        $wrongReason = $this->getWrongReason($pattern, $subjectPhrase, $option, $answer, $tenseLabel);

        return "❌ «{$option}» — неправильно.\n\n{$wrongReason}";
    }

    private function getWrongReason(string $pattern, string $subjectPhrase, string $option, string $answer, string $tenseLabel): string
    {
        // Check for tense mismatch
        $presentForms = ['do', 'does', 'am', 'is', 'are', "don't", "doesn't", 'am not', "isn't", "aren't"];
        $pastForms = ['did', 'was', 'were', "didn't", "wasn't", "weren't"];
        $futureForms = ['will', 'will be', "won't", "won't be", 'will not', 'will not be'];

        $isPresentPattern = str_contains($pattern, 'present');
        $isPastPattern = str_contains($pattern, 'past');
        $isFuturePattern = str_contains($pattern, 'future');

        if ($isPresentPattern && in_array($option, $pastForms, true)) {
            return "**Причина**: «{$option}» — форма минулого часу (Past Simple), але речення потребує теперішнього часу (Present Simple).\n**Правило**: Використовуйте do/does для дій або am/is/are для станів у Present Simple.";
        }

        if ($isPresentPattern && in_array($option, $futureForms, true)) {
            return "**Причина**: «{$option}» — форма майбутнього часу (Future Simple), але речення потребує теперішнього часу.\n**Правило**: Використовуйте do/does або am/is/are для Present Simple.";
        }

        if ($isPastPattern && in_array($option, $presentForms, true)) {
            return "**Причина**: «{$option}» — форма теперішнього часу (Present Simple), але речення потребує минулого часу (Past Simple).\n**Правило**: Використовуйте did для дій або was/were для станів у Past Simple.";
        }

        if ($isPastPattern && in_array($option, $futureForms, true)) {
            return "**Причина**: «{$option}» — форма майбутнього часу, але речення потребує минулого часу.\n**Правило**: Використовуйте did або was/were для Past Simple.";
        }

        if ($isFuturePattern && in_array($option, $presentForms, true)) {
            return "**Причина**: «{$option}» — форма теперішнього часу, але речення потребує майбутнього часу (Future Simple).\n**Правило**: Використовуйте will для дій або will be для станів у Future Simple.";
        }

        if ($isFuturePattern && in_array($option, $pastForms, true)) {
            return "**Причина**: «{$option}» — форма минулого часу, але речення потребує майбутнього часу.\n**Правило**: Використовуйте will або will be для Future Simple.";
        }

        // Check for action vs state mismatch
        $isDoPattern = str_contains($pattern, '_do_');
        $isBePattern = str_contains($pattern, '_be_');

        if ($isDoPattern && in_array($option, ['am', 'is', 'are', 'was', 'were', 'will be', "isn't", "aren't", "wasn't", "weren't", "won't be", 'am not'], true)) {
            return "**Причина**: «{$option}» — форма дієслова «to be», яка використовується для опису станів.\n**Правило**: Для питань/заперечень з дієсловами дії використовуйте do/does/did/will.";
        }

        if ($isBePattern && in_array($option, ['do', 'does', 'did', 'will', "don't", "doesn't", "didn't", "won't"], true)) {
            return "**Причина**: «{$option}» — допоміжне дієслово для дій, але речення описує стан.\n**Правило**: Для опису станів використовуйте форми дієслова «to be» (am/is/are/was/were/will be).";
        }

        // Check for subject-verb agreement issues
        if ($option === 'does' && in_array($answer, ['do', "don't"], true)) {
            return "**Причина**: «{$option}» використовується з третьою особою однини (he/she/it), але {$subjectPhrase} потребує «do».\n**Правило**: I/you/we/they → do; he/she/it → does.";
        }

        if ($option === 'do' && in_array($answer, ['does', "doesn't"], true)) {
            return "**Причина**: «{$option}» використовується з I/you/we/they, але {$subjectPhrase} потребує «does».\n**Правило**: he/she/it/назви в однині → does.";
        }

        if ($option === 'is' && in_array($answer, ['are', "aren't"], true)) {
            return "**Причина**: «{$option}» використовується з одниною, але {$subjectPhrase} потребує «are».\n**Правило**: I → am; he/she/it → is; you/we/they/множина → are.";
        }

        if ($option === 'are' && in_array($answer, ['is', "isn't"], true)) {
            return "**Причина**: «{$option}» використовується з множиною, але {$subjectPhrase} потребує «is».\n**Правило**: he/she/it/назви в однині → is.";
        }

        if ($option === 'was' && in_array($answer, ['were', "weren't"], true)) {
            return "**Причина**: «{$option}» використовується з одниною у минулому часі, але {$subjectPhrase} потребує «were».\n**Правило**: I/he/she/it → was; you/we/they/множина → were.";
        }

        if ($option === 'were' && in_array($answer, ['was', "wasn't"], true)) {
            return "**Причина**: «{$option}» використовується з множиною у минулому часі, але {$subjectPhrase} потребує «was».\n**Правило**: he/she/it/назви в однині → was.";
        }

        // Check for affirmative vs negative mismatch
        $isNegativePattern = str_contains($pattern, 'negative');
        $isQuestionPattern = str_contains($pattern, 'question');

        if ($isNegativePattern && ! str_contains($option, 'not') && ! str_contains($option, "n't")) {
            return "**Причина**: «{$option}» — стверджувальна форма, але речення потребує заперечення.\n**Правило**: Для заперечень додайте «not» або використовуйте скорочену форму з «n't».";
        }

        if ($isQuestionPattern && (str_contains($option, 'not') || str_contains($option, "n't"))) {
            return "**Причина**: «{$option}» — заперечна форма, але питальне речення не містить заперечення.\n**Правило**: Для простих питань використовуйте стверджувальну форму допоміжного дієслова.";
        }

        // Default explanation
        return "**Причина**: Ця форма не відповідає граматичним правилам речення.\n**Правило**: Зверніть увагу на час, тип речення (питання/заперечення) та узгодження з підметом.";
    }

    private function subjectPhrase(string $category, string $subject): string
    {
        return match ($category) {
            'i' => 'займенника «I»',
            'you' => 'займенника «you»',
            'third_singular' => 'третьої особи однини («' . $subject . '»)',
            'plural' => 'множини («' . $subject . '»)',
            default => 'підмета «' . $subject . '»',
        };
    }
}
