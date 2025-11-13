<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class IndefinitePronounsPracticeV2Seeder extends QuestionSeeder
{
    private array $levelDifficulty = [
        'A1' => 1,
        'A2' => 2,
        'B1' => 3,
        'B2' => 4,
        'C1' => 5,
        'C2' => 5,
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Pronouns'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Custom: Indefinite Pronouns Practice V2'])->id;

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Indefinite Pronouns Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Something / Anything / Nothing Exercises'],
            ['category' => 'English Grammar Detail']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'Indefinite Pronoun Compounds'],
            ['category' => 'English Grammar Structure']
        )->id;

        $questions = $this->questionEntries();

        $items = [];

        foreach ($questions as $index => $entry) {
            $answers = [];
            foreach ($entry['answers'] as $marker => $answer) {
                $answers[] = [
                    'marker' => $marker,
                    'answer' => $answer,
                    'verb_hint' => null,
                ];
            }

            $options = $this->flattenOptions($entry['options']);

            $uuid = $this->generateQuestionUuid($index + 1, $entry['question']);

            $items[] = [
                'uuid' => $uuid,
                'question' => $entry['question'],
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$entry['level']] ?? 2,
                'source_id' => $sourceId,
                'flag' => 0,
                'type' => 0,
                'level' => $entry['level'],
                'tag_ids' => [$themeTagId, $detailTagId, $structureTagId],
                'answers' => $answers,
                'options' => $options,
                'variants' => [$entry['question']],
            ];
        }

        $this->seedQuestionData($items, []);
    }

    private function questionEntries(): array
    {
        return [
            [
                'question' => "I can't find my keys {a1}.",
                'options' => [
                    'a1' => ['somewhere', 'anywhere', 'nowhere'],
                ],
                'answers' => ['a1' => 'anywhere'],
                'level' => 'A2',
            ],
            [
                'question' => "\"What did you have to drink?\" \"I didn't drink {a1}; only water.\"",
                'options' => [
                    'a1' => ['something', 'anything', 'nothing'],
                ],
                'answers' => ['a1' => 'anything'],
                'level' => 'A2',
            ],
            [
                'question' => '{a1} was at the party; all our friends and family were there.',
                'options' => [
                    'a1' => ['Everybody', 'Anybody', 'Nobody'],
                ],
                'answers' => ['a1' => 'Everybody'],
                'level' => 'A2',
            ],
            [
                'question' => "\"Did you see {a1} interesting at the party?\" \"{a2}. Only boring people.\"",
                'options' => [
                    'a1' => ['somebody', 'anybody', 'nobody'],
                    'a2' => ['somebody', 'nobody', 'anybody'],
                ],
                'answers' => ['a1' => 'anybody', 'a2' => 'nobody'],
                'level' => 'A2',
            ],
            [
                'question' => '{a1} robbed a bank yesterday. They took a lot of money.',
                'options' => [
                    'a1' => ['Somebody', 'Anybody', 'Nobody'],
                ],
                'answers' => ['a1' => 'Somebody'],
                'level' => 'A2',
            ],
            [
                'question' => 'The police think the robber is hiding {a1} in the neighbourhood.',
                'options' => [
                    'a1' => ['somewhere', 'anywhere', 'nowhere'],
                ],
                'answers' => ['a1' => 'somewhere'],
                'level' => 'A2',
            ],
            [
                'question' => "\"Have you eaten {a1}?\" \"{a2}: I'm very hungry.\"",
                'options' => [
                    'a1' => ['something', 'anything', 'nothing'],
                    'a2' => ['something', 'anything', 'nothing'],
                ],
                'answers' => ['a1' => 'anything', 'a2' => 'nothing'],
                'level' => 'A2',
            ],
            [
                'question' => 'Can I stay here tonight? I have {a1} to go.',
                'options' => [
                    'a1' => ['somewhere', 'anywhere', 'nowhere'],
                ],
                'answers' => ['a1' => 'nowhere'],
                'level' => 'A2',
            ],
            [
                'question' => 'I think {a1} bad has happened, because there are police officers {a2}.',
                'options' => [
                    'a1' => ['something', 'anything', 'nothing'],
                    'a2' => ['everywhere', 'anywhere', 'nowhere'],
                ],
                'answers' => ['a1' => 'something', 'a2' => 'everywhere'],
                'level' => 'A2',
            ],
            [
                'question' => '{a1} is big in New York; the streets, the buildings, the cars, even the hamburgers.',
                'options' => [
                    'a1' => ['Something', 'Everything', 'Anything'],
                ],
                'answers' => ['a1' => 'Everything'],
                'level' => 'A2',
            ],
            [
                'question' => '{a1} used my computer yesterday. I need to know who did it.',
                'options' => [
                    'a1' => ['Somebody', 'Nobody', 'Anybody'],
                ],
                'answers' => ['a1' => 'Somebody'],
                'level' => 'A2',
            ],
            [
                'question' => "It happened very quickly and I couldn't see {a1}.",
                'options' => [
                    'a1' => ['anything', 'something', 'nothing'],
                ],
                'answers' => ['a1' => 'anything'],
                'level' => 'A2',
            ],
            [
                'question' => 'Have you talked to {a1} about your problem?',
                'options' => [
                    'a1' => ['somebody', 'anybody', 'nobody'],
                ],
                'answers' => ['a1' => 'anybody'],
                'level' => 'A2',
            ],
            [
                'question' => "I'm bored. I don't have {a1} to do.",
                'options' => [
                    'a1' => ['something', 'anything', 'nothing'],
                ],
                'answers' => ['a1' => 'anything'],
                'level' => 'A2',
            ],
            [
                'question' => 'He lost his house and now he has {a1} to live.',
                'options' => [
                    'a1' => ['somewhere', 'anywhere', 'nowhere'],
                ],
                'answers' => ['a1' => 'nowhere'],
                'level' => 'A2',
            ],
            [
                'question' => "She doesn't have {a1} in her life. She's very lonely.",
                'options' => [
                    'a1' => ['somebody', 'anybody', 'nobody'],
                ],
                'answers' => ['a1' => 'nobody'],
                'level' => 'A2',
            ],
            [
                'question' => 'Would you like {a1} to eat?',
                'options' => [
                    'a1' => ['something', 'anything', 'nothing'],
                ],
                'answers' => ['a1' => 'something'],
                'level' => 'A2',
            ],
            [
                'question' => '"Do you know {a1} in Dublin?" "Yes, I know a few people."',
                'options' => [
                    'a1' => ['someone', 'anyone', 'no one'],
                ],
                'answers' => ['a1' => 'anyone'],
                'level' => 'A2',
            ],
            [
                'question' => 'Sarah told {a1} that she broke up with you. Now we all know.',
                'options' => [
                    'a1' => ['anyone', 'no one', 'everyone'],
                ],
                'answers' => ['a1' => 'no one'],
                'level' => 'A2',
            ],
            [
                'question' => "I'm going to bed. There's {a1} interesting on TV.",
                'options' => [
                    'a1' => ['something', 'anything', 'nothing'],
                ],
                'answers' => ['a1' => 'nothing'],
                'level' => 'A2',
            ],
            [
                'question' => "We have looked for Mike but we can't find him {a1}.",
                'options' => [
                    'a1' => ['anywhere', 'nowhere', 'somewhere'],
                ],
                'answers' => ['a1' => 'anywhere'],
                'level' => 'A2',
            ],
            [
                'question' => '{a1} called you this morning, but I do not know who.',
                'options' => [
                    'a1' => ['Someone', 'Anybody', 'No one'],
                ],
                'answers' => ['a1' => 'Someone'],
                'level' => 'A2',
            ],
            [
                'question' => "I didn't go {a1} yesterday. I stayed home all day.",
                'options' => [
                    'a1' => ['anywhere', 'somewhere', 'nowhere'],
                ],
                'answers' => ['a1' => 'anywhere'],
                'level' => 'A2',
            ],
            [
                'question' => "I don't know {a1} in the class yet, but I know most of them.",
                'options' => [
                    'a1' => ['anyone', 'someone', 'everyone'],
                ],
                'answers' => ['a1' => 'anyone'],
                'level' => 'A2',
            ],
            [
                'question' => "I'm sorry but I can't help you. I don't know {a1} about Napoleon.",
                'options' => [
                    'a1' => ['anything', 'something', 'nothing'],
                ],
                'answers' => ['a1' => 'anything'],
                'level' => 'A2',
            ],
            [
                'question' => "He's behaving very strangely. {a1} is wrong with him, but I don't know what.",
                'options' => [
                    'a1' => ['Something', 'Anything', 'Nothing'],
                ],
                'answers' => ['a1' => 'Something'],
                'level' => 'A2',
            ],
            [
                'question' => '{a1} was there when I arrived. I was the only person there.',
                'options' => [
                    'a1' => ['Nobody', 'Anybody', 'Everyone'],
                ],
                'answers' => ['a1' => 'Nobody'],
                'level' => 'A2',
            ],
            [
                'question' => "Have you seen my wallet? I can't find it {a1}.",
                'options' => [
                    'a1' => ['anywhere', 'nowhere', 'everywhere'],
                ],
                'answers' => ['a1' => 'anywhere'],
                'level' => 'A2',
            ],
            [
                'question' => 'We lost, so there is {a1} to celebrate today. Let\'s go home.',
                'options' => [
                    'a1' => ['nothing', 'anything', 'everything'],
                ],
                'answers' => ['a1' => 'nothing'],
                'level' => 'A2',
            ],
            [
                'question' => 'The police thought they were hiding {a1} in the house, but they did not find {a2} hiding there.',
                'options' => [
                    'a1' => ['anyone', 'someone', 'no one'],
                    'a2' => ['anyone', 'someone', 'no one'],
                ],
                'answers' => ['a1' => 'anyone', 'a2' => 'no one'],
                'level' => 'A2',
            ],
        ];
    }

    private function flattenOptions(array $optionSets): array
    {
        $values = [];
        foreach ($optionSets as $options) {
            foreach ($options as $option) {
                $values[] = (string) $option;
            }
        }

        return array_values(array_unique($values));
    }
}
