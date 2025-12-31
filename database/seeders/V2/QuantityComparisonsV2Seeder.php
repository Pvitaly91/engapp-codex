<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class QuantityComparisonsV2Seeder extends QuestionSeeder
{
    private array $levelDifficulty = [
        'A1' => 1,
        'A2' => 2,
        'B1' => 3,
        'B2' => 4,
        'C1' => 5,
        'C2' => 5,
    ];

    private array $hintTemplates = [
        'many_countable' => 'Використай форму для злічуваних у множині (many + plural noun) у питаннях або запереченнях. Приклад шаблону: *many + plural noun*.',
        'much_uncountable' => 'Для незлічуваних іменників у питаннях чи запереченнях підходить much. Приклад: *much + uncountable noun*.',
        'few_countable' => 'Мала кількість злічуваних у множині виражається через few. Формула: *few + plural noun*.',
        'little_uncountable' => 'Малу кількість незлічуваних передає little. Формула: *little + uncountable noun*.',
        'fewer_comparative' => 'Порівняння кількості злічуваних (менше, ніж) утворюй через fewer + plural noun.',
        'less_comparative' => 'Порівняння кількості незлічуваних (менше, ніж) формуй через less + uncountable noun.',
        'a_lot_of' => 'A lot of підходить для великої кількості як зі злічуваними, так і з незлічуваними. Шаблон: *a lot of + noun*.',
        'so_many' => 'So many підкреслює велику кількість злічуваних. Шаблон: *so many + plural noun*.',
        'so_much' => 'So much підкреслює велику кількість незлічуваних. Шаблон: *so much + uncountable noun*.',
        'too_many' => 'Too many позначає надмірну кількість злічуваних у множині.',
        'too_much' => 'Too much позначає надмірну кількість незлічуваних іменників.',
    ];

    private array $explanationTemplates = [
        'many_countable' => [
            'correct' => "Ця форма показує кількість злічуваних предметів у множині в питаннях чи запереченнях. Приклади: \"How many tickets do we need?\"; \"There aren't many chairs left.\"",
            'uncountable' => "%option% уживають із незлічуваними іменниками. Приклади: \"How much sugar do you want?\"; \"There isn't much rain today.\"",
            'small_quantity' => "%option% підкреслює малу кількість, а тут потрібне нейтральне запитання/ствердження про кількість. Приклади: \"I have few coins.\"; \"Only a few students arrived early.\"",
            'comparative_less' => "%option% вживається для незлічуваних у порівнянні, а тут множина злічуваних. Приклади: \"less water\"; \"less time\".",
            'intensity_so_many' => "%option% підкреслює надмірну кількість і не виражає порівняння зі «менше». Приклади: \"so many choices\"; \"so many cars\".",
        ],
        'much_uncountable' => [
            'correct' => "Форма підходить для незлічуваних іменників у запитаннях і запереченнях. Приклади: \"How much milk is left?\"; \"We don't have much rice.\"",
            'countable' => "%option% вимагає злічуваних у множині, але іменник тут незлічуваний. Приклади: \"many books\"; \"many apples\".",
            'small_quantity' => "%option% означає невелику кількість, а речення говорить про загальну кількість. Приклади: \"little time\"; \"a little help\".",
            'comparative_fewer' => "%option% використовується з порівнянням для злічуваних, що не підходить до незлічуваного іменника. Приклади: \"fewer cars\"; \"fewer mistakes\".",
        ],
        'few_countable' => [
            'correct' => "Це форма для малої кількості злічуваних у множині. Приклади: \"Few people know the shortcut.\"; \"There are few buses at night.\"",
            'uncountable' => "%option% підходить до незлічуваних, а іменник тут злічуваний. Приклади: \"little water\"; \"little light\".",
            'neutral_many' => "%option% описує велику кількість, а контекст говорить про малу. Приклади: \"many options\"; \"many friends\".",
            'comparative_less' => "%option% працює з незлічуваними в порівнянні, тоді як іменник злічуваний. Приклади: \"less noise\"; \"less sugar\".",
        ],
        'little_uncountable' => [
            'correct' => "Це позначає малу кількість незлічуваних. Приклади: \"There is little hope left.\"; \"She added little salt.\"",
            'countable' => "%option% використовується для злічуваних у множині. Приклади: \"few chairs\"; \"few ideas\".",
            'neutral_much' => "%option% означає значну кількість незлічуваних, а потрібно підкреслити малу. Приклади: \"much interest\"; \"much information\".",
            'comparative_fewer' => "%option% вживають із порівняннями для злічуваних, що не пасує до незлічуваного іменника. Приклади: \"fewer calls\"; \"fewer errors\".",
        ],
        'fewer_comparative' => [
            'correct' => "Форма fewer + plural noun показує меншу кількість злічуваних у порівнянні. Приклади: \"fewer cars than before\"; \"fewer emails this week\".",
            'uncountable' => "%option% підходить до незлічуваних у порівнянні. Приклади: \"less noise\"; \"less traffic\".",
            'neutral_many' => "%option% говорить про велику кількість і не передає ідею «менше». Приклади: \"many meetings\"; \"many visitors\".",
            'intensity_so_many' => "%option% підкреслює надмірну кількість, а контекст вимагає меншу. Приклади: \"so many tasks\"; \"so many tickets\".",
            'comparative_fewer' => "%option% вказує на меншу кількість, але не узгоджується з формою чи іменником у реченні.",
        ],
        'less_comparative' => [
            'correct' => "Less + uncountable noun означає меншу кількість незлічуваного. Приклади: \"less water than before\"; \"less time to finish\".",
            'countable' => "%option% потрібне для злічуваних у множині, але іменник тут незлічуваний. Приклади: \"fewer chairs\"; \"fewer emails\".",
            'neutral_much' => "%option% не передає ідеї «менше», лише велику кількість. Приклади: \"much stress\"; \"much excitement\".",
            'intensity_so_much' => "%option% означає сильне підкреслення кількості, а не порівняння «менше». Приклади: \"so much rain\"; \"so much work\".",
        ],
        'a_lot_of' => [
            'correct' => "Цей вираз універсальний: a lot of + noun працює із злічуваними та незлічуваними для великої кількості. Приклади: \"a lot of friends\"; \"a lot of water\".",
            'many_only' => "%option% обмежено злічуваними у множині й може звучати надто формально в ствердженні. Приклади: \"many tickets\"; \"many chairs\".",
            'much_only' => "%option% вживається з незлічуваними в питаннях/запереченнях, але тут звичайне ствердження. Приклади: \"much patience\"; \"much advice\".",
        ],
        'so_many' => [
            'correct' => "So many + plural noun підкреслює дуже велику кількість злічуваних. Приклади: \"so many emails arrived\"; \"so many choices to make\".",
            'uncountable' => "%option% стосується незлічуваних, тому не поєднується з множиною. Приклади: \"so much water\"; \"so much light\".",
            'comparative_fewer' => "%option% передає меншу кількість, а тут треба наголос на великій. Приклади: \"fewer accidents\"; \"fewer visitors\".",
        ],
        'so_much' => [
            'correct' => "So much + uncountable noun підкреслює дуже велику кількість незлічуваного. Приклади: \"so much noise outside\"; \"so much excitement in the air\".",
            'countable' => "%option% вимагає злічуваних у множині. Приклади: \"so many tickets\"; \"so many ideas\".",
            'comparative_less' => "%option% позначає меншу кількість, а не підкреслює велику. Приклади: \"less stress\"; \"less sugar\".",
        ],
        'too_many' => [
            'correct' => "Too many + plural noun означає надмірну кількість злічуваних. Приклади: \"too many files to read\"; \"too many people in the room\".",
            'uncountable' => "%option% уживають із незлічуваними, а іменник тут злічуваний. Приклади: \"too much traffic\"; \"too much smoke\".",
            'neutral_many' => "%option% виражає велику, але не надмірну кількість. Приклади: \"so many chances\"; \"many pages\".",
        ],
        'too_much' => [
            'correct' => "Too much + uncountable noun показує надмірність. Приклади: \"too much stress\"; \"too much information\".",
            'countable' => "%option% стосується множини злічуваних і не працює з незлічуваними. Приклади: \"too many calls\"; \"too many tasks\".",
            'neutral_much' => "%option% лише вказує на кількість, але не на надмірність. Приклади: \"so much work\"; \"much interest\".",
        ],
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Quantifiers'])->id;
        $sourceId = Source::firstOrCreate(
            ['name' => 'AI generated: Quantity Comparisons (much/many/less/fewer) (SET 1)']
        )->id;

        $baseTagIds = [
            Tag::firstOrCreate(
                ['name' => 'Quantity Comparisons (much/many/less/fewer)'],
                ['category' => 'English Grammar Theme']
            )->id,
            Tag::firstOrCreate(
                ['name' => 'Countable vs Uncountable Quantifiers'],
                ['category' => 'English Grammar Structure']
            )->id,
            Tag::firstOrCreate(
                ['name' => 'Quantifier Gap Fill'],
                ['category' => 'English Grammar Detail']
            )->id,
        ];

        $levelTags = [];
        foreach (array_keys($this->levelDifficulty) as $level) {
            $levelTags[$level] = Tag::firstOrCreate(
                ['name' => 'Level '.$level],
                ['category' => 'CEFR Level']
            )->id;
        }

        $items = [];
        $meta = [];

        foreach ($this->questionEntries() as $index => $entry) {
            $answersMap = [];
            $verbHints = [];
            $options = [];
            $optionMarkerMap = [];
            $hints = [];
            $explanations = [];

            foreach ($entry['markers'] as $marker => $markerData) {
                $answersMap[$marker] = $markerData['answer'];
                $verbHints[$marker] = $markerData['verb_hint'] ?? null;

                $options[$marker] = [];
                foreach ($markerData['options'] as $option) {
                    $value = $option['value'];
                    $reason = $option['reason'] ?? 'correct';

                    $options[$marker][] = $value;
                    $optionMarkerMap[$value] = $marker;
                    $explanations[$marker][$value] = $this->buildExplanation(
                        $markerData['type'],
                        $reason,
                        $value
                    );
                }

                $hints[$marker] = $this->buildHint($markerData['type']);
            }

            $flatOptions = $this->flattenOptions($options);

            $answers = [];
            foreach ($answersMap as $marker => $answer) {
                $answers[] = [
                    'marker' => $marker,
                    'answer' => $answer,
                    'verb_hint' => $this->normalizeHint($verbHints[$marker] ?? null),
                ];
            }

            $tagIds = array_values(array_unique(array_merge(
                $baseTagIds,
                [$levelTags[$entry['level']]],
                $this->resolveTagIds($entry['tag_names'] ?? [])
            )));

            $uuid = $this->generateQuestionUuid($index + 1, $entry['question']);

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
                'options' => $flatOptions,
                'variants' => [$entry['question']],
            ];

            $meta[] = [
                'uuid' => $uuid,
                'answers' => $answersMap,
                'option_markers' => $optionMarkerMap,
                'hints' => $hints,
                'explanations' => $explanations,
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

    private function buildHint(string $type): string
    {
        return $this->hintTemplates[$type] ?? '';
    }

    private function buildExplanation(string $type, string $reason, string $option): string
    {
        $template = $this->explanationTemplates[$type][$reason] ?? $this->explanationTemplates[$type]['correct'] ?? '';

        return str_replace('%option%', $option, $template);
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

    private function resolveTagIds(array $tagNames): array
    {
        $ids = [];

        foreach ($tagNames as $name) {
            if (! is_string($name) || trim($name) === '') {
                continue;
            }

            $ids[] = Tag::firstOrCreate(
                ['name' => trim($name)],
                ['category' => 'Generated Metadata']
            )->id;
        }

        return $ids;
    }

    private function questionEntries(): array
    {
        return [
            // A1
            [
                'level' => 'A1',
                'question' => 'How {a1} apples are on the table?',
                'tag_names' => ['interrogative', 'present simple', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'many_countable',
                        'answer' => 'many',
                        'verb_hint' => 'count',
                        'options' => [
                            ['value' => 'many', 'reason' => 'correct'],
                            ['value' => 'much', 'reason' => 'uncountable'],
                            ['value' => 'few', 'reason' => 'small_quantity'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A1',
                'question' => "There isn't {a1} water in the bottle.",
                'tag_names' => ['negative', 'present simple', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'much_uncountable',
                        'answer' => 'much',
                        'verb_hint' => 'use with water',
                        'options' => [
                            ['value' => 'much', 'reason' => 'correct'],
                            ['value' => 'many', 'reason' => 'countable'],
                            ['value' => 'few', 'reason' => 'small_quantity'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'We have {a1} oranges left; let’s buy more.',
                'tag_names' => ['affirmative', 'present simple', 'small quantity'],
                'markers' => [
                    'a1' => [
                        'type' => 'few_countable',
                        'answer' => 'few',
                        'verb_hint' => 'small number',
                        'options' => [
                            ['value' => 'few', 'reason' => 'correct'],
                            ['value' => 'many', 'reason' => 'neutral_many'],
                            ['value' => 'much', 'reason' => 'uncountable'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'She drinks {a1} tea in the morning.',
                'tag_names' => ['affirmative', 'habit', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'much_uncountable',
                        'answer' => 'much',
                        'verb_hint' => 'beverage quantity',
                        'options' => [
                            ['value' => 'much', 'reason' => 'correct'],
                            ['value' => 'many', 'reason' => 'countable'],
                            ['value' => 'few', 'reason' => 'small_quantity'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'My dad keeps {a1} books in his office.',
                'tag_names' => ['affirmative', 'present simple', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'many_countable',
                        'answer' => 'many',
                        'verb_hint' => 'books count',
                        'options' => [
                            ['value' => 'many', 'reason' => 'correct'],
                            ['value' => 'much', 'reason' => 'uncountable'],
                            ['value' => 'little', 'reason' => 'small_quantity'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A1',
                'question' => "I don't have {a1} coins for the bus.",
                'tag_names' => ['negative', 'present simple', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'many_countable',
                        'answer' => 'many',
                        'verb_hint' => 'coins number',
                        'options' => [
                            ['value' => 'many', 'reason' => 'correct'],
                            ['value' => 'much', 'reason' => 'uncountable'],
                            ['value' => 'little', 'reason' => 'small_quantity'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'There is {a1} sugar on the table.',
                'tag_names' => ['affirmative', 'present simple', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'little_uncountable',
                        'answer' => 'little',
                        'verb_hint' => 'small amount',
                        'options' => [
                            ['value' => 'little', 'reason' => 'correct'],
                            ['value' => 'few', 'reason' => 'countable'],
                            ['value' => 'much', 'reason' => 'neutral_much'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'Are there {a1} chairs in the classroom?',
                'tag_names' => ['interrogative', 'present simple', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'many_countable',
                        'answer' => 'many',
                        'verb_hint' => 'chairs count',
                        'options' => [
                            ['value' => 'many', 'reason' => 'correct'],
                            ['value' => 'much', 'reason' => 'uncountable'],
                            ['value' => 'less', 'reason' => 'comparative_less'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'He eats {a1} rice for lunch every day.',
                'tag_names' => ['habit', 'affirmative', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'much_uncountable',
                        'answer' => 'much',
                        'verb_hint' => 'rice amount',
                        'options' => [
                            ['value' => 'much', 'reason' => 'correct'],
                            ['value' => 'many', 'reason' => 'countable'],
                            ['value' => 'few', 'reason' => 'small_quantity'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A1',
                'question' => 'We need {a1} eggs for this recipe.',
                'tag_names' => ['instruction', 'present simple', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'many_countable',
                        'answer' => 'many',
                        'verb_hint' => 'eggs count',
                        'options' => [
                            ['value' => 'many', 'reason' => 'correct'],
                            ['value' => 'much', 'reason' => 'uncountable'],
                            ['value' => 'little', 'reason' => 'small_quantity'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A1',
                'question' => "I put {a1} salt in the soup, so it's mild.",
                'tag_names' => ['past simple', 'affirmative', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'little_uncountable',
                        'answer' => 'little',
                        'verb_hint' => 'seasoning amount',
                        'options' => [
                            ['value' => 'little', 'reason' => 'correct'],
                            ['value' => 'few', 'reason' => 'countable'],
                            ['value' => 'much', 'reason' => 'neutral_much'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A1',
                'question' => "There aren't {a1} buses after midnight.",
                'tag_names' => ['negative', 'present simple', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'many_countable',
                        'answer' => 'many',
                        'verb_hint' => 'bus count',
                        'options' => [
                            ['value' => 'many', 'reason' => 'correct'],
                            ['value' => 'much', 'reason' => 'uncountable'],
                            ['value' => 'less', 'reason' => 'comparative_less'],
                        ],
                    ],
                ],
            ],

            // A2
            [
                'level' => 'A2',
                'question' => 'Last winter, there was {a1} snow in our town.',
                'tag_names' => ['past simple', 'affirmative', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'much_uncountable',
                        'answer' => 'much',
                        'verb_hint' => 'weather amount',
                        'options' => [
                            ['value' => 'much', 'reason' => 'correct'],
                            ['value' => 'many', 'reason' => 'countable'],
                            ['value' => 'few', 'reason' => 'small_quantity'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'How {a1} students joined the club last week?',
                'tag_names' => ['interrogative', 'past simple', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'many_countable',
                        'answer' => 'many',
                        'verb_hint' => 'students count',
                        'options' => [
                            ['value' => 'many', 'reason' => 'correct'],
                            ['value' => 'much', 'reason' => 'uncountable'],
                            ['value' => 'fewer', 'reason' => 'intensity_so_many'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'question' => "She didn't buy {a1} apples yesterday.",
                'tag_names' => ['negative', 'past simple', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'many_countable',
                        'answer' => 'many',
                        'verb_hint' => 'apples count',
                        'options' => [
                            ['value' => 'many', 'reason' => 'correct'],
                            ['value' => 'much', 'reason' => 'uncountable'],
                            ['value' => 'little', 'reason' => 'small_quantity'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'They drank {a1} juice, so we need to buy more.',
                'tag_names' => ['past simple', 'affirmative', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'a_lot_of',
                        'answer' => 'a lot of',
                        'verb_hint' => 'large amount',
                        'options' => [
                            ['value' => 'a lot of', 'reason' => 'correct'],
                            ['value' => 'many', 'reason' => 'many_only'],
                            ['value' => 'much', 'reason' => 'much_only'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'Do we have {a1} bread for breakfast tomorrow?',
                'tag_names' => ['interrogative', 'future reference', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'much_uncountable',
                        'answer' => 'much',
                        'verb_hint' => 'bread amount',
                        'options' => [
                            ['value' => 'much', 'reason' => 'correct'],
                            ['value' => 'many', 'reason' => 'countable'],
                            ['value' => 'few', 'reason' => 'small_quantity'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'There were {a1} mistakes in his essay.',
                'tag_names' => ['past simple', 'affirmative', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'many_countable',
                        'answer' => 'many',
                        'verb_hint' => 'mistakes number',
                        'options' => [
                            ['value' => 'many', 'reason' => 'correct'],
                            ['value' => 'much', 'reason' => 'uncountable'],
                            ['value' => 'few', 'reason' => 'small_quantity'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'I saw {a1} people at the concert compared to last year.',
                'tag_names' => ['past simple', 'comparative', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'fewer_comparative',
                        'answer' => 'fewer',
                        'verb_hint' => 'compare numbers',
                        'options' => [
                            ['value' => 'fewer', 'reason' => 'correct'],
                            ['value' => 'less', 'reason' => 'uncountable'],
                            ['value' => 'many', 'reason' => 'neutral_many'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'She spent {a1} money on souvenirs than I expected.',
                'tag_names' => ['past simple', 'comparative', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'less_comparative',
                        'answer' => 'less',
                        'verb_hint' => 'compare amount',
                        'options' => [
                            ['value' => 'less', 'reason' => 'correct'],
                            ['value' => 'fewer', 'reason' => 'countable'],
                            ['value' => 'much', 'reason' => 'neutral_much'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'We planted {a1} trees in the park this spring.',
                'tag_names' => ['past simple', 'affirmative', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'many_countable',
                        'answer' => 'many',
                        'verb_hint' => 'tree count',
                        'options' => [
                            ['value' => 'many', 'reason' => 'correct'],
                            ['value' => 'much', 'reason' => 'uncountable'],
                            ['value' => 'little', 'reason' => 'small_quantity'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'He asked {a1} questions during the lesson.',
                'tag_names' => ['past simple', 'affirmative', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'many_countable',
                        'answer' => 'many',
                        'verb_hint' => 'question count',
                        'options' => [
                            ['value' => 'many', 'reason' => 'correct'],
                            ['value' => 'much', 'reason' => 'uncountable'],
                            ['value' => 'less', 'reason' => 'comparative_less'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'question' => "There is {a1} milk left; let's buy a new bottle.",
                'tag_names' => ['affirmative', 'present simple', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'little_uncountable',
                        'answer' => 'little',
                        'verb_hint' => 'milk amount',
                        'options' => [
                            ['value' => 'little', 'reason' => 'correct'],
                            ['value' => 'few', 'reason' => 'countable'],
                            ['value' => 'much', 'reason' => 'neutral_much'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'A2',
                'question' => 'How {a1} time do we have before the train leaves?',
                'tag_names' => ['interrogative', 'future reference', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'much_uncountable',
                        'answer' => 'much',
                        'verb_hint' => 'time amount',
                        'options' => [
                            ['value' => 'much', 'reason' => 'correct'],
                            ['value' => 'many', 'reason' => 'countable'],
                            ['value' => 'less', 'reason' => 'comparative_less'],
                        ],
                    ],
                ],
            ],

            // B1
            [
                'level' => 'B1',
                'question' => 'She made far {a1} noise than her brother during rehearsals.',
                'tag_names' => ['comparative', 'past simple', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'less_comparative',
                        'answer' => 'less',
                        'verb_hint' => 'volume comparison',
                        'options' => [
                            ['value' => 'less', 'reason' => 'correct'],
                            ['value' => 'fewer', 'reason' => 'countable'],
                            ['value' => 'much', 'reason' => 'neutral_much'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'We have {a1} opportunities to practice now that the lab is open.',
                'tag_names' => ['affirmative', 'present simple', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'many_countable',
                        'answer' => 'many',
                        'verb_hint' => 'opportunity count',
                        'options' => [
                            ['value' => 'many', 'reason' => 'correct'],
                            ['value' => 'much', 'reason' => 'uncountable'],
                            ['value' => 'few', 'reason' => 'small_quantity'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'How {a1} packets of pasta did you buy while traveling last month?',
                'tag_names' => ['interrogative', 'past simple', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'many_countable',
                        'answer' => 'many',
                        'verb_hint' => 'packet count',
                        'options' => [
                            ['value' => 'many', 'reason' => 'correct'],
                            ['value' => 'much', 'reason' => 'uncountable'],
                            ['value' => 'less', 'reason' => 'comparative_less'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'question' => "There wasn't {a1} traffic on the holiday weekend.",
                'tag_names' => ['negative', 'past simple', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'much_uncountable',
                        'answer' => 'much',
                        'verb_hint' => 'traffic amount',
                        'options' => [
                            ['value' => 'much', 'reason' => 'correct'],
                            ['value' => 'many', 'reason' => 'countable'],
                            ['value' => 'few', 'reason' => 'small_quantity'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'By the end of the month, they will have {a1} meetings than they planned.',
                'tag_names' => ['future reference', 'comparative', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'fewer_comparative',
                        'answer' => 'fewer',
                        'verb_hint' => 'meeting count',
                        'options' => [
                            ['value' => 'fewer', 'reason' => 'correct'],
                            ['value' => 'less', 'reason' => 'uncountable'],
                            ['value' => 'many', 'reason' => 'neutral_many'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'I used {a1} flour, so the cake stayed light.',
                'tag_names' => ['past simple', 'comparison to usual', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'less_comparative',
                        'answer' => 'less',
                        'verb_hint' => 'amount reduction',
                        'options' => [
                            ['value' => 'less', 'reason' => 'correct'],
                            ['value' => 'fewer', 'reason' => 'countable'],
                            ['value' => 'much', 'reason' => 'neutral_much'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'question' => "He doesn't have {a1} patience when things go wrong.",
                'tag_names' => ['negative', 'present simple', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'much_uncountable',
                        'answer' => 'much',
                        'verb_hint' => 'patience amount',
                        'options' => [
                            ['value' => 'much', 'reason' => 'correct'],
                            ['value' => 'many', 'reason' => 'countable'],
                            ['value' => 'little', 'reason' => 'small_quantity'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'She collected {a1} stamps when she was a teenager.',
                'tag_names' => ['past simple', 'affirmative', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'many_countable',
                        'answer' => 'many',
                        'verb_hint' => 'stamp count',
                        'options' => [
                            ['value' => 'many', 'reason' => 'correct'],
                            ['value' => 'much', 'reason' => 'uncountable'],
                            ['value' => 'fewer', 'reason' => 'intensity_so_many'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'There are {a1} seats available for the late show; hurry!',
                'tag_names' => ['present simple', 'limited quantity', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'few_countable',
                        'answer' => 'few',
                        'verb_hint' => 'small number',
                        'options' => [
                            ['value' => 'few', 'reason' => 'correct'],
                            ['value' => 'much', 'reason' => 'uncountable'],
                            ['value' => 'many', 'reason' => 'neutral_many'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'We spent {a1} hours discussing the budget last night.',
                'tag_names' => ['past simple', 'affirmative', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'many_countable',
                        'answer' => 'many',
                        'verb_hint' => 'hour count',
                        'options' => [
                            ['value' => 'many', 'reason' => 'correct'],
                            ['value' => 'much', 'reason' => 'uncountable'],
                            ['value' => 'less', 'reason' => 'comparative_less'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'He has {a1} friends in the new city than in his hometown.',
                'tag_names' => ['comparative', 'present simple', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'fewer_comparative',
                        'answer' => 'fewer',
                        'verb_hint' => 'friend count',
                        'options' => [
                            ['value' => 'fewer', 'reason' => 'correct'],
                            ['value' => 'less', 'reason' => 'uncountable'],
                            ['value' => 'many', 'reason' => 'neutral_many'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B1',
                'question' => 'Do you think there is {a1} homework this week compared to last week?',
                'tag_names' => ['interrogative', 'comparative', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'less_comparative',
                        'answer' => 'less',
                        'verb_hint' => 'compare workload',
                        'options' => [
                            ['value' => 'less', 'reason' => 'correct'],
                            ['value' => 'fewer', 'reason' => 'countable'],
                            ['value' => 'much', 'reason' => 'neutral_much'],
                        ],
                    ],
                ],
            ],

            // B2
            [
                'level' => 'B2',
                'question' => 'If we had allocated {a1} resources, the project would have failed.',
                'tag_names' => ['hypothetical past', 'comparative', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'fewer_comparative',
                        'answer' => 'fewer',
                        'verb_hint' => 'resource count',
                        'options' => [
                            ['value' => 'fewer', 'reason' => 'correct'],
                            ['value' => 'less', 'reason' => 'uncountable'],
                            ['value' => 'many', 'reason' => 'neutral_many'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'She spends far {a1} energy on social media now that she is studying.',
                'tag_names' => ['present simple', 'comparative', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'less_comparative',
                        'answer' => 'less',
                        'verb_hint' => 'energy amount',
                        'options' => [
                            ['value' => 'less', 'reason' => 'correct'],
                            ['value' => 'fewer', 'reason' => 'countable'],
                            ['value' => 'much', 'reason' => 'neutral_much'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'There were {a1} volunteers than expected, so the event finished early.',
                'tag_names' => ['past simple', 'comparative', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'fewer_comparative',
                        'answer' => 'fewer',
                        'verb_hint' => 'volunteer count',
                        'options' => [
                            ['value' => 'fewer', 'reason' => 'correct'],
                            ['value' => 'less', 'reason' => 'uncountable'],
                            ['value' => 'many', 'reason' => 'neutral_many'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'How {a1} complications arose during the rollout?',
                'tag_names' => ['interrogative', 'past simple', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'many_countable',
                        'answer' => 'many',
                        'verb_hint' => 'complication count',
                        'options' => [
                            ['value' => 'many', 'reason' => 'correct'],
                            ['value' => 'much', 'reason' => 'uncountable'],
                            ['value' => 'less', 'reason' => 'comparative_less'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'The revised plan left us with {a1} time to rehearse than before.',
                'tag_names' => ['comparative', 'time reference', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'less_comparative',
                        'answer' => 'less',
                        'verb_hint' => 'time comparison',
                        'options' => [
                            ['value' => 'less', 'reason' => 'correct'],
                            ['value' => 'fewer', 'reason' => 'countable'],
                            ['value' => 'much', 'reason' => 'neutral_much'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'He invested {a1} effort into the draft that the reviewers noticed.',
                'tag_names' => ['emphasis', 'past simple', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'so_much',
                        'answer' => 'so much',
                        'verb_hint' => 'intensity marker',
                        'options' => [
                            ['value' => 'so much', 'reason' => 'correct'],
                            ['value' => 'too many', 'reason' => 'countable'],
                            ['value' => 'fewer', 'reason' => 'comparative_fewer'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'There were {a1} emails to answer after the outage; we needed help.',
                'tag_names' => ['emphasis', 'past simple', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'so_many',
                        'answer' => 'so many',
                        'verb_hint' => 'email count',
                        'options' => [
                            ['value' => 'so many', 'reason' => 'correct'],
                            ['value' => 'too much', 'reason' => 'uncountable'],
                            ['value' => 'less', 'reason' => 'comparative_less'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'We had {a1} attendees last quarter, so we booked a smaller hall.',
                'tag_names' => ['past simple', 'comparative', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'fewer_comparative',
                        'answer' => 'fewer',
                        'verb_hint' => 'attendee count',
                        'options' => [
                            ['value' => 'fewer', 'reason' => 'correct'],
                            ['value' => 'less', 'reason' => 'uncountable'],
                            ['value' => 'many', 'reason' => 'neutral_many'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'She showed {a1} hesitation before accepting the offer.',
                'tag_names' => ['affirmative', 'past simple', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'little_uncountable',
                        'answer' => 'little',
                        'verb_hint' => 'small amount',
                        'options' => [
                            ['value' => 'little', 'reason' => 'correct'],
                            ['value' => 'many', 'reason' => 'countable'],
                            ['value' => 'much', 'reason' => 'neutral_much'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'The new policy caused {a1} confusion than the previous one.',
                'tag_names' => ['comparative', 'past simple', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'less_comparative',
                        'answer' => 'less',
                        'verb_hint' => 'compare confusion',
                        'options' => [
                            ['value' => 'less', 'reason' => 'correct'],
                            ['value' => 'fewer', 'reason' => 'countable'],
                            ['value' => 'much', 'reason' => 'neutral_much'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'By Friday, there will be {a1} updates left to release.',
                'tag_names' => ['future reference', 'limited quantity', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'few_countable',
                        'answer' => 'few',
                        'verb_hint' => 'small number',
                        'options' => [
                            ['value' => 'few', 'reason' => 'correct'],
                            ['value' => 'many', 'reason' => 'neutral_many'],
                            ['value' => 'much', 'reason' => 'uncountable'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'B2',
                'question' => 'Researchers found {a1} evidence to support the claim.',
                'tag_names' => ['past simple', 'affirmative', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'little_uncountable',
                        'answer' => 'little',
                        'verb_hint' => 'evidence amount',
                        'options' => [
                            ['value' => 'little', 'reason' => 'correct'],
                            ['value' => 'many', 'reason' => 'countable'],
                            ['value' => 'much', 'reason' => 'neutral_much'],
                        ],
                    ],
                ],
            ],

            // C1
            [
                'level' => 'C1',
                'question' => 'The committee devoted {a1} time to topics that mattered less.',
                'tag_names' => ['affirmative', 'past simple', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'too_much',
                        'answer' => 'too much',
                        'verb_hint' => 'excess marker',
                        'options' => [
                            ['value' => 'too much', 'reason' => 'correct'],
                            ['value' => 'too many', 'reason' => 'countable'],
                            ['value' => 'less', 'reason' => 'comparative_less'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'She faced {a1} obstacles in her early career than she admitted.',
                'tag_names' => ['comparative', 'past simple', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'fewer_comparative',
                        'answer' => 'fewer',
                        'verb_hint' => 'obstacle count',
                        'options' => [
                            ['value' => 'fewer', 'reason' => 'correct'],
                            ['value' => 'less', 'reason' => 'uncountable'],
                            ['value' => 'many', 'reason' => 'neutral_many'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'Seldom have I seen {a1} enthusiasm for a routine update.',
                'tag_names' => ['inversion', 'present perfect reference', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'so_much',
                        'answer' => 'so much',
                        'verb_hint' => 'intensity marker',
                        'options' => [
                            ['value' => 'so much', 'reason' => 'correct'],
                            ['value' => 'so many', 'reason' => 'countable'],
                            ['value' => 'fewer', 'reason' => 'comparative_fewer'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'There are far {a1} viable options now that funding was cut.',
                'tag_names' => ['comparative', 'present simple', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'fewer_comparative',
                        'answer' => 'fewer',
                        'verb_hint' => 'option count',
                        'options' => [
                            ['value' => 'fewer', 'reason' => 'correct'],
                            ['value' => 'less', 'reason' => 'uncountable'],
                            ['value' => 'many', 'reason' => 'neutral_many'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'No matter how {a1} data we gather, the model stays unstable.',
                'tag_names' => ['complex clause', 'present simple', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'much_uncountable',
                        'answer' => 'much',
                        'verb_hint' => 'data amount',
                        'options' => [
                            ['value' => 'much', 'reason' => 'correct'],
                            ['value' => 'many', 'reason' => 'countable'],
                            ['value' => 'less', 'reason' => 'comparative_less'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'The proposal attracted {a1} criticism than anticipated.',
                'tag_names' => ['comparative', 'past simple', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'less_comparative',
                        'answer' => 'less',
                        'verb_hint' => 'criticism amount',
                        'options' => [
                            ['value' => 'less', 'reason' => 'correct'],
                            ['value' => 'fewer', 'reason' => 'countable'],
                            ['value' => 'much', 'reason' => 'neutral_much'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'She left with {a1} luggage than she arrived with, thanks to the samples.',
                'tag_names' => ['comparative', 'past simple', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'less_comparative',
                        'answer' => 'less',
                        'verb_hint' => 'luggage amount',
                        'options' => [
                            ['value' => 'less', 'reason' => 'correct'],
                            ['value' => 'fewer', 'reason' => 'countable'],
                            ['value' => 'much', 'reason' => 'neutral_much'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'They produced {a1} prototypes before locking the design.',
                'tag_names' => ['past simple', 'affirmative', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'many_countable',
                        'answer' => 'many',
                        'verb_hint' => 'prototype count',
                        'options' => [
                            ['value' => 'many', 'reason' => 'correct'],
                            ['value' => 'much', 'reason' => 'uncountable'],
                            ['value' => 'fewer', 'reason' => 'intensity_so_many'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'Rarely do {a1} scholars challenge the prevailing theory so openly.',
                'tag_names' => ['inversion', 'present simple', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'many_countable',
                        'answer' => 'many',
                        'verb_hint' => 'scholar count',
                        'options' => [
                            ['value' => 'many', 'reason' => 'correct'],
                            ['value' => 'much', 'reason' => 'uncountable'],
                            ['value' => 'fewer', 'reason' => 'intensity_so_many'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'The audit revealed {a1} discrepancies than the headlines suggested.',
                'tag_names' => ['comparative', 'past simple', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'fewer_comparative',
                        'answer' => 'fewer',
                        'verb_hint' => 'discrepancy count',
                        'options' => [
                            ['value' => 'fewer', 'reason' => 'correct'],
                            ['value' => 'less', 'reason' => 'uncountable'],
                            ['value' => 'many', 'reason' => 'neutral_many'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'We encountered {a1} resistance to the plan during negotiations.',
                'tag_names' => ['past simple', 'affirmative', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'much_uncountable',
                        'answer' => 'much',
                        'verb_hint' => 'resistance amount',
                        'options' => [
                            ['value' => 'much', 'reason' => 'correct'],
                            ['value' => 'many', 'reason' => 'countable'],
                            ['value' => 'less', 'reason' => 'comparative_less'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'C1',
                'question' => 'He contributed {a1} analysis to the report that it doubled in length.',
                'tag_names' => ['emphasis', 'past simple', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'so_much',
                        'answer' => 'so much',
                        'verb_hint' => 'intensity marker',
                        'options' => [
                            ['value' => 'so much', 'reason' => 'correct'],
                            ['value' => 'so many', 'reason' => 'countable'],
                            ['value' => 'less', 'reason' => 'comparative_less'],
                        ],
                    ],
                ],
            ],

            // C2
            [
                'level' => 'C2',
                'question' => 'Had there been {a1} delays, the merger would have collapsed.',
                'tag_names' => ['third conditional mood', 'comparative', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'fewer_comparative',
                        'answer' => 'fewer',
                        'verb_hint' => 'delay count',
                        'options' => [
                            ['value' => 'fewer', 'reason' => 'correct'],
                            ['value' => 'less', 'reason' => 'uncountable'],
                            ['value' => 'many', 'reason' => 'neutral_many'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'C2',
                'question' => 'The editor demanded {a1} jargon than the draft originally contained.',
                'tag_names' => ['comparative', 'past simple', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'less_comparative',
                        'answer' => 'less',
                        'verb_hint' => 'jargon amount',
                        'options' => [
                            ['value' => 'less', 'reason' => 'correct'],
                            ['value' => 'fewer', 'reason' => 'countable'],
                            ['value' => 'much', 'reason' => 'neutral_much'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'C2',
                'question' => 'Scarcely {a1} witnesses remained willing to testify after the leak.',
                'tag_names' => ['inversion', 'past simple', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'few_countable',
                        'answer' => 'few',
                        'verb_hint' => 'small number',
                        'options' => [
                            ['value' => 'few', 'reason' => 'correct'],
                            ['value' => 'many', 'reason' => 'neutral_many'],
                            ['value' => 'much', 'reason' => 'uncountable'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'C2',
                'question' => 'No sooner had we secured funding than {a1} opportunities appeared.',
                'tag_names' => ['inversion', 'past simple', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'many_countable',
                        'answer' => 'many',
                        'verb_hint' => 'opportunity count',
                        'options' => [
                            ['value' => 'many', 'reason' => 'correct'],
                            ['value' => 'much', 'reason' => 'uncountable'],
                            ['value' => 'fewer', 'reason' => 'intensity_so_many'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'C2',
                'question' => 'The dataset contained {a1} anomalies to draw firm conclusions.',
                'tag_names' => ['affirmative', 'past simple', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'too_many',
                        'answer' => 'too many',
                        'verb_hint' => 'excess marker',
                        'options' => [
                            ['value' => 'too many', 'reason' => 'correct'],
                            ['value' => 'too much', 'reason' => 'uncountable'],
                            ['value' => 'fewer', 'reason' => 'comparative_fewer'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'C2',
                'question' => 'Only after the audit did we realize how {a1} cash reserves remained.',
                'tag_names' => ['inversion', 'past simple', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'little_uncountable',
                        'answer' => 'little',
                        'verb_hint' => 'small amount',
                        'options' => [
                            ['value' => 'little', 'reason' => 'correct'],
                            ['value' => 'many', 'reason' => 'countable'],
                            ['value' => 'much', 'reason' => 'neutral_much'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'C2',
                'question' => 'The director insisted on {a1} meetings to keep the team focused.',
                'tag_names' => ['present simple', 'comparative', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'fewer_comparative',
                        'answer' => 'fewer',
                        'verb_hint' => 'meeting count',
                        'options' => [
                            ['value' => 'fewer', 'reason' => 'correct'],
                            ['value' => 'less', 'reason' => 'uncountable'],
                            ['value' => 'many', 'reason' => 'neutral_many'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'C2',
                'question' => 'At no point did they show {a1} remorse for the oversight.',
                'tag_names' => ['inversion', 'past simple', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'much_uncountable',
                        'answer' => 'much',
                        'verb_hint' => 'remorse amount',
                        'options' => [
                            ['value' => 'much', 'reason' => 'correct'],
                            ['value' => 'many', 'reason' => 'countable'],
                            ['value' => 'less', 'reason' => 'comparative_less'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'C2',
                'question' => 'Very {a1} proposals met the standards after the review.',
                'tag_names' => ['affirmative', 'past simple', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'few_countable',
                        'answer' => 'few',
                        'verb_hint' => 'small number',
                        'options' => [
                            ['value' => 'few', 'reason' => 'correct'],
                            ['value' => 'many', 'reason' => 'neutral_many'],
                            ['value' => 'much', 'reason' => 'uncountable'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'C2',
                'question' => 'By the end, we delivered {a1} reports than the board requested.',
                'tag_names' => ['comparative', 'past simple', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'fewer_comparative',
                        'answer' => 'fewer',
                        'verb_hint' => 'report count',
                        'options' => [
                            ['value' => 'fewer', 'reason' => 'correct'],
                            ['value' => 'less', 'reason' => 'uncountable'],
                            ['value' => 'many', 'reason' => 'neutral_many'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'C2',
                'question' => 'Had the team shown {a1} hesitation, the client would have left.',
                'tag_names' => ['hypothetical past', 'comparative', 'uncountable noun'],
                'markers' => [
                    'a1' => [
                        'type' => 'less_comparative',
                        'answer' => 'less',
                        'verb_hint' => 'compare hesitation',
                        'options' => [
                            ['value' => 'less', 'reason' => 'correct'],
                            ['value' => 'fewer', 'reason' => 'countable'],
                            ['value' => 'much', 'reason' => 'neutral_much'],
                        ],
                    ],
                ],
            ],
            [
                'level' => 'C2',
                'question' => 'The appeal relied on {a1} supporting documents, which weakened it.',
                'tag_names' => ['affirmative', 'past simple', 'countable plural'],
                'markers' => [
                    'a1' => [
                        'type' => 'few_countable',
                        'answer' => 'few',
                        'verb_hint' => 'document count',
                        'options' => [
                            ['value' => 'few', 'reason' => 'correct'],
                            ['value' => 'many', 'reason' => 'neutral_many'],
                            ['value' => 'much', 'reason' => 'uncountable'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
