<?php

namespace Database\Seeders\Ai;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class CaludeDegreesOfComparisonSeeder extends QuestionSeeder
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
                'hints' => $entry['question_hints'] ?? [],
                'answers' => $entry['answers'],
                'option_markers' => $this->buildOptionMarkers($entry['options']),
                'explanations' => $entry['chatgpt_explanations'] ?? [],
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

        $adverbTagId = Tag::firstOrCreate(
            ['name' => 'Adverbs Comparison'],
            ['category' => 'English Grammar Focus']
        )->id;

        $skillTagId = Tag::firstOrCreate(
            ['name' => 'Multiple Choice Grammar Gap'],
            ['category' => 'Skill Type']
        )->id;

        return [
            $themeTagId,
            $comparativeTagId,
            $superlativeTagId,
            $irregularTagId,
            $adverbTagId,
            $skillTagId,
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

    private function questionEntries(): array
    {
        return [
            // ===================== A1 LEVEL (12 questions) =====================
            [
                'level' => 'A1',
                'question' => 'A lion is {a1} than a cat.',
                'answers' => ['a1' => 'bigger'],
                'options' => ['a1' => ['bigger', 'big', 'biggest']],
                'verb_hints' => ['a1' => 'big'],
                'question_hints' => ['Для коротких прикметників додаємо -er для порівняння двох речей.'],
                'chatgpt_explanations' => [
                    'bigger' => 'Коли порівнюємо два об\'єкти, використовуємо comparative форму. Короткі прикметники (1 склад) додають -er. Формула: adjective + -er + than. Приклад: The sun is brighter than the moon.',
                    'big' => 'Базова форма прикметника не підходить для порівняння. Потрібна comparative форма з -er.',
                    'biggest' => 'Superlative форма (найвищий ступінь) використовується для групи з трьох і більше, а не для двох об\'єктів.',
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'My pencil is {a1} than yours.',
                'answers' => ['a1' => 'longer'],
                'options' => ['a1' => ['longer', 'long', 'longest']],
                'verb_hints' => ['a1' => 'long'],
                'question_hints' => ['Додаємо -er до коротких прикметників при порівнянні двох предметів.'],
                'chatgpt_explanations' => [
                    'longer' => 'Comparative утворюється додаванням -er до односкладових прикметників. Формула: adjective + -er + than + об\'єкт. Приклад: This road is shorter than that one.',
                    'long' => 'Базова форма не виражає порівняння. Для порівняння двох предметів потрібна форма з -er.',
                    'longest' => 'Superlative використовується при виборі з трьох і більше варіантів, а не для двох.',
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'Is your bag {a1} than mine?',
                'answers' => ['a1' => 'heavier'],
                'options' => ['a1' => ['heavier', 'heavy', 'heaviest']],
                'verb_hints' => ['a1' => 'heavy'],
                'question_hints' => ['У питаннях ми все ще використовуємо comparative форму (-er) при порівнянні двох речей.'],
                'chatgpt_explanations' => [
                    'heavier' => 'У запитаннях зберігаємо comparative форму. Прикметники на -y змінюють y на i перед -er. Формула: Is + subject + adjective-er + than...? Приклад: Is this box emptier than that one?',
                    'heavy' => 'Базова форма не показує порівняння. Для запитання про порівняння потрібна форма з -er.',
                    'heaviest' => 'Superlative не використовується при порівнянні лише двох предметів.',
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'This flower is not as {a1} as that rose.',
                'answers' => ['a1' => 'pretty'],
                'options' => ['a1' => ['pretty', 'prettier', 'prettiest']],
                'verb_hints' => ['a1' => 'beautiful'],
                'question_hints' => ['У структурі "as ... as" використовуємо базову форму прикметника.'],
                'chatgpt_explanations' => [
                    'pretty' => 'Структура not as + adjective + as виражає нерівність. Між двома as стоїть базова форма прикметника. Приклад: This song is not as loud as that one.',
                    'prettier' => 'Comparative форма не використовується всередині конструкції as...as.',
                    'prettiest' => 'Superlative не поєднується зі структурою as...as.',
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'Tom runs {a1} than his brother.',
                'answers' => ['a1' => 'faster'],
                'options' => ['a1' => ['faster', 'fast', 'fastest']],
                'verb_hints' => ['a1' => 'fast'],
                'question_hints' => ['Використовуйте форму з -er для коротких слів при порівнянні дій двох людей.'],
                'chatgpt_explanations' => [
                    'faster' => 'При порівнянні дій двох осіб використовуємо comparative прислівника. Fast — короткий прислівник, тому додаємо -er. Приклад: She speaks louder than him.',
                    'fast' => 'Базова форма не передає порівняння між двома суб\'єктами.',
                    'fastest' => 'Superlative вживається для групи з трьох і більше, не для двох.',
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'The blue car is {a1} than the red one.',
                'answers' => ['a1' => 'slower'],
                'options' => ['a1' => ['slower', 'slow', 'slowest']],
                'verb_hints' => ['a1' => 'slow'],
                'question_hints' => ['Короткі прикметники утворюють comparative за допомогою -er.'],
                'chatgpt_explanations' => [
                    'slower' => 'Для порівняння двох предметів додаємо -er до короткого прикметника. Формула: subject + is + adjective-er + than + object. Приклад: The river is narrower than the street.',
                    'slow' => 'Базова форма не виражає порівняння.',
                    'slowest' => 'Superlative потребує the і використовується для найвищого ступеня в групі.',
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'This room is the {a1} in the house.',
                'answers' => ['a1' => 'smallest'],
                'options' => ['a1' => ['smallest', 'smaller', 'small']],
                'verb_hints' => ['a1' => 'small'],
                'question_hints' => ['Використовуйте "the + прикметник-est" для superlative коротких прикметників.'],
                'chatgpt_explanations' => [
                    'smallest' => 'Superlative утворюється за формулою the + adjective-est для коротких прикметників. Використовується для вибору з групи. Приклад: This is the tallest building in the city.',
                    'smaller' => 'Comparative порівнює два об\'єкти, але тут йдеться про весь будинок.',
                    'small' => 'Базова форма не виражає найвищий ступінь.',
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'Is the book as {a1} as the movie?',
                'answers' => ['a1' => 'good'],
                'options' => ['a1' => ['good', 'better', 'best']],
                'verb_hints' => ['a1' => 'quality'],
                'question_hints' => ['У конструкції "as ... as" завжди використовуємо базову форму.'],
                'chatgpt_explanations' => [
                    'good' => 'Конструкція as + adjective + as вимагає базової форми. У запитаннях структура зберігається. Приклад: Is this test as hard as the last one?',
                    'better' => 'Comparative не вживається у структурі as...as.',
                    'best' => 'Superlative не поєднується з as...as.',
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'My sister is not {a1} than me.',
                'answers' => ['a1' => 'older'],
                'options' => ['a1' => ['older', 'old', 'oldest']],
                'verb_hints' => ['a1' => 'old'],
                'question_hints' => ['Заперечні речення зберігають comparative форму при використанні "than".'],
                'chatgpt_explanations' => [
                    'older' => 'У заперечних реченнях з than зберігаємо comparative форму. Структура: subject + is not + adjective-er + than. Приклад: He is not taller than his father.',
                    'old' => 'Базова форма не виражає порівняння.',
                    'oldest' => 'Superlative не поєднується з than.',
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'A rabbit is {a1} than a turtle.',
                'answers' => ['a1' => 'faster'],
                'options' => ['a1' => ['faster', 'fast', 'fastest']],
                'verb_hints' => ['a1' => 'fast'],
                'question_hints' => ['Використовуйте comparative форму для порівняння двох тварин.'],
                'chatgpt_explanations' => [
                    'faster' => 'Порівняння двох тварин потребує comparative. Формула: A is + adjective-er + than B. Приклад: A cat is quieter than a parrot.',
                    'fast' => 'Базова форма не виражає порівняння між двома суб\'єктами.',
                    'fastest' => 'Superlative для групи з трьох і більше.',
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'Spring days are {a1} than autumn days.',
                'answers' => ['a1' => 'warmer'],
                'options' => ['a1' => ['warmer', 'warm', 'warmest']],
                'verb_hints' => ['a1' => 'warm'],
                'question_hints' => ['Порівнюйте два сезони за допомогою -er + than.'],
                'chatgpt_explanations' => [
                    'warmer' => 'Порівняння двох типів днів використовує comparative. Додаємо -er до короткого прикметника. Приклад: Spring nights are cooler than summer nights.',
                    'warm' => 'Базова форма не показує порівняння.',
                    'warmest' => 'Superlative потребує the і групу з трьох і більше.',
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'This test is the {a1} one we had.',
                'answers' => ['a1' => 'easiest'],
                'options' => ['a1' => ['easiest', 'easier', 'easy']],
                'verb_hints' => ['a1' => 'easy'],
                'question_hints' => ['Використовуйте "the + прикметник-est" для superlative коротких прикметників.'],
                'chatgpt_explanations' => [
                    'easiest' => 'Superlative утворюється за формулою the + adjective-est. Прикметники на -y змінюють y на i перед -est. Приклад: This is the happiest day of my life.',
                    'easier' => 'Comparative порівнює два об\'єкти, а тут вибір з групи.',
                    'easy' => 'Базова форма не виражає найвищий ступінь.',
                ],
            ],

            // ===================== A2 LEVEL (12 questions) =====================
            [
                'level' => 'A2',
                'question' => 'Last week was {a1} than this week.',
                'answers' => ['a1' => 'busier'],
                'options' => ['a1' => ['busier', 'busy', 'busiest']],
                'verb_hints' => ['a1' => 'busy'],
                'question_hints' => ['Для порівняння минулого використовуйте comparative + than.'],
                'chatgpt_explanations' => [
                    'busier' => 'Порівняння минулого з теперішнім використовує comparative. Формула: past subject + was + adjective-er + than + present subject. Приклад: Last week was busier than this week.',
                    'busy' => 'Базова форма не виражає порівняння.',
                    'busiest' => 'Superlative не поєднується з than.',
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'She did not speak as {a1} as her classmate.',
                'answers' => ['a1' => 'loudly'],
                'options' => ['a1' => ['loudly', 'louder', 'loudest']],
                'verb_hints' => ['a1' => 'loud'],
                'question_hints' => ['Використовуйте базову форму прислівника у структурі "as ... as".'],
                'chatgpt_explanations' => [
                    'loudly' => 'Структура not as + adverb + as порівнює дії двох осіб. Базова форма прислівника стоїть між двома as. Приклад: He did not work as efficiently as his colleague.',
                    'louder' => 'Comparative форма не вживається у структурі as...as.',
                    'loudest' => 'Superlative не поєднується з as...as.',
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'Their new house is {a1} than the old one.',
                'answers' => ['a1' => 'more modern'],
                'options' => ['a1' => ['more modern', 'moderner', 'most modern']],
                'verb_hints' => ['a1' => 'modern'],
                'question_hints' => ['Довші прикметники використовують "more + прикметник + than" для порівнянь.'],
                'chatgpt_explanations' => [
                    'more modern' => 'Довші прикметники (2+ складів) утворюють comparative за формулою more + adjective + than. Приклад: This movie is more interesting than that one.',
                    'moderner' => 'Форма з -er не вживається для довших прикметників.',
                    'most modern' => 'Superlative потребує the і не поєднується з than.',
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'Was the show {a1} than the movie?',
                'answers' => ['a1' => 'more exciting'],
                'options' => ['a1' => ['more exciting', 'excitinger', 'most exciting']],
                'verb_hints' => ['a1' => 'exciting'],
                'question_hints' => ['У питаннях використовуйте "more + прикметник + than" для довших прикметників.'],
                'chatgpt_explanations' => [
                    'more exciting' => 'У запитаннях зберігаємо comparative форму: Was + subject + more + adjective + than...? Приклад: Was the book more useful than the article?',
                    'excitinger' => 'Довгі прикметники не додають -er.',
                    'most exciting' => 'Superlative не використовується з than.',
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'This coffee tastes {a1} than the one from yesterday.',
                'answers' => ['a1' => 'better'],
                'options' => ['a1' => ['better', 'gooder', 'best']],
                'verb_hints' => ['a1' => 'good'],
                'question_hints' => ['Деякі прикметники мають неправильні форми comparative.'],
                'chatgpt_explanations' => [
                    'better' => 'Good має неправильну comparative форму. Формула: good → comparative → superlative. Приклад: Her presentation is superior to mine.',
                    'gooder' => 'Неправильні прикметники не додають -er до базової форми.',
                    'best' => 'Superlative не поєднується з than.',
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'The traffic is not as {a1} as it was this morning.',
                'answers' => ['a1' => 'heavy'],
                'options' => ['a1' => ['heavy', 'heavier', 'heaviest']],
                'verb_hints' => ['a1' => 'intense'],
                'question_hints' => ['Використовуйте базову форму у заперечних структурах "as ... as".'],
                'chatgpt_explanations' => [
                    'heavy' => 'Структура not as + adjective + as вимагає базової форми. Порівнюємо два стани руху. Приклад: The music is not as loud as it was before.',
                    'heavier' => 'Comparative не вживається всередині as...as.',
                    'heaviest' => 'Superlative не поєднується з as...as.',
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'He walked {a1} than his friend because he was tired.',
                'answers' => ['a1' => 'more slowly'],
                'options' => ['a1' => ['more slowly', 'slowlier', 'most slowly']],
                'verb_hints' => ['a1' => 'slowly'],
                'question_hints' => ['Прислівники на -ly утворюють comparative за допомогою "more".'],
                'chatgpt_explanations' => [
                    'more slowly' => 'Прислівники на -ly утворюють comparative за формулою more + adverb + than. Приклад: She spoke more quietly than usual.',
                    'slowlier' => 'Прислівники на -ly не додають -er.',
                    'most slowly' => 'Superlative не поєднується з than.',
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'This restaurant is the {a1} in the town.',
                'answers' => ['a1' => 'most expensive'],
                'options' => ['a1' => ['most expensive', 'more expensive', 'expensivest']],
                'verb_hints' => ['a1' => 'expensive'],
                'question_hints' => ['Використовуйте "the most + прикметник" для superlative довгих прикметників.'],
                'chatgpt_explanations' => [
                    'most expensive' => 'Superlative для довгих прикметників: the + most + adjective. Приклад: This is the most beautiful park in the region.',
                    'more expensive' => 'Comparative порівнює два об\'єкти, а не вибір з групи.',
                    'expensivest' => 'Довгі прикметники не додають -est.',
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'Did the journey take {a1} than expected?',
                'answers' => ['a1' => 'longer'],
                'options' => ['a1' => ['longer', 'long', 'longest']],
                'verb_hints' => ['a1' => 'long'],
                'question_hints' => ['Використовуйте comparative + than у питаннях минулого часу.'],
                'chatgpt_explanations' => [
                    'longer' => 'У запитаннях про минуле зберігаємо comparative: Did + subject + verb + adjective-er + than...? Приклад: Did the meeting last shorter than planned?',
                    'long' => 'Базова форма не виражає порівняння.',
                    'longest' => 'Superlative не поєднується з than.',
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'Her handwriting is {a1} than mine.',
                'answers' => ['a1' => 'neater'],
                'options' => ['a1' => ['neater', 'neat', 'neatest']],
                'verb_hints' => ['a1' => 'neat'],
                'question_hints' => ['Короткі прикметники додають -er для порівнянь.'],
                'chatgpt_explanations' => [
                    'neater' => 'Короткі прикметники додають -er для comparative. Формула: subject + is + adjective-er + than + object. Приклад: His desk is cleaner than hers.',
                    'neat' => 'Базова форма не виражає порівняння.',
                    'neatest' => 'Superlative потребує the і групу з трьох і більше.',
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'This path is not {a1} than the main road.',
                'answers' => ['a1' => 'safer'],
                'options' => ['a1' => ['safer', 'safe', 'safest']],
                'verb_hints' => ['a1' => 'safe'],
                'question_hints' => ['Заперечні порівняння все ще використовують "-er + than".'],
                'chatgpt_explanations' => [
                    'safer' => 'У заперечних порівняннях зберігаємо comparative + than. Формула: subject + is not + adjective-er + than. Приклад: This bridge is not stronger than that one.',
                    'safe' => 'Базова форма не виражає порівняння.',
                    'safest' => 'Superlative не поєднується з than.',
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'The situation will be {a1} next month.',
                'answers' => ['a1' => 'worse'],
                'options' => ['a1' => ['worse', 'badder', 'worst']],
                'verb_hints' => ['a1' => 'bad'],
                'question_hints' => ['Деякі прикметники мають неправильні форми comparative.'],
                'chatgpt_explanations' => [
                    'worse' => 'Bad має неправильну comparative форму. Формула: bad → comparative → superlative. Приклад: The situation is becoming more difficult.',
                    'badder' => 'Неправильні прикметники не додають -er.',
                    'worst' => 'Superlative використовується для найвищого ступеня в групі.',
                ],
            ],

            // ===================== B1 LEVEL (12 questions) =====================
            [
                'level' => 'B1',
                'question' => 'The project turned out to be {a1} than we anticipated.',
                'answers' => ['a1' => 'more challenging'],
                'options' => ['a1' => ['more challenging', 'challenginger', 'most challenging']],
                'verb_hints' => ['a1' => 'challenging'],
                'question_hints' => ['Використовуйте "more + прикметник + than" для довших прикметників у минулому контексті.'],
                'chatgpt_explanations' => [
                    'more challenging' => 'Довші прикметники утворюють comparative за формулою more + adjective + than. Приклад: The exam was more demanding than the practice test.',
                    'challenginger' => 'Довгі прикметники не додають -er.',
                    'most challenging' => 'Superlative не поєднується з than.',
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'This lecture was not as {a1} as the previous one.',
                'answers' => ['a1' => 'informative'],
                'options' => ['a1' => ['informative', 'more informative', 'informativest']],
                'verb_hints' => ['a1' => 'useful'],
                'question_hints' => ['Використовуйте базову форму у порівняннях "not as ... as".'],
                'chatgpt_explanations' => [
                    'informative' => 'Структура not as + adjective + as вимагає базової форми. Приклад: The report was not as thorough as the summary.',
                    'more informative' => 'Comparative не вживається всередині as...as.',
                    'informativest' => 'Superlative не поєднується з as...as.',
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'Will the new schedule be {a1} than the current one?',
                'answers' => ['a1' => 'more flexible'],
                'options' => ['a1' => ['more flexible', 'flexibler', 'most flexible']],
                'verb_hints' => ['a1' => 'flexible'],
                'question_hints' => ['Питання майбутнього часу використовують "more + прикметник + than" для довгих прикметників.'],
                'chatgpt_explanations' => [
                    'more flexible' => 'У запитаннях про майбутнє зберігаємо comparative: Will + subject + be + more + adjective + than...? Приклад: Will the plan be more practical than before?',
                    'flexibler' => 'Довгі прикметники не додають -er.',
                    'most flexible' => 'Superlative не поєднується з than.',
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'She performed {a1} than any other candidate.',
                'answers' => ['a1' => 'more confidently'],
                'options' => ['a1' => ['more confidently', 'confidentlier', 'most confidently']],
                'verb_hints' => ['a1' => 'confidently'],
                'question_hints' => ['Прислівники на -ly використовують "more + прислівник + than".'],
                'chatgpt_explanations' => [
                    'more confidently' => 'Прислівники на -ly утворюють comparative за формулою more + adverb + than. Приклад: He answered more directly than his colleague.',
                    'confidentlier' => 'Прислівники на -ly не додають -er.',
                    'most confidently' => 'Superlative не поєднується з than.',
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'We have {a1} time than we thought.',
                'answers' => ['a1' => 'less'],
                'options' => ['a1' => ['less', 'fewer', 'least']],
                'verb_hints' => ['a1' => 'reduced'],
                'question_hints' => ['Використовуйте "less" з незлічуваними іменниками та "fewer" зі злічуваними.'],
                'chatgpt_explanations' => [
                    'less' => 'Less використовується з незлічуваними іменниками (time, money, water). Формула: subject + have + less + uncountable noun + than. Приклад: We have less space than expected.',
                    'fewer' => 'Fewer вживається зі злічуваними іменниками у множині.',
                    'least' => 'Superlative потребує the і групу з трьох і більше.',
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'The situation is getting {a1} every day.',
                'answers' => ['a1' => 'worse and worse'],
                'options' => ['a1' => ['worse and worse', 'more and more worse', 'badder and badder']],
                'verb_hints' => ['a1' => 'bad'],
                'question_hints' => ['Використовуйте "comparative and comparative" для показу поступової зміни.'],
                'chatgpt_explanations' => [
                    'worse and worse' => 'Структура comparative and comparative показує поступову зміну. Приклад: The weather is getting colder and colder.',
                    'more and more worse' => 'Неправильні прикметники не поєднуються з more.',
                    'badder and badder' => 'Bad має неправильну форму, тому не додає -er.',
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'There are {a1} students in the library today than yesterday.',
                'answers' => ['a1' => 'fewer'],
                'options' => ['a1' => ['fewer', 'less', 'fewest']],
                'verb_hints' => ['a1' => 'reduced number'],
                'question_hints' => ['Використовуйте "fewer" зі злічуваними іменниками множини.'],
                'chatgpt_explanations' => [
                    'fewer' => 'Fewer вживається зі злічуваними іменниками у множині. Формула: There are + fewer + plural noun + than. Приклад: There are fewer cars on the road today.',
                    'less' => 'Less типово вживається з незлічуваними іменниками.',
                    'fewest' => 'Superlative потребує the і групу з трьох і більше.',
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'This is the {a1} solution we could find.',
                'answers' => ['a1' => 'most practical'],
                'options' => ['a1' => ['most practical', 'more practical', 'practicaler']],
                'verb_hints' => ['a1' => 'practical'],
                'question_hints' => ['Використовуйте "the most + прикметник" для superlative довгих прикметників.'],
                'chatgpt_explanations' => [
                    'most practical' => 'Superlative для довгих прикметників: the + most + adjective. Приклад: This is the most efficient method we have.',
                    'more practical' => 'Comparative порівнює два об\'єкти, а не вибір з групи.',
                    'practicaler' => 'Довгі прикметники не додають -er.',
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'Did the repair cost {a1} than the estimate?',
                'answers' => ['a1' => 'more'],
                'options' => ['a1' => ['more', 'most', 'much']],
                'verb_hints' => ['a1' => 'increase'],
                'question_hints' => ['Використовуйте "more" для порівняння кількостей у питаннях.'],
                'chatgpt_explanations' => [
                    'more' => 'More використовується для порівняння кількостей або вартості. Формула: Did + subject + verb + more + than...? Приклад: Did the trip cost more than planned?',
                    'most' => 'Superlative не поєднується з than.',
                    'much' => 'Much не є comparative формою для порівняння.',
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'The new model works {a1} than the old one.',
                'answers' => ['a1' => 'more efficiently'],
                'options' => ['a1' => ['more efficiently', 'efficientlier', 'most efficiently']],
                'verb_hints' => ['a1' => 'efficiently'],
                'question_hints' => ['Прислівники на -ly використовують "more + прислівник + than".'],
                'chatgpt_explanations' => [
                    'more efficiently' => 'Прислівники на -ly утворюють comparative за формулою more + adverb + than. Приклад: The system operates more reliably now.',
                    'efficientlier' => 'Прислівники на -ly не додають -er.',
                    'most efficiently' => 'Superlative не поєднується з than.',
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'He is not {a1} than his colleague at solving problems.',
                'answers' => ['a1' => 'better'],
                'options' => ['a1' => ['better', 'gooder', 'best']],
                'verb_hints' => ['a1' => 'good'],
                'question_hints' => ['Неправильні прикметники зберігають свої comparative форми у запереченнях.'],
                'chatgpt_explanations' => [
                    'better' => 'У заперечних реченнях зберігаємо comparative форму неправильних прикметників. Приклад: She is not faster than her sister.',
                    'gooder' => 'Good має неправильну форму, не додає -er.',
                    'best' => 'Superlative не поєднується з than.',
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'The test will be {a1} than the practice exam.',
                'answers' => ['a1' => 'harder'],
                'options' => ['a1' => ['harder', 'hard', 'hardest']],
                'verb_hints' => ['a1' => 'hard'],
                'question_hints' => ['Порівняння майбутнього часу використовують comparative + than.'],
                'chatgpt_explanations' => [
                    'harder' => 'Порівняння у майбутньому використовує comparative + than. Формула: subject + will be + adjective-er + than. Приклад: Tomorrow will be colder than today.',
                    'hard' => 'Базова форма не виражає порівняння.',
                    'hardest' => 'Superlative не поєднується з than.',
                ],
            ],

            // ===================== B2 LEVEL (12 questions) =====================
            [
                'level' => 'B2',
                'question' => 'The evidence suggests that the situation is {a1} complex than initially reported.',
                'answers' => ['a1' => 'more'],
                'options' => ['a1' => ['more', 'most', 'much']],
                'verb_hints' => ['a1' => 'increase'],
                'question_hints' => ['Використовуйте "more" перед довгими прикметниками для comparative форм.'],
                'chatgpt_explanations' => [
                    'more' => 'Довші прикметники утворюють comparative за формулою more + adjective + than. Приклад: The issue is more nuanced than we thought.',
                    'most' => 'Superlative не поєднується з than.',
                    'much' => 'Much може підсилювати comparative, але не є самостійною формою.',
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'If the budget had been higher, the results would have been {a1}.',
                'answers' => ['a1' => 'better'],
                'options' => ['a1' => ['better', 'gooder', 'best']],
                'verb_hints' => ['a1' => 'good'],
                'question_hints' => ['Умовні речення використовують comparative форми для показу потенційних результатів.'],
                'chatgpt_explanations' => [
                    'better' => 'У conditional реченнях comparative показує потенційний результат. Приклад: If we had started earlier, the outcome would have been more favorable.',
                    'gooder' => 'Good має неправильну форму, не додає -er.',
                    'best' => 'Superlative використовується для найвищого ступеня в групі, не для умовного порівняння.',
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'The analysis reveals that customer satisfaction is not as {a1} as competitors claim.',
                'answers' => ['a1' => 'high'],
                'options' => ['a1' => ['high', 'higher', 'highest']],
                'verb_hints' => ['a1' => 'elevated'],
                'question_hints' => ['Використовуйте базову форму у "not as ... as" для порівняння.'],
                'chatgpt_explanations' => [
                    'high' => 'Структура not as + adjective + as вимагає базової форми. Приклад: The profit margin is not as wide as expected.',
                    'higher' => 'Comparative не вживається всередині as...as.',
                    'highest' => 'Superlative не поєднується з as...as.',
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'Had the team trained {a1}, they might have won the championship.',
                'answers' => ['a1' => 'harder'],
                'options' => ['a1' => ['harder', 'hard', 'hardest']],
                'verb_hints' => ['a1' => 'intense'],
                'question_hints' => ['Використовуйте comparative прислівники в умовних структурах.'],
                'chatgpt_explanations' => [
                    'harder' => 'У conditional реченнях comparative показує інтенсивність дії. Приклад: Had they prepared longer, the presentation would have been smoother.',
                    'hard' => 'Базова форма не виражає порівняння.',
                    'hardest' => 'Superlative не використовується в conditional порівняннях.',
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'The more carefully you plan, the {a1} the outcome will be.',
                'answers' => ['a1' => 'better'],
                'options' => ['a1' => ['better', 'gooder', 'best']],
                'verb_hints' => ['a1' => 'good'],
                'question_hints' => ['Подвійна порівняльна структура: "The more..., the + comparative...".'],
                'chatgpt_explanations' => [
                    'better' => 'Структура the more..., the + comparative виражає пропорційну залежність. Приклад: The longer you wait, the more prepared you will be.',
                    'gooder' => 'Good має неправильну форму, не додає -er.',
                    'best' => 'Superlative не використовується у структурі the more..., the...',
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'The report indicates that productivity has increased {a1} rapidly than in previous years.',
                'answers' => ['a1' => 'more'],
                'options' => ['a1' => ['more', 'most', 'much']],
                'verb_hints' => ['a1' => 'increase'],
                'question_hints' => ['Використовуйте "more + прислівник + than" для прислівників у порівняннях.'],
                'chatgpt_explanations' => [
                    'more' => 'Прислівники утворюють comparative за формулою more + adverb + than. Приклад: Sales grew more steadily than expected.',
                    'most' => 'Superlative не поєднується з than.',
                    'much' => 'Much підсилює comparative, але не є самостійною формою.',
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'This is by far the {a1} innovative approach we have seen.',
                'answers' => ['a1' => 'most'],
                'options' => ['a1' => ['most', 'more', 'much']],
                'verb_hints' => ['a1' => 'highest degree'],
                'question_hints' => ['Використовуйте "the most + прикметник" для superlative довгих прикметників.'],
                'chatgpt_explanations' => [
                    'most' => 'Superlative для довгих прикметників: the + most + adjective. By far підсилює superlative. Приклад: This is by far the most remarkable achievement we have witnessed.',
                    'more' => 'Comparative порівнює два об\'єкти, а не вибір з групи.',
                    'much' => 'Much не є частиною superlative конструкції.',
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'The revised version is considerably {a1} detailed than the original.',
                'answers' => ['a1' => 'more'],
                'options' => ['a1' => ['more', 'most', 'much']],
                'verb_hints' => ['a1' => 'increase'],
                'question_hints' => ['Підсилювачі типу "considerably" можуть стояти перед comparative формами.'],
                'chatgpt_explanations' => [
                    'more' => 'Підсилювачі (considerably, significantly) стоять перед comparative. Формула: intensifier + more + adjective + than. Приклад: The analysis is significantly more thorough than before.',
                    'most' => 'Superlative не поєднується з than.',
                    'much' => 'Much може підсилювати comparative, але тут потрібна сама comparative форма.',
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'Were the negotiations as {a1} as the media portrayed them?',
                'answers' => ['a1' => 'tense'],
                'options' => ['a1' => ['tense', 'tenser', 'tensest']],
                'verb_hints' => ['a1' => 'stressful'],
                'question_hints' => ['Використовуйте базову форму у питаннях з "as ... as".'],
                'chatgpt_explanations' => [
                    'tense' => 'Конструкція as + adjective + as вимагає базової форми у запитаннях. Приклад: Was the meeting as productive as you hoped?',
                    'tenser' => 'Comparative не вживається у структурі as...as.',
                    'tensest' => 'Superlative не поєднується з as...as.',
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'The data shows that urban areas have {a1} pollution than rural regions.',
                'answers' => ['a1' => 'more'],
                'options' => ['a1' => ['more', 'most', 'much']],
                'verb_hints' => ['a1' => 'greater amount'],
                'question_hints' => ['Використовуйте "more" з незлічуваними іменниками для порівняння кількості.'],
                'chatgpt_explanations' => [
                    'more' => 'More використовується з незлічуваними іменниками для порівняння кількості. Формула: subject + has/have + more + uncountable noun + than. Приклад: Cities have more traffic than villages.',
                    'most' => 'Superlative не поєднується з than.',
                    'much' => 'Much не є comparative формою для порівняння кількості.',
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'The experiment yielded {a1} accurate results than the previous trial.',
                'answers' => ['a1' => 'less'],
                'options' => ['a1' => ['less', 'fewer', 'least']],
                'verb_hints' => ['a1' => 'reduced'],
                'question_hints' => ['Використовуйте "less + прикметник + than" для порівняння якостей.'],
                'chatgpt_explanations' => [
                    'less' => 'Less + adjective + than показує нижчий ступінь якості. Формула: subject + verb + less + adjective + than. Приклад: The new design is less complex than the old one.',
                    'fewer' => 'Fewer вживається зі злічуваними іменниками, не з прикметниками.',
                    'least' => 'Superlative не поєднується з than.',
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'No other solution proved to be as {a1} as this one.',
                'answers' => ['a1' => 'effective'],
                'options' => ['a1' => ['effective', 'more effective', 'most effective']],
                'verb_hints' => ['a1' => 'useful'],
                'question_hints' => ['Використовуйте базову форму у "as ... as" для вираження рівності.'],
                'chatgpt_explanations' => [
                    'effective' => 'Структура as + adjective + as вимагає базової форми. Приклад: No other method was as reliable as this approach.',
                    'more effective' => 'Comparative не вживається у структурі as...as.',
                    'most effective' => 'Superlative не поєднується з as...as.',
                ],
            ],

            // ===================== C1 LEVEL (12 questions) =====================
            [
                'level' => 'C1',
                'question' => 'The implications of this policy are {a1} reaching than the committee anticipated.',
                'answers' => ['a1' => 'far more'],
                'options' => ['a1' => ['far more', 'far most', 'far much']],
                'verb_hints' => ['a1' => 'extensive'],
                'question_hints' => ['Використовуйте "far more" як підсилений comparative.'],
                'chatgpt_explanations' => [
                    'far more' => 'Far more + adjective + than створює підсилений comparative. Приклад: The consequences are far more severe than we expected.',
                    'far most' => 'Superlative не поєднується з than.',
                    'far much' => 'Much може підсилювати comparative, але у сполученні з far використовується more.',
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'Had the methodology been {a1} rigorous, the findings would have been more reliable.',
                'answers' => ['a1' => 'more'],
                'options' => ['a1' => ['more', 'most', 'much']],
                'verb_hints' => ['a1' => 'increase'],
                'question_hints' => ['Умовні структури використовують comparative форми для показу гіпотетичних результатів.'],
                'chatgpt_explanations' => [
                    'more' => 'У conditional реченнях comparative показує гіпотетичний результат. Формула: Had + subject + been + more + adjective..., subject + would have + past participle. Приклад: Had the analysis been more thorough, the conclusions would have been more accurate.',
                    'most' => 'Superlative не використовується у conditional порівняннях.',
                    'much' => 'Much не є comparative формою.',
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'The researchers concluded that the correlation was not nearly as {a1} as previous studies suggested.',
                'answers' => ['a1' => 'significant'],
                'options' => ['a1' => ['significant', 'more significant', 'most significant']],
                'verb_hints' => ['a1' => 'important'],
                'question_hints' => ['Використовуйте базову форму у "not nearly as ... as" для наголосу.'],
                'chatgpt_explanations' => [
                    'significant' => 'Структура not nearly as + adjective + as підсилює нерівність. Приклад: The impact was not nearly as profound as we had hoped.',
                    'more significant' => 'Comparative не вживається у структурі as...as.',
                    'most significant' => 'Superlative не поєднується з as...as.',
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'The more nuanced the argument becomes, the {a1} persuasive it tends to be.',
                'answers' => ['a1' => 'less'],
                'options' => ['a1' => ['less', 'least', 'fewer']],
                'verb_hints' => ['a1' => 'reduced'],
                'question_hints' => ['Подвійний comparative: "The more..., the less...".'],
                'chatgpt_explanations' => [
                    'less' => 'Структура the more..., the less виражає обернену пропорційність. Приклад: The longer the meeting, the less productive it becomes.',
                    'least' => 'Superlative не використовується у структурі the more..., the...',
                    'fewer' => 'Fewer вживається зі злічуваними іменниками, не з прикметниками.',
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'This interpretation is arguably the {a1} tenable of all the proposed theories.',
                'answers' => ['a1' => 'least'],
                'options' => ['a1' => ['least', 'less', 'fewer']],
                'verb_hints' => ['a1' => 'minimum degree'],
                'question_hints' => ['Використовуйте "the least + прикметник" для негативних superlative.'],
                'chatgpt_explanations' => [
                    'least' => 'Superlative the least + adjective показує найнижчий ступінь якості в групі. Приклад: This option is the least viable among all alternatives.',
                    'less' => 'Comparative порівнює два об\'єкти, а не вибір з групи.',
                    'fewer' => 'Fewer вживається зі злічуваними іменниками, не з прикметниками.',
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'The diplomat handled the crisis {a1} tactfully than anyone had expected.',
                'answers' => ['a1' => 'more'],
                'options' => ['a1' => ['more', 'most', 'much']],
                'verb_hints' => ['a1' => 'increase'],
                'question_hints' => ['Використовуйте "more + прислівник + than" для порівнянь з прислівниками.'],
                'chatgpt_explanations' => [
                    'more' => 'Прислівники утворюють comparative за формулою more + adverb + than. Приклад: She responded more diplomatically than her predecessor.',
                    'most' => 'Superlative не поєднується з than.',
                    'much' => 'Much підсилює comparative, але не є самостійною формою.',
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'Were the regulations to be {a1} stringent, compliance would decrease significantly.',
                'answers' => ['a1' => 'more'],
                'options' => ['a1' => ['more', 'most', 'much']],
                'verb_hints' => ['a1' => 'increase'],
                'question_hints' => ['Subjunctive conditionals використовують comparative форми.'],
                'chatgpt_explanations' => [
                    'more' => 'У subjunctive conditional реченнях comparative показує гіпотетичний результат. Формула: Were + subject + to be + more + adjective..., subject + would + verb. Приклад: Were the criteria to be more demanding, fewer candidates would meet them.',
                    'most' => 'Superlative не використовується у conditional порівняннях.',
                    'much' => 'Much не є comparative формою.',
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'The economic outlook is no {a1} favorable than it was last quarter.',
                'answers' => ['a1' => 'more'],
                'options' => ['a1' => ['more', 'most', 'much']],
                'verb_hints' => ['a1' => 'increase'],
                'question_hints' => ['Використовуйте "no more + прикметник + than" для вираження рівності у негативному сенсі.'],
                'chatgpt_explanations' => [
                    'more' => 'Структура no more + adjective + than показує відсутність різниці. Приклад: The new policy is no more effective than the previous one.',
                    'most' => 'Superlative не поєднується з than.',
                    'much' => 'Much не є частиною структури no more...than.',
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'The phenomenon is considerably {a1} understood than researchers initially believed.',
                'answers' => ['a1' => 'less'],
                'options' => ['a1' => ['less', 'least', 'fewer']],
                'verb_hints' => ['a1' => 'reduced'],
                'question_hints' => ['Підсилювачі можуть стояти перед "less + прикметник + than".'],
                'chatgpt_explanations' => [
                    'less' => 'Підсилювачі (considerably, significantly) стоять перед less + adjective + than. Приклад: The mechanism is significantly less complex than expected.',
                    'least' => 'Superlative не поєднується з than.',
                    'fewer' => 'Fewer вживається зі злічуваними іменниками, не з прикметниками.',
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'This approach yields results that are {a1} consistent than those from traditional methods.',
                'answers' => ['a1' => 'far more'],
                'options' => ['a1' => ['far more', 'far most', 'far much']],
                'verb_hints' => ['a1' => 'extensive'],
                'question_hints' => ['Використовуйте "far more + прикметник + than" для сильних порівнянь.'],
                'chatgpt_explanations' => [
                    'far more' => 'Far more + adjective + than створює підсилений comparative. Приклад: The data is far more reliable than before.',
                    'far most' => 'Superlative не поєднується з than.',
                    'far much' => 'Much може підсилювати comparative, але у сполученні з far використовується more.',
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'The longer the negotiation process, the {a1} likely a satisfactory outcome becomes.',
                'answers' => ['a1' => 'less'],
                'options' => ['a1' => ['less', 'least', 'fewer']],
                'verb_hints' => ['a1' => 'reduced'],
                'question_hints' => ['Подвійний comparative може показувати обернені залежності.'],
                'chatgpt_explanations' => [
                    'less' => 'Структура the longer..., the less виражає обернену залежність. Приклад: The longer the delay, the less effective the intervention.',
                    'least' => 'Superlative не використовується у структурі the more..., the...',
                    'fewer' => 'Fewer вживається зі злічуваними іменниками, не з прикметниками.',
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'The evidence presented was not as {a1} as the prosecution had hoped.',
                'answers' => ['a1' => 'compelling'],
                'options' => ['a1' => ['compelling', 'more compelling', 'most compelling']],
                'verb_hints' => ['a1' => 'convincing'],
                'question_hints' => ['Використовуйте базову форму у структурах "not as ... as".'],
                'chatgpt_explanations' => [
                    'compelling' => 'Структура not as + adjective + as вимагає базової форми. Приклад: The argument was not as robust as initially thought.',
                    'more compelling' => 'Comparative не вживається у структурі as...as.',
                    'most compelling' => 'Superlative не поєднується з as...as.',
                ],
            ],
        ];
    }
}