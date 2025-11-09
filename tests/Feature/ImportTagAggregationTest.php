<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Modules\TagAggregation\Services\TagAggregationService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ImportTagAggregationTest extends TestCase
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

        // Ensure the config directory exists and clear any existing aggregations
        $configDir = base_path('config/tags');
        if (! File::exists($configDir)) {
            File::makeDirectory($configDir, 0755, true);
        }
        
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
    public function import_aggregations_saves_valid_json(): void
    {
        $jsonData = json_encode([
            'aggregations' => [
                [
                    'main_tag' => 'Present Simple',
                    'similar_tags' => ['Simple Present', 'Present Tense'],
                    'category' => 'Tenses',
                ],
                [
                    'main_tag' => 'Past Simple',
                    'similar_tags' => ['Simple Past'],
                    'category' => 'Tenses',
                ],
            ],
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->post('/admin/test-tags/aggregations/import', [
                'json_data' => $jsonData,
            ]);

        $response->assertRedirect('/admin/test-tags/aggregations');
        $response->assertSessionHas('status');
        $this->assertStringContainsString('успішно імпортовано', session('status'));
        $this->assertStringContainsString('2', session('status'));

        // Verify aggregations were saved
        $service = app(TagAggregationService::class);
        $aggregations = $service->getAggregations();

        $this->assertCount(2, $aggregations);
        $this->assertEquals('Present Simple', $aggregations[0]['main_tag']);
        $this->assertEquals(['Simple Present', 'Present Tense'], $aggregations[0]['similar_tags']);
        $this->assertEquals('Tenses', $aggregations[0]['category']);
        $this->assertEquals('Past Simple', $aggregations[1]['main_tag']);
        $this->assertEquals(['Simple Past'], $aggregations[1]['similar_tags']);
        $this->assertEquals('Tenses', $aggregations[1]['category']);
    }

    /** @test */
    public function import_aggregations_returns_error_for_invalid_json(): void
    {
        $response = $this->withSession(['admin_authenticated' => true])
            ->post('/admin/test-tags/aggregations/import', [
                'json_data' => 'not valid json {',
            ]);

        $response->assertRedirect('/admin/test-tags/aggregations');
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Невалідний JSON формат', session('error'));
    }

    /** @test */
    public function import_aggregations_returns_error_when_aggregations_key_is_missing(): void
    {
        $jsonData = json_encode([
            'data' => [
                [
                    'main_tag' => 'Present Simple',
                    'similar_tags' => ['Simple Present'],
                ],
            ],
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->post('/admin/test-tags/aggregations/import', [
                'json_data' => $jsonData,
            ]);

        $response->assertRedirect('/admin/test-tags/aggregations');
        $response->assertSessionHas('error');
        $this->assertStringContainsString('aggregations', session('error'));
    }

    /** @test */
    public function import_aggregations_returns_error_when_main_tag_is_missing(): void
    {
        $jsonData = json_encode([
            'aggregations' => [
                [
                    'similar_tags' => ['Simple Present'],
                    'category' => 'Tenses',
                ],
            ],
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->post('/admin/test-tags/aggregations/import', [
                'json_data' => $jsonData,
            ]);

        $response->assertRedirect('/admin/test-tags/aggregations');
        $response->assertSessionHas('error');
        $this->assertStringContainsString('main_tag', session('error'));
    }

    /** @test */
    public function import_aggregations_returns_error_when_similar_tags_is_missing(): void
    {
        $jsonData = json_encode([
            'aggregations' => [
                [
                    'main_tag' => 'Present Simple',
                    'category' => 'Tenses',
                ],
            ],
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->post('/admin/test-tags/aggregations/import', [
                'json_data' => $jsonData,
            ]);

        $response->assertRedirect('/admin/test-tags/aggregations');
        $response->assertSessionHas('error');
        $this->assertStringContainsString('similar_tags', session('error'));
    }

    /** @test */
    public function import_aggregations_accepts_optional_category(): void
    {
        $jsonData = json_encode([
            'aggregations' => [
                [
                    'main_tag' => 'Present Simple',
                    'similar_tags' => ['Simple Present'],
                ],
                [
                    'main_tag' => 'Past Simple',
                    'similar_tags' => ['Simple Past'],
                    'category' => 'Tenses',
                ],
            ],
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->post('/admin/test-tags/aggregations/import', [
                'json_data' => $jsonData,
            ]);

        $response->assertRedirect('/admin/test-tags/aggregations');
        $response->assertSessionHas('status');

        // Verify aggregations were saved
        $service = app(TagAggregationService::class);
        $aggregations = $service->getAggregations();

        $this->assertCount(2, $aggregations);
        $this->assertNull($aggregations[0]['category'] ?? null);
        $this->assertEquals('Tenses', $aggregations[1]['category']);
    }

    /** @test */
    public function import_aggregations_overwrites_existing_aggregations(): void
    {
        // First, create some existing aggregations
        $service = app(TagAggregationService::class);
        $service->addAggregation('Existing Tag', ['old1', 'old2'], 'Old Category');

        $jsonData = json_encode([
            'aggregations' => [
                [
                    'main_tag' => 'New Tag',
                    'similar_tags' => ['new1', 'new2'],
                    'category' => 'New Category',
                ],
            ],
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->post('/admin/test-tags/aggregations/import', [
                'json_data' => $jsonData,
            ]);

        $response->assertRedirect('/admin/test-tags/aggregations');
        $response->assertSessionHas('status');

        // Verify only the new aggregations exist
        $aggregations = $service->getAggregations();

        $this->assertCount(1, $aggregations);
        $this->assertEquals('New Tag', $aggregations[0]['main_tag']);
    }

    /** @test */
    public function generate_prompt_returns_valid_prompt(): void
    {
        // Create sample tags
        Tag::create(['name' => 'Present Simple', 'category' => 'Tenses']);
        Tag::create(['name' => 'Past Simple', 'category' => 'Tenses']);

        $response = $this->withSession(['admin_authenticated' => true])
            ->getJson('/admin/test-tags/aggregations/generate-prompt');

        $response->assertStatus(200);
        $response->assertJsonStructure(['prompt', 'tags_count']);
        
        $data = $response->json();
        $this->assertStringContainsString('Present Simple', $data['prompt']);
        $this->assertStringContainsString('Past Simple', $data['prompt']);
        $this->assertStringContainsString('main_tag', $data['prompt']);
        $this->assertStringContainsString('similar_tags', $data['prompt']);
        $this->assertEquals(2, $data['tags_count']);
    }

    /** @test */
    public function generate_prompt_returns_error_when_no_tags(): void
    {
        $response = $this->withSession(['admin_authenticated' => true])
            ->getJson('/admin/test-tags/aggregations/generate-prompt');

        $response->assertStatus(400);
        $response->assertJsonStructure(['error']);
    }

    /** @test */
    public function import_aggregations_accepts_api_format_without_wrapper(): void
    {
        // Create tags with categories
        Tag::create(['name' => 'Present Simple', 'category' => 'Tenses']);
        Tag::create(['name' => 'Simple Present', 'category' => 'Tenses']);
        Tag::create(['name' => 'Past Simple', 'category' => 'Tenses']);

        // API format - direct array without wrapper
        $jsonData = json_encode([
            [
                'main_tag' => 'Present Simple',
                'similar_tags' => ['Simple Present'],
            ],
            [
                'main_tag' => 'Past Simple',
                'similar_tags' => ['Simple Past'],
            ],
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->post('/admin/test-tags/aggregations/import', [
                'json_data' => $jsonData,
            ]);

        $response->assertRedirect('/admin/test-tags/aggregations');
        $response->assertSessionHas('status');

        // Verify aggregations were saved with auto-assigned categories
        $service = app(TagAggregationService::class);
        $aggregations = $service->getAggregations();

        $this->assertCount(2, $aggregations);
        $this->assertEquals('Present Simple', $aggregations[0]['main_tag']);
        $this->assertEquals('Tenses', $aggregations[0]['category']); // Auto-assigned from tag
    }

    /** @test */
    public function import_aggregations_auto_assigns_category_from_main_tag(): void
    {
        // Create tags with categories
        Tag::create(['name' => 'Present Simple', 'category' => 'Grammar']);
        Tag::create(['name' => 'Simple Present', 'category' => 'Grammar']);

        // Wrapped format without category
        $jsonData = json_encode([
            'aggregations' => [
                [
                    'main_tag' => 'Present Simple',
                    'similar_tags' => ['Simple Present'],
                ],
            ],
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->post('/admin/test-tags/aggregations/import', [
                'json_data' => $jsonData,
            ]);

        $response->assertRedirect('/admin/test-tags/aggregations');
        $response->assertSessionHas('status');

        // Verify category was auto-assigned
        $service = app(TagAggregationService::class);
        $aggregations = $service->getAggregations();

        $this->assertCount(1, $aggregations);
        $this->assertEquals('Grammar', $aggregations[0]['category']);
    }

    /** @test */
    public function import_aggregations_returns_error_for_invalid_array_format(): void
    {
        $jsonData = json_encode([
            'invalid' => 'format'
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->post('/admin/test-tags/aggregations/import', [
                'json_data' => $jsonData,
            ]);

        $response->assertRedirect('/admin/test-tags/aggregations');
        $response->assertSessionHas('error');
        $this->assertStringContainsString('масив', session('error'));
    }
}
