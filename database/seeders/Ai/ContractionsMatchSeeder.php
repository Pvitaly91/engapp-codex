<?php

namespace Database\Seeders\Ai;

use App\Models\Category;
use App\Models\Question;
use App\Models\Source;
use App\Models\Tag;
use App\Models\Test;
use Database\Seeders\QuestionSeeder;

class ContractionsMatchSeeder extends QuestionSeeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Contractions Match'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Custom: Contractions Match'])->id;
        $tagId = Tag::firstOrCreate(
            ['name' => 'Contractions'],
            ['category' => 'English Grammar Theme']
        )->id;

        $pairs = [
            ['full' => 'are not', 'short' => "aren't"],
            ['full' => 'cannot', 'short' => "can't"],
            ['full' => 'could not', 'short' => "couldn't"],
            ['full' => 'did not', 'short' => "didn't"],
            ['full' => 'do not', 'short' => "don't"],
            ['full' => 'does not', 'short' => "doesn't"],
            ['full' => 'had not', 'short' => "hadn't"],
            ['full' => 'has not', 'short' => "hasn't"],
            ['full' => 'have not', 'short' => "haven't"],
            ['full' => 'he would', 'short' => "he'd"],
            ['full' => 'he had', 'short' => "he'd (had)"],
            ['full' => 'he is', 'short' => "he's"],
            ['full' => 'he has', 'short' => "he's (has)"],
            ['full' => 'I am', 'short' => "I'm"],
            ['full' => 'I had', 'short' => "I'd"],
            ['full' => 'I would', 'short' => "I'd (would)"],
            ['full' => 'I have', 'short' => "I've"],
            ['full' => 'I will', 'short' => "I'll"],
            ['full' => 'is not', 'short' => "isn't"],
            ['full' => 'it is', 'short' => "it's"],
            ['full' => 'it has', 'short' => "it's (has)"],
            ['full' => 'it will', 'short' => "it'll"],
            ['full' => 'let us', 'short' => "let's"],
            ['full' => 'must not', 'short' => "mustn't"],
            ['full' => 'shall not', 'short' => "shan't"],
            ['full' => 'she would', 'short' => "she'd"],
            ['full' => 'she had', 'short' => "she'd (had)"],
            ['full' => 'she will', 'short' => "she'll"],
            ['full' => 'she is', 'short' => "she's"],
            ['full' => 'she has', 'short' => "she's (has)"],
            ['full' => 'should not', 'short' => "shouldn't"],
            ['full' => 'that is', 'short' => "that's"],
            ['full' => 'there is', 'short' => "there's"],
            ['full' => 'there has', 'short' => "there's (has)"],
            ['full' => 'they are', 'short' => "they're"],
            ['full' => 'they have', 'short' => "they've"],
            ['full' => 'they had', 'short' => "they'd"],
            ['full' => 'they will', 'short' => "they'll"],
            ['full' => 'was not', 'short' => "wasn't"],
            ['full' => 'we are', 'short' => "we're"],
            ['full' => 'we have', 'short' => "we've"],
            ['full' => 'we had', 'short' => "we'd"],
            ['full' => 'we will', 'short' => "we'll"],
            ['full' => 'were not', 'short' => "weren't"],
            ['full' => 'what is', 'short' => "what's"],
            ['full' => 'where is', 'short' => "where's"],
            ['full' => 'who is', 'short' => "who's"],
            ['full' => 'who will', 'short' => "who'll"],
            ['full' => 'why is', 'short' => "why's"],
            ['full' => 'will not', 'short' => "won't"],
            ['full' => 'would not', 'short' => "wouldn't"],
            ['full' => 'you are', 'short' => "you're"],
            ['full' => 'you have', 'short' => "you've"],
            ['full' => 'you had', 'short' => "you'd"],
            ['full' => 'you would', 'short' => "you'd (would)"],
            ['full' => 'you will', 'short' => "you'll"],
        ];

        $options = collect($pairs)
            ->pluck('short')
            ->unique()
            ->values()
            ->all();

        $questionPayloads = [];
        $meta = [];

        foreach ($pairs as $index => $pair) {
            $questionText = $this->titleCase($pair['full']);
            $answer = $pair['short'];
            $uuid = $this->generateQuestionUuid('match', $index + 1, $answer);

            $questionPayloads[] = [
                'uuid' => $uuid,
                'question' => $questionText,
                'category_id' => $categoryId,
                'difficulty' => 2,
                'source_id' => $sourceId,
                'flag' => 2,
                'level' => 'A2',
                'tag_ids' => [$tagId],
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => $answer,
                    ],
                ],
                'options' => $options,
                'variants' => [$questionText],
                'type' => 2,
            ];

            $meta[] = [
                'uuid' => $uuid,
                'hints' => ['Знайди відповідне скорочення.'],
                'answers' => ['a1' => $answer],
                'explanations' => [
                    $answer => sprintf('"%s" скорочується до "%s".', $questionText, $answer),
                ],
                'option_markers' => [
                    $answer => 'a1',
                ],
            ];
        }

        $this->seedQuestionData($questionPayloads, $meta);

        $uuids = array_column($questionPayloads, 'uuid');
        $idMap = Question::whereIn('uuid', $uuids)->pluck('id', 'uuid');
        $questionIds = array_values(array_filter(array_map(
            fn (string $uuid) => $idMap[$uuid] ?? null,
            $uuids
        )));

        Test::updateOrCreate(
            ['slug' => 'contractions-match'],
            [
                'name' => 'Contractions — Match',
                'filters' => ['preferred_view' => 'match'],
                'questions' => $questionIds,
            ]
        );
    }
}
