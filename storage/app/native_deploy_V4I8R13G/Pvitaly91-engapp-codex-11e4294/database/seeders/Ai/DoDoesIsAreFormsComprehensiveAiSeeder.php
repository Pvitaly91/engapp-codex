<?php

namespace Database\Seeders\Ai;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class DoDoesIsAreFormsComprehensiveAiSeeder extends QuestionSeeder
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

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Do Does Am Is Are Advanced AI Test'])->id;

        $sectionSources = [
            'present' => Source::firstOrCreate(['name' => 'Present Do/Does/To Be Practice'])->id,
            'past' => Source::firstOrCreate(['name' => 'Past Do/Did/Was/Were Practice'])->id,
            'future' => Source::firstOrCreate(['name' => 'Future Will/Be Practice'])->id,
        ];

        $themeTags = [
            'present' => Tag::firstOrCreate(['name' => 'Present Auxiliaries Focus'], ['category' => 'English Grammar Theme'])->id,
            'past' => Tag::firstOrCreate(['name' => 'Past Auxiliaries Focus'], ['category' => 'English Grammar Theme'])->id,
            'future' => Tag::firstOrCreate(['name' => 'Future Auxiliaries Focus'], ['category' => 'English Grammar Theme'])->id,
        ];

        $detailTags = [
            'present_do_usage' => Tag::firstOrCreate(['name' => 'Present Simple Do/Does Choice'], ['category' => 'English Grammar Detail'])->id,
            'present_be_usage' => Tag::firstOrCreate(['name' => 'Present Simple To Be Choice'], ['category' => 'English Grammar Detail'])->id,
            'past_do_usage' => Tag::firstOrCreate(['name' => 'Past Simple Do/Did Choice'], ['category' => 'English Grammar Detail'])->id,
            'past_be_usage' => Tag::firstOrCreate(['name' => 'Past Simple To Be Choice'], ['category' => 'English Grammar Detail'])->id,
            'future_do_usage' => Tag::firstOrCreate(['name' => 'Future Simple Will/Do Choice'], ['category' => 'English Grammar Detail'])->id,
            'future_be_usage' => Tag::firstOrCreate(['name' => 'Future Simple To Be Choice'], ['category' => 'English Grammar Detail'])->id,
        ];

        $patternConfig = [
            'present_do_question' => [
                'section' => 'present',
                'tense' => ['Present Simple'],
                'option_pool' => ['do', 'does', 'am', 'is', 'are'],
                'detail' => 'present_do_usage',
                'hint_short' => 'Present Simple question',
                'verb_hint' => 'to be',
                'tense_label' => 'Present Simple',
                'markers' => 'every day, usually, often',
            ],
            'present_do_negative' => [
                'section' => 'present',
                'tense' => ['Present Simple'],
                'option_pool' => ["don't", "doesn't", 'am not', "isn't", 'are not'],
                'detail' => 'present_do_usage',
                'hint_short' => 'Present Simple negative',
                'verb_hint' => 'to be',
                'tense_label' => 'Present Simple',
                'markers' => 'never, usually, at weekends',
            ],
            'present_be_question' => [
                'section' => 'present',
                'tense' => ['Present Simple'],
                'option_pool' => ['am', 'is', 'are', 'do'],
                'detail' => 'present_be_usage',
                'hint_short' => 'To be question (present)',
                'verb_hint' => 'to be',
                'tense_label' => 'Present Simple',
                'markers' => 'now, today, at the moment',
            ],
            'present_be_negative' => [
                'section' => 'present',
                'tense' => ['Present Simple'],
                'option_pool' => ['am not', "isn't", "aren't", "don't"],
                'detail' => 'present_be_usage',
                'hint_short' => 'To be negative (present)',
                'verb_hint' => 'to be',
                'tense_label' => 'Present Simple',
                'markers' => 'now, today, these days',
            ],
            'past_do_question' => [
                'section' => 'past',
                'tense' => ['Past Simple'],
                'option_pool' => ['did', 'do', 'does', 'was'],
                'detail' => 'past_do_usage',
                'hint_short' => 'Past Simple question',
                'verb_hint' => 'to be',
                'tense_label' => 'Past Simple',
                'markers' => 'yesterday, last night, ago',
            ],
            'past_do_negative' => [
                'section' => 'past',
                'tense' => ['Past Simple'],
                'option_pool' => ["didn't", "don't", "doesn't", "wasn't"],
                'detail' => 'past_do_usage',
                'hint_short' => 'Past Simple negative',
                'verb_hint' => 'to be',
                'tense_label' => 'Past Simple',
                'markers' => 'yesterday, last week, ago',
            ],
            'past_be_question' => [
                'section' => 'past',
                'tense' => ['Past Simple'],
                'option_pool' => ['was', 'were', 'is', 'are'],
                'detail' => 'past_be_usage',
                'hint_short' => 'To be question (past)',
                'verb_hint' => 'to be',
                'tense_label' => 'Past Simple',
                'markers' => 'yesterday, last night, last year',
            ],
            'past_be_negative' => [
                'section' => 'past',
                'tense' => ['Past Simple'],
                'option_pool' => ["wasn't", "weren't", "isn't", "aren't"],
                'detail' => 'past_be_usage',
                'hint_short' => 'To be negative (past)',
                'verb_hint' => 'to be',
                'tense_label' => 'Past Simple',
                'markers' => 'yesterday, last week, ago',
            ],
            'future_do_question' => [
                'section' => 'future',
                'tense' => ['Future Simple'],
                'option_pool' => ['will', 'will be', 'will not', "won't", 'do', 'does', 'did'],
                'detail' => 'future_do_usage',
                'hint_short' => 'Future Simple question',
                'verb_hint' => 'to be',
                'tense_label' => 'Future Simple',
                'markers' => 'tomorrow, next week, soon',
            ],
            'future_do_negative' => [
                'section' => 'future',
                'tense' => ['Future Simple'],
                'option_pool' => ["won't", 'will not', 'will', "won't be", 'do', 'does', 'did'],
                'detail' => 'future_do_usage',
                'hint_short' => 'Future Simple negative',
                'verb_hint' => 'to be',
                'tense_label' => 'Future Simple',
                'markers' => 'tomorrow, next month, later',
            ],
            'future_be_question' => [
                'section' => 'future',
                'tense' => ['Future Simple'],
                'option_pool' => ['will be', 'will', 'will not', "won't", 'is', 'are', 'was'],
                'detail' => 'future_be_usage',
                'hint_short' => 'To be question (future)',
                'verb_hint' => 'to be',
                'tense_label' => 'Future Simple',
                'markers' => 'tomorrow, next week, soon',
            ],
            'future_be_negative' => [
                'section' => 'future',
                'tense' => ['Future Simple'],
                'option_pool' => ["won't be", 'will not be', 'will be', "won't", 'is', 'are', 'was'],
                'detail' => 'future_be_usage',
                'hint_short' => 'To be negative (future)',
                'verb_hint' => 'to be',
                'tense_label' => 'Future Simple',
                'markers' => 'tomorrow, next year, later',
            ],
        ];
 
        $structureTags = [];
        foreach ($patternConfig as $config) {
            $label = $config['hint_short'];
            if (! isset($structureTags[$label])) {
                $structureTags[$label] = Tag::firstOrCreate(
                    ['name' => $label],
                    ['category' => 'English Grammar Structure']
                )->id;
            }
        }

        $entries = [
            // A1
            $this->entry('A1', 'present_do_question', '{a1} you like sweet apples?', 'do', 'you', 'you'),
            $this->entry('A1', 'present_do_question', '{a1} your friend play the guitar?', 'does', 'your friend', 'third_singular'),
            $this->entry('A1', 'present_do_negative', 'We {a1} eat meat on Mondays.', "don't", 'We', 'plural'),
            $this->entry('A1', 'present_do_negative', 'My brother {a1} watch scary films.', "doesn't", 'My brother', 'third_singular'),
            $this->entry('A1', 'present_be_question', '{a1} you ready for school?', 'are', 'you', 'you'),
            $this->entry('A1', 'present_be_question', '{a1} your sister tired after class?', 'is', 'your sister', 'third_singular'),
            $this->entry('A1', 'present_be_negative', 'I {a1} hungry right now.', 'am not', 'I', 'i'),
            $this->entry('A1', 'present_be_negative', 'The books {a1} on the table; they are in the bag.', "aren't", 'The books', 'plural'),
            $this->entry('A1', 'past_do_question', '{a1} you finish your homework yesterday?', 'did', 'you', 'you'),
            $this->entry('A1', 'past_do_question', '{a1} your parents visit grandma yesterday?', 'did', 'your parents', 'plural'),
            $this->entry('A1', 'past_do_negative', 'They {a1} play outside because it rained.', "didn't", 'They', 'plural'),
            $this->entry('A1', 'past_do_negative', 'She {a1} bring her book to class.', "didn't", 'She', 'third_singular'),
            $this->entry('A1', 'past_be_question', '{a1} you at the park on Sunday?', 'were', 'you', 'you'),
            $this->entry('A1', 'past_be_question', '{a1} he happy with the test result?', 'was', 'he', 'third_singular'),
            $this->entry('A1', 'past_be_negative', 'We {a1} late for the movie yesterday.', "weren't", 'We', 'plural'),
            $this->entry('A1', 'past_be_negative', 'I {a1} scared during the storm.', "wasn't", 'I', 'i'),
            $this->entry('A1', 'future_do_question', '{a1} you help me tomorrow?', 'will', 'you', 'you'),
            $this->entry('A1', 'future_do_question', '{a1} they join the meeting next week?', 'will', 'they', 'plural'),
            $this->entry('A1', 'future_do_negative', 'She {a1} forget about the test tomorrow.', "won't", 'She', 'third_singular'),
            $this->entry('A1', 'future_do_negative', 'We {a1} leave before sunset.', "won't", 'We', 'plural'),
            $this->entry('A1', 'future_be_question', 'Who {a1} in charge of snacks tomorrow?', 'will be', 'Who', 'third_singular'),
            $this->entry('A1', 'future_be_question', 'What {a1} the party venue on Saturday?', 'will be', 'the party venue', 'third_singular'),
            $this->entry('A1', 'future_be_negative', 'The classroom {a1} empty tomorrow morning.', "won't be", 'The classroom', 'third_singular'),
            $this->entry('A1', 'future_be_negative', 'Our plans {a1} final until we agree together.', "won't be", 'Our plans', 'plural'),

            // A2
            $this->entry('A2', 'present_do_question', '{a1} you often cook dinner for your family?', 'do', 'you', 'you'),
            $this->entry('A2', 'present_do_question', '{a1} your cousin travel for work?', 'does', 'your cousin', 'third_singular'),
            $this->entry('A2', 'present_do_negative', 'We {a1} skip our language classes.', "don't", 'We', 'plural'),
            $this->entry('A2', 'present_do_negative', 'Maria {a1} drink coffee after 6 p.m.', "doesn't", 'Maria', 'third_singular'),
            $this->entry('A2', 'present_be_question', '{a1} your parents proud of your progress?', 'are', 'your parents', 'plural'),
            $this->entry('A2', 'present_be_question', '{a1} the new cafe busy on weekends?', 'is', 'the new cafe', 'third_singular'),
            $this->entry('A2', 'present_be_negative', 'I {a1} free on Tuesday evenings.', 'am not', 'I', 'i'),
            $this->entry('A2', 'present_be_negative', 'Their ideas {a1} simple to explain.', "aren't", 'Their ideas', 'plural'),
            $this->entry('A2', 'past_do_question', '{a1} you check the email yesterday morning?', 'did', 'you', 'you'),
            $this->entry('A2', 'past_do_question', '{a1} the lecture start on time yesterday?', 'did', 'the lecture', 'third_singular'),
            $this->entry('A2', 'past_do_negative', 'They {a1} finish the group project.', "didn't", 'They', 'plural'),
            $this->entry('A2', 'past_do_negative', 'He {a1} send the parcel last night.', "didn't", 'He', 'third_singular'),
            $this->entry('A2', 'past_be_question', '{a1} you nervous before the exam?', 'were', 'you', 'you'),
            $this->entry('A2', 'past_be_question', '{a1} the hotel comfortable for the trip?', 'was', 'the hotel', 'third_singular'),
            $this->entry('A2', 'past_be_negative', 'We {a1} happy with the final score.', "weren't", 'We', 'plural'),
            $this->entry('A2', 'past_be_negative', 'I {a1} tired after the short flight.', "wasn't", 'I', 'i'),
            $this->entry('A2', 'future_do_question', '{a1} you visit the science fair next month?', 'will', 'you', 'you'),
            $this->entry('A2', 'future_do_question', '{a1} they support the charity event?', 'will', 'they', 'plural'),
            $this->entry('A2', 'future_do_negative', 'She {a1} ignore the manager’s advice.', "won't", 'She', 'third_singular'),
            $this->entry('A2', 'future_do_negative', 'We {a1} cancel the reservation without notice.', "won't", 'We', 'plural'),
            $this->entry('A2', 'future_be_question', 'Who {a1} the speaker at tomorrow’s workshop?', 'will be', 'Who', 'third_singular'),
            $this->entry('A2', 'future_be_question', 'What {a1} the team’s training location next week?', 'will be', 'the team’s training location', 'third_singular'),
            $this->entry('A2', 'future_be_negative', 'The town square {a1} quiet during the festival.', "won't be", 'The town square', 'third_singular'),
            $this->entry('A2', 'future_be_negative', 'Our schedule {a1} flexible after the deadline.', "won't be", 'Our schedule', 'third_singular'),

            // B1
            $this->entry('B1', 'present_do_question', '{a1} you follow the news about climate change?', 'do', 'you', 'you'),
            $this->entry('B1', 'present_do_question', '{a1} your manager approve the new plan?', 'does', 'your manager', 'third_singular'),
            $this->entry('B1', 'present_do_negative', 'We {a1} underestimate the time needed for research.', "don't", 'We', 'plural'),
            $this->entry('B1', 'present_do_negative', 'My colleague {a1} share confidential files.', "doesn't", 'My colleague', 'third_singular'),
            $this->entry('B1', 'present_be_question', '{a1} the students ready for the debate?', 'are', 'the students', 'plural'),
            $this->entry('B1', 'present_be_question', '{a1} your proposal clear to the clients?', 'is', 'your proposal', 'third_singular'),
            $this->entry('B1', 'present_be_negative', 'I {a1} satisfied with the draft yet.', 'am not', 'I', 'i'),
            $this->entry('B1', 'present_be_negative', 'Their reports {a1} consistent with the data.', "aren't", 'Their reports', 'plural'),
            $this->entry('B1', 'past_do_question', '{a1} you record the meeting yesterday?', 'did', 'you', 'you'),
            $this->entry('B1', 'past_do_question', '{a1} the supplier deliver the materials on Monday?', 'did', 'the supplier', 'third_singular'),
            $this->entry('B1', 'past_do_negative', 'They {a1} respond to our inquiry last week.', "didn't", 'They', 'plural'),
            $this->entry('B1', 'past_do_negative', 'She {a1} notice the error until later.', "didn't", 'She', 'third_singular'),
            $this->entry('B1', 'past_be_question', '{a1} you aware of the change in policy?', 'were', 'you', 'you'),
            $this->entry('B1', 'past_be_question', '{a1} the presentation effective for the investors?', 'was', 'the presentation', 'third_singular'),
            $this->entry('B1', 'past_be_negative', 'We {a1} confident about the outcome at first.', "weren't", 'We', 'plural'),
            $this->entry('B1', 'past_be_negative', 'I {a1} available during the afternoon session.', "wasn't", 'I', 'i'),
            $this->entry('B1', 'future_do_question', '{a1} you revise the contract tomorrow?', 'will', 'you', 'you'),
            $this->entry('B1', 'future_do_question', '{a1} they contribute to the community project?', 'will', 'they', 'plural'),
            $this->entry('B1', 'future_do_negative', 'She {a1} postpone the announcement again.', "won't", 'She', 'third_singular'),
            $this->entry('B1', 'future_do_negative', 'We {a1} compromise on safety standards.', "won't", 'We', 'plural'),
            $this->entry('B1', 'future_be_question', 'Who {a1} responsible for coordinating the volunteers?', 'will be', 'Who', 'third_singular'),
            $this->entry('B1', 'future_be_question', 'What {a1} the report’s readiness date for review?', 'will be', 'the report’s readiness date', 'third_singular'),
            $this->entry('B1', 'future_be_negative', 'The lab {a1} open during the maintenance weekend.', "won't be", 'The lab', 'third_singular'),
            $this->entry('B1', 'future_be_negative', 'Our budget {a1} unlimited for this initiative.', "won't be", 'Our budget', 'third_singular'),

            // B2
            $this->entry('B2', 'present_do_question', '{a1} you consider alternative funding sources?', 'do', 'you', 'you'),
            $this->entry('B2', 'present_do_question', '{a1} the committee require further evidence?', 'does', 'the committee', 'third_singular'),
            $this->entry('B2', 'present_do_negative', 'We {a1} rely solely on intuition in these cases.', "don't", 'We', 'plural'),
            $this->entry('B2', 'present_do_negative', 'The analyst {a1} ignore contradictory data.', "doesn't", 'The analyst', 'third_singular'),
            $this->entry('B2', 'present_be_question', '{a1} the stakeholders satisfied with the compromise?', 'are', 'the stakeholders', 'plural'),
            $this->entry('B2', 'present_be_question', '{a1} your timeline realistic given the constraints?', 'is', 'your timeline', 'third_singular'),
            $this->entry('B2', 'present_be_negative', 'I {a1} convinced that the forecast is accurate.', 'am not', 'I', 'i'),
            $this->entry('B2', 'present_be_negative', 'The guidelines {a1} aligned with current legislation.', "aren't", 'The guidelines', 'plural'),
            $this->entry('B2', 'past_do_question', '{a1} you verify the sources before publication?', 'did', 'you', 'you'),
            $this->entry('B2', 'past_do_question', '{a1} the board endorse the merger last quarter?', 'did', 'the board', 'third_singular'),
            $this->entry('B2', 'past_do_negative', 'They {a1} anticipate the sudden shift in demand.', "didn't", 'They', 'plural'),
            $this->entry('B2', 'past_do_negative', 'She {a1} include the appendix in the final draft.', "didn't", 'She', 'third_singular'),
            $this->entry('B2', 'past_be_question', '{a1} you present at the negotiation last Friday?', 'were', 'you', 'you'),
            $this->entry('B2', 'past_be_question', '{a1} the prototype ready for inspection?', 'was', 'the prototype', 'third_singular'),
            $this->entry('B2', 'past_be_negative', 'We {a1} comfortable with the initial agreement.', "weren't", 'We', 'plural'),
            $this->entry('B2', 'past_be_negative', 'I {a1} aware of the hidden fees at the time.', "wasn't", 'I', 'i'),
            $this->entry('B2', 'future_do_question', '{a1} you address the client’s concerns tomorrow?', 'will', 'you', 'you'),
            $this->entry('B2', 'future_do_question', '{a1} they implement the contingency plan if needed?', 'will', 'they', 'plural'),
            $this->entry('B2', 'future_do_negative', 'She {a1} disregard the auditor’s recommendations.', "won't", 'She', 'third_singular'),
            $this->entry('B2', 'future_do_negative', 'We {a1} finalize the deal without legal advice.', "won't", 'We', 'plural'),
            $this->entry('B2', 'future_be_question', 'Who {a1} accountable for monitoring compliance next month?', 'will be', 'Who', 'third_singular'),
            $this->entry('B2', 'future_be_question', 'What {a1} the market response to the new policy?', 'will be', 'the market response', 'third_singular'),
            $this->entry('B2', 'future_be_negative', 'The proposal {a1} viable without additional resources.', "won't be", 'The proposal', 'third_singular'),
            $this->entry('B2', 'future_be_negative', 'Our timeline {a1} flexible once production begins.', "won't be", 'Our timeline', 'third_singular'),

            // C1
            $this->entry('C1', 'present_do_question', '{a1} you factor historical trends into the analysis?', 'do', 'you', 'you'),
            $this->entry('C1', 'present_do_question', '{a1} the director endorse experimental approaches?', 'does', 'the director', 'third_singular'),
            $this->entry('C1', 'present_do_negative', 'We {a1} overlook subtle indicators of risk.', "don't", 'We', 'plural'),
            $this->entry('C1', 'present_do_negative', 'The consultant {a1} compromise professional standards.', "doesn't", 'The consultant', 'third_singular'),
            $this->entry('C1', 'present_be_question', '{a1} the teams aligned on the strategic objectives?', 'are', 'the teams', 'plural'),
            $this->entry('C1', 'present_be_question', '{a1} your argument consistent with the evidence presented?', 'is', 'your argument', 'third_singular'),
            $this->entry('C1', 'present_be_negative', 'I {a1} persuaded by the preliminary findings.', 'am not', 'I', 'i'),
            $this->entry('C1', 'present_be_negative', 'Their narrative {a1} coherent without additional context.', "isn't", 'Their narrative', 'third_singular'),
            $this->entry('C1', 'past_do_question', '{a1} you document the anomalies during the audit?', 'did', 'you', 'you'),
            $this->entry('C1', 'past_do_question', '{a1} the panel consider dissenting opinions last session?', 'did', 'the panel', 'third_singular'),
            $this->entry('C1', 'past_do_negative', 'They {a1} foresee the cascading failures in the system.', "didn't", 'They', 'plural'),
            $this->entry('C1', 'past_do_negative', 'She {a1} relay the urgency of the situation.', "didn't", 'She', 'third_singular'),
            $this->entry('C1', 'past_be_question', '{a1} you aware of the diplomatic implications at the time?', 'were', 'you', 'you'),
            $this->entry('C1', 'past_be_question', '{a1} the briefing sufficient for the delegates?', 'was', 'the briefing', 'third_singular'),
            $this->entry('C1', 'past_be_negative', 'We {a1} accountable for the oversight during that phase.', "weren't", 'We', 'plural'),
            $this->entry('C1', 'past_be_negative', 'I {a1} comfortable signing the memorandum then.', "wasn't", 'I', 'i'),
            $this->entry('C1', 'future_do_question', '{a1} you address the board’s reservations next quarter?', 'will', 'you', 'you'),
            $this->entry('C1', 'future_do_question', '{a1} they pursue litigation if negotiations fail?', 'will', 'they', 'plural'),
            $this->entry('C1', 'future_do_negative', 'She {a1} authorize expenditures without transparency.', "won't", 'She', 'third_singular'),
            $this->entry('C1', 'future_do_negative', 'We {a1} concede core principles during the talks.', "won't", 'We', 'plural'),
            $this->entry('C1', 'future_be_question', 'Who {a1} responsible for framing the policy response?', 'will be', 'Who', 'third_singular'),
            $this->entry('C1', 'future_be_question', 'What {a1} the venue for the decisive meeting?', 'will be', 'the decisive meeting venue', 'third_singular'),
            $this->entry('C1', 'future_be_negative', 'The framework {a1} sustainable without regulatory reform.', "won't be", 'The framework', 'third_singular'),
            $this->entry('C1', 'future_be_negative', 'Our leverage {a1} limitless once the contract expires.', "won't be", 'Our leverage', 'third_singular'),

            // C2
            $this->entry('C2', 'present_do_question', '{a1} you interrogate the underlying assumptions in your model?', 'do', 'you', 'you'),
            $this->entry('C2', 'present_do_question', '{a1} the commission sanction such discretionary powers?', 'does', 'the commission', 'third_singular'),
            $this->entry('C2', 'present_do_negative', 'We {a1} disregard peer-reviewed counterarguments.', "don't", 'We', 'plural'),
            $this->entry('C2', 'present_do_negative', 'The scholar {a1} dilute the nuance of her thesis.', "doesn't", 'The scholar', 'third_singular'),
            $this->entry('C2', 'present_be_question', '{a1} the research teams coordinated across jurisdictions?', 'are', 'the research teams', 'plural'),
            $this->entry('C2', 'present_be_question', '{a1} your hypothesis compatible with existing jurisprudence?', 'is', 'your hypothesis', 'third_singular'),
            $this->entry('C2', 'present_be_negative', 'I {a1} convinced by the ostensibly flawless methodology.', 'am not', 'I', 'i'),
            $this->entry('C2', 'present_be_negative', 'Their rhetoric {a1} free from ideological bias.', "isn't", 'Their rhetoric', 'third_singular'),
            $this->entry('C2', 'past_do_question', '{a1} you interrogate the witnesses during the inquiry?', 'did', 'you', 'you'),
            $this->entry('C2', 'past_do_question', '{a1} the senate ratify the protocol in the last session?', 'did', 'the senate', 'third_singular'),
            $this->entry('C2', 'past_do_negative', 'They {a1} anticipate the jurisprudential backlash.', "didn't", 'They', 'plural'),
            $this->entry('C2', 'past_do_negative', 'She {a1} disclose the addendum to the committee.', "didn't", 'She', 'third_singular'),
            $this->entry('C2', 'past_be_question', '{a1} you cognizant of the ethical ramifications then?', 'were', 'you', 'you'),
            $this->entry('C2', 'past_be_question', '{a1} the communiqué satisfactory to all delegates?', 'was', 'the communiqué', 'third_singular'),
            $this->entry('C2', 'past_be_negative', 'We {a1} culpable under the previous statute.', "weren't", 'We', 'plural'),
            $this->entry('C2', 'past_be_negative', 'I {a1} amenable to renegotiation at that juncture.', "wasn't", 'I', 'i'),
            $this->entry('C2', 'future_do_question', '{a1} you interrogate the algorithm’s opacity at the hearing?', 'will', 'you', 'you'),
            $this->entry('C2', 'future_do_question', '{a1} they invoke emergency powers if dissent escalates?', 'will', 'they', 'plural'),
            $this->entry('C2', 'future_do_negative', 'She {a1} compromise the evidentiary chain again.', "won't", 'She', 'third_singular'),
            $this->entry('C2', 'future_do_negative', 'We {a1} relinquish oversight despite political pressure.', "won't", 'We', 'plural'),
            $this->entry('C2', 'future_be_question', 'Who {a1} entrusted with drafting the supranational charter?', 'will be', 'Who', 'third_singular'),
            $this->entry('C2', 'future_be_question', 'What {a1} the interpretation of the precedent after the ruling?', 'will be', 'the interpretation', 'third_singular'),
            $this->entry('C2', 'future_be_negative', 'The mandate {a1} enforceable without multilateral consent.', "won't be", 'The mandate', 'third_singular'),
            $this->entry('C2', 'future_be_negative', 'Our position {a1} indefensible once the clause sunsets.', "won't be", 'Our position', 'third_singular'),
        ];

        $rawQuestions = [];
        foreach ($entries as $entry) {
            $config = $patternConfig[$entry['pattern']];
            $answer = $entry['answer'];
            $example = $this->formatExample($entry['question'], $answer);

            $options = $this->buildOptions($config['option_pool'], $answer);

            $rawQuestions[] = [
                'section' => $config['section'],
                'detail' => $config['detail'],
                'question' => $entry['question'],
                'verb_hint' => ['a1' => '(' . $config['verb_hint'] . ')'],
                'options' => $options,
                'answers' => ['a1' => $answer],
                'explanations' => $this->buildExplanations($entry['pattern'], $entry, $options, $answer, $example, $config['tense_label']),
                'hints' => ['a1' => $this->buildHint($entry['pattern'], $entry, $answer, $example, $config)],
                'tense' => $config['tense'],
                'level' => $entry['level'],
                'structure_tag_id' => $structureTags[$config['hint_short']] ?? null,
            ];
        }

        $sections = [
            'present' => [],
            'past' => [],
            'future' => [],
        ];

        foreach ($rawQuestions as $question) {
            $sections[$question['section']][] = $question;
        }

        $levelDifficulty = [
            'A1' => 1,
            'A2' => 2,
            'B1' => 3,
            'B2' => 4,
            'C1' => 5,
            'C2' => 5,
        ];

        $tenseTags = [];
        foreach ($sections as $sectionQuestions) {
            foreach ($sectionQuestions as $question) {
                foreach ($question['tense'] as $tenseName) {
                    if (! isset($tenseTags[$tenseName])) {
                        $tenseTags[$tenseName] = Tag::firstOrCreate(['name' => $tenseName], ['category' => 'English Grammar Tense'])->id;
                    }
                }
            }
        }

        $items = [];
        $meta = [];

        foreach ($sections as $sectionKey => $sectionQuestions) {
            foreach ($sectionQuestions as $index => $question) {
                $uuid = $this->generateQuestionUuid($sectionKey, $index, $question['question']);

                $answers = [];
                $optionMarkerMap = [];
                $firstMarker = array_key_first($question['answers']);

                if ($firstMarker !== null) {
                    foreach ($question['options'] as $option) {
                        $optionMarkerMap[$option] = $firstMarker;
                    }
                }

                foreach ($question['answers'] as $marker => $answer) {
                    $answers[] = [
                        'marker' => $marker,
                        'answer' => $answer,
                        'verb_hint' => $this->normalizeHint($question['verb_hint'][$marker] ?? null),
                    ];
                    $optionMarkerMap[$answer] = $marker;
                }

                $tagIds = [$themeTags[$sectionKey]];
                $detailKey = $question['detail'] ?? null;
                if ($detailKey !== null && isset($detailTags[$detailKey])) {
                    $tagIds[] = $detailTags[$detailKey];
                }

                if (isset($question['structure_tag_id'])) {
                    $tagIds[] = $question['structure_tag_id'];
                }

                foreach ($question['tense'] as $tenseName) {
                    $tagIds[] = $tenseTags[$tenseName];
                }

                $items[] = [
                    'uuid' => $uuid,
                    'question' => str_replace(['____', '—'], ['{a1}', '—'], $question['question']),
                    'category_id' => $categoryId,
                    'difficulty' => $levelDifficulty[$question['level']] ?? 3,
                    'source_id' => $sectionSources[$sectionKey],
                    'flag' => 2,
                    'level' => $question['level'],
                    'tag_ids' => array_values(array_unique($tagIds)),
                    'answers' => $answers,
                    'options' => $question['options'],
                    'variants' => [],
                ];

                $meta[] = [
                    'uuid' => $uuid,
                    'answers' => $question['answers'],
                    'option_markers' => $optionMarkerMap,
                    'hints' => $question['hints'],
                    'explanations' => $question['explanations'],
                ];
            }
        }

        $this->seedQuestionData($items, $meta);
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
        $answerTitle = $this->titleCase($answer);

        return match ($pattern) {
            'present_do_question' => "Час: {$config['tense_label']}.  \nФормула: **{$answerTitle} + {$subject} + V1...?**  \nПояснення: У запитанні Present Simple допоміжне do або does ставимо перед підметом, а смислове дієслово залишаємо у базовій формі.  \nПриклад: *{$example}*  \nМаркери: {$markers}.",
            'present_do_negative' => "Час: {$config['tense_label']}.  \nФормула: **{$subject} + {$answer} + V1**.  \nПояснення: Заперечення Present Simple будуємо як підмет + do або does з not, після чого головне дієслово стоїть у формі V1.  \nПриклад: *{$example}*  \nМаркери: {$markers}.",
            'present_be_question' => "Час: {$config['tense_label']}.  \nФормула: **{$answerTitle} + {$subject} + прикметник/місце?**  \nПояснення: Питання з to be починаються з відповідної форми am/is/are, далі йде підмет і продовження думки.  \nПриклад: *{$example}*  \nМаркери: {$markers}.",
            'present_be_negative' => "Час: {$config['tense_label']}.  \nФормула: **{$subject} + {$answer} + прикметник/місце**.  \nПояснення: У запереченні з to be ставимо підмет, правильну форму am/is/are з not, а потім опис чи місце.  \nПриклад: *{$example}*  \nМаркери: {$markers}.",
            'past_do_question' => "Час: {$config['tense_label']}.  \nФормула: **Did + {$subject} + V1...?**  \nПояснення: У Past Simple запитання формуємо через did перед підметом, а основне дієслово залишаємо у базовій формі.  \nПриклад: *{$example}*  \nМаркери: {$markers}.",
            'past_do_negative' => "Час: {$config['tense_label']}.  \nФормула: **{$subject} + didn't + V1**.  \nПояснення: Заперечення Past Simple складається з підмета, did not та головного дієслова без закінчень.  \nПриклад: *{$example}*  \nМаркери: {$markers}.",
            'past_be_question' => "Час: {$config['tense_label']}.  \nФормула: **Was/Were + {$subject} + прикметник/місце?**  \nПояснення: Питання з to be в минулому починаємо з was чи were, після чого ставимо підмет і інформацію про стан або місце.  \nПриклад: *{$example}*  \nМаркери: {$markers}.",
            'past_be_negative' => "Час: {$config['tense_label']}.  \nФормула: **{$subject} + wasn't/weren't + прикметник/місце**.  \nПояснення: Заперечення з to be в Past Simple будуємо як підмет + was/were + not і подальший опис.  \nПриклад: *{$example}*  \nМаркери: {$markers}.",
            'future_do_question' => "Час: {$config['tense_label']}.  \nФормула: **Will + {$subject} + V1...?**  \nПояснення: Питання про майбутні дії формуємо через will перед підметом, а смислове дієслово залишаємо у базовій формі.  \nПриклад: *{$example}*  \nМаркери: {$markers}.",
            'future_do_negative' => "Час: {$config['tense_label']}.  \nФормула: **{$subject} + won't + V1**.  \nПояснення: Заперечення Future Simple має структуру підмет + will not та дієслово в інфінітиві без to.  \nПриклад: *{$example}*  \nМаркери: {$markers}.",
            'future_be_question' => "Час: {$config['tense_label']}.  \nФормула: **Will + {$subject} + be + прикметник/місце?**  \nПояснення: Щоб запитати про стан у майбутньому, ставимо will, потім підмет і be, а далі додаємо характеристику чи місце.  \nПриклад: *{$example}*  \nМаркери: {$markers}.",
            'future_be_negative' => "Час: {$config['tense_label']}.  \nФормула: **{$subject} + won't be + прикметник/місце**.  \nПояснення: Заперечення майбутнього стану будуємо як підмет + will not be і додаємо опис ситуації чи місця.  \nПриклад: *{$example}*  \nМаркери: {$markers}.",
            default => '',
        };
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
        return match ($pattern) {
            'present_do_question' => "✅ «{$answer}» правильно, бо для {$subjectPhrase} у {$tenseLabel} запитанні потрібен допоміжний «{$answer}».  \nПриклад: *{$example}*",
            'present_do_negative' => "✅ «{$answer}» правильно, бо для {$subjectPhrase} у {$tenseLabel} запереченні вживаємо саме цю форму з not.  \nПриклад: *{$example}*",
            'present_be_question' => "✅ «{$answer}» правильно, бо дієслово to be у {$tenseLabel} з {$subjectPhrase} має саме таку форму.  \nПриклад: *{$example}*",
            'present_be_negative' => "✅ «{$answer}» правильно, бо для {$subjectPhrase} у {$tenseLabel} запереченні з to be потрібна ця форма.  \nПриклад: *{$example}*",
            'past_do_question' => "✅ «{$answer}» правильно, бо питання у {$tenseLabel} завжди використовує допоміжне «did».  \nПриклад: *{$example}*",
            'past_do_negative' => "✅ «{$answer}» правильно, бо заперечення у {$tenseLabel} формуємо через «did not».  \nПриклад: *{$example}*",
            'past_be_question' => "✅ «{$answer}» правильно, бо to be у {$tenseLabel} для {$subjectPhrase} має цю форму.  \nПриклад: *{$example}*",
            'past_be_negative' => "✅ «{$answer}» правильно, бо у {$tenseLabel} заперечення з to be для {$subjectPhrase} вимагає саме цієї форми.  \nПриклад: *{$example}*",
            'future_do_question' => "✅ «{$answer}» правильно, бо питання про майбутнє у {$tenseLabel} будуємо з «will».  \nПриклад: *{$example}*",
            'future_do_negative' => "✅ «{$answer}» правильно, бо заперечення у {$tenseLabel} утворюємо як «will not».  \nПриклад: *{$example}*",
            'future_be_question' => "✅ «{$answer}» правильно, бо стан у майбутньому передається конструкцією «will + be».  \nПриклад: *{$example}*",
            'future_be_negative' => "✅ «{$answer}» правильно, бо заперечення майбутнього стану формуємо через «will not be».  \nПриклад: *{$example}*",
            default => "✅" ,
        };
    }

    private function buildWrongExplanation(string $pattern, string $subjectPhrase, string $option, string $answer, string $tenseLabel): string
    {
        $examples = [
            'do' => '*Do they study English?*',
            'do not' => '*They do not eat meat.*',
            'does' => '*Does she study English?*',
            'does not' => '*She does not eat meat.*',
            'am' => '*Am I late?*',
            'is' => '*Is he ready?*',
            'are' => '*Are they ready?*',
            "don't" => "*They don't eat meat.*",
            "doesn't" => "*She doesn't eat meat.*",
            "I'm" => "*I'm ready.*",
            "I'm not" => "*I'm not ready.*",
            'am not' => '*I am not tired.*',
            'is not' => '*He is not ready.*',
            "isn't" => "*He isn't ready.*",
            'are not' => '*They are not ready.*',
            "aren't" => "*They aren't ready.*",
            'did' => '*Did you call her?*',
            'did not' => '*They did not call her.*',
            "didn't" => "*She didn't call yesterday.*",
            'was' => '*Was he at home?*',
            'was not' => '*He was not late.*',
            'were' => '*Were they at home?*',
            'were not' => '*They were not late.*',
            "wasn't" => "*He wasn't late.*",
            "weren't" => "*They weren't late.*",
            'will' => '*Will you join us tomorrow?*',
            'will not' => '*She will not join us tomorrow.*',
            'will be' => '*Will you be ready tomorrow?*',
            'will not be' => '*She will not be ready tomorrow.*',
            "won't" => "*She won't come tomorrow.*",
            "won't be" => "*The room won't be ready tomorrow.*",
        ];

        $example = $examples[$option] ?? '*—*';

        return match ($pattern) {
            'present_do_question' => $this->explainPresentDoQuestion($subjectPhrase, $option, $example, $answer),
            'present_do_negative' => $this->explainPresentDoNegative($subjectPhrase, $option, $example, $answer),
            'present_be_question' => $this->explainPresentBeQuestion($subjectPhrase, $option, $example, $answer),
            'present_be_negative' => $this->explainPresentBeNegative($subjectPhrase, $option, $example, $answer),
            'past_do_question' => $this->explainPastDoQuestion($option, $example),
            'past_do_negative' => $this->explainPastDoNegative($option, $example),
            'past_be_question' => $this->explainPastBeQuestion($subjectPhrase, $option, $example, $answer),
            'past_be_negative' => $this->explainPastBeNegative($subjectPhrase, $option, $example, $answer),
            'future_do_question' => $this->explainFutureDoQuestion($option, $example),
            'future_do_negative' => $this->explainFutureDoNegative($option, $example),
            'future_be_question' => $this->explainFutureBeQuestion($option, $example),
            'future_be_negative' => $this->explainFutureBeNegative($option, $example),
            default => "❌ Неправильний варіант.  \nПриклад: {$example}",
        };
    }

    private function explainPresentDoQuestion(string $subjectPhrase, string $option, string $example, string $answer): string
    {
        if (str_contains($option, 'not') || str_contains($option, "n't")) {
            return "❌ Запитання у Present Simple не містить «not», тому слід вибрати допоміжне дієслово без заперечення.  \nПриклад: {$example}";
        }

        if ($option === 'does') {
            return "❌ «does» вживається з третьою особою однини, а {$subjectPhrase} належить до іншої групи.  \nПриклад: {$example}";
        }

        if ($option === 'do' && $answer === 'does') {
            return "❌ «do» уживається з «I/you/we/they», але {$subjectPhrase} потребує форми «does».  \nПриклад: {$example}";
        }

        if ($option === 'am') {
            return "❌ «am» — форма дієслова to be для «I», а тут потрібен допоміжний «do/does».  \nПриклад: {$example}";
        }

        if ($option === 'is') {
            return "❌ «is» — форма to be для однини, але питання про дію вимагає «do/does».  \nПриклад: {$example}";
        }

        return "❌ Неправильний вибір.  \nПриклад: {$example}";
    }

    private function explainPresentDoNegative(string $subjectPhrase, string $option, string $example, string $answer): string
    {
        if ($option === 'do not' && $answer === "don't") {
            return "❌ «do not» — повна форма, але у вправі ми відпрацьовуємо скорочене «don't».  \nПриклад: {$example}";
        }

        if ($option === 'does not' && $answer === "doesn't") {
            return "❌ «does not» — повна форма, проте речення потребує скорочення «doesn't».  \nПриклад: {$example}";
        }

        if ($option === 'is not' || $option === 'are not') {
            return "❌ Форми з to be («{$option}») не використовуються з основними дієсловами; потрібне «do/does» з not.  \nПриклад: {$example}";
        }

        if ($option === "don't" && $answer === 'do not') {
            return "❌ «don't» — скорочення, а підказка просить повну форму «do not».  \nПриклад: {$example}";
        }

        if ($option === "doesn't" && $answer === 'does not') {
            return "❌ «doesn't» — скорочення, тоді як очікується повна форма «does not».  \nПриклад: {$example}";
        }

        if ($option === "doesn't" && $answer === "don't") {
            return "❌ «doesn't» підходить лише для третьої особи однини, а {$subjectPhrase} належить до іншої групи.  \nПриклад: {$example}";
        }

        if ($option === "don't" && $answer === "doesn't") {
            return "❌ «don't» використовується з «I/you/we/they», а {$subjectPhrase} потребує «doesn't».  \nПриклад: {$example}";
        }

        if ($option === 'am not') {
            return "❌ «am not» — форма to be, а тут заперечення з основним дієсловом потребує «do/does».  \nПриклад: {$example}";
        }

        if ($option === "isn't") {
            return "❌ «isn't» — форма дієслова to be, а не допоміжного do.  \nПриклад: {$example}";
        }

        return "❌ Неправильний вибір.  \nПриклад: {$example}";
    }

    private function explainPresentBeQuestion(string $subjectPhrase, string $option, string $example, string $answer): string
    {
        if (str_contains($option, 'not') || str_contains($option, "n't")) {
            return "❌ Питальні речення з to be не містять заперечення, тому потрібно обрати форму без «not».  \nПриклад: {$example}";
        }

        if ($option === "I'm") {
            return "❌ «I'm» використовується у ствердженні, а в питанні форма скорочується до «am» перед підметом.  \nПриклад: {$example}";
        }

        if ($option === 'do') {
            return "❌ «do» використовується з основними дієсловами, а тут потрібна форма to be.  \nПриклад: {$example}";
        }

        if ($option === 'am' && $answer !== 'am') {
            return "❌ «am» уживається лише з «I», але {$subjectPhrase} потребує іншої форми.  \nПриклад: {$example}";
        }

        if ($option === 'is' && $answer === 'are') {
            return "❌ «is» підходить для однини, а {$subjectPhrase} вимагає «are».  \nПриклад: {$example}";
        }

        if ($option === 'are' && $answer === 'is') {
            return "❌ «are» вживається з множиною, але {$subjectPhrase} є одниною, отже потрібне «is».  \nПриклад: {$example}";
        }

        return "❌ Неправильний вибір.  \nПриклад: {$example}";
    }

    private function explainPresentBeNegative(string $subjectPhrase, string $option, string $example, string $answer): string
    {
        if ($option === "don't") {
            return "❌ «don't» використовується з основними дієсловами, а не з to be.  \nПриклад: {$example}";
        }

        if ($option === 'is not' && $answer === "isn't") {
            return "❌ «is not» — повна форма, але вправа просить скорочення «isn't».  \nПриклад: {$example}";
        }

        if ($option === 'are not' && $answer === "aren't") {
            return "❌ «are not» — повна форма, проте потрібно скорочення «aren't».  \nПриклад: {$example}";
        }

        if ($option === "I'm not" && $answer === 'am not') {
            return "❌ «I'm not» — скорочена форма, а в пропуск вписуємо повну «am not».  \nПриклад: {$example}";
        }

        if ($option === "I'm" && $answer === 'am not') {
            return "❌ «I'm» — ствердження, тоді як речення потребує заперечення «am not».  \nПриклад: {$example}";
        }

        if ($option === 'am not' && $answer !== 'am not') {
            return "❌ «am not» уживається лише з «I», а {$subjectPhrase} належить до іншої групи.  \nПриклад: {$example}";
        }

        if ($option === "isn't" && $answer === 'am not') {
            return "❌ «isn't» стосується третьої особи однини, тоді як для займенника «I» потрібне «am not».  \nПриклад: {$example}";
        }

        if ($option === "aren't" && $answer === 'am not') {
            return "❌ «aren't» використовується з множиною, а для «I» потрібна форма «am not».  \nПриклад: {$example}";
        }

        if ($option === "isn't" && $answer === "aren't") {
            return "❌ «isn't» підходить для однини, а {$subjectPhrase} потребує множини «aren't».  \nПриклад: {$example}";
        }

        if ($option === "aren't" && $answer === "isn't") {
            return "❌ «aren't» вживається з множиною, а {$subjectPhrase} є одниною, тому потрібно «isn't».  \nПриклад: {$example}";
        }

        return "❌ Неправильний вибір.  \nПриклад: {$example}";
    }

    private function explainPastDoQuestion(string $option, string $example): string
    {
        if (str_contains($option, 'not') || str_contains($option, "n't")) {
            return "❌ Питання у Past Simple не містить «not», потрібно використати допоміжне «did» без заперечення.  \nПриклад: {$example}";
        }

        if ($option === 'do' || $option === 'does') {
            return "❌ «{$option}» виражає теперішній час, а питання в минулому потребує «did».  \nПриклад: {$example}";
        }

        if ($option === 'was') {
            return "❌ «was» — це форма to be, а не допоміжне «did» для питань з основним дієсловом.  \nПриклад: {$example}";
        }

        return "❌ Неправильний вибір.  \nПриклад: {$example}";
    }

    private function explainPastDoNegative(string $option, string $example): string
    {
        if ($option === 'did not' && $example !== '') {
            return "❌ «did not» — повна форма, а ми тренуємо скорочення «didn't» у Past Simple.  \nПриклад: {$example}";
        }

        if ($option === 'do not' || $option === 'does not') {
            return "❌ «{$option}» передає теперішній час, а Past Simple заперечення будуємо з «didn't».  \nПриклад: {$example}";
        }

        if ($option === "don't" || $option === "doesn't") {
            return "❌ «{$option}» — це теперішній час, а в минулому потрібно «didn't».  \nПриклад: {$example}";
        }

        if ($option === "wasn't") {
            return "❌ «wasn't» — форма to be, а для дії потрібне «didn't».  \nПриклад: {$example}";
        }

        return "❌ Неправильний вибір.  \nПриклад: {$example}";
    }

    private function explainPastBeQuestion(string $subjectPhrase, string $option, string $example, string $answer): string
    {
        if ($option === 'is' || $option === 'are') {
            return "❌ «{$option}» — теперішній час, а питання стосується минулого, тому потрібне «{$answer}».  \nПриклад: {$example}";
        }

        if ($option === 'was' && $answer === 'were') {
            return "❌ «was» вживається з одниною, а {$subjectPhrase} потребує «were».  \nПриклад: {$example}";
        }

        if ($option === 'were' && $answer === 'was') {
            return "❌ «were» призначене для множини, а {$subjectPhrase} є одниною, тому потрібне «was».  \nПриклад: {$example}";
        }

        return "❌ Неправильний вибір.  \nПриклад: {$example}";
    }

    private function explainPastBeNegative(string $subjectPhrase, string $option, string $example, string $answer): string
    {
        if ($option === "isn't" || $option === "aren't") {
            return "❌ «{$option}» — теперішній час, а нам потрібне минуле «{$answer}».  \nПриклад: {$example}";
        }

        if ($option === 'was not' && $answer === "wasn't") {
            return "❌ «was not» — повна форма, але речення очікує скорочення «wasn't».  \nПриклад: {$example}";
        }

        if ($option === 'were not' && $answer === "weren't") {
            return "❌ «were not» — повна форма, проте вправа тренує скорочене «weren't».  \nПриклад: {$example}";
        }

        if ($option === "wasn't" && $answer === "weren't") {
            return "❌ «wasn't» підходить для однини, а {$subjectPhrase} потребує «weren't».  \nПриклад: {$example}";
        }

        if ($option === "weren't" && $answer === "wasn't") {
            return "❌ «weren't» вживається для множини, а {$subjectPhrase} є одниною, отже потрібне «wasn't».  \nПриклад: {$example}";
        }

        return "❌ Неправильний вибір.  \nПриклад: {$example}";
    }

    private function explainFutureDoQuestion(string $option, string $example): string
    {
        if (in_array($option, ['will not', 'will not be', "won't", "won't be"], true)) {
            return "❌ Це заперечна форма, а питальне речення майбутнього часу потребує просто «will».  \nПриклад: {$example}";
        }

        if ($option === 'will be') {
            return "❌ «will be» містить зайве «be»: у питанні спочатку використовуємо «will», а далі головне дієслово.  \nПриклад: {$example}";
        }

        if ($option === 'do' || $option === 'does') {
            return "❌ «{$option}» передає теперішній час, а майбутнє питання потребує «will».  \nПриклад: {$example}";
        }

        if ($option === 'did') {
            return "❌ «did» — минулий час, а нам потрібне майбутнє «will».  \nПриклад: {$example}";
        }

        return "❌ Неправильний вибір.  \nПриклад: {$example}";
    }

    private function explainFutureDoNegative(string $option, string $example): string
    {
        if ($option === 'will not') {
            return "❌ «will not» — повна форма; вправа тренує скорочене майбутнє заперечення «won't».  \nПриклад: {$example}";
        }

        if ($option === 'will') {
            return "❌ «will» без not не створює заперечення, тому необхідно використати «won't».  \nПриклад: {$example}";
        }

        if ($option === 'will be' || $option === 'will not be' || $option === "won't be") {
            return "❌ Ця відповідь належить до конструкцій з «be», а вправа перевіряє просту форму «won't».  \nПриклад: {$example}";
        }

        if ($option === "don't" || $option === "doesn't" || $option === "didn't") {
            return "❌ «{$option}» описує інший час, а для майбутнього заперечення потрібне «won't».  \nПриклад: {$example}";
        }

        return "❌ Неправильний вибір.  \nПриклад: {$example}";
    }

    private function explainFutureBeQuestion(string $option, string $example): string
    {
        if (in_array($option, ['will not', 'will not be', "won't", "won't be"], true)) {
            return "❌ Це заперечна форма, а в питанні про стан потрібне ствердження «will be».  \nПриклад: {$example}";
        }

        if ($option === 'will') {
            return "❌ Після «will» у питанні про стан обов'язково додаємо «be».  \nПриклад: {$example}";
        }

        if ($option === 'is' || $option === 'are') {
            return "❌ «{$option}» — теперішній час, а майбутній стан вимагає допоміжного «will» перед «be».  \nПриклад: {$example}";
        }

        if ($option === 'was') {
            return "❌ «was» — минулий час, а не майбутнє.  \nПриклад: {$example}";
        }

        return "❌ Неправильний вибір.  \nПриклад: {$example}";
    }

    private function explainFutureBeNegative(string $option, string $example): string
    {
        if ($option === 'will not be') {
            return "❌ «will not be» — повна форма; у вправі закріплюємо скорочення «won't be».  \nПриклад: {$example}";
        }

        if ($option === 'will be') {
            return "❌ «will be» не містить заперечення, тож потрібне «won't be».  \nПриклад: {$example}";
        }

        if ($option === 'will not') {
            return "❌ «will not» без «be» не описує стан, а завдання тренує конструкцію «won't be».  \nПриклад: {$example}";
        }

        if ($option === 'will') {
            return "❌ «will» без not і be не формує заперечення стану.  \nПриклад: {$example}";
        }

        if ($option === "won't") {
            return "❌ «won't» без «be» не описує майбутній стан; потрібне повне «won't be».  \nПриклад: {$example}";
        }

        if ($option === "isn't" || $option === "aren't" || $option === "wasn't") {
            return "❌ «{$option}» показує теперішній або минулий час, тоді як нам потрібно майбутнє «won't be».  \nПриклад: {$example}";
        }

        return "❌ Неправильний вибір.  \nПриклад: {$example}";
    }

    private function subjectPhrase(string $category, string $subject): string
    {
        return match ($category) {
            'i' => 'займенника «I»',
            'you' => 'займенника «you»',
            'third_singular' => 'третьої особи однини (наприклад, «' . $subject . '»)',
            'plural' => 'множини (наприклад, «' . $subject . '»)',
            default => 'підмета «' . $subject . '»',
        };
    }

}
