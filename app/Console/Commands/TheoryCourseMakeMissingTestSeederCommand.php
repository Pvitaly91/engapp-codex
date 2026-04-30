<?php

namespace App\Console\Commands;

use App\Services\TheoryCourseManifestService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TheoryCourseMakeMissingTestSeederCommand extends Command
{
    protected $signature = 'theory-course:make-missing-test-seeder
        {categorySlug : Theory category slug or category slug path}
        {pageSlug : Theory page slug}
        {--type=standard : standard or polyglot}
        {--write-skeleton : Delegate to the existing V3 generator and write skeleton package files}
        {--force : Allow overwriting generator skeleton output}';

    protected $description = 'Create a scoped missing-test seeder plan, optionally delegating to existing V3 skeleton generators';

    public function __construct(
        private TheoryCourseManifestService $manifestService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $type = Str::lower(trim((string) $this->option('type')));

        if (! in_array($type, ['standard', 'polyglot'], true)) {
            $this->error('Unsupported type. Use --type=standard or --type=polyglot.');

            return self::FAILURE;
        }

        $manifest = $this->manifestService->build();
        $lesson = $this->resolveLesson(
            (string) $this->argument('categorySlug'),
            (string) $this->argument('pageSlug'),
            $manifest
        );

        if (! is_array($lesson)) {
            $this->error('Theory course lesson was not found.');

            return self::FAILURE;
        }

        $plan = $this->buildPlan($lesson, $type);
        $planPath = $this->writePlan($plan, $lesson, $type);

        $this->info('Missing-test seeder plan created.');
        $this->line('Plan: '.$this->relativePath($planPath));
        $this->line('Generator command: '.$plan['generator_cli_command']);

        if (! (bool) $this->option('write-skeleton')) {
            $this->line('Skeleton: not requested. Re-run with --write-skeleton to create repo seed files.');

            return self::SUCCESS;
        }

        $this->line('Skeleton: delegating to '.$plan['generator_artisan_command']);

        $exitCode = Artisan::call($plan['generator_artisan_command'], $plan['generator_artisan_parameters']);
        $output = trim(Artisan::output());

        if ($output !== '') {
            $this->line($output);
        }

        return $exitCode === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function resolveLesson(string $categorySlug, string $pageSlug, array $manifest): ?array
    {
        $categoryPath = $this->normalizeCategoryPath($categorySlug);
        $pageSlug = Str::lower(trim($pageSlug));
        $lesson = $this->manifestService->findLesson($manifest, $categoryPath, $pageSlug);

        if (is_array($lesson)) {
            return $lesson;
        }

        $matches = collect($manifest['flat_lessons'] ?? [])
            ->filter(function (mixed $candidate) use ($categoryPath, $pageSlug): bool {
                if (! is_array($candidate) || ($candidate['page_slug'] ?? null) !== $pageSlug) {
                    return false;
                }

                return ($candidate['category_slug'] ?? null) === $categoryPath
                    || Str::endsWith((string) ($candidate['category_slug_path'] ?? ''), '/'.$categoryPath);
            })
            ->values();

        return $matches->count() === 1 ? $matches->first() : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPlan(array $lesson, string $type): array
    {
        $force = (bool) $this->option('force');

        if ($type === 'polyglot') {
            $seeder = $this->polyglotSeederClass($lesson);
            $lessonSlug = 'theory-course-'.$this->safeSlug($lesson['category_slug'] ?? '').'-'.$this->safeSlug($lesson['page_slug'] ?? '');
            $parameters = [
                'theoryCategorySlug' => (string) ($lesson['category_slug_path'] ?? $lesson['category_slug'] ?? ''),
                'theoryPageSlug' => (string) ($lesson['page_slug'] ?? ''),
                'lessonSlug' => $lessonSlug,
                'lessonOrder' => (int) ($lesson['lesson_order'] ?? 1),
                '--title' => (string) ($lesson['title'] ?? ''),
                '--topic' => (string) ($lesson['title'] ?? ''),
                '--seeder' => $seeder,
                '--course' => TheoryCourseManifestService::COURSE_SLUG,
                '--level' => 'A2',
                '--items' => 24,
            ];

            if ((string) ($lesson['previous_lesson_slug'] ?? '') !== '') {
                $parameters['--previous'] = (string) $lesson['previous_lesson_slug'];
            }

            if ((string) ($lesson['next_lesson_slug'] ?? '') !== '') {
                $parameters['--next'] = (string) $lesson['next_lesson_slug'];
            }

            return $this->planPayload(
                $lesson,
                $type,
                $seeder,
                'polyglot:generate-v3-prompt',
                $this->withSkeletonOptions($parameters, $force),
                $this->polyglotCli($lesson, $lessonSlug, $seeder, $force),
            );
        }

        $seeder = $this->standardSeederClass($lesson);
        $parameters = [
            '--source-type' => 'theory_page',
            '--theory-page-id' => (int) ($lesson['page_id'] ?? 0),
            '--target-namespace' => 'AI/TheoryCourse',
            '--level' => ['A1', 'A2', 'B1', 'B2'],
            '--questions-per-level' => 5,
            '--generation-mode' => 'split',
        ];

        return $this->planPayload(
            $lesson,
            $type,
            $seeder,
            'v3:generate-prompt',
            $this->withSkeletonOptions($parameters, $force),
            $this->standardCli($lesson, $force),
        );
    }

    /**
     * @param  array<string, mixed>  $parameters
     * @return array<string, mixed>
     */
    private function withSkeletonOptions(array $parameters, bool $force): array
    {
        if ((bool) $this->option('write-skeleton')) {
            $parameters['--write-skeleton'] = true;
        }

        if ($force) {
            $parameters['--force'] = true;
        }

        return $parameters;
    }

    /**
     * @return array<string, mixed>
     */
    private function planPayload(
        array $lesson,
        string $type,
        string $seederClass,
        string $artisanCommand,
        array $artisanParameters,
        string $cliCommand
    ): array {
        return [
            'generated_at' => now()->toIso8601String(),
            'course_slug' => TheoryCourseManifestService::COURSE_SLUG,
            'type' => $type,
            'category_slug_path' => $lesson['category_slug_path'] ?? '',
            'page_slug' => $lesson['page_slug'] ?? '',
            'page_id' => $lesson['page_id'] ?? null,
            'lesson_slug' => $lesson['lesson_slug'] ?? '',
            'lesson_title' => $lesson['title'] ?? '',
            'theory_url' => $lesson['theory_url'] ?? '',
            'lesson_url' => $lesson['lesson_url'] ?? '',
            'suggested_seeder_class' => $seederClass,
            'generator_artisan_command' => $artisanCommand,
            'generator_artisan_parameters' => $artisanParameters,
            'generator_cli_command' => $cliCommand,
            'notes' => [
                'This command scopes generation to one theory page only.',
                'It does not mass-generate missing tests for the theory course tree.',
                'Review and fill generated question content before seeding.',
            ],
        ];
    }

    private function writePlan(array $plan, array $lesson, string $type): string
    {
        $directory = storage_path('app/theory-course/missing-seeders');
        File::ensureDirectoryExists($directory);

        $path = sprintf(
            '%s/%s-%s-%s.json',
            $directory,
            $type,
            str_replace('/', '-', $this->normalizeCategoryPath((string) ($lesson['category_slug_path'] ?? 'theory'))),
            $this->safeSlug((string) ($lesson['page_slug'] ?? 'page'))
        );

        File::put($path, json_encode($plan, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        return $path;
    }

    private function standardSeederClass(array $lesson): string
    {
        return Str::studly((string) ($lesson['page_slug'] ?? 'TheoryPage')).'TheoryCourseV3Seeder';
    }

    private function polyglotSeederClass(array $lesson): string
    {
        return 'Polyglot'.Str::studly((string) ($lesson['page_slug'] ?? 'TheoryPage')).'TheoryCourseLessonSeeder';
    }

    private function standardCli(array $lesson, bool $force): string
    {
        return sprintf(
            'php artisan v3:generate-prompt --source-type=theory_page --theory-page-id=%d --target-namespace=AI/TheoryCourse --level=A1 --level=A2 --level=B1 --level=B2 --questions-per-level=5 --generation-mode=split --write-skeleton%s',
            (int) ($lesson['page_id'] ?? 0),
            $force ? ' --force' : ''
        );
    }

    private function polyglotCli(array $lesson, string $lessonSlug, string $seeder, bool $force): string
    {
        return sprintf(
            'php artisan polyglot:generate-v3-prompt "%s" "%s" "%s" %d --title="%s" --topic="%s" --seeder=%s --course=%s --level=A2 --items=24 --write-skeleton%s',
            $lesson['category_slug_path'] ?? '',
            $lesson['page_slug'] ?? '',
            $lessonSlug,
            (int) ($lesson['lesson_order'] ?? 1),
            str_replace('"', '\"', (string) ($lesson['title'] ?? '')),
            str_replace('"', '\"', (string) ($lesson['title'] ?? '')),
            $seeder,
            TheoryCourseManifestService::COURSE_SLUG,
            $force ? ' --force' : ''
        );
    }

    private function normalizeCategoryPath(string $value): string
    {
        return implode('/', array_values(array_filter(
            explode('/', str_replace('\\', '/', Str::lower(trim($value, " \t\n\r\0\x0B/")))),
            'strlen'
        )));
    }

    private function safeSlug(string $value): string
    {
        return Str::slug($value) ?: 'theory-page';
    }

    private function relativePath(string $path): string
    {
        $normalizedPath = str_replace('\\', '/', $path);
        $normalizedRoot = rtrim(str_replace('\\', '/', base_path()), '/');

        return ltrim((string) Str::after($normalizedPath, $normalizedRoot), '/');
    }
}
