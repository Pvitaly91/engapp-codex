<?php

namespace App\Services;

use OpenAI\Client;
use OpenAI; // the facade for building the client

class ChatGPTService
{
    private Client $client;

    public function __construct()
    {
        $apiKey = config('services.chatgpt.key');
        $this->client = OpenAI::client($apiKey);
    }

    public function explainWrongAnswer(string $question, string $wrongAnswer, string $correctAnswer): string
    {
        $prompt = "Question: {$question}\nWrong answer: {$wrongAnswer}\nCorrect answer: {$correctAnswer}\nExplain in 1-2 sentences why the wrong answer is incorrect.";

        $response = $this->client->chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        return trim($response->choices[0]->message->content ?? '');
    }
}
