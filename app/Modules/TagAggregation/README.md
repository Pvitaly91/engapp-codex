# Tag Aggregation Module

This module provides tag aggregation functionality that can be reused across projects.

## Features

- Store and manage tag aggregations
- Group similar tags under a main tag
- Support for categorizing aggregations
- JSON-based storage for easy portability

## Installation

1. Register the service provider in `config/app.php`:

```php
'providers' => [
    // ...
    App\Modules\TagAggregation\TagAggregationServiceProvider::class,
],
```

2. Ensure the storage directory exists:

```bash
mkdir -p config/tags
```

## Usage

### Basic Usage

```php
use App\Modules\TagAggregation\Services\TagAggregationService;

$service = app(TagAggregationService::class);

// Add an aggregation
$service->addAggregation('Present Simple', ['Simple Present', 'Present Tense'], 'Tenses');

// Get all aggregations
$aggregations = $service->getAggregations();

// Update an aggregation
$service->updateAggregation('Present Simple', ['Simple Present'], 'Tenses');

// Remove an aggregation
$service->removeAggregation('Present Simple');

// Find main tag for a similar tag
$mainTag = $service->getMainTagFor('Simple Present'); // Returns 'Present Simple'
```

### Data Format

Aggregations are stored in `config/tags/aggregation.json` with the following structure:

```json
{
  "aggregations": [
    {
      "main_tag": "Present Simple",
      "similar_tags": ["Simple Present", "Present Tense"],
      "category": "Tenses"
    }
  ]
}
```

## API

### TagAggregationService Methods

- `getAggregations(): array` - Get all aggregations
- `saveAggregations(array $aggregations): bool` - Save aggregations
- `addAggregation(string $mainTag, array $similarTags, ?string $category = null): bool` - Add or update an aggregation
- `removeAggregation(string $mainTag): bool` - Remove an aggregation
- `updateAggregation(string $mainTag, array $similarTags, ?string $category = null): bool` - Update an aggregation
- `getMainTagFor(string $tagName): ?string` - Find the main tag for a similar tag

## Testing

Tests are located in `tests/Feature/` and include:
- `AutoTagAggregationTest.php`
- `ImportTagAggregationTest.php`
- `AggregationCategoryGroupingTest.php`
