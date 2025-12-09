<?php

namespace Tests\Feature\Admin;

use App\Models\Page;
use App\Models\PageCategory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PageBlockManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $viewsPath = storage_path('framework/views');
        if (! is_dir($viewsPath)) {
            mkdir($viewsPath, 0777, true);
        }
        config(['view.compiled' => $viewsPath]);

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('text_blocks');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('page_categories');
        Schema::enableForeignKeyConstraints();

        Schema::create('page_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('language', 8)->default('uk');
            $table->timestamps();
            
            $table->foreign('parent_id')
                ->references('id')
                ->on('page_categories')
                ->nullOnDelete();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_category_id')
                ->nullable()
                ->constrained('page_categories')
                ->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('text')->nullable();
            $table->timestamps();
        });

        Schema::create('text_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')
                ->nullable()
                ->constrained('pages')
                ->cascadeOnDelete();
            $table->foreignId('page_category_id')
                ->nullable()
                ->constrained('page_categories')
                ->cascadeOnDelete();
            $table->string('locale', 8)->default('uk');
            $table->string('type', 32)->default('box');
            $table->string('level', 16)->nullable();
            $table->string('column', 32)->nullable();
            $table->string('heading')->nullable();
            $table->string('css_class')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->longText('body')->nullable();
            $table->string('seeder')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('text_blocks');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('page_categories');
        Schema::enableForeignKeyConstraints();

        parent::tearDown();
    }

    public function test_admin_can_open_block_edit_form_for_page(): void
    {
        [$page, $block] = $this->createPageWithBlock();

        $response = $this->withSession($this->adminSession())
            ->get(route('pages.manage.blocks.edit', [$page, $block]));

        $response->assertOk();
    }

    public function test_block_edit_form_returns_not_found_for_block_from_another_page(): void
    {
        [$page, $block] = $this->createPageWithBlock();
        $otherPage = $this->createPage($page->page_category_id, 'other-page');

        $response = $this->withSession($this->adminSession())
            ->get(route('pages.manage.blocks.edit', [$otherPage, $block]));

        $response->assertNotFound();
    }

    private function createPageWithBlock(): array
    {
        $category = PageCategory::create([
            'title' => 'Category',
            'slug' => 'category',
            'language' => 'uk',
        ]);

        $page = $this->createPage($category->id, 'test-page');

        $block = $page->textBlocks()->create([
            'locale' => 'uk',
            'type' => 'box',
            'column' => 'left',
            'heading' => 'Heading',
            'sort_order' => 10,
            'body' => 'Body',
        ]);

        return [$page, $block];
    }

    private function createPage(int $categoryId, string $slug): Page
    {
        return Page::create([
            'slug' => $slug,
            'title' => ucfirst(str_replace('-', ' ', $slug)),
            'text' => '',
            'page_category_id' => $categoryId,
        ]);
    }

    private function adminSession(): array
    {
        return ['admin_authenticated' => true];
    }
}
