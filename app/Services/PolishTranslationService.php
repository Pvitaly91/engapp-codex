<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PolishTranslationService
{
    private array $cache = [];
    private string $apiKey;
    private string $model;
    private int $maxRetries = 3;
    private int $timeout = 60;

    public function __construct()
    {
        // Use Gemini as it's more reliable for translations
        $this->apiKey = config('services.gemini.key');
        $this->model = config('services.gemini.model', 'gemini-2.0-flash-exp');
    }

    /**
     * Translate a batch of English words to Polish
     *
     * @param array $words Array of words to translate
     * @return array Associative array [word => translation]
     */
    public function translateBatch(array $words): array
    {
        if (empty($words)) {
            return [];
        }

        // Check cache first
        $uncachedWords = [];
        $results = [];

        foreach ($words as $word) {
            $cacheKey = strtolower(trim($word));
            if (isset($this->cache[$cacheKey])) {
                $results[$word] = $this->cache[$cacheKey];
            } else {
                $uncachedWords[] = $word;
            }
        }

        if (empty($uncachedWords)) {
            return $results;
        }

        // Translate uncached words
        $translations = $this->callGeminiApi($uncachedWords);

        // Merge with cached results
        foreach ($translations as $word => $translation) {
            $results[$word] = $translation;
            $this->cache[strtolower(trim($word))] = $translation;
        }

        return $results;
    }

    /**
     * Validate if translation is proper Polish and not just transliteration
     *
     * @param string $word English word
     * @param string $translation Polish translation
     * @return array ['valid' => bool, 'translation' => string|null]
     */
    public function validateTranslation(string $word, string $translation): array
    {
        // Check if translation is same as word (case-insensitive)
        if (strtolower(trim($translation)) === strtolower(trim($word))) {
            // It might be a proper loanword, verify with API
            $verification = $this->verifyLoanword($word, $translation);
            return $verification;
        }

        return ['valid' => true, 'translation' => $translation];
    }

    /**
     * Verify if a word is a proper Polish loanword
     */
    private function verifyLoanword(string $word, string $translation): array
    {
        $prompt = "Is '{$word}' used in Polish as '{$translation}' (a standard loanword)? " .
                  "Answer only 'YES' if it's a commonly used Polish loanword (like 'pizza', 'radio', 'internet'), " .
                  "or provide the correct Polish translation if it should be translated differently. " .
                  "One word/phrase only.";

        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->maxRetries, 1000)
                ->post('https://generativelanguage.googleapis.com/v1beta/models/' . $this->model . ':generateContent?key=' . $this->apiKey, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.3,
                        'maxOutputTokens' => 50,
                    ]
                ]);

            if ($response->successful()) {
                $result = $response->json();
                $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
                $text = trim($text);

                if (strtoupper(substr($text, 0, 3)) === 'YES') {
                    return ['valid' => true, 'translation' => $translation];
                } else {
                    // If not YES, the text should be the correct translation
                    $corrected = $this->cleanTranslation($text);
                    return ['valid' => true, 'translation' => $corrected];
                }
            }
        } catch (\Exception $e) {
            Log::warning("Loanword verification failed for '{$word}': " . $e->getMessage());
        }

        // On error, mark as invalid
        return ['valid' => false, 'translation' => null];
    }

    /**
     * Call Gemini API to translate words
     */
    private function callGeminiApi(array $words): array
    {
        $wordsJson = json_encode($words, JSON_UNESCAPED_UNICODE);
        
        $prompt = "Translate these English words to Polish. Follow these rules strictly:\n" .
                  "1. Provide actual Polish translations, NOT transliterations\n" .
                  "2. For nouns: use base form (singular, nominative case)\n" .
                  "3. For verbs: use infinitive form\n" .
                  "4. For phrases: translate naturally preserving meaning\n" .
                  "5. For proper names/brands (like 'Google', 'London'): keep as is\n" .
                  "6. For loanwords naturally used in Polish (like 'pizza', 'radio'): you may keep them\n" .
                  "7. Return ONLY a JSON object mapping each English word to its Polish translation\n" .
                  "8. Each translation must be a single word or short phrase, no explanations, no slashes, no alternatives\n\n" .
                  "Words to translate: {$wordsJson}\n\n" .
                  "Response format: {\"word1\": \"translation1\", \"word2\": \"translation2\"}";

        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->maxRetries, 2000)
                ->post('https://generativelanguage.googleapis.com/v1beta/models/' . $this->model . ':generateContent?key=' . $this->apiKey, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.3,
                        'maxOutputTokens' => 2048,
                    ]
                ]);

            if ($response->successful()) {
                $result = $response->json();
                $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
                
                // Extract JSON from response (might be wrapped in markdown)
                $text = trim($text);
                $text = preg_replace('/^```json\s*/m', '', $text);
                $text = preg_replace('/^```\s*/m', '', $text);
                $text = trim($text);
                
                $translations = json_decode($text, true);
                
                if (is_array($translations)) {
                    // Clean translations
                    $cleaned = [];
                    foreach ($translations as $word => $translation) {
                        $cleaned[$word] = $this->cleanTranslation($translation);
                    }
                    return $cleaned;
                }
                
                Log::warning("Failed to parse Gemini response as JSON: " . $text);
            } else {
                Log::error("Gemini API error: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Gemini API call failed: " . $e->getMessage());
        }

        // Return empty translations on failure
        return array_fill_keys($words, null);
    }

    /**
     * Clean translation text
     */
    private function cleanTranslation(?string $translation): ?string
    {
        if ($translation === null || trim($translation) === '') {
            return null;
        }

        $translation = trim($translation);
        
        // Remove quotes
        $translation = trim($translation, '"\'""''');
        
        // Remove explanations in parentheses
        $translation = preg_replace('/\s*\([^)]*\)\s*/', '', $translation);
        
        // Remove explanations after dash or colon
        $translation = preg_replace('/\s*[-:–—]\s.*$/', '', $translation);
        
        // Take only first option if multiple provided
        if (strpos($translation, '/') !== false) {
            $parts = explode('/', $translation);
            $translation = trim($parts[0]);
        }
        
        $translation = trim($translation);
        
        return $translation !== '' ? $translation : null;
    }

    /**
     * Get cached translations
     */
    public function getCache(): array
    {
        return $this->cache;
    }

    /**
     * Load cache from array
     */
    public function loadCache(array $cache): void
    {
        $this->cache = $cache;
    }
}
