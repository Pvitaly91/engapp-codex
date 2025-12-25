<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestApiKeysCommand extends Command
{
    protected $signature = 'words:test-api-keys {--provider=auto : API provider (auto, gemini, openai)}';
    
    protected $description = 'Test API keys for translation services';

    public function handle(): int
    {
        $provider = $this->option('provider');
        
        $this->info('Testing API keys for translation services...');
        $this->newLine();
        
        $testedProviders = [];
        
        if ($provider === 'auto' || $provider === 'gemini') {
            $testedProviders[] = 'gemini';
        }
        
        if ($provider === 'auto' || $provider === 'openai') {
            $testedProviders[] = 'openai';
        }
        
        $allPassed = true;
        
        foreach ($testedProviders as $testProvider) {
            if ($testProvider === 'gemini') {
                $allPassed = $this->testGemini() && $allPassed;
            } elseif ($testProvider === 'openai') {
                $allPassed = $this->testOpenAI() && $allPassed;
            }
            
            $this->newLine();
        }
        
        if ($allPassed) {
            $this->info('✅ All API keys are valid and working!');
            return 0;
        } else {
            $this->error('❌ Some API keys failed validation. Please check the errors above.');
            return 1;
        }
    }
    
    private function testGemini(): bool
    {
        $this->line('Testing Gemini API...');
        
        $key = trim(config('services.gemini.key'));
        
        if (empty($key)) {
            $this->warn('  ⚠️  GEMINI_API_KEY not configured');
            $this->line('     Set it in .env: GEMINI_API_KEY=your-key-here');
            $this->line('     Get key from: https://makersuite.google.com/app/apikey');
            return false;
        }
        
        $this->line('  📋 Key found (first 10 chars): ' . substr($key, 0, 10) . '...');
        $this->line('  📋 Key length: ' . strlen($key) . ' characters');
        
        // Test with a simple request
        $model = config('services.gemini.model', 'gemini-2.0-flash-exp');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($key);
        
        try {
            $response = Http::timeout(30)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => 'Translate "hello" to Polish. Respond with just one word.']
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.3,
                    'maxOutputTokens' => 50,
                ]
            ]);
            
            if ($response->successful()) {
                $result = $response->json();
                $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
                
                $this->info('  ✅ Gemini API key is valid!');
                $this->line('     Test translation response: ' . trim($text));
                $this->line('     Model: ' . $model);
                return true;
            } else {
                $statusCode = $response->status();
                $errorBody = $response->body();
                
                $this->error('  ❌ Gemini API key validation failed');
                $this->line('     HTTP Status: ' . $statusCode);
                $this->line('     Error: ' . substr($errorBody, 0, 200));
                
                if ($statusCode === 400) {
                    $errorData = $response->json();
                    if (isset($errorData['error']['message'])) {
                        $this->line('     Details: ' . $errorData['error']['message']);
                    }
                    
                    // Check if it's an invalid key error
                    if (str_contains($errorBody, 'API key not valid')) {
                        $this->newLine();
                        $this->warn('     💡 The API key appears to be invalid. Please check:');
                        $this->line('        1. Copy the key correctly from Google AI Studio');
                        $this->line('        2. Make sure there are no extra spaces or line breaks');
                        $this->line('        3. Verify the key is enabled for Generative Language API');
                        $this->line('        4. Try regenerating the key if needed');
                    }
                }
                
                return false;
            }
        } catch (\Exception $e) {
            $this->error('  ❌ Gemini API request failed');
            $this->line('     Exception: ' . $e->getMessage());
            return false;
        }
    }
    
    private function testOpenAI(): bool
    {
        $this->line('Testing OpenAI/ChatGPT API...');
        
        $key = trim(config('services.openai.key'));
        
        if (empty($key)) {
            $this->warn('  ⚠️  OPENAI_API_KEY / CHAT_GPT_API_KEY not configured');
            $this->line('     Set it in .env: CHAT_GPT_API_KEY=your-key-here');
            $this->line('     Get key from: https://platform.openai.com/api-keys');
            return false;
        }
        
        $this->line('  📋 Key found (first 10 chars): ' . substr($key, 0, 10) . '...');
        $this->line('  📋 Key length: ' . strlen($key) . ' characters');
        
        // Test with a simple request
        $model = config('services.openai.model', 'gpt-4o-mini');
        
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $key,
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => 'Translate "hello" to Polish. Respond with just one word.'
                        ]
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 50,
                ]);
            
            if ($response->successful()) {
                $result = $response->json();
                $text = $result['choices'][0]['message']['content'] ?? '';
                
                $this->info('  ✅ OpenAI API key is valid!');
                $this->line('     Test translation response: ' . trim($text));
                $this->line('     Model: ' . $model);
                return true;
            } else {
                $statusCode = $response->status();
                $errorBody = $response->body();
                
                $this->error('  ❌ OpenAI API key validation failed');
                $this->line('     HTTP Status: ' . $statusCode);
                $this->line('     Error: ' . substr($errorBody, 0, 200));
                
                if ($statusCode === 401) {
                    $this->newLine();
                    $this->warn('     💡 Authentication failed. Please check:');
                    $this->line('        1. Copy the key correctly from OpenAI Platform');
                    $this->line('        2. Make sure there are no extra spaces');
                    $this->line('        3. Verify the key starts with "sk-"');
                }
                
                if ($statusCode === 429) {
                    $this->newLine();
                    $this->warn('     💡 Rate limit or quota exceeded:');
                    $this->line('        1. Check your billing status at platform.openai.com');
                    $this->line('        2. Verify you have available credits');
                    $this->line('        3. Wait a few minutes and try again');
                }
                
                return false;
            }
        } catch (\Exception $e) {
            $this->error('  ❌ OpenAI API request failed');
            $this->line('     Exception: ' . $e->getMessage());
            return false;
        }
    }
}
