<?php

namespace Database\Seeders;

use App\Support\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class PastSimpleVsPresentPerfectMultipleChoiceSeeder extends Seeder
{
    public function run()
    {
        $categoryId = Category::firstOrCreate(['name' => 'past'])->id;
        $sourceId   = Source::firstOrCreate(['name' => 'Past Simple vs Present Perfect — Multiple Choice'])->id;

        $grammarTags = [
            Tag::firstOrCreate(['name' => 'Past Simple'], ['category' => 'Grammar']),
            Tag::firstOrCreate(['name' => 'Present Perfect'], ['category' => 'Grammar']),
        ];

        $vocabularyTags = [
            'Education'     => Tag::firstOrCreate(['name' => 'Education'], ['category' => 'Vocabulary']),
            'Cinema'        => Tag::firstOrCreate(['name' => 'Cinema'], ['category' => 'Vocabulary']),
            'Transport'     => Tag::firstOrCreate(['name' => 'Transport'], ['category' => 'Vocabulary']),
            'Travel'        => Tag::firstOrCreate(['name' => 'Travel'], ['category' => 'Vocabulary']),
            'Leisure'       => Tag::firstOrCreate(['name' => 'Leisure'], ['category' => 'Vocabulary']),
            'Family'        => Tag::firstOrCreate(['name' => 'Family'], ['category' => 'Vocabulary']),
            'Supernatural'  => Tag::firstOrCreate(['name' => 'Supernatural'], ['category' => 'Vocabulary']),
            'Technology'    => Tag::firstOrCreate(['name' => 'Technology'], ['category' => 'Vocabulary']),
            'Entertainment' => Tag::firstOrCreate(['name' => 'Entertainment'], ['category' => 'Vocabulary']),
            'Health'        => Tag::firstOrCreate(['name' => 'Health'], ['category' => 'Vocabulary']),
            'Routine'       => Tag::firstOrCreate(['name' => 'Routine'], ['category' => 'Vocabulary']),
            'Food'          => Tag::firstOrCreate(['name' => 'Food'], ['category' => 'Vocabulary']),
        ];

        $questions = [
            [
                'question' => 'Paula and James {a1} their homework yet.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => "haven't finished", 'verb_hint' => 'not/finish'],
                ],
                'options'  => ["haven't finished", 'finished', "didn't finished"],
                'vocab'    => 'Education',
            ],
            [
                'question' => '{a1} you and Sarah see the movie yesterday?',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'Did', 'verb_hint' => 'do'],
                ],
                'options'  => ['Were', 'Have', 'Did'],
                'vocab'    => 'Cinema',
            ],
            [
                'question' => 'I have never {a1} a bicycle.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'ridden', 'verb_hint' => 'ride'],
                ],
                'options'  => ['ride', 'rode', 'ridden'],
                'vocab'    => 'Transport',
            ],
            [
                'question' => 'Have they ever {a1} in a hot air balloon?',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'flown', 'verb_hint' => 'fly'],
                ],
                'options'  => ['flew', 'flown', 'have flown'],
                'vocab'    => 'Travel',
            ],
            [
                'question' => '(A) Did you have a good time? (B) Yes, we {a1}.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'did', 'verb_hint' => 'do'],
                ],
                'options'  => ['have', 'did', 'had'],
                'vocab'    => 'Leisure',
            ],
            [
                'question' => '{a1} Maria {a2} London and Madrid last year?',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'Did',   'verb_hint' => 'do'],
                    ['marker' => 'a2', 'answer' => 'visit', 'verb_hint' => 'visit'],
                ],
                'options'  => ['Did / visit', 'Has / visited', 'Did / visited'],
                'vocab'    => 'Travel',
            ],
            [
                'question' => '(A) Has the movie already begun? (B) No, it {a1}.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => "hasn't", 'verb_hint' => 'have'],
                ],
                'options'  => ["haven't", "hasn't", "didn't"],
                'vocab'    => 'Cinema',
            ],
            [
                'question' => 'Tom has lived in Montreal {a1} last summer.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'since'],
                ],
                'options'  => ['for', 'was', 'since'],
                'vocab'    => 'Travel',
            ],
            [
                'question' => 'My family and I {a1} at home last Saturday.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => "weren't", 'verb_hint' => 'be'],
                ],
                'options'  => ["weren't", "haven't", "wasn't"],
                'vocab'    => 'Family',
            ],
            [
                'question' => '(A) {a1} you ever seen a ghost? (B) No, I {a2}.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'Have',   'verb_hint' => 'have'],
                    ['marker' => 'a2', 'answer' => "haven't", 'verb_hint' => 'have'],
                ],
                'options'  => ["Did / didn't", "Did / haven't", "Have / haven't"],
                'vocab'    => 'Supernatural',
            ],
            [
                'question' => '(A) {a1} John check his e-mail? (B) Yes, he {a2}.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'Has', 'verb_hint' => 'have'],
                    ['marker' => 'a2', 'answer' => 'has', 'verb_hint' => 'have'],
                ],
                'options'  => ['Did / did', 'Has / has', 'Has / did'],
                'vocab'    => 'Technology',
            ],
            [
                'question' => 'The children watched TV {a1} an hour last night.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'for'],
                ],
                'options'  => ['was', 'for', 'since'],
                'vocab'    => 'Entertainment',
            ],
            [
                'question' => '(A) Have you ever visited Toronto? (B) Yes, I {a1}.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'have', 'verb_hint' => 'have'],
                ],
                'options'  => ['did', 'have', 'visited'],
                'vocab'    => 'Travel',
            ],
            [
                'question' => '(A) {a1} Dr. Smith talked to you? (B) Yes, she {a2}.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'Has', 'verb_hint' => 'have'],
                    ['marker' => 'a2', 'answer' => 'has', 'verb_hint' => 'have'],
                ],
                'options'  => ['Has / has', 'Did / has', 'Has / did'],
                'vocab'    => 'Health',
            ],
            [
                'question' => 'What {a1} you done since ten o\'clock this morning?',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'have', 'verb_hint' => 'have'],
                ],
                'options'  => ['did', 'has', 'have'],
                'vocab'    => 'Routine',
            ],
            [
                'question' => 'How many cookies {a1} you {a2} last night?',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'did', 'verb_hint' => 'do'],
                    ['marker' => 'a2', 'answer' => 'eat', 'verb_hint' => 'eat'],
                ],
                'options'  => ['did / eat', 'have / eat', 'did / ate'],
                'vocab'    => 'Food',
            ],
        ];

        $service = new QuestionSeedingService();
        $items   = [];

        foreach ($questions as $i => $data) {
            $index = $i + 1;
            $slug  = Str::slug(class_basename(self::class));
            $max   = 36 - strlen((string) $index) - 1;
            $uuid  = substr($slug, 0, $max) . '-' . $index;

            $items[] = [
                'uuid'        => $uuid,
                'question'    => $data['question'],
                'difficulty'  => 2,
                'category_id' => $categoryId,
                'flag'        => 0,
                'source_id'   => $sourceId,
                'tag_ids'     => array_merge(
                    array_map(fn($tag) => $tag->id, $grammarTags),
                    [$vocabularyTags[$data['vocab']]->id]
                ),
                'level'       => 'B1',
                'answers'     => $data['answers'],
                'options'     => $data['options'],
            ];
        }

        $service->seed($items);
    }
}

