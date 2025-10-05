<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Source;
use App\Models\Tag;
use App\Services\QuestionSeedingService;
use Database\Seeders\QuestionSeeder;

class SecondConditionalTestV2Seeder extends QuestionSeeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Conditionals'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Custom: Second Conditional Test V2'])->id;

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Second Conditional Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Second Conditional Sentence Completion'],
            ['category' => 'English Grammar Detail']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'Second Conditional Sentences'],
            ['category' => 'English Grammar Structure']
        )->id;

        $tenseTagId = Tag::firstOrCreate(
            ['name' => 'Second Conditional'],
            ['category' => 'Tenses']
        )->id;

        $levelDifficulty = [
            'A1' => 1,
            'A2' => 2,
            'B1' => 3,
            'B2' => 4,
            'C1' => 5,
            'C2' => 5,
        ];

        $sharedHints = [
            'if_clause' => "Second Conditional (if-клауза) = **if + Past Simple**. Використовуємо для уявних або малоймовірних ситуацій зараз. Приклад: *If she had more time, she would travel.*",
            'result_clause' => "Результат другого умовного = **would + V1** (без to). Це показує наслідок уявної ситуації. Приклад: *He would buy a new car.*",
        ];

        $questions = [
            [
                'question' => 'If I {a1} a new job, I {a2} very happy.',
                'verb_hint' => ['a1' => '(get)', 'a2' => '(be)'],
                'options' => [
                    'a1' => ['got', 'get', 'gets', 'had got'],
                    'a2' => ['would be', 'will be', 'am', 'was'],
                ],
                'answers' => ['a1' => 'got', 'a2' => 'would be'],
                'explanations' => [
                    'got' => "✅ «got» правильно, бо в if-клаузі другого умовного потрібен Past Simple для нереальної теперішньої ситуації.",
                    'get' => "❌ «get» — Present Simple; він уживається у Zero або First Conditional, але не в Second Conditional.",
                    'gets' => "❌ «gets» — Present Simple з -s, що не підходить для уявної умови у Second Conditional.",
                    'had got' => "❌ «had got» — Past Perfect, який характерний для Third Conditional.",
                    'would be' => "✅ «would be» правильно, адже наслідок у другому умовному будуємо як would + базове дієслово.",
                    'will be' => "❌ «will be» створює First Conditional (реальна умова), а не уявну ситуацію.",
                    'am' => "❌ «am» — Present Simple, він не передає уявний результат.",
                    'was' => "❌ «was» бракує модального would, тому не відповідає формулі результату Second Conditional.",
                ],
                'hints' => [
                    'a1' => $sharedHints['if_clause'],
                    'a2' => $sharedHints['result_clause'],
                ],
                'tense' => ['Second Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'If I {a1} younger, I {a2} more.',
                'verb_hint' => ['a1' => '(be)', 'a2' => '(travel)'],
                'options' => [
                    'a1' => ['were', 'was', 'are', 'would be'],
                    'a2' => ['would travel', 'will travel', 'travel', 'would be travelling'],
                ],
                'answers' => ['a1' => 'were', 'a2' => 'would travel'],
                'explanations' => [
                    'were' => "✅ «were» — правильна форма Past Simple для «I» у другому умовному (вживаємо were для всіх осіб).",
                    'was' => "❌ «was» звучить розмовно, але граматично у Second Conditional вживаємо were для всіх осіб.",
                    'are' => "❌ «are» — Present Simple; він не показує уявну ситуацію.",
                    'would be' => "❌ «would be» належить до результату, а не до if-клауза.",
                    'would travel' => "✅ «would travel» правильно, бо наслідок утворюємо через would + V1.",
                    'will travel' => "❌ «will travel» — First Conditional, а не другий.",
                    'travel' => "❌ «travel» без would — це Present Simple, який не виражає уявний результат.",
                    'would be travelling' => "❌ «would be travelling» — форма Continuous; вона не відповідає простому результату з would + V1 у тесті.",
                ],
                'hints' => [
                    'a1' => $sharedHints['if_clause'],
                    'a2' => $sharedHints['result_clause'],
                ],
                'tense' => ['Second Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'If Sally {a1} more, she {a2} a better grade.',
                'verb_hint' => ['a1' => '(study)', 'a2' => '(have)'],
                'options' => [
                    'a1' => ['studied', 'study', 'studies', 'had studied'],
                    'a2' => ['would have', 'will have', 'have', 'would be having'],
                ],
                'answers' => ['a1' => 'studied', 'a2' => 'would have'],
                'explanations' => [
                    'studied' => "✅ «studied» — Past Simple, якого вимагає if-клауза другого умовного.",
                    'study' => "❌ «study» — Present Simple; потрібна форма Past Simple.",
                    'studies' => "❌ «studies» — Present Simple для третьої особи, що не підходить для Second Conditional.",
                    'had studied' => "❌ «had studied» — Past Perfect для Third Conditional, а не Second.",
                    'would have' => "✅ «would have» — правильний результат: would + базова форма «have».",
                    'will have' => "❌ «will have» формує First Conditional про реальні події.",
                    'have' => "❌ «have» без would — це Present Simple.",
                    'would be having' => "❌ «would be having» — форма Continuous; у завданні потрібен простий результат.",
                ],
                'hints' => [
                    'a1' => $sharedHints['if_clause'],
                    'a2' => $sharedHints['result_clause'],
                ],
                'tense' => ['Second Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'If I {a1} a new car, I {a2} a bigger one.',
                'verb_hint' => ['a1' => '(buy)', 'a2' => '(buy)'],
                'options' => [
                    'a1' => ['bought', 'buy', 'buys', 'had bought'],
                    'a2' => ['would buy', 'will buy', 'would be buying', 'would have bought'],
                ],
                'answers' => ['a1' => 'bought', 'a2' => 'would buy'],
                'explanations' => [
                    'bought' => "✅ «bought» — Past Simple від «buy», що потрібен у if-клаузі.",
                    'buy' => "❌ «buy» — Present Simple; не показує уявну ситуацію.",
                    'buys' => "❌ «buys» — Present Simple третьої особи.",
                    'had bought' => "❌ «had bought» — Past Perfect, характерний для Third Conditional.",
                    'would buy' => "✅ «would buy» — правильний результат з would + V1.",
                    'will buy' => "❌ «will buy» — конструкція для реальної майбутньої умови (First Conditional).",
                    'would be buying' => "❌ «would be buying» — форма Continuous; тут потрібен простий результат.",
                    'would have bought' => "❌ «would have bought» — форма Third Conditional для минулого результату.",
                ],
                'hints' => [
                    'a1' => $sharedHints['if_clause'],
                    'a2' => $sharedHints['result_clause'],
                ],
                'tense' => ['Second Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'If Amélie {a1} buy a new super power, she {a2} super human strength.',
                'verb_hint' => ['a1' => '(can)', 'a2' => '(have)'],
                'options' => [
                    'a1' => ['could', 'can', 'was able', 'would can'],
                    'a2' => ['would have', 'will have', 'has', 'would has'],
                ],
                'answers' => ['a1' => 'could', 'a2' => 'would have'],
                'explanations' => [
                    'could' => "✅ «could» — минула форма can, яку використовуємо для можливості в уявній умові.",
                    'can' => "❌ «can» — теперішня форма; не відповідає другому умовному.",
                    'was able' => "❌ «was able» — Past Simple, але без інфінітива «to buy» не працює в цій позиції.",
                    'would can' => "❌ «would can» — некоректна комбінація модальних дієслів.",
                    'would have' => "✅ «would have» — правильний результат з would + базова форма.",
                    'will have' => "❌ «will have» — First Conditional про реальні події.",
                    'has' => "❌ «has» — Present Simple.",
                    'would has' => "❌ «would has» — неправильна форма після would; потрібно базове дієслово без -s.",
                ],
                'hints' => [
                    'a1' => "У другому умовному модальне «can» перетворюється на Past Simple «could». Приклад: *If I could fly, I would visit you.*",
                    'a2' => $sharedHints['result_clause'],
                ],
                'tense' => ['Second Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'If he {a1} the lottery, he {a2} the world.',
                'verb_hint' => ['a1' => '(win)', 'a2' => '(travel)'],
                'options' => [
                    'a1' => ['won', 'win', 'wins', 'had won'],
                    'a2' => ['would travel', 'will travel', 'travels', 'would be travelling'],
                ],
                'answers' => ['a1' => 'won', 'a2' => 'would travel'],
                'explanations' => [
                    'won' => "✅ «won» — Past Simple, якого потребує if-клауза.",
                    'win' => "❌ «win» — Present Simple.",
                    'wins' => "❌ «wins» — Present Simple третьої особи.",
                    'had won' => "❌ «had won» — Past Perfect для Third Conditional.",
                    'would travel' => "✅ «would travel» — правильний результат з would + V1.",
                    'will travel' => "❌ «will travel» — реальна умова (First Conditional).",
                    'travels' => "❌ «travels» — Present Simple.",
                    'would be travelling' => "❌ «would be travelling» — Continuous; у вправах потрібно просту форму.",
                ],
                'hints' => [
                    'a1' => $sharedHints['if_clause'],
                    'a2' => $sharedHints['result_clause'],
                ],
                'tense' => ['Second Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'If we {a1} a new car, we {a2} to drive to the beach every day.',
                'verb_hint' => ['a1' => '(have)', 'a2' => '(be able)'],
                'options' => [
                    'a1' => ['had', 'have', 'has', 'would have'],
                    'a2' => ['would be able', 'will be able', 'are able', 'would have been able'],
                ],
                'answers' => ['a1' => 'had', 'a2' => 'would be able'],
                'explanations' => [
                    'had' => "✅ «had» — Past Simple від «have» для уявної умови.",
                    'have' => "❌ «have» — Present Simple.",
                    'has' => "❌ «has» — Present Simple третьої особи.",
                    'would have' => "❌ «would have» — частина результату, а не умови.",
                    'would be able' => "✅ «would be able» — правильний результат (would + be able to).",
                    'will be able' => "❌ «will be able» — First Conditional.",
                    'are able' => "❌ «are able» — Present Simple.",
                    'would have been able' => "❌ «would have been able» — Third Conditional (минуле).",
                ],
                'hints' => [
                    'a1' => $sharedHints['if_clause'],
                    'a2' => "Результат про можливість = **would + be able to + V1**. Приклад: *We would be able to travel more.*",
                ],
                'tense' => ['Second Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'If they {a1} perfect English, they {a2} a good job.',
                'verb_hint' => ['a1' => '(speak)', 'a2' => '(have)'],
                'options' => [
                    'a1' => ['spoke', 'speak', 'speaks', 'had spoken'],
                    'a2' => ['would have', 'will have', 'have', 'would be having'],
                ],
                'answers' => ['a1' => 'spoke', 'a2' => 'would have'],
                'explanations' => [
                    'spoke' => "✅ «spoke» — Past Simple, потрібний у if-клаузі.",
                    'speak' => "❌ «speak» — Present Simple.",
                    'speaks' => "❌ «speaks» — Present Simple третьої особи.",
                    'had spoken' => "❌ «had spoken» — Past Perfect для Third Conditional.",
                    'would have' => "✅ «would have» — правильний результат.",
                    'will have' => "❌ «will have» — First Conditional.",
                    'have' => "❌ «have» без would — Present Simple.",
                    'would be having' => "❌ «would be having» — форма Continuous, не потрібна тут.",
                ],
                'hints' => [
                    'a1' => $sharedHints['if_clause'],
                    'a2' => $sharedHints['result_clause'],
                ],
                'tense' => ['Second Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'If I {a1} in Mexico, I {a2} Spanish.',
                'verb_hint' => ['a1' => '(live)', 'a2' => '(speak)'],
                'options' => [
                    'a1' => ['lived', 'live', 'lives', 'had lived'],
                    'a2' => ['would speak', 'will speak', 'speak', 'would be speaking'],
                ],
                'answers' => ['a1' => 'lived', 'a2' => 'would speak'],
                'explanations' => [
                    'lived' => "✅ «lived» — Past Simple, що вимагає if-клауза.",
                    'live' => "❌ «live» — Present Simple.",
                    'lives' => "❌ «lives» — Present Simple третьої особи.",
                    'had lived' => "❌ «had lived» — Past Perfect.",
                    'would speak' => "✅ «would speak» — правильний результат.",
                    'will speak' => "❌ «will speak» — First Conditional.",
                    'speak' => "❌ «speak» — Present Simple.",
                    'would be speaking' => "❌ «would be speaking» — Continuous; не потрібна тривала форма.",
                ],
                'hints' => [
                    'a1' => $sharedHints['if_clause'],
                    'a2' => $sharedHints['result_clause'],
                ],
                'tense' => ['Second Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'If she {a1} to the party, she {a2} some friends.',
                'verb_hint' => ['a1' => '(go)', 'a2' => '(meet)'],
                'options' => [
                    'a1' => ['went', 'go', 'goes', 'had gone'],
                    'a2' => ['would meet', 'will meet', 'meets', 'would be meeting'],
                ],
                'answers' => ['a1' => 'went', 'a2' => 'would meet'],
                'explanations' => [
                    'went' => "✅ «went» — Past Simple для уявної умови.",
                    'go' => "❌ «go» — Present Simple.",
                    'goes' => "❌ «goes» — Present Simple третьої особи.",
                    'had gone' => "❌ «had gone» — Third Conditional.",
                    'would meet' => "✅ «would meet» — правильний результат.",
                    'will meet' => "❌ «will meet» — First Conditional.",
                    'meets' => "❌ «meets» — Present Simple.",
                    'would be meeting' => "❌ «would be meeting» — Continuous; тут потрібна проста форма.",
                ],
                'hints' => [
                    'a1' => $sharedHints['if_clause'],
                    'a2' => $sharedHints['result_clause'],
                ],
                'tense' => ['Second Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'If they {a1} more, they {a2} more intelligent.',
                'verb_hint' => ['a1' => '(study)', 'a2' => '(be)'],
                'options' => [
                    'a1' => ['studied', 'study', 'studies', 'had studied'],
                    'a2' => ['would be', 'will be', 'are', 'were'],
                ],
                'answers' => ['a1' => 'studied', 'a2' => 'would be'],
                'explanations' => [
                    'studied' => "✅ «studied» — Past Simple.",
                    'study' => "❌ «study» — Present Simple.",
                    'studies' => "❌ «studies» — Present Simple третьої особи.",
                    'had studied' => "❌ «had studied» — Past Perfect.",
                    'would be' => "✅ «would be» — правильний результат.",
                    'will be' => "❌ «will be» — First Conditional.",
                    'are' => "❌ «are» — Present Simple.",
                    'were' => "❌ «were» — Past Simple без would; не передає результат.",
                ],
                'hints' => [
                    'a1' => $sharedHints['if_clause'],
                    'a2' => $sharedHints['result_clause'],
                ],
                'tense' => ['Second Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'If we {a1} a house, we {a2} a bigger one.',
                'verb_hint' => ['a1' => '(buy)', 'a2' => '(buy)'],
                'options' => [
                    'a1' => ['bought', 'buy', 'buys', 'had bought'],
                    'a2' => ['would buy', 'will buy', 'would keep buying', 'would be buying'],
                ],
                'answers' => ['a1' => 'bought', 'a2' => 'would buy'],
                'explanations' => [
                    'bought' => "✅ «bought» — Past Simple.",
                    'buy' => "❌ «buy» — Present Simple.",
                    'buys' => "❌ «buys» — Present Simple третьої особи.",
                    'had bought' => "❌ «had bought» — Third Conditional.",
                    'would buy' => "✅ «would buy» — правильний результат.",
                    'will buy' => "❌ «will buy» — First Conditional.",
                    'would keep buying' => "❌ «would keep buying» — неправильний вираз для простого результату; зайвий елемент «keep».",
                    'would be buying' => "❌ «would be buying» — Continuous, не потрібна тут.",
                ],
                'hints' => [
                    'a1' => $sharedHints['if_clause'],
                    'a2' => $sharedHints['result_clause'],
                ],
                'tense' => ['Second Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'If my parents {a1} to dinner, we {a2} a good time.',
                'verb_hint' => ['a1' => '(come)', 'a2' => '(have)'],
                'options' => [
                    'a1' => ['came', 'come', 'comes', 'had come'],
                    'a2' => ['would have', 'will have', 'have', 'would be having'],
                ],
                'answers' => ['a1' => 'came', 'a2' => 'would have'],
                'explanations' => [
                    'came' => "✅ «came» — Past Simple.",
                    'come' => "❌ «come» — Present Simple.",
                    'comes' => "❌ «comes» — Present Simple третьої особи.",
                    'had come' => "❌ «had come» — Past Perfect.",
                    'would have' => "✅ «would have» — правильний результат.",
                    'will have' => "❌ «will have» — First Conditional.",
                    'have' => "❌ «have» без would — Present Simple.",
                    'would be having' => "❌ «would be having» — Continuous, не потрібна тут.",
                ],
                'hints' => [
                    'a1' => $sharedHints['if_clause'],
                    'a2' => $sharedHints['result_clause'],
                ],
                'tense' => ['Second Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'If he {a1} his number, he {a2} her.',
                'verb_hint' => ['a1' => '(know)', 'a2' => '(phone)'],
                'options' => [
                    'a1' => ['knew', 'know', 'knows', 'had known'],
                    'a2' => ['would phone', 'will phone', 'phones', 'would be phoning'],
                ],
                'answers' => ['a1' => 'knew', 'a2' => 'would phone'],
                'explanations' => [
                    'knew' => "✅ «knew» — Past Simple для if-клауза.",
                    'know' => "❌ «know» — Present Simple.",
                    'knows' => "❌ «knows» — Present Simple третьої особи.",
                    'had known' => "❌ «had known» — Past Perfect.",
                    'would phone' => "✅ «would phone» — правильний результат.",
                    'will phone' => "❌ «will phone» — First Conditional.",
                    'phones' => "❌ «phones» — Present Simple.",
                    'would be phoning' => "❌ «would be phoning» — Continuous; тут потрібна проста форма.",
                ],
                'hints' => [
                    'a1' => $sharedHints['if_clause'],
                    'a2' => $sharedHints['result_clause'],
                ],
                'tense' => ['Second Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'I {a1} to Spain on holiday if they {a2} cheap flights.',
                'verb_hint' => ['a1' => '(go)', 'a2' => '(have)'],
                'options' => [
                    'a1' => ['would go', 'go', 'will go', 'went'],
                    'a2' => ['had', 'have', 'would have', 'will have'],
                ],
                'answers' => ['a1' => 'would go', 'a2' => 'had'],
                'explanations' => [
                    'would go' => "✅ «would go» — правильний результат другого умовного навіть якщо стоїть перед if-клауза.",
                    'go' => "❌ «go» — Present Simple; бракує модального would.",
                    'will go' => "❌ «will go» — First Conditional.",
                    'went' => "❌ «went» — Past Simple, який використовується в if-клаузі, а не в результаті.",
                    'had' => "✅ «had» — Past Simple, потрібний у if-клаузі.",
                    'have' => "❌ «have» — Present Simple.",
                    'would have' => "❌ «would have» — Third Conditional.",
                    'will have' => "❌ «will have» — First Conditional.",
                ],
                'hints' => [
                    'a1' => $sharedHints['result_clause'],
                    'a2' => $sharedHints['if_clause'],
                ],
                'tense' => ['Second Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'They {a1} to the party if they {a2} too much homework.',
                'verb_hint' => ['a1' => '(not go)', 'a2' => '(have)'],
                'options' => [
                    'a1' => ["wouldn't go", "won't go", "don't go", "wouldn't be going"],
                    'a2' => ['had', 'have', 'would have', 'will have'],
                ],
                'answers' => ['a1' => "wouldn't go", 'a2' => 'had'],
                'explanations' => [
                    "wouldn't go" => "✅ «wouldn't go» — правильне заперечення результату з would + not + V1.",
                    "won't go" => "❌ «won't go» — First Conditional про реальне майбутнє.",
                    "don't go" => "❌ «don't go» — Present Simple.",
                    "wouldn't be going" => "❌ «wouldn't be going» — Continuous; у тесті потрібна проста форма.",
                    'had' => "✅ «had» — Past Simple для if-клауза.",
                    'have' => "❌ «have» — Present Simple.",
                    'would have' => "❌ «would have» — Third Conditional.",
                    'will have' => "❌ «will have» — First Conditional.",
                ],
                'hints' => [
                    'a1' => "Заперечення в результаті другого умовного: **would not + V1**. Приклад: *They wouldn't go out if it rained.*",
                    'a2' => $sharedHints['if_clause'],
                ],
                'tense' => ['Second Conditional'],
                'level' => 'A2',
            ],
        ];

        $service = new QuestionSeedingService();
        $items = [];
        $meta = [];

        foreach ($questions as $index => $question) {
            $questionText = $question['question'];
            $uuid = $this->generateQuestionUuid($index + 1, $questionText);

            [$preparedOptions, $optionMarkerMap] = $this->prepareOptions($question);

            $answers = [];
            foreach ($question['answers'] as $marker => $answer) {
                $answers[] = [
                    'marker' => $marker,
                    'answer' => $answer,
                    'verb_hint' => $this->normalizeVerbHint($question['verb_hint'][$marker] ?? null),
                ];
            }

            $tagIds = [$themeTagId, $detailTagId, $structureTagId, $tenseTagId];

            $items[] = [
                'uuid' => $uuid,
                'question' => $questionText,
                'category_id' => $categoryId,
                'difficulty' => $levelDifficulty[$question['level']] ?? 3,
                'source_id' => $sourceId,
                'flag' => 0,
                'level' => $question['level'],
                'tag_ids' => array_values(array_unique($tagIds)),
                'answers' => $answers,
                'options' => $preparedOptions,
                'variants' => [$question['question']],
            ];

            $meta[] = [
                'uuid' => $uuid,
                'answers' => $question['answers'],
                'hints' => $question['hints'],
                'explanations' => $question['explanations'],
                'option_markers' => $optionMarkerMap,
            ];
        }

        $service->seed($items);

        foreach ($meta as $data) {
            $question = Question::where('uuid', $data['uuid'])->first();
            if (! $question) {
                continue;
            }

            $hintText = $this->formatHints($data['hints']);
            if ($hintText !== null) {
                QuestionHint::updateOrCreate(
                    ['question_id' => $question->id, 'provider' => 'chatgpt', 'locale' => 'uk'],
                    ['hint' => $hintText]
                );
            }

            foreach ($data['explanations'] as $option => $text) {
                $marker = $data['option_markers'][$option] ?? array_key_first($data['answers']);
                $correct = $marker ? ($data['answers'][$marker] ?? reset($data['answers'])) : reset($data['answers']);

                ChatGPTExplanation::updateOrCreate(
                    [
                        'question' => $question->question,
                        'wrong_answer' => $option,
                        'correct_answer' => $correct,
                        'language' => 'ua',
                    ],
                    ['explanation' => $text]
                );
            }
        }
    }

    private function prepareOptions(array $question): array
    {
        $options = $question['options'];

        if (array_is_list($options)) {
            $marker = array_key_first($question['answers']);
            $map = [];
            foreach ($options as $option) {
                $map[$option] = $marker;
            }

            return [$options, $map];
        }

        $flat = [];
        $map = [];
        foreach ($options as $marker => $list) {
            foreach ($list as $option) {
                $flat[] = $option;
                $map[$option] = $marker;
            }
        }

        return [$flat, $map];
    }

    private function normalizeVerbHint(?string $hint): ?string
    {
        if ($hint === null) {
            return null;
        }

        return trim($hint, "() \t\n\r");
    }

    private function formatHints(array $hints): ?string
    {
        if (empty($hints)) {
            return null;
        }

        $parts = [];
        foreach ($hints as $marker => $text) {
            $text = trim((string) $text);
            if ($text === '') {
                continue;
            }

            $parts[] = '{' . $marker . '} ' . $text;
        }

        if (empty($parts)) {
            return null;
        }

        return implode("\n", $parts);
    }
}
