<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageCategory;
use App\Services\V3PromptGenerator\Data\PromptGenerationInput;
use App\Services\V3PromptGenerator\V3PromptGeneratorService;
use App\Services\V3PromptGenerator\V3SeederBlueprintService;
use App\Support\CodexPromptEnvelopeFormatter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class V3GeneratePromptCommandTest extends TestCase
{
    /**
     * @var array<int, string>
     */
    private array $cleanupPaths = [];

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

    public function test_successful_single_mode_human_output(): void
    {
        $page = $this->createTheoryPage();
        $result = $this->makeV3Result(
            prompts: [
                $this->makePromptCard(
                    'single',
                    'Prompt for Codex',
                    'V3-PROMPT-ABC12345',
                    'Main V3 prompt body for a single Codex flow.'
                ),
            ],
            source: [
                'source_type' => 'theory_page',
                'source_label' => 'Theory page',
                'id' => $page->id,
                'slug' => $page->slug,
                'topic' => $page->title,
                'url' => 'https://gramlyze.com/theory/voice/passive-voice-overview',
            ],
            preview: [
                'target_namespace' => 'AI\\ChatGptPro',
                'fully_qualified_class_name' => 'Database\\Seeders\\V3\\AI\\ChatGptPro\\PassiveVoiceOverviewV3QuestionsOnlySeeder',
                'definition_relative_path' => 'database/seeders/V3/AI/ChatGptPro/PassiveVoiceOverviewV3QuestionsOnlySeeder/definition.json',
            ],
            distribution: ['A1' => 4, 'B1' => 4],
            totalQuestions: 8,
            generationMode: 'single',
            promptAModeLabel: 'Mode A1 / repository-connected',
        );

        $this->mock(V3PromptGeneratorService::class, function ($mock) use ($result, $page) {
            $mock->shouldReceive('generate')
                ->once()
                ->with(Mockery::on(function (PromptGenerationInput $input) use ($page): bool {
                    return $input->sourceType === 'theory_page'
                        && $input->theoryPageId === $page->id
                        && $input->siteDomain === 'gramlyze.com'
                        && $input->targetNamespace === 'AI\\ChatGptPro'
                        && $input->levels === ['A1', 'B1']
                        && $input->questionsPerLevel === 4
                        && $input->generationMode === 'single'
                        && $input->promptAMode === 'repository_connected';
                }))
                ->andReturn($result);
        });

        $exitCode = Artisan::call('v3:generate-prompt', [
            '--source-type' => 'theory_page',
            '--theory-page-id' => $page->id,
            '--site-domain' => 'gramlyze.com',
            '--target-namespace' => 'AI\\ChatGptPro',
            '--level' => ['A1', 'B1'],
            '--questions-per-level' => 4,
            '--generation-mode' => 'single',
        ]);

        $output = str_replace("\r\n", "\n", Artisan::output());

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('V3 Prompt Generator', $output);
        $this->assertStringContainsString('CODEX PROMPT ID: V3-PROMPT-ABC12345', $output);
        $this->assertStringContainsString('Codex Summary (Top):', $output);
        $this->assertStringContainsString('Codex Summary (Bottom):', $output);
        $this->assertStringContainsString('Prompt Card 1: Prompt for Codex', $output);
        $this->assertStringContainsString('Wrapped Prompt Text:', $output);
        $this->assertStringContainsString('database/seeders/V3/AI/ChatGptPro/PassiveVoiceOverviewV3QuestionsOnlySeeder/definition.json', $output);
    }

    public function test_successful_split_mode_human_output(): void
    {
        $result = $this->makeV3Result(
            prompts: [
                $this->makePromptCard(
                    'llm_json',
                    'Prompt for LLM JSON generation',
                    'V3-PROMPT-1111AAAA',
                    'LLM JSON prompt body.'
                ),
                $this->makePromptCard(
                    'codex_seeder',
                    'Prompt for Codex seeder generation',
                    'V3-PROMPT-2222BBBB',
                    'Codex seeder prompt body.'
                ),
            ],
            source: [
                'source_type' => 'external_url',
                'source_label' => 'External theory URL',
                'topic' => 'Passive Voice',
                'url' => 'https://example.com/passive-voice',
            ],
            preview: [
                'target_namespace' => 'AI\\ChatGptPro',
                'fully_qualified_class_name' => 'Database\\Seeders\\V3\\AI\\ChatGptPro\\PassiveVoiceV3QuestionsOnlySeeder',
                'definition_relative_path' => 'database/seeders/V3/AI/ChatGptPro/PassiveVoiceV3QuestionsOnlySeeder/definition.json',
            ],
            distribution: ['B2' => 3],
            totalQuestions: 3,
            generationMode: 'split',
            promptAModeLabel: 'Mode A2 / no-repository fallback',
            warnings: ['Prompt буде згенеровано тільки на основі URL'],
        );

        $this->mock(V3PromptGeneratorService::class, function ($mock) use ($result) {
            $mock->shouldReceive('generate')
                ->once()
                ->with(Mockery::on(function (PromptGenerationInput $input): bool {
                    return $input->sourceType === 'external_url'
                        && $input->externalUrl === 'https://example.com/passive-voice'
                        && $input->targetNamespace === 'AI\\ChatGptPro'
                        && $input->levels === ['B2']
                        && $input->questionsPerLevel === 3
                        && $input->generationMode === 'split'
                        && $input->promptAMode === 'no_repository';
                }))
                ->andReturn($result);
        });

        $exitCode = Artisan::call('v3:generate-prompt', [
            '--source-type' => 'external_url',
            '--external-url' => 'https://example.com/passive-voice',
            '--target-namespace' => 'AI\\ChatGptPro',
            '--level' => ['B2'],
            '--questions-per-level' => 3,
            '--generation-mode' => 'split',
            '--prompt-a-mode' => 'no_repository',
        ]);

        $output = str_replace("\r\n", "\n", Artisan::output());

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Warnings', $output);
        $this->assertStringContainsString('Prompt буде згенеровано тільки на основі URL', $output);
        $this->assertStringContainsString('Prompt Card 1: Prompt for LLM JSON generation', $output);
        $this->assertStringContainsString('Prompt Card 2: Prompt for Codex seeder generation', $output);
        $this->assertStringContainsString('CODEX PROMPT ID: V3-PROMPT-1111AAAA', $output);
        $this->assertStringContainsString('CODEX PROMPT ID: V3-PROMPT-2222BBBB', $output);
    }

    public function test_json_output_mode(): void
    {
        $result = $this->makeV3Result(
            prompts: [
                $this->makePromptCard('single', 'Prompt for Codex', 'V3-PROMPT-JSON0001', 'JSON output body.'),
            ],
            source: ['source_type' => 'manual_topic', 'source_label' => 'Manual topic', 'topic' => 'Plural nouns'],
            preview: [
                'target_namespace' => 'AI\\ChatGptPro',
                'fully_qualified_class_name' => 'Database\\Seeders\\V3\\AI\\ChatGptPro\\PluralNounsV3QuestionsOnlySeeder',
                'definition_relative_path' => 'database/seeders/V3/AI/ChatGptPro/PluralNounsV3QuestionsOnlySeeder/definition.json',
            ],
            distribution: ['A1' => 5],
            totalQuestions: 5,
            generationMode: 'single',
            promptAModeLabel: 'Mode A1 / repository-connected',
        );

        $this->mock(V3PromptGeneratorService::class, function ($mock) use ($result) {
            $mock->shouldReceive('generate')->once()->andReturn($result);
        });

        $exitCode = Artisan::call('v3:generate-prompt', [
            '--manual-topic' => 'Plural nouns',
            '--target-namespace' => 'AI\\ChatGptPro',
            '--level' => ['A1'],
            '--format' => 'json',
        ]);

        $output = Artisan::output();
        $payload = json_decode($output, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertSame('manual_topic', $payload['source']['source_type']);
        $this->assertSame('V3-PROMPT-JSON0001', $payload['prompts'][0]['prompt_id']);
        $this->assertStringContainsString('CODEX PROMPT ID: V3-PROMPT-JSON0001', $payload['prompts'][0]['text']);
    }

    public function test_validation_failure_when_required_input_is_missing(): void
    {
        $exitCode = Artisan::call('v3:generate-prompt', [
            '--source-type' => 'manual_topic',
        ]);

        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Validation failed.', $output);
        $this->assertStringContainsString('manual_topic', $output);
    }

    public function test_output_file_is_written(): void
    {
        $outputRelativePath = 'storage/app/testing/v3-prompts/v3-single-output.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $this->cleanupPaths[] = $outputAbsolutePath;
        $this->cleanupPaths[] = dirname($outputAbsolutePath);

        $result = $this->makeV3Result(
            prompts: [
                $this->makePromptCard('single', 'Prompt for Codex', 'V3-PROMPT-OUTPUT01', 'Output file prompt body.'),
            ],
            source: ['source_type' => 'manual_topic', 'source_label' => 'Manual topic', 'topic' => 'Articles'],
            preview: [
                'target_namespace' => 'AI\\ChatGptPro',
                'fully_qualified_class_name' => 'Database\\Seeders\\V3\\AI\\ChatGptPro\\ArticlesV3QuestionsOnlySeeder',
                'definition_relative_path' => 'database/seeders/V3/AI/ChatGptPro/ArticlesV3QuestionsOnlySeeder/definition.json',
            ],
            distribution: ['A1' => 5],
            totalQuestions: 5,
            generationMode: 'single',
            promptAModeLabel: 'Mode A1 / repository-connected',
        );

        $this->mock(V3PromptGeneratorService::class, function ($mock) use ($result) {
            $mock->shouldReceive('generate')->once()->andReturn($result);
        });

        $exitCode = Artisan::call('v3:generate-prompt', [
            '--manual-topic' => 'Articles',
            '--target-namespace' => 'AI\\ChatGptPro',
            '--level' => ['A1'],
            '--output' => $outputRelativePath,
        ]);

        $output = Artisan::output();
        $contents = str_replace("\r\n", "\n", File::get($outputAbsolutePath));

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertStringContainsString('Consolidated output: ' . $outputRelativePath, $output);
        $this->assertStringContainsString('CODEX PROMPT ID: V3-PROMPT-OUTPUT01', $contents);
        $this->assertStringContainsString('V3 Prompt Generator', $contents);
    }

    public function test_write_dir_creates_per_prompt_files(): void
    {
        $writeDirRelativePath = 'storage/app/testing/v3-prompts/split-files';
        $writeDirAbsolutePath = base_path($writeDirRelativePath);
        $this->cleanupPaths[] = $writeDirAbsolutePath;

        $result = $this->makeV3Result(
            prompts: [
                $this->makePromptCard('llm_json', 'Prompt for LLM JSON generation', 'V3-PROMPT-FILE0001', 'LLM split file body.'),
                $this->makePromptCard('codex_seeder', 'Prompt for Codex seeder generation', 'V3-PROMPT-FILE0002', 'Codex split file body.'),
            ],
            source: ['source_type' => 'manual_topic', 'source_label' => 'Manual topic', 'topic' => 'Conditionals'],
            preview: [
                'target_namespace' => 'AI\\ChatGptPro',
                'fully_qualified_class_name' => 'Database\\Seeders\\V3\\AI\\ChatGptPro\\ConditionalsV3QuestionsOnlySeeder',
                'definition_relative_path' => 'database/seeders/V3/AI/ChatGptPro/ConditionalsV3QuestionsOnlySeeder/definition.json',
            ],
            distribution: ['B1' => 5],
            totalQuestions: 5,
            generationMode: 'split',
            promptAModeLabel: 'Mode A1 / repository-connected',
        );

        $this->mock(V3PromptGeneratorService::class, function ($mock) use ($result) {
            $mock->shouldReceive('generate')->once()->andReturn($result);
        });

        $exitCode = Artisan::call('v3:generate-prompt', [
            '--manual-topic' => 'Conditionals',
            '--target-namespace' => 'AI\\ChatGptPro',
            '--level' => ['B1'],
            '--generation-mode' => 'split',
            '--write-dir' => $writeDirRelativePath,
        ]);

        $output = Artisan::output();
        $firstPath = $writeDirAbsolutePath . DIRECTORY_SEPARATOR . '01-llm-json-v3-prompt-file0001.txt';
        $secondPath = $writeDirAbsolutePath . DIRECTORY_SEPARATOR . '02-codex-seeder-v3-prompt-file0002.txt';

        $this->assertSame(0, $exitCode);
        $this->assertDirectoryExists($writeDirAbsolutePath);
        $this->assertFileExists($firstPath);
        $this->assertFileExists($secondPath);
        $this->assertStringContainsString('Per-prompt files:', $output);
        $this->assertStringContainsString('CODEX PROMPT ID: V3-PROMPT-FILE0001', File::get($firstPath));
        $this->assertStringContainsString('CODEX PROMPT ID: V3-PROMPT-FILE0002', File::get($secondPath));
    }

    public function test_overwrite_protection_without_force(): void
    {
        $outputRelativePath = 'storage/app/testing/v3-prompts/existing-output.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        File::ensureDirectoryExists(dirname($outputAbsolutePath));
        File::put($outputAbsolutePath, 'existing');
        $this->cleanupPaths[] = $outputAbsolutePath;
        $this->cleanupPaths[] = dirname($outputAbsolutePath);

        $result = $this->makeV3Result(
            prompts: [
                $this->makePromptCard('single', 'Prompt for Codex', 'V3-PROMPT-KEEP0001', 'Should not overwrite.'),
            ],
            source: ['source_type' => 'manual_topic', 'source_label' => 'Manual topic', 'topic' => 'Gerunds'],
            preview: [
                'target_namespace' => 'AI\\ChatGptPro',
                'fully_qualified_class_name' => 'Database\\Seeders\\V3\\AI\\ChatGptPro\\GerundsV3QuestionsOnlySeeder',
                'definition_relative_path' => 'database/seeders/V3/AI/ChatGptPro/GerundsV3QuestionsOnlySeeder/definition.json',
            ],
            distribution: ['B1' => 5],
            totalQuestions: 5,
            generationMode: 'single',
            promptAModeLabel: 'Mode A1 / repository-connected',
        );

        $this->mock(V3PromptGeneratorService::class, function ($mock) use ($result) {
            $mock->shouldReceive('generate')->once()->andReturn($result);
        });

        $exitCode = Artisan::call('v3:generate-prompt', [
            '--manual-topic' => 'Gerunds',
            '--target-namespace' => 'AI\\ChatGptPro',
            '--level' => ['B1'],
            '--output' => $outputRelativePath,
        ]);

        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Refusing to overwrite existing files without --force', $output);
        $this->assertSame('existing', File::get($outputAbsolutePath));
    }

    public function test_write_skeleton_writes_expected_package_files(): void
    {
        $preview = $this->makeV3Preview('CLI Skeleton Passive Voice', 'Tests\\CodexCli\\V3Skeleton');
        $page = $this->createTheoryPage();
        $result = $this->makeV3Result(
            prompts: [
                $this->makePromptCard('single', 'Prompt for Codex', 'V3-PROMPT-SKELETON1', 'Skeleton write prompt body.'),
            ],
            source: [
                'source_type' => 'theory_page',
                'source_label' => 'Theory page',
                'id' => $page->id,
                'slug' => $page->slug,
                'title' => $page->title,
                'topic' => $page->title,
                'category_slug_path' => 'voice',
                'page_seeder_class' => 'Database\\Seeders\\Page_V3\\Voice\\PassiveVoiceOverviewTheorySeeder',
                'url' => 'https://gramlyze.com/theory/voice/passive-voice-overview',
            ],
            preview: $preview,
            distribution: ['A1' => 4, 'B1' => 4],
            totalQuestions: 8,
            generationMode: 'single',
            promptAModeLabel: 'Mode A1 / repository-connected',
        );

        $this->mock(V3PromptGeneratorService::class, function ($mock) use ($result) {
            $mock->shouldReceive('generate')->once()->andReturn($result);
        });

        $exitCode = Artisan::call('v3:generate-prompt', [
            '--source-type' => 'theory_page',
            '--theory-page-id' => $page->id,
            '--site-domain' => 'gramlyze.com',
            '--target-namespace' => 'Tests\\CodexCli\\V3Skeleton',
            '--level' => ['A1', 'B1'],
            '--questions-per-level' => 4,
            '--write-skeleton' => true,
        ]);

        $output = str_replace("\r\n", "\n", Artisan::output());
        $definition = json_decode(File::get($preview['definition_absolute_path']), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($preview['seeder_absolute_path']);
        $this->assertFileExists($preview['real_seeder_absolute_path']);
        $this->assertFileExists($preview['definition_absolute_path']);
        $this->assertFileExists($preview['localization_uk_absolute_path']);
        $this->assertFileExists($preview['localization_en_absolute_path']);
        $this->assertFileExists($preview['localization_pl_absolute_path']);
        $this->assertStringContainsString('Scaffold', $output);
        $this->assertStringContainsString('Written files: 6', $output);
        $this->assertSame([], $definition['questions']);
        $this->assertSame([], $definition['saved_test']['question_uuids']);
        $this->assertSame(
            $preview['fully_qualified_class_name'],
            $definition['saved_test']['filters']['seeder_classes'][0]
        );
        $this->assertSame('theory_page', $definition['saved_test']['filters']['prompt_generator']['source_type']);
    }

    public function test_combined_prompt_output_and_scaffold_output_works(): void
    {
        $preview = $this->makeV3Preview('CLI Combined Output', 'Tests\\CodexCli\\V3Combined');
        $outputRelativePath = 'storage/app/testing/v3-prompts/combined-output.txt';
        $outputAbsolutePath = base_path($outputRelativePath);
        $this->cleanupPaths[] = $outputAbsolutePath;
        $this->cleanupPaths[] = dirname($outputAbsolutePath);

        $result = $this->makeV3Result(
            prompts: [
                $this->makePromptCard('single', 'Prompt for Codex', 'V3-PROMPT-COMBINED', 'Combined output body.'),
            ],
            source: ['source_type' => 'manual_topic', 'source_label' => 'Manual topic', 'topic' => 'CLI Combined Output'],
            preview: $preview,
            distribution: ['A2' => 5],
            totalQuestions: 5,
            generationMode: 'single',
            promptAModeLabel: 'Mode A1 / repository-connected',
        );

        $this->mock(V3PromptGeneratorService::class, function ($mock) use ($result) {
            $mock->shouldReceive('generate')->once()->andReturn($result);
        });

        $exitCode = Artisan::call('v3:generate-prompt', [
            '--manual-topic' => 'CLI Combined Output',
            '--target-namespace' => 'Tests\\CodexCli\\V3Combined',
            '--level' => ['A2'],
            '--output' => $outputRelativePath,
            '--write-skeleton' => true,
        ]);

        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputAbsolutePath);
        $this->assertFileExists($preview['definition_absolute_path']);
        $this->assertStringContainsString('Scaffold: written (6 files)', $output);
        $this->assertStringContainsString('CODEX PROMPT ID: V3-PROMPT-COMBINED', File::get($outputAbsolutePath));
    }

    public function test_scaffold_overwrite_protection_without_force(): void
    {
        $preview = $this->makeV3Preview('CLI Overwrite Guard', 'Tests\\CodexCli\\V3OverwriteGuard');
        File::ensureDirectoryExists(dirname($preview['definition_absolute_path']));
        File::put($preview['definition_absolute_path'], '{"existing":true}');

        $result = $this->makeV3Result(
            prompts: [
                $this->makePromptCard('single', 'Prompt for Codex', 'V3-PROMPT-OVERWRITE', 'Overwrite guard body.'),
            ],
            source: ['source_type' => 'manual_topic', 'source_label' => 'Manual topic', 'topic' => 'CLI Overwrite Guard'],
            preview: $preview,
            distribution: ['B1' => 5],
            totalQuestions: 5,
            generationMode: 'single',
            promptAModeLabel: 'Mode A1 / repository-connected',
        );

        $this->mock(V3PromptGeneratorService::class, function ($mock) use ($result) {
            $mock->shouldReceive('generate')->once()->andReturn($result);
        });

        $exitCode = Artisan::call('v3:generate-prompt', [
            '--manual-topic' => 'CLI Overwrite Guard',
            '--target-namespace' => 'Tests\\CodexCli\\V3OverwriteGuard',
            '--level' => ['B1'],
            '--write-skeleton' => true,
        ]);

        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Refusing to overwrite existing files without --force', $output);
        $this->assertSame('{"existing":true}', File::get($preview['definition_absolute_path']));
    }

    public function test_scaffold_overwrite_with_force_replaces_existing_files(): void
    {
        $preview = $this->makeV3Preview('CLI Overwrite Force', 'Tests\\CodexCli\\V3OverwriteForce');
        File::ensureDirectoryExists(dirname($preview['definition_absolute_path']));
        File::put($preview['definition_absolute_path'], '{"existing":true}');

        $result = $this->makeV3Result(
            prompts: [
                $this->makePromptCard('single', 'Prompt for Codex', 'V3-PROMPT-FORCESKEL', 'Force scaffold body.'),
            ],
            source: ['source_type' => 'manual_topic', 'source_label' => 'Manual topic', 'topic' => 'CLI Overwrite Force'],
            preview: $preview,
            distribution: ['B2' => 3],
            totalQuestions: 3,
            generationMode: 'single',
            promptAModeLabel: 'Mode A1 / repository-connected',
        );

        $this->mock(V3PromptGeneratorService::class, function ($mock) use ($result) {
            $mock->shouldReceive('generate')->once()->andReturn($result);
        });

        $exitCode = Artisan::call('v3:generate-prompt', [
            '--manual-topic' => 'CLI Overwrite Force',
            '--target-namespace' => 'Tests\\CodexCli\\V3OverwriteForce',
            '--level' => ['B2'],
            '--write-skeleton' => true,
            '--force' => true,
        ]);

        $definition = json_decode(File::get($preview['definition_absolute_path']), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertSame('CLI Overwrite Force V3 Questions Test', $definition['saved_test']['name']);
    }

    public function test_json_output_mode_includes_scaffold_metadata_when_requested(): void
    {
        $preview = $this->makeV3Preview('CLI JSON Scaffold', 'Tests\\CodexCli\\V3JsonScaffold');
        $result = $this->makeV3Result(
            prompts: [
                $this->makePromptCard('single', 'Prompt for Codex', 'V3-PROMPT-JSONSKEL', 'JSON scaffold body.'),
            ],
            source: ['source_type' => 'manual_topic', 'source_label' => 'Manual topic', 'topic' => 'CLI JSON Scaffold'],
            preview: $preview,
            distribution: ['A1' => 5],
            totalQuestions: 5,
            generationMode: 'single',
            promptAModeLabel: 'Mode A1 / repository-connected',
        );

        $this->mock(V3PromptGeneratorService::class, function ($mock) use ($result) {
            $mock->shouldReceive('generate')->once()->andReturn($result);
        });

        $exitCode = Artisan::call('v3:generate-prompt', [
            '--manual-topic' => 'CLI JSON Scaffold',
            '--target-namespace' => 'Tests\\CodexCli\\V3JsonScaffold',
            '--level' => ['A1'],
            '--format' => 'json',
            '--write-skeleton' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertTrue($payload['scaffold']['requested']);
        $this->assertSame(6, $payload['scaffold']['count']);
        $this->assertCount(6, $payload['scaffold']['planned']);
        $this->assertCount(6, $payload['scaffold']['written']);
    }

    public function test_split_mode_still_writes_only_one_scaffold_package(): void
    {
        $preview = $this->makeV3Preview('CLI Split Package', 'Tests\\CodexCli\\V3SplitPackage');
        $result = $this->makeV3Result(
            prompts: [
                $this->makePromptCard('llm_json', 'Prompt for LLM JSON generation', 'V3-PROMPT-SPLIT001', 'Split LLM body.'),
                $this->makePromptCard('codex_seeder', 'Prompt for Codex seeder generation', 'V3-PROMPT-SPLIT002', 'Split Codex body.'),
            ],
            source: ['source_type' => 'manual_topic', 'source_label' => 'Manual topic', 'topic' => 'CLI Split Package'],
            preview: $preview,
            distribution: ['C1' => 2],
            totalQuestions: 2,
            generationMode: 'split',
            promptAModeLabel: 'Mode A1 / repository-connected',
        );

        $this->mock(V3PromptGeneratorService::class, function ($mock) use ($result) {
            $mock->shouldReceive('generate')->once()->andReturn($result);
        });

        $exitCode = Artisan::call('v3:generate-prompt', [
            '--manual-topic' => 'CLI Split Package',
            '--target-namespace' => 'Tests\\CodexCli\\V3SplitPackage',
            '--level' => ['C1'],
            '--generation-mode' => 'split',
            '--write-skeleton' => true,
        ]);

        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Prompt Card 1: Prompt for LLM JSON generation', $output);
        $this->assertStringContainsString('Prompt Card 2: Prompt for Codex seeder generation', $output);
        $this->assertStringContainsString('Written files: 6', $output);
    }

    private function createTheoryPage(): Page
    {
        $category = PageCategory::create([
            'title' => 'Voice',
            'slug' => 'voice',
            'language' => 'uk',
            'type' => 'theory',
            'seeder' => 'Database\\Seeders\\Page_v2\\Grammar\\PassiveVoiceSeeder',
        ]);

        return Page::create([
            'title' => 'Passive Voice Overview',
            'slug' => 'passive-voice-overview',
            'type' => 'theory',
            'page_category_id' => $category->id,
            'seeder' => 'Database\\Seeders\\Page_v2\\Grammar\\PassiveVoiceSeeder',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function makeV3Preview(string $topic, string $namespace): array
    {
        $preview = app(V3SeederBlueprintService::class)->buildPreview($namespace, $topic);
        $this->cleanupPaths[] = $preview['seeder_absolute_path'];
        $this->cleanupPaths[] = $preview['package_absolute_path'];

        return $preview;
    }

    /**
     * @param  array<int, array<string, mixed>>  $prompts
     * @param  array<string, mixed>  $source
     * @param  array<string, mixed>  $preview
     * @param  array<string, int>  $distribution
     * @return array<string, mixed>
     */
    private function makeV3Result(
        array $prompts,
        array $source,
        array $preview,
        array $distribution,
        int $totalQuestions,
        string $generationMode,
        string $promptAModeLabel,
        array $warnings = [],
    ): array {
        return [
            'source' => $source,
            'preview' => $preview,
            'reference_files' => [],
            'warnings' => $warnings,
            'distribution' => $distribution,
            'total_questions' => $totalQuestions,
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
            'goal' => 'Підготувати ' . $title . ' для тестового CLI scenario.',
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
