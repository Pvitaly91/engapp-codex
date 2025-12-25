<?php

namespace App\Services\Translate;

class TranslationProviderFactory
{
    public const ALLOWED_PROVIDERS = ['auto', 'openai', 'gemini'];

    /**
     * Create a translation provider based on the provider name.
     *
     * @param string $provider Provider name ('auto', 'openai' or 'gemini')
     * @return TranslationProviderInterface
     * @throws \InvalidArgumentException If provider is not supported
     * @throws \RuntimeException If auto-detection fails (no API keys configured)
     */
    public static function make(string $provider): TranslationProviderInterface
    {
        if (!in_array($provider, self::ALLOWED_PROVIDERS)) {
            throw new \InvalidArgumentException(
                "Invalid provider: {$provider}. Supported providers: " . implode(', ', self::ALLOWED_PROVIDERS)
            );
        }

        // Auto-detect provider based on available API keys
        if ($provider === 'auto') {
            $provider = self::detectProvider();
        }

        return match ($provider) {
            'openai' => new OpenAiTranslationProvider(),
            'gemini' => new GeminiTranslationProvider(),
        };
    }

    /**
     * Auto-detect provider based on available API keys.
     *
     * @return string Provider name ('openai' or 'gemini')
     * @throws \RuntimeException If no API keys are configured
     */
    public static function detectProvider(): string
    {
        $geminiKey = config('services.gemini.key');
        $openaiKey = config('services.openai.key');

        if (!empty($geminiKey)) {
            return 'gemini';
        }

        if (!empty($openaiKey)) {
            return 'openai';
        }

        throw new \RuntimeException(
            'No API key configured. Please set either GEMINI_API_KEY or CHAT_GPT_API_KEY in your .env file.'
        );
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
