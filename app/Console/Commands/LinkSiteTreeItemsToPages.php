<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\SiteTreeItem;
use Illuminate\Console\Command;

class LinkSiteTreeItemsToPages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'site-tree:link-pages {--variant-id= : Variant ID to link (default: base variant)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Link site tree items to pages by seeder name or slug';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $variantId = $this->option('variant-id');
        
        $this->info('🔗 Linking site tree items to pages...');
        
        // Get all pages with URLs
        $pagesWithUrls = $this->getPageUrlMap();
        $this->info("Found {count($pagesWithUrls)} pages with URLs");
        
        // Get all site tree items
        $query = SiteTreeItem::query();
        if ($variantId) {
            $query->where('variant_id', $variantId);
        }
        
        $items = $query->get();
        $this->info("Found {$items->count()} site tree items");
        
        $linkedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        
        foreach ($items as $item) {
            $result = $this->linkItemToPage($item, $pagesWithUrls);
            
            if ($result === 'linked') {
                $linkedCount++;
            } elseif ($result === 'updated') {
                $updatedCount++;
            } else {
                $skippedCount++;
            }
        }
        
        $this->info("✅ Linking complete!");
        $this->info("   - New links created: {$linkedCount}");
        $this->info("   - Existing links updated: {$updatedCount}");
        $this->info("   - Items without matches: {$skippedCount}");
        
        return Command::SUCCESS;
    }
    
    /**
     * Build a map of page titles to URLs
     */
    protected function getPageUrlMap(): array
    {
        $map = [];
        
        $categories = PageCategory::with(['pages' => function ($query) {
            $query->where('type', 'theory');
        }])->get();
        
        foreach ($categories as $category) {
            // Add category itself
            $map[$category->title] = [
                'url' => route('theory.category', $category->slug),
                'seeder' => $category->seeder ?? null,
                'slug' => $category->slug,
            ];
            
            // Add pages within category
            foreach ($category->pages as $page) {
                $map[$page->title] = [
                    'url' => route('theory.show', [$category->slug, $page->slug]),
                    'seeder' => $page->seeder ?? null,
                    'slug' => $page->slug,
                ];
            }
        }
        
        return $map;
    }
    
    /**
     * Link a site tree item to a page
     */
    protected function linkItemToPage(SiteTreeItem $item, array $pagesWithUrls): string
    {
        $match = $this->findMatch($item->title, $pagesWithUrls);
        
        if (!$match) {
            return 'skipped';
        }
        
        $hasExistingLink = !empty($item->linked_page_title) || !empty($item->linked_page_url);
        
        $item->update([
            'linked_page_title' => $match['title'],
            'linked_page_url' => $match['url'],
        ]);
        
        return $hasExistingLink ? 'updated' : 'linked';
    }
    
    /**
     * Find matching page for site tree item title
     */
    protected function findMatch(string $title, array $pagesWithUrls): ?array
    {
        // Strategy 1: Try exact title match
        if (isset($pagesWithUrls[$title])) {
            return [
                'title' => $title,
                'url' => $pagesWithUrls[$title]['url'],
            ];
        }
        
        // Strategy 2: Try title without numbering (e.g., "1. Category" -> "Category")
        $cleanTitle = preg_replace('/^\d+\.\s*/', '', $title);
        $cleanTitle = preg_replace('/^\d+\.\d+\s*/', '', $cleanTitle);
        $cleanTitle = trim($cleanTitle);
        
        if ($cleanTitle !== $title && isset($pagesWithUrls[$cleanTitle])) {
            return [
                'title' => $cleanTitle,
                'url' => $pagesWithUrls[$cleanTitle]['url'],
            ];
        }
        
        // Strategy 3: Try fuzzy matching by checking if titles contain each other
        foreach ($pagesWithUrls as $pageTitle => $pageData) {
            $cleanPageTitle = preg_replace('/^\d+\.\s*/', '', $pageTitle);
            $cleanPageTitle = preg_replace('/^\d+\.\d+\s*/', '', $cleanPageTitle);
            $cleanPageTitle = trim($cleanPageTitle);
            
            // Check if either title contains the other (case-insensitive)
            if (
                stripos($cleanTitle, $cleanPageTitle) !== false ||
                stripos($cleanPageTitle, $cleanTitle) !== false ||
                stripos($title, $pageTitle) !== false ||
                stripos($pageTitle, $title) !== false
            ) {
                return [
                    'title' => $pageTitle,
                    'url' => $pageData['url'],
                ];
            }
        }
        
        // Strategy 4: Try matching by seeder name if available
        // Extract the class name from item title and try to match with seeder classes
        foreach ($pagesWithUrls as $pageTitle => $pageData) {
            if (!empty($pageData['seeder'])) {
                // Check if seeder class name contains parts of the title
                $seederBaseName = class_basename($pageData['seeder']);
                $titleWords = preg_split('/\s+/', $cleanTitle);
                
                $matchCount = 0;
                foreach ($titleWords as $word) {
                    if (strlen($word) > 3 && stripos($seederBaseName, $word) !== false) {
                        $matchCount++;
                    }
                }
                
                // If more than half of significant words match, consider it a match
                if ($matchCount > 0 && $matchCount >= (count($titleWords) / 2)) {
                    return [
                        'title' => $pageTitle,
                        'url' => $pageData['url'],
                    ];
                }
            }
        }
        
        return null;
    }
}
