<?php

namespace Database\Seeders\AI\Claude\PassiveVoice;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class PastContinuousPassiveSeeder extends QuestionSeeder
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
        $sourceId = Source::firstOrCreate(['name' => 'AI Generated: Past Continuous Passive'])->id;
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
            ['name' => 'Past Continuous Passive'],
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

        $pastContinuousTagId = Tag::firstOrCreate(['name' => 'Past Continuous'], ['category' => 'Tense'])->id;
        $passiveTagId = Tag::firstOrCreate(['name' => 'Passive Voice'], ['category' => 'Voice'])->id;

        return [
            // ===== A1 Level: 12 questions =====
            [
                'level' => 'A1',
                'question' => 'The house {a1} when we arrived.',
                'answers' => ['a1' => 'was being painted'],
                'options' => [
                    'a1' => ['was being painted', 'were being painted', 'was painted', 'was painting'],
                ],
                'verb_hints' => ['a1' => 'paint'],
                'hints' => [
                    '**Past Continuous Passive** утворюється: was/were + being + past participle (V3).',
                    'Використовується для дій, що тривали в певний момент у минулому.',
                    'Приклад: The car was being washed when I called.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The letters {a1} when the fire started.',
                'answers' => ['a1' => 'were being typed'],
                'options' => [
                    'a1' => ['were being typed', 'was being typed', 'were typed', 'were typing'],
                ],
                'verb_hints' => ['a1' => 'type'],
                'hints' => [
                    '**Past Continuous Passive** для множини: were + being + V3.',
                    'Приклад: The reports were being written.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'Dinner {a1} when the guests came.',
                'answers' => ['a1' => 'was being prepared'],
                'options' => [
                    'a1' => ['was being prepared', 'were being prepared', 'was prepared', 'was preparing'],
                ],
                'verb_hints' => ['a1' => 'prepare'],
                'hints' => [
                    '**Past Continuous Passive**: was + being + V3.',
                    'Приклад: The meal was being cooked.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The car {a1} when it started to rain.',
                'answers' => ['a1' => 'was being washed'],
                'options' => [
                    'a1' => ['was being washed', 'were being washed', 'was washed', 'was washing'],
                ],
                'verb_hints' => ['a1' => 'wash'],
                'hints' => [
                    '**Past Continuous Passive** описує дію в процесі.',
                    'Приклад: The dog was being bathed.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The children {a1} when I visited the school.',
                'answers' => ['a1' => 'were being taught'],
                'options' => [
                    'a1' => ['were being taught', 'was being taught', 'were taught', 'were teaching'],
                ],
                'verb_hints' => ['a1' => 'teach'],
                'hints' => [
                    '**Past Continuous Passive** для множини: were + being + V3.',
                    'Приклад: The students were being tested.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The room {a1} when the manager entered.',
                'answers' => ['a1' => 'was being cleaned'],
                'options' => [
                    'a1' => ['was being cleaned', 'were being cleaned', 'was cleaned', 'was cleaning'],
                ],
                'verb_hints' => ['a1' => 'clean'],
                'hints' => [
                    '**Past Continuous Passive**: was + being + V3.',
                    'Приклад: The office was being tidied.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The packages {a1} when the truck broke down.',
                'answers' => ['a1' => "weren't being delivered"],
                'options' => [
                    'a1' => ["weren't being delivered", "wasn't being delivered", "weren't delivered", "didn't deliver"],
                ],
                'verb_hints' => ['a1' => 'deliver'],
                'hints' => [
                    '**Past Continuous Passive Negative**: were not (weren\'t) + being + V3.',
                    'Приклад: The orders weren\'t being processed.',
                ],
                'tag_ids' => [$negativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The music {a1} when I arrived.',
                'answers' => ['a1' => "wasn't being played"],
                'options' => [
                    'a1' => ["wasn't being played", "weren't being played", "wasn't played", "didn't play"],
                ],
                'verb_hints' => ['a1' => 'play'],
                'hints' => [
                    '**Past Continuous Passive Negative**: was not (wasn\'t) + being + V3.',
                    'Приклад: The radio wasn\'t being used.',
                ],
                'tag_ids' => [$negativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => '{a1} the food being served when you got there?',
                'answers' => ['a1' => 'Was'],
                'options' => [
                    'a1' => ['Was', 'Were', 'Did', 'Is'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Past Continuous Passive Question**: Was/Were + subject + being + V3?',
                    'Приклад: Was lunch being prepared?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => '{a1} the windows being washed when it started raining?',
                'answers' => ['a1' => 'Were'],
                'options' => [
                    'a1' => ['Were', 'Was', 'Did', 'Are'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Past Continuous Passive Question** для множини: Were + subject + being + V3?',
                    'Приклад: Were the floors being mopped?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The dog {a1} when the vet arrived.',
                'answers' => ['a1' => 'was being examined'],
                'options' => [
                    'a1' => ['was being examined', 'were being examined', 'was examined', 'was examining'],
                ],
                'verb_hints' => ['a1' => 'examine'],
                'hints' => [
                    '**Past Continuous Passive**: was + being + V3.',
                    'Приклад: The cat was being treated.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'I {a1} while my friends were playing.',
                'answers' => ['a1' => 'was being helped'],
                'options' => [
                    'a1' => ['was being helped', 'were being helped', 'was helped', 'was helping'],
                ],
                'verb_hints' => ['a1' => 'help'],
                'hints' => [
                    '**Past Continuous Passive** з "I": was + being + V3.',
                    'Приклад: I was being assisted.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],

            // ===== A2 Level: 12 questions =====
            [
                'level' => 'A2',
                'question' => 'A new bridge {a1} when the accident happened.',
                'answers' => ['a1' => 'was being built'],
                'options' => [
                    'a1' => ['was being built', 'were being built', 'was built', 'was building'],
                ],
                'verb_hints' => ['a1' => 'build'],
                'hints' => [
                    '**Past Continuous Passive** для будівельних робіт у процесі.',
                    'Приклад: A new road was being constructed.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The students {a1} by the professor at that time.',
                'answers' => ['a1' => 'were being interviewed'],
                'options' => [
                    'a1' => ['were being interviewed', 'was being interviewed', 'were interviewed', 'were interviewing'],
                ],
                'verb_hints' => ['a1' => 'interview'],
                'hints' => [
                    '**Past Continuous Passive** з агентом: were + being + V3 + by.',
                    'Приклад: The candidates were being evaluated.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The pizza {a1} when the doorbell rang.',
                'answers' => ['a1' => 'was being made'],
                'options' => [
                    'a1' => ['was being made', 'were being made', 'was made', 'was making'],
                ],
                'verb_hints' => ['a1' => 'make'],
                'hints' => [
                    '**Past Continuous Passive**: was + being + V3.',
                    'Приклад: The bread was being baked.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The guests {a1} when the power went out.',
                'answers' => ['a1' => 'were being served'],
                'options' => [
                    'a1' => ['were being served', 'was being served', 'were served', 'were serving'],
                ],
                'verb_hints' => ['a1' => 'serve'],
                'hints' => [
                    '**Past Continuous Passive** для множини: were + being + V3.',
                    'Приклад: The customers were being attended to.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The documents {a1} by the clerk.',
                'answers' => ['a1' => 'were being organized'],
                'options' => [
                    'a1' => ['were being organized', 'was being organized', 'were organized', 'were organizing'],
                ],
                'verb_hints' => ['a1' => 'organize'],
                'hints' => [
                    '**Past Continuous Passive** з агентом.',
                    'Приклад: The files were being sorted.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The project {a1} at that moment.',
                'answers' => ['a1' => "wasn't being developed"],
                'options' => [
                    'a1' => ["wasn't being developed", "weren't being developed", "wasn't developed", "didn't develop"],
                ],
                'verb_hints' => ['a1' => 'develop'],
                'hints' => [
                    '**Past Continuous Passive Negative**: wasn\'t + being + V3.',
                    'Приклад: The plan wasn\'t being implemented.',
                ],
                'tag_ids' => [$negativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The walls {a1} at that time.',
                'answers' => ['a1' => "weren't being painted"],
                'options' => [
                    'a1' => ["weren't being painted", "wasn't being painted", "weren't painted", "didn't paint"],
                ],
                'verb_hints' => ['a1' => 'paint'],
                'hints' => [
                    '**Past Continuous Passive Negative** для множини: weren\'t + being + V3.',
                    'Приклад: The rooms weren\'t being renovated.',
                ],
                'tag_ids' => [$negativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => '{a1} the report being written when you called?',
                'answers' => ['a1' => 'Was'],
                'options' => [
                    'a1' => ['Was', 'Were', 'Did', 'Is'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Past Continuous Passive Question**: Was + subject + being + V3?',
                    'Приклад: Was the document being prepared?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'What {a1} being discussed when you entered?',
                'answers' => ['a1' => 'was'],
                'options' => [
                    'a1' => ['was', 'were', 'did', 'is'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Past Continuous Passive**: What + was + being + V3?',
                    'Приклад: What was being considered?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The cake {a1} when the oven broke.',
                'answers' => ['a1' => 'was being baked'],
                'options' => [
                    'a1' => ['was being baked', 'were being baked', 'was baked', 'was baking'],
                ],
                'verb_hints' => ['a1' => 'bake'],
                'hints' => [
                    '**Past Continuous Passive**: was + being + V3.',
                    'Приклад: The cookies were being prepared.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'Who {a1} being trained at that time?',
                'answers' => ['a1' => 'was'],
                'options' => [
                    'a1' => ['was', 'were', 'did', 'is'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Past Continuous Passive**: Who + was + being + V3?',
                    'Приклад: Who was being coached?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The flowers {a1} when the gardener left.',
                'answers' => ['a1' => 'were being watered'],
                'options' => [
                    'a1' => ['were being watered', 'was being watered', 'were watered', 'were watering'],
                ],
                'verb_hints' => ['a1' => 'water'],
                'hints' => [
                    '**Past Continuous Passive** для множини: were + being + V3.',
                    'Приклад: The plants were being tended.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],

            // ===== B1 Level: 12 questions =====
            [
                'level' => 'B1',
                'question' => 'The new software {a1} when the system crashed.',
                'answers' => ['a1' => 'was being installed'],
                'options' => [
                    'a1' => ['was being installed', 'were being installed', 'was installed', 'was installing'],
                ],
                'verb_hints' => ['a1' => 'install'],
                'hints' => [
                    '**Past Continuous Passive** для технічних процесів.',
                    'Приклад: The update was being downloaded.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'Safety measures {a1} while the inspection occurred.',
                'answers' => ['a1' => 'were being reviewed'],
                'options' => [
                    'a1' => ['were being reviewed', 'was being reviewed', 'were reviewed', 'were reviewing'],
                ],
                'verb_hints' => ['a1' => 'review'],
                'hints' => [
                    '**Past Continuous Passive** для перевірок.',
                    'Приклад: Regulations were being examined.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The data {a1} when the server failed.',
                'answers' => ['a1' => 'was being processed'],
                'options' => [
                    'a1' => ['was being processed', 'were being processed', 'was processed', 'was processing'],
                ],
                'verb_hints' => ['a1' => 'process'],
                'hints' => [
                    '**Past Continuous Passive** для обробки даних.',
                    'Приклад: The information was being analyzed.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The patient {a1} when the emergency arose.',
                'answers' => ['a1' => 'was being operated on'],
                'options' => [
                    'a1' => ['was being operated on', 'were being operated on', 'was operated', 'was operating'],
                ],
                'verb_hints' => ['a1' => 'operate on'],
                'hints' => [
                    '**Past Continuous Passive** для медичних процедур.',
                    'Приклад: The victim was being treated.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The contract {a1} at that point.',
                'answers' => ['a1' => "wasn't being negotiated"],
                'options' => [
                    'a1' => ["wasn't being negotiated", "weren't being negotiated", "wasn't negotiated", "didn't negotiate"],
                ],
                'verb_hints' => ['a1' => 'negotiate'],
                'hints' => [
                    '**Past Continuous Passive Negative**: wasn\'t + being + V3.',
                    'Приклад: The deal wasn\'t being discussed.',
                ],
                'tag_ids' => [$negativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'New employees {a1} during that period.',
                'answers' => ['a1' => "weren't being trained"],
                'options' => [
                    'a1' => ["weren't being trained", "wasn't being trained", "weren't trained", "didn't train"],
                ],
                'verb_hints' => ['a1' => 'train'],
                'hints' => [
                    '**Past Continuous Passive Negative** для множини.',
                    'Приклад: Workers weren\'t being hired.',
                ],
                'tag_ids' => [$negativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => '{a1} the issue being addressed when you left?',
                'answers' => ['a1' => 'Was'],
                'options' => [
                    'a1' => ['Was', 'Were', 'Did', 'Is'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Past Continuous Passive Question**: Was + subject + being + V3?',
                    'Приклад: Was the problem being solved?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'Why {a1} the materials being replaced?',
                'answers' => ['a1' => 'were'],
                'options' => [
                    'a1' => ['were', 'was', 'did', 'are'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Past Continuous Passive**: Why + were + subject + being + V3?',
                    'Приклад: Why was the equipment being changed?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The old building {a1} when the order came to stop.',
                'answers' => ['a1' => 'was being demolished'],
                'options' => [
                    'a1' => ['was being demolished', 'were being demolished', 'was demolished', 'was demolishing'],
                ],
                'verb_hints' => ['a1' => 'demolish'],
                'hints' => [
                    '**Past Continuous Passive** для знесення.',
                    'Приклад: The structure was being torn down.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'How {a1} the situation being handled?',
                'answers' => ['a1' => 'was'],
                'options' => [
                    'a1' => ['was', 'were', 'did', 'is'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Past Continuous Passive**: How + was + subject + being + V3?',
                    'Приклад: How was the crisis being managed?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The tickets {a1} when the website crashed.',
                'answers' => ['a1' => 'were being sold'],
                'options' => [
                    'a1' => ['were being sold', 'was being sold', 'were sold', 'were selling'],
                ],
                'verb_hints' => ['a1' => 'sell'],
                'hints' => [
                    '**Past Continuous Passive** для продажу.',
                    'Приклад: The products were being distributed.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The curriculum {a1} when the new policy was announced.',
                'answers' => ['a1' => 'was being revised'],
                'options' => [
                    'a1' => ['was being revised', 'were being revised', 'was revised', 'was revising'],
                ],
                'verb_hints' => ['a1' => 'revise'],
                'hints' => [
                    '**Past Continuous Passive** для освітніх змін.',
                    'Приклад: The program was being updated.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],

            // ===== B2 Level: 12 questions =====
            [
                'level' => 'B2',
                'question' => 'The environmental impact {a1} when the project was halted.',
                'answers' => ['a1' => 'was being assessed'],
                'options' => [
                    'a1' => ['was being assessed', 'were being assessed', 'was assessed', 'was assessing'],
                ],
                'verb_hints' => ['a1' => 'assess'],
                'hints' => [
                    '**Past Continuous Passive** для оцінювання.',
                    'Приклад: The risks were being evaluated.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Negotiations {a1} when the conflict escalated.',
                'answers' => ['a1' => 'were being conducted'],
                'options' => [
                    'a1' => ['were being conducted', 'was being conducted', 'were conducted', 'were conducting'],
                ],
                'verb_hints' => ['a1' => 'conduct'],
                'hints' => [
                    '**Past Continuous Passive** для переговорів.',
                    'Приклад: Talks were being held.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The manuscript {a1} when the author withdrew it.',
                'answers' => ['a1' => 'was being reviewed'],
                'options' => [
                    'a1' => ['was being reviewed', 'were being reviewed', 'was reviewed', 'was reviewing'],
                ],
                'verb_hints' => ['a1' => 'review'],
                'hints' => [
                    '**Past Continuous Passive** для видавництва.',
                    'Приклад: The article was being edited.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Sustainable practices {a1} when the crisis hit.',
                'answers' => ['a1' => 'were being implemented'],
                'options' => [
                    'a1' => ['were being implemented', 'was being implemented', 'were implemented', 'were implementing'],
                ],
                'verb_hints' => ['a1' => 'implement'],
                'hints' => [
                    '**Past Continuous Passive** для ініціатив.',
                    'Приклад: New policies were being introduced.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Customer complaints {a1} seriously at that time.',
                'answers' => ['a1' => "weren't being taken"],
                'options' => [
                    'a1' => ["weren't being taken", "wasn't being taken", "weren't taken", "didn't take"],
                ],
                'verb_hints' => ['a1' => 'take'],
                'hints' => [
                    '**Past Continuous Passive Negative** для критики.',
                    'Приклад: Concerns weren\'t being addressed.',
                ],
                'tag_ids' => [$negativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The issue {a1} with the urgency it deserved.',
                'answers' => ['a1' => "wasn't being treated"],
                'options' => [
                    'a1' => ["wasn't being treated", "weren't being treated", "wasn't treated", "didn't treat"],
                ],
                'verb_hints' => ['a1' => 'treat'],
                'hints' => [
                    '**Past Continuous Passive Negative** для критики.',
                    'Приклад: The matter wasn\'t being handled properly.',
                ],
                'tag_ids' => [$negativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => '{a1} sufficient resources being allocated when you checked?',
                'answers' => ['a1' => 'Were'],
                'options' => [
                    'a1' => ['Were', 'Was', 'Did', 'Are'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Past Continuous Passive Question** для ресурсів.',
                    'Приклад: Was enough funding being provided?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'By whom {a1} the investigation being led?',
                'answers' => ['a1' => 'was'],
                'options' => [
                    'a1' => ['was', 'were', 'did', 'is'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Formal Past Continuous Passive Question** з "by whom".',
                    'Приклад: By whom was the research being conducted?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The data breach {a1} when new evidence emerged.',
                'answers' => ['a1' => 'was being investigated'],
                'options' => [
                    'a1' => ['was being investigated', 'were being investigated', 'was investigated', 'was investigating'],
                ],
                'verb_hints' => ['a1' => 'investigate'],
                'hints' => [
                    '**Past Continuous Passive** для розслідувань.',
                    'Приклад: The incident was being examined.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Several reforms {a1} when the government changed.',
                'answers' => ['a1' => 'were being introduced'],
                'options' => [
                    'a1' => ['were being introduced', 'was being introduced', 'were introduced', 'were introducing'],
                ],
                'verb_hints' => ['a1' => 'introduce'],
                'hints' => [
                    '**Past Continuous Passive** для реформ.',
                    'Приклад: Changes were being implemented.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Why {a1} the deadline being extended?',
                'answers' => ['a1' => "wasn't"],
                'options' => [
                    'a1' => ["wasn't", "weren't", "didn't", "isn't"],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Negative WH-Question Past Continuous Passive**: Why + wasn\'t + subject + being + V3?',
                    'Приклад: Why wasn\'t the schedule being adjusted?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The infrastructure {a1} when funding was cut.',
                'answers' => ['a1' => 'was being expanded'],
                'options' => [
                    'a1' => ['was being expanded', 'were being expanded', 'was expanded', 'was expanding'],
                ],
                'verb_hints' => ['a1' => 'expand'],
                'hints' => [
                    '**Past Continuous Passive** для розвитку.',
                    'Приклад: The network was being upgraded.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],

            // ===== C1 Level: 12 questions =====
            [
                'level' => 'C1',
                'question' => 'The theoretical framework {a1} when new data contradicted it.',
                'answers' => ['a1' => 'was being refined'],
                'options' => [
                    'a1' => ['was being refined', 'were being refined', 'was refined', 'was refining'],
                ],
                'verb_hints' => ['a1' => 'refine'],
                'hints' => [
                    '**Past Continuous Passive** для академічного вдосконалення.',
                    'Приклад: The methodology was being improved.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Cross-cultural competencies {a1} when the program was cancelled.',
                'answers' => ['a1' => 'were being developed'],
                'options' => [
                    'a1' => ['were being developed', 'was being developed', 'were developed', 'were developing'],
                ],
                'verb_hints' => ['a1' => 'develop'],
                'hints' => [
                    '**Past Continuous Passive** для освітніх програм.',
                    'Приклад: Skills were being cultivated.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The geopolitical implications {a1} when the situation changed.',
                'answers' => ['a1' => 'were being scrutinized'],
                'options' => [
                    'a1' => ['were being scrutinized', 'was being scrutinized', 'were scrutinized', 'were scrutinizing'],
                ],
                'verb_hints' => ['a1' => 'scrutinize'],
                'hints' => [
                    '**Past Continuous Passive** для аналізу.',
                    'Приклад: The consequences were being examined.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Innovative approaches {a1} when the project was terminated.',
                'answers' => ['a1' => 'were being explored'],
                'options' => [
                    'a1' => ['were being explored', 'was being explored', 'were explored', 'were exploring'],
                ],
                'verb_hints' => ['a1' => 'explore'],
                'hints' => [
                    '**Past Continuous Passive** для досліджень.',
                    'Приклад: New solutions were being sought.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The nuances of the policy {a1} in public discourse.',
                'answers' => ['a1' => "weren't being articulated"],
                'options' => [
                    'a1' => ["weren't being articulated", "wasn't being articulated", "weren't articulated", "didn't articulate"],
                ],
                'verb_hints' => ['a1' => 'articulate'],
                'hints' => [
                    '**Past Continuous Passive Negative** для критики.',
                    'Приклад: The details weren\'t being communicated.',
                ],
                'tag_ids' => [$negativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Indigenous perspectives {a1} in the process.',
                'answers' => ['a1' => "weren't being incorporated"],
                'options' => [
                    'a1' => ["weren't being incorporated", "wasn't being incorporated", "weren't incorporated", "didn't incorporate"],
                ],
                'verb_hints' => ['a1' => 'incorporate'],
                'hints' => [
                    '**Past Continuous Passive Negative** для соціальної критики.',
                    'Приклад: Minority voices weren\'t being heard.',
                ],
                'tag_ids' => [$negativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => '{a1} ethical standards being upheld during that period?',
                'answers' => ['a1' => 'Were'],
                'options' => [
                    'a1' => ['Were', 'Was', 'Did', 'Are'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Past Continuous Passive Question** для етичних питань.',
                    'Приклад: Were regulations being followed?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'To what extent {a1} privacy rights being protected?',
                'answers' => ['a1' => 'were'],
                'options' => [
                    'a1' => ['were', 'was', 'did', 'are'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Formal WH-Question Past Continuous Passive**.',
                    'Приклад: To what extent was security being ensured?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The socioeconomic disparities {a1} when policy shifted.',
                'answers' => ['a1' => 'were being addressed'],
                'options' => [
                    'a1' => ['were being addressed', 'was being addressed', 'were addressed', 'were addressing'],
                ],
                'verb_hints' => ['a1' => 'address'],
                'hints' => [
                    '**Past Continuous Passive** для соціальних програм.',
                    'Приклад: Inequalities were being tackled.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Biodiversity conservation efforts {a1} when funding ended.',
                'answers' => ['a1' => 'were being intensified'],
                'options' => [
                    'a1' => ['were being intensified', 'was being intensified', 'were intensified', 'were intensifying'],
                ],
                'verb_hints' => ['a1' => 'intensify'],
                'hints' => [
                    '**Past Continuous Passive** для екологічних зусиль.',
                    'Приклад: Protections were being strengthened.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Why {a1} the recommendations being ignored?',
                'answers' => ['a1' => 'were'],
                'options' => [
                    'a1' => ['were', 'was', 'did', 'are'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Past Continuous Passive** для критики.',
                    'Приклад: Why were the suggestions being dismissed?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'A paradigm shift {a1} in how we approach education.',
                'answers' => ['a1' => 'was being observed'],
                'options' => [
                    'a1' => ['was being observed', 'were being observed', 'was observed', 'was observing'],
                ],
                'verb_hints' => ['a1' => 'observe'],
                'hints' => [
                    '**Past Continuous Passive** для спостережень.',
                    'Приклад: A transformation was being witnessed.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],

            // ===== C2 Level: 12 questions =====
            [
                'level' => 'C2',
                'question' => 'The epistemological foundations {a1} when the paradigm shifted.',
                'answers' => ['a1' => 'were being reexamined'],
                'options' => [
                    'a1' => ['were being reexamined', 'was being reexamined', 'were reexamined', 'were reexamining'],
                ],
                'verb_hints' => ['a1' => 'reexamine'],
                'hints' => [
                    '**Past Continuous Passive** для філософського аналізу.',
                    'Приклад: Assumptions were being questioned.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The hegemonic discourse {a1} through critical analysis.',
                'answers' => ['a1' => 'was being deconstructed'],
                'options' => [
                    'a1' => ['was being deconstructed', 'were being deconstructed', 'was deconstructed', 'was deconstructing'],
                ],
                'verb_hints' => ['a1' => 'deconstruct'],
                'hints' => [
                    '**Past Continuous Passive** для критичного аналізу.',
                    'Приклад: The narrative was being challenged.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'Transdisciplinary methodologies {a1} when the study concluded.',
                'answers' => ['a1' => 'were being employed'],
                'options' => [
                    'a1' => ['were being employed', 'was being employed', 'were employed', 'were employing'],
                ],
                'verb_hints' => ['a1' => 'employ'],
                'hints' => [
                    '**Past Continuous Passive** для методологій.',
                    'Приклад: Novel techniques were being applied.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The ontological premises {a1} when new evidence emerged.',
                'answers' => ['a1' => 'were being reconsidered'],
                'options' => [
                    'a1' => ['were being reconsidered', 'was being reconsidered', 'were reconsidered', 'were reconsidering'],
                ],
                'verb_hints' => ['a1' => 'reconsider'],
                'hints' => [
                    '**Past Continuous Passive** для перегляду теорій.',
                    'Приклад: The hypotheses were being revised.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The subtleties of the argument {a1} in mainstream discourse.',
                'answers' => ['a1' => "weren't being conveyed"],
                'options' => [
                    'a1' => ["weren't being conveyed", "wasn't being conveyed", "weren't conveyed", "didn't convey"],
                ],
                'verb_hints' => ['a1' => 'convey'],
                'hints' => [
                    '**Past Continuous Passive Negative** для медіа-критики.',
                    'Приклад: The complexities weren\'t being explained.',
                ],
                'tag_ids' => [$negativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The ramifications of the policy {a1} with sufficient rigor.',
                'answers' => ['a1' => "weren't being analyzed"],
                'options' => [
                    'a1' => ["weren't being analyzed", "wasn't being analyzed", "weren't analyzed", "didn't analyze"],
                ],
                'verb_hints' => ['a1' => 'analyze'],
                'hints' => [
                    '**Past Continuous Passive Negative** для критики.',
                    'Приклад: The consequences weren\'t being evaluated.',
                ],
                'tag_ids' => [$negativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => '{a1} the axioms of this theory being interrogated?',
                'answers' => ['a1' => 'Were'],
                'options' => [
                    'a1' => ['Were', 'Was', 'Did', 'Are'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Past Continuous Passive Question** для академічних дискусій.',
                    'Приклад: Were the assumptions being tested?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'Through what mechanisms {a1} systemic inequalities being perpetuated?',
                'answers' => ['a1' => 'were'],
                'options' => [
                    'a1' => ['were', 'was', 'did', 'are'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Formal WH-Question Past Continuous Passive**.',
                    'Приклад: Through what processes were biases being maintained?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The hermeneutical approach {a1} to interpret the texts.',
                'answers' => ['a1' => 'was being utilized'],
                'options' => [
                    'a1' => ['was being utilized', 'were being utilized', 'was utilized', 'was utilizing'],
                ],
                'verb_hints' => ['a1' => 'utilize'],
                'hints' => [
                    '**Past Continuous Passive** для методологій.',
                    'Приклад: The framework was being applied.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'Posthumanist perspectives {a1} into the discourse.',
                'answers' => ['a1' => 'were being integrated'],
                'options' => [
                    'a1' => ['were being integrated', 'was being integrated', 'were integrated', 'were integrating'],
                ],
                'verb_hints' => ['a1' => 'integrate'],
                'hints' => [
                    '**Past Continuous Passive** для інтеграції ідей.',
                    'Приклад: New theories were being incorporated.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The dialectical tensions {a1} in the literature.',
                'answers' => ['a1' => 'were being explored'],
                'options' => [
                    'a1' => ['were being explored', 'was being explored', 'were explored', 'were exploring'],
                ],
                'verb_hints' => ['a1' => 'explore'],
                'hints' => [
                    '**Past Continuous Passive** для літературного аналізу.',
                    'Приклад: Contradictions were being examined.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The teleological assumptions {a1} by the scholars.',
                'answers' => ['a1' => 'were being challenged'],
                'options' => [
                    'a1' => ['were being challenged', 'was being challenged', 'were challenged', 'were challenging'],
                ],
                'verb_hints' => ['a1' => 'challenge'],
                'hints' => [
                    '**Past Continuous Passive** для наукових дебатів.',
                    'Приклад: Traditional views were being questioned.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastContinuousTagId, $passiveTagId],
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
        return "✅ Правильно! «{$correct}» — це коректна форма Past Continuous Passive. Формула: was/were + being + past participle (V3).";
    }

    private function wrongExplanation(string $wrong, string $correct): string
    {
        // Missing "being"
        if ((str_contains(strtolower($correct), 'was being') || str_contains(strtolower($correct), 'were being'))
            && !str_contains(strtolower($wrong), 'being')) {
            if (str_contains(strtolower($wrong), 'was ') || str_contains(strtolower($wrong), 'were ')) {
                return "❌ «{$wrong}» — це Past Simple Passive. Для Past Continuous Passive потрібен \"being\": was/were + being + V3.";
            }
        }

        // Active voice
        if (!str_contains(strtolower($wrong), 'was') && !str_contains(strtolower($wrong), 'were')
            && !str_contains(strtolower($wrong), "wasn't") && !str_contains(strtolower($wrong), "weren't")) {
            return "❌ «{$wrong}» — це Active Voice. Тут потрібен Passive Voice: was/were + being + V3.";
        }

        // Did instead of was/were
        if (str_contains(strtolower($wrong), 'did')) {
            return "❌ «{$wrong}» — did використовується в Active Voice. У Passive Voice потрібен was/were + being.";
        }

        // Wrong number agreement
        if (str_contains(strtolower($correct), 'were being') && str_contains(strtolower($wrong), 'was being')) {
            return "❌ «{$wrong}» — неправильне узгодження числа. З множиною використовуйте «were being», не «was being».";
        }
        if (str_contains(strtolower($correct), 'was being') && str_contains(strtolower($wrong), 'were being')) {
            return "❌ «{$wrong}» — неправильне узгодження числа. З одниною використовуйте «was being», не «were being».";
        }

        return "❌ «{$wrong}» — неправильна форма. Для Past Continuous Passive потрібно: was/were + being + past participle.";
    }
}
