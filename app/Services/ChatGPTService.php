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

        $prompt = "Question: {$question}\nWrong answer: {$wrongAnswer}\nCorrect answer: {$correctAnswer}\nExplain in 1-2 sentences in {$lang} why the wrong answer is incorrect.";

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
        // Simple check first: ignore case and surrounding whitespace
        if (strcasecmp(trim($correct), trim($user)) === 0) {
            return ['is_correct' => true, 'explanation' => ''];
        }

        $lang = $lang ?? config('services.chatgpt.language', 'uk');

        $normalized = [
            'original' => trim(mb_strtolower($original)),
            'reference' => trim(mb_strtolower($correct)),
            'user' => trim(mb_strtolower($user)),
        ];

        $cached = \App\Models\ChatGPTTranslationCheck::where('original', $normalized['original'])
            ->where('reference', $normalized['reference'])
            ->where('user_text', $normalized['user'])
            ->where('language', $lang)
            ->first();

        if ($cached) {
            return [
                'is_correct' => (bool) $cached->is_correct,
                'explanation' => $cached->explanation ?? '',
            ];
        }

        $key = config('services.chatgpt.key');
        if (empty($key)) {
            Log::warning('ChatGPT API key not configured');
            return ['is_correct' => false, 'explanation' => ''];
        }

        $prompt = "You are a language teacher.\n" .
            "Original: {$original}\n" .
            "Reference translation: {$correct}\n" .
            "Student translation: {$user}\n" .
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

            // Sometimes GPT returns additional text around JSON
            if (!is_array($data)) {
                $start = strpos($content, '{');
                $end = strrpos($content, '}');
                if ($start !== false && $end !== false && $end > $start) {
                    $json = substr($content, $start, $end - $start + 1);
                    $data = json_decode($json, true);
                }
            }

            if (is_array($data) && isset($data['correct'])) {
                $result = [
                    'is_correct' => (bool) $data['correct'],
                    'explanation' => $data['explanation'] ?? '',
                ];

                \App\Models\ChatGPTTranslationCheck::create([
                    'original' => $normalized['original'],
                    'reference' => $normalized['reference'],
                    'user_text' => $normalized['user'],
                    'language' => $lang,
                    'is_correct' => $result['is_correct'],
                    'explanation' => $result['explanation'],
                ]);

                return $result;
            }
        } catch (Exception $e) {
            Log::warning('ChatGPT translation check failed: ' . $e->getMessage());
        }

        return ['is_correct' => false, 'explanation' => ''];
    }

    /**
     * Generate a detailed description of what to do in a test based on its questions.
     * The description should include a short explanation of the grammar rule that
     * is tested so students know how to form the correct answers.
     */
    public function generateTestDescription(array $questions, ?string $lang = null): string
    {
        $key = config('services.chatgpt.key');
        if (empty($key)) {
            Log::warning('ChatGPT API key not configured');
            return '';
        }

        $lang = $lang ?? config('services.chatgpt.language', 'uk');
        $prompt = 'Визнач які часи використувуються в питаннях цього тесту,
            питання:';
      //  $prompt = "Сформулюй детальний опис українською того, що потрібно зробити у цьому тесті." .
      //      " Додай коротке пояснення правила або формули, за якою утворюються правильні відповіді." .
     //       " Ось питання тесту:\n";
        foreach ($questions as $i => $q) {
             $prompt .= ($i + 1) . ". {$q}\n";
        }
        $prompt .= "Напиши формули по яких утворюються речення для ціх часів, вивиди відформатований текст готовий для використання на html сторінці (ВАЖЛИВО! не потрібно писати технічні  слова: HTML код і т. д. потрібни текст готовий відформатований для читання користувачем на строінці, також приладу придумай свої приклади та виділи їх іншим кольором, та не використовуй питання з тесту, назву часів та ключові слова англійською виділяй жирним та іншим кольором ) ";
       
        try {
            $client = \OpenAI::client($key);
            $result = $client->chat()->create([
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            return trim($result->choices[0]->message->content);
        } catch (Exception $e) {
            Log::warning('ChatGPT test description failed: ' . $e->getMessage());
        }

        return '';
    }
}
