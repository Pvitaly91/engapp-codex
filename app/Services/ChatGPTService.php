<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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
                'model' => 'gpt-5',
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
                'model' => 'gpt-5',
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

    public function hintSentenceStructure(string $question, string $lang = 'uk'): string
    {
        $key = config('services.chatgpt.key');
        if (empty($key)) {
            Log::warning('ChatGPT API key not configured');
            return '';
        }

        $prompt = "Give a short hint in {$lang} on how to construct the following sentence (but do not provide a correct statement, just the formula by which the sentence is constructed):\n{$question}";

        try {
            $client = \OpenAI::client($key);
            $result = $client->chat()->create([
                'model' => 'gpt-5',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            return trim($result->choices[0]->message->content);
        } catch (Exception $e) {
            Log::warning('ChatGPT hint failed: ' . $e->getMessage());
        }

        return '';
    }

    public function determineTenseTags(string $question, array $tags): array
    {
        $key = config('services.chatgpt.key');
        if (empty($key) || empty($tags)) {
            Log::warning('ChatGPT API key not configured or no tags provided');
            return [];
        }

        $tagsList = implode(', ', $tags);
        $prompt = "Question: {$question}\n" .
            "Choose all appropriate tenses from this list: {$tagsList}. that apply only to the {missing words}.\n" .
            "Respond with a comma-separated list of tag names.";
        
        try {
            $client = \OpenAI::client($key);
            $result = $client->chat()->create([
                'model' => 'gpt-5',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            $response = trim($result->choices[0]->message->content);
            $parts = array_filter(array_map('trim', explode(',', $response)));

            // Ensure only known tags are returned
            return array_values(array_intersect($tags, $parts));
        } catch (Exception $e) {
            Log::warning('ChatGPT determine tense failed: ' . $e->getMessage());
        }

        return [];
    }

    public function determineDifficulty(string $question): string
    {
        $key = config('services.chatgpt.key');
        if (empty($key)) {
            Log::warning('ChatGPT API key not configured');
            return '';
        }

        $prompt = "Question: {$question}\n" .
            "Classify its CEFR difficulty level (A1, A2, B1, B2, C1, C2).\n" .
            "Respond with one level code.";

        try {
            $client = \OpenAI::client($key);
            $result = $client->chat()->create([
                'model' => 'gpt-5',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            $response = trim($result->choices[0]->message->content);
            $levels = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
            return in_array($response, $levels, true) ? $response : '';
        } catch (Exception $e) {
            Log::warning('ChatGPT determine difficulty failed: ' . $e->getMessage());
        }

        return '';
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
        $prompt .= "Напиши формули по яких утворюються речення для ціх часів, вивиди відформатований текст готовий для використання на html сторінці (ВАЖЛИВО! не потрібно писати технічні  слова: HTML код і т. д. потрібни текст готовий відформатований для читання користувачем на строінці, також приладу придумай свої приклади, та не використовуй питання з тесту в якості прикладу, назву часів та ключові слова англійською виділяй жирним та іншим кольором ) ";
       
        try {
            $client = \OpenAI::client($key);
            $result = $client->chat()->create([
                'model' => 'gpt-5',
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

    /**
     * Return available ChatGPT models.
     */
    public static function availableModels(): array
    {
        return [
            'gpt-5',
            'gpt-5-mini',
            'gpt-5-nano',
            'gpt-4.1',
            'gpt-4.1-mini',
            'gpt-4.1-nano',
            'gpt-4o',
            'gpt-4o-mini',
            'o1',
            'o1-preview',
            'o1-mini',
            'o3',
            'o3-mini',
            'o4-mini',
        ];
    }

    /**
     * Generate grammar questions for given tenses.
     * Each question should contain the specified number of missing words
     * marked as {a1}, {a2}, ... and return JSON with the correct answers.
     */
    public function generateGrammarQuestions(array $tenses, int $numQuestions = 1, int $answersCount = 1, string $model = 'random', string $question = ''): array
    {
       
        $key = config('services.chatgpt.key');
        if (empty($key)) {
            Log::warning('ChatGPT API key not configured');
            return [];
        }
    
    
        $tensesText = "";
      
        $i = 1;
        $random = rand($i, count($tenses));
       
        foreach($tenses as $category => $items){
            if($random == $i){
                $tensesText .= $category . ': ' . implode(', ', $items) . "\n";
                break;
            }
            $i++;
        }
       
        $prompt = "Generate {$numQuestions} short English grammar questions for the following themes: {$tensesText}. "
            . "based on this question: {$question}. "
            . "Each question must contain {$answersCount} missing word(s) represented as {a1}, {a2}, ... . "
            . "Provide the base form of each missing verb as verb_hints. "
            . 'Include a CEFR level for each question as "level" (A1, A2, B1, B2, C1, or C2) and the grammatical tense as "tense" (e.g., Present Simple). '
            . 'Respond strictly in JSON format like: [{"question":"He {a1} ...", "answers":{"a1":"goes"}, "verb_hints":{"a1":"go"}, "level":"B1", "tense":"Present Simple"}].';

        try {
            $models = self::availableModels();
            if ($model === 'random' || ! in_array($model, $models, true)) {
                $model = $models[array_rand($models)];
            }

            $client = \OpenAI::client($key);
            $result = $client->chat()->create([
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            $content = trim($result->choices[0]->message->content);
            $data = json_decode($content, true);
            if (! is_array($data)) {
                $start = strpos($content, '[');
                $end = strrpos($content, ']');
                if ($start !== false && $end !== false && $end > $start) {
                    $json = substr($content, $start, $end - $start + 1);
                    $data = json_decode($json, true);
                }
            }

            if (is_array($data)) {
                foreach ($data as &$item) {
                    $item['model'] = $model;
                }
                unset($item);
                return $data;
            }

            return [];
        } catch (Exception $e) {
            Log::warning('ChatGPT question generation failed: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Convenience wrapper to generate a single grammar question.
     */
    public function generateGrammarQuestion(array $tenses, int $answersCount = 1, string $model = 'random',string $refferance): ?array
    {
        $all = $this->generateGrammarQuestions($tenses, 1, $answersCount, $model, $refferance);
        return $all[0] ?? null;
    }
}
