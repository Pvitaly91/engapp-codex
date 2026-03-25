<?php

namespace Tests\Feature;

use App\Http\Controllers\SeedRunController;
use App\Models\Page;
use App\Models\PageCategory;
use App\Models\TextBlock;
use App\Modules\SeedRunsV2\Services\SeedRunsService;
use App\Services\QuestionDeletionService;
use App\Support\Database\JsonPageLocalizationManager;
use App\Support\Database\JsonTestLocalizationManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SeedRunCategoryDeletionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->rebuildMinimalSchema();
    }

    /** @test */
    public function destroy_with_questions_deletes_theory_category_data(): void
    {
        $fixture = $this->createCategoryDeletionFixture('Tests\\Seeders\\FakeTheoryCategorySeeder');

        $controller = new SeedRunController(
            app(QuestionDeletionService::class),
            app(JsonTestLocalizationManager::class),
            app(JsonPageLocalizationManager::class),
        );

        $request = Request::create('/admin/seed-runs/' . $fixture['seed_run_id'] . '/with-questions', 'DELETE');
        $request->headers->set('Accept', 'application/json');

        $response = $controller->destroyWithQuestions($request, $fixture['seed_run_id']);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertCategoryFixtureWasDeletedSafely($fixture);
    }

    /** @test */
    public function seed_runs_service_deletes_theory_category_data(): void
    {
        $fixture = $this->createCategoryDeletionFixture('Tests\\Seeders\\FakeTheoryCategorySeederV2');

        $result = app(SeedRunsService::class)->destroySeedRunWithData($fixture['seed_run_id']);

        $this->assertTrue($result['success']);
        $this->assertCategoryFixtureWasDeletedSafely($fixture);
    }

    private function rebuildMinimalSchema(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach (['seed_runs', 'questions', 'text_blocks', 'pages', 'page_categories'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();

        Schema::create('seed_runs', function (Blueprint $table) {
            $table->id();
            $table->string('class_name')->unique();
            $table->timestamp('ran_at')->nullable();
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('question')->nullable();
            $table->string('seeder')->nullable();
            $table->timestamps();
        });

        Schema::create('page_categories', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('language')->nullable();
            $table->string('type')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('seeder')->nullable();
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('title');
            $table->text('text')->nullable();
            $table->string('type')->nullable();
            $table->string('seeder')->nullable();
            $table->unsignedBigInteger('page_category_id')->nullable();
            $table->timestamps();
        });

        Schema::create('text_blocks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable();
            $table->unsignedBigInteger('page_id')->nullable();
            $table->unsignedBigInteger('page_category_id')->nullable();
            $table->string('locale', 8)->nullable();
            $table->string('type')->nullable();
            $table->string('column')->nullable();
            $table->string('heading')->nullable();
            $table->string('css_class')->nullable();
            $table->integer('sort_order')->default(0);
            $table->text('body')->nullable();
            $table->string('level')->nullable();
            $table->string('seeder')->nullable();
            $table->timestamps();
        });
    }

    /**
     * @return array<string, int>
     */
    private function createCategoryDeletionFixture(string $categorySeederClass): array
    {
        $seedRunId = (int) DB::table('seed_runs')->insertGetId([
            'class_name' => $categorySeederClass,
            'ran_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $category = PageCategory::create([
            'title' => 'Theory Category',
            'slug' => 'theory-category-' . $seedRunId,
            'language' => 'uk',
            'type' => 'theory',
            'seeder' => $categorySeederClass,
        ]);

        $standaloneCategoryBlock = TextBlock::create([
            'page_id' => null,
            'page_category_id' => $category->id,
            'locale' => 'uk',
            'type' => 'subtitle',
            'column' => 'header',
            'sort_order' => 0,
            'body' => 'Category description block',
            'seeder' => $categorySeederClass,
        ]);

        $page = Page::create([
            'slug' => 'theory-page-' . $seedRunId,
            'title' => 'Theory Page',
            'type' => 'theory',
            'seeder' => 'Tests\\Seeders\\OtherPageSeeder',
            'page_category_id' => $category->id,
        ]);

        $pageBlock = TextBlock::create([
            'page_id' => $page->id,
            'page_category_id' => $category->id,
            'locale' => 'uk',
            'type' => 'box',
            'column' => 'left',
            'sort_order' => 1,
            'body' => 'Page block that must survive category deletion',
            'seeder' => 'Tests\\Seeders\\OtherPageSeeder',
        ]);

        return [
            'seed_run_id' => $seedRunId,
            'category_id' => $category->id,
            'standalone_block_id' => $standaloneCategoryBlock->id,
            'page_id' => $page->id,
            'page_block_id' => $pageBlock->id,
        ];
    }

    /**
     * @param  array<string, int>  $fixture
     */
    private function assertCategoryFixtureWasDeletedSafely(array $fixture): void
    {
        $this->assertDatabaseMissing('seed_runs', ['id' => $fixture['seed_run_id']]);
        $this->assertDatabaseMissing('page_categories', ['id' => $fixture['category_id']]);
        $this->assertDatabaseMissing('text_blocks', ['id' => $fixture['standalone_block_id']]);

        $page = Page::query()->find($fixture['page_id']);
        $pageBlock = TextBlock::query()->find($fixture['page_block_id']);

        $this->assertNotNull($page);
        $this->assertNull($page->page_category_id);
        $this->assertNotNull($pageBlock);
        $this->assertSame($fixture['page_id'], $pageBlock->page_id);
        $this->assertNull($pageBlock->page_category_id);
    }
}
