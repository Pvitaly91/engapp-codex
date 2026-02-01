<?php

namespace Database\Seeders\AI\Claude\PassiveVoice;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class PresentSimplePassiveSeeder extends QuestionSeeder
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
        $sourceId = Source::firstOrCreate(['name' => 'AI Generated: Present Simple Passive'])->id;
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
            ['name' => 'Present Simple Passive'],
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

        $presentSimpleTagId = Tag::firstOrCreate(['name' => 'Present Simple'], ['category' => 'Tense'])->id;
        $passiveTagId = Tag::firstOrCreate(['name' => 'Passive Voice'], ['category' => 'Voice'])->id;

        return [
            // ===== A1 Level: 12 questions =====
            [
                'level' => 'A1',
                'question' => 'English {a1} in many countries.',
                'answers' => ['a1' => 'is spoken'],
                'options' => [
                    'a1' => ['is spoken', 'speaks', 'are spoken', 'is speaking'],
                ],
                'verb_hints' => ['a1' => 'speak'],
                'hints' => [
                    '**Present Simple Passive** утворюється за формулою: is/am/are + past participle (V3).',
                    'Використовуйте "is" з однини третьої особи.',
                    'Приклад: English is taught in schools.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The letters {a1} every morning.',
                'answers' => ['a1' => 'are delivered'],
                'options' => [
                    'a1' => ['are delivered', 'is delivered', 'delivers', 'are delivering'],
                ],
                'verb_hints' => ['a1' => 'deliver'],
                'hints' => [
                    '**Present Simple Passive** для множини: are + past participle.',
                    'Letters — множина, тому використовуємо "are".',
                    'Приклад: The newspapers are delivered daily.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'This book {a1} by students.',
                'answers' => ['a1' => 'is read'],
                'options' => [
                    'a1' => ['is read', 'reads', 'are read', 'is reading'],
                ],
                'verb_hints' => ['a1' => 'read'],
                'hints' => [
                    '**Present Simple Passive**: is/are + V3.',
                    '"Book" — однина, тому "is".',
                    'Приклад: This story is known everywhere.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The room {a1} every day.',
                'answers' => ['a1' => 'is cleaned'],
                'options' => [
                    'a1' => ['is cleaned', 'cleans', 'are cleaned', 'cleaning'],
                ],
                'verb_hints' => ['a1' => 'clean'],
                'hints' => [
                    '**Present Simple Passive** описує регулярні дії в пасивному стані.',
                    'Формула: is + V3 для однини.',
                    'Приклад: The office is cleaned daily.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'Cars {a1} in factories.',
                'answers' => ['a1' => 'are made'],
                'options' => [
                    'a1' => ['are made', 'is made', 'makes', 'are making'],
                ],
                'verb_hints' => ['a1' => 'make'],
                'hints' => [
                    '**Present Simple Passive** для загальних істин.',
                    '"Cars" — множина, тому "are".',
                    'Приклад: Computers are manufactured in China.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'Breakfast {a1} at 8 o\'clock.',
                'answers' => ['a1' => 'is served'],
                'options' => [
                    'a1' => ['is served', 'serves', 'are served', 'serving'],
                ],
                'verb_hints' => ['a1' => 'serve'],
                'hints' => [
                    '**Present Simple Passive** для розкладів і правил.',
                    'Формула: is + past participle.',
                    'Приклад: Dinner is served at 7 PM.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'This cake {a1} here.',
                'answers' => ['a1' => "isn't sold"],
                'options' => [
                    'a1' => ["isn't sold", "doesn't sell", "aren't sold", "not sold"],
                ],
                'verb_hints' => ['a1' => 'sell'],
                'hints' => [
                    '**Present Simple Passive Negative**: is not (isn\'t) + V3.',
                    'Заперечення утворюється додаванням "not" після is/are.',
                    'Приклад: This product isn\'t made here.',
                ],
                'tag_ids' => [$negativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'These toys {a1} in our store.',
                'answers' => ['a1' => "aren't sold"],
                'options' => [
                    'a1' => ["aren't sold", "isn't sold", "don't sell", "not selling"],
                ],
                'verb_hints' => ['a1' => 'sell'],
                'hints' => [
                    '**Present Simple Passive Negative** для множини: are not (aren\'t) + V3.',
                    'Приклад: These items aren\'t produced locally.',
                ],
                'tag_ids' => [$negativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => '{a1} the door locked at night?',
                'answers' => ['a1' => 'Is'],
                'options' => [
                    'a1' => ['Is', 'Does', 'Are', 'Do'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Present Simple Passive Question**: Is/Are + subject + V3?',
                    'Питання утворюється інверсією is/are.',
                    'Приклад: Is the window closed?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => '{a1} these products made in Italy?',
                'answers' => ['a1' => 'Are'],
                'options' => [
                    'a1' => ['Are', 'Is', 'Do', 'Does'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Present Simple Passive Question** для множини: Are + subject + V3?',
                    'Приклад: Are these shoes manufactured abroad?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The news {a1} on TV every evening.',
                'answers' => ['a1' => 'is broadcast'],
                'options' => [
                    'a1' => ['is broadcast', 'broadcasts', 'are broadcast', 'is broadcasting'],
                ],
                'verb_hints' => ['a1' => 'broadcast'],
                'hints' => [
                    '**Present Simple Passive**: is + V3.',
                    '"News" вживається з дієсловом в однині.',
                    'Приклад: The weather report is shown at 6 PM.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'I {a1} to speak loudly here.',
                'answers' => ['a1' => 'am not allowed'],
                'options' => [
                    'a1' => ['am not allowed', 'don\'t allow', 'is not allowed', 'not allowed'],
                ],
                'verb_hints' => ['a1' => 'allow'],
                'hints' => [
                    '**Present Simple Passive** з "I": am + V3.',
                    'Заперечення: am not + V3.',
                    'Приклад: I am not permitted to enter.',
                ],
                'tag_ids' => [$negativeTagId, $presentSimpleTagId, $passiveTagId],
            ],

            // ===== A2 Level: 12 questions =====
            [
                'level' => 'A2',
                'question' => 'The windows {a1} once a week.',
                'answers' => ['a1' => 'are washed'],
                'options' => [
                    'a1' => ['are washed', 'is washed', 'washes', 'washing'],
                ],
                'verb_hints' => ['a1' => 'wash'],
                'hints' => [
                    '**Present Simple Passive** виражає регулярні дії.',
                    'Формула для множини: are + past participle.',
                    'Приклад: The floors are mopped every day.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'Coffee {a1} in Brazil.',
                'answers' => ['a1' => 'is grown'],
                'options' => [
                    'a1' => ['is grown', 'grows', 'are grown', 'is growing'],
                ],
                'verb_hints' => ['a1' => 'grow'],
                'hints' => [
                    '**Present Simple Passive** для загальновідомих фактів.',
                    'Формула: is + V3.',
                    'Приклад: Tea is produced in India.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The museum {a1} by thousands of tourists.',
                'answers' => ['a1' => 'is visited'],
                'options' => [
                    'a1' => ['is visited', 'visits', 'are visited', 'visiting'],
                ],
                'verb_hints' => ['a1' => 'visit'],
                'hints' => [
                    '**Present Simple Passive**: is + past participle + by (agent).',
                    '"By" вказує на виконавця дії.',
                    'Приклад: The castle is admired by visitors.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'These computers {a1} to schools.',
                'answers' => ['a1' => 'are donated'],
                'options' => [
                    'a1' => ['are donated', 'is donated', 'donates', 'are donating'],
                ],
                'verb_hints' => ['a1' => 'donate'],
                'hints' => [
                    '**Present Simple Passive** для множини: are + V3.',
                    'Приклад: These books are given to students.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'Milk {a1} in the refrigerator.',
                'answers' => ['a1' => 'is kept'],
                'options' => [
                    'a1' => ['is kept', 'keeps', 'are kept', 'is keeping'],
                ],
                'verb_hints' => ['a1' => 'keep'],
                'hints' => [
                    '**Present Simple Passive** для правил і звичок.',
                    'Формула: is + V3.',
                    'Приклад: Vegetables are stored in the fridge.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'Smoking {a1} in this building.',
                'answers' => ['a1' => 'is not permitted'],
                'options' => [
                    'a1' => ['is not permitted', 'does not permit', 'are not permitted', 'not permits'],
                ],
                'verb_hints' => ['a1' => 'permit'],
                'hints' => [
                    '**Present Simple Passive Negative**: is not + V3.',
                    'Приклад: Photography is not allowed here.',
                ],
                'tag_ids' => [$negativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'Children {a1} to watch TV late at night.',
                'answers' => ['a1' => "aren't allowed"],
                'options' => [
                    'a1' => ["aren't allowed", "isn't allowed", "don't allow", "not allow"],
                ],
                'verb_hints' => ['a1' => 'allow'],
                'hints' => [
                    '**Present Simple Passive Negative** для множини: are not (aren\'t) + V3.',
                    'Приклад: Students aren\'t permitted to use phones.',
                ],
                'tag_ids' => [$negativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => '{a1} French taught in your school?',
                'answers' => ['a1' => 'Is'],
                'options' => [
                    'a1' => ['Is', 'Does', 'Are', 'Do'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Present Simple Passive Question**: Is + subject + V3?',
                    'Приклад: Is Spanish spoken here?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'Where {a1} these products manufactured?',
                'answers' => ['a1' => 'are'],
                'options' => [
                    'a1' => ['are', 'is', 'do', 'does'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question in Passive**: Where + are + subject + V3?',
                    'Приклад: Where are these cars assembled?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The building {a1} by security guards.',
                'answers' => ['a1' => 'is protected'],
                'options' => [
                    'a1' => ['is protected', 'protects', 'are protected', 'is protecting'],
                ],
                'verb_hints' => ['a1' => 'protect'],
                'hints' => [
                    '**Present Simple Passive** з агентом: is + V3 + by + agent.',
                    'Приклад: The area is guarded by police.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'How often {a1} the grass cut?',
                'answers' => ['a1' => 'is'],
                'options' => [
                    'a1' => ['is', 'are', 'does', 'do'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Present Simple Passive Question** з прислівником частоти.',
                    'Приклад: How often is the car washed?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'The reports {a1} to the manager weekly.',
                'answers' => ['a1' => 'are sent'],
                'options' => [
                    'a1' => ['are sent', 'is sent', 'sends', 'are sending'],
                ],
                'verb_hints' => ['a1' => 'send'],
                'hints' => [
                    '**Present Simple Passive** для регулярних робочих процесів.',
                    'Формула: are + V3.',
                    'Приклад: Emails are forwarded automatically.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],

            // ===== B1 Level: 12 questions =====
            [
                'level' => 'B1',
                'question' => 'The exhibition {a1} by famous artists.',
                'answers' => ['a1' => 'is curated'],
                'options' => [
                    'a1' => ['is curated', 'curates', 'are curated', 'is curating'],
                ],
                'verb_hints' => ['a1' => 'curate'],
                'hints' => [
                    '**Present Simple Passive** з агентом: is + V3 + by.',
                    'Приклад: The gallery is managed by professionals.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'Strict rules {a1} in this institution.',
                'answers' => ['a1' => 'are enforced'],
                'options' => [
                    'a1' => ['are enforced', 'is enforced', 'enforces', 'are enforcing'],
                ],
                'verb_hints' => ['a1' => 'enforce'],
                'hints' => [
                    '**Present Simple Passive** для правил: are + V3.',
                    'Приклад: Regulations are followed strictly.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The data {a1} and analyzed by scientists.',
                'answers' => ['a1' => 'is collected'],
                'options' => [
                    'a1' => ['is collected', 'collects', 'are collected', 'collecting'],
                ],
                'verb_hints' => ['a1' => 'collect'],
                'hints' => [
                    '**Present Simple Passive** для наукових процесів.',
                    '"Data" часто вживається з однини.',
                    'Приклад: Information is gathered regularly.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'New employees {a1} training before starting work.',
                'answers' => ['a1' => 'are given'],
                'options' => [
                    'a1' => ['are given', 'is given', 'gives', 'are giving'],
                ],
                'verb_hints' => ['a1' => 'give'],
                'hints' => [
                    '**Present Simple Passive** для корпоративних процесів.',
                    'Формула: are + V3.',
                    'Приклад: Interns are provided with equipment.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The package {a1} via express delivery.',
                'answers' => ['a1' => "isn't shipped"],
                'options' => [
                    'a1' => ["isn't shipped", "doesn't ship", "aren't shipped", "not ship"],
                ],
                'verb_hints' => ['a1' => 'ship'],
                'hints' => [
                    '**Present Simple Passive Negative**: is not (isn\'t) + V3.',
                    'Приклад: The order isn\'t processed on weekends.',
                ],
                'tag_ids' => [$negativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'Applications {a1} after the deadline.',
                'answers' => ['a1' => 'are not accepted'],
                'options' => [
                    'a1' => ['are not accepted', 'is not accepted', 'do not accept', 'not accepting'],
                ],
                'verb_hints' => ['a1' => 'accept'],
                'hints' => [
                    '**Present Simple Passive Negative**: are not + V3.',
                    'Приклад: Late submissions are not considered.',
                ],
                'tag_ids' => [$negativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => '{a1} all employees required to attend the meeting?',
                'answers' => ['a1' => 'Are'],
                'options' => [
                    'a1' => ['Are', 'Is', 'Do', 'Does'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Present Simple Passive Question**: Are + subject + V3?',
                    'Приклад: Are visitors allowed to enter?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'By whom {a1} the decisions made in this company?',
                'answers' => ['a1' => 'are'],
                'options' => [
                    'a1' => ['are', 'is', 'do', 'does'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Formal Passive Question** з "by whom": By whom + are + subject + V3?',
                    'Приклад: By whom is this project managed?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The software {a1} every month to fix bugs.',
                'answers' => ['a1' => 'is updated'],
                'options' => [
                    'a1' => ['is updated', 'updates', 'are updated', 'is updating'],
                ],
                'verb_hints' => ['a1' => 'update'],
                'hints' => [
                    '**Present Simple Passive** для регулярних технічних процесів.',
                    'Приклад: The system is maintained weekly.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'Why {a1} this method used in our research?',
                'answers' => ['a1' => "isn't"],
                'options' => [
                    'a1' => ["isn't", "doesn't", "aren't", "don't"],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Negative Passive**: Why + isn\'t + subject + V3?',
                    'Приклад: Why isn\'t this approach adopted?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The injured {a1} to the nearest hospital.',
                'answers' => ['a1' => 'are transported'],
                'options' => [
                    'a1' => ['are transported', 'is transported', 'transports', 'are transporting'],
                ],
                'verb_hints' => ['a1' => 'transport'],
                'hints' => [
                    '**Present Simple Passive**: "The injured" — множина.',
                    'Приклад: The wounded are treated immediately.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The equipment {a1} regularly to ensure safety.',
                'answers' => ['a1' => 'is inspected'],
                'options' => [
                    'a1' => ['is inspected', 'inspects', 'are inspected', 'is inspecting'],
                ],
                'verb_hints' => ['a1' => 'inspect'],
                'hints' => [
                    '**Present Simple Passive** для правил безпеки.',
                    'Приклад: The machinery is checked daily.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],

            // ===== B2 Level: 12 questions =====
            [
                'level' => 'B2',
                'question' => 'The proposal {a1} by the board before implementation.',
                'answers' => ['a1' => 'is reviewed'],
                'options' => [
                    'a1' => ['is reviewed', 'reviews', 'are reviewed', 'is reviewing'],
                ],
                'verb_hints' => ['a1' => 'review'],
                'hints' => [
                    '**Present Simple Passive** для формальних процедур.',
                    'Приклад: The application is assessed by a committee.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Confidential documents {a1} in secure locations.',
                'answers' => ['a1' => 'are stored'],
                'options' => [
                    'a1' => ['are stored', 'is stored', 'stores', 'are storing'],
                ],
                'verb_hints' => ['a1' => 'store'],
                'hints' => [
                    '**Present Simple Passive** для правил зберігання.',
                    'Приклад: Sensitive files are encrypted automatically.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The scholarship {a1} to outstanding students annually.',
                'answers' => ['a1' => 'is awarded'],
                'options' => [
                    'a1' => ['is awarded', 'awards', 'are awarded', 'is awarding'],
                ],
                'verb_hints' => ['a1' => 'award'],
                'hints' => [
                    '**Present Simple Passive** для регулярних нагород.',
                    'Приклад: The prize is given to the best performer.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'International standards {a1} across all branches.',
                'answers' => ['a1' => 'are implemented'],
                'options' => [
                    'a1' => ['are implemented', 'is implemented', 'implements', 'are implementing'],
                ],
                'verb_hints' => ['a1' => 'implement'],
                'hints' => [
                    '**Present Simple Passive** для корпоративних стандартів.',
                    'Приклад: Global policies are applied uniformly.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Personal information {a1} with third parties.',
                'answers' => ['a1' => 'is not shared'],
                'options' => [
                    'a1' => ['is not shared', 'does not share', 'are not shared', 'not sharing'],
                ],
                'verb_hints' => ['a1' => 'share'],
                'hints' => [
                    '**Present Simple Passive Negative** для політик конфіденційності.',
                    'Приклад: Data is not disclosed without consent.',
                ],
                'tag_ids' => [$negativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Exceptions {a1} under any circumstances.',
                'answers' => ['a1' => 'are not made'],
                'options' => [
                    'a1' => ['are not made', 'is not made', 'do not make', 'not making'],
                ],
                'verb_hints' => ['a1' => 'make'],
                'hints' => [
                    '**Present Simple Passive Negative** для строгих правил.',
                    'Приклад: Refunds are not granted after 30 days.',
                ],
                'tag_ids' => [$negativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => '{a1} clinical trials conducted before drug approval?',
                'answers' => ['a1' => 'Are'],
                'options' => [
                    'a1' => ['Are', 'Is', 'Do', 'Does'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Present Simple Passive Question** для процедур.',
                    'Приклад: Are tests performed before release?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'How {a1} the quality of products ensured?',
                'answers' => ['a1' => 'is'],
                'options' => [
                    'a1' => ['is', 'are', 'does', 'do'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**WH-Question Passive**: How + is + subject + V3?',
                    'Приклад: How is customer satisfaction measured?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The curriculum {a1} to meet industry needs.',
                'answers' => ['a1' => 'is designed'],
                'options' => [
                    'a1' => ['is designed', 'designs', 'are designed', 'is designing'],
                ],
                'verb_hints' => ['a1' => 'design'],
                'hints' => [
                    '**Present Simple Passive** для опису призначення.',
                    'Приклад: The program is structured for beginners.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The contracts {a1} by legal experts before signing.',
                'answers' => ['a1' => 'are examined'],
                'options' => [
                    'a1' => ['are examined', 'is examined', 'examines', 'are examining'],
                ],
                'verb_hints' => ['a1' => 'examine'],
                'hints' => [
                    '**Present Simple Passive** для юридичних процедур.',
                    'Приклад: Agreements are verified by lawyers.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Access to the server {a1} without authorization.',
                'answers' => ['a1' => "isn't granted"],
                'options' => [
                    'a1' => ["isn't granted", "doesn't grant", "aren't granted", "not granting"],
                ],
                'verb_hints' => ['a1' => 'grant'],
                'hints' => [
                    '**Present Simple Passive Negative** для безпеки.',
                    'Приклад: Entry isn\'t permitted without a badge.',
                ],
                'tag_ids' => [$negativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Feedback from customers {a1} to improve our services.',
                'answers' => ['a1' => 'is used'],
                'options' => [
                    'a1' => ['is used', 'uses', 'are used', 'is using'],
                ],
                'verb_hints' => ['a1' => 'use'],
                'hints' => [
                    '**Present Simple Passive** для процесів покращення.',
                    'Приклад: Suggestions are considered carefully.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],

            // ===== C1 Level: 12 questions =====
            [
                'level' => 'C1',
                'question' => 'The hypothesis {a1} through rigorous experimentation.',
                'answers' => ['a1' => 'is tested'],
                'options' => [
                    'a1' => ['is tested', 'tests', 'are tested', 'is testing'],
                ],
                'verb_hints' => ['a1' => 'test'],
                'hints' => [
                    '**Present Simple Passive** для наукових методів.',
                    'Приклад: Theories are validated through research.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Renewable energy sources {a1} as viable alternatives.',
                'answers' => ['a1' => 'are regarded'],
                'options' => [
                    'a1' => ['are regarded', 'is regarded', 'regards', 'are regarding'],
                ],
                'verb_hints' => ['a1' => 'regard'],
                'hints' => [
                    '**Present Simple Passive** з "as": are + V3 + as.',
                    'Приклад: These methods are considered effective.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The manuscript {a1} to a thorough peer review.',
                'answers' => ['a1' => 'is subjected'],
                'options' => [
                    'a1' => ['is subjected', 'subjects', 'are subjected', 'is subjecting'],
                ],
                'verb_hints' => ['a1' => 'subject'],
                'hints' => [
                    '**Present Simple Passive** з "to": is + V3 + to.',
                    'Приклад: The report is submitted to the committee.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Ethical considerations {a1} in all research projects.',
                'answers' => ['a1' => 'are prioritized'],
                'options' => [
                    'a1' => ['are prioritized', 'is prioritized', 'prioritizes', 'are prioritizing'],
                ],
                'verb_hints' => ['a1' => 'prioritize'],
                'hints' => [
                    '**Present Simple Passive** для академічних стандартів.',
                    'Приклад: Safety protocols are observed strictly.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The authenticity of the artifact {a1} by specialists.',
                'answers' => ['a1' => "isn't disputed"],
                'options' => [
                    'a1' => ["isn't disputed", "doesn't dispute", "aren't disputed", "not disputing"],
                ],
                'verb_hints' => ['a1' => 'dispute'],
                'hints' => [
                    '**Present Simple Passive Negative** для експертних думок.',
                    'Приклад: The findings aren\'t questioned by experts.',
                ],
                'tag_ids' => [$negativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Such allegations {a1} without substantial evidence.',
                'answers' => ['a1' => 'are not entertained'],
                'options' => [
                    'a1' => ['are not entertained', 'is not entertained', 'do not entertain', 'not entertaining'],
                ],
                'verb_hints' => ['a1' => 'entertain'],
                'hints' => [
                    '**Present Simple Passive Negative** для формальних відмов.',
                    'Приклад: Claims are not processed without proof.',
                ],
                'tag_ids' => [$negativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => '{a1} indigenous languages adequately preserved by governments?',
                'answers' => ['a1' => 'Are'],
                'options' => [
                    'a1' => ['Are', 'Is', 'Do', 'Does'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Present Simple Passive Question** для дискусійних питань.',
                    'Приклад: Are cultural traditions sufficiently protected?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'To what extent {a1} innovation stifled by regulations?',
                'answers' => ['a1' => 'is'],
                'options' => [
                    'a1' => ['is', 'are', 'does', 'do'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Formal WH-Question Passive**: To what extent + is + subject + V3?',
                    'Приклад: To what extent is progress hindered?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The phenomenon {a1} in terms of multiple variables.',
                'answers' => ['a1' => 'is explained'],
                'options' => [
                    'a1' => ['is explained', 'explains', 'are explained', 'is explaining'],
                ],
                'verb_hints' => ['a1' => 'explain'],
                'hints' => [
                    '**Present Simple Passive** для наукових пояснень.',
                    'Приклад: The results are interpreted differently.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Stringent measures {a1} to combat fraud.',
                'answers' => ['a1' => 'are employed'],
                'options' => [
                    'a1' => ['are employed', 'is employed', 'employs', 'are employing'],
                ],
                'verb_hints' => ['a1' => 'employ'],
                'hints' => [
                    '**Present Simple Passive** для опису заходів.',
                    'Приклад: Advanced techniques are utilized to detect errors.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The suspect\'s rights {a1} under constitutional law.',
                'answers' => ['a1' => 'are protected'],
                'options' => [
                    'a1' => ['are protected', 'is protected', 'protects', 'are protecting'],
                ],
                'verb_hints' => ['a1' => 'protect'],
                'hints' => [
                    '**Present Simple Passive** для юридичних гарантій.',
                    'Приклад: Civil liberties are guaranteed by law.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The credibility of witnesses {a1} during cross-examination.',
                'answers' => ['a1' => 'is assessed'],
                'options' => [
                    'a1' => ['is assessed', 'assesses', 'are assessed', 'is assessing'],
                ],
                'verb_hints' => ['a1' => 'assess'],
                'hints' => [
                    '**Present Simple Passive** для судових процедур.',
                    'Приклад: Evidence is evaluated by the jury.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],

            // ===== C2 Level: 12 questions =====
            [
                'level' => 'C2',
                'question' => 'The nuances of the text {a1} through meticulous analysis.',
                'answers' => ['a1' => 'are discerned'],
                'options' => [
                    'a1' => ['are discerned', 'is discerned', 'discerns', 'are discerning'],
                ],
                'verb_hints' => ['a1' => 'discern'],
                'hints' => [
                    '**Present Simple Passive** для літературного аналізу.',
                    'Приклад: Subtleties are detected through careful reading.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The theorem {a1} through deductive reasoning.',
                'answers' => ['a1' => 'is proved'],
                'options' => [
                    'a1' => ['is proved', 'proves', 'are proved', 'is proving'],
                ],
                'verb_hints' => ['a1' => 'prove'],
                'hints' => [
                    '**Present Simple Passive** для математичних доведень.',
                    'Приклад: The equation is solved using established methods.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'Societal norms {a1} and reconstructed across generations.',
                'answers' => ['a1' => 'are deconstructed'],
                'options' => [
                    'a1' => ['are deconstructed', 'is deconstructed', 'deconstructs', 'are deconstructing'],
                ],
                'verb_hints' => ['a1' => 'deconstruct'],
                'hints' => [
                    '**Present Simple Passive** для соціологічних процесів.',
                    'Приклад: Values are transmitted through education.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'Cognitive biases {a1} in experimental psychology.',
                'answers' => ['a1' => 'are investigated'],
                'options' => [
                    'a1' => ['are investigated', 'is investigated', 'investigates', 'are investigating'],
                ],
                'verb_hints' => ['a1' => 'investigate'],
                'hints' => [
                    '**Present Simple Passive** для досліджень.',
                    'Приклад: Mental processes are studied extensively.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The veracity of such claims {a1} without empirical data.',
                'answers' => ['a1' => 'is not ascertained'],
                'options' => [
                    'a1' => ['is not ascertained', 'does not ascertain', 'are not ascertained', 'not ascertaining'],
                ],
                'verb_hints' => ['a1' => 'ascertain'],
                'hints' => [
                    '**Present Simple Passive Negative** для наукової критики.',
                    'Приклад: Conclusions are not drawn without evidence.',
                ],
                'tag_ids' => [$negativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'Unsubstantiated theories {a1} in academic circles.',
                'answers' => ['a1' => 'are not propagated'],
                'options' => [
                    'a1' => ['are not propagated', 'is not propagated', 'do not propagate', 'not propagating'],
                ],
                'verb_hints' => ['a1' => 'propagate'],
                'hints' => [
                    '**Present Simple Passive Negative** для академічних стандартів.',
                    'Приклад: Pseudoscience is not tolerated in research.',
                ],
                'tag_ids' => [$negativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => '{a1} the epistemological foundations of this theory adequately examined?',
                'answers' => ['a1' => 'Are'],
                'options' => [
                    'a1' => ['Are', 'Is', 'Do', 'Does'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Present Simple Passive Question** для філософських дискусій.',
                    'Приклад: Are the ontological implications fully explored?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'Through what mechanisms {a1} collective memory perpetuated?',
                'answers' => ['a1' => 'is'],
                'options' => [
                    'a1' => ['is', 'are', 'does', 'do'],
                ],
                'verb_hints' => ['a1' => 'Use auxiliary for passive question'],
                'hints' => [
                    '**Formal WH-Question Passive** для академічних досліджень.',
                    'Приклад: Through what processes is knowledge transmitted?',
                ],
                'tag_ids' => [$interrogativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The interplay between nature and nurture {a1} extensively.',
                'answers' => ['a1' => 'is debated'],
                'options' => [
                    'a1' => ['is debated', 'debates', 'are debated', 'is debating'],
                ],
                'verb_hints' => ['a1' => 'debate'],
                'hints' => [
                    '**Present Simple Passive** для триваючих дискусій.',
                    'Приклад: The topic is discussed in academic journals.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'Linguistic structures {a1} as reflections of cognitive processes.',
                'answers' => ['a1' => 'are conceptualized'],
                'options' => [
                    'a1' => ['are conceptualized', 'is conceptualized', 'conceptualizes', 'are conceptualizing'],
                ],
                'verb_hints' => ['a1' => 'conceptualize'],
                'hints' => [
                    '**Present Simple Passive** для теоретичних концепцій.',
                    'Приклад: Language is viewed as a social construct.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The ramifications of climate change {a1} across disciplines.',
                'answers' => ['a1' => 'are analyzed'],
                'options' => [
                    'a1' => ['are analyzed', 'is analyzed', 'analyzes', 'are analyzing'],
                ],
                'verb_hints' => ['a1' => 'analyze'],
                'hints' => [
                    '**Present Simple Passive** для міждисциплінарних досліджень.',
                    'Приклад: Environmental impacts are studied globally.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The axioms of this philosophical system {a1} as self-evident.',
                'answers' => ['a1' => 'are posited'],
                'options' => [
                    'a1' => ['are posited', 'is posited', 'posits', 'are positing'],
                ],
                'verb_hints' => ['a1' => 'posit'],
                'hints' => [
                    '**Present Simple Passive** для філософських тверджень.',
                    'Приклад: The premises are assumed to be true.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentSimpleTagId, $passiveTagId],
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
        return "✅ Правильно! «{$correct}» — це коректна форма Present Simple Passive. Формула: is/am/are + past participle (V3).";
    }

    private function wrongExplanation(string $wrong, string $correct): string
    {
        // Active voice instead of passive
        if (!str_contains(strtolower($wrong), 'is ') && !str_contains(strtolower($wrong), 'are ') && !str_contains(strtolower($wrong), 'am ') && !str_contains(strtolower($wrong), "isn't") && !str_contains(strtolower($wrong), "aren't")) {
            if (str_ends_with($wrong, 's') || str_ends_with($wrong, 'es')) {
                return "❌ «{$wrong}» — це Active Voice (активний стан). Тут потрібен Passive Voice: is/are + V3.";
            }
            if (str_ends_with($wrong, 'ing')) {
                return "❌ «{$wrong}» — це форма Continuous. Для Present Simple Passive потрібно: is/are + V3 (без -ing).";
            }
        }

        // Wrong auxiliary verb
        if (str_contains(strtolower($wrong), 'do') || str_contains(strtolower($wrong), 'does')) {
            return "❌ «{$wrong}» — do/does використовується в Active Voice. У Passive Voice потрібен is/are.";
        }

        // Wrong number agreement
        if (str_contains(strtolower($correct), 'are ') && str_contains(strtolower($wrong), 'is ')) {
            return "❌ «{$wrong}» — неправильне узгодження числа. З множиною використовуйте «are», не «is».";
        }
        if (str_contains(strtolower($correct), 'is ') && str_contains(strtolower($wrong), 'are ')) {
            return "❌ «{$wrong}» — неправильне узгодження числа. З одниною використовуйте «is», не «are».";
        }

        return "❌ «{$wrong}» — неправильна форма. Для Present Simple Passive потрібно: is/are + past participle.";
    }
}
