<?php

namespace Database\Seeders\AI\Claude\PassiveVoice;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class PresentContinuousPassiveSeeder extends QuestionSeeder
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
        $sourceId = Source::firstOrCreate(['name' => 'AI Generated: Present Continuous Passive'])->id;
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
            ['name' => 'Present Continuous Passive'],
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

        $presentContinuousTagId = Tag::firstOrCreate(['name' => 'Present Continuous'], ['category' => 'Tense'])->id;
        $passiveTagId = Tag::firstOrCreate(['name' => 'Passive Voice'], ['category' => 'Voice'])->id;

        return [
            // ===== A1 Level: 12 questions =====
            [
                'level' => 'A1',
                'question' => 'The house {a1} now.',
                'answers' => ['a1' => 'is being painted'],
                'options' => [
                    'a1' => ['is being painted', 'is painted', 'is painting', 'are being painted'],
                ],
                'verb_hints' => ['a1' => 'paint'],
                'hints' => [
                    '**Present Continuous Passive** утворюється: is/am/are + being + past participle (V3).',
                    'Використовується для дій, що відбуваються зараз.',
                    'Приклад: The car is being washed at the moment.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The letters {a1} right now.',
                'answers' => ['a1' => 'are being written'],
                'options' => [
                    'a1' => ['are being written', 'is being written', 'are written', 'are writing'],
                ],
                'verb_hints' => ['a1' => 'write'],
                'hints' => [
                    '**Present Continuous Passive** для множини: are + being + V3.',
                    'Приклад: The reports are being prepared now.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'Dinner {a1} at the moment.',
                'answers' => ['a1' => 'is being cooked'],
                'options' => [
                    'a1' => ['is being cooked', 'is cooked', 'is cooking', 'are being cooked'],
                ],
                'verb_hints' => ['a1' => 'cook'],
                'hints' => [
                    '**Present Continuous Passive**: is + being + V3.',
                    '"At the moment" вказує на дію зараз.',
                    'Приклад: Breakfast is being made now.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The car {a1} in the garage.',
                'answers' => ['a1' => 'is being repaired'],
                'options' => [
                    'a1' => ['is being repaired', 'is repaired', 'is repairing', 'are being repaired'],
                ],
                'verb_hints' => ['a1' => 'repair'],
                'hints' => [
                    '**Present Continuous Passive** для тривалої дії.',
                    'Приклад: The bicycle is being fixed right now.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The children {a1} now.',
                'answers' => ['a1' => 'are being fed'],
                'options' => [
                    'a1' => ['are being fed', 'is being fed', 'are fed', 'are feeding'],
                ],
                'verb_hints' => ['a1' => 'feed'],
                'hints' => [
                    '**Present Continuous Passive** для множини: are + being + V3.',
                    'Приклад: The babies are being looked after.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The room {a1} at this moment.',
                'answers' => ['a1' => 'is being cleaned'],
                'options' => [
                    'a1' => ['is being cleaned', 'is cleaned', 'is cleaning', 'are being cleaned'],
                ],
                'verb_hints' => ['a1' => 'clean'],
                'hints' => [
                    '**Present Continuous Passive**: is + being + V3.',
                    'Приклад: The office is being tidied up.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The packages {a1} today.',
                'answers' => ['a1' => "aren't being delivered"],
                'options' => [
                    'a1' => ["aren't being delivered", "isn't being delivered", "aren't delivered", "don't deliver"],
                ],
                'verb_hints' => ['a1' => 'deliver'],
                'hints' => [
                    '**Present Continuous Passive Negative**: are not (aren\'t) + being + V3.',
                    'Приклад: The orders aren\'t being processed today.',
                ],
                'tag_ids' => [$negativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The music {a1} now.',
                'answers' => ['a1' => "isn't being played"],
                'options' => [
                    'a1' => ["isn't being played", "aren't being played", "isn't played", "doesn't play"],
                ],
                'verb_hints' => ['a1' => 'play'],
                'hints' => [
                    '**Present Continuous Passive Negative**: is not (isn\'t) + being + V3.',
                    'Приклад: The song isn\'t being recorded at the moment.',
                ],
                'tag_ids' => [$negativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => '{a1} the food being served now?',
                'answers' => ['a1' => 'Is'],
                'options' => [
                    'a1' => ['Is', 'Are', 'Does', 'Do'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Present Continuous Passive Question**: Is/Are + subject + being + V3?',
                    'Приклад: Is lunch being prepared?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => '{a1} the windows being washed?',
                'answers' => ['a1' => 'Are'],
                'options' => [
                    'a1' => ['Are', 'Is', 'Do', 'Does'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Present Continuous Passive Question** для множини: Are + subject + being + V3?',
                    'Приклад: Are the floors being mopped?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The dog {a1} in the park.',
                'answers' => ['a1' => 'is being walked'],
                'options' => [
                    'a1' => ['is being walked', 'is walked', 'is walking', 'are being walked'],
                ],
                'verb_hints' => ['a1' => 'walk'],
                'hints' => [
                    '**Present Continuous Passive**: is + being + V3.',
                    'Приклад: The pets are being cared for.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'I {a1} for the job interview.',
                'answers' => ['a1' => 'am being prepared'],
                'options' => [
                    'a1' => ['am being prepared', 'am prepared', 'is being prepared', 'am preparing'],
                ],
                'verb_hints' => ['a1' => 'prepare'],
                'hints' => [
                    '**Present Continuous Passive** з "I": am + being + V3.',
                    'Приклад: I am being trained for the position.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],

            // ===== A2 Level: 12 questions =====
            [
                'level' => 'A2',
                'question' => 'A new bridge {a1} across the river.',
                'answers' => ['a1' => 'is being built'],
                'options' => [
                    'a1' => ['is being built', 'is built', 'is building', 'are being built'],
                ],
                'verb_hints' => ['a1' => 'build'],
                'hints' => [
                    '**Present Continuous Passive** для будівництва в процесі.',
                    'Приклад: A new road is being constructed.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The students {a1} by the teacher.',
                'answers' => ['a1' => 'are being tested'],
                'options' => [
                    'a1' => ['are being tested', 'is being tested', 'are tested', 'are testing'],
                ],
                'verb_hints' => ['a1' => 'test'],
                'hints' => [
                    '**Present Continuous Passive** з агентом: are + being + V3 + by.',
                    'Приклад: The pupils are being examined by the professor.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The pizza {a1} in the oven.',
                'answers' => ['a1' => 'is being baked'],
                'options' => [
                    'a1' => ['is being baked', 'is baked', 'is baking', 'are being baked'],
                ],
                'verb_hints' => ['a1' => 'bake'],
                'hints' => [
                    '**Present Continuous Passive**: is + being + V3.',
                    'Приклад: The bread is being prepared.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The guests {a1} at the hotel.',
                'answers' => ['a1' => 'are being welcomed'],
                'options' => [
                    'a1' => ['are being welcomed', 'is being welcomed', 'are welcomed', 'are welcoming'],
                ],
                'verb_hints' => ['a1' => 'welcome'],
                'hints' => [
                    '**Present Continuous Passive** для множини: are + being + V3.',
                    'Приклад: The visitors are being greeted.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The documents {a1} by the secretary.',
                'answers' => ['a1' => 'are being organized'],
                'options' => [
                    'a1' => ['are being organized', 'is being organized', 'are organized', 'are organizing'],
                ],
                'verb_hints' => ['a1' => 'organize'],
                'hints' => [
                    '**Present Continuous Passive** з агентом.',
                    'Приклад: The files are being sorted by the assistant.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The project {a1} anymore.',
                'answers' => ['a1' => "isn't being developed"],
                'options' => [
                    'a1' => ["isn't being developed", "aren't being developed", "isn't developed", "doesn't develop"],
                ],
                'verb_hints' => ['a1' => 'develop'],
                'hints' => [
                    '**Present Continuous Passive Negative**: isn\'t + being + V3.',
                    'Приклад: The plan isn\'t being implemented.',
                ],
                'tag_ids' => [$negativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The walls {a1} this week.',
                'answers' => ['a1' => "aren't being painted"],
                'options' => [
                    'a1' => ["aren't being painted", "isn't being painted", "aren't painted", "don't paint"],
                ],
                'verb_hints' => ['a1' => 'paint'],
                'hints' => [
                    '**Present Continuous Passive Negative** для множини: aren\'t + being + V3.',
                    'Приклад: The rooms aren\'t being renovated.',
                ],
                'tag_ids' => [$negativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => '{a1} the presentation being prepared?',
                'answers' => ['a1' => 'Is'],
                'options' => [
                    'a1' => ['Is', 'Are', 'Does', 'Do'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Present Continuous Passive Question**: Is + subject + being + V3?',
                    'Приклад: Is the report being written?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'What {a1} being discussed in the meeting?',
                'answers' => ['a1' => 'is'],
                'options' => [
                    'a1' => ['is', 'are', 'does', 'do'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question in Passive**: What + is + being + V3?',
                    'Приклад: What is being considered?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The cake {a1} for the party.',
                'answers' => ['a1' => 'is being decorated'],
                'options' => [
                    'a1' => ['is being decorated', 'is decorated', 'is decorating', 'are being decorated'],
                ],
                'verb_hints' => ['a1' => 'decorate'],
                'hints' => [
                    '**Present Continuous Passive**: is + being + V3.',
                    'Приклад: The venue is being prepared.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'Who {a1} being interviewed for the position?',
                'answers' => ['a1' => 'is'],
                'options' => [
                    'a1' => ['is', 'are', 'does', 'do'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Passive**: Who + is + being + V3?',
                    'Приклад: Who is being considered?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The flowers {a1} in the garden.',
                'answers' => ['a1' => 'are being planted'],
                'options' => [
                    'a1' => ['are being planted', 'is being planted', 'are planted', 'are planting'],
                ],
                'verb_hints' => ['a1' => 'plant'],
                'hints' => [
                    '**Present Continuous Passive** для множини: are + being + V3.',
                    'Приклад: The trees are being watered.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],

            // ===== B1 Level: 12 questions =====
            [
                'level' => 'B1',
                'question' => 'The new software {a1} by the IT department.',
                'answers' => ['a1' => 'is being installed'],
                'options' => [
                    'a1' => ['is being installed', 'is installed', 'is installing', 'are being installed'],
                ],
                'verb_hints' => ['a1' => 'install'],
                'hints' => [
                    '**Present Continuous Passive** для технічних процесів.',
                    'Приклад: The system is being updated.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'Safety measures {a1} at the construction site.',
                'answers' => ['a1' => 'are being implemented'],
                'options' => [
                    'a1' => ['are being implemented', 'is being implemented', 'are implemented', 'are implementing'],
                ],
                'verb_hints' => ['a1' => 'implement'],
                'hints' => [
                    '**Present Continuous Passive** для заходів у процесі.',
                    'Приклад: New regulations are being enforced.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The survey results {a1} by the research team.',
                'answers' => ['a1' => 'are being analyzed'],
                'options' => [
                    'a1' => ['are being analyzed', 'is being analyzed', 'are analyzed', 'are analyzing'],
                ],
                'verb_hints' => ['a1' => 'analyze'],
                'hints' => [
                    '**Present Continuous Passive** для досліджень.',
                    'Приклад: The data is being processed.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The patient {a1} by the specialists.',
                'answers' => ['a1' => 'is being examined'],
                'options' => [
                    'a1' => ['is being examined', 'is examined', 'is examining', 'are being examined'],
                ],
                'verb_hints' => ['a1' => 'examine'],
                'hints' => [
                    '**Present Continuous Passive** для медичних процедур.',
                    'Приклад: The samples are being tested.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The contract {a1} at this time.',
                'answers' => ['a1' => "isn't being negotiated"],
                'options' => [
                    'a1' => ["isn't being negotiated", "aren't being negotiated", "isn't negotiated", "doesn't negotiate"],
                ],
                'verb_hints' => ['a1' => 'negotiate'],
                'hints' => [
                    '**Present Continuous Passive Negative**: isn\'t + being + V3.',
                    'Приклад: The deal isn\'t being discussed.',
                ],
                'tag_ids' => [$negativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'New employees {a1} during the holiday season.',
                'answers' => ['a1' => "aren't being hired"],
                'options' => [
                    'a1' => ["aren't being hired", "isn't being hired", "aren't hired", "don't hire"],
                ],
                'verb_hints' => ['a1' => 'hire'],
                'hints' => [
                    '**Present Continuous Passive Negative** для множини.',
                    'Приклад: Applications aren\'t being accepted.',
                ],
                'tag_ids' => [$negativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => '{a1} the issue being addressed by management?',
                'answers' => ['a1' => 'Is'],
                'options' => [
                    'a1' => ['Is', 'Are', 'Does', 'Do'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Present Continuous Passive Question**: Is + subject + being + V3?',
                    'Приклад: Is the problem being solved?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'Why {a1} the materials being replaced?',
                'answers' => ['a1' => 'are'],
                'options' => [
                    'a1' => ['are', 'is', 'do', 'does'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Passive**: Why + are + subject + being + V3?',
                    'Приклад: Why is the equipment being changed?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The old building {a1} to make way for a park.',
                'answers' => ['a1' => 'is being demolished'],
                'options' => [
                    'a1' => ['is being demolished', 'is demolished', 'is demolishing', 'are being demolished'],
                ],
                'verb_hints' => ['a1' => 'demolish'],
                'hints' => [
                    '**Present Continuous Passive** для процесів знесення.',
                    'Приклад: The structure is being torn down.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'How {a1} the situation being handled?',
                'answers' => ['a1' => 'is'],
                'options' => [
                    'a1' => ['is', 'are', 'does', 'do'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Passive**: How + is + subject + being + V3?',
                    'Приклад: How is the crisis being managed?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The tickets {a1} online at the moment.',
                'answers' => ['a1' => 'are being sold'],
                'options' => [
                    'a1' => ['are being sold', 'is being sold', 'are sold', 'are selling'],
                ],
                'verb_hints' => ['a1' => 'sell'],
                'hints' => [
                    '**Present Continuous Passive** для онлайн-процесів.',
                    'Приклад: The products are being shipped.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The curriculum {a1} to meet new standards.',
                'answers' => ['a1' => 'is being revised'],
                'options' => [
                    'a1' => ['is being revised', 'is revised', 'is revising', 'are being revised'],
                ],
                'verb_hints' => ['a1' => 'revise'],
                'hints' => [
                    '**Present Continuous Passive** для освітніх змін.',
                    'Приклад: The program is being updated.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],

            // ===== B2 Level: 12 questions =====
            [
                'level' => 'B2',
                'question' => 'The environmental impact {a1} by independent experts.',
                'answers' => ['a1' => 'is being assessed'],
                'options' => [
                    'a1' => ['is being assessed', 'is assessed', 'is assessing', 'are being assessed'],
                ],
                'verb_hints' => ['a1' => 'assess'],
                'hints' => [
                    '**Present Continuous Passive** для оцінювання.',
                    'Приклад: The risks are being evaluated.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Negotiations {a1} between the two countries.',
                'answers' => ['a1' => 'are being conducted'],
                'options' => [
                    'a1' => ['are being conducted', 'is being conducted', 'are conducted', 'are conducting'],
                ],
                'verb_hints' => ['a1' => 'conduct'],
                'hints' => [
                    '**Present Continuous Passive** для дипломатичних процесів.',
                    'Приклад: Talks are being held in Geneva.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The manuscript {a1} for publication.',
                'answers' => ['a1' => 'is being reviewed'],
                'options' => [
                    'a1' => ['is being reviewed', 'is reviewed', 'is reviewing', 'are being reviewed'],
                ],
                'verb_hints' => ['a1' => 'review'],
                'hints' => [
                    '**Present Continuous Passive** для видавничих процесів.',
                    'Приклад: The article is being edited.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Sustainable practices {a1} across all departments.',
                'answers' => ['a1' => 'are being promoted'],
                'options' => [
                    'a1' => ['are being promoted', 'is being promoted', 'are promoted', 'are promoting'],
                ],
                'verb_hints' => ['a1' => 'promote'],
                'hints' => [
                    '**Present Continuous Passive** для корпоративних ініціатив.',
                    'Приклад: Green policies are being introduced.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Customer complaints {a1} seriously at the moment.',
                'answers' => ['a1' => "aren't being taken"],
                'options' => [
                    'a1' => ["aren't being taken", "isn't being taken", "aren't taken", "don't take"],
                ],
                'verb_hints' => ['a1' => 'take'],
                'hints' => [
                    '**Present Continuous Passive Negative** для критики.',
                    'Приклад: Concerns aren\'t being addressed.',
                ],
                'tag_ids' => [$negativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The issue {a1} with the urgency it deserves.',
                'answers' => ['a1' => "isn't being treated"],
                'options' => [
                    'a1' => ["isn't being treated", "aren't being treated", "isn't treated", "doesn't treat"],
                ],
                'verb_hints' => ['a1' => 'treat'],
                'hints' => [
                    '**Present Continuous Passive Negative** для критичних зауважень.',
                    'Приклад: The matter isn\'t being handled properly.',
                ],
                'tag_ids' => [$negativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => '{a1} sufficient resources being allocated to this project?',
                'answers' => ['a1' => 'Are'],
                'options' => [
                    'a1' => ['Are', 'Is', 'Do', 'Does'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Present Continuous Passive Question** для ресурсів.',
                    'Приклад: Is enough funding being provided?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'By whom {a1} the investigation being led?',
                'answers' => ['a1' => 'is'],
                'options' => [
                    'a1' => ['is', 'are', 'does', 'do'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Formal Passive Question**: By whom + is + subject + being + V3?',
                    'Приклад: By whom is the research being conducted?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The data breach {a1} by cybersecurity specialists.',
                'answers' => ['a1' => 'is being investigated'],
                'options' => [
                    'a1' => ['is being investigated', 'is investigated', 'is investigating', 'are being investigated'],
                ],
                'verb_hints' => ['a1' => 'investigate'],
                'hints' => [
                    '**Present Continuous Passive** для розслідувань.',
                    'Приклад: The incident is being examined.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Several reforms {a1} in the healthcare sector.',
                'answers' => ['a1' => 'are being introduced'],
                'options' => [
                    'a1' => ['are being introduced', 'is being introduced', 'are introduced', 'are introducing'],
                ],
                'verb_hints' => ['a1' => 'introduce'],
                'hints' => [
                    '**Present Continuous Passive** для реформ.',
                    'Приклад: Changes are being implemented.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Why {a1} the deadline being extended?',
                'answers' => ['a1' => "isn't"],
                'options' => [
                    'a1' => ["isn't", "aren't", "doesn't", "don't"],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Negative WH-Question Passive**: Why + isn\'t + subject + being + V3?',
                    'Приклад: Why isn\'t the schedule being adjusted?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The infrastructure {a1} to accommodate growth.',
                'answers' => ['a1' => 'is being expanded'],
                'options' => [
                    'a1' => ['is being expanded', 'is expanded', 'is expanding', 'are being expanded'],
                ],
                'verb_hints' => ['a1' => 'expand'],
                'hints' => [
                    '**Present Continuous Passive** для розвитку.',
                    'Приклад: The network is being upgraded.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],

            // ===== C1 Level: 12 questions =====
            [
                'level' => 'C1',
                'question' => 'The theoretical framework {a1} to address emerging challenges.',
                'answers' => ['a1' => 'is being refined'],
                'options' => [
                    'a1' => ['is being refined', 'is refined', 'is refining', 'are being refined'],
                ],
                'verb_hints' => ['a1' => 'refine'],
                'hints' => [
                    '**Present Continuous Passive** для академічного вдосконалення.',
                    'Приклад: The methodology is being improved.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Cross-cultural competencies {a1} in international business programs.',
                'answers' => ['a1' => 'are being emphasized'],
                'options' => [
                    'a1' => ['are being emphasized', 'is being emphasized', 'are emphasized', 'are emphasizing'],
                ],
                'verb_hints' => ['a1' => 'emphasize'],
                'hints' => [
                    '**Present Continuous Passive** для освітніх тенденцій.',
                    'Приклад: Soft skills are being developed.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The geopolitical implications {a1} by foreign policy analysts.',
                'answers' => ['a1' => 'are being scrutinized'],
                'options' => [
                    'a1' => ['are being scrutinized', 'is being scrutinized', 'are scrutinized', 'are scrutinizing'],
                ],
                'verb_hints' => ['a1' => 'scrutinize'],
                'hints' => [
                    '**Present Continuous Passive** для аналізу.',
                    'Приклад: The consequences are being examined.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Innovative approaches {a1} to tackle climate change.',
                'answers' => ['a1' => 'are being explored'],
                'options' => [
                    'a1' => ['are being explored', 'is being explored', 'are explored', 'are exploring'],
                ],
                'verb_hints' => ['a1' => 'explore'],
                'hints' => [
                    '**Present Continuous Passive** для наукових досліджень.',
                    'Приклад: New solutions are being sought.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The nuances of the policy {a1} in public discourse.',
                'answers' => ['a1' => "aren't being articulated"],
                'options' => [
                    'a1' => ["aren't being articulated", "isn't being articulated", "aren't articulated", "don't articulate"],
                ],
                'verb_hints' => ['a1' => 'articulate'],
                'hints' => [
                    '**Present Continuous Passive Negative** для критики.',
                    'Приклад: The details aren\'t being communicated.',
                ],
                'tag_ids' => [$negativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Indigenous perspectives {a1} in the decision-making process.',
                'answers' => ['a1' => "aren't being incorporated"],
                'options' => [
                    'a1' => ["aren't being incorporated", "isn't being incorporated", "aren't incorporated", "don't incorporate"],
                ],
                'verb_hints' => ['a1' => 'incorporate'],
                'hints' => [
                    '**Present Continuous Passive Negative** для соціальної критики.',
                    'Приклад: Minority voices aren\'t being heard.',
                ],
                'tag_ids' => [$negativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => '{a1} ethical standards being upheld in the industry?',
                'answers' => ['a1' => 'Are'],
                'options' => [
                    'a1' => ['Are', 'Is', 'Do', 'Does'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Present Continuous Passive Question** для етичних питань.',
                    'Приклад: Are regulations being followed?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'To what extent {a1} privacy rights being protected online?',
                'answers' => ['a1' => 'are'],
                'options' => [
                    'a1' => ['are', 'is', 'do', 'does'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Formal WH-Question Passive** для обговорення прав.',
                    'Приклад: To what extent is data security being ensured?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The socioeconomic disparities {a1} through targeted interventions.',
                'answers' => ['a1' => 'are being addressed'],
                'options' => [
                    'a1' => ['are being addressed', 'is being addressed', 'are addressed', 'are addressing'],
                ],
                'verb_hints' => ['a1' => 'address'],
                'hints' => [
                    '**Present Continuous Passive** для соціальних програм.',
                    'Приклад: Inequalities are being tackled.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Biodiversity conservation efforts {a1} globally.',
                'answers' => ['a1' => 'are being intensified'],
                'options' => [
                    'a1' => ['are being intensified', 'is being intensified', 'are intensified', 'are intensifying'],
                ],
                'verb_hints' => ['a1' => 'intensify'],
                'hints' => [
                    '**Present Continuous Passive** для екологічних зусиль.',
                    'Приклад: Environmental protections are being strengthened.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Why {a1} the recommendations being ignored by policymakers?',
                'answers' => ['a1' => 'are'],
                'options' => [
                    'a1' => ['are', 'is', 'do', 'does'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Passive** для критичних питань.',
                    'Приклад: Why are the suggestions being dismissed?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The paradigm shift {a1} in how we approach education.',
                'answers' => ['a1' => 'is being observed'],
                'options' => [
                    'a1' => ['is being observed', 'is observed', 'is observing', 'are being observed'],
                ],
                'verb_hints' => ['a1' => 'observe'],
                'hints' => [
                    '**Present Continuous Passive** для спостережень.',
                    'Приклад: A transformation is being witnessed.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],

            // ===== C2 Level: 12 questions =====
            [
                'level' => 'C2',
                'question' => 'The epistemological foundations {a1} by contemporary philosophers.',
                'answers' => ['a1' => 'are being reexamined'],
                'options' => [
                    'a1' => ['are being reexamined', 'is being reexamined', 'are reexamined', 'are reexamining'],
                ],
                'verb_hints' => ['a1' => 'reexamine'],
                'hints' => [
                    '**Present Continuous Passive** для філософського аналізу.',
                    'Приклад: Fundamental assumptions are being questioned.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The hegemonic discourse {a1} through critical analysis.',
                'answers' => ['a1' => 'is being deconstructed'],
                'options' => [
                    'a1' => ['is being deconstructed', 'is deconstructed', 'is deconstructing', 'are being deconstructed'],
                ],
                'verb_hints' => ['a1' => 'deconstruct'],
                'hints' => [
                    '**Present Continuous Passive** для критичного аналізу.',
                    'Приклад: The narrative is being challenged.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'Transdisciplinary methodologies {a1} in cutting-edge research.',
                'answers' => ['a1' => 'are being employed'],
                'options' => [
                    'a1' => ['are being employed', 'is being employed', 'are employed', 'are employing'],
                ],
                'verb_hints' => ['a1' => 'employ'],
                'hints' => [
                    '**Present Continuous Passive** для наукових методів.',
                    'Приклад: Novel techniques are being applied.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The ontological premises {a1} in light of new evidence.',
                'answers' => ['a1' => 'are being reconsidered'],
                'options' => [
                    'a1' => ['are being reconsidered', 'is being reconsidered', 'are reconsidered', 'are reconsidering'],
                ],
                'verb_hints' => ['a1' => 'reconsider'],
                'hints' => [
                    '**Present Continuous Passive** для перегляду теорій.',
                    'Приклад: The hypotheses are being revised.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The subtleties of the argument {a1} in mainstream media.',
                'answers' => ['a1' => "aren't being conveyed"],
                'options' => [
                    'a1' => ["aren't being conveyed", "isn't being conveyed", "aren't conveyed", "don't convey"],
                ],
                'verb_hints' => ['a1' => 'convey'],
                'hints' => [
                    '**Present Continuous Passive Negative** для медіа-критики.',
                    'Приклад: The complexities aren\'t being explained.',
                ],
                'tag_ids' => [$negativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The ramifications of the policy {a1} with sufficient rigor.',
                'answers' => ['a1' => "aren't being analyzed"],
                'options' => [
                    'a1' => ["aren't being analyzed", "isn't being analyzed", "aren't analyzed", "don't analyze"],
                ],
                'verb_hints' => ['a1' => 'analyze'],
                'hints' => [
                    '**Present Continuous Passive Negative** для аналітичної критики.',
                    'Приклад: The consequences aren\'t being evaluated.',
                ],
                'tag_ids' => [$negativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => '{a1} the axioms of this theory being interrogated by scholars?',
                'answers' => ['a1' => 'Are'],
                'options' => [
                    'a1' => ['Are', 'Is', 'Do', 'Does'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Present Continuous Passive Question** для академічних дискусій.',
                    'Приклад: Are the assumptions being tested?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'Through what mechanisms {a1} systemic inequalities being perpetuated?',
                'answers' => ['a1' => 'are'],
                'options' => [
                    'a1' => ['are', 'is', 'do', 'does'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Formal WH-Question Passive** для соціологічних досліджень.',
                    'Приклад: Through what processes are biases being maintained?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The hermeneutical approach {a1} to interpret historical texts.',
                'answers' => ['a1' => 'is being utilized'],
                'options' => [
                    'a1' => ['is being utilized', 'is utilized', 'is utilizing', 'are being utilized'],
                ],
                'verb_hints' => ['a1' => 'utilize'],
                'hints' => [
                    '**Present Continuous Passive** для методологічних підходів.',
                    'Приклад: The framework is being applied.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'Posthumanist perspectives {a1} in contemporary art criticism.',
                'answers' => ['a1' => 'are being integrated'],
                'options' => [
                    'a1' => ['are being integrated', 'is being integrated', 'are integrated', 'are integrating'],
                ],
                'verb_hints' => ['a1' => 'integrate'],
                'hints' => [
                    '**Present Continuous Passive** для мистецтвознавства.',
                    'Приклад: New theories are being incorporated.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The dialectical tensions {a1} in postmodern literature.',
                'answers' => ['a1' => 'are being explored'],
                'options' => [
                    'a1' => ['are being explored', 'is being explored', 'are explored', 'are exploring'],
                ],
                'verb_hints' => ['a1' => 'explore'],
                'hints' => [
                    '**Present Continuous Passive** для літературного аналізу.',
                    'Приклад: Thematic contradictions are being examined.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The teleological assumptions {a1} by evolutionary biologists.',
                'answers' => ['a1' => 'are being challenged'],
                'options' => [
                    'a1' => ['are being challenged', 'is being challenged', 'are challenged', 'are challenging'],
                ],
                'verb_hints' => ['a1' => 'challenge'],
                'hints' => [
                    '**Present Continuous Passive** для наукових дебатів.',
                    'Приклад: Traditional views are being questioned.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentContinuousTagId, $passiveTagId],
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
        return "✅ Правильно! «{$correct}» — це коректна форма Present Continuous Passive. Формула: is/am/are + being + past participle (V3).";
    }

    private function wrongExplanation(string $wrong, string $correct): string
    {
        // Missing "being"
        if ((str_contains(strtolower($correct), 'is being') || str_contains(strtolower($correct), 'are being') || str_contains(strtolower($correct), 'am being')) 
            && !str_contains(strtolower($wrong), 'being')) {
            if (str_contains(strtolower($wrong), 'is ') || str_contains(strtolower($wrong), 'are ')) {
                return "❌ «{$wrong}» — це Present Simple Passive. Для Present Continuous Passive потрібен \"being\": is/are + being + V3.";
            }
        }

        // Active voice instead of passive
        if (!str_contains(strtolower($wrong), 'is ') && !str_contains(strtolower($wrong), 'are ') && !str_contains(strtolower($wrong), 'am ') && !str_contains(strtolower($wrong), "isn't") && !str_contains(strtolower($wrong), "aren't")) {
            if (str_ends_with($wrong, 'ing')) {
                return "❌ «{$wrong}» — це Active Voice (активний стан). Тут потрібен Passive Voice: is/are + being + V3.";
            }
        }

        // Wrong auxiliary verb
        if (str_contains(strtolower($wrong), 'do') || str_contains(strtolower($wrong), 'does')) {
            return "❌ «{$wrong}» — do/does використовується в Active Voice. У Passive Voice потрібен is/are + being.";
        }

        // Wrong number agreement
        if (str_contains(strtolower($correct), 'are being') && str_contains(strtolower($wrong), 'is being')) {
            return "❌ «{$wrong}» — неправильне узгодження числа. З множиною використовуйте «are being», не «is being».";
        }
        if (str_contains(strtolower($correct), 'is being') && str_contains(strtolower($wrong), 'are being')) {
            return "❌ «{$wrong}» — неправильне узгодження числа. З одниною використовуйте «is being», не «are being».";
        }

        return "❌ «{$wrong}» — неправильна форма. Для Present Continuous Passive потрібно: is/are + being + past participle.";
    }
}
