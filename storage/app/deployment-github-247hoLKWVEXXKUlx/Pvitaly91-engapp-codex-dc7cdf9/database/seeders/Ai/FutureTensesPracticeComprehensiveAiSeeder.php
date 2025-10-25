<?php

namespace Database\Seeders\Ai;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class FutureTensesPracticeComprehensiveAiSeeder extends QuestionSeeder
{
    public function run(): void
    {
          
        $categoryId = Category::firstOrCreate(['name' => 'Future Tenses Comprehensive AI Practice'])->id;
        $sourceIds = [
            'future_simple' => Source::firstOrCreate(['name' => 'Custom: Future Simple Comprehensive AI'])->id,
            'future_continuous' => Source::firstOrCreate(['name' => 'Custom: Future Continuous Comprehensive AI'])->id,
            'future_perfect' => Source::firstOrCreate(['name' => 'Custom: Future Perfect Comprehensive AI'])->id,
            'future_perfect_continuous' => Source::firstOrCreate(['name' => 'Custom: Future Perfect Continuous Comprehensive AI'])->id,
        ];

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Future Tenses Comprehensive Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTags = [
            'future_simple' => Tag::firstOrCreate(['name' => 'Future Simple Focus'], ['category' => 'English Grammar Detail'])->id,
            'future_continuous' => Tag::firstOrCreate(['name' => 'Future Continuous Focus'], ['category' => 'English Grammar Detail'])->id,
            'future_perfect' => Tag::firstOrCreate(['name' => 'Future Perfect Focus'], ['category' => 'English Grammar Detail'])->id,
            'future_perfect_continuous' => Tag::firstOrCreate(['name' => 'Future Perfect Continuous Focus'], ['category' => 'English Grammar Detail'])->id,
        ];

        $tenseTags = [
            'Future Simple' => Tag::firstOrCreate(['name' => 'Future Simple'], ['category' => 'Tenses'])->id,
            'Future Continuous' => Tag::firstOrCreate(['name' => 'Future Continuous'], ['category' => 'Tenses'])->id,
            'Future Perfect' => Tag::firstOrCreate(['name' => 'Future Perfect'], ['category' => 'Tenses'])->id,
            'Future Perfect Continuous' => Tag::firstOrCreate(['name' => 'Future Perfect Continuous'], ['category' => 'Tenses'])->id,
        ];

        $patternConfig = [
            'future_simple_question' => [
                'tense' => 'Future Simple',
                'detail' => 'future_simple',
                'structure' => 'Future Simple Question Form',
                'markers' => 'tomorrow, next week, soon',
            ],
            'future_simple_negative' => [
                'tense' => 'Future Simple',
                'detail' => 'future_simple',
                'structure' => 'Future Simple Negative Form',
                'markers' => 'tomorrow, in the future, later',
            ],
            'future_continuous_question' => [
                'tense' => 'Future Continuous',
                'detail' => 'future_continuous',
                'structure' => 'Future Continuous Question Form',
                'markers' => 'this time tomorrow, at 5 p.m. tomorrow, later today',
            ],
            'future_continuous_negative' => [
                'tense' => 'Future Continuous',
                'detail' => 'future_continuous',
                'structure' => 'Future Continuous Negative Form',
                'markers' => 'this time tomorrow, at midnight tomorrow, later today',
            ],
            'future_perfect_question' => [
                'tense' => 'Future Perfect',
                'detail' => 'future_perfect',
                'structure' => 'Future Perfect Question Form',
                'markers' => 'by tomorrow, by next week, before',
            ],
            'future_perfect_negative' => [
                'tense' => 'Future Perfect',
                'detail' => 'future_perfect',
                'structure' => 'Future Perfect Negative Form',
                'markers' => 'by tomorrow, by the deadline, before',
            ],
            'future_perfect_continuous_question' => [
                'tense' => 'Future Perfect Continuous',
                'detail' => 'future_perfect_continuous',
                'structure' => 'Future Perfect Continuous Question Form',
                'markers' => 'by the time, for, since',
            ],
            'future_perfect_continuous_negative' => [
                'tense' => 'Future Perfect Continuous',
                'detail' => 'future_perfect_continuous',
                'structure' => 'Future Perfect Continuous Negative Form',
                'markers' => 'by the time, for, since',
            ],
        ];

        $structureTagIds = [];
        foreach ($patternConfig as $config) {
            $structure = $config['structure'];
            if (! isset($structureTagIds[$structure])) {
                $structureTagIds[$structure] = Tag::firstOrCreate(
                    ['name' => $structure],
                    ['category' => 'English Grammar Structure']
                )->id;
            }
        }

        $levelDifficulty = [
            'A1' => 1,
            'A2' => 2,
            'B1' => 3,
            'B2' => 4,
            'C1' => 5,
            'C2' => 5,
        ];

        $levels = [
            'A1' => [
                ['pattern' => 'future_simple_question', 'question' => 'When {a1} the movie night on Friday?', 'forms' => $this->forms('we', 'start', 'starting', 'started')],
                ['pattern' => 'future_simple_question', 'question' => 'What time {a1} you call your sister tomorrow?', 'forms' => $this->forms('you', 'call', 'calling', 'called')],
                ['pattern' => 'future_simple_question', 'question' => 'Where {a1} they meet us next weekend?', 'forms' => $this->forms('they', 'meet', 'meeting', 'met')],
                ['pattern' => 'future_simple_negative', 'question' => 'I {a1} sweets after dinner anymore.', 'forms' => $this->forms('I', 'eat', 'eating', 'eaten')],
                ['pattern' => 'future_simple_negative', 'question' => 'We {a1} forget your birthday this year.', 'forms' => $this->forms('we', 'forget', 'forgetting', 'forgotten')],
                ['pattern' => 'future_simple_negative', 'question' => 'She {a1} buy a new phone this month.', 'forms' => $this->forms('she', 'buy', 'buying', 'bought')],
                ['pattern' => 'future_continuous_question', 'question' => 'What {a1} at 7 p.m. tomorrow?', 'forms' => $this->forms('you', 'do', 'doing', 'done')],
                ['pattern' => 'future_continuous_question', 'question' => 'Why {a1} outside tomorrow morning?', 'forms' => $this->forms('they', 'wait', 'waiting', 'waited')],
                ['pattern' => 'future_continuous_question', 'question' => 'What {a1} in the kitchen at noon?', 'forms' => $this->forms('we', 'prepare', 'preparing', 'prepared')],
                ['pattern' => 'future_continuous_negative', 'question' => 'I {a1} during the early flight tomorrow.', 'forms' => $this->forms('I', 'sleep', 'sleeping', 'slept')],
                ['pattern' => 'future_continuous_negative', 'question' => 'They {a1} for the exam at midnight.', 'forms' => $this->forms('they', 'study', 'studying', 'studied')],
                ['pattern' => 'future_continuous_negative', 'question' => 'We {a1} on the road during the storm.', 'forms' => $this->forms('we', 'drive', 'driving', 'driven')],
                ['pattern' => 'future_perfect_question', 'question' => 'How many pages {a1} by the deadline?', 'forms' => $this->forms('you', 'write', 'writing', 'written')],
                ['pattern' => 'future_perfect_question', 'question' => 'When {a1} she finish the painting?', 'forms' => $this->forms('she', 'finish', 'finishing', 'finished')],
                ['pattern' => 'future_perfect_question', 'question' => 'By what time {a1} they clean the hall?', 'forms' => $this->forms('they', 'clean', 'cleaning', 'cleaned')],
                ['pattern' => 'future_perfect_negative', 'question' => 'We {a1} the chores before the guests arrive.', 'forms' => $this->forms('we', 'finish', 'finishing', 'finished')],
                ['pattern' => 'future_perfect_negative', 'question' => 'He {a1} the tickets by noon.', 'forms' => $this->forms('he', 'book', 'booking', 'booked')],
                ['pattern' => 'future_perfect_negative', 'question' => 'I {a1} saved enough money by July.', 'forms' => $this->forms('I', 'save', 'saving', 'saved')],
                ['pattern' => 'future_perfect_continuous_question', 'question' => 'How long {a1} the guitar by next summer?', 'forms' => $this->forms('she', 'practice', 'practicing', 'practiced')],
                ['pattern' => 'future_perfect_continuous_question', 'question' => 'By 2030, how long {a1} in this city?', 'forms' => $this->forms('we', 'live', 'living', 'lived')],
                ['pattern' => 'future_perfect_continuous_question', 'question' => 'By the time the boss returns, how long {a1} on the project?', 'forms' => $this->forms('you', 'work', 'working', 'worked')],
                ['pattern' => 'future_perfect_continuous_negative', 'question' => 'I {a1} here long enough to know everyone by May.', 'forms' => $this->forms('I', 'work', 'working', 'worked')],
                ['pattern' => 'future_perfect_continuous_negative', 'question' => 'They {a1} in traffic for hours before sunset.', 'forms' => $this->forms('they', 'sit', 'sitting', 'sat')],
                ['pattern' => 'future_perfect_continuous_negative', 'question' => 'She {a1} studying the topic for a full week by Monday.', 'forms' => $this->forms('she', 'study', 'studying', 'studied')],
            ],
            'A2' => [
                ['pattern' => 'future_simple_question', 'question' => 'When {a1} the team present their idea next week?', 'forms' => $this->forms('the team', 'present', 'presenting', 'presented')],
                ['pattern' => 'future_simple_question', 'question' => 'Why {a1} you visit the client on Tuesday?', 'forms' => $this->forms('you', 'visit', 'visiting', 'visited')],
                ['pattern' => 'future_simple_question', 'question' => 'Which city {a1} they explore during the holiday?', 'forms' => $this->forms('they', 'explore', 'exploring', 'explored')],
                ['pattern' => 'future_simple_negative', 'question' => 'I {a1} skip the training session again.', 'forms' => $this->forms('I', 'skip', 'skipping', 'skipped')],
                ['pattern' => 'future_simple_negative', 'question' => 'We {a1} change the meeting place at the last minute.', 'forms' => $this->forms('we', 'change', 'changing', 'changed')],
                ['pattern' => 'future_simple_negative', 'question' => 'Lena {a1} buy the cheaper tickets this time.', 'forms' => $this->forms('Lena', 'buy', 'buying', 'bought')],
                ['pattern' => 'future_continuous_question', 'question' => 'What {a1} the students researching tomorrow afternoon?', 'forms' => $this->forms('the students', 'research', 'researching', 'researched')],
                ['pattern' => 'future_continuous_question', 'question' => 'Who {a1} you meeting after lunch?', 'forms' => $this->forms('you', 'meet', 'meeting', 'met')],
                ['pattern' => 'future_continuous_question', 'question' => 'Where {a1} we staying during the festival?', 'forms' => $this->forms('we', 'stay', 'staying', 'stayed')],
                ['pattern' => 'future_continuous_negative', 'question' => 'They {a1} driving through the night.', 'forms' => $this->forms('they', 'drive', 'driving', 'driven')],
                ['pattern' => 'future_continuous_negative', 'question' => 'I {a1} answering emails during dinner.', 'forms' => $this->forms('I', 'answer', 'answering', 'answered')],
                ['pattern' => 'future_continuous_negative', 'question' => 'The kids {a1} playing outside if it rains.', 'forms' => $this->forms('the kids', 'play', 'playing', 'played')],
                ['pattern' => 'future_perfect_question', 'question' => 'How much data {a1} the lab collect by Friday?', 'forms' => $this->forms('the lab', 'collect', 'collecting', 'collected')],
                ['pattern' => 'future_perfect_question', 'question' => 'By when {a1} you finish the online course?', 'forms' => $this->forms('you', 'finish', 'finishing', 'finished')],
                ['pattern' => 'future_perfect_question', 'question' => 'Which chapters {a1} they read before the seminar?', 'forms' => $this->forms('they', 'read', 'reading', 'read')],
                ['pattern' => 'future_perfect_negative', 'question' => 'We {a1} solved the issue before the call.', 'forms' => $this->forms('we', 'solve', 'solving', 'solved')],
                ['pattern' => 'future_perfect_negative', 'question' => 'He {a1} saved enough for the trip by April.', 'forms' => $this->forms('he', 'save', 'saving', 'saved')],
                ['pattern' => 'future_perfect_negative', 'question' => 'I {a1} completed the draft before lunch.', 'forms' => $this->forms('I', 'complete', 'completing', 'completed')],
                ['pattern' => 'future_perfect_continuous_question', 'question' => 'How long {a1} the volunteers training by the time of the race?', 'forms' => $this->forms('the volunteers', 'train', 'training', 'trained')],
                ['pattern' => 'future_perfect_continuous_question', 'question' => 'By 2026, how long {a1} in Berlin?', 'forms' => $this->forms('you', 'live', 'living', 'lived')],
                ['pattern' => 'future_perfect_continuous_question', 'question' => 'By next month, how long {a1} this podcast?', 'forms' => $this->forms('we', 'produce', 'producing', 'produced')],
                ['pattern' => 'future_perfect_continuous_negative', 'question' => 'They {a1} living in temporary housing by winter.', 'forms' => $this->forms('they', 'live', 'living', 'lived')],
                ['pattern' => 'future_perfect_continuous_negative', 'question' => 'I {a1} using this laptop long enough to replace it by June.', 'forms' => $this->forms('I', 'use', 'using', 'used')],
                ['pattern' => 'future_perfect_continuous_negative', 'question' => 'Our team {a1} studying the report for an entire day by then.', 'forms' => $this->forms('our team', 'study', 'studying', 'studied')],
            ],
            'B1' => [
                ['pattern' => 'future_simple_question', 'question' => 'When {a1} the committee announce the shortlist?', 'forms' => $this->forms('the committee', 'announce', 'announcing', 'announced')],
                ['pattern' => 'future_simple_question', 'question' => 'Why {a1} you submit the proposal without feedback?', 'forms' => $this->forms('you', 'submit', 'submitting', 'submitted')],
                ['pattern' => 'future_simple_question', 'question' => 'Which supplier {a1} they choose after the audit?', 'forms' => $this->forms('they', 'choose', 'choosing', 'chosen')],
                ['pattern' => 'future_simple_negative', 'question' => 'We {a1} approve the design without testing.', 'forms' => $this->forms('we', 'approve', 'approving', 'approved')],
                ['pattern' => 'future_simple_negative', 'question' => 'I {a1} ignore the data you collected.', 'forms' => $this->forms('I', 'ignore', 'ignoring', 'ignored')],
                ['pattern' => 'future_simple_negative', 'question' => 'The manager {a1} postpone the briefing again.', 'forms' => $this->forms('the manager', 'postpone', 'postponing', 'postponed')],
                ['pattern' => 'future_continuous_question', 'question' => 'What {a1} you presenting at the conference tomorrow?', 'forms' => $this->forms('you', 'present', 'presenting', 'presented')],
                ['pattern' => 'future_continuous_question', 'question' => 'Who {a1} the consultants advising next quarter?', 'forms' => $this->forms('the consultants', 'advise', 'advising', 'advised')],
                ['pattern' => 'future_continuous_question', 'question' => 'Where {a1} the engineers working during the relocation?', 'forms' => $this->forms('the engineers', 'work', 'working', 'worked')],
                ['pattern' => 'future_continuous_negative', 'question' => 'We {a1} waiting for confirmation all evening.', 'forms' => $this->forms('we', 'wait', 'waiting', 'waited')],
                ['pattern' => 'future_continuous_negative', 'question' => 'I {a1} checking emails during my vacation.', 'forms' => $this->forms('I', 'check', 'checking', 'checked')],
                ['pattern' => 'future_continuous_negative', 'question' => 'They {a1} using the main hall while it is repaired.', 'forms' => $this->forms('they', 'use', 'using', 'used')],
                ['pattern' => 'future_perfect_question', 'question' => 'How many prototypes {a1} the team create by September?', 'forms' => $this->forms('the team', 'create', 'creating', 'created')],
                ['pattern' => 'future_perfect_question', 'question' => 'By what date {a1} you verify all references?', 'forms' => $this->forms('you', 'verify', 'verifying', 'verified')],
                ['pattern' => 'future_perfect_question', 'question' => 'Which regions {a1} they survey before launch?', 'forms' => $this->forms('they', 'survey', 'surveying', 'surveyed')],
                ['pattern' => 'future_perfect_negative', 'question' => 'We {a1} gathered enough evidence by Monday.', 'forms' => $this->forms('we', 'gather', 'gathering', 'gathered')],
                ['pattern' => 'future_perfect_negative', 'question' => 'She {a1} completed the translation before the flight.', 'forms' => $this->forms('she', 'complete', 'completing', 'completed')],
                ['pattern' => 'future_perfect_negative', 'question' => 'I {a1} organized the files before the audit.', 'forms' => $this->forms('I', 'organize', 'organizing', 'organized')],
                ['pattern' => 'future_perfect_continuous_question', 'question' => 'How long {a1} the interns learning the new software by July?', 'forms' => $this->forms('the interns', 'learn', 'learning', 'learned')],
                ['pattern' => 'future_perfect_continuous_question', 'question' => 'By next spring, how long {a1} on the sustainability plan?', 'forms' => $this->forms('you', 'work', 'working', 'worked')],
                ['pattern' => 'future_perfect_continuous_question', 'question' => 'By 2032, how long {a1} renewable energy research?', 'forms' => $this->forms('they', 'conduct', 'conducting', 'conducted')],
                ['pattern' => 'future_perfect_continuous_negative', 'question' => 'We {a1} collaborating with that vendor long enough to renew the contract.', 'forms' => $this->forms('we', 'collaborate', 'collaborating', 'collaborated')],
                ['pattern' => 'future_perfect_continuous_negative', 'question' => 'I {a1} living abroad long enough to lose touch.', 'forms' => $this->forms('I', 'live', 'living', 'lived')],
                ['pattern' => 'future_perfect_continuous_negative', 'question' => 'The analysts {a1} monitoring the pilot for a full year by that point.', 'forms' => $this->forms('the analysts', 'monitor', 'monitoring', 'monitored')],
            ],
            'B2' => [
                ['pattern' => 'future_simple_question', 'question' => 'When {a1} the board reveal the merger details?', 'forms' => $this->forms('the board', 'reveal', 'revealing', 'revealed')],
                ['pattern' => 'future_simple_question', 'question' => 'Why {a1} you challenge the preliminary findings?', 'forms' => $this->forms('you', 'challenge', 'challenging', 'challenged')],
                ['pattern' => 'future_simple_question', 'question' => 'Which metrics {a1} they track in the next review?', 'forms' => $this->forms('they', 'track', 'tracking', 'tracked')],
                ['pattern' => 'future_simple_negative', 'question' => 'We {a1} compromise the budget for marketing.', 'forms' => $this->forms('we', 'compromise', 'compromising', 'compromised')],
                ['pattern' => 'future_simple_negative', 'question' => 'I {a1} release the statement before legal approval.', 'forms' => $this->forms('I', 'release', 'releasing', 'released')],
                ['pattern' => 'future_simple_negative', 'question' => 'The director {a1} dismiss the consultant’s warnings.', 'forms' => $this->forms('the director', 'dismiss', 'dismissing', 'dismissed')],
                ['pattern' => 'future_continuous_question', 'question' => 'What {a1} the researchers examining during the field study?', 'forms' => $this->forms('the researchers', 'examine', 'examining', 'examined')],
                ['pattern' => 'future_continuous_question', 'question' => 'Who {a1} you interviewing for the feature tomorrow?', 'forms' => $this->forms('you', 'interview', 'interviewing', 'interviewed')],
                ['pattern' => 'future_continuous_question', 'question' => 'Where {a1} the delegates staying during the summit?', 'forms' => $this->forms('the delegates', 'stay', 'staying', 'stayed')],
                ['pattern' => 'future_continuous_negative', 'question' => 'We {a1} relying on manual reports next quarter.', 'forms' => $this->forms('we', 'rely', 'relying', 'relied')],
                ['pattern' => 'future_continuous_negative', 'question' => 'I {a1} attending routine meetings while on sabbatical.', 'forms' => $this->forms('I', 'attend', 'attending', 'attended')],
                ['pattern' => 'future_continuous_negative', 'question' => 'They {a1} shipping orders during the system upgrade.', 'forms' => $this->forms('they', 'ship', 'shipping', 'shipped')],
                ['pattern' => 'future_perfect_question', 'question' => 'How many partnerships {a1} the firm secure by year-end?', 'forms' => $this->forms('the firm', 'secure', 'securing', 'secured')],
                ['pattern' => 'future_perfect_question', 'question' => 'By which milestone {a1} you compile the market analysis?', 'forms' => $this->forms('you', 'compile', 'compiling', 'compiled')],
                ['pattern' => 'future_perfect_question', 'question' => 'Which scenarios {a1} they model before presenting?', 'forms' => $this->forms('they', 'model', 'modeling', 'modeled')],
                ['pattern' => 'future_perfect_negative', 'question' => 'We {a1} resolved the compliance gaps by audit day.', 'forms' => $this->forms('we', 'resolve', 'resolving', 'resolved')],
                ['pattern' => 'future_perfect_negative', 'question' => 'She {a1} gathered stakeholder feedback before finalizing.', 'forms' => $this->forms('she', 'gather', 'gathering', 'gathered')],
                ['pattern' => 'future_perfect_negative', 'question' => 'I {a1} documented the edge cases before deployment.', 'forms' => $this->forms('I', 'document', 'documenting', 'documented')],
                ['pattern' => 'future_perfect_continuous_question', 'question' => 'How long {a1} the analysts reviewing the portfolio by December?', 'forms' => $this->forms('the analysts', 'review', 'reviewing', 'reviewed')],
                ['pattern' => 'future_perfect_continuous_question', 'question' => 'By 2030, how long {a1} you leading the remote team?', 'forms' => $this->forms('you', 'lead', 'leading', 'led')],
                ['pattern' => 'future_perfect_continuous_question', 'question' => 'By next audit, how long {a1} the company testing automated controls?', 'forms' => $this->forms('the company', 'test', 'testing', 'tested')],
                ['pattern' => 'future_perfect_continuous_negative', 'question' => 'We {a1} negotiating with that supplier long enough to extend terms.', 'forms' => $this->forms('we', 'negotiate', 'negotiating', 'negotiated')],
                ['pattern' => 'future_perfect_continuous_negative', 'question' => 'I {a1} researching the case long enough to publish by April.', 'forms' => $this->forms('I', 'research', 'researching', 'researched')],
                ['pattern' => 'future_perfect_continuous_negative', 'question' => 'The partners {a1} investing in the venture for a full decade by then.', 'forms' => $this->forms('the partners', 'invest', 'investing', 'invested')],
            ],
            'C1' => [
                ['pattern' => 'future_simple_question', 'question' => 'When {a1} the panel disclose the funding decision?', 'forms' => $this->forms('the panel', 'disclose', 'disclosing', 'disclosed')],
                ['pattern' => 'future_simple_question', 'question' => 'Why {a1} you reframe the narrative before the briefing?', 'forms' => $this->forms('you', 'reframe', 'reframing', 'reframed')],
                ['pattern' => 'future_simple_question', 'question' => 'Which hypotheses {a1} they prioritize during the symposium?', 'forms' => $this->forms('they', 'prioritize', 'prioritizing', 'prioritized')],
                ['pattern' => 'future_simple_negative', 'question' => 'We {a1} endorse the draft without empirical support.', 'forms' => $this->forms('we', 'endorse', 'endorsing', 'endorsed')],
                ['pattern' => 'future_simple_negative', 'question' => 'I {a1} undermine the peer review recommendations.', 'forms' => $this->forms('I', 'undermine', 'undermining', 'undermined')],
                ['pattern' => 'future_simple_negative', 'question' => 'The director {a1} authorize the merger without due diligence.', 'forms' => $this->forms('the director', 'authorize', 'authorizing', 'authorized')],
                ['pattern' => 'future_continuous_question', 'question' => 'What {a1} the advisory board debating throughout the retreat?', 'forms' => $this->forms('the advisory board', 'debate', 'debating', 'debated')],
                ['pattern' => 'future_continuous_question', 'question' => 'Who {a1} you consulting when the crisis unfolds?', 'forms' => $this->forms('you', 'consult', 'consulting', 'consulted')],
                ['pattern' => 'future_continuous_question', 'question' => 'Where {a1} the research fellows collaborating during the residency?', 'forms' => $this->forms('the research fellows', 'collaborate', 'collaborating', 'collaborated')],
                ['pattern' => 'future_continuous_negative', 'question' => 'We {a1} engaging in routine audits during the pilot phase.', 'forms' => $this->forms('we', 'engage', 'engaging', 'engaged')],
                ['pattern' => 'future_continuous_negative', 'question' => 'I {a1} moderating every panel while traveling abroad.', 'forms' => $this->forms('I', 'moderate', 'moderating', 'moderated')],
                ['pattern' => 'future_continuous_negative', 'question' => 'They {a1} broadcasting updates until the embargo lifts.', 'forms' => $this->forms('they', 'broadcast', 'broadcasting', 'broadcast')],
                ['pattern' => 'future_perfect_question', 'question' => 'How many case studies {a1} the institute publish by next winter?', 'forms' => $this->forms('the institute', 'publish', 'publishing', 'published')],
                ['pattern' => 'future_perfect_question', 'question' => 'By what stage {a1} you synthesize the stakeholder insights?', 'forms' => $this->forms('you', 'synthesize', 'synthesizing', 'synthesized')],
                ['pattern' => 'future_perfect_question', 'question' => 'Which protocols {a1} they refine before regulatory review?', 'forms' => $this->forms('they', 'refine', 'refining', 'refined')],
                ['pattern' => 'future_perfect_negative', 'question' => 'We {a1} mitigated the systemic risks before rollout.', 'forms' => $this->forms('we', 'mitigate', 'mitigating', 'mitigated')],
                ['pattern' => 'future_perfect_negative', 'question' => 'She {a1} assembled the interdisciplinary panel by July.', 'forms' => $this->forms('she', 'assemble', 'assembling', 'assembled')],
                ['pattern' => 'future_perfect_negative', 'question' => 'I {a1} archived the raw datasets prior to publication.', 'forms' => $this->forms('I', 'archive', 'archiving', 'archived')],
                ['pattern' => 'future_perfect_continuous_question', 'question' => 'How long {a1} the economists modeling the scenario by the summit?', 'forms' => $this->forms('the economists', 'model', 'modeling', 'modeled')],
                ['pattern' => 'future_perfect_continuous_question', 'question' => 'By 2035, how long {a1} you mentoring emerging researchers?', 'forms' => $this->forms('you', 'mentor', 'mentoring', 'mentored')],
                ['pattern' => 'future_perfect_continuous_question', 'question' => 'By next decade, how long {a1} they experimenting with quantum algorithms?', 'forms' => $this->forms('they', 'experiment', 'experimenting', 'experimented')],
                ['pattern' => 'future_perfect_continuous_negative', 'question' => 'We {a1} operating under interim guidelines long enough to normalize them.', 'forms' => $this->forms('we', 'operate', 'operating', 'operated')],
                ['pattern' => 'future_perfect_continuous_negative', 'question' => 'I {a1} scrutinizing that dataset long enough to exhaust every lead.', 'forms' => $this->forms('I', 'scrutinize', 'scrutinizing', 'scrutinized')],
                ['pattern' => 'future_perfect_continuous_negative', 'question' => 'The consortium {a1} negotiating tariffs for a full cycle by then.', 'forms' => $this->forms('the consortium', 'negotiate', 'negotiating', 'negotiated')],
            ],
            'C2' => [
                ['pattern' => 'future_simple_question', 'question' => 'When {a1} the council ratify the cross-border accord?', 'forms' => $this->forms('the council', 'ratify', 'ratifying', 'ratified')],
                ['pattern' => 'future_simple_question', 'question' => 'Why {a1} you recalibrate the protocol before peer review?', 'forms' => $this->forms('you', 'recalibrate', 'recalibrating', 'recalibrated')],
                ['pattern' => 'future_simple_question', 'question' => 'Which paradigms {a1} they interrogate in the forthcoming manifesto?', 'forms' => $this->forms('they', 'interrogate', 'interrogating', 'interrogated')],
                ['pattern' => 'future_simple_negative', 'question' => 'We {a1} concede the argument without replicable evidence.', 'forms' => $this->forms('we', 'concede', 'conceding', 'conceded')],
                ['pattern' => 'future_simple_negative', 'question' => 'I {a1} dilute the thesis to appease the reviewers.', 'forms' => $this->forms('I', 'dilute', 'diluting', 'diluted')],
                ['pattern' => 'future_simple_negative', 'question' => 'The editor {a1} approve revisions that compromise integrity.', 'forms' => $this->forms('the editor', 'approve', 'approving', 'approved')],
                ['pattern' => 'future_continuous_question', 'question' => 'What {a1} the negotiators arbitrating during the emergency summit?', 'forms' => $this->forms('the negotiators', 'arbitrate', 'arbitrating', 'arbitrated')],
                ['pattern' => 'future_continuous_question', 'question' => 'Who {a1} you liaising with while the delegation travels?', 'forms' => $this->forms('you', 'liaise', 'liaising', 'liaised')],
                ['pattern' => 'future_continuous_question', 'question' => 'Where {a1} the visiting scholars convening throughout the residency?', 'forms' => $this->forms('the visiting scholars', 'convene', 'convening', 'convened')],
                ['pattern' => 'future_continuous_negative', 'question' => 'We {a1} disseminating preliminary drafts before the embargo ends.', 'forms' => $this->forms('we', 'disseminate', 'disseminating', 'disseminated')],
                ['pattern' => 'future_continuous_negative', 'question' => 'I {a1} occupying the plenary chair during the interim period.', 'forms' => $this->forms('I', 'occupy', 'occupying', 'occupied')],
                ['pattern' => 'future_continuous_negative', 'question' => 'They {a1} operating the reactor while safeguards are offline.', 'forms' => $this->forms('they', 'operate', 'operating', 'operated')],
                ['pattern' => 'future_perfect_question', 'question' => 'How many policy briefs {a1} the task force circulate by year-end?', 'forms' => $this->forms('the task force', 'circulate', 'circulating', 'circulated')],
                ['pattern' => 'future_perfect_question', 'question' => 'By which juncture {a1} you reconcile the competing frameworks?', 'forms' => $this->forms('you', 'reconcile', 'reconciling', 'reconciled')],
                ['pattern' => 'future_perfect_question', 'question' => 'Which assumptions {a1} they invalidate before publishing?', 'forms' => $this->forms('they', 'invalidate', 'invalidating', 'invalidated')],
                ['pattern' => 'future_perfect_negative', 'question' => 'We {a1} institutionalized the reforms before the transition.', 'forms' => $this->forms('we', 'institutionalize', 'institutionalizing', 'institutionalized')],
                ['pattern' => 'future_perfect_negative', 'question' => 'She {a1} secured the multi-year grant by the conference.', 'forms' => $this->forms('she', 'secure', 'securing', 'secured')],
                ['pattern' => 'future_perfect_negative', 'question' => 'I {a1} audited the encrypted archives by the deadline.', 'forms' => $this->forms('I', 'audit', 'auditing', 'audited')],
                ['pattern' => 'future_perfect_continuous_question', 'question' => 'How long {a1} the think tank evaluating the pilot by 2040?', 'forms' => $this->forms('the think tank', 'evaluate', 'evaluating', 'evaluated')],
                ['pattern' => 'future_perfect_continuous_question', 'question' => 'By 2038, how long {a1} you curating the transnational consortium?', 'forms' => $this->forms('you', 'curate', 'curating', 'curated')],
                ['pattern' => 'future_perfect_continuous_question', 'question' => 'By the time regulation shifts, how long {a1} they refining the predictive engine?', 'forms' => $this->forms('they', 'refine', 'refining', 'refined')],
                ['pattern' => 'future_perfect_continuous_negative', 'question' => 'We {a1} leveraging provisional metrics long enough to treat them as standards.', 'forms' => $this->forms('we', 'leverage', 'leveraging', 'leveraged')],
                ['pattern' => 'future_perfect_continuous_negative', 'question' => 'I {a1} interrogating that corpus long enough to draw final conclusions.', 'forms' => $this->forms('I', 'interrogate', 'interrogating', 'interrogated')],
                ['pattern' => 'future_perfect_continuous_negative', 'question' => 'The mediators {a1} steering back-channel talks for the entire decade by then.', 'forms' => $this->forms('the mediators', 'steer', 'steering', 'steered')],
            ],
        ];

        $questions = [];
        foreach ($levels as $level => $entries) {
            foreach ($entries as $entry) {
                $pattern = $entry['pattern'];
                $config = $patternConfig[$pattern];
                $forms = $entry['forms'];

                $answer = $this->buildAnswer($pattern, $forms);
                $options = $this->ensureOptions($this->buildOptions($pattern, $forms), $answer, $forms);

                $example = $this->formatExample($entry['question'], $answer);
                $hint = $this->buildHint($pattern, $forms, $config, $example);
                $explanations = $this->buildExplanations($pattern, $forms, $options, $answer, $config['tense'], $example);

                $questions[] = [
                    'level' => $level,
                    'pattern' => $pattern,
                    'question' => $entry['question'],
                    'verb_hint' => ['a1' => '(' . $forms['verb']['base'] . ')'],
                    'options' => $options,
                    'answers' => ['a1' => $answer],
                    'explanations' => $explanations,
                    'hints' => ['a1' => $hint],
                    'tense' => [$config['tense']],
                    'detail' => $config['detail'],
                    'structure' => $config['structure'],
                ];
            }
        }

        $items = [];
        $meta = [];

        foreach ($questions as $index => $question) {
            $uuid = $this->generateQuestionUuid($question['level'], $index, $question['question']);

            $answers = [
                [
                    'marker' => 'a1',
                    'answer' => $question['answers']['a1'],
                    'verb_hint' => $this->normalizeHint($question['verb_hint']['a1'] ?? null),
                ],
            ];

            $optionMarkers = [];
            foreach ($question['options'] as $option) {
                $optionMarkers[$option] = 'a1';
            }

            $tagIds = [
                $themeTagId,
                $detailTags[$question['detail']],
                $structureTagIds[$question['structure']],
            ];

            foreach ($question['tense'] as $tenseName) {
                $tagIds[] = $tenseTags[$tenseName];
            }

            $items[] = [
                'uuid' => $uuid,
                'question' => $question['question'],
                'category_id' => $categoryId,
                'difficulty' => $levelDifficulty[$question['level']] ?? 3,
                'source_id' => $sourceIds[$question['detail']],
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
                'option_markers' => $optionMarkers,
                'hints' => $question['hints'],
                'explanations' => $question['explanations'],
            ];
        }

        $this->seedQuestionData($items, $meta);
    }

    private function forms(string $subject, string $base, string $ing, string $pp): array
    {
        return [
            'subject' => $subject,
            'verb' => [
                'base' => $base,
                'ing' => $ing,
                'pp' => $pp,
            ],
        ];
    }

    private function buildAnswer(string $pattern, array $forms): string
    {
        $subject = $forms['subject'];
        $base = $forms['verb']['base'];
        $ing = $forms['verb']['ing'];
        $pp = $forms['verb']['pp'];

        return match ($pattern) {
            'future_simple_question' => "will {$subject} {$base}",
            'future_simple_negative' => "won't {$base}",
            'future_continuous_question' => "will {$subject} be {$ing}",
            'future_continuous_negative' => "won't be {$ing}",
            'future_perfect_question' => "will {$subject} have {$pp}",
            'future_perfect_negative' => "won't have {$pp}",
            'future_perfect_continuous_question' => "will {$subject} have been {$ing}",
            'future_perfect_continuous_negative' => "won't have been {$ing}",
            default => '',
        };
    }

    private function buildOptions(string $pattern, array $forms): array
    {
        $questions = $this->questionForms($forms);
        $negatives = $this->negativeForms($forms);

        return match ($pattern) {
            'future_simple_question' => [
                $questions['simple'],
                $questions['continuous'],
                $questions['perfect'],
                $questions['perfect_continuous'],
            ],
            'future_simple_negative' => [
                $negatives['simple'],
                $negatives['continuous'],
                $negatives['perfect'],
                $negatives['affirmative_simple'],
            ],
            'future_continuous_question' => [
                $questions['continuous'],
                $questions['simple'],
                $questions['perfect'],
                $questions['perfect_continuous'],
            ],
            'future_continuous_negative' => [
                $negatives['continuous'],
                $negatives['simple'],
                $negatives['perfect'],
                $negatives['affirmative_continuous'],
            ],
            'future_perfect_question' => [
                $questions['perfect'],
                $questions['perfect_continuous'],
                $questions['continuous'],
                $questions['simple'],
            ],
            'future_perfect_negative' => [
                $negatives['perfect'],
                $negatives['perfect_continuous'],
                $negatives['simple'],
                $negatives['affirmative_perfect'],
            ],
            'future_perfect_continuous_question' => [
                $questions['perfect_continuous'],
                $questions['perfect'],
                $questions['continuous'],
                $questions['simple'],
            ],
            'future_perfect_continuous_negative' => [
                $negatives['perfect_continuous'],
                $negatives['perfect'],
                $negatives['continuous'],
                $negatives['affirmative_perfect_continuous'],
            ],
            default => [],
        };
    }

    private function ensureOptions(array $options, string $answer, array $forms): array
    {
        $options = array_values(array_unique(array_map('trim', $options)));

        if (! in_array($answer, $options, true)) {
            array_unshift($options, $answer);
        }

        if (count($options) < 4) {
            $extra = array_values(array_unique(array_merge(
                $this->questionForms($forms),
                array_values($this->negativeForms($forms))
            )));

            foreach ($extra as $candidate) {
                if (! in_array($candidate, $options, true)) {
                    $options[] = $candidate;
                }

                if (count($options) >= 4) {
                    break;
                }
            }
        }

        if (count($options) > 4) {
            $result = [];
            if (($index = array_search($answer, $options, true)) !== false) {
                $result[] = $answer;
                unset($options[$index]);
            }

            foreach ($options as $option) {
                if (count($result) >= 4) {
                    break;
                }

                $result[] = $option;
            }

            $options = $result;
        }

        if (count($options) < 4) {
            $options = array_pad($options, 4, $answer);
            $options = array_values(array_unique($options));

            if (count($options) < 4) {
                $options[] = $answer;
            }
        }

        return $options;
    }

    private function buildHint(string $pattern, array $forms, array $config, string $example): string
    {
        $markers = $config['markers'];

        return match ($pattern) {
            'future_simple_question' => "Future Simple question = **will + subject + V1?**. Використовуємо для планів, рішень або обіцянок. Приклад: *{$example}*. Маркери: {$markers}.",
            'future_simple_negative' => "Future Simple negative = **subject + won't + V1**. Використовуємо, щоб показати відмову або відсутність наміру. Приклад: *{$example}*. Маркери: {$markers}.",
            'future_continuous_question' => "Future Continuous question = **will + subject + be + V-ing?**. Питаємо про дію, що буде в процесі у певний момент. Приклад: *{$example}*. Маркери: {$markers}.",
            'future_continuous_negative' => "Future Continuous negative = **subject + won't be + V-ing**. Використовуємо, коли дія не триватиме в конкретний момент. Приклад: *{$example}*. Маркери: {$markers}.",
            'future_perfect_question' => "Future Perfect question = **will + subject + have + V3?**. Питаємо про результат до певного майбутнього моменту. Приклад: *{$example}*. Маркери: {$markers}.",
            'future_perfect_negative' => "Future Perfect negative = **subject + won't have + V3**. Показуємо, що результату не буде до зазначеного часу. Приклад: *{$example}*. Маркери: {$markers}.",
            'future_perfect_continuous_question' => "Future Perfect Continuous question = **will + subject + have been + V-ing?**. Питаємо про тривалість дії до майбутнього моменту. Приклад: *{$example}*. Маркери: {$markers}.",
            'future_perfect_continuous_negative' => "Future Perfect Continuous negative = **subject + won't have been + V-ing**. Наголошуємо, що тривалість не досягне певної межі. Приклад: *{$example}*. Маркери: {$markers}.",
            default => '',
        };
    }

    private function buildExplanations(string $pattern, array $forms, array $options, string $answer, string $tenseLabel, string $example): array
    {
        $subjectPhrase = $this->subjectPhrase($forms['subject']);

        $explanations = [];
        foreach ($options as $option) {
            if ($option === $answer) {
                $explanations[$option] = $this->buildCorrectExplanation($pattern, $subjectPhrase, $tenseLabel, $example);
            } else {
                $explanations[$option] = $this->buildWrongExplanation($pattern, $forms, $option, $example);
            }
        }

        return $explanations;
    }

    private function buildCorrectExplanation(string $pattern, string $subjectPhrase, string $tenseLabel, string $example): string
    {
        return match ($pattern) {
            'future_simple_question' => "✅ Правильно: питальна форма {$tenseLabel} потребує конструкції will + {$subjectPhrase} + V1. Приклад: *{$example}*.",
            'future_simple_negative' => "✅ Вірно: заперечення у {$tenseLabel} будуємо як {$subjectPhrase} + won't + V1. Приклад: *{$example}*.",
            'future_continuous_question' => "✅ Саме так: питання у {$tenseLabel} має схему will + {$subjectPhrase} + be + V-ing. Приклад: *{$example}*.",
            'future_continuous_negative' => "✅ Добре: заперечення {$tenseLabel} = {$subjectPhrase} + won't be + V-ing. Приклад: *{$example}*.",
            'future_perfect_question' => "✅ Влучно: питання у {$tenseLabel} використовує will + {$subjectPhrase} + have + V3. Приклад: *{$example}*.",
            'future_perfect_negative' => "✅ Чудово: заперечна форма {$tenseLabel} це {$subjectPhrase} + won't have + V3. Приклад: *{$example}*.",
            'future_perfect_continuous_question' => "✅ Коректно: питання у {$tenseLabel} вимагає will + {$subjectPhrase} + have been + V-ing. Приклад: *{$example}*.",
            'future_perfect_continuous_negative' => "✅ Так: заперечення {$tenseLabel} складається з {$subjectPhrase} + won't have been + V-ing. Приклад: *{$example}*.",
            default => "✅ Правильна відповідь. Приклад: *{$example}*.",
        };
    }

    private function buildWrongExplanation(string $pattern, array $forms, string $option, string $example): string
    {
        $questions = $this->questionForms($forms);
        $negatives = $this->negativeForms($forms);

        return match ($pattern) {
            'future_simple_question' => $this->explainFutureSimpleQuestion($option, $questions, $example),
            'future_simple_negative' => $this->explainFutureSimpleNegative($option, $negatives, $example),
            'future_continuous_question' => $this->explainFutureContinuousQuestion($option, $questions, $example),
            'future_continuous_negative' => $this->explainFutureContinuousNegative($option, $negatives, $example),
            'future_perfect_question' => $this->explainFuturePerfectQuestion($option, $questions, $example),
            'future_perfect_negative' => $this->explainFuturePerfectNegative($option, $negatives, $example),
            'future_perfect_continuous_question' => $this->explainFuturePerfectContinuousQuestion($option, $questions, $example),
            'future_perfect_continuous_negative' => $this->explainFuturePerfectContinuousNegative($option, $negatives, $example),
            default => "❌ Неправильна форма. Правильний зразок: *{$example}*.",
        };
    }

    private function explainFutureSimpleQuestion(string $option, array $forms, string $example): string
    {
        if ($option === $forms['continuous']) {
            return "❌ Це Future Continuous, він описує процес, а потрібно просте питання у Future Simple. Правильний варіант: *{$example}*";
        }

        if ($option === $forms['perfect']) {
            return "❌ Це Future Perfect, який показує результат до певного моменту. Нам потрібна проста форма. Правильний варіант: *{$example}*";
        }

        if ($option === $forms['perfect_continuous']) {
            return "❌ Це Future Perfect Continuous і наголошує на тривалості. Для запитання слід обрати Future Simple. Приклад: *{$example}*";
        }

        return "❌ Невдала відповідь. Правильна форма: *{$example}*";
    }

    private function explainFutureSimpleNegative(string $option, array $forms, string $example): string
    {
        if ($option === $forms['continuous']) {
            return "❌ Ця форма Future Continuous показує процес, а нам потрібно коротке заперечення Future Simple. Правильний зразок: *{$example}*";
        }

        if ($option === $forms['perfect']) {
            return "❌ Це Future Perfect, який описує результат, а не просте заперечення. Правильний зразок: *{$example}*";
        }

        if ($option === $forms['affirmative_simple']) {
            return "❌ Відсутнє заперечення: форма без won't не підходить. Скористайся правильним прикладом: *{$example}*";
        }

        return "❌ Невірна форма. Дивись правильний приклад: *{$example}*";
    }

    private function explainFutureContinuousQuestion(string $option, array $forms, string $example): string
    {
        if ($option === $forms['simple']) {
            return "❌ Це Future Simple, а питання про процес потребує be + V-ing. Правильна форма: *{$example}*";
        }

        if ($option === $forms['perfect']) {
            return "❌ Це Future Perfect, який показує результат. Нам потрібно запитати про дію в процесі. Приклад: *{$example}*";
        }

        if ($option === $forms['perfect_continuous']) {
            return "❌ Це Future Perfect Continuous з тривалістю, а не просте питання про процес. Правильний варіант: *{$example}*";
        }

        return "❌ Невідповідна форма. Ось правильний зразок: *{$example}*";
    }

    private function explainFutureContinuousNegative(string $option, array $forms, string $example): string
    {
        if ($option === $forms['simple']) {
            return "❌ Це простий час, без be. Для Future Continuous потрібно won't be + V-ing. Правильний приклад: *{$example}*";
        }

        if ($option === $forms['perfect']) {
            return "❌ Це Future Perfect, який говорить про завершення, а не процес. Скористайся правильним зразком: *{$example}*";
        }

        if ($option === $forms['affirmative_continuous']) {
            return "❌ Тут немає not: щоб утворити заперечення, потрібне won't be. Правильна форма: *{$example}*";
        }

        return "❌ Така форма не підходить. Правильний приклад: *{$example}*";
    }

    private function explainFuturePerfectQuestion(string $option, array $forms, string $example): string
    {
        if ($option === $forms['perfect_continuous']) {
            return "❌ Це Future Perfect Continuous, а нам потрібно питання про результат, не тривалість. Правильний варіант: *{$example}*";
        }

        if ($option === $forms['continuous']) {
            return "❌ Це Future Continuous, який описує процес. Нам потрібен результат до моменту. Приклад: *{$example}*";
        }

        if ($option === $forms['simple']) {
            return "❌ Це Future Simple, без have + V3, тож не показує завершення. Правильна форма: *{$example}*";
        }

        return "❌ Інша форма не підходить. Подивись правильний зразок: *{$example}*";
    }

    private function explainFuturePerfectNegative(string $option, array $forms, string $example): string
    {
        if ($option === $forms['perfect_continuous']) {
            return "❌ Це Future Perfect Continuous, що зосереджується на тривалості, а не на відсутності результату. Правильний варіант: *{$example}*";
        }

        if ($option === $forms['simple']) {
            return "❌ Це Future Simple, без have + V3, тож не показує результат. Скористайся правильним прикладом: *{$example}*";
        }

        if ($option === $forms['affirmative_perfect']) {
            return "❌ Тут немає заперечення: форма без won't не підходить. Правильний зразок: *{$example}*";
        }

        return "❌ Форма некоректна. Правильна відповідь: *{$example}*";
    }

    private function explainFuturePerfectContinuousQuestion(string $option, array $forms, string $example): string
    {
        if ($option === $forms['perfect']) {
            return "❌ Це Future Perfect, він говорить лише про результат. Нам потрібна тривала дія. Правильна форма: *{$example}*";
        }

        if ($option === $forms['continuous']) {
            return "❌ Це Future Continuous, де немає have been, тож немає тривалості до моменту. Приклад: *{$example}*";
        }

        if ($option === $forms['simple']) {
            return "❌ Це Future Simple і зовсім не показує тривалий процес. Правильний варіант: *{$example}*";
        }

        return "❌ Форма не підходить. Правильний зразок: *{$example}*";
    }

    private function explainFuturePerfectContinuousNegative(string $option, array $forms, string $example): string
    {
        if ($option === $forms['perfect']) {
            return "❌ Це Future Perfect, а нам треба наголосити на тривалості з have been. Правильний приклад: *{$example}*";
        }

        if ($option === $forms['continuous']) {
            return "❌ Це Future Continuous, який не містить have been і не показує тривалий період до моменту. Приклад: *{$example}*";
        }

        if ($option === $forms['affirmative_perfect_continuous']) {
            return "❌ Тут немає заперечення: без won't форма не підходить. Скористайся правильним реченням: *{$example}*";
        }

        return "❌ Така форма не відповідає умові. Правильний зразок: *{$example}*";
    }

    private function questionForms(array $forms): array
    {
        $subject = $forms['subject'];
        $base = $forms['verb']['base'];
        $ing = $forms['verb']['ing'];
        $pp = $forms['verb']['pp'];

        return [
            'simple' => "will {$subject} {$base}",
            'continuous' => "will {$subject} be {$ing}",
            'perfect' => "will {$subject} have {$pp}",
            'perfect_continuous' => "will {$subject} have been {$ing}",
        ];
    }

    private function negativeForms(array $forms): array
    {
        $base = $forms['verb']['base'];
        $ing = $forms['verb']['ing'];
        $pp = $forms['verb']['pp'];

        return [
            'simple' => "won't {$base}",
            'continuous' => "won't be {$ing}",
            'perfect' => "won't have {$pp}",
            'perfect_continuous' => "won't have been {$ing}",
            'affirmative_simple' => "will {$base}",
            'affirmative_continuous' => "will be {$ing}",
            'affirmative_perfect' => "will have {$pp}",
            'affirmative_perfect_continuous' => "will have been {$ing}",
        ];
    }

    private function subjectPhrase(string $subject): string
    {
        $normalized = strtolower(trim($subject));

        return match ($normalized) {
            'i' => 'займенника «I»',
            'you' => 'займенника «you»',
            'we' => 'займенника «we»',
            'they' => 'займенника «they»',
            'he' => 'займенника «he»',
            'she' => 'займенника «she»',
            'it' => 'займенника «it»',
            default => 'підмета «' . trim($subject) . '»',
        };
    }
}
