<?php

namespace Tests\Unit;

use App\Services\V3PromptGenerator\Data\PromptGenerationInput;
use App\Services\V3PromptGenerator\V3PromptGeneratorService;
use Tests\TestCase;

class V3PromptGeneratorServiceTest extends TestCase
{
    public function test_single_prompt_is_wrapped_and_preserves_distribution_source_and_preview(): void
    {
        $result = app(V3PromptGeneratorService::class)->generate(
            PromptGenerationInput::fromArray([
                'source_type' => 'manual_topic',
                'manual_topic' => 'Plural nouns',
                'site_domain' => 'gramlyze.com',
                'target_namespace' => 'AI\\ChatGptPro',
                'levels' => ['A1', 'B1'],
                'questions_per_level' => 4,
                'generation_mode' => 'single',
                'prompt_a_mode' => 'repository_connected',
            ])
        );

        $prompt = $result['prompts'][0];
        $promptText = str_replace("\r\n", "\n", $prompt['text']);
        $promptIdLine = 'CODEX PROMPT ID: ' . $prompt['prompt_id'];

        $this->assertSame('single', $prompt['key']);
        $this->assertSame('manual_topic', $result['source']['source_type']);
        $this->assertSame(['A1' => 4, 'B1' => 4], $result['distribution']);
        $this->assertSame('AI\\ChatGptPro', $result['preview']['target_namespace']);
        $this->assertArrayHasKey('summary', $prompt);
        $this->assertArrayHasKey('summary_top_text', $prompt);
        $this->assertArrayHasKey('summary_bottom_text', $prompt);
        $this->assertStringStartsWith($promptIdLine, $promptText);
        $this->assertStringEndsWith("\n\n" . $promptIdLine, $promptText);
        $this->assertStringContainsString('Codex Summary (Top):' . "\n" . $promptIdLine, $promptText);
        $this->assertStringContainsString('Codex Summary (Bottom):' . "\n" . $promptIdLine, $promptText);
        $this->assertStringContainsString('PluralNounsV3QuestionsOnlySeeder', $promptText);
        $this->assertStringContainsString($promptIdLine, $prompt['summary_top_text']);
        $this->assertStringContainsString($promptIdLine, $prompt['summary_bottom_text']);
    }

    public function test_split_prompts_have_distinct_and_deterministic_prompt_ids(): void
    {
        $input = PromptGenerationInput::fromArray([
            'source_type' => 'manual_topic',
            'manual_topic' => 'Conditionals',
            'site_domain' => 'gramlyze.com',
            'target_namespace' => 'AI\\Claude',
            'levels' => ['B1', 'B2'],
            'questions_per_level' => 3,
            'generation_mode' => 'split',
            'prompt_a_mode' => 'no_repository',
        ]);

        $first = app(V3PromptGeneratorService::class)->generate($input);
        $second = app(V3PromptGeneratorService::class)->generate($input);

        $firstPrompts = collect($first['prompts'])->keyBy('key');
        $secondPrompts = collect($second['prompts'])->keyBy('key');

        $this->assertCount(2, $firstPrompts);
        $this->assertTrue($firstPrompts->has('llm_json'));
        $this->assertTrue($firstPrompts->has('codex_seeder'));
        $this->assertNotSame($firstPrompts['llm_json']['prompt_id'], $firstPrompts['codex_seeder']['prompt_id']);
        $this->assertSame($firstPrompts['llm_json']['prompt_id'], $secondPrompts['llm_json']['prompt_id']);
        $this->assertSame($firstPrompts['codex_seeder']['prompt_id'], $secondPrompts['codex_seeder']['prompt_id']);

        foreach (['llm_json', 'codex_seeder'] as $key) {
            $promptIdLine = 'CODEX PROMPT ID: ' . $firstPrompts[$key]['prompt_id'];
            $promptText = str_replace("\r\n", "\n", $firstPrompts[$key]['text']);

            $this->assertStringStartsWith($promptIdLine, $promptText);
            $this->assertStringEndsWith("\n\n" . $promptIdLine, $promptText);
            $this->assertStringContainsString('Codex Summary (Top):' . "\n" . $promptIdLine, $promptText);
            $this->assertStringContainsString('Codex Summary (Bottom):' . "\n" . $promptIdLine, $promptText);
            $this->assertStringContainsString($promptIdLine, $firstPrompts[$key]['summary_top_text']);
            $this->assertStringContainsString($promptIdLine, $firstPrompts[$key]['summary_bottom_text']);
        }

        $this->assertSame('split', $first['generation_mode']);
        $this->assertSame('Mode A2 / no-repository fallback', $first['prompt_a_mode_label']);
        $this->assertSame(['B1' => 3, 'B2' => 3], $first['distribution']);
    }
}
