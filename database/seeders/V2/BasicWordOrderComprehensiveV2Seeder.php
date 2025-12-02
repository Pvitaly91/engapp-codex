<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class BasicWordOrderComprehensiveV2Seeder extends QuestionSeeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Basic Word Order Comprehensive AI Test'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Custom: Basic Word Order Comprehensive AI'])->id;

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Basic Word Order Comprehensive'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTags = [
            'word_order' => Tag::firstOrCreate(['name' => 'Word Order Focus'], ['category' => 'English Grammar Detail'])->id,
            'sentence_structure' => Tag::firstOrCreate(['name' => 'Sentence Structure Focus'], ['category' => 'English Grammar Detail'])->id,
        ];

        $structureTagIds = [
            'fill_gap' => Tag::firstOrCreate(['name' => 'Fill in the Gap'], ['category' => 'English Grammar Structure'])->id,
            'choose_order' => Tag::firstOrCreate(['name' => 'Choose Correct Order'], ['category' => 'English Grammar Structure'])->id,
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
            // Exercise 1 - Fill in the gaps with the correct options (10 questions)
            [
                'id' => 'e1q1',
                'question' => "I didn't like {a1} last night.",
                'options' => ['very much the food', 'the food very much'],
                'correct' => 'the food very much',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'hint' => 'Порядок слів: спочатку називаємо додаток (the food), потім прислівник міри (very much). Прислівник міри зазвичай стоїть після прямого додатка.',
                'explanations' => [
                    'the food very much' => "✅ «the food very much» — правильно. Прямий додаток (the food) завжди стоїть перед прислівником міри (very much). Приклад: *I didn't like the food very much last night.*",
                    'very much the food' => '❌ Прислівник міри (very much) не ставиться перед прямим додатком (the food). Правильний порядок: об\'єкт → прислівник.',
                ],
            ],
            [
                'id' => 'e1q2',
                'question' => 'We arrived {a1}.',
                'options' => ['home very late', 'very late home'],
                'correct' => 'home very late',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'hint' => 'Порядок слів: місце (home) після дієслова, потім обставина способу/часу (very late). Місце ближче до дієслова, ніж обставина часу.',
                'explanations' => [
                    'home very late' => '✅ «home very late» — правильно. Спочатку вказуємо місце (home), потім обставину часу/способу (very late). Приклад: *We arrived home very late.*',
                    'very late home' => '❌ Обставина (very late) не ставиться перед місцем (home). Місце ближче до дієслова.',
                ],
            ],
            [
                'id' => 'e1q3',
                'question' => 'Teresa speaks {a1}.',
                'options' => ['very well English', 'English very well'],
                'correct' => 'English very well',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'hint' => 'Порядок слів: прямий додаток (English) перед прислівником способу (very well). Прислівник способу ставимо після об\'єкта.',
                'explanations' => [
                    'English very well' => '✅ «English very well» — правильно. Прямий додаток (English) стоїть перед прислівником способу (very well). Приклад: *Teresa speaks English very well.*',
                    'very well English' => '❌ Прислівник способу (very well) не ставиться перед об\'єктом (English). Правильний порядок: об\'єкт → прислівник.',
                ],
            ],
            [
                'id' => 'e1q4',
                'question' => 'Please, can you put {a1}?',
                'options' => ['in the corner that box', 'that box in the corner'],
                'correct' => 'that box in the corner',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'hint' => 'Порядок слів: спочатку додаток (that box), потім обставина місця (in the corner). Предмет називаємо першим.',
                'explanations' => [
                    'that box in the corner' => '✅ «that box in the corner» — правильно. Спочатку називаємо додаток (that box), потім місце (in the corner). Приклад: *Can you put that box in the corner?*',
                    'in the corner that box' => '❌ Обставина місця (in the corner) не передує прямому додатку (that box). Предмет називаємо першим.',
                ],
            ],
            [
                'id' => 'e1q5',
                'question' => 'After the accident, Jim was {a1}.',
                'options' => ['for a week in hospital', 'in hospital for a week'],
                'correct' => 'in hospital for a week',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'hint' => 'Порядок слів: обставина місця (in hospital) перед обставиною часу/тривалості (for a week). Місце зазвичай передує часу.',
                'explanations' => [
                    'in hospital for a week' => '✅ «in hospital for a week» — правильно. Обставина місця (in hospital) стоїть перед тривалістю (for a week). Приклад: *Jim was in hospital for a week.*',
                    'for a week in hospital' => '❌ Тривалість часу (for a week) не ставиться перед місцем (in hospital). Спершу вказуємо місце.',
                ],
            ],
            [
                'id' => 'e1q6',
                'question' => 'I like {a1}.',
                'options' => ['this music a lot', 'a lot this music'],
                'correct' => 'this music a lot',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'hint' => 'Порядок слів: додаток (this music) перед прислівником міри (a lot). Об\'єкт ставимо відразу після дієслова.',
                'explanations' => [
                    'this music a lot' => '✅ «this music a lot» — правильно. Об\'єкт (this music) стоїть перед прислівником міри (a lot). Приклад: *I like this music a lot.*',
                    'a lot this music' => '❌ Прислівник міри (a lot) не ставиться перед об\'єктом (this music). Об\'єкт завжди після дієслова.',
                ],
            ],
            [
                'id' => 'e1q7',
                'question' => 'They met {a1}.',
                'options' => ["at a friend's house after a party", "after a party at a friend's house"],
                'correct' => "at a friend's house after a party",
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'hint' => 'Порядок слів: конкретніше місце (at a friend\'s house) перед ширшим контекстом (after a party). Від конкретного до загального.',
                'explanations' => [
                    "at a friend's house after a party" => "✅ «at a friend's house after a party» — правильно. Конкретне місце (at a friend's house) перед ширшим контекстом (after a party). Приклад: *They met at a friend's house after a party.*",
                    "after a party at a friend's house" => '❌ Ширший контекст (after a party) не ставиться перед конкретним місцем. Логіка: від конкретного до загального.',
                ],
            ],
            [
                'id' => 'e1q8',
                'question' => 'Tigers approach {a1}.',
                'options' => ['their victims very slowly', 'very slowly their victims'],
                'correct' => 'their victims very slowly',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'hint' => 'Порядок слів: додаток (their victims) перед прислівником способу (very slowly). Об\'єкт ближче до дієслова.',
                'explanations' => [
                    'their victims very slowly' => '✅ «their victims very slowly» — правильно. Об\'єкт (their victims) стоїть перед прислівником способу (very slowly). Приклад: *Tigers approach their victims very slowly.*',
                    'very slowly their victims' => '❌ Прислівник способу (very slowly) не ставиться перед об\'єктом (their victims). Об\'єкт ближче до дієслова.',
                ],
            ],
            [
                'id' => 'e1q9',
                'question' => 'I saw {a1}.',
                'options' => ['Hellen at the library', 'at the library Hellen'],
                'correct' => 'Hellen at the library',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'hint' => 'Порядок слів: спочатку хто/що (Hellen), потім де (at the library). Об\'єкт передує обставині місця.',
                'explanations' => [
                    'Hellen at the library' => '✅ «Hellen at the library» — правильно. Спочатку називаємо кого бачили (Hellen), потім де (at the library). Приклад: *I saw Hellen at the library.*',
                    'at the library Hellen' => '❌ Місце (at the library) не ставиться перед об\'єктом (Hellen). Особа названа першою.',
                ],
            ],
            [
                'id' => 'e1q10',
                'question' => 'He finished {a1}.',
                'options' => ['very quickly the exam', 'the exam very quickly'],
                'correct' => 'the exam very quickly',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'hint' => 'Порядок слів: додаток (the exam) перед прислівником способу (very quickly). Прислівник способу після об\'єкта.',
                'explanations' => [
                    'the exam very quickly' => '✅ «the exam very quickly» — правильно. Додаток (the exam) стоїть перед прислівником способу (very quickly). Приклад: *He finished the exam very quickly.*',
                    'very quickly the exam' => '❌ Прислівник способу (very quickly) не ставиться перед додатком (the exam). Об\'єкт іде першим.',
                ],
            ],

            // Exercise 2 - Choose the correct options to complete the sentences (10 questions)
            [
                'id' => 'e2q1',
                'question' => 'I walk {a1}.',
                'options' => ['my dog in the park every day', 'my dog every day in the park', 'every day my dog in the park'],
                'correct' => 'my dog in the park every day',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'hint' => 'Порядок слів: об\'єкт (my dog) → місце (in the park) → частота (every day). Стандартний порядок обставин.',
                'explanations' => [
                    'my dog in the park every day' => '✅ «my dog in the park every day» — правильно. Порядок: об\'єкт (my dog) → місце (in the park) → частота (every day). Приклад: *I walk my dog in the park every day.*',
                    'my dog every day in the park' => '❌ Частота (every day) не ставиться перед місцем (in the park). Стандартний порядок: об\'єкт → місце → час.',
                    'every day my dog in the park' => '❌ Частота (every day) не починає фразу після дієслова. Об\'єкт має бути одразу після дієслова.',
                ],
            ],
            [
                'id' => 'e2q2',
                'question' => 'Lisa took {a1}.',
                'options' => ['her son to the doctor this morning', 'to the doctor her son this morning', 'this morning her son to the doctor'],
                'correct' => 'her son to the doctor this morning',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'hint' => 'Порядок слів: об\'єкт (her son) → напрямок (to the doctor) → час (this morning). Об\'єкт завжди після дієслова.',
                'explanations' => [
                    'her son to the doctor this morning' => '✅ «her son to the doctor this morning» — правильно. Порядок: об\'єкт (her son) → напрямок (to the doctor) → час (this morning). Приклад: *Lisa took her son to the doctor this morning.*',
                    'to the doctor her son this morning' => '❌ Напрямок (to the doctor) не ставиться перед об\'єктом (her son). Об\'єкт завжди першим.',
                    'this morning her son to the doctor' => '❌ Час (this morning) не починає фразу. Стандартний порядок: об\'єкт → місце → час.',
                ],
            ],
            [
                'id' => 'e2q3',
                'question' => 'Can you drive {a1}?',
                'options' => ['home me after the concert', 'me after the concert home', 'me home after the concert'],
                'correct' => 'me home after the concert',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'hint' => 'Порядок слів: об\'єкт (me) → місце (home) → час (after the concert). Кого везти → куди → коли.',
                'explanations' => [
                    'me home after the concert' => '✅ «me home after the concert» — правильно. Порядок: об\'єкт (me) → місце (home) → час (after the concert). Приклад: *Can you drive me home after the concert?*',
                    'home me after the concert' => '❌ Місце (home) не ставиться перед об\'єктом (me). Кого везти — першим.',
                    'me after the concert home' => '❌ Час (after the concert) між об\'єктом і місцем порушує порядок. Куди — перед коли.',
                ],
            ],
            [
                'id' => 'e2q4',
                'question' => 'We {a1}.',
                'options' => ['enjoyed very much the trip', 'enjoyed the trip very much', 'very much enjoyed the trip'],
                'correct' => 'enjoyed the trip very much',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'hint' => 'Порядок слів: дієслово (enjoyed) → додаток (the trip) → прислівник міри (very much). Прислівник міри після об\'єкта.',
                'explanations' => [
                    'enjoyed the trip very much' => '✅ «enjoyed the trip very much» — правильно. Дієслово → об\'єкт → прислівник міри. Приклад: *We enjoyed the trip very much.*',
                    'enjoyed very much the trip' => '❌ Прислівник міри (very much) не ставиться перед об\'єктом (the trip). Об\'єкт одразу після дієслова.',
                    'very much enjoyed the trip' => '❌ Прислівник міри (very much) не починає фразу. Прислівник наприкінці.',
                ],
            ],
            [
                'id' => 'e2q5',
                'question' => 'I {a1}.',
                'options' => ['bought in Paris this jacket last year', 'bought this jacket last year in Paris', 'bought this jacket in Paris last year'],
                'correct' => 'bought this jacket in Paris last year',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'hint' => 'Порядок слів: дієслово (bought) → об\'єкт (this jacket) → місце (in Paris) → час (last year). Місце перед часом.',
                'explanations' => [
                    'bought this jacket in Paris last year' => '✅ «bought this jacket in Paris last year» — правильно. Дієслово → об\'єкт → місце → час. Приклад: *I bought this jacket in Paris last year.*',
                    'bought in Paris this jacket last year' => '❌ Місце (in Paris) не передує об\'єкту (this jacket). Об\'єкт після дієслова.',
                    'bought this jacket last year in Paris' => '❌ Час (last year) не ставиться перед місцем (in Paris). Місце перед часом.',
                ],
            ],
            [
                'id' => 'e2q6',
                'question' => 'I {a1}.',
                'options' => ['will call my parents immediately', 'will call immediately my parents', 'immediately will call my parents'],
                'correct' => 'will call my parents immediately',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'hint' => 'Порядок слів: дієслово (will call) → об\'єкт (my parents) → прислівник часу (immediately). Прислівник наприкінці.',
                'explanations' => [
                    'will call my parents immediately' => '✅ «will call my parents immediately» — правильно. Дієслово → об\'єкт → прислівник часу. Приклад: *I will call my parents immediately.*',
                    'will call immediately my parents' => '❌ Прислівник (immediately) не розділяє дієслово і об\'єкт. Прислівник наприкінці.',
                    'immediately will call my parents' => '❌ Прислівник (immediately) не починає фразу в такому реченні. Прислівник наприкінці.',
                ],
            ],
            [
                'id' => 'e2q7',
                'question' => 'David {a1}.',
                'options' => ['very much loves his children', 'loves his children very much', 'loves very much his children'],
                'correct' => 'loves his children very much',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'hint' => 'Порядок слів: дієслово (loves) → додаток (his children) → прислівник міри (very much). Прислівник міри наприкінці.',
                'explanations' => [
                    'loves his children very much' => '✅ «loves his children very much» — правильно. Дієслово → об\'єкт → прислівник міри. Приклад: *David loves his children very much.*',
                    'very much loves his children' => '❌ Прислівник міри (very much) не ставиться перед дієсловом. Прислівник наприкінці.',
                    'loves very much his children' => '❌ Прислівник міри (very much) не розділяє дієслово і об\'єкт. Об\'єкт одразу після дієслова.',
                ],
            ],
            [
                'id' => 'e2q8',
                'question' => 'I learnt {a1}.',
                'options' => ['Italian in Rome during a student exchange program', 'Italian during a student exchange program in Rome', 'in Rome Italian during a student exchange program'],
                'correct' => 'Italian in Rome during a student exchange program',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'hint' => 'Порядок слів: об\'єкт (Italian) → місце (in Rome) → час/подія (during a student exchange program). Від головного до деталей.',
                'explanations' => [
                    'Italian in Rome during a student exchange program' => '✅ «Italian in Rome during a student exchange program» — правильно. Порядок: об\'єкт (Italian) → місце (in Rome) → час/подія (during...). Приклад: *I learnt Italian in Rome during a student exchange program.*',
                    'Italian during a student exchange program in Rome' => '❌ Подія (during...) не ставиться перед місцем (in Rome). Місце ближче до об\'єкта.',
                    'in Rome Italian during a student exchange program' => '❌ Місце (in Rome) не передує об\'єкту (Italian). Об\'єкт завжди після дієслова.',
                ],
            ],
            [
                'id' => 'e2q9',
                'question' => 'We {a1}.',
                'options' => ['always finish school at 3.30 pm', 'finish always school at 3.30 pm', 'finish school at 3.30 pm always'],
                'correct' => 'always finish school at 3.30 pm',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'hint' => 'Порядок слів: прислівник частоти (always) перед дієсловом (finish), потім об\'єкт (school) → час (at 3.30 pm).',
                'explanations' => [
                    'always finish school at 3.30 pm' => '✅ «always finish school at 3.30 pm» — правильно. Прислівник частоти (always) перед дієсловом, час наприкінці. Приклад: *We always finish school at 3.30 pm.*',
                    'finish always school at 3.30 pm' => '❌ Прислівник частоти (always) не ставиться між дієсловом і об\'єктом. Частота перед дієсловом.',
                    'finish school at 3.30 pm always' => '❌ Прислівник частоти (always) зазвичай не стоїть наприкінці такого речення. Частота перед дієсловом.',
                ],
            ],
            [
                'id' => 'e2q10',
                'question' => 'I {a1}.',
                'options' => ["don't play very often tennis", "don't like tennis very much", "don't like very much tennis"],
                'correct' => "don't like tennis very much",
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'hint' => 'Порядок слів: дієслово (like) → об\'єкт (tennis) → прислівник міри (very much). Об\'єкт перед прислівником.',
                'explanations' => [
                    "don't like tennis very much" => "✅ «don't like tennis very much» — правильно. Дієслово → об'єкт → прислівник міри. Приклад: *I don't like tennis very much.*",
                    "don't play very often tennis" => '❌ Це інше дієслово (play), а не like. Крім того, порядок слів неправильний.',
                    "don't like very much tennis" => "❌ Прислівник міри (very much) не ставиться перед об'єктом (tennis). Об'єкт одразу після дієслова.",
                ],
            ],

            // Exercise 3 - Choose the correct sentence (word order) (10 questions)
            [
                'id' => 'e3q1',
                'question' => 'We {a1}.  (a nice lunch / had / today / we / at a restaurant)',
                'options' => ['had a nice lunch at a restaurant today', 'had a nice lunch today at a restaurant', 'had at a restaurant a nice lunch today'],
                'correct' => 'had a nice lunch at a restaurant today',
                'level' => 'A1',
                'detail' => 'sentence_structure',
                'structure' => 'choose_order',
                'hint' => 'Порядок слів: дієслово (had) → об\'єкт (a nice lunch) → місце (at a restaurant) → час (today). Стандартна структура речення.',
                'explanations' => [
                    'had a nice lunch at a restaurant today' => '✅ «had a nice lunch at a restaurant today» — правильно. Порядок: дієслово → об\'єкт → місце → час. Приклад: *We had a nice lunch at a restaurant today.*',
                    'had a nice lunch today at a restaurant' => '❌ Час (today) не ставиться перед місцем (at a restaurant). Місце перед часом.',
                    'had at a restaurant a nice lunch today' => '❌ Місце (at a restaurant) не розділяє дієслово і об\'єкт. Об\'єкт одразу після дієслова.',
                ],
            ],
            [
                'id' => 'e3q2',
                'question' => 'He {a1}.  (the keys / last night / in the car / forgot / He)',
                'options' => ['forgot the keys in the car last night', 'forgot in the car the keys last night', 'forgot the keys last night in the car'],
                'correct' => 'forgot the keys in the car last night',
                'level' => 'A1',
                'detail' => 'sentence_structure',
                'structure' => 'choose_order',
                'hint' => 'Порядок слів: дієслово (forgot) → об\'єкт (the keys) → місце (in the car) → час (last night). Предмет перед місцем.',
                'explanations' => [
                    'forgot the keys in the car last night' => '✅ «forgot the keys in the car last night» — правильно. Порядок: дієслово → об\'єкт → місце → час. Приклад: *He forgot the keys in the car last night.*',
                    'forgot in the car the keys last night' => '❌ Місце (in the car) не передує об\'єкту (the keys). Предмет називаємо першим.',
                    'forgot the keys last night in the car' => '❌ Час (last night) не ставиться перед місцем (in the car). Місце перед часом.',
                ],
            ],
            [
                'id' => 'e3q3',
                'question' => 'Susan {a1}.  (often / Susan / takes / to work / a taxi)',
                'options' => ['often takes a taxi to work', 'takes often a taxi to work', 'takes a taxi often to work'],
                'correct' => 'often takes a taxi to work',
                'level' => 'A1',
                'detail' => 'sentence_structure',
                'structure' => 'choose_order',
                'hint' => 'Порядок слів: прислівник частоти (often) перед дієсловом (takes), потім об\'єкт (a taxi) → напрямок (to work).',
                'explanations' => [
                    'often takes a taxi to work' => '✅ «often takes a taxi to work» — правильно. Прислівник частоти (often) перед дієсловом, потім об\'єкт → напрямок. Приклад: *Susan often takes a taxi to work.*',
                    'takes often a taxi to work' => '❌ Прислівник частоти (often) не ставиться між дієсловом і об\'єктом. Частота перед дієсловом.',
                    'takes a taxi often to work' => '❌ Прислівник частоти (often) не розділяє об\'єкт і напрямок. Частота на початку.',
                ],
            ],
            [
                'id' => 'e3q4',
                'question' => 'I {a1}.  (have to return / I / the book / to the library / today)',
                'options' => ['have to return the book to the library today', 'have to return today the book to the library', 'have to return to the library the book today'],
                'correct' => 'have to return the book to the library today',
                'level' => 'A1',
                'detail' => 'sentence_structure',
                'structure' => 'choose_order',
                'hint' => 'Порядок слів: модальна фраза (have to return) → об\'єкт (the book) → місце (to the library) → час (today).',
                'explanations' => [
                    'have to return the book to the library today' => '✅ «have to return the book to the library today» — правильно. Модальна фраза → об\'єкт → місце → час. Приклад: *I have to return the book to the library today.*',
                    'have to return today the book to the library' => '❌ Час (today) не ставиться між фразою і об\'єктом. Час наприкінці.',
                    'have to return to the library the book today' => '❌ Місце (to the library) не передує об\'єкту (the book). Об\'єкт після дієслова.',
                ],
            ],
            [
                'id' => 'e3q5',
                'question' => 'They {a1}.  (are / never / at lunchtime / They / at home)',
                'options' => ['are never at home at lunchtime', 'are never at lunchtime at home', 'never are at home at lunchtime'],
                'correct' => 'are never at home at lunchtime',
                'level' => 'A1',
                'detail' => 'sentence_structure',
                'structure' => 'choose_order',
                'hint' => 'Порядок слів: дієслово be → прислівник частоти (never) → місце (at home) → час (at lunchtime).',
                'explanations' => [
                    'are never at home at lunchtime' => '✅ «are never at home at lunchtime» — правильно. Дієслово be → прислівник частоти → місце → час. Приклад: *They are never at home at lunchtime.*',
                    'are never at lunchtime at home' => '❌ Час (at lunchtime) не ставиться перед місцем (at home). Місце перед часом.',
                    'never are at home at lunchtime' => '❌ Прислівник частоти (never) ставиться після дієслова be, а не перед ним.',
                ],
            ],
            [
                'id' => 'e3q6',
                'question' => 'It {a1}.  (It / always / very hot / here / in July / is)',
                'options' => ['is always very hot here in July', 'is very hot always here in July', 'is always very hot in July here'],
                'correct' => 'is always very hot here in July',
                'level' => 'A1',
                'detail' => 'sentence_structure',
                'structure' => 'choose_order',
                'hint' => 'Порядок слів: дієслово (is) → прислівник частоти (always) → прикметник (very hot) → місце (here) → час (in July).',
                'explanations' => [
                    'is always very hot here in July' => '✅ «is always very hot here in July» — правильно. Be → прислівник частоти → прикметник → місце → час. Приклад: *It is always very hot here in July.*',
                    'is very hot always here in July' => '❌ Прислівник частоти (always) має стояти одразу після дієслова be, не в середині.',
                    'is always very hot in July here' => '❌ Місце (here) не ставиться після часу (in July). Місце перед часом.',
                ],
            ],
            [
                'id' => 'e3q7',
                'question' => 'We {a1}.  (play / football / after school / in the park / We)',
                'options' => ['play football in the park after school', 'play football after school in the park', 'play in the park football after school'],
                'correct' => 'play football in the park after school',
                'level' => 'A1',
                'detail' => 'sentence_structure',
                'structure' => 'choose_order',
                'hint' => 'Порядок слів: дієслово (play) → об\'єкт (football) → місце (in the park) → час (after school).',
                'explanations' => [
                    'play football in the park after school' => '✅ «play football in the park after school» — правильно. Дієслово → об\'єкт → місце → час. Приклад: *We play football in the park after school.*',
                    'play football after school in the park' => '❌ Час (after school) не ставиться перед місцем (in the park). Місце перед часом.',
                    'play in the park football after school' => '❌ Місце (in the park) не розділяє дієслово і об\'єкт. Об\'єкт одразу після дієслова.',
                ],
            ],
            [
                'id' => 'e3q8',
                'question' => 'She {a1}.  (usually / She / cereal / has / for breakfast)',
                'options' => ['usually has cereal for breakfast', 'has usually cereal for breakfast', 'has cereal usually for breakfast'],
                'correct' => 'usually has cereal for breakfast',
                'level' => 'A1',
                'detail' => 'sentence_structure',
                'structure' => 'choose_order',
                'hint' => 'Порядок слів: прислівник частоти (usually) перед дієсловом (has), потім об\'єкт (cereal) → обставина (for breakfast).',
                'explanations' => [
                    'usually has cereal for breakfast' => '✅ «usually has cereal for breakfast» — правильно. Прислівник частоти перед дієсловом, потім об\'єкт → обставина. Приклад: *She usually has cereal for breakfast.*',
                    'has usually cereal for breakfast' => '❌ Прислівник частоти (usually) не ставиться між дієсловом і об\'єктом. Частота перед дієсловом.',
                    'has cereal usually for breakfast' => '❌ Прислівник частоти (usually) не розділяє об\'єкт і обставину. Частота на початку.',
                ],
            ],
            [
                'id' => 'e3q9',
                'question' => 'Linda {a1}.  (met / at a nightclub / in 2006 / Linda / her husband)',
                'options' => ['met her husband at a nightclub in 2006', 'met her husband in 2006 at a nightclub', 'met at a nightclub her husband in 2006'],
                'correct' => 'met her husband at a nightclub in 2006',
                'level' => 'A1',
                'detail' => 'sentence_structure',
                'structure' => 'choose_order',
                'hint' => 'Порядок слів: дієслово (met) → об\'єкт (her husband) → місце (at a nightclub) → час (in 2006).',
                'explanations' => [
                    'met her husband at a nightclub in 2006' => '✅ «met her husband at a nightclub in 2006» — правильно. Дієслово → об\'єкт → місце → час. Приклад: *Linda met her husband at a nightclub in 2006.*',
                    'met her husband in 2006 at a nightclub' => '❌ Час (in 2006) не ставиться перед місцем (at a nightclub). Місце перед часом.',
                    'met at a nightclub her husband in 2006' => '❌ Місце (at a nightclub) не передує об\'єкту (her husband). Об\'єкт після дієслова.',
                ],
            ],
            [
                'id' => 'e3q10',
                'question' => 'I {a1}.  (always / I / from home / work / on Tuesdays)',
                'options' => ['always work from home on Tuesdays', 'work from home always on Tuesdays', 'work always from home on Tuesdays'],
                'correct' => 'always work from home on Tuesdays',
                'level' => 'A1',
                'detail' => 'sentence_structure',
                'structure' => 'choose_order',
                'hint' => 'Порядок слів: прислівник частоти (always) перед дієсловом (work), потім місце (from home) → час (on Tuesdays).',
                'explanations' => [
                    'always work from home on Tuesdays' => '✅ «always work from home on Tuesdays» — правильно. Прислівник частоти перед дієсловом, потім місце → час. Приклад: *I always work from home on Tuesdays.*',
                    'work from home always on Tuesdays' => '❌ Прислівник частоти (always) має стояти перед дієсловом, не після місця.',
                    'work always from home on Tuesdays' => '❌ Прислівник частоти (always) не ставиться між дієсловом і місцем. Частота перед дієсловом.',
                ],
            ],
        ];

        $items = [];
        $meta = [];

        foreach ($questions as $index => $question) {
            $uuid = $this->generateQuestionUuid($question['level'], $index, $question['id']);

            $answer = $question['correct'];
            $options = $question['options'];

            $answers = [
                [
                    'marker' => 'a1',
                    'answer' => $answer,
                    'verb_hint' => null,
                ],
            ];

            $optionMarkers = [];
            foreach ($options as $option) {
                $optionMarkers[$option] = 'a1';
            }

            $tagIds = [
                $themeTagId,
                $detailTags[$question['detail']],
                $structureTagIds[$question['structure']],
            ];

            $items[] = [
                'uuid' => $uuid,
                'question' => $question['question'],
                'category_id' => $categoryId,
                'difficulty' => $levelDifficulty[$question['level']] ?? 3,
                'source_id' => $sourceId,
                'flag' => 0,
                'level' => $question['level'],
                'tag_ids' => array_values(array_unique($tagIds)),
                'answers' => $answers,
                'options' => $options,
                'variants' => [],
            ];

            $meta[] = [
                'uuid' => $uuid,
                'answers' => ['a1' => $answer],
                'option_markers' => $optionMarkers,
                'hints' => ['a1' => $question['hint']],
                'explanations' => $question['explanations'],
            ];
        }

        $this->seedQuestionData($items, $meta);
    }
}
