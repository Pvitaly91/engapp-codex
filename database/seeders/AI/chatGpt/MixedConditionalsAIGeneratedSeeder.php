<?php

namespace Database\Seeders\AI\chatGpt;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class MixedConditionalsAIGeneratedSeeder extends QuestionSeeder
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

        $sourceIds = [
            'set1' => Source::firstOrCreate([
                'name' => 'AI generated: Quantifiers Practice: Much / Many / A lot / Few / Little (SET 1)',
            ])->id,
        ];

        $baseTagIds = $this->buildBaseTags();

        $questions = $this->questionEntries();

        $items = [];
        $meta = [];

        foreach ($questions as $index => $entry) {
            if (! is_array($entry) || ! isset($entry['answers'], $entry['options'])) {
                continue;
            }

            $answers = [];
            foreach ($entry['answers'] as $marker => $answer) {
                $answers[] = [
                    'marker' => $marker,
                    'answer' => $answer,
                    'verb_hint' => $entry['verb_hints'][$marker] ?? null,
                ];
            }

            $options = $this->flattenOptions($entry['options']);
            $uuid = $this->generateQuestionUuid($index + 1, $entry['question']);

            $tagIds = array_merge($baseTagIds, $this->buildTagIds($entry['tags'] ?? []));

            $items[] = [
                'uuid' => $uuid,
                'question' => $entry['question'],
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$entry['level']] ?? 2,
                'source_id' => $sourceIds[$entry['source']] ?? reset($sourceIds),
                'flag' => 2,
                'type' => 0,
                'level' => $entry['level'],
                'tag_ids' => $tagIds,
                'answers' => $answers,
                'options' => $options,
                'variants' => [$entry['question']],
            ];

            $meta[] = [
                'uuid' => $uuid,
                'answers' => $entry['answers'],
                'hints' => $entry['hints'] ?? [],
                'explanations' => $entry['explanations'] ?? [],
            ];
        }

        $this->seedQuestionData($items, []);
        $this->attachHintsAndExplanations($meta);
    }

    private function buildBaseTags(): array
    {
        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Quantifiers Practice AI'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Much / Many / A lot / Few / Little'],
            ['category' => 'English Grammar Detail']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'Quantifier Gap-fill'],
            ['category' => 'English Grammar Structure']
        )->id;

        $focusTagId = Tag::firstOrCreate(
            ['name' => 'Countable vs Uncountable'],
            ['category' => 'English Grammar Focus']
        )->id;

        return [$themeTagId, $detailTagId, $structureTagId, $focusTagId];
    }

    private function buildTagIds(array $tags): array
    {
        $ids = [];
        foreach (array_unique($tags) as $tag) {
            $ids[] = Tag::firstOrCreate(
                ['name' => $tag],
                ['category' => 'English Grammar Tag']
            )->id;
        }
        return $ids;
    }

    private function attachHintsAndExplanations(array $meta): void
    {
        foreach ($meta as $data) {
            $question = Question::where('uuid', $data['uuid'])->first();

            if (! $question) {
                continue;
            }

            $hintText = $this->formatHints($data['hints'] ?? []);
            if ($hintText !== null) {
                QuestionHint::updateOrCreate(
                    ['question_id' => $question->id, 'provider' => 'chatgpt', 'locale' => 'uk'],
                    ['hint' => $hintText]
                );
            }

            $answers = $data['answers'] ?? [];
            foreach ($data['explanations'] ?? [] as $marker => $options) {
                if (! isset($answers[$marker])) {
                    $fallback = reset($answers);
                    $answers[$marker] = is_string($fallback) ? $fallback : (string) $fallback;
                }

                $correct = $answers[$marker];
                if (! is_string($correct)) {
                    $correct = (string) $correct;
                }

                foreach ($options as $option => $text) {
                    ChatGPTExplanation::updateOrCreate(
                        [
                            'question' => $question->question,
                            'wrong_answer' => $option,
                            'correct_answer' => $correct,
                            'language' => 'ua',
                        ],
                        ['explanation' => $text]
                    );
                }
            }
        }
    }

    private function questionEntries(): array
    {
        return [
            $this->makeEntry([
                'question' => "I don't have {a1} time today.",
                'options' => ['a1' => ['much', 'many', 'a lot of']],
                'answers' => ['a1' => 'much'],
                'level' => 'A1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'negative', 'tense' => 'present simple'],
            ]),
            $this->makeEntry([
                'question' => 'We saw too {a1} cars on the street.',
                'options' => ['a1' => ['many', 'much', 'a lot of']],
                'answers' => ['a1' => 'many'],
                'level' => 'A1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'She drinks {a1} milk every morning.',
                'options' => ['a1' => ['a lot of', 'much', 'many']],
                'answers' => ['a1' => 'a lot of'],
                'level' => 'A1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'affirmative', 'tense' => 'present simple'],
            ]),
            $this->makeEntry([
                'question' => 'How {a1} apples do you want?',
                'options' => ['a1' => ['many', 'much', 'a lot']],
                'answers' => ['a1' => 'many'],
                'level' => 'A1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'interrogative', 'tense' => 'present simple'],
            ]),
            $this->makeEntry([
                'question' => 'There is {a1} water in the bottle, so take another one.',
                'options' => ['a1' => ['little', 'few', 'many']],
                'answers' => ['a1' => 'little'],
                'level' => 'A1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'affirmative', 'tense' => 'present simple'],
            ]),
            $this->makeEntry([
                'question' => 'We have {a1} chairs, so some people must stand.',
                'options' => ['a1' => ['few', 'little', 'much']],
                'answers' => ['a1' => 'few'],
                'level' => 'A1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'affirmative', 'tense' => 'present simple'],
            ]),
            $this->makeEntry([
                'question' => 'They travel {a1} for work.',
                'options' => ['a1' => ['a lot', 'many', 'much']],
                'answers' => ['a1' => 'a lot'],
                'level' => 'A1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'without noun'],
                'context' => ['countability' => 'no noun', 'sentence_form' => 'affirmative', 'tense' => 'present simple'],
            ]),
            $this->makeEntry([
                'question' => 'Is there too {a1} sugar in your tea?',
                'options' => ['a1' => ['much', 'many', 'a lot of']],
                'answers' => ['a1' => 'much'],
                'level' => 'A1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'interrogative', 'tense' => 'present simple'],
            ]),
            $this->makeEntry([
                'question' => "I've got {a1} homework tonight, so I can't go out.",
                'options' => ['a1' => ['a lot of', 'many', 'much']],
                'answers' => ['a1' => 'a lot of'],
                'level' => 'A1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'affirmative', 'tense' => 'present perfect'],
            ]),
            $this->makeEntry([
                'question' => 'How {a1} money do you have?',
                'options' => ['a1' => ['much', 'many', 'a lot']],
                'answers' => ['a1' => 'much'],
                'level' => 'A1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'interrogative', 'tense' => 'present simple'],
            ]),
            $this->makeEntry([
                'question' => 'There are so {a1} bananas that the basket is full.',
                'options' => ['a1' => ['many', 'much', 'a lot of']],
                'answers' => ['a1' => 'many'],
                'level' => 'A1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'affirmative', 'tense' => 'present simple'],
            ]),
            $this->makeEntry([
                'question' => 'He has {a1} patience with kids.',
                'options' => ['a1' => ['little', 'few', 'many']],
                'answers' => ['a1' => 'little'],
                'level' => 'A1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'affirmative', 'tense' => 'present simple'],
            ]),
            $this->makeEntry([
                'question' => "We didn't have {a1} time to finish the test.",
                'options' => ['a1' => ['much', 'many', 'a lot of']],
                'answers' => ['a1' => 'much'],
                'level' => 'A2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'negative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'She has {a1} friends in the new city, so she feels lonely.',
                'options' => ['a1' => ['few', 'little', 'much']],
                'answers' => ['a1' => 'few'],
                'level' => 'A2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'affirmative', 'tense' => 'present simple'],
            ]),
            $this->makeEntry([
                'question' => 'There were {a1} people at the meeting, so it ended quickly.',
                'options' => ['a1' => ['few', 'little', 'many']],
                'answers' => ['a1' => 'few'],
                'level' => 'A2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'He spent {a1} money on the trip, but it was worth it.',
                'options' => ['a1' => ['a lot of', 'many', 'much']],
                'answers' => ['a1' => 'a lot of'],
                'level' => 'A2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'Do you have {a1} questions before we start?',
                'options' => ['a1' => ['many', 'much', 'a lot']],
                'answers' => ['a1' => 'many'],
                'level' => 'A2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'interrogative', 'tense' => 'present simple'],
            ]),
            $this->makeEntry([
                'question' => 'I can see so {a1} stars tonight.',
                'options' => ['a1' => ['many', 'much', 'a lot of']],
                'answers' => ['a1' => 'many'],
                'level' => 'A2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'affirmative', 'tense' => 'present simple'],
            ]),
            $this->makeEntry([
                'question' => 'There is {a1} milk left; we need to buy more.',
                'options' => ['a1' => ['little', 'few', 'many']],
                'answers' => ['a1' => 'little'],
                'level' => 'A2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'affirmative', 'tense' => 'present simple'],
            ]),
            $this->makeEntry([
                'question' => 'We will have {a1} free time next week.',
                'options' => ['a1' => ['a lot of', 'much', 'many']],
                'answers' => ['a1' => 'a lot of'],
                'level' => 'A2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'affirmative', 'tense' => 'future simple'],
            ]),
            $this->makeEntry([
                'question' => 'How {a1} vegetables does he eat each day?',
                'options' => ['a1' => ['many', 'much', 'a lot']],
                'answers' => ['a1' => 'many'],
                'level' => 'A2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'interrogative', 'tense' => 'present simple'],
            ]),
            $this->makeEntry([
                'question' => 'My sister travels {a1} for her job.',
                'options' => ['a1' => ['a lot', 'many', 'much']],
                'answers' => ['a1' => 'a lot'],
                'level' => 'A2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'without noun'],
                'context' => ['countability' => 'no noun', 'sentence_form' => 'affirmative', 'tense' => 'present simple'],
            ]),
            $this->makeEntry([
                'question' => 'We have {a1} chairs, but we need two more.',
                'options' => ['a1' => ['few', 'little', 'many']],
                'answers' => ['a1' => 'few'],
                'level' => 'A2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'affirmative', 'tense' => 'present simple'],
            ]),
            $this->makeEntry([
                'question' => 'Is there too {a1} traffic at this hour?',
                'options' => ['a1' => ['much', 'many', 'a lot of']],
                'answers' => ['a1' => 'much'],
                'level' => 'A2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'interrogative', 'tense' => 'present simple'],
            ]),
            $this->makeEntry([
                'question' => 'I had {a1} time to study, so I felt unprepared.',
                'options' => ['a1' => ['little', 'few', 'many']],
                'answers' => ['a1' => 'little'],
                'level' => 'B1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'There were {a1} emails to answer, and I finished them quickly.',
                'options' => ['a1' => ['few', 'little', 'many']],
                'answers' => ['a1' => 'few'],
                'level' => 'B1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => "We have {a1} information about the problem, so we can't decide yet.",
                'options' => ['a1' => ['little', 'few', 'much']],
                'answers' => ['a1' => 'little'],
                'level' => 'B1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'affirmative', 'tense' => 'present simple'],
            ]),
            $this->makeEntry([
                'question' => 'How {a1} people applied for the job?',
                'options' => ['a1' => ['many', 'much', 'a lot']],
                'answers' => ['a1' => 'many'],
                'level' => 'B1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'interrogative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'The kids ate too {a1} candy, so they were too excited.',
                'options' => ['a1' => ['much', 'many', 'a lot of']],
                'answers' => ['a1' => 'much'],
                'level' => 'B1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'She had so {a1} meetings today and is exhausted.',
                'options' => ['a1' => ['many', 'much', 'a lot of']],
                'answers' => ['a1' => 'many'],
                'level' => 'B1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'affirmative', 'tense' => 'present perfect'],
            ]),
            $this->makeEntry([
                'question' => "We don't have {a1} space in the car for another bag.",
                'options' => ['a1' => ['much', 'many', 'a lot of']],
                'answers' => ['a1' => 'much'],
                'level' => 'B1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'negative', 'tense' => 'present simple'],
            ]),
            $this->makeEntry([
                'question' => 'They bought {a1} during the trip.',
                'options' => ['a1' => ['a lot', 'many', 'much']],
                'answers' => ['a1' => 'a lot'],
                'level' => 'B1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'without noun'],
                'context' => ['countability' => 'no noun', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'Only {a1} students completed the extra task.',
                'options' => ['a1' => ['few', 'little', 'many']],
                'answers' => ['a1' => 'few'],
                'level' => 'B1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'There is {a1} noise outside, so I can focus.',
                'options' => ['a1' => ['little', 'few', 'much']],
                'answers' => ['a1' => 'little'],
                'level' => 'B1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'affirmative', 'tense' => 'present simple'],
            ]),
            $this->makeEntry([
                'question' => 'Did you spend too {a1} on that new laptop?',
                'options' => ['a1' => ['much', 'many', 'a lot of']],
                'answers' => ['a1' => 'much'],
                'level' => 'B1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'interrogative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => "We have {a1} options to choose from, so let's decide quickly.",
                'options' => ['a1' => ['few', 'little', 'many']],
                'answers' => ['a1' => 'few'],
                'level' => 'B1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'affirmative', 'tense' => 'present simple'],
            ]),
            $this->makeEntry([
                'question' => 'Only {a1} applicants met the requirements.',
                'options' => ['a1' => ['few', 'little', 'many']],
                'answers' => ['a1' => 'few'],
                'level' => 'B2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'She had {a1} evidence to support her claim, so the case was weak.',
                'options' => ['a1' => ['little', 'few', 'much']],
                'answers' => ['a1' => 'little'],
                'level' => 'B2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'How {a1} funding did the project receive last year?',
                'options' => ['a1' => ['much', 'many', 'a lot']],
                'answers' => ['a1' => 'much'],
                'level' => 'B2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'interrogative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'They found too {a1} errors in the report after the review.',
                'options' => ['a1' => ['many', 'much', 'a lot of']],
                'answers' => ['a1' => 'many'],
                'level' => 'B2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'We received {a1} feedback, and most of it was positive.',
                'options' => ['a1' => ['a lot of', 'many', 'much']],
                'answers' => ['a1' => 'a lot of'],
                'level' => 'B2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'There was {a1} interest in the workshop, so it was cancelled.',
                'options' => ['a1' => ['little', 'few', 'many']],
                'answers' => ['a1' => 'little'],
                'level' => 'B2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => "I don't have {a1} patience for long meetings.",
                'options' => ['a1' => ['much', 'many', 'a lot of']],
                'answers' => ['a1' => 'much'],
                'level' => 'B2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'negative', 'tense' => 'present simple'],
            ]),
            $this->makeEntry([
                'question' => 'Only {a1} of the seats were filled, so the hall felt empty.',
                'options' => ['a1' => ['few', 'little', 'many']],
                'answers' => ['a1' => 'few'],
                'level' => 'B2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'She spoke to {a1} before making the decision.',
                'options' => ['a1' => ['a lot', 'many', 'much']],
                'answers' => ['a1' => 'a lot'],
                'level' => 'B2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'without noun'],
                'context' => ['countability' => 'no noun', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'There is {a1} equipment available, so we must borrow some.',
                'options' => ['a1' => ['little', 'few', 'many']],
                'answers' => ['a1' => 'little'],
                'level' => 'B2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'affirmative', 'tense' => 'present simple'],
            ]),
            $this->makeEntry([
                'question' => 'Were there too {a1} delays on the route?',
                'options' => ['a1' => ['many', 'much', 'a lot of']],
                'answers' => ['a1' => 'many'],
                'level' => 'B2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'interrogative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'I had {a1} time to prepare, yet the presentation went well.',
                'options' => ['a1' => ['little', 'few', 'much']],
                'answers' => ['a1' => 'little'],
                'level' => 'B2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'The committee showed {a1} interest in the proposal, despite the effort.',
                'options' => ['a1' => ['little', 'few', 'much']],
                'answers' => ['a1' => 'little'],
                'level' => 'C1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'Only {a1} citizens responded to the survey, which affected the results.',
                'options' => ['a1' => ['few', 'little', 'many']],
                'answers' => ['a1' => 'few'],
                'level' => 'C1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'How {a1} evidence was presented to support the theory?',
                'options' => ['a1' => ['much', 'many', 'a lot']],
                'answers' => ['a1' => 'much'],
                'level' => 'C1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'interrogative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'The report contained so {a1} figures that they were difficult to verify.',
                'options' => ['a1' => ['many', 'much', 'a lot of']],
                'answers' => ['a1' => 'many'],
                'level' => 'C1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'We had {a1} resources to allocate, so we prioritized the essentials.',
                'options' => ['a1' => ['few', 'little', 'many']],
                'answers' => ['a1' => 'few'],
                'level' => 'C1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'There is {a1} clarity about the timeline, which worries investors.',
                'options' => ['a1' => ['little', 'few', 'much']],
                'answers' => ['a1' => 'little'],
                'level' => 'C1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'affirmative', 'tense' => 'present simple'],
            ]),
            $this->makeEntry([
                'question' => 'She had to manage too {a1} tasks at once, which increased stress.',
                'options' => ['a1' => ['many', 'much', 'a lot of']],
                'answers' => ['a1' => 'many'],
                'level' => 'C1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'They invested {a1} in the project, yet returns were modest.',
                'options' => ['a1' => ['a lot', 'many', 'much']],
                'answers' => ['a1' => 'a lot'],
                'level' => 'C1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'without noun'],
                'context' => ['countability' => 'no noun', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'Not {a1} attention was given to the risks during planning.',
                'options' => ['a1' => ['much', 'many', 'a lot of']],
                'answers' => ['a1' => 'much'],
                'level' => 'C1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'negative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'Despite the hype, there were {a1} questions at the Q&A.',
                'options' => ['a1' => ['few', 'little', 'many']],
                'answers' => ['a1' => 'few'],
                'level' => 'C1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'We received {a1} guidance, so we relied on our own judgment.',
                'options' => ['a1' => ['little', 'few', 'much']],
                'answers' => ['a1' => 'little'],
                'level' => 'C1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'Only {a1} of the documents were translated on time.',
                'options' => ['a1' => ['few', 'little', 'many']],
                'answers' => ['a1' => 'few'],
                'level' => 'C1',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'The experiment yielded {a1} data, yet the conclusions were decisive.',
                'options' => ['a1' => ['little', 'few', 'much']],
                'answers' => ['a1' => 'little'],
                'level' => 'C2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'How {a1} discretion should a judge exercise in such cases?',
                'options' => ['a1' => ['much', 'many', 'a lot']],
                'answers' => ['a1' => 'much'],
                'level' => 'C2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'interrogative', 'tense' => 'modal present'],
            ]),
            $this->makeEntry([
                'question' => 'There were {a1} anomalies in the dataset, but they were significant.',
                'options' => ['a1' => ['few', 'little', 'many']],
                'answers' => ['a1' => 'few'],
                'level' => 'C2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'The author used so {a1} references to support the argument.',
                'options' => ['a1' => ['many', 'much', 'a lot of']],
                'answers' => ['a1' => 'many'],
                'level' => 'C2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'We had {a1} time to respond, so the statement was brief.',
                'options' => ['a1' => ['little', 'few', 'much']],
                'answers' => ['a1' => 'little'],
                'level' => 'C2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'Only {a1} contributors maintained the project after the launch.',
                'options' => ['a1' => ['few', 'little', 'many']],
                'answers' => ['a1' => 'few'],
                'level' => 'C2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'The team spent {a1} on research before releasing the product.',
                'options' => ['a1' => ['a lot', 'many', 'much']],
                'answers' => ['a1' => 'a lot'],
                'level' => 'C2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'without noun'],
                'context' => ['countability' => 'no noun', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'How {a1} tolerance is there for errors in this industry?',
                'options' => ['a1' => ['much', 'many', 'a lot']],
                'answers' => ['a1' => 'much'],
                'level' => 'C2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'interrogative', 'tense' => 'present simple'],
            ]),
            $this->makeEntry([
                'question' => 'Despite extensive planning, {a1} aspects were overlooked.',
                'options' => ['a1' => ['few', 'little', 'many']],
                'answers' => ['a1' => 'few'],
                'level' => 'C2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'We received {a1} criticism, yet it was enough to revise the policy.',
                'options' => ['a1' => ['little', 'few', 'much']],
                'answers' => ['a1' => 'little'],
                'level' => 'C2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'The audience had {a1} patience for delays, so the speaker rushed.',
                'options' => ['a1' => ['little', 'few', 'much']],
                'answers' => ['a1' => 'little'],
                'level' => 'C2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'uncountable noun'],
                'context' => ['countability' => 'uncountable', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
            $this->makeEntry([
                'question' => 'There were {a1} opportunities to renegotiate, which limited options.',
                'options' => ['a1' => ['few', 'little', 'many']],
                'answers' => ['a1' => 'few'],
                'level' => 'C2',
                'source' => 'set1',
                'verb_hints' => ['a1' => 'countable plural'],
                'context' => ['countability' => 'countable plural', 'sentence_form' => 'affirmative', 'tense' => 'past simple'],
            ]),
        ];
    }

    private function makeEntry(array $entry): array
    {
        $context = $entry['context'] ?? [];
        $entry['hints'] = $this->buildHints($context);
        $entry['explanations'] = $this->buildExplanations($entry['options'], $entry['answers']);
        $entry['tags'] = $this->buildTags($entry, $context);

        return $entry;
    }

    private function buildHints(array $context): array
    {
        $hints = [];
        $countability = $context['countability'] ?? '';
        $sentenceForm = $context['sentence_form'] ?? '';

        $hints[] = match ($countability) {
            'countable plural' => 'Іменник після пропуску — злічуваний у множині.',
            'uncountable' => 'Іменник після пропуску — незлічуваний.',
            'no noun' => 'Після пропуску немає іменника, тож потрібна форма без "of".',
            default => 'Зверни увагу на тип іменника після пропуску.',
        };

        $hints[] = match ($sentenceForm) {
            'interrogative' => 'Це питання: обери квантифікатор, типовий для цього формату.',
            'negative' => 'Це заперечення: використай форму, яка типова для такого контексту.',
            default => 'Контекст підказує, чи йдеться про велику або малу кількість.',
        };

        return $hints;
    }

    private function buildExplanations(array $options, array $answers): array
    {
        $explanations = [];

        foreach ($options as $marker => $values) {
            $correct = $answers[$marker] ?? null;
            foreach ($values as $value) {
                $type = $this->classifyOption($value);
                $isCorrect = $value === $correct;
                $explanations[$marker][$value] = $this->explanationForType($type, $isCorrect);
            }
        }

        return $explanations;
    }

    private function classifyOption(string $option): string
    {
        return match ($option) {
            'many' => 'many',
            'much' => 'much',
            'a lot of' => 'a_lot_of',
            'a lot' => 'a_lot',
            'few' => 'few',
            'little' => 'little',
            default => 'other',
        };
    }

    private function explanationForType(string $type, bool $isCorrect): string
    {
        $templates = [
            'much' => [
                'rule' => 'Квантифікатор для незлічуваних іменників, здебільшого у питаннях та запереченнях.',
                'examples' => [
                    'Do you have ___ time?',
                    "I don't have ___ energy.",
                ],
            ],
            'many' => [
                'rule' => 'Форма для злічуваних іменників у множині, особливо в питаннях, запереченнях або після підсилювачів.',
                'examples' => [
                    'Are there ___ chairs left?',
                    "We didn't invite ___ guests.",
                ],
            ],
            'a_lot_of' => [
                'rule' => 'Використовується перед іменником (злічуваним або незлічуваним), зазвичай у ствердженнях.',
                'examples' => [
                    'She has ___ homework.',
                    'They bought ___ snacks.',
                ],
            ],
            'a_lot' => [
                'rule' => 'Вживається без іменника як обставина кількості.',
                'examples' => [
                    'They travel ___.',
                    'He talks ___ at work.',
                ],
            ],
            'few' => [
                'rule' => 'Позначає малу кількість злічуваних іменників у множині, часто з відтінком нестачі.',
                'examples' => [
                    'We have ___ seats left.',
                    'Only ___ students arrived.',
                ],
            ],
            'little' => [
                'rule' => 'Позначає малу кількість або обсяг незлічуваних іменників, часто з відтінком нестачі.',
                'examples' => [
                    'There is ___ water left.',
                    'He had ___ patience.',
                ],
            ],
            'other' => [
                'rule' => 'Перевір узгодження з типом іменника та формою речення.',
                'examples' => [
                    'Is there ___ sugar?',
                    'We saw ___ cars.',
                ],
            ],
        ];

        $template = $templates[$type] ?? $templates['other'];
        $prefix = $isCorrect ? '✅ ' : '❌ ';
        $examples = implode(' ', array_map(fn ($example) => "'{$example}'", $template['examples']));

        return $prefix . $template['rule'] . ' Приклади: ' . $examples;
    }

    private function buildTags(array $entry, array $context): array
    {
        $countability = $context['countability'] ?? 'unknown';
        $sentenceForm = $context['sentence_form'] ?? 'affirmative';
        $tense = $context['tense'] ?? 'present simple';
        $level = $entry['level'] ?? 'A1';
        $correct = $entry['answers']['a1'] ?? '';
        $quantity = in_array($correct, ['few', 'little'], true) ? 'quantity-small' : 'quantity-large';

        return array_values(array_unique([
            'grammar',
            'quantifiers',
            'much/many/a lot/few/little',
            $countability,
            $sentenceForm,
            $tense,
            $quantity,
            $level,
        ]));
    }
}
