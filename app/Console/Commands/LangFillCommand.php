<?php

namespace App\Console\Commands;

use App\Services\Translate\TranslationProviderFactory;
use App\Services\Translate\TranslationProviderInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class LangFillCommand extends Command
{
    protected $signature = 'lang:fill 
                            {locale : Target locale code (e.g., pl, uk)}
                            {--provider=openai : Translation provider (openai or gemini)}
                            {--batch-size=50 : Number of strings to translate in each batch}
                            {--source=en : Source language directory}
                            {--force : Overwrite existing translations}
                            {--only-missing : Only translate missing keys (default behavior)}
                            {--dry-run : Run without saving changes}
                            {--path= : Custom source path (default: resources/lang/{source})}
                            {--files= : Comma-separated list of files to process (e.g., public.php,words_test.php)}
                            {--exclude= : Comma-separated list of files to exclude}';

    protected $description = 'Fill translations in Laravel lang files from English source using AI providers';

    private TranslationProviderInterface $provider;
    private string $sourceLocale;
    private string $targetLocale;
    private int $batchSize;
    private bool $force;
    private bool $dryRun;
    private string $sourcePath;
    private string $targetPath;
    private string $cachePath;

    // Statistics
    private int $filesProcessed = 0;
    private int $stringsTranslated = 0;
    private int $stringsSkipped = 0;
    private int $stringsFailed = 0;
    private array $failedStrings = [];
    private array $validationErrors = [];

    private const PLACEHOLDER_PATTERNS = [
        // Laravel placeholders like :name, :attribute, :count
        '/:([a-zA-Z_][a-zA-Z0-9_]*)/',
        // Numbered placeholders like {0}, {1}
        '/\{(\d+)\}/',
        // Range placeholders like [2,*], [0,1]
        '/\[(\d+),(\d+|\*)\]/',
        // Printf placeholders like %s, %d, %f
        '/%[sdfb]/',
    ];

    // Delay between batches in microseconds (to avoid API rate limiting)
    private const OPENAI_BATCH_DELAY_MICROSECONDS = 2000000; // 2 seconds
    private const GEMINI_BATCH_DELAY_MICROSECONDS = 500000;  // 0.5 seconds

    public function handle(): int
    {
        $this->targetLocale = $this->argument('locale');
        $this->sourceLocale = $this->option('source');
        $this->batchSize = (int) $this->option('batch-size');
        $this->force = $this->option('force');
        $this->dryRun = $this->option('dry-run');

        // Validate provider
        $providerName = $this->option('provider');
        $allowedProviders = TranslationProviderFactory::getAllowedProviders();
        
        if (!in_array($providerName, $allowedProviders)) {
            $this->error("Invalid provider: {$providerName}");
            $this->info("Supported providers: " . implode(', ', $allowedProviders));
            return Command::FAILURE;
        }

        // Create provider
        try {
            $this->provider = TranslationProviderFactory::make($providerName);
            $this->info("Using translation provider: " . strtoupper($this->provider->getName()));
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());
            $this->newLine();
            $this->info("To fix this:");
            if ($providerName === 'openai') {
                $this->info("  CHAT_GPT_API_KEY=your-api-key-here (or OPENAI_API_KEY)");
                $this->info("  Get key from: https://platform.openai.com/api-keys");
            } else {
                $this->info("  GEMINI_API_KEY=your-api-key-here");
                $this->info("  Get key from: https://makersuite.google.com/app/apikey");
            }
            return Command::FAILURE;
        }

        // Set paths
        $this->sourcePath = $this->option('path') ?: resource_path("lang/{$this->sourceLocale}");
        $this->targetPath = resource_path("lang/{$this->targetLocale}");
        $this->cachePath = storage_path("app/lang-fill-cache/{$this->targetLocale}.json");

        // Validate source path exists
        if (!File::isDirectory($this->sourcePath)) {
            $this->error("Source directory not found: {$this->sourcePath}");
            return Command::FAILURE;
        }

        // Create target directory if not exists
        if (!File::isDirectory($this->targetPath)) {
            if (!$this->dryRun) {
                File::makeDirectory($this->targetPath, 0755, true);
            }
            $this->info("Created target directory: {$this->targetPath}");
        }

        // Create cache directory if not exists
        $cacheDir = dirname($this->cachePath);
        if (!File::isDirectory($cacheDir)) {
            File::makeDirectory($cacheDir, 0755, true);
        }

        // Load cache
        $cache = $this->loadCache();

        // Get files to process
        $files = $this->getFilesToProcess();

        if (empty($files)) {
            $this->warn("No files to process.");
            return Command::SUCCESS;
        }

        $this->info("Processing {$this->sourceLocale} → {$this->targetLocale}");
        $this->info("Source: {$this->sourcePath}");
        $this->info("Target: {$this->targetPath}");
        $this->info("Files to process: " . count($files));
        $this->newLine();

        // Process each file
        foreach ($files as $file) {
            $this->processFile($file, $cache);
        }

        // Also process JSON file if exists
        $sourceJsonFile = resource_path("lang/{$this->sourceLocale}.json");
        if (File::exists($sourceJsonFile)) {
            $this->processJsonFile($sourceJsonFile, $cache);
        }

        // Save cache
        if (!$this->dryRun) {
            $this->saveCache($cache);
        }

        // Display report
        $this->displayReport();

        return Command::SUCCESS;
    }

    private function getFilesToProcess(): array
    {
        $allFiles = File::files($this->sourcePath);
        $files = [];

        $includeFiles = $this->option('files') ? explode(',', $this->option('files')) : null;
        $excludeFiles = $this->option('exclude') ? explode(',', $this->option('exclude')) : [];

        foreach ($allFiles as $file) {
            $filename = $file->getFilename();
            
            // Skip non-PHP files
            if ($file->getExtension() !== 'php') {
                continue;
            }

            // Check include list
            if ($includeFiles !== null && !in_array($filename, $includeFiles)) {
                continue;
            }

            // Check exclude list
            if (in_array($filename, $excludeFiles)) {
                continue;
            }

            $files[] = $file;
        }

        return $files;
    }

    private function processFile(\SplFileInfo $file, array &$cache): void
    {
        $filename = $file->getFilename();
        $this->info("Processing: {$filename}");

        // Load source translations
        $sourcePath = $file->getPathname();
        $sourceData = include $sourcePath;

        if (!is_array($sourceData)) {
            $this->warn("  Skipped: Invalid PHP array format");
            return;
        }

        // Load target translations if exists
        $targetFilePath = $this->targetPath . '/' . $filename;
        $targetData = [];
        
        if (File::exists($targetFilePath)) {
            $targetData = include $targetFilePath;
            if (!is_array($targetData)) {
                $targetData = [];
            }
        }

        // Collect strings to translate
        $stringsToTranslate = $this->collectStringsToTranslate($sourceData, $targetData, '');
        
        if (empty($stringsToTranslate)) {
            $this->info("  No strings to translate");
            return;
        }

        $this->info("  Found " . count($stringsToTranslate) . " strings to translate");

        // Translate strings
        $translations = $this->translateStrings($stringsToTranslate, $cache);

        // Merge translations into target data
        $targetData = $this->mergeTranslations($sourceData, $targetData, $translations);

        // Save file
        if (!$this->dryRun) {
            $this->savePhpFile($targetFilePath, $targetData);
        }

        $this->filesProcessed++;
    }

    private function processJsonFile(string $sourceFile, array &$cache): void
    {
        $this->info("Processing: " . basename($sourceFile));

        $sourceData = json_decode(File::get($sourceFile), true);
        
        if (!is_array($sourceData)) {
            $this->warn("  Skipped: Invalid JSON format");
            return;
        }

        $targetFile = resource_path("lang/{$this->targetLocale}.json");
        $targetData = [];

        if (File::exists($targetFile)) {
            $targetData = json_decode(File::get($targetFile), true);
            if (!is_array($targetData)) {
                $targetData = [];
            }
        }

        // Collect strings to translate
        $stringsToTranslate = $this->collectStringsToTranslate($sourceData, $targetData, '');

        if (empty($stringsToTranslate)) {
            $this->info("  No strings to translate");
            return;
        }

        $this->info("  Found " . count($stringsToTranslate) . " strings to translate");

        // Translate strings
        $translations = $this->translateStrings($stringsToTranslate, $cache);

        // Merge translations into target data
        $targetData = $this->mergeTranslations($sourceData, $targetData, $translations);

        // Save file
        if (!$this->dryRun) {
            File::put(
                $targetFile,
                json_encode($targetData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );
        }

        $this->filesProcessed++;
    }

    private function collectStringsToTranslate(array $source, array $target, string $prefix): array
    {
        $strings = [];

        foreach ($source as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : (string)$key;

            if (is_array($value)) {
                // Recursively collect nested strings
                $nestedTarget = $target[$key] ?? [];
                if (!is_array($nestedTarget)) {
                    $nestedTarget = [];
                }
                $strings = array_merge($strings, $this->collectStringsToTranslate($value, $nestedTarget, $fullKey));
            } elseif (is_string($value)) {
                // Check if translation exists
                $existingTranslation = $this->getNestedValue($target, $key);
                
                if ($this->force || $existingTranslation === null) {
                    // Skip URLs and technical identifiers
                    if ($this->shouldTranslate($value)) {
                        $strings[$fullKey] = $value;
                    } else {
                        $this->stringsSkipped++;
                    }
                } else {
                    $this->stringsSkipped++;
                }
            }
        }

        return $strings;
    }

    private function getNestedValue(array $array, $key)
    {
        return $array[$key] ?? null;
    }

    private function shouldTranslate(string $value): bool
    {
        // Skip empty strings
        if (trim($value) === '') {
            return false;
        }

        // Skip URLs
        if (preg_match('/^https?:\/\//', $value)) {
            return false;
        }

        return true;
    }

    private function translateStrings(array $strings, array &$cache): array
    {
        $translations = [];
        $uncachedStrings = [];

        // Check cache first
        foreach ($strings as $key => $value) {
            $cacheKey = $this->getCacheKey($value);
            
            if (isset($cache[$cacheKey])) {
                $translations[$key] = $cache[$cacheKey];
                $this->stringsTranslated++;
            } else {
                $uncachedStrings[$key] = $value;
            }
        }

        if (empty($uncachedStrings)) {
            return $translations;
        }

        // Translate in batches
        $batches = array_chunk($uncachedStrings, $this->batchSize, true);
        $bar = $this->output->createProgressBar(count($uncachedStrings));
        $bar->start();

        foreach ($batches as $batchIndex => $batch) {
            try {
                $batchKeys = array_keys($batch);
                $batchValues = array_values($batch);

                $batchTranslations = $this->provider->translateBatch(
                    $batchValues,
                    $this->sourceLocale,
                    $this->targetLocale
                );

                foreach ($batchKeys as $index => $key) {
                    $originalValue = $batch[$key];
                    $translatedValue = $batchTranslations[$index] ?? null;

                    if ($translatedValue !== null) {
                        // Validate translation
                        $validation = $this->validateTranslation($originalValue, $translatedValue);
                        
                        if ($validation['valid']) {
                            $translations[$key] = $translatedValue;
                            $cache[$this->getCacheKey($originalValue)] = $translatedValue;
                            $this->stringsTranslated++;
                        } else {
                            // Use original or retry
                            $translations[$key] = $originalValue;
                            $this->stringsFailed++;
                            $this->validationErrors[] = [
                                'key' => $key,
                                'original' => $originalValue,
                                'translated' => $translatedValue,
                                'error' => $validation['error'],
                            ];
                        }
                    } else {
                        $translations[$key] = $originalValue;
                        $this->stringsFailed++;
                        $this->failedStrings[] = $key;
                    }

                    $bar->advance();
                }
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("  Batch {$batchIndex} failed: " . $e->getMessage());
                
                // Mark batch as failed, use original values
                foreach ($batch as $key => $value) {
                    $translations[$key] = $value;
                    $this->stringsFailed++;
                    $this->failedStrings[] = $key;
                    $bar->advance();
                }
            }

            // Delay between batches to avoid rate limiting
            if ($batchIndex < count($batches) - 1) {
                $delay = $this->provider->getName() === 'openai' 
                    ? self::OPENAI_BATCH_DELAY_MICROSECONDS 
                    : self::GEMINI_BATCH_DELAY_MICROSECONDS;
                usleep($delay);
            }
        }

        $bar->finish();
        $this->newLine();

        return $translations;
    }

    private function validateTranslation(string $original, string $translated): array
    {
        // Extract placeholders from original
        $originalPlaceholders = $this->extractPlaceholders($original);
        $translatedPlaceholders = $this->extractPlaceholders($translated);

        // Check all placeholders are preserved
        foreach ($originalPlaceholders as $placeholder) {
            if (!in_array($placeholder, $translatedPlaceholders)) {
                return [
                    'valid' => false,
                    'error' => "Missing placeholder: {$placeholder}",
                ];
            }
        }

        // Check for pluralization
        if (str_contains($original, '|')) {
            $originalParts = explode('|', $original);
            $translatedParts = explode('|', $translated);
            
            if (count($originalParts) !== count($translatedParts)) {
                return [
                    'valid' => false,
                    'error' => "Pluralization segment count mismatch: expected " . count($originalParts) . ", got " . count($translatedParts),
                ];
            }
        }

        return ['valid' => true, 'error' => null];
    }

    private function extractPlaceholders(string $text): array
    {
        $placeholders = [];

        foreach (self::PLACEHOLDER_PATTERNS as $pattern) {
            if (preg_match_all($pattern, $text, $matches)) {
                $placeholders = array_merge($placeholders, $matches[0]);
            }
        }

        return array_unique($placeholders);
    }

    private function mergeTranslations(array $source, array $target, array $translations): array
    {
        foreach ($source as $key => $value) {
            if (is_array($value)) {
                $nestedTarget = $target[$key] ?? [];
                if (!is_array($nestedTarget)) {
                    $nestedTarget = [];
                }
                $target[$key] = $this->mergeTranslations($value, $nestedTarget, $translations);
            } else {
                // Find translation by key path
                $fullKey = (string)$key;
                
                // Check if we have a direct translation
                if (isset($translations[$fullKey])) {
                    $target[$key] = $translations[$fullKey];
                } elseif (!isset($target[$key]) || $this->force) {
                    // Try to find in nested translations
                    foreach ($translations as $transKey => $transValue) {
                        $parts = explode('.', $transKey);
                        if (end($parts) === (string)$key) {
                            $target[$key] = $transValue;
                            break;
                        }
                    }
                }
            }
        }

        return $target;
    }

    private function savePhpFile(string $path, array $data): void
    {
        $content = "<?php\n\nreturn " . $this->arrayToPhpString($data, 0) . ";\n";
        File::put($path, $content);
    }

    private function arrayToPhpString(array $array, int $indent): string
    {
        $indentStr = str_repeat('    ', $indent);
        $nextIndentStr = str_repeat('    ', $indent + 1);
        
        $lines = ["["];
        
        foreach ($array as $key => $value) {
            $keyStr = is_int($key) ? $key : "'" . addslashes($key) . "'";
            
            if (is_array($value)) {
                $valueStr = $this->arrayToPhpString($value, $indent + 1);
                $lines[] = "{$nextIndentStr}{$keyStr} => {$valueStr},";
            } elseif (is_string($value)) {
                $valueStr = "'" . addslashes($value) . "'";
                $lines[] = "{$nextIndentStr}{$keyStr} => {$valueStr},";
            } elseif (is_bool($value)) {
                $valueStr = $value ? 'true' : 'false';
                $lines[] = "{$nextIndentStr}{$keyStr} => {$valueStr},";
            } elseif (is_null($value)) {
                $lines[] = "{$nextIndentStr}{$keyStr} => null,";
            } else {
                $lines[] = "{$nextIndentStr}{$keyStr} => {$value},";
            }
        }
        
        $lines[] = "{$indentStr}]";
        
        return implode("\n", $lines);
    }

    private function getCacheKey(string $text): string
    {
        return md5($text);
    }

    private function loadCache(): array
    {
        if (File::exists($this->cachePath)) {
            $content = File::get($this->cachePath);
            $cache = json_decode($content, true);
            
            if (is_array($cache)) {
                return $cache;
            }
        }

        return [];
    }

    private function saveCache(array $cache): void
    {
        File::put(
            $this->cachePath,
            json_encode($cache, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }

    private function displayReport(): void
    {
        $this->newLine();
        $this->info("=== Translation Report ({$this->sourceLocale} → {$this->targetLocale}) ===");
        $this->info("Files processed: {$this->filesProcessed}");
        $this->info("Strings translated: {$this->stringsTranslated}");
        $this->info("Strings skipped (already exist): {$this->stringsSkipped}");
        $this->info("Strings failed: {$this->stringsFailed}");

        if ($this->dryRun) {
            $this->warn("DRY RUN - No changes were saved");
        }

        if (!empty($this->failedStrings)) {
            $this->newLine();
            $this->warn("Failed strings:");
            foreach (array_slice($this->failedStrings, 0, 10) as $key) {
                $this->line("  - {$key}");
            }
            if (count($this->failedStrings) > 10) {
                $this->line("  ... and " . (count($this->failedStrings) - 10) . " more");
            }
        }

        if (!empty($this->validationErrors)) {
            $this->newLine();
            $this->warn("Validation errors:");
            foreach (array_slice($this->validationErrors, 0, 5) as $error) {
                $this->line("  - {$error['key']}: {$error['error']}");
            }
            if (count($this->validationErrors) > 5) {
                $this->line("  ... and " . (count($this->validationErrors) - 5) . " more");
            }
        }

        // Save detailed report
        if (!$this->dryRun && (!empty($this->failedStrings) || !empty($this->validationErrors))) {
            $reportPath = storage_path("logs/lang_fill_{$this->targetLocale}_" . now()->format('Y-m-d_H-i-s') . '.txt');
            $report = "Language Fill Report ({$this->sourceLocale} → {$this->targetLocale})\n";
            $report .= "Generated: " . now()->toDateTimeString() . "\n";
            $report .= "Provider: " . $this->provider->getName() . "\n\n";
            $report .= "Files processed: {$this->filesProcessed}\n";
            $report .= "Strings translated: {$this->stringsTranslated}\n";
            $report .= "Strings skipped: {$this->stringsSkipped}\n";
            $report .= "Strings failed: {$this->stringsFailed}\n\n";

            if (!empty($this->failedStrings)) {
                $report .= "Failed strings:\n";
                foreach ($this->failedStrings as $key) {
                    $report .= "  - {$key}\n";
                }
                $report .= "\n";
            }

            if (!empty($this->validationErrors)) {
                $report .= "Validation errors:\n";
                foreach ($this->validationErrors as $error) {
                    $report .= "  - {$error['key']}: {$error['error']}\n";
                    $report .= "    Original: {$error['original']}\n";
                    $report .= "    Translated: {$error['translated']}\n";
                }
            }

            File::put($reportPath, $report);
            $this->info("Detailed report saved to: {$reportPath}");
        }
    }
}
