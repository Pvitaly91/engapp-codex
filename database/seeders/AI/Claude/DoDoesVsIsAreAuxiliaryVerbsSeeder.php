<?php

namespace Database\Seeders\AI\Claude;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class DoDoesVsIsAreAuxiliaryVerbsSeeder extends QuestionSeeder
{
    private array $levelDifficulty = [
        'A1' => 1,
        'A2' => 2,
        'B1' => 3,
        'B2' => 4,
        'C1' => 5,
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Auxiliary Verbs: Do/Does vs Is/Are'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'https://gramlyze.com/pages/sentence-structures/do-does-is-are'])->id;
        $tagIds = $this->buildTags();
        $questions = $this->questionEntries();

        $items = [];
        $meta = [];
        foreach ($questions as $index => $entry) {
            $answers = [];
            foreach ($entry['answers'] as $marker => $answer) {
                $answers[] = [
                    'marker' => $marker,
                    'answer' => $answer,
                    'verb_hint' => $entry['verb_hints'][$marker] ?? null,
                ];
            }

            $options = $this->flattenOptions($entry['options']);
            $uuid = $this->generateQuestionUuid($entry['level'], $index + 1, $entry['question']);
            $questionTagIds = array_merge($tagIds, $entry['tag_ids'] ?? []);

            $items[] = [
                'uuid' => $uuid,
                'question' => $entry['question'],
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$entry['level']] ?? 3,
                'source_id' => $sourceId,
                'flag' => 2,
                'type' => 0,
                'level' => $entry['level'],
                'tag_ids' => $questionTagIds,
                'answers' => $answers,
                'options' => $options,
                'variants' => [$entry['question']],
            ];

            $meta[] = [
                'uuid' => $uuid,
                'hints' => $entry['hints'] ?? [],
                'answers' => $entry['answers'],
                'option_markers' => $this->buildOptionMarkers($entry['options']),
                'explanations' => $this->buildExplanations($entry['options'], $entry['answers'], $entry['level']),
            ];
        }

        $this->seedQuestionData($items, $meta);
    }

    private function buildTags(): array
    {
        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Auxiliary Verbs Do/Does vs Is/Are'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Auxiliary Verb Selection'],
            ['category' => 'English Grammar Detail']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'Do/Does/Is/Are Choice'],
            ['category' => 'English Grammar Structure']
        )->id;

        return [$themeTagId, $detailTagId, $structureTagId];
    }

    private function questionEntries(): array
    {
        $affirmativeTagId = Tag::firstOrCreate(['name' => 'Affirmative Sentence'], ['category' => 'Sentence Type'])->id;
        $negativeTagId = Tag::firstOrCreate(['name' => 'Negative Sentence'], ['category' => 'Sentence Type'])->id;
        $interrogativeTagId = Tag::firstOrCreate(['name' => 'Interrogative Sentence'], ['category' => 'Sentence Type'])->id;
        $presentTenseTagId = Tag::firstOrCreate(['name' => 'Present Tense'], ['category' => 'Tense'])->id;
        $pastTenseTagId = Tag::firstOrCreate(['name' => 'Past Tense'], ['category' => 'Tense'])->id;
        $futureTenseTagId = Tag::firstOrCreate(['name' => 'Future Tense'], ['category' => 'Tense'])->id;

        return [
            // ===== A1 Level: 12 questions =====
            ['level' => 'A1', 'question' => '{a1} you like coffee?', 'answers' => ['a1' => 'Do'], 'options' => ['a1' => ['Do', 'Does', 'Is', 'Are']], 'verb_hints' => ['a1' => 'action verb'], 'hints' => ['Формула: Do/Does + subject + base verb? для питань з дією.', 'З you використовуємо Do, не Does.', 'Is/Are використовуємо зі станами, не діями.'], 'tag_ids' => [$interrogativeTagId, $presentTenseTagId]],
            ['level' => 'A1', 'question' => '{a1} she a teacher?', 'answers' => ['a1' => 'Is'], 'options' => ['a1' => ['Is', 'Are', 'Do', 'Does']], 'verb_hints' => ['a1' => 'state/profession'], 'hints' => ['Формула: Is/Are + subject + noun/adjective? для станів.', 'She — третя особа однини, потрібен Is.', 'Do/Does використовуємо з дієсловами дії.'], 'tag_ids' => [$interrogativeTagId, $presentTenseTagId]],
            ['level' => 'A1', 'question' => 'They {a1} not play football on Sundays.', 'answers' => ['a1' => 'do'], 'options' => ['a1' => ['do', 'does', 'is', 'are']], 'verb_hints' => ['a1' => 'action verb'], 'hints' => ['Формула: Subject + do/does + not + base verb для заперечень.', 'They — множина, використовуємо do.', 'Is/are не поєднуються з дієсловами дії.'], 'tag_ids' => [$negativeTagId, $presentTenseTagId]],
            ['level' => 'A1', 'question' => 'My cat {a1} very fluffy.', 'answers' => ['a1' => 'is'], 'options' => ['a1' => ['is', 'are', 'do', 'does']], 'verb_hints' => ['a1' => 'description'], 'hints' => ['Формула: Subject + is/are + adjective для опису.', 'Cat — однина, потрібен is.', 'Прикметники поєднуються з to be, не з do/does.'], 'tag_ids' => [$affirmativeTagId, $presentTenseTagId]],
            ['level' => 'A1', 'question' => '{a1} your parents at home now?', 'answers' => ['a1' => 'Are'], 'options' => ['a1' => ['Are', 'Is', 'Do', 'Does']], 'verb_hints' => ['a1' => 'location'], 'hints' => ['Формула: Are + plural subject + location? для місцезнаходження.', 'Parents — множина, потрібен Are.', 'Місце описуємо через to be.'], 'tag_ids' => [$interrogativeTagId, $presentTenseTagId]],
            ['level' => 'A1', 'question' => 'He {a1} not eat meat.', 'answers' => ['a1' => 'does'], 'options' => ['a1' => ['does', 'do', 'is', 'are']], 'verb_hints' => ['a1' => 'action verb'], 'hints' => ['Формула: He/She/It + does + not + base verb.', 'He — третя особа однини, потрібен does.', 'Дієслово eat потребує does, не is.'], 'tag_ids' => [$negativeTagId, $presentTenseTagId]],
            ['level' => 'A1', 'question' => 'We {a1} happy today.', 'answers' => ['a1' => 'are'], 'options' => ['a1' => ['are', 'is', 'do', 'does']], 'verb_hints' => ['a1' => 'feeling'], 'hints' => ['Формула: We/They + are + adjective для почуттів.', 'We — множина, потрібен are.', 'Емоції описуємо через to be.'], 'tag_ids' => [$affirmativeTagId, $presentTenseTagId]],
            ['level' => 'A1', 'question' => '{a1} he speak English?', 'answers' => ['a1' => 'Does'], 'options' => ['a1' => ['Does', 'Do', 'Is', 'Are']], 'verb_hints' => ['a1' => 'action verb'], 'hints' => ['Формула: Does + he/she/it + base verb? для питань.', 'He — третя особа однини, потрібен Does.', 'Speak — дієслово дії, не стан.'], 'tag_ids' => [$interrogativeTagId, $presentTenseTagId]],
            ['level' => 'A1', 'question' => 'The children {a1} in the garden.', 'answers' => ['a1' => 'are'], 'options' => ['a1' => ['are', 'is', 'do', 'does']], 'verb_hints' => ['a1' => 'location'], 'hints' => ['Формула: Plural subject + are + location.', 'Children — множина, потрібен are.', 'Місцезнаходження описуємо через to be.'], 'tag_ids' => [$affirmativeTagId, $presentTenseTagId]],
            ['level' => 'A1', 'question' => 'I {a1} not understand this word.', 'answers' => ['a1' => 'do'], 'options' => ['a1' => ['do', 'does', 'am', 'is']], 'verb_hints' => ['a1' => 'mental action'], 'hints' => ['Формула: I + do + not + base verb.', 'I використовується з do, не does.', 'Understand — дієслово, потрібен do.'], 'tag_ids' => [$negativeTagId, $presentTenseTagId]],
            ['level' => 'A1', 'question' => '{a1} this your bag?', 'answers' => ['a1' => 'Is'], 'options' => ['a1' => ['Is', 'Are', 'Do', 'Does']], 'verb_hints' => ['a1' => 'possession'], 'hints' => ['Формула: Is + this/that + noun? для ідентифікації.', 'This — однина, потрібен Is.', 'Належність описуємо через to be.'], 'tag_ids' => [$interrogativeTagId, $presentTenseTagId]],
            ['level' => 'A1', 'question' => 'She {a1} not tired after work.', 'answers' => ['a1' => 'is'], 'options' => ['a1' => ['is', 'are', 'do', 'does']], 'verb_hints' => ['a1' => 'feeling'], 'hints' => ['Формула: She + is + not + adjective.', 'She — однина, потрібен is.', 'Tired — прикметник, поєднується з to be.'], 'tag_ids' => [$negativeTagId, $presentTenseTagId]],

            // ===== A2 Level: 12 questions =====
            ['level' => 'A2', 'question' => '{a1} your sister work in a hospital?', 'answers' => ['a1' => 'Does'], 'options' => ['a1' => ['Does', 'Do', 'Is', 'Are']], 'verb_hints' => ['a1' => 'occupation action'], 'hints' => ['Формула: Does + third person + base verb? для питань.', 'Sister — третя особа однини.', 'Work — дієслово дії, потребує Does.'], 'tag_ids' => [$interrogativeTagId, $presentTenseTagId]],
            ['level' => 'A2', 'question' => 'The students {a1} not ready for the exam.', 'answers' => ['a1' => 'are'], 'options' => ['a1' => ['are', 'is', 'do', 'does']], 'verb_hints' => ['a1' => 'state of readiness'], 'hints' => ['Формула: Plural + are + not + adjective.', 'Students — множина, потрібен are.', 'Ready — прикметник стану.'], 'tag_ids' => [$negativeTagId, $presentTenseTagId]],
            ['level' => 'A2', 'question' => '{a1} you often visit your grandparents?', 'answers' => ['a1' => 'Do'], 'options' => ['a1' => ['Do', 'Does', 'Are', 'Is']], 'verb_hints' => ['a1' => 'habitual action'], 'hints' => ['Формула: Do + you + adverb + base verb?', 'Often вказує на звичку — потрібен Do.', 'Visit — дієслово дії.'], 'tag_ids' => [$interrogativeTagId, $presentTenseTagId]],
            ['level' => 'A2', 'question' => 'My brother {a1} interested in science.', 'answers' => ['a1' => 'is'], 'options' => ['a1' => ['is', 'are', 'do', 'does']], 'verb_hints' => ['a1' => 'interest state'], 'hints' => ['Формула: Subject + is + interested in + noun.', 'Interested — прикметник стану.', 'Brother — однина, потрібен is.'], 'tag_ids' => [$affirmativeTagId, $presentTenseTagId]],
            ['level' => 'A2', 'question' => 'They {a1} not believe in ghosts.', 'answers' => ['a1' => 'do'], 'options' => ['a1' => ['do', 'does', 'are', 'is']], 'verb_hints' => ['a1' => 'mental action'], 'hints' => ['Формула: They + do + not + base verb.', 'Believe — дієслово думки.', 'They — множина, потрібен do.'], 'tag_ids' => [$negativeTagId, $presentTenseTagId]],
            ['level' => 'A2', 'question' => '{a1} the movie interesting?', 'answers' => ['a1' => 'Is'], 'options' => ['a1' => ['Is', 'Are', 'Does', 'Do']], 'verb_hints' => ['a1' => 'quality'], 'hints' => ['Формула: Is + singular noun + adjective?', 'Movie — однина, потрібен Is.', 'Interesting — прикметник якості.'], 'tag_ids' => [$interrogativeTagId, $presentTenseTagId]],
            ['level' => 'A2', 'question' => 'She {a1} not usually cook dinner.', 'answers' => ['a1' => 'does'], 'options' => ['a1' => ['does', 'do', 'is', 'are']], 'verb_hints' => ['a1' => 'habitual action'], 'hints' => ['Формула: She + does + not + adverb + base verb.', 'Usually вказує на звичку.', 'Cook — дієслово дії.'], 'tag_ids' => [$negativeTagId, $presentTenseTagId]],
            ['level' => 'A2', 'question' => 'The weather {a1} nice today.', 'answers' => ['a1' => 'is'], 'options' => ['a1' => ['is', 'are', 'does', 'do']], 'verb_hints' => ['a1' => 'weather condition'], 'hints' => ['Формула: The weather + is + adjective.', 'Weather — однина, потрібен is.', 'Погоду описуємо через to be.'], 'tag_ids' => [$affirmativeTagId, $presentTenseTagId]],
            ['level' => 'A2', 'question' => '{a1} these books belong to you?', 'answers' => ['a1' => 'Do'], 'options' => ['a1' => ['Do', 'Does', 'Are', 'Is']], 'verb_hints' => ['a1' => 'possession action'], 'hints' => ['Формула: Do + plural + base verb?', 'These books — множина.', 'Belong — дієслово володіння.'], 'tag_ids' => [$interrogativeTagId, $presentTenseTagId]],
            ['level' => 'A2', 'question' => 'We {a1} not afraid of the dark.', 'answers' => ['a1' => 'are'], 'options' => ['a1' => ['are', 'is', 'do', 'does']], 'verb_hints' => ['a1' => 'emotional state'], 'hints' => ['Формула: We + are + not + afraid of.', 'Afraid — прикметник емоційного стану.', 'We — множина, потрібен are.'], 'tag_ids' => [$negativeTagId, $presentTenseTagId]],
            ['level' => 'A2', 'question' => '{a1} the train arrive on time?', 'answers' => ['a1' => 'Does'], 'options' => ['a1' => ['Does', 'Do', 'Is', 'Are']], 'verb_hints' => ['a1' => 'scheduled action'], 'hints' => ['Формула: Does + singular + base verb?', 'Train — однина, потрібен Does.', 'Arrive — дієслово дії.'], 'tag_ids' => [$interrogativeTagId, $presentTenseTagId]],
            ['level' => 'A2', 'question' => 'His parents {a1} very proud of him.', 'answers' => ['a1' => 'are'], 'options' => ['a1' => ['are', 'is', 'do', 'does']], 'verb_hints' => ['a1' => 'emotional state'], 'hints' => ['Формула: Plural + are + adjective + of.', 'Parents — множина.', 'Proud — прикметник стану.'], 'tag_ids' => [$affirmativeTagId, $presentTenseTagId]],

            // ===== B1 Level: 12 questions =====
            ['level' => 'B1', 'question' => '{a1} your company provide health insurance?', 'answers' => ['a1' => 'Does'], 'options' => ['a1' => ['Does', 'Do', 'Is', 'Are']], 'verb_hints' => ['a1' => 'business action'], 'hints' => ['Формула: Does + organization + base verb?', 'Company — однина, потрібен Does.', 'Provide — дієслово дії.'], 'tag_ids' => [$interrogativeTagId, $presentTenseTagId]],
            ['level' => 'B1', 'question' => 'The new regulations {a1} not apply to small businesses.', 'answers' => ['a1' => 'do'], 'options' => ['a1' => ['do', 'does', 'are', 'is']], 'verb_hints' => ['a1' => 'legal action'], 'hints' => ['Формула: Plural + do + not + base verb.', 'Regulations — множина.', 'Apply — дієслово дії.'], 'tag_ids' => [$negativeTagId, $presentTenseTagId]],
            ['level' => 'B1', 'question' => '{a1} these results accurate?', 'answers' => ['a1' => 'Are'], 'options' => ['a1' => ['Are', 'Is', 'Do', 'Does']], 'verb_hints' => ['a1' => 'quality assessment'], 'hints' => ['Формула: Are + plural + adjective?', 'Results — множина, потрібен Are.', 'Accurate — прикметник якості.'], 'tag_ids' => [$interrogativeTagId, $presentTenseTagId]],
            ['level' => 'B1', 'question' => 'The manager {a1} responsible for all decisions.', 'answers' => ['a1' => 'is'], 'options' => ['a1' => ['is', 'are', 'does', 'do']], 'verb_hints' => ['a1' => 'responsibility state'], 'hints' => ['Формула: Singular + is + responsible for.', 'Manager — однина.', 'Responsible — прикметник стану.'], 'tag_ids' => [$affirmativeTagId, $presentTenseTagId]],
            ['level' => 'B1', 'question' => '{a1} the evidence support your theory?', 'answers' => ['a1' => 'Does'], 'options' => ['a1' => ['Does', 'Do', 'Is', 'Are']], 'verb_hints' => ['a1' => 'logical action'], 'hints' => ['Формула: Does + singular + base verb?', 'Evidence — однина.', 'Support — дієслово дії.'], 'tag_ids' => [$interrogativeTagId, $presentTenseTagId]],
            ['level' => 'B1', 'question' => 'We {a1} not aware of any changes to the schedule.', 'answers' => ['a1' => 'are'], 'options' => ['a1' => ['are', 'is', 'do', 'does']], 'verb_hints' => ['a1' => 'awareness state'], 'hints' => ['Формула: We + are + not + aware of.', 'Aware — прикметник стану.', 'We — множина, потрібен are.'], 'tag_ids' => [$negativeTagId, $presentTenseTagId]],
            ['level' => 'B1', 'question' => 'This research {a1} not include recent data.', 'answers' => ['a1' => 'does'], 'options' => ['a1' => ['does', 'do', 'is', 'are']], 'verb_hints' => ['a1' => 'inclusion action'], 'hints' => ['Формула: Singular + does + not + base verb.', 'Research — однина.', 'Include — дієслово дії.'], 'tag_ids' => [$negativeTagId, $presentTenseTagId]],
            ['level' => 'B1', 'question' => '{a1} you familiar with this software?', 'answers' => ['a1' => 'Are'], 'options' => ['a1' => ['Are', 'Is', 'Do', 'Does']], 'verb_hints' => ['a1' => 'familiarity state'], 'hints' => ['Формула: Are + you + familiar with?', 'Familiar — прикметник стану.', 'Стани описуємо через to be.'], 'tag_ids' => [$interrogativeTagId, $presentTenseTagId]],
            ['level' => 'B1', 'question' => 'The instructions {a1} very clear.', 'answers' => ['a1' => 'are'], 'options' => ['a1' => ['are', 'is', 'do', 'does']], 'verb_hints' => ['a1' => 'quality description'], 'hints' => ['Формула: Plural + are + adjective.', 'Instructions — множина.', 'Clear — прикметник якості.'], 'tag_ids' => [$affirmativeTagId, $presentTenseTagId]],
            ['level' => 'B1', 'question' => '{a1} this price include taxes?', 'answers' => ['a1' => 'Does'], 'options' => ['a1' => ['Does', 'Do', 'Is', 'Are']], 'verb_hints' => ['a1' => 'inclusion action'], 'hints' => ['Формула: Does + singular + base verb?', 'Price — однина.', 'Include — дієслово дії.'], 'tag_ids' => [$interrogativeTagId, $presentTenseTagId]],
            ['level' => 'B1', 'question' => 'The staff {a1} not satisfied with the new policy.', 'answers' => ['a1' => 'are'], 'options' => ['a1' => ['are', 'is', 'do', 'does']], 'verb_hints' => ['a1' => 'satisfaction state'], 'hints' => ['Формула: Collective noun + are + not + adjective.', 'Staff — колективний іменник (множина).', 'Satisfied — прикметник стану.'], 'tag_ids' => [$negativeTagId, $presentTenseTagId]],
            ['level' => 'B1', 'question' => 'The report {a1} not mention any risks.', 'answers' => ['a1' => 'does'], 'options' => ['a1' => ['does', 'do', 'is', 'are']], 'verb_hints' => ['a1' => 'communication action'], 'hints' => ['Формула: Singular + does + not + base verb.', 'Report — однина.', 'Mention — дієслово дії.'], 'tag_ids' => [$negativeTagId, $presentTenseTagId]],

            // ===== B2 Level: 12 questions =====
            ['level' => 'B2', 'question' => '{a1} the committee approve the proposal?', 'answers' => ['a1' => 'Did'], 'options' => ['a1' => ['Did', 'Does', 'Was', 'Were']], 'verb_hints' => ['a1' => 'past action'], 'hints' => ['Формула: Did + subject + base verb? для минулого.', 'Past Simple питання потребує Did.', 'Approve — дієслово дії.'], 'tag_ids' => [$interrogativeTagId, $pastTenseTagId]],
            ['level' => 'B2', 'question' => 'The negotiations {a1} not successful last year.', 'answers' => ['a1' => 'were'], 'options' => ['a1' => ['were', 'was', 'did', 'does']], 'verb_hints' => ['a1' => 'past state'], 'hints' => ['Формула: Plural + were + not + adjective.', 'Negotiations — множина.', 'Past Simple стан потребує were.'], 'tag_ids' => [$negativeTagId, $pastTenseTagId]],
            ['level' => 'B2', 'question' => '{a1} the project feasible given the budget constraints?', 'answers' => ['a1' => 'Is'], 'options' => ['a1' => ['Is', 'Are', 'Does', 'Do']], 'verb_hints' => ['a1' => 'feasibility state'], 'hints' => ['Формула: Is + singular + adjective?', 'Project — однина.', 'Feasible — прикметник можливості.'], 'tag_ids' => [$interrogativeTagId, $presentTenseTagId]],
            ['level' => 'B2', 'question' => 'The data {a1} not support the initial hypothesis.', 'answers' => ['a1' => 'does'], 'options' => ['a1' => ['does', 'do', 'is', 'are']], 'verb_hints' => ['a1' => 'evidence action'], 'hints' => ['Формула: Data + does + not + base verb.', 'Data — однина в науковому контексті.', 'Support — дієслово дії.'], 'tag_ids' => [$negativeTagId, $presentTenseTagId]],
            ['level' => 'B2', 'question' => '{a1} the stakeholders agree with the decision?', 'answers' => ['a1' => 'Do'], 'options' => ['a1' => ['Do', 'Does', 'Are', 'Is']], 'verb_hints' => ['a1' => 'agreement action'], 'hints' => ['Формула: Do + plural + base verb?', 'Stakeholders — множина.', 'Agree — дієслово дії.'], 'tag_ids' => [$interrogativeTagId, $presentTenseTagId]],
            ['level' => 'B2', 'question' => 'This approach {a1} more sustainable than the previous one.', 'answers' => ['a1' => 'is'], 'options' => ['a1' => ['is', 'are', 'does', 'do']], 'verb_hints' => ['a1' => 'comparison state'], 'hints' => ['Формула: Singular + is + comparative + than.', 'Approach — однина.', 'Порівняння потребує to be.'], 'tag_ids' => [$affirmativeTagId, $presentTenseTagId]],
            ['level' => 'B2', 'question' => '{a1} the analysis reveal any significant trends?', 'answers' => ['a1' => 'Did'], 'options' => ['a1' => ['Did', 'Does', 'Was', 'Were']], 'verb_hints' => ['a1' => 'past discovery'], 'hints' => ['Формула: Did + singular + base verb?', 'Past Simple питання.', 'Reveal — дієслово дії.'], 'tag_ids' => [$interrogativeTagId, $pastTenseTagId]],
            ['level' => 'B2', 'question' => 'The implications of this decision {a1} far-reaching.', 'answers' => ['a1' => 'are'], 'options' => ['a1' => ['are', 'is', 'do', 'does']], 'verb_hints' => ['a1' => 'consequence state'], 'hints' => ['Формула: Plural + are + adjective.', 'Implications — множина.', 'Far-reaching — прикметник.'], 'tag_ids' => [$affirmativeTagId, $presentTenseTagId]],
            ['level' => 'B2', 'question' => 'The team {a1} not meet the deadline last month.', 'answers' => ['a1' => 'did'], 'options' => ['a1' => ['did', 'does', 'was', 'were']], 'verb_hints' => ['a1' => 'past action'], 'hints' => ['Формула: Subject + did + not + base verb.', 'Past Simple заперечення.', 'Meet — дієслово дії.'], 'tag_ids' => [$negativeTagId, $pastTenseTagId]],
            ['level' => 'B2', 'question' => '{a1} these findings consistent with previous research?', 'answers' => ['a1' => 'Are'], 'options' => ['a1' => ['Are', 'Is', 'Do', 'Does']], 'verb_hints' => ['a1' => 'consistency state'], 'hints' => ['Формула: Are + plural + adjective + with?', 'Findings — множина.', 'Consistent — прикметник.'], 'tag_ids' => [$interrogativeTagId, $presentTenseTagId]],
            ['level' => 'B2', 'question' => 'The solution {a1} not address the root cause of the problem.', 'answers' => ['a1' => 'does'], 'options' => ['a1' => ['does', 'do', 'is', 'are']], 'verb_hints' => ['a1' => 'problem-solving action'], 'hints' => ['Формула: Singular + does + not + base verb.', 'Solution — однина.', 'Address — дієслово дії.'], 'tag_ids' => [$negativeTagId, $presentTenseTagId]],
            ['level' => 'B2', 'question' => 'The outcomes {a1} not what we expected.', 'answers' => ['a1' => 'were'], 'options' => ['a1' => ['were', 'was', 'did', 'does']], 'verb_hints' => ['a1' => 'past expectation'], 'hints' => ['Формула: Plural + were + not + what clause.', 'Outcomes — множина.', 'Past Simple стан.'], 'tag_ids' => [$negativeTagId, $pastTenseTagId]],

            // ===== C1 Level: 12 questions =====
            ['level' => 'C1', 'question' => '{a1} the empirical evidence substantiate the theoretical framework?', 'answers' => ['a1' => 'Does'], 'options' => ['a1' => ['Does', 'Do', 'Is', 'Are']], 'verb_hints' => ['a1' => 'academic verification'], 'hints' => ['Формула: Does + singular + base verb?', 'Evidence — однина.', 'Substantiate — академічне дієслово.'], 'tag_ids' => [$interrogativeTagId, $presentTenseTagId]],
            ['level' => 'C1', 'question' => 'The ramifications of this policy {a1} not immediately apparent.', 'answers' => ['a1' => 'are'], 'options' => ['a1' => ['are', 'is', 'do', 'does']], 'verb_hints' => ['a1' => 'visibility state'], 'hints' => ['Формула: Plural + are + not + adjective.', 'Ramifications — множина.', 'Apparent — прикметник стану.'], 'tag_ids' => [$negativeTagId, $presentTenseTagId]],
            ['level' => 'C1', 'question' => '{a1} the methodology account for potential confounding variables?', 'answers' => ['a1' => 'Does'], 'options' => ['a1' => ['Does', 'Do', 'Is', 'Are']], 'verb_hints' => ['a1' => 'research action'], 'hints' => ['Формула: Does + singular + base verb + for?', 'Methodology — однина.', 'Account for — дієслівний вираз.'], 'tag_ids' => [$interrogativeTagId, $presentTenseTagId]],
            ['level' => 'C1', 'question' => 'The underlying assumptions {a1} fundamentally flawed.', 'answers' => ['a1' => 'are'], 'options' => ['a1' => ['are', 'is', 'do', 'does']], 'verb_hints' => ['a1' => 'quality assessment'], 'hints' => ['Формула: Plural + are + adverb + adjective.', 'Assumptions — множина.', 'Flawed — прикметник якості.'], 'tag_ids' => [$affirmativeTagId, $presentTenseTagId]],
            ['level' => 'C1', 'question' => '{a1} the proposed amendments align with constitutional principles?', 'answers' => ['a1' => 'Do'], 'options' => ['a1' => ['Do', 'Does', 'Are', 'Is']], 'verb_hints' => ['a1' => 'alignment action'], 'hints' => ['Формула: Do + plural + base verb + with?', 'Amendments — множина.', 'Align — дієслово дії.'], 'tag_ids' => [$interrogativeTagId, $presentTenseTagId]],
            ['level' => 'C1', 'question' => 'The discourse {a1} not adequately address systemic inequalities.', 'answers' => ['a1' => 'does'], 'options' => ['a1' => ['does', 'do', 'is', 'are']], 'verb_hints' => ['a1' => 'communication action'], 'hints' => ['Формула: Singular + does + not + adverb + base verb.', 'Discourse — однина.', 'Address — дієслово дії.'], 'tag_ids' => [$negativeTagId, $presentTenseTagId]],
            ['level' => 'C1', 'question' => '{a1} these paradigms applicable across different cultural contexts?', 'answers' => ['a1' => 'Are'], 'options' => ['a1' => ['Are', 'Is', 'Do', 'Does']], 'verb_hints' => ['a1' => 'applicability state'], 'hints' => ['Формула: Are + plural + adjective + across?', 'Paradigms — множина.', 'Applicable — прикметник.'], 'tag_ids' => [$interrogativeTagId, $presentTenseTagId]],
            ['level' => 'C1', 'question' => 'The epistemological foundations {a1} not universally accepted.', 'answers' => ['a1' => 'are'], 'options' => ['a1' => ['are', 'is', 'do', 'does']], 'verb_hints' => ['a1' => 'acceptance state'], 'hints' => ['Формула: Plural + are + not + adverb + adjective.', 'Foundations — множина.', 'Accepted — прикметник стану.'], 'tag_ids' => [$negativeTagId, $presentTenseTagId]],
            ['level' => 'C1', 'question' => '{a1} the empirical data corroborate the qualitative findings?', 'answers' => ['a1' => 'Does'], 'options' => ['a1' => ['Does', 'Do', 'Is', 'Are']], 'verb_hints' => ['a1' => 'verification action'], 'hints' => ['Формула: Does + singular + base verb?', 'Data — однина у формальному контексті.', 'Corroborate — академічне дієслово.'], 'tag_ids' => [$interrogativeTagId, $presentTenseTagId]],
            ['level' => 'C1', 'question' => 'The implications for policy {a1} profound and multifaceted.', 'answers' => ['a1' => 'are'], 'options' => ['a1' => ['are', 'is', 'do', 'does']], 'verb_hints' => ['a1' => 'importance state'], 'hints' => ['Формула: Plural + are + adjective + and + adjective.', 'Implications — множина.', 'Profound — прикметник.'], 'tag_ids' => [$affirmativeTagId, $presentTenseTagId]],
            ['level' => 'C1', 'question' => 'This hypothesis {a1} not withstand rigorous scrutiny.', 'answers' => ['a1' => 'does'], 'options' => ['a1' => ['does', 'do', 'is', 'are']], 'verb_hints' => ['a1' => 'endurance action'], 'hints' => ['Формула: Singular + does + not + base verb.', 'Hypothesis — однина.', 'Withstand — дієслово дії.'], 'tag_ids' => [$negativeTagId, $presentTenseTagId]],
            ['level' => 'C1', 'question' => '{a1} the interviewees cognizant of the ethical implications?', 'answers' => ['a1' => 'Were'], 'options' => ['a1' => ['Were', 'Was', 'Did', 'Does']], 'verb_hints' => ['a1' => 'past awareness'], 'hints' => ['Формула: Were + plural + adjective + of?', 'Interviewees — множина.', 'Past Simple стан.'], 'tag_ids' => [$interrogativeTagId, $pastTenseTagId]],
        ];
    }

    private function flattenOptions(array $options): array
    {
        $flat = [];
        foreach ($options as $marker => $values) {
            foreach ($values as $value) {
                if (!in_array($value, $flat, true)) {
                    $flat[] = $value;
                }
            }
        }
        return $flat;
    }

    private function buildOptionMarkers(array $options): array
    {
        $map = [];
        foreach ($options as $marker => $values) {
            foreach ($values as $value) {
                $map[$value] = $marker;
            }
        }
        return $map;
    }

    private function buildExplanations(array $options, array $answers, string $level): array
    {
        $explanations = [];
        foreach ($options as $marker => $values) {
            $correctAnswer = $answers[$marker] ?? '';
            foreach ($values as $value) {
                if ($value === $correctAnswer) {
                    $explanations[$value] = "✅ Правильно! «{$value}» є коректним вибором для цього контексту.";
                } else {
                    $explanations[$value] = $this->wrongExplanation($value, $correctAnswer);
                }
            }
        }
        return $explanations;
    }

    private function wrongExplanation(string $wrong, string $correct): string
    {
        $doForms = ['Do', 'do', 'Does', 'does', 'Did', 'did'];
        $beForms = ['Is', 'is', 'Are', 'are', 'Was', 'was', 'Were', 'were', 'Am', 'am'];

        if (in_array($wrong, $doForms) && in_array($correct, $beForms)) {
            return "❌ «{$wrong}» використовується з дієсловами дії, але тут потрібна форма to be для опису стану або якості.";
        }

        if (in_array($wrong, $beForms) && in_array($correct, $doForms)) {
            return "❌ «{$wrong}» — форма to be для станів. Тут потрібен допоміжний для дієслова дії.";
        }

        if (in_array($wrong, ['Does', 'does']) && in_array($correct, ['Do', 'do'])) {
            return "❌ «{$wrong}» використовується з третьою особою однини. Для множини або I/you потрібен інший варіант.";
        }

        if (in_array($wrong, ['Do', 'do']) && in_array($correct, ['Does', 'does'])) {
            return "❌ «{$wrong}» використовується з I/you/we/they. Для третьої особи однини потрібен інший варіант.";
        }

        if (in_array($wrong, ['Is', 'is']) && in_array($correct, ['Are', 'are'])) {
            return "❌ «{$wrong}» використовується з одниною. Для множини потрібна інша форма to be.";
        }

        if (in_array($wrong, ['Are', 'are']) && in_array($correct, ['Is', 'is'])) {
            return "❌ «{$wrong}» використовується з множиною. Для однини потрібна інша форма to be.";
        }

        return "❌ «{$wrong}» не підходить у цьому контексті. Перевірте підмет та тип дієслова.";
    }
}
