<?php

namespace Database\Seeders\AI\Claude\PassiveVoice;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class PresentPerfectPassiveSeeder extends QuestionSeeder
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
        $sourceId = Source::firstOrCreate(['name' => 'AI Generated: Present Perfect Passive'])->id;
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
            ['name' => 'Present Perfect Passive'],
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

        $presentPerfectTagId = Tag::firstOrCreate(['name' => 'Present Perfect'], ['category' => 'Tense'])->id;
        $passiveTagId = Tag::firstOrCreate(['name' => 'Passive Voice'], ['category' => 'Voice'])->id;

        return [
            // ===== A1 Level: 12 questions =====
            [
                'level' => 'A1',
                'question' => 'The letter {a1} already.',
                'answers' => ['a1' => 'has been sent'],
                'options' => [
                    'a1' => ['has been sent', 'have been sent', 'was sent', 'is sent'],
                ],
                'verb_hints' => ['a1' => 'send'],
                'hints' => [
                    '**Present Perfect Passive** утворюється: has/have + been + past participle (V3).',
                    'Використовується для дій, завершених до теперішнього моменту.',
                    'Приклад: The email has been delivered.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The windows {a1} recently.',
                'answers' => ['a1' => 'have been cleaned'],
                'options' => [
                    'a1' => ['have been cleaned', 'has been cleaned', 'were cleaned', 'are cleaned'],
                ],
                'verb_hints' => ['a1' => 'clean'],
                'hints' => [
                    '**Present Perfect Passive** для множини: have + been + V3.',
                    '"Recently" вказує на Present Perfect.',
                    'Приклад: The floors have been mopped.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The cake {a1} by my mother.',
                'answers' => ['a1' => 'has been made'],
                'options' => [
                    'a1' => ['has been made', 'have been made', 'was made', 'is made'],
                ],
                'verb_hints' => ['a1' => 'make'],
                'hints' => [
                    '**Present Perfect Passive**: has + been + V3.',
                    'Приклад: The dinner has been prepared.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The homework {a1} yet.',
                'answers' => ['a1' => 'has been finished'],
                'options' => [
                    'a1' => ['has been finished', 'have been finished', 'was finished', 'is finished'],
                ],
                'verb_hints' => ['a1' => 'finish'],
                'hints' => [
                    '**Present Perfect Passive** з "yet".',
                    'Приклад: The task has been completed.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The books {a1} to the library.',
                'answers' => ['a1' => 'have been returned'],
                'options' => [
                    'a1' => ['have been returned', 'has been returned', 'were returned', 'are returned'],
                ],
                'verb_hints' => ['a1' => 'return'],
                'hints' => [
                    '**Present Perfect Passive** для множини: have + been + V3.',
                    'Приклад: The documents have been submitted.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The door {a1} just.',
                'answers' => ['a1' => 'has been opened'],
                'options' => [
                    'a1' => ['has been opened', 'have been opened', 'was opened', 'is opened'],
                ],
                'verb_hints' => ['a1' => 'open'],
                'hints' => [
                    '**Present Perfect Passive** з "just".',
                    'Приклад: The window has been closed.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The car {a1} yet.',
                'answers' => ['a1' => "hasn't been washed"],
                'options' => [
                    'a1' => ["hasn't been washed", "haven't been washed", "wasn't washed", "isn't washed"],
                ],
                'verb_hints' => ['a1' => 'wash'],
                'hints' => [
                    '**Present Perfect Passive Negative**: has not (hasn\'t) + been + V3.',
                    'Приклад: The room hasn\'t been tidied.',
                ],
                'tag_ids' => [$negativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The tickets {a1} yet.',
                'answers' => ['a1' => "haven't been bought"],
                'options' => [
                    'a1' => ["haven't been bought", "hasn't been bought", "weren't bought", "aren't bought"],
                ],
                'verb_hints' => ['a1' => 'buy'],
                'hints' => [
                    '**Present Perfect Passive Negative** для множини: have not (haven\'t) + been + V3.',
                    'Приклад: The reservations haven\'t been made.',
                ],
                'tag_ids' => [$negativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => '{a1} the room been cleaned?',
                'answers' => ['a1' => 'Has'],
                'options' => [
                    'a1' => ['Has', 'Have', 'Was', 'Is'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Present Perfect Passive Question**: Has/Have + subject + been + V3?',
                    'Приклад: Has the work been done?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => '{a1} the dishes been washed?',
                'answers' => ['a1' => 'Have'],
                'options' => [
                    'a1' => ['Have', 'Has', 'Were', 'Are'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Present Perfect Passive Question** для множини: Have + subject + been + V3?',
                    'Приклад: Have the clothes been ironed?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The dog {a1} already.',
                'answers' => ['a1' => 'has been fed'],
                'options' => [
                    'a1' => ['has been fed', 'have been fed', 'was fed', 'is fed'],
                ],
                'verb_hints' => ['a1' => 'feed'],
                'hints' => [
                    '**Present Perfect Passive** з "already".',
                    'Приклад: The cat has been given water.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'I {a1} about the meeting.',
                'answers' => ['a1' => 'have been told'],
                'options' => [
                    'a1' => ['have been told', 'has been told', 'was told', 'am told'],
                ],
                'verb_hints' => ['a1' => 'tell'],
                'hints' => [
                    '**Present Perfect Passive** з "I": have + been + V3.',
                    'Приклад: I have been informed.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],

            // ===== A2 Level: 12 questions =====
            [
                'level' => 'A2',
                'question' => 'The report {a1} to the manager.',
                'answers' => ['a1' => 'has been submitted'],
                'options' => [
                    'a1' => ['has been submitted', 'have been submitted', 'was submitted', 'is submitted'],
                ],
                'verb_hints' => ['a1' => 'submit'],
                'hints' => [
                    '**Present Perfect Passive** для завершених дій.',
                    'Приклад: The application has been filed.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'Many trees {a1} in the park this year.',
                'answers' => ['a1' => 'have been planted'],
                'options' => [
                    'a1' => ['have been planted', 'has been planted', 'were planted', 'are planted'],
                ],
                'verb_hints' => ['a1' => 'plant'],
                'hints' => [
                    '**Present Perfect Passive** для періоду до тепер.',
                    'Приклад: Many flowers have been grown.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The new bridge {a1} recently.',
                'answers' => ['a1' => 'has been completed'],
                'options' => [
                    'a1' => ['has been completed', 'have been completed', 'was completed', 'is completed'],
                ],
                'verb_hints' => ['a1' => 'complete'],
                'hints' => [
                    '**Present Perfect Passive** з "recently".',
                    'Приклад: The road has been repaired.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The students {a1} of the changes.',
                'answers' => ['a1' => 'have been informed'],
                'options' => [
                    'a1' => ['have been informed', 'has been informed', 'were informed', 'are informed'],
                ],
                'verb_hints' => ['a1' => 'inform'],
                'hints' => [
                    '**Present Perfect Passive** для множини.',
                    'Приклад: The employees have been notified.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'A new record {a1} this month.',
                'answers' => ['a1' => 'has been set'],
                'options' => [
                    'a1' => ['has been set', 'have been set', 'was set', 'is set'],
                ],
                'verb_hints' => ['a1' => 'set'],
                'hints' => [
                    '**Present Perfect Passive** для досягнень.',
                    'Приклад: A goal has been achieved.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The problem {a1} yet.',
                'answers' => ['a1' => "hasn't been solved"],
                'options' => [
                    'a1' => ["hasn't been solved", "haven't been solved", "wasn't solved", "isn't solved"],
                ],
                'verb_hints' => ['a1' => 'solve'],
                'hints' => [
                    '**Present Perfect Passive Negative** з "yet".',
                    'Приклад: The issue hasn\'t been fixed.',
                ],
                'tag_ids' => [$negativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The invitations {a1} yet.',
                'answers' => ['a1' => "haven't been sent"],
                'options' => [
                    'a1' => ["haven't been sent", "hasn't been sent", "weren't sent", "aren't sent"],
                ],
                'verb_hints' => ['a1' => 'send'],
                'hints' => [
                    '**Present Perfect Passive Negative** для множини.',
                    'Приклад: The packages haven\'t been delivered.',
                ],
                'tag_ids' => [$negativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => '{a1} the project been approved?',
                'answers' => ['a1' => 'Has'],
                'options' => [
                    'a1' => ['Has', 'Have', 'Was', 'Is'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Present Perfect Passive Question**: Has + subject + been + V3?',
                    'Приклад: Has the plan been accepted?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'How many books {a1} been sold this week?',
                'answers' => ['a1' => 'have'],
                'options' => [
                    'a1' => ['have', 'has', 'were', 'are'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Present Perfect Passive**: How many + have + been + V3?',
                    'Приклад: How many tickets have been purchased?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The museum {a1} by thousands of tourists.',
                'answers' => ['a1' => 'has been visited'],
                'options' => [
                    'a1' => ['has been visited', 'have been visited', 'was visited', 'is visited'],
                ],
                'verb_hints' => ['a1' => 'visit'],
                'hints' => [
                    '**Present Perfect Passive** з агентом.',
                    'Приклад: The castle has been seen by many.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'Where {a1} the money been kept?',
                'answers' => ['a1' => 'has'],
                'options' => [
                    'a1' => ['has', 'have', 'was', 'is'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Present Perfect Passive**: Where + has + subject + been + V3?',
                    'Приклад: Where has the key been hidden?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The old building {a1} recently.',
                'answers' => ['a1' => 'has been demolished'],
                'options' => [
                    'a1' => ['has been demolished', 'have been demolished', 'was demolished', 'is demolished'],
                ],
                'verb_hints' => ['a1' => 'demolish'],
                'hints' => [
                    '**Present Perfect Passive** для нещодавніх подій.',
                    'Приклад: The structure has been torn down.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],

            // ===== B1 Level: 12 questions =====
            [
                'level' => 'B1',
                'question' => 'The software {a1} several times this year.',
                'answers' => ['a1' => 'has been updated'],
                'options' => [
                    'a1' => ['has been updated', 'have been updated', 'was updated', 'is updated'],
                ],
                'verb_hints' => ['a1' => 'update'],
                'hints' => [
                    '**Present Perfect Passive** для повторюваних дій до тепер.',
                    'Приклад: The system has been upgraded.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'Strict measures {a1} to prevent fraud.',
                'answers' => ['a1' => 'have been taken'],
                'options' => [
                    'a1' => ['have been taken', 'has been taken', 'were taken', 'are taken'],
                ],
                'verb_hints' => ['a1' => 'take'],
                'hints' => [
                    '**Present Perfect Passive** для заходів.',
                    'Приклад: Steps have been implemented.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The research {a1} by a team of scientists.',
                'answers' => ['a1' => 'has been conducted'],
                'options' => [
                    'a1' => ['has been conducted', 'have been conducted', 'was conducted', 'is conducted'],
                ],
                'verb_hints' => ['a1' => 'conduct'],
                'hints' => [
                    '**Present Perfect Passive** з агентом.',
                    'Приклад: The study has been performed.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'Several complaints {a1} about the service.',
                'answers' => ['a1' => 'have been received'],
                'options' => [
                    'a1' => ['have been received', 'has been received', 'were received', 'are received'],
                ],
                'verb_hints' => ['a1' => 'receive'],
                'hints' => [
                    '**Present Perfect Passive** для скарг.',
                    'Приклад: Many reports have been filed.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The contract {a1} yet.',
                'answers' => ['a1' => "hasn't been signed"],
                'options' => [
                    'a1' => ["hasn't been signed", "haven't been signed", "wasn't signed", "isn't signed"],
                ],
                'verb_hints' => ['a1' => 'sign'],
                'hints' => [
                    '**Present Perfect Passive Negative** з "yet".',
                    'Приклад: The agreement hasn\'t been finalized.',
                ],
                'tag_ids' => [$negativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The candidates {a1} yet.',
                'answers' => ['a1' => "haven't been interviewed"],
                'options' => [
                    'a1' => ["haven't been interviewed", "hasn't been interviewed", "weren't interviewed", "aren't interviewed"],
                ],
                'verb_hints' => ['a1' => 'interview'],
                'hints' => [
                    '**Present Perfect Passive Negative** для множини.',
                    'Приклад: The applicants haven\'t been selected.',
                ],
                'tag_ids' => [$negativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => '{a1} the deadline been extended?',
                'answers' => ['a1' => 'Has'],
                'options' => [
                    'a1' => ['Has', 'Have', 'Was', 'Is'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Present Perfect Passive Question**: Has + subject + been + V3?',
                    'Приклад: Has the date been changed?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'Why {a1} the rules been changed?',
                'answers' => ['a1' => 'have'],
                'options' => [
                    'a1' => ['have', 'has', 'were', 'are'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Present Perfect Passive**: Why + have + subject + been + V3?',
                    'Приклад: Why has the policy been modified?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The patient {a1} to a specialist.',
                'answers' => ['a1' => 'has been referred'],
                'options' => [
                    'a1' => ['has been referred', 'have been referred', 'was referred', 'is referred'],
                ],
                'verb_hints' => ['a1' => 'refer'],
                'hints' => [
                    '**Present Perfect Passive** для медичних направлень.',
                    'Приклад: The case has been transferred.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'How much money {a1} been raised for the charity?',
                'answers' => ['a1' => 'has'],
                'options' => [
                    'a1' => ['has', 'have', 'was', 'is'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Present Perfect Passive**: How much + has + been + V3?',
                    'Приклад: How much has been collected?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The security systems {a1} recently.',
                'answers' => ['a1' => 'have been upgraded'],
                'options' => [
                    'a1' => ['have been upgraded', 'has been upgraded', 'were upgraded', 'are upgraded'],
                ],
                'verb_hints' => ['a1' => 'upgrade'],
                'hints' => [
                    '**Present Perfect Passive** для технічних оновлень.',
                    'Приклад: The servers have been improved.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The artwork {a1} to a private collector.',
                'answers' => ['a1' => 'has been sold'],
                'options' => [
                    'a1' => ['has been sold', 'have been sold', 'was sold', 'is sold'],
                ],
                'verb_hints' => ['a1' => 'sell'],
                'hints' => [
                    '**Present Perfect Passive** для продажу.',
                    'Приклад: The painting has been purchased.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],

            // ===== B2 Level: 12 questions =====
            [
                'level' => 'B2',
                'question' => 'The proposal {a1} by the committee.',
                'answers' => ['a1' => 'has been approved'],
                'options' => [
                    'a1' => ['has been approved', 'have been approved', 'was approved', 'is approved'],
                ],
                'verb_hints' => ['a1' => 'approve'],
                'hints' => [
                    '**Present Perfect Passive** для офіційних рішень.',
                    'Приклад: The request has been granted.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Significant progress {a1} in the negotiations.',
                'answers' => ['a1' => 'has been made'],
                'options' => [
                    'a1' => ['has been made', 'have been made', 'was made', 'is made'],
                ],
                'verb_hints' => ['a1' => 'make'],
                'hints' => [
                    '**Present Perfect Passive** для прогресу.',
                    'Приклад: Great strides have been achieved.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'New regulations {a1} to protect consumers.',
                'answers' => ['a1' => 'have been introduced'],
                'options' => [
                    'a1' => ['have been introduced', 'has been introduced', 'were introduced', 'are introduced'],
                ],
                'verb_hints' => ['a1' => 'introduce'],
                'hints' => [
                    '**Present Perfect Passive** для законодавства.',
                    'Приклад: New laws have been enacted.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The data {a1} and analyzed thoroughly.',
                'answers' => ['a1' => 'has been collected'],
                'options' => [
                    'a1' => ['has been collected', 'have been collected', 'was collected', 'is collected'],
                ],
                'verb_hints' => ['a1' => 'collect'],
                'hints' => [
                    '**Present Perfect Passive** для дослідницьких даних.',
                    'Приклад: The information has been gathered.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The allegations {a1} yet.',
                'answers' => ['a1' => "haven't been proven"],
                'options' => [
                    'a1' => ["haven't been proven", "hasn't been proven", "weren't proven", "aren't proven"],
                ],
                'verb_hints' => ['a1' => 'prove'],
                'hints' => [
                    '**Present Perfect Passive Negative** для недоведених звинувачень.',
                    'Приклад: The claims haven\'t been verified.',
                ],
                'tag_ids' => [$negativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'A solution {a1} yet.',
                'answers' => ['a1' => "hasn't been found"],
                'options' => [
                    'a1' => ["hasn't been found", "haven't been found", "wasn't found", "isn't found"],
                ],
                'verb_hints' => ['a1' => 'find'],
                'hints' => [
                    '**Present Perfect Passive Negative** з "yet".',
                    'Приклад: An answer hasn\'t been discovered.',
                ],
                'tag_ids' => [$negativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => '{a1} adequate funding been secured for the project?',
                'answers' => ['a1' => 'Has'],
                'options' => [
                    'a1' => ['Has', 'Have', 'Was', 'Is'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Present Perfect Passive Question** для фінансування.',
                    'Приклад: Has sufficient support been obtained?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'By whom {a1} the decision been made?',
                'answers' => ['a1' => 'has'],
                'options' => [
                    'a1' => ['has', 'have', 'was', 'is'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Formal Present Perfect Passive Question** з "by whom".',
                    'Приклад: By whom has this been authorized?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The policy {a1} to address environmental concerns.',
                'answers' => ['a1' => 'has been revised'],
                'options' => [
                    'a1' => ['has been revised', 'have been revised', 'was revised', 'is revised'],
                ],
                'verb_hints' => ['a1' => 'revise'],
                'hints' => [
                    '**Present Perfect Passive** для змін у політиці.',
                    'Приклад: The guidelines have been updated.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Several improvements {a1} since last year.',
                'answers' => ['a1' => 'have been implemented'],
                'options' => [
                    'a1' => ['have been implemented', 'has been implemented', 'were implemented', 'are implemented'],
                ],
                'verb_hints' => ['a1' => 'implement'],
                'hints' => [
                    '**Present Perfect Passive** з "since".',
                    'Приклад: Changes have been made since then.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Why {a1} this approach been abandoned?',
                'answers' => ['a1' => 'has'],
                'options' => [
                    'a1' => ['has', 'have', 'was', 'is'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Present Perfect Passive**: Why + has + subject + been + V3?',
                    'Приклад: Why has this method been rejected?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The manuscript {a1} for publication.',
                'answers' => ['a1' => 'has been accepted'],
                'options' => [
                    'a1' => ['has been accepted', 'have been accepted', 'was accepted', 'is accepted'],
                ],
                'verb_hints' => ['a1' => 'accept'],
                'hints' => [
                    '**Present Perfect Passive** для видавничих процесів.',
                    'Приклад: The article has been approved.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],

            // ===== C1 Level: 12 questions =====
            [
                'level' => 'C1',
                'question' => 'The theoretical framework {a1} to address gaps in current research.',
                'answers' => ['a1' => 'has been developed'],
                'options' => [
                    'a1' => ['has been developed', 'have been developed', 'was developed', 'is developed'],
                ],
                'verb_hints' => ['a1' => 'develop'],
                'hints' => [
                    '**Present Perfect Passive** для академічного розвитку.',
                    'Приклад: A new model has been proposed.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Comprehensive reforms {a1} across the healthcare sector.',
                'answers' => ['a1' => 'have been enacted'],
                'options' => [
                    'a1' => ['have been enacted', 'has been enacted', 'were enacted', 'are enacted'],
                ],
                'verb_hints' => ['a1' => 'enact'],
                'hints' => [
                    '**Present Perfect Passive** для реформ.',
                    'Приклад: Sweeping changes have been introduced.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The correlation {a1} through extensive statistical analysis.',
                'answers' => ['a1' => 'has been established'],
                'options' => [
                    'a1' => ['has been established', 'have been established', 'was established', 'is established'],
                ],
                'verb_hints' => ['a1' => 'establish'],
                'hints' => [
                    '**Present Perfect Passive** для наукових висновків.',
                    'Приклад: The link has been confirmed.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Indigenous rights {a1} by constitutional amendments.',
                'answers' => ['a1' => 'have been recognized'],
                'options' => [
                    'a1' => ['have been recognized', 'has been recognized', 'were recognized', 'are recognized'],
                ],
                'verb_hints' => ['a1' => 'recognize'],
                'hints' => [
                    '**Present Perfect Passive** для юридичних змін.',
                    'Приклад: Their claims have been acknowledged.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The ethical implications {a1} adequately.',
                'answers' => ['a1' => "haven't been considered"],
                'options' => [
                    'a1' => ["haven't been considered", "hasn't been considered", "weren't considered", "aren't considered"],
                ],
                'verb_hints' => ['a1' => 'consider'],
                'hints' => [
                    '**Present Perfect Passive Negative** для критики.',
                    'Приклад: The consequences haven\'t been examined.',
                ],
                'tag_ids' => [$negativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The underlying causes {a1} thoroughly.',
                'answers' => ['a1' => "haven't been investigated"],
                'options' => [
                    'a1' => ["haven't been investigated", "hasn't been investigated", "weren't investigated", "aren't investigated"],
                ],
                'verb_hints' => ['a1' => 'investigate'],
                'hints' => [
                    '**Present Perfect Passive Negative** для недостатнього аналізу.',
                    'Приклад: The root causes haven\'t been explored.',
                ],
                'tag_ids' => [$negativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => '{a1} sufficient attention been paid to minority perspectives?',
                'answers' => ['a1' => 'Has'],
                'options' => [
                    'a1' => ['Has', 'Have', 'Was', 'Is'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Present Perfect Passive Question** для критичних питань.',
                    'Приклад: Has due consideration been given?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'To what extent {a1} global standards been adopted?',
                'answers' => ['a1' => 'have'],
                'options' => [
                    'a1' => ['have', 'has', 'were', 'are'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Formal WH-Question Present Perfect Passive**.',
                    'Приклад: To what extent have changes been implemented?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The paradigm {a1} by emerging technologies.',
                'answers' => ['a1' => 'has been transformed'],
                'options' => [
                    'a1' => ['has been transformed', 'have been transformed', 'was transformed', 'is transformed'],
                ],
                'verb_hints' => ['a1' => 'transform'],
                'hints' => [
                    '**Present Perfect Passive** для технологічних змін.',
                    'Приклад: The landscape has been reshaped.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Best practices {a1} throughout the organization.',
                'answers' => ['a1' => 'have been disseminated'],
                'options' => [
                    'a1' => ['have been disseminated', 'has been disseminated', 'were disseminated', 'are disseminated'],
                ],
                'verb_hints' => ['a1' => 'disseminate'],
                'hints' => [
                    '**Present Perfect Passive** для поширення практик.',
                    'Приклад: Guidelines have been circulated.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Why {a1} these recommendations been ignored?',
                'answers' => ['a1' => 'have'],
                'options' => [
                    'a1' => ['have', 'has', 'were', 'are'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Present Perfect Passive** для критики.',
                    'Приклад: Why have the suggestions been disregarded?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The hypothesis {a1} through rigorous experimentation.',
                'answers' => ['a1' => 'has been validated'],
                'options' => [
                    'a1' => ['has been validated', 'have been validated', 'was validated', 'is validated'],
                ],
                'verb_hints' => ['a1' => 'validate'],
                'hints' => [
                    '**Present Perfect Passive** для наукової верифікації.',
                    'Приклад: The theory has been confirmed.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],

            // ===== C2 Level: 12 questions =====
            [
                'level' => 'C2',
                'question' => 'The epistemological underpinnings {a1} by contemporary scholars.',
                'answers' => ['a1' => 'have been challenged'],
                'options' => [
                    'a1' => ['have been challenged', 'has been challenged', 'were challenged', 'are challenged'],
                ],
                'verb_hints' => ['a1' => 'challenge'],
                'hints' => [
                    '**Present Perfect Passive** для філософської критики.',
                    'Приклад: The foundations have been questioned.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The hegemonic narrative {a1} through critical discourse analysis.',
                'answers' => ['a1' => 'has been deconstructed'],
                'options' => [
                    'a1' => ['has been deconstructed', 'have been deconstructed', 'was deconstructed', 'is deconstructed'],
                ],
                'verb_hints' => ['a1' => 'deconstruct'],
                'hints' => [
                    '**Present Perfect Passive** для критичного аналізу.',
                    'Приклад: The dominant discourse has been dismantled.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'Transdisciplinary approaches {a1} to address complex problems.',
                'answers' => ['a1' => 'have been employed'],
                'options' => [
                    'a1' => ['have been employed', 'has been employed', 'were employed', 'are employed'],
                ],
                'verb_hints' => ['a1' => 'employ'],
                'hints' => [
                    '**Present Perfect Passive** для методологічних підходів.',
                    'Приклад: Novel frameworks have been applied.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The ontological assumptions {a1} in recent publications.',
                'answers' => ['a1' => 'have been scrutinized'],
                'options' => [
                    'a1' => ['have been scrutinized', 'has been scrutinized', 'were scrutinized', 'are scrutinized'],
                ],
                'verb_hints' => ['a1' => 'scrutinize'],
                'hints' => [
                    '**Present Perfect Passive** для академічного перегляду.',
                    'Приклад: The premises have been examined.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The nuances of the argument {a1} adequately in public discourse.',
                'answers' => ['a1' => "haven't been articulated"],
                'options' => [
                    'a1' => ["haven't been articulated", "hasn't been articulated", "weren't articulated", "aren't articulated"],
                ],
                'verb_hints' => ['a1' => 'articulate'],
                'hints' => [
                    '**Present Perfect Passive Negative** для медіа-критики.',
                    'Приклад: The subtleties haven\'t been conveyed.',
                ],
                'tag_ids' => [$negativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The ramifications of this policy {a1} with sufficient rigor.',
                'answers' => ['a1' => "haven't been assessed"],
                'options' => [
                    'a1' => ["haven't been assessed", "hasn't been assessed", "weren't assessed", "aren't assessed"],
                ],
                'verb_hints' => ['a1' => 'assess'],
                'hints' => [
                    '**Present Perfect Passive Negative** для критики політики.',
                    'Приклад: The implications haven\'t been evaluated.',
                ],
                'tag_ids' => [$negativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => '{a1} the axioms of this theory been interrogated sufficiently?',
                'answers' => ['a1' => 'Have'],
                'options' => [
                    'a1' => ['Have', 'Has', 'Were', 'Are'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Present Perfect Passive Question** для академічної критики.',
                    'Приклад: Have the postulates been questioned?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'Through what mechanisms {a1} systemic biases been perpetuated?',
                'answers' => ['a1' => 'have'],
                'options' => [
                    'a1' => ['have', 'has', 'were', 'are'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Formal WH-Question Present Perfect Passive**.',
                    'Приклад: Through what means have inequalities been maintained?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The hermeneutical tradition {a1} by postmodern critique.',
                'answers' => ['a1' => 'has been enriched'],
                'options' => [
                    'a1' => ['has been enriched', 'have been enriched', 'was enriched', 'is enriched'],
                ],
                'verb_hints' => ['a1' => 'enrich'],
                'hints' => [
                    '**Present Perfect Passive** для інтелектуального збагачення.',
                    'Приклад: The field has been expanded.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'Posthumanist perspectives {a1} into contemporary literary criticism.',
                'answers' => ['a1' => 'have been incorporated'],
                'options' => [
                    'a1' => ['have been incorporated', 'has been incorporated', 'were incorporated', 'are incorporated'],
                ],
                'verb_hints' => ['a1' => 'incorporate'],
                'hints' => [
                    '**Present Perfect Passive** для інтеграції ідей.',
                    'Приклад: New theories have been integrated.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The teleological implications {a1} by evolutionary theorists.',
                'answers' => ['a1' => 'have been debated'],
                'options' => [
                    'a1' => ['have been debated', 'has been debated', 'were debated', 'are debated'],
                ],
                'verb_hints' => ['a1' => 'debate'],
                'hints' => [
                    '**Present Perfect Passive** для наукових дискусій.',
                    'Приклад: The consequences have been discussed.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The dialectical tensions inherent in the text {a1} by scholars.',
                'answers' => ['a1' => 'have been illuminated'],
                'options' => [
                    'a1' => ['have been illuminated', 'has been illuminated', 'were illuminated', 'are illuminated'],
                ],
                'verb_hints' => ['a1' => 'illuminate'],
                'hints' => [
                    '**Present Perfect Passive** для літературознавства.',
                    'Приклад: The contradictions have been revealed.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentPerfectTagId, $passiveTagId],
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
        return "✅ Правильно! «{$correct}» — це коректна форма Present Perfect Passive. Формула: has/have + been + past participle (V3).";
    }

    private function wrongExplanation(string $wrong, string $correct): string
    {
        // Past Simple instead of Present Perfect
        if ((str_contains(strtolower($correct), 'has been') || str_contains(strtolower($correct), 'have been'))
            && (str_contains(strtolower($wrong), 'was ') || str_contains(strtolower($wrong), 'were '))) {
            return "❌ «{$wrong}» — це Past Simple Passive. Для Present Perfect Passive потрібен has/have + been + V3.";
        }

        // Present Simple instead of Present Perfect
        if ((str_contains(strtolower($correct), 'has been') || str_contains(strtolower($correct), 'have been'))
            && (str_contains(strtolower($wrong), 'is ') || str_contains(strtolower($wrong), 'are '))) {
            return "❌ «{$wrong}» — це Present Simple Passive. Для Present Perfect Passive потрібен has/have + been + V3.";
        }

        // Wrong number agreement
        if (str_contains(strtolower($correct), 'have been') && str_contains(strtolower($wrong), 'has been')) {
            return "❌ «{$wrong}» — неправильне узгодження числа. З множиною використовуйте «have been», не «has been».";
        }
        if (str_contains(strtolower($correct), 'has been') && str_contains(strtolower($wrong), 'have been')) {
            return "❌ «{$wrong}» — неправильне узгодження числа. З одниною використовуйте «has been», не «have been».";
        }

        return "❌ «{$wrong}» — неправильна форма. Для Present Perfect Passive потрібно: has/have + been + past participle.";
    }
}
