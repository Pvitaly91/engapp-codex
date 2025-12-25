<?php

namespace App\Console\Commands;

use App\Models\Translate;
use App\Models\Word;
use App\Services\PolishTranslationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FillPolishTranslationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'words:fill-pl-export 
                            {--batch-size=50 : Number of words to translate in each batch}
                            {--no-db : Skip database synchronization}
                            {--dry-run : Run without saving changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill Polish translations in words_pl.json export file';

    private PolishTranslationService $translationService;
    private array $failedWords = [];
    private array $suspiciousWords = [];
    private int $translationsAdded = 0;
    private int $translationsRemaining = 0;

    /**
     * Execute the console command.
     */
    public function handle(PolishTranslationService $translationService): int
    {
        try {
            $this->translationService = $translationService;
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());
            $this->newLine();
            $this->warn("Note: This command is deprecated. Please use: php artisan words:fill-export pl");
            $this->newLine();
            $this->info("To fix this:");
            $this->info("1. Add your Gemini API key to .env file:");
            $this->info("   GEMINI_API_KEY=your-api-key-here");
            $this->info("2. Get API key from: https://makersuite.google.com/app/apikey");
            return Command::FAILURE;
        }
        
        $filePath = public_path('exports/words/words_pl.json');
        
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
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

        $this->info("Found " . count($wordsToTranslate) . " words to translate");
        
        // Translate words in batches
        $batchSize = (int) $this->option('batch-size');
        $batches = array_chunk($wordsToTranslate, $batchSize, true);
        
        $this->info("Processing in " . count($batches) . " batches of {$batchSize} words...");
        
        $bar = $this->output->createProgressBar(count($wordsToTranslate));
        $bar->start();

        $translations = [];
        
        foreach ($batches as $batchIndex => $batch) {
            $words = array_column($batch, 'word');
            
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
            
            // Small delay between batches to avoid rate limiting
            if ($batchIndex < count($batches) - 1) {
                usleep(500000); // 0.5 second
            }
        }
        
        $bar->finish();
        $this->newLine();

        // Update JSON data
        $data = $this->updateJsonData($data, $translations);
        
        // Save file
        if (!$this->option('dry-run')) {
            $this->info("Saving updated JSON...");
            $result = file_put_contents(
                $filePath,
                json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );
            
            if ($result === false) {
                $this->error("Failed to save file: {$filePath}");
                return Command::FAILURE;
            }
            
            $this->info("File saved successfully!");
            
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
        
        return [
            'exported_at' => now()->toIso8601String(),
            'lang' => 'pl',
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
            $filePath = public_path('exports/words/words_pl.json');
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
                    ->where('lang', 'pl')
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
                        'lang' => 'pl',
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
        $this->info("=== Translation Report ===");
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
            $reportPath = storage_path('logs/pl_translation_report_' . now()->format('Y-m-d_H-i-s') . '.txt');
            $report = "Polish Translation Report\n";
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
}
