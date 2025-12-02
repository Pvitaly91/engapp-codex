<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class BasicWordOrderComprehensiveAiSeeder extends QuestionSeeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Basic Word Order Comprehensive AI'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'https://test-english.com/grammar-points/a1/basic-word-order-in-english/'])->id;

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Basic Word Order Focus'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTags = [
            'object_adverb_order' => Tag::firstOrCreate(
                ['name' => 'Object Before Adverb Order'],
                ['category' => 'English Grammar Detail']
            )->id,
            'place_time_order' => Tag::firstOrCreate(
                ['name' => 'Place Before Time Order'],
                ['category' => 'English Grammar Detail']
            )->id,
            'frequency_adverb_order' => Tag::firstOrCreate(
                ['name' => 'Frequency Adverb Position'],
                ['category' => 'English Grammar Detail']
            )->id,
        ];

        $structureTags = [
            'object_adverb' => Tag::firstOrCreate(
                ['name' => 'Object + Adverb Structure'],
                ['category' => 'English Grammar Structure']
            )->id,
            'place_time' => Tag::firstOrCreate(
                ['name' => 'Place + Time Structure'],
                ['category' => 'English Grammar Structure']
            )->id,
            'frequency_verb' => Tag::firstOrCreate(
                ['name' => 'Frequency + Verb Structure'],
                ['category' => 'English Grammar Structure']
            )->id,
        ];

        $levelDifficulty = [
            'A1' => 1,
            'A2' => 2,
            'B1' => 3,
        ];

        $questions = [
            // Exercise 1 - Fill in the gaps with the correct options
            [
                'question' => "I didn't like {a1} last night.",
                'options' => ['very much the food', 'the food very much'],
                'answers' => ['a1' => 'the food very much'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Порядок слів: об'єкт (що?) + прислівник міри (скільки?). В англійській мові додаток ставиться безпосередньо після дієслова, а прислівник міри (very much) іде наприкінці речення.",
                ],
                'explanations' => [
                    'very much the food' => "❌ Неправильний порядок слів. Прислівник міри 'very much' не може стояти перед додатком 'the food'. В англійській мові порядок: дієслово → об'єкт → прислівник міри.",
                    'the food very much' => "✅ Правильний порядок: спочатку об'єкт 'the food' (що?), потім прислівник міри 'very much' (скільки?). Це стандартна структура англійського речення.",
                ],
                'level' => 'A1',
                'detail' => 'object_adverb_order',
                'structure' => 'object_adverb',
            ],
            [
                'question' => "We arrived {a1}.",
                'options' => ['home very late', 'very late home'],
                'answers' => ['a1' => 'home very late'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Порядок: місце (куди?) → час/спосіб (коли?/як?). Після дієслова руху спочатку вказуємо напрямок, а потім обставину часу чи способу.",
                ],
                'explanations' => [
                    'home very late' => "✅ Правильний порядок: 'home' (місце/напрямок) перед 'very late' (обставина часу). Це природний порядок слів в англійській мові.",
                    'very late home' => "❌ Неправильний порядок. Обставина часу 'very late' не повинна стояти перед напрямком 'home'. Спочатку вказуємо куди, потім коли.",
                ],
                'level' => 'A1',
                'detail' => 'place_time_order',
                'structure' => 'place_time',
            ],
            [
                'question' => "Teresa speaks {a1}.",
                'options' => ['very well English', 'English very well'],
                'answers' => ['a1' => 'English very well'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Порядок: об'єкт (що?) → прислівник способу (як?). Після дієслова 'speak' спочатку вказуємо мову, потім якість володіння нею.",
                ],
                'explanations' => [
                    'very well English' => "❌ Неправильний порядок. Прислівник способу 'very well' не ставиться перед об'єктом 'English'. В англійській мові об'єкт іде одразу після дієслова.",
                    'English very well' => "✅ Правильний порядок: 'English' (об'єкт) перед 'very well' (прислівник способу). Це стандартна структура речення.",
                ],
                'level' => 'A1',
                'detail' => 'object_adverb_order',
                'structure' => 'object_adverb',
            ],
            [
                'question' => "Please, can you put {a1}?",
                'options' => ['in the corner that box', 'that box in the corner'],
                'answers' => ['a1' => 'that box in the corner'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Порядок: об'єкт (що?) → місце (куди?). Спочатку називаємо предмет, який переміщуємо, потім вказуємо місце.",
                ],
                'explanations' => [
                    'in the corner that box' => "❌ Неправильний порядок. Обставина місця 'in the corner' не може стояти перед об'єктом 'that box'. Спочатку називаємо що, потім куди.",
                    'that box in the corner' => "✅ Правильний порядок: 'that box' (об'єкт) перед 'in the corner' (місце). Це природний порядок слів.",
                ],
                'level' => 'A1',
                'detail' => 'place_time_order',
                'structure' => 'place_time',
            ],
            [
                'question' => "After the accident, Jim was {a1}.",
                'options' => ['for a week in hospital', 'in hospital for a week'],
                'answers' => ['a1' => 'in hospital for a week'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Порядок: місце (де?) → тривалість (як довго?). Спочатку вказуємо де знаходився, потім як довго.",
                ],
                'explanations' => [
                    'for a week in hospital' => "❌ Неправильний порядок. Тривалість 'for a week' не ставиться перед місцем 'in hospital'. Спочатку вказуємо де, потім як довго.",
                    'in hospital for a week' => "✅ Правильний порядок: 'in hospital' (місце) перед 'for a week' (тривалість). Це стандартна структура.",
                ],
                'level' => 'A1',
                'detail' => 'place_time_order',
                'structure' => 'place_time',
            ],
            [
                'question' => "I like {a1}.",
                'options' => ['this music a lot', 'a lot this music'],
                'answers' => ['a1' => 'this music a lot'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Порядок: об'єкт (що?) → прислівник міри (скільки?). Після дієслова 'like' спочатку йде об'єкт, потім прислівник міри.",
                ],
                'explanations' => [
                    'this music a lot' => "✅ Правильний порядок: 'this music' (об'єкт) перед 'a lot' (прислівник міри). Об'єкт завжди ставиться одразу після дієслова.",
                    'a lot this music' => "❌ Неправильний порядок. Прислівник міри 'a lot' не може стояти перед об'єктом. В англійській мові об'єкт іде першим після дієслова.",
                ],
                'level' => 'A1',
                'detail' => 'object_adverb_order',
                'structure' => 'object_adverb',
            ],
            [
                'question' => "They met {a1}.",
                'options' => ["at a friend's house after a party", "after a party at a friend's house"],
                'answers' => ['a1' => "at a friend's house after a party"],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Порядок: конкретніше місце → ширший контекст часу/події. Спочатку вказуємо точне місце зустрічі, потім обставини.",
                ],
                'explanations' => [
                    "at a friend's house after a party" => "✅ Правильний порядок: конкретне місце 'at a friend's house' перед обставиною події 'after a party'. Це логічна послідовність.",
                    "after a party at a friend's house" => "❌ Неправильний порядок. Починати з обставини часу/події 'after a party' перед місцем не є природним у цьому контексті.",
                ],
                'level' => 'A1',
                'detail' => 'place_time_order',
                'structure' => 'place_time',
            ],
            [
                'question' => "Tigers approach {a1}.",
                'options' => ['their victims very slowly', 'very slowly their victims'],
                'answers' => ['a1' => 'their victims very slowly'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Порядок: об'єкт (кого?) → прислівник способу (як?). Після дієслова спочатку вказуємо об'єкт дії, потім як вона виконується.",
                ],
                'explanations' => [
                    'their victims very slowly' => "✅ Правильний порядок: 'their victims' (об'єкт) перед 'very slowly' (прислівник способу). Об'єкт ставиться одразу після дієслова.",
                    'very slowly their victims' => "❌ Неправильний порядок. Прислівник способу 'very slowly' не може стояти перед об'єктом 'their victims'.",
                ],
                'level' => 'A1',
                'detail' => 'object_adverb_order',
                'structure' => 'object_adverb',
            ],
            [
                'question' => "I saw {a1}.",
                'options' => ['Hellen at the library', 'at the library Hellen'],
                'answers' => ['a1' => 'Hellen at the library'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Порядок: об'єкт (кого?) → місце (де?). Спочатку називаємо кого бачили, потім де.",
                ],
                'explanations' => [
                    'Hellen at the library' => "✅ Правильний порядок: 'Hellen' (об'єкт) перед 'at the library' (місце). Це природний порядок слів.",
                    'at the library Hellen' => "❌ Неправильний порядок. Обставина місця 'at the library' не може стояти перед об'єктом 'Hellen'.",
                ],
                'level' => 'A1',
                'detail' => 'place_time_order',
                'structure' => 'place_time',
            ],
            [
                'question' => "He finished {a1}.",
                'options' => ['very quickly the exam', 'the exam very quickly'],
                'answers' => ['a1' => 'the exam very quickly'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Порядок: об'єкт (що?) → прислівник способу (як?). Спочатку називаємо що закінчив, потім як швидко.",
                ],
                'explanations' => [
                    'very quickly the exam' => "❌ Неправильний порядок. Прислівник способу 'very quickly' не ставиться перед об'єктом 'the exam'.",
                    'the exam very quickly' => "✅ Правильний порядок: 'the exam' (об'єкт) перед 'very quickly' (прислівник способу).",
                ],
                'level' => 'A1',
                'detail' => 'object_adverb_order',
                'structure' => 'object_adverb',
            ],

            // Exercise 2 - Choose the correct options
            [
                'question' => "I walk {a1}.",
                'options' => ['my dog in the park every day', 'my dog every day in the park', 'every day my dog in the park'],
                'answers' => ['a1' => 'my dog in the park every day'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Порядок: об'єкт → місце → час/частота. Після дієслова спочатку йде об'єкт, потім місце, і наприкінці частота.",
                ],
                'explanations' => [
                    'my dog in the park every day' => "✅ Правильний порядок: об'єкт 'my dog' → місце 'in the park' → частота 'every day'. Це стандартна структура.",
                    'my dog every day in the park' => "❌ Неправильний порядок. Частота 'every day' не повинна стояти перед місцем 'in the park'.",
                    'every day my dog in the park' => "❌ Неправильний порядок. Частота на початку відокремлює об'єкт від дієслова.",
                ],
                'level' => 'A1',
                'detail' => 'place_time_order',
                'structure' => 'place_time',
            ],
            [
                'question' => "Lisa took {a1}.",
                'options' => ['her son to the doctor this morning', 'to the doctor her son this morning', 'this morning her son to the doctor'],
                'answers' => ['a1' => 'her son to the doctor this morning'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Порядок: об'єкт → напрямок → час. Спочатку кого відвели, потім куди, і наприкінці коли.",
                ],
                'explanations' => [
                    'her son to the doctor this morning' => "✅ Правильний порядок: об'єкт 'her son' → напрямок 'to the doctor' → час 'this morning'.",
                    'to the doctor her son this morning' => "❌ Неправильний порядок. Напрямок 'to the doctor' не може стояти перед об'єктом 'her son'.",
                    'this morning her son to the doctor' => "❌ Неправильний порядок. Час 'this morning' на початку порушує структуру речення.",
                ],
                'level' => 'A1',
                'detail' => 'place_time_order',
                'structure' => 'place_time',
            ],
            [
                'question' => "Can you drive {a1}?",
                'options' => ['home me after the concert', 'me after the concert home', 'me home after the concert'],
                'answers' => ['a1' => 'me home after the concert'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Порядок: об'єкт → напрямок → час. Спочатку кого везти, потім куди, і наприкінці коли.",
                ],
                'explanations' => [
                    'home me after the concert' => "❌ Неправильний порядок. Напрямок 'home' не може стояти перед об'єктом 'me'.",
                    'me after the concert home' => "❌ Неправильний порядок. Час 'after the concert' не повинен стояти між об'єктом і напрямком.",
                    'me home after the concert' => "✅ Правильний порядок: об'єкт 'me' → напрямок 'home' → час 'after the concert'.",
                ],
                'level' => 'A1',
                'detail' => 'place_time_order',
                'structure' => 'place_time',
            ],
            [
                'question' => "We {a1}.",
                'options' => ['enjoyed very much the trip', 'enjoyed the trip very much', 'very much enjoyed the trip'],
                'answers' => ['a1' => 'enjoyed the trip very much'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Порядок: дієслово → об'єкт → прислівник міри. Прислівник міри ставиться наприкінці, після об'єкта.",
                ],
                'explanations' => [
                    'enjoyed very much the trip' => "❌ Неправильний порядок. Прислівник міри 'very much' не може стояти між дієсловом і об'єктом.",
                    'enjoyed the trip very much' => "✅ Правильний порядок: дієслово 'enjoyed' → об'єкт 'the trip' → прислівник міри 'very much'.",
                    'very much enjoyed the trip' => "❌ Неправильний порядок. Прислівник міри на початку порушує структуру речення.",
                ],
                'level' => 'A1',
                'detail' => 'object_adverb_order',
                'structure' => 'object_adverb',
            ],
            [
                'question' => "I {a1}.",
                'options' => ['bought in Paris this jacket last year', 'bought this jacket last year in Paris', 'bought this jacket in Paris last year'],
                'answers' => ['a1' => 'bought this jacket in Paris last year'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Порядок: дієслово → об'єкт → місце → час. Місце ставиться перед часом наприкінці речення.",
                ],
                'explanations' => [
                    'bought in Paris this jacket last year' => "❌ Неправильний порядок. Місце 'in Paris' не може стояти перед об'єктом 'this jacket'.",
                    'bought this jacket last year in Paris' => "❌ Неправильний порядок. Час 'last year' не повинен стояти перед місцем 'in Paris'.",
                    'bought this jacket in Paris last year' => "✅ Правильний порядок: дієслово → об'єкт 'this jacket' → місце 'in Paris' → час 'last year'.",
                ],
                'level' => 'A1',
                'detail' => 'place_time_order',
                'structure' => 'place_time',
            ],
            [
                'question' => "I {a1}.",
                'options' => ['will call my parents immediately', 'will call immediately my parents', 'immediately will call my parents'],
                'answers' => ['a1' => 'will call my parents immediately'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Порядок: дієслово → об'єкт → прислівник часу. Короткий прислівник часу ставиться наприкінці речення.",
                ],
                'explanations' => [
                    'will call my parents immediately' => "✅ Правильний порядок: дієслово → об'єкт 'my parents' → прислівник часу 'immediately'.",
                    'will call immediately my parents' => "❌ Неправильний порядок. Прислівник 'immediately' не може стояти між дієсловом і об'єктом.",
                    'immediately will call my parents' => "❌ Неправильний порядок. Прислівник на початку порушує структуру речення.",
                ],
                'level' => 'A1',
                'detail' => 'object_adverb_order',
                'structure' => 'object_adverb',
            ],
            [
                'question' => "David {a1}.",
                'options' => ['very much loves his children', 'loves his children very much', 'loves very much his children'],
                'answers' => ['a1' => 'loves his children very much'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Порядок: дієслово → об'єкт → прислівник міри. Прислівник міри ставиться після об'єкта.",
                ],
                'explanations' => [
                    'very much loves his children' => "❌ Неправильний порядок. Прислівник міри на початку порушує структуру.",
                    'loves his children very much' => "✅ Правильний порядок: дієслово 'loves' → об'єкт 'his children' → прислівник міри 'very much'.",
                    'loves very much his children' => "❌ Неправильний порядок. Прислівник міри не може стояти між дієсловом і об'єктом.",
                ],
                'level' => 'A1',
                'detail' => 'object_adverb_order',
                'structure' => 'object_adverb',
            ],
            [
                'question' => "I learnt {a1}.",
                'options' => ['Italian in Rome during a student exchange program', 'Italian during a student exchange program in Rome', 'in Rome Italian during a student exchange program'],
                'answers' => ['a1' => 'Italian in Rome during a student exchange program'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Порядок: об'єкт → місце → час/обставина. Спочатку що вивчив, потім де, і наприкінці за яких обставин.",
                ],
                'explanations' => [
                    'Italian in Rome during a student exchange program' => "✅ Правильний порядок: об'єкт 'Italian' → місце 'in Rome' → обставина 'during a student exchange program'.",
                    'Italian during a student exchange program in Rome' => "❌ Неправильний порядок. Час/обставина не повинна стояти перед місцем.",
                    'in Rome Italian during a student exchange program' => "❌ Неправильний порядок. Місце не може стояти перед об'єктом.",
                ],
                'level' => 'A1',
                'detail' => 'place_time_order',
                'structure' => 'place_time',
            ],
            [
                'question' => "We {a1}.",
                'options' => ['always finish school at 3.30 pm', 'finish always school at 3.30 pm', 'finish school at 3.30 pm always'],
                'answers' => ['a1' => 'always finish school at 3.30 pm'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Прислівник частоти (always, usually, often) ставиться перед основним дієсловом. Виняток: після дієслова 'be'.",
                ],
                'explanations' => [
                    'always finish school at 3.30 pm' => "✅ Правильний порядок: прислівник частоти 'always' стоїть перед основним дієсловом 'finish'.",
                    'finish always school at 3.30 pm' => "❌ Неправильний порядок. Прислівник частоти не ставиться між дієсловом і об'єктом.",
                    'finish school at 3.30 pm always' => "❌ Неправильний порядок. Прислівник частоти рідко ставиться наприкінці речення.",
                ],
                'level' => 'A1',
                'detail' => 'frequency_adverb_order',
                'structure' => 'frequency_verb',
            ],
            [
                'question' => "I {a1}.",
                'options' => ["don't play very often tennis", "don't like tennis very much", "don't like very much tennis"],
                'answers' => ['a1' => "don't like tennis very much"],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Порядок: дієслово → об'єкт → прислівник міри. Прислівник міри 'very much' ставиться наприкінці речення.",
                ],
                'explanations' => [
                    "don't play very often tennis" => "❌ Неправильний порядок слів. Прислівник частоти 'very often' не може стояти між дієсловом і об'єктом 'tennis'. Правильний порядок: дієслово → об'єкт → прислівник.",
                    "don't like tennis very much" => "✅ Правильний порядок: дієслово 'like' → об'єкт 'tennis' → прислівник міри 'very much'.",
                    "don't like very much tennis" => "❌ Неправильний порядок. Прислівник міри не може стояти між дієсловом і об'єктом.",
                ],
                'level' => 'A1',
                'detail' => 'object_adverb_order',
                'structure' => 'object_adverb',
            ],

            // Exercise 3 - Choose the correct sentence (word order)
            [
                'question' => "We {a1}.  (a nice lunch / had / today / we / at a restaurant)",
                'options' => ['had a nice lunch at a restaurant today', 'had a nice lunch today at a restaurant', 'had at a restaurant a nice lunch today'],
                'answers' => ['a1' => 'had a nice lunch at a restaurant today'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Порядок: дієслово → об'єкт → місце → час. Це стандартна структура англійського речення.",
                ],
                'explanations' => [
                    'had a nice lunch at a restaurant today' => "✅ Правильний порядок: дієслово 'had' → об'єкт 'a nice lunch' → місце 'at a restaurant' → час 'today'.",
                    'had a nice lunch today at a restaurant' => "❌ Неправильний порядок. Час 'today' не повинен стояти перед місцем 'at a restaurant'.",
                    'had at a restaurant a nice lunch today' => "❌ Неправильний порядок. Місце не може стояти між дієсловом і об'єктом.",
                ],
                'level' => 'A1',
                'detail' => 'place_time_order',
                'structure' => 'place_time',
            ],
            [
                'question' => "He {a1}.  (the keys / last night / in the car / forgot / He)",
                'options' => ['forgot the keys in the car last night', 'forgot in the car the keys last night', 'forgot the keys last night in the car'],
                'answers' => ['a1' => 'forgot the keys in the car last night'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Порядок: дієслово → об'єкт → місце → час. Спочатку що забув, потім де, і наприкінці коли.",
                ],
                'explanations' => [
                    'forgot the keys in the car last night' => "✅ Правильний порядок: дієслово → об'єкт 'the keys' → місце 'in the car' → час 'last night'.",
                    'forgot in the car the keys last night' => "❌ Неправильний порядок. Місце 'in the car' не може стояти перед об'єктом 'the keys'.",
                    'forgot the keys last night in the car' => "❌ Неправильний порядок. Час 'last night' не повинен стояти перед місцем 'in the car'.",
                ],
                'level' => 'A1',
                'detail' => 'place_time_order',
                'structure' => 'place_time',
            ],
            [
                'question' => "Susan {a1}.  (often / Susan / takes / to work / a taxi)",
                'options' => ['often takes a taxi to work', 'takes often a taxi to work', 'takes a taxi often to work'],
                'answers' => ['a1' => 'often takes a taxi to work'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Прислівник частоти (often) ставиться перед основним дієсловом, за винятком дієслова 'be'.",
                ],
                'explanations' => [
                    'often takes a taxi to work' => "✅ Правильний порядок: прислівник частоти 'often' перед дієсловом 'takes', потім об'єкт і напрямок.",
                    'takes often a taxi to work' => "❌ Неправильний порядок. Прислівник частоти не ставиться після дієслова перед об'єктом.",
                    'takes a taxi often to work' => "❌ Неправильний порядок. Прислівник частоти не ставиться між об'єктом і напрямком.",
                ],
                'level' => 'A1',
                'detail' => 'frequency_adverb_order',
                'structure' => 'frequency_verb',
            ],
            [
                'question' => "I {a1}.  (have to return / I / the book / to the library / today)",
                'options' => ['have to return the book to the library today', 'have to return today the book to the library', 'have to return to the library the book today'],
                'answers' => ['a1' => 'have to return the book to the library today'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Порядок: модальна конструкція → об'єкт → напрямок → час. Після дієслова спочатку йде об'єкт.",
                ],
                'explanations' => [
                    'have to return the book to the library today' => "✅ Правильний порядок: дієслово → об'єкт 'the book' → напрямок 'to the library' → час 'today'.",
                    'have to return today the book to the library' => "❌ Неправильний порядок. Час 'today' не може стояти між дієсловом і об'єктом.",
                    'have to return to the library the book today' => "❌ Неправильний порядок. Напрямок не може стояти перед об'єктом.",
                ],
                'level' => 'A1',
                'detail' => 'place_time_order',
                'structure' => 'place_time',
            ],
            [
                'question' => "They {a1}.  (are / never / at lunchtime / They / at home)",
                'options' => ['are never at home at lunchtime', 'are never at lunchtime at home', 'never are at home at lunchtime'],
                'answers' => ['a1' => 'are never at home at lunchtime'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Прислівник частоти (never) ставиться після дієслова 'be'. Далі місце → час.",
                ],
                'explanations' => [
                    'are never at home at lunchtime' => "✅ Правильний порядок: дієслово 'are' → прислівник 'never' → місце 'at home' → час 'at lunchtime'.",
                    'are never at lunchtime at home' => "❌ Неправильний порядок. Час 'at lunchtime' не повинен стояти перед місцем 'at home'.",
                    'never are at home at lunchtime' => "❌ Неправильний порядок. Прислівник частоти ставиться після дієслова 'be', а не перед ним.",
                ],
                'level' => 'A1',
                'detail' => 'frequency_adverb_order',
                'structure' => 'frequency_verb',
            ],
            [
                'question' => "It {a1}.  (It / always / very hot / here / in July / is)",
                'options' => ['is always very hot here in July', 'is very hot always here in July', 'is always very hot in July here'],
                'answers' => ['a1' => 'is always very hot here in July'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Прислівник частоти ставиться після 'be'. Порядок: be → always → опис → місце → час.",
                ],
                'explanations' => [
                    'is always very hot here in July' => "✅ Правильний порядок: 'is' → 'always' → 'very hot' → 'here' → 'in July'.",
                    'is very hot always here in July' => "❌ Неправильний порядок. Прислівник 'always' повинен стояти одразу після 'is'.",
                    'is always very hot in July here' => "❌ Неправильний порядок. Місце 'here' повинно стояти перед часом 'in July'.",
                ],
                'level' => 'A1',
                'detail' => 'frequency_adverb_order',
                'structure' => 'frequency_verb',
            ],
            [
                'question' => "We {a1}.  (play / football / after school / in the park / We)",
                'options' => ['play football in the park after school', 'play football after school in the park', 'play in the park football after school'],
                'answers' => ['a1' => 'play football in the park after school'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Порядок: дієслово → об'єкт → місце → час. Спочатку що граємо, потім де, і наприкінці коли.",
                ],
                'explanations' => [
                    'play football in the park after school' => "✅ Правильний порядок: дієслово → об'єкт 'football' → місце 'in the park' → час 'after school'.",
                    'play football after school in the park' => "❌ Неправильний порядок. Час 'after school' не повинен стояти перед місцем 'in the park'.",
                    'play in the park football after school' => "❌ Неправильний порядок. Місце не може стояти між дієсловом і об'єктом.",
                ],
                'level' => 'A1',
                'detail' => 'place_time_order',
                'structure' => 'place_time',
            ],
            [
                'question' => "She {a1}.  (usually / She / cereal / has / for breakfast)",
                'options' => ['usually has cereal for breakfast', 'has usually cereal for breakfast', 'has cereal usually for breakfast'],
                'answers' => ['a1' => 'usually has cereal for breakfast'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Прислівник частоти (usually) ставиться перед основним дієсловом 'has'.",
                ],
                'explanations' => [
                    'usually has cereal for breakfast' => "✅ Правильний порядок: прислівник 'usually' перед дієсловом 'has', потім об'єкт і обставина.",
                    'has usually cereal for breakfast' => "❌ Неправильний порядок. Прислівник частоти не ставиться після основного дієслова.",
                    'has cereal usually for breakfast' => "❌ Неправильний порядок. Прислівник частоти не ставиться між об'єктом і обставиною.",
                ],
                'level' => 'A1',
                'detail' => 'frequency_adverb_order',
                'structure' => 'frequency_verb',
            ],
            [
                'question' => "Linda {a1}.  (met / at a nightclub / in 2006 / Linda / her husband)",
                'options' => ['met her husband at a nightclub in 2006', 'met her husband in 2006 at a nightclub', 'met at a nightclub her husband in 2006'],
                'answers' => ['a1' => 'met her husband at a nightclub in 2006'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Порядок: дієслово → об'єкт → місце → час. Спочатку кого зустріла, потім де, і наприкінці коли.",
                ],
                'explanations' => [
                    'met her husband at a nightclub in 2006' => "✅ Правильний порядок: дієслово → об'єкт 'her husband' → місце 'at a nightclub' → час 'in 2006'.",
                    'met her husband in 2006 at a nightclub' => "❌ Неправильний порядок. Час 'in 2006' не повинен стояти перед місцем 'at a nightclub'.",
                    'met at a nightclub her husband in 2006' => "❌ Неправильний порядок. Місце не може стояти між дієсловом і об'єктом.",
                ],
                'level' => 'A1',
                'detail' => 'place_time_order',
                'structure' => 'place_time',
            ],
            [
                'question' => "I {a1}.  (always / I / from home / work / on Tuesdays)",
                'options' => ['always work from home on Tuesdays', 'work from home always on Tuesdays', 'work always from home on Tuesdays'],
                'answers' => ['a1' => 'always work from home on Tuesdays'],
                'verb_hint' => ['a1' => null],
                'hints' => [
                    'a1' => "Прислівник частоти (always) ставиться перед основним дієсловом 'work'.",
                ],
                'explanations' => [
                    'always work from home on Tuesdays' => "✅ Правильний порядок: прислівник 'always' перед дієсловом 'work', потім місце і час.",
                    'work from home always on Tuesdays' => "❌ Неправильний порядок. Прислівник частоти не ставиться між місцем і часом.",
                    'work always from home on Tuesdays' => "❌ Неправильний порядок. Прислівник частоти не ставиться після дієслова.",
                ],
                'level' => 'A1',
                'detail' => 'frequency_adverb_order',
                'structure' => 'frequency_verb',
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

            $tagIds = [
                $themeTagId,
                $detailTags[$question['detail']],
                $structureTags[$question['structure']],
            ];

            $items[] = [
                'uuid' => $uuid,
                'question' => $question['question'],
                'category_id' => $categoryId,
                'difficulty' => $levelDifficulty[$question['level']] ?? 1,
                'source_id' => $sourceId,
                'flag' => 0,
                'level' => $question['level'],
                'tag_ids' => array_values(array_unique($tagIds)),
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
}
