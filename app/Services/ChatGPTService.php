<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatGPTService
{
    public function explainWrongAnswer(string $question, string $wrongAnswer, string $correctAnswer): string
    {
        $key = config('services.chatgpt.key');
        if (empty($key)) {
            Log::warning('ChatGPT API key not configured');
           return '';
       }

        $prompt = "Question: {$question}\nWrong answer: {$wrongAnswer}\nCorrect answer: {$correctAnswer}\nExplain in 1-2 sentences why the wrong answer is incorrect.";

        try {

          
            $client = \OpenAI::client($key);

            $result = $client->chat()->create([
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);


           
            return trim($result->choices[0]->message->content);
       

            //Log::warning('ChatGPT explanation failed: ' . $response->status() . ' ' . $response->body());
        } catch (Exception $e) {
            Log::warning('ChatGPT explanation failed: ' . $e->getMessage());
        }

        return '';
    }
}
