<?php

namespace Tests\Feature\Admin;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PageCategoryManagementTest extends TestCase
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
        Schema::dropIfExists('tag_text_block');
        Schema::dropIfExists('page_category_tag');
        Schema::dropIfExists('page_tag');
        Schema::dropIfExists('tags');
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
            $table->uuid('uuid')->nullable()->unique();
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

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
            $table->timestamps();
        });

        Schema::create('page_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->unique(['tag_id', 'page_id']);
            $table->timestamps();
        });

        Schema::create('page_category_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->foreignId('page_category_id')->constrained()->cascadeOnDelete();
            $table->unique(['tag_id', 'page_category_id']);
            $table->timestamps();
        });

        Schema::create('tag_text_block', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->foreignId('text_block_id')->constrained()->cascadeOnDelete();
            $table->unique(['tag_id', 'text_block_id']);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('tag_text_block');
        Schema::dropIfExists('page_category_tag');
        Schema::dropIfExists('page_tag');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('text_blocks');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('page_categories');
        Schema::enableForeignKeyConstraints();

        parent::tearDown();
    }

    public function test_admin_can_create_category(): void
    {
        $response = $this->withSession($this->adminSession())
            ->post(route('pages.manage.categories.store'), [
                'title' => 'Grammar',
                'slug' => 'grammar',
                'language' => 'uk',
            ]);

        $response->assertRedirect(route('pages.manage.index', ['tab' => 'categories']));

        $this->assertDatabaseHas('page_categories', [
            'title' => 'Grammar',
            'slug' => 'grammar',
            'language' => 'uk',
        ]);
    }

    public function test_admin_can_update_category(): void
    {
        $category = PageCategory::create([
            'title' => 'Grammar',
            'slug' => 'grammar',
            'language' => 'uk',
        ]);

        $response = $this->withSession($this->adminSession())
            ->put(route('pages.manage.categories.update', $category), [
                'title' => 'Updated Grammar',
                'slug' => 'updated-grammar',
                'language' => 'en',
            ]);

        $response->assertRedirect(route('pages.manage.index', ['tab' => 'categories']));

        $this->assertDatabaseHas('page_categories', [
            'id' => $category->id,
            'title' => 'Updated Grammar',
            'slug' => 'updated-grammar',
            'language' => 'en',
        ]);
    }

    public function test_admin_can_delete_category_and_detach_pages(): void
    {
        $category = PageCategory::create([
            'title' => 'Grammar',
            'slug' => 'grammar',
            'language' => 'uk',
        ]);

        $page = Page::create([
            'slug' => 'verbs',
            'title' => 'Verbs',
            'text' => '',
            'page_category_id' => $category->id,
        ]);

        $response = $this->withSession($this->adminSession())
            ->delete(route('pages.manage.categories.destroy', $category));

        $response->assertRedirect(route('pages.manage.index', ['tab' => 'categories']));

        $this->assertDatabaseMissing('page_categories', ['id' => $category->id]);
        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'page_category_id' => null,
        ]);
    }

    public function test_categories_tab_shows_page_counts(): void
    {
        $category = PageCategory::create([
            'title' => 'Grammar',
            'slug' => 'grammar',
            'language' => 'uk',
        ]);

        Page::create([
            'slug' => 'verbs',
            'title' => 'Verbs',
            'text' => '',
            'page_category_id' => $category->id,
        ]);

        Page::create([
            'slug' => 'nouns',
            'title' => 'Nouns',
            'text' => '',
            'page_category_id' => $category->id,
        ]);

        PageCategory::create([
            'title' => 'Vocabulary',
            'slug' => 'vocabulary',
            'language' => 'uk',
        ]);

        $response = $this->withSession($this->adminSession())
            ->get(route('pages.manage.index', ['tab' => 'categories']));

        $response->assertOk();

        $response->assertViewHas('categories', function ($categories) use ($category) {
            $found = $categories->firstWhere('id', $category->id);

            return $found && (int) $found->pages_count === 2;
        });
    }

    public function test_admin_can_delete_all_empty_categories(): void
    {
        $filled = PageCategory::create([
            'title' => 'Grammar',
            'slug' => 'grammar',
            'language' => 'uk',
        ]);

        $emptyOne = PageCategory::create([
            'title' => 'Vocabulary',
            'slug' => 'vocabulary',
            'language' => 'uk',
        ]);

        $emptyTwo = PageCategory::create([
            'title' => 'Listening',
            'slug' => 'listening',
            'language' => 'uk',
        ]);

        Page::create([
            'slug' => 'verbs',
            'title' => 'Verbs',
            'text' => '',
            'page_category_id' => $filled->id,
        ]);

        $response = $this->withSession($this->adminSession())
            ->delete(route('pages.manage.categories.destroy-empty'));

        $response->assertRedirect(route('pages.manage.index', ['tab' => 'categories']));

        $this->assertDatabaseHas('page_categories', ['id' => $filled->id]);
        $this->assertDatabaseMissing('page_categories', ['id' => $emptyOne->id]);
        $this->assertDatabaseMissing('page_categories', ['id' => $emptyTwo->id]);
    }

    public function test_admin_can_view_category_description_blocks(): void
    {
        $category = PageCategory::create([
            'title' => 'Grammar',
            'slug' => 'grammar',
            'language' => 'uk',
        ]);

        $response = $this->withSession($this->adminSession())
            ->get(route('pages.manage.categories.blocks.index', $category));

        $response->assertOk();
    }

    public function test_admin_can_store_category_description_block(): void
    {
        $category = PageCategory::create([
            'title' => 'Grammar',
            'slug' => 'grammar',
            'language' => 'uk',
        ]);

        $response = $this->withSession($this->adminSession())
            ->post(route('pages.manage.categories.blocks.store', $category), [
                'locale' => 'uk',
                'type' => 'box',
                'column' => 'left',
                'heading' => 'Intro',
                'sort_order' => 10,
                'body' => '<p>About grammar</p>',
            ]);

        $response->assertRedirect(route('pages.manage.categories.blocks.index', $category));

        $this->assertDatabaseHas('text_blocks', [
            'page_category_id' => $category->id,
            'heading' => 'Intro',
        ]);
    }

    public function test_category_block_can_be_created_with_tags(): void
    {
        $category = PageCategory::create([
            'title' => 'Grammar',
            'slug' => 'grammar-tags',
            'language' => 'uk',
        ]);

        $tag = Tag::create(['name' => 'Theory']);

        $response = $this->withSession($this->adminSession())
            ->post(route('pages.manage.categories.blocks.store', $category), [
                'locale' => 'uk',
                'type' => 'box',
                'column' => 'left',
                'heading' => 'Tagged intro',
                'sort_order' => 10,
                'body' => '<p>About grammar</p>',
                'tags' => [$tag->id],
            ]);

        $response->assertRedirect(route('pages.manage.categories.blocks.index', $category));

        $blockId = DB::table('text_blocks')
            ->where('page_category_id', $category->id)
            ->where('heading', 'Tagged intro')
            ->value('id');

        $this->assertNotNull($blockId);

        $this->assertDatabaseHas('tag_text_block', [
            'text_block_id' => $blockId,
            'tag_id' => $tag->id,
        ]);
    }

    public function test_category_block_edit_form_returns_not_found_for_foreign_category(): void
    {
        $category = PageCategory::create([
            'title' => 'Grammar',
            'slug' => 'grammar',
            'language' => 'uk',
        ]);

        $other = PageCategory::create([
            'title' => 'Vocabulary',
            'slug' => 'vocabulary',
            'language' => 'uk',
        ]);

        $block = $category->textBlocks()->create([
            'locale' => 'uk',
            'type' => 'box',
            'column' => 'left',
            'heading' => 'Intro',
            'sort_order' => 10,
            'body' => '<p>Intro</p>',
        ]);

        $response = $this->withSession($this->adminSession())
            ->get(route('pages.manage.categories.blocks.edit', [$other, $block]));

        $response->assertNotFound();
    }

    private function adminSession(): array
    {
        return ['admin_authenticated' => true];
    }
}
