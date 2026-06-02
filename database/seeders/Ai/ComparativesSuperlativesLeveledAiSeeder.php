<?php

namespace Database\Seeders\Ai;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class ComparativesSuperlativesLeveledAiSeeder extends QuestionSeeder
{
    private array $levelDifficulty = [
        'A1' => 1,
        'A2' => 2,
        'B1' => 3,
        'B2' => 4,
        'C1' => 5,
        'C2' => 6,
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Adjectives and Adverbs'])->id;

        $sourceIds = [
            'negative' => Source::firstOrCreate(['name' => 'AI: Comparatives & Superlatives Negative Forms'])->id,
            'interrogative' => Source::firstOrCreate(['name' => 'AI: Comparatives & Superlatives Questions'])->id,
            'past' => Source::firstOrCreate(['name' => 'AI: Comparatives & Superlatives Past Tense'])->id,
            'present' => Source::firstOrCreate(['name' => 'AI: Comparatives & Superlatives Present Tense'])->id,
            'future' => Source::firstOrCreate(['name' => 'AI: Comparatives & Superlatives Future Tense'])->id,
        ];

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
        return [
            // ===== A1 Level: Basic comparatives with single blank, simple patterns =====
            [
                'level' => 'A1',
                'source' => 'present',
                'question' => 'Is the new train {a1} than the old one?',
                'answers' => ['a1' => 'faster'],
                'options' => ['a1' => ['faster', 'fastest', 'fast']],
                'verb_hints' => ['a1' => 'fast'],
            ],
            [
                'level' => 'A1',
                'source' => 'present',
                'question' => 'The desert is {a1} than the forest.',
                'answers' => ['a1' => 'drier'],
                'options' => ['a1' => ['drier', 'dry', 'driest']],
                'verb_hints' => ['a1' => 'dry'],
            ],
            [
                'level' => 'A1',
                'source' => 'present',
                'question' => 'He is {a1} than me.',
                'answers' => ['a1' => 'taller'],
                'options' => ['a1' => ['taller', 'tallest', 'tall']],
                'verb_hints' => ['a1' => 'tall'],
            ],
            [
                'level' => 'A1',
                'source' => 'present',
                'question' => 'My bag is {a1} than yours.',
                'answers' => ['a1' => 'heavier'],
                'options' => ['a1' => ['heavier', 'heaviest', 'heavy']],
                'verb_hints' => ['a1' => 'heavy'],
            ],
            [
                'level' => 'A1',
                'source' => 'present',
                'question' => 'The weather gets {a1} every day.',
                'answers' => ['a1' => 'colder'],
                'options' => ['a1' => ['colder', 'coldest', 'cold']],
                'verb_hints' => ['a1' => 'cold'],
            ],
            [
                'level' => 'A1',
                'source' => 'present',
                'question' => 'This book is {a1} than that one.',
                'answers' => ['a1' => 'thicker'],
                'options' => ['a1' => ['thicker', 'thickest', 'thick']],
                'verb_hints' => ['a1' => 'thick'],
            ],
            [
                'level' => 'A1',
                'source' => 'present',
                'question' => 'Summer is {a1} than winter.',
                'answers' => ['a1' => 'warmer'],
                'options' => ['a1' => ['warmer', 'warmest', 'warm']],
                'verb_hints' => ['a1' => 'warm'],
            ],
            [
                'level' => 'A1',
                'source' => 'present',
                'question' => 'The cat is {a1} than the dog.',
                'answers' => ['a1' => 'smaller'],
                'options' => ['a1' => ['smaller', 'smallest', 'small']],
                'verb_hints' => ['a1' => 'small'],
            ],

            // ===== A2 Level: Simple patterns with as...as, basic superlatives =====
            [
                'level' => 'A2',
                'source' => 'present',
                'question' => 'Is this path as {a1} as the other?',
                'answers' => ['a1' => 'safe'],
                'options' => ['a1' => ['safe', 'safely', 'safer']],
                'verb_hints' => ['a1' => 'secure'],
            ],
            [
                'level' => 'A2',
                'source' => 'negative',
                'question' => "This soup isn't as {a1} as yesterday's.",
                'answers' => ['a1' => 'tasty'],
                'options' => ['a1' => ['tasty', 'tastier', 'tastiest']],
                'verb_hints' => ['a1' => 'delicious'],
            ],
            [
                'level' => 'A2',
                'source' => 'negative',
                'question' => 'She is not the {a1} runner in the group.',
                'answers' => ['a1' => 'fastest'],
                'options' => ['a1' => ['fastest', 'faster', 'fast']],
                'verb_hints' => ['a1' => 'fast'],
            ],
            [
                'level' => 'A2',
                'source' => 'past',
                'question' => 'Her story was {a1} than his.',
                'answers' => ['a1' => 'clearer'],
                'options' => ['a1' => ['clearer', 'clearest', 'clear']],
                'verb_hints' => ['a1' => 'clear'],
            ],
            [
                'level' => 'A2',
                'source' => 'past',
                'question' => 'Was the river {a1} yesterday than today?',
                'answers' => ['a1' => 'higher'],
                'options' => ['a1' => ['higher', 'highest', 'high']],
                'verb_hints' => ['a1' => 'high'],
            ],
            [
                'level' => 'A2',
                'source' => 'past',
                'question' => 'I felt {a1} about the news than before.',
                'answers' => ['a1' => 'better'],
                'options' => ['a1' => ['better', 'best', 'well']],
                'verb_hints' => ['a1' => 'good'],
            ],
            [
                'level' => 'A2',
                'source' => 'future',
                'question' => 'Next month will be {a1} than this one.',
                'answers' => ['a1' => 'busier'],
                'options' => ['a1' => ['busier', 'busiest', 'busy']],
                'verb_hints' => ['a1' => 'busy'],
            ],
            [
                'level' => 'A2',
                'source' => 'future',
                'question' => 'Will the road be {a1} next year?',
                'answers' => ['a1' => 'wider'],
                'options' => ['a1' => ['wider', 'widest', 'wide']],
                'verb_hints' => ['a1' => 'wide'],
            ],

            // ===== B1 Level: Two blanks, comparative + superlative mix =====
            [
                'level' => 'B1',
                'source' => 'interrogative',
                'question' => 'Is the train {a1} than the bus, or is it as {a2} as the tram?',
                'answers' => ['a1' => 'faster', 'a2' => 'fast'],
                'options' => [
                    'a1' => ['faster', 'fastest', 'fast'],
                    'a2' => ['fast', 'faster', 'fastest'],
                ],
                'verb_hints' => ['a1' => 'fast', 'a2' => 'quick'],
            ],
            [
                'level' => 'B1',
                'source' => 'negative',
                'question' => "This soup isn't as {a1} as yesterday's; it was {a2} salty then.",
                'answers' => ['a1' => 'tasty', 'a2' => 'less'],
                'options' => [
                    'a1' => ['tasty', 'tastier', 'tastiest'],
                    'a2' => ['less', 'least', 'fewer'],
                ],
                'verb_hints' => ['a1' => 'delicious', 'a2' => 'little'],
            ],
            [
                'level' => 'B1',
                'source' => 'present',
                'question' => 'The desert is {a1} than the forest, and the nights feel {a2} than at home.',
                'answers' => ['a1' => 'drier', 'a2' => 'colder'],
                'options' => [
                    'a1' => ['drier', 'dry', 'driest'],
                    'a2' => ['colder', 'coldest', 'cold'],
                ],
                'verb_hints' => ['a1' => 'dry', 'a2' => 'cold'],
            ],
            [
                'level' => 'B1',
                'source' => 'present',
                'question' => 'He is {a1} than anyone else here, and his sister is the {a2} in the family.',
                'answers' => ['a1' => 'taller', 'a2' => 'shortest'],
                'options' => [
                    'a1' => ['taller', 'tallest', 'tall'],
                    'a2' => ['shortest', 'shorter', 'short'],
                ],
                'verb_hints' => ['a1' => 'tall', 'a2' => 'short'],
            ],
            [
                'level' => 'B1',
                'source' => 'present',
                'question' => 'My suitcase is {a1} than yours, but yours is as {a2} as mine.',
                'answers' => ['a1' => 'heavier', 'a2' => 'large'],
                'options' => [
                    'a1' => ['heavier', 'heaviest', 'heavy'],
                    'a2' => ['large', 'larger', 'largest'],
                ],
                'verb_hints' => ['a1' => 'heavy', 'a2' => 'big'],
            ],
            [
                'level' => 'B1',
                'source' => 'present',
                'question' => 'The weather gets {a1} and {a2} each day.',
                'answers' => ['a1' => 'colder', 'a2' => 'windier'],
                'options' => [
                    'a1' => ['colder', 'coldest', 'cold'],
                    'a2' => ['windier', 'windiest', 'windy'],
                ],
                'verb_hints' => ['a1' => 'cold', 'a2' => 'windy'],
            ],
            [
                'level' => 'B1',
                'source' => 'past',
                'question' => 'Her story was {a1} than his, and her voice sounded {a2}.',
                'answers' => ['a1' => 'clearer', 'a2' => 'calmer'],
                'options' => [
                    'a1' => ['clearer', 'clearest', 'clear'],
                    'a2' => ['calmer', 'calmest', 'calm'],
                ],
                'verb_hints' => ['a1' => 'clear', 'a2' => 'calm'],
            ],
            [
                'level' => 'B1',
                'source' => 'past',
                'question' => 'She walked {a1} than I expected because the road was {a2}.',
                'answers' => ['a1' => 'more slowly', 'a2' => 'rougher'],
                'options' => [
                    'a1' => ['more slowly', 'slower', 'slow'],
                    'a2' => ['rougher', 'roughest', 'rough'],
                ],
                'verb_hints' => ['a1' => 'slowly', 'a2' => 'rough'],
            ],

            // ===== B2 Level: Complex patterns, irregular forms, quantity words =====
            [
                'level' => 'B2',
                'source' => 'interrogative',
                'question' => 'Is this path as {a1} as the other, or is it {a2}?',
                'answers' => ['a1' => 'safe', 'a2' => 'safer'],
                'options' => [
                    'a1' => ['safe', 'safely', 'safer'],
                    'a2' => ['safer', 'safest', 'safe'],
                ],
                'verb_hints' => ['a1' => 'secure', 'a2' => 'safe'],
            ],
            [
                'level' => 'B2',
                'source' => 'negative',
                'question' => 'She is not the {a1} runner in the group; Mia is even {a2}.',
                'answers' => ['a1' => 'fastest', 'a2' => 'faster'],
                'options' => [
                    'a1' => ['fastest', 'faster', 'fast'],
                    'a2' => ['faster', 'fastest', 'fast'],
                ],
                'verb_hints' => ['a1' => 'fast', 'a2' => 'quick'],
            ],
            [
                'level' => 'B2',
                'source' => 'past',
                'question' => 'Was the river {a1} yesterday than today, or was it as {a2} as usual?',
                'answers' => ['a1' => 'higher', 'a2' => 'high'],
                'options' => [
                    'a1' => ['higher', 'highest', 'high'],
                    'a2' => ['high', 'higher', 'highest'],
                ],
                'verb_hints' => ['a1' => 'high', 'a2' => 'tall'],
            ],
            [
                'level' => 'B2',
                'source' => 'negative',
                'question' => "The exam wasn't {a1} difficult as the mock test; the essay was the {a2} part.",
                'answers' => ['a1' => 'as', 'a2' => 'hardest'],
                'options' => [
                    'a1' => ['as', 'so', 'more'],
                    'a2' => ['hardest', 'harder', 'hard'],
                ],
                'verb_hints' => ['a1' => 'equally', 'a2' => 'hard'],
            ],
            [
                'level' => 'B2',
                'source' => 'past',
                'question' => 'I felt {a1} about the news than before and was the {a2} hopeful I had been all week.',
                'answers' => ['a1' => 'better', 'a2' => 'most'],
                'options' => [
                    'a1' => ['better', 'best', 'good'],
                    'a2' => ['most', 'more', 'much'],
                ],
                'verb_hints' => ['a1' => 'good', 'a2' => 'much'],
            ],
            [
                'level' => 'B2',
                'source' => 'past',
                'question' => 'He used the {a1} amount of sugar possible, so the cake tasted {a2} sweet than usual.',
                'answers' => ['a1' => 'least', 'a2' => 'less'],
                'options' => [
                    'a1' => ['least', 'less', 'fewer'],
                    'a2' => ['less', 'least', 'fewer'],
                ],
                'verb_hints' => ['a1' => 'little', 'a2' => 'little'],
            ],
            [
                'level' => 'B2',
                'source' => 'past',
                'question' => 'Their solution was the {a1} of all and worked {a2} than expected.',
                'answers' => ['a1' => 'simplest', 'a2' => 'better'],
                'options' => [
                    'a1' => ['simplest', 'simpler', 'simple'],
                    'a2' => ['better', 'best', 'good'],
                ],
                'verb_hints' => ['a1' => 'simple', 'a2' => 'good'],
            ],
            [
                'level' => 'B2',
                'source' => 'future',
                'question' => "Tomorrow's meeting will be the {a1} in the week and start {a2} than usual.",
                'answers' => ['a1' => 'shortest', 'a2' => 'earlier'],
                'options' => [
                    'a1' => ['shortest', 'shorter', 'short'],
                    'a2' => ['earlier', 'earliest', 'early'],
                ],
                'verb_hints' => ['a1' => 'short', 'a2' => 'early'],
            ],
            [
                'level' => 'B2',
                'source' => 'future',
                'question' => 'Next month will be {a1} than this one, and the deadlines will come {a2}.',
                'answers' => ['a1' => 'busier', 'a2' => 'sooner'],
                'options' => [
                    'a1' => ['busier', 'busiest', 'busy'],
                    'a2' => ['sooner', 'soonest', 'soon'],
                ],
                'verb_hints' => ['a1' => 'busy', 'a2' => 'soon'],
            ],
            [
                'level' => 'B2',
                'source' => 'negative',
                'question' => 'The cafe will not be {a1} than the restaurant; meals there will cost {a2}.',
                'answers' => ['a1' => 'cheaper', 'a2' => 'more'],
                'options' => [
                    'a1' => ['cheaper', 'cheapest', 'cheap'],
                    'a2' => ['more', 'most', 'much'],
                ],
                'verb_hints' => ['a1' => 'cheap', 'a2' => 'much'],
            ],
            [
                'level' => 'B2',
                'source' => 'interrogative',
                'question' => 'Will the road be {a1} next year, or remain as {a2} as now?',
                'answers' => ['a1' => 'wider', 'a2' => 'wide'],
                'options' => [
                    'a1' => ['wider', 'widest', 'wide'],
                    'a2' => ['wide', 'wider', 'widest'],
                ],
                'verb_hints' => ['a1' => 'wide', 'a2' => 'broad'],
            ],
            [
                'level' => 'B2',
                'source' => 'future',
                'question' => 'There will be {a1} apples than oranges at the market, so buy the {a2} ones early.',
                'answers' => ['a1' => 'more', 'a2' => 'freshest'],
                'options' => [
                    'a1' => ['more', 'most', 'many'],
                    'a2' => ['freshest', 'fresher', 'fresh'],
                ],
                'verb_hints' => ['a1' => 'many', 'a2' => 'fresh'],
            ],
            [
                'level' => 'B2',
                'source' => 'future',
                'question' => 'Tomorrow I will pack the {a1} clothes for the trip to carry the {a2} weight.',
                'answers' => ['a1' => 'lightest', 'a2' => 'least'],
                'options' => [
                    'a1' => ['lightest', 'lighter', 'light'],
                    'a2' => ['least', 'less', 'fewer'],
                ],
                'verb_hints' => ['a1' => 'light', 'a2' => 'little'],
            ],

            // ===== C1 Level: Three blanks, mixed comparative/superlative/equality, complex sentences =====
            [
                'level' => 'C1',
                'source' => 'interrogative',
                'question' => 'When comparing trains, is the new model {a1} than the old one, {a2} than the commuter line, and still as {a3} as the express?',
                'answers' => ['a1' => 'faster', 'a2' => 'quieter', 'a3' => 'reliable'],
                'options' => [
                    'a1' => ['faster', 'fastest', 'fast'],
                    'a2' => ['quieter', 'quiet', 'quietest'],
                    'a3' => ['reliable', 'more reliable', 'most reliable'],
                ],
                'verb_hints' => ['a1' => 'fast', 'a2' => 'quiet', 'a3' => 'dependable'],
            ],
            [
                'level' => 'C1',
                'source' => 'negative',
                'question' => "This soup isn't as {a1} as yesterday's, the bread is {a2} fresh than usual, and the dessert is the {a3} surprise of all.",
                'answers' => ['a1' => 'tasty', 'a2' => 'less', 'a3' => 'biggest'],
                'options' => [
                    'a1' => ['tasty', 'tastier', 'tastiest'],
                    'a2' => ['less', 'least', 'fewer'],
                    'a3' => ['biggest', 'bigger', 'big'],
                ],
                'verb_hints' => ['a1' => 'delicious', 'a2' => 'little', 'a3' => 'big'],
            ],
            [
                'level' => 'C1',
                'source' => 'present',
                'question' => 'The desert is {a1} than the forest, the wind is {a2} than yesterday, and the dunes look the {a3} we have seen.',
                'answers' => ['a1' => 'drier', 'a2' => 'stronger', 'a3' => 'largest'],
                'options' => [
                    'a1' => ['drier', 'dry', 'driest'],
                    'a2' => ['stronger', 'strongest', 'strong'],
                    'a3' => ['largest', 'larger', 'large'],
                ],
                'verb_hints' => ['a1' => 'dry', 'a2' => 'strong', 'a3' => 'large'],
            ],
            [
                'level' => 'C1',
                'source' => 'interrogative',
                'question' => 'Is this path as {a1} as the other, {a2} than the rocky trail, and the {a3} choice for kids?',
                'answers' => ['a1' => 'safe', 'a2' => 'safer', 'a3' => 'best'],
                'options' => [
                    'a1' => ['safe', 'safely', 'safer'],
                    'a2' => ['safer', 'safest', 'safe'],
                    'a3' => ['best', 'better', 'good'],
                ],
                'verb_hints' => ['a1' => 'secure', 'a2' => 'safe', 'a3' => 'good'],
            ],
            [
                'level' => 'C1',
                'source' => 'present',
                'question' => 'He is {a1} than anyone here, his cousin is even {a2}, and their aunt is the {a3} adult in the group.',
                'answers' => ['a1' => 'taller', 'a2' => 'taller', 'a3' => 'oldest'],
                'options' => [
                    'a1' => ['taller', 'tallest', 'tall'],
                    'a2' => ['taller', 'tallest', 'tall'],
                    'a3' => ['oldest', 'older', 'old'],
                ],
                'verb_hints' => ['a1' => 'tall', 'a2' => 'tall', 'a3' => 'old'],
            ],
            [
                'level' => 'C1',
                'source' => 'present',
                'question' => 'My suitcase is {a1} than yours, the cabin bag is {a2}, and the duffel is the {a3} of all.',
                'answers' => ['a1' => 'heavier', 'a2' => 'lighter', 'a3' => 'lightest'],
                'options' => [
                    'a1' => ['heavier', 'heaviest', 'heavy'],
                    'a2' => ['lighter', 'lightest', 'light'],
                    'a3' => ['lightest', 'lighter', 'light'],
                ],
                'verb_hints' => ['a1' => 'heavy', 'a2' => 'light', 'a3' => 'light'],
            ],
            [
                'level' => 'C1',
                'source' => 'negative',
                'question' => 'She is not the {a1} runner in the group, her brother is {a2}, and their coach is the {a3} supporter they have.',
                'answers' => ['a1' => 'fastest', 'a2' => 'faster', 'a3' => 'most patient'],
                'options' => [
                    'a1' => ['fastest', 'faster', 'fast'],
                    'a2' => ['faster', 'fastest', 'fast'],
                    'a3' => ['most patient', 'more patient', 'patient'],
                ],
                'verb_hints' => ['a1' => 'fast', 'a2' => 'fast', 'a3' => 'patient'],
            ],
            [
                'level' => 'C1',
                'source' => 'present',
                'question' => 'The weather gets {a1} every day, the wind blows {a2} than last week, and the mornings are the {a3} part.',
                'answers' => ['a1' => 'colder', 'a2' => 'harder', 'a3' => 'coldest'],
                'options' => [
                    'a1' => ['colder', 'coldest', 'cold'],
                    'a2' => ['harder', 'hardest', 'hard'],
                    'a3' => ['coldest', 'colder', 'cold'],
                ],
                'verb_hints' => ['a1' => 'cold', 'a2' => 'hard', 'a3' => 'cold'],
            ],
            [
                'level' => 'C1',
                'source' => 'past',
                'question' => 'Her story was {a1} than his, the ending was {a2} than expected, and her points were the {a3} of the evening.',
                'answers' => ['a1' => 'clearer', 'a2' => 'sharper', 'a3' => 'strongest'],
                'options' => [
                    'a1' => ['clearer', 'clearest', 'clear'],
                    'a2' => ['sharper', 'sharpest', 'sharp'],
                    'a3' => ['strongest', 'stronger', 'strong'],
                ],
                'verb_hints' => ['a1' => 'clear', 'a2' => 'sharp', 'a3' => 'strong'],
            ],
            [
                'level' => 'C1',
                'source' => 'past',
                'question' => 'Was the river {a1} yesterday, {a2} in the morning, and the {a3} at noon?',
                'answers' => ['a1' => 'higher', 'a2' => 'calmer', 'a3' => 'highest'],
                'options' => [
                    'a1' => ['higher', 'highest', 'high'],
                    'a2' => ['calmer', 'calmest', 'calm'],
                    'a3' => ['highest', 'higher', 'high'],
                ],
                'verb_hints' => ['a1' => 'high', 'a2' => 'calm', 'a3' => 'high'],
            ],
            [
                'level' => 'C1',
                'source' => 'past',
                'question' => 'She walked {a1} than I expected, climbed {a2} than before, and finished with the {a3} time.',
                'answers' => ['a1' => 'more slowly', 'a2' => 'faster', 'a3' => 'best'],
                'options' => [
                    'a1' => ['more slowly', 'slower', 'slow'],
                    'a2' => ['faster', 'fastest', 'fast'],
                    'a3' => ['best', 'better', 'good'],
                ],
                'verb_hints' => ['a1' => 'slowly', 'a2' => 'fast', 'a3' => 'good'],
            ],
            [
                'level' => 'C1',
                'source' => 'negative',
                'question' => "The exam wasn't {a1} difficult as the mock test, the speaking part was {a2} stressful, and the writing was the {a3} section.",
                'answers' => ['a1' => 'as', 'a2' => 'less', 'a3' => 'longest'],
                'options' => [
                    'a1' => ['as', 'so', 'more'],
                    'a2' => ['less', 'least', 'fewer'],
                    'a3' => ['longest', 'longer', 'long'],
                ],
                'verb_hints' => ['a1' => 'equally', 'a2' => 'little', 'a3' => 'long'],
            ],
            [
                'level' => 'C1',
                'source' => 'past',
                'question' => 'Were the instructions {a1} than the video, {a2} than the guide, and the {a3} part of the process?',
                'answers' => ['a1' => 'clearer', 'a2' => 'longer', 'a3' => 'easiest'],
                'options' => [
                    'a1' => ['clearer', 'clearest', 'clear'],
                    'a2' => ['longer', 'longest', 'long'],
                    'a3' => ['easiest', 'easier', 'easy'],
                ],
                'verb_hints' => ['a1' => 'clear', 'a2' => 'long', 'a3' => 'easy'],
            ],
            [
                'level' => 'C1',
                'source' => 'past',
                'question' => 'I felt {a1} about the news than before, the morning was {a2} than the evening, and that day was the {a3} of the month.',
                'answers' => ['a1' => 'better', 'a2' => 'brighter', 'a3' => 'happiest'],
                'options' => [
                    'a1' => ['better', 'best', 'good'],
                    'a2' => ['brighter', 'brightest', 'bright'],
                    'a3' => ['happiest', 'happier', 'happy'],
                ],
                'verb_hints' => ['a1' => 'good', 'a2' => 'bright', 'a3' => 'happy'],
            ],
            [
                'level' => 'C1',
                'source' => 'past',
                'question' => 'He used the {a1} amount of sugar possible, added {a2} cream than before, and served the {a3} slice to me.',
                'answers' => ['a1' => 'least', 'a2' => 'less', 'a3' => 'smallest'],
                'options' => [
                    'a1' => ['least', 'less', 'fewer'],
                    'a2' => ['less', 'least', 'fewer'],
                    'a3' => ['smallest', 'smaller', 'small'],
                ],
                'verb_hints' => ['a1' => 'little', 'a2' => 'little', 'a3' => 'small'],
            ],
            [
                'level' => 'C1',
                'source' => 'past',
                'question' => 'Their solution was the {a1} of all, the meeting became {a2} than before, and the feedback was the {a3} we received.',
                'answers' => ['a1' => 'simplest', 'a2' => 'shorter', 'a3' => 'warmest'],
                'options' => [
                    'a1' => ['simplest', 'simpler', 'simple'],
                    'a2' => ['shorter', 'shortest', 'short'],
                    'a3' => ['warmest', 'warmer', 'warm'],
                ],
                'verb_hints' => ['a1' => 'simple', 'a2' => 'short', 'a3' => 'warm'],
            ],
            [
                'level' => 'C1',
                'source' => 'future',
                'question' => "Tomorrow's meeting will be the {a1} in the week, start {a2} than usual, and finish with the {a3} vote.",
                'answers' => ['a1' => 'shortest', 'a2' => 'earlier', 'a3' => 'quickest'],
                'options' => [
                    'a1' => ['shortest', 'shorter', 'short'],
                    'a2' => ['earlier', 'earliest', 'early'],
                    'a3' => ['quickest', 'quicker', 'quick'],
                ],
                'verb_hints' => ['a1' => 'short', 'a2' => 'early', 'a3' => 'quick'],
            ],
            [
                'level' => 'C1',
                'source' => 'future',
                'question' => 'Next month will be {a1} than this one, meetings will start {a2}, and it might be the {a3} season we have.',
                'answers' => ['a1' => 'busier', 'a2' => 'earlier', 'a3' => 'hardest'],
                'options' => [
                    'a1' => ['busier', 'busiest', 'busy'],
                    'a2' => ['earlier', 'earliest', 'early'],
                    'a3' => ['hardest', 'harder', 'hard'],
                ],
                'verb_hints' => ['a1' => 'busy', 'a2' => 'early', 'a3' => 'hard'],
            ],
            [
                'level' => 'C1',
                'source' => 'negative',
                'question' => 'The cafe will not be {a1} than the restaurant, the menu will be {a2} varied, and the desserts might be the {a3} part.',
                'answers' => ['a1' => 'cheaper', 'a2' => 'less', 'a3' => 'sweetest'],
                'options' => [
                    'a1' => ['cheaper', 'cheapest', 'cheap'],
                    'a2' => ['less', 'least', 'fewer'],
                    'a3' => ['sweetest', 'sweeter', 'sweet'],
                ],
                'verb_hints' => ['a1' => 'cheap', 'a2' => 'little', 'a3' => 'sweet'],
            ],
            [
                'level' => 'C1',
                'source' => 'interrogative',
                'question' => 'Will the road be {a1} next year, the bike lane {a2} than before, and the sidewalks the {a3} feature?',
                'answers' => ['a1' => 'wider', 'a2' => 'safer', 'a3' => 'newest'],
                'options' => [
                    'a1' => ['wider', 'widest', 'wide'],
                    'a2' => ['safer', 'safest', 'safe'],
                    'a3' => ['newest', 'newer', 'new'],
                ],
                'verb_hints' => ['a1' => 'wide', 'a2' => 'safe', 'a3' => 'new'],
            ],
            [
                'level' => 'C1',
                'source' => 'interrogative',
                'question' => 'Will we have {a1} chairs than yesterday, {a2} tables than today, and the {a3} space for everyone?',
                'answers' => ['a1' => 'more', 'a2' => 'fewer', 'a3' => 'least'],
                'options' => [
                    'a1' => ['more', 'most', 'many'],
                    'a2' => ['fewer', 'fewest', 'few'],
                    'a3' => ['least', 'less', 'fewer'],
                ],
                'verb_hints' => ['a1' => 'many', 'a2' => 'few', 'a3' => 'little'],
            ],
            [
                'level' => 'C1',
                'source' => 'interrogative',
                'question' => 'Will the lake be {a1} from the town than the sea, the hill even {a2}, and the campsite the {a3} spot we choose?',
                'answers' => ['a1' => 'farther', 'a2' => 'farther', 'a3' => 'closest'],
                'options' => [
                    'a1' => ['farther', 'farthest', 'far'],
                    'a2' => ['farther', 'farthest', 'far'],
                    'a3' => ['closest', 'closer', 'close'],
                ],
                'verb_hints' => ['a1' => 'far', 'a2' => 'far', 'a3' => 'close'],
            ],
            [
                'level' => 'C1',
                'source' => 'future',
                'question' => 'There will be {a1} apples than oranges at the market, {a2} pears than plums, and the {a3} berries by noon.',
                'answers' => ['a1' => 'more', 'a2' => 'fewer', 'a3' => 'sweetest'],
                'options' => [
                    'a1' => ['more', 'most', 'many'],
                    'a2' => ['fewer', 'fewest', 'few'],
                    'a3' => ['sweetest', 'sweeter', 'sweet'],
                ],
                'verb_hints' => ['a1' => 'many', 'a2' => 'few', 'a3' => 'sweet'],
            ],
            [
                'level' => 'C1',
                'source' => 'future',
                'question' => 'Tomorrow I will pack the {a1} clothes, choose {a2} shoes than usual, and take the {a3} number of books.',
                'answers' => ['a1' => 'lightest', 'a2' => 'lighter', 'a3' => 'fewest'],
                'options' => [
                    'a1' => ['lightest', 'lighter', 'light'],
                    'a2' => ['lighter', 'lightest', 'light'],
                    'a3' => ['fewest', 'fewer', 'few'],
                ],
                'verb_hints' => ['a1' => 'light', 'a2' => 'light', 'a3' => 'few'],
            ],
        ];
    }

    private function buildHints(): array
    {
        $formula = 'Формула: comparative = прикметник/прислівник + -er + than; довші слова вживають more/less + adjective. Superlative = -est/most/least.';
        $example = 'Зверни увагу на контекст речення: якщо порівнюються дві речі — використай форму на -er/than, якщо рівність — as ... as, якщо з групи — суперлатив.';
        $reminder = 'Перевір, чи треба рівність (as ... as), порівняння двох предметів (+ than), чи найвищий ступінь (the + -est/most/least).';

        return [$formula, $example, $reminder];
    }

    private function buildExplanations(array $optionSets): array
    {
        $explanations = [];
        foreach ($this->flattenOptions($optionSets) as $option) {
            $explanations[$option] = $this->describeOption($option);
        }

        return $explanations;
    }

    private function describeOption(string $option): string
    {
        $normalized = strtolower($option);

        if (str_contains($normalized, 'est') || str_contains($normalized, 'most')) {
            return 'Форми суперлатива (-est/most) порівнюють один елемент із усією групою; застосовуй їх, коли треба підкреслити найвищий ступінь.';
        }

        if (str_contains($normalized, 'more') || str_contains($normalized, 'less')) {
            return 'Comparatives з more/less показують різницю для довших прикметників або кількості; переконайся, що іменник пасує.';
        }

        if (str_contains($normalized, 'as')) {
            return 'Конструкція as ... as передає рівність; стеж, щоб обидві частини були збалансовані.';
        }

        if (str_contains($normalized, 'fewer') || str_contains($normalized, 'fewest')) {
            return 'Fewer/fewest уживай із злічуваними іменниками у множині при кількісних порівняннях.';
        }

        return 'Добирай форму до мети порівняння: -er/than — для двох предметів, as ... as — для рівності, -est/most/least — для найвищого ступеня.';
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
