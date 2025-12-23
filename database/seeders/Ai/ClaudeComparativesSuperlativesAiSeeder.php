<?php

namespace Database\Seeders\Ai;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class ClaudeComparativesSuperlativesAiSeeder extends QuestionSeeder
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

        $sourceId = Source::firstOrCreate(['name' => 'Claude AI: Comparatives & Superlatives Comprehensive Practice'])->id;

        $tagIds = $this->buildTags();

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
            $uuid = $this->generateQuestionUuid($entry['level'], $index + 1, $entry['question']);

            $questionTagIds = array_merge($tagIds, $entry['tag_ids'] ?? []);

            $items[] = [
                'uuid' => $uuid,
                'question' => $entry['question'],
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$entry['level']] ?? 3,
                'source_id' => $sourceId,
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
                'hints' => $entry['hints'] ?? [],
                'answers' => $entry['answers'],
                'option_markers' => $this->buildOptionMarkers($entry['options']),
                'explanations' => $this->buildExplanations($entry['options'], $entry['answers'], $entry['level']),
            ];
        }

        $this->seedQuestionData($items, $meta);
    }

    private function buildTags(): array
    {
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

        $thanPatternTagId = Tag::firstOrCreate(
            ['name' => 'Comparative + than Pattern'],
            ['category' => 'English Grammar Pattern']
        )->id;

        $equalityTagId = Tag::firstOrCreate(
            ['name' => 'As ... as Equality'],
            ['category' => 'English Grammar Pattern']
        )->id;

        $superlativeFormTagId = Tag::firstOrCreate(
            ['name' => 'Superlative Formation (-est / most / least)'],
            ['category' => 'English Grammar Pattern']
        )->id;

        $irregularFormsTagId = Tag::firstOrCreate(
            ['name' => 'Irregular Comparative Forms (good/bad/far)'],
            ['category' => 'English Grammar Focus']
        )->id;

        $quantityComparisonTagId = Tag::firstOrCreate(
            ['name' => 'Quantity Comparisons (much/many/less/fewer)'],
            ['category' => 'English Grammar Focus']
        )->id;

        return [
            $themeTagId,
            $detailTagId,
            $structureTagId,
            $thanPatternTagId,
            $equalityTagId,
            $superlativeFormTagId,
            $irregularFormsTagId,
            $quantityComparisonTagId,
        ];
    }

    private function questionEntries(): array
    {
        // Create additional tags for specific question types
        $affirmativeTagId = Tag::firstOrCreate(
            ['name' => 'Affirmative Sentence'],
            ['category' => 'Sentence Type']
        )->id;

        $negativeTagId = Tag::firstOrCreate(
            ['name' => 'Negative Sentence'],
            ['category' => 'Sentence Type']
        )->id;

        $interrogativeTagId = Tag::firstOrCreate(
            ['name' => 'Interrogative Sentence'],
            ['category' => 'Sentence Type']
        )->id;

        $presentTenseTagId = Tag::firstOrCreate(
            ['name' => 'Present Tense'],
            ['category' => 'Tense']
        )->id;

        $pastTenseTagId = Tag::firstOrCreate(
            ['name' => 'Past Tense'],
            ['category' => 'Tense']
        )->id;

        $futureTenseTagId = Tag::firstOrCreate(
            ['name' => 'Future Tense'],
            ['category' => 'Tense']
        )->id;

        return [
            // ===== A1 Level: 12 questions =====
            // 1. Affirmative, Present
            [
                'level' => 'A1',
                'question' => 'My brother is {a1} than me.',
                'answers' => ['a1' => 'older'],
                'options' => ['a1' => ['older', 'oldest', 'old']],
                'verb_hints' => ['a1' => 'old'],
                'hints' => [
                    'Формула: прикметник + -er + than для порівняння двох людей або речей.',
                    'Приклад структури: Subject + be + comparative + than + object.',
                    'Зверни увагу: old → older (додаємо -er до короткого прикметника).',
                ],
                'tag_ids' => [$affirmativeTagId, $presentTenseTagId],
            ],
            // 2. Affirmative, Present
            [
                'level' => 'A1',
                'question' => 'This box is {a1} than that one.',
                'answers' => ['a1' => 'bigger'],
                'options' => ['a1' => ['bigger', 'biggest', 'big']],
                'verb_hints' => ['a1' => 'big'],
                'hints' => [
                    'Формула: прикметник із подвоєнням кінцевої приголосної + -er + than.',
                    'Приклад: big → bigger (подвоюємо g перед -er).',
                    'Порівнюємо два предмети, тому використовуємо comparative.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentTenseTagId],
            ],
            // 3. Negative, Present
            [
                'level' => 'A1',
                'question' => 'This apple is not {a1} than the orange.',
                'answers' => ['a1' => 'sweeter'],
                'options' => ['a1' => ['sweeter', 'sweetest', 'sweet']],
                'verb_hints' => ['a1' => 'sweet'],
                'hints' => [
                    'Формула: not + comparative + than для заперечного порівняння.',
                    'Приклад: sweet → sweeter (додаємо -er).',
                    'Заперечення не змінює форму comparative.',
                ],
                'tag_ids' => [$negativeTagId, $presentTenseTagId],
            ],
            // 4. Interrogative, Present
            [
                'level' => 'A1',
                'question' => 'Is your house {a1} than mine?',
                'answers' => ['a1' => 'newer'],
                'options' => ['a1' => ['newer', 'newest', 'new']],
                'verb_hints' => ['a1' => 'new'],
                'hints' => [
                    'Формула: Be + subject + comparative + than + object?',
                    'Приклад: new → newer (додаємо -er).',
                    'У питаннях порядок слів змінюється, але comparative залишається.',
                ],
                'tag_ids' => [$interrogativeTagId, $presentTenseTagId],
            ],
            // 5. Affirmative, Present - Superlative
            [
                'level' => 'A1',
                'question' => 'She is the {a1} girl in our class.',
                'answers' => ['a1' => 'tallest'],
                'options' => ['a1' => ['tallest', 'taller', 'tall']],
                'verb_hints' => ['a1' => 'tall'],
                'hints' => [
                    'Формула: the + прикметник + -est для найвищого ступеня.',
                    'Приклад: tall → tallest (the + adjective-est).',
                    'Використовуємо superlative, коли порівнюємо з групою.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentTenseTagId],
            ],
            // 6. Negative, Present
            [
                'level' => 'A1',
                'question' => 'My cat is not the {a1} pet in the house.',
                'answers' => ['a1' => 'smallest'],
                'options' => ['a1' => ['smallest', 'smaller', 'small']],
                'verb_hints' => ['a1' => 'small'],
                'hints' => [
                    'Формула: not + the + superlative для заперечення найвищого ступеня.',
                    'Приклад: small → smallest (the + adjective-est).',
                    'Superlative використовується для порівняння з групою.',
                ],
                'tag_ids' => [$negativeTagId, $presentTenseTagId],
            ],
            // 7. Interrogative, Present
            [
                'level' => 'A1',
                'question' => 'Is this the {a1} way to school?',
                'answers' => ['a1' => 'shortest'],
                'options' => ['a1' => ['shortest', 'shorter', 'short']],
                'verb_hints' => ['a1' => 'short'],
                'hints' => [
                    'Формула: Be + this/that + the + superlative + noun?',
                    'Приклад: short → shortest (the + adjective-est).',
                    'Питання про найкращий/найкоротший варіант.',
                ],
                'tag_ids' => [$interrogativeTagId, $presentTenseTagId],
            ],
            // 8. Affirmative, Past
            [
                'level' => 'A1',
                'question' => 'Yesterday was {a1} than today.',
                'answers' => ['a1' => 'colder'],
                'options' => ['a1' => ['colder', 'coldest', 'cold']],
                'verb_hints' => ['a1' => 'cold'],
                'hints' => [
                    'Формула: Subject + was/were + comparative + than + object.',
                    'Приклад: cold → colder (прикметник + -er).',
                    'Минулий час: was замість is.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastTenseTagId],
            ],
            // 9. Negative, Past
            [
                'level' => 'A1',
                'question' => 'The movie was not {a1} than the book.',
                'answers' => ['a1' => 'longer'],
                'options' => ['a1' => ['longer', 'longest', 'long']],
                'verb_hints' => ['a1' => 'long'],
                'hints' => [
                    'Формула: Subject + was not + comparative + than.',
                    'Приклад: long → longer (прикметник + -er).',
                    'Заперечення в минулому часі.',
                ],
                'tag_ids' => [$negativeTagId, $pastTenseTagId],
            ],
            // 10. Interrogative, Past
            [
                'level' => 'A1',
                'question' => 'Was the test {a1} than you expected?',
                'answers' => ['a1' => 'harder'],
                'options' => ['a1' => ['harder', 'hardest', 'hard']],
                'verb_hints' => ['a1' => 'hard'],
                'hints' => [
                    'Формула: Was + subject + comparative + than + object?',
                    'Приклад: hard → harder (прикметник + -er).',
                    'Питання в минулому часі про порівняння.',
                ],
                'tag_ids' => [$interrogativeTagId, $pastTenseTagId],
            ],
            // 11. Affirmative, Future
            [
                'level' => 'A1',
                'question' => 'Tomorrow will be {a1} than today.',
                'answers' => ['a1' => 'warmer'],
                'options' => ['a1' => ['warmer', 'warmest', 'warm']],
                'verb_hints' => ['a1' => 'warm'],
                'hints' => [
                    'Формула: Subject + will be + comparative + than.',
                    'Приклад: warm → warmer (прикметник + -er).',
                    'Майбутній час: will be замість is.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureTenseTagId],
            ],
            // 12. Interrogative, Future
            [
                'level' => 'A1',
                'question' => 'Will this task be {a1} than the last one?',
                'answers' => ['a1' => 'easier'],
                'options' => ['a1' => ['easier', 'easiest', 'easy']],
                'verb_hints' => ['a1' => 'easy'],
                'hints' => [
                    'Формула: Will + subject + be + comparative + than?',
                    'Приклад: easy → easier (y змінюється на i + -er).',
                    'Питання про майбутнє порівняння.',
                ],
                'tag_ids' => [$interrogativeTagId, $futureTenseTagId],
            ],

            // ===== A2 Level: 12 questions =====
            // 1. Affirmative, Present - as...as
            [
                'level' => 'A2',
                'question' => 'This coffee is as {a1} as the one I had yesterday.',
                'answers' => ['a1' => 'strong'],
                'options' => ['a1' => ['strong', 'stronger', 'strongest']],
                'verb_hints' => ['a1' => 'powerful'],
                'hints' => [
                    'Формула: as + базова форма прикметника + as для рівності.',
                    'Приклад структури: Subject + be + as + adjective + as + object.',
                    'Не використовуй comparative (-er) у конструкції as...as.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentTenseTagId],
            ],
            // 2. Negative, Present - not as...as
            [
                'level' => 'A2',
                'question' => 'This bag is not as {a1} as my old one.',
                'answers' => ['a1' => 'heavy'],
                'options' => ['a1' => ['heavy', 'heavier', 'heaviest']],
                'verb_hints' => ['a1' => 'weighty'],
                'hints' => [
                    'Формула: not as + базова форма + as показує нерівність.',
                    'Приклад: not as heavy as = менш важкий ніж.',
                    'У заперечній формі використовуємо базовий прикметник.',
                ],
                'tag_ids' => [$negativeTagId, $presentTenseTagId],
            ],
            // 3. Interrogative, Present - irregular
            [
                'level' => 'A2',
                'question' => 'Is this restaurant {a1} than the other one?',
                'answers' => ['a1' => 'better'],
                'options' => ['a1' => ['better', 'best', 'good']],
                'verb_hints' => ['a1' => 'good'],
                'hints' => [
                    'Формула: good → better → best (неправильне ступенювання).',
                    'Приклад: good не додає -er, а змінюється на better.',
                    'Запамятай: good-better-best, bad-worse-worst.',
                ],
                'tag_ids' => [$interrogativeTagId, $presentTenseTagId],
            ],
            // 4. Affirmative, Present - more + adjective
            [
                'level' => 'A2',
                'question' => 'This book is {a1} than the movie.',
                'answers' => ['a1' => 'more interesting'],
                'options' => ['a1' => ['more interesting', 'most interesting', 'interesting']],
                'verb_hints' => ['a1' => 'interesting'],
                'hints' => [
                    'Формула: more + довгий прикметник + than.',
                    'Приклад: interesting → more interesting (не interestinger).',
                    'Довгі прикметники (3+ складів) використовують more/most.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentTenseTagId],
            ],
            // 5. Negative, Present - irregular
            [
                'level' => 'A2',
                'question' => 'Today is not {a1} than yesterday.',
                'answers' => ['a1' => 'worse'],
                'options' => ['a1' => ['worse', 'worst', 'bad']],
                'verb_hints' => ['a1' => 'bad'],
                'hints' => [
                    'Формула: bad → worse → worst (неправильне ступенювання).',
                    'Приклад: bad не додає -er, а змінюється на worse.',
                    'Заперечення: not worse than = не гірший ніж.',
                ],
                'tag_ids' => [$negativeTagId, $presentTenseTagId],
            ],
            // 6. Affirmative, Past - superlative
            [
                'level' => 'A2',
                'question' => 'That was the {a1} day of my life.',
                'answers' => ['a1' => 'happiest'],
                'options' => ['a1' => ['happiest', 'happier', 'happy']],
                'verb_hints' => ['a1' => 'happy'],
                'hints' => [
                    'Формула: the + прикметник-iest для superlative.',
                    'Приклад: happy → happiest (y змінюється на i + -est).',
                    'Використовуємо superlative для найвищого ступеня.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastTenseTagId],
            ],
            // 7. Interrogative, Past - more + adjective
            [
                'level' => 'A2',
                'question' => 'Was the exam {a1} than you thought?',
                'answers' => ['a1' => 'more difficult'],
                'options' => ['a1' => ['more difficult', 'most difficult', 'difficult']],
                'verb_hints' => ['a1' => 'difficult'],
                'hints' => [
                    'Формула: more + довгий прикметник + than для порівняння.',
                    'Приклад: difficult → more difficult.',
                    'Питання в минулому: Was + subject + comparative?',
                ],
                'tag_ids' => [$interrogativeTagId, $pastTenseTagId],
            ],
            // 8. Affirmative, Past - irregular far
            [
                'level' => 'A2',
                'question' => 'The station was {a1} than I expected.',
                'answers' => ['a1' => 'farther'],
                'options' => ['a1' => ['farther', 'farthest', 'far']],
                'verb_hints' => ['a1' => 'far'],
                'hints' => [
                    'Формула: far → farther/further → farthest/furthest.',
                    'Приклад: far має два варіанти comparative.',
                    'Farther частіше для фізичної відстані.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastTenseTagId],
            ],
            // 9. Negative, Future
            [
                'level' => 'A2',
                'question' => 'The weather will not be {a1} than today.',
                'answers' => ['a1' => 'hotter'],
                'options' => ['a1' => ['hotter', 'hottest', 'hot']],
                'verb_hints' => ['a1' => 'hot'],
                'hints' => [
                    'Формула: will not be + comparative + than.',
                    'Приклад: hot → hotter (подвоюємо t + -er).',
                    'Заперечення в майбутньому часі.',
                ],
                'tag_ids' => [$negativeTagId, $futureTenseTagId],
            ],
            // 10. Interrogative, Future - superlative
            [
                'level' => 'A2',
                'question' => 'Will this be the {a1} decision we make?',
                'answers' => ['a1' => 'most important'],
                'options' => ['a1' => ['most important', 'more important', 'important']],
                'verb_hints' => ['a1' => 'important'],
                'hints' => [
                    'Формула: the + most + довгий прикметник для superlative.',
                    'Приклад: important → the most important.',
                    'Питання про найвищий ступінь у майбутньому.',
                ],
                'tag_ids' => [$interrogativeTagId, $futureTenseTagId],
            ],
            // 11. Affirmative, Present - less + adjective
            [
                'level' => 'A2',
                'question' => 'This method is {a1} than the traditional one.',
                'answers' => ['a1' => 'less complicated'],
                'options' => ['a1' => ['less complicated', 'least complicated', 'complicated']],
                'verb_hints' => ['a1' => 'complicated'],
                'hints' => [
                    'Формула: less + прикметник + than для зменшеного порівняння.',
                    'Приклад: less complicated = менш складний.',
                    'Less - протилежність more для довгих прикметників.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentTenseTagId],
            ],
            // 12. Affirmative, Present - double comparative
            [
                'level' => 'A2',
                'question' => 'The days are getting {a1} and {a2}.',
                'answers' => ['a1' => 'longer', 'a2' => 'longer'],
                'options' => [
                    'a1' => ['longer', 'longest', 'long'],
                    'a2' => ['longer', 'longest', 'long'],
                ],
                'verb_hints' => ['a1' => 'long', 'a2' => 'long'],
                'hints' => [
                    'Формула: comparative and comparative для поступової зміни.',
                    'Приклад: longer and longer = все довші і довші.',
                    'Показує прогресивну зміну стану.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentTenseTagId],
            ],

            // ===== B1 Level: 12 questions =====
            // 1. Affirmative, Present - two comparatives
            [
                'level' => 'B1',
                'question' => 'The {a1} you study, the {a2} you become.',
                'answers' => ['a1' => 'more', 'a2' => 'smarter'],
                'options' => [
                    'a1' => ['more', 'most', 'much'],
                    'a2' => ['smarter', 'smartest', 'smart'],
                ],
                'verb_hints' => ['a1' => 'much', 'a2' => 'smart'],
                'hints' => [
                    'Формула: The + comparative, the + comparative для причинно-наслідкового зв\'язку.',
                    'Приклад: The more... the better/smarter/easier.',
                    'Структура показує пропорційну залежність.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentTenseTagId],
            ],
            // 2. Negative, Present - not nearly as
            [
                'level' => 'B1',
                'question' => 'This hotel is not nearly as {a1} as the one we stayed at before.',
                'answers' => ['a1' => 'comfortable'],
                'options' => ['a1' => ['comfortable', 'more comfortable', 'most comfortable']],
                'verb_hints' => ['a1' => 'cozy'],
                'hints' => [
                    'Формула: not nearly as + базова форма + as підсилює нерівність.',
                    'Приклад: not nearly as comfortable = набагато менш зручний.',
                    'Підсилювач nearly показує значну різницю.',
                ],
                'tag_ids' => [$negativeTagId, $presentTenseTagId],
            ],
            // 3. Interrogative, Present - intensifier
            [
                'level' => 'B1',
                'question' => 'Is this situation much {a1} than we anticipated?',
                'answers' => ['a1' => 'worse'],
                'options' => ['a1' => ['worse', 'worst', 'bad']],
                'verb_hints' => ['a1' => 'bad'],
                'hints' => [
                    'Формула: much/far/a lot + comparative підсилює порівняння.',
                    'Приклад: much worse = набагато гірший.',
                    'Підсилювачі стоять перед comparative.',
                ],
                'tag_ids' => [$interrogativeTagId, $presentTenseTagId],
            ],
            // 4. Affirmative, Past - by far the
            [
                'level' => 'B1',
                'question' => 'That was by far the {a1} experience I have ever had.',
                'answers' => ['a1' => 'most exciting'],
                'options' => ['a1' => ['most exciting', 'more exciting', 'exciting']],
                'verb_hints' => ['a1' => 'exciting'],
                'hints' => [
                    'Формула: by far + the + superlative підсилює найвищий ступінь.',
                    'Приклад: by far the most exciting = безперечно найцікавіший.',
                    'By far показує значну перевагу над іншими.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastTenseTagId],
            ],
            // 5. Negative, Past - no + comparative
            [
                'level' => 'B1',
                'question' => 'The second attempt was no {a1} than the first.',
                'answers' => ['a1' => 'better'],
                'options' => ['a1' => ['better', 'best', 'good']],
                'verb_hints' => ['a1' => 'good'],
                'hints' => [
                    'Формула: no + comparative + than означає рівність.',
                    'Приклад: no better than = не кращий ніж (однаковий).',
                    'No + comparative показує відсутність різниці.',
                ],
                'tag_ids' => [$negativeTagId, $pastTenseTagId],
            ],
            // 6. Interrogative, Past - ever + superlative
            [
                'level' => 'B1',
                'question' => 'Was that the {a1} meal you have ever cooked?',
                'answers' => ['a1' => 'tastiest'],
                'options' => ['a1' => ['tastiest', 'tastier', 'tasty']],
                'verb_hints' => ['a1' => 'tasty'],
                'hints' => [
                    'Формула: the + superlative + ever для найкращого досвіду.',
                    'Приклад: the tastiest ever = найсмачніший за все життя.',
                    'Ever + Present Perfect підкреслює досвід.',
                ],
                'tag_ids' => [$interrogativeTagId, $pastTenseTagId],
            ],
            // 7. Affirmative, Future - comparative + and + comparative
            [
                'level' => 'B1',
                'question' => 'Technology will become {a1} and {a2} advanced.',
                'answers' => ['a1' => 'more', 'a2' => 'more'],
                'options' => [
                    'a1' => ['more', 'most', 'much'],
                    'a2' => ['more', 'most', 'much'],
                ],
                'verb_hints' => ['a1' => 'much', 'a2' => 'much'],
                'hints' => [
                    'Формула: more and more + adjective для зростаючої тенденції.',
                    'Приклад: more and more advanced = дедалі досконаліший.',
                    'Показує прогресивну зміну в майбутньому.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureTenseTagId],
            ],
            // 8. Negative, Future
            [
                'level' => 'B1',
                'question' => 'The new version will not be any {a1} than the current one.',
                'answers' => ['a1' => 'cheaper'],
                'options' => ['a1' => ['cheaper', 'cheapest', 'cheap']],
                'verb_hints' => ['a1' => 'cheap'],
                'hints' => [
                    'Формула: not any + comparative означає відсутність різниці.',
                    'Приклад: not any cheaper = зовсім не дешевший.',
                    'Any підсилює заперечення.',
                ],
                'tag_ids' => [$negativeTagId, $futureTenseTagId],
            ],
            // 9. Interrogative, Future
            [
                'level' => 'B1',
                'question' => 'Will the journey be {a1} if we take the highway?',
                'answers' => ['a1' => 'quicker'],
                'options' => ['a1' => ['quicker', 'quickest', 'quick']],
                'verb_hints' => ['a1' => 'quick'],
                'hints' => [
                    'Формула: Will + subject + be + comparative?',
                    'Приклад: Will it be quicker? = Чи буде швидше?',
                    'Питання про порівняння в майбутньому.',
                ],
                'tag_ids' => [$interrogativeTagId, $futureTenseTagId],
            ],
            // 10. Affirmative, Present - fewer/less
            [
                'level' => 'B1',
                'question' => 'There are {a1} students this year than last year.',
                'answers' => ['a1' => 'fewer'],
                'options' => ['a1' => ['fewer', 'less', 'fewest']],
                'verb_hints' => ['a1' => 'few'],
                'hints' => [
                    'Формула: fewer + злічуваний іменник, less + незлічуваний.',
                    'Приклад: fewer students (злічуваний), less water (незлічуваний).',
                    'Fewer для речей, які можна порахувати.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentTenseTagId],
            ],
            // 11. Affirmative, Present - much/many comparison
            [
                'level' => 'B1',
                'question' => 'She has {a1} patience than her sister.',
                'answers' => ['a1' => 'more'],
                'options' => ['a1' => ['more', 'most', 'much']],
                'verb_hints' => ['a1' => 'much'],
                'hints' => [
                    'Формула: more + незлічуваний іменник + than.',
                    'Приклад: more patience = більше терпіння.',
                    'Much/many → more → most для кількості.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentTenseTagId],
            ],
            // 12. Affirmative, Present - one of the + superlative
            [
                'level' => 'B1',
                'question' => 'This is one of the {a1} buildings in the city.',
                'answers' => ['a1' => 'oldest'],
                'options' => ['a1' => ['oldest', 'older', 'old']],
                'verb_hints' => ['a1' => 'old'],
                'hints' => [
                    'Формула: one of the + superlative + plural noun.',
                    'Приклад: one of the oldest buildings = одна з найстаріших будівель.',
                    'Після superlative йде іменник у множині.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentTenseTagId],
            ],

            // ===== B2 Level: 12 questions =====
            // 1. Affirmative, Present - complex comparison
            [
                'level' => 'B2',
                'question' => 'The {a1} the problem, the {a2} creative the solution needs to be.',
                'answers' => ['a1' => 'more complex', 'a2' => 'more'],
                'options' => [
                    'a1' => ['more complex', 'most complex', 'complex'],
                    'a2' => ['more', 'most', 'much'],
                ],
                'verb_hints' => ['a1' => 'complex', 'a2' => 'much'],
                'hints' => [
                    'Формула: The + comparative, the + comparative для складних залежностей.',
                    'Приклад: The more complex, the more creative.',
                    'Обидві частини мають comparative form.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentTenseTagId],
            ],
            // 2. Negative, Present - nowhere near as
            [
                'level' => 'B2',
                'question' => 'The sequel is nowhere near as {a1} as the original film.',
                'answers' => ['a1' => 'captivating'],
                'options' => ['a1' => ['captivating', 'more captivating', 'most captivating']],
                'verb_hints' => ['a1' => 'engaging'],
                'hints' => [
                    'Формула: nowhere near as + adjective + as показує велику різницю.',
                    'Приклад: nowhere near as captivating = далеко не такий захоплюючий.',
                    'Сильніше ніж просто not as...as.',
                ],
                'tag_ids' => [$negativeTagId, $presentTenseTagId],
            ],
            // 3. Interrogative, Present - slightly/marginally
            [
                'level' => 'B2',
                'question' => 'Is the new model even slightly {a1} than the previous one?',
                'answers' => ['a1' => 'more efficient'],
                'options' => ['a1' => ['more efficient', 'most efficient', 'efficient']],
                'verb_hints' => ['a1' => 'efficient'],
                'hints' => [
                    'Формула: slightly/marginally + comparative показує малу різницю.',
                    'Приклад: slightly more efficient = трохи ефективніший.',
                    'Підсилювачі можуть зменшувати або збільшувати ступінь.',
                ],
                'tag_ids' => [$interrogativeTagId, $presentTenseTagId],
            ],
            // 4. Affirmative, Past - all the more
            [
                'level' => 'B2',
                'question' => 'His achievement was all the {a1} remarkable given the circumstances.',
                'answers' => ['a1' => 'more'],
                'options' => ['a1' => ['more', 'most', 'much']],
                'verb_hints' => ['a1' => 'much'],
                'hints' => [
                    'Формула: all the more + adjective підсилює через контекст.',
                    'Приклад: all the more remarkable = тим більш вражаючий.',
                    'Показує, що обставини підсилюють якість.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastTenseTagId],
            ],
            // 5. Negative, Past - not half as
            [
                'level' => 'B2',
                'question' => 'The presentation was not half as {a1} as we had expected.',
                'answers' => ['a1' => 'comprehensive'],
                'options' => ['a1' => ['comprehensive', 'more comprehensive', 'most comprehensive']],
                'verb_hints' => ['a1' => 'thorough'],
                'hints' => [
                    'Формула: not half as + adjective + as означає значно менше.',
                    'Приклад: not half as comprehensive = навіть вполовину не такий повний.',
                    'Ідіоматичний вираз для сильного заперечення.',
                ],
                'tag_ids' => [$negativeTagId, $pastTenseTagId],
            ],
            // 6. Interrogative, Past - superlative + ever
            [
                'level' => 'B2',
                'question' => 'Was that not the {a1} speech you have ever witnessed?',
                'answers' => ['a1' => 'most inspiring'],
                'options' => ['a1' => ['most inspiring', 'more inspiring', 'inspiring']],
                'verb_hints' => ['a1' => 'inspiring'],
                'hints' => [
                    'Формула: the + superlative + ever у риторичному питанні.',
                    'Приклад: the most inspiring ever = найбільш надихаючий.',
                    'Риторичне питання очікує погодження.',
                ],
                'tag_ids' => [$interrogativeTagId, $pastTenseTagId],
            ],
            // 7. Affirmative, Future - increasingly
            [
                'level' => 'B2',
                'question' => 'Resources will become increasingly {a1} in the coming decades.',
                'answers' => ['a1' => 'scarce'],
                'options' => ['a1' => ['scarce', 'scarcer', 'scarcest']],
                'verb_hints' => ['a1' => 'rare'],
                'hints' => [
                    'Формула: increasingly + базова форма показує зростання.',
                    'Приклад: increasingly scarce = дедалі рідкісніший.',
                    'Increasingly замінює more and more.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureTenseTagId],
            ],
            // 8. Negative, Future - not quite as
            [
                'level' => 'B2',
                'question' => 'The results will probably not be quite as {a1} as we hoped.',
                'answers' => ['a1' => 'spectacular'],
                'options' => ['a1' => ['spectacular', 'more spectacular', 'most spectacular']],
                'verb_hints' => ['a1' => 'impressive'],
                'hints' => [
                    'Формула: not quite as + adjective + as = трохи менше.',
                    'Приклад: not quite as spectacular = не зовсім такий вражаючий.',
                    'Показує невелику різницю, м\'яке заперечення.',
                ],
                'tag_ids' => [$negativeTagId, $futureTenseTagId],
            ],
            // 9. Interrogative, Future - considerably
            [
                'level' => 'B2',
                'question' => 'Will the new system be considerably {a1} than the current one?',
                'answers' => ['a1' => 'more reliable'],
                'options' => ['a1' => ['more reliable', 'most reliable', 'reliable']],
                'verb_hints' => ['a1' => 'reliable'],
                'hints' => [
                    'Формула: considerably + comparative показує значну різницю.',
                    'Приклад: considerably more reliable = значно надійніший.',
                    'Considerably = значно, помітно.',
                ],
                'tag_ids' => [$interrogativeTagId, $futureTenseTagId],
            ],
            // 10. Affirmative, Present - second superlative
            [
                'level' => 'B2',
                'question' => 'Tokyo is the second {a1} city in the world.',
                'answers' => ['a1' => 'largest'],
                'options' => ['a1' => ['largest', 'larger', 'large']],
                'verb_hints' => ['a1' => 'large'],
                'hints' => [
                    'Формула: the second/third + superlative для рангування.',
                    'Приклад: the second largest = другий за розміром.',
                    'Числівник стоїть перед superlative.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentTenseTagId],
            ],
            // 11. Affirmative, Present - far from the
            [
                'level' => 'B2',
                'question' => 'This solution is far from the {a1} option available.',
                'answers' => ['a1' => 'best'],
                'options' => ['a1' => ['best', 'better', 'good']],
                'verb_hints' => ['a1' => 'good'],
                'hints' => [
                    'Формула: far from + the + superlative = далеко не найкращий.',
                    'Приклад: far from the best = далеко не найкращий.',
                    'Показує значну різницю від ідеалу.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentTenseTagId],
            ],
            // 12. Negative, Present - in no way
            [
                'level' => 'B2',
                'question' => 'The new design is in no way {a1} than the original.',
                'answers' => ['a1' => 'superior'],
                'options' => ['a1' => ['superior', 'more superior', 'most superior']],
                'verb_hints' => ['a1' => 'better'],
                'hints' => [
                    'Формула: in no way + comparative показує рівність.',
                    'Приклад: in no way superior = аж ніяк не кращий.',
                    'Superior вже є comparative (не more superior).',
                ],
                'tag_ids' => [$negativeTagId, $presentTenseTagId],
            ],

            // ===== C1 Level: 12 questions =====
            // 1. Affirmative, Present - three blanks complex
            [
                'level' => 'C1',
                'question' => 'The {a1} we examine the data, the {a2} evident the patterns become, and the {a3} our conclusions are.',
                'answers' => ['a1' => 'more closely', 'a2' => 'more', 'a3' => 'more accurate'],
                'options' => [
                    'a1' => ['more closely', 'most closely', 'closely'],
                    'a2' => ['more', 'most', 'much'],
                    'a3' => ['more accurate', 'most accurate', 'accurate'],
                ],
                'verb_hints' => ['a1' => 'closely', 'a2' => 'much', 'a3' => 'accurate'],
                'hints' => [
                    'Формула: The + comparative прислівник, the + comparative прикметник для ланцюгової залежності.',
                    'Приклад: The more closely... the more evident... the more accurate.',
                    'Складна конструкція з трьома порівняльними елементами.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentTenseTagId],
            ],
            // 2. Negative, Present - by no means
            [
                'level' => 'C1',
                'question' => 'His approach is by no means {a1} to that of his predecessors.',
                'answers' => ['a1' => 'inferior'],
                'options' => ['a1' => ['inferior', 'more inferior', 'most inferior']],
                'verb_hints' => ['a1' => 'worse'],
                'hints' => [
                    'Формула: by no means + comparative/adjective показує рівність.',
                    'Приклад: by no means inferior = аж ніяк не гірший.',
                    'Inferior, superior, prior не потребують more.',
                ],
                'tag_ids' => [$negativeTagId, $presentTenseTagId],
            ],
            // 3. Interrogative, Present - nuanced comparison
            [
                'level' => 'C1',
                'question' => 'Would the alternative proposal not be substantially {a1} in addressing these concerns?',
                'answers' => ['a1' => 'more effective'],
                'options' => ['a1' => ['more effective', 'most effective', 'effective']],
                'verb_hints' => ['a1' => 'effective'],
                'hints' => [
                    'Формула: substantially + comparative показує значну різницю.',
                    'Приклад: substantially more effective = суттєво ефективніший.',
                    'Риторичне питання з формальним підсилювачем.',
                ],
                'tag_ids' => [$interrogativeTagId, $presentTenseTagId],
            ],
            // 4. Affirmative, Past - three blanks
            [
                'level' => 'C1',
                'question' => 'The research was the {a1} comprehensive, the {a2} conducted, and the {a3} funded in the field.',
                'answers' => ['a1' => 'most', 'a2' => 'best', 'a3' => 'best'],
                'options' => [
                    'a1' => ['most', 'more', 'much'],
                    'a2' => ['best', 'better', 'good'],
                    'a3' => ['best', 'better', 'good'],
                ],
                'verb_hints' => ['a1' => 'much', 'a2' => 'good', 'a3' => 'good'],
                'hints' => [
                    'Формула: the + superlative для серії найвищих якостей.',
                    'Приклад: the most... the best... the best.',
                    'Паралельна структура з трьома superlatives.',
                ],
                'tag_ids' => [$affirmativeTagId, $pastTenseTagId],
            ],
            // 5. Negative, Past - not in the slightest
            [
                'level' => 'C1',
                'question' => 'The outcome was not in the slightest {a1} than what we had anticipated.',
                'answers' => ['a1' => 'worse'],
                'options' => ['a1' => ['worse', 'worst', 'bad']],
                'verb_hints' => ['a1' => 'bad'],
                'hints' => [
                    'Формула: not in the slightest + comparative = зовсім не.',
                    'Приклад: not in the slightest worse = зовсім не гірший.',
                    'Ідіоматичний вираз для сильного заперечення.',
                ],
                'tag_ids' => [$negativeTagId, $pastTenseTagId],
            ],
            // 6. Interrogative, Past - preferable
            [
                'level' => 'C1',
                'question' => 'Would the previous arrangement not have been {a1} to the current one?',
                'answers' => ['a1' => 'preferable'],
                'options' => ['a1' => ['preferable', 'more preferable', 'most preferable']],
                'verb_hints' => ['a1' => 'better'],
                'hints' => [
                    'Формула: preferable + to (не than) для порівняння.',
                    'Приклад: preferable to = кращий за.',
                    'Preferable вже є comparative, не потребує more.',
                ],
                'tag_ids' => [$interrogativeTagId, $pastTenseTagId],
            ],
            // 7. Affirmative, Future - exponentially
            [
                'level' => 'C1',
                'question' => 'The demand for renewable energy will grow exponentially {a1} in the coming years.',
                'answers' => ['a1' => 'faster'],
                'options' => ['a1' => ['faster', 'fastest', 'fast']],
                'verb_hints' => ['a1' => 'fast'],
                'hints' => [
                    'Формула: exponentially + comparative показує стрімке зростання.',
                    'Приклад: exponentially faster = експоненційно швидше.',
                    'Науковий/технічний контекст.',
                ],
                'tag_ids' => [$affirmativeTagId, $futureTenseTagId],
            ],
            // 8. Negative, Future - not remotely
            [
                'level' => 'C1',
                'question' => 'The AI system will not be remotely as {a1} as human intuition.',
                'answers' => ['a1' => 'nuanced'],
                'options' => ['a1' => ['nuanced', 'more nuanced', 'most nuanced']],
                'verb_hints' => ['a1' => 'subtle'],
                'hints' => [
                    'Формула: not remotely as + adjective + as = далеко не такий.',
                    'Приклад: not remotely as nuanced = далеко не такий тонкий.',
                    'Сильне заперечення рівності.',
                ],
                'tag_ids' => [$negativeTagId, $futureTenseTagId],
            ],
            // 9. Interrogative, Future - proportionally
            [
                'level' => 'C1',
                'question' => 'Will the benefits be proportionally {a1} as the investment increases?',
                'answers' => ['a1' => 'greater'],
                'options' => ['a1' => ['greater', 'greatest', 'great']],
                'verb_hints' => ['a1' => 'great'],
                'hints' => [
                    'Формула: proportionally + comparative показує пропорційність.',
                    'Приклад: proportionally greater = пропорційно більший.',
                    'Формальний контекст бізнесу/економіки.',
                ],
                'tag_ids' => [$interrogativeTagId, $futureTenseTagId],
            ],
            // 10. Affirmative, Present - innately
            [
                'level' => 'C1',
                'question' => 'Some species are innately {a1} suited to extreme environments than others.',
                'answers' => ['a1' => 'better'],
                'options' => ['a1' => ['better', 'best', 'good']],
                'verb_hints' => ['a1' => 'good'],
                'hints' => [
                    'Формула: innately + comparative показує вроджену якість.',
                    'Приклад: innately better suited = за природою краще пристосований.',
                    'Науковий контекст біології/екології.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentTenseTagId],
            ],
            // 11. Affirmative, Present - arguably the
            [
                'level' => 'C1',
                'question' => 'This is arguably the {a1} discovery in modern physics.',
                'answers' => ['a1' => 'most significant'],
                'options' => ['a1' => ['most significant', 'more significant', 'significant']],
                'verb_hints' => ['a1' => 'significant'],
                'hints' => [
                    'Формула: arguably + the + superlative пом\'якшує твердження.',
                    'Приклад: arguably the most significant = мабуть, найважливіший.',
                    'Академічний стиль хеджування.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentTenseTagId],
            ],
            // 12. Negative, Present - nothing short of
            [
                'level' => 'C1',
                'question' => 'The transformation was nothing short of the {a1} remarkable change in corporate history.',
                'answers' => ['a1' => 'most'],
                'options' => ['a1' => ['most', 'more', 'much']],
                'verb_hints' => ['a1' => 'much'],
                'hints' => [
                    'Формула: nothing short of + the + superlative = справжній.',
                    'Приклад: nothing short of the most remarkable = не що інше як найвидатніший.',
                    'Ідіоматичний вираз підсилення.',
                ],
                'tag_ids' => [$affirmativeTagId, $presentTenseTagId],
            ],
        ];
    }

    private function buildExplanations(array $optionSets, array $answers, string $level): array
    {
        $explanations = [];
        foreach ($this->flattenOptions($optionSets) as $option) {
            $isCorrect = in_array($option, $answers, true);
            $explanations[$option] = $this->describeOption($option, $isCorrect, $level);
        }

        return $explanations;
    }

    private function describeOption(string $option, bool $isCorrect, string $level): string
    {
        $normalized = mb_strtolower($option);

        if ($isCorrect) {
            if (str_contains($normalized, 'est') || str_contains($normalized, 'most') || str_contains($normalized, 'least')) {
                return '✅ Правильно! Superlative форма використовується для порівняння одного елемента з групою або для вираження найвищого/найнижчого ступеня якості.';
            }

            if (str_contains($normalized, 'er') || str_contains($normalized, 'more') || str_contains($normalized, 'less')) {
                return '✅ Правильно! Comparative форма використовується для порівняння двох елементів із сполучником than або у структурі the...the.';
            }

            if ($normalized === 'better' || $normalized === 'worse' || $normalized === 'farther' || $normalized === 'further') {
                return '✅ Правильно! Це неправильна форма comparative. Запам\'ятай: good→better, bad→worse, far→farther/further.';
            }

            if ($normalized === 'best' || $normalized === 'worst' || $normalized === 'farthest' || $normalized === 'furthest') {
                return '✅ Правильно! Це неправильна форма superlative. Запам\'ятай: good→best, bad→worst, far→farthest/furthest.';
            }

            return '✅ Правильно! Базова форма прикметника використовується у конструкціях as...as для вираження рівності.';
        }

        // Incorrect answers
        if (str_contains($normalized, 'est') || str_contains($normalized, 'most') || str_contains($normalized, 'least')) {
            return '❌ Superlative форма тут не підходить. Superlative використовують, коли порівнюють з групою (the + -est/most), а не з одним об\'єктом.';
        }

        if (str_contains($normalized, 'er') || str_contains($normalized, 'more') || str_contains($normalized, 'less')) {
            return '❌ Comparative форма тут не підходить. Перевір контекст: можливо, потрібна базова форма (as...as) або superlative (the + -est/most).';
        }

        return '❌ Базова форма тут не підходить. Контекст вимагає comparative (-er/more + than) або superlative (the + -est/most).';
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
