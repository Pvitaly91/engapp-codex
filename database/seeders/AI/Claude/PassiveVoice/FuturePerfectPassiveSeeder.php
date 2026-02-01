<?php

namespace Database\Seeders\AI\Claude\PassiveVoice;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class FuturePerfectPassiveSeeder extends QuestionSeeder
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
        $sourceId = Source::firstOrCreate(['name' => 'AI Generated: Future Perfect Passive'])->id;
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
            ['name' => 'Future Perfect Passive'],
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

        $futurePerfectTagId = Tag::firstOrCreate(['name' => 'Future Perfect'], ['category' => 'Tense'])->id;
        $passiveTagId = Tag::firstOrCreate(['name' => 'Passive Voice'], ['category' => 'Voice'])->id;

        return [
            // ===== A1 Level: 12 questions =====
            [
                'level' => 'A1',
                'question' => 'The house {a1} by next month.',
                'answers' => ['a1' => 'will have been painted'],
                'options' => [
                    'a1' => ['will have been painted', 'will be painted', 'has been painted', 'was painted'],
                ],
                'verb_hints' => ['a1' => 'paint'],
                'hints' => [
                    '**Future Perfect Passive** утворюється: will + have + been + past participle (V3).',
                    'Використовується для дій, які будуть завершені до певного моменту в майбутньому.',
                    'Приклад: The room will have been cleaned by tomorrow.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The letter {a1} by Friday.',
                'answers' => ['a1' => 'will have been sent'],
                'options' => [
                    'a1' => ['will have been sent', 'will be sent', 'has been sent', 'was sent'],
                ],
                'verb_hints' => ['a1' => 'send'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The package will have been delivered.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The cake {a1} by 5 PM.',
                'answers' => ['a1' => 'will have been made'],
                'options' => [
                    'a1' => ['will have been made', 'will be made', 'has been made', 'was made'],
                ],
                'verb_hints' => ['a1' => 'make'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The meal will have been prepared.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The homework {a1} by tonight.',
                'answers' => ['a1' => 'will have been finished'],
                'options' => [
                    'a1' => ['will have been finished', 'will be finished', 'has been finished', 'was finished'],
                ],
                'verb_hints' => ['a1' => 'finish'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The task will have been completed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The window {a1} by tomorrow.',
                'answers' => ['a1' => 'will have been fixed'],
                'options' => [
                    'a1' => ['will have been fixed', 'will be fixed', 'has been fixed', 'was fixed'],
                ],
                'verb_hints' => ['a1' => 'fix'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The door will have been repaired.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The car {a1} by next week.',
                'answers' => ['a1' => 'will have been sold'],
                'options' => [
                    'a1' => ['will have been sold', 'will be sold', 'has been sold', 'was sold'],
                ],
                'verb_hints' => ['a1' => 'sell'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The house will have been bought.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The book {a1} by Monday.',
                'answers' => ['a1' => 'will have been read'],
                'options' => [
                    'a1' => ['will have been read', 'will be read', 'has been read', 'was read'],
                ],
                'verb_hints' => ['a1' => 'read'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The story will have been told.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The clothes {a1} by noon.',
                'answers' => ['a1' => 'will have been washed'],
                'options' => [
                    'a1' => ['will have been washed', 'will be washed', 'have been washed', 'were washed'],
                ],
                'verb_hints' => ['a1' => 'wash'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The dishes will have been done.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The room {a1} by evening.',
                'answers' => ['a1' => 'will have been cleaned'],
                'options' => [
                    'a1' => ['will have been cleaned', 'will be cleaned', 'has been cleaned', 'was cleaned'],
                ],
                'verb_hints' => ['a1' => 'clean'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The house will have been tidied.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The song {a1} by the time you arrive.',
                'answers' => ['a1' => 'will have been sung'],
                'options' => [
                    'a1' => ['will have been sung', 'will be sung', 'has been sung', 'was sung'],
                ],
                'verb_hints' => ['a1' => 'sing'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The music will have been played.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The flowers {a1} by 6 PM.',
                'answers' => ['a1' => 'will have been watered'],
                'options' => [
                    'a1' => ['will have been watered', 'will be watered', 'have been watered', 'were watered'],
                ],
                'verb_hints' => ['a1' => 'water'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The plants will have been tended.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The dog {a1} by bedtime.',
                'answers' => ['a1' => 'will have been fed'],
                'options' => [
                    'a1' => ['will have been fed', 'will be fed', 'has been fed', 'was fed'],
                ],
                'verb_hints' => ['a1' => 'feed'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The cat will have been given food.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],

            // ===== A2 Level: 12 questions =====
            [
                'level' => 'A2',
                'question' => 'The report {a1} by the end of the day.',
                'answers' => ['a1' => 'will have been written'],
                'options' => [
                    'a1' => ['will have been written', 'will be written', 'has been written', 'was written'],
                ],
                'verb_hints' => ['a1' => 'write'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Показує завершену дію до певного моменту в майбутньому.',
                    'Приклад: The document will have been prepared.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The building {a1} by next year.',
                'answers' => ['a1' => 'will have been constructed'],
                'options' => [
                    'a1' => ['will have been constructed', 'will be constructed', 'has been constructed', 'was constructed'],
                ],
                'verb_hints' => ['a1' => 'construct'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The bridge will have been built.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The exam papers {a1} by Tuesday.',
                'answers' => ['a1' => 'will have been marked'],
                'options' => [
                    'a1' => ['will have been marked', 'will be marked', 'have been marked', 'were marked'],
                ],
                'verb_hints' => ['a1' => 'mark'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The tests will have been graded.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The project {a1} by the deadline.',
                'answers' => ['a1' => 'will have been completed'],
                'options' => [
                    'a1' => ['will have been completed', 'will be completed', 'has been completed', 'was completed'],
                ],
                'verb_hints' => ['a1' => 'complete'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The work will have been done.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The guests {a1} by 7 PM.',
                'answers' => ['a1' => 'will have been welcomed'],
                'options' => [
                    'a1' => ['will have been welcomed', 'will be welcomed', 'have been welcomed', 'were welcomed'],
                ],
                'verb_hints' => ['a1' => 'welcome'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The visitors will have been greeted.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The meal {a1} by the time they arrive.',
                'answers' => ['a1' => 'will have been cooked'],
                'options' => [
                    'a1' => ['will have been cooked', 'will be cooked', 'has been cooked', 'was cooked'],
                ],
                'verb_hints' => ['a1' => 'cook'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The dinner will have been prepared.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The trees {a1} by spring.',
                'answers' => ['a1' => 'will have been planted'],
                'options' => [
                    'a1' => ['will have been planted', 'will be planted', 'have been planted', 'were planted'],
                ],
                'verb_hints' => ['a1' => 'plant'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The seeds will have been sown.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The road {a1} by summer.',
                'answers' => ['a1' => 'will have been repaired'],
                'options' => [
                    'a1' => ['will have been repaired', 'will be repaired', 'has been repaired', 'was repaired'],
                ],
                'verb_hints' => ['a1' => 'repair'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The street will have been paved.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The candidates {a1} by noon tomorrow.',
                'answers' => ['a1' => 'will have been interviewed'],
                'options' => [
                    'a1' => ['will have been interviewed', 'will be interviewed', 'have been interviewed', 'were interviewed'],
                ],
                'verb_hints' => ['a1' => 'interview'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The applicants will have been assessed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The furniture {a1} by the end of the week.',
                'answers' => ['a1' => 'will have been delivered'],
                'options' => [
                    'a1' => ['will have been delivered', 'will be delivered', 'has been delivered', 'was delivered'],
                ],
                'verb_hints' => ['a1' => 'deliver'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The packages will have been shipped.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The film {a1} by midnight.',
                'answers' => ['a1' => 'will have been shown'],
                'options' => [
                    'a1' => ['will have been shown', 'will be shown', 'has been shown', 'was shown'],
                ],
                'verb_hints' => ['a1' => 'show'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The movie will have been screened.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The walls {a1} by the weekend.',
                'answers' => ['a1' => 'will have been painted'],
                'options' => [
                    'a1' => ['will have been painted', 'will be painted', 'have been painted', 'were painted'],
                ],
                'verb_hints' => ['a1' => 'paint'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The ceiling will have been whitewashed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],

            // ===== B1 Level: 12 questions =====
            [
                'level' => 'B1',
                'question' => 'The equipment {a1} by the time you return.',
                'answers' => ['a1' => 'will have been installed'],
                'options' => [
                    'a1' => ['will have been installed', 'will be installed', 'has been installed', 'was installed'],
                ],
                'verb_hints' => ['a1' => 'install'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Використовується для опису завершеної дії до певного моменту в майбутньому.',
                    'Приклад: The machinery will have been set up.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The website {a1} by next month.',
                'answers' => ['a1' => 'will have been launched'],
                'options' => [
                    'a1' => ['will have been launched', 'will be launched', 'has been launched', 'was launched'],
                ],
                'verb_hints' => ['a1' => 'launch'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The application will have been released.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The evidence {a1} by the end of the trial.',
                'answers' => ['a1' => 'will have been examined'],
                'options' => [
                    'a1' => ['will have been examined', 'will be examined', 'has been examined', 'was examined'],
                ],
                'verb_hints' => ['a1' => 'examine'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The documents will have been reviewed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The concert {a1} by 10 PM.',
                'answers' => ['a1' => 'will have been recorded'],
                'options' => [
                    'a1' => ['will have been recorded', 'will be recorded', 'has been recorded', 'was recorded'],
                ],
                'verb_hints' => ['a1' => 'record'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The performance will have been filmed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The proposal {a1} by the board meeting.',
                'answers' => ['a1' => 'will have been approved'],
                'options' => [
                    'a1' => ['will have been approved', 'will be approved', 'has been approved', 'was approved'],
                ],
                'verb_hints' => ['a1' => 'approve'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The contract will have been signed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The students {a1} by graduation day.',
                'answers' => ['a1' => 'will have been assessed'],
                'options' => [
                    'a1' => ['will have been assessed', 'will be assessed', 'have been assessed', 'were assessed'],
                ],
                'verb_hints' => ['a1' => 'assess'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The candidates will have been evaluated.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The packages {a1} by tomorrow evening.',
                'answers' => ['a1' => 'will have been shipped'],
                'options' => [
                    'a1' => ['will have been shipped', 'will be shipped', 'have been shipped', 'were shipped'],
                ],
                'verb_hints' => ['a1' => 'ship'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The orders will have been dispatched.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The artwork {a1} by the exhibition opening.',
                'answers' => ['a1' => 'will have been displayed'],
                'options' => [
                    'a1' => ['will have been displayed', 'will be displayed', 'has been displayed', 'was displayed'],
                ],
                'verb_hints' => ['a1' => 'display'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The paintings will have been hung.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The data {a1} by the end of the analysis.',
                'answers' => ['a1' => 'will have been collected'],
                'options' => [
                    'a1' => ['will have been collected', 'will be collected', 'has been collected', 'was collected'],
                ],
                'verb_hints' => ['a1' => 'collect'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The information will have been gathered.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The passengers {a1} by departure time.',
                'answers' => ['a1' => 'will have been boarded'],
                'options' => [
                    'a1' => ['will have been boarded', 'will be boarded', 'have been boarded', 'were boarded'],
                ],
                'verb_hints' => ['a1' => 'board'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The travelers will have been checked in.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The files {a1} by the time you need them.',
                'answers' => ['a1' => 'will have been uploaded'],
                'options' => [
                    'a1' => ['will have been uploaded', 'will be uploaded', 'have been uploaded', 'were uploaded'],
                ],
                'verb_hints' => ['a1' => 'upload'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The documents will have been transferred.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The materials {a1} by the start of the project.',
                'answers' => ['a1' => 'will have been organized'],
                'options' => [
                    'a1' => ['will have been organized', 'will be organized', 'have been organized', 'were organized'],
                ],
                'verb_hints' => ['a1' => 'organize'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The supplies will have been sorted.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],

            // ===== B2 Level: 12 questions =====
            [
                'level' => 'B2',
                'question' => 'The scientific research {a1} by the end of the year.',
                'answers' => ['a1' => 'will have been published'],
                'options' => [
                    'a1' => ['will have been published', 'will be published', 'has been published', 'was published'],
                ],
                'verb_hints' => ['a1' => 'publish'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Використовується для підкреслення завершеності дії до певного моменту в майбутньому.',
                    'Приклад: The findings will have been released.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The environmental impact {a1} before construction begins.',
                'answers' => ['a1' => 'will have been assessed'],
                'options' => [
                    'a1' => ['will have been assessed', 'will be assessed', 'has been assessed', 'was assessed'],
                ],
                'verb_hints' => ['a1' => 'assess'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The consequences will have been evaluated.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The negotiations {a1} by the summit.',
                'answers' => ['a1' => 'will have been concluded'],
                'options' => [
                    'a1' => ['will have been concluded', 'will be concluded', 'have been concluded', 'were concluded'],
                ],
                'verb_hints' => ['a1' => 'conclude'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The talks will have been finalized.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The suspect {a1} by the time the lawyer arrives.',
                'answers' => ['a1' => 'will have been arrested'],
                'options' => [
                    'a1' => ['will have been arrested', 'will be arrested', 'has been arrested', 'was arrested'],
                ],
                'verb_hints' => ['a1' => 'arrest'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The criminal will have been caught.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The budget {a1} by next quarter.',
                'answers' => ['a1' => 'will have been allocated'],
                'options' => [
                    'a1' => ['will have been allocated', 'will be allocated', 'has been allocated', 'was allocated'],
                ],
                'verb_hints' => ['a1' => 'allocate'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The funds will have been distributed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The historical artifacts {a1} by the museum reopening.',
                'answers' => ['a1' => 'will have been restored'],
                'options' => [
                    'a1' => ['will have been restored', 'will be restored', 'have been restored', 'were restored'],
                ],
                'verb_hints' => ['a1' => 'restore'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The relics will have been preserved.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The confidential information {a1} by the security update.',
                'answers' => ['a1' => 'will have been encrypted'],
                'options' => [
                    'a1' => ['will have been encrypted', 'will be encrypted', 'has been encrypted', 'was encrypted'],
                ],
                'verb_hints' => ['a1' => 'encrypt'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The data will have been secured.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The new employees {a1} by their first day.',
                'answers' => ['a1' => 'will have been trained'],
                'options' => [
                    'a1' => ['will have been trained', 'will be trained', 'have been trained', 'were trained'],
                ],
                'verb_hints' => ['a1' => 'train'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The staff will have been instructed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The medicine {a1} before the surgery.',
                'answers' => ['a1' => 'will have been administered'],
                'options' => [
                    'a1' => ['will have been administered', 'will be administered', 'has been administered', 'was administered'],
                ],
                'verb_hints' => ['a1' => 'administer'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The treatment will have been given.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The policy changes {a1} by the new fiscal year.',
                'answers' => ['a1' => 'will have been implemented'],
                'options' => [
                    'a1' => ['will have been implemented', 'will be implemented', 'have been implemented', 'were implemented'],
                ],
                'verb_hints' => ['a1' => 'implement'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The regulations will have been enforced.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The witness statements {a1} by the court date.',
                'answers' => ['a1' => 'will have been verified'],
                'options' => [
                    'a1' => ['will have been verified', 'will be verified', 'have been verified', 'were verified'],
                ],
                'verb_hints' => ['a1' => 'verify'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The testimonies will have been confirmed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The infrastructure {a1} by the project completion.',
                'answers' => ['a1' => 'will have been upgraded'],
                'options' => [
                    'a1' => ['will have been upgraded', 'will be upgraded', 'has been upgraded', 'was upgraded'],
                ],
                'verb_hints' => ['a1' => 'upgrade'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The facilities will have been modernized.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],

            // ===== C1 Level: 12 questions =====
            [
                'level' => 'C1',
                'question' => 'The archaeological site {a1} by the end of the expedition.',
                'answers' => ['a1' => 'will have been excavated'],
                'options' => [
                    'a1' => ['will have been excavated', 'will be excavated', 'has been excavated', 'was excavated'],
                ],
                'verb_hints' => ['a1' => 'excavate'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Підкреслює завершеність складної дії до певного моменту в майбутньому.',
                    'Приклад: The ruins will have been unearthed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The genome {a1} before the research concludes.',
                'answers' => ['a1' => 'will have been sequenced'],
                'options' => [
                    'a1' => ['will have been sequenced', 'will be sequenced', 'has been sequenced', 'was sequenced'],
                ],
                'verb_hints' => ['a1' => 'sequence'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The DNA will have been analyzed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The quantum computer {a1} by the demonstration.',
                'answers' => ['a1' => 'will have been calibrated'],
                'options' => [
                    'a1' => ['will have been calibrated', 'will be calibrated', 'has been calibrated', 'was calibrated'],
                ],
                'verb_hints' => ['a1' => 'calibrate'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The instruments will have been adjusted.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The diplomatic agreement {a1} by the end of the talks.',
                'answers' => ['a1' => 'will have been ratified'],
                'options' => [
                    'a1' => ['will have been ratified', 'will be ratified', 'has been ratified', 'was ratified'],
                ],
                'verb_hints' => ['a1' => 'ratify'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The treaty will have been signed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The neural network {a1} by the product launch.',
                'answers' => ['a1' => 'will have been optimized'],
                'options' => [
                    'a1' => ['will have been optimized', 'will be optimized', 'has been optimized', 'was optimized'],
                ],
                'verb_hints' => ['a1' => 'optimize'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The algorithm will have been refined.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The constitutional amendment {a1} by the election.',
                'answers' => ['a1' => 'will have been enacted'],
                'options' => [
                    'a1' => ['will have been enacted', 'will be enacted', 'has been enacted', 'was enacted'],
                ],
                'verb_hints' => ['a1' => 'enact'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The legislation will have been passed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The satellite {a1} by the mission deadline.',
                'answers' => ['a1' => 'will have been launched'],
                'options' => [
                    'a1' => ['will have been launched', 'will be launched', 'has been launched', 'was launched'],
                ],
                'verb_hints' => ['a1' => 'launch'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The spacecraft will have been deployed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The pharmaceutical compound {a1} before clinical trials.',
                'answers' => ['a1' => 'will have been synthesized'],
                'options' => [
                    'a1' => ['will have been synthesized', 'will be synthesized', 'has been synthesized', 'was synthesized'],
                ],
                'verb_hints' => ['a1' => 'synthesize'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The medicine will have been formulated.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The nuclear facility {a1} by the regulatory deadline.',
                'answers' => ['a1' => 'will have been decommissioned'],
                'options' => [
                    'a1' => ['will have been decommissioned', 'will be decommissioned', 'has been decommissioned', 'was decommissioned'],
                ],
                'verb_hints' => ['a1' => 'decommission'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The reactor will have been shut down.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The biodiversity report {a1} by the conference.',
                'answers' => ['a1' => 'will have been compiled'],
                'options' => [
                    'a1' => ['will have been compiled', 'will be compiled', 'has been compiled', 'was compiled'],
                ],
                'verb_hints' => ['a1' => 'compile'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The survey will have been assembled.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The encryption protocol {a1} by the system upgrade.',
                'answers' => ['a1' => 'will have been deployed'],
                'options' => [
                    'a1' => ['will have been deployed', 'will be deployed', 'has been deployed', 'was deployed'],
                ],
                'verb_hints' => ['a1' => 'deploy'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The security measures will have been implemented.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The economic forecasts {a1} by the quarterly review.',
                'answers' => ['a1' => 'will have been revised'],
                'options' => [
                    'a1' => ['will have been revised', 'will be revised', 'have been revised', 'were revised'],
                ],
                'verb_hints' => ['a1' => 'revise'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The projections will have been updated.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],

            // ===== C2 Level: 12 questions =====
            [
                'level' => 'C2',
                'question' => 'The psycholinguistic study {a1} by the academic year.',
                'answers' => ['a1' => 'will have been concluded'],
                'options' => [
                    'a1' => ['will have been concluded', 'will be concluded', 'has been concluded', 'was concluded'],
                ],
                'verb_hints' => ['a1' => 'conclude'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'На найвищому рівні структура залишається незмінною, але контекст стає складнішим.',
                    'Приклад: The research will have been finalized.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The geopolitical implications {a1} before the summit.',
                'answers' => ['a1' => 'will have been scrutinized'],
                'options' => [
                    'a1' => ['will have been scrutinized', 'will be scrutinized', 'have been scrutinized', 'were scrutinized'],
                ],
                'verb_hints' => ['a1' => 'scrutinize'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The ramifications will have been examined.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The intergovernmental protocols {a1} by the assembly.',
                'answers' => ['a1' => 'will have been renegotiated'],
                'options' => [
                    'a1' => ['will have been renegotiated', 'will be renegotiated', 'have been renegotiated', 'were renegotiated'],
                ],
                'verb_hints' => ['a1' => 'renegotiate'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The agreements will have been revised.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The superconducting materials {a1} by the production deadline.',
                'answers' => ['a1' => 'will have been manufactured'],
                'options' => [
                    'a1' => ['will have been manufactured', 'will be manufactured', 'have been manufactured', 'were manufactured'],
                ],
                'verb_hints' => ['a1' => 'manufacture'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The components will have been produced.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The anthropological evidence {a1} by the publication date.',
                'answers' => ['a1' => 'will have been catalogued'],
                'options' => [
                    'a1' => ['will have been catalogued', 'will be catalogued', 'has been catalogued', 'was catalogued'],
                ],
                'verb_hints' => ['a1' => 'catalogue'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The artifacts will have been documented.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The neuroimaging data {a1} by the study conclusion.',
                'answers' => ['a1' => 'will have been interpreted'],
                'options' => [
                    'a1' => ['will have been interpreted', 'will be interpreted', 'has been interpreted', 'was interpreted'],
                ],
                'verb_hints' => ['a1' => 'interpret'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The scans will have been analyzed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The epistemological framework {a1} by the thesis defense.',
                'answers' => ['a1' => 'will have been established'],
                'options' => [
                    'a1' => ['will have been established', 'will be established', 'has been established', 'was established'],
                ],
                'verb_hints' => ['a1' => 'establish'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The foundations will have been laid.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The nanotechnology applications {a1} before regulatory approval.',
                'answers' => ['a1' => 'will have been patented'],
                'options' => [
                    'a1' => ['will have been patented', 'will be patented', 'have been patented', 'were patented'],
                ],
                'verb_hints' => ['a1' => 'patent'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The inventions will have been registered.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The macroeconomic models {a1} by the fiscal review.',
                'answers' => ['a1' => 'will have been recalibrated'],
                'options' => [
                    'a1' => ['will have been recalibrated', 'will be recalibrated', 'have been recalibrated', 'were recalibrated'],
                ],
                'verb_hints' => ['a1' => 'recalibrate'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The projections will have been adjusted.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The astrophysical observations {a1} by the journal deadline.',
                'answers' => ['a1' => 'will have been corroborated'],
                'options' => [
                    'a1' => ['will have been corroborated', 'will be corroborated', 'have been corroborated', 'were corroborated'],
                ],
                'verb_hints' => ['a1' => 'corroborate'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The findings will have been confirmed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The sociocultural paradigms {a1} by the symposium.',
                'answers' => ['a1' => 'will have been reconceptualized'],
                'options' => [
                    'a1' => ['will have been reconceptualized', 'will be reconceptualized', 'have been reconceptualized', 'were reconceptualized'],
                ],
                'verb_hints' => ['a1' => 'reconceptualize'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The theories will have been reformulated.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The hermeneutical methodology {a1} before the dissertation.',
                'answers' => ['a1' => 'will have been articulated'],
                'options' => [
                    'a1' => ['will have been articulated', 'will be articulated', 'has been articulated', 'was articulated'],
                ],
                'verb_hints' => ['a1' => 'articulate'],
                'hints' => [
                    '**Future Perfect Passive**: will + have + been + V3.',
                    'Приклад: The approach will have been defined.',
                ],
                'tag_ids' => [$affirmativeTagId, $futurePerfectTagId, $passiveTagId],
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
        return "✅ Правильно! «{$correct}» — це коректна форма Future Perfect Passive. Формула: will + have + been + past participle (V3).";
    }

    private function wrongExplanation(string $wrong, string $correct): string
    {
        // Future Simple Passive instead of Future Perfect Passive
        if (preg_match('/^will be \w+ed$/i', $wrong) || preg_match('/^will be \w+en$/i', $wrong)) {
            return "❌ «{$wrong}» — це Future Simple Passive (will be + V3). Для Future Perfect Passive потрібно: will have been + V3.";
        }

        // Present Perfect Passive
        if (preg_match('/^(has|have) been/i', $wrong)) {
            return "❌ «{$wrong}» — це Present Perfect Passive. Для Future Perfect Passive потрібно: will have been + V3.";
        }

        // Past Simple Passive
        if (preg_match('/^(was|were) \w+ed$/i', $wrong) || preg_match('/^(was|were) \w+en$/i', $wrong)) {
            return "❌ «{$wrong}» — це Past Simple Passive. Для Future Perfect Passive потрібно: will have been + V3.";
        }

        // Active Voice
        if (!str_contains(strtolower($wrong), 'been') && !str_contains(strtolower($wrong), 'be')) {
            return "❌ «{$wrong}» — це Active Voice (активний стан). Тут потрібен Passive Voice: will have been + V3.";
        }

        return "❌ «{$wrong}» — неправильна форма. Правильна відповідь: «{$correct}» (Future Perfect Passive: will + have + been + V3).";
    }
}
