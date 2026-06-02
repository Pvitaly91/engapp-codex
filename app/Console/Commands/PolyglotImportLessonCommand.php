<?php

namespace App\Console\Commands;

use App\Exceptions\PolyglotLessonValidationException;
use App\Services\PolyglotLessonImportService;
use Illuminate\Console\Command;

class PolyglotImportLessonCommand extends Command
{
    protected $signature = 'polyglot:import-lesson {path : Path to the Polyglot lesson JSON file}
        {--preview : Validate and print summary without writing to the database}
        {--force : Overwrite an existing incompatible saved_grammar_test with the same slug}';

    protected $description = 'Validate and import a Polyglot lesson JSON definition';

    public function __construct(
        protected PolyglotLessonImportService $importService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $path = (string) $this->argument('path');

        try {
            $summary = $this->option('preview')
                ? $this->importService->previewFromFile($path)
                : $this->importService->importFromFile($path, (bool) $this->option('force'), static::class);
        } catch (PolyglotLessonValidationException $exception) {
            $this->error('Polyglot lesson validation failed.');

            foreach ($exception->errors() as $error) {
                $uuid = trim((string) ($error['uuid'] ?? ''));
                $field = trim((string) ($error['field'] ?? ''));
                $message = trim((string) ($error['message'] ?? 'Invalid value.'));

                $label = $uuid !== '' ? "[{$uuid}] {$field}" : $field;
                $this->line("- {$label}: {$message}");
            }

            return self::FAILURE;
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if ($this->option('preview')) {
            $this->info('Preview only. No database changes were written.');
        } else {
            $this->info('Polyglot lesson imported successfully.');
        }

        $this->line('Slug: '.$summary['slug']);
        $this->line('Lesson order: '.$summary['lesson_order']);
        $this->line('Items: '.$summary['items_count']);
        $this->line('First 3 uuids: '.implode(', ', $summary['first_uuids']));
        $this->line('Warnings: '.$this->formatWarnings($summary['warnings']));

        return self::SUCCESS;
    }

    private function formatWarnings(array $warnings): string
    {
        if ($warnings === []) {
            return 'none';
        }

        return implode(' | ', array_map(function (array $warning) {
            $uuid = trim((string) ($warning['uuid'] ?? ''));
            $field = trim((string) ($warning['field'] ?? 'warning'));
            $message = trim((string) ($warning['message'] ?? ''));

            return sprintf('[%s] %s: %s', $uuid !== '' ? $uuid : 'root', $field, $message);
        }, $warnings));
    }
}
