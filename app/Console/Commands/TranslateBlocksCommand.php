<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Models\TextBlock;
use App\Services\Translate\TranslationProviderFactory;
use App\Services\Translate\TranslationProviderInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TranslateBlocksCommand extends Command
{
    protected $signature = 'pages:translate-blocks {targetLocale : Target locale code (e.g., en, pl)}
                            {--provider=auto : Translation provider (auto, openai, gemini)}
                            {--batch-size=5 : Number of blocks to translate in each batch (smaller is more reliable for JSON/HTML)}
                            {--source-locale=uk : Source locale to translate from}
                            {--block-id= : Translate a single block by ID}
                            {--block-uuid= : Translate a single block by UUID}
                            {--page-id= : Translate all blocks of a page by page ID}
                            {--page-slug= : Translate all blocks of a page by slug}
                            {--type= : Translate all blocks of pages with this type (e.g., theory)}
                            {--all : Translate all pages and their blocks}
                            {--include-categories : Include category blocks (page_id=null, page_category_id!=null)}
                            {--dry-run : Show what would be done without making changes}
                            {--force : Overwrite existing translations}';

    protected $description = 'Translate text blocks content using AI providers (OpenAI or Gemini)';

    private TranslationProviderInterface $provider;
    private string $sourceLocale;
    private string $targetLocale;
    private int $batchSize;
    private bool $dryRun;
    private bool $force;
    private bool $includeCategories;
    private string $cachePath;

    // Statistics
    private int $blocksProcessed = 0;
    private int $blocksTranslated = 0;
    private int $blocksSkipped = 0;
    private int $blocksFailed = 0;
    private array $failedBlocks = [];

    // Delay between batches in microseconds
    private const OPENAI_BATCH_DELAY_MICROSECONDS = 3000000; // 3 seconds (increased for reliability)
    private const GEMINI_BATCH_DELAY_MICROSECONDS = 1000000;  // 1 second (increased for reliability)

    public function handle(): int
    {
        $this->targetLocale = $this->argument('targetLocale');
        $this->sourceLocale = $this->option('source-locale');
        $this->batchSize = (int) $this->option('batch-size');
        $this->dryRun = $this->option('dry-run');
        $this->force = $this->option('force');
        $this->includeCategories = $this->option('include-categories');
        $this->cachePath = storage_path("app/translate-blocks-cache/{$this->targetLocale}.json");

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
            $this->info("Option 1 - Use Gemini:");
            $this->info("  GEMINI_API_KEY=your-api-key-here");
            $this->newLine();
            $this->info("Option 2 - Use OpenAI:");
            $this->info("  CHAT_GPT_API_KEY=your-api-key-here");
            return Command::FAILURE;
        }

        // Warn about OpenAI rate limits and recommend smaller batch size
        if ($this->provider->getName() === 'openai' && $this->batchSize > 5) {
            $this->newLine();
            $this->warn("OpenAI has stricter rate limits and timeouts.");
            $this->warn("For text_blocks with JSON/HTML content, smaller batches are recommended:");
            $this->warn("  php artisan pages:translate-blocks {$this->targetLocale} --batch-size=3");
            $this->newLine();

            if ($this->batchSize > 10 && !$this->confirm("Continue with batch size {$this->batchSize}? (Recommended: 3-5 for complex content)", false)) {
                return Command::FAILURE;
            }
        }

        // Create cache directory
        $cacheDir = dirname($this->cachePath);
        if (!File::isDirectory($cacheDir)) {
            File::makeDirectory($cacheDir, 0755, true);
        }

        // Get blocks to translate
        $blocks = $this->getBlocksToTranslate();

        if ($blocks->isEmpty()) {
            $this->info("No blocks found to translate.");
            return Command::SUCCESS;
        }

        $this->info("Source locale: {$this->sourceLocale}");
        $this->info("Target locale: {$this->targetLocale}");
        $this->info("Found {$blocks->count()} block(s) to process");
        $this->newLine();

        // Load cache
        $cache = $this->loadCache();

        // Process blocks
        $this->processBlocks($blocks, $cache);

        // Save cache
        if (!$this->dryRun) {
            $this->saveCache($cache);
        }

        // Display report
        $this->displayReport();

        return Command::SUCCESS;
    }

    private function getBlocksToTranslate()
    {
        $query = TextBlock::query()->where('locale', $this->sourceLocale);

        // Priority 1: Single block by ID or UUID
        if ($blockId = $this->option('block-id')) {
            return $query->where('id', $blockId)->get();
        }

        if ($blockUuid = $this->option('block-uuid')) {
            return $query->where('uuid', $blockUuid)->get();
        }

        // Priority 2: Page-level selection
        if ($pageId = $this->option('page-id')) {
            return $query->where('page_id', $pageId)->orderBy('sort_order')->get();
        }

        if ($pageSlug = $this->option('page-slug')) {
            $pageQuery = Page::where('slug', $pageSlug);
            
            // If type is also specified, filter by it
            if ($type = $this->option('type')) {
                $pageQuery->forType($type);
            }

            $page = $pageQuery->first();
            if (!$page) {
                $this->error("Page not found with slug: {$pageSlug}");
                return collect();
            }

            return $query->where('page_id', $page->id)->orderBy('sort_order')->get();
        }

        // Priority 3: Type-based selection
        if ($type = $this->option('type')) {
            $pageIds = Page::forType($type)->pluck('id');
            $query->whereIn('page_id', $pageIds);
            
            if ($this->includeCategories) {
                // Also include category blocks
                $query->orWhere(function ($q) {
                    $q->whereNull('page_id')
                      ->whereNotNull('page_category_id')
                      ->where('locale', $this->sourceLocale);
                });
            }

            return $query->orderBy('page_id')->orderBy('sort_order')->get();
        }

        // Priority 4: All blocks
        if ($this->option('all')) {
            if ($this->includeCategories) {
                return $query->orderBy('page_id')->orderBy('sort_order')->get();
            }

            return $query->whereNotNull('page_id')->orderBy('page_id')->orderBy('sort_order')->get();
        }

        // No valid filter specified
        $this->error("Please specify one of: --block-id, --block-uuid, --page-id, --page-slug, --type, or --all");
        return collect();
    }

    private function processBlocks($blocks, array &$cache): void
    {
        $batches = $blocks->chunk($this->batchSize);
        $totalBatches = $batches->count();

        $bar = $this->output->createProgressBar($blocks->count());
        $bar->start();

        foreach ($batches as $batchIndex => $batch) {
            $this->processBatch($batch, $cache, $bar);

            // Delay between batches
            if ($batchIndex < $totalBatches - 1) {
                $delay = $this->provider->getName() === 'openai'
                    ? self::OPENAI_BATCH_DELAY_MICROSECONDS
                    : self::GEMINI_BATCH_DELAY_MICROSECONDS;
                usleep($delay);
            }
        }

        $bar->finish();
        $this->newLine();
    }

    private function processBatch($batch, array &$cache, $bar): void
    {
        $textsToTranslate = [];
        $blockMapping = []; // Maps index to block and field
        $processedBlockIds = []; // Track which blocks we're processing

        foreach ($batch as $block) {
            // Check if translation already exists
            $existingTranslation = $this->findExistingTranslation($block);

            if ($existingTranslation && !$this->force) {
                $this->blocksSkipped++;
                $bar->advance();
                continue;
            }

            $this->blocksProcessed++;
            $processedBlockIds[] = $block->id;

            // Collect texts for translation (heading and body)
            if (!empty($block->heading)) {
                $cacheKey = $this->getCacheKey($block->heading);
                if (isset($cache[$cacheKey])) {
                    $blockMapping[] = [
                        'block' => $block,
                        'field' => 'heading',
                        'cached' => $cache[$cacheKey],
                    ];
                } else {
                    $textsToTranslate[] = $block->heading;
                    $blockMapping[] = [
                        'block' => $block,
                        'field' => 'heading',
                        'index' => count($textsToTranslate) - 1,
                    ];
                }
            }

            if (!empty($block->body)) {
                $cacheKey = $this->getCacheKey($block->body);
                if (isset($cache[$cacheKey])) {
                    $blockMapping[] = [
                        'block' => $block,
                        'field' => 'body',
                        'cached' => $cache[$cacheKey],
                    ];
                } else {
                    $textsToTranslate[] = $block->body;
                    $blockMapping[] = [
                        'block' => $block,
                        'field' => 'body',
                        'index' => count($textsToTranslate) - 1,
                    ];
                }
            }
        }

        // If all blocks were skipped, we're done
        if (empty($processedBlockIds)) {
            return;
        }

        // Translate texts
        $translations = [];
        if (!empty($textsToTranslate)) {
            try {
                $translations = $this->provider->translateBatch(
                    $textsToTranslate,
                    $this->sourceLocale,
                    $this->targetLocale,
                    ['preserve_html' => true]
                );
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Translation batch failed: " . $e->getMessage());
                
                // Mark processed blocks as failed and advance progress
                foreach ($processedBlockIds as $blockId) {
                    $this->blocksFailed++;
                    $this->failedBlocks[] = "Block ID: {$blockId}";
                }
                $bar->advance(count($processedBlockIds));
                return;
            }
        }

        // Apply translations
        $translatedData = [];
        foreach ($blockMapping as $mapping) {
            $block = $mapping['block'];
            $field = $mapping['field'];

            if (isset($mapping['cached'])) {
                $translatedValue = $mapping['cached'];
            } elseif (isset($mapping['index']) && isset($translations[$mapping['index']])) {
                $translatedValue = $translations[$mapping['index']];
                // Update cache
                $originalValue = $field === 'heading' ? $block->heading : $block->body;
                $cache[$this->getCacheKey($originalValue)] = $translatedValue;
            } else {
                continue;
            }

            // Collect translation for this block
            if (!isset($translatedData[$block->id])) {
                $translatedData[$block->id] = [
                    'block' => $block,
                    'heading' => null,
                    'body' => null,
                ];
            }
            $translatedData[$block->id][$field] = $translatedValue;
        }

        // Save translated blocks and count
        foreach ($translatedData as $blockId => $data) {
            if (!$this->dryRun) {
                $this->saveTranslatedBlock($data['block'], $data['heading'], $data['body']);
            }
            $this->blocksTranslated++;
        }

        // Advance progress bar for all processed blocks (whether translated or not)
        $bar->advance(count($processedBlockIds));
    }

    private function findExistingTranslation(TextBlock $sourceBlock): ?TextBlock
    {
        $query = TextBlock::where('locale', $this->targetLocale);

        // Match by page_id and sort_order (primary method for page blocks)
        if ($sourceBlock->page_id) {
            return $query
                ->where('page_id', $sourceBlock->page_id)
                ->where('sort_order', $sourceBlock->sort_order)
                ->first();
        }

        // Match by page_category_id and sort_order (for category blocks)
        if ($sourceBlock->page_category_id) {
            return $query
                ->where('page_category_id', $sourceBlock->page_category_id)
                ->where('sort_order', $sourceBlock->sort_order)
                ->first();
        }

        return null;
    }

    private function saveTranslatedBlock(TextBlock $sourceBlock, ?string $heading, ?string $body): void
    {
        $existingTranslation = $this->findExistingTranslation($sourceBlock);

        if ($existingTranslation) {
            // Update existing translation
            $existingTranslation->update([
                'heading' => $heading ?? $existingTranslation->heading,
                'body' => $body ?? $existingTranslation->body,
            ]);
        } else {
            // Create new translation - UUID must be unique, so generate a new one or leave null
            // UUID is globally unique, so translated blocks should have their own UUID
            TextBlock::create([
                'uuid' => null, // Don't copy source UUID - it must be globally unique
                'page_id' => $sourceBlock->page_id,
                'page_category_id' => $sourceBlock->page_category_id,
                'locale' => $this->targetLocale,
                'type' => $sourceBlock->type,
                'column' => $sourceBlock->column,
                'heading' => $heading ?? $sourceBlock->heading,
                'css_class' => $sourceBlock->css_class,
                'sort_order' => $sourceBlock->sort_order,
                'body' => $body ?? $sourceBlock->body,
                'level' => $sourceBlock->level,
                'seeder' => $sourceBlock->seeder,
            ]);
        }
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
        $this->info("Blocks processed: {$this->blocksProcessed}");
        $this->info("Blocks translated: {$this->blocksTranslated}");
        $this->info("Blocks skipped (already translated): {$this->blocksSkipped}");
        $this->info("Blocks failed: {$this->blocksFailed}");

        if ($this->dryRun) {
            $this->warn("DRY RUN - No changes were saved");
        }

        if (!empty($this->failedBlocks)) {
            $this->newLine();
            $this->warn("Failed blocks:");
            foreach (array_slice($this->failedBlocks, 0, 10) as $blockInfo) {
                $this->line("  - {$blockInfo}");
            }
            if (count($this->failedBlocks) > 10) {
                $this->line("  ... and " . (count($this->failedBlocks) - 10) . " more");
            }
        }

        // Save detailed report
        if (!$this->dryRun && !empty($this->failedBlocks)) {
            $reportPath = storage_path("logs/translate_blocks_{$this->targetLocale}_" . now()->format('Y-m-d_H-i-s') . '.txt');
            $report = "Text Block Translation Report ({$this->sourceLocale} → {$this->targetLocale})\n";
            $report .= "Generated: " . now()->toDateTimeString() . "\n";
            $report .= "Provider: " . $this->provider->getName() . "\n\n";
            $report .= "Blocks processed: {$this->blocksProcessed}\n";
            $report .= "Blocks translated: {$this->blocksTranslated}\n";
            $report .= "Blocks skipped: {$this->blocksSkipped}\n";
            $report .= "Blocks failed: {$this->blocksFailed}\n\n";

            if (!empty($this->failedBlocks)) {
                $report .= "Failed blocks:\n";
                foreach ($this->failedBlocks as $blockInfo) {
                    $report .= "  - {$blockInfo}\n";
                }
            }

            File::put($reportPath, $report);
            $this->info("Detailed report saved to: {$reportPath}");
        }
    }
}
