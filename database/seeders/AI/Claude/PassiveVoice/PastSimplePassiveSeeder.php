<?php

namespace Database\Seeders\AI\Claude\PassiveVoice;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class PastSimplePassiveSeeder extends QuestionSeeder
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
        $sourceId = Source::firstOrCreate(['name' => 'AI Generated: Past Simple Passive'])->id;
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
            ['name' => 'Past Simple Passive'],
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

        $pastSimpleTagId = Tag::firstOrCreate(['name' => 'Past Simple'], ['category' => 'Tense'])->id;
        $passiveTagId = Tag::firstOrCreate(['name' => 'Passive Voice'], ['category' => 'Voice'])->id;

        return [
            // ===== A1 Level: 12 questions =====
            [
                'level' => 'A1',
                'question' => 'The window {a1} yesterday.',
                'answers' => ['a1' => 'was broken'],
                'options' => [
                    'a1' => ['was broken', 'were broken', 'is broken', 'broke'],
                ],
                'verb_hints' => ['a1' => 'break'],
                'hints' => [
                    '**Past Simple Passive** утворюється: was/were + past participle (V3).',
                    'Використовується для завершених дій у минулому.',
                    'Приклад: The door was opened.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The letters {a1} last week.',
                'answers' => ['a1' => 'were sent'],
                'options' => [
                    'a1' => ['were sent', 'was sent', 'are sent', 'sent'],
                ],
                'verb_hints' => ['a1' => 'send'],
                'hints' => [
                    '**Past Simple Passive** для множини: were + V3.',
                    'Приклад: The packages were delivered.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The cake {a1} by my grandmother.',
                'answers' => ['a1' => 'was made'],
                'options' => [
                    'a1' => ['was made', 'were made', 'is made', 'made'],
                ],
                'verb_hints' => ['a1' => 'make'],
                'hints' => [
                    '**Past Simple Passive** з агентом: was + V3 + by.',
                    'Приклад: The meal was prepared by the chef.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The homework {a1} on time.',
                'answers' => ['a1' => 'was finished'],
                'options' => [
                    'a1' => ['was finished', 'were finished', 'is finished', 'finished'],
                ],
                'verb_hints' => ['a1' => 'finish'],
                'hints' => [
                    '**Past Simple Passive**: was + V3.',
                    'Приклад: The task was completed.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The books {a1} to the library.',
                'answers' => ['a1' => 'were returned'],
                'options' => [
                    'a1' => ['were returned', 'was returned', 'are returned', 'returned'],
                ],
                'verb_hints' => ['a1' => 'return'],
                'hints' => [
                    '**Past Simple Passive** для множини: were + V3.',
                    'Приклад: The documents were submitted.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The room {a1} this morning.',
                'answers' => ['a1' => 'was cleaned'],
                'options' => [
                    'a1' => ['was cleaned', 'were cleaned', 'is cleaned', 'cleaned'],
                ],
                'verb_hints' => ['a1' => 'clean'],
                'hints' => [
                    '**Past Simple Passive**: was + V3.',
                    'Приклад: The floor was mopped.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The car {a1} yesterday.',
                'answers' => ['a1' => "wasn't washed"],
                'options' => [
                    'a1' => ["wasn't washed", "weren't washed", "isn't washed", "didn't wash"],
                ],
                'verb_hints' => ['a1' => 'wash'],
                'hints' => [
                    '**Past Simple Passive Negative**: was not (wasn\'t) + V3.',
                    'Приклад: The dishes weren\'t cleaned.',
                ],
                'tag_ids' => [$negativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The tickets {a1} in time.',
                'answers' => ['a1' => "weren't bought"],
                'options' => [
                    'a1' => ["weren't bought", "wasn't bought", "aren't bought", "didn't buy"],
                ],
                'verb_hints' => ['a1' => 'buy'],
                'hints' => [
                    '**Past Simple Passive Negative** для множини: were not (weren\'t) + V3.',
                    'Приклад: The reservations weren\'t made.',
                ],
                'tag_ids' => [$negativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => '{a1} the door closed?',
                'answers' => ['a1' => 'Was'],
                'options' => [
                    'a1' => ['Was', 'Were', 'Is', 'Did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Past Simple Passive Question**: Was/Were + subject + V3?',
                    'Приклад: Was the window opened?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => '{a1} the flowers watered?',
                'answers' => ['a1' => 'Were'],
                'options' => [
                    'a1' => ['Were', 'Was', 'Are', 'Did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Past Simple Passive Question** для множини: Were + subject + V3?',
                    'Приклад: Were the plants cared for?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The dog {a1} in the park.',
                'answers' => ['a1' => 'was found'],
                'options' => [
                    'a1' => ['was found', 'were found', 'is found', 'found'],
                ],
                'verb_hints' => ['a1' => 'find'],
                'hints' => [
                    '**Past Simple Passive**: was + V3.',
                    'Приклад: The cat was discovered.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'I {a1} about the party.',
                'answers' => ['a1' => 'was told'],
                'options' => [
                    'a1' => ['was told', 'were told', 'am told', 'told'],
                ],
                'verb_hints' => ['a1' => 'tell'],
                'hints' => [
                    '**Past Simple Passive** з "I": was + V3.',
                    'Приклад: I was informed.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],

            // ===== A2 Level: 12 questions =====
            [
                'level' => 'A2',
                'question' => 'The building {a1} in 1990.',
                'answers' => ['a1' => 'was built'],
                'options' => [
                    'a1' => ['was built', 'were built', 'is built', 'built'],
                ],
                'verb_hints' => ['a1' => 'build'],
                'hints' => [
                    '**Past Simple Passive** для історичних дат.',
                    'Приклад: The bridge was constructed in 1985.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The students {a1} for the exam.',
                'answers' => ['a1' => 'were prepared'],
                'options' => [
                    'a1' => ['were prepared', 'was prepared', 'are prepared', 'prepared'],
                ],
                'verb_hints' => ['a1' => 'prepare'],
                'hints' => [
                    '**Past Simple Passive** для множини: were + V3.',
                    'Приклад: The candidates were trained.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The movie {a1} by Steven Spielberg.',
                'answers' => ['a1' => 'was directed'],
                'options' => [
                    'a1' => ['was directed', 'were directed', 'is directed', 'directed'],
                ],
                'verb_hints' => ['a1' => 'direct'],
                'hints' => [
                    '**Past Simple Passive** з агентом: was + V3 + by.',
                    'Приклад: The book was written by Hemingway.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The guests {a1} at the hotel.',
                'answers' => ['a1' => 'were welcomed'],
                'options' => [
                    'a1' => ['were welcomed', 'was welcomed', 'are welcomed', 'welcomed'],
                ],
                'verb_hints' => ['a1' => 'welcome'],
                'hints' => [
                    '**Past Simple Passive** для множини: were + V3.',
                    'Приклад: The visitors were greeted warmly.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The report {a1} to the manager.',
                'answers' => ['a1' => 'was submitted'],
                'options' => [
                    'a1' => ['was submitted', 'were submitted', 'is submitted', 'submitted'],
                ],
                'verb_hints' => ['a1' => 'submit'],
                'hints' => [
                    '**Past Simple Passive**: was + V3.',
                    'Приклад: The application was filed.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The thief {a1} by the police.',
                'answers' => ['a1' => "wasn't caught"],
                'options' => [
                    'a1' => ["wasn't caught", "weren't caught", "isn't caught", "didn't catch"],
                ],
                'verb_hints' => ['a1' => 'catch'],
                'hints' => [
                    '**Past Simple Passive Negative**: wasn\'t + V3.',
                    'Приклад: The criminal wasn\'t arrested.',
                ],
                'tag_ids' => [$negativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The rules {a1} properly.',
                'answers' => ['a1' => "weren't explained"],
                'options' => [
                    'a1' => ["weren't explained", "wasn't explained", "aren't explained", "didn't explain"],
                ],
                'verb_hints' => ['a1' => 'explain'],
                'hints' => [
                    '**Past Simple Passive Negative** для множини: weren\'t + V3.',
                    'Приклад: The instructions weren\'t given clearly.',
                ],
                'tag_ids' => [$negativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => '{a1} the project completed on time?',
                'answers' => ['a1' => 'Was'],
                'options' => [
                    'a1' => ['Was', 'Were', 'Is', 'Did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Past Simple Passive Question**: Was + subject + V3?',
                    'Приклад: Was the task finished?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'When {a1} the pyramids built?',
                'answers' => ['a1' => 'were'],
                'options' => [
                    'a1' => ['were', 'was', 'are', 'did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Past Simple Passive**: When + were + subject + V3?',
                    'Приклад: When was the castle constructed?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The song {a1} by millions of people.',
                'answers' => ['a1' => 'was heard'],
                'options' => [
                    'a1' => ['was heard', 'were heard', 'is heard', 'heard'],
                ],
                'verb_hints' => ['a1' => 'hear'],
                'hints' => [
                    '**Past Simple Passive** з агентом.',
                    'Приклад: The speech was listened to by thousands.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'Where {a1} the treasure hidden?',
                'answers' => ['a1' => 'was'],
                'options' => [
                    'a1' => ['was', 'were', 'is', 'did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Past Simple Passive**: Where + was + subject + V3?',
                    'Приклад: Where was the key kept?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The old factory {a1} last month.',
                'answers' => ['a1' => 'was demolished'],
                'options' => [
                    'a1' => ['was demolished', 'were demolished', 'is demolished', 'demolished'],
                ],
                'verb_hints' => ['a1' => 'demolish'],
                'hints' => [
                    '**Past Simple Passive** для нещодавніх подій.',
                    'Приклад: The building was torn down.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],

            // ===== B1 Level: 12 questions =====
            [
                'level' => 'B1',
                'question' => 'The contract {a1} by both parties.',
                'answers' => ['a1' => 'was signed'],
                'options' => [
                    'a1' => ['was signed', 'were signed', 'is signed', 'signed'],
                ],
                'verb_hints' => ['a1' => 'sign'],
                'hints' => [
                    '**Past Simple Passive** для офіційних документів.',
                    'Приклад: The agreement was finalized.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'Several suspects {a1} during the investigation.',
                'answers' => ['a1' => 'were arrested'],
                'options' => [
                    'a1' => ['were arrested', 'was arrested', 'are arrested', 'arrested'],
                ],
                'verb_hints' => ['a1' => 'arrest'],
                'hints' => [
                    '**Past Simple Passive** для кримінальних справ.',
                    'Приклад: The criminals were detained.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The research {a1} by a team of scientists.',
                'answers' => ['a1' => 'was conducted'],
                'options' => [
                    'a1' => ['was conducted', 'were conducted', 'is conducted', 'conducted'],
                ],
                'verb_hints' => ['a1' => 'conduct'],
                'hints' => [
                    '**Past Simple Passive** для досліджень.',
                    'Приклад: The experiment was performed.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The patients {a1} with a new medication.',
                'answers' => ['a1' => 'were treated'],
                'options' => [
                    'a1' => ['were treated', 'was treated', 'are treated', 'treated'],
                ],
                'verb_hints' => ['a1' => 'treat'],
                'hints' => [
                    '**Past Simple Passive** для медичних процедур.',
                    'Приклад: The injured were helped.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The deadline {a1} as requested.',
                'answers' => ['a1' => "wasn't extended"],
                'options' => [
                    'a1' => ["wasn't extended", "weren't extended", "isn't extended", "didn't extend"],
                ],
                'verb_hints' => ['a1' => 'extend'],
                'hints' => [
                    '**Past Simple Passive Negative**: wasn\'t + V3.',
                    'Приклад: The date wasn\'t changed.',
                ],
                'tag_ids' => [$negativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The candidates {a1} about the results.',
                'answers' => ['a1' => "weren't informed"],
                'options' => [
                    'a1' => ["weren't informed", "wasn't informed", "aren't informed", "didn't inform"],
                ],
                'verb_hints' => ['a1' => 'inform'],
                'hints' => [
                    '**Past Simple Passive Negative** для множини: weren\'t + V3.',
                    'Приклад: The employees weren\'t notified.',
                ],
                'tag_ids' => [$negativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => '{a1} the proposal accepted by the board?',
                'answers' => ['a1' => 'Was'],
                'options' => [
                    'a1' => ['Was', 'Were', 'Is', 'Did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Past Simple Passive Question**: Was + subject + V3?',
                    'Приклад: Was the plan approved?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'Why {a1} the meeting cancelled?',
                'answers' => ['a1' => 'was'],
                'options' => [
                    'a1' => ['was', 'were', 'is', 'did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Past Simple Passive**: Why + was + subject + V3?',
                    'Приклад: Why was the event postponed?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The patient {a1} to the hospital.',
                'answers' => ['a1' => 'was admitted'],
                'options' => [
                    'a1' => ['was admitted', 'were admitted', 'is admitted', 'admitted'],
                ],
                'verb_hints' => ['a1' => 'admit'],
                'hints' => [
                    '**Past Simple Passive** для медичних дій.',
                    'Приклад: The victim was transported.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'How many people {a1} injured in the accident?',
                'answers' => ['a1' => 'were'],
                'options' => [
                    'a1' => ['were', 'was', 'are', 'did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Past Simple Passive**: How many + were + V3?',
                    'Приклад: How many were hurt?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The security systems {a1} last year.',
                'answers' => ['a1' => 'were upgraded'],
                'options' => [
                    'a1' => ['were upgraded', 'was upgraded', 'are upgraded', 'upgraded'],
                ],
                'verb_hints' => ['a1' => 'upgrade'],
                'hints' => [
                    '**Past Simple Passive** для множини: were + V3.',
                    'Приклад: The servers were improved.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The painting {a1} to a private collector.',
                'answers' => ['a1' => 'was sold'],
                'options' => [
                    'a1' => ['was sold', 'were sold', 'is sold', 'sold'],
                ],
                'verb_hints' => ['a1' => 'sell'],
                'hints' => [
                    '**Past Simple Passive** для продажу.',
                    'Приклад: The artwork was purchased.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],

            // ===== B2 Level: 12 questions =====
            [
                'level' => 'B2',
                'question' => 'The legislation {a1} after months of debate.',
                'answers' => ['a1' => 'was passed'],
                'options' => [
                    'a1' => ['was passed', 'were passed', 'is passed', 'passed'],
                ],
                'verb_hints' => ['a1' => 'pass'],
                'hints' => [
                    '**Past Simple Passive** для законодавства.',
                    'Приклад: The law was enacted.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Significant reforms {a1} in the healthcare system.',
                'answers' => ['a1' => 'were introduced'],
                'options' => [
                    'a1' => ['were introduced', 'was introduced', 'are introduced', 'introduced'],
                ],
                'verb_hints' => ['a1' => 'introduce'],
                'hints' => [
                    '**Past Simple Passive** для реформ.',
                    'Приклад: Changes were implemented.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The data {a1} and analyzed carefully.',
                'answers' => ['a1' => 'was collected'],
                'options' => [
                    'a1' => ['was collected', 'were collected', 'is collected', 'collected'],
                ],
                'verb_hints' => ['a1' => 'collect'],
                'hints' => [
                    '**Past Simple Passive** для досліджень.',
                    'Приклад: The information was gathered.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The negotiations {a1} successfully.',
                'answers' => ['a1' => 'were concluded'],
                'options' => [
                    'a1' => ['were concluded', 'was concluded', 'are concluded', 'concluded'],
                ],
                'verb_hints' => ['a1' => 'conclude'],
                'hints' => [
                    '**Past Simple Passive** для переговорів.',
                    'Приклад: The talks were completed.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The warnings {a1} by the authorities.',
                'answers' => ['a1' => "weren't heeded"],
                'options' => [
                    'a1' => ["weren't heeded", "wasn't heeded", "aren't heeded", "didn't heed"],
                ],
                'verb_hints' => ['a1' => 'heed'],
                'hints' => [
                    '**Past Simple Passive Negative** для множини: weren\'t + V3.',
                    'Приклад: The advice wasn\'t followed.',
                ],
                'tag_ids' => [$negativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Adequate resources {a1} for the project.',
                'answers' => ['a1' => "weren't allocated"],
                'options' => [
                    'a1' => ["weren't allocated", "wasn't allocated", "aren't allocated", "didn't allocate"],
                ],
                'verb_hints' => ['a1' => 'allocate'],
                'hints' => [
                    '**Past Simple Passive Negative** для ресурсів.',
                    'Приклад: Sufficient funds weren\'t provided.',
                ],
                'tag_ids' => [$negativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => '{a1} the treaty ratified by all member states?',
                'answers' => ['a1' => 'Was'],
                'options' => [
                    'a1' => ['Was', 'Were', 'Is', 'Did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Past Simple Passive Question** для міжнародних угод.',
                    'Приклад: Was the agreement signed?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'By whom {a1} the decision made?',
                'answers' => ['a1' => 'was'],
                'options' => [
                    'a1' => ['was', 'were', 'is', 'did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Formal Past Simple Passive Question** з "by whom".',
                    'Приклад: By whom was the order given?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The policy {a1} to reflect new priorities.',
                'answers' => ['a1' => 'was revised'],
                'options' => [
                    'a1' => ['was revised', 'were revised', 'is revised', 'revised'],
                ],
                'verb_hints' => ['a1' => 'revise'],
                'hints' => [
                    '**Past Simple Passive** для змін у політиці.',
                    'Приклад: The guidelines were updated.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The documents {a1} before the meeting.',
                'answers' => ['a1' => 'were distributed'],
                'options' => [
                    'a1' => ['were distributed', 'was distributed', 'are distributed', 'distributed'],
                ],
                'verb_hints' => ['a1' => 'distribute'],
                'hints' => [
                    '**Past Simple Passive** для документів.',
                    'Приклад: The materials were handed out.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Why {a1} the project abandoned?',
                'answers' => ['a1' => 'was'],
                'options' => [
                    'a1' => ['was', 'were', 'is', 'did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Past Simple Passive**: Why + was + subject + V3?',
                    'Приклад: Why was the plan cancelled?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The manuscript {a1} by the publisher.',
                'answers' => ['a1' => 'was rejected'],
                'options' => [
                    'a1' => ['was rejected', 'were rejected', 'is rejected', 'rejected'],
                ],
                'verb_hints' => ['a1' => 'reject'],
                'hints' => [
                    '**Past Simple Passive** для видавництва.',
                    'Приклад: The article was declined.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],

            // ===== C1 Level: 12 questions =====
            [
                'level' => 'C1',
                'question' => 'The theoretical framework {a1} to account for new phenomena.',
                'answers' => ['a1' => 'was modified'],
                'options' => [
                    'a1' => ['was modified', 'were modified', 'is modified', 'modified'],
                ],
                'verb_hints' => ['a1' => 'modify'],
                'hints' => [
                    '**Past Simple Passive** для академічних змін.',
                    'Приклад: The model was adjusted.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Constitutional amendments {a1} after lengthy deliberations.',
                'answers' => ['a1' => 'were ratified'],
                'options' => [
                    'a1' => ['were ratified', 'was ratified', 'are ratified', 'ratified'],
                ],
                'verb_hints' => ['a1' => 'ratify'],
                'hints' => [
                    '**Past Simple Passive** для конституційних змін.',
                    'Приклад: The reforms were approved.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The correlation {a1} through rigorous statistical analysis.',
                'answers' => ['a1' => 'was established'],
                'options' => [
                    'a1' => ['was established', 'were established', 'is established', 'established'],
                ],
                'verb_hints' => ['a1' => 'establish'],
                'hints' => [
                    '**Past Simple Passive** для наукових висновків.',
                    'Приклад: The link was confirmed.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Indigenous territories {a1} by colonial powers.',
                'answers' => ['a1' => 'were annexed'],
                'options' => [
                    'a1' => ['were annexed', 'was annexed', 'are annexed', 'annexed'],
                ],
                'verb_hints' => ['a1' => 'annex'],
                'hints' => [
                    '**Past Simple Passive** для історичних подій.',
                    'Приклад: The lands were seized.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The ethical implications {a1} adequately before the trial.',
                'answers' => ['a1' => "weren't considered"],
                'options' => [
                    'a1' => ["weren't considered", "wasn't considered", "aren't considered", "didn't consider"],
                ],
                'verb_hints' => ['a1' => 'consider'],
                'hints' => [
                    '**Past Simple Passive Negative** для критики.',
                    'Приклад: The consequences weren\'t examined.',
                ],
                'tag_ids' => [$negativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The underlying causes {a1} thoroughly.',
                'answers' => ['a1' => "weren't investigated"],
                'options' => [
                    'a1' => ["weren't investigated", "wasn't investigated", "aren't investigated", "didn't investigate"],
                ],
                'verb_hints' => ['a1' => 'investigate'],
                'hints' => [
                    '**Past Simple Passive Negative** для недостатнього аналізу.',
                    'Приклад: The root issues weren\'t explored.',
                ],
                'tag_ids' => [$negativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => '{a1} sufficient safeguards put in place?',
                'answers' => ['a1' => 'Were'],
                'options' => [
                    'a1' => ['Were', 'Was', 'Are', 'Did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Past Simple Passive Question** для заходів безпеки.',
                    'Приклад: Were adequate measures taken?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'To what extent {a1} international standards followed?',
                'answers' => ['a1' => 'were'],
                'options' => [
                    'a1' => ['were', 'was', 'are', 'did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Formal WH-Question Past Simple Passive**.',
                    'Приклад: To what degree were guidelines adhered to?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The paradigm {a1} by the discovery.',
                'answers' => ['a1' => 'was transformed'],
                'options' => [
                    'a1' => ['was transformed', 'were transformed', 'is transformed', 'transformed'],
                ],
                'verb_hints' => ['a1' => 'transform'],
                'hints' => [
                    '**Past Simple Passive** для наукових революцій.',
                    'Приклад: The field was revolutionized.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The findings {a1} throughout the scientific community.',
                'answers' => ['a1' => 'were disseminated'],
                'options' => [
                    'a1' => ['were disseminated', 'was disseminated', 'are disseminated', 'disseminated'],
                ],
                'verb_hints' => ['a1' => 'disseminate'],
                'hints' => [
                    '**Past Simple Passive** для поширення інформації.',
                    'Приклад: The results were circulated.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Why {a1} these recommendations disregarded?',
                'answers' => ['a1' => 'were'],
                'options' => [
                    'a1' => ['were', 'was', 'are', 'did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Past Simple Passive** для критики.',
                    'Приклад: Why were the suggestions ignored?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The hypothesis {a1} through extensive experimentation.',
                'answers' => ['a1' => 'was validated'],
                'options' => [
                    'a1' => ['was validated', 'were validated', 'is validated', 'validated'],
                ],
                'verb_hints' => ['a1' => 'validate'],
                'hints' => [
                    '**Past Simple Passive** для наукової верифікації.',
                    'Приклад: The theory was confirmed.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],

            // ===== C2 Level: 12 questions =====
            [
                'level' => 'C2',
                'question' => 'The epistemological assumptions {a1} by postmodern critics.',
                'answers' => ['a1' => 'were challenged'],
                'options' => [
                    'a1' => ['were challenged', 'was challenged', 'are challenged', 'challenged'],
                ],
                'verb_hints' => ['a1' => 'challenge'],
                'hints' => [
                    '**Past Simple Passive** для філософської критики.',
                    'Приклад: The foundations were questioned.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The hegemonic narrative {a1} through deconstructive reading.',
                'answers' => ['a1' => 'was dismantled'],
                'options' => [
                    'a1' => ['was dismantled', 'were dismantled', 'is dismantled', 'dismantled'],
                ],
                'verb_hints' => ['a1' => 'dismantle'],
                'hints' => [
                    '**Past Simple Passive** для критичного аналізу.',
                    'Приклад: The dominant discourse was deconstructed.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'Novel methodologies {a1} to address the research questions.',
                'answers' => ['a1' => 'were employed'],
                'options' => [
                    'a1' => ['were employed', 'was employed', 'are employed', 'employed'],
                ],
                'verb_hints' => ['a1' => 'employ'],
                'hints' => [
                    '**Past Simple Passive** для методологій.',
                    'Приклад: Innovative approaches were applied.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The ontological premises {a1} in light of new evidence.',
                'answers' => ['a1' => 'were reconsidered'],
                'options' => [
                    'a1' => ['were reconsidered', 'was reconsidered', 'are reconsidered', 'reconsidered'],
                ],
                'verb_hints' => ['a1' => 'reconsider'],
                'hints' => [
                    '**Past Simple Passive** для перегляду теорій.',
                    'Приклад: The hypotheses were revised.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The nuances of the argument {a1} in the mainstream media.',
                'answers' => ['a1' => "weren't conveyed"],
                'options' => [
                    'a1' => ["weren't conveyed", "wasn't conveyed", "aren't conveyed", "didn't convey"],
                ],
                'verb_hints' => ['a1' => 'convey'],
                'hints' => [
                    '**Past Simple Passive Negative** для медіа-критики.',
                    'Приклад: The subtleties weren\'t communicated.',
                ],
                'tag_ids' => [$negativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The ramifications of the policy {a1} with sufficient rigor.',
                'answers' => ['a1' => "weren't analyzed"],
                'options' => [
                    'a1' => ["weren't analyzed", "wasn't analyzed", "aren't analyzed", "didn't analyze"],
                ],
                'verb_hints' => ['a1' => 'analyze'],
                'hints' => [
                    '**Past Simple Passive Negative** для критики.',
                    'Приклад: The consequences weren\'t evaluated.',
                ],
                'tag_ids' => [$negativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => '{a1} the axioms of this theory sufficiently interrogated?',
                'answers' => ['a1' => 'Were'],
                'options' => [
                    'a1' => ['Were', 'Was', 'Are', 'Did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Past Simple Passive Question** для академічної критики.',
                    'Приклад: Were the assumptions tested?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'Through what mechanisms {a1} systemic inequalities perpetuated?',
                'answers' => ['a1' => 'were'],
                'options' => [
                    'a1' => ['were', 'was', 'are', 'did'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Formal WH-Question Past Simple Passive**.',
                    'Приклад: Through what means were biases maintained?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The hermeneutical tradition {a1} by the new scholarship.',
                'answers' => ['a1' => 'was enriched'],
                'options' => [
                    'a1' => ['was enriched', 'were enriched', 'is enriched', 'enriched'],
                ],
                'verb_hints' => ['a1' => 'enrich'],
                'hints' => [
                    '**Past Simple Passive** для інтелектуального збагачення.',
                    'Приклад: The field was expanded.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'Posthumanist perspectives {a1} into the analysis.',
                'answers' => ['a1' => 'were incorporated'],
                'options' => [
                    'a1' => ['were incorporated', 'was incorporated', 'are incorporated', 'incorporated'],
                ],
                'verb_hints' => ['a1' => 'incorporate'],
                'hints' => [
                    '**Past Simple Passive** для інтеграції підходів.',
                    'Приклад: New theories were integrated.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The dialectical tensions {a1} through close reading.',
                'answers' => ['a1' => 'were revealed'],
                'options' => [
                    'a1' => ['were revealed', 'was revealed', 'are revealed', 'revealed'],
                ],
                'verb_hints' => ['a1' => 'reveal'],
                'hints' => [
                    '**Past Simple Passive** для літературного аналізу.',
                    'Приклад: The contradictions were exposed.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The teleological assumptions {a1} by the empirical findings.',
                'answers' => ['a1' => 'were undermined'],
                'options' => [
                    'a1' => ['were undermined', 'was undermined', 'are undermined', 'undermined'],
                ],
                'verb_hints' => ['a1' => 'undermine'],
                'hints' => [
                    '**Past Simple Passive** для наукових висновків.',
                    'Приклад: Traditional views were challenged.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastSimpleTagId, $passiveTagId],
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
        return "✅ Правильно! «{$correct}» — це коректна форма Past Simple Passive. Формула: was/were + past participle (V3).";
    }

    private function wrongExplanation(string $wrong, string $correct): string
    {
        // Present instead of Past
        if ((str_contains(strtolower($correct), 'was ') || str_contains(strtolower($correct), 'were '))
            && (str_contains(strtolower($wrong), 'is ') || str_contains(strtolower($wrong), 'are '))) {
            return "❌ «{$wrong}» — це Present Simple Passive. Для Past Simple Passive потрібен was/were + V3.";
        }

        // Active voice
        if (!str_contains(strtolower($wrong), 'was') && !str_contains(strtolower($wrong), 'were') 
            && !str_contains(strtolower($wrong), "wasn't") && !str_contains(strtolower($wrong), "weren't")) {
            return "❌ «{$wrong}» — це Active Voice. Тут потрібен Passive Voice: was/were + V3.";
        }

        // Wrong number agreement
        if (str_contains(strtolower($correct), 'were ') && str_contains(strtolower($wrong), 'was ')) {
            return "❌ «{$wrong}» — неправильне узгодження числа. З множиною використовуйте «were», не «was».";
        }
        if (str_contains(strtolower($correct), 'was ') && str_contains(strtolower($wrong), 'were ')) {
            return "❌ «{$wrong}» — неправильне узгодження числа. З одниною використовуйте «was», не «were».";
        }

        // Did instead of was/were
        if (str_contains(strtolower($wrong), 'did')) {
            return "❌ «{$wrong}» — did використовується в Active Voice. У Passive Voice потрібен was/were.";
        }

        return "❌ «{$wrong}» — неправильна форма. Для Past Simple Passive потрібно: was/were + past participle.";
    }
}
