<?php

namespace Tests\Feature;

use App\Services\TagAggregationService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AggregationAjaxDeleteTest extends TestCase
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

        // Clear aggregations before each test
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
    public function can_delete_aggregation_via_ajax(): void
    {
        // Create aggregation
        $service = app(TagAggregationService::class);
        $service->addAggregation('Present Simple', ['Simple Present', 'Present Tense'], 'Tenses');
        $service->addAggregation('Past Simple', ['Simple Past'], 'Tenses');

        // Verify aggregations exist
        $aggregations = $service->getAggregations();
        $this->assertCount(2, $aggregations);

        // Make AJAX DELETE request
        $response = $this->withSession(['admin_authenticated' => true])
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->delete('/admin/test-tags/aggregations/Present%20Simple');

        // Assert response
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Агрегацію тегів видалено.',
        ]);

        // Verify aggregation was deleted
        $aggregations = $service->getAggregations();
        $this->assertCount(1, $aggregations);
        $this->assertEquals('Past Simple', $aggregations[0]['main_tag']);
    }

    /** @test */
    public function can_delete_category_via_ajax(): void
    {
        // Create aggregations in different categories
        $service = app(TagAggregationService::class);
        $service->addAggregation('Present Simple', ['Simple Present'], 'Tenses');
        $service->addAggregation('Past Simple', ['Simple Past'], 'Tenses');
        $service->addAggregation('Modal Verbs', ['Modals'], 'Verbs');

        // Verify aggregations exist
        $aggregations = $service->getAggregations();
        $this->assertCount(3, $aggregations);

        // Make AJAX DELETE request for category
        $response = $this->withSession(['admin_authenticated' => true])
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->delete('/admin/test-tags/aggregations/category/Tenses');

        // Assert response
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Категорію та всі її агрегації видалено.',
        ]);

        // Verify only Verbs category aggregations remain
        $aggregations = $service->getAggregations();
        $this->assertCount(1, $aggregations);
        $this->assertEquals('Modal Verbs', $aggregations[0]['main_tag']);
        $this->assertEquals('Verbs', $aggregations[0]['category']);
    }

    /** @test */
    public function non_ajax_delete_still_works_with_redirect(): void
    {
        // Create aggregation
        $service = app(TagAggregationService::class);
        $service->addAggregation('Present Simple', ['Simple Present'], 'Tenses');

        // Make non-AJAX DELETE request (normal form submission)
        $response = $this->withSession(['admin_authenticated' => true])
            ->delete('/admin/test-tags/aggregations/Present%20Simple');

        // Assert redirect response
        $response->assertRedirect('/admin/test-tags/aggregations');
        $response->assertSessionHas('status', 'Агрегацію тегів видалено.');

        // Verify aggregation was deleted
        $aggregations = $service->getAggregations();
        $this->assertCount(0, $aggregations);
    }
}
