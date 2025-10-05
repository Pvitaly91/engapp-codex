<?php
namespace Database\Seeders;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use App\Services\QuestionSeedingService;
use App\Support\Database\Seeder;
use Illuminate\Support\Str;

class PastSimpleContinuousStorySeeder extends Seeder
{
    public function run()
    {
        $categoryId = Category::firstOrCreate(['name' => 'past'])->id;
        $sourceId   = Source::firstOrCreate(['name' => 'Past Simple and Continuous Story'])->id;
        $pastSimpleTag = Tag::firstOrCreate(['name' => 'Past Simple'], ['category' => 'Tenses']);
        $pastContinuousTag = Tag::firstOrCreate(['name' => 'Past Continuous'], ['category' => 'Tenses']);

        $question = "Frank didn't {a1} the new movie very much. "
            . "My friends {a2} a tree in the forest yesterday. "
            . "{a3} she {a4} her trip to Miami last summer? "
            . "My sister {a5} the plants while I was sweeping the floor. "
            . "Johanna is a retired journalist. She {a6} many artists on TV. "
            . "He {a7} any letters while he was living in Canada. "
            . "{a8} you {a9} when I called? "
            . "Why {a10} she {a11} when I arrived? "
            . "He {a12} his hand while he was cooking. "
            . "They {a13} when the police arrived. "
            . "When I {a14}, my mum {a15} dinner. "
            . "Nick {a16} at a party when he {a17} his girlfriend. "
            . "A: What {a18} last weekend? B: Nothing special. "
            . "I {a19} watching that series on Netflix. I {a20} the ending. "
            . "Linda {a21} when the shark {a22} her. "
            . "We {a23} a picnic when it {a24} to rain. "
            . "I {a25} to my friend on the phone when the bus {a26}. "
            . "When the earthquake {a27}, they {a28}. "
            . "What {a29} at 9:00 last night? "
            . "What {a30} while she {a31}?";

        $answers = [
            ['marker' => 'a1',  'answer' => 'like',            'verb_hint' => 'like'],
            ['marker' => 'a2',  'answer' => 'climbed',         'verb_hint' => 'climb'],
            ['marker' => 'a3',  'answer' => 'Did',             'verb_hint' => 'do'],
            ['marker' => 'a4',  'answer' => 'enjoy',           'verb_hint' => 'enjoy'],
            ['marker' => 'a5',  'answer' => 'was watering',    'verb_hint' => 'water'],
            ['marker' => 'a6',  'answer' => 'interviewed',     'verb_hint' => 'interview'],
            ['marker' => 'a7',  'answer' => "didn't send",    'verb_hint' => 'not/send'],
            ['marker' => 'a8',  'answer' => 'Were',            'verb_hint' => 'be'],
            ['marker' => 'a9',  'answer' => 'sleeping',        'verb_hint' => 'sleep'],
            ['marker' => 'a10', 'answer' => 'was',             'verb_hint' => 'be'],
            ['marker' => 'a11', 'answer' => 'crying',          'verb_hint' => 'cry'],
            ['marker' => 'a12', 'answer' => 'burnt',           'verb_hint' => 'burn'],
            ['marker' => 'a13', 'answer' => "weren't fighting", 'verb_hint' => 'not/fight'],
            ['marker' => 'a14', 'answer' => 'got back',        'verb_hint' => 'get back'],
            ['marker' => 'a15', 'answer' => 'was cooking',     'verb_hint' => 'cook'],
            ['marker' => 'a16', 'answer' => 'was dancing',     'verb_hint' => 'dance'],
            ['marker' => 'a17', 'answer' => 'met',             'verb_hint' => 'meet'],
            ['marker' => 'a18', 'answer' => 'were you doing',  'verb_hint' => 'do'],
            ['marker' => 'a19', 'answer' => 'finished',        'verb_hint' => 'finish'],
            ['marker' => 'a20', 'answer' => "didn't like",    'verb_hint' => 'not/like'],
            ['marker' => 'a21', 'answer' => 'was surfing',     'verb_hint' => 'surf'],
            ['marker' => 'a22', 'answer' => 'attacked',        'verb_hint' => 'attack'],
            ['marker' => 'a23', 'answer' => 'were having',     'verb_hint' => 'have'],
            ['marker' => 'a24', 'answer' => 'started',         'verb_hint' => 'start'],
            ['marker' => 'a25', 'answer' => 'was talking',     'verb_hint' => 'talk'],
            ['marker' => 'a26', 'answer' => 'arrived',         'verb_hint' => 'arrive'],
            ['marker' => 'a27', 'answer' => 'happened',        'verb_hint' => 'happen'],
            ['marker' => 'a28', 'answer' => 'were sleeping',   'verb_hint' => 'sleep'],
            ['marker' => 'a29', 'answer' => 'was she doing',   'verb_hint' => 'do'],
            ['marker' => 'a30', 'answer' => 'did she see',     'verb_hint' => 'see'],
            ['marker' => 'a31', 'answer' => 'was swimming',    'verb_hint' => 'swim'],
        ];

        $uuid = Str::slug(class_basename(self::class));

        $service = new QuestionSeedingService();
        $service->seed([
            [
                'uuid'        => $uuid,
                'question'    => $question,
                'category_id' => $categoryId,
                'difficulty'  => 2,
                'source_id'   => $sourceId,
                'flag'        => 0,
                'tag_ids'     => [$pastSimpleTag->id, $pastContinuousTag->id],
                'answers'     => $answers,
                'options'     => [],
            ]
        ]);
    }
}
