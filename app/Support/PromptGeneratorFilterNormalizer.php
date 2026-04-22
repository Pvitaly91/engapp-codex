<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use JsonException;

class PromptGeneratorFilterNormalizer
{
    public static function normalize(mixed $payload): mixed
    {
        if (is_string($payload)) {
            $payload = self::parseStringPayload($payload);
        }

        if (! is_array($payload)) {
            return $payload;
        }

        return self::normalizeStructuredPayload($payload);
    }

    /**
     * @return array{normalized: array<string, mixed>|null, errors: array<int, array{field: string, message: string}>}
     */
    public static function validateTheoryPagePayload(mixed $payload, string $fieldPrefix = 'prompt_generator'): array
    {
        $errors = [];

        if (! is_array($payload)) {
            return [
                'normalized' => null,
                'errors' => [
                    self::validationError($fieldPrefix, 'Prompt generator must be an object.'),
                ],
            ];
        }

        $sourceType = trim((string) ($payload['source_type'] ?? ''));

        if ($sourceType === '') {
            $errors[] = self::validationError("{$fieldPrefix}.source_type", 'Field is required.');
        } elseif (self::normalizeSourceType($sourceType) !== 'theory_page') {
            $errors[] = self::validationError(
                "{$fieldPrefix}.source_type",
                'Only theory_page source_type is supported.'
            );
        }

        if (! array_key_exists('theory_page', $payload) || ! is_array($payload['theory_page'])) {
            $errors[] = self::validationError("{$fieldPrefix}.theory_page", 'Theory page must be an object.');
        }

        self::validatePositiveIntegerField($payload['theory_page_id'] ?? null, "{$fieldPrefix}.theory_page_id", $errors);
        self::validatePositiveIntegerListField($payload['theory_page_ids'] ?? null, "{$fieldPrefix}.theory_page_ids", $errors);

        $theoryPage = is_array($payload['theory_page'] ?? null) ? $payload['theory_page'] : [];

        self::validatePositiveIntegerField($theoryPage['id'] ?? null, "{$fieldPrefix}.theory_page.id", $errors);
        self::validateNormalizedStringField(
            $theoryPage['slug'] ?? null,
            "{$fieldPrefix}.theory_page.slug",
            $errors,
            fn (mixed $value) => self::normalizeSlug($value),
            'Slug must contain at least one valid slug segment.'
        );
        self::validateNormalizedStringField(
            $theoryPage['title'] ?? null,
            "{$fieldPrefix}.theory_page.title",
            $errors,
            fn (mixed $value) => self::normalizeText($value),
            'Title must be a non-empty string.'
        );
        self::validateNormalizedStringField(
            $theoryPage['category_slug_path'] ?? null,
            "{$fieldPrefix}.theory_page.category_slug_path",
            $errors,
            fn (mixed $value) => self::normalizeSlugPath($value),
            'Category slug path must contain at least one valid slug segment.'
        );
        self::validateNormalizedStringField(
            $theoryPage['page_seeder_class'] ?? null,
            "{$fieldPrefix}.theory_page.page_seeder_class",
            $errors,
            fn (mixed $value) => self::normalizeTrimmedString($value),
            'Page seeder class must be a non-empty string.'
        );
        self::validateNormalizedStringField(
            $theoryPage['url'] ?? null,
            "{$fieldPrefix}.theory_page.url",
            $errors,
            fn (mixed $value) => self::normalizeUrl($value),
            'URL must be a valid absolute URL.'
        );

        $normalized = self::normalizeStructuredPayload($payload);

        if (($normalized['source_type'] ?? null) !== 'theory_page') {
            $errors[] = self::validationError(
                "{$fieldPrefix}.source_type",
                'Only theory_page source_type is supported.'
            );
        }

        $normalizedTheoryPage = is_array($normalized['theory_page'] ?? null) ? $normalized['theory_page'] : [];

        if ($normalizedTheoryPage === []) {
            $errors[] = self::validationError(
                "{$fieldPrefix}.theory_page",
                'Theory page metadata must contain at least one non-empty field.'
            );
        }

        if (! self::hasTheoryPageIdentifier($normalized)) {
            $errors[] = self::validationError(
                "{$fieldPrefix}.theory_page",
                'Theory page link must include slug, page_seeder_class, url, or an id.'
            );
        }

        $topLevelId = self::normalizePositiveInteger($normalized['theory_page_id'] ?? null);
        $nestedId = self::normalizePositiveInteger(Arr::get($normalized, 'theory_page.id'));
        $pageIds = self::normalizePositiveIntegerList($normalized['theory_page_ids'] ?? null);

        if ($topLevelId !== null && $nestedId !== null && $topLevelId !== $nestedId) {
            $errors[] = self::validationError(
                "{$fieldPrefix}.theory_page_id",
                'theory_page_id must match theory_page.id when both are provided.'
            );
        }

        if ($topLevelId !== null && $pageIds !== [] && ! in_array($topLevelId, $pageIds, true)) {
            $errors[] = self::validationError(
                "{$fieldPrefix}.theory_page_ids",
                'theory_page_ids must contain theory_page_id when both are provided.'
            );
        }

        if ($nestedId !== null && $pageIds !== [] && ! in_array($nestedId, $pageIds, true)) {
            $errors[] = self::validationError(
                "{$fieldPrefix}.theory_page_ids",
                'theory_page_ids must contain theory_page.id when both are provided.'
            );
        }

        return [
            'normalized' => $normalized !== [] ? $normalized : null,
            'errors' => array_values(array_unique($errors, SORT_REGULAR)),
        ];
    }

    private static function coerceValue(string $value): mixed
    {
        if ($value === '') {
            return '';
        }

        if (
            (str_starts_with($value, '[') && str_ends_with($value, ']'))
            || (str_starts_with($value, '{') && str_ends_with($value, '}'))
        ) {
            try {
                return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                // Fall through to scalar coercion.
            }
        }

        $lower = strtolower($value);

        if ($lower === 'true') {
            return true;
        }

        if ($lower === 'false') {
            return false;
        }

        if ($lower === 'null') {
            return null;
        }

        if (preg_match('/^-?\d+$/', $value) === 1) {
            return (int) $value;
        }

        if (preg_match('/^-?\d+\.\d+$/', $value) === 1) {
            return (float) $value;
        }

        return $value;
    }

    private static function parseStringPayload(string $payload): mixed
    {
        $pairs = preg_split('/\s*;\s*/u', trim($payload), -1, PREG_SPLIT_NO_EMPTY);

        if (! is_array($pairs) || $pairs === []) {
            return $payload;
        }

        $normalized = [];
        $parsed = false;

        foreach ($pairs as $pair) {
            [$key, $value] = array_pad(explode('=', $pair, 2), 2, null);

            $key = trim((string) $key);

            if ($key === '' || $value === null) {
                continue;
            }

            Arr::set($normalized, $key, self::coerceValue(trim((string) $value)));
            $parsed = true;
        }

        return $parsed ? $normalized : $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private static function normalizeStructuredPayload(array $payload): array
    {
        if (! self::looksLikeTheoryPagePayload($payload)) {
            return $payload;
        }

        $sourceType = self::normalizeSourceType($payload['source_type'] ?? null);
        $theoryPageId = self::normalizePositiveInteger($payload['theory_page_id'] ?? null);
        $theoryPageIds = self::normalizePositiveIntegerList($payload['theory_page_ids'] ?? null);
        $theoryPagePayload = is_array($payload['theory_page'] ?? null) ? $payload['theory_page'] : [];
        $theoryPage = array_filter([
            'id' => self::normalizePositiveInteger($theoryPagePayload['id'] ?? null),
            'slug' => self::normalizeSlug($theoryPagePayload['slug'] ?? null),
            'title' => self::normalizeText($theoryPagePayload['title'] ?? null),
            'category_slug_path' => self::normalizeSlugPath($theoryPagePayload['category_slug_path'] ?? null),
            'page_seeder_class' => self::normalizeTrimmedString($theoryPagePayload['page_seeder_class'] ?? null),
            'url' => self::normalizeUrl($theoryPagePayload['url'] ?? null),
        ], fn (mixed $value) => ! self::isEmptyNormalizedValue($value));

        if ($sourceType === null && ($theoryPage !== [] || $theoryPageId !== null || $theoryPageIds !== [])) {
            $sourceType = 'theory_page';
        }

        return array_filter([
            'source_type' => $sourceType,
            'theory_page_id' => $theoryPageId,
            'theory_page_ids' => $theoryPageIds !== [] ? $theoryPageIds : null,
            'theory_page' => $theoryPage !== [] ? $theoryPage : null,
        ], fn (mixed $value) => ! self::isEmptyNormalizedValue($value));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private static function looksLikeTheoryPagePayload(array $payload): bool
    {
        $sourceType = self::normalizeSourceType($payload['source_type'] ?? null);

        if ($sourceType === 'theory_page') {
            return true;
        }

        return array_intersect(['theory_page', 'theory_page_id', 'theory_page_ids'], array_keys($payload)) !== [];
    }

    private static function normalizeSourceType(mixed $value): ?string
    {
        $normalized = self::normalizeTrimmedString($value);

        return $normalized !== null ? Str::lower($normalized) : null;
    }

    private static function normalizeTrimmedString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (! is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }

    private static function normalizeText(mixed $value): ?string
    {
        $normalized = self::normalizeTrimmedString($value);

        if ($normalized === null) {
            return null;
        }

        $normalized = preg_replace('/\s+/u', ' ', $normalized);

        return is_string($normalized) ? trim($normalized) : trim((string) $value);
    }

    private static function normalizeSlug(mixed $value): ?string
    {
        $normalized = self::normalizeTrimmedString($value);

        if ($normalized === null) {
            return null;
        }

        $slug = Str::slug($normalized);

        return $slug !== '' ? $slug : null;
    }

    private static function normalizeSlugPath(mixed $value): ?string
    {
        $normalized = self::normalizeTrimmedString($value);

        if ($normalized === null) {
            return null;
        }

        $segments = array_values(array_filter(array_map(
            fn (string $segment) => Str::slug($segment),
            preg_split('/[\/\\\\]+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: []
        )));

        return $segments !== [] ? implode('/', $segments) : null;
    }

    private static function normalizeUrl(mixed $value): ?string
    {
        $normalized = self::normalizeTrimmedString($value);

        if ($normalized === null) {
            return null;
        }

        return filter_var($normalized, FILTER_VALIDATE_URL) !== false ? $normalized : null;
    }

    private static function normalizePositiveInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }

        if (is_string($value) && preg_match('/^\d+$/', trim($value)) === 1) {
            $normalized = (int) trim($value);

            return $normalized > 0 ? $normalized : null;
        }

        if (is_float($value) && floor($value) === $value) {
            return $value > 0 ? (int) $value : null;
        }

        return null;
    }

    /**
     * @return array<int, int>
     */
    private static function normalizePositiveIntegerList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(
            fn (mixed $item) => self::normalizePositiveInteger($item),
            $value
        ))));
    }

    private static function isEmptyNormalizedValue(mixed $value): bool
    {
        return $value === null || $value === [] || $value === '';
    }

    /**
     * @param  array<string, mixed>  $normalized
     */
    private static function hasTheoryPageIdentifier(array $normalized): bool
    {
        $candidates = [
            $normalized['theory_page_id'] ?? null,
            Arr::get($normalized, 'theory_page.id'),
            Arr::get($normalized, 'theory_page.slug'),
            Arr::get($normalized, 'theory_page.page_seeder_class'),
            Arr::get($normalized, 'theory_page.url'),
        ];

        foreach ($candidates as $candidate) {
            if (! self::isEmptyNormalizedValue($candidate)) {
                return true;
            }
        }

        $pageIds = $normalized['theory_page_ids'] ?? null;

        return is_array($pageIds) && $pageIds !== [];
    }

    /**
     * @param  array<int, array{field: string, message: string}>  $errors
     */
    private static function validatePositiveIntegerField(mixed $value, string $field, array &$errors): void
    {
        if (! self::hasProvidedValue($value)) {
            return;
        }

        if (self::normalizePositiveInteger($value) === null) {
            $errors[] = self::validationError($field, 'Value must be a positive integer.');
        }
    }

    /**
     * @param  array<int, array{field: string, message: string}>  $errors
     */
    private static function validatePositiveIntegerListField(mixed $value, string $field, array &$errors): void
    {
        if (! self::hasProvidedValue($value)) {
            return;
        }

        if (! is_array($value)) {
            $errors[] = self::validationError($field, 'Value must be an array of positive integers.');

            return;
        }

        foreach ($value as $index => $item) {
            if (! self::hasProvidedValue($item)) {
                continue;
            }

            if (self::normalizePositiveInteger($item) === null) {
                $errors[] = self::validationError("{$field}.{$index}", 'Array items must be positive integers.');
            }
        }
    }

    /**
     * @param  array<int, array{field: string, message: string}>  $errors
     */
    private static function validateNormalizedStringField(
        mixed $value,
        string $field,
        array &$errors,
        callable $normalizer,
        string $message
    ): void {
        if (! self::hasProvidedValue($value)) {
            return;
        }

        if (! is_scalar($value) || $normalizer($value) === null) {
            $errors[] = self::validationError($field, $message);
        }
    }

    private static function hasProvidedValue(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        if (is_array($value)) {
            return $value !== [];
        }

        return true;
    }

    /**
     * @return array{field: string, message: string}
     */
    private static function validationError(string $field, string $message): array
    {
        return [
            'field' => $field,
            'message' => $message,
        ];
    }
}
