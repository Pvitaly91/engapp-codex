<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\SiteTreeItem;

class SiteTreeLinkingService
{
    /**
     * Link site tree items to pages
     */
    public function linkItemsToPages(?int $variantId = null): array
    {
        // Get all pages with URLs
        $pagesWithUrls = $this->getPageUrlMap();
        
        // Get all site tree items for the variant
        $query = SiteTreeItem::query();
        if ($variantId) {
            $query->where('variant_id', $variantId);
        }
        
        $items = $query->get();
        
        $linkedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        
        foreach ($items as $item) {
            $match = $this->findPageMatch($item->title, $pagesWithUrls);
            
            if ($match) {
                $hasExistingLink = !empty($item->linked_page_title) || !empty($item->linked_page_url);
                
                $item->update([
                    'linked_page_title' => $match['title'],
                    'linked_page_url' => $match['url'],
                ]);
                
                if ($hasExistingLink) {
                    $updatedCount++;
                } else {
                    $linkedCount++;
                }
            } else {
                $skippedCount++;
            }
        }
        
        return [
            'total' => $items->count(),
            'pages_count' => count($pagesWithUrls),
            'linked' => $linkedCount,
            'updated' => $updatedCount,
            'skipped' => $skippedCount,
        ];
    }
    
    /**
     * Build a map of page titles to URLs
     */
    public function getPageUrlMap(): array
    {
        $map = [];
        
        $categories = PageCategory::with(['pages' => function ($query) {
            $query->where('type', 'theory');
        }])->get();
        
        foreach ($categories as $category) {
            // Add category itself
            $map[$category->title] = [
                'title' => $category->title,
                'url' => route('theory.category', $category->slug),
                'seeder' => $category->seeder ?? null,
                'slug' => $category->slug,
            ];
            
            // Add pages within category
            foreach ($category->pages as $page) {
                $map[$page->title] = [
                    'title' => $page->title,
                    'url' => route('theory.show', [$category->slug, $page->slug]),
                    'seeder' => $page->seeder ?? null,
                    'slug' => $page->slug,
                ];
            }
        }
        
        return $map;
    }
    
    /**
     * Find matching page for site tree item title
     */
    public function findPageMatch(string $title, array $pagesWithUrls): ?array
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
        
        // Strategy 4: Try matching by seeder name
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
