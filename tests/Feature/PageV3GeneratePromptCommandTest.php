<?php

namespace Tests\Feature;

use App\Models\PageCategory;
use App\Services\PageV3PromptGenerator\Data\PagePromptGenerationInput;
use App\Services\PageV3PromptGenerator\PageV3BlueprintService;
use App\Services\PageV3PromptGenerator\PageV3PromptGeneratorService;
use App\Support\CodexPromptEnvelopeFormatter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class PageV3GeneratePromptCommandTest extends TestCase
{
    /**
     * @var array<int, string>
     */
    private array $cleanupPaths = [];

    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();
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
    }

    protected function tearDown(): void
    {
        foreach (array_reverse($this->cleanupPaths) as $path) {
            if (File::isDirectory($path)) {
                File::deleteDirectory($path);
                continue;
            }

            File::delete($path);
        }

        parent::tearDown();
    }

    public function test_successful_existing_category_flow(): void
    {
        $category = $this->createCategory('Passive Voice', 'passive-voice');
        $result = $this->makePageResult(
            prompts: [
                $this->makePromptCard('single', 'Prompt for Codex', 'PAGE-V3-PROMPT-ABCD1234', 'Existing category prompt body.'),
            ],
            source: ['source_type' => 'manual_topic', 'source_label' => 'Manual topic', 'topic' => 'Passive Voice Causative'],
            category: [
                'mode' => 'existing',
                'mode_label' => 'Use existing theory category',
                'selected_category' => [
                    'id' => $category->id,
                    'title' => $category->title,
                    'slug' => $category->slug,
                ],
            ],
            preview: [
                'page_fully_qualified_class_name' => 'Database\\Seeders\\Page_V3\\PassiveVoice\\PassiveVoiceCausativeTheorySeeder',
                'page_definition_relative_path' => 'database/seeders/Page_V3/PassiveVoice/PassiveVoiceCausativeTheorySeeder/definition.json',
                'category_slug' => 'passive-voice',
            ],
            generationMode: 'single',
            promptAModeLabel: 'Mode A1 / repository-connected',
        );

        $this->mock(PageV3PromptGeneratorService::class, function ($mock) use ($category, $result) {
            $mock->shouldReceive('generate')
                ->once()
                ->with(Mockery::on(function (PagePromptGenerationInput $input) use ($category): bool {
                    return $input->sourceType === 'manual_topic'
                        && $input->manualTopic === 'Passive Voice Causative'
                        && $input->categoryMode === 'existing'
                        && $input->existingCategoryId === $category->id
                        && $input->generationMode === 'single'
                        && $input->promptAMode === 'repository_connected';
                }))
                ->andReturn($result);
        });

        $exitCode = Artisan::call('page-v3:generate-prompt', [
            '--manual-topic' => 'Passive Voice Causative',
            '--category-mode' => 'existing',
            '--existing-category-id' => $category->id,
        ]);

        $output = str_replace("\r\n", "\n", Artisan::output());

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Page_V3 Prompt Generator', $output);
        $this->assertStringContainsString('Passive Voice', $output);
        $this->assertStringContainsString('CODEX PROMPT ID: PAGE-V3-PROMPT-ABCD1234', $output);
        $this->assertStringContainsString('Codex Summary (Top):', $output);
        $this->assertStringContainsString('Codex Summary (Bottom):', $output);
        $this->assertStringContainsString('database/seeders/Page_V3/PassiveVoice/PassiveVoiceCausativeTheorySeeder/definition.json', $output);
    }

    public function test_successful_new_category_flow(): void
    {
        $result = $this->makePageResult(
            prompts: [
                $this->makePromptCard('single', 'Prompt for Codex', 'PAGE-V3-PROMPT-NEWCAT01', 'New category prompt body.'),
            ],
            source: ['source_type' => 'manual_topic', 'source_label' => 'Manual topic', 'topic' => 'Alternative Questions'],
            category: [
                'mode' => 'new',
                'mode_label' => 'Create new theory category',
                'new_category_title' => 'Types of Questions',
                'new_category_slug' => 'types-of-questions',
            ],
            preview: [
                'page_fully_qualified_class_name' => 'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\AlternativeQuestionsTheorySeeder',
                'page_definition_relative_path' => 'database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions/AlternativeQuestionsTheorySeeder/definition.json',
                'category_slug' => 'types-of-questions',
                'category_definition_relative_path' => 'database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions/TypesOfQuestionsCategorySeeder/definition.json',
            ],
            generationMode: 'single',
            promptAModeLabel: 'Mode A1 / repository-connected',
        );

        $this->mock(PageV3PromptGeneratorService::class, function ($mock) use ($result) {
            $mock->shouldReceive('generate')
                ->once()
                ->with(Mockery::on(function (PagePromptGenerationInput $input): bool {
                    return $input->sourceType === 'manual_topic'
                        && $input->manualTopic === 'Alternative Questions'
                        && $input->categoryMode === 'new'
                        && $input->newCategoryTitle === 'Types of Questions';
                }))
                ->andReturn($result);
        });

        $exitCode = Artisan::call('page-v3:generate-prompt', [
            '--manual-topic' => 'Alternative Questions',
            '--category-mode' => 'new',
            '--new-category-title' => 'Types of Questions',
        ]);

        $output = str_replace("\r\n", "\n", Artisan::output());

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('New category title: `Types of Questions`', $output);
        $this->assertStringContainsString('Planned category definition:', $output);
        $this->assertStringContainsString('CODEX PROMPT ID: PAGE-V3-PROMPT-NEWCAT01', $output);
    }

    public function test_successful_split_mode_output(): void
    {
        $result = $this->makePageResult(
            prompts: [
                $this->makePromptCard('llm_json_pack', 'Prompt for LLM JSON generation', 'PAGE-V3-PROMPT-1111AAAA', 'LLM JSON pack body.'),
                $this->makePromptCard('codex_page_v3', 'Prompt for Codex seeder generation', 'PAGE-V3-PROMPT-2222BBBB', 'Codex Page_V3 body.'),
            ],
            source: [
                'source_type' => 'external_url',
                'source_label' => 'External theory URL',
                'topic' => 'Alternative Questions',
                'url' => 'https://example.com/alternative-questions',
            ],
            category: [
                'mode' => 'new',
                'mode_label' => 'Create new theory category',
                'new_category_title' => 'Types of Questions',
                'new_category_slug' => 'types-of-questions',
            ],
            preview: [
                'page_fully_qualified_class_name' => 'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\AlternativeQuestionsTheorySeeder',
                'page_definition_relative_path' => 'database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions/AlternativeQuestionsTheorySeeder/definition.json',
                'category_slug' => 'types-of-questions',
                'category_definition_relative_path' => 'database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions/TypesOfQuestionsCategorySeeder/definition.json',
            ],
            generationMode: 'split',
            promptAModeLabel: 'Mode A2 / no-repository fallback',
            warnings: ['Prompt буде згенеровано тільки на основі URL'],
        );

        $this->mock(PageV3PromptGeneratorService::class, function ($mock) use ($result) {
            $mock->shouldReceive('generate')
                ->once()
                ->with(Mockery::on(function (PagePromptGenerationInput $input): bool {
                    return $input->sourceType === 'external_url'
                        && $input->externalUrl === 'https://example.com/alternative-questions'
                        && $input->categoryMode === 'new'
                        && $input->newCategoryTitle === 'Types of Questions'
                        && $input->generationMode === 'split'
                        && $input->promptAMode === 'no_repository';
                }))
                ->andReturn($result);
        });

        $exitCode = Artisan::call('page-v3:generate-prompt', [
            '--source-type' => 'external_url',
            '--external-url' => 'https://example.com/alternative-questions',
            '--category-mode' => 'new',
            '--new-category-title' => 'Types of Questions',
            '--generation-mode' => 'split',
            '--prompt-a-mode' => 'no_repository',
        ]);

        $output = str_replace("\r\n", "\n", Artisan::output());

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Warnings', $output);
        $this->assertStringContainsString('Prompt Card 1: Prompt for LLM JSON generation', $output);
        $this->assertStringContainsString('Prompt Card 2: Prompt for Codex seeder generation', $output);
        $this->assertStringContainsString('CODEX PROMPT ID: PAGE-V3-PROMPT-1111AAAA', $output);
        $this->assertStringContainsString('CODEX PROMPT ID: PAGE-V3-PROMPT-2222BBBB', $output);
    }

    public function test_json_output_mode(): void
    {
        $result = $this->makePageResult(
            prompts: [
                $this->makePromptCard('single', 'Prompt for Codex', 'PAGE-V3-PROMPT-JSON0001', 'Page JSON output body.'),
            ],
            source: ['source_type' => 'manual_topic', 'source_label' => 'Manual topic', 'topic' => 'Reported Speech'],
            category: [
                'mode' => 'ai_select',
                'mode_label' => 'Let AI choose the best category or create a new one',
            ],
            preview: [
                'page_fully_qualified_class_name' => 'Database\\Seeders\\Page_V3\\Tenses\\ReportedSpeechTheorySeeder',
                'page_definition_relative_path' => 'database/seeders/Page_V3/Tenses/ReportedSpeechTheorySeeder/definition.json',
                'category_slug' => 'tenses',
            ],
            generationMode: 'single',
            promptAModeLabel: 'Mode A1 / repository-connected',
        );

        $this->mock(PageV3PromptGeneratorService::class, function ($mock) use ($result) {
            $mock->shouldReceive('generate')->once()->andReturn($result);
        });

        $exitCode = Artisan::call('page-v3:generate-prompt', [
            '--manual-topic' => 'Reported Speech',
            '--category-mode' => 'ai_select',
            '--format' => 'json',
        ]);

        $output = Artisan::output();
        $payload = json_decode($output, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertSame('ai_select', $payload['category']['mode']);
        $this->assertSame('PAGE-V3-PROMPT-JSON0001', $payload['prompts'][0]['prompt_id']);
        $this->assertStringContainsString('CODEX PROMPT ID: PAGE-V3-PROMPT-JSON0001', $payload['prompts'][0]['text']);
    }

    public function test_validation_failure(): void
    {
        $exitCode = Artisan::call('page-v3:generate-prompt', [
            '--category-mode' => 'new',
        ]);

        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Validation failed.', $output);
        $this->assertStringContainsString('manual_topic', $output);
        $this->assertStringContainsString('new_category_title', $output);
    }

    public function test_output_and_write_dir_with_force(): void
    {
        $outputRelativePath = 'storage/app/testing/page-v3-prompts/page-v3-output.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $writeDirRelativePath = 'storage/app/testing/page-v3-prompts/files';
        $writeDirAbsolutePath = base_path($writeDirRelativePath);

        File::ensureDirectoryExists(dirname($outputAbsolutePath));
        File::put($outputAbsolutePath, 'old-output');
        File::ensureDirectoryExists($writeDirAbsolutePath);
        File::put($writeDirAbsolutePath . DIRECTORY_SEPARATOR . '01-single-page-v3-prompt-force0001.txt', 'old-prompt');

        $this->cleanupPaths[] = $outputAbsolutePath;
        $this->cleanupPaths[] = dirname($outputAbsolutePath);
        $this->cleanupPaths[] = $writeDirAbsolutePath;

        $result = $this->makePageResult(
            prompts: [
                $this->makePromptCard('single', 'Prompt for Codex', 'PAGE-V3-PROMPT-FORCE0001', 'Force write body.'),
            ],
            source: ['source_type' => 'manual_topic', 'source_label' => 'Manual topic', 'topic' => 'Reported Speech'],
            category: [
                'mode' => 'ai_select',
                'mode_label' => 'Let AI choose the best category or create a new one',
            ],
            preview: [
                'page_fully_qualified_class_name' => 'Database\\Seeders\\Page_V3\\Tenses\\ReportedSpeechTheorySeeder',
                'page_definition_relative_path' => 'database/seeders/Page_V3/Tenses/ReportedSpeechTheorySeeder/definition.json',
                'category_slug' => 'tenses',
            ],
            generationMode: 'single',
            promptAModeLabel: 'Mode A1 / repository-connected',
        );

        $this->mock(PageV3PromptGeneratorService::class, function ($mock) use ($result) {
            $mock->shouldReceive('generate')->once()->andReturn($result);
        });

        $exitCode = Artisan::call('page-v3:generate-prompt', [
            '--manual-topic' => 'Reported Speech',
            '--category-mode' => 'ai_select',
            '--output' => $outputRelativePath,
            '--write-dir' => $writeDirRelativePath,
            '--force' => true,
        ]);

        $output = Artisan::output();
        $promptFilePath = $writeDirAbsolutePath . DIRECTORY_SEPARATOR . '01-single-page-v3-prompt-force0001.txt';

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Consolidated output: ' . $outputRelativePath, $output);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertFileExists($promptFilePath);
        $this->assertStringContainsString('CODEX PROMPT ID: PAGE-V3-PROMPT-FORCE0001', File::get($outputAbsolutePath));
        $this->assertStringContainsString('CODEX PROMPT ID: PAGE-V3-PROMPT-FORCE0001', File::get($promptFilePath));
    }

    public function test_overwrite_protection_without_force(): void
    {
        $outputRelativePath = 'storage/app/testing/page-v3-prompts/existing-output.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        File::ensureDirectoryExists(dirname($outputAbsolutePath));
        File::put($outputAbsolutePath, 'existing');
        $this->cleanupPaths[] = $outputAbsolutePath;
        $this->cleanupPaths[] = dirname($outputAbsolutePath);

        $result = $this->makePageResult(
            prompts: [
                $this->makePromptCard('single', 'Prompt for Codex', 'PAGE-V3-PROMPT-KEEP0001', 'Should not overwrite.'),
            ],
            source: ['source_type' => 'manual_topic', 'source_label' => 'Manual topic', 'topic' => 'Gerunds'],
            category: [
                'mode' => 'ai_select',
                'mode_label' => 'Let AI choose the best category or create a new one',
            ],
            preview: [
                'page_fully_qualified_class_name' => 'Database\\Seeders\\Page_V3\\Gerunds\\GerundsTheorySeeder',
                'page_definition_relative_path' => 'database/seeders/Page_V3/Gerunds/GerundsTheorySeeder/definition.json',
                'category_slug' => 'gerunds',
            ],
            generationMode: 'single',
            promptAModeLabel: 'Mode A1 / repository-connected',
        );

        $this->mock(PageV3PromptGeneratorService::class, function ($mock) use ($result) {
            $mock->shouldReceive('generate')->once()->andReturn($result);
        });

        $exitCode = Artisan::call('page-v3:generate-prompt', [
            '--manual-topic' => 'Gerunds',
            '--category-mode' => 'ai_select',
            '--output' => $outputRelativePath,
        ]);

        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Refusing to overwrite existing files without --force', $output);
        $this->assertSame('existing', File::get($outputAbsolutePath));
    }

    public function test_write_skeleton_in_existing_mode_writes_page_only(): void
    {
        $category = $this->createCategory('CLI Existing Category', 'cli-existing-category', 'Database\\Seeders\\Page_V3\\Tests\\CodexCli\\Existing\\CliExistingCategorySeeder');
        $preview = $this->makePagePreview(
            topic: 'CLI Existing Page',
            categoryMode: 'existing',
            categoryContext: [
                'id' => $category->id,
                'title' => $category->title,
                'slug' => $category->slug,
                'namespace' => 'Tests\\CodexCli\\Existing',
                'seeder_relative_path' => 'database/seeders/Page_V3/Tests/CodexCli/Existing/CliExistingCategorySeeder.php',
            ],
        );

        $result = $this->makePageResult(
            prompts: [
                $this->makePromptCard('single', 'Prompt for Codex', 'PAGE-V3-PROMPT-EXISTSKEL', 'Existing mode skeleton body.'),
            ],
            source: ['source_type' => 'manual_topic', 'source_label' => 'Manual topic', 'topic' => 'CLI Existing Page'],
            category: [
                'mode' => 'existing',
                'mode_label' => 'Use existing theory category',
                'selected_category' => [
                    'id' => $category->id,
                    'title' => $category->title,
                    'slug' => $category->slug,
                    'namespace' => 'Tests\\CodexCli\\Existing',
                    'seeder_relative_path' => 'database/seeders/Page_V3/Tests/CodexCli/Existing/CliExistingCategorySeeder.php',
                ],
            ],
            preview: $preview,
            generationMode: 'single',
            promptAModeLabel: 'Mode A1 / repository-connected',
        );

        $this->mock(PageV3PromptGeneratorService::class, function ($mock) use ($result) {
            $mock->shouldReceive('generate')->once()->andReturn($result);
        });

        $exitCode = Artisan::call('page-v3:generate-prompt', [
            '--manual-topic' => 'CLI Existing Page',
            '--category-mode' => 'existing',
            '--existing-category-id' => $category->id,
            '--write-skeleton' => true,
        ]);

        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertFileExists(base_path($preview['page_seeder_relative_path']));
        $this->assertFileExists(base_path($preview['page_definition_relative_path']));
        $this->assertFileDoesNotExist(base_path($preview['category_definition_relative_path']));
        $this->assertStringContainsString('Written files: 5', $output);
    }

    public function test_write_skeleton_in_new_mode_writes_category_and_page_packages(): void
    {
        $preview = $this->makePagePreview(
            topic: 'CLI New Page',
            categoryMode: 'new',
            newCategoryTitle: 'CLI New Category'
        );

        $result = $this->makePageResult(
            prompts: [
                $this->makePromptCard('single', 'Prompt for Codex', 'PAGE-V3-PROMPT-NEWSKEL01', 'New mode skeleton body.'),
            ],
            source: ['source_type' => 'manual_topic', 'source_label' => 'Manual topic', 'topic' => 'CLI New Page'],
            category: [
                'mode' => 'new',
                'mode_label' => 'Create new theory category',
                'new_category_title' => 'CLI New Category',
                'new_category_slug' => $preview['category_slug'],
            ],
            preview: $preview,
            generationMode: 'single',
            promptAModeLabel: 'Mode A1 / repository-connected',
        );

        $this->mock(PageV3PromptGeneratorService::class, function ($mock) use ($result) {
            $mock->shouldReceive('generate')->once()->andReturn($result);
        });

        $exitCode = Artisan::call('page-v3:generate-prompt', [
            '--manual-topic' => 'CLI New Page',
            '--category-mode' => 'new',
            '--new-category-title' => 'CLI New Category',
            '--write-skeleton' => true,
        ]);

        $pageDefinition = json_decode(File::get(base_path($preview['page_definition_relative_path'])), true, 512, JSON_THROW_ON_ERROR);
        $categoryDefinition = json_decode(File::get(base_path($preview['category_definition_relative_path'])), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertFileExists(base_path($preview['page_definition_relative_path']));
        $this->assertFileExists(base_path($preview['category_definition_relative_path']));
        $this->assertSame('page', $pageDefinition['content_type']);
        $this->assertSame('category', $categoryDefinition['content_type']);
        $this->assertSame($preview['category_slug'], $pageDefinition['page']['category']['slug']);
    }

    public function test_ai_select_mode_refuses_write_skeleton(): void
    {
        $result = $this->makePageResult(
            prompts: [
                $this->makePromptCard('single', 'Prompt for Codex', 'PAGE-V3-PROMPT-AISELECT', 'AI select prompt body.'),
            ],
            source: ['source_type' => 'manual_topic', 'source_label' => 'Manual topic', 'topic' => 'AI Select Topic'],
            category: [
                'mode' => 'ai_select',
                'mode_label' => 'Let AI choose the best category or create a new one',
            ],
            preview: app(PageV3BlueprintService::class)->buildPreview('AI Select Topic', 'ai_select', null, null),
            generationMode: 'single',
            promptAModeLabel: 'Mode A1 / repository-connected',
        );

        $this->mock(PageV3PromptGeneratorService::class, function ($mock) use ($result) {
            $mock->shouldReceive('generate')->once()->andReturn($result);
        });

        $exitCode = Artisan::call('page-v3:generate-prompt', [
            '--manual-topic' => 'AI Select Topic',
            '--category-mode' => 'ai_select',
            '--write-skeleton' => true,
        ]);

        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Cannot write Page_V3 skeleton for ai_select mode.', $output);
    }

    public function test_scaffold_overwrite_with_force(): void
    {
        $preview = $this->makePagePreview(
            topic: 'CLI Force Page',
            categoryMode: 'new',
            newCategoryTitle: 'CLI Force Category'
        );
        File::ensureDirectoryExists(dirname(base_path($preview['page_definition_relative_path'])));
        File::put(base_path($preview['page_definition_relative_path']), '{"existing":true}');

        $result = $this->makePageResult(
            prompts: [
                $this->makePromptCard('single', 'Prompt for Codex', 'PAGE-V3-PROMPT-FORCESKEL', 'Force page skeleton body.'),
            ],
            source: ['source_type' => 'manual_topic', 'source_label' => 'Manual topic', 'topic' => 'CLI Force Page'],
            category: [
                'mode' => 'new',
                'mode_label' => 'Create new theory category',
                'new_category_title' => 'CLI Force Category',
                'new_category_slug' => $preview['category_slug'],
            ],
            preview: $preview,
            generationMode: 'single',
            promptAModeLabel: 'Mode A1 / repository-connected',
        );

        $this->mock(PageV3PromptGeneratorService::class, function ($mock) use ($result) {
            $mock->shouldReceive('generate')->once()->andReturn($result);
        });

        $exitCode = Artisan::call('page-v3:generate-prompt', [
            '--manual-topic' => 'CLI Force Page',
            '--category-mode' => 'new',
            '--new-category-title' => 'CLI Force Category',
            '--write-skeleton' => true,
            '--force' => true,
        ]);

        $pageDefinition = json_decode(File::get(base_path($preview['page_definition_relative_path'])), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertSame('CLI Force Page', $pageDefinition['page']['title']);
    }

    public function test_json_output_mode_includes_scaffold_metadata_when_requested(): void
    {
        $preview = $this->makePagePreview(
            topic: 'CLI JSON Page',
            categoryMode: 'new',
            newCategoryTitle: 'CLI JSON Category'
        );

        $result = $this->makePageResult(
            prompts: [
                $this->makePromptCard('single', 'Prompt for Codex', 'PAGE-V3-PROMPT-JSONSKEL', 'JSON scaffold page body.'),
            ],
            source: ['source_type' => 'manual_topic', 'source_label' => 'Manual topic', 'topic' => 'CLI JSON Page'],
            category: [
                'mode' => 'new',
                'mode_label' => 'Create new theory category',
                'new_category_title' => 'CLI JSON Category',
                'new_category_slug' => $preview['category_slug'],
            ],
            preview: $preview,
            generationMode: 'single',
            promptAModeLabel: 'Mode A1 / repository-connected',
        );

        $this->mock(PageV3PromptGeneratorService::class, function ($mock) use ($result) {
            $mock->shouldReceive('generate')->once()->andReturn($result);
        });

        $exitCode = Artisan::call('page-v3:generate-prompt', [
            '--manual-topic' => 'CLI JSON Page',
            '--category-mode' => 'new',
            '--new-category-title' => 'CLI JSON Category',
            '--format' => 'json',
            '--write-skeleton' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertTrue($payload['scaffold']['requested']);
        $this->assertSame(10, $payload['scaffold']['count']);
        $this->assertCount(10, $payload['scaffold']['planned']);
        $this->assertCount(10, $payload['scaffold']['written']);
    }

    public function test_split_mode_still_writes_one_resolved_package_set(): void
    {
        $preview = $this->makePagePreview(
            topic: 'CLI Split Page',
            categoryMode: 'new',
            newCategoryTitle: 'CLI Split Category'
        );

        $result = $this->makePageResult(
            prompts: [
                $this->makePromptCard('llm_json_pack', 'Prompt for LLM JSON generation', 'PAGE-V3-PROMPT-SPLIT01', 'Split LLM page body.'),
                $this->makePromptCard('codex_page_v3', 'Prompt for Codex seeder generation', 'PAGE-V3-PROMPT-SPLIT02', 'Split Codex page body.'),
            ],
            source: ['source_type' => 'manual_topic', 'source_label' => 'Manual topic', 'topic' => 'CLI Split Page'],
            category: [
                'mode' => 'new',
                'mode_label' => 'Create new theory category',
                'new_category_title' => 'CLI Split Category',
                'new_category_slug' => $preview['category_slug'],
            ],
            preview: $preview,
            generationMode: 'split',
            promptAModeLabel: 'Mode A1 / repository-connected',
        );

        $this->mock(PageV3PromptGeneratorService::class, function ($mock) use ($result) {
            $mock->shouldReceive('generate')->once()->andReturn($result);
        });

        $exitCode = Artisan::call('page-v3:generate-prompt', [
            '--manual-topic' => 'CLI Split Page',
            '--category-mode' => 'new',
            '--new-category-title' => 'CLI Split Category',
            '--generation-mode' => 'split',
            '--write-skeleton' => true,
        ]);

        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Prompt Card 1: Prompt for LLM JSON generation', $output);
        $this->assertStringContainsString('Prompt Card 2: Prompt for Codex seeder generation', $output);
        $this->assertStringContainsString('Written files: 10', $output);
    }

    private function createCategory(string $title, string $slug, ?string $seeder = null): PageCategory
    {
        return PageCategory::create([
            'title' => $title,
            'slug' => $slug,
            'language' => 'uk',
            'type' => 'theory',
            'seeder' => $seeder,
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $categoryContext
     * @return array<string, mixed>
     */
    private function makePagePreview(
        string $topic,
        string $categoryMode,
        ?array $categoryContext = null,
        ?string $newCategoryTitle = null,
    ): array {
        $preview = app(PageV3BlueprintService::class)->buildPreview($topic, $categoryMode, $categoryContext, $newCategoryTitle);

        $this->cleanupPaths[] = base_path($preview['page_seeder_relative_path']);
        $this->cleanupPaths[] = base_path($preview['page_package_relative_path']);

        if ($categoryMode === 'new') {
            $this->cleanupPaths[] = base_path($preview['category_seeder_relative_path']);
            $this->cleanupPaths[] = base_path($preview['category_package_relative_path']);
        }

        return $preview;
    }

    /**
     * @param  array<int, array<string, mixed>>  $prompts
     * @param  array<string, mixed>  $source
     * @param  array<string, mixed>  $category
     * @param  array<string, mixed>  $preview
     * @return array<string, mixed>
     */
    private function makePageResult(
        array $prompts,
        array $source,
        array $category,
        array $preview,
        string $generationMode,
        string $promptAModeLabel,
        array $warnings = [],
    ): array {
        return [
            'source' => $source,
            'category' => $category,
            'category_catalog_count' => 12,
            'preview' => $preview,
            'reference_files' => [],
            'warnings' => $warnings,
            'generation_mode' => $generationMode,
            'prompt_a_mode' => $promptAModeLabel === 'Mode A2 / no-repository fallback'
                ? 'no_repository'
                : 'repository_connected',
            'prompt_a_mode_label' => $promptAModeLabel,
            'prompts' => $prompts,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function makePromptCard(string $key, string $title, string $promptId, string $body): array
    {
        $formatter = app(CodexPromptEnvelopeFormatter::class);
        $summary = [
            'goal' => 'Підготувати ' . $title . ' для тестового Page_V3 CLI scenario.',
            'work' => 'Використати existing service result fields без ручного rebuilding envelope.',
            'constraints' => 'Зберегти canonical CODEX PROMPT ID та summary blocks.',
            'result' => 'Готовий wrapped prompt text для console output.',
        ];

        return [
            'key' => $key,
            'title' => $title,
            'prompt_id' => $promptId,
            'prompt_id_text' => $formatter->formatPromptIdLine($promptId),
            'summary' => $summary,
            'summary_top_text' => $formatter->formatSummaryBlock('Top', $promptId, $summary),
            'summary_bottom_text' => $formatter->formatSummaryBlock('Bottom', $promptId, $summary),
            'text' => $formatter->wrapPrompt($promptId, $summary, $body),
        ];
    }
}
