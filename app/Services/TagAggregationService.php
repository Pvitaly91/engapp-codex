<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class TagAggregationService
{
    private string $configPath;

    public function __construct()
    {
        $this->configPath = base_path('config/tags/aggregation.json');
    }

    public function getAggregations(): array
    {
        if (!File::exists($this->configPath)) {
            return [];
        }

        $content = File::get($this->configPath);
        $data = json_decode($content, true);

        return $data['aggregations'] ?? [];
    }

    public function getCategoryCreationTimes(): array
    {
        if (!File::exists($this->configPath)) {
            return [];
        }

        $content = File::get($this->configPath);
        $data = json_decode($content, true);

        return $data['category_created_at'] ?? [];
    }

    public function saveAggregations(array $aggregations): bool
    {
        $categoryCreationTimes = $this->getCategoryCreationTimes();
        return $this->saveAggregationsWithMetadata($aggregations, $categoryCreationTimes);
    }

    private function saveAggregationsWithMetadata(array $aggregations, array $categoryCreationTimes): bool
    {
        $data = [
            'aggregations' => $aggregations,
            'category_created_at' => $categoryCreationTimes,
        ];
        
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        return File::put($this->configPath, $json) !== false;
    }

    public function addAggregation(string $mainTag, array $similarTags, ?string $category = null): bool
    {
        $aggregations = $this->getAggregations();
        $categoryCreationTimes = $this->getCategoryCreationTimes();
        
        // Check if main tag already exists in aggregations
        $exists = false;
        foreach ($aggregations as &$aggregation) {
            if ($aggregation['main_tag'] === $mainTag) {
                $aggregation['similar_tags'] = array_unique(array_merge(
                    $aggregation['similar_tags'] ?? [],
                    $similarTags
                ));
                // Update category if provided
                if ($category !== null) {
                    $aggregation['category'] = $category;
                    // Track category creation time if it's a new category
                    if (!isset($categoryCreationTimes[$category])) {
                        $categoryCreationTimes[$category] = now()->toIso8601String();
                    }
                }
                $exists = true;
                break;
            }
        }
        
        if (!$exists) {
            $newAggregation = [
                'main_tag' => $mainTag,
                'similar_tags' => array_unique($similarTags),
                'category' => $category,
                'created_at' => now()->toIso8601String(),
            ];
            
            // Add to beginning of array to show newest first
            array_unshift($aggregations, $newAggregation);
            
            // Track category creation time if it's a new category
            if ($category && !isset($categoryCreationTimes[$category])) {
                $categoryCreationTimes[$category] = now()->toIso8601String();
            }
        }
        
        return $this->saveAggregationsWithMetadata($aggregations, $categoryCreationTimes);
    }

    public function removeAggregation(string $mainTag): bool
    {
        $aggregations = $this->getAggregations();
        
        $aggregations = array_filter($aggregations, function ($aggregation) use ($mainTag) {
            return $aggregation['main_tag'] !== $mainTag;
        });
        
        return $this->saveAggregations(array_values($aggregations));
    }

    public function updateAggregation(string $mainTag, array $similarTags, ?string $category = null): bool
    {
        $aggregations = $this->getAggregations();
        
        foreach ($aggregations as &$aggregation) {
            if ($aggregation['main_tag'] === $mainTag) {
                $aggregation['similar_tags'] = array_unique($similarTags);
                // Update category if provided
                if ($category !== null) {
                    $aggregation['category'] = $category;
                }
                return $this->saveAggregations($aggregations);
            }
        }
        
        return false;
    }

    public function getMainTagFor(string $tagName): ?string
    {
        $aggregations = $this->getAggregations();
        
        foreach ($aggregations as $aggregation) {
            if (in_array($tagName, $aggregation['similar_tags'] ?? [])) {
                return $aggregation['main_tag'];
            }
        }
        
        return null;
    }
}
