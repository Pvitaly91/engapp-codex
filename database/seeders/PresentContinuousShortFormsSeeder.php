<?php 

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\VerbHint;
use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PresentContinuousShortFormsSeeder extends Seeder
{
    private function attachOption(Question $question, string $value, ?int $flag = null)
    {
        $option = QuestionOption::firstOrCreate(['option' => $value]);

        $exists = DB::table('question_option_question')
            ->where('question_id', $question->id)
            ->where('option_id', $option->id)
            ->where(function ($query) use ($flag) {
                if ($flag === null) {
                    $query->whereNull('flag');
                } else {
                    $query->where('flag', $flag);
                }
            })
            ->exists();

        if (! $exists) {
            $question->options()->attach($option->id, ['flag' => $flag]);
        }

        return $option;
    }

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

        foreach ($data as $i => $d) {
            $q = Question::create([
                'uuid'        => Str::slug(class_basename(self::class)) . '-' . ($i + 1),
                'question'    => $d['question'],
                'category_id' => $cat_present,
                'difficulty'  => 2,
                'source_id'   => $sourceId,
                'flag'        => 0,
            ]);

            foreach ($d['answers'] as $ans) {
                $option = $this->attachOption($q, $ans['answer']);
                QuestionAnswer::firstOrCreate([
                    'question_id' => $q->id,
                    'marker'      => $ans['marker'],
                    'option_id'   => $option->id,
                ]);
                if (!empty($ans['verb_hint'])) {
                    $hintOption = $this->attachOption($q, $ans['verb_hint'], 1);
                    VerbHint::firstOrCreate([
                        'question_id' => $q->id,
                        'marker'      => $ans['marker'],
                        'option_id'   => $hintOption->id,
                    ]);
                }
            }

            foreach ($d['options'] as $opt) {
                $this->attachOption($q, $opt);
            }

            $q->tags()->syncWithoutDetaching([$tenseTag->id, $themeTag->id]);
        }
    }
}
