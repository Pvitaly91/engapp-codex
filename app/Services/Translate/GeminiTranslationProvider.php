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

    // Content type constants
    private const CONTENT_TYPE_JSON = 'json';
    private const CONTENT_TYPE_HTML = 'html';
    private const CONTENT_TYPE_PLAIN = 'plain';

    public function __construct()
    {
        $this->apiKey = trim(config('services.gemini.key'));
        $this->model = config('services.gemini.model', 'gemini-2.0-flash-exp');
        // Increased default timeout for large content
        $this->timeout = config('services.gemini.timeout', 120);
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

        // Detect content types and build optimized request
        $contentAnalysis = $this->analyzeContent($texts);

        // Build numbered list for better tracking
        $numberedTexts = [];
        foreach (array_values($texts) as $index => $text) {
            $numberedTexts[$index] = $text;
        }

        $textsJson = json_encode($numberedTexts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // Build optimized prompt based on content types
        $prompt = $this->buildPrompt($sourceLang, $targetLang, $textsJson, $contentAnalysis);

        // Calculate dynamic max_tokens based on input size
        $inputLength = strlen($textsJson);
        $maxTokens = $this->calculateMaxTokens($inputLength, $contentAnalysis);

        // Calculate dynamic timeout based on content complexity
        $dynamicTimeout = $this->calculateTimeout($inputLength, $contentAnalysis);

        try {
            $response = Http::timeout($dynamicTimeout)
                ->retry($this->maxRetries, 3000, function ($exception, $request) {
                    // Also retry on timeout
                    if ($exception instanceof \Illuminate\Http\Client\ConnectionException) {
                        return true;
                    }
                    return false;
                }, true)
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
                        'maxOutputTokens' => $maxTokens,
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

    /**
     * Analyze content to detect types (JSON, HTML, plain text)
     */
    private function analyzeContent(array $texts): array
    {
        $hasJson = false;
        $hasHtml = false;
        $hasPlain = false;
        $totalLength = 0;
        $jsonCount = 0;
        $htmlCount = 0;

        foreach ($texts as $text) {
            $totalLength += strlen($text);
            $type = $this->detectContentType($text);
            
            switch ($type) {
                case self::CONTENT_TYPE_JSON:
                    $hasJson = true;
                    $jsonCount++;
                    break;
                case self::CONTENT_TYPE_HTML:
                    $hasHtml = true;
                    $htmlCount++;
                    break;
                default:
                    $hasPlain = true;
            }
        }

        return [
            'hasJson' => $hasJson,
            'hasHtml' => $hasHtml,
            'hasPlain' => $hasPlain,
            'totalLength' => $totalLength,
            'jsonCount' => $jsonCount,
            'htmlCount' => $htmlCount,
            'count' => count($texts),
        ];
    }

    /**
     * Detect the content type of a single text
     */
    private function detectContentType(string $text): string
    {
        $trimmed = trim($text);
        
        // Check for JSON (starts with { or [)
        if (preg_match('/^[\{\[]/', $trimmed)) {
            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return self::CONTENT_TYPE_JSON;
            }
        }

        // Check for HTML tags
        if (preg_match('/<[a-z][\s\S]*>/i', $trimmed)) {
            return self::CONTENT_TYPE_HTML;
        }

        return self::CONTENT_TYPE_PLAIN;
    }

    /**
     * Calculate dynamic max_tokens based on input size and content type
     */
    private function calculateMaxTokens(int $inputLength, array $contentAnalysis): int
    {
        // Base calculation: roughly 4 characters per token, output usually similar size to input
        $estimatedTokens = (int) ceil($inputLength / 3); // slightly more generous
        
        // Add buffer for JSON structure overhead
        if ($contentAnalysis['hasJson']) {
            $estimatedTokens = (int) ($estimatedTokens * 1.3);
        }

        // Minimum 2048, maximum 16384
        return max(2048, min(16384, $estimatedTokens));
    }

    /**
     * Calculate dynamic timeout based on content complexity
     */
    private function calculateTimeout(int $inputLength, array $contentAnalysis): int
    {
        // Base timeout
        $timeout = $this->timeout;

        // Add time for JSON content (more complex to process)
        if ($contentAnalysis['hasJson']) {
            $timeout += 30;
        }

        // Add time for HTML content
        if ($contentAnalysis['hasHtml']) {
            $timeout += 20;
        }

        // Add time for large content (10 seconds per 10KB)
        $timeout += (int) ($inputLength / 10000) * 10;

        // Cap at 300 seconds (5 minutes)
        return min(300, $timeout);
    }

    private function buildPrompt(string $sourceLang, string $targetLang, string $textsJson, array $contentAnalysis): string
    {
        $rules = [
            "1. Return a JSON object with the same numeric keys and translated values",
            "2. PRESERVE ALL PLACEHOLDERS EXACTLY: :name, :count, {0}, {1}, [2,*], %s, %d, etc.",
            "3. For pluralization strings with | separator: translate each segment separately, keep the | and prefix markers",
            "4. DO NOT translate URLs (http://, https://)",
            "5. DO NOT translate technical identifiers or keys",
        ];

        // Add JSON-specific rules
        if ($contentAnalysis['hasJson']) {
            $rules[] = "6. FOR JSON CONTENT: The input may contain JSON strings. Translate ONLY the human-readable text values inside the JSON. Preserve all JSON structure, keys, and technical values exactly. Only translate strings that are meant for users to read (like 'title', 'description', 'text', 'label', 'items' text values).";
            $rules[] = "7. JSON keys like 'title', 'items', 'description' should NOT be translated - only their STRING VALUES should be translated.";
        }

        // Add HTML-specific rules
        if ($contentAnalysis['hasHtml']) {
            $rules[] = ($contentAnalysis['hasJson'] ? "8" : "6") . ". FOR HTML CONTENT: Preserve ALL HTML tags exactly (<div>, <span>, <p>, <strong>, <em>, <a href=\"...\">, etc.). Only translate the text content between tags.";
        }

        $rulesText = implode("\n", $rules);

        return <<<PROMPT
Translate the following content from {$sourceLang} to {$targetLang}.

CRITICAL RULES:
{$rulesText}

Input (JSON object with numeric keys, values may be plain text, HTML, or JSON strings):
{$textsJson}

Output: Return ONLY a valid JSON object with the same numeric keys and translated values.
Example input: {"0": "Hello", "1": "<p>World</p>", "2": "{\"title\": \"Привіт\"}"}
Example output: {"0": "Привіт", "1": "<p>Світ</p>", "2": "{\"title\": \"Hello\"}"}
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
