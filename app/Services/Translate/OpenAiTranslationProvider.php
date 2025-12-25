<?php

namespace App\Services\Translate;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiTranslationProvider implements TranslationProviderInterface
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
        $this->apiKey = config('services.openai.key');
        $this->model = config('services.openai.model', 'gpt-4o-mini');
        $this->timeout = config('services.openai.timeout', 60);
        $this->maxRetries = config('services.openai.max_retries', 3);

        if (empty($this->apiKey)) {
            throw new \RuntimeException('OPENAI_API_KEY (or CHAT_GPT_API_KEY) is not configured. Please set it in your .env file.');
        }
    }

    public function getName(): string
    {
        return 'openai';
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
                ->retry($this->maxRetries, 2000, function ($exception, $request) {
                    if ($exception instanceof \Illuminate\Http\Client\RequestException) {
                        if ($exception->response && $exception->response->status() === 429) {
                            return true;
                        }
                    }
                    return false;
                }, true)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a professional translator specializing in UI/interface translations. Always respond with valid JSON only. Never add explanations or comments.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 4096,
                ]);

            if ($response->successful()) {
                $result = $response->json();
                $text = $result['choices'][0]['message']['content'] ?? '';
                
                return $this->parseResponse($text, count($texts));
            } else {
                $statusCode = $response->status();
                $errorBody = $response->body();
                $errorMsg = "OpenAI API error (HTTP {$statusCode}): " . substr($errorBody, 0, 500);
                Log::error($errorMsg);
                throw new \RuntimeException($errorMsg);
            }
        } catch (\Exception $e) {
            Log::error("OpenAI API call failed: " . $e->getMessage());
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
            $errorMsg = "Failed to parse OpenAI response as JSON. Response: " . substr($text, 0, 200);
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
