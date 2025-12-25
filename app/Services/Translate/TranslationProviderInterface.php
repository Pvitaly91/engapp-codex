<?php

namespace App\Services\Translate;

interface TranslationProviderInterface
{
    /**
     * Translate a batch of texts from source language to target language.
     *
     * @param array<string> $texts Array of texts to translate
     * @param string $source Source language code (e.g., 'en')
     * @param string $target Target language code (e.g., 'pl', 'uk')
     * @param array $options Additional options for translation
     * @return array<string> Array of translated texts in the same order as input
     * @throws \RuntimeException If translation fails
     */
    public function translateBatch(array $texts, string $source, string $target, array $options = []): array;

    /**
     * Get the provider name.
     *
     * @return string Provider name (e.g., 'openai', 'gemini')
     */
    public function getName(): string;
}
