<?php

namespace Database\Seeders\AI\Claude;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class QuantifiersPracticeAIGeneratedSeeder extends QuestionSeeder
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
        $categoryId = Category::firstOrCreate(['name' => 'Quantifiers'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'AI generated: Quantifiers Practice: Much / Many / A lot / Few / Little (SET 1)'])->id;
        $tagIds = $this->buildTags();
        $questions = $this->questionEntries();

        $items = [];
        $meta = [];
        foreach ($questions as $index => $entry) {
            $answers = [];
            foreach ($entry['answers'] as $marker => $answer) {
                $answers[] = [
                    'marker' => $marker,
                    'answer' => $answer,
                    'verb_hint' => $entry['verb_hints'][$marker] ?? null,
                ];
            }

            $options = $this->flattenOptions($entry['options']);
            $uuid = $this->generateQuestionUuid($entry['level'], $index + 1, $entry['question']);
            $questionTagIds = array_merge($tagIds, $entry['tag_ids'] ?? []);

            $items[] = [
                'uuid' => $uuid,
                'question' => $entry['question'],
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$entry['level']] ?? 3,
                'source_id' => $sourceId,
                'flag' => 2,
                'type' => 0,
                'level' => $entry['level'],
                'tag_ids' => $questionTagIds,
                'answers' => $answers,
                'options' => $options,
                'variants' => [$entry['question']],
            ];

            $meta[] = [
                'uuid' => $uuid,
                'hints' => $entry['hints'] ?? [],
                'answers' => $entry['answers'],
                'option_markers' => $this->buildOptionMarkers($entry['options']),
                'explanations' => $this->buildExplanations($entry['options'], $entry['answers'], $entry['level']),
            ];
        }

        $this->seedQuestionData($items, $meta);
    }

    private function buildTags(): array
    {
        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Quantifiers Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Much / Many / A lot / Few / Little'],
            ['category' => 'English Grammar Detail']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'Quantifier Choice'],
            ['category' => 'English Grammar Structure']
        )->id;

        return [$themeTagId, $detailTagId, $structureTagId];
    }

    private function questionEntries(): array
    {
        $affirmativeTagId = Tag::firstOrCreate(['name' => 'Affirmative Sentence'], ['category' => 'Sentence Type'])->id;
        $negativeTagId = Tag::firstOrCreate(['name' => 'Negative Sentence'], ['category' => 'Sentence Type'])->id;
        $interrogativeTagId = Tag::firstOrCreate(['name' => 'Interrogative Sentence'], ['category' => 'Sentence Type'])->id;
        $countableTagId = Tag::firstOrCreate(['name' => 'Countable Nouns'], ['category' => 'Noun Type'])->id;
        $uncountableTagId = Tag::firstOrCreate(['name' => 'Uncountable Nouns'], ['category' => 'Noun Type'])->id;

        return [
            // ===== A1 Level: 12 questions =====
            [
                'level' => 'A1',
                'question' => 'I have {a1} friends at school.',
                'answers' => ['a1' => 'many'],
                'options' => ['a1' => ['many', 'much', 'a little']],
                'verb_hints' => ['a1' => 'countable plural noun'],
                'hints' => [
                    'Many вживається зі злічуваними іменниками у множині.',
                    'Friends — злічуваний іменник.',
                    'Приклад: I have many books.',
                ],
                'tag_ids' => [$affirmativeTagId, $countableTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'There is {a1} water in the glass.',
                'answers' => ['a1' => 'a little'],
                'options' => ['a1' => ['a little', 'a few', 'many']],
                'verb_hints' => ['a1' => 'uncountable noun'],
                'hints' => [
                    'A little вживається з незлічуваними іменниками.',
                    'Water — незлічуваний іменник.',
                    'Приклад: There is a little milk in the cup.',
                ],
                'tag_ids' => [$affirmativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'She eats {a1} fruit every day.',
                'answers' => ['a1' => 'a lot of'],
                'options' => ['a1' => ['a lot of', 'many', 'few']],
                'verb_hints' => ['a1' => 'large quantity'],
                'hints' => [
                    'A lot of вживається з будь-якими іменниками у стверджувальних реченнях.',
                    'Fruit може бути незлічуваним у загальному значенні.',
                    'Приклад: She drinks a lot of juice.',
                ],
                'tag_ids' => [$affirmativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'We have {a1} eggs in the fridge.',
                'answers' => ['a1' => 'a few'],
                'options' => ['a1' => ['a few', 'a little', 'much']],
                'verb_hints' => ['a1' => 'countable plural noun'],
                'hints' => [
                    'A few вживається зі злічуваними іменниками у множині.',
                    'Eggs — злічуваний іменник.',
                    'Приклад: I have a few apples.',
                ],
                'tag_ids' => [$affirmativeTagId, $countableTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'Do you have {a1} money?',
                'answers' => ['a1' => 'much'],
                'options' => ['a1' => ['much', 'many', 'few']],
                'verb_hints' => ['a1' => 'uncountable in question'],
                'hints' => [
                    'Much вживається з незлічуваними іменниками у питаннях.',
                    'Money — незлічуваний іменник.',
                    'Приклад: Do you have much time?',
                ],
                'tag_ids' => [$interrogativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'There are not {a1} cars in the street.',
                'answers' => ['a1' => 'many'],
                'options' => ['a1' => ['many', 'much', 'a little']],
                'verb_hints' => ['a1' => 'countable in negative'],
                'hints' => [
                    'Many вживається зі злічуваними іменниками у запереченнях.',
                    'Cars — злічуваний іменник у множині.',
                    'Приклад: There are not many people here.',
                ],
                'tag_ids' => [$negativeTagId, $countableTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'My sister has {a1} dolls.',
                'answers' => ['a1' => 'a lot of'],
                'options' => ['a1' => ['a lot of', 'much', 'a little']],
                'verb_hints' => ['a1' => 'large quantity countable'],
                'hints' => [
                    'A lot of вживається зі злічуваними іменниками для великої кількості.',
                    'Dolls — злічуваний іменник у множині.',
                    'Приклад: He has a lot of toys.',
                ],
                'tag_ids' => [$affirmativeTagId, $countableTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'How {a1} sugar do you want?',
                'answers' => ['a1' => 'much'],
                'options' => ['a1' => ['much', 'many', 'few']],
                'verb_hints' => ['a1' => 'How + uncountable'],
                'hints' => [
                    'How much вживається з незлічуваними іменниками.',
                    'Sugar — незлічуваний іменник.',
                    'Приклад: How much coffee do you drink?',
                ],
                'tag_ids' => [$interrogativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'I drink {a1} coffee in the morning.',
                'answers' => ['a1' => 'a little'],
                'options' => ['a1' => ['a little', 'a few', 'many']],
                'verb_hints' => ['a1' => 'small quantity uncountable'],
                'hints' => [
                    'A little означає невелику кількість незлічуваного.',
                    'Coffee — незлічуваний іменник.',
                    'Приклад: I need a little help.',
                ],
                'tag_ids' => [$affirmativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'How {a1} books are on the table?',
                'answers' => ['a1' => 'many'],
                'options' => ['a1' => ['many', 'much', 'little']],
                'verb_hints' => ['a1' => 'How + countable'],
                'hints' => [
                    'How many вживається зі злічуваними іменниками.',
                    'Books — злічуваний іменник у множині.',
                    'Приклад: How many students are in the class?',
                ],
                'tag_ids' => [$interrogativeTagId, $countableTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'He does not have {a1} time today.',
                'answers' => ['a1' => 'much'],
                'options' => ['a1' => ['much', 'many', 'few']],
                'verb_hints' => ['a1' => 'uncountable in negative'],
                'hints' => [
                    'Much вживається з незлічуваними іменниками у запереченнях.',
                    'Time — незлічуваний іменник.',
                    'Приклад: I do not have much patience.',
                ],
                'tag_ids' => [$negativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'A1',
                'question' => 'The children ate {a1} cookies.',
                'answers' => ['a1' => 'a lot of'],
                'options' => ['a1' => ['a lot of', 'much', 'a little']],
                'verb_hints' => ['a1' => 'large quantity'],
                'hints' => [
                    'A lot of вказує на велику кількість.',
                    'Cookies — злічуваний іменник у множині.',
                    'Приклад: They bought a lot of presents.',
                ],
                'tag_ids' => [$affirmativeTagId, $countableTagId],
            ],

            // ===== A2 Level: 12 questions =====
            [
                'level' => 'A2',
                'question' => 'There was {a1} traffic on the road this morning.',
                'answers' => ['a1' => 'a lot of'],
                'options' => ['a1' => ['a lot of', 'many', 'a few']],
                'verb_hints' => ['a1' => 'uncountable large quantity'],
                'hints' => [
                    'A lot of вживається з незлічуваними іменниками у стверджувальних реченнях.',
                    'Traffic — незлічуваний іменник.',
                    'Приклад: There was a lot of noise outside.',
                ],
                'tag_ids' => [$affirmativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'Only {a1} students passed the exam.',
                'answers' => ['a1' => 'a few'],
                'options' => ['a1' => ['a few', 'a little', 'much']],
                'verb_hints' => ['a1' => 'small quantity countable'],
                'hints' => [
                    'A few означає невелику кількість злічуваних іменників.',
                    'Students — злічуваний іменник у множині.',
                    'Приклад: A few people came to the party.',
                ],
                'tag_ids' => [$affirmativeTagId, $countableTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'She spent {a1} money on clothes last month.',
                'answers' => ['a1' => 'a lot of'],
                'options' => ['a1' => ['a lot of', 'many', 'few']],
                'verb_hints' => ['a1' => 'large quantity uncountable'],
                'hints' => [
                    'A lot of вживається для великої кількості.',
                    'Money — незлічуваний іменник.',
                    'Приклад: He earns a lot of money.',
                ],
                'tag_ids' => [$affirmativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'Were there {a1} people at the concert?',
                'answers' => ['a1' => 'many'],
                'options' => ['a1' => ['many', 'much', 'a little']],
                'verb_hints' => ['a1' => 'countable in question'],
                'hints' => [
                    'Many вживається зі злічуваними іменниками у питаннях.',
                    'People — злічуваний іменник.',
                    'Приклад: Were there many cars in the parking lot?',
                ],
                'tag_ids' => [$interrogativeTagId, $countableTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'I have {a1} free time on weekends.',
                'answers' => ['a1' => 'little'],
                'options' => ['a1' => ['little', 'few', 'many']],
                'verb_hints' => ['a1' => 'almost none uncountable'],
                'hints' => [
                    'Little (без артикля a) означає майже нічого.',
                    'Time — незлічуваний іменник.',
                    'Приклад: There is little hope left.',
                ],
                'tag_ids' => [$affirmativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'There are {a1} good restaurants in this town.',
                'answers' => ['a1' => 'few'],
                'options' => ['a1' => ['few', 'little', 'much']],
                'verb_hints' => ['a1' => 'almost none countable'],
                'hints' => [
                    'Few (без артикля a) означає майже нічого зі злічуваними.',
                    'Restaurants — злічуваний іменник у множині.',
                    'Приклад: Few students understood the lesson.',
                ],
                'tag_ids' => [$affirmativeTagId, $countableTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'Did you make {a1} mistakes in the test?',
                'answers' => ['a1' => 'many'],
                'options' => ['a1' => ['many', 'much', 'a little']],
                'verb_hints' => ['a1' => 'countable in question'],
                'hints' => [
                    'Many вживається зі злічуваними іменниками у питаннях.',
                    'Mistakes — злічуваний іменник у множині.',
                    'Приклад: Did you take many photos?',
                ],
                'tag_ids' => [$interrogativeTagId, $countableTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'We need {a1} more information about the project.',
                'answers' => ['a1' => 'a little'],
                'options' => ['a1' => ['a little', 'a few', 'many']],
                'verb_hints' => ['a1' => 'some more uncountable'],
                'hints' => [
                    'A little вживається з незлічуваними іменниками.',
                    'Information — незлічуваний іменник.',
                    'Приклад: I need a little advice.',
                ],
                'tag_ids' => [$affirmativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'They did not receive {a1} complaints last year.',
                'answers' => ['a1' => 'many'],
                'options' => ['a1' => ['many', 'much', 'a little']],
                'verb_hints' => ['a1' => 'countable in negative'],
                'hints' => [
                    'Many вживається зі злічуваними у запереченнях.',
                    'Complaints — злічуваний іменник у множині.',
                    'Приклад: We did not get many replies.',
                ],
                'tag_ids' => [$negativeTagId, $countableTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'Is there {a1} milk left in the carton?',
                'answers' => ['a1' => 'much'],
                'options' => ['a1' => ['much', 'many', 'few']],
                'verb_hints' => ['a1' => 'uncountable in question'],
                'hints' => [
                    'Much вживається з незлічуваними у питаннях.',
                    'Milk — незлічуваний іменник.',
                    'Приклад: Is there much bread?',
                ],
                'tag_ids' => [$interrogativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'She made {a1} progress in learning Spanish.',
                'answers' => ['a1' => 'a lot of'],
                'options' => ['a1' => ['a lot of', 'many', 'few']],
                'verb_hints' => ['a1' => 'large quantity uncountable'],
                'hints' => [
                    'A lot of вживається з незлічуваними у стверджувальних реченнях.',
                    'Progress — незлічуваний іменник.',
                    'Приклад: They made a lot of effort.',
                ],
                'tag_ids' => [$affirmativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'A2',
                'question' => 'Can I have {a1} more cookies, please?',
                'answers' => ['a1' => 'a few'],
                'options' => ['a1' => ['a few', 'a little', 'much']],
                'verb_hints' => ['a1' => 'some more countable'],
                'hints' => [
                    'A few вживається зі злічуваними іменниками.',
                    'Cookies — злічуваний іменник у множині.',
                    'Приклад: Can I borrow a few pencils?',
                ],
                'tag_ids' => [$interrogativeTagId, $countableTagId],
            ],

            // ===== B1 Level: 12 questions =====
            [
                'level' => 'B1',
                'question' => 'The company has invested {a1} money in new technology.',
                'answers' => ['a1' => 'a great deal of'],
                'options' => ['a1' => ['a great deal of', 'many', 'few']],
                'verb_hints' => ['a1' => 'large quantity formal'],
                'hints' => [
                    'A great deal of — формальний варіант a lot of для незлічуваних.',
                    'Money — незлічуваний іменник.',
                    'Приклад: She has a great deal of experience.',
                ],
                'tag_ids' => [$affirmativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'There were {a1} opportunities for career advancement.',
                'answers' => ['a1' => 'few'],
                'options' => ['a1' => ['few', 'little', 'much']],
                'verb_hints' => ['a1' => 'almost none countable'],
                'hints' => [
                    'Few (без a) означає недостатньо, майже нічого.',
                    'Opportunities — злічуваний іменник у множині.',
                    'Приклад: Few people know about this place.',
                ],
                'tag_ids' => [$affirmativeTagId, $countableTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'She showed {a1} interest in the proposal.',
                'answers' => ['a1' => 'little'],
                'options' => ['a1' => ['little', 'few', 'many']],
                'verb_hints' => ['a1' => 'almost none uncountable'],
                'hints' => [
                    'Little (без a) означає недостатньо з незлічуваними.',
                    'Interest — незлічуваний іменник.',
                    'Приклад: He paid little attention to the warning.',
                ],
                'tag_ids' => [$affirmativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'Have you had {a1} experience with this software?',
                'answers' => ['a1' => 'much'],
                'options' => ['a1' => ['much', 'many', 'few']],
                'verb_hints' => ['a1' => 'uncountable in question'],
                'hints' => [
                    'Much вживається з незлічуваними у питаннях.',
                    'Experience — незлічуваний іменник.',
                    'Приклад: Have you had much contact with the team?',
                ],
                'tag_ids' => [$interrogativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The team faces {a1} challenges this quarter.',
                'answers' => ['a1' => 'a number of'],
                'options' => ['a1' => ['a number of', 'much', 'a little']],
                'verb_hints' => ['a1' => 'several countable'],
                'hints' => [
                    'A number of означає кілька зі злічуваними.',
                    'Challenges — злічуваний іменник у множині.',
                    'Приклад: A number of issues were raised.',
                ],
                'tag_ids' => [$affirmativeTagId, $countableTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'We did not encounter {a1} resistance from the local community.',
                'answers' => ['a1' => 'much'],
                'options' => ['a1' => ['much', 'many', 'few']],
                'verb_hints' => ['a1' => 'uncountable in negative'],
                'hints' => [
                    'Much вживається з незлічуваними у запереченнях.',
                    'Resistance — незлічуваний іменник.',
                    'Приклад: We did not face much opposition.',
                ],
                'tag_ids' => [$negativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The report contains {a1} useful data.',
                'answers' => ['a1' => 'a lot of'],
                'options' => ['a1' => ['a lot of', 'many', 'few']],
                'verb_hints' => ['a1' => 'large quantity'],
                'hints' => [
                    'A lot of вживається з незлічуваними у стверджувальних реченнях.',
                    'Data — незлічуваний іменник у цьому контексті.',
                    'Приклад: The book contains a lot of information.',
                ],
                'tag_ids' => [$affirmativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'How {a1} evidence do you have to support your claim?',
                'answers' => ['a1' => 'much'],
                'options' => ['a1' => ['much', 'many', 'few']],
                'verb_hints' => ['a1' => 'How + uncountable'],
                'hints' => [
                    'How much вживається з незлічуваними іменниками.',
                    'Evidence — незлічуваний іменник.',
                    'Приклад: How much proof is there?',
                ],
                'tag_ids' => [$interrogativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'They received {a1} applications for the position.',
                'answers' => ['a1' => 'a large number of'],
                'options' => ['a1' => ['a large number of', 'much', 'a little']],
                'verb_hints' => ['a1' => 'many formal'],
                'hints' => [
                    'A large number of — формальний варіант many.',
                    'Applications — злічуваний іменник у множині.',
                    'Приклад: A large number of people attended.',
                ],
                'tag_ids' => [$affirmativeTagId, $countableTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'There has been {a1} improvement in her condition.',
                'answers' => ['a1' => 'little'],
                'options' => ['a1' => ['little', 'few', 'many']],
                'verb_hints' => ['a1' => 'almost none uncountable'],
                'hints' => [
                    'Little (без a) означає майже ніякого покращення.',
                    'Improvement — незлічуваний іменник.',
                    'Приклад: There was little change in the results.',
                ],
                'tag_ids' => [$affirmativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'Did the survey reveal {a1} problems with the product?',
                'answers' => ['a1' => 'many'],
                'options' => ['a1' => ['many', 'much', 'a little']],
                'verb_hints' => ['a1' => 'countable in question'],
                'hints' => [
                    'Many вживається зі злічуваними у питаннях.',
                    'Problems — злічуваний іменник у множині.',
                    'Приклад: Did you find many errors?',
                ],
                'tag_ids' => [$interrogativeTagId, $countableTagId],
            ],
            [
                'level' => 'B1',
                'question' => 'The manager gave us {a1} advice on the project.',
                'answers' => ['a1' => 'a lot of'],
                'options' => ['a1' => ['a lot of', 'many', 'few']],
                'verb_hints' => ['a1' => 'large quantity uncountable'],
                'hints' => [
                    'A lot of вживається з незлічуваними.',
                    'Advice — незлічуваний іменник.',
                    'Приклад: She gave me a lot of help.',
                ],
                'tag_ids' => [$affirmativeTagId, $uncountableTagId],
            ],

            // ===== B2 Level: 12 questions =====
            [
                'level' => 'B2',
                'question' => 'The research yielded {a1} significant findings.',
                'answers' => ['a1' => 'a considerable number of'],
                'options' => ['a1' => ['a considerable number of', 'much', 'a little']],
                'verb_hints' => ['a1' => 'formal many'],
                'hints' => [
                    'A considerable number of — формальний вираз для багатьох злічуваних.',
                    'Findings — злічуваний іменник у множині.',
                    'Приклад: A considerable number of participants agreed.',
                ],
                'tag_ids' => [$affirmativeTagId, $countableTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'There is {a1} evidence to suggest a correlation.',
                'answers' => ['a1' => 'sufficient'],
                'options' => ['a1' => ['sufficient', 'many', 'few']],
                'verb_hints' => ['a1' => 'enough uncountable'],
                'hints' => [
                    'Sufficient означає достатньо з незлічуваними.',
                    'Evidence — незлічуваний іменник.',
                    'Приклад: There is sufficient data to proceed.',
                ],
                'tag_ids' => [$affirmativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The organization allocates {a1} resources to marketing.',
                'answers' => ['a1' => 'considerable'],
                'options' => ['a1' => ['considerable', 'few', 'little']],
                'verb_hints' => ['a1' => 'large amount formal'],
                'hints' => [
                    'Considerable означає значну кількість.',
                    'Resources — може бути злічуваним або незлічуваним.',
                    'Приклад: They invested considerable effort.',
                ],
                'tag_ids' => [$affirmativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Were there {a1} participants in the clinical trial?',
                'answers' => ['a1' => 'enough'],
                'options' => ['a1' => ['enough', 'much', 'a little']],
                'verb_hints' => ['a1' => 'sufficient countable'],
                'hints' => [
                    'Enough вживається з будь-якими іменниками.',
                    'Participants — злічуваний іменник у множині.',
                    'Приклад: Were there enough volunteers?',
                ],
                'tag_ids' => [$interrogativeTagId, $countableTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The budget constraints left {a1} room for innovation.',
                'answers' => ['a1' => 'little'],
                'options' => ['a1' => ['little', 'few', 'many']],
                'verb_hints' => ['a1' => 'almost none uncountable'],
                'hints' => [
                    'Little (без a) означає майже нічого.',
                    'Room (у значенні простір) — незлічуваний іменник.',
                    'Приклад: There was little scope for change.',
                ],
                'tag_ids' => [$affirmativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The study identified {a1} factors contributing to the outcome.',
                'answers' => ['a1' => 'several'],
                'options' => ['a1' => ['several', 'much', 'a little']],
                'verb_hints' => ['a1' => 'some countable'],
                'hints' => [
                    'Several означає кілька зі злічуваними.',
                    'Factors — злічуваний іменник у множині.',
                    'Приклад: Several issues were addressed.',
                ],
                'tag_ids' => [$affirmativeTagId, $countableTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Has the committee received {a1} feedback from stakeholders?',
                'answers' => ['a1' => 'much'],
                'options' => ['a1' => ['much', 'many', 'few']],
                'verb_hints' => ['a1' => 'uncountable in question'],
                'hints' => [
                    'Much вживається з незлічуваними у питаннях.',
                    'Feedback — незлічуваний іменник.',
                    'Приклад: Has there been much discussion?',
                ],
                'tag_ids' => [$interrogativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The analysis revealed {a1} discrepancies in the data.',
                'answers' => ['a1' => 'numerous'],
                'options' => ['a1' => ['numerous', 'much', 'a little']],
                'verb_hints' => ['a1' => 'many formal'],
                'hints' => [
                    'Numerous — формальний синонім many.',
                    'Discrepancies — злічуваний іменник у множині.',
                    'Приклад: Numerous studies have confirmed this.',
                ],
                'tag_ids' => [$affirmativeTagId, $countableTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'We did not observe {a1} variation in the results.',
                'answers' => ['a1' => 'much'],
                'options' => ['a1' => ['much', 'many', 'few']],
                'verb_hints' => ['a1' => 'uncountable in negative'],
                'hints' => [
                    'Much вживається з незлічуваними у запереченнях.',
                    'Variation — незлічуваний іменник.',
                    'Приклад: We did not detect much change.',
                ],
                'tag_ids' => [$negativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The proposal attracted {a1} attention from investors.',
                'answers' => ['a1' => 'a great deal of'],
                'options' => ['a1' => ['a great deal of', 'many', 'few']],
                'verb_hints' => ['a1' => 'large amount formal'],
                'hints' => [
                    'A great deal of — формальний вираз для багато.',
                    'Attention — незлічуваний іменник.',
                    'Приклад: The case attracted a great deal of interest.',
                ],
                'tag_ids' => [$affirmativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'Are there {a1} regulations governing this industry?',
                'answers' => ['a1' => 'many'],
                'options' => ['a1' => ['many', 'much', 'a little']],
                'verb_hints' => ['a1' => 'countable in question'],
                'hints' => [
                    'Many вживається зі злічуваними у питаннях.',
                    'Regulations — злічуваний іменник у множині.',
                    'Приклад: Are there many laws about this?',
                ],
                'tag_ids' => [$interrogativeTagId, $countableTagId],
            ],
            [
                'level' => 'B2',
                'question' => 'The team demonstrated {a1} expertise in the field.',
                'answers' => ['a1' => 'considerable'],
                'options' => ['a1' => ['considerable', 'many', 'few']],
                'verb_hints' => ['a1' => 'significant uncountable'],
                'hints' => [
                    'Considerable означає значну кількість.',
                    'Expertise — незлічуваний іменник.',
                    'Приклад: She showed considerable skill.',
                ],
                'tag_ids' => [$affirmativeTagId, $uncountableTagId],
            ],

            // ===== C1 Level: 12 questions =====
            [
                'level' => 'C1',
                'question' => 'The dissertation contains {a1} groundbreaking insights.',
                'answers' => ['a1' => 'a wealth of'],
                'options' => ['a1' => ['a wealth of', 'much', 'little']],
                'verb_hints' => ['a1' => 'abundance formal'],
                'hints' => [
                    'A wealth of означає багатство, велику кількість.',
                    'Insights — злічуваний іменник у множині.',
                    'Приклад: The archive holds a wealth of documents.',
                ],
                'tag_ids' => [$affirmativeTagId, $countableTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'There has been {a1} scholarly debate on this topic.',
                'answers' => ['a1' => 'considerable'],
                'options' => ['a1' => ['considerable', 'many', 'few']],
                'verb_hints' => ['a1' => 'significant uncountable'],
                'hints' => [
                    'Considerable вживається з незлічуваними для значної кількості.',
                    'Debate — незлічуваний іменник.',
                    'Приклад: There was considerable controversy.',
                ],
                'tag_ids' => [$affirmativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The methodology encountered {a1} criticism from peers.',
                'answers' => ['a1' => 'substantial'],
                'options' => ['a1' => ['substantial', 'many', 'few']],
                'verb_hints' => ['a1' => 'significant formal'],
                'hints' => [
                    'Substantial означає значну кількість.',
                    'Criticism — незлічуваний іменник.',
                    'Приклад: The theory faced substantial opposition.',
                ],
                'tag_ids' => [$affirmativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Have {a1} empirical studies been conducted on this phenomenon?',
                'answers' => ['a1' => 'sufficient'],
                'options' => ['a1' => ['sufficient', 'much', 'a little']],
                'verb_hints' => ['a1' => 'enough countable'],
                'hints' => [
                    'Sufficient означає достатньо.',
                    'Studies — злічуваний іменник у множині.',
                    'Приклад: Have sufficient measures been taken?',
                ],
                'tag_ids' => [$interrogativeTagId, $countableTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The institution possesses {a1} archival material.',
                'answers' => ['a1' => 'an abundance of'],
                'options' => ['a1' => ['an abundance of', 'many', 'few']],
                'verb_hints' => ['a1' => 'large quantity formal'],
                'hints' => [
                    'An abundance of означає велику кількість.',
                    'Material — незлічуваний іменник.',
                    'Приклад: The region has an abundance of resources.',
                ],
                'tag_ids' => [$affirmativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The findings provide {a1} support for the hypothesis.',
                'answers' => ['a1' => 'little'],
                'options' => ['a1' => ['little', 'few', 'many']],
                'verb_hints' => ['a1' => 'almost none uncountable'],
                'hints' => [
                    'Little (без a) означає недостатньо підтримки.',
                    'Support — незлічуваний іменник.',
                    'Приклад: The data provides little evidence.',
                ],
                'tag_ids' => [$affirmativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Did the peer review identify {a1} methodological flaws?',
                'answers' => ['a1' => 'many'],
                'options' => ['a1' => ['many', 'much', 'a little']],
                'verb_hints' => ['a1' => 'countable in question'],
                'hints' => [
                    'Many вживається зі злічуваними у питаннях.',
                    'Flaws — злічуваний іменник у множині.',
                    'Приклад: Did you notice many inconsistencies?',
                ],
                'tag_ids' => [$interrogativeTagId, $countableTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The framework encompasses {a1} theoretical perspectives.',
                'answers' => ['a1' => 'a multitude of'],
                'options' => ['a1' => ['a multitude of', 'much', 'a little']],
                'verb_hints' => ['a1' => 'very many formal'],
                'hints' => [
                    'A multitude of означає безліч.',
                    'Perspectives — злічуваний іменник у множині.',
                    'Приклад: The city attracts a multitude of tourists.',
                ],
                'tag_ids' => [$affirmativeTagId, $countableTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The analysis did not reveal {a1} correlation between the variables.',
                'answers' => ['a1' => 'any significant'],
                'options' => ['a1' => ['any significant', 'many', 'few']],
                'verb_hints' => ['a1' => 'none meaningful'],
                'hints' => [
                    'Any significant вживається у запереченнях.',
                    'Correlation — незлічуваний іменник.',
                    'Приклад: We did not find any noticeable difference.',
                ],
                'tag_ids' => [$negativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The symposium attracted {a1} distinguished scholars.',
                'answers' => ['a1' => 'a large number of'],
                'options' => ['a1' => ['a large number of', 'much', 'a little']],
                'verb_hints' => ['a1' => 'many formal'],
                'hints' => [
                    'A large number of — формальний вираз для багатьох.',
                    'Scholars — злічуваний іменник у множині.',
                    'Приклад: A large number of experts participated.',
                ],
                'tag_ids' => [$affirmativeTagId, $countableTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'Is there {a1} consensus among researchers on this issue?',
                'answers' => ['a1' => 'much'],
                'options' => ['a1' => ['much', 'many', 'few']],
                'verb_hints' => ['a1' => 'uncountable in question'],
                'hints' => [
                    'Much вживається з незлічуваними у питаннях.',
                    'Consensus — незлічуваний іменник.',
                    'Приклад: Is there much agreement on the policy?',
                ],
                'tag_ids' => [$interrogativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'C1',
                'question' => 'The investigation uncovered {a1} irregularities.',
                'answers' => ['a1' => 'numerous'],
                'options' => ['a1' => ['numerous', 'much', 'a little']],
                'verb_hints' => ['a1' => 'many formal'],
                'hints' => [
                    'Numerous — формальний синонім many.',
                    'Irregularities — злічуваний іменник у множині.',
                    'Приклад: Numerous cases have been reported.',
                ],
                'tag_ids' => [$affirmativeTagId, $countableTagId],
            ],

            // ===== C2 Level: 12 questions =====
            [
                'level' => 'C2',
                'question' => 'The treatise presents {a1} erudite observations.',
                'answers' => ['a1' => 'a plethora of'],
                'options' => ['a1' => ['a plethora of', 'much', 'little']],
                'verb_hints' => ['a1' => 'excess or abundance'],
                'hints' => [
                    'A plethora of означає надлишок або велику кількість.',
                    'Observations — злічуваний іменник у множині.',
                    'Приклад: The paper offers a plethora of examples.',
                ],
                'tag_ids' => [$affirmativeTagId, $countableTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'There is {a1} ontological ambiguity in the conceptual framework.',
                'answers' => ['a1' => 'considerable'],
                'options' => ['a1' => ['considerable', 'many', 'few']],
                'verb_hints' => ['a1' => 'significant uncountable'],
                'hints' => [
                    'Considerable вживається з незлічуваними для значної кількості.',
                    'Ambiguity — незлічуваний іменник.',
                    'Приклад: There was considerable uncertainty.',
                ],
                'tag_ids' => [$affirmativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The paradigm shift generated {a1} epistemological controversy.',
                'answers' => ['a1' => 'substantial'],
                'options' => ['a1' => ['substantial', 'many', 'few']],
                'verb_hints' => ['a1' => 'significant formal'],
                'hints' => [
                    'Substantial означає значну кількість.',
                    'Controversy — незлічуваний іменник.',
                    'Приклад: The decision sparked substantial debate.',
                ],
                'tag_ids' => [$affirmativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'Have {a1} hermeneutical approaches been applied to this text?',
                'answers' => ['a1' => 'sufficient'],
                'options' => ['a1' => ['sufficient', 'much', 'a little']],
                'verb_hints' => ['a1' => 'enough countable'],
                'hints' => [
                    'Sufficient означає достатньо.',
                    'Approaches — злічуваний іменник у множині.',
                    'Приклад: Have sufficient analyses been conducted?',
                ],
                'tag_ids' => [$interrogativeTagId, $countableTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The corpus contains {a1} lexicographical data.',
                'answers' => ['a1' => 'an unprecedented amount of'],
                'options' => ['a1' => ['an unprecedented amount of', 'many', 'few']],
                'verb_hints' => ['a1' => 'very large uncountable'],
                'hints' => [
                    'An unprecedented amount of означає безпрецедентну кількість.',
                    'Data — незлічуваний іменник.',
                    'Приклад: The database stores an unprecedented amount of information.',
                ],
                'tag_ids' => [$affirmativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The hypothesis received {a1} empirical corroboration.',
                'answers' => ['a1' => 'scant'],
                'options' => ['a1' => ['scant', 'many', 'a few']],
                'verb_hints' => ['a1' => 'very little formal'],
                'hints' => [
                    'Scant означає мізерну кількість, майже нічого.',
                    'Corroboration — незлічуваний іменник.',
                    'Приклад: There was scant evidence to support the claim.',
                ],
                'tag_ids' => [$affirmativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'Did the philological analysis reveal {a1} semantic nuances?',
                'answers' => ['a1' => 'myriad'],
                'options' => ['a1' => ['myriad', 'much', 'a little']],
                'verb_hints' => ['a1' => 'countless'],
                'hints' => [
                    'Myriad означає незліченну кількість.',
                    'Nuances — злічуваний іменник у множині.',
                    'Приклад: The text reveals myriad interpretations.',
                ],
                'tag_ids' => [$interrogativeTagId, $countableTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The postcolonial discourse encompasses {a1} ideological perspectives.',
                'answers' => ['a1' => 'a vast array of'],
                'options' => ['a1' => ['a vast array of', 'much', 'little']],
                'verb_hints' => ['a1' => 'wide variety'],
                'hints' => [
                    'A vast array of означає широкий спектр.',
                    'Perspectives — злічуваний іменник у множині.',
                    'Приклад: The field includes a vast array of methodologies.',
                ],
                'tag_ids' => [$affirmativeTagId, $countableTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The dialectical tension has produced {a1} philosophical ramifications.',
                'answers' => ['a1' => 'far-reaching'],
                'options' => ['a1' => ['far-reaching', 'much', 'a little']],
                'verb_hints' => ['a1' => 'extensive significant'],
                'hints' => [
                    'Far-reaching означає далекосяжний, широкий.',
                    'Ramifications — злічуваний іменник у множині.',
                    'Приклад: The policy had far-reaching consequences.',
                ],
                'tag_ids' => [$affirmativeTagId, $countableTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'Is there {a1} scholarly consensus on the teleological implications?',
                'answers' => ['a1' => 'any appreciable'],
                'options' => ['a1' => ['any appreciable', 'many', 'few']],
                'verb_hints' => ['a1' => 'noticeable amount'],
                'hints' => [
                    'Any appreciable означає будь-яку помітну кількість.',
                    'Consensus — незлічуваний іменник.',
                    'Приклад: Is there any appreciable difference?',
                ],
                'tag_ids' => [$interrogativeTagId, $uncountableTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The meta-analysis synthesized {a1} empirical investigations.',
                'answers' => ['a1' => 'a comprehensive body of'],
                'options' => ['a1' => ['a comprehensive body of', 'much', 'a little']],
                'verb_hints' => ['a1' => 'extensive collection'],
                'hints' => [
                    'A comprehensive body of означає всебічну сукупність.',
                    'Investigations — злічуваний іменник у множині.',
                    'Приклад: The review covers a comprehensive body of literature.',
                ],
                'tag_ids' => [$affirmativeTagId, $countableTagId],
            ],
            [
                'level' => 'C2',
                'question' => 'The phenomenological inquiry yielded {a1} theoretical insights.',
                'answers' => ['a1' => 'profound'],
                'options' => ['a1' => ['profound', 'much', 'few']],
                'verb_hints' => ['a1' => 'deep significant'],
                'hints' => [
                    'Profound означає глибокий, значний.',
                    'Insights — злічуваний іменник у множині.',
                    'Приклад: The study provided profound understanding.',
                ],
                'tag_ids' => [$affirmativeTagId, $countableTagId],
            ],
        ];
    }

    private function flattenOptions(array $options): array
    {
        $flat = [];
        foreach ($options as $values) {
            foreach ($values as $value) {
                if (!in_array($value, $flat, true)) {
                    $flat[] = $value;
                }
            }
        }
        return $flat;
    }

    private function buildOptionMarkers(array $options): array
    {
        $map = [];
        foreach ($options as $marker => $values) {
            foreach ($values as $value) {
                $map[$value] = $marker;
            }
        }
        return $map;
    }

    private function buildExplanations(array $options, array $answers, string $level): array
    {
        $explanations = [];
        foreach ($options as $marker => $values) {
            $correctAnswer = $answers[$marker] ?? '';
            foreach ($values as $value) {
                if ($value === $correctAnswer) {
                    $explanations[$value] = $this->correctExplanation($value);
                } else {
                    $explanations[$value] = $this->wrongExplanation($value, $correctAnswer);
                }
            }
        }
        return $explanations;
    }

    private function correctExplanation(string $correct): string
    {
        $explanations = [
            'many' => "✅ Правильно! «many» вживається зі злічуваними іменниками у множині.",
            'much' => "✅ Правильно! «much» вживається з незлічуваними іменниками.",
            'a lot of' => "✅ Правильно! «a lot of» вживається з будь-якими іменниками для великої кількості.",
            'a few' => "✅ Правильно! «a few» означає невелику кількість злічуваних іменників.",
            'a little' => "✅ Правильно! «a little» означає невелику кількість незлічуваних іменників.",
            'few' => "✅ Правильно! «few» (без a) означає майже нічого зі злічуваними.",
            'little' => "✅ Правильно! «little» (без a) означає майже нічого з незлічуваними.",
            'several' => "✅ Правильно! «several» означає кілька зі злічуваними іменниками.",
            'enough' => "✅ Правильно! «enough» означає достатню кількість.",
            'numerous' => "✅ Правильно! «numerous» — формальний синонім many.",
            'considerable' => "✅ Правильно! «considerable» означає значну кількість.",
            'substantial' => "✅ Правильно! «substantial» означає значну кількість.",
            'sufficient' => "✅ Правильно! «sufficient» означає достатню кількість.",
        ];

        foreach ($explanations as $key => $text) {
            if (stripos($correct, $key) !== false) {
                return $text;
            }
        }

        return "✅ Правильно! «{$correct}» є коректним вибором у цьому контексті.";
    }

    private function wrongExplanation(string $wrong, string $correct): string
    {
        $wrongLower = strtolower($wrong);
        $correctLower = strtolower($correct);

        if ($wrongLower === 'much' && in_array($correctLower, ['many', 'a few', 'few', 'several', 'numerous'])) {
            return "❌ «much» вживається з незлічуваними іменниками, а тут потрібен квантифікатор для злічуваних.";
        }

        if ($wrongLower === 'many' && in_array($correctLower, ['much', 'a little', 'little'])) {
            return "❌ «many» вживається зі злічуваними іменниками, а тут потрібен квантифікатор для незлічуваних.";
        }

        if ($wrongLower === 'a few' && in_array($correctLower, ['a little', 'much', 'little'])) {
            return "❌ «a few» вживається зі злічуваними іменниками у множині.";
        }

        if ($wrongLower === 'a little' && in_array($correctLower, ['a few', 'many', 'few'])) {
            return "❌ «a little» вживається з незлічуваними іменниками.";
        }

        if ($wrongLower === 'few' && in_array($correctLower, ['little', 'much', 'a little'])) {
            return "❌ «few» вживається зі злічуваними іменниками у множині.";
        }

        if ($wrongLower === 'little' && in_array($correctLower, ['few', 'many', 'a few'])) {
            return "❌ «little» вживається з незлічуваними іменниками.";
        }

        return "❌ «{$wrong}» не підходить у цьому контексті. Правильна відповідь: «{$correct}».";
    }
}
