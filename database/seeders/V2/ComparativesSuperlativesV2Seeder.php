<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class ComparativesSuperlativesV2Seeder extends QuestionSeeder
{
    private array $levelDifficulty = [
        'A1' => 1,
        'A2' => 2,
        'B1' => 3,
        'B2' => 4,
        'C1' => 5,
        'C2' => 5,
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Adjectives and Adverbs'])->id;
        $sourceIds = [
            'exercise1' => Source::firstOrCreate(['name' => 'Custom: Comparatives & Superlatives V2 (Exercise 1)'])->id,
            'exercise2' => Source::firstOrCreate(['name' => 'Custom: Comparatives & Superlatives V2 (Exercise 2)'])->id,
            'exercise3' => Source::firstOrCreate(['name' => 'Custom: Comparatives & Superlatives V2 (Exercise 3)'])->id,
        ];

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Comparatives and Superlatives Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Degrees of Comparison'],
            ['category' => 'English Grammar Detail']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'Comparative / Superlative Choice'],
            ['category' => 'English Grammar Structure']
        )->id;

        $questions = $this->questionEntries();

        $items = [];
        $meta = [];

        foreach ($questions as $index => $entry) {
            $answers = [];
            foreach ($entry['answers'] as $marker => $answer) {
                $answers[] = [
                    'marker' => $marker,
                    'answer' => $answer,
                    'verb_hint' => $entry['verb_hints'][$marker] ?? null,
                ];
            }

            $options = $this->flattenOptions($entry['options']);

            $uuid = $this->generateQuestionUuid($index + 1, $entry['question']);

            $items[] = [
                'uuid' => $uuid,
                'question' => $entry['question'],
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$entry['level']] ?? 3,
                'source_id' => $sourceIds[$entry['source']] ?? reset($sourceIds),
                'flag' => 0,
                'type' => 0,
                'level' => $entry['level'],
                'tag_ids' => [$themeTagId, $detailTagId, $structureTagId],
                'answers' => $answers,
                'options' => $options,
                'variants' => [$entry['question']],
            ];

            $meta[] = [
                'uuid' => $uuid,
                'answers' => $entry['answers'],
                'hints' => $entry['hints'] ?? [],
                'explanations' => $entry['explanations'] ?? [],
            ];
        }

        $this->seedQuestionData($items, []);
        $this->attachHintsAndExplanations($meta);
    }

    private function attachHintsAndExplanations(array $meta): void
    {
        foreach ($meta as $data) {
            $question = Question::where('uuid', $data['uuid'])->first();

            if (! $question) {
                continue;
            }

            $hintText = $this->formatHints($data['hints'] ?? []);
            if ($hintText !== null) {
                QuestionHint::updateOrCreate(
                    ['question_id' => $question->id, 'provider' => 'chatgpt', 'locale' => 'uk'],
                    ['hint' => $hintText]
                );
            }

            $answers = $data['answers'] ?? [];
            foreach ($data['explanations'] ?? [] as $marker => $options) {
                if (! isset($answers[$marker])) {
                    $fallback = reset($answers);
                    $answers[$marker] = is_string($fallback) ? $fallback : (string) $fallback;
                }

                $correct = $answers[$marker];
                if (! is_string($correct)) {
                    $correct = (string) $correct;
                }

                foreach ($options as $option => $text) {
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
    }

    private function questionEntries(): array
    {
        return [
            [
                'question' => "Hi Suzan, I'm having a wonderful time in Los Angeles. The weather is {a1} and drier {a2} in England and Americans are friendlier than {a3}. The food is better here; it's not the same {a4} the food in England at all! From all the countries I've been to, I think English food is the {a5} pleasant. It's awful. Yesterday I had the best hamburger I’ve {a6} eaten! The hotel is beautiful. I think it’s not as {a7} the hotel we stayed in New York, but it’s {a8} comfortable. They say that Los Angeles is one of the most expensive cities {a9} the world, but actually, I'm not spending {a10} money as I thought. I’ll phone you when I get back. Love, Megan.",
                'answers' => [
                    'a1' => 'hotter',
                    'a2' => 'than',
                    'a3' => 'us',
                    'a4' => 'as',
                    'a5' => 'least',
                    'a6' => 'ever',
                    'a7' => 'nice as',
                    'a8' => 'much more',
                    'a9' => 'in',
                    'a10' => 'as much',
                ],
                'options' => [
                    'a1' => ['hotter', 'hottest', 'hoter'],
                    'a2' => ['than', 'that', 'as'],
                    'a3' => ['us', 'our', 'we'],
                    'a4' => ['as', 'that', 'than'],
                    'a5' => ['least', 'more', 'less'],
                    'a6' => ['ever', 'never', 'before'],
                    'a7' => ['nice as', 'nicer as', 'nicer than', 'as nice as'],
                    'a8' => ['much more', 'lot more', 'most', 'far more'],
                    'a9' => ['in', 'of', 'from'],
                    'a10' => ['as much', 'as many', 'more'],
                ],
                'verb_hints' => [],
                'hints' => [
                    'Формула comparative: **adjective/adverb + -er/more + than** (hot → hotter than; dry → drier than).',
                    'Формула рівності: **not (the) same + as** та **not as + adjective + as** для заперечення рівності.',
                    'Формула суперлатива: **the + most/least + adjective** для найвищого або найнижчого ступеня якості.',
                ],
                'explanations' => [
                    'a1' => [
                        'hotter' => 'Comparative утворюється за схемою base adjective + -er + than, тож hot → hotter + than.',
                        'hottest' => 'Superlative потребує формули the + adjective-est або the most + adjective, а не порівняння з than.',
                        'hoter' => 'При утворенні hotter подвоюємо кінцеву приголосну перед -er: hot → hotter.',
                    ],
                    'a2' => [
                        'than' => 'Після comparative використовується сполучник than: comparative + than + об’єкт.',
                        'that' => 'Spолучник that не входить до формули comparative + than.',
                        'as' => 'As вживається у структурі as + adjective + as для рівних порівнянь, не після comparative.',
                    ],
                    'a3' => [
                        'us' => 'Після than у порівняннях ставимо об’єктний відмінок (me/us/them).',
                        'our' => 'Присвійний займенник не відповідає формулі than + object pronoun.',
                        'we' => 'Subjektний займенник без допоміжного дієслова не підходить у схемі comparative + than + object.',
                    ],
                    'a4' => [
                        'as' => 'Порівняння рівності оформлюють через структуру (not) the same as / as + adjective + as.',
                        'that' => 'Same + that не утворює стандартної конструкції подібності.',
                        'than' => 'Than входить у формулу comparative + than, а не the same as.',
                    ],
                    'a5' => [
                        'least' => 'Суперлатив нижчого ступеня має структуру the + least + adjective.',
                        'more' => 'More створює comparative (more + adjective + than), що не передає найвищий/найнижчий ступінь.',
                        'less' => 'Less — звичайний comparative без the; для найнижчого ступеня потрібна формула the least + adjective.',
                    ],
                    'a6' => [
                        'ever' => 'Колокація the + superlative + ever підкреслює найкращий/найгірший досвід у житті.',
                        'never' => 'Never змінює значення конструкції ever + present perfect, що описує досвід.',
                        'before' => 'Before без ever не дає формули суперлативного досвіду (the best ... ever).',
                    ],
                    'a7' => [
                        'nice as' => 'Структура not as + adjective + as використовує базову форму прикметника між двома as.',
                        'nicer as' => 'Comparative з as порушує формулу; після comparative стоїть than.',
                        'nicer than' => 'Формула not as ... as не потребує comparative, лише базову форму adjective.',
                        'as nice as' => 'Повна модель as + adjective + as показує рівність, коли обидва елементи as присутні.',
                    ],
                    'a8' => [
                        'much more' => 'Посилений comparative: intensifier (much) + more + adjective.',
                        'lot more' => 'Поширена розмовна модель — a lot more + adjective; без артикля порушує формулу.',
                        'most' => 'Most + adjective формує суперлатив (the most ...), а не порівняння з than/as.',
                        'far more' => 'Інший підсилювач у схемі far + more + adjective.',
                    ],
                    'a9' => [
                        'in' => 'Фраза one of the + superlative + plural noun + in the world використовує прийменник in.',
                        'of' => 'Прийменник of у цій позиції не входить до стандартної формули one of the most + noun + in the world.',
                        'from' => 'From позначає походження і не поєднується з суперлативною рамкою one of the most ... in the world.',
                    ],
                    'a10' => [
                        'as much' => 'Структура кількості для незлічуваних: as much + uncountable noun + as.',
                        'as many' => 'Формула as many + countable noun + as застосовується лише до злічуваних іменників.',
                        'more' => 'Comparative кількості more + noun + than не відповідає моделі as much ... as.',
                    ],
                ],
                'level' => 'B1',
                'source' => 'exercise1',
            ],
            [
                'question' => 'This tea tastes a bit {a1} the other.',
                'answers' => ['a1' => 'more bitter than'],
                'options' => [
                    'a1' => ['more bitter than', 'bitterest than', 'more bitter that', 'far more bitter than'],
                ],
                'verb_hints' => [],
                'hints' => ['Формула порівняння двох предметів: comparative (more + adjective) + than + об’єкт.'],
                'explanations' => [
                    'a1' => [
                        'more bitter than' => 'Мультискладові прикметники утворюють comparative за схемою more + adjective + than.',
                        'bitterest than' => 'Суперлатив adjective-est використовується з the та без than.',
                        'more bitter that' => 'Сполучник у формулі comparative — than, не that.',
                        'far more bitter than' => 'Підсилювач far може стояти перед more: far + more + adjective + than.',
                    ],
                ],
                'level' => 'B1',
                'source' => 'exercise2',
            ],
            [
                'question' => 'She seems {a1} since she got divorced.',
                'answers' => ['a1' => 'happier'],
                'options' => [
                    'a1' => ['happier', 'more happy', 'more happier', 'far happier'],
                ],
                'verb_hints' => [],
                'hints' => ['Правило: односкладові та деякі двоскладові прикметники → base adjective + -er + than.'],
                'explanations' => [
                    'a1' => [
                        'happier' => 'Слова на -y змінюють -y → -ier у порівняльній формі: happy → happier.',
                        'more happy' => 'Для коротких прикметників застосовується суфікс -er, а не конструкція more + adjective.',
                        'more happier' => 'Подвійний comparative (more + -er) суперечить формулі ступенювання.',
                        'far happier' => 'Підсилювач far додається перед готовим comparative: far + comparative + than.',
                    ],
                ],
                'level' => 'B1',
                'source' => 'exercise2',
            ],
            [
                'question' => "Could you speak {a1}, please? I don't understand you.",
                'answers' => ['a1' => 'more slowly'],
                'options' => [
                    'a1' => ['more slowly', 'slowlier', 'most slowly', 'a bit more slowly'],
                ],
                'verb_hints' => [],
                'hints' => ['Формула для прислівників на -ly: more/less + adverb + than для порівняння швидкості чи інтенсивності.'],
                'explanations' => [
                    'a1' => [
                        'more slowly' => 'Прислівники утворюють comparative через more + adverb: more slowly + than ...',
                        'slowlier' => 'Додавання -er до прислівників на -ly не відповідає правилу ступенювання.',
                        'most slowly' => 'Most + adverb формує суперлатив, а не порівняльну форму.',
                        'a bit more slowly' => 'Підсилювачі (a bit, slightly) можуть ставитися перед more + adverb.',
                    ],
                ],
                'level' => 'B1',
                'source' => 'exercise2',
            ],
            [
                'question' => 'Choose the two correct sentences.',
                'answers' => [
                    'a1' => "Your car isn't any cheaper than ours.",
                    'a2' => 'Your car is no cheaper than ours.',
                ],
                'options' => [
                    'a1' => [
                        "Your car isn't any cheaper than ours.",
                        'Your car is no cheaper than ours.',
                        "Your car isn't as cheaper as ours.",
                    ],
                ],
                'verb_hints' => [],
                'hints' => ['Шаблон відсутності різниці: any/no + comparative + than, без конструкції as + comparative.',
                    'Речення з двома правильними варіантами відображають однаковий зміст через різні підсилювачі (any/no).',
                ],
                'explanations' => [
                    'a1' => [
                        "Your car isn't any cheaper than ours." => 'Модель any + comparative + than вказує, що різниці фактично немає.',
                        'Your car is no cheaper than ours.' => 'No + comparative + than означає рівність за ознакою (ціна однакова).',
                        "Your car isn't as cheaper as ours." => 'As ... as потребує базової форми прикметника: as + adjective + as.',
                    ],
                ],
                'level' => 'B1',
                'source' => 'exercise2',
            ],
            [
                'question' => 'The economy is getting {a1}.',
                'answers' => ['a1' => 'worse and worse'],
                'options' => [
                    'a1' => ['worse and worse', 'more and more worse', 'every day badder', 'steadily worse'],
                ],
                'verb_hints' => [],
                'hints' => ['Структура поступової зміни: comparative and comparative (worse and worse) для позначення тренду.'],
                'explanations' => [
                    'a1' => [
                        'worse and worse' => 'Сталий зразок comparative and comparative підкреслює поступове погіршення.',
                        'more and more worse' => 'Комбінація more + worse порушує правило: irregular adjective bad → worse (без more).',
                        'every day badder' => 'Bad має неправильний comparative worse; форма badder не використовується.',
                        'steadily worse' => 'Інший спосіб передати тренд: steadily + comparative.',
                    ],
                ],
                'level' => 'B1',
                'source' => 'exercise2',
            ],
            [
                'question' => 'Which two sentences mean the same?',
                'answers' => [
                    'a1' => 'Tom is more intelligent than David.',
                    'a2' => "David isn't as intelligent as Tom.",
                ],
                'options' => [
                    'a1' => [
                        'Tom is more intelligent than David.',
                        'David is more intelligent than Tom.',
                        "David isn't as intelligent as Tom.",
                    ],
                ],
                'verb_hints' => [],
                'hints' => ['Заперечна конструкція not as ... as має те саме значення, що й порівняння з than у зворотному порядку.'],
                'explanations' => [
                    'a1' => [
                        'Tom is more intelligent than David.' => 'Пряма форма comparative: subject + verb + more + adjective + than + object.',
                        'David is more intelligent than Tom.' => 'Речення дзеркально змінює логіку порівняння й робить Давида розумнішим.',
                        "David isn't as intelligent as Tom." => 'Not as + adjective + as передає, що суб’єкт має нижчий показник ознаки, ніж об’єкт.',
                    ],
                ],
                'level' => 'B1',
                'source' => 'exercise2',
            ],
            [
                'question' => "I'm trying to do it {a1} I can.",
                'answers' => ['a1' => 'as fast as'],
                'options' => [
                    'a1' => ['as fast as', 'as faster as', 'faster as', 'as quickly as'],
                ],
                'verb_hints' => [],
                'hints' => ['Шаблон as + adjective/adverb + as для максимуму зусиль чи рівності.'],
                'explanations' => [
                    'a1' => [
                        'as fast as' => 'Стандартний шаблон as + adverb + as для максимальної швидкості.',
                        'as faster as' => 'Конструкція as ... as не містить comparative усередині; потрібна базова форма.',
                        'faster as' => 'Порушений порядок елементів: формула вимагає двох as по краях і базової форми між ними.',
                        'as quickly as' => 'Синонімічний варіант із quickly відповідає тій самій формулі as + adverb + as.',
                    ],
                ],
                'level' => 'B1',
                'source' => 'exercise2',
            ],
            [
                'question' => 'He has more talent than {a1}. (Choose TWO correct options.)',
                'answers' => [
                    'a1' => 'me',
                    'a2' => 'I do',
                ],
                'options' => [
                    'a1' => ['me', 'I do', 'I have'],
                ],
                'verb_hints' => [],
                'hints' => ['Після than можна ставити об’єктний займенник (me) або скорочене повторення дієслова (I do).'],
                'explanations' => [
                    'a1' => [
                        'me' => 'У формулі comparative + than + object pronoun після than вживається об’єктний займенник.',
                        'I do' => 'Коротка відповідь після than може повторювати допоміжне дієслово: than I do.',
                        'I have' => 'Повтор дієслова have створює важку структуру й не вписується у звичний патерн than + auxiliary.',
                    ],
                ],
                'level' => 'B1',
                'source' => 'exercise2',
            ],
            [
                'question' => 'He is {a1}.',
                'answers' => ['a1' => "the most boring teacher I've ever met"],
                'options' => [
                    'a1' => [
                        "the most boring teacher I've ever met",
                        "the most boring teacher I've never met",
                        "The boringest teacher I've never met",
                        'the very boringest teacher',
                    ],
                ],
                'verb_hints' => [],
                'hints' => ['Ever + present perfect підкреслює досвід; superlative: the most + adjective.'],
                'explanations' => [
                    'a1' => [
                        "the most boring teacher I've ever met" => 'Суперлатив за формулою the + most + adjective з уточненням досвіду ever + present perfect.',
                        "the most boring teacher I've never met" => 'Never met суперечить додатку про зустріч: конструкція заперечує сам факт досвіду.',
                        "The boringest teacher I've never met" => 'Форма boringest стилістично маргінальна, а never met прибирає досвід.',
                        'the very boringest teacher' => 'Без уточнення досвіду (ever met) та зі спрощеним boringest вислів виглядає нефіксованим.',
                    ],
                ],
                'level' => 'B1',
                'source' => 'exercise2',
            ],
            [
                'question' => 'It was the best day {a1} my life.',
                'answers' => ['a1' => 'of'],
                'options' => [
                    'a1' => ['of', 'in', 'than', 'during'],
                ],
                'verb_hints' => [],
                'hints' => ['Фіксована колокація: the best/worst day **of** my life.'],
                'explanations' => [
                    'a1' => [
                        'of' => 'Колокація the + superlative + noun + of my life використовує прийменник of.',
                        'in' => 'In my life можливе, але у сталому виразі the best day of my life традиційно ставлять of.',
                        'than' => 'Сполучник than належить до формули comparative + than і не підходить після superlative.',
                        'during' => 'During my life не поєднується з одноразовою подією the best day.',
                    ],
                ],
                'level' => 'B1',
                'source' => 'exercise2',
            ],
            [
                'question' => 'This exam was {a1} than the exam in May. (easy)',
                'answers' => ['a1' => 'easier'],
                'options' => [
                    'a1' => ['easier', 'more easy', 'the easiest', 'far easier'],
                ],
                'verb_hints' => ['a1' => 'easy'],
                'hints' => ['Односкладові прикметники додають -er; порівняння з than.'],
                'explanations' => [
                    'a1' => [
                        'easier' => 'Прикметники на -y переходять у -ier у comparative: easy → easier + than ...',
                        'more easy' => 'Короткі прикметники користуються суфіксом -er замість конструкції more + adjective.',
                        'the easiest' => 'Формула the + adjective-est утворює суперлатив, а не порівняння двох предметів.',
                        'far easier' => 'Підсилювач far може стояти перед comparative: far + comparative + than.',
                    ],
                ],
                'level' => 'B1',
                'source' => 'exercise3',
            ],
            [
                'question' => "You should drive {a1} or you'll have an accident. (slowly)",
                'answers' => ['a1' => 'more slowly'],
                'options' => [
                    'a1' => ['more slowly', 'slower', 'slowliest', 'a lot more slowly'],
                ],
                'verb_hints' => ['a1' => 'slowly'],
                'hints' => ['Прислівники на -ly: comparative через more + adverb.'],
                'explanations' => [
                    'a1' => [
                        'more slowly' => 'Прислівники ступенюються моделлю more/less + adverb + than.',
                        'slower' => 'Slower — форма прикметника slow; для прислівника потрібно more slowly.',
                        'slowliest' => 'Суперлатив прислівника твориться як the most slowly, а не шляхом додавання -est.',
                        'a lot more slowly' => 'A lot / much можуть підсилювати comparative: a lot more slowly than ...',
                    ],
                ],
                'level' => 'B1',
                'source' => 'exercise3',
            ],
            [
                'question' => 'My new home is {a1} to work than the old one. (near)',
                'answers' => ['a1' => 'nearer'],
                'options' => [
                    'a1' => ['nearer', 'more near', 'nearest', 'far nearer'],
                ],
                'verb_hints' => ['a1' => 'near'],
                'hints' => ['Near → nearer/farther для звичайного порівняння відстані.'],
                'explanations' => [
                    'a1' => [
                        'nearer' => 'Односкладовий near утворює comparative за правилом adjective + -er + than: near → nearer.',
                        'more near' => 'Для коротких прикметників не застосовується more + adjective.',
                        'nearest' => 'The nearest — суперлатив для вибору з групи, не для порівняння двох будинків.',
                        'far nearer' => 'Far може підсилювати ступінь: far + comparative + than.',
                    ],
                ],
                'level' => 'B1',
                'source' => 'exercise3',
            ],
            [
                'question' => "The test wasn't as {a1} as I thought. (difficult)",
                'answers' => ['a1' => 'difficult'],
                'options' => [
                    'a1' => ['difficult', 'more difficult', 'most difficult', 'so difficult'],
                ],
                'verb_hints' => ['a1' => 'difficult'],
                'hints' => ['У конструкції not as ... as використовується базова форма прикметника.'],
                'explanations' => [
                    'a1' => [
                        'difficult' => 'Шаблон not as + adjective + as використовує базову форму без суфіксів чи more/most.',
                        'more difficult' => 'Comparative + than не входить у конструкцію not as ... as.',
                        'most difficult' => 'Superlative (the most + adjective) не поєднується зі схемою not as ... as.',
                        'so difficult' => 'So + adjective зазвичай продовжується that + clause; у цій конструкції потрібен as ... as.',
                    ],
                ],
                'level' => 'B1',
                'source' => 'exercise3',
            ],
            [
                'question' => "This is the {a1} place I've ever travelled to. (far)",
                'answers' => ['a1' => 'farthest'],
                'options' => [
                    'a1' => ['farthest', 'farther', 'more far', 'most far'],
                ],
                'verb_hints' => ['a1' => 'far'],
                'hints' => ['Superlative для відстані: the farthest/the furthest (у подорожах найчастіше farthest).'],
                'explanations' => [
                    'a1' => [
                        'farthest' => 'Суперлатив від far: the + farthest/furthest + noun, особливо з ever + present perfect.',
                        'farther' => 'Farther — comparative форма (farther than ...), а не суперлатив.',
                        'more far' => 'Far має неправильні ступені (farther/farthest або further/furthest), не форму з more.',
                        'most far' => 'Стандартна модель суперлатива для far — farthest/furthest, а не most far.',
                    ],
                ],
                'level' => 'B1',
                'source' => 'exercise3',
            ],
            [
                'question' => 'You look much {a1} than the last time I saw you. (thin)',
                'answers' => ['a1' => 'thinner'],
                'options' => [
                    'a1' => ['thinner', 'more thin', 'thinnest', 'a lot thinner'],
                ],
                'verb_hints' => ['a1' => 'thin'],
                'hints' => ['Thin → thinner для звичайного порівняння; much + comparative для підсилення.'],
                'explanations' => [
                    'a1' => [
                        'thinner' => 'Thin → thinner утворюється через подвоєння n та суфікс -er у comparative.',
                        'more thin' => 'Короткі прикметники ступенюються суфіксом -er, а не конструкцією more + adjective.',
                        'thinnest' => 'The thinnest — суперлатив, що вживається для групи, а не для порівняння двох станів.',
                        'a lot thinner' => 'A lot слугує підсилювачем перед comparative: a lot + comparative + than.',
                    ],
                ],
                'level' => 'B1',
                'source' => 'exercise3',
            ],
            [
                'question' => 'My new computer is a bit {a1} than the old one. (good)',
                'answers' => ['a1' => 'better'],
                'options' => [
                    'a1' => ['better', 'more good', 'gooder', 'much better'],
                ],
                'verb_hints' => ['a1' => 'good'],
                'hints' => ['Неправильне порівняння good → better → the best.'],
                'explanations' => [
                    'a1' => [
                        'better' => 'Неправильна пара ступенів: good → better → the best; comparative не утворюється через more.',
                        'more good' => 'Good не поєднується з more для ступенювання, використовують форму better.',
                        'gooder' => 'Для good не додається суфікс -er; існує лише irregular форма better.',
                        'much better' => 'Підсилювач much розташовується перед irregular comparative: much better than ...',
                    ],
                ],
                'level' => 'B1',
                'source' => 'exercise3',
            ],
            [
                'question' => 'September is the {a1} month of the year for us. (busy)',
                'answers' => ['a1' => 'busiest'],
                'options' => [
                    'a1' => ['busiest', 'more busy', 'busyest', 'most busy'],
                ],
                'verb_hints' => ['a1' => 'busy'],
                'hints' => ['Суперлатив для busy: the busiest. Подвоюємо приголосну y → iest.'],
                'explanations' => [
                    'a1' => [
                        'busiest' => 'Busy → busiest: y → iest у суперлативі з артиклем the.',
                        'more busy' => 'More + adjective + than створює comparative, але наявність the сигналізує суперлатив.',
                        'busyest' => 'Орфографія суперлатива: y змінюється на i перед суфіксом -est → busiest.',
                        'most busy' => 'Форма most busy менш уживана; типова формула — the busiest.',
                    ],
                ],
                'level' => 'B1',
                'source' => 'exercise3',
            ],
            [
                'question' => "There are {a1} people today because it's been raining a lot. (few)",
                'answers' => ['a1' => 'fewer'],
                'options' => [
                    'a1' => ['fewer', 'less', 'fewest', 'a lot fewer'],
                ],
                'verb_hints' => ['a1' => 'few'],
                'hints' => ['Few → fewer (зі злічуваними іменниками), fewest — суперлатив.'],
                'explanations' => [
                    'a1' => [
                        'fewer' => 'Зі злічуваними іменниками використовується comparative fewer + plural noun.',
                        'less' => 'Less типово вживається з незлічуваними; для кількості людей коректніший fewer.',
                        'fewest' => 'The fewest — суперлативна форма, а в реченні потрібно comparative.',
                        'a lot fewer' => 'A lot працює як підсилювач у моделі a lot + comparative.',
                    ],
                ],
                'level' => 'B1',
                'source' => 'exercise3',
            ],
            [
                'question' => 'The {a1} part of the exam was the listening. (tricky)',
                'answers' => ['a1' => 'trickiest'],
                'options' => [
                    'a1' => ['trickiest', 'more tricky', 'trickier', 'most tricky'],
                ],
                'verb_hints' => ['a1' => 'tricky'],
                'hints' => ['Суперлатив із the: the trickiest. Comparative: trickier.'],
                'explanations' => [
                    'a1' => [
                        'trickiest' => 'The + adjective-est формує суперлатив: the trickiest part.',
                        'more tricky' => 'More + adjective створює comparative, що не поєднується зі статтею the у цьому реченні.',
                        'trickier' => 'Comparative (adjective + -er) не використовується зі the для опису одного елемента з групи.',
                        'most tricky' => 'Most + adjective — альтернативний суперлатив, але стандартна форма для tricky — trickiest.',
                    ],
                ],
                'level' => 'B1',
                'source' => 'exercise3',
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
}
