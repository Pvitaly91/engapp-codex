<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class FirstConditionalPracticeV2Seeder extends QuestionSeeder
{
    public function run(): void
    { 
        $categoryId = Category::firstOrCreate(['name' => 'Conditionals'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Custom: First Conditional Practice V2'])->id;

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'First Conditional Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'First Conditional Sentences'],
            ['category' => 'English Grammar Structure']
        )->id;

        $tenseTagId = Tag::firstOrCreate(['name' => 'First Conditional'], ['category' => 'Tenses'])->id;

        $levelDifficulty = [
            'A1' => 1,
            'A2' => 2,
            'B1' => 3,
            'B2' => 4,
            'C1' => 5,
            'C2' => 5,
        ];

        $questions = [
            [
                'question' => 'If it {a1} tomorrow, we {a2} indoors.',
                'verb_hint' => ['a1' => '(rain)', 'a2' => '(stay)'],
                'options' => [
                    'a1' => ['rains', 'will rain', 'is raining'],
                    'a2' => ['will stay', 'stays', 'are staying'],
                ],
                'answers' => ['a1' => 'rains', 'a2' => 'will stay'],
                'tense' => ['First Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'If she {a1} hard, she {a2} the exam.',
                'verb_hint' => ['a1' => '(study)', 'a2' => '(pass)'],
                'options' => [
                    'a1' => ['studies', 'will study', 'is studying'],
                    'a2' => ['will pass', 'passes', 'is passing'],
                ],
                'answers' => ['a1' => 'studies', 'a2' => 'will pass'],
                'tense' => ['First Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'I will call you if I {a1} my work early.',
                'verb_hint' => ['a1' => '(finish)'],
                'options' => ['finish', 'will finish', 'am finishing'],
                'answers' => ['a1' => 'finish'],
                'tense' => ['First Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'If they {a1} the bus, they {a2} late for school.',
                'verb_hint' => ['a1' => '(miss)', 'a2' => '(be)'],
                'options' => [
                    'a1' => ['miss', 'will miss', 'are missing'],
                    'a2' => ['will be', 'are', 'is being'],
                ],
                'answers' => ['a1' => 'miss', 'a2' => 'will be'],
                'tense' => ['First Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'If you {a1} too much, you {a2} sick.',
                'verb_hint' => ['a1' => '(eat)', 'a2' => '(feel)'],
                'options' => [
                    'a1' => ['eat', 'will eat', 'are eating'],
                    'a2' => ['will feel', 'feel', 'are feeling'],
                ],
                'answers' => ['a1' => 'eat', 'a2' => 'will feel'],
                'tense' => ['First Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'We {a1} to the beach if it {a2} cold.',
                'verb_hint' => ['a1' => '(not go)', 'a2' => '(be)'],
                'options' => [
                    'a1' => ["won't go", "don't go", "aren't going"],
                    'a2' => ['is', 'will be', 'was'],
                ],
                'answers' => ['a1' => "won't go", 'a2' => 'is'],
                'tense' => ['First Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'If he {a1} late, he {a2} the meeting.',
                'verb_hint' => ['a1' => '(work)', 'a2' => '(not attend)'],
                'options' => [
                    'a1' => ['works', 'is working', 'will work'],
                    'a2' => ["won't attend", "doesn't attend", "is not attending"],
                ],
                'answers' => ['a1' => 'works', 'a2' => "won't attend"],
                'tense' => ['First Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'If the price {a1}, people {a2} less.',
                'verb_hint' => ['a1' => '(rise)', 'a2' => '(buy)'],
                'options' => [
                    'a1' => ['rises', 'will rise', 'is rising'],
                    'a2' => ['will buy', 'buy', 'are buying'],
                ],
                'answers' => ['a1' => 'rises', 'a2' => 'will buy'],
                'tense' => ['First Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'She {a1} upset if you {a2}.',
                'verb_hint' => ['a1' => '(not be)', 'a2' => '(apologize)'],
                'options' => [
                    'a1' => ["won't be", "isn't", 'will not be'],
                    'a2' => ['apologize', 'will apologize', 'are apologizing'],
                ],
                'answers' => ['a1' => "won't be", 'a2' => 'apologize'],
                'tense' => ['First Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'If you {a1} regularly, you {a2} healthier.',
                'verb_hint' => ['a1' => '(exercise)', 'a2' => '(be)'],
                'options' => [
                    'a1' => ['exercise', 'will exercise', 'are exercising'],
                    'a2' => ['will be', 'are', 'be'],
                ],
                'answers' => ['a1' => 'exercise', 'a2' => 'will be'],
                'tense' => ['First Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'If the traffic {a1} heavy, we {a2} a different route.',
                'verb_hint' => ['a1' => '(be)', 'a2' => '(take)'],
                'options' => [
                    'a1' => ['is', 'will be', 'was'],
                    'a2' => ['will take', 'take', 'are taking'],
                ],
                'answers' => ['a1' => 'is', 'a2' => 'will take'],
                'tense' => ['First Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'If they {a1}, they {a2} the train.',
                'verb_hint' => ['a1' => '(not hurry)', 'a2' => '(miss)'],
                'options' => [
                    'a1' => ["don't hurry", "won't hurry", "aren't hurrying"],
                    'a2' => ['will miss', 'miss', 'are missing'],
                ],
                'answers' => ['a1' => "don't hurry", 'a2' => 'will miss'],
                'tense' => ['First Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'If I {a1} her, I {a2} her the news.',
                'verb_hint' => ['a1' => '(see)', 'a2' => '(tell)'],
                'options' => [
                    'a1' => ['see', 'will see', 'am seeing'],
                    'a2' => ['will tell', 'tell', 'am telling'],
                ],
                'answers' => ['a1' => 'see', 'a2' => 'will tell'],
                'tense' => ['First Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'If the car {a1} down, we {a2} for help.',
                'verb_hint' => ['a1' => '(break)', 'a2' => '(call)'],
                'options' => [
                    'a1' => ['breaks', 'will break', 'is breaking'],
                    'a2' => ['will call', 'call', 'are calling'],
                ],
                'answers' => ['a1' => 'breaks', 'a2' => 'will call'],
                'tense' => ['First Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'We {a1} for a picnic if it {a2}.',
                'verb_hint' => ['a1' => '(not go)', 'a2' => '(rain)'],
                'options' => [
                    'a1' => ["won't go", "don't go", "aren't going"],
                    'a2' => ['rains', 'will rain', 'is raining'],
                ],
                'answers' => ['a1' => "won't go", 'a2' => 'rains'],
                'tense' => ['First Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'If he {a1} more, he {a2} better grades.',
                'verb_hint' => ['a1' => '(study)', 'a2' => '(get)'],
                'options' => [
                    'a1' => ['studies', 'will study', 'is studying'],
                    'a2' => ['will get', 'gets', 'is getting'],
                ],
                'answers' => ['a1' => 'studies', 'a2' => 'will get'],
                'tense' => ['First Conditional'],
                'level' => 'A2',
            ],
            [
                'question' => 'If you {a1} the plants, they {a2}.',
                'verb_hint' => ['a1' => '(not water)', 'a2' => '(die)'],
                'options' => [
                    'a1' => ["don't water", "won't water", "aren't watering"],
                    'a2' => ['will die', 'die', 'are dying'],
                ],
                'answers' => ['a1' => "don't water", 'a2' => 'will die'],
                'tense' => ['First Conditional'],
                'level' => 'A2',
            ],
        ];

        $items = [];
        $meta = [];

        foreach ($questions as $index => $question) {
            $answersMap = [];
            foreach ($question['answers'] as $marker => $value) {
                $answersMap[$marker] = $this->normalizeValue($value);
            }

            $optionSets = $this->prepareOptionSets($question['options'], $answersMap);
            $flattenedOptions = $this->flattenOptions($optionSets);

            $verbHints = [];
            foreach ($answersMap as $marker => $value) {
                $hintSource = $question['verb_hint'][$marker] ?? null;
                $verbHints[$marker] = $this->normalizeHint($hintSource);
            }

            $example = $this->buildExample($question['question'], $answersMap);

            $answers = [];
            foreach ($answersMap as $marker => $answer) {
                $answers[] = [
                    'marker' => $marker,
                    'answer' => $answer,
                    'verb_hint' => $verbHints[$marker] ?? null,
                ];
            }

            $hints = [];
            $explanations = [];
            $optionMarkerMap = [];

            foreach ($optionSets as $marker => $options) {
                foreach ($options as $option) {
                    if (! isset($optionMarkerMap[$option])) {
                        $optionMarkerMap[$option] = $marker;
                    }
                }

                $clauseType = $this->detectClauseType($question['question'], $marker);
                $hints[$marker] = $this->buildHintForClause($clauseType, $verbHints[$marker] ?? null, $example);

                $explanations = array_merge(
                    $explanations,
                    $this->buildExplanationsForClause(
                        $clauseType,
                        $options,
                        $answersMap[$marker],
                        $example
                    )
                );
            }

            $tagIds = [$themeTagId, $structureTagId, $tenseTagId];
            foreach ($question['tense'] as $tenseName) {
                $tagIds[] = Tag::firstOrCreate(['name' => $tenseName], ['category' => 'Tenses'])->id;
            }

            $uuid = $this->generateQuestionUuid($index + 1, $question['question']);

            $items[] = [
                'uuid' => $uuid,
                'question' => $question['question'],
                'category_id' => $categoryId,
                'difficulty' => $levelDifficulty[$question['level']] ?? 3,
                'source_id' => $sourceId,
                'flag' => 0,
                'level' => $question['level'],
                'tag_ids' => array_values(array_unique($tagIds)),
                'answers' => $answers,
                'options' => $flattenedOptions,
                'variants' => [$question['question']],
            ];

            $meta[] = [
                'uuid' => $uuid,
                'answers' => $answersMap,
                'option_markers' => $optionMarkerMap,
                'hints' => $hints,
                'explanations' => $explanations,
            ];
        }

        $this->seedQuestionData($items, $meta);
    }

    private function prepareOptionSets(array $options, array $answers): array
    {
        if ($this->isAssoc($options)) {
            $result = [];
            foreach ($options as $marker => $choices) {
                $result[$marker] = array_map(fn ($value) => $this->normalizeValue((string) $value), (array) $choices);
            }

            return $result;
        }

        $marker = array_key_first($answers);
        if ($marker === null) {
            return [];
        }

        return [
            $marker => array_map(fn ($value) => $this->normalizeValue((string) $value), $options),
        ];
    }

    private function flattenOptions(array $optionSets): array
    {
        $all = [];
        foreach ($optionSets as $options) {
            foreach ($options as $option) {
                $all[] = $option;
            }
        }

        return array_values(array_unique($all));
    }

    private function detectClauseType(string $question, string $marker): string
    {
        $placeholder = '{' . $marker . '}';
        $position = mb_stripos($question, $placeholder);

        if ($position === false) {
            return 'result';
        }

        $before = mb_substr($question, 0, $position);
        $beforeLower = mb_strtolower($before);

        if (mb_strripos($beforeLower, 'if') !== false) {
            return 'condition';
        }

        return 'result';
    }

    private function buildHintForClause(string $clauseType, ?string $verbHint, string $example): string
    {
        $base = $clauseType === 'condition'
            ? 'If-clause → Present Simple (без will).'
            : "Main clause → will + V1 / won't + V1 для результату.";

        if ($verbHint) {
            $base .= ' Дієслово: ' . $verbHint . '.';
        }

        return $base . ' Приклад: *' . $example . '*.';
    }

    private function buildExplanationsForClause(string $clauseType, array $options, string $answer, string $example): array
    {
        $result = [];
        foreach ($options as $option) {
            if ($option === $answer) {
                $result[$option] = $this->buildCorrectExplanation($clauseType, $option, $example);
            } else {
                $result[$option] = $this->buildWrongExplanation($clauseType, $option, $answer, $example);
            }
        }

        return $result;
    }

    private function buildCorrectExplanation(string $clauseType, string $answer, string $example): string
    {
        if ($clauseType === 'condition') {
            return '✅ «' . $answer . '» — правильна форма Present Simple у частині з if. Приклад: *' . $example . '*.';
        }

        return '✅ «' . $answer . '» — коректна форма Future Simple у головному реченні. Приклад: *' . $example . '*.';
    }

    private function buildWrongExplanation(string $clauseType, string $option, string $answer, string $example): string
    {
        $type = $this->classifyOption($option);

        if ($clauseType === 'condition') {
            return match ($type) {
                'future' => '❌ У підрядній частині першого умовного не вживаємо will/won\'t. Дивись приклад: *' . $example . '*.',
                'continuous' => '❌ If-clause вимагає Present Simple, а не тривалий час. Правильний приклад: *' . $example . '*.',
                'past' => '❌ Перший умовний після if використовує теперішній час, не минулий. Приклад: *' . $example . '*.',
                'perfect' => '❌ Потрібна проста форма, без have + V3. Орієнтуйся на приклад: *' . $example . '*.',
                default => '❌ Спробуй Present Simple у частині з if. Правильний приклад: *' . $example . '*.',
            };
        }

        if ($type === 'future' && $this->isFullNegativeVariant($option, $answer)) {
            return '❌ «' . $option . '» граматично можливо, але завдання очікує скорочення «' . $answer . '». Приклад: *' . $example . '*.';
        }

        return match ($type) {
            'future' => '❌ Форма «' . $option . '» не відповідає потрібній моделі will + правильне дієслово. Подивись: *' . $example . '*.',
            'continuous' => '❌ У головному реченні потрібне will + V1 (або won\'t + V1), а не тривалий час. Правильний приклад: *' . $example . '*.',
            'do' => '❌ Заперечення з do/does не підходить; потрібне will/won\'t. Подивись на приклад: *' . $example . '*.',
            'past' => '❌ Результат у першому умовному будуємо через will, не минулий час. Приклад: *' . $example . '*.',
            'perfect' => '❌ Не використовуй have + V3 у результаті; потрібна проста форма з will. Приклад: *' . $example . '*.',
            'be_negative' => '❌ Краще використати will/won\'t + be, як у прикладі: *' . $example . '*.',
            default => '❌ Основне речення має містити will або won\'t. Орієнтуйся на приклад: *' . $example . '*.',
        };
    }

    private function classifyOption(string $option): string
    {
        $value = mb_strtolower($option);

        if (str_contains($value, 'will have')) {
            return 'perfect';
        }

        if (str_contains($value, "won't") || preg_match('/\bwill\b/', $value)) {
            return 'future';
        }

        if (preg_match('/\b(am|is|are|was|were)\b[^a-z]*[a-z]+ing/', $value)) {
            return 'continuous';
        }

        if (preg_match('/\b(did|was|were)\b/', $value)) {
            return 'past';
        }

        if (preg_match("/\b(do|does|don't|doesn't)\b/", $value)) {
            return 'do';
        }

        if (preg_match("/\b(has|have|had)\b/", $value)) {
            return 'perfect';
        }

        if (preg_match("/\b(isn't|aren't)\b/", $value)) {
            return 'be_negative';
        }

        return 'present_simple';
    }

    private function isFullNegativeVariant(string $option, string $answer): bool
    {
        $optionNormalized = str_replace(' ', '', mb_strtolower($option));
        $answerNormalized = str_replace(' ', '', mb_strtolower($answer));

        return str_contains($optionNormalized, 'willnot') && str_contains($answerNormalized, "won't");
    }

    private function buildExample(string $question, array $answers): string
    {
        $result = $question;
        foreach ($answers as $marker => $answer) {
            $result = str_replace('{' . $marker . '}', $answer, $result);
        }

        $result = preg_replace('/\s+/', ' ', trim($result));

        $first = mb_substr($result, 0, 1, 'UTF-8');
        $rest = mb_substr($result, 1, null, 'UTF-8');

        return mb_strtoupper($first, 'UTF-8') . $rest;
    }

    private function normalizeValue(string $value): string
    {
        $value = str_replace(['’', '‘', '‛', 'ʻ'], "'", $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }

    private function isAssoc(array $array): bool
    {
        if ($array === []) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }
}
