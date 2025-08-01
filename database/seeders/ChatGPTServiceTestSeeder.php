<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\ChatGPTService;
use App\Services\QuestionSeedingService;
use App\Models\Category;
use App\Models\Source;
use App\Models\Test;
use Illuminate\Support\Str;

class ChatGPTServiceTestSeeder extends Seeder
{
    public function run(): void
    {
        $gpt = app(ChatGPTService::class);

        $generated = $gpt->generateGrammarQuestion(['Present'], 1);
        if (!$generated) {
            $generated = [
                'question' => 'He {a1} here.',
                'answers' => ['a1' => 'is'],
                'verb_hints' => ['a1' => 'be'],
            ];
        }

        $category = Category::firstOrCreate(['name' => 'Present']);
        $sourceId = Source::firstOrCreate(['name' => 'AI Generated'])->id;

        $answers = [];
        $options = [];
        foreach ($generated['answers'] as $marker => $val) {
            $ans = ['marker' => $marker, 'answer' => $val];
            if (!empty($generated['verb_hints'][$marker] ?? null)) {
                $ans['verb_hint'] = $generated['verb_hints'][$marker];
            }
            $answers[] = $ans;
            $options[] = $val;
        }

        $uuid = Str::uuid()->toString();
        $service = new QuestionSeedingService();
        $service->seed([
            [
                'uuid' => $uuid,
                'question' => $generated['question'],
                'category_id' => $category->id,
                'difficulty' => 1,
                'source_id' => $sourceId,
                'flag' => 1,
                'answers' => $answers,
                'options' => $options,
            ],
        ]);

        $question = \App\Models\Question::where('uuid', $uuid)->first();

        $description = $gpt->generateTestDescription([$generated['question']]);

        Test::firstOrCreate(
            ['slug' => 'chatgpt-service-test'],
            [
                'name' => 'ChatGPTService Test',
                'filters' => [
                    'categories' => [$category->id],
                    'difficulty_from' => '1',
                    'difficulty_to' => '1',
                    'num_questions' => '1',
                    'manual_input' => false,
                    'autocomplete_input' => false,
                    'check_one_input' => false,
                    'include_ai' => false,
                    'only_ai' => false,
                ],
                'questions' => [$question->id],
                'description' => $description,
            ]
        );
    }
}
