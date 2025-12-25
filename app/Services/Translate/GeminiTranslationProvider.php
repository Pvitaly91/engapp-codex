<?php

namespace App\Services\Translate;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiTranslationProvider implements TranslationProviderInterface
{
    private string $apiKey;
    private string $model;
    private int $timeout;
    private int $maxRetries;

    private const LANGUAGE_NAMES = [
        'uk' => 'Ukrainian',
        'pl' => 'Polish',
        'en' => 'English',
        'de' => 'German',
        'fr' => 'French',
        'es' => 'Spanish',
        'it' => 'Italian',
    ];

    public function __construct()
    {
        $this->apiKey = trim(config('services.gemini.key'));
        $this->model = config('services.gemini.model', 'gemini-2.0-flash-exp');
        $this->timeout = config('services.gemini.timeout', 60);
        $this->maxRetries = config('services.gemini.max_retries', 3);

        if (empty($this->apiKey)) {
            throw new \RuntimeException('GEMINI_API_KEY is not configured. Please set it in your .env file.');
        }
    }

    public function getName(): string
    {
        return 'gemini';
    }

    public function translateBatch(array $texts, string $source, string $target, array $options = []): array
    {
        if (empty($texts)) {
            return [];
        }

        $sourceLang = $this->getLanguageName($source);
        $targetLang = $this->getLanguageName($target);

        // Build numbered list for better tracking
        $numberedTexts = [];
        foreach (array_values($texts) as $index => $text) {
            $numberedTexts[$index] = $text;
        }

        $textsJson = json_encode($numberedTexts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $prompt = $this->buildPrompt($sourceLang, $targetLang, $textsJson);

        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->maxRetries, 2000)
                ->post('https://generativelanguage.googleapis.com/v1beta/models/' . $this->model . ':generateContent?key=' . urlencode($this->apiKey), [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.3,
                        'maxOutputTokens' => 4096,
                    ]
                ]);

            if ($response->successful()) {
                $result = $response->json();
                $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
                
                return $this->parseResponse($text, count($texts));
            } else {
                $statusCode = $response->status();
                $errorBody = $response->body();
                $errorMsg = "Gemini API error (HTTP {$statusCode}): " . substr($errorBody, 0, 500);
                Log::error($errorMsg);
                throw new \RuntimeException($errorMsg);
            }
        } catch (\Exception $e) {
            Log::error("Gemini API call failed: " . $e->getMessage());
            throw $e;
        }
    }

    private function buildPrompt(string $sourceLang, string $targetLang, string $textsJson): string
    {
        return <<<PROMPT
Translate the following UI strings from {$sourceLang} to {$targetLang}.

CRITICAL RULES:
1. Translate ONLY the text values, keeping the same array indices
2. PRESERVE ALL PLACEHOLDERS EXACTLY as they appear:
   - Laravel placeholders: :name, :count, :attribute, :date, :value, :min, :max, etc.
   - Numbered placeholders: {0}, {1}, [2,*], etc.
   - Printf placeholders: %s, %d, %f, etc.
3. For pluralization strings (containing |):
   - Translate each segment separately
   - Keep the | separator
   - Preserve prefix markers like {0}, {1}, [2,*]
   Example: "{0} No items|{1} One item|[2,*] :count items" stays as "{0} ...|{1} ...|[2,*] :count ..."
4. PRESERVE HTML tags and Markdown formatting
5. DO NOT translate URLs (http://, https://)
6. DO NOT translate technical identifiers

Input (JSON object with numeric keys):
{$textsJson}

Output format: Return a JSON object with the same numeric keys and translated values.
Example: {"0": "translated text 1", "1": "translated text 2"}
PROMPT;
    }

    private function parseResponse(string $text, int $expectedCount): array
    {
        $text = trim($text);
        
        // Remove markdown code blocks if present
        $text = preg_replace('/^```(?:json)?\s*/m', '', $text);
        $text = preg_replace('/```\s*$/m', '', $text);
        $text = trim($text);

        $translations = json_decode($text, true);

        if (!is_array($translations)) {
            $errorMsg = "Failed to parse Gemini response as JSON. Response: " . substr($text, 0, 200);
            Log::warning($errorMsg);
            throw new \RuntimeException($errorMsg);
        }

        // Convert numeric keys to sequential array
        $result = [];
        for ($i = 0; $i < $expectedCount; $i++) {
            $result[$i] = $translations[$i] ?? $translations[(string)$i] ?? null;
        }

        return $result;
    }

    private function getLanguageName(string $code): string
    {
        return self::LANGUAGE_NAMES[$code] ?? ucfirst($code);
    }
}
