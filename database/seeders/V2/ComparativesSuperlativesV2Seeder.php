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
