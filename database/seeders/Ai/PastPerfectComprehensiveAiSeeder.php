<?php

namespace Database\Seeders\Ai;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Source;
use App\Models\Tag;
use App\Services\QuestionSeedingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PastPerfectComprehensiveAiSeeder extends Seeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'AI Past Perfect Comprehensive Test'])->id;

        $sectionSources = [
            'A' => Source::firstOrCreate(['name' => 'AI Past Perfect Comprehensive Test — Section A'])->id,
            'B' => Source::firstOrCreate(['name' => 'AI Past Perfect Comprehensive Test — Section B'])->id,
            'C' => Source::firstOrCreate(['name' => 'AI Past Perfect Comprehensive Test — Section C'])->id,
            'D' => Source::firstOrCreate(['name' => 'AI Past Perfect Comprehensive Test — Section D'])->id,
            'E' => Source::firstOrCreate(['name' => 'AI Past Perfect Comprehensive Test — Section E'])->id,
            'F' => Source::firstOrCreate(['name' => 'AI Past Perfect Comprehensive Test — Section F'])->id,
            'G' => Source::firstOrCreate(['name' => 'AI Past Perfect Comprehensive Test — Section G'])->id,
        ];

        $sectionThemeTags = [
            'A' => Tag::firstOrCreate(['name' => 'Past Perfect Simple Practice'], ['category' => 'Grammar Theme'])->id,
            'B' => Tag::firstOrCreate(['name' => 'Past Perfect Continuous Practice'], ['category' => 'Grammar Theme'])->id,
            'C' => Tag::firstOrCreate(['name' => 'Mixed Past Perfect Usage'], ['category' => 'Grammar Theme'])->id,
            'D' => Tag::firstOrCreate(['name' => 'Past Perfect Mixed Multiple Choice'], ['category' => 'Grammar Theme'])->id,
            'E' => Tag::firstOrCreate(['name' => 'Past Perfect Simple Forms'], ['category' => 'Grammar Theme'])->id,
            'F' => Tag::firstOrCreate(['name' => 'Past Perfect Continuous Forms'], ['category' => 'Grammar Theme'])->id,
            'G' => Tag::firstOrCreate(['name' => 'Past Perfect Continuous Cause & Effect'], ['category' => 'Grammar Theme'])->id,
        ];

        $detailTagDefinitions = [
            'by_the_time_completion'      => 'By the Time Prior Completion',
            'before_clause_completion'    => 'Before-Clause Prior Completion',
            'duration_before_event'       => 'Duration Before Another Event',
            'cause_duration_because'      => 'Because-Clause Duration Cause',
            'cause_result_completion'     => 'Because-Clause Result Focus',
            'missing_information_cause'   => 'Missing Information Cause',
            'negative_experience_before'  => 'Negative Experience Before Event',
            'already_completion'          => 'Already Completed Before Event',
            'wrong_choice_consequence'    => 'Wrong Choice Consequence',
            'resulting_state_after_time'  => 'Resulting State After Long Action',
            'negative_cause_continuous'   => 'Negative Continuous Cause Statement',
        ];

        $detailTags = [];
        foreach ($detailTagDefinitions as $key => $name) {
            $detailTags[$key] = Tag::firstOrCreate(['name' => $name], ['category' => 'Grammar Detail'])->id;
        }

        $questionData = [
            'A' => [
                [
                    'question' => 'By the time the train arrived, we {a1} our tickets.',
                    'verb_hint' => ['a1' => '(buy)'],
                    'options' => ['bought', 'had bought', 'were buying'],
                    'answers' => ['a1' => 'had bought'],
                    'explanations' => [
                        'bought' => '❌ Past Simple = V2. Не підкреслює, що дія відбулася перед іншою.',
                        'had bought' => '✅ Past Perfect Simple = had + V3. Використовується, коли дія сталася раніше іншої у минулому.',
                        'were buying' => '❌ Past Continuous. Показує процес, але не завершений факт.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Simple.  \nФормула: **had + V3**.  \nПриклад: *They had left before we arrived.*  \nМаркер: «by the time».",
                    ],
                    'tense' => ['Past Perfect Simple'],
                    'level' => 'B1',
                    'detail_tags' => ['by_the_time_completion'],
                ],
                [
                    'question' => 'She {a1} the book before the teacher asked for it.',
                    'verb_hint' => ['a1' => '(finish)'],
                    'options' => ['finished', 'had finished', 'was finishing'],
                    'answers' => ['a1' => 'had finished'],
                    'explanations' => [
                        'finished' => '❌ Past Simple. Не показує послідовність «раніше/пізніше».',
                        'had finished' => '✅ Past Perfect Simple = had + V3. Спочатку завершила книгу, потім була інша дія.',
                        'was finishing' => '❌ Past Continuous. Лише процес, а не результат.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Simple.  \nФормула: **had + V3**.  \nПриклад: *I had finished my work before dinner.*  \nМаркер: «before».",
                    ],
                    'tense' => ['Past Perfect Simple'],
                    'level' => 'B1',
                    'detail_tags' => ['before_clause_completion'],
                ],
                [
                    'question' => 'They {a1} the letter before I opened the door.',
                    'verb_hint' => ['a1' => '(write)'],
                    'options' => ['wrote', 'had written', 'were writing'],
                    'answers' => ['a1' => 'had written'],
                    'explanations' => [
                        'wrote' => '❌ Past Simple. Просто дія в минулому, без логіки «раніше іншої».',
                        'had written' => '✅ Past Perfect Simple = had + V3. Правильно, бо дія завершилася до відкриття дверей.',
                        'were writing' => '❌ Past Continuous. Показує процес, але не результат.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Simple.  \nФормула: **had + V3**.  \nПриклад: *She had written the email before I called her.*  \nМаркер: «before».",
                    ],
                    'tense' => ['Past Perfect Simple'],
                    'level' => 'B1',
                    'detail_tags' => ['before_clause_completion'],
                ],
                [
                    'question' => 'He {a1} his homework before he went out to play.',
                    'verb_hint' => ['a1' => '(complete)'],
                    'options' => ['completed', 'had completed', 'was completing'],
                    'answers' => ['a1' => 'had completed'],
                    'explanations' => [
                        'completed' => '❌ Past Simple. Не передає послідовності.',
                        'had completed' => '✅ Past Perfect Simple = had + V3. Спочатку зробив домашнє, потім пішов гуляти.',
                        'was completing' => '❌ Past Continuous. Показує процес, але потрібен завершений факт.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Simple.  \nФормула: **had + V3**.  \nПриклад: *He had completed the test before the bell rang.*  \nМаркер: «before».",
                    ],
                    'tense' => ['Past Perfect Simple'],
                    'level' => 'B1',
                    'detail_tags' => ['before_clause_completion'],
                ],
                [
                    'question' => 'I {a1} dinner before my parents came home.',
                    'verb_hint' => ['a1' => '(prepare)'],
                    'options' => ['prepared', 'had prepared', 'was preparing'],
                    'answers' => ['a1' => 'had prepared'],
                    'explanations' => [
                        'prepared' => '❌ Past Simple. Не підкреслює, що дія відбулася раніше приходу.',
                        'had prepared' => '✅ Past Perfect Simple = had + V3. Правильно, бо вечеря була готова *до* приходу батьків.',
                        'was preparing' => '❌ Past Continuous. Описує процес, а не завершення.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Simple.  \nФормула: **had + V3**.  \nПриклад: *I had prepared dinner before they arrived.*  \nМаркер: «before».",
                    ],
                    'tense' => ['Past Perfect Simple'],
                    'level' => 'B1',
                    'detail_tags' => ['before_clause_completion'],
                ],
            ],
            'B' => [
                [
                    'question' => 'She {a1} for the bus for twenty minutes before it finally came.',
                    'verb_hint' => ['a1' => '(wait)'],
                    'options' => ['was waiting', 'had waited', 'had been waiting'],
                    'answers' => ['a1' => 'had been waiting'],
                    'explanations' => [
                        'was waiting' => '❌ Past Continuous. Показує процес, але не «раніше іншої події».',
                        'had waited' => '❌ Past Perfect Simple. Показує завершеність, але тут важлива тривалість.',
                        'had been waiting' => '✅ Past Perfect Continuous = had + been + V-ing. Формула для тривалих дій до іншої в минулому.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Continuous.  \nФормула: **had + been + V-ing**.  \nПриклад: *She had been waiting for an hour before the bus arrived.*  \nМаркер: «for …», «before».",
                    ],
                    'tense' => ['Past Perfect Continuous'],
                    'level' => 'B1',
                    'detail_tags' => ['duration_before_event'],
                ],
                [
                    'question' => 'They {a1} English for two years before they moved to London.',
                    'verb_hint' => ['a1' => '(study)'],
                    'options' => ['studied', 'had studied', 'had been studying'],
                    'answers' => ['a1' => 'had been studying'],
                    'explanations' => [
                        'studied' => '❌ Past Simple. Просто факт навчання, без наголосу на тривалості.',
                        'had studied' => '❌ Past Perfect Simple. Дає результат, але не процес протягом двох років.',
                        'had been studying' => '✅ Past Perfect Continuous. Формула: had + been + V-ing. Правильно, бо дія тривала перед іншою подією.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Continuous.  \nФормула: **had + been + V-ing**.  \nПриклад: *They had been studying English for years before they moved.*  \nМаркер: «for …», «before».",
                    ],
                    'tense' => ['Past Perfect Continuous'],
                    'level' => 'B1',
                    'detail_tags' => ['duration_before_event'],
                ],
                [
                    'question' => 'We {a1} for hours when the rain finally stopped.',
                    'verb_hint' => ['a1' => '(walk)'],
                    'options' => ['walked', 'had walked', 'had been walking'],
                    'answers' => ['a1' => 'had been walking'],
                    'explanations' => [
                        'walked' => '❌ Past Simple. Просто факт, без тривалості.',
                        'had walked' => '❌ Past Perfect Simple. Показує результат, але тут важлива тривалість.',
                        'had been walking' => '✅ Past Perfect Continuous = had + been + V-ing. Правильно, бо дія тривала певний час.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Continuous.  \nФормула: **had + been + V-ing**.  \nПриклад: *We had been walking for two hours when the storm began.*  \nМаркер: «for … when».",
                    ],
                    'tense' => ['Past Perfect Continuous'],
                    'level' => 'B1',
                    'detail_tags' => ['duration_before_event'],
                ],
                [
                    'question' => 'He {a1} the piano all afternoon before his friends visited him.',
                    'verb_hint' => ['a1' => '(play)'],
                    'options' => ['played', 'had played', 'had been playing'],
                    'answers' => ['a1' => 'had been playing'],
                    'explanations' => [
                        'played' => '❌ Past Simple. Не підкреслює тривалість.',
                        'had played' => '❌ Past Perfect Simple. Показує результат, але тут важлива тривалість.',
                        'had been playing' => '✅ Past Perfect Continuous = had + been + V-ing. Правильно, бо дія тривала весь день.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Continuous.  \nФормула: **had + been + V-ing**.  \nПриклад: *He had been playing the piano before his friends came.*  \nМаркер: «all afternoon», «before».",
                    ],
                    'tense' => ['Past Perfect Continuous'],
                    'level' => 'B1',
                    'detail_tags' => ['duration_before_event'],
                ],
                [
                    'question' => 'I {a1} to call you for days before you finally answered.',
                    'verb_hint' => ['a1' => '(try)'],
                    'options' => ['tried', 'had tried', 'had been trying'],
                    'answers' => ['a1' => 'had been trying'],
                    'explanations' => [
                        'tried' => '❌ Past Simple. Одноразова дія, без тривалості.',
                        'had tried' => '❌ Past Perfect Simple. Описує результат, але не повторювані дії.',
                        'had been trying' => '✅ Past Perfect Continuous = had + been + V-ing. Правильно, бо підкреслює безперервні спроби протягом кількох днів.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Continuous.  \nФормула: **had + been + V-ing**.  \nПриклад: *I had been trying to reach you all week.*  \nМаркер: «for days», «before».",
                    ],
                    'tense' => ['Past Perfect Continuous'],
                    'level' => 'B1',
                    'detail_tags' => ['duration_before_event'],
                ],
            ],
            'C' => [
                [
                    'question' => 'By the time I met him, he {a1} in that company for ten years.',
                    'verb_hint' => ['a1' => '(work)'],
                    'options' => ['worked', 'had worked', 'had been working'],
                    'answers' => ['a1' => 'had been working'],
                    'explanations' => [
                        'worked' => '❌ Past Simple. Просто факт, без відношення до іншої події.',
                        'had worked' => '❌ Past Perfect Simple. Можливо, але без акценту на тривалості.',
                        'had been working' => '✅ Past Perfect Continuous = had + been + V-ing. Правильно, бо дія тривала 10 років до моменту зустрічі.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Continuous.  \nФормула: **had + been + V-ing**.  \nПриклад: *He had been working there for years before I met him.*  \nМаркер: «for …», «by the time».",
                    ],
                    'tense' => ['Past Perfect Continuous'],
                    'level' => 'B1',
                    'detail_tags' => ['by_the_time_completion', 'duration_before_event'],
                ],
                [
                    'question' => 'They were tired because they {a1} the house all day.',
                    'verb_hint' => ['a1' => '(clean)'],
                    'options' => ['cleaned', 'had cleaned', 'had been cleaning'],
                    'answers' => ['a1' => 'had been cleaning'],
                    'explanations' => [
                        'cleaned' => '❌ Past Simple. Одноразова дія.',
                        'had cleaned' => '❌ Past Perfect Simple. Показує результат, але тут важливий процес, що викликав втому.',
                        'had been cleaning' => '✅ Past Perfect Continuous = had + been + V-ing. Вірно, бо підкреслює виснажливу тривалість.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Continuous.  \nФормула: **had + been + V-ing**.  \nПриклад: *They had been cleaning all day, so they were tired.*  \nМаркер: «all day», «because».",
                    ],
                    'tense' => ['Past Perfect Continuous'],
                    'level' => 'B1',
                    'detail_tags' => ['cause_duration_because'],
                ],
                [
                    'question' => 'She was upset because she {a1} the message earlier.',
                    'verb_hint' => ['a1' => '(not/receive)'],
                    'options' => ['had not received', 'did not receive', 'was not receiving'],
                    'answers' => ['a1' => 'had not received'],
                    'explanations' => [
                        'had not received' => '✅ Past Perfect Simple = had + not + V3. Показує, що вона не отримала повідомлення до іншої події.',
                        'did not receive' => '❌ Past Simple. Просто факт у минулому, без «раніше».',
                        'was not receiving' => '❌ Past Continuous. Процес, а не результат.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Simple.  \nФормула: **had + not + V3**.  \nПриклад: *She was upset because she had not received the email.*  \nМаркер: «because», «earlier».",
                    ],
                    'tense' => ['Past Perfect Simple'],
                    'level' => 'B1',
                    'detail_tags' => ['missing_information_cause'],
                ],
                [
                    'question' => 'The streets were wet because it {a1} all night.',
                    'verb_hint' => ['a1' => '(rain)'],
                    'options' => ['rained', 'had rained', 'had been raining'],
                    'answers' => ['a1' => 'had been raining'],
                    'explanations' => [
                        'rained' => '❌ Past Simple. Факт, але не акцентує тривалість.',
                        'had rained' => '❌ Past Perfect Simple. Дає результат, але тут важлива тривалість процесу.',
                        'had been raining' => '✅ Past Perfect Continuous = had + been + V-ing. Пояснює стан вулиць (мокрі) після тривалого дощу.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Continuous.  \nФормула: **had + been + V-ing**.  \nПриклад: *It had been raining all night, so the streets were wet.*  \nМаркер: «all night», «because».",
                    ],
                    'tense' => ['Past Perfect Continuous'],
                    'level' => 'B1',
                    'detail_tags' => ['cause_duration_because'],
                ],
                [
                    'question' => 'I {a1} him before he moved abroad.',
                    'verb_hint' => ['a1' => '(never/meet)'],
                    'options' => ['never met', 'had never met', 'was never meeting'],
                    'answers' => ['a1' => 'had never met'],
                    'explanations' => [
                        'never met' => '❌ Past Simple. Не підкреслює, що дія була до іншої.',
                        'had never met' => '✅ Past Perfect Simple = had + never + V3. Вірно, бо дія (зустріч) не відбулася до моменту переїзду.',
                        'was never meeting' => '❌ Past Continuous. Некоректна форма.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Simple.  \nФормула: **had + never + V3**.  \nПриклад: *I had never met him before that day.*  \nМаркер: «before».",
                    ],
                    'tense' => ['Past Perfect Simple'],
                    'level' => 'B1',
                    'detail_tags' => ['before_clause_completion', 'negative_experience_before'],
                ],
            ],
            'D' => [
                [
                    'question' => 'I was exhausted when I got home. I {a1} for hours.',
                    'verb_hint' => [],
                    'options' => ['had worked', 'had been working', 'was working'],
                    'answers' => ['a1' => 'had been working'],
                    'explanations' => [
                        'had worked' => '❌ Past Perfect Simple. Показує результат, але тут акцент на процесі.',
                        'had been working' => '✅ Past Perfect Continuous = had + been + V-ing. Вірно, бо дія тривала певний час.',
                        'was working' => '❌ Past Continuous. Не показує зв’язку «до іншої дії».',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Continuous.  \nФормула: **had + been + V-ing**.  \nПриклад: *I was tired because I had been working all day.*  \nМаркер: «for hours».",
                    ],
                    'tense' => ['Past Perfect Continuous'],
                    'level' => 'B1',
                    'detail_tags' => ['resulting_state_after_time'],
                ],
                [
                    'question' => 'She was very happy because she {a1} the exam.',
                    'verb_hint' => [],
                    'options' => ['had passed', 'passed', 'had been passing'],
                    'answers' => ['a1' => 'had passed'],
                    'explanations' => [
                        'had passed' => '✅ Past Perfect Simple = had + V3. Показує, що вона склала іспит до моменту радості.',
                        'passed' => '❌ Past Simple. Просто факт у минулому.',
                        'had been passing' => '❌ Past Perfect Continuous. Некоректна форма (іспити — результат, а не процес).',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Simple.  \nФормула: **had + V3**.  \nПриклад: *She was happy because she had passed the test.*  \nМаркер: «because».",
                    ],
                    'tense' => ['Past Perfect Simple'],
                    'level' => 'B1',
                    'detail_tags' => ['cause_result_completion'],
                ],
                [
                    'question' => 'When we arrived at the station, the train {a1}.',
                    'verb_hint' => [],
                    'options' => ['had already left', 'already left', 'was leaving'],
                    'answers' => ['a1' => 'had already left'],
                    'explanations' => [
                        'had already left' => '✅ Past Perfect Simple = had + V3. Дія (поїзд поїхав) відбулася до нашого приходу.',
                        'already left' => '❌ Past Simple. Некоректне вживання з «already».',
                        'was leaving' => '❌ Past Continuous. Показує процес, але тут потрібен результат.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Simple.  \nФормула: **had + already + V3**.  \nПриклад: *The train had already left when we arrived.*  \nМаркер: «already».",
                    ],
                    'tense' => ['Past Perfect Simple'],
                    'level' => 'B1',
                    'detail_tags' => ['already_completion'],
                ],
                [
                    'question' => 'The children were dirty because they {a1} in the garden.',
                    'verb_hint' => [],
                    'options' => ['played', 'had been playing', 'had played'],
                    'answers' => ['a1' => 'had been playing'],
                    'explanations' => [
                        'played' => '❌ Past Simple. Не пояснює стан «брудні».',
                        'had been playing' => '✅ Past Perfect Continuous = had + been + V-ing. Правильно, бо стан результат тривалого процесу.',
                        'had played' => '❌ Past Perfect Simple. Акцент на завершеності, а не тривалості.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Continuous.  \nФормула: **had + been + V-ing**.  \nПриклад: *They were wet because they had been playing in the rain.*  \nМаркер: «because».",
                    ],
                    'tense' => ['Past Perfect Continuous'],
                    'level' => 'B1',
                    'detail_tags' => ['cause_duration_because'],
                ],
                [
                    'question' => 'By the time the teacher came, the students {a1} their homework.',
                    'verb_hint' => [],
                    'options' => ['had already done', 'already did', 'were doing'],
                    'answers' => ['a1' => 'had already done'],
                    'explanations' => [
                        'had already done' => '✅ Past Perfect Simple = had + already + V3. Вірно, бо дія завершилася до приходу вчителя.',
                        'already did' => '❌ Past Simple. Некоректна конструкція.',
                        'were doing' => '❌ Past Continuous. Показує процес, але не завершення.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Simple.  \nФормула: **had + already + V3**.  \nПриклад: *They had already done their work before the teacher arrived.*  \nМаркер: «by the time».",
                    ],
                    'tense' => ['Past Perfect Simple'],
                    'level' => 'B1',
                    'detail_tags' => ['by_the_time_completion', 'already_completion'],
                ],
                [
                    'question' => 'She looked tired because she {a1} all night.',
                    'verb_hint' => [],
                    'options' => ['had studied', 'had been studying', 'studied'],
                    'answers' => ['a1' => 'had been studying'],
                    'explanations' => [
                        'had studied' => '❌ Past Perfect Simple. Показує результат, але акцент тут на процесі.',
                        'had been studying' => '✅ Past Perfect Continuous = had + been + V-ing. Пояснює причину її втоми.',
                        'studied' => '❌ Past Simple. Не пов’язує дії між собою.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Continuous.  \nФормула: **had + been + V-ing**.  \nПриклад: *She was tired because she had been studying all night.*  \nМаркер: «all night».",
                    ],
                    'tense' => ['Past Perfect Continuous'],
                    'level' => 'B1',
                    'detail_tags' => ['cause_duration_because'],
                ],
                [
                    'question' => 'They {a1} all the cake before we arrived.',
                    'verb_hint' => [],
                    'options' => ['ate', 'had eaten', 'had been eating'],
                    'answers' => ['a1' => 'had eaten'],
                    'explanations' => [
                        'ate' => '❌ Past Simple. Просто факт, але не «раніше іншої події».',
                        'had eaten' => '✅ Past Perfect Simple = had + V3. Правильно, бо торт з’їли до нашого приходу.',
                        'had been eating' => '❌ Past Perfect Continuous. Описує процес, але тут важливий завершений результат.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Simple.  \nФормула: **had + V3**.  \nПриклад: *They had eaten everything before we came.*  \nМаркер: «before».",
                    ],
                    'tense' => ['Past Perfect Simple'],
                    'level' => 'B1',
                    'detail_tags' => ['before_clause_completion'],
                ],
                [
                    'question' => 'We were late because we {a1} the wrong bus.',
                    'verb_hint' => [],
                    'options' => ['took', 'had taken', 'had been taking'],
                    'answers' => ['a1' => 'had taken'],
                    'explanations' => [
                        'took' => '❌ Past Simple. Просто факт, але не показує зв’язок «раніше іншої події».',
                        'had taken' => '✅ Past Perfect Simple = had + V3. Вірно, бо спочатку вони сіли на неправильний автобус, і лише потім запізнилися.',
                        'had been taking' => '❌ Past Perfect Continuous. Некоректне вживання з «bus».',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Simple.  \nФормула: **had + V3**.  \nПриклад: *We were late because we had taken the wrong road.*  \nМаркер: «because».",
                    ],
                    'tense' => ['Past Perfect Simple'],
                    'level' => 'B1',
                    'detail_tags' => ['cause_result_completion', 'wrong_choice_consequence'],
                ],
                [
                    'question' => 'He was angry because his brother {a1} his computer.',
                    'verb_hint' => [],
                    'options' => ['used', 'had used', 'had been using'],
                    'answers' => ['a1' => 'had been using'],
                    'explanations' => [
                        'used' => '❌ Past Simple. Факт, але не пояснює стан злості.',
                        'had used' => '❌ Past Perfect Simple. Показує результат, але тут акцент на тривалості.',
                        'had been using' => '✅ Past Perfect Continuous = had + been + V-ing. Правильно, бо його брат користувався комп’ютером деякий час.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Continuous.  \nФормула: **had + been + V-ing**.  \nПриклад: *He was angry because his friend had been using his phone.*  \nМаркер: «because».",
                    ],
                    'tense' => ['Past Perfect Continuous'],
                    'level' => 'B1',
                    'detail_tags' => ['cause_duration_because'],
                ],
                [
                    'question' => 'By the time we got to the cinema, the film {a1}.',
                    'verb_hint' => [],
                    'options' => ['started', 'had started', 'had been starting'],
                    'answers' => ['a1' => 'had started'],
                    'explanations' => [
                        'started' => '❌ Past Simple. Просто факт, але не показує відношення «раніше іншої події».',
                        'had started' => '✅ Past Perfect Simple = had + V3. Правильно, бо фільм уже почався до того, як ми прийшли.',
                        'had been starting' => '❌ Past Perfect Continuous. Некоректне вживання (старт — завершена дія, не процес).',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Simple.  \nФормула: **had + V3**.  \nПриклад: *The lesson had started before we entered the class.*  \nМаркер: «by the time».",
                    ],
                    'tense' => ['Past Perfect Simple'],
                    'level' => 'B1',
                    'detail_tags' => ['by_the_time_completion'],
                ],
            ],
            'E' => [
                [
                    'question' => 'By the time I got home, my sister {a1} dinner.',
                    'verb_hint' => ['a1' => '(cook)'],
                    'options' => ['cooked', 'had cooked', 'was cooking'],
                    'answers' => ['a1' => 'had cooked'],
                    'explanations' => [
                        'cooked' => '❌ Past Simple. Просто дія, без підкреслення «раніше іншої».',
                        'had cooked' => '✅ Past Perfect Simple (Affirmative) = had + V3. Вірно, бо вечеря була готова до приходу.',
                        'was cooking' => '❌ Past Continuous. Процес, але не завершення.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Simple (Affirmative).  \nФормула: **had + V3**.  \nПриклад: *She had finished her work before he arrived.*  \nМаркер: «by the time».",
                    ],
                    'tense' => ['Past Perfect Simple (Affirmative)'],
                    'level' => 'B1',
                    'detail_tags' => ['by_the_time_completion'],
                ],
                [
                    'question' => 'She was upset because she {a1} the message.',
                    'verb_hint' => ['a1' => '(not/receive)'],
                    'options' => ['did not receive', 'had not received', 'was not receiving'],
                    'answers' => ['a1' => 'had not received'],
                    'explanations' => [
                        'did not receive' => '❌ Past Simple. Просто факт.',
                        'had not received' => '✅ Past Perfect Simple (Negative) = had + not + V3. Вірно, бо дія не відбулася до іншої.',
                        'was not receiving' => '❌ Past Continuous. Не підходить.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Simple (Negative).  \nФормула: **had + not + V3**.  \nПриклад: *She had not received the email before the meeting.*  \nМаркер: «before».",
                    ],
                    'tense' => ['Past Perfect Simple (Negative)'],
                    'level' => 'B1',
                    'detail_tags' => ['missing_information_cause'],
                ],
                [
                    'question' => '{a1} your homework before you went out?',
                    'verb_hint' => ['a1' => '(you/finish)'],
                    'options' => ['Did you finish', 'Had you finished', 'Were you finishing'],
                    'answers' => ['a1' => 'Had you finished'],
                    'explanations' => [
                        'Did you finish' => '❌ Past Simple. Не передає, що дія повинна була статися раніше іншої.',
                        'Had you finished' => '✅ Past Perfect Simple (Question) = Had + subject + V3. Правильна форма.',
                        'Were you finishing' => '❌ Past Continuous. Некоректно для логіки «до іншої дії».',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Simple (Interrogative).  \nФормула: **Had + subject + V3**.  \nПриклад: *Had you finished your homework before dinner?*  \nМаркер: «before».",
                    ],
                    'tense' => ['Past Perfect Simple (Question)'],
                    'level' => 'B1',
                    'detail_tags' => ['before_clause_completion'],
                ],
            ],
            'F' => [
                [
                    'question' => 'She was tired because she {a1} all night.',
                    'verb_hint' => ['a1' => '(study)'],
                    'options' => ['had studied', 'had been studying', 'was studying'],
                    'answers' => ['a1' => 'had been studying'],
                    'explanations' => [
                        'had studied' => '❌ Past Perfect Simple. Показує результат, але акцент на процесі.',
                        'had been studying' => '✅ Past Perfect Continuous (Affirmative) = had + been + V-ing. Вірно, бо дія тривала і пояснює втому.',
                        'was studying' => '❌ Past Continuous. Не відноситься до іншої події.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Continuous (Affirmative).  \nФормула: **had + been + V-ing**.  \nПриклад: *She was tired because she had been studying all night.*  \nМаркер: «because».",
                    ],
                    'tense' => ['Past Perfect Continuous (Affirmative)'],
                    'level' => 'B1',
                    'detail_tags' => ['cause_duration_because'],
                ],
                [
                    'question' => 'They were wet because they {a1} in the rain.',
                    'verb_hint' => ['a1' => '(not/play)'],
                    'options' => ['had not played', 'had not been playing', 'did not play'],
                    'answers' => ['a1' => 'had not been playing'],
                    'explanations' => [
                        'had not played' => '❌ Past Perfect Simple. Факт, але не процес.',
                        'had not been playing' => '✅ Past Perfect Continuous (Negative) = had + not + been + V-ing. Вірно, якщо хочемо заперечити тривалу дію.',
                        'did not play' => '❌ Past Simple. Просто факт, не підкреслює зв’язок.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Continuous (Negative).  \nФормула: **had + not + been + V-ing**.  \nПриклад: *They were not tired because they had not been working hard.*  \nМаркер: «because».",
                    ],
                    'tense' => ['Past Perfect Continuous (Negative)'],
                    'level' => 'B1',
                    'detail_tags' => ['negative_cause_continuous'],
                ],
                [
                    'question' => '{a1} for a long time before the bus arrived?',
                    'verb_hint' => ['a1' => '(they/wait)'],
                    'options' => ['Had they been waiting', 'Were they waiting', 'Did they wait'],
                    'answers' => ['a1' => 'Had they been waiting'],
                    'explanations' => [
                        'Had they been waiting' => '✅ Past Perfect Continuous (Question) = Had + subject + been + V-ing. Правильна форма.',
                        'Were they waiting' => '❌ Past Continuous. Показує процес, але не зв’язок із іншою дією.',
                        'Did they wait' => '❌ Past Simple. Не виражає тривалості.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Continuous (Interrogative).  \nФормула: **Had + subject + been + V-ing**.  \nПриклад: *Had they been waiting long before the train came?*  \nМаркер: «for a long time», «before».",
                    ],
                    'tense' => ['Past Perfect Continuous (Question)'],
                    'level' => 'B1',
                    'detail_tags' => ['duration_before_event'],
                ],
            ],
            'G' => [
                [
                    'question' => 'He was tired because he {a1} all day.',
                    'verb_hint' => ['a1' => '(work)'],
                    'options' => ['worked', 'had worked', 'had been working'],
                    'answers' => ['a1' => 'had been working'],
                    'explanations' => [
                        'worked' => '❌ Past Simple. Просто факт, але не тривалість.',
                        'had worked' => '❌ Past Perfect Simple. Показує завершення, але тут важливий процес.',
                        'had been working' => '✅ Past Perfect Continuous = had + been + V-ing. Пояснює причину його втоми.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Continuous.  \nФормула: **had + been + V-ing**.  \nПриклад: *He was tired because he had been working all day.*  \nМаркер: «because».",
                    ],
                    'tense' => ['Past Perfect Continuous'],
                    'level' => 'B2',
                    'detail_tags' => ['cause_duration_because'],
                ],
                [
                    'question' => 'Her clothes were dirty because she {a1} in the garden.',
                    'verb_hint' => ['a1' => '(play)'],
                    'options' => ['played', 'had played', 'had been playing'],
                    'answers' => ['a1' => 'had been playing'],
                    'explanations' => [
                        'played' => '❌ Past Simple. Просто факт, але не пояснює наслідок (брудний одяг).',
                        'had played' => '❌ Past Perfect Simple. Показує результат, але не процес.',
                        'had been playing' => '✅ Past Perfect Continuous = had + been + V-ing. Підкреслює тривалість, що призвела до результату.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Continuous.  \nФормула: **had + been + V-ing**.  \nПриклад: *Her clothes were dirty because she had been playing outside.*  \nМаркер: «because».",
                    ],
                    'tense' => ['Past Perfect Continuous'],
                    'level' => 'B2',
                    'detail_tags' => ['cause_duration_because'],
                ],
                [
                    'question' => 'The ground was wet because it {a1} all night.',
                    'verb_hint' => ['a1' => '(rain)'],
                    'options' => ['rained', 'had rained', 'had been raining'],
                    'answers' => ['a1' => 'had been raining'],
                    'explanations' => [
                        'rained' => '❌ Past Simple. Просто факт, без логіки наслідку.',
                        'had rained' => '❌ Past Perfect Simple. Показує завершення, але тут важлива тривалість процесу.',
                        'had been raining' => '✅ Past Perfect Continuous = had + been + V-ing. Пояснює стан мокрої землі.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Continuous.  \nФормула: **had + been + V-ing**.  \nПриклад: *The ground was wet because it had been raining all night.*  \nМаркер: «all night», «because».",
                    ],
                    'tense' => ['Past Perfect Continuous'],
                    'level' => 'B2',
                    'detail_tags' => ['cause_duration_because'],
                ],
                [
                    'question' => 'She lost her voice because she {a1} for hours.',
                    'verb_hint' => ['a1' => '(sing)'],
                    'options' => ['sang', 'had sung', 'had been singing'],
                    'answers' => ['a1' => 'had been singing'],
                    'explanations' => [
                        'sang' => '❌ Past Simple. Просто факт, але не пояснює наслідок.',
                        'had sung' => '❌ Past Perfect Simple. Показує завершення, але тут потрібен процес.',
                        'had been singing' => '✅ Past Perfect Continuous = had + been + V-ing. Правильно, бо тривала дія призвела до втрати голосу.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Continuous.  \nФормула: **had + been + V-ing**.  \nПриклад: *She lost her voice because she had been singing all day.*  \nМаркер: «because».",
                    ],
                    'tense' => ['Past Perfect Continuous'],
                    'level' => 'B2',
                    'detail_tags' => ['cause_duration_because'],
                ],
                [
                    'question' => 'They were late because they {a1} the wrong way.',
                    'verb_hint' => ['a1' => '(drive)'],
                    'options' => ['drove', 'had driven', 'had been driving'],
                    'answers' => ['a1' => 'had been driving'],
                    'explanations' => [
                        'drove' => '❌ Past Simple. Просто факт, без відношення до іншої події.',
                        'had driven' => '❌ Past Perfect Simple. Показує завершення, але тут важливий процес.',
                        'had been driving' => '✅ Past Perfect Continuous = had + been + V-ing. Правильно, бо вони довго їхали неправильною дорогою, і це спричинило запізнення.',
                    ],
                    'hints' => [
                        'a1' => "⏳ Час: Past Perfect Continuous.  \nФормула: **had + been + V-ing**.  \nПриклад: *They were late because they had been driving in the wrong direction.*  \nМаркер: «because».",
                    ],
                    'tense' => ['Past Perfect Continuous'],
                    'level' => 'B2',
                    'detail_tags' => ['cause_duration_because', 'wrong_choice_consequence'],
                ],
            ],
        ];

        $levelDifficulty = [
            'A1' => 1,
            'A2' => 2,
            'B1' => 3,
            'B2' => 4,
            'C1' => 5,
            'C2' => 5,
        ];

        $tenseTagIds = [];
        foreach ($questionData as $sections) {
            foreach ($sections as $question) {
                foreach ($question['tense'] as $tenseName) {
                    if (! isset($tenseTagIds[$tenseName])) {
                        $tenseTagIds[$tenseName] = Tag::firstOrCreate(['name' => $tenseName], ['category' => 'Tenses'])->id;
                    }
                }
            }
        }

        $service = new QuestionSeedingService();
        $items = [];
        $meta = [];

        foreach ($questionData as $sectionKey => $questions) {
            foreach ($questions as $question) {
                $uuid = (string) Str::uuid();
                $answers = [];
                $optionMarkerMap = [];

                $firstMarker = array_key_first($question['answers']);
                if ($firstMarker !== null) {
                    foreach ($question['options'] as $option) {
                        $optionMarkerMap[$option] = $firstMarker;
                    }
                }

                foreach ($question['answers'] as $marker => $answer) {
                    $answers[] = [
                        'marker' => $marker,
                        'answer' => $answer,
                        'verb_hint' => $this->normalizeHint($question['verb_hint'][$marker] ?? null),
                    ];
                    $optionMarkerMap[$answer] = $marker;
                }

                $tagIds = [$sectionThemeTags[$sectionKey]];
                foreach ($question['tense'] as $tenseName) {
                    $tagIds[] = $tenseTagIds[$tenseName];
                }
                foreach ($question['detail_tags'] as $detailKey) {
                    $tagIds[] = $detailTags[$detailKey];
                }

                $items[] = [
                    'uuid' => $uuid,
                    'question' => $question['question'],
                    'category_id' => $categoryId,
                    'difficulty' => $levelDifficulty[$question['level']] ?? 3,
                    'source_id' => $sectionSources[$sectionKey],
                    'flag' => 2,
                    'level' => $question['level'],
                    'tag_ids' => array_values(array_unique($tagIds)),
                    'answers' => $answers,
                    'options' => $question['options'],
                    'variants' => $question['variants'] ?? [],
                ];

                $meta[] = [
                    'uuid' => $uuid,
                    'answers' => $question['answers'],
                    'option_markers' => $optionMarkerMap,
                    'hints' => $question['hints'],
                    'explanations' => $question['explanations'],
                ];
            }
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
                $correct = $data['answers'][$marker] ?? reset($data['answers']);

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

    private function normalizeHint(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return trim($value, "() \t\n\r");
    }

    private function formatHints(array $hints): ?string
    {
        if (empty($hints)) {
            return null;
        }

        $parts = [];
        foreach ($hints as $marker => $text) {
            $parts[] = '{' . $marker . '} ' . ltrim($text);
        }

        return implode("\n", $parts);
    }
}
