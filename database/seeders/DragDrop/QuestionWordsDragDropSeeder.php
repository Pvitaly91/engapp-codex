<?php

namespace Database\Seeders\DragDrop;

use App\Models\Category;
use App\Models\Question;
use App\Models\Source;
use App\Models\Tag;
use App\Models\Test;
use App\Services\QuestionSeedingService;
use App\Support\Database\Seeder;
use Illuminate\Support\Str;

class QuestionWordsDragDropSeeder extends Seeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'question_words'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Question Words — Drag & Drop'])->id;
        $tag = Tag::firstOrCreate(['name' => 'question_words_drag_drop']);

        $service = new QuestionSeedingService();

        $questions = [
            ['prompt' => '{a1} is your hobby?', 'tail' => '– Drawing', 'answer' => 'What'],
            ['prompt' => '{a1} did you live last year?', 'tail' => '– In London', 'answer' => 'Where'],
            ['prompt' => '{a1} are you late?', 'tail' => '– I’ve missed my bus.', 'answer' => 'Why'],
            ['prompt' => '{a1} lessons do you have?', 'tail' => '– Six lessons', 'answer' => 'How many'],
            ['prompt' => '{a1} is that man at the door?', 'tail' => '– My uncle.', 'answer' => 'Who'],
            ['prompt' => '{a1} do you go to the club?', 'tail' => '– At 6 o’clock', 'answer' => 'When'],
            ['prompt' => '{a1} did you feel yesterday?', 'tail' => '– Awfully', 'answer' => 'How'],
            ['prompt' => '{a1} is your sister?', 'tail' => '– She is eleven.', 'answer' => 'How old'],
            ['prompt' => '{a1} are you crying?', 'tail' => '– I’ve lost my keys.', 'answer' => 'Why'],
            ['prompt' => '{a1} will you return?', 'tail' => '– In two days', 'answer' => 'When'],
            ['prompt' => '{a1} books have you bought?', 'tail' => '– Three books', 'answer' => 'How many'],
            ['prompt' => '{a1} is your dad?', 'tail' => '– He is 45.', 'answer' => 'How old'],
            ['prompt' => '{a1} will the concert start?', 'tail' => '– At seven p.m.', 'answer' => 'When'],
            ['prompt' => '{a1} is playing with the dog?', 'tail' => '– My friend Tom', 'answer' => 'Who'],
            ['prompt' => '{a1} is the kitten?', 'tail' => '– Under the table', 'answer' => 'Where'],
            ['prompt' => '{a1} book is on the table?', 'tail' => '– It’s mine.', 'answer' => 'Whose'],
            ['prompt' => '{a1} will you get to London?', 'tail' => '– By car', 'answer' => 'How'],
            ['prompt' => '{a1} do you do in the evening?', 'tail' => '– I usually watch TV.', 'answer' => 'What'],
            ['prompt' => '{a1} friends do you have?', 'tail' => '– I have a lot of friends.', 'answer' => 'How many'],
            ['prompt' => '{a1} is the tea?', 'tail' => '– It’s 50p.', 'answer' => 'How much'],
            ['prompt' => '{a1} cat is on the tree?', 'tail' => '– It’s Mona’s cat.', 'answer' => 'Whose'],
            ['prompt' => '{a1} sports do you like?', 'tail' => '– I like basketball.', 'answer' => 'What'],
            ['prompt' => '{a1} are your parents?', 'tail' => '– They are in the shop.', 'answer' => 'Where'],
            ['prompt' => '{a1} swims faster: you or Alec?', 'tail' => '– Alec swims faster.', 'answer' => 'Who'],
            ['prompt' => '{a1} is your new car?', 'tail' => '– It’s very expensive.', 'answer' => 'How much'],
            ['prompt' => '{a1} will you spend your holiday?', 'tail' => '– In Greece, I think.', 'answer' => 'Where'],
            ['prompt' => '{a1} will you go to Paris?', 'tail' => '– By plane.', 'answer' => 'How'],
            ['prompt' => '{a1} bag is it?', 'tail' => '– It’s Tom’s bag.', 'answer' => 'Whose'],
        ];

        $items = [];
        foreach ($questions as $index => $q) {
            $position = $index + 1;
            $slug = Str::slug(class_basename(self::class));
            $max = 36 - strlen((string) $position) - 1;
            $uuid = substr($slug, 0, max($max, 4)) . '-' . $position;

            $questionText = $q['prompt'] . "\n" . $q['tail'];

            $items[] = [
                'uuid' => $uuid,
                'question' => $questionText,
                'difficulty' => 1,
                'category_id' => $categoryId,
                'source_id' => $sourceId,
                'flag' => 0,
                'tag_ids' => [$tag->id],
                'answers' => [
                    ['marker' => 'a1', 'answer' => $q['answer']],
                ],
                'options' => [],
                'seeder' => static::class,
            ];
        }

        $service->seed($items);

        $uuids = array_column($items, 'uuid');
        $idMap = Question::whereIn('uuid', $uuids)->pluck('id', 'uuid');
        $questionIds = array_values(array_filter(array_map(fn ($uuid) => $idMap[$uuid] ?? null, $uuids)));

        Test::updateOrCreate(
            ['slug' => 'question-words-drag-drop'],
            [
                'name' => 'Question Words — Drag & Drop',
                'filters' => ['preferred_view' => 'drag-drop'],
                'questions' => $questionIds,
            ]
        );
    }
}
