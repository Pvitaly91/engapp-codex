<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class PastSimpleOrPresentPerfectEx4Seeder extends Seeder
{
    public function run()
    {
        $categoryId = Category::firstOrCreate(['name' => 'past'])->id;
        $sourceId   = Source::firstOrCreate(['name' => 'Past Simple or Present Perfect Exercise 4'])->id;
        $grammarTag = Tag::firstOrCreate(['name' => 'Past Simple or Present Perfect']);

        $vocabularyTags = [
            'Family'        => Tag::firstOrCreate(['name' => 'Family'], ['category' => 'Vocabulary']),
            'Relationships' => Tag::firstOrCreate(['name' => 'Relationships'], ['category' => 'Vocabulary']),
            'Neighbours'    => Tag::firstOrCreate(['name' => 'Neighbours'], ['category' => 'Vocabulary']),
            'Education'     => Tag::firstOrCreate(['name' => 'Education'], ['category' => 'Vocabulary']),
            'Housing'       => Tag::firstOrCreate(['name' => 'Housing'], ['category' => 'Vocabulary']),
            'Sports'        => Tag::firstOrCreate(['name' => 'Sports'], ['category' => 'Vocabulary']),
            'Politics'      => Tag::firstOrCreate(['name' => 'Politics'], ['category' => 'Vocabulary']),
            'History'       => Tag::firstOrCreate(['name' => 'History'], ['category' => 'Vocabulary']),
            'Weather'       => Tag::firstOrCreate(['name' => 'Weather'], ['category' => 'Vocabulary']),
            'Technology'    => Tag::firstOrCreate(['name' => 'Technology'], ['category' => 'Vocabulary']),
            'Accidents'     => Tag::firstOrCreate(['name' => 'Accidents'], ['category' => 'Vocabulary']),
            'Housework'     => Tag::firstOrCreate(['name' => 'Housework'], ['category' => 'Vocabulary']),
            'Cinema'        => Tag::firstOrCreate(['name' => 'Cinema'], ['category' => 'Vocabulary']),
            'Travel'        => Tag::firstOrCreate(['name' => 'Travel'], ['category' => 'Vocabulary']),
            'Languages'     => Tag::firstOrCreate(['name' => 'Languages'], ['category' => 'Vocabulary']),
            'Celebrations'  => Tag::firstOrCreate(['name' => 'Celebrations'], ['category' => 'Vocabulary']),
            'Leisure'       => Tag::firstOrCreate(['name' => 'Leisure'], ['category' => 'Vocabulary']),
            'Crime'         => Tag::firstOrCreate(['name' => 'Crime'], ['category' => 'Vocabulary']),
            'Health'        => Tag::firstOrCreate(['name' => 'Health'], ['category' => 'Vocabulary']),
            'Work'          => Tag::firstOrCreate(['name' => 'Work'], ['category' => 'Vocabulary']),
            'Emotions'      => Tag::firstOrCreate(['name' => 'Emotions'], ['category' => 'Vocabulary']),
        ];

        $questions = [
            [
                'question' => 'My dad {a1} (win) the lottery last weekend.',
                'answers'  => [
                    'a1' => ['answer' => 'won', 'verb_hint' => 'win'],
                ],
                'vocab' => 'Family',
            ],
            [
                'question' => 'I {a1} (not/see) my friend Sam for some time.',
                'answers'  => [
                    'a1' => ['answer' => "haven't seen", 'verb_hint' => 'not/see'],
                ],
                'vocab' => 'Relationships',
            ],
            [
                'question' => 'The neighbours {a1} (build) a greenhouse, it looks nice.',
                'answers'  => [
                    'a1' => ['answer' => 'have built', 'verb_hint' => 'build'],
                ],
                'vocab' => 'Neighbours',
            ],
            [
                'question' => 'My brother {a1} (revise) for his test all evening yesterday.',
                'answers'  => [
                    'a1' => ['answer' => 'revised', 'verb_hint' => 'revise'],
                ],
                'vocab' => 'Education',
            ],
            [
                'question' => 'We {a1} (decide) to move to the countryside soon.',
                'answers'  => [
                    'a1' => ['answer' => 'have decided', 'verb_hint' => 'decide'],
                ],
                'vocab' => 'Housing',
            ],
            [
                'question' => 'They {a1} (not/finish) their training session yet.',
                'answers'  => [
                    'a1' => ['answer' => "haven't finished", 'verb_hint' => 'not/finish'],
                ],
                'vocab' => 'Sports',
            ],
            [
                'question' => 'The Americans {a1} (elect) Mr Trump as their President.',
                'answers'  => [
                    'a1' => ['answer' => 'elected', 'verb_hint' => 'elect'],
                ],
                'vocab' => 'Politics',
            ],
            [
                'question' => 'The Chinese {a1} (build) the longest wall ever.',
                'answers'  => [
                    'a1' => ['answer' => 'have built', 'verb_hint' => 'build'],
                ],
                'vocab' => 'History',
            ],
            [
                'question' => 'It {a1} (not/rain) much this winter!',
                'answers'  => [
                    'a1' => ['answer' => "hasn't rained", 'verb_hint' => 'not/rain'],
                ],
                'vocab' => 'Weather',
            ],
            [
                'question' => 'Sandy {a1} (always/want) to become a star on YouTube so she {a2} (send) some tutorials already.',
                'answers'  => [
                    'a1' => ['answer' => 'has always wanted', 'verb_hint' => 'always/want'],
                    'a2' => ['answer' => 'has sent', 'verb_hint' => 'send'],
                ],
                'vocab' => 'Technology',
            ],
            [
                'question' => 'Oh my god! A car {a1} (just/run) over a child on the pedestrian crossings!',
                'answers'  => [
                    'a1' => ['answer' => 'has just run', 'verb_hint' => 'just/run'],
                ],
                'vocab' => 'Accidents',
            ],
            [
                'question' => 'My children {a1} (tidy) their bedroom last weekend.',
                'answers'  => [
                    'a1' => ['answer' => 'tidied', 'verb_hint' => 'tidy'],
                ],
                'vocab' => 'Housework',
            ],
            [
                'question' => 'The teachers {a1} (correct) our last exam last week.',
                'answers'  => [
                    'a1' => ['answer' => 'corrected', 'verb_hint' => 'correct'],
                ],
                'vocab' => 'Education',
            ],
            [
                'question' => 'I {a1} (not/be) to the cinema for ages.',
                'answers'  => [
                    'a1' => ['answer' => "haven't been", 'verb_hint' => 'not/be'],
                ],
                'vocab' => 'Cinema',
            ],
            [
                'question' => 'My cousin {a1} (get) lost during his last trip in Paris.',
                'answers'  => [
                    'a1' => ['answer' => 'got', 'verb_hint' => 'get'],
                ],
                'vocab' => 'Travel',
            ],
            [
                'question' => 'We {a1} (buy) our house in 2010.',
                'answers'  => [
                    'a1' => ['answer' => 'bought', 'verb_hint' => 'buy'],
                ],
                'vocab' => 'Housing',
            ],
            [
                'question' => 'My step-sister {a1} (live) in London since 2014.',
                'answers'  => [
                    'a1' => ['answer' => 'has lived', 'verb_hint' => 'live'],
                ],
                'vocab' => 'Family',
            ],
            [
                'question' => 'We {a1} (start) learning Spanish two years ago.',
                'answers'  => [
                    'a1' => ['answer' => 'started', 'verb_hint' => 'start'],
                ],
                'vocab' => 'Languages',
            ],
            [
                'question' => 'I am sorry I {a1} (miss) your birthday last Sunday.',
                'answers'  => [
                    'a1' => ['answer' => 'missed', 'verb_hint' => 'miss'],
                ],
                'vocab' => 'Celebrations',
            ],
            [
                'question' => 'They {a1} (get) their swimming pool for three years.',
                'answers'  => [
                    'a1' => ['answer' => 'have had', 'verb_hint' => 'get'],
                ],
                'vocab' => 'Leisure',
            ],
            [
                'question' => 'Politicians {a1} (lose) popularity these past years.',
                'answers'  => [
                    'a1' => ['answer' => 'have lost', 'verb_hint' => 'lose'],
                ],
                'vocab' => 'Politics',
            ],
            [
                'question' => 'Some kids {a1} (steal) my mail from my box.',
                'answers'  => [
                    'a1' => ['answer' => 'have stolen', 'verb_hint' => 'steal'],
                ],
                'vocab' => 'Crime',
            ],
            [
                'question' => "I can't remember when Mike {a1} (last/come) home.",
                'answers'  => [
                    'a1' => ['answer' => 'last came', 'verb_hint' => 'last/come'],
                ],
                'vocab' => 'Relationships',
            ],
            [
                'question' => 'My brother {a1} (not/invite) me lately.',
                'answers'  => [
                    'a1' => ['answer' => "hasn't invited", 'verb_hint' => 'not/invite'],
                ],
                'vocab' => 'Family',
            ],
            [
                'question' => 'The British {a1} (decide) to leave Europe.',
                'answers'  => [
                    'a1' => ['answer' => 'have decided', 'verb_hint' => 'decide'],
                ],
                'vocab' => 'Politics',
            ],
            [
                'question' => 'Christopher Columbus {a1} (discover) America more than five centuries ago, he {a2} (think) it {a3} (be) India.',
                'answers'  => [
                    'a1' => ['answer' => 'discovered', 'verb_hint' => 'discover'],
                    'a2' => ['answer' => 'thought', 'verb_hint' => 'think'],
                    'a3' => ['answer' => 'was', 'verb_hint' => 'be'],
                ],
                'vocab' => 'History',
            ],
            [
                'question' => '{a1} (you/manage) to get an appointment with the doctor for your flu?',
                'answers'  => [
                    'a1' => ['answer' => 'Have you managed', 'verb_hint' => 'manage'],
                ],
                'vocab' => 'Health',
            ],
            [
                'question' => 'I {a1} (work) for hours now.',
                'answers'  => [
                    'a1' => ['answer' => 'have worked', 'verb_hint' => 'work'],
                ],
                'vocab' => 'Work',
            ],
            [
                'question' => 'Sally {a1} (be) very disappointed when she {a2} (find) out that her husband had cancelled their holidays.',
                'answers'  => [
                    'a1' => ['answer' => 'was', 'verb_hint' => 'be'],
                    'a2' => ['answer' => 'found', 'verb_hint' => 'find'],
                ],
                'vocab' => 'Emotions',
            ],
            [
                'question' => 'They {a1} (watch) the football match yesterday.',
                'answers'  => [
                    'a1' => ['answer' => 'watched', 'verb_hint' => 'watch'],
                ],
                'vocab' => 'Sports',
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
                'level'       => 'B1',
                'answers'     => $answers,
                'options'     => [],
            ];
        }

        $service->seed($items);
    }
}
