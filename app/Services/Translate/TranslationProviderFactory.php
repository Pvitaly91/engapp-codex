<?php

namespace App\Services\Translate;

class TranslationProviderFactory
{
    public const ALLOWED_PROVIDERS = ['openai', 'gemini'];

    /**
     * Create a translation provider based on the provider name.
     *
     * @param string $provider Provider name ('openai' or 'gemini')
     * @return TranslationProviderInterface
     * @throws \InvalidArgumentException If provider is not supported
     */
    public static function make(string $provider): TranslationProviderInterface
    {
        if (!in_array($provider, self::ALLOWED_PROVIDERS)) {
            throw new \InvalidArgumentException(
                "Invalid provider: {$provider}. Supported providers: " . implode(', ', self::ALLOWED_PROVIDERS)
            );
        }

        return match ($provider) {
            'openai' => new OpenAiTranslationProvider(),
            'gemini' => new GeminiTranslationProvider(),
        };
    }

    /**
     * Get list of allowed providers.
     *
     * @return array<string>
     */
    public static function getAllowedProviders(): array
    {
        return self::ALLOWED_PROVIDERS;
    }
}
