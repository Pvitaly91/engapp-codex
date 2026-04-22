<?php

namespace App\Console\Commands;

use App\Services\PolyglotV3PromptGeneratorService;
use App\Services\PolyglotV3SkeletonWriterService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PolyglotGenerateV3PromptCommand extends Command
{
    protected $signature = 'polyglot:generate-v3-prompt
        {theoryCategorySlug : Theory category slug, for example verb-to-be}
        {theoryPageSlug : Theory page slug, for example verb-to-be-present}
        {lessonSlug : Target lesson slug}
        {lessonOrder : Target lesson order}
        {--title= : Lesson title}
        {--topic= : Lesson topic label}
        {--seeder= : Seeder class base name}
        {--course=polyglot-english-a1 : Course slug}
        {--level=A1 : CEFR level}
        {--previous= : Previous lesson slug}
        {--next= : Next lesson slug}
        {--items=24 : Expected item count}
        {--prompt-id= : Explicit prompt id}
        {--output= : Write prompt text to a file instead of stdout-only}
        {--write-skeleton : Create canonical V3 Polyglot skeleton files}
        {--force : Overwrite existing output or scaffold files}';

    protected $description = 'Generate a theory-aware V3 Polyglot authoring prompt and optional skeleton package';

    public function __construct(
        private PolyglotV3PromptGeneratorService $promptGenerator,
        private PolyglotV3SkeletonWriterService $skeletonWriter,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $generated = $this->promptGenerator->generate([
                'theory_category_slug' => $this->argument('theoryCategorySlug'),
                'theory_page_slug' => $this->argument('theoryPageSlug'),
                'lesson_slug' => $this->argument('lessonSlug'),
                'lesson_order' => (int) $this->argument('lessonOrder'),
                'lesson_title' => $this->option('title'),
                'topic' => $this->option('topic'),
                'seeder' => $this->option('seeder'),
                'course_slug' => $this->option('course'),
                'level' => $this->option('level'),
                'previous_lesson_slug' => $this->option('previous'),
                'next_lesson_slug' => $this->option('next'),
                'items_count' => (int) $this->option('items'),
                'prompt_id' => $this->option('prompt-id'),
            ]);
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $outputPath = $this->resolveOutputPath($this->option('output'));
        $writeSkeleton = (bool) $this->option('write-skeleton');
        $force = (bool) $this->option('force');
        $plannedWrites = $writeSkeleton ? $this->skeletonWriter->plannedFiles($generated) : [];

        if ($outputPath !== null) {
            $plannedWrites[] = $outputPath;
        }

        if (! $force) {
            $existingPaths = array_values(array_filter($plannedWrites, fn (string $path) => File::exists($path)));

            if ($existingPaths !== []) {
                $this->error('Refusing to overwrite existing files without --force:');
                foreach ($existingPaths as $existingPath) {
                    $this->line('- ' . $this->relativePath($existingPath));
                }

                return self::FAILURE;
            }
        }

        $this->info(sprintf(
            'Resolved theory page: %s [%s]',
            $generated['theory_context']['page_title'],
            $generated['theory_context']['route_path']
        ));
        $this->line($this->promptGenerator->formatPromptIdLine($generated['prompt_id']));
        $this->line('Target V3 paths:');
        foreach ([
            $generated['target_paths']['loader_relative_path'],
            $generated['target_paths']['real_seeder_relative_path'],
            $generated['target_paths']['definition_relative_path'],
            $generated['target_paths']['uk_relative_path'],
            $generated['target_paths']['en_relative_path'],
            $generated['target_paths']['pl_relative_path'],
        ] as $relativePath) {
            $this->line('- ' . $relativePath);
        }

        if ($outputPath !== null) {
            File::ensureDirectoryExists(dirname($outputPath));
            File::put($outputPath, $generated['prompt_text']);
            $this->line('Prompt output: ' . $this->relativePath($outputPath));
        } else {
            $this->line('Prompt output: stdout');
        }

        if ($writeSkeleton) {
            try {
                $written = $this->skeletonWriter->write($generated, $force);
                $this->line('Scaffold: written (' . $written['count'] . ' files)');
            } catch (\Throwable $exception) {
                $this->error($exception->getMessage());

                return self::FAILURE;
            }
        } else {
            $this->line('Scaffold: not requested');
        }

        if ($outputPath === null) {
            $this->newLine();
            $this->line($generated['prompt_text']);
        }

        return self::SUCCESS;
    }

    private function resolveOutputPath(mixed $value): ?string
    {
        $path = trim((string) ($value ?? ''));

        if ($path === '') {
            return null;
        }

        if ($this->isAbsolutePath($path)) {
            return $path;
        }

        return base_path($path);
    }

    private function isAbsolutePath(string $path): bool
    {
        return preg_match('/^(?:[A-Za-z]:[\\\\\\/]|\\\\\\\\)/', $path) === 1;
    }

    private function relativePath(string $absolutePath): string
    {
        $normalizedPath = str_replace('\\', '/', $absolutePath);
        $normalizedRoot = rtrim(str_replace('\\', '/', base_path()), '/');

        return ltrim((string) \Illuminate\Support\Str::after($normalizedPath, $normalizedRoot), '/');
    }
}
