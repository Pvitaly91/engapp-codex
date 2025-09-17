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
            'name' => 'Present Perfect or Present Perfect Continuous — Exercise 04 - AI',
        ])->id;

        $tenseTags = [
            'perfect' => Tag::firstOrCreate(['name' => 'Present Perfect'], ['category' => 'Tenses']),
            'perfect_continuous' => Tag::firstOrCreate(['name' => 'Present Perfect Continuous'], ['category' => 'Tenses']),
        ];

        $detailTags = [
            'recent_activity'    => Tag::firstOrCreate(['name' => 'present_perfect_recent_activity'], ['category' => 'Details']),
            'completed_action'   => Tag::firstOrCreate(['name' => 'present_perfect_completed_action'], ['category' => 'Details']),
            'duration'           => Tag::firstOrCreate(['name' => 'present_perfect_duration'], ['category' => 'Details']),
            'visible_result'     => Tag::firstOrCreate(['name' => 'present_perfect_visible_result'], ['category' => 'Details']),
            'symptom'            => Tag::firstOrCreate(['name' => 'present_perfect_symptom'], ['category' => 'Details']),
            'activity_question'  => Tag::firstOrCreate(['name' => 'present_perfect_recent_activity_question'], ['category' => 'Details']),
            'support'            => Tag::firstOrCreate(['name' => 'present_perfect_support'], ['category' => 'Details']),
            'sensory_result'     => Tag::firstOrCreate(['name' => 'present_perfect_sensory_result'], ['category' => 'Details']),
            'emotion_result'     => Tag::firstOrCreate(['name' => 'present_perfect_emotion_result'], ['category' => 'Details']),
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
                'detail_tag' => 'recent_activity',
            ],
            [
                'question' => 'The computer works fine again. Tom {a1} it.',
                'answers' => [
                    'a1' => ['answer' => 'has fixed', 'verb_hint' => 'fix'],
                ],
                'options' => ['has fixed', 'has been fixing', 'fixed', 'was fixing'],
                'level' => 'A2',
                'tenses' => ['perfect'],
                'detail_tag' => 'completed_action',
            ],
            [
                'question' => 'I need a break, I {a1} emails since the morning.',
                'answers' => [
                    'a1' => ['answer' => 'have been writing', 'verb_hint' => 'write'],
                ],
                'options' => ['have written', 'have been writing', 'wrote', 'was writing'],
                'level' => 'B1',
                'tenses' => ['perfect_continuous'],
                'detail_tag' => 'duration',
            ],
            [
                'question' => 'The windows look shiny. Somebody {a1} them.',
                'answers' => [
                    'a1' => ['answer' => 'has cleaned', 'verb_hint' => 'clean'],
                ],
                'options' => ['has cleaned', 'has been cleaning', 'cleaned', 'was cleaning'],
                'level' => 'A2',
                'tenses' => ['perfect'],
                'detail_tag' => 'visible_result',
            ],
            [
                'question' => 'I’m sneezing a lot. I {a1} flowers outside.',
                'answers' => [
                    'a1' => ['answer' => 'have been picking', 'verb_hint' => 'pick'],
                ],
                'options' => ['have picked', 'have been picking', 'picked', 'was picking'],
                'level' => 'B1',
                'tenses' => ['perfect_continuous'],
                'detail_tag' => 'symptom',
            ],
            [
                'question' => 'You smell of paint! {a1}?',
                'answers' => [
                    'a1' => ['answer' => 'Have you been painting', 'verb_hint' => 'you/paint'],
                ],
                'options' => ['Have you painted', 'Have you been painting', 'Did you paint', 'Were you painting'],
                'level' => 'B1',
                'tenses' => ['perfect_continuous'],
                'detail_tag' => 'activity_question',
            ],
            [
                'question' => 'Anna is confident for her driving test. I {a1} her to practise.',
                'answers' => [
                    'a1' => ['answer' => 'have helped', 'verb_hint' => 'help'],
                ],
                'options' => ['have helped', 'have been helping', 'helped', 'was helping'],
                'level' => 'A2',
                'tenses' => ['perfect'],
                'detail_tag' => 'support',
            ],
            [
                'question' => 'It smells nice in here because we {a1} a cake.',
                'answers' => [
                    'a1' => ['answer' => 'have been baking', 'verb_hint' => 'bake'],
                ],
                'options' => ['have baked', 'have been baking', 'baked', 'were baking'],
                'level' => 'B1',
                'tenses' => ['perfect_continuous'],
                'detail_tag' => 'sensory_result',
            ],
            [
                'question' => 'His eyes are red because he {a1} for hours.',
                'answers' => [
                    'a1' => ['answer' => 'has been crying', 'verb_hint' => 'cry'],
                ],
                'options' => ['has cried', 'has been crying', 'cried', 'was crying'],
                'level' => 'B1',
                'tenses' => ['perfect_continuous'],
                'detail_tag' => 'emotion_result',
            ],
        ];

        $service = new QuestionSeedingService();
        $items = [];
        $slug = Str::slug(class_basename(self::class));
        $index = 1;

        foreach ($questions as $question) {
            $uuid = substr($slug, 0, 33 - strlen((string) $index) - 1) . '-' . $index."-ai";

            $answers = [];
            foreach ($question['answers'] as $marker => $answerData) {
                $answers[] = [
                    'marker' => $marker,
                    'answer' => $answerData['answer'],
                    'verb_hint' => $answerData['verb_hint'],
                ];
            }

            $tagIds = [];//[$detailTags[$question['detail_tag']]->id];
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
