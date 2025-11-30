<?php

namespace Database\Seeders\Ai;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class GptDegreesOfComparisonAiSeeder extends QuestionSeeder
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

        $sourceIds = [
            'present' => Source::firstOrCreate(['name' => 'AI: Degrees of Comparison Present Practice'])->id,
            'past' => Source::firstOrCreate(['name' => 'AI: Degrees of Comparison Past Practice'])->id,
            'future' => Source::firstOrCreate(['name' => 'AI: Degrees of Comparison Future Practice'])->id,
            'questions' => Source::firstOrCreate(['name' => 'AI: Degrees of Comparison Question Forms'])->id,
            'mixed' => Source::firstOrCreate(['name' => 'AI: Degrees of Comparison Mixed Forms'])->id,
        ];

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
                'source_id' => $sourceIds[$entry['source']] ?? reset($sourceIds),
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
                'hints' => $this->buildHints(),
                'answers' => $entry['answers'],
                'option_markers' => $this->buildOptionMarkers($entry['options']),
                'explanations' => $this->buildExplanations($entry['options']),
            ];
        }

        $this->seedQuestionData($items, $meta);
    }

    private function buildTags(): array
    {
        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Degrees of Comparison Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Comparatives and Superlatives'],
            ['category' => 'English Grammar Detail']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'Comparative / Superlative Structures'],
            ['category' => 'English Grammar Structure']
        )->id;

        $patternTagId = Tag::firstOrCreate(
            ['name' => 'As ... as Equality Patterns'],
            ['category' => 'English Grammar Pattern']
        )->id;

        $irregularTagId = Tag::firstOrCreate(
            ['name' => 'Irregular Comparison Forms'],
            ['category' => 'English Grammar Focus']
        )->id;

        $timeTagId = Tag::firstOrCreate(
            ['name' => 'Time-frame Comparisons (past/present/future)'],
            ['category' => 'English Grammar Focus']
        )->id;

        return [
            $themeTagId,
            $detailTagId,
            $structureTagId,
            $patternTagId,
            $irregularTagId,
            $timeTagId,
        ];
    }

    private function buildHints(): array
    {
        return [
            'Схема comparative для коротких прикметників: **adjective + -er + than** (clean → cleaner than).',
            'Схема comparative для довших прикметників та прислівників: **more/less + adjective/adverb + than**.',
            'Рівність: **as + adjective/adverb + as**; суперлатив: **the + adjective-est / the most / the least**.',
            'Для незлічуваних іменників вживай **more/less**; для злічуваних – **more/fewer**.',
            'У запитаннях та запереченнях зберігай форму ступенювання, змінюючи лише допоміжне дієслово та порядок.',
        ];
    }

    private function buildExplanations(array $optionSets): array
    {
        $explanations = [];

        foreach ($optionSets as $options) {
            foreach ($options as $option) {
                $normalized = strtolower((string) $option);

                if (! isset($explanations[$option])) {
                    $explanations[$option] = $this->describeOption($normalized);
                }
            }
        }

        return $explanations;
    }

    private function describeOption(string $normalized): string
    {
        if (str_contains($normalized, 'as') && str_contains($normalized, 'as')) {
            return 'Формула as + adjective/adverb + as виражає рівність; перед першим as може стояти not для заперечення.';
        }

        if (str_contains($normalized, 'more') && str_contains($normalized, 'than')) {
            return 'More/less + adjective/adverb + than застосовується для довших слів або кількості, підкреслюючи різницю.';
        }

        if (str_contains($normalized, 'more ')) {
            return 'More + adjective/adverb сигналізує про підсилення ознаки; than може бути явним або зрозумілим з контексту.';
        }

        if (str_contains($normalized, 'less') && str_contains($normalized, 'than')) {
            return 'Less + adjective/adverb + than показує нижчий ступінь ознаки для двох елементів.';
        }

        if (str_contains($normalized, 'less ')) {
            return 'Less + adjective/adverb працює як м’який comparative, особливо коли різниця не велика.';
        }

        if (str_contains($normalized, 'er') && str_contains($normalized, 'than')) {
            return 'Короткі прикметники вживають суфікс -er перед than (tall → taller than) без допоміжних more/most.';
        }

        if (str_contains($normalized, 'better') || str_contains($normalized, 'worse')) {
            return 'Неправильні форми better/worse уживаються замість more good/more bad у порівняннях двох предметів.';
        }

        if (str_contains($normalized, 'est') || str_contains($normalized, 'most') || str_contains($normalized, 'least')) {
            return 'Суперлативи будуються за схемою the + adjective-est / the most / the least + adjective для групи трьох і більше.';
        }

        if (str_contains($normalized, 'fewer')) {
            return 'Fewer вказує на меншу кількість із злічуваними іменниками у множині: fewer + plural noun.';
        }

        return 'Обирай форму, що відповідає функції: порівняння двох елементів, рівність або найвищий ступінь.';
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
            // A1 level — simple present and equality
            ['level' => 'A1', 'source' => 'present', 'question' => 'My bike is {a1} than his.', 'answers' => ['a1' => 'slower'], 'options' => ['a1' => ['slower', 'slowest', 'slow']], 'verb_hints' => ['a1' => 'slow']],
            ['level' => 'A1', 'source' => 'present', 'question' => 'Her sandwich is {a1} than mine.', 'answers' => ['a1' => 'smaller'], 'options' => ['a1' => ['smaller', 'smallest', 'small']], 'verb_hints' => ['a1' => 'small']],
            ['level' => 'A1', 'source' => 'questions', 'question' => 'Is the river {a1} today than yesterday?', 'answers' => ['a1' => 'higher'], 'options' => ['a1' => ['higher', 'highest', 'high']], 'verb_hints' => ['a1' => 'high']],
            ['level' => 'A1', 'source' => 'present', 'question' => 'This puzzle is {a1} than that one.', 'answers' => ['a1' => 'easier'], 'options' => ['a1' => ['easier', 'easy', 'easiest']], 'verb_hints' => ['a1' => 'easy']],
            ['level' => 'A1', 'source' => 'present', 'question' => 'Our dog runs {a1} than the cat.', 'answers' => ['a1' => 'faster'], 'options' => ['a1' => ['faster', 'fastest', 'fast']], 'verb_hints' => ['a1' => 'fast']],
            ['level' => 'A1', 'source' => 'present', 'question' => 'The soup is not as {a1} as yesterday.', 'answers' => ['a1' => 'hot'], 'options' => ['a1' => ['hot', 'hotter', 'hottest']], 'verb_hints' => ['a1' => 'warm']],
            ['level' => 'A1', 'source' => 'questions', 'question' => 'Are these shoes as {a1} as those?', 'answers' => ['a1' => 'comfortable'], 'options' => ['a1' => ['comfortable', 'more comfortable', 'comfortablest']], 'verb_hints' => ['a1' => 'comfy']],
            ['level' => 'A1', 'source' => 'present', 'question' => 'The red apple is {a1} than the green one.', 'answers' => ['a1' => 'sweeter'], 'options' => ['a1' => ['sweeter', 'sweetest', 'sweet']], 'verb_hints' => ['a1' => 'sweet']],
            ['level' => 'A1', 'source' => 'present', 'question' => 'This road is {a1} than the city street.', 'answers' => ['a1' => 'wider'], 'options' => ['a1' => ['wider', 'widest', 'wide']], 'verb_hints' => ['a1' => 'wide']],
            ['level' => 'A1', 'source' => 'present', 'question' => 'Tom is {a1} than Ben.', 'answers' => ['a1' => 'younger'], 'options' => ['a1' => ['younger', 'youngest', 'young']], 'verb_hints' => ['a1' => 'young']],
            ['level' => 'A1', 'source' => 'mixed', 'question' => 'This box is {a1} than it looks.', 'answers' => ['a1' => 'lighter'], 'options' => ['a1' => ['lighter', 'lightest', 'light']], 'verb_hints' => ['a1' => 'light']],
            ['level' => 'A1', 'source' => 'questions', 'question' => 'Is the film as {a1} as the book?', 'answers' => ['a1' => 'long'], 'options' => ['a1' => ['long', 'longer', 'longest']], 'verb_hints' => ['a1' => 'length']],

            // A2 level — negatives, past actions, equality variations
            ['level' => 'A2', 'source' => 'past', 'question' => 'Yesterday was {a1} than today.', 'answers' => ['a1' => 'colder'], 'options' => ['a1' => ['colder', 'coldest', 'cold']], 'verb_hints' => ['a1' => 'cold']],
            ['level' => 'A2', 'source' => 'present', 'question' => 'My story is not as {a1} as hers.', 'answers' => ['a1' => 'interesting'], 'options' => ['a1' => ['interesting', 'more interesting', 'interestinger']], 'verb_hints' => ['a1' => 'engaging']],
            ['level' => 'A2', 'source' => 'past', 'question' => 'Their trip was {a1} than ours.', 'answers' => ['a1' => 'longer'], 'options' => ['a1' => ['longer', 'longest', 'long']], 'verb_hints' => ['a1' => 'long']],
            ['level' => 'A2', 'source' => 'questions', 'question' => 'Were the seats {a1} last week?', 'answers' => ['a1' => 'cheaper'], 'options' => ['a1' => ['cheaper', 'cheap', 'cheapest']], 'verb_hints' => ['a1' => 'cheap']],
            ['level' => 'A2', 'source' => 'mixed', 'question' => 'This river is {a1} than that lake.', 'answers' => ['a1' => 'deeper'], 'options' => ['a1' => ['deeper', 'deepest', 'deep']], 'verb_hints' => ['a1' => 'deep']],
            ['level' => 'A2', 'source' => 'present', 'question' => 'Our car is {a1} than we expected.', 'answers' => ['a1' => 'quieter'], 'options' => ['a1' => ['quieter', 'quiet', 'quietest']], 'verb_hints' => ['a1' => 'quiet']],
            ['level' => 'A2', 'source' => 'questions', 'question' => 'Is the new sofa as {a1} as the old one?', 'answers' => ['a1' => 'soft'], 'options' => ['a1' => ['soft', 'softer', 'softest']], 'verb_hints' => ['a1' => 'cushion']],
            ['level' => 'A2', 'source' => 'past', 'question' => 'The mountain road was {a1} than the highway.', 'answers' => ['a1' => 'narrower'], 'options' => ['a1' => ['narrower', 'narrowest', 'narrow']], 'verb_hints' => ['a1' => 'narrow']],
            ['level' => 'A2', 'source' => 'mixed', 'question' => 'This exercise is {a1} than the last one.', 'answers' => ['a1' => 'harder'], 'options' => ['a1' => ['harder', 'hardest', 'hard']], 'verb_hints' => ['a1' => 'hard']],
            ['level' => 'A2', 'source' => 'questions', 'question' => 'Is your cousin {a1} than your brother?', 'answers' => ['a1' => 'taller'], 'options' => ['a1' => ['taller', 'tallest', 'tall']], 'verb_hints' => ['a1' => 'tall']],
            ['level' => 'A2', 'source' => 'present', 'question' => 'Our plan is not the {a1} idea.', 'answers' => ['a1' => 'best'], 'options' => ['a1' => ['best', 'better', 'good']], 'verb_hints' => ['a1' => 'good']],
            ['level' => 'A2', 'source' => 'mixed', 'question' => 'This hill is {a1} than the last one we climbed.', 'answers' => ['a1' => 'steeper'], 'options' => ['a1' => ['steeper', 'steepest', 'steep']], 'verb_hints' => ['a1' => 'steep']],

            // B1 level — mixed tenses, quantities, future statements
            ['level' => 'B1', 'source' => 'future', 'question' => 'Tomorrow will be {a1} than today.', 'answers' => ['a1' => 'warmer'], 'options' => ['a1' => ['warmer', 'warmest', 'warm']], 'verb_hints' => ['a1' => 'warm']],
            ['level' => 'B1', 'source' => 'past', 'question' => 'The meeting lasted {a1} than we planned.', 'answers' => ['a1' => 'longer'], 'options' => ['a1' => ['longer', 'longest', 'long']], 'verb_hints' => ['a1' => 'long']],
            ['level' => 'B1', 'source' => 'mixed', 'question' => 'She sings {a1} than her sister.', 'answers' => ['a1' => 'more beautifully'], 'options' => ['a1' => ['more beautifully', 'beautifulest', 'beautifuller']], 'verb_hints' => ['a1' => 'beautiful']],
            ['level' => 'B1', 'source' => 'present', 'question' => 'This exam is {a1} than the previous one.', 'answers' => ['a1' => 'less difficult'], 'options' => ['a1' => ['less difficult', 'difficulter', 'most difficult']], 'verb_hints' => ['a1' => 'difficult']],
            ['level' => 'B1', 'source' => 'questions', 'question' => 'Will the new park be as {a1} as the old one?', 'answers' => ['a1' => 'big'], 'options' => ['a1' => ['big', 'bigger', 'biggest']], 'verb_hints' => ['a1' => 'size']],
            ['level' => 'B1', 'source' => 'mixed', 'question' => 'This year, our sales are {a1} than last year.', 'answers' => ['a1' => 'higher'], 'options' => ['a1' => ['higher', 'highest', 'high']], 'verb_hints' => ['a1' => 'high']],
            ['level' => 'B1', 'source' => 'past', 'question' => 'The traffic was {a1} than usual.', 'answers' => ['a1' => 'heavier'], 'options' => ['a1' => ['heavier', 'heavy', 'heaviest']], 'verb_hints' => ['a1' => 'heavy']],
            ['level' => 'B1', 'source' => 'future', 'question' => 'Next week will be the {a1} part of the trip.', 'answers' => ['a1' => 'most exciting'], 'options' => ['a1' => ['most exciting', 'more exciting', 'excitingest']], 'verb_hints' => ['a1' => 'excite']],
            ['level' => 'B1', 'source' => 'questions', 'question' => 'Is this laptop {a1} than that tablet?', 'answers' => ['a1' => 'more expensive'], 'options' => ['a1' => ['more expensive', 'expensiver', 'most expensive']], 'verb_hints' => ['a1' => 'expensive']],
            ['level' => 'B1', 'source' => 'mixed', 'question' => 'We need {a1} chairs for the hall.', 'answers' => ['a1' => 'fewer'], 'options' => ['a1' => ['fewer', 'less', 'fewest']], 'verb_hints' => ['a1' => 'few']],
            ['level' => 'B1', 'source' => 'present', 'question' => 'Their garden is {a1} than ours.', 'answers' => ['a1' => 'greener'], 'options' => ['a1' => ['greener', 'greenest', 'green']], 'verb_hints' => ['a1' => 'green']],
            ['level' => 'B1', 'source' => 'mixed', 'question' => 'This sofa feels {a1} than the chair.', 'answers' => ['a1' => 'more comfortable'], 'options' => ['a1' => ['more comfortable', 'comfortabler', 'most comfortable']], 'verb_hints' => ['a1' => 'comfort']],

            // B2 level — complex clauses, negatives, future comparisons
            ['level' => 'B2', 'source' => 'future', 'question' => 'The new model will be {a1} than the current one.', 'answers' => ['a1' => 'more reliable'], 'options' => ['a1' => ['more reliable', 'reliabler', 'most reliable']], 'verb_hints' => ['a1' => 'rely']],
            ['level' => 'B2', 'source' => 'past', 'question' => 'Her explanation was not as {a1} as the teacher hoped.', 'answers' => ['a1' => 'clear'], 'options' => ['a1' => ['clear', 'clearer', 'clearest']], 'verb_hints' => ['a1' => 'clarify']],
            ['level' => 'B2', 'source' => 'mixed', 'question' => 'The river became {a1} after the storm.', 'answers' => ['a1' => 'wider'], 'options' => ['a1' => ['wider', 'widest', 'wide']], 'verb_hints' => ['a1' => 'wide']],
            ['level' => 'B2', 'source' => 'questions', 'question' => 'Is this policy {a1} than the previous one for small firms?', 'answers' => ['a1' => 'more flexible'], 'options' => ['a1' => ['more flexible', 'flexibler', 'most flexible']], 'verb_hints' => ['a1' => 'flexible']],
            ['level' => 'B2', 'source' => 'present', 'question' => 'Our solution is {a1} than theirs because it scales faster.', 'answers' => ['a1' => 'more practical'], 'options' => ['a1' => ['more practical', 'practicaler', 'most practical']], 'verb_hints' => ['a1' => 'practical']],
            ['level' => 'B2', 'source' => 'past', 'question' => 'The train was {a1} crowded than usual.', 'answers' => ['a1' => 'less'], 'options' => ['a1' => ['less', 'fewer', 'least']], 'verb_hints' => ['a1' => 'reduced']],
            ['level' => 'B2', 'source' => 'present', 'question' => 'This path is not as {a1} as it looks on the map.', 'answers' => ['a1' => 'dangerous'], 'options' => ['a1' => ['dangerous', 'more dangerous', 'dangerouser']], 'verb_hints' => ['a1' => 'risky']],
            ['level' => 'B2', 'source' => 'mixed', 'question' => 'He finished the task {a1} than anyone else.', 'answers' => ['a1' => 'faster'], 'options' => ['a1' => ['faster', 'fastest', 'fast']], 'verb_hints' => ['a1' => 'fast']],
            ['level' => 'B2', 'source' => 'questions', 'question' => 'Will the revised plan be the {a1} option available?', 'answers' => ['a1' => 'least costly'], 'options' => ['a1' => ['least costly', 'less costly', 'most costly']], 'verb_hints' => ['a1' => 'cost']],
            ['level' => 'B2', 'source' => 'future', 'question' => 'Next quarter should be {a1} challenging than this one.', 'answers' => ['a1' => 'less'], 'options' => ['a1' => ['less', 'fewer', 'least']], 'verb_hints' => ['a1' => 'reduced']],
            ['level' => 'B2', 'source' => 'mixed', 'question' => 'Her draft is {a1} than the version we saw last week.', 'answers' => ['a1' => 'more concise'], 'options' => ['a1' => ['more concise', 'conciser', 'most concise']], 'verb_hints' => ['a1' => 'concise']],
            ['level' => 'B2', 'source' => 'present', 'question' => 'The new stadium is {a1} than the old arena.', 'answers' => ['a1' => 'larger'], 'options' => ['a1' => ['larger', 'largest', 'large']], 'verb_hints' => ['a1' => 'large']],

            // C1 level — nuanced contexts, mixed forms, complex sentences
            ['level' => 'C1', 'source' => 'present', 'question' => 'Her argument is {a1} than his because it addresses counterpoints.', 'answers' => ['a1' => 'more persuasive'], 'options' => ['a1' => ['more persuasive', 'persuasiver', 'most persuasive']], 'verb_hints' => ['a1' => 'persuade']],
            ['level' => 'C1', 'source' => 'mixed', 'question' => 'The report is not as {a1} as the executive summary suggested.', 'answers' => ['a1' => 'comprehensive'], 'options' => ['a1' => ['comprehensive', 'more comprehensive', 'comprehensivest']], 'verb_hints' => ['a1' => 'complete']],
            ['level' => 'C1', 'source' => 'future', 'question' => 'Our next campaign should be {a1} targeted than this one.', 'answers' => ['a1' => 'more precisely'], 'options' => ['a1' => ['more precisely', 'preciselyer', 'most precisely']], 'verb_hints' => ['a1' => 'precise']],
            ['level' => 'C1', 'source' => 'past', 'question' => 'The negotiation was {a1} balanced than the last round.', 'answers' => ['a1' => 'more delicately'], 'options' => ['a1' => ['more delicately', 'delicatelyer', 'most delicately']], 'verb_hints' => ['a1' => 'delicate']],
            ['level' => 'C1', 'source' => 'questions', 'question' => 'Is the revised timeline {a1} realistic than the first draft?', 'answers' => ['a1' => 'more'], 'options' => ['a1' => ['more', 'most', 'less']], 'verb_hints' => ['a1' => 'increase']],
            ['level' => 'C1', 'source' => 'mixed', 'question' => 'His critique was {a1} nuanced than mine.', 'answers' => ['a1' => 'more'], 'options' => ['a1' => ['more', 'most', 'less']], 'verb_hints' => ['a1' => 'increase']],
            ['level' => 'C1', 'source' => 'present', 'question' => 'These findings are {a1} significant than the preliminary data.', 'answers' => ['a1' => 'more'], 'options' => ['a1' => ['more', 'most', 'less']], 'verb_hints' => ['a1' => 'increase']],
            ['level' => 'C1', 'source' => 'future', 'question' => 'The revised budget is expected to be {a1} restrictive than anticipated.', 'answers' => ['a1' => 'less'], 'options' => ['a1' => ['less', 'least', 'fewer']], 'verb_hints' => ['a1' => 'reduce']],
            ['level' => 'C1', 'source' => 'past', 'question' => 'Her tone sounded {a1} conciliatory after the apology.', 'answers' => ['a1' => 'more'], 'options' => ['a1' => ['more', 'most', 'less']], 'verb_hints' => ['a1' => 'increase']],
            ['level' => 'C1', 'source' => 'mixed', 'question' => 'The proposal is {a1} aligned with our strategy than before.', 'answers' => ['a1' => 'better'], 'options' => ['a1' => ['better', 'best', 'good']], 'verb_hints' => ['a1' => 'good']],
            ['level' => 'C1', 'source' => 'questions', 'question' => 'Will their solution be the {a1} feasible among the options?', 'answers' => ['a1' => 'most'], 'options' => ['a1' => ['most', 'more', 'less']], 'verb_hints' => ['a1' => 'highest']],
            ['level' => 'C1', 'source' => 'present', 'question' => 'The data set is {a1} representative than last year’s sample.', 'answers' => ['a1' => 'more'], 'options' => ['a1' => ['more', 'most', 'less']], 'verb_hints' => ['a1' => 'increase']],
        ];
    }
}
