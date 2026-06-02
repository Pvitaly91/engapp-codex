<?php

namespace Database\Seeders\V1\Tenses\Mixed;

use App\Support\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class PresentContinuousPastSimpleTestSeeder extends Seeder
{
    public function run()
    {
        $cats = [
            'past' => Category::firstOrCreate(['name' => 'past']),
            'present' => Category::firstOrCreate(['name' => 'present']),
        ];

        $sourceId = Source::firstOrCreate(['name' => 'Present/Past Mix Test'])->id;
        $themeTag = Tag::firstOrCreate(['name' => 'present_continuous_past_simple_test']);

        $questions = [
            [
                'question' => 'Can I talk to you? — No, you can\'t. I {a1} a bath.',
                'category' => 'present',
                'answers' => [
                    'a1' => ['answer' => 'am having', 'verb_hint' => 'have'],
                ],
                'options' => ['am having', 'have', 'had'],
            ],
            [
                'question' => 'She {a1} to music every morning.',
                'category' => 'present',
                'answers' => [
                    'a1' => ['answer' => 'listens', 'verb_hint' => 'listen'],
                ],
                'options' => ['listens', 'listen', 'listened'],
            ],
            [
                'question' => 'We {a1} metal music, we {a2} rock.',
                'category' => 'present',
                'answers' => [
                    'a1' => ['answer' => "don\'t like", 'verb_hint' => 'not/like'],
                    'a2' => ['answer' => 'prefer', 'verb_hint' => 'prefer'],
                ],
                'options' => ["don\'t like / prefer", 'like / prefer', "don\'t like / prefers"],
            ],
            [
                'question' => 'When he {a1} the accident yesterday, he {a2} the driver.',
                'category' => 'past',
                'answers' => [
                    'a1' => ['answer' => 'saw', 'verb_hint' => 'see'],
                    'a2' => ['answer' => 'helped', 'verb_hint' => 'help'],
                ],
                'options' => ['saw / helped', 'saw / helps', 'see / helped'],
            ],
            [
                'question' => 'What {a1}? — I {a2} for my glasses.',
                'category' => 'present',
                'answers' => [
                    'a1' => ['answer' => 'are you doing', 'verb_hint' => 'do'],
                    'a2' => ['answer' => 'am looking', 'verb_hint' => 'look'],
                ],
                'options' => ['are you doing / am looking', 'do you do / look', 'were you doing / was looking'],
            ],
            [
                'question' => 'They {a1} presents when they {a2} us.',
                'category' => 'present',
                'answers' => [
                    'a1' => ['answer' => 'always bring', 'verb_hint' => 'bring'],
                    'a2' => ['answer' => 'visit', 'verb_hint' => 'visit'],
                ],
                'options' => ['always bring / visit', 'brought / visited', 'bring / visits'],
            ],
            [
                'question' => 'When we {a1} younger, we {a2} Prague was in France.',
                'category' => 'past',
                'answers' => [
                    'a1' => ['answer' => 'were', 'verb_hint' => 'be'],
                    'a2' => ['answer' => 'thought', 'verb_hint' => 'think'],
                ],
                'options' => ['were / thought', 'are / think', 'was / thought'],
            ],
            [
                'question' => 'He {a1} the award in 2006.',
                'category' => 'past',
                'answers' => [
                    'a1' => ['answer' => "didn\'t win", 'verb_hint' => 'not/win'],
                ],
                'options' => ["didn\'t win", 'wins', 'won'],
            ],
            [
                'question' => "That's typical! He {a1} to win every time he {a2} in a competition.",
                'category' => 'present',
                'answers' => [
                    'a1' => ['answer' => 'wants', 'verb_hint' => 'want'],
                    'a2' => ['answer' => 'participates', 'verb_hint' => 'participate'],
                ],
                'options' => ['wants / participates', 'want / participates', 'wanted / participated'],
            ],
            [
                'question' => 'Why {a1} that toy to school last week? — Because I {a2} to show it to my schoolmates.',
                'category' => 'past',
                'answers' => [
                    'a1' => ['answer' => 'did you bring', 'verb_hint' => 'bring'],
                    'a2' => ['answer' => 'wanted', 'verb_hint' => 'want'],
                ],
                'options' => ['did you bring / wanted', 'do you bring / want', 'were you bringing / was wanting'],
            ],
            [
                'question' => 'Be quiet, please. My children {a1} to sleep.',
                'category' => 'present',
                'answers' => [
                    'a1' => ['answer' => 'are trying', 'verb_hint' => 'try'],
                ],
                'options' => ['are trying', 'try', 'tried'],
            ],
            [
                'question' => 'They {a1} to school at weekends.',
                'category' => 'present',
                'answers' => [
                    'a1' => ['answer' => "don\'t go", 'verb_hint' => 'not/go'],
                ],
                'options' => ["don\'t go", 'go', 'went'],
            ],
            [
                'question' => 'Why {a1} him? He {a2} the truth. He {a3}.',
                'category' => 'present',
                'answers' => [
                    'a1' => ['answer' => 'do you believe', 'verb_hint' => 'believe'],
                    'a2' => ['answer' => 'never tells', 'verb_hint' => 'tell'],
                    'a3' => ['answer' => 'always lies', 'verb_hint' => 'lie'],
                ],
                'options' => ['do you believe / never tells / always lies', 'did you believe / never told / always lied'],
            ],
            [
                'question' => 'She {a1} to visit London last month. She {a2} interesting people, {a3} wonderful sights, and {a4} herself a lot.',
                'category' => 'past',
                'answers' => [
                    'a1' => ['answer' => 'decided', 'verb_hint' => 'decide'],
                    'a2' => ['answer' => 'met', 'verb_hint' => 'meet'],
                    'a3' => ['answer' => 'saw', 'verb_hint' => 'see'],
                    'a4' => ['answer' => 'enjoyed', 'verb_hint' => 'enjoy'],
                ],
                'options' => ['decided / met / saw / enjoyed', 'decides / meets / sees / enjoys'],
            ],
            [
                'question' => 'Where {a1}? I {a2} for her.',
                'category' => 'present',
                'answers' => [
                    'a1' => ['answer' => 'is she', 'verb_hint' => 'be'],
                    'a2' => ['answer' => 'am waiting', 'verb_hint' => 'wait'],
                ],
                'options' => ['is she / am waiting', 'was she / waited', 'does she be / wait'],
            ],
            [
                'question' => '{a1} that water {a2} at 100 degrees Celsius?',
                'category' => 'present',
                'answers' => [
                    'a1' => ['answer' => 'Do they know', 'verb_hint' => 'know'],
                    'a2' => ['answer' => 'boils', 'verb_hint' => 'boil'],
                ],
                'options' => ['Do they know / boils', 'Did they know / boiled', 'Are they knowing / is boiling'],
            ],
            [
                'question' => 'My parents {a1} the house in 1985.',
                'category' => 'past',
                'answers' => [
                    'a1' => ['answer' => 'built', 'verb_hint' => 'build'],
                ],
                'options' => ['built', 'build', 'were building'],
            ],
            [
                'question' => 'He {a1} her because she {a2} up with him last year.',
                'category' => 'past',
                'answers' => [
                    'a1' => ['answer' => "didn\'t marry", 'verb_hint' => 'not/marry'],
                    'a2' => ['answer' => 'broke', 'verb_hint' => 'break'],
                ],
                'options' => ["didn\'t marry / broke", 'doesn\'t marry / breaks', 'married / broke'],
            ],
            [
                'question' => 'Look at her! She {a1} in the park over there. Isn\'t she wonderful?',
                'category' => 'present',
                'answers' => [
                    'a1' => ['answer' => 'is sitting', 'verb_hint' => 'sit'],
                ],
                'options' => ['is sitting', 'sits', 'sat'],
            ],
            [
                'question' => '{a1} the book he {a2} yesterday?',
                'category' => 'past',
                'answers' => [
                    'a1' => ['answer' => 'Did he like', 'verb_hint' => 'like'],
                    'a2' => ['answer' => 'borrowed', 'verb_hint' => 'borrow'],
                ],
                'options' => ['Did he like / borrowed', 'Does he like / borrows', 'Did he like / borrow'],
            ],
        ];

        $service = new QuestionSeedingService();
        $items = [];
        foreach ($questions as $i => $q) {
            $index = $i + 1;
            $slug  = Str::slug(class_basename(self::class));
            $max   = 36 - strlen((string) $index) - 1;
            $uuid  = substr($slug, 0, $max) . '-' . $index;

            $answers = [];
            foreach ($q['answers'] as $marker => $answerData) {
                $answers[] = [
                    'marker'    => $marker,
                    'answer'    => $answerData['answer'],
                    'verb_hint' => $answerData['verb_hint'] ?? null,
                ];
            }

            $items[] = [
                'uuid'        => $uuid,
                'question'    => $q['question'],
                'difficulty'  => 2,
                'category_id' => $cats[$q['category']]->id,
                'flag'        => 0,
                'source_id'   => $sourceId,
                'tag_ids'     => [$themeTag->id],
                'answers'     => $answers,
                'options'     => $q['options'],
            ];
        }

        $service->seed($items);
    }
}
