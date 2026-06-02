<?php

namespace Database\Seeders\V1\Tenses\Past;

use App\Support\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class PastPerfectVsPastSimpleTestSeeder extends Seeder
{
    public function run()
    {
        $categoryId = 1; // Past
        $sourceId = Source::firstOrCreate(['name' => 'Past perfect / Past simple'])->id;

        $generalTag = Tag::firstOrCreate(['name' => 'Past Perfect vs Past Simple'], ['category' => 'Grammar']);

        $detailedTags = [
            'Past Simple' => Tag::firstOrCreate(['name' => 'Past Simple'], ['category' => 'Tenses']),
            'Past Perfect' => Tag::firstOrCreate(['name' => 'Past Perfect'], ['category' => 'Tenses']),
        ];

        $questions = [
            [
                'question' => 'Tom {a1} me the book yesterday but I knew he {a2} it.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'gave', 'verb_hint' => 'give'],
                    ['marker' => 'a2', 'answer' => "hadn't read", 'verb_hint' => 'not read'],
                ],
                'options' => ['gave', 'had given', 'was giving', 'has given', "hadn't read", "didn't read", "wasn't reading", "hasn't read"],
                'tenses' => ['Past Simple', 'Past Perfect'],
            ],
            [
                'question' => 'Alice came back from Australia and she said that she {a1} it a lot there.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'had enjoyed', 'verb_hint' => 'enjoy'],
                ],
                'options' => ['had enjoyed', 'enjoyed', 'was enjoying', 'has enjoyed'],
                'tenses' => ['Past Perfect'],
            ],
            [
                'question' => 'When I arrived home I realised that I {a1} my grandparents.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => "hadn't phoned", 'verb_hint' => 'not phone'],
                ],
                'options' => ["hadn't phoned", "didn't phone", "wasn't phoning", "haven't phoned"],
                'tenses' => ['Past Perfect'],
            ],
            [
                'question' => 'The postman {a1} after I {a2} the office.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'came', 'verb_hint' => 'come'],
                    ['marker' => 'a2', 'answer' => 'had left', 'verb_hint' => 'leave'],
                ],
                'options' => ['came', 'had come', 'was coming', 'comes', 'had left', 'left', 'was leaving', 'have left'],
                'tenses' => ['Past Simple', 'Past Perfect'],
            ],
            [
                'question' => 'When the old lady {a1} to her flat, she {a2} that burglars {a3}.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'returned', 'verb_hint' => 'return'],
                    ['marker' => 'a2', 'answer' => 'saw', 'verb_hint' => 'see'],
                    ['marker' => 'a3', 'answer' => 'had broken in', 'verb_hint' => 'break into'],
                ],
                'options' => ['returned', 'had returned', 'was returning', 'returns', 'saw', 'had seen', 'was seeing', 'sees', 'had broken in', 'broke in', 'were breaking in', 'has broken in'],
                'tenses' => ['Past Simple', 'Past Perfect'],
            ],
            [
                'question' => 'The train {a1} before Helen {a2} at the station.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'had left', 'verb_hint' => 'leave'],
                    ['marker' => 'a2', 'answer' => 'arrived', 'verb_hint' => 'arrive'],
                ],
                'options' => ['had left', 'left', 'was leaving', 'has left', 'arrived', 'had arrived', 'was arriving', 'arrives'],
                'tenses' => ['Past Simple', 'Past Perfect'],
            ],
            [
                'question' => 'My grandmother {a1} some cheese sandwiches when I {a2} home at 5.30.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'had made', 'verb_hint' => 'make'],
                    ['marker' => 'a2', 'answer' => 'got', 'verb_hint' => 'get'],
                ],
                'options' => ['had made', 'made', 'was making', 'has made', 'got', 'had got', 'was getting', 'get'],
                'tenses' => ['Past Simple', 'Past Perfect'],
            ],
            [
                'question' => 'After she {a1} the lesson, she {a2} the exercises.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'had studied', 'verb_hint' => 'study'],
                    ['marker' => 'a2', 'answer' => 'did', 'verb_hint' => 'do'],
                ],
                'options' => ['had studied', 'studied', 'was studying', 'has studied', 'did', 'had done', 'was doing', 'does'],
                'tenses' => ['Past Simple', 'Past Perfect'],
            ],
            [
                'question' => 'Barbara {a1} that she {a2} any shopping for the weekend.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'forgot', 'verb_hint' => 'forget'],
                    ['marker' => 'a2', 'answer' => "hadn't done", 'verb_hint' => 'not do'],
                ],
                'options' => ['forgot', 'had forgotten', 'was forgetting', 'forgets', "hadn't done", "didn't do", "hasn't done", "wasn't doing"],
                'tenses' => ['Past Simple', 'Past Perfect'],
            ],
            [
                'question' => 'Jack {a1} very tired because he {a2} until late.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'was', 'verb_hint' => 'be'],
                    ['marker' => 'a2', 'answer' => 'had worked', 'verb_hint' => 'work'],
                ],
                'options' => ['was', 'had been', 'were', 'has been', 'had worked', 'worked', 'was working', 'has worked'],
                'tenses' => ['Past Simple', 'Past Perfect'],
            ],
            [
                'question' => 'Before Henry {a1} home, his friend {a2} the dog for a walk.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'came', 'verb_hint' => 'come'],
                    ['marker' => 'a2', 'answer' => 'had taken', 'verb_hint' => 'take'],
                ],
                'options' => ['came', 'had come', 'was coming', 'comes', 'had taken', 'took', 'was taking', 'has taken'],
                'tenses' => ['Past Simple', 'Past Perfect'],
            ],
            [
                'question' => 'By the time Linda {a1} her car, her boyfriend {a2} two cups of coffee.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'parked', 'verb_hint' => 'park'],
                    ['marker' => 'a2', 'answer' => 'had drunk', 'verb_hint' => 'drink'],
                ],
                'options' => ['parked', 'had parked', 'was parking', 'parks', 'had drunk', 'drank', 'was drinking', 'has drunk'],
                'tenses' => ['Past Simple', 'Past Perfect'],
            ],
            [
                'question' => 'My dog {a1} hungry because he {a2} nothing to eat since breakfast.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'was', 'verb_hint' => 'be'],
                    ['marker' => 'a2', 'answer' => 'had had', 'verb_hint' => 'have'],
                ],
                'options' => ['was', 'had been', 'were', 'has been', 'had had', 'had', 'was having', 'has had'],
                'tenses' => ['Past Simple', 'Past Perfect'],
            ],
            [
                'question' => 'I wondered where I {a1} him before.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'had seen', 'verb_hint' => 'see'],
                ],
                'options' => ['had seen', 'saw', 'was seeing', 'have seen'],
                'tenses' => ['Past Perfect'],
            ],
            [
                'question' => 'The robbers {a1} by the time the police {a2}.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'had escaped', 'verb_hint' => 'escape'],
                    ['marker' => 'a2', 'answer' => 'arrived', 'verb_hint' => 'arrive'],
                ],
                'options' => ['had escaped', 'escaped', 'were escaping', 'has escaped', 'arrived', 'had arrived', 'was arriving', 'arrives'],
                'tenses' => ['Past Simple', 'Past Perfect'],
            ],
            [
                'question' => 'The policeman {a1} the man who {a2} the money.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'arrested', 'verb_hint' => 'arrest'],
                    ['marker' => 'a2', 'answer' => 'had stolen', 'verb_hint' => 'steal'],
                ],
                'options' => ['arrested', 'had arrested', 'was arresting', 'arrests', 'had stolen', 'stole', 'was stealing', 'has stolen'],
                'tenses' => ['Past Simple', 'Past Perfect'],
            ],
            [
                'question' => 'The computer {a1} that George Hurt {a2} some crimes in the past.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'showed', 'verb_hint' => 'show'],
                    ['marker' => 'a2', 'answer' => 'had committed', 'verb_hint' => 'commit'],
                ],
                'options' => ['showed', 'had shown', 'was showing', 'shows', 'had committed', 'committed', 'was committing', 'has committed'],
                'tenses' => ['Past Simple', 'Past Perfect'],
            ],
            [
                'question' => 'The man {a1} all the windows after he {a2} the front door.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'checked', 'verb_hint' => 'check'],
                    ['marker' => 'a2', 'answer' => 'had locked', 'verb_hint' => 'lock'],
                ],
                'options' => ['checked', 'had checked', 'was checking', 'checks', 'had locked', 'locked', 'was locking', 'has locked'],
                'tenses' => ['Past Simple', 'Past Perfect'],
            ],
            [
                'question' => 'When I {a1} the chocolate box, I saw that somebody {a2} all of them.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'opened', 'verb_hint' => 'open'],
                    ['marker' => 'a2', 'answer' => 'had eaten', 'verb_hint' => 'eat'],
                ],
                'options' => ['opened', 'had opened', 'was opening', 'open', 'had eaten', 'ate', 'was eating', 'has eaten'],
                'tenses' => ['Past Simple', 'Past Perfect'],
            ],
            [
                'question' => 'When Elisa {a1} Arthur, he {a2} his studies at university.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'met', 'verb_hint' => 'meet'],
                    ['marker' => 'a2', 'answer' => 'had finished', 'verb_hint' => 'finish'],
                ],
                'options' => ['met', 'had met', 'was meeting', 'meets', 'had finished', 'finished', 'was finishing', 'has finished'],
                'tenses' => ['Past Simple', 'Past Perfect'],
            ],
            [
                'question' => 'I {a1} him because I {a2} his photo in the newspaper before.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'recognized', 'verb_hint' => 'recognize'],
                    ['marker' => 'a2', 'answer' => 'had seen', 'verb_hint' => 'see'],
                ],
                'options' => ['recognized', 'had recognized', 'was recognizing', 'recognised', 'had seen', 'saw', 'was seeing', 'have seen'],
                'tenses' => ['Past Simple', 'Past Perfect'],
            ],
            [
                'question' => 'Margaret was upset because her husband {a1} her birthday.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'had forgotten', 'verb_hint' => 'forget'],
                ],
                'options' => ['had forgotten', 'forgot', 'was forgetting', 'has forgotten'],
                'tenses' => ['Past Perfect'],
            ],
        ];

        $service = new QuestionSeedingService();
        $items = [];

        foreach ($questions as $i => $q) {
            $index = $i + 1;
            $slug = Str::slug(class_basename(self::class));
            $max  = 36 - strlen((string) $index) - 1;
            $uuid = substr($slug, 0, $max) . '-' . $index;

            $tagIds = [$generalTag->id];
            foreach ($q['tenses'] as $tense) {
                $tagIds[] = $detailedTags[$tense]->id;
            }

            $items[] = [
                'uuid'        => $uuid,
                'question'    => $q['question'],
                'difficulty'  => 2,
                'category_id' => $categoryId,
                'flag'        => 0,
                'source_id'   => $sourceId,
                'tag_ids'     => $tagIds,
                'level'       => 'B1',
                'answers'     => $q['answers'],
                'options'     => $q['options'],
            ];
        }

        $service->seed($items);
    }
}
