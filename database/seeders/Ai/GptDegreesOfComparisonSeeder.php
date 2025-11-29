<?php

namespace Database\Seeders\Ai;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class GptDegreesOfComparisonSeeder extends QuestionSeeder
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
            'A1' => Source::firstOrCreate(['name' => 'AI: Degrees of Comparison A1'])->id,
            'A2' => Source::firstOrCreate(['name' => 'AI: Degrees of Comparison A2'])->id,
            'B1' => Source::firstOrCreate(['name' => 'AI: Degrees of Comparison B1'])->id,
            'B2' => Source::firstOrCreate(['name' => 'AI: Degrees of Comparison B2'])->id,
            'C1' => Source::firstOrCreate(['name' => 'AI: Degrees of Comparison C1'])->id,
        ];

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Degrees of Comparison Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Comparatives and Superlatives'],
            ['category' => 'English Grammar Detail']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'Adjectives and Adverbs Comparison'],
            ['category' => 'English Grammar Structure']
        )->id;

        $adverbFocusTagId = Tag::firstOrCreate(
            ['name' => 'Adverb Degree Forms'],
            ['category' => 'English Grammar Focus']
        )->id;

        $aiTagId = Tag::firstOrCreate(
            ['name' => 'AI Generated Content'],
            ['category' => 'Content Type']
        )->id;

        $levelTags = [];
        foreach (['A1', 'A2', 'B1', 'B2', 'C1'] as $level) {
            $levelTags[$level] = Tag::firstOrCreate(
                ['name' => "CEFR {$level}"],
                ['category' => 'Level']
            )->id;
        }

        $tagCache = [];
        $resolveTag = function (string $name, string $category = 'AI Grammar Focus') use (&$tagCache) {
            if (isset($tagCache[$category][$name])) {
                return $tagCache[$category][$name];
            }

            $tagCache[$category][$name] = Tag::firstOrCreate([
                'name' => $name,
            ], [
                'category' => $category,
            ])->id;

            return $tagCache[$category][$name];
        };

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

            $questionTagIds = [
                $themeTagId,
                $detailTagId,
                $structureTagId,
                $adverbFocusTagId,
                $aiTagId,
                $levelTags[$entry['level']] ?? null,
            ];

            foreach ($entry['tags'] ?? [] as $tagName) {
                $questionTagIds[] = $resolveTag($tagName);
            }

            $questionTagIds = array_values(array_unique(array_filter($questionTagIds)));

            $items[] = [
                'uuid' => $uuid,
                'question' => $entry['question'],
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$entry['level']] ?? 3,
                'source_id' => $sourceIds[$entry['level']] ?? reset($sourceIds),
                'flag' => 2,
                'type' => 0,
                'level' => $entry['level'],
                'tag_ids' => $questionTagIds,
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
            // A1 Questions
            [
                'question' => 'My new phone is {a1} than the old one.',
                'options' => [
                    'a1' => ['lighter', 'lightest', 'more light'],
                ],
                'answers' => ['a1' => 'lighter'],
                'verb_hints' => [
                    'a1' => 'базовий прикметник: light',
                ],
                'level' => 'A1',
                'source' => 'A1',
                'tags' => ['affirmative sentence', 'comparative', 'present simple'],
                'hints' => [
                    'a1' => 'Для коротких прикметників при порівнянні двох предметів додаємо -er перед «than».',
                ],
                'explanations' => [
                    'a1' => [
                        'lighter' => 'Використовуємо порівняльну форму короткого прикметника: adjective + -er + than.',
                        'lightest' => 'Це найвищий ступінь, який вживається з «the» при порівнянні трьох і більше.',
                        'more light' => 'Короткі прикметники зазвичай не вживають «more» для порівняльного ступеня.',
                    ],
                ],
            ],
            [
                'question' => 'This box is {a1} than that one; I need help carrying it.',
                'options' => [
                    'a1' => ['heavier', 'heavy', 'more heavy'],
                ],
                'answers' => ['a1' => 'heavier'],
                'verb_hints' => [
                    'a1' => 'базовий прикметник: heavy',
                ],
                'level' => 'A1',
                'source' => 'A1',
                'tags' => ['affirmative sentence', 'comparative', 'present simple'],
                'hints' => [
                    'a1' => 'Короткі прикметники, що закінчуються на -y, змінюють закінчення на -ier при порівнянні двох речей.',
                ],
                'explanations' => [
                    'a1' => [
                        'heavier' => 'Порівняльний ступінь: прикметник на -y → -ier + than.',
                        'heavy' => 'Базова форма використовується для конструкції «as...as», а не після «than».',
                        'more heavy' => 'Короткі прикметники зазвичай утворюють порівняльний ступінь за допомогою -er, а не «more».',
                    ],
                ],
            ],
            [
                'question' => 'Today is the {a1} day of the week for me.',
                'options' => [
                    'a1' => ['busiest', 'busier', 'more busy'],
                ],
                'answers' => ['a1' => 'busiest'],
                'verb_hints' => [
                    'a1' => 'найвищий ступінь для busy',
                ],
                'level' => 'A1',
                'source' => 'A1',
                'tags' => ['superlative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'Використовуйте найвищий ступінь з «the», коли обираєте один предмет із багатьох.',
                ],
                'explanations' => [
                    'a1' => [
                        'busiest' => 'Найвищий ступінь для коротких прикметників на -y: прибираємо y + -iest.',
                        'busier' => 'Порівняльний ступінь порівнює два предмети, а не один серед усіх.',
                        'more busy' => 'Короткі прикметники зазвичай вживають -est для найвищого ступеня, а не «most».',
                    ],
                ],
            ],
            [
                'question' => 'My sister runs {a1} than me.',
                'options' => [
                    'a1' => ['faster', 'fastest', 'fastly'],
                ],
                'answers' => ['a1' => 'faster'],
                'verb_hints' => [
                    'a1' => 'порівняльний ступінь від fast',
                ],
                'level' => 'A1',
                'source' => 'A1',
                'tags' => ['comparative', 'affirmative sentence', 'present simple', 'adverb comparison'],
                'hints' => [
                    'a1' => 'Короткі прислівники також мають закінчення -er при порівнянні двох дій.',
                ],
                'explanations' => [
                    'a1' => [
                        'faster' => 'Порівняльний ступінь прислівника: основа + -er + than.',
                        'fastest' => 'Це найвищий ступінь, який вживається з «the» для трьох і більше.',
                        'fastly' => 'Прислівник уже існує як «fast»; додавання -ly тут є неправильним.',
                    ],
                ],
            ],
            [
                'question' => 'Their house is {a1} than ours.',
                'options' => [
                    'a1' => ['bigger', 'biggest', 'more big'],
                ],
                'answers' => ['a1' => 'bigger'],
                'verb_hints' => [
                    'a1' => 'короткий прикметник: закінчення приголосна + голосна + приголосна',
                ],
                'level' => 'A1',
                'source' => 'A1',
                'tags' => ['comparative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'Подвоюємо останню приголосну для односкладових прикметників типу CVC перед додаванням -er.',
                ],
                'explanations' => [
                    'a1' => [
                        'bigger' => 'Схема: прикметник CVC → подвоєння кінцевої приголосної + -er для порівняння.',
                        'biggest' => 'Найвищий ступінь потребує «the» і використовується при порівнянні більш ніж двох.',
                        'more big' => 'Короткі прикметники віддають перевагу порівняльному ступеню з -er, а не «more».',
                    ],
                ],
            ],
            [
                'question' => 'This road is the {a1} in town.',
                'options' => [
                    'a1' => ['narrowest', 'narrower', 'more narrow'],
                ],
                'answers' => ['a1' => 'narrowest'],
                'verb_hints' => [
                    'a1' => 'найвищий ступінь із закінченням -est',
                ],
                'level' => 'A1',
                'source' => 'A1',
                'tags' => ['superlative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'Щоб показати найвищий ступінь серед багатьох доріг, використовуйте форму з -est.',
                ],
                'explanations' => [
                    'a1' => [
                        'narrowest' => 'Найвищий ступінь короткого прикметника: основа + -est з «the».',
                        'narrower' => 'Це порівняльний ступінь, який підходить лише при порівнянні двох доріг.',
                        'more narrow' => 'Короткі прикметники зазвичай вживають -er/-est замість «more».',
                    ],
                ],
            ],
            [
                'question' => 'Jim is {a1} than his brother.',
                'options' => [
                    'a1' => ['taller', 'tallest', 'tall'],
                ],
                'answers' => ['a1' => 'taller'],
                'verb_hints' => [
                    'a1' => 'короткий прикметник: tall',
                ],
                'level' => 'A1',
                'source' => 'A1',
                'tags' => ['comparative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'При порівнянні двох людей потрібна форма з -er плюс «than».',
                ],
                'explanations' => [
                    'a1' => [
                        'taller' => 'Порівняльний ступінь односкладового прикметника вживає -er з than.',
                        'tallest' => 'Найвищий ступінь для вибору одного з багатьох і зазвичай потребує «the».',
                        'tall' => 'Базова форма підходить для конструкцій типу «as...as», а не після «than».',
                    ],
                ],
            ],
            [
                'question' => 'This puzzle is {a1} than the previous one.',
                'options' => [
                    'a1' => ['easier', 'easiest', 'more easy'],
                ],
                'answers' => ['a1' => 'easier'],
                'verb_hints' => [
                    'a1' => 'прикметник із закінченням -y',
                ],
                'level' => 'A1',
                'source' => 'A1',
                'tags' => ['comparative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'Замініть кінцеву -y на -i перед додаванням -er для коротких прикметників.',
                ],
                'explanations' => [
                    'a1' => [
                        'easier' => 'Правило порівняльного ступеня: прибираємо -y, додаємо -ier, потім вживаємо з «than».',
                        'easiest' => 'Найвищий ступінь порівнює більше двох головоломок і вживається з «the».',
                        'more easy' => 'Короткі прикметники вживають порівняльний ступінь з -er, а не «more».',
                    ],
                ],
            ],
            [
                'question' => 'Our garden is the {a1} on the street.',
                'options' => [
                    'a1' => ['smallest', 'smaller', 'more small'],
                ],
                'answers' => ['a1' => 'smallest'],
                'verb_hints' => [
                    'a1' => 'найвищий ступінь від small',
                ],
                'level' => 'A1',
                'source' => 'A1',
                'tags' => ['superlative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'Щоб показати найвищий ступінь серед багатьох, вживайте форму з -est разом з «the».',
                ],
                'explanations' => [
                    'a1' => [
                        'smallest' => 'Правило найвищого ступеня: базовий прикметник + -est для найвищого рівня.',
                        'smaller' => 'Порівняльний ступінь лише для двох предметів і вживає -er.',
                        'more small' => 'Короткі прикметники вживають -er/-est замість «more»/«most».',
                    ],
                ],
            ],
            [
                'question' => 'The bus is {a1} than the train today.',
                'options' => [
                    'a1' => ['slower', 'slowest', 'more slow'],
                ],
                'answers' => ['a1' => 'slower'],
                'verb_hints' => [
                    'a1' => 'порівняльний ступінь від slow',
                ],
                'level' => 'A1',
                'source' => 'A1',
                'tags' => ['comparative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'Вживайте прикметник + -er для порівняння двох видів транспорту.',
                ],
                'explanations' => [
                    'a1' => [
                        'slower' => 'Порівняльний ступінь короткого прикметника вживає -er перед «than».',
                        'slowest' => 'Найвищий ступінь потребує «the» і означає три або більше варіантів.',
                        'more slow' => 'Короткі прикметники зазвичай уникають «more» для порівняння.',
                    ],
                ],
            ],
            [
                'question' => 'This story is {a1} than the last one.',
                'options' => [
                    'a1' => ['funnier', 'funniest', 'more funny'],
                ],
                'answers' => ['a1' => 'funnier'],
                'verb_hints' => [
                    'a1' => 'заміна -y на -i та додавання -er',
                ],
                'level' => 'A1',
                'source' => 'A1',
                'tags' => ['comparative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'Короткі прикметники на -y вживають -ier у порівняльному ступені.',
                ],
                'explanations' => [
                    'a1' => [
                        'funnier' => 'Схема: прикметник на -y → замінюємо y на i та додаємо -er для порівняння.',
                        'funniest' => 'Найвищий ступінь потребує «the» і порівнює більше двох історій.',
                        'more funny' => 'Короткі прикметники вживають форму з -er, а не «more».',
                    ],
                ],
            ],
            [
                'question' => 'He is the {a1} student in the class.',
                'options' => [
                    'a1' => ['smartest', 'smarter', 'more smart'],
                ],
                'answers' => ['a1' => 'smartest'],
                'verb_hints' => [
                    'a1' => 'найвищий ступінь із закінченням -est',
                ],
                'level' => 'A1',
                'source' => 'A1',
                'tags' => ['superlative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'Використовуйте найвищий ступінь, щоб показати найвищу позицію серед багатьох однокласників.',
                ],
                'explanations' => [
                    'a1' => [
                        'smartest' => 'Найвищий ступінь односкладового прикметника додає -est з «the».',
                        'smarter' => 'Порівняльний ступінь порівнює лише двох учнів.',
                        'more smart' => 'Короткі прикметники віддають перевагу схемі -er/-est замість «more».',
                    ],
                ],
            ],

            // A2 Questions
            [
                'question' => 'This summer is not as {a1} as last summer.',
                'options' => [
                    'a1' => ['hot', 'hotter', 'hottest'],
                ],
                'answers' => ['a1' => 'hot'],
                'verb_hints' => [
                    'a1' => 'базовий прикметник у конструкції as...as',
                ],
                'level' => 'A2',
                'source' => 'A2',
                'tags' => ['negative sentence', 'equality structure', 'present simple'],
                'hints' => [
                    'a1' => 'У конструкціях «as...as» прикметник залишається в базовій формі.',
                ],
                'explanations' => [
                    'a1' => [
                        'hot' => 'Конструкція «not as...as» вживає базовий прикметник без закінчень порівняльного ступеня.',
                        'hotter' => 'Додавання -er використовується для прямих порівнянь з «than».',
                        'hottest' => 'Найвищий ступінь потребує «the» і стосується трьох і більше.',
                    ],
                ],
            ],
            [
                'question' => 'Are these cookies {a1} than the ones you baked yesterday?',
                'options' => [
                    'a1' => ['sweeter', 'sweetest', 'more sweet'],
                ],
                'answers' => ['a1' => 'sweeter'],
                'verb_hints' => [
                    'a1' => 'порівняльний ступінь короткого прикметника',
                ],
                'level' => 'A2',
                'source' => 'A2',
                'tags' => ['interrogative sentence', 'comparative', 'present simple'],
                'hints' => [
                    'a1' => 'Для двох речей короткі прикметники зазвичай додають -er перед «than».',
                ],
                'explanations' => [
                    'a1' => [
                        'sweeter' => 'Правило порівняльного ступеня: прикметник + -er + than для двох.',
                        'sweetest' => 'Найвищий ступінь потребує «the» і використовується для трьох і більше печива.',
                        'more sweet' => 'Короткі прикметники зазвичай уникають «more» у порівняльному ступені.',
                    ],
                ],
            ],
            [
                'question' => 'This path is {a1} than we expected, so drive slowly.',
                'options' => [
                    'a1' => ['narrower', 'narrowest', 'more narrow'],
                ],
                'answers' => ['a1' => 'narrower'],
                'verb_hints' => [
                    'a1' => 'порівняльний ступінь від narrow',
                ],
                'level' => 'A2',
                'source' => 'A2',
                'tags' => ['comparative', 'affirmative sentence', 'present simple', 'advice'],
                'hints' => [
                    'a1' => 'Вживайте форму з -er для порівняння двох доріг або очікувань.',
                ],
                'explanations' => [
                    'a1' => [
                        'narrower' => 'Схема порівняльного ступеня для коротких прикметників додає -er з «than».',
                        'narrowest' => 'Найвищий ступінь описує крайність серед кількох шляхів.',
                        'more narrow' => 'Короткі прикметники зазвичай утворюють порівняльний ступінь з -er.',
                    ],
                ],
            ],
            [
                'question' => 'My room is the {a1} in the apartment.',
                'options' => [
                    'a1' => ['brightest', 'brighter', 'more bright'],
                ],
                'answers' => ['a1' => 'brightest'],
                'verb_hints' => [
                    'a1' => 'найвищий ступінь з the + -est',
                ],
                'level' => 'A2',
                'source' => 'A2',
                'tags' => ['superlative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'При виборі одного з багатьох вживайте найвищий ступінь з «the».',
                ],
                'explanations' => [
                    'a1' => [
                        'brightest' => 'Найвищий ступінь: прикметник + -est показує найвищий рівень.',
                        'brighter' => 'Порівняльний ступінь лише протиставляє дві кімнати.',
                        'more bright' => 'Короткі прикметники віддають перевагу -er/-est, а не «more».',
                    ],
                ],
            ],
            [
                'question' => 'He speaks English {a1} than his sister.',
                'options' => [
                    'a1' => ['more fluently', 'most fluently', 'fluently'],
                ],
                'answers' => ['a1' => 'more fluently'],
                'verb_hints' => [
                    'a1' => 'порівняльний ступінь довгого прислівника',
                ],
                'level' => 'A2',
                'source' => 'A2',
                'tags' => ['comparative', 'affirmative sentence', 'adverb comparison', 'present simple'],
                'hints' => [
                    'a1' => 'Довгі прислівники утворюють порівняльний ступінь з «more» перед словом.',
                ],
                'explanations' => [
                    'a1' => [
                        'more fluently' => 'Схема: more + прислівник + than для довгих прислівників.',
                        'most fluently' => 'Найвищий ступінь вживає «the most» при порівнянні більше двох людей.',
                        'fluently' => 'Базова форма підходить для структур «as...as», а не для прямого порівняння з than.',
                    ],
                ],
            ],
            [
                'question' => "The test wasn't {a1} than last week's exam.",
                'options' => [
                    'a1' => ['harder', 'hardest', 'more hard'],
                ],
                'answers' => ['a1' => 'harder'],
                'verb_hints' => [
                    'a1' => 'порівняльний ступінь від hard',
                ],
                'level' => 'A2',
                'source' => 'A2',
                'tags' => ['comparative', 'negative sentence', 'past simple'],
                'hints' => [
                    'a1' => 'Навіть у запереченнях порівняння двох іспитів потребує форми з -er та «than».',
                ],
                'explanations' => [
                    'a1' => [
                        'harder' => 'Порівняльний ступінь короткого прикметника додає -er для показу різниці.',
                        'hardest' => 'Найвищий ступінь вказує на крайність серед кількох тестів.',
                        'more hard' => 'Короткі прикметники зазвичай не вживають «more» для порівняльного ступеня.',
                    ],
                ],
            ],
            [
                'question' => 'Is this the {a1} route to the museum?',
                'options' => [
                    'a1' => ['fastest', 'faster', 'more fast'],
                ],
                'answers' => ['a1' => 'fastest'],
                'verb_hints' => [
                    'a1' => 'найвищий ступінь від fast',
                ],
                'level' => 'A2',
                'source' => 'A2',
                'tags' => ['superlative', 'interrogative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'При запитанні про найкращий варіант серед багатьох вживайте найвищий ступінь з «the».',
                ],
                'explanations' => [
                    'a1' => [
                        'fastest' => 'Найвищий ступінь короткого прикметника вживає -est для найвищого рівня.',
                        'faster' => 'Порівняльний ступінь порівнює лише два маршрути.',
                        'more fast' => 'Короткі прикметники уникають «more» і вживають -er/-est замість.',
                    ],
                ],
            ],
            [
                'question' => 'Their explanation was {a1} than yours.',
                'options' => [
                    'a1' => ['clearer', 'clearest', 'more clear'],
                ],
                'answers' => ['a1' => 'clearer'],
                'verb_hints' => [
                    'a1' => 'порівняльний ступінь від clear',
                ],
                'level' => 'A2',
                'source' => 'A2',
                'tags' => ['comparative', 'affirmative sentence', 'past simple'],
                'hints' => [
                    'a1' => 'Вживайте форму з -er для безпосереднього порівняння двох пояснень.',
                ],
                'explanations' => [
                    'a1' => [
                        'clearer' => 'Короткі прикметники утворюють порівняльний ступінь з -er, коли за ними йде «than».',
                        'clearest' => 'Найвищий ступінь описує найвищий рівень серед кількох пояснень.',
                        'more clear' => 'Короткі прикметники зазвичай уникають «more» для порівняння.',
                    ],
                ],
            ],
            [
                'question' => 'We arrived {a1} than planned because of traffic.',
                'options' => [
                    'a1' => ['later', 'latest', 'more late'],
                ],
                'answers' => ['a1' => 'later'],
                'verb_hints' => [
                    'a1' => 'порівняльний прислівник від late',
                ],
                'level' => 'A2',
                'source' => 'A2',
                'tags' => ['comparative', 'affirmative sentence', 'past simple', 'adverb comparison'],
                'hints' => [
                    'a1' => 'Неправильний прислівник вживає -er у порівняльному ступені для позначення затримки.',
                ],
                'explanations' => [
                    'a1' => [
                        'later' => 'Схема порівняльного ступеня прислівника: додаємо -er для показу більшої затримки.',
                        'latest' => 'Найвищий ступінь з -est вживається з «the».',
                        'more late' => 'Цей прислівник уже має усталений порівняльний ступінь без «more».',
                    ],
                ],
            ],
            [
                'question' => 'Tonight will be {a1} than last night according to the forecast.',
                'options' => [
                    'a1' => ['colder', 'coldest', 'more cold'],
                ],
                'answers' => ['a1' => 'colder'],
                'verb_hints' => [
                    'a1' => 'порівняльний ступінь від cold',
                ],
                'level' => 'A2',
                'source' => 'A2',
                'tags' => ['comparative', 'future reference', 'affirmative sentence'],
                'hints' => [
                    'a1' => 'Для двох ночей додаємо -er до прикметника перед «than».',
                ],
                'explanations' => [
                    'a1' => [
                        'colder' => 'Порівняльний ступінь короткого прикметника вживає -er для показу нижчої температури.',
                        'coldest' => 'Найвищий ступінь порівнював би три і більше ночей.',
                        'more cold' => 'Короткі прикметники вживають -er, а не «more» у порівняннях.',
                    ],
                ],
            ],
            [
                'question' => 'She is {a1} worried about the result now.',
                'options' => [
                    'a1' => ['less', 'lesser', 'least'],
                ],
                'answers' => ['a1' => 'less'],
                'verb_hints' => [
                    'a1' => 'порівняльний ступінь від little (кількість)',
                ],
                'level' => 'A2',
                'source' => 'A2',
                'tags' => ['comparative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'Щоб показати менший ступінь, вживайте порівняльний ступінь, протилежний до «more».',
                ],
                'explanations' => [
                    'a1' => [
                        'less' => 'Порівняльний ступінь малої кількості поєднується з прикметниками для показу зменшення.',
                        'lesser' => 'Ця форма є офіційною і часто вживається перед іменником, а не перед прикметником окремо.',
                        'least' => 'Найвищий ступінь вказує на найменший ступінь серед кількох разів.',
                    ],
                ],
            ],
            [
                'question' => "This is the {a1} I've ever been to.",
                'options' => [
                    'a1' => ['most exciting', 'more exciting', 'excitingest'],
                ],
                'answers' => ['a1' => 'most exciting'],
                'verb_hints' => [
                    'a1' => 'найвищий ступінь з most + довгий прикметник',
                ],
                'level' => 'A2',
                'source' => 'A2',
                'tags' => ['superlative', 'affirmative sentence', 'present perfect context'],
                'hints' => [
                    'a1' => 'Для довгих прикметників вживайте «the most» для вираження найвищого ступеня.',
                ],
                'explanations' => [
                    'a1' => [
                        'most exciting' => 'Довгі прикметники утворюють найвищий ступінь з «the most» + прикметник.',
                        'more exciting' => 'Порівняльний ступінь лише порівнює дві події.',
                        'excitingest' => 'Ця форма є нестандартною; довгі прикметники уникають закінчень -est.',
                    ],
                ],
            ],

            // B1 Questions
            [
                'question' => "Yesterday's game was {a1} than I thought.",
                'options' => [
                    'a1' => ['more thrilling', 'most thrilling', 'thrillingest'],
                ],
                'answers' => ['a1' => 'more thrilling'],
                'verb_hints' => [
                    'a1' => 'порівняльний ступінь з more + довгий прикметник',
                ],
                'level' => 'B1',
                'source' => 'B1',
                'tags' => ['comparative', 'past simple', 'affirmative sentence'],
                'hints' => [
                    'a1' => 'Довгі прикметники вживають «more» для порівняльного ступеня при протиставленні двох речей.',
                ],
                'explanations' => [
                    'a1' => [
                        'more thrilling' => 'Порівняльний ступінь довгого прикметника вживає more + прикметник + than.',
                        'most thrilling' => 'Найвищий ступінь для вибору одного з трьох і більше.',
                        'thrillingest' => 'Додавання -est до довгих прикметників є нестандартним.',
                    ],
                ],
            ],
            [
                'question' => 'He drives {a1} now than when he was younger.',
                'options' => [
                    'a1' => ['more carefully', 'most carefully', 'carefuller'],
                ],
                'answers' => ['a1' => 'more carefully'],
                'verb_hints' => [
                    'a1' => 'порівняльний ступінь прислівника carefully',
                ],
                'level' => 'B1',
                'source' => 'B1',
                'tags' => ['comparative', 'present simple', 'adverb comparison'],
                'hints' => [
                    'a1' => 'Вживайте more + прислівник + than для порівняння способів дії.',
                ],
                'explanations' => [
                    'a1' => [
                        'more carefully' => 'Довгі прислівники вживають more + прислівник + than для порівняння.',
                        'most carefully' => 'Найвищий ступінь з «the» порівнює три і більше ситуацій.',
                        'carefuller' => 'Додавання -er не є стандартним для цього прислівника.',
                    ],
                ],
            ],
            [
                'question' => 'Which is {a1}, the mountain trail or the river path?',
                'options' => [
                    'a1' => ['safer', 'safest', 'more safe'],
                ],
                'answers' => ['a1' => 'safer'],
                'verb_hints' => [
                    'a1' => 'порівняльний ступінь короткого прикметника',
                ],
                'level' => 'B1',
                'source' => 'B1',
                'tags' => ['comparative', 'interrogative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'Для двох маршрутів вживайте порівняльний ступінь з -er, де «than» мається на увазі.',
                ],
                'explanations' => [
                    'a1' => [
                        'safer' => 'Короткі прикметники часто вживають -er у запитаннях, що порівнюють два варіанти.',
                        'safest' => 'Найвищий ступінь потребував би вибору одного з кількох маршрутів.',
                        'more safe' => 'Короткі прикметники зазвичай уникають «more» для порівняння.',
                    ],
                ],
            ],
            [
                'question' => 'The meeting ended {a1} than expected, so we caught the earlier train.',
                'options' => [
                    'a1' => ['earlier', 'earliest', 'more early'],
                ],
                'answers' => ['a1' => 'earlier'],
                'verb_hints' => [
                    'a1' => 'порівняльний прислівник від early',
                ],
                'level' => 'B1',
                'source' => 'B1',
                'tags' => ['comparative', 'past simple', 'adverb comparison'],
                'hints' => [
                    'a1' => 'Деякі прислівники мають форму з -er для позначення більш раннього часу.',
                ],
                'explanations' => [
                    'a1' => [
                        'earlier' => 'Неправильний прислівник вживає -er для порівняння часу.',
                        'earliest' => 'Найвищий ступінь означає найраніший серед кількох разів.',
                        'more early' => 'Ця фраза менш поширена; форма з -er є стандартною.',
                    ],
                ],
            ],
            [
                'question' => 'This laptop is the {a1} option for our budget.',
                'options' => [
                    'a1' => ['least expensive', 'less expensive', 'more inexpensive'],
                ],
                'answers' => ['a1' => 'least expensive'],
                'verb_hints' => [
                    'a1' => 'найвищий ступінь з least',
                ],
                'level' => 'B1',
                'source' => 'B1',
                'tags' => ['superlative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'Щоб показати найнижчий ступінь вартості, вживайте «the least» з прикметником.',
                ],
                'explanations' => [
                    'a1' => [
                        'least expensive' => 'Найвищий ступінь довгих прикметників може вживати «the least» для найнижчого рівня.',
                        'less expensive' => 'Порівняльний ступінь лише протиставляє дві ціни.',
                        'more inexpensive' => 'Подвійне маркування робить фразу незграбною; вживайте один маркер найвищого ступеня.',
                    ],
                ],
            ],
            [
                'question' => 'Her presentation was {a1} prepared than last time.',
                'options' => [
                    'a1' => ['better', 'best', 'more good'],
                ],
                'answers' => ['a1' => 'better'],
                'verb_hints' => [
                    'a1' => 'неправильний порівняльний ступінь від good',
                ],
                'level' => 'B1',
                'source' => 'B1',
                'tags' => ['comparative', 'affirmative sentence', 'past simple', 'irregular adjective'],
                'hints' => [
                    'a1' => 'Вживайте неправильну форму порівняльного ступеня від «good» при порівнянні двох спроб.',
                ],
                'explanations' => [
                    'a1' => [
                        'better' => 'Неправильний порівняльний ступінь від good показує вищу якість, ніж минулого разу.',
                        'best' => 'Найвищий ступінь вживається для вибору одного з трьох і більше.',
                        'more good' => 'Ця фраза є неграматичною; вживайте встановлену неправильну форму better.',
                    ],
                ],
            ],
            [
                'question' => 'By next year, the new road will make the journey {a1}.',
                'options' => [
                    'a1' => ['shorter', 'shortest', 'more short'],
                ],
                'answers' => ['a1' => 'shorter'],
                'verb_hints' => [
                    'a1' => 'порівняльний ступінь для майбутнього покращення',
                ],
                'level' => 'B1',
                'source' => 'B1',
                'tags' => ['comparative', 'future reference', 'affirmative sentence'],
                'hints' => [
                    'a1' => 'Вживайте форму з -er короткого прикметника для показу зменшеної відстані.',
                ],
                'explanations' => [
                    'a1' => [
                        'shorter' => 'Порівняльний ступінь короткого прикметника вживає -er для позначення меншої кількості.',
                        'shortest' => 'Найвищий ступінь для крайності серед кількох варіантів.',
                        'more short' => 'Короткі прикметники віддають перевагу -er/-est замість «more».',
                    ],
                ],
            ],
            [
                'question' => 'The sequel was not {a1} as the original.',
                'options' => [
                    'a1' => ['as entertaining', 'more entertaining', 'most entertaining'],
                ],
                'answers' => ['a1' => 'as entertaining'],
                'verb_hints' => [
                    'a1' => 'рівність з as...as для довгого прикметника',
                ],
                'level' => 'B1',
                'source' => 'B1',
                'tags' => ['equality structure', 'negative sentence', 'comparative', 'past simple'],
                'hints' => [
                    'a1' => 'Щоб сказати, що дві речі схожі, вживайте «as» + прикметник + «as» навіть у запереченнях.',
                ],
                'explanations' => [
                    'a1' => [
                        'as entertaining' => 'Схема рівності зберігає прикметник у базовій формі всередині as...as.',
                        'more entertaining' => 'Це означало б, що продовження краще, що речення заперечує.',
                        'most entertaining' => 'Найвищий ступінь передбачає, що він кращий за всіх інших, а не рівний.',
                    ],
                ],
            ],
            [
                'question' => 'He answered the questions {a1} of all the candidates.',
                'options' => [
                    'a1' => ['most confidently', 'more confidently', 'confidence'],
                ],
                'answers' => ['a1' => 'most confidently'],
                'verb_hints' => [
                    'a1' => 'найвищий ступінь прислівника з most',
                ],
                'level' => 'B1',
                'source' => 'B1',
                'tags' => ['superlative', 'adverb comparison', 'past simple'],
                'hints' => [
                    'a1' => 'При порівнянні трьох і більше людей вживайте «the most» + прислівник.',
                ],
                'explanations' => [
                    'a1' => [
                        'most confidently' => 'Найвищий ступінь прислівника вживає the most + прислівник для найвищого рівня.',
                        'more confidently' => 'Порівняльний ступінь лише протиставляє двох кандидатів.',
                        'confidence' => 'Це іменник, а не прислівникова форма.',
                    ],
                ],
            ],
            [
                'question' => 'Is the blue jacket {a1} than the gray one?',
                'options' => [
                    'a1' => ['more stylish', 'most stylish', 'stylelier'],
                ],
                'answers' => ['a1' => 'more stylish'],
                'verb_hints' => [
                    'a1' => 'порівняльний ступінь з more + довгий прикметник',
                ],
                'level' => 'B1',
                'source' => 'B1',
                'tags' => ['comparative', 'interrogative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'Довгі прикметники вживають «more» для порівняння двох предметів.',
                ],
                'explanations' => [
                    'a1' => [
                        'more stylish' => 'Порівняльний ступінь з довгим прикметником вживає more + прикметник + than.',
                        'most stylish' => 'Найвищий ступінь для вибору одного з багатьох.',
                        'stylelier' => 'Це написання є нестандартним; вживайте описовий порівняльний ступінь.',
                    ],
                ],
            ],
            [
                'question' => 'We chose the route that was {a1} crowded.',
                'options' => [
                    'a1' => ['least', 'less', 'lesser'],
                ],
                'answers' => ['a1' => 'least'],
                'verb_hints' => [
                    'a1' => 'найвищий ступінь від little (кількість)',
                ],
                'level' => 'B1',
                'source' => 'B1',
                'tags' => ['superlative', 'affirmative sentence', 'past simple'],
                'hints' => [
                    'a1' => 'Щоб показати найменшу кількість серед варіантів, вживайте найвищий ступінь, протилежний до «most».',
                ],
                'explanations' => [
                    'a1' => [
                        'least' => 'Найвищий ступінь малої кількості вживає the least перед прикметником.',
                        'less' => 'Порівняльний ступінь вказує на меншу кількість між двома.',
                        'lesser' => 'Ця форма вживається атрибутивно і звучить офіційно, не з прикметниками окремо.',
                    ],
                ],
            ],
            [
                'question' => 'They worked {a1} this week because the deadline was close.',
                'options' => [
                    'a1' => ['harder', 'hardest', 'more hard'],
                ],
                'answers' => ['a1' => 'harder'],
                'verb_hints' => [
                    'a1' => 'порівняльний ступінь прислівника hard',
                ],
                'level' => 'B1',
                'source' => 'B1',
                'tags' => ['comparative', 'affirmative sentence', 'past simple', 'adverb comparison'],
                'hints' => [
                    'a1' => 'Вживайте неправильну форму порівняльного ступеня прислівника для показу додаткових зусиль.',
                ],
                'explanations' => [
                    'a1' => [
                        'harder' => 'Порівняльний ступінь прислівника зберігає ту ж форму з -er для показу більшої інтенсивності.',
                        'hardest' => 'Найвищий ступінь для найвищого рівня серед кількох періодів.',
                        'more hard' => 'Цей прислівник не потребує «more» для порівняння.',
                    ],
                ],
            ],

            // B2 Questions
            [
                'question' => 'No sooner had we started than the wind grew {a1} than expected.',
                'options' => [
                    'a1' => ['stronger', 'strongest', 'more strong'],
                ],
                'answers' => ['a1' => 'stronger'],
                'verb_hints' => [
                    'a1' => 'порівняльний ступінь від strong',
                ],
                'level' => 'B2',
                'source' => 'B2',
                'tags' => ['comparative', 'past perfect context', 'inversion structure'],
                'hints' => [
                    'a1' => 'Після протиставлення двох умов вживайте прикметник + -er перед «than».',
                ],
                'explanations' => [
                    'a1' => [
                        'stronger' => 'Порівняльний ступінь короткого прикметника вживає -er для позначення посиленої сили.',
                        'strongest' => 'Найвищий ступінь сигналізує про найвищий рівень серед кількох, а не двох.',
                        'more strong' => 'Короткі прикметники зазвичай уникають «more» для порівняльного ступеня.',
                    ],
                ],
            ],
            [
                'question' => 'She sings {a1} in small venues than in stadiums.',
                'options' => [
                    'a1' => ['more beautifully', 'most beautifully', 'beautifuller'],
                ],
                'answers' => ['a1' => 'more beautifully'],
                'verb_hints' => [
                    'a1' => 'порівняльний ступінь довгого прислівника beautifully',
                ],
                'level' => 'B2',
                'source' => 'B2',
                'tags' => ['comparative', 'affirmative sentence', 'adverb comparison', 'present simple'],
                'hints' => [
                    'a1' => 'Довгі прислівники вживають «more» для порівняння виступів у двох місцях.',
                ],
                'explanations' => [
                    'a1' => [
                        'more beautifully' => 'Схема: more + прислівник + than для багатоскладових прислівників.',
                        'most beautifully' => 'Найвищий ступінь порівнює три і більше виступів.',
                        'beautifuller' => 'Ця форма є нестандартною для прислівника.',
                    ],
                ],
            ],
            [
                'question' => 'Of the three proposals, the second was the {a1}.',
                'options' => [
                    'a1' => ['most feasible', 'more feasible', 'feasiblest'],
                ],
                'answers' => ['a1' => 'most feasible'],
                'verb_hints' => [
                    'a1' => 'найвищий ступінь з most + довгий прикметник',
                ],
                'level' => 'B2',
                'source' => 'B2',
                'tags' => ['superlative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'При порівнянні трьох і більше варіантів вживайте «the most» з довгими прикметниками.',
                ],
                'explanations' => [
                    'a1' => [
                        'most feasible' => 'Найвищий ступінь для довгих прикметників вживає the most + прикметник для найвищої придатності.',
                        'more feasible' => 'Порівняльний ступінь лише ранжує дві пропозиції.',
                        'feasiblest' => 'Довгі прикметники уникають закінчень -est.',
                    ],
                ],
            ],
            [
                'question' => 'The instructions were {a1} than they first appeared once we read the footnotes.',
                'options' => [
                    'a1' => ['more complex', 'most complex', 'complexer'],
                ],
                'answers' => ['a1' => 'more complex'],
                'verb_hints' => [
                    'a1' => 'порівняльний ступінь з more + довгий прикметник',
                ],
                'level' => 'B2',
                'source' => 'B2',
                'tags' => ['comparative', 'past simple', 'contrast clause'],
                'hints' => [
                    'a1' => 'Вживайте «more» з довгими прикметниками для показу підвищеної складності.',
                ],
                'explanations' => [
                    'a1' => [
                        'more complex' => 'Порівняльний ступінь з довгим прикметником слідує схемі more + прикметник + than.',
                        'most complex' => 'Найвищий ступінь вказує на найвищу складність серед кількох.',
                        'complexer' => 'Це закінчення є незвичним для довгих прикметників.',
                    ],
                ],
            ],
            [
                'question' => 'Had you arrived earlier, you would have found the seats {a1}.',
                'options' => [
                    'a1' => ['more comfortable', 'most comfortable', 'comfortabler'],
                ],
                'answers' => ['a1' => 'more comfortable'],
                'verb_hints' => [
                    'a1' => 'порівняльний ступінь з more + довгий прикметник',
                ],
                'level' => 'B2',
                'source' => 'B2',
                'tags' => ['comparative', 'conditional clause', 'past perfect context'],
                'hints' => [
                    'a1' => 'Довгі прикметники в гіпотетичних порівняннях вживають more + прикметник.',
                ],
                'explanations' => [
                    'a1' => [
                        'more comfortable' => 'Схема порівняльного ступеня вживає more + прикметник + than в умовних ідеях.',
                        'most comfortable' => 'Найвищий ступінь підходить для вибору найкращого серед кількох місць.',
                        'comfortabler' => 'Додавання -er до цього прикметника є нестандартним.',
                    ],
                ],
            ],
            [
                'question' => 'This device operates {a1} when the temperature drops below zero.',
                'options' => [
                    'a1' => ['less efficiently', 'least efficiently', 'inefficiently'],
                ],
                'answers' => ['a1' => 'less efficiently'],
                'verb_hints' => [
                    'a1' => 'порівняльний ступінь зменшення з прислівником',
                ],
                'level' => 'B2',
                'source' => 'B2',
                'tags' => ['comparative', 'adverb comparison', 'present simple'],
                'hints' => [
                    'a1' => 'Вживайте порівняльний ступінь, протилежний до «more», для показу зниженої продуктивності.',
                ],
                'explanations' => [
                    'a1' => [
                        'less efficiently' => 'Порівняльний ступінь ефективності вживає less + прислівник + than для показу зниження.',
                        'least efficiently' => 'Найвищий ступінь позначає найнижчий рівень серед кількох ситуацій.',
                        'inefficiently' => 'Базова форма не має порівняльного маркера для двох умов.',
                    ],
                ],
            ],
            [
                'question' => 'She felt {a1} about the decision after hearing the new information.',
                'options' => [
                    'a1' => ['more certain', 'most certain', 'certainer'],
                ],
                'answers' => ['a1' => 'more certain'],
                'verb_hints' => [
                    'a1' => 'порівняльний ступінь з more + прикметник',
                ],
                'level' => 'B2',
                'source' => 'B2',
                'tags' => ['comparative', 'affirmative sentence', 'past simple'],
                'hints' => [
                    'a1' => 'Довші прикметники часто вживають «more» для порівняння між двома моментами.',
                ],
                'explanations' => [
                    'a1' => [
                        'more certain' => 'Порівняльний ступінь з more + прикметник виражає підвищену впевненість.',
                        'most certain' => 'Найвищий ступінь вказує на найвищий рівень серед кількох варіантів.',
                        'certainer' => 'Ця форма є незвичною; вживайте описовий порівняльний ступінь.',
                    ],
                ],
            ],
            [
                'question' => 'The documentary was {a1} than the drama but still engaging.',
                'options' => [
                    'a1' => ['less dramatic', 'least dramatic', 'more dramatic'],
                ],
                'answers' => ['a1' => 'less dramatic'],
                'verb_hints' => [
                    'a1' => 'порівняльний ступінь зменшення з довгим прикметником',
                ],
                'level' => 'B2',
                'source' => 'B2',
                'tags' => ['comparative', 'affirmative sentence', 'past simple'],
                'hints' => [
                    'a1' => 'Щоб показати менший ступінь, поєднуйте «less» з прикметником.',
                ],
                'explanations' => [
                    'a1' => [
                        'less dramatic' => 'Порівняльний ступінь з less + прикметник виражає знижений ступінь.',
                        'least dramatic' => 'Найвищий ступінь вказує на мінімальний ступінь серед кількох шоу.',
                        'more dramatic' => 'Це означало б протилежне, імплікуючи більшу інтенсивність.',
                    ],
                ],
            ],
            [
                'question' => 'Among the interns, Maria responded {a1} to crisis situations.',
                'options' => [
                    'a1' => ['most calmly', 'more calmly', 'calmer'],
                ],
                'answers' => ['a1' => 'most calmly'],
                'verb_hints' => [
                    'a1' => 'найвищий ступінь прислівника з most',
                ],
                'level' => 'B2',
                'source' => 'B2',
                'tags' => ['superlative', 'adverb comparison', 'present simple'],
                'hints' => [
                    'a1' => 'При порівнянні більше двох людей вживайте «the most» + прислівник.',
                ],
                'explanations' => [
                    'a1' => [
                        'most calmly' => 'Структура найвищого ступеня прислівника: the most + прислівник для найвищого рівня.',
                        'more calmly' => 'Порівняльний ступінь лише порівнює двох стажерів.',
                        'calmer' => 'Це форма прикметника і не є правильним прислівниковим найвищим ступенем.',
                    ],
                ],
            ],
            [
                'question' => 'The castle is {a1} than it looks from the road because of the underground rooms.',
                'options' => [
                    'a1' => ['larger', 'largest', 'more large'],
                ],
                'answers' => ['a1' => 'larger'],
                'verb_hints' => [
                    'a1' => 'порівняльний ступінь від large',
                ],
                'level' => 'B2',
                'source' => 'B2',
                'tags' => ['comparative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'Короткі прикметники вживають -er для порівняння зовнішнього вигляду з реальністю.',
                ],
                'explanations' => [
                    'a1' => [
                        'larger' => 'Порівняльний ступінь короткого прикметника утворюється з -er і мається на увазі than.',
                        'largest' => 'Найвищий ступінь потребує «the» і порівнює більше двох замків.',
                        'more large' => 'Короткі прикметники зазвичай уникають «more» для порівняльного ступеня.',
                    ],
                ],
            ],
            [
                'question' => 'If the weather gets {a1}, the event will move indoors.',
                'options' => [
                    'a1' => ['worse', 'worst', 'baddest'],
                ],
                'answers' => ['a1' => 'worse'],
                'verb_hints' => [
                    'a1' => 'неправильний порівняльний ступінь від bad',
                ],
                'level' => 'B2',
                'source' => 'B2',
                'tags' => ['comparative', 'conditional clause', 'future reference', 'irregular adjective'],
                'hints' => [
                    'a1' => 'Вживайте неправильну форму порівняльного ступеня для показу погіршення умов.',
                ],
                'explanations' => [
                    'a1' => [
                        'worse' => 'Неправильний порівняльний ступінь для bad вказує на гірший стан.',
                        'worst' => 'Найвищий ступінь позначає найнижчу якість серед кількох разів.',
                        'baddest' => 'Ця розмовна форма не є стандартною для офіційного порівняння.',
                    ],
                ],
            ],
            [
                'question' => 'They completed the project {a1} than the rival team despite fewer resources.',
                'options' => [
                    'a1' => ['more quickly', 'most quickly', 'quickerly'],
                ],
                'answers' => ['a1' => 'more quickly'],
                'verb_hints' => [
                    'a1' => 'порівняльний прислівник з more',
                ],
                'level' => 'B2',
                'source' => 'B2',
                'tags' => ['comparative', 'adverb comparison', 'past simple'],
                'hints' => [
                    'a1' => 'Багато прислівників на -ly вживають more + прислівник + than для порівняння.',
                ],
                'explanations' => [
                    'a1' => [
                        'more quickly' => 'Порівняльний ступінь прислівника з -ly вживає more + прислівник для показу різниці в швидкості.',
                        'most quickly' => 'Найвищий ступінь порівнює три і більше команд.',
                        'quickerly' => 'Ця форма є неправильною; прислівники на -ly уникають закінчення -er.',
                    ],
                ],
            ],

            // C1 Questions
            [
                'question' => 'Had the negotiations gone {a1}, the merger would have been announced last quarter.',
                'options' => [
                    'a1' => ['more smoothly', 'most smoothly', 'smoother'],
                ],
                'answers' => ['a1' => 'more smoothly'],
                'verb_hints' => [
                    'a1' => 'порівняльний прислівник з more',
                ],
                'level' => 'C1',
                'source' => 'C1',
                'tags' => ['comparative', 'conditional clause', 'past perfect context', 'adverb comparison'],
                'hints' => [
                    'a1' => 'Для довших прислівників у гіпотетичних твердженнях вживайте more + прислівник.',
                ],
                'explanations' => [
                    'a1' => [
                        'more smoothly' => 'Порівняльний ступінь прислівника з more показує вищий ступінь легкості.',
                        'most smoothly' => 'Найвищий ступінь передбачав би порівняння серед багатьох сценаріїв.',
                        'smoother' => 'Ця форма прикметника не підходить для прислівникової ролі тут.',
                    ],
                ],
            ],
            [
                'question' => 'He is {a1} convinced that the strategy will fail after the latest reports.',
                'options' => [
                    'a1' => ['more firmly', 'most firmly', 'firmest'],
                ],
                'answers' => ['a1' => 'more firmly'],
                'verb_hints' => [
                    'a1' => 'порівняльний прислівник ступеня',
                ],
                'level' => 'C1',
                'source' => 'C1',
                'tags' => ['comparative', 'affirmative sentence', 'present simple', 'adverb comparison'],
                'hints' => [
                    'a1' => 'Вживайте more + прислівник для вираження сильнішого способу переконання.',
                ],
                'explanations' => [
                    'a1' => [
                        'more firmly' => 'Порівняльний ступінь способу вживає more + прислівник для вказівки на посилену міцність.',
                        'most firmly' => 'Найвищий ступінь порівнював би кількох людей, а не просто зміну в одній людині.',
                        'firmest' => 'Це найвищий ступінь прикметника і не модифікує дієслово належним чином.',
                    ],
                ],
            ],
            [
                'question' => 'The revised policy is the {a1} balanced solution we have seen so far.',
                'options' => [
                    'a1' => ['most', 'more', 'mostly'],
                ],
                'answers' => ['a1' => 'most'],
                'verb_hints' => [
                    'a1' => 'маркер найвищого ступеня перед прикметником',
                ],
                'level' => 'C1',
                'source' => 'C1',
                'tags' => ['superlative', 'affirmative sentence', 'present perfect context'],
                'hints' => [
                    'a1' => 'Щоб показати найвищий ступінь, поставте маркер найвищого ступеня перед прикметником.',
                ],
                'explanations' => [
                    'a1' => [
                        'most' => 'Конструкція найвищого ступеня потребує the + most перед довгим прикметником.',
                        'more' => 'Це сформувало б порівняльний ступінь, а не найвищий.',
                        'mostly' => 'Як прислівник, він не підходить до структури іменникової фрази.',
                    ],
                ],
            ],
            [
                'question' => 'Should costs rise {a1} than predicted, the board will revisit the budget.',
                'options' => [
                    'a1' => ['more sharply', 'most sharply', 'sharper'],
                ],
                'answers' => ['a1' => 'more sharply'],
                'verb_hints' => [
                    'a1' => 'порівняльний прислівник з more',
                ],
                'level' => 'C1',
                'source' => 'C1',
                'tags' => ['comparative', 'conditional clause', 'future reference', 'adverb comparison'],
                'hints' => [
                    'a1' => 'Вживайте more + прислівник для порівняння інтенсивності потенційної зміни.',
                ],
                'explanations' => [
                    'a1' => [
                        'more sharply' => 'Порівняльний ступінь прислівника вказує на сильніше зростання порівняно з прогнозами.',
                        'most sharply' => 'Найвищий ступінь порівнював би кілька сценаріїв, а не два.',
                        'sharper' => 'Ця форма прикметника не модифікує дієслово безпосередньо.',
                    ],
                ],
            ],
            [
                'question' => 'She delivered her keynote {a1} than anyone had anticipated, captivating the audience.',
                'options' => [
                    'a1' => ['more persuasively', 'most persuasively', 'persuasiveer'],
                ],
                'answers' => ['a1' => 'more persuasively'],
                'verb_hints' => [
                    'a1' => 'порівняльний прислівник способу',
                ],
                'level' => 'C1',
                'source' => 'C1',
                'tags' => ['comparative', 'past perfect context', 'adverb comparison'],
                'hints' => [
                    'a1' => 'Довгі прислівники, що описують спосіб, вживають more + прислівник для порівняння.',
                ],
                'explanations' => [
                    'a1' => [
                        'more persuasively' => 'Порівняльний ступінь з more + прислівник показує сильніший спосіб, ніж очікувалося.',
                        'most persuasively' => 'Найвищий ступінь ранжував би її серед багатьох спікерів.',
                        'persuasiveer' => 'Ця нестандартна форма не створює правильний прислівник.',
                    ],
                ],
            ],
            [
                'question' => 'Of all the applicants, he seemed the {a1} suited for the role requiring diplomacy.',
                'options' => [
                    'a1' => ['best', 'better', 'more good'],
                ],
                'answers' => ['a1' => 'best'],
                'verb_hints' => [
                    'a1' => 'неправильний найвищий ступінь від good',
                ],
                'level' => 'C1',
                'source' => 'C1',
                'tags' => ['superlative', 'affirmative sentence', 'irregular adjective'],
                'hints' => [
                    'a1' => 'Вживайте неправильну форму найвищого ступеня від «good» для найкращого кандидата.',
                ],
                'explanations' => [
                    'a1' => [
                        'best' => 'Неправильний найвищий ступінь показує найвищий рівень без «most».',
                        'better' => 'Це порівняльний ступінь і порівнює лише двох.',
                        'more good' => 'Ця фраза є неграматичною; вживайте встановлені неправильні форми.',
                    ],
                ],
            ],
            [
                'question' => 'If the team were {a1} prepared, the launch would not have been delayed.',
                'options' => [
                    'a1' => ['better', 'best', 'more well'],
                ],
                'answers' => ['a1' => 'better'],
                'verb_hints' => [
                    'a1' => 'неправильний порівняльний ступінь від good/well',
                ],
                'level' => 'C1',
                'source' => 'C1',
                'tags' => ['comparative', 'conditional clause', 'past perfect context', 'irregular adjective'],
                'hints' => [
                    'a1' => 'Вживайте неправильну форму порівняльного ступеня для показу нереального покращеного стану.',
                ],
                'explanations' => [
                    'a1' => [
                        'better' => 'Неправильний порівняльний ступінь виражає вищу якість, ніж поточний стан.',
                        'best' => 'Найвищий ступінь порівнював би команду з кількома іншими.',
                        'more well' => 'Стандартна англійська віддає перевагу неправильній формі порівняльного ступеня.',
                    ],
                ],
            ],
            [
                'question' => 'The report was no {a1} than a preliminary draft despite its length.',
                'options' => [
                    'a1' => ['more authoritative', 'most authoritative', 'authoritativeer'],
                ],
                'answers' => ['a1' => 'more authoritative'],
                'verb_hints' => [
                    'a1' => 'порівняльний ступінь з more + довгий прикметник',
                ],
                'level' => 'C1',
                'source' => 'C1',
                'tags' => ['comparative', 'negative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'Для довгих прикметників у негативному порівнянні все одно вживайте more + прикметник.',
                ],
                'explanations' => [
                    'a1' => [
                        'more authoritative' => 'Порівняльний ступінь з more + прикметник підходить навіть з негативними маркерами.',
                        'most authoritative' => 'Найвищий ступінь претендував би на найвищу позицію, що речення заперечує.',
                        'authoritativeer' => 'Ця форма є неправильною для довгих прикметників.',
                    ],
                ],
            ],
            [
                'question' => 'Her tone grew {a1} polite as the meeting dragged on.',
                'options' => [
                    'a1' => ['less', 'lesser', 'least'],
                ],
                'answers' => ['a1' => 'less'],
                'verb_hints' => [
                    'a1' => 'порівняльний ступінь для зменшення',
                ],
                'level' => 'C1',
                'source' => 'C1',
                'tags' => ['comparative', 'affirmative sentence', 'past simple'],
                'hints' => [
                    'a1' => 'Щоб показати поступове зменшення, вживайте порівняльний ступінь, протилежний до «more».',
                ],
                'explanations' => [
                    'a1' => [
                        'less' => 'Порівняльний ступінь вказує на менший ступінь без явного додавання than.',
                        'lesser' => 'Офіційна атрибутивна форма, не природна перед прикметником окремо.',
                        'least' => 'Найвищий ступінь передбачав би порівняння серед багатьох етапів.',
                    ],
                ],
            ],
            [
                'question' => 'Among the various solutions, the iterative approach proved {a1} in practice.',
                'options' => [
                    'a1' => ['most resilient', 'more resilient', 'resiliencest'],
                ],
                'answers' => ['a1' => 'most resilient'],
                'verb_hints' => [
                    'a1' => 'найвищий ступінь з most + довгий прикметник',
                ],
                'level' => 'C1',
                'source' => 'C1',
                'tags' => ['superlative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'Щоб показати найсильніший варіант з кількох, вживайте «the most» з прикметником.',
                ],
                'explanations' => [
                    'a1' => [
                        'most resilient' => 'Найвищий ступінь довгого прикметника утворюється з the most + прикметник.',
                        'more resilient' => 'Порівняльний ступінь лише протиставляє два рішення.',
                        'resiliencest' => 'Це закінчення не є стандартним для довгих прикметників.',
                    ],
                ],
            ],
            [
                'question' => 'Were the data {a1} reliable, we could publish without further tests.',
                'options' => [
                    'a1' => ['more reliable', 'most reliable', 'reliabler'],
                ],
                'answers' => ['a1' => 'more reliable'],
                'verb_hints' => [
                    'a1' => 'порівняльний ступінь з more + довгий прикметник',
                ],
                'level' => 'C1',
                'source' => 'C1',
                'tags' => ['comparative', 'conditional clause', 'present simple'],
                'hints' => [
                    'a1' => 'Вживайте more + прикметник у нереальних умовах для показу уявного вищого рівня.',
                ],
                'explanations' => [
                    'a1' => [
                        'more reliable' => 'Порівняльний ступінь з more + прикметник виражає нереальну вищу якість.',
                        'most reliable' => 'Найвищий ступінь порівнював би багато наборів даних.',
                        'reliabler' => 'Ця форма з -er є нестандартною для довгих прикметників.',
                    ],
                ],
            ],
            [
                'question' => 'The manuscript reads {a1} now that the redundant chapters have been removed.',
                'options' => [
                    'a1' => ['more concisely', 'most concisely', 'conciselier'],
                ],
                'answers' => ['a1' => 'more concisely'],
                'verb_hints' => [
                    'a1' => 'порівняльний прислівник з more',
                ],
                'level' => 'C1',
                'source' => 'C1',
                'tags' => ['comparative', 'adverb comparison', 'present perfect context'],
                'hints' => [
                    'a1' => 'Довгі прислівники у порівняннях вживають more + прислівник для показу покращеного стилю.',
                ],
                'explanations' => [
                    'a1' => [
                        'more concisely' => 'Порівняльний ступінь прислівника з more показує більш стислий стиль написання, ніж раніше.',
                        'most concisely' => 'Найвищий ступінь порівнював би кілька версій тексту.',
                        'conciselier' => 'Ця штучна форма не є стандартною англійською.',
                    ],
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
}
