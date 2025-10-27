<?php

namespace Database\Seeders\V1\Tenses\Past;

use App\Support\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\{Category, Source, Tag};
use Illuminate\Support\Str;

class PastSimpleContinuousSentencesTestSeeder extends Seeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'past'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Past Simple and Continuous Sentences'])->id;
        $themeTag = Tag::firstOrCreate(['name' => 'past_simple_continuous_sentences_test']);

        $questions = [
            [
                'question' => 'My uncle {a1} a lot of money to buy a new sports car two years ago.',
                'answers' => [
                    'a1' => ['answer' => 'saved', 'verb_hint' => 'save'],
                ],
                'options' => [],
            ],
            [
                'question' => 'Bernardo {a1} his dogs when he {a2} your girlfriend with Sergio.',
                'answers' => [
                    'a1' => ['answer' => 'was walking', 'verb_hint' => 'walk'],
                    'a2' => ['answer' => 'saw', 'verb_hint' => 'see'],
                ],
                'options' => [],
            ],
            [
                'question' => 'Margaret {a1} all the letters she {a2} last week.',
                'answers' => [
                    'a1' => ['answer' => 'replied', 'verb_hint' => 'reply'],
                    'a2' => ['answer' => 'received', 'verb_hint' => 'receive'],
                ],
                'options' => [],
            ],
            [
                'question' => 'While Julian {a1} his motorbike, his grandfather {a2} a magazine.',
                'answers' => [
                    'a1' => ['answer' => 'was repairing', 'verb_hint' => 'repair'],
                    'a2' => ['answer' => 'was reading', 'verb_hint' => 'read'],
                ],
                'options' => [],
            ],
            [
                'question' => 'Andrew {a1} some money to his sister and she {a2} him "thank you".',
                'answers' => [
                    'a1' => ['answer' => 'gave', 'verb_hint' => 'give'],
                    'a2' => ['answer' => 'told', 'verb_hint' => 'tell'],
                ],
                'options' => [],
            ],
            [
                'question' => 'When Caroline {a1} off the bus, it {a2} a little.',
                'answers' => [
                    'a1' => ['answer' => 'got', 'verb_hint' => 'get'],
                    'a2' => ['answer' => 'was snowing', 'verb_hint' => 'snow'],
                ],
                'options' => [],
            ],
            [
                'question' => 'I think some prices {a1} a lot during the last two months.',
                'answers' => [
                    'a1' => ['answer' => 'increased', 'verb_hint' => 'increase'],
                ],
                'options' => [],
            ],
            [
                'question' => 'They {a1} on the phone when the boss {a2} at the office.',
                'answers' => [
                    'a1' => ['answer' => 'were talking', 'verb_hint' => 'talk'],
                    'a2' => ['answer' => 'arrived', 'verb_hint' => 'arrive'],
                ],
                'options' => [],
            ],
            [
                'question' => 'My cousin Lilia {a1} me good jokes yesterday evening at the pub.',
                'answers' => [
                    'a1' => ['answer' => 'told', 'verb_hint' => 'tell'],
                ],
                'options' => [],
            ],
            [
                'question' => 'It was very cold so I {a1} and {a2} tired.',
                'answers' => [
                    'a1' => ['answer' => 'shivered', 'verb_hint' => 'shiver'],
                    'a2' => ['answer' => 'felt', 'verb_hint' => 'feel'],
                ],
                'options' => [],
            ],
            [
                'question' => 'Leonard {a1} some research at the laboratory when I called him.',
                'answers' => [
                    'a1' => ['answer' => 'was doing', 'verb_hint' => 'do'],
                ],
                'options' => [],
            ],
            [
                'question' => 'The girl {a1} something in my ear but I couldn\'t understand it.',
                'answers' => [
                    'a1' => ['answer' => 'whispered', 'verb_hint' => 'whisper'],
                ],
                'options' => [],
            ],
            [
                'question' => 'At 5:00 Mrs Simpson {a1} a cup of coffee with some friends.',
                'answers' => [
                    'a1' => ['answer' => 'was having', 'verb_hint' => 'have'],
                ],
                'options' => [],
            ],
            [
                'question' => 'Helen and Hector {a1} a lot of money last Easter holidays.',
                'answers' => [
                    'a1' => ['answer' => 'spent', 'verb_hint' => 'spend'],
                ],
                'options' => [],
            ],
            [
                'question' => 'Alexander {a1} to cry when I {a2} him the bad news.',
                'answers' => [
                    'a1' => ['answer' => 'began', 'verb_hint' => 'begin'],
                    'a2' => ['answer' => 'told', 'verb_hint' => 'tell'],
                ],
                'options' => [],
            ],
            [
                'question' => 'The Williams family {a1} house when my mother had the baby.',
                'answers' => [
                    'a1' => ['answer' => 'was moving', 'verb_hint' => 'move'],
                ],
                'options' => [],
            ],
            [
                'question' => 'Hannah Hutted {a1} a well-known writer five years ago in France.',
                'answers' => [
                    'a1' => ['answer' => 'became', 'verb_hint' => 'become'],
                ],
                'options' => [],
            ],
            [
                'question' => 'The snow {a1} the mountains when I {a2} up.',
                'answers' => [
                    'a1' => ['answer' => 'was covering', 'verb_hint' => 'cover'],
                    'a2' => ['answer' => 'woke', 'verb_hint' => 'wake'],
                ],
                'options' => [],
            ],
            [
                'question' => 'The little girl {a1} down and {a2} loudly.',
                'answers' => [
                    'a1' => ['answer' => 'fell', 'verb_hint' => 'fall'],
                    'a2' => ['answer' => 'cried', 'verb_hint' => 'cry'],
                ],
                'options' => [],
            ],
            [
                'question' => 'We {a1} bingo when the lights {a2} out.',
                'answers' => [
                    'a1' => ['answer' => 'were playing', 'verb_hint' => 'play'],
                    'a2' => ['answer' => 'went', 'verb_hint' => 'go'],
                ],
                'options' => [],
            ],
            [
                'question' => 'Many people {a1} on the ship when it hit some rocks.',
                'answers' => [
                    'a1' => ['answer' => 'were traveling', 'verb_hint' => 'travel'],
                ],
                'options' => [],
            ],
        ];

        $service = new QuestionSeedingService();
        $items = [];
        foreach ($questions as $i => $q) {
            $index = $i + 1;
            $slug = Str::slug(class_basename(self::class));
            $max = 36 - strlen((string) $index) - 1;
            $uuid = substr($slug, 0, $max) . '-' . $index;

            $answers = [];
            foreach ($q['answers'] as $marker => $answerData) {
                $answers[] = [
                    'marker' => $marker,
                    'answer' => $answerData['answer'],
                    'verb_hint' => $answerData['verb_hint'] ?? null,
                ];
            }

            $items[] = [
                'uuid' => $uuid,
                'question' => $q['question'],
                'difficulty' => 2,
                'category_id' => $categoryId,
                'source_id' => $sourceId,
                'flag' => 0,
                'tag_ids' => [$themeTag->id],
                'answers' => $answers,
                'options' => $q['options'],
            ];
        }

        $service->seed($items);
    }
}
