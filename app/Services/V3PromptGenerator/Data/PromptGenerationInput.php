<?php

namespace App\Services\V3PromptGenerator\Data;

final readonly class PromptGenerationInput
{
    /**
     * @param  array<int, string>  $levels
     */
    public function __construct(
        public string $sourceType,
        public ?int $theoryPageId,
        public ?string $manualTopic,
        public ?string $externalUrl,
        public string $siteDomain,
        public string $targetNamespace,
        public array $levels,
        public int $questionsPerLevel,
        public string $generationMode,
        public string $promptAMode,
    ) {
    }

    public static function fromArray(array $validated): self
    {
        return new self(
            sourceType: (string) ($validated['source_type'] ?? 'manual_topic'),
            theoryPageId: isset($validated['theory_page_id']) ? (int) $validated['theory_page_id'] : null,
            manualTopic: self::normalizeNullableString($validated['manual_topic'] ?? null),
            externalUrl: self::normalizeNullableString($validated['external_url'] ?? null),
            siteDomain: trim((string) ($validated['site_domain'] ?? 'gramlyze.com')),
            targetNamespace: trim((string) ($validated['target_namespace'] ?? 'AI\\ChatGptPro')),
            levels: array_values(array_map(
                static fn ($level) => strtoupper(trim((string) $level)),
                $validated['levels'] ?? []
            )),
            questionsPerLevel: (int) ($validated['questions_per_level'] ?? 0),
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
