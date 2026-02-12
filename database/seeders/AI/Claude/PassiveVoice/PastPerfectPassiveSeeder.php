<?php

namespace Database\Seeders\AI\Claude\PassiveVoice;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class PastPerfectPassiveSeeder extends QuestionSeeder
{
    private array $levelDifficulty = [
        'A1' => 1,
        'A2' => 2,
        'B1' => 3,
        'B2' => 4,
        'C1' => 5,
        'C2' => 5,
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Passive Voice'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'AI Generated: Past Perfect Passive'])->id;
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
                'explanations' => $this->buildExplanations($entry['options'], $entry['answers']),
            ];
        }

        $this->seedQuestionData($items, $meta);
    }

    private function buildTags(): array
    {
        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Passive Voice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Past Perfect Passive'],
            ['category' => 'English Grammar Detail']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'Passive Construction'],
            ['category' => 'English Grammar Structure']
        )->id;

        return [$themeTagId, $detailTagId, $structureTagId];
    }

    private function questionEntries(): array
    {
        $affirmativeTagId = Tag::firstOrCreate(['name' => 'Affirmative Sentence'], ['category' => 'Sentence Type'])->id;
        $negativeTagId = Tag::firstOrCreate(['name' => 'Negative Sentence'], ['category' => 'Sentence Type'])->id;
        $interrogativeTagId = Tag::firstOrCreate(['name' => 'Interrogative Sentence'], ['category' => 'Sentence Type'])->id;

        $pastPerfectTagId = Tag::firstOrCreate(['name' => 'Past Perfect'], ['category' => 'Tense'])->id;
        $passiveTagId = Tag::firstOrCreate(['name' => 'Passive Voice'], ['category' => 'Voice'])->id;

        return [
            // ===== A1 Level: 12 questions =====
            [
                'level' => 'A1',
                'question' => 'The house {a1} before we moved in.',
                'answers' => ['a1' => 'had been painted'],
                'options' => [
                    'a1' => ['had been painted', 'was painted', 'has been painted', 'had painted'],
                ],
                'verb_hints' => ['a1' => 'paint'],
                'hints' => [
                    '**Past Perfect Passive** утворюється: had + been + past participle (V3).',
                    'Використовується для дій, завершених до іншої дії в минулому.',
                    'Приклад: The room had been cleaned before the guests arrived.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The letters {a1} before he arrived.',
                'answers' => ['a1' => 'had been sent'],
                'options' => [
                    'a1' => ['had been sent', 'were sent', 'have been sent', 'had sent'],
                ],
                'verb_hints' => ['a1' => 'send'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Показує, що дія сталася раніше за іншу в минулому.',
                    'Приклад: The packages had been delivered.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The cake {a1} before the party started.',
                'answers' => ['a1' => 'had been made'],
                'options' => [
                    'a1' => ['had been made', 'was made', 'has been made', 'had made'],
                ],
                'verb_hints' => ['a1' => 'make'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The meal had been prepared.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The homework {a1} before the teacher asked for it.',
                'answers' => ['a1' => 'had been finished'],
                'options' => [
                    'a1' => ['had been finished', 'was finished', 'has been finished', 'had finished'],
                ],
                'verb_hints' => ['a1' => 'finish'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The task had been completed.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The books {a1} before the library closed.',
                'answers' => ['a1' => 'had been returned'],
                'options' => [
                    'a1' => ['had been returned', 'were returned', 'have been returned', 'had returned'],
                ],
                'verb_hints' => ['a1' => 'return'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The documents had been submitted.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The room {a1} before we arrived.',
                'answers' => ['a1' => 'had been cleaned'],
                'options' => [
                    'a1' => ['had been cleaned', 'was cleaned', 'has been cleaned', 'had cleaned'],
                ],
                'verb_hints' => ['a1' => 'clean'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The floor had been mopped.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The car {a1} before we needed it.',
                'answers' => ['a1' => "hadn't been washed"],
                'options' => [
                    'a1' => ["hadn't been washed", "wasn't washed", "hasn't been washed", "hadn't washed"],
                ],
                'verb_hints' => ['a1' => 'wash'],
                'hints' => [
                    '**Past Perfect Passive Negative**: had not (hadn\'t) + been + V3.',
                    'Приклад: The dishes hadn\'t been done.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The tickets {a1} before the event.',
                'answers' => ['a1' => "hadn't been bought"],
                'options' => [
                    'a1' => ["hadn't been bought", "weren't bought", "haven't been bought", "hadn't bought"],
                ],
                'verb_hints' => ['a1' => 'buy'],
                'hints' => [
                    '**Past Perfect Passive Negative**: hadn\'t + been + V3.',
                    'Приклад: The reservations hadn\'t been made.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => '{a1} the door been locked before you left?',
                'answers' => ['a1' => 'Had'],
                'options' => [
                    'a1' => ['Had', 'Was', 'Has', 'Did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Past Perfect Passive Question**: Had + subject + been + V3?',
                    'Приклад: Had the window been closed?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => '{a1} the flowers been watered before the rain?',
                'answers' => ['a1' => 'Had'],
                'options' => [
                    'a1' => ['Had', 'Were', 'Have', 'Did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Past Perfect Passive Question**: Had + subject + been + V3?',
                    'Приклад: Had the plants been cared for?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The dog {a1} before we adopted him.',
                'answers' => ['a1' => 'had been found'],
                'options' => [
                    'a1' => ['had been found', 'was found', 'has been found', 'had found'],
                ],
                'verb_hints' => ['a1' => 'find'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The cat had been discovered.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'I {a1} about the change before the meeting.',
                'answers' => ['a1' => 'had been told'],
                'options' => [
                    'a1' => ['had been told', 'was told', 'have been told', 'had told'],
                ],
                'verb_hints' => ['a1' => 'tell'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: I had been informed.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],

            // ===== A2 Level: 12 questions =====
            [
                'level' => 'A2',
                'question' => 'The building {a1} before the earthquake.',
                'answers' => ['a1' => 'had been constructed'],
                'options' => [
                    'a1' => ['had been constructed', 'was constructed', 'has been constructed', 'had constructed'],
                ],
                'verb_hints' => ['a1' => 'construct'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The bridge had been built.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The students {a1} before the exam began.',
                'answers' => ['a1' => 'had been prepared'],
                'options' => [
                    'a1' => ['had been prepared', 'were prepared', 'have been prepared', 'had prepared'],
                ],
                'verb_hints' => ['a1' => 'prepare'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The candidates had been trained.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The movie {a1} before the premiere.',
                'answers' => ['a1' => 'had been filmed'],
                'options' => [
                    'a1' => ['had been filmed', 'was filmed', 'has been filmed', 'had filmed'],
                ],
                'verb_hints' => ['a1' => 'film'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The show had been recorded.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The guests {a1} before the dinner.',
                'answers' => ['a1' => 'had been invited'],
                'options' => [
                    'a1' => ['had been invited', 'were invited', 'have been invited', 'had invited'],
                ],
                'verb_hints' => ['a1' => 'invite'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The visitors had been welcomed.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The report {a1} before the deadline.',
                'answers' => ['a1' => 'had been submitted'],
                'options' => [
                    'a1' => ['had been submitted', 'was submitted', 'has been submitted', 'had submitted'],
                ],
                'verb_hints' => ['a1' => 'submit'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The application had been filed.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The thief {a1} before he could escape.',
                'answers' => ['a1' => "hadn't been caught"],
                'options' => [
                    'a1' => ["hadn't been caught", "wasn't caught", "hasn't been caught", "hadn't caught"],
                ],
                'verb_hints' => ['a1' => 'catch'],
                'hints' => [
                    '**Past Perfect Passive Negative**: hadn\'t + been + V3.',
                    'Приклад: The criminal hadn\'t been arrested.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The rules {a1} properly before the game.',
                'answers' => ['a1' => "hadn't been explained"],
                'options' => [
                    'a1' => ["hadn't been explained", "weren't explained", "haven't been explained", "hadn't explained"],
                ],
                'verb_hints' => ['a1' => 'explain'],
                'hints' => [
                    '**Past Perfect Passive Negative**: hadn\'t + been + V3.',
                    'Приклад: The instructions hadn\'t been given.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => '{a1} the project been completed before the deadline?',
                'answers' => ['a1' => 'Had'],
                'options' => [
                    'a1' => ['Had', 'Was', 'Has', 'Did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Past Perfect Passive Question**: Had + subject + been + V3?',
                    'Приклад: Had the task been finished?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'When {a1} the pyramids been built?',
                'answers' => ['a1' => 'had'],
                'options' => [
                    'a1' => ['had', 'were', 'have', 'did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Past Perfect Passive**: When + had + subject + been + V3?',
                    'Приклад: When had the castle been constructed?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The song {a1} before the album was released.',
                'answers' => ['a1' => 'had been recorded'],
                'options' => [
                    'a1' => ['had been recorded', 'was recorded', 'has been recorded', 'had recorded'],
                ],
                'verb_hints' => ['a1' => 'record'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The track had been mixed.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'Where {a1} the treasure been hidden?',
                'answers' => ['a1' => 'had'],
                'options' => [
                    'a1' => ['had', 'was', 'has', 'did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Past Perfect Passive**: Where + had + subject + been + V3?',
                    'Приклад: Where had the key been kept?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The old factory {a1} before the new one was built.',
                'answers' => ['a1' => 'had been demolished'],
                'options' => [
                    'a1' => ['had been demolished', 'was demolished', 'has been demolished', 'had demolished'],
                ],
                'verb_hints' => ['a1' => 'demolish'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The building had been torn down.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],

            // ===== B1 Level: 12 questions =====
            [
                'level' => 'B1',
                'question' => 'The contract {a1} before the meeting.',
                'answers' => ['a1' => 'had been signed'],
                'options' => [
                    'a1' => ['had been signed', 'was signed', 'has been signed', 'had signed'],
                ],
                'verb_hints' => ['a1' => 'sign'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The agreement had been finalized.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'Several suspects {a1} before the case was closed.',
                'answers' => ['a1' => 'had been arrested'],
                'options' => [
                    'a1' => ['had been arrested', 'were arrested', 'have been arrested', 'had arrested'],
                ],
                'verb_hints' => ['a1' => 'arrest'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The criminals had been detained.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The research {a1} before the funding ran out.',
                'answers' => ['a1' => 'had been conducted'],
                'options' => [
                    'a1' => ['had been conducted', 'was conducted', 'has been conducted', 'had conducted'],
                ],
                'verb_hints' => ['a1' => 'conduct'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The experiment had been performed.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The patients {a1} before the doctor arrived.',
                'answers' => ['a1' => 'had been treated'],
                'options' => [
                    'a1' => ['had been treated', 'were treated', 'have been treated', 'had treated'],
                ],
                'verb_hints' => ['a1' => 'treat'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The injured had been helped.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The deadline {a1} before we asked.',
                'answers' => ['a1' => "hadn't been extended"],
                'options' => [
                    'a1' => ["hadn't been extended", "wasn't extended", "hasn't been extended", "hadn't extended"],
                ],
                'verb_hints' => ['a1' => 'extend'],
                'hints' => [
                    '**Past Perfect Passive Negative**: hadn\'t + been + V3.',
                    'Приклад: The date hadn\'t been changed.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The candidates {a1} about the results before they found out.',
                'answers' => ['a1' => "hadn't been informed"],
                'options' => [
                    'a1' => ["hadn't been informed", "weren't informed", "haven't been informed", "hadn't informed"],
                ],
                'verb_hints' => ['a1' => 'inform'],
                'hints' => [
                    '**Past Perfect Passive Negative**: hadn\'t + been + V3.',
                    'Приклад: The employees hadn\'t been notified.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => '{a1} the proposal been accepted before the board met?',
                'answers' => ['a1' => 'Had'],
                'options' => [
                    'a1' => ['Had', 'Was', 'Has', 'Did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Past Perfect Passive Question**: Had + subject + been + V3?',
                    'Приклад: Had the plan been approved?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'Why {a1} the meeting been cancelled?',
                'answers' => ['a1' => 'had'],
                'options' => [
                    'a1' => ['had', 'was', 'has', 'did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Past Perfect Passive**: Why + had + subject + been + V3?',
                    'Приклад: Why had the event been postponed?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The patient {a1} before the emergency occurred.',
                'answers' => ['a1' => 'had been admitted'],
                'options' => [
                    'a1' => ['had been admitted', 'was admitted', 'has been admitted', 'had admitted'],
                ],
                'verb_hints' => ['a1' => 'admit'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The victim had been transported.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'How many people {a1} been injured before help arrived?',
                'answers' => ['a1' => 'had'],
                'options' => [
                    'a1' => ['had', 'were', 'have', 'did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Past Perfect Passive**: How many + had + been + V3?',
                    'Приклад: How many had been hurt?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The security systems {a1} before the break-in.',
                'answers' => ['a1' => 'had been upgraded'],
                'options' => [
                    'a1' => ['had been upgraded', 'were upgraded', 'have been upgraded', 'had upgraded'],
                ],
                'verb_hints' => ['a1' => 'upgrade'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The servers had been improved.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The painting {a1} before the auction.',
                'answers' => ['a1' => 'had been sold'],
                'options' => [
                    'a1' => ['had been sold', 'was sold', 'has been sold', 'had sold'],
                ],
                'verb_hints' => ['a1' => 'sell'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The artwork had been purchased.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],

            // ===== B2 Level: 12 questions =====
            [
                'level' => 'B2',
                'question' => 'The legislation {a1} before the crisis erupted.',
                'answers' => ['a1' => 'had been passed'],
                'options' => [
                    'a1' => ['had been passed', 'was passed', 'has been passed', 'had passed'],
                ],
                'verb_hints' => ['a1' => 'pass'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The law had been enacted.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Significant reforms {a1} before the scandal broke.',
                'answers' => ['a1' => 'had been introduced'],
                'options' => [
                    'a1' => ['had been introduced', 'were introduced', 'have been introduced', 'had introduced'],
                ],
                'verb_hints' => ['a1' => 'introduce'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: Changes had been implemented.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The data {a1} before the system crashed.',
                'answers' => ['a1' => 'had been collected'],
                'options' => [
                    'a1' => ['had been collected', 'was collected', 'has been collected', 'had collected'],
                ],
                'verb_hints' => ['a1' => 'collect'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The information had been gathered.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The negotiations {a1} before the deadline.',
                'answers' => ['a1' => 'had been concluded'],
                'options' => [
                    'a1' => ['had been concluded', 'were concluded', 'have been concluded', 'had concluded'],
                ],
                'verb_hints' => ['a1' => 'conclude'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The talks had been completed.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The warnings {a1} before the disaster.',
                'answers' => ['a1' => "hadn't been heeded"],
                'options' => [
                    'a1' => ["hadn't been heeded", "weren't heeded", "haven't been heeded", "hadn't heeded"],
                ],
                'verb_hints' => ['a1' => 'heed'],
                'hints' => [
                    '**Past Perfect Passive Negative**: hadn\'t + been + V3.',
                    'Приклад: The advice hadn\'t been followed.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Adequate resources {a1} before the project failed.',
                'answers' => ['a1' => "hadn't been allocated"],
                'options' => [
                    'a1' => ["hadn't been allocated", "weren't allocated", "haven't been allocated", "hadn't allocated"],
                ],
                'verb_hints' => ['a1' => 'allocate'],
                'hints' => [
                    '**Past Perfect Passive Negative**: hadn\'t + been + V3.',
                    'Приклад: Sufficient funds hadn\'t been provided.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => '{a1} the treaty been ratified before the conflict started?',
                'answers' => ['a1' => 'Had'],
                'options' => [
                    'a1' => ['Had', 'Was', 'Has', 'Did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Past Perfect Passive Question**: Had + subject + been + V3?',
                    'Приклад: Had the agreement been signed?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'By whom {a1} the decision been made?',
                'answers' => ['a1' => 'had'],
                'options' => [
                    'a1' => ['had', 'was', 'has', 'did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Formal Past Perfect Passive Question** з "by whom".',
                    'Приклад: By whom had the order been given?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The policy {a1} before the new government took over.',
                'answers' => ['a1' => 'had been revised'],
                'options' => [
                    'a1' => ['had been revised', 'was revised', 'has been revised', 'had revised'],
                ],
                'verb_hints' => ['a1' => 'revise'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The guidelines had been updated.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The documents {a1} before the audit.',
                'answers' => ['a1' => 'had been distributed'],
                'options' => [
                    'a1' => ['had been distributed', 'were distributed', 'have been distributed', 'had distributed'],
                ],
                'verb_hints' => ['a1' => 'distribute'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The materials had been handed out.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Why {a1} the project been abandoned?',
                'answers' => ['a1' => 'had'],
                'options' => [
                    'a1' => ['had', 'was', 'has', 'did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Past Perfect Passive**: Why + had + subject + been + V3?',
                    'Приклад: Why had the plan been cancelled?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The manuscript {a1} before the author died.',
                'answers' => ['a1' => 'had been rejected'],
                'options' => [
                    'a1' => ['had been rejected', 'was rejected', 'has been rejected', 'had rejected'],
                ],
                'verb_hints' => ['a1' => 'reject'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The article had been declined.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],

            // ===== C1 Level: 12 questions =====
            [
                'level' => 'C1',
                'question' => 'The theoretical framework {a1} before new data emerged.',
                'answers' => ['a1' => 'had been modified'],
                'options' => [
                    'a1' => ['had been modified', 'was modified', 'has been modified', 'had modified'],
                ],
                'verb_hints' => ['a1' => 'modify'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The model had been adjusted.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Constitutional amendments {a1} before the referendum.',
                'answers' => ['a1' => 'had been ratified'],
                'options' => [
                    'a1' => ['had been ratified', 'were ratified', 'have been ratified', 'had ratified'],
                ],
                'verb_hints' => ['a1' => 'ratify'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The reforms had been approved.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The correlation {a1} before the paper was published.',
                'answers' => ['a1' => 'had been established'],
                'options' => [
                    'a1' => ['had been established', 'was established', 'has been established', 'had established'],
                ],
                'verb_hints' => ['a1' => 'establish'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The link had been confirmed.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Indigenous territories {a1} before independence was declared.',
                'answers' => ['a1' => 'had been annexed'],
                'options' => [
                    'a1' => ['had been annexed', 'were annexed', 'have been annexed', 'had annexed'],
                ],
                'verb_hints' => ['a1' => 'annex'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The lands had been seized.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The ethical implications {a1} adequately before the trial began.',
                'answers' => ['a1' => "hadn't been considered"],
                'options' => [
                    'a1' => ["hadn't been considered", "weren't considered", "haven't been considered", "hadn't considered"],
                ],
                'verb_hints' => ['a1' => 'consider'],
                'hints' => [
                    '**Past Perfect Passive Negative**: hadn\'t + been + V3.',
                    'Приклад: The consequences hadn\'t been examined.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The underlying causes {a1} thoroughly before the report.',
                'answers' => ['a1' => "hadn't been investigated"],
                'options' => [
                    'a1' => ["hadn't been investigated", "weren't investigated", "haven't been investigated", "hadn't investigated"],
                ],
                'verb_hints' => ['a1' => 'investigate'],
                'hints' => [
                    '**Past Perfect Passive Negative**: hadn\'t + been + V3.',
                    'Приклад: The root issues hadn\'t been explored.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => '{a1} sufficient safeguards been put in place before the breach?',
                'answers' => ['a1' => 'Had'],
                'options' => [
                    'a1' => ['Had', 'Were', 'Have', 'Did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Past Perfect Passive Question**: Had + subject + been + V3?',
                    'Приклад: Had adequate measures been taken?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'To what extent {a1} international standards been followed?',
                'answers' => ['a1' => 'had'],
                'options' => [
                    'a1' => ['had', 'were', 'have', 'did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Formal WH-Question Past Perfect Passive**.',
                    'Приклад: To what degree had guidelines been adhered to?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The paradigm {a1} by the discovery.',
                'answers' => ['a1' => 'had been transformed'],
                'options' => [
                    'a1' => ['had been transformed', 'was transformed', 'has been transformed', 'had transformed'],
                ],
                'verb_hints' => ['a1' => 'transform'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The field had been revolutionized.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The findings {a1} before the conference.',
                'answers' => ['a1' => 'had been disseminated'],
                'options' => [
                    'a1' => ['had been disseminated', 'were disseminated', 'have been disseminated', 'had disseminated'],
                ],
                'verb_hints' => ['a1' => 'disseminate'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The results had been circulated.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Why {a1} these recommendations been disregarded?',
                'answers' => ['a1' => 'had'],
                'options' => [
                    'a1' => ['had', 'were', 'have', 'did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Past Perfect Passive**: Why + had + subject + been + V3?',
                    'Приклад: Why had the suggestions been ignored?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The hypothesis {a1} before the study concluded.',
                'answers' => ['a1' => 'had been validated'],
                'options' => [
                    'a1' => ['had been validated', 'was validated', 'has been validated', 'had validated'],
                ],
                'verb_hints' => ['a1' => 'validate'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The theory had been confirmed.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],

            // ===== C2 Level: 12 questions =====
            [
                'level' => 'C2',
                'question' => 'The epistemological assumptions {a1} by earlier critics.',
                'answers' => ['a1' => 'had been challenged'],
                'options' => [
                    'a1' => ['had been challenged', 'were challenged', 'have been challenged', 'had challenged'],
                ],
                'verb_hints' => ['a1' => 'challenge'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The foundations had been questioned.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The hegemonic narrative {a1} before postmodernism emerged.',
                'answers' => ['a1' => 'had been dismantled'],
                'options' => [
                    'a1' => ['had been dismantled', 'was dismantled', 'has been dismantled', 'had dismantled'],
                ],
                'verb_hints' => ['a1' => 'dismantle'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The dominant discourse had been deconstructed.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'Novel methodologies {a1} before the paradigm shifted.',
                'answers' => ['a1' => 'had been employed'],
                'options' => [
                    'a1' => ['had been employed', 'were employed', 'have been employed', 'had employed'],
                ],
                'verb_hints' => ['a1' => 'employ'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: Innovative approaches had been applied.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The ontological premises {a1} before the new evidence surfaced.',
                'answers' => ['a1' => 'had been reconsidered'],
                'options' => [
                    'a1' => ['had been reconsidered', 'were reconsidered', 'have been reconsidered', 'had reconsidered'],
                ],
                'verb_hints' => ['a1' => 'reconsider'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The hypotheses had been revised.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The nuances of the argument {a1} in the initial coverage.',
                'answers' => ['a1' => "hadn't been conveyed"],
                'options' => [
                    'a1' => ["hadn't been conveyed", "weren't conveyed", "haven't been conveyed", "hadn't conveyed"],
                ],
                'verb_hints' => ['a1' => 'convey'],
                'hints' => [
                    '**Past Perfect Passive Negative**: hadn\'t + been + V3.',
                    'Приклад: The subtleties hadn\'t been communicated.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The ramifications of the policy {a1} with sufficient rigor.',
                'answers' => ['a1' => "hadn't been analyzed"],
                'options' => [
                    'a1' => ["hadn't been analyzed", "weren't analyzed", "haven't been analyzed", "hadn't analyzed"],
                ],
                'verb_hints' => ['a1' => 'analyze'],
                'hints' => [
                    '**Past Perfect Passive Negative**: hadn\'t + been + V3.',
                    'Приклад: The consequences hadn\'t been evaluated.',
                ],
                'tag_ids' => [$negativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => '{a1} the axioms of this theory been sufficiently interrogated?',
                'answers' => ['a1' => 'Had'],
                'options' => [
                    'a1' => ['Had', 'Were', 'Have', 'Did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Past Perfect Passive Question**: Had + subject + been + V3?',
                    'Приклад: Had the assumptions been tested?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'Through what mechanisms {a1} systemic inequalities been perpetuated?',
                'answers' => ['a1' => 'had'],
                'options' => [
                    'a1' => ['had', 'were', 'have', 'did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Formal WH-Question Past Perfect Passive**.',
                    'Приклад: Through what means had biases been maintained?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The hermeneutical tradition {a1} by new scholarship.',
                'answers' => ['a1' => 'had been enriched'],
                'options' => [
                    'a1' => ['had been enriched', 'was enriched', 'has been enriched', 'had enriched'],
                ],
                'verb_hints' => ['a1' => 'enrich'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The field had been expanded.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'Posthumanist perspectives {a1} into the analysis before the debate.',
                'answers' => ['a1' => 'had been incorporated'],
                'options' => [
                    'a1' => ['had been incorporated', 'were incorporated', 'have been incorporated', 'had incorporated'],
                ],
                'verb_hints' => ['a1' => 'incorporate'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: New theories had been integrated.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The dialectical tensions {a1} before the commentary was written.',
                'answers' => ['a1' => 'had been revealed'],
                'options' => [
                    'a1' => ['had been revealed', 'were revealed', 'have been revealed', 'had revealed'],
                ],
                'verb_hints' => ['a1' => 'reveal'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: The contradictions had been exposed.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The teleological assumptions {a1} by empirical findings.',
                'answers' => ['a1' => 'had been undermined'],
                'options' => [
                    'a1' => ['had been undermined', 'were undermined', 'have been undermined', 'had undermined'],
                ],
                'verb_hints' => ['a1' => 'undermine'],
                'hints' => [
                    '**Past Perfect Passive**: had + been + V3.',
                    'Приклад: Traditional views had been challenged.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastPerfectTagId, $passiveTagId],
            ],
        ];
    }

    private function flattenOptions(array $options): array
    {
        $flat = [];
        foreach ($options as $values) {
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

    private function buildExplanations(array $options, array $answers): array
    {
        $explanations = [];
        foreach ($options as $marker => $values) {
            $correctAnswer = $answers[$marker] ?? '';
            foreach ($values as $value) {
                if ($value === $correctAnswer) {
                    $explanations[$value] = $this->correctExplanation($value);
                } else {
                    $explanations[$value] = $this->wrongExplanation($value, $correctAnswer);
                }
            }
        }
        return $explanations;
    }

    private function correctExplanation(string $correct): string
    {
        return "✅ Правильно! «{$correct}» — це коректна форма Past Perfect Passive. Формула: had + been + past participle (V3).";
    }

    private function wrongExplanation(string $wrong, string $correct): string
    {
        // Past Simple instead of Past Perfect
        if (str_contains(strtolower($correct), 'had been')
            && (str_contains(strtolower($wrong), 'was ') || str_contains(strtolower($wrong), 'were '))) {
            return "❌ «{$wrong}» — це Past Simple Passive. Для Past Perfect Passive потрібен had + been + V3.";
        }

        // Present Perfect instead of Past Perfect
        if (str_contains(strtolower($correct), 'had been')
            && (str_contains(strtolower($wrong), 'has been') || str_contains(strtolower($wrong), 'have been'))) {
            return "❌ «{$wrong}» — це Present Perfect Passive. Для Past Perfect Passive потрібен had + been + V3.";
        }

        // Active voice
        if (str_contains(strtolower($correct), 'had been') && str_contains(strtolower($wrong), 'had ')
            && !str_contains(strtolower($wrong), 'had been')) {
            return "❌ «{$wrong}» — це Active Voice. Тут потрібен Passive Voice: had + been + V3.";
        }

        // Did instead of had
        if (str_contains(strtolower($wrong), 'did')) {
            return "❌ «{$wrong}» — did використовується в Active Voice. У Passive Voice потрібен had + been.";
        }

        return "❌ «{$wrong}» — неправильна форма. Для Past Perfect Passive потрібно: had + been + past participle.";
    }
}
