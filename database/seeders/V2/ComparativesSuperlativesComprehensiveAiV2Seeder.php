<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class ComparativesSuperlativesComprehensiveAiV2Seeder extends QuestionSeeder
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
        $categoryId = Category::firstOrCreate(['name' => 'Comparatives & Superlatives Comprehensive AI'])->id;
        $sourceIds = [
            'exercise1' => Source::firstOrCreate(['name' => 'Custom: Comparatives & Superlatives Comprehensive AI (Exercise 1)'])->id,
            'exercise2' => Source::firstOrCreate(['name' => 'Custom: Comparatives & Superlatives Comprehensive AI (Exercise 2)'])->id,
            'exercise3' => Source::firstOrCreate(['name' => 'Custom: Comparatives & Superlatives Comprehensive AI (Exercise 3)'])->id,
        ];

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Comparatives and Superlatives AI Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Degrees of Comparison AI Focus'],
            ['category' => 'English Grammar Detail']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'Comparative / Superlative Choice'],
            ['category' => 'English Grammar Structure']
        )->id;

        $patternTags = [
            Tag::firstOrCreate(['name' => 'Comparative + than Pattern'], ['category' => 'English Grammar Pattern'])->id,
            Tag::firstOrCreate(['name' => 'As ... as Equality'], ['category' => 'English Grammar Pattern'])->id,
            Tag::firstOrCreate(['name' => 'Superlative Formation (-est / most / least)'], ['category' => 'English Grammar Pattern'])->id,
        ];

        $focusTags = [
            Tag::firstOrCreate(['name' => 'Irregular Comparative Forms (good/bad/far)'], ['category' => 'English Grammar Focus'])->id,
            Tag::firstOrCreate(['name' => 'Quantity Comparisons (much/many/less/fewer)'], ['category' => 'English Grammar Focus'])->id,
        ];

        $questions = $this->questionEntries();

        $tagIds = array_merge([$themeTagId, $detailTagId, $structureTagId], $patternTags, $focusTags);

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
                'tag_ids' => $tagIds,
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
                        'that' => 'Сполучник that не входить до формули comparative + than.',
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
                        'more slowly' => 'Прислівники ступенюються моделлю more/less + adverb + than.',
                        'slowlier' => 'Комбінація -lier не є стандартним порівнянням для прислівників на -ly.',
                        'most slowly' => 'The most slowly — суперлатив, не comparative між двома діями.',
                        'a bit more slowly' => 'Можливий підсилювач a bit перед comparative: a bit more slowly.',
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
                        "Your car isn't as cheaper as ours.",
                        'Your car is no cheaper than ours.',
                        'Your car is less cheap ours.',
                    ],
                ],
                'verb_hints' => [],
                'hints' => ['Порівняння рівності з cheaper than та заперечення no/any + comparative виражають однакову думку.'],
                'explanations' => [
                    'a1' => [
                        "Your car isn't any cheaper than ours." => 'Заперечення any + comparative виражає відсутність різниці у ціні.',
                        "Your car isn't as cheaper as ours." => 'As ... as вимагає базової форми прикметника: as cheap as.',
                        'Your car is no cheaper than ours.' => 'No + comparative також означає відсутність різниці: no cheaper than = not cheaper than.',
                        'Your car is less cheap ours.' => 'Less cheap without than некоректно оформлює порівняння.',
                    ],
                ],
                'level' => 'B1',
                'source' => 'exercise2',
            ],
            [
                'question' => 'The economy is getting {a1}.',
                'answers' => ['a1' => 'worse and worse'],
                'options' => [
                    'a1' => ['worse and worse', 'more and more worse', 'every day badder', 'increasingly worse'],
                ],
                'verb_hints' => [],
                'hints' => ['Повтор comparative + and + comparative (worse and worse) показує поступове погіршення.'],
                'explanations' => [
                    'a1' => [
                        'worse and worse' => 'Стала конструкція comparative + and + comparative позначає зростання ступеня.',
                        'more and more worse' => 'Подвійне ступенювання more + worse граматично хибне.',
                        'every day badder' => 'Badder не використовується; правильний comparative — worse.',
                        'increasingly worse' => 'Хоч і можливо, але не передає шаблон "ставати все гіршим" як worse and worse.',
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
                        'Tom is as intelligent as David.',
                    ],
                ],
                'verb_hints' => [],
                'hints' => ['Порівняння more ... than та негативна форма not as ... as можуть передавати однакову ідею нерівності.'],
                'explanations' => [
                    'a1' => [
                        'Tom is more intelligent than David.' => 'Comparative more + adjective + than виражає, що Том розумніший.',
                        'David is more intelligent than Tom.' => 'Це протилежне твердження, змінює суб’єкт порівняння.',
                        "David isn't as intelligent as Tom." => 'Not as + adjective + as підтверджує, що Девід поступається Тому.',
                        'Tom is as intelligent as David.' => 'As ... as без not означає рівність, а не перевагу.',
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
                'hints' => ['Структура as + adverb + as показує максимальну можливу міру дії.'],
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
                    'a1' => ['me', 'I do', 'I have', 'myself'],
                ],
                'verb_hints' => [],
                'hints' => ['Після than можна ставити об’єктний займенник (me) або скорочене повторення дієслова (I do).'],
                'explanations' => [
                    'a1' => [
                        'me' => 'У формулі comparative + than + object pronoun після than вживається об’єктний займенник.',
                        'I do' => 'Коротка відповідь після than може повторювати допоміжне дієслово: than I do.',
                        'I have' => 'Повтор дієслова have створює важку структуру й не вписується у звичний патерн than + auxiliary.',
                        'myself' => 'Reflexive займенник не є типовим після than для порівнянь.',
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
                        'during' => 'During my life не поєднується з одноразоою подією the best day.',
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
                'hints' => ['Короткі прикметники додають -er у comparative: near → nearer than ...'],
                'explanations' => [
                    'a1' => [
                        'nearer' => 'Правильний comparative для near — nearer + than.',
                        'more near' => 'Короткі прикметники не використовують more для comparative.',
                        'nearest' => 'Nearest — суперлатив (the nearest), а не порівняння двох предметів.',
                        'far nearer' => 'Підсилювач far можливий перед comparative: far nearer than ...',
                    ],
                ],
                'level' => 'B1',
                'source' => 'exercise3',
            ],
            [
                'question' => "The test wasn't as {a1} as I thought. (difficult)",
                'answers' => ['a1' => 'difficult'],
                'options' => [
                    'a1' => ['difficult', 'more difficult', 'most difficult', 'quite difficult'],
                ],
                'verb_hints' => ['a1' => 'difficult'],
                'hints' => ['Not as + adjective + as використовує базову форму прикметника.'],
                'explanations' => [
                    'a1' => [
                        'difficult' => 'У структурі not as ... as після as ставимо базову форму adjective, не comparative.',
                        'more difficult' => 'Comparative more + adjective + than не підходить після not as ... as.',
                        'most difficult' => 'Most difficult формує суперлатив, а не порівняння рівності.',
                        'quite difficult' => 'Хоч можливий описовий варіант, але конструкція not as ... as вимагає базової форми без quite.',
                    ],
                ],
                'level' => 'B1',
                'source' => 'exercise3',
            ],
            [
                'question' => "This is the {a1} place I've ever travelled to. (far)",
                'answers' => ['a1' => 'farthest'],
                'options' => [
                    'a1' => ['farthest', 'farther', 'more far', 'the most far'],
                ],
                'verb_hints' => ['a1' => 'far'],
                'hints' => ['Суперлатив для far має форму farthest / furthest з the.'],
                'explanations' => [
                    'a1' => [
                        'farthest' => 'У суперлативі вживаємо the farthest / furthest при досвіді ever + present perfect.',
                        'farther' => 'Farther — comparative, не суперлатив.',
                        'more far' => 'More far не використовується, бо far має власні ступені.',
                        'the most far' => 'The most far — неприродна форма; стандарт — the farthest/furthest.',
                    ],
                ],
                'level' => 'B1',
                'source' => 'exercise3',
            ],
            [
                'question' => 'You look much {a1} than the last time I saw you. (thin)',
                'answers' => ['a1' => 'thinner'],
                'options' => [
                    'a1' => ['thinner', 'more thin', 'thinnest', 'far thinner'],
                ],
                'verb_hints' => ['a1' => 'thin'],
                'hints' => ['Односкладові прикметники → adjective + -er для comparative.'],
                'explanations' => [
                    'a1' => [
                        'thinner' => 'Thin → thinner за рахунок подвоєння n у comparative.',
                        'more thin' => 'Для коротких прикметників more не застосовується.',
                        'thinnest' => 'Thinnest — суперлатив, не comparative.',
                        'far thinner' => 'Підсилювач far вживається перед comparative: far thinner than ...',
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
                'hints' => ['Good має нерегулярний comparative: better than ...'],
                'explanations' => [
                    'a1' => [
                        'better' => 'Неправильний comparative для good — better.',
                        'more good' => 'More good не використовується, бо good має власну форму better.',
                        'gooder' => 'Gooder — розмовний жарт, не нормативна форма.',
                        'much better' => 'Підсилювач much перед comparative: much better than ...',
                    ],
                ],
                'level' => 'B1',
                'source' => 'exercise3',
            ],
            [
                'question' => 'September is the {a1} month of the year for us. (busy)',
                'answers' => ['a1' => 'busiest'],
                'options' => [
                    'a1' => ['busiest', 'more busy', 'busyest', 'the most busy'],
                ],
                'verb_hints' => ['a1' => 'busy'],
                'hints' => ['Суперлатив коротких прикметників: the + adjective + -est (busy → busiest).'],
                'explanations' => [
                    'a1' => [
                        'busiest' => 'Busy → busiest (y → i) у формулі the + superlative + noun.',
                        'more busy' => 'Для busy суперлатив не утворюється через the most у стандартних випадках.',
                        'busyest' => 'Орфографічна помилка: потрібно busiest.',
                        'the most busy' => 'Можливий, але менш вживаний варіант; класична форма — busiest.',
                    ],
                ],
                'level' => 'B1',
                'source' => 'exercise3',
            ],
            [
                'question' => "There are {a1} people today because it's been raining a lot. (few)",
                'answers' => ['a1' => 'fewer'],
                'options' => [
                    'a1' => ['fewer', 'less', 'fewest', 'far fewer'],
                ],
                'verb_hints' => ['a1' => 'few'],
                'hints' => ['Для злічуваних іменників: comparative fewer (not less) + plural noun.'],
                'explanations' => [
                    'a1' => [
                        'fewer' => 'Few → fewer для злічуваних: fewer people than ...',
                        'less' => 'Less використовується з незлічуваними (less money), не з people.',
                        'fewest' => 'Fewest — суперлатив, не comparative.',
                        'far fewer' => 'Підсилювач far перед comparative: far fewer people than ...',
                    ],
                ],
                'level' => 'B1',
                'source' => 'exercise3',
            ],
            [
                'question' => 'The {a1} part of the exam was the listening. (tricky)',
                'answers' => ['a1' => 'trickiest'],
                'options' => [
                    'a1' => ['trickiest', 'more tricky', 'trickier', 'the most tricky'],
                ],
                'verb_hints' => ['a1' => 'tricky'],
                'hints' => ['Superlative для tricky: the + adjective + -est (trickiest).'],
                'explanations' => [
                    'a1' => [
                        'trickiest' => 'Tricky → trickiest (y → i) у суперлативі.',
                        'more tricky' => 'More tricky — comparative, не superlative.',
                        'trickier' => 'Trickier — comparative, а в реченні потрібен superlative.',
                        'the most tricky' => 'Можливий, але стандартна форма — trickiest у суперлативі.',
                    ],
                ],
                'level' => 'B1',
                'source' => 'exercise3',
            ],
        ];
    }

    private function flattenOptions(array $optionSets): array
    {
        $options = [];

        foreach ($optionSets as $marker => $optionList) {
            foreach ($optionList as $value) {
                $options[] = [
                    'marker' => $marker,
                    'value' => $value,
                    'type' => 'text',
                ];
            }
        }

        return $options;
    }
}

