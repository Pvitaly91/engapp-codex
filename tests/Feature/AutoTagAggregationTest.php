<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Services\ChatGPTService;
use App\Services\GeminiService;
use App\Services\TagAggregationService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AutoTagAggregationTest extends TestCase
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
    public function auto_aggregation_creates_aggregations_from_gemini(): void
    {
        // Create sample tags
        Tag::create(['name' => 'Present Simple', 'category' => 'Tenses']);
        Tag::create(['name' => 'Simple Present', 'category' => 'Tenses']);
        Tag::create(['name' => 'Past Simple', 'category' => 'Tenses']);
        Tag::create(['name' => 'Simple Past', 'category' => 'Tenses']);

        $this->mock(GeminiService::class, function ($mock) {
            $mock->shouldReceive('suggestTagAggregations')
                ->once()
                ->andReturn([
                    [
                        'main_tag' => 'Present Simple',
                        'similar_tags' => ['Simple Present'],
                    ],
                    [
                        'main_tag' => 'Past Simple',
                        'similar_tags' => ['Simple Past'],
                    ],
                ]);
        });

        $response = $this->withSession(['admin_authenticated' => true])
            ->post('/admin/test-tags/aggregations/auto');

        $response->assertRedirect('/admin/test-tags/aggregations');
        $response->assertSessionHas('status', 'Автоматично створено агрегацій: 2.');

        // Verify aggregations were saved
        $service = app(TagAggregationService::class);
        $aggregations = $service->getAggregations();

        $this->assertCount(2, $aggregations);
        $this->assertEquals('Present Simple', $aggregations[0]['main_tag']);
        $this->assertEquals(['Simple Present'], $aggregations[0]['similar_tags']);
        $this->assertEquals('Past Simple', $aggregations[1]['main_tag']);
        $this->assertEquals(['Simple Past'], $aggregations[1]['similar_tags']);
    }

    /** @test */
    public function auto_aggregation_returns_error_when_no_tags_exist(): void
    {
        $response = $this->withSession(['admin_authenticated' => true])
            ->post('/admin/test-tags/aggregations/auto');

        $response->assertRedirect('/admin/test-tags/aggregations');
        $response->assertSessionHas('error', 'Немає тегів для агрегації.');
    }

    /** @test */
    public function auto_aggregation_returns_error_when_gemini_fails(): void
    {
        // Create sample tags
        Tag::create(['name' => 'Present Simple', 'category' => 'Tenses']);
        Tag::create(['name' => 'Past Simple', 'category' => 'Tenses']);

        $this->mock(GeminiService::class, function ($mock) {
            $mock->shouldReceive('suggestTagAggregations')
                ->once()
                ->andThrow(new \RuntimeException('Не отримано відповіді від Gemini API'));
        });

        $response = $this->withSession(['admin_authenticated' => true])
            ->post('/admin/test-tags/aggregations/auto');

        $response->assertRedirect('/admin/test-tags/aggregations');
        $response->assertSessionHas('error');
        // Check that error message contains the detailed error
        $this->assertStringContainsString('Gemini', session('error'));
        $this->assertStringContainsString('Не отримано відповіді від Gemini API', session('error'));
    }

    /** @test */
    public function auto_aggregation_chatgpt_returns_error_when_fails(): void
    {
        // Create sample tags
        Tag::create(['name' => 'Present Simple', 'category' => 'Tenses']);
        Tag::create(['name' => 'Past Simple', 'category' => 'Tenses']);

        $this->mock(ChatGPTService::class, function ($mock) {
            $mock->shouldReceive('suggestTagAggregations')
                ->once()
                ->andThrow(new \RuntimeException('Не налаштовано API ключ ChatGPT'));
        });

        $response = $this->withSession(['admin_authenticated' => true])
            ->post('/admin/test-tags/aggregations/auto-chatgpt');

        $response->assertRedirect('/admin/test-tags/aggregations');
        $response->assertSessionHas('error');
        // Check that error message contains the detailed error
        $this->assertStringContainsString('ChatGPT', session('error'));
        $this->assertStringContainsString('Не налаштовано API ключ ChatGPT', session('error'));
    }

    /** @test */
    public function auto_aggregation_chatgpt_creates_aggregations(): void
    {
        // Create sample tags
        Tag::create(['name' => 'Present Simple', 'category' => 'Tenses']);
        Tag::create(['name' => 'Simple Present', 'category' => 'Tenses']);
        Tag::create(['name' => 'Past Simple', 'category' => 'Tenses']);

        $this->mock(ChatGPTService::class, function ($mock) {
            $mock->shouldReceive('suggestTagAggregations')
                ->once()
                ->andReturn([
                    [
                        'main_tag' => 'Present Simple',
                        'similar_tags' => ['Simple Present'],
                    ],
                ]);
        });

        $response = $this->withSession(['admin_authenticated' => true])
            ->post('/admin/test-tags/aggregations/auto-chatgpt');

        $response->assertRedirect('/admin/test-tags/aggregations');
        $response->assertSessionHas('status', 'Автоматично створено агрегацій: 1.');

        // Verify aggregations were saved
        $service = app(TagAggregationService::class);
        $aggregations = $service->getAggregations();

        $this->assertCount(1, $aggregations);
        $this->assertEquals('Present Simple', $aggregations[0]['main_tag']);
        $this->assertEquals(['Simple Present'], $aggregations[0]['similar_tags']);
    }
}
