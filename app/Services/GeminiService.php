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
                Log::warning('Gemini request failed: ' . $response->body());
                return null;
            }

            $data = $response->json();

            // типовий шлях до тексту
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        } catch (Exception $e) {
            Log::warning('Gemini request failed: ' . $e->getMessage());
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
        $lang = "ua";
        $prompt = "Give a short hint in {$lang} on how to construct the following sentence (but do not provide a correct statement, just the formula by which the sentence is constructed):\n{$question}";

        return $this->request($prompt) ?? '';
    }

    public function determineDifficulty(string $question): string
    {
        $prompt = "Question: {$question}\n" .
            "Classify its CEFR difficulty level (A1, A2, B1, B2, C1, C2).\n" .
            "Respond with one level code.";

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
        $prompt = "Question: {$question}\n" .
            "Choose all appropriate tenses from this list: {$tagsList}. that apply only to the {missing words}.\n" .
            "Respond with a comma-separated list of tag names.";


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
        $prompt = "Generate {$numQuestions} short English grammar questions for the following tenses: {$tensesText}. Each question must contain {$answersCount} missing word(s) represented as {a1}, {a2}, ... . Provide the base form of each missing verb as verb_hints. Respond strictly in JSON format like: [{\"question\":\"He {a1} ...\", \"answers\":{\"a1\":\"goes\"}, \"verb_hints\":{\"a1\":\"go\"}}].";

        $text = $this->request($prompt);
        if (!$text) {
            return [];
        }

        $data = json_decode($text, true);
        if (!is_array($data)) {
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
            $prompt .= ($i + 1) . ". {$q}\n";
        }
        $prompt .= "Напиши формули по яких утворюються речення для ціх часів, вивиди відформатований текст готовий для використання на html сторінці (ВАЖЛИВО! не потрібно писати технічні  слова: HTML код і т. д. потрібни текст готовий відформатований для читання користувачем на строінці, також приладу придумай свої приклади, та не використовуй питання з тесту в якості прикладу, назву часів та ключові слова англійською виділяй жирним та іншим кольором ) ";

        return $this->request($prompt) ?? '';
    }
}
