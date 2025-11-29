<?php

namespace Database\Seeders\V2;

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
                    'a1' => 'base adjective: light',
                ],
                'level' => 'A1',
                'source' => 'A1',
                'tags' => ['affirmative sentence', 'comparative', 'present simple'],
                'hints' => [
                    'a1' => 'For short adjectives comparing two items, add -er before "than".',
                ],
                'explanations' => [
                    'a1' => [
                        'lighter' => 'Use the comparative form of a short adjective: adjective + -er + than.',
                        'lightest' => 'This is a superlative form used with "the" when comparing three or more.',
                        'more light' => 'Short adjectives normally do not use "more" for the comparative.',
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
                    'a1' => 'base adjective: heavy',
                ],
                'level' => 'A1',
                'source' => 'A1',
                'tags' => ['affirmative sentence', 'comparative', 'present simple'],
                'hints' => [
                    'a1' => 'Short adjectives ending in -y change to -ier when comparing two things.',
                ],
                'explanations' => [
                    'a1' => [
                        'heavier' => 'Comparative pattern: adjective ending in -y → -ier + than.',
                        'heavy' => 'Base form is used for "as...as" patterns, not after "than".',
                        'more heavy' => 'Short adjectives typically form the comparative with -er instead of "more".',
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
                    'a1' => 'superlative for busy',
                ],
                'level' => 'A1',
                'source' => 'A1',
                'tags' => ['superlative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'Use the superlative form with "the" when choosing one item out of many.',
                ],
                'explanations' => [
                    'a1' => [
                        'busiest' => 'Superlative pattern for short adjectives ending in -y: drop y + -iest.',
                        'busier' => 'Comparative form compares two things, not one against all.',
                        'more busy' => 'Short adjectives usually take -est for the superlative rather than "most".',
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
                    'a1' => 'comparative form of fast',
                ],
                'level' => 'A1',
                'source' => 'A1',
                'tags' => ['comparative', 'affirmative sentence', 'present simple', 'adverb comparison'],
                'hints' => [
                    'a1' => 'Short adverbs share the -er pattern when comparing two actions.',
                ],
                'explanations' => [
                    'a1' => [
                        'faster' => 'Comparative adverb pattern: base + -er + than.',
                        'fastest' => 'This is the superlative form used with "the" for three or more items.',
                        'fastly' => 'The adverb already exists as "fast"; adding -ly is incorrect here.',
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
                    'a1' => 'short adjective ending with consonant + vowel + consonant',
                ],
                'level' => 'A1',
                'source' => 'A1',
                'tags' => ['comparative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'Double the final consonant for one-syllable CVC adjectives before adding -er.',
                ],
                'explanations' => [
                    'a1' => [
                        'bigger' => 'Pattern: CVC adjective → double final consonant + -er for comparison.',
                        'biggest' => 'Superlative requires "the" and is used when comparing more than two.',
                        'more big' => 'Short adjectives prefer the -er comparative instead of "more".',
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
                    'a1' => 'superlative form with -est',
                ],
                'level' => 'A1',
                'source' => 'A1',
                'tags' => ['superlative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'To show the extreme degree among many roads, use the -est form.',
                ],
                'explanations' => [
                    'a1' => [
                        'narrowest' => 'Superlative of a short adjective: base + -est with "the".',
                        'narrower' => 'This is comparative and fits when only two roads are compared.',
                        'more narrow' => 'Short adjectives typically use -er/-est instead of "more".',
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
                    'a1' => 'short adjective: tall',
                ],
                'level' => 'A1',
                'source' => 'A1',
                'tags' => ['comparative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'Comparing two people needs the -er form plus "than".',
                ],
                'explanations' => [
                    'a1' => [
                        'taller' => 'Comparative of a one-syllable adjective uses -er with than.',
                        'tallest' => 'Superlative is for one out of many and usually requires "the".',
                        'tall' => 'Base form fits patterns like "as...as", not after "than".',
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
                    'a1' => 'adjective ending in -y',
                ],
                'level' => 'A1',
                'source' => 'A1',
                'tags' => ['comparative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'Change final -y to -i before adding -er for short adjectives.',
                ],
                'explanations' => [
                    'a1' => [
                        'easier' => 'Comparative rule: drop -y, add -ier, then use with "than".',
                        'easiest' => 'Superlative form compares more than two puzzles and uses "the".',
                        'more easy' => 'Short adjectives take the -er comparative rather than "more".',
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
                    'a1' => 'superlative of small',
                ],
                'level' => 'A1',
                'source' => 'A1',
                'tags' => ['superlative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'To show the extreme degree among many, use the -est form with "the".',
                ],
                'explanations' => [
                    'a1' => [
                        'smallest' => 'Superlative rule: base adjective + -est for the highest degree.',
                        'smaller' => 'Comparative is only for two items and uses -er.',
                        'more small' => 'Short adjectives use -er/-est instead of "more"/"most".',
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
                    'a1' => 'comparative of slow',
                ],
                'level' => 'A1',
                'source' => 'A1',
                'tags' => ['comparative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'Use adjective + -er to compare two ways of travel.',
                ],
                'explanations' => [
                    'a1' => [
                        'slower' => 'Comparative of a short adjective uses -er before "than".',
                        'slowest' => 'Superlative would need "the" and implies three or more options.',
                        'more slow' => 'Short adjectives generally avoid "more" for comparison.',
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
                    'a1' => 'change -y to -i then add -er',
                ],
                'level' => 'A1',
                'source' => 'A1',
                'tags' => ['comparative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'Short adjectives ending in -y take -ier in the comparative.',
                ],
                'explanations' => [
                    'a1' => [
                        'funnier' => 'Pattern: adjective ending in -y → replace y with i and add -er for comparison.',
                        'funniest' => 'Superlative form requires "the" and compares more than two stories.',
                        'more funny' => 'Short adjectives use the -er form rather than "more".',
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
                    'a1' => 'superlative with -est',
                ],
                'level' => 'A1',
                'source' => 'A1',
                'tags' => ['superlative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'Use the superlative form to show the top position among many classmates.',
                ],
                'explanations' => [
                    'a1' => [
                        'smartest' => 'Superlative of a one-syllable adjective adds -est with "the".',
                        'smarter' => 'Comparative only compares two students.',
                        'more smart' => 'Short adjectives prefer the -er/-est pattern instead of "more".',
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
                    'a1' => 'use base adjective in as...as',
                ],
                'level' => 'A2',
                'source' => 'A2',
                'tags' => ['negative sentence', 'equality structure', 'present simple'],
                'hints' => [
                    'a1' => 'In "as...as" comparisons, keep the adjective in its base form.',
                ],
                'explanations' => [
                    'a1' => [
                        'hot' => 'The pattern "not as...as" uses the base adjective without comparative endings.',
                        'hotter' => 'Adding -er is for direct comparisons with "than".',
                        'hottest' => 'Superlative needs "the" and involves three or more items.',
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
                    'a1' => 'short adjective comparative',
                ],
                'level' => 'A2',
                'source' => 'A2',
                'tags' => ['interrogative sentence', 'comparative', 'present simple'],
                'hints' => [
                    'a1' => 'For two things, short adjectives usually add -er before "than".',
                ],
                'explanations' => [
                    'a1' => [
                        'sweeter' => 'Comparative rule: adjective + -er + than for two items.',
                        'sweetest' => 'Superlative requires "the" and is used for three or more cookies.',
                        'more sweet' => 'Short adjectives tend to avoid "more" in the comparative.',
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
                    'a1' => 'comparative of narrow',
                ],
                'level' => 'A2',
                'source' => 'A2',
                'tags' => ['comparative', 'affirmative sentence', 'present simple', 'advice'],
                'hints' => [
                    'a1' => 'Use the -er form to compare two roads or expectations.',
                ],
                'explanations' => [
                    'a1' => [
                        'narrower' => 'Comparative pattern for short adjectives adds -er with "than".',
                        'narrowest' => 'Superlative describes the extreme among several paths.',
                        'more narrow' => 'Short adjectives typically form the comparative with -er.',
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
                    'a1' => 'superlative with the + -est',
                ],
                'level' => 'A2',
                'source' => 'A2',
                'tags' => ['superlative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'When choosing one out of many, use the superlative with "the".',
                ],
                'explanations' => [
                    'a1' => [
                        'brightest' => 'Superlative: adjective + -est shows the highest degree.',
                        'brighter' => 'Comparative only contrasts two rooms.',
                        'more bright' => 'Short adjectives prefer -er/-est rather than "more".',
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
                    'a1' => 'comparative of a long adverb',
                ],
                'level' => 'A2',
                'source' => 'A2',
                'tags' => ['comparative', 'affirmative sentence', 'adverb comparison', 'present simple'],
                'hints' => [
                    'a1' => 'Long adverbs form the comparative with "more" before the word.',
                ],
                'explanations' => [
                    'a1' => [
                        'more fluently' => 'Pattern: more + adverb + than for longer adverbs.',
                        'most fluently' => 'Superlative uses "the most" when comparing more than two people.',
                        'fluently' => 'Base form suits "as...as" structures, not direct comparison with than.',
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
                    'a1' => 'comparative of hard',
                ],
                'level' => 'A2',
                'source' => 'A2',
                'tags' => ['comparative', 'negative sentence', 'past simple'],
                'hints' => [
                    'a1' => 'Even in negatives, comparing two exams needs the -er form with "than".',
                ],
                'explanations' => [
                    'a1' => [
                        'harder' => 'Comparative of a short adjective adds -er to show difference.',
                        'hardest' => 'Superlative indicates the extreme among several tests.',
                        'more hard' => 'Short adjectives do not usually take "more" for the comparative.',
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
                    'a1' => 'superlative of fast',
                ],
                'level' => 'A2',
                'source' => 'A2',
                'tags' => ['superlative', 'interrogative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'When asking about the top option among many, use the superlative with "the".',
                ],
                'explanations' => [
                    'a1' => [
                        'fastest' => 'Superlative of a short adjective uses -est for the highest degree.',
                        'faster' => 'Comparative compares only two routes.',
                        'more fast' => 'Short adjectives avoid "more" and use -er/-est instead.',
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
                    'a1' => 'comparative of clear',
                ],
                'level' => 'A2',
                'source' => 'A2',
                'tags' => ['comparative', 'affirmative sentence', 'past simple'],
                'hints' => [
                    'a1' => 'Use the -er form to compare two explanations directly.',
                ],
                'explanations' => [
                    'a1' => [
                        'clearer' => 'Short adjectives form the comparative with -er when followed by "than".',
                        'clearest' => 'Superlative describes the highest degree among several explanations.',
                        'more clear' => 'Short adjectives typically avoid "more" for comparison.',
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
                    'a1' => 'comparative adverb of late',
                ],
                'level' => 'A2',
                'source' => 'A2',
                'tags' => ['comparative', 'affirmative sentence', 'past simple', 'adverb comparison'],
                'hints' => [
                    'a1' => 'Irregular adverb uses -er in the comparative to show a delay.',
                ],
                'explanations' => [
                    'a1' => [
                        'later' => 'Comparative adverb pattern: add -er to show a greater delay.',
                        'latest' => 'Superlative form with -est is used with "the".',
                        'more late' => 'The adverb already has a set comparative without "more".',
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
                    'a1' => 'comparative of cold',
                ],
                'level' => 'A2',
                'source' => 'A2',
                'tags' => ['comparative', 'future reference', 'affirmative sentence'],
                'hints' => [
                    'a1' => 'For two nights, add -er to the adjective before "than".',
                ],
                'explanations' => [
                    'a1' => [
                        'colder' => 'Comparative of a short adjective uses -er to show lower temperature.',
                        'coldest' => 'Superlative would compare three or more nights.',
                        'more cold' => 'Short adjectives use -er rather than "more" in comparisons.',
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
                    'a1' => 'comparative of little (amount)',
                ],
                'level' => 'A2',
                'source' => 'A2',
                'tags' => ['comparative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'To show a smaller degree, use the comparative form opposite of "more".',
                ],
                'explanations' => [
                    'a1' => [
                        'less' => 'Comparative of small amount pairs with adjectives to show decrease.',
                        'lesser' => 'This form is formal and often used before a noun, not before an adjective alone.',
                        'least' => 'Superlative indicates the smallest degree among several times.',
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
                    'a1' => 'superlative with most + long adjective',
                ],
                'level' => 'A2',
                'source' => 'A2',
                'tags' => ['superlative', 'affirmative sentence', 'present perfect context'],
                'hints' => [
                    'a1' => 'For long adjectives, use "the most" to express the highest degree.',
                ],
                'explanations' => [
                    'a1' => [
                        'most exciting' => 'Long adjectives form the superlative with "the most" + adjective.',
                        'more exciting' => 'Comparative only compares two events.',
                        'excitingest' => 'This form is non-standard; long adjectives avoid -est endings.',
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
                    'a1' => 'comparative with more + long adjective',
                ],
                'level' => 'B1',
                'source' => 'B1',
                'tags' => ['comparative', 'past simple', 'affirmative sentence'],
                'hints' => [
                    'a1' => 'Long adjectives take "more" for the comparative when contrasting two things.',
                ],
                'explanations' => [
                    'a1' => [
                        'more thrilling' => 'Comparative of a long adjective uses more + adjective + than.',
                        'most thrilling' => 'Superlative is for one item out of three or more.',
                        'thrillingest' => 'Adding -est to long adjectives is non-standard.',
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
                    'a1' => 'comparative of careful (adverb)',
                ],
                'level' => 'B1',
                'source' => 'B1',
                'tags' => ['comparative', 'present simple', 'adverb comparison'],
                'hints' => [
                    'a1' => 'Use more + adverb + than for comparing manners of action.',
                ],
                'explanations' => [
                    'a1' => [
                        'more carefully' => 'Long adverbs use more + adverb + than for comparison.',
                        'most carefully' => 'Superlative with "the" compares three or more situations.',
                        'carefuller' => 'Adding -er is not standard for this adverb.',
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
                    'a1' => 'short adjective comparative',
                ],
                'level' => 'B1',
                'source' => 'B1',
                'tags' => ['comparative', 'interrogative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'For two routes, use the -er comparative before "than" is implied.',
                ],
                'explanations' => [
                    'a1' => [
                        'safer' => 'Short adjectives often take -er in questions comparing two options.',
                        'safest' => 'Superlative would require choosing one out of several routes.',
                        'more safe' => 'Short adjectives commonly avoid "more" for comparison.',
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
                    'a1' => 'comparative adverb of early',
                ],
                'level' => 'B1',
                'source' => 'B1',
                'tags' => ['comparative', 'past simple', 'adverb comparison'],
                'hints' => [
                    'a1' => 'Some adverbs have an -er form to show a sooner time.',
                ],
                'explanations' => [
                    'a1' => [
                        'earlier' => 'Irregular adverb uses -er for comparison of time.',
                        'earliest' => 'Superlative suggests the soonest among several times.',
                        'more early' => 'This phrase is less common; the -er form is standard.',
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
                    'a1' => 'superlative using least',
                ],
                'level' => 'B1',
                'source' => 'B1',
                'tags' => ['superlative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'To show the lowest degree of cost, use "the least" with the adjective.',
                ],
                'explanations' => [
                    'a1' => [
                        'least expensive' => 'Superlative of long adjectives can use "the least" for the lowest degree.',
                        'less expensive' => 'Comparative only contrasts two prices.',
                        'more inexpensive' => 'Double marking makes the phrase awkward; use a single superlative marker instead.',
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
                    'a1' => 'irregular comparative of good',
                ],
                'level' => 'B1',
                'source' => 'B1',
                'tags' => ['comparative', 'affirmative sentence', 'past simple', 'irregular adjective'],
                'hints' => [
                    'a1' => 'Use the irregular comparative form of "good" when comparing two attempts.',
                ],
            ],
            [
                'question' => 'By next year, the new road will make the journey {a1}.',
                'options' => [
                    'a1' => ['shorter', 'shortest', 'more short'],
                ],
                'answers' => ['a1' => 'shorter'],
                'verb_hints' => [
                    'a1' => 'comparative for future improvement',
                ],
                'level' => 'B1',
                'source' => 'B1',
                'tags' => ['comparative', 'future reference', 'affirmative sentence'],
                'hints' => [
                    'a1' => 'Use the -er form of a short adjective to show a reduced distance.',
                ],
                'explanations' => [
                    'a1' => [
                        'shorter' => 'Comparative of a short adjective uses -er to indicate a smaller amount.',
                        'shortest' => 'Superlative is for the extreme among several options.',
                        'more short' => 'Short adjectives prefer -er/-est instead of "more".',
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
                    'a1' => 'equality with as...as for long adjective',
                ],
                'level' => 'B1',
                'source' => 'B1',
                'tags' => ['equality structure', 'negative sentence', 'comparative', 'past simple'],
                'hints' => [
                    'a1' => 'To say two things are similar, use "as" + adjective + "as" even in negatives.',
                ],
                'explanations' => [
                    'a1' => [
                        'as entertaining' => 'Equality pattern keeps the adjective in base form within as...as.',
                        'more entertaining' => 'This would state the sequel is superior, which the sentence denies.',
                        'most entertaining' => 'Superlative suggests it is better than all others, not equal.',
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
                    'a1' => 'superlative adverb with most',
                ],
                'level' => 'B1',
                'source' => 'B1',
                'tags' => ['superlative', 'adverb comparison', 'past simple'],
                'hints' => [
                    'a1' => 'When comparing three or more people, use "the most" + adverb.',
                ],
                'explanations' => [
                    'a1' => [
                        'most confidently' => 'Superlative adverb uses the most + adverb to show the highest degree.',
                        'more confidently' => 'Comparative only contrasts two candidates.',
                        'confidence' => 'This is a noun, not an adverbial form.',
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
                    'a1' => 'comparative with more + long adjective',
                ],
                'level' => 'B1',
                'source' => 'B1',
                'tags' => ['comparative', 'interrogative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'Long adjectives take "more" for comparison of two items.',
                ],
                'explanations' => [
                    'a1' => [
                        'more stylish' => 'Comparative with a long adjective uses more + adjective + than.',
                        'most stylish' => 'Superlative is reserved for one out of many choices.',
                        'stylelier' => 'This spelling is non-standard; use a periphrastic comparative instead.',
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
                    'a1' => 'superlative of little (quantity)',
                ],
                'level' => 'B1',
                'source' => 'B1',
                'tags' => ['superlative', 'affirmative sentence', 'past simple'],
                'hints' => [
                    'a1' => 'To show the smallest amount among options, use the superlative form opposite of "most".',
                ],
                'explanations' => [
                    'a1' => [
                        'least' => 'Superlative of small quantity uses the least before the adjective.',
                        'less' => 'Comparative indicates a smaller amount between two.',
                        'lesser' => 'This form is used attributively and sounds formal, not with adjectives alone.',
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
                    'a1' => 'comparative of hard (adverb)',
                ],
                'level' => 'B1',
                'source' => 'B1',
                'tags' => ['comparative', 'affirmative sentence', 'past simple', 'adverb comparison'],
                'hints' => [
                    'a1' => 'Use the irregular comparative adverb form to show extra effort.',
                ],
                'explanations' => [
                    'a1' => [
                        'harder' => 'Adverbial comparative keeps the same form with -er to show greater intensity.',
                        'hardest' => 'Superlative is for the highest degree among several periods.',
                        'more hard' => 'The adverb does not need "more" for comparison.',
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
                    'a1' => 'comparative of strong',
                ],
                'level' => 'B2',
                'source' => 'B2',
                'tags' => ['comparative', 'past perfect context', 'inversion structure'],
                'hints' => [
                    'a1' => 'After contrasting two conditions, use adjective + -er before "than".',
                ],
                'explanations' => [
                    'a1' => [
                        'stronger' => 'Comparative of a short adjective uses -er to mark the increased force.',
                        'strongest' => 'Superlative signals the highest degree among several, not two.',
                        'more strong' => 'Short adjectives generally avoid "more" for the comparative.',
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
                    'a1' => 'comparative of long adverb beautifully',
                ],
                'level' => 'B2',
                'source' => 'B2',
                'tags' => ['comparative', 'affirmative sentence', 'adverb comparison', 'present simple'],
                'hints' => [
                    'a1' => 'Long adverbs take "more" to compare performance in two settings.',
                ],
                'explanations' => [
                    'a1' => [
                        'more beautifully' => 'Pattern: more + adverb + than for multi-syllable adverbs.',
                        'most beautifully' => 'Superlative compares three or more performances.',
                        'beautifuller' => 'This form is non-standard for the adverb.',
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
                    'a1' => 'superlative with most + long adjective',
                ],
                'level' => 'B2',
                'source' => 'B2',
                'tags' => ['superlative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'When comparing three or more options, use "the most" with long adjectives.',
                ],
                'explanations' => [
                    'a1' => [
                        'most feasible' => 'Superlative for long adjectives uses the most + adjective to show top suitability.',
                        'more feasible' => 'Comparative only ranks two proposals.',
                        'feasiblest' => 'Long adjectives avoid -est endings.',
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
                    'a1' => 'comparative with more + long adjective',
                ],
                'level' => 'B2',
                'source' => 'B2',
                'tags' => ['comparative', 'past simple', 'contrast clause'],
                'hints' => [
                    'a1' => 'Use "more" with long adjectives to show increased difficulty.',
                ],
                'explanations' => [
                    'a1' => [
                        'more complex' => 'Comparative with long adjective follows more + adjective + than.',
                        'most complex' => 'Superlative indicates top difficulty among several.',
                        'complexer' => 'This ending is unusual for long adjectives.',
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
                    'a1' => 'comparative with more + long adjective',
                ],
                'level' => 'B2',
                'source' => 'B2',
                'tags' => ['comparative', 'conditional clause', 'past perfect context'],
                'hints' => [
                    'a1' => 'Long adjectives in hypothetical comparisons use more + adjective.',
                ],
                'explanations' => [
                    'a1' => [
                        'more comfortable' => 'Comparative pattern uses more + adjective + than in conditional ideas.',
                        'most comfortable' => 'Superlative suits choosing the best among several seating options.',
                        'comfortabler' => 'Adding -er to this adjective is non-standard.',
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
                    'a1' => 'comparative showing decrease with adverb',
                ],
                'level' => 'B2',
                'source' => 'B2',
                'tags' => ['comparative', 'adverb comparison', 'present simple'],
                'hints' => [
                    'a1' => 'Use the comparative opposite of "more" to show reduced performance.',
                ],
                'explanations' => [
                    'a1' => [
                        'less efficiently' => 'Comparative of efficiency uses less + adverb + than to show decline.',
                        'least efficiently' => 'Superlative marks the lowest degree among several situations.',
                        'inefficiently' => 'Base form lacks the comparative marker for two conditions.',
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
                    'a1' => 'comparative with more + adjective',
                ],
                'level' => 'B2',
                'source' => 'B2',
                'tags' => ['comparative', 'affirmative sentence', 'past simple'],
                'hints' => [
                    'a1' => 'Longer adjectives often use "more" for comparison between two moments.',
                ],
                'explanations' => [
                    'a1' => [
                        'more certain' => 'Comparative with more + adjective expresses increased confidence.',
                        'most certain' => 'Superlative indicates the highest degree among several options.',
                        'certainer' => 'This form is unusual; use the periphrastic comparative instead.',
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
                    'a1' => 'comparative showing reduction with long adjective',
                ],
                'level' => 'B2',
                'source' => 'B2',
                'tags' => ['comparative', 'affirmative sentence', 'past simple'],
                'hints' => [
                    'a1' => 'To show a smaller degree, pair "less" with the adjective.',
                ],
                'explanations' => [
                    'a1' => [
                        'less dramatic' => 'Comparative using less + adjective expresses a reduced degree.',
                        'least dramatic' => 'Superlative indicates the minimal degree among several shows.',
                        'more dramatic' => 'This would mean the opposite, implying greater intensity.',
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
                    'a1' => 'superlative adverb with most',
                ],
                'level' => 'B2',
                'source' => 'B2',
                'tags' => ['superlative', 'adverb comparison', 'present simple'],
                'hints' => [
                    'a1' => 'When comparing more than two people, use "the most" + adverb.',
                ],
                'explanations' => [
                    'a1' => [
                        'most calmly' => 'Superlative adverb structure: the most + adverb for the highest degree.',
                        'more calmly' => 'Comparative only compares two interns.',
                        'calmer' => 'This is an adjective form and not the correct adverbial superlative.',
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
                    'a1' => 'comparative of large',
                ],
                'level' => 'B2',
                'source' => 'B2',
                'tags' => ['comparative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'Short adjectives use -er to compare appearance with reality.',
                ],
                'explanations' => [
                    'a1' => [
                        'larger' => 'Comparative of a short adjective forms with -er and an implied than.',
                        'largest' => 'Superlative requires "the" and compares more than two castles.',
                        'more large' => 'Short adjectives typically avoid "more" for the comparative.',
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
                    'a1' => 'irregular comparative of bad',
                ],
                'level' => 'B2',
                'source' => 'B2',
                'tags' => ['comparative', 'conditional clause', 'future reference', 'irregular adjective'],
                'hints' => [
                    'a1' => 'Use the irregular comparative form to show decline in conditions.',
                ],
                'explanations' => [
                    'a1' => [
                        'worse' => 'Irregular comparative for bad indicates a poorer state.',
                        'worst' => 'Superlative marks the lowest quality among several times.',
                        'baddest' => 'This colloquial form is not standard for formal comparison.',
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
                    'a1' => 'comparative adverb with more',
                ],
                'level' => 'B2',
                'source' => 'B2',
                'tags' => ['comparative', 'adverb comparison', 'past simple'],
                'hints' => [
                    'a1' => 'Many adverbs ending in -ly use more + adverb + than for comparison.',
                ],
                'explanations' => [
                    'a1' => [
                        'more quickly' => 'Comparative adverb with -ly uses more + adverb to show speed difference.',
                        'most quickly' => 'Superlative compares three or more teams.',
                        'quickerly' => 'This form is incorrect; -ly adverbs avoid the -er ending.',
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
                    'a1' => 'comparative adverb with more',
                ],
                'level' => 'C1',
                'source' => 'C1',
                'tags' => ['comparative', 'conditional clause', 'past perfect context', 'adverb comparison'],
                'hints' => [
                    'a1' => 'For longer adverbs in hypothetical statements, use more + adverb.',
                ],
                'explanations' => [
                    'a1' => [
                        'more smoothly' => 'Comparative adverb with more shows a higher degree of ease.',
                        'most smoothly' => 'Superlative would suggest comparison among many scenarios.',
                        'smoother' => 'This adjective form does not fit the adverbial role here.',
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
                    'a1' => 'comparative adverb showing degree',
                ],
                'level' => 'C1',
                'source' => 'C1',
                'tags' => ['comparative', 'affirmative sentence', 'present simple', 'adverb comparison'],
                'hints' => [
                    'a1' => 'Use more + adverb to express a stronger manner of believing.',
                ],
                'explanations' => [
                    'a1' => [
                        'more firmly' => 'Comparative of manner uses more + adverb to indicate increased strength.',
                        'most firmly' => 'Superlative would compare several people, not just a change in one person.',
                        'firmest' => 'This is an adjective superlative and does not modify the verb appropriately.',
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
                    'a1' => 'superlative marker before adjective',
                ],
                'level' => 'C1',
                'source' => 'C1',
                'tags' => ['superlative', 'affirmative sentence', 'present perfect context'],
                'hints' => [
                    'a1' => 'To show the highest degree, place the superlative determiner before the adjective.',
                ],
                'explanations' => [
                    'a1' => [
                        'most' => 'Superlative construction requires the + most before a long adjective.',
                        'more' => 'This would form a comparative rather than a superlative.',
                        'mostly' => 'As an adverb, it does not fit the noun phrase structure.',
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
                    'a1' => 'comparative adverb with more',
                ],
                'level' => 'C1',
                'source' => 'C1',
                'tags' => ['comparative', 'conditional clause', 'future reference', 'adverb comparison'],
                'hints' => [
                    'a1' => 'Use more + adverb to compare the intensity of a potential change.',
                ],
                'explanations' => [
                    'a1' => [
                        'more sharply' => 'Comparative adverb indicates a stronger rise compared with predictions.',
                        'most sharply' => 'Superlative would compare several scenarios, not two.',
                        'sharper' => 'This adjective form does not modify the verb directly.',
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
                    'a1' => 'comparative adverb for manner',
                ],
                'level' => 'C1',
                'source' => 'C1',
                'tags' => ['comparative', 'past perfect context', 'adverb comparison'],
                'hints' => [
                    'a1' => 'Long adverbs describing manner use more + adverb for comparison.',
                ],
                'explanations' => [
                    'a1' => [
                        'more persuasively' => 'Comparative with more + adverb shows a stronger manner than expected.',
                        'most persuasively' => 'Superlative would rank her against many speakers.',
                        'persuasiveer' => 'This non-standard form does not create a correct adverb.',
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
                    'a1' => 'irregular superlative of good',
                ],
                'level' => 'C1',
                'source' => 'C1',
                'tags' => ['superlative', 'affirmative sentence', 'irregular adjective'],
                'hints' => [
                    'a1' => 'Use the irregular superlative form of "good" for the top candidate.',
                ],
                'explanations' => [
                    'a1' => [
                        'best' => 'Irregular superlative shows the highest degree without "most".',
                        'better' => 'This is the comparative form and compares only two.',
                        'more good' => 'This phrase is ungrammatical; use the set irregular forms.',
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
                    'a1' => 'irregular comparative of good/well',
                ],
                'level' => 'C1',
                'source' => 'C1',
                'tags' => ['comparative', 'conditional clause', 'past perfect context', 'irregular adjective'],
                'hints' => [
                    'a1' => 'Use the irregular comparative form to show an unreal improved state.',
                ],
                'explanations' => [
                    'a1' => [
                        'better' => 'Irregular comparative expresses a higher quality than the current state.',
                        'best' => 'Superlative would compare the team with several others.',
                        'more well' => 'Standard English prefers the irregular comparative form.',
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
                    'a1' => 'comparative with more + long adjective',
                ],
                'level' => 'C1',
                'source' => 'C1',
                'tags' => ['comparative', 'negative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'For long adjectives in a negative comparison, still use more + adjective.',
                ],
                'explanations' => [
                    'a1' => [
                        'more authoritative' => 'Comparative with more + adjective fits even with negative markers.',
                        'most authoritative' => 'Superlative would claim the top position, which the sentence denies.',
                        'authoritativeer' => 'This form is incorrect for long adjectives.',
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
                    'a1' => 'comparative for reduced degree',
                ],
                'level' => 'C1',
                'source' => 'C1',
                'tags' => ['comparative', 'affirmative sentence', 'past simple'],
                'hints' => [
                    'a1' => 'To show a gradual decrease, use the comparative opposite of "more".',
                ],
                'explanations' => [
                    'a1' => [
                        'less' => 'Comparative of degree indicates a smaller amount without adding than explicitly.',
                        'lesser' => 'Formal attributive form, not natural before an adjective alone.',
                        'least' => 'Superlative would imply comparison among many stages.',
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
                    'a1' => 'superlative with most + long adjective',
                ],
                'level' => 'C1',
                'source' => 'C1',
                'tags' => ['superlative', 'affirmative sentence', 'present simple'],
                'hints' => [
                    'a1' => 'To show the strongest option out of several, use "the most" with the adjective.',
                ],
                'explanations' => [
                    'a1' => [
                        'most resilient' => 'Superlative of a long adjective forms with the most + adjective.',
                        'more resilient' => 'Comparative only contrasts two solutions.',
                        'resiliencest' => 'This ending is not standard for long adjectives.',
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
                    'a1' => 'comparative with more + long adjective',
                ],
                'level' => 'C1',
                'source' => 'C1',
                'tags' => ['comparative', 'conditional clause', 'present simple'],
                'hints' => [
                    'a1' => 'Use more + adjective in unreal conditions to show a higher level imagined.',
                ],
                'explanations' => [
                    'a1' => [
                        'more reliable' => 'Comparative with more + adjective expresses an unreal higher quality.',
                        'most reliable' => 'Superlative would compare many data sets.',
                        'reliabler' => 'This -er form is non-standard for long adjectives.',
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
                    'a1' => 'comparative adverb with more',
                ],
                'level' => 'C1',
                'source' => 'C1',
                'tags' => ['comparative', 'adverb comparison', 'present perfect context'],
                'hints' => [
                    'a1' => 'Long adverbs in comparisons use more + adverb to show improved style.',
                ],
                'explanations' => [
                    'a1' => [
                        'more concisely' => 'Comparative adverb with more shows a tighter writing style than before.',
                        'most concisely' => 'Superlative would compare several versions of the text.',
                        'conciselier' => 'This constructed form is not standard English.',
                    ],
                ],
            ],
        ];
    }
}
