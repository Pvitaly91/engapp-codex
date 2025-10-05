<?php

namespace Database\Seeders\Ai;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Source;
use App\Models\Tag;
use App\Services\QuestionSeedingService;
use App\Support\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class MixedPerfectTenseDetailedSeeder extends Seeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Mixed Perfect Tense Detailed Test'])->id;
        $sourceId   = Source::firstOrCreate(['name' => 'Mixed Perfect Choices with Detailed Explanations'])->id;

        $grammarTags = [
            'duration_result'      => Tag::firstOrCreate(['name' => 'Duration vs Result'], ['category' => 'Grammar Detail'])->id,
            'past_time_reference'  => Tag::firstOrCreate(['name' => 'Past Time Reference'], ['category' => 'Grammar Detail'])->id,
            'since_usage'          => Tag::firstOrCreate(['name' => 'Since and For Usage'], ['category' => 'Grammar Detail'])->id,
            'ongoing_project'      => Tag::firstOrCreate(['name' => 'Ongoing Actions'], ['category' => 'Grammar Detail'])->id,
            'questions_with_yet'   => Tag::firstOrCreate(['name' => 'Questions with Yet'], ['category' => 'Grammar Detail'])->id,
            'past_simple_ago'      => Tag::firstOrCreate(['name' => 'Past Simple with Ago'], ['category' => 'Grammar Detail'])->id,
            'present_result'       => Tag::firstOrCreate(['name' => 'Present Result Situations'], ['category' => 'Grammar Detail'])->id,
            'negative_with_yet'    => Tag::firstOrCreate(['name' => 'Negative with Yet'], ['category' => 'Grammar Detail'])->id,
            'waiting_duration'     => Tag::firstOrCreate(['name' => 'Waiting Duration'], ['category' => 'Grammar Detail'])->id,
            'already_usage'        => Tag::firstOrCreate(['name' => 'Already Statements'], ['category' => 'Grammar Detail'])->id,
            'habit_change'         => Tag::firstOrCreate(['name' => 'Habit Changes'], ['category' => 'Grammar Detail'])->id,
            'limited_activity'     => Tag::firstOrCreate(['name' => 'Limited Activity Emphasis'], ['category' => 'Grammar Detail'])->id,
            'duration_gap'         => Tag::firstOrCreate(['name' => 'Duration Emphasis'], ['category' => 'Grammar Detail'])->id,
            'experience_gap'       => Tag::firstOrCreate(['name' => 'Experience Gaps'], ['category' => 'Grammar Detail'])->id,
            'long_term_action'     => Tag::firstOrCreate(['name' => 'Long-Term Actions'], ['category' => 'Grammar Detail'])->id,
            'injury_result'        => Tag::firstOrCreate(['name' => 'Injury Results'], ['category' => 'Grammar Detail'])->id,
            'interrupted_action'   => Tag::firstOrCreate(['name' => 'Interrupted Actions'], ['category' => 'Grammar Detail'])->id,
            'prior_sequence'       => Tag::firstOrCreate(['name' => 'Prior Past Actions'], ['category' => 'Grammar Detail'])->id,
            'present_evidence'     => Tag::firstOrCreate(['name' => 'Present Evidence'], ['category' => 'Grammar Detail'])->id,
        ];

        $contextTags = [
            'housework'      => Tag::firstOrCreate(['name' => 'Housework Situations'], ['category' => 'Usage Context'])->id,
            'childhood'      => Tag::firstOrCreate(['name' => 'Childhood Memories'], ['category' => 'Usage Context'])->id,
            'relationships'  => Tag::firstOrCreate(['name' => 'Relationships'], ['category' => 'Usage Context'])->id,
            'renovation'     => Tag::firstOrCreate(['name' => 'Home Renovation'], ['category' => 'Usage Context'])->id,
            'family'         => Tag::firstOrCreate(['name' => 'Family Life'], ['category' => 'Usage Context'])->id,
            'departures'     => Tag::firstOrCreate(['name' => 'Travel Departures'], ['category' => 'Usage Context'])->id,
            'locked_out'     => Tag::firstOrCreate(['name' => 'Home Access Problems'], ['category' => 'Usage Context'])->id,
            'understanding'  => Tag::firstOrCreate(['name' => 'Understanding Issues'], ['category' => 'Usage Context'])->id,
            'commuting'      => Tag::firstOrCreate(['name' => 'Commuting'], ['category' => 'Usage Context'])->id,
            'reading'        => Tag::firstOrCreate(['name' => 'Reading & Study'], ['category' => 'Usage Context'])->id,
            'beverages'      => Tag::firstOrCreate(['name' => 'Beverage Habits'], ['category' => 'Usage Context'])->id,
            'media'          => Tag::firstOrCreate(['name' => 'Entertainment Habits'], ['category' => 'Usage Context'])->id,
            'technology'     => Tag::firstOrCreate(['name' => 'Technology Use'], ['category' => 'Usage Context'])->id,
            'driving'        => Tag::firstOrCreate(['name' => 'Driving & Transport'], ['category' => 'Usage Context'])->id,
            'living'         => Tag::firstOrCreate(['name' => 'Living in Cities'], ['category' => 'Usage Context'])->id,
            'health'         => Tag::firstOrCreate(['name' => 'Health & Injuries'], ['category' => 'Usage Context'])->id,
            'meals'          => Tag::firstOrCreate(['name' => 'Meals & Dining'], ['category' => 'Usage Context'])->id,
            'train_travel'   => Tag::firstOrCreate(['name' => 'Train Travel'], ['category' => 'Usage Context'])->id,
            'borrowing'      => Tag::firstOrCreate(['name' => 'Borrowing & Sharing'], ['category' => 'Usage Context'])->id,
            'incidents'      => Tag::firstOrCreate(['name' => 'Home Incidents'], ['category' => 'Usage Context'])->id,
        ];
  
        $questions = [
            [ 
                'question'  => 'Jane {a1} in the house for hours. He {a2} three rooms so far.',
                'verb_hint' => ['a1' => '(work)', 'a2' => '(clean)'],
                'options'   => [
                    'a1' => ['has worked', 'has been working'],
                    'a2' => ['has cleaned', 'has been cleaning'],
                ],
                'answers'   => ['a1' => 'has been working', 'a2' => 'has cleaned'],
                'explanations' => [
                    'has worked' => "❌ Present Perfect Simple: have/has + V3. Хоча 'has worked' граматично правильно, воно підкреслює лише факт роботи. Вираз 'for hours' вимагає акценту на тривалості, тому Continuous доречніший.",
                    'has been working' => "✅ Present Perfect Continuous: have/has + been + V-ing. Оскільки 'Jane' у третій особі однини → 'has been working'. Вираз 'for hours' прямо вказує на тривалість процесу, тому це правильний вибір.",
                    'has cleaned' => "✅ Present Perfect Simple: have/has + V3. Підмет 'he' → 'has cleaned'. Маркер 'so far' вказує на результат, тому ця форма правильна.",
                    'has been cleaning' => "❌ Present Perfect Continuous: have/has + been + V-ing. Але тут йдеться не про сам процес, а про результат — три кімнати вже закінчені. Тому Continuous недоречний.",
                ],
                'hints' => [
                    'a1' => "Present Perfect Continuous: have/has + been + V-ing → для тривалості ('for hours').",
                    'a2' => "Present Perfect Simple: have/has + V3 → для результату ('so far').",
                ], 
                'level' => 'B2',
                'tense' => ['Present Perfect Continuous','Present Perfect Simple'],
                'variants' => [
                    'Emma {a1} in the kitchen since morning. She {a2} three cakes so far.',
                    'Lucas {a1} in the garden all afternoon. He {a2} five trees already.',
                    'Sophie {a1} at her desk all day. She {a2} four reports so far.',
                    'David {a1} in the workshop for hours. He {a2} two chairs already.',
                    'Anna {a1} on her computer since noon. She {a2} three documents so far.',
                ],
                'grammar_key' => 'duration_result',
                'context_key' => 'housework',
            ],
            [
                'question'  => 'He {a1} there when he was a child.',
                'verb_hint' => ['a1' => '(live)'],
                'options'   => ['has lived', 'lived', 'has been living'],
                'answers'   => ['a1' => 'lived'],
                'explanations' => [
                    'has lived' => "❌ Present Perfect Simple: have/has + V3. Використовується для досвіду, актуального зараз. Але 'when he was a child' → завершений період у минулому, тому ця форма не підходить.",
                    'lived' => "✅ Past Simple: V2. Вираз 'when he was a child' — типовий маркер завершеного минулого, тому правильно 'He lived there'.",
                    'has been living' => "❌ Present Perfect Continuous: have/has + been + V-ing. Означає процес, що триває й досі. Але дія повністю завершена ('when he was a child').",
                ],
                'hints' => [
                    'a1' => "Past Simple: V2 → для завершених дій з маркером минулого ('when').",
                ],
                'level' => 'A2',
                'tense' => ['Past Simple'],
                'variants' => [
                    'She {a1} in London when she was at school.',
                    'My parents {a1} in that town twenty years ago.',
                    'We {a1} in Spain when I was little.',
                    'They {a1} in the countryside in their childhood.',
                    'Tom {a1} in Paris during his university years.',
                ],
                'grammar_key' => 'past_time_reference',
                'context_key' => 'childhood',
            ],
            [
                'question'  => 'I {a1} her since last year.',
                'verb_hint' => ['a1' => '(not/see)'],
                'options'   => ["haven't seen", "didn't see", "haven't been seeing"],
                'answers'   => ['a1' => "haven't seen"],
                'explanations' => [
                    "haven't seen" => "✅ Present Perfect Simple: have/has + not + V3. 'Since last year' показує період від минулого до тепер, тому 'I haven't seen her since last year' є правильною формою.",
                    "didn't see" => "❌ Past Simple: V2. Використовується для завершених дій, але 'since last year' означає період, що досі триває. Тому неправильно.",
                    "haven't been seeing" => "❌ Present Perfect Continuous: have/has + been + V-ing. Дієслово 'see' є статичним, і Continuous звучить неприродно.",
                ],
                'hints' => [
                    'a1' => "Present Perfect Simple: have/has + not + V3. З 'since'/'for'.",
                ],
                'level' => 'B1',
                'tense' => ['Present Perfect Simple'],
                'variants' => [
                    'I {a1} him since January.',
                    'We {a1} our teacher since the exam.',
                    'They {a1} my parents since Christmas.',
                    'She {a1} her cousin since last year.',
                    'I {a1} my best friend since we finished school.',
                ],
                'grammar_key' => 'since_usage',
                'context_key' => 'relationships',
            ],
            [
                'question'  => 'They {a1} this room for a month. I’m sure they will never be ready with it.',
                'verb_hint' => ['a1' => '(decorate)'],
                'options'   => ['have decorated', 'decorated', 'have been decorating'],
                'answers'   => ['a1' => 'have been decorating'],
                'explanations' => [
                    'have decorated' => "❌ Present Perfect Simple: have/has + V3. Використовується для завершених дій. Але робота ще триває, тому Simple тут не підходить.",
                    'decorated' => "❌ Past Simple: V2. Використовується для завершених минулих подій. У реченні процес ще не закінчився, тому Past Simple невірний.",
                    'have been decorating' => "✅ Present Perfect Continuous: have/has + been + V-ing. 'For a month' → акцент на тривалості дії, яка ще триває. Це правильний вибір.",
                ],
                'hints' => [
                    'a1' => 'Present Perfect Continuous: have/has + been + V-ing → для тривалих процесів.',
                ],
                'level' => 'B2',
                'tense' => ['Present Perfect Continuous'],
                'variants' => [
                    'We {a1} this project for weeks.',
                    'He {a1} the car for two hours.',
                    'She {a1} that essay all day.',
                    'They {a1} the walls since last Monday.',
                    'I {a1} my presentation for three days.',
                ],
                'grammar_key' => 'ongoing_project',
                'context_key' => 'renovation',
            ],
            [
                'question'  => 'Dad, {a1} reading the paper yet?',
                'verb_hint' => ['a1' => '(finish)'],
                'options'   => ['did you finish', 'have you finished', 'are you finishing'],
                'answers'   => ['a1' => 'have you finished'],
                'explanations' => [
                    'did you finish' => "❌ Past Simple: did + V1. Але слово 'yet' показує зв’язок із теперішнім моментом, тому потрібно Present Perfect.",
                    'have you finished' => "✅ Present Perfect Simple: have/has + V3. Вживається у питаннях із 'yet' для дій, які могли завершитися до теперішнього моменту.",
                    'are you finishing' => "❌ Present Continuous: be + V-ing. Використовується для процесу, але питання тут про завершення дії.",
                ],
                'hints' => [
                    'a1' => "Present Perfect Simple: have/has + V3 → питання із 'yet'.",
                ],
                'level' => 'B1',
                'tense' => ['Present Perfect Simple'],
                'variants' => [
                    'Mom, {a1} cooking dinner yet?',
                    'Teacher, {a1} checking our tests yet?',
                    'Ben, {a1} your homework yet?',
                    'Guys, {a1} cleaning the room yet?',
                    'Anna, {a1} writing the letter yet?',
                ],
                'grammar_key' => 'questions_with_yet',
                'context_key' => 'family',
            ],
            [
                'question'  => 'They {a1} a few minutes ago.',
                'verb_hint' => ['a1' => '(leave)'],
                'options'   => ['left', 'have just left', 'have been leaving'],
                'answers'   => ['a1' => 'left'],
                'explanations' => [
                    'left' => "✅ Past Simple: V2. Маркер 'ago' завжди вимагає Past Simple, оскільки це завершений момент у минулому.",
                    'have just left' => "❌ Present Perfect Simple: have/has + just + V3. Правильне з 'just', але не з 'ago'.",
                    'have been leaving' => "❌ Present Perfect Continuous: have/has + been + V-ing. Continuous не використовується з одноразовою дією 'leave'.",
                ],
                'hints' => [
                    'a1' => "Past Simple: V2 → з маркером 'ago'.",
                ],
                'level' => 'A2',
                'tense' => ['Past Simple'],
                'variants' => [
                    'She {a1} ten minutes ago.',
                    'We {a1} school an hour ago.',
                    'My friends {a1} the party a while ago.',
                    'They {a1} home five minutes ago.',
                    'He {a1} the office two hours ago.',
                ],
                'grammar_key' => 'past_simple_ago',
                'context_key' => 'departures',
            ],
            [
                'question'  => 'I can’t get into my house because I {a1} my keys.',
                'verb_hint' => ['a1' => '(lose)'],
                'options'   => ['lost', 'have been losing', 'have lost'],
                'answers'   => ['a1' => 'have lost'],
                'explanations' => [
                    'lost' => "❌ Past Simple: V2. Хоча 'I lost my keys' граматично правильне, але тут важливий наслідок у теперішньому (я не можу потрапити в дім). Тому потрібно Present Perfect.",
                    'have been losing' => "❌ Present Perfect Continuous: have/has + been + V-ing. Continuous означає повторювану або тривалу дію. Але тут один конкретний випадок втрати.",
                    'have lost' => "✅ Present Perfect Simple: have/has + V3. Показує дію, яка має теперішній результат (ключів немає зараз).",
                ],
                'hints' => [
                    'a1' => 'Present Perfect Simple: have/has + V3 → для дій із результатом у теперішньому.',
                ],
                'level' => 'B1',
                'tense' => ['Present Perfect Simple'],
                'variants' => [
                    'I can’t find my phone because I {a1} it.',
                    'He can’t open the door because he {a1} the key.',
                    'She can’t pay because she {a1} her wallet.',
                    'We can’t log in because we {a1} the password.',
                    'They can’t get home because they {a1} the tickets.',
                ],
                'grammar_key' => 'present_result',
                'context_key' => 'locked_out',
            ],
            [
                'question'  => 'My mom {a1} the problem yet.',
                'verb_hint' => ['a1' => '(understand/not)'],
                'options'   => ["hasn't been understanding", 'has not understood', "didn't understand"],
                'answers'   => ['a1' => 'has not understood'],
                'explanations' => [
                    "hasn't been understanding" => "❌ Present Perfect Continuous: have/has + not + been + V-ing. Але дієслово 'understand' є статичним, тому Continuous тут звучить неприродно.",
                    'has not understood' => "✅ Present Perfect Simple: have/has + not + V3. У третій особі однини → 'has not understood'. Маркер 'yet' вказує на дію, яка досі не завершена. Це правильна форма.",
                    "didn't understand" => "❌ Past Simple: V2. Означає завершену дію в минулому, але з 'yet' говоримо про теперішній результат. Тому неправильно.",
                ],
                'hints' => [
                    'a1' => "Present Perfect Simple (negative): have/has + not + V3 → із 'yet'.",
                ],
                'level' => 'B1',
                'tense' => ['Present Perfect Simple'],
                'variants' => [
                    'Dad {a1} the question yet.',
                    'Anna {a1} the rules yet.',
                    'Tom {a1} the instructions yet.',
                    'My brother {a1} the message yet.',
                    'She {a1} the situation yet.',
                ],
                'grammar_key' => 'negative_with_yet',
                'context_key' => 'understanding',
            ],
            [
                'question'  => 'She {a1} for the bus for twenty minutes.',
                'verb_hint' => ['a1' => '(wait)'],
                'options'   => ['has waited', 'waited', 'has been waiting'],
                'answers'   => ['a1' => 'has been waiting'],
                'explanations' => [
                    'has waited' => "❌ Present Perfect Simple: have/has + V3. Ця форма підкреслює завершеність, але очікування ще триває.",
                    'waited' => "❌ Past Simple: V2. Це описувало б дію у минулому, а тут дія триває зараз.",
                    'has been waiting' => "✅ Present Perfect Continuous: have/has + been + V-ing. Вираз 'for twenty minutes' вказує на тривалість дії від минулого до тепер. Це правильна форма.",
                ],
                'hints' => [
                    'a1' => "Present Perfect Continuous: have/has + been + V-ing → для тривалості ('for').",
                ],
                'level' => 'B1',
                'tense' => ['Present Perfect Continuous'],
                'variants' => [
                    'He {a1} for the train for half an hour.',
                    'We {a1} for the teacher since 9 a.m.',
                    'They {a1} at the station for two hours.',
                    'I {a1} outside the shop since morning.',
                    'Anna {a1} in the queue for fifteen minutes.',
                ],
                'grammar_key' => 'waiting_duration',
                'context_key' => 'commuting',
            ],
            [
                'question'  => 'We {a1} this book already.',
                'verb_hint' => ['a1' => '(read)'],
                'options'   => ['have read', 'read', 'have been reading'],
                'answers'   => ['a1' => 'have read'],
                'explanations' => [
                    'have read' => "✅ Present Perfect Simple: have/has + V3. 'Already' показує, що дія завершена до теперішнього моменту.",
                    'read' => "❌ Past Simple: V2. Використовується для дій у минулому з маркером часу, якого тут немає.",
                    'have been reading' => "❌ Present Perfect Continuous: have/has + been + V-ing. Ця форма описувала б процес, але 'already' підкреслює завершений результат.",
                ],
                'hints' => [
                    'a1' => "Present Perfect Simple: have/has + V3 → із 'already'.",
                ],
                'level' => 'A2',
                'tense' => ['Present Perfect Simple'],
                'variants' => [
                    'I {a1} this magazine already.',
                    'She {a1} the novel already.',
                    'We {a1} that article already.',
                    'They {a1} the story already.',
                    'He {a1} the letter already.',
                ],
                'grammar_key' => 'already_usage',
                'context_key' => 'reading',
            ],
            [
                'question'  => 'Mark {a1} coffee for three months. He switched to tea.',
                'verb_hint' => ['a1' => '(drink/not)'],
                'options'   => ["hasn't drunk", "didn't drink", "hasn't been drinking"],
                'answers'   => ['a1' => "hasn't drunk"],
                'explanations' => [
                    "hasn't drunk" => "✅ Present Perfect Simple (negative): have/has + not + V3. Маркер 'for three months' показує період до теперішнього моменту. Правильна форма — 'hasn't drunk'.",
                    "didn't drink" => "❌ Past Simple: V2. Використовується для завершених дій, але 'for three months' охоплює період, що триває. Тому Past Simple не підходить.",
                    "hasn't been drinking" => "❌ Present Perfect Continuous (negative): have/has + not + been + V-ing. У поєднанні з 'switched to tea' йдеться про зміну стану (результат), а не про тривалість. Тому Continuous не доречний.",
                ],
                'hints' => [
                    'a1' => "Present Perfect Simple (negative): have/has + not + V3 → із 'for'.",
                ],
                'level' => 'B2',
                'tense' => ['Present Perfect Simple'],
                'variants' => [
                    'Emma {a1} milk for weeks. She drinks juice instead.',
                    'Tom {a1} soda for a month. He prefers water.',
                    'We {a1} alcohol since January. We decided to stop.',
                    'Anna {a1} tea for a year. She prefers coffee.',
                    'He {a1} cola recently. He switched to mineral water.',
                ],
                'grammar_key' => 'habit_change',
                'context_key' => 'beverages',
            ],
            [
                'question'  => 'I {a1} TV this week – just today for half an hour.',
                'verb_hint' => ['a1' => '(watch/not)'],
                'options'   => ["haven't watched", "haven't been watching", "didn't watch", "wasn't watching"],
                'answers'   => ['a1' => "haven't been watching"],
                'explanations' => [
                    "haven't watched" => "❌ Present Perfect Simple: have/has + not + V3. Хоча граматично правильно, але тут акцент на процесі, а не на повній відсутності дії.",
                    "haven't been watching" => "✅ Present Perfect Continuous (negative): have/has + not + been + V-ing. Маркер 'this week' показує період, що ще триває. Тому правильна форма — 'haven't been watching'.",
                    "didn't watch" => "❌ Past Simple: V2. Використовується для завершених періодів ('yesterday'), але 'this week' все ще триває.",
                    "wasn't watching" => "❌ Past Continuous: was/were + not + V-ing. Використовується для дій у конкретний момент у минулому. Тут мова про довший період.",
                ],
                'hints' => [
                    'a1' => "Present Perfect Continuous (negative): have/has + not + been + V-ing → для періоду 'this week'.",
                ],
                'level' => 'B2',
                'tense' => ['Present Perfect Continuous'],
                'variants' => [
                    'I {a1} movies this month – only short clips.',
                    'She {a1} series recently – she’s been busy.',
                    'We {a1} YouTube these days – no free time.',
                    'They {a1} films this week – only news.',
                    'He {a1} cartoons lately – just homework.',
                ],
                'grammar_key' => 'limited_activity',
                'context_key' => 'media',
            ],
            [
                'question'  => 'I {a1} the computer for half an hour, only for about 5 minutes.',
                'verb_hint' => ['a1' => '(play/not)'],
                'options'   => ["haven't played", "haven't been playing", "didn't play", "wasn't playing"],
                'answers'   => ['a1' => "haven't been playing"],
                'explanations' => [
                    "haven't played" => "❌ Present Perfect Simple (negative): have/has + not + V3. Показує лише факт відсутності, але тут акцент на процесі.",
                    "haven't been playing" => "✅ Present Perfect Continuous (negative): have/has + not + been + V-ing. Вираз 'for half an hour' → тривалість. Це правильна форма.",
                    "didn't play" => "❌ Past Simple: V2. Використовується для завершеного минулого, а тут ідеться про теперішній період.",
                    "wasn't playing" => "❌ Past Continuous: was/were + not + V-ing. Описує момент у минулому, а не теперішню відсутність дії.",
                ],
                'hints' => [
                    'a1' => "Present Perfect Continuous (negative): have/has + not + been + V-ing → для тривалості ('for half an hour').",
                ],
                'level' => 'B2',
                'tense' => ['Present Perfect Continuous'],
                'variants' => [
                    'I {a1} games this evening – just reading.',
                    'He {a1} football this week – only basketball.',
                    'We {a1} chess today – only talking.',
                    'She {a1} the piano lately – she’s busy.',
                    'They {a1} video games this month – just studying.',
                ],
                'grammar_key' => 'duration_gap',
                'context_key' => 'technology',
            ],
            [
                'question'  => 'Bob {a1} a car for eight years.',
                'verb_hint' => ['a1' => '(drive/not)'],
                'options'   => ["hasn't driven", "hasn't been driving", "didn't drive", "wasn't driving"],
                'answers'   => ['a1' => "hasn't driven"],
                'explanations' => [
                    "hasn't driven" => "✅ Present Perfect Simple (negative): have/has + not + V3. 'For eight years' показує тривалий період, і важливий саме факт відсутності досвіду.",
                    "hasn't been driving" => "❌ Present Perfect Continuous (negative): have/has + not + been + V-ing. Тут не процес, а відсутність досвіду, тому Continuous менш доречний.",
                    "didn't drive" => "❌ Past Simple: V2. Використовується для завершених періодів, але 'for eight years' включає теперішній момент.",
                    "wasn't driving" => "❌ Past Continuous: was/were + not + V-ing. Це дія в конкретному моменті минулого, а не довготривалий період.",
                ],
                'hints' => [
                    'a1' => "Present Perfect Simple (negative): have/has + not + V3 → для відсутності досвіду протягом часу.",
                ],
                'level' => 'B2',
                'tense' => ['Present Perfect Simple'],
                'variants' => [
                    'Tom {a1} a bike for years.',
                    'We {a1} a bus for a long time.',
                    'She {a1} a train for months.',
                    'I {a1} a taxi in ages.',
                    'They {a1} a plane for many years.',
                ],
                'grammar_key' => 'experience_gap',
                'context_key' => 'driving',
            ],
            [
                'question'  => 'We {a1} in this city for ten years now.',
                'verb_hint' => ['a1' => '(live)'],
                'options'   => ['have lived', 'lived', 'have been living'],
                'answers'   => ['a1' => 'have been living'],
                'explanations' => [
                    'have lived' => "❌ Present Perfect Simple: have/has + V3. Це граматично можливо, але форма Simple більше підкреслює факт. У реченні 'for ten years now' акцент на тривалості процесу, тому Continuous звучить природніше.",
                    'lived' => "❌ Past Simple: V2. Використовується для завершених дій у минулому. Але ми все ще живемо у цьому місті, тому ця форма не підходить.",
                    'have been living' => "✅ Present Perfect Continuous: have/has + been + V-ing. Показує дію, що почалася в минулому й триває дотепер. Маркер 'for ten years now' прямо вимагає Continuous.",
                ],
                'hints' => [
                    'a1' => "Present Perfect Continuous: have/has + been + V-ing → для тривалості ('for ten years now').",
                ],
                'level' => 'B1',
                'tense' => ['Present Perfect Continuous'],
                'variants' => [
                    'I {a1} here for five years.',
                    'She {a1} in London since 2010.',
                    'They {a1} in this flat for months.',
                    'Tom {a1} in Paris for two years now.',
                    'We {a1} at this address for decades.',
                ],
                'grammar_key' => 'long_term_action',
                'context_key' => 'living',
            ],
            [
                'question'  => 'She {a1} her leg and can’t walk.',
                'verb_hint' => ['a1' => '(break)'],
                'options'   => ['broke', 'has broken', 'has been breaking'],
                'answers'   => ['a1' => 'has broken'],
                'explanations' => [
                    'broke' => "❌ Past Simple: V2. 'She broke her leg' означає лише факт у минулому. Але речення має теперішній наслідок ('can’t walk'), тому Past Simple не підходить.",
                    'has broken' => "✅ Present Perfect Simple: have/has + V3. Використовується, коли дія в минулому має результат у теперішньому. У цьому випадку — вона не може ходити.",
                    'has been breaking' => "❌ Present Perfect Continuous: have/has + been + V-ing. Дієслово 'break' описує миттєву дію, а не процес, тому Continuous неприродний.",
                ],
                'hints' => [
                    'a1' => 'Present Perfect Simple: have/has + V3 → для минулої дії з теперішнім наслідком.',
                ],
                'level' => 'A2',
                'tense' => ['Present Perfect Simple'],
                'variants' => [
                    'He {a1} his arm and can’t write.',
                    'Tom {a1} his phone and it doesn’t work.',
                    'We {a1} the window and now it’s cold.',
                    'They {a1} the vase and it’s on the floor.',
                    'I {a1} my glasses and can’t see well.',
                ],
                'grammar_key' => 'injury_result',
                'context_key' => 'health',
            ],
            [
                'question'  => 'They {a1} dinner when I arrived.',
                'verb_hint' => ['a1' => '(eat)'],
                'options'   => ['were eating', 'ate', 'have eaten'],
                'answers'   => ['a1' => 'were eating'],
                'explanations' => [
                    'were eating' => "✅ Past Continuous: was/were + V-ing. Використовується для дії, яка була в процесі у конкретний момент у минулому ('when I arrived').",
                    'ate' => "❌ Past Simple: V2. Підкреслює завершену дію, але в реченні мова йде про процес, який тривав у момент мого приходу.",
                    'have eaten' => "❌ Present Perfect Simple: have/has + V3. Використовується для теперішнього результату, а тут опис події у минулому.",
                ],
                'hints' => [
                    'a1' => 'Past Continuous: was/were + V-ing → дія в процесі у минулому.',
                ],
                'level' => 'B1',
                'tense' => ['Past Continuous'],
                'variants' => [
                    'She {a1} lunch when I called.',
                    'We {a1} breakfast when the phone rang.',
                    'They {a1} pizza when their friends came.',
                    'I {a1} soup when the bell rang.',
                    'Tom {a1} a sandwich when I entered.',
                ],
                'grammar_key' => 'interrupted_action',
                'context_key' => 'meals',
            ],
            [
                'question'  => 'By the time we arrived, the train {a1}.',
                'verb_hint' => ['a1' => '(leave)'],
                'options'   => ['left', 'had left', 'has left'],
                'answers'   => ['a1' => 'had left'],
                'explanations' => [
                    'left' => "❌ Past Simple: V2. Це означало б, що поїзд пішов одночасно з нашим приходом. Але 'by the time' вимагає більш ранньої дії.",
                    'had left' => "✅ Past Perfect: had + V3. Використовується для дії, що завершилася до іншої минулої дії. Тут — поїзд пішов до нашого приходу.",
                    'has left' => "❌ Present Perfect Simple: have/has + V3. Використовується для теперішнього, а не минулого часу.",
                ],
                'hints' => [
                    'a1' => 'Past Perfect: had + V3 → дія сталася перед іншою у минулому.',
                ],
                'level' => 'B2',
                'tense' => ['Past Perfect'],
                'variants' => [
                    'By the time she came, the bus {a1}.',
                    'By the time we got home, the guests {a1}.',
                    'By midnight, the shop {a1}.',
                    'By the time I called, the show {a1}.',
                    'By the time he arrived, the plane {a1}.',
                ],
                'grammar_key' => 'prior_sequence',
                'context_key' => 'train_travel',
            ],
            [
                'question'  => 'When I got home, my brother {a1} my bike without asking.',
                'verb_hint' => ['a1' => '(take)'],
                'options'   => ['took', 'had taken', 'has taken'],
                'answers'   => ['a1' => 'had taken'],
                'explanations' => [
                    'took' => "❌ Past Simple: V2. Це означало б, що дія відбулася одночасно з моїм поверненням. Але ми підкреслюємо, що він зробив це раніше.",
                    'had taken' => "✅ Past Perfect: had + V3. Використовується для дії, яка відбулася до іншої в минулому. Він узяв велосипед перед моїм поверненням.",
                    'has taken' => "❌ Present Perfect Simple: have/has + V3. Це означало б теперішній результат, але в реченні опис минулої ситуації.",
                ],
                'hints' => [
                    'a1' => 'Past Perfect: had + V3 → дія до іншої минулої події.',
                ],
                'level' => 'B2',
                'tense' => ['Past Perfect'],
                'variants' => [
                    'When I arrived, my sister {a1} my book.',
                    'When I came home, my friend {a1} my laptop.',
                    'When we entered, they {a1} all the food.',
                    'When I returned, someone {a1} my seat.',
                    'When I got back, the kids {a1} my toys.',
                ],
                'grammar_key' => 'prior_sequence',
                'context_key' => 'borrowing',
            ],
            [
                'question'  => 'Look! Somebody {a1} the window.',
                'verb_hint' => ['a1' => '(break)'],
                'options'   => ['broke', 'has broken', 'had broken'],
                'answers'   => ['a1' => 'has broken'],
                'explanations' => [
                    'broke' => "❌ Past Simple: V2. Показує дію у минулому, але ми бачимо результат зараз (розбите вікно). Тому ця форма не підходить.",
                    'has broken' => "✅ Present Perfect Simple: have/has + V3. Використовується для минулих дій із теперішнім результатом. У цьому випадку — розбите вікно.",
                    'had broken' => "❌ Past Perfect: had + V3. Використовується для дій перед іншою минулою подією, але контекст теперішній.",
                ],
                'hints' => [
                    'a1' => 'Present Perfect Simple: have/has + V3 → минула дія з теперішнім результатом.',
                ],
                'level' => 'A2',
                'tense' => ['Present Perfect Simple'],
                'variants' => [
                    'Look! Somebody {a1} the door.',
                    'Look! Somebody {a1} the glass.',
                    'Look! Somebody {a1} the mirror.',
                    'Look! Somebody {a1} the vase.',
                    'Look! Somebody {a1} the lock.',
                ],
                'grammar_key' => 'present_evidence',
                'context_key' => 'incidents',
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

        $service = new QuestionSeedingService();
        $items   = [];
        $meta    = [];

        foreach ($questions as $data) {
            $uuid             = (string) Str::uuid();
            $answers          = [];
            $optionMarkerMap  = [];
            $options          = $data['options'];

            if (! empty($options) && is_array($options) && is_array(reset($options))) {
                foreach ($options as $marker => $values) {
                    foreach ($values as $value) {
                        $optionMarkerMap[$value] = $marker;
                    }
                }
                $flatOptions = array_values(array_unique(Arr::flatten($options)));
            } else {
                $marker = array_key_first($data['answers']);
                foreach ($options as $value) {
                    $optionMarkerMap[$value] = $marker;
                }
                $flatOptions = array_values(array_unique($options));
            }

            foreach ($data['answers'] as $marker => $answer) {
                $answers[] = [
                    'marker'    => $marker,
                    'answer'    => $answer,
                    'verb_hint' => $this->normalizeHint($data['verb_hint'][$marker] ?? null),
                ];
                $optionMarkerMap[$answer] = $marker;
            }

            $tenseTagIds = [];
            foreach ($data['tense'] as $tenseName) {
                $tenseTagIds[] = Tag::firstOrCreate(['name' => $tenseName], ['category' => 'Tenses'])->id;
            }

            $tagIds = array_values(array_unique(array_merge(
                $tenseTagIds,
                [$grammarTags[$data['grammar_key']], $contextTags[$data['context_key']]]
            )));

            $items[] = [
                'uuid'        => $uuid,
                'question'    => $data['question'],
                'category_id' => $categoryId,
                'difficulty'  => $levelDifficulty[$data['level']] ?? 3,
                'source_id'   => $sourceId,
                'flag'        => 2,
                'level'       => $data['level'],
                'tag_ids'     => $tagIds,
                'answers'     => $answers,
                'options'     => $flatOptions,
                'variants'    => $data['variants'] ?? [],
            ];

            $meta[] = [
                'uuid'           => $uuid,
                'answers'        => $data['answers'],
                'option_markers' => $optionMarkerMap,
                'hints'          => $data['hints'],
                'explanations'   => $data['explanations'],
            ];
        }

        $service->seed($items);

        foreach ($meta as $item) {
            $question = Question::where('uuid', $item['uuid'])->first();
            if (! $question) {
                continue;
            }

            $hintText = $this->formatHints($item['hints']);
            if ($hintText !== null) {
                QuestionHint::updateOrCreate(
                    ['question_id' => $question->id, 'provider' => 'chatgpt', 'locale' => 'uk'],
                    ['hint' => $hintText]
                );
            }

            foreach ($item['explanations'] as $option => $text) {
                $marker  = $item['option_markers'][$option] ?? array_key_first($item['answers']);
                $correct = $item['answers'][$marker] ?? reset($item['answers']);

                ChatGPTExplanation::updateOrCreate(
                    [
                        'question'       => $question->question,
                        'wrong_answer'   => $option,
                        'correct_answer' => $correct,
                        'language'       => 'ua',
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
            $parts[] = '{' . $marker . '} ' . $text;
        }

        return implode("\n", $parts);
    }
}
