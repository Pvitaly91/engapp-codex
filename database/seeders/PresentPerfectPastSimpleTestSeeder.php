<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class PresentPerfectPastSimpleTestSeeder extends Seeder
{
    public function run()
    {
        $categoryId = Category::firstOrCreate(['name' => 'past'])->id;
        $sourceId   = Source::firstOrCreate(['name' => 'Present Perfect or Past Simple Test'])->id;
        $grammarTag = Tag::firstOrCreate(['name' => 'Present Perfect or Past Simple'], ['category' => 'Tenses']);

        $vocabularyTags = [
            'Food'           => Tag::firstOrCreate(['name' => 'Food'], ['category' => 'Vocabulary']),
            'Reading'        => Tag::firstOrCreate(['name' => 'Reading'], ['category' => 'Vocabulary']),
            'Relationships'  => Tag::firstOrCreate(['name' => 'Relationships'], ['category' => 'Vocabulary']),
            'Searching'      => Tag::firstOrCreate(['name' => 'Searching'], ['category' => 'Vocabulary']),
            'Love'           => Tag::firstOrCreate(['name' => 'Love'], ['category' => 'Vocabulary']),
            'Letters'        => Tag::firstOrCreate(['name' => 'Letters'], ['category' => 'Vocabulary']),
            'Writing'        => Tag::firstOrCreate(['name' => 'Writing'], ['category' => 'Vocabulary']),
            'Communication'  => Tag::firstOrCreate(['name' => 'Communication'], ['category' => 'Vocabulary']),
            'Neighbours'     => Tag::firstOrCreate(['name' => 'Neighbours'], ['category' => 'Vocabulary']),
            'Sports'         => Tag::firstOrCreate(['name' => 'Sports'], ['category' => 'Vocabulary']),
            'Technology'     => Tag::firstOrCreate(['name' => 'Technology'], ['category' => 'Vocabulary']),
            'Family'         => Tag::firstOrCreate(['name' => 'Family'], ['category' => 'Vocabulary']),
            'Housing'        => Tag::firstOrCreate(['name' => 'Housing'], ['category' => 'Vocabulary']),
            'Community'      => Tag::firstOrCreate(['name' => 'Community'], ['category' => 'Vocabulary']),
        ];

        $questions = [
            [
                'question' => 'They {a1} three chocolates this week. They {a2} them yesterday.',
                'answers'  => [
                    'a1' => ['answer' => 'have eaten', 'verb_hint' => 'eat'],
                    'a2' => ['answer' => 'ate', 'verb_hint' => 'eat'],
                ],
                'level' => 'B1',
                'vocab' => 'Food',
            ],
            [
                'question' => 'I {a1} a lot when I {a2} younger, but since last year I {a3} only two books.',
                'answers'  => [
                    'a1' => ['answer' => 'read', 'verb_hint' => 'read'],
                    'a2' => ['answer' => 'was', 'verb_hint' => 'be'],
                    'a3' => ['answer' => 'have read', 'verb_hint' => 'read'],
                ],
                'level' => 'B1',
                'vocab' => 'Reading',
            ],
            [
                'question' => 'She {a1} him for ages, ten years ago {a2} the last time she {a3} him.',
                'answers'  => [
                    'a1' => ['answer' => 'hasn\'t seen', 'verb_hint' => 'not/see'],
                    'a2' => ['answer' => 'was', 'verb_hint' => 'be'],
                    'a3' => ['answer' => 'saw', 'verb_hint' => 'see'],
                ],
                'level' => 'B1',
                'vocab' => 'Relationships',
            ],
            [
                'question' => 'Oh, finally! Where {a1} you {a2}? I {a3} everywhere. You {a4} yourself really very well.',
                'answers'  => [
                    'a1' => ['answer' => 'have', 'verb_hint' => 'be'],
                    'a2' => ['answer' => 'been', 'verb_hint' => 'be'],
                    'a3' => ['answer' => 'have looked', 'verb_hint' => 'look'],
                    'a4' => ['answer' => 'have hidden', 'verb_hint' => 'hide'],
                ],
                'level' => 'B1',
                'vocab' => 'Searching',
            ],
            [
                'question' => 'Maya and Jed {a1} in love three years ago. They {a2} married last year. They {a3} very happy ever since.',
                'answers'  => [
                    'a1' => ['answer' => 'fell', 'verb_hint' => 'fall'],
                    'a2' => ['answer' => 'got', 'verb_hint' => 'get'],
                    'a3' => ['answer' => 'have been', 'verb_hint' => 'be'],
                ],
                'level' => 'B1',
                'vocab' => 'Love',
            ],
            [
                'question' => 'Yesterday Mr. Smith {a1} a letter from a friend.',
                'answers'  => [
                    'a1' => ['answer' => 'received', 'verb_hint' => 'receive'],
                ],
                'level' => 'B1',
                'vocab' => 'Letters',
            ],
            [
                'question' => 'He {a1} just {a2} an answer.',
                'answers'  => [
                    'a1' => ['answer' => 'has', 'verb_hint' => 'have'],
                    'a2' => ['answer' => 'written', 'verb_hint' => 'write'],
                ],
                'level' => 'B1',
                'vocab' => 'Writing',
            ],
            [
                'question' => 'They {a1} from each other since last year.',
                'answers'  => [
                    'a1' => ['answer' => 'haven\'t heard', 'verb_hint' => 'not/hear'],
                ],
                'level' => 'B1',
                'vocab' => 'Communication',
            ],
            [
                'question' => 'She {a1} her new neighbours yet.',
                'answers'  => [
                    'a1' => ['answer' => 'hasn\'t seen', 'verb_hint' => 'not/see'],
                ],
                'level' => 'B1',
                'vocab' => 'Neighbours',
            ],
            [
                'question' => 'Her husband {a1} already {a2} to them.',
                'answers'  => [
                    'a1' => ['answer' => 'has', 'verb_hint' => 'have'],
                    'a2' => ['answer' => 'spoken', 'verb_hint' => 'speak'],
                ],
                'level' => 'B1',
                'vocab' => 'Neighbours',
            ],
            [
                'question' => 'Tom and George {a1} golf as opponents since their childhood. Last year Tom {a2} the championship. He {a3} for four hours a day ever since.',
                'answers'  => [
                    'a1' => ['answer' => 'have played', 'verb_hint' => 'play'],
                    'a2' => ['answer' => 'won', 'verb_hint' => 'win'],
                    'a3' => ['answer' => 'has practised', 'verb_hint' => 'practise'],
                ],
                'level' => 'B1',
                'vocab' => 'Sports',
            ],
            [
                'question' => 'People {a1} less sociable since internet {a2}.',
                'answers'  => [
                    'a1' => ['answer' => 'have become', 'verb_hint' => 'become'],
                    'a2' => ['answer' => 'appeared', 'verb_hint' => 'appear'],
                ],
                'level' => 'B1',
                'vocab' => 'Technology',
            ],
            [
                'question' => "Mrs. Brown's husband {a1} last year, so she {a2} very lonely ever since.",
                'answers'  => [
                    'a1' => ['answer' => 'died', 'verb_hint' => 'die'],
                    'a2' => ['answer' => 'has felt', 'verb_hint' => 'feel'],
                ],
                'level' => 'B1',
                'vocab' => 'Family',
            ],
            [
                'question' => 'She {a1} in the same house since 1999.',
                'answers'  => [
                    'a1' => ['answer' => 'has lived', 'verb_hint' => 'live'],
                ],
                'level' => 'B1',
                'vocab' => 'Housing',
            ],
            [
                'question' => "The Johnson's {a1} in our neighbourhood three years ago. Since then they {a2} already {a3} a lot of new friends.",
                'answers'  => [
                    'a1' => ['answer' => 'moved', 'verb_hint' => 'move'],
                    'a2' => ['answer' => 'have', 'verb_hint' => 'have'],
                    'a3' => ['answer' => 'made', 'verb_hint' => 'make'],
                ],
                'level' => 'B1',
                'vocab' => 'Community',
            ],
        ];

        $service = new QuestionSeedingService();
        $items   = [];

        foreach ($questions as $i => $q) {
            $index = $i + 1;
            $slug  = Str::slug(class_basename(self::class));
            $max   = 36 - strlen((string) $index) - 1;
            $uuid  = substr($slug, 0, $max) . '-' . $index;

            $answers = [];
            foreach ($q['answers'] as $marker => $data) {
                $answers[] = [
                    'marker'    => $marker,
                    'answer'    => $data['answer'],
                    'verb_hint' => $data['verb_hint'] ?? null,
                ];
            }

            $items[] = [
                'uuid'        => $uuid,
                'question'    => $q['question'],
                'difficulty'  => 2,
                'category_id' => $categoryId,
                'flag'        => 0,
                'source_id'   => $sourceId,
                'tag_ids'     => [$grammarTag->id, $vocabularyTags[$q['vocab']]->id],
                'level'       => $q['level'],
                'answers'     => $answers,
                'options'     => [],
            ];
        }

        $service->seed($items);
    }
}
