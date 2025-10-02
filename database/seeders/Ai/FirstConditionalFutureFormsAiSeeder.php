<?php

namespace Database\Seeders\Ai;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class FirstConditionalFutureFormsAiSeeder extends QuestionSeeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Conditionals'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Custom: First Conditional Future Forms AI'])->id;

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'First Conditional Future Forms Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'First Conditional Sentences'],
            ['category' => 'English Grammar Structure']
        )->id;

        $tenseTagIds = [
            'First Conditional' => Tag::firstOrCreate(['name' => 'First Conditional'], ['category' => 'Tenses'])->id,
            'Future Simple' => Tag::firstOrCreate(['name' => 'Future Simple'], ['category' => 'Tenses'])->id,
            'Future Continuous' => Tag::firstOrCreate(['name' => 'Future Continuous'], ['category' => 'Tenses'])->id,
            'Future Perfect' => Tag::firstOrCreate(['name' => 'Future Perfect'], ['category' => 'Tenses'])->id,
            'Future Perfect Continuous' => Tag::firstOrCreate(['name' => 'Future Perfect Continuous'], ['category' => 'Tenses'])->id,
        ];

        $levelDifficulty = [
            'A1' => 1,
            'A2' => 2,
            'B1' => 3,
            'B2' => 4,
            'C1' => 5,
            'C2' => 5,
        ];

        $items = [];
        $meta = [];

        foreach ($this->buildQuestions() as $index => $question) {
            $options = [];
            $answersMap = [];
            $verbHints = [];
            $markerMeta = [];

            foreach ($question['markers'] as $marker => $markerData) {
                $options[$marker] = $markerData['options'];
                $answersMap[$marker] = $this->normalizeValue($markerData['answer']);
                $verbHints[$marker] = $this->normalizeHint($markerData['hint'] ?? null);
                $markerMeta[$marker] = $markerData['meta'] ?? [];
            }

            $optionSets = $this->prepareOptionSets($options, $answersMap);
            $flattenedOptions = $this->flattenOptions($optionSets);

            $example = $this->buildExample($question['question'], $answersMap);

            $answers = [];
            foreach ($answersMap as $marker => $answer) {
                $answers[] = [
                    'marker' => $marker,
                    'answer' => $answer,
                    'verb_hint' => $verbHints[$marker] ?? null,
                ];
            }

            $hints = [];
            $explanations = [];
            $optionMarkerMap = [];

            foreach ($optionSets as $marker => $optionList) {
                $metaInfo = $markerMeta[$marker] ?? [];

                foreach ($optionList as $option) {
                    if (! isset($optionMarkerMap[$option])) {
                        $optionMarkerMap[$option] = $marker;
                    }
                }

                $hints[$marker] = $this->buildHintForClause($metaInfo, $verbHints[$marker] ?? null, $example);
                $explanations = array_merge(
                    $explanations,
                    $this->buildExplanationsForClause(
                        $metaInfo,
                        $optionList,
                        $answersMap[$marker],
                        $example
                    )
                );
            }

            $tagIds = [$themeTagId, $structureTagId];
            foreach ($question['tense_tags'] as $tenseName) {
                if (! isset($tenseTagIds[$tenseName])) {
                    $tenseTagIds[$tenseName] = Tag::firstOrCreate(['name' => $tenseName], ['category' => 'Tenses'])->id;
                }

                $tagIds[] = $tenseTagIds[$tenseName];
            }

            $uuid = $this->generateQuestionUuid($index + 1, $question['question']);

            $items[] = [
                'uuid' => $uuid,
                'question' => $question['question'],
                'category_id' => $categoryId,
                'difficulty' => $levelDifficulty[$question['level']] ?? 3,
                'source_id' => $sourceId,
                'flag' => 2,
                'level' => $question['level'],
                'tag_ids' => array_values(array_unique($tagIds)),
                'answers' => $answers,
                'options' => $flattenedOptions,
                'variants' => [$question['question']],
            ];

            $meta[] = [
                'uuid' => $uuid,
                'answers' => $answersMap,
                'option_markers' => $optionMarkerMap,
                'hints' => $hints,
                'explanations' => $explanations,
            ];
        }

        $this->seedQuestionData($items, $meta);
    }

    private function buildQuestions(): array
    {
        $questions = [];

        // A1 level — Future Simple (questions)
        $questions[] = $this->makeQuestion(
            'A1',
            'Will we {a2} the picnic if the weather {a1} sunny?',
            [
                'options' => ['is', 'will be', 'are being'],
                'answer' => 'is',
                'hint' => '(be)',
            ],
            [
                'options' => ['have', 'has', 'having'],
                'answer' => 'have',
                'hint' => '(have)',
                'form' => 'question',
            ],
            'Future Simple'
        );

        $questions[] = $this->makeQuestion(
            'A1',
            'Will she {a2} you if the train {a1} late?',
            [
                'options' => ['arrives', 'will arrive', 'is arriving'],
                'answer' => 'arrives',
                'hint' => '(arrive)',
            ],
            [
                'options' => ['call', 'calls', 'calling'],
                'answer' => 'call',
                'hint' => '(call)',
                'form' => 'question',
            ],
            'Future Simple'
        );

        $questions[] = $this->makeQuestion(
            'A1',
            'Will they {a2} a taxi if the rain {a1} heavy?',
            [
                'options' => ['gets', 'will get', 'is getting'],
                'answer' => 'gets',
                'hint' => '(get)',
            ],
            [
                'options' => ['take', 'takes', 'taking'],
                'answer' => 'take',
                'hint' => '(take)',
                'form' => 'question',
            ],
            'Future Simple'
        );

        // A1 level — Future Simple (negatives)
        $questions[] = $this->makeQuestion(
            'A1',
            'We {a2} the picnic if the weather {a1} rainy.',
            [
                'options' => ['is', 'will be', 'was'],
                'answer' => 'is',
                'hint' => '(be)',
            ],
            [
                'options' => ["won't have", 'will have', 'are having'],
                'answer' => "won't have",
                'hint' => '(have)',
                'form' => 'negative',
            ],
            'Future Simple'
        );

        $questions[] = $this->makeQuestion(
            'A1',
            'She {a2} you if the train {a1} late again.',
            [
                'options' => ['arrives', 'will arrive', 'arrived'],
                'answer' => 'arrives',
                'hint' => '(arrive)',
            ],
            [
                'options' => ["won't call", 'will call', 'is calling'],
                'answer' => "won't call",
                'hint' => '(call)',
                'form' => 'negative',
            ],
            'Future Simple'
        );

        $questions[] = $this->makeQuestion(
            'A1',
            'They {a2} a taxi if the rain {a1} light showers.',
            [
                'options' => ['brings', 'will bring', 'is bringing'],
                'answer' => 'brings',
                'hint' => '(bring)',
            ],
            [
                'options' => ["won't take", 'will take', 'are taking'],
                'answer' => "won't take",
                'hint' => '(take)',
                'form' => 'negative',
            ],
            'Future Simple'
        );

        // A1 level — Future Continuous (questions)
        $questions[] = $this->makeQuestion(
            'A1',
            'Will you be {a2} outside if the sun {a1} warm?',
            [
                'options' => ['is', 'will be', 'was'],
                'answer' => 'is',
                'hint' => '(be)',
            ],
            [
                'options' => ['playing', 'play', 'played'],
                'answer' => 'playing',
                'hint' => '(play)',
                'form' => 'question',
            ],
            'Future Continuous'
        );

        $questions[] = $this->makeQuestion(
            'A1',
            'Will the kids be {a2} in the yard if the rain {a1}?',
            [
                'options' => ['stops', 'will stop', 'is stopping'],
                'answer' => 'stops',
                'hint' => '(stop)',
            ],
            [
                'options' => ['running', 'run', 'ran'],
                'answer' => 'running',
                'hint' => '(run)',
                'form' => 'question',
            ],
            'Future Continuous'
        );

        $questions[] = $this->makeQuestion(
            'A1',
            'Will Anna be {a2} dinner if the guests {a1} early?',
            [
                'options' => ['arrive', 'will arrive', 'are arriving'],
                'answer' => 'arrive',
                'hint' => '(arrive)',
            ],
            [
                'options' => ['cooking', 'cook', 'cooked'],
                'answer' => 'cooking',
                'hint' => '(cook)',
                'form' => 'question',
            ],
            'Future Continuous'
        );

        // A1 level — Future Continuous (negatives)
        $questions[] = $this->makeQuestion(
            'A1',
            'You {a2} outside if the sun {a1} too hot.',
            [
                'options' => ['gets', 'will get', 'is getting'],
                'answer' => 'gets',
                'hint' => '(get)',
            ],
            [
                'options' => ["won't be playing", "won't play", "aren't playing"],
                'answer' => "won't be playing",
                'hint' => '(play)',
                'form' => 'negative',
            ],
            'Future Continuous'
        );

        $questions[] = $this->makeQuestion(
            'A1',
            'The kids {a2} in the yard if the rain {a1} again.',
            [
                'options' => ['starts', 'will start', 'started'],
                'answer' => 'starts',
                'hint' => '(start)',
            ],
            [
                'options' => ["won't be running", "won't run", "aren't running"],
                'answer' => "won't be running",
                'hint' => '(run)',
                'form' => 'negative',
            ],
            'Future Continuous'
        );

        $questions[] = $this->makeQuestion(
            'A1',
            'Anna {a2} dinner if the guests {a1} late.',
            [
                'options' => ['come', 'will come', 'are coming'],
                'answer' => 'come',
                'hint' => '(come)',
            ],
            [
                'options' => ["won't be cooking", "won't cook", "isn't cooking"],
                'answer' => "won't be cooking",
                'hint' => '(cook)',
                'form' => 'negative',
            ],
            'Future Continuous'
        );

        // A1 level — Future Perfect (questions)
        $questions[] = $this->makeQuestion(
            'A1',
            'Will she have {a2} her homework if the class {a1} early?',
            [
                'options' => ['ends', 'will end', 'is ending'],
                'answer' => 'ends',
                'hint' => '(end)',
            ],
            [
                'options' => ['finished', 'finish', 'finishing'],
                'answer' => 'finished',
                'hint' => '(finish)',
                'form' => 'question',
            ],
            'Future Perfect'
        );

        $questions[] = $this->makeQuestion(
            'A1',
            'Will they have {a2} the room if the teacher {a1} the bell?',
            [
                'options' => ['rings', 'will ring', 'is ringing'],
                'answer' => 'rings',
                'hint' => '(ring)',
            ],
            [
                'options' => ['cleaned', 'clean', 'cleaning'],
                'answer' => 'cleaned',
                'hint' => '(clean)',
                'form' => 'question',
            ],
            'Future Perfect'
        );

        $questions[] = $this->makeQuestion(
            'A1',
            'Will we have {a2} the bags if the bus {a1} on time?',
            [
                'options' => ['arrives', 'will arrive', 'arrived'],
                'answer' => 'arrives',
                'hint' => '(arrive)',
            ],
            [
                'options' => ['packed', 'pack', 'packing'],
                'answer' => 'packed',
                'hint' => '(pack)',
                'form' => 'question',
            ],
            'Future Perfect'
        );

        // A1 level — Future Perfect (negatives)
        $questions[] = $this->makeQuestion(
            'A1',
            'She {a2} her homework if the class {a1} late.',
            [
                'options' => ['runs', 'will run', 'is running'],
                'answer' => 'runs',
                'hint' => '(run)',
            ],
            [
                'options' => ["won't have finished", 'will have finished', 'is finishing'],
                'answer' => "won't have finished",
                'hint' => '(finish)',
                'form' => 'negative',
            ],
            'Future Perfect'
        );

        $questions[] = $this->makeQuestion(
            'A1',
            'They {a2} the room if the teacher {a1} early.',
            [
                'options' => ['leaves', 'will leave', 'left'],
                'answer' => 'leaves',
                'hint' => '(leave)',
            ],
            [
                'options' => ["won't have cleaned", 'will have cleaned', 'are cleaning'],
                'answer' => "won't have cleaned",
                'hint' => '(clean)',
                'form' => 'negative',
            ],
            'Future Perfect'
        );

        $questions[] = $this->makeQuestion(
            'A1',
            'We {a2} the bags if the bus {a1} soon.',
            [
                'options' => ['comes', 'will come', 'is coming'],
                'answer' => 'comes',
                'hint' => '(come)',
            ],
            [
                'options' => ["won't have packed", 'will have packed', 'are packing'],
                'answer' => "won't have packed",
                'hint' => '(pack)',
                'form' => 'negative',
            ],
            'Future Perfect'
        );

        // A1 level — Future Perfect Continuous (questions)
        $questions[] = $this->makeQuestion(
            'A1',
            'Will they have been {a2} long if the bus {a1} late?',
            [
                'options' => ['arrives', 'will arrive', 'arrived'],
                'answer' => 'arrives',
                'hint' => '(arrive)',
            ],
            [
                'options' => ['waiting', 'wait', 'waited'],
                'answer' => 'waiting',
                'hint' => '(wait)',
                'form' => 'question',
            ],
            'Future Perfect Continuous'
        );

        $questions[] = $this->makeQuestion(
            'A1',
            'Will she have been {a2} for hours if the store {a1} late?',
            [
                'options' => ['opens', 'will open', 'opening'],
                'answer' => 'opens',
                'hint' => '(open)',
            ],
            [
                'options' => ['waiting', 'wait', 'waited'],
                'answer' => 'waiting',
                'hint' => '(wait)',
                'form' => 'question',
            ],
            'Future Perfect Continuous'
        );

        $questions[] = $this->makeQuestion(
            'A1',
            'Will we have been {a2} long if the teacher {a1} after lunch?',
            [
                'options' => ['returns', 'will return', 'returned'],
                'answer' => 'returns',
                'hint' => '(return)',
            ],
            [
                'options' => ['studying', 'study', 'studied'],
                'answer' => 'studying',
                'hint' => '(study)',
                'form' => 'question',
            ],
            'Future Perfect Continuous'
        );

        // A1 level — Future Perfect Continuous (negatives)
        $questions[] = $this->makeQuestion(
            'A1',
            'They {a2} long if the bus {a1} on time.',
            [
                'options' => ['arrives', 'will arrive', 'arrived'],
                'answer' => 'arrives',
                'hint' => '(arrive)',
            ],
            [
                'options' => ["won't have been waiting", 'will have been waiting', 'are waiting'],
                'answer' => "won't have been waiting",
                'hint' => '(wait)',
                'form' => 'negative',
            ],
            'Future Perfect Continuous'
        );

        $questions[] = $this->makeQuestion(
            'A1',
            'She {a2} for hours if the store {a1} early.',
            [
                'options' => ['opens', 'will open', 'opened'],
                'answer' => 'opens',
                'hint' => '(open)',
            ],
            [
                'options' => ["won't have been standing", 'will have been standing', 'is standing'],
                'answer' => "won't have been standing",
                'hint' => '(stand)',
                'form' => 'negative',
            ],
            'Future Perfect Continuous'
        );

        $questions[] = $this->makeQuestion(
            'A1',
            'We {a2} long if the teacher {a1} before noon.',
            [
                'options' => ['comes', 'will come', 'coming'],
                'answer' => 'comes',
                'hint' => '(come)',
            ],
            [
                'options' => ["won't have been studying", 'will have been studying', 'are studying'],
                'answer' => "won't have been studying",
                'hint' => '(study)',
                'form' => 'negative',
            ],
            'Future Perfect Continuous'
        );

        // A2 level — Future Simple (questions)
        $questions[] = $this->makeQuestion(
            'A2',
            'Will the team {a2} the report if the manager {a1} feedback?',
            [
                'options' => ['gives', 'will give', 'is giving'],
                'answer' => 'gives',
                'hint' => '(give)',
            ],
            [
                'options' => ['submit', 'submits', 'submitting'],
                'answer' => 'submit',
                'hint' => '(submit)',
                'form' => 'question',
            ],
            'Future Simple'
        );

        $questions[] = $this->makeQuestion(
            'A2',
            'Will you {a2} the client if the office {a1} open early?',
            [
                'options' => ['opens', 'will open', 'opening'],
                'answer' => 'opens',
                'hint' => '(open)',
            ],
            [
                'options' => ['call', 'calls', 'calling'],
                'answer' => 'call',
                'hint' => '(call)',
                'form' => 'question',
            ],
            'Future Simple'
        );

        $questions[] = $this->makeQuestion(
            'A2',
            'Will the students {a2} the trip if the school {a1} buses?',
            [
                'options' => ['provides', 'will provide', 'is providing'],
                'answer' => 'provides',
                'hint' => '(provide)',
            ],
            [
                'options' => ['take', 'takes', 'taking'],
                'answer' => 'take',
                'hint' => '(take)',
                'form' => 'question',
            ],
            'Future Simple'
        );

        // A2 level — Future Simple (negatives)
        $questions[] = $this->makeQuestion(
            'A2',
            'The team {a2} the report if the manager {a1} late feedback.',
            [
                'options' => ['gives', 'will give', 'gave'],
                'answer' => 'gives',
                'hint' => '(give)',
            ],
            [
                'options' => ["won't submit", 'will submit', 'is submitting'],
                'answer' => "won't submit",
                'hint' => '(submit)',
                'form' => 'negative',
            ],
            'Future Simple'
        );

        $questions[] = $this->makeQuestion(
            'A2',
            'You {a2} the client if the office {a1} closed.',
            [
                'options' => ['stays', 'will stay', 'is staying'],
                'answer' => 'stays',
                'hint' => '(stay)',
            ],
            [
                'options' => ["won't call", 'will call', 'are calling'],
                'answer' => "won't call",
                'hint' => '(call)',
                'form' => 'negative',
            ],
            'Future Simple'
        );

        $questions[] = $this->makeQuestion(
            'A2',
            'The students {a2} the trip if the school {a1} buses.',
            [
                'options' => ['cancels', 'will cancel', 'is cancelling'],
                'answer' => 'cancels',
                'hint' => '(cancel)',
            ],
            [
                'options' => ["won't take", 'will take', 'are taking'],
                'answer' => "won't take",
                'hint' => '(take)',
                'form' => 'negative',
            ],
            'Future Simple'
        );

        // A2 level — Future Continuous (questions)
        $questions[] = $this->makeQuestion(
            'A2',
            'Will the designers be {a2} if the client {a1} new sketches?',
            [
                'options' => ['sends', 'will send', 'is sending'],
                'answer' => 'sends',
                'hint' => '(send)',
            ],
            [
                'options' => ['working', 'work', 'worked'],
                'answer' => 'working',
                'hint' => '(work)',
                'form' => 'question',
            ],
            'Future Continuous'
        );

        $questions[] = $this->makeQuestion(
            'A2',
            'Will you be {a2} notes if the lecturer {a1} slowly?',
            [
                'options' => ['speaks', 'will speak', 'is speaking'],
                'answer' => 'speaks',
                'hint' => '(speak)',
            ],
            [
                'options' => ['taking', 'take', 'took'],
                'answer' => 'taking',
                'hint' => '(take)',
                'form' => 'question',
            ],
            'Future Continuous'
        );

        $questions[] = $this->makeQuestion(
            'A2',
            'Will the volunteers be {a2} if the storm {a1} early?',
            [
                'options' => ['clears', 'will clear', 'is clearing'],
                'answer' => 'clears',
                'hint' => '(clear)',
            ],
            [
                'options' => ['setting up', 'set up', 'setted up'],
                'answer' => 'setting up',
                'hint' => '(set up)',
                'form' => 'question',
            ],
            'Future Continuous'
        );

        // A2 level — Future Continuous (negatives)
        $questions[] = $this->makeQuestion(
            'A2',
            'The designers {a2} if the client {a1} late updates.',
            [
                'options' => ['sends', 'will send', 'sent'],
                'answer' => 'sends',
                'hint' => '(send)',
            ],
            [
                'options' => ["won't be working", "won't work", "aren't working"],
                'answer' => "won't be working",
                'hint' => '(work)',
                'form' => 'negative',
            ],
            'Future Continuous'
        );

        $questions[] = $this->makeQuestion(
            'A2',
            'You {a2} notes if the lecturer {a1} too fast.',
            [
                'options' => ['speaks', 'will speak', 'spoke'],
                'answer' => 'speaks',
                'hint' => '(speak)',
            ],
            [
                'options' => ["won't be taking", "won't take", "aren't taking"],
                'answer' => "won't be taking",
                'hint' => '(take)',
                'form' => 'negative',
            ],
            'Future Continuous'
        );

        $questions[] = $this->makeQuestion(
            'A2',
            'The volunteers {a2} if the storm {a1} all morning.',
            [
                'options' => ['lasts', 'will last', 'lasting'],
                'answer' => 'lasts',
                'hint' => '(last)',
            ],
            [
                'options' => ["won't be setting up", "won't set up", "aren't setting up"],
                'answer' => "won't be setting up",
                'hint' => '(set up)',
                'form' => 'negative',
            ],
            'Future Continuous'
        );

        // A2 level — Future Perfect (questions)
        $questions[] = $this->makeQuestion(
            'A2',
            'Will the editor have {a2} the article if the writer {a1} on time?',
            [
                'options' => ['hands', 'will hand', 'handed'],
                'answer' => 'hands',
                'hint' => '(hand in)',
            ],
            [
                'options' => ['finished', 'finish', 'finishing'],
                'answer' => 'finished',
                'hint' => '(finish)',
                'form' => 'question',
            ],
            'Future Perfect'
        );

        $questions[] = $this->makeQuestion(
            'A2',
            'Will you have {a2} the slides if the meeting {a1} early?',
            [
                'options' => ['starts', 'will start', 'starting'],
                'answer' => 'starts',
                'hint' => '(start)',
            ],
            [
                'options' => ['prepared', 'prepare', 'preparing'],
                'answer' => 'prepared',
                'hint' => '(prepare)',
                'form' => 'question',
            ],
            'Future Perfect'
        );

        $questions[] = $this->makeQuestion(
            'A2',
            'Will the cooks have {a2} the meals if the market {a1} fresh food?',
            [
                'options' => ['brings', 'will bring', 'bringing'],
                'answer' => 'brings',
                'hint' => '(bring)',
            ],
            [
                'options' => ['prepared', 'prepare', 'preparing'],
                'answer' => 'prepared',
                'hint' => '(prepare)',
                'form' => 'question',
            ],
            'Future Perfect'
        );

        // A2 level — Future Perfect (negatives)
        $questions[] = $this->makeQuestion(
            'A2',
            'The editor {a2} the article if the writer {a1} late.',
            [
                'options' => ['hands', 'will hand', 'handed'],
                'answer' => 'hands',
                'hint' => '(hand in)',
            ],
            [
                'options' => ["won't have finished", 'will have finished', 'is finishing'],
                'answer' => "won't have finished",
                'hint' => '(finish)',
                'form' => 'negative',
            ],
            'Future Perfect'
        );

        $questions[] = $this->makeQuestion(
            'A2',
            'You {a2} the slides if the meeting {a1} late.',
            [
                'options' => ['starts', 'will start', 'started'],
                'answer' => 'starts',
                'hint' => '(start)',
            ],
            [
                'options' => ["won't have prepared", 'will have prepared', 'are preparing'],
                'answer' => "won't have prepared",
                'hint' => '(prepare)',
                'form' => 'negative',
            ],
            'Future Perfect'
        );

        $questions[] = $this->makeQuestion(
            'A2',
            'The cooks {a2} the meals if the market {a1} supplies.',
            [
                'options' => ['delays', 'will delay', 'delayed'],
                'answer' => 'delays',
                'hint' => '(delay)',
            ],
            [
                'options' => ["won't have prepared", 'will have prepared', 'are preparing'],
                'answer' => "won't have prepared",
                'hint' => '(prepare)',
                'form' => 'negative',
            ],
            'Future Perfect'
        );

        // A2 level — Future Perfect Continuous (questions)
        $questions[] = $this->makeQuestion(
            'A2',
            'Will the trainees have been {a2} long if the coach {a1} late?',
            [
                'options' => ['arrives', 'will arrive', 'arrived'],
                'answer' => 'arrives',
                'hint' => '(arrive)',
            ],
            [
                'options' => ['waiting', 'wait', 'waited'],
                'answer' => 'waiting',
                'hint' => '(wait)',
                'form' => 'question',
            ],
            'Future Perfect Continuous'
        );

        $questions[] = $this->makeQuestion(
            'A2',
            'Will you have been {a2} long if the bus {a1} late?',
            [
                'options' => ['comes', 'will come', 'coming'],
                'answer' => 'comes',
                'hint' => '(come)',
            ],
            [
                'options' => ['standing', 'stand', 'stood'],
                'answer' => 'standing',
                'hint' => '(stand)',
                'form' => 'question',
            ],
            'Future Perfect Continuous'
        );

        $questions[] = $this->makeQuestion(
            'A2',
            'Will the researchers have been {a2} if the lab {a1} early samples?',
            [
                'options' => ['delivers', 'will deliver', 'delivered'],
                'answer' => 'delivers',
                'hint' => '(deliver)',
            ],
            [
                'options' => ['testing', 'test', 'tested'],
                'answer' => 'testing',
                'hint' => '(test)',
                'form' => 'question',
            ],
            'Future Perfect Continuous'
        );

        // A2 level — Future Perfect Continuous (negatives)
        $questions[] = $this->makeQuestion(
            'A2',
            'The trainees {a2} long if the coach {a1} on time.',
            [
                'options' => ['arrives', 'will arrive', 'arrived'],
                'answer' => 'arrives',
                'hint' => '(arrive)',
            ],
            [
                'options' => ["won't have been waiting", 'will have been waiting', 'are waiting'],
                'answer' => "won't have been waiting",
                'hint' => '(wait)',
                'form' => 'negative',
            ],
            'Future Perfect Continuous'
        );

        $questions[] = $this->makeQuestion(
            'A2',
            'You {a2} long if the bus {a1} quickly.',
            [
                'options' => ['comes', 'will come', 'coming'],
                'answer' => 'comes',
                'hint' => '(come)',
            ],
            [
                'options' => ["won't have been standing", 'will have been standing', 'are standing'],
                'answer' => "won't have been standing",
                'hint' => '(stand)',
                'form' => 'negative',
            ],
            'Future Perfect Continuous'
        );

        $questions[] = $this->makeQuestion(
            'A2',
            'The researchers {a2} if the lab {a1} quickly.',
            [
                'options' => ['delivers', 'will deliver', 'delivered'],
                'answer' => 'delivers',
                'hint' => '(deliver)',
            ],
            [
                'options' => ["won't have been testing", 'will have been testing', 'are testing'],
                'answer' => "won't have been testing",
                'hint' => '(test)',
                'form' => 'negative',
            ],
            'Future Perfect Continuous'
        );

        // B1 level — Future Simple (questions)
        $questions[] = $this->makeQuestion(
            'B1',
            'Will the analysts {a2} a summary if the director {a1} fresh instructions?',
            [
                'options' => ['sends', 'will send', 'is sending'],
                'answer' => 'sends',
                'hint' => '(send)',
            ],
            [
                'options' => ['prepare', 'prepares', 'preparing'],
                'answer' => 'prepare',
                'hint' => '(prepare)',
                'form' => 'question',
            ],
            'Future Simple'
        );

        $questions[] = $this->makeQuestion(
            'B1',
            'Will the committee {a2} a vote if the agenda {a1} ready on time?',
            [
                'options' => ['is', 'will be', 'being'],
                'answer' => 'is',
                'hint' => '(be)',
            ],
            [
                'options' => ['hold', 'holds', 'holding'],
                'answer' => 'hold',
                'hint' => '(hold)',
                'form' => 'question',
            ],
            'Future Simple'
        );

        $questions[] = $this->makeQuestion(
            'B1',
            'Will you {a2} the budget if the finance team {a1} approval?',
            [
                'options' => ['grants', 'will grant', 'is granting'],
                'answer' => 'grants',
                'hint' => '(grant)',
            ],
            [
                'options' => ['revise', 'revises', 'revising'],
                'answer' => 'revise',
                'hint' => '(revise)',
                'form' => 'question',
            ],
            'Future Simple'
        );

        // B1 level — Future Simple (negatives)
        $questions[] = $this->makeQuestion(
            'B1',
            'The analysts {a2} a summary if the director {a1} unclear notes.',
            [
                'options' => ['sends', 'will send', 'sent'],
                'answer' => 'sends',
                'hint' => '(send)',
            ],
            [
                'options' => ["won't prepare", 'will prepare', 'are preparing'],
                'answer' => "won't prepare",
                'hint' => '(prepare)',
                'form' => 'negative',
            ],
            'Future Simple'
        );

        $questions[] = $this->makeQuestion(
            'B1',
            'The committee {a2} a vote if the agenda {a1} incomplete.',
            [
                'options' => ['is', 'will be', 'was'],
                'answer' => 'is',
                'hint' => '(be)',
            ],
            [
                'options' => ["won't hold", 'will hold', 'is holding'],
                'answer' => "won't hold",
                'hint' => '(hold)',
                'form' => 'negative',
            ],
            'Future Simple'
        );

        $questions[] = $this->makeQuestion(
            'B1',
            'You {a2} the budget if the finance team {a1} permission.',
            [
                'options' => ['withholds', 'will withhold', 'withheld'],
                'answer' => 'withholds',
                'hint' => '(withhold)',
            ],
            [
                'options' => ["won't revise", 'will revise', 'are revising'],
                'answer' => "won't revise",
                'hint' => '(revise)',
                'form' => 'negative',
            ],
            'Future Simple'
        );

        // B1 level — Future Continuous (questions)
        $questions[] = $this->makeQuestion(
            'B1',
            'Will the engineers be {a2} if the supplier {a1} the prototype?',
            [
                'options' => ['delivers', 'will deliver', 'is delivering'],
                'answer' => 'delivers',
                'hint' => '(deliver)',
            ],
            [
                'options' => ['testing', 'test', 'tested'],
                'answer' => 'testing',
                'hint' => '(test)',
                'form' => 'question',
            ],
            'Future Continuous'
        );

        $questions[] = $this->makeQuestion(
            'B1',
            'Will the consultants be {a2} if the client {a1} extra sessions?',
            [
                'options' => ['requests', 'will request', 'requested'],
                'answer' => 'requests',
                'hint' => '(request)',
            ],
            [
                'options' => ['presenting', 'present', 'presented'],
                'answer' => 'presenting',
                'hint' => '(present)',
                'form' => 'question',
            ],
            'Future Continuous'
        );

        $questions[] = $this->makeQuestion(
            'B1',
            'Will you be {a2} the database if the system {a1} stable?',
            [
                'options' => ['stays', 'will stay', 'is staying'],
                'answer' => 'stays',
                'hint' => '(stay)',
            ],
            [
                'options' => ['updating', 'update', 'updated'],
                'answer' => 'updating',
                'hint' => '(update)',
                'form' => 'question',
            ],
            'Future Continuous'
        );

        // B1 level — Future Continuous (negatives)
        $questions[] = $this->makeQuestion(
            'B1',
            'The engineers {a2} if the supplier {a1} delays.',
            [
                'options' => ['causes', 'will cause', 'caused'],
                'answer' => 'causes',
                'hint' => '(cause)',
            ],
            [
                'options' => ["won't be testing", "won't test", "aren't testing"],
                'answer' => "won't be testing",
                'hint' => '(test)',
                'form' => 'negative',
            ],
            'Future Continuous'
        );

        $questions[] = $this->makeQuestion(
            'B1',
            'The consultants {a2} if the client {a1} the extra sessions.',
            [
                'options' => ['cancels', 'will cancel', 'cancelled'],
                'answer' => 'cancels',
                'hint' => '(cancel)',
            ],
            [
                'options' => ["won't be presenting", "won't present", "aren't presenting"],
                'answer' => "won't be presenting",
                'hint' => '(present)',
                'form' => 'negative',
            ],
            'Future Continuous'
        );

        $questions[] = $this->makeQuestion(
            'B1',
            'You {a2} the database if the system {a1} errors.',
            [
                'options' => ['shows', 'will show', 'showed'],
                'answer' => 'shows',
                'hint' => '(show)',
            ],
            [
                'options' => ["won't be updating", "won't update", "aren't updating"],
                'answer' => "won't be updating",
                'hint' => '(update)',
                'form' => 'negative',
            ],
            'Future Continuous'
        );

        // B1 level — Future Perfect (questions)
        $questions[] = $this->makeQuestion(
            'B1',
            'Will the researchers have {a2} the data if the samples {a1} early?',
            [
                'options' => ['arrive', 'will arrive', 'arriving'],
                'answer' => 'arrive',
                'hint' => '(arrive)',
            ],
            [
                'options' => ['analysed', 'analyse', 'analysing'],
                'answer' => 'analysed',
                'hint' => '(analyse)',
                'form' => 'question',
            ],
            'Future Perfect'
        );

        $questions[] = $this->makeQuestion(
            'B1',
            'Will the authors have {a2} the draft if the editor {a1} suggestions?',
            [
                'options' => ['sends', 'will send', 'is sending'],
                'answer' => 'sends',
                'hint' => '(send)',
            ],
            [
                'options' => ['revised', 'revise', 'revising'],
                'answer' => 'revised',
                'hint' => '(revise)',
                'form' => 'question',
            ],
            'Future Perfect'
        );

        $questions[] = $this->makeQuestion(
            'B1',
            'Will you have {a2} the keynote if the conference {a1} funding?',
            [
                'options' => ['secures', 'will secure', 'secured'],
                'answer' => 'secures',
                'hint' => '(secure)',
            ],
            [
                'options' => ['prepared', 'prepare', 'preparing'],
                'answer' => 'prepared',
                'hint' => '(prepare)',
                'form' => 'question',
            ],
            'Future Perfect'
        );

        // B1 level — Future Perfect (negatives)
        $questions[] = $this->makeQuestion(
            'B1',
            'The researchers {a2} the data if the samples {a1} late.',
            [
                'options' => ['arrive', 'will arrive', 'arrived'],
                'answer' => 'arrive',
                'hint' => '(arrive)',
            ],
            [
                'options' => ["won't have analysed", 'will have analysed', 'are analysing'],
                'answer' => "won't have analysed",
                'hint' => '(analyse)',
                'form' => 'negative',
            ],
            'Future Perfect'
        );

        $questions[] = $this->makeQuestion(
            'B1',
            'The authors {a2} the draft if the editor {a1} slowly.',
            [
                'options' => ['responds', 'will respond', 'responded'],
                'answer' => 'responds',
                'hint' => '(respond)',
            ],
            [
                'options' => ["won't have revised", 'will have revised', 'are revising'],
                'answer' => "won't have revised",
                'hint' => '(revise)',
                'form' => 'negative',
            ],
            'Future Perfect'
        );

        $questions[] = $this->makeQuestion(
            'B1',
            'You {a2} the keynote if the conference {a1} support.',
            [
                'options' => ['loses', 'will lose', 'lost'],
                'answer' => 'loses',
                'hint' => '(lose)',
            ],
            [
                'options' => ["won't have prepared", 'will have prepared', 'are preparing'],
                'answer' => "won't have prepared",
                'hint' => '(prepare)',
                'form' => 'negative',
            ],
            'Future Perfect'
        );

        // B1 level — Future Perfect Continuous (questions)
        $questions[] = $this->makeQuestion(
            'B1',
            'Will the analysts have been {a2} the metrics if the system {a1} longer hours?',
            [
                'options' => ['runs', 'will run', 'running'],
                'answer' => 'runs',
                'hint' => '(run)',
            ],
            [
                'options' => ['tracking', 'track', 'tracked'],
                'answer' => 'tracking',
                'hint' => '(track)',
                'form' => 'question',
            ],
            'Future Perfect Continuous'
        );

        $questions[] = $this->makeQuestion(
            'B1',
            'Will the interns have been {a2} new code if the mentor {a1} extra tasks?',
            [
                'options' => ['assigns', 'will assign', 'assigned'],
                'answer' => 'assigns',
                'hint' => '(assign)',
            ],
            [
                'options' => ['writing', 'write', 'wrote'],
                'answer' => 'writing',
                'hint' => '(write)',
                'form' => 'question',
            ],
            'Future Perfect Continuous'
        );

        $questions[] = $this->makeQuestion(
            'B1',
            'Will you have been {a2} clients if the company {a1} new leads?',
            [
                'options' => ['sends', 'will send', 'sent'],
                'answer' => 'sends',
                'hint' => '(send)',
            ],
            [
                'options' => ['calling', 'call', 'called'],
                'answer' => 'calling',
                'hint' => '(call)',
                'form' => 'question',
            ],
            'Future Perfect Continuous'
        );

        // B1 level — Future Perfect Continuous (negatives)
        $questions[] = $this->makeQuestion(
            'B1',
            'The analysts {a2} the metrics if the system {a1} downtime.',
            [
                'options' => ['faces', 'will face', 'faced'],
                'answer' => 'faces',
                'hint' => '(face)',
            ],
            [
                'options' => ["won't have been tracking", 'will have been tracking', 'are tracking'],
                'answer' => "won't have been tracking",
                'hint' => '(track)',
                'form' => 'negative',
            ],
            'Future Perfect Continuous'
        );

        $questions[] = $this->makeQuestion(
            'B1',
            'The interns {a2} new code if the mentor {a1} extra tasks.',
            [
                'options' => ['ignores', 'will ignore', 'ignored'],
                'answer' => 'ignores',
                'hint' => '(ignore)',
            ],
            [
                'options' => ["won't have been writing", 'will have been writing', 'are writing'],
                'answer' => "won't have been writing",
                'hint' => '(write)',
                'form' => 'negative',
            ],
            'Future Perfect Continuous'
        );

        $questions[] = $this->makeQuestion(
            'B1',
            'You {a2} clients if the company {a1} new leads.',
            [
                'options' => ['stops', 'will stop', 'stopped'],
                'answer' => 'stops',
                'hint' => '(stop)',
            ],
            [
                'options' => ["won't have been calling", 'will have been calling', 'are calling'],
                'answer' => "won't have been calling",
                'hint' => '(call)',
                'form' => 'negative',
            ],
            'Future Perfect Continuous'
        );

        // B2 level — Future Simple (questions)
        $questions[] = $this->makeQuestion(
            'B2',
            'Will the board {a2} the merger if the regulators {a1} approval?',
            [
                'options' => ['grant', 'will grant', 'granting'],
                'answer' => 'grant',
                'hint' => '(grant)',
            ],
            [
                'options' => ['approve', 'approves', 'approving'],
                'answer' => 'approve',
                'hint' => '(approve)',
                'form' => 'question',
            ],
            'Future Simple'
        );

        $questions[] = $this->makeQuestion(
            'B2',
            'Will the lead scientist {a2} publication if the peer review {a1} supportive?',
            [
                'options' => ['remains', 'will remain', 'remaining'],
                'answer' => 'remains',
                'hint' => '(remain)',
            ],
            [
                'options' => ['pursue', 'pursues', 'pursuing'],
                'answer' => 'pursue',
                'hint' => '(pursue)',
                'form' => 'question',
            ],
            'Future Simple'
        );

        $questions[] = $this->makeQuestion(
            'B2',
            'Will you {a2} the expansion if investors {a1} capital?',
            [
                'options' => ['release', 'will release', 'releasing'],
                'answer' => 'release',
                'hint' => '(release)',
            ],
            [
                'options' => ['initiate', 'initiates', 'initiating'],
                'answer' => 'initiate',
                'hint' => '(initiate)',
                'form' => 'question',
            ],
            'Future Simple'
        );

        // B2 level — Future Simple (negatives)
        $questions[] = $this->makeQuestion(
            'B2',
            'The board {a2} the merger if the regulators {a1} objections.',
            [
                'options' => ['raise', 'will raise', 'raising'],
                'answer' => 'raise',
                'hint' => '(raise)',
            ],
            [
                'options' => ["won't approve", 'will approve', 'are approving'],
                'answer' => "won't approve",
                'hint' => '(approve)',
                'form' => 'negative',
            ],
            'Future Simple'
        );

        $questions[] = $this->makeQuestion(
            'B2',
            'The lead scientist {a2} publication if the peer review {a1} hostile.',
            [
                'options' => ['turns', 'will turn', 'turned'],
                'answer' => 'turns',
                'hint' => '(turn)',
            ],
            [
                'options' => ["won't pursue", 'will pursue', 'is pursuing'],
                'answer' => "won't pursue",
                'hint' => '(pursue)',
                'form' => 'negative',
            ],
            'Future Simple'
        );

        $questions[] = $this->makeQuestion(
            'B2',
            'You {a2} the expansion if investors {a1} hesitant.',
            [
                'options' => ['grow', 'will grow', 'grew'],
                'answer' => 'grow',
                'hint' => '(grow)',
            ],
            [
                'options' => ["won't initiate", 'will initiate', 'are initiating'],
                'answer' => "won't initiate",
                'hint' => '(initiate)',
                'form' => 'negative',
            ],
            'Future Simple'
        );

        // B2 level — Future Continuous (questions)
        $questions[] = $this->makeQuestion(
            'B2',
            'Will the auditors be {a2} the accounts if the firm {a1} detailed records?',
            [
                'options' => ['provides', 'will provide', 'provided'],
                'answer' => 'provides',
                'hint' => '(provide)',
            ],
            [
                'options' => ['reviewing', 'review', 'reviewed'],
                'answer' => 'reviewing',
                'hint' => '(review)',
                'form' => 'question',
            ],
            'Future Continuous'
        );

        $questions[] = $this->makeQuestion(
            'B2',
            'Will the negotiators be {a2} proposals if the council {a1} new demands?',
            [
                'options' => ['issues', 'will issue', 'issued'],
                'answer' => 'issues',
                'hint' => '(issue)',
            ],
            [
                'options' => ['drafting', 'draft', 'drafted'],
                'answer' => 'drafting',
                'hint' => '(draft)',
                'form' => 'question',
            ],
            'Future Continuous'
        );

        $questions[] = $this->makeQuestion(
            'B2',
            'Will you be {a2} the keynote if the sponsor {a1} final confirmation?',
            [
                'options' => ['delivers', 'will deliver', 'delivered'],
                'answer' => 'delivers',
                'hint' => '(deliver)',
            ],
            [
                'options' => ['rehearsing', 'rehearse', 'rehearsed'],
                'answer' => 'rehearsing',
                'hint' => '(rehearse)',
                'form' => 'question',
            ],
            'Future Continuous'
        );

        // B2 level — Future Continuous (negatives)
        $questions[] = $this->makeQuestion(
            'B2',
            'The auditors {a2} the accounts if the firm {a1} disorganised files.',
            [
                'options' => ['keeps', 'will keep', 'kept'],
                'answer' => 'keeps',
                'hint' => '(keep)',
            ],
            [
                'options' => ["won't be reviewing", "won't review", "aren't reviewing"],
                'answer' => "won't be reviewing",
                'hint' => '(review)',
                'form' => 'negative',
            ],
            'Future Continuous'
        );

        $questions[] = $this->makeQuestion(
            'B2',
            'The negotiators {a2} proposals if the council {a1} talks.',
            [
                'options' => ['suspends', 'will suspend', 'suspended'],
                'answer' => 'suspends',
                'hint' => '(suspend)',
            ],
            [
                'options' => ["won't be drafting", "won't draft", "aren't drafting"],
                'answer' => "won't be drafting",
                'hint' => '(draft)',
                'form' => 'negative',
            ],
            'Future Continuous'
        );

        $questions[] = $this->makeQuestion(
            'B2',
            'You {a2} the keynote if the sponsor {a1} support.',
            [
                'options' => ['withdraws', 'will withdraw', 'withdrew'],
                'answer' => 'withdraws',
                'hint' => '(withdraw)',
            ],
            [
                'options' => ["won't be rehearsing", "won't rehearse", "aren't rehearsing"],
                'answer' => "won't be rehearsing",
                'hint' => '(rehearse)',
                'form' => 'negative',
            ],
            'Future Continuous'
        );

        // B2 level — Future Perfect (questions)
        $questions[] = $this->makeQuestion(
            'B2',
            'Will the development team have {a2} the release if the testers {a1} approval?',
            [
                'options' => ['grant', 'will grant', 'granted'],
                'answer' => 'grant',
                'hint' => '(grant)',
            ],
            [
                'options' => ['finalised', 'finalise', 'finalising'],
                'answer' => 'finalised',
                'hint' => '(finalise)',
                'form' => 'question',
            ],
            'Future Perfect'
        );

        $questions[] = $this->makeQuestion(
            'B2',
            'Will the scholars have {a2} their findings if the archive {a1} access?',
            [
                'options' => ['permits', 'will permit', 'permitted'],
                'answer' => 'permits',
                'hint' => '(permit)',
            ],
            [
                'options' => ['published', 'publish', 'publishing'],
                'answer' => 'published',
                'hint' => '(publish)',
                'form' => 'question',
            ],
            'Future Perfect'
        );

        $questions[] = $this->makeQuestion(
            'B2',
            'Will you have {a2} the audit if the software {a1} stable builds?',
            [
                'options' => ['produces', 'will produce', 'produced'],
                'answer' => 'produces',
                'hint' => '(produce)',
            ],
            [
                'options' => ['completed', 'complete', 'completing'],
                'answer' => 'completed',
                'hint' => '(complete)',
                'form' => 'question',
            ],
            'Future Perfect'
        );

        // B2 level — Future Perfect (negatives)
        $questions[] = $this->makeQuestion(
            'B2',
            'The development team {a2} the release if the testers {a1} blocking bugs.',
            [
                'options' => ['report', 'will report', 'reported'],
                'answer' => 'report',
                'hint' => '(report)',
            ],
            [
                'options' => ["won't have finalised", 'will have finalised', 'are finalising'],
                'answer' => "won't have finalised",
                'hint' => '(finalise)',
                'form' => 'negative',
            ],
            'Future Perfect'
        );

        $questions[] = $this->makeQuestion(
            'B2',
            'The scholars {a2} their findings if the archive {a1} restricted.',
            [
                'options' => ['stays', 'will stay', 'stayed'],
                'answer' => 'stays',
                'hint' => '(stay)',
            ],
            [
                'options' => ["won't have published", 'will have published', 'are publishing'],
                'answer' => "won't have published",
                'hint' => '(publish)',
                'form' => 'negative',
            ],
            'Future Perfect'
        );

        $questions[] = $this->makeQuestion(
            'B2',
            'You {a2} the audit if the software {a1} unstable.',
            [
                'options' => ['remains', 'will remain', 'remained'],
                'answer' => 'remains',
                'hint' => '(remain)',
            ],
            [
                'options' => ["won't have completed", 'will have completed', 'are completing'],
                'answer' => "won't have completed",
                'hint' => '(complete)',
                'form' => 'negative',
            ],
            'Future Perfect'
        );

        // B2 level — Future Perfect Continuous (questions)
        $questions[] = $this->makeQuestion(
            'B2',
            'Will the analysts have been {a2} volatility if the market {a1} sharp swings?',
            [
                'options' => ['shows', 'will show', 'showed'],
                'answer' => 'shows',
                'hint' => '(show)',
            ],
            [
                'options' => ['monitoring', 'monitor', 'monitored'],
                'answer' => 'monitoring',
                'hint' => '(monitor)',
                'form' => 'question',
            ],
            'Future Perfect Continuous'
        );

        $questions[] = $this->makeQuestion(
            'B2',
            'Will the medical team have been {a2} the patients if the ward {a1} extra staff?',
            [
                'options' => ['assigns', 'will assign', 'assigned'],
                'answer' => 'assigns',
                'hint' => '(assign)',
            ],
            [
                'options' => ['monitoring', 'monitor', 'monitored'],
                'answer' => 'monitoring',
                'hint' => '(monitor)',
                'form' => 'question',
            ],
            'Future Perfect Continuous'
        );

        $questions[] = $this->makeQuestion(
            'B2',
            'Will you have been {a2} new partners if the accelerator {a1} introductions?',
            [
                'options' => ['arranges', 'will arrange', 'arranged'],
                'answer' => 'arranges',
                'hint' => '(arrange)',
            ],
            [
                'options' => ['cultivating', 'cultivate', 'cultivated'],
                'answer' => 'cultivating',
                'hint' => '(cultivate)',
                'form' => 'question',
            ],
            'Future Perfect Continuous'
        );

        // B2 level — Future Perfect Continuous (negatives)
        $questions[] = $this->makeQuestion(
            'B2',
            'The analysts {a2} volatility if the market {a1} calm.',
            [
                'options' => ['stays', 'will stay', 'stayed'],
                'answer' => 'stays',
                'hint' => '(stay)',
            ],
            [
                'options' => ["won't have been monitoring", 'will have been monitoring', 'are monitoring'],
                'answer' => "won't have been monitoring",
                'hint' => '(monitor)',
                'form' => 'negative',
            ],
            'Future Perfect Continuous'
        );

        $questions[] = $this->makeQuestion(
            'B2',
            'The medical team {a2} the patients if the ward {a1} fewer staff.',
            [
                'options' => ['loses', 'will lose', 'lost'],
                'answer' => 'loses',
                'hint' => '(lose)',
            ],
            [
                'options' => ["won't have been monitoring", 'will have been monitoring', 'are monitoring'],
                'answer' => "won't have been monitoring",
                'hint' => '(monitor)',
                'form' => 'negative',
            ],
            'Future Perfect Continuous'
        );

        $questions[] = $this->makeQuestion(
            'B2',
            'You {a2} new partners if the accelerator {a1} introductions.',
            [
                'options' => ['halts', 'will halt', 'halted'],
                'answer' => 'halts',
                'hint' => '(halt)',
            ],
            [
                'options' => ["won't have been cultivating", 'will have been cultivating', 'are cultivating'],
                'answer' => "won't have been cultivating",
                'hint' => '(cultivate)',
                'form' => 'negative',
            ],
            'Future Perfect Continuous'
        );

        // C1 level — Future Simple (questions)
        $questions[] = $this->makeQuestion(
            'C1',
            'Will the policy committee {a2} reforms if the audit {a1} systemic gaps?',
            [
                'options' => ['identifies', 'will identify', 'identifying'],
                'answer' => 'identifies',
                'hint' => '(identify)',
            ],
            [
                'options' => ['enact', 'enacts', 'enacting'],
                'answer' => 'enact',
                'hint' => '(enact)',
                'form' => 'question',
            ],
            'Future Simple'
        );

        $questions[] = $this->makeQuestion(
            'C1',
            'Will the principal investigator {a2} the grant if the foundation {a1} revised metrics?',
            [
                'options' => ['introduces', 'will introduce', 'introduced'],
                'answer' => 'introduces',
                'hint' => '(introduce)',
            ],
            [
                'options' => ['accept', 'accepts', 'accepting'],
                'answer' => 'accept',
                'hint' => '(accept)',
                'form' => 'question',
            ],
            'Future Simple'
        );

        $questions[] = $this->makeQuestion(
            'C1',
            'Will you {a2} the acquisition if the market {a1} favourable shifts?',
            [
                'options' => ['shows', 'will show', 'showed'],
                'answer' => 'shows',
                'hint' => '(show)',
            ],
            [
                'options' => ['pursue', 'pursues', 'pursuing'],
                'answer' => 'pursue',
                'hint' => '(pursue)',
                'form' => 'question',
            ],
            'Future Simple'
        );

        // C1 level — Future Simple (negatives)
        $questions[] = $this->makeQuestion(
            'C1',
            'The policy committee {a2} reforms if the audit {a1} superficial issues.',
            [
                'options' => ['reveals', 'will reveal', 'revealed'],
                'answer' => 'reveals',
                'hint' => '(reveal)',
            ],
            [
                'options' => ["won't enact", 'will enact', 'is enacting'],
                'answer' => "won't enact",
                'hint' => '(enact)',
                'form' => 'negative',
            ],
            'Future Simple'
        );

        $questions[] = $this->makeQuestion(
            'C1',
            'The principal investigator {a2} the grant if the foundation {a1} prohibitive metrics.',
            [
                'options' => ['imposes', 'will impose', 'imposed'],
                'answer' => 'imposes',
                'hint' => '(impose)',
            ],
            [
                'options' => ["won't accept", 'will accept', 'is accepting'],
                'answer' => "won't accept",
                'hint' => '(accept)',
                'form' => 'negative',
            ],
            'Future Simple'
        );

        $questions[] = $this->makeQuestion(
            'C1',
            'You {a2} the acquisition if the market {a1} volatile signals.',
            [
                'options' => ['sends', 'will send', 'sent'],
                'answer' => 'sends',
                'hint' => '(send)',
            ],
            [
                'options' => ["won't pursue", 'will pursue', 'are pursuing'],
                'answer' => "won't pursue",
                'hint' => '(pursue)',
                'form' => 'negative',
            ],
            'Future Simple'
        );

        // C1 level — Future Continuous (questions)
        $questions[] = $this->makeQuestion(
            'C1',
            'Will the auditors be {a2} compliance if the ministry {a1} urgent directives?',
            [
                'options' => ['issues', 'will issue', 'issued'],
                'answer' => 'issues',
                'hint' => '(issue)',
            ],
            [
                'options' => ['scrutinising', 'scrutinise', 'scrutinised'],
                'answer' => 'scrutinising',
                'hint' => '(scrutinise)',
                'form' => 'question',
            ],
            'Future Continuous'
        );

        $questions[] = $this->makeQuestion(
            'C1',
            'Will the legal team be {a2} amendments if parliament {a1} counterproposals?',
            [
                'options' => ['submits', 'will submit', 'submitted'],
                'answer' => 'submits',
                'hint' => '(submit)',
            ],
            [
                'options' => ['drafting', 'draft', 'drafted'],
                'answer' => 'drafting',
                'hint' => '(draft)',
                'form' => 'question',
            ],
            'Future Continuous'
        );

        $questions[] = $this->makeQuestion(
            'C1',
            'Will you be {a2} stakeholder briefings if the regulator {a1} revised guidance?',
            [
                'options' => ['publishes', 'will publish', 'published'],
                'answer' => 'publishes',
                'hint' => '(publish)',
            ],
            [
                'options' => ['delivering', 'deliver', 'delivered'],
                'answer' => 'delivering',
                'hint' => '(deliver)',
                'form' => 'question',
            ],
            'Future Continuous'
        );

        // C1 level — Future Continuous (negatives)
        $questions[] = $this->makeQuestion(
            'C1',
            'The auditors {a2} compliance if the ministry {a1} silent.',
            [
                'options' => ['remains', 'will remain', 'remained'],
                'answer' => 'remains',
                'hint' => '(remain)',
            ],
            [
                'options' => ["won't be scrutinising", "won't scrutinise", "aren't scrutinising"],
                'answer' => "won't be scrutinising",
                'hint' => '(scrutinise)',
                'form' => 'negative',
            ],
            'Future Continuous'
        );

        $questions[] = $this->makeQuestion(
            'C1',
            'The legal team {a2} amendments if parliament {a1} the bill.',
            [
                'options' => ['withdraws', 'will withdraw', 'withdrew'],
                'answer' => 'withdraws',
                'hint' => '(withdraw)',
            ],
            [
                'options' => ["won't be drafting", "won't draft", "aren't drafting"],
                'answer' => "won't be drafting",
                'hint' => '(draft)',
                'form' => 'negative',
            ],
            'Future Continuous'
        );

        $questions[] = $this->makeQuestion(
            'C1',
            'You {a2} stakeholder briefings if the regulator {a1} delays guidance.',
            [
                'options' => ['delays', 'will delay', 'delayed'],
                'answer' => 'delays',
                'hint' => '(delay)',
            ],
            [
                'options' => ["won't be delivering", "won't deliver", "aren't delivering"],
                'answer' => "won't be delivering",
                'hint' => '(deliver)',
                'form' => 'negative',
            ],
            'Future Continuous'
        );

        // C1 level — Future Perfect (questions)
        $questions[] = $this->makeQuestion(
            'C1',
            'Will the commission have {a2} the report if the inquiry {a1} classified files?',
            [
                'options' => ['unlocks', 'will unlock', 'unlocked'],
                'answer' => 'unlocks',
                'hint' => '(unlock)',
            ],
            [
                'options' => ['compiled', 'compile', 'compiling'],
                'answer' => 'compiled',
                'hint' => '(compile)',
                'form' => 'question',
            ],
            'Future Perfect'
        );

        $questions[] = $this->makeQuestion(
            'C1',
            'Will the consortium have {a2} agreements if the mediator {a1} consensus?',
            [
                'options' => ['builds', 'will build', 'built'],
                'answer' => 'builds',
                'hint' => '(build)',
            ],
            [
                'options' => ['ratified', 'ratify', 'ratifying'],
                'answer' => 'ratified',
                'hint' => '(ratify)',
                'form' => 'question',
            ],
            'Future Perfect'
        );

        $questions[] = $this->makeQuestion(
            'C1',
            'Will you have {a2} the strategic brief if the board {a1} final mandates?',
            [
                'options' => ['issues', 'will issue', 'issued'],
                'answer' => 'issues',
                'hint' => '(issue)',
            ],
            [
                'options' => ['completed', 'complete', 'completing'],
                'answer' => 'completed',
                'hint' => '(complete)',
                'form' => 'question',
            ],
            'Future Perfect'
        );

        // C1 level — Future Perfect (negatives)
        $questions[] = $this->makeQuestion(
            'C1',
            'The commission {a2} the report if the inquiry {a1} the files.',
            [
                'options' => ['withholds', 'will withhold', 'withheld'],
                'answer' => 'withholds',
                'hint' => '(withhold)',
            ],
            [
                'options' => ["won't have compiled", 'will have compiled', 'are compiling'],
                'answer' => "won't have compiled",
                'hint' => '(compile)',
                'form' => 'negative',
            ],
            'Future Perfect'
        );

        $questions[] = $this->makeQuestion(
            'C1',
            'The consortium {a2} agreements if the mediator {a1} consensus.',
            [
                'options' => ['fails', 'will fail', 'failed'],
                'answer' => 'fails',
                'hint' => '(fail)',
            ],
            [
                'options' => ["won't have ratified", 'will have ratified', 'are ratifying'],
                'answer' => "won't have ratified",
                'hint' => '(ratify)',
                'form' => 'negative',
            ],
            'Future Perfect'
        );

        $questions[] = $this->makeQuestion(
            'C1',
            'You {a2} the strategic brief if the board {a1} mandates.',
            [
                'options' => ['postpones', 'will postpone', 'postponed'],
                'answer' => 'postpones',
                'hint' => '(postpone)',
            ],
            [
                'options' => ["won't have completed", 'will have completed', 'are completing'],
                'answer' => "won't have completed",
                'hint' => '(complete)',
                'form' => 'negative',
            ],
            'Future Perfect'
        );

        // C1 level — Future Perfect Continuous (questions)
        $questions[] = $this->makeQuestion(
            'C1',
            'Will the economists have been {a2} indicators if the ministry {a1} transparent data?',
            [
                'options' => ['releases', 'will release', 'released'],
                'answer' => 'releases',
                'hint' => '(release)',
            ],
            [
                'options' => ['tracking', 'track', 'tracked'],
                'answer' => 'tracking',
                'hint' => '(track)',
                'form' => 'question',
            ],
            'Future Perfect Continuous'
        );

        $questions[] = $this->makeQuestion(
            'C1',
            'Will the negotiation team have been {a2} scenarios if stakeholders {a1} new constraints?',
            [
                'options' => ['outline', 'will outline', 'outlined'],
                'answer' => 'outline',
                'hint' => '(outline)',
            ],
            [
                'options' => ['modelling', 'model', 'modelled'],
                'answer' => 'modelling',
                'hint' => '(model)',
                'form' => 'question',
            ],
            'Future Perfect Continuous'
        );

        $questions[] = $this->makeQuestion(
            'C1',
            'Will you have been {a2} coalition partners if the alliance {a1} progress?',
            [
                'options' => ['reports', 'will report', 'reported'],
                'answer' => 'reports',
                'hint' => '(report)',
            ],
            [
                'options' => ['cultivating', 'cultivate', 'cultivated'],
                'answer' => 'cultivating',
                'hint' => '(cultivate)',
                'form' => 'question',
            ],
            'Future Perfect Continuous'
        );

        // C1 level — Future Perfect Continuous (negatives)
        $questions[] = $this->makeQuestion(
            'C1',
            'The economists {a2} indicators if the ministry {a1} opaque data.',
            [
                'options' => ['publishes', 'will publish', 'published'],
                'answer' => 'publishes',
                'hint' => '(publish)',
            ],
            [
                'options' => ["won't have been tracking", 'will have been tracking', 'are tracking'],
                'answer' => "won't have been tracking",
                'hint' => '(track)',
                'form' => 'negative',
            ],
            'Future Perfect Continuous'
        );

        $questions[] = $this->makeQuestion(
            'C1',
            'The negotiation team {a2} scenarios if stakeholders {a1} silence.',
            [
                'options' => ['maintain', 'will maintain', 'maintained'],
                'answer' => 'maintain',
                'hint' => '(maintain)',
            ],
            [
                'options' => ["won't have been modelling", 'will have been modelling', 'are modelling'],
                'answer' => "won't have been modelling",
                'hint' => '(model)',
                'form' => 'negative',
            ],
            'Future Perfect Continuous'
        );

        $questions[] = $this->makeQuestion(
            'C1',
            'You {a2} coalition partners if the alliance {a1} stagnation.',
            [
                'options' => ['signals', 'will signal', 'signalled'],
                'answer' => 'signals',
                'hint' => '(signal)',
            ],
            [
                'options' => ["won't have been cultivating", 'will have been cultivating', 'are cultivating'],
                'answer' => "won't have been cultivating",
                'hint' => '(cultivate)',
                'form' => 'negative',
            ],
            'Future Perfect Continuous'
        );

        // C2 level — Future Simple (questions)
        $questions[] = $this->makeQuestion(
            'C2',
            'Will the international tribunal {a2} sanctions if the committee {a1} irrefutable evidence?',
            [
                'options' => ['presents', 'will present', 'presented'],
                'answer' => 'presents',
                'hint' => '(present)',
            ],
            [
                'options' => ['impose', 'imposes', 'imposing'],
                'answer' => 'impose',
                'hint' => '(impose)',
                'form' => 'question',
            ],
            'Future Simple'
        );

        $questions[] = $this->makeQuestion(
            'C2',
            'Will the chief negotiator {a2} the accord if the counterpart {a1} substantive concessions?',
            [
                'options' => ['offers', 'will offer', 'offered'],
                'answer' => 'offers',
                'hint' => '(offer)',
            ],
            [
                'options' => ['endorse', 'endorses', 'endorsing'],
                'answer' => 'endorse',
                'hint' => '(endorse)',
                'form' => 'question',
            ],
            'Future Simple'
        );

        $questions[] = $this->makeQuestion(
            'C2',
            'Will you {a2} the manifesto if the coalition {a1} unified priorities?',
            [
                'options' => ['adopts', 'will adopt', 'adopted'],
                'answer' => 'adopts',
                'hint' => '(adopt)',
            ],
            [
                'options' => ['publish', 'publishes', 'publishing'],
                'answer' => 'publish',
                'hint' => '(publish)',
                'form' => 'question',
            ],
            'Future Simple'
        );

        // C2 level — Future Simple (negatives)
        $questions[] = $this->makeQuestion(
            'C2',
            'The international tribunal {a2} sanctions if the committee {a1} ambiguous evidence.',
            [
                'options' => ['delivers', 'will deliver', 'delivered'],
                'answer' => 'delivers',
                'hint' => '(deliver)',
            ],
            [
                'options' => ["won't impose", 'will impose', 'is imposing'],
                'answer' => "won't impose",
                'hint' => '(impose)',
                'form' => 'negative',
            ],
            'Future Simple'
        );

        $questions[] = $this->makeQuestion(
            'C2',
            'The chief negotiator {a2} the accord if the counterpart {a1} symbolic gestures.',
            [
                'options' => ['offers', 'will offer', 'offered'],
                'answer' => 'offers',
                'hint' => '(offer)',
            ],
            [
                'options' => ["won't endorse", 'will endorse', 'is endorsing'],
                'answer' => "won't endorse",
                'hint' => '(endorse)',
                'form' => 'negative',
            ],
            'Future Simple'
        );

        $questions[] = $this->makeQuestion(
            'C2',
            'You {a2} the manifesto if the coalition {a1} fractured priorities.',
            [
                'options' => ['retains', 'will retain', 'retained'],
                'answer' => 'retains',
                'hint' => '(retain)',
            ],
            [
                'options' => ["won't publish", 'will publish', 'are publishing'],
                'answer' => "won't publish",
                'hint' => '(publish)',
                'form' => 'negative',
            ],
            'Future Simple'
        );

        // C2 level — Future Continuous (questions)
        $questions[] = $this->makeQuestion(
            'C2',
            'Will the investigative panel be {a2} testimony if the court {a1} closed-door hearings?',
            [
                'options' => ['convenes', 'will convene', 'convened'],
                'answer' => 'convenes',
                'hint' => '(convene)',
            ],
            [
                'options' => ['examining', 'examine', 'examined'],
                'answer' => 'examining',
                'hint' => '(examine)',
                'form' => 'question',
            ],
            'Future Continuous'
        );

        $questions[] = $this->makeQuestion(
            'C2',
            'Will the crisis unit be {a2} evacuations if the embassy {a1} emergency alerts?',
            [
                'options' => ['issues', 'will issue', 'issued'],
                'answer' => 'issues',
                'hint' => '(issue)',
            ],
            [
                'options' => ['coordinating', 'coordinate', 'coordinated'],
                'answer' => 'coordinating',
                'hint' => '(coordinate)',
                'form' => 'question',
            ],
            'Future Continuous'
        );

        $questions[] = $this->makeQuestion(
            'C2',
            'Will you be {a2} expert panels if the agency {a1} urgent mandates?',
            [
                'options' => ['delivers', 'will deliver', 'delivered'],
                'answer' => 'delivers',
                'hint' => '(deliver)',
            ],
            [
                'options' => ['assembling', 'assemble', 'assembled'],
                'answer' => 'assembling',
                'hint' => '(assemble)',
                'form' => 'question',
            ],
            'Future Continuous'
        );

        // C2 level — Future Continuous (negatives)
        $questions[] = $this->makeQuestion(
            'C2',
            'The investigative panel {a2} testimony if the court {a1} proceedings.',
            [
                'options' => ['suspends', 'will suspend', 'suspended'],
                'answer' => 'suspends',
                'hint' => '(suspend)',
            ],
            [
                'options' => ["won't be examining", "won't examine", "aren't examining"],
                'answer' => "won't be examining",
                'hint' => '(examine)',
                'form' => 'negative',
            ],
            'Future Continuous'
        );

        $questions[] = $this->makeQuestion(
            'C2',
            'The crisis unit {a2} evacuations if the embassy {a1} warnings.',
            [
                'options' => ['rescinds', 'will rescind', 'rescinded'],
                'answer' => 'rescinds',
                'hint' => '(rescind)',
            ],
            [
                'options' => ["won't be coordinating", "won't coordinate", "aren't coordinating"],
                'answer' => "won't be coordinating",
                'hint' => '(coordinate)',
                'form' => 'negative',
            ],
            'Future Continuous'
        );

        $questions[] = $this->makeQuestion(
            'C2',
            'You {a2} expert panels if the agency {a1} mandates.',
            [
                'options' => ['delays', 'will delay', 'delayed'],
                'answer' => 'delays',
                'hint' => '(delay)',
            ],
            [
                'options' => ["won't be assembling", "won't assemble", "aren't assembling"],
                'answer' => "won't be assembling",
                'hint' => '(assemble)',
                'form' => 'negative',
            ],
            'Future Continuous'
        );

        // C2 level — Future Perfect (questions)
        $questions[] = $this->makeQuestion(
            'C2',
            'Will the oversight board have {a2} the ruling if the panel {a1} encrypted transcripts?',
            [
                'options' => ['releases', 'will release', 'released'],
                'answer' => 'releases',
                'hint' => '(release)',
            ],
            [
                'options' => ['finalised', 'finalise', 'finalising'],
                'answer' => 'finalised',
                'hint' => '(finalise)',
                'form' => 'question',
            ],
            'Future Perfect'
        );

        $questions[] = $this->makeQuestion(
            'C2',
            'Will the diplomatic corps have {a2} accords if the summit {a1} consensus?',
            [
                'options' => ['secures', 'will secure', 'secured'],
                'answer' => 'secures',
                'hint' => '(secure)',
            ],
            [
                'options' => ['brokered', 'broker', 'brokering'],
                'answer' => 'brokered',
                'hint' => '(broker)',
                'form' => 'question',
            ],
            'Future Perfect'
        );

        $questions[] = $this->makeQuestion(
            'C2',
            'Will you have {a2} the policy blueprint if the legislature {a1} final readings?',
            [
                'options' => ['completes', 'will complete', 'completed'],
                'answer' => 'completes',
                'hint' => '(complete)',
            ],
            [
                'options' => ['prepared', 'prepare', 'preparing'],
                'answer' => 'prepared',
                'hint' => '(prepare)',
                'form' => 'question',
            ],
            'Future Perfect'
        );

        // C2 level — Future Perfect (negatives)
        $questions[] = $this->makeQuestion(
            'C2',
            'The oversight board {a2} the ruling if the panel {a1} transcripts.',
            [
                'options' => ['withholds', 'will withhold', 'withheld'],
                'answer' => 'withholds',
                'hint' => '(withhold)',
            ],
            [
                'options' => ["won't have finalised", 'will have finalised', 'are finalising'],
                'answer' => "won't have finalised",
                'hint' => '(finalise)',
                'form' => 'negative',
            ],
            'Future Perfect'
        );

        $questions[] = $this->makeQuestion(
            'C2',
            'The diplomatic corps {a2} accords if the summit {a1} consensus.',
            [
                'options' => ['evades', 'will evade', 'evaded'],
                'answer' => 'evades',
                'hint' => '(evade)',
            ],
            [
                'options' => ["won't have brokered", 'will have brokered', 'are brokering'],
                'answer' => "won't have brokered",
                'hint' => '(broker)',
                'form' => 'negative',
            ],
            'Future Perfect'
        );

        $questions[] = $this->makeQuestion(
            'C2',
            'You {a2} the policy blueprint if the legislature {a1} debate.',
            [
                'options' => ['stalls', 'will stall', 'stalled'],
                'answer' => 'stalls',
                'hint' => '(stall)',
            ],
            [
                'options' => ["won't have prepared", 'will have prepared', 'are preparing'],
                'answer' => "won't have prepared",
                'hint' => '(prepare)',
                'form' => 'negative',
            ],
            'Future Perfect'
        );

        // C2 level — Future Perfect Continuous (questions)
        $questions[] = $this->makeQuestion(
            'C2',
            'Will the humanitarian task force have been {a2} aid routes if the border {a1} clearances?',
            [
                'options' => ['grants', 'will grant', 'granted'],
                'answer' => 'grants',
                'hint' => '(grant)',
            ],
            [
                'options' => ['mapping', 'map', 'mapped'],
                'answer' => 'mapping',
                'hint' => '(map)',
                'form' => 'question',
            ],
            'Future Perfect Continuous'
        );

        $questions[] = $this->makeQuestion(
            'C2',
            'Will the ethics board have been {a2} disclosures if the consortium {a1} sensitive data?',
            [
                'options' => ['shares', 'will share', 'shared'],
                'answer' => 'shares',
                'hint' => '(share)',
            ],
            [
                'options' => ['scrutinising', 'scrutinise', 'scrutinised'],
                'answer' => 'scrutinising',
                'hint' => '(scrutinise)',
                'form' => 'question',
            ],
            'Future Perfect Continuous'
        );

        $questions[] = $this->makeQuestion(
            'C2',
            'Will you have been {a2} diplomatic channels if the envoy {a1} overtures?',
            [
                'options' => ['extends', 'will extend', 'extended'],
                'answer' => 'extends',
                'hint' => '(extend)',
            ],
            [
                'options' => ['cultivating', 'cultivate', 'cultivated'],
                'answer' => 'cultivating',
                'hint' => '(cultivate)',
                'form' => 'question',
            ],
            'Future Perfect Continuous'
        );

        // C2 level — Future Perfect Continuous (negatives)
        $questions[] = $this->makeQuestion(
            'C2',
            'The humanitarian task force {a2} aid routes if the border {a1} permissions.',
            [
                'options' => ['denies', 'will deny', 'denied'],
                'answer' => 'denies',
                'hint' => '(deny)',
            ],
            [
                'options' => ["won't have been mapping", 'will have been mapping', 'are mapping'],
                'answer' => "won't have been mapping",
                'hint' => '(map)',
                'form' => 'negative',
            ],
            'Future Perfect Continuous'
        );

        $questions[] = $this->makeQuestion(
            'C2',
            'The ethics board {a2} disclosures if the consortium {a1} cooperation.',
            [
                'options' => ['refuses', 'will refuse', 'refused'],
                'answer' => 'refuses',
                'hint' => '(refuse)',
            ],
            [
                'options' => ["won't have been scrutinising", 'will have been scrutinising', 'are scrutinising'],
                'answer' => "won't have been scrutinising",
                'hint' => '(scrutinise)',
                'form' => 'negative',
            ],
            'Future Perfect Continuous'
        );

        $questions[] = $this->makeQuestion(
            'C2',
            'You {a2} diplomatic channels if the envoy {a1} overtures.',
            [
                'options' => ['withdraws', 'will withdraw', 'withdrew'],
                'answer' => 'withdraws',
                'hint' => '(withdraw)',
            ],
            [
                'options' => ["won't have been cultivating", 'will have been cultivating', 'are cultivating'],
                'answer' => "won't have been cultivating",
                'hint' => '(cultivate)',
                'form' => 'negative',
            ],
            'Future Perfect Continuous'
        );

        // Additional levels will be appended below.

        return $questions;
    }

    private function makeQuestion(string $level, string $question, array $condition, array $result, string $tense): array
    {
        return [
            'question' => $question,
            'level' => $level,
            'tense_tags' => ['First Conditional', $tense],
            'markers' => [
                'a1' => [
                    'options' => $condition['options'],
                    'answer' => $condition['answer'],
                    'hint' => $condition['hint'] ?? null,
                    'meta' => ['role' => 'condition'],
                ],
                'a2' => [
                    'options' => $result['options'],
                    'answer' => $result['answer'],
                    'hint' => $result['hint'] ?? null,
                    'meta' => [
                        'role' => 'result',
                        'tense' => $tense,
                        'form' => $result['form'] ?? 'question',
                    ],
                ],
            ],
        ];
    }

    private function buildHintForClause(array $meta, ?string $verbHint, string $example): string
    {
        $description = $this->describeStructure($meta);

        if ($verbHint) {
            $description .= ' Дієслово: ' . $verbHint . '.';
        }

        return $description . ' Приклад: *' . $example . '*.';
    }

    private function buildExplanationsForClause(array $meta, array $options, string $answer, string $example): array
    {
        $structure = $this->describeStructure($meta);

        $result = [];
        foreach ($options as $option) {
            if ($option === $answer) {
                $result[$option] = '✅ «' . $option . '» — відповідає структурі: ' . $structure . '. Приклад: *' . $example . '*.';
                continue;
            }

            $result[$option] = '❌ «' . $option . '» не відповідає структурі: ' . $structure . '. Зверни увагу на приклад: *' . $example . '*.';
        }

        return $result;
    }

    private function describeStructure(array $meta): string
    {
        if (($meta['role'] ?? 'condition') === 'condition') {
            return 'If-clause → Present Simple без will';
        }

        $tense = $meta['tense'] ?? 'Future Simple';
        $form = $meta['form'] ?? 'question';

        return match ($tense) {
            'Future Continuous' => $form === 'negative'
                ? 'Main clause → Future Continuous (won\'t be + V-ing)'
                : 'Main clause → Future Continuous (will + підмет + be + V-ing)',
            'Future Perfect' => $form === 'negative'
                ? 'Main clause → Future Perfect (won\'t have + V3)'
                : 'Main clause → Future Perfect (will + підмет + have + V3)',
            'Future Perfect Continuous' => $form === 'negative'
                ? 'Main clause → Future Perfect Continuous (won\'t have been + V-ing)'
                : 'Main clause → Future Perfect Continuous (will + підмет + have been + V-ing)',
            default => $form === 'negative'
                ? 'Main clause → Future Simple (won\'t + V1)'
                : 'Main clause → Future Simple (will + підмет + V1)',
        };
    }

    private function prepareOptionSets(array $options, array $answers): array
    {
        if ($this->isAssoc($options)) {
            $result = [];
            foreach ($options as $marker => $choices) {
                $result[$marker] = array_map(fn ($value) => $this->normalizeValue((string) $value), (array) $choices);
            }

            return $result;
        }

        $marker = array_key_first($answers);
        if ($marker === null) {
            return [];
        }

        return [
            $marker => array_map(fn ($value) => $this->normalizeValue((string) $value), $options),
        ];
    }

    private function flattenOptions(array $optionSets): array
    {
        $all = [];
        foreach ($optionSets as $options) {
            foreach ($options as $option) {
                $all[] = $option;
            }
        }

        return array_values(array_unique($all));
    }

    private function buildExample(string $question, array $answers): string
    {
        $result = $question;
        foreach ($answers as $marker => $answer) {
            $result = str_replace('{' . $marker . '}', $answer, $result);
        }

        $result = preg_replace('/\s+/', ' ', trim($result));

        $first = mb_substr($result, 0, 1, 'UTF-8');
        $rest = mb_substr($result, 1, null, 'UTF-8');

        return mb_strtoupper($first, 'UTF-8') . $rest;
    }

    private function normalizeValue(string $value): string
    {
        $value = str_replace(['’', '‘', '‛', 'ʻ'], "'", $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }

    private function isAssoc(array $array): bool
    {
        if ($array === []) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }
}
