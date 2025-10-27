<?php

namespace Database\Seeders\V1\Tenses\Past;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use App\Services\QuestionSeedingService;
use App\Support\Database\Seeder;
use Illuminate\Support\Str;

class PastSimpleOrPastPerfectTestSeeder extends Seeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'past'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Past Simple or Past Perfect -2'])->id;

        $themeTag = Tag::firstOrCreate(['name' => 'Past Simple or Past Perfect'], ['category' => 'Grammar']);
        $pastSimpleTag = Tag::firstOrCreate(['name' => 'Past Simple'], ['category' => 'Tenses']);
        $pastPerfectTag = Tag::firstOrCreate(['name' => 'Past Perfect'], ['category' => 'Tenses']);

        $data = [
            [
                'question' => 'I wish I {a1} taller. I\'m the shortest person in my class.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'were', 'verb_hint' => 'be'],
                ],
                'options' => ['were', 'was', 'had been', 'have been'],
                'level' => 'B1',
            ],
            [
                'question' => 'Before she {a1} home, Amy {a2} some make-up.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'had left', 'verb_hint' => 'leave'],
                    ['marker' => 'a2', 'answer' => 'put on', 'verb_hint' => 'put on'],
                ],
                'options' => ['had left', 'left', 'was leaving', 'has left', 'put on', 'had put on', 'puts on', 'was putting on'],
                'level' => 'B1',
            ],
            [
                'question' => 'I {a1} offended because my friend {a2} me an ungrateful princess.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'felt', 'verb_hint' => 'feel'],
                    ['marker' => 'a2', 'answer' => 'had called', 'verb_hint' => 'call'],
                ],
                'options' => ['felt', 'had felt', 'was feeling', 'feel', 'had called', 'called', 'was calling', 'has called'],
                'level' => 'B1',
            ],
            [
                'question' => 'I wish I {a1} to the party. I have a terrible headache now.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => "hadn't gone", 'verb_hint' => 'not/go'],
                ],
                'options' => ["hadn't gone", "didn't go", "haven't gone", "wasn't going"],
                'level' => 'B1',
            ],
            [
                'question' => 'My sister {a1} me she {a2} my stuff in my room but honestly I don’t believe her.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'told', 'verb_hint' => 'tell'],
                    ['marker' => 'a2', 'answer' => "hadn't touched", 'verb_hint' => 'not/touch'],
                ],
                'options' => ['told', 'had told', 'was telling', 'has told', "hadn't touched", "didn't touch", "hasn't touched", "wasn't touching"],
                'level' => 'B1',
            ],
            [
                'question' => 'Before I {a1} downstairs to the kitchen, I {a2} my bed.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'rushed', 'verb_hint' => 'rush'],
                    ['marker' => 'a2', 'answer' => 'had made', 'verb_hint' => 'make'],
                ],
                'options' => ['rushed', 'had rushed', 'was rushing', 'rush', 'had made', 'made', 'was making', 'have made'],
                'level' => 'B1',
            ],
            [
                'question' => 'When Charles {a1} his office, he {a2} someone {a3} his drawers.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'entered', 'verb_hint' => 'enter'],
                    ['marker' => 'a2', 'answer' => 'realised', 'verb_hint' => 'realise'],
                    ['marker' => 'a3', 'answer' => 'had searched', 'verb_hint' => 'search'],
                ],
                'options' => ['entered', 'had entered', 'was entering', 'enters', 'realised', 'had realised', 'was realising', 'realizes', 'had searched', 'searched', 'was searching', 'has searched'],
                'level' => 'B2',
            ],
            [
                'question' => 'Andy {a1} his safety rope before he {a2} climbing the rock.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'had tightened', 'verb_hint' => 'tighten'],
                    ['marker' => 'a2', 'answer' => 'started', 'verb_hint' => 'start'],
                ],
                'options' => ['had tightened', 'tightened', 'was tightening', 'has tightened', 'started', 'had started', 'was starting', 'starts'],
                'level' => 'B1',
            ],
            [
                'question' => 'When the kids {a1} back home, their dinner {a2} ready.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'came', 'verb_hint' => 'come'],
                    ['marker' => 'a2', 'answer' => 'was', 'verb_hint' => 'be'],
                ],
                'options' => ['came', 'had come', 'were coming', 'come', 'was', 'had been', 'were', 'has been'],
                'level' => 'A2',
            ],
            [
                'question' => 'After he {a1} the gold medal at the Olympic Games, he {a2} to go on holidays.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'had won', 'verb_hint' => 'win'],
                    ['marker' => 'a2', 'answer' => 'decided', 'verb_hint' => 'decide'],
                ],
                'options' => ['had won', 'won', 'was winning', 'has won', 'decided', 'had decided', 'decides', 'was deciding'],
                'level' => 'B1',
            ],
            [
                'question' => 'Sheila {a1} with joy after she {a2} her dream job.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'jumped', 'verb_hint' => 'jump'],
                    ['marker' => 'a2', 'answer' => 'had got', 'verb_hint' => 'get'],
                ],
                'options' => ['jumped', 'had jumped', 'was jumping', 'jumps', 'had got', 'got', 'had gotten', 'was getting'],
                'level' => 'B1',
            ],
            [
                'question' => 'Lots of people {a1} their homes after the tornado {a2} across the country.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'lost', 'verb_hint' => 'lose'],
                    ['marker' => 'a2', 'answer' => 'had swept', 'verb_hint' => 'sweep'],
                ],
                'options' => ['lost', 'had lost', 'were losing', 'lose', 'had swept', 'swept', 'was sweeping', 'has swept'],
                'level' => 'B1',
            ],
            [
                'question' => 'Everybody {a1} astonished when the rock star {a2} on stage in a very bizarre outfit.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'was', 'verb_hint' => 'be'],
                    ['marker' => 'a2', 'answer' => 'appeared', 'verb_hint' => 'appear'],
                ],
                'options' => ['was', 'had been', 'were', 'has been', 'appeared', 'had appeared', 'was appearing', 'appears'],
                'level' => 'A2',
            ],
            [
                'question' => 'After he {a1} a fish sandwich, Will {a2} food poisoning.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'had eaten', 'verb_hint' => 'eat'],
                    ['marker' => 'a2', 'answer' => 'got', 'verb_hint' => 'have'],
                ],
                'options' => ['had eaten', 'ate', 'was eating', 'has eaten', 'got', 'had had', 'was having', 'has had'],
                'level' => 'B1',
            ],
            [
                'question' => 'Grandma {a1} asleep before her favourite soap opera {a2}.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'had fallen', 'verb_hint' => 'fall'],
                    ['marker' => 'a2', 'answer' => 'ended', 'verb_hint' => 'end'],
                ],
                'options' => ['had fallen', 'fell', 'was falling', 'has fallen', 'ended', 'had ended', 'was ending', 'has ended'],
                'level' => 'B1',
            ],
        ];

        $service = new QuestionSeedingService;
        $items = [];
        foreach ($data as $i => $d) {
            $index = $i + 1;
            $slug = Str::slug(class_basename(self::class));
            $max = 36 - strlen((string) $index) - 1;
            $uuid = substr($slug, 0, $max).'-'.$index;

            $items[] = [
                'uuid' => $uuid,
                'question' => $d['question'],
                'difficulty' => in_array($d['level'], ['A1', 'A2']) ? 1 : 2,
                'category_id' => $categoryId,
                'source_id' => $sourceId,
                'flag' => 0,
                'tag_ids' => [$themeTag->id, $pastSimpleTag->id, $pastPerfectTag->id],
                'level' => $d['level'],
                'answers' => $d['answers'],
                'options' => $d['options'],
            ];
        }

        $service->seed($items);
    }
}
