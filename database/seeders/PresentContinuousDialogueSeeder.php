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

class PresentContinuousDialogueSeeder extends Seeder
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
            'name' => 'Present Continuous Dialogue'
        ])->id;

        $tenseTag = Tag::firstOrCreate(['name' => 'Present Continuous'], ['category' => 'Tenses']);
        $themeTag = Tag::firstOrCreate(['name' => 'present_continuous_dialogue']);

        $data = [
            [
                'question' => 'Suzan: Hi Mark. What {a1} (you/do)?',
                'answers' => [['marker' => 'a1', 'answer' => 'are you doing', 'verb_hint' => 'do']],
                'options' => ['are you doing', 'do you do', 'did you do', 'are you do'],
            ],
            [
                'question' => 'Mark: I {a1} (watch) TV.',
                'answers' => [['marker' => 'a1', 'answer' => 'am watching', 'verb_hint' => 'watch']],
                'options' => ['am watching', 'am watch', 'is watching', 'watch'],
            ],
            [
                'question' => 'Suzan: What {a1} (you/watch)?',
                'answers' => [['marker' => 'a1', 'answer' => 'are you watching', 'verb_hint' => 'watch']],
                'options' => ['are you watching', 'do you watch', 'did you watch', 'are you watch'],
            ],
            [
                'question' => 'Mark: A football match. Liverpool {a1} (play) against Manchester United.',
                'answers' => [['marker' => 'a1', 'answer' => 'are playing', 'verb_hint' => 'play']],
                'options' => ['are playing', 'is playing', 'play', 'plays'],
            ],
            [
                'question' => 'Suzan: {a1} (you/enjoy) it?',
                'answers' => [['marker' => 'a1', 'answer' => 'Are you enjoying', 'verb_hint' => 'enjoy']],
                'options' => ['Are you enjoying', 'Do you enjoy', 'Did you enjoy', 'Are you enjoy'],
            ],
            [
                'question' => 'Mark: Yes, I {a1}. It\'s a great match.',
                'answers' => [['marker' => 'a1', 'answer' => 'am', 'verb_hint' => 'be']],
                'options' => ['am', 'do', 'did', 'was'],
            ],
            [
                'question' => 'Suzan: {a1} (your team/win)?',
                'answers' => [['marker' => 'a1', 'answer' => 'Is your team winning', 'verb_hint' => 'win']],
                'options' => ['Is your team winning', 'Does your team win', 'Did your team win', 'Is your team win'],
            ],
            [
                'question' => 'Mark: Yes! But what about you? What {a1} (you/do)?',
                'answers' => [['marker' => 'a1', 'answer' => 'are you doing', 'verb_hint' => 'do']],
                'options' => ['are you doing', 'do you do', 'did you do', 'are you do'],
            ],
            [
                'question' => 'Suzan: I\'m with Alice. We {a1} (study) for our maths exam.',
                'answers' => [['marker' => 'a1', 'answer' => 'are studying', 'verb_hint' => 'study']],
                'options' => ['are studying', 'is studying', 'study', 'studies'],
            ],
            [
                'question' => 'Mark: Well, I\'m sure you {a1} (not enjoy) maths. Do you want to take a break and come to my house?',
                'answers' => [['marker' => 'a1', 'answer' => "aren't enjoying", 'verb_hint' => 'enjoy']],
                'options' => ["aren't enjoying", "aren't enjoy", "don\'t enjoy", "not enjoying"],
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
