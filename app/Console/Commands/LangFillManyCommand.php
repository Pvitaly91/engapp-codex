<?php

namespace App\Console\Commands;

use App\Services\Translate\TranslationProviderFactory;
use Illuminate\Console\Command;

class LangFillManyCommand extends Command
{
    protected $signature = 'lang:fill-many 
                            {--locales= : Comma-separated list of locale codes (e.g., pl,uk)}
                            {--provider=openai : Translation provider (openai or gemini)}
                            {--batch-size=50 : Number of strings to translate in each batch}
                            {--source=en : Source language directory}
                            {--force : Overwrite existing translations}
                            {--dry-run : Run without saving changes}
                            {--path= : Custom source path}
                            {--files= : Comma-separated list of files to process}
                            {--exclude= : Comma-separated list of files to exclude}';

    protected $description = 'Fill translations for multiple locales from English source using AI providers';

    public function handle(): int
    {
        $localesOption = $this->option('locales');
        
        if (empty($localesOption)) {
            $this->error("Please specify locales using --locales=pl,uk");
            return Command::FAILURE;
        }

        $locales = array_map('trim', explode(',', $localesOption));
        
        if (empty($locales)) {
            $this->error("No valid locales provided");
            return Command::FAILURE;
        }

        // Validate provider
        $providerName = $this->option('provider');
        $allowedProviders = TranslationProviderFactory::getAllowedProviders();
        
        if (!in_array($providerName, $allowedProviders)) {
            $this->error("Invalid provider: {$providerName}");
            $this->info("Supported providers: " . implode(', ', $allowedProviders));
            return Command::FAILURE;
        }

        $this->info("Processing locales: " . implode(', ', $locales));
        $this->info("Using provider: {$providerName}");
        $this->newLine();

        $results = [];
        
        foreach ($locales as $locale) {
            $this->info("=== Processing locale: {$locale} ===");
            $this->newLine();

            // Build command options
            $options = [
                'locale' => $locale,
                '--provider' => $providerName,
                '--batch-size' => $this->option('batch-size'),
                '--source' => $this->option('source'),
            ];

            if ($this->option('force')) {
                $options['--force'] = true;
            }

            if ($this->option('dry-run')) {
                $options['--dry-run'] = true;
            }

            if ($this->option('path')) {
                $options['--path'] = $this->option('path');
            }

            if ($this->option('files')) {
                $options['--files'] = $this->option('files');
            }

            if ($this->option('exclude')) {
                $options['--exclude'] = $this->option('exclude');
            }

            // Call the single locale command
            $exitCode = $this->call('lang:fill', $options);
            
            $results[$locale] = $exitCode === Command::SUCCESS;
            
            $this->newLine();
        }

        // Display summary
        $this->info("=== Summary ===");
        foreach ($results as $locale => $success) {
            $status = $success ? '✓' : '✗';
            $this->line("{$status} {$locale}");
        }

        $failedCount = count(array_filter($results, fn($r) => !$r));
        
        if ($failedCount > 0) {
            $this->warn("{$failedCount} locale(s) failed");
            return Command::FAILURE;
        }

        $this->info("All locales processed successfully!");
        return Command::SUCCESS;
    }
}
