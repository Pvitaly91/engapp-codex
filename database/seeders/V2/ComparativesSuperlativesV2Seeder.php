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
                    'Порівняння + than: **hotter than**, **friendlier than**, **drier than**.',
                    'Структура not the same **as** / as + прикметник + as для рівних порівнянь.',
                    'Суперлатив для найгіршого/найкращого: **the least pleasant**, **the best**.',
                ],
                'explanations' => [
                    'a1' => [
                        'hotter' => '✅ Comparative + than для порівняння двох місць.',
                        'hottest' => '❌ Superlative вживається з the, коли є група, а не порівняння з одним місцем.',
                        'hoter' => '❌ Орфографічна помилка: потрібна подвоєна t → hotter.',
                    ],
                    'a2' => [
                        'than' => '✅ Формула comparative + than: drier than in England.',
                        'that' => '❌ Після comparative потрібен than.',
                        'as' => '❌ As використовується в конструкції as ... as для рівності.',
                    ],
                    'a3' => [
                        'us' => '✅ Після than потрібен об’єктний відмінок: than us.',
                        'our' => '❌ Наш (присвійний) не підходить після than.',
                        'we' => '❌ Than we звучить формально без дієслова; у розмовній мові ставлять us.',
                    ],
                    'a4' => [
                        'as' => '✅ Not the same as — стандартна конструкція подібності/відмінності.',
                        'that' => '❌ That не поєднується з same у цьому значенні.',
                        'than' => '❌ Than використовується лише з comparative.',
                    ],
                    'a5' => [
                        'least' => '✅ The least + adjective для найменшого ступеня.',
                        'more' => '❌ More робить comparative, а потрібен суперлатив найгіршого.',
                        'less' => '❌ Less — звичайний comparative без the.',
                    ],
                    'a6' => [
                        'ever' => '✅ Best ... ever — усталена колокація для найкращого досвіду.',
                        'never' => '❌ Never порушує позитивний зміст конструкції the best ... ever.',
                        'before' => '❌ Before без ever звучить неповно.',
                    ],
                    'a7' => [
                        'nice as' => '✅ Конструкція as + adjective + as для рівного порівняння (not as nice as).',
                        'nicer as' => '❌ Після comparative потрібен than, не as.',
                        'nicer than' => '❌ У реченні є not as ... as, тож потрібна базова форма nice, не nicer.',
                        'as nice as' => 'ℹ️ Повна форма конструкції; у реченні перший as вже є перед прогалиною.',
                    ],
                    'a8' => [
                        'much more' => '✅ Much more + adjective для посилення comparative.',
                        'lot more' => '❌ Неформально можливе a lot more, але потрібен артикль a.',
                        'most' => '❌ Most створює суперлатив, а тут просте порівняння.',
                        'far more' => 'ℹ️ Можливе альтернативне підсилення, але правильна відповідь — much more.',
                    ],
                    'a9' => [
                        'in' => '✅ One of the most expensive cities in the world — фіксована колокація.',
                        'of' => '❌ Не використовується з cities у такій конструкції.',
                        'from' => '❌ From позначає походження, а не належність до групи.',
                    ],
                    'a10' => [
                        'as much' => '✅ As much money as — кількість незлічуваних іменників.',
                        'as many' => '❌ Many вживається зі злічуваними, не підходить для money.',
                        'more' => '❌ More money than — інша структура, без першого as.',
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
                'hints' => ['Формула: comparative + than для різниці між двома речами.'],
                'explanations' => [
                    'a1' => [
                        'more bitter than' => '✅ Correct comparative of multi-syllable adjective: more + adjective + than.',
                        'bitterest than' => '❌ Superlative потребує the і не поєднується з than.',
                        'more bitter that' => '❌ Після comparative вживається than, не that.',
                        'far more bitter than' => 'ℹ️ Альтернативне підсилення правильного порівняння; базова відповідь — more bitter than.',
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
                'hints' => ['Односкладові та деякі двоскладові прикметники утворюють comparative додаванням -er.'],
                'explanations' => [
                    'a1' => [
                        'happier' => '✅ Прикметник із закінченням -y → -ier у comparative.',
                        'more happy' => '❌ Для happy вживається суфікс -er, не more.',
                        'more happier' => '❌ Подвійне порівняння: more + -er не сумісні.',
                        'far happier' => 'ℹ️ Можливе підсилення правильної форми, але базова відповідь — happier.',
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
                'hints' => ['Прислівники на -ly утворюють comparative через more/less.'],
                'explanations' => [
                    'a1' => [
                        'more slowly' => '✅ Правильний comparative для прислівника slowly — more + adverb.',
                        'slowlier' => '❌ Неправильне суфіксальне утворення для прислівника.',
                        'most slowly' => '❌ Це суперлатив, потрібен comparative.',
                        'a bit more slowly' => 'ℹ️ Варіант із підсиленням, базова відповідь — more slowly.',
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
                'hints' => ['Порівняння рівності/нерівності: any/no + comparative + than, без as перед comparative.'],
                'explanations' => [
                    'a1' => [
                        "Your car isn't any cheaper than ours." => '✅ Any + comparative + than = немає жодної переваги в ціні.',
                        'Your car is no cheaper than ours.' => '✅ No + comparative + than = така сама ціна, не дешевше.',
                        "Your car isn't as cheaper as ours." => '❌ Після as не можна ставити comparative; має бути as cheap as.',
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
                'hints' => ['Подвоєний comparative (worse and worse) показує поступове погіршення.'],
                'explanations' => [
                    'a1' => [
                        'worse and worse' => '✅ Сталій вираз для поступового погіршення.',
                        'more and more worse' => '❌ Подвійний comparative more + worse некоректний.',
                        'every day badder' => '❌ Bad → worse; форма badder не вживається.',
                        'steadily worse' => 'ℹ️ Можливий варіант, але цільова конструкція — worse and worse.',
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
                        'Tom is more intelligent than David.' => '✅ Пряме порівняння: Том розумніший.',
                        'David is more intelligent than Tom.' => '❌ Протилежне твердження змінює зміст.',
                        "David isn't as intelligent as Tom." => '✅ Not as intelligent as = менш розумний, збігається зі змістом першого речення.',
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
                        'as fast as' => '✅ Стандартна конструкція as ... as для швидкості.',
                        'as faster as' => '❌ Comparative faster не вживається всередині конструкції as ... as.',
                        'faster as' => '❌ Порушений порядок: потрібно as fast as.',
                        'as quickly as' => 'ℹ️ Синонімічний варіант із quickly; прийнятна, але базова відповідь — as fast as.',
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
                        'me' => '✅ Після than найпростіше поставити об’єктний займенник.',
                        'I do' => '✅ Скорочена форма порівняння: than I do.',
                        'I have' => '❌ Повторює дієслово have, але вибивається зі стилю й звучить надто важко.',
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
                        "the most boring teacher I've ever met" => '✅ Правильний суперлатив + ever met для досвіду.',
                        "the most boring teacher I've never met" => '❌ Never метається зі значенням: якщо never met, ви його не зустрічали.',
                        "The boringest teacher I've never met" => '❌ Boringest — розмовне, але never met руйнує сенс.',
                        'the very boringest teacher' => '❌ Не вистачає частини про досвід; boringest стилістично слабке.',
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
                        'of' => '✅ The best day of my life — стандартний вираз.',
                        'in' => '❌ In my life звучить менш природно без of.',
                        'than' => '❌ Потрібен прийменник, не сполучник порівняння.',
                        'during' => '❌ During my life не поєднується з the best day.',
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
                        'easier' => '✅ Правильний comparative для easy: -y → -ier.',
                        'more easy' => '❌ Для short adjectives використовується суфікс -er, не more.',
                        'the easiest' => '❌ Це суперлатив; у реченні йдеться про порівняння двох іспитів.',
                        'far easier' => 'ℹ️ Підсилений варіант правильної форми; базова відповідь — easier.',
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
                        'more slowly' => '✅ Правильний comparative для slowly.',
                        'slower' => '❌ Slower — форма прикметника, не прислівника.',
                        'slowliest' => '❌ Неправильне утворення суперлатива прислівника.',
                        'a lot more slowly' => 'ℹ️ Підсилення правильної відповіді, але базова — more slowly.',
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
                        'nearer' => '✅ Стандартний comparative near → nearer.',
                        'more near' => '❌ Для near використовується суфікс -er, не more.',
                        'nearest' => '❌ Суперлатив, а порівнюються два будинки.',
                        'far nearer' => 'ℹ️ Підсилена форма правильної відповіді; базова — nearer.',
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
                        'difficult' => '✅ Not as + adjective + as потребує простої форми прикметника.',
                        'more difficult' => '❌ Comparative руйнує шаблон not as ... as.',
                        'most difficult' => '❌ Superlative не поєднується з not as ... as.',
                        'so difficult' => '❌ Потрібна друга частина so ... that; тут інша структура.',
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
                        'farthest' => '✅ Superlative з the ... ever travelled to — найвіддаленіше місце.',
                        'farther' => '❌ Це comparative; потрібен суперлатив.',
                        'more far' => '❌ Far утворює ступені неправильно, не з more.',
                        'most far' => '❌ Не вживається; правильний суперлатив — farthest/furthest.',
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
                        'thinner' => '✅ Правильний comparative: thin → thinner.',
                        'more thin' => '❌ Thin утворює comparative із суфіксом -er.',
                        'thinnest' => '❌ Це суперлатив, а речення — порівняння.',
                        'a lot thinner' => 'ℹ️ Підсилена форма правильної відповіді; базова — thinner.',
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
                        'better' => '✅ Неправильна форма comparative для good.',
                        'more good' => '❌ Good не утворює ступінь із more.',
                        'gooder' => '❌ Такої форми немає; потрібен better.',
                        'much better' => 'ℹ️ Підсилений варіант правильної відповіді.',
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
                        'busiest' => '✅ The busiest — правильний суперлатив.',
                        'more busy' => '❌ Це comparative, а в реченні є the — потрібен суперлатив.',
                        'busyest' => '❌ Орфографія: y → iest.',
                        'most busy' => '❌ Рідкісний варіант; стандартна форма busy → busiest.',
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
                        'fewer' => '✅ Comparative для злічуваних: fewer people.',
                        'less' => '❌ Less використовується зі злічуваними лише рідко; коректніше fewer.',
                        'fewest' => '❌ Це суперлатив, не comparative.',
                        'a lot fewer' => 'ℹ️ Підсилений варіант правильної відповіді.',
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
                        'trickiest' => '✅ Найскладніша частина — superlative with the.',
                        'more tricky' => '❌ Це comparative, але вже є the, отже потрібен суперлатив.',
                        'trickier' => '❌ Comparative без the не підходить.',
                        'most tricky' => '❌ Можливий розмовний варіант, але стандарт — trickiest.',
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
