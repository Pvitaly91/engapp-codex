<?php

namespace App\Services\PageV3PromptGenerator\Data;

final readonly class PagePromptGenerationInput
{
    public function __construct(
        public string $sourceType,
        public ?string $manualTopic,
        public ?string $externalUrl,
        public string $categoryMode,
        public ?int $existingCategoryId,
        public ?string $newCategoryTitle,
        public string $generationMode,
        public string $promptAMode,
    ) {
    }

    public static function fromArray(array $validated): self
    {
        return new self(
            sourceType: (string) ($validated['source_type'] ?? 'manual_topic'),
            manualTopic: self::normalizeNullableString($validated['manual_topic'] ?? null),
            externalUrl: self::normalizeNullableString($validated['external_url'] ?? null),
            categoryMode: (string) ($validated['category_mode'] ?? 'existing'),
            existingCategoryId: isset($validated['existing_category_id']) ? (int) $validated['existing_category_id'] : null,
            newCategoryTitle: self::normalizeNullableString($validated['new_category_title'] ?? null),
            generationMode: (string) ($validated['generation_mode'] ?? 'single'),
            promptAMode: (string) ($validated['prompt_a_mode'] ?? 'repository_connected'),
        );
    }

    private static function normalizeNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }
}
