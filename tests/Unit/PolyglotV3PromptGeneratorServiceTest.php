<?php

namespace Tests\Unit;

use App\Services\PolyglotV3PromptGeneratorService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PolyglotV3PromptGeneratorServiceTest extends TestCase
{
    public function test_service_builds_wrapped_prompt_with_real_theory_context_and_auto_generated_prompt_id(): void
    {
        $generated = app(PolyglotV3PromptGeneratorService::class)->generate($this->sampleInput());

        $promptId = $generated['prompt_id'];
        $prompt = str_replace("\r\n", "\n", $generated['prompt_text']);

        $this->assertMatchesRegularExpression('/^GLZ-PROMPT-[A-F0-9]{8}$/', $promptId);
        $this->assertStringStartsWith(
            'CODEX PROMPT ID: ' . $promptId . "\n\n" . 'Codex Summary (Top):' . "\n" . 'CODEX PROMPT ID: ' . $promptId,
            $prompt
        );
        $this->assertStringEndsWith("\n\n" . 'CODEX PROMPT ID: ' . $promptId, $prompt);
        $this->assertGreaterThanOrEqual(4, substr_count($prompt, 'CODEX PROMPT ID: ' . $promptId));
        $this->assertStringContainsString(
            'Codex Summary (Top):' . "\n" . 'CODEX PROMPT ID: ' . $promptId . "\n" . '- Мета:',
            $prompt
        );
        $this->assertStringContainsString(
            'Codex Summary (Bottom):' . "\n" . 'CODEX PROMPT ID: ' . $promptId . "\n" . '- Мета:',
            $prompt
        );
        $this->assertStringContainsString('polyglot-sample-v3-lesson', $prompt);
        $this->assertStringContainsString('/theory/verb-to-be/verb-to-be-present', $prompt);
        $this->assertStringContainsString(
            'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePresentTheorySeeder',
            $prompt
        );
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotSampleLessonSeeder/definition.json',
            $prompt
        );
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotToBeLessonSeeder/definition.json',
            $prompt
        );
        $this->assertStringContainsString(
            'database/seeders/V3/Polyglot/PolyglotThereIsThereAreLessonSeeder/definition.json',
            $prompt
        );
        $this->assertStringContainsString('V2/Polyglot/*` are thin bridges only', $prompt);
        $this->assertSame('basic-grammar/verb-to-be', $generated['theory_context']['category_slug_path']);
    }

    public function test_service_preserves_custom_prompt_id_in_outer_lines_and_summary_blocks(): void
    {
        $generated = app(PolyglotV3PromptGeneratorService::class)->generate(
            $this->sampleInput([
                'prompt_id' => 'cp-custom-123',
            ])
        );

        $promptId = $generated['prompt_id'];
        $prompt = str_replace("\r\n", "\n", $generated['prompt_text']);

        $this->assertSame('CP-CUSTOM-123', $promptId);
        $this->assertStringStartsWith('CODEX PROMPT ID: CP-CUSTOM-123', $prompt);
        $this->assertStringEndsWith("\n\nCODEX PROMPT ID: CP-CUSTOM-123", $prompt);
        $this->assertStringContainsString(
            'Codex Summary (Top):' . "\n" . 'CODEX PROMPT ID: CP-CUSTOM-123',
            $prompt
        );
        $this->assertStringContainsString(
            'Codex Summary (Bottom):' . "\n" . 'CODEX PROMPT ID: CP-CUSTOM-123',
            $prompt
        );
    }

    public function test_docs_template_remains_aligned_with_cli_workflow(): void
    {
        $contents = File::get(base_path('docs/prompts/polyglot-v3-lesson-generator.md'));

        $this->assertStringContainsString('polyglot:generate-v3-prompt', $contents);
        $this->assertStringContainsString('FORMAT OF YOUR RESPONSE — REQUIRED', $contents);
        $this->assertStringContainsString('CODEX PROMPT ID: GLZ-PROMPT-....', $contents);
        $this->assertStringContainsString('Codex Summary (Top):', $contents);
        $this->assertStringContainsString('Codex Summary (Bottom):', $contents);
        $this->assertStringContainsString('--write-skeleton', $contents);
        $this->assertStringContainsString('database/seeders/V3/Polyglot', $contents);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function sampleInput(array $overrides = []): array
    {
        return array_merge([
            'theory_category_slug' => 'verb-to-be',
            'theory_page_slug' => 'verb-to-be-present',
            'lesson_slug' => 'polyglot-sample-v3-lesson',
            'lesson_order' => 3,
            'lesson_title' => 'Polyglot Sample Lesson',
            'topic' => 'verb to be',
            'seeder' => 'PolyglotSampleLessonSeeder',
            'course_slug' => 'polyglot-english-a1',
            'level' => 'A1',
            'previous_lesson_slug' => 'polyglot-there-is-there-are-a1',
            'items_count' => 24,
        ], $overrides);
    }
}
