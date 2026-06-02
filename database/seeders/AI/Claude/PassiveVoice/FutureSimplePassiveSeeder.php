<?php

namespace Database\Seeders\AI\Claude\PassiveVoice;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class FutureSimplePassiveSeeder extends QuestionSeeder
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
        $sourceId = Source::firstOrCreate(['name' => 'AI Generated: Future Simple Passive'])->id;
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
            ['name' => 'Future Simple Passive'],
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

        $futureSimpleTagId = Tag::firstOrCreate(['name' => 'Future Simple'], ['category' => 'Tense'])->id;
        $passiveTagId = Tag::firstOrCreate(['name' => 'Passive Voice'], ['category' => 'Voice'])->id;

        return [
            // ===== A1 Level: 12 questions =====
            [
                'level' => 'A1',
                'question' => 'The house {a1} next month.',
                'answers' => ['a1' => 'will be painted'],
                'options' => [
                    'a1' => ['will be painted', 'is painted', 'was painted', 'will paint'],
                ],
                'verb_hints' => ['a1' => 'paint'],
                'hints' => [
                    '**Future Simple Passive** утворюється: will + be + past participle (V3).',
                    'Використовується для дій, що відбудуться в майбутньому.',
                    'Приклад: The room will be cleaned tomorrow.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The letters {a1} tomorrow.',
                'answers' => ['a1' => 'will be sent'],
                'options' => [
                    'a1' => ['will be sent', 'are sent', 'were sent', 'will send'],
                ],
                'verb_hints' => ['a1' => 'send'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The packages will be delivered.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The cake {a1} for the party.',
                'answers' => ['a1' => 'will be made'],
                'options' => [
                    'a1' => ['will be made', 'is made', 'was made', 'will make'],
                ],
                'verb_hints' => ['a1' => 'make'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The meal will be prepared.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The homework {a1} by Friday.',
                'answers' => ['a1' => 'will be finished'],
                'options' => [
                    'a1' => ['will be finished', 'is finished', 'was finished', 'will finish'],
                ],
                'verb_hints' => ['a1' => 'finish'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The task will be completed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The books {a1} to the library.',
                'answers' => ['a1' => 'will be returned'],
                'options' => [
                    'a1' => ['will be returned', 'are returned', 'were returned', 'will return'],
                ],
                'verb_hints' => ['a1' => 'return'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The documents will be submitted.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The room {a1} before the guests arrive.',
                'answers' => ['a1' => 'will be cleaned'],
                'options' => [
                    'a1' => ['will be cleaned', 'is cleaned', 'was cleaned', 'will clean'],
                ],
                'verb_hints' => ['a1' => 'clean'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The floor will be mopped.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The car {a1} today.',
                'answers' => ['a1' => "won't be washed"],
                'options' => [
                    'a1' => ["won't be washed", "isn't washed", "wasn't washed", "won't wash"],
                ],
                'verb_hints' => ['a1' => 'wash'],
                'hints' => [
                    '**Future Simple Passive Negative**: will not (won\'t) + be + V3.',
                    'Приклад: The dishes won\'t be done.',
                ],
                'tag_ids' => [$negativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The tickets {a1} online.',
                'answers' => ['a1' => "won't be bought"],
                'options' => [
                    'a1' => ["won't be bought", "aren't bought", "weren't bought", "won't buy"],
                ],
                'verb_hints' => ['a1' => 'buy'],
                'hints' => [
                    '**Future Simple Passive Negative**: won\'t + be + V3.',
                    'Приклад: The reservations won\'t be made.',
                ],
                'tag_ids' => [$negativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => '{a1} the door be locked tonight?',
                'answers' => ['a1' => 'Will'],
                'options' => [
                    'a1' => ['Will', 'Is', 'Was', 'Does'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Future Simple Passive Question**: Will + subject + be + V3?',
                    'Приклад: Will the window be closed?',
                ],
                'tag_ids' => [$interrogativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => '{a1} the flowers be watered tomorrow?',
                'answers' => ['a1' => 'Will'],
                'options' => [
                    'a1' => ['Will', 'Are', 'Were', 'Do'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Future Simple Passive Question**: Will + subject + be + V3?',
                    'Приклад: Will the plants be cared for?',
                ],
                'tag_ids' => [$interrogativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The dog {a1} at the vet.',
                'answers' => ['a1' => 'will be examined'],
                'options' => [
                    'a1' => ['will be examined', 'is examined', 'was examined', 'will examine'],
                ],
                'verb_hints' => ['a1' => 'examine'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The cat will be treated.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'I {a1} about the results.',
                'answers' => ['a1' => 'will be told'],
                'options' => [
                    'a1' => ['will be told', 'am told', 'was told', 'will tell'],
                ],
                'verb_hints' => ['a1' => 'tell'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: I will be informed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],

            // ===== A2 Level: 12 questions =====
            [
                'level' => 'A2',
                'question' => 'The new bridge {a1} next year.',
                'answers' => ['a1' => 'will be built'],
                'options' => [
                    'a1' => ['will be built', 'is built', 'was built', 'will build'],
                ],
                'verb_hints' => ['a1' => 'build'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The road will be constructed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The students {a1} for the exam.',
                'answers' => ['a1' => 'will be prepared'],
                'options' => [
                    'a1' => ['will be prepared', 'are prepared', 'were prepared', 'will prepare'],
                ],
                'verb_hints' => ['a1' => 'prepare'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The candidates will be trained.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The movie {a1} in Hollywood.',
                'answers' => ['a1' => 'will be filmed'],
                'options' => [
                    'a1' => ['will be filmed', 'is filmed', 'was filmed', 'will film'],
                ],
                'verb_hints' => ['a1' => 'film'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The show will be recorded.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The guests {a1} to the dinner.',
                'answers' => ['a1' => 'will be invited'],
                'options' => [
                    'a1' => ['will be invited', 'are invited', 'were invited', 'will invite'],
                ],
                'verb_hints' => ['a1' => 'invite'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The visitors will be welcomed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The report {a1} before the deadline.',
                'answers' => ['a1' => 'will be submitted'],
                'options' => [
                    'a1' => ['will be submitted', 'is submitted', 'was submitted', 'will submit'],
                ],
                'verb_hints' => ['a1' => 'submit'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The application will be filed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The thief {a1} easily.',
                'answers' => ['a1' => "won't be caught"],
                'options' => [
                    'a1' => ["won't be caught", "isn't caught", "wasn't caught", "won't catch"],
                ],
                'verb_hints' => ['a1' => 'catch'],
                'hints' => [
                    '**Future Simple Passive Negative**: won\'t + be + V3.',
                    'Приклад: The criminal won\'t be arrested.',
                ],
                'tag_ids' => [$negativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The rules {a1} to everyone.',
                'answers' => ['a1' => "won't be explained"],
                'options' => [
                    'a1' => ["won't be explained", "aren't explained", "weren't explained", "won't explain"],
                ],
                'verb_hints' => ['a1' => 'explain'],
                'hints' => [
                    '**Future Simple Passive Negative**: won\'t + be + V3.',
                    'Приклад: The instructions won\'t be given.',
                ],
                'tag_ids' => [$negativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => '{a1} the project be completed on time?',
                'answers' => ['a1' => 'Will'],
                'options' => [
                    'a1' => ['Will', 'Is', 'Was', 'Does'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Future Simple Passive Question**: Will + subject + be + V3?',
                    'Приклад: Will the task be finished?',
                ],
                'tag_ids' => [$interrogativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'When {a1} the new building be opened?',
                'answers' => ['a1' => 'will'],
                'options' => [
                    'a1' => ['will', 'is', 'was', 'does'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Future Simple Passive**: When + will + subject + be + V3?',
                    'Приклад: When will the store be opened?',
                ],
                'tag_ids' => [$interrogativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The song {a1} on the radio.',
                'answers' => ['a1' => 'will be played'],
                'options' => [
                    'a1' => ['will be played', 'is played', 'was played', 'will play'],
                ],
                'verb_hints' => ['a1' => 'play'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The music will be broadcast.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'Where {a1} the meeting be held?',
                'answers' => ['a1' => 'will'],
                'options' => [
                    'a1' => ['will', 'is', 'was', 'does'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Future Simple Passive**: Where + will + subject + be + V3?',
                    'Приклад: Where will the event be organized?',
                ],
                'tag_ids' => [$interrogativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The old factory {a1} soon.',
                'answers' => ['a1' => 'will be demolished'],
                'options' => [
                    'a1' => ['will be demolished', 'is demolished', 'was demolished', 'will demolish'],
                ],
                'verb_hints' => ['a1' => 'demolish'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The building will be torn down.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],

            // ===== B1 Level: 12 questions =====
            [
                'level' => 'B1',
                'question' => 'The contract {a1} by both parties next week.',
                'answers' => ['a1' => 'will be signed'],
                'options' => [
                    'a1' => ['will be signed', 'is signed', 'was signed', 'will sign'],
                ],
                'verb_hints' => ['a1' => 'sign'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The agreement will be finalized.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'Several employees {a1} for the new positions.',
                'answers' => ['a1' => 'will be hired'],
                'options' => [
                    'a1' => ['will be hired', 'are hired', 'were hired', 'will hire'],
                ],
                'verb_hints' => ['a1' => 'hire'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: New staff will be recruited.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The research {a1} by the end of the year.',
                'answers' => ['a1' => 'will be completed'],
                'options' => [
                    'a1' => ['will be completed', 'is completed', 'was completed', 'will complete'],
                ],
                'verb_hints' => ['a1' => 'complete'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The study will be finished.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The patients {a1} with the new treatment.',
                'answers' => ['a1' => 'will be treated'],
                'options' => [
                    'a1' => ['will be treated', 'are treated', 'were treated', 'will treat'],
                ],
                'verb_hints' => ['a1' => 'treat'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The injured will be helped.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The deadline {a1} this time.',
                'answers' => ['a1' => "won't be extended"],
                'options' => [
                    'a1' => ["won't be extended", "isn't extended", "wasn't extended", "won't extend"],
                ],
                'verb_hints' => ['a1' => 'extend'],
                'hints' => [
                    '**Future Simple Passive Negative**: won\'t + be + V3.',
                    'Приклад: The date won\'t be changed.',
                ],
                'tag_ids' => [$negativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The candidates {a1} about the results immediately.',
                'answers' => ['a1' => "won't be informed"],
                'options' => [
                    'a1' => ["won't be informed", "aren't informed", "weren't informed", "won't inform"],
                ],
                'verb_hints' => ['a1' => 'inform'],
                'hints' => [
                    '**Future Simple Passive Negative**: won\'t + be + V3.',
                    'Приклад: The employees won\'t be notified.',
                ],
                'tag_ids' => [$negativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => '{a1} the proposal be accepted by the board?',
                'answers' => ['a1' => 'Will'],
                'options' => [
                    'a1' => ['Will', 'Is', 'Was', 'Does'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Future Simple Passive Question**: Will + subject + be + V3?',
                    'Приклад: Will the plan be approved?',
                ],
                'tag_ids' => [$interrogativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'Why {a1} the event be cancelled?',
                'answers' => ['a1' => 'will'],
                'options' => [
                    'a1' => ['will', 'is', 'was', 'does'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Future Simple Passive**: Why + will + subject + be + V3?',
                    'Приклад: Why will the meeting be postponed?',
                ],
                'tag_ids' => [$interrogativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The patient {a1} to the specialist.',
                'answers' => ['a1' => 'will be referred'],
                'options' => [
                    'a1' => ['will be referred', 'is referred', 'was referred', 'will refer'],
                ],
                'verb_hints' => ['a1' => 'refer'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The case will be transferred.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'How many people {a1} be affected by the changes?',
                'answers' => ['a1' => 'will'],
                'options' => [
                    'a1' => ['will', 'are', 'were', 'do'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Future Simple Passive**: How many + will + be + V3?',
                    'Приклад: How many will be impacted?',
                ],
                'tag_ids' => [$interrogativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The security systems {a1} next month.',
                'answers' => ['a1' => 'will be upgraded'],
                'options' => [
                    'a1' => ['will be upgraded', 'are upgraded', 'were upgraded', 'will upgrade'],
                ],
                'verb_hints' => ['a1' => 'upgrade'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The servers will be improved.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The products {a1} in stores worldwide.',
                'answers' => ['a1' => 'will be sold'],
                'options' => [
                    'a1' => ['will be sold', 'are sold', 'were sold', 'will sell'],
                ],
                'verb_hints' => ['a1' => 'sell'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The items will be distributed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],

            // ===== B2 Level: 12 questions =====
            [
                'level' => 'B2',
                'question' => 'The legislation {a1} by parliament next session.',
                'answers' => ['a1' => 'will be passed'],
                'options' => [
                    'a1' => ['will be passed', 'is passed', 'was passed', 'will pass'],
                ],
                'verb_hints' => ['a1' => 'pass'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The law will be enacted.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Significant reforms {a1} in the coming years.',
                'answers' => ['a1' => 'will be introduced'],
                'options' => [
                    'a1' => ['will be introduced', 'are introduced', 'were introduced', 'will introduce'],
                ],
                'verb_hints' => ['a1' => 'introduce'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: Changes will be implemented.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The data {a1} before the analysis begins.',
                'answers' => ['a1' => 'will be collected'],
                'options' => [
                    'a1' => ['will be collected', 'is collected', 'was collected', 'will collect'],
                ],
                'verb_hints' => ['a1' => 'collect'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The information will be gathered.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The negotiations {a1} by the end of the month.',
                'answers' => ['a1' => 'will be concluded'],
                'options' => [
                    'a1' => ['will be concluded', 'are concluded', 'were concluded', 'will conclude'],
                ],
                'verb_hints' => ['a1' => 'conclude'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The talks will be completed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The warnings {a1} this time.',
                'answers' => ['a1' => "won't be ignored"],
                'options' => [
                    'a1' => ["won't be ignored", "aren't ignored", "weren't ignored", "won't ignore"],
                ],
                'verb_hints' => ['a1' => 'ignore'],
                'hints' => [
                    '**Future Simple Passive Negative**: won\'t + be + V3.',
                    'Приклад: The advice won\'t be disregarded.',
                ],
                'tag_ids' => [$negativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Adequate resources {a1} for the project.',
                'answers' => ['a1' => "won't be allocated"],
                'options' => [
                    'a1' => ["won't be allocated", "aren't allocated", "weren't allocated", "won't allocate"],
                ],
                'verb_hints' => ['a1' => 'allocate'],
                'hints' => [
                    '**Future Simple Passive Negative**: won\'t + be + V3.',
                    'Приклад: Sufficient funds won\'t be provided.',
                ],
                'tag_ids' => [$negativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => '{a1} the treaty be ratified by all member states?',
                'answers' => ['a1' => 'Will'],
                'options' => [
                    'a1' => ['Will', 'Is', 'Was', 'Does'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Future Simple Passive Question**: Will + subject + be + V3?',
                    'Приклад: Will the agreement be signed?',
                ],
                'tag_ids' => [$interrogativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'By whom {a1} the decision be made?',
                'answers' => ['a1' => 'will'],
                'options' => [
                    'a1' => ['will', 'is', 'was', 'does'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Formal Future Simple Passive Question** з "by whom".',
                    'Приклад: By whom will the order be given?',
                ],
                'tag_ids' => [$interrogativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The policy {a1} to reflect new priorities.',
                'answers' => ['a1' => 'will be revised'],
                'options' => [
                    'a1' => ['will be revised', 'is revised', 'was revised', 'will revise'],
                ],
                'verb_hints' => ['a1' => 'revise'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The guidelines will be updated.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The documents {a1} before the audit.',
                'answers' => ['a1' => 'will be distributed'],
                'options' => [
                    'a1' => ['will be distributed', 'are distributed', 'were distributed', 'will distribute'],
                ],
                'verb_hints' => ['a1' => 'distribute'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The materials will be handed out.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Why {a1} the project be abandoned?',
                'answers' => ['a1' => "won't"],
                'options' => [
                    'a1' => ["won't", "isn't", "wasn't", "doesn't"],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Negative WH-Question Future Simple Passive**: Why + won\'t + subject + be + V3?',
                    'Приклад: Why won\'t the plan be continued?',
                ],
                'tag_ids' => [$interrogativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The manuscript {a1} for publication.',
                'answers' => ['a1' => 'will be accepted'],
                'options' => [
                    'a1' => ['will be accepted', 'is accepted', 'was accepted', 'will accept'],
                ],
                'verb_hints' => ['a1' => 'accept'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The article will be approved.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],

            // ===== C1 Level: 12 questions =====
            [
                'level' => 'C1',
                'question' => 'The theoretical framework {a1} to incorporate new findings.',
                'answers' => ['a1' => 'will be modified'],
                'options' => [
                    'a1' => ['will be modified', 'is modified', 'was modified', 'will modify'],
                ],
                'verb_hints' => ['a1' => 'modify'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The model will be adjusted.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Constitutional amendments {a1} by the legislature.',
                'answers' => ['a1' => 'will be ratified'],
                'options' => [
                    'a1' => ['will be ratified', 'are ratified', 'were ratified', 'will ratify'],
                ],
                'verb_hints' => ['a1' => 'ratify'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The reforms will be approved.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The correlation {a1} through further research.',
                'answers' => ['a1' => 'will be established'],
                'options' => [
                    'a1' => ['will be established', 'is established', 'was established', 'will establish'],
                ],
                'verb_hints' => ['a1' => 'establish'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The link will be confirmed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Indigenous rights {a1} by new legislation.',
                'answers' => ['a1' => 'will be protected'],
                'options' => [
                    'a1' => ['will be protected', 'are protected', 'were protected', 'will protect'],
                ],
                'verb_hints' => ['a1' => 'protect'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: Cultural heritage will be preserved.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The ethical implications {a1} in this approach.',
                'answers' => ['a1' => "won't be considered"],
                'options' => [
                    'a1' => ["won't be considered", "aren't considered", "weren't considered", "won't consider"],
                ],
                'verb_hints' => ['a1' => 'consider'],
                'hints' => [
                    '**Future Simple Passive Negative**: won\'t + be + V3.',
                    'Приклад: The consequences won\'t be examined.',
                ],
                'tag_ids' => [$negativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The underlying causes {a1} thoroughly.',
                'answers' => ['a1' => "won't be investigated"],
                'options' => [
                    'a1' => ["won't be investigated", "aren't investigated", "weren't investigated", "won't investigate"],
                ],
                'verb_hints' => ['a1' => 'investigate'],
                'hints' => [
                    '**Future Simple Passive Negative**: won\'t + be + V3.',
                    'Приклад: The root issues won\'t be explored.',
                ],
                'tag_ids' => [$negativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => '{a1} sufficient safeguards be put in place?',
                'answers' => ['a1' => 'Will'],
                'options' => [
                    'a1' => ['Will', 'Are', 'Were', 'Do'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Future Simple Passive Question**: Will + subject + be + V3?',
                    'Приклад: Will adequate measures be taken?',
                ],
                'tag_ids' => [$interrogativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'To what extent {a1} international standards be followed?',
                'answers' => ['a1' => 'will'],
                'options' => [
                    'a1' => ['will', 'are', 'were', 'do'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Formal WH-Question Future Simple Passive**.',
                    'Приклад: To what degree will guidelines be adhered to?',
                ],
                'tag_ids' => [$interrogativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The paradigm {a1} by this discovery.',
                'answers' => ['a1' => 'will be transformed'],
                'options' => [
                    'a1' => ['will be transformed', 'is transformed', 'was transformed', 'will transform'],
                ],
                'verb_hints' => ['a1' => 'transform'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The field will be revolutionized.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The findings {a1} throughout the scientific community.',
                'answers' => ['a1' => 'will be disseminated'],
                'options' => [
                    'a1' => ['will be disseminated', 'are disseminated', 'were disseminated', 'will disseminate'],
                ],
                'verb_hints' => ['a1' => 'disseminate'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The results will be circulated.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Why {a1} these recommendations be disregarded?',
                'answers' => ['a1' => 'will'],
                'options' => [
                    'a1' => ['will', 'are', 'were', 'do'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Future Simple Passive**: Why + will + subject + be + V3?',
                    'Приклад: Why will the suggestions be ignored?',
                ],
                'tag_ids' => [$interrogativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The hypothesis {a1} through rigorous testing.',
                'answers' => ['a1' => 'will be validated'],
                'options' => [
                    'a1' => ['will be validated', 'is validated', 'was validated', 'will validate'],
                ],
                'verb_hints' => ['a1' => 'validate'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The theory will be confirmed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],

            // ===== C2 Level: 12 questions =====
            [
                'level' => 'C2',
                'question' => 'The epistemological assumptions {a1} by future scholars.',
                'answers' => ['a1' => 'will be challenged'],
                'options' => [
                    'a1' => ['will be challenged', 'are challenged', 'were challenged', 'will challenge'],
                ],
                'verb_hints' => ['a1' => 'challenge'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The foundations will be questioned.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The hegemonic narrative {a1} through critical analysis.',
                'answers' => ['a1' => 'will be dismantled'],
                'options' => [
                    'a1' => ['will be dismantled', 'is dismantled', 'was dismantled', 'will dismantle'],
                ],
                'verb_hints' => ['a1' => 'dismantle'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The dominant discourse will be deconstructed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'Novel methodologies {a1} in future research.',
                'answers' => ['a1' => 'will be employed'],
                'options' => [
                    'a1' => ['will be employed', 'are employed', 'were employed', 'will employ'],
                ],
                'verb_hints' => ['a1' => 'employ'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: Innovative approaches will be applied.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The ontological premises {a1} as more evidence emerges.',
                'answers' => ['a1' => 'will be reconsidered'],
                'options' => [
                    'a1' => ['will be reconsidered', 'are reconsidered', 'were reconsidered', 'will reconsider'],
                ],
                'verb_hints' => ['a1' => 'reconsider'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The hypotheses will be revised.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The nuances of the argument {a1} in simplified accounts.',
                'answers' => ['a1' => "won't be conveyed"],
                'options' => [
                    'a1' => ["won't be conveyed", "aren't conveyed", "weren't conveyed", "won't convey"],
                ],
                'verb_hints' => ['a1' => 'convey'],
                'hints' => [
                    '**Future Simple Passive Negative**: won\'t + be + V3.',
                    'Приклад: The subtleties won\'t be communicated.',
                ],
                'tag_ids' => [$negativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The ramifications of the policy {a1} with sufficient rigor.',
                'answers' => ['a1' => "won't be analyzed"],
                'options' => [
                    'a1' => ["won't be analyzed", "aren't analyzed", "weren't analyzed", "won't analyze"],
                ],
                'verb_hints' => ['a1' => 'analyze'],
                'hints' => [
                    '**Future Simple Passive Negative**: won\'t + be + V3.',
                    'Приклад: The consequences won\'t be evaluated.',
                ],
                'tag_ids' => [$negativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => '{a1} the axioms of this theory be interrogated sufficiently?',
                'answers' => ['a1' => 'Will'],
                'options' => [
                    'a1' => ['Will', 'Are', 'Were', 'Do'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Future Simple Passive Question**: Will + subject + be + V3?',
                    'Приклад: Will the assumptions be tested?',
                ],
                'tag_ids' => [$interrogativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'Through what mechanisms {a1} systemic inequalities be perpetuated?',
                'answers' => ['a1' => 'will'],
                'options' => [
                    'a1' => ['will', 'are', 'were', 'do'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Formal WH-Question Future Simple Passive**.',
                    'Приклад: Through what means will biases be maintained?',
                ],
                'tag_ids' => [$interrogativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The hermeneutical tradition {a1} by new scholarship.',
                'answers' => ['a1' => 'will be enriched'],
                'options' => [
                    'a1' => ['will be enriched', 'is enriched', 'was enriched', 'will enrich'],
                ],
                'verb_hints' => ['a1' => 'enrich'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The field will be expanded.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'Posthumanist perspectives {a1} into future analyses.',
                'answers' => ['a1' => 'will be incorporated'],
                'options' => [
                    'a1' => ['will be incorporated', 'are incorporated', 'were incorporated', 'will incorporate'],
                ],
                'verb_hints' => ['a1' => 'incorporate'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: New theories will be integrated.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The dialectical tensions {a1} in forthcoming studies.',
                'answers' => ['a1' => 'will be explored'],
                'options' => [
                    'a1' => ['will be explored', 'are explored', 'were explored', 'will explore'],
                ],
                'verb_hints' => ['a1' => 'explore'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: The contradictions will be examined.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The teleological assumptions {a1} by empirical research.',
                'answers' => ['a1' => 'will be undermined'],
                'options' => [
                    'a1' => ['will be undermined', 'are undermined', 'were undermined', 'will undermine'],
                ],
                'verb_hints' => ['a1' => 'undermine'],
                'hints' => [
                    '**Future Simple Passive**: will + be + V3.',
                    'Приклад: Traditional views will be challenged.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureSimpleTagId, $passiveTagId],
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
        return "✅ Правильно! «{$correct}» — це коректна форма Future Simple Passive. Формула: will + be + past participle (V3).";
    }

    private function wrongExplanation(string $wrong, string $correct): string
    {
        // Present instead of Future
        if (str_contains(strtolower($correct), 'will be')
            && (str_contains(strtolower($wrong), 'is ') || str_contains(strtolower($wrong), 'are '))) {
            return "❌ «{$wrong}» — це Present Simple Passive. Для Future Simple Passive потрібен will + be + V3.";
        }

        // Past instead of Future
        if (str_contains(strtolower($correct), 'will be')
            && (str_contains(strtolower($wrong), 'was ') || str_contains(strtolower($wrong), 'were '))) {
            return "❌ «{$wrong}» — це Past Simple Passive. Для Future Simple Passive потрібен will + be + V3.";
        }

        // Active voice
        if (str_contains(strtolower($correct), 'will be') && str_contains(strtolower($wrong), 'will ')
            && !str_contains(strtolower($wrong), 'will be')) {
            return "❌ «{$wrong}» — це Active Voice. Тут потрібен Passive Voice: will + be + V3.";
        }

        // Does instead of will
        if (str_contains(strtolower($wrong), 'does') || str_contains(strtolower($wrong), 'do ')) {
            return "❌ «{$wrong}» — do/does використовується в Active Voice. У Future Passive потрібен will + be.";
        }

        return "❌ «{$wrong}» — неправильна форма. Для Future Simple Passive потрібно: will + be + past participle.";
    }
}
