<?php

namespace Database\Seeders\Ai;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class GeminiDegreesOfComparisonSeeder extends QuestionSeeder
{
    private array $levelDifficulty = [
        'A1' => 1,
        'A2' => 2,
        'B1' => 3,
        'B2' => 4,
        'C1' => 5,
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Adjectives and Adverbs'])->id;

        $sourceId = Source::firstOrCreate(['name' => 'AI:GptDegreesOfComparison'])->id;

        $tagIds = $this->buildTags();

        $items = [];
        $meta = [];

        foreach ($this->questionEntries() as $index => $entry) {
            $answers = [];
            foreach ($entry['answers'] as $marker => $answer) {
                $answers[] = [
                    'marker' => $marker,
                    'answer' => $answer,
                    'verb_hint' => $entry['verb_hints'][$marker] ?? null,
                ];
            }

            $options = $this->flattenOptions($entry['options']);
            $uuid = $this->generateQuestionUuid($entry['level'], $index + 1, $entry['question']);

            $items[] = [
                'uuid' => $uuid,
                'question' => $entry['question'],
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$entry['level']] ?? 3,
                'source_id' => $sourceId,
                'flag' => 2,
                'type' => 0,
                'level' => $entry['level'],
                'tag_ids' => $tagIds,
                'answers' => $answers,
                'options' => $options,
                'variants' => [$entry['question']],
            ];

            $meta[] = [
                'uuid' => $uuid,
                'hints' => $entry['hints'] ?? [],
                'answers' => $entry['answers'],
                'option_markers' => $this->buildOptionMarkers($entry['options']),
                'explanations' => $entry['explanations'] ?? [],
            ];
        }

        $this->seedQuestionData($items, $meta);
    }

    private function buildTags(): array
    {
        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Degrees of Comparison'],
            ['category' => 'English Grammar Theme']
        )->id;

        $comparativeTagId = Tag::firstOrCreate(
            ['name' => 'Comparative Forms'],
            ['category' => 'English Grammar Detail']
        )->id;

        $superlativeTagId = Tag::firstOrCreate(
            ['name' => 'Superlative Forms'],
            ['category' => 'English Grammar Detail']
        )->id;

        $irregularTagId = Tag::firstOrCreate(
            ['name' => 'Irregular Adjectives'],
            ['category' => 'English Grammar Focus']
        )->id;

        $adverbsTagId = Tag::firstOrCreate(
            ['name' => 'Adverbs of Comparison'],
            ['category' => 'English Grammar Focus']
        )->id;

        $equalityTagId = Tag::firstOrCreate(
            ['name' => 'Equality Comparisons (as...as)'],
            ['category' => 'English Grammar Pattern']
        )->id;

        $skillTagId = Tag::firstOrCreate(
            ['name' => 'Multiple Choice Grammar'],
            ['category' => 'Skill Type']
        )->id;

        return [
            $themeTagId,
            $comparativeTagId,
            $superlativeTagId,
            $irregularTagId,
            $adverbsTagId,
            $equalityTagId,
            $skillTagId,
        ];
    }

    private function questionEntries(): array
    {
        return [
            // ===== A1 Level (12 questions): Simple vocabulary, basic present tense, short adjectives =====
            [
                'level' => 'A1',
                'question' => 'An elephant is {a1} than a mouse.',
                'answers' => ['a1' => 'bigger'],
                'options' => ['a1' => ['bigger', 'biggest', 'big']],
                'verb_hints' => ['a1' => 'large in size'],
                'hints' => ['Для коротких прикметників додаємо -er для порівняння двох речей.'],
                'explanations' => [
                    'bigger' => 'Коли порівнюємо два предмети, використовуємо прикметник + -er + than. Формула: adjective + -er + than. Наприклад: A car is faster than a bicycle.',
                    'biggest' => 'Суперлатив (найвищий ступінь) використовується для порівняння з групою, а не двох предметів. Формула: the + adjective-est.',
                    'big' => 'Базова форма прикметника не підходить для порівняння. Потрібно додати -er для comparative.',
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'The summer day is {a1} than the spring day.',
                'answers' => ['a1' => 'hotter'],
                'options' => ['a1' => ['hotter', 'hottest', 'hot']],
                'verb_hints' => ['a1' => 'temperature, warm'],
                'hints' => ['Короткі прикметники із закінченням приголосна-голосна-приголосна подвоюють останню приголосну перед -er.'],
                'explanations' => [
                    'hotter' => 'При порівнянні двох сезонів вживаємо comparative: adjective + -er + than. Hot подвоює t перед -er.',
                    'hottest' => 'Суперлатив вживається з the для найвищого ступеня серед групи, не для порівняння двох речей.',
                    'hot' => 'Базова форма не виражає порівняння. Для двох предметів потрібен comparative.',
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'The kitten is {a1} than the puppy.',
                'answers' => ['a1' => 'smaller'],
                'options' => ['a1' => ['smaller', 'smallest', 'small']],
                'verb_hints' => ['a1' => 'little, not large'],
                'hints' => ['Використовуйте прикметник + -er + than при порівнянні двох тварин або предметів.'],
                'explanations' => [
                    'smaller' => 'Формула comparative для коротких прикметників: base + -er + than. Наприклад: A mouse is quieter than a bird.',
                    'smallest' => 'Superlative потребує the та використовується для групи з трьох і більше.',
                    'small' => 'Без суфікса -er неможливо виразити порівняння двох предметів.',
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'Is your bicycle {a1} than mine?',
                'answers' => ['a1' => 'newer'],
                'options' => ['a1' => ['newer', 'newest', 'new']],
                'verb_hints' => ['a1' => 'recent, modern'],
                'hints' => ['У питаннях форма comparative залишається такою ж: прикметник + -er + than.'],
                'explanations' => [
                    'newer' => 'У запитаннях comparative зберігає форму: Is X adjective-er than Y? Наприклад: Is the tree taller than the fence?',
                    'newest' => 'Superlative не підходить для порівняння двох велосипедів; потрібен comparative.',
                    'new' => 'Базова форма не виражає порівняння в питальному реченні.',
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'This pencil is not as {a1} as that pen.',
                'answers' => ['a1' => 'long'],
                'options' => ['a1' => ['long', 'longer', 'longest']],
                'verb_hints' => ['a1' => 'length, size'],
                'hints' => ['Конструкція "as + прикметник + as" використовує базову форму прикметника.'],
                'explanations' => [
                    'long' => 'Конструкція as + adjective + as використовує базову форму. Наприклад: not as tall as.',
                    'longer' => 'Comparative не вживається в структурі as ... as; там потрібна базова форма.',
                    'longest' => 'Superlative не підходить для конструкції рівності as ... as.',
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'The giraffe is the {a1} animal in the zoo.',
                'answers' => ['a1' => 'tallest'],
                'options' => ['a1' => ['tallest', 'taller', 'tall']],
                'verb_hints' => ['a1' => 'height, high'],
                'hints' => ['Використовуйте "the + прикметник-est" для порівняння одного предмета з усіма іншими в групі.'],
                'explanations' => [
                    'tallest' => 'Superlative: the + adjective-est для найвищого ступеня в групі. Наприклад: the fastest car.',
                    'taller' => 'Comparative порівнює два предмети, а не один із групою.',
                    'tall' => 'Базова форма не виражає найвищий ступінь.',
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'Water is {a1} than juice.',
                'answers' => ['a1' => 'cheaper'],
                'options' => ['a1' => ['cheaper', 'cheapest', 'cheap']],
                'verb_hints' => ['a1' => 'price, cost'],
                'hints' => ['Короткі прикметники додають -er для порівняння двох предметів.'],
                'explanations' => [
                    'cheaper' => 'Порівняння двох речей: adjective + -er + than. Наприклад: A bike is slower than a car.',
                    'cheapest' => 'Superlative вживається для найвищого ступеня серед групи, а не двох предметів.',
                    'cheap' => 'Базова форма не виражає порівняння.',
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'My room is not {a1} than yours.',
                'answers' => ['a1' => 'cleaner'],
                'options' => ['a1' => ['cleaner', 'cleanest', 'clean']],
                'verb_hints' => ['a1' => 'tidy, neat'],
                'hints' => ['Заперечні речення з "not" все ще використовують comparative форму для двох предметів.'],
                'explanations' => [
                    'cleaner' => 'У запереченні comparative зберігає форму: not + adjective-er + than. Наприклад: not faster than.',
                    'cleanest' => 'Superlative не підходить для заперечного порівняння двох кімнат.',
                    'clean' => 'Базова форма не виражає порівняння.',
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'Is this box as {a1} as that one?',
                'answers' => ['a1' => 'heavy'],
                'options' => ['a1' => ['heavy', 'heavier', 'heaviest']],
                'verb_hints' => ['a1' => 'weight'],
                'hints' => ['Питання з "as...as" використовують базову форму прикметника.'],
                'explanations' => [
                    'heavy' => 'Питання з as ... as використовує базову форму прикметника. Наприклад: as fast as.',
                    'heavier' => 'Comparative не вживається в структурі as ... as.',
                    'heaviest' => 'Superlative не підходить для питань про рівність.',
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'Today is {a1} than yesterday.',
                'answers' => ['a1' => 'colder'],
                'options' => ['a1' => ['colder', 'coldest', 'cold']],
                'verb_hints' => ['a1' => 'temperature, chilly'],
                'hints' => ['Порівнюйте два дні за допомогою прикметника + -er + than.'],
                'explanations' => [
                    'colder' => 'Порівняння двох днів: adjective + -er + than. Наприклад: Monday was busier than Tuesday.',
                    'coldest' => 'Superlative потребує the та групи з трьох і більше.',
                    'cold' => 'Базова форма не виражає порівняння двох днів.',
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'The blue car is the {a1} in the parking lot.',
                'answers' => ['a1' => 'fastest'],
                'options' => ['a1' => ['fastest', 'faster', 'fast']],
                'verb_hints' => ['a1' => 'speed'],
                'hints' => ['Використовуйте "the + прикметник-est" для опису найкращого предмета в групі.'],
                'explanations' => [
                    'fastest' => 'Superlative: the + adjective-est виражає найвищий ступінь у групі. Наприклад: the oldest building.',
                    'faster' => 'Comparative порівнює два предмети, а не один із групою.',
                    'fast' => 'Базова форма не виражає найвищий ступінь.',
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'A feather is {a1} than a stone.',
                'answers' => ['a1' => 'lighter'],
                'options' => ['a1' => ['lighter', 'lightest', 'light']],
                'verb_hints' => ['a1' => 'weight, not heavy'],
                'hints' => ['Короткі прикметники утворюють comparative за допомогою -er + than.'],
                'explanations' => [
                    'lighter' => 'Comparative для коротких прикметників: base + -er + than. Наприклад: Paper is thinner than cardboard.',
                    'lightest' => 'Superlative вживається для групи, а не двох предметів.',
                    'light' => 'Базова форма не виражає порівняння.',
                ],
            ],

            // ===== A2 Level (12 questions): Mix of present/past/future, longer adjectives, some irregular =====
            [
                'level' => 'A2',
                'question' => 'Last Monday was {a1} than this Monday.',
                'answers' => ['a1' => 'sunnier'],
                'options' => ['a1' => ['sunnier', 'sunniest', 'sunny']],
                'verb_hints' => ['a1' => 'weather, bright'],
                'hints' => ['Прикметники на -y змінюють закінчення на -ier у формі comparative.'],
                'explanations' => [
                    'sunnier' => 'Прикметники на -y змінюють закінчення на -ier: sunny → sunnier. Наприклад: happy → happier.',
                    'sunniest' => 'Superlative потребує the та групи з трьох і більше днів.',
                    'sunny' => 'Базова форма не виражає порівняння минулого і сьогоднішнього дня.',
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'This movie was not as {a1} as the book.',
                'answers' => ['a1' => 'interesting'],
                'options' => ['a1' => ['interesting', 'more interesting', 'most interesting']],
                'verb_hints' => ['a1' => 'engaging, captivating'],
                'hints' => ['Структура "not as + прикметник + as" використовує базову форму.'],
                'explanations' => [
                    'interesting' => 'Конструкція not as + adjective + as використовує базову форму. Наприклад: not as exciting as.',
                    'more interesting' => 'Comparative не вживається в структурі as ... as.',
                    'most interesting' => 'Superlative не підходить для конструкції рівності.',
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'Will the new building be {a1} than the old one?',
                'answers' => ['a1' => 'taller'],
                'options' => ['a1' => ['taller', 'tallest', 'tall']],
                'verb_hints' => ['a1' => 'height'],
                'hints' => ['Питання про майбутнє все ще використовують comparative форму для двох предметів.'],
                'explanations' => [
                    'taller' => 'У майбутньому часі comparative зберігає форму: Will X be adjective-er than Y? Наприклад: Will it be colder tomorrow?',
                    'tallest' => 'Superlative не підходить для порівняння двох будівель.',
                    'tall' => 'Базова форма не виражає порівняння.',
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'My grandfather is {a1} than my father.',
                'answers' => ['a1' => 'wiser'],
                'options' => ['a1' => ['wiser', 'wisest', 'wise']],
                'verb_hints' => ['a1' => 'knowledge, smart'],
                'hints' => ['Прикметники на -e просто додають -r для comparative.'],
                'explanations' => [
                    'wiser' => 'Прикметники на -e додають лише -r: wise → wiser. Наприклад: large → larger.',
                    'wisest' => 'Superlative потребує the та групи з трьох і більше осіб.',
                    'wise' => 'Базова форма не виражає порівняння двох людей.',
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'The bus was {a1} than we expected.',
                'answers' => ['a1' => 'slower'],
                'options' => ['a1' => ['slower', 'slowest', 'slow']],
                'verb_hints' => ['a1' => 'speed, pace'],
                'hints' => ['Використовуйте comparative + than для вираження несподіваних відмінностей.'],
                'explanations' => [
                    'slower' => 'Comparative виражає різницю: adjective-er + than expected. Наприклад: The test was harder than expected.',
                    'slowest' => 'Superlative не підходить для порівняння з очікуваннями.',
                    'slow' => 'Базова форма не виражає порівняння.',
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'This tea tastes {a1} than the coffee.',
                'answers' => ['a1' => 'sweeter'],
                'options' => ['a1' => ['sweeter', 'sweetest', 'sweet']],
                'verb_hints' => ['a1' => 'taste, flavour'],
                'hints' => ['Короткі прикметники додають -er при порівнянні двох речей.'],
                'explanations' => [
                    'sweeter' => 'Короткі прикметники: base + -er + than. Наприклад: The juice was tastier than the soda.',
                    'sweetest' => 'Superlative потребує групи, а не двох напоїв.',
                    'sweet' => 'Базова форма не виражає порівняння смаку.',
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'Is English {a1} than French?',
                'answers' => ['a1' => 'easier'],
                'options' => ['a1' => ['easier', 'easiest', 'easy']],
                'verb_hints' => ['a1' => 'simple, not difficult'],
                'hints' => ['Питання про порівняння двох мов використовують прикметник-ier + than.'],
                'explanations' => [
                    'easier' => 'Питання з comparative: Is X adjective-er than Y? Easy → easier. Наприклад: Is the exam harder than before?',
                    'easiest' => 'Superlative не підходить для порівняння двох мов.',
                    'easy' => 'Базова форма не виражає порівняння.',
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'Her dress was the {a1} at the party.',
                'answers' => ['a1' => 'prettiest'],
                'options' => ['a1' => ['prettiest', 'prettier', 'pretty']],
                'verb_hints' => ['a1' => 'beautiful, nice'],
                'hints' => ['Використовуйте "the + прикметник-iest" для найкращого предмета в групі.'],
                'explanations' => [
                    'prettiest' => 'Superlative: the + adjective-iest для найвищого ступеня. Pretty → prettiest. Наприклад: the happiest day.',
                    'prettier' => 'Comparative порівнює два предмети, а не один із групою.',
                    'pretty' => 'Базова форма не виражає найвищий ступінь.',
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'This pizza is {a1} than that one.',
                'answers' => ['a1' => 'better'],
                'options' => ['a1' => ['better', 'best', 'good']],
                'verb_hints' => ['a1' => 'quality, taste'],
                'hints' => ['Деякі прикметники мають неправильні форми comparative.'],
                'explanations' => [
                    'better' => 'Good має неправильну форму comparative: good → better. Наприклад: This book is better than the movie.',
                    'best' => 'Superlative вживається для найвищого ступеня в групі.',
                    'good' => 'Базова форма не виражає порівняння. Good → better, не gooder.',
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'The traffic will be {a1} later in the evening.',
                'answers' => ['a1' => 'worse'],
                'options' => ['a1' => ['worse', 'worst', 'bad']],
                'verb_hints' => ['a1' => 'negative, not good'],
                'hints' => ['Bad має неправильну форму comparative.'],
                'explanations' => [
                    'worse' => 'Bad → worse — неправильна форма. Наприклад: My cold is getting worse.',
                    'worst' => 'Superlative потребує the: the worst day.',
                    'bad' => 'Базова форма не виражає порівняння. Bad → worse, не badder.',
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'Did you walk {a1} than usual yesterday?',
                'answers' => ['a1' => 'farther'],
                'options' => ['a1' => ['farther', 'farthest', 'far']],
                'verb_hints' => ['a1' => 'distance'],
                'hints' => ['Far має неправильний comparative: farther/further.'],
                'explanations' => [
                    'farther' => 'Far → farther/further — неправильна форма. Наприклад: The store is farther than the bank.',
                    'farthest' => 'Superlative потребує the: the farthest point.',
                    'far' => 'Базова форма не виражає порівняння відстані.',
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'This exercise is the {a1} in the book.',
                'answers' => ['a1' => 'hardest'],
                'options' => ['a1' => ['hardest', 'harder', 'hard']],
                'verb_hints' => ['a1' => 'difficult, challenging'],
                'hints' => ['Використовуйте "the + прикметник-est" коли один предмет виділяється в групі.'],
                'explanations' => [
                    'hardest' => 'Superlative: the + adjective-est для найвищого ступеня. Наприклад: the longest river.',
                    'harder' => 'Comparative порівнює два предмети, а не один із групою.',
                    'hard' => 'Базова форма не виражає найвищий ступінь.',
                ],
            ],

            // ===== B1 Level (12 questions): Present/past/future, longer adjectives, adverbs, more/most =====
            [
                'level' => 'B1',
                'question' => 'She speaks English {a1} than her brother.',
                'answers' => ['a1' => 'more fluently'],
                'options' => ['a1' => ['more fluently', 'most fluently', 'fluently']],
                'verb_hints' => ['a1' => 'smooth speech'],
                'hints' => ['Прислівники на -ly утворюють comparative за допомогою "more" + прислівник.'],
                'explanations' => [
                    'more fluently' => 'Прислівники на -ly утворюють comparative з more: more fluently. Наприклад: She runs more quickly than him.',
                    'most fluently' => 'Superlative вживається для найвищого ступеня в групі.',
                    'fluently' => 'Базова форма не виражає порівняння.',
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'Was the performance {a1} than you thought?',
                'answers' => ['a1' => 'more exciting'],
                'options' => ['a1' => ['more exciting', 'most exciting', 'excitinger']],
                'verb_hints' => ['a1' => 'thrilling, engaging'],
                'hints' => ['Довгі прикметники використовують "more" + прикметник + "than" для порівнянь.'],
                'explanations' => [
                    'more exciting' => 'Довгі прикметники утворюють comparative з more: more exciting. Наприклад: more interesting than.',
                    'most exciting' => 'Superlative потребує the та групи.',
                    'excitinger' => 'Довгі прикметники не додають -er. Правильно: more exciting.',
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'The new smartphone is {a1} than the old model.',
                'answers' => ['a1' => 'more expensive'],
                'options' => ['a1' => ['more expensive', 'expensiver', 'most expensive']],
                'verb_hints' => ['a1' => 'price, costly'],
                'hints' => ['Багатоскладові прикметники утворюють comparative за допомогою "more" перед ними.'],
                'explanations' => [
                    'more expensive' => 'Багатоскладові прикметники: more + adjective + than. Наприклад: more beautiful than.',
                    'expensiver' => 'Довгі прикметники не додають -er. Правильно: more expensive.',
                    'most expensive' => 'Superlative потребує the та групи.',
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'He did not finish the project as {a1} as she did.',
                'answers' => ['a1' => 'quickly'],
                'options' => ['a1' => ['quickly', 'more quickly', 'quickest']],
                'verb_hints' => ['a1' => 'speed, fast'],
                'hints' => ['Конструкція "as + прислівник + as" використовує базову форму.'],
                'explanations' => [
                    'quickly' => 'Конструкція as + adverb + as використовує базову форму. Наприклад: as carefully as.',
                    'more quickly' => 'Comparative не вживається в структурі as ... as.',
                    'quickest' => 'Superlative не підходить для конструкції рівності.',
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'Will this be the {a1} decision of your life?',
                'answers' => ['a1' => 'most important'],
                'options' => ['a1' => ['most important', 'more important', 'importantest']],
                'verb_hints' => ['a1' => 'significant, crucial'],
                'hints' => ['Довгі прикметники утворюють superlative за допомогою "the most" + прикметник.'],
                'explanations' => [
                    'most important' => 'Superlative для довгих прикметників: the most + adjective. Наприклад: the most beautiful city.',
                    'more important' => 'Comparative порівнює два предмети, а не найвищий ступінь.',
                    'importantest' => 'Довгі прикметники не додають -est. Правильно: the most important.',
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'The teacher explained the lesson {a1} this time.',
                'answers' => ['a1' => 'more clearly'],
                'options' => ['a1' => ['more clearly', 'clearlier', 'most clearly']],
                'verb_hints' => ['a1' => 'understandable'],
                'hints' => ['Прислівники на -ly використовують "more" для порівнянь.'],
                'explanations' => [
                    'more clearly' => 'Прислівники на -ly: more + adverb. Наприклад: more slowly, more carefully.',
                    'clearlier' => 'Прислівники на -ly не додають -er. Правильно: more clearly.',
                    'most clearly' => 'Superlative потребує the та контексту групи.',
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'Is pollution getting {a1} every year?',
                'answers' => ['a1' => 'worse'],
                'options' => ['a1' => ['worse', 'more bad', 'worst']],
                'verb_hints' => ['a1' => 'negative change'],
                'hints' => ['Bad має неправильну форму comparative.'],
                'explanations' => [
                    'worse' => 'Bad → worse — неправильна форма. Наприклад: The traffic is getting worse.',
                    'more bad' => 'Bad не вживається з more. Правильно: worse.',
                    'worst' => 'Superlative потребує the: the worst pollution.',
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'That was the {a1} movie I have ever seen.',
                'answers' => ['a1' => 'least interesting'],
                'options' => ['a1' => ['least interesting', 'less interesting', 'interestingless']],
                'verb_hints' => ['a1' => 'boring, dull'],
                'hints' => ['Використовуйте "the least + прикметник" для найнижчого ступеня в групі.'],
                'explanations' => [
                    'least interesting' => 'The least + adjective виражає найнижчий ступінь. Наприклад: the least expensive option.',
                    'less interesting' => 'Less + adjective порівнює два предмети, а не найнижчий ступінь.',
                    'interestingless' => 'Неправильна форма. Для найнижчого ступеня: the least + adjective.',
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'She arrived {a1} than I did.',
                'answers' => ['a1' => 'later'],
                'options' => ['a1' => ['later', 'latest', 'more late']],
                'verb_hints' => ['a1' => 'time, timing'],
                'hints' => ['Late має правильний comparative: later.'],
                'explanations' => [
                    'later' => 'Late → later — правильна форма comparative. Наприклад: He finished later than expected.',
                    'latest' => 'Superlative потребує the: the latest news.',
                    'more late' => 'Late не вживається з more. Правильно: later.',
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'The second test was {a1} than the first.',
                'answers' => ['a1' => 'less difficult'],
                'options' => ['a1' => ['less difficult', 'difficulter', 'least difficult']],
                'verb_hints' => ['a1' => 'easier, simpler'],
                'hints' => ['Використовуйте "less + прикметник + than" для порівняння нижчого ступеня.'],
                'explanations' => [
                    'less difficult' => 'Less + adjective + than виражає нижчий ступінь. Наприклад: less expensive than.',
                    'difficulter' => 'Довгі прикметники не додають -er. Правильно: less/more difficult.',
                    'least difficult' => 'Superlative потребує the та групи.',
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'He drives {a1} than his father.',
                'answers' => ['a1' => 'more carefully'],
                'options' => ['a1' => ['more carefully', 'carefullier', 'most carefully']],
                'verb_hints' => ['a1' => 'attention, caution'],
                'hints' => ['Прислівники на -ly використовують "more" для порівнянь.'],
                'explanations' => [
                    'more carefully' => 'Прислівники на -ly: more + adverb + than. Наприклад: more politely than.',
                    'carefullier' => 'Прислівники на -ly не додають -er. Правильно: more carefully.',
                    'most carefully' => 'Superlative потребує the та контексту групи.',
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'This is the {a1} restaurant in the city.',
                'answers' => ['a1' => 'most popular'],
                'options' => ['a1' => ['most popular', 'popularest', 'more popular']],
                'verb_hints' => ['a1' => 'famous, well-known'],
                'hints' => ['Довгі прикметники утворюють superlative за допомогою "the most" + прикметник.'],
                'explanations' => [
                    'most popular' => 'Superlative для довгих прикметників: the most + adjective. Наприклад: the most beautiful.',
                    'popularest' => 'Довгі прикметники не додають -est. Правильно: the most popular.',
                    'more popular' => 'Comparative порівнює два предмети, а не найвищий ступінь.',
                ],
            ],

            // ===== B2 Level (12 questions): Complex structures, adverbs, contrast, multiple items =====
            [
                'level' => 'B2',
                'question' => 'The more you practice, the {a1} you will become.',
                'answers' => ['a1' => 'better'],
                'options' => ['a1' => ['better', 'best', 'good']],
                'verb_hints' => ['a1' => 'improvement, progress'],
                'hints' => ['Конструкція "the more... the + comparative" показує паралельну зміну.'],
                'explanations' => [
                    'better' => 'Конструкція the more... the + comparative: the more... the better. Наприклад: The harder you work, the more you earn.',
                    'best' => 'Superlative не вживається в цій паралельній конструкції.',
                    'good' => 'Базова форма не підходить. Good → better в цій структурі.',
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'Of all the candidates, she performed the {a1}.',
                'answers' => ['a1' => 'most impressively'],
                'options' => ['a1' => ['most impressively', 'more impressively', 'impressively']],
                'verb_hints' => ['a1' => 'outstanding, remarkable'],
                'hints' => ['Прислівники використовують "the most + прислівник" для superlative.'],
                'explanations' => [
                    'most impressively' => 'Superlative прислівника: the most + adverb. Наприклад: the most efficiently.',
                    'more impressively' => 'Comparative порівнює два, а не виражає найвищий ступінь.',
                    'impressively' => 'Базова форма не виражає найвищий ступінь.',
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'This proposal is no {a1} than the previous one.',
                'answers' => ['a1' => 'better'],
                'options' => ['a1' => ['better', 'best', 'good']],
                'verb_hints' => ['a1' => 'quality, improvement'],
                'hints' => ['Конструкція "no + comparative + than" підкреслює рівність або відсутність покращення.'],
                'explanations' => [
                    'better' => 'No + comparative + than виражає відсутність різниці. Наприклад: no faster than, no cheaper than.',
                    'best' => 'Superlative не вживається в конструкції no ... than.',
                    'good' => 'Базова форма не підходить. Good → better в цій структурі.',
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'Had he studied harder, he would have achieved {a1} results.',
                'answers' => ['a1' => 'better'],
                'options' => ['a1' => ['better', 'best', 'more good']],
                'verb_hints' => ['a1' => 'improved, superior'],
                'hints' => ['В умовних реченнях comparative форми можуть описувати гіпотетичні результати.'],
                'explanations' => [
                    'better' => 'У conditional comparative виражає гіпотетичний результат. Good → better. Наприклад: would have been faster.',
                    'best' => 'Superlative не підходить для порівняння двох сценаріїв.',
                    'more good' => 'Good не вживається з more. Правильно: better.',
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'The situation is becoming increasingly {a1}.',
                'answers' => ['a1' => 'more complex'],
                'options' => ['a1' => ['more complex', 'complexer', 'most complex']],
                'verb_hints' => ['a1' => 'complicated, difficult'],
                'hints' => ['Прислівники типу "increasingly" підсилюють comparative форму.'],
                'explanations' => [
                    'more complex' => 'Increasingly + comparative підсилює зміну. Наприклад: increasingly more difficult.',
                    'complexer' => 'Довгі прикметники не додають -er. Правильно: more complex.',
                    'most complex' => 'Superlative не підходить для прогресивної зміни.',
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'The painting was far {a1} than I had anticipated.',
                'answers' => ['a1' => 'more valuable'],
                'options' => ['a1' => ['more valuable', 'valuabler', 'most valuable']],
                'verb_hints' => ['a1' => 'worth, price'],
                'hints' => ['Підсилювачі типу "far" посилюють comparative форму.'],
                'explanations' => [
                    'more valuable' => 'Far + comparative підсилює різницю. Наприклад: far more expensive, far better.',
                    'valuabler' => 'Довгі прикметники не додають -er. Правильно: more valuable.',
                    'most valuable' => 'Superlative не підходить для порівняння з очікуваннями.',
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'Is this the {a1} solution we can find?',
                'answers' => ['a1' => 'least expensive'],
                'options' => ['a1' => ['least expensive', 'less expensive', 'expensivest']],
                'verb_hints' => ['a1' => 'cheapest, affordable'],
                'hints' => ['Використовуйте "the least + прикметник" для мінімального ступеня.'],
                'explanations' => [
                    'least expensive' => 'The least + adjective виражає найнижчий ступінь. Наприклад: the least complicated option.',
                    'less expensive' => 'Less + adjective порівнює два, а не виражає найнижчий ступінь.',
                    'expensivest' => 'Довгі прикметники не додають -est. Правильно: the least expensive.',
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'The athlete ran {a1} in the final race than in the qualifiers.',
                'answers' => ['a1' => 'faster'],
                'options' => ['a1' => ['faster', 'fastest', 'more fast']],
                'verb_hints' => ['a1' => 'speed, pace'],
                'hints' => ['Короткі прислівники типу "fast" додають -er для порівнянь.'],
                'explanations' => [
                    'faster' => 'Короткі прислівники додають -er: fast → faster. Наприклад: hard → harder.',
                    'fastest' => 'Superlative потребує the та групи.',
                    'more fast' => 'Короткі прислівники не вживаються з more. Правильно: faster.',
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'The project was completed {a1} than expected, which pleased the client.',
                'answers' => ['a1' => 'more efficiently'],
                'options' => ['a1' => ['more efficiently', 'efficientlier', 'most efficiently']],
                'verb_hints' => ['a1' => 'effective, productive'],
                'hints' => ['Прислівники на -ly використовують "more + прислівник + than" для порівнянь.'],
                'explanations' => [
                    'more efficiently' => 'Прислівники на -ly: more + adverb + than. Наприклад: more effectively than planned.',
                    'efficientlier' => 'Прислівники на -ly не додають -er. Правильно: more efficiently.',
                    'most efficiently' => 'Superlative потребує the та контексту групи.',
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'The {a1} the explanation, the easier it is to understand.',
                'answers' => ['a1' => 'simpler'],
                'options' => ['a1' => ['simpler', 'simplest', 'more simple']],
                'verb_hints' => ['a1' => 'clear, basic'],
                'hints' => ['Конструкція "the + comparative... the + comparative" показує кореляцію.'],
                'explanations' => [
                    'simpler' => 'Конструкція the + comparative... the + comparative. Наприклад: The shorter the wait, the better.',
                    'simplest' => 'Superlative не вживається в паралельній конструкції the... the.',
                    'more simple' => 'Simple може мати обидві форми, але simpler частіша в the... the.',
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'Among all the options, this one seems the {a1} feasible.',
                'answers' => ['a1' => 'least'],
                'options' => ['a1' => ['least', 'less', 'lesser']],
                'verb_hints' => ['a1' => 'minimum, smallest degree'],
                'hints' => ['Використовуйте "the least" перед прикметниками для мінімального ступеня.'],
                'explanations' => [
                    'least' => 'The least + adjective виражає найнижчий ступінь серед групи. Наприклад: the least likely outcome.',
                    'less' => 'Less порівнює два предмети, а не виражає найнижчий ступінь.',
                    'lesser' => 'Lesser вживається в інших контекстах (the lesser evil), не з feasible.',
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'This version is slightly {a1} reliable than the previous one.',
                'answers' => ['a1' => 'more'],
                'options' => ['a1' => ['more', 'most', 'much']],
                'verb_hints' => ['a1' => 'degree, extent'],
                'hints' => ['Прислівники типу "slightly" модифікують comparative форму.'],
                'explanations' => [
                    'more' => 'Slightly + more + adjective пом\'якшує порівняння. Наприклад: slightly more expensive.',
                    'most' => 'Superlative не підходить для порівняння двох версій.',
                    'much' => 'Much підсилює, а не пом\'якшує порівняння.',
                ],
            ],

            // ===== C1 Level (12 questions): Advanced vocabulary, complex sentences, subtle meanings =====
            [
                'level' => 'C1',
                'question' => 'The implications of this decision are far {a1} than we initially assumed.',
                'answers' => ['a1' => 'more significant'],
                'options' => ['a1' => ['more significant', 'significanter', 'most significant']],
                'verb_hints' => ['a1' => 'important, meaningful'],
                'hints' => ['Far + comparative підсилює ступінь різниці.'],
                'explanations' => [
                    'more significant' => 'Far + more + adjective підсилює різницю. Наприклад: far more consequential than expected.',
                    'significanter' => 'Довгі прикметники не додають -er. Правильно: more significant.',
                    'most significant' => 'Superlative не підходить для порівняння з припущеннями.',
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'Had the circumstances been {a1}, the outcome might have differed.',
                'answers' => ['a1' => 'more favorable'],
                'options' => ['a1' => ['more favorable', 'favorabler', 'most favorable']],
                'verb_hints' => ['a1' => 'positive, advantageous'],
                'hints' => ['В умовних структурах comparative форми описують гіпотетичні стани.'],
                'explanations' => [
                    'more favorable' => 'У conditional comparative описує гіпотетичний стан. Наприклад: had it been simpler.',
                    'favorabler' => 'Довгі прикметники не додають -er. Правильно: more favorable.',
                    'most favorable' => 'Superlative не підходить для умовного порівняння.',
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'The research findings are considerably {a1} robust than previous studies suggested.',
                'answers' => ['a1' => 'more'],
                'options' => ['a1' => ['more', 'most', 'much']],
                'verb_hints' => ['a1' => 'degree, extent'],
                'hints' => ['Підсилювачі типу "considerably" посилюють comparative.'],
                'explanations' => [
                    'more' => 'Considerably + more + adjective підсилює порівняння. Наприклад: considerably more reliable.',
                    'most' => 'Superlative не підходить для порівняння з попередніми дослідженнями.',
                    'much' => 'Much + adjective не утворює правильну comparative структуру.',
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'The {a1} one examines the data, the {a2} the pattern becomes.',
                'answers' => ['a1' => 'more closely', 'a2' => 'clearer'],
                'options' => [
                    'a1' => ['more closely', 'closer', 'closest'],
                    'a2' => ['clearer', 'more clear', 'clearest'],
                ],
                'verb_hints' => ['a1' => 'carefully, attentively', 'a2' => 'obvious, evident'],
                'hints' => ['Конструкція "the more... the more" використовує comparative форми в обох частинах.'],
                'explanations' => [
                    'more closely' => 'Прислівник closely: more closely. Наприклад: the more carefully you read.',
                    'closer' => 'Closer — прикметник, а не прислівник дії examines.',
                    'closest' => 'Superlative не вживається в конструкції the more... the more.',
                    'clearer' => 'Clear → clearer у другій частині паралельної конструкції.',
                    'more clear' => 'Clear може мати обидві форми, але clearer частіша.',
                    'clearest' => 'Superlative не вживається в конструкції the... the.',
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'This methodology is no {a1} accurate than the conventional approach.',
                'answers' => ['a1' => 'more'],
                'options' => ['a1' => ['more', 'most', 'much']],
                'verb_hints' => ['a1' => 'degree, extent'],
                'hints' => ['No + comparative + than підкреслює відсутність переваги.'],
                'explanations' => [
                    'more' => 'No + more + adjective + than виражає відсутність переваги. Наприклад: no more effective than.',
                    'most' => 'Superlative не вживається в конструкції no ... than.',
                    'much' => 'Much не утворює правильну no ... than структуру.',
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'The negotiation proceeded {a1} than anyone had anticipated.',
                'answers' => ['a1' => 'more smoothly'],
                'options' => ['a1' => ['more smoothly', 'smoothlier', 'most smoothly']],
                'verb_hints' => ['a1' => 'easily, without problems'],
                'hints' => ['Прислівники на -ly використовують "more + прислівник" для порівнянь.'],
                'explanations' => [
                    'more smoothly' => 'Прислівники на -ly: more + adverb + than. Наприклад: more efficiently than expected.',
                    'smoothlier' => 'Прислівники на -ly не додають -er. Правильно: more smoothly.',
                    'most smoothly' => 'Superlative потребує the та контексту групи.',
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'Of all the proposals submitted, this one is by far the {a1}.',
                'answers' => ['a1' => 'most compelling'],
                'options' => ['a1' => ['most compelling', 'more compelling', 'compellingest']],
                'verb_hints' => ['a1' => 'convincing, persuasive'],
                'hints' => ['By far + superlative підкреслює значну перевагу.'],
                'explanations' => [
                    'most compelling' => 'By far the + most + adjective підсилює superlative. Наприклад: by far the most innovative.',
                    'more compelling' => 'Comparative порівнює два, а не виражає найвищий ступінь.',
                    'compellingest' => 'Довгі прикметники не додають -est. Правильно: the most compelling.',
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'The {a1} intricate the design, the {a2} demanding the implementation.',
                'answers' => ['a1' => 'more', 'a2' => 'more'],
                'options' => [
                    'a1' => ['more', 'most', 'much'],
                    'a2' => ['more', 'most', 'much'],
                ],
                'verb_hints' => ['a1' => 'complex, detailed', 'a2' => 'challenging, difficult'],
                'hints' => ['Конструкція "the more... the more" вимагає comparative в обох частинах.'],
                'explanations' => [
                    'more' => 'Конструкція the + more + adjective... the + more + adjective. Наприклад: the more complex, the more costly.',
                    'most' => 'Superlative не вживається в паралельній конструкції the... the.',
                    'much' => 'Much не утворює правильну паралельну структуру.',
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'The analysis reveals that the correlation is significantly {a1} pronounced than previously documented.',
                'answers' => ['a1' => 'more'],
                'options' => ['a1' => ['more', 'most', 'much']],
                'verb_hints' => ['a1' => 'degree, extent'],
                'hints' => ['Significantly + comparative підсилює порівняння.'],
                'explanations' => [
                    'more' => 'Significantly + more + adjective підсилює порівняння. Наприклад: significantly more evident.',
                    'most' => 'Superlative не підходить для порівняння з попередньою документацією.',
                    'much' => 'Much + adjective не утворює правильну comparative структуру.',
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'Were the resources {a1} abundant, the project could be completed sooner.',
                'answers' => ['a1' => 'more'],
                'options' => ['a1' => ['more', 'most', 'much']],
                'verb_hints' => ['a1' => 'degree, quantity'],
                'hints' => ['В умовній інверсії comparative описує гіпотетичні умови.'],
                'explanations' => [
                    'more' => 'Were + subject + more + adjective — умовна інверсія. Наприклад: Were it more accessible.',
                    'most' => 'Superlative не підходить для умовного речення.',
                    'much' => 'Much не утворює правильну conditional структуру.',
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'This approach is marginally {a1} effective than the alternative.',
                'answers' => ['a1' => 'more'],
                'options' => ['a1' => ['more', 'most', 'much']],
                'verb_hints' => ['a1' => 'degree, extent'],
                'hints' => ['Marginally + comparative вказує на невелику різницю.'],
                'explanations' => [
                    'more' => 'Marginally + more + adjective вказує на малу різницю. Наприклад: marginally more successful.',
                    'most' => 'Superlative не підходить для порівняння двох підходів.',
                    'much' => 'Much підсилює, а не пом\'якшує порівняння.',
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'The evidence suggests that the earlier hypothesis was no {a1} valid than the current theory.',
                'answers' => ['a1' => 'more'],
                'options' => ['a1' => ['more', 'most', 'less']],
                'verb_hints' => ['a1' => 'degree, extent'],
                'hints' => ['No + comparative + than підкреслює рівну валідність.'],
                'explanations' => [
                    'more' => 'No + more + adjective + than виражає рівність. Наприклад: no more reliable than.',
                    'most' => 'Superlative не вживається в конструкції no ... than.',
                    'less' => 'Less змінює значення речення на протилежне.',
                ],
            ],
        ];
    }

    private function flattenOptions(array $optionSets): array
    {
        $values = [];
        foreach ($optionSets as $options) {
            foreach ($options as $option) {
                $values[] = (string) $option;
            }
        }

        return array_values(array_unique($values));
    }

    private function buildOptionMarkers(array $optionSets): array
    {
        $markers = [];
        foreach ($optionSets as $marker => $options) {
            foreach ($options as $option) {
                $markers[(string) $option] = $marker;
            }
        }

        return $markers;
    }
}