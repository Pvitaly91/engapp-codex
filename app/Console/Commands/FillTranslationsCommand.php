<?php

namespace App\Console\Commands;

use App\Models\Translate;
use App\Models\Word;
use App\Services\TranslationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FillTranslationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'words:fill-export 
                            {lang : Language code (uk, pl, en, etc.)}
                            {--provider=auto : Translation provider (auto, gemini, openai)}
                            {--batch-size=50 : Number of words to translate in each batch}
                            {--no-db : Skip database synchronization}
                            {--dry-run : Run without saving changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill translations in words export file for specified language';

    /**
     * Minimum viable JSON file size in bytes.
     * 
     * This is a safety threshold to catch obviously corrupted or empty files.
     * 
     * Example minimum structure (around 100+ bytes):
     * {
     *   "exported_at": "2025-01-01T00:00:00+00:00",
     *   "lang": "pl",
     *   "counts": {"total_words": 0, "with_translation": 0, "without_translation": 0},
     *   "with_translation": [],
     *   "without_translation": []
     * }
     * 
     * We use 50 bytes as a conservative threshold since the structural validation
     * (checking for required fields) provides the primary validation.
     * A truly empty or corrupted file will be much smaller than this.
     */
    private const MIN_JSON_SIZE = 50;

    private TranslationService $translationService;
    private array $failedWords = [];
    private array $suspiciousWords = [];
    private int $translationsAdded = 0;
    private int $translationsRemaining = 0;
    private string $lang;
    private int $delayBetweenBatches = 500000; // microseconds (0.5 seconds default)

    private const ALLOWED_LANGS = ['uk', 'pl', 'en', 'de', 'fr', 'es', 'it'];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->lang = $this->argument('lang');
        
        // Validate language
        if (!in_array($this->lang, self::ALLOWED_LANGS)) {
            $this->error("Unsupported language: {$this->lang}");
            $this->info("Supported languages: " . implode(', ', self::ALLOWED_LANGS));
            return Command::FAILURE;
        }

        // Get provider option
        $provider = $this->option('provider');
        if (!in_array($provider, ['auto', 'gemini', 'openai'])) {
            $this->error("Invalid provider: {$provider}");
            $this->info("Supported providers: auto, gemini, openai");
            return Command::FAILURE;
        }

        // Create translation service for target language
        try {
            $this->translationService = new TranslationService($this->lang, $provider);
            
            // Show which provider is being used
            $actualProvider = $this->translationService->getProvider();
            $this->info("Using translation provider: " . strtoupper($actualProvider));
            $this->newLine();
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());
            $this->newLine();
            $this->info("To fix this:");
            $this->info("Option 1 - Use Gemini:");
            $this->info("  GEMINI_API_KEY=your-api-key-here");
            $this->info("  Get key from: https://makersuite.google.com/app/apikey");
            $this->newLine();
            $this->info("Option 2 - Use OpenAI/ChatGPT:");
            $this->info("  CHAT_GPT_API_KEY=your-api-key-here");
            $this->info("  Get key from: https://platform.openai.com/api-keys");
            return Command::FAILURE;
        }
        
        $filePath = public_path("exports/words/words_{$this->lang}.json");
        
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            $this->info("Please create the export file first using the admin panel.");
            return Command::FAILURE;
        }

        $this->info("Reading {$filePath}...");
        $jsonContent = file_get_contents($filePath);
        $data = json_decode($jsonContent, true);

        if (!$data) {
            $this->error("Failed to parse JSON file");
            return Command::FAILURE;
        }

        // Collect all words with null translations
        $wordsToTranslate = $this->collectWordsToTranslate($data);
        
        if (empty($wordsToTranslate)) {
            $this->info("No words with null translations found!");
            return Command::SUCCESS;
        }

        $this->info("Found " . count($wordsToTranslate) . " words to translate to {$this->lang}");
        
        // Translate words in batches
        $batchSize = (int) $this->option('batch-size');
        
        // Warn about OpenAI rate limits
        if ($this->translationService->getProvider() === 'openai') {
            if ($batchSize > 20) {
                $this->newLine();
                $this->warn("OpenAI has stricter rate limits than Gemini.");
                $this->warn("For better reliability, consider using a smaller batch size:");
                $this->warn("  php artisan words:fill-export {$this->lang} --batch-size=10");
                $this->newLine();
                
                if (!$this->confirm("Continue with batch size {$batchSize}?", true)) {
                    return Command::FAILURE;
                }
            }
            
            // Increase delay between batches for OpenAI
            $this->delayBetweenBatches = 2000000; // 2 seconds
        } else {
            $this->delayBetweenBatches = 500000; // 0.5 seconds for Gemini
        }
        
        $batches = array_chunk($wordsToTranslate, $batchSize, true);
        
        $this->info("Processing in " . count($batches) . " batches of {$batchSize} words...");
        
        $bar = $this->output->createProgressBar(count($wordsToTranslate));
        $bar->start();

        $translations = [];
        
        foreach ($batches as $batchIndex => $batch) {
            $words = array_column($batch, 'word');
            
            try {
                // Translate batch
                $batchTranslations = $this->translationService->translateBatch($words);
                
                // Validate each translation
                foreach ($batch as $key => $wordData) {
                    $word = $wordData['word'];
                    $translation = $batchTranslations[$word] ?? null;
                    
                    if ($translation !== null) {
                        $validation = $this->translationService->validateTranslation($word, $translation);
                        
                        if ($validation['valid']) {
                            $translations[$key] = $validation['translation'];
                        } else {
                            $this->suspiciousWords[] = $word;
                            $translations[$key] = null;
                        }
                    } else {
                        $this->failedWords[] = $word;
                        $translations[$key] = null;
                    }
                    
                    $bar->advance();
                }
            } catch (\Exception $e) {
                $bar->clear();
                $this->newLine();
                $this->error("Translation batch failed: " . $e->getMessage());
                $this->newLine();
                $this->error("Failed at batch " . ($batchIndex + 1) . " of " . count($batches));
                $this->info("Words in this batch: " . implode(', ', array_slice($words, 0, 5)) . (count($words) > 5 ? '...' : ''));
                $this->newLine();
                
                // Mark all words in this batch as failed
                foreach ($batch as $key => $wordData) {
                    $this->failedWords[] = $wordData['word'];
                    $translations[$key] = null;
                    $bar->advance();
                }
                
                // Ask if user wants to continue
                if ($batchIndex < count($batches) - 1) {
                    if (!$this->confirm('Continue with next batch?', false)) {
                        $bar->finish();
                        $this->newLine();
                        $this->warn("Translation stopped by user");
                        break;
                    }
                    $bar->display();
                }
            }
            
            // Delay between batches to avoid rate limiting
            if ($batchIndex < count($batches) - 1) {
                usleep($this->delayBetweenBatches);
            }
        }
        
        $bar->finish();
        $this->newLine();

        // Update JSON data
        $data = $this->updateJsonData($data, $translations);
        
        // Save file
        if (!$this->option('dry-run')) {
            $this->info("Saving updated JSON...");
            
            // Create backup before saving
            $backupPath = $this->generateBackupFilename($filePath);
            if (!copy($filePath, $backupPath)) {
                $this->error("CRITICAL: Could not create backup file!");
                $this->error("Refusing to proceed without backup for data safety.");
                return Command::FAILURE;
            }
            $this->info("Backup created: " . basename($backupPath));
            
            // Encode JSON
            $jsonString = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
            if ($jsonString === false) {
                $this->error("Failed to encode JSON: " . json_last_error_msg());
                $this->info("Original file preserved as backup: " . basename($backupPath));
                return Command::FAILURE;
            }
            
            // Validate JSON is not empty and has proper structure
            if (strlen($jsonString) < self::MIN_JSON_SIZE) {
                $this->error("Refusing to save empty/invalid JSON!");
                $this->error("JSON string length: " . strlen($jsonString));
                $this->info("Original file preserved as backup: " . basename($backupPath));
                return Command::FAILURE;
            }
            
            // Verify structure by decoding
            $decoded = json_decode($jsonString, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error("JSON re-decode validation failed: " . json_last_error_msg());
                $this->info("Original file preserved as backup: " . basename($backupPath));
                return Command::FAILURE;
            }
            
            if (!isset($decoded['counts']) || !isset($decoded['with_translation']) || !isset($decoded['without_translation'])) {
                $this->error("JSON structure validation failed - missing required fields!");
                $this->error("Available fields: " . implode(', ', array_keys($decoded ?? [])));
                $this->info("Original file preserved as backup: " . basename($backupPath));
                return Command::FAILURE;
            }
            
            $this->info("JSON encoded successfully (" . number_format(strlen($jsonString)) . " bytes)");
            
            // Write to file
            $result = file_put_contents($filePath, $jsonString);
            
            if ($result === false) {
                $this->error("Failed to save file: {$filePath}");
                $this->error("Restore backup with: cp {$backupPath} {$filePath}");
                return Command::FAILURE;
            }
            
            $this->info("File saved successfully! ({$result} bytes written)");
            
            // Verify file was written correctly by checking it can be read back
            $verifyContent = file_get_contents($filePath);
            if ($verifyContent === false || $verifyContent === '') {
                $errorMsg = $verifyContent === false ? "could not read file back" : "file is empty";
                $this->error("Warning: File verification failed ({$errorMsg})");
                return $this->restoreFromBackup($backupPath, $filePath);
            }
            
            if (strlen($verifyContent) < self::MIN_JSON_SIZE) {
                $this->error("Warning: File verification failed (size: " . strlen($verifyContent) . " bytes, minimum: " . self::MIN_JSON_SIZE . ")");
                return $this->restoreFromBackup($backupPath, $filePath);
            }
            
            // Sync with database
            if (!$this->option('no-db')) {
                $this->syncWithDatabase($translations);
            }
        } else {
            $this->warn("Dry run mode - changes not saved");
        }

        // Display report
        $this->displayReport();

        return Command::SUCCESS;
    }

    /**
     * Collect all words that need translation
     */
    private function collectWordsToTranslate(array $data): array
    {
        $wordsToTranslate = [];
        
        // Check both with_translation and without_translation arrays
        foreach (['with_translation', 'without_translation'] as $section) {
            if (!isset($data[$section])) {
                continue;
            }
            
            foreach ($data[$section] as $index => $wordData) {
                if (!isset($wordData['translation']) || $wordData['translation'] === null) {
                    $wordsToTranslate["{$section}_{$index}"] = $wordData;
                }
            }
        }
        
        return $wordsToTranslate;
    }

    /**
     * Update JSON data with translations
     */
    private function updateJsonData(array $data, array $translations): array
    {
        $withTranslation = $data['with_translation'] ?? [];
        $withoutTranslation = $data['without_translation'] ?? [];
        
        // Create new arrays
        $newWithTranslation = [];
        $newWithoutTranslation = [];
        
        // Process with_translation section
        foreach ($withTranslation as $index => $wordData) {
            $key = "with_translation_{$index}";
            
            if (isset($translations[$key])) {
                $wordData['translation'] = $translations[$key];
                
                if ($translations[$key] !== null) {
                    $newWithTranslation[] = $wordData;
                    $this->translationsAdded++;
                } else {
                    $newWithoutTranslation[] = $wordData;
                    $this->translationsRemaining++;
                }
            } else {
                // Word already had translation
                $newWithTranslation[] = $wordData;
            }
        }
        
        // Process without_translation section
        foreach ($withoutTranslation as $index => $wordData) {
            $key = "without_translation_{$index}";
            
            if (isset($translations[$key])) {
                $wordData['translation'] = $translations[$key];
                
                if ($translations[$key] !== null) {
                    $newWithTranslation[] = $wordData;
                    $this->translationsAdded++;
                } else {
                    $newWithoutTranslation[] = $wordData;
                    $this->translationsRemaining++;
                }
            } else {
                // Keep in without_translation
                $newWithoutTranslation[] = $wordData;
                $this->translationsRemaining++;
            }
        }
        
        // Update counts
        $totalWords = count($newWithTranslation) + count($newWithoutTranslation);
        
        // Validate we haven't lost data
        $originalTotal = count($withTranslation) + count($withoutTranslation);
        if ($totalWords !== $originalTotal) {
            throw new \RuntimeException(
                "Data integrity error: Original had {$originalTotal} words, but result has {$totalWords} words!"
            );
        }
        
        return [
            'exported_at' => now()->toIso8601String(),
            'lang' => $this->lang,
            'counts' => [
                'total_words' => $totalWords,
                'with_translation' => count($newWithTranslation),
                'without_translation' => count($newWithoutTranslation),
            ],
            'with_translation' => $newWithTranslation,
            'without_translation' => $newWithoutTranslation,
        ];
    }

    /**
     * Sync translations with database
     */
    private function syncWithDatabase(array $translations): void
    {
        $this->info("Synchronizing with database...");
        
        DB::beginTransaction();
        
        try {
            $synced = 0;
            $skipped = 0;
            
            // Read the file to get word data with IDs
            $filePath = public_path("exports/words/words_{$this->lang}.json");
            $data = json_decode(file_get_contents($filePath), true);
            
            // Build word map
            $wordMap = [];
            foreach (array_merge($data['with_translation'] ?? [], $data['without_translation'] ?? []) as $wordData) {
                if (isset($wordData['id']) && isset($wordData['word']) && isset($wordData['translation']) && $wordData['translation'] !== null) {
                    $wordMap[$wordData['id']] = [
                        'word' => $wordData['word'],
                        'translation' => $wordData['translation'],
                    ];
                }
            }
            
            foreach ($wordMap as $wordId => $data) {
                // Check if word exists in database
                $word = Word::find($wordId);
                if (!$word) {
                    continue;
                }
                
                // Check if translation already exists
                $existingTranslate = Translate::where('word_id', $wordId)
                    ->where('lang', $this->lang)
                    ->first();
                
                if ($existingTranslate) {
                    // Skip if existing translation is not empty
                    if (trim($existingTranslate->translation ?? '') !== '') {
                        $skipped++;
                        continue;
                    }
                    
                    // Update empty translation
                    $existingTranslate->update(['translation' => $data['translation']]);
                    $synced++;
                } else {
                    // Create new translation
                    Translate::create([
                        'word_id' => $wordId,
                        'lang' => $this->lang,
                        'translation' => $data['translation'],
                    ]);
                    $synced++;
                }
            }
            
            DB::commit();
            $this->info("Database sync complete: {$synced} synced, {$skipped} skipped");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Database sync failed: " . $e->getMessage());
        }
    }

    /**
     * Display final report
     */
    private function displayReport(): void
    {
        $this->newLine();
        $this->info("=== Translation Report ({$this->lang}) ===");
        $this->info("Translations added: {$this->translationsAdded}");
        $this->info("Translations remaining null: {$this->translationsRemaining}");
        
        if (!empty($this->failedWords)) {
            $this->newLine();
            $this->warn("Failed to translate (" . count($this->failedWords) . " words):");
            foreach (array_slice($this->failedWords, 0, 20) as $word) {
                $this->line("  - {$word}");
            }
            if (count($this->failedWords) > 20) {
                $this->line("  ... and " . (count($this->failedWords) - 20) . " more");
            }
        }
        
        if (!empty($this->suspiciousWords)) {
            $this->newLine();
            $this->warn("Suspicious translations (" . count($this->suspiciousWords) . " words):");
            foreach (array_slice($this->suspiciousWords, 0, 20) as $word) {
                $this->line("  - {$word}");
            }
            if (count($this->suspiciousWords) > 20) {
                $this->line("  ... and " . (count($this->suspiciousWords) - 20) . " more");
            }
        }
        
        // Save detailed report
        if (!$this->option('dry-run') && (!empty($this->failedWords) || !empty($this->suspiciousWords))) {
            $reportPath = storage_path("logs/{$this->lang}_translation_report_" . now()->format('Y-m-d_H-i-s') . '.txt');
            $report = "{$this->lang} Translation Report\n";
            $report .= "Generated: " . now()->toDateTimeString() . "\n\n";
            $report .= "Translations added: {$this->translationsAdded}\n";
            $report .= "Translations remaining null: {$this->translationsRemaining}\n\n";
            
            if (!empty($this->failedWords)) {
                $report .= "Failed words:\n";
                foreach ($this->failedWords as $word) {
                    $report .= "  - {$word}\n";
                }
                $report .= "\n";
            }
            
            if (!empty($this->suspiciousWords)) {
                $report .= "Suspicious words:\n";
                foreach ($this->suspiciousWords as $word) {
                    $report .= "  - {$word}\n";
                }
            }
            
            file_put_contents($reportPath, $report);
            $this->info("Detailed report saved to: {$reportPath}");
        }
    }

    /**
     * Generate a unique backup filename using microtime and random suffix
     * 
     * @param string $filePath Original file path
     * @return string Backup file path
     */
    private function generateBackupFilename(string $filePath): string
    {
        // Use microtime for microsecond precision + random suffix for extra uniqueness
        // Replace dots with underscores to avoid multiple file extensions
        $timestamp = str_replace('.', '_', microtime(true));
        $random = substr(md5(uniqid('', true)), 0, 8);
        return $filePath . '.backup_' . $timestamp . '_' . $random;
    }

    /**
     * Restore file from backup and return failure status
     * 
     * @param string $backupPath Backup file path
     * @param string $filePath Original file path
     * @return int Command::FAILURE status code
     */
    private function restoreFromBackup(string $backupPath, string $filePath): int
    {
        $this->error("Restoring from backup...");
        
        if (copy($backupPath, $filePath)) {
            $this->info("Backup restored successfully!");
        } else {
            $this->error("CRITICAL: Backup restore failed! Manually restore with:");
            $this->error("  cp {$backupPath} {$filePath}");
        }
        
        return Command::FAILURE;
    }
}
