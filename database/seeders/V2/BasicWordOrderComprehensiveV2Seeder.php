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
            // Exercise 1 - Fill in the gaps with the correct options
            [
                'question' => "I didn't like {a1} last night.",
                'options' => ['very much the food', 'the food very much'],
                'correct' => 'the food very much',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'exercise' => 1,
            ],
            [
                'question' => 'We arrived {a1}.',
                'options' => ['home very late', 'very late home'],
                'correct' => 'home very late',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'exercise' => 1,
            ],
            [
                'question' => 'Teresa speaks {a1}.',
                'options' => ['very well English', 'English very well'],
                'correct' => 'English very well',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'exercise' => 1,
            ],
            [
                'question' => 'Please, can you put {a1}?',
                'options' => ['in the corner that box', 'that box in the corner'],
                'correct' => 'that box in the corner',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'exercise' => 1,
            ],
            [
                'question' => 'After the accident, Jim was {a1}.',
                'options' => ['for a week in hospital', 'in hospital for a week'],
                'correct' => 'in hospital for a week',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'exercise' => 1,
            ],
            [
                'question' => 'I like {a1}.',
                'options' => ['this music a lot', 'a lot this music'],
                'correct' => 'this music a lot',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'exercise' => 1,
            ],
            [
                'question' => 'They met {a1}.',
                'options' => ["at a friend's house after a party", "after a party at a friend's house"],
                'correct' => "at a friend's house after a party",
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'exercise' => 1,
            ],
            [
                'question' => 'Tigers approach {a1}.',
                'options' => ['their victims very slowly', 'very slowly their victims'],
                'correct' => 'their victims very slowly',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'exercise' => 1,
            ],
            [
                'question' => 'I saw {a1}.',
                'options' => ['Hellen at the library', 'at the library Hellen'],
                'correct' => 'Hellen at the library',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'exercise' => 1,
            ],
            [
                'question' => 'He finished {a1}.',
                'options' => ['very quickly the exam', 'the exam very quickly'],
                'correct' => 'the exam very quickly',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'exercise' => 1,
            ],

            // Exercise 2 - Choose the correct options to complete the sentences
            [
                'question' => 'I walk {a1}.',
                'options' => ['my dog in the park every day', 'my dog every day in the park', 'every day my dog in the park'],
                'correct' => 'my dog in the park every day',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'exercise' => 2,
            ],
            [
                'question' => 'Lisa took {a1}.',
                'options' => ['her son to the doctor this morning', 'to the doctor her son this morning', 'this morning her son to the doctor'],
                'correct' => 'her son to the doctor this morning',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'exercise' => 2,
            ],
            [
                'question' => 'Can you drive {a1}?',
                'options' => ['home me after the concert', 'me after the concert home', 'me home after the concert'],
                'correct' => 'me home after the concert',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'exercise' => 2,
            ],
            [
                'question' => 'We {a1}.',
                'options' => ['enjoyed very much the trip', 'enjoyed the trip very much', 'very much enjoyed the trip'],
                'correct' => 'enjoyed the trip very much',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'exercise' => 2,
            ],
            [
                'question' => 'I {a1}.',
                'options' => ['bought in Paris this jacket last year', 'bought this jacket last year in Paris', 'bought this jacket in Paris last year'],
                'correct' => 'bought this jacket in Paris last year',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'exercise' => 2,
            ],
            [
                'question' => 'I {a1}.',
                'options' => ['will call my parents immediately', 'will call immediately my parents', 'immediately will call my parents'],
                'correct' => 'will call my parents immediately',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'exercise' => 2,
            ],
            [
                'question' => 'David {a1}.',
                'options' => ['very much loves his children', 'loves his children very much', 'loves very much his children'],
                'correct' => 'loves his children very much',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'exercise' => 2,
            ],
            [
                'question' => 'I learnt {a1}.',
                'options' => ['Italian in Rome during a student exchange program', 'Italian during a student exchange program in Rome', 'in Rome Italian during a student exchange program'],
                'correct' => 'Italian in Rome during a student exchange program',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'exercise' => 2,
            ],
            [
                'question' => 'We {a1}.',
                'options' => ['always finish school at 3.30 pm', 'finish always school at 3.30 pm', 'finish school at 3.30 pm always'],
                'correct' => 'always finish school at 3.30 pm',
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'exercise' => 2,
            ],
            [
                'question' => 'I {a1}.',
                'options' => ["don't play very often tennis", "don't like tennis very much", "don't like very much tennis"],
                'correct' => "don't like tennis very much",
                'level' => 'A1',
                'detail' => 'word_order',
                'structure' => 'fill_gap',
                'exercise' => 2,
            ],

            // Exercise 3 - Choose the correct sentence (word order)
            [
                'question' => 'We {a1}.  (a nice lunch / had / today / we / at a restaurant)',
                'options' => ['had a nice lunch at a restaurant today', 'had a nice lunch today at a restaurant', 'had at a restaurant a nice lunch today'],
                'correct' => 'had a nice lunch at a restaurant today',
                'level' => 'A1',
                'detail' => 'sentence_structure',
                'structure' => 'choose_order',
                'exercise' => 3,
            ],
            [
                'question' => 'He {a1}.  (the keys / last night / in the car / forgot / He)',
                'options' => ['forgot the keys in the car last night', 'forgot in the car the keys last night', 'forgot the keys last night in the car'],
                'correct' => 'forgot the keys in the car last night',
                'level' => 'A1',
                'detail' => 'sentence_structure',
                'structure' => 'choose_order',
                'exercise' => 3,
            ],
            [
                'question' => 'Susan {a1}.  (often / Susan / takes / to work / a taxi)',
                'options' => ['often takes a taxi to work', 'takes often a taxi to work', 'takes a taxi often to work'],
                'correct' => 'often takes a taxi to work',
                'level' => 'A1',
                'detail' => 'sentence_structure',
                'structure' => 'choose_order',
                'exercise' => 3,
            ],
            [
                'question' => 'I {a1}.  (have to return / I / the book / to the library / today)',
                'options' => ['have to return the book to the library today', 'have to return today the book to the library', 'have to return to the library the book today'],
                'correct' => 'have to return the book to the library today',
                'level' => 'A1',
                'detail' => 'sentence_structure',
                'structure' => 'choose_order',
                'exercise' => 3,
            ],
            [
                'question' => 'They {a1}.  (are / never / at lunchtime / They / at home)',
                'options' => ['are never at home at lunchtime', 'are never at lunchtime at home', 'never are at home at lunchtime'],
                'correct' => 'are never at home at lunchtime',
                'level' => 'A1',
                'detail' => 'sentence_structure',
                'structure' => 'choose_order',
                'exercise' => 3,
            ],
            [
                'question' => 'It {a1}.  (It / always / very hot / here / in July / is)',
                'options' => ['is always very hot here in July', 'is very hot always here in July', 'is always very hot in July here'],
                'correct' => 'is always very hot here in July',
                'level' => 'A1',
                'detail' => 'sentence_structure',
                'structure' => 'choose_order',
                'exercise' => 3,
            ],
            [
                'question' => 'We {a1}.  (play / football / after school / in the park / We)',
                'options' => ['play football in the park after school', 'play football after school in the park', 'play in the park football after school'],
                'correct' => 'play football in the park after school',
                'level' => 'A1',
                'detail' => 'sentence_structure',
                'structure' => 'choose_order',
                'exercise' => 3,
            ],
            [
                'question' => 'She {a1}.  (usually / She / cereal / has / for breakfast)',
                'options' => ['usually has cereal for breakfast', 'has usually cereal for breakfast', 'has cereal usually for breakfast'],
                'correct' => 'usually has cereal for breakfast',
                'level' => 'A1',
                'detail' => 'sentence_structure',
                'structure' => 'choose_order',
                'exercise' => 3,
            ],
            [
                'question' => 'Linda {a1}.  (met / at a nightclub / in 2006 / Linda / her husband)',
                'options' => ['met her husband at a nightclub in 2006', 'met her husband in 2006 at a nightclub', 'met at a nightclub her husband in 2006'],
                'correct' => 'met her husband at a nightclub in 2006',
                'level' => 'A1',
                'detail' => 'sentence_structure',
                'structure' => 'choose_order',
                'exercise' => 3,
            ],
            [
                'question' => 'I {a1}.  (always / I / from home / work / on Tuesdays)',
                'options' => ['always work from home on Tuesdays', 'work from home always on Tuesdays', 'work always from home on Tuesdays'],
                'correct' => 'always work from home on Tuesdays',
                'level' => 'A1',
                'detail' => 'sentence_structure',
                'structure' => 'choose_order',
                'exercise' => 3,
            ],
        ];

        $items = [];
        $meta = [];

        foreach ($questions as $index => $question) {
            $uuid = $this->generateQuestionUuid($question['level'], $index, $question['question']);

            $answer = $question['correct'];
            $options = $question['options'];

            // Build explanations for each option
            $explanations = $this->buildExplanations($question, $answer, $options);

            // Build hint
            $hint = $this->buildHint($question, $answer);

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
                'hints' => ['a1' => $hint],
                'explanations' => $explanations,
            ];
        }

        $this->seedQuestionData($items, $meta);
    }

    private function buildHint(array $question, string $answer): string
    {
        $exercise = $question['exercise'] ?? 1;

        $baseHints = [
            // Exercise 1 hints
            "I didn't like {a1} last night." => 'Порядок слів: спочатку називаємо додаток (the food), потім прислівник міри (very much). Прислівник міри зазвичай стоїть після прямого додатка.',
            'We arrived {a1}.' => 'Порядок слів: місце (home) після дієслова, потім обставина способу/часу (very late). Місце ближче до дієслова, ніж обставина часу.',
            'Teresa speaks {a1}.' => "Порядок слів: прямий додаток (English) перед прислівником способу (very well). Прислівник способу ставимо після об'єкта.",
            'Please, can you put {a1}?' => 'Порядок слів: спочатку додаток (that box), потім обставина місця (in the corner). Предмет називаємо першим.',
            'After the accident, Jim was {a1}.' => 'Порядок слів: обставина місця (in hospital) перед обставиною часу/тривалості (for a week). Місце зазвичай передує часу.',
            'I like {a1}.' => "Порядок слів: додаток (this music) перед прислівником міри (a lot). Об'єкт ставимо відразу після дієслова.",
            'They met {a1}.' => "Порядок слів: конкретніше місце (at a friend's house) перед ширшим контекстом (after a party). Від конкретного до загального.",
            'Tigers approach {a1}.' => "Порядок слів: додаток (their victims) перед прислівником способу (very slowly). Об'єкт ближче до дієслова.",
            'I saw {a1}.' => "Порядок слів: спочатку хто/що (Hellen), потім де (at the library). Об'єкт передує обставині місця.",
            'He finished {a1}.' => "Порядок слів: додаток (the exam) перед прислівником способу (very quickly). Прислівник способу після об'єкта.",

            // Exercise 2 hints
            'I walk {a1}.' => "Порядок слів: об'єкт (my dog) → місце (in the park) → частота/час (every day). Стандартний порядок обставин.",
            'Lisa took {a1}.' => "Порядок слів: об'єкт (her son) → напрямок (to the doctor) → час (this morning). Об'єкт завжди після дієслова.",
            'Can you drive {a1}?' => "Порядок слів: об'єкт (me) → місце призначення (home) → час (after the concert). Кого везти → куди → коли.",
            'We {a1}.' => "Порядок слів: дієслово (enjoyed) → додаток (the trip) → прислівник міри (very much). Прислівник міри після об'єкта.",
            'I {a1}.' => $this->getHintForQuestion($question['question'], $exercise),
            'David {a1}.' => 'Порядок слів: дієслово (loves) → додаток (his children) → прислівник міри (very much). Прислівник міри наприкінці.',
            'I learnt {a1}.' => "Порядок слів: об'єкт (Italian) → місце (in Rome) → час/подія (during a student exchange program). Від головного до деталей.",
            'We {a1}.' => $this->getHintForQuestion($question['question'], $exercise),

            // Exercise 3 hints (with word lists)
            'We {a1}.  (a nice lunch / had / today / we / at a restaurant)' => "Порядок слів: дієслово (had) → об'єкт (a nice lunch) → місце (at a restaurant) → час (today). Стандартна структура речення.",
            'He {a1}.  (the keys / last night / in the car / forgot / He)' => "Порядок слів: дієслово (forgot) → об'єкт (the keys) → місце (in the car) → час (last night). Предмет перед місцем.",
            'Susan {a1}.  (often / Susan / takes / to work / a taxi)' => "Порядок слів: прислівник частоти (often) перед дієсловом (takes), потім об'єкт (a taxi) → напрямок (to work).",
            'I {a1}.  (have to return / I / the book / to the library / today)' => "Порядок слів: модальна фраза (have to return) → об'єкт (the book) → місце (to the library) → час (today).",
            'They {a1}.  (are / never / at lunchtime / They / at home)' => 'Порядок слів: прислівник частоти (never) після дієслова be (are), місце (at home) перед часом (at lunchtime).',
            'It {a1}.  (It / always / very hot / here / in July / is)' => 'Порядок слів: дієслово (is) → прислівник частоти (always) → прикметник (very hot) → місце (here) → час (in July).',
            'We {a1}.  (play / football / after school / in the park / We)' => "Порядок слів: дієслово (play) → об'єкт (football) → місце (in the park) → час (after school).",
            'She {a1}.  (usually / She / cereal / has / for breakfast)' => "Порядок слів: прислівник частоти (usually) перед дієсловом (has), потім об'єкт (cereal) → обставина (for breakfast).",
            'Linda {a1}.  (met / at a nightclub / in 2006 / Linda / her husband)' => "Порядок слів: дієслово (met) → об'єкт (her husband) → місце (at a nightclub) → час (in 2006).",
            'I {a1}.  (always / I / from home / work / on Tuesdays)' => 'Порядок слів: прислівник частоти (always) перед дієсловом (work), потім місце (from home) → час (on Tuesdays).',
        ];

        return $baseHints[$question['question']] ?? "Зверніть увагу на правильний порядок слів у реченні. В англійській мові важливо дотримуватися послідовності: підмет → дієслово → об'єкт → місце → час.";
    }

    private function getHintForQuestion(string $question, int $exercise): string
    {
        // Handle duplicate question texts across exercises
        if ($exercise === 2) {
            if (strpos($question, 'bought') !== false || strpos($question, 'Paris') !== false || strpos($question, 'jacket') !== false) {
                return "Порядок слів: дієслово (bought) → об'єкт (this jacket) → місце (in Paris) → час (last year). Місце перед часом.";
            }
            if (strpos($question, 'will call') !== false || strpos($question, 'parents') !== false) {
                return "Порядок слів: дієслово (will call) → об'єкт (my parents) → прислівник часу (immediately). Прислівник наприкінці.";
            }
            if (strpos($question, 'always finish') !== false || strpos($question, 'school') !== false) {
                return "Порядок слів: прислівник частоти (always) перед дієсловом (finish), потім об'єкт (school) → час (at 3.30 pm).";
            }
            if (strpos($question, "don't") !== false || strpos($question, 'tennis') !== false) {
                return "Порядок слів: дієслово (like) → об'єкт (tennis) → прислівник міри (very much). Об'єкт перед прислівником.";
            }
        }

        return "Зверніть увагу на стандартний порядок слів в англійській мові: підмет → дієслово → об'єкт → місце → час.";
    }

    private function buildExplanations(array $question, string $answer, array $options): array
    {
        $explanations = [];

        $explanationData = $this->getExplanationData($question, $answer);

        foreach ($options as $option) {
            if ($option === $answer) {
                $explanations[$option] = $explanationData['correct'] ?? '✅ Правильна відповідь. Дотримано стандартний порядок слів в англійській мові.';
            } else {
                $explanations[$option] = $explanationData['wrong'][$option] ?? "❌ Неправильний порядок слів. Правильний варіант: \"{$answer}\".";
            }
        }

        return $explanations;
    }

    private function getExplanationData(array $question, string $answer): array
    {
        $questionText = $question['question'];
        $exercise = $question['exercise'] ?? 1;

        $data = [
            // Exercise 1
            "I didn't like {a1} last night." => [
                'correct' => "✅ «the food very much» — правильно. Прямий додаток (the food) завжди стоїть перед прислівником міри (very much). Приклад: *I didn't like the food very much last night.*",
                'wrong' => [
                    'very much the food' => "❌ Прислівник міри (very much) не ставиться перед прямим додатком (the food). Правильний порядок: об'єкт → прислівник.",
                ],
            ],
            'We arrived {a1}.' => [
                'correct' => '✅ «home very late» — правильно. Спочатку вказуємо місце (home), потім обставину часу/способу (very late). Приклад: *We arrived home very late.*',
                'wrong' => [
                    'very late home' => '❌ Обставина (very late) не ставиться перед місцем (home). Місце ближче до дієслова.',
                ],
            ],
            'Teresa speaks {a1}.' => [
                'correct' => '✅ «English very well» — правильно. Прямий додаток (English) стоїть перед прислівником способу (very well). Приклад: *Teresa speaks English very well.*',
                'wrong' => [
                    'very well English' => "❌ Прислівник способу (very well) не ставиться перед об'єктом (English). Правильний порядок: об'єкт → прислівник.",
                ],
            ],
            'Please, can you put {a1}?' => [
                'correct' => '✅ «that box in the corner» — правильно. Спочатку називаємо додаток (that box), потім місце (in the corner). Приклад: *Can you put that box in the corner?*',
                'wrong' => [
                    'in the corner that box' => '❌ Обставина місця (in the corner) не передує прямому додатку (that box). Предмет називаємо першим.',
                ],
            ],
            'After the accident, Jim was {a1}.' => [
                'correct' => '✅ «in hospital for a week» — правильно. Обставина місця (in hospital) стоїть перед тривалістю (for a week). Приклад: *Jim was in hospital for a week.*',
                'wrong' => [
                    'for a week in hospital' => '❌ Тривалість часу (for a week) не ставиться перед місцем (in hospital). Спершу вказуємо місце.',
                ],
            ],
            'I like {a1}.' => [
                'correct' => "✅ «this music a lot» — правильно. Об'єкт (this music) стоїть перед прислівником міри (a lot). Приклад: *I like this music a lot.*",
                'wrong' => [
                    'a lot this music' => "❌ Прислівник міри (a lot) не ставиться перед об'єктом (this music). Об'єкт завжди після дієслова.",
                ],
            ],
            'They met {a1}.' => [
                'correct' => "✅ «at a friend's house after a party» — правильно. Конкретне місце (at a friend's house) перед ширшим контекстом (after a party). Приклад: *They met at a friend's house after a party.*",
                'wrong' => [
                    "after a party at a friend's house" => '❌ Ширший контекст (after a party) не ставиться перед конкретним місцем. Логіка: від конкретного до загального.',
                ],
            ],
            'Tigers approach {a1}.' => [
                'correct' => "✅ «their victims very slowly» — правильно. Об'єкт (their victims) стоїть перед прислівником способу (very slowly). Приклад: *Tigers approach their victims very slowly.*",
                'wrong' => [
                    'very slowly their victims' => "❌ Прислівник способу (very slowly) не ставиться перед об'єктом (their victims). Об'єкт ближче до дієслова.",
                ],
            ],
            'I saw {a1}.' => [
                'correct' => '✅ «Hellen at the library» — правильно. Спочатку називаємо кого бачили (Hellen), потім де (at the library). Приклад: *I saw Hellen at the library.*',
                'wrong' => [
                    'at the library Hellen' => "❌ Місце (at the library) не ставиться перед об'єктом (Hellen). Особа названа першою.",
                ],
            ],
            'He finished {a1}.' => [
                'correct' => '✅ «the exam very quickly» — правильно. Додаток (the exam) стоїть перед прислівником способу (very quickly). Приклад: *He finished the exam very quickly.*',
                'wrong' => [
                    'very quickly the exam' => "❌ Прислівник способу (very quickly) не ставиться перед додатком (the exam). Об'єкт іде першим.",
                ],
            ],

            // Exercise 2
            'I walk {a1}.' => [
                'correct' => "✅ «my dog in the park every day» — правильно. Порядок: об'єкт (my dog) → місце (in the park) → частота (every day). Приклад: *I walk my dog in the park every day.*",
                'wrong' => [
                    'my dog every day in the park' => "❌ Частота (every day) не ставиться перед місцем (in the park). Стандартний порядок: об'єкт → місце → час.",
                    'every day my dog in the park' => "❌ Частота (every day) не починає фразу після дієслова. Об'єкт має бути одразу після дієслова.",
                ],
            ],
            'Lisa took {a1}.' => [
                'correct' => "✅ «her son to the doctor this morning» — правильно. Порядок: об'єкт (her son) → напрямок (to the doctor) → час (this morning). Приклад: *Lisa took her son to the doctor this morning.*",
                'wrong' => [
                    'to the doctor her son this morning' => "❌ Напрямок (to the doctor) не ставиться перед об'єктом (her son). Об'єкт завжди першим.",
                    'this morning her son to the doctor' => "❌ Час (this morning) не починає фразу. Стандартний порядок: об'єкт → місце → час.",
                ],
            ],
            'Can you drive {a1}?' => [
                'correct' => "✅ «me home after the concert» — правильно. Порядок: об'єкт (me) → місце (home) → час (after the concert). Приклад: *Can you drive me home after the concert?*",
                'wrong' => [
                    'home me after the concert' => "❌ Місце (home) не ставиться перед об'єктом (me). Кого везти — першим.",
                    'me after the concert home' => "❌ Час (after the concert) між об'єктом і місцем порушує порядок. Куди — перед коли.",
                ],
            ],
            'We {a1}.' => $this->getWeExplanation($question),
            'I {a1}.' => $this->getIExplanation($question),
            'David {a1}.' => [
                'correct' => "✅ «loves his children very much» — правильно. Дієслово (loves) → об'єкт (his children) → прислівник міри (very much). Приклад: *David loves his children very much.*",
                'wrong' => [
                    'very much loves his children' => '❌ Прислівник міри (very much) не ставиться перед дієсловом. Прислівник наприкінці.',
                    'loves very much his children' => "❌ Прислівник міри (very much) не розділяє дієслово і об'єкт. Об'єкт одразу після дієслова.",
                ],
            ],
            'I learnt {a1}.' => [
                'correct' => "✅ «Italian in Rome during a student exchange program» — правильно. Порядок: об'єкт (Italian) → місце (in Rome) → час/подія (during...). Приклад: *I learnt Italian in Rome during a student exchange program.*",
                'wrong' => [
                    'Italian during a student exchange program in Rome' => "❌ Подія (during...) не ставиться перед місцем (in Rome). Місце ближче до об'єкта.",
                    'in Rome Italian during a student exchange program' => "❌ Місце (in Rome) не передує об'єкту (Italian). Об'єкт завжди після дієслова.",
                ],
            ],

            // Exercise 3
            'We {a1}.  (a nice lunch / had / today / we / at a restaurant)' => [
                'correct' => "✅ «had a nice lunch at a restaurant today» — правильно. Порядок: дієслово → об'єкт → місце → час. Приклад: *We had a nice lunch at a restaurant today.*",
                'wrong' => [
                    'had a nice lunch today at a restaurant' => '❌ Час (today) не ставиться перед місцем (at a restaurant). Місце перед часом.',
                    'had at a restaurant a nice lunch today' => "❌ Місце (at a restaurant) не розділяє дієслово і об'єкт. Об'єкт одразу після дієслова.",
                ],
            ],
            'He {a1}.  (the keys / last night / in the car / forgot / He)' => [
                'correct' => "✅ «forgot the keys in the car last night» — правильно. Порядок: дієслово → об'єкт → місце → час. Приклад: *He forgot the keys in the car last night.*",
                'wrong' => [
                    'forgot in the car the keys last night' => "❌ Місце (in the car) не передує об'єкту (the keys). Предмет називаємо першим.",
                    'forgot the keys last night in the car' => '❌ Час (last night) не ставиться перед місцем (in the car). Місце перед часом.',
                ],
            ],
            'Susan {a1}.  (often / Susan / takes / to work / a taxi)' => [
                'correct' => "✅ «often takes a taxi to work» — правильно. Прислівник частоти (often) перед дієсловом, потім об'єкт → напрямок. Приклад: *Susan often takes a taxi to work.*",
                'wrong' => [
                    'takes often a taxi to work' => "❌ Прислівник частоти (often) не ставиться між дієсловом і об'єктом. Частота перед дієсловом.",
                    'takes a taxi often to work' => "❌ Прислівник частоти (often) не розділяє об'єкт і напрямок. Частота на початку.",
                ],
            ],
            'I {a1}.  (have to return / I / the book / to the library / today)' => [
                'correct' => "✅ «have to return the book to the library today» — правильно. Модальна фраза → об'єкт → місце → час. Приклад: *I have to return the book to the library today.*",
                'wrong' => [
                    'have to return today the book to the library' => "❌ Час (today) не ставиться між фразою і об'єктом. Час наприкінці.",
                    'have to return to the library the book today' => "❌ Місце (to the library) не передує об'єкту (the book). Об'єкт після дієслова.",
                ],
            ],
            'They {a1}.  (are / never / at lunchtime / They / at home)' => [
                'correct' => '✅ «are never at home at lunchtime» — правильно. Дієслово be → прислівник частоти → місце → час. Приклад: *They are never at home at lunchtime.*',
                'wrong' => [
                    'are never at lunchtime at home' => '❌ Час (at lunchtime) не ставиться перед місцем (at home). Місце перед часом.',
                    'never are at home at lunchtime' => '❌ Прислівник частоти (never) ставиться після дієслова be, а не перед ним.',
                ],
            ],
            'It {a1}.  (It / always / very hot / here / in July / is)' => [
                'correct' => '✅ «is always very hot here in July» — правильно. Be → прислівник частоти → прикметник → місце → час. Приклад: *It is always very hot here in July.*',
                'wrong' => [
                    'is very hot always here in July' => '❌ Прислівник частоти (always) має стояти одразу після дієслова be, не в середині.',
                    'is always very hot in July here' => '❌ Місце (here) не ставиться після часу (in July). Місце перед часом.',
                ],
            ],
            'We {a1}.  (play / football / after school / in the park / We)' => [
                'correct' => "✅ «play football in the park after school» — правильно. Дієслово → об'єкт → місце → час. Приклад: *We play football in the park after school.*",
                'wrong' => [
                    'play football after school in the park' => '❌ Час (after school) не ставиться перед місцем (in the park). Місце перед часом.',
                    'play in the park football after school' => "❌ Місце (in the park) не розділяє дієслово і об'єкт. Об'єкт одразу після дієслова.",
                ],
            ],
            'She {a1}.  (usually / She / cereal / has / for breakfast)' => [
                'correct' => "✅ «usually has cereal for breakfast» — правильно. Прислівник частоти перед дієсловом, потім об'єкт → обставина. Приклад: *She usually has cereal for breakfast.*",
                'wrong' => [
                    'has usually cereal for breakfast' => "❌ Прислівник частоти (usually) не ставиться між дієсловом і об'єктом. Частота перед дієсловом.",
                    'has cereal usually for breakfast' => "❌ Прислівник частоти (usually) не розділяє об'єкт і обставину. Частота на початку.",
                ],
            ],
            'Linda {a1}.  (met / at a nightclub / in 2006 / Linda / her husband)' => [
                'correct' => "✅ «met her husband at a nightclub in 2006» — правильно. Дієслово → об'єкт → місце → час. Приклад: *Linda met her husband at a nightclub in 2006.*",
                'wrong' => [
                    'met her husband in 2006 at a nightclub' => '❌ Час (in 2006) не ставиться перед місцем (at a nightclub). Місце перед часом.',
                    'met at a nightclub her husband in 2006' => "❌ Місце (at a nightclub) не передує об'єкту (her husband). Об'єкт після дієслова.",
                ],
            ],
            'I {a1}.  (always / I / from home / work / on Tuesdays)' => [
                'correct' => '✅ «always work from home on Tuesdays» — правильно. Прислівник частоти перед дієсловом, потім місце → час. Приклад: *I always work from home on Tuesdays.*',
                'wrong' => [
                    'work from home always on Tuesdays' => '❌ Прислівник частоти (always) має стояти перед дієсловом, не після місця.',
                    'work always from home on Tuesdays' => '❌ Прислівник частоти (always) не ставиться між дієсловом і місцем. Частота перед дієсловом.',
                ],
            ],
        ];

        return $data[$questionText] ?? [
            'correct' => '✅ Правильна відповідь. Дотримано стандартний порядок слів.',
            'wrong' => [],
        ];
    }

    private function getWeExplanation(array $question): array
    {
        $questionText = $question['question'];
        $exercise = $question['exercise'] ?? 1;

        if ($exercise === 2 && strpos($questionText, 'always finish') !== false) {
            return [
                'correct' => '✅ «always finish school at 3.30 pm» — правильно. Прислівник частоти (always) перед дієсловом, час наприкінці. Приклад: *We always finish school at 3.30 pm.*',
                'wrong' => [
                    'finish always school at 3.30 pm' => "❌ Прислівник частоти (always) не ставиться між дієсловом і об'єктом. Частота перед дієсловом.",
                    'finish school at 3.30 pm always' => '❌ Прислівник частоти (always) зазвичай не стоїть наприкінці такого речення. Частота перед дієсловом.',
                ],
            ];
        }

        if ($exercise === 2 && strpos($questionText, 'enjoyed') !== false) {
            return [
                'correct' => "✅ «enjoyed the trip very much» — правильно. Дієслово → об'єкт → прислівник міри. Приклад: *We enjoyed the trip very much.*",
                'wrong' => [
                    'enjoyed very much the trip' => "❌ Прислівник міри (very much) не ставиться перед об'єктом (the trip). Об'єкт одразу після дієслова.",
                    'very much enjoyed the trip' => '❌ Прислівник міри (very much) не починає фразу. Прислівник наприкінці.',
                ],
            ];
        }

        return [
            'correct' => '✅ Правильна відповідь. Дотримано стандартний порядок слів.',
            'wrong' => [],
        ];
    }

    private function getIExplanation(array $question): array
    {
        $questionText = $question['question'];
        $exercise = $question['exercise'] ?? 1;

        if ($exercise === 2) {
            if (strpos($questionText, 'bought') !== false || strpos($questionText, 'I {a1}.') !== false) {
                $options = $question['options'] ?? [];
                if (in_array('bought in Paris this jacket last year', $options)) {
                    return [
                        'correct' => "✅ «bought this jacket in Paris last year» — правильно. Дієслово → об'єкт → місце → час. Приклад: *I bought this jacket in Paris last year.*",
                        'wrong' => [
                            'bought in Paris this jacket last year' => "❌ Місце (in Paris) не передує об'єкту (this jacket). Об'єкт після дієслова.",
                            'bought this jacket last year in Paris' => '❌ Час (last year) не ставиться перед місцем (in Paris). Місце перед часом.',
                        ],
                    ];
                }
                if (in_array('will call my parents immediately', $options)) {
                    return [
                        'correct' => "✅ «will call my parents immediately» — правильно. Дієслово → об'єкт → прислівник часу. Приклад: *I will call my parents immediately.*",
                        'wrong' => [
                            'will call immediately my parents' => "❌ Прислівник (immediately) не розділяє дієслово і об'єкт. Прислівник наприкінці.",
                            'immediately will call my parents' => '❌ Прислівник (immediately) не починає фразу в такому реченні. Прислівник наприкінці.',
                        ],
                    ];
                }
                if (in_array("don't play very often tennis", $options)) {
                    return [
                        'correct' => "✅ «don't like tennis very much» — правильно. Дієслово → об'єкт → прислівник міри. Приклад: *I don't like tennis very much.*",
                        'wrong' => [
                            "don't play very often tennis" => '❌ Це інше дієслово (play), а не like. Крім того, порядок слів неправильний.',
                            "don't like very much tennis" => "❌ Прислівник міри (very much) не ставиться перед об'єктом (tennis). Об'єкт одразу після дієслова.",
                        ],
                    ];
                }
            }
        }

        return [
            'correct' => '✅ Правильна відповідь. Дотримано стандартний порядок слів.',
            'wrong' => [],
        ];
    }
}
