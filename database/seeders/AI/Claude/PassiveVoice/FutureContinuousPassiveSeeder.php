<?php

namespace Database\Seeders\AI\Claude\PassiveVoice;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class FutureContinuousPassiveSeeder extends QuestionSeeder
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
        $sourceId = Source::firstOrCreate(['name' => 'AI Generated: Future Continuous Passive'])->id;
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
            ['name' => 'Future Continuous Passive'],
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

        $futureContinuousTagId = Tag::firstOrCreate(['name' => 'Future Continuous'], ['category' => 'Tense'])->id;
        $passiveTagId = Tag::firstOrCreate(['name' => 'Passive Voice'], ['category' => 'Voice'])->id;

        return [
            // ===== A1 Level: 12 questions =====
            [
                'level' => 'A1',
                'question' => 'The dinner {a1} at 7 PM tomorrow.',
                'answers' => ['a1' => 'will be being served'],
                'options' => [
                    'a1' => ['will be being served', 'will be served', 'is being served', 'was being served'],
                ],
                'verb_hints' => ['a1' => 'serve'],
                'hints' => [
                    '**Future Continuous Passive** утворюється: will + be + being + past participle (V3).',
                    'Використовується для дій, що триватимуть у певний момент у майбутньому.',
                    'Приклад: The food will be being prepared at noon.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The room {a1} at this time tomorrow.',
                'answers' => ['a1' => 'will be being cleaned'],
                'options' => [
                    'a1' => ['will be being cleaned', 'will be cleaned', 'is being cleaned', 'was being cleaned'],
                ],
                'verb_hints' => ['a1' => 'clean'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The house will be being renovated.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The car {a1} at 3 PM.',
                'answers' => ['a1' => 'will be being repaired'],
                'options' => [
                    'a1' => ['will be being repaired', 'will be repaired', 'is being repaired', 'was being repaired'],
                ],
                'verb_hints' => ['a1' => 'repair'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The bike will be being fixed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The cake {a1} when you arrive.',
                'answers' => ['a1' => 'will be being baked'],
                'options' => [
                    'a1' => ['will be being baked', 'will be baked', 'is being baked', 'was being baked'],
                ],
                'verb_hints' => ['a1' => 'bake'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The cookies will be being made.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The package {a1} at noon tomorrow.',
                'answers' => ['a1' => 'will be being delivered'],
                'options' => [
                    'a1' => ['will be being delivered', 'will be delivered', 'is being delivered', 'was being delivered'],
                ],
                'verb_hints' => ['a1' => 'deliver'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The mail will be being sorted.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The letter {a1} at 10 AM.',
                'answers' => ['a1' => 'will be being written'],
                'options' => [
                    'a1' => ['will be being written', 'will be written', 'is being written', 'was being written'],
                ],
                'verb_hints' => ['a1' => 'write'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The report will be being typed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The clothes {a1} at this time tomorrow.',
                'answers' => ['a1' => 'will be being washed'],
                'options' => [
                    'a1' => ['will be being washed', 'will be washed', 'is being washed', 'were being washed'],
                ],
                'verb_hints' => ['a1' => 'wash'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The dishes will be being done.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The flowers {a1} at 4 PM.',
                'answers' => ['a1' => 'will be being watered'],
                'options' => [
                    'a1' => ['will be being watered', 'will be watered', 'are being watered', 'were being watered'],
                ],
                'verb_hints' => ['a1' => 'water'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The plants will be being tended.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The dog {a1} at 5 PM tomorrow.',
                'answers' => ['a1' => 'will be being walked'],
                'options' => [
                    'a1' => ['will be being walked', 'will be walked', 'is being walked', 'was being walked'],
                ],
                'verb_hints' => ['a1' => 'walk'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The cat will be being fed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The baby {a1} at 8 PM.',
                'answers' => ['a1' => 'will be being fed'],
                'options' => [
                    'a1' => ['will be being fed', 'will be fed', 'is being fed', 'was being fed'],
                ],
                'verb_hints' => ['a1' => 'feed'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The children will be being watched.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The song {a1} at 9 PM.',
                'answers' => ['a1' => 'will be being sung'],
                'options' => [
                    'a1' => ['will be being sung', 'will be sung', 'is being sung', 'was being sung'],
                ],
                'verb_hints' => ['a1' => 'sing'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The music will be being played.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The film {a1} at 8 PM tomorrow.',
                'answers' => ['a1' => 'will be being shown'],
                'options' => [
                    'a1' => ['will be being shown', 'will be shown', 'is being shown', 'was being shown'],
                ],
                'verb_hints' => ['a1' => 'show'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The video will be being recorded.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],

            // ===== A2 Level: 12 questions =====
            [
                'level' => 'A2',
                'question' => 'The report {a1} at this time on Monday.',
                'answers' => ['a1' => 'will be being prepared'],
                'options' => [
                    'a1' => ['will be being prepared', 'will be prepared', 'is being prepared', 'was being prepared'],
                ],
                'verb_hints' => ['a1' => 'prepare'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Позначає дію, яка триватиме в певний момент у майбутньому.',
                    'Приклад: The documents will be being reviewed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The building {a1} when you visit next year.',
                'answers' => ['a1' => 'will be being constructed'],
                'options' => [
                    'a1' => ['will be being constructed', 'will be constructed', 'is being constructed', 'was being constructed'],
                ],
                'verb_hints' => ['a1' => 'construct'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The bridge will be being built.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The exam papers {a1} at 2 PM.',
                'answers' => ['a1' => 'will be being checked'],
                'options' => [
                    'a1' => ['will be being checked', 'will be checked', 'are being checked', 'were being checked'],
                ],
                'verb_hints' => ['a1' => 'check'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The tests will be being graded.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The new system {a1} at 10 AM tomorrow.',
                'answers' => ['a1' => 'will be being tested'],
                'options' => [
                    'a1' => ['will be being tested', 'will be tested', 'is being tested', 'was being tested'],
                ],
                'verb_hints' => ['a1' => 'test'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The software will be being updated.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The guests {a1} at this time tomorrow.',
                'answers' => ['a1' => 'will be being welcomed'],
                'options' => [
                    'a1' => ['will be being welcomed', 'will be welcomed', 'are being welcomed', 'were being welcomed'],
                ],
                'verb_hints' => ['a1' => 'welcome'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The visitors will be being greeted.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The project {a1} at 3 PM on Friday.',
                'answers' => ['a1' => 'will be being discussed'],
                'options' => [
                    'a1' => ['will be being discussed', 'will be discussed', 'is being discussed', 'was being discussed'],
                ],
                'verb_hints' => ['a1' => 'discuss'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The plan will be being reviewed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The trees {a1} at this time next week.',
                'answers' => ['a1' => 'will be being planted'],
                'options' => [
                    'a1' => ['will be being planted', 'will be planted', 'are being planted', 'were being planted'],
                ],
                'verb_hints' => ['a1' => 'plant'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The seeds will be being sown.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The road {a1} at noon tomorrow.',
                'answers' => ['a1' => 'will be being repaired'],
                'options' => [
                    'a1' => ['will be being repaired', 'will be repaired', 'is being repaired', 'was being repaired'],
                ],
                'verb_hints' => ['a1' => 'repair'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The street will be being paved.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The walls {a1} at 11 AM.',
                'answers' => ['a1' => 'will be being painted'],
                'options' => [
                    'a1' => ['will be being painted', 'will be painted', 'are being painted', 'were being painted'],
                ],
                'verb_hints' => ['a1' => 'paint'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The ceiling will be being whitewashed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The food {a1} at 6 PM.',
                'answers' => ['a1' => 'will be being cooked'],
                'options' => [
                    'a1' => ['will be being cooked', 'will be cooked', 'is being cooked', 'was being cooked'],
                ],
                'verb_hints' => ['a1' => 'cook'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The dinner will be being prepared.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The candidates {a1} at 9 AM tomorrow.',
                'answers' => ['a1' => 'will be being interviewed'],
                'options' => [
                    'a1' => ['will be being interviewed', 'will be interviewed', 'are being interviewed', 'were being interviewed'],
                ],
                'verb_hints' => ['a1' => 'interview'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The applicants will be being assessed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The furniture {a1} at this time on Saturday.',
                'answers' => ['a1' => 'will be being moved'],
                'options' => [
                    'a1' => ['will be being moved', 'will be moved', 'is being moved', 'was being moved'],
                ],
                'verb_hints' => ['a1' => 'move'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The boxes will be being transported.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],

            // ===== B1 Level: 12 questions =====
            [
                'level' => 'B1',
                'question' => 'The equipment {a1} while you are having lunch.',
                'answers' => ['a1' => 'will be being installed'],
                'options' => [
                    'a1' => ['will be being installed', 'will be installed', 'is being installed', 'was being installed'],
                ],
                'verb_hints' => ['a1' => 'install'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Використовується для опису дії, що триватиме в певний момент у майбутньому.',
                    'Приклад: The machinery will be being set up.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The website {a1} at this time tomorrow.',
                'answers' => ['a1' => 'will be being updated'],
                'options' => [
                    'a1' => ['will be being updated', 'will be updated', 'is being updated', 'was being updated'],
                ],
                'verb_hints' => ['a1' => 'update'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The application will be being modified.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The evidence {a1} when the trial begins.',
                'answers' => ['a1' => 'will be being examined'],
                'options' => [
                    'a1' => ['will be being examined', 'will be examined', 'is being examined', 'was being examined'],
                ],
                'verb_hints' => ['a1' => 'examine'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The witnesses will be being questioned.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The performance {a1} when you arrive at the theatre.',
                'answers' => ['a1' => 'will be being recorded'],
                'options' => [
                    'a1' => ['will be being recorded', 'will be recorded', 'is being recorded', 'was being recorded'],
                ],
                'verb_hints' => ['a1' => 'record'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The concert will be being filmed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The proposal {a1} at 4 PM tomorrow.',
                'answers' => ['a1' => 'will be being reviewed'],
                'options' => [
                    'a1' => ['will be being reviewed', 'will be reviewed', 'is being reviewed', 'was being reviewed'],
                ],
                'verb_hints' => ['a1' => 'review'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The contract will be being analyzed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The students {a1} while we discuss the schedule.',
                'answers' => ['a1' => 'will be being supervised'],
                'options' => [
                    'a1' => ['will be being supervised', 'will be supervised', 'are being supervised', 'were being supervised'],
                ],
                'verb_hints' => ['a1' => 'supervise'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The children will be being watched.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The package {a1} at this time on Monday.',
                'answers' => ['a1' => 'will be being tracked'],
                'options' => [
                    'a1' => ['will be being tracked', 'will be tracked', 'is being tracked', 'was being tracked'],
                ],
                'verb_hints' => ['a1' => 'track'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The shipment will be being monitored.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The artwork {a1} when the gallery opens.',
                'answers' => ['a1' => 'will be being displayed'],
                'options' => [
                    'a1' => ['will be being displayed', 'will be displayed', 'is being displayed', 'was being displayed'],
                ],
                'verb_hints' => ['a1' => 'display'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The paintings will be being exhibited.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The documents {a1} when you return from the meeting.',
                'answers' => ['a1' => 'will be being printed'],
                'options' => [
                    'a1' => ['will be being printed', 'will be printed', 'are being printed', 'were being printed'],
                ],
                'verb_hints' => ['a1' => 'print'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The files will be being copied.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The passengers {a1} at 6 AM.',
                'answers' => ['a1' => 'will be being boarded'],
                'options' => [
                    'a1' => ['will be being boarded', 'will be boarded', 'are being boarded', 'were being boarded'],
                ],
                'verb_hints' => ['a1' => 'board'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The travelers will be being checked in.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The data {a1} when the system reboots.',
                'answers' => ['a1' => 'will be being transferred'],
                'options' => [
                    'a1' => ['will be being transferred', 'will be transferred', 'is being transferred', 'was being transferred'],
                ],
                'verb_hints' => ['a1' => 'transfer'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The information will be being uploaded.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The materials {a1} when you arrive at the office.',
                'answers' => ['a1' => 'will be being organized'],
                'options' => [
                    'a1' => ['will be being organized', 'will be organized', 'are being organized', 'were being organized'],
                ],
                'verb_hints' => ['a1' => 'organize'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The supplies will be being sorted.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],

            // ===== B2 Level: 12 questions =====
            [
                'level' => 'B2',
                'question' => 'The scientific experiment {a1} at this time next week.',
                'answers' => ['a1' => 'will be being conducted'],
                'options' => [
                    'a1' => ['will be being conducted', 'will be conducted', 'is being conducted', 'was being conducted'],
                ],
                'verb_hints' => ['a1' => 'conduct'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Використовується для підкреслення тривалості дії в певний момент у майбутньому.',
                    'Приклад: The research will be being carried out.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The environmental impact {a1} when the project launches.',
                'answers' => ['a1' => 'will be being assessed'],
                'options' => [
                    'a1' => ['will be being assessed', 'will be assessed', 'is being assessed', 'was being assessed'],
                ],
                'verb_hints' => ['a1' => 'assess'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The consequences will be being evaluated.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The negotiations {a1} while we wait for the results.',
                'answers' => ['a1' => 'will be being held'],
                'options' => [
                    'a1' => ['will be being held', 'will be held', 'are being held', 'were being held'],
                ],
                'verb_hints' => ['a1' => 'hold'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The talks will be being conducted.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The suspect {a1} when the lawyer arrives.',
                'answers' => ['a1' => 'will be being questioned'],
                'options' => [
                    'a1' => ['will be being questioned', 'will be questioned', 'is being questioned', 'was being questioned'],
                ],
                'verb_hints' => ['a1' => 'question'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The witness will be being interrogated.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The budget {a1} at this time on Friday.',
                'answers' => ['a1' => 'will be being finalized'],
                'options' => [
                    'a1' => ['will be being finalized', 'will be finalized', 'is being finalized', 'was being finalized'],
                ],
                'verb_hints' => ['a1' => 'finalize'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The accounts will be being closed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The historical artifacts {a1} when the exhibition opens.',
                'answers' => ['a1' => 'will be being preserved'],
                'options' => [
                    'a1' => ['will be being preserved', 'will be preserved', 'are being preserved', 'were being preserved'],
                ],
                'verb_hints' => ['a1' => 'preserve'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The relics will be being restored.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The confidential data {a1} at this time tomorrow.',
                'answers' => ['a1' => 'will be being encrypted'],
                'options' => [
                    'a1' => ['will be being encrypted', 'will be encrypted', 'is being encrypted', 'was being encrypted'],
                ],
                'verb_hints' => ['a1' => 'encrypt'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The files will be being secured.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The new employees {a1} while you are on vacation.',
                'answers' => ['a1' => 'will be being trained'],
                'options' => [
                    'a1' => ['will be being trained', 'will be trained', 'are being trained', 'were being trained'],
                ],
                'verb_hints' => ['a1' => 'train'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The staff will be being instructed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The medicine {a1} when the patient wakes up.',
                'answers' => ['a1' => 'will be being administered'],
                'options' => [
                    'a1' => ['will be being administered', 'will be administered', 'is being administered', 'was being administered'],
                ],
                'verb_hints' => ['a1' => 'administer'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The treatment will be being given.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The policy changes {a1} when the board meets.',
                'answers' => ['a1' => 'will be being implemented'],
                'options' => [
                    'a1' => ['will be being implemented', 'will be implemented', 'are being implemented', 'were being implemented'],
                ],
                'verb_hints' => ['a1' => 'implement'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The regulations will be being enforced.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The witness statements {a1} at this time tomorrow.',
                'answers' => ['a1' => 'will be being verified'],
                'options' => [
                    'a1' => ['will be being verified', 'will be verified', 'are being verified', 'were being verified'],
                ],
                'verb_hints' => ['a1' => 'verify'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The testimonies will be being confirmed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The infrastructure {a1} when the funding arrives.',
                'answers' => ['a1' => 'will be being upgraded'],
                'options' => [
                    'a1' => ['will be being upgraded', 'will be upgraded', 'is being upgraded', 'was being upgraded'],
                ],
                'verb_hints' => ['a1' => 'upgrade'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The facilities will be being modernized.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],

            // ===== C1 Level: 12 questions =====
            [
                'level' => 'C1',
                'question' => 'The archaeological site {a1} at this time next month.',
                'answers' => ['a1' => 'will be being excavated'],
                'options' => [
                    'a1' => ['will be being excavated', 'will be excavated', 'is being excavated', 'was being excavated'],
                ],
                'verb_hints' => ['a1' => 'excavate'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Підкреслює тривалість складної дії в певний момент у майбутньому.',
                    'Приклад: The ruins will be being unearthed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The genome {a1} when the new equipment arrives.',
                'answers' => ['a1' => 'will be being sequenced'],
                'options' => [
                    'a1' => ['will be being sequenced', 'will be sequenced', 'is being sequenced', 'was being sequenced'],
                ],
                'verb_hints' => ['a1' => 'sequence'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The DNA will be being analyzed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The quantum computer {a1} at this time next year.',
                'answers' => ['a1' => 'will be being calibrated'],
                'options' => [
                    'a1' => ['will be being calibrated', 'will be calibrated', 'is being calibrated', 'was being calibrated'],
                ],
                'verb_hints' => ['a1' => 'calibrate'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The instruments will be being adjusted.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The diplomatic agreement {a1} when the summit ends.',
                'answers' => ['a1' => 'will be being ratified'],
                'options' => [
                    'a1' => ['will be being ratified', 'will be ratified', 'is being ratified', 'was being ratified'],
                ],
                'verb_hints' => ['a1' => 'ratify'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The treaty will be being signed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The neural network {a1} while you attend the conference.',
                'answers' => ['a1' => 'will be being optimized'],
                'options' => [
                    'a1' => ['will be being optimized', 'will be optimized', 'is being optimized', 'was being optimized'],
                ],
                'verb_hints' => ['a1' => 'optimize'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The algorithm will be being refined.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The constitutional amendment {a1} at this time tomorrow.',
                'answers' => ['a1' => 'will be being debated'],
                'options' => [
                    'a1' => ['will be being debated', 'will be debated', 'is being debated', 'was being debated'],
                ],
                'verb_hints' => ['a1' => 'debate'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The legislation will be being discussed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The satellite imagery {a1} when the mission begins.',
                'answers' => ['a1' => 'will be being transmitted'],
                'options' => [
                    'a1' => ['will be being transmitted', 'will be transmitted', 'is being transmitted', 'was being transmitted'],
                ],
                'verb_hints' => ['a1' => 'transmit'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The data will be being relayed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The pharmaceutical compound {a1} at this time next week.',
                'answers' => ['a1' => 'will be being synthesized'],
                'options' => [
                    'a1' => ['will be being synthesized', 'will be synthesized', 'is being synthesized', 'was being synthesized'],
                ],
                'verb_hints' => ['a1' => 'synthesize'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The medicine will be being formulated.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The nuclear reactor {a1} when the inspection team arrives.',
                'answers' => ['a1' => 'will be being decommissioned'],
                'options' => [
                    'a1' => ['will be being decommissioned', 'will be decommissioned', 'is being decommissioned', 'was being decommissioned'],
                ],
                'verb_hints' => ['a1' => 'decommission'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The facility will be being shut down.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The biodiversity survey {a1} while you complete the analysis.',
                'answers' => ['a1' => 'will be being compiled'],
                'options' => [
                    'a1' => ['will be being compiled', 'will be compiled', 'is being compiled', 'was being compiled'],
                ],
                'verb_hints' => ['a1' => 'compile'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The report will be being assembled.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The encryption protocol {a1} at this time next month.',
                'answers' => ['a1' => 'will be being implemented'],
                'options' => [
                    'a1' => ['will be being implemented', 'will be implemented', 'is being implemented', 'was being implemented'],
                ],
                'verb_hints' => ['a1' => 'implement'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The security measures will be being deployed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The economic indicators {a1} when the markets open.',
                'answers' => ['a1' => 'will be being monitored'],
                'options' => [
                    'a1' => ['will be being monitored', 'will be monitored', 'are being monitored', 'were being monitored'],
                ],
                'verb_hints' => ['a1' => 'monitor'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The trends will be being tracked.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],

            // ===== C2 Level: 12 questions =====
            [
                'level' => 'C2',
                'question' => 'The psycholinguistic phenomenon {a1} at this time next semester.',
                'answers' => ['a1' => 'will be being investigated'],
                'options' => [
                    'a1' => ['will be being investigated', 'will be investigated', 'is being investigated', 'was being investigated'],
                ],
                'verb_hints' => ['a1' => 'investigate'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'На найвищому рівні структура залишається незмінною, але контекст стає складнішим.',
                    'Приклад: The theory will be being tested.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The geopolitical ramifications {a1} while you present your findings.',
                'answers' => ['a1' => 'will be being analyzed'],
                'options' => [
                    'a1' => ['will be being analyzed', 'will be analyzed', 'are being analyzed', 'were being analyzed'],
                ],
                'verb_hints' => ['a1' => 'analyze'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The implications will be being considered.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The intergovernmental protocols {a1} when the assembly convenes.',
                'answers' => ['a1' => 'will be being renegotiated'],
                'options' => [
                    'a1' => ['will be being renegotiated', 'will be renegotiated', 'are being renegotiated', 'were being renegotiated'],
                ],
                'verb_hints' => ['a1' => 'renegotiate'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The agreements will be being revised.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The superconducting materials {a1} at this time next quarter.',
                'answers' => ['a1' => 'will be being manufactured'],
                'options' => [
                    'a1' => ['will be being manufactured', 'will be manufactured', 'are being manufactured', 'were being manufactured'],
                ],
                'verb_hints' => ['a1' => 'manufacture'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The components will be being produced.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The anthropological evidence {a1} while you review the literature.',
                'answers' => ['a1' => 'will be being catalogued'],
                'options' => [
                    'a1' => ['will be being catalogued', 'will be catalogued', 'is being catalogued', 'was being catalogued'],
                ],
                'verb_hints' => ['a1' => 'catalogue'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The artifacts will be being documented.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The neuroimaging data {a1} when the study concludes.',
                'answers' => ['a1' => 'will be being processed'],
                'options' => [
                    'a1' => ['will be being processed', 'will be processed', 'is being processed', 'was being processed'],
                ],
                'verb_hints' => ['a1' => 'process'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The scans will be being interpreted.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The epistemological assumptions {a1} at this time next year.',
                'answers' => ['a1' => 'will be being reconsidered'],
                'options' => [
                    'a1' => ['will be being reconsidered', 'will be reconsidered', 'are being reconsidered', 'were being reconsidered'],
                ],
                'verb_hints' => ['a1' => 'reconsider'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The premises will be being re-examined.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The nanotechnology applications {a1} while we await regulatory approval.',
                'answers' => ['a1' => 'will be being explored'],
                'options' => [
                    'a1' => ['will be being explored', 'will be explored', 'are being explored', 'were being explored'],
                ],
                'verb_hints' => ['a1' => 'explore'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The possibilities will be being examined.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The macroeconomic projections {a1} when the fiscal year begins.',
                'answers' => ['a1' => 'will be being recalculated'],
                'options' => [
                    'a1' => ['will be being recalculated', 'will be recalculated', 'are being recalculated', 'were being recalculated'],
                ],
                'verb_hints' => ['a1' => 'recalculate'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The forecasts will be being adjusted.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The astrophysical observations {a1} at this time next week.',
                'answers' => ['a1' => 'will be being corroborated'],
                'options' => [
                    'a1' => ['will be being corroborated', 'will be corroborated', 'are being corroborated', 'were being corroborated'],
                ],
                'verb_hints' => ['a1' => 'corroborate'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The findings will be being confirmed.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The sociocultural dynamics {a1} while the research team gathers data.',
                'answers' => ['a1' => 'will be being scrutinized'],
                'options' => [
                    'a1' => ['will be being scrutinized', 'will be scrutinized', 'are being scrutinized', 'were being scrutinized'],
                ],
                'verb_hints' => ['a1' => 'scrutinize'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The patterns will be being examined.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The hermeneutical interpretations {a1} when the symposium begins.',
                'answers' => ['a1' => 'will be being challenged'],
                'options' => [
                    'a1' => ['will be being challenged', 'will be challenged', 'are being challenged', 'were being challenged'],
                ],
                'verb_hints' => ['a1' => 'challenge'],
                'hints' => [
                    '**Future Continuous Passive**: will + be + being + V3.',
                    'Приклад: The readings will be being contested.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureContinuousTagId, $passiveTagId],
            ],
        ];
    }
}