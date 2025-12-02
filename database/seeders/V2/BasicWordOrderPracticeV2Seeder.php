<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class BasicWordOrderPracticeV2Seeder extends QuestionSeeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Basic Word Order Practice V2'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Custom: Basic Word Order Practice V2'])->id;

        $themeTagId = Tag::firstOrCreate([
            'name' => 'Basic Word Order Practice',
        ], ['category' => 'English Grammar Theme'])->id;

        $detailTagId = Tag::firstOrCreate([
            'name' => 'Word Order Focus',
        ], ['category' => 'English Grammar Detail'])->id;

        $structureTagId = Tag::firstOrCreate([
            'name' => 'Basic Word Order Sentence',
        ], ['category' => 'English Grammar Structure'])->id;

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
                'question' => "I didn't like {a1} last night.",
                'options' => ['very much the food', 'the food very much'],
                'answers' => ['a1' => 'the food very much'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Спочатку називаємо додаток, потім прислівник міри (наприклад, very much).",
                ],
                'explanations' => [
                    'very much the food' => '❌ Прислівник міри не ставимо перед основним додатком у такому реченні.',
                    'the food very much' => '✅ Прямий додаток іде перед прислівником міри, тому порядок природний.',
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'We arrived {a1}.',
                'options' => ['home very late', 'very late home'],
                'answers' => ['a1' => 'home very late'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Місце після дієслова, потім обставина часу/способу (напр., very late).",
                ],
                'explanations' => [
                    'home very late' => '✅ Спочатку місце (home), а вже потім обставина часу/способу.',
                    'very late home' => '❌ Обставина very late перед місцем звучить неприродно.',
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'Teresa speaks {a1}.',
                'options' => ['very well English', 'English very well'],
                'answers' => ['a1' => 'English very well'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Прямий додаток (що?) перед прислівником способу (how?).",
                ],
                'explanations' => [
                    'very well English' => '❌ Прислівник способу не ставимо перед об’єктом у такій конструкції.',
                    'English very well' => '✅ Об’єкт (English) стоїть перед прислівником способу, що відповідає звичному порядку.',
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'Please, can you put {a1}?',
                'options' => ['in the corner that box', 'that box in the corner'],
                'answers' => ['a1' => 'that box in the corner'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Спочатку додаток, потім обставина місця.",
                ],
                'explanations' => [
                    'in the corner that box' => '❌ Обставина місця не має випереджати прямий додаток у цьому реченні.',
                    'that box in the corner' => '✅ Предмет називаємо першим, а уточнення місця додаємо після нього.',
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'After the accident, Jim was {a1}.',
                'options' => ['for a week in hospital', 'in hospital for a week'],
                'answers' => ['a1' => 'in hospital for a week'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Обставина місця зазвичай перед тривалістю часу.",
                ],
                'explanations' => [
                    'for a week in hospital' => '❌ Тривалість часу ставити перед місцем звучить неприродно.',
                    'in hospital for a week' => '✅ Спершу вказуємо місце, а потім тривалість перебування.',
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'I like {a1}.',
                'options' => ['this music a lot', 'a lot this music'],
                'answers' => ['a1' => 'this music a lot'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Додаток (що?) перед прислівником міри a lot.",
                ],
                'explanations' => [
                    'this music a lot' => '✅ Об’єкт ставиться перед коротким прислівником міри.',
                    'a lot this music' => '❌ Переміщення a lot уперед порушує стандартний порядок.',
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'They met {a1}.',
                'options' => ["at a friend's house after a party", "after a party at a friend's house"],
                'answers' => ['a1' => "at a friend's house after a party"],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Спершу конкретніше місце, потім ширший контекст часу/події.",
                ],
                'explanations' => [
                    "at a friend's house after a party" => '✅ Конкретне місце перед обставиною часу/події звучить природно.',
                    "after a party at a friend's house" => '❌ Починати з after a party зміщує логіку опису місця.',
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'Tigers approach {a1}.',
                'options' => ['their victims very slowly', 'very slowly their victims'],
                'answers' => ['a1' => 'their victims very slowly'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Додаток перед прислівником способу.",
                ],
                'explanations' => [
                    'their victims very slowly' => '✅ Об’єкт ставиться перед описом способу дії.',
                    'very slowly their victims' => '❌ Переміщення very slowly уперед порушує типовий порядок.',
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'I saw {a1}.',
                'options' => ['Hellen at the library', 'at the library Hellen'],
                'answers' => ['a1' => 'Hellen at the library'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Спочатку хто/що бачили, потім де.",
                ],
                'explanations' => [
                    'Hellen at the library' => '✅ Спершу називаємо особу, а місце додаємо після.',
                    'at the library Hellen' => '❌ Починати з місця у такому простому реченні неприродно.',
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'He finished {a1}.',
                'options' => ['very quickly the exam', 'the exam very quickly'],
                'answers' => ['a1' => 'the exam very quickly'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Додаток перед прислівником швидкості/способу.",
                ],
                'explanations' => [
                    'very quickly the exam' => '❌ Прислівник не ставимо перед прямим додатком у цій конструкції.',
                    'the exam very quickly' => '✅ Об’єкт іде першим, а опис швидкості додається опісля.',
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'I walk {a1}.',
                'options' => ['my dog in the park every day', 'my dog every day in the park', 'every day my dog in the park'],
                'answers' => ['a1' => 'my dog in the park every day'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Порядок: об’єкт → місце → частота/час.",
                ],
                'explanations' => [
                    'my dog in the park every day' => '✅ Прямий додаток, потім місце, далі частота — природний порядок.',
                    'my dog every day in the park' => '❌ Частота перед місцем робить речення менш природним.',
                    'every day my dog in the park' => '❌ Починати з частоти й одразу ставити об’єкт порушує типовий порядок.',
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'Lisa took {a1}.',
                'options' => ['her son to the doctor this morning', 'to the doctor her son this morning', 'this morning her son to the doctor'],
                'answers' => ['a1' => 'her son to the doctor this morning'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Спочатку об’єкт, далі напрямок/місце, потім час.",
                ],
                'explanations' => [
                    'her son to the doctor this morning' => '✅ Дитина як об’єкт, місце призначення потім, час наприкінці.',
                    'to the doctor her son this morning' => '❌ Речення починається з обставини напрямку, що робить порядок неоковирним.',
                    'this morning her son to the doctor' => '❌ Час перед об’єктом і місцем порушує стандартний порядок.',
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'Can you drive {a1}?',
                'options' => ['home me after the concert', 'me after the concert home', 'me home after the concert'],
                'answers' => ['a1' => 'me home after the concert'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Об’єкт → місце призначення → час.",
                ],
                'explanations' => [
                    'home me after the concert' => '❌ Починати з місця, а потім ставити об’єкт у такому реченні незвично.',
                    'me after the concert home' => '❌ Час між об’єктом і місцем ламає природний порядок.',
                    'me home after the concert' => '✅ Об’єкт спершу, потім куди, і лише потім час.',
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'We {a1}.',
                'options' => ['enjoyed very much the trip', 'enjoyed the trip very much', 'very much enjoyed the trip'],
                'answers' => ['a1' => 'enjoyed the trip very much'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Додаток після дієслова, прислівник міри наприкінці.",
                ],
                'explanations' => [
                    'enjoyed very much the trip' => '❌ Прислівник міри перед додатком робить порядок неприродним.',
                    'enjoyed the trip very much' => '✅ Після дієслова йде об’єкт, а далі посилювач почуття.',
                    'very much enjoyed the trip' => '❌ Розділяти прислівником початок речення недоречно.',
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'I {a1}.',
                'options' => ['bought in Paris this jacket last year', 'bought this jacket last year in Paris', 'bought this jacket in Paris last year'],
                'answers' => ['a1' => 'bought this jacket in Paris last year'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Порядок обставин: місце перед часом у кінці речення.",
                ],
                'explanations' => [
                    'bought in Paris this jacket last year' => '❌ Місце перед додатком порушує природний порядок.',
                    'bought this jacket last year in Paris' => '❌ Час перед місцем наприкінці виглядає менш природно.',
                    'bought this jacket in Paris last year' => '✅ Об’єкт після дієслова, потім місце, а далі час.',
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'I {a1}.',
                'options' => ['will call my parents immediately', 'will call immediately my parents', 'immediately will call my parents'],
                'answers' => ['a1' => 'will call my parents immediately'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Об’єкт після дієслова, короткий прислівник часу наприкінці.",
                ],
                'explanations' => [
                    'will call my parents immediately' => '✅ Після дієслова йде об’єкт, а потім додаткове слово часу.',
                    'will call immediately my parents' => '❌ Вставляти прислівник між дієсловом та об’єктом тут не слід.',
                    'immediately will call my parents' => '❌ Починати з прислівника часу у цьому простому реченні неприродно.',
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'David {a1}.',
                'options' => ['very much loves his children', 'loves his children very much', 'loves very much his children'],
                'answers' => ['a1' => 'loves his children very much'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Додаток після дієслова, прислівник міри після додатка.",
                ],
                'explanations' => [
                    'very much loves his children' => '❌ Починати з very much не потрібно.',
                    'loves his children very much' => '✅ Об’єкт одразу після дієслова, посилювач почуття наприкінці.',
                    'loves very much his children' => '❌ Прислівник усередині фрази порушує послідовність.',
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'I learnt {a1}.',
                'options' => ['Italian in Rome during a student exchange program', 'Italian during a student exchange program in Rome', 'in Rome Italian during a student exchange program'],
                'answers' => ['a1' => 'Italian in Rome during a student exchange program'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Об’єкт → місце → обставина часу/події наприкінці.",
                ],
                'explanations' => [
                    'Italian in Rome during a student exchange program' => '✅ Після об’єкта йде місце, а потім опис програми/часу.',
                    'Italian during a student exchange program in Rome' => '❌ Час/подія перед уточненням місця робить порядок менш логічним.',
                    'in Rome Italian during a student exchange program' => '❌ Починати з місця перед об’єктом тут не слід.',
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'We {a1}.',
                'options' => ['always finish school at 3.30 pm', 'finish always school at 3.30 pm', 'finish school at 3.30 pm always'],
                'answers' => ['a1' => 'always finish school at 3.30 pm'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Звичний прислівник частоти ставимо перед основним дієсловом.",
                ],
                'explanations' => [
                    'always finish school at 3.30 pm' => '✅ Прислівник частоти стоїть перед дієсловом, час наприкінці.',
                    'finish always school at 3.30 pm' => '❌ Розривати дієслово й об’єкт прислівником частоти небажано.',
                    'finish school at 3.30 pm always' => '❌ Частоту наприкінці такого речення вживають рідко.',
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'I {a1}.',
                'options' => ["don't play very often tennis", "don't like tennis very much", "don't like very much tennis"],
                'answers' => ['a1' => "don't like tennis very much"],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Після дієслова like одразу йде об’єкт, посилювач наприкінці.",
                ],
                'explanations' => [
                    "don't play very often tennis" => '❌ Інший дієслово (play) не підходить до змісту про симпатію.',
                    "don't like tennis very much" => '✅ Об’єкт відразу після like, прислівник міри наприкінці.',
                    "don't like very much tennis" => '❌ Прислівник міри не ставимо перед об’єктом.',
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'We {a1}.  (a nice lunch / had / today / we / at a restaurant)',
                'options' => ['had a nice lunch at a restaurant today', 'had a nice lunch today at a restaurant', 'had at a restaurant a nice lunch today'],
                'answers' => ['a1' => 'had a nice lunch at a restaurant today'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Порядок: дієслово → об’єкт → місце → час.",
                ],
                'explanations' => [
                    'had a nice lunch at a restaurant today' => '✅ Після дієслова йде повний об’єкт, далі місце та час.',
                    'had a nice lunch today at a restaurant' => '❌ Час перед місцем наприкінці звучить менш природно.',
                    'had at a restaurant a nice lunch today' => '❌ Вставляти місце всередину об’єкта недоречно.',
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'He {a1}.  (the keys / last night / in the car / forgot / He)',
                'options' => ['forgot the keys in the car last night', 'forgot in the car the keys last night', 'forgot the keys last night in the car'],
                'answers' => ['a1' => 'forgot the keys in the car last night'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Об’єкт після дієслова, місце далі, час наприкінці.",
                ],
                'explanations' => [
                    'forgot the keys in the car last night' => '✅ Ключі як об’єкт, місце після, час наприкінці.',
                    'forgot in the car the keys last night' => '❌ Місце перед об’єктом робить речення кострубате.',
                    'forgot the keys last night in the car' => '❌ Час перед уточненням місця наприкінці менш логічний.',
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'Susan {a1}.  (often / Susan / takes / to work / a taxi)',
                'options' => ['often takes a taxi to work', 'takes often a taxi to work', 'takes a taxi often to work'],
                'answers' => ['a1' => 'often takes a taxi to work'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Прислівник частоти перед дієсловом, об’єкт одразу після нього.",
                ],
                'explanations' => [
                    'often takes a taxi to work' => '✅ Частота перед основним дієсловом, далі об’єкт і місце.',
                    'takes often a taxi to work' => '❌ Вставляти often після дієслова й перед об’єктом небажано.',
                    'takes a taxi often to work' => '❌ Розривати об’єкт прислівником частоти робить фразу незручною.',
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'I {a1}.  (have to return / I / the book / to the library / today)',
                'options' => ['have to return the book to the library today', 'have to return today the book to the library', 'have to return to the library the book today'],
                'answers' => ['a1' => 'have to return the book to the library today'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Після модальної частини ставимо об’єкт, далі місце, потім час.",
                ],
                'explanations' => [
                    'have to return the book to the library today' => '✅ Об’єкт після return, місце далі, час наприкінці.',
                    'have to return today the book to the library' => '❌ Час посередині фрази робить її важкою.',
                    'have to return to the library the book today' => '❌ Місце перед об’єктом ускладнює сприйняття.',
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'They {a1}.  (are / never / at lunchtime / They / at home)',
                'options' => ['are never at home at lunchtime', 'are never at lunchtime at home', 'never are at home at lunchtime'],
                'answers' => ['a1' => 'are never at home at lunchtime'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Прислівник частоти після допоміжного дієслова, місце перед часовою обставиною.",
                ],
                'explanations' => [
                    'are never at home at lunchtime' => '✅ Never після are, місце далі, час наприкінці.',
                    'are never at lunchtime at home' => '❌ Час перед місцем виглядає незграбно.',
                    'never are at home at lunchtime' => '❌ Починати з never перед формою be не природно у твердженні.',
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'It {a1}.  (It / always / very hot / here / in July / is)',
                'options' => ['is always very hot here in July', 'is very hot always here in July', 'is always very hot in July here'],
                'answers' => ['a1' => 'is always very hot here in July'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Після дієслова be ставимо прислівник частоти, місце й час наприкінці.",
                ],
                'explanations' => [
                    'is always very hot here in July' => '✅ Always після is, далі прикметник, потім місце і час.',
                    'is very hot always here in July' => '❌ Прислівник посеред опису робить порядок незручним.',
                    'is always very hot in July here' => '❌ Розміщення місця після часу звучить менш природно.',
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'We {a1}.  (play / football / after school / in the park / We)',
                'options' => ['play football in the park after school', 'play football after school in the park', 'play in the park football after school'],
                'answers' => ['a1' => 'play football in the park after school'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Після дієслова об’єкт, потім місце, а далі час.",
                ],
                'explanations' => [
                    'play football in the park after school' => '✅ Об’єкт football після дієслова, місце далі, час наприкінці.',
                    'play football after school in the park' => '❌ Час перед місцем виглядає менш природно.',
                    'play in the park football after school' => '❌ Місце між дієсловом і об’єктом створює незручний порядок.',
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'She {a1}.  (usually / She / cereal / has / for breakfast)',
                'options' => ['usually has cereal for breakfast', 'has usually cereal for breakfast', 'has cereal usually for breakfast'],
                'answers' => ['a1' => 'usually has cereal for breakfast'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Прислівник частоти перед основним дієсловом have.",
                ],
                'explanations' => [
                    'usually has cereal for breakfast' => '✅ Usually перед has, далі об’єкт і обставина прийому їжі.',
                    'has usually cereal for breakfast' => '❌ Вставка usually після has порушує плавність.',
                    'has cereal usually for breakfast' => '❌ Прислівник у середині об’єктної групи звучить неприродно.',
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'Linda {a1}.  (met / at a nightclub / in 2006 / Linda / her husband)',
                'options' => ['met her husband at a nightclub in 2006', 'met her husband in 2006 at a nightclub', 'met at a nightclub her husband in 2006'],
                'answers' => ['a1' => 'met her husband at a nightclub in 2006'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Після дієслова об’єкт, потім місце, час наприкінці.",
                ],
                'explanations' => [
                    'met her husband at a nightclub in 2006' => '✅ Об’єкт одразу після дієслова, місце далі, час у кінці.',
                    'met her husband in 2006 at a nightclub' => '❌ Час перед місцем наприкінці менш природний.',
                    'met at a nightclub her husband in 2006' => '❌ Місце перед об’єктом робить речення незграбним.',
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'I {a1}.  (always / I / from home / work / on Tuesdays)',
                'options' => ['always work from home on Tuesdays', 'work from home always on Tuesdays', 'work always from home on Tuesdays'],
                'answers' => ['a1' => 'always work from home on Tuesdays'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Прислівник частоти перед дієсловом work, обставина місця та часу далі.",
                ],
                'explanations' => [
                    'always work from home on Tuesdays' => '✅ Частота перед дієсловом, потім місце й час.',
                    'work from home always on Tuesdays' => '❌ Розміщення always після місця робить фразу менш природною.',
                    'work always from home on Tuesdays' => '❌ Вставка always між дієсловом та місцем порушує порядок.',
                ],
                'level' => 'A1',
            ],
        ];

        $items = [];
        $meta = [];

        foreach ($questions as $index => $question) {
            $uuid = $this->generateQuestionUuid($question['level'], $index, $question['question']);

            $answers = [
                [
                    'marker' => 'a1',
                    'answer' => $question['answers']['a1'],
                    'verb_hint' => $this->normalizeHint($question['verb_hint']['a1'] ?? null),
                ],
            ];

            $optionMarkers = [];
            foreach ($question['options'] as $option) {
                $optionMarkers[$option] = 'a1';
            }

            $items[] = [
                'uuid' => $uuid,
                'question' => $question['question'],
                'category_id' => $categoryId,
                'difficulty' => $levelDifficulty[$question['level']] ?? 3,
                'source_id' => $sourceId,
                'flag' => 0,
                'level' => $question['level'],
                'tag_ids' => [$themeTagId, $detailTagId, $structureTagId],
                'answers' => $answers,
                'options' => $question['options'],
                'variants' => [],
            ];

            $meta[] = [
                'uuid' => $uuid,
                'answers' => $question['answers'],
                'option_markers' => $optionMarkers,
                'hints' => $question['hints'],
                'explanations' => $question['explanations'],
            ];
        }

        $this->seedQuestionData($items, $meta);
    }

    protected function normalizeHint(?string $hint): ?string
    {
        if ($hint === null) {
            return null;
        }

        return trim($hint, "() \t\n\r");
    }
}
