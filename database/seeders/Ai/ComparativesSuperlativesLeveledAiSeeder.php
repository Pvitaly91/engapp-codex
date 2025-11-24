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
        }

        $this->seedQuestionData($items, []);
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

    private function resolveSource(string $form, string $tense): string
    {
        if ($form === 'negative') {
            return 'negative';
        }

        if ($form === 'question') {
            return 'interrogative';
        }

        return $tense;
    }

    private function questionEntries(): array
    {
        $levels = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

        $templates = [
            [
                'tense' => 'present',
                'form' => 'question',
                'base' => [
                    'question' => 'Is the new train {a1} than the old one?',
                    'answers' => ['a1' => 'faster'],
                    'options' => ['a1' => ['faster', 'fastest', 'fast']],
                ],
                'advanced' => [
                    'question' => "Is the new train {a1} than the old one, or is it as {a2} as last year's model?",
                    'answers' => ['a1' => 'faster', 'a2' => 'fast'],
                    'options' => [
                        'a1' => ['faster', 'fastest', 'fast'],
                        'a2' => ['fast', 'faster', 'fastest'],
                    ],
                ],
            ],
            [
                'tense' => 'present',
                'form' => 'negative',
                'base' => [
                    'question' => "This soup isn't as {a1} as yesterday's.",
                    'answers' => ['a1' => 'tasty'],
                    'options' => ['a1' => ['tasty', 'tastier', 'tastiest']],
                    'verb_hints' => ['a1' => 'not (it)'],
                ],
                'advanced' => [
                    'question' => "This soup isn't as {a1} as yesterday's; it was {a2} salty then.",
                    'answers' => ['a1' => 'tasty', 'a2' => 'less'],
                    'options' => [
                        'a1' => ['tasty', 'tastier', 'tastiest'],
                        'a2' => ['less', 'least', 'fewer'],
                    ],
                    'verb_hints' => ['a1' => 'not (it)', 'a2' => 'not (it)'],
                ],
            ],
            [
                'tense' => 'present',
                'form' => 'statement',
                'base' => [
                    'question' => 'The desert is {a1} than the forest.',
                    'answers' => ['a1' => 'drier'],
                    'options' => ['a1' => ['drier', 'dry', 'driest']],
                ],
                'advanced' => [
                    'question' => 'The desert is {a1} than the forest, and the nights feel {a2} than at home.',
                    'answers' => ['a1' => 'drier', 'a2' => 'colder'],
                    'options' => [
                        'a1' => ['drier', 'dry', 'driest'],
                        'a2' => ['colder', 'coldest', 'cold'],
                    ],
                ],
            ],
            [
                'tense' => 'present',
                'form' => 'question',
                'base' => [
                    'question' => 'Is this path as {a1} as the other?',
                    'answers' => ['a1' => 'safe'],
                    'options' => ['a1' => ['safe', 'safely', 'safer']],
                ],
                'advanced' => [
                    'question' => 'Is this path as {a1} as the other, or is it {a2}?',
                    'answers' => ['a1' => 'safe', 'a2' => 'safer'],
                    'options' => [
                        'a1' => ['safe', 'safely', 'safer'],
                        'a2' => ['safer', 'safest', 'safe'],
                    ],
                ],
            ],
            [
                'tense' => 'present',
                'form' => 'statement',
                'base' => [
                    'question' => 'He is {a1} than anyone else here.',
                    'answers' => ['a1' => 'taller'],
                    'options' => ['a1' => ['taller', 'tallest', 'tall']],
                ],
                'advanced' => [
                    'question' => 'He is {a1} than anyone else here, and his sister is the {a2} in the family.',
                    'answers' => ['a1' => 'taller', 'a2' => 'shortest'],
                    'options' => [
                        'a1' => ['taller', 'tallest', 'tall'],
                        'a2' => ['shortest', 'shorter', 'short'],
                    ],
                ],
            ],
            [
                'tense' => 'present',
                'form' => 'statement',
                'base' => [
                    'question' => 'My suitcase is {a1} than yours.',
                    'answers' => ['a1' => 'heavier'],
                    'options' => ['a1' => ['heavier', 'heaviest', 'heavy']],
                ],
                'advanced' => [
                    'question' => 'My suitcase is {a1} than yours, but yours is as {a2} as mine.',
                    'answers' => ['a1' => 'heavier', 'a2' => 'large'],
                    'options' => [
                        'a1' => ['heavier', 'heaviest', 'heavy'],
                        'a2' => ['large', 'larger', 'largest'],
                    ],
                ],
            ],
            [
                'tense' => 'present',
                'form' => 'negative',
                'base' => [
                    'question' => 'She is not the {a1} runner in the group.',
                    'answers' => ['a1' => 'fastest'],
                    'options' => ['a1' => ['fastest', 'faster', 'fast']],
                    'verb_hints' => ['a1' => 'not (she)'],
                ],
                'advanced' => [
                    'question' => 'She is not the {a1} runner in the group; Mia is even {a2}.',
                    'answers' => ['a1' => 'fastest', 'a2' => 'faster'],
                    'options' => [
                        'a1' => ['fastest', 'faster', 'fast'],
                        'a2' => ['faster', 'fastest', 'fast'],
                    ],
                    'verb_hints' => ['a1' => 'not (she)'],
                ],
            ],
            [
                'tense' => 'present',
                'form' => 'statement',
                'base' => [
                    'question' => 'The weather gets {a1} every day.',
                    'answers' => ['a1' => 'colder'],
                    'options' => ['a1' => ['colder', 'coldest', 'cold']],
                ],
                'advanced' => [
                    'question' => 'The weather gets {a1} and {a2} each day.',
                    'answers' => ['a1' => 'colder', 'a2' => 'windier'],
                    'options' => [
                        'a1' => ['colder', 'coldest', 'cold'],
                        'a2' => ['windier', 'windiest', 'windy'],
                    ],
                ],
            ],
            [
                'tense' => 'past',
                'form' => 'statement',
                'base' => [
                    'question' => 'Her story was {a1} than his.',
                    'answers' => ['a1' => 'clearer'],
                    'options' => ['a1' => ['clearer', 'clearest', 'clear']],
                ],
                'advanced' => [
                    'question' => 'Her story was {a1} than his, and her voice sounded {a2}.',
                    'answers' => ['a1' => 'clearer', 'a2' => 'calmer'],
                    'options' => [
                        'a1' => ['clearer', 'clearest', 'clear'],
                        'a2' => ['calmer', 'calmest', 'calm'],
                    ],
                ],
            ],
            [
                'tense' => 'past',
                'form' => 'question',
                'base' => [
                    'question' => 'Was the river {a1} yesterday than today?',
                    'answers' => ['a1' => 'higher'],
                    'options' => ['a1' => ['higher', 'highest', 'high']],
                ],
                'advanced' => [
                    'question' => 'Was the river {a1} yesterday than today, or was it as {a2} as usual?',
                    'answers' => ['a1' => 'higher', 'a2' => 'high'],
                    'options' => [
                        'a1' => ['higher', 'highest', 'high'],
                        'a2' => ['high', 'higher', 'highest'],
                    ],
                ],
            ],
            [
                'tense' => 'past',
                'form' => 'statement',
                'base' => [
                    'question' => 'She walked {a1} than I expected.',
                    'answers' => ['a1' => 'more slowly'],
                    'options' => ['a1' => ['more slowly', 'slower', 'slow']],
                ],
                'advanced' => [
                    'question' => 'She walked {a1} than I expected because the road was {a2}.',
                    'answers' => ['a1' => 'more slowly', 'a2' => 'rougher'],
                    'options' => [
                        'a1' => ['more slowly', 'slower', 'slow'],
                        'a2' => ['rougher', 'roughest', 'rough'],
                    ],
                ],
            ],
            [
                'tense' => 'past',
                'form' => 'negative',
                'base' => [
                    'question' => "The exam wasn't {a1} difficult as the mock test.",
                    'answers' => ['a1' => 'as'],
                    'options' => ['a1' => ['as', 'so', 'more']],
                    'verb_hints' => ['a1' => 'not (it)'],
                ],
                'advanced' => [
                    'question' => "The exam wasn't {a1} difficult as the mock test; the essay was the {a2} part.",
                    'answers' => ['a1' => 'as', 'a2' => 'hardest'],
                    'options' => [
                        'a1' => ['as', 'so', 'more'],
                        'a2' => ['hardest', 'harder', 'hard'],
                    ],
                    'verb_hints' => ['a1' => 'not (it)'],
                ],
            ],
            [
                'tense' => 'past',
                'form' => 'question',
                'base' => [
                    'question' => 'Were the instructions {a1} than the video?',
                    'answers' => ['a1' => 'clearer'],
                    'options' => ['a1' => ['clearer', 'clearest', 'clear']],
                ],
                'advanced' => [
                    'question' => 'Were the instructions {a1} than the video, or simply {a2}?',
                    'answers' => ['a1' => 'clearer', 'a2' => 'shorter'],
                    'options' => [
                        'a1' => ['clearer', 'clearest', 'clear'],
                        'a2' => ['shorter', 'shortest', 'short'],
                    ],
                ],
            ],
            [
                'tense' => 'past',
                'form' => 'statement',
                'base' => [
                    'question' => 'I felt {a1} about the news than before.',
                    'answers' => ['a1' => 'better'],
                    'options' => ['a1' => ['better', 'best', 'well']],
                ],
                'advanced' => [
                    'question' => 'I felt {a1} about the news than before and was the {a2} hopeful I had been all week.',
                    'answers' => ['a1' => 'better', 'a2' => 'most'],
                    'options' => [
                        'a1' => ['better', 'best', 'well'],
                        'a2' => ['most', 'more', 'much'],
                    ],
                ],
            ],
            [
                'tense' => 'past',
                'form' => 'statement',
                'base' => [
                    'question' => 'He used the {a1} amount of sugar possible.',
                    'answers' => ['a1' => 'least'],
                    'options' => ['a1' => ['least', 'less', 'fewer']],
                ],
                'advanced' => [
                    'question' => 'He used the {a1} amount of sugar possible, so the cake tasted {a2} sweet than usual.',
                    'answers' => ['a1' => 'least', 'a2' => 'less'],
                    'options' => [
                        'a1' => ['least', 'less', 'fewer'],
                        'a2' => ['less', 'least', 'fewer'],
                    ],
                ],
            ],
            [
                'tense' => 'past',
                'form' => 'statement',
                'base' => [
                    'question' => 'Their solution was the {a1} of all.',
                    'answers' => ['a1' => 'simplest'],
                    'options' => ['a1' => ['simplest', 'simple', 'more simple']],
                ],
                'advanced' => [
                    'question' => 'Their solution was the {a1} of all and worked {a2} than expected.',
                    'answers' => ['a1' => 'simplest', 'a2' => 'better'],
                    'options' => [
                        'a1' => ['simplest', 'simple', 'more simple'],
                        'a2' => ['better', 'best', 'well'],
                    ],
                ],
            ],
            [
                'tense' => 'future',
                'form' => 'statement',
                'base' => [
                    'question' => "Tomorrow's meeting will be the {a1} in the week.",
                    'answers' => ['a1' => 'shortest'],
                    'options' => ['a1' => ['shortest', 'shorter', 'short']],
                ],
                'advanced' => [
                    'question' => "Tomorrow's meeting will be the {a1} in the week and start {a2} than usual.",
                    'answers' => ['a1' => 'shortest', 'a2' => 'earlier'],
                    'options' => [
                        'a1' => ['shortest', 'shorter', 'short'],
                        'a2' => ['earlier', 'earliest', 'early'],
                    ],
                ],
            ],
            [
                'tense' => 'future',
                'form' => 'statement',
                'base' => [
                    'question' => 'Next month will be {a1} than this one.',
                    'answers' => ['a1' => 'busier'],
                    'options' => ['a1' => ['busier', 'busiest', 'busy']],
                ],
                'advanced' => [
                    'question' => 'Next month will be {a1} than this one, and the deadlines will come {a2}.',
                    'answers' => ['a1' => 'busier', 'a2' => 'sooner'],
                    'options' => [
                        'a1' => ['busier', 'busiest', 'busy'],
                        'a2' => ['sooner', 'soonest', 'soon'],
                    ],
                ],
            ],
            [
                'tense' => 'future',
                'form' => 'negative',
                'base' => [
                    'question' => 'The cafe will not be {a1} than the restaurant on Friday.',
                    'answers' => ['a1' => 'cheaper'],
                    'options' => ['a1' => ['cheaper', 'cheapest', 'cheap']],
                    'verb_hints' => ['a1' => 'not (cafe)'],
                ],
                'advanced' => [
                    'question' => 'The cafe will not be {a1} than the restaurant on Friday; meals there will cost {a2}.',
                    'answers' => ['a1' => 'cheaper', 'a2' => 'more'],
                    'options' => [
                        'a1' => ['cheaper', 'cheapest', 'cheap'],
                        'a2' => ['more', 'most', 'many'],
                    ],
                    'verb_hints' => ['a1' => 'not (cafe)'],
                ],
            ],
            [
                'tense' => 'future',
                'form' => 'question',
                'base' => [
                    'question' => 'Will the road be {a1} next year?',
                    'answers' => ['a1' => 'wider'],
                    'options' => ['a1' => ['wider', 'widest', 'wide']],
                ],
                'advanced' => [
                    'question' => 'Will the road be {a1} next year, or remain as {a2} as now?',
                    'answers' => ['a1' => 'wider', 'a2' => 'wide'],
                    'options' => [
                        'a1' => ['wider', 'widest', 'wide'],
                        'a2' => ['wide', 'wider', 'widest'],
                    ],
                ],
            ],
            [
                'tense' => 'future',
                'form' => 'question',
                'base' => [
                    'question' => 'Will we have {a1} chairs than yesterday?',
                    'answers' => ['a1' => 'more'],
                    'options' => ['a1' => ['more', 'most', 'many']],
                ],
                'advanced' => [
                    'question' => 'Will we have {a1} chairs than yesterday so everyone sits {a2}?',
                    'answers' => ['a1' => 'more', 'a2' => 'comfortably'],
                    'options' => [
                        'a1' => ['more', 'most', 'many'],
                        'a2' => ['comfortably', 'more comfortably', 'most comfortably'],
                    ],
                ],
            ],
            [
                'tense' => 'future',
                'form' => 'question',
                'base' => [
                    'question' => 'Will the lake be {a1} from the town than the sea?',
                    'answers' => ['a1' => 'farther'],
                    'options' => ['a1' => ['farther', 'further', 'furthest']],
                ],
                'advanced' => [
                    'question' => 'Will the lake be {a1} from the town than the sea, or about the {a2} distance?',
                    'answers' => ['a1' => 'farther', 'a2' => 'same'],
                    'options' => [
                        'a1' => ['farther', 'further', 'furthest'],
                        'a2' => ['same', 'similar', 'equal'],
                    ],
                ],
            ],
            [
                'tense' => 'future',
                'form' => 'statement',
                'base' => [
                    'question' => 'There will be {a1} apples than oranges at the market.',
                    'answers' => ['a1' => 'more'],
                    'options' => ['a1' => ['more', 'most', 'many']],
                ],
                'advanced' => [
                    'question' => 'There will be {a1} apples than oranges at the market, so buy the {a2} ones early.',
                    'answers' => ['a1' => 'more', 'a2' => 'freshest'],
                    'options' => [
                        'a1' => ['more', 'most', 'many'],
                        'a2' => ['freshest', 'fresher', 'fresh'],
                    ],
                ],
            ],
            [
                'tense' => 'future',
                'form' => 'statement',
                'base' => [
                    'question' => 'Tomorrow I will pack the {a1} clothes for the trip.',
                    'answers' => ['a1' => 'lightest'],
                    'options' => ['a1' => ['lightest', 'lighter', 'light']],
                ],
                'advanced' => [
                    'question' => 'Tomorrow I will pack the {a1} clothes for the trip to carry the {a2} weight.',
                    'answers' => ['a1' => 'lightest', 'a2' => 'least'],
                    'options' => [
                        'a1' => ['lightest', 'lighter', 'light'],
                        'a2' => ['least', 'less', 'fewer'],
                    ],
                ],
            ],
        ];

        $entries = [];
        foreach ($levels as $level) {
            foreach ($templates as $index => $template) {
                $useAdvanced = $level !== 'A1' && ($index % 2 === 1) && isset($template['advanced']);
                $data = $useAdvanced ? $template['advanced'] : $template['base'];

                $entries[] = [
                    'level' => $level,
                    'source' => $this->resolveSource($template['form'], $template['tense']),
                    'question' => $data['question'],
                    'answers' => $data['answers'],
                    'options' => $data['options'],
                    'verb_hints' => $data['verb_hints'] ?? [],
                ];
            }
        }

        return $entries;
    }
}
