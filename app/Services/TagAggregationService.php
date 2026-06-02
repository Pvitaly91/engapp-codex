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
                $currentTime = now()->toIso8601String();
                
                // Handle both old format (array of strings) and new format (array of objects)
                $existingSimilarTags = $aggregation['similar_tags'] ?? [];
                $existingTagNames = [];
                
                // Extract existing tag names
                foreach ($existingSimilarTags as $item) {
                    if (is_array($item) && isset($item['tag'])) {
                        $existingTagNames[] = $item['tag'];
                    } else {
                        // Old format: convert string to new format
                        $existingTagNames[] = $item;
                    }
                }
                
                // Merge and convert to new format with timestamps
                $mergedTags = array_unique(array_merge($existingTagNames, $similarTags));
                $similarTagsWithTimestamps = [];
                
                foreach ($mergedTags as $tag) {
                    // Check if this tag already exists in the old array
                    $foundExisting = false;
                    foreach ($existingSimilarTags as $item) {
                        if (is_array($item) && isset($item['tag']) && $item['tag'] === $tag) {
                            $similarTagsWithTimestamps[] = $item; // Keep existing with its timestamp
                            $foundExisting = true;
                            break;
                        } elseif (!is_array($item) && $item === $tag) {
                            // Convert old format to new format, keep original order
                            $similarTagsWithTimestamps[] = [
                                'tag' => $tag,
                                'added_at' => $aggregation['created_at'] ?? $currentTime,
                            ];
                            $foundExisting = true;
                            break;
                        }
                    }
                    
                    // If it's a new tag, add it with current timestamp
                    if (!$foundExisting) {
                        $similarTagsWithTimestamps[] = [
                            'tag' => $tag,
                            'added_at' => $currentTime,
                        ];
                    }
                }
                
                $aggregation['similar_tags'] = $similarTagsWithTimestamps;
                
                // Add main_tag_created_at if it doesn't exist
                if (!isset($aggregation['main_tag_created_at'])) {
                    $aggregation['main_tag_created_at'] = $aggregation['created_at'] ?? $currentTime;
                }
                
                // Update category if provided
                if ($category !== null) {
                    $aggregation['category'] = $category;
                    // Track category creation time if it's a new category
                    if (!isset($categoryCreationTimes[$category])) {
                        $categoryCreationTimes[$category] = $currentTime;
                    }
                }
                $exists = true;
                break;
            }
        }
        
        if (!$exists) {
            $currentTime = now()->toIso8601String();
            
            // Create similar tags with timestamps
            $similarTagsWithTimestamps = [];
            foreach (array_unique($similarTags) as $tag) {
                $similarTagsWithTimestamps[] = [
                    'tag' => $tag,
                    'added_at' => $currentTime,
                ];
            }
            
            $newAggregation = [
                'main_tag' => $mainTag,
                'main_tag_created_at' => $currentTime,
                'similar_tags' => $similarTagsWithTimestamps,
                'category' => $category,
                'created_at' => $currentTime,
            ];
            
            // Add to beginning of array to show newest first
            array_unshift($aggregations, $newAggregation);
            
            // Track category creation time if it's a new category
            if ($category && !isset($categoryCreationTimes[$category])) {
                $categoryCreationTimes[$category] = $currentTime;
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
