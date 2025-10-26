<?php

namespace Database\Seeders\V1\Questions;

use App\Support\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class ShortAnswersSeeder extends Seeder
{

    public function run()
    {
        $cat_present = Category::firstOrCreate(['name' => 'present'])->id;
        $cat_past = Category::firstOrCreate(['name' => 'past'])->id;
        $cat_future = Category::firstOrCreate(['name' => 'Future'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Short answers. Answer the questions with short answers.'])->id;

        $themeTag = Tag::firstOrCreate(['name' => 'short_answers']);

        $data = [
            [
                'question' => 'Can you speak French? Yes, {a1} No, {a2}',
                'difficulty' => 1,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'I can.', 'verb_hint' => 'can'],
                    ['marker' => 'a2', 'answer' => "I can't.", 'verb_hint' => 'can'],
                ],
            ],
            [
                'question' => 'Are they eating cheese? Yes, {a1} No, {a2}',
                'difficulty' => 1,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'They are.', 'verb_hint' => 'be'],
                    ['marker' => 'a2', 'answer' => "They aren\'t.", 'verb_hint' => 'be'],
                ],
            ],
            [
                'question' => 'Could your sister swim last year? Yes, {a1} No, {a2}',
                'difficulty' => 2,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'She could.', 'verb_hint' => 'can'],
                    ['marker' => 'a2', 'answer' => "She couldn\'t.", 'verb_hint' => 'can'],
                ],
            ],
            [
                'question' => 'Do you go to school every day? Yes, {a1} No, {a2}',
                'difficulty' => 1,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'I do.', 'verb_hint' => 'do'],
                    ['marker' => 'a2', 'answer' => "I don't.", 'verb_hint' => 'do'],
                ],
            ],
            [
                'question' => 'Are you happy? Yes, {a1} No, {a2}',
                'difficulty' => 1,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'I am.', 'verb_hint' => 'be'],
                    ['marker' => 'a2', 'answer' => "I'm not.", 'verb_hint' => 'be'],
                ],
            ],
            [
                'question' => 'Does a cat drink milk? Yes, {a1} No, {a2}',
                'difficulty' => 1,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'It does.', 'verb_hint' => 'do'],
                    ['marker' => 'a2', 'answer' => "It doesn't.", 'verb_hint' => 'do'],
                ],
            ],
            [
                'question' => 'Was this table green? Yes, {a1} No, {a2}',
                'difficulty' => 1,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'It was.', 'verb_hint' => 'be'],
                    ['marker' => 'a2', 'answer' => "It wasn't.", 'verb_hint' => 'be'],
                ],
            ],
            [
                'question' => 'Is Tom wearing a blue tie? Yes, {a1} No, {a2}',
                'difficulty' => 1,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'He is.', 'verb_hint' => 'be'],
                    ['marker' => 'a2', 'answer' => "He isn't.", 'verb_hint' => 'be'],
                ],
            ],
            [
                'question' => 'Have they got a new pet? Yes, {a1} No, {a2}',
                'difficulty' => 2,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'They have.', 'verb_hint' => 'have'],
                    ['marker' => 'a2', 'answer' => "They haven't.", 'verb_hint' => 'have'],
                ],
            ],
            [
                'question' => 'Does your aunt work? Yes, {a1} No, {a2}',
                'difficulty' => 1,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'She does.', 'verb_hint' => 'do'],
                    ['marker' => 'a2', 'answer' => "She doesn't.", 'verb_hint' => 'do'],
                ],
            ],
            [
                'question' => 'Did she live in Madrid? Yes, {a1} No, {a2}',
                'difficulty' => 1,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'She did.', 'verb_hint' => 'do'],
                    ['marker' => 'a2', 'answer' => "She didn't.", 'verb_hint' => 'do'],
                ],
            ],
            [
                'question' => 'Are your parents doctors? Yes, {a1} No, {a2}',
                'difficulty' => 1,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'They are.', 'verb_hint' => 'be'],
                    ['marker' => 'a2', 'answer' => "They aren't.", 'verb_hint' => 'be'],
                ],
            ],
            [
                'question' => 'Can a dolphin swim? Yes, {a1} No, {a2}',
                'difficulty' => 1,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'It can.', 'verb_hint' => 'can'],
                    ['marker' => 'a2', 'answer' => "It can't.", 'verb_hint' => 'can'],
                ],
            ],
            [
                'question' => 'Was that dog eating bones? Yes, {a1} No, {a2}',
                'difficulty' => 1,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'It was.', 'verb_hint' => 'be'],
                    ['marker' => 'a2', 'answer' => "It wasn't.", 'verb_hint' => 'be'],
                ],
            ],
            [
                'question' => 'Could you open the window? Yes, {a1} No, {a2}',
                'difficulty' => 2,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'I could.', 'verb_hint' => 'can'],
                    ['marker' => 'a2', 'answer' => "I couldn't.", 'verb_hint' => 'can'],
                ],
            ],
            [
                'question' => 'Has he got a small car? Yes, {a1} No, {a2}',
                'difficulty' => 2,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'He has.', 'verb_hint' => 'have'],
                    ['marker' => 'a2', 'answer' => "He hasn't.", 'verb_hint' => 'have'],
                ],
            ],
            [
                'question' => 'Do you like Coke? Yes, {a1} No, {a2}',
                'difficulty' => 1,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'I do.', 'verb_hint' => 'do'],
                    ['marker' => 'a2', 'answer' => "I don't.", 'verb_hint' => 'do'],
                ],
            ],
            [
                'question' => 'Can Anna play the piano? Yes, {a1} No, {a2}',
                'difficulty' => 2,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'She can.', 'verb_hint' => 'can'],
                    ['marker' => 'a2', 'answer' => "She can't.", 'verb_hint' => 'can'],
                ],
            ],
            [
                'question' => 'Are we writing an email? Yes, {a1} No, {a2}',
                'difficulty' => 2,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'We are.', 'verb_hint' => 'be'],
                    ['marker' => 'a2', 'answer' => "We aren't.", 'verb_hint' => 'be'],
                ],
            ],
            [
                'question' => 'Do you want to go to the cinema? Yes, {a1} No, {a2}',
                'difficulty' => 1,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'I do.', 'verb_hint' => 'do'],
                    ['marker' => 'a2', 'answer' => "I don't.", 'verb_hint' => 'do'],
                ],
            ],
            [
                'question' => 'Did Peter study at university? Yes, {a1} No, {a2}',
                'difficulty' => 2,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'He did.', 'verb_hint' => 'do'],
                    ['marker' => 'a2', 'answer' => "He didn't.", 'verb_hint' => 'do'],
                ],
            ],
            [
                'question' => 'Was she hungry? Yes, {a1} No, {a2}',
                'difficulty' => 1,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'She was.', 'verb_hint' => 'be'],
                    ['marker' => 'a2', 'answer' => "She wasn't.", 'verb_hint' => 'be'],
                ],
            ],
            [
                'question' => 'Does George like onions? Yes, {a1} No, {a2}',
                'difficulty' => 1,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'He does.', 'verb_hint' => 'do'],
                    ['marker' => 'a2', 'answer' => "He doesn't.", 'verb_hint' => 'do'],
                ],
            ],
            [
                'question' => 'Was Linda watching the news? Yes, {a1} No, {a2}',
                'difficulty' => 2,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'She was.', 'verb_hint' => 'be'],
                    ['marker' => 'a2', 'answer' => "She wasn't.", 'verb_hint' => 'be'],
                ],
            ],
        ];

        $service = new QuestionSeedingService();
        $items = [];
        foreach ($data as $i => $d) {
            $index = $i + 1;
            $slug  = Str::slug(class_basename(self::class));
            $max   = 36 - strlen((string) $index) - 1;
            $uuid  = substr($slug, 0, $max) . '-' . $index;

           $d['uuid'] = $uuid;
            $d['tag_ids'] = [$themeTag->id];
            $items[]   = $d;
        }

        $service->seed($items);
    }
}
