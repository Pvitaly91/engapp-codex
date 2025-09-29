<?php

namespace Database\Seeders\Ai;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Source;
use App\Models\Tag;
use App\Services\QuestionSeedingService;
use Database\Seeders\QuestionSeeder;

class DoDoesIsAreFormsComprehensiveAiSeeder extends QuestionSeeder
{
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
                'options' => ['do', 'does', 'am', 'is'],
                'detail' => 'present_do_usage',
                'hint_short' => 'Present Simple question',
                'verb_hint' => 'to be',
                'tense_label' => 'Present Simple',
                'markers' => 'every day, usually, often',
            ],
            'present_do_negative' => [
                'section' => 'present',
                'tense' => ['Present Simple'],
                'options' => ["don't", "doesn't", 'am not', "isn't"],
                'detail' => 'present_do_usage',
                'hint_short' => 'Present Simple negative',
                'verb_hint' => 'to be',
                'tense_label' => 'Present Simple',
                'markers' => 'never, usually, at weekends',
            ],
            'present_be_question' => [
                'section' => 'present',
                'tense' => ['Present Simple'],
                'options' => ['am', 'is', 'are', 'do'],
                'detail' => 'present_be_usage',
                'hint_short' => 'To be question (present)',
                'verb_hint' => 'to be',
                'tense_label' => 'Present Simple',
                'markers' => 'now, today, at the moment',
            ],
            'present_be_negative' => [
                'section' => 'present',
                'tense' => ['Present Simple'],
                'options' => ['am not', "isn't", "aren't", "don't"],
                'detail' => 'present_be_usage',
                'hint_short' => 'To be negative (present)',
                'verb_hint' => 'to be',
                'tense_label' => 'Present Simple',
                'markers' => 'now, today, these days',
            ],
            'past_do_question' => [
                'section' => 'past',
                'tense' => ['Past Simple'],
                'options' => ['did', 'do', 'does', 'was'],
                'detail' => 'past_do_usage',
                'hint_short' => 'Past Simple question',
                'verb_hint' => 'to be',
                'tense_label' => 'Past Simple',
                'markers' => 'yesterday, last night, ago',
            ],
            'past_do_negative' => [
                'section' => 'past',
                'tense' => ['Past Simple'],
                'options' => ["didn't", "don't", "doesn't", "wasn't"],
                'detail' => 'past_do_usage',
                'hint_short' => 'Past Simple negative',
                'verb_hint' => 'to be',
                'tense_label' => 'Past Simple',
                'markers' => 'yesterday, last week, ago',
            ],
            'past_be_question' => [
                'section' => 'past',
                'tense' => ['Past Simple'],
                'options' => ['was', 'were', 'is', 'are'],
                'detail' => 'past_be_usage',
                'hint_short' => 'To be question (past)',
                'verb_hint' => 'to be',
                'tense_label' => 'Past Simple',
                'markers' => 'yesterday, last night, last year',
            ],
            'past_be_negative' => [
                'section' => 'past',
                'tense' => ['Past Simple'],
                'options' => ["wasn't", "weren't", "isn't", "aren't"],
                'detail' => 'past_be_usage',
                'hint_short' => 'To be negative (past)',
                'verb_hint' => 'to be',
                'tense_label' => 'Past Simple',
                'markers' => 'yesterday, last week, ago',
            ],
            'future_do_question' => [
                'section' => 'future',
                'tense' => ['Future Simple'],
                'options' => ['will', 'do', 'does', 'did'],
                'detail' => 'future_do_usage',
                'hint_short' => 'Future Simple question',
                'verb_hint' => 'to be',
                'tense_label' => 'Future Simple',
                'markers' => 'tomorrow, next week, soon',
            ],
            'future_do_negative' => [
                'section' => 'future',
                'tense' => ['Future Simple'],
                'options' => ["won't", "don't", "doesn't", "didn't"],
                'detail' => 'future_do_usage',
                'hint_short' => 'Future Simple negative',
                'verb_hint' => 'to be',
                'tense_label' => 'Future Simple',
                'markers' => 'tomorrow, next month, later',
            ],
            'future_be_question' => [
                'section' => 'future',
                'tense' => ['Future Simple'],
                'options' => ['will', 'is', 'are', 'was'],
                'detail' => 'future_be_usage',
                'hint_short' => 'To be question (future)',
                'verb_hint' => 'to be',
                'tense_label' => 'Future Simple',
                'markers' => 'tomorrow, next week, soon',
            ],
            'future_be_negative' => [
                'section' => 'future',
                'tense' => ['Future Simple'],
                'options' => ["won't be", "isn't", "aren't", "wasn't"],
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
            $this->entry('A1', 'future_be_question', 'Who {a1} be in charge of snacks tomorrow?', 'will', 'Who', 'third_singular'),
            $this->entry('A1', 'future_be_question', 'Where {a1} the party be on Saturday?', 'will', 'the party', 'third_singular'),
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
            $this->entry('A2', 'future_be_question', 'Who {a1} be the speaker at tomorrow’s workshop?', 'will', 'Who', 'third_singular'),
            $this->entry('A2', 'future_be_question', 'Where {a1} the team be for training next week?', 'will', 'the team', 'third_singular'),
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
            $this->entry('B1', 'future_be_question', 'Who {a1} be responsible for coordinating the volunteers?', 'will', 'Who', 'third_singular'),
            $this->entry('B1', 'future_be_question', 'When {a1} the report be ready for review?', 'will', 'the report', 'third_singular'),
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
            $this->entry('B2', 'future_be_question', 'Who {a1} be accountable for monitoring compliance next month?', 'will', 'Who', 'third_singular'),
            $this->entry('B2', 'future_be_question', 'How {a1} the market be affected by the new policy?', 'will', 'the market', 'third_singular'),
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
            $this->entry('C1', 'future_be_question', 'Who {a1} be responsible for framing the policy response?', 'will', 'Who', 'third_singular'),
            $this->entry('C1', 'future_be_question', 'Where {a1} the decisive meeting be convened?', 'will', 'the decisive meeting', 'third_singular'),
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
            $this->entry('C2', 'future_be_question', 'Who {a1} be entrusted with drafting the supranational charter?', 'will', 'Who', 'third_singular'),
            $this->entry('C2', 'future_be_question', 'How {a1} the precedent be interpreted after the ruling?', 'will', 'the precedent', 'third_singular'),
            $this->entry('C2', 'future_be_negative', 'The mandate {a1} enforceable without multilateral consent.', "won't be", 'The mandate', 'third_singular'),
            $this->entry('C2', 'future_be_negative', 'Our position {a1} indefensible once the clause sunsets.', "won't be", 'Our position', 'third_singular'),
        ];

        $rawQuestions = [];
        foreach ($entries as $entry) {
            $config = $patternConfig[$entry['pattern']];
            $answer = $entry['answer'];
            $example = $this->formatExample($entry['question'], $answer);

            $rawQuestions[] = [
                'section' => $config['section'],
                'detail' => $config['detail'],
                'question' => $entry['question'],
                'verb_hint' => ['a1' => '(' . $config['verb_hint'] . ')'],
                'options' => $config['options'],
                'answers' => ['a1' => $answer],
                'explanations' => $this->buildExplanations($entry['pattern'], $entry, $config['options'], $answer, $example, $config['tense_label']),
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

        $service = new QuestionSeedingService();
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

        $service->seed($items);

        foreach ($meta as $data) {
            $question = Question::where('uuid', $data['uuid'])->first();
            if (! $question) {
                continue;
            }

            $hintText = $this->formatHints($data['hints']);
            if ($hintText !== null) {
                QuestionHint::updateOrCreate(
                    ['question_id' => $question->id, 'provider' => 'chatgpt', 'locale' => 'uk'],
                    ['hint' => $hintText]
                );
            }

            foreach ($data['explanations'] as $option => $text) {
                $marker = $data['option_markers'][$option] ?? array_key_first($data['answers']);
                $correct = $data['answers'][$marker] ?? reset($data['answers']);

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

    private function buildHint(string $pattern, array $entry, string $answer, string $example, array $config): string
    {
        $subject = $entry['subject'];
        $markers = $config['markers'];
        $answerTitle = $this->titleCase($answer);

        return match ($pattern) {
            'present_do_question' => "Час: {$config['tense_label']}.  \nФормула: **{$answerTitle} + {$subject} + V1...?**  \nПриклад: *{$example}*  \nМаркери: {$markers}.",
            'present_do_negative' => "Час: {$config['tense_label']}.  \nФормула: **{$subject} + {$answer} + V1**.  \nПриклад: *{$example}*  \nМаркери: {$markers}.",
            'present_be_question' => "Час: {$config['tense_label']}.  \nФормула: **{$answerTitle} + {$subject} + прикметник/місце?**  \nПриклад: *{$example}*  \nМаркери: {$markers}.",
            'present_be_negative' => "Час: {$config['tense_label']}.  \nФормула: **{$subject} + {$answer} + прикметник/місце**.  \nПриклад: *{$example}*  \nМаркери: {$markers}.",
            'past_do_question' => "Час: {$config['tense_label']}.  \nФормула: **Did + {$subject} + V1...?**  \nПриклад: *{$example}*  \nМаркери: {$markers}.",
            'past_do_negative' => "Час: {$config['tense_label']}.  \nФормула: **{$subject} + didn't + V1**.  \nПриклад: *{$example}*  \nМаркери: {$markers}.",
            'past_be_question' => "Час: {$config['tense_label']}.  \nФормула: **Was/Were + {$subject} + прикметник/місце?**  \nПриклад: *{$example}*  \nМаркери: {$markers}.",
            'past_be_negative' => "Час: {$config['tense_label']}.  \nФормула: **{$subject} + wasn't/weren't + прикметник/місце**.  \nПриклад: *{$example}*  \nМаркери: {$markers}.",
            'future_do_question' => "Час: {$config['tense_label']}.  \nФормула: **Will + {$subject} + V1...?**  \nПриклад: *{$example}*  \nМаркери: {$markers}.",
            'future_do_negative' => "Час: {$config['tense_label']}.  \nФормула: **{$subject} + won't + V1**.  \nПриклад: *{$example}*  \nМаркери: {$markers}.",
            'future_be_question' => "Час: {$config['tense_label']}.  \nФормула: **Will + {$subject} + be + прикметник/місце?**  \nПриклад: *{$example}*  \nМаркери: {$markers}.",
            'future_be_negative' => "Час: {$config['tense_label']}.  \nФормула: **{$subject} + won't be + прикметник/місце**.  \nПриклад: *{$example}*  \nМаркери: {$markers}.",
            default => '',
        };
    }

    private function buildStructureNote(string $pattern, array $entry, string $answer, string $example): string
    {
        $subject = $entry['subject'];
        $subjectDescription = $this->describeSubject($subject, $entry['subject_category']);

        return match ($pattern) {
            'present_do_question', 'past_do_question', 'future_do_question' => "ℹ️ Побудова: Розпочніть питання з допоміжного «{$answer}» (в українському перекладі — з частки «Чи»), після нього подайте {$subjectDescription}, далі основне дієслово у базовій формі та завершіть решту змісту, як у прикладі *{$example}*.",
            'present_do_negative', 'past_do_negative', 'future_do_negative' => "ℹ️ Побудова: Почніть із {$subjectDescription}, додайте заперечну форму «{$answer}», потім вжийте основне дієслово у базовій формі та завершіть речення додатковою інформацією. Орієнтуйтеся на приклад *{$example}*.",
            'present_be_question', 'past_be_question', 'future_be_question' => "ℹ️ Побудова: Розпочніть запитання з частки «Чи», далі подайте {$subjectDescription} та поставте правильну форму to be «{$answer}», після чого додайте прикметник або обставину, як показано в *{$example}*.",
            'present_be_negative', 'past_be_negative', 'future_be_negative' => "ℹ️ Побудова: Розташуйте {$subjectDescription}, після нього — відповідну заперечну форму to be «{$answer}», а далі подайте прикметник чи іменник, що описує стан, як у прикладі *{$example}*.",
            default => '',
        };
    }

    private function describeSubject(string $subject, string $category): string
    {
        $normalized = mb_strtolower($subject, 'UTF-8');

        $questionWords = ['who', 'what', 'where', 'when', 'why', 'how'];
        if (in_array($normalized, $questionWords, true)) {
            return "запитального слова «{$subject}»";
        }

        return match ($category) {
            'i' => 'займенника «I»',
            'you' => 'займенника «you»',
            'plural' => "множинного підмета «{$subject}»",
            'third_singular' => "словосполучення підмета «{$subject}»",
            default => "підмета «{$subject}»",
        };
    }

    private function buildExplanations(string $pattern, array $entry, array $options, string $answer, string $example, string $tenseLabel): array
    {
        $subjectPhrase = $this->subjectPhrase($entry['subject_category'], $entry['subject']);

        $explanations = [];
        foreach ($options as $option) {
            if ($option === $answer) {
                $base = $this->buildCorrectExplanation($pattern, $subjectPhrase, $answer, $example, $tenseLabel);
            } else {
                $base = $this->buildWrongExplanation($pattern, $subjectPhrase, $option, $answer, $tenseLabel);
            }

            $structure = $this->buildStructureNote($pattern, $entry, $answer, $example);
            $explanations[$option] = $structure === '' ? $base : $base . "\n\n" . $structure;
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
            'does' => '*Does she study English?*',
            'am' => '*Am I late?*',
            'is' => '*Is he ready?*',
            'are' => '*Are they ready?*',
            "don't" => "*They don't eat meat.*",
            "doesn't" => "*She doesn't eat meat.*",
            'am not' => '*I am not tired.*',
            "isn't" => "*He isn't ready.*",
            "aren't" => "*They aren't ready.*",
            'did' => '*Did you call her?*',
            "didn't" => "*She didn't call yesterday.*",
            'was' => '*Was he at home?*',
            'were' => '*Were they at home?*',
            "wasn't" => "*He wasn't late.*",
            "weren't" => "*They weren't late.*",
            'will' => '*Will you join us tomorrow?*',
            "won't" => "*She won't come tomorrow.*",
            'will be' => '*Will the meeting be online?*',
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
        if ($option === "don't" || $option === "doesn't" || $option === "didn't") {
            return "❌ «{$option}» описує інший час, а для майбутнього заперечення потрібне «won't».  \nПриклад: {$example}";
        }

        return "❌ Неправильний вибір.  \nПриклад: {$example}";
    }

    private function explainFutureBeQuestion(string $option, string $example): string
    {
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

    private function titleCase(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        $first = mb_substr($value, 0, 1, 'UTF-8');
        $rest = mb_substr($value, 1, null, 'UTF-8');

        return mb_strtoupper($first, 'UTF-8') . $rest;
    }

    private function formatExample(string $question, string $answer): string
    {
        $sentence = str_replace('{a1}', $answer, $question);
        $sentence = preg_replace_callback('/^[a-zа-яёіїєґ]/iu', fn ($m) => mb_strtoupper($m[0], 'UTF-8'), $sentence);

        return $sentence;
    }

    private function normalizeHint(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return trim($value, "() \t\n\r");
    }

    private function formatHints(array $hints): ?string
    {
        if (empty($hints)) {
            return null;
        }

        $parts = [];
        foreach ($hints as $text) {
            $clean = trim($text);

            if ($clean === '') {
                continue;
            }

            $parts[] = $clean;
        }

        if (empty($parts)) {
            return null;
        }

        return implode("\n", $parts);
    }
}
