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

    /** @test */
    public function destroy_folder_with_questions_deletes_unused_linked_categories_and_preserves_shared_ones(): void
    {
        $fixture = $this->createFolderDeletionFixture();

        $controller = new SeedRunController(
            app(QuestionDeletionService::class),
            app(JsonTestLocalizationManager::class),
            app(JsonPageLocalizationManager::class),
        );

        $request = Request::create('/admin/seed-runs/folders/delete-with-questions', 'DELETE', [
            'seed_run_ids' => array_values($fixture['seed_run_ids']),
            'folder_label' => 'Page_v2/TestFolder',
        ]);
        $request->headers->set('Accept', 'application/json');

        $response = $controller->destroyFolderWithQuestions($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());

        $payload = $response->getData(true);
        $this->assertSame(4, $payload['deleted_count']);
        $this->assertSame(3, $payload['categories_deleted']);
        $this->assertSame(2, $payload['pages_deleted']);

        $this->assertFolderFixtureWasDeletedSafely($fixture);
    }

    /** @test */
    public function seed_runs_service_deletes_unused_category_linked_through_page_data(): void
    {
        $fixture = $this->createLinkedPageCategoryFixture('Tests\\Seeders\\Page_v2\\Single\\LinkedPageSeeder');

        $result = app(SeedRunsService::class)->destroySeedRunWithData($fixture['seed_run_id']);

        $this->assertTrue($result['success']);
        $this->assertSame(1, $result['deleted_pages']);
        $this->assertSame(1, $result['deleted_categories']);
        $this->assertLinkedPageCategoryFixtureWasDeleted($fixture);
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
     * @return array<string, mixed>
     */
    private function createFolderDeletionFixture(): array
    {
        $seeders = [
            'parent_category' => 'Tests\\Seeders\\Page_v2\\Folder\\ParentCategorySeeder',
            'child_category' => 'Tests\\Seeders\\Page_v2\\Folder\\ChildCategorySeeder',
            'orphan_page' => 'Tests\\Seeders\\Page_v2\\Folder\\OrphanPageSeeder',
            'shared_page' => 'Tests\\Seeders\\Page_v2\\Folder\\SharedPageSeeder',
        ];

        $seedRunIds = [];

        foreach ($seeders as $key => $className) {
            $seedRunIds[$key] = (int) DB::table('seed_runs')->insertGetId([
                'class_name' => $className,
                'ran_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $parentCategory = PageCategory::create([
            'title' => 'Folder Parent Category',
            'slug' => 'folder-parent-category',
            'language' => 'uk',
            'type' => 'theory',
            'seeder' => $seeders['parent_category'],
        ]);

        $parentBlock = TextBlock::create([
            'page_id' => null,
            'page_category_id' => $parentCategory->id,
            'locale' => 'uk',
            'type' => 'subtitle',
            'column' => 'header',
            'sort_order' => 0,
            'body' => 'Parent category description',
            'seeder' => $seeders['parent_category'],
        ]);

        $childCategory = PageCategory::create([
            'title' => 'Folder Child Category',
            'slug' => 'folder-child-category',
            'language' => 'uk',
            'type' => 'theory',
            'parent_id' => $parentCategory->id,
            'seeder' => $seeders['child_category'],
        ]);

        $childBlock = TextBlock::create([
            'page_id' => null,
            'page_category_id' => $childCategory->id,
            'locale' => 'uk',
            'type' => 'subtitle',
            'column' => 'header',
            'sort_order' => 0,
            'body' => 'Child category description',
            'seeder' => $seeders['child_category'],
        ]);

        $orphanCategory = PageCategory::create([
            'title' => 'Unused Linked Category',
            'slug' => 'unused-linked-category',
            'language' => 'uk',
            'type' => 'theory',
            'seeder' => 'Tests\\Seeders\\Legacy\\UnusedLinkedCategorySeeder',
        ]);

        $orphanCategoryBlock = TextBlock::create([
            'page_id' => null,
            'page_category_id' => $orphanCategory->id,
            'locale' => 'uk',
            'type' => 'subtitle',
            'column' => 'header',
            'sort_order' => 0,
            'body' => 'Unused linked category description',
            'seeder' => 'Tests\\Seeders\\Legacy\\UnusedLinkedCategorySeeder',
        ]);

        $orphanPage = Page::create([
            'slug' => 'unused-linked-page',
            'title' => 'Unused Linked Page',
            'type' => 'theory',
            'seeder' => $seeders['orphan_page'],
            'page_category_id' => $orphanCategory->id,
        ]);

        $orphanPageBlock = TextBlock::create([
            'page_id' => $orphanPage->id,
            'page_category_id' => $orphanCategory->id,
            'locale' => 'uk',
            'type' => 'box',
            'column' => 'left',
            'sort_order' => 1,
            'body' => 'Unused linked page block',
            'seeder' => $seeders['orphan_page'],
        ]);

        $sharedCategory = PageCategory::create([
            'title' => 'Shared Category',
            'slug' => 'shared-linked-category',
            'language' => 'uk',
            'type' => 'theory',
            'seeder' => 'Tests\\Seeders\\Legacy\\SharedLinkedCategorySeeder',
        ]);

        $sharedCategoryBlock = TextBlock::create([
            'page_id' => null,
            'page_category_id' => $sharedCategory->id,
            'locale' => 'uk',
            'type' => 'subtitle',
            'column' => 'header',
            'sort_order' => 0,
            'body' => 'Shared category description',
            'seeder' => 'Tests\\Seeders\\Legacy\\SharedLinkedCategorySeeder',
        ]);

        $sharedTargetPage = Page::create([
            'slug' => 'shared-target-page',
            'title' => 'Shared Target Page',
            'type' => 'theory',
            'seeder' => $seeders['shared_page'],
            'page_category_id' => $sharedCategory->id,
        ]);

        $sharedTargetPageBlock = TextBlock::create([
            'page_id' => $sharedTargetPage->id,
            'page_category_id' => $sharedCategory->id,
            'locale' => 'uk',
            'type' => 'box',
            'column' => 'left',
            'sort_order' => 1,
            'body' => 'Shared target page block',
            'seeder' => $seeders['shared_page'],
        ]);

        $sharedOtherPage = Page::create([
            'slug' => 'shared-other-page',
            'title' => 'Shared Other Page',
            'type' => 'theory',
            'seeder' => 'Tests\\Seeders\\Other\\SharedPageSeeder',
            'page_category_id' => $sharedCategory->id,
        ]);

        $sharedOtherPageBlock = TextBlock::create([
            'page_id' => $sharedOtherPage->id,
            'page_category_id' => $sharedCategory->id,
            'locale' => 'uk',
            'type' => 'box',
            'column' => 'left',
            'sort_order' => 1,
            'body' => 'Shared other page block',
            'seeder' => 'Tests\\Seeders\\Other\\SharedPageSeeder',
        ]);

        return [
            'seed_run_ids' => $seedRunIds,
            'parent_category_id' => $parentCategory->id,
            'parent_block_id' => $parentBlock->id,
            'child_category_id' => $childCategory->id,
            'child_block_id' => $childBlock->id,
            'orphan_category_id' => $orphanCategory->id,
            'orphan_category_block_id' => $orphanCategoryBlock->id,
            'orphan_page_id' => $orphanPage->id,
            'orphan_page_block_id' => $orphanPageBlock->id,
            'shared_category_id' => $sharedCategory->id,
            'shared_category_block_id' => $sharedCategoryBlock->id,
            'shared_target_page_id' => $sharedTargetPage->id,
            'shared_target_page_block_id' => $sharedTargetPageBlock->id,
            'shared_other_page_id' => $sharedOtherPage->id,
            'shared_other_page_block_id' => $sharedOtherPageBlock->id,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function createLinkedPageCategoryFixture(string $pageSeederClass): array
    {
        $seedRunId = (int) DB::table('seed_runs')->insertGetId([
            'class_name' => $pageSeederClass,
            'ran_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $category = PageCategory::create([
            'title' => 'Linked Category',
            'slug' => 'linked-category-' . $seedRunId,
            'language' => 'uk',
            'type' => 'theory',
            'seeder' => 'Tests\\Seeders\\Legacy\\LinkedCategorySeeder',
        ]);

        $categoryBlock = TextBlock::create([
            'page_id' => null,
            'page_category_id' => $category->id,
            'locale' => 'uk',
            'type' => 'subtitle',
            'column' => 'header',
            'sort_order' => 0,
            'body' => 'Linked category description',
            'seeder' => 'Tests\\Seeders\\Legacy\\LinkedCategorySeeder',
        ]);

        $page = Page::create([
            'slug' => 'linked-page-' . $seedRunId,
            'title' => 'Linked Page',
            'type' => 'theory',
            'seeder' => $pageSeederClass,
            'page_category_id' => $category->id,
        ]);

        $pageBlock = TextBlock::create([
            'page_id' => $page->id,
            'page_category_id' => $category->id,
            'locale' => 'uk',
            'type' => 'box',
            'column' => 'left',
            'sort_order' => 1,
            'body' => 'Linked page block',
            'seeder' => $pageSeederClass,
        ]);

        return [
            'seed_run_id' => $seedRunId,
            'category_id' => $category->id,
            'category_block_id' => $categoryBlock->id,
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

    /**
     * @param  array<string, mixed>  $fixture
     */
    private function assertFolderFixtureWasDeletedSafely(array $fixture): void
    {
        foreach ($fixture['seed_run_ids'] as $seedRunId) {
            $this->assertDatabaseMissing('seed_runs', ['id' => $seedRunId]);
        }

        $this->assertDatabaseMissing('page_categories', ['id' => $fixture['parent_category_id']]);
        $this->assertDatabaseMissing('page_categories', ['id' => $fixture['child_category_id']]);
        $this->assertDatabaseMissing('page_categories', ['id' => $fixture['orphan_category_id']]);
        $this->assertDatabaseHas('page_categories', ['id' => $fixture['shared_category_id']]);

        $this->assertDatabaseMissing('text_blocks', ['id' => $fixture['parent_block_id']]);
        $this->assertDatabaseMissing('text_blocks', ['id' => $fixture['child_block_id']]);
        $this->assertDatabaseMissing('text_blocks', ['id' => $fixture['orphan_category_block_id']]);
        $this->assertDatabaseMissing('text_blocks', ['id' => $fixture['orphan_page_block_id']]);
        $this->assertDatabaseMissing('text_blocks', ['id' => $fixture['shared_target_page_block_id']]);
        $this->assertDatabaseHas('text_blocks', ['id' => $fixture['shared_category_block_id']]);
        $this->assertDatabaseHas('text_blocks', ['id' => $fixture['shared_other_page_block_id']]);

        $this->assertDatabaseMissing('pages', ['id' => $fixture['orphan_page_id']]);
        $this->assertDatabaseMissing('pages', ['id' => $fixture['shared_target_page_id']]);
        $this->assertDatabaseHas('pages', ['id' => $fixture['shared_other_page_id']]);

        $sharedOtherPage = Page::query()->find($fixture['shared_other_page_id']);
        $sharedOtherPageBlock = TextBlock::query()->find($fixture['shared_other_page_block_id']);

        $this->assertNotNull($sharedOtherPage);
        $this->assertSame($fixture['shared_category_id'], $sharedOtherPage->page_category_id);
        $this->assertNotNull($sharedOtherPageBlock);
        $this->assertSame($fixture['shared_category_id'], $sharedOtherPageBlock->page_category_id);
    }

    /**
     * @param  array<string, int>  $fixture
     */
    private function assertLinkedPageCategoryFixtureWasDeleted(array $fixture): void
    {
        $this->assertDatabaseMissing('seed_runs', ['id' => $fixture['seed_run_id']]);
        $this->assertDatabaseMissing('page_categories', ['id' => $fixture['category_id']]);
        $this->assertDatabaseMissing('pages', ['id' => $fixture['page_id']]);
        $this->assertDatabaseMissing('text_blocks', ['id' => $fixture['category_block_id']]);
        $this->assertDatabaseMissing('text_blocks', ['id' => $fixture['page_block_id']]);
    }
}
