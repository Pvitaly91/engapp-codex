<?php

namespace Database\Seeders\Ai;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Source;
use App\Models\Tag;
use App\Services\QuestionSeedingService;
use Database\Seeders\QuestionSeeder;

class FutureTensesComprehensiveAiSeeder extends QuestionSeeder
{ 
    
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Future Tenses Comprehensive AI Test'])->id;

        $sectionSources = [
            'mixed' => Source::firstOrCreate(['name' => 'Future Tenses Mixed Practice'])->id,
            'duration' => Source::firstOrCreate(['name' => 'Future Perfect Continuous Focus'])->id,
        ];

        $themeTags = [
            'mixed' => Tag::firstOrCreate(['name' => 'Future Tenses Mixed Practice'], ['category' => 'English Grammar Theme'])->id,
            'duration' => Tag::firstOrCreate(['name' => 'Future Perfect Continuous Practice'], ['category' => 'English Grammar Theme'])->id,
        ];

        $detailTags = [
            'future_perfect_completion' => Tag::firstOrCreate(['name' => 'Completion before Future Moment'], ['category' => 'English Grammar Detail'])->id,
            'future_continuous_in_progress' => Tag::firstOrCreate(['name' => 'Ongoing Action at Future Time'], ['category' => 'English Grammar Detail'])->id,
            'future_simple_plan' => Tag::firstOrCreate(['name' => 'Simple Future Plans and Decisions'], ['category' => 'English Grammar Detail'])->id,
            'future_perfect_continuous_duration' => Tag::firstOrCreate(['name' => 'Future Duration before Reference Point'], ['category' => 'English Grammar Detail'])->id,
        ];
 
        $rawQuestions = [
            [
                'question'  => 'John {a1} the whole house by the time the guests arrive.',
                'verb_hint' => ['a1' => '(tidy up)'],
                'options'   => ['will tidy up', 'will be tidying up', 'will have tidied up', 'will have been tidying up'],
                'answers'   => ['a1' => 'will have tidied up'],
                'explanations' => [
                    'will tidy up' => "❌ Future Simple. Формула: **will + V1**. Використовується для простих фактів у майбутньому.  \nПриклад: *I will tidy up my room tomorrow.*",
                    'will be tidying up' => "❌ Future Continuous. Формула: **will + be + V-ing**. Показує процес у певний момент у майбутньому.  \nПриклад: *This time tomorrow, I will be tidying up the house.*",
                    'will have tidied up' => "✅ Future Perfect. Формула: **will + have + V3**. Правильно, бо дія завершиться до того, як приїдуть гості.  \nПриклад: *By 8 o’clock, I will have finished my work.*",
                    'will have been tidying up' => "❌ Future Perfect Continuous. Формула: **will + have + been + V-ing**. Використовується для тривалості дії до моменту.  \nПриклад: *By 8 o’clock, I will have been tidying up for two hours.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Perfect.  \nФормула: **will + have + V3**.  \nПриклад: *By tomorrow, I will have finished my work.*  \nМаркери: by the time, by tomorrow, by next week.",
                ],
                'tense' => ['Future Perfect'],
                'level' => 'B1',
            ],
            [
                'question'  => 'By this time next year, she {a1} her studies.',
                'verb_hint' => ['a1' => '(finish)'],
                'options'   => ['will finish', 'will be finishing', 'will have finished', 'will have been finishing'],
                'answers'   => ['a1' => 'will have finished'],
                'explanations' => [
                    'will finish' => "❌ Future Simple. Формула: **will + V1**. Просто факт, не гарантує завершення.  \nПриклад: *She will finish her studies in June.*",
                    'will be finishing' => "❌ Future Continuous. Формула: **will + be + V-ing**. Показує дію в процесі.  \nПриклад: *This time tomorrow, she will be finishing her exam.*",
                    'will have finished' => "✅ Future Perfect. Формула: **will + have + V3**. Показує завершеність до певного моменту.  \nПриклад: *By next summer, she will have finished her studies.*",
                    'will have been finishing' => "❌ Future Perfect Continuous. Формула: **will + have + been + V-ing**. Підкреслює тривалість, але тут важливий результат.  \nПриклад: *By 5 pm, she will have been finishing her essay for two hours.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Perfect.  \nФормула: **will + have + V3**.  \nПриклад: *By next summer, they will have completed the project.*  \nМаркери: by this time, by next year, before.",
                ],
                'tense' => ['Future Perfect'],
                'level' => 'B1',
            ],
            [
                'question'  => 'This time tomorrow, they {a1} in the swimming pool.',
                'verb_hint' => ['a1' => '(swim)'],
                'options'   => ['will swim', 'will be swimming', 'will have swum', 'will have been swimming'],
                'answers'   => ['a1' => 'will be swimming'],
                'explanations' => [
                    'will swim' => "❌ Future Simple. Формула: **will + V1**. Просто факт.  \nПриклад: *They will swim next weekend.*",
                    'will be swimming' => "✅ Future Continuous. Формула: **will + be + V-ing**. Показує дію, яка триватиме в певний момент.  \nПриклад: *This time tomorrow, they will be swimming in the pool.*",
                    'will have swum' => "❌ Future Perfect. Формула: **will + have + V3**. Це результат, а не процес.  \nПриклад: *By 10 a.m., they will have swum 20 laps.*",
                    'will have been swimming' => "❌ Future Perfect Continuous. Формула: **will + have + been + V-ing**. Використовується для підкреслення тривалості.  \nПриклад: *By 10 a.m., they will have been swimming for an hour.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Continuous.  \nФормула: **will + be + V-ing**.  \nПриклад: *At 10 a.m. tomorrow, I will be taking an exam.*  \nМаркери: this time tomorrow, at 5 p.m. next Monday.",
                ],
                'tense' => ['Future Continuous'],
                'level' => 'B1',
            ],
            [
                'question'  => 'I {a1} my grandparents at the weekend.',
                'verb_hint' => ['a1' => '(visit)'],
                'options'   => ['will visit', 'will be visiting', 'will have visited', 'will have been visiting'],
                'answers'   => ['a1' => 'will visit'],
                'explanations' => [
                    'will visit' => "✅ Future Simple. Формула: **will + V1**. Звичайна дія в майбутньому.  \nПриклад: *I will visit my grandparents at the weekend.*",
                    'will be visiting' => "❌ Future Continuous. Формула: **will + be + V-ing**. Підкреслює процес, тут не потрібно.  \nПриклад: *This time tomorrow, I will be visiting my grandparents.*",
                    'will have visited' => "❌ Future Perfect. Формула: **will + have + V3**. Показує завершеність, тут не доречно.  \nПриклад: *By Sunday, I will have visited them.*",
                    'will have been visiting' => "❌ Future Perfect Continuous. Формула: **will + have + been + V-ing**. Показує тривалість.  \nПриклад: *By noon, I will have been visiting my grandparents for two hours.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Simple.  \nФормула: **will + V1**.  \nПриклад: *I will visit my aunt tomorrow.*  \nМаркери: tomorrow, next week, soon.",
                ],
                'tense' => ['Future Simple'],
                'level' => 'A2',
            ],
            [
                'question'  => 'By the end of the week, we {a1} this project.',
                'verb_hint' => ['a1' => '(complete)'],
                'options'   => ['will complete', 'will be completing', 'will have completed', 'will have been completing'],
                'answers'   => ['a1' => 'will have completed'],
                'explanations' => [
                    'will complete' => "❌ Future Simple. Формула: **will + V1**. Просто майбутня дія, але не завершеність.  \nПриклад: *We will complete the project next month.*",
                    'will be completing' => "❌ Future Continuous. Формула: **will + be + V-ing**. Показує процес.  \nПриклад: *This time tomorrow, we will be completing the project.*",
                    'will have completed' => "✅ Future Perfect. Формула: **will + have + V3**. Дія завершиться до кінця тижня.  \nПриклад: *By Friday, we will have completed the project.*",
                    'will have been completing' => "❌ Future Perfect Continuous. Формула: **will + have + been + V-ing**. Це тривалість.  \nПриклад: *By Friday, we will have been completing it for a week.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Perfect.  \nФормула: **will + have + V3**.  \nПриклад: *By Friday, they will have finished the work.*  \nМаркери: by the end of, by tomorrow, before.",
                ],
                'tense' => ['Future Perfect'],
                'level' => 'B1',
            ],
            [
                'question'  => 'In ten years, people {a1} on Mars.',
                'verb_hint' => ['a1' => '(live)'],
                'options'   => ['will live', 'will be living', 'will have lived', 'will have been living'],
                'answers'   => ['a1' => 'will be living'],
                'explanations' => [
                    'will live' => "❌ Future Simple. Формула: **will + V1**. Просто факт.  \nПриклад: *People will live longer in the future.*",
                    'will be living' => "✅ Future Continuous. Формула: **will + be + V-ing**. Підкреслює стан у процесі.  \nПриклад: *In ten years, people will be living on Mars.*",
                    'will have lived' => "❌ Future Perfect. Формула: **will + have + V3**. Це завершеність.  \nПриклад: *By 2030, people will have lived on Mars for a decade.*",
                    'will have been living' => "❌ Future Perfect Continuous. Формула: **will + have + been + V-ing**. Це тривалість.  \nПриклад: *By 2030, people will have been living on Mars for 10 years.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Continuous.  \nФормула: **will + be + V-ing**.  \nПриклад: *In 50 years, robots will be working everywhere.*  \nМаркери: in 10 years, this time next month.",
                ],
                'tense' => ['Future Continuous'],
                'level' => 'B1',
            ],
            [
                'question'  => 'He {a1} his homework tomorrow morning.',
                'verb_hint' => ['a1' => '(do)'],
                'options'   => ['will do', 'will be doing', 'will have done', 'will have been doing'],
                'answers'   => ['a1' => 'will do'],
                'explanations' => [
                    'will do' => "✅ Future Simple. Формула: **will + V1**. Звичайна дія у майбутньому.  \nПриклад: *He will do his homework tomorrow.*",
                    'will be doing' => "❌ Future Continuous. Формула: **will + be + V-ing**. Це процес у певний момент.  \nПриклад: *At 9 am, he will be doing his homework.*",
                    'will have done' => "❌ Future Perfect. Формула: **will + have + V3**. Це завершеність.  \nПриклад: *By noon, he will have done his homework.*",
                    'will have been doing' => "❌ Future Perfect Continuous. Формула: **will + have + been + V-ing**. Це тривалість.  \nПриклад: *By noon, he will have been doing his homework for two hours.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Simple.  \nФормула: **will + V1**.  \nПриклад: *He will do his homework tomorrow.*  \nМаркери: tomorrow, next Monday, soon.",
                ],
                'tense' => ['Future Simple'],
                'level' => 'A2',
            ],
            [
                'question'  => 'By 2025, they {a1} a new bridge here.',
                'verb_hint' => ['a1' => '(build)'],
                'options'   => ['will build', 'will be building', 'will have built', 'will have been building'],
                'answers'   => ['a1' => 'will have built'],
                'explanations' => [
                    'will build' => "❌ Future Simple. Формула: **will + V1**. Просто факт.  \nПриклад: *They will build a new house next year.*",
                    'will be building' => "❌ Future Continuous. Формула: **will + be + V-ing**. Показує процес, а не завершеність.  \nПриклад: *This time next week, they will be building a garage.*",
                    'will have built' => "✅ Future Perfect. Формула: **will + have + V3**. Правильно, бо до 2025 року дія буде завершена.  \nПриклад: *By next year, they will have built the bridge.*",
                    'will have been building' => "❌ Future Perfect Continuous. Формула: **will + have + been + V-ing**. Використовується для тривалості.  \nПриклад: *By 2025, they will have been building the bridge for 3 years.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Perfect.  \nФормула: **will + have + V3**.  \nПриклад: *By tomorrow, I will have completed the task.*  \nМаркери: by, by the time, before.",
                ],
                'tense' => ['Future Perfect'],
                'level' => 'B1',
            ],
            [
                'question'  => 'At 10 o’clock tomorrow, I {a1} an exam.',
                'verb_hint' => ['a1' => '(take)'],
                'options'   => ['will take', 'will be taking', 'will have taken', 'will have been taking'],
                'answers'   => ['a1' => 'will be taking'],
                'explanations' => [
                    'will take' => "❌ Future Simple. Формула: **will + V1**. Просто факт.  \nПриклад: *I will take the exam tomorrow.*",
                    'will be taking' => "✅ Future Continuous. Формула: **will + be + V-ing**. Правильно, бо підкреслює дію у процесі у конкретний момент.  \nПриклад: *At 10 a.m. tomorrow, I will be taking the exam.*",
                    'will have taken' => "❌ Future Perfect. Формула: **will + have + V3**. Це завершеність, тут не підходить.  \nПриклад: *By 10 a.m., I will have taken the exam.*",
                    'will have been taking' => "❌ Future Perfect Continuous. Використовується для тривалості.  \nПриклад: *By 10 a.m., I will have been taking exams for 2 hours.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Continuous.  \nФормула: **will + be + V-ing**.  \nПриклад: *This time tomorrow, I will be sitting in class.*  \nМаркери: this time tomorrow, at 5 p.m. next Monday.",
                ],
                'tense' => ['Future Continuous'],
                'level' => 'B1',
            ],
            [
                'question'  => 'She {a1} to Paris next summer.',
                'verb_hint' => ['a1' => '(go)'],
                'options'   => ['will go', 'will be going', 'will have gone', 'will have been going'],
                'answers'   => ['a1' => 'will go'],
                'explanations' => [
                    'will go' => "✅ Future Simple. Формула: **will + V1**. Звичайна дія у майбутньому.  \nПриклад: *She will go to London next summer.*",
                    'will be going' => "❌ Future Continuous. Формула: **will + be + V-ing**. Це процес у моменті.  \nПриклад: *This time tomorrow, she will be going to Paris.*",
                    'will have gone' => "❌ Future Perfect. Формула: **will + have + V3**. Це завершеність, тут не підходить.  \nПриклад: *By June, she will have gone to Paris.*",
                    'will have been going' => "❌ Future Perfect Continuous. Формула: **will + have + been + V-ing**. Невдало для простого плану.  \nПриклад: *By June, she will have been going to Paris every year.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Simple.  \nФормула: **will + V1**.  \nПриклад: *I will go there tomorrow.*  \nМаркери: tomorrow, next week, soon.",
                ],
                'tense' => ['Future Simple'],
                'level' => 'A2',
            ],
            [
                'question'  => 'By next Friday, we {a1} all the tests.',
                'verb_hint' => ['a1' => '(check)'],
                'options'   => ['will check', 'will be checking', 'will have checked', 'will have been checking'],
                'answers'   => ['a1' => 'will have checked'],
                'explanations' => [
                    'will check' => "❌ Future Simple. Формула: **will + V1**. Просто факт.  Приклад: *We will check the tests tomorrow.*",
                    'will be checking' => "❌ Future Continuous. Формула: **will + be + V-ing**. Це дія у процесі.  Приклад: *At 10 a.m. tomorrow, we will be checking the tests.*",
                    'will have checked' => "✅ Future Perfect. Формула: **will + have + V3**. Правильно, бо дія буде завершена до вказаного моменту.  Приклад: *By next Friday, we will have checked all the tests.*",
                    'will have been checking' => "❌ Future Perfect Continuous. Підкреслює тривалість, тут не потрібно.  Приклад: *By next Friday, we will have been checking tests for a week.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Perfect.  Формула: **will + have + V3**.  Приклад: *By tomorrow, I will have done my homework.*  Маркери: by next Friday, before, by the time.",
                ],
                'tense' => ['Future Perfect'],
                'level' => 'B1',
            ],
            [
                'question'  => 'This time next week, she {a1} in Spain.',
                'verb_hint' => ['a1' => '(travel)'],
                'options'   => ['will travel', 'will be travelling', 'will have travelled', 'will have been travelling'],
                'answers'   => ['a1' => 'will be travelling'],
                'explanations' => [
                    'will travel' => "❌ Future Simple. Формула: **will + V1**. Просто факт.  Приклад: *She will travel to Spain next summer.*",
                    'will be travelling' => "✅ Future Continuous. Формула: **will + be + V-ing**. Правильно, бо підкреслює дію у процесі.  Приклад: *This time next week, she will be travelling in Spain.*",
                    'will have travelled' => "❌ Future Perfect. Формула: **will + have + V3**. Це результат, а не процес.  Приклад: *By next week, she will have travelled to Spain.*",
                    'will have been travelling' => "❌ Future Perfect Continuous. Формула: **will + have + been + V-ing**. Це тривалість.  Приклад: *By next week, she will have been travelling for a month.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Continuous.  Формула: **will + be + V-ing**.  Приклад: *At this time tomorrow, I will be working.*  Маркери: this time tomorrow, at 5 p.m. next Sunday.",
                ],
                'tense' => ['Future Continuous'],
                'level' => 'B1',
            ],
            [
                'question'  => 'He {a1} a book tomorrow evening.',
                'verb_hint' => ['a1' => '(read)'],
                'options'   => ['will read', 'will be reading', 'will have read', 'will have been reading'],
                'answers'   => ['a1' => 'will be reading'],
                'explanations' => [
                    'will read' => "❌ Future Simple. Формула: **will + V1**. Просто факт.  Приклад: *He will read a book tomorrow.*",
                    'will be reading' => "✅ Future Continuous. Формула: **will + be + V-ing**. Правильно, бо підкреслює дію у процесі в конкретний час.  Приклад: *Tomorrow evening, he will be reading a book.*",
                    'will have read' => "❌ Future Perfect. Формула: **will + have + V3**. Це завершеність.  Приклад: *By tomorrow evening, he will have read the book.*",
                    'will have been reading' => "❌ Future Perfect Continuous. Формула: **will + have + been + V-ing**. Використовується для тривалості.  Приклад: *By tomorrow evening, he will have been reading for 2 hours.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Continuous.  Формула: **will + be + V-ing**.  Приклад: *At 8 p.m. tomorrow, I will be watching TV.*  Маркери: tomorrow evening, this time tomorrow.",
                ],
                'tense' => ['Future Continuous'],
                'level' => 'B1',
            ],
            [
                'question'  => 'By the time you come, I {a1} the letter.',
                'verb_hint' => ['a1' => '(write)'],
                'options'   => ['will write', 'will be writing', 'will have written', 'will have been writing'],
                'answers'   => ['a1' => 'will have written'],
                'explanations' => [
                    'will write' => "❌ Future Simple. Формула: **will + V1**. Просто факт.  Приклад: *I will write a letter tomorrow.*",
                    'will be writing' => "❌ Future Continuous. Формула: **will + be + V-ing**. Це процес у моменті.  Приклад: *When you call, I will be writing a letter.*",
                    'will have written' => "✅ Future Perfect. Формула: **will + have + V3**. Дія завершиться до моменту приходу.  Приклад: *By the time you come, I will have written the letter.*",
                    'will have been writing' => "❌ Future Perfect Continuous. Використовується для тривалості.  Приклад: *By the time you come, I will have been writing for an hour.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Perfect.  Формула: **will + have + V3**.  Приклад: *By the time he arrives, I will have finished the job.*  Маркери: by the time, before.",
                ],
                'tense' => ['Future Perfect'],
                'level' => 'B1',
            ],
            [
                'question'  => 'By the end of the day, she {a1} twenty emails.',
                'verb_hint' => ['a1' => '(send)'],
                'options'   => ['will send', 'will be sending', 'will have sent', 'will have been sending'],
                'answers'   => ['a1' => 'will have sent'],
                'explanations' => [
                    'will send' => "❌ Future Simple. Формула: **will + V1**. Просто факт у майбутньому.  Приклад: *She will send an email tomorrow.*",
                    'will be sending' => "❌ Future Continuous. Формула: **will + be + V-ing**. Показує дію у процесі.  Приклад: *This time tomorrow, she will be sending emails.*",
                    'will have sent' => "✅ Future Perfect. Формула: **will + have + V3**. Дія завершиться до кінця дня.  Приклад: *By the end of the day, she will have sent twenty emails.*",
                    'will have been sending' => "❌ Future Perfect Continuous. Формула: **will + have + been + V-ing**. Використовується для тривалості.  Приклад: *By 5 pm, she will have been sending emails for 3 hours.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Perfect.  Формула: **will + have + V3**.  Приклад: *By the evening, I will have finished my homework.*  Маркери: by the end of, before, by tomorrow.",
                ],
                'tense' => ['Future Perfect'],
                'level' => 'B1',
            ],
            [
                'question'  => 'At 7 o’clock tomorrow, we {a1} dinner.',
                'verb_hint' => ['a1' => '(have)'],
                'options'   => ['will have', 'will be having', 'will have had', 'will have been having'],
                'answers'   => ['a1' => 'will be having'],
                'explanations' => [
                    'will have' => "❌ Future Simple. Формула: **will + V1**. Просто факт.  Приклад: *We will have dinner tomorrow.*",
                    'will be having' => "✅ Future Continuous. Формула: **will + be + V-ing**. Правильно, бо підкреслює дію у процесі у конкретний момент.  Приклад: *At 7 p.m. tomorrow, we will be having dinner.*",
                    'will have had' => "❌ Future Perfect. Формула: **will + have + V3**. Це завершеність.  Приклад: *By 7 p.m., we will have had dinner.*",
                    'will have been having' => "❌ Future Perfect Continuous. Підкреслює тривалість.  Приклад: *By 7 p.m., we will have been having dinner for 30 minutes.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Continuous.  Формула: **will + be + V-ing**.  Приклад: *At this time tomorrow, I will be cooking.*  Маркери: this time tomorrow, at 7 p.m. tomorrow.",
                ],
                'tense' => ['Future Continuous'],
                'level' => 'B1',
            ],
            [
                'question'  => 'They {a1} a new house next month.',
                'verb_hint' => ['a1' => '(buy)'],
                'options'   => ['will buy', 'will be buying', 'will have bought', 'will have been buying'],
                'answers'   => ['a1' => 'will buy'],
                'explanations' => [
                    'will buy' => "✅ Future Simple. Формула: **will + V1**. Звичайна дія у майбутньому.  Приклад: *They will buy a new house next month.*",
                    'will be buying' => "❌ Future Continuous. Формула: **will + be + V-ing**. Це дія у процесі, тут не підходить.  Приклад: *This time next month, they will be buying furniture.*",
                    'will have bought' => "❌ Future Perfect. Формула: **will + have + V3**. Показує завершеність, а не просто план.  Приклад: *By next month, they will have bought the house.*",
                    'will have been buying' => "❌ Future Perfect Continuous. Формула: **will + have + been + V-ing**. Використовується для тривалості.  Приклад: *By next month, they will have been buying things for the house for a week.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Simple.  Формула: **will + V1**.  Приклад: *I will buy some food tomorrow.*  Маркери: tomorrow, next month, soon.",
                ],
                'tense' => ['Future Simple'],
                'level' => 'A2',
            ],
            [
                'question'  => 'By noon tomorrow, I {a1} the report.',
                'verb_hint' => ['a1' => '(finish)'],
                'options'   => ['will finish', 'will be finishing', 'will have finished', 'will have been finishing'],
                'answers'   => ['a1' => 'will have finished'],
                'explanations' => [
                    'will finish' => "❌ Future Simple. Формула: **will + V1**. Просто дія.  Приклад: *I will finish the report tomorrow.*",
                    'will be finishing' => "❌ Future Continuous. Формула: **will + be + V-ing**. Це процес.  Приклад: *At 11 a.m., I will be finishing the report.*",
                    'will have finished' => "✅ Future Perfect. Формула: **will + have + V3**. Правильно, бо дія завершиться до полудня.  Приклад: *By noon tomorrow, I will have finished the report.*",
                    'will have been finishing' => "❌ Future Perfect Continuous. Використовується для тривалості.  Приклад: *By noon, I will have been finishing it for 2 hours.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Perfect.  Формула: **will + have + V3**.  Приклад: *By tomorrow morning, I will have done the task.*  Маркери: by noon, by tomorrow, before.",
                ],
                'tense' => ['Future Perfect'],
                'level' => 'B1',
            ],
            [
                'question'  => 'At this time next Sunday, we {a1} in the mountains.',
                'verb_hint' => ['a1' => '(walk)'],
                'options'   => ['will walk', 'will be walking', 'will have walked', 'will have been walking'],
                'answers'   => ['a1' => 'will be walking'],
                'explanations' => [
                    'will walk' => "❌ Future Simple. Формула: **will + V1**. Просто факт.  Приклад: *We will walk in the park tomorrow.*",
                    'will be walking' => "✅ Future Continuous. Формула: **will + be + V-ing**. Правильно, бо це дія у процесі у конкретний час.  Приклад: *At this time next Sunday, we will be walking in the mountains.*",
                    'will have walked' => "❌ Future Perfect. Формула: **will + have + V3**. Показує завершеність.  Приклад: *By Sunday, we will have walked 20 km.*",
                    'will have been walking' => "❌ Future Perfect Continuous. Формула: **will + have + been + V-ing**. Це тривалість.  Приклад: *By Sunday, we will have been walking for 3 hours.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Continuous.  Формула: **will + be + V-ing**.  Приклад: *This time tomorrow, I will be studying.*  Маркери: this time tomorrow, at 3 p.m. next Sunday.",
                ],
                'tense' => ['Future Continuous'],
                'level' => 'B1',
            ],
            [
                'question'  => 'He {a1} his driving test next week.',
                'verb_hint' => ['a1' => '(pass)'],
                'options'   => ['will pass', 'will be passing', 'will have passed', 'will have been passing'],
                'answers'   => ['a1' => 'will pass'],
                'explanations' => [
                    'will pass' => "✅ Future Simple. Формула: **will + V1**. Звичайна дія у майбутньому.  Приклад: *He will pass his exam next week.*",
                    'will be passing' => "❌ Future Continuous. Формула: **will + be + V-ing**. Показує процес у певний момент.  Приклад: *This time tomorrow, he will be passing his exam.*",
                    'will have passed' => "❌ Future Perfect. Формула: **will + have + V3**. Це завершеність.  Приклад: *By next week, he will have passed his exam.*",
                    'will have been passing' => "❌ Future Perfect Continuous. Показує тривалість.  Приклад: *By next week, he will have been passing many exams.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Simple.  Формула: **will + V1**.  Приклад: *I will pass the test tomorrow.*  Маркери: tomorrow, next week, soon.",
                ],
                'tense' => ['Future Simple'],
                'level' => 'A2',
            ],
            [
                'question'  => 'By 2030, scientists {a1} a cure for this disease.',
                'verb_hint' => ['a1' => '(discover)'],
                'options'   => ['will discover', 'will be discovering', 'will have discovered', 'will have been discovering'],
                'answers'   => ['a1' => 'will have discovered'],
                'explanations' => [
                    'will discover' => "❌ Future Simple. Формула: **will + V1**. Просто прогноз.  Приклад: *They will discover new things in the future.*",
                    'will be discovering' => "❌ Future Continuous. Формула: **will + be + V-ing**. Це процес, не завершеність.  Приклад: *This time next year, scientists will be discovering new methods.*",
                    'will have discovered' => "✅ Future Perfect. Формула: **will + have + V3**. Правильно, бо дія завершиться до 2030 року.  Приклад: *By 2030, scientists will have discovered a cure.*",
                    'will have been discovering' => "❌ Future Perfect Continuous. Показує тривалість.  Приклад: *By 2030, scientists will have been discovering cures for decades.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Perfect.  Формула: **will + have + V3**.  Приклад: *By next year, we will have discovered the truth.*  Маркери: by 2030, before, by the time.",
                ],
                'tense' => ['Future Perfect'],
                'level' => 'B2',
            ],
            [
                'question'  => 'Tomorrow morning, I {a1} my presentation.',
                'verb_hint' => ['a1' => '(give)'],
                'options'   => ['will give', 'will be giving', 'will have given', 'will have been giving'],
                'answers'   => ['a1' => 'will give'],
                'explanations' => [
                    'will give' => "✅ Future Simple. Формула: **will + V1**. Звичайна дія у майбутньому.  Приклад: *I will give a presentation tomorrow.*",
                    'will be giving' => "❌ Future Continuous. Формула: **will + be + V-ing**. Це процес у певний момент.  Приклад: *At 9 a.m., I will be giving a presentation.*",
                    'will have given' => "❌ Future Perfect. Формула: **will + have + V3**. Це завершеність.  Приклад: *By 10 a.m., I will have given my talk.*",
                    'will have been giving' => "❌ Future Perfect Continuous. Підкреслює тривалість.  Приклад: *By 10 a.m., I will have been giving presentations for an hour.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Simple.  Формула: **will + V1**.  Приклад: *She will give a lecture tomorrow.*  Маркери: tomorrow, next morning, soon.",
                ],
                'tense' => ['Future Simple'],
                'level' => 'A2',
            ],
            [
                'question'  => 'By next Monday, she {a1} here for two years.',
                'verb_hint' => ['a1' => '(work)'],
                'options'   => ['will work', 'will be working', 'will have worked', 'will have been working'],
                'answers'   => ['a1' => 'will have been working'],
                'explanations' => [
                    'will work' => "❌ Future Simple. Формула: **will + V1**. Використовується для простих майбутніх фактів.  Приклад: *She will work here next year.* — констатація факту, без акценту на тривалість.",
                    'will be working' => "❌ Future Continuous. Формула: **will + be + V-ing**. Використовується для процесів у певний момент у майбутньому.  Приклад: *This time tomorrow, she will be working.* — підкреслює процес, але не показує «як довго».",
                    'will have worked' => "❌ Future Perfect. Формула: **will + have + V3**. Показує результат завершення, а не тривалість.  Приклад: *By Monday, she will have worked here for 2 years.* — звучить як завершення.",
                    'will have been working' => "✅ Future Perfect Continuous. Формула: **will + have + been + V-ing**. Використовується для дій, що тривають певний час до певного моменту.  Приклад: *By next Monday, she will have been working here for two years.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Perfect Continuous.  Формула: **will + have + been + V-ing**.  Використовується для підкреслення тривалості дії до певного моменту у майбутньому.  Маркери: for, since, by, by the time.  Приклад: *By 2025, I will have been studying English for 5 years.*",
                ],
                'tense' => ['Future Perfect Continuous'],
                'level' => 'B2',
            ],
            [
                'question'  => 'By the time you arrive, I {a1} for an hour.',
                'verb_hint' => ['a1' => '(wait)'],
                'options'   => ['will wait', 'will be waiting', 'will have waited', 'will have been waiting'],
                'answers'   => ['a1' => 'will have been waiting'],
                'explanations' => [
                    'will wait' => "❌ Future Simple. Формула: **will + V1**. Просто обіцянка чи факт у майбутньому.  Приклад: *I will wait for you tomorrow.*",
                    'will be waiting' => "❌ Future Continuous. Формула: **will + be + V-ing**. Дія у процесі в конкретний момент, але без акценту на тривалість.  Приклад: *At 5 pm, I will be waiting at the station.*",
                    'will have waited' => "❌ Future Perfect. Формула: **will + have + V3**. Показує завершення очікування.  Приклад: *By 6 pm, I will have waited for an hour.*",
                    'will have been waiting' => "✅ Future Perfect Continuous. Формула: **will + have + been + V-ing**. Наголошує, що очікування триватиме певний час до твого приходу.  Приклад: *By the time you arrive, I will have been waiting for an hour.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Perfect Continuous.  Формула: **will + have + been + V-ing**.  Вживається для дії, що триватиме до конкретного моменту.  Приклад: *By the time he comes, she will have been waiting for 2 hours.*  Маркери: by the time, for an hour, since morning.",
                ],
                'tense' => ['Future Perfect Continuous'],
                'level' => 'B2',
            ],
            [
                'question'  => 'By 2026, they {a1} in London for a decade.',
                'verb_hint' => ['a1' => '(live)'],
                'options'   => ['will live', 'will be living', 'will have lived', 'will have been living'],
                'answers'   => ['a1' => 'will have been living'],
                'explanations' => [
                    'will live' => "❌ Future Simple. Просто констатація майбутнього факту.  Приклад: *They will live in London next year.*",
                    'will be living' => "❌ Future Continuous. Підкреслює процес у моменті, але не тривалість.  Приклад: *This time tomorrow, they will be living in London.*",
                    'will have lived' => "❌ Future Perfect. Формула: **will + have + V3**. Наголошує на результаті.  Приклад: *By 2026, they will have lived in London for 10 years.*",
                    'will have been living' => "✅ Future Perfect Continuous. Підкреслює тривалість проживання до певного моменту.  Приклад: *By 2026, they will have been living in London for a decade.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Perfect Continuous.  Формула: **will + have + been + V-ing**.  Приклад: *By 2030, I will have been living abroad for 5 years.*",
                ],
                'tense' => ['Future Perfect Continuous'],
                'level' => 'B2',
            ],
            [
                'question'  => 'By the evening, we {a1} the whole day.',
                'verb_hint' => ['a1' => '(travel)'],
                'options'   => ['will travel', 'will be travelling', 'will have travelled', 'will have been travelling'],
                'answers'   => ['a1' => 'will have been travelling'],
                'explanations' => [
                    'will travel' => "❌ Future Simple — план.  Приклад: *We will travel tomorrow.*",
                    'will be travelling' => "❌ Future Continuous — дія у процесі.  Приклад: *At 10 am, we will be travelling.*",
                    'will have travelled' => "❌ Future Perfect — результат.  Приклад: *By evening, we will have travelled 200 km.*",
                    'will have been travelling' => "✅ Future Perfect Continuous — тривалість подорожі до вечора.  Приклад: *By the evening, we will have been travelling all day.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Perfect Continuous.  Формула: **will + have + been + V-ing**.  Маркери: all day, for three hours, since morning.  Приклад: *By evening, we will have been travelling for 12 hours.*",
                ],
                'tense' => ['Future Perfect Continuous'],
                'level' => 'B2',
            ],
            [
                'question'  => 'Next June, I {a1} here for 5 years.',
                'verb_hint' => ['a1' => '(study)'],
                'options'   => ['will study', 'will be studying', 'will have studied', 'will have been studying'],
                'answers'   => ['a1' => 'will have been studying'],
                'explanations' => [
                    'will study' => "❌ Future Simple — план.  Приклад: *I will study tomorrow.*",
                    'will be studying' => "❌ Future Continuous — процес у моменті.  Приклад: *This time tomorrow, I will be studying.*",
                    'will have studied' => "❌ Future Perfect — результат.  Приклад: *By June, I will have studied here for 5 years.*",
                    'will have been studying' => "✅ Future Perfect Continuous — підкреслює тривалість навчання.  Приклад: *By June, I will have been studying here for 5 years.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Perfect Continuous.  Формула: **will + have + been + V-ing**.  Приклад: *By June, I will have been studying for 5 years.*  Маркери: since, for, by next June.",
                ],
                'tense' => ['Future Perfect Continuous'],
                'level' => 'B2',
            ],
            [
                'question'  => 'By 8 pm, she {a1} dinner for two hours.',
                'verb_hint' => ['a1' => '(cook)'],
                'options'   => ['will cook', 'will be cooking', 'will have cooked', 'will have been cooking'],
                'answers'   => ['a1' => 'will have been cooking'],
                'explanations' => [
                    'will cook' => "❌ Future Simple — план.  Приклад: *She will cook tomorrow.*",
                    'will be cooking' => "❌ Future Continuous — процес.  Приклад: *At 7 pm, she will be cooking.*",
                    'will have cooked' => "❌ Future Perfect — результат.  Приклад: *By 8 pm, she will have cooked dinner.*",
                    'will have been cooking' => "✅ Future Perfect Continuous — дія триває певний час.  Приклад: *By 8 pm, she will have been cooking for two hours.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Perfect Continuous.  Формула: **will + have + been + V-ing**.  Маркери: for two hours, since 6 pm.  Приклад: *By 8 pm, she will have been cooking for 2 hours.*",
                ],
                'tense' => ['Future Perfect Continuous'],
                'level' => 'B2',
            ],
            [
                'question'  => 'By the time we reach the station, the train {a1} for 30 minutes.',
                'verb_hint' => ['a1' => '(wait)'],
                'options'   => ['will wait', 'will be waiting', 'will have waited', 'will have been waiting'],
                'answers'   => ['a1' => 'will have been waiting'],
                'explanations' => [
                    'will wait' => "❌ Future Simple — факт.  Приклад: *The train will wait for 5 minutes.*",
                    'will be waiting' => "❌ Future Continuous — процес.  Приклад: *At 5 pm, the train will be waiting.*",
                    'will have waited' => "❌ Future Perfect — результат.  Приклад: *By 5 pm, the train will have waited.*",
                    'will have been waiting' => "✅ Future Perfect Continuous — дія триває до моменту.  Приклад: *By the time we arrive, the train will have been waiting for 30 minutes.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Perfect Continuous.  Формула: **will + have + been + V-ing**.  Приклад: *By the time you arrive, I will have been waiting for half an hour.*",
                ],
                'tense' => ['Future Perfect Continuous'],
                'level' => 'B2',
            ],
            [
                'question'  => 'By July, they {a1} the house for a year.',
                'verb_hint' => ['a1' => '(renovate)'],
                'options'   => ['will renovate', 'will be renovating', 'will have renovated', 'will have been renovating'],
                'answers'   => ['a1' => 'will have been renovating'],
                'explanations' => [
                    'will renovate' => "❌ Future Simple — план.  Приклад: *They will renovate the house.*",
                    'will be renovating' => "❌ Future Continuous — процес.  Приклад: *This time tomorrow, they will be renovating the house.*",
                    'will have renovated' => "❌ Future Perfect — результат.  Приклад: *By July, they will have renovated the house.*",
                    'will have been renovating' => "✅ Future Perfect Continuous — дія триває рік до липня.  Приклад: *By July, they will have been renovating the house for a year.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Perfect Continuous.  Формула: **will + have + been + V-ing**.  Маркери: for, since, by July.  Приклад: *By July, they will have been renovating the house for 12 months.*",
                ],
                'tense' => ['Future Perfect Continuous'],
                'level' => 'B2',
            ],
            [
                'question'  => 'In 2027, I {a1} in this company for 15 years.',
                'verb_hint' => ['a1' => '(work)'],
                'options'   => ['will work', 'will be working', 'will have worked', 'will have been working'],
                'answers'   => ['a1' => 'will have been working'],
                'explanations' => [
                    'will work' => "❌ Future Simple — план.  Приклад: *I will work in this company next year.*",
                    'will be working' => "❌ Future Continuous — процес.  Приклад: *This time tomorrow, I will be working here.*",
                    'will have worked' => "❌ Future Perfect — результат.  Приклад: *By 2027, I will have worked here for 15 years.*",
                    'will have been working' => "✅ Future Perfect Continuous — наголошує на тривалості.  Приклад: *In 2027, I will have been working in this company for 15 years.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Perfect Continuous.  Формула: **will + have + been + V-ing**.  Приклад: *By 2030, I will have been working here for 20 years.*",
                ],
                'tense' => ['Future Perfect Continuous'],
                'level' => 'B2',
            ],
            [
                'question'  => 'By tomorrow morning, he {a1} the whole night.',
                'verb_hint' => ['a1' => '(study)'],
                'options'   => ['will study', 'will be studying', 'will have studied', 'will have been studying'],
                'answers'   => ['a1' => 'will have been studying'],
                'explanations' => [
                    'will study' => "❌ Future Simple — план.  Приклад: *He will study tomorrow.*",
                    'will be studying' => "❌ Future Continuous — процес.  Приклад: *At 3 am, he will be studying.*",
                    'will have studied' => "❌ Future Perfect — результат.  Приклад: *By morning, he will have studied all night.*",
                    'will have been studying' => "✅ Future Perfect Continuous — дія тривала усю ніч.  Приклад: *By tomorrow morning, he will have been studying the whole night.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Perfect Continuous.  Формула: **will + have + been + V-ing**.  Маркери: all night, for hours, since yesterday.  Приклад: *By tomorrow, he will have been studying for 10 hours.*",
                ],
                'tense' => ['Future Perfect Continuous'],
                'level' => 'B2',
            ],
            [
                'question'  => 'By the end of this year, she {a1} French for three years.',
                'verb_hint' => ['a1' => '(learn)'],
                'options'   => ['will learn', 'will be learning', 'will have learned', 'will have been learning'],
                'answers'   => ['a1' => 'will have been learning'],
                'explanations' => [
                    'will learn' => "❌ Future Simple — факт.  Приклад: *She will learn French next year.*",
                    'will be learning' => "❌ Future Continuous — процес.  Приклад: *This time tomorrow, she will be learning French.*",
                    'will have learned' => "❌ Future Perfect — результат.  Приклад: *By next year, she will have learned French.*",
                    'will have been learning' => "✅ Future Perfect Continuous — акцент на тривалості.  Приклад: *By the end of this year, she will have been learning French for three years.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Perfect Continuous.  Формула: **will + have + been + V-ing**.  Приклад: *By next year, I will have been learning Spanish for 2 years.*",
                ],
                'tense' => ['Future Perfect Continuous'],
                'level' => 'B2',
            ],
            [
                'question'  => 'By next winter, we {a1} in this flat for ten years.',
                'verb_hint' => ['a1' => '(live)'],
                'options'   => ['will live', 'will be living', 'will have lived', 'will have been living'],
                'answers'   => ['a1' => 'will have been living'],
                'explanations' => [
                    'will live' => "❌ Future Simple — просто факт.  Приклад: *We will live in this flat next year.*",
                    'will be living' => "❌ Future Continuous — процес.  Приклад: *This time tomorrow, we will be living in this flat.*",
                    'will have lived' => "❌ Future Perfect — результат.  Приклад: *By next year, we will have lived here for 10 years.*",
                    'will have been living' => "✅ Future Perfect Continuous — підкреслює тривалість проживання.  Приклад: *By next winter, we will have been living here for ten years.*",
                ],
                'hints' => [
                    'a1' => "Час: Future Perfect Continuous.  Формула: **will + have + been + V-ing**.  Приклад: *By next winter, we will have been living here for a decade.*",
                ],
                'tense' => ['Future Perfect Continuous'],
                'level' => 'B2',
            ],
        ];

        $sections = [
            'mixed' => array_slice($rawQuestions, 0, 22),
            'duration' => array_slice($rawQuestions, 22),
        ];

        $levelDifficulty = [
            'A1' => 1,
            'A2' => 2,
            'B1' => 3,
            'B2' => 4,
            'C1' => 5,
            'C2' => 5,
        ];

        $detailByTense = [
            'Future Perfect' => 'future_perfect_completion',
            'Future Continuous' => 'future_continuous_in_progress',
            'Future Simple' => 'future_simple_plan',
            'Future Perfect Continuous' => 'future_perfect_continuous_duration',
        ];

        $tenseTags = [];
        foreach ($sections as $sectionQuestions) {
            foreach ($sectionQuestions as $question) {
                foreach ($question['tense'] as $tenseName) {
                    if (! isset($tenseTags[$tenseName])) {
                        $tenseTags[$tenseName] = Tag::firstOrCreate(['name' => $tenseName], ['category' => 'English Grammar Tense'])->id;
                    }
                }
            }
        }

        $service = new QuestionSeedingService();
        $items = [];
        $meta = [];

        foreach ($sections as $sectionKey => $sectionQuestions) {
            foreach ($sectionQuestions as $index => $question) {
                $uuid = $this->generateQuestionUuid($sectionKey, $index, $question['question']);

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

                $tagIds = [$themeTags[$sectionKey]];
                $detailKey = $detailByTense[$question['tense'][0] ?? ''] ?? null;
                if ($detailKey !== null && isset($detailTags[$detailKey])) {
                    $tagIds[] = $detailTags[$detailKey];
                }

                foreach ($question['tense'] as $tenseName) {
                    $tagIds[] = $tenseTags[$tenseName];
                }

                $items[] = [
                    'uuid' => $uuid,
                    'question' => str_replace(['____', '—'], ['{a1}', '—'], $question['question']),
                    'category_id' => $categoryId,
                    'difficulty' => $levelDifficulty[$question['level']] ?? 3,
                    'source_id' => $sectionSources[$sectionKey],
                    'flag' => 2,
                    'level' => $question['level'],
                    'tag_ids' => array_values(array_unique($tagIds)),
                    'answers' => $answers,
                    'options' => $question['options'],
                    'variants' => [],
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
            $parts[] = '{' . $marker . '} ' . trim($text);
        }

        return implode("\n", $parts);
    }
}
