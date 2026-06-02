<?php

namespace App\Console\Commands;

use App\Http\Requests\Admin\GenerateV3PromptRequest;
use App\Services\V3PromptGenerator\Data\PromptGenerationInput;
use App\Services\V3PromptGenerator\V3PromptGeneratorService;
use App\Services\V3PromptGenerator\V3SkeletonWriterService;
use App\Support\Console\CodexPromptConsolePresenter;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class V3GeneratePromptCommand extends Command
{
    protected $signature = 'v3:generate-prompt
        {--source-type= : manual_topic | theory_page | external_url}
        {--theory-page-id= : Existing theory page database id}
        {--manual-topic= : Manual topic label}
        {--external-url= : External theory URL}
        {--site-domain=gramlyze.com : Public site domain for theory page URLs}
        {--target-namespace=AI : Target namespace inside database/seeders/V3}
        {--level=* : One or more CEFR levels, repeat the option for multiple values}
        {--questions-per-level=5 : Number of questions per selected level}
        {--generation-mode=single : single | split}
        {--prompt-a-mode=repository_connected : repository_connected | no_repository}
        {--format=human : human | json}
        {--output= : Write consolidated output to a file}
        {--write-dir= : Write one .txt file per prompt card into a directory}
        {--write-skeleton : Create the resolved V3 scaffold package files}
        {--force : Overwrite existing output files}';

    protected $description = 'Generate classic V3 prompt cards through the existing V3 prompt generator service';

    public function __construct(
        private V3PromptGeneratorService $v3PromptGeneratorService,
        private CodexPromptConsolePresenter $codexPromptConsolePresenter,
        private V3SkeletonWriterService $v3SkeletonWriterService,
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
            $result = $this->v3PromptGeneratorService->generate(
                PromptGenerationInput::fromArray(Arr::except($validated, ['format', 'output', 'write_dir']))
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
                ? $this->v3SkeletonWriterService->plannedFiles($result)
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
                $written = $this->v3SkeletonWriterService->write($result, $force);
                $writtenScaffold['written'] = (array) ($written['written'] ?? []);
                $writtenScaffold['count'] = (int) ($written['count'] ?? 0);
            }

            $payload = $result;
            $payload['scaffold'] = $writtenScaffold;

            $consolidatedOutput = $format === 'json'
                ? $this->codexPromptConsolePresenter->renderJsonOutput($payload)
                : $this->codexPromptConsolePresenter->renderHumanOutput(
                    'V3 Prompt Generator',
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

        $this->info('V3 prompt generation completed.');
        $this->line('Format: ' . $format);
        $this->line('Source type: ' . ($result['source']['source_type'] ?? 'unknown'));
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
        $normalized = GenerateV3PromptRequest::normalizeInput([
            'source_type' => $this->resolvedSourceType(),
            'theory_page_id' => $this->option('theory-page-id'),
            'manual_topic' => $this->option('manual-topic'),
            'external_url' => $this->option('external-url'),
            'site_domain' => $this->option('site-domain'),
            'target_namespace' => $this->option('target-namespace'),
            'levels' => $this->resolvedLevels(),
            'questions_per_level' => $this->option('questions-per-level'),
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
                GenerateV3PromptRequest::sharedRules($normalized),
                [
                    'format' => ['required', Rule::in(['human', 'json'])],
                    'output' => ['nullable', 'string', 'max:2000'],
                    'write_dir' => ['nullable', 'string', 'max:2000'],
                ]
            ),
            array_merge(
                GenerateV3PromptRequest::sharedMessages(),
                [
                    'format.required' => 'Вкажіть формат output.',
                    'format.in' => 'Підтримуються тільки формати human або json.',
                    'output.max' => 'Шлях для --output занадто довгий.',
                    'write_dir.max' => 'Шлях для --write-dir занадто довгий.',
                ]
            ),
            GenerateV3PromptRequest::sharedAttributes()
        )->validate();
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<int, array<string, mixed>>
     */
    private function humanSections(array $result, array $scaffold = []): array
    {
        $distributionLines = array_map(
            static fn (string $level, int $count): string => sprintf('- %s: %d question(s)', $level, $count),
            array_keys((array) ($result['distribution'] ?? [])),
            array_values((array) ($result['distribution'] ?? []))
        );

        $contextLines = [
            '- Source type: ' . ($result['source']['source_label'] ?? $result['source']['source_type'] ?? 'unknown'),
            '- Generation mode: ' . ($result['generation_mode'] ?? 'unknown'),
            '- Prompt A mode: ' . ($result['prompt_a_mode_label'] ?? 'unknown'),
            '- Site domain: `' . ($result['source']['site_domain'] ?? $this->option('site-domain')) . '`',
        ];

        if (! empty($result['source']['topic'])) {
            $contextLines[] = '- Topic: `' . $result['source']['topic'] . '`';
        }

        if (! empty($result['source']['id'])) {
            $contextLines[] = '- Theory page id: ' . (int) $result['source']['id'];
        }

        if (! empty($result['source']['slug'])) {
            $contextLines[] = '- Theory page slug: `' . $result['source']['slug'] . '`';
        }

        if (! empty($result['source']['url'])) {
            $contextLines[] = '- Source URL: `' . $result['source']['url'] . '`';
        }

        $previewLines = [
            '- Target namespace: `' . ($result['preview']['target_namespace'] ?? '') . '`',
            '- Planned class: `' . ($result['preview']['fully_qualified_class_name'] ?? '') . '`',
            '- Planned definition: `' . ($result['preview']['definition_relative_path'] ?? '') . '`',
            '- Total questions: ' . (int) ($result['total_questions'] ?? 0),
        ];

        if ($distributionLines !== []) {
            $previewLines[] = '- Distribution:';

            foreach ($distributionLines as $distributionLine) {
                $previewLines[] = '  ' . $distributionLine;
            }
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

    /**
     * @return array<int, string>
     */
    private function resolvedLevels(): array
    {
        $levels = array_values(array_filter(
            array_map(
                static fn ($level): string => trim((string) $level),
                (array) $this->option('level')
            ),
            static fn (string $level): bool => $level !== ''
        ));

        return $levels !== [] ? $levels : ['A1'];
    }

    private function resolvedSourceType(): string
    {
        $sourceType = trim((string) ($this->option('source-type') ?? ''));

        if ($sourceType !== '') {
            return $sourceType;
        }

        if (filled($this->option('theory-page-id'))) {
            return 'theory_page';
        }

        if (filled($this->option('external-url'))) {
            return 'external_url';
        }

        return 'manual_topic';
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
