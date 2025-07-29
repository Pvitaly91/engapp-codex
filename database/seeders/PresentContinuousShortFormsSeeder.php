<?php 

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class PresentContinuousShortFormsSeeder extends Seeder
{

    public function run()
    {
        $cat_present = Category::firstOrCreate(['name' => 'present'])->id;
        $sourceId = Source::firstOrCreate([
            'name' => 'Present Continuous Short Forms'
        ])->id;

        $tenseTag = Tag::firstOrCreate(['name' => 'Present Continuous'], ['category' => 'Tenses']);
        $themeTag = Tag::firstOrCreate(['name' => 'present_continuous_short_forms']);

        $data = [
            [
                'question' => 'He {a1} money because he wants to buy a car. (save)',
                'answers' => [['marker' => 'a1', 'answer' => "is saving", 'verb_hint' => 'save']],
                'options' => ['is saving', "isn\'t saving", 'saves', "doesn\'t save"],
            ],
            [
                'question' => 'You {a1} to me. (not listen)',
                'answers' => [['marker' => 'a1', 'answer' => "aren't listening", 'verb_hint' => 'listen']],
                'options' => ["aren't listening", "isn't listening", "doesn't listen", "are listening"],
            ],
            [
                'question' => 'My parents {a1} for a new apartment. (look)',
                'answers' => [['marker' => 'a1', 'answer' => "are looking", 'verb_hint' => 'look']],
                'options' => ['are looking', 'is looking', 'look', 'are look'],
            ],
            [
                'question' => 'The victim {a1} with the police. (not cooperate)',
                'answers' => [['marker' => 'a1', 'answer' => "isn't cooperating", 'verb_hint' => 'cooperate']],
                'options' => ["isn't cooperating", "aren't cooperating", "doesn't cooperate", "is cooperating"],
            ],
            [
                'question' => 'We {a1} a trip to Japan. (plan)',
                'answers' => [['marker' => 'a1', 'answer' => "are planning", 'verb_hint' => 'plan']],
                'options' => ['are planning', 'planning', 'plans', 'are plan'],
            ],
            [
                'question' => 'I {a1} fish for dinner. (cook)',
                'answers' => [['marker' => 'a1', 'answer' => "am cooking", 'verb_hint' => 'cook']],
                'options' => ['am cooking', "is cooking", "are cooking", "cook"],
            ],
            [
                'question' => 'The water {a1}. (boil)',
                'answers' => [['marker' => 'a1', 'answer' => "is boiling", 'verb_hint' => 'boil']],
                'options' => ['is boiling', "are boiling", "boil", "boils"],
            ],
            [
                'question' => 'He {a1} because he has health problems. (not train)',
                'answers' => [['marker' => 'a1', 'answer' => "isn't training", 'verb_hint' => 'train']],
                'options' => ["isn't training", "aren't training", "isn't train", "doesn't train"],
            ],
            [
                'question' => 'I {a1} at present. I\'m unemployed. (not work)',
                'answers' => [['marker' => 'a1', 'answer' => "am not working", 'verb_hint' => 'work']],
                'options' => ['am not working', "aren't working", "am working", "work"],
            ],
            [
                'question' => 'It {a1} now. We can go to the park. (not rain)',
                'answers' => [['marker' => 'a1', 'answer' => "isn't raining", 'verb_hint' => 'rain']],
                'options' => ["isn't raining", "aren't raining", "doesn't rain", "is raining"],
            ],
        ];

        $service = new QuestionSeedingService();
        $items = [];
        foreach ($data as $i => $d) {
            $index = $i + 1;
            $slug  = Str::slug(class_basename(self::class));
            $max   = 36 - strlen((string) $index) - 1;
            $uuid  = substr($slug, 0, $max) . '-' . $index;

            $d['uuid']        = $uuid;
            $d['category_id'] = $cat_present;
            $d['difficulty']  = 2;
            $d['source_id']   = $sourceId;
            $d['flag']        = 0;
            $d['tag_ids']     = [$tenseTag->id, $themeTag->id];

            $items[] = $d;
        }

        $service->seed($items);
    }
}
