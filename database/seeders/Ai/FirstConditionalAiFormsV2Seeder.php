<?php

namespace Database\Seeders\Ai;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;
use JsonException;

class FirstConditionalAiFormsV2Seeder extends QuestionSeeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Conditionals'])->id;

        $sourceMap = [
            'negative_past' => Source::firstOrCreate(['name' => 'AI: First Conditional Negative — Past Context'])->id,
            'negative_present' => Source::firstOrCreate(['name' => 'AI: First Conditional Negative — Present Context'])->id,
            'negative_future' => Source::firstOrCreate(['name' => 'AI: First Conditional Negative — Future Context'])->id,
            'interrogative_past' => Source::firstOrCreate(['name' => 'AI: First Conditional Questions — Past Context'])->id,
            'interrogative_present' => Source::firstOrCreate(['name' => 'AI: First Conditional Questions — Present Context'])->id,
            'interrogative_future' => Source::firstOrCreate(['name' => 'AI: First Conditional Questions — Future Context'])->id,
        ];

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

        $dataPath = __DIR__ . '/data/first_conditional_ai_forms.json';
        $json = file_get_contents($dataPath);

        if ($json === false) {
            return;
        }

        try {
            $questions = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            return;
        }

        if (! is_array($questions) || $questions === []) {
            return;
        }

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
                'source_id' => $sourceMap[$question['source_key']] ?? null,
                'flag' => 2,
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
            ? 'If-clause → Present Simple/Perfect (без will).'
            : "Main clause → will/won't + V1 для результату.";

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
            return '✅ «' . $answer . '» — доречна форма теперішнього часу в частині з if. Приклад: *' . $example . '*.';
        }

        return '✅ «' . $answer . '» — правильна форма will/won\'t у головному реченні. Приклад: *' . $example . '*.';
    }

    private function buildWrongExplanation(string $clauseType, string $option, string $answer, string $example): string
    {
        $type = $this->classifyOption($option);

        if ($clauseType === 'condition') {
            return match ($type) {
                'future' => '❌ У підрядній частині першого умовного не вживаємо will/won\'t. Приклад: *' . $example . '*.',
                'continuous' => '❌ If-clause вимагає просту форму, а не тривалий час. Правильний приклад: *' . $example . '*.',
                'past' => '❌ Перший умовний використовує теперішні форми після if, не минулий час. Приклад: *' . $example . '*.',
                'perfect' => '❌ Вживаємо без have + V3, якщо потрібно Present Simple. Орієнтуйся на приклад: *' . $example . '*.',
                default => '❌ Спробуй просту теперішню форму в if-clause. Правильний приклад: *' . $example . '*.',
            };
        }

        if ($type === 'future' && $this->isFullNegativeVariant($option, $answer)) {
            return '❌ «' . $option . '» граматично можливо, але потрібно коротка форма «' . $answer . '». Приклад: *' . $example . '*.';
        }

        return match ($type) {
            'future' => '❌ Варіант «' . $option . '» не відповідає моделі will/won\'t + V1. Подивись: *' . $example . '*.',
            'continuous' => '❌ У головному реченні потрібна форма will + V1, а не тривалий час. Правильний приклад: *' . $example . '*.',
            'do' => '❌ Заперечення з do/does не пасує; потрібна конструкція з will. Подивись на приклад: *' . $example . '*.',
            'past' => '❌ Результат першого умовного будується через will, не минулий час. Приклад: *' . $example . '*.',
            'perfect' => '❌ Не використовуй have + V3 у результаті; краще will + V1. Приклад: *' . $example . '*.',
            'be_negative' => '❌ Краще вжити will/won\'t + be, як у прикладі: *' . $example . '*.',
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
