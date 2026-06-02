<?php

namespace App\Support;

use App\Exceptions\PolyglotLessonValidationException;
use App\Models\Question;

class PolyglotLessonSchemaValidator
{
    private const STRUCTURAL_GRAMMAR_TAGS = [
        'affirmative',
        'negative',
        'question',
        'wh-question',
    ];

    public function validate(array $payload): array
    {
        $errors = [];

        if (! array_key_exists('test', $payload) || ! is_array($payload['test'])) {
            $errors[] = $this->error(null, 'test', 'Root key [test] must be an object.');
        }

        if (! array_key_exists('items', $payload) || ! is_array($payload['items'])) {
            $errors[] = $this->error(null, 'items', 'Root key [items] must be a non-empty array.');
        }

        if ($errors !== []) {
            throw PolyglotLessonValidationException::fromErrors($errors);
        }

        $test = $this->normalizeTest($payload['test'], $errors);
        $items = $this->normalizeItems($payload['items'], $test, $errors);

        if ($errors !== []) {
            throw PolyglotLessonValidationException::fromErrors($errors);
        }

        return [
            'test' => $test,
            'items' => $items,
            'warnings' => $this->buildWarnings($items),
        ];
    }

    private function normalizeTest(array $test, array &$errors): array
    {
        $name = $this->requiredString($test, 'name', $errors);
        $slug = $this->requiredString($test, 'slug', $errors);
        $descriptionUk = $this->requiredString($test, 'description_uk', $errors);
        $courseSlug = $this->requiredString($test, 'course_slug', $errors);
        $topic = $this->requiredString($test, 'topic', $errors);
        $level = strtoupper($this->requiredString($test, 'level', $errors));
        $interfaceLocale = strtolower($this->requiredString($test, 'interface_locale', $errors));
        $studyLocale = strtolower($this->requiredString($test, 'study_locale', $errors));
        $targetLocale = strtolower($this->requiredString($test, 'target_locale', $errors));
        $mode = trim((string) ($test['mode'] ?? ''));
        $questionType = (int) ($test['question_type'] ?? 0);
        $lessonOrder = (int) ($test['lesson_order'] ?? 0);

        if ($mode !== 'compose_tokens') {
            $errors[] = $this->error(null, 'test.mode', 'Mode must be compose_tokens.');
        }

        if ($questionType !== (int) Question::TYPE_COMPOSE_TOKENS) {
            $errors[] = $this->error(null, 'test.question_type', 'Question type must be 4.');
        }

        if ($lessonOrder < 1) {
            $errors[] = $this->error(null, 'test.lesson_order', 'Lesson order must be a positive integer.');
        }

        $completion = $test['completion'] ?? null;
        if (! is_array($completion)) {
            $errors[] = $this->error(null, 'test.completion', 'Completion must be an object.');
            $completion = [];
        }

        $rollingWindow = (int) ($completion['rolling_window'] ?? 0);
        $minRating = (float) ($completion['min_rating'] ?? 0);

        if ($rollingWindow < 1) {
            $errors[] = $this->error(null, 'test.completion.rolling_window', 'Rolling window must be a positive integer.');
        }

        if ($minRating <= 0) {
            $errors[] = $this->error(null, 'test.completion.min_rating', 'Min rating must be greater than zero.');
        }

        $normalized = [
            'name' => $name,
            'slug' => $slug,
            'description_uk' => $descriptionUk,
            'course_slug' => $courseSlug,
            'lesson_order' => $lessonOrder,
            'previous_lesson_slug' => $this->nullableString($test['previous_lesson_slug'] ?? null),
            'next_lesson_slug' => $this->nullableString($test['next_lesson_slug'] ?? null),
            'topic' => $topic,
            'level' => $level,
            'interface_locale' => $interfaceLocale,
            'study_locale' => $studyLocale,
            'target_locale' => $targetLocale,
            'mode' => 'compose_tokens',
            'question_type' => (int) Question::TYPE_COMPOSE_TOKENS,
            'completion' => [
                'rolling_window' => $rollingWindow,
                'min_rating' => $minRating,
            ],
        ];

        if (array_key_exists('prompt_generator', $test)) {
            $promptGenerator = $this->normalizePromptGenerator($test['prompt_generator'], $errors);

            if ($promptGenerator !== null) {
                $normalized['prompt_generator'] = $promptGenerator;
            }
        }

        return $normalized;
    }

    private function normalizeItems(array $items, array $test, array &$errors): array
    {
        if ($items === []) {
            $errors[] = $this->error(null, 'items', 'Items must be a non-empty array.');

            return [];
        }

        $normalized = [];
        $seenUuids = [];

        foreach (array_values($items) as $index => $item) {
            if (! is_array($item)) {
                $errors[] = $this->error(null, "items[{$index}]", 'Each item must be an object.');
                continue;
            }

            $uuid = $this->requiredString($item, 'uuid', $errors, null, "items[{$index}].uuid");

            if ($uuid !== '' && isset($seenUuids[$uuid])) {
                $errors[] = $this->error($uuid, 'uuid', 'UUID must be unique.');
            }

            if ($uuid !== '') {
                $seenUuids[$uuid] = true;
            }

            $sourceText = $this->requiredString($item, 'source_text_uk', $errors, $uuid, 'source_text_uk');
            $targetText = $this->requiredString($item, 'target_text', $errors, $uuid, 'target_text');

            $tokens = $this->normalizeStringArray($item['tokens_correct'] ?? null, $uuid, 'tokens_correct', $errors, true);
            $distractors = $this->normalizeStringArray($item['distractors'] ?? null, $uuid, 'distractors', $errors, false, false);
            $grammarTags = $this->normalizeGrammarTags($item['grammar_tags'] ?? null, $uuid, $test, $errors);
            $hintUk = $this->nullableString($item['hint_uk'] ?? null);
            $explanations = $this->normalizeExplanationMap($item['distractor_explanations_uk'] ?? [], $uuid, $errors);

            if ($targetText !== '' && $tokens !== []) {
                $this->assertTargetTextMatchesTokens($uuid, $sourceText, $targetText, $tokens, $errors);
            }

            $normalized[] = [
                'uuid' => $uuid,
                'source_text_uk' => $this->normalizeSpacing($sourceText),
                'target_text' => $this->normalizeSpacingWithPunctuation($targetText),
                'tokens_correct' => array_values($tokens),
                'distractors' => array_values($distractors),
                'hint_uk' => $hintUk,
                'grammar_tags' => $grammarTags,
                'distractor_explanations_uk' => $explanations,
            ];
        }

        return $normalized;
    }

    private function normalizeGrammarTags(mixed $value, ?string $uuid, array $test, array &$errors): array
    {
        $tags = $this->normalizeStringArray($value, $uuid, 'grammar_tags', $errors, true, true);
        $levelTag = strtolower($test['level']);

        if (! in_array('present-simple', $tags, true)) {
            $errors[] = $this->error($uuid, 'grammar_tags', 'Grammar tags must include present-simple.');
        }

        if (! in_array($levelTag, $tags, true)) {
            $errors[] = $this->error($uuid, 'grammar_tags', sprintf('Grammar tags must include %s.', $levelTag));
        }

        $topicalTags = array_values(array_filter(
            $tags,
            fn (string $tag) => ! in_array($tag, array_merge(['present-simple', $levelTag], self::STRUCTURAL_GRAMMAR_TAGS), true)
        ));

        if ($topicalTags === []) {
            $errors[] = $this->error($uuid, 'grammar_tags', 'Grammar tags must include at least one topical tag.');
        }

        return $tags;
    }

    private function normalizeExplanationMap(mixed $value, ?string $uuid, array &$errors): array
    {
        if ($value === null || $value === []) {
            return [];
        }

        if (! is_array($value)) {
            $errors[] = $this->error($uuid, 'distractor_explanations_uk', 'Distractor explanations must be an object.');

            return [];
        }

        $normalized = [];

        foreach ($value as $token => $text) {
            $normalizedToken = trim((string) $token);
            $normalizedText = $this->nullableString($text);

            if ($normalizedToken === '') {
                $errors[] = $this->error($uuid, 'distractor_explanations_uk', 'Explanation keys must be non-empty strings.');
                continue;
            }

            if ($normalizedText === null) {
                $errors[] = $this->error($uuid, "distractor_explanations_uk.{$normalizedToken}", 'Explanation text must be a non-empty string.');
                continue;
            }

            $normalized[$normalizedToken] = $normalizedText;
        }

        return $normalized;
    }

    private function assertTargetTextMatchesTokens(
        ?string $uuid,
        string $sourceText,
        string $targetText,
        array $tokens,
        array &$errors
    ): void {
        $isQuestion = $this->isQuestionSentence($sourceText);
        $expectedPunctuation = $isQuestion ? '?' : '.';
        $target = $this->normalizeSpacingWithPunctuation($targetText);

        if (! str_ends_with($target, $expectedPunctuation)) {
            $errors[] = $this->error(
                $uuid,
                'target_text',
                sprintf('Target text must end with %s.', $expectedPunctuation)
            );
        }

        $joined = $this->normalizeSpacingWithPunctuation(implode(' ', $tokens).$expectedPunctuation);

        if ($this->normalizeComparisonValue($target) !== $this->normalizeComparisonValue($joined)) {
            $errors[] = $this->error(
                $uuid,
                'target_text',
                'Target text must match tokens_correct after punctuation normalization.'
            );
        }
    }

    private function buildWarnings(array $items): array
    {
        $warnings = [];

        foreach ($items as $item) {
            $duplicateCorrectTokens = $this->duplicateValues($item['tokens_correct']);
            if ($duplicateCorrectTokens !== []) {
                $warnings[] = [
                    'uuid' => $item['uuid'],
                    'field' => 'tokens_correct',
                    'message' => 'Duplicate correct tokens are present: '.implode(', ', $duplicateCorrectTokens),
                ];
            }

            $duplicateDistractors = $this->duplicateValues($item['distractors']);
            if ($duplicateDistractors !== []) {
                $warnings[] = [
                    'uuid' => $item['uuid'],
                    'field' => 'distractors',
                    'message' => 'Duplicate distractors are present: '.implode(', ', $duplicateDistractors),
                ];
            }
        }

        return $warnings;
    }

    private function duplicateValues(array $values): array
    {
        $counts = array_count_values(array_map(fn ($value) => trim((string) $value), $values));

        return array_values(array_keys(array_filter($counts, fn (int $count) => $count > 1)));
    }

    private function normalizeStringArray(
        mixed $value,
        ?string $uuid,
        string $field,
        array &$errors,
        bool $required = true,
        bool $dedupe = false
    ): array {
        if (! is_array($value)) {
            $errors[] = $this->error($uuid, $field, 'Value must be an array.');

            return [];
        }

        $normalized = [];
        foreach ($value as $index => $item) {
            $string = $this->nullableString($item);

            if ($string === null) {
                $errors[] = $this->error($uuid, "{$field}.{$index}", 'Array items must be non-empty strings.');
                continue;
            }

            $string = $field === 'grammar_tags'
                ? strtolower($string)
                : $this->normalizeSpacing($string);

            if ($dedupe && in_array($string, $normalized, true)) {
                continue;
            }

            $normalized[] = $string;
        }

        if ($required && $normalized === []) {
            $errors[] = $this->error($uuid, $field, 'Array must contain at least one item.');
        }

        return $normalized;
    }

    private function requiredString(
        array $payload,
        string $key,
        array &$errors,
        ?string $uuid = null,
        ?string $fieldOverride = null
    ): string {
        $value = $this->nullableString($payload[$key] ?? null);

        if ($value === null) {
            $errors[] = $this->error($uuid, $fieldOverride ?? $key, 'Field is required.');

            return '';
        }

        return $value;
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    private function normalizeSpacing(string $value): string
    {
        $normalized = preg_replace('/\s+/u', ' ', trim($value));

        return is_string($normalized) ? $normalized : trim($value);
    }

    private function normalizeSpacingWithPunctuation(string $value): string
    {
        $normalized = $this->normalizeSpacing($value);
        $normalized = preg_replace('/\s+([?.!,;:])/u', '$1', $normalized);

        return is_string($normalized) ? $normalized : $this->normalizeSpacing($value);
    }

    private function normalizeComparisonValue(string $value): string
    {
        return mb_strtolower($this->normalizeSpacingWithPunctuation($value), 'UTF-8');
    }

    private function isQuestionSentence(string $value): bool
    {
        return str_ends_with(trim($value), '?');
    }

    private function normalizePromptGenerator(mixed $value, array &$errors): ?array
    {
        $result = PromptGeneratorFilterNormalizer::validateTheoryPagePayload($value, 'test.prompt_generator');

        foreach ($result['errors'] as $error) {
            $errors[] = $this->error(null, $error['field'], $error['message']);
        }

        $normalized = $result['normalized'] ?? null;

        return is_array($normalized) ? $normalized : null;
    }

    private function error(?string $uuid, string $field, string $message): array
    {
        return [
            'uuid' => $uuid,
            'field' => $field,
            'message' => $message,
        ];
    }
}
