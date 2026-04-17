<?php

namespace App\Services\PageV3PromptGenerator\Data;

use App\Support\TheoryVariantPayloadSanitizer;

final readonly class TheoryVariantPromptGenerationInput
{
    public function __construct(
        public ?string $sourceLookupUrl,
        public string $targetType,
        public string $targetCategorySlug,
        public ?string $targetPageSlug,
        public string $locale,
        public ?string $targetNamespace,
        public ?string $className,
        public ?string $variantKey,
        public ?string $label,
        public ?string $provider,
        public ?string $model,
        public ?string $promptVersion,
        public ?string $sourceUrl,
        public ?string $sourcePageTitle,
        public ?string $sourceCategoryTitle,
        public ?string $sourcePageSeederClass,
        public ?string $targetLearnerLevels,
        public ?string $tone,
        public ?string $rewriteGoal,
        public ?string $contentStrategy,
        public ?string $mustCoverList,
        public ?string $avoidList,
        public ?string $editorNotes,
        public string $outputModePreference,
    ) {
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            sourceLookupUrl: self::normalizeNullableString($validated['source_lookup_url'] ?? null),
            targetType: (string) ($validated['target_type'] ?? 'page'),
            targetCategorySlug: (string) ($validated['target_category_slug'] ?? ''),
            targetPageSlug: self::normalizeNullableString($validated['target_page_slug'] ?? null),
            locale: TheoryVariantPayloadSanitizer::sanitizeLocale((string) ($validated['locale'] ?? 'uk')),
            targetNamespace: self::normalizeNullableString($validated['namespace'] ?? null),
            className: self::normalizeNullableString($validated['class_name'] ?? null),
            variantKey: self::normalizeNullableString($validated['variant_key'] ?? null),
            label: self::normalizeNullableString($validated['label'] ?? null),
            provider: self::normalizeNullableString($validated['provider'] ?? null),
            model: self::normalizeNullableString($validated['model'] ?? null),
            promptVersion: self::normalizeNullableString($validated['prompt_version'] ?? null),
            sourceUrl: self::normalizeNullableString($validated['source_url'] ?? null),
            sourcePageTitle: self::normalizeNullableString($validated['source_page_title'] ?? null),
            sourceCategoryTitle: self::normalizeNullableString($validated['source_category_title'] ?? null),
            sourcePageSeederClass: self::normalizeNullableString($validated['source_page_seeder_class'] ?? null),
            targetLearnerLevels: self::normalizeNullableString($validated['target_learner_levels'] ?? null),
            tone: self::normalizeNullableString($validated['tone'] ?? null),
            rewriteGoal: self::normalizeNullableString($validated['rewrite_goal'] ?? null),
            contentStrategy: self::normalizeNullableString($validated['content_strategy'] ?? null),
            mustCoverList: self::normalizeNullableString($validated['must_cover_list'] ?? null),
            avoidList: self::normalizeNullableString($validated['avoid_list'] ?? null),
            editorNotes: self::normalizeNullableString($validated['editor_notes'] ?? null),
            outputModePreference: (string) ($validated['output_mode_preference'] ?? 'downloadable_php_file'),
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
