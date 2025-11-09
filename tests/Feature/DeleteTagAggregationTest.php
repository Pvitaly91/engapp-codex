<?php

namespace Tests\Feature;

use App\Services\TagAggregationService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DeleteTagAggregationTest extends TestCase
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

        // Ensure the config directory exists and clear aggregations
        $configDir = base_path('config/tags');
        if (! File::exists($configDir)) {
            File::makeDirectory($configDir, 0755, true);
        }
        
        // Clean up the aggregation file before each test
        $configPath = base_path('config/tags/aggregation.json');
        File::put($configPath, json_encode(['aggregations' => []], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
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
    public function delete_aggregation_removes_aggregation_and_redirects(): void
    {
        // Create some aggregations
        $service = app(TagAggregationService::class);
        $service->addAggregation('Present Simple', ['Simple Present', 'Present Tense'], 'Tenses');
        $service->addAggregation('Past Simple', ['Simple Past'], 'Tenses');

        // Delete one aggregation using regular request (not AJAX)
        $response = $this->withSession(['admin_authenticated' => true])
            ->delete('/admin/test-tags/aggregations/' . urlencode('Present Simple'));

        $response->assertRedirect('/admin/test-tags/aggregations');
        $response->assertSessionHas('status');
        $this->assertStringContainsString('видалено', session('status'));

        // Verify aggregation was removed
        $aggregations = $service->getAggregations();
        $this->assertCount(1, $aggregations);
        $this->assertEquals('Past Simple', $aggregations[0]['main_tag']);
    }

    /** @test */
    public function delete_aggregation_returns_json_for_ajax_request(): void
    {
        // Create some aggregations
        $service = app(TagAggregationService::class);
        $service->addAggregation('Present Simple', ['Simple Present', 'Present Tense'], 'Tenses');
        $service->addAggregation('Past Simple', ['Simple Past'], 'Tenses');

        // Delete one aggregation using AJAX request
        $response = $this->withSession(['admin_authenticated' => true])
            ->deleteJson('/admin/test-tags/aggregations/' . urlencode('Present Simple'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'main_tag']);
        $response->assertJson([
            'main_tag' => 'Present Simple',
        ]);

        // Verify aggregation was removed
        $aggregations = $service->getAggregations();
        $this->assertCount(1, $aggregations);
        $this->assertEquals('Past Simple', $aggregations[0]['main_tag']);
    }

    /** @test */
    public function delete_aggregation_handles_special_characters_in_tag_name(): void
    {
        // Create aggregation with special characters
        $service = app(TagAggregationService::class);
        $service->addAggregation('Tag / with / slashes', ['Similar Tag'], 'Category');

        // Delete using AJAX request
        $response = $this->withSession(['admin_authenticated' => true])
            ->deleteJson('/admin/test-tags/aggregations/' . urlencode('Tag / with / slashes'));

        $response->assertStatus(200);
        $response->assertJson([
            'main_tag' => 'Tag / with / slashes',
        ]);

        // Verify aggregation was removed
        $aggregations = $service->getAggregations();
        $this->assertCount(0, $aggregations);
    }

    /** @test */
    public function delete_nonexistent_aggregation_succeeds_gracefully(): void
    {
        // Create one aggregation
        $service = app(TagAggregationService::class);
        $service->addAggregation('Present Simple', ['Simple Present'], 'Tenses');

        // Try to delete a non-existent aggregation
        $response = $this->withSession(['admin_authenticated' => true])
            ->deleteJson('/admin/test-tags/aggregations/' . urlencode('Nonexistent Tag'));

        $response->assertStatus(200);
        
        // Verify original aggregation still exists
        $aggregations = $service->getAggregations();
        $this->assertCount(1, $aggregations);
        $this->assertEquals('Present Simple', $aggregations[0]['main_tag']);
    }
}
