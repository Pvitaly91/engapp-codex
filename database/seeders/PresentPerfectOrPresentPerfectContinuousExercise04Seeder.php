<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use App\Services\QuestionSeedingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PresentPerfectOrPresentPerfectContinuousExercise04Seeder extends Seeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Present Perfect'])->id;

        $sourceId = Source::firstOrCreate([
            'name' => 'Present Perfect or Present Perfect Continuous — Exercise 04',
        ])->id;

        $tenseTags = [
            'perfect' => Tag::firstOrCreate(['name' => 'Present Perfect'], ['category' => 'Tenses']),
            'perfect_continuous' => Tag::firstOrCreate(['name' => 'Present Perfect Continuous'], ['category' => 'Tenses']),
        ];

        $detailTags = [
            1 => Tag::firstOrCreate(['name' => 'present_perfect_continuous_recent_activity_result'], ['category' => 'Details']),
            2 => Tag::firstOrCreate(['name' => 'present_perfect_completed_action_fixing'], ['category' => 'Details']),
            3 => Tag::firstOrCreate(['name' => 'present_perfect_continuous_duration_since_morning'], ['category' => 'Details']),
            4 => Tag::firstOrCreate(['name' => 'present_perfect_result_visible_evidence'], ['category' => 'Details']),
            5 => Tag::firstOrCreate(['name' => 'present_perfect_continuous_symptom_cause'], ['category' => 'Details']),
            6 => Tag::firstOrCreate(['name' => 'present_perfect_continuous_recent_activity_question'], ['category' => 'Details']),
            7 => Tag::firstOrCreate(['name' => 'present_perfect_support_preparation'], ['category' => 'Details']),
            8 => Tag::firstOrCreate(['name' => 'present_perfect_continuous_recent_activity_smell'], ['category' => 'Details']),
            9 => Tag::firstOrCreate(['name' => 'present_perfect_continuous_emotion_result'], ['category' => 'Details']),
        ];

        $questions = [
            [
                'question' => 'Why are your hands dirty? – I {a1} in the garden.',
                'answers' => [
                    'a1' => ['answer' => 'have been digging', 'verb_hint' => 'dig'],
                ],
                'options' => ['have dug', 'have been digging', 'dug', 'was digging'],
                'level' => 'B1',
                'tenses' => ['perfect_continuous'],
                'detail_tag' => 1,
            ],
            [
                'question' => 'The computer works fine again. Tom {a1} it.',
                'answers' => [
                    'a1' => ['answer' => 'has fixed', 'verb_hint' => 'fix'],
                ],
                'options' => ['has fixed', 'has been fixing', 'fixed', 'was fixing'],
                'level' => 'A2',
                'tenses' => ['perfect'],
                'detail_tag' => 2,
            ],
            [
                'question' => 'I need a break, I {a1} emails since the morning.',
                'answers' => [
                    'a1' => ['answer' => 'have been writing', 'verb_hint' => 'write'],
                ],
                'options' => ['have written', 'have been writing', 'wrote', 'was writing'],
                'level' => 'B1',
                'tenses' => ['perfect_continuous'],
                'detail_tag' => 3,
            ],
            [
                'question' => 'The windows look shiny. Somebody {a1} them.',
                'answers' => [
                    'a1' => ['answer' => 'has cleaned', 'verb_hint' => 'clean'],
                ],
                'options' => ['has cleaned', 'has been cleaning', 'cleaned', 'was cleaning'],
                'level' => 'A2',
                'tenses' => ['perfect'],
                'detail_tag' => 4,
            ],
            [
                'question' => 'I’m sneezing a lot. I {a1} flowers outside.',
                'answers' => [
                    'a1' => ['answer' => 'have been picking', 'verb_hint' => 'pick'],
                ],
                'options' => ['have picked', 'have been picking', 'picked', 'was picking'],
                'level' => 'B1',
                'tenses' => ['perfect_continuous'],
                'detail_tag' => 5,
            ],
            [
                'question' => 'You smell of paint! {a1}?',
                'answers' => [
                    'a1' => ['answer' => 'Have you been painting', 'verb_hint' => 'you/paint'],
                ],
                'options' => ['Have you painted', 'Have you been painting', 'Did you paint', 'Were you painting'],
                'level' => 'B1',
                'tenses' => ['perfect_continuous'],
                'detail_tag' => 6,
            ],
            [
                'question' => 'Anna is confident for her driving test. I {a1} her to practise.',
                'answers' => [
                    'a1' => ['answer' => 'have helped', 'verb_hint' => 'help'],
                ],
                'options' => ['have helped', 'have been helping', 'helped', 'was helping'],
                'level' => 'A2',
                'tenses' => ['perfect'],
                'detail_tag' => 7,
            ],
            [
                'question' => 'It smells nice in here because we {a1} a cake.',
                'answers' => [
                    'a1' => ['answer' => 'have been baking', 'verb_hint' => 'bake'],
                ],
                'options' => ['have baked', 'have been baking', 'baked', 'were baking'],
                'level' => 'B1',
                'tenses' => ['perfect_continuous'],
                'detail_tag' => 8,
            ],
            [
                'question' => 'His eyes are red because he {a1} for hours.',
                'answers' => [
                    'a1' => ['answer' => 'has been crying', 'verb_hint' => 'cry'],
                ],
                'options' => ['has cried', 'has been crying', 'cried', 'was crying'],
                'level' => 'B1',
                'tenses' => ['perfect_continuous'],
                'detail_tag' => 9,
            ],
        ];

        $service = new QuestionSeedingService();
        $items = [];
        $slug = Str::slug(class_basename(self::class));
        $index = 1;

        foreach ($questions as $question) {
            $uuid = substr($slug, 0, 36 - strlen((string) $index) - 1) . '-' . $index;

            $answers = [];
            foreach ($question['answers'] as $marker => $answerData) {
                $answers[] = [
                    'marker' => $marker,
                    'answer' => $answerData['answer'],
                    'verb_hint' => $answerData['verb_hint'],
                ];
            }

            $tagIds = [$detailTags[$question['detail_tag']]->id];
            foreach ($question['tenses'] as $tense) {
                $tagIds[] = $tenseTags[$tense]->id;
            }

            $items[] = [
                'uuid' => $uuid,
                'question' => $question['question'],
                'difficulty' => $question['level'] === 'A1' ? 1 : 2,
                'category_id' => $categoryId,
                'flag' => 0,
                'source_id' => $sourceId,
                'tag_ids' => $tagIds,
                'level' => $question['level'],
                'answers' => $answers,
                'options' => $question['options'],
            ];

            $index++;
        }

        $service->seed($items);
    }
}
