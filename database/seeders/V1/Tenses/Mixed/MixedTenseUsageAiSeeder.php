<?php

namespace Database\Seeders\V1\Tenses\Mixed;

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

class MixedTenseUsageAiSeeder extends Seeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Mixed Tenses'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Mixed Tense Usage Review -generated AI help hints'])->id;

        $grammarTags = [
            'duration_result' => Tag::firstOrCreate(['name' => 'Duration vs Result'], ['category' => 'Grammar Focus'])->id,
            'past_habit' => Tag::firstOrCreate(['name' => 'Past Habit Statements'], ['category' => 'Grammar Focus'])->id,
            'unfinished_period' => Tag::firstOrCreate(['name' => 'Unfinished Time Periods'], ['category' => 'Grammar Focus'])->id,
            'long_project' => Tag::firstOrCreate(['name' => 'Ongoing Projects'], ['category' => 'Grammar Focus'])->id,
            'question_yet' => Tag::firstOrCreate(['name' => 'Questions with Yet'], ['category' => 'Grammar Focus'])->id,
            'past_marker' => Tag::firstOrCreate(['name' => 'Past Simple Time Markers'], ['category' => 'Grammar Focus'])->id,
            'present_result' => Tag::firstOrCreate(['name' => 'Present Result Situations'], ['category' => 'Grammar Focus'])->id,
            'negative_yet' => Tag::firstOrCreate(['name' => 'Negative with Yet'], ['category' => 'Grammar Focus'])->id,
            'duration_focus' => Tag::firstOrCreate(['name' => 'Duration Emphasis'], ['category' => 'Grammar Focus'])->id,
            'already_statement' => Tag::firstOrCreate(['name' => 'Already Statements'], ['category' => 'Grammar Focus'])->id,
            'negative_habit' => Tag::firstOrCreate(['name' => 'Negative Habit Statements'], ['category' => 'Grammar Focus'])->id,
            'limited_activity' => Tag::firstOrCreate(['name' => 'Limited Activity Frequency'], ['category' => 'Grammar Focus'])->id,
            'experience_gap' => Tag::firstOrCreate(['name' => 'Experience Gaps'], ['category' => 'Grammar Focus'])->id,
            'long_term' => Tag::firstOrCreate(['name' => 'Long-Term Actions'], ['category' => 'Grammar Focus'])->id,
            'injury_result' => Tag::firstOrCreate(['name' => 'Injury Results'], ['category' => 'Grammar Focus'])->id,
            'interrupted_action' => Tag::firstOrCreate(['name' => 'Interrupted Actions'], ['category' => 'Grammar Focus'])->id,
            'sequencing' => Tag::firstOrCreate(['name' => 'Sequencing with By the Time'], ['category' => 'Grammar Focus'])->id,
            'prior_action' => Tag::firstOrCreate(['name' => 'Prior Past Actions'], ['category' => 'Grammar Focus'])->id,
            'evidence_present' => Tag::firstOrCreate(['name' => 'Present Evidence'], ['category' => 'Grammar Focus'])->id,
            'specific_time' => Tag::firstOrCreate(['name' => 'Specific Time in Past Continuous'], ['category' => 'Grammar Focus'])->id,
            'future_deadline' => Tag::firstOrCreate(['name' => 'Future Perfect Deadlines'], ['category' => 'Grammar Focus'])->id,
            'future_process' => Tag::firstOrCreate(['name' => 'Future Continuous Processes'], ['category' => 'Grammar Focus'])->id,
        ];

        $vocabularyTags = [
            'housework' => Tag::firstOrCreate(['name' => 'Housework & Cleaning'], ['category' => 'Vocabulary Detail'])->id,
            'childhood' => Tag::firstOrCreate(['name' => 'Childhood Memories'], ['category' => 'Vocabulary Detail'])->id,
            'relationships' => Tag::firstOrCreate(['name' => 'Friendships & Relationships'], ['category' => 'Vocabulary Detail'])->id,
            'renovation' => Tag::firstOrCreate(['name' => 'Home Renovation'], ['category' => 'Vocabulary Detail'])->id,
            'family' => Tag::firstOrCreate(['name' => 'Family Life'], ['category' => 'Vocabulary Detail'])->id,
            'travel' => Tag::firstOrCreate(['name' => 'Travel & Transport'], ['category' => 'Vocabulary Detail'])->id,
            'daily_problem' => Tag::firstOrCreate(['name' => 'Daily Problems'], ['category' => 'Vocabulary Detail'])->id,
            'thinking' => Tag::firstOrCreate(['name' => 'Thinking & Understanding'], ['category' => 'Vocabulary Detail'])->id,
            'commuting' => Tag::firstOrCreate(['name' => 'Commuting'], ['category' => 'Vocabulary Detail'])->id,
            'reading' => Tag::firstOrCreate(['name' => 'Reading & Study'], ['category' => 'Vocabulary Detail'])->id,
            'beverages' => Tag::firstOrCreate(['name' => 'Beverages & Habits'], ['category' => 'Vocabulary Detail'])->id,
            'media' => Tag::firstOrCreate(['name' => 'Entertainment & Media'], ['category' => 'Vocabulary Detail'])->id,
            'technology' => Tag::firstOrCreate(['name' => 'Technology Use'], ['category' => 'Vocabulary Detail'])->id,
            'driving' => Tag::firstOrCreate(['name' => 'Driving'], ['category' => 'Vocabulary Detail'])->id,
            'living' => Tag::firstOrCreate(['name' => 'Home & Living'], ['category' => 'Vocabulary Detail'])->id,
            'health' => Tag::firstOrCreate(['name' => 'Health & Injuries'], ['category' => 'Vocabulary Detail'])->id,
            'meals' => Tag::firstOrCreate(['name' => 'Meals & Dining'], ['category' => 'Vocabulary Detail'])->id,
            'borrowing' => Tag::firstOrCreate(['name' => 'Borrowing & Sharing'], ['category' => 'Vocabulary Detail'])->id,
            'incidents' => Tag::firstOrCreate(['name' => 'Home Incidents'], ['category' => 'Vocabulary Detail'])->id,
            'study' => Tag::firstOrCreate(['name' => 'Study Routine'], ['category' => 'Vocabulary Detail'])->id,
            'education_goal' => Tag::firstOrCreate(['name' => 'Education Goals'], ['category' => 'Vocabulary Detail'])->id,
            'holiday' => Tag::firstOrCreate(['name' => 'Holidays & Leisure'], ['category' => 'Vocabulary Detail'])->id,
        ];

        // Correction tags for fixed questions
        $fixedTag = Tag::firstOrCreate(['name' => 'fixed'], ['category' => 'Review Status'])->id;
        $correctionTag = Tag::firstOrCreate(['name' => 'He -> She'], ['category' => 'Answer Corrections'])->id;

        $questionData = [
            [
                'question' => 'Jane {a1} in the house for hours. She {a2} three rooms so far.',
                'verb_hint' => ['a1' => '(work)', 'a2' => '(clean)'],
                'options' => [
                    'a1' => ['has worked', 'has been working'],
                    'a2' => ['has cleaned', 'has been cleaning'],
                ],
                'answers' => ['a1' => 'has been working', 'a2' => 'has cleaned'],
                'explanations' => [
                    'has worked' => "Форма Present Perfect Simple підкреслює результат, але у виразі 'for hours' важлива саме тривалість процесу. Тому відповідь неправильна.",
                    'has been working' => "Правильна відповідь. 'For hours' вказує на дію, яка триває вже певний час, тому тут потрібен Present Perfect Continuous.",
                    'has cleaned' => "Правильна відповідь. Вираз 'so far' показує результат до цього моменту, тому вживаємо Present Perfect Simple.",
                    'has been cleaning' => "Ця форма підкреслює сам процес, але контекст говорить про результат (закінчила три кімнати). Тому неправильна.",
                ],
                'hints' => [
                    'a1' => 'Present Perfect Continuous: have/has + been + V-ing. Використовується для дій, що тривають до тепер.',
                    'a2' => "Present Perfect Simple: have/has + V3. Використовується з 'so far', щоб підкреслити результат.",
                ],
                'level' => 'B2',
                'tense' => ['Present Perfect Continuous', 'Present Perfect Simple'],
                'grammar' => 'duration_result',
                'vocab' => 'housework',
                'correction_tags' => [$fixedTag, $correctionTag],
            ],
            [
                'question' => 'He {a1} there when he was a child.',
                'verb_hint' => ['a1' => '(live)'],
                'options' => ['lived', 'has lived', 'has been living'],
                'answers' => ['a1' => 'lived'],
                'explanations' => [
                    'has lived' => "Ця форма означає досвід, який актуальний тепер. Але у виразі 'when he was a child' мова йде про завершений минулий період. Тому ця форма неправильна.",
                    'lived' => "Правильна відповідь. Минуле обставинне речення вимагає Past Simple, бо дія завершена ('when he was a child').",
                    'has been living' => "Ця форма описує процес, що триває й досі. Але дія обмежена минулим періодом, тому це неправильно.",
                ],
                'hints' => [
                    'a1' => 'Past Simple: V2. Використовується для завершених дій у минулому з маркерами часу (when, ago, yesterday).',
                ],
                'level' => 'A2',
                'tense' => ['Past Simple'],
                'grammar' => 'past_habit',
                'vocab' => 'childhood',
            ],
            [
                'question' => 'I {a1} her since last year.',
                'verb_hint' => ['a1' => '(not/see)'],
                'options' => ["haven't seen", "didn't see", "haven't been seeing"],
                'answers' => ['a1' => "haven't seen"],
                'explanations' => [
                    "haven't seen" => "Правильна відповідь. 'Since last year' показує період від минулого до теперішнього моменту. Використовуємо Present Perfect Simple.",
                    "didn't see" => "Past Simple означає завершену дію в минулому, але тут дія ще триває ('з того часу і досі'). Тому ця форма неправильна.",
                    "haven't been seeing" => "Continuous з дієсловом 'see' звучить неприродно, адже 'see' — це статичне дієслово. Тому ця відповідь неправильна.",
                ],
                'hints' => [
                    'a1' => "Present Perfect Simple: have/has + not + V3. Використовується з 'since/for'.",
                ],
                'level' => 'B1',
                'tense' => ['Present Perfect Simple'],
                'grammar' => 'unfinished_period',
                'vocab' => 'relationships',
            ],
            [
                'question' => 'They {a1} this room for a month. I’m sure they will never be ready with it.',
                'verb_hint' => ['a1' => '(decorate)'],
                'options' => ['have decorated', 'decorated', 'have been decorating'],
                'answers' => ['a1' => 'have been decorating'],
                'explanations' => [
                    'have decorated' => 'Ця форма підкреслює завершений результат. Але робота ще триває, тому вона неправильна.',
                    'decorated' => 'Past Simple позначає завершену дію в минулому, але процес ще триває зараз. Тому ця відповідь не підходить.',
                    'have been decorating' => 'Правильна відповідь. Вираз "for a month" означає дію, що триває певний час і досі не завершена, отже вживаємо Present Perfect Continuous.',
                ],
                'hints' => [
                    'a1' => 'Present Perfect Continuous: have/has + been + V-ing. Показує процес, що триває.',
                ],
                'level' => 'B2',
                'tense' => ['Present Perfect Continuous'],
                'grammar' => 'long_project',
                'vocab' => 'renovation',
            ],
            [
                'question' => 'Dad, {a1} reading the paper yet?',
                'verb_hint' => ['a1' => '(finish)'],
                'options' => ['did you finish', 'have you finished', 'are you finishing'],
                'answers' => ['a1' => 'have you finished'],
                'explanations' => [
                    'did you finish' => "Past Simple означає завершену дію в минулому, але слово 'yet' вказує на теперішній момент. Тому ця форма неправильна.",
                    'have you finished' => "Правильна відповідь. 'Yet' сигналізує, що ми питаємо про завершення дії до теперішнього моменту. Це класичний випадок Present Perfect.",
                    'are you finishing' => 'Present Continuous показує дію в процесі, але питання стосується завершення. Тому ця форма невірна.',
                ],
                'hints' => [
                    'a1' => "Present Perfect Simple: have/has + V3. Використовується в питаннях із 'yet'.",
                ],
                'level' => 'B1',
                'tense' => ['Present Perfect Simple'],
                'grammar' => 'question_yet',
                'vocab' => 'family',
            ],
            [
                'question' => 'They {a1} a few minutes ago.',
                'verb_hint' => ['a1' => '(leave)'],
                'options' => ['left', 'have just left', 'have been leaving'],
                'answers' => ['a1' => 'left'],
                'explanations' => [
                    'left' => "Правильна відповідь. Слово 'ago' є маркером Past Simple, тому ми використовуємо форму V2.",
                    'have just left' => "Ця форма правильна зі словом 'just', але не з 'ago'. Тому вона тут неправильна.",
                    'have been leaving' => "Continuous тут недоречний, бо дія завершена. 'Ago' вимагає Past Simple.",
                ],
                'hints' => [
                    'a1' => "Past Simple: V2. Використовуємо з маркером 'ago'.",
                ],
                'level' => 'A2',
                'tense' => ['Past Simple'],
                'grammar' => 'past_marker',
                'vocab' => 'travel',
            ],
            [
                'question' => 'I can’t get into my house because I {a1} my keys.',
                'verb_hint' => ['a1' => '(lose)'],
                'options' => ['lost', 'have been losing', 'have lost'],
                'answers' => ['a1' => 'have lost'],
                'explanations' => [
                    'lost' => 'Past Simple вказує на завершену дію в минулому, але тут ключі все ще відсутні й наслідок відчувається тепер. Тому ця форма неправильна.',
                    'have been losing' => 'Continuous означає дію, що відбувалась неодноразово або тривало. Але тут ідеться про один конкретний випадок втрати, тому ця форма невірна.',
                    'have lost' => 'Правильна відповідь. Present Perfect Simple показує дію в минулому з наслідком у теперішньому: ключі втрачені, і я не можу потрапити в дім.',
                ],
                'hints' => [
                    'a1' => 'Present Perfect Simple: have/has + V3. Використовується для дій із результатом у теперішньому.',
                ],
                'level' => 'B1',
                'tense' => ['Present Perfect Simple'],
                'grammar' => 'present_result',
                'vocab' => 'daily_problem',
            ],
            [
                'question' => 'My mom {a1} the problem yet.',
                'verb_hint' => ['a1' => '(understand/not)'],
                'options' => ["hasn't been understanding", 'has not understood', "didn't understand"],
                'answers' => ['a1' => 'has not understood'],
                'explanations' => [
                    "hasn't been understanding" => "Continuous не підходить, оскільки 'understand' належить до статичних дієслів, які рідко вживаються у тривалих формах. Тому ця форма неправильна.",
                    'has not understood' => "Правильна відповідь. Слово 'yet' показує, що дія досі не завершена. Для цього використовують Present Perfect Simple у заперечній формі.",
                    "didn't understand" => "Past Simple означає завершене нерозуміння в минулому, але з 'yet' ми маємо справу із теперішнім результатом. Тому відповідь невірна.",
                ],
                'hints' => [
                    'a1' => "Present Perfect Simple: have/has + not + V3. Уживається у запереченнях із 'yet'.",
                ],
                'level' => 'B1',
                'tense' => ['Present Perfect Simple'],
                'grammar' => 'negative_yet',
                'vocab' => 'thinking',
            ],
            [
                'question' => 'She {a1} for the bus for twenty minutes.',
                'verb_hint' => ['a1' => '(wait)'],
                'options' => ['has waited', 'waited', 'has been waiting'],
                'answers' => ['a1' => 'has been waiting'],
                'explanations' => [
                    'has waited' => 'Present Perfect Simple підкреслює завершення дії, але тут дія ще триває. Тому форма невірна.',
                    'waited' => 'Past Simple позначає завершене очікування в минулому, тоді як у реченні йдеться про триваючу дію. Неправильна відповідь.',
                    'has been waiting' => 'Правильна відповідь. Вираз "for twenty minutes" показує тривалість дії, яка почалась у минулому і триває досі. Це класичний випадок для Present Perfect Continuous.',
                ],
                'hints' => [
                    'a1' => "Present Perfect Continuous: have/has + been + V-ing. Використовується з 'for' і 'since' для тривалості.",
                ],
                'level' => 'B1',
                'tense' => ['Present Perfect Continuous'],
                'grammar' => 'duration_focus',
                'vocab' => 'commuting',
            ],
            [
                'question' => 'We {a1} this book already.',
                'verb_hint' => ['a1' => '(read)'],
                'options' => ['have read', 'read', 'have been reading'],
                'answers' => ['a1' => 'have read'],
                'explanations' => [
                    'have read' => "Правильна відповідь. Слово 'already' сигналізує про дію, яка завершилась до теперішнього моменту, тому тут потрібен Present Perfect Simple.",
                    'read' => "Past Simple можливий у конкретному контексті з минулим маркером часу, але тут його немає. 'Already' вимагає зв’язку з теперішнім. Неправильна відповідь.",
                    'have been reading' => "Ця форма підкреслює тривалий процес, але контекст говорить про завершення ('already'). Тому ця форма неправильна.",
                ],
                'hints' => [
                    'a1' => "Present Perfect Simple: have/has + V3. Використовується з 'already' для завершених дій.",
                ],
                'level' => 'A2',
                'tense' => ['Present Perfect Simple'],
                'grammar' => 'already_statement',
                'vocab' => 'reading',
            ],
            [
                'question' => 'Mark {a1} coffee for three months. He switched to tea.',
                'verb_hint' => ['a1' => '(drink/not)'],
                'options' => ["hasn't drunk", "didn't drink", "hasn't been drinking"],
                'answers' => ['a1' => "hasn't drunk"],
                'explanations' => [
                    "hasn't drunk" => "Правильна відповідь. Вираз 'for three months' підкреслює період, що триває до тепер. Тому ми використовуємо Present Perfect Simple у заперечній формі.",
                    "didn't drink" => 'Past Simple позначає завершену дію в минулому. Але тут дія охоплює проміжок часу, який триває до тепер, тому ця форма невірна.',
                    "hasn't been drinking" => "Continuous підкреслює процес, але в поєднанні з 'switched to tea' зрозуміло, що дія завершена як результат вибору. Тому невірно.",
                ],
                'hints' => [
                    'a1' => "Present Perfect Simple: have/has + not + V3. Показує дію, яка триває або не триває протягом певного часу.",
                ],
                'level' => 'B2',
                'tense' => ['Present Perfect Simple'],
                'grammar' => 'negative_habit',
                'vocab' => 'beverages',
            ],
            [
                'question' => 'I {a1} TV this week – just today for half an hour.',
                'verb_hint' => ['a1' => '(watch/not)'],
                'options' => ["haven't watched", "haven't been watching", "didn't watch", "wasn't watching"],
                'answers' => ['a1' => "haven't been watching"],
                'explanations' => [
                    "haven't watched" => "Ця форма могла б бути правильною, але контекст 'just today for half an hour' підкреслює, що дія майже не відбувалась, а не повністю відсутня. Тому менш природно.",
                    "haven't been watching" => "Правильна відповідь. Фраза 'this week' вказує на період, який ще триває, а тривала форма Continuous підкреслює процес. Тому використовується Present Perfect Continuous.",
                    "didn't watch" => "Past Simple описує завершений період у минулому, але тут період 'this week' ще триває. Тому ця форма невірна.",
                    "wasn't watching" => 'Past Continuous використовується для конкретного моменту в минулому, а не для опису періоду до теперішнього моменту. Тому неправильно.',
                ],
                'hints' => [
                    'a1' => "Present Perfect Continuous: have/has + been + V-ing. Використовується з виразами на кшталт 'this week', коли період ще триває.",
                ],
                'level' => 'B2',
                'tense' => ['Present Perfect Continuous'],
                'grammar' => 'limited_activity',
                'vocab' => 'media',
            ],
            [
                'question' => 'I {a1} the computer for half an hour, only for about 5 minutes.',
                'verb_hint' => ['a1' => '(play/not)'],
                'options' => ["haven't played", "haven't been playing", "didn't play", "wasn't playing"],
                'answers' => ['a1' => "haven't been playing"],
                'explanations' => [
                    "haven't played" => "Ця форма могла б підійти, але вона підкреслює лише факт відсутності дії. Контекст 'for half an hour… only 5 minutes' робить акцент на тривалості. Тому менш природно.",
                    "haven't been playing" => "Правильна відповідь. Вираз 'for half an hour' вказує на процес, який не відбувався у цей період. Це класичний випадок для Present Perfect Continuous у заперечній формі.",
                    "didn't play" => "Past Simple використовується для завершених дій у минулому, але період 'for half an hour' ще охоплює теперішній момент. Тому ця форма неправильна.",
                    "wasn't playing" => "Past Continuous вказує на дію у конкретний момент у минулому, але тут йдеться про період, що триває до теперішнього. Відповідь невірна.",
                ],
                'hints' => [
                    'a1' => "Present Perfect Continuous: have/has + been + V-ing. Використовуємо з 'for'/'since', коли підкреслюється тривалість або відсутність процесу.",
                ],
                'level' => 'B2',
                'tense' => ['Present Perfect Continuous'],
                'grammar' => 'duration_focus',
                'vocab' => 'technology',
            ],
            [
                'question' => 'Bob {a1} a car for eight years.',
                'verb_hint' => ['a1' => '(drive/not)'],
                'options' => ["hasn't driven", "hasn't been driving", "didn't drive", "wasn't driving"],
                'answers' => ['a1' => "hasn't driven"],
                'explanations' => [
                    "hasn't driven" => "Правильна відповідь. Вираз 'for eight years' описує тривалий проміжок часу до тепер. Тут важливий факт відсутності досвіду, тому Present Perfect Simple.",
                    "hasn't been driving" => 'Ця форма підкреслює відсутність процесу. Але в реченні важливий саме факт, що він не мав досвіду керування протягом цього часу. Тому менш підходить.',
                    "didn't drive" => 'Past Simple описує завершені дії у минулому, але період ще триває до тепер. Тому ця форма невірна.',
                    "wasn't driving" => 'Past Continuous використовується для певного моменту у минулому, а не для багаторічного проміжку. Тому невірно.',
                ],
                'hints' => [
                    'a1' => "Present Perfect Simple: have/has + not + V3. Використовується, коли говоримо про тривалий період відсутності дії до тепер.",
                ],
                'level' => 'B2',
                'tense' => ['Present Perfect Simple'],
                'grammar' => 'experience_gap',
                'vocab' => 'driving',
            ],
            [
                'question' => 'We {a1} in this city for ten years now.',
                'verb_hint' => ['a1' => '(live)'],
                'options' => ['have lived', 'lived', 'have been living'],
                'answers' => ['a1' => 'have been living'],
                'explanations' => [
                    'have lived' => 'Ця форма також можлива, але вона підкреслює сам факт проживання. У реченні ж важлива тривалість процесу ("for ten years now"), тому краще Continuous.',
                    'lived' => 'Past Simple не підходить, бо дія ще триває. Ми все ще живемо тут.',
                    'have been living' => 'Правильна відповідь. "For ten years now" показує дію, яка почалась у минулому і триває досі. Це класичний випадок Present Perfect Continuous.',
                ],
                'hints' => [
                    'a1' => "Present Perfect Continuous: have/has + been + V-ing. Використовується з 'for'/'since', коли підкреслюємо тривалість.",
                ],
                'level' => 'B1',
                'tense' => ['Present Perfect Continuous'],
                'grammar' => 'long_term',
                'vocab' => 'living',
            ],
            [
                'question' => 'She {a1} her leg and can’t walk.',
                'verb_hint' => ['a1' => '(break)'],
                'options' => ['broke', 'has broken', 'has been breaking'],
                'answers' => ['a1' => 'has broken'],
                'explanations' => [
                    'broke' => 'Past Simple описує подію в минулому, але тоді не було б пояснення теперішньої проблеми. У реченні важливий наслідок, тому це неправильно.',
                    'has broken' => 'Правильна відповідь. Present Perfect Simple підкреслює, що подія у минулому має наслідки зараз (вона не може ходити).',
                    'has been breaking' => 'Continuous у такому контексті не вживається, бо "break" — це миттєва дія, а не процес. Тому ця форма неправильна.',
                ],
                'hints' => [
                    'a1' => 'Present Perfect Simple: have/has + V3. Використовується, коли минула дія має теперішній результат.',
                ],
                'level' => 'A2',
                'tense' => ['Present Perfect Simple'],
                'grammar' => 'injury_result',
                'vocab' => 'health',
            ],
            [
                'question' => 'They {a1} dinner when I arrived.',
                'verb_hint' => ['a1' => '(eat)'],
                'options' => ['were eating', 'ate', 'have eaten'],
                'answers' => ['a1' => 'were eating'],
                'explanations' => [
                    'were eating' => "Правильна відповідь. Past Continuous використовується для дій, що тривали у конкретний момент у минулому ('when I arrived').",
                    'ate' => "Past Simple описує завершену дію. Але тут акцент на процесі, який відбувався саме у момент мого приходу.",
                    'have eaten' => 'Present Perfect вживається для дій із результатом у теперішньому, а тут описується подія у минулому. Неправильна форма.',
                ],
                'hints' => [
                    'a1' => 'Past Continuous: was/were + V-ing. Використовується для опису дій у процесі у минулому.',
                ],
                'level' => 'B1',
                'tense' => ['Past Continuous'],
                'grammar' => 'interrupted_action',
                'vocab' => 'meals',
            ],
            [
                'question' => 'By the time we arrived, the train {a1}.',
                'verb_hint' => ['a1' => '(leave)'],
                'options' => ['left', 'had left', 'has left'],
                'answers' => ['a1' => 'had left'],
                'explanations' => [
                    'left' => "Past Simple означає, що дія і наша подорож відбулися одночасно. Але 'by the time' вимагає вживання минулого більш раннього часу. Тому ця форма невірна.",
                    'had left' => "Правильна відповідь. Past Perfect вказує, що дія завершилась раніше іншої дії в минулому ('by the time we arrived').",
                    'has left' => 'Present Perfect не підходить, бо мова йде про минулий момент, а не теперішній результат.',
                ],
                'hints' => [
                    'a1' => 'Past Perfect: had + V3. Використовується для дії, що сталася перед іншою подією в минулому.',
                ],
                'level' => 'B2',
                'tense' => ['Past Perfect'],
                'grammar' => 'sequencing',
                'vocab' => 'travel',
            ],
            [
                'question' => 'When I got home, my brother {a1} my bike without asking.',
                'verb_hint' => ['a1' => '(take)'],
                'options' => ['took', 'had taken', 'has taken'],
                'answers' => ['a1' => 'had taken'],
                'explanations' => [
                    'took' => 'Past Simple вказує, що дія відбулася одночасно з моїм поверненням. Але ми підкреслюємо, що це сталося РАНІШЕ. Тому ця форма неправильна.',
                    'had taken' => 'Правильна відповідь. Past Perfect використовується, щоб показати, що одна дія (він узяв велосипед) сталася раніше іншої (я повернувся додому).',
                    'has taken' => 'Present Perfect описує події з теперішнім результатом, але у реченні йдеться про минулий момент. Тому ця форма невірна.',
                ],
                'hints' => [
                    'a1' => 'Past Perfect: had + V3. Використовується для дій, що сталися до іншої події у минулому.',
                ],
                'level' => 'B2',
                'tense' => ['Past Perfect'],
                'grammar' => 'prior_action',
                'vocab' => 'borrowing',
            ],
            [
                'question' => 'Look! Somebody {a1} the window.',
                'verb_hint' => ['a1' => '(break)'],
                'options' => ['broke', 'has broken', 'had broken'],
                'answers' => ['a1' => 'has broken'],
                'explanations' => [
                    'broke' => "Past Simple описує подію у минулому, але ми бачимо результат прямо зараз (розбите вікно). Тому ця форма неправильна.",
                    'has broken' => 'Правильна відповідь. Present Perfect використовується, коли минула дія має очевидний наслідок у теперішньому.',
                    'had broken' => 'Past Perfect описує події, що сталися перед іншими діями в минулому, але тут йдеться про теперішній результат. Тому ця форма невірна.',
                ],
                'hints' => [
                    'a1' => 'Present Perfect Simple: have/has + V3. Використовується, коли минула дія має результат зараз.',
                ],
                'level' => 'A2',
                'tense' => ['Present Perfect Simple'],
                'grammar' => 'evidence_present',
                'vocab' => 'incidents',
            ],
            [
                'question' => 'At 7 p.m. yesterday I {a1} my homework.',
                'verb_hint' => ['a1' => '(do)'],
                'options' => ['was doing', 'did', 'have done'],
                'answers' => ['a1' => 'was doing'],
                'explanations' => [
                    'was doing' => "Правильна відповідь. Past Continuous використовується для дій, що відбувались у конкретний момент у минулому ('at 7 p.m. yesterday').",
                    'did' => 'Past Simple означає завершену дію, але контекст показує процес у певний час. Тому ця форма невірна.',
                    'have done' => "Present Perfect стосується теперішнього результату, але у реченні є минулий час 'yesterday'. Це робить варіант неправильним.",
                ],
                'hints' => [
                    'a1' => 'Past Continuous: was/were + V-ing. Використовується для дій у процесі у конкретний момент у минулому.',
                ],
                'level' => 'B1',
                'tense' => ['Past Continuous'],
                'grammar' => 'specific_time',
                'vocab' => 'study',
            ],
            [
                'question' => 'By next year, she {a1} her studies.',
                'verb_hint' => ['a1' => '(finish)'],
                'options' => ['will finish', 'will have finished', 'is finishing'],
                'answers' => ['a1' => 'will have finished'],
                'explanations' => [
                    'will finish' => "Future Simple означає дію в майбутньому, але не підкреслює, що вона завершиться ДО певного моменту. Тому ця форма неправильна.",
                    'will have finished' => "Правильна відповідь. Future Perfect використовується, щоб показати, що дія завершиться до певного часу в майбутньому ('by next year').",
                    'is finishing' => "Present Continuous може вживатися для запланованих дій, але тут потрібне підкреслення моменту завершення до конкретного майбутнього часу.",
                ],
                'hints' => [
                    'a1' => 'Future Perfect: will have + V3. Використовується з "by" для позначення завершення дії до певного моменту.',
                ],
                'level' => 'C1',
                'tense' => ['Future Perfect'],
                'grammar' => 'future_deadline',
                'vocab' => 'education_goal',
            ],
            [
                'question' => 'This time tomorrow, we {a1} on the beach.',
                'verb_hint' => ['a1' => '(lie)'],
                'options' => ['will lie', 'will be lying', 'are lying'],
                'answers' => ['a1' => 'will be lying'],
                'explanations' => [
                    'will lie' => 'Future Simple описує факт майбутньої дії, але не підкреслює її тривалість у конкретний момент. Тому ця форма менш точна.',
                    'will be lying' => "Правильна відповідь. Future Continuous використовується для дій, що будуть у процесі у конкретний момент у майбутньому ('this time tomorrow').",
                    'are lying' => "Present Continuous може описувати заплановані майбутні дії, але тут ключовий вираз 'this time tomorrow' вимагає Future Continuous.",
                ],
                'hints' => [
                    'a1' => 'Future Continuous: will be + V-ing. Використовується, щоб описати дію у процесі в певний момент майбутнього.',
                ],
                'level' => 'B2',
                'tense' => ['Future Continuous'],
                'grammar' => 'future_process',
                'vocab' => 'holiday',
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
        $items = [];
        $meta = [];

        foreach ($questionData as $index => $data) {
            $uuid = (string) Str::uuid();
            $answers = [];
            $optionMarkerMap = [];
            $options = $data['options'];

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
                    'marker' => $marker,
                    'answer' => $answer,
                    'verb_hint' => $this->normalizeHint($data['verb_hint'][$marker] ?? null),
                ];
                $optionMarkerMap[$answer] = $marker;
            }

            $tenseTags = [];
            foreach ($data['tense'] as $tenseName) {
                $tenseTags[] = Tag::firstOrCreate(['name' => $tenseName], ['category' => 'Tenses'])->id;
            }

            $baseTags = array_merge(
                $tenseTags,
                [$grammarTags[$data['grammar']], $vocabularyTags[$data['vocab']]]
            );
            
            // Add correction tags if present
            if (isset($data['correction_tags'])) {
                $baseTags = array_merge($baseTags, $data['correction_tags']);
            }
            
            $tagIds = array_values(array_unique($baseTags));

            $items[] = [
                'uuid' => $uuid,
                'question' => $data['question'],
                'category_id' => $categoryId,
                'difficulty' => $levelDifficulty[$data['level']] ?? 3,
                'source_id' => $sourceId,
                'flag' => 0,
                'level' => $data['level'],
                'tag_ids' => $tagIds,
                'answers' => $answers,
                'options' => $flatOptions,
            ];

            $meta[] = [
                'uuid' => $uuid,
                'answers' => $data['answers'],
                'option_markers' => $optionMarkerMap,
                'hints' => $data['hints'],
                'explanations' => $data['explanations'],
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
                $marker = $item['option_markers'][$option] ?? array_key_first($item['answers']);
                $correct = $item['answers'][$marker] ?? reset($item['answers']);

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
            $markerLabel = '{' . $marker . '}';
            $parts[] = $markerLabel . ' ' . ltrim($text);
        }

        return implode("\n", $parts);
    }
}
