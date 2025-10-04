<?php

namespace Database\Seeders\Ai;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class FirstConditionalChooseABCAiSeeder extends QuestionSeeder
{
    public function run(): void
    {  
        $categoryId = Category::firstOrCreate(['name' => 'First Conditional Comprehensive AI Test'])->id;

        $sectionSources = [
            'question_present' => Source::firstOrCreate(['name' => 'AI: First Conditional Questions — Present Context'])->id,
            'question_past' => Source::firstOrCreate(['name' => 'AI: First Conditional Questions — Past Context'])->id,
            'question_future' => Source::firstOrCreate(['name' => 'AI: First Conditional Questions — Future Context'])->id,
            'negative_present' => Source::firstOrCreate(['name' => 'AI: First Conditional Negatives — Present Context'])->id,
            'negative_past' => Source::firstOrCreate(['name' => 'AI: First Conditional Negatives — Past Context'])->id,
            'negative_future' => Source::firstOrCreate(['name' => 'AI: First Conditional Negatives — Future Context'])->id,
        ];

        $themeTags = [
            'question_present' => Tag::firstOrCreate(['name' => 'First Conditional Question Forms'], ['category' => 'English Grammar Theme'])->id,
            'question_past' => Tag::firstOrCreate(['name' => 'First Conditional Question Forms'], ['category' => 'English Grammar Theme'])->id,
            'question_future' => Tag::firstOrCreate(['name' => 'First Conditional Question Forms'], ['category' => 'English Grammar Theme'])->id,
            'negative_present' => Tag::firstOrCreate(['name' => 'First Conditional Negative Forms'], ['category' => 'English Grammar Theme'])->id,
            'negative_past' => Tag::firstOrCreate(['name' => 'First Conditional Negative Forms'], ['category' => 'English Grammar Theme'])->id,
            'negative_future' => Tag::firstOrCreate(['name' => 'First Conditional Negative Forms'], ['category' => 'English Grammar Theme'])->id,
        ];

        $detailTags = [
            'question_present_result' => Tag::firstOrCreate(['name' => 'Result Questions — Present Trigger'], ['category' => 'English Grammar Detail'])->id,
            'question_past_result' => Tag::firstOrCreate(['name' => 'Result Questions — Past Trigger'], ['category' => 'English Grammar Detail'])->id,
            'question_future_result' => Tag::firstOrCreate(['name' => 'Result Questions — Future Trigger'], ['category' => 'English Grammar Detail'])->id,
            'negative_present_condition' => Tag::firstOrCreate(['name' => 'If-Clause Negatives — Present Trigger'], ['category' => 'English Grammar Detail'])->id,
            'negative_present_result' => Tag::firstOrCreate(['name' => 'Result Negatives — Present Trigger'], ['category' => 'English Grammar Detail'])->id,
            'negative_past_condition' => Tag::firstOrCreate(['name' => 'If-Clause Negatives — Past Trigger'], ['category' => 'English Grammar Detail'])->id,
            'negative_past_result' => Tag::firstOrCreate(['name' => 'Result Negatives — Past Trigger'], ['category' => 'English Grammar Detail'])->id,
            'negative_future_condition' => Tag::firstOrCreate(['name' => 'If-Clause Negatives — Future Trigger'], ['category' => 'English Grammar Detail'])->id,
            'negative_future_result' => Tag::firstOrCreate(['name' => 'Result Negatives — Future Trigger'], ['category' => 'English Grammar Detail'])->id,
        ];

        $contextTags = [
            'present' => Tag::firstOrCreate(['name' => 'Present Time Reference'], ['category' => 'English Grammar Tense'])->id,
            'past' => Tag::firstOrCreate(['name' => 'Past Time Reference'], ['category' => 'English Grammar Tense'])->id,
            'future' => Tag::firstOrCreate(['name' => 'Future Time Reference'], ['category' => 'English Grammar Tense'])->id,
        ];

        $firstConditionalTag = Tag::firstOrCreate(['name' => 'First Conditional'], ['category' => 'English Grammar Tense'])->id;

        $structureTagId = Tag::firstOrCreate(['name' => 'First Conditional Clause Completion'], ['category' => 'English Grammar Structure'])->id;

        $patternConfig = [
            'question_present' => [
                'section' => 'question_present',
                'form' => 'question',
                'context' => 'present',
                'detail_key' => 'question_present_result',
                'markers' => 'today, this morning, right now',
            ],
            'question_past' => [
                'section' => 'question_past',
                'form' => 'question',
                'context' => 'past',
                'detail_key' => 'question_past_result',
                'markers' => 'yesterday, last night, earlier',
            ],
            'question_future' => [
                'section' => 'question_future',
                'form' => 'question',
                'context' => 'future',
                'detail_key' => 'question_future_result',
                'markers' => 'tomorrow, next week, soon',
            ],
            'negative_present' => [
                'section' => 'negative_present',
                'form' => 'negative',
                'context' => 'present',
                'markers' => 'today, this week, these days',
            ],
            'negative_past' => [
                'section' => 'negative_past',
                'form' => 'negative',
                'context' => 'past',
                'markers' => 'yesterday, last time, previously',
            ],
            'negative_future' => [
                'section' => 'negative_future',
                'form' => 'negative',
                'context' => 'future',
                'markers' => 'tomorrow, next month, later',
            ],
        ];

        $levelDifficulty = [
            'A1' => 1,
            'A2' => 2,
            'B1' => 3,
            'B2' => 4,
            'C1' => 5,
            'C2' => 5,
        ];

        $entries = [
            // A1
            $this->entry('A1', 'question_present', 'If Mia finishes her homework now, {a1} she watch cartoons?', 'will', ['will', 'does', 'would'], 'result', 'will + base verb — she', 'watch', 'she'),
            $this->entry('A1', 'question_present', 'If the bus arrives on time today, {a1} we leave for the zoo early?', 'will', ['will', 'are', 'shall'], 'result', 'will + base verb — we', 'leave', 'we'),
            $this->entry('A1', 'question_present', 'If you pack your bag before dinner, {a1} you join us for the game?', 'will', ['will', 'do', 'can'], 'result', 'will + base verb — you', 'join', 'you'),
            $this->entry('A1', 'question_present', 'If Leo finds his keys in the hall, {a1} he open the garage?', 'will', ['will', 'does', 'has'], 'result', 'will + base verb — he', 'open', 'he'),

            $this->entry('A1', 'question_past', 'If you remember yesterday\'s song, {a1} you sing it for us?', 'will', ['will', 'would', 'did'], 'result', 'will + base verb — you', 'sing', 'you'),
            $this->entry('A1', 'question_past', 'If Sara checks last night\'s homework, {a1} she explain the answer?', 'will', ['will', 'would', 'does'], 'result', 'will + base verb — she', 'explain', 'she'),
            $this->entry('A1', 'question_past', 'If the boys think about last weekend\'s game, {a1} they try a new tactic?', 'will', ['will', 'would', 'were'], 'result', 'will + base verb — they', 'try', 'they'),
            $this->entry('A1', 'question_past', 'If grandma finds the recipe from yesterday, {a1} she bake the pie again?', 'will', ['will', 'shall', 'did'], 'result', 'will + base verb — she', 'bake', 'she'),

            $this->entry('A1', 'question_future', 'If the weather stays warm tomorrow, {a1} we have a picnic?', 'will', ['will', 'would', 'are'], 'result', 'will + base verb — we', 'have', 'we'),
            $this->entry('A1', 'question_future', 'If you finish packing tonight, {a1} you leave with us in the morning?', 'will', ['will', 'can', 'do'], 'result', 'will + base verb — you', 'leave', 'you'),
            $this->entry('A1', 'question_future', 'If the coach calls later, {a1} he pick you up?', 'will', ['will', 'would', 'does'], 'result', 'will + base verb — he', 'pick', 'he'),
            $this->entry('A1', 'question_future', 'If Mia chooses the movie for tomorrow, {a1} we watch it together?', 'will', ['will', 'shall', 'are'], 'result', 'will + base verb — we', 'watch', 'we'),

            $this->entry('A1', 'negative_present', 'If Tom {a1} finish his chores, he won\'t get dessert.', "doesn't", ["doesn't", "don't", "didn't"], 'condition', 'does not + base verb — he', 'finish', 'he'),
            $this->entry('A1', 'negative_present', 'If we {a1} bring our tickets, the guard won\'t let us in.', "don't", ["don't", "doesn't", 'did'], 'condition', 'do not + base verb — we', 'bring', 'we'),
            $this->entry('A1', 'negative_present', 'If the soup tastes bland, we {a1} add more salt.', "won't", ["won't", 'will', 'would'], 'result', 'will not + base verb — we', 'add', 'we'),
            $this->entry('A1', 'negative_present', 'If the puppy chews the shoes again, dad {a1} buy new ones.', "won't", ["won't", 'will', 'can'], 'result', 'will not + base verb — he', 'buy', 'he'),

            $this->entry('A1', 'negative_past', 'If Lena {a1} clean yesterday\'s spill, the stain won\'t come out.', "doesn't", ["doesn't", "didn't", "don't"], 'condition', 'does not + base verb — she', 'clean', 'she'),
            $this->entry('A1', 'negative_past', 'If the twins {a1} finish last night\'s project, they won\'t present today.', "don't", ["don't", "didn't", "doesn't"], 'condition', 'do not + base verb — they', 'finish', 'they'),
            $this->entry('A1', 'negative_past', 'If you forget yesterday\'s promise, your friend {a1} trust you.', "won't", ["won't", 'will', 'would'], 'result', 'will not + base verb — they', 'trust', 'they'),
            $this->entry('A1', 'negative_past', 'If the coach repeats last week\'s mistake, the team {a1} win tonight.', "won't", ["won't", 'will', 'would'], 'result', 'will not + base verb — they', 'win', 'they'),

            $this->entry('A1', 'negative_future', 'If you {a1} set an alarm for tomorrow, you won\'t wake up on time.', "don't", ["don't", "won't", "didn't"], 'condition', 'do not + base verb — you', 'set', 'you'),
            $this->entry('A1', 'negative_future', 'If the kids {a1} pack their bags tonight, they won\'t join the trip.', "don't", ["don't", "won't", "didn't"], 'condition', 'do not + base verb — they', 'pack', 'they'),
            $this->entry('A1', 'negative_future', 'If the weather turns stormy tomorrow, we {a1} start the hike.', "won't", ["won't", 'will', 'would'], 'result', 'will not + base verb — we', 'start', 'we'),
            $this->entry('A1', 'negative_future', 'If Nora forgets to book the tickets tonight, they {a1} travel on Friday.', "won't", ["won't", 'will', 'shall'], 'result', 'will not + base verb — they', 'travel', 'they'),

            // A2
            $this->entry('A2', 'question_present', 'If Maria tidies her desk this afternoon, {a1} she start the new project?', 'will', ['will', 'does', 'would'], 'result', 'will + base verb — she', 'start', 'she'),
            $this->entry('A2', 'question_present', 'If the neighbours close their windows now, {a1} they keep the noise out?', 'will', ['will', 'would', 'are'], 'result', 'will + base verb — they', 'keep', 'they'),
            $this->entry('A2', 'question_present', 'If you organise the files before lunch, {a1} you send the report?', 'will', ['will', 'shall', 'do'], 'result', 'will + base verb — you', 'send', 'you'),
            $this->entry('A2', 'question_present', 'If the printer works again, {a1} we print the posters today?', 'will', ['will', 'would', 'are'], 'result', 'will + base verb — we', 'print', 'we'),

            $this->entry('A2', 'question_past', 'If Lena reviews yesterday\'s notes, {a1} she answer the tricky question?', 'will', ['will', 'would', 'did'], 'result', 'will + base verb — she', 'answer', 'she'),
            $this->entry('A2', 'question_past', 'If your brother recalls last night\'s conversation, {a1} he change his mind?', 'will', ['will', 'would', 'was'], 'result', 'will + base verb — he', 'change', 'he'),
            $this->entry('A2', 'question_past', 'If the volunteers check last week\'s list, {a1} they invite everyone?', 'will', ['will', 'would', 'were'], 'result', 'will + base verb — they', 'invite', 'they'),
            $this->entry('A2', 'question_past', 'If the chef finds last year\'s recipe, {a1} he cook the holiday dish?', 'will', ['will', 'would', 'has'], 'result', 'will + base verb — he', 'cook', 'he'),

            $this->entry('A2', 'question_future', 'If the ferry arrives early tomorrow, {a1} we board right away?', 'will', ['will', 'would', 'are'], 'result', 'will + base verb — we', 'board', 'we'),
            $this->entry('A2', 'question_future', 'If you finish the draft tonight, {a1} you send it to the client in the morning?', 'will', ['will', 'shall', 'do'], 'result', 'will + base verb — you', 'send', 'you'),
            $this->entry('A2', 'question_future', 'If the team secures funding next month, {a1} they expand the workshop?', 'will', ['will', 'would', 'are'], 'result', 'will + base verb — they', 'expand', 'they'),
            $this->entry('A2', 'question_future', 'If Mira books the hall for Saturday, {a1} we host the event there?', 'will', ['will', 'shall', 'would'], 'result', 'will + base verb — we', 'host', 'we'),

            $this->entry('A2', 'negative_present', 'If Daniel {a1} follow the instructions, the device won\'t start properly.', "doesn't", ["doesn't", "don't", "didn't"], 'condition', 'does not + base verb — he', 'follow', 'he'),
            $this->entry('A2', 'negative_present', 'If we {a1} water the garden regularly, the plants won\'t thrive.', "don't", ["don't", "doesn't", 'did'], 'condition', 'do not + base verb — we', 'water', 'we'),
            $this->entry('A2', 'negative_present', 'If the train is delayed again, we {a1} catch the opening ceremony.', "won't", ["won't", 'will', 'would'], 'result', 'will not + base verb — we', 'catch', 'we'),
            $this->entry(
                'A2',
                'negative_present',
                'If the café closes early tonight, they {a1} serve the late customers and the staff {a2} earn extra tips.',
                ['a1' => "won't", 'a2' => 'will not'],
                [
                    'a1' => ["won't", 'shall', 'could'],
                    'a2' => ['will not', 'might', 'would'],
                ],
                ['a1' => 'result', 'a2' => 'result'],
                ['a1' => 'will not + base verb — they', 'a2' => 'will not + base verb — they (staff)'],
                ['a1' => 'serve', 'a2' => 'earn'],
                ['a1' => 'they', 'a2' => 'they (the staff)']
            ),

            $this->entry('A2', 'negative_past', 'If Emma {a1} review last week\'s grammar, she won\'t feel confident.', "doesn't", ["doesn't", "didn't", "don't"], 'condition', 'does not + base verb — she', 'review', 'she'),
            $this->entry('A2', 'negative_past', 'If the players {a1} learn from yesterday\'s loss, they won\'t improve.', "don't", ["don't", "didn't", "doesn't"], 'condition', 'do not + base verb — they', 'learn', 'they'),
            $this->entry('A2', 'negative_past', 'If you ignore yesterday\'s warning, the manager {a1} trust your judgement.', "won't", ["won't", 'will', 'would'], 'result', 'will not + base verb — they', 'trust', 'they'),
            $this->entry(
                'A2',
                'negative_past',
                'If the reporter repeats last year\'s rumour, the audience {a1} believe the news and sponsors {a2} renew their support.',
                ['a1' => "won't", 'a2' => 'will not'],
                [
                    'a1' => ["won't", 'shall', 'could'],
                    'a2' => ['will not', 'might', 'would'],
                ],
                ['a1' => 'result', 'a2' => 'result'],
                ['a1' => 'will not + base verb — they', 'a2' => 'will not + base verb — they (sponsors)'],
                ['a1' => 'believe', 'a2' => 'renew'],
                ['a1' => 'they', 'a2' => 'they (the sponsors)']
            ),

            $this->entry('A2', 'negative_future', 'If you {a1} confirm the booking tonight, the venue won\'t hold the date.', "don't", ["don't", "won't", "didn't"], 'condition', 'do not + base verb — you', 'confirm', 'you'),
            $this->entry('A2', 'negative_future', 'If the interns {a1} submit their forms tomorrow, they won\'t join the trip.', "don't", ["don't", "won't", "didn't"], 'condition', 'do not + base verb — they', 'submit', 'they'),
            $this->entry('A2', 'negative_future', 'If the weather turns stormy next weekend, we {a1} set up the stage outside.', "won't", ["won't", 'will', 'would'], 'result', 'will not + base verb — we', 'set', 'we'),
            $this->entry(
                'A2',
                'negative_future',
                'If Nora forgets to charge the cameras tonight, they {a1} record the ceremony and the crew {a2} capture the speeches.',
                ['a1' => "won't", 'a2' => 'will not'],
                [
                    'a1' => ["won't", 'shall', 'could'],
                    'a2' => ['will not', 'might', 'would'],
                ],
                ['a1' => 'result', 'a2' => 'result'],
                ['a1' => 'will not + base verb — they', 'a2' => 'will not + base verb — the crew'],
                ['a1' => 'record', 'a2' => 'capture'],
                ['a1' => 'they', 'a2' => 'they (the crew)']
            ),

            // B1
            $this->entry('B1', 'question_present', 'If the marketing team finalises the budget today, {a1} they launch the campaign?', 'will', ['will', 'would', 'are'], 'result', 'will + base verb — they', 'launch', 'they'),
            $this->entry('B1', 'question_present', 'If you update the spreadsheet before noon, {a1} you share it with finance?', 'will', ['will', 'shall', 'do'], 'result', 'will + base verb — you', 'share', 'you'),
            $this->entry('B1', 'question_present', 'If Carla arranges the meeting room now, {a1} she invite the partners?', 'will', ['will', 'does', 'would'], 'result', 'will + base verb — she', 'invite', 'she'),
            $this->entry('B1', 'question_present', 'If the students submit their surveys this morning, {a1} we analyse the results today?', 'will', ['will', 'would', 'are'], 'result', 'will + base verb — we', 'analyse', 'we'),

            $this->entry('B1', 'question_past', 'If the analysts review last quarter\'s data, {a1} they present new conclusions?', 'will', ['will', 'would', 'were'], 'result', 'will + base verb — they', 'present', 'they'),
            $this->entry('B1', 'question_past', 'If your mentor remembers yesterday\'s discussion, {a1} he adjust the plan?', 'will', ['will', 'would', 'was'], 'result', 'will + base verb — he', 'adjust', 'he'),
            $this->entry('B1', 'question_past', 'If the committee rereads last year\'s report, {a1} it adopt the recommendations?', 'will', ['will', 'would', 'did'], 'result', 'will + base verb — it', 'adopt', 'it'),
            $this->entry('B1', 'question_past', 'If the lawyer recalls the previous case, {a1} she use it in court?', 'will', ['will', 'would', 'did'], 'result', 'will + base verb — she', 'use', 'she'),

            $this->entry('B1', 'question_future', 'If the supplier ships the parts next week, {a1} we begin assembly on Monday?', 'will', ['will', 'would', 'are'], 'result', 'will + base verb — we', 'begin', 'we'),
            $this->entry('B1', 'question_future', 'If you complete the proposal tonight, {a1} you present it tomorrow?', 'will', ['will', 'shall', 'do'], 'result', 'will + base verb — you', 'present', 'you'),
            $this->entry('B1', 'question_future', 'If the city approves the permit next month, {a1} they open the new park?', 'will', ['will', 'would', 'are'], 'result', 'will + base verb — they', 'open', 'they'),
            $this->entry('B1', 'question_future', 'If Jared books the flights this evening, {a1} we travel together on Friday?', 'will', ['will', 'would', 'shall'], 'result', 'will + base verb — we', 'travel', 'we'),

            $this->entry('B1', 'negative_present', 'If the engineer {a1} test the update, the app won\'t run smoothly.', "doesn't", ["doesn't", "don't", "didn't"], 'condition', 'does not + base verb — he', 'test', 'he'),
            $this->entry('B1', 'negative_present', 'If we {a1} follow the brief precisely, the client won\'t approve it.', "don't", ["don't", "doesn't", 'did'], 'condition', 'do not + base verb — we', 'follow', 'we'),
            $this->entry('B1', 'negative_present', 'If the courier arrives late again, we {a1} deliver on schedule.', "won't", ["won't", 'will', 'would'], 'result', 'will not + base verb — we', 'deliver', 'we'),
            $this->entry(
                'B1',
                'negative_present',
                'If the venue raises the price, they {a1} attract many bookings and local organisers {a2} book weekend slots.',
                ['a1' => "won't", 'a2' => 'will not'],
                [
                    'a1' => ["won't", 'shall', 'could'],
                    'a2' => ['will not', 'might', 'would'],
                ],
                ['a1' => 'result', 'a2' => 'result'],
                ['a1' => 'will not + base verb — they', 'a2' => 'will not + base verb — they (organisers)'],
                ['a1' => 'attract', 'a2' => 'book'],
                ['a1' => 'they', 'a2' => 'they (organisers)']
            ),

            $this->entry('B1', 'negative_past', 'If Morgan {a1} review last week\'s draft, she won\'t spot the typo.', "doesn't", ["doesn't", "didn't", "don't"], 'condition', 'does not + base verb — she', 'review', 'she'),
            $this->entry('B1', 'negative_past', 'If the trainees {a1} practise after yesterday\'s feedback, they won\'t improve.', "don't", ["don't", "didn't", "doesn't"], 'condition', 'do not + base verb — they', 'practise', 'they'),
            $this->entry('B1', 'negative_past', 'If you ignore last year\'s audit, the board {a1} trust your forecast.', "won't", ["won't", 'will', 'would'], 'result', 'will not + base verb — they', 'trust', 'they'),
            $this->entry(
                'B1',
                'negative_past',
                'If the editor repeats last season\'s rumour, readers {a1} believe the article and advertisers {a2} renew their contracts.',
                ['a1' => "won't", 'a2' => 'will not'],
                [
                    'a1' => ["won't", 'shall', 'could'],
                    'a2' => ['will not', 'might', 'would'],
                ],
                ['a1' => 'result', 'a2' => 'result'],
                ['a1' => 'will not + base verb — they', 'a2' => 'will not + base verb — they (advertisers)'],
                ['a1' => 'believe', 'a2' => 'renew'],
                ['a1' => 'they', 'a2' => 'they (advertisers)']
            ),

            $this->entry('B1', 'negative_future', 'If you {a1} secure the sponsorship by Friday, the festival won\'t cover costs.', "don't", ["don't", "won't", "didn't"], 'condition', 'do not + base verb — you', 'secure', 'you'),
            $this->entry('B1', 'negative_future', 'If the designers {a1} submit concepts tomorrow, they won\'t join the shortlist.', "don't", ["don't", "won't", "didn't"], 'condition', 'do not + base verb — they', 'submit', 'they'),
            $this->entry('B1', 'negative_future', 'If the forecast predicts storms next week, we {a1} schedule outdoor sessions.', "won't", ["won't", 'will', 'would'], 'result', 'will not + base verb — we', 'schedule', 'we'),
            $this->entry(
                'B1',
                'negative_future',
                'If Nadia forgets to lock the lab tonight, they {a1} keep sensitive data safe and the team {a2} trust the storage.',
                ['a1' => "won't", 'a2' => 'will not'],
                [
                    'a1' => ["won't", 'shall', 'could'],
                    'a2' => ['will not', 'might', 'would'],
                ],
                ['a1' => 'result', 'a2' => 'result'],
                ['a1' => 'will not + base verb — they', 'a2' => 'will not + base verb — the team'],
                ['a1' => 'keep', 'a2' => 'trust'],
                ['a1' => 'they', 'a2' => 'they (the team)']
            ),

            // B2
            $this->entry('B2', 'question_present', 'If the board approves the revised timeline today, {a1} they announce the merger?', 'will', ['will', 'would', 'are'], 'result', 'will + base verb — they', 'announce', 'they'),
            $this->entry('B2', 'question_present', 'If you reconcile the figures before lunch, {a1} you forward the summary to stakeholders?', 'will', ['will', 'shall', 'do'], 'result', 'will + base verb — you', 'forward', 'you'),
            $this->entry('B2', 'question_present', 'If Elena coordinates the mentors this morning, {a1} she invite the scholarship winners?', 'will', ['will', 'does', 'would'], 'result', 'will + base verb — she', 'invite', 'she'),
            $this->entry('B2', 'question_present', 'If the consultants validate the model now, {a1} we implement the new policy?', 'will', ['will', 'would', 'are'], 'result', 'will + base verb — we', 'implement', 'we'),

            $this->entry('B2', 'question_past', 'If the auditors reassess last year\'s discrepancies, {a1} they propose sanctions?', 'will', ['will', 'would', 'were'], 'result', 'will + base verb — they', 'propose', 'they'),
            $this->entry('B2', 'question_past', 'If your lead remembers yesterday\'s negotiation, {a1} he reopen the offer?', 'will', ['will', 'would', 'was'], 'result', 'will + base verb — he', 'reopen', 'he'),
            $this->entry('B2', 'question_past', 'If the task force reviews last quarter\'s failures, {a1} it adjust the rollout?', 'will', ['will', 'would', 'did'], 'result', 'will + base verb — it', 'adjust', 'it'),
            $this->entry('B2', 'question_past', 'If the researcher revisits previous trials, {a1} she publish new findings?', 'will', ['will', 'would', 'did'], 'result', 'will + base verb — she', 'publish', 'she'),

            $this->entry('B2', 'question_future', 'If the investors sign next week, {a1} we expand into the new region?', 'will', ['will', 'would', 'are'], 'result', 'will + base verb — we', 'expand', 'we'),
            $this->entry('B2', 'question_future', 'If you deliver the prototype tomorrow, {a1} you demonstrate it at the expo?', 'will', ['will', 'shall', 'do'], 'result', 'will + base verb — you', 'demonstrate', 'you'),
            $this->entry('B2', 'question_future', 'If the agency approves the grant next month, {a1} they sponsor additional labs?', 'will', ['will', 'would', 'are'], 'result', 'will + base verb — they', 'sponsor', 'they'),
            $this->entry('B2', 'question_future', 'If Victor locks in the keynote speaker tonight, {a1} we open registration on Monday?', 'will', ['will', 'would', 'shall'], 'result', 'will + base verb — we', 'open', 'we'),

            $this->entry('B2', 'negative_present', 'If the architect {a1} check the structural report, the council won\'t issue permits.', "doesn't", ["doesn't", "don't", "didn't"], 'condition', 'does not + base verb — he', 'check', 'he'),
            $this->entry('B2', 'negative_present', 'If we {a1} monitor compliance closely, regulators won\'t trust our audit.', "don't", ["don't", "doesn't", 'did'], 'condition', 'do not + base verb — we', 'monitor', 'we'),
            $this->entry('B2', 'negative_present', 'If the supplier misses today\'s cut-off, we {a1} meet the production target.', "won't", ["won't", 'will', 'would'], 'result', 'will not + base verb — we', 'meet', 'we'),
            $this->entry(
                'B2',
                'negative_present',
                'If the director delays the approval, the team {a1} launch the beta and investors {a2} increase their confidence.',
                ['a1' => "won't", 'a2' => 'will not'],
                [
                    'a1' => ["won't", 'shall', 'could'],
                    'a2' => ['will not', 'might', 'would'],
                ],
                ['a1' => 'result', 'a2' => 'result'],
                ['a1' => 'will not + base verb — they', 'a2' => 'will not + base verb — they (investors)'],
                ['a1' => 'launch', 'a2' => 'increase'],
                ['a1' => 'they', 'a2' => 'they (investors)']
            ),

            $this->entry('B2', 'negative_past', 'If Marisa {a1} document last week\'s incident, she won\'t defend her decision.', "doesn't", ["doesn't", "didn't", "don't"], 'condition', 'does not + base verb — she', 'document', 'she'),
            $this->entry('B2', 'negative_past', 'If the analysts {a1} study yesterday\'s anomaly, they won\'t solve the pattern.', "don't", ["don't", "didn't", "doesn't"], 'condition', 'do not + base verb — they', 'study', 'they'),
            $this->entry('B2', 'negative_past', 'If you dismiss last year\'s forecast, shareholders {a1} support your strategy.', "won't", ["won't", 'will', 'would'], 'result', 'will not + base verb — they', 'support', 'they'),
            $this->entry(
                'B2',
                'negative_past',
                'If the spokesperson repeats last season\'s rumour, journalists {a1} trust the briefing and donors {a2} renew their backing.',
                ['a1' => "won't", 'a2' => 'will not'],
                [
                    'a1' => ["won't", 'shall', 'could'],
                    'a2' => ['will not', 'might', 'would'],
                ],
                ['a1' => 'result', 'a2' => 'result'],
                ['a1' => 'will not + base verb — they', 'a2' => 'will not + base verb — they (donors)'],
                ['a1' => 'trust', 'a2' => 'renew'],
                ['a1' => 'they', 'a2' => 'they (donors)']
            ),

            $this->entry('B2', 'negative_future', 'If you {a1} secure the regulatory sign-off this week, the release won\'t happen.', "don't", ["don't", "won't", "didn't"], 'condition', 'do not + base verb — you', 'secure', 'you'),
            $this->entry('B2', 'negative_future', 'If the developers {a1} submit patches tomorrow, they won\'t join the deployment.', "don't", ["don't", "won't", "didn't"], 'condition', 'do not + base verb — they', 'submit', 'they'),
            $this->entry('B2', 'negative_future', 'If the forecast warns of outages next month, we {a1} schedule the launch.', "won't", ["won't", 'will', 'would'], 'result', 'will not + base verb — we', 'schedule', 'we'),
            $this->entry(
                'B2',
                'negative_future',
                'If Helena forgets to brief the press tonight, they {a1} cover the announcement and the audience {a2} tune in live.',
                ['a1' => "won't", 'a2' => 'will not'],
                [
                    'a1' => ["won't", 'shall', 'could'],
                    'a2' => ['will not', 'might', 'would'],
                ],
                ['a1' => 'result', 'a2' => 'result'],
                ['a1' => 'will not + base verb — they', 'a2' => 'will not + base verb — the audience'],
                ['a1' => 'cover', 'a2' => 'tune'],
                ['a1' => 'they', 'a2' => 'they (the audience)']
            ),

            // C1
            $this->entry('C1', 'question_present', 'If the negotiations conclude this morning, {a1} they unveil the joint statement?', 'will', ['will', 'would', 'are'], 'result', 'will + base verb — they', 'unveil', 'they'),
            $this->entry('C1', 'question_present', 'If you consolidate the stakeholder feedback now, {a1} you circulate a revised draft?', 'will', ['will', 'shall', 'do'], 'result', 'will + base verb — you', 'circulate', 'you'),
            $this->entry('C1', 'question_present', 'If Helena moderates the panel today, {a1} she invite dissenting voices?', 'will', ['will', 'does', 'would'], 'result', 'will + base verb — she', 'invite', 'she'),
            $this->entry('C1', 'question_present', 'If the committee tallies the votes immediately, {a1} we announce the decision publicly?', 'will', ['will', 'would', 'are'], 'result', 'will + base verb — we', 'announce', 'we'),

            $this->entry('C1', 'question_past', 'If the diplomats revisited last year\'s accord, {a1} they propose amendments?', 'will', ['will', 'would', 'were'], 'result', 'will + base verb — they', 'propose', 'they'),
            $this->entry('C1', 'question_past', 'If your advisor recalled yesterday\'s briefing, {a1} he adjust the strategy?', 'will', ['will', 'would', 'was'], 'result', 'will + base verb — he', 'adjust', 'he'),
            $this->entry('C1', 'question_past', 'If the panel reviewed prior case law, {a1} it endorse the precedent?', 'will', ['will', 'would', 'did'], 'result', 'will + base verb — it', 'endorse', 'it'),
            $this->entry('C1', 'question_past', 'If the scientist reexamined previous samples, {a1} she publish a correction?', 'will', ['will', 'would', 'did'], 'result', 'will + base verb — she', 'publish', 'she'),

            $this->entry('C1', 'question_future', 'If the parliament votes next week, {a1} we implement the new mandate?', 'will', ['will', 'would', 'are'], 'result', 'will + base verb — we', 'implement', 'we'),
            $this->entry('C1', 'question_future', 'If you deliver the policy memo tomorrow, {a1} you brief the cabinet on Friday?', 'will', ['will', 'shall', 'do'], 'result', 'will + base verb — you', 'brief', 'you'),
            $this->entry('C1', 'question_future', 'If the consortium secures funding next quarter, {a1} they expand the research hub?', 'will', ['will', 'would', 'are'], 'result', 'will + base verb — they', 'expand', 'they'),
            $this->entry('C1', 'question_future', 'If Marcus confirms the keynote tonight, {a1} we broadcast the agenda in the morning?', 'will', ['will', 'would', 'shall'], 'result', 'will + base verb — we', 'broadcast', 'we'),

            $this->entry('C1', 'negative_present', 'If the chairperson {a1} enforce the guidelines, the forum won\'t maintain order.', "doesn't", ["doesn't", "don't", "didn't"], 'condition', 'does not + base verb — she', 'enforce', 'she'),
            $this->entry('C1', 'negative_present', 'If we {a1} scrutinise the data carefully, regulators won\'t accept the audit.', "don't", ["don't", "doesn't", 'did'], 'condition', 'do not + base verb — we', 'scrutinise', 'we'),
            $this->entry('C1', 'negative_present', 'If the syndicate misses today\'s deadline, we {a1} secure investor confidence.', "won't", ["won't", 'will', 'would'], 'result', 'will not + base verb — we', 'secure', 'we'),
            $this->entry(
                'C1',
                'negative_present',
                'If the curator delays the catalogue, the gallery {a1} draw international visitors and patrons {a2} pledge new donations.',
                ['a1' => "won't", 'a2' => 'will not'],
                [
                    'a1' => ["won't", 'shall', 'could'],
                    'a2' => ['will not', 'might', 'would'],
                ],
                ['a1' => 'result', 'a2' => 'result'],
                ['a1' => 'will not + base verb — they', 'a2' => 'will not + base verb — they (patrons)'],
                ['a1' => 'draw', 'a2' => 'pledge'],
                ['a1' => 'they', 'a2' => 'they (patrons)']
            ),

            $this->entry('C1', 'negative_past', 'If Martina {a1} archive last week\'s testimony, she won\'t defend the committee\'s stance.', "doesn't", ["doesn't", "didn't", "don't"], 'condition', 'does not + base verb — she', 'archive', 'she'),
            $this->entry('C1', 'negative_past', 'If the negotiators {a1} process yesterday\'s feedback, they won\'t adjust concessions.', "don't", ["don't", "didn't", "doesn't"], 'condition', 'do not + base verb — they', 'process', 'they'),
            $this->entry('C1', 'negative_past', 'If you dismiss last year\'s audit, the ministry {a1} renew the license.', "won't", ["won't", 'will', 'would'], 'result', 'will not + base verb — they', 'renew', 'they'),
            $this->entry(
                'C1',
                'negative_past',
                'If the columnist repeats prior allegations, readers {a1} accept the clarification and sponsors {a2} renew their commitment.',
                ['a1' => "won't", 'a2' => 'will not'],
                [
                    'a1' => ["won't", 'shall', 'could'],
                    'a2' => ['will not', 'might', 'would'],
                ],
                ['a1' => 'result', 'a2' => 'result'],
                ['a1' => 'will not + base verb — they', 'a2' => 'will not + base verb — they (sponsors)'],
                ['a1' => 'accept', 'a2' => 'renew'],
                ['a1' => 'they', 'a2' => 'they (sponsors)']
            ),

            $this->entry('C1', 'negative_future', 'If you {a1} finalise the compliance file tomorrow, the regulator won\'t sign off.', "don't", ["don't", "won't", "didn't"], 'condition', 'do not + base verb — you', 'finalise', 'you'),
            $this->entry('C1', 'negative_future', 'If the delegates {a1} submit amendments next week, they won\'t influence the resolution.', "don't", ["don't", "won't", "didn't"], 'condition', 'do not + base verb — they', 'submit', 'they'),
            $this->entry('C1', 'negative_future', 'If the forecast signals disruptions next quarter, we {a1} deploy the new platform.', "won't", ["won't", 'will', 'would'], 'result', 'will not + base verb — we', 'deploy', 'we'),
            $this->entry(
                'C1',
                'negative_future',
                'If Lila forgets to brief the ambassadors tonight, they {a1} endorse the proposal and regional partners {a2} align with the plan.',
                ['a1' => "won't", 'a2' => 'will not'],
                [
                    'a1' => ["won't", 'shall', 'could'],
                    'a2' => ['will not', 'might', 'would'],
                ],
                ['a1' => 'result', 'a2' => 'result'],
                ['a1' => 'will not + base verb — they', 'a2' => 'will not + base verb — they (partners)'],
                ['a1' => 'endorse', 'a2' => 'align'],
                ['a1' => 'they', 'a2' => 'they (partners)']
            ),

            // C2
            $this->entry('C2', 'question_present', 'If the commission aligns the draft this morning, {a1} they ratify the emergency protocol?', 'will', ['will', 'would', 'are'], 'result', 'will + base verb — they', 'ratify', 'they'),
            $this->entry('C2', 'question_present', 'If you synthesise the testimonies now, {a1} you publish an interim finding?', 'will', ['will', 'shall', 'do'], 'result', 'will + base verb — you', 'publish', 'you'),
            $this->entry('C2', 'question_present', 'If Dr. Novak convenes the advisory board today, {a1} she invite dissenting scholars?', 'will', ['will', 'does', 'would'], 'result', 'will + base verb — she', 'invite', 'she'),
            $this->entry('C2', 'question_present', 'If the tribunal tallies the objections immediately, {a1} we release the ruling at noon?', 'will', ['will', 'would', 'are'], 'result', 'will + base verb — we', 'release', 'we'),

            $this->entry('C2', 'question_past', 'If the envoys revisited last decade\'s accord, {a1} they propose a new framework?', 'will', ['will', 'would', 'were'], 'result', 'will + base verb — they', 'propose', 'they'),
            $this->entry('C2', 'question_past', 'If your counsel recalled yesterday\'s deposition, {a1} he challenge the clause?', 'will', ['will', 'would', 'was'], 'result', 'will + base verb — he', 'challenge', 'he'),
            $this->entry('C2', 'question_past', 'If the review board analysed prior jurisprudence, {a1} it endorse the sanction?', 'will', ['will', 'would', 'did'], 'result', 'will + base verb — it', 'endorse', 'it'),
            $this->entry('C2', 'question_past', 'If the researcher reevaluated archived datasets, {a1} she publish a retraction?', 'will', ['will', 'would', 'did'], 'result', 'will + base verb — she', 'publish', 'she'),

            $this->entry('C2', 'question_future', 'If the legislature passes the motion next week, {a1} we enforce the revised charter?', 'will', ['will', 'would', 'are'], 'result', 'will + base verb — we', 'enforce', 'we'),
            $this->entry('C2', 'question_future', 'If you deliver the white paper tomorrow, {a1} you brief the senate committee on Monday?', 'will', ['will', 'shall', 'do'], 'result', 'will + base verb — you', 'brief', 'you'),
            $this->entry('C2', 'question_future', 'If the foundation secures endowment funding next quarter, {a1} they establish satellite centres?', 'will', ['will', 'would', 'are'], 'result', 'will + base verb — they', 'establish', 'they'),
            $this->entry('C2', 'question_future', 'If Elias confirms the international delegation tonight, {a1} we broadcast the summit schedule?', 'will', ['will', 'would', 'shall'], 'result', 'will + base verb — we', 'broadcast', 'we'),

            $this->entry('C2', 'negative_present', 'If the chair {a1} enforce procedural decorum, the chamber won\'t retain credibility.', "doesn't", ["doesn't", "don't", "didn't"], 'condition', 'does not + base verb — she', 'enforce', 'she'),
            $this->entry('C2', 'negative_present', 'If we {a1} interrogate the anomalies thoroughly, auditors won\'t accept the ledger.', "don't", ["don't", "doesn't", 'did'], 'condition', 'do not + base verb — we', 'interrogate', 'we'),
            $this->entry('C2', 'negative_present', 'If the syndicate misses today\'s compliance filing, we {a1} regain regulatory trust.', "won't", ["won't", 'will', 'would'], 'result', 'will not + base verb — we', 'regain', 'we'),
            $this->entry(
                'C2',
                'negative_present',
                'If the curator withholds the catalogue, the museum {a1} attract international scholars and benefactors {a2} pledge endowments.',
                ['a1' => "won't", 'a2' => 'will not'],
                [
                    'a1' => ["won't", 'shall', 'could'],
                    'a2' => ['will not', 'might', 'would'],
                ],
                ['a1' => 'result', 'a2' => 'result'],
                ['a1' => 'will not + base verb — they', 'a2' => 'will not + base verb — they (benefactors)'],
                ['a1' => 'attract', 'a2' => 'pledge'],
                ['a1' => 'they', 'a2' => 'they (benefactors)']
            ),

            $this->entry('C2', 'negative_past', 'If Mirella {a1} archive last week\'s testimony transcripts, she won\'t justify the verdict.', "doesn't", ["doesn't", "didn't", "don't"], 'condition', 'does not + base verb — she', 'archive', 'she'),
            $this->entry('C2', 'negative_past', 'If the mediators {a1} digest yesterday\'s concessions, they won\'t alter their mandate.', "don't", ["don't", "didn't", "doesn't"], 'condition', 'do not + base verb — they', 'digest', 'they'),
            $this->entry('C2', 'negative_past', 'If you disregard last year\'s injunction, the court {a1} approve your appeal.', "won't", ["won't", 'will', 'would'], 'result', 'will not + base verb — they', 'approve', 'they'),
            $this->entry(
                'C2',
                'negative_past',
                'If the commentator recycles prior accusations, viewers {a1} trust the analysis and sponsors {a2} renew their grants.',
                ['a1' => "won't", 'a2' => 'will not'],
                [
                    'a1' => ["won't", 'shall', 'could'],
                    'a2' => ['will not', 'might', 'would'],
                ],
                ['a1' => 'result', 'a2' => 'result'],
                ['a1' => 'will not + base verb — they', 'a2' => 'will not + base verb — they (sponsors)'],
                ['a1' => 'trust', 'a2' => 'renew'],
                ['a1' => 'they', 'a2' => 'they (sponsors)']
            ),

            $this->entry('C2', 'negative_future', 'If you {a1} finalise the compliance annex tomorrow, the regulator won\'t clear the case.', "don't", ["don't", "won't", "didn't"], 'condition', 'do not + base verb — you', 'finalise', 'you'),
            $this->entry('C2', 'negative_future', 'If the delegates {a1} submit revisions next session, they won\'t influence the treaty.', "don't", ["don't", "won't", "didn't"], 'condition', 'do not + base verb — they', 'submit', 'they'),
            $this->entry('C2', 'negative_future', 'If the forecast signals supply shocks next quarter, we {a1} authorise the rollout.', "won't", ["won't", 'will', 'would'], 'result', 'will not + base verb — we', 'authorise', 'we'),
            $this->entry(
                'C2',
                'negative_future',
                'If Amara forgets to brief the coalition tonight, they {a1} endorse the motion and regional allies {a2} align with the proposal.',
                ['a1' => "won't", 'a2' => 'will not'],
                [
                    'a1' => ["won't", 'shall', 'could'],
                    'a2' => ['will not', 'might', 'would'],
                ],
                ['a1' => 'result', 'a2' => 'result'],
                ['a1' => 'will not + base verb — they', 'a2' => 'will not + base verb — they (allies)'],
                ['a1' => 'endorse', 'a2' => 'align'],
                ['a1' => 'they', 'a2' => 'they (allies)']
            ),
        ];

        $rawQuestions = [];
        foreach ($entries as $entry) {
            $config = $patternConfig[$entry['pattern']];
            $answers = $entry['answers'];
            $optionSets = $this->normalizeOptionSets($entry['options'], $answers);

            $detailKey = $config['detail_key'] ?? null;
            if ($config['form'] === 'negative') {
                $primaryClause = $entry['clauses']['a1'] ?? reset($entry['clauses']);
                $detailKey = match ($primaryClause) {
                    'condition' => ($config['context'] === 'present' ? 'negative_present_condition' : ($config['context'] === 'past' ? 'negative_past_condition' : 'negative_future_condition')),
                    'result' => ($config['context'] === 'present' ? 'negative_present_result' : ($config['context'] === 'past' ? 'negative_past_result' : 'negative_future_result')),
                    default => $detailKey,
                };
            }

            $wrappedVerbHints = [];
            foreach ($entry['verb_hint'] as $marker => $hint) {
                $wrappedVerbHints[$marker] = '(' . $hint . ')';
            }

            $rawQuestions[] = [
                'section' => $config['section'],
                'detail_key' => $detailKey,
                'question' => $entry['question'],
                'verb_hint' => $wrappedVerbHints,
                'option_sets' => $optionSets,
                'answers' => $answers,
                'clauses' => $entry['clauses'],
                'verb_base' => $entry['verb_base'],
                'subject_hint' => $entry['subject_hint'],
                'level' => $entry['level'],
            ];
        }

        $items = [];
        $meta = [];

        foreach ($rawQuestions as $index => $question) {
            $sectionKey = $question['section'];
            $config = $patternConfig[$sectionKey] ?? null;

            $uuid = $this->generateQuestionUuid($sectionKey, $question['level'], $index);

            $answers = [];
            $optionMarkerMap = [];
            foreach ($question['option_sets'] as $marker => $options) {
                foreach ($options as $option) {
                    if (! isset($optionMarkerMap[$option])) {
                        $optionMarkerMap[$option] = $marker;
                    }
                }
            }

            foreach ($question['answers'] as $marker => $answer) {
                $answers[] = [
                    'marker' => $marker,
                    'answer' => $answer,
                    'verb_hint' => $this->sanitizeVerbHint($question, $marker),
                ];
                $optionMarkerMap[$answer] = $marker;
            }

            $example = $this->formatExample($question['question'], $question['answers']);

            $hints = [];
            foreach ($question['answers'] as $marker => $answer) {
                $hints[$marker] = $this->buildHintForMarker($question, $config, $example, $marker);
            }

            $explanations = $this->buildExplanationsForQuestion($question, $config, $example);

            $tagIds = [$firstConditionalTag];

            if ($config !== null) {
                $tagIds[] = $contextTags[$config['context']] ?? null;
                $tagIds[] = $themeTags[$config['section']] ?? null;
            }

            $detailKey = $question['detail_key'] ?? null;
            if ($detailKey !== null && isset($detailTags[$detailKey])) {
                $tagIds[] = $detailTags[$detailKey];
            }

            $tagIds[] = $structureTagId;

            $items[] = [
                'uuid' => $uuid,
                'question' => $question['question'],
                'category_id' => $categoryId,
                'difficulty' => $levelDifficulty[$question['level']] ?? 3,
                'source_id' => $sectionSources[$sectionKey] ?? null,
                'flag' => 2,
                'level' => $question['level'],
                'tag_ids' => array_values(array_unique(array_filter($tagIds))),
                'answers' => $answers,
                'options' => $this->flattenOptions($question['option_sets']),
                'variants' => [],
            ];

            $meta[] = [
                'uuid' => $uuid,
                'answers' => $question['answers'],
                'option_markers' => $optionMarkerMap,
                'hints' => $hints,
                'explanations' => $explanations,
            ];
        }

        $this->seedQuestionData($items, $meta);
    }

    private function buildHintForMarker(array $question, array $config, string $example, string $marker): string
    {
        $context = $this->contextLabel($config['context']);
        $markers = $config['markers'];
        $subject = $question['subject_hint'][$marker] ?? '';
        $base = $question['verb_base'][$marker] ?? '';
        $answer = $question['answers'][$marker] ?? '';

        if ($config['form'] === 'question') {
            return "Контекст: {$context}.  \nФормула: **will + {$subject} + V1 ({$base})?**  \nПояснення: питання у першому умовному ставить `will` перед підметом, а смислове дієслово залишається у базовій формі.  \nПриклад: *{$example}*  \nМаркери: {$markers}.";
        }

        if (str_contains($answer, "won't") || str_contains($answer, 'will not')) {
            return "Контекст: {$context}.  \nФормула: **{$subject} + will not + V1 ({$base})**.  \nПояснення: результат у першому умовному заперечуємо через `will not` + базове дієслово.  \nПриклад: *{$example}*  \nМаркери: {$markers}.";
        }

        $helper = str_contains($answer, "doesn't") ? 'does not' : 'do not';

        return "Контекст: {$context}.  \nФормула: **{$subject} + {$helper} + V1 ({$base})**.  \nПояснення: if-клауза в першому умовному стоїть у Present Simple, тому використовуємо {$helper} + базове дієслово.  \nПриклад: *{$example}*  \nМаркери: {$markers}.";
    }

    private function buildExplanationsForQuestion(array $question, array $config, string $example): array
    {
        $explanations = [];
        foreach ($question['option_sets'] as $marker => $options) {
            $answer = $question['answers'][$marker] ?? '';

            foreach ($options as $option) {
                if (isset($explanations[$option])) {
                    continue;
                }

                if ($option === $answer) {
                    $explanations[$option] = $this->buildCorrectExplanationForMarker($question, $config, $example, $marker);
                } else {
                    $explanations[$option] = $this->buildWrongExplanationForMarker($question, $config, $option, $marker);
                }
            }
        }

        return $explanations;
    }

    private function buildCorrectExplanationForMarker(array $question, array $config, string $example, string $marker): string
    {
        $subject = $question['subject_hint'][$marker] ?? '';
        $base = $question['verb_base'][$marker] ?? '';
        $answer = $question['answers'][$marker] ?? '';

        if ($config['form'] === 'question') {
            return "✅ «{$answer}» правильно, бо питання про результат ставить `will` перед підметом {$subject}, а дієслово залишається у формі V1 ({$base}).  \nПриклад: *{$example}*.";
        }

        if (str_contains($answer, "won't") || str_contains($answer, 'will not')) {
            return "✅ «{$answer}» правильно, бо заперечення результату у першому умовному будуємо як `will not` + V1 ({$base}) для {$subject}.  \nПриклад: *{$example}*.";
        }

        $helper = str_contains($answer, "doesn't") ? 'does not' : 'do not';

        return "✅ «{$answer}» правильно, бо заперечення в if-клаузі у Present Simple має структуру {$helper} + V1 ({$base}) для {$subject}.  \nПриклад: *{$example}*.";
    }

    private function buildWrongExplanationForMarker(array $question, array $config, string $option, string $marker): string
    {
        $subject = $question['subject_hint'][$marker] ?? '';
        $base = $question['verb_base'][$marker] ?? '';

        $messages = [
            'do' => "❌ «do» лише допоміжне для ствердження/запитання у Present Simple, але в першому умовному результаті потрібен `will`.",
            'does' => "❌ «does» використовується у Present Simple, тоді як перший умовний вимагає `will` у результаті.",
            'did' => "❌ «did» — минулий час; if-клауза в першому умовному стоїть у Present Simple, а результат — з `will`.",
            'has' => "❌ «has» утворює Present Perfect, що не відповідає структурі першого умовного.",
            'have' => "❌ «have» створює перфектну конструкцію, а нам потрібен `will` + V1.",
            'is' => "❌ «is» — форма to be у Present Simple, але в результаті першого умовного ми вживаємо `will` + V1.",
            'are' => "❌ «are» не передає майбутній результат; потрібен `will` + V1.",
            'was' => "❌ «was» — Past Simple, тоді як перший умовний використовує Present Simple в if-клаузі й `will` у результаті.",
            'were' => "❌ «were» — Past Simple; перший умовний не використовує минулий допоміжний у цих позиціях.",
            'shall' => "❌ «shall» трапляється в офіційних пропозиціях, але стандартна форма першого умовного — `will`.",
            'should' => "❌ «should» передає пораду, а не стандартний результат першого умовного.",
            'can' => "❌ «can» говорить про можливість, однак граматично правильний майбутній результат формується з `will`.",
            'could' => "❌ «could» звучить як умовний другого типу; для першого умовного треба `will`.",
            'would' => "❌ «would» характерне для другого умовного; у першому умовному потрібно `will`.",
            "wouldn't" => "❌ «wouldn't» вказує на другий умовний. Перший умовний використовує `will not`/`won't`.",
            "didn't" => "❌ «didn't» — минуле заперечення. У першому умовному if-клауза стоїть у Present Simple з `do/does not`.",
            'will' => "❌ «will» без not не підходить, бо тут потрібно заперечення `will not`.",
        ];

        if ($config['form'] === 'negative') {
            if (in_array($option, ['do', 'does'], true)) {
                return "❌ «{$option}» без not не створює заперечення. Для {$subject} потрібно `{$option} not` + V1 ({$base}).";
            }

            if ($option === 'will') {
                return $messages['will'];
            }

            if ($option === "didn't") {
                return $messages["didn't"];
            }

            if ($option === 'would' || $option === "wouldn't") {
                return $messages[$option];
            }

            if (isset($messages[$option])) {
                return $messages[$option];
            }

            return "❌ «{$option}» не відповідає структурі заперечного першого умовного.";
        }

        if (isset($messages[$option])) {
            return $messages[$option];
        }

        return "❌ «{$option}» не відповідає структурі питання в першому умовному.";
    }

    private function contextLabel(string $context): string
    {
        return match ($context) {
            'past' => 'тригери з минулим досвідом',
            'future' => 'тригери з майбутніми планами',
            default => 'тригери з теперішнього моменту',
        };
    }

    private function entry(
        string $level,
        string $pattern,
        string $question,
        array|string $answers,
        array $options,
        array|string $clauses,
        array|string $verbHints,
        array|string $verbBases,
        array|string $subjectHints
    ): array {
        $answerMap = is_array($answers) ? $answers : ['a1' => $answers];

        return [
            'level' => $level,
            'pattern' => $pattern,
            'question' => $question,
            'answers' => $answerMap,
            'options' => $options,
            'clauses' => $this->normalizeAttribute($answerMap, $clauses),
            'verb_hint' => $this->normalizeAttribute($answerMap, $verbHints),
            'verb_base' => $this->normalizeAttribute($answerMap, $verbBases),
            'subject_hint' => $this->normalizeAttribute($answerMap, $subjectHints),
        ];
    }

    private function normalizeAttribute(array $answers, array|string $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        $marker = array_key_first($answers) ?? 'a1';

        return [$marker => $value];
    }

    private function normalizeOptionSets(array $options, array $answers): array
    {
        if ($this->isAssoc($options)) {
            $result = [];
            foreach ($options as $marker => $choices) {
                $result[$marker] = array_map(fn ($value) => (string) $value, (array) $choices);
            }

            return $result;
        }

        $marker = array_key_first($answers) ?? 'a1';

        return [
            $marker => array_map(fn ($value) => (string) $value, $options),
        ];
    }

    private function flattenOptions(array $optionSets): array
    {
        $all = [];
        foreach ($optionSets as $options) {
            foreach ($options as $option) {
                $all[] = $option;
            }
        }

        return array_values(array_unique($all));
    }

    private function isAssoc(array $array): bool
    {
        return $array !== [] && array_keys($array) !== range(0, count($array) - 1);
    }

    private function sanitizeVerbHint(array $question, string $marker): ?string
    {
        $rawHint = $question['verb_hint'][$marker] ?? null;
        if (! is_string($rawHint)) {
            return null;
        }

        $clean = $this->normalizeHint($rawHint);
        if ($clean === null || $clean === '') {
            return null;
        }

        $subject = $question['subject_hint'][$marker] ?? null;

        if (is_string($subject) && $subject !== '' && $this->subjectVisibleInQuestion($question['question'], $marker, $subject)) {
            $pattern = sprintf('/\s*[—-]\s*%s\b.*$/iu', preg_quote($subject, '/'));
            $clean = preg_replace($pattern, '', $clean);
        }

        $clean = trim(preg_replace('/\s+/', ' ', (string) $clean));

        if ($clean === '') {
            return null;
        }

        if (stripos($clean, 'will not') !== false || stripos($clean, "won't") !== false) {
            return 'not';
        }

        if (preg_match('/\bdo(?:es)? not\b/i', $clean)) {
            return 'not (present simple)';
        }

        if (preg_match('/\bwill\b/i', $clean)) {
            return 'future auxiliary';
        }

        return $clean;
    }

    private function subjectVisibleInQuestion(string $question, string $marker, string $subject): bool
    {
        $placeholder = '{' . $marker . '}';
        $position = mb_strpos($question, $placeholder, 0, 'UTF-8');

        if ($position === false) {
            return false;
        }

        $before = mb_substr($question, 0, $position, 'UTF-8');

        return preg_match('/\b' . preg_quote($subject, '/') . '\b/iu', $before) === 1;
    }
}
