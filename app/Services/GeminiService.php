<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $endpoint = 'https://generativelanguage.googleapis.com/v1beta';

    protected function request(string $prompt): ?string
    {
        $key = config('services.gemini.key');
        $model = config('services.gemini.model', 'gemini-2.5-flash'); // << важливо: актуальна модель
        if (empty($key)) {
            Log::warning('Gemini API key not configured');

            return null;
        }

        $url = "{$this->endpoint}/models/{$model}:generateContent?key={$key}";

        try {
            $payload = [
                'contents' => [[
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ]],
            ];

            $response = Http::timeout(30)
                ->retry(2, 300)  // легка повторна спроба на мережеві збої
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            if ($response->failed()) {
                Log::warning('Gemini request failed: '.$response->body());

                return null;
            }

            $data = $response->json();

            // типовий шлях до тексту
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        } catch (Exception $e) {
            Log::warning('Gemini request failed: '.$e->getMessage());

            return null;
        }
    }

    public function explainWrongAnswer(string $question, string $wrongAnswer, string $correctAnswer, ?string $lang = null): string
    {
        $lang = $lang ?? 'uk';
        $prompt = "Question: {$question}\nWrong answer: {$wrongAnswer}\nCorrect answer: {$correctAnswer}\nExplain in 1-2 sentences in {$lang} why the wrong answer is incorrect.";

        return $this->request($prompt) ?? '';
    }

    public function hintSentenceStructure(string $question, string $lang = 'uk'): string
    {
        $lang = 'ua';
        $prompt = "Give a short hint in {$lang} on how to construct the following sentence (but do not provide a correct statement, just the formula by which the sentence is constructed):\n{$question}";

        return $this->request($prompt) ?? '';
    }

    public function determineDifficulty(string $question): string
    {
        $prompt = "Question: {$question}\n".
            "Classify its CEFR difficulty level (A1, A2, B1, B2, C1, C2).\n".
            'Respond with one level code.';

        $response = $this->request($prompt);
        if (! $response) {
            return '';
        }

        if (preg_match('/A1|A2|B1|B2|C1|C2/', $response, $m)) {
            return $m[0];
        }

        return '';
    }

    public function determineTenseTags(string $question, array $tags): array
    {
        if (empty($tags)) {
            return [];
        }

        $tagsList = implode(', ', $tags);
        $prompt = "Question: {$question}\n".
            "Choose all appropriate tenses from this list: {$tagsList}. that apply only to the {missing words}.\n".
            'Respond with a comma-separated list of tag names.';

        $response = $this->request($prompt);
        if (! $response) {
            return [];
        }

        $parts = array_filter(array_map('trim', explode(',', $response)));

        return array_values(array_intersect($tags, $parts));
    }

    public function generateGrammarQuestions(array $tenses, int $numQuestions = 1, int $answersCount = 1): array
    {
        $answersCount = max(1, min(10, $answersCount));
        $tensesText = implode(', ', $tenses);
        $prompt = "Generate {$numQuestions} short English grammar questions for the following tenses: {$tensesText}. Each question must contain {$answersCount} missing word(s) represented as {a1}, {a2}, ... . Provide the base form of each missing verb as verb_hints. Include a CEFR level for each question as \\\"level\\\" (A1, A2, B1, B2, C1, or C2) and the grammatical tense as \\\"tense\\\" (e.g., Present Simple). Respond strictly in JSON format like: [{\\\"question\\\":\\\"He {a1} ...\\\", \\\"answers\\\":{\\\"a1\\\":\\\"goes\\\"}, \\\"verb_hints\\\":{\\\"a1\\\":\\\"go\\\"}, \\\"level\\\":\\\"B1\\\", \\\"tense\\\":\\\"Present Simple\\\"}].";

        $text = $this->request($prompt);
        if (! $text) {
            return [];
        }

        $data = json_decode($text, true);
        if (! is_array($data)) {
            $start = strpos($text, '[');
            $end = strrpos($text, ']');
            if ($start !== false && $end !== false && $end > $start) {
                $json = substr($text, $start, $end - $start + 1);
                $data = json_decode($json, true);
            }
        }

        return is_array($data) ? $data : [];
    }

    public function generateGrammarQuestion(array $tenses, int $answersCount = 1): ?array
    {
        $all = $this->generateGrammarQuestions($tenses, 1, $answersCount);

        return $all[0] ?? null;
    }

    public function generateTestDescription(array $questions, ?string $lang = null): string
    {
        $lang = $lang ?? 'uk';
        $prompt = 'Визнач які часи використувуються в питаннях цього тесту, питання:';
        foreach ($questions as $i => $q) {
            $prompt .= ($i + 1).". {$q}\n";
        }
        $prompt .= 'Напиши формули по яких утворюються речення для ціх часів, вивиди відформатований текст готовий для використання на html сторінці (ВАЖЛИВО! не потрібно писати технічні  слова: HTML код і т. д. потрібни текст готовий відформатований для читання користувачем на строінці, також приладу придумай свої приклади, та не використовуй питання з тесту в якості прикладу, назву часів та ключові слова англійською виділяй жирним та іншим кольором ) ';

        return $this->request($prompt) ?? '';
    }

    public function suggestTagAggregations(array $tags): array
    {
        if (empty($tags)) {
            $error = 'Не передано жодного тегу для аналізу';
            Log::warning('Gemini suggestTagAggregations: No tags provided');
            throw new \RuntimeException($error);
        }

        $tagsList = implode(', ', $tags);
        $prompt = "You are a grammar tag analyzer. Analyze the following list of English grammar tags and suggest aggregations.\n\n";
        $prompt .= "Tags: {$tagsList}\n\n";
        $prompt .= "Group similar or related tags together. For each group, identify:\n";
        $prompt .= "1. A main_tag (the most general or commonly used tag in the group)\n";
        $prompt .= "2. similar_tags (array of related tags that should be aggregated under the main tag)\n\n";
        $prompt .= "Rules:\n";
        $prompt .= "- Only group tags that are clearly related or synonyms\n";
        $prompt .= "- Each tag should appear only once in the result\n";
        $prompt .= "- Don't create aggregations for tags that are clearly distinct\n";
        $prompt .= "- similar_tags should not include the main_tag itself\n\n";
        $prompt .= "Respond strictly in JSON format as an array of objects:\n";
        $prompt .= '[{"main_tag": "Present Simple", "similar_tags": ["Simple Present", "Present Tense"]}, ...]';

        $response = $this->request($prompt);
        if (! $response) {
            $error = 'Не отримано відповіді від Gemini API. Можливо, не налаштовано API ключ або досягнуто ліміт запитів.';
            Log::warning('Gemini suggestTagAggregations: No response from API');
            throw new \RuntimeException($error);
        }

        // Try to parse JSON response
        $data = json_decode($response, true);
        if (! is_array($data)) {
            // Try to extract JSON from response
            $start = strpos($response, '[');
            $end = strrpos($response, ']');
            if ($start !== false && $end !== false && $end > $start) {
                $json = substr($response, $start, $end - $start + 1);
                $data = json_decode($json, true);
            }
        }

        if (! is_array($data)) {
            $error = 'Gemini повернув некоректну відповідь (не JSON). Відповідь: '.substr($response, 0, 200).'...';
            Log::warning('Gemini suggestTagAggregations: Invalid JSON response', ['response' => substr($response, 0, 500)]);
            throw new \RuntimeException($error);
        }

        // Validate and filter the aggregations
        $validAggregations = [];
        foreach ($data as $aggregation) {
            if (
                ! is_array($aggregation) ||
                ! isset($aggregation['main_tag']) ||
                ! isset($aggregation['similar_tags']) ||
                ! is_array($aggregation['similar_tags']) ||
                empty($aggregation['similar_tags'])
            ) {
                continue;
            }

            // Verify that all tags exist in the original list
            $mainTag = $aggregation['main_tag'];
            if (! in_array($mainTag, $tags)) {
                continue;
            }

            $similarTags = array_filter($aggregation['similar_tags'], function ($tag) use ($tags, $mainTag) {
                return in_array($tag, $tags) && $tag !== $mainTag;
            });

            if (! empty($similarTags)) {
                $validAggregations[] = [
                    'main_tag' => $mainTag,
                    'similar_tags' => array_values($similarTags),
                ];
            }
        }

        if (empty($validAggregations)) {
            $error = 'Gemini не знайшов схожих тегів для агрегації. Можливо, всі теги занадто різні або відповідь не містила валідних груп.';
            Log::warning('Gemini suggestTagAggregations: No valid aggregations found in response');
            throw new \RuntimeException($error);
        }

        return $validAggregations;
    }
}
