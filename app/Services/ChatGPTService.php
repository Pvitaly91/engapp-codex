<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\ChatGPTExplanation;

class ChatGPTService
{
    public function explainWrongAnswer(string $question, string $wrongAnswer, string $correctAnswer, ?string $lang = null): string
    {
        $key = config('services.chatgpt.key');
        if (empty($key)) {
            Log::warning('ChatGPT API key not configured');
            return '';
        }

        $lang = "ua";//$lang ?? config('services.chatgpt.language', 'ua');

        $cached = ChatGPTExplanation::where('question', $question)
            ->where('wrong_answer', $wrongAnswer)
            ->where('correct_answer', $correctAnswer)
            ->where('language', $lang)
            ->first();

        if ($cached) {
            return $cached->explanation;
        }

        $prompt = "Question: {$question}\nWrong answer: {$wrongAnswer}\nCorrect answer: {$correctAnswer}\nExplain in 1-2 sentences in {$lang} why the wrong answer is incorrect. add html styles";

        try {

          
            $client = \OpenAI::client($key);

            $result = $client->chat()->create([
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            $text = trim($result->choices[0]->message->content);

            ChatGPTExplanation::create([
                'question' => $question,
                'wrong_answer' => $wrongAnswer,
                'correct_answer' => $correctAnswer,
                'language' => $lang,
                'explanation' => $text,
            ]);

            return $text;
       

            //Log::warning('ChatGPT explanation failed: ' . $response->status() . ' ' . $response->body());
        } catch (Exception $e) {
            Log::warning('ChatGPT explanation failed: ' . $e->getMessage());
        }

        return '';
    }

    public function checkTranslation(string $original, string $correct, string $user, ?string $lang = null): array
    {
        $key = config('services.chatgpt.key');
        if (empty($key)) {
            Log::warning('ChatGPT API key not configured');
            return ['is_correct' => false, 'explanation' => ''];
        }

        $lang = $lang ?? config('services.chatgpt.language', 'uk');

        $prompt = "You are a language teacher.\n".
            "Original: {$original}\n".
            "Reference translation: {$correct}\n".
            "Student translation: {$user}\n".
            "Respond in JSON with keys 'correct' (true or false) and 'explanation' in {$lang}.";

        try {
            $client = \OpenAI::client($key);
            $result = $client->chat()->create([
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            $content = trim($result->choices[0]->message->content);
            $data = json_decode($content, true);
            if (is_array($data) && isset($data['correct'])) {
                return [
                    'is_correct' => (bool) $data['correct'],
                    'explanation' => $data['explanation'] ?? '',
                ];
            }
        } catch (Exception $e) {
            Log::warning('ChatGPT translation check failed: ' . $e->getMessage());
        }

        return ['is_correct' => false, 'explanation' => ''];
    }
}
