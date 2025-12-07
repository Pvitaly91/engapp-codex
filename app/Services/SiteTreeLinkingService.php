<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\SiteTreeItem;

class SiteTreeLinkingService
{
    /**
     * Link site tree items to pages based on seeder field
     */
    public function linkItemsToPages(?int $variantId = null): array
    {
        // Get all site tree items for the variant
        $query = SiteTreeItem::query();
        if ($variantId) {
            $query->where('variant_id', $variantId);
        }
        
        $items = $query->get();
        
        // Step 1: Clear all existing links
        $clearedCount = 0;
        foreach ($items as $item) {
            if (!empty($item->linked_page_title) || !empty($item->linked_page_url)) {
                $item->update([
                    'linked_page_title' => null,
                    'linked_page_url' => null,
                ]);
                $clearedCount++;
            }
        }
        
        // Step 2: Build seeder map
        $seederMap = $this->buildSeederMap();
        
        // Step 3: Link items based on seeder field only
        $linkedCount = 0;
        $skippedCount = 0;
        
        foreach ($items as $item) {
            $match = $this->findPageBySeeder($item->title, $seederMap);
            
            if ($match) {
                $item->update([
                    'linked_page_title' => $match['title'],
                    'linked_page_url' => $match['url'],
                ]);
                $linkedCount++;
            } else {
                $skippedCount++;
            }
        }
        
        return [
            'total' => $items->count(),
            'cleared' => $clearedCount,
            'linked' => $linkedCount,
            'skipped' => $skippedCount,
        ];
    }
    
    /**
     * Build a map of seeder class names to page/category info
     */
    protected function buildSeederMap(): array
    {
        $map = [];
        
        // Get all categories with their seeders
        $categories = PageCategory::whereNotNull('seeder')->get();
        foreach ($categories as $category) {
            if (!empty($category->seeder)) {
                $map[$category->seeder] = [
                    'title' => $category->title,
                    'url' => route('theory.category', $category->slug),
                    'type' => 'category',
                ];
            }
        }
        
        // Get all theory pages with their seeders
        $pages = Page::where('type', 'theory')
            ->whereNotNull('seeder')
            ->with('category')
            ->get();
            
        foreach ($pages as $page) {
            if (!empty($page->seeder) && $page->category) {
                $map[$page->seeder] = [
                    'title' => $page->title,
                    'url' => route('theory.show', [$page->category->slug, $page->slug]),
                    'type' => 'page',
                ];
            }
        }
        
        return $map;
    }
    
    /**
     * Find matching page by seeder class name
     */
    protected function findPageBySeeder(string $itemTitle, array $seederMap): ?array
    {
        // Clean the title - remove numbering and extra characters
        $cleanTitle = preg_replace('/^\d+\.\s*/', '', $itemTitle);
        $cleanTitle = preg_replace('/^\d+\.\d+\s*/', '', $cleanTitle);
        $cleanTitle = trim($cleanTitle);
        
        // Extract English part (before —) if present
        if (strpos($cleanTitle, '—') !== false) {
            $parts = explode('—', $cleanTitle);
            $cleanTitle = trim($parts[0]);
        }
        
        // Convert title to potential seeder class name pattern
        // Example: "Word Order" -> "WordOrder"
        $titlePattern = $this->titleToSeederPattern($cleanTitle);
        
        // Search through seeder map
        foreach ($seederMap as $seederClass => $pageInfo) {
            $seederBaseName = class_basename($seederClass);
            
            // Check if the seeder class name contains the title pattern
            if (stripos($seederBaseName, $titlePattern) !== false) {
                return $pageInfo;
            }
        }
        
        return null;
    }
    
    /**
     * Convert title to seeder pattern for matching
     */
    protected function titleToSeederPattern(string $title): string
    {
        // Remove common connecting words
        $words = preg_split('/\s+/', $title);
        $significantWords = array_filter($words, function($word) {
            $lowerWord = strtolower($word);
            return !in_array($lowerWord, ['and', 'or', 'the', 'a', 'an', 'in', 'on', 'at', 'to', 'for', 'of', 'with']);
        });
        
        // Join words without spaces (CamelCase pattern)
        return implode('', array_map('ucfirst', $significantWords));
    }
}
