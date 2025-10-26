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

class FutureTensesPracticeV2Seeder extends QuestionSeeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Future Tenses Practice V2'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Custom: Future Tenses Practice V2'])->id;

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Future Tenses Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTags = [
            'future_perfect' => Tag::firstOrCreate(['name' => 'Future Perfect Focus'], ['category' => 'English Grammar Detail'])->id,
            'future_continuous' => Tag::firstOrCreate(['name' => 'Future Continuous Focus'], ['category' => 'English Grammar Detail'])->id,
            'future_simple' => Tag::firstOrCreate(['name' => 'Future Simple Focus'], ['category' => 'English Grammar Detail'])->id,
            'future_perfect_continuous' => Tag::firstOrCreate(['name' => 'Future Perfect Continuous Focus'], ['category' => 'English Grammar Detail'])->id,
        ];

        $tenseTags = [
            'Future Perfect' => Tag::firstOrCreate(['name' => 'Future Perfect'], ['category' => 'Tenses'])->id,
            'Future Continuous' => Tag::firstOrCreate(['name' => 'Future Continuous'], ['category' => 'Tenses'])->id,
            'Future Simple' => Tag::firstOrCreate(['name' => 'Future Simple'], ['category' => 'Tenses'])->id,
            'Future Perfect Continuous' => Tag::firstOrCreate(['name' => 'Future Perfect Continuous'], ['category' => 'Tenses'])->id,
        ];

        $detailByTense = [
            'Future Perfect' => 'future_perfect',
            'Future Continuous' => 'future_continuous',
            'Future Simple' => 'future_simple',
            'Future Perfect Continuous' => 'future_perfect_continuous',
        ];

        $levelDifficulty = [
            'A1' => 1,
            'A2' => 2,
            'B1' => 3,
            'B2' => 4,
            'C1' => 5,
            'C2' => 5,
        ];

        $questions = [
            [
                'question'  => 'John {a1} the whole house by the time the guests arrive.',
                'verb_hint' => ['a1' => '(tidy up)'],
                'options'   => ['will have tidied up', 'will be tidying up', 'will have been tidying up', 'will tidy up'],
                'answers'   => ['a1' => 'will have tidied up'],
                'explanations' => [
                    'will have tidied up' => "✅ Future Perfect – дія завершиться до приходу гостей.  
Формула: **will + have + V3**.  
Приклад: *By the evening, she will have tidied up the room.*",
                    'will be tidying up' => "❌ Future Continuous – дія у процесі, не результат.  
Приклад: *At 5 pm, she will be tidying up.*",
                    'will have been tidying up' => "❌ Future Perfect Continuous – тривалість, тут не підходить.  
Приклад: *By the time you arrive, she will have been tidying up for an hour.*",
                    'will tidy up' => "❌ Future Simple – просто план, без зв’язку «раніше/пізніше».  
Приклад: *She will tidy up tomorrow.*",
                ],
                'hints' => [
                    'a1' => "Future Perfect = **will + have + V3**.  
Використовуємо, коли дія завершиться до іншої у майбутньому.  
Маркери: *by, by the time, before*.  
Приклад: *By next week, I will have finished the project.*",
                ],
                'tense' => ['Future Perfect'],
                'level' => 'B1',
            ],
            [
                'question'  => 'How long {a1} here by the end of next year?',
                'verb_hint' => ['a1' => '(work)'],
                'options'   => ['will you have been working', 'will you be working', 'will you have worked', 'will you work'],
                'answers'   => ['a1' => 'will you have been working'],
                'explanations' => [
                    'will you have been working' => "✅ Future Perfect Continuous – тривалість до майбутнього моменту.  
Формула: **will + have + been + V-ing**.  
Приклад: *How long will you have been working here by 2030?*",
                    'will you be working' => "❌ Future Continuous – процес у моменті, без тривалості.  
Приклад: *Will you be working at 5 pm tomorrow?*",
                    'will you have worked' => "❌ Future Perfect – результат, не тривалість.  
Приклад: *By next year, you will have worked here for 2 years.*",
                    'will you work' => "❌ Future Simple – загальний факт у майбутньому.  
Приклад: *Will you work here next year?*",
                ],
                'hints' => [
                    'a1' => "Future Perfect Continuous = **will + have + been + V-ing**.  
Маркери: *for, since, by the end of…*.  
Приклад: *By the end of 2025, I will have been studying English for 10 years.*",
                ],
                'tense' => ['Future Perfect Continuous'],
                'level' => 'B2',
            ],
            [
                'question'  => 'I {a1} the work by ten o’clock.',
                'verb_hint' => ['a1' => '(do)'],
                'options'   => ['will have done', 'will have been doing', 'will do', 'will be doing'],
                'answers'   => ['a1' => 'will have done'],
                'explanations' => [
                    'will have done' => "✅ Future Perfect – дія завершиться до 10-ї години.  
Формула: **will + have + V3**.  
Приклад: *By 9 pm, I will have done my homework.*",
                    'will have been doing' => "❌ Future Perfect Continuous – наголошує на процесі, не результаті.  
Приклад: *By 10 pm, I will have been doing this task for two hours.*",
                    'will do' => "❌ Future Simple – загальний факт.  
Приклад: *I will do it tomorrow.*",
                    'will be doing' => "❌ Future Continuous – процес у моменті.  
Приклад: *At 10 pm, I will be doing my homework.*",
                ],
                'hints' => [
                    'a1' => "Future Perfect = **will + have + V3**.  
Маркери: *by 10 o’clock, by tomorrow*.  
Приклад: *By the end of the day, she will have finished the report.*",
                ],
                'tense' => ['Future Perfect'],
                'level' => 'B1',
            ],
            [
                'question'  => 'I’m sure you {a1} me what I need to know.',
                'verb_hint' => ['a1' => '(tell)'],
                'options'   => ['will tell', 'will be telling', 'will have told', 'will have been telling'],
                'answers'   => ['a1' => 'will tell'],
                'explanations' => [
                    'will tell' => "✅ Future Simple – проста дія в майбутньому.  
Формула: **will + V1**.  
Приклад: *She will tell me the truth.*",
                    'will be telling' => "❌ Future Continuous – процес.  
Приклад: *At 5 pm, she will be telling a story.*",
                    'will have told' => "❌ Future Perfect – результат у майбутньому.  
Приклад: *By 6 pm, she will have told the news.*",
                    'will have been telling' => "❌ Future Perfect Continuous – дія у тривалості.  
Приклад: *By 6 pm, she will have been telling stories for an hour.*",
                ],
                'hints' => [
                    'a1' => "Future Simple = **will + V1**.  
Використовується для простих майбутніх дій.  
Приклад: *I will help you tomorrow.*",
                ],
                'tense' => ['Future Simple'],
                'level' => 'A2',
            ],
            [
                'question'  => 'I {a1} TV about this time tomorrow.',
                'verb_hint' => ['a1' => '(watch)'],
                'options'   => ['will be watching', 'will have watched', 'will have been watching', 'will watch'],
                'answers'   => ['a1' => 'will be watching'],
                'explanations' => [
                    'will be watching' => "✅ Future Continuous – дія у процесі в конкретний час.  
Формула: **will + be + V-ing**.  
Приклад: *At 8 pm tomorrow, I will be watching TV.*",
                    'will have watched' => "❌ Future Perfect – завершена дія.  
Приклад: *By 9 pm, I will have watched two films.*",
                    'will have been watching' => "❌ Future Perfect Continuous – дія у тривалості.  
Приклад: *By 9 pm, I will have been watching TV for 2 hours.*",
                    'will watch' => "❌ Future Simple – план.  
Приклад: *I will watch TV tomorrow.*",
                ],
                'hints' => [
                    'a1' => "Future Continuous = **will + be + V-ing**.  
Маркери: *this time tomorrow, at 5 pm tomorrow*.  
Приклад: *This time tomorrow, I will be working.*",
                ],
                'tense' => ['Future Continuous'],
                'level' => 'B1',
            ],
            [
                'question'  => 'By September I {a1} her for a whole year.',
                'verb_hint' => ['a1' => '(know)'],
                'options'   => ['will have known', 'will have been knowing', 'will be knowing', 'will know'],
                'answers'   => ['a1' => 'will have known'],
                'explanations' => [
                    'will have known' => "✅ Future Perfect – результат до певного моменту.  
Формула: **will + have + V3**.  
Приклад: *By next year, I will have known her for a year.*",
                    'will have been knowing' => "❌ Неправильна форма – дієслово *know* не використовується у Continuous.",
                    'will be knowing' => "❌ Future Continuous – некоректне з *know*.",
                    'will know' => "❌ Future Simple – загальний факт, не акцент на завершенні.  
Приклад: *I will know her tomorrow.*",
                ],
                'hints' => [
                    'a1' => "Future Perfect = **will + have + V3**.  
Маркери: *by, before*.  
Приклад: *By 2025, I will have known him for 10 years.*",
                ],
                'tense' => ['Future Perfect'],
                'level' => 'B2',
            ],
            [
                'question'  => 'He {a1} for five hours by noon.',
                'verb_hint' => ['a1' => '(work)'],
                'options'   => ['will have been working', 'will have worked', 'will work', 'will be working'],
                'answers'   => ['a1' => 'will have been working'],
                'explanations' => [
                    'will have been working' => "✅ Future Perfect Continuous – дія триває певний час до моменту.  
Формула: **will + have + been + V-ing**.  
Приклад: *By noon, he will have been working for 5 hours.*",
                    'will have worked' => "❌ Future Perfect – акцент на завершенні, не на тривалості.  
Приклад: *By noon, he will have worked here for 5 years.*",
                    'will work' => "❌ Future Simple – загальний факт.  
Приклад: *He will work tomorrow.*",
                    'will be working' => "❌ Future Continuous – процес у певний момент.  
Приклад: *At 10 am, he will be working.*",
                ],
                'hints' => [
                    'a1' => "Future Perfect Continuous = **will + have + been + V-ing**.  
Маркери: *for two hours, by noon, since morning*.  
Приклад: *By the evening, I will have been studying for 6 hours.*",
                ],
                'tense' => ['Future Perfect Continuous'],
                'level' => 'B2',
            ],
            [
                'question'  => 'I {a1} Harry Potter three times if I read it again.',
                'verb_hint' => ['a1' => '(read)'],
                'options'   => ['will have read', 'will have been reading', 'will be reading', 'will read'],
                'answers'   => ['a1' => 'will have read'],
                'explanations' => [
                    'will have read' => "✅ Future Perfect – дія буде завершена у майбутньому (три рази).  
Формула: **will + have + V3**.  
Приклад: *By next month, I will have read this book twice.*",
                    'will have been reading' => "❌ Future Perfect Continuous – наголошує на тривалості.  
Приклад: *By evening, I will have been reading for 2 hours.*",
                    'will be reading' => "❌ Future Continuous – процес.  
Приклад: *Tomorrow evening, I will be reading.*",
                    'will read' => "❌ Future Simple – загальний факт.  
Приклад: *I will read this book tomorrow.*",
                ],
                'hints' => [
                    'a1' => "Future Perfect = **will + have + V3**.  
Приклад: *By tomorrow, I will have read this article.*",
                ],
                'tense' => ['Future Perfect'],
                'level' => 'B1',
            ],
            [
                'question'  => 'The time may come when people {a1} all the oil.',
                'verb_hint' => ['a1' => '(use up)'],
                'options'   => ['will have used up', 'will be using', 'will use', 'will have been using'],
                'answers'   => ['a1' => 'will have used up'],
                'explanations' => [
                    'will have used up' => "✅ Future Perfect – до певного моменту все буде використано.  
Формула: **will + have + V3**.  
Приклад: *By 2050, people will have used up most of the resources.*",
                    'will be using' => "❌ Future Continuous – процес.  
Приклад: *At 5 pm, they will be using oil.*",
                    'will use' => "❌ Future Simple – загальний факт.  
Приклад: *People will use more energy in the future.*",
                    'will have been using' => "❌ Future Perfect Continuous – тривалість, не завершення.  
Приклад: *By 2050, people will have been using oil for 200 years.*",
                ],
                'hints' => [
                    'a1' => "Future Perfect = **will + have + V3**.  
Приклад: *By the time he arrives, I will have finished my work.*",
                ],
                'tense' => ['Future Perfect'],
                'level' => 'B2',
            ],
            [
                'question'  => 'I {a1} English for six years by the end of next month.',
                'verb_hint' => ['a1' => '(study)'],
                'options'   => ['will have been studying', 'will study', 'will be studying', 'will have studied'],
                'answers'   => ['a1' => 'will have been studying'],
                'explanations' => [
                    'will have been studying' => "✅ Future Perfect Continuous – дія триватиме шість років.  
Формула: **will + have + been + V-ing**.  
Приклад: *By next year, I will have been studying English for 7 years.*",
                    'will study' => "❌ Future Simple – план.  
Приклад: *I will study English tomorrow.*",
                    'will be studying' => "❌ Future Continuous – процес.  
Приклад: *At 5 pm, I will be studying.*",
                    'will have studied' => "❌ Future Perfect – результат, не тривалість.  
Приклад: *By June, I will have studied here for 2 years.*",
                ],
                'hints' => [
                    'a1' => "Future Perfect Continuous = **will + have + been + V-ing**.  
Приклад: *By 2026, I will have been studying for 10 years.*",
                ],
                'tense' => ['Future Perfect Continuous'],
                'level' => 'B2',
            ],
            [
                'question'  => '______ you {a1} your house off by the time you’re fifty?',
                'verb_hint' => ['a1' => '(pay)'],
                'options'   => ['Will you have paid', 'Will you pay', 'Will you be paying', 'Will you have been paying'],
                'answers'   => ['a1' => 'Will you have paid'],
                'explanations' => [
                    'Will you have paid' => "✅ Future Perfect – завершення до певного віку.  
Формула: **will + have + V3**.  
Приклад: *Will you have paid the debt by 2030?*",
                    'Will you pay' => "❌ Future Simple – загальне питання про майбутнє.  
Приклад: *Will you pay for it tomorrow?*",
                    'Will you be paying' => "❌ Future Continuous – процес.  
Приклад: *Will you be paying at 5 pm?*",
                    'Will you have been paying' => "❌ Future Perfect Continuous – тривалість.  
Приклад: *By 2030, will you have been paying for 20 years?*",
                ],
                'hints' => [
                    'a1' => "Future Perfect = **will + have + V3**.  
Приклад: *By 2025, will you have finished school?*",
                ],
                'tense' => ['Future Perfect'],
                'level' => 'B2',
            ],
            [
                'question'  => 'I {a1} over the Pacific about this time tomorrow.',
                'verb_hint' => ['a1' => '(fly)'],
                'options'   => ['will be flying', 'will have been flying', 'will fly', 'will have flown'],
                'answers'   => ['a1' => 'will be flying'],
                'explanations' => [
                    'will be flying' => "✅ Future Continuous – процес у певний момент.  
Формула: **will + be + V-ing**.  
Приклад: *This time tomorrow, I will be flying over the ocean.*",
                    'will have been flying' => "❌ Future Perfect Continuous – тривалість польоту.  
Приклад: *By 6 pm, I will have been flying for 2 hours.*",
                    'will fly' => "❌ Future Simple – план.  
Приклад: *I will fly to Paris next week.*",
                    'will have flown' => "❌ Future Perfect – результат.  
Приклад: *By 6 pm, I will have flown 500 miles.*",
                ],
                'hints' => [
                    'a1' => "Future Continuous = **will + be + V-ing**.  
Маркери: *this time tomorrow*.  
Приклад: *Tomorrow at 5 pm, I will be flying.*",
                ],
                'tense' => ['Future Continuous'],
                'level' => 'B1',
            ],
            [
                'question'  => 'John is very upset today. I {a1} to talk to him.',
                'verb_hint' => ['a1' => '(try)'],
                'options'   => ['will try', 'will be trying', 'will have tried', 'will have been trying'],
                'answers'   => ['a1' => 'will try'],
                'explanations' => [
                    'will try' => "✅ Future Simple – просте рішення у майбутньому.  
Формула: **will + V1**.  
Приклад: *I will try to help him tomorrow.*",
                    'will be trying' => "❌ Future Continuous – процес.  
Приклад: *At 5 pm, I will be trying to call him.*",
                    'will have tried' => "❌ Future Perfect – завершена дія.  
Приклад: *By tomorrow, I will have tried to fix it.*",
                    'will have been trying' => "❌ Future Perfect Continuous – тривалість.  
Приклад: *By evening, I will have been trying for hours.*",
                ],
                'hints' => [
                    'a1' => "Future Simple = **will + V1**.  
Приклад: *I will try again tomorrow.*",
                ],
                'tense' => ['Future Simple'],
                'level' => 'A2',
            ],
            [
                'question'  => 'He {a1} overtime every day next week.',
                'verb_hint' => ['a1' => '(work)'],
                'options'   => ['will be working', 'will work', 'will have worked', 'will have been working'],
                'answers'   => ['a1' => 'will be working'],
                'explanations' => [
                    'will be working' => "✅ Future Continuous – дія у процесі.  
Формула: **will + be + V-ing**.  
Приклад: *Next week, he will be working overtime.*",
                    'will work' => "❌ Future Simple – загальний факт.  
Приклад: *He will work next week.*",
                    'will have worked' => "❌ Future Perfect – результат.  
Приклад: *By Friday, he will have worked 40 hours.*",
                    'will have been working' => "❌ Future Perfect Continuous – тривалість.  
Приклад: *By Friday, he will have been working all week.*",
                ],
                'hints' => [
                    'a1' => "Future Continuous = **will + be + V-ing**.  
Приклад: *This time tomorrow, I will be working.*",
                ],
                'tense' => ['Future Continuous'],
                'level' => 'B1',
            ],
            [
                'question'  => 'Don’t call me between 3 and 4 because I {a1} a meeting then.',
                'verb_hint' => ['a1' => '(have)'],
                'options'   => ['will be having', 'will have', 'will have been having', 'will have had'],
                'answers'   => ['a1' => 'will be having'],
                'explanations' => [
                    'will be having' => "✅ Future Continuous – процес у конкретний час.  
Формула: **will + be + V-ing**.  
Приклад: *At 3 pm, I will be having a meeting.*",
                    'will have' => "❌ Future Simple – факт.  
Приклад: *I will have a meeting tomorrow.*",
                    'will have been having' => "❌ Future Perfect Continuous – тривалість.  
Приклад: *By 4 pm, I will have been having a meeting for an hour.*",
                    'will have had' => "❌ Future Perfect – результат.  
Приклад: *By 4 pm, I will have had a meeting.*",
                ],
                'hints' => [
                    'a1' => "Future Continuous = **will + be + V-ing**.  
Приклад: *Tomorrow at 3 pm, I will be having a lesson.*",
                ],
                'tense' => ['Future Continuous'],
                'level' => 'B1',
            ],
            [
                'question'  => 'By 2060 the Earth {a1} electricity generated by powerstations on the Moon.',
                'verb_hint' => ['a1' => '(use)'],
                'options'   => ['will be using', 'will have used', 'will have been using', 'will use'],
                'answers'   => ['a1' => 'will be using'],
                'explanations' => [
                    'will be using' => "✅ Future Continuous – процес використання.  
Формула: **will + be + V-ing**.  
Приклад: *By 2060, people will be using new technologies.*",
                    'will have used' => "❌ Future Perfect – завершення, не процес.  
Приклад: *By 2060, people will have used all oil.*",
                    'will have been using' => "❌ Future Perfect Continuous – тривалість.  
Приклад: *By 2060, they will have been using solar power for decades.*",
                    'will use' => "❌ Future Simple – загальний факт.  
Приклад: *In the future, people will use more solar energy.*",
                ],
                'hints' => [
                    'a1' => "Future Continuous = **will + be + V-ing**.  
Приклад: *In 20 years, we will be using new sources of energy.*",
                ],
                'tense' => ['Future Continuous'],
                'level' => 'B2',
            ],
            [
                'question'  => 'Sorry, but I can’t come at 5. I {a1} football with my mates for the whole day.',
                'verb_hint' => ['a1' => '(play)'],
                'options'   => ['will be playing', 'will have played', 'will have been playing', 'will play'],
                'answers'   => ['a1' => 'will have been playing'],
                'explanations' => [
                    'will have been playing' => "✅ Future Perfect Continuous – дія триватиме увесь день.  
Формула: **will + have + been + V-ing**.  
Приклад: *By evening, I will have been playing football for 5 hours.*",
                    'will be playing' => "❌ Future Continuous – процес у моменті.  
Приклад: *At 5 pm, I will be playing football.*",
                    'will have played' => "❌ Future Perfect – завершення.  
Приклад: *By 5 pm, I will have played two matches.*",
                    'will play' => "❌ Future Simple – загальний факт.  
Приклад: *I will play football tomorrow.*",
                ],
                'hints' => [
                    'a1' => "Future Perfect Continuous = **will + have + been + V-ing**.  
Приклад: *By evening, we will have been playing all day.*",
                ],
                'tense' => ['Future Perfect Continuous'],
                'level' => 'B2',
            ],
            [
                'question'  => 'By 11 a.m. tomorrow, I {a1} an exam for sixty minutes.',
                'verb_hint' => ['a1' => '(do)'],
                'options'   => ['will have been doing', 'will have done', 'will do', 'will be doing'],
                'answers'   => ['a1' => 'will have been doing'],
                'explanations' => [
                    'will have been doing' => "✅ Future Perfect Continuous – дія триватиме годину.  
Формула: **will + have + been + V-ing**.  
Приклад: *By 11 am, I will have been doing the exam for an hour.*",
                    'will have done' => "❌ Future Perfect – завершення.  
Приклад: *By 11 am, I will have done the exam.*",
                    'will do' => "❌ Future Simple – загальний факт.  
Приклад: *I will do the exam tomorrow.*",
                    'will be doing' => "❌ Future Continuous – процес у моменті.  
Приклад: *At 11 am, I will be doing the exam.*",
                ],
                'hints' => [
                    'a1' => "Future Perfect Continuous = **will + have + been + V-ing**.  
Приклад: *By tomorrow, I will have been working for 5 hours.*",
                ],
                'tense' => ['Future Perfect Continuous'],
                'level' => 'B2',
            ],
            [
                'question'  => 'He {a1} for lunch by 1.30.',
                'verb_hint' => ['a1' => '(leave)'],
                'options'   => ['will have left', 'will have been lefting', 'will leave', 'will be leaving'],
                'answers'   => ['a1' => 'will have left'],
                'explanations' => [
                    'will have left' => "✅ Future Perfect – дія завершиться до 1:30.  
Формула: **will + have + V3**.  
Приклад: *By 2 pm, he will have left the office.*",
                    'will have been lefting' => "❌ Некоректна форма – *leave* не утворює *lefting*.",
                    'will leave' => "❌ Future Simple – факт.  
Приклад: *He will leave at 2 pm.*",
                    'will be leaving' => "❌ Future Continuous – процес.  
Приклад: *At 1.30, he will be leaving.*",
                ],
                'hints' => [
                    'a1' => "Future Perfect = **will + have + V3**.  
Приклад: *By 8 pm, she will have left home.*",
                ],
                'tense' => ['Future Perfect'],
                'level' => 'B1',
            ],
            [
                'question'  => 'I’m worried that I {a1} with work by six.',
                'verb_hint' => ['a1' => '(not finish)'],
                'options'   => ['won’t have finished', 'won’t be finishing', 'won’t have been finishing', 'won’t finish'],
                'answers'   => ['a1' => 'won’t have finished'],
                'explanations' => [
                    'won’t have finished' => "✅ Future Perfect (negative) – дія не завершиться.  
Формула: **will not + have + V3**.  
Приклад: *I won’t have finished the report by 5 pm.*",
                    'won’t be finishing' => "❌ Future Continuous (negative) – процес.  
Приклад: *At 5 pm, I won’t be finishing the report.*",
                    'won’t have been finishing' => "❌ Future Perfect Continuous (negative) – рідко вживається.",
                    'won’t finish' => "❌ Future Simple (negative) – просто факт.  
Приклад: *I won’t finish it tomorrow.*",
                ],
                'hints' => [
                    'a1' => "Future Perfect = **will (not) + have + V3**.  
Приклад: *By the deadline, I won’t have finished the task.*",
                ],
                'tense' => ['Future Perfect'],
                'level' => 'B2',
            ],
            [
                'question'  => 'By the time Harry {a1}, his friend {a2} his presents.',
                'verb_hint' => ['a1' => '(wake up)', 'a2' => '(open)'],
                'options'   => [
                    'a1' => ['will have woken up'],
                    'a2' => ['will have opened', 'will have been opening', 'will be opening'],
                ],
                'answers'   => ['a1' => 'will have woken up', 'a2' => 'will have opened'],
                'explanations' => [
                    'will have woken up' => "✅ Future Perfect – дія завершиться.  
Формула: **will + have + V3**.  
Приклад: *By 8 am, he will have woken up.*",
                    'will have opened' => "✅ Future Perfect – подарунки вже відкриті.  
Приклад: *By the time you arrive, I will have opened the gift.*",
                    'will have been opening' => "❌ Future Perfect Continuous – тривалість, тут не потрібна.",
                    'will be opening' => "❌ Future Continuous – процес.  
Приклад: *At 9 am, he will be opening his presents.*",
                ],
                'hints' => [
                    'a1' => "Future Perfect = **will + have + V3**.  
Приклад: *By next week, I will have woken up earlier every day.*",
                    'a2' => "Future Perfect = **will + have + V3**.  
Приклад: *By the time you come, I will have opened the book.*",
                ],
                'tense' => ['Future Perfect'],
                'level' => 'B2',
            ],
            [
                'question'  => 'Ron {a1} sweets for some time when Harry finds a postcard.',
                'verb_hint' => ['a1' => '(eat)'],
                'options'   => ['will have been eating', 'will have eaten', 'will eat', 'will be eating'],
                'answers'   => ['a1' => 'will have been eating'],
                'explanations' => [
                    'will have been eating' => "✅ Future Perfect Continuous – дія тривала певний час.  
Формула: **will + have + been + V-ing**.  
Приклад: *He will have been eating for an hour when she arrives.*",
                    'will have eaten' => "❌ Future Perfect – завершення, а не тривалість.  
Приклад: *By 6 pm, he will have eaten dinner.*",
                    'will eat' => "❌ Future Simple – загальний факт.  
Приклад: *He will eat sweets tomorrow.*",
                    'will be eating' => "❌ Future Continuous – процес у моменті.  
Приклад: *At 5 pm, he will be eating.*",
                ],
                'hints' => [
                    'a1' => "Future Perfect Continuous = **will + have + been + V-ing**.  
Приклад: *By the time you come, he will have been eating for an hour.*",
                ],
                'tense' => ['Future Perfect Continuous'],
                'level' => 'B2',
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

            $tagIds = [$themeTagId];
            foreach ($question['tense'] as $tenseName) {
                if (isset($tenseTags[$tenseName])) {
                    $tagIds[] = $tenseTags[$tenseName];
                }

                $detailKey = $detailByTense[$tenseName] ?? null;
                if ($detailKey && isset($detailTags[$detailKey])) {
                    $tagIds[] = $detailTags[$detailKey];
                }
            }

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

    protected function formatHints(array $hints): ?string
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
