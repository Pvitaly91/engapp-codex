<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Services\TagAggregationService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AggregationCategoryGroupingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Run only the migrations needed for tags
        $migrations = [
            '2025_07_30_000001_create_tags_table.php',
            '2025_08_01_000001_add_category_to_tags_table.php',
        ];
        foreach ($migrations as $file) {
            Artisan::call('migrate', ['--path' => 'database/migrations/'.$file]);
        }

        // Ensure the config directory exists
        $configDir = base_path('config/tags');
        if (! File::exists($configDir)) {
            File::makeDirectory($configDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up the aggregation file after tests
        $configPath = base_path('config/tags/aggregation.json');
        if (File::exists($configPath)) {
            File::put($configPath, json_encode(['aggregations' => []], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        parent::tearDown();
    }

    /** @test */
    public function aggregations_page_groups_by_category(): void
    {
        // Create sample tags with different categories
        Tag::create(['name' => 'Present Simple', 'category' => 'Tenses']);
        Tag::create(['name' => 'Simple Present', 'category' => 'Tenses']);
        Tag::create(['name' => 'Modal Verbs', 'category' => 'Verbs']);
        Tag::create(['name' => 'Auxiliary Verbs', 'category' => 'Verbs']);
        Tag::create(['name' => 'Articles', 'category' => null]);

        // Create aggregations
        $service = app(TagAggregationService::class);
        $service->addAggregation('Present Simple', ['Simple Present'], 'Tenses');
        $service->addAggregation('Modal Verbs', ['Auxiliary Verbs'], 'Verbs');
        $service->addAggregation('Articles', ['A', 'An', 'The'], null);

        // Make GET request to aggregations page
        $response = $this->withSession(['admin_authenticated' => true])
            ->get('/admin/test-tags/aggregations');

        $response->assertStatus(200);
        
        // Check that the view receives aggregationsByCategory
        $response->assertViewHas('aggregationsByCategory');
        
        // Verify the grouping structure
        $aggregationsByCategory = $response->viewData('aggregationsByCategory');
        
        // Should have 3 categories: Tenses, Verbs, and "Без категорії"
        $this->assertCount(3, $aggregationsByCategory);
        
        // Check Tenses category
        $this->assertTrue($aggregationsByCategory->has('Tenses'));
        $this->assertCount(1, $aggregationsByCategory->get('Tenses'));
        $this->assertEquals('Present Simple', $aggregationsByCategory->get('Tenses')[0]['main_tag']);
        
        // Check Verbs category
        $this->assertTrue($aggregationsByCategory->has('Verbs'));
        $this->assertCount(1, $aggregationsByCategory->get('Verbs'));
        $this->assertEquals('Modal Verbs', $aggregationsByCategory->get('Verbs')[0]['main_tag']);
        
        // Check uncategorized (Без категорії)
        $this->assertTrue($aggregationsByCategory->has('Без категорії'));
        $this->assertCount(1, $aggregationsByCategory->get('Без категорії'));
        $this->assertEquals('Articles', $aggregationsByCategory->get('Без категорії')[0]['main_tag']);
    }

    /** @test */
    public function aggregations_page_shows_empty_message_when_no_aggregations(): void
    {
        $response = $this->withSession(['admin_authenticated' => true])
            ->get('/admin/test-tags/aggregations');

        $response->assertStatus(200);
        $response->assertSee('Агрегації ще не створено');
    }

    /** @test */
    public function uncategorized_aggregations_appear_last(): void
    {
        // Create tags and aggregations
        Tag::create(['name' => 'Tag A', 'category' => 'Category A']);
        Tag::create(['name' => 'Tag B', 'category' => 'Category B']);
        Tag::create(['name' => 'Tag C', 'category' => null]);

        $service = app(TagAggregationService::class);
        $service->addAggregation('Tag C', ['C1'], null);
        $service->addAggregation('Tag B', ['B1'], 'Category B');
        $service->addAggregation('Tag A', ['A1'], 'Category A');

        // Make GET request
        $response = $this->withSession(['admin_authenticated' => true])
            ->get('/admin/test-tags/aggregations');

        $response->assertStatus(200);
        
        $aggregationsByCategory = $response->viewData('aggregationsByCategory');
        
        // Get the keys (category names) in order
        $categoryKeys = $aggregationsByCategory->keys()->toArray();
        
        // "Без категорії" should be the last one
        $this->assertEquals('Без категорії', $categoryKeys[count($categoryKeys) - 1]);
    }
}
