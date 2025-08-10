<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected function request(string $prompt): ?string
    {
        $key = config('services.gemini.key');
        if (empty($key)) {
            Log::warning('Gemini API key not configured');
            return null;
        }

        try {
            $response = Http::post(
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $key,
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                ]
            );

            if ($response->failed()) {
                Log::warning('Gemini request failed: ' . $response->body());
                return null;
            }

            $data = $response->json();
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
}
