<?php

namespace Tests\Unit;

use App\Models\Page;
use App\Models\PageCategory;
use App\Services\PageV3PromptGenerator\Data\PagePromptGenerationInput;
use App\Services\PageV3PromptGenerator\PageV3PromptGeneratorService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PageV3PromptGeneratorServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pages');
        Schema::dropIfExists('page_categories');
        Schema::enableForeignKeyConstraints();

        Schema::create('page_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('language', 8)->default('uk');
            $table->string('type')->nullable();
            $table->string('seeder')->nullable();
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_category_id')->nullable()->index();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('text')->nullable();
            $table->string('type')->nullable();
            $table->string('seeder')->nullable();
            $table->timestamps();
        });
    }

    public function test_single_prompt_has_top_and_bottom_prompt_id_and_preserves_existing_category_logic(): void
    {
        $category = $this->createTheoryCategory([
            'title' => 'Passive Voice',
            'slug' => 'passive-voice',
            'seeder' => 'Database\\Seeders\\Page_V3\\PassiveVoice\\PassiveVoiceCategorySeeder',
        ]);

        Page::create([
            'title' => 'Existing passive voice page',
            'slug' => 'existing-passive-voice-page',
            'type' => 'theory',
            'page_category_id' => $category->id,
        ]);

        $result = app(PageV3PromptGeneratorService::class)->generate(
            PagePromptGenerationInput::fromArray([
                'source_type' => 'manual_topic',
                'manual_topic' => 'Passive Voice Causative',
                'category_mode' => 'existing',
                'existing_category_id' => $category->id,
                'generation_mode' => 'single',
                'prompt_a_mode' => 'repository_connected',
            ])
        );

        $prompt = $result['prompts'][0];
        $promptText = str_replace("\r\n", "\n", $prompt['text']);
        $promptIdLine = 'CODEX PROMPT ID: ' . $prompt['prompt_id'];

        $this->assertSame('existing', $result['category']['mode']);
        $this->assertSame('Passive Voice', $result['category']['selected_category']['title']);
        $this->assertSame('PassiveVoice', $result['preview']['category_namespace']);
        $this->assertStringStartsWith($promptIdLine, $promptText);
        $this->assertStringEndsWith("\n\n" . $promptIdLine, $promptText);
        $this->assertStringContainsString('Codex Summary (Top):' . "\n" . $promptIdLine, $promptText);
        $this->assertStringContainsString('Codex Summary (Bottom):' . "\n" . $promptIdLine, $promptText);
        $this->assertStringContainsString($promptIdLine, $prompt['summary_top_text']);
        $this->assertStringContainsString($promptIdLine, $prompt['summary_bottom_text']);
    }

    public function test_split_prompts_have_distinct_ids_and_preserve_category_source_and_generation_mode_logic(): void
    {
        $result = app(PageV3PromptGeneratorService::class)->generate(
            PagePromptGenerationInput::fromArray([
                'source_type' => 'manual_topic',
                'manual_topic' => 'Alternative Questions',
                'category_mode' => 'new',
                'new_category_title' => 'Types of Questions',
                'generation_mode' => 'split',
                'prompt_a_mode' => 'no_repository',
            ])
        );

        $prompts = collect($result['prompts'])->keyBy('key');

        $this->assertCount(2, $prompts);
        $this->assertTrue($prompts->has('llm_json_pack'));
        $this->assertTrue($prompts->has('codex_page_v3'));
        $this->assertNotSame($prompts['llm_json_pack']['prompt_id'], $prompts['codex_page_v3']['prompt_id']);
        $this->assertSame('new', $result['category']['mode']);
        $this->assertSame('Types of Questions', $result['category']['new_category_title']);
        $this->assertSame('AlternativeQuestionsTheorySeeder', $result['preview']['page_class_name']);
        $this->assertSame('split', $result['generation_mode']);
        $this->assertSame('Mode A2 / no-repository fallback', $result['prompt_a_mode_label']);

        foreach (['llm_json_pack', 'codex_page_v3'] as $key) {
            $promptIdLine = 'CODEX PROMPT ID: ' . $prompts[$key]['prompt_id'];
            $promptText = str_replace("\r\n", "\n", $prompts[$key]['text']);

            $this->assertStringStartsWith($promptIdLine, $promptText);
            $this->assertStringEndsWith("\n\n" . $promptIdLine, $promptText);
            $this->assertStringContainsString('Codex Summary (Top):' . "\n" . $promptIdLine, $promptText);
            $this->assertStringContainsString('Codex Summary (Bottom):' . "\n" . $promptIdLine, $promptText);
            $this->assertStringContainsString($promptIdLine, $prompts[$key]['summary_top_text']);
            $this->assertStringContainsString($promptIdLine, $prompts[$key]['summary_bottom_text']);
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createTheoryCategory(array $attributes): PageCategory
    {
        return PageCategory::create(array_merge([
            'title' => 'Basic Grammar',
            'slug' => 'basic-grammar',
            'language' => 'uk',
            'type' => 'theory',
            'seeder' => null,
        ], $attributes));
    }
}
