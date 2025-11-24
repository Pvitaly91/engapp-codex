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
                'hints' => $entry['hints'] ?? [],
                'answers' => $entry['answers'],
                'option_markers' => $entry['option_markers'] ?? $this->buildOptionMarkers($entry['options']),
                'explanations' => $entry['explanations'] ?? [],
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

        $templates = $this->questionTemplates();

        $entries = [];
        foreach ($levels as $level) {
            foreach ($templates as $template) {
                foreach (['simple', 'paired', 'extended'] as $variantKey) {
                    $variant = $this->pickVariant($template['variants'][$variantKey] ?? [], $level);

                    if (empty($variant)) {
                        continue;
                    }

                    $entries[] = [
                        'level' => $level,
                        'source' => $this->resolveSource($template['form'], $template['tense']),
                        'question' => $variant['question'],
                        'answers' => $variant['answers'],
                        'options' => $variant['options'],
                        'verb_hints' => $variant['verb_hints'] ?? [],
                        'hints' => $this->buildHints($variant),
                        'explanations' => $this->buildExplanations($variant['options'], $variant['concept'] ?? 'comparison control'),
                        'option_markers' => $this->buildOptionMarkers($variant['options']),
                    ];
                }
            }
        }

        return $entries;
    }

    private function questionTemplates(): array
    {
        return [
            [
                'tense' => 'present',
                'form' => 'question',
                'variants' => [
                    'simple' => [
                        'question' => 'Is the new train {a1} than the old one?',
                        'answers' => ['a1' => 'faster'],
                        'options' => ['a1' => ['faster', 'fastest', 'fast']],
                        'concept' => 'basic comparative with short adjective',
                        'example' => 'The new train is faster than the bus.',
                    ],
                    'paired' => [
                        'default' => [
                            'question' => "Is the new train {a1} than the old one, or is it as {a2} as last year's model?",
                            'answers' => ['a1' => 'faster', 'a2' => 'fast'],
                            'options' => [
                                'a1' => ['faster', 'fastest', 'fast'],
                                'a2' => ['fast', 'faster', 'fastest'],
                            ],
                            'concept' => 'comparative vs equality',
                            'example' => 'The metro is faster than the bus but as fast as the tram.',
                        ],
                        'a1' => [
                            'question' => 'Is the new train really {a1} than the old one today?',
                            'answers' => ['a1' => 'faster'],
                            'options' => ['a1' => ['faster', 'fastest', 'fast']],
                            'concept' => 'simple comparative check',
                        ],
                    ],
                    'extended' => [
                        'default' => [
                            'question' => 'When comparing the two trains, is the new model {a1} than the old one, {a2} than the commuter line, and still as {a3} as the express on weekends?',
                            'answers' => ['a1' => 'faster', 'a2' => 'quieter', 'a3' => 'reliable'],
                            'options' => [
                                'a1' => ['faster', 'fastest', 'fast'],
                                'a2' => ['quieter', 'quiet', 'quietest'],
                                'a3' => ['reliable', 'more reliable', 'most reliable'],
                            ],
                            'concept' => 'mixed comparative frames',
                            'example' => 'Use -er for short adjectives and more/less for longer ones.',
                        ],
                        'a1' => [
                            'question' => 'Is the new train {a1} than the slow bus?',
                            'answers' => ['a1' => 'faster'],
                            'options' => ['a1' => ['faster', 'fast', 'fastest']],
                            'concept' => 'single comparative',
                        ],
                    ],
                ],
            ],
            [
                'tense' => 'present',
                'form' => 'negative',
                'variants' => [
                    'simple' => [
                        'question' => "This soup isn't as {a1} as yesterday's.",
                        'answers' => ['a1' => 'tasty'],
                        'options' => ['a1' => ['tasty', 'tastier', 'tastiest']],
                        'verb_hints' => ['a1' => 'not (it)'],
                        'concept' => 'as ... as equality in negatives',
                        'example' => 'The tea is not as hot as the coffee.',
                    ],
                    'paired' => [
                        'default' => [
                            'question' => "This soup isn't as {a1} as yesterday's; it was {a2} salty then.",
                            'answers' => ['a1' => 'tasty', 'a2' => 'less'],
                            'options' => [
                                'a1' => ['tasty', 'tastier', 'tastiest'],
                                'a2' => ['less', 'least', 'fewer'],
                            ],
                            'verb_hints' => ['a1' => 'not (it)', 'a2' => 'not (it)'],
                            'concept' => 'negative equality and quantity contrast',
                        ],
                        'a1' => [
                            'question' => "Today's soup isn't as {a1} as the one we had before.",
                            'answers' => ['a1' => 'tasty'],
                            'options' => ['a1' => ['tasty', 'tastier', 'tastiest']],
                            'verb_hints' => ['a1' => 'not (it)'],
                            'concept' => 'simple negative equality',
                        ],
                    ],
                    'extended' => [
                        'default' => [
                            'question' => "This soup isn't as {a1} as yesterday's, the bread is {a2} fresh than usual, and the dessert is the {a3} surprise of all.",
                            'answers' => ['a1' => 'tasty', 'a2' => 'less', 'a3' => 'biggest'],
                            'options' => [
                                'a1' => ['tasty', 'tastier', 'tastiest'],
                                'a2' => ['less', 'least', 'fewer'],
                                'a3' => ['biggest', 'bigger', 'big'],
                            ],
                            'verb_hints' => ['a1' => 'not (it)', 'a2' => 'not (bread)'],
                            'concept' => 'layered negative comparisons',
                        ],
                        'a1' => [
                            'question' => "This soup isn't very {a1} compared to last week.",
                            'answers' => ['a1' => 'tasty'],
                            'options' => ['a1' => ['tasty', 'tastier', 'tastiest']],
                            'verb_hints' => ['a1' => 'not (it)'],
                            'concept' => 'single adjective contrast',
                        ],
                    ],
                ],
            ],
            [
                'tense' => 'present',
                'form' => 'statement',
                'variants' => [
                    'simple' => [
                        'question' => 'The desert is {a1} than the forest.',
                        'answers' => ['a1' => 'drier'],
                        'options' => ['a1' => ['drier', 'dry', 'driest']],
                        'concept' => 'comparative with y ending',
                        'example' => 'The room is drier than the hall.',
                    ],
                    'paired' => [
                        'default' => [
                            'question' => 'The desert is {a1} than the forest, and the nights feel {a2} than at home.',
                            'answers' => ['a1' => 'drier', 'a2' => 'colder'],
                            'options' => [
                                'a1' => ['drier', 'dry', 'driest'],
                                'a2' => ['colder', 'coldest', 'cold'],
                            ],
                            'concept' => 'multiple short adjective comparatives',
                        ],
                        'a1' => [
                            'question' => 'The desert is much {a1} than the forest.',
                            'answers' => ['a1' => 'drier'],
                            'options' => ['a1' => ['drier', 'dry', 'driest']],
                            'concept' => 'comparative emphasis',
                        ],
                    ],
                    'extended' => [
                        'default' => [
                            'question' => 'The desert is {a1} than the forest, the wind is {a2} than yesterday, and the dunes look the {a3} we have seen.',
                            'answers' => ['a1' => 'drier', 'a2' => 'stronger', 'a3' => 'largest'],
                            'options' => [
                                'a1' => ['drier', 'dry', 'driest'],
                                'a2' => ['stronger', 'strongest', 'strong'],
                                'a3' => ['largest', 'larger', 'large'],
                            ],
                            'concept' => 'chain of comparative and superlative forms',
                        ],
                        'a1' => [
                            'question' => 'The desert air is {a1} than in the forest.',
                            'answers' => ['a1' => 'drier'],
                            'options' => ['a1' => ['drier', 'dry', 'driest']],
                            'concept' => 'simple comparative statement',
                        ],
                    ],
                ],
            ],
            [
                'tense' => 'present',
                'form' => 'question',
                'variants' => [
                    'simple' => [
                        'question' => 'Is this path as {a1} as the other?',
                        'answers' => ['a1' => 'safe'],
                        'options' => ['a1' => ['safe', 'safely', 'safer']],
                        'concept' => 'as ... as equality',
                        'example' => 'The park is as safe as the square.',
                    ],
                    'paired' => [
                        'default' => [
                            'question' => 'Is this path as {a1} as the other, or is it {a2}?',
                            'answers' => ['a1' => 'safe', 'a2' => 'safer'],
                            'options' => [
                                'a1' => ['safe', 'safely', 'safer'],
                                'a2' => ['safer', 'safest', 'safe'],
                            ],
                            'concept' => 'equality versus comparative',
                        ],
                        'a1' => [
                            'question' => 'Is this path really {a1} to walk?',
                            'answers' => ['a1' => 'safe'],
                            'options' => ['a1' => ['safe', 'safer', 'safest']],
                            'concept' => 'simple safety question',
                        ],
                    ],
                    'extended' => [
                        'default' => [
                            'question' => 'Is this path as {a1} as the other, {a2} than the rocky trail, and the {a3} choice for kids?',
                            'answers' => ['a1' => 'safe', 'a2' => 'safer', 'a3' => 'best'],
                            'options' => [
                                'a1' => ['safe', 'safely', 'safer'],
                                'a2' => ['safer', 'safest', 'safe'],
                                'a3' => ['best', 'better', 'good'],
                            ],
                            'concept' => 'layered equality and irregular forms',
                        ],
                        'a1' => [
                            'question' => 'Is the path {a1} enough for children?',
                            'answers' => ['a1' => 'safe'],
                            'options' => ['a1' => ['safe', 'safer', 'safest']],
                            'concept' => 'single safety comparison',
                        ],
                    ],
                ],
            ],
            [
                'tense' => 'present',
                'form' => 'statement',
                'variants' => [
                    'simple' => [
                        'question' => 'He is {a1} than anyone else here.',
                        'answers' => ['a1' => 'taller'],
                        'options' => ['a1' => ['taller', 'tallest', 'tall']],
                        'concept' => 'comparative with tall',
                        'example' => 'She is taller than her brother.',
                    ],
                    'paired' => [
                        'default' => [
                            'question' => 'He is {a1} than anyone else here, and his sister is the {a2} in the family.',
                            'answers' => ['a1' => 'taller', 'a2' => 'shortest'],
                            'options' => [
                                'a1' => ['taller', 'tallest', 'tall'],
                                'a2' => ['shortest', 'shorter', 'short'],
                            ],
                            'concept' => 'comparative plus superlative',
                        ],
                        'a1' => [
                            'question' => 'He is much {a1} than me.',
                            'answers' => ['a1' => 'taller'],
                            'options' => ['a1' => ['taller', 'tallest', 'tall']],
                            'concept' => 'basic height comparison',
                        ],
                    ],
                    'extended' => [
                        'default' => [
                            'question' => 'He is {a1} than anyone else here, his cousin is even {a2}, and their aunt is the {a3} adult in the group.',
                            'answers' => ['a1' => 'taller', 'a2' => 'tallest', 'a3' => 'oldest'],
                            'options' => [
                                'a1' => ['taller', 'tallest', 'tall'],
                                'a2' => ['tallest', 'taller', 'tall'],
                                'a3' => ['oldest', 'older', 'old'],
                            ],
                            'concept' => 'irregular adjective patterns',
                        ],
                        'a1' => [
                            'question' => 'He is {a1} than the rest of the class.',
                            'answers' => ['a1' => 'taller'],
                            'options' => ['a1' => ['taller', 'tallest', 'tall']],
                            'concept' => 'single height comparison',
                        ],
                    ],
                ],
            ],
            [
                'tense' => 'present',
                'form' => 'statement',
                'variants' => [
                    'simple' => [
                        'question' => 'My suitcase is {a1} than yours.',
                        'answers' => ['a1' => 'heavier'],
                        'options' => ['a1' => ['heavier', 'heaviest', 'heavy']],
                        'concept' => 'comparative with -y ending',
                        'example' => 'The box is heavier than the bag.',
                    ],
                    'paired' => [
                        'default' => [
                            'question' => 'My suitcase is {a1} than yours, but yours is as {a2} as mine.',
                            'answers' => ['a1' => 'heavier', 'a2' => 'large'],
                            'options' => [
                                'a1' => ['heavier', 'heaviest', 'heavy'],
                                'a2' => ['large', 'larger', 'largest'],
                            ],
                            'concept' => 'size vs weight comparison',
                        ],
                        'a1' => [
                            'question' => 'My suitcase feels {a1} than your backpack.',
                            'answers' => ['a1' => 'heavier'],
                            'options' => ['a1' => ['heavier', 'heavy', 'heaviest']],
                            'concept' => 'single weight comparison',
                        ],
                    ],
                    'extended' => [
                        'default' => [
                            'question' => 'My suitcase is {a1} than yours, the cabin bag is {a2}, and the duffel is the {a3} of all.',
                            'answers' => ['a1' => 'heavier', 'a2' => 'lighter', 'a3' => 'lightest'],
                            'options' => [
                                'a1' => ['heavier', 'heaviest', 'heavy'],
                                'a2' => ['lighter', 'lightest', 'light'],
                                'a3' => ['lightest', 'lighter', 'light'],
                            ],
                            'concept' => 'comparative ladder for weight',
                        ],
                        'a1' => [
                            'question' => 'This suitcase is {a1} than mine.',
                            'answers' => ['a1' => 'heavier'],
                            'options' => ['a1' => ['heavier', 'heavy', 'heaviest']],
                            'concept' => 'simple weight statement',
                        ],
                    ],
                ],
            ],
            [
                'tense' => 'present',
                'form' => 'negative',
                'variants' => [
                    'simple' => [
                        'question' => 'She is not the {a1} runner in the group.',
                        'answers' => ['a1' => 'fastest'],
                        'options' => ['a1' => ['fastest', 'faster', 'fast']],
                        'verb_hints' => ['a1' => 'not (she)'],
                        'concept' => 'negative superlative',
                        'example' => 'He is not the fastest player.',
                    ],
                    'paired' => [
                        'default' => [
                            'question' => 'She is not the {a1} runner in the group; Mia is even {a2}.',
                            'answers' => ['a1' => 'fastest', 'a2' => 'faster'],
                            'options' => [
                                'a1' => ['fastest', 'faster', 'fast'],
                                'a2' => ['faster', 'fastest', 'fast'],
                            ],
                            'verb_hints' => ['a1' => 'not (she)'],
                            'concept' => 'contrastive comparative',
                        ],
                        'a1' => [
                            'question' => 'She is not the {a1} in her team.',
                            'answers' => ['a1' => 'fastest'],
                            'options' => ['a1' => ['fastest', 'faster', 'fast']],
                            'verb_hints' => ['a1' => 'not (she)'],
                            'concept' => 'single negative superlative',
                        ],
                    ],
                    'extended' => [
                        'default' => [
                            'question' => 'She is not the {a1} runner in the group, her brother is {a2}, and their coach is the {a3} supporter they have.',
                            'answers' => ['a1' => 'fastest', 'a2' => 'faster', 'a3' => 'most patient'],
                            'options' => [
                                'a1' => ['fastest', 'faster', 'fast'],
                                'a2' => ['faster', 'fastest', 'fast'],
                                'a3' => ['most patient', 'more patient', 'patient'],
                            ],
                            'verb_hints' => ['a1' => 'not (she)'],
                            'concept' => 'negative plus mixed comparatives',
                        ],
                        'a1' => [
                            'question' => 'She is not the {a1} athlete today.',
                            'answers' => ['a1' => 'fastest'],
                            'options' => ['a1' => ['fastest', 'faster', 'fast']],
                            'verb_hints' => ['a1' => 'not (she)'],
                            'concept' => 'simple negative note',
                        ],
                    ],
                ],
            ],
            [
                'tense' => 'present',
                'form' => 'statement',
                'variants' => [
                    'simple' => [
                        'question' => 'The weather gets {a1} every day.',
                        'answers' => ['a1' => 'colder'],
                        'options' => ['a1' => ['colder', 'coldest', 'cold']],
                        'concept' => 'comparative change over time',
                        'example' => 'The nights get colder each week.',
                    ],
                    'paired' => [
                        'default' => [
                            'question' => 'The weather gets {a1} and {a2} each day.',
                            'answers' => ['a1' => 'colder', 'a2' => 'windier'],
                            'options' => [
                                'a1' => ['colder', 'coldest', 'cold'],
                                'a2' => ['windier', 'windiest', 'windy'],
                            ],
                            'concept' => 'double comparative',
                        ],
                        'a1' => [
                            'question' => 'It feels {a1} and darker now.',
                            'answers' => ['a1' => 'colder'],
                            'options' => ['a1' => ['colder', 'cold', 'coldest']],
                            'concept' => 'simple weather comparison',
                        ],
                    ],
                    'extended' => [
                        'default' => [
                            'question' => 'The weather gets {a1} every day, the wind blows {a2} than last week, and the mornings are the {a3} part.',
                            'answers' => ['a1' => 'colder', 'a2' => 'harder', 'a3' => 'coldest'],
                            'options' => [
                                'a1' => ['colder', 'coldest', 'cold'],
                                'a2' => ['harder', 'hardest', 'hard'],
                                'a3' => ['coldest', 'colder', 'cold'],
                            ],
                            'concept' => 'comparative chain with superlative ending',
                        ],
                        'a1' => [
                            'question' => 'The air is {a1} today.',
                            'answers' => ['a1' => 'colder'],
                            'options' => ['a1' => ['colder', 'cold', 'coldest']],
                            'concept' => 'single climate comparison',
                        ],
                    ],
                ],
            ],
            [
                'tense' => 'past',
                'form' => 'statement',
                'variants' => [
                    'simple' => [
                        'question' => 'Her story was {a1} than his.',
                        'answers' => ['a1' => 'clearer'],
                        'options' => ['a1' => ['clearer', 'clearest', 'clear']],
                        'concept' => 'comparative clarity',
                        'example' => 'The notes were clearer than the slides.',
                    ],
                    'paired' => [
                        'default' => [
                            'question' => 'Her story was {a1} than his, and her voice sounded {a2}.',
                            'answers' => ['a1' => 'clearer', 'a2' => 'calmer'],
                            'options' => [
                                'a1' => ['clearer', 'clearest', 'clear'],
                                'a2' => ['calmer', 'calmest', 'calm'],
                            ],
                            'concept' => 'multiple descriptive comparatives',
                        ],
                        'a1' => [
                            'question' => 'Her story was much {a1}.',
                            'answers' => ['a1' => 'clearer'],
                            'options' => ['a1' => ['clearer', 'clearest', 'clear']],
                            'concept' => 'single clarity comparison',
                        ],
                    ],
                    'extended' => [
                        'default' => [
                            'question' => 'Her story was {a1} than his, the ending was {a2} than expected, and her points were the {a3} of the evening.',
                            'answers' => ['a1' => 'clearer', 'a2' => 'sharper', 'a3' => 'strongest'],
                            'options' => [
                                'a1' => ['clearer', 'clearest', 'clear'],
                                'a2' => ['sharper', 'sharpest', 'sharp'],
                                'a3' => ['strongest', 'stronger', 'strong'],
                            ],
                            'concept' => 'comparative narrative focus',
                        ],
                        'a1' => [
                            'question' => 'Her story felt {a1} than his.',
                            'answers' => ['a1' => 'clearer'],
                            'options' => ['a1' => ['clearer', 'clearest', 'clear']],
                            'concept' => 'simple past comparison',
                        ],
                    ],
                ],
            ],
            [
                'tense' => 'past',
                'form' => 'question',
                'variants' => [
                    'simple' => [
                        'question' => 'Was the river {a1} yesterday than today?',
                        'answers' => ['a1' => 'higher'],
                        'options' => ['a1' => ['higher', 'highest', 'high']],
                        'concept' => 'comparative past question',
                        'example' => 'Was the water higher than last week?',
                    ],
                    'paired' => [
                        'default' => [
                            'question' => 'Was the river {a1} yesterday than today, or was it as {a2} as usual?',
                            'answers' => ['a1' => 'higher', 'a2' => 'high'],
                            'options' => [
                                'a1' => ['higher', 'highest', 'high'],
                                'a2' => ['high', 'higher', 'highest'],
                            ],
                            'concept' => 'comparative vs equality in past',
                        ],
                        'a1' => [
                            'question' => 'Was the river {a1} last night?',
                            'answers' => ['a1' => 'higher'],
                            'options' => ['a1' => ['higher', 'high', 'highest']],
                            'concept' => 'single past height question',
                        ],
                    ],
                    'extended' => [
                        'default' => [
                            'question' => 'Was the river {a1} yesterday, {a2} in the morning, and the {a3} at noon?',
                            'answers' => ['a1' => 'higher', 'a2' => 'calmer', 'a3' => 'highest'],
                            'options' => [
                                'a1' => ['higher', 'highest', 'high'],
                                'a2' => ['calmer', 'calmest', 'calm'],
                                'a3' => ['highest', 'higher', 'high'],
                            ],
                            'concept' => 'timeline comparisons',
                        ],
                        'a1' => [
                            'question' => 'Was the river very {a1} yesterday?',
                            'answers' => ['a1' => 'high'],
                            'options' => ['a1' => ['high', 'higher', 'highest']],
                            'concept' => 'simple level question',
                        ],
                    ],
                ],
            ],
            [
                'tense' => 'past',
                'form' => 'statement',
                'variants' => [
                    'simple' => [
                        'question' => 'She walked {a1} than I expected.',
                        'answers' => ['a1' => 'more slowly'],
                        'options' => ['a1' => ['more slowly', 'slower', 'slow']],
                        'concept' => 'comparative with adverbs',
                        'example' => 'He walked more slowly than usual.',
                    ],
                    'paired' => [
                        'default' => [
                            'question' => 'She walked {a1} than I expected because the road was {a2}.',
                            'answers' => ['a1' => 'more slowly', 'a2' => 'rougher'],
                            'options' => [
                                'a1' => ['more slowly', 'slower', 'slow'],
                                'a2' => ['rougher', 'roughest', 'rough'],
                            ],
                            'concept' => 'adverb comparative with reason',
                        ],
                        'a1' => [
                            'question' => 'She walked very {a1}.',
                            'answers' => ['a1' => 'slowly'],
                            'options' => ['a1' => ['slowly', 'slower', 'slowest']],
                            'concept' => 'single pace statement',
                        ],
                    ],
                    'extended' => [
                        'default' => [
                            'question' => 'She walked {a1} than I expected, climbed {a2} than before, and finished with the {a3} time.',
                            'answers' => ['a1' => 'more slowly', 'a2' => 'faster', 'a3' => 'best'],
                            'options' => [
                                'a1' => ['more slowly', 'slower', 'slow'],
                                'a2' => ['faster', 'fastest', 'fast'],
                                'a3' => ['best', 'better', 'good'],
                            ],
                            'concept' => 'mixing adverb and adjective degrees',
                        ],
                        'a1' => [
                            'question' => 'She moved {a1} yesterday.',
                            'answers' => ['a1' => 'slowly'],
                            'options' => ['a1' => ['slowly', 'slower', 'slowest']],
                            'concept' => 'simple past adverb',
                        ],
                    ],
                ],
            ],
            [
                'tense' => 'past',
                'form' => 'negative',
                'variants' => [
                    'simple' => [
                        'question' => "The exam wasn't {a1} difficult as the mock test.",
                        'answers' => ['a1' => 'as'],
                        'options' => ['a1' => ['as', 'so', 'more']],
                        'verb_hints' => ['a1' => 'not (it)'],
                        'concept' => 'negative equality with as/so',
                        'example' => 'The task was not as long as before.',
                    ],
                    'paired' => [
                        'default' => [
                            'question' => "The exam wasn't {a1} difficult as the mock test; the essay was the {a2} part.",
                            'answers' => ['a1' => 'as', 'a2' => 'hardest'],
                            'options' => [
                                'a1' => ['as', 'so', 'more'],
                                'a2' => ['hardest', 'harder', 'hard'],
                            ],
                            'verb_hints' => ['a1' => 'not (it)'],
                            'concept' => 'negative equality plus superlative',
                        ],
                        'a1' => [
                            'question' => "The exam wasn't so {a1} this time.",
                            'answers' => ['a1' => 'hard'],
                            'options' => ['a1' => ['hard', 'harder', 'hardest']],
                            'verb_hints' => ['a1' => 'not (it)'],
                            'concept' => 'simple negative comparison',
                        ],
                    ],
                    'extended' => [
                        'default' => [
                            'question' => "The exam wasn't {a1} difficult as the mock test, the speaking part was {a2} stressful, and the writing was the {a3} section.",
                            'answers' => ['a1' => 'as', 'a2' => 'less', 'a3' => 'longest'],
                            'options' => [
                                'a1' => ['as', 'so', 'more'],
                                'a2' => ['less', 'least', 'fewer'],
                                'a3' => ['longest', 'longer', 'long'],
                            ],
                            'verb_hints' => ['a1' => 'not (it)'],
                            'concept' => 'tiered equality and quantity',
                        ],
                        'a1' => [
                            'question' => "The exam wasn't very {a1} overall.",
                            'answers' => ['a1' => 'hard'],
                            'options' => ['a1' => ['hard', 'harder', 'hardest']],
                            'verb_hints' => ['a1' => 'not (it)'],
                            'concept' => 'single negative adjective',
                        ],
                    ],
                ],
            ],
            [
                'tense' => 'past',
                'form' => 'question',
                'variants' => [
                    'simple' => [
                        'question' => 'Were the instructions {a1} than the video?',
                        'answers' => ['a1' => 'clearer'],
                        'options' => ['a1' => ['clearer', 'clearest', 'clear']],
                        'concept' => 'clarity question',
                        'example' => 'Were the slides clearer than the notes?',
                    ],
                    'paired' => [
                        'default' => [
                            'question' => 'Were the instructions {a1} than the video, or simply {a2}?',
                            'answers' => ['a1' => 'clearer', 'a2' => 'shorter'],
                            'options' => [
                                'a1' => ['clearer', 'clearest', 'clear'],
                                'a2' => ['shorter', 'shortest', 'short'],
                            ],
                            'concept' => 'clarity vs brevity',
                        ],
                        'a1' => [
                            'question' => 'Were the instructions {a1}?',
                            'answers' => ['a1' => 'clear'],
                            'options' => ['a1' => ['clear', 'clearer', 'clearest']],
                            'concept' => 'simple clarity question',
                        ],
                    ],
                    'extended' => [
                        'default' => [
                            'question' => 'Were the instructions {a1} than the video, {a2} than the guide, and the {a3} part of the process?',
                            'answers' => ['a1' => 'clearer', 'a2' => 'longer', 'a3' => 'easiest'],
                            'options' => [
                                'a1' => ['clearer', 'clearest', 'clear'],
                                'a2' => ['longer', 'longest', 'long'],
                                'a3' => ['easiest', 'easier', 'easy'],
                            ],
                            'concept' => 'comparison across resources',
                        ],
                        'a1' => [
                            'question' => 'Were the instructions very {a1}?',
                            'answers' => ['a1' => 'clear'],
                            'options' => ['a1' => ['clear', 'clearer', 'clearest']],
                            'concept' => 'single positive check',
                        ],
                    ],
                ],
            ],
            [
                'tense' => 'past',
                'form' => 'statement',
                'variants' => [
                    'simple' => [
                        'question' => 'I felt {a1} about the news than before.',
                        'answers' => ['a1' => 'better'],
                        'options' => ['a1' => ['better', 'best', 'well']],
                        'concept' => 'irregular comparative',
                        'example' => 'She felt better after the call.',
                    ],
                    'paired' => [
                        'default' => [
                            'question' => 'I felt {a1} about the news than before and was the {a2} hopeful I had been all week.',
                            'answers' => ['a1' => 'better', 'a2' => 'most'],
                            'options' => [
                                'a1' => ['better', 'best', 'well'],
                                'a2' => ['most', 'more', 'much'],
                            ],
                            'concept' => 'irregular comparative plus quantity',
                        ],
                        'a1' => [
                            'question' => 'I felt much {a1}.',
                            'answers' => ['a1' => 'better'],
                            'options' => ['a1' => ['better', 'best', 'well']],
                            'concept' => 'short irregular comparison',
                        ],
                    ],
                    'extended' => [
                        'default' => [
                            'question' => 'I felt {a1} about the news than before, the morning was {a2} than the evening, and that day was the {a3} of the month.',
                            'answers' => ['a1' => 'better', 'a2' => 'brighter', 'a3' => 'happiest'],
                            'options' => [
                                'a1' => ['better', 'best', 'well'],
                                'a2' => ['brighter', 'brightest', 'bright'],
                                'a3' => ['happiest', 'happier', 'happy'],
                            ],
                            'concept' => 'emotional comparisons across time',
                        ],
                        'a1' => [
                            'question' => 'I felt {a1} yesterday.',
                            'answers' => ['a1' => 'better'],
                            'options' => ['a1' => ['better', 'best', 'well']],
                            'concept' => 'single feeling comparison',
                        ],
                    ],
                ],
            ],
            [
                'tense' => 'past',
                'form' => 'statement',
                'variants' => [
                    'simple' => [
                        'question' => 'He used the {a1} amount of sugar possible.',
                        'answers' => ['a1' => 'least'],
                        'options' => ['a1' => ['least', 'less', 'fewer']],
                        'concept' => 'quantity superlative',
                        'example' => 'She spent the least money.',
                    ],
                    'paired' => [
                        'default' => [
                            'question' => 'He used the {a1} amount of sugar possible, so the cake tasted {a2} sweet than usual.',
                            'answers' => ['a1' => 'least', 'a2' => 'less'],
                            'options' => [
                                'a1' => ['least', 'less', 'fewer'],
                                'a2' => ['less', 'least', 'fewer'],
                            ],
                            'concept' => 'quantity reduction',
                        ],
                        'a1' => [
                            'question' => 'He added the {a1} sugar.',
                            'answers' => ['a1' => 'least'],
                            'options' => ['a1' => ['least', 'less', 'fewer']],
                            'concept' => 'single quantity focus',
                        ],
                    ],
                    'extended' => [
                        'default' => [
                            'question' => 'He used the {a1} amount of sugar possible, added {a2} cream than before, and served the {a3} slice to me.',
                            'answers' => ['a1' => 'least', 'a2' => 'less', 'a3' => 'smallest'],
                            'options' => [
                                'a1' => ['least', 'less', 'fewer'],
                                'a2' => ['less', 'least', 'fewer'],
                                'a3' => ['smallest', 'smaller', 'small'],
                            ],
                            'concept' => 'quantities and superlatives',
                        ],
                        'a1' => [
                            'question' => 'He used very {a1} sugar.',
                            'answers' => ['a1' => 'little'],
                            'options' => ['a1' => ['little', 'less', 'least']],
                            'concept' => 'simple quantity description',
                        ],
                    ],
                ],
            ],
            [
                'tense' => 'past',
                'form' => 'statement',
                'variants' => [
                    'simple' => [
                        'question' => 'Their solution was the {a1} of all.',
                        'answers' => ['a1' => 'simplest'],
                        'options' => ['a1' => ['simplest', 'simple', 'more simple']],
                        'concept' => 'superlative formation',
                        'example' => 'That design was the simplest idea.',
                    ],
                    'paired' => [
                        'default' => [
                            'question' => 'Their solution was the {a1} of all and worked {a2} than expected.',
                            'answers' => ['a1' => 'simplest', 'a2' => 'better'],
                            'options' => [
                                'a1' => ['simplest', 'simple', 'more simple'],
                                'a2' => ['better', 'best', 'well'],
                            ],
                            'concept' => 'superlative plus irregular comparative',
                        ],
                        'a1' => [
                            'question' => 'Their idea was the {a1}.',
                            'answers' => ['a1' => 'simplest'],
                            'options' => ['a1' => ['simplest', 'simple', 'more simple']],
                            'concept' => 'single superlative choice',
                        ],
                    ],
                    'extended' => [
                        'default' => [
                            'question' => 'Their solution was the {a1} of all, the meeting became {a2} than before, and the feedback was the {a3} we received.',
                            'answers' => ['a1' => 'simplest', 'a2' => 'shorter', 'a3' => 'warmest'],
                            'options' => [
                                'a1' => ['simplest', 'simple', 'more simple'],
                                'a2' => ['shorter', 'shortest', 'short'],
                                'a3' => ['warmest', 'warmer', 'warm'],
                            ],
                            'concept' => 'superlative and comparative mix',
                        ],
                        'a1' => [
                            'question' => 'Their plan was {a1} than ours.',
                            'answers' => ['a1' => 'simpler'],
                            'options' => ['a1' => ['simpler', 'simplest', 'simple']],
                            'concept' => 'comparative form',
                        ],
                    ],
                ],
            ],
            [
                'tense' => 'future',
                'form' => 'statement',
                'variants' => [
                    'simple' => [
                        'question' => "Tomorrow's meeting will be the {a1} in the week.",
                        'answers' => ['a1' => 'shortest'],
                        'options' => ['a1' => ['shortest', 'shorter', 'short']],
                        'concept' => 'future superlative',
                        'example' => 'It will be the shortest call.',
                    ],
                    'paired' => [
                        'default' => [
                            'question' => "Tomorrow's meeting will be the {a1} in the week and start {a2} than usual.",
                            'answers' => ['a1' => 'shortest', 'a2' => 'earlier'],
                            'options' => [
                                'a1' => ['shortest', 'shorter', 'short'],
                                'a2' => ['earlier', 'earliest', 'early'],
                            ],
                            'concept' => 'superlative and comparative of time',
                        ],
                        'a1' => [
                            'question' => "Tomorrow's meeting will be {a1} than today's.",
                            'answers' => ['a1' => 'shorter'],
                            'options' => ['a1' => ['shorter', 'shortest', 'short']],
                            'concept' => 'future comparative time',
                        ],
                    ],
                    'extended' => [
                        'default' => [
                            'question' => "Tomorrow's meeting will be the {a1} in the week, start {a2} than usual, and finish with the {a3} vote.",
                            'answers' => ['a1' => 'shortest', 'a2' => 'earlier', 'a3' => 'quickest'],
                            'options' => [
                                'a1' => ['shortest', 'shorter', 'short'],
                                'a2' => ['earlier', 'earliest', 'early'],
                                'a3' => ['quickest', 'quicker', 'quick'],
                            ],
                            'concept' => 'time management comparisons',
                        ],
                        'a1' => [
                            'question' => 'Tomorrow the meeting will be very {a1}.',
                            'answers' => ['a1' => 'short'],
                            'options' => ['a1' => ['short', 'shorter', 'shortest']],
                            'concept' => 'simple duration note',
                        ],
                    ],
                ],
            ],
            [
                'tense' => 'future',
                'form' => 'statement',
                'variants' => [
                    'simple' => [
                        'question' => 'Next month will be {a1} than this one.',
                        'answers' => ['a1' => 'busier'],
                        'options' => ['a1' => ['busier', 'busiest', 'busy']],
                        'concept' => 'future comparative',
                        'example' => 'Next week will be busier than this one.',
                    ],
                    'paired' => [
                        'default' => [
                            'question' => 'Next month will be {a1} than this one, and the deadlines will come {a2}.',
                            'answers' => ['a1' => 'busier', 'a2' => 'sooner'],
                            'options' => [
                                'a1' => ['busier', 'busiest', 'busy'],
                                'a2' => ['sooner', 'soonest', 'soon'],
                            ],
                            'concept' => 'time pressure comparison',
                        ],
                        'a1' => [
                            'question' => 'Next month will feel {a1}.',
                            'answers' => ['a1' => 'busier'],
                            'options' => ['a1' => ['busier', 'busy', 'busiest']],
                            'concept' => 'single future comparison',
                        ],
                    ],
                    'extended' => [
                        'default' => [
                            'question' => 'Next month will be {a1} than this one, meetings will start {a2}, and it might be the {a3} season we have.',
                            'answers' => ['a1' => 'busier', 'a2' => 'earlier', 'a3' => 'hardest'],
                            'options' => [
                                'a1' => ['busier', 'busiest', 'busy'],
                                'a2' => ['earlier', 'earliest', 'early'],
                                'a3' => ['hardest', 'harder', 'hard'],
                            ],
                            'concept' => 'future workload projections',
                        ],
                        'a1' => [
                            'question' => 'Next month will be more {a1}.',
                            'answers' => ['a1' => 'busy'],
                            'options' => ['a1' => ['busy', 'busier', 'busiest']],
                            'concept' => 'simple workload note',
                        ],
                    ],
                ],
            ],
            [
                'tense' => 'future',
                'form' => 'negative',
                'variants' => [
                    'simple' => [
                        'question' => 'The cafe will not be {a1} than the restaurant on Friday.',
                        'answers' => ['a1' => 'cheaper'],
                        'options' => ['a1' => ['cheaper', 'cheapest', 'cheap']],
                        'verb_hints' => ['a1' => 'not (cafe)'],
                        'concept' => 'negative comparative cost',
                        'example' => 'The meal will not be cheaper there.',
                    ],
                    'paired' => [
                        'default' => [
                            'question' => 'The cafe will not be {a1} than the restaurant on Friday; meals there will cost {a2}.',
                            'answers' => ['a1' => 'cheaper', 'a2' => 'more'],
                            'options' => [
                                'a1' => ['cheaper', 'cheapest', 'cheap'],
                                'a2' => ['more', 'most', 'many'],
                            ],
                            'verb_hints' => ['a1' => 'not (cafe)'],
                            'concept' => 'negative comparative with quantity',
                        ],
                        'a1' => [
                            'question' => 'The cafe will not be very {a1}.',
                            'answers' => ['a1' => 'cheap'],
                            'options' => ['a1' => ['cheap', 'cheaper', 'cheapest']],
                            'verb_hints' => ['a1' => 'not (cafe)'],
                            'concept' => 'single negative price note',
                        ],
                    ],
                    'extended' => [
                        'default' => [
                            'question' => 'The cafe will not be {a1} than the restaurant on Friday, the menu will be {a2} varied, and the desserts might be the {a3} part.',
                            'answers' => ['a1' => 'cheaper', 'a2' => 'less', 'a3' => 'sweetest'],
                            'options' => [
                                'a1' => ['cheaper', 'cheapest', 'cheap'],
                                'a2' => ['less', 'least', 'fewer'],
                                'a3' => ['sweetest', 'sweeter', 'sweet'],
                            ],
                            'verb_hints' => ['a1' => 'not (cafe)'],
                            'concept' => 'negative pricing with extra comparisons',
                        ],
                        'a1' => [
                            'question' => 'The cafe will not be {a1} to visit.',
                            'answers' => ['a1' => 'cheap'],
                            'options' => ['a1' => ['cheap', 'cheaper', 'cheapest']],
                            'verb_hints' => ['a1' => 'not (cafe)'],
                            'concept' => 'single negative projection',
                        ],
                    ],
                ],
            ],
            [
                'tense' => 'future',
                'form' => 'question',
                'variants' => [
                    'simple' => [
                        'question' => 'Will the road be {a1} next year?',
                        'answers' => ['a1' => 'wider'],
                        'options' => ['a1' => ['wider', 'widest', 'wide']],
                        'concept' => 'future comparative plan',
                        'example' => 'Will the street be wider than now?',
                    ],
                    'paired' => [
                        'default' => [
                            'question' => 'Will the road be {a1} next year, or remain as {a2} as now?',
                            'answers' => ['a1' => 'wider', 'a2' => 'wide'],
                            'options' => [
                                'a1' => ['wider', 'widest', 'wide'],
                                'a2' => ['wide', 'wider', 'widest'],
                            ],
                            'concept' => 'future comparative vs equality',
                        ],
                        'a1' => [
                            'question' => 'Will the road stay {a1}?',
                            'answers' => ['a1' => 'wide'],
                            'options' => ['a1' => ['wide', 'wider', 'widest']],
                            'concept' => 'simple dimension question',
                        ],
                    ],
                    'extended' => [
                        'default' => [
                            'question' => 'Will the road be {a1} next year, the bike lane {a2} than before, and the sidewalks the {a3} feature?',
                            'answers' => ['a1' => 'wider', 'a2' => 'safer', 'a3' => 'newest'],
                            'options' => [
                                'a1' => ['wider', 'widest', 'wide'],
                                'a2' => ['safer', 'safest', 'safe'],
                                'a3' => ['newest', 'newer', 'new'],
                            ],
                            'concept' => 'future upgrade comparisons',
                        ],
                        'a1' => [
                            'question' => 'Will the road be {a1}?',
                            'answers' => ['a1' => 'wider'],
                            'options' => ['a1' => ['wider', 'wide', 'widest']],
                            'concept' => 'single projection',
                        ],
                    ],
                ],
            ],
            [
                'tense' => 'future',
                'form' => 'question',
                'variants' => [
                    'simple' => [
                        'question' => 'Will we have {a1} chairs than yesterday?',
                        'answers' => ['a1' => 'more'],
                        'options' => ['a1' => ['more', 'most', 'many']],
                        'concept' => 'quantity comparison',
                        'example' => 'Will we have more seats?',
                    ],
                    'paired' => [
                        'default' => [
                            'question' => 'Will we have {a1} chairs than yesterday so everyone sits {a2}?',
                            'answers' => ['a1' => 'more', 'a2' => 'comfortably'],
                            'options' => [
                                'a1' => ['more', 'most', 'many'],
                                'a2' => ['comfortably', 'more comfortably', 'most comfortably'],
                            ],
                            'concept' => 'quantity plus adverb comparison',
                        ],
                        'a1' => [
                            'question' => 'Will there be {a1} chairs?',
                            'answers' => ['a1' => 'more'],
                            'options' => ['a1' => ['more', 'most', 'many']],
                            'concept' => 'simple future quantity',
                        ],
                    ],
                    'extended' => [
                        'default' => [
                            'question' => 'Will we have {a1} chairs than yesterday, {a2} tables than today, and the {a3} space for everyone?',
                            'answers' => ['a1' => 'more', 'a2' => 'fewer', 'a3' => 'least'],
                            'options' => [
                                'a1' => ['more', 'most', 'many'],
                                'a2' => ['fewer', 'fewest', 'few'],
                                'a3' => ['least', 'less', 'fewer'],
                            ],
                            'concept' => 'quantity comparisons with count vs mass',
                        ],
                        'a1' => [
                            'question' => 'Will we get {a1} chairs tomorrow?',
                            'answers' => ['a1' => 'more'],
                            'options' => ['a1' => ['more', 'most', 'many']],
                            'concept' => 'single quantity forecast',
                        ],
                    ],
                ],
            ],
            [
                'tense' => 'future',
                'form' => 'question',
                'variants' => [
                    'simple' => [
                        'question' => 'Will the lake be {a1} from the town than the sea?',
                        'answers' => ['a1' => 'farther'],
                        'options' => ['a1' => ['farther', 'further', 'furthest']],
                        'concept' => 'irregular distance comparative',
                        'example' => 'The shop is farther than the park.',
                    ],
                    'paired' => [
                        'default' => [
                            'question' => 'Will the lake be {a1} from the town than the sea, or about the {a2} distance?',
                            'answers' => ['a1' => 'farther', 'a2' => 'same'],
                            'options' => [
                                'a1' => ['farther', 'further', 'furthest'],
                                'a2' => ['same', 'similar', 'equal'],
                            ],
                            'concept' => 'distance comparison vs equality',
                        ],
                        'a1' => [
                            'question' => 'Will the lake be much {a1}?',
                            'answers' => ['a1' => 'farther'],
                            'options' => ['a1' => ['farther', 'further', 'furthest']],
                            'concept' => 'simple distance idea',
                        ],
                    ],
                    'extended' => [
                        'default' => [
                            'question' => 'Will the lake be {a1} from the town than the sea, the hill even {a2}, and the campsite the {a3} spot we choose?',
                            'answers' => ['a1' => 'farther', 'a2' => 'further', 'a3' => 'closest'],
                            'options' => [
                                'a1' => ['farther', 'further', 'furthest'],
                                'a2' => ['further', 'furthest', 'farther'],
                                'a3' => ['closest', 'closer', 'close'],
                            ],
                            'concept' => 'irregular distance set',
                        ],
                        'a1' => [
                            'question' => 'Will the lake be {a1} away?',
                            'answers' => ['a1' => 'farther'],
                            'options' => ['a1' => ['farther', 'further', 'furthest']],
                            'concept' => 'single distance projection',
                        ],
                    ],
                ],
            ],
            [
                'tense' => 'future',
                'form' => 'statement',
                'variants' => [
                    'simple' => [
                        'question' => 'There will be {a1} apples than oranges at the market.',
                        'answers' => ['a1' => 'more'],
                        'options' => ['a1' => ['more', 'most', 'many']],
                        'concept' => 'future quantity contrast',
                        'example' => 'There will be more apples available.',
                    ],
                    'paired' => [
                        'default' => [
                            'question' => 'There will be {a1} apples than oranges at the market, so buy the {a2} ones early.',
                            'answers' => ['a1' => 'more', 'a2' => 'freshest'],
                            'options' => [
                                'a1' => ['more', 'most', 'many'],
                                'a2' => ['freshest', 'fresher', 'fresh'],
                            ],
                            'concept' => 'quantity and quality',
                        ],
                        'a1' => [
                            'question' => 'There will be {a1} apples tomorrow.',
                            'answers' => ['a1' => 'more'],
                            'options' => ['a1' => ['more', 'most', 'many']],
                            'concept' => 'single future statement',
                        ],
                    ],
                    'extended' => [
                        'default' => [
                            'question' => 'There will be {a1} apples than oranges at the market, {a2} pears than plums, and the {a3} berries by noon.',
                            'answers' => ['a1' => 'more', 'a2' => 'fewer', 'a3' => 'sweetest'],
                            'options' => [
                                'a1' => ['more', 'most', 'many'],
                                'a2' => ['fewer', 'fewest', 'few'],
                                'a3' => ['sweetest', 'sweeter', 'sweet'],
                            ],
                            'concept' => 'future market mix',
                        ],
                        'a1' => [
                            'question' => 'There will be many {a1} apples.',
                            'answers' => ['a1' => 'more'],
                            'options' => ['a1' => ['more', 'most', 'many']],
                            'concept' => 'single abundance note',
                        ],
                    ],
                ],
            ],
            [
                'tense' => 'future',
                'form' => 'statement',
                'variants' => [
                    'simple' => [
                        'question' => 'Tomorrow I will pack the {a1} clothes for the trip.',
                        'answers' => ['a1' => 'lightest'],
                        'options' => ['a1' => ['lightest', 'lighter', 'light']],
                        'concept' => 'future superlative choice',
                        'example' => 'Pack the lightest jacket.',
                    ],
                    'paired' => [
                        'default' => [
                            'question' => 'Tomorrow I will pack the {a1} clothes for the trip to carry the {a2} weight.',
                            'answers' => ['a1' => 'lightest', 'a2' => 'least'],
                            'options' => [
                                'a1' => ['lightest', 'lighter', 'light'],
                                'a2' => ['least', 'less', 'fewer'],
                            ],
                            'concept' => 'weight minimization',
                        ],
                        'a1' => [
                            'question' => 'Tomorrow I will pack {a1} clothes.',
                            'answers' => ['a1' => 'light'],
                            'options' => ['a1' => ['light', 'lighter', 'lightest']],
                            'concept' => 'simple packing plan',
                        ],
                    ],
                    'extended' => [
                        'default' => [
                            'question' => 'Tomorrow I will pack the {a1} clothes, choose {a2} shoes than usual, and take the {a3} number of books.',
                            'answers' => ['a1' => 'lightest', 'a2' => 'lighter', 'a3' => 'fewest'],
                            'options' => [
                                'a1' => ['lightest', 'lighter', 'light'],
                                'a2' => ['lighter', 'lightest', 'light'],
                                'a3' => ['fewest', 'fewer', 'few'],
                            ],
                            'concept' => 'packing priorities with quantities',
                        ],
                        'a1' => [
                            'question' => 'Tomorrow I will pack the {a1} bag.',
                            'answers' => ['a1' => 'lightest'],
                            'options' => ['a1' => ['lightest', 'lighter', 'light']],
                            'concept' => 'single choice future',
                        ],
                    ],
                ],
            ],
        ];
    }

    private function pickVariant(array $variantSet, string $level): array
    {
        if (isset($variantSet['a1']) && $level === 'A1') {
            return $variantSet['a1'];
        }

        if (isset($variantSet['default'])) {
            return $variantSet['default'];
        }

        return $variantSet;
    }

    private function buildHints(array $variant): array
    {
        $formula = $variant['formula'] ?? 'Formula: comparative = adjective/adverb + -er + than; longer words often use more/less + adjective. Superlative = -est/most/least.';
        $example = $variant['example'] ?? 'Example: "faster than a car", "as calm as before", "the most helpful".';
        $reminder = $variant['reminder'] ?? 'Check if the prompt needs equality (as ... as), comparative (+ than), or superlative (the + -est/most).';

        return [$formula, $example, $reminder];
    }

    private function buildExplanations(array $optionSets, string $concept): array
    {
        $explanations = [];
        foreach ($this->flattenOptions($optionSets) as $option) {
            $explanations[$option] = $this->describeOption($option, $concept);
        }

        return $explanations;
    }

    private function describeOption(string $option, string $concept): string
    {
        $normalized = strtolower($option);

        if (str_contains($normalized, 'est') || str_contains($normalized, 'most')) {
            return 'Superlative forms (-est/most) compare one item with the whole set; use them only when selecting the top degree rather than comparing two things.';
        }

        if (str_contains($normalized, 'more') || str_contains($normalized, 'less')) {
            return 'Comparatives with more/less highlight differences for longer adjectives or quantities; confirm the noun type and whether a shorter -er form fits.';
        }

        if (str_contains($normalized, 'as')) {
            return 'The as ... as frame signals equality; ensure both halves of the structure balance when the idea is similarity rather than ranking.';
        }

        if (str_contains($normalized, 'fewer') || str_contains($normalized, 'fewest')) {
            return 'Use fewer/fewest with countable nouns; pair them with plural nouns when talking about number comparisons.';
        }

        return 'Match the form to the comparison goal for ' . $concept . ': use -er/than for two items, as ... as for equality, and -est/most/least for top degree statements.';
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
