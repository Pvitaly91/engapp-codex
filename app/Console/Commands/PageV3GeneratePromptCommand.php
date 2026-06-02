<?php

namespace App\Console\Commands;

use App\Http\Requests\Admin\GeneratePageV3PromptRequest;
use App\Services\PageV3PromptGenerator\Data\PagePromptGenerationInput;
use App\Services\PageV3PromptGenerator\PageV3PromptGeneratorService;
use App\Services\PageV3PromptGenerator\PageV3SkeletonWriterService;
use App\Support\Console\CodexPromptConsolePresenter;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PageV3GeneratePromptCommand extends Command
{
    protected $signature = 'page-v3:generate-prompt
        {--source-type= : manual_topic | external_url}
        {--manual-topic= : Manual topic label}
        {--external-url= : External theory URL}
        {--category-mode= : existing | new | ai_select}
        {--existing-category-id= : Existing theory category database id}
        {--new-category-title= : New theory category title}
        {--generation-mode=single : single | split}
        {--prompt-a-mode=repository_connected : repository_connected | no_repository}
        {--format=human : human | json}
        {--output= : Write consolidated output to a file}
        {--write-dir= : Write one .txt file per prompt card into a directory}
        {--write-skeleton : Create the resolved Page_V3 scaffold package files}
        {--force : Overwrite existing output files}';

    protected $description = 'Generate Page_V3 prompt cards through the existing Page_V3 prompt generator service';

    public function __construct(
        private PageV3PromptGeneratorService $pageV3PromptGeneratorService,
        private CodexPromptConsolePresenter $codexPromptConsolePresenter,
        private PageV3SkeletonWriterService $pageV3SkeletonWriterService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $validated = $this->validatedInput();
        } catch (ValidationException $exception) {
            $this->renderValidationErrors($exception);

            return self::FAILURE;
        }

        try {
            $result = $this->pageV3PromptGeneratorService->generate(
                PagePromptGenerationInput::fromArray(Arr::except($validated, ['format', 'output', 'write_dir']))
            );
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        try {
            $format = (string) $validated['format'];
            $outputPath = $this->codexPromptConsolePresenter->resolvePath($validated['output'] ?? null);
            $writeDirectory = $this->codexPromptConsolePresenter->resolvePath($validated['write_dir'] ?? null);
            $prompts = array_values((array) ($result['prompts'] ?? []));
            $writeSkeleton = (bool) $this->option('write-skeleton');
            $force = (bool) $this->option('force');
            $plannedScaffoldPaths = $writeSkeleton
                ? $this->pageV3SkeletonWriterService->plannedFiles($result)
                : [];

            $plannedPaths = $outputPath !== null ? [$outputPath] : [];

            if ($writeDirectory !== null) {
                $plannedPaths = array_merge(
                    $plannedPaths,
                    $this->codexPromptConsolePresenter->plannedPromptFilePaths($writeDirectory, $prompts)
                );
            }

            $plannedPaths = array_values(array_merge($plannedPaths, $plannedScaffoldPaths));

            if (! $force) {
                $existingPaths = $this->codexPromptConsolePresenter->existingPaths($plannedPaths);

                if ($existingPaths !== []) {
                    $this->error('Refusing to overwrite existing files without --force:');

                    foreach ($existingPaths as $existingPath) {
                        $this->line('- ' . $this->codexPromptConsolePresenter->relativePath($existingPath));
                    }

                    return self::FAILURE;
                }
            }

            $writtenPromptFiles = [];
            $writtenScaffold = [
                'requested' => $writeSkeleton,
                'planned' => array_map(
                    fn (string $path): string => $this->codexPromptConsolePresenter->relativePath($path),
                    $plannedScaffoldPaths
                ),
                'written' => [],
                'count' => 0,
                'skipped_reason' => $writeSkeleton ? null : null,
            ];

            if ($writeSkeleton) {
                $written = $this->pageV3SkeletonWriterService->write($result, $force);
                $writtenScaffold['written'] = (array) ($written['written'] ?? []);
                $writtenScaffold['count'] = (int) ($written['count'] ?? 0);
            }

            $payload = $result;
            $payload['scaffold'] = $writtenScaffold;

            $consolidatedOutput = $format === 'json'
                ? $this->codexPromptConsolePresenter->renderJsonOutput($payload)
                : $this->codexPromptConsolePresenter->renderHumanOutput(
                    'Page_V3 Prompt Generator',
                    $this->humanSections($result, $writtenScaffold),
                    $prompts,
                );

            if ($outputPath !== null) {
                $this->codexPromptConsolePresenter->writeFile($outputPath, $consolidatedOutput);
            }

            if ($writeDirectory !== null) {
                $writtenPromptFiles = $this->codexPromptConsolePresenter->writePromptFiles($writeDirectory, $prompts);
            }
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if ($format === 'json' && $outputPath === null) {
            $this->line($consolidatedOutput);

            return self::SUCCESS;
        }

        if ($outputPath === null) {
            $this->line($consolidatedOutput);

            if ($writtenPromptFiles !== []) {
                $this->newLine();
                $this->line('Per-prompt files:');

                foreach ($writtenPromptFiles as $file) {
                    $this->line('- ' . $file['relative_path']);
                }
            }

            return self::SUCCESS;
        }

        $this->info('Page_V3 prompt generation completed.');
        $this->line('Format: ' . $format);
        $this->line('Source type: ' . ($result['source']['source_type'] ?? 'unknown'));
        $this->line('Category mode: ' . ($result['category']['mode'] ?? 'unknown'));
        $this->line('Generation mode: ' . ($result['generation_mode'] ?? 'unknown'));
        $this->line('Prompt A mode: ' . ($result['prompt_a_mode_label'] ?? 'unknown'));
        $this->line('Consolidated output: ' . $this->codexPromptConsolePresenter->relativePath($outputPath));
        $this->line(
            $writtenScaffold['requested']
                ? 'Scaffold: written (' . $writtenScaffold['count'] . ' files)'
                : 'Scaffold: not requested'
        );

        if ($writtenPromptFiles !== []) {
            $this->line('Per-prompt files:');

            foreach ($writtenPromptFiles as $file) {
                $this->line('- ' . $file['relative_path']);
            }
        }

        foreach ((array) ($writtenScaffold['written'] ?? []) as $path) {
            $this->line('- ' . $path);
        }

        foreach ((array) ($result['warnings'] ?? []) as $warning) {
            $this->warn((string) $warning);
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedInput(): array
    {
        $normalized = GeneratePageV3PromptRequest::normalizeInput([
            'source_type' => $this->resolvedSourceType(),
            'manual_topic' => $this->option('manual-topic'),
            'external_url' => $this->option('external-url'),
            'category_mode' => $this->resolvedCategoryMode(),
            'existing_category_id' => $this->option('existing-category-id'),
            'new_category_title' => $this->option('new-category-title'),
            'generation_mode' => $this->option('generation-mode'),
            'prompt_a_mode' => $this->option('prompt-a-mode'),
        ]);

        return Validator::make(
            array_merge($normalized, [
                'format' => trim((string) $this->option('format')),
                'output' => $this->option('output'),
                'write_dir' => $this->option('write-dir'),
            ]),
            array_merge(
                GeneratePageV3PromptRequest::sharedRules($normalized),
                [
                    'format' => ['required', Rule::in(['human', 'json'])],
                    'output' => ['nullable', 'string', 'max:2000'],
                    'write_dir' => ['nullable', 'string', 'max:2000'],
                ]
            ),
            array_merge(
                GeneratePageV3PromptRequest::sharedMessages(),
                [
                    'format.required' => 'Вкажіть формат output.',
                    'format.in' => 'Підтримуються тільки формати human або json.',
                    'output.max' => 'Шлях для --output занадто довгий.',
                    'write_dir.max' => 'Шлях для --write-dir занадто довгий.',
                ]
            ),
            GeneratePageV3PromptRequest::sharedAttributes()
        )->validate();
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<int, array<string, mixed>>
     */
    private function humanSections(array $result, array $scaffold = []): array
    {
        $contextLines = [
            '- Source type: ' . ($result['source']['source_label'] ?? $result['source']['source_type'] ?? 'unknown'),
            '- Category mode: ' . ($result['category']['mode_label'] ?? $result['category']['mode'] ?? 'unknown'),
            '- Generation mode: ' . ($result['generation_mode'] ?? 'unknown'),
            '- Prompt A mode: ' . ($result['prompt_a_mode_label'] ?? 'unknown'),
        ];

        if (! empty($result['source']['topic'])) {
            $contextLines[] = '- Topic: `' . $result['source']['topic'] . '`';
        }

        if (! empty($result['source']['url'])) {
            $contextLines[] = '- Source URL: `' . $result['source']['url'] . '`';
        }

        if (($result['category']['mode'] ?? null) === 'existing' && ! empty($result['category']['selected_category'])) {
            $selectedCategory = (array) $result['category']['selected_category'];
            $contextLines[] = '- Existing category: `' . ($selectedCategory['title'] ?? '') . '`';
            $contextLines[] = '- Existing category slug: `' . ($selectedCategory['slug'] ?? '') . '`';
        }

        if (($result['category']['mode'] ?? null) === 'new') {
            $contextLines[] = '- New category title: `' . ($result['category']['new_category_title'] ?? '') . '`';
            $contextLines[] = '- New category slug: `' . ($result['category']['new_category_slug'] ?? '') . '`';
        }

        $previewLines = [
            '- Planned page class: `' . ($result['preview']['page_fully_qualified_class_name'] ?? '') . '`',
            '- Planned page definition: `' . ($result['preview']['page_definition_relative_path'] ?? '') . '`',
            '- Planned category slug: `' . ($result['preview']['category_slug'] ?? '') . '`',
            '- Category catalog size: ' . (int) ($result['category_catalog_count'] ?? 0),
        ];

        if (! empty($result['preview']['category_definition_relative_path'])) {
            $previewLines[] = '- Planned category definition: `' . $result['preview']['category_definition_relative_path'] . '`';
        }

        $scaffoldLines = [];

        if (($scaffold['requested'] ?? false) === true) {
            $scaffoldLines[] = '- Requested: yes';

            if (! empty($scaffold['planned'])) {
                $scaffoldLines[] = '- Planned files:';

                foreach ((array) $scaffold['planned'] as $path) {
                    $scaffoldLines[] = '  - `' . $path . '`';
                }
            }

            $scaffoldLines[] = '- Written files: ' . (int) ($scaffold['count'] ?? 0);

            foreach ((array) ($scaffold['written'] ?? []) as $path) {
                $scaffoldLines[] = '  - `' . $path . '`';
            }
        }

        return array_values(array_filter([
            [
                'title' => 'Context',
                'lines' => $contextLines,
            ],
            ! empty($result['warnings']) ? [
                'title' => 'Warnings',
                'lines' => array_map(static fn ($warning): string => '- ' . (string) $warning, (array) $result['warnings']),
            ] : null,
            [
                'title' => 'Preview',
                'lines' => $previewLines,
            ],
            $scaffoldLines !== [] ? [
                'title' => 'Scaffold',
                'lines' => $scaffoldLines,
            ] : null,
        ]));
    }

    private function resolvedSourceType(): string
    {
        $sourceType = trim((string) ($this->option('source-type') ?? ''));

        if ($sourceType !== '') {
            return $sourceType;
        }

        return filled($this->option('external-url')) ? 'external_url' : 'manual_topic';
    }

    private function resolvedCategoryMode(): string
    {
        $categoryMode = trim((string) ($this->option('category-mode') ?? ''));

        if ($categoryMode !== '') {
            return $categoryMode;
        }

        if (filled($this->option('existing-category-id'))) {
            return 'existing';
        }

        if (filled($this->option('new-category-title'))) {
            return 'new';
        }

        return 'ai_select';
    }

    private function renderValidationErrors(ValidationException $exception): void
    {
        $this->error('Validation failed.');

        foreach ($exception->errors() as $field => $messages) {
            foreach ($messages as $message) {
                $this->line(sprintf('- %s: %s', $field, $message));
            }
        }
    }
}
