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

    public function saveAggregations(array $aggregations): bool
    {
        $data = ['aggregations' => $aggregations];
        
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        return File::put($this->configPath, $json) !== false;
    }

    public function addAggregation(string $mainTag, array $similarTags): bool
    {
        $aggregations = $this->getAggregations();
        
        // Check if main tag already exists in aggregations
        $exists = false;
        foreach ($aggregations as &$aggregation) {
            if ($aggregation['main_tag'] === $mainTag) {
                $aggregation['similar_tags'] = array_unique(array_merge(
                    $aggregation['similar_tags'] ?? [],
                    $similarTags
                ));
                $exists = true;
                break;
            }
        }
        
        if (!$exists) {
            $aggregations[] = [
                'main_tag' => $mainTag,
                'similar_tags' => array_unique($similarTags),
            ];
        }
        
        return $this->saveAggregations($aggregations);
    }

    public function removeAggregation(string $mainTag): bool
    {
        $aggregations = $this->getAggregations();
        
        $aggregations = array_filter($aggregations, function ($aggregation) use ($mainTag) {
            return $aggregation['main_tag'] !== $mainTag;
        });
        
        return $this->saveAggregations(array_values($aggregations));
    }

    public function updateAggregation(string $mainTag, array $similarTags): bool
    {
        $aggregations = $this->getAggregations();
        
        foreach ($aggregations as &$aggregation) {
            if ($aggregation['main_tag'] === $mainTag) {
                $aggregation['similar_tags'] = array_unique($similarTags);
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
